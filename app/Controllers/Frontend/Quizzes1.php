<?php
namespace App\Controllers\Frontend;
use App\Controllers\BaseController;
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

    public function index()
    {
        
        $studentId = (int)$this->session->get('student_id');
        $classId   = (int)$this->session->get('student_class_id'); // adapt to your mapping

        // Unattempted
        $unattempted = $this->db->query("
          SELECT q.*
          FROM quizzes q
          LEFT JOIN quiz_attempts qa
            ON qa.quiz_id = q.quiz_id
           AND qa.student_id = ?
           AND qa.status = 'submitted'
          WHERE q.cls_sec_id = ?
            AND q.is_published = 1
            AND (q.start_at IS NULL OR q.start_at <= NOW())
            AND (q.end_at IS NULL OR q.end_at >= NOW())
          GROUP BY q.quiz_id
          HAVING COUNT(qa.attempt_id) < q.max_attempts
          ORDER BY q.start_at DESC, q.quiz_id DESC
        ", [$studentId, $classId])->getResult();

        // echo "<pre>";
        // print_r($this->db->getLastQuery());
        // echo "</pre>";
        // exit;

        // Attempted
        $attempted = $this->db->query("
          SELECT q.title, q.quiz_id, qa.attempt_id, qa.attempt_no, qa.score_obtained, qa.status, qa.submitted_at
          FROM quiz_attempts qa
          JOIN quizzes q ON q.quiz_id = qa.quiz_id
          WHERE qa.student_id = ?
          ORDER BY qa.submitted_at DESC
        ", [$studentId])->getResult();


        

        return view('frontend/quizzes/index', compact('unattempted','attempted'));
    }

    // public function start($quizId)
    // {
    //     $studentId = (int)$this->session->get('student_id');
    //     $quiz = $this->db->table('quizzes')->where('quiz_id',$quizId)->get()->getRow();
    //     if (!$quiz || !$quiz->is_published) return redirect()->back()->with('error','Quiz not available');

    //     // create attempt_no
    //     $prev = $this->db->table('quiz_attempts')
    //         ->where(['quiz_id'=>$quizId,'student_id'=>$studentId])
    //         ->countAllResults();
    //     $attemptNo = $prev + 1;
    //     if ($attemptNo > $quiz->max_attempts) return redirect()->back()->with('error','Max attempts reached');

    //     $this->db->table('quiz_attempts')->insert([
    //         'quiz_id'     => $quizId,
    //         'student_id'  => $studentId,
    //         'attempt_no'  => $attemptNo,
    //         'started_at'  => date('Y-m-d H:i:s'),
    //         'status'      => 'in_progress',
    //     ]);

    //     $attemptId = $this->db->insertID();

    //     // Load questions
    //     $qq = $this->db->table('quiz_questions')->where('quiz_id',$quizId)->orderBy('order_index')->get()->getResult();

    //     // You can shuffle here in PHP if $quiz->shuffle_questions == 1

    //     return view('frontend/quizzes/attempt', [
    //         'quiz'      => $quiz,
    //         'attemptId' => $attemptId,
    //         'qq'        => $qq
    //     ]);
    // }
    // helper inside the controller (top of class) or make it private method
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

    // 1) Load quiz + guards (same as start)
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $quizId)
        ->get()
        ->getRow();

    if (! $quiz || ! $quiz->is_published) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz not available.');
    }

    $now = date('Y-m-d H:i:s');
    if (!empty($quiz->start_at) && $quiz->start_at > $now) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz has not started yet.');
    }
    if (!empty($quiz->end_at) && $quiz->end_at < $now) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz has ended.');
    }

    // 2) Load quiz questions (same tables & join as start)
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
    'q.options_json', // ⬅️ add this line
];

    // If you have a correct_answer column (for TF / fill / short), include it
    if ($this->columnExists($qbTable, 'correct_answer')) {
        $sel[] = 'q.correct_answer';
    }

    $builder = $this->db->table("$qqTable qq")
        ->select(implode(', ', $sel))
        ->join("$qbTable q", 'q.id = qq.question_id', 'left')   // ✅ same join as start()
        ->where('qq.quiz_id', $quizId)
        ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false);

    $res = $builder->get();
    if ($res === false) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Unable to load quiz questions for practice.');
    }

    $qq = $res->getResult();
    if (! $qq) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'No questions found for this quiz.');
    }

    // 3) Apply questions_count limit (same as start)
    $limit = (int) ($quiz->questions_count ?? 0);
    if ($limit > 0 && count($qq) > $limit) {
        shuffle($qq);
        $qq = array_slice($qq, 0, $limit);
        $qq = array_values($qq);
    }

    // 4) Optional shuffle question order (same as start)
    if (!empty($quiz->shuffle_questions) && (int) $quiz->shuffle_questions === 1) {
        shuffle($qq);
        $i = 1;
        foreach ($qq as $row) {
            $row->order_index = $i++;
        }
    }




    // 5) Render the practice view (no attemptId – practice only)
    return view('frontend/quizzes/quiz_practice_play', [
         'quiz'      => $quiz,
        
        'qq'        => $qq,
    ]);
}

