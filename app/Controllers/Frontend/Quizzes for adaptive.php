<?php
namespace App\Controllers\Frontend;
use App\Controllers\BaseController;
use Config\Database;
use App\Libraries\AiQuizEngine;

class Quizzes extends BaseController
{
    protected $db; protected $session;

    public function __construct()
    {
        $this->db = db_connect();
        $this->session = session();
        helper(['form','url','text', 'wifi']);
    }



public function start(int $quizId)
{
    $quizId = (int) $quizId;

    [$studentId, $isImpersonation] = $this->resolveStudent($quizId);
    $quiz       = $this->loadQuizOrFail($quizId);
    $this->validateQuizAvailability($quiz);

    $campusId   = $this->resolveCampusOrFail($studentId);
    $this->enforceWifiIfRequired($quiz, $campusId);

    [$attempt, $currentLevel] = $this->resolveAttempt($quiz, $studentId);

    $questions = $this->loadOrAssignQuestions($quiz, $attempt, $currentLevel);
    $saved     = $this->loadSavedAnswers($attempt->attempt_id, $questions);

    return $this->renderQuiz(
        $quiz,
        $attempt,
        $questions,
        $saved
    );
}

private function resolveCampusOrFail(int $studentId): int
{
    $campusId = (int) ($this->session->get('member_campusid') ?? 0);

    if ($campusId > 0) {
        return $campusId;
    }

    $row = $this->db->table('students')
        ->select('campus_id')
        ->where('student_id', $studentId)
        ->get()
        ->getRow();

    if (!$row || (int)$row->campus_id <= 0) {
        throw new \RuntimeException('Campus not configured for your account.');
    }

    return (int)$row->campus_id;
}


protected function enforceWifiIfRequired(object $quiz, int $campusId): void
{
    if (empty($quiz->wifi_only) || (int)$quiz->wifi_only !== 1) {
        return; // no restriction
    }

    $clientIp = (string) $this->request->getIPAddress();

    if (! $this->isIpAllowedForCampus($campusId, $clientIp)) {
        throw new \RuntimeException(
            'You can only attempt this quiz from school Wi-Fi.'
        );
    }
}


protected function enforceQuizTimingOrFail(object $quiz): void
{
    $now     = date('Y-m-d H:i:s');
    $startAt = $quiz->start_at ?? null;
    $endAt   = $quiz->end_at ?? null;

    $hasStart = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
    $hasEnd   = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';

    // If start == end → forever
    if ($hasStart && $hasEnd && $startAt === $endAt) {
        return;
    }

    if ($hasStart && $startAt > $now) {
        throw new \RuntimeException('Quiz has not started yet.');
    }

    if ($hasEnd && $endAt < $now) {
        throw new \RuntimeException('Quiz has ended.');
    }
}


private function enforceWifiRule(object $quiz, int $campusId): void
{
    if ((int)$quiz->wifi_only !== 1) {
        return;
    }

    $clientIp = $this->request->getIPAddress();

    if (!$this->isIpAllowedForCampus($campusId, $clientIp)) {
        throw new \RuntimeException('You can only attempt this quiz from school Wi-Fi.');
    }
}


private function resolveEffectiveStudentIdOrFail(int $quizId): int
{
    $request = $this->request;
    $session = $this->session;

    $tokenStr = trim((string) ($request->getGet('impersonate_token')
        ?? $request->getGet('impersonate')
        ?? ''));

    if ($tokenStr !== '') {
        $row = $this->db->table('quiz_impersonation_tokens')
            ->where('token', $tokenStr)
            ->get()
            ->getRowArray();

        if (!$row) {
            throw new \RuntimeException('Invalid quiz link.');
        }

        if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            throw new \RuntimeException('Quiz link has expired.');
        }

        if (isset($row['quiz_id']) && (int)$row['quiz_id'] !== $quizId) {
            throw new \RuntimeException('Quiz link does not match this quiz.');
        }

        if ((int)$row['student_id'] <= 0) {
            throw new \RuntimeException('Quiz link is not attached to a student.');
        }

        $session->set('impersonate', true);
        $session->set('impersonated_student_id', (int)$row['student_id']);

        return (int)$row['student_id'];
    }

    // Normal login
    $sid = (int) ($request->getGet('sid') ?? 0);

    if ($sid > 0) {
        return $sid;
    }

    $sid = (int) ($session->get('student_id') ?? 0);

    if ($sid <= 0) {
        throw new \RuntimeException('Access denied.');
    }

    return $sid;
}


private function resolveQuizOrFail(int $quizId): object
{
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $quizId)
        ->where('is_published', 1)
        ->get()
        ->getRow();

    if (!$quiz) {
        throw new \RuntimeException('Quiz not available.');
    }

    $now = date('Y-m-d H:i:s');

    if (!empty($quiz->start_at) && $quiz->start_at > $now) {
        throw new \RuntimeException('Quiz has not started yet.');
    }

    if (!empty($quiz->end_at) && $quiz->end_at < $now) {
        throw new \RuntimeException('Quiz has ended.');
    }

    return $quiz;
}


private function resolveStudent(int $quizId): array
{
    $token = trim((string) $this->request->getGet('impersonate_token'));

    if ($token !== '') {
        $row = $this->db->table('quiz_impersonation_tokens')
            ->where('token', $token)
            ->where('quiz_id', $quizId)
            ->where('used', 0)
            ->get()
            ->getRow();

        if (! $row) {
            throw new \RuntimeException('Invalid or expired quiz link.');
        }

        $this->db->table('quiz_impersonation_tokens')
            ->where('id', $row->id)
            ->update([
                'used'   => 1,
                'use_ip'=> $this->request->getIPAddress(),
            ]);

        return [(int)$row->student_id, true];
    }

    $studentId = (int) ($this->session->get('student_id') ?? 0);
    if ($studentId <= 0) {
        redirect()->to('/student/login')->send();
        exit;
    }

    return [$studentId, false];
}


private function loadQuizOrFail(int $quizId)
{
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $quizId)
        ->where('is_published', 1)
        ->get()
        ->getRow();

    if (! $quiz) {
        throw new \RuntimeException('Quiz not available.');
    }

    return $quiz;
}

private function validateQuizAvailability(object $quiz): void
{
    $now     = date('Y-m-d H:i:s');
    $startAt = $quiz->start_at ?? null;
    $endAt   = $quiz->end_at   ?? null;

    $hasStart = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
    $hasEnd   = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';

    // SPECIAL CASE:
    // If start_at == end_at → quiz is always available
    $isForever = $hasStart && $hasEnd && ($startAt === $endAt);
    if ($isForever) {
        return;
    }

    if ($hasStart && $startAt > $now) {
        throw new \RuntimeException('Quiz has not started yet.');
    }

    // 🚨 END TIME CHECK
    // ❗ DO NOT block adaptive quizzes
    if (
        $hasEnd &&
        $endAt < $now &&
        (int)($quiz->is_adaptive ?? 0) !== 1
    ) {
        throw new \RuntimeException('Quiz has ended.');
    }
}

private function resolveAttempt(object $quiz, int $studentId): array
{
    $isAdaptive = ((int)$quiz->is_adaptive === 1);

    /* ----------------------------------------
     * 1) Resume existing IN-PROGRESS attempt
     * ---------------------------------------- */
    $builder = $this->db->table('quiz_attempts')
        ->where([
            'quiz_id'    => $quiz->quiz_id,
            'student_id' => $studentId,
            'status'     => 'in_progress',
        ]);

    if ($isAdaptive) {
        $builder->where('level_id IS NOT NULL', null, false);
    }

    $attempt = $builder
        ->orderBy('attempt_id', 'DESC')
        ->get()
        ->getRow();

    if ($attempt) {
        return [$attempt, (int)$attempt->level_id];
    }

    /* ----------------------------------------
     * 2) Determine NEXT attempt_no safely
     * ---------------------------------------- */
    $maxAttemptNo = (int) $this->db->table('quiz_attempts')
        ->selectMax('attempt_no')
        ->where([
            'quiz_id'    => $quiz->quiz_id,
            'student_id' => $studentId,
        ])
        ->get()
        ->getRow()
        ->attempt_no;

    $nextAttemptNo = $maxAttemptNo + 1;

    /* ----------------------------------------
     * 3) Resolve level (adaptive only)
     * ---------------------------------------- */
    $levelId = null;

    if ($isAdaptive) {
        $level = $this->db->table('quiz_levels')
            ->where('quiz_id', $quiz->quiz_id)
            ->orderBy('level_no', 'ASC')
            ->get()
            ->getRow();

        if (! $level) {
            throw new \RuntimeException('Adaptive quiz has no levels configured.');
        }

        $levelId = (int) $level->level_id;
    }

    /* ----------------------------------------
     * 4) Create NEW attempt (SAFE)
     * ---------------------------------------- */
    $this->db->table('quiz_attempts')->insert([
        'quiz_id'    => $quiz->quiz_id,
        'student_id' => $studentId,
        'attempt_no' => $nextAttemptNo,
        'level_id'   => $levelId,
        'status'     => 'in_progress',
        'started_at' => date('Y-m-d H:i:s'),
        'client_ip'  => $this->request->getIPAddress(),
    ]);

    $attemptId = (int) $this->db->insertID();

    if ($attemptId <= 0) {
        throw new \RuntimeException('Failed to create quiz attempt.');
    }

    $attempt = $this->db->table('quiz_attempts')
        ->where('attempt_id', $attemptId)
        ->get()
        ->getRow();

    return [$attempt, $levelId];
}

