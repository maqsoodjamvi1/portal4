<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use stdClass;

class StudentsLateComming extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-add-student-latecomming');
    }

    public function index()
    {
        return view('admin/students_latecomming', $this->data());
    }

    public function data()
    {
        $response = new stdClass();
        $draw = $this->request->getPost('draw');
        $sessionid = $this->session->get('member_sessionid');

        $response->draw = $draw;
        $response->recordsTotal = $this->db->table('attendance')->countAll();

        $results = $this->db->table('attendance')->get()->getResult();
        $response->recordsFiltered = $response->recordsTotal;

        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()->getRow();

        $response->data = [];

        foreach ($results as $row) {
            $student = $this->db->table('students')->where('student_id', $row->student_id)->get()->getRow();
            $studentClass = $this->db->table('student_class')->where('student_id', $row->student_id)->get()->getRow();
            $class = $this->db->table('classes')->where('class_id', $studentClass->class_id)->get()->getRow();

            $termsSession = $this->db->query(
                "SELECT * FROM terms_session WHERE session_id = ? AND ? BETWEEN start_date AND end_date",
                [$sessionid, $row->date]
            )->getResult();

            $termName = $termsSession ? $this->db->table('terms')->where('term_id', $termsSession[0]->term_id)->get()->getRow('name') : '';

            $response->data[] = [
                'id' => $row->cid ?? null,
                'student' => $student->first_name . ' ' . $student->last_name,
                'class' => $class->class_name,
                'session_name' => $academic_session->session_name,
                'term_name' => $termName,
                'date' => $row->date,
                'detail' => $row->detail
            ];
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-student-latecomming');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $data = [
            'sessionData' => ['campusid' => $campusid, 'sessionid' => $sessionid],
            'infostudents' => $this->db->table('students')->get()->getResult(),
            'classesinfo' => $this->db->table('classes')->get()->getResult(),
            'sectionsclassinfo' => userClassSections(),
            'campusinfo' => $this->db->table('campus')->where('campus_id', $campusid)->get()->getResult(),
            'examinfo' => $this->db->table('exam')->where(['campus_id' => $campusid, 'session_id' => $sessionid])->get()->getResult(),
            'academic_session' => $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getResult(),
            'subjectinfo' => $this->db->table('allsubject')->get()->getResult(),
        ];

        return view('admin/students_latecomming_edit', $data);
    }

    public function save()
    {
        check_permission('admin-add-student-latecomming');
        $student_ids = $this->request->getPost('student_id');
        $user_id = $this->session->get('member_userid');
        $campusid = $this->session->get('member_campusid');
        $date = date('Y-m-d');

        $this->db->transBegin();

        foreach ($student_ids as $student_id) {
            $datevalue = $this->request->getPost('date');
            $day = date('l', strtotime($datevalue));

            $classSecInfo = $this->db->table('student_class')
                ->where('student_id', $student_id)
                ->where('status', 1)
                ->get()->getRow();

            $schoolTimings = $this->db->query("
                SELECT *, (checkout_timing - checkin_timing) AS duration
                FROM school_timings 
                WHERE cls_sec_id = ? AND dayname = ? AND type_id = (
                    SELECT type_id FROM school_timing_types WHERE STATUS=1 AND campus_id=?
                )",
                [$classSecInfo->cls_sec_id, $day, $campusid]
            )->getRow();

            $checkinTime = $this->request->getPost($student_id . '_checkin_date');
            if ($schoolTimings && $checkinTime) {
                $timeDef = (strtotime($checkinTime) - strtotime($schoolTimings->checkin_timing)) / 60;
                $this->db->table('attendance')
                    ->where(['student_id' => $student_id, 'date' => $datevalue])
                    ->update([
                        'checkin' => $checkinTime,
                        'lc_duration' => $timeDef,
                        'updated_date' => $date,
                        'user_id' => $user_id
                    ]);
            }
        }

        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Attendance Success']);
    }

    public function delete()
    {
        check_permission('admin-del-attendance');
        $id = (int)$this->request->getGet('id');

        $this->db->transBegin();
        $this->db->table('classes')->where('id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Attendance Success']);
    }

    public function get_studentinfo()
	{
	    $campusid = session('member_campusid');
	    $term = $this->request->getPost('term');

	    $keyword = $term['term'];
	    $builder = $this->db->table('students');
	    $builder->like('first_name', $keyword)
	            ->orLike('last_name', $keyword)
	            ->where('status', 1)
	            ->where('campus_id', $campusid);

	    $students = $builder->get()->getResultArray();

	    $data = [];

	    foreach ($students as $student) {
	        $classStudent = $this->db->table('student_class')
	            ->where('status', 1)
	            ->where('student_id', $student['student_id'])
	            ->get()->getRow();

	        if ($classStudent) {
	            $data[] = [
	                'id' => $student['student_id'],
	                'text' => $student['first_name'] . ' ' . $student['last_name']
	            ];
	        }
	    }

	    return $this->response->setJSON($data);
	}

	public function get_students_byclass()
	{
	    $eid = $this->request->getPost('eid');
	    $session_id = $this->request->getPost('session_id');
	    $campus_id = $this->request->getPost('campus_id');
	    $student_id = $this->request->getPost('student_id');
	    $datevalue = $this->request->getPost('date');

	    $timestamp = strtotime($datevalue);
	    $day = date('l', $timestamp);

	    $studentsList = '';
	    $studentsList .= '<input type="hidden" name="campus_id" value="' . esc($campus_id) . '">';
	    $studentsList .= '<input type="hidden" name="class_id" value="' . esc($student_id) . '">';

	    $students = $this->db->table('students')
	        ->where('status', 1)
	        ->where('parent_id', function ($builder) use ($student_id) {
	            return $builder->select('parent_id')->from('students')->where('student_id', $student_id);
	        })
	        ->get()->getResult();

	    if (!empty($students)) {
	        $studentsList .= '<div class="table-responsive"><table class="table" style="width:100%;">
	            <tr><th style="width:15%;">Photo</th><th style="width:15%;">Name</th><th style="width:15%;">Select And Set Time</th></tr>';

	        foreach ($students as $row) {
	            $attendance = $this->db->table('attendance')
	                ->where(['student_id' => $row->student_id, 'date' => $datevalue])
	                ->get()->getRow();

	            if ($attendance) {
	                $photoPath = FCPATH . "uploads/" . $row->profile_photo;
	                $photoTag = file_exists($photoPath) && !empty($row->profile_photo)
	                    ? "<img style='width:50px;height:50px;border-radius:30px;display:block;margin:auto;' src='" . base_url("uploads/" . $row->profile_photo) . "'>"
	                    : "<i class='fa fa-user' style='font-size:40px;display:block;text-align:center;'></i>";

	                $studentsList .= '<tr>
	                    <td>' . $photoTag . '<input type="hidden" name="student_id[]" value="' . $row->student_id . '" class="form-control"></td>
	                    <td>' . esc($row->first_name . ' ' . $row->last_name) . '<br>' . esc($row->reg_no) . '</td>
	                    <td>';

	                if ($attendance->status != 'P') {
	                    $studentsList .= esc($attendance->status);
	                } else {
	                    $studentsList .= ($attendance->lc_duration > 0)
	                        ? esc($attendance->lc_duration . " Min Late")
	                        : '
	                        <input type="checkbox" class="toggle_option" value="1" id="' . $row->student_id . '_late_comming_toggle" 
	                            name="' . $row->student_id . '_status" style="height:17px;width:20px;margin-right:2px;">
	                        <div class="input-group ' . $row->student_id . '_clockpicker" id="' . $row->student_id . '_clockpicker" 
	                            data-placement="left" data-align="top" data-autoclose="true">
	                            <input type="text" class="form-control" disabled name="' . $row->student_id . '_checkin_date" id="' . $row->student_id . '_checkin_date" value="">
	                            <span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
	                        </div>
	                        <script>
	                            $("#' . $row->student_id . '_late_comming_toggle").click(function() {
	                                if ($(this).is(":checked")) {
	                                    $("#' . $row->student_id . '_checkin_date").removeAttr("disabled");
	                                    let now = new Date();
	                                    let time = now.getHours() + ":" + now.getMinutes();
	                                    $("#' . $row->student_id . '_checkin_date").val(time);
	                                } else {
	                                    $("#' . $row->student_id . '_checkin_date").attr("disabled", "disabled").val("");
	                                }
	                            });

	                            var input' . $row->student_id . ' = $("#' . $row->student_id . '_clockpicker").clockpicker({ autoclose: true, "default": "now" });

	                            $("#' . $row->student_id . '_late_comming_toggle").click(function(e) {
	                                e.stopPropagation();
	                                input' . $row->student_id . '.clockpicker("show").clockpicker("toggleView", "hours");
	                            });
	                        </script>';
	                }

	                $studentsList .= '</td></tr>';
	            }
	        }

	        $studentsList .= '</table></div>';
	    } else {
	        $studentsList .= '<div class="alert alert-warning">No students found or today is off.</div>';
	    }

	    return $this->response->setBody($studentsList);
	}



}
