<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModelResultList;

class StudentsWithResultList extends BaseController
{
    protected $studentsModel;
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        check_permission('admin-students-contact-list');
        $this->db = \Config\Database::connect();
        $this->studentsModel = new StudentsModelResultList();
    }

    public function index()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $schoolInfo = getSchoolInfo();
        $currentRole = currentUserRoles();

        $examList = $this->db->table('exam')
                            ->where('session_id', $sessionId)
                            ->where('campus_id', $campusId)
                            ->get()->getResult();

        $sections = in_array(5, $currentRole) ? teacherSubjectSections() : userClassSections();

        return view('admin/students_w_result_list', [
            'sectionsclassinfo' => $sections,
            'exams'             => $examList
        ]);
    }

    public function data()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $schoolInfo = getSchoolInfo();
        $examId = $this->request->getGet('exam_id');

        $list = $this->studentsModel->getDatatables();
        $data = [];
        $no = $this->request->getPost('start');

        foreach ($list as $row) {
            $no++;

            $studentData = [];
            $studentData['id'] = $row->student_id;

            $profilePath = FCPATH . 'uploads/' . $row->profile_photo;
            if ($row->profile_photo && file_exists($profilePath)) {
                $studentData['profile_photo'] = "<img style='width:50px;height:50px;border-radius:30px;margin:0 auto;display:block;' src='" . base_url("uploads/{$row->profile_photo}") . "'>";
            } else {
                $studentData['profile_photo'] = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
            }

            $className = '';
            $sectionName = '';
            $address = '';

            $studentClass = $this->db->table('student_class')
                ->where('student_id', $row->student_id)
                ->where('status', $this->request->getGet('status'))
                ->where('session_id', $sessionId)
                ->get()->getRow();

            if ($studentClass) {
                $clsSec = $this->db->table('class_section')
                    ->where('cls_sec_id', $studentClass->cls_sec_id)->get()->getRow();
                if ($clsSec) {
                    $classInfo = $this->db->table('classes')->where('class_id', $clsSec->class_id)->get()->getRow();
                    $sectionInfo = $this->db->table('sections')->where('section_id', $clsSec->section_id)->get()->getRow();

                    $className = $classInfo->class_name ?? '';
                    $sectionName = $sectionInfo->section_name ?? '';
                }
            }

            $parentInfo = $this->db->table('parents')->where('parent_id', $row->parent_id)->get()->getRow();
            $studentData['name'] = $row->first_name . ' ' . $row->last_name;
            $studentData['f_name'] = $parentInfo->f_name ?? '';
            $studentData['age'] = date_diff(date_create($row->date_of_birth), date_create('now'))->y . ' Years';
            $studentData['gender'] = $row->gender;
            $studentData['address'] = $parentInfo->address_line1 ?? '';
            $studentData['class'] = $className . '(' . $sectionName . ')';
            $studentData['section'] = $sectionName;

            $url = rawurlencode("https://{$schoolInfo->domain}.timesoftsol.com/students_results_card/?pid={$parentInfo->parent_id}&session_id={$sessionId}&exam_id={$examId}");
            $studentData['w_contacts'] = "<a target='_blank' class='btn btn-success btn-xs' href='https://wa.me/{$parentInfo->whatsapp}?text={$url}'><i class='fab fa-whatsapp'></i> Send</a>";

            $data[] = $studentData;
        }

        return $this->response->setJSON([
            'draw'            => $this->request->getPost('draw'),
            'recordsTotal'    => $this->studentsModel->countAll(),
            'recordsFiltered' => $this->studentsModel->countFiltered(),
            'data'            => $data,
        ]);
    }

    public function get_parentinfo()
    {
        $campusId = session('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $parents = $this->db->query("SELECT * FROM parents WHERE f_name LIKE '%$term%' AND campus_id = $campusId")->getResult();

        $data = [];
        foreach ($parents as $parent) {
            $student = $this->db->table('students')
                ->where('parent_id', $parent->parent_id)
                ->where('campus_id', $campusId)
                ->get()->getRow();
            if ($student) {
                $data[] = ["id" => $parent->parent_id, "text" => $parent->f_name];
            }
        }

        return $this->response->setJSON($data);
    }

    public function get_studentinfo()
    {
        $campusId = session('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $status = $this->request->getPost('status');

        $students = $this->db->query(
            "SELECT * FROM students WHERE (first_name LIKE '%$term%' OR last_name LIKE '%$term%') AND status = $status AND campus_id = $campusId"
        )->getResult();

        $data = [];
        foreach ($students as $student) {
            $classStudent = $this->db->table('student_class')->where('student_id', $student->student_id)->get()->getRow();
            $parent = $this->db->table('parents')->where('parent_id', $student->parent_id)->get()->getRow();

            if ($classStudent && $parent) {
                $data[] = ["id" => $student->student_id, "text" => "{$student->first_name} {$student->last_name} c/o {$parent->f_name}"];
            }
        }

        return $this->response->setJSON($data);
    }
}