private function loadOrAssignQuestions(object $quiz, object $attempt, ?int $levelId): array
{
    /**
     * 1) Resume if questions already assigned
     */
    $existing = $this->db->table('quiz_attempt_questions')
        ->where('attempt_id', $attempt->attempt_id)
        ->countAllResults();

    if ($existing > 0) {
        return $this->db->table('quiz_attempt_questions qa')
            ->select('
                qa.question_id,
                qa.display_order,
                qa.marks,
                qa.question_type,
                q.question,
                q.question_type AS qb_question_type,
                q.question_image,
                q.question_media,
                q.question_image_alt,
                q.correct_option,
                q.option_a,
                q.option_b,
                q.option_c,
                q.option_d,
                q.options_json,
                q.is_drag
            ')
            ->join('qb_questions q', 'q.id = qa.question_id', 'left')
            ->where('qa.attempt_id', $attempt->attempt_id)
            ->orderBy('qa.display_order', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * 2) Fetch questions (adaptive vs normal)
     */
    $builder = $this->db->table('quiz_questions qq')
        ->select('
            qq.question_id,
            qq.order_index,
            q.question_type,
            q.question,
            q.question_image,
            q.question_media,
            q.question_image_alt,
            q.correct_option,
            q.option_a,
            q.option_b,
            q.option_c,
            q.option_d,
            q.options_json,
            q.is_drag
        ')
        ->join('qb_questions q', 'q.id = qq.question_id', 'left')
        ->where('qq.quiz_id', $quiz->quiz_id);

    if ((int)$quiz->is_adaptive === 1) {
        if (!$levelId) {
            throw new \RuntimeException('Adaptive level missing.');
        }
        $builder->where('qq.level_id', $levelId);
    } else {
        if ((int)$quiz->questions_count > 0) {
            $builder->limit((int)$quiz->questions_count);
        }
    }

    $questions = $builder
        ->orderBy('qq.order_index', 'ASC')
        ->get()
        ->getResult();

    if (empty($questions)) {
        throw new \RuntimeException('No questions found for this quiz.');
    }

    /**
     * 3) Persist per-attempt questions
     */
    $batch = [];
    $order = 1;

    foreach ($questions as $q) {
        $batch[] = [
            'attempt_id'    => $attempt->attempt_id,
            'quiz_id'       => $quiz->quiz_id,
            'question_id'   => (int)$q->question_id,
            'display_order' => $order++,
            'marks'         => (float)($quiz->per_question_marks ?? 1),
            'question_type' => $q->question_type ?? 'mcq_single',
        ];
    }

    $this->db->table('quiz_attempt_questions')->insertBatch($batch);

    return $questions;
}

private function loadSavedAnswers(int $attemptId, array $questions): array
{
    if (empty($questions)) return [];

    $ids = array_map(fn($q) => (int)$q->question_id, $questions);

    $rows = $this->db->table('quiz_attempt_answers')
        ->where('attempt_id',$attemptId)
        ->whereIn('question_id',$ids)
        ->get()
        ->getResultArray();

    $out = [];
    foreach ($rows as $r) {
        $out[(int)$r['question_id']] = $r;
    }

    return $out;
}


private function renderQuiz($quiz, $attempt, $questions, $saved)
{
    return view('frontend/quizzes/template1', [
        'quiz'           => $quiz,
        'attemptId'      => $attempt->attempt_id,
        'qq'             => $questions,
        'savedAnswers'   => $saved,
        'totalQuestions' => count($questions),
        'timeLimitSec'   => (int)$quiz->time_limit_sec,
    ]);
}

public function reviewAttempt($attemptId)
{
    $attemptId = (int) $attemptId;

    // Optional: permission check
    if (function_exists('check_permission')) {
        check_permission('admin-view-quiz-result');
    }

    // 1) Load attempt
    $attempt = $this->db->table('quiz_attempts')
        ->where('attempt_id', $attemptId)
        ->get()
        ->getRow();

    if (! $attempt) {
        return redirect()->back()->with('error', 'Attempt not found.');
    }

    // 2) Load quiz
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $attempt->quiz_id)
        ->get()
        ->getRow();

    if (! $quiz) {
        return redirect()->back()->with('error', 'Quiz not found.');
    }

    // 3) Load questions (same join you use in start())
    $qqTable = 'quiz_questions';
    $qbTable = 'qb_questions';

    $questions = $this->db->table("$qqTable qq")
        ->select("
            qq.question_id,
            qq.marks,
            qq.order_index,
            q.question_type,
            q.question,
            q.correct_option,
            q.options_json,
            q.option_a, q.option_b, q.option_c, q.option_d,
            q.answer_text
        ")
        ->join("$qbTable q", 'q.id = qq.question_id', 'left')
        ->where('qq.quiz_id', $quiz->quiz_id)
        ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false)
        ->get()
        ->getResult();

    // 4) Load answers for this attempt
    $answers = $this->db->table('quiz_attempt_answers')
        ->where('attempt_id', $attemptId)
        ->get()
        ->getResult();

    $ansByQ = [];
    foreach ($answers as $a) {
        $ansByQ[$a->question_id] = $a;
    }

    // 5) Build stats: total, correct, wrong, unattempted, total_marks
    $stats = [
        'total_questions' => count($questions),
        'correct'         => 0,
        'wrong'           => 0,
        'unattempted'     => 0,
        'total_marks'     => 0,
    ];

    foreach ($questions as $qrow) {
        $stats['total_marks'] += (float) ($qrow->marks ?? 1);

        $given = $ansByQ[$qrow->question_id] ?? null;

        if (! $given || (
            ($given->selected_option  === null || $given->selected_option  === '') &&
            ($given->selected_options === null || $given->selected_options === '') &&
            ($given->answer_text      === null || $given->answer_text      === '')
        )) {
            $stats['unattempted']++;
        } else {
            if ((int) ($given->is_correct ?? 0) === 1) {
                $stats['correct']++;
            } else {
                $stats['wrong']++;
            }
        }
    }

    $percentage = null;
    if ($stats['total_marks'] > 0 && $attempt->score_obtained !== null) {
        $percentage = round(($attempt->score_obtained / $stats['total_marks']) * 100, 2);
    }

    // 6) Render SAME review view you use in frontend
    return view('frontend/quizzes/review_attempt', [
        'quiz'        => $quiz,
        'attempt'     => $attempt,
        'qq'          => $questions,
        'ansByQ'      => $ansByQ,
        'stats'       => $stats,
        'percentage'  => $percentage,
        'isAdminReview' => true,   // optional flag if you want to tweak UI
    ]);
}


public function index()
{
    $studentId = (int)$this->session->get('student_id');
    $classId   = (int)$this->session->get('student_class_id'); // adapt to your mapping

    // Unattempted quizzes
    // Rule:
    //  - If start_at = end_at  → ignore time, always available
    //  - Else                  → respect start/end window
    $unattempted = $this->db->query("
        SELECT q.*
        FROM quizzes q
        LEFT JOIN quiz_attempts qa
          ON qa.quiz_id    = q.quiz_id
         AND qa.student_id = ?
         AND qa.status     = 'submitted'
        WHERE q.cls_sec_id   = ?
          AND q.is_published = 1
          AND (
                (q.start_at = q.end_at)
                OR (
                    (q.start_at IS NULL OR q.start_at <= NOW())
                    AND (q.end_at IS NULL   OR q.end_at   >= NOW())
                )
              )
        GROUP BY q.quiz_id
        HAVING COUNT(qa.attempt_id) < q.max_attempts
        ORDER BY q.start_at DESC, q.quiz_id DESC
    ", [$studentId, $classId])->getResult();

    // Attempted quizzes (unchanged)
    $attempted = $this->db->query("
        SELECT 
          q.title,
          q.quiz_id,
          qa.attempt_id,
          qa.attempt_no,
          qa.score_obtained,
          qa.status,
          qa.submitted_at
        FROM quiz_attempts qa
        JOIN quizzes q ON q.quiz_id = qa.quiz_id
        WHERE qa.student_id = ?
        ORDER BY qa.submitted_at DESC
    ", [$studentId])->getResult();

    return view('frontend/quizzes/index', compact('unattempted', 'attempted'));
}

   
private function columnExists(string $table, string $column): bool
{
    $q = $this->db->query("SHOW COLUMNS FROM `$table` LIKE ?", [$column]);
    return $q && $q->getNumRows() > 0;
}



public function practice($quizId)
{
    $quizId    = (int) $quizId;
    $studentId = (int) ($this->session->get('student_id') ?? 0);

    if ($quizId <= 0 || $studentId <= 0) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Invalid quiz or student not logged in.');
    }

    // 1) Load quiz
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $quizId)
        ->where('is_published', 1)
        ->get()
        ->getRow();

    if (! $quiz) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz not available.');
    }

    // 2) Time rules (same as start)
    $now = date('Y-m-d H:i:s');

    $startAt = $quiz->start_at ?? null;
$endAt   = $quiz->end_at ?? null;

$hasStart  = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
$hasEnd    = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';
$isForever = ($hasStart && $hasEnd && $startAt === $endAt);

if (! $isForever) {
    if ($hasStart && $startAt > $now) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz has not started yet.');
    }
    if ($hasEnd && $endAt < $now) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz has ended.');
    }
}

    // 3) Load questions
    $qqTable = 'quiz_questions';
    $qbTable = 'qb_questions';

    $sel = [
        'qq.question_id',
        $this->columnExists($qqTable, 'order_index') ? 'qq.order_index' : 'NULL AS order_index',
        $this->columnExists($qqTable, 'marks')       ? 'qq.marks'       : '1 AS marks',
        'q.question_type',
        'q.question',
        'q.correct_option',
        'q.option_a',
        'q.option_b',
        'q.option_c',
        'q.option_d',
        'q.options_json',
        'q.answer_text'

    ];

   

    $qq = $this->db->table("$qqTable qq")
        ->select(implode(', ', $sel))
        ->join("$qbTable q", 'q.id = qq.question_id', 'left')
        ->where('qq.quiz_id', $quizId)
        ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false)
        ->get()
        ->getResult();

    if (empty($qq)) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'No questions found for this quiz.');
    }

    // 4) Apply question limit
    $limit = (int) ($quiz->questions_count ?? 0);
    if ($limit > 0 && count($qq) > $limit) {
        shuffle($qq);
        $qq = array_slice($qq, 0, $limit);
    }

    // 5) Shuffle if enabled
    if (!empty($quiz->shuffle_questions)) {
        shuffle($qq);
    }

    // 6) Render PRACTICE view (NO attemptId)
    return view('frontend/quizzes/quiz_practice_play', [
        'quiz'       => $quiz,
        'qq'         => $qq,
        'isPractice' => true,
    ]);
}




// public function start($quizId)
// {
//     log_message('debug', "=== START METHOD CALLED ===");
//     log_message('debug', "Received quizId parameter: {$quizId}");
//     log_message('debug', "Request URI: " . $this->request->getUri()->getPath());
//     log_message('debug', "GET parameters: " . print_r($this->request->getGet(), true));
//     log_message('debug', "Session student_id: " . ($this->session->get('student_id') ?? 'not set'));
    
//     $quizId  = (int) $quizId;
//     $session = $this->session;
//     $request = $this->request;

//     // =====================================================
//     // 1) Resolve student via token OR normal student login
//     // =====================================================
//     $tokenStr = trim((string) ($request->getGet('impersonate_token') ?? $request->getGet('impersonate') ?? ''));

//     $isImpersonation    = false;
//     $effectiveStudentId = 0;

//     if ($tokenStr !== '') {
//         // --- Validate token row ---
//         $row = $this->db->table('quiz_impersonation_tokens')
//             ->where('token', $tokenStr)
//             ->get()
//             ->getRowArray();

//         if (! $row) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Invalid quiz link.');
//         }

//         // Check token expiry
//         if (! empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz link has expired.');
//         }

//         // Optional: single-use check
//         if (isset($row['used']) && (int) $row['used'] === 1) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz link has already been used.');
//         }

//         // Ensure the token belongs to this quiz (if quiz_id column exists)
//         if (isset($row['quiz_id']) && (int) $row['quiz_id'] !== $quizId) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz link does not match this quiz.');
//         }

//         $effectiveStudentId = (int) ($row['student_id'] ?? 0);
//         if ($effectiveStudentId <= 0) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz link is not attached to any student.');
//         }

//         $isImpersonation = true;

//         // Mark impersonation in session
//         $session->set('impersonate', true);
//         $session->set('impersonated_student_id', $effectiveStudentId);

//         // Mark token as used + IP (audit)
//         $updateData = [
//             'used'   => 1,
//             'use_ip' => $request->getIPAddress(),
//         ];
//         $this->db->table('quiz_impersonation_tokens')
//             ->where('id', $row['id'])
//             ->update($updateData);
//     } else {
//         // ===== Normal student / parent flow =====
//         $sidParam = (int) $request->getGet('sid');
//         log_message('debug', "sid parameter from URL: {$sidParam}");
        
//         if ($sidParam > 0) {
//             $effectiveStudentId = $sidParam;
//         } else {
//             // Fallback: student logged in as themselves
//             $effectiveStudentId = (int) ($session->get('student_id') ?? 0);
//         }

//         if ($effectiveStudentId <= 0) {
//             return redirect()->to(base_url('student/login'))
//                 ->with('error', 'Access denied (not logged in).');
//         }
        
//         log_message('debug', "Effective student ID determined: {$effectiveStudentId}");
//         log_message('debug', "Is impersonation: " . ($isImpersonation ? 'yes' : 'no'));
//     }

//     if ($quizId <= 0) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Invalid quiz.');
//     }

//     // ==========================================
//     // 2) Load quiz + guards
//     // ==========================================
//     $quiz = $this->db->table('quizzes')
//         ->where('quiz_id', $quizId)
//         ->get()
//         ->getRow();

//     if (! $quiz || ! $quiz->is_published) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Quiz not available.');
//     }

//     $now     = date('Y-m-d H:i:s');
//     $startAt = $quiz->start_at ?? null;
//     $endAt   = $quiz->end_at   ?? null;

//     // Normalised flags
//     $hasStart = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
//     $hasEnd   = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';

//     // Special rule: if start_at and end_at are BOTH set and equal,
//     // ignore time constraints (quiz is always available).
//     $isForever = $hasStart && $hasEnd && ($startAt === $endAt);

//     if (! $isForever) {
//         if ($hasStart && $startAt > $now) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz has not started yet.');
//         }

//         if ($hasEnd && $endAt < $now) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz has ended.');
//         }
//     }

// $isAdaptive = ((int)($quiz->is_adaptive ?? 0) === 1);
//     // ==========================================
//     // 3) Resolve campus
//     // ==========================================
//     $campusId = (int) ($session->get('member_campusid') ?? 0);
//     if ($campusId <= 0) {
//         $row = $this->db->table('students')
//             ->select('campus_id')
//             ->where('student_id', $effectiveStudentId)
//             ->get()
//             ->getRow();
//         $campusId = $row ? (int) $row->campus_id : 0;
//     }
//     if ($campusId <= 0) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Campus not configured for your account.');
//     }

//     // ==========================================
//     // 4) Wi-Fi restriction
//     // ==========================================
//     $clientIp = (string) $request->getIPAddress();
//     if (! empty($quiz->wifi_only) && (int) $quiz->wifi_only === 1) {
//         if (! $this->isIpAllowedForCampus($campusId, $clientIp)) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'You can only attempt this quiz from school Wi-Fi.');
//         }
//     }

//     // ==========================================
//     // 5) Attempt limit (based on effectiveStudentId)
//     // ==========================================
//     $prevCount = $this->db->table('quiz_attempts')
//         ->where([
//             'quiz_id'    => $quizId,
//             'student_id' => $effectiveStudentId,
//         ])
//         ->whereIn('status', ['submitted','completed']) 
//         ->countAllResults();

//     $attemptNo = $prevCount + 1;

//     if ((int) $quiz->max_attempts > 0 && $attemptNo > (int) $quiz->max_attempts) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Max attempts reached.');
//     }

//     // ==========================================
//     // 6) Create attempt
//     // ==========================================
//    // $activeKey = $quizId . '-' . $effectiveStudentId;
//     $activeKey = $quizId . '-' . $effectiveStudentId . '-L' . ($currentLevelId ?? 1);

//     // Auto-submit very old in_progress attempts
//     $timeLimitSec = (int) ($quiz->time_limit_sec ?? 0);
//     $graceSec     = 300; // 5 min grace
//     if ($timeLimitSec > 0) {
//         $expiryTs = date('Y-m-d H:i:s', time() - ($timeLimitSec + $graceSec));

//         // Auto-submit very old in_progress attempts
//         $this->db->table('quiz_attempts')
//             ->where([
//                 'quiz_id'            => $quizId,
//                 'student_id'         => $effectiveStudentId,
//                 'status'             => 'in_progress',
//                 'active_attempt_key' => $activeKey,
//             ])
//             ->where('started_at <', $expiryTs)
//             ->update([
//                 'status'             => 'submitted', // Changed from 'abandoned'
//                 'submitted_at'       => date('Y-m-d H:i:s'),
//                 'active_attempt_key' => null,
//             ]);
//     }

//     log_message('debug', "Checking for existing in_progress attempts with key: {$activeKey}");
    
//     // 1) Try to RESUME existing in_progress attempt
//     $attemptRow = $this->db->table('quiz_attempts')
//         ->where([
//             'quiz_id'            => $quizId,
//             'student_id'         => $effectiveStudentId,
//             'status'             => 'in_progress',
//             'active_attempt_key' => $activeKey,
//         ])
//         ->orderBy('attempt_id', 'DESC')
//         ->get()
//         ->getRow();

//     log_message('debug', "Found attempt ID: " . ($attemptRow->attempt_id ?? 'none') . 
//                " with status: " . ($attemptRow->status ?? 'none'));

//     if ($attemptRow) {
//         // ✅ resume
//         $attemptId = (int) $attemptRow->attempt_id;
//         $attemptNo = (int) $attemptRow->attempt_no;
//         log_message('debug', "Resuming existing attempt {$attemptId}");
//     } else {
//         // 2) No active attempt -> CREATE new one safely (unique key protection)
//         // Count only finished attempts (so refresh doesn't create attempt #2)
//         $prevCount = $this->db->table('quiz_attempts')
//             ->where(['quiz_id'=>$quizId, 'student_id'=>$effectiveStudentId])
//             ->whereIn('status', ['submitted','completed'])
//             ->countAllResults();

//         $attemptNo = $prevCount + 1;

//         if ((int) $quiz->max_attempts > 0 && $attemptNo > (int) $quiz->max_attempts) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Max attempts reached.');
//         }

//         try {
//             $insertData = [
//                 'quiz_id'            => $quizId,
//                 'student_id'         => $effectiveStudentId,
//                 'attempt_no'         => $attemptNo,
//                 'started_at'         => $now,
//                 'status'             => 'in_progress', // Always set explicitly
//                 'client_ip'          => $clientIp,
//                 'active_attempt_key' => $activeKey,
//             ];
            
//             log_message('debug', "Creating new attempt with data: " . print_r($insertData, true));
            
//             $this->db->table('quiz_attempts')->insert($insertData);
//             $attemptId = (int) $this->db->insertID();
            
//             log_message('debug', "Created new attempt ID: {$attemptId}");
            
//         } catch (\Throwable $e) {
//             log_message('error', "Error creating attempt: " . $e->getMessage());
            
//             // If UNIQUE(active_attempt_key) blocked a duplicate insert, RESUME the existing attempt
//             $attemptRow = $this->db->table('quiz_attempts')
//                 ->where([
//                     'quiz_id'            => $quizId,
//                     'student_id'         => $effectiveStudentId,
//                     'active_attempt_key' => $activeKey,
//                 ])
//                 ->whereIn('status', ['in_progress', 'submitted']) // Check both statuses
//                 ->orderBy('attempt_id', 'DESC')
//                 ->get()
//                 ->getRow();

