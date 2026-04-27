<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use DateTime;

class AttendanceReport extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
        check_permission('admin-attendance-monthly-report');
    }



public function index()
{
    // Get session data
    $campus_id = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');
    $system_id = (int) getSchoolInfo()->system_id;
    
    $class_sections = [];
    
    try {
        // First, try to get class sections that have students
        $sql = "SELECT DISTINCT cs.cls_sec_id, c.class_name, sec.section_name 
                FROM class_section cs
                INNER JOIN classes c ON c.class_id = cs.class_id
                INNER JOIN sections sec ON sec.section_id = cs.section_id
                INNER JOIN student_class sc ON sc.cls_sec_id = cs.cls_sec_id 
                    AND sc.session_id = ? 
                    AND sc.status = 1
                WHERE cs.campus_id = ? 
                AND cs.status = 1
                ORDER BY c.class_id ASC, sec.section_name ASC";
        
        $result = $this->db->query($sql, [$sessionid, $campus_id]);
        
        if ($result && $result->getNumRows() > 0) {
            $class_sections = $result->getResult();
        }
    } catch (\Exception $e) {
        log_message('error', 'Error getting class sections with students: ' . $e->getMessage());
    }
    
    // If no class sections with students, get all class sections
    if (empty($class_sections)) {
        try {
            $sql2 = "SELECT cs.cls_sec_id, c.class_name, sec.section_name 
                     FROM class_section cs
                     INNER JOIN classes c ON c.class_id = cs.class_id
                     INNER JOIN sections sec ON sec.section_id = cs.section_id
                     WHERE cs.campus_id = ? 
                     AND cs.status = 1
                     ORDER BY c.class_id ASC, sec.section_name ASC";
            
            $result2 = $this->db->query($sql2, [$campus_id]);
            
            if ($result2 && $result2->getNumRows() > 0) {
                $class_sections = $result2->getResult();
            }
        } catch (\Exception $e) {
            log_message('error', 'Error getting all class sections: ' . $e->getMessage());
        }
    }
    
    // If still no class sections, try to get just classes
    if (empty($class_sections)) {
        try {
            $classes = $this->db->table('classes')
                ->where('system_id', $system_id)
                ->where('status', 1)
                ->get()
                ->getResult();
            
            foreach ($classes as $class) {
                $class_sections[] = (object)[
                    'cls_sec_id' => $class->class_id,
                    'class_name' => $class->class_name,
                    'section_name' => ''
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error getting classes: ' . $e->getMessage());
        }
    }
    
    return view('admin/attendance_report', [
        'class_sections' => $class_sections,
        'campusid' => $campus_id,
        'currentSessionId' => $sessionid
    ]);
}

public function workingDaysReport()
{
    check_permission('admin-emp-attendance-monthly-report');
    
    $campus_id = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');
    $current_year = date('Y');
    $current_month = date('m');
    
    // Get all class sections
    $class_sections = $this->getAllClassSectionsWithStudents($campus_id, $sessionid);
    
    // Get available years from attendance data
    $years = $this->db->table('attendance')
        ->select('DISTINCT YEAR(date) as year')
        ->orderBy('year', 'DESC')
        ->get()
        ->getResultArray();
    
    if (empty($years)) {
        $years = [['year' => $current_year]];
    }
    
    $months = $this->getMonthsList();
    
    return view('admin/attendance_working_days_report', [
        'class_sections' => $class_sections,
        'campus_id' => $campus_id,
        'session_id' => $sessionid,
        'current_year' => $current_year,
        'current_month' => $current_month,
        'years' => $years,
        'months' => $months
    ]);
}

/**
 * Get Working Days Report Data via AJAX
 */
public function getWorkingDaysReportData()
{
    $request = $this->request;
    $campus_id = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');
    
    // Get year and month - support both formats
    $year = (int) $request->getPost('year');
    $month = (int) $request->getPost('month');
    $monthYear = $request->getPost('month_year');
    
    // If month_year is provided, parse it (format: "2024-01" or "January 2024")
    if (empty($year) && !empty($monthYear)) {
        if (strpos($monthYear, '-') !== false) {
            // Format: "2024-01"
            $parts = explode('-', $monthYear);
            $year = (int) $parts[0];
            $month = (int) $parts[1];
        } else {
            // Format: "January 2024"
            $date = \DateTime::createFromFormat('F Y', $monthYear);
            if ($date) {
                $year = (int) $date->format('Y');
                $month = (int) $date->format('m');
            }
        }
    }
    
    $cls_sec_id = (int) $request->getPost('cls_sec_id');
    
    if (!$year || !$month) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Please select year and month'
        ]);
    }
    
    $reportData = $this->generateWorkingDaysReport($campus_id, $sessionid, $year, $month, $cls_sec_id);
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $reportData
    ]);
}
/**
 * Generate Working Days Report
 */


/**
 * Check if a day is off based on school timings
 * A day is OFF if checkin_time == checkout_time (same time means no school)
 */
