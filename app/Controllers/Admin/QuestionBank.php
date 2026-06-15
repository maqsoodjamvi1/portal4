<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class QuestionBank extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = db_connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-questions');
    }

    /**
     * Overview: class → subject → topic counts, proof read, links to form.
     */
    public function index()
    {
        return view('admin/question_bank_report');
    }

    /**
     * Add/edit questions (JSON + cards).
     */
    public function form()
    {
        return view('admin/question_bank_form', [
            'classes'  => $this->getAllClasses(),
            'subjects' => $this->getAllSubjects(),
        ]);
    }


public function summary_all()
{
    $db = db_connect();
    $boardService = new \App\Libraries\QbBoardPublisherService($db);
    $systemId     = (int) (getSchoolInfo()->system_id ?? 0);

    // If you use sessions, you can also filter by session_id here (optional)
    // $sessionId = (int) ($this->session->get('member_sessionid') ?? 0);

    $sql = "
        SELECT
            q.class_id,
            COALESCE(c.class_short_name, c.class_name, CONCAT('Class #', q.class_id)) AS class_name,

            q.subject_id,
            COALESCE(s.subject_short_name, s.subject_name, CONCAT('Subject #', q.subject_id)) AS subject_name,

            q.topic_id,
            COALESCE(t.topic_name, CONCAT('Topic #', q.topic_id)) AS topic_name,

            COUNT(*) AS total_questions,

            SUM(CASE WHEN q.question_type = 'mcq' THEN 1 ELSE 0 END)       AS mcq_single_count,
            SUM(CASE WHEN q.question_type = 'mcq_multi' THEN 1 ELSE 0 END) AS mcq_multi_count,
            SUM(CASE WHEN q.question_type = 'tf' THEN 1 ELSE 0 END)        AS tf_count,
            SUM(CASE WHEN q.question_type = 'fill' THEN 1 ELSE 0 END)      AS fill_count,
            SUM(CASE WHEN q.question_type = 'short' THEN 1 ELSE 0 END)     AS short_count,
            SUM(CASE WHEN q.question_type IN ('descriptive', 'description') THEN 1 ELSE 0 END) AS descriptive_count,
            SUM(CASE WHEN q.question_type = 'match' THEN 1 ELSE 0 END)     AS match_count,

            -- optional (only if you still care about drag/non-drag)
            SUM(CASE WHEN q.question_type = 'match' AND q.is_drag = 1 THEN 1 ELSE 0 END) AS match_drag_count,
            SUM(CASE WHEN q.question_type = 'match' AND q.is_drag = 0 THEN 1 ELSE 0 END) AS match_nodrag_count

        FROM qb_questions q
        LEFT JOIN classes c     ON c.class_id = q.class_id
        LEFT JOIN allsubject s  ON s.sid      = q.subject_id
        LEFT JOIN qb_topics t   ON t.id       = q.topic_id
        GROUP BY q.class_id, q.subject_id, q.topic_id
        ORDER BY q.class_id ASC, q.subject_id ASC, q.topic_id ASC
    ";

    $rows = $db->query($sql)->getResultArray();

    // Build nested tree: class -> subject -> topic
    $tree = [];
    foreach ($rows as $r) {
        $cid = (int)$r['class_id'];
        $sid = (int)$r['subject_id'];
        $tid = (int)$r['topic_id'];

        if (!isset($tree[$cid])) {
            $tree[$cid] = [
                'class_id'   => $cid,
                'class_name' => $r['class_name'],
                'subjects'   => []
            ];
        }
        if (!isset($tree[$cid]['subjects'][$sid])) {
            $tree[$cid]['subjects'][$sid] = [
                'subject_id'   => $sid,
                'subject_name' => $r['subject_name'],
                'topics'       => []
            ];
        }

        $tree[$cid]['subjects'][$sid]['topics'][] = [
            'topic_id'           => $tid,
            'topic_name'         => $r['topic_name'],
            'total_questions'    => (int)$r['total_questions'],
            'mcq_single_count'   => (int)$r['mcq_single_count'],
            'mcq_multi_count'    => (int)$r['mcq_multi_count'],
            'tf_count'           => (int)$r['tf_count'],
            'fill_count'         => (int)$r['fill_count'],
            'short_count'        => (int)$r['short_count'],
            'descriptive_count'  => (int)($r['descriptive_count'] ?? 0),
            'match_count'        => (int)$r['match_count'],

            // optional
            'match_drag_count'   => (int)$r['match_drag_count'],
            'match_nodrag_count' => (int)$r['match_nodrag_count'],
        ];
    }

    $topicIds = [];
    foreach ($tree as $classRow) {
        foreach ($classRow['subjects'] as $subRow) {
            foreach ($subRow['topics'] as $topRow) {
                $topicIds[] = (int) ($topRow['topic_id'] ?? 0);
            }
        }
    }
    $boardLabelsMap = $boardService->getLabelsMapForTopics($topicIds);

    // convert associative to indexed arrays
    $out = [];
    foreach ($tree as $c) {
        foreach ($c['subjects'] as &$sub) {
            foreach ($sub['topics'] as &$top) {
                $tid = (int) ($top['topic_id'] ?? 0);
                $labels = $boardLabelsMap[$tid] ?? [];
                $top['board_publishers']    = $labels;
                $top['board_publisher_ids'] = array_values(array_map(
                    static fn (array $bp): int => (int) ($bp['id'] ?? 0),
                    $labels
                ));
            }
            unset($top);
        }
        unset($sub);
        $c['subjects'] = array_values($c['subjects']);
        $out[] = $c;
    }

    return $this->response->setJSON([
        'status'           => 1,
        'data'             => $out,
        'board_publishers' => $boardService->listForSystem($systemId, true),
    ]);
}

    /* -------------------------------------------------------------
     * Save question (multi-type)
     * ----------------------------------------------------------- */
