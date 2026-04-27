<?php
namespace App\Controllers\Admin;



/**

 * Chalan Type Manage

 *

 * @author		Maqsood Ahmed

 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions

 * @email		maqsoodjamvi@gmail.com

 * @filesource

 */



class Chalan_type extends MY_Controller {



	function __construct(){

		parent::__construct();

		check_permission('admin-chalan-type');

	}



	/**

	 * Index Page for this controller.

	 */

	public function index()

	{

		$this->load->view('chalan_type', $this->template_data);

	}



	function data(){

		$response = new stdClass;

		$response->draw = $this->input->post('draw');



		$search = $this->input->post('search');

		$keyword = '';

		if($search) $keyword = $search['value'];

		$this->db->select('count(A.chalan_type_id) as ccount', FALSE);

		$this->db->from('chalan_type A');

		if($keyword){

			$this->db->where('(A.chalan_type_name=' . $this->db->escape($keyword) .  ')');

		}

		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;



		$this->db->select('A.*');

		$this->db->from('chalan_type A');

		if($keyword){

			$this->db->where('(A.chalan_type_name=' . $this->db->escape($keyword) .  ')');

		}

		$this->db->order_by('A.chalan_type_id', 'desc');

		$this->db->limit($this->input->post('length'), $this->input->post('start'));

		$results = $this->db->get()->result();



		$response->recordsFiltered = $response->recordsTotal;



		$response->data = array();

		foreach($results as $row){

			$data = array();

			$data['id'] = $row->chalan_type_id;

			$data['chalan_type_name'] = $row->chalan_type_name;

			$data['chalan_type_detail'] = $row->chalan_type_detail;

			

			$response->data[] = $data;

		}



		$this->output->set_output(json_encode($response));

	}



	function add(){

		check_permission('admin-add-chalan-type');

		$this->load->view('chalan_type_edit', $this->template_data);

	}



	function edit(){

		check_permission('admin-edit-chalan-type');

		$chalan_type_id = intval($this->input->get('id'));

		$this->db->where('chalan_type_id', $chalan_type_id);

		$info = $this->db->get('chalan_type')->row();

		$this->template_data['info'] = $info;

		$this->load->view('chalan_type_edit', $this->template_data);

	}



	function save(){

		$id = intval($this->input->post('id'));

		$this->form_validation->set_rules('chalan_type_name', 'Chalan Type Name', 'trim|required');

		if($this->form_validation->run() === FALSE){

			json_response(array('success' => FALSE, 'msg' => validation_errors()));

		}else{

			if($id === 0){

				check_permission('admin-add-chalan-type');

				$this->db->trans_begin();

				$data = array(

					'chalan_type_name' => trim($this->input->post('chalan_type_name')),

					'chalan_type_detail' => trim($this->input->post('chalan_type_detail')),

						);

				$this->db->insert('chalan_type', $data);

				$new_user_id = $this->db->insert_id();



				$this->db->trans_complete();

				json_response(array('success' => TRUE, 'msg' => 'Add Chalan Type Success'));

			}else{

				check_permission('admin-edit-chalan-type');

				$this->db->trans_begin();

				$data = array(

					'chalan_type_name' => trim($this->input->post('chalan_type_name')),

					'chalan_type_detail' => trim($this->input->post('chalan_type_detail')),

				);

				$this->db->where('chalan_type_id', $id);

				$this->db->update('chalan_type', $data);

				$this->db->trans_complete();

				json_response(array('success' => TRUE, 'msg' => 'Edit Chalan Type Success'));

			}



		}

	}



	function delete(){

		check_permission('admin-del-chalan-type');

		$id = intval($this->input->get('id'));



		$this->db->trans_begin();



		// delete user

		$this->db->where('id', $id);

		$this->db->delete('classes');



		$this->db->trans_complete();

		json_response(array('success' => TRUE, 'msg' => 'Delete Chalan Type Success'));

	}



}

// end this file

