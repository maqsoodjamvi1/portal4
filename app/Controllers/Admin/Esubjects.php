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



class Esubjects extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-subjects');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$this->load->view('esubjects', $this->template_data);
	}

	function data(){

		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];

		$this->db->select('count(A.sub_id) as ccount', FALSE);
		$this->db->from('esubjects A');
		if($keyword){
			$this->db->where('(A.subject=' . $this->db->escape($keyword) .  ')');
		}

		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('esubjects A');
		if($keyword){
			$this->db->where('(A.subject=' . $this->db->escape($keyword) .  ')');
		}

		$this->db->order_by('A.sub_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();

		foreach($results as $row){
			$data = array();
			
			$data['id'] = $row->sub_id;
			$data['subject'] = $row->subject;
			$data['short_name'] = $row->short_name;
			$data['detail'] = $row->detail;

			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));

	}



	function add(){
		check_permission('admin-add-subject');
		$subjectsinfo = $this->db->get('esubjects')->result();
		$this->template_data['subjectsinfo'] = $subjectsinfo;
		$this->load->view('esubjects_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-subject');
		$sid = intval($this->input->get('id'));
		$this->db->where('sid', $sid);
		$info = $this->db->get('esubjects')->row();
		$this->template_data['info'] = $info;
		$this->load->view('esubjects_edit', $this->template_data);
	}

	function save(){
		//$id = intval($this->input->post('id'));
		$rowscount = $this->input->post('rowscount');
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
	
		{
			{
				//print_r($_POST);
			check_permission('admin-add-subject');
			$this->db->trans_begin();
			for($i=0; $i < count($rowscount); $i++){
				$id = $this->input->post('id'.$i);
				$meta_title = $this->input->post('meta_title'.$i);
				$meta_keywords = $this->input->post('meta_keywords'.$i);
				$meta_description = $this->input->post('meta_description'.$i);
				
				if($id == 0){
					$data = array(
							'subject' => trim($this->input->post('subject'.$i)),
							'slug' => trim($this->input->post('slug'.$i)),
							'short_name' => trim($this->input->post('short_name'.$i)),
							'detail' => trim($this->input->post('detail'.$i)),
							'meta_title' => $meta_title,
							'meta_keywords' => $meta_keywords,
							'meta_description' => $meta_description,
						);
					//print_r($data);
					$this->db->insert('esubjects', $data);
					$new_user_id = $this->db->insert_id();

				}else{
					$data = array(
					'subject' => trim($this->input->post('subject'.$i)),
					'slug' => trim($this->input->post('slug'.$i)),
					'short_name' => trim($this->input->post('short_name'.$i)),
					'detail' => trim($this->input->post('detail'.$i)),
					'meta_title' => $meta_title,
					'meta_keywords' => $meta_keywords,
					'meta_description' => $meta_description,
				);
					//print_r($data);
					$this->db->where('sub_id', $id);
					$this->db->update('esubjects', $data);
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
		$this->db->delete('allsubject');
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Subject Success'));
	}
}
// end this file
