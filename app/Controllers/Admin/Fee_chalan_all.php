<?php
namespace App\Controllers\Admin;


/**
 * Fee Chalan Single Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */

class Fee_chalan_all extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-fee-chalan');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$this->load->view('add_all_chalan', $this->template_data);
	}

	function add(){
	check_permission('admin-add-fee-chalan');		
	$this->load->view('add_all_chalan', $this->template_data);
	}

	function loadStudents(){
		$session_id = $this->session->userdata('member_sessionid');
		$campus_id = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		//$this->db->where('status', 4);
		$studentsinfo = $this->db->query('select * from students WHERE status=4 AND campus_id='.$campus_id.' AND student_id IN(select student_id from student_class WHERE session_id='.$session_id.')')->result();
		$studentStr = '';
		$this->db->where('system_id', $schoolinfo->system_id);
		$feetypeinfo = $this->db->get('fee_type')->result();
		$studentStr .= '<table class="table"><thead>';
			$studentStr .= '<tr>';
			$studentStr .= '<th>Name</th>';
			foreach ($feetypeinfo as $key => $value) {
				$studentStr .= '<th>'.$value->fee_type_name.'</th>';
			}
			$studentStr .= '</tr></thead><tbody>';
		foreach ($studentsinfo as $key => $student) {

		$this->db->where('student_id', $student->student_id);
		$this->db->where('session_id', $session_id);
		$studentclassinfo = $this->db->get('student_class')->row();
		
		$classesinfo = $this->db->query('select * from classes WHERE class_id IN(select class_id from class_section WHERE cls_sec_id='.$studentclassinfo->cls_sec_id.')')->row();

		
			$studentStr .= '<tr>';
			$studentStr .= '<td><input type="hidden" name="student_id[]" value="'.$student->student_id.'">'.$student->first_name." ".$student->last_name.'</td>';
			foreach ($feetypeinfo as $key => $value) {
		
				$this->db->where('fee_type_id', $value->fee_type_id);
				$this->db->where('campus_id', $campus_id);
				$this->db->where('session_id', $session_id);
				$this->db->where('class_id', $classesinfo->class_id);
				$feeamountinfo = $this->db->get('fee_amount')->row();

				if($feeamountinfo){
					$amount = $feeamountinfo->amount;
				}else{
					$amount = 0;
				}
				$studentStr .= '<td><input
				 name="fee_type_name['.$student->student_id.']['.$value->fee_type_id.']" class="form-control" type="text" value="'.$amount.'"></td>';
			}
			

			$studentStr .= '</tr></tbody>';
		}
		$studentStr .= '</table>';
		echo $studentStr;
	}

	function edit(){
		check_permission('admin-edit-fee-chalan');
		$chalan_type_id = intval($this->input->get('id'));

		$this->db->where('chalan_type_id', $chalan_type_id);
		$info = $this->db->get('chalan_type')->row();
		$this->template_data['info'] = $info;

		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$this->load->view('fee_chalan_edit', $this->template_data);
	}

function save(){

		$chalandata = array();
		$amount = 0;
		$issue_date = DateTime::createFromFormat('d/m/Y',$this->input->post('issue_date'));
		$issuedate = $issue_date->format('Y-m-d');
		
		$due_date = DateTime::createFromFormat('d/m/Y',$this->input->post('due_date'));
		$duedate = $due_date->format('Y-m-d');
		$arrMonth  = explode('-', $this->input->post('fee_month'));
		$fee_month = $arrMonth[1].'/'.$arrMonth[0];
		$session_id = $this->session->userdata('member_sessionid');
		
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');

		$feeTypes = $this->input->post('fee_type_name');
		check_permission('admin-add-fee-chalan');
		foreach ($feeTypes as $key => $feeTypeIds) {

			$this->db->trans_begin();
			$data = array(
			'status' => 1,
			);

			$this->db->where('student_id', $key);
			$this->db->where("session_id", $session_id);
			$this->db->update('students', $data);

			$this->db->where('student_id', $key);
			$this->db->where("session_id", $session_id);
			$this->db->update('student_class', $data);
			$this->db->trans_complete();
			
			$this->db->where('student_id',$key);
			$studentinfo = $this->db->get('students')->row();	
			
			if($studentinfo){
				$discounted_amount = $studentinfo->discounted_amount;
			}else{
				$discounted_amount = 0;
			}

			foreach ($feeTypeIds as $key2 => $value) {
			
			$this->db->where('fee_type_id',$key2);
			$this->db->where('is_monthly_fee',1);
			$isDiscount = $this->db->get('fee_type')->row();

			if($isDiscount){
				$discount = $discounted_amount;
			}else{
				$discount = 0;
			}
				
		
		$this->db->where('fee_type_id', $key2);
		$this->db->where('student_id', $key);
		$this->db->where('fee_month', $fee_month);
		$feeChalaninfo = $this->db->get('fee_chalan')->row();
			
		if(empty($feeChalaninfo)){	
			$data = array(
				'fee_type_id' => $key2,
				'student_id' => $key,
				'issue_date' => $issuedate,
				'due_date' => $duedate,
				'fee_month' => $fee_month,
				'amount' => $value,
				'discount' => $discount,
				'status' => 'unpaid',
				'created_date' => $date,
				'user_id' => $user_id
				);

			
			$this->db->insert('fee_chalan', $data);
			$new_chalan_id = $this->db->insert_id();
		}
			}
		}
		
	json_response(array('success' => TRUE, 'msg' => 'Add Chalan Success'));

}

function download(){
		 $data =  $this->data();
	     $this->template_data['data'] = $data;			
		 $this->load->view('fee_chalan_single_pdf', $this->template_data);
	}
	
function data(){
		$student_data = array();
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');
		$student_id = $_GET['id'];
		$keyword = '';
		$result = $this->db->query('SELECT t1.cls_sec_id,t2.student_id,t2.parent_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE  t1.status=1 AND t2.student_id ='.$student_id.' and t1.student_id ='.$student_id)->result(); 

		$response->recordsTotal = count((array)$result);
		$response->student_data = array();

	foreach($result as $row)
		{
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
		$fee_chalan = $this->db->get('fee_chalan')->result();
	    //exit;
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

