<?php
namespace App\Controllers\Admin;


/**
 * Question Bank Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */

class Question_bank_gk_ur extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-questions');
	}

	/**
	 * Index Page for this controller.
	*/

	public function index()
	{	
		$data = $this->data();
		$this->template_data['data'] = $data;
		$this->load->view('Question_bank_gk_ur', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		$getid = $_GET['topic_skill_id'];
		if($search) $keyword = $search['value'];

		$this->db->select('count(A.content_id) as ccount', FALSE);
		$this->db->from('question_bank A');

		if($getid){
			$this->db->where('(A.topic_skills_id='.$this->db->escape($getid).')');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('question_bank A');
		if($getid){
			$this->db->where('(A.topic_skills_id=' . $this->db->escape($getid) .  ')');
			$this->db->where('(A.content_type_id=15)');
		}

		$this->db->order_by('A.content_id', 'desc');
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

		$this->db->where('question_id', $row->content_id);
		$questionoptions_info = $this->db->get('question_options')->result();	
		$questionOptions = array();

		foreach ($questionoptions_info as  $question_option) {
			$questionOptions[] = $question_option->option_text;	
		}

		$data[] = array(
			'id' => $row->content_id,
			'class_name' => $class_name,
			'subject' => $subject_name,
			'subject_category' => $sub_category_info->cat_name,
			'topic' => $sub_cat_topic_info->topic,
			'topic_skills' => $topic_skills_info->topic_skill,
			'question_eng' => $row->question_text,
			'question_ur' => $row->question_text,
			'questionOptions' => $questionOptions
		);
		$response->data[] = $data;
	}
	return $data;
}

function add(){
	check_permission('admin-add-question');
	$topic_id = $_GET['topic_id'];
	
	if(isset($topic_id)){
		
		$this->db->where('sub_cat_topic_id', $topic_id);
		$topicinfo = $this->db->get('esub_cat_topic')->result();
		$this->template_data['topicinfo'] = $topicinfo;

		$this->db->where('sub_cat_id', $topicinfo[0]->sub_cat_id);
		$sub_category_info = $this->db->get('ecategories')->result();
		$this->template_data['sub_category_info'] = $sub_category_info;

		$this->db->where('sub_id', $sub_category_info[0]->e_sub_id);
		$subjectinfo = $this->db->get('esubjects')->result();	
		$this->template_data['subjectinfo'] = $subjectinfo;

	
	}

	$quiz_type_info = $this->db->get('content_type')->result();
	$this->template_data['quiz_type_info'] = $quiz_type_info;
	$this->load->view('question_bank_gk_ur_edit', $this->template_data);
}

function save(){
	  $id = intval($this->input->post('id'));	  
	  header('Content-Type: application/json');

		if($id === 0){
			check_permission('admin-add-question');
			$this->db->trans_begin();
			$questioncount = $this->input->post('questioncount');
			for($i=0; $i < count($questioncount); $i++){

			$data = array(
				'topic_skills_id' => trim($this->input->post('topic_skill_id')),
				'content_type_id' => trim($this->input->post('content_type_id')),
				'question_text' => trim($this->input->post('question_text'.$i)),
				'answer_text' => trim($this->input->post('answer_text'.$i)) ,
			);

			$this->db->insert('learning_question_bank', $data);
			$new_question_id = $this->db->insert_id();

			$data2 = array(
				'class_id' => trim($this->input->post('class_id')),
				's_id' => trim($this->input->post('subject_id')),
				'sub_cat_id' => trim($this->input->post('cat_id')),
				'sub_cat_topic_id' => trim($this->input->post('topic_id')),
				'topic_skills_id' => trim($this->input->post('topic_skill_id')),
				'content_type_id' => trim($this->input->post('content_type_id')),
				'content_id' => trim($new_question_id),
				'diff_level' => 0,
				'temp_id' => 0,
				'quiz_Learning' => 0
			);

			$this->db->insert('quiz_indexing', $data2);	
	}

	$this->db->trans_complete();

	$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Learning Question Success')));
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
			json_response(array('success' => TRUE, 'msg' => 'Edit Class Dairy Success'));
		}
	}
}
// end this file
