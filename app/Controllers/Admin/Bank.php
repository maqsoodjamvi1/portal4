<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Bank extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['text']);
    }

    /**
     * GET /admin/bank/search?class_id=..&subject_id=..&topic=..&limit=50
     * Returns JSON: [{id, question_type, question, option_a, option_b, option_c, option_d, correct_option, answer_text}]
     */
    public function search(): ResponseInterface
    {
        $classId   = (int) ($this->request->getGet('class_id') ?? 0);
        $subjectId = (int) ($this->request->getGet('subject_id') ?? 0);
        $topic     = trim((string) ($this->request->getGet('topic') ?? ''));
        $limit     = (int) ($this->request->getGet('limit') ?? 50);
        if ($limit <= 0 || $limit > 500) $limit = 50;

        // Base: qb_questions (adjust name if your bank table is different)
        $b = $this->db->table('qb_questions')
            ->select('
                id,
                question_type,
                question,
                option_a, option_b, option_c, option_d,
                correct_option,
                answer_text
            ', false)
            ->limit($limit)
            ->orderBy('id', 'DESC'); // if qb_id exists this still works due to select alias

        if ($classId > 0)   $b->where('class_id', $classId);
        if ($subjectId > 0) $b->where('subject_id', $subjectId);
        //if ($topic !== '')  $b->like('topic', $topic, 'both'); // adjust if your column name differs

        // If your table uses qb_id as PK (common), ensure select alias above matches
        $res  = $b->get();
        $rows = $res ? $res->getResultArray() : [];
       

        // Guarantee keys exist for front-end
        foreach ($rows as &$r) {
            $r['id']             = (int) ($r['id'] ?? 0);              // COALESCE ensures filled
            $r['question_type']  = $r['question_type'] ?? 'mcq_single';
            $r['question']       = $r['question'] ?? '';
            $r['option_a']       = $r['option_a'] ?? null;
            $r['option_b']       = $r['option_b'] ?? null;
            $r['option_c']       = $r['option_c'] ?? null;
            $r['option_d']       = $r['option_d'] ?? null;
            $r['correct_option'] = $r['correct_option'] ?? null;
            $r['answer_text']    = $r['answer_text'] ?? null;
        }
        unset($r);

        return $this->response->setJSON($rows);
    }
}