public function summary()
{
    $classId   = (int) $this->request->getGet('class_id');
    $subjectId = (int) $this->request->getGet('subject_id');
    $topicId   = (int) $this->request->getGet('topic_id');

    $db = db_connect();

    $rows = $db->table('qb_questions')
        ->select('question_type, is_drag')
        ->where('class_id', $classId)
        ->where('subject_id', $subjectId)
        ->where('topic_id', $topicId)
        ->get()->getResult();

    $sum = [
        'total'         => count($rows),
        'mcq'           => 0,
        'mcq_multi'     => 0,
        'tf'            => 0,
        'fill'          => 0,
        'short'         => 0,
        'descriptive'   => 0,
        'match'         => 0,
        'match_drag'    => 0,
        'match_nodrag'  => 0,
    ];

    foreach ($rows as $r) {
        $type = (string) ($r->question_type ?? '');
        if ($type === 'mcq') {
            $sum['mcq']++;
        } elseif ($type === 'mcq_multi') {
            $sum['mcq_multi']++;
        } elseif ($type === 'tf') {
            $sum['tf']++;
        } elseif ($type === 'fill') {
            $sum['fill']++;
        } elseif ($type === 'short') {
            $sum['short']++;
        } elseif ($type === 'descriptive' || $type === 'description') {
            $sum['descriptive']++;
        } elseif ($type === 'match') {
            $sum['match']++;
            if ((int) ($r->is_drag ?? 0) === 1) {
                $sum['match_drag']++;
            } else {
                $sum['match_nodrag']++;
            }
        }
    }

    return $this->response->setJSON($sum);
}

