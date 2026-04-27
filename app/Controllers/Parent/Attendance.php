<?php

namespace App\Controllers\Parent;

use App\Controllers\BaseController;

class Attendance extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
    }

   // In your controller method that loads this view
public function index()
{
    // Get parent ID from session
    $parent_id = session('member_id');
    
    // Get all children with their profile photos
    $children = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, c.class_short_name, sec.section_name')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.parent_id', $parent_id)
        ->where('s.status', 1)
        ->orderBy('s.first_name', 'ASC')
        ->get()
        ->getResult();
    
    // Get current session
    $session = $this->db->table('academic_session')
        ->where('status', 1)
        ->orderBy('session_id', 'DESC')
        ->get()
        ->getRow();
    
    $session_id = $session->session_id ?? 0;
    
    return view('parent/attendance_view', [
        'children' => $children,
        'session_id' => $session_id
    ]);
}

    public function view($token = null)
{
    if (!$token) {
        return redirect()->to('/')->with('error', 'Invalid request');
    }
    
    // Verify token
    $tokenRecord = $this->db->table('attendance_share_tokens')
        ->where('token', $token)
        ->get()
        ->getRow();
    
    if (!$tokenRecord) {
        return redirect()->to('/')->with('error', 'Invalid or expired link');
    }
    
    // Check if token is expired
    if ($tokenRecord->expires_at && strtotime($tokenRecord->expires_at) < time()) {
        return redirect()->to('/')->with('error', 'This link has expired. Please request a new one.');
    }
    
    $student_id = $tokenRecord->student_id;
    
    // Get the most recent session
    $session = $this->db->table('academic_session')
        ->orderBy('session_id', 'DESC')
        ->get()
        ->getRow();
    
    $session_id = $session->session_id ?? 0;
    
    // Get student info
    $student = $this->db->table('students')
        ->where('student_id', $student_id)
        ->get()
        ->getRow();
    
    if (!$student) {
        return redirect()->to('/')->with('error', 'Student not found');
    }
    
    // Get student's cls_sec_id
    $studentClass = $this->db->table('student_class')
        ->select('cls_sec_id, session_id')
        ->where('student_id', $student_id)
        ->where('status', 1)
        ->orderBy('session_id', 'DESC')
        ->get()
        ->getRow();
    
    if ($studentClass) {
        $cls_sec_id = $studentClass->cls_sec_id;
        if ($studentClass->session_id) {
            $session_id = $studentClass->session_id;
        }
    } else {
        $cls_sec_id = 0;
    }
    
    // ========== KEY FIX: Get off days ==========
    $offDays = $this->getOffDays($cls_sec_id, $student_id);
    
    // Get siblings - all children with same parent_id
    $children = $this->db->table('students')
        ->select('student_id, first_name, last_name, profile_photo')
        ->where('parent_id', $student->parent_id)
        ->where('status', 1)
        ->orderBy('first_name', 'ASC')
        ->get()
        ->getResult();
    
    // Build children array with class info
    $childrenArray = [];
    foreach ($children as $child) {
        $childClass = $this->db->table('student_class sc')
            ->select('c.class_short_name, sec.section_name')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('sc.student_id', $child->student_id)
            ->where('sc.session_id', $session_id)
            ->where('sc.status', 1)
            ->get()
            ->getRow();
        
        $childObj = new \stdClass();
        $childObj->student_id = $child->student_id;
        $childObj->first_name = $child->first_name;
        $childObj->last_name = $child->last_name;
        $childObj->class_short_name = ($childClass && $childClass->class_short_name) ? $childClass->class_short_name : 'N/A';
        $childObj->section_name = ($childClass && $childClass->section_name) ? $childClass->section_name : '';
        $childObj->profile_photo = $child->profile_photo;
        
        $childrenArray[] = $childObj;
    }
    
    // Get class info for selected student
    $classInfo = $this->db->table('student_class sc')
        ->select('c.class_short_name, sec.section_name')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('sc.student_id', $student_id)
        ->where('sc.session_id', $session_id)
        ->where('sc.status', 1)
        ->get()
        ->getRow();
    
    // Get parent info
    $parent = $this->db->table('parents')
        ->select('f_name')
        ->where('parent_id', $student->parent_id)
        ->get()
        ->getRow();
    
    // Get session dates
    $sessionRecord = $this->db->table('academic_session')
        ->where('session_id', $session_id)
        ->get()
        ->getRow();
    
    if ($sessionRecord) {
        $start_date = $sessionRecord->start_date;
        $end_date = $sessionRecord->end_date;
        if (strtotime($end_date) > time()) {
            $end_date = date('Y-m-d');
        }
    } else {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-30 days'));
    }
    
    // Get attendance records
    $attendance = $this->db->table('attendance')
        ->select('date, status')
        ->where('student_id', $student_id)
        ->where('date >=', $start_date)
        ->where('date <=', $end_date)
        ->orderBy('date', 'ASC')
        ->get()
        ->getResult();
    
    // ========== FIX: Calculate total working days ==========
    // Total working days = COUNT of attendance records (each record = one day of attendance marked)
    $totalWorkingDays = count($attendance);
    
    // Get all dates in range
    $allDates = $this->getDatesInRange($start_date, $end_date);
    
    // Build attendance data (SKIP off days)
    $attendanceData = [];
    $presentCount = 0;
    $absentCount = 0;
    $lateCount = 0;
    $earlyLeaveCount = 0;
    
    $attendanceMap = [];
    foreach ($attendance as $record) {
        $attendanceMap[$record->date] = $record;
    }
    
    foreach ($allDates as $date) {
        $dayOfWeek = date('l', strtotime($date));
        $dayName = $dayOfWeek;
        
        // SKIP off days - don't add to attendance data
        if (in_array($dayName, $offDays)) {
            continue;
        }
        
        $record = $attendanceMap[$date] ?? null;
        
        // Only include dates that have attendance records
        if ($record) {
            $attStatus = strtolower(trim($record->status));
            if ($attStatus === 'present' || $attStatus === 'p') {
                $status = 'P';
                $presentCount++;
            } else if ($attStatus === 'absent' || $attStatus === 'a') {
                $status = 'A';
                $absentCount++;
            } else if ($attStatus === 'late' || $attStatus === 'l') {
                $status = 'L';
                $lateCount++;
            } else if ($attStatus === 'el' || $attStatus === 'early leave') {
                $status = 'EL';
                $earlyLeaveCount++;
            } else {
                $status = strtoupper(substr($attStatus, 0, 2));
            }
            
            $attendanceData[] = [
                'date' => $date,
                'date_formatted' => date('d M Y', strtotime($date)),
                'day_name' => substr($dayOfWeek, 0, 3),
                'status' => $status
            ];
        }
        // Skip dates without attendance records (they are not counted as working days)
    }
    
    // Handle class section
    if ($classInfo && $classInfo->class_short_name) {
        $classSection = $classInfo->class_short_name . ($classInfo->section_name ? ' - ' . $classInfo->section_name : '');
    } else {
        $classSection = 'Class not assigned';
    }
    
    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
    
    // Total working days is now the count of attendance records
    $attendanceRate = $totalWorkingDays > 0 ? round(($presentCount / $totalWorkingDays) * 100, 1) : 0;
    
    return view('parent/attendance_shared', [
        'student' => $student,
        'student_name' => $studentName,
        'class_section' => $classSection,
        'parent_name' => $parent->f_name ?? 'Parent',
        'attendance_data' => $attendanceData,
        'summary' => [
            'total_days' => $totalWorkingDays,  // Now correctly counts attendance records
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'early_leave_count' => $earlyLeaveCount,
            'attendance_rate' => $attendanceRate
        ],
        'children' => $childrenArray,
        'session_id' => $session_id
    ]);
}

