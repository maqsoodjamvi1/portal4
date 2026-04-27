<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\Frontend\AuthModel;
use Config\Database;

class Dashboard extends BaseController
{
    protected $session;
    protected $db;
    protected $authModel;

    public function __construct()
    {
        $this->session   = session();
        $this->db        = Database::connect();
        $this->authModel = new AuthModel();
        helper(['url', 'form', 'server']); // Added 'server' helper
    }

   public function index()
{
    $auth = $this->session->get('auth');
    if (!$auth || empty($auth['logged_in'])) {
        return redirect()->route('login');
    }

    $role   = $auth['role'];
    $name   = $auth['name'] ?? '';
    $active = (int) ($this->session->get('active_student_id') ?? 0);
    $parentId = (int) $auth['user_id'];

    $schoolInfo = getSchoolInfo();
    $campusInfo = getCampusInfo();

    if ($role === 'parent') {
        $children = $this->getChildrenWithCurrentClass($parentId);

        if (!$active && !empty($children)) {
            $active = (int) $children[0]['student_id'];
            $this->session->set('active_student_id', $active);
        }

        // Get BMI data
        $bmiData = $active > 0 ? $this->getStudentBMI($active) : null;
        
        $bmiSuggestions = ($bmiData && !empty($bmiData->bmi_category)) ? $this->getBMISuggestions($bmiData->bmi_category) : null;

        $feeHistory = $this->getFeeHistoryForFamily($parentId);
        $currentWeekAttendance = $active > 0 ? $this->getCurrentWeekAttendance($active) : [];
        $todayDiary = $active > 0 ? $this->getTodayDiary($active) : [];
        $bagPackItems = $active > 0 ? $this->getBagPackItems($active) : [];
        $quizSchedule = $active > 0 ? $this->getQuizSchedule($active) : [];
        $currentClassInfo = $active > 0 ? $this->getCurrentClassInfo($active) : null;
        $studentInfo = $active > 0 ? $this->getStudentInfo($active) : null;
        $currentLanguage = $this->getCurrentLanguage();
        $bmiHistory = $active > 0 ? $this->getBMIHistory($active) : [];

        $totalUnpaidAmount = $active > 0 ? $this->getTotalUnpaidAmountForFamily($parentId) : ['monthly' => 0, 'other' => 0, 'total' => 0];

        return view('frontend/dashboard/parent', [
            'role'          => 'parent',
            'name'          => $name,
            'schoolInfo'    => $schoolInfo,
            'campusInfo'    => $campusInfo,
            'studentInfo'   => $studentInfo,
            'children'      => $children,
            'activeStudentId' => $active,
            'activeStudentName' => $this->getStudentName($active),
            'currentClassInfo' => $currentClassInfo,
            'feeHistory'    => $feeHistory,
            'totalUnpaidAmount' => $this->getTotalUnpaidAmountForFamily($parentId),
            'currentWeekAttendance' => $currentWeekAttendance,
            'todayDiary'    => $todayDiary,
            'bagPackItems'  => $bagPackItems,
            'quizSchedule'  => $quizSchedule,
            'currencySymbol' => $campusInfo->currency_code ?? 'Rs.',
            // BMI Data
            'bmiData' => $bmiData,
            'bmiHistory' => $bmiHistory,

            'bmiSuggestions' => $bmiSuggestions,
              'totalUnpaidAmount' => $totalUnpaidAmount,
        ]);
    }

    return view('frontend/dashboard/student', [
        'role'            => 'student',
        'name'            => $name,
        'activeStudentId' => $active,
    ]);
}

private function getCurrentLanguage()
{
    $session = session();
    $language = $session->get('language');
    if (!$language) {
        $language = $this->request->getCookie('lang') ?: 'en';
    }
    return $language;
}

    private function getStudentInfo(int $studentId)
{
    return $this->db->table('students')
        ->select('student_id, first_name, last_name, date_of_birth')
        ->where('student_id', $studentId)
        ->get()
        ->getRow();  // Returns object
}
    /**
     * Get children with current class information
     */
 protected function getChildrenWithCurrentClass(int $parentId): array
{
    $sql = "
        SELECT 
            s.student_id,
            s.first_name,
            s.last_name,
            s.date_of_birth,
            s.profile_photo,
            s.reg_no,
            c.class_name,
            sec.section_name,
            sc.cls_sec_id
        FROM students s
        JOIN student_class sc ON sc.student_id = s.student_id AND sc.status = 1
        JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        JOIN classes c ON c.class_id = cs.class_id
        LEFT JOIN sections sec ON sec.section_id = cs.section_id
        WHERE s.parent_id = ? AND s.status = '1'
        ORDER BY s.first_name ASC
    ";

    $rows = $this->db->query($sql, [$parentId])->getResultArray();

    $children = [];
    foreach ($rows as $row) {
        $photoFile = $row['profile_photo'] ?? '';
        $photoFile = ltrim((string)$photoFile, '/');
        
        // Build photo URL - check if in uploads or student_photos directory
        $photoUrl = getStudentPhotoUrl($row['profile_photo'] ?? '');// default
        if (!empty($photoFile)) {
            // Try different possible paths
            $possiblePaths = [
                'uploads/' . $photoFile,
                'student_photos/' . $photoFile,
                'system-logo/' . $photoFile
            ];
            
            foreach ($possiblePaths as $path) {
                $fullPath = FCPATH . $path;
                if (file_exists($fullPath)) {
                    $photoUrl = base_url($path);
                    break;
                }
            }
        }
        
        $children[] = [
            'student_id' => (int)$row['student_id'],
            'name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            'first_name' => $row['first_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'reg_no' => $row['reg_no'] ?? '',
            'class_name' => $row['class_name'] ?? '',
            'section_name' => $row['section_name'] ?? '',
            'class_display' => trim(($row['class_name'] ?? '') . ' ' . ($row['section_name'] ?? '')),
            'profile_photo_url' => $photoUrl,
            'cls_sec_id' => (int)$row['cls_sec_id']
        ];
    }

    return $children;
}
    /**
     * Get student name by ID
     */
    protected function getStudentName(int $studentId): string
    {
        $student = $this->db->table('students')
            ->select("CONCAT(first_name, ' ', last_name) as full_name")
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();
        
        return $student['full_name'] ?? 'Student';
    }

