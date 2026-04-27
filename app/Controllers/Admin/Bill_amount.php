<?php
namespace App\Controllers\Admin;



/**
 * Bill Amount Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Bill_amount extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-bill-amount');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$this->load->view('bill_amount', $this->template_data);
	}

	function data(){
		$campus_id = $this->input->get('campus_id');
		$session_id = $this->session->userdata('member_sessionid');
		$plan_id = $this->input->post('plan_id'); 
		$schoolinfo = getSchoolInfo();

		$bill_plans_info = $this->db->get('bill_plans')->result();

		$bill_type_info = $this->db->get('bill_type')->result();

		$data = '';
		$data .= '<table class="table"><tr><td></td>';
		
		if(isset($bill_type_info)){
			foreach ($bill_type_info as $bill_type_value) { 
			$data .= '<th>'.$bill_type_value->bill_type_name.'<input type="hidden" value="'.$bill_type_value->bill_type_id.'" name="bill_type_id[]"></th>';						
			} 

		} 

		if(isset($bill_plans_info)){
			foreach ($bill_plans_info as  $billvalue) { 
				$data .= '<tr><th>'.$billvalue->plan_name.'<input type="hidden" name="plan_id[]" value="'.$billvalue->plan_id.'" ></th>';
			foreach ($bill_type_info as  $bill_type_value) { 
				
				$this->db->where('campus_id', $campus_id);
				$this->db->where('plan_id', $billvalue->plan_id);
				$this->db->where('bill_type_id', $bill_type_value->bill_type_id);
				$bill_amount_info = $this->db->get('bill_amount')->row();
				
				$amount_id = 0;
				$fee_amount = 0;
				$status = 0;
				if($bill_amount_info){
					$amount_id = $bill_amount_info->amount_id;
					$status = $bill_amount_info->status;
					$fee_amount = $bill_amount_info->amount;
				}

				$data .= '<td>';
				
				$data .= '<input ';
				if($status == 1){
					$data .= 'checked';	
				}
				$data .=' name="'.$bill_type_value->bill_type_id.'_'.$billvalue->plan_id.'_status"  type="checkbox" value="1" ><input type="hidden" class="form-control" name="'.$bill_type_value->bill_type_id.'_'.$billvalue->plan_id.'_amount_id" id="'.$bill_type_value->bill_type_id.'_'.$billvalue->plan_id.'_amount_id" value="'.$amount_id.'"><input type="text" class="form-control" name="ftv'.$bill_type_value->bill_type_id.'_ci'.$billvalue->plan_id.'_amount" id="ftv'.$bill_type_value->bill_type_id.'_ci'.$billvalue->plan_id.'_amount" value="'.$fee_amount.'">';
				

				$data .= '</td>';
				} 
				$data .= '</tr>';		
				} 
			}				

		$data .= '</table>';
		$this->output->set_output($data);
	}

	function add(){
		check_permission('admin-add-bill-amount');
		$campus_id = $this->session->userdata('member_campusid');
		$session_id = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$info = $this->db->get('fee_amount')->row();
		$this->template_data['info'] = $info;

		$bill_plans_info = $this->db->get('bill_plans')->result();
		$this->template_data['bill_plans_info'] = $bill_plans_info;

		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$this->load->view('bill_amount_edit', $this->template_data);

	}

	function edit(){

		check_permission('admin-edit-bill-amount');
		$amount_id = intval($this->input->get('id'));

		$this->db->where('amount_id', $amount_id);
		$info = $this->db->get('fee_amount')->row();
		$this->template_data['info'] = $info;

		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$this->load->view('bill_amount_edit', $this->template_data);
	}


	function save(){
		$id = intval($this->input->post('id'));
		$campus_id = $this->input->post('campus_id');
		if(empty($campus_id)){
			echo "campus is not selected";
			exit;
		}
		$session_id = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');


		// $campus_bill_info = $this->db->query('select * from campus_bills WHERE status=1 AND campus_id='.$campus_id)->row();
		
		// $max_fee_limit = $campus_bill_info->max_fee;
		
		$bill_type_ids = $this->input->post('bill_type_id');
		$plan_ids = $this->input->post('plan_id');

		foreach($bill_type_ids as $bill_type_id){
			$i=0;
			foreach($plan_ids as $plan_id){

			$amount = $this->input->post("ftv".$bill_type_id."_ci".$plan_id."_amount");
			$amount_id = $this->input->post("".$bill_type_id."_".$plan_id."_amount_id");
			$status = $this->input->post("".$bill_type_id."_".$plan_id."_status");
			
			if($status){
				$status = 1;
			}else{
				$status = 0;
			}
			
			if($amount_id > 0){
				$data = array(
				'bill_type_id' => $bill_type_id,
				'campus_id' => $campus_id,
				'amount' => $amount,
				'plan_id' => $plan_id,
				'status' => $status,
				'user_id' => $user_id,
				'updated_date' => $date
				);
				$this->db->where('amount_id', $amount_id);
				$this->db->update('bill_amount', $data);

			}else{

				$data = array(
				'bill_type_id' => $bill_type_id,
				'campus_id' => $campus_id,
				'amount' => $amount,
				'plan_id' => $plan_id,
				'status' => $status,
				'user_id' => $user_id,
				'created_date' => $date
				);
				$this->db->insert('bill_amount', $data);
				$new_timetable_id = $this->db->insert_id();
			}

			$i++;
			}
		} 

		json_response(array('success' => TRUE, 'msg' => 'Update Bill Amount Success'));

	}

}

// end this file

