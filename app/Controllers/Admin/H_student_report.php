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



class H_student_report extends MY_Controller {

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
		$campusid = $this->session->userdata('member_campusid');

		$campus_info = $this->db->query('select * from campus WHERE campus_id='.$campusid)->row();
		$this->template_data['campus_info'] = $campus_info;

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

		
		$this->load->view('h_student_beds_report', $this->template_data);
	}

	function data(){
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
		$blocksInfo = $this->db->get('h_blocks')->result();
		
		//$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
	  	$roomsInfo = $this->db->get('h_rooms')->result();

		$blockCount = $this->db->query('select count(block_id) as total from h_blocks where status=1 and campus_id='.$campusid)->row();  
		$width = (90/$blockCount->total);
		$data = '';
		$data .= '<section class="section2">
		<div class="table-box"><table class="table table-bordered" style="margin-bottom:0px;"><thead><tr class="header">';
          	if(isset($blocksInfo)){
				foreach ($blocksInfo as  $blockvalue) { 
            		$data .= '<th style="width:'.$width.'%"><input type="hidden" name="subjects[]"  value="'.$blockvalue->block_id.'"  />'.$blockvalue->block_name.'</th>';
            	 } 
            } 
        $data .= '</tr></thead><tbody>';
          	if(isset($roomsInfo)){ 
					foreach ($roomsInfo as  $roomvalue) { 
				// $data .= '<tr><th style="line-height:1;width:100px;"><input type="hidden" name="sections[]"  value="'.$roomvalue->room_id.'"  />Room# '.$roomvalue->room_name.' 
				// </th>';
              	
				if(isset($blocksInfo)){
				foreach ($blocksInfo as  $blockvalue) { 		
						$this->db->where('status', 1);
						$this->db->where('room_id', $roomvalue->room_id);
						$this->db->where('block_id', $blockvalue->block_id);
						$blockRooms = $this->db->get('h_block_rooms')->row();
					if($blockRooms){
						$this->db->where('status', 1);
						$this->db->where('block_room_id', $blockRooms->block_room_id);
						$roomBeds = $this->db->get('h_room_beds')->result();
						

					$data .= '<td  style="text-align: left;vertical-align:middle;padding:3px 8px;line-height:1;font-size: 14px !important;">';
					$data .= "R# ".$blockRooms->room_no."<br>";
					if($roomBeds){
					$nCount = 1;	
					foreach ($roomBeds as $key => $roomBedsValue) {
						
						$this->db->where('status', 1);
						$this->db->where('session_id', $sessionid);
						$this->db->where('bed_id', $roomBedsValue->bed_id);
						$this->db->where('block_room_id', $roomBedsValue->block_room_id);
						$h_student_beds = $this->db->get('h_student_bed')->row();

						if($h_student_beds){
							
							$this->db->where('status', 1);
							$this->db->where('student_id', $h_student_beds->student_id);
							$h_student_info = $this->db->get('students')->row();

							
							
							if($h_student_info){

								$this->db->where('parent_id', $h_student_info->parent_id);
								$h_parent_info = $this->db->get('parents')->row();

								$data .= "<strong>".$nCount.'. '.$h_student_info->first_name.' '.$h_student_info->last_name."</strong><br>";
								$data .= '<a href="https://wa.me/'.$h_parent_info->whatsapp.'">'.$h_parent_info->whatsapp."<a><br>";
							}
							
						}else{
							$data .= $nCount.' '.'****<br>';
							
						}	
						$nCount++;
						}
					}
         			$data .= '</td>';
            	 	}else{
            	 		$data .= '<td></td>';
            	 	} 
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
          $data .= '</tbody></table></div></section>';

		$this->output->set_output($data);
	}



	function data2(){
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
		$blocksInfo = $this->db->get('h_blocks')->result();
		
		//$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
	  	$roomsInfo = $this->db->get('h_rooms')->result();

		$roomCount = $this->db->query('select count(room_id) as total from h_rooms where status=1 and campus_id='.$campusid)->row();  
		$width = (90/$roomCount->total);
		$data = '';
		$data .= '<section class="section2">
		<div class="table-box"><table class="table table-bordered" style="margin-bottom:0px;"><thead><tr class="header"><th style="width:10%"></th>';
          	if(isset($roomsInfo)){
				foreach ($roomsInfo as  $roomvalue) { 
            		$data .= '<th style="width:'.$width.'%"><input type="hidden" name="subjects[]"  value="'.$roomvalue->room_id.'"  />'.$roomvalue->room_name.'</th>';
            	 } 
            } 
        $data .= '</tr></thead><tbody>';
          	if(isset($blocksInfo)){
				foreach ($blocksInfo as  $blockvalue) { 
				$data .= '<tr><th style="line-height:1;width:100px;"><input type="hidden" name="sections[]"  value="'.$blockvalue->block_id.'"  />Room# '.$blockvalue->block_name.' 
				</th>';
              	if(isset($roomsInfo)){ 
					foreach ($roomsInfo as  $roomvalue) { 

						$this->db->where('status', 1);
						$this->db->where('room_id', $roomvalue->room_id);
						$this->db->where('block_id', $blockvalue->block_id);
						$blockRooms = $this->db->get('h_block_rooms')->row();
					if($blockRooms){
						$this->db->where('status', 1);
						$this->db->where('block_room_id', $blockRooms->block_room_id);
						$roomBeds = $this->db->get('h_room_beds')->result();
						

					$data .= '<td  style="text-align: left;vertical-align:middle;padding:3px 8px;line-height:1;font-size: 11px !important;">';
					$data .= "R# ".$blockRooms->room_no."<br>";
					if($roomBeds){
					$nCount = 1;	
					foreach ($roomBeds as $key => $roomBedsValue) {
						
						$this->db->where('status', 1);
						$this->db->where('session_id', $sessionid);
						$this->db->where('bed_id', $roomBedsValue->bed_id);
						$this->db->where('block_room_id', $roomBedsValue->block_room_id);
						$h_student_beds = $this->db->get('h_student_bed')->row();

						if($h_student_beds){
							
							$this->db->where('status', 1);
							$this->db->where('student_id', $h_student_beds->student_id);
							$h_student_info = $this->db->get('students')->row();
							
							if($h_student_info){

								$this->db->where('parent_id', $h_student_info->parent_id);
								$h_parent_info = $this->db->get('parents')->row();

								$data .= "<strong>".$nCount.'. '.$h_student_info->first_name.' '.$h_student_info->last_name."</strong><br>";
								$data .= $h_parent_info->father_contact."<br>";
							}
							
						}else{
							$data .= $nCount.' '.'****<br>';
							
						}	
						$nCount++;
						}
					}
         			$data .= '</td>';
            	 	}else{
            	 		$data .= '<td></td>';
            	 	} 
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
          $data .= '</tbody></table></div></section>';

		$this->output->set_output($data);
	}


	function data3(){
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
		$blocksInfo = $this->db->get('h_blocks')->result();
		
		//$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
	  	$roomsInfo = $this->db->get('h_rooms')->result();

		$blockCount = $this->db->query('select count(block_id) as total from h_blocks where status=1 and campus_id='.$campusid)->row();  
		$width = (90/$blockCount->total);
		$data = '';
		$data .= '<section class="section2">
		<div class="table-box"><table class="table table-bordered" style="margin-bottom:0px;"><thead><tr class="header">';
          	if(isset($blocksInfo)){
				foreach ($blocksInfo as  $blockvalue) { 
            		$data .= '<th style="width:'.$width.'%"><input type="hidden" name="subjects[]"  value="'.$blockvalue->block_id.'"  />'.$blockvalue->block_name.'</th>';
            	 } 
            } 
        $data .= '</tr></thead><tbody>';
          	if(isset($roomsInfo)){ 
					foreach ($roomsInfo as  $roomvalue) { 
				// $data .= '<tr><th style="line-height:1;width:100px;"><input type="hidden" name="sections[]"  value="'.$roomvalue->room_id.'"  />Room# '.$roomvalue->room_name.' 
				// </th>';
              	
				if(isset($blocksInfo)){
				foreach ($blocksInfo as  $blockvalue) { 		
						$this->db->where('status', 1);
						$this->db->where('room_id', $roomvalue->room_id);
						$this->db->where('block_id', $blockvalue->block_id);
						$blockRooms = $this->db->get('h_block_rooms')->row();
					if($blockRooms){
						$this->db->where('status', 1);
						$this->db->where('block_room_id', $blockRooms->block_room_id);
						$roomBeds = $this->db->get('h_room_beds')->result();
						

					$data .= '<td  style="text-align: left;vertical-align:middle;padding:3px 8px;line-height:1;font-size: 14px !important;">';
					$data .= "R# ".$blockRooms->room_no."<br>";
					if($roomBeds){
					$nCount = 1;	
					foreach ($roomBeds as $key => $roomBedsValue) {
						
						$this->db->where('status', 1);
						$this->db->where('session_id', $sessionid);
						$this->db->where('bed_id', $roomBedsValue->bed_id);
						$this->db->where('block_room_id', $roomBedsValue->block_room_id);
						$h_student_beds = $this->db->get('h_student_bed')->row();



						if($h_student_beds){
							
							$unpaidFee = $this->db->query('SELECT SUM(amount-discount) as total FROM fee_chalan WHERE student_id='.$h_student_beds->student_id.' AND status="unpaid" AND fee_type_id IN(SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.')')->row();

							$this->db->where('status', 1);
							$this->db->where('student_id', $h_student_beds->student_id);
							$h_student_info = $this->db->get('students')->row();
							
							if(!empty($unpaidFee->total)){

								$this->db->where('parent_id', $h_student_info->parent_id);
								$h_parent_info = $this->db->get('parents')->row(); 

								$data .= "<strong>".$nCount.'. '.$h_student_info->first_name.' '.$h_student_info->last_name."</strong>&nbsp;&nbsp;&nbsp;&nbsp;";
								$data .= $unpaidFee->total;
								$unpaidTotalFee =  'RS '.$unpaidFee->total;
								$data .= '<br><a href="https://wa.me/'.$h_parent_info->whatsapp.'?text=your amount '.$unpaidTotalFee.' is pending">'.$h_parent_info->whatsapp."<a><br>";
							}
							
						}else{ 
							$data .= $nCount.' '.'****<br>';
							
						}	
						$nCount++;
						}
					}
         			$data .= '</td>';
            	 	}else{
            	 		$data .= '<td></td>';
            	 	} 
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
          $data .= '</tbody></table></div></section>';

		$this->output->set_output($data);
	}


	function report2(){
		check_permission('admin-add-room-beds');
		$campusid = $this->session->userdata('member_campusid');

		$campus_info = $this->db->query('select * from campus WHERE campus_id='.$campusid)->row();
		$this->template_data['campus_info'] = $campus_info;

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

		$this->load->view('h_student_beds_report2', $this->template_data);
	}


	function defaulter(){
		check_permission('admin-add-room-beds');
		$campusid = $this->session->userdata('member_campusid');

		$campus_info = $this->db->query('select * from campus WHERE campus_id='.$campusid)->row();
		$this->template_data['campus_info'] = $campus_info;

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

		$this->load->view('h_student_beds_defaulter', $this->template_data);
	}
	
}
// end this file
