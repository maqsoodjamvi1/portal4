<?php

/**
 * Fee Chalan Pay Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */

defined('BASEPATH') OR exit('No direct script access allowed');
class Fee_chalan_pay1 extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-fee-chalan-pay');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$currentrole = currentUserRoles();
		
		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
			//print_r($sectionsclassinfo);
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	

	  $this->load->view('fee_chalan_pay', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.chalan_id) as ccount', FALSE);
		$this->db->from('fee_chalan A');
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('fee_chalan A');
		$this->db->order_by('A.chalan_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();
		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
		$this->db->where('fee_type_id', $row->fee_type_id);
		$fee_type_info = $this->db->get('fee_type')->row();

		$this->db->where('student_id',  $row->student_id);
		$student_info = $this->db->get('students')->row();

			$data = array();
			$data['id'] = $row->chalan_id;
			$data['student_name'] = $student_info->first_name." ".$student_info->last_name;
			$data['due_date'] = $row->due_date;
			$data['issue_date'] = $row->issue_date;
			$data['fee_month'] = $row->fee_month;
			$data['amount'] = $row->amount;
			$data['status'] = $row->status;
			$data['discount'] = $row->discount;
			$data['paiddate'] = $row->paid_date;
			$data['fee_name'] = $fee_type_info->fee_type_name;
						
			$response->data[] = $data;

		}

		return $response;
		//$this->output->set_output(json_encode($response));

	}

	function add(){
		check_permission('admin-add-fee-chalan-pay');
		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$this->load->view('fee_chalan_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-fee-chalan-pay');
		$chalan_type_id = intval($this->input->get('id'));
		
		$this->db->where('chalan_type_id', $chalan_type_id);
		$info = $this->db->get('chalan_type')->row();
		$this->template_data['info'] = $info;
		
		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;
		$this->load->view('fee_chalan_edit', $this->template_data);
	}

	function get_students_list(){
		
		$campus_id = $this->session->userdata('member_campusid');
		$session_id = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		$this->db->where('campus_id', $campus_id);
		$campus_info = $this->db->get('campus')->row();
		
		$feeList ='';
		$student_id = $this->input->post('student_id');
		$reg_no = $this->input->post('reg_no');
		

	$campus_id = (int) $campus_id;
	$session_id = (int) $session_id;
	$parent_id = (int) ($parent_id ?? 0);

	if($student_id){
		$parentinfo = $this->db->select('parent_id')->from('students')->where('student_id', (int) $student_id)->get()->row();
		$parent_id = (int) ($parentinfo->parent_id ?? 0);
	}else if($reg_no){
		$parentinfo = $this->db->select('parent_id')->from('students')->where('reg_no', $reg_no)->get()->row();
		$parent_id = (int) ($parentinfo->parent_id ?? 0);
	}else{
		$parent_id = (int) $this->input->post('parent_id');
	}
	

	$studentslistinfo = $this->db->from('students')->where('campus_id', $campus_id)->where('status', 1)->where('parent_id', $parent_id)->get()->result();

	$this->db->select('SUM(fc.amount - fc.discount) AS feeTotal', false);
	$this->db->from('fee_chalan fc');
	$this->db->join('students s', 's.student_id = fc.student_id');
	$this->db->where('fc.status', 'unpaid');
	$this->db->where('s.campus_id', $campus_id);
	$this->db->where('s.status', 1);
	$this->db->where('s.parent_id', $parent_id);
	$unpaidsuminfo = $this->db->get()->row();

	$this->db->select('SUM(fc.amount - fc.discount) AS feeTotal', false);
	$this->db->from('fee_chalan fc');
	$this->db->join('students s', 's.student_id = fc.student_id');
	$this->db->where('fc.status', 'paid');
	$this->db->where('fc.paid_date', date('Y-m-d'));
	$this->db->where('s.campus_id', $campus_id);
	$this->db->where('s.status', 1);
	$this->db->where('s.parent_id', $parent_id);
	$paidsuminfo = $this->db->get()->row();

	$this->db->select('SUM(fc.amount - fc.discount) AS discountedTotal', false);
	$this->db->from('fee_chalan fc');
	$this->db->join('students s', 's.student_id = fc.student_id');
	$this->db->where('fc.status', 'discounted');
	$this->db->where('fc.paid_date', date('Y-m-d'));
	$this->db->where('s.campus_id', $campus_id);
	$this->db->where('s.status', 1);
	$this->db->where('s.parent_id', $parent_id);
	$discountedsuminfo = $this->db->get()->row();
	
	$this->db->select('SUM(fc.amount) AS finetotal', false);
	$this->db->from('fee_chalan fc');
	$this->db->join('students s', 's.student_id = fc.student_id');
	$this->db->where('fc.status', 'unpaid');
	$this->db->where('fc.fee_type_id', 0);
	$this->db->where('s.campus_id', $campus_id);
	$this->db->where('s.status', 1);
	$this->db->where('s.parent_id', $parent_id);
	$finesuminfo = $this->db->get()->row();

	$this->db->select('SUM(fc.amount) AS finetotal', false);
	$this->db->from('fee_chalan fc');
	$this->db->join('students s', 's.student_id = fc.student_id');
	$this->db->where('fc.status', 'paid');
	$this->db->where('fc.fee_type_id', 0);
	$this->db->where('fc.paid_date', date('Y-m-d'));
	$this->db->where('s.campus_id', $campus_id);
	$this->db->where('s.status', 1);
	$this->db->where('s.parent_id', $parent_id);
	$paidfinesuminfo = $this->db->get()->row();

		$totalpaidwithfine = 0;
		$totalUnpaidfee = 0;
		$unpaidfeesum = 0;
		$unpaidfinesum = 0;
		$paidfeesum = 0;
		$paidfinesum = 0;
		$discountedsum = 0;
		
		if($unpaidsuminfo){
			$unpaidfeesum = $unpaidsuminfo->feeTotal;
		}

		if($finesuminfo){
			$unpaidfinesum = $finesuminfo->finetotal;
		}

		if($paidsuminfo){
			$paidfeesum = $paidsuminfo->feeTotal;
		}

		if($paidfinesuminfo){
			$paidfinesum = $paidfinesuminfo->finetotal;
		}

		if($discountedsuminfo){
			$discountedsum = $discountedsuminfo->discountedTotal;
		}

		$totalpaidwithfine = ($paidfeesum + $paidfinesum);

		$totalUnpaidfee = ($unpaidfeesum + $unpaidfinesum);

		$totalFeeAmounts = $totalpaidwithfine + $totalUnpaidfee + $discountedsum;
		
		$fine = 0;
	
	if(isset($studentslistinfo)){

		//$this->db->where('parent_id', $parent_id);
		$parentinfo = $this->db->from('parents')->where('parent_id', $parent_id)->get()->row();
		
		if(empty($parentinfo)){
			echo "Family information not found";
			exit;
		}

		$feeList .= "<a style='margin: 30px 0;margin-bottom: 10px;float:right;' class='btn btn-primary float-end' target='_blank' href='admin.php#/fee_history_report?parent_id=".$parentinfo->parent_id."'> Fee History of: ".$parentinfo->f_name."</a> &nbsp;<a style='margin: 30px 0;margin-bottom: 10px;float:right;' data-bs-toggle='modal' data-bs-target='#updatediscount'  class='btn btn-primary float-end'  href='#'>Update Student Fee</a> <a style='margin: 30px 0;margin-bottom: 10px;float:right;'  class='btn btn-primary float-end' id='payAllFee' data-parentID=".$parentinfo->parent_id."  href='#'>Pay All</a> <a style='margin: 30px 0;margin-bottom: 10px;float:right;'  class='btn btn-primary float-end' id='sendSms' data-parentID=".$parentinfo->parent_id."  href='#'>Send SMS</a>";
		
		$this->db->select('SUM(fc.amount) AS total', false);
		$this->db->from('fee_chalan fc');
		$this->db->join('students s', 's.student_id = fc.student_id');
		$this->db->where('fc.status', 'unpaid');
		$this->db->where('s.campus_id', $campus_id);
		$this->db->where('s.parent_id', (int) $parentinfo->parent_id);
		$studentAmountInfo = $this->db->get()->row();
		
		if(empty($studentAmountInfo->total)){
			$feeList .= "<a style='margin: 30px 0;margin-bottom: 10px;float:right;' data-bs-toggle='modal' data-bs-target='#payAdvanceFee'  class='btn btn-primary float-end'  href='#'>Pay Advance Fee</a>";
		}
		$feeList .= '<div id="payAdvanceFee" class="modal fade" role="dialog">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">   
		        <h5 class="modal-title">Student Advance Fee</h5>
		        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
		      </div>
		      <div class="modal-body"><form id="AdvanceFee"><div class="row">';
		    $totalFee = 0;
		    foreach ($studentslistinfo as $key => $value) {
		     	
		     	$studentFeeInfo = $this->db->from('fee_chalan')->where('student_id', (int) $value->student_id)->where('fee_type_id', 194)->get()->row();
		     	$advanceFeeAmount = 0;
		     	if($studentFeeInfo){
		     		if($studentFeeInfo->amount > 0){
		     			$advanceFeeAmount = $studentFeeInfo->amount;
		     		}
		     	}

		     	$studentClassRow = $this->db->select('cls_sec_id')->from('student_class')->where('student_id', (int) $value->student_id)->where('session_id', $session_id)->get()->row();
		     	$studentsClassInfo = $studentClassRow ? $this->db->from('class_section')->where('cls_sec_id', (int) $studentClassRow->cls_sec_id)->get()->row() : null;
		     
		     if($studentsClassInfo){
		     	
		     	$ClassesInfo = $this->db->from('classes')->where('class_id', (int) $studentsClassInfo->class_id)->get()->row();

		     	$SectionInfo = $this->db->from('sections')->where('section_id', (int) $studentsClassInfo->section_id)->get()->row();


		       $feeList .= '<div class="col-lg-6 mb-2">'.$value->first_name." ".$value->last_name." ".$ClassesInfo->class_name."</div>";
		       
		       $feeList .= '<div class="col-lg-6 mb-2"><input type="hidden" class="form-control studentIDs" value="'.$value->student_id.'" name="student_id[]"><input type="text" class="form-control discounts" value="'.($advanceFeeAmount).'" name="advance_amount[]"></div><div class="col-lg-6 text-start mb-2"></div><div class="col-lg-6 text-start mb-2"></div>';
		       //$totalFee = $totalFee + ($studentClassInfo->amount-$value->discounted_amount);
		       //$feeList .= '<div class="col-lg-6">Total Fee</div><div class="col-lg-6">'.$totalFee.'</div>';
		      
		       }  
		   }
		    $feeList .= '</div></form></div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		         <button type="button" id="advFeePay" class="btn btn-primary">Pay Advance Fee</button>
		      </div>
		    </div>
		  </div>
		</div>';

		$feeList .= '<div id="updatediscount" class="modal fade" role="dialog">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">   
		        <h5 class="modal-title">Update Student Fee</h5>
		        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
		      </div>
		      <div class="modal-body"><form id="discountUpdate"><div class="row">';
		    $totalFee = 0;
		    foreach ($studentslistinfo as $key => $value) {
		     	
		     	$studentClassRow = $this->db->select('cls_sec_id')->from('student_class')->where('student_id', (int) $value->student_id)->where('session_id', $session_id)->get()->row();
		     	$classSectionRow = $studentClassRow ? $this->db->select('class_id')->from('class_section')->where('cls_sec_id', (int) $studentClassRow->cls_sec_id)->get()->row() : null;
		     	$monthlyFeeType = $this->db->select('fee_type_id')->from('fee_type')->where('is_monthly_fee', 1)->where('s_flag', 1)->where('system_id', (int) $schoolinfo->system_id)->get()->row();
		     	$studentClassInfo = ($classSectionRow && $monthlyFeeType) ? $this->db->from('fee_amount')
		     		->where('campus_id', $campus_id)
		     		->where('session_id', $session_id)
		     		->where('fee_type_id', (int) $monthlyFeeType->fee_type_id)
		     		->where('class_id', (int) $classSectionRow->class_id)
		     		->get()->row() : null;

		     	$studentClassRow = $this->db->select('cls_sec_id')->from('student_class')->where('student_id', (int) $value->student_id)->where('session_id', $session_id)->get()->row();
		     	$studentsClassInfo = $studentClassRow ? $this->db->from('class_section')->where('cls_sec_id', (int) $studentClassRow->cls_sec_id)->get()->row() : null;
		     
		     if($studentsClassInfo){
		     	
		     	$ClassesInfo = $this->db->from('classes')->where('class_id', (int) $studentsClassInfo->class_id)->get()->row();

		     	$SectionInfo = $this->db->from('sections')->where('section_id', (int) $studentsClassInfo->section_id)->get()->row();


		       $feeList .= '<div class="col-lg-6 mb-2">'.$value->first_name." ".$value->last_name." ".$ClassesInfo->class_name."</div>";
		       
		       $feeList .= '<div class="col-lg-6 mb-2"><input type="hidden" class="form-control studentIDs" value="'.$value->student_id.'" name="student_id[]"><input type="hidden" class="form-control studentClassFee" value="'.$studentClassInfo->amount.'" name="student_class_fee[]"><input type="text" class="form-control discounts" value="'.($studentClassInfo->amount-$value->discounted_amount).'" name="discounted_amount[]"></div><div class="col-lg-6 text-start mb-2"></div><div class="col-lg-6 text-start mb-2"></div>';
		       $totalFee = $totalFee + ($studentClassInfo->amount-$value->discounted_amount);
		       $feeList .= '<div class="col-lg-6">Total Fee</div><div class="col-lg-6">'.$totalFee.'</div>';
		      
		       }  
		   }
		    $feeList .= '</div></form></div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		         <button type="button" id="discUpdate" class="btn btn-primary">Update</button>
		      </div>
		    </div>
		  </div>
		</div>';
		 $feeList .= "<script>
		 $('#advFeePay').click(function(){
		         $.ajax({
		            url: 'admin.php?c=fee_chalan_pay&m=AdvFee',
		            type: 'POST',
		            data:$('#AdvanceFee').serialize(),
		            success:function(res){
		             toastr.success('Updated Successfully');
		         	}
		         });

		        $('#updatediscount').modal('hide');
			});
		$('#discUpdate').click(function(){
		         $.ajax({
		            url: 'admin.php?c=students&m=updateDiscounts',
		            type: 'POST',
		            data:$('#discountUpdate').serialize(),
		            success:function(res){
		             toastr.success('Updated Successfully');
		         	}
		         });

		        $('#updatediscount').modal('hide');
			});

		$('#payAllFee').click(function(){
			 if(confirm('Are you sure you want to update this?')){
				var parentID = $(this).data('parentid');
				var datePaid = $('#datePaid').val();
				
		         $.ajax({
		            url: 'admin.php?c=fee_chalan_pay&m=payFeeAll',
		            type: 'POST',
		            data:{parent_id: parentID,datePaid:datePaid},
		            success:function(res){
		             $('#feetypeinfo').html('All Fee Paid Successfully');
		             toastr.success('Updated Successfully');
		         	}
		         });

		        $('#updatediscount').modal('hide');
		    }else{
		    	return false;
		    }
		});	

		$('#sendSms').click(function(){
			 if(confirm('Are you sure you want to update this?')){
				var parentID = $(this).data('parentid');
				var datePaid = $('#datePaid').val();
				
		         $.ajax({
		            url: 'admin.php?c=fee_chalan_pay&m=sendSMS',
		            type: 'POST',
		            data:{parent_id: parentID,datePaid:datePaid},
		            success:function(res){
		             var json = $.parseJSON(res);	
		             if(json.success){
		              toastr.success('Message Sent Successfully');
		         	 }else{
		         	 	toastr.error(json.msg);
		         	 }
		         	}
		         });

		        //$('#updatediscount').modal('hide');
		    }else{
		    	return false;
		    }
		});	
		</script>";

		$feeList .= '<table class="table table-bordered" style="width:100%;margin-bottom:20px;"><tr style="background: #367fa9;color: #fff;font-weight: normal;"><th style="color: #fff;">Student</th><th  style="color: #fff;">Fee Type</th><th  style="color: #fff;">Amount</th><th style="color: #fff;" colspan="1">Operation</th></tr>';
		$total=0;
		$totalfine=0;
		$subtotal =0;
		$fine=0;
		$i=1;

	foreach ($studentslistinfo as $key => $students_info) { 

		$studentClassRow = $this->db->select('cls_sec_id')->from('student_class')->where('student_id', (int) $students_info->student_id)->where('session_id', $session_id)->get()->row();
		$stdClassInfo = $studentClassRow ? $this->db->from('class_section')->where('cls_sec_id', (int) $studentClassRow->cls_sec_id)->get()->row() : null;

		$class_name = '';

		if($stdClassInfo){
		     	
	     	$ClsInfo = $this->db->from('classes')->where('class_id', (int) $stdClassInfo->class_id)->get()->row();

	     	$SecInfo = $this->db->from('sections')->where('section_id', (int) $stdClassInfo->section_id)->get()->row();

	     	$class_name = $ClsInfo->class_name;
		}
		//echo $class_name;

	  	$where = "student_id='".$students_info->student_id."' AND status='unpaid'";
		$this->db->where($where);
		$fee_chalan = $this->db->get('fee_chalan')->result();	
		foreach($fee_chalan as $row){
		
		$this->db->where('fee_type_id', $row->fee_type_id);
		$fee_type = $this->db->get('fee_type')->row();
		
		$total = $total + $row->amount;
		$currmonth = date("m/Y");
		
		
		if($fee_type->is_monthly_fee == 1 && $row->fee_month == $currmonth){
			$date1=date_create(date("Y-m-d"));
			$date2 = date_create($row->due_date);
			$diff=date_diff($date1,$date2);
			$days =  $diff->format("%R%a");

			if($days < 0){
				$fine = abs(($days*$campus_info->late_fee_fine));
			} 
		}
		 //echo $fine;
		if($fee_type->is_monthly_fee == 1){
			$total = ($total - $row->discount);
			$subtotal = $total;
			}
			$totalfine = ($totalfine + $fine);
			$feeMonth = '';
			
			if ($row->fee_month) {
			    $FeeMonthParts = explode('-', $row->fee_month); // ['2025', '03']

			    if (count($FeeMonthParts) === 2) {
			        $monthNum = (int) $FeeMonthParts[1]; // '03' => 3
			        $yearShort = substr($FeeMonthParts[0], -2); // '2025' => '25'

			        // Convert numeric month to short name (e.g., 3 => 'Mar')
			        $monthName = date('M', mktime(0, 0, 0, $monthNum, 10)); // 'Mar'

			        $feeMonth = $monthName . ' ' . $yearShort; // 'Mar 25'
			    } else {
			        $feeMonth = 'Invalid Format';
			    }
			}

			
		$nmonth = date("d M Y", strtotime($row->due_date));
		$profile_photo = '';
		
		$imgurl = FCPATH."uploads/".$students_info->profile_photo;
			
		if($students_info->profile_photo){
			if(file_exists($imgurl)){

				$profile_photo = "<img style='width:50px;height:50px;text-align: center;display: block;border-radius: 30px;margin: 0 auto;' src='".base_url("uploads/".$students_info->profile_photo)."' >";
						
			}else{

				$profile_photo = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
			}
			}else{
				$profile_photo = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
			}
					
		$feeList .= "<tr id='feepaid'><th class='leftdate'>".$profile_photo."</th><th class='leftdate'>".$students_info->first_name." ".$students_info->last_name."<br> ".$class_name."<br> ".$fee_type->fee_type_name." of ".$feeMonth."<br>Due Date: ".$nmonth."</th><th class='rightdata'><input type='hidden' id='student_id".$i."' name='student_id' value='".$students_info->student_id."' />".($row->amount-$row->discount)."/-</th>";
		
		$feeList .= '<td><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payfee" data-feeamount="'.($row->amount-$row->discount).'" data-whatever="'.$row->chalan_id.'" data-fine="'.$fine.'" data-student_id="'.$students_info->student_id.'">Pay</button> <a class="btn btn-primary" href="/admin.php#/fee_chalan_single?m=add&id='.$students_info->student_id.'">Generate Chalan</a></td>';

		
		$feeList .= '</tr>';
		$fine=0;
		$i++;
	}
		
	$Year = date('Y');
	$month = date('m');
	$FeeMonth = '';
		
	$paidfee = $this->db->query(
		'SELECT * FROM fee_chalan WHERE student_id = ? AND status != ? AND YEAR(updated_date) = ? AND MONTH(updated_date) = ?',
		[(int) $students_info->student_id, 'unpaid', (int) $Year, (int) $month]
	)->result();
			
	foreach ($paidfee as $key => $value) {

		if (!empty($value->fee_month)) {

	        // fee_month is in format YYYY-MM, e.g., 2025-03
	        $FeeMonth = explode('-', $value->fee_month); // ['2025', '03']
	        $monthNum = (int) $FeeMonth[1]; // 3
	        $year = substr($FeeMonth[0], -2); // '25'

	        // Convert month number to short name
	        $month = date('M', mktime(0, 0, 0, $monthNum, 1));

	        // e.g., 'Mar 25'
	        $feeMonth = $month . ' ' . $year;

	    } else {
	        $feeMonth = '';
	    }
						
		//print_r($value);
			$pmonth = date("d M Y", strtotime($value->paid_date));

			$profile_photo = '';
		
		$imgurl = FCPATH."uploads/".$students_info->profile_photo;
			
		if($students_info->profile_photo){
			if(file_exists($imgurl)){

				$profile_photo = "<img style='width:50px;height:50px;text-align: center;display: block;border-radius: 30px;margin: 0 auto;' src='".base_url("uploads/".$students_info->profile_photo)."' >";
						
			}else{

				$profile_photo = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
			}
			}else{
				$profile_photo = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
			}
		
		
			$this->db->where('fee_type_id', $value->fee_type_id);
			$fee_type = $this->db->get('fee_type')->row();

		$feeList .= "<tr><td class='leftdate'>".$profile_photo."</td><td class='leftdate' style='text-transform: capitalize;'>".$students_info->first_name." ".$students_info->last_name."<br> ".$class_name."<br>".$fee_type->fee_type_name." of ".$feeMonth."<br>".$value->status." At: ".$pmonth."</td><td class='rightdata'>".($value->amount-$value->discount)."/-</td>";
		$timestamp = strtotime($value->updated_date);
		$new_date_format = date('Y-m-d', $timestamp);
		if($new_date_format == date('Y-m-d')){
			$feeList .= '<td style="text-align:center;"><button type="button" class="btn btn-primary" data-bs-toggle="modal" id="unpayfee'.$value->chalan_id.'" data-feeamount="'.($value->amount-$value->discount).'" data-whatever="'.$value->chalan_id.'" data-fine="'.$fine.'" data-student_id="'.$students_info->student_id.'">Make UnPaid</button></td>';
			$feeList .= "<script>
				$('#unpayfee".$value->chalan_id."').click(function(){		
				    if(confirm('Are you sure you want to update this?')){
				        $.ajax({
				            url: 'admin.php?c=fee_chalan_pay&m=updatePaidFee',
				            type: 'POST',
				            data:{challan_id:$value->chalan_id},
				            success:function(res){
				             toastr.success('Updated Successfully');
				             location.reload();
				         	}
				         });
				    }
				    else{
				        return false;
				    }
				});
				// $('#unpayfee".$value->chalan_id."').click(function(){		
		         

				// });
			
			</script>";

		}else{
			$feeList .= "<th>".ucfirst($value->status)."</th>";
		}
			$feeList .= "</tr>";
		}
	} 
		$feeList .= "<tr><td colspan='2' style='border:0 none !important; padding:10px 0px;'></td></tr>";	
		$feeList .= "<tr><td  colspan='2'  style='border:0 none !important; padding:10px 0px;'></td><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #ccc !important;'>Total </th><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #ccc !important;' class='rightdata'>".($totalFeeAmounts)."/-</th></tr>";
		$feeList .= "<tr><td  colspan='2'  style='border:0 none !important; padding:10px 0px;'></td><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #ccc !important;'>Paid</th><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #ccc !important;'  class='rightdata'>".($totalpaidwithfine)."/-</th></tr>";
		$feeList .= "<tr><td  colspan='2' style='border:0 none !important; padding:10px 0px;'></td><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #000 !important;'>Discount</th><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #000 !important;' class='rightdata'>".($discountedsum)."/-</th></tr>";
		$feeList .= "<tr><td  colspan='2'  style='border:0 none !important; padding:10px 0px;'></td><th style='border:0 none !important; padding:10px 0px; border-bottom:1px solid #000 !important;'>Balance</th><th style='border:0 none !important; border-bottom:1px solid #000 !important; padding:10px 0px;' class='rightdata'>".($totalUnpaidfee)."/-</th></tr>

		</table>";

$feeList .= '<div class="modal fade"  id="payfee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Pay Fee</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<label id="totalAmount">Total: </label>'.($totalFeeAmounts).'
      	&nbsp;&nbsp;&nbsp;<label>Paid: </label>'.($totalpaidwithfine).'<br>
      	<label>Discount: </label>'.($discountedsum).'&nbsp;&nbsp;&nbsp;
      	<label>Balance: </label>'.($totalUnpaidfee).'
        <form id="payFeeData">
        <input type="hidden" name="chalan_id" id="ChalanID">
        <input type="hidden" name="PaidDate" id="PaidDate">
        <input type="hidden" name="student_id" id="studentID">
        <input type="hidden" name="fineamount" id="fineamount">
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Fee Amount:</label>
            <input type="text" class="form-control" name="fee_amount" id="feeAmount">
          </div>
          <div class="form-group">
            <label for="message-text" class="col-form-label">Discount:</label>
           	<input type="text" id="discountAmount"  class="form-control" value="0" name="discountamount">
          </div>
          <div class="form-group">
            <label for="message-text" class="col-form-label">Paid Amount:</label>
           	<input type="text" id="PaidAmount"  class="form-control" name="paid_amount">
          </div>
         <div class="form-group">
            <label for="message-text" class="col-form-label">Balance:</label>
           	<input type="text" id="balance" readonly  class="form-control" value="0" name="balance">
          </div>
           <div class="form-group" id="feeFine" style="display:none;">
            <label for="message-text" class="col-form-label">Fine:</label><br>
            <label><input type="radio"  class="fine" value="paywithfine" name="fine"> Pay With Fine</label>
            <label><input type="radio" value="paywithoutfine" name="fine" class="fine"> Pay Without Fine</label>
            <label><input type="radio" checked="checked" name="fine" value="paywithdiscountfine" class="fine"> Pay With Discount Fine</label>
           </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id="payFee" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </div>
</div>';
$feeList .= " <script>
        $(document).on('change keyup blur', '#discountAmount', function() {
            var main = $('#feeAmount').val();
            var disc = $('#discountAmount').val();
            
            var discont = main - disc;
            $('#PaidAmount').val(discont);
        });
        $(document).on('change keyup blur', '#PaidAmount,#discountAmount', function() {

        	var feeAmount = $('#feeAmount').val();
            var main = $('#PaidAmount').val();
            var disc = $('#discountAmount').val();
            
            var paid = (Number(main) + Number(disc));
            //alert(paid);
            var unpaid = feeAmount - paid ;
            $('#balance').val(unpaid);
            if(unpaid < 0){
            	$('#balance').css('background-color', 'red');
            	$('#balance').css('color', '#fff');
            	$('#payFee').prop('disabled', true);
            }else{
            	$('#balance').css('background-color', '#eee');
            	$('#balance').css('color', '#000');
            	$('#payFee').prop('disabled', false);
            }
        });
    </script><script>
	$('#payFee').click(function(){
 	var chalan_id = $( '#ChalanID' ).val();
 	var paid_date = $( '#PaidDate' ).val();
 	var studentid = $( '#studentID' ).val();
 	
 	if($('.fine').is(':checked')){
 		var fine = $( '.fine:checked' ).val();
 	}else{
 		var fine = '';
 	}
 	var fee_amount = $( '#feeAmount' ).val();
 	var paid_amount = $( '#PaidAmount' ).val();
 	var fineamount = $( '#fineamount' ).val();
 	var discountAmount = $( '#discountAmount' ).val();
 	
	if (confirm('Are you sure you want to pay?')) {	
 	      $.ajax({
            url: 'admin.php?c=ajax&m=pay_fee',
            type: 'POST',
            data:{chalan_id: chalan_id,studentid:studentid,paid_date:paid_date,fee_amount:fee_amount,paid_amount:paid_amount,fine:fine,fineamount:fineamount,discountAmount:discountAmount}, 
    success:function(res){
 		$('#payFee').html('Paid Successfully'); 
 		$('#payFee').prop('disabled', true);  
        var student_id = $( '#studentID' ).val();
         $.ajax({
            url: 'admin.php?c=fee_chalan_pay&m=get_students_list',
            type: 'POST',
            data:{student_id: student_id},
            success:function(res){
             if(res){
                $('#feetypeinfo').html(res);
             }else{
               $('#feetypeinfo').html('Record Not Found'); 
             }
         	}
         });

        $('#payfee').modal('hide'); 

		}
      });
	}
 });
			
$('#payfee').on('hidden.bs.modal', function () { 
    //location.reload();
    $('#payFeeData')[0].reset();
    $('#feeFine').hide();
    $('#payFee').html('Submit'); 
 	$('#payFee').prop('disabled', false);
 	$('#balance').css('background-color', '#eee');
    $('#balance').css('color', '#000');  

});

$('#payfee').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('whatever') // Extract info from data-* attributes
  var feeAmount = button.data('feeamount');
  var paiddate = $('#datePaid').val();
  $('#PaidDate').val(paiddate);
  var student_id = button.data('student_id');
  var fine = button.data('fine');
  if(fine){
  	$('#feeFine').show();
  }
  
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('.modal-title').text('Pay Fee ')
  modal.find('#feeAmount').val(feeAmount)
  modal.find('#ChalanID').val(recipient)
  modal.find('#studentID').val(student_id)
  modal.find('#fineamount').val(fine)
  modal.find('#PaidAmount').val(feeAmount)
});

