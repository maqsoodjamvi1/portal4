<?php
namespace App\Controllers\Admin;



/**
 * Fee Chalan Single Copy Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Fee_chalan_thermal_copy extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-fee-chalan-pdf');
	}

	/**
	 * Index Page for this controller.
	 */

public function index()
{
	$campus_id = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	$schoolinfo = getSchoolInfo();

	$currentrole = currentUserRoles();

	if(in_array(5, $currentrole)){
		$sectionsclassinfo = teacherSubjectSections();
	}else{
		$sectionsclassinfo = userClassSections();
	}

	$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
	  		
  $data =  $this->data();
  $this->template_data['data'] = $data;
  $this->load->view('fee_chalan_thermal_copy', $this->template_data);
}

function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$cls_sec_id = $this->input->get('cls_sec_id');
		$feeMonth = $this->input->get('fee_month');
		if($feeMonth){
		 $feeMonthArr = explode('-', $feeMonth);
		 $get_fee_month = $feeMonthArr[1].'/'.$feeMonthArr[0];
		 //print_r($get_fee_month);
		}else{
			$get_fee_month = '';
		}
		$campus_id = $this->session->userdata('member_campusid');
		$student_data = array();

		$keyword = '';
	if(empty($cls_sec_id)){
		return;
	}	

	if($cls_sec_id){
		$result = $this->db->query('SELECT t1.cls_sec_id,t2.student_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE t1.student_id = t2.student_id and t1.status=1 and t2.campus_id='.$campus_id.' AND t1.cls_sec_id='.$cls_sec_id.' order by t1.cls_sec_id asc')->result();
	}else{	
		$result = $this->db->query('SELECT t1.cls_sec_id,t2.student_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE t1.student_id = t2.student_id and t1.status=1 and t2.campus_id='.$campus_id.' order by t1.cls_sec_id asc')->result(); 
	}

	$response->recordsTotal = count((array)$result);
		$response->student_data = array();

	foreach($result as $row)
	{
		if($get_fee_month){
			$whereclause = " AND fee_month='".$get_fee_month."'";
		}else{
		$whereclause = '';
		}

		if($get_fee_month){
			$this->db->where('fee_month',  $get_fee_month);	
		}

		$where = "student_id='".$row->student_id."' AND status='unpaid'";
		$this->db->where($where);
		$this->db->order_by("issue_date", "desc");
		$chalan_info = $this->db->get('fee_chalan')->row();
	
		$unpaind_total = $this->db->query("SELECT sum(fee.amount)- sum(fee.discount) as total FROM fee_chalan fee where student_id=".$row->student_id." and status='unpaid' ".$whereclause)->row(); 

		if($unpaind_total->total){
			
			$classSectioninfo = getClassSection($row->cls_sec_id);

			$this->db->where('parent_id',  $row->parent_id);
			$parentinfo = $this->db->get('parents')->row();


			$this->db->where('campus_id',  $row->campus_id);
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

	//$where = "student_id='".$row->student_id."' AND status='unpaid'";
	$where = '';			
	if($get_fee_month){
		$where .= " AND fee_month='".$get_fee_month."'";
	}
	
	$fee_chalan_value = $this->db->query('SELECT * FROM fee_chalan where status="unpaid" AND student_id='.$row->student_id.' '.$where.' order by issue_date desc')->result(); 
	 
	

	$FChalanNum = $this->db->query('select chalan_id from fee_chalan where student_id='.$row->student_id.' AND status="unpaid"  ORDER BY chalan_id ASC')->row();

	$student_fee = array();
	foreach($fee_chalan_value as $chalanvalue){ 

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



	if(isset($parentinfo->father_contact)){
		$father_contact = $parentinfo->father_contact;
	}else{
		$father_contact = '';
	}

		
	if(isset($parentinfo->mother_contact)){
		$mother_contact = $parentinfo->mother_contact;
	}else{
		$mother_contact = '';
	}

	if(isset($parentinfo->f_name)){
		$f_name = $parentinfo->f_name;
	}else{
		$f_name = '';
	}

	if(isset($parentinfo->parent_id)){
		$parent_id = $parentinfo->parent_id;
	}else{
		$parent_id = '';
	}	

		$issue_date = date_create_from_format('Y-m-d', $chalan_info->issue_date);
		$issue_date = date_format($issue_date, 'j-M-Y');

		$due_date = date_create_from_format('Y-m-d', $chalan_info->due_date);
		$due_date = date_format($due_date, 'j-M-Y');

		// $fee_monthArr = explode('/', $chalan_info->fee_month); 
		// $dt = DateTime::createFromFormat('!m', $fee_monthArr[0]);
		// $fee_month = $dt->format('F').'-'.$fee_monthArr[1]; 
		$month_name = '';
		if($chalan_info->fee_month){
			list($month, $year) = explode('/', $chalan_info->fee_month);
        	$month_name = DateTime::createFromFormat('!m', $month)->format('F');	
        }
 
		//if($parentinfo){
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
		'student_id' => $row->student_id,
	    'reg_no' => $row->reg_no,
		'student_name' => $row->first_name." ".$row->last_name,
		'family_no' => $parent_id,
		'f_name' => $f_name,
		'class_name' => $classSectioninfo['sectionclassname'],
		'fee_month' => $month_name,
		'issue_date' => $issue_date,
		'due_date' => $due_date,
		'student_fee'=> $student_fee,
		'fee_fine' =>$fee_fine

		);

	//}
	
	}
   }

   return $student_data;
}
}
// end this file