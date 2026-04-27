<?php
namespace App\Controllers\Admin;



/**

 * Fee Chalan Document Manage

 *

 * @author		Maqsood Ahmed

 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions

 * @email		maqsoodjamvi@gmail.com

 * @filesource

 */

 





class Fee_chalan_document extends MY_Controller {



	function __construct(){

		parent::__construct();

		check_permission('admin-fee-chalan-documents');

	}



	/**

	 * Index Page for this controller.

	 */

	public function index()

	{

		$this->load->view('fee_chalan_document', $this->template_data);

	}



	function data(){

		$response = new stdClass;

		$response->draw = $this->input->post('draw');



		$search = $this->input->post('search');

		$keyword = '';

		if($search) $keyword = $search['value'];

		$this->db->select('count(A.ch_document_id) as ccount', FALSE);

		$this->db->from('chalan_document A');

		

		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;



	

		$this->db->select('A.*');

		$this->db->from('chalan_document A');

		

		$this->db->order_by('A.ch_document_id', 'desc');

		$this->db->limit($this->input->post('length'), $this->input->post('start'));

		$results = $this->db->get()->result();



		$response->recordsFiltered = $response->recordsTotal;



		$response->data = array();

		foreach($results as $row){

		

		$downloadlink = "<a target='_blank' href='".base_url($row->name)."'>Click to Download</a>";

			

		$this->db->where('campus_id', $row->campus_id);

		$campusinfo = $this->db->get('campus')->row();

		$campus_id = $this->session->userdata('member_campusid');

		

		if($campus_id == $campusinfo->campus_id){

			$data = array();

			$data['id'] = $row->ch_document_id;

			$data['name'] = $downloadlink;

			$data['campus'] = $campusinfo->campus_name;

			$data['created_date'] = $row->created_date;	

			$response->data[] = $data;

		}

		}

		

		$this->output->set_output(json_encode($response));

	}



}

// end this file

