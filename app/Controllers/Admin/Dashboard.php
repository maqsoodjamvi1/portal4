<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\DashboardDataService;
use App\Libraries\DashboardMetricsService;
use App\Libraries\SchoolSetupProgress;
use Config\Database;
use Config\Services;
use DateTime;
use DateInterval;
use DatePeriod;

class Dashboard extends BaseController
{
    protected $db;
    protected $session;
    protected $campusId;
    protected $sessionId;
    protected $schoolInfo;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->initializeServices();
    }
public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();

        $this->session = Services::session();
        log_message('debug', 'Academic session initialized: ' . print_r($this->session, true));
    }




/**
 * Get teacher's recent attendance (AJAX)
 */

public function getRecentAttendance()
{
    try {
        $this->response->setHeader('Content-Type', 'application/json');

        $teacher_id = $this->session->get('member_userid');
        $campus_id = $this->session->get('member_campusid');

        $isTeacher = $this->isDashboardTeacher((int) $teacher_id);
        $isDirectorOrPrincipal = ! $isTeacher && (int) $teacher_id > 0;

        if (!$teacher_id) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'No user logged in',
                'attendance' => [],
                'settings' => [
                    'checkin' => '08:15:00',
                    'checkout' => '12:00:00',
                    'halfday_checkout' => '16:00:00'
                ]
            ]);
        }

        // If director/principal, return all staff attendance
        if ($isDirectorOrPrincipal) {
            $attendance = $this->db->table('attendance_employee a')
                ->select('a.date, a.checkin, a.checkout, a.status, a.check_in_method, u.first_name, u.last_name')
                ->join('users u', 'a.emp_id = u.id')
                ->where('u.campus_id', $campus_id)
                ->orderBy('a.date', 'DESC')
                ->limit(10)
                ->get()
                ->getResult();
        } else {
            // Regular teacher - get their own attendance
            $attendance = $this->db->table('attendance_employee')
                ->select('date, checkin, checkout, status, check_in_method')
                ->where('emp_id', $teacher_id)
                ->orderBy('date', 'DESC')
                ->limit(10)
                ->get()
                ->getResult();
        }

        // Get attendance settings
        $settings = $this->db->table('attendance_settings')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();

        // Default settings
        $defaultSettings = [
            'checkin' => '08:15:00',
            'checkout' => '12:00:00',
            'halfday_checkout' => '16:00:00',
            'grace_period_minutes' => 5
        ];

        $formattedAttendance = [];
        foreach ($attendance as $att) {
            if ($isDirectorOrPrincipal) {
                $formattedAttendance[] = [
                    'date' => date('d M Y', strtotime($att->date)),
                    'employee' => $att->first_name . ' ' . $att->last_name,
                    'checkin' => $att->checkin ? date('h:i A', strtotime($att->checkin)) : '-',
                    'checkout' => $att->checkout ? date('h:i A', strtotime($att->checkout)) : '-',
                    'status' => $att->status ?? 'present',
                    'status_badge' => ($att->status ?? 'present') == 'late' ? 'warning' : 'success'
                ];
            } else {
                $formattedAttendance[] = [
                    'date' => date('d M Y', strtotime($att->date)),
                    'checkin' => $att->checkin ? date('h:i A', strtotime($att->checkin)) : '-',
                    'checkout' => $att->checkout ? date('h:i A', strtotime($att->checkout)) : '-',
                    'status' => $att->status ?? 'present',
                    'status_badge' => ($att->status ?? 'present') == 'late' ? 'warning' : 'success'
                ];
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'attendance' => $formattedAttendance,
            'isDirectorOrPrincipal' => $isDirectorOrPrincipal,
            'settings' => [
                'checkin' => $settings->checkin ?? $defaultSettings['checkin'],
                'checkout' => $settings->checkout ?? $defaultSettings['checkout'],
                'halfday_checkout' => $settings->halfday_checkout ?? $defaultSettings['halfday_checkout'],
                'grace_period_minutes' => $settings->grace_period_minutes ?? $defaultSettings['grace_period_minutes']
            ]
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Exception in getRecentAttendance: ' . $e->getMessage());

        return $this->response->setJSON([
            'success' => false,
            'error' => $e->getMessage(),
            'attendance' => [],
            'settings' => [
                'checkin' => '08:15:00',
                'checkout' => '12:00:00',
                'halfday_checkout' => '16:00:00',
                'grace_period_minutes' => 5
            ]
        ]);
    }
}


/**
 * Process attendance via QR scan (AJAX)
 */
public function processTeacherAttendance()
{
    // Enable CORS for AJAX
    $this->response->setHeader('Content-Type', 'application/json');

    if (!$this->request->isAJAX()) {
        log_message('error', 'processTeacherAttendance: Not an AJAX request');
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }

    $qr_code = $this->request->getPost('qr_code');
    $teacher_id = session()->get('member_userid');
    $campus_id = session()->get('member_campusid');

    log_message('debug', 'processTeacherAttendance - Teacher ID: ' . $teacher_id . ', Campus ID: ' . $campus_id);
    log_message('debug', 'processTeacherAttendance - QR Code: ' . $qr_code);

    if (!$teacher_id) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'User not logged in'
        ]);
    }

    // Verify campus QR code
    $campus_qr = $this->db->table('campus_qr_codes')
        ->where('campus_id', $campus_id)
        ->where('qr_code', $qr_code)
        ->where('is_active', 1)
        ->get()
        ->getRow();

    if (!$campus_qr) {
        log_message('debug', 'Invalid QR code for campus: ' . $campus_id);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid or expired campus QR code'
        ]);
    }

    $today = date('Y-m-d');
    $current_time = date('H:i:s');

    // Check today's attendance
    $attendance = $this->db->table('attendance_employee')
        ->where('emp_id', $teacher_id)
        ->where('date', $today)
        ->get()
        ->getRow();

    if (!$attendance) {
        // First scan - CHECK IN
        $settings = $this->db->table('attendance_settings')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();

        // UPDATED: Use new column names
        $late_threshold = $settings->checkin ?? '08:15:00';           // Renamed from late_threshold
        $halfday_checkout = $settings->halfday_checkout ?? '16:00:00'; // New column for full day
        $checkout_threshold = $settings->checkout ?? '12:00:00';       // Renamed from half_day_threshold

        // Determine status based on check-in time
        $status = $current_time > $late_threshold ? 'late' : 'present';

        $insertData = [
            'emp_id' => $teacher_id,
            'date' => $today,
            'checkin' => $current_time,
            'status' => $status,
            'check_in_method' => 'qr_mobile',
            'created_date' => date('Y-m-d H:i:s'),
            'user_id' => $teacher_id
        ];

        $this->db->table('attendance_employee')->insert($insertData);

        log_message('debug', 'Check-in recorded for teacher: ' . $teacher_id);

        return $this->response->setJSON([
            'success' => true,
            'type' => 'checkin',
            'message' => '✅ Check-in successful at ' . date('h:i A'),
            'time' => date('h:i A'),
            'status' => $status,
            'is_late' => ($status == 'late'),
            'checkin_time' => date('h:i A'),
            'required_checkin' => date('h:i A', strtotime($late_threshold))
        ]);

    } elseif (!$attendance->checkout) {
        // Second scan - CHECK OUT
        $settings = $this->db->table('attendance_settings')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();

        // UPDATED: Use new column names for checkout logic
        $halfday_checkout = $settings->halfday_checkout ?? '16:00:00';  // Full day checkout time
        $checkout_threshold = $settings->checkout ?? '12:00:00';        // Half day threshold

        $checkin = new \DateTime($attendance->checkin);
        $checkout = new \DateTime($current_time);
        $interval = $checkin->diff($checkout);
        $hours = $interval->h + ($interval->i / 60);
        $total_minutes = ($interval->h * 60) + $interval->i;

        // Determine if it's half day or full day
        $is_half_day = ($current_time <= $halfday_checkout) && ($hours < 6);

        $status = $attendance->status;
        if ($is_half_day && $status != 'late') {
            $status = 'half_day';
        } elseif (!$is_half_day && $status != 'late') {
            $status = 'present';
        }

        $this->db->table('attendance_employee')
            ->where('attendance_id', $attendance->attendance_id)
            ->update([
                'checkout' => $current_time,
                'check_out_method' => 'qr_mobile',
                'lc_duration' => $total_minutes,
                'status' => $status,
                'updated_date' => date('Y-m-d H:i:s')
            ]);

        log_message('debug', 'Check-out recorded for teacher: ' . $teacher_id);

        return $this->response->setJSON([
            'success' => true,
            'type' => 'checkout',
            'message' => '✅ Check-out successful at ' . date('h:i A'),
            'time' => date('h:i A'),
            'duration' => round($hours, 1),
            'status' => $status,
            'is_half_day' => $is_half_day,
            'checkout_time' => date('h:i A'),
            'required_checkout' => $is_half_day ? date('h:i A', strtotime($checkout_threshold)) : date('h:i A', strtotime($halfday_checkout))
        ]);

    } else {
        return $this->response->setJSON([
            'success' => false,
            'message' => '⚠️ Attendance already completed for today'
        ]);
    }
}




