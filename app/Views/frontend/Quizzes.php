<?php
namespace App\Controllers\Frontend;
use App\Controllers\BaseController;
use App\Libraries\AdaptiveQuizService;
use Config\Database;

class Quizzes extends BaseController
{
    protected $db; protected $session;

    public function __construct()
    {
        $this->db = db_connect();
        $this->session = session();
        helper(['form','url','text', 'wifi']);
    }

    protected function adaptiveQuiz(): AdaptiveQuizService
    {
        return new AdaptiveQuizService($this->db);
    }

    protected function adaptiveStartUrl(int $quizId, int $studentId): string
    {
        $url = base_url('student/quizzes/start/' . $quizId);
        $sessionSid = (int) ($this->session->get('student_id') ?? 0);
        if ($studentId > 0 && $studentId !== $sessionSid) {
            $url .= '?sid=' . $studentId;
        }

        return $url;
    }

    protected function adaptiveCatalogUrl(int $studentId): string
    {
        $url = base_url('student/quizzes/all');
        $sessionSid = (int) ($this->session->get('student_id') ?? 0);
        if ($studentId > 0 && $studentId !== $sessionSid) {
            $url .= '?sid=' . $studentId;
        }

        return $url;
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
    $quizId  = (int) $quizId;
    $session = $this->session;
    $request = $this->request;
    $auth    = $session->get('auth') ?? [];
    $role    = (string) ($auth['role'] ?? '');

    $sidParam = (int) $request->getGet('sid');
    $effectiveStudentId = 0;

    if ($role === 'parent') {
        $parentId = (int) ($auth['user_id'] ?? 0);
        $candidate = $sidParam > 0
            ? $sidParam
            : (int) ($session->get('active_student_id') ?? $session->get('student_id') ?? 0);
        if ($candidate > 0 && $parentId > 0) {
            $ok = $this->db->table('students')
                ->where('student_id', $candidate)
                ->where('parent_id', $parentId)
                ->countAllResults() > 0;
            $effectiveStudentId = $ok ? $candidate : 0;
        }
    } elseif ($role === 'student') {
        $effectiveStudentId = (int) ($session->get('student_id') ?? 0);
        if ($sidParam > 0 && $sidParam !== $effectiveStudentId) {
            $effectiveStudentId = 0;
        }
    } else {
        $effectiveStudentId = $sidParam > 0
            ? $sidParam
            : (int) ($session->get('student_id') ?? 0);
    }

    if ($quizId <= 0 || $effectiveStudentId <= 0) {
        return redirect()->to(base_url('student/quizzes/all'))
            ->with('error', 'Invalid quiz or student not logged in.');
    }

    // 1) Load quiz
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $quizId)
        ->where('is_published', 1)
        ->get()
        ->getRow();

    if (! $quiz) {
        return redirect()->to(base_url('student/quizzes/all'))
            ->with('error', 'Quiz not available.');
    }

    // Optional: ensure quiz matches student's current class section
    $stuCls = $this->db->table('student_class')
        ->select('cls_sec_id')
        ->where('student_id', $effectiveStudentId)
        ->where('status', 1)
        ->orderBy('sc_id', 'DESC')
        ->get()
        ->getRow();
    $stuClsSec = $stuCls ? (int) $stuCls->cls_sec_id : 0;
    if ($stuClsSec > 0 && (int) ($quiz->cls_sec_id ?? 0) !== $stuClsSec) {
        return redirect()->to(base_url('student/quizzes/all'))
            ->with('error', 'This quiz is not assigned to your class.');
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
        return redirect()->to(base_url('student/quizzes/all'))
            ->with('error', 'Quiz has not started yet.');
    }
    if ($hasEnd && $endAt < $now) {
        return redirect()->to(base_url('student/quizzes/all'))
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
        return redirect()->to(base_url('student/quizzes/all'))
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




public function start($quizId)
{
    log_message('debug', "=== START METHOD CALLED ===");
    log_message('debug', "Received quizId parameter: {$quizId}");
    log_message('debug', "Request URI: " . $this->request->getUri()->getPath());
    log_message('debug', "GET parameters: " . print_r($this->request->getGet(), true));
    log_message('debug', "Session student_id: " . ($this->session->get('student_id') ?? 'not set'));
    
    $quizId  = (int) $quizId;
    $session = $this->session;
    $request = $this->request;

    // =====================================================
    // 1) Resolve student via token OR normal student login
    // =====================================================
    $tokenStr = trim((string) ($request->getGet('impersonate_token') ?? $request->getGet('impersonate') ?? ''));

    $isImpersonation    = false;
    $effectiveStudentId = 0;

    if ($tokenStr !== '') {
        // --- Validate token row ---
        $row = $this->db->table('quiz_impersonation_tokens')
            ->where('token', $tokenStr)
            ->get()
            ->getRowArray();

        if (! $row) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Invalid quiz link.');
        }

        // Check token expiry
        if (! empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Quiz link has expired.');
        }

        // Optional: single-use check
        if (isset($row['used']) && (int) $row['used'] === 1) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Quiz link has already been used.');
        }

        // Ensure the token belongs to this quiz (if quiz_id column exists)
        if (isset($row['quiz_id']) && (int) $row['quiz_id'] !== $quizId) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Quiz link does not match this quiz.');
        }

