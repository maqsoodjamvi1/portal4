<?php
namespace App\Controllers\Admin;


/**
 * Hostel Room Beds Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */



class H_room_beds extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-room-beds');
		$this->load->library('session');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('h_room_beds', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.room_bed_id) as ccount', FALSE);
		$this->db->from('h_room_beds A');
		$this->db->where('(A.block_room_id IN(select block_room_id from h_block_rooms where status=1 AND campus_id=' . $this->db->escape($campusid) . '))');
				
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('h_room_beds A');
		$this->db->where('(A.block_room_id IN(select block_room_id from h_block_rooms where status=1 AND campus_id=' . $this->db->escape($campusid) . '))');
		
		$this->db->order_by('A.room_bed_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			
			$this->db->where('block_room_id', $row->block_room_id);
			$blockRoomsInfo = $this->db->get('h_block_rooms')->row();

			$this->db->where('block_id', $blockRoomsInfo->block_id);
			$blockInfo = $this->db->get('h_blocks')->row();

			$this->db->where('room_id', $blockRoomsInfo->room_id);
			$roomsInfo = $this->db->get('h_rooms')->row();	
		
			$data = array();
			$data['id'] = $row->room_bed_id;
			$data['block_name'] = $blockInfo->block_name;
			$data['room_name'] = $roomsInfo->room_name;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function data2(){
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();
		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
		$blockRoomsinfo = $this->db->get('h_block_rooms')->result();
		$blocksRoomsinfo = array();
		foreach($blockRoomsinfo as $blockroom){
		
		$this->db->where('block_id', $blockroom->block_id);
		$blockinfo = $this->db->get('h_blocks')->row();

	   $this->db->where('room_id', $blockroom->room_id);
	   $roominfo = $this->db->get('h_rooms')->row();
		
		$blocksRoomsinfo[] = array(
		'block_room_id' => $blockroom->block_room_id,
		'blocroomname' => $roominfo->room_name." (".$blockinfo->block_name.")"
		);
		
		}
		//$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		$this->db->where('campus_id', $campusid);
	  	$bedsinfo = $this->db->get('h_beds')->result();
		$data = '';
		$data .= '<section class="section2">
		<div class="table-box"><table class="table" style="margin-bottom:0px;"><thead><tr class="header"><th></th>';
          		if(isset($bedsinfo)){
				foreach ($bedsinfo as  $bedvalue) { 
            		$data .= '<th><input type="hidden" name="subjects[]"  value="'.$bedvalue->bed_id.'"  />'.$bedvalue->bed_no.'</th>';
            	 } 
            } 
        $data .= '</tr></thead><tbody>';
          	if(isset($blocksRoomsinfo)){
				foreach ($blocksRoomsinfo as  $roomvalue) { 
				$data .= '<tr><th style="line-height:1;"><input type="hidden" name="sections[]"  value="'.$roomvalue["block_room_id"].'"  />'.$roomvalue["blocroomname"].'
				</th>';
              	if(isset($bedsinfo)){ 
					foreach ($bedsinfo as  $bedvalue) { 

						$this->db->where('bed_id', $bedvalue->bed_id);
						$this->db->where('block_room_id', $roomvalue["block_room_id"]);
						$roomBeds = $this->db->get('h_room_beds')->row();

						$data .= '<td  style="text-align:center;vertical-align:middle;padding:3px 8px;line-height:1;">';
						if($roomBeds){
							$data .= '<input type="checkbox" ';
            				if($roomBeds->status == 1){
            					$data .= ' checked ';
            				}
            				$data .= ' class="setRoomBeds setlock_'.$roomvalue["block_room_id"].' setlock_'.$bedvalue->bed_id.'" name="'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'_room_beds[]"  value="'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'"  />';
						}else{
            				$data .= '<input type="checkbox" class="setRoomBeds setlock_'.$roomvalue["block_room_id"].' setlock_'.$bedvalue->bed_id.'"  name="'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'_room_beds[]"  value="'.$roomvalue["block_room_id"].'_'.$bedvalue->bed_id.'"  />';
            			}
            			$data .= '</td>';
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
          $data .= '</tbody></table></div></section><style>
          section{overflow:hidden;}
          .table-box {
	overflow: scroll;
	height: 500px;	
}
table {width: 100%;}

table th {	padding: 7px;background-color: #ddd;}
table td {}

table tr th{position: sticky;left: 0;}


</style><script type="text/javascript">
		$(function(){
         $(".setRoomBeds").on("change",function() {
            
            if(this.checked){
            	var status = 1;
            }else{
            	var status = 0;
            }

            var room_beds_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "admin.php?c=h_room_beds&m=updateRoomBeds", 
                data: {room_beds_id:room_beds_id,status:status},
                success:function(res){
            		toastr.success(res.msg);
			  	} 
            });

           });  
      }); 
      </script>';

		$this->output->set_output($data);
	}


	function updateRoomBeds(){
		$campusid = $this->session->userdata('member_campusid');
		$status = $this->input->post('status');
		$room_beds_ids = $this->input->post('room_beds_id');
		$roomBedArr = explode('_', $room_beds_ids);

		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');

		$block_room_id = $roomBedArr[0];
		$bed_id = $roomBedArr[1];

		
		$this->db->where('block_room_id', $block_room_id);
		$this->db->where('bed_id', $bed_id);
		$roomBeds = $this->db->get('h_room_beds')->row();

		if($roomBeds){

			$data = array(
				'user_id' => $user_id,
				'updated_date' => $date,
				'status' => $status
			);

			$this->db->where('block_room_id', $block_room_id);
			$this->db->where('bed_id', $bed_id);
			$this->db->update('h_room_beds', $data);

		}else{
			$data = array(
				'block_room_id' => $block_room_id,
				'bed_id' =>  $bed_id,
				'campus_id' => $campusid,
				'user_id' => $user_id,
				'created_date' => $date,
				'status' => 1
			);
			$this->db->insert('h_room_beds', $data);
		}
		
		json_response(array('success' => TRUE, 'msg' => 'Add Room Beds Success'));
	}

	function add(){
		check_permission('admin-add-room-beds');
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
		'sectionclassname' => $classinfo->class_name." (".$sectioninfo->short_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		

	    $subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('h_room_beds_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-room-beds');
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
		
		$this->load->view('h_room_beds_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata['member_campusid'];
		$subjects = $this->input->post('subjects');	
		$sections = $this->input->post('sections');	
		$schoolinfo = getSchoolInfo();
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
				
		check_permission('admin-add-room-beds');
		$this->db->trans_begin();
		foreach ($sections as $key => $section_id) {
			foreach($subjects as $subject_id){
			$ssvalue = $this->input->post($section_id.'_'.$subject_id.'_section_subjects');
				if(!empty($ssvalue)){		
					$data = array(
						'subject_id' => $subject_id,
						'cls_sec_id' =>  $section_id,
						'user_id' => $user_id,
						'created_date' => $date
					);
					$this->db->insert('section_subjects', $data);
				}
			}
		}
		
		$new_user_id = $this->db->insert_id();

		$this->db->trans_complete();
		
		json_response(array('success' => TRUE, 'msg' => 'Add Room Beds Success'));
		
	}

	function delete(){
		check_permission('admin-del-room-beds');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('cs_id', $id);
		$this->db->delete('section_subjects');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Room Beds Success'));
	}

}
// end this file
