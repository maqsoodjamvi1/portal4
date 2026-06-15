<?php
namespace App\Controllers\Admin;



/**
 * Campus Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Custom_campus extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-custom-campus');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('custom_campus', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$schoolinfo = getSchoolInfo();

		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.campus_id) as ccount', FALSE);
		$this->db->from('campus A');
		$this->db->where('(A.system_id =' . $this->db->escape($schoolinfo->system_id) . ')');
		if($keyword){
			$this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		$this->db->select('A.*');
		$this->db->from('campus A');
		$this->db->where('(A.system_id =' . $this->db->escape($schoolinfo->system_id) . ')');
		if($keyword){
			$this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
		}

		$this->db->order_by('A.campus_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();
		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->campus_id;
			$data['campus_name'] = $row->campus_name;
			$data['short_name'] = $row->short_name;
			$data['landline'] = $row->landline;
			$data['mobile_no'] = $row->mobile_no;
			$data['location'] = $row->location;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-custom-campus');
		
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

		$this->load->view('custom_campus_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-custom-campus');
		
		$campus_id = intval($this->input->get('id'));

		$this->db->where('campus_id', $campus_id);
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
		$this->load->view('custom_campus_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
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
				check_permission('admin-add-custom-campus');
				$this->db->trans_begin();
				$data = array(
					'system_id' => trim($this->input->post('system_id')),
					'campus_name' => trim($this->input->post('campus_name')),
					'short_name' => trim($this->input->post('short_name')),
					//'landline' => trim($this->input->post('landline')),
					'mobile_no' => trim($this->input->post('mobile_no')),
					'location' => trim($this->input->post('location')),
					// 'bank_name' => trim($this->input->post('bank_name')),
					// 'bank_address' => trim($this->input->post('bank_address')),
					// 'bank_code' => trim($this->input->post('bank_code')),
					// 'bank_acc' => trim($this->input->post('bank_acc')),
					// 'chalan_h_msg' => trim($this->input->post('chalan_h_msg')),
					// 'chalan_f_msg' => trim($this->input->post('chalan_f_msg')),
					// 'late_fee_fine' => trim($this->input->post('late_fee_fine')),
					// 'fee_issue_date' => trim($this->input->post('fee_issue_date')),
					// 'fee_due_date' => trim($this->input->post('fee_due_date')),
					'created_date' => $date,
					'user_id' => $user_id,
				);

				$this->db->insert('campus', $data);
				$new_campus_id = $this->db->insert_id();

			   $next_due_date = date('Y-m-d', strtotime("+30 days"));		
			   if($_SERVER['HTTP_HOST'] != 'trail.timesoftsol.com'){
	
				$data2 = array(
					'campus_id' => trim($new_campus_id),
					'plan_id' => $plan,
					'install_id' => trim($installment_plans),
					'max_students' => trim($this->input->post('max_students')),
					'max_fee' => trim($this->input->post('max_fee')),
					'status' => 1,
					'campus_expiry' => $next_due_date,
					'bill_amount' => trim($this->input->post('price')),
					'bill_status' => 'unpaid',
					'bill_issue_date' => $date,
					'created_date' => $date,
					'user_id' => $user_id
				);

				$this->db->insert('campus_bills', $data2);
				$new_bill_id = $this->db->insert_id();
				}else{

				$password = password_hash(trim($this->input->post['password']), PASSWORD_BCRYPT);

				$dataUsers = array(
					'campus_id' => $new_campus_id,
					'first_name' => trim($this->input->post('first_name')),
					'last_name' => trim($this->input->post('last_name')),
					'email' => trim($this->input->post('email')),
					'username' => trim($this->input->post('email')),
					'password' => trim($password),
					'mobile_no' => trim($this->input->post('mobile_no')),
					'address' => trim($this->input->post('location')),
					'created_date' => $date, 
					'user_id' => $user_id,
				);

			
			$this->db->insert('users', $dataUsers);
			$last_user_id = $this->db->insert_id();
			
			if($this->db->affected_rows() > 0){

	     		$bill_issue_date = date('Y-m-d');
		      	$next_due_date = date('Y-m-d', strtotime("+".$installnfo->month_count." month"));

			   
			    $dataUserRole2 = array(
			    	'userID' => $last_user_id, 
			    	'roleID' => 3, 
			    );

			    $this->db->insert('user_roles', $dataUserRole2);
			
			   	$dataCampusBills = array(
					'campus_id' => trim($new_campus_id),
					'plan_id' => $plan,
					'install_id' => trim($installment_plans),
					'max_students' => trim($this->input->post('max_students')),
					'max_fee' => trim($this->input->post('max_fee')),
					'status' => 1,
					'campus_expiry' => $next_due_date,
					'bill_amount' => trim($this->input->post('price')),
					'bill_status' => 'unpaid',
					'bill_issue_date' => $date,
					'created_date' => $date,
					'user_id' => $user_id
				);

			     
			    $this->db->insert('campus_bills', $dataCampusBills);
				$campus_bill_id = $this->db->insert_id();

				}

			}

			$this->db->trans_complete();
			json_response(array('success' => TRUE, 'msg' => 'Add Campus Success'));

			}else{
				check_permission('admin-edit-custom-campus');
				$this->db->trans_begin();
				$data = array(
					'campus_name' => trim($this->input->post('campus_name')),
					'short_name' => trim($this->input->post('short_name')),
					'landline' => trim($this->input->post('landline')),
					'mobile_no' => trim($this->input->post('mobile_no')),
					'location' => trim($this->input->post('location')),
					'bank_name' => trim($this->input->post('bank_name')),
					'bank_address' => trim($this->input->post('bank_address')),
					'bank_code' => trim($this->input->post('bank_code')),
					'bank_acc' => trim($this->input->post('bank_acc')),
					'chalan_h_msg' => trim($this->input->post('chalan_h_msg')),
					'chalan_f_msg' => trim($this->input->post('chalan_f_msg')),
					'late_fee_fine' => trim($this->input->post('late_fee_fine')),
					'fee_issue_date' => trim($this->input->post('fee_issue_date')),
					'fee_due_date' => trim($this->input->post('fee_due_date')),
					'updated_date' => $date,
					'user_id' => $user_id,
				);

				$this->db->where('campus_id', $id);
				$this->db->update('campus', $data);
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Campus Success'));
			}
		}
	}

	function get_packages(){
		helper('role');

		$max_students = (int) ($this->input->post('max_students') ?? 0);
		$planId       = getSystemPlanId();

		$this->db->where('plan_id', $planId);
		if ($max_students > 0) {
			$this->db->where('student_limit', $max_students);
		}
		$system_plans = $this->db->get('system_plans')->row();

		if (! $system_plans) {
			echo '<p class="text-muted">Annual package is not configured.</p>';
			exit;
		}

		$Bill = $system_plans->price . '/Annum';
		$packagePlan  = '<table class="table">';
		$packagePlan .= '<tr><td><input type="checkbox" name="plan_id" value="' . (int) $system_plans->plan_id . '" checked></td>';
		$packagePlan .= '<td style="vertical-align: middle;">' . esc($system_plans->plan_name) . ' (Annual)</td>';
		$packagePlan .= '<td>Max Students: ' . (int) $system_plans->student_limit . '</td>';
		$packagePlan .= '<td>Max Fee: ' . esc($system_plans->fee_limit) . '</td>';
		$packagePlan .= '<td>' . esc($Bill) . '</td></tr>';
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

		$installmentBill = $systemPlan->factor * $installmentPlan->discount_factor * $installmentPlan->month_count * $maxFee->max_fee * $numberOfStudents->no_of_students;

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