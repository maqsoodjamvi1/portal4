<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class VocabBank extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = db_connect();
        $this->session = session();
        helper(['form', 'url']);
    }

    /* ============================================================
     * UI: Vocabulary Form
     * ============================================================ */
    public function index()
    {
        return view('admin/vocab_bank_form', [
            'classes'  => $this->getAllClasses(),
            //'subjects' => $this->getAllSubjects(),
        ]);
    }

   public function report()
{
    $classes = $this->getAllClasses(); // same as index()

    return view('admin/vocab_bank_report', [
        'classes' => $classes,
    ]);
}

   public function listofwords()
{
    $classes = $this->getAllClasses(); // same as index()

    return view('admin/vocab_bank_words', [
        'classes' => $classes,
    ]);
}




public function reportData()
{
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(400)
            ->setJSON(['status' => 'error', 'msg' => 'Invalid request type (not AJAX).']);
    }

    $cid = (int) $this->request->getGet('class_id');
    $sid = (int) $this->request->getGet('subject_id');

    // Support multiple topics: topic_ids=1,2,3
    $topicIdsParam = trim((string) $this->request->getGet('topic_ids'));
    $topicIds      = [];

    if ($topicIdsParam !== '') {
        foreach (explode(',', $topicIdsParam) as $idStr) {
            $id = (int) $idStr;
            if ($id > 0) {
                $topicIds[] = $id;
            }
        }
        $topicIds = array_values(array_unique($topicIds));
    }

    // Fallback: single topic_id
    if (empty($topicIds)) {
        $singleTid = (int) $this->request->getGet('topic_id');
        if ($singleTid > 0) {
            $topicIds = [$singleTid];
        }
    }

    if (! $cid || ! $sid || empty($topicIds)) {
        return $this->response->setJSON([
            'status' => 'error',
            'msg'    => 'Class, Subject and at least one Topic are required.',
        ]);
    }

    try {

        $builder = $this->db->table('vocab_bank');

        $builder->where('class_id',   $cid);
        $builder->where('subject_id', $sid);
        $builder->whereIn('topic_id', $topicIds);

        /* ============================
           🔹 UPDATED SELECT COLUMNS
           ============================ */
        $builder->select([
            'id',
            'class_id',
            'subject_id',
            'topic_id',

            'word',
            'meaning_en',
            'meaning_ur',
            'example_sentence',
            'part_of_speech',

            // 🔹 NEW VOCAB DETAIL FIELDS
            'syllables',
            'synonyms',
            'antonyms',
            'related_words',
            'confusing_pair',
            'confusing_pair_difference',
            'difficulty_level',
            'dictation_focus',
            'phonics_pattern'

        ]);

        $builder->orderBy('topic_id', 'ASC');

        $items = $builder->get()->getResultArray();

        // ---- Class name ----
        $className = '';
        $rowC = $this->db->table('classes')
            ->select('class_name')
            ->where('class_id', $cid)
            ->get()->getRowArray();
        if ($rowC) {
            $className = $rowC['class_name'];
        }

        // ---- Subject name ----
        $subjectName = '';
        $rowS = $this->db->table('allsubject')
            ->select('subject_name')
            ->where('sid', $sid)
            ->get()->getRowArray();
        if ($rowS) {
            $subjectName = $rowS['subject_name'];
        }

        // ---- Part-of-speech breakdown ----
        $posCounts = [];
        foreach ($items as $r) {
            $pos = trim($r['part_of_speech'] ?? '');
            if ($pos === '') {
                $pos = '(Not set)';
            }
            if (! isset($posCounts[$pos])) {
                $posCounts[$pos] = 0;
            }
            $posCounts[$pos]++;
        }

        $header = [
            'class_name'   => $className,
            'subject_name' => $subjectName,
            'total_words'  => count($items),
        ];

        $topicCounts = [];

foreach ($items as $r) {
    $tid = $r['topic_id'];
    if (!isset($topicCounts[$tid])) {
        $topicCounts[$tid] = 0;
    }
    $topicCounts[$tid]++;
}

        return $this->response->setJSON([
            'status'                => 'ok',
            'header'                => $header,
            'items'                 => $items,
            'part_of_speech_counts' => $posCounts,
            'topic_counts' => $topicCounts,
        ]);

    } catch (\Throwable $e) {

        log_message(
            'error',
            'VocabBank::reportData error: {msg}',
            ['msg' => $e->getMessage()]
        );

        return $this->response->setStatusCode(500)
            ->setJSON([
                'status' => 'error',
                'msg'    => 'DB / PHP error: ' . $e->getMessage(),
            ]);
    }
}


    /* ============================================================
     * SAVE VOCAB ENTRIES
     * ============================================================ */


