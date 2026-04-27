<?php
namespace App\Controllers\Admin;



/**
 * Transport Fee Type Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */



class Transport_fee_type extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-transport-fee-type');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('transport_fee_type', $this->template_data);	
	}
	
	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];

		$this->db->select('count(A.fee_type_id) as ccount', FALSE);
		$this->db->from('fee_type A');
		$this->db->where('(A.system_id='.$schoolinfo->system_id.')');
		$this->db->where('is_monthly_fee !=', 1);
		if($keyword){
			$this->db->where('(A.fee_type_name=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('fee_type A');
		$this->db->where('(A.system_id='.$schoolinfo->system_id.')');	
		$this->db->where('is_monthly_fee !=', 1);
		if($keyword){
			$this->db->where('(A.fee_type_name=' . $this->db->escape($keyword) .  ')');
		}
		$this->db->order_by('A.fee_type_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();
		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->fee_type_id;
			$data['fee_type_name'] = $row->fee_type_name;
			$data['fee_type_detail'] = $row->fee_type_detail;
			$data['is_transport_fee'] = $row->is_transport_fee;
			$response->data[] = $data;

		}

		$this->output->set_output(json_encode($response));

	}

	function add(){
		check_permission('admin-add-transport-fee-type');
		$schoolinfo = getSchoolInfo();

		$this->db->where('system_id', $schoolinfo->system_id);
		$this->db->where('is_monthly_fee !=', 1);
		$info = $this->db->get('fee_type')->result();
		$this->template_data['info'] = $info;

		// $this->db->where('system_id', $schoolinfo->system_id);
		// $this->db->where('is_monthly_fee', 1);
		// $isMonthly = $this->db->get('fee_type')->row();
		// $this->template_data['isMonthly'] = $isMonthly;

		$this->db->where('system_id', $schoolinfo->system_id);
		$this->db->where('is_transport_fee', 1);
		$isTransport = $this->db->get('fee_type')->row();
		$this->template_data['isTransport'] = $isTransport;	
		
		$this->load->view('transport_fee_type_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-transport-fee-type');
		$fee_type_id = intval($this->input->get('id'));
		
		$this->db->where('fee_type_id', $fee_type_id);
		$info = $this->db->get('fee_type')->row();
		$this->template_data['info'] = $info;

		$this->load->view('transport_fee_type_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$schoolinfo = getSchoolInfo();
		$rowscount = $this->input->post('rowscount');
		$is_transport_fee = $this->input->post('is_transport_fee');

		for($i=0; $i < count($rowscount); $i++){

			$id = $this->input->post('id'.$i);

			if('is_transport_fee_'.$i == $is_transport_fee){
				$transportFee = 1;
			}else{
				$transportFee = 0;
			}

			if($id == 0){

				// $this->db->where('system_id', $schoolinfo->system_id);
				// $this->db->where('is_monthly_fee', 1);
				// $isMonthly = $this->db->get('fee_type')->row();
				// if($monthlyFee == $isMonthly){
				// 	json_response(array('success' => TRUE, 'msg' => 'Duplicate Monthly Fee'));
				// 	exit;
				// }
				
				$data = array(
					'fee_type_name' => trim($this->input->post('fee_type_name'.$i)),
					'fee_type_detail' => trim($this->input->post('fee_type_detail'.$i)),
					'is_transport_fee' =>  $transportFee,
					'is_monthly_fee' =>  0,
					'system_id' => $schoolinfo->system_id,
					'user_id' => $user_id,
					'created_date' => $date
				);

				//print_r($data);
				$this->db->insert('fee_type', $data);
				$new_user_id = $this->db->insert_id();

			}else{
					
			$data = array(
				'fee_type_name' => trim($this->input->post('fee_type_name'.$i)),
				'fee_type_detail' => trim($this->input->post('fee_type_detail'.$i)),
				'user_id' => $user_id,
				'updated_date' => $date
			);

			
			$this->db->where('fee_type_id', $id);
			$this->db->update('fee_type', $data);
			
			}

			$this->db->trans_complete();
			}

			json_response(array('success' => TRUE, 'msg' => 'Transport Fee Type Updated Success'));
		
	}

	function delete(){
		check_permission('admin-del-fee-type');
		$id = intval($this->input->get('id'));
		$this->db->trans_begin();
		// delete user
		$this->db->where('fee_type_id', $id);
		$this->db->delete('fee_type');
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Transport Fee Type Success'));
	}
}
// end this file