/**
 * Process attendance via QR scan (AJAX)
 */

/**
 * Get teacher's recent attendance (AJAX)
 */

    protected function initializeServices()
    {
        helper(['auth', 'server']);
        $this->db = Database::connect();
        $this->session = Services::session();
        $this->campusId = $this->session->get('member_campusid');
        $this->sessionId = $this->session->get('member_sessionid');
        $this->schoolInfo = $this->resolveSchoolInfo();
    }

    /**
     * System ID for the campus currently selected in session (not hardcoded).
     */
    protected function getDashboardSystemId(): int
    {
        return (int) ($this->schoolInfo->system_id ?? 0);
    }

    protected function resolveSchoolInfo(): object
    {
        if (function_exists('getSchoolInfo')) {
            $info = getSchoolInfo();
            if ($info && ! empty($info->system_id)) {
                return $info;
            }
        }

        $campusId = (int) ($this->campusId ?? $this->session->get('member_campusid'));
        if ($campusId > 0) {
            $row = $this->db->table('campus')
                ->select('system_id')
                ->where('campus_id', $campusId)
                ->get()
                ->getRow();
            if ($row && (int) $row->system_id > 0) {
                return (object) ['system_id' => (int) $row->system_id];
            }
        }

        return (object) ['system_id' => 1];
    }



/**
 * Get attendance settings for current campus
 * @return \CodeIgniter\HTTP\Response
 */
public function getAttendanceSettings()
{
    try {
        // Force JSON response
        $this->response->setHeader('Content-Type', 'application/json');

        // Get campus ID from session
        $campus_id = session()->get('member_campusid');

        // Try to get settings from database
        $settings = null;

        // Check if table exists
        try {
            $settings = $this->db->table('attendance_settings')
                ->where('campus_id', $campus_id)
                ->get()
                ->getRow();
        } catch (\Exception $e) {
            log_message('error', 'Error fetching attendance settings: ' . $e->getMessage());
        }

        // Prepare response data
        $responseData = [
            'checkin' => $settings->checkin ?? '08:15:00',
            'checkout' => $settings->checkout ?? '12:00:00',
            'halfday_checkout' => $settings->halfday_checkout ?? '16:00:00',
            'grace_period_minutes' => $settings->grace_period_minutes ?? 5,
            'allow_self_checkout' => $settings->allow_self_checkout ?? 1
        ];

        // Return JSON
        return $this->response->setJSON($responseData);

    } catch (\Exception $e) {
        log_message('error', 'Error in getAttendanceSettings: ' . $e->getMessage());

        // Return default settings
        return $this->response->setJSON([
            'checkin' => '08:15:00',
            'checkout' => '12:00:00',
            'halfday_checkout' => '16:00:00',
            'grace_period_minutes' => 5,
            'allow_self_checkout' => 1
        ]);
    }
}

/**
 * Active billing plan for the campus (e.g. 3).
 */
protected function getCampusPlanId(?int $campusId = null): int
{
    $campusId = $campusId ?? (int) $this->session->get('member_campusid');
    if ($campusId <= 0) {
        return 0;
    }

    $row = $this->db->table('campus_bills')
        ->select('plan_id')
        ->where('campus_id', $campusId)
        ->where('status', 1)
        ->limit(1)
        ->get()
        ->getRow();

    return (int) ($row->plan_id ?? 0);
}

/**
 * Teacher role uses role_name_id = 5 (roles.id varies by plan, e.g. 15 on plan 3).
 */