public function d_debugTimings($student_id = null)
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    if (!$student_id) {
        return 'No student ID provided';
    }
    
    // Get student's campus_id
    $student = $this->db->table('students')
        ->select('campus_id')
        ->where('student_id', $student_id)
        ->get()
        ->getRow();
    
    $campus_id = $student->campus_id ?? 0;
    
    // Get the most recent session
    $session = $this->db->table('academic_session')
        ->orderBy('session_id', 'DESC')
        ->get()
        ->getRow();
    
    $session_id = $session->session_id ?? 0;
    
    // Get student's cls_sec_id from ANY session
    $studentClass = $this->db->table('student_class')
        ->select('cls_sec_id, session_id')
        ->where('student_id', $student_id)
        ->where('status', 1)
        ->orderBy('session_id', 'DESC')
        ->get()
        ->getRow();
    
    $cls_sec_id = $studentClass->cls_sec_id ?? 0;
    $found_session_id = $studentClass->session_id ?? 0;
    
    echo "<pre>";
    echo "Student ID: $student_id\n";
    echo "Campus ID: $campus_id\n";
    echo "Session ID (from academic_session): $session_id\n";
    echo "Session ID (from student_class): $found_session_id\n";
    echo "cls_sec_id: $cls_sec_id\n\n";
    
    // Check if student has any class section at all
    $allClasses = $this->db->table('student_class')
        ->select('cls_sec_id, session_id, status')
        ->where('student_id', $student_id)
        ->get()
        ->getResult();
    
    echo "All student_class records:\n";
    foreach ($allClasses as $sc) {
        echo "  cls_sec_id: {$sc->cls_sec_id}, session_id: {$sc->session_id}, status: {$sc->status}\n";
    }
    
    if (!$cls_sec_id) {
        echo "\n!!! No class section found for this student !!!\n";
        echo "Please ensure the student is enrolled in a class for the current session.\n";
        echo "</pre>";
        exit;
    }
    
    // Get active timing type
    $activeType = $this->db->table('school_timing_types')
        ->select('type_id, type_name')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->get()
        ->getRow();
    
    echo "Active Timing Type: " . ($activeType ? $activeType->type_name . " (ID: {$activeType->type_id})" : 'None found') . "\n\n";
    
    // Get timings for this section
    $timings = $this->db->table('school_timings')
        ->select('dayname, checkin_timing, checkout_timing')
        ->where('cls_sec_id', $cls_sec_id);
    
    if ($activeType) {
        $timings->where('type_id', $activeType->type_id);
    }
    
    $timingsResult = $timings->get()->getResult();
    
    echo "School Timings for cls_sec_id = $cls_sec_id:\n";
    echo "----------------------------------------\n";
    
    if (empty($timingsResult)) {
        echo "No timings found for this class section!\n";
        echo "Please add timings to school_timings table for cls_sec_id = $cls_sec_id\n";
    } else {
        $offDays = [];
        $workingDays = [];
        
        foreach ($timingsResult as $timing) {
            $isOff = ($timing->checkin_timing === $timing->checkout_timing);
            $status = $isOff ? 'OFF (Same time)' : 'WORKING';
            echo "{$timing->dayname}: {$timing->checkin_timing} - {$timing->checkout_timing} [$status]\n";
            
            if ($isOff) {
                $offDays[] = $timing->dayname;
            } else {
                $workingDays[] = $timing->dayname;
            }
        }
        
        echo "\nWorking Days: " . (empty($workingDays) ? 'none' : implode(', ', $workingDays));
        echo "\nOff Days: " . (empty($offDays) ? 'none' : implode(', ', $offDays));
    }
    
    echo "</pre>";
    exit;
}


