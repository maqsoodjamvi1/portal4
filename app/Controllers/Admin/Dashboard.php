<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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
        
        // Check user role using role IDs
        $userRoleIds = currentUserRoles(); // This returns role IDs like [5], [1], etc.
        
        // Check if user is teacher (role ID = 5)
        $isTeacher = in_array(5, $userRoleIds);
        
        // Check if user is director/principal (any role other than teacher)
        $isDirectorOrPrincipal = !$isTeacher && !empty($userRoleIds);
        
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
        helper(['auth']);
        $this->db = Database::connect();
        $this->session = Services::session();
        $this->campusId = $this->session->get('member_campusid');
        $this->sessionId = $this->session->get('member_sessionid');
        $this->schoolInfo = $this->getSchoolInfo();
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

public function index()
{
    // Get current academic session (for other dashboard data)
    $this->academic_session = $this->getAcademicSession();
    
    if (empty($this->academic_session) || empty($this->academic_session->session_id)) {
        return view('admin/dashboard', [
            'error' => 'No valid academic session found. Please check system settings.'
        ]);
    }

    // ========== CHECK USER ROLE USING ROLE IDS ==========
    $userRoleIds = currentUserRoles(); // This returns role IDs like [5], [1,5], etc.
    
    // Check if user is teacher (role ID = 5)
    $isTeacher = in_array(5, $userRoleIds);
    
    // Check if user is director/principal (any role that is not teacher)
    $isDirectorOrPrincipal = !$isTeacher && !empty($userRoleIds);
    
    $campus_id = (int) session('member_campusid');
    $user_id = (int) session('member_userid');
    $today = date('Y-m-d');
    
    // For debugging (remove in production)
    log_message('debug', 'Dashboard - User Role IDs: ' . print_r($userRoleIds, true));
    log_message('debug', 'Dashboard - Is Teacher (role ID 5): ' . ($isTeacher ? 'Yes' : 'No'));
    log_message('debug', 'Dashboard - Is Director/Principal: ' . ($isDirectorOrPrincipal ? 'Yes' : 'No'));
    // ========== END ROLE CHECK ==========

    // ============================================
    // TEACHER SPECIFIC DATA: CLASS SECTIONS & SUBJECTS
    // ============================================
    $teacherSections = [];
    $teacherSubjects = [];
    $subjectsPerSection = [];
    $classTeacherSections = [];
    $todayAttendance = null;
    
    if ($isTeacher && $user_id) {
        
        // 1. Get Class Sections where teacher is Class Teacher (from teacher_section table)
       // Get Class Sections where teacher is Class Teacher (from teacher_section table)
// Get Class Sections with Class Teacher Names (for all sections, not just current user)
$classTeacherSections = $this->db->table('teacher_section ts')
    ->select('cs.cls_sec_id, cs.class_id, cs.section_id, c.class_name, c.class_short_name, s.section_name, s.short_name as section_short_name, CONCAT(u.first_name, " ", u.last_name) as teacher_name, u.id as teacher_id')
    ->join('class_section cs', 'cs.cls_sec_id = ts.cls_sec_id')
    ->join('classes c', 'c.class_id = cs.class_id')
    ->join('sections s', 's.section_id = cs.section_id')
    ->join('users u', 'u.id = ts.tid')
    ->where('ts.status', 1)
    ->where('cs.campus_id', $campus_id)
    ->where('cs.status', 1)
    ->groupBy('cs.cls_sec_id')
    ->orderBy('c.class_id', 'ASC')
    ->get()
    ->getResult();
        // 2. Get Class Sections where teacher teaches ANY subject (via teacher_subjects)
        $teacherSubjectSections = $this->db->query("
            SELECT DISTINCT 
                cs.cls_sec_id, 
                cs.class_id, 
                cs.section_id, 
                c.class_name, 
                c.class_short_name, 
                s.section_name, 
                s.short_name as section_short_name
            FROM teacher_subjects ts
            INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
            INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
            INNER JOIN classes c ON c.class_id = cs.class_id
            INNER JOIN sections s ON s.section_id = cs.section_id
            WHERE ts.tid = ?
                AND cs.campus_id = ?
                AND ts.status = 1
                AND ss.status = 1
                AND cs.status = 1
                AND c.status = 1
                AND s.status = 1
            ORDER BY c.class_id ASC, s.section_id ASC
        ", [$user_id, $campus_id])->getResult();
        
        // Merge both sets of sections (remove duplicates)
       $allTeacherSections = [];
$sectionIds = [];

// Add class teacher sections
foreach ($classTeacherSections as $section) {
    if (!in_array($section->cls_sec_id, $sectionIds)) {
        $sectionIds[] = $section->cls_sec_id;
        $allTeacherSections[] = $section;
    }
}

// Add subject teacher sections
foreach ($teacherSubjectSections as $section) {
    if (!in_array($section->cls_sec_id, $sectionIds)) {
        $sectionIds[] = $section->cls_sec_id;
        $allTeacherSections[] = $section;
    }
}

// ========== ADD THIS SORTING CODE HERE ==========
// Sort by class_id in ascending order (G3, G4, G5, G6...)
usort($allTeacherSections, function($a, $b) {
    return $a->class_id - $b->class_id;
});
// ================================================

$teacherSections = $allTeacherSections;
        
        // 3. Get Subjects taught by teacher (distinct)
        $teacherSubjects = $this->db->query("
            SELECT DISTINCT 
                a.sid, 
                a.subject_name, 
                a.subject_short_name
            FROM teacher_subjects ts
            INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
            INNER JOIN allsubject a ON a.sid = ss.subject_id
            WHERE ts.tid = ?
                AND ts.status = 1
                AND ss.status = 1
                AND a.status = 1
            ORDER BY a.subject_name ASC
        ", [$user_id])->getResult();
        
        // 4. Get subjects per section (for display)
        foreach ($teacherSections as $section) {
            $sectionSubjects = $this->db->query("
                SELECT a.sid, a.subject_name, a.subject_short_name
                FROM teacher_subjects ts
                INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                INNER JOIN allsubject a ON a.sid = ss.subject_id
                WHERE ts.tid = ?
                    AND ss.cls_sec_id = ?
                    AND ts.status = 1
                    AND ss.status = 1
                ORDER BY a.subject_name ASC
            ", [$user_id, $section->cls_sec_id])->getResult();
            
            $subjectsPerSection[$section->cls_sec_id] = $sectionSubjects;
        }
        
        // 5. Get teacher's today's attendance status
        $todayAttendance = $this->db->table('attendance_employee')
            ->where('emp_id', $user_id)
            ->where('date', $today)
            ->get()
            ->getRow();
    }

    // Get selected session from URL parameter
    $selectedSessionId = $this->request->getGet('session_id');
    
    // Determine which session to use for fee collection chart
    $useSessionForChart = null;
    if ($selectedSessionId) {
        $useSessionForChart = $this->db->table('academic_session')
            ->where('session_id', $selectedSessionId)
            ->get()
            ->getRow();
    }
    
    // Get all sessions for dropdown
    $allSessions = $this->db->table('academic_session')
        ->where('system_id', 1)
        ->orderBy('start_date', 'DESC')
        ->get()
        ->getResult();

    // Get all monthly fee type IDs
    $feeTypeRows = $this->db->table('fee_type')
        ->select('fee_type_id')
        ->where('system_id', 1)
        ->where('is_monthly_fee', 1)
        ->get()
        ->getResultArray();

    $feeTypeIds = array_column($feeTypeRows, 'fee_type_id');
    
    // Get fee data based on selection
    if ($useSessionForChart) {
        // If session selected, use session months
        $feeData = $this->getFeeDataArraysForSession($feeTypeIds, $useSessionForChart);
        $chartTitle = $useSessionForChart->session_name;
        $chartType = 'session';
    } else {
        // Default: show previous 12 months from current date
        $feeData = $this->getFeeDataArraysLast12Months($feeTypeIds);
        $chartTitle = 'Last 12 Months';
        $chartType = 'default';
    }
    
    $feeSummaryData = $this->getFeeSummary();
    $classStrength  = $this->getClassWiseStudentCount();
    $attendance = $this->getTodaysAttendance();

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
        // For directors/principals: Get ALL employees attendance for today
        $teacherAttendance = $this->db->table('attendance_employee a')
            ->select('a.*, u.first_name, u.last_name, u.designation, u.photo')
            ->join('users u', 'a.emp_id = u.id')
            ->where('a.date', $today)
            ->where('u.campus_id', $campus_id)
            ->where('u.status', 1)
            ->orderBy('a.checkin', 'ASC')
            ->get()
            ->getResult();
        
        // Get total employees count
        $totalEmployees = $this->db->table('users')
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->countAllResults();
        
        // Get present count
        $presentCount = count($teacherAttendance);
        
        // Get checked out count
        $checkedOutCount = $this->db->table('attendance_employee')
            ->where('date', $today)
            ->where('checkout IS NOT NULL')
            ->countAllResults();
        
        // Get late arrivals
        $lateCount = $this->db->table('attendance_employee')
            ->where('date', $today)
            ->where('status', 'late')
            ->countAllResults();
        
        // Get absent employees (active employees with no attendance today)
        $presentEmployeeIds = array_column($teacherAttendance, 'emp_id');
        if (!empty($presentEmployeeIds)) {
            $absentEmployees = $this->db->table('users')
                ->select('id, first_name, last_name, designation')
                ->where('campus_id', $campus_id)
                ->where('status', 1)
                ->whereNotIn('id', $presentEmployeeIds)
                ->orderBy('first_name', 'ASC')
                ->get()
                ->getResult();
        } else {
            $absentEmployees = $this->db->table('users')
                ->select('id, first_name, last_name, designation')
                ->where('campus_id', $campus_id)
                ->where('status', 1)
                ->orderBy('first_name', 'ASC')
                ->get()
                ->getResult();
        }
        
        $showEmployeeAttendance = true;
        
    } else if ($isTeacher) {
        // For regular teachers (role ID 5): Get their own attendance history only
        $teacherAttendance = $this->db->table('attendance_employee')
            ->where('emp_id', $user_id)
            ->orderBy('date', 'DESC')
            ->limit(10)
            ->get()
            ->getResult();
        
        $showEmployeeAttendance = false;
    }
    // ========== END ATTENDANCE DATA ==========

    $session_id = (int) session('member_sessionid');

    // Subquery: strength per section
    $strengthSub = $this->db->table('student_class sc')
        ->select('sc.cls_sec_id, COUNT(sc.student_id) AS strength', false)
        ->join('students st', 'st.student_id = sc.student_id', 'inner')
        ->where('sc.session_id', $session_id)
        ->where('st.campus_id',  $campus_id)
        ->where('st.status',     1)
        ->groupBy('sc.cls_sec_id')
        ->getCompiledSelect();

    // Subquery: attendance counts per section for today
    $attSub = $this->db->table('attendance a')
        ->select("
            sc.cls_sec_id,
            SUM(CASE WHEN a.status = 'P' THEN 1 ELSE 0 END) AS present_count,
            SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) AS absent_count,
            SUM(CASE WHEN a.status = 'L' THEN 1 ELSE 0 END) AS leave_count,
            SUM(CASE WHEN a.el_duration > 0 THEN 1 ELSE 0 END) AS el_count,
            SUM(CASE WHEN a.lc_duration > 0 THEN 1 ELSE 0 END) AS lc_count
        ", false)
        ->join('student_class sc', 'sc.student_id = a.student_id', 'inner')
        ->where('a.date',        $today)
        ->where('sc.session_id', $session_id)
        ->groupBy('sc.cls_sec_id')
        ->getCompiledSelect();

    // Subquery: one teacher name per section
    $teacherSub = $this->db->table('teacher_section ts')
        ->select("ts.cls_sec_id, MIN(CONCAT(u.first_name, ' ', u.last_name)) AS teacher_name", false)
        ->join('users u', 'u.id = ts.tid', 'inner')
        ->where('ts.status', 1)
        ->groupBy('ts.cls_sec_id')
        ->getCompiledSelect();

    $pendingAttendance = $this->db->table('mark_attendance ma')
        ->select("
            ma.cls_sec_id,
            c.class_name,
            s.section_name,
            tsub.teacher_name,
            ma.status,
            COALESCE(ss.strength, 0)        AS strength,
            COALESCE(att.present_count, 0)  AS present_count,
            COALESCE(att.absent_count, 0)   AS absent_count,
            COALESCE(att.leave_count, 0)    AS leave_count,
            COALESCE(att.el_count, 0)       AS el_count,
            COALESCE(att.lc_count, 0)       AS lc_count
        ", false)
        ->join('class_section cs', 'cs.cls_sec_id = ma.cls_sec_id', 'inner')
        ->join('classes c',       'c.class_id    = cs.class_id',   'left')
        ->join('sections s',      's.section_id  = cs.section_id', 'left')
        ->join("({$teacherSub}) tsub", 'tsub.cls_sec_id = ma.cls_sec_id', 'left')
        ->join("({$strengthSub}) ss",  'ss.cls_sec_id  = ma.cls_sec_id', 'left')
        ->join("({$attSub}) att",      'att.cls_sec_id = ma.cls_sec_id', 'left')
        ->where('cs.campus_id',  $campus_id)
        ->where('cs.status',     1)
        ->where('ma.date',       $today)
        ->where('ma.status',     'pending')
        ->groupBy('ma.cls_sec_id, c.class_name, s.section_name, tsub.teacher_name, ma.status, ss.strength, att.present_count, att.absent_count, att.leave_count, att.el_count, att.lc_count')
        ->orderBy('c.class_name', 'ASC')
        ->orderBy('s.section_name', 'ASC')
        ->get()
        ->getResultArray();

    $data = array_merge([
        'noOfstudent'       => $this->getStudentCount(),
        'infoteachers'      => $this->getTeacherCount(),
        'academic_session'  => $this->academic_session,
        'allSessions'       => $allSessions,
        'selectedSessionId' => $selectedSessionId,
        'chartTitle'        => $chartTitle,
        'chartType'         => $chartType,
        'termSessionInfo'   => $this->getCurrentTermSession(),
        'termInfo'          => $this->getCurrentTermInfo(),
        'termWeeksInfo'     => $this->getCurrentTermWeek(),
        'examsInfo'         => $this->getCurrentExam(),
        'attendance'        => $attendance,
        'totalCollection'   => $this->getTotalCollection(),
        'expenseInfoTotal'  => $this->getCurrentMonthExpenses(),
        
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
        // ========== END TEACHER DATA ==========
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

    $labels = [];
    $male   = [];
    $female = [];

    foreach ($classes as $class) {
        // Use joins instead of IN (SELECT …)
        $maleCount = $this->db->table('students s')
            ->join('student_class sc', 'sc.student_id = s.student_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where('cs.class_id',    $class->class_id)
            ->where('sc.session_id',  $sessionId)
            ->where('s.campus_id',    $campusId)
            ->where('s.status',       1)
            ->where('s.gender',       'Male')
            ->countAllResults();

        $femaleCount = $this->db->table('students s')
            ->join('student_class sc', 'sc.student_id = s.student_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where('cs.class_id',    $class->class_id)
            ->where('sc.session_id',  $sessionId)
            ->where('s.campus_id',    $campusId)
            ->where('s.status',       1)
            ->where('s.gender',       'Female')
            ->countAllResults();

        $labels[] = $class->class_short_name;
        $male[]   = (int) $maleCount;
        $female[] = (int) $femaleCount;
    }

    return [
        'classes'      => $labels,
        'male_counts'  => $male,
        'female_counts'=> $female,
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
    
    if (!$sessionId) {
        // If no session in session data, get the latest active session
        $today = date('Y-m-d');
        $session = $this->db->table('academic_session')
            ->where('system_id', 1)
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
            ->where('system_id', 1)
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


protected function getFeeSummary(): array
{
    $month    = date('Y-m');
    $campusId = (int) $this->campusId;

    $monthlyFee = $this->db->table('fee_type')
        ->select('fee_type_id, fee_type_name')
        ->where('system_id', 1)
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
            'monthlyFee'            => null
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

    return [
        'PaidFee_info'          => (float) ($result->paid_fee ?? 0),
        'discountedFee_info'    => (float) ($result->discounted_fee ?? 0),
        'RemainingBalance_info' => (float) ($result->remaining_balance ?? 0),
        'prjectedFee_info'      => (float) ($result->project_fee ?? 0),
        'monthlyFee'            => $monthlyFee
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

    protected function getSchoolInfo()
    {
        // Implement your getSchoolInfo logic here
        return (object) ['system_id' => 1]; // Example implementation
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
        return $this->db->table('users u')
            ->selectCount('u.id', 'totalTeachers')
            ->join('user_roles ur', 'u.id = ur.userID')
            ->where('ur.roleID', 5)
            ->where('u.status', 1)
            ->where('u.campus_id', $this->campusId)
            ->get()
            ->getRow()->totalTeachers ?? 0;
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

    

   


 }