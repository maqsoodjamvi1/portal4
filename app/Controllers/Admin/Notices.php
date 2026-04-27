<?php
namespace App\Controllers\Admin;


/**
 * Classes Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Notices extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-notices');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('notices', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.notice_id) as ccount', FALSE);
		$this->db->where('(A.campus_id=' . $this->db->escape($campusid) .  ')');
		$this->db->from('notices A');
		if($keyword){
			$this->db->where('(A.notice_name=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

	
		$this->db->select('A.*');
		$this->db->from('notices A');
		$this->db->where('(A.campus_id=' . $this->db->escape($campusid) .  ')');
		if($keyword){
			$this->db->where('(A.notice_name=' . $this->db->escape($keyword)  . ')');
		}
		$this->db->order_by('A.notice_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->notice_id;
			$data['notice_name'] = $row->notice_name;
			$data['notice_date'] = $row->notice_date;
			$data['notice_detail'] = $row->notice_detail;
			$data['notice_audio'] = $row->notice_audio;
			$data['status'] = $row->status;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-notices');
		$this->load->view('notices_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-notices');
		$id = intval($this->input->get('id'));

		$this->db->where('notice_id', $id);
		$info = $this->db->get('notices')->row();
		$this->template_data['info'] = $info;
		$this->load->view('notices_edit', $this->template_data);
	}

	function save(){
		//print_r($_POST);
		//exit;
		$notice_audio =  ''; //array();
		$campus_id = $this->session->userdata('member_campusid');
		header('Content-Type: application/json');
	  	$config['upload_path']   = './noticesaudios/';
	  	$config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|ogv|odt|odp|pdf|rtf|sxc|sxi|txt|exe|wav|avi|mpeg|mp3|mp4|3gp";  
	  	$config['max_size']   = 1024;
	  	$this->load->library('upload', $config);

		$id = intval($this->input->post('id'));
		$this->form_validation->set_rules('notice_name', 'Notice Name', 'trim|required');
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			if($id === 0){
				check_permission('admin-add-notices');
				$this->db->trans_begin();
				$status = 0;
				if($this->input->post('status')){
					$status = $this->input->post('status');
				}

				$this->upload->initialize($config);
				$this->upload->do_upload('notice_audio');  // File Name
	 			$notice_audio = $this->upload->data(); 
	  			$notice_audio = $notice_audio['file_name']; 
	  			

				$data = array(
					'notice_name' => trim($this->input->post('notice_name')),
					'notice_date' => trim($this->input->post('notice_date')),
					'notice_detail' => trim($this->input->post('notice_detail')),
					'notice_audio' => $notice_audio,
					'status' => $status,
					'campus_id' => $campus_id,
				);
				$this->db->insert('notices', $data);
				$new_user_id = $this->db->insert_id();

				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Add Notice Success'));
			}else{
				check_permission('admin-edit-notices');
				$this->db->trans_begin();
				$status = 0;
				if($this->input->post('status')){
					$status = $this->input->post('status');
				}

				$this->upload->initialize($config);
	 			$this->upload->do_upload('notice_audio');  // File Name
	 			$notice_audio = $this->upload->data(); 
	  			$notice_audio = $notice_audio['file_name'];  

				$data = array(
					'notice_name' => trim($this->input->post('notice_name')),
					'notice_date' => trim($this->input->post('notice_date')),
					'notice_detail' => trim($this->input->post('notice_detail')),
					'notice_audio' => $notice_audio,
					'status' => $status,
					'campus_id' => $campus_id,
				);


				$this->db->where('notice_id', $id);
				$this->db->update('notices', $data);
				
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Notice Success'));
			}

		}
	}

	function delete(){
		check_permission('admin-del-notices');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('notice_id', $id);
		$this->db->delete('notices');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Notice Success'));
	}

}
// end this file
