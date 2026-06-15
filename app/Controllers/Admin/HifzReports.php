<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\HifzReportService;
use App\Libraries\ReportCsvExport;

class HifzReports extends BaseController
{
    protected $db;
    protected $session;
    protected HifzReportService $reports;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        $this->reports = new HifzReportService();
        helper(['form', 'url', 'hifz']);
        check_permission('admin-hifz-reports');
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

        $students = $this->db->table('hifz_students hs')
            ->select('hs.student_id, s.first_name, s.last_name, s.reg_no, hsec.section_name')
            ->join('students s', 's.student_id = hs.student_id')
            ->join('hifz_sections hsec', 'hsec.hifz_sec_id = hs.hifz_sec_id', 'left')
            ->where('hs.campus_id', $campusId)
            ->where('hs.session_id', $sessionId)
            ->where('hs.status', 1)
            ->where('s.status', 1)
            ->orderBy('hsec.sort_order', 'ASC')
            ->orderBy('s.first_name', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/hifz/reports/index', [
            'title'        => 'Hifz Progress Reports',
            'sections'     => activeHifzSections($campusId, $sessionId),
            'students'     => $students,
            'defaultFrom'  => date('Y-m-01'),
            'defaultTo'    => date('Y-m-d'),
        ]);
    }

    public function sectionData()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $hifzSecId = (int) $this->request->getPost('hifz_sec_id');
        [$from, $to] = $this->parseDateRange();

        if ($hifzSecId <= 0) {
            return json_response(['success' => false, 'msg' => 'Select a Hifz section.']);
        }

        $rows = $this->reports->getSectionReport($hifzSecId, $campusId, $sessionId, $from, $to);

        return json_response(['success' => true, 'data' => $rows]);
    }

    public function exportSectionCsv()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $hifzSecId = (int) $this->request->getPost('hifz_sec_id');
        [$from, $to] = $this->parseDateRange();

        if ($hifzSecId <= 0) {
            return $this->response->setStatusCode(400)->setBody('Select a Hifz section.');
        }

        $rows = $this->reports->getSectionReport($hifzSecId, $campusId, $sessionId, $from, $to);
        $csvRows = [];
        foreach ($rows as $r) {
            $csvRows[] = [
                $r['reg_no'] ?? '',
                $r['student_name'] ?? '',
                (string) ($r['current_juz'] ?? ''),
                (string) ($r['days_logged'] ?? ''),
                $r['last_date'] ?? '',
                $r['last_quality'] ?? '',
            ];
        }

        return ReportCsvExport::downloadResponse(
            $this->response,
            'hifz-section-report-' . $from . '-to-' . $to . '.csv',
            ['Reg No', 'Student', 'Current Para', 'Days Logged', 'Last Date', 'Last Quality'],
            $csvRows
        );
    }

    public function studentData()
    {
        $sessionId = (int) $this->session->get('member_sessionid');
        $studentId = (int) $this->request->getPost('student_id');
        [$from, $to] = $this->parseDateRange();

        if ($studentId <= 0) {
            return json_response(['success' => false, 'msg' => 'Select a student.']);
        }

        $summary = $this->reports->getStudentSummary($studentId, $sessionId, $from, $to);
        if (empty($summary['found'])) {
            return json_response(['success' => false, 'msg' => 'Student is not actively enrolled in Hifz.']);
        }

        return json_response(['success' => true, 'summary' => $summary]);
    }

    /**
     * @return array{0:string,1:string}
     */
    protected function parseDateRange(): array
    {
        $from = trim((string) $this->request->getPost('date_from'));
        $to   = trim((string) $this->request->getPost('date_to'));

        if (! $this->validDate($from)) {
            $from = date('Y-m-01');
        }
        if (! $this->validDate($to)) {
            $to = date('Y-m-d');
        }
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
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
