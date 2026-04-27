<?php
namespace App\Controllers\Admin;


/**
 * Topic Skills Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Topic_skills extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-topic-skills');
	}
	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('topic_skills', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.topic_skills_id) as ccount', FALSE);
		$this->db->from('topic_skills A');
		if($keyword){
			$this->db->where('(A.topic_skill=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('topic_skills A');
		if($keyword){
			$this->db->where('(A.topic_skill=' . $this->db->escape($keyword) .  ')');
		}
		$this->db->order_by('A.topic_skills_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$sub_cat_id = 0;
			$e_sub_id = 0;
			$subjectName = '';
			$catName = '';
			$topicName = '';
			$this->db->where('sub_cat_topic_id', $row->sub_cat_topic_id);
			$topicinfo = $this->db->get('esub_cat_topic')->row();
			if($topicinfo){
				$sub_cat_id = $topicinfo->sub_cat_id;
				$topicName = $topicinfo->topic;
			}

			$this->db->where('sub_cat_id', $sub_cat_id);
			$sub_cat_info = $this->db->get('ecategories')->row();
			//print_r($sub_cat_info);
		
			// $this->db->where('cs_id', $sub_cat_info->class_sub_id);
			// $classsubjectinfo = $this->db->get('class_subjects')->row();	
			if($sub_cat_info){
				$e_sub_id = $sub_cat_info->e_sub_id; 
				$catName = $sub_cat_info->cat_title;
			}
			$this->db->where('sub_id', $e_sub_id);
			$subjectinfo = $this->db->get('esubjects')->row();
			if($subjectinfo){
				$subjectName = $subjectinfo->subject;
			}
		
			$data = array();
			$data['id'] = $row->topic_skills_id;
			$data['class'] = '';//$classinfo->class_name;
			$data['subject'] = $subjectName;
			$data['cat_name'] = $catName;
			$data['topic'] = $topicName;
			$data['topic_skill'] = $row->topic_skill; 
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-topic-skill');		
		// $classesinfo = $this->db->get('classes')->result();
		// $this->template_data['classesinfo'] = $classesinfo;

		$subjectinfo = $this->db->get('esubjects')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('topic_skills_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-topic-skill');
		$id = intval($this->input->get('id'));
		
		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$subjectinfo = $this->db->get('esubjects')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;
		
		$this->db->where('sub_cat_id', $id);
		$sub_category_info = $this->db->get('ecategories')->row();
		$this->template_data['sub_category_info'] = $sub_category_info;
		
		$this->db->where('sub_cat_topic_id', $id);
		$info = $this->db->get('esub_cat_topic')->row();
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
				check_permission('admin-add-topic-skill');
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
				json_response(array('success' => TRUE, 'msg' => 'Add Topic Skill Success'));

			}else{
				check_permission('admin-edit-topic-skill');
				$this->db->trans_begin();

				$data = array(
					'sub_cat_topic_id' => trim($this->input->post('topic_id')),
					'topic_skill' => trim($this->input->post('topic_skill')),
					'detail' => trim($this->input->post('detail')),
					);
				$this->db->where('topic_skills_id', $id);
				$this->db->update('topic_skills', $data);
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Topic Skill Success'));
			}

		}
	}

	function delete(){
		check_permission('admin-del-topic-skill');
		$id = intval($this->input->get('id'));
		$this->db->trans_begin();
		// delete user
		$this->db->where('topic_skills_id', $id);
		$this->db->delete('topic_skills');
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Topic Skill Success'));
	}
}
// end this file