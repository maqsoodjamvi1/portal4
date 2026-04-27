<?php
namespace App\Controllers\Admin;



/**
 * Hostel Rooms Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class H_rooms extends MY_Controller {

	function __construct(){
		parent::__construct(); 
		check_permission('admin-rooms');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('h_rooms', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.room_id) as ccount', FALSE);
		$this->db->from('h_rooms A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.room_name=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

	
		$this->db->select('A.*');
		$this->db->from('h_rooms A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.room_name=' . $this->db->escape($keyword)  . ')');
		}
		$this->db->order_by('A.room_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->room_id;
			$data['room_name'] = $row->room_name;
			$data['status'] = $row->status;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-rooms');
		$campus_id = $this->session->userdata('member_campusid');

		$this->db->where('campus_id', $campus_id);
		$info = $this->db->get('h_rooms')->result();
		$this->template_data['info'] = $info;
		$this->load->view('h_rooms_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-rooms');
		$id = intval($this->input->get('id'));

		$this->db->where('room_id', $id);
		$info = $this->db->get('h_rooms')->row();
		$this->template_data['info'] = $info;
		$this->load->view('h_rooms_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		$campus_id = $this->session->userdata('member_campusid');
		$rowscount = $this->input->post('rowscount');

			for($i=0; $i < count($rowscount); $i++){
				$id = $this->input->post('id'.$i);	
				$this->db->trans_begin();
			if($id == 0){
					$data = array(
					'room_name' => trim($this->input->post('room_name'.$i)),
					'status' => trim($this->input->post('status'.$i)),
					'campus_id' => $campus_id,
					'user_id' => $user_id,
					'created_date' => $date
				);
				$this->db->insert('h_rooms', $data);
				$new_user_id = $this->db->insert_id();

			}else{
					
				$data = array(
					'room_name' => trim($this->input->post('room_name'.$i)),
					'status' => trim($this->input->post('status'.$i)),
					'campus_id' => $campus_id,
					'user_id' => $user_id,
					'updated_date' => $date
				);
				$this->db->where('room_id', $id);
				$this->db->update('h_rooms', $data);

				//print_r($this->db->error());
			}
				
			$this->db->trans_complete();
		}
			
	     json_response(array('success' => TRUE, 'msg' => 'Add Room Success'));
			
	}

	function delete(){
		check_permission('admin-del-class');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('class_id', $id);
		$this->db->delete('classes');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Classes Success'));
	}

}
// end this file