//             if ($attemptRow) {
//                 if ($attemptRow->status === 'submitted') {
//                     // If already submitted, we need to create a new attempt
//                     log_message('debug', "Found submitted attempt, will create new one");
//                     $attemptRow = null;
                    
//                     // Increment attempt number for the new attempt
//                     $prevCount++;
//                     $attemptNo = $prevCount + 1;
                    
//                     // Create new attempt
//                     $this->db->table('quiz_attempts')->insert([
//                         'quiz_id'            => $quizId,
//                         'student_id'         => $effectiveStudentId,
//                         'attempt_no'         => $attemptNo,
//                         'started_at'         => $now,
//                         'status'             => 'in_progress',
//                         'client_ip'          => $clientIp,
//                         'active_attempt_key' => $activeKey,
//                     ]);
//                     $attemptId = (int) $this->db->insertID();
//                 } else {
//                     $attemptId = (int) $attemptRow->attempt_id;
//                     $attemptNo = (int) $attemptRow->attempt_no;
//                     log_message('debug', "Resuming attempt from catch block: {$attemptId}");
//                 }
//             } else {
//                 throw $e; // real error
//             }
//         }
//     }

//     // Double-check that attempt has a valid status
//     if (!isset($attemptId)) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Failed to create or retrieve quiz attempt.');
//     }

//     // Verify the attempt status is valid
//     $finalAttemptCheck = $this->db->table('quiz_attempts')
//         ->select('status')
//         ->where('attempt_id', $attemptId)
//         ->get()
//         ->getRow();
    
//     if (!$finalAttemptCheck) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Quiz attempt not found.');
//     }
    
//     if (empty($finalAttemptCheck->status) || !in_array($finalAttemptCheck->status, ['in_progress', 'submitted', 'completed'])) {
//         log_message('error', "Invalid attempt status: " . ($finalAttemptCheck->status ?? 'NULL'));
//         // Force fix the status
//         $this->db->table('quiz_attempts')
//             ->where('attempt_id', $attemptId)
//             ->update(['status' => 'in_progress']);
//     }

//     // ==========================================
//     // 7) Check if this attempt already has questions assigned
//     // ==========================================
//     $qq = []; // This will hold our questions

//     // Check if questions already exist for this attempt
//     $existingQuestions = $this->db->table('quiz_attempt_questions')
//         ->where('attempt_id', $attemptId)
//         ->countAllResults();

//     if ($existingQuestions > 0) {
//         // ==========================================
//         // 7a) RESUME: Load the SAME questions from previous attempt
//         // ==========================================
//         $qqTable = 'quiz_attempt_questions';
//         $qbTable = 'qb_questions';
        
//         $sel = [
//             'qa.question_id',
//             'qa.display_order AS order_index',
//             'qa.marks',
//             'q.question_type',
//             'q.question',
//             'q.question_image',     
//             'q.question_media',  
//             'q.question_image_alt', 
//             'q.correct_option',
//             'q.option_a',
//             'q.option_b',
//             'q.option_c',
//             'q.option_d',
//             'q.options_json',
//             'q.is_drag',
//         ];
        
//         $builder = $this->db->table("$qqTable qa")
//             ->select(implode(', ', $sel))
//             ->join("$qbTable q", 'q.id = qa.question_id', 'left')
//             ->where('qa.attempt_id', $attemptId)
//             ->orderBy('qa.display_order', 'ASC');
        
//         $res = $builder->get();
//         if ($res !== false) {
//             $qq = $res->getResult();
//         }
        
//         log_message('debug', "Resuming attempt {$attemptId}: Found " . count($qq) . " existing questions");
//     } else {
//         // ==========================================
//         // 7b) NEW ATTEMPT: Select random questions
//         // ==========================================
//         $qqTable = 'quiz_questions';
//         $qbTable = 'qb_questions';
        
//         $sel = [
//             'qq.question_id',
//             $this->columnExists($qqTable, 'order_index') ? 'qq.order_index' : 'NULL AS order_index',
//             $this->columnExists($qqTable, 'marks')       ? 'qq.marks'       : '1 AS marks',
//             'q.question_type',
//             'q.question',
//             'q.question_image',     
//             'q.question_media',  
//             'q.question_image_alt', 
//             'q.correct_option',
//             'q.option_a',
//             'q.option_b',
//             'q.option_c',
//             'q.option_d',
//             'q.options_json',
//             'q.is_drag',
//         ];
        
//         $builder = $this->db->table("$qqTable qq")
//             ->select(implode(', ', $sel))
//             ->join("$qbTable q", 'q.id = qq.question_id', 'left')
//             ->where('qq.quiz_id', $quizId)
//             ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false);
        
//         $res = $builder->get();
//         if ($res === false) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Unable to load quiz questions.');
//         }
        
//         $allQuestions = $res->getResult();
//         if (empty($allQuestions)) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'No questions found for this quiz.');
//         }
        

//         $currentLevel = 1;
// $lastPassed   = 0;

// if ($isAdaptive) {
//     $attemptMeta = $this->db->table('quiz_attempts')
//         ->select('current_level, last_passed_level')
//         ->where('attempt_id', $attemptId)
//         ->get()
//         ->getRow();

//     if ($attemptMeta) {
//         $currentLevel = max(1, (int)$attemptMeta->current_level);
//         $lastPassed   = (int)$attemptMeta->last_passed_level;
//     }
// }
// if (! $isAdaptive) {
//     // ✅ KEEP ALL YOUR EXISTING LOGIC AS-IS

//         // ==========================================
//         // 8) Compute per-type counts & target total
//         // ==========================================
//         $normalizeType = function (?string $t): string {
//             $t = strtolower(trim((string) $t));
//             if ($t === '') return '';
            
//             switch ($t) {
//                 case 'mcq':
//                 case 'mcq_single':
//                 case 'single':
//                     return 'mcq_single';
                    
//                 case 'mcq_multi':
//                 case 'mcq_multiple':
//                 case 'multiple':
//                     return 'mcq_multi';
                    
//                 case 'tf':
//                 case 'true_false':
//                 case 'true/false':
//                     return 'tf';
                    
//                 case 'fill':
//                 case 'fill_blank':
//                 case 'fill_blanks':
//                 case 'fib':
//                     return 'fill';
                    
//                 case 'short':
//                 case 'short_answer':
//                     return 'short';
                    
//                 case 'match':
//                 case 'matching':
//                 case 'match_the_column':
//                     return 'match';
                    
//                 default:
//                     return $t; // fallback "other"
//             }
//         };
        
//         // Required counts from quiz config (per type)
//         $typeCounts = [
//             'mcq_single' => (int) ($quiz->count_mcq_single ?? 0),
//             'mcq_multi'  => (int) ($quiz->count_mcq_multi  ?? 0),
//             'tf'         => (int) ($quiz->count_tf         ?? 0),
//             'fill'       => (int) ($quiz->count_fill       ?? 0),
//             'short'      => (int) ($quiz->count_short      ?? 0),
//             'match'      => (int) ($quiz->count_match      ?? 0),
//         ];
//         $sumTypeCounts = array_sum($typeCounts);
        
//         // Target total: questions_count if set, else fall back
//         $questionsCountCfg = (int) ($quiz->questions_count ?? 0);
//         if ($questionsCountCfg > 0) {
//             $targetTotal = min($questionsCountCfg, count($allQuestions));
//         } else {
//             // If no questions_count configured, target = sum of types (if >0) or all
//             $targetTotal = ($sumTypeCounts > 0)
//                 ? min($sumTypeCounts, count($allQuestions))
//                 : count($allQuestions);
//         }
        
//         // Group all questions by normalized type
//         $byType = [];
//         foreach ($allQuestions as $qRow) {
//             $key = $normalizeType($qRow->question_type ?? '');
//             if ($key === '') {
//                 $key = 'other';
//             }
//             if (! isset($byType[$key])) {
//                 $byType[$key] = [];
//             }
//             $byType[$key][] = $qRow;
//         }
        
//         $selectedQuestions = [];
        
//         if ($sumTypeCounts > 0) {
//             // ==========================================
//             // 8a) Per-type selection
//             // ==========================================
//             foreach ($typeCounts as $typeKey => $need) {
//                 if ($need <= 0) {
//                     continue;
//                 }
                
//                 $pool = $byType[$typeKey] ?? [];
//                 if (empty($pool)) {
//                     // no questions of this type, will back-fill later
//                     continue;
//                 }
                
//                 shuffle($pool);
//                 $slice = array_slice($pool, 0, $need);
//                 foreach ($slice as $qRow) {
//                     $selectedQuestions[] = $qRow;
//                 }
//             }
            
//             // Remove duplicates by question_id just in case
//             $tmp = [];
//             $byId = [];
//             foreach ($selectedQuestions as $qRow) {
//                 $qid = (int) $qRow->question_id;
//                 if (!isset($byId[$qid])) {
//                     $byId[$qid] = true;
//                     $tmp[] = $qRow;
//                 }
//             }
//             $selectedQuestions = $tmp;
            
//             // ==========================================
//             // 8b) BACK-FILL to reach targetTotal
//             // ==========================================
//             if (count($selectedQuestions) < $targetTotal) {
//                 $selectedIds = [];
//                 foreach ($selectedQuestions as $qRow) {
//                     $selectedIds[(int)$qRow->question_id] = true;
//                 }
                
//                 $poolRemaining = [];
//                 foreach ($allQuestions as $qRow) {
//                     $qid = (int) $qRow->question_id;
//                     if (!isset($selectedIds[$qid])) {
//                         $poolRemaining[] = $qRow;
//                     }
//                 }
                
//                 if (!empty($poolRemaining)) {
//                     shuffle($poolRemaining);
//                     foreach ($poolRemaining as $qRow) {
//                         if (count($selectedQuestions) >= $targetTotal) {
//                             break;
//                         }
//                         $selectedQuestions[] = $qRow;
//                     }
//                 }
//             }
            
//             // As a last fallback, if still nothing, use all questions
//             if (empty($selectedQuestions)) {
//                 $selectedQuestions = $allQuestions;
//             }
//         } else {
//             // ==========================================
//             // 8c) Legacy: no per-type config, just use questions_count
//             // ==========================================
//             $selectedQuestions = $allQuestions;
            
//             if ($questionsCountCfg > 0 && count($selectedQuestions) > $questionsCountCfg) {
//                 shuffle($selectedQuestions);
//                 $selectedQuestions = array_slice($selectedQuestions, 0, $questionsCountCfg);
//             }
//         }
        
//         // Re-index
//         $qq = array_values($selectedQuestions);
        
//         // ==========================================
//         // 9) Question order (shuffle / order by type / keep)
//         // ==========================================
//         if (! empty($quiz->shuffle_questions) && (int) $quiz->shuffle_questions === 1) {
//             shuffle($qq);
//         } elseif (! empty($quiz->is_order_by_qtype)) {
//             // group visually by question type
//             usort($qq, function ($a, $b) use ($normalizeType) {
//                 return strcmp(
//                     $normalizeType($a->question_type ?? ''),
//                     $normalizeType($b->question_type ?? '')
//                 );
//             });
//         }
        
//         // Ensure sequential order_index for this attempt
//         $displayOrder = 1;
//         foreach ($qq as $rowQ) {
//             $rowQ->order_index = $displayOrder++;
//         }
        
//         // ==========================================
//         // 10) Persist per-attempt question list
//         // ==========================================
//         if (! empty($qq)) {
//             $batch = [];
//             $order = 1;
            
//             foreach ($qq as $rowQ) {
//                 $batch[] = [
//                     'attempt_id'    => $attemptId,
//                     'quiz_id'       => $quizId,
//                     'question_id'   => (int) $rowQ->question_id,
//                     'display_order' => $order++,
//                     'marks'         => (float) ($rowQ->marks ?? $quiz->per_question_marks ?? 1),
//                     'question_type' => $normalizeType($rowQ->question_type ?? ''),
//                 ];
//             }
            
//             $this->db->table('quiz_attempt_questions')->insertBatch($batch);
//         }
        
//         log_message('debug', "New attempt {$attemptId}: Selected " . count($qq) . " questions");
//     }
// }

// if ($isAdaptive) {

//     // ----------------------------------
//     // A) Load current level definition
//     // ----------------------------------
//     $level = $this->db->table('quiz_levels')
//         ->where([
//             'quiz_id'  => $quizId,
//             'level_no' => $currentLevel
//         ])
//         ->get()
//         ->getRow();

//     if (! $level) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Invalid quiz level configuration.');
//     }

//     // ----------------------------------
//     // B) Resume if questions already exist
//     // ----------------------------------
//     $existingQuestions = $this->db->table('quiz_attempt_questions')
//         ->where([
//             'attempt_id' => $attemptId,
//             'level_no'   => $currentLevel
//         ])
//         ->countAllResults();

//     if ($existingQuestions > 0) {
//         // RESUME EXACT SAME QUESTIONS
//         $qq = $this->db->table('quiz_attempt_questions qa')
//             ->select('qa.question_id, qa.display_order, qa.marks, q.*')
//             ->join('qb_questions q', 'q.id = qa.question_id')
//             ->where([
//                 'qa.attempt_id' => $attemptId,
//                 'qa.level_no'   => $currentLevel
//             ])
//             ->orderBy('qa.display_order', 'ASC')
//             ->get()
//             ->getResult();

//     } else {

//         // ----------------------------------
//         // C) Fetch questions assigned to THIS LEVEL ONLY
//         // ----------------------------------
//         $questions = $this->db->table('quiz_questions qq')
//             ->select('qq.question_id, q.*')
//             ->join('qb_questions q', 'q.id = qq.question_id')
//             ->where([
//                 'qq.quiz_id'  => $quizId,
//                 'qq.level_id' => $level->level_id
//             ])
//             ->orderBy('qq.order_index', 'ASC')
//             ->get()
//             ->getResult();

//         if (empty($questions)) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'No questions found for this level.');
//         }

//         // ----------------------------------
//         // D) Persist questions per attempt + level
//         // ----------------------------------
//         $batch = [];
//         $order = 1;

//         foreach ($questions as $q) {
//             $batch[] = [
//                 'attempt_id'    => $attemptId,
//                 'quiz_id'       => $quizId,
//                 'level_no'      => $currentLevel,
//                 'question_id'   => (int)$q->question_id,
//                 'display_order' => $order++,
//                 'marks'         => (float)($quiz->per_question_marks ?? 1),
//                 'question_type' => $q->question_type
//             ];
//         }

//         $this->db->table('quiz_attempt_questions')->insertBatch($batch);

//         $qq = $questions;
//     }
// }

//     // ==========================================
//     // 11) Load previously saved answers for THIS attempt
//     // ==========================================
//     $savedAnswers = [];

