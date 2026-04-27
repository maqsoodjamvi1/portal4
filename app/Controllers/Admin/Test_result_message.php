<?php
namespace App\Controllers\Admin;



/**
 * Result Message Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class Test_result_message extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-test-result-message');
	}

	/**
	 * Index Page for this controller.
	*/
	 
public function index()
{
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	$schoolinfo = getSchoolInfo();
	$sessionData = array(
	'campusid' => $campusid,
	'sessionid' => $sessionid
	);
	$this->template_data['sessionData'] = $sessionData;

	$currentrole = currentUserRoles();

	if(in_array(5, $currentrole)){
		$sectionsclassinfo = teacherSubjectSections();
	}else{
		$sectionsclassinfo = userClassSections();
	}

	$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

	$this->db->where('campus_id', $campusid); 
	$campusinfo = $this->db->get('campus')->result();
	$this->template_data['campusinfo'] = $campusinfo;

	// $this->db->where('campus_id', $campusid); 
	// $this->db->where('session_id', $sessionid);
	// $examinfo = $this->db->get('exam')->result();
	// $this->template_data['examinfo'] = $examinfo;

	$this->db->where('campus_id', $campusid); 
	$this->db->where('session_id', $sessionid);
	$testSeriesinfo = $this->db->get('test_series')->result();
	$this->template_data['testSeriesinfo'] = $testSeriesinfo;

	// $fee_types = $this->db->query('SELECT * FROM `fee_type` WHERE system_id  ='.$schoolinfo->system_id)->result();
	// $this->template_data['fee_types'] = $fee_types;	
	
	$this->load->view('test_result_message_edit', $this->template_data);
}

