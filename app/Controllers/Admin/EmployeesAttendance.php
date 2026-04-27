<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class EmployeesAttendance extends BaseController
{
    protected $db;
    protected $session;
    protected $userId;
    protected $campusId;
    protected $sessionId;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();

        check_permission('admin-student-attendance');

        $this->userId = $this->session->get('member_userid');
        $this->campusId = $this->session->get('member_campusid');
        $this->sessionId = $this->session->get('member_sessionid');
    }

    public function index()
    {
        return view('admin/employees_attendance', [
            'sessionData' => [
                'campusid' => $this->campusId,
                'sessionid' => $this->sessionId
            ]
        ]);
    }

    public function data()
    {
        $request = service('request');
        $draw = $request->getPost('draw');
        $search = $request->getPost('search');
        $keyword = $search['value'] ?? '';

        $builder = $this->db->table('attendance');
        $recordsTotal = $builder->countAllResults(false);

        $attendanceData = $builder->get()->getResult();

        $academicSession = $this->db->table('academic_session')
            ->where('session_id', $this->sessionId)
            ->get()->getRow();

        $data = [];
        foreach ($attendanceData as $row) {
            $student = $this->db->table('users')->where('id', $row->student_id)->get()->getRow();
            $studentClass = $this->db->table('student_class')->where('student_id', $row->student_id)->get()->getRow();
            $class = $this->db->table('classes')->where('class_id', $studentClass->class_id)->get()->getRow();

            $termName = '';
            $termsSession = $this->db->query(
                "SELECT * FROM terms_session WHERE session_id = {$this->sessionId} AND '{$row->date}' BETWEEN start_date AND end_date"
            )->getResult();

            if (!empty($termsSession)) {
                $term = $this->db->table('terms')->where('term_id', $termsSession[0]->term_id)->get()->getRow();
                $termName = $term->name;
            }

            $data[] = [
                'id'            => $row->attendance_id,
                'student'       => $student->first_name . ' ' . $student->last_name,
                'class'         => $class->class_name,
                'session_name'  => $academicSession->session_name,
                'term_name'     => $termName,
                'date'          => $row->date,
                'detail'        => $row->detail
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data'            => $data
        ]);
    }

    public function add()
    {
        check_permission('admin-add-student-attendance');

        $data = [
            'sessionData'       => ['campusid' => $this->campusId, 'sessionid' => $this->sessionId],
            'infousers'         => $this->db->table('users')->get()->getResult(),
            'classesinfo'       => $this->db->table('classes')->get()->getResult(),
            'sectionsclassinfo' => userClassSections(),
            'campusinfo'        => $this->db->table('campus')->where('campus_id', $this->campusId)->get()->getResult(),
            'examinfo'          => $this->db->table('exam')->where(['campus_id' => $this->campusId, 'session_id' => $this->sessionId])->get()->getResult(),
            'academic_session'  => $this->db->table('academic_session')->where('session_id', $this->sessionId)->get()->getResult(),
            'subjectinfo'       => $this->db->table('allsubject')->get()->getResult(),
        ];

        return view('admin/employees_attendance_edit', $data);
    }

    public function edit()
    {
        check_permission('admin-edit-student-attendance');
        $id = (int) $this->request->getGet('id');

        $data = [
            'sessionData'   => ['campusid' => $this->campusId, 'sessionid' => $this->sessionId],
            'info'          => $this->db->table('studentsresults')->where('student_id', $id)->get()->getRow(),
            'infostudents'  => $this->db->table('students')->get()->getResult(),
            'classesinfo'   => $this->db->table('classes')->get()->getResult(),
            'subjectinfo'   => $this->db->table('allsubject')->get()->getResult(),
        ];

        return view('admin/employees_attendance_edit', $data);
    }

    public function save()
	{
	    check_permission('admin-add-student-attendance');

	    $employee_ids = $this->request->getPost('employee_id');
	    $date = date('Y-m-d');
	    $inputDate = $this->request->getPost('date');
	    $day = date('l', strtotime($inputDate));

	    $this->db->transStart();

	    foreach ($employee_ids as $employee_id) {
	        $status = $this->request->getPost("{$employee_id}_status");
	        $checkin = $this->request->getPost("{$employee_id}_checkin_date");
	        $checkout = $status === 'A' ? $checkin : $this->request->getPost("{$employee_id}_checkout_date");

	        $empTiming = $this->db->table('emp_timings')
	            ->where('user_id', $employee_id)
	            ->get()->getRow();

	        $lc_time_def = (strtotime($checkin) - strtotime($empTiming->checkin)) / 60;
	        $el_time_def = (strtotime($empTiming->checkout) - strtotime($checkout)) / 60;

	        $builder = $this->db->table('attendance_employee');

	        $exists = $builder
	            ->where(['emp_id' => $employee_id, 'date' => $inputDate])
	            ->get()->getRow();

	        $data = [
	            'emp_id' => $employee_id,
	            'date' => $inputDate,
	            'status' => $status,
	            'checkin' => $checkin,
	            'checkout' => $checkout,
	            'lc_duration' => $lc_time_def,
	            'el_duration' => $el_time_def,
	            'user_id' => $this->userId,
	            ($exists ? 'updated_date' : 'created_date') => $date
	        ];

	        if ($exists) {
	            $builder->where(['emp_id' => $employee_id, 'date' => $inputDate])->update($data);
	        } else {
	            $builder->insert($data);
	        }
	    }

	    $this->db->transComplete();

	    return $this->response->setJSON(['success' => true, 'msg' => 'Attendance saved successfully.']);
	}

	public function get_employees()
	{
	    $campus_id = $this->request->getPost('campus_id');
	    $datevalue = $this->request->getPost('date');
	    $day = date('l', strtotime($datevalue));
	    $users = $this->db->table('users')->where(['status' => 1, 'campus_id' => $campus_id])->get()->getResult();

	    $html = '<div class="table-responsive"><table class="table" style="width:100%;">
	    <tr><th>Photo</th><th>Name</th><th>A</th><th>P</th><th>LC</th><th>EL</th></tr>';

	    foreach ($users as $row) {
	        $empTiming = $this->db->table('emp_timings')->where(['user_id' => $row->id, 'dayname' => $day])->get()->getRow();

	        if (!$empTiming) {
	            return $this->response->setBody(
	                "<div class='alert alert-danger'>Set employee timing first. <a href='/admin.php#/emp_timing?m=add'>Set Timing</a></div>"
	            );
	        }

	        $attendance = $this->db->table('attendance_employee')
	            ->where(['emp_id' => $row->id, 'date' => $datevalue])
	            ->get()->getRow();

	        $photo = $row->photo && file_exists(FCPATH . 'uploads/' . $row->photo)
	            ? "<img style='width:50px;height:50px;border-radius:30px;margin:0 auto;display:block;' src='" . base_url("uploads/{$row->photo}") . "'>"
	            : "<i class='fa fa-user' style='font-size:40px;display:block;text-align:center;'></i>";

	        $name = esc($row->first_name . ' ' . $row->last_name);

	        $checkin = $attendance->checkin ?? $empTiming->checkin;
	        $checkout = $attendance->checkout ?? $empTiming->checkout;

	        $status = $attendance->status ?? '';

	        $html .= "<tr>
	            <td>$photo<input type='hidden' name='employee_id[]' value='{$row->id}'></td>
	            <td>$name</td>
	            <td><input type='radio' name='{$row->id}_status' value='A' " . ($status == 'A' ? 'checked' : '') . "> A</td>
	            <td><input type='radio' name='{$row->id}_status' value='P' " . ($status == 'P' ? 'checked' : '') . "> P</td>
	            <td><input type='radio' name='{$row->id}_status' value='LC' " . ($status == 'LC' ? 'checked' : '') . "> LC
	                <input type='text' name='{$row->id}_checkin_date' value='$checkin' class='form-control clockpicker'></td>
	            <td><input type='radio' name='{$row->id}_status' value='EL' " . ($status == 'EL' ? 'checked' : '') . "> EL
	                <input type='text' name='{$row->id}_checkout_date' value='$checkout' class='form-control clockpicker'></td>
	        </tr>";
	    }

	    $html .= '</table></div><script>$(function(){$(".clockpicker").clockpicker();});</script>';

	    return $this->response->setBody($html);
	}

	public function delete()
	{
	    check_permission('admin-del-attendance');
	    $id = (int) $this->request->getGet('id');

	    $this->db->transStart();
	    $this->db->table('attendance_employee')->where('attendance_id', $id)->delete();
	    $this->db->transComplete();

	    return $this->response->setJSON(['success' => true, 'msg' => 'Attendance record deleted successfully.']);
	}



}