</script><style>.modal-dialog {
          width: 360px;
          // height:700px !important;
        }
.modal-content {
    /* 80% of window height */
    height: 60%;
    background-color:#BBD6EC;
}        
.modal-header {
    background-color: #337AB7;
    padding:16px 16px;
    color:#FFF;
    border-bottom:2px dashed #337AB7;
 } </style>";		
		
	$this->output->set_output(($feeList));	
}
		 
		
}	







function updatePaidFee(){
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$data = array(
		'status' => 'unpaid',
		'updated_date' => $date,
		'user_id' => $user_id
	);
	$this->db->where('chalan_id', $this->input->post('challan_id'));
	$this->db->where('paid_date', date('Y-m-d'));
	$this->db->update('fee_chalan', $data);
}

function get_studentinfo(){

	
	$campusid = (int) $this->session->userdata('member_campusid');
	$term = $this->input->post('term');
	$cls_sec_id = (int) $this->input->post('flag');
	$searchTerm = trim((string) ($term['term'] ?? ''));

	$this->db->from('students');
	$this->db->where('status', 1);
	$this->db->where('campus_id', $campusid);
	if ($searchTerm !== '') {
		$this->db->group_start();
		$this->db->like('first_name', $searchTerm);
		$this->db->or_like('last_name', $searchTerm);
		$this->db->group_end();
	}
	if ($cls_sec_id > 0) {
		$sub = $this->db->select('student_id')->from('student_class')->where('status', 1)->where('cls_sec_id', $cls_sec_id)->get_compiled_select();
		$this->db->where("student_id IN ($sub)", null, false);
	}
	$studentsinfo = $this->db->get()->result_array();

	 // Initialize Array with fetched data
     $data = array();
     foreach($studentsinfo as $student){
     	$fatherName = '';
     	$classstudents = $this->db->from('student_class')->where('status', 1)->where('student_id', (int) $student['student_id'])->get()->row();
     	$studentsParents = $this->db->from('parents')->where('parent_id', (int) $student['parent_id'])->get()->row();
     	if($studentsParents){
     		$fatherName = $studentsParents->f_name;
     	}
     	if($classstudents){

     		$classSection = getClassSection($classstudents->cls_sec_id);
     		$section = '';
     		if($classSection){
     			$section = $classSection['sectionclassname'];
     		}

     		$data[] = array("id"=>$student['student_id'], "text"=>$student['first_name']." ".$student['last_name']." c/o ".$fatherName." ".$section);
     	}
     }
	return json_response($data);	 
}

function get_parentinfo(){
		$campusid = (int) $this->session->userdata('member_campusid');
		$term = $this->input->post('term');
		$searchTerm = trim((string) ($term['term'] ?? ''));

		$this->db->from('parents');
		$this->db->where('campus_id', $campusid);
		if ($searchTerm !== '') {
			$this->db->like('f_name', $searchTerm);
		}
		$parentssinfo = $this->db->get()->result_array();
		 // Initialize Array with fetched data

     $data = array();
     foreach($parentssinfo as $parent){
     	$classstudents = $this->db->from('students')->where('parent_id', (int) $parent['parent_id'])->where('campus_id', $campusid)->get()->row();
     	if($classstudents){
     		 $data[] = array("id" => $parent['parent_id'], "text" => $parent['f_name']);
     	}
     }

		return json_response($data);	 
	}

}

// end this file