public function d_debugChildren($token = null)
{
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    if (!$token) {
        return 'No token provided';
    }
    
    $tokenRecord = $this->db->table('attendance_share_tokens')
        ->where('token', $token)
        ->get()
        ->getRow();
    
    if (!$tokenRecord) {
        return 'Token not found';
    }
    
    $student_id = $tokenRecord->student_id;
    $session_id = $this->getCurrentSessionId();
    
    // Get student
    $student = $this->db->table('students')
        ->where('student_id', $student_id)
        ->get()
        ->getRow();
    
    if (!$student) {
        return 'Student not found';
    }
    
    // Get children - SIMPLE QUERY
    $children = $this->db->table('students')
        ->where('parent_id', $student->parent_id)
        ->where('status', 1)
        ->get()
        ->getResult();
    
    // Return as plain text for debugging
    echo "<pre>";
    echo "Student ID: " . $student_id . "\n";
    echo "Parent ID: " . $student->parent_id . "\n";
    echo "Session ID: " . $session_id . "\n";
    echo "Children Count: " . count($children) . "\n";
    echo "Children: \n";
    print_r($children);
    echo "</pre>";
    exit;
}


public function d_debugOffDays($student_id)
{
    $session_id = $this->getCurrentSessionId();
    
    // Get student's cls_sec_id
    $studentClass = $this->db->table('student_class')
        ->select('cls_sec_id')
        ->where('student_id', $student_id)
        ->where('session_id', $session_id)
        ->where('status', 1)
        ->get()
        ->getRow();
    
    $cls_sec_id = $studentClass->cls_sec_id ?? 0;
    
    // Get timings
    $campus_id = (int) session('member_campusid');
    $activeType = $this->db->table('school_timing_types')
        ->select('type_id')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRow();
    
    $activeTypeId = $activeType ? $activeType->type_id : null;
    
    $timings = $this->db->table('school_timings')
        ->select('dayname, checkin_timing, checkout_timing')
        ->where('cls_sec_id', $cls_sec_id);
    
    if ($activeTypeId) {
        $timings->where('type_id', $activeTypeId);
    }
    
    $timingsResult = $timings->get()->getResult();
    
    $offDays = [];
    $workingDays = [];
    
    foreach ($timingsResult as $timing) {
        if ($timing->checkin_timing === $timing->checkout_timing) {
            $offDays[] = $timing->dayname;
        } else {
            $workingDays[] = $timing->dayname;
        }
    }
    
    echo "<pre>";
    echo "Student ID: $student_id\n";
    echo "Session ID: $session_id\n";
    echo "cls_sec_id: $cls_sec_id\n";
    echo "Active Type ID: " . ($activeTypeId ?? 'null') . "\n";
    echo "Timings found: " . count($timingsResult) . "\n";
    echo "Working Days (checkin != checkout): " . implode(', ', $workingDays) . "\n";
    echo "Off Days (checkin = checkout): " . implode(', ', $offDays) . "\n";
    echo "</pre>";
    exit;
}
public function getChildAttendance()
{
    $request = $this->request;
    $student_id = $request->getPost('student_id');
    $session_id = $request->getPost('session_id');
    
    $current_date = date('Y-m-d');
    
    // Get session dates
    $session = $this->db->table('academic_session')
        ->where('session_id', $session_id)
        ->get()
        ->getRow();
    
    if ($session) {
        $start_date = $session->start_date;
        $end_date = $session->end_date;
        if (strtotime($end_date) > strtotime($current_date)) {
            $end_date = $current_date;
        }
    } else {
        $end_date = $current_date;
        $start_date = date('Y-m-d', strtotime('-30 days'));
    }
    
    // Get student's cls_sec_id
    $studentClass = $this->db->table('student_class')
        ->select('cls_sec_id')
        ->where('student_id', $student_id)
        ->where('session_id', $session_id)
        ->where('status', 1)
        ->get()
        ->getRow();
    
    $cls_sec_id = $studentClass->cls_sec_id ?? 0;
    
    // Get student info with class and profile photo
    $student = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, c.class_short_name, sec.section_name')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . (int)$session_id . ' AND sc.status = 1', 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.student_id', $student_id)
        ->get()
        ->getRow();
    
    // Get attendance records
    $attendance = $this->db->table('attendance')
        ->select('date, status')
        ->where('student_id', $student_id)
        ->where('date >=', $start_date)
        ->where('date <=', $end_date)
        ->orderBy('date', 'ASC')
        ->get()
        ->getResult();
    
    // Calculate counts
    $totalWorkingDays = count($attendance);
    $presentCount = 0;
    $absentCount = 0;
    $lateCount = 0;
    $earlyLeaveCount = 0;
    $lateComingCount = 0;
    
    $attendanceData = [];
    
    foreach ($attendance as $record) {
        $date = $record->date;
        $dayOfWeek = date('l', strtotime($date));
        $shortDayName = substr($dayOfWeek, 0, 3);
        $attStatus = strtolower(trim($record->status));
        
        if ($attStatus === 'present' || $attStatus === 'p') {
            $status = 'P';
            $presentCount++;
        } else if ($attStatus === 'absent' || $attStatus === 'a') {
            $status = 'A';
            $absentCount++;
        } else if ($attStatus === 'late' || $attStatus === 'l') {
            $status = 'L';
            $lateCount++;
        } else if ($attStatus === 'el' || $attStatus === 'early leave') {
            $status = 'EL';
            $earlyLeaveCount++;
        } else if ($attStatus === 'lc' || $attStatus === 'late coming') {
            $status = 'LC';
            $lateComingCount++;
        } else {
            $status = strtoupper(substr($attStatus, 0, 2));
        }
        
        $attendanceData[] = [
            'date' => $date,
            'date_formatted' => date('d M Y', strtotime($date)),
            'day_name' => $shortDayName,
            'full_day_name' => $dayOfWeek,
            'status' => $status
        ];
    }
    
    // Get valid school days for display
    $validSchoolDays = $this->getValidSchoolDays($cls_sec_id);
    
    $attendanceRate = $totalWorkingDays > 0 ? round(($presentCount / $totalWorkingDays) * 100, 1) : 0;
    
    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'student' => $student,
            'attendance' => $attendanceData,
            'working_days' => $validSchoolDays,
            'summary' => [
                'start_date' => date('d M Y', strtotime($start_date)),
                'end_date' => date('d M Y', strtotime($end_date)),
                'total_days' => $totalWorkingDays,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
                'early_leave_count' => $earlyLeaveCount,
                'late_coming_count' => $lateComingCount,
                'attendance_rate' => $attendanceRate
            ]
        ]
    ]);
}
/**
 * Get valid school days (days with different checkin/checkout times)
 * Used only for display columns, NOT for counting working days
 */
