<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Controller;
use CodeIgniter\API\ResponseTrait;
use stdClass;

/**
 * Students Attendance Manage
 *
 * @author      Maqsood Ahmed
 * @copyright   Copyright (c) 2018-2019 TIME Soft Solutions
 * @email       maqsoodjamvi@gmail.com
 */
class StudentsAttendance extends BaseController
{
    use ResponseTrait;

    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['permission', 'school', 'url', 'form']);
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        check_permission('admin-student-attendance');
        return view('admin/students_attendance', $this->template_data);
    }

    public function data()
    {
        check_permission('admin-student-attendance');
        
        $response = new stdClass;
        $response->draw = $this->request->getPost('draw');
        $sessionid = $this->session->get('member_sessionid');

        $search = $this->request->getPost('search');
        $keyword = '';
        if ($search) $keyword = $search['value'];
        
        $response->recordsTotal = $this->db->table('attendance')->countAll();

        $results = $this->db->table('attendance')->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;

        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()
            ->getRow();

        $response->data = [];
        foreach ($results as $row) {
            $data = [];
            $allsubjectinfo = [];
            $data['id'] = $row->cid;
            
            $studentsinfo = $this->db->table('students')
                ->where('student_id', $row->student_id)
                ->get()
                ->getRow();

            $studentclass = $this->db->table('student_class')
                ->where('student_id', $row->student_id)
                ->get()
                ->getRow();

            $classesinfo = $this->db->table('classes')
                ->where('class_id', $studentclass->class_id)
                ->get()
                ->getRow();

            $terms_session = $this->db->query("SELECT * FROM terms_session where session_id = " . $sessionid . " and '" . $row->date . "' between start_date and end_date")->getResult();
            
            if ($terms_session) {
                $termsinfo = $this->db->table('terms')
                    ->where('term_id', $terms_session[0]->term_id)
                    ->get()
                    ->getRow();
                $term_name = $termsinfo->name;
            } else {
                $term_name = '';
            }

            $data['student'] = $studentsinfo->first_name . " " . $studentsinfo->last_name;
            $data['class'] = $classesinfo->class_name;
            $data['session_name'] = $academic_session->session_name;
            $data['term_name'] = $term_name;
            $data['date'] = $row->date;
            $data['detail'] = $row->detail;
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-student-attendance');
        
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        $infostudents = $this->db->table('students')->get()->getResult();
        $this->template_data['infostudents'] = $infostudents;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $classsectioninfo = $this->db->table('class_section')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->get()
            ->getResult();

        $sectionsclassinfo = [];
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')
                ->where('class_id', $section->class_id)
                ->get()
                ->getRow();

            $sectioninfo = $this->db->table('sections')
                ->where('section_id', $section->section_id)
                ->get()
                ->getRow();
            
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
        
        return view('admin/students_attendance_edit', $this->template_data);
    }

    public function report()
    {
        check_permission('admin-add-student-attendance');
        
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        $infostudents = $this->db->table('students')->get()->getResult();
        $this->template_data['infostudents'] = $infostudents;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $classsectioninfo = $this->db->table('class_section')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->get()
            ->getResult();

        $sectionsclassinfo = [];
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')
                ->where('class_id', $section->class_id)
                ->get()
                ->getRow();

            $sectioninfo = $this->db->table('sections')
                ->where('section_id', $section->section_id)
                ->get()
                ->getRow();
            
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
        
        return view('admin/students_attendance_list', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-student-attendance');
        $id = intval($this->request->getGet('id'));
        
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        $info = $this->db->table('studentsresults')
            ->where('student_id', $id)
            ->get()
            ->getRow();

        $infostudents = $this->db->table('students')->get()->getResult();
        $this->template_data['infostudents'] = $infostudents;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $subjectinfo = $this->db->table('allsubject')->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        $this->template_data['info'] = $info;
        return view('admin/students_attendance_edit', $this->template_data);
    }

    public function save()
    {
        check_permission('admin-add-student-attendance');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $student_ids = $this->request->getPost('student_id');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');
        
        $this->db->transBegin();

        foreach ($student_ids as $key => $student_id) {
            $attendanceInfo = $this->db->query('SELECT * from attendance where student_id=' . $student_id . ' AND date="' . $this->request->getPost('date') . '"')->getRow();

            $datevalue = $this->request->getPost('date');
            $timestamp = strtotime($datevalue);
            $day = date('l', $timestamp);

            $classSecinfo = $this->db->table('student_class')
                ->where('student_id', $student_id)
                ->where('status', 1)
                ->get()
                ->getRow();

            $schooltimings = $this->db->query("SELECT *,(checkout_timing - checkin_timing) AS duration FROM school_timings WHERE
                 cls_sec_id =" . $classSecinfo->cls_sec_id . " AND dayname ='" . $day . "'  AND type_id = (SELECT type_id FROM school_timing_types WHERE status=1 AND campus_id=" . $campusid . ")")->getRow();

            if ($this->request->getPost($student_id . '_status') == 'A') {
                $checkouttime = $this->request->getPost($student_id . '_checkin_date');
            } else {
                $checkouttime = $this->request->getPost($student_id . '_checkout_date');
            }

            $time1 = strtotime($schooltimings->checkin_timing);
            $time2 = strtotime($this->request->getPost($student_id . '_checkin_date'));
            $lc_duration = ($time2 - $time1) / 60;
            
            $lcDuration = 0;
            if ($lc_duration > 0) {
                $lcDuration = $lc_duration;
            }

            $time1 = strtotime($schooltimings->checkout_timing);
            $time2 = strtotime($this->request->getPost($student_id . '_checkout_date'));

            $el_duration = ($time1 - $time2) / 60;
            $elDuration = 0;
            if ($el_duration > 0) {
                $elDuration = $el_duration;
            }

            if ($attendanceInfo) {
                $data = [
                    'student_id' => $student_id,
                    'date' => $this->request->getPost('date'),
                    'status' => $this->request->getPost($student_id . '_status'),
                    'checkin' => $this->request->getPost($student_id . '_checkin_date'),
                    'checkout' => $checkouttime,
                    'el_duration' => $elDuration,
                    'lc_duration' => $lcDuration,
                    'updated_date' => $date,
                    'user_id' => $user_id,
                ];
                
                $this->db->table('attendance')
                    ->where('student_id', $student_id)
                    ->where('date', $this->request->getPost('date'))
                    ->update($data);
            } else {
                $data = [
                    'student_id' => $student_id,
                    'date' => $this->request->getPost('date'),
                    'status' => $this->request->getPost($student_id . '_status'),
                    'checkin' => $this->request->getPost($student_id . '_checkin_date'),
                    'checkout' => $checkouttime,
                    'el_duration' => $elDuration,
                    'lc_duration' => $lcDuration,
                    'updated_date' => $date,
                    'user_id' => $user_id,
                ];

                $this->db->table('attendance')->insert($data);
            }
            $new_user_id = $this->db->insertID();
        }
        
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->respond(['success' => false, 'msg' => 'Failed to update attendance']);
        } else {
            $this->db->transCommit();
            return $this->respond(['success' => true, 'msg' => 'Update Attendance Success']);
        }
    }

    public function get_students_byclass()
    {
        $eid = $this->request->getPost('eid');
        $session_id = $this->request->getPost('session_id');
        $campus_id = $this->request->getPost('campus_id');
        $id = $this->request->getPost('section_id');
        $subject_id = $this->request->getPost('subject_id');
        $datevalue = $this->request->getPost('date');
        
        $timestamp = strtotime($datevalue);
        $day = date('l', $timestamp);
        
        $data = [];
        $studentsList = '';
        
        $studentsList .= '<input type="hidden" name="campus_id"  value="' . $campus_id . '">';
        $studentsList .= '<input type="hidden" name="class_id"  value="' . $id . '">';
        
        $classstudents = $this->db->query("select * from student_class where  status=1 and cls_sec_id = " . $id)->getResult();
        
        $schooltime_info = $this->db->query('select * from school_timings where cls_sec_id=' . $id . ' AND dayname="' . $day . '" AND type_id IN(select type_id from school_timing_types where status=1 AND campus_id=' . $campus_id . ')')->getRow();

        if (empty($schooltime_info)) {
            echo "<div class='alert alert-danger'>Click Here To Set School Timing Before Taking Attendance <a href='/admin/school_timing/add'>School Timing</a></div>";
            exit;
        }

        $studentsList .= '<div class="table-responsive"><table class="table" style="width:100%;">
        <tr><th style="width:15%;">Photo</th><th style="width:15%;">Name</th><th style="width:15%;">A<br><label style="font-size:10px;"><input class="selectA" type="checkbox"> Select All</label></th><th style="width:15%;">P<br> <label style="font-size:10px;"><input class="selectP" type="checkbox"> Select All</label></th><th style="width:15%;">L<br> <label style="font-size:10px;"><input class="selectL" type="checkbox"> Select All</label></th><th style="width:15%;">LC</th><th style="width:15%;">EL</th>';  
    
        $studentsList .= '</tr>';
        $i = 1;
        
        foreach ($classstudents as $row) {
            $attendance_info = $this->db->table('attendance')
                ->where('student_id', $row->student_id)
                ->where('date', $datevalue)
                ->get()
                ->getRow();

            $studentsinfo = $this->db->table('students')
                ->where('student_id', $row->student_id)
                ->get()
                ->getRow();
            
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
                
                $studentsList .= '<tr><td style=" vertical-align:middle; word-break: break-word;"> ' . $profile_photo . '<input type="hidden" name="student_id[]" value="' . $studentsinfo->student_id . '" class="form-control"> </td>';
                $studentsList .= '<td style=" vertical-align:middle;">' . $studentName . '<br>' . $studentsinfo->reg_no . '</td><td>
                <div class="funkyradio">
                    <div class="funkyradio-default">
                    <input type="radio"';
                    
                    if ($attendance_info) {
                        if ($attendance_info->status == 'A') {
                            $studentsList .= ' checked="checked"';
                        }
                    }
                    
                    $studentsList .= ' id="' . $studentsinfo->student_id . '_absent_toggle" class="radioA" value="A" name="' . $studentsinfo->student_id . '_status"> 
                    <label for="' . $studentsinfo->student_id . '_absent_toggle">A</label>
                </div>
                </div>
                </td><td><div class="funkyradio">
                    <div class="funkyradio-default">
                <input type="radio"';
                
                if ($attendance_info) {
                    if ($attendance_info->status == 'P') {
                        $studentsList .= ' checked="checked"';
                    }
                }
                
                $studentsList .= ' class="toggle_option radioP"  value="P" id="' . $studentsinfo->student_id . '_present_toggle" name="' . $studentsinfo->student_id . '_status">
                <label for="' . $studentsinfo->student_id . '_present_toggle"> P </label>
                </div>
                </div>
                </td><td><div class="funkyradio">
                    <div class="funkyradio-default">
                <input type="radio"';
                
                if ($attendance_info) {
                    if ($attendance_info->status == 'L') {
                        $studentsList .= ' checked="checked"';
                    }
                }
                
                $studentsList .= ' class="toggle_option radioL"  value="L" id="' . $studentsinfo->student_id . '_leave_toggle" name="' . $studentsinfo->student_id . '_status">
                <label for="' . $studentsinfo->student_id . '_leave_toggle"> L </label>
                </div>
                </div>
                </td><td><div class="funkyradio">
                <div class="funkyradio-default">
                <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
                <input type="text" class="form-control ' . $studentsinfo->student_id . '_checkin_date" name="' . $studentsinfo->student_id . '_checkin_date" value="';
                
                if ($attendance_info) {
                    $studentsList .= $attendance_info->checkin;
                } else {
                    if ($schooltime_info) {
                        $studentsList .= $schooltime_info->checkin_timing;
                    }
                }
                
                $studentsList .= '">
                <span class="input-group-text btn btn-secondary">
                    <span class="far fa-clock"></span>
                </span>
                </div>
                </div>
                </div> 
            </td><td><div class="funkyradio">
            <div class="funkyradio-default">
            <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
            <input type="text" class="form-control"   name="' . $studentsinfo->student_id . '_checkout_date" value="';
            
            if ($attendance_info) {
                $studentsList .= $attendance_info->checkout;
            } else {
                if ($schooltime_info) {
                    $studentsList .= $schooltime_info->checkout_timing;
                }
            }
            
            $studentsList .= '">
            <span class="input-group-text btn btn-secondary">
                <span class="far fa-clock"></span>
            </span>
        </div>
            </div>
                </div><script>
            $(function(){
            $(".' . $studentsinfo->student_id . '_checkin_date").click(function () {
                $("#' . $studentsinfo->student_id . '_late_comming_toggle").prop("checked", true);
            });
            });	
            </script>
                </td>
                </tr>';
            }
            $i++;
        }
        
        $studentsList .= '</table></div><script>
        $(function(){
            $(".selectA").click(function(){
                $(".radioA").not(this).prop("checked", this.checked);
            });	
            $(".selectP").click(function(){
                $(".radioP").not(this).prop("checked", this.checked);
            });	
            $(".selectL").click(function(){
                $(".radioL").not(this).prop("checked", this.checked);
            });
            $(".clockpicker").clockpicker();
        });	
        </script>';
        
        return $this->response->setBody($studentsList);
    }
public function get_students_byabsentees()
{
    helper(['url']); // for site_url(), route_to()
    $parser = \Config\Services::parser();

    // ---- Inputs (sanitize & defaults)
    $eid         = (int) $this->request->getPost('eid');
    $session_id  = (int) $this->request->getPost('session_id');
    $campus_id   = (int) $this->request->getPost('campus_id');
    $section_id  = (int) $this->request->getPost('section_id'); // not used here but kept
    $subject_id  = (int) $this->request->getPost('subject_id'); // not used here but kept
    $datevalue   = trim((string) $this->request->getPost('date'));
    $term_session_id = (int) ($this->session->get('member_termsessionid') ?? 0);

    // Validate/normalize date (YYYY-MM-DD). Fallback to today.
    if (!$datevalue || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datevalue)) {
        $datevalue = date('Y-m-d');
    }
    $timestamp      = strtotime($datevalue);
    $attendanceDate = date('d m Y', $timestamp);

    // ---- Safety checks
    if ($campus_id <= 0 || $term_session_id <= 0) {
        return $this->response->setStatusCode(400)
            ->setBody('<div class="alert alert-danger">Missing campus or term session.</div>');
    }

    // ---- Fetch config/template once
    $terms_session_info = $this->db->table('terms_session')
        ->where('term_session_id', $term_session_id)
        ->get()->getRow();

    if (!$terms_session_info) {
        return $this->response->setStatusCode(404)
            ->setBody('<div class="alert alert-warning">Term session not found.</div>');
    }

    $campusInfo = $this->db->table('campus')
        ->where('campus_id', $campus_id)
        ->get()->getRow();

    $template = (string) ($campusInfo->attendance_sms ?? '');

    // ---- Get active students for this campus (via join to filter campus)
    // Only essentials to keep memory low
    $classStudents = $this->db->table('student_class sc')
        ->select('sc.student_id, sc.cls_sec_id')
        ->join('students s', 's.student_id = sc.student_id', 'inner')
        ->where('sc.status', 1)
        ->where('s.campus_id', $campus_id)
        ->get()->getResult();

    // ---- Build table
    $studentsList  = '';
    $studentsList .= '<div class="table-responsive">';
    $studentsList .= '<strong>Attendance Date: ' . esc($attendanceDate) . '</strong>';
    $studentsList .= '<table class="table table-bordered" style="width:100%">';
    $studentsList .= '<tr>
        <th style="width:15%;">Reg #</th>
        <th style="width:15%;">Name</th>
        <th style="width:15%;">Class</th>
        <th style="width:15%;">Whatsapp</th>
        <th style="width:15%;">Total</th>
        <th style="width:15%;">Action</th>
    </tr>';

    foreach ($classStudents as $row) {
        // Absent today?
        $attendance_info = $this->db->table('attendance')
            ->where([
                'student_id' => (int) $row->student_id,
                'status'     => 'A',
            ])
            ->where('date', $datevalue)
            ->get()->getRow();

        if (!$attendance_info) {
            continue; // only list absentees
        }

        // Term counts
        $absentess_info = $this->db->table('attendance')
            ->selectCount('attendance_id', 'total_absentees')
            ->where('student_id', (int) $row->student_id)
            ->where('date >=', $terms_session_info->start_date)
            ->where('date <=', $terms_session_info->end_date)
            ->where('status', 'A')
            ->get()->getRow();

        $presents_info = $this->db->table('attendance')
            ->selectCount('attendance_id', 'total_presents')
            ->where('student_id', (int) $row->student_id)
            ->where('date >=', $terms_session_info->start_date)
            ->where('date <=', $terms_session_info->end_date)
            ->get()->getRow();

        // Student & class/section
        $studentsinfo = $this->db->table('students')
            ->where('student_id', (int) $row->student_id)
            ->get()->getRow();

        if (!$studentsinfo) {
            continue;
        }

        $classsectioninfo = $this->db->table('class_section')
            ->where('cls_sec_id', (int) $row->cls_sec_id)
            ->get()->getRow();

        $className   = '';
        $sectionName = '';

        if ($classsectioninfo) {
            $classinfo = $this->db->table('classes')
                ->where('class_id', (int) $classsectioninfo->class_id)
                ->get()->getRow();
            $sectionInfo = $this->db->table('sections')
                ->where('section_id', (int) $classsectioninfo->section_id)
                ->get()->getRow();

            $className   = (string) ($classinfo->class_name ?? '');
            $sectionName = (string) ($sectionInfo->section_name ?? '');
        }

        $StudentClass = trim($className . ($sectionName ? " ({$sectionName})" : ''));

        // Parent
        $parentssinfo = $this->db->table('parents')
            ->where('parent_id', (int) $studentsinfo->parent_id)
            ->get()->getRow();

        // Message parse
        $studentName = trim(($studentsinfo->first_name ?? '') . ' ' . ($studentsinfo->last_name ?? ''));
        $dataForTpl  = [
            'first_name'  => $studentsinfo->first_name ?? '',
            'last_name'   => $studentsinfo->last_name ?? '',
            'date'        => $datevalue,
            'father_name' => $parentssinfo->f_name ?? '',
            'class'       => $StudentClass,
        ];
        $parsedMessage = $template ? $parser->setData($dataForTpl)->renderString($template) : '';

        // Detail URL (from named route)
        $detailPath = route_to('students_attendance_detail'); // /admin/students_attendance/students_attendance_detail
        $detailUrl  = site_url($detailPath) . '?' . http_build_query([
            'parent_id' => (int) ($parentssinfo->parent_id ?? 0),
            'campus_id' => $campus_id,
        ]);

        // WhatsApp link
        $waNumber = preg_replace('/\D+/', '', (string) ($parentssinfo->whatsapp ?? ''));
        $waText   = trim($parsedMessage . ' ' . $detailUrl);
        $waHref   = $waNumber ? ('https://wa.me/' . $waNumber . '?text=' . rawurlencode($waText)) : '#';

        // Row
        $studentsList .= '<tr>';
        $studentsList .= '<td style="vertical-align:middle;word-break:break-word;text-align:center;padding:0 4px;">' . esc($studentsinfo->reg_no ?? '') . '</td>';
        $studentsList .= '<td style="vertical-align:middle;padding:0 4px;">' . esc($studentName) . '</td>';
        $studentsList .= '<td style="text-align:center;vertical-align:middle;padding:0 4px;">' . esc($StudentClass) . '</td>';
        $studentsList .= '<td style="text-align:center;padding:0 4px;">'
                       .   '<a target="_blank" rel="noopener" href="' . esc($waHref) . '">' . esc($parentssinfo->whatsapp ?? '') . '</a>'
                       . '</td>';
        $studentsList .= '<td style="text-align:center;vertical-align:middle;padding:0 4px;">'
                       .   (int) ($absentess_info->total_absentees ?? 0) . '/' . (int) ($presents_info->total_presents ?? 0)
                       . '</td>';
        $studentsList .= '<td style="text-align:center;vertical-align:middle;padding:0 4px;">'
                       .   '<a target="_blank" rel="noopener" href="' . esc($detailUrl) . '">View</a>'
                       . '</td>';
        $studentsList .= '</tr>';
    }

    $studentsList .= '</table></div>';

    // (Optional) Keep your JS if needed by your page
    $studentsList .= '<script>
        $(function(){
            $(".selectA").on("click", function(){ $(".radioA").not(this).prop("checked", this.checked); });
            $(".selectP").on("click", function(){ $(".radioP").not(this).prop("checked", this.checked); });
            $(".selectL").on("click", function(){ $(".radioL").not(this).prop("checked", this.checked); });
            $(".clockpicker").clockpicker && $(".clockpicker").clockpicker();
        });
    </script>';

    return $this->response->setBody($studentsList);
}

    public function delete()
    {
        check_permission('admin-del-attendance');
        $id = intval($this->request->getGet('id'));
        
        $this->db->transBegin();
        $this->db->table('classes')
            ->where('id', $id)
            ->delete();

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->respond(['success' => false, 'msg' => 'Failed to delete attendance']);
        } else {
            $this->db->transCommit();
            return $this->respond(['success' => true, 'msg' => 'Delete Attendance Success']);
        }
    }
}