        $effectiveStudentId = (int) ($row['student_id'] ?? 0);
        if ($effectiveStudentId <= 0) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Quiz link is not attached to any student.');
        }

        $isImpersonation = true;

        // Mark impersonation in session
        $session->set('impersonate', true);
        $session->set('impersonated_student_id', $effectiveStudentId);

        // Mark token as used + IP (audit)
        $updateData = [
            'used'   => 1,
            'use_ip' => $request->getIPAddress(),
        ];
        $this->db->table('quiz_impersonation_tokens')
            ->where('id', $row['id'])
            ->update($updateData);
    } else {
        // ===== Normal student / parent flow =====
        $sidParam = (int) $request->getGet('sid');
        log_message('debug', "sid parameter from URL: {$sidParam}");
        
        if ($sidParam > 0) {
            $effectiveStudentId = $sidParam;
        } else {
            // Fallback: student logged in as themselves
            $effectiveStudentId = (int) ($session->get('student_id') ?? 0);
        }

        if ($effectiveStudentId <= 0) {
            return redirect()->to(base_url('student/login'))
                ->with('error', 'Access denied (not logged in).');
        }
        
        log_message('debug', "Effective student ID determined: {$effectiveStudentId}");
        log_message('debug', "Is impersonation: " . ($isImpersonation ? 'yes' : 'no'));
    }

    if ($quizId <= 0) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Invalid quiz.');
    }

    // ==========================================
    // 2) Load quiz + guards
    // ==========================================
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $quizId)
        ->get()
        ->getRow();

    if (! $quiz || ! $quiz->is_published) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz not available.');
    }

    $now     = date('Y-m-d H:i:s');
    $startAt = $quiz->start_at ?? null;
    $endAt   = $quiz->end_at   ?? null;

    // Normalised flags
    $hasStart = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
    $hasEnd   = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';

    // Special rule: if start_at and end_at are BOTH set and equal,
    // ignore time constraints (quiz is always available).
    $isForever = $hasStart && $hasEnd && ($startAt === $endAt);

    if (! $isForever) {
        if ($hasStart && $startAt > $now) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Quiz has not started yet.');
        }

        if ($hasEnd && $endAt < $now && (int) ($quiz->is_adaptive ?? 0) !== 1) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Quiz has ended.');
        }
    }

    $isAdaptive   = $this->adaptiveQuiz()->isAdaptiveQuiz($quiz);
    $levelInfo    = null;
    $allLevels    = [];
    $currentLevelNo = 1;
    $totalLevels  = 0;

    // ==========================================
    // 3) Resolve campus
    // ==========================================
    $campusId = (int) ($session->get('member_campusid') ?? 0);
    if ($campusId <= 0) {
        $row = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $effectiveStudentId)
            ->get()
            ->getRow();
        $campusId = $row ? (int) $row->campus_id : 0;
    }
    if ($campusId <= 0) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Campus not configured for your account.');
    }

    // ==========================================
    // 4) Wi-Fi restriction
    // ==========================================
    $clientIp = (string) $request->getIPAddress();
    if (! empty($quiz->wifi_only) && (int) $quiz->wifi_only === 1) {
        if (! $this->isIpAllowedForCampus($campusId, $clientIp)) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'You can only attempt this quiz from school Wi-Fi.');
        }
    }

    // ==========================================
    // 5) Attempt limit (based on effectiveStudentId)
    // ==========================================
    $prevCount = $this->db->table('quiz_attempts')
        ->where([
            'quiz_id'    => $quizId,
            'student_id' => $effectiveStudentId,
        ])
        ->whereIn('status', ['submitted','completed']) 
        ->countAllResults();

    $attemptNo = $prevCount + 1;

    if (! $isAdaptive && (int) $quiz->max_attempts > 0 && $attemptNo > (int) $quiz->max_attempts) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Max attempts reached.');
    }

    // ==========================================
    // 6) Create attempt
    // ==========================================
    $activeKey = $quizId . '-' . $effectiveStudentId;

    // Auto-submit very old in_progress attempts
    $timeLimitSec = (int) ($quiz->time_limit_sec ?? 0);
    $graceSec     = 300; // 5 min grace
    if ($timeLimitSec > 0) {
        $expiryTs = date('Y-m-d H:i:s', time() - ($timeLimitSec + $graceSec));

        // Auto-submit very old in_progress attempts
        $this->db->table('quiz_attempts')
            ->where([
                'quiz_id'            => $quizId,
                'student_id'         => $effectiveStudentId,
                'status'             => 'in_progress',
                'active_attempt_key' => $activeKey,
            ])
            ->where('started_at <', $expiryTs)
            ->update([
                'status'             => 'submitted', // Changed from 'abandoned'
                'submitted_at'       => date('Y-m-d H:i:s'),
                'active_attempt_key' => null,
            ]);
    }

    log_message('debug', "Checking for existing in_progress attempts with key: {$activeKey}");
    
    // 1) Try to RESUME existing in_progress attempt
    $attemptRow = $this->db->table('quiz_attempts')
        ->where([
            'quiz_id'            => $quizId,
            'student_id'         => $effectiveStudentId,
            'status'             => 'in_progress',
            'active_attempt_key' => $activeKey,
        ])
        ->orderBy('attempt_id', 'DESC')
        ->get()
        ->getRow();

    log_message('debug', "Found attempt ID: " . ($attemptRow->attempt_id ?? 'none') . 
               " with status: " . ($attemptRow->status ?? 'none'));

    if ($attemptRow) {
        // ✅ resume
        $attemptId = (int) $attemptRow->attempt_id;
        $attemptNo = (int) $attemptRow->attempt_no;
        log_message('debug', "Resuming existing attempt {$attemptId}");
    } else {
        // 2) No active attempt -> CREATE new one safely (unique key protection)
        // Count only finished attempts (so refresh doesn't create attempt #2)
        $prevCount = $this->db->table('quiz_attempts')
            ->where(['quiz_id'=>$quizId, 'student_id'=>$effectiveStudentId])
            ->whereIn('status', ['submitted','completed'])
            ->countAllResults();

        $attemptNo = $prevCount + 1;

        if (! $isAdaptive && (int) $quiz->max_attempts > 0 && $attemptNo > (int) $quiz->max_attempts) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Max attempts reached.');
        }

        try {
            $insertData = [
                'quiz_id'            => $quizId,
                'student_id'         => $effectiveStudentId,
                'attempt_no'         => $attemptNo,
                'started_at'         => $now,
                'status'             => 'in_progress', // Always set explicitly
                'client_ip'          => $clientIp,
                'active_attempt_key' => $activeKey,
            ];
            
            log_message('debug', "Creating new attempt with data: " . print_r($insertData, true));
            
            $this->db->table('quiz_attempts')->insert($insertData);
            $attemptId = (int) $this->db->insertID();
            
            log_message('debug', "Created new attempt ID: {$attemptId}");
            
        } catch (\Throwable $e) {
            log_message('error', "Error creating attempt: " . $e->getMessage());
            
            // If UNIQUE(active_attempt_key) blocked a duplicate insert, RESUME the existing attempt
            $attemptRow = $this->db->table('quiz_attempts')
                ->where([
                    'quiz_id'            => $quizId,
                    'student_id'         => $effectiveStudentId,
                    'active_attempt_key' => $activeKey,
                ])
                ->whereIn('status', ['in_progress', 'submitted']) // Check both statuses
                ->orderBy('attempt_id', 'DESC')
                ->get()
                ->getRow();

            if ($attemptRow) {
                if ($attemptRow->status === 'submitted') {
                    // If already submitted, we need to create a new attempt
                    log_message('debug', "Found submitted attempt, will create new one");
                    $attemptRow = null;
                    
                    // Increment attempt number for the new attempt
                    $prevCount++;
                    $attemptNo = $prevCount + 1;
                    
                    // Create new attempt
                    $this->db->table('quiz_attempts')->insert([
                        'quiz_id'            => $quizId,
                        'student_id'         => $effectiveStudentId,
                        'attempt_no'         => $attemptNo,
                        'started_at'         => $now,
                        'status'             => 'in_progress',
                        'client_ip'          => $clientIp,
                        'active_attempt_key' => $activeKey,
                    ]);
                    $attemptId = (int) $this->db->insertID();
                } else {
                    $attemptId = (int) $attemptRow->attempt_id;
                    $attemptNo = (int) $attemptRow->attempt_no;
                    log_message('debug', "Resuming attempt from catch block: {$attemptId}");
                }
            } else {
                throw $e; // real error
            }
        }
    }

    // Double-check that attempt has a valid status
    if (!isset($attemptId)) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Failed to create or retrieve quiz attempt.');
    }

    // Verify the attempt status is valid
    $finalAttemptCheck = $this->db->table('quiz_attempts')
        ->select('status')
        ->where('attempt_id', $attemptId)
        ->get()
        ->getRow();
    
    if (!$finalAttemptCheck) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz attempt not found.');
    }
    
    if (empty($finalAttemptCheck->status) || !in_array($finalAttemptCheck->status, ['in_progress', 'submitted', 'completed'])) {
        log_message('error', "Invalid attempt status: " . ($finalAttemptCheck->status ?? 'NULL'));
        // Force fix the status
        $this->db->table('quiz_attempts')
            ->where('attempt_id', $attemptId)
            ->update(['status' => 'in_progress']);
    }

    // ==========================================
    // 7) Adaptive: attach level to attempt
    // ==========================================
    if ($isAdaptive) {
        $levelResult = $this->adaptiveQuiz()->attachLevelToAttempt(
            $quiz,
            $effectiveStudentId,
            $attemptId,
            $this->adaptiveCatalogUrl($effectiveStudentId)
        );
        if ($levelResult instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $levelResult;
        }
        $levelInfo = $levelResult;
        if (! $levelInfo) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Could not load quiz level.');
        }
        $allLevels      = $this->adaptiveQuiz()->getLevels($quizId);
        $totalLevels    = count($allLevels);
        $currentLevelNo = (int) ($levelInfo->level_no ?? 1);
    }

    // ==========================================
    // 8) Check if this attempt already has questions assigned
    // ==========================================
    $qq = []; // This will hold our questions

    // Check if questions already exist for this attempt
    $existingQuestions = $this->db->table('quiz_attempt_questions')
        ->where('attempt_id', $attemptId)
        ->countAllResults();

    if ($isAdaptive && $levelInfo && $existingQuestions > 0) {
        $levelId = (int) $levelInfo->level_id;
        $needsReset = false;

        if ($this->columnExists('quiz_questions', 'level_id')) {
            $expectedForLevel = $this->db->table('quiz_questions')
                ->where(['quiz_id' => $quizId, 'level_id' => $levelId])
                ->countAllResults();

            if ($expectedForLevel > 0 && $existingQuestions > $expectedForLevel) {
                $needsReset = true;
            }

            $wrongLevelCount = (int) $this->db->table('quiz_attempt_questions qa')
                ->join('quiz_questions qq', 'qq.question_id = qa.question_id AND qq.quiz_id = ' . (int) $quizId, 'inner')
                ->where('qa.attempt_id', $attemptId)
                ->where('qq.level_id !=', $levelId)
                ->countAllResults();

            if ($wrongLevelCount > 0) {
                $needsReset = true;
            }
        } elseif ((int) ($quiz->questions_count ?? 0) > 0 && $existingQuestions > (int) $quiz->questions_count) {
            $needsReset = true;
        }

        if ($needsReset) {
            $this->db->table('quiz_attempt_questions')->where('attempt_id', $attemptId)->delete();
            $existingQuestions = 0;
        }
    }

    if ($isAdaptive && $levelInfo && $existingQuestions === 0) {
        $assigned = $this->adaptiveQuiz()->assignQuestionsForLevel(
            $attemptId,
            $quizId,
            (int) $levelInfo->level_id,
            $quiz
        );
        if ($assigned === 0) {
            return redirect()->to($this->adaptiveCatalogUrl($effectiveStudentId))
                ->with('error', 'No questions are assigned to ' . $this->adaptiveQuiz()->levelLabel($levelInfo) . '.');
        }
        $existingQuestions = $assigned;
    }

    if ($existingQuestions > 0) {
        // ==========================================
        // 7a) RESUME: Load the SAME questions from previous attempt
        // ==========================================
        $qqTable = 'quiz_attempt_questions';
        $qbTable = 'qb_questions';
        
        $sel = [
            'qa.question_id',
            'qa.display_order AS order_index',
            'qa.marks',
            'q.question_type',
            'q.question',
            'q.question_image',     
            'q.question_media',  
            'q.question_image_alt', 
            'q.correct_option',
            'q.option_a',
            'q.option_b',
            'q.option_c',
            'q.option_d',
            'q.options_json',
            'q.is_drag',
        ];
        
        $builder = $this->db->table("$qqTable qa")
            ->select(implode(', ', $sel))
            ->join("$qbTable q", 'q.id = qa.question_id', 'left')
            ->where('qa.attempt_id', $attemptId);

        if ($isAdaptive && $levelInfo && $this->columnExists('quiz_questions', 'level_id')) {
            $builder->join(
                'quiz_questions qq_lvl',
                'qq_lvl.question_id = qa.question_id AND qq_lvl.quiz_id = ' . (int) $quizId,
                'inner'
            )->where('qq_lvl.level_id', (int) $levelInfo->level_id);
        }

        $builder->orderBy('qa.display_order', 'ASC');

        $res = $builder->get();
        if ($res !== false) {
            $qq = $res->getResult();
        }

        if ($isAdaptive && $levelInfo && $qq === [] && $existingQuestions > 0) {
            $this->db->table('quiz_attempt_questions')->where('attempt_id', $attemptId)->delete();
            $this->adaptiveQuiz()->assignQuestionsForLevel(
                $attemptId,
                $quizId,
                (int) $levelInfo->level_id,
                $quiz
            );
            $res = $this->db->table("$qqTable qa")
                ->select(implode(', ', $sel))
                ->join("$qbTable q", 'q.id = qa.question_id', 'left')
                ->join(
                    'quiz_questions qq_lvl',
                    'qq_lvl.question_id = qa.question_id AND qq_lvl.quiz_id = ' . (int) $quizId,
                    'inner'
                )
                ->where('qa.attempt_id', $attemptId)
                ->where('qq_lvl.level_id', (int) $levelInfo->level_id)
                ->orderBy('qa.display_order', 'ASC')
                ->get();
            if ($res !== false) {
                $qq = $res->getResult();
            }
        }
        
        log_message('debug', "Resuming attempt {$attemptId}: Found " . count($qq) . " existing questions");
    } elseif ($isAdaptive) {
        return redirect()->to($this->adaptiveCatalogUrl($effectiveStudentId))
            ->with('error', 'Unable to load questions for this level.');
    } else {
        // ==========================================
        // 8b) NEW ATTEMPT: Select random questions
        // ==========================================
        $qqTable = 'quiz_questions';
        $qbTable = 'qb_questions';
        
        $sel = [
            'qq.question_id',
            $this->columnExists($qqTable, 'order_index') ? 'qq.order_index' : 'NULL AS order_index',
            $this->columnExists($qqTable, 'marks')       ? 'qq.marks'       : '1 AS marks',
            'q.question_type',
            'q.question',
            'q.question_image',     
            'q.question_media',  
            'q.question_image_alt', 
            'q.correct_option',
            'q.option_a',
            'q.option_b',
            'q.option_c',
            'q.option_d',
            'q.options_json',
            'q.is_drag',
        ];
        
        $builder = $this->db->table("$qqTable qq")
            ->select(implode(', ', $sel))
            ->join("$qbTable q", 'q.id = qq.question_id', 'left')
            ->where('qq.quiz_id', $quizId)
            ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false);
        
        $res = $builder->get();
        if ($res === false) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Unable to load quiz questions.');
        }
        
        $allQuestions = $res->getResult();
        if (empty($allQuestions)) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'No questions found for this quiz.');
        }
        
        // ==========================================
        // 8) Compute per-type counts & target total
        // ==========================================
        $normalizeType = function (?string $t): string {
            $t = strtolower(trim((string) $t));
            if ($t === '') return '';
            
            switch ($t) {
                case 'mcq':
                case 'mcq_single':
                case 'single':
                    return 'mcq_single';
                    
                case 'mcq_multi':
                case 'mcq_multiple':
                case 'multiple':
                    return 'mcq_multi';
                    
                case 'tf':
                case 'true_false':
                case 'true/false':
                    return 'tf';
                    
                case 'fill':
                case 'fill_blank':
                case 'fill_blanks':
                case 'fib':
                    return 'fill';
                    
                case 'short':
                case 'short_answer':
                    return 'short';
                    
                case 'match':
                case 'matching':
                case 'match_the_column':
                    return 'match';
                    
                default:
                    return $t; // fallback "other"
            }
        };
        
        // Required counts from quiz config (per type)
        $typeCounts = [
            'mcq_single' => (int) ($quiz->count_mcq_single ?? 0),
            'mcq_multi'  => (int) ($quiz->count_mcq_multi  ?? 0),
            'tf'         => (int) ($quiz->count_tf         ?? 0),
            'fill'       => (int) ($quiz->count_fill       ?? 0),
            'short'      => (int) ($quiz->count_short      ?? 0),
            'match'      => (int) ($quiz->count_match      ?? 0),
        ];
        $sumTypeCounts = array_sum($typeCounts);
        
        // Target total: questions_count if set, else fall back
        $questionsCountCfg = (int) ($quiz->questions_count ?? 0);
        if ($questionsCountCfg > 0) {
            $targetTotal = min($questionsCountCfg, count($allQuestions));
        } else {
            // If no questions_count configured, target = sum of types (if >0) or all
            $targetTotal = ($sumTypeCounts > 0)
                ? min($sumTypeCounts, count($allQuestions))
                : count($allQuestions);
        }
        
        // Group all questions by normalized type
        $byType = [];
        foreach ($allQuestions as $qRow) {
            $key = $normalizeType($qRow->question_type ?? '');
            if ($key === '') {
                $key = 'other';
            }
            if (! isset($byType[$key])) {
                $byType[$key] = [];
            }
            $byType[$key][] = $qRow;
        }
        
        $selectedQuestions = [];
        
        if ($sumTypeCounts > 0) {
            // ==========================================
            // 8a) Per-type selection
            // ==========================================
            foreach ($typeCounts as $typeKey => $need) {
                if ($need <= 0) {
                    continue;
                }
                
                $pool = $byType[$typeKey] ?? [];
                if (empty($pool)) {
                    // no questions of this type, will back-fill later
                    continue;
                }
                
                shuffle($pool);
                $slice = array_slice($pool, 0, $need);
                foreach ($slice as $qRow) {
                    $selectedQuestions[] = $qRow;
                }
            }
            
            // Remove duplicates by question_id just in case
            $tmp = [];
            $byId = [];
            foreach ($selectedQuestions as $qRow) {
                $qid = (int) $qRow->question_id;
                if (!isset($byId[$qid])) {
                    $byId[$qid] = true;
                    $tmp[] = $qRow;
                }
            }
            $selectedQuestions = $tmp;
            
            // ==========================================
            // 8b) BACK-FILL to reach targetTotal
            // ==========================================
            if (count($selectedQuestions) < $targetTotal) {
                $selectedIds = [];
                foreach ($selectedQuestions as $qRow) {
                    $selectedIds[(int)$qRow->question_id] = true;
                }
                
                $poolRemaining = [];
                foreach ($allQuestions as $qRow) {
                    $qid = (int) $qRow->question_id;
                    if (!isset($selectedIds[$qid])) {
                        $poolRemaining[] = $qRow;
                    }
                }
                
                if (!empty($poolRemaining)) {
                    shuffle($poolRemaining);
                    foreach ($poolRemaining as $qRow) {
                        if (count($selectedQuestions) >= $targetTotal) {
                            break;
                        }
                        $selectedQuestions[] = $qRow;
                    }
                }
            }
            
            // As a last fallback, if still nothing, use all questions
            if (empty($selectedQuestions)) {
                $selectedQuestions = $allQuestions;
            }
        } else {
            // ==========================================
            // 8c) Legacy: no per-type config, just use questions_count
            // ==========================================
            $selectedQuestions = $allQuestions;
            
            if ($questionsCountCfg > 0 && count($selectedQuestions) > $questionsCountCfg) {
                shuffle($selectedQuestions);
                $selectedQuestions = array_slice($selectedQuestions, 0, $questionsCountCfg);
            }
        }
        
        // Re-index
        $qq = array_values($selectedQuestions);
        
        // ==========================================
        // 9) Question order (shuffle / order by type / keep)
        // ==========================================
        if (! empty($quiz->shuffle_questions) && (int) $quiz->shuffle_questions === 1) {
            shuffle($qq);
        } elseif (! empty($quiz->is_order_by_qtype)) {
            // group visually by question type
            usort($qq, function ($a, $b) use ($normalizeType) {
                return strcmp(
                    $normalizeType($a->question_type ?? ''),
                    $normalizeType($b->question_type ?? '')
                );
            });
        }
        
        // Ensure sequential order_index for this attempt
        $displayOrder = 1;
        foreach ($qq as $rowQ) {
            $rowQ->order_index = $displayOrder++;
        }
        
        // ==========================================
        // 10) Persist per-attempt question list
        // ==========================================
        if (! empty($qq)) {
            $batch = [];
            $order = 1;
            
            foreach ($qq as $rowQ) {
                $batch[] = [
                    'attempt_id'    => $attemptId,
                    'quiz_id'       => $quizId,
                    'question_id'   => (int) $rowQ->question_id,
                    'display_order' => $order++,
                    'marks'         => (float) ($rowQ->marks ?? $quiz->per_question_marks ?? 1),
                    'question_type' => $normalizeType($rowQ->question_type ?? ''),
                ];
            }
            
            $this->db->table('quiz_attempt_questions')->insertBatch($batch);
        }
        
        log_message('debug', "New attempt {$attemptId}: Selected " . count($qq) . " questions");
    }

    // ==========================================
    // 11) Load previously saved answers for THIS attempt
    // ==========================================
    $savedAnswers = [];

    if ($attemptId > 0 && !empty($qq)) {
        // Get question IDs from the questions we're showing
        $questionIds = array_map(function($q) {
            return (int) $q->question_id;
        }, $qq);
        
        if (!empty($questionIds)) {
            log_message('debug', "=== LOADING SAVED ANSWERS ===");
            log_message('debug', "Attempt ID: {$attemptId}");
            log_message('debug', "Questions in this attempt: " . implode(', ', $questionIds));
            
            // Load answers for these specific questions
            $answersResult = $this->db->table('quiz_attempt_answers')
                ->where('attempt_id', $attemptId)
                ->whereIn('question_id', $questionIds)
                ->get()
                ->getResultArray();
            
            log_message('debug', "Found " . count($answersResult) . " saved answers for attempt {$attemptId}");
            
            foreach ($answersResult as $answer) {
                $questionId = (int) $answer['question_id'];
                $questionType = $answer['question_type'] ?? '';
                
                // Handle different question types
                if (in_array($questionType, ['mcq_single', 'tf'])) {
                    $savedAnswers[$questionId] = [
                        'type' => $questionType,
                        'selected_option' => $answer['selected_option'] ?? '',
                        'answer_text' => $answer['answer_text'] ?? ''
                    ];
                } elseif ($questionType === 'mcq_multi') {
                    // For multi-select, store as array
                    $selected = !empty($answer['selected_options']) 
                        ? json_decode($answer['selected_options'], true) 
                        : [];
                    $savedAnswers[$questionId] = [
                        'type' => $questionType,
                        'selected_options' => $selected
                    ];
                } elseif (in_array($questionType, ['fill', 'short', 'match'])) {
                    $savedAnswers[$questionId] = [
                        'type' => $questionType,
                        'answer_text' => $answer['answer_text'] ?? ''
                    ];
                } else {
                    // Fallback: If question_type is empty, try to determine from data
                    if (!empty($answer['selected_option'])) {
                        $savedAnswers[$questionId] = [
                            'type' => 'mcq_single',
                            'selected_option' => $answer['selected_option'],
                            'answer_text' => $answer['answer_text'] ?? ''
                        ];
                    } elseif (!empty($answer['selected_options'])) {
                        $selected = !empty($answer['selected_options']) 
                            ? json_decode($answer['selected_options'], true) 
                            : [];
                        $savedAnswers[$questionId] = [
                            'type' => 'mcq_multi',
                            'selected_options' => $selected
                        ];
                    } elseif (!empty($answer['answer_text'])) {
                        // Try to determine if it's JSON (match question)
                        $text = $answer['answer_text'];
                        $decoded = json_decode($text, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $savedAnswers[$questionId] = [
                                'type' => 'match',
                                'answer_text' => $text
                            ];
                        } else {
                            // Check if it's short answer (multiple lines) or fill (single line)
                            $isShort = (strpos($text, "\n") !== false || strlen($text) > 100);
                            $savedAnswers[$questionId] = [
                                'type' => $isShort ? 'short' : 'fill',
                                'answer_text' => $text
                            ];
                        }
                    }
                }
            }
        }
        
        log_message('debug', "Total saved answers loaded: " . count($savedAnswers));
    }

    $topicRows = $this->db->table('qb_topics')
        ->select('qb_topics.topic_name')
        ->join('quiz_topics', 'quiz_topics.topic_id = qb_topics.id', 'inner')
        ->where('quiz_topics.quiz_id', $quizId)
        ->orderBy('qb_topics.topic_name', 'ASC')
        ->get()
        ->getResultArray();

    $topicList = array_column($topicRows, 'topic_name');
    
    // ==========================================
    // 12) Time limit (seconds)
    // ==========================================
    $timeLimitSec = (int) ($quiz->time_limit_sec ?? 0);

    // ==========================================
    // 13) Render quiz
    // ==========================================
    $actualTotalQuestions = count($qq);
    
    $useKidsTemplate = (int) $request->getGet('kids') === 1;
    $viewFile        = $useKidsTemplate
        ? 'frontend/quizzes/template1_kids'
        : 'frontend/quizzes/template1';

    return view($viewFile, [
        'quiz'             => $quiz,
        'timeLimitSec'     => $timeLimitSec,
        'attemptId'        => $attemptId,
        'qq'               => $qq,
        'totalQuestions'   => count($qq),
        'topicList'        => $topicList,
        'savedAnswers'     => $savedAnswers,
        'isAdaptive'       => $isAdaptive,
        'levelInfo'        => $levelInfo,
        'allLevels'        => $allLevels,
        'currentLevelNo'   => $currentLevelNo,
        'totalLevels'      => $totalLevels,
        'studentIdForUrl'  => $effectiveStudentId,
    ]);
}


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


    // AJAX: save one answer
