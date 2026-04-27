<?php
namespace App\Controllers\Admin;


/**
 * Topic Skills View Buttons Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Topic_skills_view_buttons extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-topic-skills-view-buttons');
	}
	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$data = $this->data();
		$this->template_data['data'] = $data;
		$this->load->view('topic_skills_btns', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		$topic_id = $_GET['topic_id'];
		if($search) $keyword = $search['value'];
		// $this->session->set_userdata('search', $search);
		// $perpage = 10;
		$this->db->select('count(A.sub_cat_topic_id) as ccount', FALSE);
		$this->db->from('esub_cat_topic A');
		if($topic_id){
			$this->db->where('(A.sub_cat_topic_id=' . $this->db->escape($topic_id) .  ')');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		// $offset = $response->draw * $perpage;

		$this->db->select('A.*');
		$this->db->from('esub_cat_topic A');
		if($topic_id){
			$this->db->where('(A.sub_cat_topic_id=' . $this->db->escape($topic_id) .  ')');
		}
		$this->db->order_by('A.sub_cat_topic_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		foreach($results as $row){
			
		
			$this->db->where('sub_cat_id', $row->sub_cat_id);
			$sub_cat_info = $this->db->get('ecategories')->row();


			$this->db->where('sub_id', $sub_cat_info->e_sub_id);
			$subjectinfo = $this->db->get('esubjects')->row();	


			$data = array(
			'id' => $row->sub_cat_topic_id,
			'subject' => $subjectinfo->subject,
			'cat_name' => $sub_cat_info->cat_title,
			'topic' => $row->topic,
			
			);

		}

		return $data;

		//$this->output->set_output(json_encode($response));

	}

}

// end this file

