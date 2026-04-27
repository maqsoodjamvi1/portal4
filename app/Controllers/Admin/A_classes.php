<?php
namespace App\Controllers\Admin;


/**
 * Classes Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class A_classes extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-classes');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('a_classes', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.class_id) as ccount', FALSE);
		$this->db->from('a_classes A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.class_name=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

	
		$this->db->select('A.*');
		$this->db->from('a_classes A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.class_name=' . $this->db->escape($keyword)  . ')');
		}
		$this->db->order_by('A.class_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->class_id;
			$data['class_name'] = $row->class_name;
			$data['class_short_name'] = $row->class_short_name;
			$data['detail'] = $row->detail;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-class');
		$campus_id = $this->session->userdata('member_campusid');

		$this->db->where('campus_id', $campus_id);
		$info = $this->db->get('a_classes')->result();
		$this->template_data['info'] = $info;
		$this->load->view('a_classes_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-class');
		$id = intval($this->input->get('id'));

		$this->db->where('class_id', $id);
		$info = $this->db->get('a_classes')->row();
		$this->template_data['info'] = $info;
		$this->load->view('a_classes_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		$campus_id = $this->session->userdata('member_campusid');
		
		$rowscount = $this->input->post('rowscount');

			for($i=0; $i < count($rowscount); $i++){
				$id = $this->input->post('id'.$i);	
				$this->db->trans_begin();
				if($id == 0){
					$data = array(
					'class_name' => trim($this->input->post('class_name'.$i)),
					'class_short_name' => trim($this->input->post('class_short_name'.$i)),
					'detail' => trim($this->input->post('detail'.$i)),
					'campus_id' => $campus_id,
					'user_id' => $user_id,
					'created_date' => $date
				);
				$this->db->insert('a_classes', $data);
				$new_user_id = $this->db->insert_id();

			}else{
					
				$data = array(
					'class_name' => trim($this->input->post('class_name'.$i)),
					'class_short_name' => trim($this->input->post('class_short_name'.$i)),
					'detail' => trim($this->input->post('detail'.$i)),
					'campus_id' => $campus_id,
					'user_id' => $user_id,
					'created_date' => $date
				);
				$this->db->where('class_id', $id);
				$this->db->update('a_classes', $data);
			}
				
			$this->db->trans_complete();
		}

					
			json_response(array('success' => TRUE, 'msg' => 'Add Class Success'));
			
	}

	function delete(){
		check_permission('admin-del-class');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('class_id', $id);
		$this->db->delete('a_classes');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Classes Success'));
	}

}
// end this file
