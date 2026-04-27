<?php
namespace App\Controllers\Admin;


/**
 * Quiz Questions Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Quiz_questions extends MY_Controller {
	
	function __construct(){
		parent::__construct();
		check_permission('admin-quiz-questions');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$getid = $this->input->get('id');
		$this->template_data['getid'] = $getid;
		$this->load->view('quiz_questions', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$getid = $_GET['id'];
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.quiz_question_id) as ccount', FALSE);
		$this->db->from('quiz_questions A');
		if($getid){
			$this->db->where('(A.quiz_id='.$this->db->escape($getid).')');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->where('quiz_id', $getid);
		$results = $this->db->get('quiz_questions')->result();
		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
		$this->db->where('quiz_id', $row->quiz_id);
		$quiz_info = $this->db->get('quiz')->row();	
		
		$this->db->where('question_id', $row->question_id);
		$question_bank_info = $this->db->get('question_bank')->row();		
		//print_r($row);
		$data = array();

		$data['id'] = $row->quiz_question_id;
		$data['quiz_name'] = $quiz_info->quiz_name;
		$data['question_text'] = $row->question_id;
		$response->data[] = $data;
		}
		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-quiz-questions');
		
		$topicinfo = $this->db->get('esub_cat_topic')->result();
		$this->template_data['topicinfo'] = $topicinfo;
	
		$sub_category_info = $this->db->get('ecategories')->result();
		$this->template_data['sub_category_info'] = $sub_category_info;
		
		$subjectinfo = $this->db->get('esubjects')->result();	
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('quiz_questions_edit', $this->template_data);
	}

	function selectQuestion(){

		$subject_id = intval($this->input->post('subject_id'));	
		$quiz_id = intval($this->input->post('quiz_id'));	
		$subjectinfo = $this->db->query('SELECT * FROM contents WHERE topic_id IN(SELECT sub_cat_topic_id FROM esub_cat_topic WHERE sub_cat_id IN(SELECT sub_id FROM esubjects WHERE sub_id='.$subject_id.'))')->result();
		
		$question = '';
		foreach ($subjectinfo as $key => $value) {

			$this->db->where('content_id', $value->content_id);
			$this->db->where('quiz_id', $quiz_id);
			$quizQuestionInfo = $this->db->get('quiz_questions')->row();
			
			if($quizQuestionInfo){
				$quizQuestionID = $quizQuestionInfo->content_id;
			}else{
				$quizQuestionID = '';
			}

			if($value->question_type == 'text'){
				$question .= "<input name='content_id[]' ";
				if(!empty($quizQuestionID)){
					$question .= 'checked';
				}
				$question .= " value='".$value->content_id."' type='checkbox' > ".$value->question."<br>";
			}

			if($value->question_type == 'image'){
				$question .= '<input value="'.$value->content_id.'" name="content_id[]" type="checkbox"';
				if(!empty($quizQuestionID)){
					$question .= 'checked';
				}
				$question .=  ' value="'.$value->content_id.'" > <img src="worksheets/'.$value->question_image.'"><br>';
			}

			if($value->question_type == 'video'){
				$question .= "<input name='content_id[]'";
				if(!empty($quizQuestionID)){
					$question .= 'checked';
				}
				$question .= " value='".$value->content_id."' type='checkbox' > ".$value->question." ".$value->video_url."<br>";
			}
		}
		echo $question;
		
	}

	function save(){
	  $quiz_questions_id = 0;
	  $user_id = $this->session->userdata['member_userid'];
	  $date = date('Y-m-d H:i:s');
	  $quizid = $this->input->post('id');
	 
	  $content_ids = $this->input->post('content_id');	
	  check_permission('admin-add-quiz-questions');
			
			$this->db->trans_begin();
			foreach ($content_ids as  $content_id) {
				
				$this->db->where('content_id', $content_id);
				$this->db->where('quiz_id', $quizid);
				$quizQuestionInfo = $this->db->get('quiz_questions')->row();

				$data = array(
					'quiz_id' => $quizid,
					'content_id' => $content_id,	
					'user_id' => $user_id,
					'created_date' => $date
				);

				if($quizQuestionInfo){
					$this->db->where('content_id', $content_id);
					$this->db->update('quiz_questions', $data);
				}else{
					$this->db->insert('quiz_questions', $data);
					//$quiz_question_id = $this->db->insert_id();  	
				}
			}

			$this->db->trans_complete();
			$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Quiz Question Added')));
		}

}

// end this file