    /**
     * Get current class info for a student
     */
    protected function getCurrentClassInfo(int $studentId): ?array
    {
        $sql = "
            SELECT 
                c.class_name,
                sec.section_name,
                CONCAT(c.class_name, ' ', sec.section_name) as class_display
            FROM students s
            JOIN student_class sc ON sc.student_id = s.student_id AND sc.status = 1
            JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            JOIN classes c ON c.class_id = cs.class_id
            LEFT JOIN sections sec ON sec.section_id = cs.section_id
            WHERE s.student_id = ?
            LIMIT 1
        ";

        return $this->db->query($sql, [$studentId])->getRowArray();
    }

    /**
     * Get fee history for last 6 months
     */


private function getFeeHistoryForFamily(int $parentId): array
{
    $feeHistory = [];
    $currentDate = new \DateTime();
    
    // Get all children of this parent
    $children = $this->db->table('students')
        ->select('student_id')
        ->where('parent_id', $parentId)
        ->where('status', '1')
        ->get()
        ->getResultArray();
    
    if (empty($children)) {
        return [];
    }
    
    $studentIds = array_column($children, 'student_id');
    $studentIdsStr = implode(',', $studentIds);
    
    // Get last 6 months based on paid_date
    for ($i = 5; $i >= 0; $i--) {
        $monthDate = clone $currentDate;
        $monthDate->modify("-$i months");
        $paidMonth = $monthDate->format('Y-m');
        $monthName = $monthDate->format('F Y');
        $monthShort = $monthDate->format('M-y');
        
        // Query for monthly fee paid in this month (is_monthly_fee = 1)
        $monthlyQuery = $this->db->query("
            SELECT 
                SUM(fc.amount - fc.discount) AS monthly_paid
            FROM fee_chalan fc
            INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            WHERE fc.student_id IN ($studentIdsStr)
                AND DATE_FORMAT(fc.paid_date, '%Y-%m') = '{$paidMonth}'
                AND fc.status = 'paid'
                AND ft.is_monthly_fee = 1
        ");
        
        $monthlyPaid = 0;
        if ($monthlyQuery && $monthlyQuery->getRow()) {
            $monthlyPaid = (float)($monthlyQuery->getRow()->monthly_paid ?? 0);
        }
        
        // Query for other fees paid in this month (is_monthly_fee != 1)
        $otherQuery = $this->db->query("
            SELECT 
                SUM(fc.amount - fc.discount) AS other_paid
            FROM fee_chalan fc
            INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            WHERE fc.student_id IN ($studentIdsStr)
                AND DATE_FORMAT(fc.paid_date, '%Y-%m') = '{$paidMonth}'
                AND fc.status = 'paid'
                AND (ft.is_monthly_fee != 1 OR ft.is_monthly_fee IS NULL)
        ");
        
        $otherPaid = 0;
        if ($otherQuery && $otherQuery->getRow()) {
            $otherPaid = (float)($otherQuery->getRow()->other_paid ?? 0);
        }
        
        $feeHistory[] = [
            'month' => $paidMonth,
            'month_name' => $monthName,
            'month_short' => $monthShort,
            'monthly_paid' => $monthlyPaid,
            'monthly_paid_formatted' => number_format($monthlyPaid, 0),
            'other_paid' => $otherPaid,
            'other_paid_formatted' => number_format($otherPaid, 0),
            'total_paid' => $monthlyPaid + $otherPaid,
            'total_paid_formatted' => number_format($monthlyPaid + $otherPaid, 0),
            'children' => $children
        ];
    }
    
    return $feeHistory;
}

private function getTotalUnpaidAmountForFamily(int $parentId): array
{
    // Get all children of this parent
    $children = $this->db->table('students')
        ->select('student_id')
        ->where('parent_id', $parentId)
        ->where('status', '1')
        ->get()
        ->getResultArray();
    
    if (empty($children)) {
        return ['monthly' => 0, 'other' => 0, 'total' => 0];
    }
    
    $studentIds = array_column($children, 'student_id');
    $studentIdsStr = implode(',', $studentIds);
    
    // Single query to get all unpaid amounts
    $query = $this->db->query("
        SELECT 
            COALESCE(SUM(CASE WHEN ft.is_monthly_fee = 1 THEN fc.amount - fc.discount ELSE 0 END), 0) AS unpaid_monthly,
            COALESCE(SUM(CASE WHEN ft.is_monthly_fee != 1 OR ft.is_monthly_fee IS NULL THEN fc.amount - fc.discount ELSE 0 END), 0) AS unpaid_other,
            COALESCE(SUM(fc.amount - fc.discount), 0) AS total_unpaid
        FROM fee_chalan fc
        INNER JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
        WHERE fc.student_id IN ($studentIdsStr)
            AND fc.status = 'unpaid'
    ");
    
    $result = $query->getRow();
    
    $unpaidMonthly = (float)($result->unpaid_monthly ?? 0);
    $unpaidOther = (float)($result->unpaid_other ?? 0);
    
    return [
        'monthly' => $unpaidMonthly,
        'monthly_formatted' => number_format($unpaidMonthly, 0),
        'other' => $unpaidOther,
        'other_formatted' => number_format($unpaidOther, 0),
        'total' => $unpaidMonthly + $unpaidOther,
        'total_formatted' => number_format($unpaidMonthly + $unpaidOther, 0)
    ];
}
    /**
     * Get current week attendance (Monday to Friday)
     * Only shows days that are school days (checkin_time != checkout_time)
     */
protected function getCurrentWeekAttendance(int $studentId): array
{
    $today = new \DateTime();
    $today->setTime(0, 0, 0);
    $currentDate = $today->format('Y-m-d');
    
    // Find Monday of current week
    $dayOfWeek = (int)$today->format('N'); // 1=Monday, 7=Sunday
    $monday = clone $today;
    if ($dayOfWeek != 1) {
        $monday->modify('last monday');
    }
    
    $weekDates = [];
    $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    
    // Get student's cls_sec_id
    $clsSecId = $this->getStudentClsSecId($studentId);
    
    // Get school timings for this class/section
    $schoolTimings = $this->getSchoolTimings($clsSecId);
    
    // Build week days with dates
    for ($i = 0; $i < 5; $i++) {
        $date = clone $monday;
        $date->modify("+$i days");
        $dateStr = $date->format('Y-m-d');
        $dayName = $weekDays[$i];
        
        // Check if this is a school day
        $isSchoolDay = true;
        $timing = $schoolTimings[$dayName] ?? null;
        
        if (!$timing) {
            $isSchoolDay = false;
        } elseif ($timing['checkin_timing'] === $timing['checkout_timing']) {
            $isSchoolDay = false;
        }
        
        // Get attendance status for this date
        $attendance = $this->db->table('attendance')
            ->select('status')
            ->where('student_id', $studentId)
            ->where('date', $dateStr)
            ->get()
            ->getRowArray();
        
        $status = $attendance['status'] ?? null;
        
        // Determine display status (show original status)
        $displayStatus = '';
        $statusClass = '';
        
        if (!$isSchoolDay) {
            $displayStatus = 'OFF';
            $statusClass = 'bg-secondary';
        } elseif ($status === null) {
            // No attendance record found
            if ($dateStr < $currentDate) {
                $displayStatus = 'OFF';
                $statusClass = 'bg-secondary';
            } else {
                $displayStatus = '—';
                $statusClass = 'bg-light text-dark';
            }
        } else {
            // Show original status (P, LC, L, A, etc.)
            $displayStatus = strtoupper($status);
            
            // Set color class based on status (for visual indication)
            if (in_array(strtolower($status), ['present', 'p', 'lc', 'l'])) {
                $statusClass = 'bg-success';  // Green for present (including LC, L)
            } elseif (strtolower($status) === 'absent' || strtolower($status) === 'a') {
                $statusClass = 'bg-danger';   // Red for absent
            } else {
                $statusClass = 'bg-info';      // Blue for other
            }
        }
        
        $weekDates[] = [
            'date' => $dateStr,
            'day_name' => ucfirst($dayName),
            'day_short' => substr(ucfirst($dayName), 0, 3),
            'is_school_day' => $isSchoolDay,
            'status' => $displayStatus,
            'status_class' => $statusClass,
            'raw_status' => $status,
            'is_past_day' => ($dateStr < $currentDate)
        ];
    }
    
    // Calculate statistics - Count LC and L as present for percentage
    $workingDays = 0;
    $absentDays = 0;
    $presentDays = 0;
    
    foreach ($weekDates as $day) {
        // Only count days that have attendance records (working days)
        if ($day['is_school_day'] && $day['date'] <= $currentDate && $day['raw_status'] !== null) {
            $workingDays++;
            
            // Treat P, LC, L as present for percentage calculation
            if (in_array(strtolower($day['raw_status'] ?? ''), ['present', 'p', 'lc', 'l'])) {
                $presentDays++;
            } elseif (strtolower($day['raw_status'] ?? '') === 'absent' || strtolower($day['raw_status'] ?? '') === 'a') {
                $absentDays++;
            }
        }
    }
    
    return [
        'week_start' => $monday->format('Y-m-d'),
        'week_end' => $date->format('Y-m-d'),
        'week_days' => $weekDates,
        'working_days' => $workingDays,
        'present_days' => $presentDays,
        'absent_days' => $absentDays,
        'attendance_percentage' => $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 1) : 0
    ];
}
/**
 * Get ACTIVE school timings for a class/section
 * This checks which timing type is currently active based on dates
 */
/**
 * Get ACTIVE school timings for a class/section
 */
protected function getActiveSchoolTimings(?int $clsSecId): array
{
    if (!$clsSecId) {
        return [];
    }
    
    // Get timings for this class/section
    // If there are multiple type_ids, get the one with status=1 or the first one
    $timings = $this->db->table('school_timings st')
        ->select('st.dayname, st.checkin_timing, st.checkout_timing')
        ->where('st.cls_sec_id', $clsSecId)
        ->groupBy('st.dayname')
        ->get()
        ->getResultArray();
    
    $result = [];
    foreach ($timings as $timing) {
        $day = strtolower(trim($timing['dayname'] ?? ''));
        $result[$day] = [
            'checkin_timing' => $timing['checkin_timing'] ?? '00:00:00',
            'checkout_timing' => $timing['checkout_timing'] ?? '00:00:00'
        ];
    }
    
    return $result;
}

    /**
     * Get student's cls_sec_id
     */


    private function getStudentAge($studentId)
{
    $student = $this->db->table('students')
        ->select('date_of_birth, date_of_birth_age, db_status')
        ->where('student_id', $studentId)
        ->get()
        ->getRow();
    
    if (!$student) {
        return ['years' => 0, 'months' => 0, 'display' => '0 years'];
    }
    
    // Determine which date of birth to use
    $dob = null;
    if ($student->db_status == 1 && !empty($student->date_of_birth_age)) {
        $dob = new DateTime($student->date_of_birth_age);
    } elseif (!empty($student->date_of_birth)) {
        $dob = new DateTime($student->date_of_birth);
    } else {
        return ['years' => 0, 'months' => 0, 'display' => '0 years'];
    }
    
    $today = new DateTime();
    $diff = $dob->diff($today);
    
    $years = $diff->y;
    $months = $diff->m;
    
    // Build display string
    $display = '';
    if ($years > 0) {
        $display .= $years . ' year' . ($years > 1 ? 's' : '');
    }
    if ($months > 0) {
        if ($years > 0) $display .= ' ';
        $display .= $months . ' month' . ($months > 1 ? 's' : '');
    }
    if ($years == 0 && $months == 0) {
        $display = '0 years';
    }
    
    return [
        'years' => $years,
        'months' => $months,
        'display' => $display
    ];
}


    protected function getStudentClsSecId(int $studentId): ?int
    {
        $result = $this->db->table('student_class')
            ->select('cls_sec_id')
            ->where('student_id', $studentId)
            ->where('status', 1)
            ->orderBy('sc_id', 'DESC')
            ->get()
            ->getRowArray();
        
        return $result['cls_sec_id'] ?? null;
    }

    /**
     * Get school timings for a class/section
     */
    protected function getSchoolTimings(?int $clsSecId): array
    {
        if (!$clsSecId) {
            return [];
        }
        
        $timings = $this->db->table('school_timings')
            ->select('dayname, checkin_timing, checkout_timing')
            ->where('cls_sec_id', $clsSecId)
            ->get()
            ->getResultArray();
        
        $result = [];
        foreach ($timings as $timing) {
            $day = strtolower(trim($timing['dayname'] ?? ''));
            $result[$day] = [
                'checkin_timing' => $timing['checkin_timing'] ?? '00:00:00',
                'checkout_timing' => $timing['checkout_timing'] ?? '00:00:00'
            ];
        }
        
        return $result;
    }

    /**
     * Get today's diary entries
     */
   /**
 * Get today's diary entries
 */
protected function getTodayDiary(int $studentId): array
{
    $clsSecId = $this->getStudentClsSecId($studentId);
    if (!$clsSecId) {
        return [];
    }
    
    $today = date('Y-m-d');
    $dayOfWeek = date('N'); // 1=Monday, 5=Friday, 6=Saturday, 7=Sunday
    
    // Determine which date to show diary for
    $diaryDate = $today;
    
    // If today is Saturday (6) or Sunday (7), show Friday's diary
    if ($dayOfWeek == 6 || $dayOfWeek == 7) {
        $friday = new \DateTime($today);
        $friday->modify('last friday');
        $diaryDate = $friday->format('Y-m-d');
    }
    
    $sql = "
    SELECT 
        cd.did,
        cd.detail as homework,
        cd.other_detail as classwork,
        cd.video_url,
        cd.is_book,
        cd.is_notebook,
        cd.is_audio,
        cd.is_video,
        cd.is_picture,
        cd.audio_caption,
        cd.video_caption,
        cd.picture_caption,
        cd.quiz_id,
        ss.subject_id,
        sub.subject_name,
        q.title as quiz_title,
        q.time_limit_sec,
        q.questions_count
    FROM classdairy cd
    LEFT JOIN section_subjects ss ON ss.sec_sub_id = cd.sec_sub_id
    LEFT JOIN allsubject sub ON sub.sid = ss.subject_id
    LEFT JOIN quizzes q ON q.quiz_id = cd.quiz_id
    WHERE cd.cls_sec_id = ? 
        AND cd.date = ?
    ORDER BY sub.subject_name ASC
    ";
    
    $query = $this->db->query($sql, [$clsSecId, $diaryDate]);
    
    if (!$query) {
        return [];
    }
    
    $diaryEntries = $query->getResultArray();
    
    if (empty($diaryEntries)) {
        return [];
    }
    
    foreach ($diaryEntries as &$entry) {
        $did = (int)($entry['did'] ?? 0);
        
        // Add diary date info
        $entry['diary_date'] = $diaryDate;
        $entry['diary_day_name'] = date('l', strtotime($diaryDate));
        
        // Get audio recordings
        $audioQuery = $this->db->table('student_audio_recordings')
            ->select('recording_id, audio_file_path, audio_duration, recording_date, status, teacher_feedback, rating')
            ->where('student_id', $studentId)
            ->where('class_dairy_id', $did)
            ->orderBy('recording_id', 'DESC')
            ->get();
        
        $entry['audio_recordings'] = ($audioQuery && $audioQuery->getResultArray()) ? $audioQuery->getResultArray() : [];
        
        // Get video recordings
        $videoQuery = $this->db->table('student_video_recordings')
            ->select('recording_id, video_file_path, video_duration, recording_date, status, teacher_feedback, rating')
            ->where('student_id', $studentId)
            ->where('class_dairy_id', $did)
            ->orderBy('recording_id', 'DESC')
            ->get();
        
        $entry['video_recordings'] = ($videoQuery && $videoQuery->getResultArray()) ? $videoQuery->getResultArray() : [];

        // Get picture recordings
        $pictureQuery = $this->db->table('student_picture_recording')
            ->select('picture_id, picture_path, created_date, status, teacher_remarks, rating')
            ->where('student_id', $studentId)
            ->where('classdairy_id', $did)
            ->orderBy('picture_id', 'DESC')
            ->get();

        $entry['picture_recordings'] = ($pictureQuery && $pictureQuery->getResultArray()) ? $pictureQuery->getResultArray() : [];
        
        // Format homework and classwork
        $entry['homework_formatted'] = !empty($entry['homework']) ? nl2br(esc($entry['homework'])) : '<em class="text-muted">No homework assigned.</em>';
        $entry['classwork_formatted'] = !empty($entry['classwork']) ? nl2br(esc($entry['classwork'])) : '<em class="text-muted">No classwork recorded.</em>';
        $entry['subject_name'] = $entry['subject_name'] ?? 'General';
        
        // Quiz info - ADD THIS
        $entry['has_quiz'] = !empty($entry['quiz_id']);
        if ($entry['has_quiz']) {
            $entry['quiz_duration_minutes'] = ceil(($entry['time_limit_sec'] ?? 0) / 60);
        }
        
        // Recording requirements
        $entry['requires_audio'] = (int)($entry['is_audio'] ?? 0) === 1;
        $entry['requires_video'] = (int)($entry['is_video'] ?? 0) === 1;
        $entry['requires_picture'] = (int)($entry['is_picture'] ?? 0) === 1;

        // Captions from teacher
        $entry['audio_caption'] = $entry['audio_caption'] ?? null;
        $entry['video_caption'] = $entry['video_caption'] ?? null;
        $entry['picture_caption'] = $entry['picture_caption'] ?? null;
    }
    
    return $diaryEntries;
}

    /**
     * Get bag pack items for tomorrow
     * Uses is_book and is_notebook flags from classdairy
     */
   protected function getBagPackItems(int $studentId): array
{
    $clsSecId = $this->getStudentClsSecId($studentId);
    if (!$clsSecId) {
        return [];
    }
    
    $today = date('Y-m-d');
    $dayOfWeek = date('N'); // 1=Monday, 5=Friday, 6=Saturday, 7=Sunday
    
    // Determine bag pack date
    if ($dayOfWeek >= 5) {
        // Friday, Saturday, or Sunday - show Monday's bag pack
        $monday = new \DateTime($today);
        $monday->modify('next monday');
        $bagPackDate = $monday->format('Y-m-d');
    } else {
        // Monday to Thursday - show next day's bag pack
        $bagPackDate = date('Y-m-d', strtotime('+1 day'));
    }
    
    $sql = "
        SELECT 
            cd.is_book,
            cd.is_notebook,
            ss.subject_id,
            sub.subject_name,
            cd.detail as homework
        FROM classdairy cd
        JOIN section_subjects ss ON ss.sec_sub_id = cd.sec_sub_id
        JOIN allsubject sub ON sub.sid = ss.subject_id
        WHERE cd.cls_sec_id = ? 
            AND cd.date = ?
        ORDER BY sub.subject_name ASC
    ";
    
    $items = $this->db->query($sql, [$clsSecId, $bagPackDate])->getResultArray();
    
    $bagPack = [];
    foreach ($items as $item) {
        if ($item['is_book'] == 1) {
            $bagPack[] = [
                'type' => 'book',
                'subject' => $item['subject_name'],
                'item_name' => $item['subject_name'] . ' Book',
                'icon' => 'fa-book'
            ];
        }
        if ($item['is_notebook'] == 1) {
            $bagPack[] = [
                'type' => 'notebook',
                'subject' => $item['subject_name'],
                'item_name' => $item['subject_name'] . ' Notebook',
                'icon' => 'fa-pencil-alt'
            ];
        }
    }
    
    return $bagPack;
}
    /**
     * Get quiz schedule for the week
     */
    protected function getQuizSchedule(int $studentId): array
    {
        $clsSecId = $this->getStudentClsSecId($studentId);
        if (!$clsSecId) {
            return [];
        }
        
        $sql = "
            SELECT 
                qs.day_of_week,
                qs.schedule_time,
                sub.subject_name,
                q.title as quiz_title,
                q.quiz_id,
                q.time_limit_sec,
                q.questions_count
            FROM quiz_schedule qs
            JOIN quizzes q ON q.quiz_id = qs.quiz_id
            JOIN section_subjects ss ON ss.sec_sub_id = qs.sec_sub_id
            JOIN allsubject sub ON sub.sid = ss.subject_id
            WHERE qs.cls_sec_id = ? 
                AND qs.is_active = 1
                AND (qs.end_date IS NULL OR qs.end_date >= CURDATE())
            ORDER BY FIELD(qs.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')
        ";
        
        $schedule = $this->db->query($sql, [$clsSecId])->getResultArray();
        
        $dayMap = [
            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
            'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'
        ];
        
        foreach ($schedule as &$item) {
            $item['day_short'] = $dayMap[strtolower($item['day_of_week'])] ?? ucfirst(substr($item['day_of_week'], 0, 3));
            $item['time_formatted'] = $item['schedule_time'] ? date('g:i A', strtotime($item['schedule_time'])) : 'Anytime';
            $item['duration_minutes'] = ceil(($item['time_limit_sec'] ?? 0) / 60);
        }
        
        return $schedule;
    }

    /**
     * Handle audio recording upload
     */
 
    /**
     * Handle video recording upload - UPDATED for new table structure
     */

/**
 * Get active term session based on current date - FIXED
 */
protected function getActiveTermSession(int $studentId): ?array
{
    $sessionid = session('member_sessionid');
    $system_id = getSchoolInfo()->system_id ?? null;
    
    if (!$sessionid || !$system_id) {
        // Fallback: get any term session
        $query = $this->db->table('terms_session')
            ->select('term_session_id, term_id, start_date, end_date')
            ->limit(1)
            ->get();
        
        if ($query && $query->getResultArray()) {
            $row = $query->getRowArray();
            if ($row) {
                return $row;
            }
        }
        return null;
    }
    
    $today = date('Y-m-d');
    
    // Try to find current term
    $query = $this->db->table('terms_session')
        ->select('term_session_id, term_id, start_date, end_date')
        ->where('session_id', $sessionid)
        ->where('system_id', $system_id)
        ->where('start_date <=', $today)
        ->where('end_date >=', $today)
        ->get();
    
    if ($query && $query->getResultArray()) {
        $row = $query->getRowArray();
        if ($row) {
            return $row;
        }
    }
    
    // Fallback: get most recent term
    $query = $this->db->table('terms_session')
        ->select('term_session_id, term_id, start_date, end_date')
        ->where('session_id', $sessionid)
        ->where('system_id', $system_id)
        ->orderBy('start_date', 'DESC')
        ->limit(1)
        ->get();
    
    if ($query && $query->getResultArray()) {
        $row = $query->getRowArray();
        if ($row) {
            return $row;
        }
    }
    
    // Final fallback: get any term
    $query = $this->db->table('terms_session')
        ->select('term_session_id, term_id, start_date, end_date')
        ->limit(1)
        ->get();
    
    if ($query && $query->getResultArray()) {
        $row = $query->getRowArray();
        if ($row) {
            return $row;
        }
    }
    
    return null;
}

/**
 * Handle video recording upload - COMPLETE FIXED VERSION
 */


public function uploadAudioRecording()
    {
        $auth = $this->session->get('auth');
        if (!$auth || $auth['role'] !== 'parent') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $studentId = (int)($this->request->getPost('student_id') ?? 0);
        $diaryId = (int)($this->request->getPost('diary_id') ?? 0);
        
        // Verify student belongs to this parent
        $verify = $this->db->table('students')
            ->where('student_id', $studentId)
            ->where('parent_id', $auth['user_id'])
            ->get()
            ->getRowArray();
        
        if (!$verify) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid student']);
        }
        
        // Get diary information for the required fields
        $diaryInfo = $this->db->table('classdairy cd')
            ->select('cd.sec_sub_id, cd.cls_sec_id, cd.term_weeks_id')
            ->where('cd.did', $diaryId)
            ->get()
            ->getRowArray();
        
        if (!$diaryInfo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid diary entry']);
        }
        
        // Get active term session
        $termSession = $this->getActiveTermSession($studentId);
        $termSessionId = $termSession['term_session_id'] ?? null;
        
        if (!$termSessionId) {
            return $this->response->setJSON(['success' => false, 'message' => 'No active term session found']);
        }
        
        $audioFile = $this->request->getFile('audio_recording');
        if (!$audioFile || !$audioFile->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'No audio file uploaded']);
        }
        
        // Generate unique filename
        $filename = 'audio_' . $studentId . '_' . $diaryId . '_' . time() . '.webm';
        $uploadPath = ROOTPATH . 'public/uploads/audio/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        if ($audioFile->move($uploadPath, $filename)) {
            $filePath = 'uploads/audio/' . $filename;
            
            $this->db->table('student_audio_recordings')->insert([
                'student_id' => $studentId,
                'term_session_id' => $termSessionId,
                'cls_sec_id' => $diaryInfo['cls_sec_id'],
                'sec_sub_id' => $diaryInfo['sec_sub_id'],
                'term_weeks_id' => $diaryInfo['term_weeks_id'],
                'class_dairy_id' => $diaryId,
                'audio_file_path' => $filePath,
                'audio_duration' => (int)($this->request->getPost('duration') ?? 0),
                'recording_date' => date('Y-m-d H:i:s'),
                'status' => 'pending',
                'created_date' => date('Y-m-d H:i:s')
            ]);
            
            return $this->response->setJSON(['success' => true, 'message' => 'Audio uploaded successfully', 'file_path' => base_url($filePath)]);
        }
        
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to upload audio']);
    }



