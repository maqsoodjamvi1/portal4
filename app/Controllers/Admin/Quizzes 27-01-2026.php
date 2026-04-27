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


// public function index()
// {
//     $db = db_connect();

//     $sessionId = (int) ($this->session->get('member_sessionid')
//         ?? $this->session->get('academic_session_id')
//         ?? 0);

//     $sql = "
//     SELECT
//         q.quiz_id,
//         q.title,
//         q.cls_sec_id,
//         q.sec_sub_id,
//         q.term_session_id,
//         q.instructions,
//         q.time_limit_sec,
//         q.start_at,
//         q.end_at,
//         q.max_attempts,
//         q.shuffle_questions,
//         q.shuffle_options,
//         q.show_solution,
//         q.negative_mark_per_q,
//         q.is_published,

//         /* ✅ counts stored in quizzes table (PLANNED counts) */
//         COALESCE(q.count_mcq_single, 0) AS plan_mcq_single,
//         COALESCE(q.count_mcq_multi, 0)  AS plan_mcq_multi,
//         COALESCE(q.count_tf, 0)         AS plan_tf,
//         COALESCE(q.count_fill, 0)       AS plan_fill,
//         COALESCE(q.count_short, 0)      AS plan_short,
//         COALESCE(q.count_match, 0)      AS plan_match,

//         /* ✅ Calculate planned total */
//         (COALESCE(q.count_mcq_single, 0) + 
//          COALESCE(q.count_mcq_multi, 0) + 
//          COALESCE(q.count_tf, 0) + 
//          COALESCE(q.count_fill, 0) + 
//          COALESCE(q.count_short, 0) + 
//          COALESCE(q.count_match, 0)) AS planned_total_questions,

//         /* ✅ ACTUAL question type counts from qb_questions */
//         COALESCE(qtypes.actual_mcq, 0)       AS actual_mcq_single,
//         COALESCE(qtypes.actual_mcq_multi, 0) AS actual_mcq_multi,
//         COALESCE(qtypes.actual_fill, 0)      AS actual_fill,
//         COALESCE(qtypes.actual_tf, 0)        AS actual_tf,
//         COALESCE(qtypes.actual_short, 0)     AS actual_short,
//         COALESCE(qtypes.actual_match, 0)     AS actual_match,
//         COALESCE(qtypes.actual_total, 0)     AS actual_total_questions,

//         CONCAT(
//             COALESCE(c.class_short_name, c.class_name, 'Class'),
//             ' - ',
//             COALESCE(sec.section_name, 'Section')
//         ) AS cls_sec_name,

//         COALESCE(subj.subject_short_name, subj.subject_name, 'Subject') AS sec_sub_name,

//         /* ✅ Term session name */
//         CONCAT(t.name, ' - ', ac.session_name) AS term_session_name,

//         /* ✅ Topics from actual questions */
//         COALESCE(qs.topic_names, '') AS topic_names,

//         /* ✅ Attempt counts per attempt */
//         COALESCE(ax.attempt_1_count, 0) AS attempt_1_count,
//         COALESCE(ax.attempt_2_count, 0) AS attempt_2_count,
//         COALESCE(ax.attempt_3_count, 0) AS attempt_3_count,
//         COALESCE(ax.attempt_4_count, 0) AS attempt_4_count,
//         COALESCE(ax.attempt_5_count, 0) AS attempt_5_count,
//         COALESCE(ax.attempt_6_count, 0) AS attempt_6_count,
//         COALESCE(ax.attempt_7_count, 0) AS attempt_7_count,
//         COALESCE(ax.attempt_8_count, 0) AS attempt_8_count,
//         COALESCE(ax.attempt_9_count, 0) AS attempt_9_count,
//         COALESCE(ax.attempt_10_count, 0) AS attempt_10_count,
//         COALESCE(ax.total_attempted_students, 0) AS attempted_students_count,

//         COALESCE(csx.class_student_count, 0) AS class_student_count

//     FROM quizzes q
//     LEFT JOIN class_section cs ON cs.cls_sec_id = q.cls_sec_id
//     LEFT JOIN classes c        ON c.class_id = cs.class_id
//     LEFT JOIN sections sec     ON sec.section_id = cs.section_id
//     LEFT JOIN section_subjects ssub ON ssub.sec_sub_id = q.sec_sub_id
//     LEFT JOIN allsubject subj       ON subj.sid = ssub.subject_id
    
//     /* ✅ Term session joins */
//     LEFT JOIN terms_session ts       ON ts.term_session_id = q.term_session_id
//     LEFT JOIN terms t                ON t.term_id = ts.term_id
//     LEFT JOIN academic_session ac    ON ac.session_id = ts.session_id

//     /* ✅ Actual question type counts from qb_questions */
//     LEFT JOIN (
//         SELECT
//             qq.quiz_id,
//             SUM(CASE WHEN qb.question_type = 'mcq' THEN 1 ELSE 0 END) AS actual_mcq,
//             SUM(CASE WHEN qb.question_type = 'mcq_multi' THEN 1 ELSE 0 END) AS actual_mcq_multi,
//             SUM(CASE WHEN qb.question_type = 'fill' THEN 1 ELSE 0 END) AS actual_fill,
//             SUM(CASE WHEN qb.question_type = 'tf' THEN 1 ELSE 0 END) AS actual_tf,
//             SUM(CASE WHEN qb.question_type = 'short' THEN 1 ELSE 0 END) AS actual_short,
//             SUM(CASE WHEN qb.question_type = 'match' THEN 1 ELSE 0 END) AS actual_match,
//             COUNT(*) AS actual_total
//         FROM quiz_questions qq
//         INNER JOIN qb_questions qb ON qb.id = qq.question_id
//         GROUP BY qq.quiz_id
//     ) qtypes ON qtypes.quiz_id = q.quiz_id

//     /* ✅ Topics */
//     LEFT JOIN (
//         SELECT
//             qq.quiz_id,
//             GROUP_CONCAT(DISTINCT t.topic_name ORDER BY t.topic_name SEPARATOR ', ') AS topic_names
//         FROM quiz_questions qq
//         INNER JOIN qb_questions qb ON qb.id = qq.question_id
//         LEFT JOIN qb_topics t      ON t.id = qb.topic_id
//         GROUP BY qq.quiz_id
//     ) qs ON qs.quiz_id = q.quiz_id

//     /* ✅ Students in class */
//     LEFT JOIN (
//         SELECT
//             sc.cls_sec_id,
//             COUNT(DISTINCT sc.student_id) AS class_student_count
//         FROM student_class sc
//         WHERE sc.status = 1 AND sc.session_id = {$sessionId}
//         GROUP BY sc.cls_sec_id
//     ) csx ON csx.cls_sec_id = q.cls_sec_id

//     /* ✅ Attempt counts per attempt number */
//     LEFT JOIN (
//         SELECT
//             qa.quiz_id,
//             COUNT(DISTINCT qa.student_id) AS total_attempted_students,
//             SUM(CASE WHEN qa.attempt_no = 1 THEN 1 ELSE 0 END) AS attempt_1_count,
//             SUM(CASE WHEN qa.attempt_no = 2 THEN 1 ELSE 0 END) AS attempt_2_count,
//             SUM(CASE WHEN qa.attempt_no = 3 THEN 1 ELSE 0 END) AS attempt_3_count,
//             SUM(CASE WHEN qa.attempt_no = 4 THEN 1 ELSE 0 END) AS attempt_4_count,
//             SUM(CASE WHEN qa.attempt_no = 5 THEN 1 ELSE 0 END) AS attempt_5_count,
//             SUM(CASE WHEN qa.attempt_no = 6 THEN 1 ELSE 0 END) AS attempt_6_count,
//             SUM(CASE WHEN qa.attempt_no = 7 THEN 1 ELSE 0 END) AS attempt_7_count,
//             SUM(CASE WHEN qa.attempt_no = 8 THEN 1 ELSE 0 END) AS attempt_8_count,
//             SUM(CASE WHEN qa.attempt_no = 9 THEN 1 ELSE 0 END) AS attempt_9_count,
//             SUM(CASE WHEN qa.attempt_no = 10 THEN 1 ELSE 0 END) AS attempt_10_count
//         FROM quiz_attempts qa
//         GROUP BY qa.quiz_id
//     ) ax ON ax.quiz_id = q.quiz_id

//     ORDER BY q.start_at DESC
//     ";

//     $quizzes = $db->query($sql)->getResult();

//     // Prepare filter options
//     $clsOptions = [];
//     $subjOptions = [];
//     $termOptions = [];

//     foreach ($quizzes as $q) {
//         $clsId = (int)($q->cls_sec_id ?? 0);
//         $clsLbl = trim($q->cls_sec_name ?? ('Class-Section #'.$clsId));
//         if ($clsId) $clsOptions[$clsId] = $clsLbl;

//         $subLbl = trim($q->sec_sub_name ?? 'Subject');
//         if ($subLbl !== '') $subjOptions[$subLbl] = $subLbl;

//         $tsid = (int)($q->term_session_id ?? 0);
//         $tsName = trim($q->term_session_name ?? 'Term Session #'.$tsid);
//         if ($tsid) $termOptions[$tsid] = $tsName;
//     }

//     // Sort options
//     ksort($clsOptions);
//     ksort($subjOptions);
//     ksort($termOptions);

//     return view('admin/quizzes/index_cards', [
//         'quizzes' => $quizzes,
//         'clsOptions' => $clsOptions,
//         'subjOptions' => $subjOptions,
//         'termOptions' => $termOptions
//     ]);
// }