public function start($quizId)
{
    $quizId   = (int) $quizId;
    $session  = $this->session;
    $request  = $this->request;

    // This is your logged-in staff/admin user (from portal)
    $userId = (int) $session->get('member_userid');

    // ==========================================
    // 1) Handle impersonation_token (admin/teacher)
    // ==========================================
    $token = $request->getGet('impersonate_token');

    if ($token) {
        // --- Role check using user_roles table ---
        // Only allow if this portal user has roleid 1 or 5
        $roleRow = $this->db->table('user_roles')
            ->select('roleid')
            ->where('userid', $userId)
            ->get()
            ->getRow();

        $roleId = $roleRow ? (int) $roleRow->roleid : 0;

        if (! in_array($roleId, [1, 5], true)) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'You are not allowed to start this quiz.');
        }

        // --- Validate token itself ---
        $row = $this->db->table('quiz_impersonation_tokens')
            ->where('token', $token)
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

        // Optional: single-use
        if ((int) $row['used'] === 1) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Quiz link has already been used.');
        }

        // Ensure the token belongs to this quiz
        if ((int) $row['quiz_id'] !== $quizId) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'Quiz link does not match this quiz.');
        }

        // Mark impersonation in session (student to impersonate)
        $session->set('impersonate', true);
        $session->set('impersonated_student_id', (int) $row['student_id']);

        // Mark token as used + IP (audit log)
        $this->db->table('quiz_impersonation_tokens')
            ->where('id', $row['id'])
            ->update([
                'used'   => 1,
                'use_ip' => $request->getIPAddress(),
            ]);
    }

    // ==========================================
    // 2) Resolve studentId (real or impersonated)
    // ==========================================
    $studentId = $session->get('impersonate')
        ? (int) $session->get('impersonated_student_id')
        : (int) $session->get('student_id');

    if ($quizId <= 0 || $studentId <= 0) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Invalid quiz or student not logged in.');
    }

    // ==========================================
    // 3) Load quiz + guards (same as before)
    // ==========================================
    $quiz = $this->db->table('quizzes')->where('quiz_id', $quizId)->get()->getRow();
    if (! $quiz || ! $quiz->is_published) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz not available.');
    }

    $now = date('Y-m-d H:i:s');

    if (! empty($quiz->start_at) && $quiz->start_at > $now) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz has not started yet.');
    }
    if (! empty($quiz->end_at) && $quiz->end_at < $now) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz has ended.');
    }

    // ==========================================
    // 4) Resolve campus
    // ==========================================
    $campusId = (int) ($session->get('member_campusid') ?? 0);
    if ($campusId <= 0) {
        $row = $this->db->table('students')->select('campus_id')
            ->where('student_id', $studentId)->get()->getRow();
        $campusId = $row ? (int) $row->campus_id : 0;
    }
    if ($campusId <= 0) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Campus not configured for your account.');
    }

    // ==========================================
    // 5) Wi-Fi restriction
    // ==========================================
    $clientIp = (string) $request->getIPAddress();
    if (! empty($quiz->wifi_only) && (int) $quiz->wifi_only === 1) {
        if (! $this->isIpAllowedForCampus($campusId, $clientIp)) {
            return redirect()->to(base_url('student/quizzes'))
                ->with('error', 'You can only attempt this quiz from school Wi-Fi.');
        }
    }

    // ==========================================
    // 6) Attempt limit
    // ==========================================
    $prevCount = $this->db->table('quiz_attempts')
        ->where([
            'quiz_id'    => $quizId,
            'student_id' => $studentId,
        ])
        ->countAllResults();

    $attemptNo = $prevCount + 1;

    if ((int) $quiz->max_attempts > 0 && $attemptNo > (int) $quiz->max_attempts) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Max attempts reached.');
    }

    // ==========================================
    // 7) Create attempt
    // ==========================================
    $this->db->table('quiz_attempts')->insert([
        'quiz_id'    => $quizId,
        'student_id' => $studentId,
        'attempt_no' => $attemptNo,
        'started_at' => $now,
        'status'     => 'in_progress',
        'client_ip'  => $clientIp,
    ]);
    $attemptId = (int) $this->db->insertID();

    // ==========================================
    // 8) Load quiz questions
    // ==========================================
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
    'q.options_json',   // <-- for match pairs
    'q.is_drag',        // <-- new flag
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

    $qq = $res->getResult();

    // ==========================================
    // 9) Apply limit questions_count
    // ==========================================
    $limit = (int) ($quiz->questions_count ?? 0);
    if ($limit > 0 && count($qq) > $limit) {
        shuffle($qq);                 // shuffle QUESTIONS only
        $qq = array_slice($qq, 0, $limit);
        $qq = array_values($qq);
    }

    // ==========================================
    // 10) Optional shuffle question order
    // ==========================================
    if (! empty($quiz->shuffle_questions) && (int) $quiz->shuffle_questions === 1) {
        shuffle($qq);
        $i = 1;
        foreach ($qq as $rowQ) {
            $rowQ->order_index = $i++;
        }
    }

    // ==========================================
    // 11) Render quiz
    // ==========================================
    return view('frontend/quizzes/template1', [
        'quiz'      => $quiz,
        'attemptId' => $attemptId,
        'qq'        => $qq,
    ]);
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

    // AJAX: save one answer
    public function saveAnswer()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        $attemptId  = (int)$this->request->getPost('attempt_id');
        $questionId = (int)$this->request->getPost('question_id');
        $payload    = $this->request->getPost(); // selected_option | selected_options[] | answer_text | response_json

        // upsert
        $existing = $this->db->table('quiz_attempt_answers')
            ->where(['attempt_id'=>$attemptId,'question_id'=>$questionId])->get()->getRow();

        $row = [
            'attempt_id'      => $attemptId,
            'question_id'     => $questionId,
            'selected_option' => $payload['selected_option'] ?? null,
            'selected_options'=> isset($payload['selected_options']) ? json_encode((array)$payload['selected_options']) : null,
            'answer_text'     => $payload['answer_text'] ?? null,
            'response_json'   => isset($payload['response_json']) ? json_encode($payload['response_json']) : null,
            'answered_at'     => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->table('quiz_attempt_answers')
                ->where('id', $existing->id)->update($row);
        } else {
            $this->db->table('quiz_attempt_answers')->insert($row);
        }

        return $this->response->setJSON(['status'=>'ok']);
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
        ]);

    return redirect()->to(base_url('student/quizzes/review/'.$attemptId));
}



