<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Students Leaves Manage
 *
 * @author      Maqsood Ahmed
 * @copyright   Copyright (c) 2018-2019 TIME Soft Solutions
 * @email       maqsoodjamvi@gmail.com
 */
class Students_leaves extends BaseController
{
    protected $session;
    protected $db;
    
    public function __construct()
    {
        helper(['form', 'url', 'session']);
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        
        // Check permission (you'll need to implement your own permission checking)
        check_permission('admin-student-attendance');
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        return view('admin/students_leaveapplications', $this->template_data);
    }

    public function data()
    {
        $campusid = $this->session->get('member_campusid');
        $status = $this->request->getPost('id');
        
        $query = $this->db->query('SELECT * FROM leave_applications WHERE status='.$status.' AND student_id IN(SELECT student_id FROM students WHERE campus_id='.$campusid.')');
        $results = $query->getResult();
        
        $data = '';
        $data .= '<table class="table"><tr><th>#</th><th>Student</th><th>App Date</th><th>Detail</th><th>Leave Start Date</th><th>Leave End Date</th><th>Type</th><th>Status</th>';
        
        if($status == 0) {
            $data .= '<th style="width: 140px;">Action</th>';
        }
        
        $data .= '</tr>';
        
        foreach($results as $row) {
            $studentQuery = $this->db->table('students')->where('student_id', $row->student_id)->get();
            $studentsinfo = $studentQuery->getRow();

            $data .= '<tr><td>'.$row->app_id.'</td><td>'.$studentsinfo->first_name." ".$studentsinfo->last_name.'</td><td>'.$row->app_date.'</td><td>'.$row->app_detail.'</td><td>'.$row->leave_start_date.'</td><td>'.$row->leave_end_date.'</td><td>'.$row->type.'</td><td>';
            
            if($row->status == 0) {
                $data .= 'Pending';
            }
            if($row->status == 1) {
                $data .= 'Approved';
            }
            if($row->status == 2) {
                $data .= 'Rejected';
            }
            
            $data .= '</td>';
            
            if($row->status == 0) {
                $data .= '<td><div class="btn-group" role="group" aria-label="Basic example"><input type="button" value="Approve" id="approveleave'.$row->app_id.'" data-id="'.$row->app_id.'" data-stdid="'.$studentsinfo->student_id.'" class="btn btn-success btn-sm"><input type="button" value="Reject" id="rejectleave'.$row->app_id.'" data-id="'.$row->app_id.'" data-stdid="'.$studentsinfo->student_id.'" class="btn btn-danger btn-sm"></div></td></tr><script>
                $(function(){    
                    $("#approveleave'.$row->app_id.'").click(function(){
                        var r=confirm("Are you sure?");
                          if (r==true)
                          {
                            var id = $(this).data("id");
                            var student_id = $(this).data("stdid");
                            $.ajax({
                                type: "POST",
                                url: "'.site_url("admin/students_leaves/approveleave").'",
                                data:{id: id,student_id:student_id},
                                success: function(data){
                                    var json = $.parseJSON(data);            
                                    if(json.success){
                                           toastr.success("Leave Approved");
                                           location.reload();
                                      }else{
                                           toastr.error("change error");
                                    }
                                }
                            });
                            }else{
                                
                            }
                    });

                    $("#rejectleave'.$row->app_id.'").click(function(){
                            var id = $(this).data("id");
                            $("#app_id").val(id);
                            $("#my-modal").modal({
                                 show: true
                            }); 
                            var student_id = $(this).data("stdid");
                    });
                });
                </script>';
            }
        }
        
        $data .= '</table><div class="modal fade" id="my-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Reject Leave Application</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
              <form action="'.site_url("admin/students_leaves/rejectleave").'" method="post">
              <input type="hidden" name="app_id" id="app_id" >
              <div class="form-group">
              <label>Reason</label>
              <textarea name="reason" id="reason" class="form-control"></textarea>
              <button type="submit" class="btn btn-primary">Save changes</button>
              </form>   
              </div>   
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>';
        
        return $this->response->setBody($data);
    }