//     if ($attemptId > 0 && !empty($qq)) {
//         // Get question IDs from the questions we're showing
//         $questionIds = array_map(function($q) {
//             return (int) $q->question_id;
//         }, $qq);
        
//         if (!empty($questionIds)) {
//             log_message('debug', "=== LOADING SAVED ANSWERS ===");
//             log_message('debug', "Attempt ID: {$attemptId}");
//             log_message('debug', "Questions in this attempt: " . implode(', ', $questionIds));
            
//             // Load answers for these specific questions
//             $answersResult = $this->db->table('quiz_attempt_answers')
//                 ->where('attempt_id', $attemptId)
//                 ->whereIn('question_id', $questionIds)
//                 ->get()
//                 ->getResultArray();
            
//             log_message('debug', "Found " . count($answersResult) . " saved answers for attempt {$attemptId}");
            
//             foreach ($answersResult as $answer) {
//                 $questionId = (int) $answer['question_id'];
//                 $questionType = $answer['question_type'] ?? '';
                
//                 // Handle different question types
//                 if (in_array($questionType, ['mcq_single', 'tf'])) {
//                     $savedAnswers[$questionId] = [
//                         'type' => $questionType,
//                         'selected_option' => $answer['selected_option'] ?? '',
//                         'answer_text' => $answer['answer_text'] ?? ''
//                     ];
//                 } elseif ($questionType === 'mcq_multi') {
//                     // For multi-select, store as array
//                     $selected = !empty($answer['selected_options']) 
//                         ? json_decode($answer['selected_options'], true) 
//                         : [];
//                     $savedAnswers[$questionId] = [
//                         'type' => $questionType,
//                         'selected_options' => $selected
//                     ];
//                 } elseif (in_array($questionType, ['fill', 'short', 'match'])) {
//                     $savedAnswers[$questionId] = [
//                         'type' => $questionType,
//                         'answer_text' => $answer['answer_text'] ?? ''
//                     ];
//                 } else {
//                     // Fallback: If question_type is empty, try to determine from data
//                     if (!empty($answer['selected_option'])) {
//                         $savedAnswers[$questionId] = [
//                             'type' => 'mcq_single',
//                             'selected_option' => $answer['selected_option'],
//                             'answer_text' => $answer['answer_text'] ?? ''
//                         ];
//                     } elseif (!empty($answer['selected_options'])) {
//                         $selected = !empty($answer['selected_options']) 
//                             ? json_decode($answer['selected_options'], true) 
//                             : [];
//                         $savedAnswers[$questionId] = [
//                             'type' => 'mcq_multi',
//                             'selected_options' => $selected
//                         ];
//                     } elseif (!empty($answer['answer_text'])) {
//                         // Try to determine if it's JSON (match question)
//                         $text = $answer['answer_text'];
//                         $decoded = json_decode($text, true);
//                         if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
//                             $savedAnswers[$questionId] = [
//                                 'type' => 'match',
//                                 'answer_text' => $text
//                             ];
//                         } else {
//                             // Check if it's short answer (multiple lines) or fill (single line)
//                             $isShort = (strpos($text, "\n") !== false || strlen($text) > 100);
//                             $savedAnswers[$questionId] = [
//                                 'type' => $isShort ? 'short' : 'fill',
//                                 'answer_text' => $text
//                             ];
//                         }
//                     }
//                 }
//             }
//         }
        
//         log_message('debug', "Total saved answers loaded: " . count($savedAnswers));
//     }

//     $topicRows = $this->db->table('qb_topics')
//         ->select('qb_topics.topic_name')
//         ->join('quiz_topics', 'quiz_topics.topic_id = qb_topics.id', 'inner')
//         ->where('quiz_topics.quiz_id', $quizId)
//         ->orderBy('qb_topics.topic_name', 'ASC')
//         ->get()
//         ->getResultArray();

//     $topicList = array_column($topicRows, 'topic_name');
    
//     // ==========================================
//     // 12) Time limit (seconds)
//     // ==========================================
//     $timeLimitSec = (int) ($quiz->time_limit_sec ?? 0);

//     // ==========================================
//     // 13) Render quiz
//     // ==========================================
//     $actualTotalQuestions = count($qq);
    
//     $viewFile = (!empty($quiz->kids_mode) && (int)$quiz->kids_mode === 1)
//         ? 'frontend/quizzes/template1_kids'
//         : 'frontend/quizzes/template1';

//     return view($viewFile, [
//         'quiz'           => $quiz,
//         'timeLimitSec'   => $timeLimitSec,
//         'attemptId'      => $attemptId,
//         'qq'             => $qq,
//         'totalQuestions' => count($qq),
//         'topicList'      => $topicList,
//         'savedAnswers'   => $savedAnswers,
//     ]);
// }



public function submit()
{
    $attemptId = (int)$this->request->getPost('attempt_id');
    $attempt   = $this->db->table('quiz_attempts')
        ->where('attempt_id',$attemptId)
        ->get()
        ->getRow();

    if (!$attempt) {
        return redirect()->back()->with('error','Invalid attempt');
    }

    $quiz = $this->db->table('quizzes')
        ->where('quiz_id',$attempt->quiz_id)
        ->get()
        ->getRow();

    if (! $quiz) {
        return redirect()->back()->with('error','Quiz not found.');
    }

    // option map posted from the attempt view
    // optmap[question_id][newLetter] = originalLetter
    $optmapPost = $this->request->getPost('optmap') ?? [];

    // fetch questions & answers
    $qq = $this->db->table('quiz_attempt_questions qa')
    ->select('qa.question_id, qa.marks, q.question_type, q.correct_option, q.options_json, q.answer_text')
    ->join('qb_questions q', 'q.id = qa.question_id')
    ->where('qa.attempt_id', $attemptId)
    ->orderBy('qa.display_order', 'ASC')
    ->get()
    ->getResult();


    $ans = $this->db->table('quiz_attempt_answers')
        ->where('attempt_id',$attemptId)
        ->get()
        ->getResult();

    $ansByQ = [];
    foreach ($ans as $a) {
        $ansByQ[$a->question_id] = $a;
    }

    $score        = 0.0;
    $negativePerQ = (float) ($quiz->negative_mark_per_q ?? 0);

    foreach ($qq as $row) {
        $q = $this->db->table('qb_questions')
            ->where('id', $row->question_id)  // PK = id
            ->get()
            ->getRow();

        if (!$q) {
            continue;
        }

        $awarded = 0.0;
        $given   = $ansByQ[$row->question_id] ?? null;
        $qType   = $q->question_type ?? 'mcq_single';

        // mapping for shuffled options of THIS question
        $mapForQ = [];
        if (isset($optmapPost[$row->question_id]) && is_array($optmapPost[$row->question_id])) {
            $mapForQ = $optmapPost[$row->question_id];   // [new => orig]
        }

        switch ($qType) {
            case 'mcq':
            case 'mcq_single':
                if ($given && $given->selected_option !== null && $given->selected_option !== '') {
                    $selectedNew = strtoupper((string) $given->selected_option);

                    // translate new letter → original letter if shuffled
                    $selectedOrig = $selectedNew;
                    if (!empty($quiz->shuffle_options) && (int)$quiz->shuffle_options === 1 && !empty($mapForQ)) {
                        if (isset($mapForQ[$selectedNew])) {
                            $selectedOrig = strtoupper((string) $mapForQ[$selectedNew]);
                        }
                    }

                    $correctOrig = strtoupper(trim((string)$q->correct_option)); // from bank

                    if ($selectedOrig === $correctOrig) {
                        $awarded = (float) $row->marks;
                    } else {
                        $awarded = 0 - $negativePerQ;
                    }
                } else {
                    $awarded = 0.0;
                }
                break;

            case 'tf':
            case 'true_false':
                if ($given && $given->answer_text !== null && $given->answer_text !== '') {
                    if (strcasecmp(trim((string)$given->answer_text), trim((string)$q->answer_text)) === 0) {
                        $awarded = (float)$row->marks;
                    } else {
                        $awarded = 0 - $negativePerQ;
                    }
                } else {
                    $awarded = 0.0;
                }
                break;

            case 'fill':
            case 'fill_blank':
                if ($given && $given->answer_text !== null && $given->answer_text !== '') {
                    if (strcasecmp(trim((string)$given->answer_text), trim((string)$q->answer_text)) === 0) {
                        $awarded = (float)$row->marks;
                    } else {
                        $awarded = 0 - $negativePerQ;
                    }
                } else {
                    $awarded = 0.0;
                }
                break;

          case 'mcq_multi':

    // decode the question JSON
    $json = json_decode($q->options_json ?? '[]', true);

    // extract only the correct_multi options
    $correct = [];
    if (!empty($json['correct_multi']) && is_array($json['correct_multi'])) {
        $correct = array_map('strtoupper', $json['correct_multi']);
    }

    // selected options from attempt_answers -> JSON
    $selectedNewArr = $given
        ? (array) json_decode($given->selected_options ?? '[]', true)
        : [];

    // convert NEW letters → ORIGINAL letters using mapping when shuffled
    $selectedOrigArr = [];
    foreach ($selectedNewArr as $letterNew) {
        $letterNew  = strtoupper((string)$letterNew);
        $letterOrig = $letterNew;

        if (!empty($quiz->shuffle_options) &&
            !empty($mapForQ) &&
            isset($mapForQ[$letterNew])) {

            $letterOrig = strtoupper((string) $mapForQ[$letterNew]);
        }

        $selectedOrigArr[] = $letterOrig;
    }

    $selectedOrigArr = array_values(array_unique($selectedOrigArr));
    $correct         = array_values(array_unique($correct));

    sort($correct);
    sort($selectedOrigArr);

    $awarded = ($correct === $selectedOrigArr)
                ? (float)$row->marks
                : 0.0;

    break;


            case 'match':
                // Auto-check full correct match; otherwise 0 / negative
                if ($given && !empty($q->options_json)) {
                    $correctPairs = json_decode($q->options_json, true);
                    $givenPairs   = json_decode($given->answer_text ?? '[]', true);

                    if (!is_array($correctPairs)) $correctPairs = [];
                    if (!is_array($givenPairs))   $givenPairs   = [];

                    // Build maps: key = normalized left, value = normalized right
                    $correctMap = [];
                    foreach ($correctPairs as $p) {
                        $l = isset($p['left'])  ? trim(mb_strtolower($p['left']))  : '';
                        $r = isset($p['right']) ? trim(mb_strtolower($p['right'])) : '';
                        if ($l !== '') {
                            $correctMap[$l] = $r;
                        }
                    }

                    $givenMap = [];
                    foreach ($givenPairs as $p) {
                        $l = isset($p['left'])  ? trim(mb_strtolower($p['left']))  : '';
                        $v = isset($p['value']) ? trim(mb_strtolower($p['value'])) : '';
                        if ($l !== '') {
                            $givenMap[$l] = $v;
                        }
                    }

                    if (!empty($correctMap)) {
                        $allCorrect = true;

                        // Every correct left must exist and match right
                        foreach ($correctMap as $l => $rRight) {
                            if (!array_key_exists($l, $givenMap)) {
                                $allCorrect = false;
                                break;
                            }
                            if ($givenMap[$l] !== $rRight) {
                                $allCorrect = false;
                                break;
                            }
                        }

                        // Optional: if student gave extra lefts not in correct, treat as wrong
                        if ($allCorrect) {
                            foreach ($givenMap as $l => $_v) {
                                if (!array_key_exists($l, $correctMap)) {
                                    $allCorrect = false;
                                    break;
                                }
                            }
                        }

                        if ($allCorrect) {
                            $awarded = (float)$row->marks;
                        } else {
                            // full wrong – you can set to 0.0 if you don't want negative
                            $awarded = 0 - $negativePerQ;
                        }
                    } else {
                        $awarded = 0.0;
                    }
                } else {
                    $awarded = 0.0;
                }
                break;

            // short_answer (and any others) => manual marking later
            default:
                $awarded = 0.0;
        }

        $this->db->table('quiz_attempt_answers')
                 ->where([
                     'attempt_id'  => $attemptId,
                     'question_id' => $row->question_id
                 ])
                 ->update([
                     'is_correct'    => $awarded > 0 ? 1 : 0,
                     'marks_awarded' => $awarded,
                 ]);

        $score += $awarded;
    }

   /* =====================================================
 * FINALIZE ATTEMPT (COMMON FOR ALL QUIZZES)
 * ===================================================== */

$this->db->table('quiz_attempts')
    ->where('attempt_id', $attemptId)
    ->update([
        'submitted_at'        => date('Y-m-d H:i:s'),
        
        'score_obtained'      => max(0, $score),
        'active_attempt_key'  => null,
    ]);

/* =====================================================
 * ADAPTIVE QUIZ FLOW (FINAL – STABLE)
 * ===================================================== */

if ((int)$quiz->is_adaptive === 1) {

    // ---------- 1) Resolve current level ----------
    $currentLevelId = (int)($attempt->level_id ?? 0);

    if ($currentLevelId === 0) {
        $firstLevel = $this->db->table('quiz_levels')
            ->where('quiz_id', $quiz->quiz_id)
            ->orderBy('level_no', 'ASC')
            ->get()
            ->getRow();

        if (!$firstLevel) {
            return redirect()->back()->with('error', 'Adaptive levels not configured.');
        }

        $currentLevelId = (int)$firstLevel->level_id;

        $this->db->table('quiz_attempts')
            ->where('attempt_id', $attemptId)
            ->update(['level_id' => $currentLevelId]);

            $attempt = $this->db->table('quiz_attempts')
    ->where('attempt_id', $attemptId)
    ->get()
    ->getRow();

    }

 

// ---------- 2) Evaluate level ----------
$aiEngine = new AiQuizEngine();

$aiResult = $aiEngine->evaluateLevel(
    (int)$attempt->student_id,
    (int)$attempt->quiz_id,
    (int)$currentLevelId,
    (int)$attemptId
);


    // ---------- 3) Store level result ----------
    $this->db->table('student_quiz_levels')->insert([
        'student_id'   => $attempt->student_id,
        'quiz_id'      => $attempt->quiz_id,
        'level_id'     => $currentLevelId,
        'attempt_no'   => $attempt->attempt_no,
        'raw_score'    => max(0, $score),
        'ai_score'     => $aiResult['ai_score'] ?? null,
        'decision'     => $aiResult['decision'],
        'passed'       => in_array($aiResult['decision'], ['ADVANCE','ADVANCE_FAST']) ? 1 : 0,
        'started_at'   => $attempt->started_at,
        'completed_at' => date('Y-m-d H:i:s'),
    ]);

    // ---------- 4) Find current level_no ----------
    $currentLevelNo = (int)$this->db->table('quiz_levels')
        ->select('level_no')
        ->where('level_id', $currentLevelId)
        ->get()
        ->getRow()
        ->level_no;

    // ---------- 5) PASS → NEXT LEVEL ----------
    if (in_array($aiResult['decision'], ['ADVANCE','ADVANCE_FAST'])) {

        $nextLevel = $this->db->table('quiz_levels')
            ->where('quiz_id', $quiz->quiz_id)
            ->where('level_no >', $currentLevelNo)
            ->orderBy('level_no', 'ASC')
            ->get()
            ->getRow();

        if ($nextLevel) {
            // Create NEW attempt for next level
            $this->db->table('quiz_attempts')->insert([
                'quiz_id'    => $quiz->quiz_id,
                'student_id' => $attempt->student_id,
                'attempt_no' => $attempt->attempt_no + 1,
                'level_id'   => $nextLevel->level_id,
                'status'     => 'in_progress',
                'started_at' => date('Y-m-d H:i:s'),
                'client_ip'  => $this->request->getIPAddress(),
            ]);

            return redirect()->to(
                base_url('student/quizzes/start/'.$quiz->quiz_id)
            )->with('msg', 'Level cleared! Proceeding to next level.');
        }

        // 🎉 FINAL LEVEL PASSED
        $this->db->table('quiz_attempts')
            ->where('attempt_id', $attemptId)
            ->update(['status' => 'completed']);


if ((int)$quiz->is_adaptive !== 1) {
        return redirect()->to(
            base_url('student/quizzes/review/'.$attemptId)
        )->with('msg', 'Congratulations! You completed all levels.');
    }
}

    // ---------- 6) FAIL → RETRY SAME LEVEL ----------
    $this->db->table('quiz_attempts')->insert([
        'quiz_id'    => $quiz->quiz_id,
        'student_id' => $attempt->student_id,
        'attempt_no' => $attempt->attempt_no + 1,
        'level_id'   => $currentLevelId,
        'status'     => 'in_progress',
        'started_at' => date('Y-m-d H:i:s'),
        'client_ip'  => $this->request->getIPAddress(),
    ]);

    return redirect()->to(
        base_url('student/quizzes/start/'.$quiz->quiz_id)
    )->with('error', 'Level not cleared. Please try again.');
}

/* =====================================================
 * NON-ADAPTIVE QUIZ FLOW
 * ===================================================== */

// 🚫 NEVER reach here for adaptive quizzes
if ((int)$quiz->is_adaptive !== 1) {
    $this->db->table('quiz_attempts')
        ->where('attempt_id', $attemptId)
        ->update(['status' => 'submitted']);

    return redirect()->to(base_url('student/quizzes/review/'.$attemptId));
}

// Safety fallback (should never hit)
return redirect()->to(base_url('student/quizzes'));


}






