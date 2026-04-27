<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;

class Students_bulk_info extends BaseController
{
    protected $db;
    protected $session;
    protected $students;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text']);
        check_permission('admin-students');
        $this->students = new StudentsModel();  
    }

    public function index()
    {
        $campus_id = $this->session->get('member_campusid');
        $currentrole = currentUserRoles();

        $data = [
            'sectionsclassinfo' => in_array(5, $currentrole) ? teacherSubjectSections() : $this->userClassSections(),
            'campus_info' => $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow(),
            'campus_flags' => $this->getCampusFlags($campus_id)
        ];

        return view('admin/students_bulk_info', $data);
    }

    protected function userClassSections()
    {
        return $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.section_id, CONCAT(c.class_name, " (", s.section_name, ")") as sectionclassname')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.status', 1)
            ->where('cs.campus_id', $this->session->get('member_campusid'))
            ->get()
            ->getResultArray();
    }

    protected function getCampusFlags($campus_id)
    {
        return $this->db->table('campus')
            ->select('daycare_flag, boarding_flag')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();
    }

    public function data()
    {
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $cls_sec_id = $this->request->getPost('cls_sec_id');

        $query = $this->db->table('student_class sc')
            ->join('students s', 's.student_id = sc.student_id')
            ->where('s.campus_id', $campusid)
            ->where('s.status', 1)
            ->where('sc.session_id', $sessionid);

        if (!empty($cls_sec_id)) {
            $query->where('sc.cls_sec_id', $cls_sec_id);
        }

        $student_class = $query->orderBy('sc.cls_sec_id', 'ASC')
            ->get()
            ->getResult();

        $studentsList = '';
        $campus_flags = $this->getCampusFlags($campusid);

        foreach ($student_class as $studentinfo) {
            $student = $this->db->table('students')
                ->where('student_id', $studentinfo->student_id)
                ->get()
                ->getRow();

            $parent = $this->db->table('parents')
                ->where('parent_id', $student->parent_id)
                ->get()
                ->getRow();

            $studentsList .= view('admin/partials/student_bulk_info_row', [
                'student' => $student,
                'parent_name' => $parent->f_name ?? '',
                'date_of_birth' => $student->date_of_birth ?? '',
                'gender' => $student->gender ?? 'male',
                'daycare_flag' => $student->flag ?? '',
                'profile_photo' => $student->profile_photo ?? '',
                'campus_flags' => $campus_flags
            ]);
        }

        return $this->response->setBody($studentsList);
    }

    public function saveStudentInfo()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'student_id' => 'required|numeric',
            'date_of_birth' => 'permit_empty|valid_date',
            'gender' => 'required|in_list[male,female]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $campusid = $this->session->get('member_campusid');
        $campus_flags = $this->getCampusFlags($campusid);
        $flag = null;

        if ($campus_flags->daycare_flag == 1 && $campus_flags->boarding_flag == 1) {
            $flag = $this->request->getPost('daycare_flag');
        } elseif ($campus_flags->daycare_flag == 1) {
            $flag = 1;
        } elseif ($campus_flags->boarding_flag == 1) {
            $flag = 2;
        }

        $data = [
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'flag' => $flag,
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id' => $this->session->get('member_userid')
        ];

        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads', $newName);
            $data['profile_photo'] = $newName;
        }

        $this->db->table('students')
            ->where('student_id', $this->request->getPost('student_id'))
            ->update($data);

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Student info updated successfully'
        ]);
    }
}