//app/Controllers/Admin/Quizzes.php (index)
public function index()
{
    $db = db_connect();

    // Debug: Check what session values exist
    $sessionId = (int) ($this->session->get('member_sessionid')
        ?? $this->session->get('academic_session_id')
        ?? 0);
    
    log_message('debug', 'Using sessionId: ' . $sessionId);

    // First, let's test with a SIMPLE query
    $testQuery = "SHOW TABLES LIKE 'quizzes'";
    $tablesExist = $db->query($testQuery)->getResult();
    
    if (empty($tablesExist)) {
        log_message('error', 'Quizzes table does not exist');
        return view('admin/quizzes/index_cards', ['quizzes' => []]);
    }

    // SIMPLIFIED VERSION - Build query step by step
    $sql = "
    SELECT
        q.quiz_id,
        q.title,
        q.cls_sec_id,
        q.sec_sub_id,
        q.term_session_id,
        q.instructions,
        q.time_limit_sec,
        q.start_at,
        q.end_at,
        q.max_attempts,
        q.shuffle_questions,
        q.shuffle_options,
        q.show_solution,
        q.negative_mark_per_q,
        q.is_published,
        
        -- Basic planned counts (check if these columns exist)
        IFNULL(q.count_mcq_single, 0) AS plan_mcq_single,
        IFNULL(q.count_mcq_multi, 0) AS plan_mcq_multi,
        IFNULL(q.count_tf, 0) AS plan_tf,
        IFNULL(q.count_fill, 0) AS plan_fill,
        IFNULL(q.count_short, 0) AS plan_short,
        IFNULL(q.count_match, 0) AS plan_match,
        
        -- Class and section names
        COALESCE(c.class_short_name, c.class_name, 'Class') AS class_name_part,
        COALESCE(sec.section_name, 'Section') AS section_name_part,
        
        -- Subject name
        COALESCE(subj.subject_short_name, subj.subject_name, 'Subject') AS sec_sub_name,
        
        -- Term session
        COALESCE(t.name, '') AS term_name,
        COALESCE(ac.session_name, '') AS session_name
        
    FROM quizzes q
    
    -- Basic joins only
    LEFT JOIN class_section cs ON cs.cls_sec_id = q.cls_sec_id
    LEFT JOIN classes c ON c.class_id = cs.class_id
    LEFT JOIN sections sec ON sec.section_id = cs.section_id
    LEFT JOIN section_subjects ssub ON ssub.sec_sub_id = q.sec_sub_id
    LEFT JOIN allsubject subj ON subj.sid = ssub.subject_id
    LEFT JOIN terms_session ts ON ts.term_session_id = q.term_session_id
    LEFT JOIN terms t ON t.term_id = ts.term_id
    LEFT JOIN academic_session ac ON ac.session_id = ts.session_id
    
    WHERE 1=1
    ORDER BY q.cls_sec_id, q.start_at DESC
    ";

    try {
        log_message('debug', 'Executing simplified query');
        $result = $db->query($sql);
        
        if (!$result) {
            log_message('error', 'Query execution failed');
            return view('admin/quizzes/index_cards', ['quizzes' => []]);
        }
        
        $quizzes = $result->getResult();
        log_message('debug', 'Number of quizzes found: ' . count($quizzes));
        
        if (!empty($quizzes)) {
            // Debug first quiz structure
            $firstQuiz = $quizzes[0];
            $fields = get_object_vars($firstQuiz);
            log_message('debug', 'First quiz fields: ' . implode(', ', array_keys($fields)));
            
            // Process quizzes to add calculated fields
            foreach ($quizzes as $quiz) {
                // Build class section name
                $quiz->cls_sec_name = $quiz->class_name_part . ' - ' . $quiz->section_name_part;
                
                // Build term session name
                $quiz->term_session_name = trim($quiz->term_name . ' - ' . $quiz->session_name);
                if ($quiz->term_session_name === ' - ') {
                    $quiz->term_session_name = '';
                }
                
                // Set defaults for missing fields
                $quiz->actual_mcq = 0;
                $quiz->actual_mcq_multi = 0;
                $quiz->actual_fill = 0;
                $quiz->actual_tf = 0;
                $quiz->actual_short = 0;
                $quiz->actual_match = 0;
                $quiz->actual_total_questions = 0;
                $quiz->topic_names = '';
                $quiz->attempt_1_count = 0;
                $quiz->attempt_2_count = 0;
                $quiz->attempt_3_count = 0;
                $quiz->attempt_4_count = 0;
                $quiz->attempt_5_count = 0;
                $quiz->attempt_6_count = 0;
                $quiz->attempt_7_count = 0;
                $quiz->attempt_8_count = 0;
                $quiz->attempt_9_count = 0;
                $quiz->attempt_10_count = 0;
                $quiz->attempted_students_count = 0;
                $quiz->class_student_count = 0;
            }
        }
        
        // Check if view file exists
        $viewPath = APPPATH . 'Views/admin/quizzes/index_cards.php';
        if (!file_exists($viewPath)) {
            log_message('error', 'View file not found: ' . $viewPath);
            // Create a simple response
            echo "<h1>Quizzes</h1>";
            echo "<p>Found " . count($quizzes) . " quizzes</p>";
            if (!empty($quizzes)) {
                echo "<ul>";
                foreach ($quizzes as $quiz) {
                    echo "<li>{$quiz->title} (ID: {$quiz->quiz_id})</li>";
                }
                echo "</ul>";
            }
            return;
        }
        
        return view('admin/quizzes/index_cards', [
            'quizzes' => $quizzes
        ]);
        
    } catch (\Exception $e) {
        log_message('error', 'Quiz query failed: ' . $e->getMessage());
        log_message('error', 'Trace: ' . $e->getTraceAsString());
        
        // Try an even simpler query
        try {
            $simpleQuery = "SELECT quiz_id, title, is_published FROM quizzes LIMIT 10";
            $quizzes = $db->query($simpleQuery)->getResult();
            log_message('debug', 'Simple query successful, found: ' . count($quizzes));
            
            return view('admin/quizzes/index_cards', [
                'quizzes' => $quizzes
            ]);
        } catch (\Exception $e2) {
            log_message('error', 'Even simple query failed: ' . $e2->getMessage());
            return view('admin/quizzes/index_cards', [
                'quizzes' => []
            ]);
        }
    }
}

