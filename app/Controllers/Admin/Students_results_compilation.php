<?php

/**
 * Fee Student Results Compilation Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Students_results_compilation extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-results-compilation');

	}

	/** 
	 * Index Page for this controller.
	*/

	public function index()
	{
		$this->load->view('students_results_compilation', $this->template_data);
	}

	function data(){

		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$response->recordsTotal = $this->db->count_all('studentsresults');
		$results = $this->db->get('studentsresults')->result();
		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		
		foreach($results as $row){
			$data = array();
			$allsubjectinfo = array();
 		    $data['student_id'] =	$row->student_id;
			
			$this->db->where('student_id', $row->student_id);
			$studentsinfo = $this->db->get('students')->row();
			
			$this->db->where('class_id', $row->class_id);
			$classesinfo = $this->db->get('classes')->row();
			
			$this->db->where('sid', $row->subject_id);
			$allsubjectinfo = $this->db->get('allsubject')->row();
			
			$this->db->where('session_id', $row->session_id);
			$session_id_info = $this->db->get('academic_session')->row();
			
			$data['student'] = $studentsinfo->first_name." ".$studentsinfo->last_name;
			$data['class'] = $classesinfo->class_name;
			$data['subject'] = $allsubjectinfo->subject_name;
			$data['obtained_marks'] = $row->obtained_marks;
			$data['session_id_info'] = $session_id_info->session_name;
			$response->data[] = $data;
		}


		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-results-compilation');
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');

		$this->db->where('session_id', $sessionid);
		$this->db->where('campus_id', $campusid);
		$examinfo = $this->db->get('exam')->result();
 		$this->template_data['examinfo'] = $examinfo;

		$this->load->view('students_results_compilation', $this->template_data);

	}


