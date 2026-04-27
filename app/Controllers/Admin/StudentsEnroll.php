<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;
use CodeIgniter\Database\BaseConnection;
use stdClass;

class StudentsEnroll extends BaseController
{
    protected $db;
    protected $session;
    protected $studentsModel;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-students');
        helper(['form', 'url']);
        $this->studentsModel = new StudentsModel();
    }

    public function index()
    {
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
        $this->template_data['campus_info'] = $campus_info;

        return view('admin/enroll_students', $this->template_data);
    }

    public function data()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        if ($cls_sec_id) {
            $student_class = $this->db->query(
                'SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND campus_id=?) AND session_id =? AND cls_sec_id =? ORDER BY cls_sec_id ASC',
                [$campusid, $sessionid, $cls_sec_id]
            )->getResult();
        } else {
            $student_class = $this->db->query(
                'SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND campus_id=?) AND session_id =? ORDER BY cls_sec_id ASC',
                [$campusid, $sessionid]
            )->getResult();
        }

        $classSection = $this->db->table('class_section')->where('cls_sec_id', $cls_sec_id)->get()->getRow();

        $studentsList = '<table class="table table-striped table-bordered table-hover" id="students-datatable"  style="font-size:10px;width: 100%;"><thead><tr>
            <th style="width: 55px !important;" nowrap>#</th>
            <th style="width: 100px !important;">Name</th>
            <th style="width:130px;">School</th>
            <th style="width:130px;">Academy</th>
            <th style="width:130px;">Transport</th>
            <th style="width:130px;">Hostel</th>
            <th style="width:50px;">Action</th>
        </tr>
        </thead>
        <tbody>';

        foreach ($student_class as $studentinfo) {
            $list = $this->db->table('students')
                ->where('campus_id', $campusid)
                ->where('student_id', $studentinfo->student_id)
                ->where('status', 1)
                ->get()->getResult();

            foreach ($list as $key => $value) {
                $parentinfo = $this->db->table('parents')
                    ->where('parent_id', $value->parent_id)
                    ->get()->getRow();

                $f_name = '';
                $father_contact = '';
                $mother_contact = '';
                $emergency_contact = '';
                $whatsapp_contact = '';
                $address = '';
                $balance = 0;

                if ($parentinfo) {
                    $address = $parentinfo->address_line1;
                    $f_name = $parentinfo->f_name;
                    $father_cnic = $parentinfo->father_cnicnew ?? '';
                    $father_contact = $parentinfo->father_contact;
                    $mother_contact = $parentinfo->mother_contact;
                    $whatsapp_contact = $parentinfo->whatsapp;
                    $emergency_contact = $parentinfo->emergency_contact;
                }

                $studentsList .= '<tr>
                    <th nowrap>
                        <input type="hidden" id="studentID' . $value->student_id . '" name="student_id' . $value->student_id . '" value="' . $value->student_id . '">
                        <input type="hidden" value="' . $value->parent_id . '" id="parent_id' . $value->student_id . '" name="parent_id">' . $value->student_id . '
                    </th>';
                $studentsList .= '<td nowrap>' . $value->first_name . ' ' . $value->last_name . '<br> c/o ' . $f_name . '</td>';

                $studentsList .= '<td nowrap><input type="checkbox" ';
                if ($value->s_flag == 1) {
                    $studentsList .= 'checked';
                }
                $studentsList .= ' id="s_flag' . $value->student_id . '" class="form-control" name="s_flag" value="1"></td>';

                $studentsList .= '<td nowrap><input type="checkbox" ';
                if ($value->a_flag == 1) {
                    $studentsList .= 'checked';
                }
                $studentsList .= ' id="a_flag' . $value->student_id . '" class="form-control" name="a_flag" value="1" ></td>';

                $studentsList .= '<td nowrap><input type="checkbox" ';
                if ($value->t_flag == 1) {
                    $studentsList .= 'checked';
                }
                $studentsList .= ' id="t_flag' . $value->student_id . '" class="form-control" name="t_flag" value="1" ></td>';

                $studentsList .= '<td nowrap><input type="checkbox" ';
                if ($value->h_flag == 1) {
                    $studentsList .= 'checked';
                }
                $studentsList .= ' id="h_flag' . $value->student_id . '" class="form-control" name="h_flag" value="1" ></td>';

                $studentsList .= '<td>
                    <a  id="save' . $value->student_id . '"  data-id="' . $value->student_id . '" class="btn btn-primary btn-xs">Save</a>
                    </td></tr>';
                $studentsList .= '<script type="text/javascript">
                    $("#save' . $value->student_id . '").click(function(){
                        var student_id = $("#studentID' . $value->student_id . '").val();
                        var s_flag = $("#s_flag' . $value->student_id . '").prop("checked") ? 1 : 0;
                        var a_flag = $("#a_flag' . $value->student_id . '").prop("checked") ? 1 : 0;
                        var t_flag = $("#t_flag' . $value->student_id . '").prop("checked") ? 1 : 0;
                        var h_flag = $("#h_flag' . $value->student_id . '").prop("checked") ? 1 : 0;

                        $.ajax({
                            url: "' . base_url('admin/students_enroll/enrollstudentinfo') . '",
                            type: "POST",
                            data: {s_flag: s_flag, a_flag: a_flag, t_flag: t_flag, h_flag: h_flag, student_id: student_id},
                            success: function(res) {
                                toastr.success("Updated Successfully"); 
                            }
                        });
                    });
                </script>';
            }
        }
        $studentsList .= '</tbody></table>';

        echo $studentsList;
        exit;
    }



    public function enrollStudentInfo()
    {
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        $studentID = $this->request->getPost('student_id');
        $s_flag = $this->request->getPost('s_flag');
        $a_flag = $this->request->getPost('a_flag');
        $t_flag = $this->request->getPost('t_flag');
        $h_flag = $this->request->getPost('h_flag');

        $data = [
            's_flag' => trim($s_flag),
            'a_flag' => trim($a_flag),
            't_flag' => trim($t_flag),
            'h_flag' => trim($h_flag),
            'updated_date' => $date,
            'user_id' => $user_id
        ];

        $this->db->table('students')
            ->where('student_id', $studentID)
            ->where('campus_id', $campusid)
            ->update($data);

        // Optionally send a JSON response
        return $this->response->setJSON(['status' => 'success']);
    }
}
