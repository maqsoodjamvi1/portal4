<?php
namespace App\Controllers\Admin;


/**
 * Hostel Block Rooms Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class H_block_rooms extends MY_Controller { 

	function __construct(){
		parent::__construct();
		check_permission('admin-block-rooms');
		$this->load->library('session');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('h_block_rooms', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.block_room_id) as ccount', FALSE);
		$this->db->from('h_block_rooms A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

	
		$this->db->select('A.*');
		$this->db->from('h_block_rooms A');
		$this->db->where('(A.campus_id =' . $this->db->escape($campus_id) . ')');
		
		$this->db->order_by('A.block_room_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
	
		$this->db->where('block_id', $row->block_id);
		$blockinfo = $this->db->get('h_blocks')->row();


	    $this->db->where('room_id', $row->room_id);
	    $roominfo = $this->db->get('h_rooms')->row();

			$data = array();
			$data['id'] = $row->block_room_id;
			$data['block_name'] = $blockinfo->block_name;
			$data['room_name'] = $roominfo->room_name;
			$response->data[] = $data;

		}

		$this->output->set_output(json_encode($response));
	}

	function data2(){
		$campusid = $this->session->userdata('member_campusid');
		
		$this->db->where('campus_id', $campusid);
		$blockRoomsInfo = $this->db->get('h_block_rooms')->result();
		
		$this->db->where('campus_id', $campusid);
		$blocksInfo = $this->db->get('h_blocks')->result();

	    $this->db->where('campus_id', $campusid);
	    $roomsInfo = $this->db->get('h_rooms')->result();
		
		$data = '<p>Select checkbox to save Block Rooms</p>';
		$data .= '<table class="table"><tr><th></th>';
          	if(isset($blocksInfo)){
				foreach ($blocksInfo as  $blockvalue) { 
            		$data .= '<th><input type="hidden" name="section_id[]"  value="'.$blockvalue->block_id.'"  />'.$blockvalue->block_name.'</th>';
            	 } 
            } 
        $data .= '</tr>';
          	if(isset($roomsInfo)){
				foreach ($roomsInfo as  $roominfo) { 
					
					$data .= '<tr><td><input type="hidden" name="class_id[]"  value="'.$roominfo->room_id.'"  />'.$roominfo->room_name.'</td>';
              	if(isset($blocksInfo)){
					foreach ($blocksInfo as  $blockvalue) { 

						$this->db->where('room_id', $roominfo->room_id);
						$this->db->where('campus_id', $campusid);
						$this->db->where('block_id', $blockvalue->block_id);
						$blockrooms = $this->db->get('h_block_rooms')->row();
						
						if($blockrooms)
						{
            				$data .= '<td><input type="checkbox" ';
            				if($blockrooms->status == 1){
            					$data .= ' checked ';
            				}
            				$data .= ' class="setRoomBlock setlock_'.$blockvalue->block_id.'"  name="'.$roominfo->room_id.'_'.$blockvalue->block_id.'_room_block[]"  value="'.$roominfo->room_id.'_'.$blockvalue->block_id.'"  /></td>';
            			}else{
            				$data .= '<td><input type="checkbox" class="setRoomBlock setlock_'.$blockvalue->block_id.'"  name="'.$roominfo->room_id.'_'.$blockvalue->block_id.'_room_block[]"  value="'.$roominfo->room_id.'_'.$blockvalue->block_id.'"  /></td>';
            			}
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
        $data .= '</table><script type="text/javascript">
		$(function(){
         $(".setRoomBlock").on("change",function() {
            
            if(this.checked){
            	var status = 1;
            }else{
            	var status = 0;
            }

            var rooms_block_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "admin.php?c=h_block_rooms&m=updateBlockRooms", 
                data: {rooms_block_id:rooms_block_id,status:status},
                success:function(res){
            		toastr.success(res.msg);
			  	} 
            });

           });  
      }); 
      </script>';  
 
		$this->output->set_output($data);
	}

	function updateBlockRooms(){
		$campusid = $this->session->userdata('member_campusid');
		$status = $this->input->post('status');
		$rooms_block_id = $this->input->post('rooms_block_id');
		$roomBlockArr = explode('_', $rooms_block_id);

		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');

		$room_id = $roomBlockArr[0];
		$block_id = $roomBlockArr[1];

		$this->db->where('room_id', $room_id);
		$this->db->where('campus_id', $campusid);
		$this->db->where('block_id', $block_id);
		$blockrooms = $this->db->get('h_block_rooms')->row();

		if($blockrooms){

			$data = array(
				'user_id' => $user_id,
				'updated_date' => $date,
				'status' => $status
			);

			$this->db->where('room_id', $room_id);
			$this->db->where('campus_id', $campusid);
			$this->db->where('block_id', $block_id);
			$this->db->update('h_block_rooms', $data);

		}else{
			$data = array(
				'room_id' => $room_id,
				'block_id' =>  $block_id,
				'campus_id' =>  $campusid,
				'user_id' => $user_id,
				'created_date' => $date,
				'status' => 1
			);
			$this->db->insert('h_block_rooms', $data);
		}
		
		json_response(array('success' => TRUE, 'msg' => 'Add Block Rooms Success'));
	}

	function add(){
		check_permission('admin-add-block-rooms');
		$campusid = $this->session->userdata('member_campusid');
		
		$this->db->where('campus_id', $campusid);
		$classsectioninfo = $this->db->get('class_section')->result();
		$sectionsclassinfo = array();
		foreach($classsectioninfo as $section){
		
		$this->db->where('class_id', $section->class_id);
		$classinfo = $this->db->get('classes')->row();

		$this->db->where('section_id', $section->section_id);
		$sectioninfo = $this->db->get('sections')->row();
		
		$sectionsclassinfo[] = array(
		'section_id' => $section->cls_sec_id,
		'sectionclassname' => $classinfo->class_name." (".$sectioninfo->section_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		

	   $subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('h_block_rooms_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-block-rooms');
		$id = intval($this->input->get('id'));
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('cs_id', $id);
		$info = $this->db->get('section_subjects')->row();
		$this->template_data['info'] = $info;	
		
		$this->db->where('campus_id', $campusid);
		$classsectioninfo = $this->db->get('class_section')->result();
		$sectionsclassinfo = array();
		foreach($classsectioninfo as $section){
		
		$this->db->where('class_id', $section->class_id);
		$classinfo = $this->db->get('classes')->row();

		$this->db->where('section_id', $section->section_id);
		$sectioninfo = $this->db->get('sections')->row();
		
		$sectionsclassinfo[] = array(
		'section_id' => $section->cls_sec_id,
		'sectionclassname' => $classinfo->class_name." (".$sectioninfo->section_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		
		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;
		
		$this->load->view('class_section_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata['member_campusid'];
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$schoolinfo = getSchoolInfo();
		$section_ids = $this->input->post('section_id');	
		$class_ids = $this->input->post('class_id');
		$cls_sec_ids = $this->input->post('cls_sec_id');	
				
		check_permission('admin-add-block-rooms');
		$this->db->trans_begin();

		//print_r($cls_sec_ids);

		// foreach ($class_ids as $key => $class_id) {
		// 	foreach($section_ids as $key => $section_id){
			
		// 	$ssvalue = $this->input->post($class_id.'_'.$section_id.'_class_section');
				
		// 		if(!empty($ssvalue)){	

		// 			$this->db->where('class_id', $class_id);
		// 			$this->db->where('campus_id', $campus_id);
		// 			$this->db->where('section_id', $section_id);
		// 			$classsection = $this->db->get('class_section')->row();

		// 			if($classsection){
		// 				print_r($classsection);
		// 				echo "OLD Entry<br>";
		// 			}else{
		// 				echo "New Entry<br>";
		// 				$data = array(
		// 				'class_id' => $class_id,
		// 				'section_id' =>  $section_id,
		// 				'campus_id' =>  $campus_id,
		// 				'user_id' => $user_id,
		// 				'created_date' => $date,
		// 				'status' => 1
		// 			);
		// 			//$this->db->insert('class_section', $data);
		// 			}

					
		// 		}

		// 	}
		// }
		
		$new_user_id = $this->db->insert_id();
		$this->db->trans_complete();
		$this->db->where('system_id', $schoolinfo->system_id);
		$subjects_info = $this->db->get('allsubject')->row();

		if(empty($subjects_info->sid)){
			$this->output->set_output(json_encode(array('subject_id' => FALSE, 'msg' => 'Class Section Success')));
		}else{
			json_response(array('success' => TRUE, 'msg' => 'Add Class Section Success'));
		}

	}

	function delete(){
		check_permission('admin-del-block-rooms');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('cs_id', $id);
		$this->db->delete('section_subjects');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Section Subjects Success'));
	}

}
// end this file
