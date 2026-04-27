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



class Profit_loss_report extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-profit-loss-reports');
	}

	/**
	 * Index Page for this controller.
	 */

	function index(){
		$this->load->view('profit_loss_report', $this->template_data);
	}

	
	function data(){
		$data = '';
		//$session_id = $this->input->post('session_id');
		$session_id = $this->session->userdata('member_sessionid');
		$campus_id = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		$this->db->where('session_id', $session_id);
 		$this->db->where('system_id', $schoolinfo->system_id);
		$academic_session = $this->db->get('academic_session')->row();

		$feeTypesInfo = $this->db->query('select * from expense_heads where system_id='.$schoolinfo->system_id)->result();

		//print_r($feeTypesInfo);


		//$studentClass = $this->db->query('select * from expenses where session_id='.$session_id.' AND student_id IN( select student_id from students where status=1 AND campus_id='.$campus_id.')')->result();

		$start  = new DateTime($academic_session->start_date);
		$start->modify('first day of this month');
		$end      = new DateTime($academic_session->end_date);
		$end->modify('first day of next month');
		$interval = DateInterval::createFromDateString('1 month');
		$period   = new DatePeriod($start, $interval, $end);


		$Yearmonths = '';
		
		$data .= '<table class="table"><tr><th style="width: 115px;"></th>';

		$data .= '<th>Total Expense</th>';
		$data .= '<th>Total Collection</th>';
		$data .= '<th>Profit/Loss</th>';
		$data .= '</tr>';
		foreach ($period as $dt) {
		
		$data .= '<tr><th>'.$dt->format("m/Y").'</th>';
		
		$feeInfoTotal = $this->db->query('SELECT SUM(amount) as totalSum from expenses where  MONTH(created_date)="'.$dt->format("m").'" AND  YEAR(created_date)="'.$dt->format("Y").'" AND campus_id='.$campus_id)->row();

		$data .= '<td>';
		if($feeInfoTotal->totalSum){
			$data .= '<div style="color:#000;border-bottom:1px solid #000;">'.$feeInfoTotal->totalSum.'/- </div>';
		}else{
				$data .= '<div style="color:#000;border-bottom:1px solid #000;">0/- </div>';
			}
		
		$data .=  '</td>';

		$TotalCollection = $this->db->query('SELECT SUM(amount - discount) as TotalCol FROM fee_chalan WHERE MONTH(paid_date)="'.$dt->format("m").'" AND YEAR(paid_date)="'.$dt->format("Y").'" AND `status`="paid" AND student_id IN(SELECT student_id FROM students  WHERE campus_id='.$campus_id.')')->row();

		$data .=  '<td>';
		
		if($TotalCollection->TotalCol){
			$data .= '<div style="color:#000;border-bottom:1px solid #000;">'.$TotalCollection->TotalCol.'/- </div>';
		}else{
				$data .= '<div style="color:#000;border-bottom:1px solid #000;">0/- </div>';
			}
		$data .=  '</td>';
		$data .=  '<td>';

		if($TotalCollection && $feeInfoTotal){
			$data .= '<div style="color:#000;border-bottom:1px solid #000;">'.($TotalCollection->TotalCol-$feeInfoTotal->totalSum).'/- </div>';
		}else{
				$data .= '<div style="color:#000;border-bottom:1px solid #000;">0/- </div>';
			}
		$data .=  '</td>';

		$data .= '</tr>';

		}

		$data .= '</table>';
		$this->output->set_output($data);
	}
}
// end this file
