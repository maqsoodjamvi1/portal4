<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">


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

	// public function grade($marks){
		
	// 	$schoolinfo = getSchoolInfo();

	// 	$gradingPolicyInfo = $this->db->query('SELECT * FROM grading_policy WHERE system_id= '.$schoolinfo->system_id.' AND '.$marks.' BETWEEN mark_from AND mark_to ')->row();	
	// 	return $gradingPolicyInfo;
	// }
   public function grade($marks){
        $schoolinfo = getSchoolInfo();

        $this->db->select('grading_policy.*, grades.name as grade_name, grades.detail as grade_detail');
        $this->db->from('grading_policy');
        $this->db->join('grades', 'grades.gid = grading_policy.gid', 'left');
        $this->db->where('grading_policy.system_id', $schoolinfo->system_id);
        $this->db->where("{$marks} BETWEEN grading_policy.mark_from AND grading_policy.mark_to", NULL, FALSE);
        
        return $this->db->get()->row();
    } 

public function data()
{
    $useShortName = $this->input->post('useShortName') == '1';
    $rowHeight = (int) $this->input->post('rowHeight') ?: 30; // Default 30px
    $showMarks = filter_var($this->input->post('showMarks'), FILTER_VALIDATE_BOOLEAN);
    $showPercentage = filter_var($this->input->post('showPercentage'), FILTER_VALIDATE_BOOLEAN);
    $showGrades = filter_var($this->input->post('showGrades'), FILTER_VALIDATE_BOOLEAN);
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
            SELECT DISTINCT ds.sec_sub_id, ss.subject_id, s.subject_name, s.subject_short_name
            FROM datesheet ds
            JOIN section_subjects ss ON ss.sec_sub_id = ds.sec_sub_id AND ss.status = 1
            JOIN allsubject s ON s.sid = ss.subject_id
            WHERE ds.cls_sec_id = ? AND ds.total_marks > 0 AND ds.eid IN ($examids_csv)
        ", [$cls_sec_id])->result();



		$str = '<div class="student-result-card" style="page-break-inside: avoid;">';
		$str .= '<div class="printable-header" style="overflow: hidden; position: relative; height: auto; margin-bottom: 10px; padding: 10px; border-bottom: 2px solid #000;">';

		// School Logo (Left)
		if (!empty($schoolinfo->logo)) {
		    $str .= '<img src="' . base_url() . 'system-logo/' . $schoolinfo->logo . '" style="position: absolute; left: 10px; top: 10px; width: 120px; height: 120px; object-fit: contain; border: none;">';

		}

		

		// School Name (Center)
        $str .= '<h1 style="margin: 0 auto; font-size: 60px; font-family: Bebas Neue, cursive; letter-spacing: 2px; word-spacing: 4px; transform: scaleX(1.3); display: block; text-align: center; width: fit-content;">' . $schoolinfo->system_name . '</h1>';

		// Campus Info
		$campus_info = $this->db->get_where('campus', [
		    'system_id' => $schoolinfo->system_id,
		    'campus_id' => $campus_id
		])->row();

		$campus_name = $campus_info->campus_name ?? 'Main Campus';
		$landline = $campus_info->landline ?? 'N/A';
		$location = $campus_info->location ?? 'N/A';
		$website = $campus_info->website ?? '';

		// Campus Name, Contact, Location
		$str .= '<p style="margin: 1px 0; text-align: center; font-size: 20px;"><strong>Campus:</strong> ' . $campus_name . ' | <strong>Phone:</strong> ' . $landline . '</p>';
		$str .= '<p style="margin: 0px 0; text-align: center; font-size: 20px;"><strong>Location:</strong> ' . $location . '</p>';
		$str .= '<p style="margin: 0px 0; text-align: center; font-size: 20px;"><strong>Website:</strong> ' . $website . '</p>';

        // Determine Latest Exam (by exam date or eid)
        $latest_exam = end($exams); // assumes $exams is sorted by date or ID ascending

        // Use the name of the latest exam
        $latest_exam_name = $latest_exam['exam_name'] ?? 'Latest Exam';

        // Report Title with Latest Exam Name
        $str .= '<h2 style="margin: 0 auto; font-size: 40px; font-family: Bebas Neue, cursive; letter-spacing: 2px; word-spacing: 4px; transform: scaleX(1.3); display: block; text-align: center; width: fit-content;">Academic Report of ' . $latest_exam_name . '</h2>';

		$str .= '</div>'; // center div
		$str .= '</div>'; // printable-header


$str .= '<div style="
    background: linear-gradient(90deg, #f0f4f8, #d9e2ec);
    padding: 15px;
    border-radius: 12px;
    margin: 10px 0;
    font-size: 16px;
    color: #2c3e50;
    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
">';

// LEFT SIDE: Student Info
$str .= '<div style="width: 78%;">';

// Class Info Row
$str .= '<div style="width:100%; margin-bottom:10px; text-align:center;">';
$str .= '<div style="display:inline-block; font-size: 22px; font-weight: bold; color: #1a1a1a;" title="Class">
    🏫 Class: ' . $class_info['sectionclassname'] . '</div>';
$str .= '</div>';

// Row 1
$str .= '<div style="width:100%; display:flex; justify-content:space-between; margin-bottom:5px;">';
$str .= '<div style="width:48%; font-size: 18px;"><strong>🆔 Reg No:</strong> ' . $student_info->reg_no . '</div>';
$str .= '<div style="width:48%; font-size: 18px;"><strong>📞 Father Contact:</strong> ' . $parent_info->father_contact . '</div>';
$str .= '</div>';

// Row 2
$str .= '<div style="width:100%; display:flex; justify-content:space-between; margin-bottom:5px;">';
$str .= '<div style="width:48%; font-size: 18px;"><strong>👤 Name:</strong> ' . $student_info->first_name . ' ' . $student_info->last_name . '</div>';
$str .= '<div style="width:48%; font-size: 18px;"><strong>📱 Mother Contact:</strong> ' . $parent_info->mother_contact . '</div>';
$str .= '</div>';

// Row 3
$str .= '<div style="width:100%; display:flex; justify-content:space-between;">';
$str .= '<div style="width:48%; font-size: 18px;"><strong>👨‍👧 Father:</strong> ' . $f_name . '</div>';
$str .= '<div style="width:48%; font-size: 18px;"><strong>🚨 Emergency:</strong> ' . $parent_info->emergency_contact . '</div>';
$str .= '</div>';

$str .= '</div>'; // close LEFT info section

// RIGHT SIDE: Student Photo
$str .= '<div style="width: 20%; text-align: center;">';
if (!empty($student_info->profile_photo)) {
    $str .= '<img src="' . base_url() . 'uploads/' . $student_info->profile_photo . '" style="width: 100px; height: 130px; object-fit: cover; border-radius: 8px; border: 2px solid #ccc;">';
} else {
    $str .= '<div style="width: 100px; height: 130px; border-radius: 8px; border: 1px solid #000; text-align: center; line-height: 130px; font-size: 24px;"><i class="fa fa-user"></i></div>';
}
$str .= '</div>';

$str .= '</div>'; // end background container
$str .= '</div>';
$str .= '</div>';
$str .= '</div>';


          // Calculate number of visible columns
        $colCount = 0;
        if ($showMarks) $colCount += 2;
        if ($showPercentage) $colCount++;
        if ($showGrades) $colCount++;
        
        // Calculate column width percentage
        $colWidth = $colCount > 0 ? (100 / $colCount) : 100;
        
            $str .= '<table class="table table-bordered">';
            $str .= '<thead>';
            // ✅ Row 1: Subject + each exam name spanning multiple sub-columns
            $str .= '<tr style="height:' . $rowHeight . 'px;">';
            $str .= '<th rowspan="2" style="width: 30px; text-align:center;">Subject</th>'; // ✅ Added correctly
             
            foreach ($exams as $term) {
                   $n = 0;
                    if ($showMarks) { $n += 2; }
                    if ($showPercentage) { $n += 1; }
                    if ($showGrades) { $n += 1; }
                     $str .= '<th colspan="' . $n . '" style="text-align:center;">' . $term['exam_name'] . '</th>';
            }
            $str .= '</tr>';

            // Row 2: Sub-columns under each exam
            $str .= '<tr style="height:' . $rowHeight . 'px;">';
            foreach ($exams as $term) {
                if ($showMarks) {             
                $str .= '<th>Obt</th>';
                $str .= '<th>Total</th>';}
                if ($showPercentage) {
                $str .= '<th>Per (%)</th>';}
                if ($showGrades) {
                $str .= '<th>Grade</th>';}
            }
            $str .= '</tr>';

            $str .= '</thead><tbody>';

        $exam_totals = [];
        $has_any_grade = false;
      
         foreach ($subjects as $subject) {
             $subjectLabel = $useShortName && !empty($subject->subject_short_name)
                ? $subject->subject_short_name
                   : $subject->subject_name;
                      $str .= '<tr style="height:' . $rowHeight . 'px;"><td style="text-align:left;">' . $subjectLabel . '</td>';
            //$str .= '<tr style="height:'.$rowHeight.'px;"><td style="text-align:left;">' . $subject->subject_name . '</td>';
            foreach ($exams as $exam) {
                $res = $this->db->get_where('subject_results', [
                    'student_id' => $studentinfo->student_id,
                    'eid' => $exam['eid'],
                    'sec_sub_id' => $subject->sec_sub_id])->row();

                $ds = $this->db->get_where('datesheet', [
                    'eid' => $exam['eid'],
                    'sec_sub_id' => $subject->sec_sub_id
                ])->row();

              if ($res && $ds && $ds->total_marks > 0) {
                    $perc = round(($res->obtained_marks / $ds->total_marks) * 100);
                    $grade_obj = $this->grade($perc);
                    $grade = $grade_obj->grade_name ?? '-';
                    $has_any_grade = true;

                    
                    
                    if ($showMarks) {
                         $str .= '<td>' . $res->obtained_marks . '</td>';
                          $str .= '<td>' . $ds->total_marks . '</td>';  
                    }
                    
                    if ($showPercentage) {
                          $str .= '<td>' . $perc . '%</td>';
                    }
                    
                    if ($showGrades) {
                         $str .= '<td>' . $grade . '</td>';
                    }
                    
                    
                    // Sum totals
                    if (!isset($exam_totals[$exam['eid']])) {
                         $exam_totals[$exam['eid']] = ['obt' => 0, 'total' => 0];
                        }
                    $exam_totals[$exam['eid']]['obt'] += $res->obtained_marks;
                    $exam_totals[$exam['eid']]['total'] += $ds->total_marks;
                } else {
                    $str .= '<td>-</td><td>-</td><td>-</td><td>-</td>';
                }
            }
            $str .= '</tr>';
        }

                
           
        
        

         if ($has_any_grade) {
            $str .= '<tr style="height:'.$rowHeight.'px;"><th>Total</th>';
            foreach ($exams as $exam) {
                $tot = $exam_totals[$exam['eid']] ?? null;
                if ($tot && $tot['total'] > 0) {
                    $perc = round(($tot['obt'] / $tot['total']) * 100);
                      $grade_obj = $this->grade($perc);
                    $grade = $grade_obj->grade_name ?? '-';
                 

                    
                    
                    if ($showMarks) {
                        $str .= '<td>' . $tot['obt'] . '</td>';
                       $str .= '<td>' . $tot['total'] . '</td>';
                    }
                    
                    if ($showPercentage) {
                         $str .= '<td>' . $perc . '%</td>';
                    }
                    
                    if ($showGrades) {
                         $str .= '<td>' . $grade . '</td>';
                    }
                    
                    
                } else {
                     if ($showMarks) {
                            $str .= '<td>-</td><td>-</td>';
                        }
                        if ($showPercentage) {
                            $str .= '<td>-</td>';
                        }
                        if ($showGrades) {
                            $str .= '<td>-</td>';
                        }
                }
            }
            $str .= '</tr>';

            $str .= '<tr style="height:'.$rowHeight.'px;"><th>Position</th>';
            foreach ($exams as $exam) {
                        $rank = $exam_rankings[$exam['eid']][$studentinfo->student_id] ?? '-';
                          $rank_display = is_numeric($rank) ? $this->ordinal($rank) : '-';
                            $n=0;
                        if ($showMarks) { 
                            $n += 2;
                        }
                        if ($showPercentage) {         
                            $n += 1;
                         }
                        if ($showGrades) {
                            $n += 1;
                        }
                             
                             $str .= '<td  colspan = "' . $n . '" style="text-align:center;font-weight:bold;">'.$rank_display.'</td>';
                }// ending of position loop
                 $str .= '</tr>';

                $str .= '<tr style="height:' . $rowHeight . 'px;"><th>Attendance</th>';

                foreach ($exams as $exam) {
                    // Fetch term session date range for this exam
                    $term_session = $this->db->get_where('terms_session', [
                        'term_id' => $exam['term_id'],
                        'session_id' => $exam['session_id']
                    ])->row();

                    $present = $absent = $late = $early = 0;

                    if ($term_session && $term_session->start_date && $term_session->end_date) {
                        $attendance_summary = $this->db->query("
                            SELECT status, COUNT(*) as count
                            FROM attendance
                            WHERE student_id = ?
                            AND date BETWEEN ? AND ?
                            GROUP BY status
                        ", [
                            $studentinfo->student_id,
                            $term_session->start_date,
                            $term_session->end_date
                        ])->result();

                        foreach ($attendance_summary as $att) {
                            switch ($att->status) {
                                case 'P': $present = $att->count; break;
                                case 'A': $absent = $att->count; break;
                                case 'LC': $late = $att->count; break;
                                case 'EL': $early = $att->count; break;
                            }
                        }
                    }

                    // Count columns shown
                    $n = 0;
                    if ($showMarks) $n += 2;
                    if ($showPercentage) $n++;
                    if ($showGrades) $n++;

                   
                    // Output a compact badge layout
    $str .= '<td colspan="' . $n . '">';
    $str .= '<table style="width: 100%; font-size: 12px; text-align: center; border-collapse: collapse;"><tr>';
    
    $str .= '<td style="background: #e6ffed; color: #1a7f37; padding: 4px 6px; border-radius: 6px;"><strong>✅ Present:</strong> ' . $present . '</td>';
    $str .= '<td style="background: #ffeaea; color: #c0392b; padding: 4px 6px; border-radius: 6px;"><strong>❌ Absent:</strong> ' . $absent . '</td>';
    $str .= '<td style="background: #fff8e1; color: #b9770e; padding: 4px 6px; border-radius: 6px;"><strong>🕒 Late:</strong> ' . $late . '</td>';
    $str .= '<td style="background: #f0f0f5; color: #34495e; padding: 4px 6px; border-radius: 6px;"><strong>🚪 Early:</strong> ' . $early . '</td>';

    $str .= '</tr></table>';
    $str .= '</td>';
                }

                $str .= '</tr>';

        }

        $str .= '</tbody></table></div></div></div></div><div style="page-break-after:always;"></div>';
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