public function uploadVideoRecording()
{
    $auth = $this->session->get('auth');
    if (!$auth || $auth['role'] !== 'parent') {
        return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
    }
    
    $studentId = (int)($this->request->getPost('student_id') ?? 0);
    $diaryId = (int)($this->request->getPost('diary_id') ?? 0);
    
    // Verify student belongs to this parent
    $verify = $this->db->table('students')
        ->where('student_id', $studentId)
        ->where('parent_id', $auth['user_id'])
        ->get()
        ->getRowArray();
    
    if (!$verify) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid student']);
    }
    
    $videoFile = $this->request->getFile('video_recording');
    if (!$videoFile || !$videoFile->isValid()) {
        return $this->response->setJSON(['success' => false, 'message' => 'No video file uploaded']);
    }
    
    // Get diary information
    $diaryQuery = $this->db->table('classdairy cd')
        ->select('cd.sec_sub_id, cd.cls_sec_id, cd.term_weeks_id')
        ->where('cd.did', $diaryId)
        ->get();
    
    if (!$diaryQuery) {
        return $this->response->setJSON(['success' => false, 'message' => 'Database query failed for diary']);
    }
    
    $diaryInfo = $diaryQuery->getRowArray();
    
    if (!$diaryInfo) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid diary entry']);
    }
    
    // Get active term session
    $termSession = $this->getActiveTermSession($studentId);
    
    if (!$termSession || empty($termSession['term_session_id'])) {
        // Instead of failing, create a temporary term session or use a default
        // For now, let's try to get any term session
        $anyTerm = $this->db->table('terms_session')
            ->select('term_session_id')
            ->limit(1)
            ->get()
            ->getRowArray();
        
        if (!$anyTerm) {
            return $this->response->setJSON(['success' => false, 'message' => 'No term session found in system']);
        }
        $termSessionId = $anyTerm['term_session_id'];
    } else {
        $termSessionId = $termSession['term_session_id'];
    }
    
    // Generate unique filename
    $filename = 'video_' . $studentId . '_' . $diaryId . '_' . time() . '.webm';
    $uploadPath = ROOTPATH . 'public/uploads/videos/';
    
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    
    if ($videoFile->move($uploadPath, $filename)) {
        $filePath = 'uploads/videos/' . $filename;
        
        $insertData = [
            'student_id' => $studentId,
            'term_session_id' => $termSessionId,
            'cls_sec_id' => $diaryInfo['cls_sec_id'],
            'sec_sub_id' => $diaryInfo['sec_sub_id'],
            'term_weeks_id' => $diaryInfo['term_weeks_id'],
            'class_dairy_id' => $diaryId,
            'video_file_path' => $filePath,
            'video_duration' => (int)($this->request->getPost('duration') ?? 0),
            'recording_date' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'created_date' => date('Y-m-d H:i:s')
        ];
        
        $inserted = $this->db->table('student_video_recordings')->insert($insertData);
        
        if ($inserted) {
            $videoId = $this->db->insertID();
            
            // Fetch the inserted video record - WITH SAFETY CHECK
            $videoQuery = $this->db->table('student_video_recordings')
                ->where('video_id', $videoId)
                ->get();
            
            if ($videoQuery && $videoQuery->getResultArray()) {
                $newVideo = $videoQuery->getRowArray();
                
                if ($newVideo) {
                    return $this->response->setJSON([
                        'success' => true, 
                        'message' => 'Video uploaded successfully',
                        'video' => [
                            'video_id' => $newVideo['video_id'],
                            'video_file_path' => $newVideo['video_file_path'],
                            'video_url' => base_url($newVideo['video_file_path']),
                            'recording_date' => $newVideo['recording_date'],
                            'status' => $newVideo['status'],
                            'video_duration' => $newVideo['video_duration']
                        ]
                    ]);
                }
            }
            
            // If we can't fetch the video but insert succeeded
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Video uploaded successfully',
                'video' => [
                    'video_id' => $videoId,
                    'video_file_path' => $filePath,
                    'video_url' => base_url($filePath),
                    'recording_date' => date('Y-m-d H:i:s'),
                    'status' => 'pending',
                    'video_duration' => (int)($this->request->getPost('duration') ?? 0)
                ]
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to save to database']);
        }
    }
    
    return $this->response->setJSON(['success' => false, 'message' => 'Failed to move uploaded file']);
}


