<?php
namespace App\Controllers\Admin;



/**
 * Pay Campus Bill Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Pay_campus_bill extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-pay-campus-bill');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('pay_campus_bill', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$schoolinfo = getSchoolInfo();
		$campusid = $this->session->userdata('member_campusid');
		
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.campus_id) as ccount', FALSE);
		$this->db->from('campus_bills A');
		if($keyword){
			$this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('campus_bills A');
		if($keyword){
			$this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
		}
		$this->db->order_by('A.campus_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();
		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		foreach($results as $row){
			
			$this->db->where('campus_id', $row->campus_id);
			$campus_info = $this->db->get('campus')->row();	
			$campus_name = '';
			$campus_bill_discount = 0;
			if($campus_info){
				$campus_name = $campus_info->campus_name;
				//$campus_bill_discount = $campus_info->campus_bill_discount;
			}

			$this->db->where('plan_id', $row->plan_id);
			$system_plansinfo = $this->db->get('system_plans')->row();	
			$plan_name = '';

			if($system_plansinfo){
				$plan_name = $system_plansinfo->plan_name;
				$price = $system_plansinfo->price;
				$max_fee = $system_plansinfo->fee_limit;
				$install_name = $system_plansinfo->month_count;
				$no_of_students = $system_plansinfo->student_limit;
			}

			$data = array();
			$data['id'] = $row->bill_id;
			$data['bill_status'] = $row->bill_status;
			$data['campus_id'] = $row->campus_id;
			$data['campus_name'] = $campus_name;
			$data['campus_bill_discount'] = '';//$row->discount;
			$data['plan_name'] = $plan_name;
			$data['install_name'] = $row->install_id;
			$data['no_of_students'] = $row->max_students;
			$data['max_fee'] = $row->max_fee;
			$data['bill_amount'] = $row->bill_amount;
			$response->data[] = $data;

		}

		$this->output->set_output(json_encode($response));
	}

	function save(){

		$bill_id = intval($this->input->post('bill_id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
	
		$this->db->where('bill_id', $bill_id);
		$billInfo = $this->db->get('campus_bills')->row();

	
		$this->db->trans_begin();	
		
     	   $next_due_date = date('Y-m-d', strtotime("+".$billInfo->install_id." month"));
     	   $billAmount = ($billInfo->bill_amount - $this->input->post('discount'));

     	   $data2  = array(
				'status' => 0,
			);

		$this->db->where('campus_id', $billInfo->campus_id);
		$this->db->update('campus_bills',$data2);

		$data  = array(
			'campus_expiry' => $next_due_date, 
			'status' => 1,
			'bill_status' => 'paid',
			'updated_date' => $date,
			'discount' => $this->input->post('discount'),
			'bill_amount' => ($billAmount),
			'user_id' => $user_id 
		);

		$this->db->where('bill_id', $bill_id);
		$this->db->update('campus_bills',$data);
		//exit;

		//print_r($data);

		$dataCampus  = array(
			'campus_bill_discount' => $this->input->post('discount'), 
		);

		//print_r($dataCampus);
		$this->db->where('campus_id', $billInfo->campus_id);
		$this->db->update('campus',$dataCampus);

        $this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Bill Paid'));
	
}

	function calculateCampusBill(){
		$max_fee = 0;
		$max_students = 0;
		$plan = $this->input->post('plan');
		if($this->input->post('max_fee')){
			$max_fee = $this->input->post('max_fee');
		}
		if($this->input->post('max_students')){
			$max_students = $this->input->post('max_students');
		}
		
		$installment_plan = $this->input->post('installment_plan');
		$currentDate = date('Y-m-d');

		$next_due_date = date('Y-m-d', strtotime("+30 days"));		

		$this->db->where('plan_id', $plan);
		$systemPlan = $this->db->get('system_plans')->row();

		$this->db->where('install_id', $installment_plan);
		$installmentPlan = $this->db->get('system_installment_plan')->row();

		$this->db->where('id', $max_students);
		$numberOfStudents = $this->db->get('number_of_students')->row();

		$this->db->where('id', $max_fee);
		$maxFee = $this->db->get('max_student_fee')->row();

		$monthlyBill = ($systemPlan->factor*$installmentPlan->discount_factor*$maxFee->max_fee*$numberOfStudents->charged);

		$installmentBill = ($systemPlan->factor*$installmentPlan->discount_factor*$installmentPlan->month_count*$maxFee->max_fee*$numberOfStudents->charged);
		
		echo $monthlyBill."/Month<br>";
		echo $installmentBill."/".$installmentPlan->install_name."<br><input type='hidden' name='bill_amount' value='".$installmentBill."'>";

	}

	function delete(){
		check_permission('admin-del-user');
		$id = intval($this->input->get('id'));
		$this->db->trans_begin();
		// delete user
		$this->db->where('id', $id);
		$this->db->delete('classes');
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Campus Success'));
	}
}
// end this file