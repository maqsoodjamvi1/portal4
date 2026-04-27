<?php
namespace App\Controllers\Frontend;



/**
 * Fee Chalan Single Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
*/


class Fee_chalan_sibling extends MY_Controller {

	function __construct(){
		parent::__construct();
	}

	/**
	 * Index Page for this controller.
	 */

public function index()
{
	$data =  $this->data();
	$this->template_data['data'] = $data;	
	$this->load->view('templates/header', $this->template_data);				
	$this->load->view('fee_chalan_sibling_pdf', $this->template_data);
	$this->load->view('templates/footer', $this->template_data);
}

	
function data(){
		$student_data = array();
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');
		$parent_id = $_GET['parent_id'];
		$schoolinfo = getSchoolInfoFront();
		$keyword = '';

	$stdresult = $this->db->query('SELECT student_id from students where parent_id='.$parent_id)->result();
	foreach($stdresult as $studentinfo){
		
		$result = $this->db->query('SELECT t1.cls_sec_id,t2.student_id,t2.parent_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE  t1.status=1 AND t2.student_id ='.$studentinfo->student_id.' and t1.student_id ='.$studentinfo->student_id)->result(); 


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
		$this->db->order_by("fee_month", "asc");
		$fee_chalan = $this->db->get('fee_chalan')->result();
	    //exit;

		$FChalanNum = $this->db->query('select chalan_id from fee_chalan where student_id='.$row->student_id.' AND status="unpaid" ORDER BY chalan_id DESC')->row();


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
		'system_name' => $schoolinfo['system_name'],
		'chalan_no' => $FChalanNum->chalan_id,
		'logo' => $schoolinfo['logo'],
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
}
   return $student_data;		
}

}
// end this file

