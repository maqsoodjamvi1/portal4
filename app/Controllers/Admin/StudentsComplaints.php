<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class StudentsComplaints extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        check_permission('admin-student-complaints');
        return view('admin/students_complaints');
    }

    public function data()
    {
        check_permission('admin-student-complaints');
        $sessionid = $this->session->get('member_sessionid');
        $campus_id = $this->session->get('member_campusid');

        $results = $this->db->query("SELECT * FROM complaints WHERE student_id IN (SELECT student_id FROM students WHERE campus_id = $campus_id)")->getResult();

        $academic_session = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();

        $data = [];
        foreach ($results as $row) {
            $student = $this->db->table('students')->where('student_id', $row->student_id)->get()->getRow();
            $studentClass = $this->db->table('student_class')->where('student_id', $row->student_id)->get()->getRow();
            $cls_sec_id = $studentClass->cls_sec_id ?? 0;
            $classSection = $this->db->table('class_section')->where('cls_sec_id', $cls_sec_id)->get()->getRow();
            $class = $this->db->table('classes')->where('class_id', $classSection->class_id ?? 0)->get()->getRow();

            $term = $this->db->query("SELECT * FROM terms_session WHERE session_id = $sessionid AND '{$row->date}' BETWEEN start_date AND end_date")->getRow();
            $termName = $term ? ($this->db->table('terms')->where('term_id', $term->term_id)->get()->getRow('name')) : '';

            $data[] = [
                'id' => $row->cid,
                'student' => ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''),
                'class' => $class->class_name ?? '',
                'session_name' => $academic_session->session_name ?? '',
                'term_name' => $termName,
                'date' => $row->date,
                'detail' => $row->detail
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function add()
    {
        check_permission('admin-add-student-complaint');
        $campus_id = $this->session->get('member_campusid');
        $session_id = $this->session->get('member_sessionid');

        $students = $this->db->table('students')->get()->getResult();
        $class_sections = $this->db->table('class_section')->where('campus_id', $campus_id)->get()->getResult();

        $sectionsClassInfo = [];
        foreach ($class_sections as $section) {
            $class = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sec = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsClassInfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => ($class->class_name ?? '') . ' (' . ($sec->section_name ?? '') . ')'
            ];
        }

        return view('admin/students_complaints_edit', [
            'infostudents' => $students,
            'sectionsclassinfo' => $sectionsClassInfo,
            'campusinfo' => $this->db->table('campus')->where('campus_id', $campus_id)->get()->getResult(),
            'examinfo' => $this->db->table('exam')->where(['campus_id' => $campus_id, 'session_id' => $session_id])->get()->getResult(),
            'academic_session' => $this->db->table('academic_session')->where('session_id', $session_id)->get()->getResult(),
            'subjectinfo' => $this->db->table('allsubject')->get()->getResult()
        ]);
    }

    public function save()
    {
        check_permission('admin-add-student-complaint');

        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $campusid = $this->session->get('member_campusid');

        $student_ids = $this->request->getPost('student_id');
        $details = $this->request->getPost('detail');
        $type = $this->request->getPost('type');
        $complaintDate = $this->request->getPost('date');

        $this->db->transStart();

        foreach ($student_ids as $index => $student_id) {
            if ($this->request->getPost('enable_com_' . $student_id)) {
                $this->db->table('complaints')->where([
                    'student_id' => $student_id,
                    'type' => $type,
                    'date' => $complaintDate
                ])->delete();

                $this->db->table('complaints')->insert([
                    'student_id' => $student_id,
                    'date' => $complaintDate,
                    'type' => $type,
                    'detail' => $details[$index]
                ]);

                $student = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();
                $parent = $this->db->table('parents')->where('parent_id', $student->parent_id ?? 0)->get()->getRow();

                if ($parent) {
                    $this->db->table('sms')->insert([
                        'mobile' => $parent->father_contact,
                        'message' => $details[$index],
                        'campus_id' => $campusid,
                        'parent_id' => $parent->parent_id,
                        'status' => 0,
                        'user_id' => $user_id,
                        'created_date' => $date
                    ]);
                }
            }
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Complaint Success']);
    }

    public function delete($id)
    {
        check_permission('admin-del-complaint');
        $this->db->transStart();
        $this->db->table('complaints')->where('cid', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Complaint Success']);
    }
}