public function editQuestions($quizId)
{
    $db = db_connect();
    
    // Get quiz details
    $quiz = $db->table('quizzes')
               ->where('quiz_id', $quizId)
               ->get()
               ->getRow();
    
    if (!$quiz) {
        return redirect()->back()->with('error', 'Quiz not found.');
    }
    
    // Get all questions for this quiz
    $questions = $db->table('quiz_questions qq')
                   ->select('qq.*, qb.*')
                   ->join('qb_questions qb', 'qb.id = qq.question_id')
                   ->where('qq.quiz_id', $quizId)
                   ->orderBy('qq.id', 'asc')
                   ->get()
                   ->getResult();
    
    // Get class, subject, topic info from quiz
    $quizInfo = $db->query("
        SELECT 
            q.cls_sec_id,
            q.sec_sub_id,
            cs.class_id,
            cs.section_id,
            c.class_name,
            c.class_short_name,
            s.section_name,
            ss.subject_id,
            subj.subject_name,
            subj.subject_short_name
        FROM quizzes q
        LEFT JOIN class_section cs ON cs.cls_sec_id = q.cls_sec_id
        LEFT JOIN classes c ON c.class_id = cs.class_id
        LEFT JOIN sections s ON s.section_id = cs.section_id
        LEFT JOIN section_subjects ss ON ss.sec_sub_id = q.sec_sub_id
        LEFT JOIN allsubject subj ON subj.sid = ss.subject_id
        WHERE q.quiz_id = ?
    ", [$quizId])->getRow();
    
    // Get all topics for the subject
    $topics = [];
    if ($quizInfo && $quizInfo->subject_id) {
        $topics = $db->table('qb_topics')
                    ->where('class_id', $quizInfo->class_id ?? 0)
                    ->where('subject_id', $quizInfo->subject_id)
                    ->orderBy('topic_name', 'asc')
                    ->get()
                    ->getResult();
    }
    
    // Get all classes for dropdown
    $classes = $db->table('classes')
                 ->orderBy('class_name', 'asc')
                 ->get()
                 ->getResult();
    
    // Get subjects for the class section (cls_sec_id)
    $subjects = [];
    if ($quizInfo && $quizInfo->cls_sec_id) {
        $subjects = $db->query("
            SELECT DISTINCT s.*
            FROM section_subjects ss
            JOIN allsubject s ON s.sid = ss.subject_id
            WHERE ss.cls_sec_id = ?
            ORDER BY s.subject_name
        ", [$quizInfo->cls_sec_id])->getResult();
    }
    
    // Prepare questions data for the form
    $formattedQuestions = [];
    foreach ($questions as $q) {
        $questionData = [
            'id' => $q->id,
            'question_id' => $q->question_id,
            'question_type' => $q->question_type,
            'question' => $q->question,
            'question_media' => $q->question_media ?? 'text',
            'question_image' => $q->question_image,
            'question_image_alt' => $q->question_image_alt,
            'difficulty' => $q->difficulty ?? 'normal',
            'option_a' => $q->option_a,
            'option_b' => $q->option_b,
            'option_c' => $q->option_c,
            'option_d' => $q->option_d,
            'correct_option' => $q->correct_option,
            'answer_text' => $q->answer_text,
            'options_json' => $q->options_json,
            'is_drag' => $q->is_drag ?? 0
        ];
        
        // Parse JSON for MCQ multi and match questions
        if ($q->question_type === 'mcq_multi' && $q->options_json) {
            $jsonData = json_decode($q->options_json, true);
            if ($jsonData) {
                $questionData['correct_multi'] = $jsonData['correct_multi'] ?? [];
            }
        } elseif ($q->question_type === 'match' && $q->options_json) {
            $jsonData = json_decode($q->options_json, true);
            $questionData['match_pairs'] = is_array($jsonData) ? $jsonData : [];
        }
        
        $formattedQuestions[] = $questionData;
    }
    
    return view('admin/quizzes/edit_questions', [
        'quiz' => $quiz,
        'quizInfo' => $quizInfo,
        'questions' => $formattedQuestions,
        'classes' => $classes,
        'subjects' => $subjects,
        'topics' => $topics,
        'quizId' => $quizId
    ]);
}

public function updateQuestions($quizId)
{
    $quizId = (int) $quizId;
    
    // Verify quiz exists
    $quiz = $this->db->table('quizzes')
        ->where('quiz_id', $quizId)
        ->get()
        ->getRow();
    
    if (!$quiz) {
        return redirect()->back()->with('error', 'Quiz not found.');
    }
    
    $validation = \Config\Services::validation();
    
    // Basic validation
    $rules = [
        'questions' => 'required|is_array'
    ];
    
    if (!$this->validate($rules)) {
        return redirect()->back()
            ->withInput()
            ->with('validation', $validation);
    }
    
    // ✅ Upload directory (same as save method)
    $uploadDir = WRITEPATH . 'uploads/qb_questions';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }
    
    $this->db->transBegin();
    
    try {
        $questions = $this->request->getPost('questions');
        $updatedQuestions = 0;
        $skipped = 0;
        
        // Get all existing quiz questions to verify they belong to this quiz
        $existingQuizQuestionIds = $this->db->table('quiz_questions')
            ->select('question_id')
            ->where('quiz_id', $quizId)
            ->get()
            ->getResultArray();
        
        $existingQuestionIds = array_column($existingQuizQuestionIds, 'question_id');
        
        foreach ($questions as $idx => $qData) {
            $questionId = (int) ($qData['id'] ?? 0);
            
            // Check if this question belongs to the quiz
            if (!in_array($questionId, $existingQuestionIds)) {
                $skipped++;
                log_message('warning', "Skip idx={$idx}: Question ID {$questionId} not found in quiz {$quizId}");
                continue;
            }
            
            $type = trim((string) ($qData['question_type'] ?? 'mcq'));
            $difficulty = trim((string) ($qData['difficulty'] ?? 'normal'));
            
            // ✅ question mode
            $questionMedia = trim((string) ($qData['question_media'] ?? 'text'));
            if (!in_array($questionMedia, ['text', 'image'], true)) {
                $questionMedia = 'text';
            }
            
            $questionText = trim((string) ($qData['question'] ?? ''));
            $imageAlt = trim((string) ($qData['question_image_alt'] ?? ''));
            
            // ✅ file handling
            $files = $this->request->getFiles();
            $file = $files['questions'][$idx]['question_image'] ?? null;
            
            if (!$file) {
                $file = $this->request->getFile("questions.$idx.question_image");
            }
            
            // ✅ Validation
            $hasError = false;
            
            if ($questionMedia === 'text') {
                if ($questionText === '') {
                    $hasError = true;
                    log_message('warning', "Skip idx={$idx}: question text empty (text mode)");
                }
            } else { // image mode
                $hasNewFile = $file && $file->isValid();
                
                // For update, existing image might already exist, so we don't require new file
                if (!$hasNewFile) {
                    // Check if existing question has image
                    $existingQuestion = $this->db->table('qb_questions')
                        ->select('question_image')
                        ->where('id', $questionId)
                        ->get()
                        ->getRow();
                    
                    if (empty($existingQuestion->question_image)) {
                        $hasError = true;
                        log_message('warning', "Skip idx={$idx}: no existing image and no new image provided");
                    }
                } else {
                    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                    $mime = $file->getMimeType();
                    $sizeOk = ($file->getSize() <= 2 * 1024 * 1024); // 2MB
                    
                    if (!in_array($mime, $allowed, true) || !$sizeOk) {
                        $hasError = true;
                        log_message('warning', "Skip idx={$idx}: invalid image mime/size");
                    }
                }
            }
            
            if ($hasError) {
                $skipped++;
                continue;
            }
            
            // ============ UPDATE EXISTING QUESTION IN QB_QUESTIONS ONLY ============
            $existingQuestion = $this->db->table('qb_questions')
                ->where('id', $questionId)
                ->get()
                ->getRow();
            
            if (!$existingQuestion) {
                $skipped++;
                log_message('warning', "Skip idx={$idx}: Question ID {$questionId} not found in qb_questions");
                continue;
            }
            
            // Base update data - preserve class/subject/topic from existing question
            $data = [
                'question_type' => $type,
                'difficulty' => $difficulty,
                'question_media' => $questionMedia,
                'question_image_alt' => ($imageAlt !== '' ? $imageAlt : null),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Drag flag (for match type)
            $data['is_drag'] = (isset($qData['is_drag']) && $qData['is_drag'] == '1') ? 1 : 0;
            
            // Reset fields based on type
            $data['option_a'] = null;
            $data['option_b'] = null;
            $data['option_c'] = null;
            $data['option_d'] = null;
            $data['correct_option'] = null;
            $data['answer_text'] = null;
            $data['options_json'] = null;
            
            // ✅ Apply question text/image
            if ($questionMedia === 'text') {
                $data['question'] = $questionText;
                // If switching from image to text, remove the image
                if (!empty($existingQuestion->question_image)) {
                    $oldImagePath = WRITEPATH . $existingQuestion->question_image;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $data['question_image'] = null;
            } else {
                $data['question'] = null;
                
                if ($file && $file->isValid()) {
                    try {
                        // Delete old image if exists
                        if (!empty($existingQuestion->question_image)) {
                            $oldImagePath = WRITEPATH . $existingQuestion->question_image;
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                        
                        // Upload new image
                        $newName = $file->getRandomName();
                        $file->move($uploadDir, $newName);
                        $data['question_image'] = 'uploads/qb_questions/' . $newName;
                        
                    } catch (\Throwable $e) {
                        log_message('error', "Update image upload failed: " . $e->getMessage());
                        // Keep existing image if upload fails
                        $data['question_image'] = $existingQuestion->question_image;
                    }
                } else {
                    // Keep existing image if no new file
                    $data['question_image'] = $existingQuestion->question_image;
                }
            }
            
            // Handle question type specific fields
            $this->processQuestionData($type, $qData, $data);
            
            // Update ONLY the question in qb_questions
            $this->db->table('qb_questions')
                ->where('id', $questionId)
                ->update($data);
            
            $updatedQuestions++;
        }
        
        // DO NOT update quiz_questions table - keep existing relationships
        
        // Update quiz questions count (should remain the same since we're not adding/removing)
        // But update timestamp to show quiz was modified
        $this->db->table('quizzes')
            ->where('quiz_id', $quizId)
            ->update([
                'updated_date' => date('Y-m-d H:i:s')
            ]);
        
        $this->db->transCommit();
        
        $message = "Successfully updated {$updatedQuestions} question(s) in question bank.";
        if ($skipped > 0) {
            $message .= " Skipped: {$skipped} question(s) due to validation errors.";
        }
        
        return redirect()->back()->with('success', $message);
        
    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', 'Quiz questions update failed: ' . $e->getMessage());
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to update questions: ' . $e->getMessage());
    }
}

/**
 * Helper method to process question type specific data
 */
private function processQuestionData($type, $qData, &$data)
{
    switch ($type) {
        case 'mcq':
            $data['option_a'] = trim($qData['option_a'] ?? '');
            $data['option_b'] = trim($qData['option_b'] ?? '');
            $data['option_c'] = trim($qData['option_c'] ?? '');
            $data['option_d'] = trim($qData['option_d'] ?? '');
            $data['correct_option'] = strtoupper(trim($qData['correct_option'] ?? ''));
            break;
            
        case 'true_false':
            $data['correct_option'] = trim($qData['correct_option'] ?? '');
            break;
            
        case 'fill':
            $data['answer_text'] = trim($qData['answer_text'] ?? '');
            break;
            
        case 'short':
            $data['answer_text'] = trim($qData['answer_text'] ?? '');
            break;
            
        case 'match':
            // For match type, store options in JSON
            $pairs = [];
            if (isset($qData['match_pairs']) && is_array($qData['match_pairs'])) {
                foreach ($qData['match_pairs'] as $pair) {
                    if (!empty($pair['left']) && !empty($pair['right'])) {
                        $pairs[] = [
                            'left' => trim($pair['left']),
                            'right' => trim($pair['right'])
                        ];
                    }
                }
            }
            if (!empty($pairs)) {
                $data['options_json'] = json_encode($pairs);
            }
            break;
            
        case 'multi':
            $options = [];
            $correct = [];
            
            for ($i = 1; $i <= 4; $i++) {
                $optionKey = 'option_' . chr(96 + $i); // a, b, c, d
                $correctKey = 'correct_' . chr(96 + $i);
                
                if (!empty($qData[$optionKey])) {
                    $options[] = trim($qData[$optionKey]);
                    if (isset($qData[$correctKey]) && $qData[$correctKey] == '1') {
                        $correct[] = chr(64 + $i); // A, B, C, D
                    }
                }
            }
            
            if (!empty($options)) {
                $data['options_json'] = json_encode([
                    'options' => $options,
                    'correct' => $correct
                ]);
            }
            break;
    }
}
/**
 * Helper method to process question data based on type
 */

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


public function printAll($quizId)
{
    helper('text');
    $quizId = (int) $quizId;

    // ===== Load quiz header (same as your existing code) =====
    $quiz = $this->db->table('quizzes q')
        ->select("
            q.*,
            CONCAT(c.class_name, ' - ', sec.section_name) AS cls_sec_name,
            subj.subject_name AS sec_sub_name
        ", false)
        ->join('class_section cs', 'cs.cls_sec_id = q.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->join('section_subjects ssub', 'ssub.sec_sub_id = q.sec_sub_id', 'left')
        ->join('allsubject subj', 'subj.sid = ssub.subject_id', 'left')
        ->where('q.quiz_id', $quizId)
        ->get()
        ->getRow();

    if (!$quiz) {
        return redirect()->to(base_url('admin/quizzes'))
            ->with('error', 'Quiz not found.');
    }

    // ===== Load ALL questions (NO LIMIT, NO SHUFFLE) =====
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
            q.options_json,
             q.correct_option,
             q.answer_text
        ")
        ->join('qb_questions q', 'q.id = qq.question_id', 'left')
        ->where('qq.quiz_id', $quizId)
        ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false)
        ->get()
        ->getResult();

    if (empty($rows)) {
        return redirect()->to(base_url('admin/quizzes'))
            ->with('error', 'No questions found.');
    }

    // ===== Type labels (same as before) =====
    foreach ($rows as $r) {
        switch (strtolower($r->question_type)) {
            case 'mcq':
            case 'mcq_single':   $r->type_label = 'MCQ (Single)'; break;
            case 'mcq_multi':    $r->type_label = 'MCQ (Multiple)'; break;
            case 'true_false':
            case 'tf':           $r->type_label = 'True / False'; break;
            case 'fill':
            case 'fill_blank':   $r->type_label = 'Fill in the Blanks'; break;
            case 'short':
            case 'short_answer': $r->type_label = 'Short Answer'; break;
            case 'match':        $r->type_label = 'Match the Column'; break;
            default:             $r->type_label = ucfirst($r->question_type);
        }
    }

    return view('admin/quizzes/print_quiz', [
        'quiz'      => $quiz,
        'questions' => $rows,
        'printMode' => 'all' // optional flag
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



public function ajaxQbSummary()
{
    try {
        $systemId = 1;
        $classId = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');
        //$subjectId  = 3192;
        $builder = $this->db->table('qb_questions q');
        
        $builder->select([
            'q.class_id',
            'q.subject_id',
            'q.topic_id',
            'c.class_name',
            's.subject_name',
            't.topic_name',
            'COUNT(q.id) AS question_count'
        ]);

        // Join with classes table
        $builder->join(
            'classes c',
            'c.class_id = q.class_id AND c.system_id = '.$systemId.' AND c.status = 1',
            'inner'
        );

        // Join with subjects table
        $builder->join(
            'allsubject s',
            's.sid = q.subject_id AND s.system_id = '.$systemId.' AND s.status = 1',
            'inner'
        );

        // Join with topics table
        $builder->join(
            'qb_topics t',
            't.id = q.topic_id',
            'inner'
        );

        // Apply filters ONLY if provided
        if ($classId > 0) {
            $builder->where('q.class_id', $classId);
        }
        
        if ($subjectId > 0) {
            $builder->where('q.subject_id', $subjectId);
        }

        // Group by and order
        $builder->groupBy([
            'q.class_id',
            'q.subject_id',
            'q.topic_id',
            'c.class_name',
            's.subject_name',
            't.topic_name'
        ]);

        $builder->orderBy('c.class_name', 'ASC');
        $builder->orderBy('s.subject_name', 'ASC');
        $builder->orderBy('t.topic_name', 'ASC');

        $result = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'ok'   => true,
            'data' => $result
        ]);

    } catch (\Throwable $e) {
        log_message('error', 'QB Summary Error: '.$e->getMessage());
        return $this->response->setJSON([
            'ok'  => false,
            'msg' => 'Failed to load question bank: ' . $e->getMessage()
        ]);
    }
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

public function ajaxQbSubjects()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(400);
    }

    try {
        $campusId = (int) ($this->session->get('member_campusid') ?? 0);
        
        if ($campusId <= 0) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Campus not selected'
            ]);
        }

        // Get distinct subjects from question bank for this campus
        $subjects = $this->db->table('qb_questions q')
            ->select('
                DISTINCT q.subject_id,
                s.subject_name,
                s.subject_short_name
            ')
            ->join('allsubject s', 's.sid = q.subject_id', 'left')
            ->where('q.campus_id', $campusId)
            
            ->orderBy('s.subject_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'ok' => true,
            'data' => $subjects
        ]);

    } catch (\Exception $e) {
        log_message('error', 'QB Subjects Error: ' . $e->getMessage());
        
        return $this->response->setJSON([
            'ok' => false,
            'msg' => 'Error loading subjects'
        ]);
    }
}

public function ajaxQbTopics()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(400);
    }

    try {
        $classId = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');
        
        if ($classId <= 0 || $subjectId <= 0) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Class and Subject required'
            ]);
        }

        // Get topics for this class and subject
        $topics = $this->db->table('qb_topics')
            ->select('id as topic_id, topic_name')
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->orderBy('topic_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'ok' => true,
            'data' => $topics
        ]);

    } catch (\Exception $e) {
        log_message('error', 'QB Topics Error: ' . $e->getMessage());
        
        return $this->response->setJSON([
            'ok' => false,
            'msg' => 'Error loading topics'
        ]);
    }
}