// public function start($quizId)
// {

//      log_message('debug', "=== START METHOD CALLED ===");
//     log_message('debug', "Received quizId parameter: {$quizId}");
//     log_message('debug', "Request URI: " . $this->request->getUri()->getPath());
//     log_message('debug', "GET parameters: " . print_r($this->request->getGet(), true));
//     log_message('debug', "Session student_id: " . ($this->session->get('student_id') ?? 'not set'));
//     $quizId  = (int) $quizId;
//     $session = $this->session;
//     $request = $this->request;

//     // =====================================================
//     // 1) Resolve student via token OR normal student login
//     // =====================================================
//     // Support both ?impersonate_token=... and ?impersonate=...
//     $tokenStr = trim((string) ($request->getGet('impersonate_token') ?? $request->getGet('impersonate') ?? ''));

//     $isImpersonation    = false;
//     $effectiveStudentId = 0;

//     if ($tokenStr !== '') {
//         // --- Validate token row ---
//         $row = $this->db->table('quiz_impersonation_tokens')
//             ->where('token', $tokenStr)
//             ->get()
//             ->getRowArray();

//         if (! $row) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Invalid quiz link.');
//         }

//         // Check token expiry
//         if (! empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz link has expired.');
//         }

//         // Optional: single-use check
//         if (isset($row['used']) && (int) $row['used'] === 1) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz link has already been used.');
//         }

//         // Ensure the token belongs to this quiz (if quiz_id column exists)
//         if (isset($row['quiz_id']) && (int) $row['quiz_id'] !== $quizId) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz link does not match this quiz.');
//         }

//         $effectiveStudentId = (int) ($row['student_id'] ?? 0);
//         if ($effectiveStudentId <= 0) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz link is not attached to any student.');
//         }

//         $isImpersonation = true;

//         // Mark impersonation in session (useful if you show a banner "You are impersonating...")
//         $session->set('impersonate', true);
//         $session->set('impersonated_student_id', $effectiveStudentId);

//         // Mark token as used + IP (audit)
//         $updateData = [
//             'used'   => 1,
//             'use_ip' => $request->getIPAddress(),
//         ];
//         $this->db->table('quiz_impersonation_tokens')
//             ->where('id', $row['id'])
//             ->update($updateData);
//     } else {
//         // ===== Normal student / parent flow =====
//         // NEW: prefer ?sid= for parent dashboard multi-child links
        
//         // Add logging in the student resolution section
// $sidParam = (int) $request->getGet('sid');
// log_message('debug', "sid parameter from URL: {$sidParam}");
// log_message('debug', "Effective student ID determined: {$effectiveStudentId}");
// log_message('debug', "Is impersonation: " . ($isImpersonation ? 'yes' : 'no'));

//         if ($sidParam > 0) {
//             $effectiveStudentId = $sidParam;
//         } else {
//             // Fallback: student logged in as themselves
//             $effectiveStudentId = (int) ($session->get('student_id') ?? 0);
//         }

//         if ($effectiveStudentId <= 0) {
//             return redirect()->to(base_url('student/login'))
//                 ->with('error', 'Access denied (not logged in).');
//         }
//     }

//     if ($quizId <= 0) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Invalid quiz.');
//     }

//     // ==========================================
//     // 2) Load quiz + guards
//     // ==========================================
//     $quiz = $this->db->table('quizzes')
//         ->where('quiz_id', $quizId)
//         ->get()
//         ->getRow();

//     if (! $quiz || ! $quiz->is_published) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Quiz not available.');
//     }

//     $now     = date('Y-m-d H:i:s');
//     $startAt = $quiz->start_at ?? null;
//     $endAt   = $quiz->end_at   ?? null;

//     // Normalised flags
//     $hasStart = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
//     $hasEnd   = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';

//     // 🔥 Special rule: if start_at and end_at are BOTH set and equal,
//     // ignore time constraints (quiz is always available).
//     $isForever = $hasStart && $hasEnd && ($startAt === $endAt);

//     if (! $isForever) {
//         if ($hasStart && $startAt > $now) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz has not started yet.');
//         }

//         if ($hasEnd && $endAt < $now) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Quiz has ended.');
//         }
//     }

//     // ==========================================
//     // 3) Resolve campus
//     // ==========================================
//     $campusId = (int) ($session->get('member_campusid') ?? 0);
//     if ($campusId <= 0) {
//         $row = $this->db->table('students')
//             ->select('campus_id')
//             ->where('student_id', $effectiveStudentId)
//             ->get()
//             ->getRow();
//         $campusId = $row ? (int) $row->campus_id : 0;
//     }
//     if ($campusId <= 0) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Campus not configured for your account.');
//     }

//     // ==========================================
//     // 4) Wi-Fi restriction
//     // ==========================================
//     $clientIp = (string) $request->getIPAddress();
//     if (! empty($quiz->wifi_only) && (int) $quiz->wifi_only === 1) {
//         if (! $this->isIpAllowedForCampus($campusId, $clientIp)) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'You can only attempt this quiz from school Wi-Fi.');
//         }
//     }

//     // ==========================================
//     // 5) Attempt limit (based on effectiveStudentId)
//     // ==========================================
//     $prevCount = $this->db->table('quiz_attempts')
//         ->where([
//             'quiz_id'    => $quizId,
//             'student_id' => $effectiveStudentId,
//         ])
//         ->whereIn('status', ['submitted','completed']) 
//         ->countAllResults();

//     $attemptNo = $prevCount + 1;

//     if ((int) $quiz->max_attempts > 0 && $attemptNo > (int) $quiz->max_attempts) {
//         return redirect()->to(base_url('student/quizzes'))
//             ->with('error', 'Max attempts reached.');
//     }

//     // ==========================================
//     // 6) Create attempt
//     // ==========================================
//     $activeKey = $quizId . '-' . $effectiveStudentId;

//     // optional: expire old in_progress if time passed (recommended)
//     $timeLimitSec = (int) ($quiz->time_limit_sec ?? 0);
//     $graceSec     = 300; // 5 min grace
//     if ($timeLimitSec > 0) {
//         $expiryTs = date('Y-m-d H:i:s', time() - ($timeLimitSec + $graceSec));

//         // mark very old in_progress as abandoned so user can start new
//         $this->db->table('quiz_attempts')
//             ->where([
//                 'quiz_id'            => $quizId,
//                 'student_id'         => $effectiveStudentId,
//                 'status'             => 'in_progress',
//                 'active_attempt_key' => $activeKey,
//             ])
//             ->where('started_at <', $expiryTs)
//             ->update([
//                 'status'             => 'abandoned',
//                 'submitted_at'           => date('Y-m-d H:i:s'),
//                 'active_attempt_key' => null,
//             ]);
//     }

//     // 1) Try to RESUME existing in_progress attempt
//     $attemptRow = $this->db->table('quiz_attempts')
//         ->where([
//             'quiz_id'            => $quizId,
//             'student_id'         => $effectiveStudentId,
//             'status'             => 'in_progress',
//             'active_attempt_key' => $activeKey,
//         ])
//         ->orderBy('attempt_id', 'DESC')
//         ->get()
//         ->getRow();

//     if ($attemptRow) {
//         // ✅ resume
//         $attemptId = (int) $attemptRow->attempt_id;
//         $attemptNo = (int) $attemptRow->attempt_no;
//     } else {
//         // 2) No active attempt -> CREATE new one safely (unique key protection)
//         // Count only finished attempts (so refresh doesn't create attempt #2)
//         $prevCount = $this->db->table('quiz_attempts')
//             ->where(['quiz_id'=>$quizId, 'student_id'=>$effectiveStudentId])
//             ->whereIn('status', ['submitted','completed'])
//             ->countAllResults();

//         $attemptNo = $prevCount + 1;

//         if ((int) $quiz->max_attempts > 0 && $attemptNo > (int) $quiz->max_attempts) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Max attempts reached.');
//         }

//         try {
//             $this->db->table('quiz_attempts')->insert([
//                 'quiz_id'            => $quizId,
//                 'student_id'         => $effectiveStudentId,
//                 'attempt_no'         => $attemptNo,
//                 'started_at'         => $now,
//                 'status'             => 'in_progress',
//                 'client_ip'          => $clientIp,
//                 'active_attempt_key' => $activeKey,
//             ]);
//             $attemptId = (int) $this->db->insertID();
//         } catch (\Throwable $e) {
//             // If UNIQUE(active_attempt_key) blocked a duplicate insert, RESUME the existing attempt
//             $attemptRow = $this->db->table('quiz_attempts')
//                 ->where([
//                     'quiz_id'            => $quizId,
//                     'student_id'         => $effectiveStudentId,
//                     'status'             => 'in_progress',
//                     'active_attempt_key' => $activeKey,
//                 ])
//                 ->orderBy('attempt_id', 'DESC')
//                 ->get()
//                 ->getRow();

//             if (!$attemptRow) {
//                 throw $e; // real error
//             }

//             $attemptId = (int) $attemptRow->attempt_id;
//             $attemptNo = (int) $attemptRow->attempt_no;
//         }
//     }

//     // ==========================================
//     // 7) Check if this attempt already has questions assigned
//     // ==========================================
//     $qq = []; // This will hold our questions

//     // Check if questions already exist for this attempt
//     $existingQuestions = $this->db->table('quiz_attempt_questions')
//         ->where('attempt_id', $attemptId)
//         ->countAllResults();

//     if ($existingQuestions > 0) {
//         // ==========================================
//         // 7a) RESUME: Load the SAME questions from previous attempt
//         // ==========================================
//         $qqTable = 'quiz_attempt_questions';
//         $qbTable = 'qb_questions';
        
//         $sel = [
//             'qa.question_id',
//             'qa.display_order AS order_index',
//             'qa.marks',
//             'q.question_type',
//             'q.question',
//             'q.question_image',     
//             'q.question_media',  
//             'q.question_image_alt', 
//             'q.correct_option',
//             'q.option_a',
//             'q.option_b',
//             'q.option_c',
//             'q.option_d',
//             'q.options_json',
//             'q.is_drag',
//         ];
        
//         $builder = $this->db->table("$qqTable qa")
//             ->select(implode(', ', $sel))
//             ->join("$qbTable q", 'q.id = qa.question_id', 'left')
//             ->where('qa.attempt_id', $attemptId)
//             ->orderBy('qa.display_order', 'ASC');
        
//         $res = $builder->get();
//         if ($res !== false) {
//             $qq = $res->getResult();
//         }
        
//         // Debug log
//         log_message('debug', "Resuming attempt {$attemptId}: Found " . count($qq) . " existing questions");
//     } else {
//         // ==========================================
//         // 7b) NEW ATTEMPT: Select random questions
//         // ==========================================
//         $qqTable = 'quiz_questions';
//         $qbTable = 'qb_questions';
        
//         $sel = [
//             'qq.question_id',
//             $this->columnExists($qqTable, 'order_index') ? 'qq.order_index' : 'NULL AS order_index',
//             $this->columnExists($qqTable, 'marks')       ? 'qq.marks'       : '1 AS marks',
//             'q.question_type',
//             'q.question',
//             'q.question_image',     
//             'q.question_media',  
//             'q.question_image_alt', 
//             'q.correct_option',
//             'q.option_a',
//             'q.option_b',
//             'q.option_c',
//             'q.option_d',
//             'q.options_json',
//             'q.is_drag',
//         ];
        
//         $builder = $this->db->table("$qqTable qq")
//             ->select(implode(', ', $sel))
//             ->join("$qbTable q", 'q.id = qq.question_id', 'left')
//             ->where('qq.quiz_id', $quizId)
//             ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false);
        
//         $res = $builder->get();
//         if ($res === false) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'Unable to load quiz questions.');
//         }
        
//         $allQuestions = $res->getResult();
//         if (empty($allQuestions)) {
//             return redirect()->to(base_url('student/quizzes'))
//                 ->with('error', 'No questions found for this quiz.');
//         }
        
//         // ==========================================
//         // 8) Compute per-type counts & target total
//         // ==========================================
//         $normalizeType = function (?string $t): string {
//             $t = strtolower(trim((string) $t));
//             if ($t === '') return '';
            
//             switch ($t) {
//                 case 'mcq':
//                 case 'mcq_single':
//                 case 'single':
//                     return 'mcq_single';
                    
