<?php
/**
 * Result Card Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Students_results_card extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-result-cards');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index(){
		$campus_id = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$where = "session_id=".$sessionid." AND campus_id=".$campus_id;
		$this->db->where($where);	
		$exams = $this->db->get('exam')->result();

		$currentrole = currentUserRoles();

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

		$this->template_data['exams'] = $exams;
		$this->load->view('students_results_card', $this->template_data);
	}

	public function grade($marks){
		
		$schoolinfo = getSchoolInfo();

		$gradingPolicyInfo = $this->db->query('SELECT * FROM grading_policy WHERE system_id= '.$schoolinfo->system_id.' AND '.$marks.' BETWEEN mark_from AND mark_to ')->row();	
		return $gradingPolicyInfo;
	}

public function data()
{
    $cls_sec_id = (int) $this->input->post('cls_sec_id');
    $examids = $this->input->post('examids') ?? [];

    $campus_id = (int) $this->session->userdata('member_campusid');
    $sessionid = (int) $this->session->userdata('member_sessionid');
    $schoolinfo = getSchoolInfo();

    if (!$cls_sec_id || empty($examids)) {
        echo "Invalid class or exam selection.";
        return;
    }

    $student_ids = array_column(
        $this->db->select('student_id')->from('students')->where('campus_id', $campus_id)->get()->result_array(),
        'student_id'
    );

    $student_class = $this->db
        ->where('session_id', $sessionid)
        ->where('cls_sec_id', $cls_sec_id)
        ->where_in('student_id', $student_ids)
        ->get('student_class')
        ->result();

    $exam_info = $this->db->where_in('eid', $examids)->get('exam')->result_array();

    $output = '';
    $class_exam_totals = [];
    foreach ($student_class as $std) {
        foreach ($exam_info as $exam) {
            $total = $this->db->query("SELECT SUM(r.obtained_marks) AS obt, SUM(d.total_marks) AS total
                FROM subject_results r
                JOIN datesheet d ON r.eid = d.eid AND r.sec_sub_id = d.sec_sub_id
                WHERE r.student_id = ? AND r.eid = ? AND d.cls_sec_id = ?",
                [$std->student_id, $exam['eid'], $cls_sec_id]
            )->row();

            if ($total && $total->total > 0) {
                $class_exam_totals[$exam['eid']][$std->student_id] = $total->obt;
            }
        }
    }

    $exam_rankings = [];
    foreach ($class_exam_totals as $eid => $student_scores) {
        arsort($student_scores);
        $rank = 1;
        $last_score = null;
        $tie_rank = 1;
        foreach ($student_scores as $sid => $score) {
            if ($score !== $last_score) {
                $rank = $tie_rank;
            }
            $exam_rankings[$eid][$sid] = $rank;
            $last_score = $score;
            $tie_rank++;
        }
    }

    foreach ($student_class as $studentinfo) {
        $student_info = $this->db->get_where('students', ['student_id' => $studentinfo->student_id])->row();
        if (!$student_info) continue;

        $valid_examids = [];
        foreach ($exam_info as $exam) {
            $has_result = $this->db
                ->where('student_id', $studentinfo->student_id)
                ->where('eid', $exam['eid'])
                ->limit(1)
                ->get('subject_results')
                ->num_rows();

            if ($has_result > 0) {
                $valid_examids[] = $exam['eid'];
            }
        }

        if (empty($valid_examids)) continue;

        $exams = array_filter($exam_info, function ($e) use ($valid_examids) {
            return in_array($e['eid'], $valid_examids);
        });

        $parent_info = $this->db->get_where('parents', ['parent_id' => $student_info->parent_id])->row();
        $f_name = $parent_info->f_name ?? '';
        $class_info = getClassSection($studentinfo->cls_sec_id);

        $examids_csv = implode(',', array_map('intval', $valid_examids));
        $subjects = $this->db->query("
            SELECT DISTINCT ds.sec_sub_id, ss.subject_id, s.subject_name
            FROM datesheet ds
            JOIN section_subjects ss ON ss.sec_sub_id = ds.sec_sub_id AND ss.status = 1
            JOIN allsubject s ON s.sid = ss.subject_id
            WHERE ds.cls_sec_id = ? AND ds.total_marks > 0 AND ds.eid IN ($examids_csv)
        ", [$cls_sec_id])->result();

        $str = '<div class="student-result-card">';
        $str .= '<div class="printable-header" style="overflow: hidden; position: relative; height: 100px; margin-bottom: 10px;">';

       if (!empty($schoolinfo->logo)) {
    $str .= '<img src="'.base_url().'system-logo/'.$schoolinfo->logo.'" style="position: absolute; right: 0; top: 0; width: 85px; height: 85px; object-fit: contain;">';
}
if (!empty($student_info->profile_photo)) {
    $str .= '<img src="'.base_url().'uploads/'.$student_info->profile_photo.'" style="position: absolute; left: 0; top: 0; width: 65px; height: 65px; object-fit: cover; border:1px solid #000; border-radius: 8px;">';
} else {
    $str .= '<div style="position: absolute; left: 0; top: 0; width: 65px; height: 65px; border-radius: 8px; border: 1px solid #000; text-align: center; line-height: 65px; font-size: 24px;"><i class="fa fa-user"></i></div>';
}

        $str .= '<h1 style="margin: 0; text-align: center; font-size: 36px;">'.$schoolinfo->system_name.'</h1>';
		$campus_info = $this->db->get_where('campus', ['system_id' => $schoolinfo->system_id, 'campus_id' => $campus_id])->row();
		$campus_name = $campus_info->campus_name ?? 'Main Campus';

		$exam_names = array_unique(array_column($exams, 'exam_name'));
		$str .= '<div style="text-align: center; margin: 5px 0;">';
		$str .= '<h2 style="display: inline; font-size: 24px; margin: 0 10px;">'.implode(', ', $exam_names).'</h2>';
		$str .= '<h3 style="display: inline; font-size: 18px; margin: 0 10px;">'.$campus_name.'</h3>';
		$str .= '</div>';
        
        
        $str .= '</div>';

        $str .= '<div style="border:1px solid #000; float:left; width:100%; margin:10px auto;padding:10px;clear:both;">';
        $str .= '<div style="width:33%; float:left;"><strong>Reg No:</strong> '.$student_info->reg_no.' ('.$class_info['sectionclassname'].')</div>';
        $str .= '<div style="width:33%; float:left;"><strong>Name:</strong> '.$student_info->first_name.' '.$student_info->last_name.'</div>';
        $str .= '<div style="width:33%; float:left;"><strong>Father Name:</strong> '.$f_name.'</div>';

        $str .= '<div style="width:33%; float:left;"><strong>Father Contact:</strong> '.$parent_info->father_contact.'</div>';
$str .= '<div style="width:33%; float:left;"><strong>Mother Contact:</strong> '.$parent_info->mother_contact.'</div>';
$str .= '<div style="width:33%; float:left;"><strong>Emergency Contact:</strong> '.$parent_info->emergency_contact.'</div>';

        $str .= '<table class="table table-bordered"><thead><tr><th>Subject</th>';
        foreach ($exams as $term) {
            $str .= '<th>'.$term['exam_name'].'<br><div style="border-top:1px solid #000;">
                        <div style="width:20%;float:left;">Obt.</div>
                        <div style="width:20%;float:left;border-left:1px solid #000;">Total</div>
                        <div style="width:20%;float:left;border-left:1px solid #000;">Per</div>
                        <div style="width:40%;float:left;border-left:1px solid #000;">Grade</div>
                     </div></th>';
        }
        $str .= '</tr></thead><tbody>';

        $exam_totals = [];
        $has_any_grade = false;
      
        foreach ($subjects as $subject) {
            $str .= '<tr><td>'.$subject->subject_name.'</td>';
            foreach ($exams as $exam) {
                $res = $this->db->get_where('subject_results', [
                    'student_id' => $studentinfo->student_id,
                    'eid' => $exam['eid'],
                    'sec_sub_id' => $subject->sec_sub_id
                ])->row();

                $ds = $this->db->get_where('datesheet', [
                    'eid' => $exam['eid'],
                    'sec_sub_id' => $subject->sec_sub_id
                ])->row();

                if ($res && $ds && $ds->total_marks > 0) {
                    $perc = round(($res->obtained_marks / $ds->total_marks) * 100);
                    $grade_obj = $this->grade($perc);
                    $grade = $grade_obj->name ?? '-';
                    $has_any_grade = true;

                    $str .= '<td><div style="width:20%;float:left;">'.$res->obtained_marks.'</div>
                            <div style="width:20%;float:left;border-left:1px solid #000;">'.$ds->total_marks.'</div>
                            <div style="width:20%;float:left;border-left:1px solid #000;">'.$perc.'%</div>
                            <div style="width:40%;float:left;border-left:1px solid #000;">'.$grade.'</div></td>';

                    if (!isset($exam_totals[$exam['eid']])) {
                        $exam_totals[$exam['eid']] = ['obt' => 0, 'total' => 0];
                    }
                    $exam_totals[$exam['eid']]['obt'] += $res->obtained_marks;
                    $exam_totals[$exam['eid']]['total'] += $ds->total_marks;
                } else {
                    $str .= '<td><div style="text-align:center;">-</div></td>';
                }
            }
            $str .= '</tr>';
        }

        if ($has_any_grade) {
            $str .= '<tr><th>Total</th>';
            foreach ($exams as $exam) {
                $tot = $exam_totals[$exam['eid']] ?? null;
                if ($tot && $tot['total'] > 0) {
                    $perc = round(($tot['obt'] / $tot['total']) * 100);
                    $grade_obj = $this->grade($perc);
                    $grade = $grade_obj->name ?? '-';

                    $str .= '<td><div style="width:20%;float:left;">'.$tot['obt'].'</div>
                            <div style="width:20%;float:left;border-left:1px solid #000;">'.$tot['total'].'</div>
                            <div style="width:20%;float:left;border-left:1px solid #000;">'.$perc.'%</div>
                            <div style="width:40%;float:left;border-left:1px solid #000;">'.$grade.'</div></td>';
                } else {
                    $str .= '<td><div style="text-align:center;">-</div></td>';
                }
            }
            $str .= '</tr>';

            $str .= '<tr><th>Rank</th>';
            foreach ($exams as $exam) {
                $rank = $exam_rankings[$exam['eid']][$studentinfo->student_id] ?? '-';
                  $rank_display = is_numeric($rank) ? $this->ordinal($rank) : '-';
                $str .= '<td  style="text-align:center;font-weight:bold;">'.$rank_display.'</td>';
            }
            $str .= '</tr>';
        }

        $str .= '</tbody></table></div></div><div style="page-break-after:always;"></div>';
        $output .= $str;
    }

    echo $output;
}

private function ordinal($number)
{
    if (!in_array(($number % 100), [11, 12, 13])){
        switch ($number % 10){
            case 1:  return $number.'st';
            case 2:  return $number.'nd';
            case 3:  return $number.'rd';
        }
    }
    return $number.'th';
}

		
}
// end this file