public function uploadPicture()
{
    $auth = $this->session->get('auth');
    if (!$auth || $auth['role'] !== 'parent') {
        return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
    }
    
    $studentId = (int)($this->request->getPost('student_id') ?? 0);
    $diaryId = (int)($this->request->getPost('diary_id') ?? 0);
    
    // Verify student belongs to this parent
    $verify = $this->db->table('students')
        ->where('student_id', $studentId)
        ->where('parent_id', $auth['user_id'])
        ->get()
        ->getRowArray();
    
    if (!$verify) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid student']);
    }
    
    // Get diary information
    $diaryInfo = $this->db->table('classdairy cd')
        ->select('cd.sec_sub_id, cd.cls_sec_id, cd.term_weeks_id')
        ->where('cd.did', $diaryId)
        ->get()
        ->getRowArray();
    
    if (!$diaryInfo) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid diary entry']);
    }
    
    // Get active term session
    $termSession = $this->getActiveTermSession($studentId);
    
    if (!$termSession || empty($termSession['term_session_id'])) {
        $anyTerm = $this->db->table('terms_session')
            ->select('term_session_id')
            ->limit(1)
            ->get()
            ->getRowArray();
        
        if (!$anyTerm) {
            return $this->response->setJSON(['success' => false, 'message' => 'No term session found']);
        }
        $termSessionId = $anyTerm['term_session_id'];
    } else {
        $termSessionId = $termSession['term_session_id'];
    }
    
    $pictureFile = $this->request->getFile('picture');
    if (!$pictureFile || !$pictureFile->isValid()) {
        return $this->response->setJSON(['success' => false, 'message' => 'No picture file uploaded']);
    }
    
    // Validate image type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    if (!in_array($pictureFile->getMimeType(), $allowedTypes)) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid file type']);
    }
    
    // Generate unique filename
    $extension = $pictureFile->getExtension();
    $filename = 'picture_' . $studentId . '_' . $diaryId . '_' . time() . '.' . $extension;
    $uploadPath = ROOTPATH . 'public/uploads/pictures/';
    
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    
    if ($pictureFile->move($uploadPath, $filename)) {
        $filePath = 'uploads/pictures/' . $filename;
        
        $insertData = [
            'student_id' => $studentId,
            'campus_id' => $verify['campus_id'],
            'classdairy_id' => $diaryId,
            'picture_path' => $filePath,
            'status' => 'pending',
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s')
        ];
        
        $inserted = $this->db->table('student_picture_recording')->insert($insertData);
        
        if ($inserted) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Picture uploaded successfully',
                'picture' => [
                    'picture_path' => $filePath,
                    'picture_url' => base_url($filePath)
                ]
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to save to database']);
        }
    }
    
    return $this->response->setJSON(['success' => false, 'message' => 'Failed to move uploaded file']);
}  // <-- Make sure this closing bracket exists