public function ajaxQbQuestions()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(400);
    }

    try {
        $classIds = $this->request->getPost('class_ids') ?? [];
        $subjectIds = $this->request->getPost('subject_ids') ?? [];
        $topicIds = $this->request->getPost('topic_ids') ?? [];
        $questionTypes = $this->request->getPost('question_types') ?? [];
        
        // Build query
        $builder = $this->db->table('qb_questions q');
        
        $builder->select('
            q.id,
            q.question,
            q.question_type,
            q.difficulty,
            q.created_at,
            q.class_id,
            q.subject_id,
            q.topic_id,
            c.class_name,
            s.subject_name,
            t.topic_name
        ')
        ->join('classes c', 'c.class_id = q.class_id', 'left')
        ->join('allsubject s', 's.sid = q.subject_id', 'left')
        ->join('qb_topics t', 't.id = q.topic_id', 'left');
        // Removed campus_id and status filters since you don't have them
        
        // Apply filters - check if arrays are not empty and not just containing empty strings
        $classIds = array_filter(array_map('intval', $classIds), function($val) {
            return $val > 0;
        });
        
        $subjectIds = array_filter(array_map('intval', $subjectIds), function($val) {
            return $val > 0;
        });
        
        $topicIds = array_filter(array_map('intval', $topicIds), function($val) {
            return $val > 0;
        });
        
        if (!empty($classIds)) {
            $builder->whereIn('q.class_id', $classIds);
        }
        
        if (!empty($subjectIds)) {
            $builder->whereIn('q.subject_id', $subjectIds);
        }
        
        if (!empty($topicIds)) {
            $builder->whereIn('q.topic_id', $topicIds);
        }
        
        if (!empty($questionTypes)) {
            $builder->whereIn('q.question_type', $questionTypes);
        }
        
        $builder->orderBy('q.id', 'ASC');
        
        $questions = $builder->get()->getResultArray();
        
        return $this->response->setJSON([
            'ok' => true,
            'data' => $questions
        ]);

    } catch (\Exception $e) {
        log_message('error', 'QB Questions Error: ' . $e->getMessage());
        log_message('error', 'QB Questions Query: ' . $this->db->getLastQuery());
        
        return $this->response->setJSON([
            'ok' => false,
            'msg' => 'Error loading questions'
        ]);
    }
}

public function edit($quizId)
{
    $quizId = (int) $quizId;
    
    // Load quiz
    $quiz = $this->db->table('quizzes q')
        ->select('q.*, cs.cls_sec_id, ssub.subject_id')
        ->join('class_section cs', 'cs.cls_sec_id = q.cls_sec_id', 'left')
        ->join('section_subjects ssub', 'ssub.sec_sub_id = q.sec_sub_id', 'left')
        ->where('q.quiz_id', $quizId)
        ->get()
        ->getRow();
    
    if (!$quiz) {
        return redirect()->to('admin/quizzes')->with('error', 'Quiz not found.');
    }
    
    // Load class sections (same as create method)
    $campusId = (int) ($this->session->get('member_campusid') ?? 0);
    $sessionId = (int) ($this->session->get('member_sessionid') ?? 0);
    
    $classSections = [];
    if ($campusId > 0) {
        $classSections = $this->db->table('class_section cs')
            ->select("cs.cls_sec_id, CONCAT(c.class_name, ' - ', s.section_name) AS label", false)
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->get()->getResultArray();
    }
    
    // Load terms
    $terms = [];
    if ($sessionId > 0) {
        $terms = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, t.term_name, t.term_id')
            ->join('terms t', 't.term_id = ts.term_id', 'left')
            ->where('ts.session_id', $sessionId)
            ->where('ts.status', 1)
            ->orderBy('t.term_order', 'ASC')
            ->get()
            ->getResult();
    }
    
    // Load assigned questions for this quiz
    $assignedQuestions = $this->db->table('quiz_questions qq')
        ->select('qq.question_id, q.question, q.question_type')
        ->join('qb_questions q', 'q.id = qq.question_id', 'left')
        ->where('qq.quiz_id', $quizId)
        ->orderBy('qq.order_index', 'ASC')
        ->get()
        ->getResult();
    
    // Load assigned topics for this quiz
    $assignedTopics = $this->db->table('quiz_topics qt')
        ->select('qt.topic_id, t.topic_name')
        ->join('qb_topics t', 't.id = qt.topic_id', 'left')
        ->where('qt.quiz_id', $quizId)
        ->get()
        ->getResult();
    
    return view('admin/quizzes/edit', [
        'quiz' => $quiz,
        'campusId' => $campusId,
        'sessionId' => $sessionId,
        'classSections' => $classSections,
        'terms' => $terms,
        'assignedQuestions' => $assignedQuestions,
        'assignedTopics' => $assignedTopics,
        'validation' => \Config\Services::validation()
    ]);
}

