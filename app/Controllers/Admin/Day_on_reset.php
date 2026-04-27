<?php
namespace App\Controllers\Admin;


/**
 * Timetable Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Day_on_resetttt extends MY_Controller { 

	function __construct(){
		parent::__construct();
		check_permission('admin-day-on-reset');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('day_on_reset_edit', $this->template_data);
	}

	// function data(){
		
	// 	$campusid = $this->session->userdata('member_campusid');
	// 	$school_timing_type_id = $this->input->post('school_timing_type_id');

		
	// 	$this->db->where('campus_id', $campusid);
	// 	$this->db->where('slot_type', 'FullDay'); 
	// 	$infoslots = $this->db->get('slots')->result();

	// 	$datevalue = date('Y-m-d'); 
	// 	$timestamp = strtotime($datevalue);
	//    	$currDay =  date('l', $timestamp);

	// 	$this->db->where('campus_id', $campusid);
	// 	$this->db->where('status', 1);
	// 	$sectionsinfo = $this->db->get('class_section')->result();
		
	// 	if(empty($sectionsinfo)){
	// 		echo "<div class='btn btn-danger'>Please add class sections to add school timing.</div>";
	// 		exit;
	// 	}
	// 	$sectionsclassinfo = array();
	// 	foreach($sectionsinfo as $section){
		
	// 	$this->db->where('class_id', $section->class_id);
	// 	$classinfo = $this->db->get('classes')->row();

	// 	$this->db->where('section_id', $section->section_id);
	// 	$sectioninfo = $this->db->get('sections')->row();
		 
	// 	$sectionsclassinfo[] = array(
	// 	'section_id' => $section->cls_sec_id,
	// 	'sectionclassname' => $classinfo->class_name." (".$sectioninfo->section_name.")"
	// 	);
	// 	}
		
	// 	$this->db->select('*');
 //    	$this->db->from('slots');
 //    	$this->db->where('campus_id', $campusid);
	// 	$this->db->where('slot_type', 'FullDay'); 
 //    	$totalslots = $this->db->get()->num_rows();

	// 	$subjectinfo = $this->db->get('allsubject')->result();
		
	// 	$data = '';
	// 		$data .= "<table class='table col-lg-12' style='width:100%;'><tr><th></th>"; 	
	// 		$daysName = array('0' => $currDay);

	// 			foreach ($daysName as $key => $day) {		
	// 				$data .= '<th style="width: 132px;"><input type="hidden" name="dayname[]" value="'.$day.'"/>'.$day.'<br>Set Off <input type="checkbox" id="setclockoff_'.$day.'"><br>Set to column <input type="checkbox" id="setclock_'.$day.'"> <script> 
	// 					$(function(){
	// 					$("#setclock_'.$day.'").click(function(){
	// 					if ( this.checked ) {
	// 					var checkintime = $("#'.$day.'_'.$sectionsclassinfo[0]['section_id'].'_checkin_date").val();
	// 					$(".clockpicker_'.$day.'").val(checkintime);
	// 					var checkouttime = $("#'.$day.'_'.$sectionsclassinfo[0]['section_id'].'_checkout_date").val();
	// 					$(".clockpickercheckout_'.$day.'").val(checkouttime);
	// 					}
	// 					});
	// 					$("#setclockoff_'.$day.'").click(function(){
	// 					if ( this.checked ) {
	// 						$(".clockpicker_'.$day.'").val("08:00");
	// 						$(".clockpickercheckout_'.$day.'").val("08:00");
	// 					}
	// 					});
	// 					});	 
	// 					</script></th>';
	// 			}

	// 			$data .= '<th>Select Class</th></tr>';	
	// 			if(isset($sectionsclassinfo)){
	// 			$i=0;
	// 			foreach($sectionsclassinfo as $section) {
	// 				$data .= '<tr><th>'.$section['sectionclassname'].'<br> <script> 
	// 					$(function(){
	// 					$("#setclock_'.$section['section_id'].'").click(function(){
	// 					if(this.checked){
	// 					var checkintime = $("#Monday_'.$section['section_id'].'_checkin_date").val();
	// 					$(".clockpicker_'.$section['section_id'].'").val(checkintime);
	// 					var checkouttime = $("#Monday_'.$section['section_id'].'_checkout_date").val();
	// 					$(".clockpickercheckout_'.$section['section_id'].'").val(checkouttime);
	// 					}
	// 					});
	// 					$("#setclockoff_'.$day.'").click(function(){
	// 					if ( this.checked ) {
	// 						$(".clockpicker_'.$day.'").val("08:00");
	// 						$(".clockpickercheckout_'.$day.'").val("08:00");
	// 					}
	// 					});
	// 					});	 
	// 					</script></th>';
	// 					foreach ($daysName as $key => $value) {
	// 						$this->db->where('dayname', $value);
	// 						$this->db->where('cls_sec_id', $section['section_id']);
	// 						$this->db->where('type_id', $school_timing_type_id);
	// 						$school_timings_info = $this->db->get('school_timings')->row();
	// 					$data .= '<td><div class="input-group clockpicker " data-placement="left" data-align="top" data-autoclose="true">
	// 					    <input type="text" class="form-control clockpicker_'.$value.' clockpicker_'.$section['section_id'].'" placeholder="Check In" name="'.$value.'_'.$section['section_id'].'_checkin_date" id="'.$value.'_'.$section['section_id'].'_checkin_date" value="';
	// 					    if($school_timings_info){
	// 					    	$data .= $school_timings_info->checkin_timing;
	// 					    }
	// 					   $data .= '">
	// 					    <span class="input-group-addon btn btn-default">
	// 					        <span class="far fa-clock"></span>
	// 					    </span>
	// 					</div><div class="input-group clockpicker " data-placement="left" data-align="top" data-autoclose="true">
	// 					    <input type="text" class="form-control clockpickercheckout_'.$value.' clockpickercheckout_'.$section['section_id'].'" placeholder="Check Out" name="'.$value.'_'.$section['section_id'].'_checkout_date"  id="'.$value.'_'.$section['section_id'].'_checkout_date" value="';
	// 					    if($school_timings_info){
	// 					    	$data .= $school_timings_info->checkout_timing;
	// 					    }
	// 					   $data .= '">
	// 					    <span class="input-group-addon btn btn-default">
	// 					        <span class="far fa-clock"></span>
	// 					    </span>
	// 					</div>';
	// 					$data .= '</td>';
	// 				 	}
	// 				 	$data .= '<td><input name="section_id[]" value="'.$section['section_id'].'" type="checkbox" id="'.$section['section_id'].'"></td>';
	// 				 	$data .= '</tr>';	
						
	// 			 } 
	// 			 } 				
	// 		$data .=  '</table>
	// 		<script>
	// 		$(function(){
	// 			$(".clockpicker").clockpicker();
	// 		});	
	// 		</script>';

	// 	 $this->output->set_output($data);
	// }

	// function add(){
	// 	check_permission('admin-add-timetable');
	// 	$campusid = $this->session->userdata('member_campusid');

	// 	$info = $this->db->get('school_timings')->result_array();
	// 	$this->template_data['info'] = $info;

	// 	$infoschooltimingtypes = $this->db->get('school_timing_types')->result_array();
	// 	$this->template_data['infoschooltimingtypes'] = $infoschooltimingtypes;
	
	// 	$sectionsclassinfo = userClassSections();
	// 	$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		
	// 	$subjectinfo = $this->db->get('allsubject')->result();
	// 	$this->template_data['subjectinfo'] = $subjectinfo;

	// 	$this->load->view('day_on_reset_edit', $this->template_data);
	// }

	function edit(){
	
		check_permission('admin-edit-teacher-subject');
		$id = intval($this->input->get('id'));
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('sst', $id);
		$this->db->where('campus_id', $campusid);
		$info = $this->db->get('teacher_subjects')->row();
		$this->template_data['info'] = $info;
		
		$this->db->where('campus_id', $campusid);
		$infoteachers = $this->db->get('teachers')->result();
		$this->template_data['infoteachers'] = $infoteachers;

		$classinfo = $this->db->get('classes')->result();
		$this->template_data['classinfo'] = $classinfo;		

		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;
		
		$this->load->view('day_on_reset_edit', $this->template_data);
	}
	
	function save(){
		$id = 0;//intval($this->input->post('id'));
		$campus_id = intval($this->session->userdata['member_campusid']);
		$school_timing_type_id = $this->input->post('school_timing_type_id');
		$sessionid = $this->session->userdata('member_sessionid');
		$dateCU = date('Y-m-d  H:i:s');
		$user_id = $this->session->userdata['member_userid'];
		
		$section_ids = $this->input->post('section_id');
		$days = $this->input->post('dayname');
		if($section_ids){
			$cls_sec_List = implode(', ', $section_ids); 
		}else{
			json_response(array('error' => TRUE, 'msg' => 'Select Section to Update'));
			exit;
		}	
			
		check_permission('admin-add-timetable');
		$this->db->trans_begin();
		
		foreach($section_ids as $section_id){
			
			$studentClassInfo = $this->db->query('select student_id from student_class where session_id ='.$sessionid.' AND  cls_sec_id='.$section_id)->result();

			$checkintime = $this->input->post($days[0]."_".$section_id."_checkin_date");
			$checkouttime = $this->input->post($days[0]."_".$section_id."_checkout_date");

			if($checkintime == $checkouttime){
				$status = 'O';
			}else{
				$status = 'P';
			}
			
			if($studentClassInfo){

			 foreach ($studentClassInfo as $key => $value) {
			 		
				$studentInfo = $this->db->query('select * from attendance where date="'.date('Y-m-d').'" AND student_id='.$value->student_id)->row();

					$data = array(
						'student_id' => $value->student_id,
						'date' => date('Y-m-d'),
						'status' => $status,
						'checkin' => $checkintime,
						'checkout' => $checkouttime,
						'el_duration' => 0,
						'lc_duration' => 0,
						'created_date' => $dateCU,
						'user_id' => $user_id
					);
				
				if($studentInfo){
					$this->db->where('attendance_id', $studentInfo->attendance_id);
					$this->db->update('attendance', $data);
				}else{
					$this->db->insert('attendance', $data);
					$new_attendance_id = $this->db->insert_id();
				}	
				
			}

			}	
		}

				
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Add School Timing Success'));
			
	}

}
// end this file