public function review($attemptId)
{
    $attemptId = (int) $attemptId;

    // 1) Load attempt
    $attempt = $this->db->table('quiz_attempts')
        ->where('attempt_id', $attemptId)
        ->get()
        ->getRow();

    if (! $attempt) {
        return redirect()->back()->with('error', 'Invalid attempt');
    }

    // 2) Load quiz & guard "show_solution"
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $attempt->quiz_id)
        ->get()
        ->getRow();

    if (! $quiz) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Quiz not found.');
    }

    if (! (int) $quiz->show_solution) {
        return redirect()->to(base_url('student/quizzes'))
            ->with('error', 'Solution review is disabled');
    }

    // 3) Load all answers for this attempt (these represent the questions actually used)
    $answerRows = $this->db->table('quiz_attempt_answers')
        ->where('attempt_id', $attemptId)
        ->get()
        ->getResult();

    $ansByQ = [];
    $qIds   = [];

    foreach ($answerRows as $a) {
        $qid = (int) $a->question_id;
        if ($qid <= 0) {
            continue;
        }
        $ansByQ[$qid] = $a;
        $qIds[]       = $qid;
    }

    $qIds = array_values(array_unique($qIds));

    // If somehow there are no answers/records, keep qq empty
    $qq = [];
    if (! empty($qIds)) {
        // 4) Load ONLY those questions that were actually part of this attempt
        $builder = $this->db->table('quiz_questions qq')
            ->select('qq.*, qbank.*')
            ->join('qb_questions qbank', 'qbank.id = qq.question_id', 'left')
            ->where('qq.quiz_id', $quiz->quiz_id)
            ->whereIn('qq.question_id', $qIds)
            ->orderBy('qq.order_index', 'ASC');

        $res = $builder->get();
        $qq  = $res ? $res->getResult() : [];
    }

    // 5) Log that student viewed result
    $this->db->table('quiz_result_views')->insert([
        'attempt_id' => $attemptId,
        'student_id' => $attempt->student_id,
        'viewed_at'  => date('Y-m-d H:i:s'),
    ]);

    // 6) Render review page with only "attempt questions"
    return view('frontend/quizzes/review', [
        'quiz'     => $quiz,
        'attempt'  => $attempt,
        'qq'       => $qq,
        'answers'  => $ansByQ,
    ]);
}

}
