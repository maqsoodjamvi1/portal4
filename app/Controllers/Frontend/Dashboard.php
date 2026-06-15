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
        helper(['url', 'form', 'server', 'parent_portal', 'hifz', 'school']);
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

        $dashboardUnannouncedDatesheet = $active > 0 ? $this->getDashboardUnannouncedDatesheet($active) : ['show' => false];
        $dashboardLastAnnouncedResult    = $active > 0 ? $this->getDashboardLastAnnouncedExamResult($active) : ['exam' => null];
        $showHifzPortal = $active > 0 && campusHifzEnabled() && studentHifzActive($active) !== null;
        $campusId       = (int) ($campusInfo->campus_id ?? session()->get('member_campusid') ?? 0);
        $portalNotices  = $active > 0 ? parent_portal_get_notices($active, $campusId) : [];
        $hifzSummary    = $active > 0 ? parent_portal_hifz_summary($active) : null;

        $parentActionCenter = parent_portal_build_action_center(
            $totalUnpaidAmount,
            is_array($quizSchedule) ? $quizSchedule : [],
            is_array($todayDiary) ? $todayDiary : []
        );

        $actionCenterCount = count($parentActionCenter);
        $actionCenterFirstUrl = $actionCenterCount > 0
            ? ($parentActionCenter[0]['url'] ?? base_url('student/dashboard#alerts'))
            : base_url('student/dashboard#alerts');

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
            'dashboardUnannouncedDatesheet' => $dashboardUnannouncedDatesheet,
            'dashboardLastAnnouncedResult' => $dashboardLastAnnouncedResult,
            'showHifzPortal' => $showHifzPortal,
            'portalNotices' => $portalNotices,
            'hifzSummary' => $hifzSummary,
            'parentActionCenter' => $parentActionCenter,
            'actionCenterCount'  => $actionCenterCount,
            'actionCenterFirstUrl' => $actionCenterFirstUrl,
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
    return \parent_portal_get_children($parentId);
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
 * Get diary entries for a specific date (no weekend shifting).
 */
