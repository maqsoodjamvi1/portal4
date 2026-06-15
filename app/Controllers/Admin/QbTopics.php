<?php

namespace App\Controllers\Admin;



use App\Controllers\BaseController;

use App\Libraries\QbBoardPublisherService;

use CodeIgniter\HTTP\ResponseInterface;



class QbTopics extends BaseController

{

    protected $db;

    protected $session;

    protected QbBoardPublisherService $boardService;



    public function __construct()

    {

        $this->db           = \Config\Database::connect();

        $this->session      = session();

        $this->boardService = new QbBoardPublisherService($this->db);

    }



    public function index()

    {

        $systemId = $this->resolveSystemId();

        $classes  = $this->db->table('classes')

            ->select('class_id, class_name')

            ->where('system_id', $systemId)

            ->where('status', 1)

            ->orderBy('class_id', 'ASC')

            ->get()->getResult();



        return view('admin/topics_bulk', [

            'classes'         => $classes,

            'boardPublishers' => $this->boardService->listForSystem($systemId, true),

        ]);

    }



    public function data(): ResponseInterface

    {

        if (! $this->request->isAJAX()) {

            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'msg' => 'Invalid request']);

        }



        $classId   = (int) $this->request->getGet('class_id');

        $subjectId = (int) $this->request->getGet('subject_id');

        $filterBp  = $this->intListFromRequest($this->request->getGet('board_publisher_ids'));



        if ($classId <= 0 || $subjectId <= 0) {

            return $this->response->setJSON(['status' => 'ok', 'rows' => []]);

        }



        $rows = $this->db->table('qb_topics')

            ->select('id, topic_name, description')

            ->where('class_id', $classId)

            ->where('subject_id', $subjectId)

            ->orderBy('id', 'ASC')

            ->get()->getResultArray();



        $topicIds = array_map(static fn ($r) => (int) ($r['id'] ?? 0), $rows);

        $linkMap  = $this->boardService->getIdsMapForTopics($topicIds);

        $labelMap = $this->boardService->getLabelsMapForTopics($topicIds);



        $out = [];

        foreach ($rows as $row) {

            $tid = (int) ($row['id'] ?? 0);

            $ids = $linkMap[$tid] ?? [];



            if ($filterBp !== [] && ! $this->topicMatchesBoardFilter($tid, $ids, $filterBp)) {

                continue;

            }



            $row['board_publisher_ids'] = $ids;

            $row['board_publishers']      = $labelMap[$tid] ?? [];

            $out[] = $row;

        }



        return $this->response->setJSON(['status' => 'ok', 'rows' => $out]);

    }



    public function saveBulk(): ResponseInterface

    {

        if (! $this->request->isAJAX()) {

            return $this->response->setStatusCode(400)

                ->setJSON(['status' => 'error', 'msg' => 'Invalid request (not AJAX).']);

        }



        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || empty($payload)) {

            $payload = $this->request->getPost();

        }



        $classId   = (int) ($payload['class_id'] ?? 0);

        $subjectId = (int) ($payload['subject_id'] ?? 0);

        $topics    = $payload['topics'] ?? null;



        if ($classId <= 0 || $subjectId <= 0) {

            return $this->response->setJSON(['status' => 'error', 'msg' => 'Class and Subject are required.']);

        }

        if (! is_array($topics)) {

            return $this->response->setJSON([

                'status' => 'error',

                'msg'    => 'No topics submitted (topics must be array).',

                'debug'  => ['received_keys' => array_keys((array) $payload)],

            ]);

        }



        $memberId = (int) (session()->get('member_id') ?? 0);

        $now      = date('Y-m-d H:i:s');



        $clean = [];

        foreach ($topics as $row) {

            $id   = (int) ($row['id'] ?? 0);

            $name = trim((string) ($row['topic_name'] ?? ''));

            if ($name === '') {

                continue;

            }

            $desc = trim((string) ($row['description'] ?? ''));

            $bpIds = $row['board_publisher_ids'] ?? [];

            if (! is_array($bpIds)) {

                $bpIds = [];

            }



            $clean[] = [

                'id'                   => $id,

                'topic_name'           => $name,

                'description'          => $desc !== '' ? $desc : null,

                'board_publisher_ids'  => array_values(array_unique(array_filter(array_map('intval', $bpIds)))),

            ];

        }



        if (! $clean) {

            return $this->response->setJSON(['status' => 'error', 'msg' => 'Please enter at least one topic name.']);

        }



        $seen   = [];

        $unique = [];

        foreach ($clean as $r) {

            $key = mb_strtolower($r['topic_name']);

            if (isset($seen[$key])) {

                continue;

            }

            $seen[$key] = true;

            $unique[]   = $r;

        }



        $db = $this->db;

        $db->transBegin();



        try {

            $existing = $db->table('qb_topics')

                ->select('id, topic_name')

                ->where('class_id', $classId)

                ->where('subject_id', $subjectId)

                ->get()->getResultArray();



            $existingByLower = [];

            foreach ($existing as $ex) {

                $existingByLower[mb_strtolower($ex['topic_name'])] = (int) $ex['id'];

            }



            $updated = 0;

            $inserted = 0;



            foreach ($unique as $r) {

                $id    = (int) $r['id'];

                $name  = $r['topic_name'];

                $lower = mb_strtolower($name);

                $topicId = 0;



                if ($id > 0) {

                    $db->table('qb_topics')

                        ->where('id', $id)

                        ->where('class_id', $classId)

                        ->where('subject_id', $subjectId)

                        ->update([

                            'topic_name'  => $name,

                            'description' => $r['description'],

                        ]);



                    $topicId = $id;

                    if ($db->affectedRows() > 0) {

                        $updated++;

                    }

                } else {

                    if (isset($existingByLower[$lower])) {

                        $topicId = $existingByLower[$lower];

                    } else {

                        $db->table('qb_topics')->insert([

                            'class_id'    => $classId,

                            'subject_id'  => $subjectId,

                            'topic_name'  => $name,

                            'description' => $r['description'],

                            'created_at'  => $now,

                            'created_by'  => $memberId,

                        ]);



                        if ($db->affectedRows() > 0) {

                            $inserted++;

                            $topicId = (int) $db->insertID();

                            $existingByLower[$lower] = $topicId;

                        }

                    }

                }



                if ($topicId > 0) {

                    $this->boardService->syncTopicLinks($topicId, $r['board_publisher_ids']);

                }

            }



            if ($db->transStatus() === false) {

                throw new \RuntimeException('Transaction failed.');

            }



            $db->transCommit();



            return $this->response->setJSON([

                'status' => 'ok',

                'msg'    => "Saved successfully. Updated: {$updated}, Inserted: {$inserted}",

            ]);

        } catch (\Throwable $e) {

            $db->transRollback();



            return $this->response->setStatusCode(500)->setJSON([

                'status' => 'error',

                'msg'    => 'Failed to save topics. ' . $e->getMessage(),

            ]);

        }

    }



    /**

     * @param list<int> $linkedIds

     * @param list<int> $filterIds

     */

    protected function topicMatchesBoardFilter(int $topicId, array $linkedIds, array $filterIds): bool

    {

        if ($filterIds === []) {

            return true;

        }



        if ($linkedIds === []) {

            return true;

        }



        foreach ($linkedIds as $id) {

            if (in_array($id, $filterIds, true)) {

                return true;

            }

        }



        return false;

    }



    /**

     * @param mixed $value

     * @return list<int>

     */

    protected function intListFromRequest($value): array

    {

        if (! is_array($value)) {

            if (is_string($value) && $value !== '') {

                $value = explode(',', $value);

            } else {

                return [];

            }

        }



        $out = [];

        foreach ($value as $item) {

            $id = (int) $item;

            if ($id > 0) {

                $out[] = $id;

            }

        }



        return array_values(array_unique($out));

    }



    protected function resolveSystemId(): int

    {

        $campusId = (int) ($this->session->get('member_campusid') ?? 0);

        if ($campusId <= 0) {

            return 1;

        }



        $row = $this->db->table('campus')

            ->select('system_id')

            ->where('campus_id', $campusId)

            ->limit(1)

            ->get()

            ->getRow();



        return (int) ($row->system_id ?? 1);

    }

}
