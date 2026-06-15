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
   helper('school');
   $eid = $this->request->getPost('eid');
        $session_id = $this->request->getPost('session_id') ?: session('member_sessionid');
        $campus_id = $this->request->getPost('campus_id');
        $class_id = (int)$this->request->getPost('class_id');
        $id = (int)($this->request->getPost('cls_sec_id') ?? $this->request->getPost('section_id'));
        $subject_id = $this->request->getPost('subject_id');
        $datevalue = $this->request->getPost('date');

        if (empty($class_id) && empty($id)) {
            echo "<div style='background:red;color:#fff;padding:5px;'>Please Select Class</div>";
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
        $studentsList .= '<input type="hidden" name="class_id"  value="' . $class_id . '">';

        $db = \Config\Database::connect();
        if (!empty($id)) {
            $targetSectionIds = [$id];
        } else {
            $targetSectionIds = array_map(static function($row) {
                return (int)$row['cls_sec_id'];
            }, $db->table('class_section')
                ->select('cls_sec_id')
                ->where('class_id', $class_id)
                ->where('campus_id', (int)$campus_id)
                ->where('status', 1)
                ->get()
                ->getResultArray());
        }

        if (empty($targetSectionIds)) {
            return $this->response->setBody("<div style='background:#ffc107;color:#000;padding:8px;'>No sections found for selected class.</div>");
        }

        foreach ($targetSectionIds as $id) {

            $classstudents = $db->query("select * from student_class where status=1 and cls_sec_id = " . $id)->getResult();

            // Assuming getClassSection is a helper function or model method
            $sectionInfo = getClassSection($id);

            $classstudentsTotal = $db->query("select count(student_id) as totalStudents from student_class where status=1 and cls_sec_id = " . $id)->getRow();

            $timestamp = strtotime($datevalue);
            $monthyear = date('F Y', $timestamp);

            // Timings by day name for selected section
            $timingsByDay = [];
            $timingRows = getSchoolTimingsForSections([(int) $id], (int) $campus_id);
            foreach ($timingRows as $timing) {
                $timingsByDay[strtolower($timing['dayname'])] = $timing;
            }

            // Dates where at least one attendance record exists for section
            $attendanceDatesSql = "
                SELECT DISTINCT a.date
                FROM attendance a
                INNER JOIN student_class sc ON sc.student_id = a.student_id AND sc.status = 1
                WHERE sc.cls_sec_id = ?
                AND a.date LIKE ?
            ";
            $attendanceDatesParams = [(int)$id, $datevalue . '-%'];
            if (!empty($session_id)) {
                $attendanceDatesSql .= " AND sc.session_id = ?";
                $attendanceDatesParams[] = (int)$session_id;
            }

            $attendanceDatesRaw = $db->query($attendanceDatesSql, $attendanceDatesParams)->getResultArray();

            $attendanceDatesMap = [];
            foreach ($attendanceDatesRaw as $rowDate) {
                $attendanceDatesMap[$rowDate['date']] = true;
            }

            $permanentOffDays = [];
            $noRecordOffDays = [];
            $workingDates = [];

            foreach ($list as $eachDate) {
                $timestampDate = strtotime($eachDate);
                $currentday = strtotime(date('Y-m-d'));
                if ($timestampDate > $currentday) {
                    continue;
                }

                $dayName = strtolower(date('l', $timestampDate));
                $timingInfo = $timingsByDay[$dayName] ?? null;
                $isPermanentOff = true;

                if ($timingInfo) {
                    $checkin = trim((string)($timingInfo['checkin_timing'] ?? ''));
                    $checkout = trim((string)($timingInfo['checkout_timing'] ?? ''));
                    $isPermanentOff = ($checkin === '' || $checkout === '' || $checkin === $checkout);
                }

                if ($isPermanentOff) {
                    $permanentOffDays[] = $eachDate;
                    continue;
                }

                if (!isset($attendanceDatesMap[$eachDate])) {
                    $noRecordOffDays[] = $eachDate;
                    continue;
                }

                $workingDates[] = $eachDate;
            }

            $studentsList .= '<style>
            .monthly-report-wrap{overflow-x:auto;max-width:100%;border:1px solid #d6d9de}
            .monthly-report-table{width:max-content;min-width:100%;border-collapse:collapse}
            .monthly-report-table th,.monthly-report-table td{padding:3px 4px;text-align:center;vertical-align:middle}
            .monthly-report-table .student-photo-col{min-width:60px}
            .monthly-report-table .student-name-col{min-width:230px;max-width:230px;text-align:left}
            .monthly-report-table .day-col{min-width:34px;max-width:34px}
            .monthly-report-table .total-col{min-width:36px;max-width:36px}
            .monthly-report-table .student-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;font-weight:600}
            .monthly-report-table .student-reg{white-space:nowrap;font-size:11px;color:#555}
            .status-pill{display:inline-block;min-width:24px;padding:1px 4px;border-radius:3px;line-height:1.2;white-space:nowrap;font-size:11px}
            .status-pill.status-p{background:#e9ecef;color:#111}
            .status-pill.status-a{background:#dc3545;color:#fff}
            .status-pill.status-lx{background:#ffc107;color:#111}
            </style><div class="">
           <h1 style="text-align:center;font-size:24px;">Monthly Attendance Report</h1>
           <div class="row"><div class="col-lg-4"><h6 style="text-align:center;margin-top:0px;margin-bottom:20px;">' . $sectionInfo["sectionclassname"] . '</h6></div><div class="col-lg-4"><h6 style="text-align:center;margin-top:0px;margin-bottom:20px;">' . $monthyear . '</h6></div><div class="col-lg-4"><h6  style="margin-top:0px;margin-bottom:20px;text-align:center">Total Students: ' . $classstudentsTotal->totalStudents . '</h6></div></div>
           <div class="row" style="margin:0 0 12px 0;">
             <div class="col-lg-12">
               <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;">
                 <span style="background:#17a2b8;color:#fff;padding:6px 10px;border-radius:16px;font-size:12px;">Working Days: ' . count($workingDates) . '</span>
                 <span style="background:#6c757d;color:#fff;padding:6px 10px;border-radius:16px;font-size:12px;">Permanent Off (Timings Off): ' . count($permanentOffDays) . '</span>
                 <span style="background:#ffc107;color:#000;padding:6px 10px;border-radius:16px;font-size:12px;">No-Record Off: ' . count($noRecordOffDays) . '</span>
               </div>
             </div>
           </div>
           <div class="monthly-report-wrap"><table class="table table-bordered monthly-report-table">';
            $studentsList .= '<tr>';
            $studentsList .= '<td colspan="2" style=" vertical-align:middle;">Presents Of The Day</td>';
            $presentDayTotal = 0;
            foreach ($workingDates as $key => $date) {

                $timestamp = strtotime($date);
                $currentday = strtotime(date('Y-m-d'));
                $month = date('m', $timestamp);
                $year = date('Y', $timestamp);
                if ($timestamp > $currentday) break;

                $resulttotalP = $db->query("select count(STATUS) as totalP from attendance where status = 'P' AND student_id IN(SELECT student_id FROM student_class WHERE status=1 AND cls_sec_id = " . $id . ") AND date = '" . $date . "'")->getRow();

                $presentDayTotal += (int)$resulttotalP->totalP;
                $studentsList .= '<td class="day-col"><span class="status-pill status-p" style="background:green;color:#fff;">' . $resulttotalP->totalP . '</span></td>';
            }
            $studentsList .= '<td class="total-col"><span class="status-pill status-p" style="background:#204d74;color:#fff;">' . $presentDayTotal . '</span></td>';
            $studentsList .= '<td class="total-col"></td><td class="total-col"></td><td class="total-col"></td><td class="total-col"></td>';
            $studentsList .= '</tr>';
            $studentsList .= '<tr>';
            $studentsList .= '<td colspan="2" style=" vertical-align:middle;">Absents Of The Day</td>';
            $absentDayTotal = 0;
            foreach ($workingDates as $key => $date) {

                $timestamp = strtotime($date);
                $currentday = strtotime(date('Y-m-d'));
                $month = date('m', $timestamp);
                $year = date('Y', $timestamp);
                if ($timestamp > $currentday) break;

                $resulttotalA = $db->query("select count(STATUS) as totalA from attendance where status = 'A' AND student_id IN(SELECT student_id FROM student_class WHERE status=1 AND cls_sec_id=" . $id . ") AND date = '" . $date . "'")->getRow();

                $absentDayTotal += (int)$resulttotalA->totalA;
                $studentsList .= '<td class="day-col"><span class="status-pill status-a">' . $resulttotalA->totalA . '</span></td>';
            }
            $studentsList .= '<td class="total-col"></td><td class="total-col"><span class="status-pill status-p" style="background:#204d74;color:#fff;">' . $absentDayTotal . '</span></td>';
            $studentsList .= '<td class="total-col"></td><td class="total-col"></td><td class="total-col"></td>';
            $studentsList .= '</tr>';
            $studentsList .= '<tr>';
            $studentsList .= '<td colspan="2" style=" vertical-align:middle;">Leaves Of The Day</td>';
            $leaveDayTotal = 0;
            foreach ($workingDates as $key => $date) {

                $timestamp = strtotime($date);
                $currentday = strtotime(date('Y-m-d'));
                $month = date('m', $timestamp);
                $year = date('Y', $timestamp);
                if ($timestamp > $currentday) break;

                $resulttotalL = $db->query("select count(STATUS) as totalL from attendance where status = 'L' AND student_id IN(SELECT student_id FROM student_class WHERE status=1 AND cls_sec_id = " . $id . ") AND date = '" . $date . "'")->getRow();

                $leaveDayTotal += (int)$resulttotalL->totalL;
                $studentsList .= '<td class="day-col"><span class="status-pill status-lx">' . $resulttotalL->totalL . '</span></td>';
            }
            $studentsList .= '<td class="total-col"></td><td class="total-col"></td><td class="total-col"></td><td class="total-col"></td><td class="total-col"><span class="status-pill status-p" style="background:#204d74;color:#fff;">' . $leaveDayTotal . '</span></td>';
            $studentsList .= '</tr>';
            $reportMonth = (int)date('m', strtotime($datevalue . '-01'));
            $reportYear = (int)date('Y', strtotime($datevalue . '-01'));

            $studentsList .= '<tr style="background: #204d74;color: #fff;"><th class="student-photo-col">Photo</th><th class="student-name-col">Student</th>';
            foreach ($workingDates as $key => $date) {
                $timestamp = strtotime($date);
                $currentday = strtotime(date('Y-m-d'));
                $daydate = date('d', $timestamp);
                $dayName = date('D', $timestamp);
                if ($timestamp > $currentday) break;
                $studentsList .= '<th class="day-col">' . $daydate . '<br>' . substr($dayName, 0, -1) . '</th>';
            }
            $studentsList .= '<th class="total-col">P</th><th class="total-col">A</th><th class="total-col">LC</th><th class="total-col">EL</th><th class="total-col">L</th></tr>';
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

                    $studentsList .= '<tr><td class="student-photo-col"> ' . $profile_photo . '</td>';
                    $studentsList .= '<td class="student-name-col" title="' . esc($studentName . ' - ' . $studentsinfo->reg_no) . '"><span class="student-name">' . esc($studentName) . '</span><span class="student-reg">' . esc($studentsinfo->reg_no) . '</span></td>';
                    foreach ($workingDates as $key => $date) {

                        $timestamp = strtotime($date);
                        $currentday = strtotime(date('Y-m-d'));
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
                                    $attendance_status = '<span class="status-pill status-a">' . $attendance_info->status . '</span>';
                                } else {
                                    if ($attendance_info->status == 'P') {
                                        $attendance_status = '<span class="status-pill status-p">' . $attendance_info->status . '</span>';
                                    }
                                }
                            } else {

                                if ($attendance_info->el_duration > 0 && empty($attendance_info->lc_duration)) {
                                    $attendance_status = '<span class="status-pill status-lx">EL</span>';
                                }

                                if ($attendance_info->lc_duration > 0 && empty($attendance_info->el_duration)) {
                                    $attendance_status = '<span class="status-pill status-lx">LC</span>';
                                }

                                if ($attendance_info->lc_duration > 0 && $attendance_info->el_duration > 0) {
                                    $attendance_status = '<span class="status-pill status-lx">LE</span>';
                                }
                            }
                        }
                        $studentsList .= '<td class="day-col">' . $attendance_status . '</td>';
                    }

                    $resultP = $db->query("select count(STATUS) as totalP from attendance where student_id =" . $row->student_id . " and STATUS = 'P' and Month(date) = " . $reportMonth . " and Year(date) =" . $reportYear)->getRow();

                    $resultLC = $db->query("select count(STATUS) as totalLC from attendance where student_id =" . $row->student_id . " AND lc_duration > 0 AND STATUS = 'P' and Month(date) = " . $reportMonth . " and Year(date) =" . $reportYear)->getRow();

                    $resultL = $db->query("select count(STATUS) as totalL from attendance where student_id =" . $row->student_id . " and STATUS = 'L' and Month(date) = " . $reportMonth . " and Year(date) =" . $reportYear)->getRow();

                    $resultEL = $db->query("select count(STATUS) as totalEL from attendance where student_id =" . $row->student_id . " AND el_duration > 0 AND STATUS = 'P' and Month(date) = " . $reportMonth . " and Year(date) =" . $reportYear)->getRow();

                    $resultA = $db->query("select count(STATUS) as totalA from attendance where student_id =" . $row->student_id . " and STATUS = 'A' and Month(date) = " . $reportMonth . " and Year(date) =" . $reportYear)->getRow();

                    $studentsList .= '<td class="total-col"><span style="background:#204d74;display:inline-block;color:#fff;min-width:24px;text-align:center;border-radius:10px;">' . $resultP->totalP . '</span></td>
    <td class="total-col"><span style="background:#204d74;display:inline-block;color:#fff;min-width:24px;text-align:center;border-radius:10px;">' . $resultA->totalA . '</span></td>
    <td class="total-col"><span style="background:#204d74;display:inline-block;color:#fff;min-width:24px;text-align:center;border-radius:10px;">' . $resultLC->totalLC . '</span></td>
    <td class="total-col"><span style="background:#204d74;display:inline-block;color:#fff;min-width:24px;text-align:center;border-radius:10px;">' . $resultEL->totalEL . '</span></td>
    <td class="total-col"><span style="background:#204d74;display:inline-block;color:#fff;min-width:24px;text-align:center;border-radius:10px;">' . $resultL->totalL . '</span></td></tr>';
                }
                $i++;
            }
        }
        $studentsList .= '</table></div></div><script>
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