protected function getDiaryForDate(int $studentId, string $date): array
{
    $clsSecId = $this->getStudentClsSecId($studentId);
    if (!$clsSecId) {
        return [];
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

    $query = $this->db->query($sql, [$clsSecId, $date]);
    if (!$query) {
        return [];
    }

    $diaryEntries = $query->getResultArray();
    if (empty($diaryEntries)) {
        return [];
    }

    foreach ($diaryEntries as &$entry) {
        $did = (int)($entry['did'] ?? 0);

        $entry['diary_date'] = $date;
        $entry['diary_day_name'] = date('l', strtotime($date));

        $audioQuery = $this->db->table('student_audio_recordings')
            ->select('recording_id, audio_file_path, audio_duration, recording_date, status, teacher_feedback, rating')
            ->where('student_id', $studentId)
            ->where('class_dairy_id', $did)
            ->orderBy('recording_id', 'DESC')
            ->get();
        $entry['audio_recordings'] = ($audioQuery && $audioQuery->getResultArray()) ? $audioQuery->getResultArray() : [];

        $videoQuery = $this->db->table('student_video_recordings')
            ->select('recording_id, video_file_path, video_duration, recording_date, status, teacher_feedback, rating')
            ->where('student_id', $studentId)
            ->where('class_dairy_id', $did)
            ->orderBy('recording_id', 'DESC')
            ->get();
        $entry['video_recordings'] = ($videoQuery && $videoQuery->getResultArray()) ? $videoQuery->getResultArray() : [];

        $pictureQuery = $this->db->table('student_picture_recording')
            ->select('picture_id, picture_path, created_date, status, teacher_remarks, rating')
            ->where('student_id', $studentId)
            ->where('classdairy_id', $did)
            ->orderBy('picture_id', 'DESC')
            ->get();
        $entry['picture_recordings'] = ($pictureQuery && $pictureQuery->getResultArray()) ? $pictureQuery->getResultArray() : [];

        $entry['homework_formatted'] = !empty($entry['homework']) ? nl2br(esc($entry['homework'])) : '<em class="text-muted">No homework assigned.</em>';
        $entry['classwork_formatted'] = !empty($entry['classwork']) ? nl2br(esc($entry['classwork'])) : '<em class="text-muted">No classwork recorded.</em>';
        $entry['subject_name'] = $entry['subject_name'] ?? 'General';

        $entry['has_quiz'] = !empty($entry['quiz_id']);
        if ($entry['has_quiz']) {
            $entry['quiz_duration_minutes'] = ceil(($entry['time_limit_sec'] ?? 0) / 60);
        }

        $entry['requires_audio'] = (int)($entry['is_audio'] ?? 0) === 1;
        $entry['requires_video'] = (int)($entry['is_video'] ?? 0) === 1;
        $entry['requires_picture'] = (int)($entry['is_picture'] ?? 0) === 1;

        $entry['audio_caption'] = $entry['audio_caption'] ?? null;
        $entry['video_caption'] = $entry['video_caption'] ?? null;
        $entry['picture_caption'] = $entry['picture_caption'] ?? null;
    }

    return $diaryEntries;
}

/**
 * Academic session id for diary/term weeks: member session first, else from student's campus.
 */
protected function resolveAcademicSessionIdForDiary(int $studentId): int
{
    $sid = (int) (session('member_sessionid') ?? 0);
    if ($sid > 0) {
        return $sid;
    }

    $fromStudent = $this->getCurrentAcademicSessionIdForStudent($studentId);
    if ($fromStudent !== null && $fromStudent > 0) {
        return $fromStudent;
    }

    $campusSystemId = $this->getStudentCampusSystemId($studentId);
    if ($campusSystemId > 0) {
        $latest = $this->getLatestAcademicSessionIdForCampusSystem($campusSystemId);
        if ($latest !== null && $latest > 0) {
            return $latest;
        }
    }

    return 0;
}

/**
 * @return list<array<string,mixed>>
 */
private function buildTermDiaryIndexRows(int $systemId, int $sessionId): array
{
    if ($systemId <= 0 || $sessionId <= 0) {
        return [];
    }

    $termSessions = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date, t.name as term_name, t.short_name as term_short')
        ->join('terms t', 't.term_id = ts.term_id AND t.system_id = ts.system_id', 'left')
        ->where('ts.system_id', $systemId)
        ->where('ts.session_id', $sessionId)
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResultArray();

    if (empty($termSessions)) {
        return [];
    }

    $out = [];
    foreach ($termSessions as $ts) {
        $termSessionId = (int) ($ts['term_session_id'] ?? 0);
        if ($termSessionId <= 0) {
            continue;
        }

        $weeks = $this->db->table('term_weeks tw')
            ->select('tw.term_weeks_id, tw.week_no, tw.week_name, tw.start_date, tw.end_date')
            ->where('tw.system_id', $systemId)
            ->where('tw.term_session_id', $termSessionId)
            ->orderBy('tw.start_date', 'ASC')
            ->get()
            ->getResultArray();

        $weeksOut = [];
        foreach ($weeks as $w) {
            $start = (string) ($w['start_date'] ?? '');
            $end = (string) ($w['end_date'] ?? '');
            if ($start === '' || $end === '') {
                continue;
            }

            try {
                $cursor = new \DateTime($start);
                $endDt = new \DateTime($end);
            } catch (\Throwable $e) {
                continue;
            }
            $cursor->setTime(0, 0, 0);
            $endDt->setTime(0, 0, 0);

            $days = [];
            while ($cursor <= $endDt) {
                $days[] = [
                    'date' => $cursor->format('Y-m-d'),
                    'day_name' => $cursor->format('l'),
                    'day_short' => $cursor->format('D'),
                ];
                $cursor->modify('+1 day');
            }

            $weekName = trim((string) ($w['week_name'] ?? ''));
            $weekNo = (string) ($w['week_no'] ?? '');
            $weekLabel = $weekName !== '' ? $weekName : ('Week ' . $weekNo);

            $weeksOut[] = [
                'term_weeks_id' => (int) ($w['term_weeks_id'] ?? 0),
                'week_name' => $weekName,
                'week_label' => $weekLabel,
                'start_date' => $start,
                'end_date' => $end,
                'days' => $days,
            ];
        }

        $termName = trim((string) ($ts['term_name'] ?? ''));
        $termShort = trim((string) ($ts['term_short'] ?? ''));
        $termLabel = $termName !== '' ? $termName : ($termShort !== '' ? $termShort : ('Term #' . (int) ($ts['term_id'] ?? 0)));

        $out[] = [
            'term_session_id' => $termSessionId,
            'term_label' => $termLabel,
            'term_start' => (string) ($ts['start_date'] ?? ''),
            'term_end' => (string) ($ts['end_date'] ?? ''),
            'weeks' => $weeksOut,
        ];
    }

    return $out;
}

private function getStudentCampusSystemId(int $studentId): int
{
    if ($studentId <= 0) {
        return 0;
    }

    $row = $this->db->table('students s')
        ->select('c.system_id')
        ->join('campus c', 'c.campus_id = s.campus_id', 'left')
        ->where('s.student_id', $studentId)
        ->get()
        ->getRowArray();

    return (int) ($row['system_id'] ?? 0);
}

private function getLatestAcademicSessionIdForCampusSystem(int $systemId): ?int
{
    if ($systemId <= 0) {
        return null;
    }

    $session = $this->db->table('academic_session')
        ->select('session_id')
        ->where('system_id', $systemId)
        ->orderBy('start_date', 'DESC')
        ->limit(1)
        ->get()
        ->getRowArray();

    return isset($session['session_id']) ? (int) $session['session_id'] : null;
}

/**
 * Build Term -> Weeks -> Days index for the diary page (all terms in session).
 */
protected function getTermDiaryIndex(int $studentId): array
{
    $school = getSchoolInfo();
    $schoolSystemId = isset($school->system_id) ? (int) $school->system_id : 0;
    $campusSystemId = $this->getStudentCampusSystemId($studentId);
    $sessionId = $this->resolveAcademicSessionIdForDiary($studentId);

    $seen = [];
    $try = function (int $sys, int $sid) use (&$seen): array {
        if ($sys <= 0 || $sid <= 0) {
            return [];
        }
        $k = $sys . ':' . $sid;
        if (isset($seen[$k])) {
            return [];
        }
        $seen[$k] = true;

        return $this->buildTermDiaryIndexRows($sys, $sid);
    };

    $out = $try($schoolSystemId, $sessionId);
    if ($out !== []) {
        return $out;
    }

    if ($campusSystemId > 0 && $campusSystemId !== $schoolSystemId) {
        $out = $try($campusSystemId, $sessionId);
        if ($out !== []) {
            return $out;
        }
    }

    $latestSession = $campusSystemId > 0
        ? $this->getLatestAcademicSessionIdForCampusSystem($campusSystemId)
        : null;
    if ($latestSession !== null && $latestSession > 0 && $latestSession !== $sessionId) {
        if ($campusSystemId > 0) {
            $out = $try($campusSystemId, $latestSession);
            if ($out !== []) {
                return $out;
            }
        }
        if ($schoolSystemId > 0 && $schoolSystemId !== $campusSystemId) {
            $out = $try($schoolSystemId, $latestSession);
            if ($out !== []) {
                return $out;
            }
        }
    }

    return [];
}

/**
 * Calendar week (Mon–Sun) with diary entries per day (server-rendered).
 *
 * @return array{week_start:string,week_end:string,days:array<int,array{date:string,day_name:string,day_short:string,is_today:bool,entries:array}>}
 */
protected function getCurrentCalendarWeekDiaries(int $studentId): array
{
    $monday = new \DateTime('today');
    if ((int) $monday->format('N') !== 1) {
        $monday->modify('last monday');
    }
    $monday->setTime(0, 0, 0);

    $today = (new \DateTime('today'))->format('Y-m-d');
    $days = [];
    $cursor = clone $monday;
    for ($i = 0; $i < 7; $i++) {
        $d = $cursor->format('Y-m-d');
        $days[] = [
            'date' => $d,
            'day_name' => $cursor->format('l'),
            'day_short' => $cursor->format('D'),
            'is_today' => $d === $today,
            'entries' => $this->getDiaryForDate($studentId, $d),
        ];
        $cursor->modify('+1 day');
    }

    $weekEnd = (clone $monday)->modify('+6 days')->format('Y-m-d');

    return [
        'week_start' => $monday->format('Y-m-d'),
        'week_end' => $weekEnd,
        'days' => $days,
    ];
}

/**
 * Term weeks for the term session that contains today (single term block).
 *
 * @return list<array<string,mixed>>
 */
protected function getCurrentTermDiaryIndex(int $studentId): array
{
    $all = $this->getTermDiaryIndex($studentId);
    if ($all === []) {
        return [];
    }

    $today = date('Y-m-d');
    foreach ($all as $term) {
        $start = (string) ($term['term_start'] ?? '');
        $end = (string) ($term['term_end'] ?? '');
        if ($start !== '' && $end !== '' && $today >= $start && $today <= $end) {
            return [$term];
        }
    }

    // Fallback: term with max overlap with current calendar week
    $week = $this->getCurrentCalendarWeekDiaries($studentId);
    $ws = $week['week_start'] ?? $today;
    $we = $week['week_end'] ?? $today;
    $best = null;
    $bestScore = -1;
    foreach ($all as $term) {
        $start = (string) ($term['term_start'] ?? '');
        $end = (string) ($term['term_end'] ?? '');
        if ($start === '' || $end === '') {
            continue;
        }
        $overlapStart = max($start, $ws);
        $overlapEnd = min($end, $we);
        if ($overlapStart <= $overlapEnd) {
            $score = (int) ((new \DateTime($overlapEnd))->diff(new \DateTime($overlapStart))->days) + 1;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $term;
            }
        }
    }

    if ($best !== null) {
        return [$best];
    }

    $last = $all[count($all) - 1] ?? null;

    return $last !== null ? [$last] : [];
}

    /**
     * Term weeks for the current term only (`term_weeks` rows for that term session).
     *
     * @return list<array<string,mixed>>
     */
    protected function getTermWeeksForCurrentTerm(int $studentId): array
    {
        $terms = $this->getCurrentTermDiaryIndex($studentId);
        if (empty($terms[0]['weeks'])) {
            return [];
        }

        return $terms[0]['weeks'];
    }

    /**
     * Current term session date range only (no other terms).
     *
     * @return array{start:string,end:string,label:string}|null
     */
    protected function getCurrentTermDateBounds(int $studentId): ?array
    {
        $terms = $this->getCurrentTermDiaryIndex($studentId);
        if (empty($terms[0])) {
            return null;
        }
        $t = $terms[0];
        $start = (string) ($t['term_start'] ?? '');
        $end = (string) ($t['term_end'] ?? '');
        if ($start === '' || $end === '') {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => (string) ($t['term_label'] ?? ''),
        ];
    }

    /**
     * Diary page date from ?date= (Y-m-d), default today, clamped to current term.
     * When term bounds are missing (e.g. parent has no session in session), still honour ?date=
     * so Prev/Next and day links load the requested day instead of snapping to today.
     */
    protected function resolveParentDiaryViewDate(?array $bounds): string
    {
        $today = date('Y-m-d');
        $raw = $this->request->getGet('date');
        $viewDate = $today;
        if (is_string($raw) && trim($raw) !== '') {
            try {
                $viewDate = (new \DateTime(trim($raw)))->format('Y-m-d');
            } catch (\Throwable $e) {
                $viewDate = $today;
            }
        }

        if ($bounds === null) {
            try {
                $min = (new \DateTime($today))->modify('-400 days')->format('Y-m-d');
                $max = (new \DateTime($today))->modify('+400 days')->format('Y-m-d');
            } catch (\Throwable $e) {
                return $viewDate;
            }
            if ($viewDate < $min) {
                return $min;
            }
            if ($viewDate > $max) {
                return $max;
            }

            return $viewDate;
        }

        if ($viewDate < $bounds['start']) {
            return $bounds['start'];
        }
        if ($viewDate > $bounds['end']) {
            return $bounds['end'];
        }

        return $viewDate;
    }

    /**
     * ISO weekday numbers (1=Mon … 7=Sun) that are configured in `school_timings` with
     * check-in and check-out times that differ, for the student's section and campus active timing type.
     *
     * @return array<int, true> empty means fall back to Monday–Friday in the UI.
     */
    protected function getDiaryWorkingWeekdayNumbers(int $studentId): array
    {
        $clsSecId = $this->getStudentClsSecId($studentId);
        if ($clsSecId === null || $clsSecId <= 0) {
            return [];
        }

        $campusRow = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();
        $campusId = (int) ($campusRow['campus_id'] ?? 0);
        if ($campusId <= 0) {
            return [];
        }

        $nums = getWorkingWeekdayNumbersForSection((int) $clsSecId, $campusId);

        return $nums !== [] ? array_fill_keys($nums, true) : [];
    }

    /**
     * @param array<int, true> $workingMap
     */
    protected function isDiarySchoolWorkingYmd(string $ymd, array $workingMap): bool
    {
        try {
            $n = (int) (new \DateTime($ymd))->format('N');
        } catch (\Throwable $e) {
            return false;
        }

        if ($workingMap === []) {
            return $n >= 1 && $n <= 5;
        }

        return ! empty($workingMap[$n]);
    }

    /**
     * @param array<int, true> $workingMap
     */
    protected function annotateDiaryToolbarDayRow(array $day, string $viewDate, ?array $bounds, array $workingMap): ?array
    {
        $d = (string) ($day['date'] ?? '');
        if ($d === '' || ! $this->isDiarySchoolWorkingYmd($d, $workingMap)) {
            return null;
        }

        $enabled = true;
        if ($bounds !== null) {
            $enabled = ($d >= $bounds['start'] && $d <= $bounds['end']);
        }

        $abbrKeys = [
            1 => 'day_abbr_mon', 2 => 'day_abbr_tue', 3 => 'day_abbr_wed', 4 => 'day_abbr_thu',
            5 => 'day_abbr_fri', 6 => 'day_abbr_sat', 7 => 'day_abbr_sun',
        ];
        try {
            $n = (int) (new \DateTime($d))->format('N');
        } catch (\Throwable $e) {
            $n = 1;
        }
        $langKey = $abbrKeys[$n] ?? 'day_abbr_mon';
        $dayAbbr = lang('ParentPortal.' . $langKey);

        $ts = strtotime($d);
        $yr = $ts ? (int) date('Y', $ts) : 0;
        $curY = (int) date('Y');
        $dateShort = ($ts && $yr !== $curY) ? date('j M Y', $ts) : ($ts ? date('j M', $ts) : '');

        return array_merge($day, [
            'enabled' => $enabled,
            'is_today' => $d === date('Y-m-d'),
            'is_selected' => $d === $viewDate,
            'day_abbr' => $dayAbbr,
            'date_short' => $dateShort,
        ]);
    }

    /**
     * Prayer-style toolbar: Prev / Next move adjacent `term_weeks` rows; center pill = week name; one day row.
     *
     * @param array{start:string,end:string,label:string}|null $bounds
     *
     * @return array{
     *   week_pill:string,
     *   prev_url:?string,
     *   next_url:?string,
     *   week_start:string,
     *   week_end:string,
     *   range_label:string,
     *   days:list<array<string,mixed>>,
     *   used_term_weeks:bool
     * }
     */
    protected function buildDiaryTermWeekToolbar(int $studentId, string $viewDate, ?array $bounds): array
    {
        $base = base_url('student/dashboard/section/diary');
        $weeks = $this->getTermWeeksForCurrentTerm($studentId);

        if ($weeks === []) {
            return $this->buildDiaryCalendarWeekToolbarFallback($studentId, $viewDate, $bounds, $base);
        }

        $idx = -1;
        foreach ($weeks as $i => $w) {
            $s = (string) ($w['start_date'] ?? '');
            $e = (string) ($w['end_date'] ?? '');
            if ($s !== '' && $e !== '' && $viewDate >= $s && $viewDate <= $e) {
                $idx = $i;
                break;
            }
        }

        if ($idx < 0) {
            $idx = 0;
            for ($i = count($weeks) - 1; $i >= 0; $i--) {
                $s = (string) ($weeks[$i]['start_date'] ?? '');
                if ($s !== '' && $viewDate >= $s) {
                    $idx = $i;
                    break;
                }
            }
        }

        $cur = $weeks[$idx];
        $wStart = (string) ($cur['start_date'] ?? '');
        $wEnd = (string) ($cur['end_date'] ?? '');

        $weekNameDb = trim((string) ($cur['week_name'] ?? ''));
        $pill = $weekNameDb !== '' ? $weekNameDb : trim((string) ($cur['week_label'] ?? ''));
        if ($pill === '' && $wStart !== '') {
            $pill = $this->resolveTermWeekLabelForStudentMonday($studentId, $wStart);
        }

        $prevUrl = null;
        if ($idx > 0) {
            $ps = (string) ($weeks[$idx - 1]['start_date'] ?? '');
            if ($ps !== '') {
                $prevUrl = $base . '?date=' . rawurlencode($ps);
            }
        }

        $nextUrl = null;
        if ($idx < count($weeks) - 1) {
            $ns = (string) ($weeks[$idx + 1]['start_date'] ?? '');
            if ($ns !== '') {
                $nextUrl = $base . '?date=' . rawurlencode($ns);
            }
        }

        $workingMap = $this->getDiaryWorkingWeekdayNumbers($studentId);
        $daysOut = [];
        foreach ($cur['days'] ?? [] as $day) {
            if (! is_array($day)) {
                continue;
            }
            $row = $this->annotateDiaryToolbarDayRow($day, $viewDate, $bounds, $workingMap);
            if ($row !== null) {
                $daysOut[] = $row;
            }
        }

        return [
            'week_pill' => $pill,
            'prev_url' => $prevUrl,
            'next_url' => $nextUrl,
            'week_start' => $wStart,
            'week_end' => $wEnd,
            'range_label' => '',
            'days' => $daysOut,
            'used_term_weeks' => true,
        ];
    }

    /**
     * @param array{start:string,end:string,label:string}|null $bounds
     *
     * @return array<string,mixed>
     */
    private function buildDiaryCalendarWeekToolbarFallback(int $studentId, string $viewDate, ?array $bounds, string $base): array
    {
        try {
            $monday = new \DateTime($viewDate);
        } catch (\Throwable $e) {
            $monday = new \DateTime('today');
        }
        $monday->setTime(0, 0, 0);
        if ((int) $monday->format('N') !== 1) {
            $monday->modify('last monday');
        }

        $workingMap = $this->getDiaryWorkingWeekdayNumbers($studentId);
        $daysOut = [];
        $cursor = clone $monday;
        for ($i = 0; $i < 7; $i++) {
            $d = $cursor->format('Y-m-d');
            $day = [
                'date' => $d,
                'day_name' => $cursor->format('l'),
                'day_short' => $cursor->format('D'),
            ];
            $row = $this->annotateDiaryToolbarDayRow($day, $viewDate, $bounds, $workingMap);
            if ($row !== null) {
                $daysOut[] = $row;
            }
            $cursor->modify('+1 day');
        }

        $monStr = $monday->format('Y-m-d');
        $wEnd = (clone $monday)->modify('+6 days')->format('Y-m-d');
        $prevMon = (clone $monday)->modify('-7 days')->format('Y-m-d');
        $nextMon = (clone $monday)->modify('+7 days')->format('Y-m-d');

        $pill = $this->resolveTermWeekLabelForStudentMonday($studentId, $monStr);

        return [
            'week_pill' => $pill,
            'prev_url' => $base . '?date=' . rawurlencode($prevMon),
            'next_url' => $base . '?date=' . rawurlencode($nextMon),
            'week_start' => $monStr,
            'week_end' => $wEnd,
            'range_label' => '',
            'days' => $daysOut,
            'used_term_weeks' => false,
        ];
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

            $sc = $this->db->table('student_class sc')
                ->select('sc.cls_sec_id, cs.class_id')
                ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
                ->where('sc.student_id', $studentId)
                ->where('sc.status', 1)
                ->orderBy('sc.sc_id', 'DESC')
                ->get()
                ->getRowArray();
            if ($sc) {
                $this->session->set([
                    'student_id'       => (int) $studentId,
                    'cls_sec_id'       => (int) ($sc['cls_sec_id'] ?? 0),
                    'student_class_id' => (int) ($sc['class_id'] ?? 0),
                ]);
            }
        }

        $to = $this->request->getGet('to');
        if (is_string($to) && $to !== '') {
            $path = rawurldecode(trim($to));
            $path = trim(str_replace('\\', '/', $path), '/');
            if ($path !== '' && \function_exists('parent_portal_is_safe_return_path') && \parent_portal_is_safe_return_path($path)) {
                return redirect()->to(base_url($path));
            }
        }

        return redirect()->route('dashboard');
    }

    /**
     * Dedicated class diary page (action center and bookmarks).
     */
    public function classDiary()
    {
        return $this->parentSection('diary');
    }

    /**
     * Parent-only: BMI, diary, bag, prayers on their own pages (hub links here).
     */
    public function parentSection(string $segment)
    {
        helper('hifz');
        $allowed = ['bmi', 'diary', 'bag', 'prayers', 'hifz'];
        if (! in_array($segment, $allowed, true)) {
            return redirect()->route('dashboard');
        }

        $auth = $this->session->get('auth');
        if (! $auth || empty($auth['logged_in']) || ($auth['role'] ?? '') !== 'parent') {
            return redirect()->route('login');
        }

        $parentId = (int) $auth['user_id'];
        $children = $this->getChildrenWithCurrentClass($parentId);
        $active     = (int) ($this->session->get('active_student_id') ?? 0);
        if (! $active && ! empty($children)) {
            $active = (int) $children[0]['student_id'];
            $this->session->set('active_student_id', $active);
        }

        $schoolInfo = getSchoolInfo();
        $campusInfo = getCampusInfo();
        $studentInfo = $active > 0 ? $this->getStudentInfo($active) : null;
        $currentLanguage = $this->getCurrentLanguage();
        $isUrdu          = strtolower(trim((string) $currentLanguage)) === 'ur';

        $studentAge = 0;
        if ($studentInfo && ! empty($studentInfo->date_of_birth)) {
            $studentAge = (int) date_diff(date_create($studentInfo->date_of_birth), date_create('today'))->y;
        }
        $campusPrayerStartAge     = isset($campusInfo->prayer_tracking_start_age) ? (int) $campusInfo->prayer_tracking_start_age : 7;
        $campusPrayerMandatoryAge = isset($campusInfo->prayer_tracking_mandatory_age) ? (int) $campusInfo->prayer_tracking_mandatory_age : 10;
        $isEligibleForPrayer      = $studentAge >= $campusPrayerStartAge;
        $isMandatory              = $studentAge >= $campusPrayerMandatoryAge;

        if ($segment === 'prayers' && ! $isEligibleForPrayer) {
            return redirect()->route('dashboard')->with('error', 'Prayer tracking is not available for this age yet.');
        }

        if ($segment === 'hifz') {
            if (! campusHifzEnabled()) {
                return redirect()->route('dashboard')->with('error', lang('ParentPortal.hifz_not_available'));
            }
            if ($active <= 0 || studentHifzActive($active) === null) {
                return redirect()->route('dashboard')->with('error', lang('ParentPortal.hifz_not_enrolled'));
            }
        }

        $titles = [
            'bmi'     => lang('ParentPortal.section_bmi'),
            'diary'   => lang('ParentPortal.section_diary'),
            'bag'     => lang('ParentPortal.section_bag'),
            'prayers' => lang('ParentPortal.section_prayers'),
            'hifz'    => lang('ParentPortal.section_hifz'),
        ];

        $data = [
            'title'             => $titles[$segment] ?? 'Portal',
            'role'              => 'parent',
            'name'              => $auth['name'] ?? '',
            'schoolInfo'        => $schoolInfo,
            'campusInfo'        => $campusInfo,
            'children'          => $children,
            'activeStudentId'   => $active,
            'activeStudentName' => $this->getStudentName($active),
            'studentInfo'       => $studentInfo,
            'isUrdu'            => $isUrdu,
            'segment'           => $segment,
            'returnPath'        => 'student/dashboard/section/' . $segment,
            'isEligibleForPrayer' => $isEligibleForPrayer,
            'isMandatory'         => $isMandatory,
        ];

        switch ($segment) {
            case 'bmi':
                $bmiData = $active > 0 ? $this->getStudentBMI($active) : null;
                $data['bmiData']        = $bmiData;
                $data['bmiHistory']     = $active > 0 ? $this->getBMIHistory($active) : [];
                $data['bmiSuggestions'] = ($bmiData && ! empty($bmiData->bmi_category))
                    ? $this->getBMISuggestions($bmiData->bmi_category)
                    : null;
                break;

            case 'diary':
                $bounds = $active > 0 ? $this->getCurrentTermDateBounds($active) : null;
                $viewDate = $active > 0 ? $this->resolveParentDiaryViewDate($bounds) : date('Y-m-d');
                $data['diaryViewDate'] = $viewDate;
                $data['diaryViewDayName'] = date('l', strtotime($viewDate));
                $data['diaryEntries'] = $active > 0 ? $this->getDiaryForDate($active, $viewDate) : [];
                $data['diaryTermBounds'] = $bounds;
                $data['diaryWeekPicker'] = $active > 0
                    ? $this->buildDiaryTermWeekToolbar($active, $viewDate, $bounds)
                    : [
                        'week_pill' => '',
                        'prev_url' => null,
                        'next_url' => null,
                        'week_start' => '',
                        'week_end' => '',
                        'range_label' => '',
                        'days' => [],
                        'used_term_weeks' => false,
                    ];
                $base = base_url('student/dashboard/section/diary');
                $data['diaryNav'] = [
                    'today' => $base . '?date=' . rawurlencode(date('Y-m-d')),
                    'is_viewing_today' => $viewDate === date('Y-m-d'),
                ];
                $data['quizSchedule'] = $active > 0 ? $this->getQuizSchedule($active) : [];
                $data['todayDiary'] = [];
                $data['returnPath'] = 'student/dashboard/section/diary?date=' . rawurlencode($viewDate);
                break;

            case 'bag':
                $data['bagPackItems'] = $active > 0 ? $this->getBagPackItems($active) : [];
                break;

            case 'prayers':
                // flags already in $data
                break;

            case 'hifz':
                $sessionId = hifzStudentSessionId($active);
                $data['hifzData'] = (new \App\Libraries\HifzReportService())->getPortalData($active, $sessionId, 30);
                break;
        }

        return view('frontend/dashboard/parent_section', $data);
    }

    /**
     * AJAX: return diary HTML for a specific date.
     * GET params: student_id (required), date (YYYY-MM-DD required)
     */
    public function getDiaryByDate()
    {
        $auth = $this->session->get('auth');
        if (!$auth || empty($auth['logged_in']) || ($auth['role'] ?? '') !== 'parent') {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $studentId = (int) ($this->request->getGet('student_id') ?? 0);
        $date = (string) ($this->request->getGet('date') ?? '');
        if ($studentId <= 0 || $date === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $dt = new \DateTime($date);
        } catch (\Throwable $e) {
            $dt = null;
        }
        if (!$dt) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid date']);
        }
        $date = $dt->format('Y-m-d');

        // Verify student belongs to this parent
        $verify = $this->db->table('students')
            ->where('student_id', $studentId)
            ->where('parent_id', (int) $auth['user_id'])
            ->get()
            ->getRowArray();
        if (!$verify) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid student']);
        }

        $entries = $this->getDiaryForDate($studentId, $date);
        $dayName = (new \DateTime($date))->format('l');

        $html = view('frontend/dashboard/partials/diary_day_entries', [
            'activeStudentId' => $studentId,
            'diaryDate' => $date,
            'diaryDayName' => $dayName,
            'diaryEntries' => $entries,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'date' => $date,
            'html' => $html,
        ]);
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
 * Week label for prayer toolbar: `term_weeks.week_name` (same source as admin / diary index).
 * Uses school system + active member session first (matches getTermDiaryIndex), then campus fallback.
 */
