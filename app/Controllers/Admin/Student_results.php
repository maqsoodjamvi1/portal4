<?php
namespace App\Controllers\Admin;



/**
 * Result Card Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class Student_results extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-student-result-report');
	}

	/**
	* Index Page for this controller.
	*/

	public function index(){
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$this->db->where('system_id', $schoolinfo->system_id);	
		$allsubject = $this->db->get('allsubject')->result();

		$this->template_data['allsubject'] = $allsubject;

		$this->db->where('campus_id', $campusid);
		$classsectioninfo = $this->db->get('class_section')->result();
		$sectionsclassinfo = array();
		foreach($classsectioninfo as $section){
		
		$this->db->where('class_id', $section->class_id);
		$classinfo = $this->db->get('classes')->row();

		$this->db->where('section_id', $section->section_id);
		$sectioninfo = $this->db->get('sections')->row();
		
		$sectionsclassinfo[] = array(
		'section_id' => $section->cls_sec_id,
		'sectionclassname' => $classinfo->class_name." (".$sectioninfo->section_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

		$this->load->view('student_results', $this->template_data);
	}

	public function grade($marks){
		$grade ='';
		$schoolinfo = getSchoolInfo();

		$gradingPolicyInfo = $this->db->query('SELECT * FROM grading_policy WHERE system_id= '.$schoolinfo->system_id.' AND '.$marks.' BETWEEN mark_from AND mark_to ')->row();	
		return $gradingPolicyInfo;


	}

	function get_studentinfo(){
		$campusid = $this->session->userdata('member_campusid');
		$term = $this->input->post('term');		
		$status = 1;

	 $studentsinfo = $this->db->query("select * from students where (first_name like '%".$term['term']."%' OR last_name like '%".$term['term']."%') AND status=".$status." AND campus_id=".$campusid)->result_array();
		
		 // Initialize Array with fetched data 
    
    $data = array();
    
    foreach($studentsinfo as $student){

     	$classstudents = $this->db->query("select * from student_class where  student_id = ".$student['student_id'])->row();

     	$parentsInfo = $this->db->query("select f_name from parents where  parent_id = ".$student['parent_id'])->row();
     	
     	$stdInfotxt = $student['first_name']." ".$student['last_name']." c/o ".$parentsInfo->f_name;

     	if($classstudents){
     		 $data[] = array("id"=>$student['student_id'], "text"=>$stdInfotxt);
     	}
     }

		return json_response($data);	 
}

	public function data()
	{
		$student_id = $this->input->post('student_id'); 
		$subject_id = $this->input->post('subject_id'); 
		$schoolinfo = getSchoolInfo();

		$this->db->where('system_id', $schoolinfo->system_id);
		$academicSession = $this->db->get('academic_session')->result();

		$exampercentage = 0;

		if($this->input->post('non_academics')){
			$non_academics = $this->input->post('non_academics');
		}else{
			$non_academics = array('study_complaints','discinpline_complaints','absentees');
		}
		
		if($this->input->post('academic_result')){
			$academic_result = $this->input->post('academic_result');
		}else{
			$academic_result =  array('marks','percentage','grade');
		}

		$examids = $this->input->post('examids');

		$campus_id = $this->session->userdata('member_campusid');
		//$sessionid = $this->session->userdata('member_sessionid');
	
$strResultCard = '';
$nCount = 0; 
foreach($academicSession as $sessionInfo){

	$sessionid = $sessionInfo->session_id;
	$sessionName = $sessionInfo->session_name;
		
	$student_class = $this->db->query('SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE STATUS=1 AND campus_id='.$campus_id.') AND session_id ='.$sessionid.' AND student_id ='.$student_id.' order by cls_sec_id asc')->result();


	if($student_class){
	if(!empty($examids)){
		$eids = implode(', ', $examids); 
		$where = "eid IN(".$eids.")";
	}else{	
		$where = "session_id=".$sessionid." AND status=1 AND campus_id=".$campus_id;
	}
	$this->db->where($where);	
	$exams = $this->db->get('exam')->result_array();

	$headerRep = reportHeader();
	
	if($nCount == 0){
	$strResultCard .= $headerRep;	
	$strResultCard .= '<div class="reportHeading">Student Result Report</div><table class="resultReport table table-bordered" style="margin-bottom: 2px;">
	<thead><tr><th style="text-align: center;padding: 0 8px;line-height: 35px;width:15%;z-index: 1000;"><div style="width:100%;">Name</div></th><th style="text-align: center;padding: 0 8px;line-height: 35px;width: 15%;z-index: 1000;"><div style="width:100%;">Subject</div></th>';

	foreach($exams as $term){ 
		$strResultCard .= '<th style="text-align: center;padding: 0 8px;line-height: 35px;">'.$term['exam_name'].'<br>';
		if(in_array('marks', $academic_result)){
			$strResultCard .= '<div style="width:33%;float:left;">Marks</div>';
		}
		if(in_array('percentage', $academic_result)){
			$strResultCard .= '<div style="width:33%;float:left;border-start:1px solid #000;">Per</div>';
		}
		if(in_array('grade', $academic_result)){
			$strResultCard .= '<div style="width:33%;float:left;border-start:1px solid #000;">Grade</div></th>';
		}
	} 

	$strResultCard .= '<th style="text-align:center;">Total<br>';
	if(in_array('marks', $academic_result)){
			$strResultCard .= '<div style="width:33%;float:left;">Marks</div>';
		}
		if(in_array('percentage', $academic_result)){
			$strResultCard .= '<div style="width:33%;float:left;border-start:1px solid #000;">Per</div>';
		}
		if(in_array('grade', $academic_result)){
			$strResultCard .= '<div style="width:33%;float:left;border-start:1px solid #000;">Grade</div>';
		}
}

$nCount++;
foreach ($student_class as $studentinfo) {
		
		
		$this->db->where('cls_sec_id', $studentinfo->cls_sec_id);
		$classSection = $this->db->get('class_section')->row();

		$this->db->where('class_id', $classSection->class_id);
		$classesInfo = $this->db->get('classes')->row();

		if($subject_id){
			$this->db->where('subject_id', $subject_id);
		}
		$this->db->where('cls_sec_id', $studentinfo->cls_sec_id);
		$class_subjects = $this->db->get('section_subjects')->result();


		if($subject_id){
			$this->db->where('subject_id', $subject_id);
		}
		$this->db->where('cls_sec_id', $studentinfo->cls_sec_id);
		$subjectSubCount = $this->db->get('section_subjects')->result_array();


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
		$examName = $exams[$termlastkey]['exam_name'];
		}else{
			//echo "<div class='col-lg-12' style='background: red;color: #fff;width: 95%;margin: 14px 37px !important;'>Select exam to check result</div>";
			//exit;
		}
		
	$totalSectionSubject = count($subjectSubCount) + 2;

	if(in_array('position', $academic_result)){	
			$totalSectionSubject = $totalSectionSubject + 1;
	}

	$strResultCard .= '</tr></thead><tbody><tr><th rowspan="'.$totalSectionSubject.'">'.$classesInfo->class_name.' ('.$sessionName.')</th>';

	foreach ($class_subjects as $key => $subjects) {
		
		$this->db->where('sid', $subjects->subject_id);
		$academicsubjects = $this->db->get('allsubject')->row();
		
		$strResultCard .= '<tr><th style="padding: 4px 8px;">'.$academicsubjects->subject_name.'</th>';
		if(!empty($examids)){
			$eids = implode(', ', $examids); 
			$where = "student_id=".$studentinfo->student_id." AND session_id=".$sessionid." AND sec_sub_id=".$subjects->sec_sub_id."  AND eid IN(".$eids.")";
		}else{
			$where = "student_id=".$studentinfo->student_id." AND session_id=".$sessionid." AND sec_sub_id=".$subjects->sec_sub_id."  AND eid IN(select eid from exam where session_id=".$sessionid." AND status=1 AND campus_id=".$campus_id.")";
		}

		$this->db->where($where);
		$stdresults = $this->db->get('subject_results')->result();

		$emptycol = '';//(count($exam)-count($stdresults)); 

	if($emptycol >0){
		for($i=1; $i<=$emptycol; $i++){
			$strResultCard .= '<td style="padding:5px;">0</td>';
	}
	}
	
	//$allsubjectsTotal = 0;
	//$allsubjectsObtained = 0;
	
	foreach($stdresults as $numbers){
		
		$this->db->where('eid', $numbers->eid);
		$this->db->where('sec_sub_id', $numbers->sec_sub_id);
		$datesheetinfo = $this->db->get('datesheet')->row();
		if($datesheetinfo->total_marks != 0){
			$subjectPercentage = round(($numbers->obtained_marks/$datesheetinfo->total_marks)*100);
			//$allsubjectsObtained = ($allsubjectsTotal + $numbers->obtained_marks);
			//$allsubjectsTotal = ($allsubjectsTotal + $datesheetinfo->total_marks);
		}
		
		$subjectgrade = $this->grade($subjectPercentage);
		
		$this->db->where('gid', $subjectgrade->gid);
		$gradeinfo = $this->db->get('grades')->row();
		
		$subjectgradeName = '';
		if($gradeinfo){
				$subjectgradeName = $gradeinfo->name;
		}

		$strResultCard .= '<td style="padding: 0px 8px;line-height: 30px;font-size: 12px;text-align: center;">';
		
		if(in_array('marks', $academic_result)){
			if($datesheetinfo->total_marks > 0){
				$strResultCard .= "<div style='width: 33%;float: left;'>".$numbers->obtained_marks.'/'.$datesheetinfo->total_marks." </div>";
  		}else{
  			$strResultCard .= "<div style='width: 33%;float: left;'>-</div>";
  		}
		}
		
		if(in_array('percentage', $academic_result)){
			if($datesheetinfo->total_marks > 0){
				$strResultCard .= '<div style="border-start:1px solid #000;width: 33%;float: left;">'.$subjectPercentage.'% </div>';
			}else{
				$strResultCard .= "<div style='border-start:1px solid #000;width: 33%;float: left;'>-</div>";
			}
		}

		if(in_array('grade', $academic_result)){
			if($subjectgrade){
				if($datesheetinfo->total_marks > 0){
					$strResultCard .= '<div style="border-start:1px solid #000;width: 33%;float: left;">'.$subjectgradeName.'</div>';
  			}else{
  				$strResultCard .= "<div style='border-start:1px solid #000;width: 33%;float: left;'>-</div>";
  			}
			}
		}
		
	} 

	$subjectsObtainedMarks = $this->db->query('SELECT SUM(obtained_marks) as obtained_marks FROM subject_results WHERE eid IN(SELECT eid FROM exam WHERE session_id='.$sessionid.') AND sec_sub_id = '.$numbers->sec_sub_id.' AND student_id='.$studentinfo->student_id)->row();

	$subjectsTotalMarks = $this->db->query('SELECT SUM(total_marks) as total_marks FROM datesheet WHERE eid IN(SELECT eid FROM exam WHERE session_id='.$sessionid.') AND sec_sub_id = '.$numbers->sec_sub_id)->row();

	$allsubjectsObtained = $subjectsObtainedMarks->obtained_marks;
	$allsubjectsTotal = $subjectsTotalMarks->total_marks;

	if($allsubjectsTotal != 0){
		$allsubjectPercentage = round(($allsubjectsObtained/$allsubjectsTotal)*100);
	}

	$allsubjectgrade = $this->grade($allsubjectPercentage);

	$this->db->where('gid', $allsubjectgrade->gid);
	$allgradeinfo = $this->db->get('grades')->row();
		

$strResultCard .= '</td><td style="padding: 0 8px;line-height:30px;text-align:center;">';
	if(in_array('marks', $academic_result)){
			if($allsubjectsTotal > 0){
				$strResultCard .= "<div style='width: 33%;float: left;'>".$allsubjectsObtained.'/'.$allsubjectsTotal." </div>";
  		}else{
  			$strResultCard .= "<div style='width: 33%;float: left;'>-</div>";
  		}
		}

		if(in_array('percentage', $academic_result)){
				if($allsubjectsTotal > 0){
				$strResultCard .= '<div style="border-start:1px solid #000;width: 33%;float: left;">'.$allsubjectPercentage.'% </div>';
			}else{
				$strResultCard .= "<div style='border-start:1px solid #000;width: 33%;float: left;'>-</div>";
			}
		}

		if(in_array('grade', $academic_result)){
			if($allgradeinfo){
				if($allsubjectsTotal > 0){
					$strResultCard .= '<div style="border-start:1px solid #000;width: 33%;float: left;">'.$allgradeinfo->name.'</div>';
  			}else{
  				$strResultCard .= "<div style='border-start:1px solid #000;width: 33%;float: left;'>-</div>";
  			}
			}
		}	
	$strResultCard .= '</td>'; 
	$strResultCard .= '</tr>';
 } 

$strResultCard .= '</th></tr>';
if(empty($subject_id)){

$strResultCard .= '<tr><th style="font-size: 14px;">Total</th>';
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
 		
 		$allExamMarks = 0;
 		$allExamObtained = 0;
 		
 		foreach ($results as $result)	{	

			$position  = $this->addOrdinalNumberSuffix($result->position);	
			$resulttotal[] = array('position' => $position);
			if($result->obtain_total_mark == 0 || $result->exam_total_mark == 0){
				$exampercentage	= 0;
			}elseif($result->obtain_total_mark > 0 && $result->exam_total_mark > 0){	$exampercentage = round(($result->obtain_total_mark/$result->exam_total_mark)*100);
		}

	$examgrade = '';
	$examgrade = $this->grade($exampercentage);

	$this->db->where('gid', $examgrade->gid);
	$examgradeinfo = $this->db->get('grades')->row();
	

	$allExamObtained = ($allExamObtained + $result->obtain_total_mark);
	$allExamMarks = ($allExamMarks + $result->exam_total_mark);				
	
	$strResultCard .= '<td style="font-size: 14px;text-align: center;padding: 0 8px;line-height: 45px;">';
	if(in_array('marks', $academic_result)){
		$strResultCard .= '<div  style="width: 33%;
    float: left;font-size:13px;">'.$result->obtain_total_mark."/"; 
		$strResultCard .= $result->exam_total_mark." </div>"; 
	}
	if(in_array('percentage', $academic_result)){
		$strResultCard .= '<div  style="border-start:1px solid #000;width: 33%;
    float: left;font-size:13px;">&nbsp;&nbsp;<b>('.round($exampercentage).'%)</b> </div>';
	}
	if(in_array('grade', $academic_result)){
		if($examgradeinfo){
			$strResultCard .= '<div  style="border-start:1px solid #000;width: 33%;
    float: left;font-size:13px;">'.$examgradeinfo->name.'</div>';
		}
	}
	$strResultCard .= '</td>';
	}

		if($allExamMarks != 0){
			$allExamPercentage = round(($allExamObtained/$allExamMarks)*100);
		}
		
	$allExamgrade = $this->grade($allExamPercentage);

	$this->db->where('gid', $allExamgrade->gid);
	$allexamgradeinfo = $this->db->get('grades')->row();


	$strResultCard .= '<td style="font-size: 14px !important;text-align: center;padding: 0 8px;line-height: 45px;">';
	
	if(in_array('marks', $academic_result)){
		$strResultCard .= '<div  style="width: 33%;
    float: left;">'.$allExamObtained."/"; 
		$strResultCard .= $allExamMarks." </div>"; 
	}
	if(in_array('percentage', $academic_result)){
		$strResultCard .= '<div  style="border-start:1px solid #000;width: 33%;
    float: left;">&nbsp;&nbsp;<b>('.round($allExamPercentage).'%)</b> </div>';
	}
	if(in_array('grade', $academic_result)){
	if($allexamgradeinfo){
		$strResultCard .= '<div  style="border-start:1px solid #000;width: 33%;
  float: left;">'.$allexamgradeinfo->name.'</div>';
	}

	}

}		 
	 
	$strResultCard .= '</tr>';
	if(in_array('position', $academic_result)){	
	$strResultCard .= '<tr><th style="font-size: 14px;">Position</th>';
	if($results){	
 	foreach ($results as $result){
 	
		$strResultCard .= '<td style="font-size: 14px;text-align: center;">';
		$strResultCard .= $result->position; 
		$strResultCard .= '</td>';
	}
	}
	$strResultCard .= '</tr>';

	}

	}

	$strResultCard .= '<tr style="border-bottom:2px solid #000;"></tr>';

	}

	}
	}
	$strResultCard .= '</tbody></table>';

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