private function isDayOffFromTimings($timings, $dayName)
{
    // If no timing exists for this day, it's OFF
    if (!isset($timings[$dayName])) {
        return true;
    }
    
    $timing = $timings[$dayName];
    
    // If checkin or checkout is null, it's OFF
    if (empty($timing->checkin_timing) || empty($timing->checkout_timing)) {
        return true;
    }
    
    // If checkin and checkout are the same, it's OFF (no school)
    $checkin = (string)$timing->checkin_timing;
    $checkout = (string)$timing->checkout_timing;
    
    return $checkin === $checkout;
}

private function generateWorkingDaysReport($campus_id, $sessionid, $year, $month, $cls_sec_id = 0)
{
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $monthStart = "$year-$month-01";
    $monthEnd = "$year-$month-$daysInMonth";
    
    // Use DateTime for proper date comparison
    $currentDateObj = new DateTime();
    $currentDateObj->setTime(0, 0, 0); // Set to midnight for accurate comparison
    
    // Get all dates in the month
    $dates = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = sprintf("%04d-%02d-%02d", $year, $month, $d);
        $dateObj = new DateTime($date);
        $dateObj->setTime(0, 0, 0);
        
        // Compare dates properly
        $isFuture = $dateObj > $currentDateObj;
        
        $dates[] = [
            'date' => $date,
            'day' => date('l', strtotime($date)),
            'day_num' => $d,
            'is_future' => $isFuture
        ];
    }
    
    // Get active timing type
    $activeTimingType = $this->db->table('school_timing_types')
        ->select('type_id')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRow();
    $activeTypeId = $activeTimingType ? $activeTimingType->type_id : null;
    
    // Get class sections
    if ($cls_sec_id > 0) {
        $sections = $this->getClassSectionById($cls_sec_id);
    } else {
        $sections = $this->getAllClassSectionsWithStudents($campus_id, $sessionid);
    }
    
    $report = [];
    
    foreach ($sections as $section) {
        $cls_sec_id_val = $section->cls_sec_id ?? $section['cls_sec_id'] ?? 0;
        
        if (!$cls_sec_id_val) {
            continue;
        }
        
        // Get total active students in this section for the current session
        $totalStudents = $this->db->table('student_class')
            ->where('cls_sec_id', $cls_sec_id_val)
            ->where('session_id', $sessionid)
            ->where('status', 1)
            ->countAllResults();
        
        if ($totalStudents == 0) {
            continue;
        }
        
        // Get school timings for this section
        $timings = $this->getSectionTimings($cls_sec_id_val, $activeTypeId);
        
        // Get attendance records for the month (only Present status)
        $attendanceRecords = $this->db->table('attendance a')
            ->select('a.date, COUNT(DISTINCT a.student_id) as present_count')
            ->join('student_class sc', 'sc.student_id = a.student_id AND sc.session_id = ' . (int)$sessionid)
            ->where('sc.cls_sec_id', $cls_sec_id_val)
            ->where('sc.status', 1)
            ->where('a.date >=', $monthStart)
            ->where('a.date <=', $monthEnd)
            ->where('a.status', 'P')
            ->groupBy('a.date')
            ->get()
            ->getResultArray();
        
        // Get detailed attendance breakdown (for tooltips)
        $detailedAttendance = $this->db->table('attendance a')
            ->select("
                a.date,
                SUM(CASE WHEN a.status = 'P' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'L' THEN 1 ELSE 0 END) as leave_count,
                SUM(CASE WHEN a.status = 'LC' THEN 1 ELSE 0 END) as late_count
            ")
            ->join('student_class sc', 'sc.student_id = a.student_id AND sc.session_id = ' . (int)$sessionid)
            ->where('sc.cls_sec_id', $cls_sec_id_val)
            ->where('sc.status', 1)
            ->where('a.date >=', $monthStart)
            ->where('a.date <=', $monthEnd)
            ->groupBy('a.date')
            ->get()
            ->getResultArray();
        
        // Create lookup arrays
        $attendanceMap = [];
        foreach ($attendanceRecords as $record) {
            $attendanceMap[$record['date']] = (int)$record['present_count'];
        }
        
        $detailedMap = [];
        foreach ($detailedAttendance as $record) {
            $detailedMap[$record['date']] = [
                'present' => (int)$record['present_count'],
                'absent' => (int)$record['absent_count'],
                'leave' => (int)$record['leave_count'],
                'late' => (int)$record['late_count']
            ];
        }
        
        // Build daily status
        $dailyStatus = [];
        $workingDays = 0;
        $offScheduleDays = 0;
        $offNoRecordDays = 0;
        
        foreach ($dates as $dateInfo) {
            $date = $dateInfo['date'];
            $dayName = $dateInfo['day'];
            $presentCount = $attendanceMap[$date] ?? 0;
            $isFuture = $dateInfo['is_future'];
            
            // Get detailed counts if available
            $absentCount = $detailedMap[$date]['absent'] ?? 0;
            $leaveCount = $detailedMap[$date]['leave'] ?? 0;
            $lateCount = $detailedMap[$date]['late'] ?? 0;
            
            // Calculate attendance percentage
            $attendancePercentage = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;
            
            // Determine if day is OFF based on school timings
            $isOffBySchedule = $this->isDayOffFromTimings($timings, $dayName);
            
            if ($isFuture) {
                $status = 'future';
            } elseif ($isOffBySchedule) {
                $status = 'off_schedule';
                $offScheduleDays++;
            } elseif ($presentCount > 0) {
                $status = 'working';
                $workingDays++;
            } else {
                $status = 'off_no_record';
                $offNoRecordDays++;
            }
            
            $dailyStatus[] = [
                'date' => $date,
                'day' => $dayName,
                'day_num' => $dateInfo['day_num'],
                'status' => $status,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'leave_count' => $leaveCount,
                'late_count' => $lateCount,
                'total_students' => $totalStudents,
                'percentage' => $attendancePercentage,
                'is_future' => $isFuture
            ];
        }
        
        // Calculate attendance rate (percentage of working days with attendance)
        $attendanceRate = $workingDays > 0 ? round(($workingDays / ($workingDays + $offNoRecordDays)) * 100, 1) : 0;
        
        $report[] = [
            'cls_sec_id' => $cls_sec_id_val,
            'class_name' => $section->class_name ?? $section['class_name'] ?? 'N/A',
            'section_name' => $section->section_name ?? $section['section_name'] ?? 'N/A',
            'total_students' => $totalStudents,
            'summary' => [
                'total_days' => $daysInMonth,
                'working_days' => $workingDays,
                'off_schedule_days' => $offScheduleDays,
                'off_no_record_days' => $offNoRecordDays,
                'attendance_rate' => $attendanceRate
            ],
            'daily_status' => $dailyStatus,
            'timings' => $timings
        ];
    }
    
    return [
        'year' => $year,
        'month' => $month,
        'month_name' => date('F', mktime(0, 0, 0, $month, 1, $year)),
        'total_days' => $daysInMonth,
        'sections' => $report
    ];
}