//                 case 'mcq_multi':
//                 case 'mcq_multiple':
//                 case 'multiple':
//                     return 'mcq_multi';
                    
//                 case 'tf':
//                 case 'true_false':
//                 case 'true/false':
//                     return 'tf';
                    
//                 case 'fill':
//                 case 'fill_blank':
//                 case 'fill_blanks':
//                 case 'fib':
//                     return 'fill';
                    
//                 case 'short':
//                 case 'short_answer':
//                     return 'short';
                    
//                 case 'match':
//                 case 'matching':
//                 case 'match_the_column':
//                     return 'match';
                    
//                 default:
//                     return $t; // fallback "other"
//             }
//         };
        
//         // Required counts from quiz config (per type)
//         $typeCounts = [
//             'mcq_single' => (int) ($quiz->count_mcq_single ?? 0),
//             'mcq_multi'  => (int) ($quiz->count_mcq_multi  ?? 0),
//             'tf'         => (int) ($quiz->count_tf         ?? 0),
//             'fill'       => (int) ($quiz->count_fill       ?? 0),
//             'short'      => (int) ($quiz->count_short      ?? 0),
//             'match'      => (int) ($quiz->count_match      ?? 0),
//         ];
//         $sumTypeCounts = array_sum($typeCounts);
        
//         // Target total: questions_count if set, else fall back
//         $questionsCountCfg = (int) ($quiz->questions_count ?? 0);
//         if ($questionsCountCfg > 0) {
//             $targetTotal = min($questionsCountCfg, count($allQuestions));
//         } else {
//             // If no questions_count configured, target = sum of types (if >0) or all
//             $targetTotal = ($sumTypeCounts > 0)
//                 ? min($sumTypeCounts, count($allQuestions))
//                 : count($allQuestions);
//         }
        
//         // Group all questions by normalized type
//         $byType = [];
//         foreach ($allQuestions as $qRow) {
//             $key = $normalizeType($qRow->question_type ?? '');
//             if ($key === '') {
//                 $key = 'other';
//             }
//             if (! isset($byType[$key])) {
//                 $byType[$key] = [];
//             }
//             $byType[$key][] = $qRow;
//         }
        
//         $selectedQuestions = [];
        
//         if ($sumTypeCounts > 0) {
//             // ==========================================
//             // 8a) Per-type selection
//             // ==========================================
//             foreach ($typeCounts as $typeKey => $need) {
//                 if ($need <= 0) {
//                     continue;
//                 }
                
//                 $pool = $byType[$typeKey] ?? [];
//                 if (empty($pool)) {
//                     // no questions of this type, will back-fill later
//                     continue;
//                 }
                
//                 shuffle($pool);
//                 $slice = array_slice($pool, 0, $need);
//                 foreach ($slice as $qRow) {
//                     $selectedQuestions[] = $qRow;
//                 }
//             }
            
//             // Remove duplicates by question_id just in case
//             $tmp = [];
//             $byId = [];
//             foreach ($selectedQuestions as $qRow) {
//                 $qid = (int) $qRow->question_id;
//                 if (!isset($byId[$qid])) {
//                     $byId[$qid] = true;
//                     $tmp[] = $qRow;
//                 }
//             }
//             $selectedQuestions = $tmp;
            
//             // ==========================================
//             // 8b) BACK-FILL to reach targetTotal
//             // ==========================================
//             if (count($selectedQuestions) < $targetTotal) {
//                 $selectedIds = [];
//                 foreach ($selectedQuestions as $qRow) {
//                     $selectedIds[(int)$qRow->question_id] = true;
//                 }
                
//                 $poolRemaining = [];
//                 foreach ($allQuestions as $qRow) {
//                     $qid = (int) $qRow->question_id;
//                     if (!isset($selectedIds[$qid])) {
//                         $poolRemaining[] = $qRow;
//                     }
//                 }
                
//                 if (!empty($poolRemaining)) {
//                     shuffle($poolRemaining);
//                     foreach ($poolRemaining as $qRow) {
//                         if (count($selectedQuestions) >= $targetTotal) {
//                             break;
//                         }
//                         $selectedQuestions[] = $qRow;
//                     }
//                 }
//             }
            
//             // As a last fallback, if still nothing, use all questions
//             if (empty($selectedQuestions)) {
//                 $selectedQuestions = $allQuestions;
//             }
//         } else {
//             // ==========================================
//             // 8c) Legacy: no per-type config, just use questions_count
//             // ==========================================
//             $selectedQuestions = $allQuestions;
            
//             if ($questionsCountCfg > 0 && count($selectedQuestions) > $questionsCountCfg) {
//                 shuffle($selectedQuestions);
//                 $selectedQuestions = array_slice($selectedQuestions, 0, $questionsCountCfg);
//             }
//         }
        
//         // Re-index
//         $qq = array_values($selectedQuestions);
        
//         // ==========================================
//         // 9) Question order (shuffle / order by type / keep)
//         // ==========================================
//         if (! empty($quiz->shuffle_questions) && (int) $quiz->shuffle_questions === 1) {
//             shuffle($qq);
//         } elseif (! empty($quiz->is_order_by_qtype)) {
//             // group visually by question type
//             usort($qq, function ($a, $b) use ($normalizeType) {
//                 return strcmp(
//                     $normalizeType($a->question_type ?? ''),
//                     $normalizeType($b->question_type ?? '')
//                 );
//             });
//         }
        
//         // Ensure sequential order_index for this attempt
//         $displayOrder = 1;
//         foreach ($qq as $rowQ) {
//             $rowQ->order_index = $displayOrder++;
//         }
        
//         // ==========================================
//         // 10) Persist per-attempt question list
//         // ==========================================
//         if (! empty($qq)) {
//             $batch = [];
//             $order = 1;
            
//             foreach ($qq as $rowQ) {
//                 $batch[] = [
//                     'attempt_id'    => $attemptId,
//                     'quiz_id'       => $quizId,
//                     'question_id'   => (int) $rowQ->question_id,
//                     'display_order' => $order++,
//                     'marks'         => (float) ($rowQ->marks ?? $quiz->per_question_marks ?? 1),
//                     'question_type' => $normalizeType($rowQ->question_type ?? ''),
//                 ];
//             }
            
//             $this->db->table('quiz_attempt_questions')->insertBatch($batch);
//         }
        
//         log_message('debug', "New attempt {$attemptId}: Selected " . count($qq) . " questions");
//     }

//       // ==========================================
//     // 11) Load previously saved answers for THIS attempt
//     // ==========================================
//     $savedAnswers = [];

//     if ($attemptId > 0 && !empty($qq)) {
//         // Get question IDs from the questions we're showing
//         $questionIds = array_map(function($q) {
//             return (int) $q->question_id;
//         }, $qq);
        
//         if (!empty($questionIds)) {
//             // Debug: Log what we're looking for
//             log_message('debug', "=== LOADING SAVED ANSWERS ===");
//             log_message('debug', "Attempt ID: {$attemptId}");
//             log_message('debug', "Questions in this attempt: " . implode(', ', $questionIds));
            
//             // Load answers for these specific questions
//             $answersResult = $this->db->table('quiz_attempt_answers')
//                 ->where('attempt_id', $attemptId)
//                 ->whereIn('question_id', $questionIds)
//                 ->get()
//                 ->getResultArray();
            
//             // Debug: Log what we found
//             log_message('debug', "Found " . count($answersResult) . " saved answers for attempt {$attemptId}");
            
//             if (!empty($answersResult)) {
//                 log_message('debug', "Raw answers data:");
//                 foreach ($answersResult as $index => $answer) {
//                     log_message('debug', "Answer #{$index}: " . print_r($answer, true));
//                 }
//             }
            
//             foreach ($answersResult as $answer) {
//                 $questionId = (int) $answer['question_id'];
//                 $questionType = $answer['question_type'] ?? '';
                
//                 // Debug each answer
//                 log_message('debug', "Processing answer for Q{$questionId} (type: {$questionType}): " . 
//                     "selected_option=" . ($answer['selected_option'] ?? 'NULL') . ", " .
//                     "selected_options=" . ($answer['selected_options'] ?? 'NULL') . ", " .
//                     "answer_text=" . (!empty($answer['answer_text']) ? substr($answer['answer_text'], 0, 50) . "..." : 'NULL'));
                
//                 // Handle different question types
//                 if (in_array($questionType, ['mcq_single', 'tf'])) {
//                     $savedAnswers[$questionId] = [
//                         'type' => $questionType,
//                         'selected_option' => $answer['selected_option'] ?? '',
//                         'answer_text' => $answer['answer_text'] ?? ''
//                     ];
//                 } elseif ($questionType === 'mcq_multi') {
//                     // For multi-select, store as array
//                     $selected = !empty($answer['selected_options']) 
//                         ? json_decode($answer['selected_options'], true) 
//                         : [];
//                     $savedAnswers[$questionId] = [
//                         'type' => $questionType,
//                         'selected_options' => $selected
//                     ];
//                 } elseif (in_array($questionType, ['fill', 'short', 'match'])) {
//                     $savedAnswers[$questionId] = [
//                         'type' => $questionType,
//                         'answer_text' => $answer['answer_text'] ?? ''
//                     ];
//                 } else {
//                     // Fallback: If question_type is empty, try to determine from data
//                     if (!empty($answer['selected_option'])) {
//                         $savedAnswers[$questionId] = [
//                             'type' => 'mcq_single',
//                             'selected_option' => $answer['selected_option'],
//                             'answer_text' => $answer['answer_text'] ?? ''
//                         ];
//                     } elseif (!empty($answer['selected_options'])) {
//                         $selected = !empty($answer['selected_options']) 
//                             ? json_decode($answer['selected_options'], true) 
//                             : [];
//                         $savedAnswers[$questionId] = [
//                             'type' => 'mcq_multi',
//                             'selected_options' => $selected
//                         ];
//                     } elseif (!empty($answer['answer_text'])) {
//                         // Try to determine if it's JSON (match question)
//                         $text = $answer['answer_text'];
//                         $decoded = json_decode($text, true);
//                         if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
//                             $savedAnswers[$questionId] = [
//                                 'type' => 'match',
//                                 'answer_text' => $text
//                             ];
//                         } else {
//                             // Check if it's short answer (multiple lines) or fill (single line)
//                             $isShort = (strpos($text, "\n") !== false || strlen($text) > 100);
//                             $savedAnswers[$questionId] = [
//                                 'type' => $isShort ? 'short' : 'fill',
//                                 'answer_text' => $text
//                             ];
//                         }
//                     }
//                 }
//             }
//         }
        
//         // Debug final saved answers
//         log_message('debug', "=== FINAL SAVED ANSWERS ===");
//         log_message('debug', "Total saved answers: " . count($savedAnswers));
//         if (!empty($savedAnswers)) {
//             log_message('debug', "Saved answer IDs: " . implode(', ', array_keys($savedAnswers)));
//             foreach ($savedAnswers as $qid => $answer) {
//                 log_message('debug', "Q{$qid}: type={$answer['type']}, data=" . print_r($answer, true));
//             }
//         } else {
//             log_message('debug', "No saved answers found for attempt {$attemptId}");
            
//             // Debug: Check if table has any answers at all for this attempt
//             $anyAnswers = $this->db->table('quiz_attempt_answers')
//                 ->where('attempt_id', $attemptId)
//                 ->countAllResults();
//             log_message('debug', "Total answers in quiz_attempt_answers for attempt {$attemptId}: {$anyAnswers}");
            
//             // Show all answers for this attempt (even for other questions)
//             if ($anyAnswers > 0) {
//                 $allAnswers = $this->db->table('quiz_attempt_answers')
//                     ->where('attempt_id', $attemptId)
//                     ->get()
//                     ->getResultArray();
//                 log_message('debug', "All answers for attempt {$attemptId}: " . print_r($allAnswers, true));
//             }
//         }
//     }

//     $topicRows = $this->db->table('qb_topics')
//         ->select('qb_topics.topic_name')
//         ->join('quiz_topics', 'quiz_topics.topic_id = qb_topics.id', 'inner')
//         ->where('quiz_topics.quiz_id', $quizId)
//         ->orderBy('qb_topics.topic_name', 'ASC')
//         ->get()
//         ->getResultArray();

//     $topicList = array_column($topicRows, 'topic_name');
    
//     // ==========================================
//     // 12) Time limit (seconds)
//     // ==========================================
//     $timeLimitSec = (int) ($quiz->time_limit_sec ?? 0);

//     // ==========================================
//     // 13) Render quiz
//     // ==========================================
//     $actualTotalQuestions = count($qq);
    
//     $viewFile = (!empty($quiz->kids_mode) && (int)$quiz->kids_mode === 1)
//         ? 'frontend/quizzes/template1_kids'
//         : 'frontend/quizzes/template1';

