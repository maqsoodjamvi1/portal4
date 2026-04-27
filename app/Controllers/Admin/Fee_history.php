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


class Fee_history extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-fee-history');
	}

	/**
	 * Index Page for this controller.
	*/

	public function index()
	{

		$student_id = $this->input->get('student_id');
		$this->template_data['student_id'] = $student_id;
		
		$this->db->where('student_id', $student_id);
		$studentsinfo = $this->db->get('students')->row();
		$this->template_data['studentsinfo'] = $studentsinfo;

		$results = $this->db->query("SELECT SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE student_id=".$student_id." AND status='unpaid'")->row();

		$this->template_data['unpaidtotal'] = $results;

		$this->load->view('fee_history', $this->template_data);

	}

		function data(){

		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$student_id = $this->input->get('student_id');
		$search = '';
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.chalan_id) as ccount', FALSE);
		$this->db->from('fee_chalan A');
		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;

		$results = $this->db->query("SELECT paid_date,SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE student_id IN(SELECT student_id FROM students WHERE student_id=".$student_id.") AND status='paid'  group by paid_date order by paid_date")->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();

		foreach($results as $row){
		
			$data = array();
			$data['amount'] = $row->total;
			$data['paiddate'] = $row->paid_date;
			$response->data[] = $data;
		
		}

		$this->output->set_output(json_encode($response));

	}




}

// end this file