/**
 * Get all class sections with students
 */
private function getAllClassSectionsWithStudents($campus_id, $sessionid)
{
    try {
        $sql = "SELECT DISTINCT cs.cls_sec_id, c.class_name, sec.section_name 
                FROM class_section cs
                INNER JOIN classes c ON c.class_id = cs.class_id
                INNER JOIN sections sec ON sec.section_id = cs.section_id
                INNER JOIN student_class sc ON sc.cls_sec_id = cs.cls_sec_id 
                    AND sc.session_id = ? 
                    AND sc.status = 1
                WHERE cs.campus_id = ? 
                AND cs.status = 1
                ORDER BY c.class_id ASC, sec.section_name ASC";
        
        $result = $this->db->query($sql, [$sessionid, $campus_id]);
        
        if ($result && $result->getNumRows() > 0) {
            return $result->getResult();
        }
    } catch (\Exception $e) {
        log_message('error', 'Error getting class sections: ' . $e->getMessage());
    }
    
    return [];
}

/**
 * Get class section by ID
 */
private function getClassSectionById($cls_sec_id)
{
    $sql = "SELECT cs.cls_sec_id, c.class_name, sec.section_name 
            FROM class_section cs
            INNER JOIN classes c ON c.class_id = cs.class_id
            INNER JOIN sections sec ON sec.section_id = cs.section_id
            WHERE cs.cls_sec_id = ?";
    
    $result = $this->db->query($sql, [$cls_sec_id]);
    
    if ($result && $result->getNumRows() > 0) {
        return $result->getResult();
    }
    
    return [];
}

/**
 * Get section timings
 */



/**
 * Get months list
 */