/**
 * Compress video using FFmpeg
 */
private function compressVideo($inputPath, $outputPath)
{
    // Check if FFmpeg is available
    $ffmpegPath = exec('which ffmpeg');
    if (empty($ffmpegPath)) {
        // Try common paths
        $possiblePaths = ['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg'];
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $ffmpegPath = $path;
                break;
            }
        }
    }
    
    if (empty($ffmpegPath) || !file_exists($ffmpegPath)) {
        log_message('debug', 'FFmpeg not found, skipping compression');
        return false;
    }
    
    // Compression settings for minimum size
    // -crf 28: higher compression (lower quality, 18-28 range)
    // -b:v 500k: limit video bitrate
    // -b:a 64k: limit audio bitrate
    // -vf scale=640:-2: resize to max width 640px
    $command = escapeshellcmd($ffmpegPath) . ' -i ' . escapeshellarg($inputPath) . 
               ' -c:v libx264 -crf 28 -preset ultrafast -b:v 500k' .
               ' -c:a aac -b:a 64k' .
               ' -vf scale=640:-2' .
               ' -movflags +faststart' .
               ' -y ' . escapeshellarg($outputPath) . ' 2>&1';
    
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
        $originalSize = filesize($inputPath);
        $compressedSize = filesize($outputPath);
        $ratio = round(($compressedSize / $originalSize) * 100, 2);
        log_message('debug', "Video compressed: {$originalSize} -> {$compressedSize} bytes ({$ratio}%)");
        return true;
    }
    
    log_message('error', 'FFmpeg compression failed: ' . implode("\n", $output));
    return false;
}
    /**
     * Handle video recording upload
     */
  
    /**
     * Switch active student
     */
    public function switchStudent(int $studentId)
    {
        $auth = $this->session->get('auth');
        if (!$auth || $auth['role'] !== 'parent') {
            return redirect()->route('dashboard');
        }

        $row = $this->db->table('students')
            ->select('student_id')
            ->where('student_id', $studentId)
            ->where('parent_id', (int) $auth['user_id'])
            ->get()
            ->getRowArray();

        if ($row) {
            $this->session->set('active_student_id', (int) $studentId);
        }
        return redirect()->route('dashboard');
    }




