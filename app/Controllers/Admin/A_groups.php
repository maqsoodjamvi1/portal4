<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Sections Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class A_groups extends BaseController {

	public function __construct(){
		check_permission('admin-sections');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('a_groups', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.group_id) as ccount', FALSE);
		$this->db->from('a_groups A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campusid) . ')');
		if($keyword){
			$this->db->where('(A.group_name=' . $this->db->escape($keyword) .  ')');
		}
		
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('a_groups A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campusid) . ')');
		if($keyword){
			$this->db->where('(A.group_name=' . $this->db->escape($keyword) .  ')');
		}
		
		$this->db->order_by('A.group_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			//print_r($row);
		
			$data = array();
			$data['id'] = $row->group_id;
			$data['group_name'] = $row->group_name;
			$data['short_name'] = $row->short_name;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-section');	
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('campus_id', $campusid);
		$info = $this->db->get('a_groups')->result();
		$this->template_data['info'] = $info;
 		
		$this->load->view('a_groups_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-section');
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('campus_id', $campusid);
		$info = $this->db->get('a_groups')->result();
		$this->template_data['info'] = $info;

		$this->load->view('a_groups_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();
		$rowscount = $this->input->post('rowscount');

			for($i=0; $i < count($rowscount); $i++){
				$id = $this->input->post('id'.$i);
				
				if($id == 0){
					$data = array(
					'group_name' => trim($this->input->post('group_name'.$i)),
					'short_name' => trim($this->input->post('short_name'.$i)),
					'campus_id' => $campusid,
					'user_id' => $user_id,
					'created_date' => $date
				);
				$this->db->insert('a_groups', $data);
				$new_user_id = $this->db->insert_id();

			}else{
					
				$data = array(
					'group_name' => trim($this->input->post('group_name'.$i)),
					'short_name' => trim($this->input->post('short_name'.$i)),
					'campus_id' => $campusid,
					'user_id' => $user_id,
					'created_date' => $date
				);
				$this->db->where('group_id', $id);
				$this->db->update('a_groups', $data);
			}
				
				$this->db->trans_complete();
			}
			
			json_response(array('success' => TRUE, 'msg' => 'Group updated success'));
			
	}

	function delete(){
		check_permission('admin-del-user');
		$id = intval($this->input->get('id'));
		$this->db->trans_begin();
		// delete user
		$this->db->where('group_id', $id);
		$this->db->delete('a_groups');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Group Success'));
	}
}
// end this file
