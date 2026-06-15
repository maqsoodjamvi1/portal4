<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class StudentWeeklyNonPresentReport extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'server', 'school']);
        check_permission('admin-student-attendance');
    }

    public function index()
    {
        $campusId = (int) $this->session->get('member_campusid');

        return view('admin/student_weekly_non_present_report', [
            'terms'    => termSessions(),
            'sections' => $this->getActiveClassSections($campusId),
        ]);
    }

    public function data(): ResponseInterface
    {
        $termSessionId = (int) $this->request->getPost('term_session_id');
        $clsSecIdRaw   = $this->request->getPost('cls_sec_id');
        $allClasses    = ($clsSecIdRaw === 'all');
        $clsSecId      = $allClasses ? 0 : (int) $clsSecIdRaw;
        $campusId      = (int) $this->session->get('member_campusid');
        $sessionId     = (int) $this->session->get('member_sessionid');

        if ($termSessionId <= 0) {
            return $this->response->setBody(
                '<div class="alert alert-warning mb-0">Please select a Term Session.</div>'
            );
        }

        if (!$allClasses && $clsSecId <= 0) {
            return $this->response->setBody(
                '<div class="alert alert-warning mb-0">Please select a Class Section or All Classes.</div>'
            );
        }

        $includeFilters = $this->parseIncludeFilters();
        if ($includeFilters === []) {
            return $this->response->setBody(
                '<div class="alert alert-warning mb-0">Please select at least one type to include in the count (Absentees, Leave, Late Coming, or Early Left).</div>'
            );
        }

        $termSession = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, ts.session_id, t.name AS term_name')
            ->join('terms t', 't.term_id = ts.term_id', 'inner')
            ->where('ts.term_session_id', $termSessionId)
            ->where('ts.session_id', $sessionId)
            ->get()
            ->getRow();

        if (!$termSession) {
            return $this->response->setBody(
                '<div class="alert alert-danger mb-0">Invalid term session selected.</div>'
            );
        }

        $sectionIds   = [];
        $sectionLabel = 'All Classes';

        if ($allClasses) {
            foreach ($this->getActiveClassSections($campusId) as $section) {
                $id = (int) ($section['cls_sec_id'] ?? 0);
                if ($id > 0) {
                    $sectionIds[] = $id;
                }
            }

            if ($sectionIds === []) {
                return $this->response->setBody(
                    '<div class="alert alert-warning mb-0">No active class sections found for this campus.</div>'
                );
            }
        } else {
            $section = $this->db->table('class_section cs')
                ->select('cs.cls_sec_id, c.class_name, s.section_name')
                ->join('classes c', 'c.class_id = cs.class_id', 'inner')
                ->join('sections s', 's.section_id = cs.section_id', 'inner')
                ->where('cs.cls_sec_id', $clsSecId)
                ->where('cs.campus_id', $campusId)
                ->where('cs.status', 1)
                ->get()
                ->getRow();

            if (!$section) {
                return $this->response->setBody(
                    '<div class="alert alert-danger mb-0">Invalid or inactive class section selected.</div>'
                );
            }

            $sectionIds   = [$clsSecId];
            $sectionLabel = trim($section->class_name . ' - ' . $section->section_name);
        }

        $weeks = $this->db->table('term_weeks')
            ->select('term_weeks_id, week_no, week_name, start_date, end_date')
            ->where('term_session_id', $termSessionId)
            ->orderBy('week_no', 'ASC')
            ->get()
            ->getResult();

        if ($weeks === []) {
            return $this->response->setBody(
                '<div class="alert alert-info mb-0">No weeks found for this term session. '
                . 'Please set up weeks in <a href="' . esc(base_url('admin/term_weeks')) . '">Term Weeks</a> '
                . 'or the Academic Calendar.</div>'
            );
        }

        $students = $this->db->table('student_class sc')
            ->select(
                'sc.cls_sec_id, s.student_id, s.first_name, s.last_name, s.reg_no, c.class_id, cs.section_id',
                false
            )
            ->join('students s', 's.student_id = sc.student_id', 'inner')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->whereIn('sc.cls_sec_id', $sectionIds)
            ->where('sc.session_id', $sessionId)
            ->where('sc.status', 1)
            ->where('s.status', 1)
            ->where('s.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('cs.section_id', 'ASC')
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC')
            ->get()
            ->getResult();

        if ($students === []) {
            return $this->response->setBody(
                '<div class="alert alert-warning mb-0">No active students found for the selected class section(s).</div>'
            );
        }

        $studentIds = array_map(static fn($s) => (int) $s->student_id, $students);
        $rangeStart = (string) $weeks[0]->start_date;
        $rangeEnd   = (string) $weeks[0]->end_date;
        foreach ($weeks as $week) {
            if ((string) $week->start_date < $rangeStart) {
                $rangeStart = (string) $week->start_date;
            }
            if ((string) $week->end_date > $rangeEnd) {
                $rangeEnd = (string) $week->end_date;
            }
        }

        $attendanceByStudentDate = [];
        $attendanceRows = $this->db->table('attendance')
            ->select('student_id, date, status, lc_duration, el_duration')
            ->whereIn('student_id', $studentIds)
            ->where('date >=', $rangeStart)
            ->where('date <=', $rangeEnd)
            ->get()
            ->getResult();

        foreach ($attendanceRows as $row) {
            $sid = (int) $row->student_id;
            $dateKey = date('Y-m-d', strtotime((string) $row->date));
            $attendanceByStudentDate[$sid][$dateKey] = $row;
        }

        $sectionAttendanceDatesBySection = $this->buildSectionAttendanceDatesBySection(
            $sectionIds,
            $studentIds,
            $rangeStart,
            $rangeEnd,
            $sessionId
        );

        $timingsBySection = [];
        foreach ($sectionIds as $sectionId) {
            $rows = getSchoolTimingsForSections([$sectionId], $campusId);
            $map = [];
            foreach ($rows as $timing) {
                $map[$timing['dayname']] = (object) $timing;
            }
            $timingsBySection[$sectionId] = $map;
        }

        $termLabel    = esc($termSession->term_name ?? 'Term');
        $filtersLabel = esc($this->formatIncludeFiltersLabel($includeFilters));

        $sectionLabelMap = [];
        foreach ($this->getActiveClassSections($campusId) as $sec) {
            $sectionLabelMap[(int) ($sec['cls_sec_id'] ?? 0)] = $sec['sectionclassname'] ?? 'Section';
        }

        $sectionsToRender = $this->buildSectionsToRender(
            $allClasses,
            $sectionIds,
            $sectionLabel,
            $sectionLabelMap,
            $students
        );

        $reportBlocksHtml = '';
        $sectionCount     = count($sectionsToRender);

        foreach ($sectionsToRender as $index => $sectionData) {
            $sectionId    = (int) $sectionData['id'];
            $isLastBlock  = ($index === $sectionCount - 1);
            $pageBreakCls = ($allClasses && !$isLastBlock) ? ' page-break-after' : '';

            $reportBlocksHtml .= $this->buildSectionBlockHtml(
                $sectionData['label'],
                $sectionData['students'],
                $weeks,
                $attendanceByStudentDate,
                $timingsBySection[$sectionId] ?? [],
                $sectionAttendanceDatesBySection[$sectionId] ?? [],
                $includeFilters,
                $pageBreakCls,
                $allClasses
            );
        }

        $previewHeader = '
            <div class="report-preview-header">
                <div class="report-preview-title">Student Weekly Non-Present Report</div>
                <div class="report-preview-subtitle">Term: ' . $termLabel
                . ' | Section: ' . esc($sectionLabel)
                . ' | Included: ' . $filtersLabel . '</div>
            </div>';

        $maxStudentColPx = 0;
        foreach ($sectionsToRender as $sectionData) {
            $longestInSection = $this->findLongestDisplayName($sectionData['students']);
            $maxStudentColPx = max($maxStudentColPx, $this->estimateStudentColumnPx($longestInSection));
        }

        $output = '
            <div id="reportPrintArea">
                ' . $previewHeader . '
                <div class="report-sections-wrap">' . $reportBlocksHtml . '</div>
            </div>
            <input type="hidden" id="reportMetaTerm" value="' . $termLabel . '">
            <input type="hidden" id="reportMetaSection" value="' . esc($sectionLabel) . '">
            <input type="hidden" id="reportMetaFilters" value="' . $filtersLabel . '">
            <input type="hidden" id="reportMetaStudentColPx" value="' . $maxStudentColPx . '">
            <input type="hidden" id="reportMetaAllClasses" value="' . ($allClasses ? '1' : '0') . '">
            <input type="hidden" id="reportMetaWeekCount" value="' . count($weeks) . '">';

        return $this->response->setBody($output);
    }

    private function buildDisplayName(object $student): string
    {
        $fullName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
        $regNo    = trim((string) ($student->reg_no ?? ''));

        if ($regNo === '') {
            return $fullName;
        }

        return $fullName . ' (' . $regNo . ')';
    }

    private function findLongestDisplayName(array $students): string
    {
        $longest = '';
        foreach ($students as $student) {
            $displayName = $this->buildDisplayName($student);
            if (strlen($displayName) > strlen($longest)) {
                $longest = $displayName;
            }
        }

        return $longest;
    }

    /**
     * @return list<array{id: int, label: string, students: list<object>}>
     */
    private function buildSectionsToRender(
        bool $allClasses,
        array $sectionIds,
        string $sectionLabel,
        array $sectionLabelMap,
        array $students
    ): array {
        if (!$allClasses) {
            return [[
                'id'       => (int) $sectionIds[0],
                'label'    => $sectionLabel,
                'students' => $students,
            ]];
        }

        $grouped = [];
        foreach ($students as $student) {
            $grouped[(int) $student->cls_sec_id][] = $student;
        }

        $sectionsToRender = [];
        foreach ($sectionIds as $sectionId) {
            $sectionId = (int) $sectionId;
            if (empty($grouped[$sectionId])) {
                continue;
            }

            $sectionsToRender[] = [
                'id'       => $sectionId,
                'label'    => $sectionLabelMap[$sectionId] ?? 'Section',
                'students' => $grouped[$sectionId],
            ];
        }

        return $sectionsToRender;
    }

    private function buildWeekHeaderHtml(
        array $weeks,
        array $timings,
        array $sectionAttendanceDates
    ): string {
        $headerHtml = '';
        foreach ($weeks as $week) {
            $workingDays = $this->countWorkingDaysInWeek(
                (string) $week->start_date,
                (string) $week->end_date,
                $timings,
                $sectionAttendanceDates
            );

            $weekLabel   = esc($this->formatWeekColumnLabel($week));
            $fullName    = esc($week->week_name ?: ('Week ' . $week->week_no));
            $headerLabel = $weekLabel . '<br><span class="week-days">(' . $workingDays . ')</span>';
            $headerHtml .= '<th class="text-center week-col col-week" title="' . esc($fullName . ' (' . $week->start_date . ' to ' . $week->end_date . ')') . '">'
                . $headerLabel . '</th>';
        }

        return $headerHtml;
    }

    private function buildSectionBlockHtml(
        string $sectionLabel,
        array $students,
        array $weeks,
        array $attendanceByStudentDate,
        array $timings,
        array $sectionAttendanceDates,
        array $includeFilters,
        string $pageBreakCls,
        bool $showSectionHeading
    ): string {
        $longestDisplayName = $this->findLongestDisplayName($students);
        $studentColPx         = $this->estimateStudentColumnPx($longestDisplayName);
        $weekTotals           = array_fill(0, count($weeks), 0);
        $rowsHtml             = '';
        $rowNum               = 1;
        $grandTotal           = 0;

        foreach ($students as $student) {
            $studentId   = (int) $student->student_id;
            $displayName = $this->buildDisplayName($student);
            $rowTotal    = 0;

            $rowsHtml .= '<tr>';
            $rowsHtml .= '<td class="text-center col-num">' . $rowNum . '</td>';
            $rowsHtml .= '<td class="student-name col-student">' . esc($displayName) . '</td>';

            foreach ($weeks as $weekIndex => $week) {
                $count = $this->countNonPresentInWeek(
                    (string) $week->start_date,
                    (string) $week->end_date,
                    $attendanceByStudentDate[$studentId] ?? [],
                    $timings,
                    $includeFilters,
                    $sectionAttendanceDates
                );

                $rowTotal += $count;
                $weekTotals[$weekIndex] += $count;
                $rowsHtml .= '<td class="text-center week-count col-week">' . $count . '</td>';
            }

            $grandTotal += $rowTotal;
            $rowsHtml .= '<td class="text-center fw-bold row-total col-total">' . $rowTotal . '</td>';
            $rowsHtml .= '</tr>';
            $rowNum++;
        }

        $headerHtml = $this->buildWeekHeaderHtml($weeks, $timings, $sectionAttendanceDates);

        $footerHtml = '';
        foreach ($weekTotals as $total) {
            $footerHtml .= '<td class="text-center fw-bold">' . $total . '</td>';
        }

        $colgroupHtml = '<colgroup><col class="col-num"><col class="col-student">';
        foreach ($weeks as $week) {
            $colgroupHtml .= '<col class="col-week">';
        }
        $colgroupHtml .= '<col class="col-total"></colgroup>';

        $sectionHeading = '';
        if ($showSectionHeading) {
            $sectionHeading = '<div class="section-report-heading">' . esc($sectionLabel) . '</div>';
        }

        return '
            <div class="report-section-block' . $pageBreakCls . '">
                ' . $sectionHeading . '
                <div class="table-responsive">
                    <table class="table table-bordered table-sm weekly-non-present-table mb-0" style="--student-col: ' . $studentColPx . 'px;">
                        ' . $colgroupHtml . '
                        <thead>
                            <tr>
                                <th class="text-center col-num">#</th>
                                <th class="col-student">Student</th>
                                ' . $headerHtml . '
                                <th class="text-center col-total">Total</th>
                            </tr>
                        </thead>
                        <tbody>' . $rowsHtml . '</tbody>
                        <tfoot>
                            <tr class="summary-row">
                                <td colspan="2" class="text-end fw-bold">Column Total</td>
                                ' . $footerHtml . '
                                <td class="text-center fw-bold">' . $grandTotal . '</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>';
    }

    private function estimateStudentColumnPx(string $longestName): int
    {
        $len = strlen($longestName);
        if ($len === 0) {
            return 160;
        }

        return max(140, min(270, (int) round($len * 6.4) + 14));
    }

    private function buildSectionAttendanceDatesBySection(
        array $sectionIds,
        array $studentIds,
        string $rangeStart,
        string $rangeEnd,
        int $sessionId
    ): array {
        $result = [];
        foreach ($sectionIds as $sectionId) {
            $result[$sectionId] = [];
        }

        if ($studentIds === [] || $sectionIds === []) {
            return $result;
        }

        $rows = $this->db->table('attendance a')
            ->select('DATE(a.date) AS att_date, sc.cls_sec_id', false)
            ->join('student_class sc', 'sc.student_id = a.student_id', 'inner')
            ->whereIn('a.student_id', $studentIds)
            ->whereIn('sc.cls_sec_id', $sectionIds)
            ->where('sc.session_id', $sessionId)
            ->where('sc.status', 1)
            ->where('a.date >=', $rangeStart)
            ->where('a.date <=', $rangeEnd)
            ->groupBy('att_date, sc.cls_sec_id')
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $sectionId = (int) $row->cls_sec_id;
            $dateKey = date('Y-m-d', strtotime((string) ($row->att_date ?? '')));
            $result[$sectionId][$dateKey] = true;
        }

        return $result;
    }

    private function countWorkingDaysInWeek(
        string $startDate,
        string $endDate,
        array $timings,
        array $sectionAttendanceDates
    ): int {
        $count   = 0;
        $current = strtotime($startDate);
        $end     = strtotime($endDate);
        $today   = strtotime(date('Y-m-d'));

        while ($current <= $end) {
            if ($current > $today) {
                break;
            }

            $date    = date('Y-m-d', $current);
            $dayName = date('l', $current);

            if (
                $this->isWorkingDay($dayName, $timings)
                && !empty($sectionAttendanceDates[$date])
            ) {
                $count++;
            }

            $current = strtotime('+1 day', $current);
        }

        return $count;
    }

    private function countWorkingDaysInWeekForSections(
        string $startDate,
        string $endDate,
        array $sectionIds,
        array $timingsBySection,
        array $sectionAttendanceDatesBySection
    ): int {
        $count   = 0;
        $current = strtotime($startDate);
        $end     = strtotime($endDate);
        $today   = strtotime(date('Y-m-d'));

        while ($current <= $end) {
            if ($current > $today) {
                break;
            }

            $date    = date('Y-m-d', $current);
            $dayName = date('l', $current);
            $counted = false;

            foreach ($sectionIds as $sectionId) {
                $timings = $timingsBySection[$sectionId] ?? [];
                $sectionDates = $sectionAttendanceDatesBySection[$sectionId] ?? [];

                if (
                    $this->isWorkingDay($dayName, $timings)
                    && !empty($sectionDates[$date])
                ) {
                    $counted = true;
                    break;
                }
            }

            if ($counted) {
                $count++;
            }

            $current = strtotime('+1 day', $current);
        }

        return $count;
    }

    private function countNonPresentInWeek(
        string $startDate,
        string $endDate,
        array $attendanceByDate,
        array $timings,
        array $includeFilters,
        array $sectionAttendanceDates
    ): int {
        $count   = 0;
        $current = strtotime($startDate);
        $end     = strtotime($endDate);
        $today   = strtotime(date('Y-m-d'));

        while ($current <= $end) {
            if ($current > $today) {
                break;
            }

            $date       = date('Y-m-d', $current);
            $dayName    = date('l', $current);
            $attendance = $attendanceByDate[$date] ?? null;

            if (!$this->isWorkingDay($dayName, $timings)) {
                $current = strtotime('+1 day', $current);
                continue;
            }

            if (empty($sectionAttendanceDates[$date])) {
                $current = strtotime('+1 day', $current);
                continue;
            }

            if ($attendance === null) {
                $current = strtotime('+1 day', $current);
                continue;
            }

            if ($this->shouldCountDay($attendance, $includeFilters)) {
                $count++;
            }

            $current = strtotime('+1 day', $current);
        }

        return $count;
    }

    /**
     * @return array<string, bool> Keys: absent, leave, late, early_left
     */
    private function parseIncludeFilters(): array
    {
        $filters = [
            'absent'     => (bool) $this->request->getPost('include_absent'),
            'leave'      => (bool) $this->request->getPost('include_leave'),
            'late'       => (bool) $this->request->getPost('include_late'),
            'early_left' => (bool) $this->request->getPost('include_early_left'),
        ];

        return array_filter($filters) ?: [];
    }

    private function formatIncludeFiltersLabel(array $includeFilters): string
    {
        $labels = [
            'absent'     => 'Absentees',
            'leave'      => 'Leave',
            'late'       => 'Late Coming',
            'early_left' => 'Early Left',
        ];

        $selected = [];
        foreach ($includeFilters as $key => $enabled) {
            if ($enabled && isset($labels[$key])) {
                $selected[] = $labels[$key];
            }
        }

        return $selected !== [] ? implode(', ', $selected) : 'None';
    }

    /**
     * @return list<string> Category keys: absent, leave, late, early_left
     */
    private function getDayCategories($attendance): array
    {
        if ($attendance === null) {
            return [];
        }

        $status = strtoupper(trim((string) ($attendance->status ?? '')));

        if ($status === '') {
            return [];
        }

        $lcDuration = (int) ($attendance->lc_duration ?? 0);
        $elDuration = (int) ($attendance->el_duration ?? 0);
        $categories = [];

        if ($this->isExplicitAbsent($status)) {
            $categories[] = 'absent';
        }

        if (in_array($status, ['L', 'LEAVE'], true)) {
            $categories[] = 'leave';
        }

        if (
            in_array($status, ['LC', 'LATE', 'LATE COMING'], true)
            || ($lcDuration > 0 && in_array($status, ['P', 'PRESENT'], true))
        ) {
            $categories[] = 'late';
        }

        if (
            in_array($status, ['EL', 'EARLY LEAVE'], true)
            || ($elDuration > 0 && in_array($status, ['P', 'PRESENT'], true))
        ) {
            $categories[] = 'early_left';
        }

        return array_values(array_unique($categories));
    }

    private function shouldCountDay($attendance, array $includeFilters): bool
    {
        foreach ($this->getDayCategories($attendance) as $category) {
            if (!empty($includeFilters[$category])) {
                return true;
            }
        }

        return false;
    }

    private function isExplicitAbsent(string $status): bool
    {
        return in_array($status, ['A', 'ABSENT'], true);
    }

    private function isWorkingDay(string $dayName, array $timings): bool
    {
        if (!isset($timings[$dayName])) {
            return false;
        }

        $timing = $timings[$dayName];
        $checkin  = (string) ($timing->checkin_timing ?? '');
        $checkout = (string) ($timing->checkout_timing ?? '');

        if ($checkin === '' || $checkout === '') {
            return false;
        }

        return $checkin !== $checkout;
    }

    private function formatWeekColumnLabel(object $week): string
    {
        $weekName = trim((string) ($week->week_name ?? ''));

        if ($weekName !== '' && preg_match('/(W\d+)$/i', $weekName, $matches)) {
            return strtoupper($matches[1]);
        }

        $weekNo = (int) ($week->week_no ?? 0);

        return $weekNo > 0 ? ('W' . $weekNo) : $weekName;
    }

    private function getActiveClassSections(int $campusId): array
    {
        if ($campusId <= 0) {
            return [];
        }

        $rows = $this->db->table('class_section cs')
            ->select(
                'cs.cls_sec_id,
                 CONCAT(COALESCE(c.class_short_name, c.class_name), " - ", COALESCE(s.short_name, s.section_name)) AS sectionclassname',
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
                'cls_sec_id'       => (int) ($row['cls_sec_id'] ?? 0),
                'sectionclassname' => $row['sectionclassname'] ?? '',
            ];
        }

        return $sections;
    }
}
