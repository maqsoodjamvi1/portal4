<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class EmployeesAttendance extends BaseController
{
    protected $db;
    protected $session;
    protected $userId;
    protected $campusId;
    protected $sessionId;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db      = \Config\Database::connect();
        $this->session = session();

        check_permission('admin-student-attendance');

        $this->userId    = $this->session->get('member_userid');
        $this->campusId  = $this->session->get('member_campusid');
        $this->sessionId = $this->session->get('member_sessionid');
    }

    public function index()
    {
        return view('admin/employees_attendance', [
            'sessionData' => [
                'campusid'  => $this->campusId,
                'sessionid' => $this->sessionId,
            ],
        ]);
    }

    public function data()
    {
        $request = service('request');
        $draw    = $request->getPost('draw');

        $builder      = $this->db->table('attendance');
        $recordsTotal = $builder->countAllResults(false);
        $attendanceData = $builder->get()->getResult();

        $academicSession = $this->db->table('academic_session')
            ->where('session_id', $this->sessionId)
            ->get()->getRow();

        $data = [];
        foreach ($attendanceData as $row) {
            $student = $this->db->table('users')->where('id', $row->student_id)->get()->getRow();
            $studentClass = $this->db->table('student_class')->where('student_id', $row->student_id)->get()->getRow();
            $class = $studentClass
                ? $this->db->table('classes')->where('class_id', $studentClass->class_id)->get()->getRow()
                : null;

            $termName = '';
            $termsSession = $this->db->query(
                "SELECT * FROM terms_session WHERE session_id = ? AND ? BETWEEN start_date AND end_date",
                [$this->sessionId, $row->date]
            )->getResult();

            if (! empty($termsSession)) {
                $term = $this->db->table('terms')->where('term_id', $termsSession[0]->term_id)->get()->getRow();
                $termName = $term->name ?? '';
            }

            $data[] = [
                'id'           => $row->attendance_id,
                'student'      => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                'class'        => $class->class_name ?? '',
                'session_name' => $academicSession->session_name ?? '',
                'term_name'    => $termName,
                'date'         => $row->date,
                'detail'       => $row->detail,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data'            => $data,
        ]);
    }

    public function add()
    {
        check_permission('admin-add-student-attendance');

        return view('admin/employees_attendance_edit', [
            'sessionData' => [
                'campusid'  => $this->campusId,
                'sessionid' => $this->sessionId,
            ],
        ]);
    }

    public function edit()
    {
        check_permission('admin-edit-student-attendance');

        return view('admin/employees_attendance_edit', [
            'sessionData' => [
                'campusid'  => $this->campusId,
                'sessionid' => $this->sessionId,
            ],
        ]);
    }

    public function save(): ResponseInterface
    {
        check_permission('admin-add-student-attendance');

        $employeeIds = $this->request->getPost('employee_id');
        $inputDate   = $this->request->getPost('date');
        $now         = date('Y-m-d');

        if (! $employeeIds || ! $inputDate) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Date and employees are required.']);
        }

        if (! is_array($employeeIds)) {
            $employeeIds = [$employeeIds];
        }

        $employeeIds = array_values(array_unique(array_map('intval', $employeeIds)));
        $day         = date('l', strtotime($inputDate));

        $timingsByUser = $this->loadTimingsForDay($employeeIds, $day);

        $this->db->transStart();

        foreach ($employeeIds as $employeeId) {
            $status = $this->request->getPost("{$employeeId}_status");
            if ($status === null || $status === '') {
                continue;
            }

            $empTiming = $this->resolveEmployeeTiming($employeeId, $day, $timingsByUser, (int) $this->campusId);

            $status = $this->normalizeAttendanceStatus($status);
            if ($status === '') {
                continue;
            }

            $remarks = trim((string) $this->request->getPost("{$employeeId}_remarks"));
            $scheduledIn  = $this->normalizeTime($empTiming->checkin ?? '');
            $scheduledOut = $this->normalizeTime($empTiming->checkout ?? '');

            $checkin  = '';
            $checkout = '';
            $lcDuration = 0;
            $elDuration = 0;

            switch ($status) {
                case 'P':
                    $checkin  = $scheduledIn;
                    $checkout = $scheduledOut;
                    break;
                case 'LC':
                    $checkin = $this->normalizeTime($this->request->getPost("{$employeeId}_checkin_date"));
                    if ($checkin === '') {
                        $checkin = $scheduledIn;
                    }
                    $checkout = $scheduledOut;
                    if ($checkin && $scheduledIn) {
                        $lcDuration = max(0, (int) round((strtotime($checkin) - strtotime($scheduledIn)) / 60));
                    }
                    break;
                case 'EL':
                    $checkin  = $scheduledIn;
                    $checkout = $this->normalizeTime($this->request->getPost("{$employeeId}_checkout_date"));
                    if ($checkout === '') {
                        $checkout = $scheduledOut;
                    }
                    if ($checkout && $scheduledOut) {
                        $elDuration = max(0, (int) round((strtotime($scheduledOut) - strtotime($checkout)) / 60));
                    }
                    break;
                case 'A':
                case 'L':
                    break;
            }

            $builder = $this->db->table('attendance_employee');
            $exists  = $builder
                ->where(['emp_id' => $employeeId, 'date' => $inputDate])
                ->get()
                ->getRow();

            $row = [
                'emp_id'      => $employeeId,
                'date'        => $inputDate,
                'status'      => $status,
                'checkin'     => $checkin,
                'checkout'    => $checkout,
                'lc_duration' => $lcDuration,
                'el_duration' => $elDuration,
                'remarks'     => in_array($status, ['A', 'L'], true) ? $remarks : '',
                'user_id'     => $this->userId,
            ];

            if ($exists) {
                $row['updated_date'] = $now;
                $builder->where(['emp_id' => $employeeId, 'date' => $inputDate])->update($row);
            } else {
                $row['created_date'] = $now;
                $builder->insert($row);
            }
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Failed to save attendance.']);
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Attendance saved successfully.']);
    }

    public function get_employees(): ResponseInterface
    {
        check_permission('admin-add-student-attendance');

        try {
            return $this->buildEmployeesAttendanceGrid();
        } catch (\Throwable $e) {
            log_message('error', 'EmployeesAttendance::get_employees — ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setBody(
                "<div class='alert alert-danger mb-0'>Could not load employees. Please refresh and try again.</div>"
            );
        }
    }

    private function buildEmployeesAttendanceGrid(): ResponseInterface
    {
        $campusId  = (int) $this->request->getPost('campus_id');
        $dateValue = $this->request->getPost('date');

        if ($campusId <= 0) {
            $campusId = (int) $this->campusId;
        }

        if (! $dateValue || ! strtotime($dateValue)) {
            return $this->response->setBody(
                "<div class='alert alert-warning mb-0'>Please select a valid date.</div>"
            );
        }

        $day = date('l', strtotime($dateValue));

        $users = $this->db->table('users')
            ->select('id, first_name, last_name, photo, designation')
            ->where(['status' => 1, 'campus_id' => $campusId])
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get()
            ->getResult();

        if ($users === []) {
            return $this->response->setBody(
                "<div class='alert alert-info mb-0'>No active employees found for this campus.</div>"
            );
        }

        $userIds = array_map(static fn ($u) => (int) $u->id, $users);

        $timingsByUser = $this->loadTimingsForDay($userIds, $day);

        $attendanceByUser = [];
        $attendanceRows = $this->db->table('attendance_employee')
            ->where('date', $dateValue)
            ->whereIn('emp_id', $userIds)
            ->get()
            ->getResult();

        foreach ($attendanceRows as $row) {
            $attendanceByUser[(int) $row->emp_id] = $row;
        }

        $employees           = [];
        $missingTimingCount  = 0;

        foreach ($users as $user) {
            $uid = (int) $user->id;
            $hasCustomTiming = isset($timingsByUser[$uid]);
            $timing = $this->resolveEmployeeTiming($uid, $day, $timingsByUser, $campusId);

            if (! $hasCustomTiming) {
                $missingTimingCount++;
            }

            $attendance = $attendanceByUser[$uid] ?? null;
            $status     = $this->normalizeAttendanceStatus(
                $attendance ? (string) ($attendance->status ?? '') : ''
            );

            $checkin = ($attendance && $status === 'LC' && ! empty($attendance->checkin))
                ? $attendance->checkin
                : ($timing->checkin ?? '');
            $checkout = ($attendance && $status === 'EL' && ! empty($attendance->checkout))
                ? $attendance->checkout
                : ($timing->checkout ?? '');

            $employees[] = [
                'id'            => $uid,
                'name'          => trim($user->first_name . ' ' . $user->last_name),
                'designation'   => trim((string) ($user->designation ?? '')),
                'photo_url'     => $this->employeePhotoUrl($user->photo ?? null),
                'status'        => $status,
                'has_saved'     => $attendance !== null,
                'has_custom_timing' => $hasCustomTiming,
                'remarks'       => $attendance ? (string) ($attendance->remarks ?? '') : '',
                'checkin'       => $this->formatTimeForInput($checkin),
                'checkout'      => $this->formatTimeForInput($checkout),
                'scheduled_in'  => $this->formatTimeForInput($timing->checkin ?? ''),
                'scheduled_out' => $this->formatTimeForInput($timing->checkout ?? ''),
            ];
        }

        return $this->response->setBody(view('admin/partials/employees_attendance_grid', [
            'employees'            => $employees,
            'missing_timing_count' => $missingTimingCount,
            'day_label'            => $day,
        ]));
    }

    public function delete(): ResponseInterface
    {
        check_permission('admin-del-attendance');
        $id = (int) $this->request->getGet('id');

        $this->db->table('attendance_employee')->where('attendance_id', $id)->delete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Attendance record deleted successfully.']);
    }

    /**
     * Employee-specific timing for a day, or campus default when not configured.
     */
    private function resolveEmployeeTiming(int $userId, string $day, array $timingsByUser, int $campusId): object
    {
        if (isset($timingsByUser[$userId])) {
            return $timingsByUser[$userId];
        }

        return $this->defaultCampusTiming($campusId);
    }

    private function defaultCampusTiming(int $campusId): object
    {
        $settings = null;
        if ($campusId > 0) {
            $settings = $this->db->table('attendance_settings')
                ->where('campus_id', $campusId)
                ->get()
                ->getRow();
        }

        $checkin = $this->normalizeTime($settings->checkin ?? '08:00');
        $checkout = $this->normalizeTime(
            $settings->halfday_checkout ?? $settings->checkout ?? '16:00'
        );

        return (object) [
            'user_id'  => 0,
            'dayname'  => '',
            'checkin'  => $checkin !== '' ? $checkin : '08:00',
            'checkout' => $checkout !== '' ? $checkout : '16:00',
        ];
    }

    /**
     * @param int[] $userIds
     * @return array<int, object>
     */
    private function loadTimingsForDay(array $userIds, string $day): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = $this->db->table('emp_timings')
            ->where('dayname', $day)
            ->whereIn('user_id', $userIds)
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->user_id] = $row;
        }

        return $map;
    }

    private function employeePhotoUrl(?string $photo): string
    {
        if (function_exists('getEmployeePhotoUrl')) {
            return getEmployeePhotoUrl($photo);
        }

        if (! empty($photo) && is_file(FCPATH . 'uploads/' . $photo)) {
            return base_url('uploads/' . $photo);
        }

        return base_url('resource/adminlte/dist/img/emp-avatar.jpg');
    }

    private function normalizeTime(?string $time): string
    {
        if ($time === null || $time === '') {
            return '';
        }

        $time = trim($time);
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            return $time;
        }

        $ts = strtotime($time);

        return $ts ? date('H:i', $ts) : $time;
    }

    /**
     * HH:MM for HTML time inputs.
     */
    private function formatTimeForInput($time): string
    {
        $normalized = $this->normalizeTime($time !== null ? (string) $time : '');

        return strlen($normalized) >= 5 ? substr($normalized, 0, 5) : $normalized;
    }

    private function normalizeAttendanceStatus(?string $status): string
    {
        $code = strtoupper(trim((string) $status));
        $map  = [
            'PRESENT' => 'P',
            'PR'      => 'P',
            'ABSENT'  => 'A',
            'AB'      => 'A',
            'LATE'    => 'LC',
            'LATE COMING' => 'LC',
            'EARLY'   => 'EL',
            'EARLY LEAVING' => 'EL',
            'LEAVE'   => 'L',
            'LV'      => 'L',
        ];

        if (isset($map[$code])) {
            return $map[$code];
        }

        return in_array($code, ['P', 'A', 'LC', 'EL', 'L'], true) ? $code : '';
    }
}