//     return view($viewFile, [
//         'quiz'           => $quiz,
//         'timeLimitSec'   => $timeLimitSec,
//         'attemptId'      => $attemptId,
//         'qq'             => $qq,
//         'totalQuestions' => count($qq),
//         'topicList'      => $topicList,
//         'savedAnswers'   => $savedAnswers,
//     ]);
// }
    public function results($quizId)
{
    $quizId = (int) $quizId;
    if ($quizId <= 0) {
        return redirect()->to(base_url('student/dashboard'))
            ->with('error', 'Invalid quiz.');
    }

    $db = $this->db ?? db_connect();

    // ---- Load quiz meta (title / subject / class / section) ----
   $quiz = $db->table('quizzes q')
    ->select('q.*,
              c.class_name AS class_name,
              s.section_name AS section_name,
              subj.subject_name')

    // Join class_section → classes + sections
    ->join('class_section cs', 'cs.cls_sec_id = q.cls_sec_id', 'left')
    ->join('classes c', 'c.class_id = cs.class_id', 'left')
    ->join('sections s', 's.section_id = cs.section_id', 'left')

    // Join section_subjects → subject
    ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'left')
    ->join('allsubject subj', 'subj.sid = ss.subject_id', 'left')

    ->where('q.quiz_id', $quizId)
    ->get()
    ->getRow();


    if (! $quiz) {
        return redirect()->to(base_url('student/dashboard'))
            ->with('error', 'Quiz not found.');
    }

    // ---- Fetch all attempts for this quiz (all students of that quiz) ----
    $rows = $db->table('quiz_attempts qa')
        ->select('
            qa.student_id,
            qa.attempt_no,
            qa.score_obtained,
            s.first_name      AS student_name,
            s.reg_no    AS reg_no,
            s.profile_photo AS profile_photo
        ')
        ->join('students s', 's.student_id = qa.student_id', 'left')
        ->where('qa.quiz_id', $quizId)
        ->orderBy('s.first_name ASC')
        ->orderBy('qa.attempt_no ASC')
        ->get()
        ->getResultArray();

    // ---- Build matrix: student row => attempts columns ----
    $attemptNumbers = [];       // unique attempt_no list
    $studentsMatrix = [];       // [student_id => [..., scores => [attempt_no => score]]]

    foreach ($rows as $r) {
        $sid = (int) $r['student_id'];
        $att = (int) $r['attempt_no'];
        $score = (float) ($r['score_obtained'] ?? 0);

        if (!in_array($att, $attemptNumbers, true)) {
            $attemptNumbers[] = $att;
        }

        if (!isset($studentsMatrix[$sid])) {
            // normalise photo path for your uploads folder
            $photo = ltrim((string)($r['profile_photo'] ?? ''), '/');
            $photoUrl = $photo !== ''
                ? base_url('uploads/' . $photo)
                : base_url('resource/img/avatar-student.png');

            $studentsMatrix[$sid] = [
                'student_id'   => $sid,
                'student_name' => $r['student_name'] ?? '—',
                'reg_no'       => $r['reg_no'] ?? '',
                'photo_url'    => $photoUrl,
                'scores'       => [], // attempt_no => score
            ];
        }

        $studentsMatrix[$sid]['scores'][$att] = $score;
    }

    sort($attemptNumbers);                           // attempt 1,2,3...
    $studentRows = array_values($studentsMatrix);    // re-index

    // Optional: class-section label for header
    $classSection = trim(
        ($quiz->class_name ?? '') .
        (!empty($quiz->section_name) ? ' - '.$quiz->section_name : '')
    );

    return view('frontend/quizzes/results', [
        'quiz'          => $quiz,
        'attemptNumbers'=> $attemptNumbers,
        'studentRows'   => $studentRows,
        'classSection'  => $classSection,
    ]);
}


/**
 * Limit questions based on questions_count and shuffle_questions flag.
 *
 * @param array $questions   rows from quiz_questions + qb_questions
 * @param int   $limit       questions_count from quiz (0 = no limit)
 * @param int   $shuffleFlag shuffle_questions (0/1)
 * @return array
 */
protected function applyQuestionLimitAndShuffle(array $questions, int $limit, int $shuffleFlag): array
{
    // Normalize array indexes
    $questions = array_values($questions);

    $doShuffle = ($shuffleFlag === 1);

    // If a limit is set, optionally shuffle first then slice
    if ($limit > 0 && count($questions) > $limit) {
        if ($doShuffle) {
            shuffle($questions);
        }
        $questions = array_slice($questions, 0, $limit);
        $questions = array_values($questions); // reindex
    } elseif ($doShuffle) {
        // No limit but shuffle requested
        shuffle($questions);
    }

    // Ensure order_index is sequential for front-end
    $i = 1;
    foreach ($questions as $q) {
        $q->order_index = $i++;
    }

    return $questions;
}


/**
 * Shuffle MCQ options if shuffle_options = 1.
 *
 * Assumptions:
 * - MCQ types: mcq, mcq_single, mcq_multi, mcq_multi_correct (adjust to your exact list)
 * - Uses option_a..option_d and correct_option letter.
 */
protected function applyOptionShuffle(array $questions, int $shuffleFlag): array
{
    if ($shuffleFlag !== 1) {
        return $questions; // no change
    }

    $mcqTypes = ['mcq', 'mcq_single', 'mcq_multi', 'mcq_multi_correct'];

    foreach ($questions as $q) {
        $type = strtolower((string) ($q->question_type ?? ''));

        if (! in_array($type, $mcqTypes, true)) {
            continue; // only MCQ types
        }

        // If your qb_questions already store randomized options in options_json,
        // and you don't want to re-randomize, you can skip when options_json is present.
        // if (!empty($q->options_json)) continue;

        // Collect options
        $opts = [];
        if (isset($q->option_a) && $q->option_a !== '') {
            $opts[] = ['key' => 'A', 'text' => $q->option_a];
        }
        if (isset($q->option_b) && $q->option_b !== '') {
            $opts[] = ['key' => 'B', 'text' => $q->option_b];
        }
        if (isset($q->option_c) && $q->option_c !== '') {
            $opts[] = ['key' => 'C', 'text' => $q->option_c];
        }
        if (isset($q->option_d) && $q->option_d !== '') {
            $opts[] = ['key' => 'D', 'text' => $q->option_d];
        }

        if (count($opts) <= 1) {
            continue;
        }

        $correctKey  = strtoupper((string) ($q->correct_option ?? ''));
        foreach ($opts as &$o) {
            $o['is_correct'] = ($o['key'] === $correctKey);
        }
        unset($o);

        shuffle($opts);

        // Reassign to option_a..option_d and update correct_option
        $mapKeyToField = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
        $letters       = array_keys($mapKeyToField);
        $newCorrectKey = null;

        // Reset old values
        foreach ($mapKeyToField as $field) {
            if (property_exists($q, $field)) {
                $q->{$field} = null;
            }
        }

        foreach ($opts as $idx => $opt) {
            if ($idx >= count($letters)) {
                break;
            }
            $letter = $letters[$idx];
            $field  = $mapKeyToField[$letter];

            $q->{$field} = $opt['text'];

            if (!empty($opt['is_correct'])) {
                $newCorrectKey = $letter;
            }
        }

        if ($newCorrectKey !== null) {
            $q->correct_option = $newCorrectKey;
        }
    }

    return $questions;
}


//     // AJAX: save one answer
// public function saveAnswer()
// {
//     $post = $this->request->getPost();
    
//     // Log incoming data
//     log_message('debug', 'saveAnswer called with: ' . print_r($post, true));
    
//     if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

//     $attemptId  = (int)$this->request->getPost('attempt_id');
//     $questionId = (int)$this->request->getPost('question_id');
//     $payload    = $this->request->getPost();

//     // 1. Get question details INCLUDING correct_answer
//     $questionDetails = $this->db->table('quiz_attempt_questions qaq')
//         ->select('qaq.question_type, qaq.marks, qbq.correct_option, qbq.option_a, qbq.option_b, qbq.option_c, qbq.option_d')
//         ->join('qb_questions qbq', 'qbq.id = qaq.question_id', 'left')
//         ->where([
//             'qaq.attempt_id' => $attemptId,
//             'qaq.question_id' => $questionId
//         ])
//         ->get()
//         ->getRow();
    
//     if (!$questionDetails) {
//         log_message('error', "Question details not found for attempt {$attemptId}, question {$questionId}");
//         return $this->response->setJSON(['status'=>'error', 'message'=>'Question not found']);
//     }
    
//     $questionType = $questionDetails->question_type ?? 'mcq_single';
//     $correctOption = $questionDetails->correct_option ?? '';
    
//     log_message('debug', "Question type for Q{$questionId}: {$questionType}");
//     log_message('debug', "Correct option for Q{$questionId}: " . ($correctOption ?: '(empty)'));

//     // 2. Prepare base row data
//     $row = [
//         'attempt_id'      => $attemptId,
//         'question_id'     => $questionId,
//         'question_type'   => $questionType,
//         'selected_option' => $payload['selected_option'] ?? null,
//         'selected_options'=> isset($payload['selected_options']) ? json_encode((array)$payload['selected_options']) : null,
//         'answer_text'     => $payload['answer_text'] ?? null,
//         'response_json'   => isset($payload['response_json']) ? json_encode($payload['response_json']) : null,
//         'answered_at'     => date('Y-m-d H:i:s'),
//         // Initialize is_correct as 0 (false) - will be updated below
//         'is_correct'      => 0,
//     ];

//     // 3. Calculate is_correct based on question type
//     $isCorrect = 0; // Default to incorrect
    
//     if (in_array($questionType, ['mcq_single', 'tf'])) {
//         // Single choice or True/False
//         $userAnswer = trim(strtoupper($row['selected_option'] ?? ''));
//         $correctAnswer = trim(strtoupper($correctOption));
        
//         log_message('debug', "Comparing answers - User: '{$userAnswer}', Correct: '{$correctAnswer}'");
        
//         if ($userAnswer === $correctAnswer && !empty($userAnswer)) {
//             $isCorrect = 1;
//             log_message('debug', "✓ Q{$questionId}: Answer is CORRECT");
//         } else {
//             log_message('debug', "✗ Q{$questionId}: Answer is INCORRECT or empty");
//         }
        
//     } elseif ($questionType === 'mcq_multi') {
//         // Multiple choice
//         $userAnswers = !empty($payload['selected_options']) ? (array)$payload['selected_options'] : [];
//         $correctAnswers = !empty($correctOption) ? array_map('trim', explode(',', $correctOption)) : [];
        
//         // Normalize all answers to uppercase for comparison
//         $userAnswersNormalized = array_map('strtoupper', $userAnswers);
//         $correctAnswersNormalized = array_map('strtoupper', $correctAnswers);
        
//         // Sort arrays for consistent comparison
//         sort($userAnswersNormalized);
//         sort($correctAnswersNormalized);
        
//         log_message('debug', "Comparing multi-answers - User: " . implode(',', $userAnswersNormalized) . 
//                    ", Correct: " . implode(',', $correctAnswersNormalized));
        
//         // Check if arrays are identical
//         if (!empty($userAnswersNormalized) && $userAnswersNormalized === $correctAnswersNormalized) {
//             $isCorrect = 1;
//             log_message('debug', "✓ Q{$questionId}: Multi-answer is CORRECT");
//         } else {
//             log_message('debug', "✗ Q{$questionId}: Multi-answer is INCORRECT");
//         }
        
//     } elseif (in_array($questionType, ['fill', 'short', 'match'])) {
//         // For fill-in-blank, short answer, and matching questions
//         // Usually these need manual review or more complex checking
        
//         $userAnswer = trim($row['answer_text'] ?? '');
//         $correctAnswer = trim($correctOption);
        
//         if (!empty($userAnswer)) {
//             // For fill-in questions, you might want exact match or case-insensitive
//             if ($questionType === 'fill') {
//                 // Case-insensitive comparison for fill-in
//                 if (strcasecmp($userAnswer, $correctAnswer) === 0) {
//                     $isCorrect = 1;
//                     log_message('debug', "✓ Q{$questionId}: Fill answer is CORRECT (case-insensitive)");
//                 } else {
//                     log_message('debug', "✗ Q{$questionId}: Fill answer does not match");
//                 }
//             } elseif ($questionType === 'short') {
//                 // Short answers usually need manual review or keyword matching
//                 // For now, mark as needs review (0)
//                 log_message('debug', "Q{$questionId}: Short answer needs manual review");
//                 $isCorrect = 0; // Default to incorrect, needs teacher review
//             } elseif ($questionType === 'match') {
//                 // For matching questions, compare JSON structures
//                 if (!empty($userAnswer) && !empty($correctAnswer)) {
//                     try {
//                         $userMatch = json_decode($userAnswer, true);
//                         $correctMatch = json_decode($correctAnswer, true);
                        
//                         if (json_last_error() === JSON_ERROR_NONE && $userMatch === $correctMatch) {
//                             $isCorrect = 1;
//                             log_message('debug', "✓ Q{$questionId}: Match answer is CORRECT");
//                         }
//                     } catch (\Exception $e) {
//                         log_message('error', "Error decoding match JSON: " . $e->getMessage());
//                     }
//                 }
//             }
//         }
//     }
    
//     // 4. Update is_correct in the row
//     $row['is_correct'] = $isCorrect;
//     log_message('debug', "Setting is_correct = {$isCorrect} for Q{$questionId}");

//     // 5. Check if answer already exists
//    $exists = $this->db->table('quiz_attempt_answers')
//     ->where([
//         'attempt_id'  => $attemptId,
//         'question_id' => $row['question_id'],
//     ])
//     ->countAllResults();

// $data = [
//     'attempt_id'     => $attemptId,
//     'quiz_id'        => $quiz->quiz_id,
//     'question_id'    => $row->question_id,
//     'question_type'  => $qType,
//     'is_correct'     => $awarded > 0 ? 1 : 0,
//     'marks_awarded'  => $awarded,
//     'updated_at'     => date('Y-m-d H:i:s'),
// ];

// if ($exists > 0) {
//     $this->db->table('quiz_attempt_answers')
//         ->where([
//             'attempt_id'  => $attemptId,
//             'question_id' => $row->question_id,
//         ])
//         ->update($data);
// } else {
//     $data['created_at'] = date('Y-m-d H:i:s');
//     $this->db->table('quiz_attempt_answers')->insert($data);
// }


//     // 7. Debug: Verify what was saved
//     $savedRow = $this->db->table('quiz_attempt_answers')
//         ->where(['attempt_id'=>$attemptId,'question_id'=>$questionId])
//         ->get()
//         ->getRow();
    
//     log_message('debug', "Saved row details:");
//     log_message('debug', "  - selected_option: " . ($savedRow->selected_option ?? 'NULL'));
//     log_message('debug', "  - selected_options: " . ($savedRow->selected_options ?? 'NULL'));
//     log_message('debug', "  - is_correct: " . ($savedRow->is_correct ?? 'NULL'));
//     log_message('debug', "  - correct_option (from qb_questions): {$correctOption}");

//     return $this->response->setJSON([
//         'status' => 'ok',
//         'is_correct' => $isCorrect,
//         'correct_option' => $correctOption
//     ]);
// }



public function saveAnswer()
{
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(400);
    }

    $attemptId  = (int) $this->request->getPost('attempt_id');
    $questionId = (int) $this->request->getPost('question_id');
    $payload    = $this->request->getPost();

    if ($attemptId <= 0 || $questionId <= 0) {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Invalid attempt or question',
        ]);
    }

    /* -------------------------------------------------
     * 1) Load question meta for THIS attempt
     * ------------------------------------------------- */
    $qRow = $this->db->table('quiz_attempt_questions qa')
        ->select('qa.question_type, qb.correct_option')
        ->join('qb_questions qb', 'qb.id = qa.question_id', 'left')
        ->where([
            'qa.attempt_id'  => $attemptId,
            'qa.question_id' => $questionId,
        ])
        ->get()
        ->getRow();

    if (! $qRow) {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Question not found for this attempt',
        ]);
    }

    $questionType  = $qRow->question_type ?? 'mcq_single';
    $correctOption = trim((string) ($qRow->correct_option ?? ''));

    /* -------------------------------------------------
     * 2) Build answer row
     * ------------------------------------------------- */
    $row = [
        'attempt_id'       => $attemptId,
        'question_id'      => $questionId,
        'question_type'    => $questionType,
        'selected_option'  => $payload['selected_option'] ?? null,
        'selected_options' => isset($payload['selected_options'])
            ? json_encode((array) $payload['selected_options'])
            : null,
        'answer_text'      => $payload['answer_text'] ?? null,
        'response_json'    => isset($payload['response_json'])
            ? json_encode($payload['response_json'])
            : null,
        'answered_at'      => date('Y-m-d H:i:s'),
        'is_correct'       => 0,
    ];

    /* -------------------------------------------------
     * 3) Compute is_correct (LIGHT CHECK ONLY)
     * ------------------------------------------------- */
    $isCorrect = 0;

    if (in_array($questionType, ['mcq_single', 'tf'], true)) {
        $user = strtoupper(trim((string) $row['selected_option']));
        if ($user !== '' && $user === strtoupper($correctOption)) {
            $isCorrect = 1;
        }
    }

    if ($questionType === 'mcq_multi') {
        $user = array_map('strtoupper', (array) json_decode($row['selected_options'] ?? '[]', true));
        $corr = array_map('strtoupper', array_map('trim', explode(',', $correctOption)));
        sort($user);
        sort($corr);
        if ($user === $corr && ! empty($user)) {
            $isCorrect = 1;
        }
    }

    if ($questionType === 'fill') {
        if (strcasecmp(trim((string) $row['answer_text']), $correctOption) === 0) {
            $isCorrect = 1;
        }
    }

    $row['is_correct'] = $isCorrect;

    /* -------------------------------------------------
     * 4) UPSERT answer
     * ------------------------------------------------- */
    $exists = $this->db->table('quiz_attempt_answers')
        ->where([
            'attempt_id'  => $attemptId,
            'question_id' => $questionId,
        ])
        ->countAllResults();

    if ($exists > 0) {
        $this->db->table('quiz_attempt_answers')
            ->where([
                'attempt_id'  => $attemptId,
                'question_id' => $questionId,
            ])
            ->update($row);
    } else {
        $row['answered_at'] = date('Y-m-d H:i:s');
        $this->db->table('quiz_attempt_answers')->insert($row);
    }

    return $this->response->setJSON([
        'status'      => 'ok',
        'is_correct'  => $isCorrect,
    ]);
}

