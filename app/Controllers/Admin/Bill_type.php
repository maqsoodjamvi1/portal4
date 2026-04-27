<?php
namespace App\Controllers\Admin;



/**
 * Bill Type Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */



class Bill_type extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-bill-type');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('bill_type', $this->template_data);
	}
	
	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];

		$this->db->select('count(A.bill_type_id) as ccount', FALSE);
		$this->db->from('bill_type A');
		if($keyword){
			$this->db->where('(A.bill_type_name=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('bill_type A');
		if($keyword){
			$this->db->where('(A.bill_type_name=' . $this->db->escape($keyword) .  ')');
		}
		$this->db->order_by('A.bill_type_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();
		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->bill_type_id;
			$data['bill_type_name'] = $row->bill_type_name;
			$data['bill_type_detail'] = $row->bill_type_detail;
			$response->data[] = $data;

		}

		$this->output->set_output(json_encode($response));

	}

	function add(){
		check_permission('admin-add-bill-type');
		$schoolinfo = getSchoolInfo();

		$info = $this->db->get('bill_type')->result();
		$this->template_data['info'] = $info;
		$this->load->view('bill_type_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-bill-type');
		$bill_type_id = intval($this->input->get('id'));
		
		$this->db->where('bill_type_id', $fee_type_id);
		$info = $this->db->get('bill_type')->row();
		$this->template_data['info'] = $info;
		$this->load->view('bill_type_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$schoolinfo = getSchoolInfo();
		$rowscount = $this->input->post('rowscount');
		

		for($i=0; $i < count($rowscount); $i++){

			$id = $this->input->post('id'.$i);

			if($id == 0){
				
				$data = array(
					'bill_type_name' => trim($this->input->post('bill_type_name'.$i)),
					'bill_type_detail' => trim($this->input->post('bill_type_detail'.$i)),
					'user_id' => $user_id,
					'created_date' => $date
				);


				$this->db->insert('bill_type', $data);
				$new_user_id = $this->db->insert_id();
				

			}else{
					
			$data = array(
				'bill_type_name' => trim($this->input->post('bill_type_name'.$i)),
				'bill_type_detail' => trim($this->input->post('bill_type_detail'.$i)),
				'user_id' => $user_id,
				'updated_date' => $date
			);

			
			$this->db->where('bill_type_id', $id);
			$this->db->update('bill_type', $data);

			
			}

			$this->db->trans_complete();
			}

			json_response(array('success' => TRUE, 'msg' => 'Bill Type Updated Success'));
			
	}

}
// end this file