public function save()
{
    // vocab rows: vocab[0][word], vocab[0][meaning_en], ...
    $json = trim((string) $this->request->getPost('vocab_json'));

    if ($json !== '') {
        $decoded = json_decode($json, true);

        if (! isset($decoded['vocab']) || ! is_array($decoded['vocab'])) {
            return redirect()->back()
                ->with('error', 'Invalid JSON format. Expected vocab array.');
        }

        $entries = $decoded['vocab'];
    }
    // 🔹 CASE 2: Normal form submission
    else {
        $entries = $this->request->getPost('vocab');
    }
    if (empty($entries) || !is_array($entries)) {
        return redirect()->back()->with('error', 'No vocabulary items submitted.');
    }

    // Normalize single row
    if (isset($entries['word'])) {
        $entries = [ $entries ];
    }

    // 🔹 GLOBAL IDs
    $globalClassId   = (int) (
        $this->request->getPost('vocab_master_class_id')
        ?? $this->request->getPost('class_id_master')
        ?? 0
    );

    $globalSubjectId = (int) (
        $this->request->getPost('vocab_master_subject_id')
        ?? $this->request->getPost('subject_id_master')
        ?? 0
    );

    $globalTopicId   = (int) (
        $this->request->getPost('vocab_master_topic_id')
        ?? $this->request->getPost('topic_id_master')
        ?? 0
    );

    $tbl     = $this->db->table('vocab_bank');
    $saved   = 0;
    $skipped = 0;
    $total   = count($entries);

    foreach ($entries as $v) {

        $word = trim($v['word'] ?? '');

        // ---- Required validation ----
        if (
            $globalClassId <= 0 ||
            $globalSubjectId <= 0 ||
            $globalTopicId <= 0 ||
            $word === ''
        ) {
            $skipped++;
            continue;
        }

        $data = [
            'class_id'         => $globalClassId,
            'subject_id'       => $globalSubjectId,
            'topic_id'         => $globalTopicId,

            'word'             => $word,
            'meaning_en'       => trim($v['meaning_en'] ?? ''),
            'meaning_ur'       => trim($v['meaning_ur'] ?? ''),
            'example_sentence' => trim($v['example_sentence'] ?? ''),
            'part_of_speech'   => trim($v['part_of_speech'] ?? ''),

            // 🔹 NEW AI VOCAB FIELDS
            'syllables'                  => trim($v['syllables'] ?? ''),
            'synonyms'                   => trim($v['synonyms'] ?? ''),
            'antonyms'                   => trim($v['antonyms'] ?? ''),
            'related_words'              => trim($v['related_words'] ?? ''),
            'confusing_pair'             => trim($v['confusing_pair'] ?? ''),
            'confusing_pair_difference'  => trim($v['confusing_pair_difference'] ?? ''),

            'difficulty_level'  => trim($v['difficulty_level'] ?? ''),

            'dictation_focus'  => trim($v['dictation_focus'] ?? ''),

            'phonics_pattern'  => trim($v['phonics_pattern'] ?? ''),

            'created_at'       => date('Y-m-d H:i:s'),
            'created_by'       => (int) ($this->session->get('member_id') ?? 0),
        ];

        try {
            log_message('debug', 'VOCAB ROW: ' . json_encode($v));
            if ($tbl->insert($data)) {
                $saved++;
            } else {
                $skipped++;
            }
        } catch (\Throwable $e) {
            log_message('error', 'VocabBank save() failed: ' . $e->getMessage());
            $skipped++;
        }
    }

    return redirect()
        ->to(base_url('admin/vocab-bank'))
        ->with('msg', "{$saved} of {$total} vocabulary items saved. Skipped: {$skipped}.");
}