private function getMonthsList()
{
    return [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
}

/**
 * Export Working Days Report to Excel
 */
public function exportWorkingDaysReport()
{
     $request = $this->request;
    $campus_id = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');
    
    // Get year and month - support both formats
    $year = (int) $request->getPost('year');
    $month = (int) $request->getPost('month');
    $monthYear = $request->getPost('month_year');
    
    // If month_year is provided, parse it
    if (empty($year) && !empty($monthYear)) {
        if (strpos($monthYear, '-') !== false) {
            $parts = explode('-', $monthYear);
            $year = (int) $parts[0];
            $month = (int) $parts[1];
        } else {
            $date = \DateTime::createFromFormat('F Y', $monthYear);
            if ($date) {
                $year = (int) $date->format('Y');
                $month = (int) $date->format('m');
            }
        }
    }
    
    $cls_sec_id = (int) $request->getPost('cls_sec_id');
    
    if (!$year || !$month) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid parameters'
        ]);
    }
    
    $reportData = $this->generateWorkingDaysReport($campus_id, $sessionid, $year, $month, $cls_sec_id);
    // Set headers for CSV download
    $filename = "working_days_report_{$year}_{$month}.csv";
    
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Report Header
    fputcsv($output, ['WORKING DAYS & ATTENDANCE REPORT']);
    fputcsv($output, ['Month:', $reportData['month_name'] . ' ' . $reportData['year']]);
    fputcsv($output, ['Generated On:', date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    foreach ($reportData['sections'] as $section) {
        fputcsv($output, ['']);
        fputcsv($output, ['CLASS: ' . $section['class_name'] . ' - SECTION: ' . $section['section_name']]);
        fputcsv($output, ['Total Students:', $section['total_students']]);
        fputcsv($output, ['Working Days:', $section['summary']['working_days']]);
        fputcsv($output, ['OFF Days:', $section['summary']['off_days']]);
        fputcsv($output, ['Full Attendance Days:', $section['summary']['full_attendance_days']]);
        fputcsv($output, ['Partial Attendance Days:', $section['summary']['partial_attendance_days']]);
        fputcsv($output, ['No Data Days:', $section['summary']['no_data_days']]);
        fputcsv($output, ['Attendance Rate:', $section['summary']['attendance_rate'] . '%']);
        fputcsv($output, []);
        fputcsv($output, ['Date', 'Day', 'Status', 'Present', 'Total', 'Percentage']);
        
        foreach ($section['daily_status'] as $day) {
            $statusText = $this->getWorkingDayStatusText($day['status']);
            fputcsv($output, [
                $day['date'],
                $day['day'],
                $statusText,
                $day['present_count'],
                $day['total_students'],
                $day['percentage'] . '%'
            ]);
        }
        
        fputcsv($output, []);
        fputcsv($output, ['---']);
    }
    
    fclose($output);
    exit();
}

/**
 * Get status text for working days report
 */
private function getWorkingDayStatusText($status)
{
    $map = [
        'full' => 'Full Attendance',
        'partial' => 'Partial Attendance',
        'off' => 'OFF Day',
        'no_data' => 'No Records',
        'future' => 'Future Date',
        'none' => 'No Data'
    ];
    return $map[$status] ?? $status;
}

public function getReportData()
{
    $request = $this->request;
    
    // Get session data
    $campus_id = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');
    
    // Get POST data
    $filter_type = $request->getPost('filter_type');
    $cls_sec_id = $request->getPost('cls_sec_id');
    
    // Get current date
    $current_date = date('Y-m-d');
    
    // Date range variables
    $start_date = null;
    $end_date = null;
    $original_end_date = null;
    $date_note = '';
    
    // Calculate date range based on filter type
    switch ($filter_type) {
        case 'month':
            $month_year = $request->getPost('month_year');
            if ($month_year) {
                $date = \DateTime::createFromFormat('F Y', $month_year);
                if ($date) {
                    $start_date = $date->format('Y-m-01');
                    $original_end_date = $date->format('Y-m-t');
                    $end_date = $original_end_date;
                    
                    // If end date is greater than current date, use current date
                    if (strtotime($end_date) > strtotime($current_date)) {
                        $end_date = $current_date;
                        $date_note = ' (Report shows data from ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($current_date)) . ' as future dates are not available)';
                    }
                }
            }
            break;
            
        case 'current_week':
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $original_end_date = date('Y-m-d', strtotime('sunday this week'));
            $end_date = $original_end_date;
            
            // If end date is greater than current date, use current date
            if (strtotime($end_date) > strtotime($current_date)) {
                $end_date = $current_date;
                $date_note = ' (Report shows data from ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($current_date)) . ' as future dates are not available)';
            }
            break;
            
        case 'current_session':
            // Get session dates from academic_session table
            $session = $this->db->table('academic_session')
                ->where('session_id', $sessionid)
                ->get()
                ->getRow();
            
            if ($session) {
                $start_date = $session->start_date;
                $original_end_date = $session->end_date;
                $end_date = $original_end_date;
                
                // If session end date is greater than current date, use current date
                if (strtotime($end_date) > strtotime($current_date)) {
                    $end_date = $current_date;
                    $date_note = ' (Session ends on ' . date('d M Y', strtotime($original_end_date)) . '. Report shows data from ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($current_date)) . ')';
                }
            } else {
                // Fallback: Get the latest active session
                $session = $this->db->table('academic_session')
                    ->where('status', 1)
                    ->orderBy('session_id', 'DESC')
                    ->get()
                    ->getRow();
                if ($session) {
                    $start_date = $session->start_date;
                    $original_end_date = $session->end_date;
                    $end_date = $original_end_date;
                    $sessionid = $session->session_id;
                    
                    if (strtotime($end_date) > strtotime($current_date)) {
                        $end_date = $current_date;
                        $date_note = ' (Session ends on ' . date('d M Y', strtotime($original_end_date)) . '. Report shows data from ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($current_date)) . ')';
                    }
                }
            }
            break;
            
        case 'custom_range':
            $start_date = $request->getPost('start_date');
            $original_end_date = $request->getPost('end_date');
            $end_date = $original_end_date;
            
            // If end date is greater than current date, use current date
            if ($end_date && strtotime($end_date) > strtotime($current_date)) {
                $end_date = $current_date;
                $date_note = ' (End date adjusted from ' . date('d M Y', strtotime($original_end_date)) . ' to ' . date('d M Y', strtotime($current_date)) . ' as future dates are not available)';
            }
            break;
    }
    
    // Also ensure start date is not greater than end date
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        $start_date = $end_date;
    }
    
    // Validate date range
    if (!$start_date || !$end_date) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid date range selected. Please check your report type and date selection.'
        ]);
    }
    
    // Build query to get students with parent WhatsApp
    $studentQuery = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.parent_id, c.class_short_name, sec.section_name, sc.cls_sec_id, cs.class_id, p.whatsapp, p.f_name as parent_name')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . (int)$sessionid . ' AND sc.status = 1', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', '1');
        
    
    // Apply class section filter
    if (!empty($cls_sec_id) && $cls_sec_id > 0) {
        $studentQuery->where('sc.cls_sec_id', $cls_sec_id);
    }
    
    // Order by class_id
    $studentQuery->orderBy('cs.class_id', 'ASC');
    $studentQuery->orderBy('s.first_name', 'ASC');
    
    $students = $studentQuery->get()->getResult();
    
    // Get attendance data for each student with siblings info
    $studentsWithAttendance = [];
    $processedParents = [];
    
    foreach ($students as $student) {
        $parent_id = $student->parent_id;
        $parentName = !empty($student->parent_name) ? $student->parent_name : 'Parent';
        
        // Get all siblings for this parent (only once per parent)
        if (!isset($processedParents[$parent_id . '_siblings'])) {
            $siblings = $this->db->table('students s')
                ->select('s.student_id, s.first_name, s.last_name, c.class_short_name, sec.section_name')
                ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . (int)$sessionid . ' AND sc.status = 1', 'left')
                ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
                ->join('classes c', 'c.class_id = cs.class_id', 'left')
                ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
                ->where('s.parent_id', $parent_id)
                ->where('s.status', '1')
                
                ->get()
                ->getResult();
            
            $siblingsAttendance = [];
            foreach ($siblings as $sibling) {
                // Get absent dates for sibling
                $absentResult = $this->db->table('attendance')
                    ->select("DATE_FORMAT(date, '%d-%b-%Y') as absent_date")
                    ->where('student_id', $sibling->student_id)
                    ->where('date >=', $start_date)
                    ->where('date <=', $end_date)
                    ->where('status', 'A')
                    ->orderBy('date', 'ASC')
                    ->get()
                    ->getResult();
                
                $absentDates = [];
                foreach ($absentResult as $absent) {
                    $absentDates[] = $absent->absent_date;
                }
                
                $absentCount = count($absentDates);
                
                $classSection = ($sibling->class_short_name ?? '') . ($sibling->section_name ? ' - ' . $sibling->section_name : '');
                
                $siblingsAttendance[] = [
                    'student_id' => $sibling->student_id,
                    'student_name' => trim(($sibling->first_name ?? '') . ' ' . ($sibling->last_name ?? '')),
                    'class_section' => $classSection ?: '-',
                    'absent_count' => $absentCount,
                    'absent_dates' => !empty($absentDates) ? implode(', ', $absentDates) : '-'
                ];
            }
            
            // Sort siblings by absent count (highest first)
            usort($siblingsAttendance, function($a, $b) {
                return $b['absent_count'] - $a['absent_count'];
            });
            
            $processedParents[$parent_id . '_siblings'] = $siblingsAttendance;
        }
        $siblingsAttendance = $processedParents[$parent_id . '_siblings'];
        
        // Get current student's attendance
        $absentResult = $this->db->table('attendance')
            ->select("DATE_FORMAT(date, '%d-%b-%Y') as absent_date")
            ->where('student_id', $student->student_id)
            ->where('date >=', $start_date)
            ->where('date <=', $end_date)
            ->where('status', 'A')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResult();
        
        $absentDates = [];
        foreach ($absentResult as $absent) {
            $absentDates[] = $absent->absent_date;
        }
        
        $absentCount = count($absentDates);
        
        // Create class section display name using class_short_name
        $classSection = ($student->class_short_name ?? '') . ($student->section_name ? ' - ' . $student->section_name : '');
        
        $studentsWithAttendance[] = [
            'student_id' => $student->student_id,
            'first_name' => $student->first_name ?? '',
            'last_name' => $student->last_name ?? '',
            'class_section' => $classSection ?: '-',
            'class_id' => $student->class_id ?? 0,
            'absent_count' => $absentCount,
            'absent_dates' => !empty($absentDates) ? implode(', ', $absentDates) : '-',
            'whatsapp' => $student->whatsapp ?? '',
            'parent_name' => $parentName,
            'siblings' => $siblingsAttendance,
            'parent_id' => $parent_id
        ];
    }
    
    // Calculate total statistics
    $total_students = count($studentsWithAttendance);
    $total_absent_count = array_sum(array_column($studentsWithAttendance, 'absent_count'));
    $students_with_absent = count(array_filter($studentsWithAttendance, function($s) { 
        return $s['absent_count'] > 0; 
    }));
    
    // Calculate working days (only up to current date)
    $working_days = $this->getWorkingDays($campus_id, $start_date, $end_date, $sessionid);
    
    // Format display dates
    $display_start_date = date('d M Y', strtotime($start_date));
    $display_end_date = date('d M Y', strtotime($end_date));
    
    // Add the note to end date display
    if (!empty($date_note)) {
        $display_end_date_with_note = $display_end_date;
    } else {
        $display_end_date_with_note = $display_end_date;
    }
    
    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'students' => $studentsWithAttendance,
            'summary' => [
                'start_date' => $display_start_date,
                'end_date' => $display_end_date,
                'end_date_original' => $original_end_date ? date('d M Y', strtotime($original_end_date)) : null,
                'date_note' => $date_note,
                'working_days' => $working_days,
                'total_students' => $total_students,
                'total_absent_count' => $total_absent_count,
                'students_with_absent' => $students_with_absent,
                'avg_absent_per_student' => $total_students > 0 ? round($total_absent_count / $total_students, 2) : 0
            ]
        ]
    ]);
}


