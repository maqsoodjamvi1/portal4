<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Quizzes extends BaseController
{
    protected $db; protected $session;

    public function __construct()
    {
        $this->db = db_connect();
        $this->session = session();
        helper(['form','url']);
    }

    private function resolveCampusAndSession(): array
{
    // Get from session if already set
    $campusId  = (int) ($this->session->get('member_campusid') ?? 0);
    $sessionId = (int) ($this->session->get('member_sessionid') ?? $this->session->get('academic_session_id') ?? 0);

    // If sessionId is missing, derive it from campus -> system -> academic_session
    if ($sessionId <= 0 && $campusId > 0) {
        // 1) campus -> system_id
        $systemId = 0;
        $row = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', $campusId)
            ->limit(1)->get()->getRow();
        if ($row) $systemId = (int) ($row->system_id ?? 0);

        // 2) system_id -> academic_session (prefer active today; fallback latest active)
        if ($systemId > 0) {
            $today = date('Y-m-d');

            $qActive = $this->db->table('academic_session')
                ->select('session_id')
                ->where('system_id', $systemId)
                ->where('status', 1)
                ->where('start_date <=', $today)
                ->where('end_date >=', $today)
                ->orderBy('start_date', 'DESC')
                ->limit(1)->get();

            if ($qActive && ($r = $qActive->getRow())) {
                $sessionId = (int) ($r->session_id ?? 0);
            }

            if ($sessionId <= 0) {
                $qLatest = $this->db->table('academic_session')
                    ->select('session_id')
                    ->where('system_id', $systemId)
                    ->where('status', 1)
                    ->orderBy('end_date', 'DESC')
                    ->limit(1)->get();
                if ($qLatest && ($r2 = $qLatest->getRow())) {
                    $sessionId = (int) ($r2->session_id ?? 0);
                }
            }
        }

        // Persist for later requests
        if ($sessionId > 0) {
            $this->session->set('academic_session_id', $sessionId);
            // If you also use member_sessionid elsewhere, set it too:
            $this->session->set('member_sessionid', $sessionId);
        }
    }

    return [$campusId, $sessionId];
}


// app/Controllers/Admin/Quizzes.php (index)
public function index()
{
    $db = db_connect();

    // Pull quizzes with readable names for class-section & section-subject
    $quizzes = $db->table('quizzes q')
        ->select("
            q.quiz_id, q.title, q.cls_sec_id, q.sec_sub_id, q.term_session_id,
            q.instructions, q.time_limit_sec, q.start_at, q.end_at, q.max_attempts,
            q.shuffle_questions, q.shuffle_options, q.show_solution, q.negative_mark_per_q,
            q.is_published,

            /* Class-Section: e.g. 'G-8 Blue' */
            CONCAT(
              COALESCE(c.class_short_name, c.class_name, 'Class'),
              ' - ',
              COALESCE(sec.section_name, 'Section')
            ) AS cls_sec_name,

            /* Subject short name */
            COALESCE(subj.subject_short_name, subj.subject_name, 'Subject') AS sec_sub_name
        ")
        ->join('class_section cs', 'cs.cls_sec_id = q.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->join('section_subjects ssub', 'ssub.sec_sub_id = q.sec_sub_id', 'left')
        ->join('allsubject subj', 'subj.sid = ssub.subject_id', 'left')
        ->orderBy('q.start_at', 'DESC')
        ->get()->getResult();

    return view('admin/quizzes/index_cards', [
        'quizzes' => $quizzes,
    ]);
}


public function print($quizId)
{
    helper('text');

    $quizId = (int) $quizId;

    // 1) Load quiz header info
    $quiz = $this->db->table('quizzes q')
        ->select("
            q.*,
            CONCAT(c.class_name, ' - ', sec.section_name) AS cls_sec_name,
            subj.subject_name AS sec_sub_name
        ", false)
        ->join('class_section cs',    'cs.cls_sec_id  = q.cls_sec_id',  'left')
        ->join('classes c',           'c.class_id     = cs.class_id',   'left')
        ->join('sections sec',        'sec.section_id = cs.section_id', 'left')
        ->join('section_subjects ssub','ssub.sec_sub_id = q.sec_sub_id','left')
        ->join('allsubject subj',     'subj.sid       = ssub.subject_id','left')
        ->where('q.quiz_id', $quizId)
        ->limit(1)
        ->get()
        ->getRow();

    if (! $quiz) {
        return redirect()->to(base_url('admin/quizzes'))
            ->with('error', 'Quiz not found.');
    }

    // 1.b) Topics from quiz_topics
    $topics = [];
    try {
        $topicRows = $this->db->table('quiz_topics qt')
            ->select('t.topic_name')
            ->join('qb_topics t', 't.id = qt.topic_id', 'left')
            ->where('qt.quiz_id', $quizId)
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

    // 1.c) School (system) + campus info
    $system = $this->db->table('system')
        ->select('system_name, logo')
        ->get()
        ->getRow();

    $campus = null;
    $campusId = (int)($quiz->campus_id ?? (session('member_campusid') ?? 0));

    if ($campusId > 0) {
        $campus = $this->db->table('campus')
            ->select('campus_name, location')
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();
    }

    // 2) Load questions linked to this quiz
    $rows = $this->db->table('quiz_questions qq')
        ->select("
            qq.question_id,
            qq.order_index,
            qq.marks,
            q.question_type,
            q.question,
            q.option_a,
            q.option_b,
            q.option_c,
            q.option_d,
            q.options_json
        ")
        ->join('qb_questions q', 'q.id = qq.question_id', 'left')
        ->where('qq.quiz_id', $quizId)
        ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false)
        ->get()
        ->getResult();

    if (empty($rows)) {
        return redirect()->to(base_url('admin/quizzes'))
            ->with('error', 'No questions found for this quiz.');
    }

    // 3) Apply questions_count limit (same logic as play)
    $limit = (int) ($quiz->questions_count ?? 0);
    if ($limit > 0 && count($rows) > $limit) {
        shuffle($rows); // shuffle QUESTIONS only
        $rows = array_slice($rows, 0, $limit);
        $rows = array_values($rows);
    }

    // 4) Optional: order by question type if quiz.is_order_by_qtype == 1
    $isOrderByType = property_exists($quiz, 'is_order_by_qtype')
        ? (int) $quiz->is_order_by_qtype
        : 0;

    if ($isOrderByType === 1) {
        $typeOrder = [
            'mcq_single'   => 1,
            'mcq'          => 1,
            'mcq_multi'    => 2,
            'true_false'   => 3,
            'tf'           => 3,
            'fill_blank'   => 4,
            'fill'         => 4,
            'short_answer' => 5,
            'short'        => 5,
            'match'        => 6,
        ];

        usort($rows, static function ($a, $b) use ($typeOrder) {
            $ta = strtolower($a->question_type ?? '');
            $tb = strtolower($b->question_type ?? '');

            $oa = $typeOrder[$ta] ?? 99;
            $ob = $typeOrder[$tb] ?? 99;

            if ($oa === $ob) {
                return ($a->order_index <=> $b->order_index)
                    ?: ($a->question_id <=> $b->question_id);
            }

            return $oa <=> $ob;
        });
    }

    // 5) Human type labels for view
    foreach ($rows as $r) {
        $t = strtolower($r->question_type ?? 'mcq');
        switch ($t) {
            case 'mcq':
            case 'mcq_single':
                $r->type_label = 'MCQ (Single)';
                break;
            case 'mcq_multi':
                $r->type_label = 'MCQ (Multiple)';
                break;
            case 'true_false':
            case 'tf':
                $r->type_label = 'True / False';
                break;
            case 'fill':
            case 'fill_blank':
                $r->type_label = 'Fill in the Blanks';
                break;
            case 'short':
            case 'short_answer':
                $r->type_label = 'Short Answer';
                break;
            case 'match':
                $r->type_label = 'Match the Column';
                break;
            default:
                $r->type_label = ucfirst($t);
        }
    }

    return view('admin/quizzes/print_quiz', [
        'quiz'      => $quiz,
        'questions' => $rows,
        'topics'    => $topics,
        'system'    => $system,
        'campus'    => $campus,
        // If you later generate QR HTML, pass like:
        // 'qrHtml' => $qrHtml,
    ]);
}


public function printVersions($quizId)
{
    helper(['text']);

    $quizId = (int) $quizId;

    // 1) Load quiz + class/subject info
    $quiz = $this->db->table('quizzes q')
        ->select("
            q.*,
            cs.cls_sec_id,
            CONCAT(c.class_name, ' - ', sec.section_name) AS cls_sec_name,
            subj.subject_name AS sec_sub_name
        ", false)
        ->join('class_section cs',    'cs.cls_sec_id  = q.cls_sec_id',  'left')
        ->join('classes c',           'c.class_id     = cs.class_id',   'left')
        ->join('sections sec',        'sec.section_id = cs.section_id', 'left')
        ->join('section_subjects ssub','ssub.sec_sub_id = q.sec_sub_id','left')
        ->join('allsubject subj',     'subj.sid       = ssub.subject_id','left')
        ->where('q.quiz_id', $quizId)
        ->limit(1)
        ->get()
        ->getRow();

    if (! $quiz) {
        return redirect()->to(base_url('admin/quizzes'))
            ->with('error', 'Quiz not found.');
    }

    // 2) Load topics (for header)
    $topics = [];
    try {
        $topicRows = $this->db->table('quiz_topics qt')
            ->select('t.topic_name')
            ->join('qb_topics t', 't.id = qt.topic_id', 'left')
            ->where('qt.quiz_id', $quizId)
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

    // 3) Load system & campus info (for header)
    $system = $this->db->table('system')
        ->select('system_name, logo')
        ->limit(1)
        ->get()
        ->getRow();

    // campus: use class-section students' campus (same for all)
    $campus = null;

    // 4) Load all students of this quiz's class-section
    $studentsQ = $this->db->table('student_class sc')
        ->select('
            s.student_id,
            s.first_name,
            s.last_name,
            
            s.profile_photo,
            s.campus_id,
            sc.cls_sec_id
        ')
        ->join('students s', 's.student_id = sc.student_id', 'left')
        ->where('sc.cls_sec_id', (int)$quiz->cls_sec_id)
        ->where('sc.status', 1)
        ->orderBy('s.first_name', 'ASC')
        ->orderBy('s.last_name',  'ASC')
        ->get();

    $students = $studentsQ ? $studentsQ->getResult() : [];

    if (! empty($students)) {
        $campusId = (int) ($students[0]->campus_id ?? 0);
        if ($campusId > 0) {
            $campus = $this->db->table('campus')
                ->select('campus_name, location')
                ->where('campus_id', $campusId)
                ->limit(1)
                ->get()
                ->getRow();
        }
    }

    // 5) Base questions for this quiz
    $baseQuestions = $this->db->table('quiz_questions qq')
        ->select("
            qq.question_id,
            qq.order_index,
            qq.marks,
            q.question_type,
            q.question,
            q.option_a,
            q.option_b,
            q.option_c,
            q.option_d,
            q.options_json
        ")
        ->join('qb_questions q', 'q.id = qq.question_id', 'left')
        ->where('qq.quiz_id', $quizId)
        ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false)
        ->get()
        ->getResult();

    if (empty($baseQuestions)) {
        return redirect()->to(base_url('admin/quizzes'))
            ->with('error', 'No questions found for this quiz.');
    }

    // 6) Build per-student randomized versions
    $limitQuestions   = (int) ($quiz->questions_count ?? 0);
    $shuffleQuestions = true; // For exam printing: always randomize order
    $shuffleOptions   = true; // For exam printing: always randomize options

    $versions = [];

    foreach ($students as $student) {
        // clone base questions array
        $qList = $baseQuestions;

        // Shuffle question order
        if ($shuffleQuestions) {
            shuffle($qList);
        }

        // Apply question limit if set
        if ($limitQuestions > 0 && count($qList) > $limitQuestions) {
            $qList = array_slice($qList, 0, $limitQuestions);
            $qList = array_values($qList);
        }

        // Prepare options layout and randomization per question
        foreach ($qList as $q) {
            $type = strtolower($q->question_type ?? 'mcq');

            // Build raw options
            $rawOptions = [];
            if (trim((string)$q->option_a) !== '') $rawOptions[] = trim((string)$q->option_a);
            if (trim((string)$q->option_b) !== '') $rawOptions[] = trim((string)$q->option_b);
            if (trim((string)$q->option_c) !== '') $rawOptions[] = trim((string)$q->option_c);
            if (trim((string)$q->option_d) !== '') $rawOptions[] = trim((string)$q->option_d);

            // Only MCQ types use options layout
            if (in_array($type, ['mcq','mcq_single','mcq_multi'], true) && ! empty($rawOptions)) {
                // Shuffle options for this student/question
                if ($shuffleOptions && count($rawOptions) > 1) {
                    shuffle($rawOptions);
                }

                $printOptions = [];
                $maxLen       = 0;

                foreach ($rawOptions as $idx => $text) {
                    $len = mb_strlen($text);
                    if ($len > $maxLen) {
                        $maxLen = $len;
                    }

                    $printOptions[] = [
                        'label' => chr(65 + $idx), // A, B, C, D...
                        'text'  => $text,
                    ];
                }

                if ($maxLen < 10) {
                    $layoutCols = 4;
                } elseif ($maxLen < 30) {
                    $layoutCols = 2;
                } else {
                    $layoutCols = 1;
                }

                $q->print_options = $printOptions;
                $q->layout_cols   = $layoutCols;
            } else {
                $q->print_options = [];
                $q->layout_cols   = 1;
            }

            // Human type label (for header badge if needed)
            $t = strtolower($q->question_type ?? 'mcq');
            switch ($t) {
                case 'mcq':
                case 'mcq_single':
                    $q->type_label = 'MCQ (Single)';
                    break;
                case 'mcq_multi':
                    $q->type_label = 'MCQ (Multiple)';
                    break;
                case 'true_false':
                case 'tf':
                    $q->type_label = 'True / False';
                    break;
                case 'fill':
                case 'fill_blank':
                    $q->type_label = 'Fill in the Blanks';
                    break;
                case 'short':
                case 'short_answer':
                    $q->type_label = 'Short Answer';
                    break;
                case 'match':
                    $q->type_label = 'Match the Column';
                    break;
                default:
                    $q->type_label = ucfirst($t);
            }
        }

        // QR code payload (simple: quiz+student)
       // QR code payload (simple: quiz+student)
$qrPayload = 'QUIZ:' . $quizId . '|STU:' . $student->student_id;

// Using QRServer API (embeddable in <img>)
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data='
    . urlencode($qrPayload);

$versions[] = [
    'student'   => $student,
    'questions' => $qList,
    'qr_url'    => $qrUrl,
];
    }

    if (empty($versions)) {
        return redirect()->to(base_url('admin/quizzes'))
            ->with('error', 'No active students found for this quiz class-section.');
    }

    return view('admin/quizzes/print_quiz_versions', [
        'quiz'     => $quiz,
        'topics'   => $topics,
        'system'   => $system,
        'campus'   => $campus,
        'versions' => $versions,
    ]);
}



public function create()
{
    // --- Session & Campus from PHP session ---
    $sessionId = (int) ($this->session->get('member_sessionid') ?? 0);
    $campusId  = (int) ($this->session->get('member_campusid') ?? 0);

    // --- Resolve system_id from campus ---
    $systemId = 0;
    if ($campusId > 0) {
        $row = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', $campusId)
            ->limit(1)->get()->getRow();
        if ($row) $systemId = (int) ($row->system_id ?? 0);
    }

    // --- Resolve session_id if missing (active window -> latest) ---
    if ($sessionId <= 0 && $systemId > 0) {
        $today = date('Y-m-d');

        $qActive = $this->db->table('academic_session')
            ->select('session_id')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->orderBy('start_date', 'DESC')
            ->limit(1)->get();

        if ($qActive && ($r = $qActive->getRow())) {
            $sessionId = (int) ($r->session_id ?? 0);
        }

        if ($sessionId <= 0) {
            $qLatest = $this->db->table('academic_session')
                ->select('session_id')
                ->where('system_id', $systemId)
                ->where('status', 1)
                ->orderBy('end_date', 'DESC')
                ->limit(1)->get();
            if ($qLatest && ($r2 = $qLatest->getRow())) {
                $sessionId = (int) ($r2->session_id ?? 0);
            }
        }

        if ($sessionId > 0) {
            $this->session->set('academic_session_id', $sessionId);
        }
    }

    // --- Class Sections list (array: cls_sec_id + label) ---
    $classSections = [];
    if ($campusId > 0) {
        $classSections = $this->db->table('class_section cs')
            ->select("cs.cls_sec_id, CONCAT(c.class_name, ' - ', s.section_name) AS label", false)
            ->join('classes c',  'c.class_id  = cs.class_id',  'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->get()->getResultArray();
    }

    // --- Terms for the resolved session (VALUE = term_session_id) ---
    $terms = [];
    if ($sessionId > 0) {
        // Introspect columns to avoid SQL errors
        $tsCols = [];
        try {
            foreach ($this->db->getFieldData('terms_session') as $f) {
                $tsCols[strtolower($f->name)] = $f->name; // preserve actual case
            }
        } catch (\Throwable $e) {
            $tsCols = [];
        }

        $colTSId     = $tsCols['term_session_id'] ?? ($tsCols['id'] ?? 'term_session_id');
        $colTermId   = $tsCols['term_id'] ?? 'term_id';
        $colSessId   = $tsCols['session_id'] ?? 'session_id';
        $colStart    = $tsCols['start_date'] ?? null;
        $colEnd      = $tsCols['end_date'] ?? null;
        $colTSStatus = $tsCols['status'] ?? ($tsCols['STATUS'] ?? null);

        // Try to discover terms table & name column
        $termsTableExists = false;
        $termNameCol = null;
        try {
            $tCols = $this->db->getFieldData('terms');
            if ($tCols) {
                $termsTableExists = true;
                $names = array_map(fn($x) => strtolower($x->name), $tCols);
                if (in_array('name', $names, true))       $termNameCol = 'name';
                elseif (in_array('term_name', $names, true)) $termNameCol = 'term_name';
            }
        } catch (\Throwable $e) {
            $termsTableExists = false;
        }

        // Build select safely
        $selectParts = [
            "ts.`{$colTSId}` AS term_session_id",
            "ts.`{$colTermId}` AS term_id",
        ];
        if ($colStart) $selectParts[] = "ts.`{$colStart}` AS start_date";
        if ($colEnd)   $selectParts[] = "ts.`{$colEnd}` AS end_date";
        if ($termsTableExists && $termNameCol) $selectParts[] = "t.`{$termNameCol}` AS term_name";

        $tb = $this->db->table('terms_session ts')->select(implode(', ', $selectParts), false)
            ->where("ts.`{$colSessId}`", $sessionId);

        if ($termsTableExists && $termNameCol) {
            $tb->join('terms t', "t.`{$colTermId}` = ts.`{$colTermId}`", 'left');
        }

        if ($colTSStatus) {
            $tb->where("ts.`{$colTSStatus}`", 1);
        }

        if ($colStart) $tb->orderBy("ts.`{$colStart}`", 'ASC');
        else           $tb->orderBy("ts.`{$colTSId}`", 'ASC');

        $q = $tb->get();
        if ($q === false) {
            // Guard: if SQL failed, don’t crash the view
            // You can inspect $this->db->error() while debugging
            $terms = [];
        } else {
            $terms = $q->getResult();
        }
    }

    // Optional legacy labels map
    $clsSecLabels = [];
    foreach ($classSections as $row) {
        $id = (int) ($row['cls_sec_id'] ?? 0);
        if ($id) $clsSecLabels[$id] = (string) ($row['label'] ?? ('#' . $id));
    }

    return view('admin/quizzes/create', [
        'campusId'      => $campusId,
        'sessionId'     => $sessionId,
        'classSections' => $classSections,
        'clsSecLabels'  => $clsSecLabels,
        'terms'         => $terms, // ->term_session_id, ->term_id, ->term_name?, ->start_date?, ->end_date?
    ]);
}

public function ajaxQbQuestionsBySecSub($secSubId = 0)
{
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(400);
    }

    $secSubId = (int) $secSubId;
    if ($secSubId <= 0) {
        return $this->response->setJSON([
            'ok'  => false,
            'msg' => 'Invalid sec_sub_id',
        ]);
    }

    // optional: topic_ids[] from POST
    $topicIds = $this->request->getPost('topic_ids');
    $topicIdsClean = [];

    if (is_array($topicIds)) {
        foreach ($topicIds as $tid) {
            $tid = (int) $tid;
            if ($tid > 0) {
                $topicIdsClean[] = $tid;
            }
        }
        $topicIdsClean = array_values(array_unique($topicIdsClean));
    } elseif (is_string($topicIds) && $topicIds !== '') {
        // in case you send comma-separated string
        foreach (explode(',', $topicIds) as $tid) {
            $tid = (int) $tid;
            if ($tid > 0) {
                $topicIdsClean[] = $tid;
            }
        }
        $topicIdsClean = array_values(array_unique($topicIdsClean));
    }

    $builder = $this->db->table('qb_questions q');
    $builder->select('q.*, t.topic_name');

    $builder->join('section_subjects ss', 'ss.subject_id = q.subject_id', 'inner');
    $builder->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id AND cs.class_id = q.class_id', 'inner');
    $builder->join('qb_topics t', 't.id = q.topic_id', 'left');

    $builder->where('ss.sec_sub_id', $secSubId);

    // If some topics are selected, filter by them
    if (! empty($topicIdsClean)) {
        $builder->whereIn('q.topic_id', $topicIdsClean);
    }

    $builder->orderBy('t.topic_name', 'ASC')
            ->orderBy('q.id', 'ASC');

    $rows = $builder->get()->getResultArray();

    return $this->response->setJSON([
        'ok'   => true,
        'data' => $rows,
    ]);
}


public function ajaxQbTopicsBySecSub($secSubId = 0)
{
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(400);
    }

    $secSubId = (int) $secSubId;
    if ($secSubId <= 0) {
        return $this->response->setJSON([
            'ok'  => false,
            'msg' => 'Invalid sec_sub_id',
        ]);
    }

    // Resolve class_id + subject_id from this sec_sub_id
    $secRow = $this->db->table('section_subjects ss')
        ->select('ss.subject_id, cs.class_id')
        ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id', 'inner')
        ->where('ss.sec_sub_id', $secSubId)
        ->get()
        ->getRowArray();

    if (! $secRow) {
        return $this->response->setJSON([
            'ok'  => false,
            'msg' => 'Unable to resolve class/subject for sec_sub_id',
        ]);
    }

    $classId   = (int) $secRow['class_id'];
    $subjectId = (int) $secRow['subject_id'];

    // Get all topics for this class+subject
    $topics = $this->db->table('qb_topics')
        ->select('id, topic_name')
        ->where('class_id', $classId)
        ->where('subject_id', $subjectId)
        ->orderBy('topic_name', 'ASC')
        ->get()
        ->getResultArray();

    $topicIds = array_map(static function ($t) {
        return (int) $t['id'];
    }, $topics);

    return $this->response->setJSON([
        'ok'          => true,
        'class_id'    => $classId,
        'subject_id'  => $subjectId,
        'topics'      => $topics,
        // by default all topics selected
        'topic_ids'   => $topicIds,
    ]);
}


 public function ajaxClassSections()
    {
        if (! $this->request->isAJAX()) return $this->response->setStatusCode(400);

        $campusId = (int) ($this->request->getGet('campus_id') ?: (session('member_campusid') ?? 0));

        $rows = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.class_id, cs.section_id')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('cs.class_id')
            ->get()->getResult();

        // Optional label enrichment as above
        $classIds   = array_unique(array_map(fn($r)=> (int)$r->class_id, $rows));
        $sectionIds = array_unique(array_map(fn($r)=> (int)$r->section_id, $rows));

        $classes = [];
        if (! empty($classIds)) {
            $rs = $this->db->table('classes')->select('class_id, class_name, class_short')
                ->whereIn('class_id', $classIds)->get()->getResult();
            foreach ($rs as $r) $classes[$r->class_id] = $r;
        }

        $sections = [];
        if (! empty($sectionIds)) {
            $rs = $this->db->table('sections')->select('section_id, section_name')
                ->whereIn('section_id', $sectionIds)->get()->getResult();
            foreach ($rs as $r) $sections[$r->section_id] = $r;
        }

        $data = array_map(function($r) use ($classes,$sections){
            $c = $classes[$r->class_id] ?? null;
            $s = $sections[$r->section_id] ?? null;
            $cLabel = $c? ($c->class_short ?: $c->class_name) : ('Class '.$r->class_id);
            $sLabel = $s? $s->section_name : ('Sec '.$r->section_id);
            return [
                'cls_sec_id' => (int)$r->cls_sec_id,
                'label'      => $cLabel . ' - ' . $sLabel,
            ];
        }, $rows);

        return $this->response->setJSON(['ok'=>true,'data'=>$data]);
    }

public function ajaxSectionSubjects($clsSecId = 0)
{
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(400);
    }

    $clsSecId = (int) $clsSecId;
    if (! $clsSecId) {
        return $this->response->setJSON(['ok' => false, 'data' => []]);
    }

    $rows = $this->db->table('section_subjects ss')
        ->select('ss.sec_sub_id, ss.subject_id, s.subject_name, s.subject_short_name')
        ->join('allsubject s', 's.sid = ss.subject_id', 'left')
        ->where('ss.cls_sec_id', $clsSecId)
        ->where('ss.status', 1)
        ->get()->getResultArray();

    // Normalize for JS: name + sec_sub_id + subject_id
    $data = array_map(function($r){
        return [
            'sec_sub_id'        => (int) $r['sec_sub_id'],
            'subject_id'        => (int) $r['subject_id'],
            'name'              => $r['subject_name'] ?? $r['subject_short_name'] ?? '',
            'subject_name'      => $r['subject_name'] ?? '',
            'subject_short_name'=> $r['subject_short_name'] ?? '',
        ];
    }, $rows);

    return $this->response->setJSON([
        'ok'   => true,
        'data' => $data,
    ]);
}


     public function ajaxTermsBySession()
    {
        if (! $this->request->isAJAX()) return $this->response->setStatusCode(400);

        $sessionId = (int) ($this->request->getGet('session_id') ?: (session('academic_session_id') ?? 0));

        $rows = $this->db->table('terms_session')
            ->where('session_id', $sessionId)
            ->orderBy('term_order', 'ASC')
            ->get()->getResult();

        // If you have a terms table to resolve names:
        $termIds = array_unique(array_map(fn($r)=> (int)($r->term_id ?? 0), $rows));
        $termNames = [];
        if (! empty($termIds)) {
            $ts = $this->db->table('terms')->select('term_id, term_name')
                ->whereIn('term_id', $termIds)->get()->getResult();
            foreach ($ts as $t) $termNames[$t->term_id] = $t->term_name;
        }

        $data = array_map(function($r) use ($termNames){
            $tid = (int)($r->term_id ?? 0);
            return [
                'term_id'   => $tid,
                'term_name' => $termNames[$tid] ?? ('Term '.$tid),
            ];
        }, $rows);

        return $this->response->setJSON(['ok'=>true,'data'=>$data]);
    }
public function store()
{
    if (!$this->request->is('post')) {
        return redirect()->to(base_url('admin/quizzes/create'));
    }

    $validation = \Config\Services::validation();

    $rules = [
        'title'           => 'required|string|min_length[3]',
        'term_session_id' => 'required|integer',
        'cls_sec_id'      => 'required|integer',
        'subject_id'      => 'required|integer',
    ];

    if (! $this->validate($rules)) {
        return redirect()->back()
            ->withInput()
            ->with('validation', $validation);
    }

    // We still read campus_id/session_id from form in case you use them later
    $campusId    = (int) $this->request->getPost('campus_id');
    $sessionId   = (int) $this->request->getPost('session_id');

    $termSession = (int) $this->request->getPost('term_session_id');
    $clsSecId    = (int) $this->request->getPost('cls_sec_id');
    $secSubId    = (int) $this->request->getPost('subject_id'); // sec_sub_id

    $title        = trim((string) $this->request->getPost('title'));
    $instructions = trim((string) $this->request->getPost('instructions'));

    $timeLimitMin     = (int) ($this->request->getPost('time_limit_min') ?? 0);
    $maxAttempts      = (int) ($this->request->getPost('max_attempts') ?? 1);
    $questionsCount   = (int) ($this->request->getPost('questions_count') ?? 0);
    $perQuestionMarks = (float) ($this->request->getPost('per_question_marks') ?? 1);
    $negativePerQ     = (float) ($this->request->getPost('negative_mark_per_q') ?? 0);

      $countMcqSingle = (int) ($this->request->getPost('count_mcq_single') ?? 0);
    $countMcqMulti  = (int) ($this->request->getPost('count_mcq_multi')  ?? 0);
    $countTf        = (int) ($this->request->getPost('count_tf')         ?? 0);
    $countFill      = (int) ($this->request->getPost('count_fill')       ?? 0);
    $countShort     = (int) ($this->request->getPost('count_short')      ?? 0);
    $countMatch     = (int) ($this->request->getPost('count_match')      ?? 0);

    // never negative
    $countMcqSingle = max(0, $countMcqSingle);
    $countMcqMulti  = max(0, $countMcqMulti);
    $countTf        = max(0, $countTf);
    $countFill      = max(0, $countFill);
    $countShort     = max(0, $countShort);
    $countMatch     = max(0, $countMatch);

    $startAt = (string) $this->request->getPost('start_at');
    $endAt   = (string) $this->request->getPost('end_at');
    if ($startAt === '') $startAt = null;
    if ($endAt === '')   $endAt   = null;

    // ===== Boolean Toggles =====
    $shuffleQuestions = $this->request->getPost('shuffle_questions') ? 1 : 0;
    $shuffleOptions   = $this->request->getPost('shuffle_options') ? 1 : 0;
    $showSolution     = $this->request->getPost('show_solution') ? 1 : 0;
    $wifiOnly         = $this->request->getPost('wifi_only') ? 1 : 0;
    $isPublished      = $this->request->getPost('is_published') ? 1 : 0;

    // NEW toggles
    $isUrdu          = $this->request->getPost('is_urdu') ? 1 : 0;
    $isOrderByQtype  = $this->request->getPost('is_order_by_qtype') ? 1 : 0;

    // ===== Selected question IDs =====
    $questionIds = $this->request->getPost('question_ids');
    if (!is_array($questionIds)) {
        $questionIds = [];
    }

    // ===== Selected topics for quiz_topics =====
    $topicIds = $this->request->getPost('quiz_topic_ids');
    if (!is_array($topicIds)) {
        $topicIds = [];
    }

    // ===== Time limit column note =====
    // We treat DB column `time_limit_sec` as misnamed; store MINUTES as-is (no * 60)
    if ($timeLimitMin < 0) {
        $timeLimitMin = 0;
    }
    $timeLimitSec = $timeLimitMin * 60; 

    $db = $this->db;
    $db->transBegin();

    try {
        // IMPORTANT: still no campus_id, no session_id here (if table doesn't have them)
         $quizData = [
            'term_session_id'     => $termSession,
            'cls_sec_id'          => $clsSecId,
            'sec_sub_id'          => $secSubId,
            'title'               => $title,
            'instructions'        => $instructions,
            'time_limit_sec'      => $timeLimitSec,   // still storing minutes here by design
            'max_attempts'        => $maxAttempts,
            'questions_count'     => $questionsCount,

            'count_mcq_single'    => $countMcqSingle,
            'count_mcq_multi'     => $countMcqMulti,
            'count_tf'            => $countTf,
            'count_fill'          => $countFill,
            'count_short'         => $countShort,
            'count_match'         => $countMatch,

            'per_question_marks'  => $perQuestionMarks,
            'negative_mark_per_q' => $negativePerQ,
            'start_at'            => $startAt,
            'end_at'              => $endAt,
            'shuffle_questions'   => $shuffleQuestions,
            'shuffle_options'     => $shuffleOptions,
            'show_solution'       => $showSolution,
            'wifi_only'           => $wifiOnly,
            'is_published'        => $isPublished,
            'created_date'        => date('Y-m-d H:i:s'),
        ];

        $db->table('quizzes')->insert($quizData);
        $quizId = (int) $db->insertID();

        if ($quizId <= 0) {
            throw new \RuntimeException('Failed to create quiz record.');
        }

        // ----- Insert quiz_questions (pivot) -----
        if (!empty($questionIds)) {
            $batchQQ = [];
            $sort    = 1;
            foreach ($questionIds as $qid) {
                $qid = (int) $qid;
                if (!$qid) continue;

                $batchQQ[] = [
                    'quiz_id'      => $quizId,
                    'question_id'  => $qid,
                    'order_index'  => $sort++,
                ];
            }

            if ($batchQQ) {
                $db->table('quiz_questions')->insertBatch($batchQQ);
            }
        }

        // ----- Insert quiz_topics (pivot) -----
        if (!empty($topicIds)) {
            $topicIds   = array_unique(array_map('intval', $topicIds));
            $batchTopic = [];
            foreach ($topicIds as $tid) {
                if (!$tid) continue;
                $batchTopic[] = [
                    'quiz_id'  => $quizId,
                    'topic_id' => $tid,
                ];
            }
            if ($batchTopic) {
                $db->table('quiz_topics')->insertBatch($batchTopic);
            }
        }

        $db->transCommit();
    } catch (\Throwable $e) {
        $db->transRollback();

        // 🔍 TEMP DEBUG: show exact DB error on screen
        $err = $db->error();
        echo '<pre>';
        echo "DB ERROR CODE : " . ($err['code'] ?? 'NULL') . "\n";
        echo "DB ERROR MSG  : " . ($err['message'] ?? 'NULL') . "\n";
        echo "PHP EXCEPTION : " . $e->getMessage() . "\n";
        echo "</pre>";
        exit;

        // (after fixing, restore to redirect+flash)
        /*
        log_message('error', 'Quiz store failed: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to save quiz. Please try again.');
        */
    }

    return redirect()->to(base_url('admin/quizzes'))
        ->with('success', 'Quiz created successfully.');
}



public function ajaxByFilters()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(400)
            ->setJSON(['ok' => false, 'msg' => 'Bad request']);
    }

    $termSessionId = (int) $this->request->getGet('term_session_id');
    $clsSecId      = (int) $this->request->getGet('cls_sec_id');
    $secSubId      = (int) $this->request->getGet('sec_sub_id');

    if (!$termSessionId || !$clsSecId || !$secSubId) {
        return $this->response->setJSON([
            'ok'  => false,
            'msg' => 'Missing filters',
        ]);
    }

    $rows = $this->db->table('quizzes')
        ->select('quiz_id, title, max_attempts, questions_count, is_published, created_date')
        ->where('term_session_id', $termSessionId)
        ->where('cls_sec_id', $clsSecId)
        ->where('sec_sub_id', $secSubId)
        ->orderBy('created_date', 'DESC')
        ->limit(18)
        ->get()
        ->getResultArray();

    return $this->response->setJSON([
        'ok'   => true,
        'data' => $rows,
    ]);
}

    // public function results($quizId)
    // {
    //     $rows = $this->db->query("
    //         SELECT qa.*, s.student_name
    //         FROM quiz_attempts qa
    //         JOIN students s ON s.student_id = qa.student_id
    //         WHERE qa.quiz_id = ".$quizId."
    //         ORDER BY qa.submitted_at DESC
    //     ")->getResult();

    //     echo "<pre>";
    //     print_r($this->db->getLastQuery());
    //     echo "</pre>";
    //     exit;

    //     return view('admin/quizzes/results', ['attempts'=>$rows]);
    // }

public function results($quizId)
{
    $quizId = (int) $quizId;

    // ===== 1) Quiz header info =====
    $quiz = $this->db->table('quizzes q')
        ->select("
            q.quiz_id, q.title, q.start_at, q.end_at,
            q.cls_sec_id, q.sec_sub_id, q.term_session_id,
            CONCAT(c.class_name, ' - ', sec.section_name) AS cls_sec_name,
            subj.subject_name AS sec_sub_name,
            t.name AS term_name,
            q.created_date AS created_date
        ", false)
        ->join('class_section cs',    'cs.cls_sec_id  = q.cls_sec_id',           'left')
        ->join('classes c',           'c.class_id     = cs.class_id',            'left')
        ->join('sections sec',        'sec.section_id = cs.section_id',          'left')
        ->join('section_subjects ssub','ssub.sec_sub_id = q.sec_sub_id',         'left')
        ->join('allsubject subj',     'subj.sid       = ssub.subject_id',        'left')
        ->join('terms_session ts',    'ts.term_session_id = q.term_session_id',  'left')
        ->join('terms t',             't.term_id      = ts.term_id',             'left')
        ->where('q.quiz_id', $quizId)
        ->limit(1)
        ->get()
        ->getRow();

    if (! $quiz) {
        // basic guard
        return redirect()->to(base_url('admin/quizzes'))
            ->with('error', 'Quiz not found.');
    }

    // ===== 2) Topics for header =====
    $topics = [];
    try {
        $topicRows = $this->db->table('quiz_topics qt')
            ->select('t.topic_name')
            ->join('qb_topics t', 't.id = qt.topic_id', 'left')
            ->where('qt.quiz_id', $quiz->quiz_id)
            ->get()
            ->getResult();

        foreach ($topicRows as $tr) {
            if (! empty($tr->topic_name)) {
                $topics[] = $tr->topic_name;
            }
        }
        $topics = array_values(array_unique($topics));
    } catch (\Throwable $e) {
        $topics = [];
    }

    // ===== 3) Load attempts (with started_at for duration) =====
    $attemptsQ = $this->db->table('quiz_attempts qa')
        ->select("
            qa.attempt_id, qa.quiz_id, qa.student_id, qa.attempt_no,
            qa.status, qa.score_obtained, qa.submitted_at, qa.started_at,
            CONCAT_WS(' ', s.first_name, s.last_name) AS student_name,
            s.profile_photo
        ")
        ->join('students s', 's.student_id = qa.student_id', 'left')
        ->where('qa.quiz_id', $quizId)
        ->get();

    $attempts = $attemptsQ ? $attemptsQ->getResult() : [];

    // ===== 4) Total students in class-section =====
    $totalStudents = null;
    if (! empty($quiz->cls_sec_id)) {
        $qTotal = $this->db->table('student_class')
            ->where('cls_sec_id', (int) $quiz->cls_sec_id)
            ->where('status', 1)
            ->countAllResults();
        $totalStudents = (int) $qTotal;
    }

    // ===== 5) Quiz questions (for total marks & total questions) =====
    $quizQuestions = $this->db->table('quiz_questions')
        ->select('question_id, marks')
        ->where('quiz_id', $quizId)
        ->get()
        ->getResult();

    $totalMarks      = 0.0;
    $totalQuestions  = 0;
    $questionIds     = [];

    if (! empty($quizQuestions)) {
        foreach ($quizQuestions as $qRow) {
            $totalQuestions++;
            $totalMarks += (float) ($qRow->marks ?? 0);
            $questionIds[] = (int) $qRow->question_id;
        }
    }

    // ===== 6) All answers for all attempts (for per-attempt stats) =====
    $attemptIds = [];
    foreach ($attempts as $a) {
        $attemptIds[] = (int) $a->attempt_id;
    }
    $attemptIds = array_values(array_unique($attemptIds));

    $answersByAttempt = [];
    if (! empty($attemptIds)) {
        $ansRows = $this->db->table('quiz_attempt_answers')
            ->select('attempt_id, question_id, is_correct')
            ->whereIn('attempt_id', $attemptIds)
            ->get()
            ->getResult();

        foreach ($ansRows as $row) {
            $aid = (int) $row->attempt_id;
            if (! isset($answersByAttempt[$aid])) {
                $answersByAttempt[$aid] = [];
            }
            $answersByAttempt[$aid][] = $row;
        }
    }

    // ===== 7) Compute per-attempt stats (total, correct, wrong, unattempted, percentage, duration) =====
    if (! empty($attempts)) {
        foreach ($attempts as $a) {
            $aid = (int) $a->attempt_id;

            $correct      = 0;
            $wrong        = 0;
            $attemptedCnt = 0;

            if (! empty($answersByAttempt[$aid])) {
                // assuming 1 row per (attempt, question)
                $seenQ = [];
                foreach ($answersByAttempt[$aid] as $ansRow) {
                    $qid = (int) $ansRow->question_id;
                    if ($qid <= 0 || isset($seenQ[$qid])) {
                        continue;
                    }
                    $seenQ[$qid] = true;
                    $attemptedCnt++;

                    if ((int) $ansRow->is_correct === 1) {
                        $correct++;
                    } else {
                        $wrong++;
                    }
                }
            }

            $unattempted = max(0, $totalQuestions - $attemptedCnt);

            $score       = (float) ($a->score_obtained ?? 0);
            $percentage  = ($totalMarks > 0)
                ? round(($score / $totalMarks) * 100, 1)
                : null;

            // duration
            $durationText = '';
            $startRaw     = $a->started_at ?? null;
            $endRaw       = $a->submitted_at ?? null;

            if ($startRaw && $endRaw) {
                $startTs = strtotime($startRaw);
                $endTs   = strtotime($endRaw);
                if ($startTs && $endTs && $endTs > $startTs) {
                    $diff = $endTs - $startTs;
                    $mins = floor($diff / 60);
                    $secs = $diff % 60;
                    $durationText = sprintf('%d min %02d sec', $mins, $secs);
                }
            }

            // attach computed fields to attempt object for use in view
            $a->total_questions = $totalQuestions;
            $a->total_marks     = $totalMarks;
            $a->stat_correct    = $correct;
            $a->stat_wrong      = $wrong;
            $a->stat_unattempted= $unattempted;
            $a->percentage      = $percentage;
            $a->duration_text   = $durationText;
        }

        // ===== 8) Sort attempts: by percentage desc, then score desc =====
        usort($attempts, static function ($x, $y) {
            $px = $x->percentage ?? -1;
            $py = $y->percentage ?? -1;

            if ($px == $py) {
                return ($y->score_obtained <=> $x->score_obtained); // score desc
            }
            return ($py <=> $px); // percentage desc
        });
    }

    // ===== 9) Aggregate stats for header (avg, participation) =====
    $attemptCount  = is_array($attempts) ? count($attempts) : 0;
    $avgScore      = '—';
    if (! empty($attempts)) {
        $sum = 0; $n = 0;
        foreach ($attempts as $a) {
            if (isset($a->score_obtained) && $a->score_obtained !== null && $a->score_obtained !== '') {
                $sum += (float) $a->score_obtained; $n++;
            }
        }
        if ($n > 0) $avgScore = number_format($sum / $n, 2);
    }

    $participation = '—';
    if ($totalStudents && $totalStudents > 0) {
        $participation = number_format(($attemptCount / $totalStudents) * 100, 1) . '%';
    }

    // You can pass avgScore & participation directly or recompute in view as before
    return view('admin/quizzes/results_cards', [
        'quiz'          => $quiz,
        'attempts'      => $attempts,
        'totalStudents' => $totalStudents,
        'topics'        => $topics,
        'totalMarks'    => $totalMarks,
        'avgScore'      => $avgScore,
        'participation' => $participation,
        'attemptCount'  => $attemptCount,
    ]);
}

}