private function getValidSchoolDays($cls_sec_id)
{
    if (!$cls_sec_id) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
    
    // Get active timing type for this campus
    $campus_id = (int) session('member_campusid');
    $activeTimingType = $this->db->table('school_timing_types')
        ->select('type_id')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRow();
    $activeTypeId = $activeTimingType ? $activeTimingType->type_id : null;
    
    // Get school timings for this section
    $timings = $this->db->table('school_timings st')
        ->select('st.dayname, st.checkin_timing, st.checkout_timing')
        ->where('st.cls_sec_id', $cls_sec_id);
    
    if ($activeTypeId) {
        $timings->where('st.type_id', $activeTypeId);
    }
    
    $timings = $timings->get()->getResult();
    
    $validDays = [];
    foreach ($timings as $timing) {
        // Only include days where checkin and checkout are different
        if ($timing->checkin_timing && $timing->checkout_timing) {
            $checkin = (string)$timing->checkin_timing;
            $checkout = (string)$timing->checkout_timing;
            
            if ($checkin !== $checkout) {
                $validDays[] = $timing->dayname;
            }
        }
    }
    
    // If no valid days found, return default Monday-Friday
    if (empty($validDays)) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
    
    return $validDays;
}
/**
 * Get working days for a class section (days where checkin != checkout)
 */
