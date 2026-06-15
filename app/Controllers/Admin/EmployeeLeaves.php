<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class EmployeeLeaves extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        //check_permission('admin-employee-leaves');
    }

    public function index()
    {
        return view('admin/employee_leaveapplications', $this->template_data);
    }

    public function data()
    {
        $campusid = $this->session->get('member_campusid');
        $status = $this->request->getPost('id');

        $results = $this->db->query("
            SELECT * FROM employees_leave_applications 
            WHERE status = {$status} 
            AND user_id IN (
                SELECT user_id FROM users WHERE campus_id = {$campusid}
            )
        ")->getResult();

        $data = '';
        $data .= '<table class="table"><tr><th>#</th><th>Employee</th><th>App Date</th><th>Detail</th><th>Leave Start Date</th><th>Leave End Date</th><th>Type</th><th>Status</th>';
        if ($status == 0) {
            $data .= '<th style="width: 140px;">Action</th>';
        }
        $data .= '</tr>';

        foreach ($results as $row) {
            $usersinfo = $this->db->table('users')->where('id', $row->emp_id)->get()->getRow();

            $data .= '<tr><td>' . $row->app_id . '</td><td>' . $usersinfo->first_name . ' ' . $usersinfo->last_name . '</td><td>' . $row->app_date . '</td><td>' . $row->app_detail . '</td><td>' . $row->leave_start_date . '</td><td>' . $row->leave_end_date . '</td><td>' . $row->type . '</td><td>';

            if ($row->status == 0) {
                $data .= 'Pending';
            } elseif ($row->status == 1) {
                $data .= 'Approved';
            } elseif ($row->status == 2) {
                $data .= 'Rejected';
            }

            $data .= '</td>';

            if ($row->status == 0) {
                $data .= '<td><div class="btn-group" role="group" aria-label="Basic example"><input type="button" value="Approve" id="approveleave' . $row->app_id . '" data-id="' . $row->app_id . '" data-stdid="' . $usersinfo->id . '" class="btn btn-success btn-sm"><input type="button" value="Reject" id="rejectleave' . $row->app_id . '" data-id="' . $row->app_id . '" data-stdid="' . $usersinfo->id . '" class="btn btn-danger btn-sm"></div></td></tr><script>
                    $(function(){	
                        $("#approveleave' . $row->app_id . '").click(function(){
                            var r=confirm("Are you sure?");
                            if (r==true) {
                                var id = $(this).data("id");
                                var student_id = $(this).data("stdid");
                                $.ajax({
                                    type: "POST",
                                    url: "' . site_url("admin/employee-leaves/approveleave") . '",
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
                            }
                        });

                        $("#rejectleave' . $row->app_id . '").click(function(){
                            var id = $(this).data("id");
                            $("#app_id").val(id);
                            $("#my-modal").modal({ show: true }); 
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
              <form action="' . site_url("admin/employee-leaves/rejectleave") . '" method="post">
              <input type="hidden" name="app_id" id="app_id" >
              <div class="form-group">
              <label>Reason</label>
              <textarea name="reason" id="reason" class="form-control"></textarea>
              <button type="submit" class="btn btn-primary">Save changes</button>
              </form>   
              </div>   
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>';

        return $this->response->setBody($data);
    }

    public function approveleave()
		{
		    $student_id = $this->request->getPost('student_id');
		    $app_id     = $this->request->getPost('id');
		    $user_id    = $this->session->get('member_userid');
		    $date       = date('Y-m-d');

		    $leaveinfo = $this->db->table('employees_leave_applications')->where('app_id', $app_id)->get()->getRow();

		    if (!$leaveinfo) {
		        return $this->response->setJSON(['error' => true, 'msg' => 'Leave application not found']);
		    }

		    $this->db->transStart();

		    if ($leaveinfo->leave_start_date == $leaveinfo->leave_end_date) {
		        $leavedate = $leaveinfo->leave_start_date;

		        if ($date > $leavedate) {
		            return $this->response->setJSON(['error' => true, 'msg' => 'Over Date Applications can\'t be approved']);
		        }

		        $attendanceinfo = $this->db->table('attendance_employee')
		            ->where(['date' => $leavedate, 'emp_id' => $student_id])
		            ->get()->getRow();

		        $data = [
		            'emp_id'       => $student_id,
		            'date'         => $leavedate,
		            'checkin'      => '08:15',
		            'checkout'     => '08:15',
		            'status'       => 'L',
		            'lc_duration'  => 0,
		            'el_duration'  => 0,
		            'user_id'      => $user_id,
		        ];

		        if ($attendanceinfo) {
		            $data['updated_date'] = $date;
		            $this->db->table('attendance_employee')->where('attendance_id', $attendanceinfo->attendance_id)->update($data);
		        } else {
		            $data['created_date'] = $date;
		            $this->db->table('attendance_employee')->insert($data);
		        }
		    } else {
		        $period = $this->date_range($leaveinfo->leave_start_date, $leaveinfo->leave_end_date);

		        foreach ($period as $leavedate) {
		            $this->db->table('attendance_employee')->insert([
		                'emp_id'      => $student_id,
		                'date'        => $leavedate,
		                'checkin'     => '08:15',
		                'checkout'    => '08:15',
		                'status'      => 'L',
		                'lc_duration' => 0,
		                'el_duration' => 0,
		                'created_date' => $date,
		                'user_id'     => $user_id,
		            ]);
		        }
		    }

		    $this->db->table('employees_leave_applications')
		        ->where('app_id', $app_id)
		        ->update([
		            'status'       => 1,
		            'updated_date' => $date,
		            'user_id'      => $user_id
		        ]);

		    $this->db->transComplete();

		    return $this->response->setJSON(['success' => true, 'msg' => 'Leave Accepted']);
		}

		public function rejectleave()
{
    $app_id = $this->request->getPost('app_id');
    $reason = $this->request->getPost('reason');

    $this->db->table('employees_leave_applications')
        ->where('app_id', $app_id)
        ->update([
            'reason' => $reason,
            'status' => 2,
        ]);

    return redirect()->to(site_url('#/employee_leaves'));
}

private function date_range($first, $last, $step = '+1 day', $format = 'Y-m-d')
{
    $dates = [];
    $current = strtotime($first);
    $last = strtotime($last);

    while ($current <= $last) {
        $dates[] = date($format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

public function save()
{
    check_permission('admin-add-employee-attendance');

    $student_ids     = $this->request->getPost('student_id');
    $leave_type      = $this->request->getPost('leave_type');
    $app_detail      = $this->request->getPost('app_detail');
    $app_date        = $this->request->getPost('date');
    $type            = $this->request->getPost('type');
    $user_id         = $this->session->get('member_userid');
    $date            = date('Y-m-d');

    $leave_start_date = $leave_end_date = '';
    if ($leave_type == 1) {
        $leave_start_date = $this->request->getPost('singledate');
        $leave_end_date = $this->request->getPost('singledate');
    } else {
        $leave_start_date = $this->request->getPost('from_date');
        $leave_end_date = $this->request->getPost('to_date');
    }

    foreach ($student_ids as $student_id) {
        if ($this->request->getPost("{$student_id}_status")) {
            $exists = $this->db->table('employees_leave_applications')
                ->where('emp_id', $student_id)
                ->where('app_date', $app_date)
                ->get()->getRow();

            $data = [
                'emp_id'          => $student_id,
                'app_date'        => $app_date,
                'leave_start_date'=> $leave_start_date,
                'leave_end_date'  => $leave_end_date,
                'type'            => $type,
                'status'          => 0,
                'app_detail'      => $app_detail,
                'created_date'    => $date,
                'user_id'         => $user_id,
            ];

            $this->db->transStart();

            if ($exists) {
                $this->db->table('employees_leave_applications')
                    ->where(['emp_id' => $student_id, 'app_date' => $app_date])
                    ->update($data);
            } else {
                $this->db->table('employees_leave_applications')->insert($data);
            }

            $this->db->transComplete();
        }
    }

    return $this->response->setJSON(['success' => true, 'msg' => 'Leave Application Created']);
}


public function get_employeeinfo()
{
    $campusid = $this->session->get('member_campusid');
    $term     = $this->request->getPost('term');

    $results = $this->db->table('users')
        ->like('first_name', $term['term'])
        ->orLike('last_name', $term['term'])
        ->where(['status' => 1, 'campus_id' => $campusid])
        ->get()->getResultArray();

    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'id' => $row['id'],
            'text' => $row['first_name'] . ' ' . $row['last_name'],
        ];
    }

    return $this->response->setJSON($data);
}


public function delete()
{
    check_permission('admin-del-attendance');
    $id = (int) $this->request->getGet('id');

    $this->db->transStart();
    $this->db->table('employees_leave_applications')->where('app_id', $id)->delete();
    $this->db->transComplete();

    return $this->response->setJSON(['success' => true, 'msg' => 'Leave application deleted successfully.']);
}


public function add()
{
    //check_permission('admin-add-employee-attendance');

    $campusid   = $this->session->get('member_campusid');
    $sessionid  = $this->session->get('member_sessionid');

    $sessionData = [
        'campusid'  => $campusid,
        'sessionid' => $sessionid
    ];
    $this->template_data['sessionData'] = $sessionData;

    $this->template_data['infostudents'] = $this->db->table('students')->get()->getResult();
    $this->template_data['classesinfo'] = $this->db->table('classes')->get()->getResult();

    $sections = [];
    $classSectionRows = $this->db->table('class_section')->where('campus_id', $campusid)->get()->getResult();
    foreach ($classSectionRows as $row) {
        $class  = $this->db->table('classes')->where('class_id', $row->class_id)->get()->getRow();
        $sect   = $this->db->table('sections')->where('section_id', $row->section_id)->get()->getRow();
        $sections[] = [
            'section_id' => $row->cls_sec_id,
            'sectionclassname' => $class->class_name . ' (' . $sect->section_name . ')'
        ];
    }
    $this->template_data['sectionsclassinfo'] = $sections;

    $this->template_data['campusinfo'] = $this->db->table('campus')->where('campus_id', $campusid)->get()->getResult();
    $this->template_data['examinfo'] = $this->db->table('exam')->where(['campus_id' => $campusid, 'session_id' => $sessionid])->get()->getResult();
    $this->template_data['academic_session'] = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getResult();
    $this->template_data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

    return view('admin/employee_leaves_edit', $this->template_data);
}

public function edit()
{
    check_permission('admin-edit-employee-attendance');

    $id = (int) $this->request->getGet('id');
    $campusid  = $this->session->get('member_campusid');
    $sessionid = $this->session->get('member_sessionid');

    $this->template_data['sessionData'] = [
        'campusid'  => $campusid,
        'sessionid' => $sessionid
    ];

    $this->template_data['info'] = $this->db->table('studentsresults')->where('student_id', $id)->get()->getRow();
    $this->template_data['infostudents'] = $this->db->table('students')->get()->getResult();
    $this->template_data['classesinfo'] = $this->db->table('classes')->get()->getResult();
    $this->template_data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

    return view('employee_leaves_edit', $this->template_data);
}


public function get_employee()
{
    $session_id = $this->request->getPost('session_id');
    $campus_id  = $this->request->getPost('campus_id');
    $id         = $this->request->getPost('emp_id');
    $datevalue  = $this->request->getPost('date');
    $day        = date('l', strtotime($datevalue));

    $studentsList = '';
    $studentsList .= '<input type="hidden" name="campus_id"  value="' . $campus_id . '">';

    $userslistinfo = $this->db->table('users')
        ->where('status', 1)
        ->where('id', $id)
        ->get()->getResult();

    $studentsList .= '<div class="table-responsive"><table class="table" style="width:100%;">
        <tr><th style="width:15%;">Photo</th><th style="width:15%;">Name</th><th style="width:15%;">Select Student</th></tr>';

    $attendance_info = $this->db->table('employees_leave_applications')
        ->where('emp_id', $id)
        ->where('app_date', $datevalue)
        ->get()->getRow();

    $noOfLeaveDays = 0;
    $startDate = '';
    $endDate = '';
    if ($attendance_info) {
        $date1 = date_create($attendance_info->leave_start_date);
        $date2 = date_create($attendance_info->leave_end_date);
        $diff  = date_diff($date1, $date2);
        $noOfLeaveDays = $diff->format("%a");

        if ($noOfLeaveDays > 1) {
            $startDate = date_format($date1, 'Y-m-d');
            $endDate   = date_format($date2, 'Y-m-d');
        } else {
            $startDate = date_format($date1, 'Y-m-d');
        }
    }

    foreach ($userslistinfo as $row) {
        $studentName = esc($row->first_name . ' ' . $row->last_name);
        $imgurl = FCPATH . 'uploads/' . $row->photo;

        if (!empty($row->photo) && file_exists($imgurl)) {
            $profile_photo = "<img style='width:50px;height:50px;text-align:center;display:block;border-radius:30px;margin:0 auto;' src='" . base_url("uploads/" . $row->photo) . "'>";
        } else {
            $profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
        }

        $studentsList .= '<tr><td style="vertical-align:middle;">' . $profile_photo . '<input type="hidden" name="student_id[]" value="' . $row->id . '" class="form-control"></td>';
        $studentsList .= '<td style="vertical-align:middle;">' . $studentName . '</td>';
        $studentsList .= '<td><div class="funkyradio">
            <div class="funkyradio-default">
                <label for="' . $row->id . '_leave_toggle">
                    <input type="checkbox" checked=checked class="toggle_option" value="1" id="' . $row->id . '_leave_toggle" name="' . $row->id . '_status"> Leave
                </label>
            </div>
        </div></td></tr>';
    }

    $leave_type_info = $this->db->table('leave_type')->get()->getResult();

    $studentsList .= '</table>
        <div class="form-group">
            <label><input name="leave_type" ' . ($noOfLeaveDays < 2 ? 'checked=checked' : '') . ' id="singleday" value="1" type="radio"> Single Day</label>
            <label><input name="leave_type" ' . ($noOfLeaveDays > 1 ? 'checked=checked' : '') . ' id="multipledays" value="2" type="radio"> Multiple Days</label>
        </div>
        <div id="ksingledate" ' . ($noOfLeaveDays < 2 ? 'style="display:block"' : 'style="display:none"') . '>
            <div class="form-group">
                <label>Date</label><br>
                <input type="date" class="form-control" value="' . $startDate . '" name="singledate">
            </div>
        </div>
        <div id="daterange" ' . ($noOfLeaveDays > 1 ? 'style="display:block"' : 'style="display:none"') . '>
            <div class="form-group">
                <label>From Date</label><br>
                <input type="date" class="form-control" value="' . $startDate . '" name="from_date">
            </div>
            <div class="form-group">
                <label>To Date</label><br>
                <input type="date" class="form-control" value="' . $endDate . '" name="to_date">
            </div>
        </div>
        <div class="form-group">
            <label>Leave Type</label>
            <select name="type" class="form-control">';

    foreach ($leave_type_info as $type) {
        $studentsList .= '<option value="' . esc($type->type_name) . '">' . esc($type->type_name) . '</option>';
    }

    $studentsList .= '</select></div>
        <div class="form-group">
            <textarea class="form-control" id="app_detail" name="app_detail"></textarea>
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
                    default: "now"
                });
            });
        </script>';

    return $this->response->setBody($studentsList);
}


}
