<?php
namespace App\Controllers\Admin;



/**
 * Users Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Campus_plans extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-campus-plans');
	}

	/**
	 * Index Page for this controller.
	*/
	public function index()
	{
		$this->load->view('campus_plans', $this->template_data);
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
		$this->db->where('(A.campus_id =' . $this->db->escape($campusid) . ')');
		if($keyword){
			$this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('campus_bills A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campusid) . ')');
		if($keyword){
			$this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
		}

		$this->db->order_by('A.campus_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();
		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		$nCount = 1;
		foreach($results as $row){

						
			$data = array();
			$data['id'] = $row->bill_id;
			$data['campus_id'] = $campusid;
			$data['sr_no'] = $nCount;
			$data['no_of_students'] = $row->max_students;
			$data['max_fee'] = $row->max_fee;
			$data['bill_amount'] = $row->bill_amount;
			$data['expiry'] = $row->campus_expiry;
			$data['status'] = $row->bill_status;
			$data['paid_date'] = $row->paid_date;
			$response->data[] = $data;
			$nCount++;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-campus');
		
		$system_plansinfo = $this->db->get('system_plans')->result();
		$this->template_data['system_plansinfo'] = $system_plansinfo;

		$system_installment_planinfo = $this->db->get('system_installment_plan')->result();
		$this->template_data['system_installment_planinfo'] = $system_installment_planinfo;

		$number_of_students = $this->db->get('number_of_students')->result();
		$this->template_data['number_of_students'] = $number_of_students;

		$max_student_feeinfo = $this->db->get('max_student_fee')->result();
		$this->template_data['max_student_feeinfo'] = $max_student_feeinfo;

		$schoolinfo = getSchoolInfo();
		$this->template_data['schoolinfo'] = $schoolinfo;

		$this->load->view('campus_plans_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-campus');
		$campus_id = $this->session->userdata('member_campusid');
		
		$bill_id = intval($this->input->get('id'));

		$this->db->where('bill_id', $bill_id);
		$this->db->where('status', 1);
		$campus_bills_info = $this->db->get('campus_bills')->row();
		$this->template_data['campus_bills_info'] = $campus_bills_info;

		$this->db->where('plan_id', $campus_bills_info->plan_id);
		$system_plansinfo = $this->db->get('system_plans')->result();
		$this->template_data['system_plansinfo'] = $system_plansinfo;

		$this->db->where('install_id', $campus_bills_info->install_id);
		$system_installment_planinfo = $this->db->get('system_installment_plan')->result();
		$this->template_data['system_installment_planinfo'] = $system_installment_planinfo;

		$this->db->where('id', $campus_bills_info->max_students);
		$number_of_students = $this->db->get('number_of_students')->result();
		$this->template_data['number_of_students'] = $number_of_students;

		$this->db->where('id', $campus_bills_info->max_fee);
		$max_student_feeinfo = $this->db->get('max_student_fee')->result();
		$this->template_data['max_student_feeinfo'] = $max_student_feeinfo;


		$schoolinfo = getSchoolInfo();
		$this->template_data['schoolinfo'] = $schoolinfo;

		$this->db->where('campus_id', $campus_id);
		$info = $this->db->get('campus')->row();
		$this->template_data['info'] = $info;
		$this->load->view('campus_plans_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$campusid = $this->session->userdata('member_campusid');
		$no_of_students = $this->input->post('max_students');
		$stdMaxFee = $this->input->post('max_fee');
		$package = $this->input->post('package');
		if($package){
			$packageArr = explode('_', $package);
			$installment_plan = $packageArr[1];
			$plan = $packageArr[2];
			
			$this->db->where('plan_id', $plan);
			$system_plans_info = $this->db->get('system_plans')->row();

			$this->db->where('install_id', $installment_plan);
			$installment_plans_info = $this->db->get('system_installment_plan')->row();
	
			$bill_amount = round($system_plans_info->factor*$installment_plans_info->discount_factor*$installment_plans_info->month_count*$stdMaxFee*$no_of_students);

		}else{
			echo json_encode(array('error' => TRUE, 'msg' => 'Select Package'));
		    exit;
		}
		{
			if($id === 0){
				check_permission('admin-add-campus-plan');
				$this->db->trans_begin();
				
				$next_due_date = date('Y-m-d', strtotime("+30 days"));		

				$data2 = array(
					'campus_id' => trim($campusid),
					'plan_id' => trim($plan),
					'install_id' => trim($installment_plan),
					'max_students' => trim($this->input->post('max_students')),
					'max_fee' => trim($this->input->post('max_fee')),
					'status' => 0,
					'campus_expiry' => $next_due_date,
					'bill_amount' => trim($bill_amount),
					'bill_status' => 'unpaid',
					'bill_issue_date' => $date,
				);

				$this->db->insert('campus_bills', $data2);
				$new_bill_id = $this->db->insert_id();


				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Add Campus Bill Success'));

			}else{
				check_permission('admin-edit-campus-plan');
				$this->db->trans_begin();
					$data2 = array(
					'plan_id' => trim($plan),
					'install_id' => trim($installment_plan),
					'max_students' => trim($this->input->post('max_students')),
					'max_fee' => trim($this->input->post('max_fee')),
					'bill_amount' => trim($bill_amount),
				);

				$this->db->where('bill_id', $id);
				$this->db->update('campus_bills', $data2);
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Campus Plan Success'));
			}
		}
	}

	function get_packages(){
		$max_fee = 0;
		$max_students = 0;
		$stdMaxFee = 0;
		$no_of_students = 0;
		
		if($this->input->post('max_fee')){
		 	$max_fee = $this->input->post('max_fee');
		}
		if($this->input->post('max_students')){
			$max_students = $this->input->post('max_students');
		}
		
		$currentDate = date('Y-m-d');
		$next_due_date = date('Y-m-d', strtotime("+30 days"));
		
		$system_plans_info = $this->db->get('system_plans')->result();

		$installment_plans_info = $this->db->get('system_installment_plan')->result();

		$this->db->where('id', $max_students);
		$number_of_students_info = $this->db->get('number_of_students')->row();
		if($number_of_students_info){
			$no_of_students = $number_of_students_info->no_of_students;
		}
		
		
		$this->db->where('id', $max_fee);
		$max_student_fee_info = $this->db->get('max_student_fee')->row();
		if($max_student_fee_info){
			$stdMaxFee = $max_student_fee_info->max_fee;
		}
		

		$packagePlan = '';
		$packagePlan .= '<table class="table">';
		$packagePlan .= '<tr><td></td>';
		foreach ($installment_plans_info as $key => $installment_plans) {
			$packagePlan .= '<td style="vertical-align: middle;">'.$installment_plans->install_name.'</td>';
		}
	$packagePlan .= '<tr>';
	foreach ($system_plans_info as $key => $system_plans) {
		
		$packagePlan .= '<tr><td style="vertical-align: middle;">'.$system_plans->plan_name.'</td>';

	foreach ($installment_plans_info as $key => $installment_plans) {

		$factorAfterInstallment = ($system_plans->factor - $installment_plans->discount_factor);
		 
		$monthlyBill = round($system_plans->factor*$installment_plans->discount_factor*$stdMaxFee*$no_of_students);

		$installmentBill = round($system_plans->factor*$installment_plans->discount_factor*$installment_plans->month_count*$stdMaxFee*$no_of_students);

		$packagePlan .= '<td style="font-size:12px;vertical-align:middle;"><lable><input style="" name="package" value="bill_'.$installment_plans->install_id.'_'.$system_plans->plan_id.'" type="checkbox"><div style="float:right;"> Per Month '.$monthlyBill."/-<br> Bill ".$installmentBill.'/-<div></lable></td>';
	}
		$packagePlan .= '<tr>';
	}

		$packagePlan .= '</table><script>
		$(document).on("click", "input[type=\'checkbox\']", function() {      
   		 $("input[type=\'checkbox\']").not(this).prop("checked", false);      
		});</script>';
		echo $packagePlan;
		exit;
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