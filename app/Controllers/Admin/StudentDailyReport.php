<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ReportCsvExport;
use CodeIgniter\HTTP\ResponseInterface;

class StudentDailyReport extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-student-attendance');
    }

    public function index()
    {
        return view('admin/student_daily_report', [
            'defaultDate' => date('Y-m-d'),
        ]);
    }

    public function data(): ResponseInterface
    {
        $date = trim((string) $this->request->getPost('date'));
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');

        $sections = $this->getActiveClassSections($campusId);
        if (empty($sections)) {
            return $this->response->setBody(
                '<div class="alert alert-warning mb-0">No active class sections found for this campus.</div>'
            );
        }

        $activeSectionIds = array_column($sections, 'cls_sec_id');

        $students = $this->db->table('student_class sc')
            ->select('sc.cls_sec_id, s.student_id, s.first_name, s.last_name')
            ->join('students s', 's.student_id = sc.student_id', 'inner')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
            ->where('sc.session_id', $sessionId)
            ->where('sc.status', 1)
            ->where('s.status', 1)
            ->where('s.campus_id', $campusId)
            ->where('cs.status', 1)
            ->where('cs.campus_id', $campusId)
            ->whereIn('sc.cls_sec_id', $activeSectionIds)
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->get()
            ->getResult();

        $studentsBySection = [];
        foreach ($students as $student) {
            $sectionId = (int) $student->cls_sec_id;
            $fullName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            if ($fullName === '') {
                continue;
            }
            $studentsBySection[$sectionId][] = [
                'student_id' => (int) $student->student_id,
                'name'       => $fullName,
            ];
        }

        $attendanceRows = $this->db->table('attendance')
            ->select('student_id, status')
            ->where('date', $date)
            ->get()
            ->getResult();

        $attendanceMap = [];
        foreach ($attendanceRows as $row) {
            $attendanceMap[(int) $row->student_id] = (string) ($row->status ?? '');
        }

        // Sections with at least one attendance record on this date (others treated as off day)
        $sectionsWithAttendance = [];
        $attendanceBySectionRows = $this->db->table('attendance a')
            ->select('sc.cls_sec_id, COUNT(DISTINCT a.student_id) AS record_count', false)
            ->join('student_class sc', 'sc.student_id = a.student_id', 'inner')
            ->join('students s', 's.student_id = a.student_id', 'inner')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
            ->where('a.date', $date)
            ->where('sc.session_id', $sessionId)
            ->where('sc.status', 1)
            ->where('s.status', 1)
            ->where('s.campus_id', $campusId)
            ->where('cs.status', 1)
            ->where('cs.campus_id', $campusId)
            ->whereIn('sc.cls_sec_id', $activeSectionIds)
            ->groupBy('sc.cls_sec_id')
            ->get()
            ->getResult();

        foreach ($attendanceBySectionRows as $row) {
            if ((int) ($row->record_count ?? 0) > 0) {
                $sectionsWithAttendance[(int) $row->cls_sec_id] = true;
            }
        }

        $rowsHtml = '';
        $count = 1;
        $totalNonPresent = 0;

        foreach ($sections as $section) {
            $clsSecId = (int) ($section['cls_sec_id'] ?? 0);

            // No attendance marked for this section on this date = off day, skip it
            if (empty($sectionsWithAttendance[$clsSecId])) {
                continue;
            }

            $sectionLabel = esc($section['sectionclassname'] ?? $section['sectionclassname_full'] ?? 'Section');
            $sectionStudents = $studentsBySection[$clsSecId] ?? [];
            $nonPresentNames = [];

            foreach ($sectionStudents as $student) {
                $status = $attendanceMap[$student['student_id']] ?? null;
                if (!$this->isPresentStatus($status)) {
                    $nonPresentNames[] = esc($student['name']);
                }
            }

            $totalNonPresent += count($nonPresentNames);

            if (empty($nonPresentNames)) {
                $namesCell = empty($sectionStudents)
                    ? '<span class="text-muted">No students enrolled</span>'
                    : '<span class="text-success">All Present</span>';
            } else {
                $namesCell = implode(', ', $nonPresentNames);
            }

            $rowsHtml .= '<tr>';
            $rowsHtml .= '<td class="text-center">' . $count . '</td>';
            $rowsHtml .= '<td class="section-name">' . $sectionLabel . '</td>';
            $rowsHtml .= '<td class="absent-names">' . $namesCell . '</td>';
            $rowsHtml .= '</tr>';
            $count++;
        }

        $formattedDate = date('d M Y (l)', strtotime($date));

        if ($rowsHtml === '') {
            return $this->response->setBody(
                '<div class="alert alert-info mb-0">No attendance records found for <strong>' . esc($formattedDate) . '</strong>. All classes are treated as off day.</div>'
            );
        }

        $output = '
            <div id="reportPrintArea">
                <div class="report-preview-header d-none d-print-block">
                    <div class="report-preview-title">Student Daily Report</div>
                    <div class="report-preview-subtitle">Date: ' . esc($formattedDate) . '</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm daily-report-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th style="width:220px;">Class Section</th>
                                <th>Non-Present Students</th>
                            </tr>
                        </thead>
                        <tbody>' . $rowsHtml . '</tbody>
                        <tfoot>
                            <tr class="summary-row">
                                <td colspan="2" class="text-end fw-bold">Total Non-Present</td>
                                <td class="fw-bold">' . number_format($totalNonPresent) . '</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>';

        return $this->response->setBody($output);
    }

    public function exportCsv(): ResponseInterface
    {
        $date = trim((string) $this->request->getPost('date'));
        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $sections  = $this->getActiveClassSections($campusId);

        if ($sections === []) {
            return $this->response->setStatusCode(400)->setBody('No sections found.');
        }

        $activeSectionIds = array_column($sections, 'cls_sec_id');
        $students = $this->db->table('student_class sc')
            ->select('sc.cls_sec_id, s.student_id, s.first_name, s.last_name')
            ->join('students s', 's.student_id = sc.student_id', 'inner')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
            ->where('sc.session_id', $sessionId)
            ->where('sc.status', 1)
            ->where('s.status', 1)
            ->where('s.campus_id', $campusId)
            ->whereIn('sc.cls_sec_id', $activeSectionIds)
            ->get()
            ->getResult();

        $studentsBySection = [];
        foreach ($students as $student) {
            $sectionId = (int) $student->cls_sec_id;
            $fullName  = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            if ($fullName !== '') {
                $studentsBySection[$sectionId][] = ['student_id' => (int) $student->student_id, 'name' => $fullName];
            }
        }

        $attendanceMap = [];
        foreach ($this->db->table('attendance')->select('student_id, status')->where('date', $date)->get()->getResult() as $row) {
            $attendanceMap[(int) $row->student_id] = (string) ($row->status ?? '');
        }

        $csvRows = [];
        foreach ($sections as $section) {
            $clsSecId = (int) ($section['cls_sec_id'] ?? 0);
            $label    = $section['sectionclassname'] ?? $section['sectionclassname_full'] ?? 'Section';
            $names    = [];
            foreach ($studentsBySection[$clsSecId] ?? [] as $student) {
                if (! $this->isPresentStatus($attendanceMap[$student['student_id']] ?? null)) {
                    $names[] = $student['name'];
                }
            }
            $csvRows[] = [$label, $names === [] ? 'All Present' : implode('; ', $names)];
        }

        return ReportCsvExport::downloadResponse(
            $this->response,
            'student-daily-report-' . $date . '.csv',
            ['Class Section', 'Non-Present Students'],
            $csvRows
        );
    }

    private function isPresentStatus(?string $status): bool
    {
        $normalized = strtoupper(trim((string) $status));

        if ($normalized === '') {
            return false;
        }

        return in_array($normalized, ['P', 'PRESENT', 'LC', 'LATE', 'LATE COMING'], true);
    }

    /**
     * Active class sections only (class_section.status = 1).
     */
    private function getActiveClassSections(int $campusId): array
    {
        $rows = $this->db->table('class_section cs')
            ->select(
                'cs.cls_sec_id,
                 cs.section_id,
                 cs.class_id,
                 c.class_name,
                 c.class_short_name,
                 s.section_name,
                 s.short_name AS section_short_name,
                 CONCAT(COALESCE(c.class_short_name, c.class_name), " - ", COALESCE(s.short_name, s.section_name)) AS sectionclassname,
                 CONCAT(COALESCE(c.class_short_name, c.class_name), " - ", s.section_name) AS sectionclassname_full',
                false
            )
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections s', 's.section_id = cs.section_id', 'inner')
            ->where('cs.status', 1)
            ->where('cs.campus_id', $campusId)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.section_id', 'ASC')
            ->get()
            ->getResultArray();

        $sections = [];
        foreach ($rows as $row) {
            $sections[] = [
                'cls_sec_id'            => (int) ($row['cls_sec_id'] ?? 0),
                'class_id'              => (int) ($row['class_id'] ?? 0),
                'class_name'            => $row['class_name'] ?? '',
                'class_short_name'      => $row['class_short_name'] ?? ($row['class_name'] ?? ''),
                'section_id'            => (int) ($row['section_id'] ?? 0),
                'section_name'          => $row['section_name'] ?? '',
                'section_short_name'    => $row['section_short_name'] ?? ($row['section_name'] ?? ''),
                'sectionclassname'      => $row['sectionclassname'] ?? '',
                'sectionclassname_full' => $row['sectionclassname_full'] ?? '',
            ];
        }

        return $sections;
    }
}