function data(){
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	$schoolinfo = getSchoolInfo();

	$sessionInfo = $this->db->query('SELECT * FROM `academic_session` WHERE session_id ='.$sessionid)->row();
	$dateArr = explode('-',$sessionInfo->start_date);
	$session_year = $dateArr[0];

	$fee_type_id = $this->input->post('fee_type_id');
	$fee_month = $this->input->post('month');
	
	if(!empty($fee_month)){
		$monthdate = '"'.'0'.$fee_month.'/'.$session_year.'"';
	}

	$defaulter_fee_sms = '';
	$campusInfo = $this->db->query('SELECT student_fee_sms FROM campus WHERE campus_id='.$campusid)->row();
	if($campusInfo){
		$defaulter_fee_sms = $campusInfo->student_fee_sms;
	} 

	$strMessage = '<div class="form-group" style="clear:both;margin-top: 15px;">
     <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="father_contact" class="form-control" required > Father Contact </label>
     <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="mother_contact" class="form-control" required> Mother Contact </label>
     <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="emergency_contact" class="form-control" required > Emergency Contact </label>
  	</div>';
	
	// <input type="button" value="Date" onclick="formatText (\'date\');" /> 
    // <input type="button" value="Class" onclick="formatText (\'class\');" />

	$strMessage .= '<div class="form-group" style="clear:both;"> 
      <label for="description">Message</label>
      <textarea class="form-control" name="message" id="message" ></textarea>
	    <input type="button" value="First Name" onclick="formatText (\'first_name\');" /> 
	    <input type="button" value="Last Name" onclick="formatText (\'last_name\');" /> 
	    <input type="button" value="Father Name" onclick="formatText (\'father_name\');" /> 
	     
    <input type="button" value="Result" onclick="formatText (\'result\');" /> 
		<script type="text/javascript">
			function formatText(tag) {
		   var Field = document.getElementById(\'message\');
		   var val = Field.value;
		   var selected_txt = val.substring(Field.selectionStart, Field.selectionEnd);
		   var before_txt = val.substring(0, Field.selectionStart);
		   var after_txt = val.substring(Field.selectionEnd, val.length);
		   Field.value += \'{\' + tag + \'}\';
		}
		</script>
		</div><div class="form-group">';
	
	$students = $this->db->query('select * from student_class where student_id IN (select student_id from students where campus_id='.$campusid.') AND status=1 AND session_id='.$sessionid)->result();

	$detaulterArr = array();
	
			$strMessage .= '</div>';

	echo $strMessage;

}

function save(){
	$this->load->library('parser');
  	$id = intval($this->input->post('id'));	
  	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');
	$schoolinfo = getSchoolInfo();

	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
  
  	$this->db->where('campus_id', $campusid);
    $campusinfo = $this->db->get('campus')->row();
    $template = $this->input->post('message');
    $contacts = $this->input->post('contacts');
    $cls_sec_id = $this->input->post('cls_sec_id');
    $eid = $this->input->post('eid');
    if(empty($contacts)){
			echo 'Select Contact Type';
			exit;
		}
	
	if($id === 0){
		check_permission('admin-add-enquiry');
		$this->db->trans_begin();
	
	$students = $this->db->query('select student_id from student_class where status=1 AND session_id='.$sessionid.' and cls_sec_id='.$cls_sec_id)->result();

	foreach($students as $student){
		$student_id = $student->student_id; 
		
		// $datesheetinfo2 = $this->db->query('select * from datesheet where eid='.$eid.' AND sec_sub_id IN(SELECT sec_sub_id from section_subjects where cls_sec_id='.$cls_sec_id.' AND status=1)')->result();

		$datesheetSubject = $this->db->query('SELECT DISTINCT subject_id FROM tests WHERE total_marks > 0 AND cls_sec_id="'.$cls_sec_id.'" AND session_id="'.$sessionid.'" AND campus_id="'.$campus_id.'" GROUP BY subject_id')->result();
		
		if($datesheetSubject){
		$resultList = '';
		foreach ($datesheetSubject as $key => $value) {

			{

			$this->db->where('subject_id', $value->subject_id);
			$this->db->where('cls_sec_id', $studentinfo->cls_sec_id);
			$this->db->where('status', 1);
			$subjects = $this->db->get('section_subjects')->row();

			$this->db->where('sid', $subjects->subject_id);
			$academicsubjects = $this->db->get('allsubject')->row();

			  
		$where = "student_id=".$studentinfo->student_id." AND sec_sub_id=".$subjects->sec_sub_id."  AND t_series_id IN(select t_series_id from test_series where session_id=".$sessionid." AND status=1 AND campus_id=".$campus_id.")";
		
		$this->db->where($where);
		$stdresults = $this->db->get('test_series_subject_results')->result();

			  $this->db->where('sec_sub_id', $value->sec_sub_id);
			  $sectionSubjectInfo = $this->db->get('section_subjects')->row();

			  $this->db->where('sid', $sectionSubjectInfo->subject_id);
			  $allsubjectInfo = $this->db->get('allsubject')->row();

			    $obtained_marks = 0;
			    $result_id = 0;
				
				if($resultsdetail){
					$result_id = $resultsdetail->result_id;	
					$obtained_marks = $resultsdetail->obtained_marks;
				}
		
			  $resultList .= $allsubjectInfo->subject_short_name." ".$obtained_marks."/".$total_marks.", ";
			  }
			}
			}

		$results = $this->db->query('select * from exam_results WHERE student_id='.$student_id.' AND eid='.$eid)->row();
		$resultList .= "Total Marks: ".$results->obtain_total_mark."/".$results->exam_total_mark.", ";	
		//print_r($results);
		if($resultList){	
			
			$parentsInfo =  $this->db->query('select * from parents where parent_id IN (select parent_id from students where student_id='.$student_id.')')->row();
			
			$this->db->where('student_id', $student_id);
			$studentInfo = $this->db->get('students')->row();

			$this->db->where('student_id', $student_id);
			$studentClassInfo = $this->db->get('student_class')->row();

			$this->db->where('cls_sec_id', $studentClassInfo->cls_sec_id);
			$classSectionInfo = $this->db->get('class_section')->row();	

			$this->db->where('class_id', $classSectionInfo->class_id);
			$classInfo = $this->db->get('classes')->row();

			$this->db->where('section_id', $classSectionInfo->section_id);
			$SectionInfo = $this->db->get('sections')->row();

			$StudentClass = $classInfo->class_name.'('.$SectionInfo->section_name.')';
			
		if($parentsInfo){
			$mobile = '';
			foreach ($contacts as $key => $value) {

			if($value == 'father_contact'){
				$mobile = $parentsInfo->father_contact;
			}

			if($value == 'mother_contact'){
				$mobile = $parentsInfo->mother_contact;
			}

			if($value == 'emergency_contact'){
				$mobile = $parentsInfo->emergency_contact;
			}	

			$smsDate = date('Y-m-d');

			if(!empty($mobile)){

			$dataMessage = array(
		        'first_name' => $studentInfo->first_name,
		        'last_name' => $studentInfo->last_name,
		        'father_name' => $parentsInfo->f_name,
		        'class' => $StudentClass,
		        'result' => rtrim($resultList, ', '),
		        'date' => $smsDate
			);

			$parsedMessage = $this->parser->parse_string($template, $dataMessage);
			
			$data = array(
			'mobile' => $mobile,
			'message' => trim($parsedMessage),
			'campus_id' => trim($campusid),
			'parent_id' => $parentsInfo->parent_id,
			'status' => 0,
			'user_id' => $user_id, 
			'created_date' => $date
			);
			
			$this->db->insert('sms', $data);
			
		}
		}
		}
	
	}

	}	
		$this->db->trans_complete();
		$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Message Success')));
	}
}

}
// end this file