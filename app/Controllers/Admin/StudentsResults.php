<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class StudentsResults extends BaseController
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
        check_permission('admin-students-results');
        return view('admin/students_results', []);
    }

    public function data()
    {
        $draw = $this->request->getPost('draw');
        $keyword = $this->request->getPost('search')['value'] ?? '';

        $results = $this->db->table('subject_results')->get()->getResult();

        // Pre-fetch all referenced data in fewer queries
        $student_ids = array_unique(array_column($results, 'student_id'));
        $cls_sec_ids = array_unique(array_column($results, 'cls_sec_id'));
        $sec_sub_ids = array_unique(array_column($results, 'sec_sub_id'));
        $session_ids = array_unique(array_column($results, 'session_id'));

        $students = $this->getIndexedData('students', 'student_id', $student_ids);
        $classes = $this->getIndexedData('classes', 'class_id');
        $sections = $this->getIndexedData('class_section', 'cls_sec_id', $cls_sec_ids);
        $subjects = $this->getIndexedData('section_subjects', 'sec_sub_id', $sec_sub_ids, ['status' => 1]);
        $all_subjects = $this->getIndexedData('allsubject', 'sid');
        $sessions = $this->getIndexedData('academic_session', 'session_id', $session_ids);

        $data = [];

        foreach ($results as $row) {
            $student = $students[$row->student_id] ?? null;
            $section = $sections[$row->cls_sec_id] ?? null;
            $class = $section && isset($classes[$section->class_id]) ? $classes[$section->class_id] : null;
            $sec_subject = $subjects[$row->sec_sub_id] ?? null;
            $subject = $sec_subject && isset($all_subjects[$sec_subject->subject_id]) ? $all_subjects[$sec_subject->subject_id] : null;
            $session = $sessions[$row->session_id] ?? null;

            if (!$student || !$subject || !$class || !$session) continue;

            $data[] = [
                'student_id' => $row->student_id,
                'student' => $student->first_name . ' ' . $student->last_name,
                'class' => $class->class_name,
                'subject' => $subject->subject_name,
                'obtained_marks' => $row->obtained_marks,
                'session_id_info' => $session->session_name
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => count($results),
            'recordsFiltered' => count($results),
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    private function getIndexedData($table, $keyField, $whereIn = [], $additionalWhere = [])
    {
        $builder = $this->db->table($table);
        if (!empty($whereIn)) {
            $builder->whereIn($keyField, $whereIn);
        }
        if (!empty($additionalWhere)) {
            $builder->where($additionalWhere);
        }
        $query = $builder->get()->getResult();
        $indexed = [];
        foreach ($query as $item) {
            $indexed[$item->$keyField] = $item;
        }
        return $indexed;
    }

    public function add()
    {
        check_permission('admin-add-students-result');

        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid,
        ];
        $data['sessionData'] = $sessionData;

        $data['infostudents'] = $this->db->table('students')->where('status', 1)->get()->getResult();

        $currentrole = currentUserRoles();
        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }
        $data['sectionsclassinfo'] = $sectionsclassinfo;

        $data['campusinfo'] = $this->db->table('campus')->where('campus_id', $campusid)->get()->getResult();

        $data['examinfo'] = $this->db->table('exam')->where(['campus_id' => $campusid, 'session_id' => $sessionid])->get()->getResult();

        $data['academic_session'] = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getResult();

        $data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

        return view('admin/students_results_edit', $data);
    }

    public function edit()
    {
        check_permission('admin-edit-students-result');
        $id = intval($this->request->getGet('id'));

        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid,
        ];
        $data['sessionData'] = $sessionData;

        $data['info'] = $this->db->table('subject_results')->where('student_id', $id)->get()->getRow();

        $data['infostudents'] = $this->db->table('students')->get()->getResult();
        $data['classesinfo'] = $this->db->table('classes')->get()->getResult();
        $data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

        return view('admin/students_results_edit', $data);
    }

    public function save()
    {
        check_permission('admin-add-students-result');

        $eid = intval($this->request->getPost('eid'));
        $session_id = intval($this->request->getPost('session_id'));
        $cls_sec_id = intval($this->request->getPost('cls_sec_id'));

        if (empty($eid)) {
            return json_response(['error' => TRUE, 'msg' => 'Exam is not selected']);
        }

        $result_ids = $this->request->getPost('result_id');
        $sec_sub_ids = $this->request->getPost('sec_sub_id');
        $marks_data = $this->request->getPost('obtained_marks');

        $this->db->transStart();

        $this->save_subject_results($eid, $session_id, $cls_sec_id, $result_ids, $sec_sub_ids, $marks_data);
        $this->update_exam_rankings($eid, $cls_sec_id);

        $this->db->transComplete();

        return $this->response->setJSON(['success' => TRUE, 'msg' => 'Add Result Success']);
    }

    private function save_subject_results($eid, $session_id, $cls_sec_id, $result_ids, $sec_sub_ids, $marks_data)
    {
        foreach ($marks_data as $student_id => $subjects) {
            foreach ($subjects as $sec_sub_id => $obtained_marks) {
                $result_id = $result_ids[$student_id][$sec_sub_id] ?? 0;

                $data = [
                    'student_id' => $student_id,
                    'eid' => $eid,
                    'session_id' => $session_id,
                    'cls_sec_id' => $cls_sec_id,
                    'sec_sub_id' => $sec_sub_ids[$sec_sub_id],
                    'obtained_marks' => $obtained_marks
                ];

                if ($result_id > 0) {
                    $this->db->table('subject_results')->where('result_id', $result_id)->update($data);
                } else {
                    $this->db->table('subject_results')->insert($data);
                }
            }
        }
    }

    private function update_exam_rankings($eid, $cls_sec_id)
    {
        $exam = $this->db->table('exam')->where('eid', $eid)->get()->getRow();
        $term = $this->db->table('terms_session')->where([
            'term_id' => $exam->term_id,
            'session_id' => $exam->session_id
        ])->get()->getRow();

        $from = $term->start_date;
        $to = $term->end_date;

        $ranking_sql = "
            SELECT eid, student_id, `rank`, total_score FROM (
                SELECT *, IF(@marks=(@marks:=total_score), @auto, @auto:=@auto+1) AS `rank`
                FROM (
                    SELECT student_id, SUM(obtained_marks) AS total_score, eid
                    FROM subject_results, (SELECT @auto:=0, @marks:=0) AS init
                    WHERE sec_sub_id IN (
                        SELECT sec_sub_id FROM datesheet
                        WHERE sec_sub_id IN (
                            SELECT sec_sub_id FROM section_subjects
                            WHERE cls_sec_id={$cls_sec_id} AND status=1
                        )
                        AND total_marks > 0 AND eid={$eid}
                    )
                    AND eid={$eid}
                    GROUP BY student_id
                ) sub ORDER BY total_score DESC
            ) result
        ";

        $ranked = $this->db->query($ranking_sql)->getResult();

        if (!$ranked) return;

        $total_marks_row = $this->db->query("
            SELECT SUM(total_marks) AS totalmarks FROM datesheet
            WHERE eid = {$eid}
            AND sec_sub_id IN (
                SELECT sec_sub_id FROM section_subjects WHERE cls_sec_id = {$cls_sec_id} AND status=1
            )
        ")->getRow();

        $total_marks = $total_marks_row->totalmarks ?? 0;

        foreach ($ranked as $entry) {
            $stats = $this->calculate_attendance_stats($entry->student_id, $from, $to);

            $exam_data = [
                'eid' => $eid,
                'student_id' => $entry->student_id,
                'position' => $entry->rank,
                'exam_total_mark' => $total_marks,
                'obtain_total_mark' => $entry->total_score,
                'study_complaints' => $stats['study_complaints'],
                'disc_complaints' => $stats['disc_complaints'],
                'late_comming' => $stats['late_comming'],
                'absentees' => $stats['absentees'],
                'leave' => $stats['leave'],
                'early_left' => $stats['early_left'],
                'working_days' => $stats['working_days'],
                'remark' => 'Test'
            ];

            $exists = $this->db->table('exam_results')
                ->where(['eid' => $eid, 'student_id' => $entry->student_id])
                ->get()->getRow();

            if ($exists) {
                $this->db->table('exam_results')
                    ->where(['eid' => $eid, 'student_id' => $entry->student_id])
                    ->update($exam_data);
            } else {
                $this->db->table('exam_results')->insert($exam_data);
            }
        }
    }

    private function calculate_attendance_stats($student_id, $from, $to)
    {
        $stats = [];
        $stats['study_complaints'] = $this->count_rows('complaints', ['student_id' => $student_id, 'type' => 'Study'], $from, $to);
        $stats['disc_complaints'] = $this->count_rows('complaints', ['student_id' => $student_id, 'type' => 'Discipline'], $from, $to);
        $stats['late_comming'] = $this->count_rows('attendance', ['student_id' => $student_id], $from, $to, 'lc_duration > 0');
        $stats['absentees'] = $this->count_rows('attendance', ['student_id' => $student_id, 'status' => 'A'], $from, $to);
        $stats['leave'] = $this->count_rows('attendance', ['student_id' => $student_id, 'status' => 'L'], $from, $to);
        $stats['early_left'] = $this->count_rows('attendance', ['student_id' => $student_id], $from, $to, 'el_duration > 0');
        $stats['presents'] = $this->count_rows('attendance', ['student_id' => $student_id, 'status' => 'P'], $from, $to);

        $stats['working_days'] = $stats['presents'] + $stats['leave'] + $stats['absentees'];

        return $stats;
    }

    private function count_rows($table, $where, $from, $to, $extra_condition = '')
    {
        $builder = $this->db->table($table);
        $builder->where($where);
        $builder->where("date BETWEEN '$from' AND '$to'");
        if ($extra_condition) $builder->where($extra_condition);
        return $builder->countAllResults();
    }

    public function get_students()
    {
        $eid = intval($this->request->getPost('eid'));
        $session_id = intval($this->request->getPost('session_id'));
        $campus_id = intval($this->request->getPost('campus_id'));
        $cls_sec_id = intval($this->request->getPost('cls_sec_id'));

        if (empty($eid)) {
            echo "<div class='text-danger'>Exam is not selected</div><br>";
            return;
        }
        if ($cls_sec_id == 0) {
            echo "<div style='background:red;color:#fff;margin:10px 0px; padding:5px;'>Select Class Section</div>";
            return;
        }

        // Fetch data
        $students = $this->get_class_students($cls_sec_id, $session_id);
        $datesheet = $this->get_datesheet_subjects($eid, $cls_sec_id);
        $section_meta = $this->get_section_meta($session_id, $campus_id, $eid, $cls_sec_id);

        if (empty($datesheet)) {
            echo '<div class="alert alert-danger" role="alert">Create Datesheet To Enter Result</div>';
            return;
        }

        // Build HTML Output
        $html = $this->build_students_results_table($students, $datesheet, $section_meta, $eid, $session_id, $campus_id, $cls_sec_id);
        echo $html;
    }

    private function get_class_students($cls_sec_id, $session_id)
    {
        return $this->db->table('student_class')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('session_id', $session_id)
            ->where('status', 1)
            ->get()
            ->getResult();
    }

    private function get_datesheet_subjects($eid, $cls_sec_id)
    {
        $role = currentUserRoles();
        $user_id = $this->session->get('member_userid');
        if (in_array(5, $role)) {
            $sql = "
                SELECT * FROM datesheet
                WHERE eid = {$eid}
                AND sec_sub_id IN (
                    SELECT sec_sub_id FROM teacher_subjects
                    WHERE status = 1
                    AND cls_sec_id = {$cls_sec_id}
                    AND tid = {$user_id}
                    AND sec_sub_id IN (
                        SELECT sec_sub_id FROM section_subjects
                        WHERE cls_sec_id = {$cls_sec_id} AND status = 1
                    )
                )
            ";
            return $this->db->query($sql)->getResult();
        } else {
            $sql = "
                SELECT * FROM datesheet
                WHERE eid = {$eid}
                AND sec_sub_id IN (
                    SELECT sec_sub_id FROM section_subjects
                    WHERE cls_sec_id = {$cls_sec_id} AND status = 1
                )
            ";
            return $this->db->query($sql)->getResult();
        }
    }

    private function get_section_meta($session_id, $campus_id, $eid, $cls_sec_id)
    {
        $section = getClassSection($cls_sec_id);
        $session = $this->db->table('academic_session')->where('session_id', $session_id)->get()->getRow();
        $campus = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
        $exam = $this->db->table('exam')->where('eid', $eid)->get()->getRow();

        return [
            'session' => $session->session_name ?? '',
            'campus' => $campus->campus_name ?? '',
            'exam' => $exam->exam_name ?? '',
            'class_section' => $section['sectionclassname'] ?? ''
        ];
    }

    private function build_students_results_table($students, $datesheet, $meta, $eid, $session_id, $campus_id, $cls_sec_id)
    {
        $thead = '';
        $thead .= "<input type='hidden' name='eeid' value='{$eid}'>";
        $thead .= "<input type='hidden' name='session_id' value='{$session_id}'>";
        $thead .= "<input type='hidden' name='campus_id' value='{$campus_id}'>";
        $thead .= "<input type='hidden' name='eid' value='{$eid}'>";
        $thead .= "<input type='hidden' name='class_id' value='{$cls_sec_id}'>";

        $thead .= "<div class='table-box'><table class='table'>
            <tr><th>Session</th><th>{$meta['session']}</th>
            <th>Campus</th><th>{$meta['campus']}</th>
            <th>Exam</th><th>{$meta['exam']}</th>
            <th>Class</th><th>{$meta['class_section']}</th></tr></table>";

        $thead .= "<table class='table'><thead><tr class='header'>
            <th>#</th><th>Photo</th><th>Student</th>";

        $width = count($datesheet) > 0 ? round(85 / count($datesheet)) : 85;

        foreach ($datesheet as $subject) {
            $subject_data = $this->db->table('section_subjects')
                ->where('sec_sub_id', $subject->sec_sub_id)
                ->where('status', 1)
                ->get()->getRow();

            $sub = $this->db->table('allsubject')
                ->where('sid', $subject_data->subject_id ?? 0)
                ->get()->getRow();

            $short = $sub->subject_short_name ?? '';
            $total = $subject->total_marks;

            if ($short && $total > 0) {
                $thead .= "<th style='width:{$width}%;'><input type='hidden' name='sec_sub_id[{$subject->sec_sub_id}]' value='{$subject->sec_sub_id}'>
                    <span>{$short}<br>{$total}</span></th>";
            }
        }

        $thead .= "</tr></thead><tbody>";
        $i = 1;

        foreach ($students as $std) {
            $student = $this->db->table('students')->where('student_id', $std->student_id)->get()->getRow();
            if (!$student) continue;

            $name = $student->first_name . ' ' . $student->last_name;
            $photo = base_url("uploads/{$student->profile_photo}");
            $exists = is_file(FCPATH . "uploads/" . $student->profile_photo);
            $photo_html = $exists
                ? "<img src='{$photo}' class='img-circle' style='width:50px;height:50px;'/>"
                : "<i class='fa fa-user' style='font-size:40px;'></i>";

            $thead .= "<tr><td>{$i}</td><td>{$photo_html}</td><td><b>{$student->reg_no}</b><br>{$name}</td>";

            foreach ($datesheet as $subject) {
                $result = $this->db->table('subject_results')
                    ->where('student_id', $student->student_id)
                    ->where('eid', $eid)
                    ->where('sec_sub_id', $subject->sec_sub_id)
                    ->get()->getRow();

                $obtained = $result->obtained_marks ?? 0;
                $result_id = $result->result_id ?? 0;
                $max = $subject->total_marks;

                $thead .= "<td><input type='hidden' name='result_id[{$student->student_id}][{$subject->sec_sub_id}]' value='{$result_id}'>
                           <input type='number' name='obtained_marks[{$student->student_id}][{$subject->sec_sub_id}]' value='{$obtained}' min='0' max='{$max}' step='0.01'  class='form-control'></td>";
            }
            $thead .= "</tr>";
            $i++;
        }

        $thead .= "</tbody></table></div>";
        return $thead;
    }
}
