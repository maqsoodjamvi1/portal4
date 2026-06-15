<?php

namespace App\Libraries;

/**
 * Links online quizzes to formal exams (unannounced / announced).
 * Exam-linked quizzes stay hidden from student/parent portals until the exam is announced.
 */
class ExamQuizService
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function hasExamIdColumn(): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        try {
            $cached = $this->db->fieldExists('exam_id', 'quizzes');
        } catch (\Throwable $e) {
            $cached = false;
        }

        return $cached;
    }

    /**
     * Latest unannounced exam for campus + academic session.
     *
     * @return array<string, mixed>|null
     */
    public function resolveUnannouncedExam(int $campusId, int $sessionId): ?array
    {
        if ($campusId <= 0 || $sessionId <= 0) {
            return null;
        }

        $row = $this->db->table('exam e')
            ->select('e.eid, e.exam_name, e.short_name, e.exam_start_date, e.exam_end_date, e.status, e.term_id')
            ->where('e.campus_id', $campusId)
            ->where('e.session_id', $sessionId)
            ->where('e.status', '0')
            ->orderBy('e.eid', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getExamStatus(int $examId): ?string
    {
        if ($examId <= 0) {
            return null;
        }

        $row = $this->db->table('exam')
            ->select('status')
            ->where('eid', $examId)
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ? (string) ($row['status'] ?? '') : null;
    }

    public function getExamIdFromQuiz(object|array $quiz): int
    {
        if (!$this->hasExamIdColumn()) {
            return 0;
        }

        if (is_array($quiz)) {
            return (int) ($quiz['exam_id'] ?? 0);
        }

        return (int) ($quiz->exam_id ?? 0);
    }

    /** Hidden from student/parent portals while linked exam is unannounced. */
    public function isPortalHidden(object|array $quiz): bool
    {
        $examId = $this->getExamIdFromQuiz($quiz);
        if ($examId <= 0) {
            return false;
        }

        return $this->getExamStatus($examId) === '0';
    }

    /** SQL fragment: exclude exam-only (unannounced) quizzes from portal listings. */
    public function portalVisibilitySql(string $quizAlias = 'q'): string
    {
        if (!$this->hasExamIdColumn()) {
            return '';
        }

        $q = preg_replace('/[^a-zA-Z0-9_]/', '', $quizAlias) ?: 'q';

        return " AND ({$q}.exam_id IS NULL OR {$q}.exam_id = 0 OR EXISTS (
            SELECT 1 FROM exam ex
            WHERE ex.eid = {$q}.exam_id AND ex.status = '1'
        )) ";
    }

    /** Admin impersonation may run exam-prep quizzes (unpublished / not yet on portal). */
    public function adminImpersonationAllowed(object|array $quiz): bool
    {
        if (is_array($quiz)) {
            $published = (int) ($quiz['is_published'] ?? 0) === 1;
        } else {
            $published = (int) ($quiz->is_published ?? 0) === 1;
        }

        if ($published) {
            return true;
        }

        return $this->getExamIdFromQuiz($quiz) > 0;
    }

    /** Skip schedule window checks when admin tests an exam-linked quiz. */
    public function adminBypassSchedule(object|array $quiz): bool
    {
        return $this->getExamIdFromQuiz($quiz) > 0;
    }
}
