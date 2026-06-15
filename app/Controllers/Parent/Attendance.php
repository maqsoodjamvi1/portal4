<?php

namespace App\Controllers\Parent;

use App\Controllers\BaseController;
use DateInterval;
use DateTimeImmutable;

class Attendance extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'school']);
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
        'session_id' => $session_id,
        'share_token' => $token,
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
    
    // Get timings for this section (campus-scoped)
    $timingsResult = getSchoolTimingsForSections([(int) $cls_sec_id], (int) $campus_id);
    
    echo "School Timings for cls_sec_id = $cls_sec_id:\n";
    echo "----------------------------------------\n";
    
    if (empty($timingsResult)) {
        echo "No timings found for this class section!\n";
        echo "Please add timings to school_timings table for cls_sec_id = $cls_sec_id\n";
    } else {
        $offDays = [];
        $workingDays = [];
        
        foreach ($timingsResult as $timing) {
            $isOff = ! isSchoolTimingWorkingDay($timing['checkin_timing'] ?? null, $timing['checkout_timing'] ?? null);
            $status = $isOff ? 'OFF (Same time)' : 'WORKING';
            echo ($timing['dayname'] ?? '') . ': ' . ($timing['checkin_timing'] ?? '') . ' - ' . ($timing['checkout_timing'] ?? '') . " [$status]\n";
            
            if ($isOff) {
                $offDays[] = $timing['dayname'] ?? '';
            } else {
                $workingDays[] = $timing['dayname'] ?? '';
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
    $timingsResult = getSchoolTimingsForSections([(int) $cls_sec_id], (int) $campus_id);

    $offDays = [];
    $workingDays = [];

    foreach ($timingsResult as $timing) {
        if (isSchoolTimingWorkingDay($timing['checkin_timing'] ?? null, $timing['checkout_timing'] ?? null)) {
            $workingDays[] = $timing['dayname'] ?? '';
        } else {
            $offDays[] = $timing['dayname'] ?? '';
        }
    }

    echo "<pre>";
    echo "Student ID: $student_id\n";
    echo "Session ID: $session_id\n";
    echo "cls_sec_id: $cls_sec_id\n";
    echo "Campus ID: $campus_id\n";
    echo "Timings found: " . count($timingsResult) . "\n";
    echo "Working Days (checkin != checkout): " . implode(', ', $workingDays) . "\n";
    echo "Off Days (checkin = checkout): " . implode(', ', $offDays) . "\n";
    echo "</pre>";
    exit;
}

    private function parentAssertShareTokenAllowsStudent(string $token, int $studentId): bool
    {
        $token = trim($token);
        if ($token === '' || $studentId <= 0) {
            return false;
        }

        $tokenRecord = $this->db->table('attendance_share_tokens')
            ->where('token', $token)
            ->get()
            ->getRow();

        if (! $tokenRecord) {
            return false;
        }

        if (! empty($tokenRecord->expires_at) && strtotime((string) $tokenRecord->expires_at) < time()) {
            return false;
        }

        $holder = $this->db->table('students')
            ->select('parent_id')
            ->where('student_id', (int) $tokenRecord->student_id)
            ->get()
            ->getRow();

        $target = $this->db->table('students')
            ->select('parent_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRow();

        if (! $holder || ! $target) {
            return false;
        }

        return (int) $holder->parent_id > 0 && (int) $holder->parent_id === (int) $target->parent_id;
    }

    private function parentGetSystemIdForStudent(int $studentId): int
    {
        $student = $this->db->table('students')->select('campus_id')->where('student_id', $studentId)->get()->getRowArray();
        if (! $student || empty($student['campus_id'])) {
            return 0;
        }
        $campus = $this->db->table('campus')->select('system_id')->where('campus_id', (int) $student['campus_id'])->get()->getRowArray();
        if (! $campus || empty($campus['system_id'])) {
            return 0;
        }

        return (int) $campus['system_id'];
    }

    /**
     * @return array{session_id: int, session_name: string, start_date: string, end_date: string}|null
     */
    private function parentGetCurrentAcademicSessionForStudent(int $studentId): ?array
    {
        $student = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();

        if (! $student || empty($student['campus_id'])) {
            return null;
        }

        $campus = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', (int) $student['campus_id'])
            ->get()
            ->getRowArray();

        if (! $campus || empty($campus['system_id'])) {
            return null;
        }

        $systemId = (int) $campus['system_id'];

        $session = $this->db->table('academic_session')
            ->select('session_id, session_name, start_date, end_date')
            ->where('system_id', $systemId)
            ->where('CURDATE() BETWEEN start_date AND end_date', null, false)
            ->orderBy('start_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! $session) {
            $session = $this->db->table('academic_session')
                ->select('session_id, session_name, start_date, end_date')
                ->where('system_id', $systemId)
                ->orderBy('start_date', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        if (! $session) {
            return null;
        }

        return [
            'session_id'   => (int) $session['session_id'],
            'session_name' => (string) ($session['session_name'] ?? ''),
            'start_date'   => (string) ($session['start_date'] ?? ''),
            'end_date'     => (string) ($session['end_date'] ?? ''),
        ];
    }

    private function parentParseYmd(?string $s): ?DateTimeImmutable
    {
        $k = $this->parentNormalizeYmd((string) $s);
        if ($k === null) {
            return null;
        }

        return DateTimeImmutable::createFromFormat('Y-m-d', $k) ?: null;
    }

    private function parentNormalizeYmd(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '' || strpos($raw, '0000-00-00') === 0) {
            return null;
        }
        $t = strtotime($raw);

        return $t ? date('Y-m-d', $t) : null;
    }

    /**
     * @param list<array{term_session_id: int, term_name: string, start_date: string, end_date: string}> $terms
     */
    private function parentPickCurrentTermSessionId(array $terms): int
    {
        if ($terms === []) {
            return 0;
        }

        $today = date('Y-m-d');
        foreach ($terms as $t) {
            $s = $this->parentNormalizeYmd((string) ($t['start_date'] ?? ''));
            $e = $this->parentNormalizeYmd((string) ($t['end_date'] ?? ''));
            if ($s === null || $e === null) {
                continue;
            }
            if ($today >= $s && $today <= $e) {
                return (int) ($t['term_session_id'] ?? 0);
            }
        }

        $picked = 0;
        $pickedStart = '';
        foreach ($terms as $t) {
            $s = $this->parentNormalizeYmd((string) ($t['start_date'] ?? ''));
            if ($s === null) {
                continue;
            }
            if ($s <= $today && ($pickedStart === '' || $s >= $pickedStart)) {
                $pickedStart = $s;
                $picked      = (int) ($t['term_session_id'] ?? 0);
            }
        }

        if ($picked > 0) {
            return $picked;
        }

        $last = $terms[count($terms) - 1];

        return (int) ($last['term_session_id'] ?? 0);
    }

    /**
     * Mon–Fri stats for a term; end date capped to today for ongoing terms.
     *
     * @param array<string, string> $byDate
     *
     * @return array{working_days: int, present: int, absent: int, leave: int, late: int, early_leave: int, no_record: int, present_pct: float|null, attendance_rate_pct: float|null}
     */
    private function parentSummarizeTermWeekdays(string $startYmd, string $endYmd, array $byDate): array
    {
        $out = [
            'working_days'        => 0,
            'present'             => 0,
            'absent'              => 0,
            'leave'               => 0,
            'late'                => 0,
            'early_leave'         => 0,
            'no_record'           => 0,
            'present_pct'         => null,
            'attendance_rate_pct' => null,
        ];

        $start = $this->parentParseYmd(substr(trim($startYmd), 0, 10));
        $end   = $this->parentParseYmd(substr(trim($endYmd), 0, 10));
        if ($start === null || $end === null || $start > $end) {
            return $out;
        }

        $today = new DateTimeImmutable('today');
        if ($end > $today) {
            $end = $today;
        }
        if ($start > $end) {
            return $out;
        }

        $cursor = $start;
        while ($cursor <= $end) {
            $n = (int) $cursor->format('N');
            if ($n >= 6) {
                $cursor = $cursor->add(new DateInterval('P1D'));
                continue;
            }

            $ymd  = $cursor->format('Y-m-d');
            $code = $byDate[$ymd] ?? null;
            $code = $code !== null ? strtoupper(trim((string) $code)) : '';

            $out['working_days']++;

            if ($code === '' || $code === '?') {
                $out['no_record']++;
            } elseif ($code === 'P') {
                $out['present']++;
            } elseif ($code === 'A') {
                $out['absent']++;
            } elseif ($code === 'L') {
                $out['leave']++;
            } elseif ($code === 'LC') {
                $out['late']++;
            } elseif ($code === 'EL') {
                $out['early_leave']++;
            } else {
                $out['no_record']++;
            }

            $cursor = $cursor->add(new DateInterval('P1D'));
        }

        $wd = $out['working_days'];
        if ($wd > 0) {
            $attended                   = $out['present'] + $out['late'] + $out['early_leave'];
            $out['present_pct']         = round(100.0 * $out['present'] / $wd, 1);
            $out['attendance_rate_pct'] = round(100.0 * $attended / $wd, 1);
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function parentRowToDisplayCode(array $row, bool $hasLc, bool $hasEl): string
    {
        $lc = $hasLc ? (float) ($row['lc_duration'] ?? 0) : 0.0;
        $el = $hasEl ? (float) ($row['el_duration'] ?? 0) : 0.0;

        if ($el > 0) {
            return 'EL';
        }
        if ($lc > 0) {
            return 'LC';
        }

        $st = strtoupper(trim((string) ($row['status'] ?? '')));
        $st = str_replace([' ', '-', '_'], '', $st);

        if (in_array($st, ['P', 'PRESENT', 'PR'], true)) {
            return 'P';
        }
        if (in_array($st, ['A', 'ABSENT', 'AB'], true)) {
            return 'A';
        }
        if (in_array($st, ['L', 'LEAVE', 'LV'], true)) {
            return 'L';
        }
        if (in_array($st, ['LC', 'LATECOMING'], true)) {
            return 'LC';
        }
        if (in_array($st, ['EL', 'EARLYLEAVE'], true)) {
            return 'EL';
        }
        if (strlen($st) === 1) {
            return $st;
        }
        if (strlen($st) === 2) {
            return $st;
        }

        return '?';
    }

    /**
     * @return array<string, string> Y-m-d => code
     */
    private function parentFetchAttendanceByDateRange(int $studentId, string $startDate, string $endDate): array
    {
        $start = $this->parentNormalizeYmd($startDate) ?? '';
        $end   = $this->parentNormalizeYmd($endDate) ?? '';
        if ($start === '' || $end === '' || $studentId <= 0) {
            return [];
        }

        try {
            $fields = $this->db->getFieldNames('attendance');
        } catch (\Throwable $e) {
            return [];
        }

        $has = static function (string $name) use ($fields): bool {
            return in_array($name, $fields, true);
        };

        $colDate = $has('attendance_date') ? 'attendance_date' : ($has('att_date') ? 'att_date' : ($has('date') ? 'date' : null));
        if ($colDate === null) {
            return [];
        }

        $hasLc = $has('lc_duration');
        $hasEl = $has('el_duration');

        $select = ['student_id', "{$colDate} AS attendance_date", 'status'];
        if ($hasLc) {
            $select[] = 'lc_duration';
        }
        if ($hasEl) {
            $select[] = 'el_duration';
        }

        $rows = $this->db->table('attendance')
            ->select(implode(', ', $select))
            ->where('student_id', $studentId)
            ->where($colDate . ' >=', $start)
            ->where($colDate . ' <=', $end)
            ->orderBy($colDate, 'ASC')
            ->get()
            ->getResultArray();

        $byDate = [];
        foreach ($rows as $r) {
            $d = $this->parentNormalizeYmd((string) ($r['attendance_date'] ?? ''));
            if ($d === null) {
                continue;
            }
            if (! isset($byDate[$d])) {
                $byDate[$d] = $this->parentRowToDisplayCode($r, $hasLc, $hasEl);
            }
        }

        return $byDate;
    }

    /**
     * @param array{working_days: int, present: int, absent: int, leave: int, late: int, early_leave: int, no_record: int, present_pct: float|null, attendance_rate_pct: float|null} $sum
     *
     * @return array{start_date: string, end_date: string, total_days: int, present_count: int, absent_count: int, late_count: int, early_leave_count: int, late_coming_count: int, attendance_rate: float|int}
     */
    private function parentSummaryToLegacyJson(array $sum, string $startYmd, string $endYmd): array
    {
        $today  = date('Y-m-d');
        $endCap = $this->parentNormalizeYmd($endYmd) ?? $today;
        if ($endCap > $today) {
            $endCap = $today;
        }
        $startOk = $this->parentNormalizeYmd($startYmd) ?? $endCap;
        if ($startOk > $endCap) {
            $startOk = $endCap;
        }

        $rate = $sum['attendance_rate_pct'];

        return [
            'start_date'          => date('d M Y', strtotime($startOk)),
            'end_date'            => date('d M Y', strtotime($endCap)),
            'total_days'          => (int) ($sum['working_days'] ?? 0),
            'present_count'       => (int) ($sum['present'] ?? 0),
            'absent_count'        => (int) ($sum['absent'] ?? 0),
            'late_count'          => (int) ($sum['leave'] ?? 0),
            'early_leave_count'   => (int) ($sum['early_leave'] ?? 0),
            'late_coming_count'   => (int) ($sum['late'] ?? 0),
            'attendance_rate'     => $rate !== null ? (float) $rate : 0.0,
        ];
    }

    public function getChildAttendance()
    {
        $request    = $this->request;
        $student_id = (int) $request->getPost('student_id');
        $shareToken = trim((string) $request->getPost('share_token'));

        if ($student_id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid student']);
        }

        if ($shareToken !== '') {
            if (! $this->parentAssertShareTokenAllowsStudent($shareToken, $student_id)) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Access denied']);
            }
        }

        $student = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.profile_photo')
            ->where('s.student_id', $student_id)
            ->get()
            ->getRow();

        if (! $student) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Student not found']);
        }

        $systemId = $this->parentGetSystemIdForStudent($student_id);

        $history = $this->db->table('student_class sc')
            ->select('sc.session_id, sc.cls_sec_id, as.session_name, as.start_date, as.end_date, c.class_short_name, sec.section_name')
            ->join('academic_session as', 'as.session_id = sc.session_id', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('sc.student_id', $student_id)
            ->where('sc.session_id IS NOT NULL', null, false)
            ->where('sc.status', 1)
            ->orderBy('sc.session_id', 'DESC')
            ->get()
            ->getResultArray();

        $sessions = [];
        foreach ($history as $row) {
            $sessId = (int) ($row['session_id'] ?? 0);
            if ($sessId <= 0 || isset($sessions[$sessId])) {
                continue;
            }
            $sessions[$sessId] = [
                'session_id'   => $sessId,
                'session_name' => (string) ($row['session_name'] ?? ('Session ' . $sessId)),
                'cls_sec_id'   => (int) ($row['cls_sec_id'] ?? 0),
                'class_short'  => (string) ($row['class_short_name'] ?? ''),
                'section_name' => (string) ($row['section_name'] ?? ''),
            ];
        }

        if ($sessions === []) {
            return $this->response->setJSON([
                'success' => true,
                'data'    => [
                    'student'      => $student,
                    'attendance'   => [],
                    'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'summary'      => [
                        'start_date'          => date('d M Y'),
                        'end_date'            => date('d M Y'),
                        'total_days'          => 0,
                        'present_count'       => 0,
                        'absent_count'        => 0,
                        'late_count'          => 0,
                        'early_leave_count'   => 0,
                        'late_coming_count'   => 0,
                        'attendance_rate'     => 0,
                    ],
                    'current_term' => null,
                    'other_terms'  => [],
                ],
            ]);
        }

        $sessionIds = array_keys($sessions);
        rsort($sessionIds, SORT_NUMERIC);
        $anchorSessionId = (int) $sessionIds[0];

        $calendarSession   = $this->parentGetCurrentAcademicSessionForStudent($student_id);
        $calendarSessionId = (int) ($calendarSession['session_id'] ?? 0);
        $enrolledInCalendar = $calendarSessionId > 0 && isset($sessions[$calendarSessionId]);
        $detailSessionId    = $enrolledInCalendar ? $calendarSessionId : $anchorSessionId;

        $termsBySession = [];
        if ($systemId > 0 && $sessionIds !== []) {
            $termRows = $this->db->table('terms_session ts')
                ->select('ts.term_session_id, ts.session_id, ts.start_date, ts.end_date, t.name AS term_name')
                ->join('terms t', 't.term_id = ts.term_id', 'inner')
                ->where('ts.system_id', $systemId)
                ->whereIn('ts.session_id', $sessionIds)
                ->orderBy('ts.start_date', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($termRows as $tr) {
                $sid = (int) ($tr['session_id'] ?? 0);
                if (! isset($sessions[$sid])) {
                    continue;
                }
                $termsBySession[$sid][] = [
                    'term_session_id' => (int) ($tr['term_session_id'] ?? 0),
                    'term_name'       => (string) ($tr['term_name'] ?? 'Term'),
                    'start_date'      => (string) ($tr['start_date'] ?? ''),
                    'end_date'        => (string) ($tr['end_date'] ?? ''),
                ];
            }
        }

        $minD = null;
        $maxD = null;
        foreach ($termsBySession as $terms) {
            foreach ($terms as $t) {
                $ds = $this->parentNormalizeYmd((string) ($t['start_date'] ?? ''));
                $de = $this->parentNormalizeYmd((string) ($t['end_date'] ?? ''));
                if ($ds === null || $de === null) {
                    continue;
                }
                if ($minD === null || $ds < $minD) {
                    $minD = $ds;
                }
                if ($maxD === null || $de > $maxD) {
                    $maxD = $de;
                }
            }
        }

        if (($minD === null || $maxD === null) && $anchorSessionId > 0) {
            $asR = $this->db->table('academic_session')
                ->select('start_date, end_date')
                ->where('session_id', $anchorSessionId)
                ->get()
                ->getRow();
            if ($asR) {
                $minD = $this->parentNormalizeYmd((string) $asR->start_date);
                $maxD = $this->parentNormalizeYmd((string) $asR->end_date);
                $today = date('Y-m-d');
                if ($maxD !== null && $maxD > $today) {
                    $maxD = $today;
                }
            }
        }

        $byDate = [];
        if ($minD !== null && $maxD !== null) {
            $byDate = $this->parentFetchAttendanceByDateRange($student_id, $minD, $maxD);
        }

        $detailTerms         = $termsBySession[$detailSessionId] ?? [];
        $detailTermSessionId = $this->parentPickCurrentTermSessionId($detailTerms);
        $detailTermMeta      = null;
        foreach ($detailTerms as $t) {
            if ((int) ($t['term_session_id'] ?? 0) === $detailTermSessionId) {
                $detailTermMeta = $t;
                break;
            }
        }

        $clsDetail = (int) ($sessions[$detailSessionId]['cls_sec_id'] ?? 0);
        if ($clsDetail <= 0) {
            $clsDetail = (int) ($sessions[$anchorSessionId]['cls_sec_id'] ?? 0);
        }

        $studentDisplay = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, c.class_short_name, sec.section_name')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . (int) $detailSessionId . ' AND sc.status = 1', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('s.student_id', $student_id)
            ->get()
            ->getRow();

        if (! $studentDisplay) {
            $studentDisplay = $student;
        }

        $offDays     = $this->getOffDays($clsDetail, $student_id);
        $workingCols = $this->getValidSchoolDays($clsDetail, $student_id);

        $attendanceData = [];
        $summaryForJson = [
            'start_date'          => date('d M Y'),
            'end_date'            => date('d M Y'),
            'total_days'          => 0,
            'present_count'       => 0,
            'absent_count'        => 0,
            'late_count'          => 0,
            'early_leave_count'   => 0,
            'late_coming_count'   => 0,
            'attendance_rate'     => 0,
        ];
        $currentTermPayload = null;

        if ($detailTermSessionId > 0 && $detailTermMeta !== null) {
            $tStart = $this->parentNormalizeYmd((string) $detailTermMeta['start_date']) ?? date('Y-m-d');
            $tEnd   = $this->parentNormalizeYmd((string) $detailTermMeta['end_date']) ?? $tStart;
            $today  = date('Y-m-d');
            if ($tEnd > $today) {
                $tEnd = $today;
            }
            if ($tStart <= $tEnd) {
                $sumArr         = $this->parentSummarizeTermWeekdays($tStart, $tEnd, $byDate);
                $summaryForJson = $this->parentSummaryToLegacyJson($sumArr, $tStart, $tEnd);

                foreach ($this->getDatesInRange($tStart, $tEnd) as $date) {
                    $dow = date('l', strtotime($date));
                    if (in_array($dow, $offDays, true)) {
                        continue;
                    }
                    $code = strtoupper(trim((string) ($byDate[$date] ?? '')));
                    if ($code === '' || $code === '?') {
                        continue;
                    }
                    $attendanceData[] = [
                        'date'           => $date,
                        'date_formatted' => date('d M Y', strtotime($date)),
                        'day_name'       => substr($dow, 0, 3),
                        'full_day_name'  => $dow,
                        'status'         => $code,
                    ];
                }
            }

            $currentTermPayload = [
                'term_session_id' => $detailTermSessionId,
                'term_name'       => (string) ($detailTermMeta['term_name'] ?? ''),
                'session_id'      => $detailSessionId,
                'session_name'    => (string) ($sessions[$detailSessionId]['session_name'] ?? ''),
                'start_date'      => (string) ($detailTermMeta['start_date'] ?? ''),
                'end_date'        => (string) ($detailTermMeta['end_date'] ?? ''),
            ];
        } elseif ($anchorSessionId > 0) {
            $asRow = $this->db->table('academic_session')
                ->select('start_date, end_date, session_name')
                ->where('session_id', $anchorSessionId)
                ->get()
                ->getRow();
            if ($asRow) {
                $tStart = $this->parentNormalizeYmd((string) $asRow->start_date) ?? date('Y-m-d');
                $tEnd   = $this->parentNormalizeYmd((string) $asRow->end_date) ?? $tStart;
                $today  = date('Y-m-d');
                if ($tEnd > $today) {
                    $tEnd = $today;
                }
                if ($tStart <= $tEnd) {
                    $sumArr         = $this->parentSummarizeTermWeekdays($tStart, $tEnd, $byDate);
                    $summaryForJson = $this->parentSummaryToLegacyJson($sumArr, $tStart, $tEnd);
                    $attendanceData = [];
                    foreach ($this->getDatesInRange($tStart, $tEnd) as $date) {
                        $dow = date('l', strtotime($date));
                        if (in_array($dow, $offDays, true)) {
                            continue;
                        }
                        $code = strtoupper(trim((string) ($byDate[$date] ?? '')));
                        if ($code === '' || $code === '?') {
                            continue;
                        }
                        $attendanceData[] = [
                            'date'           => $date,
                            'date_formatted' => date('d M Y', strtotime($date)),
                            'day_name'       => substr($dow, 0, 3),
                            'full_day_name'  => $dow,
                            'status'         => $code,
                        ];
                    }
                    $currentTermPayload = [
                        'term_session_id' => 0,
                        'term_name'       => 'Academic session',
                        'session_id'      => $anchorSessionId,
                        'session_name'    => (string) ($asRow->session_name ?? ''),
                        'start_date'      => (string) $asRow->start_date,
                        'end_date'        => (string) $asRow->end_date,
                    ];
                }
            }
        }

        $otherTerms = [];
        foreach ($sessionIds as $sid) {
            $sid = (int) $sid;
            $sessName = (string) ($sessions[$sid]['session_name'] ?? '');
            foreach ($termsBySession[$sid] ?? [] as $t) {
                $tid = (int) ($t['term_session_id'] ?? 0);
                if ($tid === $detailTermSessionId && $sid === $detailSessionId) {
                    continue;
                }
                $ds = $this->parentNormalizeYmd((string) ($t['start_date'] ?? ''));
                $de = $this->parentNormalizeYmd((string) ($t['end_date'] ?? ''));
                if ($ds === null || $de === null) {
                    continue;
                }
                $sumArr   = $this->parentSummarizeTermWeekdays($ds, $de, $byDate);
                $legacy   = $this->parentSummaryToLegacyJson($sumArr, $ds, $de);
                $otherTerms[] = [
                    'session_id'      => $sid,
                    'session_name'    => $sessName,
                    'term_session_id' => $tid,
                    'term_name'       => (string) ($t['term_name'] ?? ''),
                    'start_date'      => $ds,
                    'end_date'        => $de,
                    'summary'         => $legacy,
                ];
            }
        }

        usort($otherTerms, static function (array $a, array $b): int {
            if ($a['session_id'] !== $b['session_id']) {
                return $b['session_id'] <=> $a['session_id'];
            }

            return strcmp($b['start_date'], $a['start_date']);
        });

        return $this->response->setJSON([
            'success' => true,
            'data'    => [
                'student'      => $studentDisplay,
                'attendance'   => $attendanceData,
                'working_days' => $workingCols,
                'summary'      => $summaryForJson,
                'current_term' => $currentTermPayload,
                'other_terms'  => $otherTerms,
            ],
        ]);
    }
/**
 * Get valid school days (days with different checkin/checkout times)
 * Used only for display columns, NOT for counting working days
 */
private function getValidSchoolDays($cls_sec_id, $student_id = null)
{
    return $this->resolveWorkingDayNames((int) $cls_sec_id, $student_id);
}

private function getWorkingDays($cls_sec_id, $student_id = null)
{
    return $this->resolveWorkingDayNames((int) $cls_sec_id, $student_id);
}

private function getOffDays($cls_sec_id, $student_id = null)
{
    $campusId = $this->resolveCampusIdForTiming((int) $cls_sec_id, $student_id);
    if ($campusId <= 0 || $cls_sec_id <= 0) {
        return [];
    }

    $offDays = [];
    foreach (getSchoolTimingsForSections([(int) $cls_sec_id], $campusId) as $row) {
        if (isSchoolTimingOffDay($row)) {
            $offDays[] = $row['dayname'];
        }
    }

    return $offDays;
}

private function resolveCampusIdForTiming(int $clsSecId, $studentId = null): int
{
    if ($studentId) {
        $student = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', (int) $studentId)
            ->get()
            ->getRow();
        if ($student && ! empty($student->campus_id)) {
            return (int) $student->campus_id;
        }
    }

    $campusId = (int) session('member_campusid');
    if ($campusId > 0) {
        return $campusId;
    }

    if ($clsSecId > 0) {
        $section = $this->db->table('class_section')
            ->select('campus_id')
            ->where('cls_sec_id', $clsSecId)
            ->get()
            ->getRow();
        if ($section && ! empty($section->campus_id)) {
            return (int) $section->campus_id;
        }
    }

    return 0;
}

private function resolveWorkingDayNames(int $clsSecId, $studentId = null): array
{
    if ($clsSecId <= 0) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }

    $campusId = $this->resolveCampusIdForTiming($clsSecId, $studentId);
    if ($campusId <= 0) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }

    $dayMap = array_flip(schoolTimingWeekdayMap());
    $nums   = getWorkingWeekdayNumbersForSection($clsSecId, $campusId);
    if ($nums === []) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }

    $names = [];
    foreach ($nums as $num) {
        if (isset($dayMap[$num])) {
            $names[] = $dayMap[$num];
        }
    }

    return $names !== [] ? $names : ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
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
}