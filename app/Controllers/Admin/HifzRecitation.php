<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\HifzDailyService;

class HifzRecitation extends BaseController
{
    protected $db;
    protected $session;
    protected HifzDailyService $daily;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        $this->daily   = new HifzDailyService();
        helper(['form', 'url', 'hifz']);
        check_permission('admin-hifz-recitation');
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        hifz_ensure_database_schema();
        $this->requireHifzCampus();
    }

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

        redirect()->to(base_url('admin/#/profile_campus'))->send();
        exit;
    }

    public function index()
    {
        $sections = hifzRecitationSectionsForUser();

        return view('admin/hifz/recitation/index', [
            'title'    => 'Daily Recitation',
            'sections' => $sections,
            'qualities' => hifzRecitationQualityOptions(),
            'paras'    => hifzJuzCatalog(),
            'today'    => date('Y-m-d'),
        ]);
    }

    public function load()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $hifzSecId = (int) $this->request->getPost('hifz_sec_id');
        $date      = trim((string) $this->request->getPost('recitation_date'));

        if ($hifzSecId <= 0) {
            return json_response(['success' => false, 'msg' => 'Select a Hifz section.']);
        }

        if (! $this->validDate($date)) {
            return json_response(['success' => false, 'msg' => 'Invalid date.']);
        }

        if (! $this->canAccessSection($hifzSecId)) {
            return json_response(['success' => false, 'msg' => 'You are not assigned to this Hifz section.']);
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

        $students = $this->db->table('hifz_students hs')
            ->select('hs.*, s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo')
            ->join('students s', 's.student_id = hs.student_id')
            ->where('hs.hifz_sec_id', $hifzSecId)
            ->where('hs.campus_id', $campusId)
            ->where('hs.session_id', $sessionId)
            ->where('hs.status', 1)
            ->where('s.status', 1)
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->get()
            ->getResult();

        $sectionPeers = [];
        foreach ($students as $student) {
            $sectionPeers[] = [
                'student_id'   => (int) $student->student_id,
                'student_name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                'reg_no'       => $student->reg_no ?? '',
            ];
        }

        $payload = [];
        foreach ($students as $student) {
            try {
                $payload[] = $this->daily->buildStudentDay($student, $student, $date, $sessionId, $sectionPeers);
            } catch (\Throwable $e) {
                log_message('error', '[HifzRecitation::load] student ' . (int) ($student->student_id ?? 0) . ': ' . $e->getMessage());

                return json_response([
                    'success' => false,
                    'msg'     => 'Could not build student data. ' . $e->getMessage(),
                ], '', 500);
            }
        }

        $savedCount = 0;
        foreach ($payload as $p) {
            if (! empty($p['record_sabaq']) || ! empty($p['record_sabqi'])
                || ! empty($p['record_manzil']) || ! empty($p['record_mutalia'])) {
                $savedCount++;
            }
        }

        $teacherRow = $this->db->table('hifz_teacher_sections')
            ->select('teacher_id')
            ->where('hifz_sec_id', $hifzSecId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();

        return json_response([
            'success'      => true,
            'section_name' => $section->section_name,
            'teacher_id'   => (int) ($teacherRow->teacher_id ?? 0),
            'students'     => $payload,
            'section_peers' => $sectionPeers,
            'saved_count'  => $savedCount,
            'total_count'  => count($payload),
        ]);
    }

    public function save()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $userId    = (int) $this->session->get('member_userid');
        $hifzSecId = (int) $this->request->getPost('hifz_sec_id');
        $studentId = (int) $this->request->getPost('student_id');
        $date      = trim((string) $this->request->getPost('recitation_date'));

        if ($hifzSecId <= 0 || $studentId <= 0) {
            return json_response(['success' => false, 'msg' => 'Missing section or student.']);
        }

        if (! $this->validDate($date)) {
            return json_response(['success' => false, 'msg' => 'Invalid date.']);
        }

        if (! $this->canAccessSection($hifzSecId)) {
            return json_response(['success' => false, 'msg' => 'You are not assigned to this Hifz section.']);
        }

        $enroll = $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('hifz_sec_id', $hifzSecId)
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();

        if (! $enroll) {
            return json_response(['success' => false, 'msg' => 'Student is not in this Hifz section.']);
        }

        $teacherRow = $this->db->table('hifz_teacher_sections')
            ->select('teacher_id')
            ->where('hifz_sec_id', $hifzSecId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();

        $teacherId = (int) ($teacherRow->teacher_id ?? $userId);

        $input = [
            'record_sabaq'   => $this->request->getPost('record_sabaq'),
            'record_sabqi'   => $this->request->getPost('record_sabqi'),
            'record_manzil'  => $this->request->getPost('record_manzil'),
            'record_mutalia' => $this->request->getPost('record_mutalia'),
            'sabaq_quality'  => $this->request->getPost('sabaq_quality'),
            'sabaq_remarks'  => $this->request->getPost('sabaq_remarks'),
            'sabaq_hard_mistakes' => $this->request->getPost('sabaq_hard_mistakes'),
            'sabaq_soft_mistakes' => $this->request->getPost('sabaq_soft_mistakes'),
            'sabaq_listener_type' => $this->request->getPost('sabaq_listener_type'),
            'sabaq_listener_student_id' => $this->request->getPost('sabaq_listener_student_id'),
            'sabqi_paras'    => $this->request->getPost('sabqi_paras'),
            'remove_sabqi_paras' => $this->request->getPost('remove_sabqi_paras'),
            'sabqi_quality'  => $this->request->getPost('sabqi_quality'),
            'sabqi_remarks'  => $this->request->getPost('sabqi_remarks'),
            'sabqi_hard_mistakes' => $this->request->getPost('sabqi_hard_mistakes'),
            'sabqi_soft_mistakes' => $this->request->getPost('sabqi_soft_mistakes'),
            'sabqi_listener_type' => $this->request->getPost('sabqi_listener_type'),
            'sabqi_listener_student_id' => $this->request->getPost('sabqi_listener_student_id'),
            'manzil_juz_list' => $this->request->getPost('manzil_juz_list'),
            'manzil_quality' => $this->request->getPost('manzil_quality'),
            'manzil_remarks' => $this->request->getPost('manzil_remarks'),
            'manzil_listener_type' => $this->request->getPost('manzil_listener_type'),
            'manzil_listener_student_id' => $this->request->getPost('manzil_listener_student_id'),
            'manzil_hard_mistakes' => $this->request->getPost('manzil_hard_mistakes'),
            'manzil_soft_mistakes' => $this->request->getPost('manzil_soft_mistakes'),
            'mutalia_lines'  => $this->request->getPost('mutalia_lines'),
            'mutalia_remarks' => $this->request->getPost('mutalia_remarks'),
        ];

        $result = $this->daily->saveStudentDay(
            $studentId,
            $campusId,
            $sessionId,
            $hifzSecId,
            $teacherId,
            $userId,
            $date,
            $input,
            $enroll
        );

        if (empty($result['success'])) {
            return json_response($result);
        }

        $studentRow = $this->db->table('hifz_students hs')
            ->select('hs.*, s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo')
            ->join('students s', 's.student_id = hs.student_id')
            ->where('hs.student_id', $studentId)
            ->where('hs.hifz_sec_id', $hifzSecId)
            ->where('hs.campus_id', $campusId)
            ->where('hs.session_id', $sessionId)
            ->get()
            ->getRow();

        if ($studentRow) {
            $enroll = $this->db->table('hifz_students')
                ->where('student_id', $studentId)
                ->where('hifz_sec_id', $hifzSecId)
                ->where('campus_id', $campusId)
                ->where('session_id', $sessionId)
                ->where('status', 1)
                ->get()
                ->getRow() ?? $enroll;

            $sectionPeers = $this->sectionPeersForHifzSec($hifzSecId, $campusId, $sessionId);
            $result['student'] = $this->daily->buildStudentDay($studentRow, $enroll, $date, $sessionId, $sectionPeers);
        }

        return json_response($result);
    }

    public function sabqiRemovePara()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $hifzSecId = (int) $this->request->getPost('hifz_sec_id');
        $studentId = (int) $this->request->getPost('student_id');
        $paraNo    = (int) $this->request->getPost('para_no');
        $date      = trim((string) $this->request->getPost('recitation_date'));

        if ($hifzSecId <= 0 || $studentId <= 0 || $paraNo <= 0) {
            return json_response(['success' => false, 'msg' => 'Invalid request.']);
        }

        if (! $this->validDate($date)) {
            $date = date('Y-m-d');
        }

        $enroll = $this->db->table('hifz_students')
            ->where('student_id', $studentId)
            ->where('hifz_sec_id', $hifzSecId)
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->where('status', 1)
            ->get()
            ->getRow();

        if (! $enroll) {
            return json_response(['success' => false, 'msg' => 'Student not found.']);
        }

        $result = $this->daily->removeSabqiPara($enroll, $paraNo);

        if (empty($result['success'])) {
            return json_response($result);
        }

        $studentRow = $this->db->table('hifz_students hs')
            ->select('hs.*, s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo')
            ->join('students s', 's.student_id = hs.student_id')
            ->where('hs.student_id', $studentId)
            ->where('hs.hifz_sec_id', $hifzSecId)
            ->where('hs.campus_id', $campusId)
            ->where('hs.session_id', $sessionId)
            ->get()
            ->getRow();

        if ($studentRow) {
            $enroll = $this->db->table('hifz_students')
                ->where('student_id', $studentId)
                ->where('hifz_sec_id', $hifzSecId)
                ->where('campus_id', $campusId)
                ->where('session_id', $sessionId)
                ->where('status', 1)
                ->get()
                ->getRow() ?? $enroll;

            $sectionPeers = $this->sectionPeersForHifzSec($hifzSecId, $campusId, $sessionId);
            $result['student'] = $this->daily->buildStudentDay($studentRow, $enroll, $date, $sessionId, $sectionPeers);
        }

        return json_response($result);
    }

    /**
     * @return list<array{student_id:int,student_name:string,reg_no:string}>
     */
    protected function sectionPeersForHifzSec(int $hifzSecId, int $campusId, int $sessionId): array
    {
        $rows = $this->db->table('hifz_students hs')
            ->select('s.student_id, s.first_name, s.last_name, s.reg_no')
            ->join('students s', 's.student_id = hs.student_id')
            ->where('hs.hifz_sec_id', $hifzSecId)
            ->where('hs.campus_id', $campusId)
            ->where('hs.session_id', $sessionId)
            ->where('hs.status', 1)
            ->where('s.status', 1)
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->get()
            ->getResult();

        $peers = [];
        foreach ($rows as $row) {
            $peers[] = [
                'student_id'   => (int) $row->student_id,
                'student_name' => trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')),
                'reg_no'       => $row->reg_no ?? '',
            ];
        }

        return $peers;
    }

    protected function canAccessSection(int $hifzSecId): bool
    {
        foreach (hifzRecitationSectionsForUser() as $sec) {
            if ((int) $sec['hifz_sec_id'] === $hifzSecId) {
                return true;
            }
        }

        return false;
    }

    protected function validDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $date);

        return $dt && $dt->format('Y-m-d') === $date;
    }
}