/**
 * Generate a secure share token for a student
 */

/**
 * Get weekly attendance for a student (for session report)
 */


public function getWeeklyAttendance()
{
    $request = $this->request;
    $student_id = $request->getPost('student_id');
    
    if (!$student_id) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'No student ID provided'
        ]);
    }
    
    $session_id = (int) session('member_sessionid');
    $campus_id = (int) session('member_campusid');
    $current_date = date('Y-m-d');
    
    // Get student info
    $student = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.parent_id, c.class_short_name, sec.section_name')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $session_id . ' AND sc.status = 1', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.student_id', $student_id)
        ->get()
        ->getRow();
    
    if (!$student) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Student not found'
        ]);
    }
    
    // Get parent name
    $parent = $this->db->table('parents')
        ->select('f_name')
        ->where('parent_id', $student->parent_id)
        ->get()
        ->getRow();
    
    // Get all terms for this session
    $terms = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, ts.term_id, t.name as term_name')
        ->join('terms t', 't.term_id = ts.term_id')
        ->where('ts.session_id', $session_id)
        ->where('t.status', 1)
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResult();
    
    $weeklyData = [];
    $totalWorkingDays = 0;
    $totalAbsentDays = 0;
    $allAbsentDates = [];
    
    foreach ($terms as $term) {
        // Get all weeks for this term (ALL weeks, not just up to current date)
        $weeks = $this->db->table('term_weeks')
            ->select('term_weeks_id, week_no, week_name, start_date, end_date')
            ->where('term_session_id', $term->term_session_id)
            ->orderBy('week_no', 'ASC')
            ->get()
            ->getResult();
        
        $termWeeks = [];
        foreach ($weeks as $week) {
            // Get all days in this week
            $weekDays = $this->getWeekDaysWithAttendance($student_id, $week->start_date, $week->end_date);
            
            $weekData = [
                'week_no' => $week->week_no,
                'week_name' => $week->week_name,
                'days' => $weekDays,
                'absent_count' => 0,
                'working_days' => 0,
                'days_with_record' => 0
            ];
            
            foreach ($weekDays as $day) {
                // Only count days that have attendance records (not OFF)
                if (isset($day['has_record']) && $day['has_record']) {
                    $weekData['days_with_record']++;
                    if ($day['status'] === 'A') {
                        $weekData['absent_count']++;
                        $totalAbsentDays++;
                        $allAbsentDates[] = $day['date_formatted'];
                    }
                    $totalWorkingDays++;
                    $weekData['working_days']++;
                }
            }
            
            // Add weeks even if they have no records (show empty week)
            $termWeeks[] = $weekData;
        }
        
        if (!empty($termWeeks)) {
            $weeklyData[] = [
                'term_name' => $term->term_name,
                'weeks' => $termWeeks
            ];
        }
    }
    
    $classSection = ($student->class_short_name ?? '') . ($student->section_name ? ' - ' . $student->section_name : '');
    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
    
    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'student_id' => $student->student_id,
            'student_name' => $studentName,
            'class_section' => $classSection,
            'parent_name' => $parent->f_name ?? 'Parent',
            'whatsapp' => $parent->whatsapp ?? '',
            'weekly_data' => $weeklyData,
            'summary' => [
                'total_working_days' => $totalWorkingDays,
                'total_absent_days' => $totalAbsentDays,
                'absent_dates' => implode(', ', $allAbsentDates)
            ]
        ]
    ]);
}
/**
 * Get days of a week with attendance status
 * Only show attendance for days where a record exists
 * Days with no record = OFF (no school/holiday)
 */
