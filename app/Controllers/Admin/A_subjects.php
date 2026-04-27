<?php
namespace App\Controllers\Admin;



/**
 * Subjects Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */



class A_subjects extends BaseController {
	function __construct(){
		parent::__construct();
		check_permission('admin-subjects');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$this->load->view('a_subjects', $this->template_data);
	}

	function data(){

		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];

		$this->db->select('count(A.sid) as ccount', FALSE);
		$this->db->from('a_subject A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.subject_name=' . $this->db->escape($keyword) .  ')');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;



		$this->db->select('A.*');
		$this->db->from('a_subject A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		if($keyword){
			$this->db->where('(A.subject_name=' . $this->db->escape($keyword) .  ')');
		}

		$this->db->order_by('A.sid', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();

		foreach($results as $row){
			$data = array();
			
			$data['id'] = $row->sid;
			$data['subject_name'] = $row->subject_name;
			$data['subject_short_name'] = $row->subject_short_name;

			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));

	}



	function add(){
		check_permission('admin-add-subject');
		$campus_id = $this->session->userdata('member_campusid');
		$this->db->where('campus_id', $campus_id);
		$subjectsinfo = $this->db->get('a_subject')->result();
		$this->template_data['subjectsinfo'] = $subjectsinfo;
		$this->load->view('a_subjects_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-subject');
		$sid = intval($this->input->get('id'));
		$this->db->where('sid', $sid);
		$info = $this->db->get('a_subject')->row();
		$this->template_data['info'] = $info;
		$this->load->view('a_subjects_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$rowscount = $this->input->post('rowscount');
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$campus_id = $this->session->userdata('member_campusid');

		{
			{
				//print_r($_POST);
				check_permission('admin-add-subject');
				$this->db->trans_begin();
				for($i=0; $i < count($rowscount); $i++){
					$id = $this->input->post('id'.$i);
				
				if($id == 0){

					$data = array(
						'subject_name' => trim($this->input->post('subject_name'.$i)),
						'subject_short_name' => trim($this->input->post('short_name'.$i)),
						'campus_id' => $campus_id,
						'user_id' => $user_id,
						'created_date' => $date
					);
					$this->db->insert('a_subject', $data);
					$new_user_id = $this->db->insert_id();

				}else{
					$data = array(
					'subject_name' => trim($this->input->post('subject_name'.$i)),
					'subject_short_name' => trim($this->input->post('short_name'.$i)),
					'user_id' => $user_id,
					'updated_date' => $date
					);
					$this->db->where('sid', $id);
					$this->db->update('a_subject', $data);
				}
				
				$this->db->trans_complete();
			}
			
			json_response(array('success' => TRUE, 'msg' => 'Add Subject Success'));
			
		
			}

		}

	}

	function delete(){
		check_permission('admin-del-user');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('sid', $id);
		$this->db->delete('a_subject');
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Subject Success'));
	}
}
// end this file