public function saveAnswer()
{
    $post = $this->request->getPost();
    
    // Log incoming data
    log_message('debug', 'saveAnswer called with: ' . print_r($post, true));
    
    if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

    $attemptId  = (int)$this->request->getPost('attempt_id');
    $questionId = (int)$this->request->getPost('question_id');
    $payload    = $this->request->getPost();

    // 1. Get question details INCLUDING correct_answer
    $questionDetails = $this->db->table('quiz_attempt_questions qaq')
        ->select('qaq.question_type, qaq.marks, qbq.correct_option, qbq.option_a, qbq.option_b, qbq.option_c, qbq.option_d')
        ->join('qb_questions qbq', 'qbq.id = qaq.question_id', 'left')
        ->where([
            'qaq.attempt_id' => $attemptId,
            'qaq.question_id' => $questionId
        ])
        ->get()
        ->getRow();
    
    if (!$questionDetails) {
        log_message('error', "Question details not found for attempt {$attemptId}, question {$questionId}");
        return $this->response->setJSON(['status'=>'error', 'message'=>'Question not found']);
    }
    
    $questionType = $questionDetails->question_type ?? 'mcq_single';
    $correctOption = $questionDetails->correct_option ?? '';
    
    log_message('debug', "Question type for Q{$questionId}: {$questionType}");
    log_message('debug', "Correct option for Q{$questionId}: " . ($correctOption ?: '(empty)'));

    // 2. Prepare base row data
    $row = [
        'attempt_id'      => $attemptId,
        'question_id'     => $questionId,
        'question_type'   => $questionType,
        'selected_option' => $payload['selected_option'] ?? null,
        'selected_options'=> isset($payload['selected_options']) ? json_encode((array)$payload['selected_options']) : null,
        'answer_text'     => $payload['answer_text'] ?? null,
        'response_json'   => isset($payload['response_json']) ? json_encode($payload['response_json']) : null,
        'answered_at'     => date('Y-m-d H:i:s'),
        // Initialize is_correct as 0 (false) - will be updated below
        'is_correct'      => 0,
    ];

    // 3. Calculate is_correct based on question type
    $isCorrect = 0; // Default to incorrect
    
    if (in_array($questionType, ['mcq_single', 'tf'])) {
        // Single choice or True/False
        $userAnswer = trim(strtoupper($row['selected_option'] ?? ''));
        $correctAnswer = trim(strtoupper($correctOption));
        
        log_message('debug', "Comparing answers - User: '{$userAnswer}', Correct: '{$correctAnswer}'");
        
        if ($userAnswer === $correctAnswer && !empty($userAnswer)) {
            $isCorrect = 1;
            log_message('debug', "✓ Q{$questionId}: Answer is CORRECT");
        } else {
            log_message('debug', "✗ Q{$questionId}: Answer is INCORRECT or empty");
        }
        
    } elseif ($questionType === 'mcq_multi') {
        // Multiple choice
        $userAnswers = !empty($payload['selected_options']) ? (array)$payload['selected_options'] : [];
        $correctAnswers = !empty($correctOption) ? array_map('trim', explode(',', $correctOption)) : [];
        
        // Normalize all answers to uppercase for comparison
        $userAnswersNormalized = array_map('strtoupper', $userAnswers);
        $correctAnswersNormalized = array_map('strtoupper', $correctAnswers);
        
        // Sort arrays for consistent comparison
        sort($userAnswersNormalized);
        sort($correctAnswersNormalized);
        
        log_message('debug', "Comparing multi-answers - User: " . implode(',', $userAnswersNormalized) . 
                   ", Correct: " . implode(',', $correctAnswersNormalized));
        
        // Check if arrays are identical
        if (!empty($userAnswersNormalized) && $userAnswersNormalized === $correctAnswersNormalized) {
            $isCorrect = 1;
            log_message('debug', "✓ Q{$questionId}: Multi-answer is CORRECT");
        } else {
            log_message('debug', "✗ Q{$questionId}: Multi-answer is INCORRECT");
        }
        
    } elseif (in_array($questionType, ['fill', 'short', 'match'])) {
        // For fill-in-blank, short answer, and matching questions
        // Usually these need manual review or more complex checking
        
        $userAnswer = trim($row['answer_text'] ?? '');
        $correctAnswer = trim($correctOption);
        
        if (!empty($userAnswer)) {
            // For fill-in questions, you might want exact match or case-insensitive
            if ($questionType === 'fill') {
                // Case-insensitive comparison for fill-in
                if (strcasecmp($userAnswer, $correctAnswer) === 0) {
                    $isCorrect = 1;
                    log_message('debug', "✓ Q{$questionId}: Fill answer is CORRECT (case-insensitive)");
                } else {
                    log_message('debug', "✗ Q{$questionId}: Fill answer does not match");
                }
            } elseif ($questionType === 'short') {
                // Short answers usually need manual review or keyword matching
                // For now, mark as needs review (0)
                log_message('debug', "Q{$questionId}: Short answer needs manual review");
                $isCorrect = 0; // Default to incorrect, needs teacher review
            } elseif ($questionType === 'match') {
                // For matching questions, compare JSON structures
                if (!empty($userAnswer) && !empty($correctAnswer)) {
                    try {
                        $userMatch = json_decode($userAnswer, true);
                        $correctMatch = json_decode($correctAnswer, true);
                        
                        if (json_last_error() === JSON_ERROR_NONE && $userMatch === $correctMatch) {
                            $isCorrect = 1;
                            log_message('debug', "✓ Q{$questionId}: Match answer is CORRECT");
                        }
                    } catch (\Exception $e) {
                        log_message('error', "Error decoding match JSON: " . $e->getMessage());
                    }
                }
            }
        }
    }
    
    // 4. Update is_correct in the row
    $row['is_correct'] = $isCorrect;
    log_message('debug', "Setting is_correct = {$isCorrect} for Q{$questionId}");

    // 5. Check if answer already exists
    $existing = $this->db->table('quiz_attempt_answers')
        ->where(['attempt_id'=>$attemptId,'question_id'=>$questionId])
        ->get()
        ->getRow();

    // 6. Save/Update the answer
    if ($existing) {
        $this->db->table('quiz_attempt_answers')
            ->where('id', $existing->id)
            ->update($row);
        log_message('debug', "Updated answer for Q{$questionId}");
    } else {
        $this->db->table('quiz_attempt_answers')->insert($row);
        log_message('debug', "Inserted new answer for Q{$questionId}");
    }

    // 7. Debug: Verify what was saved
    $savedRow = $this->db->table('quiz_attempt_answers')
        ->where(['attempt_id'=>$attemptId,'question_id'=>$questionId])
        ->get()
        ->getRow();
    
    log_message('debug', "Saved row details:");
    log_message('debug', "  - selected_option: " . ($savedRow->selected_option ?? 'NULL'));
    log_message('debug', "  - selected_options: " . ($savedRow->selected_options ?? 'NULL'));
    log_message('debug', "  - is_correct: " . ($savedRow->is_correct ?? 'NULL'));
    log_message('debug', "  - correct_option (from qb_questions): {$correctOption}");

    return $this->response->setJSON([
        'status' => 'ok',
        'is_correct' => $isCorrect,
        'correct_option' => $correctOption
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

    if ($this->adaptiveQuiz()->isAdaptiveQuiz($quiz) && ! empty($attempt->level_id)) {
        return $this->handleAdaptiveFormSubmit($attempt, $quiz);
    }

    // option map posted from the attempt view
    // optmap[question_id][newLetter] = originalLetter
    $optmapPost = $this->request->getPost('optmap') ?? [];

    // fetch questions & answers
    $qq  = $this->db->table('quiz_questions')
        ->where('quiz_id',$quiz->quiz_id)
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

    $this->db->table('quiz_attempts')
        ->where('attempt_id',$attemptId)
        ->update([
            'submitted_at'   => date('Y-m-d H:i:s'),
            'status'         => 'submitted',
            'score_obtained' => max(0, $score),
             'active_attempt_key' => null,
        ]);

    return redirect()->to(base_url('student/quizzes/review/'.$attemptId));
}

    /**
     * Non-AJAX submit for adaptive quizzes (timer expiry / no JS).
     */
    private function handleAdaptiveFormSubmit(object $attempt, object $quiz)
    {
        $startUrl = $this->adaptiveStartUrl((int) $quiz->quiz_id, (int) $attempt->student_id);
        $level    = $this->adaptiveQuiz()->getLevel((int) $attempt->level_id);

        if (! $level) {
            return redirect()->back()->with('error', 'Quiz level not found.');
        }

        if ($attempt->status !== 'in_progress') {
            return redirect()->to($startUrl)->with('msg', 'This level was already submitted.');
        }

        $optmapPost = $this->request->getPost('optmap') ?? [];
        $result     = $this->adaptiveQuiz()->finalizeLevelAttempt(
            $quiz,
            $attempt,
            $level,
            is_array($optmapPost) ? $optmapPost : []
        );

        if ($result['passed'] && $result['is_final_level']) {
            return redirect()->to(base_url('student/quizzes/review/' . $attempt->attempt_id))
                ->with('msg', 'Congratulations! You completed all levels.');
        }

        if ($result['passed'] && $result['has_next_level']) {
            return redirect()->to($startUrl)
                ->with('msg', 'Level passed! Continue to the next level.');
        }

        if ($result['passed']) {
            return redirect()->to($startUrl)->with('msg', 'Level passed!');
        }

        return redirect()->to($startUrl)
            ->with(
                'error',
                'Level not passed. Score ' . $result['percentage'] . '% — need ' . $result['min_pass'] . '%. Try again.'
            );
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

    /**
     * AJAX: grade current level and return pass/fail + next steps.
     */
    public function submitLevel()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
        }

        $attemptId = (int) $this->request->getPost('attempt_id');
        $attempt   = $this->db->table('quiz_attempts')->where('attempt_id', $attemptId)->get()->getRow();

        if (! $attempt || $attempt->status !== 'in_progress') {
            return $this->response->setJSON(['success' => false, 'message' => 'Attempt not found or already submitted.']);
        }

        $quiz = $this->db->table('quizzes')->where('quiz_id', $attempt->quiz_id)->get()->getRow();
        if (! $quiz || ! $this->adaptiveQuiz()->isAdaptiveQuiz($quiz)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not an adaptive quiz.']);
        }

        $levelId = (int) ($attempt->level_id ?? 0);
        $level   = $this->adaptiveQuiz()->getLevel($levelId);
        if (! $level) {
            return $this->response->setJSON(['success' => false, 'message' => 'Level not found.']);
        }

        $optmap = $this->request->getPost('optmap') ?? [];
        $result = $this->adaptiveQuiz()->finalizeLevelAttempt($quiz, $attempt, $level, is_array($optmap) ? $optmap : []);

        $message = $result['passed']
            ? ($result['is_final_level']
                ? 'Excellent! You completed the final level.'
                : 'Level ' . $result['current_level_no'] . ' passed! You can continue to level ' . ($result['next_level_no'] ?? '') . '.')
            : 'You scored ' . $result['percentage'] . '%. You need at least ' . $result['min_pass'] . '% to pass. Review your answers and try again.';

        return $this->response->setJSON([
            'success'        => true,
            'passed'         => (bool) $result['passed'],
            'has_next_level' => (bool) $result['has_next_level'],
            'is_final_level' => (bool) $result['is_final_level'],
            'message'        => $message,
            'score'          => [
                'raw'        => $result['score'],
                'max'        => $result['max_marks'],
                'percentage' => $result['percentage'],
            ],
            'min_pass'         => $result['min_pass'],
            'current_level_no' => $result['current_level_no'],
            'total_levels'     => $result['total_levels'],
            'level_label'      => $result['level_label'],
        ]);
    }

    /**
     * AJAX: start a fresh in-progress attempt for the next level.
     */
    public function moveToNextLevel()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $attemptId = (int) $this->request->getPost('attempt_id');
        $attempt   = $this->db->table('quiz_attempts')->where('attempt_id', $attemptId)->get()->getRow();

        if (! $attempt) {
            return $this->response->setJSON(['success' => false, 'message' => 'Attempt not found.']);
        }

        $quiz    = $this->db->table('quizzes')->where('quiz_id', $attempt->quiz_id)->get()->getRow();
        $levelId = (int) ($attempt->level_id ?? 0);
        $level   = $this->adaptiveQuiz()->getLevel($levelId);

        if (! $quiz || ! $level) {
            return $this->response->setJSON(['success' => false, 'message' => 'Quiz or level not found.']);
        }

        $lastPass = $this->db->tableExists('student_quiz_levels')
            ? $this->db->table('student_quiz_levels')
                ->where([
                    'student_id' => (int) $attempt->student_id,
                    'quiz_id'    => (int) $attempt->quiz_id,
                    'level_id'   => $levelId,
                    'passed'     => 1,
                ])
                ->orderBy('completed_at', 'DESC')
                ->get()
                ->getRow()
            : null;

        if (! $lastPass) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pass the current level before continuing.']);
        }

        $levels = $this->adaptiveQuiz()->getLevels((int) $quiz->quiz_id);
        $next   = null;
        foreach ($levels as $lvl) {
            if ((int) $lvl->level_no > (int) $level->level_no) {
                $next = $lvl;
                break;
            }
        }

        if (! $next) {
            return $this->response->setJSON([
                'success'  => true,
                'redirect' => base_url('student/quizzes/review/' . $attemptId),
            ]);
        }

        $activeKey = $quiz->quiz_id . '-' . $attempt->student_id;
        $this->db->table('quiz_attempts')
            ->where([
                'quiz_id'            => $quiz->quiz_id,
                'student_id'         => $attempt->student_id,
                'status'             => 'in_progress',
                'active_attempt_key' => $activeKey,
            ])
            ->update(['status' => 'submitted', 'active_attempt_key' => null]);

        $newId = $this->adaptiveQuiz()->createLevelAttempt(
            (int) $quiz->quiz_id,
            (int) $attempt->student_id,
            (int) $next->level_id,
            $activeKey,
            (string) $this->request->getIPAddress()
        );

        $this->adaptiveQuiz()->assignQuestionsForLevel($newId, (int) $quiz->quiz_id, (int) $next->level_id, $quiz);

        return $this->response->setJSON([
            'success'  => true,
            'redirect' => $this->adaptiveStartUrl((int) $quiz->quiz_id, (int) $attempt->student_id),
            'message'  => 'Starting ' . $this->adaptiveQuiz()->levelLabel($next) . '.',
        ]);
    }

    /**
     * AJAX: retry the same level with a new attempt.
     */
    public function retryCurrentLevel()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $attemptId = (int) $this->request->getPost('attempt_id');
        $attempt   = $this->db->table('quiz_attempts')->where('attempt_id', $attemptId)->get()->getRow();

        if (! $attempt) {
            return $this->response->setJSON(['success' => false, 'message' => 'Attempt not found.']);
        }

        $quiz    = $this->db->table('quizzes')->where('quiz_id', $attempt->quiz_id)->get()->getRow();
        $levelId = (int) ($attempt->level_id ?? 0);

        if (! $quiz || $levelId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid quiz level.']);
        }

        $activeKey = $quiz->quiz_id . '-' . $attempt->student_id;
        $this->db->table('quiz_attempts')
            ->where([
                'quiz_id'            => $quiz->quiz_id,
                'student_id'         => $attempt->student_id,
                'status'             => 'in_progress',
                'active_attempt_key' => $activeKey,
            ])
            ->update(['status' => 'submitted', 'active_attempt_key' => null]);

        $newId = $this->adaptiveQuiz()->createLevelAttempt(
            (int) $quiz->quiz_id,
            (int) $attempt->student_id,
            $levelId,
            $activeKey,
            (string) $this->request->getIPAddress()
        );

        $this->adaptiveQuiz()->assignQuestionsForLevel($newId, (int) $quiz->quiz_id, $levelId, $quiz);

        return $this->response->setJSON([
            'success'  => true,
            'redirect' => $this->adaptiveStartUrl((int) $quiz->quiz_id, (int) $attempt->student_id),
            'message'  => 'New attempt started for this level.',
        ]);
    }

    /**
     * AJAX: mark adaptive quiz complete after final level.
     */
    public function completeAdaptiveQuiz()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $attemptId = (int) $this->request->getPost('attempt_id');
        $attempt   = $this->db->table('quiz_attempts')->where('attempt_id', $attemptId)->get()->getRow();

        if (! $attempt) {
            return $this->response->setJSON(['success' => false, 'message' => 'Attempt not found.']);
        }

        $this->db->table('quiz_attempts')
            ->where('attempt_id', $attemptId)
            ->update(['status' => 'completed']);

        return $this->response->setJSON([
            'success'  => true,
            'redirect' => base_url('student/quizzes/review/' . $attemptId),
            'message'  => 'Quiz completed successfully!',
        ]);
    }

}
