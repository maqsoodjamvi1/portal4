<?php
namespace App\Controllers\Frontend;


/**
 * Test Result Card Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2023 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class Test_series_result_card extends MY_Controller {

	function __construct(){
		parent::__construct();
	}

	/**
	 * Index Page for this controller.
	 */
	public function index(){
		$this->page_construct('test_series_result_card');
	} 

	public function grade($marks){
		$grade ='';
		$schoolinfo = getSchoolInfoFront();

		$gradingPolicyInfo = $this->db->query('SELECT * FROM grading_policy WHERE system_id= '.$schoolinfo['system_id'].' AND '.$marks.' BETWEEN mark_from AND mark_to ')->row();	
		return $gradingPolicyInfo;
	}

	public function data()
	{
		//$cls_sec_id = $this->input->post('cls_sec_id');
		$schoolinfo = getSchoolInfoFront();
		if($this->input->post('session_id')){
			$sessionid = $this->input->post('session_id');
		}else{
				$sessionid = $schoolinfo['session_id'];	
		}
	
	  $testids = $this->input->post('testids');
		
		$exampercentage = 0;
		$cnic = $this->input->post('cnic');

		$parent_info = $this->db->query('SELECT * from parents where father_cnicnew="'.$cnic.'"')->row();	
	
		$campus_id = $parent_info->campus_id;	

	{
		$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND parent_id='.$parent_info->parent_id.') AND session_id ='.$sessionid.' order by cls_sec_id asc')->result();	
	}
	
	// $this->db->query(SELECT t_series_id FROM `test_series` WHERE campus_id=1 AND session_id=28)->result();
	
	if(!empty($testids)){
		$where = "t_series_id =".$testids;
	}else{	
		$where = "session_id=".$sessionid." AND status=1 AND campus_id=".$campus_id;
	}

	$this->db->where($where);	
	$tests = $this->db->get('test_series')->result_array();
	//print_r($this->db->last_query());

	$strResultCard = '';	
	foreach ($student_class as $studentinfo) {

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

		//$class_info = getClassSection($studentinfo->cls_sec_id);

		$resultcard = array();	
		$resulttotal = array();
		$resulttotalpercentage = array();
		$nonacademicresultcard = array();
		}
		if(!empty($tests)){
		  $termlastkey = count($tests)-1;
			$testName = $tests[$termlastkey]['series_name'];
		}else{
			echo "<div class='col-lg-12' style='background: red;color: #fff;width: 95%;margin: 14px 37px !important;'>Select exam to check result</div>";
			exit;
		}

		$testName = '';
		
	$strResultCard .= '<page><div style="border:1px dashed #000; border-radius:10px; text-align:center;"><div  class="col-lg-3" style="float: left;width: 100px;">';

	if(!empty($student_info->profile_photo)){ 
		$strResultCard .= '<img class="header_photo" src="/uploads/'.$student_info->profile_photo.'">';
	}else{ 
		$strResultCard .= '<i style="font-size: 90px;text-align: center;display: block;margin-top: 0px;" class="fa fa-user"></i>';
	} 

	$strResultCard .= '</div><div  class="col-lg-9" style="margin: 0 auto;"><h1 class="school_name" >'.$schoolinfo['system_name'].'</h1>';
	$strResultCard .= '<h3 class="campus_name" >'.$testName." ".$campus_info->campus_name.'</h3></div></div><div style="border:1px solid #000; float:left; width:100%; margin:10px auto;">';

	$strResultCard .= '<div style="width:33%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Reg No</strong> '.$student_info->reg_no.'</div>';

	$strResultCard .= '<div style="width:33%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Name:</strong>'.$student_info->first_name.' '.$student_info->last_name.'</div>';

	$strResultCard .= '<div style="width:33%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Father Name:</strong> '.$f_name.'</div>';

	$strResultCard .= '<div style="width:33%; padding-left:15px;  float:left;"> <strong>Father Contact #:</strong>'.$father_contact.'</div>';

	$strResultCard .= '<div style="width:33%; padding-left:15px;  float:left;"> <strong>Mother Contact #:</strong>  '.$mother_contact.'</div>';

	$strResultCard .= '<div style="width:33%; padding-left:15px;  float:left;"> <strong>Emergency Contact #:</strong> '.$emergency_contact.'</div></div>';

	$strResultCard .= '<div style="border:2px solid #000; float:left; width:100%; margin-bottom:0px auto; padding:2px;"><div class="heading">ACADEMIC PROGRESS</div>';
	$strResultCard .= '<table class="table table-bordered" style="margin-bottom: 2px;">
	<thead><tr><th style="width: 15%;">Subject</th>';

	foreach($tests as $term){ 
		$strResultCard .= '<th style="text-align: center;padding: 0px;line-height: 35px;">'.$term['series_name'].'<br>';
		$strResultCard .= '<div style="border-top:1px solid #000;">';
		$strResultCard .= '<div style="width:35%;float:left;border-start:1px solid #000;">Test Date</div>';
		//if(in_array('marks', $academic_result)){
			$strResultCard .= '<div style="width:25%;float:left;border-start:1px solid #000;">Marks</div>';
		//}
		//if(in_array('percentage', $academic_result)){
			$strResultCard .= '<div style="width:20%;float:left;border-start:1px solid #000;">PER</div>';
		//}
		//if(in_array('grade', $academic_result)){
			$strResultCard .= '<div style="width:20%;float:left;border-start:1px solid #000;">GR</div>';
		//}
		$strResultCard .= '</div>';
		$strResultCard .= '</th>';
	}  

	$strResultCard .= '</tr></thead><tbody>';
