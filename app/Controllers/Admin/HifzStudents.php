<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\HifzEnrollmentService;

class HifzStudents extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'hifz']);
        check_permission('admin-hifz-students');
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
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');

        return view('admin/hifz/students/index', [
            'title'        => 'Hifz Students',
            'hifzSections' => activeHifzSections($campusId, $sessionId),
            'sequences'    => hifzMemorizationSequenceOptions(),
            'manzilOptions'=> hifzManzilParasPerDayOptions(),
            'campus'       => getCampusInfo(),
        ]);
    }

    public function data()
    {
        $campusId   = (int) $this->session->get('member_campusid');
        $sessionId  = (int) $this->session->get('member_sessionid');
        $hifzSecId  = (int) $this->request->getPost('hifz_sec_id');
        $keyword    = trim((string) ($this->request->getPost('search')['value'] ?? ''));

        $qb = $this->db->table('hifz_students hs')
            ->select('hs.id, hs.student_id, hs.hifz_sec_id, hs.memorization_sequence')
            ->select('hs.sabaq_lines_per_day, hs.mutalia_lines_per_day, hs.manzil_paras_per_day, hs.current_global_line, hs.current_juz')
            ->select('hs.enrollment_date, s.first_name, s.last_name, s.reg_no')
            ->select('hsec.section_name AS hifz_section_name')
            ->select('CONCAT(c.class_name, " (", sec.section_name, ")") AS academic_section', false)
            ->select('CONCAT(u.first_name, " ", COALESCE(u.last_name, "")) AS teacher_name', false)
            ->join('students s', 's.student_id = hs.student_id')
            ->join('hifz_sections hsec', 'hsec.hifz_sec_id = hs.hifz_sec_id', 'left')
            ->join('student_class sc', 'sc.student_id = hs.student_id AND sc.session_id = hs.session_id AND sc.status = 1', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->join('hifz_teacher_sections hts', 'hts.hifz_sec_id = hs.hifz_sec_id AND hts.session_id = hs.session_id AND hts.status = 1', 'left')
            ->join('users u', 'u.id = hts.teacher_id', 'left')
            ->where('hs.campus_id', $campusId)
            ->where('hs.session_id', $sessionId)
            ->where('hs.status', 1)
            ->where('s.status', 1);

        if ($hifzSecId > 0) {
            $qb->where('hs.hifz_sec_id', $hifzSecId);
        }

        if ($keyword !== '') {
            $qb->groupStart()
                ->like('s.first_name', $keyword)
                ->orLike('s.last_name', $keyword)
                ->orLike('s.reg_no', $keyword)
                ->orLike('hsec.section_name', $keyword)
            ->groupEnd();
        }

        $total = $qb->countAllResults(false);

        $rows = $qb->orderBy('hsec.sort_order', 'ASC')
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->limit((int) $this->request->getPost('length'), (int) $this->request->getPost('start'))
            ->get()
            ->getResultArray();

        $seqLabels = hifzMemorizationSequenceOptions();
        $data      = [];
        $n         = (int) $this->request->getPost('start') + 1;

        foreach ($rows as $row) {
            $seq = $row['memorization_sequence'] ?? '';
            $data[] = [
                'sno'              => $n++,
                'student_name'     => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                'reg_no'           => $row['reg_no'] ?? '',
                'academic_section' => $row['academic_section'] ?? '—',
                'hifz_section_name'=> $row['hifz_section_name'] ?? '—',
                'sequence_label'   => $seqLabels[$seq] ?? $seq,
                'sabaq_lines'      => (int) ($row['sabaq_lines_per_day'] ?? 0),
                'mutalia_lines'    => (int) ($row['mutalia_lines_per_day'] ?? 0),
                'manzil_paras'     => (int) ($row['manzil_paras_per_day'] ?? 1),
                'cursor_line'      => (int) ($row['current_global_line'] ?? 0),
                'current_juz'      => (int) ($row['current_juz'] ?? 0),
                'teacher_name'     => trim($row['teacher_name'] ?? '') ?: '—',
                'enrollment_date'  => $row['enrollment_date'] ?? '',
                'id'               => (int) $row['id'],
                'student_id'       => (int) $row['student_id'],
                'hifz_sec_id'      => (int) $row['hifz_sec_id'],
                'memorization_sequence' => $seq,
                'actions'          => '',
            ];
        }

        return $this->response->setJSON([
            'draw'            => (int) ($this->request->getPost('draw') ?? 0),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
    }

    public function save()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $userId    = (int) $this->session->get('member_userid');
        $studentId = (int) $this->request->getPost('student_id');

        $row = $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();

        if (! $row) {
            return json_response(['success' => false, 'msg' => 'Active Hifz enrollment not found.']);
        }

        $svc = new HifzEnrollmentService();

        return json_response($svc->sync($studentId, $campusId, $sessionId, $userId, [
            'is_hifz'               => 1,
            'hifz_sec_id'           => $this->request->getPost('hifz_sec_id'),
            'memorization_sequence' => $this->request->getPost('memorization_sequence'),
            'sabaq_lines_per_day'   => $this->request->getPost('sabaq_lines_per_day'),
            'mutalia_lines_per_day' => $this->request->getPost('mutalia_lines_per_day'),
            'manzil_paras_per_day'  => $this->request->getPost('manzil_paras_per_day'),
        ]));
    }

    public function withdraw()
    {
        $sessionId = (int) $this->session->get('member_sessionid');
        $userId    = (int) $this->session->get('member_userid');
        $studentId = (int) $this->request->getPost('student_id');

        if ($studentId <= 0) {
            return json_response(['success' => false, 'msg' => 'Invalid student.']);
        }

        (new HifzEnrollmentService())->withdraw($studentId, $sessionId, $userId);

        return json_response(['success' => true, 'msg' => 'Student withdrawn from Hifz program.']);
    }

    public function moveSection()
    {
        $sessionId = (int) $this->session->get('member_sessionid');
        $userId    = (int) $this->session->get('member_userid');
        $studentId = (int) $this->request->getPost('student_id');
        $hifzSecId = (int) $this->request->getPost('hifz_sec_id');

        $updated = $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->update([
                'hifz_sec_id'  => $hifzSecId,
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id'      => $userId,
            ]);

        if (! $updated) {
            return json_response(['success' => false, 'msg' => 'Could not move student.']);
        }

        return json_response(['success' => true, 'msg' => 'Hifz section updated.']);
    }
}
