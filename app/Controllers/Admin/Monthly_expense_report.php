<?php
namespace App\Controllers\Admin;


/**
 * Student Fee Report
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Monthly_expense_report extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-monthly-expense-reports');
	}

	/**
	 * Index Page for this controller.
	 */

	function index(){
		$this->load->view('monthly_expense_report', $this->template_data);
	}

	
	function data(){
		$data = '';
		//$session_id = $this->input->post('session_id');
		$session_id = $this->session->userdata('member_sessionid');
		$campus_id = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		// $this->db->where('session_id', $session_id);
 	// 	$this->db->where('system_id', $schoolinfo->system_id);
		// $academic_session = $this->db->get('academic_session')->row();

		// $feeTypesInfo = $this->db->query('select * from expense_heads where system_id='.$schoolinfo->system_id)->result();

		//print_r($feeTypesInfo);


		//$studentClass = $this->db->query('select * from expenses where session_id='.$session_id.' AND student_id IN( select student_id from students where status=1 AND campus_id='.$campus_id.')')->result();

		// $start  = new DateTime($academic_session->start_date);
		// $start->modify('first day of this month');
		// $end      = new DateTime($academic_session->end_date);
		// $end->modify('first day of next month');
		// $interval = DateInterval::createFromDateString('1 month');
		// $period   = new DatePeriod($start, $interval, $end);


		// $Yearmonths = '';
		
		$data .= '<table class="table table-bordered">';
		// $data .= '<tr>';
		// $data .= '<th></th>';
		// $data .= '<th></th>';
		// $data .= '</tr>';
		
		$data .= '<tr>';
		
		//$feeInfoTotal = $this->db->query('select SUM(amount) as totalSum from expenses where  MONTH(created_date)="'.$dt->format("m").'" AND  YEAR(created_date)="'.$dt->format("Y").'" AND campus_id='.$campus_id)->row();

		$data .= '<th style="width:50%;">';
		$data .= 'Total Projected Fee';
		$data .= '</th>';

		// $TotalCollection = $this->db->query('SELECT SUM(amount) as TotalCol FROM fee_chalan WHERE MONTH(paid_date)="'.$dt->format("m").'"
  //      AND YEAR(paid_date)="'.$dt->format("Y").'" AND STATUS="paid" AND student_id IN(SELECT student_id FROM students WHERE campus_id='.$campus_id.')')->row();

		$data .= '<th>';
		$data .= '</th>';

		$data .= '</tr>';
		$data .= '<tr>';
		
		$data .= '<th>';
		$data .= 'Remaining Balance';
		$data .= '</th>';

		$data .= '<th>';
		$data .= '</th>';

		$data .= '</tr>';

		$feeTypes = $this->db->query('SELECT * FROM fee_type WHERE system_id='.$schoolinfo->system_id)->result();

		foreach ($feeTypes as $key => $value) {
		$data .= '<tr>';
		
		$data .= '<th>';
		$data .= $value->fee_type_name;
		$data .= '</th>';

		$data .= '<th>';
		$data .= '</th>';


		$data .= '</tr>';
		}

		$data .= '<tr>';
		
		$data .= '<th>';
		$data .= 'Total Balance To Be Collected This Month';
		$data .= '</th>';

		$data .= '<th>';
		$data .= '</th>';
		$data .= '<tr>';
		
		$data .= '<th>';
		$data .= 'Total Received Amount';
		$data .= '</th>';

		$data .= '<th>';
		$data .= '</th>';
		

		$data .= '</tr>';
		$data .= '<tr>';
		
		$data .= '<th>';
		$data .= 'Remaining';
		$data .= '</th>';

		$data .= '<th>';
		$data .= '</th>';
		

		$data .= '</tr>';

		$data .= '</table>';

		$data .= '<h2 class="text-center">Expense Details</h2>';

		$data .= '<table class="table table-bordered">';
		$data .= '<tr>';
		$data .= '<th>Budget</th>';
		$data .= '<th>Current Month Amount Due</th>';
		$data .= '<th>Previous Amount Due</th>';
		$data .= '<th>Total Due</th>';
		$data .= '<th>Received</th>';
		$data .= '<th>Remaining</th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Building Rent</th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Salary</th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Foundation Fund</th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Bills</th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Development Expense</th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Other Expense</th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Total</th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '</table>';

		$data .= '<h2 class="text-center">Final Report</h2>';

		$data .= '<table class="table table-bordered">';
		$data .= '<tr>';
		$data .= '<th>Current Month Total Income</th>';
		$data .= '<th></th>';
		$data .= '<th>Previous Loss</th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Current Month Total Expense</th>';
		$data .= '<th></th>';
		$data .= '<th>Total Loss</th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Profit</th>';
		$data .= '<th></th>';
		$data .= '<th>Previous Profit</th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Loss</th>';
		$data .= '<th></th>';
		$data .= '<th>Current Profit</th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '<th>Current Month Loss</th>';
		$data .= '<th></th>';
		$data .= '<th>Total Profit</th>';
		$data .= '<th></th>';
		$data .= '</tr>';
		$data .= '<tr>';
		$data .= '</table>';

		$this->output->set_output($data);
	}
}
// end this file
