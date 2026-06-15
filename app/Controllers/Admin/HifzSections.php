<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class HifzSections extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'hifz']);
        check_permission('admin-hifz-sections');
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->requireHifzCampus();
    }

    /**
     * Campus must have Hifz Program enabled (Campus Profile → Services).
     */
    protected function requireHifzCampus(): void
    {
        if (campusHifzEnabled()) {
            return;
        }

        if ($this->request->isAJAX()) {
            json_response([
                'success' => false,
                'msg'     => 'Enable Hifz Program in Campus Profile → Services tab.',
            ]);
        }

        redirect()->to(base_url('admin/#/profile_campus'))
            ->with('error', 'Enable Hifz Program in Campus Profile → Services tab.')
            ->send();
        exit;
    }

    public function index()
    {
        $campusId = (int) $this->session->get('member_campusid');

        $teachers = $this->db->table('users')
            ->select('id, first_name, last_name')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/hifz/sections/index', [
            'title'    => 'Hifz Sections',
            'teachers' => $teachers,
        ]);
    }

    public function data()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');

        return json_response([
            'success' => true,
            'rows'    => $this->fetchGridRows($campusId, $sessionId),
        ]);
    }

    public function add()
    {
        return redirect()->to(base_url('admin/hifz/sections'));
    }

    public function edit()
    {
        return redirect()->to(base_url('admin/hifz/sections'));
    }

    public function save()
    {
        return $this->bulkSave();
    }

    public function bulkSave()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $userId    = (int) $this->session->get('member_userid');
        $now       = date('Y-m-d H:i:s');

        $ids        = $this->request->getPost('hifz_sec_id');
        $names      = $this->request->getPost('section_name');
        $sortOrders = $this->request->getPost('sort_order');
        $statuses   = $this->request->getPost('status');
        $teacherIds = $this->request->getPost('teacher_id');

        if (! is_array($names)) {
            return json_response(['success' => false, 'msg' => 'No section data received.']);
        }

        $rowCount = count($names);
        $errors   = [];
        $toSave   = [];

        for ($i = 0; $i < $rowCount; $i++) {
            $secId   = (int) ($ids[$i] ?? 0);
            $name    = trim((string) ($names[$i] ?? ''));
            $sort    = (int) ($sortOrders[$i] ?? 0);
            $status  = ! empty($statuses[$i]) ? 1 : 0;
            $teacher = (int) ($teacherIds[$i] ?? 0);

            if ($secId <= 0 && $name === '') {
                continue;
            }

            if ($name === '') {
                $errors[] = ['row' => $i + 1, 'field' => 'section_name', 'message' => 'Section name is required.'];

                continue;
            }

            $toSave[] = [
                'hifz_sec_id' => $secId,
                'section_name' => $name,
                'sort_order'   => $sort,
                'status'       => $status,
                'teacher_id'   => $teacher,
            ];
        }

        if ($errors !== []) {
            return json_response([
                'success' => false,
                'msg'     => $errors[0]['message'],
                'errors'  => $errors,
            ]);
        }

        if ($toSave === []) {
            return json_response(['success' => false, 'msg' => 'Add at least one section with a name.']);
        }

        $namesSeen = [];
        foreach ($toSave as $idx => $row) {
            $key = strtolower($row['section_name']);
            if (isset($namesSeen[$key])) {
                return json_response([
                    'success' => false,
                    'msg'     => 'Duplicate section name: ' . $row['section_name'],
                    'errors'  => [['row' => $idx + 1, 'field' => 'section_name', 'message' => 'Duplicate section name.']],
                ]);
            }
            $namesSeen[$key] = true;
        }

        $validTeacherIds = [];
        if ($campusId > 0) {
            $teacherRows = $this->db->table('users')
                ->select('id')
                ->where('campus_id', $campusId)
                ->where('status', 1)
                ->get()
                ->getResultArray();
            foreach ($teacherRows as $t) {
                $validTeacherIds[(int) $t['id']] = true;
            }
        }

        $this->db->transStart();

        $saved = 0;

        foreach ($toSave as $idx => $row) {
            $secId    = $row['hifz_sec_id'];
            $teacherId = $row['teacher_id'];

            if ($teacherId > 0 && ! isset($validTeacherIds[$teacherId])) {
                $this->db->transRollback();

                return json_response([
                    'success' => false,
                    'msg'     => 'Invalid teacher on row ' . ($idx + 1) . '.',
                    'errors'  => [['row' => $idx + 1, 'field' => 'teacher_id', 'message' => 'Teacher not found.']],
                ]);
            }

            $payload = [
                'section_name' => $row['section_name'],
                'sort_order'   => $row['sort_order'],
                'status'       => $row['status'],
                'updated_date' => $now,
                'user_id'      => $userId,
            ];

            if ($secId > 0) {
                $exists = $this->db->table('hifz_sections')
                    ->where('hifz_sec_id', $secId)
                    ->where('campus_id', $campusId)
                    ->where('session_id', $sessionId)
                    ->get()
                    ->getRow();

                if (! $exists) {
                    $this->db->transRollback();

                    return json_response([
                        'success' => false,
                        'msg'     => 'Section not found on row ' . ($idx + 1) . '.',
                    ]);
                }

                $this->db->table('hifz_sections')
                    ->where('hifz_sec_id', $secId)
                    ->update($payload);
            } else {
                $payload['campus_id']    = $campusId;
                $payload['session_id']   = $sessionId;
                $payload['created_date'] = $now;
                $this->db->table('hifz_sections')->insert($payload);
                $secId = (int) $this->db->insertID();
            }

            $this->syncTeacherForSection($secId, $sessionId, $campusId, $teacherId, $userId, $now);
            $saved++;
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return json_response(['success' => false, 'msg' => 'Failed to save sections. Please try again.']);
        }

        return json_response([
            'success' => true,
            'msg'     => $saved === 1 ? '1 section saved.' : $saved . ' sections saved.',
            'rows'    => $this->fetchGridRows($campusId, $sessionId),
        ]);
    }

    public function toggleStatus()
    {
        $id        = (int) $this->request->getPost('id');
        $status    = (int) $this->request->getPost('status');
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');

        $this->db->table('hifz_sections')
            ->where('hifz_sec_id', $id)
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->update([
                'status'       => $status ? 1 : 0,
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id'      => (int) $this->session->get('member_userid'),
            ]);

        return json_response(['success' => true, 'msg' => 'Status updated.']);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function fetchGridRows(int $campusId, int $sessionId): array
    {
        $sections = $this->db->table('hifz_sections')
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('section_name', 'ASC')
            ->get()
            ->getResult();

        $rows = [];
        $n    = 1;

        foreach ($sections as $sec) {
            $secId = (int) $sec->hifz_sec_id;

            $studentCount = (int) $this->db->table('hifz_students')
                ->where('hifz_sec_id', $secId)
                ->where('session_id', $sessionId)
                ->where('status', 1)
                ->countAllResults();

            $hts = $this->db->table('hifz_teacher_sections')
                ->select('teacher_id')
                ->where('hifz_sec_id', $secId)
                ->where('session_id', $sessionId)
                ->where('status', 1)
                ->get()
                ->getRow();

            $teacherId   = (int) ($hts->teacher_id ?? 0);
            $teacherName = '';

            if ($teacherId > 0) {
                $u = $this->db->table('users')
                    ->select('first_name, last_name')
                    ->where('id', $teacherId)
                    ->get()
                    ->getRow();
                $teacherName = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : '';
            }

            $rows[] = [
                'sno'           => $n++,
                'hifz_sec_id'   => $secId,
                'section_name'  => (string) $sec->section_name,
                'sort_order'    => (int) $sec->sort_order,
                'student_count' => $studentCount,
                'teacher_id'    => $teacherId,
                'teacher_name'  => $teacherName !== '' ? $teacherName : '—',
                'status'        => (int) $sec->status,
            ];
        }

        return $rows;
    }

    protected function syncTeacherForSection(
        int $hifzSecId,
        int $sessionId,
        int $campusId,
        int $teacherId,
        int $userId,
        string $now
    ): void {
        $this->db->table('hifz_teacher_sections')
            ->where('hifz_sec_id', $hifzSecId)
            ->where('session_id', $sessionId)
            ->delete();

        if ($teacherId <= 0) {
            return;
        }

        $this->db->table('hifz_teacher_sections')->insert([
            'hifz_sec_id'  => $hifzSecId,
            'teacher_id'   => $teacherId,
            'session_id'   => $sessionId,
            'campus_id'    => $campusId,
            'status'       => 1,
            'created_date' => $now,
            'user_id'      => $userId,
        ]);
    }
}
