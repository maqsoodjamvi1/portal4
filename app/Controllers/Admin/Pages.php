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


class Pages extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-pages');
	}

	/**
	 * Index Page for this controller.
	 */
	 
	public function index()
	{
		$this->load->view('pages', $this->template_data);
	}

	function data(){
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.page_id) as ccount', FALSE);
		$this->db->from('sys_pages A');
		$this->db->where('(A.system_id=' . $this->db->escape($schoolinfo->system_id) .  ')');
		if($keyword){
			$this->db->where('(A.title=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;


		$this->db->select('A.*');
		$this->db->from('sys_pages A');
		$this->db->where('(A.system_id=' . $this->db->escape($schoolinfo->system_id) .  ')');
		if($keyword){
			$this->db->where('(A.title=' . $this->db->escape($keyword) .  ')');
		}
		$this->db->order_by('A.page_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		
		
		$response->data = array();
		foreach($results as $row){
			
						
			$data = array();
			$data['id'] = $row->page_id;
			$data['title'] = $row->title;
			$data['content'] = $row->content;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-page');
		$schoolinfo = getSchoolInfo();
		$this->load->view('page_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-page');
		$id = intval($this->input->get('id'));

		$this->db->where('page_id', $id);
		$info = $this->db->get('sys_pages')->row();
		$this->template_data['info'] = $info;

		$this->load->view('page_edit', $this->template_data);
	}


function save(){

  	$id = intval($this->input->post('id'));	
  	$schoolinfo = getSchoolInfo();
  	$campusid = $this->session->userdata('member_campusid');
  	$date = date('Y-m-d H:i:s');
 

 		if($id === 0){
			check_permission('admin-add-page');
			$this->db->trans_begin();
				
			$data = array(
				'title' => trim($this->input->post('title')),
				'content' => trim($this->input->post('content')),
				'system_id' => $schoolinfo->system_id,
				'created_date' => $date,
			);
			
			$this->db->insert('sys_pages', $data);
			$new_question_id = $this->db->insert_id();
		
			$this->db->trans_complete();
			$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Add Page Success')));
		}else{
			check_permission('admin-edit-page');
			$this->db->trans_begin();
				$data = array(
				'title' => trim($this->input->post('title')),
				'content' => trim($this->input->post('content')),
				'updated_date' => $date,
			   );
		
			$this->db->where('page_id', $id);
			$this->db->update('sys_pages', $data); 
			$this->db->trans_complete();
			json_response(array('success' => TRUE, 'msg' => 'Edit Page Success'));
		}
	}

	function delete(){
		check_permission('admin-del-page');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

		// delete user
		$this->db->where('id', $id);
		$this->db->delete('sys_pages');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Page Success'));
	}


}
// end this file