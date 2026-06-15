<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class HifzTeachers extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'hifz']);
        check_permission('admin-hifz-teachers');
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->requireHifzCampus();
    }

    protected function requireHifzCampus(): void
    {
        if (campusHifzEnabled()) {
            return;
        }

        if ($this->request->isAJAX()) {
            json_response(['success' => false, 'msg' => 'Enable Hifz Program in Campus Profile → Services tab.']);
        }

        redirect()->to(base_url('admin/#/profile_campus'))->send();
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

        return view('admin/hifz/teachers/index', [
            'title'    => 'Assign Hifz Teachers',
            'teachers' => $teachers,
        ]);
    }

    public function data()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');

        $sections = $this->db->table('hifz_sections hs')
            ->select('hs.hifz_sec_id, hs.section_name, hs.sort_order')
            ->select('COUNT(hst.id) AS student_count', false)
            ->select('hts.teacher_id, CONCAT(u.first_name, " ", COALESCE(u.last_name, "")) AS teacher_name', false)
            ->join('hifz_students hst', 'hst.hifz_sec_id = hs.hifz_sec_id AND hst.session_id = hs.session_id AND hst.status = 1', 'left')
            ->join('hifz_teacher_sections hts', 'hts.hifz_sec_id = hs.hifz_sec_id AND hts.session_id = hs.session_id AND hts.status = 1', 'left')
            ->join('users u', 'u.id = hts.teacher_id', 'left')
            ->where('hs.campus_id', $campusId)
            ->where('hs.session_id', $sessionId)
            ->where('hs.status', 1)
            ->groupBy('hs.hifz_sec_id, hs.section_name, hs.sort_order, hts.teacher_id, u.first_name, u.last_name')
            ->orderBy('hs.sort_order', 'ASC')
            ->orderBy('hs.section_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['data' => $sections]);
    }

    public function save()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $userId    = (int) $this->session->get('member_userid');
        $hifzSecId = (int) $this->request->getPost('hifz_sec_id');
        $teacherId = (int) $this->request->getPost('teacher_id');
        $now       = date('Y-m-d H:i:s');

        if ($hifzSecId <= 0) {
            return json_response(['success' => false, 'msg' => 'Invalid Hifz section.']);
        }

        $section = $this->db->table('hifz_sections')
            ->where('hifz_sec_id', $hifzSecId)
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();

        if (! $section) {
            return json_response(['success' => false, 'msg' => 'Hifz section not found.']);
        }

        $this->db->transStart();

        $this->db->table('hifz_teacher_sections')
            ->where('hifz_sec_id', $hifzSecId)
            ->where('session_id', $sessionId)
            ->delete();

        if ($teacherId > 0) {
            $teacher = $this->db->table('users')
                ->where('id', $teacherId)
                ->where('campus_id', $campusId)
                ->where('status', 1)
                ->get()
                ->getRow();

            if (! $teacher) {
                $this->db->transRollback();

                return json_response(['success' => false, 'msg' => 'Teacher not found.']);
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

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return json_response(['success' => false, 'msg' => 'Failed to save teacher assignment.']);
        }

        $teacherName = '—';
        if ($teacherId > 0) {
            $t = $this->db->table('users')->select('first_name, last_name')->where('id', $teacherId)->get()->getRow();
            $teacherName = $t ? trim($t->first_name . ' ' . ($t->last_name ?? '')) : '—';
        }

        return json_response([
            'success'      => true,
            'msg'          => $teacherId > 0 ? 'Teacher assigned.' : 'Teacher removed from section.',
            'teacher_name' => $teacherName,
        ]);
    }
}
