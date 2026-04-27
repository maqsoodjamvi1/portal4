<?php
namespace App\Controllers\Admin;


/**
 * Fee Chalan Family Wise Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */
 


class Fee_chalan_familywise extends MY_Controller {

function __construct(){
		parent::__construct();
		check_permission('admin-fee-chalan-familywise');
	}

/**
 * Index Page for this controller.
 */
public function index()
{
  $data =  $this->data();
  $this->template_data['data'] = $data;
  $this->load->view('fee_chalan_familywise', $this->template_data);
}
	
function data(){
		
	$response = new stdClass;
	$response->draw = $this->input->post('draw');

	$get_fee_month = $this->input->get('fee_month');
	$campus_id = $this->session->userdata('member_campusid');
	$keyword = '';
	
	$result = $this->db->query('SELECT * FROM parents where parent_id IN(select parent_id from students where status=1 and campus_id='.$campus_id.' )')->result(); 

	//print_r($result);
	$response->recordsTotal = count((array)$result);
	$response->student_data = array();
	$student_data = array();	
	foreach($result as $row)
	{
	  
	  $max_chalan_info = $this->db->query('SELECT MAX(chalan_id) as chalan_id FROM fee_chalan WHERE student_id IN (SELECT student_id FROM students WHERE parent_id = '.$row->parent_id.') AND NOT fee_type_id = 0 AND status="unpaid"')->row();  
	  
	  if(!empty($max_chalan_info->chalan_id)){
	  	
	  $chalan_info = $this->db->query('SELECT * FROM fee_chalan where status="unpaid" AND chalan_id='.$max_chalan_info->chalan_id)->row();
	
		if($chalan_info){

	 	$unpaind_total = $this->db->query("SELECT sum(fee.amount)- sum(fee.discount) as total FROM fee_chalan fee where student_id IN(select student_id from students where status=1 and parent_id=".$row->parent_id.") and status='unpaid' ")->row();

	 if($unpaind_total->total){
			
		$this->db->where('campus_id',  $campus_id);
		$campusinfo = $this->db->get('campus')->row();

		$systemInfo = getSchoolInfo();

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
		
 		
	$fee_chalan = $this->db->query("SELECT sum(amount) as amount,sum(discount) as discount, status,fee_type_id, fee_month from  fee_chalan  where status = 'unpaid' and student_id IN (select student_id from students where status=1 and parent_id =".$row->parent_id.") group by fee_month,fee_type_id")->result();

		
	  $FChalanNum = $this->db->query('select chalan_id from fee_chalan where student_id IN (select student_id from students where status=1 and parent_id ='.$row->parent_id.') AND status="unpaid"  ORDER BY chalan_id DESC')->row();

	    $student_fee = array();
		foreach($fee_chalan as $chalanvalue){

			$feeType_info = $this->db->query('SELECT * from fee_type where fee_type_id='.$chalanvalue->fee_type_id)->row();
			$feeTypeName = $feeType_info->fee_type_name;
			$discount = $chalanvalue->discount;
		
			$student_fee[] = array(
				'amount' => $chalanvalue->amount,
				'discount' => $discount,
				'fee_month' => $feeTypeName." (".$chalanvalue->fee_month.")",
			);	
		   			   
		}

		
		$fee_fine = array();
		
		$issue_date = date_create_from_format('Y-m-d', $chalan_info->issue_date);
		$issue_date = date_format($issue_date, 'j-M-Y');
		
		$due_date = date_create_from_format('Y-m-d', $chalan_info->due_date);
		$due_date = date_format($due_date, 'j-M-Y');
		
		// $fee_month = date_create_from_format('m/Y', $chalan_info->fee_month);
		// $fee_month = date_format($fee_month, 'M-Y'); 
		$fee_monthArr = explode('/', $chalan_info->fee_month); 
		$dt = DateTime::createFromFormat('!m', $fee_monthArr[0]);
		//echo ;
		//print_r($fee_month);
		$fee_month = $dt->format('F').'-'.$fee_monthArr[1]; 	

		$student_names = $this->db->query('select student_id,reg_no,first_name,last_name,status from students where status=1 and campus_id='.$campus_id.' and parent_id ='.$row->parent_id.' order by class_id DESC')->result_array();	

		$stdinfo = '';
		$sessionid = $this->session->userdata('member_sessionid');
		foreach ($student_names as $key => $stddata) {
			
			$classSecInfo = $this->db->query('SELECT cls_sec_id FROM student_class WHERE session_id='.$sessionid.' and student_id ='.$stddata['student_id'])->row();
			$sectionclassname = '';
			
			if($classSecInfo){
				$classSection = getClassSection($classSecInfo->cls_sec_id);
				if($classSection){
					$sectionclassname = $classSection['sectionclassname'];
				}
			}

			$stdinfo .= $stddata['first_name']." ".$stddata['last_name']." <strong>". $sectionclassname."</strong>, ";
		}
			$student_data[] = array(	  
			'campus_name' => $campus_name,
			'chalan_no' => $FChalanNum->chalan_id,
			'system_name' => $systemInfo->system_name,
			'logo' => $systemInfo->logo,
			'location' => $location,	
			'bank_name' => $bank_name,	
			'bank_address' => $bank_address,	
			'bank_code' => $bank_code,	
			'bank_acc' => $bank_acc,
			'chalan_h_msg' => $chalan_h_msg,
			'chalan_f_msg' => $chalan_f_msg,	
		  'stdinfo' => rtrim($stdinfo),//$student_info->reg_no,
			'f_name' => $row->f_name,
			'family_no' => $row->parent_id,
			'father_contact' => $row->father_contact,
			'mother_contact' => $row->mother_contact,
			'emergency_contact' => $row->emergency_contact,
			'fee_month' => $fee_month,
			'issue_date' => $issue_date,
			'due_date' => $due_date,
			'student_fee'=> $student_fee,
			'fee_fine' =>$fee_fine
			);
		   
			}
		}
	 	}
		}
   		return $student_data;	
	}

/**
 * Index Page for this controller.
*/

public function single_copy()
{
  $data =  $this->data();
  $this->template_data['data'] = $data;
  $this->load->view('single_copy_fee_chalan_familywise', $this->template_data);
}

}
// end this file