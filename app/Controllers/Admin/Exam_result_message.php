<?php
namespace App\Controllers\Admin;



/**
 * Defaulter Message Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class Defaulter_message extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-defaulter-message');
	}

	/**
	 * Index Page for this controller.
	*/
	 
public function index()
{
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	$defaulter_fee_sms = '';
	$campusInfo = $this->db->query('SELECT student_fee_sms FROM campus WHERE campus_id='.$campusid)->row();
	if($campusInfo){
		$defaulter_fee_sms = $campusInfo->student_fee_sms;
	}
	$this->template_data['defaulter_fee_sms'] = $defaulter_fee_sms;

	$campusSections = getAllClassSection();
	$this->template_data['campusSections'] = $campusSections;

	$students = $this->db->query('select * from student_class where student_id IN (select student_id from students where campus_id='.$campusid.') AND status=1 AND session_id='.$sessionid)->result();

	$detaulterArr = array();
	foreach($students as $student){

		$studentInfo = $this->db->query('SELECT * FROM students WHERE student_id='.$student->student_id)->row();
				
		$studentsFee = $this->db->query('SELECT SUM(amount-discount) AS feeTotal FROM fee_chalan WHERE status="unpaid" AND  student_id='.$student->student_id)->row();
		
		if($studentsFee){	
			if($studentsFee->feeTotal > 0){	
				$parentsInfo =  $this->db->query('select * from parents where parent_id IN (select parent_id from students where student_id='.$student->student_id.')')->row();

				$detaulterArr[] = array(
					'student_id' => $studentInfo->student_id,
					'first_name' => $studentInfo->first_name,
					'last_name' => $studentInfo->last_name,
					'f_name' => $parentsInfo->f_name,
					'parent_id' => $parentsInfo->parent_id,
					'unpaid_fee' => $studentsFee->feeTotal
				);

		
			}
		}
	
	}
	

	$this->template_data['detaulterArr'] = $detaulterArr;

	$this->load->view('defaulter_message_edit', $this->template_data);
}

function data(){
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
}


public function parent_sms()
{
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');

	$defaulter_fee_sms = '';
	$campusInfo = $this->db->query('SELECT family_fee_sms FROM campus WHERE campus_id='.$campusid)->row();
	if($campusInfo){
		$defaulter_fee_sms = $campusInfo->family_fee_sms;
	}
	$this->template_data['defaulter_fee_sms'] = $defaulter_fee_sms;

	$campusSections = getAllClassSection();
	$this->template_data['campusSections'] = $campusSections;

	$parents = $this->db->query('select * from parents where parent_id IN (select parent_id from students where campus_id='.$campusid.' AND status=1)')->result();

	$detaulterArr = array();
	foreach($parents as $parent){

		//$studentInfo = $this->db->query('SELECT * FROM students WHERE student_id='.$student->student_id)->row();

				
		$studentsFee = $this->db->query('SELECT SUM(amount-discount) AS feeTotal FROM fee_chalan WHERE status="unpaid" AND student_id IN(select student_id from students where parent_id='.$parent->parent_id.' AND status=1)')->row();
		
		if($studentsFee){	
			if($studentsFee->feeTotal > 0){	
				
				$detaulterArr[] = array(
					'f_name' => $parent->f_name,
					'parent_id' => $parent->parent_id,
					'unpaid_fee' => $studentsFee->feeTotal
				);

		
			}
		}
	
	}
	

	$this->template_data['detaulterArr'] = $detaulterArr;

	$this->load->view('parent_message_edit', $this->template_data);
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
    if(empty($contacts)){
			echo 'Select Contact Type';
			exit;
		}
	
	if($id === 0){
			check_permission('admin-add-enquiry');
			$this->db->trans_begin();
	
	$students = $this->input->post('student_id');
	//$this->db->query('select * from student_class where student_id IN (select student_id from students where campus_id='.$campusid.') AND status=1 AND session_id='.$sessionid)->result();


	foreach($students as $student_id){

		$studentsFee = $this->db->query('SELECT SUM(amount-discount) AS feeTotal FROM fee_chalan WHERE status="unpaid" AND  student_id='.$student_id)->row();
		
		if($studentsFee){	
			if($studentsFee->feeTotal > 0){
				
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
			        'balance' => $studentsFee->feeTotal,
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
	}	
		$this->db->trans_complete();
		$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Message Success')));
	}
}


function saveparent(){
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
    if(empty($contacts)){
			echo 'Select Contact Type';
			exit;
		}
	
	if($id === 0){
			check_permission('admin-add-enquiry');
			$this->db->trans_begin();
	
	$parents = $this->input->post('parent_id');
	

	foreach($parents as $parent_id){

		// $studentsFee = $this->db->query('SELECT SUM(amount-discount) AS feeTotal FROM fee_chalan WHERE status="unpaid" AND  student_id IN(select student_id from students where parent_id='.$parent_id.' AND status=1)')->row();
		$studentsFee = $this->db->query('SELECT SUM(amount-discount) AS feeTotal FROM fee_chalan WHERE status="unpaid" AND student_id IN(select student_id from students where parent_id='.$parent_id.' AND status=1)')->row();
		
		if($studentsFee){	
			if($studentsFee->feeTotal > 0){
				
			$parentsInfo =  $this->db->query('select * from parents where parent_id ='.$parent_id)->row();

			
			
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
			        'father_name' => $parentsInfo->f_name,
			        'balance' => $studentsFee->feeTotal,
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
		}	
			$this->db->trans_complete();
			$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Message Success')));
		}
	}

	function delete(){
		check_permission('admin-del-enquiry');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

		// delete user
		$this->db->where('id', $id);
		$this->db->delete('classes');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Question Quiz Success'));
	}


}
// end this file