<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ExamQuizService;

class QuizAssign extends BaseController
{
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->session = session();
        check_any_permission(['admin-quiz', 'admin-quiz-assign']);
    }

    private function resolveCampusId(): int
    {
        return (int) ($this->session->get('member_campusid') ?? 0);
    }

    /** @return array{cls_sec_id:int,class_name:string,section_name:string}|null */
    private function classSectionForCampus(int $clsSecId, int $campusId): ?array
    {
        if ($clsSecId <= 0 || $campusId <= 0) {
            return null;
        }

        return \Config\Database::connect()
            ->table('class_section cs')
            ->select('cs.cls_sec_id, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections s', 's.section_id = cs.section_id', 'inner')
            ->where('cs.cls_sec_id', $clsSecId)
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->get()
            ->getRowArray() ?: null;
    }

    public function index()
    {
        $campusId = $this->resolveCampusId();
        $classSections = [];

        if ($campusId > 0) {
            $classSections = \Config\Database::connect()
                ->table('class_section cs')
                ->select('cs.cls_sec_id, c.class_name, s.section_name')
                ->join('classes c', 'c.class_id = cs.class_id', 'inner')
                ->join('sections s', 's.section_id = cs.section_id', 'inner')
                ->where('cs.campus_id', $campusId)
                ->where('cs.status', 1)
                ->orderBy('c.class_name', 'ASC')
                ->orderBy('s.section_name', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('admin/quizzes/quiz_selector', [
            'classSections'       => $classSections,
            'examQuizColumnReady' => (new ExamQuizService())->hasExamIdColumn(),
            'unannouncedExam'     => (new ExamQuizService())->resolveUnannouncedExam(
                $campusId,
                (int) (session('member_sessionid') ?? 0)
            ),
        ]);
    }

/**
 * Load students by cls_sec_id
 */


public function load_students_for_quiz($cls_sec_id, $quiz_id)
{
    $db         = \Config\Database::connect();
    $cls_sec_id = (int) $cls_sec_id;
    $quiz_id    = (int) $quiz_id;
    $campusId   = $this->resolveCampusId();

    if ($campusId <= 0 || ! $this->classSectionForCampus($cls_sec_id, $campusId)) {
        return $this->response->setJSON([
            'available'     => false,
            'reason'        => 'access_denied',
            'attempted'     => [],
            'not_attempted' => [],
        ]);
    }

    // ==========================================
    // 0) Get quiz meta: subject_name + topic_name + start/end
    // ==========================================
    $examQuizSvc = new ExamQuizService();
    $selectCols  = 'q.quiz_id, q.title, q.sec_sub_id, q.start_at, q.end_at, a.subject_name, t.topic_name';
    if ($examQuizSvc->hasExamIdColumn()) {
        $selectCols = 'q.quiz_id, q.title, q.sec_sub_id, q.start_at, q.end_at, q.exam_id, a.subject_name, t.topic_name';
    }

    $quizMeta = $db->table('quizzes q')
        ->select($selectCols, false)
        ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id', 'inner')
        ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
        ->join('qb_topics t', 't.id = q.topic_id', 'left')
        ->where('q.quiz_id', $quiz_id)
        ->where('cs.campus_id', $campusId)
        ->get()
        ->getRowArray();

    $topicsJoined = '';
    if ($quizMeta && $db->tableExists('quiz_topics')) {
        $topicRow = $db->query("
            SELECT GROUP_CONCAT(DISTINCT qt.topic_name ORDER BY qt.topic_name SEPARATOR ', ') AS topics
            FROM quiz_topics qtp
            JOIN qb_topics qt ON qt.id = qtp.topic_id
            WHERE qtp.quiz_id = ?
        ", [$quiz_id])->getRowArray();
        $topicsJoined = trim((string) ($topicRow['topics'] ?? ''));
    }

    // If quiz not found → nothing to show
    if (!$quizMeta) {
        return $this->response->setJSON([
            'available'      => false,
            'reason'         => 'quiz_not_found',
            'attempted'      => [],
            'not_attempted'  => [],
        ]);
    }

    $subjectName  = trim((string) ($quizMeta['subject_name'] ?? ''));
    $topicName    = trim((string) ($quizMeta['topic_name'] ?? ''));
    $topicDisplay = $topicsJoined !== '' ? $topicsJoined : $topicName;

    // ==========================================
    // ✅ Availability rule
    // - If start_at = end_at        → always available
    // - Else only if end_at not finished
    // ==========================================
    $now     = date('Y-m-d H:i:s');
    $startAt = $quizMeta['start_at'] ?? null;
    $endAt   = $quizMeta['end_at']   ?? null;

    // Normalise empty endAt
    $endAtNullish = (!$endAt || $endAt === '0000-00-00 00:00:00');

    $alwaysAvailable = ($startAt && $endAt && $startAt === $endAt);

    $endNotFinished  = $endAtNullish || ($endAt >= $now);

    $isAvailable = $alwaysAvailable || $endNotFinished;

    $examQuizSvc = new ExamQuizService();
    $examId      = $examQuizSvc->getExamIdFromQuiz($quizMeta);
    if ($examId > 0 && $examQuizSvc->getExamStatus($examId) === '0') {
        $isAvailable = true;
    }

    if (!$isAvailable) {
        // Quiz is closed / expired → don’t show any students
        return $this->response->setJSON([
            'available'      => false,
            'reason'         => 'quiz_expired',
            'attempted'      => [],
            'not_attempted'  => [],
        ]);
    }

    // ==========================================
    // 1) Attempted students
    // ==========================================
    $attempted = $db->query("
        SELECT 
            s.student_id,
            CONCAT(s.first_name, ' ', s.last_name) AS full_name,
            s.gender,
            s.date_of_birth,
            s.profile_photo,

            c.class_name,
            se.section_name,

            p.f_name AS father_name,
            p.father_cnic,
            p.whatsapp,

            qa.score_obtained,
            qa.attempt_id,
            qa.status AS attempt_status

        FROM students s
        JOIN student_class sc ON sc.student_id = s.student_id
        JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        JOIN classes c ON c.class_id = cs.class_id
        JOIN sections se ON se.section_id = cs.section_id
        LEFT JOIN parents p ON p.parent_id = s.parent_id
        JOIN quiz_attempts qa ON qa.student_id = s.student_id AND qa.quiz_id = ?

        WHERE sc.cls_sec_id = ?
          AND sc.status = 1
          AND s.campus_id = ?
    ", [$quiz_id, $cls_sec_id, $campusId])->getResultArray();

    // ==========================================
    // 2) NOT attempted students
    // ==========================================
    $not_attempted = $db->query("
        SELECT 
            s.student_id,
            CONCAT(s.first_name, ' ', s.last_name) AS full_name,
            s.gender,
            s.date_of_birth,
            s.profile_photo,

            c.class_name,
            se.section_name,

            p.f_name AS father_name,
            p.father_cnic,
            p.whatsapp

        FROM students s
        JOIN student_class sc ON sc.student_id = s.student_id
        JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        JOIN classes c ON c.class_id = cs.class_id
        JOIN sections se ON se.section_id = cs.section_id
        LEFT JOIN parents p ON p.parent_id = s.parent_id

        WHERE sc.cls_sec_id = ?
          AND sc.status = 1
          AND s.campus_id = ?
          AND s.student_id NOT IN (
             SELECT student_id FROM quiz_attempts WHERE quiz_id = ?
          )
    ", [$cls_sec_id, $campusId, $quiz_id])->getResultArray();

    // ==========================================
    // 3) Age calculation helper
    // ==========================================
    $ageCalc = function ($dob) {
        if (!$dob) {
            return ['years' => 0, 'months' => 0];
        }
        try {
            $d1   = new \DateTime($dob);
            $d2   = new \DateTime();
            $diff = $d1->diff($d2);
            return ['years' => $diff->y, 'months' => $diff->m];
        } catch (\Exception $e) {
            return ['years' => 0, 'months' => 0];
        }
    };

    // ==========================================
    // 4) Enrich rows: age + subject_name + topic_name
    // ==========================================
    foreach ($attempted as &$a) {
        $x = $ageCalc($a['date_of_birth'] ?? null);
        $a['age_years']    = $x['years'];
        $a['age_months']   = $x['months'];
        $a['subject_name']  = $subjectName;
        $a['topic_name']    = $topicDisplay;
        $a['topic_display'] = $topicDisplay;
    }
    unset($a);

    foreach ($not_attempted as &$na) {
        $x = $ageCalc($na['date_of_birth'] ?? null);
        $na['age_years']    = $x['years'];
        $na['age_months']   = $x['months'];
        $na['subject_name']  = $subjectName;
        $na['topic_name']    = $topicDisplay;
        $na['topic_display'] = $topicDisplay;
    }
    unset($na);

    // ==========================================
    // 5) Return JSON
    // ==========================================
    return $this->response->setJSON([
        'available'      => true,
        'quiz'           => [
            'quiz_id'      => $quiz_id,
            'title'        => trim((string) ($quizMeta['title'] ?? '')),
            'subject_name' => $subjectName,
            'topic_name'   => $topicDisplay,
        ],
        'attempted'      => $attempted,
        'not_attempted'  => $not_attempted,
    ]);
}

    /**
     * Load subjects by cls_sec_id (NOT student_id)
     */
    public function load_subjects($cls_sec_id)
    {
        $db        = \Config\Database::connect();
        $cls_sec_id = (int) $cls_sec_id;
        $campusId   = $this->resolveCampusId();

        if ($cls_sec_id <= 0 || $campusId <= 0 || ! $this->classSectionForCampus($cls_sec_id, $campusId)) {
            return $this->response->setJSON([]);
        }

        $subjects = $db->query("
            SELECT 
                sc.sec_sub_id,
                sc.subject_id,
                s.subject_name
            FROM section_subjects sc
            JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            JOIN allsubject s ON sc.subject_id = s.sid
            WHERE sc.cls_sec_id = ?
              AND cs.campus_id = ?
              AND sc.status = 1
        ", [$cls_sec_id, $campusId])->getResultArray();

        // JS expects a plain array, so we return it directly
        return $this->response->setJSON($subjects);
    }

    /**
     * Load quizzes by sec_sub_id
     */
    public function load_quizzes($sec_sub_id)
    {
        $db = \Config\Database::connect();
        $sec_sub_id = (int) $sec_sub_id;
        $campusId   = $this->resolveCampusId();

        if ($sec_sub_id <= 0 || $campusId <= 0) {
            return $this->response->setJSON([]);
        }

        $quizzes = $db->query("
            SELECT q.quiz_id, q.title
            FROM quizzes q
            JOIN section_subjects ss ON ss.sec_sub_id = q.sec_sub_id
            JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
            WHERE q.sec_sub_id = ?
              AND cs.campus_id = ?
            ORDER BY q.quiz_id DESC
        ", [$sec_sub_id, $campusId])->getResultArray();

        return $this->response->setJSON($quizzes);
    }

public function generateImpersonationLink()
{
    $session = session();

    // Only allow AJAX
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(405)
            ->setJSON([
                'success' => false,
                'message' => 'AJAX request required.',
            ]);
    }

   

    $userId = $this->session->get('member_userid');
    if ($userId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Access denied (not logged in).',
        ]);
    }

    $campusId = $this->resolveCampusId();
    if ($campusId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Campus not selected.',
        ]);
    }

    // Get POST data
    $quiz_id    = (int) $this->request->getPost('quiz_id');
    $student_id = (int) $this->request->getPost('student_id');

    if ($quiz_id <= 0 || $student_id <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid quiz or student.',
        ]);
    }

    $db = \Config\Database::connect();

    $studentOk = $db->table('students')
        ->where('student_id', $student_id)
        ->where('campus_id', $campusId)
        ->countAllResults() > 0;

    $quizOk = $db->table('quizzes q')
        ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id', 'inner')
        ->where('q.quiz_id', $quiz_id)
        ->where('cs.campus_id', $campusId)
        ->countAllResults() > 0;

    if (! $studentOk || ! $quizOk) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Quiz or student does not belong to your campus.',
        ]);
    }

    // Create token
    $token     = bin2hex(random_bytes(24)); // 48 hex chars
    $expiresAt = date('Y-m-d H:i:s', strtotime('+3 hours')); // adjust lifetime

    $data = [
        'token'      => $token,
        'quiz_id'    => $quiz_id,
        'student_id' => $student_id,
        'created_by' => $userId,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => $expiresAt,
        'used'       => 0,
    ];

    $db->table('quiz_impersonation_tokens')->insert($data);

    // Must match your frontend route: quiz/start/(:num)
    $link = site_url("quiz/start/{$quiz_id}?impersonate_token={$token}");

    return $this->response->setJSON([
        'success' => true,
        'link'    => $link,
        'message' => 'Quiz link generated.',
    ]);
}