//foreach($tests as $term){ 
 
	if(!empty($testids)){
		$datesheetSubject = $this->db->query('SELECT DISTINCT subject_id FROM tests WHERE total_marks > 0 AND cls_sec_id="'.$studentinfo->cls_sec_id.'" AND t_series_id ="'.$testids.'"  GROUP BY subject_id')->result();
	}else{
		$datesheetSubject = $this->db->query('SELECT DISTINCT subject_id FROM tests WHERE total_marks > 0 AND cls_sec_id="'.$studentinfo->cls_sec_id.'" AND t_series_id ="'.$term['t_series_id'].'" AND session_id="'.$sessionid.'" AND campus_id="'.$campus_id.'" GROUP BY subject_id')->result();
	}
//$strResultCard .= '<tr><td>';
	foreach ($datesheetSubject as $key => $value) {

		$this->db->where('subject_id', $value->subject_id);
		$this->db->where('cls_sec_id', $studentinfo->cls_sec_id);
		$this->db->where('status', 1);
		$subjects = $this->db->get('section_subjects')->row();

		$this->db->where('sid', $subjects->subject_id);
		$academicsubjects = $this->db->get('allsubject')->row();
			
		$strResultCard .= '<tr><th style="padding: 4px 8px;">'.$academicsubjects->subject_name.'</th><td style="margin:0;padding:0;" colspan="3">';
		
		if(!empty($testids)){
			$where = "student_id=".$studentinfo->student_id." AND sec_sub_id=".$subjects->sec_sub_id."  AND t_series_id =".$testids;
		}else{
			$where = "student_id=".$studentinfo->student_id." AND sec_sub_id=".$subjects->sec_sub_id."  AND t_series_id IN(select t_series_id from test_series where session_id=".$sessionid." AND status=1 AND campus_id=".$campus_id.")";
		}
		$this->db->where($where);
		
		$stdresults = $this->db->get('test_results')->result();
		// print_r($this->db->last_query());
		// echo "<pre>";
		// print_r($stdresults);

		$emptycol = (count($tests)-count($stdresults)); 

	if($emptycol > 0){
		for($i=1; $i<=$emptycol; $i++){
			//$strResultCard .= '<td style="padding: 0px;line-height: 30px;font-size: 12px;text-align: center;"><div style="width: 33%;float: left;text-align:center;">-</div><div style="width: 33%;float: left;text-align:center;border-start:1px solid #000;">-</div><div style="width: 33%;float: left;text-align:center;border-start:1px solid #000;">-</div></td>';
		}
	}


	foreach($stdresults as $numbers){
		
		$this->db->where('sec_sub_id', $numbers->sec_sub_id);
		$section_subjects_info = $this->db->get('section_subjects')->row();
	
		$test_series_total_marks = $this->db->query('SELECT test_date,t_series_id, cls_sec_id, SUM(total_marks) AS totalmarks  FROM tests WHERE t_series_id = '.$numbers->t_series_id.'  AND cls_sec_id='.$studentinfo->cls_sec_id.' AND subject_id='.$section_subjects_info->subject_id.' AND test_id='.$numbers->test_id.' GROUP BY  test_date,subject_id,cls_sec_id, test_id')->row();
		$test_date = '';
		if($test_series_total_marks){
			$test_date = $test_series_total_marks->test_date;
			$newDateString = date_format(date_create_from_format('Y-m-d', $test_date), 'd-M-Y');
		}else{
			echo "Result Not Found";
			exit;
		}

		$subject_total_marks = $test_series_total_marks->totalmarks;

		if($subject_total_marks != 0){
			$subjectPercentage = round(((int)$numbers->obtained_marks/$subject_total_marks)*100);
		}
		
		$subjectgrade = $this->grade($subjectPercentage); 

		$strResultCard .= '<table class="table" style="margin:0;padding:0;border: 0 none;"><tr><td style="padding: 0px;line-height: 30px;font-size: 12px;text-align: center;">';
		$strResultCard .= "<div style='width: 40%;float: left;'>".$newDateString." </div>";
		//if(in_array('marks', $academic_result)){
			if($subject_total_marks > 0){
				$strResultCard .= "<div style='border-start:1px solid #000;width: 20%;float: left;'>".$numbers->obtained_marks.'/'.$subject_total_marks." </div>";
  		}else{
  			$strResultCard .= "<div style='border-start:1px solid #000;width: 20%;float: left;'>-</div>";
  		}
	//	}
		
		//if(in_array('percentage', $academic_result)){
			if($subject_total_marks > 0){
				$strResultCard .= '<div style="border-start:1px solid #000;width: 20%;float: left;">'.$subjectPercentage.'% </div>';
			}else{
				$strResultCard .= "<div style='border-start:1px solid #000;width: 20%;float: left;'>-</div>";
			}
		//}

		//if(in_array('grade', $academic_result)){
			if($subjectgrade){
				$this->db->where('gid', $subjectgrade->gid);
				$gradeinfo = $this->db->get('grades')->row();
				if($subject_total_marks > 0){
					$strResultCard .= '<div style="border-start:1px solid #000;width: 20%;float: left;">'.$gradeinfo->name.'</div>';
  			}else{
  				$strResultCard .= "<div style='border-start:1px solid #000;width: 20%;float: left;'>-</div>";
  			}
			}
		//}
		
		$strResultCard .= '</td></tr></table>'; 
	} 
	$strResultCard .= '</td></tr>';
 } 
 	//$strResultCard .= '</td></tr>';
	if(!empty($testids)){
		
		$results = $this->db->query('select * from test_results_compiled WHERE student_id='.$studentinfo->student_id.' AND t_series_id ='.$testids)->result();

	}else{	
	
		$results = $this->db->query('select * from test_results_compiled WHERE student_id='.$studentinfo->student_id.' AND t_series_id IN(select t_series_id from tests where campus_id='.$campus_id.' AND session_id='.$sessionid.')')->result();
	}


 	if($results){	

 		$strResultCard .= '<tr></tr><tr><th style="font-size: 14px;">Total</th>';
		$emptycol1 = '';//(count($exams)-count($value['resulttotalpercentage']));

			if($emptycol1 > 0){
				for($i=1; $i<=$emptycol1; $i++){
					$strResultCard .= '<td style="padding:5px;text-align:center;">0</td>';
				}
			}

 		foreach ($results as $result)	{	
 		
			$position  = $this->addOrdinalNumberSuffix($result->position);	
			$resulttotal[] = array('position' => $position);

			if($result->obtain_total_mark == 0 || $result->test_total_mark == 0){
				$exampercentage	= 0;
			}elseif($result->obtain_total_mark > 0 && $result->test_total_mark > 0){	
				$exampercentage = round(($result->obtain_total_mark/$result->test_total_mark)*100);
			}

		$examgrade = '';
		$examgrade = $this->grade($exampercentage);		
		
		$strResultCard .= '<td style="font-size: 14px;text-align: center;padding: 0px;line-height: 30px;"><div  style="width: 40%;float: left;">&nbsp;&nbsp;</div>';
		//if(in_array('marks', $academic_result)){
			$strResultCard .= '<div  style="border-start:1px solid #000;width: 20%;
	    float: left;">'.$result->obtain_total_mark."/"; 
			$strResultCard .= $result->test_total_mark." </div>"; 
		//}
		//if(in_array('percentage', $academic_result)){
			$strResultCard .= '<div  style="border-start:1px solid #000;width: 20%;
	    float: left;">&nbsp;&nbsp;<b>('.round($exampercentage).'%)</b> </div>';
		//}
		//if(in_array('grade', $academic_result)){
			if($examgrade){
				$this->db->where('gid', $examgrade->gid);
				$examgradeinfo = $this->db->get('grades')->row();
				$strResultCard .= '<div  style="border-start:1px solid #000;width: 20%;
	    float: left;">'.$examgradeinfo->name.'</div>';
			}
		//}
		$strResultCard .= '</td>';
		}
		 
	 
	$strResultCard .= '</tr>';
	//if(in_array('position', $academic_result)){	
	//$strResultCard .= '<tr><th style="font-size: 14px;">Position</th>';
	// if($results){	
 	// foreach ($results as $result){
 	
	// 	$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
	// 	$strResultCard .= $this->addOrdinalNumberSuffix($result->position); 
	// 	$strResultCard .= '</td>';
	// }
	// }
	$strResultCard .= '</tr>';
	//}
}	
	$strResultCard .= '</tbody></table></div></page><br><br><br><br><div style="clear: both;margin-bottom: 60px;"></div><p style="page-break-before: always;">&nbsp;</p>';
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
