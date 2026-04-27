<?php
namespace App\Controllers\Admin;



/**
 * Vehicles Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/



class Vehicles extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-vehicles');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('vehicles', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.vehicle_id) as ccount', FALSE);
		$this->db->from('vehicles A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.reg_no=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

	
		$this->db->select('A.*');
		$this->db->from('vehicles A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.reg_no=' . $this->db->escape($keyword)  . ')');
		}
		$this->db->order_by('A.vehicle_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->vehicle_id;
			$data['reg_no'] = $row->reg_no;
			$data['vehicle_code'] = $row->vehicle_code;
			$data['route'] = $row->route;
			$data['route_fare'] = $row->route_fare;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-vehicle');
		$campus_id = $this->session->userdata('member_campusid');

		$this->db->where('campus_id', $campus_id);
		$info = $this->db->get('vehicles')->result();
		$this->template_data['info'] = $info;

		$this->load->view('vehicles_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-vehicle');
		$id = intval($this->input->get('id'));

		$this->db->where('vehicle_id', $id);
		$info = $this->db->get('vehicles')->row();
		$this->template_data['info'] = $info;
		$this->load->view('vehicles_edit', $this->template_data);
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
					'reg_no' => trim($this->input->post('reg_no'.$i)),
					'vehicle_code' => trim($this->input->post('vehicle_code'.$i)),
					'driver' => trim($this->input->post('driver'.$i)),
					'contact' => trim($this->input->post('contact'.$i)),
					'route' => trim($this->input->post('route'.$i)),
					'route_fare' => trim($this->input->post('route_fare'.$i)),
					'seating_capacity' => trim($this->input->post('seating_capacity'.$i)),
					'campus_id' => $campus_id,
					'user_id' => $user_id,
					'created_date' => $date
				);


				$this->db->insert('vehicles', $data);
				$new_user_id = $this->db->insert_id();

			}else{
					
				$data = array(
					'reg_no' => trim($this->input->post('reg_no'.$i)),
					'vehicle_code' => trim($this->input->post('vehicle_code'.$i)),
					'driver' => trim($this->input->post('driver'.$i)),
					'contact' => trim($this->input->post('contact'.$i)),
					'route' => trim($this->input->post('route'.$i)),
					'route_fare' => trim($this->input->post('route_fare'.$i)),
					'seating_capacity' => trim($this->input->post('seating_capacity'.$i)),
					'campus_id' => $campus_id,
					'user_id' => $user_id,
					'created_date' => $date
				);
				
				$this->db->where('vehicle_id', $id);
				$this->db->update('vehicles', $data);
				
			}
				
			$this->db->trans_complete();
		}

		json_response(array('success' => TRUE, 'msg' => 'Add Vehicle Success'));
			
	}

}
// end this file
