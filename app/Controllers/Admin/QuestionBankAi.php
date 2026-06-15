<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\QuestionBankAiService;
use CodeIgniter\HTTP\ResponseInterface;

class QuestionBankAi extends BaseController
{
    protected $db;
    protected $session;
    protected QuestionBankAiService $aiService;

    public function __construct()
    {
        $this->db        = db_connect();
        $this->session   = session();
        $this->aiService = new QuestionBankAiService();
        helper(['form', 'url']);
        check_any_permission([
            'admin-question-bank-ai',
            'admin-questions',
            'admin-question-bank-overview',
            'admin-exams',
        ], true);
    }

    public function index()
    {
        return view('admin/question_bank_ai_generate', [
            'classes' => $this->getAllClasses(),
        ]);
    }

    public function generate(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'AJAX only',
            ]);
        }

        $classId   = (int) $this->request->getPost('class_id');
        $subjectId = (int) $this->request->getPost('subject_id');
        $topicId   = (int) $this->request->getPost('topic_id');

        if ($classId <= 0 || $subjectId <= 0 || $topicId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Please select class, subject, and topic.',
            ]);
        }

        $counts = $this->aiService->sanitizeCounts([
            'mcq'          => $this->request->getPost('count_mcq'),
            'tf'           => $this->request->getPost('count_tf'),
            'fill'         => $this->request->getPost('count_fill'),
            'short'        => $this->request->getPost('count_short'),
            'descriptive'  => $this->request->getPost('count_descriptive'),
            'match'        => $this->request->getPost('count_match'),
        ]);

        $context = [
            'class_name'         => $this->getClassNameById($classId),
            'subject_name'       => $this->getSubjectNameById($subjectId),
            'topic_name'         => '',
            'topic_description'  => '',
            'extra_instructions' => trim((string) $this->request->getPost('extra_instructions')),
        ];

        $topicRow = $this->db->table('qb_topics')
            ->select('topic_name, description')
            ->where('id', $topicId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($topicRow) {
            $context['topic_name'] = (string) ($topicRow['topic_name'] ?? '');
        }

        $postedDesc = trim((string) $this->request->getPost('topic_description'));
        if ($postedDesc !== '') {
            $context['topic_description'] = $postedDesc;
        } elseif ($topicRow) {
            $context['topic_description'] = trim((string) ($topicRow['description'] ?? ''));
        }

        $result = $this->aiService->generate($context, $counts);

        $difficulty = trim((string) $this->request->getPost('difficulty'));
        if (!in_array($difficulty, ['easy', 'normal', 'hard'], true)) {
            $difficulty = 'normal';
        }

        if (($result['status'] ?? '') === 'ok' && !empty($result['questions'])) {
            foreach ($result['questions'] as &$q) {
                $q['class_id']    = $classId;
                $q['subject_id']  = $subjectId;
                $q['topic_id']    = $topicId;
                $q['difficulty']  = $difficulty;
            }
            unset($q);
        }

        $code = ($result['status'] ?? '') === 'ok' ? 200 : 422;

        return $this->response->setStatusCode($code)->setJSON($result);
    }

    private function getAllClasses(): array
    {
        $systemId = (int) (getSchoolInfo()->system_id ?? 0);
        try {
            $b = $this->db->table('classes')->orderBy('class_id', 'ASC');
            if ($systemId > 0) {
                $b->where('system_id', $systemId)->where('status', 1);
            }

            return $b->get()->getResult();
        } catch (\Throwable $e) {
            return $this->db->table('classes')->orderBy('class_id', 'ASC')->get()->getResult();
        }
    }

    private function getClassNameById(int $id): string
    {
        if ($id <= 0) {
            return '';
        }
        try {
            $row = $this->db->table('classes')->select('class_name')->where('class_id', $id)->limit(1)->get()->getRow();

            return $row ? (string) ($row->class_name ?? '') : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function saveTopic(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'AJAX only',
            ]);
        }

        $classId   = (int) $this->request->getPost('class_id');
        $subjectId = (int) $this->request->getPost('subject_id');
        $topicName = trim((string) $this->request->getPost('topic_name'));
        $desc      = trim((string) $this->request->getPost('description'));

        if ($classId <= 0 || $subjectId <= 0 || $topicName === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Class, subject, and topic name are required.',
            ]);
        }

        $existingRows = $this->db->table('qb_topics')
            ->select('id, topic_name')
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->get()
            ->getResultArray();

        $lower = mb_strtolower($topicName);
        foreach ($existingRows as $ex) {
            if (mb_strtolower((string) ($ex['topic_name'] ?? '')) === $lower) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status'  => 'error',
                    'message' => 'A topic with this name already exists for this class and subject.',
                ]);
            }
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('qb_topics')->insert([
            'class_id'    => $classId,
            'subject_id'  => $subjectId,
            'topic_name'  => $topicName,
            'description' => $desc !== '' ? $desc : null,
            'created_at'  => $now,
            'created_by'  => (int) ($this->session->get('member_id') ?? 0),
        ]);

        $newId = (int) $this->db->insertID();
        if ($newId <= 0) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Could not save topic.',
            ]);
        }

        return $this->response->setJSON([
            'status'      => 'ok',
            'message'     => 'Topic added.',
            'id'          => $newId,
            'topic_name'  => $topicName,
            'description' => $desc,
        ]);
    }

    public function updateTopic(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'AJAX only',
            ]);
        }

        $topicId = (int) $this->request->getPost('topic_id');
        $desc    = trim((string) $this->request->getPost('description'));

        if ($topicId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Topic is required.',
            ]);
        }

        $row = $this->db->table('qb_topics')
            ->select('id')
            ->where('id', $topicId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Topic not found.',
            ]);
        }

        $this->db->table('qb_topics')
            ->where('id', $topicId)
            ->update(['description' => $desc !== '' ? $desc : null]);

        return $this->response->setJSON([
            'status'  => 'ok',
            'message' => 'Topic description saved.',
        ]);
    }

    private function getSubjectNameById(int $id): string
    {
        $candidates = [
            ['table' => 'subjects', 'id' => 'subject_id', 'name' => 'subject_name'],
            ['table' => 'allsubject', 'id' => 'sid', 'name' => 'subject_name'],
            ['table' => 'subject', 'id' => 'id', 'name' => 'name'],
        ];

        foreach ($candidates as $cfg) {
            try {
                $row = $this->db->table($cfg['table'])
                    ->select($cfg['name'] . ' AS name')
                    ->where($cfg['id'], $id)
                    ->limit(1)
                    ->get()
                    ->getRow();
                if ($row) {
                    return (string) ($row->name ?? '');
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return '';
    }
}
