<?php
namespace App\Controllers\Admin;



/**
 * Class Sub Category Manage
 *
 * @author		Maqsood Jamvi
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Class_sub_cat extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-class-subject-category');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$this->load->view('class_sub_cat', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.class_sub_cat_id) as ccount', FALSE);
		$this->db->from('class_sub_cat A');
		
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('class_sub_cat A');
		$this->db->order_by('A.class_sub_cat_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();


		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		foreach($results as $row){
		$data = array();
			

		$this->db->where('class_id',  $row->class_id);
		$classinfo = $this->db->get('classes')->row();

		$this->db->where('sub_cat_id',  $row->sub_cat_id);
		$sub_category_info = $this->db->get('sub_category')->row();

			$data['id'] = $row->class_sub_cat_id;
			$data['class'] = $classinfo->class_name;
			$data['subject_cat'] = $sub_category_info->cat_name;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}



	function add(){

		check_permission('admin-add-class-subject-category');
		$classinfo = $this->db->get('classes')->result();
		$this->template_data['classinfo'] = $classinfo;

		$classinfo = $this->db->get('classes')->result();
		$this->template_data['classinfo'] = $classinfo;


		$this->db->where('subject_type', 'academic');
		$allsubject_info = $this->db->get('allsubject')->result();
		$this->template_data['allsubject_info'] = $allsubject_info;

		
		$sub_category_info = $this->db->get('sub_category')->result();
		$this->template_data['sub_category_info'] = $sub_category_info;

		$this->load->view('class_sub_cat_edit', $this->template_data);

	}



	function edit(){
		check_permission('admin-edit--class-subject-category');
		$id = intval($this->input->get('id'));

		$classinfo = $this->db->get('classes')->result();
		$this->template_data['classinfo'] = $classinfo;
		
		$this->db->where('subject_type', 'academic');
		$allsubject_info = $this->db->get('allsubject')->result();
		$this->template_data['allsubject_info'] = $allsubject_info;

		$this->db->where('term_id', $id);
		$info = $this->db->get('terms')->row();
		$this->template_data['info'] = $info;
		$this->load->view('class_sub_cat_edit', $this->template_data);
	}


	function save(){
		$id = intval($this->input->post('id'));
		if($id === 0){
			check_permission('admin-add-class-subject-category');
			$subcatids = $this->input->post('sub_cat_id'); 
			$this->db->trans_begin();

			$this->db->where('subject_id', $this->input->post('subject_id'));
  			$this->db->delete('class_sub_cat');
		
			foreach($subcatids as $sub_cat_id){
			$data = array(
				'class_id' => trim($this->input->post('class_id')),
				'sub_cat_id' => trim($sub_cat_id),
				'subject_id' => trim($this->input->post('subject_id'))
			);
			//print_r($data);
			$this->db->insert('class_sub_cat', $data);
			$new_user_id = $this->db->insert_id();

		 }



			$this->db->trans_complete();

			json_response(array('success' => TRUE, 'msg' => 'Add Class Subject Category Success'));

		}else{

			check_permission('admin-edit-class-subject-category');

			$this->db->trans_begin();

			$data = array(

				'class_id' => trim($this->input->post('class_id')),

				'subject_id' => trim($this->input->post('subject_id')),

				'sub_cat_id' => trim($this->input->post('sub_cat_id'))

			);

			$this->db->where('class_sub_cat_id', $id);

			$this->db->update('class_sub_cat', $data);

			

			$this->db->trans_complete();

			json_response(array('success' => TRUE, 'msg' => 'Edit Class Subject Category Success'));

		}



	}



	function delete(){

		check_permission('admin-del-class-subject-category');

		$id = intval($this->input->get('id'));



		$this->db->trans_begin();

		// delete user

		$this->db->where('class_sub_cat_id', $id);

		$this->db->delete('class_sub_cat');



		$this->db->trans_complete();

		json_response(array('success' => TRUE, 'msg' => 'Delete Class Subject Category Success'));

	}



}

// end this file