// public function update($quizId)
// {
//     $quizId = (int) $quizId;
    
//     // Verify quiz exists
//     $quiz = $this->db->table('quizzes')
//         ->where('quiz_id', $quizId)
//         ->get()
//         ->getRow();
    
//     if (!$quiz) {
//         return redirect()->back()->with('error', 'Quiz not found.');
//     }
    
//     $this->db->transBegin();
    
//     try {
//         // Get existing quiz questions to update their content
//         $existingQuestions = $this->db->table('quiz_questions')
//             ->where('quiz_id', $quizId)
//             ->get()
//             ->getResult();
        
//         // Update each question, its options, and answers
//         $questionsData = $this->request->getPost('questions') ?? [];
        
//         foreach ($existingQuestions as $quizQuestion) {
//             $questionId = $quizQuestion->question_id;
            
//             // Check if this question exists in the submitted data
//             if (isset($questionsData[$questionId])) {
//                 $questionData = $questionsData[$questionId];
                
//                 // Update the question text in qb_questions table
//                 $questionUpdate = [
//                     'question' => trim($questionData['question'] ?? ''),
//                     'explanation' => trim($questionData['explanation'] ?? ''),
//                     'updated_date' => date('Y-m-d H:i:s'),
//                 ];
                
//                 // Only update if there's actual question data
//                 if (!empty($questionUpdate['question'])) {
//                     $this->db->table('qb_questions')
//                         ->where('question_id', $questionId)
//                         ->update($questionUpdate);
//                 }
                
//                 // Handle options update for MCQ questions
//                 if (isset($questionData['options']) && is_array($questionData['options'])) {
//                     // Get existing options for this question
//                     $existingOptions = $this->db->table('qb_options')
//                         ->where('question_id', $questionId)
//                         ->orderBy('option_index', 'asc')
//                         ->get()
//                         ->getResult();
                    
//                     // Update each option
//                     foreach ($existingOptions as $index => $option) {
//                         $optionIndex = $option->option_index;
                        
//                         if (isset($questionData['options'][$optionIndex])) {
//                             $optionUpdate = [
//                                 'option_text' => trim($questionData['options'][$optionIndex]),
//                                 'updated_date' => date('Y-m-d H:i:s'),
//                             ];
                            
//                             $this->db->table('qb_options')
//                                 ->where('option_id', $option->option_id)
//                                 ->update($optionUpdate);
//                         }
//                     }
                    
//                     // Handle answers update
//                     if (isset($questionData['answers']) && is_array($questionData['answers'])) {
//                         // For single-correct answer questions, replace the answer
//                         if (isset($questionData['correct_answer'])) {
//                             // Delete existing answers
//                             $this->db->table('qb_answers')
//                                 ->where('question_id', $questionId)
//                                 ->delete();
                            
//                             // Insert new answer
//                             $answerData = [
//                                 'question_id' => $questionId,
//                                 'answer_text' => trim($questionData['correct_answer']),
//                                 'created_date' => date('Y-m-d H:i:s'),
//                             ];
                            
//                             $this->db->table('qb_answers')->insert($answerData);
//                         }
//                         // For multiple-correct answer questions
//                         else {
//                             // Get existing answers
//                             $existingAnswers = $this->db->table('qb_answers')
//                                 ->where('question_id', $questionId)
//                                 ->get()
//                                 ->getResult();
                            
//                             // Update or insert answers based on submitted data
//                             $submittedAnswers = array_values($questionData['answers']);
                            
//                             foreach ($existingAnswers as $answerIndex => $answer) {
//                                 if (isset($submittedAnswers[$answerIndex])) {
//                                     $answerUpdate = [
//                                         'answer_text' => trim($submittedAnswers[$answerIndex]),
//                                         'updated_date' => date('Y-m-d H:i:s'),
//                                     ];
                                    
//                                     $this->db->table('qb_answers')
//                                         ->where('answer_id', $answer->answer_id)
//                                         ->update($answerUpdate);
//                                 }
//                             }
                            
//                             // If more answers were submitted than exist, add new ones
//                             for ($i = count($existingAnswers); $i < count($submittedAnswers); $i++) {
//                                 $answerData = [
//                                     'question_id' => $questionId,
//                                     'answer_text' => trim($submittedAnswers[$i]),
//                                     'created_date' => date('Y-m-d H:i:s'),
//                                 ];
                                
//                                 $this->db->table('qb_answers')->insert($answerData);
//                             }
//                         }
//                     }
//                 }
//                 // Handle answer update for fill-in-the-blank or short answer questions
//                 elseif (isset($questionData['answer'])) {
//                     // Delete existing answers
//                     $this->db->table('qb_answers')
//                         ->where('question_id', $questionId)
//                         ->delete();
                    
//                     // Insert new answer
//                     $answerData = [
//                         'question_id' => $questionId,
//                         'answer_text' => trim($questionData['answer']),
//                         'created_date' => date('Y-m-d H:i:s'),
//                     ];
                    
//                     $this->db->table('qb_answers')->insert($answerData);
//                 }
//             }
//         }
        
//         $this->db->transCommit();
        
//         return redirect()->to('admin/quizzes')->with('success', 'Quiz questions updated successfully.');
        
//     } catch (\Exception $e) {
//         $this->db->transRollback();
//         log_message('error', 'Quiz questions update failed: ' . $e->getMessage());
        
//         return redirect()->back()
//             ->withInput()
//             ->with('error', 'Failed to update quiz questions: ' . $e->getMessage());
//     }
// }