private function normalizeAnswer($answer, $questionType)
{
    if (is_array($answer)) {
        return array_map(function($item) {
            return strtoupper(trim($item));
        }, $answer);
    }
    
    $normalized = strtoupper(trim($answer));
    
    // Handle true/false variations
    if ($questionType === 'tf') {
        if ($normalized === 'T' || $normalized === 'TRUE') return 'T';
        if ($normalized === 'F' || $normalized === 'FALSE') return 'F';
    }
    
    return $normalized;
}



private function handleAdaptiveDecision($student_id, $quiz_id, $current_level_id, $decision)
{
    switch ($decision) {

        case 'ADVANCE_FAST':
            $this->unlockNextLevel($student_id, $quiz_id, $current_level_id, 2);
            break;

        case 'ADVANCE':
            $this->unlockNextLevel($student_id, $quiz_id, $current_level_id, 1);
            break;

        case 'REPEAT_SAME':
        case 'REPEAT_EASIER':
            // Do nothing → student retries same level
            break;

        case 'HOLD_REVIEW':
            $this->assignRemedialFlag($student_id, $quiz_id, $current_level_id);
            break;
    }
}


private function unlockNextLevel($student_id, $quiz_id, $current_level_id, $jump = 1)
{
    // get current level number
    $level = $this->db->table('quiz_levels')
        ->where('level_id', $current_level_id)
        ->get()
        ->getRow();

    if (!$level) return;

    $nextLevelNo = $level->level_no + $jump;

    $nextLevel = $this->db->table('quiz_levels')
        ->where('quiz_id', $quiz_id)
        ->where('level_no', $nextLevelNo)
        ->where('is_active', 1)
        ->get()
        ->getRow();

    if (!$nextLevel) {
        // quiz completed
        return;
    }

    // allow student to start next level (no insert yet, just unlocked)
}

 protected function isIpAllowedForCampus(int $campusId, string $ip): bool
    {
        if ($campusId <= 0 || $ip === '') {
            return false;
        }

        $rules = $this->db->table('campus_wifi_rules')
            ->where('campus_id', $campusId)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        // No rules configured => treat as unrestricted
        if (empty($rules)) {
            return true;
        }

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false;
        }

        foreach ($rules as $r) {
            $type  = $r['rule_type'] ?? 'single';
            $start = $r['ip_start'] ?? '';
            $end   = $r['ip_end']   ?? '';

            if ($type === 'single') {
                if ($ip === $start) {
                    return true;
                }
            } elseif ($type === 'range') {
                $startLong = ip2long($start);
                $endLong   = ip2long($end);
                if ($startLong !== false && $endLong !== false) {
                    if ($ipLong >= $startLong && $ipLong <= $endLong) {
                        return true;
                    }
                }
            }
        }

        return false;
    }



public function review($attemptId)
{
    helper('url');

    $attemptId = (int) $attemptId;

    // 0) Auth info from frontend portal
    $auth   = $this->session->get('auth') ?? [];
    $role   = $auth['role']    ?? null;   // 'student', 'parent', etc.
    $userId = (int)($auth['user_id'] ?? 0);

    // Portal/admin user (backend) – used for impersonation / staff
    $memberUserId   = (int) ($this->session->get('member_userid') ?? 0);
    $isAdminOrStaff = $memberUserId > 0;   // you can tighten via user_roles later

    // 1) Load attempt
    $attempt = $this->db->table('quiz_attempts')
        ->where('attempt_id', $attemptId)
        ->get()
        ->getRow();

    if (! $attempt) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Invalid attempt');
    }

    // 2) Load quiz
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $attempt->quiz_id)
        ->get()
        ->getRow();

    if (! $quiz) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz not found.');
    }

    // 3) Resolve the student for this attempt
    $stu = $this->db->table('students')
        ->where('student_id', $attempt->student_id)
        ->get()
        ->getRow();

    if (! $stu) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Student not found for this attempt.');
    }

    // 4) PERMISSION CHECKS
    if (! $isAdminOrStaff) {

        // (A) If frontend role is "student" => map user_id -> student_id
        if ($role === 'student') {
            $myStu = $this->db->table('students')
                ->select('student_id')
                ->where('user_id', $userId)
                ->get()
                ->getRow();

            $myStudentId = $myStu ? (int)$myStu->student_id : 0;

            if ($attempt->student_id !== $myStudentId) {
                return redirect()->to(base_url('student/quizzes'))
                    ->with('error', 'You are not allowed to view this attempt.');
            }
        }

        // (B) If frontend role is "parent" => check this student belongs to that parent
        if ($role === 'parent') {
            $parentId = $userId; // your parent login uses user_id = parent_id
            if ((int)$stu->parent_id !== $parentId) {
                return redirect()->to(base_url('student/quizzes'))
                    ->with('error', 'You are not allowed to view this attempt.');
            }
        }

        // (C) If some other role with no permission
        if (! in_array($role, ['student', 'parent'], true)) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Unauthorized.');
        }

        // (D) Respect show_solution for non-staff
        if (! (int)$quiz->show_solution) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Solution review is disabled');
        }
    }

    // 5) Student info (first_name + last_name + cls_sec_id + profile_photo)
    $studentName       = '';
    $classSectionLabel = '';
    $subjectName       = '';
    $topics            = [];
    $studentPhotoUrl   = '';

   $sessionId = (int) (
    $this->session->get('member_sessionid')
    ?? $this->session->get('academic_session_id')
    ?? 0
);

$stuInfo = $this->db->table('students s')
    ->select('
        s.first_name,
        s.last_name,
        sc.cls_sec_id,
        s.profile_photo
    ')
    ->join(
        'student_class sc',
        'sc.student_id = s.student_id AND sc.session_id = '.$sessionId,
        'left'
    )
    ->where('s.student_id', $attempt->student_id)
    ->get()
    ->getRow();


    $clsSecId = 0;
    if ($stuInfo) {
        $first = trim((string)($stuInfo->first_name ?? ''));
        $last  = trim((string)($stuInfo->last_name ?? ''));
        $studentName = trim($first . ' ' . $last);
        $clsSecId    = (int)($stuInfo->cls_sec_id ?? 0);

        $rawPhoto = trim((string)($stuInfo->profile_photo ?? ''));
        if ($rawPhoto !== '') {
            if (preg_match('#^https?://#i', $rawPhoto)) {
                $studentPhotoUrl = $rawPhoto;
            } else {
                $studentPhotoUrl = base_url($rawPhoto);
            }
        }
    }

    // 6) Class – Section label
    if ($clsSecId > 0) {
        $cs = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, c.class_name, s.section_name')
            ->join('classes c',  'c.class_id = cs.class_id',  'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->where('cs.cls_sec_id', $clsSecId)
            ->get()
            ->getRow();
        if ($cs) {
            $className   = $cs->class_name   ?? '';
            $sectionName = $cs->section_name ?? '';
            $classSectionLabel = trim($className . ($sectionName ? (' - ' . $sectionName) : ''));
        }
    }

    // 7) Subject name (from quiz->sec_sub_id)
    if (!empty($quiz->sec_sub_id)) {
        $subRow = $this->db->table('section_subjects ss')
            ->select('sub.subject_name')
            ->join('allsubject sub', 'sub.sid = ss.subject_id', 'left')
            ->where('ss.sec_sub_id', $quiz->sec_sub_id)
            ->get()
            ->getRow();
        if ($subRow) {
            $subjectName = $subRow->subject_name ?? '';
        }
    }

    // 8) Topics covered (quiz_topics + qb_topics)
    try {
        $topicRows = $this->db->table('quiz_topics qt')
            ->select('t.topic_name')
            ->join('qb_topics t', 't.id = qt.topic_id', 'left')
            ->where('qt.quiz_id', $quiz->quiz_id)
            ->get()
            ->getResult();
        foreach ($topicRows as $tr) {
            if (!empty($tr->topic_name)) {
                $topics[] = $tr->topic_name;
            }
        }
        $topics = array_values(array_unique($topics));
    } catch (\Throwable $e) {
        $topics = [];
    }

    // 9) Load all answers for this attempt
    $answerRows = $this->db->table('quiz_attempt_answers')
        ->where('attempt_id', $attemptId)
        ->get()
        ->getResult();

    $answers = [];
    $qIds    = [];

    foreach ($answerRows as $a) {
        $qid = (int) $a->question_id;
        if ($qid <= 0) {
            continue;
        }
        $answers[$qid] = $a;   // map question_id => answer row
        $qIds[]        = $qid;
    }

    $qIds = array_values(array_unique($qIds));

    // 10) Load ONLY those questions that were actually part of this attempt
    $qq = [];

    if (! empty($qIds)) {
        $builder = $this->db->table('quiz_questions qq')
            ->select('qq.*, qbank.*')
            ->join('qb_questions qbank', 'qbank.id = qq.question_id', 'left')
            ->where('qq.quiz_id', $quiz->quiz_id)
            ->whereIn('qq.question_id', $qIds)
            ->orderBy('qq.order_index', 'ASC');

        $res = $builder->get();
        $qq  = $res ? $res->getResult() : [];
    }

    // 11) Stats
    $stats = [
        'total_questions' => 0,
        'correct'         => 0,
        'wrong'           => 0,
        'unattempted'     => 0,
        'total_marks'     => 0,
    ];

    if (!empty($qq)) {
        foreach ($qq as $row) {
            $qid = (int)$row->question_id;
            $stats['total_questions']++;
            $stats['total_marks'] += (float)($row->marks ?? 0);

            $ansRow = $answers[$qid] ?? null;
            if (!$ansRow) {
                $stats['unattempted']++;
                continue;
            }

            // detect if actually attempted
            $qType = strtolower($row->question_type ?? 'mcq');
            $hasAnswer = false;

            switch ($qType) {
                case 'mcq':
                case 'mcq_single':
                    $hasAnswer = !empty($ansRow->selected_option);
                    break;
                case 'mcq_multi':
                    $sel = json_decode($ansRow->selected_options ?? '[]', true);
                    $hasAnswer = !empty($sel);
                    break;
                case 'tf':
                case 'true_false':
                case 'fill':
                case 'fill_blank':
                case 'short':
                case 'short_answer':
                    $hasAnswer = (trim((string)($ansRow->answer_text ?? '')) !== '');
                    break;
                case 'match':
                    $pairs = json_decode($ansRow->answer_text ?? '[]', true);
                    $hasAnswer = !empty($pairs);
                    break;
                default:
                    $hasAnswer = (trim((string)($ansRow->answer_text ?? '')) !== '');
            }

            if (!$hasAnswer) {
                $stats['unattempted']++;
                continue;
            }

            if ((int)$ansRow->is_correct === 1) {
                $stats['correct']++;
            } else {
                $stats['wrong']++;
            }
        }
    }

    $score      = (float)($attempt->score_obtained ?? 0);
    $maxMarks   = $stats['total_marks'] ?: (float)($attempt->total_marks ?? 0);
    $percentage = ($maxMarks > 0)
        ? round(($score / $maxMarks) * 100, 1)
        : null;

    // 12) Duration (start → finish)
    $durationText = '';
    $startRaw     = $attempt->started_at ?? $attempt->created_at ?? null;
    $endRaw       = $attempt->submitted_at ?? null;

    if ($startRaw && $endRaw) {
        $startTs = strtotime($startRaw);
        $endTs   = strtotime($endRaw);
        if ($startTs && $endTs && $endTs > $startTs) {
            $diff = $endTs - $startTs; // seconds
            $mins = floor($diff / 60);
            $secs = $diff % 60;
            $durationText = sprintf('%d min %02d sec', $mins, $secs);
        }
    }

    // 13) Log that student viewed result (only real student, not staff)
    if (! $isAdminOrStaff && $role === 'student' && $userId > 0) {
        $this->db->table('quiz_result_views')->insert([
            'attempt_id' => $attemptId,
            'student_id' => $attempt->student_id,
            'viewed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // 14) All attempts summary for this quiz & student
    $attemptsSummary = $this->db->table('quiz_attempts')
        ->where('quiz_id', $quiz->quiz_id)
        ->where('student_id', $attempt->student_id)
        ->orderBy('attempt_no', 'ASC')
        ->get()
        ->getResult();

    // 15) Render review page – NO redirect after this
    return view('frontend/quizzes/review', [
        'quiz'              => $quiz,
        'attempt'           => $attempt,
        'qq'                => $qq,
        'answers'           => $answers,
        'canShowSolution'   => (int)$quiz->show_solution === 1,
        'studentName'       => $studentName,
        'classSectionLabel' => $classSectionLabel,
        'subjectName'       => $subjectName,
        'topics'            => $topics,
        'attemptsSummary'   => $attemptsSummary,
        'studentPhotoUrl'   => $studentPhotoUrl,
        'stats'             => $stats,
        'percentage'        => $percentage,
        'durationText'      => $durationText,
    ]);
}

}