protected function isDashboardTeacher(?int $userId = null): bool
{
    $userId = $userId ?? (int) $this->session->get('member_userid');
    if ($userId <= 0) {
        return false;
    }

    $planId = $this->getCampusPlanId();
    if ($planId <= 0) {
        return false;
    }

    $teacherRoleNameId = 5;

    $primary = $this->db->table('user_roles ur')
        ->select('r.role_name_id')
        ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
        ->where('ur.userID', $userId)
        ->get()
        ->getResultArray();

    foreach ($primary as $row) {
        if ((int) ($row['role_name_id'] ?? 0) === $teacherRoleNameId) {
            return true;
        }
    }

    // Legacy rows where user_roles.roleID stores role_name_id
    $legacy = $this->db->table('user_roles ur')
        ->select('r.role_name_id')
        ->join('roles r', 'r.role_name_id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
        ->where('ur.userID', $userId)
        ->get()
        ->getResultArray();

    foreach ($legacy as $row) {
        if ((int) ($row['role_name_id'] ?? 0) === $teacherRoleNameId) {
            return true;
        }
    }

    return false;
}

public function index()
{
    if (! $this->session->get('IsAuthorized') && ! $this->session->get('member_userid')) {
        return redirect()->to(base_url('admin/login'));
    }

    helper('server');
    $schoolinfo = function_exists('getSchoolInfo') ? getSchoolInfo() : null;
    $systemId   = (int) ($schoolinfo->system_id ?? 0);
    $campusId   = (int) ($this->session->get('member_campusid') ?? 0);
    $userId     = (int) ($this->session->get('member_userid') ?? 0);

    if ($systemId > 0 && ! \App\Libraries\SchoolSetupProgress::isTeacher($userId, $campusId)) {
        if (trim((string) ($this->session->get('member_reg_text') ?? $schoolinfo->reg_text ?? '')) === '') {
            return redirect()->to(base_url('admin/profile-system'));
        }
        if (! \App\Libraries\SchoolSetupProgress::isComplete($systemId, $campusId)) {
            $next = \App\Libraries\SchoolSetupProgress::nextStepUrl($systemId, $campusId)
                ?? base_url('admin/getting-started');

            return redirect()->to($next)->with(
                'setup_required',
                lang('SchoolSetup.redirect_dashboard_required')
            );
        }
    }

    // Get current academic session (for other dashboard data)
    $this->academic_session = $this->getAcademicSession();

    if (empty($this->academic_session) || empty($this->academic_session->session_id)) {
        return view('admin/dashboard', [
            'error' => 'No valid academic session found. Please check system settings.'
        ]);
    }

    // Teacher = role_name_id 5 for the campus plan (not roles.id 5, which differs per plan)
    $isTeacher = $this->isDashboardTeacher();
    $isDirectorOrPrincipal = ! $isTeacher && (int) $this->session->get('member_userid') > 0;
    $dashboardLayoutRole = DashboardMetricsService::resolveLayoutRole($isTeacher);
    helper('permission');

    $setupProgressForActions = null;
    if ($systemId > 0 && ! SchoolSetupProgress::isTeacher($userId, $campusId)) {
        $setupProgressForActions = SchoolSetupProgress::getStatus($systemId, $campusId);
    }

    $campus_id = (int) session('member_campusid');
    $user_id = (int) session('member_userid');
    $today = date('Y-m-d');

    log_message('debug', 'Dashboard - Campus plan: ' . $this->getCampusPlanId());
    log_message('debug', 'Dashboard - Is Teacher (role_name_id 5): ' . ($isTeacher ? 'Yes' : 'No'));
    log_message('debug', 'Dashboard - Is Director/Principal: ' . ($isDirectorOrPrincipal ? 'Yes' : 'No'));

    $teacherSections        = [];
    $teacherSubjects        = [];
    $subjectsPerSection     = [];
    $classTeacherSections   = [];
    $todayAttendance        = null;
    $classTeacherMap        = [];

    $dashData = new DashboardDataService(
        $this->db,
        $campus_id,
        (int) session('member_sessionid'),
        $this->getDashboardSystemId()
    );

    if ($isTeacher && $user_id) {
        $teacherCtx = $dashData->loadTeacherContext($user_id, $today);
        $teacherSections      = $teacherCtx['teacherSections'];
        $teacherSubjects      = $teacherCtx['teacherSubjects'];
        $subjectsPerSection   = $teacherCtx['subjectsPerSection'];
        $classTeacherSections = $teacherCtx['classTeacherSections'];
        $todayAttendance      = $teacherCtx['todayAttendance'];
        $classTeacherMap      = $teacherCtx['classTeacherMap'];
    }

    $selectedSessionId = $this->request->getGet('session_id');
    $dashboardSystemId = $this->getDashboardSystemId();
    $allSessions       = [];
    $feeData           = ['months' => [], 'paid' => [], 'unpaid' => []];
    $chartTitle        = '';
    $chartType         = 'default';
    $feeSummaryData    = [];
    $classStrength     = ['classes' => [], 'male_counts' => [], 'female_counts' => []];
    $attendance        = ['present' => 0, 'absent' => 0, 'leaves' => 0];
    $loadFinance       = DashboardMetricsService::shouldLoadFinanceCharts();
    $loadAttendance    = DashboardMetricsService::shouldLoadStudentAttendanceBlock();
    $loadOverview      = DashboardMetricsService::shouldLoadOverviewKpis();
    $loadHealth        = DashboardMetricsService::shouldLoadHealthBlock();

    if ($loadFinance || $loadOverview) {
        $allSessions = $this->db->table('academic_session')
            ->where('system_id', $dashboardSystemId)
            ->orderBy('start_date', 'DESC')
            ->get()
            ->getResult();
    }

    if ($loadFinance) {
        $financeBundle = $dashData->loadFinanceBundle(
            $selectedSessionId ? (int) $selectedSessionId : null,
            static fn () => $allSessions,
            function () use ($selectedSessionId, $dashboardSystemId) {
                $useSessionForChart = null;
                if ($selectedSessionId) {
                    $useSessionForChart = $this->db->table('academic_session')
                        ->where('session_id', $selectedSessionId)
                        ->get()
                        ->getRow();
                }

                $feeTypeRows = $this->db->table('fee_type')
                    ->select('fee_type_id')
                    ->where('system_id', $dashboardSystemId)
                    ->where('is_monthly_fee', 1)
                    ->get()
                    ->getResultArray();

                $feeTypeIds = array_column($feeTypeRows, 'fee_type_id');

                if ($useSessionForChart) {
                    $feeData    = $this->getFeeDataArraysForSession($feeTypeIds, $useSessionForChart);
                    $chartTitle = $useSessionForChart->session_name;
                    $chartType  = 'session';
                } else {
                    $feeData    = $this->getFeeDataArraysLast12Months($feeTypeIds);
                    $chartTitle = 'Last 12 Months';
                    $chartType  = 'default';
                }

                return ['feeData' => $feeData, 'chartTitle' => $chartTitle, 'chartType' => $chartType];
            },
            fn () => $this->getFeeSummary()
        );

        $allSessions    = $financeBundle['allSessions'] ?? $allSessions;
        $feeData        = $financeBundle['feeData'] ?? $feeData;
        $chartTitle     = $financeBundle['chartTitle'] ?? $chartTitle;
        $chartType      = $financeBundle['chartType'] ?? $chartType;
        $feeSummaryData = $financeBundle['feeSummaryData'] ?? $feeSummaryData;
    }

    if ($loadFinance || $loadOverview) {
        $classStrength = $dashData->remember(
            'class_strength',
            fn () => $dashData->loadClassStrength((int) $this->academic_session->session_id)
        );
    }

    if ($loadAttendance || $loadOverview) {
        $attendance = $this->getTodaysAttendance();
    }

    // ========== GET TEACHER/STAFF ATTENDANCE DATA BASED ON ROLE ==========
    // Initialize variables
    $teacherAttendance = [];
    $totalEmployees = 0;
    $presentCount = 0;
    $checkedOutCount = 0;
    $lateCount = 0;
    $absentEmployees = [];
    $showEmployeeAttendance = false;

    if ($isDirectorOrPrincipal) {
        $staffBlock = $dashData->remember(
            'staff_att_' . $today,
            fn () => $dashData->loadDirectorStaffAttendance($today)
        );
        $teacherAttendance      = $staffBlock['teacherAttendance'];
        $totalEmployees         = $staffBlock['totalEmployees'];
        $presentCount           = $staffBlock['presentCount'];
        $checkedOutCount        = $staffBlock['checkedOutCount'];
        $lateCount              = $staffBlock['lateCount'];
        $absentEmployees        = $staffBlock['absentEmployees'];
        $showEmployeeAttendance = $staffBlock['showEmployeeAttendance'];
    } elseif ($isTeacher) {
        $staffBlock             = $dashData->loadTeacherStaffAttendance($user_id);
        $teacherAttendance      = $staffBlock['teacherAttendance'];
        $showEmployeeAttendance = $staffBlock['showEmployeeAttendance'];
    }

    $pendingAttendance       = [];
    $teacherPendingAttendance = [];
    $teacherDiaryMissing      = ['count' => 0, 'sections' => []];
    $teacherResultsPending    = ['count' => 0];
    $teacherQuizOpen          = 0;
    $teacherPendingSectionIds = [];

    if ($loadAttendance) {
        $pendingAttendance = $dashData->remember(
            'pending_att_' . $today,
            fn () => $dashData->loadPendingAttendance($today)
        );
    }

    $examsInfo = ($loadOverview || DashboardMetricsService::shouldLoadOperationsBlock() || $isTeacher)
        ? $this->getCurrentExam()
        : null;

    if ($isTeacher && $user_id) {
        $teacherPendingAttendance = $dashData->loadTeacherPendingAttendance(
            $user_id,
            $today,
            $classTeacherMap,
            $teacherSections
        );
        $teacherDiaryMissing = $dashData->loadTeacherDiaryMissing($user_id, $today);
        if ($examsInfo && ! empty($examsInfo->eid)) {
            $teacherResultsPending = $dashData->loadTeacherResultsPending($user_id, (int) $examsInfo->eid);
        }
        $teacherQuizOpen = $dashData->loadTeacherQuizOpen($user_id);
        $teacherPendingSectionIds = array_map(
            static fn (array $row): int => (int) ($row['cls_sec_id'] ?? 0),
            $teacherPendingAttendance
        );
        $teacherPendingSectionIds = array_values(array_filter($teacherPendingSectionIds, static fn (int $id): bool => $id > 0));
    }

    $bmiStats            = $loadHealth ? $this->getBmiStatistics($campus_id) : (object) [];
    $healthAlertsSummary = $loadHealth ? $this->getHealthAlertsSummary($campus_id) : ['count' => 0, 'recent' => []];

    $attPresent = (int) ($attendance['present'] ?? 0);
    $attAbsent  = (int) ($attendance['absent'] ?? 0);
    $attLeaves  = (int) ($attendance['leaves'] ?? 0);
    $attTotal   = $attPresent + $attAbsent + $attLeaves;
    $attendanceRate = $attTotal > 0 ? round(($attPresent / $attTotal) * 100) : 0;

    $feePaidPct = ($feeSummaryData['prjectedFee_info'] ?? 0) > 0
        ? round((($feeSummaryData['PaidFee_info'] ?? 0) / $feeSummaryData['prjectedFee_info']) * 100)
        : 0;

    $noOfstudent  = $loadOverview && DashboardMetricsService::can('admin-db-students') ? $this->getStudentCount() : 0;
    $infoteachers = $loadOverview && DashboardMetricsService::can('admin-db-teacher') ? $this->getTeacherCount() : 0;

    if ($isTeacher) {
        $firstPending = $teacherPendingAttendance[0] ?? null;
        $firstDiary   = $teacherDiaryMissing['sections'][0] ?? null;
        $actionCenter = DashboardMetricsService::buildTeacherActionCenter([
            'pendingAttendanceCount' => count($teacherPendingAttendance),
            'pendingAttendanceUrl'   => $firstPending
                ? base_url('admin/students_absentees/add?cls_sec_id=' . (int) $firstPending['cls_sec_id'] . '&date=' . urlencode($today))
                : base_url('admin/students_absentees/add'),
            'diaryMissingCount'      => (int) ($teacherDiaryMissing['count'] ?? 0),
            'diaryMissingUrl'        => $firstDiary
                ? base_url('admin/classdiary/add?cls_sec_id=' . (int) $firstDiary)
                : base_url('admin/classdiary/add'),
            'resultsPendingCount'    => (int) ($teacherResultsPending['count'] ?? 0),
            'quizOpenCount'          => $teacherQuizOpen,
            'campusId'               => $campus_id,
            'userId'                 => $user_id,
        ]);
    } else {
        $actionCenter = DashboardMetricsService::buildActionCenter(
            $campus_id,
            $systemId,
            $user_id,
            $setupProgressForActions,
            is_array($pendingAttendance) ? count($pendingAttendance) : 0
        );
    }

    $showOptionalModules = $systemId > 0
        && ! SchoolSetupProgress::isTeacher($userId, $campusId)
        && SchoolSetupProgress::isComplete($systemId, $campusId)
        && ! session()->get('optional_modules_dismissed');

    $data = array_merge([
        'dashboardLayoutRole' => $dashboardLayoutRole,
        'actionCenter'        => $actionCenter,
        'showOptionalModules' => $showOptionalModules,
        'noOfstudent'       => $noOfstudent,
        'infoteachers'      => $infoteachers,
        'academic_session'  => $this->academic_session,
        'allSessions'       => $allSessions,
        'selectedSessionId' => $selectedSessionId,
        'chartTitle'        => $chartTitle,
        'chartType'         => $chartType,
        'termSessionInfo'   => $loadOverview ? $this->getCurrentTermSession() : null,
        'termInfo'          => $loadOverview ? $this->getCurrentTermInfo() : null,
        'termWeeksInfo'     => $loadOverview ? $this->getCurrentTermWeek() : null,
        'examsInfo'         => $examsInfo,
        'attendance'        => $attendance,
        'totalCollection'   => $loadFinance ? $this->getTotalCollection() : 0,
        'expenseInfoTotal'  => $loadFinance ? $this->getCurrentMonthExpenses() : 0,

        // Class strength data
        'clsArr'  => $classStrength['classes'],
        'stdMArr' => $classStrength['male_counts'],
        'stdFArr' => $classStrength['female_counts'],

        // Fee data
        'prStr'   => $feeData['months'],
        'paidStr' => $feeData['paid'],
        'unpaidStr' => $feeData['unpaid'],

        // Dashboard: pending + stats
        'pendingAttendance' => $pendingAttendance,
        'pendingCount'      => is_array($pendingAttendance) ? count($pendingAttendance) : 0,
        'attendanceDate'    => $today,

        // ========== ADD TEACHER/STAFF ATTENDANCE DATA ==========
        'isTeacher' => $isTeacher,
        'isDirectorOrPrincipal' => $isDirectorOrPrincipal,
        'teacherAttendance' => $teacherAttendance,
        'totalEmployees' => $totalEmployees,
        'presentCount' => $presentCount,
        'checkedOutCount' => $checkedOutCount,
        'lateCount' => $lateCount,
        'absentEmployees' => $absentEmployees,
        'showEmployeeAttendance' => $showEmployeeAttendance,

        // ========== ADD TEACHER CLASS & SUBJECT DATA ==========
        'teacherSections' => $teacherSections,
        'teacherSubjects' => $teacherSubjects,
        'subjectsPerSection' => $subjectsPerSection,
        'classTeacherSections' => $classTeacherSections,
        'todayAttendance' => $todayAttendance,
        'classTeacherMap' => $classTeacherMap,
        'user_id' => $user_id,
        'teacherPendingAttendance' => $teacherPendingAttendance,
        'teacherPendingSectionIds' => $teacherPendingSectionIds,
        'teacherDiaryMissing' => $teacherDiaryMissing,
        'teacherResultsPending' => $teacherResultsPending,
        'teacherQuizOpen' => $teacherQuizOpen,
        // ========== END TEACHER DATA ==========
        'academicOps' => DashboardMetricsService::shouldLoadOperationsBlock()
            ? $this->getAcademicOpsSummary($examsInfo)
            : [],
        'bmiStats' => $bmiStats,
        'healthAlertsCount' => $healthAlertsSummary['count'],
        'recentHealthAlerts' => $healthAlertsSummary['recent'],
        'attendanceRate' => $attendanceRate,
        'attendanceTotal' => $attTotal,
        'feePaidPct' => $feePaidPct,
        'userName' => trim((string) ($this->session->get('first_name') . ' ' . $this->session->get('last_name'))),
    ], $feeSummaryData);

    return view('admin/dashboard', $data);
}


protected function getClassWiseStudentCount(): array
{
    $sessionId = (int) $this->academic_session->session_id;
    $campusId  = (int) $this->campusId;
    $systemId  = (int) $this->schoolInfo->system_id;

    $classes = $this->db->table('classes c')
        ->select('c.class_id, c.class_short_name')
        ->where('c.system_id', $systemId)
        ->orderBy('c.class_id', 'ASC')
        ->get()
        ->getResult();

    $rows = $this->db->table('students s')
        ->select('cs.class_id, LOWER(TRIM(s.gender)) AS gender, COUNT(DISTINCT s.student_id) AS cnt', false)
        ->join('student_class sc', 'sc.student_id = s.student_id')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->where('sc.session_id', $sessionId)
        ->where('sc.status', 1)
        ->where('s.campus_id', $campusId)
        ->where('s.status', 1)
        ->groupBy('cs.class_id, LOWER(TRIM(s.gender))')
        ->get()
        ->getResultArray();

    $byClass = [];
    foreach ($rows as $row) {
        $classId = (int) ($row['class_id'] ?? 0);
        if ($classId <= 0) {
            continue;
        }
        if (! isset($byClass[$classId])) {
            $byClass[$classId] = ['male' => 0, 'female' => 0];
        }
        $gender = (string) ($row['gender'] ?? '');
        $count  = (int) ($row['cnt'] ?? 0);
        if (in_array($gender, ['male', 'm'], true)) {
            $byClass[$classId]['male'] += $count;
        } elseif (in_array($gender, ['female', 'f'], true)) {
            $byClass[$classId]['female'] += $count;
        }
    }

    $labels = [];
    $male   = [];
    $female = [];

    foreach ($classes as $class) {
        $labels[] = $class->class_short_name;
        $male[]   = (int) ($byClass[$class->class_id]['male'] ?? 0);
        $female[] = (int) ($byClass[$class->class_id]['female'] ?? 0);
    }

    return [
        'classes'       => $labels,
        'male_counts'   => $male,
        'female_counts' => $female,
    ];
}


protected function getAcademicSession($sessionId = null)
{
    // If session ID is provided, get that specific session
    if ($sessionId) {
        return $this->db->table('academic_session')
            ->where('session_id', $sessionId)
            ->get()
            ->getRow();
    }

    // Otherwise get current session from session data
    $sessionId = $this->session->get('member_sessionid');

    $systemId = $this->getDashboardSystemId();

    if (!$sessionId) {
        // If no session in session data, get the latest active session
        $today = date('Y-m-d');
        $session = $this->db->table('academic_session')
            ->where('system_id', $systemId)
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->orderBy('session_id', 'DESC')
            ->get()
            ->getRow();

        if ($session) {
            return $session;
        }

        // If no active session, get the latest session
        return $this->db->table('academic_session')
            ->where('system_id', $systemId)
            ->orderBy('session_id', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();
    }

    return $this->db->table('academic_session')
        ->where('session_id', $sessionId)
        ->get()
        ->getRow();
}

protected function getAcademicSessionMonths($sessionId = null)
{
    // If session ID is provided, use that session's months
    if ($sessionId) {
        $session = $this->db->table('academic_session')
            ->where('session_id', $sessionId)
            ->get()
            ->getRow();

        if ($session) {
            $start = new DateTime($session->start_date);
            $end = new DateTime($session->end_date);
            $start->modify('first day of this month');
            $end->modify('first day of this month');

            $months = [];
            $current = clone $start;

            while ($current <= $end) {
                $months[] = $current->format('M Y');
                $current->modify('+1 month');
            }

            // Get the last 12 months of the session (or all if less than 12)
            if (count($months) <= 12) {
                return $months;
            }

            // Return the LAST 12 months (not the first 12)
            return array_slice($months, -12);
        }
    }

    // Default: Get previous 12 months (including current month)
    return $this->getPrevious12Months();
}

/**
 * Get previous 12 months including current month
 * Example: If today is March 2026, returns months from April 2025 to March 2026
 */
protected function getPrevious12Months()
{
    $months = [];

    // Get current month and year
    $currentMonth = (int) date('m');
    $currentYear = (int) date('Y');

    // Generate previous 12 months (oldest to newest)
    for ($i = 11; $i >= 0; $i--) {
        $monthNum = $currentMonth - $i;
        $year = $currentYear;

        if ($monthNum <= 0) {
            $monthNum += 12;
            $year--;
        }

        // Format month name
        $date = DateTime::createFromFormat('Y-m-d', $year . '-' . str_pad($monthNum, 2, '0', STR_PAD_LEFT) . '-01');
        if ($date) {
            $months[] = $date->format('M Y');
        }
    }

    return $months;
}

public function debugMonths()
{
    echo "<pre>";
    echo "=== Current Server Date ===\n";
    echo "Date: " . date('Y-m-d H:i:s') . "\n";
    echo "Month: " . date('M Y') . "\n\n";

    echo "=== getAcademicSessionMonths() output ===\n";
    $months = $this->getAcademicSessionMonths();
    print_r($months);

    echo "\n=== getPrevious12Months() output ===\n";
    $previousMonths = $this->getPrevious12Months();
    print_r($previousMonths);

    echo "</pre>";
    die();
}

protected function getFeeTotalsByMonth(array $feeTypeIds, $sessionId = null)
{
    if (empty($feeTypeIds)) {
        return [];
    }

    // Get months in correct order (oldest to newest)
    $orderedMonths = $this->getAcademicSessionMonths($sessionId);

    if (empty($orderedMonths)) {
        return [];
    }

    // Convert display months to database format (Y-m)
    $dbMonths = [];
    foreach ($orderedMonths as $monthDisplay) {
        $date = DateTime::createFromFormat('M Y', $monthDisplay);
        if ($date) {
            $dbMonths[] = $date->format('Y-m');
        }
    }

    $builder = $this->db->table('fee_chalan fc')
        ->select("
            fc.fee_month,
            SUM(CASE WHEN fc.status = 'paid' THEN fc.amount - fc.discount ELSE 0 END) AS paid_total,
            SUM(CASE WHEN fc.status = 'unpaid' THEN fc.amount - fc.discount ELSE 0 END) AS unpaid_total
        ", false)
        ->join('students s', 's.student_id = fc.student_id', 'inner')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'inner')
        ->whereIn('fc.fee_type_id', $feeTypeIds)
        ->where('s.campus_id', (int) $this->campusId)
        ->whereIn('fc.fee_month', $dbMonths)
        ->where('ft.is_monthly_fee', 1)
        ->groupBy('fc.fee_month')
        ->orderBy('fc.fee_month', 'ASC');

    $results = $builder->get()->getResultArray();

    // Create a map of existing data using Y-m format
    $dataMap = [];
    foreach ($results as $row) {
        $dataMap[$row['fee_month']] = $row;
    }

    // Map data to display months
    $completeResults = [];
    foreach ($orderedMonths as $index => $monthDisplay) {
        $dbMonth = $dbMonths[$index];
        if (isset($dataMap[$dbMonth])) {
            $completeResults[] = [
                'fee_month' => $monthDisplay,
                'paid_total' => (float) $dataMap[$dbMonth]['paid_total'],
                'unpaid_total' => (float) $dataMap[$dbMonth]['unpaid_total']
            ];
        } else {
            $completeResults[] = [
                'fee_month' => $monthDisplay,
                'paid_total' => 0,
                'unpaid_total' => 0
            ];
        }
    }

    return $completeResults;
}

protected function getFeeDataArrays(array $feeTypeIds, $sessionId = null)
{
    $months = [];
    $paid   = [];
    $unpaid = [];

    if (!empty($feeTypeIds)) {
        $results = $this->getFeeTotalsByMonth($feeTypeIds, $sessionId);

        // Create a map of existing data
        $dataMap = [];
        foreach ($results as $row) {
            $dataMap[$row['fee_month']] = $row;
        }

        // Get the months in correct order (oldest to newest)
        $orderedMonths = $this->getAcademicSessionMonths($sessionId);

        // Fill data in the correct order
        foreach ($orderedMonths as $month) {
            $months[] = $month;
            if (isset($dataMap[$month])) {
                $paid[] = (float) $dataMap[$month]['paid_total'];
                $unpaid[] = (float) $dataMap[$month]['unpaid_total'];
            } else {
                $paid[] = 0;
                $unpaid[] = 0;
            }
        }
    }

    return [
        'months' => $months,
        'paid'   => $paid,
        'unpaid' => $unpaid
    ];
}

protected function formatMonthDisplay($yearMonth)
{
    $date = DateTime::createFromFormat('Y-m', $yearMonth);
    if ($date) {
        return $date->format('M Y'); // Returns "Jan 2024", "Feb 2024", etc.
    }
    return $yearMonth;
}
   protected function getMonthlyFeeType()
{
    $query = $this->db->table('fee_type')
        ->where('system_id', $this->schoolInfo->system_id)
        ->where('is_monthly_fee', 1)
        ->limit(1)
        ->get();

    log_message('debug', 'getMonthlyFeeType SQL: ' . $this->db->getLastQuery());

    $row = $query->getRow();
    log_message('debug', 'getMonthlyFeeType Result: ' . print_r($row, true));

    return $row;
}


/**
 * Get previous 12 months from current date
 */
protected function getLast12MonthsFromCurrentDate()
{
    $months = [];
    $currentDate = new DateTime();

    // Start from 11 months ago
    for ($i = 11; $i >= 0; $i--) {
        $date = clone $currentDate;
        $date->modify("-$i months");
        $months[] = $date->format('M Y');
    }

    return $months;
}

/**
 * Get months for a specific academic session (last 12 months of that session)
 */
protected function getSessionMonths($session)
{
    if (!$session) {
        return $this->getLast12MonthsFromCurrentDate();
    }

    $start = new DateTime($session->start_date);
    $end = new DateTime($session->end_date);
    $start->modify('first day of this month');
    $end->modify('first day of this month');

    $months = [];
    $current = clone $start;

    while ($current <= $end) {
        $months[] = $current->format('M Y');
        $current->modify('+1 month');
    }

    // Return last 12 months of the session (or all if less than 12)
    if (count($months) <= 12) {
        return $months;
    }

    return array_slice($months, -12);
}

/**
 * Get fee data for last 12 months from current date
 */
protected function getFeeDataArraysLast12Months(array $feeTypeIds)
{
    $months = $this->getLast12MonthsFromCurrentDate();
    return $this->getFeeDataForMonths($feeTypeIds, $months);
}

/**
 * Get fee data for a specific session
 */
protected function getFeeDataArraysForSession(array $feeTypeIds, $session)
{
    $months = $this->getSessionMonths($session);
    return $this->getFeeDataForMonths($feeTypeIds, $months);
}

/**
 * Get fee data for specific months
 */
protected function getFeeDataForMonths(array $feeTypeIds, array $months)
{
    $paid = [];
    $unpaid = [];

    if (!empty($feeTypeIds) && !empty($months)) {
        // Convert display months to database format (Y-m)
        $dbMonths = [];
        foreach ($months as $monthDisplay) {
            $date = DateTime::createFromFormat('M Y', $monthDisplay);
            if ($date) {
                $dbMonths[] = $date->format('Y-m');
            }
        }

        // Query database for these months
        $builder = $this->db->table('fee_chalan fc')
            ->select("
                fc.fee_month,
                SUM(CASE WHEN fc.status = 'paid' THEN fc.amount - fc.discount ELSE 0 END) AS paid_total,
                SUM(CASE WHEN fc.status = 'unpaid' THEN fc.amount - fc.discount ELSE 0 END) AS unpaid_total
            ", false)
            ->join('students s', 's.student_id = fc.student_id', 'inner')
            ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'inner')
            ->whereIn('fc.fee_type_id', $feeTypeIds)
            ->where('s.campus_id', (int) $this->campusId)
            ->whereIn('fc.fee_month', $dbMonths)
            ->where('ft.is_monthly_fee', 1)
            ->groupBy('fc.fee_month')
            ->orderBy('fc.fee_month', 'ASC');

        $results = $builder->get()->getResultArray();

        // Create a map of existing data
        $dataMap = [];
        foreach ($results as $row) {
            $dataMap[$row['fee_month']] = $row;
        }

        // Fill data in month order
        foreach ($dbMonths as $index => $dbMonth) {
            if (isset($dataMap[$dbMonth])) {
                $paid[] = (float) $dataMap[$dbMonth]['paid_total'];
                $unpaid[] = (float) $dataMap[$dbMonth]['unpaid_total'];
            } else {
                $paid[] = 0;
                $unpaid[] = 0;
            }
        }
    }

    return [
        'months' => $months,
        'paid'   => $paid,
        'unpaid' => $unpaid
    ];
}


/**
 * Whether a published quiz is currently open (matches admin/quizzes index_cards logic).
 */
protected function isQuizOpen(object $quiz): bool
{
    $nowTs = time();

    $toTs = static function ($dt) {
        if (!$dt) {
            return null;
        }
        $ts = strtotime((string) $dt);

        return $ts ?: null;
    };

    $s = $toTs($quiz->start_at ?? null);
    $e = $toTs($quiz->end_at ?? null);

    if (!$s && !$e) {
        return true;
    }

    if (($quiz->start_at ?? null) && ($quiz->end_at ?? null) && (string) $quiz->start_at === (string) $quiz->end_at) {
        return true;
    }

    if (!$s && $e) {
        return $e >= $nowTs;
    }
    if ($s && !$e) {
        return $s <= $nowTs;
    }

    return ($s <= $nowTs && $nowTs <= $e);
}

protected function getAcademicOpsSummary(?object $examsInfo = null): array
{
    $campusId = (int) $this->campusId;
    $today    = date('Y-m-d');

    $summary = [
        'quizOpen'            => 0,
        'quizClosed'          => 0,
        'pendingAudio'        => 0,
        'pendingVideo'        => 0,
        'datesheetExamName'   => null,
        'datesheetRowCount'   => 0,
        'diaryEntriesToday'   => 0,
    ];

    if ($campusId <= 0) {
        return $summary;
    }

    $quizzes = $this->db->table('quizzes q')
        ->select('q.start_at, q.end_at')
        ->join('class_section cs', 'cs.cls_sec_id = q.cls_sec_id', 'inner')
        ->where('cs.campus_id', $campusId)
        ->where('q.is_published', 1)
        ->get()
        ->getResult();

    foreach ($quizzes as $quiz) {
        if ($this->isQuizOpen($quiz)) {
            $summary['quizOpen']++;
        } else {
            $summary['quizClosed']++;
        }
    }

    $summary['pendingAudio'] = (int) $this->db->table('student_audio_recordings ar')
        ->join('students s', 's.student_id = ar.student_id', 'inner')
        ->where('ar.status', 'pending')
        ->where('s.campus_id', $campusId)
        ->countAllResults();

    $summary['pendingVideo'] = (int) $this->db->table('student_video_recordings vr')
        ->join('students s', 's.student_id = vr.student_id', 'inner')
        ->where('vr.status', 'pending')
        ->where('s.campus_id', $campusId)
        ->countAllResults();

    if ($examsInfo && !empty($examsInfo->eid)) {
        $summary['datesheetExamName'] = $examsInfo->exam_name ?? null;
        $summary['datesheetRowCount'] = (int) $this->db->table('datesheet d')
            ->join('class_section cs', 'cs.cls_sec_id = d.cls_sec_id', 'inner')
            ->where('cs.campus_id', $campusId)
            ->where('d.eid', (int) $examsInfo->eid)
            ->countAllResults();
    }

    $summary['diaryEntriesToday'] = (int) $this->db->table('classdairy cd')
        ->join('section_subjects ss', 'ss.sec_sub_id = cd.sec_sub_id', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id', 'inner')
        ->where('cs.campus_id', $campusId)
        ->where('cd.date', $today)
        ->countAllResults();

    return $summary;
}

protected function getFeeSummary(): array
{
    $month    = date('Y-m');
    $campusId = (int) $this->campusId;

    $monthlyFee = $this->db->table('fee_type')
        ->select('fee_type_id, fee_type_name')
        ->where('system_id', $this->getDashboardSystemId())
        ->where('is_monthly_fee', 1)
        ->limit(1)
        ->get()
        ->getRow();

    if (!$monthlyFee) {
        log_message('error', 'No monthly fee type found for getFeeSummary()');
        return [
            'PaidFee_info'          => 0,
            'discountedFee_info'    => 0,
            'RemainingBalance_info' => 0,
            'prjectedFee_info'      => 0,
            'monthlyFee'            => null,
            'monthlyFeeStudentCounts' => [
                'fee_month'       => $month,
                'total_students'  => 0,
                'paid_students'   => 0,
                'unpaid_students' => 0,
            ],
        ];
    }

    $feeTypeId = (int) $monthlyFee->fee_type_id;

    $result = $this->db->table('fee_chalan fc')
        ->select("
            SUM(CASE WHEN fc.status = 'paid'       THEN fc.amount - fc.discount ELSE 0 END) AS paid_fee,
            SUM(CASE WHEN fc.status = 'discounted' THEN fc.amount - fc.discount ELSE 0 END) AS discounted_fee,
            SUM(CASE WHEN fc.status = 'unpaid'     THEN fc.amount - fc.discount ELSE 0 END) AS remaining_balance,
            SUM(fc.amount - fc.discount) AS project_fee
        ", false)
        ->join('students s', 's.student_id = fc.student_id', 'inner')
        ->where('fc.fee_type_id', $feeTypeId)
        ->where('fc.fee_month', $month)
        ->where('s.campus_id', $campusId)
        ->get()
        ->getRow();

    $studentCounts = function_exists('getMonthlyFeeStudentCounts')
        ? getMonthlyFeeStudentCounts($campusId, $month)
        : [
            'fee_month'       => $month,
            'total_students'  => 0,
            'paid_students'   => 0,
            'unpaid_students' => 0,
        ];

    return [
        'PaidFee_info'          => (float) ($result->paid_fee ?? 0),
        'discountedFee_info'    => (float) ($result->discounted_fee ?? 0),
        'RemainingBalance_info' => (float) ($result->remaining_balance ?? 0),
        'prjectedFee_info'      => (float) ($result->project_fee ?? 0),
        'monthlyFee'            => $monthlyFee,
        'monthlyFeeStudentCounts' => $studentCounts,
    ];
}




protected function getFeeDataString(array $feeTypeIds, string $status)
{
    $months = $this->getAcademicSessionMonths();
    $totals = $this->getFeeTotalsByMonth($feeTypeIds, $status);
    $map = array_column($totals, 'total', 'fee_month');

    $data = [];
    foreach ($months as $month) {
        $data[] = $map[$month] ?? 0;
    }
    return implode(', ', $data);
}

protected function getFeeTotalForMonth(array $feeTypeIds, string $month, string $status)
{
    // Safety check: avoid running without FeeTypeIds
    if (empty($feeTypeIds)) {
        log_message('error', 'getFeeTotalForMonth called with EMPTY feeTypeIds. Aborting to prevent large query.');
        return 0;
    }


    // Build query using JOIN instead of IN (SELECT ...)
    $builder = $this->db->table('fee_chalan fc')
        ->select('SUM(fc.amount - fc.discount) AS total', false)
        ->join('students s', 's.student_id = fc.student_id', 'inner')
        ->whereIn('fc.fee_type_id', $feeTypeIds)
        ->where('fc.fee_month', $month)
        ->where('fc.status', $status)
        ->where('s.campus_id', (int) $this->campusId);

    // Log SQL for debugging
    $sql = $builder->getCompiledSelect();
    log_message('debug', 'getFeeTotalForMonth SQL: ' . $sql);

    // Execute and fetch only ONE row
    $result = $builder->get()->getRow();

    // Log result for debugging
    log_message('debug', 'getFeeTotalForMonth Result: ' . print_r($result, true));

    // Return safe numeric value
    return isset($result->total) ? (float) $result->total : 0;
}

protected function getFeeCollectionLast12Months()
{
    $campusId = (int) $this->campusId;

    $query = $this->db->query("
        SELECT
            fc.fee_month,
            DATE_FORMAT(STR_TO_DATE(CONCAT(fc.fee_month, '-01'), '%Y-%m-%d'), '%b %Y') AS month_display,
            SUM(CASE WHEN fc.status = 'paid' THEN fc.amount - fc.discount ELSE 0 END) AS paid_amount,
            SUM(CASE WHEN fc.status = 'unpaid' THEN fc.amount - fc.discount ELSE 0 END) AS unpaid_amount,
            SUM(fc.amount - fc.discount) AS total_amount,
            COUNT(DISTINCT fc.chalan_id) AS total_chalans,
            COUNT(DISTINCT CASE WHEN fc.status = 'paid' THEN fc.chalan_id END) AS paid_chalans
        FROM fee_chalan fc
        INNER JOIN students s ON s.student_id = fc.student_id
        INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
        WHERE fc.fee_month >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 11 MONTH), '%Y-%m')
            AND fc.fee_month <= DATE_FORMAT(NOW(), '%Y-%m')
            AND s.campus_id = ?
            AND ft.is_monthly_fee = 1
        GROUP BY fc.fee_month
        ORDER BY fc.fee_month ASC
    ", [$campusId]);

    return $query->getResultArray();
}

protected function getCurrentMonthFeeSummary()
{
    $campusId = (int) $this->campusId;
    $currentMonth = date('Y-m');

    $query = $this->db->query("
        SELECT
            SUM(CASE WHEN fc.status = 'paid' THEN fc.amount - fc.discount ELSE 0 END) AS paid_amount,
            SUM(CASE WHEN fc.status = 'unpaid' THEN fc.amount - fc.discount ELSE 0 END) AS unpaid_amount,
            SUM(CASE WHEN fc.status = 'discounted' THEN fc.amount - fc.discount ELSE 0 END) AS discounted_amount,
            SUM(fc.amount - fc.discount) AS total_amount,
            COUNT(DISTINCT fc.chalan_id) AS total_chalans,
            COUNT(DISTINCT CASE WHEN fc.status = 'paid' THEN fc.chalan_id END) AS paid_chalans,
            COUNT(DISTINCT CASE WHEN fc.status = 'unpaid' THEN fc.chalan_id END) AS unpaid_chalans
        FROM fee_chalan fc
        INNER JOIN students s ON s.student_id = fc.student_id
        INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
        WHERE fc.fee_month = ?
            AND s.campus_id = ?
            AND ft.is_monthly_fee = 1
    ", [$currentMonth, $campusId]);

    return $query->getRow();
}

protected function getTotalCollection()
{
    $start = date('Y-m-01');
    $end = date('Y-m-t');

    $query = $this->db->table('fee_chalan fc')
        ->select('SUM(fc.amount - fc.discount) as TotalCol')
        ->join('students s', 'fc.student_id = s.student_id')
        ->where('fc.status', 'paid')
        ->where('fc.paid_date >=', $start)
        ->where('fc.paid_date <=', $end)
        ->where('s.campus_id', $this->campusId)
        ->get();

    log_message('debug', 'getTotalCollection SQL: ' . $this->db->getLastQuery());

    $result = $query->getRow();
    log_message('debug', 'getTotalCollection Result: ' . print_r($result, true));

    return $result->TotalCol ?? 0;
}

protected function getCurrentMonthExpenses()
{
    $start = date('Y-m-01');
    $end = date('Y-m-t');

    $query = $this->db->table('expenses')
        ->selectSum('amount', 'totalSum')
        ->where('created_date >=', $start)
        ->where('created_date <=', $end)
        ->where('campus_id', $this->campusId)
        ->get();



    $result = $query->getRow();


    return $result->totalSum ?? 0;
}

    /** @deprecated Use resolveSchoolInfo() via $this->schoolInfo */
    protected function getSchoolInfo()
    {
        return $this->resolveSchoolInfo();
    }

    protected function getStudentCount()
    {
        return $this->db->table('students s')
            ->selectCount('s.student_id', 'studentTotal')
            ->join('student_class sc', 's.student_id = sc.student_id')
            ->where('s.campus_id', $this->campusId)
            ->where('sc.status', 1)
            ->get()
            ->getRow()->studentTotal ?? 0;
    }

    protected function getTeacherCount()
    {
        $campusId = (int) $this->campusId;
        if ($campusId <= 0) {
            return 0;
        }

        $campusBill = $this->db->table('campus_bills')
            ->select('plan_id')
            ->where('status', 1)
            ->where('campus_id', $campusId)
            ->orderBy('campus_expiry', 'DESC')
            ->get()
            ->getRow();
        $planId = (int) ($campusBill->plan_id ?? 0);

        if ($planId <= 0) {
            return 0;
        }

        // Primary mapping: user_roles.roleID -> roles.id (strict teacher role_name_id = 5)
        $primaryRows = $this->db->table('user_roles ur')
            ->distinct()
            ->select('ur.userID')
            ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . $planId, 'inner')
            ->where('r.role_name_id', 5)
            ->get()
            ->getResultArray();

        // Legacy mapping: user_roles.roleID -> roles.role_name_id (strict role_name_id = 5)
        $legacyRows = $this->db->table('user_roles ur')
            ->distinct()
            ->select('ur.userID')
            ->join('roles r', 'r.role_name_id = ur.roleID AND r.plan_id = ' . $planId, 'inner')
            ->where('r.role_name_id', 5)
            ->get()
            ->getResultArray();

        $teacherUserIds = [];
        foreach (array_merge($primaryRows, $legacyRows) as $row) {
            $uid = (int) ($row['userID'] ?? 0);
            if ($uid > 0) {
                $teacherUserIds[$uid] = $uid;
            }
        }

        if (empty($teacherUserIds)) {
            return 0;
        }

        return (int) $this->db->table('users')
            ->where('status', 1)
            ->where('campus_id', $campusId)
            ->whereIn('id', array_values($teacherUserIds))
            ->countAllResults();
    }


   protected function getCurrentTermSession()
{
    $today = date('Y-m-d');
    $systemId = $this->schoolInfo->system_id ?? 1;

    return $this->db->table('terms_session')
        ->where('system_id', $systemId)
        ->where('start_date <=', $today)
        ->where('end_date >=', $today)
        ->orderBy('term_session_id', 'DESC')
        ->limit(1)
        ->get()
        ->getRow();
}

protected function getCurrentTermInfo()
{
    $termSession = $this->getCurrentTermSession();

    if (!$termSession || empty($termSession->term_id)) {
        return null;
    }

    return $this->db->table('terms')
        ->where('term_id', $termSession->term_id)
        ->get()
        ->getRow();
}

protected function getCurrentTermWeek()
{
    $today = date('Y-m-d');
    $systemId = $this->schoolInfo->system_id ?? 1;

    return $this->db->table('term_weeks')
        ->where('system_id', $systemId)
        ->where('start_date <=', $today)
        ->where('end_date >=', $today)
        ->orderBy('term_weeks_id', 'DESC')
        ->limit(1)
        ->get()
        ->getRow();
}

protected function getCurrentExam()
{
    $today = date('Y-m-d');

    return $this->db->table('exam')
        ->where('campus_id', $this->campusId)
        ->where('exam_start_date <=', $today)
        ->where('exam_end_date >=', $today)
        ->orderBy('exam_start_date', 'DESC')
        ->limit(1)
        ->get()
        ->getRow();
}




    protected function getTodaysAttendance()
    {
        $today = date('Y-m-d');
        $attendance = $this->db->table('attendance a')
            ->select("
                SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as absent_total,
                SUM(CASE WHEN a.status = 'P' THEN 1 ELSE 0 END) as present_total,
                SUM(CASE WHEN a.status = 'L' THEN 1 ELSE 0 END) as leaves_total
            ")
            ->join('students s', 'a.student_id = s.student_id')
            ->where('a.date', $today)
            ->where('s.status', '1')
            ->where('s.campus_id', $this->campusId)
            ->get()
            ->getRow();

        return [
            'absent'  => $attendance->absent_total ?? 0,
            'present' => $attendance->present_total ?? 0,
            'leaves'  => $attendance->leaves_total ?? 0
        ];
    }


    protected function getMonthlyFeeTypeIds()
    {
        $result = $this->db->table('fee_type')
            ->select('fee_type_id')
            ->where('system_id', $this->schoolInfo->system_id)
            ->where('is_monthly_fee', 1)
            ->get()
            ->getResultArray();

        return array_column($result, 'fee_type_id');
    }

    protected function getBmiStatistics(int $campusId): object
    {
        if ($campusId <= 0) {
            return (object) [
                'total' => 0, 'underweight' => 0, 'normal' => 0,
                'overweight' => 0, 'obese' => 0, 'avg_bmi' => 0,
            ];
        }

        return $this->db->table('students')
            ->select('
                COUNT(*) as total,
                SUM(CASE WHEN bmi_category = "underweight" THEN 1 ELSE 0 END) as underweight,
                SUM(CASE WHEN bmi_category = "normal" THEN 1 ELSE 0 END) as normal,
                SUM(CASE WHEN bmi_category = "overweight" THEN 1 ELSE 0 END) as overweight,
                SUM(CASE WHEN bmi_category = "obese" THEN 1 ELSE 0 END) as obese,
                AVG(bmi) as avg_bmi
            ', false)
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->where('bmi IS NOT NULL')
            ->get()
            ->getRow() ?? (object) [
                'total' => 0, 'underweight' => 0, 'normal' => 0,
                'overweight' => 0, 'obese' => 0, 'avg_bmi' => 0,
            ];
    }

    protected function getHealthAlertsSummary(int $campusId): array
    {
        if ($campusId <= 0) {
            return ['count' => 0, 'recent' => []];
        }

        $count = (int) $this->db->table('health_alerts ha')
            ->join('students s', 's.student_id = ha.student_id')
            ->where('s.campus_id', $campusId)
            ->where('ha.is_read', 0)
            ->countAllResults();

        $recent = $this->db->table('health_alerts ha')
            ->select('ha.alert_type, ha.created_date, CONCAT(s.first_name, " ", s.last_name) as student_name')
            ->join('students s', 's.student_id = ha.student_id')
            ->where('s.campus_id', $campusId)
            ->where('ha.is_read', 0)
            ->orderBy('ha.created_date', 'DESC')
            ->limit(5)
            ->get()
            ->getResult();

        return ['count' => $count, 'recent' => $recent];
    }
}
