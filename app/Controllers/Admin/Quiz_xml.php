<?php
namespace App\Controllers\Admin;


/**
 * Quiz Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Quiz_xml extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-quiz');
	}

	/**
	 * Index Page for this controller.
	 */
	 
	public function index()
	{
		$quizinfo = $this->db->get('quiz')->result();
		$quizQuestioninfo = $this->db->get('quiz_questions')->result();

		$strXmlQuestion = '';
		$strXmlQuestion .= '<?xml version="1.0" encoding="UTF-8"?><questions><category>';
		foreach ($quizinfo as $key => $value) {
		
		$strXmlQuestion .= '<thumb name="'.$value->quiz_name.'">assets/item_thumb_0.svg</thumb>';
		}

	$strXmlQuestion .= '</category>';
	foreach ($quizQuestioninfo as $key => $questionInfo) {
	
	$this->db->where('quiz_id', $questionInfo->quiz_id);
	$QustionQuizInfo = $this->db->get('quiz')->row();
		
	$this->db->where('content_id', $questionInfo->content_id);
	$QustionContentInfo = $this->db->get('contents')->row();
	
	$strXmlQuestion .= '<item>
		<category>Quiz 1</category>
		<landscape>
			<question type="text"><![CDATA[Which is not number?]]></question>
			<answers correctAnswer="1,3,4">
				<answer type="text" top="41" width="35" left="14"><![CDATA[Space]]></answer>
				<answer top="41" type="text" left="51" width="35"><![CDATA[999]]></answer>
				<answer top="58" type="text" left="14" width="35"><![CDATA[7Eleven]]></answer>
				<answer top="58" type="text" left="51" width="35"><![CDATA[Sixteen]]></answer>
				<answer top="75" type="text" submit="true" left="32" width="35"><![CDATA[Done]]></answer>
			</answers>
			<inputs/>
			<explanation/>
		</landscape>
		
		<portrait>
			<question type="text" fontSize="35" top="15" lineHeight="45" align="center"><![CDATA[ Which is not number? ]]></question>
			<answers correctAnswer="1,3,4">
				<answer type="text" width="90" height="10" top="35" fontSize="30" lineHeight="30" offsetTop="-10"><![CDATA[Space]]></answer>
				<answer width="90" height="10" top="47" type="text" fontSize="30" lineHeight="30" offsetTop="-10"><![CDATA[999]]></answer>
				<answer width="90" height="10" top="59" type="text" fontSize="30" lineHeight="30" offsetTop="-10"><![CDATA[7Eleven]]></answer>
				<answer width="90" height="10" top="71" type="text" fontSize="30" lineHeight="30" offsetTop="-10"><![CDATA[Sixteen]]></answer>
				<answer width="90" height="10" top="85" type="text" fontSize="30" lineHeight="30" offsetTop="-10" submit="true"><![CDATA[Done]]></answer>
			</answers>
			<inputs/>
			<explanation/>
		</portrait>
	</item>';
	
	}
	$strXmlQuestion .= '</questions>';
	# code...
	$xml = new SimpleXMLElement($strXmlQuestion);
	Header('Content-type: text/xml');
	print($xml->asXML());
	//$this->load->view('quiz_xml', $this->template_data);
	
	}


	function add(){
		check_permission('admin-add-quiz');
		$schoolinfo = getSchoolInfo();
		$subjectinfo = $this->db->get('esubjects')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$quiz_type_info = $this->db->get('content_type')->result();
		$this->template_data['quiz_type_info'] = $quiz_type_info;

		$this->load->view('quiz_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-quiz');
		$id = intval($this->input->get('id'));

		$this->db->where('quiz_id', $id);
		$info = $this->db->get('quiz')->row();
		$this->template_data['info'] = $info;

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$subjectinfo = $this->db->get('esubjects')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('quiz_edit', $this->template_data);
	}


function save(){
	
  	$id = intval($this->input->post('id'));	
  	$campusid = $this->session->userdata('member_campusid');
  	$date = date('Y-m-d H:i:s');

  	$start_datetime = $this->input->post('start_datetime');
	
	$expire_datetime = $this->input->post('expire_datetime');
	
  	header('Content-Type: application/json');
	$config['upload_path']   = './worksheets/';
	$config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|odt|odp|pdf|rtf|sxc|sxi|txt|exe|avi|mpeg|mp3|mp4|3gp";  
	$config['max_size']   = 1024;
	$this->load->library('upload', $config);

	$this->upload->initialize($config);
	$this->upload->do_upload('quiz_image');  // File Name
	$quizImg = $this->upload->data();
	
	if($quizImg['file_name']){
  	 	$quizImgName = $quizImg['file_name'];
	}else{
		$quizImgName = $this->input->post('quiz_image');
	}
 
		if($id === 0){
			check_permission('admin-add-quiz');
			$this->db->trans_begin();
			
			$data = array(
				'class_sub_id' => trim($this->input->post('subject_id')),
				'quiz_name' => trim($this->input->post('quiz_name')),
				'quiz_image' => $quizImgName,
				'start_datetime' => trim($start_datetime),
				'expire_datetime' => trim($expire_datetime),
				'campus_id' => $campusid,
				'created_date' => $date,
				);
			
			$this->db->insert('quiz', $data);
			$new_question_id = $this->db->insert_id();
			
			$this->db->trans_complete();
			$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Quiz Success')));
		}else{
			check_permission('admin-edit-quiz');
			$this->db->trans_begin();
				$data = array(
				'class_sub_id' => trim($this->input->post('subject_id')),
				'quiz_name' => trim($this->input->post('quiz_name')),
				'quiz_image' => $quizImgName,
				'start_datetime' => trim($start_datetime),
				'expire_datetime' => trim($expire_datetime),
				'campus_id' => $campusid,
				'updated_date' => $date,
			   );
			
			$this->db->where('quiz_id', $id);
			$this->db->update('quiz', $data); 
		
			$this->db->trans_complete();
			json_response(array('success' => TRUE, 'msg' => 'Edit  Quiz Success'));
		}
	}

	function delete(){
		check_permission('admin-del-quiz');
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