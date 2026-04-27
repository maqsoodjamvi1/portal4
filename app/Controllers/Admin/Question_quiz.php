<?php
namespace App\Controllers\Admin;


/**
 * Questions Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */

class Question_quiz extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-questions');
	}

	/**
	 * Index Page for this controller.
	*/

	public function index()
	{
		$this->load->view('question_quiz', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];

		$this->db->select('count(A.question_id) as ccount', FALSE);
		$this->db->from('question_bank A');

		if($keyword){
			$this->db->where('(A.short_title=' . $this->db->escape($keyword) .  ')');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('question_bank A');
		if($keyword){
			$this->db->where('(A.short_title=' . $this->db->escape($keyword) .  ')');
		}

		$this->db->order_by('A.question_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){

		$this->db->where('topic_skills_id', $row->topic_skills_id);
		$topic_skills_info = $this->db->get('topic_skills')->row();	

		if($topic_skills_info){
		$this->db->where('sub_cat_topic_id', $topic_skills_info->sub_cat_topic_id);
		$sub_cat_topic_info = $this->db->get('sub_cat_topic')->row();	
		}else{
		$sub_cat_topic_info = '';
		}

		if($sub_cat_topic_info){
		$this->db->where('sub_cat_id', $sub_cat_topic_info->sub_cat_id);
		$sub_category_info = $this->db->get('sub_category')->row();		
		}else{
		$sub_category_info = '';
		}	

		if($sub_category_info){
		$this->db->where('cs_id', $sub_category_info->class_sub_id);
		$class_subjects_info = $this->db->get('class_subjects')->row();		
		}else{
		$class_subjects_info = '';
		}

		if($class_subjects_info){
			$this->db->where('class_id', $class_subjects_info->class_id);
			$class_info = $this->db->get('classes')->row();	

			$class_name = $class_info->class_name;
			$this->db->where('sid', $class_subjects_info->subject_id);

			$subject_info = $this->db->get('allsubject')->row();	
			$subject_name = $subject_info->subject_name;

		}else{
			$class_name = '';
			$subject_name = '';
		}

		$data = array();
		$data['id'] = $row->question_id;
		$data['class_name'] = $class_name;
		$data['subject'] = $subject_name;
		$data['question_eng'] = $row->question_text;
		$data['question_ur'] = $row->question_text_urdu;
		$response->data[] = $data;
	}
	$this->output->set_output(json_encode($response));
}

function add(){
	check_permission('admin-add-question');
	
	$topicinfo = $this->db->get('esub_cat_topic')->result();
	$this->template_data['topicinfo'] = $topicinfo;
	
	$sub_category_info = $this->db->get('ecategories')->result();
	$this->template_data['sub_category_info'] = $sub_category_info;
	
	$subjectinfo = $this->db->get('esubjects')->result();	
	$this->template_data['subjectinfo'] = $subjectinfo;

	$this->load->view('question_quiz_edit', $this->template_data);

}

function edit(){
	check_permission('admin-edit-question');
	$id = intval($this->input->get('id'));
	
	$this->db->where('did', $id);
	$info = $this->db->get('classdairy')->row();
	$this->template_data['info'] = $info;

	$classesinfo = $this->db->get('classes')->result();	
	$this->template_data['classesinfo'] = $classesinfo;

	$subjectinfo = $this->db->get('allsubject')->result();
	$this->template_data['subjectinfo'] = $subjectinfo;
	
	$this->load->view('question_quiz_edit', $this->template_data);
}

function save(){

	$id = intval($this->input->post('id'));
	header('Content-Type: application/json');
	$config['upload_path']   = './questionuploads/';

	  $config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|odt|odp|pdf|rtf|sxc|sxi|txt|exe|avi|mpeg|mp3|mp4|3gp";  

	  $config['max_size']   = 1024;

	  $this->load->library('upload', $config);

	 

	  $this->upload->do_upload('header_audio');

	  $header_audio = $this->upload->data();

	  $header_audio_name = $header_audio['file_name']; 

	  

	  $this->upload->initialize($config);

	  $this->upload->do_upload('question_audio');  // File Name

	  $question_audio = $this->upload->data(); 

	  $question_audio_name = $question_audio['file_name']; 



	  $this->upload->initialize($config);

	  $this->upload->do_upload('question_image');  // File Name

	  $question_image = $this->upload->data(); 

	  $question_image_name = $question_image['file_name']; 



	  $this->upload->initialize($config);

	  $this->upload->do_upload('hint_audio');  // File Name

	  $hint_audio = $this->upload->data(); 

	  $hint_audio_name = $hint_audio['file_name']; 

	 

	  $this->upload->initialize($config);

	  $this->upload->do_upload('hint_image');  // File Name

	  $hint_image = $this->upload->data(); 

	  $hint_image_name = $hint_image['file_name'];  	 

	

		{

			if($id === 0){

				check_permission('admin-add-question');

				$this->db->trans_begin();

				

				$data = array(

					'topic_skills_id' => trim($this->input->post('topic_skill_id')),

					'quiz_type_id' => trim($this->input->post('quiz_type')),

					'header_text' => trim($this->input->post('header_text')) ,

					'question_text' => trim($this->input->post('question_text')),

					'hint_text' => trim($this->input->post('hint_text')),

					'header_audio' => $header_audio_name,

					'question_audio' => $question_audio_name,

					'question_image' => $question_image_name,					

					'hint_audio' => $hint_audio_name,					

					'hint_image' => $hint_image_name,

				);

				

				$this->db->insert('question_bank', $data);

				$new_question_id = $this->db->insert_id();

				

				

				$optionscount = $this->input->post('optionscount');

				//$option_image = $this->input->post('option_image');

				

			for($i=0; $i < count($optionscount); $i++){

				  

				  $option_image = 'option_image'.$i;	

				  $this->upload->initialize($config);

	 			  $this->upload->do_upload($option_image);  // File Name

	 			  $option_image = $this->upload->data(); 

	  			  $option_image = $option_image['file_name'];  	 

	

 				  $this->upload->initialize($config);

	 			  $this->upload->do_upload('option_audio'.$i);  // File Name

	 			  $option_audio = $this->upload->data(); 

	  			  $option_audio = $option_audio['file_name'];  

			

				$optionsdata = array(

					'question_id' => $new_question_id,

					'is_correct' => trim($this->input->post('is_correct'.$i)),

					'option_text' => trim($this->input->post('option_text'.$i)),

					'option_image' =>  $option_image,

					'option_audio' => $option_audio,

				);

				

				//print_r($optionsdata);

				

				$this->db->insert('question_options', $optionsdata);

				$new_user_id = $this->db->insert_id();

				

				}

			

				$this->db->trans_complete();

				$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Question Success')));

			}else{

				check_permission('admin-edit-question');

				$this->db->trans_begin();

				$data = array(

					'date1' => trim($this->input->post('date1')),

					'class_id' => trim($this->input->post('class_id')),

					'subject_id' => trim($this->input->post('subject_id')),

					'short_title' => trim($this->input->post('short_title')),

					'detail' => trim($this->input->post('detail')),

					'type1' => trim($this->input->post('type1'))

				);

				$this->db->where('did', $id);

				$this->db->update('classdairy', $data);

				

				$this->db->trans_complete();

				json_response(array('success' => TRUE, 'msg' => 'Edit Question Quiz Success'));

			}



		}

	}



	function delete(){

		check_permission('admin-del-question');

		$id = intval($this->input->get('id'));



		$this->db->trans_begin();



		$this->db->where('id', $id);

		$this->db->delete('classes');



		$this->db->trans_complete();

		json_response(array('success' => TRUE, 'msg' => 'Delete Question Quiz Success'));

	}

}

// end this file