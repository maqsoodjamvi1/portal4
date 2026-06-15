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
		helper('role');

		$max_fee = (int) ($this->input->post('max_fee') ?? 0);
		$max_students = (int) ($this->input->post('max_students') ?? 0);
		$stdMaxFee = 0;
		$no_of_students = 0;

		$system_plans = $this->db->table('system_plans')->where('plan_id', getSystemPlanId())->get()->getRow();
		$installmentPlan = getAnnualInstallPlan();

		$number_of_students_info = $this->db->table('number_of_students')->where('id', $max_students)->get()->getRow();
		if ($number_of_students_info) {
			$no_of_students = $number_of_students_info->no_of_students;
		}

		$max_student_fee_info = $this->db->table('max_student_fee')->where('id', $max_fee)->get()->getRow();
		if ($max_student_fee_info) {
			$stdMaxFee = $max_student_fee_info->max_fee;
		}

		if (! $system_plans || ! $installmentPlan) {
			echo '<p class="text-muted">Annual package is not configured.</p>';
			exit;
		}

		$installmentBill = round($system_plans->factor * $installmentPlan->discount_factor * $installmentPlan->month_count * $stdMaxFee * $no_of_students);
		$packagePlan  = '<table class="table">';
		$packagePlan .= '<tr><td style="vertical-align: middle;">' . esc($system_plans->plan_name) . ' (Annual)</td>';
		$packagePlan .= '<td style="font-size:12px;vertical-align:middle;">';
		$packagePlan .= '<label><input name="package" value="bill_' . (int) $installmentPlan->install_id . '_' . (int) $system_plans->plan_id . '" type="checkbox" checked> ';
		$packagePlan .= 'Annual bill ' . $installmentBill . '/-</label></td></tr>';
		$packagePlan .= '</table>';
		echo $packagePlan;
		exit;
	}

	function calculateCampusBill(){
		helper('role');

		$max_fee = (int) ($this->input->post('max_fee') ?? 0);
		$max_students = (int) ($this->input->post('max_students') ?? 0);

		$systemPlan = $this->db->table('system_plans')->where('plan_id', getSystemPlanId())->get()->getRow();
		$installmentPlan = getAnnualInstallPlan();
		$numberOfStudents = $this->db->table('number_of_students')->where('id', $max_students)->get()->getRow();
		$maxFee = $this->db->table('max_student_fee')->where('id', $max_fee)->get()->getRow();

		if (! $systemPlan || ! $installmentPlan || ! $numberOfStudents || ! $maxFee) {
			echo '<span class="text-danger">Unable to calculate bill.</span>';
			return;
		}

		$charged = $numberOfStudents->charged ?? $numberOfStudents->no_of_students ?? 0;
		$installmentBill = $systemPlan->factor * $installmentPlan->discount_factor * $installmentPlan->month_count * $maxFee->max_fee * $charged;

		echo $installmentBill . '/' . esc($installmentPlan->install_name) . ' (Annual)<br>';
		echo '<input type="hidden" name="bill_amount" value="' . esc($installmentBill) . '">';
		echo '<input type="hidden" name="plan_id" value="' . (int) $systemPlan->plan_id . '">';
		echo '<input type="hidden" name="installment_plan" value="' . (int) $installmentPlan->install_id . '">';
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