    public function rejectleave()
    {
        $app_id = $this->request->getPost('app_id');
        $reason = $this->request->getPost('reason');
        
        $data = [
            'reason' => $reason,
            'status' => 2,  
        ];
        
        $this->db->table('leave_applications')->where('app_id', $app_id)->update($data);
        return redirect()->to(site_url("#/students_leaves"));
    }

    public function approveleave()
    {
        $student_id = $this->request->getPost('student_id');    
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');

        $leaveQuery = $this->db->table('leave_applications')->where('app_id', $this->request->getPost('id'))->get();
        $leaveinfo = $leaveQuery->getRow();
        
        if($leaveinfo->leave_start_date == $leaveinfo->leave_end_date) {
            $leavedate = $leaveinfo->leave_start_date; 

            $attendanceQuery = $this->db->table('attendance')
                ->where('date', $leavedate)
                ->where('student_id', $student_id)
                ->get();
            $attendanceinfo = $attendanceQuery->getRow();
            
            $attendanceData = [
                'student_id' => $student_id,
                'date' => $leavedate,
                'checkin' => '08:15',
                'checkout' => '08:15',
                'status' => 'L', 
                'lc_duration' => '0', 
                'el_duration' => '0', 
                'updated_date' => $date,
                'user_id' => $user_id,          
            ];
            
            if($attendanceinfo) {
                $this->db->table('attendance')
                    ->where('attendance_id', $attendanceinfo->attendance_id)
                    ->update($attendanceData);
            } else {
                $attendanceData['created_date'] = $date;
                $this->db->table('attendance')->insert($attendanceData);
            }
        } else {
            $period = $this->date_range($leaveinfo->leave_start_date, $leaveinfo->leave_end_date, $step = '+1 day', $output_format = 'Y-m-d');
            
            foreach ($period as $key => $value) {
                $leavedate = $value; 
                $data2 = [
                    'student_id' => $student_id,
                    'date' => $leavedate,
                    'checkin' => '08:15',
                    'checkout' => '08:15',
                    'status' => 'L',  
                    'lc_duration' => '0', 
                    'el_duration' => '0', 
                    'created_date' => $date,
                    'user_id' => $user_id,         
                ];
                $this->db->table('attendance')->insert($data2);     
            }
        }
        
        $data = [
            'status' => 1,  
            'updated_date' => $date,
            'user_id' => $user_id,
        ];
        
        $this->db->table('leave_applications')
            ->where('app_id', $this->request->getPost('id'))
            ->update($data);
        
        return $this->response->setJSON(['success' => TRUE, 'msg' => 'Leave Accepted']);
    }

