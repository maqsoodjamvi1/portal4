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


class Campus_bill extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-campus-bill');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$bill_id = $this->input->get('id');

		$this->db->where('bill_id', $bill_id);
		$campusbillinfo = $this->db->get('campus_bills')->row();
		$this->template_data['campusbillinfo'] = $campusbillinfo;

		$this->db->where('campus_id', $campusbillinfo->campus_id);
		$campusinfo = $this->db->get('campus')->row();

		$this->template_data['campusinfo'] = $campusinfo;

		$this->db->where('plan_id', $campusbillinfo->plan_id);
		$systemPlaninfo = $this->db->get('system_plans')->row();
		$this->template_data['systemPlaninfo'] = $systemPlaninfo;
		
		// $this->db->where('install_id', $campusbillinfo->install_id);
		// $installmentPlaninfo = $this->db->get('system_installment_plan')->row();
		// $this->template_data['installmentPlaninfo'] = $installmentPlaninfo;
		
		// $this->db->where('id', $campusbillinfo->max_students);
		// $number_of_students = $this->db->get('number_of_students')->row();
		// $this->template_data['number_of_students'] = $number_of_students;
		
		// $this->db->where('id', $campusbillinfo->max_fee);
		// $max_student_feeinfo = $this->db->get('max_student_fee')->row();
		// $this->template_data['max_student_feeinfo'] = $max_student_feeinfo;

		$monthlyBill = round($campusbillinfo->bill_amount);
		// echo $systemPlaninfo->factor."<br>";
		// echo $max_student_feeinfo->max_fee."<br>"; 
		// echo $number_of_students->no_of_students."<br>";
		// echo $installmentPlaninfo->discount_factor;
		// $installmentBill = round($system_plans->factor*$installment_plans->month_count*$stdMaxFee*$no_of_students);
		
		$this->load->view('campus_bill', $this->template_data);
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

		$this->load->view('campus_edit_bill', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-campus');
		$campus_id = intval($this->input->get('id'));

		$this->db->where('campus_id', $campus_id);
		$info = $this->db->get('campus')->row();
		$this->template_data['info'] = $info;
		$this->load->view('campus_edit_bill', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$this->form_validation->set_rules('campus_name', 'Campus Name', 'trim|required');
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			if($id === 0){
				check_permission('admin-add-campus');
				$this->db->trans_begin();
				$data = array(
					'system_id' => trim($this->input->post('system_id')),
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
					'create_date' => $date,
					'user_id' => $user_id,
					'status' => 0

				);

				$this->db->insert('campus', $data);
				$new_campus_id = $this->db->insert_id();

			    $next_due_date = date('Y-m-d', strtotime("+30 days"));		


				$data2 = array(
					'campus_id' => trim($new_campus_id),
					'plan_id' => trim($this->input->post('plan_id')),
					'install_id' => trim($this->input->post('install_id')),
					'max_students' => trim($this->input->post('max_students')),
					'max_fee' => trim($this->input->post('max_fee')),
					'status' => 0,
					'campus_expiry' => $next_due_date,
					'bill_amount' => trim($this->input->post('bill_amount')),
					'bill_status' => 0,
					'bill_issue_date' => $date,
				);

				$this->db->insert('campus_bills', $data2);
				$new_bill_id = $this->db->insert_id();


				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Add Campus Success'));

			}else{
				check_permission('admin-edit-campus');
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