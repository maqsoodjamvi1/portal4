<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class QbTopics extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {

        $campusId  = (int) ($this->session->get('member_campusid') ?? 0);
    $sessionId = (int) ($this->session->get('member_sessionid') ?? $this->session->get('academic_session_id') ?? 0);

 $systemId = 0;
        $row = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', $campusId)
            ->limit(1)->get()->getRow();

             if ($row) $systemId = (int) ($row->system_id ?? 0);
        // Load classes list from your existing table/logic
        // Adjust table name/fields if yours differ.
        $classes = $this->db->table('classes')
            ->select('class_id, class_name')
             ->where('system_id', $systemId)
                ->where('status', 1)
            ->orderBy('class_id', 'ASC')
            ->get()->getResult();

        return view('admin/topics_bulk', [
            'classes' => $classes,
        ]);
    }

    public function data(): ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'msg' => 'Invalid request']);
        }

        $classId   = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');

        if ($classId <= 0 || $subjectId <= 0) {
            return $this->response->setJSON(['status' => 'ok', 'rows' => []]);
        }

        $rows = $this->db->table('qb_topics')
            ->select('id, topic_name')
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON(['status' => 'ok', 'rows' => $rows]);
    }


 public function saveBulk(): ResponseInterface
{
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(400)
            ->setJSON(['status' => 'error', 'msg' => 'Invalid request (not AJAX).']);
    }

    // Read JSON body (preferred)
    $payload = $this->request->getJSON(true);

    // Fallback to form-post (if you ever use it)
    if (!is_array($payload) || empty($payload)) {
        $payload = $this->request->getPost();
    }

    $classId   = (int)($payload['class_id'] ?? 0);
    $subjectId = (int)($payload['subject_id'] ?? 0);
    $topics    = $payload['topics'] ?? null;

    if ($classId <= 0 || $subjectId <= 0) {
        return $this->response->setJSON(['status' => 'error', 'msg' => 'Class and Subject are required.']);
    }
    if (!is_array($topics)) {
        return $this->response->setJSON([
            'status' => 'error',
            'msg'    => 'No topics submitted (topics must be array).',
            'debug'  => ['received_keys' => array_keys((array)$payload)]
        ]);
    }

    $memberId = (int) (session()->get('member_id') ?? 0);
    $now      = date('Y-m-d H:i:s');

    // Normalize
    $clean = [];
    foreach ($topics as $row) {
        $id   = (int)($row['id'] ?? 0);
        $name = trim((string)($row['topic_name'] ?? ''));
        if ($name === '') continue;
        $clean[] = ['id' => $id, 'topic_name' => $name];
    }

    if (!$clean) {
        return $this->response->setJSON(['status' => 'error', 'msg' => 'Please enter at least one topic name.']);
    }

    // Unique submitted (case-insensitive)
    $seen = [];
    $unique = [];
    foreach ($clean as $r) {
        $key = mb_strtolower($r['topic_name']);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $unique[] = $r;
    }

    $db = $this->db;
    $db->transBegin();

    try {
        // Existing (for duplicate avoidance)
        $existing = $db->table('qb_topics')
            ->select('id, topic_name')
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->get()->getResultArray();

        $existingByLower = [];
        foreach ($existing as $ex) {
            $existingByLower[mb_strtolower($ex['topic_name'])] = (int)$ex['id'];
        }

        $updated = 0;
        $inserted = 0;

        foreach ($unique as $r) {
            $id    = (int)$r['id'];
            $name  = $r['topic_name'];
            $lower = mb_strtolower($name);

            if ($id > 0) {
                // Update only if row belongs to same class/subject
                $db->table('qb_topics')
                    ->where('id', $id)
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->update([
                        'topic_name' => $name,
                        
                    ]);

                if ($db->affectedRows() > 0) $updated++;
            } else {
                // Skip if exists already by same name
                if (isset($existingByLower[$lower])) continue;

                $db->table('qb_topics')->insert([
                    'class_id'   => $classId,
                    'subject_id' => $subjectId,
                    'topic_name' => $name,
                    'created_at' => $now,
                    'created_by' => $memberId,
                ]);

                if ($db->affectedRows() > 0) $inserted++;
            }
        }

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Transaction failed.');
        }

        $db->transCommit();

        return $this->response->setJSON([
            'status'   => 'ok',
            'msg'      => "Saved successfully. Updated: {$updated}, Inserted: {$inserted}",
        ]);
    } catch (\Throwable $e) {
        $db->transRollback();
        return $this->response->setStatusCode(500)->setJSON([
            'status' => 'error',
            'msg'    => 'Failed to save topics. ' . $e->getMessage(),
        ]);
    }
}



}