private function resolveTermWeekLabelForStudentMonday(int $studentId, string $mondayYmd): string
{
    $school = function_exists('getSchoolInfo') ? getSchoolInfo() : null;
    $schoolSystemId = isset($school->system_id) ? (int) $school->system_id : 0;
    $sessionId = $this->resolveAcademicSessionIdForDiary($studentId);

    if ($schoolSystemId > 0 && $sessionId > 0) {
        $row = $this->db->table('term_weeks tw')
            ->select('tw.week_name, tw.week_no')
            ->join('terms_session ts', 'ts.term_session_id = tw.term_session_id', 'inner')
            ->where('tw.system_id', $schoolSystemId)
            ->where('ts.system_id', $schoolSystemId)
            ->where('ts.session_id', $sessionId)
            ->where('tw.start_date <=', $mondayYmd)
            ->where('tw.end_date >=', $mondayYmd)
            ->orderBy('tw.start_date', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!empty($row)) {
            $name = trim((string) ($row['week_name'] ?? ''));
            if ($name !== '') {
                return $name;
            }
            $wno = (int) ($row['week_no'] ?? 0);
            if ($wno > 0) {
                return 'Week ' . $wno;
            }
        }
    }

    // Fallback: campus system (older / alternate installs)
    $ctx = $this->db->table('students s')
        ->select('c.system_id')
        ->join('campus c', 'c.campus_id = s.campus_id', 'left')
        ->where('s.student_id', $studentId)
        ->get()
        ->getRowArray();

    $campusSystemId = isset($ctx['system_id']) ? (int) $ctx['system_id'] : 0;
    if ($campusSystemId > 0) {
        $row2 = $this->db->table('term_weeks tw')
            ->select('tw.week_name, tw.week_no')
            ->join('terms_session ts', 'ts.term_session_id = tw.term_session_id', 'inner')
            ->where('tw.system_id', $campusSystemId)
            ->where('tw.start_date <=', $mondayYmd)
            ->where('tw.end_date >=', $mondayYmd)
            ->where('ts.start_date <=', $mondayYmd)
            ->where('ts.end_date >=', $mondayYmd)
            ->orderBy('tw.start_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!empty($row2)) {
            $name = trim((string) ($row2['week_name'] ?? ''));
            if ($name !== '') {
                return $name;
            }
            $wno = (int) ($row2['week_no'] ?? 0);
            if ($wno > 0) {
                return 'Week ' . $wno;
            }
        }
    }

    return $this->resolvePrayerWeekFallbackLabel($mondayYmd);
}

