<?php
namespace App\Controllers\Admin;



/**

 * Audio Lecture Manage

 *

 * @author		Maqsood Ahmed

 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions

 * @email		maqsoodjamvi@gmail.com

 * @filesource

 */





class Audio_lecture extends MY_Controller {



	function __construct(){

		parent::__construct();

		check_permission('admin-audio-lecture');

	}



	/**

	 * Index Page for this controller.

	 */

	public function index()

	{

		$this->load->view('audio_lecture', $this->template_data);

	}



	function data(){

		

		$response = new stdClass;

		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');

		$keyword = '';

		if($search) $keyword = $search['value'];

		$this->db->select('count(A.question_id) as ccount', FALSE);

		$this->db->from('question_bank_gk A');

		if($keyword){

			$this->db->where('(A.short_title=' . $this->db->escape($keyword) .  ')');

		}

		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;



		$this->db->select('A.*');

		$this->db->from('question_bank_gk A');

		if($keyword){

			$this->db->where('(A.short_title=' . $this->db->escape($keyword) .  ')');

		}

		$this->db->order_by('A.question_id', 'desc');

		$this->db->limit($this->input->post('length'), $this->input->post('start'));

		$results = $this->db->get()->result();



		$response->recordsFiltered = $response->recordsTotal;

		

		$response->data = array();

		foreach($results as $row){

		

		$this->db->where('class_id', $row->class_id);

		$class_info = $this->db->get('classes')->row();		

		

			$data = array();

			$data['id'] = $row->question_id;

			$data['class_name'] = $class_info->class_name;

			$data['subject_id'] = $row->subject_id;

			$data['question_eng'] = $row->q_text_eng;

			$data['question_ur'] = $row->q_text_urdu;

			$response->data[] = $data;

		}



		$this->output->set_output(json_encode($response));

	}



	function add(){

		check_permission('admin-add-audio-lecture');



		$topic_skills_id = $_GET['topic_skill_id'];

		if(isset($topic_skills_id)){

		$this->db->where('topic_skills_id', $topic_skills_id);

		$topic_skills_info = $this->db->get('topic_skills')->result();

		$this->template_data['topic_skills_info'] = $topic_skills_info;



		$this->db->where('sub_cat_topic_id', $topic_skills_info[0]->sub_cat_topic_id);

		$topicinfo = $this->db->get('sub_cat_topic')->result();

		$this->template_data['topicinfo'] = $topicinfo;



		$this->db->where('sub_cat_id', $topicinfo[0]->sub_cat_id);

		$sub_category_info = $this->db->get('sub_category')->result();

		$this->template_data['sub_category_info'] = $sub_category_info;



		$this->db->where('cs_id', $sub_category_info[0]->class_sub_id);

		$classsubjectinfo = $this->db->get('class_subjects')->result();	

		$this->template_data['classsubjectinfo'] = $classsubjectinfo;

	

		$this->db->where('sid', $classsubjectinfo[0]->subject_id);

		$subjectinfo = $this->db->get('allsubject')->result();

		$this->template_data['subjectinfo'] = $subjectinfo;



		$this->db->where('class_id', $classsubjectinfo[0]->class_id);

		$classesinfo = $this->db->get('classes')->result();

		$this->template_data['classesinfo'] = $classesinfo;

	

		}else{

		$classesinfo = $this->db->get('classes')->result();

		$this->template_data['classesinfo'] = $classesinfo;

	   }

		$quiz_type_info = $this->db->get('content_type')->result();

		$this->template_data['quiz_type_info'] = $quiz_type_info;



		$this->load->view('audio_lecture_edit', $this->template_data);

	}



	function edit(){

		check_permission('admin-edit-audio-lecture');

		$id = intval($this->input->get('id'));



		$this->db->where('did', $id);

		$info = $this->db->get('classdairy')->row();

		$this->template_data['info'] = $info;



		$classesinfo = $this->db->get('classes')->result();

		$this->template_data['classesinfo'] = $classesinfo;



		$subjectinfo = $this->db->get('allsubject')->result();

		$this->template_data['subjectinfo'] = $subjectinfo;



		$this->load->view('audio_lecture_edit', $this->template_data);

	}





	function save(){

	  $id = intval($this->input->post('id'));	  

	  header('Content-Type: application/json');

	  	

		{

			if($id === 0){

				check_permission('admin-add-audio-lecture');

				$this->db->trans_begin();

				$questioncount = $this->input->post('questioncount');

				for($i=0; $i < count($questioncount); $i++){

					$data = array(

						'topic_skills_id' => trim($this->input->post('topic_skill_id')),

						'content_type_id' => trim($this->input->post('content_type_id')),

						'document_url' => trim($this->input->post('document_url'.$i)),

						'created_date' => trim(date('Y-m-d')),

					);

				

				

				$this->db->insert('skill_document', $data);

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

				$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Audio Lecture Success')));

			

			}else{

				check_permission('admin-edit-user');

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

				json_response(array('success' => TRUE, 'msg' => 'Edit Audio Lecture Success'));

			}



		}

	}



	function delete(){

		check_permission('admin-del-audio-lecture');

		$id = intval($this->input->get('id'));



		$this->db->trans_begin();



		// delete user

		$this->db->where('id', $id);

		$this->db->delete('classes');



		$this->db->trans_complete();

		json_response(array('success' => TRUE, 'msg' => 'Delete Audio Lecture Success'));

	}





}

// end this file

