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

class Students_results_card2  extends MY_Controller {

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
		$this->load->view('students_results_card2', $this->template_data); 
	}

	public function grade($marks){
		$grade ='';
		$schoolinfo = getSchoolInfo();

		$gradingPolicyInfo = $this->db->query('SELECT * FROM grading_policy WHERE system_id= '.$schoolinfo->system_id.' AND '.$marks.' BETWEEN mark_from AND mark_to ')->row();	
		return $gradingPolicyInfo;
	}

	public function data()
	{
		$cls_sec_id = $this->input->post('cls_sec_id');
		$position_num = $this->input->post('position_num');
		$remarks = $this->input->post('remarks');
		$class_teacher_sign = $this->input->post('class_teacher_sign');
		$principle_sign = $this->input->post('principle_sign');
		$parent_sign = $this->input->post('parent_sign');
		$sign_lines = $this->input->post('sign_lines');
		$sign_lines2 = $this->input->post('sign_lines2');
		$sign_lines3 = $this->input->post('sign_lines3');
		$hide_profile_pic = $this->input->post('hide_profile_pic');
		$hide_phone_number = $this->input->post('hide_phone_number');
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');
		$previous_session = $this->input->post('previous_session');

		$schoolinfo = getSchoolInfo();
		$exampercentage = 0;
		if($this->input->post('non_academics')){
			$non_academics = $this->input->post('non_academics');
		}else{
			$non_academics = array();
		}
		
		
		if($this->input->post('academic_result')){
			$academic_result = $this->input->post('academic_result');
		}else{
			$academic_result =  array('marks','percentage','grade');
		}

		$examids = $this->input->post('examids');

		$campus_id = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$sessionData = array(
		'campusid' => $campus_id,
		'sessionid' => $sessionid
		);

	$this->template_data['sessionData'] = $sessionData;

	if($previous_session){
		if($cls_sec_id){
		    $student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE  campus_id='.$campus_id.') AND session_id ='.$sessionid.' AND  cls_sec_id ='.$cls_sec_id.' order by cls_sec_id asc')->result();	
		}else{
			$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE campus_id='.$campus_id.') AND session_id ='.$sessionid.' order by cls_sec_id asc')->result();	
		}
	}else{
		if($cls_sec_id){
			$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE  campus_id='.$campus_id.') AND session_id ='.$sessionid.' AND status=1 AND  cls_sec_id ='.$cls_sec_id.' order by cls_sec_id asc')->result();	
		}else{
			$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE campus_id='.$campus_id.') AND session_id ='.$sessionid.' AND status=1 order by cls_sec_id asc')->result();	
		}
	}
	
	if(!empty($examids)){
		$eids = implode(', ', $examids); 
		$where = "eid IN(".$eids.")";
	}else{	
		$where = "session_id=".$sessionid." AND status=1 AND campus_id=".$campus_id;
	}

	$this->db->where($where);	
	$exams = $this->db->get('exam')->result_array();

	$strResultCard = '';	
	foreach ($student_class as $studentinfo) {
		if(!empty($examids)){
			$eids = implode(', ', $examids); 
			$datesheetSubject = $this->db->query('SELECT DISTINCT sec_sub_id FROM datesheet WHERE total_marks > 0 AND cls_sec_id="'.$studentinfo->cls_sec_id.'" AND eid IN(select eid from exam where eid IN("'.$eids.'")) AND sec_sub_id IN(SELECT sec_sub_id from section_subjects where cls_sec_id='.$cls_sec_id.' AND status=1) GROUP BY sec_sub_id')->result();
		}else{
			$datesheetSubject = $this->db->query('SELECT DISTINCT sec_sub_id FROM datesheet WHERE total_marks > 0 AND cls_sec_id="'.$studentinfo->cls_sec_id.'" AND eid IN(select eid from exam where session_id="'.$sessionid.'" AND status=1 AND campus_id="'.$campus_id.'") AND sec_sub_id IN(SELECT sec_sub_id from section_subjects where cls_sec_id='.$cls_sec_id.' AND status=1) GROUP BY sec_sub_id')->result();
		}

		$totalA = 0;
		$totalP = 0;
		if($start_date && $end_date){
			$totaPresents = $this->db->query("SELECT count(attendance_id) as totalP FROM attendance WHERE `date` BETWEEN '".$start_date."' AND  '".$end_date."' AND student_id=".$studentinfo->student_id." AND `status` = 'P'")->row();

			$totaAbsentees = $this->db->query("SELECT count(attendance_id) as totalA FROM attendance WHERE `date` BETWEEN '".$start_date."' AND  '".$end_date."' AND student_id=".$studentinfo->student_id." AND `status` = 'A'")->row();
		
			//print_r($totaPresents->totalP);
			$totalA = $totaAbsentees->totalA;
			$totalP = $totaPresents->totalP;

		}

		$this->db->where('student_id', $studentinfo->student_id);
		$student_info = $this->db->get('students')->row();
		
		$this->db->where('campus_id', $campus_id);
		$campus_info = $this->db->get('campus')->row();

		if($student_info){

		$this->db->where('parent_id', $student_info->parent_id);
		$parent_info = $this->db->get('parents')->row();
		$f_name = '';
		$father_contact = '';
		$mother_contact = '';
		$emergency_contact = '';
		if($parent_info){
			$f_name = $parent_info->f_name;
			$father_contact = $parent_info->father_contact;
			$mother_contact = $parent_info->mother_contact;
			$emergency_contact = $parent_info->emergency_contact;
		}

		$class_info = getClassSection($studentinfo->cls_sec_id);

		$resultcard = array();	
		$resulttotal = array();
		$resulttotalpercentage = array();
		$nonacademicresultcard = array();
		}
		if(!empty($exams)){
		$termlastkey = count($exams)-1;
		
		$examsession_id = $exams[$termlastkey]['session_id'];
		$this->db->where('session_id', $examsession_id);
		$academic_session_info = $this->db->get('academic_session')->row();
		$examName = $exams[$termlastkey]['exam_name'];
		}else{
			echo "<div class='col-lg-12' style='background: red;color: #fff;width: 95%;margin: 14px 37px !important;'>Select exam to check result</div>";
			exit;
		}
		
	$strResultCard .= '<page style="float: left;color: #000 !important;font-family: Arial, Helvetica, sans-serif;"><div style="border:1px dashed #000; border-radius:10px; text-align:center;float:left;" class="col-lg-12"><div  class="col-lg-3" style="float: left;width: 100px;">';
		if($hide_profile_pic != 1){
			if(!empty($student_info->profile_photo)){ 
				$strResultCard .= '<img style="width: 65px;margin-top: 8px;border-radius: 8px;" src="uploads/'.$student_info->profile_photo.'">';
			}else{ 
				$strResultCard .= '<i style="font-size: 90px;text-align: center;display: block;margin-top: 0px;" class="fa fa-user"></i>';

			} 
		}
	$strResultCard .= '</div><div  class="col-lg-9 printable_result_header_width" style="margin: 0 auto;float:left;"><h1 style="margin-top:5px; font-size:40px; font-family:Times New Roman">'.$schoolinfo->system_name.'</h1>';
	$strResultCard .= '<h3 style="margin-top:5px;font-family: "Orbitron";font-size: 16px;">'.$examName." (".$academic_session_info->session_name.") ".$campus_info->campus_name.'</h3></div>';
	$strResultCard .= '<div  class="col-lg-3" style="float: right;width: 100px;">';
//if($hide_profile_pic != 1)
{
	if(!empty($schoolinfo->logo)){ 
		$strResultCard .= '<img style="width: 85px;margin-top: 8px;border-radius: 8px;" src="'.base_url().'system-logo/'.$schoolinfo->logo.'">';
	}

}
	$strResultCard .= '</div>';

	$strResultCard .= '</div>';
	$strResultCard .= '<div style="border:1px solid #000; float:left; width:100%; margin:10px auto;">';

	$strResultCard .= '<div style="width:33%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Reg No</strong> '.$student_info->reg_no.' ( '.$class_info['sectionclassname'].')</div>';

	$strResultCard .= '<div style="width:33%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Name:</strong>'.$student_info->first_name.' '.$student_info->last_name.'</div>';

	$strResultCard .= '<div style="width:33%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Father Name:</strong> '.$f_name.'</div>';
	if($hide_phone_number != 1){
		$strResultCard .= '<div style="width:33%; padding-left:15px;  float:left;"> <strong>Father Contact #:</strong>'.$father_contact.'</div>';

		$strResultCard .= '<div style="width:33%; padding-left:15px;  float:left;"> <strong>Mother Contact #:</strong>  '.$mother_contact.'</div>';

		$strResultCard .= '<div style="width:33%; padding-left:15px;  float:left;"> <strong>Emergency Contact #:</strong> '.$emergency_contact.'</div>';
	}
	$strResultCard .= '</div><div style="border:2px solid #000; float:left; width:100%; margin-bottom:0px auto; padding:2px;"><div class="heading">ACADEMIC PROGRESS</div>';
	$strResultCard .= '<table class="table table-bordered" style="margin-bottom: 2px;">
	<thead><tr><th class="heading3" style="width: 18%;">Subject</th>';

	foreach($exams as $term){ 
		$strResultCard .= '<th class="heading3" style="text-align: center;padding: 0px;line-height: 35px;">'.$term['exam_name'].'<br>';
		$strResultCard .= '<div style="border-top:1px solid #000;">';
		//$colNcount  = (100%/count($academic_result));

		if(in_array('marks', $academic_result)){
			$strResultCard .= '<div style="width:20%;float:left;">Obt. </div>';
		}
		if(in_array('marks', $academic_result)){ 
			$strResultCard .= '<div style="border-left:1px solid #000;width:20%;float:left;">Total </div>';
		}
		if(in_array('percentage', $academic_result)){
			$strResultCard .= '<div style="width:20%;float:left;border-left:1px solid #000;">Per</div>';
		}
		if(in_array('grade', $academic_result)){
			$strResultCard .= '<div style="width:40%;float:left;border-left:1px solid #000;">Grade</div>';
		}
		if(in_array('subject_remarks', $academic_result)){
			$strResultCard .= '<div style="width:40%;float:left;border-left:1px solid #000;">Remarks</div>';
		}
		$strResultCard .= '</div>';
		$strResultCard .= '</th>';
	} 

	$strResultCard .= '</tr></thead><tbody>';

	foreach ($datesheetSubject as $key => $value) {

		$this->db->where('sec_sub_id', $value->sec_sub_id);
		$this->db->where('status', 1);
		$subjects = $this->db->get('section_subjects')->row();

	if($subjects){
		$this->db->where('sid', $subjects->subject_id);
		$academicsubjects = $this->db->get('allsubject')->row();
			
		$strResultCard .= '<tr><th class="heading3" style="padding: 2px 8px;">'.$academicsubjects->subject_name.'</th>';
		
		if(!empty($examids)){
			$eids = implode(', ', $examids); 
			$where = "student_id=".$studentinfo->student_id." AND session_id=".$sessionid." AND sec_sub_id=".$subjects->sec_sub_id."  AND eid IN(".$eids.")";
		}else{
			$where = "student_id=".$studentinfo->student_id." AND session_id=".$sessionid." AND sec_sub_id=".$subjects->sec_sub_id."  AND eid IN(select eid from exam where session_id=".$sessionid." AND status=1 AND campus_id=".$campus_id.")";
		}
		$this->db->where($where);
		$stdresults = $this->db->get('subject_results')->result();
		// print_r($this->db->last_query());
		// exit;

		$emptycol = (count($exams)-count($stdresults)); 

	if($emptycol > 0){
		for($i=1; $i<=$emptycol; $i++){
			// <div style="width: 20%;float: left;text-align:center;border-left:1px solid #000;">-</div>
			$strResultCard .= '<td style="padding: 0px;line-height: 30px;text-align: center;"><div style="width: 20%;float: left;text-align:center;">-</div><div style="width: 20%;float: left;text-align:center;border-left:1px solid #000;">-</div><div style="width: 20%;float: left;text-align:center;border-left:1px solid #000;">-</div><div style="width: 25%;float: left;text-align:center;border-left:1px solid #000;">-</div></td>';
	}
	}
	foreach($stdresults as $numbers){
		
		// $this->db->where('eid', $numbers->eid);
		// $this->db->where('sec_sub_id', $numbers->sec_sub_id);
		// $datesheetinfo = $this->db->get('datesheet')->row();

		$datesheetinfo = $this->db->query('select * from datesheet where eid='.$numbers->eid.' AND sec_sub_id IN(SELECT sec_sub_id from section_subjects where sec_sub_id='.$numbers->sec_sub_id.' AND status=1 )')->row();

		if($datesheetinfo->total_marks != 0){
			$subjectPercentage = round(($numbers->obtained_marks/$datesheetinfo->total_marks)*100);
		}
		
		$subjectgrade = $this->grade($subjectPercentage);

		$strResultCard .= '<td class="heading3" style="padding: 0px;line-height: 35px;text-align: center;">';
		
		if(in_array('marks', $academic_result)){
			if($datesheetinfo->total_marks > 0){
				$strResultCard .= "<div style='width: 20%;float: left;'>".$numbers->obtained_marks."</div><div style='border-left:1px solid #000;width: 20%;float: left;'>".$datesheetinfo->total_marks." </div>";
	  		}else{
	  			$strResultCard .= "<div style='width: 20%;float: left;'>-</div>";
	  			$strResultCard .= "<div style='border-left:1px solid #000;width: 20%;float: left;'>-</div>";
	  		}
		}
		
		if(in_array('percentage', $academic_result)){
			if($datesheetinfo->total_marks > 0){
				$strResultCard .= '<div style="border-left:1px solid #000;width: 20%;float: left;">'.$subjectPercentage.'% </div>';
			}else{
				$strResultCard .= "<div style='border-left:1px solid #000;width: 20%;float: left;'>-</div>";
			}
		}

		if(in_array('grade', $academic_result)){
			if($subjectgrade){
				$this->db->where('gid', $subjectgrade->gid);
				$gradeinfo = $this->db->get('grades')->row();
				if($datesheetinfo->total_marks > 0){
					$strResultCard .= '<div style="border-left:1px solid #000;width: 40%;float: left;">'.$gradeinfo->name.'</div>';
  			}else{
  				$strResultCard .= "<div style='border-left:1px solid #000;width: 40%;float: left;'>-</div>";
  			}
			}
		}

		if(in_array('subject_remarks', $academic_result)){
			if($subjectgrade){
				$this->db->where('gid', $subjectgrade->gid);
				$gradeinfo = $this->db->get('grades')->row();
				//print_r($gradeinfo);
				if($datesheetinfo->total_marks > 0){
					$strResultCard .= '<div style="border-left:1px solid #000;width: 20%;float: left;">'.$gradeinfo->detail.'</div>';
  			}else{
  				$strResultCard .= "<div style='border-left:1px solid #000;width: 20%;float: left;'>-</div>";
  			}
			} 
		}
		
		$strResultCard .= '</td>'; 
	} 
	$strResultCard .= '</tr>';
	}
 } 

$strResultCard .= '<tr></tr><tr><th class="heading3" style="font-size: 14px;">Total</th>';
$emptycol1 = '';//(count($exams)-count($value['resulttotalpercentage']));

	if($emptycol1 > 0){
		for($i=1; $i<=$emptycol1; $i++){
			$strResultCard .= '<td style="padding:5px;text-align:center;">0</td>';
		}
	}
	
	if(!empty($examids)){
		$eids = implode(', ', $examids); 
		$results = $this->db->query('select * from exam_results WHERE student_id='.$studentinfo->student_id.' AND eid IN('.$eids.')')->result();

	}else{	
 		$results = $this->db->query('select * from exam_results WHERE student_id='.$studentinfo->student_id.' AND eid IN(select eid from exam where campus_id='.$campus_id.' AND session_id='.$sessionid.' AND status=1)')->result();
 	}
 	
 	if($results){	
 		foreach ($results as $result)	{	
 		
			$position  = $this->addOrdinalNumberSuffix($result->position);	
			$resulttotal[] = array('position' => $position);
			if($result->obtain_total_mark == 0 || $result->exam_total_mark == 0){
				$exampercentage	= 0;
			}elseif($result->obtain_total_mark > 0 && $result->exam_total_mark > 0){	$exampercentage = round(($result->obtain_total_mark/$result->exam_total_mark)*100);
			}

	$examgrade = '';
	$examgrade = $this->grade($exampercentage);		
	
	$strResultCard .= '<td class="heading3" style="font-size: 16px;text-align: center;padding: 0px;line-height: 35px;font-weight:bold;">';
	if(in_array('marks', $academic_result)){
		$strResultCard .= '<div  style="width: 20%;float: left;">'.$result->obtain_total_mark."</div>
		<div  style='border-left:1px solid #000;width: 20%;float: left;'>"; 
		$strResultCard .= $result->exam_total_mark." </div>"; 
	}
	if(in_array('percentage', $academic_result)){
		$strResultCard .= '<div  style="border-left:1px solid #000;width: 20%;
    float: left;">&nbsp;&nbsp;'.round($exampercentage).'% </div>';
	}
	if(in_array('grade', $academic_result)){
		if($examgrade){
			$this->db->where('gid', $examgrade->gid);
			$examgradeinfo = $this->db->get('grades')->row();
			$strResultCard .= '<div  style="border-left:1px solid #000;width: 40%;
    float: left;">'.$examgradeinfo->name.'</div>';
		}
	}
	if(in_array('subject_remarks', $academic_result)){
		if($examgrade){
			$this->db->where('gid', $examgrade->gid);
			$examgradeinfo = $this->db->get('grades')->row();
			$strResultCard .= '<div  style="border-left:1px solid #000;width: 20%;
    float: left;">'.$examgradeinfo->detail.'</div>';
		}
	}
	$strResultCard .= '</td>';
	}
}		 
	 
	$strResultCard .= '</tr>';

	if(in_array('total_remarks', $academic_result)){	
		$this->db->where('gid', $examgrade->gid);
		$examgradeinfo = $this->db->get('grades')->row();
		$strResultCard .= '<tr><th style="font-size: 14px;">Remarks</th>';
			$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
			$strResultCard .= $examgradeinfo->detail;
			$strResultCard .= '</td>';
		
		$strResultCard .= '</tr>';
	
	}

	if(in_array('position', $academic_result)){	
		$strResultCard .= '<tr><th style="font-size: 14px;">Position</th>';
		if($results){	
			foreach ($results as $result){

			$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
			if($result->position <= $position_num){
				$strResultCard .= $this->addOrdinalNumberSuffix($result->position); 
			}
			$strResultCard .= '</td>';
		}
		}
		$strResultCard .= '</tr>';
	
	}

	if(in_array('presents', $non_academics)){	
		$strResultCard .= '<tr><th style="font-size: 14px;">Presents</th>';
		
			$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
			$strResultCard .= $totalP;
			$strResultCard .= '</td>';
		
		$strResultCard .= '</tr>';
	
	}

	if(in_array('absentees', $non_academics)){	
		$strResultCard .= '<tr><th style="font-size: 14px;">Absentees</th>';
		
			$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
			$strResultCard .= $totalA;
			$strResultCard .= '</td>';
		
		$strResultCard .= '</tr>';
	
	}

	$strResultCard .= '<tr style="display:none"><td  style="padding: 3px;" colspan="6"><div class="heading2">NON ACADEMIC</div></td></tr>';
	//print_r($non_academics);
	if(in_array('study_complaints', $non_academics)){	
	$strResultCard .= '<tr><th style="width:15%;">Study Complaints</th>';
	if($results){	
 	foreach ($results as $result){
 	
		$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
		$strResultCard .= $result->study_complaints; 
		$strResultCard .= '</td>';
	}
	}
	$strResultCard .= '</tr>';
	}
	if(in_array('discinpline_complaints', $non_academics)){	
	$strResultCard .= '<tr><th style="width:15%;">Discipline Complaints</th>';
	if($results){	
 	foreach ($results as $result){		
		$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
		$strResultCard .= $result->disc_complaints; 
		$strResultCard .= '</td>';
	}
	}
	$strResultCard .= '</tr>';
}
// if(in_array('absentees', $non_academics)){	
// $strResultCard .= '<tr><th style="width:15%;">Absentees</th>';
// 	if($results){	
//  	foreach ($results as $result){		
// 	$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
// 	$strResultCard .= $result->absentees."/";
// 	$strResultCard .= $result->working_days; 
// 	$strResultCard .= '</td>';
// 	}
// }
// 	$strResultCard .= '</tr>';	
// }
if(in_array('leaves', $non_academics)){	
	$strResultCard .= '<tr><th style="width:15%;">Leaves</th>';
	if($results){	
 	foreach ($results as $result){		
	$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
	$strResultCard .= $result->leave."/";
	$strResultCard .= $result->working_days; 
	$strResultCard .= '</td>';
	}
}
	$strResultCard .= '</tr>';
}
if(in_array('late_comming', $non_academics)){	
	$strResultCard .= '<tr><th style="width:15%;">Late Comming</th>';
	if($results){	
	 	foreach ($results as $result){		
		$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
		$strResultCard .= $result->late_comming."/";
		$strResultCard .= $result->working_days; 
		$strResultCard .= '</td>';
		}
	}
	$strResultCard .= '</tr>';
}
if(in_array('early_left', $non_academics)){	
	$strResultCard .= '<tr><th style="width:15%;">Early Left</th>';
	if($results){	
	 	foreach ($results as $result){		
		$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
		$strResultCard .= $result->early_left."/";
		$strResultCard .= $result->working_days; 
		$strResultCard .= '</td>';
		}
	}
	$strResultCard .= '</tr>';
}
	$strResultCard .= '</tbody></table></div>';
	if($sign_lines == 1){
		$strResultCard .= '<div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;"><span style="font-size:16px;font-weight:bold;">'.$remarks.'</span>&nbsp;&nbsp;</div>';
	}
	if($sign_lines2 == 1){
		$strResultCard .= '<div style="float:left;width:48%; border-bottom:1px solid;margin-top:20px;"><span style="font-size:16px;font-weight:bold;">'.$class_teacher_sign.'</span>&nbsp;&nbsp;</div>';
		$strResultCard .= '<div style="float:left;width:48%;margin-left:2%; border-bottom:1px solid;margin-top:20px;"><span style="font-size:16px;font-weight:bold;">'.$principle_sign.'</span>&nbsp;&nbsp;</div>';
	}
	if($sign_lines3 == 1){
		$strResultCard .= '<div style="float:left;width:48%; border-bottom:1px solid;margin-top:20px;"><span style="font-size:16px;font-weight:bold;">'.$parent_sign.'</span>&nbsp;&nbsp;</div>';
		$strResultCard .= '<div style="float:left;width:48%;margin-left:2%; border-bottom:1px solid;margin-top:20px;"><span style="font-size:16px;font-weight:bold;"></span>&nbsp;&nbsp;</div>';
	}
	$strResultCard .= '</page><br><br><br><br><div style="clear: both;margin-bottom: 60px;"></div><p style="page-break-before: always;">&nbsp;</p>';
}

	echo $strResultCard;		  
	//$this->template_data['data'] = $data;
	//$this->load->view('students_results_card', $this->template_data);
}

function addOrdinalNumberSuffix($num) {
    if (!in_array(($num % 100),array(11,12,13))){
      switch ($num % 10) {
        // Handle 1st, 2nd, 3rd
        case 1:  return $num.'st';
        case 2:  return $num.'nd';
        case 3:  return $num.'rd';
      }
    }
    return $num.'th';
  }

}
// end this file