public function classResults($clsSecId)
{
    try {
        $clsSecId = (int) $clsSecId;
        
        log_message('debug', 'Loading class results for ID: ' . $clsSecId);
        
        // ===== 1) Get Class-Section Info =====
        $classInfo = $this->db->table('class_section cs')
            ->select("
                cs.cls_sec_id,
                CONCAT(c.class_name, ' - ', sec.section_name) AS cls_sec_name,
                c.class_name,
                sec.section_name,
                cs.status
            ", false)
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('cs.cls_sec_id', $clsSecId)
            ->get()
            ->getRow();

        if (!$classInfo) {
            throw new \Exception('Class-section not found.');
        }

        // ===== 2) Get all students in this class =====
        $students = $this->db->table('student_class sc')
            ->select("
                sc.student_id,
                CONCAT_WS(' ', s.first_name, s.last_name) AS student_name,
                s.profile_photo,
                s.reg_no
            ")
            ->join('students s', 's.student_id = sc.student_id', 'left')
            ->where('sc.cls_sec_id', $clsSecId)
            ->where('sc.status', 1)
            ->where('s.status', 1)
            ->orderBy('s.reg_no', 'ASC')
            ->get()
            ->getResult();

        $studentIds = [];
        foreach ($students as $student) {
            $studentIds[] = $student->student_id;
        }

        // ===== 3) Get all quizzes for this class WITH max_attempts =====
        $quizzes = $this->db->table('quizzes q')
            ->select("
                q.quiz_id,
                q.title,
                q.start_at,
                q.end_at,
                q.questions_count,
                q.created_date,
                q.max_attempts,
                subj.sid AS subject_id,
                subj.subject_name,
                t.name AS term_name
            ", false)
            ->join('section_subjects ssub', 'ssub.sec_sub_id = q.sec_sub_id', 'left')
            ->join('allsubject subj', 'subj.sid = ssub.subject_id', 'left')
            ->join('terms_session ts', 'ts.term_session_id = q.term_session_id', 'left')
            ->join('terms t', 't.term_id = ts.term_id', 'left')
            ->where('q.cls_sec_id', $clsSecId)
            ->where('q.is_published', 1)
            ->orderBy('subj.subject_name', 'ASC')
            ->orderBy('q.created_date', 'DESC')
            ->get()
            ->getResult();

        // ===== 4) Get all attempts for these quizzes =====
        $attempts = [];
        $studentQuizAttemptCounts = []; // To store attempt counts per student per quiz
        
        if (!empty($studentIds) && !empty($quizzes)) {
            $quizIds = [];
            foreach ($quizzes as $quiz) {
                $quizIds[] = $quiz->quiz_id;
            }

            if (!empty($quizIds)) {
                // Get all attempts for counting
                $attemptsQ = $this->db->table('quiz_attempts qa')
                    ->select("
                        qa.attempt_id,
                        qa.quiz_id,
                        qa.student_id,
                        qa.attempt_no,
                        qa.status,
                        qa.score_obtained,
                        qa.submitted_at,
                        q.questions_count,
                        q.max_attempts,
                        q.title AS quiz_title,
                        subj.sid AS subject_id,
                        subj.subject_name
                    ", false)
                    ->join('quizzes q', 'q.quiz_id = qa.quiz_id', 'left')
                    ->join('section_subjects ssub', 'ssub.sec_sub_id = q.sec_sub_id', 'left')
                    ->join('allsubject subj', 'subj.sid = ssub.subject_id', 'left')
                    ->whereIn('qa.quiz_id', $quizIds)
                    ->whereIn('qa.student_id', $studentIds)
                    ->where('qa.status', 'submitted')
                    ->orderBy('qa.attempt_no', 'DESC')
                    ->get();

                if ($attemptsQ) {
                    $attempts = $attemptsQ->getResult();
                    
                    // Count attempts per student per quiz
                    foreach ($attempts as $attempt) {
                        $studentId = $attempt->student_id;
                        $quizId = $attempt->quiz_id;
                        
                        if (!isset($studentQuizAttemptCounts[$studentId][$quizId])) {
                            $studentQuizAttemptCounts[$studentId][$quizId] = 0;
                        }
                        $studentQuizAttemptCounts[$studentId][$quizId] = max(
                            $studentQuizAttemptCounts[$studentId][$quizId],
                            $attempt->attempt_no
                        );
                    }
                }
            }
        }

        // ===== 5) Calculate average participation =====
        $totalStudents = count($students);
        $totalQuizzes = count($quizzes);
        $totalAttempts = count($attempts);
        
        if ($totalStudents > 0 && $totalQuizzes > 0) {
            $maxPossibleAttempts = $totalStudents * $totalQuizzes;
            $avgParticipation = $maxPossibleAttempts > 0 ? ($totalAttempts / $maxPossibleAttempts) * 100 : 0;
        } else {
            $avgParticipation = 0;
        }

        // ===== 6) Organize data for student performance =====
        $subjectWiseQuizzes = [];
        $studentPerformance = [];
        $subjectToppers = [];
        
        // Group quizzes by subject
        foreach ($quizzes as $quiz) {
            $subjectId = $quiz->subject_id ?? 0;
            $subjectName = $quiz->subject_name ?? 'Unknown Subject';
            
            if (!isset($subjectWiseQuizzes[$subjectId])) {
                $subjectWiseQuizzes[$subjectId] = [
                    'subject_id' => $subjectId,
                    'subject_name' => $subjectName,
                    'quizzes' => []
                ];
            }
            
            $subjectWiseQuizzes[$subjectId]['quizzes'][] = $quiz;
        }
        
        // Initialize student performance with quiz_scores array
        foreach ($students as $student) {
            $studentId = $student->student_id;
            $studentPerformance[$studentId] = [
                'student_id' => $studentId,
                'student_name' => $student->student_name,
                'profile_photo' => $student->profile_photo,
                'reg_no' => $student->reg_no,
                'total_score' => 0,
                'total_possible' => 0,
                'quiz_scores' => [],
                'attempted_quiz_count' => 0,
                'attempted_quizzes' => [],
                'subject_wise' => []
            ];
        }
        
        // Process attempts and organize by quiz - get highest score attempt
        $bestAttempts = [];
        foreach ($attempts as $attempt) {
            $studentId = $attempt->student_id;
            $quizId = $attempt->quiz_id;
            $attemptNo = $attempt->attempt_no;
            
            $key = $studentId . '_' . $quizId;
            
            if (!isset($bestAttempts[$key]) || $attempt->score_obtained > $bestAttempts[$key]['score']) {
                $bestAttempts[$key] = [
                    'score' => $attempt->score_obtained,
                    'attempt_no' => $attemptNo,
                    'subject_id' => $attempt->subject_id,
                    'subject_name' => $attempt->subject_name,
                    'quiz_title' => $attempt->quiz_title,
                    'questions_count' => $attempt->questions_count
                ];
            }
        }
        
        // Store best attempts in student performance
        foreach ($bestAttempts as $key => $attempt) {
            list($studentId, $quizId) = explode('_', $key);
            
            if (!isset($studentPerformance[$studentId])) {
                continue;
            }
            
            // Get max_attempts for this quiz
            $quizMaxAttempts = 0;
            foreach ($quizzes as $quiz) {
                if ($quiz->quiz_id == $quizId) {
                    $quizMaxAttempts = $quiz->max_attempts ?? 0;
                    break;
                }
            }
            
            // Get attempt count for this student and quiz
            $attemptCount = $studentQuizAttemptCounts[$studentId][$quizId] ?? 0;
            
            // Calculate scores
            $score = (int)round($attempt['score'] ?? 0);
            $totalMarks = (int)($attempt['questions_count'] ?? 1);
            $quizPercentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100) : 0;
            
            $studentPerformance[$studentId]['quiz_scores'][$quizId] = [
                'quiz_title' => $attempt['quiz_title'],
                'subject_name' => $attempt['subject_name'],
                'score' => $score,
                'total_marks' => $totalMarks,
                'percentage' => $quizPercentage,
                'attempt_count' => $attemptCount,
                'max_attempts' => $quizMaxAttempts
            ];
            
            // Update overall stats
            $studentPerformance[$studentId]['total_score'] += $score;
            $studentPerformance[$studentId]['total_possible'] += $totalMarks;
            
            // Update attempted quiz count
            if (!in_array($quizId, $studentPerformance[$studentId]['attempted_quizzes'])) {
                $studentPerformance[$studentId]['attempted_quizzes'][] = $quizId;
                $studentPerformance[$studentId]['attempted_quiz_count'] = count($studentPerformance[$studentId]['attempted_quizzes']);
            }
            
            // Also maintain subject_wise structure
            $subjectId = $attempt['subject_id'];
            $subjectName = $attempt['subject_name'];
            
            if (!isset($studentPerformance[$studentId]['subject_wise'][$subjectId])) {
                $studentPerformance[$studentId]['subject_wise'][$subjectId] = [
                    'subject_name' => $subjectName,
                    'total_score' => 0,
                    'total_possible' => 0,
                    'quiz_count' => 0,
                    'quizzes' => []
                ];
            }
            
            $studentPerformance[$studentId]['subject_wise'][$subjectId]['total_score'] += $score;
            $studentPerformance[$studentId]['subject_wise'][$subjectId]['total_possible'] += $totalMarks;
            $studentPerformance[$studentId]['subject_wise'][$subjectId]['quiz_count']++;
            $studentPerformance[$studentId]['subject_wise'][$subjectId]['quizzes'][$quizId] = [
                'quiz_title' => $attempt['quiz_title'],
                'score' => $score,
                'total_marks' => $totalMarks,
                'percentage' => $quizPercentage,
                'attempt_count' => $attemptCount,
                'max_attempts' => $quizMaxAttempts
            ];
        }

        // ===== 7) Calculate overall toppers =====
        $overallToppers = [];
        $overallScores = [];
        
        foreach ($studentPerformance as $studentId => $performance) {
            if ($performance['attempted_quiz_count'] > 0) {
                $overallPercentage = $performance['total_possible'] > 0 
                    ? round(($performance['total_score'] / $performance['total_possible']) * 100)
                    : 0;
                
                $overallScores[] = [
                    'student_id' => $studentId,
                    'student_name' => $performance['student_name'],
                    'profile_photo' => $performance['profile_photo'],
                    'reg_no' => $performance['reg_no'],
                    'percentage' => $overallPercentage,
                    'total_quizzes' => $totalQuizzes,
                    'attempted_quizzes' => $performance['attempted_quiz_count']
                ];
            }
        }
        
        // Sort by percentage
        usort($overallScores, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });
        
        $overallToppers = array_slice($overallScores, 0, 3);
        
        // ===== 8) Calculate subject-wise toppers =====
        foreach ($subjectWiseQuizzes as $subjectId => $subject) {
            $subjectScores = [];
            
            foreach ($studentPerformance as $studentId => $performance) {
                if (isset($performance['subject_wise'][$subjectId])) {
                    $subjectData = $performance['subject_wise'][$subjectId];
                    $subjectPercentage = $subjectData['total_possible'] > 0 
                        ? round(($subjectData['total_score'] / $subjectData['total_possible']) * 100)
                        : 0;
                    
                    if ($subjectData['quiz_count'] > 0) {
                        $subjectScores[] = [
                            'student_id' => $studentId,
                            'student_name' => $performance['student_name'],
                            'profile_photo' => $performance['profile_photo'],
                            'reg_no' => $performance['reg_no'],
                            'percentage' => $subjectPercentage,
                            'attempted_count' => $subjectData['quiz_count']
                        ];
                    }
                }
            }
            
            if (!empty($subjectScores)) {
                usort($subjectScores, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });
                
                $subjectToppers[$subjectId] = [
                    'subject_id' => $subjectId,
                    'subject_name' => $subject['subject_name'],
                    'topStudents' => array_slice($subjectScores, 0, 3)
                ];
            }
        }
        
        // ===== 9) Get current term for report =====
        $currentTerm = '';
        if (!empty($quizzes) && isset($quizzes[0]->term_name)) {
            $currentTerm = $quizzes[0]->term_name;
        }
        
        // ===== 10) Prepare data for view =====
        $data = [
            'title' => 'Class Performance Report - ' . $classInfo->cls_sec_name,
            'classInfo' => $classInfo,
            'students' => $students,
            'quizzes' => $quizzes,
            'attempts' => $attempts,
            'studentQuizAttemptCounts' => $studentQuizAttemptCounts,
            'subjectWiseQuizzes' => $subjectWiseQuizzes,
            'studentPerformance' => $studentPerformance,
            'overallToppers' => $overallToppers,
            'subjectToppers' => $subjectToppers ?? [],
            'totalStudents' => $totalStudents,
            'totalQuizzes' => $totalQuizzes,
            'totalAttempts' => $totalAttempts,
            'avgParticipation' => round($avgParticipation),
            'currentTerm' => $currentTerm
        ];
        
        return view('admin/quizzes/class_results_report', $data);
        
    } catch (\Exception $e) {
        log_message('error', 'Error in classResults: ' . $e->getMessage());
        log_message('error', 'Trace: ' . $e->getTraceAsString());
        
        return "<h1>Error Loading Report</h1>
                <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <a href='" . base_url('admin/quizzes') . "' class='btn btn-primary'>Back to Quizzes</a>";
    }
}
// public function classResults($clsSecId)
// {
//     try {
//         $clsSecId = (int) $clsSecId;
        
//         log_message('debug', 'Loading class results for ID: ' . $clsSecId);
        
//         // ===== 1) Get Class-Section Info =====
//         $classInfo = $this->db->table('class_section cs')
//             ->select("
//                 cs.cls_sec_id,
//                 CONCAT(c.class_name, ' - ', sec.section_name) AS cls_sec_name,
//                 c.class_name,
//                 sec.section_name,
//                 cs.status
//             ", false)
//             ->join('classes c', 'c.class_id = cs.class_id', 'left')
//             ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
//             ->where('cs.cls_sec_id', $clsSecId)
//             ->get()
//             ->getRow();

//         if (!$classInfo) {
//             throw new \Exception('Class-section not found.');
//         }

//         // ===== 2) Get all students in this class =====
//         $students = $this->db->table('student_class sc')
//             ->select("
//                 sc.student_id,
//                 CONCAT_WS(' ', s.first_name, s.last_name) AS student_name,
//                 s.profile_photo,
//                 s.reg_no
                
                
//             ")
//             ->join('students s', 's.student_id = sc.student_id', 'left')
//             ->where('sc.cls_sec_id', $clsSecId)
//             ->where('sc.status', 1)
//             ->where('s.status', 1)
//             ->orderBy('s.reg_no', 'ASC')
//             ->get()
//             ->getResult();

//         $studentIds = [];
//         foreach ($students as $student) {
//             $studentIds[] = $student->student_id;
//         }

//         // ===== 3) Get all quizzes for this class WITH max_attempts =====
//         $quizzes = $this->db->table('quizzes q')
//             ->select("
//                 q.quiz_id,
//                 q.title,
//                 q.start_at,
//                 q.end_at,
//                 q.questions_count,
//                 q.created_date,
//                 q.max_attempts,
//                 subj.sid AS subject_id,
//                 subj.subject_name,
//                 t.name AS term_name
//             ", false)
//             ->join('section_subjects ssub', 'ssub.sec_sub_id = q.sec_sub_id', 'left')
//             ->join('allsubject subj', 'subj.sid = ssub.subject_id', 'left')
//             ->join('terms_session ts', 'ts.term_session_id = q.term_session_id', 'left')
//             ->join('terms t', 't.term_id = ts.term_id', 'left')
//             ->where('q.cls_sec_id', $clsSecId)
//             ->where('q.is_published', 1)
//             ->orderBy('subj.subject_name', 'ASC')
//             ->orderBy('q.created_date', 'DESC')
//             ->get()
//             ->getResult();

//         // ===== 4) Get all attempts for these quizzes =====
//         $attempts = [];
//         $studentQuizAttemptCounts = []; // To store attempt counts per student per quiz
//         $studentQuizTotalScores = []; // To store total scores per student per quiz across all attempts
//         $studentQuizAverageScores = []; // To store average scores per student per quiz
//         $studentAllAttempts = []; // To store all attempts data for detailed report
        
//         if (!empty($studentIds) && !empty($quizzes)) {
//             $quizIds = [];
//             foreach ($quizzes as $quiz) {
//                 $quizIds[] = $quiz->quiz_id;
//             }

//             if (!empty($quizIds)) {
//                 // Get all attempts for counting
//                 $attemptsQ = $this->db->table('quiz_attempts qa')
//                     ->select("
//                         qa.attempt_id,
//                         qa.quiz_id,
//                         qa.student_id,
//                         qa.attempt_no,
//                         qa.status,
//                         qa.score_obtained,
//                         qa.submitted_at,
//                         qa.started_at,
                        
//                         q.questions_count,
//                         q.max_attempts,
//                         q.title AS quiz_title,
//                         subj.sid AS subject_id,
//                         subj.subject_name
//                     ", false)
//                     ->join('quizzes q', 'q.quiz_id = qa.quiz_id', 'left')
//                     ->join('section_subjects ssub', 'ssub.sec_sub_id = q.sec_sub_id', 'left')
//                     ->join('allsubject subj', 'subj.sid = ssub.subject_id', 'left')
//                     ->whereIn('qa.quiz_id', $quizIds)
//                     ->whereIn('qa.student_id', $studentIds)
//                     ->where('qa.status', 'submitted')
//                     ->orderBy('qa.student_id', 'ASC')
//                     ->orderBy('qa.quiz_id', 'ASC')
//                     ->orderBy('qa.attempt_no', 'DESC')
//                     ->get();

//                 if ($attemptsQ) {
//                     $attempts = $attemptsQ->getResult();
                    
//                     // Process all attempts
//                     foreach ($attempts as $attempt) {
//                         $studentId = $attempt->student_id;
//                         $quizId = $attempt->quiz_id;
//                         $score = (int)round($attempt->score_obtained ?? 0);
                        
//                         // Initialize arrays if not exists
//                         if (!isset($studentQuizAttemptCounts[$studentId])) {
//                             $studentQuizAttemptCounts[$studentId] = [];
//                         }
//                         if (!isset($studentQuizTotalScores[$studentId])) {
//                             $studentQuizTotalScores[$studentId] = [];
//                         }
//                         if (!isset($studentAllAttempts[$studentId])) {
//                             $studentAllAttempts[$studentId] = [];
//                         }
                        
//                         // Store detailed attempt data
//                         $studentAllAttempts[$studentId][$quizId][] = [
//                             'attempt_id' => $attempt->attempt_id,
//                             'attempt_no' => $attempt->attempt_no,
//                             'score_obtained' => $score,
//                             'total_marks' => $attempt->questions_count,
//                             'percentage' => $attempt->questions_count > 0 ? round(($score / $attempt->questions_count) * 100) : 0,
//                             'submitted_at' => $attempt->submitted_at,
//                             'started_at' => $attempt->started_at,
                            
//                             'quiz_title' => $attempt->quiz_title,
//                             'subject_name' => $attempt->subject_name
//                         ];
                        
//                         // Count attempts per student per quiz
//                         if (!isset($studentQuizAttemptCounts[$studentId][$quizId])) {
//                             $studentQuizAttemptCounts[$studentId][$quizId] = 0;
//                         }
//                         $studentQuizAttemptCounts[$studentId][$quizId]++;
                        
//                         // Sum scores per student per quiz (all attempts)
//                         if (!isset($studentQuizTotalScores[$studentId][$quizId])) {
//                             $studentQuizTotalScores[$studentId][$quizId] = 0;
//                         }
//                         $studentQuizTotalScores[$studentId][$quizId] += $score;
//                     }
                    
//                     // Calculate average scores
//                     foreach ($studentQuizTotalScores as $studentId => $quizScores) {
//                         foreach ($quizScores as $quizId => $totalScore) {
//                             $attemptCount = $studentQuizAttemptCounts[$studentId][$quizId] ?? 1;
//                             $studentQuizAverageScores[$studentId][$quizId] = $attemptCount > 0 ? 
//                                 round($totalScore / $attemptCount) : 0;
//                         }
//                     }
//                 }
//             }
//         }

//         // ===== 5) Calculate average participation =====
//         $totalStudents = count($students);
//         $totalQuizzes = count($quizzes);
//         $totalAttempts = count($attempts);
        
//         if ($totalStudents > 0 && $totalQuizzes > 0) {
//             $maxPossibleAttempts = $totalStudents * $totalQuizzes;
//             $avgParticipation = $maxPossibleAttempts > 0 ? ($totalAttempts / $maxPossibleAttempts) * 100 : 0;
//         } else {
//             $avgParticipation = 0;
//         }

//         // ===== 6) Organize data for student performance =====
//         $subjectWiseQuizzes = [];
//         $studentPerformance = [];
//         $subjectToppers = [];
        
//         // Group quizzes by subject
//         foreach ($quizzes as $quiz) {
//             $subjectId = $quiz->subject_id ?? 0;
//             $subjectName = $quiz->subject_name ?? 'Unknown Subject';
            
//             if (!isset($subjectWiseQuizzes[$subjectId])) {
//                 $subjectWiseQuizzes[$subjectId] = [
//                     'subject_id' => $subjectId,
//                     'subject_name' => $subjectName,
//                     'quizzes' => []
//                 ];
//             }
            
//             $subjectWiseQuizzes[$subjectId]['quizzes'][] = $quiz;
//         }
        
//         // Initialize student performance with quiz_scores array
//         foreach ($students as $student) {
//             $studentId = $student->student_id;
//             $studentPerformance[$studentId] = [
//                 'student_id' => $studentId,
//                 'student_name' => $student->student_name,
//                 'profile_photo' => $student->profile_photo,
//                 'reg_no' => $student->reg_no,
                
//                 'total_obtained_sum' => 0, // Sum of obtained marks across all quizzes (sum of all attempts)
//                 'total_average_score' => 0, // Average score across all quizzes
//                 'total_possible_score' => 0, // Sum of maximum marks of all attempted quizzes
//                 'weighted_total_score' => 0, // For percentage calculation (sum of weighted scores)
//                 'total_attempts_count' => 0, // Total attempts across all quizzes
//                 'max_attempts_possible' => 0, // Sum of max_attempts across all quizzes
//                 'total_quizzes' => count($quizzes),
//                 'attempted_quizzes_count' => 0, // Number of quizzes attempted (at least once)
//                 'quiz_scores' => [],
//                 'attempted_quizzes' => [],
//                 'subject_wise' => [],
//                 'all_attempts' => $studentAllAttempts[$studentId] ?? [] // Detailed attempts data
//             ];
//         }
        
//       // Process all quizzes for each student
// foreach ($students as $student) {
//     $studentId = $student->student_id;
    
//     foreach ($quizzes as $quiz) {
//         $quizId = $quiz->quiz_id;
//         $totalMarks = (int)($quiz->questions_count ?? 1);
//         $quizMaxAttempts = (int)($quiz->max_attempts ?? 0);
//         $subjectId = $quiz->subject_id ?? 0;
//         $subjectName = $quiz->subject_name ?? 'Unknown Subject';
        
//         // Get data for this quiz
//         $totalScore = isset($studentQuizTotalScores[$studentId][$quizId]) ? 
//             (int)$studentQuizTotalScores[$studentId][$quizId] : 0;
//         $averageScore = isset($studentQuizAverageScores[$studentId][$quizId]) ? 
//             (int)$studentQuizAverageScores[$studentId][$quizId] : 0;
//         $attemptCount = isset($studentQuizAttemptCounts[$studentId][$quizId]) ? 
//             (int)$studentQuizAttemptCounts[$studentId][$quizId] : 0;
        
//         if ($attemptCount > 0) {
//             // Calculate total possible marks based on number of attempts
//             $totalPossibleMarks = $totalMarks * $attemptCount;
            
//             // Calculate quiz percentage based on TOTAL score (not average)
//             $quizPercentage = $totalPossibleMarks > 0 ? round(($totalScore / $totalPossibleMarks) * 100) : 0;
            
//             $studentPerformance[$studentId]['quiz_scores'][$quizId] = [
//                 'quiz_title' => $quiz->title,
//                 'subject_name' => $subjectName,
//                 'total_obtained' => $totalScore, // Sum of all attempts
//                 'average_obtained' => $averageScore, // Average of all attempts
//                 'total_marks' => $totalMarks, // Max marks per attempt
//                 'total_possible_marks' => $totalPossibleMarks, // Max marks × number of attempts
//                 'percentage' => $quizPercentage,
//                 'attempt_count' => $attemptCount,
//                 'max_attempts' => $quizMaxAttempts
//             ];
            
//             // Update overall stats
//             $studentPerformance[$studentId]['total_obtained_sum'] += $totalScore;
//             $studentPerformance[$studentId]['total_average_score'] += $averageScore;
//             $studentPerformance[$studentId]['total_possible_score'] += $totalPossibleMarks; // Changed to total_possible_marks
//             $studentPerformance[$studentId]['weighted_total_score'] += $quizPercentage; // Use actual percentage
//             $studentPerformance[$studentId]['total_attempts_count'] += $attemptCount;
//             $studentPerformance[$studentId]['max_attempts_possible'] += $quizMaxAttempts;
            
//             // Update attempted quiz count
//             if (!in_array($quizId, $studentPerformance[$studentId]['attempted_quizzes'])) {
//                 $studentPerformance[$studentId]['attempted_quizzes'][] = $quizId;
//                 $studentPerformance[$studentId]['attempted_quizzes_count'] = count($studentPerformance[$studentId]['attempted_quizzes']);
//             }
            
//             // Also maintain subject_wise structure
//             if (!isset($studentPerformance[$studentId]['subject_wise'][$subjectId])) {
//                 $studentPerformance[$studentId]['subject_wise'][$subjectId] = [
//                     'subject_name' => $subjectName,
//                     'total_obtained_sum' => 0,
//                     'total_average_score' => 0,
//                     'total_possible_score' => 0,
//                     'weighted_total_score' => 0,
//                     'quiz_count' => 0,
//                     'total_attempts' => 0,
//                     'quizzes' => []
//                 ];
//             }
            
//             $studentPerformance[$studentId]['subject_wise'][$subjectId]['total_obtained_sum'] += $totalScore;
//             $studentPerformance[$studentId]['subject_wise'][$subjectId]['total_average_score'] += $averageScore;
//             $studentPerformance[$studentId]['subject_wise'][$subjectId]['total_possible_score'] += $totalPossibleMarks;
//             $studentPerformance[$studentId]['subject_wise'][$subjectId]['weighted_total_score'] += $quizPercentage;
//             $studentPerformance[$studentId]['subject_wise'][$subjectId]['total_attempts'] += $attemptCount;
//             $studentPerformance[$studentId]['subject_wise'][$subjectId]['quiz_count']++;
//             $studentPerformance[$studentId]['subject_wise'][$subjectId]['quizzes'][$quizId] = [
//                 'quiz_title' => $quiz->title,
//                 'total_obtained' => $totalScore,
//                 'average_obtained' => $averageScore,
//                 'total_marks' => $totalMarks,
//                 'total_possible_marks' => $totalPossibleMarks,
//                 'percentage' => $quizPercentage,
//                 'attempt_count' => $attemptCount,
//                 'max_attempts' => $quizMaxAttempts
//             ];
//         }
//     }
// }
//       // ===== 7) Calculate overall toppers =====
// $overallToppers = [];
// $overallScores = [];

// foreach ($studentPerformance as $studentId => $performance) {
//     if ($performance['attempted_quizzes_count'] > 0) {
//         // Calculate overall percentage based on total obtained vs total possible
//         $overallPercentage = $performance['total_possible_score'] > 0 ? 
//             round(($performance['total_obtained_sum'] / $performance['total_possible_score']) * 100) : 0;
        
//         $overallScores[] = [
//             'student_id' => $studentId,
//             'student_name' => $performance['student_name'],
//             'profile_photo' => $performance['profile_photo'],
//             'reg_no' => $performance['reg_no'],
//             'percentage' => $overallPercentage,
//             'total_quizzes' => $totalQuizzes,
//             'attempted_quizzes' => $performance['attempted_quizzes_count'],
//             'total_attempts' => $performance['total_attempts_count'],
//             'max_attempts_possible' => $performance['max_attempts_possible'],
//             'total_obtained_sum' => $performance['total_obtained_sum'],
//             'total_average_score' => $performance['total_average_score'],
//             'total_possible_score' => $performance['total_possible_score'],
//             'all_attempts' => $performance['all_attempts'] // Include all attempts data
//         ];
//     }
// }
        
//         // Sort by percentage (based on weighted average)
//         usort($overallScores, function($a, $b) {
//             return $b['percentage'] <=> $a['percentage'];
//         });
        
//         $overallToppers = array_slice($overallScores, 0, 3);
        
//         // ===== 8) Calculate subject-wise toppers =====
//         foreach ($subjectWiseQuizzes as $subjectId => $subject) {
//             $subjectScores = [];
            
//             foreach ($studentPerformance as $studentId => $performance) {
//                 if (isset($performance['subject_wise'][$subjectId])) {
//                     $subjectData = $performance['subject_wise'][$subjectId];
//                     $subjectPercentage = $subjectData['quiz_count'] > 0 ?
//                         round($subjectData['weighted_total_score'] / $subjectData['quiz_count']) : 0;
                    
//                     if ($subjectData['quiz_count'] > 0) {
//                         $subjectScores[] = [
//                             'student_id' => $studentId,
//                             'student_name' => $performance['student_name'],
//                             'profile_photo' => $performance['profile_photo'],
//                             'reg_no' => $performance['reg_no'],
//                             'percentage' => $subjectPercentage,
//                             'attempted_count' => $subjectData['quiz_count'],
//                             'total_attempts' => $subjectData['total_attempts'],
//                             'total_obtained_sum' => $subjectData['total_obtained_sum'],
//                             'total_average_score' => $subjectData['total_average_score'],
//                             'total_possible_score' => $subjectData['total_possible_score']
//                         ];
//                     }
//                 }
//             }
            
//             if (!empty($subjectScores)) {
//                 usort($subjectScores, function($a, $b) {
//                     return $b['percentage'] <=> $a['percentage'];
//                 });
                
//                 $subjectToppers[$subjectId] = [
//                     'subject_id' => $subjectId,
//                     'subject_name' => $subject['subject_name'],
//                     'topStudents' => array_slice($subjectScores, 0, 3)
//                 ];
//             }
//         }
        
//         // ===== 9) Get current term for report =====
//         $currentTerm = '';
//         if (!empty($quizzes) && isset($quizzes[0]->term_name)) {
//             $currentTerm = $quizzes[0]->term_name;
//         }
        
//         // ===== 10) Prepare data for view =====
//         $data = [
//             'title' => 'Class Performance Report - ' . $classInfo->cls_sec_name,
//             'classInfo' => $classInfo,
//             'students' => $students,
//             'quizzes' => $quizzes,
//             'attempts' => $attempts,
//             'studentQuizAttemptCounts' => $studentQuizAttemptCounts,
//             'studentQuizTotalScores' => $studentQuizTotalScores,
//             'studentQuizAverageScores' => $studentQuizAverageScores,
//             'studentAllAttempts' => $studentAllAttempts,
//             'subjectWiseQuizzes' => $subjectWiseQuizzes,
//             'studentPerformance' => $studentPerformance,
//             'overallToppers' => $overallToppers,
//             'subjectToppers' => $subjectToppers ?? [],
//             'totalStudents' => $totalStudents,
//             'totalQuizzes' => $totalQuizzes,
//             'totalAttempts' => $totalAttempts,
//             'avgParticipation' => round($avgParticipation),
//             'currentTerm' => $currentTerm
//         ];
        
//         return view('admin/quizzes/class_results_report', $data);
        
//     } catch (\Exception $e) {
//         log_message('error', 'Error in classResults: ' . $e->getMessage());
//         log_message('error', 'Trace: ' . $e->getTraceAsString());
        
//         return "<h1>Error Loading Report</h1>
//                 <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
//                 <a href='" . base_url('admin/quizzes') . "' class='btn btn-primary'>Back to Quizzes</a>";
//     }
// }
}