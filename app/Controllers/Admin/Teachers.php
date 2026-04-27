<?php
namespace App\Controllers\Admin;



/**

 * Users Manage

 *

 * @author		Chaegumi

 * @copyright	Copyright (c) 2016~2099 cxpcms.com

 * @email		chaegumi@qq.com

 * @filesource

 */





class Teachers extends MY_Controller {



	function __construct(){

		parent::__construct();

		check_permission('admin-users');

	}



	/**

	 * Index Page for this controller.

	 */

	public function index()

	{

		$this->load->view('teachers', $this->template_data);

	}



	function data(){

		$response = new stdClass;

		$response->draw = $this->input->post('draw');



		$search = $this->input->post('search');

		$keyword = '';

		if($search) $keyword = $search['value'];

		// $this->session->set_userdata('search', $search);

		// $perpage = 10;

		$this->db->select('count(A.tid) as ccount', FALSE);

		$this->db->from('teachers A');

		if($keyword){

			$this->db->where('(A.first_name=' . $this->db->escape($keyword) . ' or A.email=' . $this->db->escape($keyword) . ')');

		}

		$q = $this->db->get()->row();

		$response->recordsTotal = $q->ccount;



		// $offset = $response->draw * $perpage;



		$this->db->select('A.*');

		$this->db->from('teachers A');

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

			//$data['issys'] = $row->issys;

			$response->data[] = $data;

		}



