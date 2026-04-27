<?php
namespace App\Controllers\Frontend;


/**
 * Datesheet Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class Students_datesheet_card extends MY_Controller {

	function __construct(){
		parent::__construct();
	}

	/**
	 * Index Page for this controller.
	 */
	public function index(){
		$this->page_construct('students_datesheet_card');
	}

	public function grade($marks){
		$grade ='';
		$schoolinfo = getSchoolInfoFront();

		$gradingPolicyInfo = $this->db->query('SELECT * FROM grading_policy WHERE system_id= '.$schoolinfo['system_id'].' AND '.$marks.' BETWEEN mark_from AND mark_to ')->row();	
		return $gradingPolicyInfo;
	}

	public function data()
	{

		$schoolinfo = getSchoolInfoFront();
		if($this->input->post('session_id')){
			$sessionid = $this->input->post('session_id');
		}else{
			$sessionid = $schoolinfo['session_id'];	
		}
	
	$testids = $this->input->post('testids');
		
	$exampercentage = 0;
	$pid = $this->input->post('pid');
	
	$examids = $this->input->post('examids');

	$parent_info = $this->db->query('SELECT * from parents where parent_id="'.$pid.'"')->row();	
	
	$campus_id = $parent_info->campus_id;

	$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND parent_id='.$parent_info->parent_id.') AND session_id ='.$sessionid.' order by cls_sec_id asc')->result();	
	
	if(!empty($examids)){
		$eids = $examids;
		$where = "eid IN(".$eids.")";
	}else{	
		$where = "session_id=".$sessionid." AND status=1 AND campus_id=".$campus_id;
	}

	$this->db->where($where);	
	$exams = $this->db->get('exam')->result_array();

	$strResultCard = '';	
	foreach ($student_class as $studentinfo) {
		
		$this->db->where('eid',$eids);
		$this->db->where('cls_sec_id',$studentinfo->cls_sec_id);
		$this->db->order_by("exam_date", "ASC");
		$datesheetSubject = $this->db->get('datesheet')->result();

		$classSectioninfo = getClassSection($studentinfo->cls_sec_id);
		
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
		
	$strResultCard .= '<page style="float: left;color: #000 !important;font-family: Arial, Helvetica, sans-serif;"><div style="border:1px dashed #000; border-radius:10px; text-align:center;" class="col-lg-12">';
	$strResultCard .= '<div  class="col-lg-3 text-center" >';

	if(!empty($schoolinfo['logo'])){ 
		$strResultCard .= '<img style="width: 65px;margin-top: 8px;border-radius: 8px;" src="'.base_url().'system-logo/'.$schoolinfo['logo'].'">';
	}

	$strResultCard .= '</div>';
 
	$strResultCard .= '<div  class="col-lg-9 printable_result_header_width" style="margin: 0 auto;width:100%;"><h1 style="margin-top:5px; font-size:22px;font-weight:bold; font-family:Times New Roman">'.$schoolinfo['system_name'].'</h1><span style="margin-top:5px;font-size: 16px;display: block;">'.$campus_info->campus_name.'</span>';

	$strResultCard .= '</div></div>';
	$strResultCard .= '<div style="border:1px solid #000; float:left; width:100%; margin:10px auto;">';
$strResultCard .= '<div  class="col-lg-3 text-center" >';
	if(!empty($student_info->profile_photo)){ 
		$strResultCard .= '<img style="width: 65px;height:65px;margin-top: 8px;text-align:center;border-radius: 50px;" src="/uploads/'.$student_info->profile_photo.'">';
	}else{ 
		$strResultCard .= '<i style="font-size: 90px;text-align: center;display: block;margin-top: 0px;" class="fa fa-user"></i>';
	} 

	$strResultCard .= '</div>';

	$strResultCard .= '<div style="width:100%; padding-left:15px; border-bottom:1px solid #000; float:left;text-align:center;">'.$student_info->first_name.' '.$student_info->last_name.' ('.$classSectioninfo['sectionclassname'].')</div>';
	
	$strResultCard .= '</div><div style="border:2px solid #000; float:left; width:100%; margin-bottom:0px auto; padding:2px;"><div class="heading">DATE SHEET <br><span style="font-size: 11px;display:block;text-align:center;">'.$examName." (".$academic_session_info->session_name.') </span></div></div>';
	$strResultCard .= '<table class="table table-bordered" style="margin-bottom: 2px;">
	<thead><tr>';
	$strResultCard .= '</tr></thead><tbody>';
	foreach ($datesheetSubject as $key => $value) {

		$this->db->where('sec_sub_id', $value->sec_sub_id);
		$this->db->where('status', 1);
		$subjects = $this->db->get('section_subjects')->row();

		$exam_date = DateTime::createFromFormat('Y-m-d',$value->exam_date);
	  $exam_date = $exam_date->format('j-M-Y');
	
		$dayOfWeek = date("l", strtotime($value->exam_date));
	  $total_marks = $value->total_marks;
	  $syllabus = $value->syllabus;

		if($subjects && $total_marks > 0){ 

			$this->db->where('sid', $subjects->subject_id);
			$academicsubjects = $this->db->get('allsubject')->row();
				
			$strResultCard .= '<tr><th class="heading3" style="padding: 2px 8px;width:40%;">'.$exam_date.'</th>';
			$strResultCard .= '<th class="heading3" style="padding: 2px 8px;">'.$dayOfWeek.'</th>';
			$strResultCard .= '<th class="heading3" style="padding: 2px 8px;">'.$academicsubjects->subject_name.'</th>';
		  $strResultCard .= '</tr>';
		  $strResultCard .= '<tr style="border-bottom: 4px solid maroon;">';
		  $strResultCard .= '<th colspan="3">'.$syllabus.'</th>';
		  $strResultCard .= '</tr>';


		}

	} 

		$strResultCard .= '</tbody></table></div>';
		$strResultCard .= '</page><br><br><br><br><div style="clear: both;margin-bottom: 60px;"></div><p style="page-break-before: always;">&nbsp;</p>';
}

	echo $strResultCard;		  
	
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
