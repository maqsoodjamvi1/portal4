<?php
namespace App\Controllers\Admin;



/**
 * Fee Amount Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class H_fee_amount extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-h-fee-amount');
	}

	/**
	 * Index Page for this controller.
	 */

	public function index()
	{
		$this->load->view('h_fee_amount', $this->template_data);
	}

	function data(){
		$campus_id = $this->session->userdata('member_campusid');
		$session_id = $this->session->userdata('member_sessionid');
		$sessionid = $this->input->post('session_id'); 
		$schoolinfo = getSchoolInfo();

		$this->db->where('campus_id', $campus_id);
		$this->db->where('status', 1); 
		$blockRoomInfo = $this->db->get('h_block_rooms')->result();

		$this->db->where('system_id', $schoolinfo->system_id);
		$this->db->where('h_flag', 1);
		$fee_type_info = $this->db->get('fee_type')->result();

		$data = '';
		$data .= '<table class="table"><tr><td></td>';
		if(isset($fee_type_info)){
			foreach ($fee_type_info as  $fee_type_value) { 
			$data .= '<th>'.$fee_type_value->fee_type_name.'<input type="hidden" value="'.$fee_type_value->fee_type_id.'" name="fee_type_id[]"></th>';						
			} 
		} 

		if(isset($blockRoomInfo)){
			foreach ($blockRoomInfo as  $blockroomvalue) { 
				 
				$this->db->where('block_id', $blockroomvalue->block_id);
				$blocks_info = $this->db->get('h_blocks')->row();

				$this->db->where('room_id', $blockroomvalue->room_id);
				$this->db->where('status', 1); 
				$rooms_info = $this->db->get('h_rooms')->row();

				// $this->db->where('room_id', $blockroomvalue->room_id);
				// $this->db->where('campus_id', $campus_id);
				// $this->db->where('block_id', $blockroomvalue->block_id);
				// $blockrooms = $this->db->get('h_block_rooms')->row();
				
				$data .= '<tr><th>Room# '.$blockroomvalue->room_no.'<br>'.$rooms_info->room_name.'<input type="hidden" name="block_id[]" value="'.$blockroomvalue->block_id.'" ><input type="hidden" name="room_id[]" value="'.$blockroomvalue->room_id.'" ></th>';
			
			foreach ($fee_type_info as  $fee_type_value) { 

				$this->db->where('campus_id', $campus_id);
				$this->db->where('session_id', $sessionid);
				$this->db->where('block_id', $blockroomvalue->block_id);
				$this->db->where('room_id', $blockroomvalue->room_id);
				$this->db->where('h_fee_type_id', $fee_type_value->fee_type_id);
				$fee_amount_info = $this->db->get('h_fee_amount')->row();
				
				$amount_id = 0;
				$fee_amount = 0;
				if($fee_amount_info){
					$amount_id = $fee_amount_info->amount_id;
					$fee_amount = $fee_amount_info->amount;
				}

				$data .= '<td>';

				if($sessionid == $session_id){
					$data .= '<input type="hidden" class="form-control" name="'.$fee_type_value->fee_type_id.'_'.$blockroomvalue->block_id.'_'.$blockroomvalue->room_id.'_amount_id" id="'.$fee_type_value->fee_type_id.'_'.$blockroomvalue->block_id.'_'.$blockroomvalue->room_id.'_amount_id" value="'.$amount_id.'"><input type="text" class="form-control" name="ftv'.$fee_type_value->fee_type_id.'_ci'.$blockroomvalue->block_id.'_'.$blockroomvalue->room_id.'_amount" id="ftv'.$fee_type_value->fee_type_id.'_ci'.$blockroomvalue->block_id.'_'.$blockroomvalue->room_id.'_amount" value="'.$fee_amount.'">';
				}else{
					$data .= $fee_amount;
				}

				$data .= '</td>';
				} 
				$data .= '</tr>';		
				} 
			}				

		$data .= '</table>';
		$this->output->set_output($data);
	}

	function add(){
		check_permission('admin-add-h-fee-amount');
		$campus_id = $this->session->userdata('member_campusid');
		$session_id = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$info = $this->db->get('fee_amount')->row();
		$this->template_data['info'] = $info;

		$this->db->where('system_id', $schoolinfo->system_id);
		$this->db->where('session_id', $session_id);
		$current_academic_sessioninfo = $this->db->get('academic_session')->row();
		$this->template_data['current_academic_sessioninfo'] = $current_academic_sessioninfo;

		$this->db->where('system_id', $schoolinfo->system_id);
		$academic_sessioninfo = $this->db->get('academic_session')->result();
		$this->template_data['academic_sessioninfo'] = $academic_sessioninfo;

		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$this->load->view('h_fee_amount_edit', $this->template_data);

	}

	function edit(){

		check_permission('admin-edit-h-fee-amount');
		$amount_id = intval($this->input->get('id'));

		$this->db->where('amount_id', $amount_id);
		$info = $this->db->get('fee_amount')->row();
		$this->template_data['info'] = $info;

		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$this->load->view('h_fee_amount_edit', $this->template_data);
	}


	function save(){

		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata('member_campusid');
		$session_id = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');

		$monthfee = $this->db->query('select fee_type_id from fee_type where is_monthly_fee =1 AND system_id='.$schoolinfo->system_id)->row();

		if($monthfee){
			$monthly_fee_id = $monthfee->fee_type_id;
		}else{
			json_response(array('error' => TRUE, 'msg' => 'Set Monthly Fee'));
			exit;
		}

		$campus_bill_info = $this->db->query('select * from campus_bills WHERE status=1 AND campus_id='.$campus_id)->row();
		
		$max_fee_limit = $campus_bill_info->max_fee;
		
		$fee_type_ids = $this->input->post('fee_type_id');
		$block_ids = $this->input->post('block_id');
		$room_ids = $this->input->post('room_id');

		foreach($fee_type_ids as $fee_type_id){
			$i=0;
			foreach($room_ids as $room_id){

			$block_id = $block_ids[$i];	

			$amount = $this->input->post("ftv".$fee_type_id."_ci".$block_id.'_'.$room_id."_amount");
			$amount_id = $this->input->post("".$fee_type_id."_".$block_id.'_'.$room_id."_amount_id");

			if($amount_id > 0){
				$data = array(
				'block_id' => $block_id,
				'room_id' => $room_id,
				'h_fee_type_id' => $fee_type_id,
				'campus_id' => $campus_id,
				'amount' => $amount,
				'session_id' => $session_id,
				'user_id' => $user_id,
				'updated_date' => $date
				);

				$this->db->where('amount_id', $amount_id);
				$this->db->update('h_fee_amount', $data);

			}else{

				$data = array(
				'block_id' => $block_id,
				'room_id' => $room_id,
				'h_fee_type_id' => $fee_type_id,
				'campus_id' => $campus_id,
				'amount' => $amount,
				'session_id' => $session_id,
				'user_id' => $user_id,
				'created_date' => $date
				);
				$this->db->insert('h_fee_amount', $data);	
				$new_timetable_id = $this->db->insert_id();

			}

			$i++;
			}
		} 

		json_response(array('success' => TRUE, 'msg' => 'Update Fee Amount Success'));

	}

}

// end this file