/**
 * Remove a student's quiz attempt so they can take the quiz again (Quiz Assign).
 */
public function resetQuizAttempt()
{
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(405)->setJSON([
            'success' => false,
            'message' => 'AJAX request required.',
        ]);
    }

    $campusId   = $this->resolveCampusId();
    $attemptId  = (int) $this->request->getPost('attempt_id');
    $quizId     = (int) $this->request->getPost('quiz_id');
    $studentId  = (int) $this->request->getPost('student_id');

    if ($campusId <= 0 || $attemptId <= 0 || $quizId <= 0 || $studentId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request.',
        ]);
    }

    $db = \Config\Database::connect();

    $attempt = $db->table('quiz_attempts qa')
        ->select('qa.attempt_id, qa.quiz_id, qa.student_id')
        ->join('students s', 's.student_id = qa.student_id', 'inner')
        ->join('quizzes q', 'q.quiz_id = qa.quiz_id', 'inner')
        ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id', 'inner')
        ->where('qa.attempt_id', $attemptId)
        ->where('qa.quiz_id', $quizId)
        ->where('qa.student_id', $studentId)
        ->where('s.campus_id', $campusId)
        ->where('cs.campus_id', $campusId)
        ->get()
        ->getRow();

    if (! $attempt) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Attempt not found or access denied.',
        ]);
    }

    $db->transStart();

    try {
        if ($db->tableExists('quiz_attempt_answers')) {
            $db->table('quiz_attempt_answers')->where('attempt_id', $attemptId)->delete();
        }
        if ($db->tableExists('quiz_attempt_questions')) {
            $db->table('quiz_attempt_questions')->where('attempt_id', $attemptId)->delete();
        }
        $db->table('quiz_attempts')->where('attempt_id', $attemptId)->delete();
    } catch (\Throwable $e) {
        $db->transRollback();
        log_message('error', 'resetQuizAttempt failed: ' . $e->getMessage());

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Could not reset attempt. Please try again.',
        ]);
    }

    if ($db->transStatus() === false) {
        $db->transRollback();

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Could not reset attempt.',
        ]);
    }

    $db->transComplete();

    return $this->response->setJSON([
        'success' => true,
        'message' => 'Attempt removed. The student can take this quiz again.',
    ]);
}