function save(){
	$id = intval($this->input->post('eeid'));
	$campus_id = $this->session->userdata('member_campusid');
	$session_id = $this->session->userdata('member_sessionid');
	$eid = $this->input->post('eid');
	$fromDate = $this->input->post('from_date');
	$toDate = $this->input->post('to_date');

	if(empty($eid)){
		json_response(array('error' => TRUE, 'msg' => 'Exam is not selected'));
		exit;
	} 
			
if($id === 0){

check_permission('admin-add-results-compilation');

$subjectinfo = $this->db->get('allsubject')->result();

//$subejectids = '';
// foreach($subjectinfo as $subject){
// 	$subejectids .= $subject->sid.",";
// }
// $subejectids = rtrim($subejectids,',');
		
$study_complaints = 0;
$disc_complaints = 0;
$late_comming = 0;
$absentees = 0;
$leave = 0;
$early_left = 0;
$presents = 0;
$working_days = 0;	

$clsSecInfo = $this->db->query('select * from class_section WHERE status=1 and campus_id='.$campus_id)->result();

foreach ($clsSecInfo as $key => $clsSection) {

	$result = $this->db->query('SELECT eid,student_id,rank,total_score FROM (SELECT *,  IF(@marks=(@marks:=total_score), @auto, @auto:=@auto+1) AS rank FROM (SELECT * FROM (SELECT student_id, SUM(obtained_marks) AS total_score,eid FROM subject_results ,(SELECT @auto:=0, @marks:=0) AS init WHERE sec_sub_id IN(SELECT sec_sub_id FROM datesheet WHERE `sec_sub_id` IN(SELECT sec_sub_id FROM section_subjects WHERE cls_sec_id='.$clsSection->cls_sec_id.') AND total_marks > 0 AND eid='.$eid.') AND `eid`='.$eid.' GROUP BY student_id ) sub ORDER BY total_score DESC) t ) AS result')->result();	

	$afftectedRows=0;			

if($result){

	$total_marks = $this->db->query('SELECT eid, cls_sec_id, SUM(total_marks) AS totalmarks  FROM datesheet WHERE eid = '.$eid.'  AND cls_sec_id='.$clsSection->cls_sec_id.' GROUP BY  cls_sec_id, eid')->row();

	$total_marks = $total_marks->totalmarks;

	foreach ($result as $key => $value) {

		$stdComplaintsresult = $this->db->query('SELECT count(cid) AS StdCompaintsT from complaints where student_id='.$value->student_id.' AND type="Study" AND date between "'.$fromDate.'" AND "'.$toDate.'"')->row();
		
		$discComplaintsresult = $this->db->query('SELECT count(cid) AS discCompaintsT from complaints where student_id='.$value->student_id.' AND type="Discipline" AND date between "'.$fromDate.'" AND "'.$toDate.'"')->row();
		
		$Absresult = $this->db->query('SELECT COUNT(attendance_id) AS AbsenteesT FROM attendance WHERE student_id='.$value->student_id.' AND status="A" AND date between "'.$fromDate.'" AND "'.$toDate.'"')->row();
			
		
		$leaveresult = $this->db->query('SELECT COUNT(attendance_id) AS leavesT FROM attendance WHERE student_id='.$value->student_id.' AND STATUS="L" AND date between "'.$fromDate.'" AND "'.$toDate.'"')->row();
		$pesentresult = $this->db->query('SELECT COUNT(attendance_id) AS presentT FROM attendance WHERE student_id='.$value->student_id.' AND STATUS="P" AND date between "'.$fromDate.'" AND "'.$toDate.'"')->row();

		$lcresult = $this->db->query('SELECT COUNT(attendance_id) AS lcT FROM attendance WHERE student_id='.$value->student_id.' AND lc_duration > 0 AND date between "'.$fromDate.'" AND "'.$toDate.'"')->row();

		$elresult = $this->db->query('SELECT COUNT(attendance_id) AS elT FROM attendance WHERE student_id='.$value->student_id.' AND el_duration > 0 AND date between "'.$fromDate.'" AND "'.$toDate.'"')->row();


			 
		if(isset($stdComplaintsresult)){ 
			$study_complaints = $stdComplaintsresult->StdCompaintsT;
		}
		if(isset($discComplaintsresult)){
			$disc_complaints = $discComplaintsresult->discCompaintsT;
		}
		if(isset($lcresult)){
			$late_comming = $lcresult->lcT;
		}
		if(isset($Absresult)){
			$absentees = $Absresult->AbsenteesT;
		}
		if(isset($leaveresult)){
			$leave = $leaveresult->leavesT;
		}
		if(isset($elresult)){
			$early_left = $elresult->elT;
		}
			
		if(isset($pesentresult)){
			$presents = $pesentresult->presentT;
		}
			
		$totalWorkingDays =  ($presents + $leave + $absentees);

		$selectexisting = $this->db->query("SELECT *  FROM exam_results where eid=".$eid." AND  student_id=".$value->student_id)->row();

	
		if(empty($selectexisting)){
			$this->db->trans_begin();
			$data = array( 
				'eid' => $eid,
				'student_id' => $value->student_id,
				'position' => $value->rank,
				'exam_total_mark' => $total_marks,
				'obtain_total_mark' => $value->total_score,
				'study_complaints' => $study_complaints,
				'disc_complaints' => $disc_complaints,
				'late_comming' => $late_comming,
				'absentees' => $absentees,
				'leave' => $leave,
				'early_left' => $early_left,
				'working_days' => $totalWorkingDays,
				'remark' => 'Test',

			);

			

			$this->db->insert('exam_results', $data);
			$new_user_id = $this->db->insert_id();
			//echo $this->db->last_query();
			$this->db->trans_complete();
			$afftectedRows++;			

			}else{

			//$this->db->trans_begin();	

			$data = array(
				'position' => $value->rank,
				'exam_total_mark' => $total_marks,
				'obtain_total_mark' => $value->total_score,
				'study_complaints' => $study_complaints,
				'disc_complaints' => $disc_complaints,
				'late_comming' => $late_comming,
				'absentees' => $absentees,
				'leave' => $leave,
				'early_left' => $early_left,
				'working_days' => $totalWorkingDays,

			);
			
			
			$this->db->where('student_id', $value->student_id);
			$this->db->where('eid', $eid);	
			$this->db->update('exam_results', $data);

		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";
		 	//print_r($this->db->error());
			//$this->db->trans_complete();
			
			$afftectedRows++;
		}
	
		$afftectedRows = $afftectedRows;

		}
	}
	
	}

	
	json_response(array('success' => TRUE, 'msg' => 'Results Compiled '));

}

}

}

// end this file

