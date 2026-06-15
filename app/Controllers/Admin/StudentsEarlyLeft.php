<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class StudentsEarlyLeft extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper('school');
        check_permission('admin-add-student-earlyleft');
    }

    public function index()
    {
        return view('admin/students_earlyleft', []);
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $sessionid = $this->session->get('member_sessionid');

        $response->recordsTotal = $this->db->table('attendance')->countAll();
        $results = $this->db->table('attendance')->get()->getResult();
        $response->recordsFiltered = $response->recordsTotal;

        $academic_session = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();

        $response->data = [];
        foreach ($results as $row) {
            $student = $this->db->table('students')->where('student_id', $row->student_id)->get()->getRow();
            $studentClass = $this->db->table('student_class')->where('student_id', $row->student_id)->get()->getRow();
            $class = $this->db->table('classes')->where('class_id', $studentClass->class_id)->get()->getRow();

            $termsSession = $this->db->query("SELECT * FROM terms_session WHERE session_id = ? AND ? BETWEEN start_date AND end_date", [$sessionid, $row->date])->getResult();
            $termName = $termsSession ? $this->db->table('terms')->where('term_id', $termsSession[0]->term_id)->get()->getRow('name') : '';

            $response->data[] = [
                'id' => $row->cid ?? null,
                'student' => $student->first_name . ' ' . $student->last_name,
                'class' => $class->class_name,
                'session_name' => $academic_session->session_name,
                'term_name' => $termName,
                'date' => $row->date,
                'detail' => $row->detail,
            ];
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-student-earlyleft');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $data = [
            'sessionData' => ['campusid' => $campusid, 'sessionid' => $sessionid],
            'infostudents' => $this->db->table('students')->get()->getResult(),
            'classesinfo' => $this->db->table('classes')->get()->getResult(),
            'sectionsclassinfo' => $this->loadSectionClasses($campusid),
            'campusinfo' => $this->db->table('campus')->where('campus_id', $campusid)->get()->getResult(),
            'examinfo' => $this->db->table('exam')->where(['campus_id' => $campusid, 'session_id' => $sessionid])->get()->getResult(),
            'academic_session' => $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getResult(),
            'subjectinfo' => $this->db->table('allsubject')->get()->getResult(),
        ];

        return view('admin/students_earlyleft_edit', $data);
    }

    public function save()
    {
        check_permission('admin-add-student-earlyleft');

        $student_ids = $this->request->getPost('student_id');
        $campusid = $this->session->get('member_campusid');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');
        $datevalue = $this->request->getPost('date');
        $day = date('l', strtotime($datevalue));

        $this->db->transBegin();

        foreach ($student_ids as $student_id) {
            $classSecinfo = $this->db->table('student_class')
                ->where(['student_id' => $student_id, 'status' => 1])
                ->get()->getRow();

            $schoolTimingsRow = getSchoolTimingForSectionDay(
                (int) $classSecinfo->cls_sec_id,
                $day,
                (int) $campusid
            );
            $schooltimings = $schoolTimingsRow !== null ? (object) $schoolTimingsRow : null;

            $checkoutTime = $this->request->getPost($student_id . '_checkout_date');

            if (!empty($schooltimings) && $checkoutTime) {
                $timeDef = (strtotime($schooltimings->checkout_timing) - strtotime($checkoutTime)) / 60;
                $this->db->table('attendance')
                    ->where(['student_id' => $student_id, 'date' => $datevalue])
                    ->update([
                        'el_duration' => $timeDef,
                        'checkout' => $checkoutTime,
                        'updated_date' => $date,
                        'user_id' => $user_id,
                    ]);
            }
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Early Left Success']);
    }

    public function get_studentinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term')['term'];

        $students = $this->db->table('students')
            ->like('first_name', $term)
            ->orLike('last_name', $term)
            ->where(['status' => 1, 'campus_id' => $campusid])
            ->get()->getResultArray();

        $data = [];
        foreach ($students as $student) {
            $classStudent = $this->db->table('student_class')
                ->where(['student_id' => $student['student_id'], 'status' => 1])
                ->get()->getRow();

            if ($classStudent) {
                $data[] = ["id" => $student['student_id'], "text" => $student['first_name'] . " " . $student['last_name']];
            }
        }

        return $this->response->setJSON($data);
    }

    public function get_students_byclass()
    {
        $student_id = $this->request->getPost('student_id');
        $date = $this->request->getPost('date');

        $timestamp = strtotime($date);
        $day = date('l', $timestamp);

        $students = $this->db->table('students')
            ->where('status', 1)
            ->where('parent_id', function ($builder) use ($student_id) {
                return $builder->select('parent_id')->from('students')->where('student_id', $student_id);
            })
            ->get()->getResult();

        $html = '<table class="table"><thead><tr><th>Photo</th><th>Name</th><th>Early Leave</th></tr></thead><tbody>';

        foreach ($students as $row) {
            $attendance = $this->db->table('attendance')
                ->where(['student_id' => $row->student_id, 'date' => $date])
                ->get()->getRow();

            if ($attendance) {
                $photoPath = base_url('uploads/' . $row->profile_photo);
                $photoTag = file_exists(FCPATH . 'uploads/' . $row->profile_photo)
                    ? "<img src='{$photoPath}' style='width:50px;height:50px;border-radius:30px;'>"
                    : "<i class='fa fa-user' style='font-size:40px;'></i>";

                $html .= "<tr><td>{$photoTag}</td><td>{$row->first_name} {$row->last_name}<br>{$row->reg_no}</td><td>";

                if ($attendance->status !== 'P') {
                    $html .= esc($attendance->status);
                } elseif ($attendance->el_duration > 0) {
                    $html .= esc($attendance->el_duration . ' Min Early Leave');
                } else {
                    $html .= "<input type='checkbox' class='toggle_option' name='{$row->student_id}_status' id='{$row->student_id}_earlyleft_toggle'>";
                    $html .= "<input type='text' class='form-control' disabled name='{$row->student_id}_checkout_date' id='{$row->student_id}_checkout_date'>";
                }

                $html .= '</td></tr>';
            }
        }

        $html .= '</tbody></table>';
        return $this->response->setBody($html);
    }

    private function loadSectionClasses($campus_id)
    {
        $result = [];
        $sections = $this->db->table('class_section')->where('campus_id', $campus_id)->get()->getResult();

        foreach ($sections as $section) {
            $class = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sec = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();

            $result[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $class->class_name . " (" . $sec->section_name . ")"
            ];
        }

        return $result;
    }
}
