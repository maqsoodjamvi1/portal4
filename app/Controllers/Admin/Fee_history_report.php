<?php
namespace App\Controllers\Admin;


/**
 * Fee History Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Fee_history_report extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-fee-history-report');
	}

	/**
	 * Index Page for this controller.
	*/

	public function index()
	{
		$parent_id = $this->input->get('parent_id');
		$this->template_data['parent_id'] = $parent_id;
		
		$this->db->where('parent_id', $parent_id);
		$parentsinfo = $this->db->get('parents')->row();
		$this->template_data['parentsinfo'] = $parentsinfo;

		$results = $this->db->query("SELECT SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE student_id IN(select student_id from students where parent_id=".$parent_id.") AND status='unpaid'")->row();

		$this->template_data['unpaidtotal'] = $results;

		$this->load->view('fee_history_report', $this->template_data);

	}

	function data(){
		$schoolinfo = getSchoolInfo();
		
		$feeTypesInfo = $this->db->query("select * from fee_type where fee_type_id = 194 and system_id=".$schoolinfo->system_id)->result();

		$parent_id = $this->input->get('parent_id');
		$results = $this->db->query("SELECT paid_date,SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE  student_id IN(SELECT student_id FROM students WHERE parent_id=".$parent_id.") AND status='paid' group by paid_date order by paid_date DESC")->result();


		$strFeeReport = '';
		$strFeeReport .= '<table class="table table-bordered">';
		//$strFeeReport .= '<tr><th>Date</th>';

		// foreach ($feeTypesInfo as $key => $value) {
		// 	$strFeeReport .= '<th>'.$value->fee_type_name.'</th>';
		// }
		//$strFeeReport .= '<th>Fee Type</th>';
		
		//$strFeeReport .= '<th>Total<th></tr>';
		foreach ($results as $feevalue) {
			
		//$date = date_create($feevalue->paid_date);
		//$dateFormated date_format($date, 'l jS \of F Y');
		$date = date_create($feevalue->paid_date);
		$dateFormated = date_format($date,"l jS F Y");
	
		$strFeeReport .= '<tr class="bg-danger"><th class="bg-danger">'.$dateFormated.'</th>';
		$strFeeReport .= '<th class="bg-danger">Total Amount: '.$feevalue->total.'/-</th><th class="bg-danger"></th></tr>';
		$strFeeReport .= '<tr>';	
		$resultsDetail = $this->db->query("SELECT fee_month,student_id,fee_type_id,SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE student_id IN(SELECT student_id FROM students WHERE parent_id=".$parent_id.") AND status='paid' AND paid_date='".$feevalue->paid_date."' GROUP BY student_id,fee_type_id,fee_month order by student_id DESC")->result();
			
			foreach ($resultsDetail as $key => $value) {

				$studentInfo = $this->db->query("SELECT first_name,last_name from students where student_id=".$value->student_id)->row();

				// $studentClsInfo = $this->db->query("SELECT * FROM student_class WHERE student_id=".$value->student_id." AND status=1")->row();

				// $studentClass = getClassSection($studentClsInfo->cls_sec_id);

				$feeTypeInfo = $this->db->query("SELECT fee_type_name from fee_type where fee_type_id=".$value->fee_type_id)->row();

				$strFeeReport .= '<tr><td>'.$studentInfo->first_name." ".$studentInfo->last_name.'</td><td>'.$feeTypeInfo->fee_type_name.' ('.$value->fee_month.')</td><td>'.$value->total.'/-</td></tr>';
			}
		
		$strFeeReport .='</tr>';

		}
		// foreach ($feeTypesInfo as $key => $value) {
			
		// 	$resultsbyfeeType = $this->db->query("SELECT SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE status='paid' AND fee_type_id=".$value->fee_type_id." AND paid_date='".$feevalue->paid_date."' order by paid_date")->row();
			
		// 	$strFeeReport .= '<th>'.$resultsbyfeeType->total.'</th>';
		// }
		$strFeeReport .= '</table>';
		

		print_r($strFeeReport);	
	}
}

// end this file

