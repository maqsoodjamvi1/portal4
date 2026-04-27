<?php
namespace App\Controllers\Admin;


/**
 * Academic Session Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2016~2099 TIME Soft Soltions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Ci_session_view_demo extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-ci-session_view');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('ci_session_view_demo', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$schoolinfo = getSchoolInfo();
		$this->db->db_select('timeschool_demo');
		$keyword = '';
		$this->db->select('count(A.id) as ccount', FALSE);
		$this->db->from('ci_sessions A');
		
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		// $offset = $response->draw * $perpage;

		$this->db->select('A.*');
		$this->db->from('ci_sessions A');
		$this->db->order_by('A.id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
		// you retrieve your dat in ci_sesion_ table of the database y a request 
		// .....
		$session_data = $row->data;  // your BLOB data who are a String

   		$return_data = array();  // array where you put your "BLOB" resolved data
             
	   $offset = 0;
	   while ($offset < strlen($session_data)) 
	    {
	       if (!strstr(substr($session_data, $offset), "|")) 
	        {
	          throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
	        }
	          $pos = strpos($session_data, "|", $offset);
	          $num = $pos - $offset;
	          $varname = substr($session_data, $offset, $num);
	          $offset += $num + 1;
	          $data = unserialize(substr($session_data, $offset));
	          $return_data[$varname] = $data;  
	          $offset += strlen(serialize($data)); 
	    }
		
	$campusid = '';
	$userid = '';
	$name = '';
	$phone = '';
	
	if(isset($return_data['member_campusid'])){
		$campusid = $return_data['member_campusid'];
	}

	if(isset($return_data['member_userid'])){
		$userid = $return_data['member_userid'];
	}
	
	if(isset($return_data['member_demoname'])){
		$name = $return_data['member_demoname'];
	}

	if(isset($return_data['member_demophone'])){
		$phone = $return_data['member_demophone'];
	}

	$campus = '';
	$user = '';
	
	$this->db->where('campus_id', $campusid);
	$campusinfo = $this->db->get('campus')->row();
	
	if($campusinfo){
		$campus = $campusinfo->campus_name;
	}

	$this->db->where('id', $userid);
	$userinfo = $this->db->get('users')->row();
		
		if($userinfo){
			$user = $userinfo->first_name." ".$userinfo->last_name;
		}

			$ip_address = $row->ip_address;
			$data = array();
			$data['id'] = $row->id;
			$data['name'] = $name;
			$data['phone'] = $phone;
			$data['ip_address'] = $ip_address;
			$data['timestamp'] = date('d-m-Y h:i:sa',$row->timestamp);
			$data['campusid'] = $campus;
			$data['userid'] = $user;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-academic-session');
		$schoolinfo = getSchoolInfo();
		$academic_session = $this->db->query('SELECT * from academic_session where  system_id='.$schoolinfo->system_id.'  order by session_id desc')->row();
		
		$this->template_data['academic_session'] = $academic_session;
		
		$this->load->view('academic_session_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-academic-session');
		$session_id = intval($this->input->get('id'));
		$this->db->where('session_id', $session_id);
		$info = $this->db->get('academic_session')->row();
		$this->template_data['info'] = $info;
		$this->load->view('academic_session_edit', $this->template_data);
	}

	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		$schoolinfo = getSchoolInfo();
		
		$start_date = date($this->input->post('start_date'));
		$end_date = date($this->input->post('end_date'));

		if($end_date < $start_date)
		{
		    json_response(array('error' => FALSE, 'msg' => 'End date should be greater'));
		    exit; 
		}
		
		$this->form_validation->set_rules('session_name', 'Session Name', 'trim|required');
		
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			if($id === 0){
				check_permission('admin-add-academic-session');
				$this->db->trans_begin();
				
				$this->db->where('system_id', $schoolinfo->system_id);
				$academic_session_info = $this->db->get('academic_session')->row();

				if(empty($academic_session_info)){
					
					$data = array(
					'session_name' => trim($this->input->post('session_name')),
					'start_date' => trim($this->input->post('start_date')),
					'end_date' => trim($this->input->post('end_date')),
					'system_id' => $schoolinfo->system_id,
					'user_id' => $user_id,
					'created_date' => $date 
				);
				
				$this->db->insert('academic_session', $data);
				$new_session_id = $this->db->insert_id();

				$sess_data = [
				 'member_sessionid'	=> $new_session_id,
				 ];
	
				$this->session->set_userdata($sess_data);	
				}else{
					
				$data = array(
					'session_name' => trim($this->input->post('session_name')),
					'start_date' => trim($this->input->post('start_date')),
					'end_date' => trim($this->input->post('end_date')),
					'system_id' => $schoolinfo->system_id,
					'user_id' => $user_id,
					'created_date' => $date
				);
				
				$this->db->insert('academic_session', $data);
				$new_session_id = $this->db->insert_id();
			}

				$this->db->trans_complete();

				$this->db->where('system_id', $schoolinfo->system_id);
				$terms_info = $this->db->get('terms')->row();
				
				if(empty($terms_info->term_id)){
					$this->output->set_output(json_encode(array('term_id' => FALSE, 'msg' => 'Session Success')));
				}else {
					json_response(array('success' => TRUE, 'msg' => 'Add Academic Session Success'));
				}
				
			}else{
			    check_permission('admin-edit-academic-session');
				$this->db->trans_begin();
				$data = array(
					'session_name' => trim($this->input->post('session_name')),
					'start_date' => trim($this->input->post('start_date')),
					'end_date' => trim($this->input->post('end_date')),
					'user_id' => $user_id,
					'updated_date' => $date
				);
				$this->db->where('session_id', $id);
				$this->db->update('academic_session', $data);
				// User Roles
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Academic Session Success'));
			}

		}
	}

	function delete(){
		check_permission('admin-del-academic-session');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

		// delete class
		$this->db->where('id', $id);
		$this->db->delete('academic_session');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Academic Session Success'));
	}


}
// end this file