    protected function date_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d')
    {
        $dates = [];
        $current = strtotime($first);
        $last = strtotime($last);

        while($current <= $last) {
            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
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
            ->get()
            ->getResult();
        
        $sectionsclassinfo = [];
        foreach($classsectioninfo as $section) {
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
                'sectionclassname' => $classinfo->class_name." (".$sectioninfo->section_name.")"
            ];
        }
        
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;    
        
        $campusinfo = $this->db->table('campus')
            ->where('campus_id', $campusid)
            ->get()
            ->getResult();
        $this->template_data['campusinfo'] = $campusinfo;
        
        $examinfo = $this->db->table('exam')
            ->where('campus_id', $campusid)
            ->where('session_id', $sessionid)
            ->get()
            ->getResult();
        $this->template_data['examinfo'] = $examinfo;

        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()
            ->getResult();
        $this->template_data['academic_session'] = $academic_session;

        $subjectinfo = $this->db->table('allsubject')->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        return view('admin/students_leaves_edit', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-student-attendance');
        $id = (int)$this->request->getGet('id');
        
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
        return view('admin/students_leaves_edit', $this->template_data);
    }

    public function save()
    {    
        check_permission('admin-add-student-attendance');
        $student_ids = $this->request->getPost('student_id');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');
    
        $this->db->transBegin();
        $leave_type = $this->request->getPost('leave_type');

        $leave_start_date = '';
        $leave_end_date = '';
        if($leave_type == 1) {
            $leave_start_date = $this->request->getPost('singledate');
            $leave_end_date = $this->request->getPost('singledate');
        } else {
            $leave_start_date = $this->request->getPost('from_date');
            $leave_end_date = $this->request->getPost('to_date');
        }
        
        foreach($student_ids as $key => $student_id) {
            if($this->request->getPost($student_id.'_status')) {
                $stdLeaveAppInfo = $this->db->query('SELECT * FROM leave_applications WHERE student_id='.$student_id.' AND app_date="'.$this->request->getPost('date').'"')->getRow();
                
                $data = [
                    'student_id' => $student_id,
                    'app_date' => $this->request->getPost('date'),
                    'leave_start_date' => $leave_start_date,
                    'leave_end_date' => $leave_end_date,
                    'type' => $this->request->getPost('type'),
                    'status' => 0,
                    'app_detail' => $this->request->getPost('app_detail'),
                    'created_date' => $date,
                    'user_id' => $user_id,
                ];

                if(!empty($stdLeaveAppInfo)) {
                    $this->db->table('leave_applications')
                        ->where('student_id', $student_id)
                        ->where('app_date', $this->request->getPost('date'))
                        ->update($data);
                } else {
                    $this->db->table('leave_applications')->insert($data);
                }    
                $new_user_id = $this->db->insertID();
            }
        }
        
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => FALSE, 'msg' => 'Failed to create leave application']);
        } else {
            $this->db->transCommit();
            return $this->response->setJSON(['success' => TRUE, 'msg' => 'Leave Application Created']);
        }
    }

    public function get_studentinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term');        
        $studentsinfo = $this->db->query("SELECT * FROM students WHERE (first_name LIKE '%".$term['term']."%' OR last_name LIKE '%".$term['term']."%') AND status=1 AND campus_id=".$campusid)->getResultArray();

        $data = [];
        foreach($studentsinfo as $student) {
            $classstudents = $this->db->query("SELECT * FROM student_class WHERE status=1 AND student_id = ".$student['student_id'])->getRow();
            if($classstudents) {
                $parentInfo = $this->db->query("SELECT * FROM parents WHERE parent_id = ".$student['parent_id'])->getRow();
                $data[] = ["id"=>$student['student_id'], "text"=>$student['first_name']." ".$student['last_name']." c/o ".$parentInfo->f_name];
            }
        }
        
        return $this->response->setJSON($data);     
    }

    public function get_students_byclass()
    {
        $eid = $this->request->getPost('eid');
        $session_id = $this->request->getPost('session_id');
        $campus_id = $this->request->getPost('campus_id');
        $id = $this->request->getPost('student_id');
        $datevalue = $this->request->getPost('date'); 
      
        $timestamp = strtotime($datevalue);
        $day = date('l', $timestamp);    
       
        $studentsList = '';
        $studentsList .= '<input type="hidden" name="campus_id"  value="'.$campus_id.'">';
        $studentsList .= '<input type="hidden" name="class_id"  value="'.$id.'">';
                
        $studentslistinfo = $this->db->query("SELECT * FROM students WHERE status=1 AND campus_id =".$campus_id." AND parent_id=(SELECT parent_id FROM students WHERE student_id='".$id."')")->getResult();

        $studentsList .= '<div class="table-responsive"><table class="table" style="width:100%;">
        <tr><th style="width:15%;">Photo</th><th style="width:15%;">Name</th><th style="width:15%;">Select Student </th>';  
        $studentsList .= '</tr>';
        
        $attendance_info = $this->db->table('leave_applications')
            ->where('student_id', $id)
            ->where('app_date', $datevalue)
            ->get()
            ->getRow();    
        
        $noOfLeaveDays = 0;
        $startDate = '';
        $endDate = '';
        
        if($attendance_info) {
            $date1 = date_create($attendance_info->leave_start_date);
            $date2 = date_create($attendance_info->leave_end_date);
            $diff = date_diff($date1,$date2);
            $noOfLeaveDays = $diff->format("%a");
            
            if($noOfLeaveDays > 1) {
                $startDate = $date1->format('Y-m-d');
                $endDate = $date2->format('Y-m-d');
            } else {
                $startDate = $date1->format('Y-m-d');
            }
        }

        foreach($studentslistinfo as $row) {
            $studentName = $row->first_name." ".$row->last_name;
            $imgurl = FCPATH."uploads/".$row->profile_photo;

            if($row->profile_photo && file_exists($imgurl)) {
                $profile_photo = "<img style='width:50px;height:50px;text-align:center;display: block;border-radius: 30px;margin: 0 auto;' src='".base_url("uploads/".$row->profile_photo)."' >";
            } else {
                $profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
            }
           
            $studentsList .= '<tr><td style=" vertical-align:middle; word-break: break-word;"> '.$profile_photo.'<input type="hidden" name="student_id[]" value="'.$row->student_id.'" class="form-control"> </td>';
            $studentsList .= '<td style=" vertical-align:middle;">'.$studentName.'<br>'.$row->reg_no.'</td><td><div class="funkyradio">
                <div class="funkyradio-default">
                <label for="'.$row->student_id.'_leave_toggle"><input type="checkbox"';
                
                $studentsList .= ' checked=checked class="toggle_option" value="1" id="'.$row->student_id.'_leave_toggle" name="'.$row->student_id.'_status"> 
                Leave</label>
                </div> 
                </div>
                </td>
                </tr>';
        } 
        
        $leave_type_info = $this->db->table('leave_type')->get()->getResult();    
        $studentsList .= '</table>
            <div class="form-group">
                <label><input name="leave_type"';
                
                if($noOfLeaveDays < 2) {
                    $studentsList .= ' checked=checked ';
                }
                
                $studentsList .= ' id="singleday" value="1" type="radio"> Single Day</label>
                <label><input name="leave_type"';
                
                if($noOfLeaveDays > 1) {
                    $studentsList .= ' checked=checked ';
                }
                
                $studentsList .= ' id="multipledays" value="2" type="radio"> Multiple Days</label>
            </div>
            <div id="ksingledate" ';
            
            if($noOfLeaveDays < 2) {
                $studentsList .= ' style="display:block" ';
            } else {
                $studentsList .= ' style="display:none" ';
            }
            
            $studentsList .= ' >
                <div class="form-group">
                    <label>Date</label><br>
                    <input type="date" class="form-control" value="'.$startDate.'" name="singledate">
                </div>
            </div>
            <div id="daterange" ';
            
            if($noOfLeaveDays > 1) {
                $studentsList .= ' style="display:block" ';
            } else {
                $studentsList .= ' style="display:none" ';
            }
            
            $studentsList .= ' >
                <div class="form-group">
                    <label>From Date</label><br>
                    <input type="date" class="form-control" value="'.$startDate.'" name="from_date">
                </div>
                <div class="form-group">
                    <label>To Date</label><br>
                    <input type="date" class="form-control" value="'.$endDate.'" name="to_date">
                </div>
            </div>
            <div class="form-group">
                <label>Leave Type</label>
                <select name="type" class="form-control">';
                
                foreach ($leave_type_info as $key => $leavetype) {
                    $studentsList .= '<option value="'.$leavetype->type_name.'">'.$leavetype->type_name.'</option>';
                }
                    
                $studentsList .= '</select>
            </div>
            <div class="form-group">
                <textarea class="form-control" id="app_detail" name="app_detail"></textarea>
            </div>
        </div>
        <script>
        $(function(){
            $("#singleday").click(function(){
                $("#ksingledate").show();
                $("#daterange").hide();
            });

            $("#multipledays").click(function(){
                $("#daterange").show();
                $("#ksingledate").hide();
            });
            
            $(".clockpicker").clockpicker({
                placement: "bottom",
                align: "left", 
                autoclose: true,
                "default": "now"
            });
        });    
        </script>'; 
        
        return $this->response->setBody($studentsList);    
    }

    public function delete()
    {
        check_permission('admin-del-attendance');
        $id = (int)$this->request->getGet('id');
        
        $this->db->transBegin();
        $this->db->table('classes')->where('id', $id)->delete();
        
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => FALSE, 'msg' => 'Failed to delete attendance']);
        } else {
            $this->db->transCommit();
            return $this->response->setJSON(['success' => TRUE, 'msg' => 'Delete Attendance Success']);
        }
    }
}