<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;

class PendingQuizzes extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = db_connect();
        $this->session = session();
        helper(['url']);
    }

    /**
     * Get current logged-in student + cls_sec_id
     */
    private function getStudentMeta(): array
    {
        $studentId = (int) $this->session->get('student_id');
        $clsSecId  = (int) $this->session->get('cls_sec_id');

        if ($studentId && !$clsSecId) {
            // fallback: fetch from a_students
            $row = $this->db->table('a_students')
                ->select('cls_sec_id')
                ->where('student_id', $studentId)
                ->limit(1)->get()->getRow();
            if ($row) {
                $clsSecId = (int) ($row->cls_sec_id ?? 0);
            }
        }

        return [$studentId, $clsSecId];
    }

    public function index()
    {
        [$studentId, $clsSecId] = $this->getStudentMeta();

        if (!$studentId || !$clsSecId) {
            return view('frontend/quizzes/pending', [
                'unattempted'     => [],
                'err'             => 'Student or class-section not set.',
                'termOptions'     => [],
                'subjectOptions'  => [],
                'currentTerm'     => 0,
                'currentSubject'  => 0,
            ]);
        }

        // --- Filters from GET ---
        $termSessionId = (int) $this->request->getGet('term_session_id');
        $subjectId     = (int) $this->request->getGet('subject_id');

        // ---------------------------------
        // 1) Get pending quizzes (with attempts count, subject & term/session)
        // ---------------------------------
        $params    = [$studentId, $clsSecId];
        $filterSql = '';

        if ($termSessionId > 0) {
            $filterSql   .= ' AND q.term_session_id = ?';
            $params[]     = $termSessionId;
        }
        if ($subjectId > 0) {
            $filterSql   .= ' AND q.subject_id = ?';
            $params[]     = $subjectId;
        }

      $sql = "
    SELECT 
        q.quiz_id,
        q.title,
        q.start_at,
        q.end_at,
        q.max_attempts,
        q.sec_sub_id,                       -- replaced subject_id
        q.term_session_id,
        q.questions_count,

        s.subject_name,
        s.subject_short_name,

        ts.term_session_id AS ts_id,
        CONCAT(t.short_name, ' - ', acs.session_name) AS term_session_label,

        COUNT(qa.attempt_id) AS attempts_used

    FROM quizzes q

    LEFT JOIN quiz_attempts qa
           ON qa.quiz_id    = q.quiz_id
          AND qa.student_id = ?
          AND qa.status     = 'submitted'

    -- NEW: get subject from sec_sub_id
    LEFT JOIN section_subjects ss
           ON ss.sec_sub_id = q.sec_sub_id

    LEFT JOIN allsubject s
           ON s.sid = ss.subject_id

    LEFT JOIN terms_session ts
           ON ts.term_session_id = q.term_session_id

    LEFT JOIN terms t
           ON t.term_id = ts.term_id

    LEFT JOIN academic_session acs
           ON acs.session_id = ts.session_id

    WHERE q.cls_sec_id   = ?
      AND q.is_published = 1
      AND (q.start_at IS NULL OR q.start_at <= NOW())
      AND (q.end_at   IS NULL OR q.end_at   >= NOW())
      {$filterSql}

    GROUP BY 
        q.quiz_id,
        q.title,
        q.start_at,
        q.end_at,
        q.max_attempts,
        q.sec_sub_id,
        q.term_session_id,
        q.questions_count,
        s.subject_name,
        s.subject_short_name,
        ts.term_session_id,
        t.short_name,
        acs.session_name

    HAVING attempts_used < q.max_attempts

    ORDER BY COALESCE(q.start_at, '1970-01-01') DESC, q.quiz_id DESC
";


        $unattempted = $this->db->query($sql, $params)->getResult();

        // ---------------------------------
        // 2) Enrich each quiz with:
        //    - remaining_attempts
        //    - total questions from quiz_questions
        //    - last attempt result (correct, wrong, score)
        // ---------------------------------
        foreach ($unattempted as $row) {
            $quizId         = (int) $row->quiz_id;
            $attemptsUsed   = (int) ($row->attempts_used ?? 0);
            $row->attempts_used = $attemptsUsed;
            $row->remaining_attempts = max(0, ((int) $row->max_attempts) - $attemptsUsed);

            // total questions as per quiz_questions table
            $row->questions_db = (int) $this->db->table('quiz_questions')
                ->where('quiz_id', $quizId)
                ->countAllResults();

            // questions_count as per quizzes.questions_count (already selected)
            $row->questions_count = (int) ($row->questions_count ?? 0);

            // LAST SUBMITTED ATTEMPT
            $attempt = $this->db->table('quiz_attempts')
                ->where('quiz_id', $quizId)
                ->where('student_id', $studentId)
                ->where('status', 'submitted')
                ->orderBy('submitted_at', 'DESC')
                ->get(1)->getRow();

            $row->last_score    = null;
            $row->correct_count = 0;
            $row->wrong_count   = 0;

            if ($attempt) {
                $row->last_score = (float) $attempt->score_obtained;

                $res = $this->db->table('quiz_attempt_answers')
                    ->select("
                        SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) AS correct_count,
                        SUM(CASE WHEN is_correct = 0 THEN 1 ELSE 0 END) AS wrong_count
                    ")
                    ->where('attempt_id', $attempt->attempt_id)
                    ->get()->getRow();

                if ($res) {
                    $row->correct_count = (int) ($res->correct_count ?? 0);
                    $row->wrong_count   = (int) ($res->wrong_count   ?? 0);
                }
            }
        }

        // ---------------------------------
        // 3) Filter dropdown options (terms & subjects)
        // ---------------------------------

        // Subjects for this class-section's quizzes
       $subjectOptions = $this->db->query("
    SELECT DISTINCT
        ss.subject_id,
        COALESCE(s.subject_short_name, s.subject_name) AS subject_label
    FROM quizzes q
    LEFT JOIN section_subjects ss
           ON ss.sec_sub_id = q.sec_sub_id
    LEFT JOIN allsubject s
           ON s.sid = ss.subject_id
    WHERE q.cls_sec_id = ?
      AND q.is_published = 1
    ORDER BY subject_label
", [$clsSecId])->getResult();

        // Term/session options for this class-section's quizzes
        $termOptions = $this->db->query("
            SELECT DISTINCT
                ts.term_session_id,
                CONCAT(t.short_name, ' - ', acs.session_name) AS term_session_label
            FROM quizzes q
            JOIN terms_session ts ON ts.term_session_id = q.term_session_id
            JOIN terms t         ON t.term_id          = ts.term_id
            JOIN academic_session acs ON acs.session_id = ts.session_id
            WHERE q.cls_sec_id   = ?
              AND q.is_published = 1
            ORDER BY acs.session_name DESC, t.short_name ASC
        ", [$clsSecId])->getResult();

        return view('frontend/quizzes/pending', [
            'unattempted'     => $unattempted,
            'err'             => '',
            'termOptions'     => $termOptions,
            'subjectOptions'  => $subjectOptions,
            'currentTerm'     => $termSessionId,
            'currentSubject'  => $subjectId,
        ]);
    }
}