		$this->output->set_output(json_encode($response));

	}



	function add(){

		check_permission('admin-add-user');

		$userRoles = array();

		$this->template_data['userRoles'] = $userRoles;

		

		$campusid = $this->session->userdata('member_campusid');

		$sessionid = $this->session->userdata('member_sessionid');

		$sessionData = array(

		'campusid' => $campusid,

		'sessionid' => $sessionid

		);

		$this->template_data['sessionData'] = $sessionData;



		$this->load->view('teachers_edit', $this->template_data);

	}



	function edit(){

		check_permission('admin-edit-user');

		$id = intval($this->input->get('id'));

		

		$campusid = $this->session->userdata('member_campusid');

		$sessionid = $this->session->userdata('member_sessionid');

		$sessionData = array(

		'campusid' => $campusid,

		'sessionid' => $sessionid

		);

		$this->template_data['sessionData'] = $sessionData;



		$this->db->where('tid', $id);

		$info = $this->db->get('teachers')->row();

		$this->template_data['info'] = $info;

		$this->load->view('teachers_edit', $this->template_data);

	}







	function save(){

		$now = date('Y-m-d H:i:s');

		$id = intval($this->input->post('id'));

		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');

		if($this->form_validation->run() === FALSE){

			json_response(array('success' => FALSE, 'msg' => validation_errors()));

		}else{

			if($id === 0){

				check_permission('admin-add-user');

				$this->db->trans_begin();

				$data = array(

					'first_name' => trim($this->input->post('first_name')),

					'last_name' => trim($this->input->post('last_name')),

					'dob' => trim($this->input->post('dob')),

					'f_first_name' => trim($this->input->post('f_first_name')),

					'f_last_name' => trim($this->input->post('f_last_name')),

					'f_cnic' => trim($this->input->post('f_cnic')),

					'gender' => trim($this->input->post('gender')),

					'marital_status' => trim($this->input->post('marital_status')),

					'joining_date' => trim($this->input->post('joining_date')),

					'email' => trim($this->input->post('email')),

					'mobile_no' => trim($this->input->post('mobile_no')),

					'land_line' => trim($this->input->post('land_line')),

					'address_1' => trim($this->input->post('address_1')),

					'address_2' => trim($this->input->post('address_2')),

					'emergency_contact_person' => trim($this->input->post('emergency_contact_person')),

					'emergency_contact_no' => trim($this->input->post('emergency_contact_no')),

					'salary' => trim($this->input->post('salary')),

					'qualification' => trim($this->input->post('qualification')),

					'experience' => trim($this->input->post('experience')),

					'skills' => trim($this->input->post('skills')),

					'datecreated' => trim($now),

					'campus_id' => trim($this->input->post('campus_id')),

					//'dateupdated' => trim($this->input->post('dateupdated')),



				);

				$this->db->insert('teachers', $data);

				$new_user_id = $this->db->insert_id();



				// set user roles

				$rolesarr = $this->input->post('roles');



				$this->db->trans_complete();

				json_response(array('success' => TRUE, 'msg' => 'Add Teacher Success'));

			}else{

				check_permission('admin-edit-user');

				$this->db->trans_begin();

				$data = array(

					'first_name' => trim($this->input->post('first_name')),

					'last_name' => trim($this->input->post('last_name')),

					'dob' => trim($this->input->post('dob')),

					'f_first_name' => trim($this->input->post('f_first_name')),

					'f_last_name' => trim($this->input->post('f_last_name')),

					'f_cnic' => trim($this->input->post('f_cnic')),

					'gender' => trim($this->input->post('gender')),

					'marital_status' => trim($this->input->post('marital_status')),

					'joining_date' => trim($this->input->post('joining_date')),

					'email' => trim($this->input->post('email')),

					'mobile_no' => trim($this->input->post('mobile_no')),

					'land_line' => trim($this->input->post('land_line')),

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

					'campus_id' => trim($this->input->post('campus_id')),

				);

				

				$this->db->where('tid', $id);

				$this->db->update('teachers', $data);

				// User Roles

				$rolesarr = $this->input->post('roles');



				$this->db->trans_complete();

				json_response(array('success' => TRUE, 'msg' => 'Edit Teacher Success'));

			}



		}

	}



	function delete(){

		check_permission('admin-del-user');

		$id = intval($this->input->get('id'));



		$this->db->trans_begin();



		// delete user perms

		$this->db->where('userID', $id);

		$this->db->delete('user_perms');



		// delete user roles

		$this->db->where('userID', $id);

		$this->db->delete('user_roles');





		// delete user detail

		// $this->db->where('user_id', $id);

		// $this->db->delete('user_profile');



		// delete user

		$this->db->where('id', $id);

		$this->db->delete('users');



		$this->db->trans_complete();

		json_response(array('success' => TRUE, 'msg' => 'Delete User Success'));

	}



	function edit_password(){

		if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST'){

			$this->form_validation->set_rules('password', 'New Password', 'trim|required');

			if($this->form_validation->run() === FALSE){

				json_response(array('success' => FALSE, 'msg' => validation_errors()));

			}else{

				$user_id = intval($this->input->post('user_id'));

				$this->db->where('id', $user_id);

				$data = array(

					'password' => password_hash(trim($this->input->post('password')), PASSWORD_BCRYPT)

				);

				$this->db->update('users', $data);

				json_response(array('success' => TRUE, 'msg' => 'Change Password Success'));

			}

		}else{

			$user_id = intval($this->input->get('user_id'));

			$this->template_data['user_id'] = $user_id;

			$this->load->view('edit_password', $this->template_data);

		}

	}



	function set_perms(){

		if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST'){

			foreach ($_POST as $k => $v)

			{

				if (substr($k,0,5) == "perm_")

				{

					$permID = str_replace("perm_","",$k);

					if ($v == 'x')

					{

						$strSQL = "DELETE FROM `user_perms` WHERE `userID` = ? AND `permID` = ?";

						$this->db->query($strSQL,array($_POST['user_id'],floatval($permID)));

					} else {

						$strSQL = "REPLACE INTO `user_perms` SET `userID` = ?, `permID` = ?, `value` = ?";

						$this->db->query($strSQL,array($_POST['user_id'],floatval($permID),$v));



					}

				}

			}

			cxp_update_cache();

			json_response(array('success' => TRUE, 'msg' => 'change user permission success'));

		}else{

			$user_id = intval($this->input->get('user_id'));

			$this->db->where('id', $user_id);

			$info = $this->db->get('users')->row();

			$this->template_data['info'] = $info;

			$this->template_data['user_id'] = $user_id;



			$this->load->view('set_perms', $this->template_data);

		}



	}



	function perm_data(){

		$permissions = permissions_list();

	  $perm_parr = array();

	  foreach($permissions as $row){

		$perm_parr[$row->parent_id][] = $row;

	  }



	  $user_id = intval($this->input->post('user_id'));

	  $this->load->library('Member_acl');

			$my_acl=new Member_acl($user_id);

			$this->template_data['my_acl'] = $my_acl;

			$rPerms = $my_acl->getPermArr();

			$this->template_data['rPerms'] = $rPerms;

	  $this->output->set_output('[' . $this->loop_parent($perm_parr, 0, 0, 0, '', $rPerms) . ']');

	}



	function loop_parent($perm_parr, $parent_id, $curloop, $curid, $html, $rPerms){

		if(isset($perm_parr[$parent_id]) && count($perm_parr[$parent_id])>0){



			  foreach($perm_parr[$parent_id] as $row){

				$permKey = $row->permKey;

				$selhtml = '';

				$selhtml .= "<select name=\"perm_" . $row->id . "\">";

				$selhtml .= "<option value=\"1\"";

				if (isset($rPerms[$permKey]) && ($rPerms[$permKey]['value'] === '1' || $rPerms[$permKey]['value'] === true) && $rPerms[$permKey]['inheritted'] != true) { $selhtml .= " selected=\"selected\""; }

				$selhtml .= ">Allow</option>";

				$selhtml .= "<option value=\"0\"";

				if(isset($rPerms[$permKey])){if ($rPerms[$permKey]['value'] === false && $rPerms[$permKey]['inheritted'] != true) { $selhtml .= " selected=\"selected\""; }}

				$selhtml .= ">Deny</option>";

				$selhtml .= "<option value=\"x\"";

				$iVal = '';

				if(isset($rPerms[$permKey])){

					if ($rPerms[$permKey]['inheritted'] == true || !array_key_exists($permKey,$rPerms))

					{

						$selhtml .= " selected=\"selected\"";

						if ($rPerms[$permKey]['value'] === true )

						{

							$iVal = '(Allow)';

						} else {

							$iVal = '(Deny)';

						}

					}

				}else{

					$selhtml .= " selected=\"selected\"";

					$iVal = '(Deny)';

				}

				$selhtml .= ">Inherit $iVal</option>";

                $selhtml .= "</select>";



				  if(isset($perm_parr[$row->id]) && count($perm_parr[$row->id])>0){

					$html .= "{id:" . $row->id . ",name:'" . $row->permName . "', select:'" . $selhtml . "', children:[";

					$html = $this->loop_parent($perm_parr, $row->id, $curloop + 1, $curid, $html, $rPerms) . ']},';



				  }else{

					  $html .= "{id:" . $row->id . ",name:'" . $row->permName . "', select:'" . $selhtml . "'},";

				  }

			  }

		}else{

			// $html .= ']},';

		}

		return $html;

	}

}

// end this file

