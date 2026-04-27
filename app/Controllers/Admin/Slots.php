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


class Slots extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-slots');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('slots', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.slot_id) as ccount', FALSE);
		$this->db->from('slots A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campusid) . ')');
		if($keyword){
			$this->db->where('(A.slot_name=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

	
		$this->db->select('A.*');
		$this->db->from('slots A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campusid).')');
		if($keyword){
			$this->db->where('(A.slot_name=' . $this->db->escape($keyword)  . ')');
		}
		$this->db->order_by('A.slot_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->slot_id;
			$data['slot_name'] = $row->slot_name;
			$data['start_time'] = $row->start_time;
			$data['end_time'] = $row->end_time;
			$data['slot_type'] = $row->slot_type;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-slot');
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('campus_id', $campusid);
		$info = $this->db->get('slots')->result();
		$this->template_data['info'] = $info;
		$this->load->view('slot_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-slot');
		$id = intval($this->input->get('id'));

		$this->db->where('slot_id', $id);
		$info = $this->db->get('slots')->row();
		$this->template_data['info'] = $info;
		$this->load->view('slot_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$campusid = $this->session->userdata('member_campusid');
		$rowscount = $this->input->post('rowscount');

			for($i=0; $i < count($rowscount); $i++){
				$id = $this->input->post('id'.$i);
				
				if($id == 0){
					$data = array(
					'slot_name' => trim($this->input->post('slot_name'.$i)),
					'start_time' => trim($this->input->post('start_time'.$i)),
					'end_time' => trim($this->input->post('end_time'.$i)),
					'slot_type' => trim($this->input->post('slot_type'.$i)),
					'campus_id' => $campusid,
					'user_id' => $user_id,
					'created_date' => $date
				);
					
				$this->db->insert('slots', $data);
				$new_user_id = $this->db->insert_id();

			}else{
					
				$data2 = array(
					'slot_name' => trim($this->input->post('slot_name'.$i)),
					'start_time' => trim($this->input->post('start_time'.$i)),
					'end_time' => trim($this->input->post('end_time'.$i)),
					'slot_type' => trim($this->input->post('slot_type'.$i)),
					'campus_id' => $campusid,
					'user_id' => $user_id,
					'updated_date' => $date
				);
				
				$this->db->where('slot_id', $id);
				$this->db->update('slots', $data2);
			}
				
				$this->db->trans_complete();
			}
			
			json_response(array('success' => TRUE, 'msg' => 'Add Slot Success'));
	
	}

}
// end this file
