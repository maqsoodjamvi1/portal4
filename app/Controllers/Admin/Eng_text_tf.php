<?php
namespace App\Controllers\Admin;


/**
 * English Text Fill In The Blanks Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutuins
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Eng_text_tf extends MY_Controller {
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
		$this->load->view('eng_text_tf', $this->template_data);
	}
	
	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';

		$getid = $_GET['topic_id'];
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.content_id) as ccount', FALSE);
		$this->db->from('contents A');

		if($getid){
			$this->db->where('(A.topic_id='.$this->db->escape($getid).')');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('contents A');
		if($getid){
			$this->db->where('(A.topic_id=' . $this->db->escape($getid) .  ')');
			$this->db->where('(A.cat_type_id=3)');
		}

		$this->db->order_by('A.content_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;	
		$response->data = array();
		$data =  array();
		foreach($results as $row){

		
		$this->db->where('sub_cat_topic_id', $topic_skills_info->sub_cat_topic_id);
		$sub_cat_topic_info = $this->db->get('esub_cat_topic')->row();	
		
		if($sub_cat_topic_info){
		$this->db->where('sub_cat_id', $sub_cat_topic_info->sub_cat_id);
		$sub_category_info = $this->db->get('ecategories')->row();		
		}else{
		$sub_category_info = '';
		}	
		if($sub_category_info){
		$this->db->where('sub_id', $sub_category_info->e_sub_id);
		$subjects_info = $this->db->get('esubjects')->row();		
		}else{
		$subjects_info = '';
	}

	
	$this->db->where('question_id', $row->content_id);
	$questionoptions_info = $this->db->get('question_options')->result();	
	$questionOptions = array();
	
	foreach ($questionoptions_info as  $question_option) {
		$questionOptions[] = $question_option->option_text;	
	}

	$data[] = array(
		'id' => $row->content_id,
		'subject' => $subjects_info->subject,
		'subject_category' => $sub_category_info->cat_title,
		'topic' => $sub_cat_topic_info->topic,
		'question_eng' => $row->question_text,
		'question_ur' => $row->question_text,
		'questionOptions' => $questionOptions
	);

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

		$this->load->view('eng_text_tf_edit', $this->template_data);

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
		$this->load->view('eng_text_tf_edit', $this->template_data);

	}


function save(){
	  $id = intval($this->input->post('id'));
		if($id === 0){
			check_permission('admin-add-question');
			$this->db->trans_begin();
			$optionscount = $this->input->post('optionscount');

			for($i=0; $i < count($optionscount); $i++){
				$data = array(
					'topic_skills_id' => trim($this->input->post('topic_skill_id')),
					'content_type_id' => trim($this->input->post('content_type_id')),
					'question_text' => trim($this->input->post('question_text'.$i)),
					'hint_text' => trim($this->input->post('hint_text'.$i)),
				);
				
				$this->db->insert('question_bank', $data);
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

				$is_correct = 0;
				$nCount = 0;
				for($j=0; $j<1; $j++){	
				    if($nCount == 0){
				    	$is_correct = 1;
				    }else{
				    	$is_correct = 0;
				    }

				$optionvalue = $this->input->post('option'.$i);   
				if($optionvalue == 1){
					$option_text = 'True';	
				}else{
					$option_text = 'False';	
				}  
				    
				$optionsdata = array(
					'question_id' => $new_question_id,
					'is_correct' => $is_correct,
					'option_text' => trim($option_text),
				);

				$this->db->insert('question_options', $optionsdata);
				$new_user_id = $this->db->insert_id();
				$nCount++;
		}
				
	}
	$this->db->trans_complete();
	$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Question Success')));
}

}

function delete(){
	check_permission('admin-del-question');
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