public function prayerTracking(Request $request)
{
    $student_id = auth()->user()->student_id;
    $student = Students::find($student_id);
    
    // Check if student should track prayers based on age
    $age = Carbon::parse($student->date_of_birth)->age;
    $campus = Campus::find($student->campus_id);
    $is_mandatory = $age >= $campus->prayer_tracking_mandatory_age;
    $is_eligible = $age >= $campus->prayer_tracking_start_age;
    
    if (!$is_eligible) {
        return view('student.prayer.not-eligible', compact('student', 'age'));
    }
    
    $today = date('Y-m-d');
    $todayPrayer = StudentPrayerTracking::firstOrNew([
        'student_id' => $student_id,
        'prayer_date' => $today
    ]);
    
    // Get last 7 days
    $weeklyPrayers = StudentPrayerTracking::where('student_id', $student_id)
        ->where('prayer_date', '>=', date('Y-m-d', strtotime('-7 days')))
        ->orderBy('prayer_date', 'desc')
        ->get();
    
    // Get monthly summary
    $monthlySummary = StudentPrayerTracking::where('student_id', $student_id)
        ->whereYear('prayer_date', date('Y'))
        ->whereMonth('prayer_date', date('m'))
        ->get();
    
    $totalDays = $monthlySummary->count();
    $completedDays = $monthlySummary->where('is_completed', 1)->count();
    $avgPercentage = $totalDays > 0 ? round(($completedDays / $totalDays) * 100) : 0;
    
    return view('student.prayer.index', compact(
        'student', 'todayPrayer', 'weeklyPrayers', 
        'monthlySummary', 'totalDays', 'completedDays', 
        'avgPercentage', 'age', 'is_mandatory'
    ));
}

  

  public function getPrayerStatus()
{
    $studentId = (int) $this->request->getGet('student_id');
    $date = $this->request->getGet('date') ?? date('Y-m-d');
    
    // Get student info to verify access
    $auth = $this->session->get('auth');
    if (!$auth || ($auth['role'] !== 'parent' && $auth['role'] !== 'student')) {
        return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
    }
    
    // Verify student belongs to this parent (if parent role)
    if ($auth['role'] === 'parent') {
        $verify = $this->db->table('students')
            ->where('student_id', $studentId)
            ->where('parent_id', $auth['user_id'])
            ->get()
            ->getRow();
        
        if (!$verify) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid student']);
        }
    }
    
    // Get prayer status for the date
    $prayer = $this->db->table('student_prayer_tracking')
        ->select('fajr, dhuhr, asr, maghrib, isha, total_offered, is_completed')
        ->where('student_id', $studentId)
        ->where('prayer_date', $date)
        ->get()
        ->getRow();
    
    if (!$prayer) {
        return $this->response->setJSON([
            'success' => true,
            'prayers' => [
                'fajr' => 0,
                'dhuhr' => 0,
                'asr' => 0,
                'maghrib' => 0,
                'isha' => 0,
                'total_offered' => 0,
                'is_completed' => 0
            ]
        ]);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'prayers' => [
            'fajr' => (int)$prayer->fajr,
            'dhuhr' => (int)$prayer->dhuhr,
            'asr' => (int)$prayer->asr,
            'maghrib' => (int)$prayer->maghrib,
            'isha' => (int)$prayer->isha,
            'total_offered' => (int)$prayer->total_offered,
            'is_completed' => (int)$prayer->is_completed
        ]
    ]);
}