/**
 * Get days of a week with attendance status
 */

private function getWeekDaysWithAttendance($student_id, $start_date, $end_date)
{
    $days = [];
    $current = strtotime($start_date);
    $end = strtotime($end_date);
    $current_date = date('Y-m-d');
    
    // Get the student's cls_sec_id
    $sessionid = (int) session('member_sessionid');
    $studentClass = $this->db->table('student_class')
        ->select('cls_sec_id')
        ->where('student_id', $student_id)
        ->where('session_id', $sessionid)
        ->where('status', 1)
        ->get()
        ->getRow();
    
    $cls_sec_id = $studentClass->cls_sec_id ?? 0;
    
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
    $timings = $this->getSectionTimings($cls_sec_id, $activeTypeId);
    
    // Define day names mapping
    $dayNames = [
        'Monday' => 'Mon',
        'Tuesday' => 'Tue',
        'Wednesday' => 'Wed',
        'Thursday' => 'Thu',
        'Friday' => 'Fri',
        'Saturday' => 'Sat',
        'Sunday' => 'Sun'
    ];
    
    while ($current <= $end) {
        $date = date('Y-m-d', $current);
        $fullDayName = date('l', $current);
        $shortDayName = $dayNames[$fullDayName] ?? substr($fullDayName, 0, 3);
        $dateObj = new \DateTime($date);
        $dateObj->setTime(0, 0, 0);
        $todayObj = new \DateTime($current_date);
        $todayObj->setTime(0, 0, 0);
        
        // Skip future dates
        if ($dateObj > $todayObj) {
            $current = strtotime('+1 day', $current);
            continue;
        }
        
        // Check if this day has VALID school timing (different checkin/checkout)
        $hasValidTiming = $this->hasValidSchoolTiming($timings, $fullDayName);
        
        // If no valid timing (same checkin/checkout or no timing), SKIP this day entirely
        // Do NOT add to $days array
        if (!$hasValidTiming) {
            $current = strtotime('+1 day', $current);
            continue;
        }
        
        // Get attendance record for this student
        $attendance = $this->db->table('attendance')
            ->select('status')
            ->where('student_id', $student_id)
            ->where('date', $date)
            ->get()
            ->getRow();
        
        // If no attendance record on a valid school day, show as OFF
        if (!$attendance) {
            $days[] = [
                'date' => $date,
                'date_formatted' => date('d-M-Y', $current),
                'day_name' => $shortDayName,
                'status' => 'OFF',
                'has_record' => false,
                'is_working_day' => true,
                'is_future' => false
            ];
            $current = strtotime('+1 day', $current);
            continue;
        }
        
        // Record exists on a valid school day - use actual status
        $hasRecord = true;
        $attStatus = strtolower(trim($attendance->status));
        
        if ($attStatus === 'present' || $attStatus === 'p') {
            $status = 'P';
        } else if ($attStatus === 'absent' || $attStatus === 'a') {
            $status = 'A';
        } else if ($attStatus === 'late' || $attStatus === 'l') {
            $status = 'L';
        } else if ($attStatus === 'el' || $attStatus === 'early leave') {
            $status = 'EL';
        } else if ($attStatus === 'lc' || $attStatus === 'late coming') {
            $status = 'LC';
        } else {
            $status = strtoupper(substr($attStatus, 0, 2));
        }
        
        $days[] = [
            'date' => $date,
            'date_formatted' => date('d-M-Y', $current),
            'day_name' => $shortDayName,
            'status' => $status,
            'has_record' => $hasRecord,
            'is_working_day' => true,
            'is_future' => false
        ];
        
        $current = strtotime('+1 day', $current);
    }
    
    return $days;
}

