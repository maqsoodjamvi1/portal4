<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class WpResultsCard extends BaseController
{
    protected $db;

    public function __construct()
    {
        check_permission('admin-wp-result-cards');
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $campus_id = session('member_campusid');
        $sessionid = session('member_sessionid');
        $schoolinfo = getSchoolInfo();

        // $terms_session_info = $this->db->table('terms_session')
        //     ->where('session_id', $sessionid)
        //     ->get()->getResult();
        $terms_session_info = $this->db->table('terms_session ts')
            ->select('ts.*, t.name as term_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->get()->getResult();
        //$this->template_data['terms_session_info'] = $terms_session_info;


        $classes_info = $this->db->table('classes')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $data = [
            'terms_session_info' => $terms_session_info,
            'classes_info' => $classes_info,
            'terms_session_info' => $terms_session_info,
        ];

        return view('admin/wp_results_card', $data);
    }

    public function grade($marks)
    {
        $schoolinfo = getSchoolInfo();

        return $this->db->query(
            'SELECT * FROM grading_policy WHERE system_id= ? AND ? BETWEEN mark_from AND mark_to',
            [$schoolinfo->system_id, $marks]
        )->getRow();
    }

    public function data()
    {
        $class_id = $this->request->getPost('class_id');
        $termsession_ids = $this->request->getPost('termsession_id');
        $term_week_ids = $this->request->getPost('term_week_id');

        $schoolinfo = getSchoolInfo();
        $campus_id = session('member_campusid');
        $sessionid = session('member_sessionid');

        if (!$class_id) {
            return $this->response->setBody("Select class to see progress");
        }

        if (empty($termsession_ids)) {
            return $this->response->setBody('<div class="col-lg-12" style="background: red;color: #fff;width: 95%;margin: 14px 37px !important;">Select term to check result</div>');
        }

        $student_class = $this->db->query("
            SELECT * FROM student_class 
            WHERE student_id IN (
                SELECT student_id FROM students WHERE status=1 AND campus_id=$campus_id
            ) AND session_id = $sessionid 
            AND cls_sec_id IN (
                SELECT cls_sec_id FROM class_section WHERE class_id=$class_id AND status=1 AND campus_id=$campus_id
            ) 
            ORDER BY cls_sec_id ASC
        ")->getResult();

        $strResultCard = '';

        foreach ($student_class as $studentinfo) {
            if (!empty($term_week_ids)) {
                $term_weekIDs = implode(',', $term_week_ids);
                $objGrades = $this->db->query("
                    SELECT sub_obj_id, obj_grades, SUM(obj_grades) AS total_grades 
                    FROM wp_std_weeekly_progress 
                    WHERE class_id=$class_id AND session_id=$sessionid AND term_week_id IN($term_weekIDs) 
                    GROUP BY obj_grades, sub_obj_id
                ")->getResult();
            } else {
                $termsession_id = implode(',', $termsession_ids);
                $objGrades = $this->db->query("
                    SELECT sub_obj_id, obj_grades, SUM(obj_grades) AS total_grades 
                    FROM wp_std_weeekly_progress 
                    WHERE class_id=$class_id AND session_id=$sessionid 
                    AND term_week_id IN (
                        SELECT term_weeks_id FROM term_weeks WHERE term_session_id IN($termsession_id)
                    ) 
                    GROUP BY obj_grades, sub_obj_id
                ")->getResult();
            }

            $obj_grades = '';
            $grades_total = '';

            foreach ($objGrades as $value) {
                $subObjective = $this->db->query("
                    SELECT * FROM wp_sub_objectives 
                    WHERE class_id=$class_id AND sub_obj_id=$value->sub_obj_id 
                    GROUP BY sub_obj_id
                ")->getRow();

                if ($subObjective) {
                    $objective = $this->db->table('wp_objectives')->where('obj_id', $subObjective->obj_id)->get()->getRow();
                    $subject = $this->db->table('allsubject')->where('sid', $subObjective->subject_id)->get()->getRow();

                    if ($subject && $objective) {
                        $obj_grades .= '"' . $subject->subject_name . ' ' . $objective->objective . '",';
                        $grades_total .= $value->total_grades . ',';
                    }
                }
            }

            $student_info = $this->db->table('students')->where('student_id', $studentinfo->student_id)->get()->getRow();
            $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();

            if ($student_info) {
                $parent_info = $this->db->table('parents')->where('parent_id', $student_info->parent_id)->get()->getRow();
                $f_name = $parent_info->f_name ?? '';
                $father_contact = $parent_info->father_contact ?? '';
                $mother_contact = $parent_info->mother_contact ?? '';
                $emergency_contact = $parent_info->emergency_contact ?? '';
                $class_info = getClassSection($studentinfo->cls_sec_id);

                $strResultCard .= '<page><div style="border:1px dashed #000; border-radius:10px; text-align:center;" class="col-lg-12">';
                $strResultCard .= '<div class="col-lg-3" style="float: left;width: 100px;">';

                if (!empty($student_info->profile_photo)) {
                    $strResultCard .= '<img style="width: 65px;margin-top: 8px;border-radius: 8px;" src="uploads/' . $student_info->profile_photo . '">';
                } else {
                    $strResultCard .= '<i style="font-size: 90px;text-align: center;display: block;margin-top: 0px;" class="fa fa-user"></i>';
                }

                $strResultCard .= '</div><div class="col-lg-9" style="margin: 0 auto;">';
                $strResultCard .= '<h1 style="margin-top:5px; font-size:40px;">' . esc($schoolinfo->system_name) . '</h1>';
                $strResultCard .= '<h3 style="margin-top:5px;font-size: 16px;">' . esc($campus_info->campus_name) . '</h3></div></div>';
                $strResultCard .= '<div style="border:1px solid #000; float:left; width:100%; margin:10px auto;">';
                $strResultCard .= '<div style="width:33%; padding-left:15px; float:left;"><strong>Reg No:</strong> ' . esc($student_info->reg_no) . ' (' . esc($class_info['sectionclassname']) . ')</div>';
                $strResultCard .= '<div style="width:33%; padding-left:15px; float:left;"><strong>Name:</strong> ' . esc($student_info->first_name . ' ' . $student_info->last_name) . '</div>';
                $strResultCard .= '<div style="width:33%; padding-left:15px; float:left;"><strong>Father Name:</strong> ' . esc($f_name) . '</div>';
                $strResultCard .= '<div style="width:33%; padding-left:15px; float:left;"><strong>Father Contact #:</strong> ' . esc($father_contact) . '</div>';
                $strResultCard .= '<div style="width:33%; padding-left:15px; float:left;"><strong>Mother Contact #:</strong> ' . esc($mother_contact) . '</div>';
                $strResultCard .= '<div style="width:33%; padding-left:15px; float:left;"><strong>Emergency Contact #:</strong> ' . esc($emergency_contact) . '</div>';
                $strResultCard .= '</div>';

                $strResultCard .= '<div style="border:2px solid #000; float:left; width:100%; margin-bottom:0px auto; padding:2px;">';
                $strResultCard .= '<div class="heading">ACADEMIC PROGRESS</div>';
                $strResultCard .= '<canvas id="myChart' . $student_info->student_id . '" style="width:100%;max-width:600px"></canvas>';
                $strResultCard .= '<script>
                var xValues = [' . rtrim($obj_grades, ",") . '];
                var yValues = [' . rtrim($grades_total, ",") . '];
                var barColors = ["red", "green","blue","orange","brown"];
                new Chart("myChart' . $student_info->student_id . '", {
                  type: "bar",
                  data: {
                    labels: xValues,
                    datasets: [{
                      backgroundColor: barColors,
                      data: yValues
                    }]
                  },
                  options: {
                    legend: {display: false},
                    title: {
                      display: true,
                      text: "Session Term Progress Report"
                    }
                  }
                });
                </script>';
                $strResultCard .= '</div></page><br><br><br><br><div style="clear: both;margin-bottom: 60px;"></div><p style="page-break-before: always;">&nbsp;</p>';
            }
        }

        return $this->response->setBody($strResultCard);
    }

    public function addOrdinalNumberSuffix($num)
    {
        if (!in_array(($num % 100), [11, 12, 13])) {
            switch ($num % 10) {
                case 1: return $num . 'st';
                case 2: return $num . 'nd';
                case 3: return $num . 'rd';
            }
        }
        return $num . 'th';
    }
}