/**
 * Get working days for a class section (days where checkin != checkout)
 */
private function getWorkingDays($cls_sec_id, $student_id = null)
{
    if (!$cls_sec_id) {
        // Default: Monday to Friday are working days
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
    
    // Get campus_id from the student
    $campus_id = null;
    
    if ($student_id) {
        $student = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $student_id)
            ->get()
            ->getRow();
        
        if ($student) {
            $campus_id = $student->campus_id;
        }
    }
    
    if (!$campus_id) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
    
    // Get active timing type
    $activeType = $this->db->table('school_timing_types')
        ->select('type_id')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRow();
    
    $activeTypeId = $activeType ? $activeType->type_id : null;
    
    if (!$activeTypeId) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
    
    // Get timings for this section
    $timings = $this->db->table('school_timings')
        ->select('dayname, checkin_timing, checkout_timing')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('type_id', $activeTypeId)
        ->get()
        ->getResult();
    
    $workingDays = [];
    foreach ($timings as $timing) {
        // If checkin != checkout, it's a working day
        if ($timing->checkin_timing !== $timing->checkout_timing) {
            $workingDays[] = $timing->dayname;
        }
    }
    
    // If no working days found from timings, use default
    if (empty($workingDays)) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
    
    return $workingDays;
}
    private function getDatesInRange($start_date, $end_date)
    {
        $dates = [];
        $current = strtotime($start_date);
        $end = strtotime($end_date);
        
        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }
        
        return $dates;
    }
    private function getCurrentSessionId()
{
    // Get the most recent session (by session_id descending)
    $session = $this->db->table('academic_session')
        ->orderBy('session_id', 'DESC')
        ->get()
        ->getRow();
    
    if ($session) {
        return $session->session_id;
    }
    
    return 0;
}
/**
 * Get off days for a class section based on active timing type
 * Returns days where checkin_time equals checkout_time (permanently off)
 */

/**
 * Get off days for a class section based on active timing type
 * Returns days where checkin_time equals checkout_time (permanently off)
 */
/**
 * Get off days for a class section based on active timing type
 */
/**
 * Get off days for a class section based on active timing type
 * Returns days where checkin_time equals checkout_time (permanently off)
 */
private function getOffDays($cls_sec_id, $student_id = null)
{
    if (!$cls_sec_id) {
        return [];
    }
    
    // Get campus_id from the student
    $campus_id = null;
    
    if ($student_id) {
        $student = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $student_id)
            ->get()
            ->getRow();
        
        if ($student) {
            $campus_id = $student->campus_id;
        }
    }
    
    if (!$campus_id) {
        return [];
    }
    
    // Get active timing type
    $activeType = $this->db->table('school_timing_types')
        ->select('type_id')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRow();
    
    $activeTypeId = $activeType ? $activeType->type_id : null;
    
    if (!$activeTypeId) {
        return [];
    }
    
    // Get timings for this section
    $timings = $this->db->table('school_timings')
        ->select('dayname, checkin_timing, checkout_timing')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('type_id', $activeTypeId)
        ->get()
        ->getResult();
    
    $offDays = [];
    foreach ($timings as $timing) {
        if ($timing->checkin_timing === $timing->checkout_timing) {
            $offDays[] = $timing->dayname;
        }
    }
    
    return $offDays;
}
}