public function getCount()
{
    $classId   = (int) $this->request->getGet('class_id');
    $subjectId = (int) $this->request->getGet('subject_id');
    $topicId   = (int) $this->request->getGet('topic_id');

    if ($classId <= 0 || $subjectId <= 0 || $topicId <= 0) {
        return $this->response->setJSON([
            'status' => 'error',
            'count'  => 0,
            'words'  => [],
            'msg'    => 'Missing class/subject/topic',
        ]);
    }

    // Get all words for this combo
    $rows = $this->db->table('vocab_bank')
        ->select('word')
        ->where('class_id', $classId)
        ->where('subject_id', $subjectId)
        ->where('topic_id', $topicId)
        ->orderBy('topic_id', 'ASC')
        ->get()
        ->getResult();

    $words = [];
    foreach ($rows as $r) {
        $w = trim((string) ($r->word ?? ''));
        if ($w !== '') {
            $words[] = $w;
        }
    }

    $count = count($words);

    return $this->response->setJSON([
        'status' => 'ok',
        'count'  => $count,
        'words'  => $words,                 // array of words
        'list'   => implode(', ', $words),  // ready comma-separated string if you need it
    ]);
}


    /* ============================================================
     * LIST VOCAB
     * ============================================================ */
    public function list()
    {
        $classId   = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');
        $topicId   = (int) $this->request->getGet('topic_id');

        $builder = $this->db->table('vocab_bank v')
            ->select('v.*, c.class_name, t.topic_name, s.subject_name')
            ->join('classes c', 'c.class_id = v.class_id', 'left')
            ->join('subjects s', 's.subject_id = v.subject_id', 'left')
            ->join('vocab_topics t', 't.id = v.topic_id', 'left')
            ->orderBy('v.topic_id', 'DESC');

        if ($classId > 0)   $builder->where('v.class_id', $classId);
        if ($subjectId > 0) $builder->where('v.subject_id', $subjectId);
        if ($topicId > 0)   $builder->where('v.topic_id', $topicId);

        $rows = $builder->get()->getResult();

        return view('admin/vocab_bank_list', [
            'vocab' => $rows
        ]);
    }


    /* ============================================================
     * AJAX: Topics
     * ============================================================ */
    public function topics(): ResponseInterface
    {
        $classId   = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');

        $builder = $this->db->table('vocab_topics')->orderBy('id', 'ASC');

        if ($classId > 0)   $builder->where('class_id', $classId);
        if ($subjectId > 0) $builder->where('subject_id', $subjectId);

        $topics = $builder->get()->getResult();

        return $this->response->setJSON($topics);
    }

    /* ============================================================
     * Helper functions
     * ============================================================ */
    public function getAllClasses(): array
    {
        return $this->db->table('classes')
            ->where('status', 1)
            ->orderBy('class_id')
            ->get()->getResult();
    }

     public function getAllSubjects(): \CodeIgniter\HTTP\ResponseInterface
    {
        $classId  = (int) $this->request->getGet('class_id');
        $campusId = (int) ($this->session->get('member_campusid') ?? 0);

        if ($classId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Invalid class_id'
            ]);
        }

        $builder = $this->db->table('class_section cs')
            ->select('DISTINCT a.sid AS subject_id, a.subject_name, a.subject_short_name', false)
            ->join('section_subjects ss', 'ss.cls_sec_id = cs.cls_sec_id', 'inner')
            ->join('allsubject a', 'a.sid = ss.subject_id', 'inner')
            ->where('cs.class_id', $classId)
            ->orderBy('a.subject_name', 'ASC');

        // If these columns exist, good; if not, ignore gracefully.
        try { $builder->where('ss.status', 1); } catch (\Throwable $e) {}
        try { if ($campusId > 0) { $builder->where('cs.campus_id', $campusId); } } catch (\Throwable $e) {}

        $query = null;
        try { $query = $builder->get(); } catch (\Throwable $e) { $query = null; }

        $subjects = [];
        if ($query) {
            $subjects = $query->getResultArray();
            // Filter out blanks defensively
            $subjects = array_values(array_filter($subjects, function ($r) {
                return isset($r['subject_name']) && trim((string)$r['subject_name']) !== '';
            }));
        }

        return $this->response->setJSON([
            'status'   => 'ok',
            'subjects' => $subjects,
            'count'    => count($subjects),
        ]);
    }

}
