<?php
namespace App\Controllers\Admin;


/**
 * Academic Session Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2016~2099 TIME Soft Soltions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class First_academic_session extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-add-academic-session');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('first_academic_session_edit', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		// $this->session->set_userdata('search', $search);
		// $perpage = 10;
		$this->db->select('count(A.session_id) as ccount', FALSE);
		$this->db->from('academic_session A');
		$this->db->where('(A.system_id =' . $this->db->escape($schoolinfo->system_id) . ')');
		if($keyword){
			$this->db->where('(A.session_name=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		// $offset = $response->draw * $perpage;

		$this->db->select('A.*');
		$this->db->from('academic_session A');
		$this->db->where('(A.system_id =' . $this->db->escape($schoolinfo->system_id) . ')');
		if($keyword){
			$this->db->where('(A.session_name=' . $this->db->escape($keyword) .  ')');
		}
		$this->db->order_by('A.session_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->session_id;
			$data['session_name'] = $row->session_name;
			$data['start_date'] = $row->start_date;
			$data['end_date'] = $row->end_date;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-academic-session');
		$schoolinfo = getSchoolInfo();
		$academic_session = $this->db->query('SELECT * from academic_session where  system_id='.$schoolinfo->system_id.'  order by session_id desc')->row();
		
		$this->template_data['academic_session'] = $academic_session;
		
		$this->load->view('first_academic_session_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-academic-session');
		$session_id = intval($this->input->get('id'));
		$this->db->where('session_id', $session_id);
		$info = $this->db->get('academic_session')->row();
		$this->template_data['info'] = $info;
		$this->load->view('first_academic_session_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$schoolinfo = getSchoolInfo();

		$this->form_validation->set_rules('session_name', 'Session Name', 'trim|required');
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			if($id === 0){
				check_permission('admin-add-academic-session');
				$this->db->trans_begin();
				
				$data = array(
					'session_name' => trim($this->input->post('session_name')),
					'start_date' => trim($this->input->post('start_date')),
					'end_date' => trim($this->input->post('end_date')),
					'system_id' => $schoolinfo->system_id,
					'user_id' => $user_id,
					'created_date' => $date
				);
				
				$this->db->insert('academic_session', $data);
				$new_session_id = $this->db->insert_id();

				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Add Academic Session Success'));
			}else{
			    check_permission('admin-edit-academic-session');
				$this->db->trans_begin();
				$data = array(
					'session_name' => trim($this->input->post('session_name')),
					'start_date' => trim($this->input->post('start_date')),
					'end_date' => trim($this->input->post('end_date')),
					'user_id' => $user_id,
					'updated_date' => $date
				);
				$this->db->where('session_id', $id);
				$this->db->update('academic_session', $data);
				// User Roles
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Academic Session Success'));
			}

		}
	}

	function delete(){
		check_permission('admin-del-academic-session');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

		// delete class
		$this->db->where('id', $id);
		$this->db->delete('academic_session');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Academic Session Success'));
	}


}
// end this file
