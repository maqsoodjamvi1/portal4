<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Admin\StudentsModelContactList;

class StudentsList extends BaseController
{
    protected $db;
    protected $studentsModel;

    public function __construct()
    {
        helper(['form', 'url']);
        check_permission('admin-students-contact-list');
        $this->db = \Config\Database::connect();
        $this->studentsModel = new StudentsModelContactList();
    }

    public function index()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');

        $where = [ 'session_id' => $sessionId, 'campus_id' => $campusId ];
        $testSeries = $this->db->table('test_series')->where($where)->get()->getResult();

        $role = currentUserRoles();
        $sectionsClassInfo = in_array(5, $role) ? teacherSubjectSections() : userClassSections();

        return view('admin/students_list', [
            'sectionsclassinfo' => $sectionsClassInfo,
            'test_series' => $testSeries
        ]);
    }

    public function data()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $schoolInfo = getSchoolInfo();
        $testId = $this->request->getPost('test_id');
        $status = $this->request->getGet('status');

        $list = $this->studentsModel->getDatatables();
        $response = [];
        $no = $this->request->getPost('start');

        foreach ($list as $row) {
            $no++;
            $studentId = $row->student_id;
            $parentInfo = $this->db->table('parents')->where('parent_id', $row->parent_id)->get()->getRow();

            $studentClass = $this->db->table('student_class')
                ->where(['student_id' => $studentId, 'status' => $status, 'session_id' => $sessionId])
                ->get()->getRow();

            $className = '';
            $sectionName = '';
            if ($studentClass) {
                $classSection = $this->db->table('class_section')->where('cls_sec_id', $studentClass->cls_sec_id)->get()->getRow();
                if ($classSection) {
                    $class = $this->db->table('classes')->where('class_id', $classSection->class_id)->get()->getRow();
                    $section = $this->db->table('sections')->where('section_id', $classSection->section_id)->get()->getRow();
                    $className = $class->class_name ?? '';
                    $sectionName = $section->section_name ?? '';
                }
            }

            $imgPath = FCPATH . "uploads/" . $row->profile_photo;
            if ($row->profile_photo && file_exists($imgPath)) {
                $profilePhoto = "<img style='width:50px;height:50px;text-align: center;display: block;border-radius: 30px;margin: 0 auto;' src='" . base_url("uploads/" . $row->profile_photo) . "' >";
            } else {
                $profilePhoto = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
            }

            $url = rawurlencode('http://' . $schoolInfo->domain . '.timesoftsol.com/test_series_result_card/?cnic=' . ($parentInfo->father_cnicnew ?? '') . '&session_id=' . $sessionId . '&test_id=' . $testId);

            $response[] = [
                'id' => $studentId,
                'profile_photo' => $profilePhoto,
                'name' => $row->first_name . " " . $row->last_name,
                'f_name' => $parentInfo->f_name ?? '',
                'class' => $className . "(" . $sectionName . ")",
                'w_contacts' => '<a target="_blank" class="btn btn-success btn-xs" href="https://wa.me/' . ($parentInfo->whatsapp ?? '') . '?text=' . $url . '"><i class="fab fa-whatsapp"></i> Send</a>'
            ];
        }

        return $this->response->setJSON([
            'draw' => (int) $this->request->getPost('draw'),
            'recordsTotal' => $this->studentsModel->countAll(),
            'recordsFiltered' => $this->studentsModel->countFiltered(),
            'data' => $response,
        ]);
    }

    public function get_parentinfo()
    {
        $campusId = session('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';

        $parents = $this->db->query("SELECT * FROM parents WHERE (f_name LIKE '%$term%') AND campus_id = $campusId")
            ->getResultArray();

        $data = [];
        foreach ($parents as $parent) {
            $hasStudent = $this->db->table('students')
                ->where(['parent_id' => $parent['parent_id'], 'campus_id' => $campusId])
                ->countAllResults();
            if ($hasStudent > 0) {
                $data[] = ["id" => $parent['parent_id'], "text" => $parent['f_name']];
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
        )->getResultArray();

        $data = [];
        foreach ($students as $student) {
            $classStudent = $this->db->table('student_class')->where('student_id', $student['student_id'])->get()->getRow();
            $parentName = $this->db->table('parents')->select('f_name')->where('parent_id', $student['parent_id'])->get()->getRow()->f_name ?? '';
            if ($classStudent) {
                $text = $student['first_name'] . " " . $student['last_name'] . " c/o " . $parentName;
                $data[] = ["id" => $student['student_id'], "text" => $text];
            }
        }

        return $this->response->setJSON($data);
    }
}