/**
 * Save prayer status for a student
 */
public function savePrayer()
{
    $auth = $this->session->get('auth');
    if (!$auth || ($auth['role'] !== 'parent' && $auth['role'] !== 'student')) {
        return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
    }
    
    $studentId = (int) $this->request->getPost('student_id');
    $prayerDate = $this->request->getPost('prayer_date') ?? date('Y-m-d');
    $prayerName = $this->request->getPost('prayer_name');
    $value = (int) $this->request->getPost('value');
    
    // Verify student belongs to this parent (if parent role)
    if ($auth['role'] === 'parent') {
        $verify = $this->db->table('students')
            ->where('student_id', $studentId)
            ->where('parent_id', $auth['user_id'])
            ->get()
            ->getRow();
        
        if (!$verify) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid student']);
        }
    }
    
    // Get campus_id for the student
    $student = $this->db->table('students')
        ->select('campus_id')
        ->where('student_id', $studentId)
        ->get()
        ->getRow();
    
    if (!$student) {
        return $this->response->setJSON(['success' => false, 'message' => 'Student not found']);
    }
    
    // Check if record exists
    $existing = $this->db->table('student_prayer_tracking')
        ->where('student_id', $studentId)
        ->where('prayer_date', $prayerDate)
        ->get()
        ->getRow();
    
    if ($existing) {
        // Update existing record
        $this->db->table('student_prayer_tracking')
            ->where('prayer_id', $existing->prayer_id)
            ->update([
                $prayerName => $value,
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id' => $auth['user_id']
            ]);
    } else {
        // Create new record
        $insertData = [
            'student_id' => $studentId,
            'campus_id' => $student->campus_id,
            'prayer_date' => $prayerDate,
            'fajr' => 0,
            'dhuhr' => 0,
            'asr' => 0,
            'maghrib' => 0,
            'isha' => 0,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id' => $auth['user_id']
        ];
        $insertData[$prayerName] = $value;
        $this->db->table('student_prayer_tracking')->insert($insertData);
    }
    
    // Get updated totals
    $updated = $this->db->table('student_prayer_tracking')
        ->select('fajr, dhuhr, asr, maghrib, isha, total_offered, is_completed')
        ->where('student_id', $studentId)
        ->where('prayer_date', $prayerDate)
        ->get()
        ->getRow();
    
    return $this->response->setJSON([
        'success' => true,
        'message' => 'Prayer status updated',
        'prayers' => [
            'fajr' => (int)$updated->fajr,
            'dhuhr' => (int)$updated->dhuhr,
            'asr' => (int)$updated->asr,
            'maghrib' => (int)$updated->maghrib,
            'isha' => (int)$updated->isha,
            'total_offered' => (int)$updated->total_offered,
            'is_completed' => (int)$updated->is_completed
        ]
    ]);
}

