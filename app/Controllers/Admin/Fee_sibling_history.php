<?php
namespace App\Controllers\Admin;


/**
 * Fee Sibling History Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Fee_sibling_history extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-fee-sibling-history');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$parent_id = $this->input->get('parent_id');
		$this->template_data['parent_id'] = $parent_id;
		
		$this->db->where('parent_id', $parent_id);
		$studentsinfo = $this->db->get('students')->result();
		$this->template_data['studentsinfo'] = $studentsinfo;

		
		$this->db->where('parent_id', $parent_id);
		$parentsinfo = $this->db->get('parents')->row();
		$this->template_data['parentsinfo'] = $parentsinfo;

		//print_r($studentsinfo);

		$results = $this->db->query("SELECT SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE student_id IN(SELECT student_id FROM students WHERE parent_id=".$parent_id.") AND status='unpaid'")->row();

		$this->template_data['unpaidtotal'] = $results;

		$this->load->view('fee_sibling_history', $this->template_data);

	}

	function data(){

		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$parent_id = $this->input->get('parent_id');
		$search = '';
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.chalan_id) as ccount', FALSE);
		$this->db->from('fee_chalan A');
		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;

		$results = $this->db->query("SELECT paid_date,SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE student_id IN(SELECT student_id FROM students WHERE parent_id=".$parent_id.") AND status='paid'  group by paid_date order by paid_date")->result();

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