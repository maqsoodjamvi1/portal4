<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModelContactList;

class StudentsContactList extends BaseController
{
    protected $db;
    protected $session;
    protected $students;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-students-contact-list');
        $this->students = new StudentsModelContactList();
    }

    public function index()
    {
        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $data = [
            'sectionsclassinfo' => $sectionsclassinfo,
        ];

        return view('admin/students_contact_list', $data);
    }

    public function data()
    {
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $list = $this->students->getDatatables();
        $data = [];
        $response = [];
        $no = (int)($this->request->getPost('start') ?? 0);

        foreach ($list as $row) {
            $no++;
            $total_discount = 0;
            $payable = 0;
            $projectedfee = 0;
            $className = '';
            $sectionName = '';
            $address = '';

            // Unpaid calculations
            $unpaid = $this->db->query('SELECT SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE status = "UnPaid" and student_id = ?', [$row->student_id])->getRow();
            $discount = $this->db->query('SELECT SUM(discount) as total_discount FROM fee_chalan WHERE status = "UnPaid" and student_id = ?', [$row->student_id])->getRow();

            if ($discount) $total_discount = $discount->total_discount;
            if ($unpaid) $payable = $unpaid->total;

            $studentclassinfo = $this->db->table('student_class')
                ->where('student_id', $row->student_id)
                ->where('status', $this->request->getGet('status'))
                ->where('session_id', $sessionid)
                ->get()->getRow();

            if ($studentclassinfo) {
                $classsectioninfo = $this->db->table('class_section')
                    ->where('cls_sec_id', $studentclassinfo->cls_sec_id)
                    ->get()->getRow();

                if ($classsectioninfo) {
                    $classinfo = $this->db->table('classes')
                        ->where('class_id', $classsectioninfo->class_id)
                        ->get()->getRow();

                    $sectionInfo = $this->db->table('sections')
                        ->where('section_id', $classsectioninfo->section_id)
                        ->get()->getRow();
                    $sectionName = $sectionInfo->section_name ?? '';
                }
                $className = $classinfo->class_name ?? '';
            }

            $parentinfo = $this->db->table('parents')
                ->where('parent_id', $row->parent_id)
                ->get()->getRow();

            $f_name = '';
            $father_contact = '';
            $mother_contact = '';
            $emergency_contact = '';
            $whatsapp_contact = '';
            if ($parentinfo) {
                $address = $parentinfo->address_line1;
                $f_name = $parentinfo->f_name;
                $father_contact = $parentinfo->father_contact;
                $mother_contact = $parentinfo->mother_contact;
                $emergency_contact = $parentinfo->emergency_contact;
                $whatsapp_contact = $parentinfo->whatsapp;
            }

            $item = [];
            $item['id'] = $row->student_id;

            $imgurl = FCPATH . "uploads/" . $row->profile_photo;
            if ($row->profile_photo && file_exists($imgurl)) {
                $item['profile_photo'] = "<img style='width:50px;height:50px;text-align: center;display: block;border-radius: 30px;margin: 0 auto;' src='" . base_url("uploads/" . $row->profile_photo) . "' >";
            } else {
                $item['profile_photo'] = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
            }

            $age = $row->date_of_birth ? date_diff(date_create($row->date_of_birth), date_create('now'))->y : '';

            $item['reg_no'] = $row->reg_no;
            $item['name'] = $row->first_name . " " . $row->last_name;
            $item['f_name'] = $f_name;
            $item['age'] = $age . " Years";
            $item['gender'] = $row->gender;
            $item['address'] = $address;
            $item['class'] = $className . "(" . $sectionName . ")";
            $item['section'] = $sectionName;
            $item['f_contacts'] = $father_contact;
            $item['m_contacts'] = $mother_contact;
            $item['e_contacts'] = $emergency_contact;
            $item['w_contacts'] = $whatsapp_contact;
            $item['payable'] = $payable;
            $item['discounted_amount'] = $row->discounted_amount;
            $item['discounted'] = $total_discount;
            $item['projectedfee'] = $projectedfee;
            $response[] = $item;
        }

        $output = [
            "draw" => (int)($this->request->getPost('draw') ?? 1),
            "recordsTotal" => $this->students->countAll(),
            "recordsFiltered" => $this->students->countFiltered(),
            "data" => $response,
        ];

        return $this->response->setJSON($output);
    }

    public function add()
    {
        check_permission('admin-add-student');
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $campus_bill_info = $this->db->table('campus_bills')->where(['status' => 1, 'campus_id' => $campusid])->get()->getRow();
        $max_student_id = $campus_bill_info->max_students ?? 0;

        $max_no_of_students_info = $this->db->table('number_of_students')->where('id', $max_student_id)->get()->getRow();
        $max_student_limit = $max_no_of_students_info->no_of_students ?? 0;

        $students_info = $this->db->query('select count(student_id) as studentTotal from students WHERE student_id IN(SELECT student_id from student_class WHERE status=1)  AND campus_id=' . $campusid)->getRow();
        $noOfstudent = $students_info->studentTotal ?? 0;

        $max_limit = $noOfstudent >= $max_student_limit ? '<div class="col-lg-12">Maximum Limit Exceeded</div>' : '';
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        $classesinfo = $this->db->table('classes')->get()->getResult();

        $academic_session = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();
        $sessionName = $academic_session ? explode('-', $academic_session->session_name) : ['0000', '0000'];
        $sessionYear = isset($sessionName[1]) ? ((int)$sessionName[1] - 1) : date('Y');

        $last_row = $this->db->table('students')->where('session_id', $sessionid)->orderBy('student_id', 'desc')->get()->getResult();
        $last_id = count($last_row) + 1;

        $reg_no = $sessionYear . '-' . ($schoolinfo->short_name ?? 'SCH') . '-' . $last_id;

        $currentrole = currentUserRoles();
        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $data = [
            'max_limit' => $max_limit,
            'sessionData' => $sessionData,
            'classesinfo' => $classesinfo,
            'reg_no' => $reg_no,
            'sectionsclassinfo' => $sectionsclassinfo,
        ];

        return view('students_contact_list_edit', $data);
    }

    public function edit()
    {
        check_permission('admin-edit-student');
        $id = (int)$this->request->getGet('id');

        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        $info = $this->db->table('students')->where('student_id', $id)->get()->getRow();
        $parentsinfo = $this->db->table('parents')->where('parent_id', $info->parent_id)->get()->getRow();
        $sectionsclassinfo = userClassSections();
        $studentclassinfo = $this->db->table('student_class')->where('student_id', $id)->where('status', 1)->get()->getRow();
        $classesinfo = $this->db->table('classes')->get()->getResult();
        $academic_sessioninfo = $this->db->table('academic_session')->get()->getResult();

        $data = [
            'sessionData' => $sessionData,
            'info' => $info,
            'parentsinfo' => $parentsinfo,
            'sectionsclassinfo' => $sectionsclassinfo,
            'studentclassinfo' => $studentclassinfo,
            'classesinfo' => $classesinfo,
            'academic_sessioninfo' => $academic_sessioninfo,
        ];

        return view('students_contact_list_edit', $data);
    }

    public function save()
    {
        // Not implemented: Add logic if you want file upload and save to work in CI4
        return $this->response->setJSON(['success' => false, 'msg' => 'Not implemented yet']);
    }

    public function get_parentinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $parents = $this->db->table('parents')
            ->like('f_name', $term)
            ->where('campus_id', $campusid)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($parents as $parent) {
            $classstudents = $this->db->table('students')->where('parent_id', $parent['parent_id'])->where('campus_id', $campusid)->get()->getRow();
            if ($classstudents) {
                $data[] = ["id" => $parent['parent_id'], "text" => $parent['f_name']];
            }
        }
        return $this->response->setJSON($data);
    }

    public function get_studentinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $status = $this->request->getPost('status');
        $studentsinfo = $this->db->table('students')
            ->groupStart()
                ->like('first_name', $term)
                ->orLike('last_name', $term)
            ->groupEnd()
            ->where('status', $status)
            ->where('campus_id', $campusid)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($studentsinfo as $student) {
            $classstudents = $this->db->table('student_class')->where('student_id', $student['student_id'])->get()->getRow();
            $parentsInfo = $this->db->table('parents')->select('f_name')->where('parent_id', $student['parent_id'])->get()->getRow();

            $stdInfotxt = $student['first_name'] . " " . $student['last_name'] . " c/o " . ($parentsInfo->f_name ?? '');

            if ($classstudents) {
                $data[] = ["id" => $student['student_id'], "text" => $stdInfotxt];
            }
        }
        return $this->response->setJSON($data);
    }

    public function delete()
    {
        check_permission('admin-del-student');
        $id = (int)$this->request->getGet('id');

        $this->db->transBegin();
        $this->db->table('students')->where('student_id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Student Success']);
    }
}
