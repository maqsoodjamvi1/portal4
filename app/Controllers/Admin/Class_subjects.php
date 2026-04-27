<?php
namespace App\Controllers\Admin;



/**
 * Class Subjects Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Class_subjects extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-class-subjects');
		$this->load->library('session');

	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('class_subjects', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.cs_id) as ccount', FALSE);
		$this->db->from('class_subjects A');

		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;


		$this->db->select('A.*');
		$this->db->from('class_subjects A');
		$this->db->order_by('A.cs_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();

		foreach($results as $row){
			$data = array();
			$this->db->where('class_id', $row->class_id);
			$classinfo = $this->db->get('classes')->row();
			
			$this->db->where('sid', $row->subject_id);
			$allsubjectinfo = $this->db->get('allsubject')->row();
			
			$data['id'] = $row->cs_id;
			$data['class'] = $classinfo->class_name;
			$data['subject'] = $allsubjectinfo->subject_name;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-class-subjects');
	    $classinfo = $this->db->get('classes')->result();
		$this->template_data['classinfo'] = $classinfo;
		
		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('class_subjects_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-class-subjects');
		$id = intval($this->input->get('id'));

		$this->db->where('cs_id', $id);
		$info = $this->db->get('class_subjects')->row();
		$this->template_data['info'] = $info;	
		
		$classinfo = $this->db->get('classes')->result();
		$this->template_data['classinfo'] = $classinfo;
		
		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;
		
		$this->load->view('class_subjects_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata['member_campusid'];
		$subjects = $this->input->post('subjects');	

		{
			if($id === 0){
				check_permission('admin-add-class-subjects');
				$this->db->trans_begin();
				foreach($subjects as $subject_id){
				$data = array(
					'subject_id' => $subject_id,
					'class_id' =>  intval($this->input->post('class_id')),
				);
				$this->db->insert('class_subjects', $data);
				}
				$new_user_id = $this->db->insert_id();

				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Add Class Subjects Success'));

			}else{

				check_permission('admin-edit-class-subjects');
				$this->db->trans_begin();
				
				$data = array(
					'subject_id' => intval($this->input->post('sub_id')),
					'class_id' =>  intval($this->input->post('class_id')),
				);

				$this->db->where('cs_id', $id);
				$this->db->update('class_subjects', $data);
				
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Class Subjects Success'));
			}
		}
	}

	function delete(){
		check_permission('admin-del-class-subjects');
		$id = intval($this->input->get('id'));
		$this->db->trans_begin();
		// delete user
		$this->db->where('cs_id', $id);
		$this->db->delete('class_subjects');
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Class Subjects Success'));
	}
}
// end this file
