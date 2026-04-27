<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;

class AttendanceMonthlyReport extends BaseController
{

    
    public function index()
    {
        check_permission('admin-attendance-monthly-report');

        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');

        $sessionData = [
            'campusid' => $campusId,
            'sessionid' => $sessionId
        ];

        $db = db_connect();

        $data['sessionData'] = $sessionData;
        $data['infostudents'] = $db->table('students')->get()->getResult();
        $data['classesinfo'] = $db->table('classes')->get()->getResult();

        $currentrole = currentUserRoles();
        if (in_array(5, $currentrole)) {
            $data['sectionsclassinfo'] = teacherSubjectSections();
        } else {
            $data['sectionsclassinfo'] = userClassSections();
        }

        return view('admin/attendance_monthlyreport', $data);
    }


public function get_students_byclass()
{
   $eid = $this->request->getPost('eid');
        $session_id = $this->request->getPost('session_id');
        $campus_id = $this->request->getPost('campus_id');
        $id = $this->request->getPost('section_id');
        $subject_id = $this->request->getPost('subject_id');
        $datevalue = $this->request->getPost('date');

        if (empty($id)) {
            echo "<div style='background:red;color:#fff;padding:5px;'>Please Select Section</div>";
            exit;
        }

        $timestamp = strtotime($datevalue);
        $month = date('m', $timestamp);
        $year = date('Y', $timestamp);

        $list = array();
        $d = date('d', strtotime('last day of this month', strtotime($datevalue)));

        for ($i = 1; $i <= $d; $i++) {
            $list[] = $datevalue . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        $data = array();
        $studentsList = '';

        $studentsList .= '<input type="hidden" name="campus_id"  value="' . $campus_id . '">';
        $studentsList .= '<input type="hidden" name="class_id"  value="' . $id . '">';

        {
            $db = \Config\Database::connect();

            $classstudents = $db->query("select * from student_class where status=1 and cls_sec_id = " . $id)->getResult();

            // Assuming getClassSection is a helper function or model method
            $sectionInfo = getClassSection($id);

            $classstudentsTotal = $db->query("select count(student_id) as totalStudents from student_class where status=1 and cls_sec_id = " . $id)->getRow();

            $timestamp = strtotime($datevalue);
            $monthyear = date('F Y', $timestamp);

            $studentsList .= '<div class="">
           <h1 style="text-align:center;font-size:24px;">Monthly Attendance Report</h1>
           <div class="row"><div class="col-lg-4"><h6 style="text-align:center;margin-top:0px;margin-bottom:20px;">' . $sectionInfo["sectionclassname"] . '</h6></div><div class="col-lg-4"><h6 style="text-align:center;margin-top:0px;margin-bottom:20px;">' . $monthyear . '</h6></div><div class="col-lg-4"><h6  style="margin-top:0px;margin-bottom:20px;text-align:center">Total Students: ' . $classstudentsTotal->totalStudents . '</h6></div></div><table class="table table-bordered" style="width:98%;">';
            $studentsList .= '<tr>';
            $studentsList .= '<td colspan="2" style=" vertical-align:middle;">Presents Of The Day</td>';
            foreach ($list as $key => $date) {

                $timestamp = strtotime($date);
                $currentday = strtotime(date('d-m-Y'));
                $month = date('m', $timestamp);
                $year = date('Y', $timestamp);
                if ($timestamp > $currentday) break;

                $resulttotalP = $db->query("select count(STATUS) as totalP from attendance where status = 'P' AND student_id IN(SELECT student_id FROM student_class WHERE status=1 AND cls_sec_id = " . $id . ") AND date = '" . $date . "'")->getRow();

                $studentsList .= '<td><span style=" background: green;display: block;color: #fff;text-align: center;margin: 0 auto;">' . $resulttotalP->totalP . '</span></td>';
            }
            $studentsList .= '</tr>';
            $studentsList .= '<tr>';
            $studentsList .= '<td colspan="2" style=" vertical-align:middle;">Absents Of The Day</td>';
            foreach ($list as $key => $date) {

                $timestamp = strtotime($date);
                $currentday = strtotime(date('d-m-Y'));
                $month = date('m', $timestamp);
                $year = date('Y', $timestamp);
                if ($timestamp > $currentday) break;

                $resulttotalA = $db->query("select count(STATUS) as totalA from attendance where status = 'A' AND student_id IN(SELECT student_id FROM student_class WHERE status=1 AND cls_sec_id=" . $id . ") AND date = '" . $date . "'")->getRow();

                $studentsList .= '<td><span style=" background: red;display: block;color: #fff;text-align: center;margin: 0 auto;">' . $resulttotalA->totalA . '</span></td>';
            }
            $studentsList .= '</tr>';
            $studentsList .= '<tr>';
            $studentsList .= '<td colspan="2" style=" vertical-align:middle;">Leaves Of The Day</td>';
            foreach ($list as $key => $date) {

                $timestamp = strtotime($date);
                $currentday = strtotime(date('d-m-Y'));
                $month = date('m', $timestamp);
                $year = date('Y', $timestamp);
                if ($timestamp > $currentday) break;

                $resulttotalL = $db->query("select count(STATUS) as totalL from attendance where status = 'L' AND student_id IN(SELECT student_id FROM student_class WHERE status=1 AND cls_sec_id = " . $id . ") AND date = '" . $date . "'")->getRow();

                $studentsList .= '<td><span style=" background: #ffc107;display: block;color: #000;text-align: center;margin: 0 auto;">' . $resulttotalL->totalL . '</span></td>';
            }
            $studentsList .= '</tr>';
            $studentsList .= '<tr style="background: #204d74;color: #fff;"><th style="width: 6%; text-align: center;">Photo</th><th style="width:15%;">Name</th>';
            foreach ($list as $key => $date) {
                $timestamp = strtotime($date);
                $currentday = strtotime(date('d-m-Y'));
                $daydate = date('d', $timestamp);
                $dayName = date('D', $timestamp);
                if ($timestamp > $currentday) break;
                $studentsList .= '<th>' . $daydate . '<br>' . substr($dayName, 0, -1) . '</th>';
            }
            $studentsList .= '<th>P</th><th>A</th><th>LC</th><th>EL</th><th>L</th></tr>';
            $i = 1;

            foreach ($classstudents as $row) {
                $builder = $db->table('students');
                $builder->where('student_id', $row->student_id);
                $studentsinfo = $builder->get()->getRow();
                if ($studentsinfo) {

                    $studentName = $studentsinfo->first_name . " " . $studentsinfo->last_name;

                    $imgurl = FCPATH . "uploads/" . $studentsinfo->profile_photo;
                    if ($studentsinfo->profile_photo) {
                        if (file_exists($imgurl)) {

                            $profile_photo = "<img style='width:50px;height:50px;text-align:center;display: block;border-radius: 30px;margin: 0 auto;' src='" . base_url("uploads/" . $studentsinfo->profile_photo) . "' >";
                        } else {

                            $profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
                        }
                    } else {

                        $profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
                    }

                    $studentsList .= '<tr><td style=" vertical-align:middle; word-break: break-word;"> ' . $profile_photo . '</td>';
                    $studentsList .= '<td style=" vertical-align:middle;">' . $studentName . '<br>' . $studentsinfo->reg_no . '</td>';
                    foreach ($list as $key => $date) {

                        $timestamp = strtotime($date);
                        $currentday = strtotime(date('d-m-Y'));
                        $month = date('m', $timestamp);
                        $year = date('Y', $timestamp);
                        if ($timestamp > $currentday) break;

                        $builder = $db->table('attendance');
                        $builder->where('student_id', $row->student_id);
                        $builder->where('date', $date);
                        $attendance_info = $builder->get()->getRow();
                        $attendance_status = '-';
                        if ($attendance_info) {
                            if (empty($attendance_info->el_duration) && empty($attendance_info->lc_duration)) {
                                if ($attendance_info->status == 'A') {
                                    $attendance_status = '<span style="background: red;display: block;color: #fff;width: 20px;
    text-align: center;margin: 0 auto;">' . $attendance_info->status . '</span>';
                                } else {
                                    if ($attendance_info->status == 'P') {
                                        $attendance_status = '<span style="display: block;width: 20px;
    text-align: center;margin: 0 auto;">' . $attendance_info->status . '</span>';
                                    }
                                }
                            } else {

                                if ($attendance_info->el_duration > 0 && empty($attendance_info->lc_duration)) {
                                    $attendance_status = '<span style=" background: #ffc107;display: block;color: #000;width: 20px;
    text-align: center;margin: 0 auto;">EL</span>';
                                }

                                if ($attendance_info->lc_duration > 0 && empty($attendance_info->el_duration)) {
                                    $attendance_status = '<span style=" background: #ffc107;display: block;color: #000;width: 20px;
    text-align: center;margin: 0 auto;">LC</span>';
                                }

                                if ($attendance_info->lc_duration > 0 && $attendance_info->el_duration > 0) {
                                    $attendance_status = '<span style=" background: #ffc107;display: block;color: #000;width: 20px;
    text-align: center;margin: 0 auto;">LE</span>';
                                }
                            }
                        }
                        $studentsList .= '<td>' . $attendance_status . '</td>';
                    }

                    $resultP = $db->query("select count(STATUS) as totalP from attendance where student_id =" . $row->student_id . " and STATUS = 'P' and Month(date) = " . $month . " and Year(date) =" . $year)->getRow();

                    $resultLC = $db->query("select count(STATUS) as totalLC from attendance where student_id =" . $row->student_id . " AND lc_duration > 0 AND STATUS = 'P' and Month(date) = " . $month . " and Year(date) =" . $year)->getRow();

                    $resultL = $db->query("select count(STATUS) as totalL from attendance where student_id =" . $row->student_id . " and STATUS = 'L' and Month(date) = " . $month . " and Year(date) =" . $year)->getRow();

                    $resultEL = $db->query("select count(STATUS) as totalEL from attendance where student_id =" . $row->student_id . " AND el_duration > 0 AND STATUS = 'P' and Month(date) = " . $month . " and Year(date) =" . $year)->getRow();

                    $resultA = $db->query("select count(STATUS) as totalA from attendance where student_id =" . $row->student_id . " and STATUS = 'A' and Month(date) = " . $month . " and Year(date) =" . $year)->getRow();

                    $studentsList .= '<td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">' . $resultP->totalP . '</span></td>
    <td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">' . $resultA->totalA . '</span></td>
    <td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">' . $resultLC->totalLC . '</span></td>
    <td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">' . $resultEL->totalEL . '</span></td>
    <td><span style=" background: #204d74;display: block;color: #fff;width: 20px;text-align: center;margin: 0 auto;border-radius: 10px;">' . $resultL->totalL . '</span></td></tr>';
                }
                $i++;
            }
        }
        $studentsList .= '</table></div><script>
$(function(){
$(".clockpicker").clockpicker();
});    
</script>';

        return $this->response->setBody($studentsList);
    }

// Add this helper method to get attendance data

public function getStudentAttendanceData()
{
    $student_id = $this->request->getPost('student_id');
    $session_id = $this->request->getPost('session_id');
    
    if (empty($student_id) || empty($session_id)) {
        return $this->response->setJSON(['error' => 'Missing parameters']);
    }
    
    $db = \Config\Database::connect();
    
    $schoolinfo  = getSchoolInfo();
    // Get session start and end dates
    $session = $db->table('academic_session')
                 ->where('session_id', $session_id)
                 ->where('system_id', $schoolinfo->system_id)
                 ->get()
                 ->getRow();
    
    if (!$session) {
        return $this->response->setJSON(['error' => 'Session not found']);
    }
    
    // Get all attendance for this student in this session
    $attendance = $db->query("
        SELECT DATE_FORMAT(date, '%Y-%m-%d') as date, status 
        FROM attendance 
        WHERE student_id = ? 
        AND date >= ? 
        AND date <= ?
        ORDER BY date
    ", [$student_id, $session->start_date, $session->end_date])->getResult();
    
    // Organize attendance by month and day
    $attendanceData = [];
    foreach ($attendance as $record) {
        $date = \DateTime::createFromFormat('Y-m-d', $record->date);
        $month = $date->format('F'); // Full month name
        $day = $date->format('d');   // Day without leading zero
        
        if (!isset($attendanceData[$month])) {
            $attendanceData[$month] = [];
        }
        $attendanceData[$month][$day] = $record->status;
    }
    
    return $this->response->setJSON($attendanceData);
}


public function getStudentsBySection()
{
    $section_id = $this->request->getPost('section_id');
    
    $db = \Config\Database::connect();
    
    $students = $db->query("
        SELECT s.student_id, s.first_name, s.last_name, s.reg_no, s.father_name, s.profile_photo
        FROM students s 
        INNER JOIN student_class sc ON s.student_id = sc.student_id 
        WHERE sc.cls_sec_id = ? AND sc.status = 1 
        ORDER BY s.first_name
    ", [$section_id])->getResult();
    
    return $this->response->setJSON(['students' => $students]);
}
public function studentSessionReport()
{
    check_permission('admin-attendance-monthly-report');
    
    $campusId = session('member_campusid');
    $session_id = session('member_sessionid');

    $db = \Config\Database::connect();
    
    // Get all academic sessions for dropdown
    $schoolinfo  = getSchoolInfo();
    
    // Get session start and end dates (single session for dates)
    $currentSession = $db->table('academic_session')
                 ->where('session_id', $session_id)
                 ->where('system_id', $schoolinfo->system_id)
                 ->get()
                 ->getRow();
    
    // Get ALL academic sessions for dropdown (this should be an array)
    $allSessions = $db->table('academic_session')
                    ->where('system_id', $schoolinfo->system_id)
                    ->orderBy('session_id', 'DESC')
                    ->get()
                    ->getResult();
    
    // Get class sections for optional filtering
    $currentrole = currentUserRoles();
    if (in_array(5, $currentrole)) {
        $sectionsclassinfo = teacherSubjectSections();
    } else {
        $sectionsclassinfo = userClassSections();
    }
    
    $data = [
        'currentSession' => $currentSession, // Single session object
        'sessions' => $allSessions, // Array of all sessions for dropdown
        'sectionsclassinfo' => $sectionsclassinfo,
        'campus_id' => $campusId,
        'session_id' => $session_id,
    ];
    
    return view('admin/attendance_monthly_report/student_session_report', $data);
}
// Add this new method to your controller for student search
public function getStudentInfo()
{
    $search_term = trim($this->request->getPost('term') ?? '');
    $cls_sec_id  = $this->request->getPost('flag');
    $campusid    = session('member_campusid');

    $builder = $this->db->table('students')
        ->select('
            students.student_id,
            CONCAT(students.first_name, " ", COALESCE(students.last_name, "")) AS student_name,
            students.reg_no,
            parents.f_name AS father_name,
            CONCAT(classes.class_name, " ", sections.section_name) AS section_name
        ')
        ->join('parents', 'parents.parent_id = students.parent_id', 'left')
        ->join('student_class', 'student_class.student_id = students.student_id AND student_class.status = 1', 'left')
        ->join('class_section', 'class_section.cls_sec_id = student_class.cls_sec_id', 'left')
        ->join('classes', 'classes.class_id = class_section.class_id', 'left')
        ->join('sections', 'sections.section_id = class_section.section_id', 'left')
        ->where('students.status', 1)
        ->where('students.campus_id', $campusid);

    // Faster search using FULLTEXT or LIKE fallback
    if ($search_term !== '') {
        $builder->groupStart()
            ->like('students.first_name', $search_term)
            ->orLike('students.last_name', $search_term)
            ->orLike('students.reg_no', $search_term)
            ->orLike('parents.f_name', $search_term)
            ->orLike('CONCAT(students.first_name, " ", students.last_name)', $search_term)
        ->groupEnd();
    }

    // Optional filter by class-section
    if ($cls_sec_id && is_numeric($cls_sec_id)) {
        $builder->where('student_class.cls_sec_id', $cls_sec_id);
    }

    $query = $builder->groupBy('students.student_id')
                     ->orderBy('students.first_name')
                     ->limit(50) // Limit results for better performance
                     ->get();

    $data = array_map(function ($row) {
        return [
            'id'   => $row->student_id,
            'text' => "{$row->student_name} (Reg: {$row->reg_no}) - c/o {$row->father_name} - {$row->section_name}"
        ];
    }, $query->getResult());

    return $this->response->setJSON($data);
}

public function getStudentDetails()
{
    $student_id = $this->request->getPost('student_id');
    
    if (empty($student_id)) {
        return $this->response->setJSON(['success' => false, 'message' => 'Student ID required']);
    }
    
    $db = \Config\Database::connect();
    
    $student = $db->table('students')
        ->select('
            students.*,
            CONCAT(students.first_name, " ", COALESCE(students.last_name, "")) AS full_name,
            parents.f_name AS father_name,
            CONCAT(classes.class_name, " ", sections.section_name) AS section_name
        ')
        ->join('parents', 'parents.parent_id = students.parent_id', 'left')
        ->join('student_class', 'student_class.student_id = students.student_id AND student_class.status = 1', 'left')
        ->join('class_section', 'class_section.cls_sec_id = student_class.cls_sec_id', 'left')
        ->join('classes', 'classes.class_id = class_section.class_id', 'left')
        ->join('sections', 'sections.section_id = class_section.section_id', 'left')
        ->where('students.student_id', $student_id)
        ->where('students.status', 1)
        ->get()
        ->getRow();
    
    if ($student) {
        // Check if profile photo exists
        $photo_path = FCPATH . 'uploads/' . ($student->profile_photo ?? '');
        $photo_url = ($student->profile_photo && file_exists($photo_path)) 
            ? base_url('uploads/' . $student->profile_photo)
            : base_url('assets/img/default-avatar.png');
        
        return $this->response->setJSON([
            'success' => true,
            'full_name' => $student->full_name,
            'reg_no' => $student->reg_no,
            'father_name' => $student->father_name,
            'section_name' => $student->section_name,
            'photo_url' => $photo_url
        ]);
    }
    
    return $this->response->setJSON(['success' => false, 'message' => 'Student not found']);
}

public function studentWiseSessionReport()
{
    $session_id = $this->request->getGet('session_id');
    $student_id = $this->request->getGet('student_id');
    $campus_id = session('member_campusid');
    $report_type = $this->request->getGet('report_type') ?? 'monthly';
    
    $db = \Config\Database::connect();
    
    // Get session information with dates
    $schoolinfo  = getSchoolInfo();
    // Get session start and end dates
    $session = $db->table('academic_session')
                 ->where('session_id', $session_id)
                 ->where('system_id', $schoolinfo->system_id)
                 ->get()
                 ->getRow();
    
    if (!$session) {
        return redirect()->back()->with('error', 'Session not found');
    }
    
    // Get student information
    $student = $db->table('students')
                 ->select('students.*, 
                          parents.f_name as father_name,
                          CONCAT(classes.class_name, " ", sections.section_name) as section_name')
                 ->join('parents', 'parents.parent_id = students.parent_id', 'left')
                 ->join('student_class', 'student_class.student_id = students.student_id AND student_class.status = 1', 'left')
                 ->join('class_section', 'class_section.cls_sec_id = student_class.cls_sec_id', 'left')
                 ->join('classes', 'classes.class_id = class_section.class_id', 'left')
                 ->join('sections', 'sections.section_id = class_section.section_id', 'left')
                 ->where('students.student_id', $student_id)
                 ->where('students.status', 1)
                 ->get()
                 ->getRow();
    
    if (!$student) {
        return redirect()->back()->with('error', 'Student not found');
    }
    
    // Get attendance data for the student within session dates
    $attendanceData = $this->getStudentAttendanceDataForSession($student_id, $session);
    
    // Get session months (only months that fall within session dates)
    $sessionMonths = $this->getSessionMonths($session);
    
    // Get holidays within session (optional)
    $holidays = $this->getHolidaysWithinSession($session->start_date, $session->end_date, $campus_id);
    
    $data = [
        'student' => $student,
        'session' => $session,
        'session_id' => $session_id,
        'campus_id' => $campus_id,
        'report_type' => $report_type,
        'attendanceData' => $attendanceData,
        'sessionMonths' => $sessionMonths,
        'holidays' => $holidays,
    ];
    
    // Load the appropriate view based on report type
    $view_name = ($report_type === 'detailed') 
        ? 'admin/attendance_monthly_report/student_detailed_report'
        : 'admin/attendance_monthly_report/student_wise_report';
    
    return view($view_name, $data);
}

// Helper method to get session months
private function getSessionMonths($session)
{
    if (empty($session->start_date) || empty($session->end_date)) {
        return [];
    }
    
    $start = new DateTime($session->start_date);
    $end = new DateTime($session->end_date);
    
    $months = [];
    $current = clone $start;
    
    // Set to first day of the month
    $current->modify('first day of this month');
    
    while ($current <= $end) {
        $monthName = $current->format('F'); // Full month name
        $year = $current->format('Y');
        $monthNum = $current->format('m');
        
        // Calculate actual days in this month that fall within session
        $monthStart = clone $current;
        $monthEnd = clone $current;
        $monthEnd->modify('last day of this month');
        
        // Adjust start/end if session doesn't cover full month
        if ($monthStart < $start) {
            $monthStart = clone $start;
        }
        if ($monthEnd > $end) {
            $monthEnd = clone $end;
        }
        
        // Get days in this month within session
        $daysInMonth = [];
        $dayCounter = clone $monthStart;
        while ($dayCounter <= $monthEnd) {
            $day = (int)$dayCounter->format('j'); // Day without leading zero
            $daysInMonth[] = $day;
            $dayCounter->modify('+1 day');
        }
        
        $months[$monthName] = [
            'year' => $year,
            'month_num' => $monthNum,
            'start_date' => $monthStart->format('Y-m-d'),
            'end_date' => $monthEnd->format('Y-m-d'),
            'days_in_session' => $daysInMonth,
            'total_days_in_session' => count($daysInMonth),
        ];
        
        // Move to next month
        $current->modify('+1 month')->modify('first day of this month');
    }
    
    return $months;
}

// Helper method to get holidays
private function getHolidaysWithinSession($start_date, $end_date, $campus_id)
{
    $db = \Config\Database::connect();
    
    $holidays = $db->table('holidays')
                  ->select('holiday_date')
                  ->where('holiday_date >=', $start_date)
                  ->where('holiday_date <=', $end_date)
                  
                  ->where('is_normal', 1)
                  ->get()
                  ->getResultArray();
    
    $holidayDates = [];
    foreach ($holidays as $holiday) {
        $date = new DateTime($holiday['holiday_date']);
        $month = $date->format('F');
        $day = (int)$date->format('j');
        
        if (!isset($holidayDates[$month])) {
            $holidayDates[$month] = [];
        }
        $holidayDates[$month][$day] = true;
    }
    
    return $holidayDates;
}

// Updated attendance data method
private function getStudentAttendanceDataForSession($student_id, $session)
{
    $db = \Config\Database::connect();
    
    // Get all attendance for this student in this session
    $attendance = $db->table('attendance')
                    ->select("DATE_FORMAT(date, '%Y-%m-%d') as attendance_date, status, 
                             el_duration, lc_duration, remarks")
                    ->where('student_id', $student_id)
                    ->where('date >=', $session->start_date)
                    ->where('date <=', $session->end_date)
                    ->orderBy('date', 'ASC')
                    ->get()
                    ->getResult();
    
    // Organize by month
    $organizedData = [];
    foreach ($attendance as $record) {
        $date = \DateTime::createFromFormat('Y-m-d', $record->attendance_date);
        $month = $date->format('F'); // Full month name
        $day = $date->format('j');   // Day without leading zero
        
        if (!isset($organizedData[$month])) {
            $organizedData[$month] = [];
        }
        
        // Determine status with special codes
        $status = $record->status;
        if ($status == 'P') {
            if ($record->el_duration > 0 && $record->lc_duration > 0) {
                $status = 'LE'; // Late & Early Leave
            } elseif ($record->el_duration > 0) {
                $status = 'EL'; // Early Leave
            } elseif ($record->lc_duration > 0) {
                $status = 'LC'; // Late Coming
            }
        }
        
        $organizedData[$month][$day] = [
            'status' => $status,
            'remarks' => $record->remarks
        ];
    }
    
    return $organizedData;
}
}
