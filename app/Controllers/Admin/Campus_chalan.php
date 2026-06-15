<?php
namespace App\Controllers\Admin;



/**
 * Campus Chalan Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/


class Campus_chalan extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-campus-chalan');
	}


function index(){
	
	check_permission('admin-add-campus-chalan');
	$campus_id = $this->input->get('campus_id');
	$schoolinfo = getSchoolInfo();

	$this->db->where('campus_id', $campus_id);
	$info = $this->db->get('campus')->row();
	
	$this->template_data['info'] = $info;
	
	$bill_plans_info = $this->db->get('bill_plans')->result();
	$defaultBillTypeId = ensureDefaultBillTypeId();
	$bill_plans_data = [];

	foreach ($bill_plans_info as $bill_plans) {
		$this->db->where('bill_type_id', $defaultBillTypeId);
		$this->db->where('campus_id', $campus_id);
		$this->db->where('plan_id', $bill_plans->plan_id);
		$billamountinfo = $this->db->get('bill_amount')->row();

		$bill_plans_data[] = [
			'plan_id'   => $bill_plans->plan_id,
			'plan_name' => $bill_plans->plan_name,
			'amount'    => $billamountinfo ? $billamountinfo->amount : 0,
		];
	}

	$this->template_data['bill_plans_data'] = $bill_plans_data;
	$this->template_data['default_bill_type_id'] = $defaultBillTypeId;
	$this->load->view('add_campus_chalan', $this->template_data);

}

function save(){

		$chalandata = array();

		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$campus_id = $this->input->post('campus_id');
		$plan_id = $this->input->post('plan_id');
		$bill_type_id = ensureDefaultBillTypeId();
		$bill_type_amount = $this->input->post('bill_amount_' . $plan_id);

		$issue_date = DateTime::createFromFormat('d/m/Y',$this->input->post('issue_date'));
		$issuedate = $issue_date->format('Y-m-d');
		
		$due_date = DateTime::createFromFormat('d/m/Y',$this->input->post('due_date'));
		$duedate = $due_date->format('Y-m-d');
		
		$id = intval($this->input->post('id'));
		$session_id = $this->session->userdata('member_sessionid');


		$amount = 0;
		check_permission('admin-add-campus-chalan');
		$this->db->trans_begin();
		
		$this->db->where('bill_type_id', $bill_type_id);
		$this->db->where('campus_id', $campus_id);
		$this->db->where('plan_id', $plan_id);
		$billChalaninfo = $this->db->get('campus_chalan')->row();

		if (empty($billChalaninfo)) {
			$data = [
				'plan_id'      => $plan_id,
				'bill_type_id' => $bill_type_id,
				'campus_id'    => $campus_id,
				'issue_date'   => $issuedate,
				'due_date'     => $duedate,
				'bill_amount'  => $bill_type_amount,
				'bill_status'  => 'unpaid',
				'created_date' => $date,
				'user_id'      => $user_id,
			];

			$this->db->insert('campus_chalan', $data);
		}

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Add Campus Chalan Success'));

}
function download(){
		 $data =  $this->data();
	     $this->template_data['data'] = $data;			
		 $this->load->view('campus_chalan_pdf', $this->template_data);
	}
	
function data(){
		$student_data = array();
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');
		$student_id = $_GET['id'];
		$schoolinfo = getSchoolInfo();
		$keyword = '';
		$result = $this->db->query('SELECT t1.cls_sec_id,t2.student_id,t2.parent_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE  t1.status=1 AND t2.student_id ='.$student_id.' and t1.student_id ='.$student_id)->result(); 

		$response->recordsTotal = count((array)$result);
		$response->student_data = array();

	foreach($result as $row){
		$where = "student_id='".$row->student_id."' AND status='unpaid'";
		$this->db->where($where);
		$this->db->order_by("fee_month", "asc");
		$chalan_info = $this->db->get('fee_chalan')->row();

	 	$unpaind_total = $this->db->query("SELECT sum(fee.amount)- sum(fee.discount) as total FROM fee_chalan fee where student_id=".$row->student_id." and status='unpaid'")->row();

	 if($unpaind_total->total){

		$classSectioninfo = getClassSection($row->cls_sec_id);

		$this->db->where('campus_id',  $row->campus_id);
		$campusinfo = $this->db->get('campus')->row();


		if($campusinfo->campus_name){
			$campus_name = $campusinfo->campus_name;
		}else{
			$campus_name = '';
		}

		if($campusinfo->location){
		$location = $campusinfo->location;
		}else{
		$location = '';
		}

		if($campusinfo->bank_name){
		$bank_name = $campusinfo->bank_name;
		}else{
		$bank_name = '';
		}

		if($campusinfo->bank_address){
		$bank_address = $campusinfo->bank_address;
		}else{
		$bank_address = '';
		}

		if($campusinfo->bank_code){
		$bank_code = $campusinfo->bank_code;
		}else{
		$bank_code = '';
		}

		if($campusinfo->bank_acc){
		$bank_acc = $campusinfo->bank_acc;
		}else{
		$bank_acc = '';
		}

		if($campusinfo->chalan_h_msg){
		$chalan_h_msg = $campusinfo->chalan_h_msg;
		}else{
		$chalan_h_msg = '';
		}

		if($campusinfo->chalan_f_msg){
		$chalan_f_msg = $campusinfo->chalan_f_msg;
		}else{
		$chalan_f_msg = '';
		}
		$where = "student_id='".$row->student_id."' AND status='unpaid'";
		$this->db->where($where);
		$this->db->order_by("fee_month", "asc");
		$fee_chalan = $this->db->get('fee_chalan')->result();
	    //exit;

		$FChalanNum = $this->db->query('SELECT chalan_id from fee_chalan where student_id='.$row->student_id.' AND status="unpaid" ORDER BY chalan_id DESC')->row();

	    $student_fee = array();
		foreach($fee_chalan as $chalanvalue){
		
		$this->db->where('fee_type_id', $chalanvalue->fee_type_id);
		$fee_type_info = $this->db->get('fee_type')->row();
		
		$student_fee[] = array(
			'id' => $chalanvalue->chalan_id,
			'amount' => $chalanvalue->amount,
			'status' => $chalanvalue->status,
			'discount' => $chalanvalue->discount,
			'paiddate' => $chalanvalue->paid_date,
			'fee_month' => $chalanvalue->fee_month,
			'fee_name' => $fee_type_info->fee_type_name,	
			'is_monthly_fee' => $fee_type_info->is_monthly_fee	

		   );	

		   			  
		}
	
	$fee_fine = array();
	
	$this->db->where('parent_id', $row->parent_id);
	$parentinfo = $this->db->get('parents')->row();

	$student_data[] = array(	  
		'campus_name' => $campus_name,
		'system_name' => $schoolinfo->system_name,
		'chalan_no' => $FChalanNum->chalan_id,
		'logo' => $schoolinfo->logo,
		'location' => $location,	
		'bank_name' => $bank_name,	
		'bank_address' => $bank_address,	
		'bank_code' => $bank_code,	
		'bank_acc' => $bank_acc,
		'chalan_h_msg' => $chalan_h_msg,
		'chalan_f_msg' => $chalan_f_msg,	
		'student_id' => $row->student_id,
	    'reg_no' => $row->reg_no,
		'student_name' => $row->first_name." ".$row->last_name,
		'family_no' => $parentinfo->parent_id,
		'f_name' => $parentinfo->f_name,
		'class_name' => $classSectioninfo['sectionclassname'],
		'fee_month' => $chalan_info->fee_month,
		'issue_date' => $chalan_info->issue_date,
		'due_date' => $chalan_info->due_date,
		'student_fee'=> $student_fee,
		'fee_fine' =>$fee_fine
		);
	}
   }
   return $student_data;		
}

}
// end this file

