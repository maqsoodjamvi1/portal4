<?php
namespace App\Controllers\Admin;


/**
 * Add Quiz Questions Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */

class Quiz_questions_add extends MY_Controller {
	
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
		$this->load->view('quiz_questions_add', $this->template_data);
	}

	function data(){ 
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		$quiz_id = $_GET['id'];

		$this->db->where('quiz_id', 'quiz_id');
		$quizinfo = $this->db->get('quiz')->result();

		$this->db->select('count(A.sub_cat_topic_id) as ccount', FALSE);
		$this->db->from('esub_cat_topic A');

		if($keyword){
			$this->db->where('(A.topic =' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('esub_cat_topic A');
		if($keyword){
			$this->db->where('(A.topic =' . $this->db->escape($keyword) .  ')');
		}
		$this->db->order_by('A.sub_cat_topic_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();

		foreach($results as $row){
			
			$sub_cat_info = '';
			$topicinfo = '';
			$cat_title = '';
			$subject = '';
			$topic = '';

			$this->db->where('sub_cat_topic_id', $row->sub_cat_topic_id);
			$topicinfo = $this->db->get('esub_cat_topic')->row();

			if($topicinfo){
				$topic = $topicinfo->topic; 
				$this->db->where('sub_cat_id', $topicinfo->sub_cat_id);
				$sub_cat_info = $this->db->get('ecategories')->row();
			}
			
			if($sub_cat_info){
				$cat_title = $sub_cat_info->cat_title;
				$this->db->where('sub_id', $sub_cat_info->e_sub_id);
				$subjectinfo = $this->db->get('esubjects')->row();
			}

			if($subjectinfo){
				$subject = $subjectinfo->subject;
			}

			$data = array();
			$data['id'] = $row->sub_cat_topic_id;
			$data['class'] = '';//$classinfo->class_name;
			$data['subject'] = $subject;
			$data['cat_name'] = $cat_title;
			$data['topic'] = $topic;
			$data['topic_skill'] = $row->topic; 
			//$data['detail'] = $row->detail;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}



	function add(){

		check_permission('admin-add-quiz-question');

		

		$classesinfo = $this->db->get('classes')->result();

		$this->template_data['classesinfo'] = $classesinfo;



		$this->db->where('subject_type', 'academic');

		$subjectinfo = $this->db->get('allsubject')->result();

		$this->template_data['subjectinfo'] = $subjectinfo;



		$this->load->view('topic_skills_edit', $this->template_data);

	}



	function edit(){

		check_permission('admin-edit-quiz-question');

		$id = intval($this->input->get('id'));

		

		$classesinfo = $this->db->get('classes')->result();

		$this->template_data['classesinfo'] = $classesinfo;



		$this->db->where('subject_type', 'academic');

		$subjectinfo = $this->db->get('allsubject')->result();

		$this->template_data['subjectinfo'] = $subjectinfo;

		

		$this->db->where('sub_cat_id', $id);

		$sub_category_info = $this->db->get('sub_category')->row();

		$this->template_data['sub_category_info'] = $sub_category_info;

		

		$this->db->where('sub_cat_topic_id', $id);

		$info = $this->db->get('sub_cat_topic')->row();

		$this->template_data['info'] = $info;

		

		$this->load->view('topic_skills_edit', $this->template_data);

	}







	function save(){

		$id = intval($this->input->post('id'));

		$rowscount = $this->input->post('rowscount');



		$this->form_validation->set_rules('topic_skill0', 'Name', 'trim|required');

		if($this->form_validation->run() === FALSE){

			json_response(array('success' => FALSE, 'msg' => validation_errors()));

		}else{

			if($id === 0){

				check_permission('admin-add-quiz-question');

				$this->db->trans_begin();

				for($i=0; $i < count($rowscount); $i++){



					$detail = $this->input->post('detail'.$i);

					$topic_name = $this->input->post('topic_skill'.$i);

					



					$data = array(

						'sub_cat_topic_id' => trim($this->input->post('topic_id')),

						'topic_skill' => trim($topic_name),

						'detail' => trim($detail),

						);

					$this->db->insert('topic_skills', $data);

					$topic_skills_id = $this->db->insert_id();



					$data2 = array(

						'class_id' => trim($this->input->post('class_id')),

						's_id' => trim($this->input->post('subject_id')),

						'sub_cat_id' => trim($this->input->post('cat_id')),

						'sub_cat_topic_id' => trim($this->input->post('topic_id')),

						'topic_skills_id' => trim($topic_skills_id),

						);

					$this->db->insert('quiz_indexing', $data2);	



				}

			

				$this->db->trans_complete();

				json_response(array('success' => TRUE, 'msg' => 'Add Quiz Questions Success'));

			}else{

				check_permission('admin-edit-quiz-question');

				$this->db->trans_begin();

				$data = array(

					'sub_cat_topic_id' => trim($this->input->post('topic_id')),

					'topic_skill' => trim($this->input->post('topic_skill')),

					'detail' => trim($this->input->post('detail')),

					);

				$this->db->where('topic_skills_id', $id);

				$this->db->update('topic_skills', $data);

				

				$this->db->trans_complete();

				json_response(array('success' => TRUE, 'msg' => 'Edit Quiz Questions Success'));

			}



		}

	}



	function delete(){

		check_permission('admin-del-quiz-question');

		$id = intval($this->input->get('id'));



		$this->db->trans_begin();



		// delete user

		$this->db->where('id', $id);

		$this->db->delete('classes');



		$this->db->trans_complete();

		json_response(array('success' => TRUE, 'msg' => 'Delete Quiz Questions Success'));

	}



}

// end this file