public function save()
{
    log_message('debug', 'QB FILES: ' . print_r($this->request->getFiles(), true));

    $questions = $this->request->getPost('questions');

    if (empty($questions) || !is_array($questions)) {
        return redirect()->back()->with('error', 'No questions submitted.');
    }

    $result = $this->persistQuestions($questions, $this->request->getFiles());

    if ($result['saved'] > 0) {
        return redirect()
            ->to(base_url('admin/question-bank/form'))
            ->with('msg', "{$result['saved']} of {$result['total']} question(s) saved successfully. Skipped: {$result['skipped']}.");
    }

    return redirect()
        ->back()
        ->with('error', "No questions were saved. Total submitted: {$result['total']}, skipped: {$result['skipped']}. Check logs for details.");
}

    /**
     * AJAX: save one or more questions (proof-read modal).
     */
    public function saveAjax(): ResponseInterface
    {
        $questions = $this->request->getPost('questions');

        if (empty($questions) || !is_array($questions)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 0,
                'message' => 'No questions submitted.',
            ]);
        }

        $result = $this->persistQuestions($questions, $this->request->getFiles());

        if ($result['saved'] > 0) {
            $saved = (int) $result['saved'];
            $total = (int) $result['total'];
            $id    = $result['saved_ids'][0] ?? 0;

            return $this->response->setJSON([
                'status'    => 1,
                'message'   => "{$saved} of {$total} question(s) saved successfully.",
                'saved'     => $saved,
                'skipped'   => (int) $result['skipped'],
                'total'     => $total,
                'saved_ids' => $result['saved_ids'],
                'id'        => $id,
            ]);
        }

        return $this->response->setStatusCode(422)->setJSON([
            'status'    => 0,
            'message'   => 'No questions were saved. Check required fields.',
            'saved'     => 0,
            'skipped'   => $result['skipped'],
            'total'     => $result['total'],
            'saved_ids' => [],
        ]);
    }

    /**
     * AJAX: single question for edit modal.
     */
    public function questionOne($id = 0): ResponseInterface
    {
        $id = (int) $id;
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 0,
                'message' => 'Invalid question id.',
            ]);
        }

        try {
            $builder = $this->db->table('qb_questions q')
                ->select('q.*, c.class_name, t.topic_name, s.subject_name')
                ->join('classes c', 'c.class_id = q.class_id', 'left');

            if ($this->db->tableExists('allsubject')) {
                $builder->join('allsubject s', 's.sid = q.subject_id', 'left');
            } elseif ($this->db->tableExists('subjects')) {
                $builder->join('subjects s', 's.subject_id = q.subject_id', 'left');
            }

            if ($this->db->tableExists('qb_topics')) {
                $builder->join('qb_topics t', 't.id = q.topic_id', 'left');
            }

            $row = $builder->where('q.id', $id)->get()->getRow();
        } catch (\Throwable $e) {
            log_message('error', 'QuestionBank::questionOne failed - ' . $e->getMessage());
            $row = null;
        }

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 0,
                'message' => 'Question not found.',
            ]);
        }

        return $this->response->setJSON([
            'status'   => 1,
            'question' => $this->normalizeQuestionBankRowForJson($row),
        ]);
    }

    /**
     * AJAX: delete a question.
     */
    public function deleteQuestion(): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id') ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 0,
                'message' => 'Invalid question id.',
            ]);
        }

        $existing = $this->db->table('qb_questions')->where('id', $id)->get()->getRowArray();
        if (!$existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 0,
                'message' => 'Question not found.',
            ]);
        }

        try {
            $img = (string) ($existing['question_image'] ?? '');
            if ($img !== '') {
                $path = WRITEPATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $img), DIRECTORY_SEPARATOR);
                if (is_file($path)) {
                    @unlink($path);
                }
            }

            $this->db->table('qb_questions')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            log_message('error', 'QuestionBank::deleteQuestion failed - ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 0,
                'message' => 'Could not delete question.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 1,
            'message' => 'Question deleted.',
            'id'      => $id,
        ]);
    }

    /**
     * @param array<string, mixed> $files
     * @return array{saved: int, skipped: int, total: int, saved_ids: list<int>}
     */
    private function persistQuestions(array $questions, array $files): array
    {
        $savedCount = 0;
        $skipped    = 0;
        $total      = count($questions);
        $savedIds   = [];

        log_message('debug', 'QB persistQuestions raw: ' . print_r($questions, true));

        $uploadDir = WRITEPATH . 'uploads/qb_questions';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        foreach ($questions as $idx => $q) {

        $classId    = (int) ($q['class_id'] ?? 0);
        $subjectId  = (int) ($q['subject_id'] ?? 0);
        $topicId    = (int) ($q['topic_id'] ?? 0);

        $type       = trim((string) ($q['question_type'] ?? 'mcq'));
        $difficulty = trim((string) ($q['difficulty'] ?? 'normal'));

        // ✅ new: question mode
        $questionMedia = trim((string) ($q['question_media'] ?? 'text')); // text|image
        if (!in_array($questionMedia, ['text', 'image'], true)) {
            $questionMedia = 'text';
        }

        $questionText = trim((string) ($q['question'] ?? ''));
        $imageAlt     = trim((string) ($q['question_image_alt'] ?? ''));

        $qid = (int) ($q['id'] ?? 0);
        $existing = null;
        if ($qid > 0) {
            $existing = $this->db->table('qb_questions')->where('id', $qid)->get()->getRowArray();
            if (!$existing) {
                $skipped++;
                log_message('warning', "QB save() skip idx={$idx}: invalid question id {$qid}");
                continue;
            }
        }

        // CI4 nested file: questions[IDX][question_image]
        $file = $files['questions'][$idx]['question_image'] ?? null;

        // fallback (optional)
        if (!$file) {
            $file = $this->request->getFile("questions.$idx.question_image");
        }

        $hasNewImage = $file && $file->isValid();

        // ✅ Validation:
        // - class/subject/topic always required
        // - if text mode => question text required
        // - if image mode => new upload OR (update) keep existing image
        if ($classId <= 0 || $subjectId <= 0 || $topicId <= 0) {
            $skipped++;
            log_message('warning', "QB save() skip idx={$idx}: missing class/subject/topic");
            continue;
        }

        if ($questionMedia === 'text') {
            if ($questionText === '') {
                $skipped++;
                log_message('warning', "QB save() skip idx={$idx}: question text empty (text mode)");
                continue;
            }
        } else { // image mode
            if (!$hasNewImage && !($qid > 0 && !empty($existing['question_image']))) {
                $skipped++;
                log_message('warning', "QB save() skip idx={$idx}: missing/invalid image (image mode, no existing)");
                continue;
            }

            if ($hasNewImage) {
                // validate image
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $mime    = $file->getMimeType();
                $sizeOk  = ($file->getSize() <= 2 * 1024 * 1024); // 2MB

                if (!in_array($mime, $allowed, true) || !$sizeOk) {
                    $skipped++;
                    log_message('warning', "QB save() skip idx={$idx}: invalid image mime/size (mime={$mime}, size={$file->getSize()})");
                    continue;
                }
            }
        }

        // Base fields (no created_* here — insert vs update below)
        $data = [
            'class_id'       => $classId,
            'subject_id'     => $subjectId,
            'topic_id'       => $topicId,
            'question_type'  => $type,
            'difficulty'     => $difficulty,

            // ✅ new columns
            'question_media'     => $questionMedia,
            'question_image'     => null,
            'question_image_alt' => ($imageAlt !== '' ? $imageAlt : null),

            // default
            'question' => null,
        ];

        // Drag flag (your existing)
        $data['is_drag'] = (isset($q['is_drag']) && $q['is_drag'] == '1') ? 1 : 0;

        // Ensure we always define these keys
        $data['option_a']       = null;
        $data['option_b']       = null;
        $data['option_c']       = null;
        $data['option_d']       = null;
        $data['correct_option'] = null;
        $data['answer_text']    = null;
        $data['options_json']   = null;

        // ✅ Apply question text/image
        if ($questionMedia === 'text') {
            $data['question'] = $questionText;
            $data['question_image'] = null;
        } else {
            if ($hasNewImage) {
                try {
                    $newName = $file->getRandomName();
                    $file->move($uploadDir, $newName);

                    $data['question_image'] = 'uploads/qb_questions/' . $newName;
                    $data['question']       = null;
                } catch (\Throwable $e) {
                    $skipped++;
                    log_message('error', "QB save() upload failed idx={$idx}: " . $e->getMessage());
                    continue;
                }
            } else {
                // Update without replacing image
                $data['question_image'] = $existing['question_image'] ?? null;
                $data['question']       = null;
            }
        }

        /* =======================================
           TYPE: MCQ (single correct)
        ======================================= */
        if ($type === 'mcq') {
            $data['option_a'] = $q['option_a'] ?? '';
            $data['option_b'] = $q['option_b'] ?? '';
            $data['option_c'] = $q['option_c'] ?? '';
            $data['option_d'] = $q['option_d'] ?? '';

            $correct = strtoupper(trim($q['correct_option'] ?? 'A'));
            $data['correct_option'] = in_array($correct, ['A', 'B', 'C', 'D'], true) ? $correct : 'A';
        }

        /* =======================================
           TYPE: MCQ MULTI (multiple correct)
        ======================================= */
        elseif ($type === 'mcq_multi') {

            $opts = [
                'A' => $q['option_a'] ?? '',
                'B' => $q['option_b'] ?? '',
                'C' => $q['option_c'] ?? '',
                'D' => $q['option_d'] ?? '',
            ];

            $data['option_a'] = $opts['A'];
            $data['option_b'] = $opts['B'];
            $data['option_c'] = $opts['C'];
            $data['option_d'] = $opts['D'];

            $correctMulti = $q['correct_multi'] ?? [];
            if (!is_array($correctMulti)) {
                $correctMulti = [];
            }

            $correctMulti = array_values(array_unique(
                array_map('trim', array_map('strtoupper', $correctMulti))
            ));

            $json = [
                'options'       => $opts,
                'correct_multi' => $correctMulti,
            ];

            $data['options_json']   = json_encode($json, JSON_UNESCAPED_UNICODE);
            $data['correct_option'] = null;
            $data['answer_text']    = null;
        }

        elseif ($type === 'tf') {
            $val = ($q['answer_text'] ?? '') === 'True' ? 'True' : 'False';
            $data['answer_text'] = $val;
        }

        elseif ($type === 'fill') {
            $data['answer_text'] = trim($q['answer_text'] ?? '');
        }

        elseif ($type === 'short') {
            $data['answer_text'] = trim($q['answer_text'] ?? '');
        }

        elseif ($type === 'descriptive') {
            $data['answer_text'] = trim($q['answer_text'] ?? '');
            if ($data['answer_text'] === '') {
                $skipped++;
                log_message('warning', "QB save() skip idx={$idx}: descriptive answer_text empty");
                continue;
            }
        }

        elseif ($type === 'match') {
            $pairs = $q['match_pairs'] ?? [];
            if (!empty($pairs) && is_array($pairs)) {
                $data['options_json'] = json_encode($pairs, JSON_UNESCAPED_UNICODE);
            } else {
                $data['options_json'] = null;
            }
        }

        /* =======================================
           INSERT or UPDATE
        ======================================= */
        try {
            if ($qid > 0) {
                $ok = $this->db->table('qb_questions')->where('id', $qid)->update($data);
                if ($ok === false) {
                    throw new \RuntimeException('update returned false');
                }
                $savedIds[] = $qid;
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = (int) ($this->session->get('member_id') ?? 0);
                $ok                  = $this->db->table('qb_questions')->insert($data);
                if (!$ok) {
                    $dbError = $this->db->error();
                    log_message(
                        'error',
                        'QB save() insert failed idx=' . $idx . ' : ' . json_encode($dbError)
                        . ' | data=' . json_encode($data)
                    );
                    $skipped++;
                    continue;
                }
                $savedIds[] = (int) $this->db->insertID();
            }

            $savedCount++;
        } catch (\Throwable $e) {
            log_message(
                'error',
                'QB save() exception: ' . $e->getMessage()
                . ' | idx=' . $idx
                . ' | data=' . json_encode($data)
            );
            $skipped++;
            continue;
        }
        }

        return [
            'saved'     => $savedCount,
            'skipped'   => $skipped,
            'total'     => $total,
            'saved_ids' => $savedIds,
        ];
    }


    /* -------------------------------------------------------------
     * List questions
     * ----------------------------------------------------------- */
    public function list()
    {
        $classId   = (int) ($this->request->getGet('class_id') ?? 0);
        $subjectId = (int) ($this->request->getGet('subject_id') ?? 0);
        $topicId   = (int) ($this->request->getGet('topic_id') ?? 0);

        $questions = $this->fetchQuestionsFiltered($classId, $subjectId, $topicId, 'DESC', null);

        return view('admin/question_bank_list', [
            'questions' => $questions,
        ]);
    }

    /**
     * AJAX: full question rows for proofreading / bulk edit (JSON).
     * Filters: class_id (required), optional subject_id, topic_id.
     */
    public function questionsJson(): ResponseInterface
    {
        $classId   = (int) ($this->request->getGet('class_id') ?? 0);
        $subjectId = (int) ($this->request->getGet('subject_id') ?? 0);
        $topicId   = (int) ($this->request->getGet('topic_id') ?? 0);
        $limit     = (int) ($this->request->getGet('limit') ?? 200);
        $limit     = max(1, min($limit, 500));

        if ($classId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 0,
                'message' => 'class_id is required',
            ]);
        }

        $total     = $this->countQuestionsFiltered($classId, $subjectId, $topicId);
        $rows      = $this->fetchQuestionsFiltered($classId, $subjectId, $topicId, 'ASC', $limit);
        $truncated = $total > count($rows);

        $questions = [];
        foreach ($rows as $row) {
            $questions[] = $this->normalizeQuestionBankRowForJson($row);
        }

        $first = $rows[0] ?? null;

        $labels = [
            'class_name'   => '',
            'subject_name' => '',
            'topic_name'   => '',
        ];
        if ($first) {
            $labels['class_name'] = (string) ($first->class_name ?? '');
            if ($subjectId > 0) {
                $labels['subject_name'] = (string) ($first->subject_name ?? '');
            }
            if ($topicId > 0) {
                $labels['topic_name'] = (string) ($first->topic_name ?? '');
            } elseif ($subjectId > 0) {
                $labels['topic_name'] = 'All topics in subject';
            } elseif ($classId > 0) {
                $labels['subject_name'] = 'All subjects';
                $labels['topic_name']   = 'All topics';
            }
        }

        return $this->response->setJSON([
            'status'    => 1,
            'meta'      => [
                'total'     => $total,
                'returned'  => count($questions),
                'truncated' => $truncated,
                'limit'     => $limit,
            ],
            'filter'    => [
                'class_id'   => $classId,
                'subject_id' => $subjectId,
                'topic_id'   => $topicId,
            ],
            'labels'    => $labels,
            'questions' => $questions,
        ]);
    }

    /**
     * Full-page proof read (same filters as questions-json).
     */
    public function proofRead()
    {
        $classId   = (int) ($this->request->getGet('class_id') ?? 0);
        $subjectId = (int) ($this->request->getGet('subject_id') ?? 0);
        $topicId   = (int) ($this->request->getGet('topic_id') ?? 0);

        if ($classId <= 0) {
            return redirect()->to(site_url('admin/question-bank/overview'))
                ->with('error', 'Invalid proof-read link: class is required.');
        }

        $limit = 500;
        $total = $this->countQuestionsFiltered($classId, $subjectId, $topicId);
        $rows  = $this->fetchQuestionsFiltered($classId, $subjectId, $topicId, 'ASC', $limit);

        $questions = [];
        foreach ($rows as $row) {
            $questions[] = $this->normalizeQuestionBankRowForJson($row);
        }

        $truncated = $total > count($questions);
        $first     = $rows[0] ?? null;

        $labels = [
            'class_name'   => '',
            'subject_name' => '',
            'topic_name'   => '',
        ];
        if ($first) {
            $labels['class_name'] = (string) ($first->class_name ?? '');
            if ($subjectId > 0) {
                $labels['subject_name'] = (string) ($first->subject_name ?? '');
            }
            if ($topicId > 0) {
                $labels['topic_name'] = (string) ($first->topic_name ?? '');
            } elseif ($subjectId > 0) {
                $labels['topic_name'] = 'All topics in subject';
            } elseif ($classId > 0) {
                $labels['subject_name'] = 'All subjects';
                $labels['topic_name']   = 'All topics';
            }
        }

        return view('admin/question_bank_proof_read', [
            'filter_class_id'   => $classId,
            'filter_subject_id' => $subjectId,
            'filter_topic_id'   => $topicId,
            'questions'         => $questions,
            'meta'              => [
                'total'     => $total,
                'returned'  => count($questions),
                'truncated' => $truncated,
                'limit'     => $limit,
            ],
            'labels' => $labels,
        ]);
    }



    /**
     * AJAX: get subjects for a class based on your real structure:
     * classes -> class_section -> section_subjects -> a_subject
     */
    // AJAX: subjects by class (GET ?class_id=123)
    public function subjects(): \CodeIgniter\HTTP\ResponseInterface
    {
        $classId  = (int) $this->request->getGet('class_id');
        $campusId = (int) ($this->session->get('member_campusid') ?? 0);

        if ($classId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Invalid class_id',
            ]);
        }

        $subjects = $this->fetchSubjectsForClass($classId, $campusId);
        $source   = $subjects['_source'] ?? 'section';
        unset($subjects['_source']);
        $list = $subjects['list'] ?? [];

        return $this->response->setJSON([
            'status'   => 'ok',
            'subjects' => $list,
            'count'    => count($list),
            'source'   => $source,
        ]);
    }

    /**
     * Resolve subjects for a class: timetable mapping, then QB topics/questions, then all subjects.
     *
     * @return array{list: list<array<string, mixed>>, _source: string}
     */
    private function fetchSubjectsForClass(int $classId, int $campusId): array
    {
        $sessionId = (int) ($this->session->get('member_sessionid') ?? $this->session->get('academic_session_id') ?? 0);

        $list = $this->fetchSubjectsFromClassSections($classId, $campusId, $sessionId, true);
        $source = 'section';

        if ($list === [] && $campusId > 0) {
            $list = $this->fetchSubjectsFromClassSections($classId, 0, $sessionId, true);
        }

        if ($list === []) {
            $list = $this->fetchSubjectsFromClassSections($classId, $campusId, $sessionId, false);
        }

        if ($list === [] && $campusId > 0) {
            $list = $this->fetchSubjectsFromClassSections($classId, 0, $sessionId, false);
        }

        if ($list === []) {
            $list   = $this->fetchSubjectsFromQbTopics($classId);
            $source = 'qb_topics';
        }

        if ($list === []) {
            $list   = $this->fetchSubjectsFromQbQuestions($classId);
            $source = 'qb_questions';
        }

        if ($list === []) {
            $list   = $this->fetchAllSubjectsForSystem();
            $source = 'allsubject';
        }

        $list = $this->dedupeSubjectsById($list);

        return ['list' => $list, '_source' => $source];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchSubjectsFromClassSections(int $classId, int $campusId, int $sessionId, bool $activeOnly): array
    {
        if (!$this->db->tableExists('class_section')
            || !$this->db->tableExists('section_subjects')
            || !$this->db->tableExists('allsubject')) {
            return [];
        }

        try {
            $builder = $this->db->table('class_section cs')
                ->select('DISTINCT a.sid AS subject_id, a.subject_name, a.subject_short_name', false)
                ->join('section_subjects ss', 'ss.cls_sec_id = cs.cls_sec_id', 'inner')
                ->join('allsubject a', 'a.sid = ss.subject_id', 'inner')
                ->where('cs.class_id', $classId);

            if ($campusId > 0 && $this->db->fieldExists('campus_id', 'class_section')) {
                $builder->where('cs.campus_id', $campusId);
            }

            if ($sessionId > 0 && $this->db->fieldExists('session_id', 'class_section')) {
                $builder->where('cs.session_id', $sessionId);
            }

            if ($activeOnly && $this->db->fieldExists('status', 'class_section')) {
                $builder->where('cs.status', 1);
            }

            if ($activeOnly && $this->db->fieldExists('status', 'section_subjects')) {
                $builder->where('ss.status', 1);
            }

            $rows = $builder->orderBy('a.subject_name', 'ASC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'QuestionBank::fetchSubjectsFromClassSections ' . $e->getMessage());

            return [];
        }

        return $this->filterSubjectRows($rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchSubjectsFromQbTopics(int $classId): array
    {
        if (!$this->db->tableExists('qb_topics')) {
            return [];
        }

        try {
            $builder = $this->db->table('qb_topics t')
                ->select('DISTINCT t.subject_id, a.subject_name, a.subject_short_name', false)
                ->where('t.class_id', $classId)
                ->where('t.subject_id >', 0);

            if ($this->db->tableExists('allsubject')) {
                $builder->join('allsubject a', 'a.sid = t.subject_id', 'left');
            } else {
                $builder->select('DISTINCT t.subject_id', false);
            }

            $rows = $builder->orderBy('a.subject_name', 'ASC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'QuestionBank::fetchSubjectsFromQbTopics ' . $e->getMessage());

            return [];
        }

        foreach ($rows as &$row) {
            if (empty($row['subject_name'])) {
                $row['subject_name'] = 'Subject #' . (int) ($row['subject_id'] ?? 0);
            }
        }
        unset($row);

        return $this->filterSubjectRows($rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchSubjectsFromQbQuestions(int $classId): array
    {
        if (!$this->db->tableExists('qb_questions')) {
            return [];
        }

        try {
            $builder = $this->db->table('qb_questions q')
                ->select('DISTINCT q.subject_id, a.subject_name, a.subject_short_name', false)
                ->where('q.class_id', $classId)
                ->where('q.subject_id >', 0);

            if ($this->db->tableExists('allsubject')) {
                $builder->join('allsubject a', 'a.sid = q.subject_id', 'left');
            } else {
                $builder->select('DISTINCT q.subject_id', false);
            }

            $rows = $builder->orderBy('a.subject_name', 'ASC')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'QuestionBank::fetchSubjectsFromQbQuestions ' . $e->getMessage());

            return [];
        }

        foreach ($rows as &$row) {
            if (empty($row['subject_name'])) {
                $row['subject_name'] = 'Subject #' . (int) ($row['subject_id'] ?? 0);
            }
        }
        unset($row);

        return $this->filterSubjectRows($rows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchAllSubjectsForSystem(): array
    {
        $rows = [];
        foreach ($this->getAllSubjects() as $row) {
            $arr    = (array) $row;
            $rows[] = [
                'subject_id'         => (int) ($arr['subject_id'] ?? 0),
                'subject_name'       => (string) ($arr['subject_name'] ?? ''),
                'subject_short_name' => (string) ($arr['subject_short_name'] ?? $arr['subject_name'] ?? ''),
            ];
        }

        return $this->filterSubjectRows($rows);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function filterSubjectRows(array $rows): array
    {
        return array_values(array_filter($rows, static function ($r) {
            $id = (int) ($r['subject_id'] ?? 0);
            if ($id <= 0) {
                return false;
            }
            $name = trim((string) ($r['subject_name'] ?? $r['subject_short_name'] ?? ''));

            return $name !== '';
        }));
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function dedupeSubjectsById(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $id = (int) ($r['subject_id'] ?? 0);
            if ($id > 0 && !isset($out[$id])) {
                $out[$id] = $r;
            }
        }

        usort($out, static function ($a, $b) {
            return strcasecmp((string) ($a['subject_name'] ?? ''), (string) ($b['subject_name'] ?? ''));
        });

        return array_values($out);
    }



    public function bankSearch(): ResponseInterface
    {
        $classId = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');
        $topicText = trim($this->request->getGet('topic') ?? '');
        $limit = (int) $this->request->getGet('limit') ?: 50;


        $builder = $this->db->table('qb_questions')
        ->select('id, question_type, question, correct_option, answer_text')
        ->where('class_id', $classId)
        ->where('subject_id', $subjectId);


        if ($topicText !== '') {
         $builder->like('question', $topicText);
        }


        $builder->limit($limit)->orderBy('id', 'DESC');


        $questions = $builder->get()->getResult();

        // echo "TEST";
        // print_r($builder->getLastQuery());
        // exit;

        return $this->response->setJSON($questions);
    }


    /* -------------------------------------------------------------
     * AJAX: topics by class+subject
     * ----------------------------------------------------------- */
    public function topics(): ResponseInterface
    {
        $classId   = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');

        $builder = $this->db->table('qb_topics')
            ->select('id, topic_name, description, class_id, subject_id')
            ->orderBy('id', 'ASC');

        if ($classId > 0) {
            $builder->where('class_id', $classId);
        }
        if ($subjectId > 0) {
            $builder->where('subject_id', $subjectId);
        }

        $res = $builder->get();
        $topics = $res ? $res->getResult() : [];

        return $this->response->setJSON($topics);
    }

    /* -------------------------------------------------------------
     * AJAX: save new topic
     * ----------------------------------------------------------- */
   public function saveTopic(): ResponseInterface
{
    $classId   = (int) $this->request->getPost('class_id');
    $subjectId = (int) $this->request->getPost('subject_id');
    $topicName = trim((string) $this->request->getPost('topic_name'));

    // Basic validation
    if ($classId <= 0 || $subjectId <= 0 || $topicName === '') {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Please select class, subject and enter topic name.');
    }

    $ok = $this->db->table('qb_topics')->insert([
        'class_id'   => $classId,
        'subject_id' => $subjectId,
        'topic_name' => $topicName,
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => (int) ($this->session->get('member_id') ?? 0),
    ]);

    if ($ok && $this->db->affectedRows() > 0) {
        // success – reload same page with success flash message
        return redirect()
            ->back()
            ->with('msg', 'Topic added successfully.');
    }

    // insert failed
    return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Unable to add topic. Please try again.');
}


// public function aiGenerate(): ResponseInterface
// {
//     if (!$this->request->isAJAX()) {
//         return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX only']);
//     }

//     $subjectId = (int) $this->request->getPost('subject_id');
//     $topicName = (string) $this->request->getPost('topic_name');
//     $count     = (int) ($this->request->getPost('count') ?? 5);
//     $aiTypes   = (string) $this->request->getPost('ai_types') ?: 'mcq';

//     $subjectLabel = '';
//     if ($subjectId > 0) {
//         $subjectLabel = $this->getSubjectNameById($subjectId);
//     }

//     $prompt = $this->buildMultiTypePrompt($aiTypes, $count, $subjectLabel, $topicName);

//     $providers = ['Gemini']; // later you can add 'DeepSeek', 'OpenRouter'

//     foreach ($providers as $provider) {
//         $method = "call{$provider}";
//         try {
//             if (!method_exists($this, $method)) {
//                 log_message('error', 'AI provider method missing: {m}', ['m' => $method]);
//                 continue;
//             }

//             [$text, $raw] = $this->$method($prompt);

//             if (empty($text)) {
//                 log_message('error', 'AI provider {p} returned empty text', ['p' => $provider]);
//                 continue;
//             }

//             $items = $this->decodeJsonQuestions($text);

//             if ($items !== null) {
//                 return $this->response->setJSON([
//                     'questions' => $items,
//                     'provider'  => strtolower($provider),
//                     'raw'       => $raw, // optional for debugging in dev only
//                 ]);
//             }

//             log_message('error', 'AI provider {p} JSON structure invalid', ['p' => $provider]);
//         } catch (\Throwable $e) {
//             log_message('error', 'AI provider {p} threw exception: {msg}', [
//                 'p'   => $provider,
//                 'msg' => $e->getMessage(),
//             ]);
//         }
//     }

//     // At this point, at least one provider was tried and failed
//     return $this->response->setJSON([
//         'error' => 'All AI providers failed',
//     ]);
// }

public function parseJsonMcqs(): ResponseInterface
{
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'msg'    => 'AJAX only',
        ]);
    }

    $raw = (string) $this->request->getPost('mcq_json');

    if (trim($raw) === '') {
        return $this->response->setJSON([
            'status' => 'error',
            'msg'    => 'Empty JSON text.',
        ]);
    }

    $decoded = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return $this->response->setJSON([
            'status' => 'error',
            'msg'    => 'Invalid JSON: ' . json_last_error_msg(),
        ]);
    }

    // Accept:
    // 1) { "mcqs": [ ... ] }
    // 2) { "questions": [ ... ] }
    // 3) [ ... ]
    if (isset($decoded['mcqs']) && is_array($decoded['mcqs'])) {
        $list = $decoded['mcqs'];
    } elseif (isset($decoded['questions']) && is_array($decoded['questions'])) {
        $list = $decoded['questions'];
    } elseif (is_array($decoded)) {
        $list = $decoded;
    } else {
        return $this->response->setJSON([
            'status' => 'error',
            'msg'    => 'JSON must be an array or contain "mcqs" / "questions" array.',
        ]);
    }

    $questions = [];
    $errors    = [];

    foreach ($list as $idx => $item) {
        if (!is_array($item)) {
            $errors[] = 'Item #' . ($idx + 1) . ' is not an object.';
            continue;
        }

        $norm = $this->normalizeJsonMcqItem($item, $idx);
        if ($norm === null) {
            $errors[] = 'Item #' . ($idx + 1) . ' is missing required fields (question/options).';
            continue;
        }

        $questions[] = $norm;
    }

    if (empty($questions)) {
        return $this->response->setJSON([
            'status' => 'error',
            'msg'    => 'No valid MCQs found in JSON.',
            'errors' => $errors,
        ]);
    }

    return $this->response->setJSON([
        'status'    => 'ok',
        'count'     => count($questions),
        'questions' => $questions,
        'errors'    => $errors, // optional; can be empty
    ]);
}

    /* -------------------------------------------------------------
     * Helpers
     * ----------------------------------------------------------- */
private function buildMultiTypePrompt(string $typesCsv, int $count, string $subject = '', string $topic = ''): string
{
    $types   = array_map('trim', explode(',', $typesCsv));
    $context = '';
    if ($subject !== '') {
        $context .= "Subject: {$subject}. ";
    }
    if ($topic !== '') {
        $context .= "Topic: {$topic}. ";
    }

    $parts = [];
    foreach ($types as $t) {
        switch ($t) {
            case 'mcq':
                $parts[] =
                    "include multiple-choice questions (mcq) with fields: " .
                    "question, option_a, option_b, option_c, option_d, correct_option.\n" .
                    "- For EACH mcq:\n" .
                    "  * EXACTLY one option must be correct.\n" .
                    "  * The correct answer MUST be placed in a RANDOM option (A, B, C, or D), " .
                    "    not always option_a.\n" .
                    "  * Set correct_option to the letter (A/B/C/D) of the correct option.\n" .
                    "  * Across all mcq questions, distribute correct_option values roughly evenly among A, B, C, and D.";
                break;

            case 'tf':
                $parts[] =
                    "include true/false questions (tf) with fields: question and answer_text (either \"True\" or \"False\").";
                break;

            case 'short':
                $parts[] =
                    "include short-answer questions (short) with fields: question and answer_text (a brief phrase or sentence).";
                break;

            case 'fill':
                $parts[] =
                    "include fill-in-the-blank questions (fill) with fields: " .
                    "question (use ____ for the blank) and answer_text (the missing word or phrase).";
                break;

            case 'match':
                $parts[] =
                    "include matching questions (match) with fields: question and answer_text " .
                    "(answer_text must be a JSON array of pairs, e.g. [[\"A\",\"1\"],[\"B\",\"2\"]]).";
                break;
        }
    }

    $typeInstructions = implode("\n", $parts);

    return
"You are a school question bank assistant.
{$context}
Generate {$count} questions TOTAL in pure JSON array format (no extra text).
Each array item MUST be a JSON object with at least:
- 'question_type' (one of: mcq, tf, short, fill, match)
- the specific fields required for its type.

{$typeInstructions}

General rules:
- All questions must be age-appropriate and academically correct.
- Do NOT include any explanation or solution text.
- Return ONLY a valid JSON array of question objects (no markdown, no comments, no trailing commas).";
}



    private function makeQuestionsListBuilder(int $classId, int $subjectId, int $topicId)
    {
        $builder = $this->db->table('qb_questions q')
            ->select('q.*, c.class_name, t.topic_name, s.subject_name')
            ->join('classes c', 'c.class_id = q.class_id', 'left');

        if ($this->db->tableExists('allsubject')) {
            $builder->join('allsubject s', 's.sid = q.subject_id', 'left');
        } elseif ($this->db->tableExists('subjects')) {
            $builder->join('subjects s', 's.subject_id = q.subject_id', 'left');
        } else {
            $builder->select("'Unknown' AS subject_name", false);
        }

        if ($this->db->tableExists('qb_topics')) {
            $builder->join('qb_topics t', 't.id = q.topic_id', 'left');
        } else {
            $builder->select("'Unknown' AS topic_name", false);
        }

        if ($classId > 0) {
            $builder->where('q.class_id', $classId);
        }
        if ($subjectId > 0) {
            $builder->where('q.subject_id', $subjectId);
        }
        if ($topicId > 0) {
            $builder->where('q.topic_id', $topicId);
        }

        return $builder;
    }

    /**
     * @return list<object>
     */
    private function fetchQuestionsFiltered(int $classId, int $subjectId, int $topicId, string $order = 'DESC', ?int $limit = null): array
    {
        try {
            $builder = $this->makeQuestionsListBuilder($classId, $subjectId, $topicId);
            $builder->orderBy('q.id', strtoupper($order) === 'ASC' ? 'ASC' : 'DESC');
            if ($limit !== null && $limit > 0) {
                $builder->limit($limit);
            }
            $query = $builder->get();
            if (!$query) {
                return [];
            }

            return $query->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'QuestionBank::fetchQuestionsFiltered failed - ' . $e->getMessage());

            return [];
        }
    }

    private function countQuestionsFiltered(int $classId, int $subjectId, int $topicId): int
    {
        try {
            $builder = $this->db->table('qb_questions q');
            if ($classId > 0) {
                $builder->where('q.class_id', $classId);
            }
            if ($subjectId > 0) {
                $builder->where('q.subject_id', $subjectId);
            }
            if ($topicId > 0) {
                $builder->where('q.topic_id', $topicId);
            }

            return (int) $builder->countAllResults();
        } catch (\Throwable $e) {
            log_message('error', 'QuestionBank::countQuestionsFiltered failed - ' . $e->getMessage());

            return 0;
        }
    }

    private function normalizeQuestionBankRowForJson(object $row): array
    {
        return \App\Libraries\QbQuestionPresenter::normalize($row);
    }



    private function getAllClasses(): array
    {
         $system_id = (int) getSchoolInfo()->system_id;
        try {
            $res = $this->db->table('classes')
            ->where('system_id', $system_id)
            ->where('status', 1)
            ->orderBy('class_id', 'ASC')->get();
            return $res->getResult();
        } catch (\Throwable $e) {
            return $this->db->table('classes')->orderBy('class_id', 'ASC')->get()->getResult();
        }
    }

    private function getAllSubjects(): array
    {
        foreach ([
            ['table' => 'allsubject', 'id' => 'sid',        'name' => 'subject_name'],
            ['table' => 'subjects',   'id' => 'subject_id', 'name' => 'subject_name'],
            ['table' => 'subject',    'id' => 'id',         'name' => 'name'],
        ] as $cfg) {
            if (! $this->db->tableExists($cfg['table'])) {
                continue;
            }

            try {
                $b = $this->db->table($cfg['table'])->select("{$cfg['id']} AS subject_id, {$cfg['name']} AS subject_name");
                if ($this->db->fieldExists('status', $cfg['table'])) {
                    $b->where('status', 1);
                }
                $q = $b->orderBy($cfg['id'], 'ASC')->get();
                if ($q) {
                    $rows = $q->getResult();
                    if (! empty($rows)) {
                        return $rows;
                    }
                }
            } catch (\Throwable $e) {
                log_message('debug', 'QuestionBank::getAllSubjects skipped ' . $cfg['table'] . ': ' . $e->getMessage());
            }
        }

        return [];
    }


   private function getSubjectNameById(int $id): string
    {
        $candidates = [
            ['table' => 'allsubject', 'id' => 'sid',        'name' => 'subject_name'],
            ['table' => 'subjects',   'id' => 'subject_id', 'name' => 'subject_name'],
            ['table' => 'subject',    'id' => 'id',         'name' => 'name'],
        ];

        foreach ($candidates as $cfg) {
            if (! $this->db->tableExists($cfg['table'])) {
                continue;
            }

            try {
                $q = $this->db->table($cfg['table'])
                    ->select($cfg['name'] . ' AS name')
                    ->where($cfg['id'], $id)
                    ->limit(1)
                    ->get();

                if ($q && ($row = $q->getRow())) {
                    return (string) ($row->name ?? '');
                }
            } catch (\Throwable $e) {
                log_message('debug', 'QuestionBank::getSubjectNameById skipped ' . $cfg['table'] . ': ' . $e->getMessage());
            }
        }

        return '';
    }

private function decodeJsonQuestions(?string $text): ?array
{
    if ($text === null || trim($text) === '') {
        log_message('error', 'AI decode failed: empty response text');
        return null;
    }

    // Strip markdown code fences if model returns ```json ... ```
    $clean = trim($text);

    // Optional: remove typical markdown fences
    if (str_starts_with($clean, '```')) {
        $clean = preg_replace('/^```[a-zA-Z0-9]*\s*/', '', $clean); // remove ```json or ```
        $clean = preg_replace('/```$/', '', $clean);
        $clean = trim($clean);
    }

    $data = json_decode($clean, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('error', 'AI decode failed: JSON error {err} text: {text}', [
            'err'  => json_last_error_msg(),
            'text' => mb_substr($clean, 0, 1000),
        ]);
        return null;
    }

    if (!is_array($data)) {
        log_message('error', 'AI decode failed: decoded data is not an array');
        return null;
    }

    // If it returns an object with "questions" key
    if (isset($data['questions']) && is_array($data['questions'])) {
        return $data['questions'];
    }

    // If it already returns a plain JSON array of objects
    return $data;
}

    /* ---------- AI HTTP clients ---------- */

    private function callGemini(string $prompt): array
    {
        $apiKey = getenv('google.api_key');
        if (!$apiKey) {
            return [null, 'Gemini not configured'];
        }

        #$url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}";
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$apiKey}";
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [null, 'cURL: ' . $err];
        }

        $decoded = json_decode($response, true);
        $text    = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return [$text, $response];
    }

    private function callDeepSeek(string $prompt): array
    {
        $apiKey = getenv('deepseek.api_key');
        if (!$apiKey) {
            return [null, 'DeepSeek not configured'];
        }

        $url = 'https://api.deepseek.com/v1/chat/completions';
        $payload = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'You output JSON only.'],
                ['role' => 'user',   'content' => $prompt],
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [null, 'cURL: ' . $err];
        }

        $decoded = json_decode($response, true);
        if (isset($decoded['error'])) {
            return [null, $response];
        }

        $content = $decoded['choices'][0]['message']['content'] ?? null;
        return [$content, $response];
    }

    private function callOpenRouter(string $prompt): array
    {
        $apiKey = getenv('openrouter.api_key');
        if (!$apiKey) {
            return [null, 'OpenRouter not configured'];
        }

        $url = 'https://openrouter.ai/api/v1/chat/completions';
        $payload = [
            'model'    => 'mistralai/mistral-7b-instruct',
            'messages' => [
                ['role' => 'system', 'content' => 'You output JSON only.'],
                ['role' => 'user',   'content' => $prompt],
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: https://yourdomain.com',
                'X-Title: Question Bank',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [null, 'cURL: ' . $err];
        }

        $decoded = json_decode($response, true);
        if (isset($decoded['error'])) {
            return [null, $response];
        }

        $content = $decoded['choices'][0]['message']['content'] ?? null;
        return [$content, $response];
    }
}
