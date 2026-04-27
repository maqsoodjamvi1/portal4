<?php
namespace App\Controllers\Admin;


/**
 * Employees Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Employees extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-employees');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('employees', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.tid) as ccount', FALSE);
		$this->db->from('employees A');
		if($keyword){
			$this->db->where('(A.first_name=' . $this->db->escape($keyword) . ' or A.email=' . $this->db->escape($keyword) . ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		
		$this->db->select('A.*');
		$this->db->from('employees A');
		if($keyword){
			$this->db->where('(A.first_name=' . $this->db->escape($keyword) . ' or A.email=' . $this->db->escape($keyword) . ')');
		}
		$this->db->order_by('A.tid', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$data['id'] = $row->tid;
			$data['first_name'] = $row->first_name;
			$data['last_name'] = $row->last_name;
			$data['status'] = $row->status;
			$data['mobile_no'] = $row->mobile_no;
			$data['mobile_no2'] = $row->mobile_no2;
			//$data['issys'] = $row->issys;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-employee');
		$userRoles = array();
		$this->template_data['userRoles'] = $userRoles;

		$emp_types = $this->db->get('user_type')->result();
		$this->template_data['emp_types'] = $emp_types; 

		$this->load->view('employees_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-employee');
		$id = intval($this->input->get('id'));
		$userRoles = array();
		$this->template_data['userRoles'] = $userRoles;

		$emp_types = $this->db->get('emp_type')->result();
		$this->template_data['emp_types'] = $emp_types;

		$this->db->where('tid', $id);
		$info = $this->db->get('employees')->row();
		$this->template_data['info'] = $info;
		$this->load->view('employees_edit', $this->template_data);
	}

	function save(){
		$now = date('Y-m-d H:i:s');
		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata('member_campusid');
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			if($id === 0){
				check_permission('admin-add-employee');
				$this->db->trans_begin();
				$data = array(
					'first_name' => trim($this->input->post('first_name')),
					'last_name' => trim($this->input->post('last_name')),
					'dob' => trim($this->input->post('dob')),
					'f_first_name' => trim($this->input->post('f_first_name')),
					'f_last_name' => trim($this->input->post('f_last_name')),
					'emp_type_id' => trim($this->input->post('emp_type_id')),
					'cnic' => trim($this->input->post('username')),
					'gender' => trim($this->input->post('gender')),
					'marital_status' => trim($this->input->post('marital_status')),
					'joining_date' => trim($this->input->post('joining_date')),
					'email' => trim($this->input->post('email')),
					'mobile_no' => trim($this->input->post('mobile_no')),
					'mobile_no2' => trim($this->input->post('mobile_no2')),
					'address' => trim($this->input->post('address_1')),
					'address_2' => trim($this->input->post('address_2')),
					'emergency_contact_person' => trim($this->input->post('emergency_contact_person')),
					'emergency_contact_no' => trim($this->input->post('emergency_contact_no')),
					'qualification' => trim($this->input->post('qualification')),
					'experience' => trim($this->input->post('experience')),
					'skills' => trim($this->input->post('skills')),
					'datecreated' => trim($now),
					'campus_id' => $campus_id,
			
				);
				$this->db->insert('employees', $data);
				$new_emp_id = $this->db->insert_id();

				$data2 = array(
					'username' => trim($this->input->post('username')),
					'email' => trim($this->input->post('email')),
					'status' => 1,
					'campus_id'=> $campus_id,
					'password' => password_hash(trim($this->input->post('password')), PASSWORD_BCRYPT)

				);

				$this->db->insert('users', $data2);
				$new_user_id = $this->db->insert_id();

				// set user roles
				$rolesarr = $this->input->post('roles');
				if($rolesarr){
					$sql = 'insert into user_roles(userID, roleID) values';
					$tstr = '';
					foreach($rolesarr as $v){
						$tstr .= '(' . $new_user_id . ', ' . $v . '),';
					}
					if($tstr != ''){
						$sql .= rtrim($tstr, ',');
						$this->db->query($sql);
					}
				}


				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Add Employee Success'));
			}else{
				check_permission('admin-edit-employee');
				$this->db->trans_begin();
				$data = array(
					'first_name' => trim($this->input->post('first_name')),
					'last_name' => trim($this->input->post('last_name')),
					'dob' => trim($this->input->post('dob')),
					'f_first_name' => trim($this->input->post('f_first_name')),
					'f_last_name' => trim($this->input->post('f_last_name')),
					'cnic' => trim($this->input->post('cnic')),
					'gender' => trim($this->input->post('gender')),
					'marital_status' => trim($this->input->post('marital_status')),
					'joining_date' => trim($this->input->post('joining_date')),
					'email' => trim($this->input->post('email')),
					'mobile_no' => trim($this->input->post('mobile_no')),
					'mobile_no2' => trim($this->input->post('mobile_no2')),
					'address_1' => trim($this->input->post('address_1')),
					'address_2' => trim($this->input->post('address_2')),
					'emergency_contact_person' => trim($this->input->post('emergency_contact_person')),
					'emergency_contact_no' => trim($this->input->post('emergency_contact_no')),
					'salary' => trim($this->input->post('salary')),
					'qualification' => trim($this->input->post('qualification')),
					'experience' => trim($this->input->post('experience')),
					'skills' => trim($this->input->post('skills')),
					//'datecreated' => trim($this->input->post('datecreated')),
					'dateupdated' => trim($now),
					'campus_id' => $campus_id,
				);
				
				$this->db->where('tid', $id);
				$this->db->update('employees', $data);
				
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Employee Success'));
			}

		}
	}

	function delete(){
		check_permission('admin-del-employee');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

		// delete user
		$this->db->where('tid', $id);
		$this->db->delete('employees');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Employee Success'));
	}
}
// end this file