/**
 * Check if a day has valid school timing (different checkin and checkout times)
 */
private function hasValidSchoolTiming($timings, $dayName)
{
    // If no timing exists for this day, return false
    if (!isset($timings[$dayName])) {
        return false;
    }
    
    $timing = $timings[$dayName];
    
    // If checkin or checkout is null, return false
    if (empty($timing->checkin_timing) || empty($timing->checkout_timing)) {
        return false;
    }
    
    // Convert to string for comparison
    $checkin = (string)$timing->checkin_timing;
    $checkout = (string)$timing->checkout_timing;
    
    // Return true ONLY if checkin and checkout are DIFFERENT
    return $checkin !== $checkout;
}

/**
 * Check if a day is a school day (has timings)
 */
private function isSchoolDay($timings, $dayName)
{
    if (empty($timings)) {
        // If no timings defined, assume Monday-Friday are school days
        $schoolDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        return in_array($dayName, $schoolDays);
    }
    return isset($timings[$dayName]);
}
/**
 * Get school timings for a section
 */
private function getSectionTimings($cls_sec_id, $activeTypeId = null)
{
    if (!$cls_sec_id) {
        return [];
    }
    
    $query = $this->db->table('school_timings st')
        ->select('st.dayname, st.checkin_timing, st.checkout_timing, st.type_id')
        ->where('st.cls_sec_id', $cls_sec_id);
    
    if ($activeTypeId) {
        $query->where('st.type_id', $activeTypeId);
    }
    
    $timings = $query->get()->getResult();
    
    $timingsByDay = [];
    foreach ($timings as $timing) {
        $timingsByDay[$timing->dayname] = $timing;
    }
    
    return $timingsByDay;
}

/**
 * Get working days count
 */
private function getWorkingDays($campus_id, $start_date, $end_date, $sessionid)
{
    // Get holidays for the date range
    $holidays = $this->db->table('holidays h')
        ->join('terms_session ts', 'ts.term_session_id = h.term_session_id')
        ->where('h.holiday_date >=', $start_date)
        ->where('h.holiday_date <=', $end_date)
        ->where('ts.session_id', $sessionid)
        ->get()
        ->getResult();
    
    $holiday_dates = [];
    foreach ($holidays as $holiday) {
        $holiday_dates[] = $holiday->holiday_date;
    }
    
    $current = strtotime($start_date);
    $end = strtotime($end_date);
    $working_days = 0;
    
    while ($current <= $end) {
        $date = date('Y-m-d', $current);
        $day_of_week = date('N', $current);
        
        // Exclude Sundays (7) from working days
        if ($day_of_week != 7 && !in_array($date, $holiday_dates)) {
            $working_days++;
        }
        
        $current = strtotime('+1 day', $current);
    }
    
    return $working_days;
}