/**
 * Get prayer statistics for a student
 */
public function getPrayerStats()
{
    $studentId = (int) $this->request->getGet('student_id');
    
    $auth = $this->session->get('auth');
    if (!$auth || ($auth['role'] !== 'parent' && $auth['role'] !== 'student')) {
        return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
    }
    
    // Verify student belongs to this parent (if parent role)
    if ($auth['role'] === 'parent') {
        $verify = $this->db->table('students')
            ->where('student_id', $studentId)
            ->where('parent_id', $auth['user_id'])
            ->get()
            ->getRow();
        
        if (!$verify) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid student']);
        }
    }
    
    // Get current week streak (Sunday to Saturday)
    $weekStart = date('Y-m-d', strtotime('last sunday'));
    if (date('N') == 7) { // If today is Sunday
        $weekStart = date('Y-m-d');
    }
    
    $weeklyQuery = $this->db->table('student_prayer_tracking')
        ->select('COUNT(*) as days_completed')
        ->where('student_id', $studentId)
        ->where('prayer_date >=', $weekStart)
        ->where('is_completed', 1)
        ->get()
        ->getRow();
    
    // Get current month streak
    $monthStart = date('Y-m-01');
    $monthlyQuery = $this->db->table('student_prayer_tracking')
        ->select('COUNT(*) as days_completed')
        ->where('student_id', $studentId)
        ->where('prayer_date >=', $monthStart)
        ->where('is_completed', 1)
        ->get()
        ->getRow();
    
    // Get total days tracked
    $totalQuery = $this->db->table('student_prayer_tracking')
        ->select('COUNT(*) as total_days')
        ->where('student_id', $studentId)
        ->where('is_completed', 1)
        ->get()
        ->getRow();
    
    return $this->response->setJSON([
        'success' => true,
        'weekly_streak' => (int)($weeklyQuery->days_completed ?? 0),
        'monthly_streak' => (int)($monthlyQuery->days_completed ?? 0),
        'total_days' => (int)($totalQuery->total_days ?? 0)
    ]);
}
/**
 * Get student BMI data
 */
private function getStudentBMI(int $studentId)
{
    return $this->db->table('students')
        ->select('height, weight, bmi, bmi_category, bmi_percentile, bmi_updated_date')
        ->where('student_id', $studentId)
        ->get()
        ->getRow();
}

/**
 * Get BMI history
 */

private function getBMIHistory(int $studentId)
{
    // Get all BMI history records
    $history = $this->db->table('bmi_history')
        ->select('height, weight, bmi, bmi_category, recorded_date')
        ->where('student_id', $studentId)
        ->orderBy('recorded_date', 'DESC')
        ->limit(6)
        ->get()
        ->getResultArray();
    
    // Group by month
    $groupedHistory = [];
    foreach ($history as $record) {
        $monthKey = date('Y-m', strtotime($record['recorded_date']));
        $monthName = date('M-y', strtotime($record['recorded_date'])); // Nov-26, Dec-26
        
        if (!isset($groupedHistory[$monthKey])) {
            $groupedHistory[$monthKey] = [
                'month' => $monthKey,
                'month_display' => $monthName,
                'height' => $record['height'],
                'weight' => $record['weight'],
                'bmi' => $record['bmi'],
                'bmi_category' => $record['bmi_category'],
                'recorded_date' => $record['recorded_date']
            ];
        } else {
            // If multiple records in same month, use the latest one
            if (strtotime($record['recorded_date']) > strtotime($groupedHistory[$monthKey]['recorded_date'])) {
                $groupedHistory[$monthKey] = [
                    'month' => $monthKey,
                    'month_display' => $monthName,
                    'height' => $record['height'],
                    'weight' => $record['weight'],
                    'bmi' => $record['bmi'],
                    'bmi_category' => $record['bmi_category'],
                    'recorded_date' => $record['recorded_date']
                ];
            }
        }
    }
    
    // Sort by month descending (latest first)
    krsort($groupedHistory);
    
    // Limit to last 6 months
    $groupedHistory = array_slice($groupedHistory, 0, 6, true);
    
    return array_values($groupedHistory);
}

/**
 * Get BMI suggestions based on category
 */
private function getBMISuggestions(string $bmiCategory)
{
    return $this->db->table('nutrition_suggestions')
        ->select('title, description, diet_tips, foods_to_eat, foods_to_avoid, exercise_suggestions, medical_advice')
        ->where('bmi_category', $bmiCategory)
        ->where('is_active', 1)
        ->orderBy('sort_order', 'ASC')
        ->limit(1)
        ->get()
        ->getRow();
}
}