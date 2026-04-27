<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;
use CodeIgniter\Database\BaseBuilder;


class Students_bulk_cnic extends BaseController
{
    protected $db;
    protected $session;
    protected $students;

    public function __construct()
    {
      $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-students');

        $this->students = new StudentsModel(); // Now correctly namespaced
    }

    public function index()
    {
        $campus_id = $this->session->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        $currentrole = currentUserRoles();

        $sectionsclassinfo = in_array(5, $currentrole) ? teacherSubjectSections() : $this->userClassSections();

        $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();

        return view('admin/students_bulk_cnic', [
            'sectionsclassinfo' => $sectionsclassinfo,
            'campus_info' => $campus_info,
        ]);
    }

    protected function userClassSections()
{
    $db = \Config\Database::connect();
    $campus_id = $this->session->get('member_campusid');

    return $db->table('class_section cs')
        ->select('cs.cls_sec_id, cs.section_id, CONCAT(c.class_name, " (", s.section_name, ")") as sectionclassname')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.status', 1)
        ->where('cs.campus_id', $campus_id)
        ->get()
        ->getResultArray(); // Must return array, not stdClass
}

    public function data()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $currentrole = currentUserRoles();

        $sectionsclassinfo = in_array(5, $currentrole) ? teacherSubjectSections() : userClassSections();

        if ($cls_sec_id) {
            $student_class = $this->db->query("
                SELECT * FROM student_class 
                WHERE student_id IN (
                    SELECT student_id FROM students WHERE status = 1 AND campus_id = $campusid
                ) 
                AND session_id = $sessionid 
                AND cls_sec_id = $cls_sec_id 
                ORDER BY cls_sec_id ASC
            ")->getResult();
        } else {
            $student_class = $this->db->query("
                SELECT * FROM student_class 
                WHERE student_id IN (
                    SELECT student_id FROM students WHERE status = 1 AND campus_id = $campusid
                ) 
                AND session_id = $sessionid 
                ORDER BY cls_sec_id ASC
            ")->getResult();
        }

        $studentsList = '<table class="table table-striped table-bordered table-hover" id="students-datatable" style="font-size:10px;width: 100%;">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Father Name</th>
            <th>Father CNIC</th>
            <th>Action</th>
        </tr>
        </thead><tbody>';

        foreach ($student_class as $studentinfo) {
            $students = $this->db->table('students')
                ->where('campus_id', $campusid)
                ->where('student_id', $studentinfo->student_id)
                ->where('status', 1)
                ->get()->getResult();

            foreach ($students as $value) {
                $parentinfo = $this->db->table('parents')->where('parent_id', $value->parent_id)->get()->getRow();
                $parentinfoCampus = $this->db->table('parents')->where('parent_id', $value->parent_id)->where('campus_id', $campusid)->get()->getRow();

                $noParent = !$parentinfoCampus ? '*' : '';

                $f_name = $parentinfo->f_name ?? '';
                $father_cnic = $parentinfo->father_cnic ?? '';
                $parent_id = $parentinfo->parent_id ?? 0;

                $studentsList .= '<tr>
                    <th>' . $noParent . '<input type="hidden" id="studentID' . $value->student_id . '" value="' . $value->student_id . '">
                    <input type="hidden" id="parent_id' . $value->student_id . '" value="' . $parent_id . '">' . $value->student_id . '</th>
                    <td>' . $value->first_name . ' ' . $value->last_name . '</td>
                    <td><input type="text" id="father_name' . $value->student_id . '" class="form-control" value="' . $f_name . '"></td>
                    <td><input type="text" id="father_cnic' . $value->student_id . '" class="form-control" value="' . $father_cnic . '" data-inputmask=\'"mask": "99999-9999999-9"\' data-mask></td>
                    <td><a id="save' . $value->student_id . '" data-id="' . $value->student_id . '" class="btn btn-primary btn-xs">Save</a></td>
                </tr>';

                $studentsList .= '<script>$(function(){ $("[data-mask]").inputmask(); });</script>';
                $studentsList .= '<script>
                $("#save' . $value->student_id . '").click(function(){
                    $.post("' . base_url('admin/students_bulk_cnic/update-parent-info') . '", {
                        student_id: $("#studentID' . $value->student_id . '").val(),
                        parent_id: $("#parent_id' . $value->student_id . '").val(),
                        father_name: $("#father_name' . $value->student_id . '").val(),
                        father_cnic: $("#father_cnic' . $value->student_id . '").val()
                    }, function(res){
                        toastr.success("Updated Successfully");
                    });
                });
                </script>';
            }
        }

        $studentsList .= '</tbody></table>';

        return $this->response->setBody($studentsList);
    }

    public function updateParentInfo()
    {
        $campusid = $this->session->get('member_campusid');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        $parentID = $this->request->getPost('parent_id');
        $studentID = $this->request->getPost('student_id');
        $father_cnic = $this->request->getPost('father_cnic');
        $father_name = $this->request->getPost('father_name');

        $existingParent = $this->db->table('parents')
            ->where('campus_id', $campusid)
            ->where('father_cnic', $father_cnic)
            ->get()->getRow();

        if ($existingParent && !empty($existingParent->father_cnic)) {
            $this->db->table('students')->where('student_id', $studentID)->where('campus_id', $campusid)->update([
                'parent_id' => $existingParent->parent_id,
                'updated_date' => $date,
                'user_id' => $user_id,
            ]);

            $this->db->table('parents')->where('parent_id', $parentID)->update([
                'f_name' => $father_name,
                'father_cnic' => $father_cnic,
                'created_date' => $date,
                'user_id' => $user_id,
            ]);
        } else {
            $parentData = [
                'f_name' => $father_name,
                'father_cnic' => $father_cnic,
                'password' => '$2y$11$devU5YfJe43QwVEdvRU3UevZO.vlbd3u56yeGYt2k1d2c56VYjm/a',
                'campus_id' => $campusid,
                'created_date' => $date,
                'user_id' => $user_id,
            ];

            $this->db->table('parents')->insert($parentData);
            $new_parent_id = $this->db->insertID();

            $this->db->table('students')->where('student_id', $studentID)->where('campus_id', $campusid)->update([
                'parent_id' => $new_parent_id,
                'updated_date' => $date,
                'user_id' => $user_id,
            ]);
        }

        return $this->response->setJSON(['status' => 'success']);
    }
}