/**
 * Generate a secure share token for a student
 */
public function generateShareToken()
{
    $request = $this->request;
    $student_id = $request->getPost('student_id');
    $campus_id = session('member_campusid');
    
    // Verify that the logged-in user has access to this student
    $student = $this->db->table('students')
        ->select('student_id, parent_id')
        ->where('student_id', $student_id)
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();
    
    if (!$student) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Student not found or access denied'
        ]);
    }
    
    // Check if attendance_share_tokens table exists, if not create it
    $tableExists = $this->db->query("SHOW TABLES LIKE 'attendance_share_tokens'")->getNumRows();
    if (!$tableExists) {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `attendance_share_tokens` (
              `id` int NOT NULL AUTO_INCREMENT,
              `student_id` int NOT NULL,
              `token` varchar(64) NOT NULL,
              `parent_id` int NOT NULL,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              `expires_at` datetime DEFAULT NULL,
              `is_used` tinyint(1) DEFAULT '0',
              `click_count` int DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_token` (`token`),
              KEY `idx_student_id` (`student_id`),
              KEY `idx_token` (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        ");
    }
    
    // Generate unique token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Delete any existing tokens for this student
    $this->db->table('attendance_share_tokens')
        ->where('student_id', $student_id)
        ->delete();
    
    // Insert new token
    $this->db->table('attendance_share_tokens')->insert([
        'student_id' => $student_id,
        'token' => $token,
        'parent_id' => $student->parent_id,
        'expires_at' => $expires_at,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    // Generate share URL
    $shareUrl = base_url("parent/attendance/view/{$token}");
    
    return $this->response->setJSON([
        'success' => true,
        'token' => $token,
        'share_url' => $shareUrl,
        'expires_at' => $expires_at
    ]);
}

/**
 * Export to Excel
 */


public function exportExcel()
{
    $request = $this->request;
    $campus_id = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');
    
    $filter_type = $request->getPost('filter_type');
    $cls_sec_id = $request->getPost('cls_sec_id');
    $current_date = date('Y-m-d');
    
    $start_date = null;
    $end_date = null;
    
    switch ($filter_type) {
        case 'month':
            $month_year = $request->getPost('month_year');
            if ($month_year) {
                $date = \DateTime::createFromFormat('F Y', $month_year);
                if ($date) {
                    $start_date = $date->format('Y-m-01');
                    $end_date = $date->format('Y-m-t');
                    if (strtotime($end_date) > strtotime($current_date)) {
                        $end_date = $current_date;
                    }
                }
            }
            break;
            
        case 'current_week':
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $end_date = date('Y-m-d', strtotime('sunday this week'));
            if (strtotime($end_date) > strtotime($current_date)) {
                $end_date = $current_date;
            }
            break;
            
        case 'current_session':
            $session = $this->db->table('academic_session')
                ->where('session_id', $sessionid)
                ->get()
                ->getRow();
            if ($session) {
                $start_date = $session->start_date;
                $end_date = $session->end_date;
                if (strtotime($end_date) > strtotime($current_date)) {
                    $end_date = $current_date;
                }
            }
            break;
            
        case 'custom_range':
            $start_date = $request->getPost('start_date');
            $end_date = $request->getPost('end_date');
            if ($end_date && strtotime($end_date) > strtotime($current_date)) {
                $end_date = $current_date;
            }
            break;
    }
    
    if (!$start_date || !$end_date) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid date range selected'
        ]);
    }
    
    // Get students and attendance data for export
    $studentQuery = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, c.class_short_name, sec.section_name, p.f_name as parent_name')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . (int)$sessionid . ' AND sc.status = 1', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', '1');
        
    
    if (!empty($cls_sec_id) && $cls_sec_id > 0) {
        $studentQuery->where('sc.cls_sec_id', $cls_sec_id);
    }
    
    $students = $studentQuery->get()->getResult();
    
    $exportData = [];
    foreach ($students as $student) {
        $absentResult = $this->db->table('attendance')
            ->select("DATE_FORMAT(date, '%d-%b-%Y') as absent_date")
            ->where('student_id', $student->student_id)
            ->where('date >=', $start_date)
            ->where('date <=', $end_date)
            ->where('status', 'A')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResult();
        
        $absentDates = [];
        foreach ($absentResult as $absent) {
            $absentDates[] = $absent->absent_date;
        }
        
        $absentCount = count($absentDates);
        $classSection = ($student->class_short_name ?? '') . ($student->section_name ? ' - ' . $student->section_name : '');
        
        $exportData[] = [
            'reg_no' => $student->reg_no ?? '-',
            'student_name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
            'class_section' => $classSection ?: '-',
            'parent_name' => $student->parent_name ?? '-',
            'absent_count' => $absentCount,
            'absent_dates' => !empty($absentDates) ? implode(', ', $absentDates) : '-'
        ];
    }
    
    // Set headers for CSV download
    $filename = 'attendance_report_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Add headers
    fputcsv($output, ['Registration No', 'Student Name', 'Class Section', 'Parent Name', 'Absent Days', 'Absent Dates']);
    
    // Add data
    foreach ($exportData as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}
   }