private function resolvePrayerWeekFallbackLabel(string $mondayYmd): string
{
    try {
        $dt = new \DateTime($mondayYmd);
        $end = (clone $dt)->modify('+6 days');

        return $dt->format('M j') . ' – ' . $end->format('M j, Y');
    } catch (\Throwable $e) {
        return $mondayYmd;
    }
}

/**
 * Get prayer status for a full week (Monday to Sunday).
 * Query params:
 * - student_id (required)
 * - week_start (optional, YYYY-MM-DD; must be a Monday). Defaults to current week Monday.
 */
public function getPrayerWeekStatus()
{
    $studentId = (int) ($this->request->getGet('student_id') ?? 0);
    $weekStart = (string) ($this->request->getGet('week_start') ?? '');

    $auth = $this->session->get('auth');
    if (!$auth || (($auth['role'] ?? '') !== 'parent' && ($auth['role'] ?? '') !== 'student')) {
        return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
    }

    if ($studentId <= 0) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid student']);
    }

    // Verify student belongs to this parent (if parent role)
    if (($auth['role'] ?? '') === 'parent') {
        $verify = $this->db->table('students')
            ->where('student_id', $studentId)
            ->where('parent_id', (int) $auth['user_id'])
            ->get()
            ->getRow();

        if (!$verify) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid student']);
        }
    }

    // Compute current week Monday if not provided / invalid
    $dt = null;
    if ($weekStart !== '') {
        try {
            $dt = new \DateTime($weekStart);
        } catch (\Throwable $e) {
            $dt = null;
        }
    }

    if (!$dt) {
        $dt = new \DateTime('today');
    }
    $dt->setTime(0, 0, 0);
    $dayOfWeek = (int) $dt->format('N'); // 1=Mon ... 7=Sun
    if ($dayOfWeek !== 1) {
        $dt->modify('last monday');
    }

    $start = $dt->format('Y-m-d');
    $endDt = clone $dt;
    $endDt->modify('+6 days');
    $end = $endDt->format('Y-m-d');

    $rows = $this->db->table('student_prayer_tracking')
        ->select('prayer_date, fajr, dhuhr, asr, maghrib, isha, total_offered, is_completed')
        ->where('student_id', $studentId)
        ->where('prayer_date >=', $start)
        ->where('prayer_date <=', $end)
        ->orderBy('prayer_date', 'ASC')
        ->get()
        ->getResultArray();

    $byDate = [];
    foreach ($rows as $r) {
        $d = (string) ($r['prayer_date'] ?? '');
        if ($d === '') {
            continue;
        }
        $byDate[$d] = [
            'fajr' => (int) ($r['fajr'] ?? 0),
            'dhuhr' => (int) ($r['dhuhr'] ?? 0),
            'asr' => (int) ($r['asr'] ?? 0),
            'maghrib' => (int) ($r['maghrib'] ?? 0),
            'isha' => (int) ($r['isha'] ?? 0),
            'total_offered' => (int) ($r['total_offered'] ?? 0),
            'is_completed' => (int) ($r['is_completed'] ?? 0),
        ];
    }

    $days = [];
    $cursor = clone $dt;
    for ($i = 0; $i < 7; $i++) {
        $dateStr = $cursor->format('Y-m-d');
        $days[] = [
            'date' => $dateStr,
            'day_name' => $cursor->format('l'),
            'day_short' => $cursor->format('D'),
            'prayers' => $byDate[$dateStr] ?? [
                'fajr' => 0,
                'dhuhr' => 0,
                'asr' => 0,
                'maghrib' => 0,
                'isha' => 0,
                'total_offered' => 0,
                'is_completed' => 0,
            ],
        ];
        $cursor->modify('+1 day');
    }

    $weekLabel = $this->resolveTermWeekLabelForStudentMonday($studentId, $start);

    return $this->response->setJSON([
        'success' => true,
        'week_start' => $start,
        'week_end' => $end,
        'week_label' => $weekLabel,
        'week_name' => $weekLabel,
        'days' => $days,
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

/**
 * Academic session for student (same idea as Frontend\DatesheetController).
 */
private function getCurrentAcademicSessionIdForStudent(int $studentId): ?int
{
    $student = $this->db->table('students')
        ->select('campus_id')
        ->where('student_id', $studentId)
        ->get()
        ->getRowArray();

    if (!$student || empty($student['campus_id'])) {
        return null;
    }

    $campus = $this->db->table('campus')
        ->select('system_id')
        ->where('campus_id', (int) $student['campus_id'])
        ->get()
        ->getRowArray();

    if (!$campus || empty($campus['system_id'])) {
        return null;
    }

    $session = $this->db->table('academic_session')
        ->select('session_id')
        ->where('system_id', (int) $campus['system_id'])
        ->where('CURDATE() BETWEEN start_date AND end_date', null, false)
        ->orderBy('start_date', 'DESC')
        ->limit(1)
        ->get()
        ->getRowArray();

    return isset($session['session_id']) ? (int) $session['session_id'] : null;
}

/**
 * Student campus + class section for exam/datesheet scoping.
 */
private function getStudentCampusClsSecForExam(int $studentId): ?array
{
    $row = $this->db->table('students s')
        ->select('s.campus_id, sc.cls_sec_id')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
        ->where('s.student_id', $studentId)
        ->get()
        ->getRowArray();

    if (!$row || empty($row['campus_id'])) {
        return null;
    }

    return [
        'campus_id' => (int) $row['campus_id'],
        'cls_sec_id' => isset($row['cls_sec_id']) ? (int) $row['cls_sec_id'] : 0,
    ];
}

/**
 * Latest exam row for session + campus (status 0 or 1), ordered by eid DESC.
 */
private function getLatestExamForStudentSession(int $studentId): ?object
{
    $ctx = $this->getStudentCampusClsSecForExam($studentId);
    $sessionId = $this->getCurrentAcademicSessionIdForStudent($studentId);
    if (!$ctx || !$sessionId) {
        return null;
    }

    $campusId = $ctx['campus_id'];

    return $this->db->table('exam')
        ->where('session_id', $sessionId)
        ->groupStart()
            ->where('campus_id', $campusId)
            ->orWhere('campus_id', 0)
            ->orWhere('campus_id IS NULL', null, false)
        ->groupEnd()
        ->whereIn('status', [0, 1])
        ->orderBy('eid', 'DESC')
        ->get(1)
        ->getRow() ?: null;
}

/**
 * Datesheet for dashboard: only when the latest exam for the session is still unannounced.
 * Exam.status: 0 = Unannounced, 1 = Announced (same as admin Exams switch).
 *
 * @return array{show:bool,eid?:int,exam_name?:string,datesheet?:array,message?:string}
 */
private function getDashboardUnannouncedDatesheet(int $studentId): array
{
    $base = ['show' => false];
    $exam = $this->getLatestExamForStudentSession($studentId);
    if (!$exam || (int) $exam->status !== 0) {
        return $base;
    }

    $ctx = $this->getStudentCampusClsSecForExam($studentId);
    if (!$ctx || $ctx['cls_sec_id'] <= 0) {
        return [
            'show' => true,
            'eid' => (int) $exam->eid,
            'exam_name' => (string) ($exam->exam_name ?? ''),
            'datesheet' => [],
            'message' => 'Class not assigned.',
        ];
    }

    $eid = (int) $exam->eid;
    $datesheetData = $this->db->table('datesheet ds')
        ->select('ds.*, sub.subject_name, ss.subject_id')
        ->join('section_subjects ss', 'ss.sec_sub_id = ds.sec_sub_id AND ss.status = 1')
        ->join('allsubject sub', 'sub.sid = ss.subject_id')
        ->where('ds.eid', $eid)
        ->where('ds.cls_sec_id', $ctx['cls_sec_id'])
        ->where('ds.total_marks !=', 0)
        ->orderBy('ds.exam_date', 'ASC')
        ->get()
        ->getResultArray();

    $datesheet = [];
    foreach ($datesheetData as $row) {
        $d = $row['exam_date'];
        $datesheet[$d][] = $row;
    }

    return [
        'show' => true,
        'eid' => $eid,
        'exam_name' => (string) ($exam->exam_name ?? ''),
        'datesheet' => $datesheet,
        'message' => empty($datesheet) ? 'No datesheet rows published yet for this class.' : null,
    ];
}

/**
 * Latest announced exam (exam.status = 1) and compiled exam_results + per-subject marks if present.
 * Exam.status: 0 = Unannounced, 1 = Announced.
 *
 * @return array{exam: ?object, exam_result: ?object, subjects: array<int, array<string,mixed>>}
 */
private function getDashboardLastAnnouncedExamResult(int $studentId): array
{
    $out = ['exam' => null, 'exam_result' => null, 'subjects' => []];

    $ctx = $this->getStudentCampusClsSecForExam($studentId);
    $sessionId = $this->getCurrentAcademicSessionIdForStudent($studentId);
    if (!$ctx || !$sessionId) {
        return $out;
    }

    $campusId = $ctx['campus_id'];

    $exam = $this->db->table('exam')
        ->where('session_id', $sessionId)
        ->groupStart()
            ->where('campus_id', $campusId)
            ->orWhere('campus_id', 0)
            ->orWhere('campus_id IS NULL', null, false)
        ->groupEnd()
        ->where('status', 1)
        ->orderBy('eid', 'DESC')
        ->get(1)
        ->getRow();

    if (!$exam) {
        return $out;
    }

    $eid = (int) $exam->eid;
    $out['exam'] = $exam;

    $examResult = $this->db->table('exam_results')
        ->where('eid', $eid)
        ->where('student_id', $studentId)
        ->get(1)
        ->getRow();

    $out['exam_result'] = $examResult ?: null;

    if ($ctx['cls_sec_id'] > 0) {
        $subjects = $this->db->table('subject_results sr')
            ->select('sub.subject_name, sr.obtained_marks, ds.total_marks, ds.exam_date')
            ->join('datesheet ds', 'ds.eid = sr.eid AND ds.cls_sec_id = sr.cls_sec_id AND ds.sec_sub_id = sr.sec_sub_id', 'inner')
            ->join('section_subjects ss', 'ss.sec_sub_id = sr.sec_sub_id AND ss.status = 1', 'inner')
            ->join('allsubject sub', 'sub.sid = ss.subject_id', 'inner')
            ->where('sr.eid', $eid)
            ->where('sr.student_id', $studentId)
            ->orderBy('ds.exam_date', 'ASC')
            ->orderBy('sub.subject_name', 'ASC')
            ->get()
            ->getResultArray();
        $out['subjects'] = $subjects;
    }

    return $out;
}
}