public function load_quizzes_by_clssec($clsSecId = 0)
{
    $db       = \Config\Database::connect();
    $clsSecId = (int) $clsSecId;
    $campusId = $this->resolveCampusId();
    $mode     = strtolower(trim((string) ($this->request->getGet('mode') ?? 'regular')));

    if ($clsSecId <= 0 || $campusId <= 0 || ! $this->classSectionForCampus($clsSecId, $campusId)) {
        return $this->response->setJSON([]);
    }

    $sessionId   = (int) (session('member_sessionid') ?? 0);
    $examQuizSvc = new ExamQuizService();
    $unannounced = $examQuizSvc->resolveUnannouncedExam($campusId, $sessionId);
    $examFilter  = '';

    if ($examQuizSvc->hasExamIdColumn()) {
        if ($mode === 'exam') {
            if (!$unannounced) {
                return $this->response->setJSON([]);
            }
            $examFilter = ' AND q.exam_id = ' . (int) ($unannounced['eid'] ?? 0) . ' ';
        } else {
            $examFilter = " AND (q.exam_id IS NULL OR q.exam_id = 0 OR EXISTS (
                SELECT 1 FROM exam ex WHERE ex.eid = q.exam_id AND ex.status = '1'
            )) ";
        }
    } elseif ($mode === 'exam') {
        return $this->response->setJSON([]);
    }

    $examIdSelect = $examQuizSvc->hasExamIdColumn() ? 'q.exam_id, q.is_published,' : '';
    $examGroupBy  = $examQuizSvc->hasExamIdColumn() ? 'q.exam_id, q.is_published,' : '';

    // Use current academic session from staff session
    $sql = "
        SELECT
            q.quiz_id,
            q.title,
            {$examIdSelect}

            cs.cls_sec_id,
            c.class_name,
            se.section_name,

            a.subject_name,

            -- topics from quiz_topics
            GROUP_CONCAT(DISTINCT qt.topic_name ORDER BY qt.topic_name SEPARATOR ', ') AS topics,

            q.start_at,
            q.end_at,
            q.time_limit_sec,         -- seconds
            q.questions_count,
            q.count_mcq_single,
            q.count_mcq_multi,
            q.count_tf,
            q.count_short,
            q.count_fill,
            q.count_match,

            -- total students in this class-section + session
            (
              SELECT COUNT(*)
              FROM student_class sc
              WHERE sc.cls_sec_id = cs.cls_sec_id
                AND sc.session_id = :sessionId:
                AND sc.status = 1
            ) AS total_students,

            -- attempts of this quiz by students of this class-section + session
            (
              SELECT COUNT(*)
              FROM quiz_attempts qa
              WHERE qa.quiz_id = q.quiz_id
                AND qa.student_id IN (
                  SELECT sc2.student_id
                  FROM student_class sc2
                  WHERE sc2.cls_sec_id = cs.cls_sec_id
                    AND sc2.session_id = :sessionId:
                    AND sc2.status = 1
                )
            ) AS attempted_students

        FROM quizzes q
        JOIN section_subjects ss ON ss.sec_sub_id = q.sec_sub_id
        JOIN class_section cs    ON cs.cls_sec_id = ss.cls_sec_id
        JOIN classes c           ON c.class_id    = cs.class_id
        JOIN sections se         ON se.section_id = cs.section_id
        JOIN allsubject a        ON a.sid         = ss.subject_id
        LEFT JOIN quiz_topics qtp ON qtp.quiz_id  = q.quiz_id
        LEFT JOIN qb_topics qt    ON qt.id        = qtp.topic_id

        WHERE cs.cls_sec_id = :clsSecId:
          AND cs.campus_id = :campusId:
          {$examFilter}

        GROUP BY
            q.quiz_id,
            q.title,
            {$examGroupBy}
            cs.cls_sec_id,
            c.class_name,
            se.section_name,
            a.subject_name,
            q.start_at,
            q.end_at,
            q.time_limit_sec,
            q.questions_count,
            q.count_mcq_single,
            q.count_mcq_multi,
            q.count_tf,
            q.count_short,
            q.count_fill,
            q.count_match

        ORDER BY q.quiz_id DESC
    ";

    $rows = $db->query($sql, [
        'clsSecId'  => $clsSecId,
        'campusId'  => $campusId,
        'sessionId' => $sessionId,
    ])->getResultArray();

    $availableRows = [];
    $now = new \DateTime();
    $skipExpiryCheck = ($mode === 'exam');

    foreach ($rows as &$row) {

        // ---------- AVAILABILITY LOGIC ----------
        $startAtRaw = $row['start_at'] ?? null;
        $endAtRaw   = $row['end_at']   ?? null;

        $endNullish = (!$endAtRaw || $endAtRaw === '0000-00-00 00:00:00');

        // If start_at and end_at are exactly same → always available
        $alwaysAvailable = ($startAtRaw !== null && $startAtRaw !== '' && $startAtRaw === $endAtRaw);

        $endNotFinished = false;
        if ($endNullish) {
            $endNotFinished = true;
        } else {
            try {
                $endDT = new \DateTime($endAtRaw);
                $endNotFinished = ($endDT >= $now);
            } catch (\Exception $e) {
                $endNotFinished = false;
            }
        }

        $isAvailable = $skipExpiryCheck || $alwaysAvailable || $endNotFinished;

        // ❌ Skip expired quizzes (regular mode only)
        if (! $isAvailable) {
            continue;
        }

        if ($mode === 'exam') {
            $row['is_exam_quiz'] = 1;
        }

        // ---------- Existing post-processing ----------

        // total / attempted / remaining
        $total = (int) ($row['total_students'] ?? 0);
        $att   = (int) ($row['attempted_students'] ?? 0);
        $row['remaining_students'] = max(0, $total - $att);

        // duration (seconds -> minutes)
        $secs = (int) ($row['time_limit_sec'] ?? 0);
        $row['duration_minutes'] = $secs > 0 ? (int) ceil($secs / 60) : 0;

        // remaining_time + status
        $remainingStr = '-';
        $status       = 'live';

        if ($alwaysAvailable) {
            // Forever quiz: treat as live, no countdown
            $remainingStr = '-';
            $status       = 'live';
        } elseif (! $endNullish) {
            try {
                $end = new \DateTime($endAtRaw);
                $diffSec = $end->getTimestamp() - $now->getTimestamp();

                if ($diffSec <= 0) {
                    $remainingStr = 'Ended';
                    $status       = 'closed';
                } else {
                    $status = 'live';
                    $days  = intdiv($diffSec, 86400);
                    $rem   = $diffSec % 86400;
                    $hours = intdiv($rem, 3600);
                    $rem   = $rem % 3600;
                    $mins  = intdiv($rem, 60);

                    $parts = [];
                    if ($days > 0)  $parts[] = $days . 'd';
                    if ($hours > 0) $parts[] = $hours . 'h';
                    if ($mins > 0 || empty($parts)) $parts[] = $mins . 'm';

                    $remainingStr = implode(' ', $parts);
                }
            } catch (\Exception $e) {
                $remainingStr = '-';
            }
        }

        $row['remaining_time'] = $remainingStr;
        $row['quiz_status']    = $status;

        $availableRows[] = $row;
    }
    unset($row);

    return $this->response->setJSON($availableRows);
}


}
