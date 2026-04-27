<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class WpStdWeeeklyProgress extends BaseController
{
    protected $db;
    protected $template_data = [];

    public function __construct()
    {
        check_permission('admin-students-weekly-progress');
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);
    }

    public function index()
    {
        return view('admin/wp_std_weeekly_progress', $this->template_data);
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');

        $response->recordsTotal = $this->db->table('subject_results')->countAll();
        $results = $this->db->table('subject_results')->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $data = [];
            $data['student_id'] = $row->student_id;

            $student = $this->db->table('students')->where('student_id', $row->student_id)->get()->getRow();
            $cls_sec = $this->db->table('class_section')->where(['cls_sec_id' => $row->cls_sec_id, 'status' => 1])->get()->getRow();
            $class = $this->db->table('classes')->where('class_id', $cls_sec->class_id ?? 0)->get()->getRow();
            $section_subject = $this->db->table('section_subjects')->where(['sec_sub_id' => $row->sec_sub_id, 'status' => 1])->get()->getRow();
            $subject = $this->db->table('allsubject')->where('sid', $section_subject->subject_id ?? 0)->get()->getRow();
            $session = $this->db->table('academic_session')->where('session_id', $row->session_id)->get()->getRow();

            if ($student) {
                $data['student'] = $student->first_name . ' ' . $student->last_name;
                $data['class'] = $class->class_name ?? '';
                $data['subject'] = $subject->subject_name ?? '';
                $data['obtained_marks'] = $row->obtained_marks;
                $data['session_id_info'] = $session->session_name ?? '';
                $response->data[] = $data;
            }
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-students-weekly-progress');
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $this->template_data['sessionData'] = ['campusid' => $campusid, 'sessionid' => $sessionid];
        $this->template_data['infostudents'] = $this->db->table('students')->get()->getResult();
        $this->template_data['classesinfo'] = $this->db->table('classes')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        
        //$this->template_data['terms_session_info'] = $this->db->table('terms_session')->where('session_id', $sessionid)->get()->getResult();
        $terms_session_info = $this->db->table('terms_session ts')
            ->select('ts.*, t.name as term_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.session_id', $sessionid)
            ->get()->getResult();

        $this->template_data['terms_session_info'] = $terms_session_info;


        $this->template_data['academic_session'] = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getResult();
        $this->template_data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

        return view('admin/wp_std_weeekly_progress', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-students-result');

        $id = $this->request->getGet('id');
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');

        $this->template_data['sessionData'] = ['campusid' => $campusid, 'sessionid' => $sessionid];
        $this->template_data['info'] = $this->db->table('studentsresults')->where('student_id', $id)->get()->getRow();
        $this->template_data['infostudents'] = $this->db->table('students')->get()->getResult();
        $this->template_data['classesinfo'] = $this->db->table('classes')->get()->getResult();
        $this->template_data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

        return view('admin/wp_std_weeekly_progress', $this->template_data);
    }

    public function save()
    {
        check_permission('admin-add-students-weekly-progress');
        $this->db->transStart();

        $std_wp_ids = $this->request->getPost('std_wp_id');
        $subject_id = $this->request->getPost('sub_id');
        $class_id = $this->request->getPost('class_id');
        $obj_ids = $this->request->getPost('obj_id');
        $remarksID = $this->request->getPost('remarks');

        foreach ($this->request->getPost('obj_grade') as $studentID => $grades) {
            foreach ($grades as $key => $grade_id) {
                $remarks = $remarksID[$studentID][$key];
                $std_wp_id = $std_wp_ids[$studentID][$key];
                $obj_id = $obj_ids[$key];

                $wp_sub = $this->db->table('wp_sub_objectives')
                    ->where([
                        'obj_id' => $obj_id,
                        'subject_id' => $subject_id,
                        'class_id' => $class_id
                    ])
                    ->get()->getRow();

                if ($wp_sub) {
                    $data = [
                        'student_id' => $studentID,
                        'term_week_id' => $this->request->getPost('term_weeks'),
                        'session_id' => $this->request->getPost('session_id'),
                        'class_id' => $class_id,
                        'subject_id' => $subject_id,
                        'sub_obj_id' => $wp_sub->sub_obj_id,
                        'obj_grades' => $grade_id,
                        'remarks' => $remarks
                    ];

                    if ($std_wp_id > 0) {
                        $this->db->table('wp_std_weeekly_progress')->where('std_wp_id', $std_wp_id)->update($data);
                    } else {
                        $this->db->table('wp_std_weeekly_progress')->insert($data);
                    }
                }
            }
        }

        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Result Success']);
    }

    public function selectSectionSubjectbySection()
    {
        $campusid = session('member_campusid');
        $class_id = $this->request->getPost('class_id');

        $section_subjects = $this->db->query(
            "SELECT DISTINCT(subject_id) FROM section_subjects WHERE cls_sec_id IN (
                SELECT cls_sec_id FROM class_section WHERE class_id=? AND status=1 AND campus_id=?
            ) AND status=1", [$class_id, $campusid]
        )->getResult();

        $output = '<option value="">Select Subject</option>';
        foreach ($section_subjects as $s) {
            $subject = $this->db->table('allsubject')->where('sid', $s->subject_id)->get()->getRow();
            if ($subject) {
                $output .= "<option value='{$subject->sid}'>{$subject->subject_name}</option>";
            }
        }

        return $this->response->setBody($output);
    }

    public function get_students()
    {
        $term_weeks = intval($this->request->getPost('term_weeks'));
        $session_id = intval($this->request->getPost('session_id'));
        $campus_id = intval($this->request->getPost('campus_id'));
        $class_id = intval($this->request->getPost('class_id'));
        $subject_id = intval($this->request->getPost('sub_id'));

        if (empty($term_weeks)) {
            return $this->response->setBody("<div class='text-danger'>Term Week is not selected</div><br>");
        }

        if ($class_id == 0) {
            return $this->response->setBody("<div style='background:red;color:#fff;margin:10px 0px; padding:5px;'>Select Class </div>");
        }

        $studentsList = '';
        $studentsList .= '<input type="hidden" name="session_id" value="' . $session_id . '">';
        $studentsList .= '<input type="hidden" name="campus_id" value="' . $campus_id . '">';
        $studentsList .= '<input type="hidden" name="term_weeks" value="' . $term_weeks . '">';
        $studentsList .= '<input type="hidden" name="class_id" value="' . $class_id . '">';

        $classstudents = $this->db->query(
            "SELECT * FROM student_class WHERE status=1 AND cls_sec_id IN(
                SELECT cls_sec_id FROM class_section WHERE class_id=? AND campus_id=?
            )", [$class_id, $campus_id]
        )->getResult();

        $term_week_info = $this->db->table('term_weeks')->where('term_weeks_id', $term_weeks)->get()->getRow();
        $weekly_planning_info = $this->db->table('weekly_planning')
            ->where([
                'subject_id' => $subject_id,
                'class_id' => $class_id,
                'campus_id' => $campus_id,
                'term_week_id' => $term_week_info->term_weeks_id,
            ])->get()->getRow();

        if ($weekly_planning_info) {
            $studentsList .= '<p>' . esc($weekly_planning_info->objectives) . '</p>';
        }

        $studentsList .= '<table class="table" style="width:100%;"><tr><td>#</td><th style="width:5%;">Photo</th><th style="width:12%">Student</th>';

        $wp_sub_objectives_info = $this->db->table('wp_sub_objectives')
            ->where(['subject_id' => $subject_id, 'class_id' => $class_id, 'status' => 1])
            ->get()->getResult();

        $width = count($wp_sub_objectives_info) > 0 ? (85 / count($wp_sub_objectives_info)) : 20;

        foreach ($wp_sub_objectives_info as $value) {
            $obj = $this->db->table('wp_objectives')->where('obj_id', $value->obj_id)->get()->getRow();
            if ($obj) {
                $studentsList .= '<th style="width:' . $width . '%;">
                    <input type="hidden" name="cls_sec_id" value="' . $class_id . '">
                    <input type="hidden" name="obj_id[' . $obj->obj_id . ']" value="' . $obj->obj_id . '">' . esc($obj->objective) . '</th>';
            }
        }

        $studentsList .= '</tr>';
        $i = 1;

        foreach ($classstudents as $row) {
            $student = $this->db->table('students')->where('student_id', $row->student_id)->get()->getRow();

            if (!$student) continue;

            $name = $student->first_name . ' ' . $student->last_name;
            $photo = $student->profile_photo;
            $photo_path = FCPATH . "uploads/" . $photo;

            if (!empty($photo) && file_exists($photo_path)) {
                $profile_photo = "<img style='width:50px;height:50px;text-align:center;display: block;border-radius: 30px;margin: 0 auto;' src='" . base_url("uploads/" . $photo) . "' >";
            } else {
                $profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
            }

            $studentsList .= '<tr><td>' . $i++ . '</td><td>' . $profile_photo . '</td><td><b>' . esc($student->reg_no) . '</b><br>' . esc($name) .
                '<input type="hidden" name="student_id[]" value="' . $student->student_id . '" class="form-control"></td>';

            foreach ($wp_sub_objectives_info as $obj) {
                $result = $this->db->table('wp_std_weeekly_progress')
                    ->where([
                        'student_id' => $student->student_id,
                        'subject_id' => $subject_id,
                        'class_id' => $class_id,
                        'sub_obj_id' => $obj->obj_id,
                        'term_week_id' => $term_weeks
                    ])->get()->getRow();

                $std_wp_id = $result->std_wp_id ?? 0;
                $obj_grades = $result->obj_grades ?? '';
                $remarks = $result->remarks ?? '';

                $studentsList .= '<td style="width:' . $width . '%;">
                    <input type="hidden" name="std_wp_id[' . $student->student_id . '][' . $obj->obj_id . ']" value="' . $std_wp_id . '">';

                $grades = $this->db->table('wp_objective_grade')->get()->getResult();

                $studentsList .= '<select class="form-control" name="obj_grade[' . $student->student_id . '][' . $obj->obj_id . ']">';
                foreach ($grades as $grade) {
                    $selected = $obj_grades == $grade->id ? 'selected' : '';
                    $studentsList .= '<option value="' . $grade->id . '" ' . $selected . '>' . esc($grade->obj_grade) . '</option>';
                }
                $studentsList .= '</select><br>';
                $studentsList .= '<input class="form-control" name="remarks[' . $student->student_id . '][' . $obj->obj_id . ']" placeholder="Remarks" value="' . esc($remarks) . '" type="text">';
                $studentsList .= '</td>';
            }

            $studentsList .= '</tr>';
        }

        $studentsList .= '</table>';
        return $this->response->setBody($studentsList);
    }
}
