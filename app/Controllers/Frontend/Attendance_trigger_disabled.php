<?php
namespace App\Controllers\Frontend;


/**
 * Students Complaint Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 *filesource
 */

//

class Attendance_trigger extends BaseController {

	// function __construct(){
	// 	parent::__construct();
	// 	//check_permission('admin-student-attendance');
	// }

	/**
	 * Index Page for this controller.
	 */
	 
	public function index()
	{

		//print_r("Test");
		$sessionid = $this->session->userdata('member_sessionid');	

		$campusinfo = $this->db->get('campus')->result();

		$this->db->where('session_id', $sessionid);
		$academic_session = $this->db->get('academic_session')->row();

		$date = date('Y-m-d');
		$nameOfDay = date('l', strtotime($date));
		//exit;


		foreach ($campusinfo as $key => $campus) {
			
			$this->db->where('campus_id', $campus->campus_id);
			$sectionsinfo = $this->db->get('class_section')->result();
					
			//exit;


			foreach ($sectionsinfo as $key => $section) {
				
				$schooltimingsinfo = $this->db->query("SELECT *,(checkout_timing-checkin_timing) AS duration FROM school_timings WHERE section_id =".$section->cls_sec_id." AND dayname ='".$nameOfDay."'  AND type_id = (SELECT type_id FROM school_timing_types WHERE STATUS=1 )")->row();

			// 	echo "<pre>";
			// print_r($schooltimingsinfo);
			// echo "</pre>";
				
				if($schooltimingsinfo){
				if($schooltimingsinfo->duration != 0){


						$this->db->where('status', 1);
						$this->db->where('cls_sec_id', $section->cls_sec_id);
						$students = $this->db->get('student_class')->result();
						
						foreach ($students as $key => $student) {
							// $attendanceinfo = '';
							$this->db->where('student_id', $student->student_id);
							$this->db->where('date', date('Y-m-d'));
							$attendanceinfo = $this->db->get('attendance')->result();


							if(empty($attendanceinfo)){
								$data = array(
								'student_id' => $student->student_id,
								'date' => date('Y-m-d'),
								'status' => 'P',
								'checkin' => $schooltimingsinfo->checkin_timing,
								'checkout' => $schooltimingsinfo->checkout_timing,
								'el_duration' => 0,
								'lc_duration' => 0
								
							);
							
							$this->db->insert('attendance', $data);
						}

					}
						

						// echo "<pre>";
						// print_r($students);
						// echo "</pre>";
				}
			}
				//echo  "Attendance Added Successfully";

				//print_r($schooltimingsinfo);
			}

			
		}
		echo "Request Success";
		//$this->load->view('attendance_trigger', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$sessionid = $this->session->userdata('member_sessionid');

		
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		//$q = $this->db->get()->row();
		$response->recordsTotal = $this->db->count_all('attendance');

		$results = $this->db->get('attendance')->result();

		$response->recordsFiltered = $response->recordsTotal;

		$this->db->where('session_id', $sessionid);
		$academic_session = $this->db->get('academic_session')->row();

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$allsubjectinfo = array();
 		    $data['id'] =	$row->cid;
			$this->db->where('student_id', $row->student_id);
			$studentsinfo = $this->db->get('students')->row();

			$this->db->where('student_id', $row->student_id);
			$studentclass= $this->db->get('student_class')->row();

			$this->db->where('class_id', $studentclass->class_id);
			$classesinfo = $this->db->get('classes')->row();
			//print_r($studentclass);
			$terms_session = $this->db->query("SELECT * FROM terms_session where session_id = ".$sessionid." and '".$row->date."' between start_date and end_date")->result();
			if($terms_session){				
				$this->db->where('term_id', $terms_session[0]->term_id);
				$termsinfo = $this->db->get('terms')->row();
				$term_name = $termsinfo->name;
			}else{
			 	$term_name = '';	
			}

		    //$date['id'] = $row->cid;
			$data['student'] = $studentsinfo->first_name." ".$studentsinfo->last_name;
			$data['class'] = $classesinfo->class_name;
			$data['session_name'] = $academic_session->session_name;
			$data['term_name'] = $term_name;
			$data['date'] = $row->date;
			$data['detail'] = $row->detail; 
			$response->data[] = $data;
		}
		
		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-student-attendance');
		
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$sessionData = array(
		'campusid' => $campusid,
		'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;

		$infostudents = $this->db->get('students')->result();
		$this->template_data['infostudents'] = $infostudents;

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$this->db->where('campus_id', $campusid);
		$sectionsinfo = $this->db->get('sections')->result();
		$sectionsclassinfo = array();
		foreach($sectionsinfo as $section){
		
		$this->db->where('class_id', $section->class_id);
		$classinfo = $this->db->get('classes')->row();
		 
		$sectionsclassinfo[] = array(
		'section_id' => $section->sec_id,
		'sectionclassname' => $classinfo->class_name." (".$section->section_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	
		
		$this->db->where('campus_id', $campusid); 
		$campusinfo = $this->db->get('campus')->result();
		$this->template_data['campusinfo'] = $campusinfo;
		
		$this->db->where('campus_id', $campusid); 
		$this->db->where('session_id', $sessionid);
		$examinfo = $this->db->get('exam')->result();
 		$this->template_data['examinfo'] = $examinfo;

		$this->db->where('session_id', $sessionid); 
		$academic_session = $this->db->get('academic_session')->result();
 		$this->template_data['academic_session'] = $academic_session;

		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('students_attendance_edit', $this->template_data);
	}

	
	function edit(){
		check_permission('admin-edit-student-attendance');
		$id = intval($this->input->get('id'));
		
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$sessionData = array(
		'campusid' => $campusid,
		'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;

		$this->db->where('student_id', $id);
		$info = $this->db->get('studentsresults')->row();

		$infostudents = $this->db->get('students')->result();
		$this->template_data['infostudents'] = $infostudents;

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->template_data['info'] = $info;
		$this->load->view('students_attendance_edit', $this->template_data);
	}

	function save(){
	
		$student_ids = $this->input->post('student_id');
		check_permission('admin-add-student-attendance');
		$this->db->trans_begin();


		foreach($student_ids as $key => $student_id){

			$this->db->query('Delete from attendance where student_id='.$student_id.' AND date='.$this->input->post('date'));

				if($this->input->post($student_id.'_status') == 'A'){
					$checkouttime = $this->input->post($student_id.'_checkin_date');
				}else{
					$checkouttime = $this->input->post($student_id.'_checkout_date');
				}

				$data = array(
					'student_id' => $student_id,
					'date' => $this->input->post('date'),
					'status' => $this->input->post($student_id.'_status'),
					'checkin' => $this->input->post($student_id.'_checkin_date'),
					'checkout' => $checkouttime
					
				);
				
			$this->db->insert('attendance', $data);
			$new_user_id = $this->db->insert_id();
		}
		
		
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Add Attendance Success'));
		
	}

	function get_students_byclass(){
	   
	   $eid = $this->input->post('eid');
	   $session_id = $this->input->post('session_id');
	   $campus_id = $this->input->post('campus_id');
	   $id = $this->input->post('section_id');
	   $subject_id = $this->input->post('subject_id');
	   $datevalue = $this->input->post('date'); 
	  
	   $timestamp = strtotime($datevalue);
	   $day = date('l', $timestamp);
		
	   
	   $data = array();
	   $studentsList = '';
	   
		$studentsList .= '<input type="hidden" name="campus_id"  value="'.$campus_id.'">';
	    $studentsList .= '<input type="hidden" name="class_id"  value="'.$id.'">';
	   
	   {
	      	
	   	$classstudents = $this->db->query("select * from student_class where  status=1 and section_id = ".$id)->result();
	   	
		$this->db->where('class_id', $classstudents[0]->class_id);
		$classesinfo = $this->db->get('classes')->row();

		$this->db->where('campus_id', $campus_id);
		$this->db->where('class_id', $classstudents[0]->class_id);
		$this->db->where('day', $day);
		$schooltime_info = $this->db->get('school_timings')->row();

	    $this->db->where('session_id', $session_id);
		$session_id_info = $this->db->get('academic_session')->row();
		
		$this->db->where('campus_id', $campus_id);
		$campus_info = $this->db->get('campus')->row();
		
		$this->db->where('eid', $eid);
		$exam_info = $this->db->get('exam')->row();
	
	
	   $studentsList .= '<div class="table-responsive"><table class="table" style="width:100%;">
	   <tr><th style="width:15%;">Photo</th><th style="width:15%;">Name</th><th style="width:15%;">A</th><th style="width:15%;">P</th><th style="width:15%;">LC</th><th style="width:15%;">EL</th>';  
	   $studentsList .= '</tr>';
  	   $i=1;
	   
	   foreach($classstudents as $row){
 		
 		$this->db->where('student_id', $row->student_id);
		$this->db->where('date', $datevalue);
		$attendance_info = $this->db->get('attendance')->row();	

		//print_r($attendance_info);    

		   $this->db->where('student_id', $row->student_id);
		   $studentsinfo = $this->db->get('students')->row();
		 
		if($studentsinfo){

		   $studentName = $studentsinfo->first_name." ".$studentsinfo->last_name;	

		   $imgurl = FCPATH."uploads/".$studentsinfo->profile_photo;
			if($studentsinfo->profile_photo){   
			if(file_exists($imgurl)){

				$profile_photo = "<img style='width:50px;height:50px;text-align:center;display: block;border-radius: 30px;margin: 0 auto;' src='".base_url("uploads/".$studentsinfo->profile_photo)."' >";
						
			}else{

				$profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
			}
			}else{
				
				$profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
			}
		   
		  $studentsList .= '<tr><td style=" vertical-align:middle; word-break: break-word;"> '.$profile_photo.'<input type="hidden" name="student_id[]" value="'.$studentsinfo->student_id.'" class="form-control"> </td>';
		  $studentsList .= '<td style=" vertical-align:middle;">'.$studentName.'<br>'.$studentsinfo->reg_no.'</td><td>
  	<div class="funkyradio">
       <div class="funkyradio-default">
    	<input type="radio"';
    	if($attendance_info){
	    	if( $attendance_info->status == 'A'){
	    		$studentsList .=  ' checked="checked"';
	    	}
    	}
    	   $studentsList .=  ' id="'.$studentsinfo->student_id.'_absent_toggle" value="A" name="'.$studentsinfo->student_id.'_status"> 
    	  <label for="'.$studentsinfo->student_id.'_absent_toggle">A</label>
   	</div>
   	</div>
   </td><td><div class="funkyradio">
        <div class="funkyradio-default">
     <input type="radio"';
     	if($attendance_info){
	    	if( $attendance_info->status == 'P'){
	    		$studentsList .=  ' checked="checked"';
	    	}
    	}	
    	   $studentsList .=  ' class="toggle_option" value="P" id="'.$studentsinfo->student_id.'_present_toggle" name="'.$studentsinfo->student_id.'_status">
     <label for="'.$studentsinfo->student_id.'_present_toggle"> P </label>
</div>
   	</div>
     </td><td><div class="funkyradio">
     <div class="funkyradio-default">
    	<input type="radio"';
    	if($attendance_info){
	    	if( $attendance_info->status == 'LC'){
	    		$studentsList .=  ' checked="checked"';
	    	}
    	}
    	   $studentsList .=  ' class="toggle_option" value="LC" id="'.$studentsinfo->student_id.'_late_comming_toggle" name="'.$studentsinfo->student_id.'_status"> 
    	<label for="'.$studentsinfo->student_id.'_late_comming_toggle">LC</label>
   <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
    <input type="text" class="form-control" name="'.$studentsinfo->student_id.'_checkin_date" value="';
    if($attendance_info){
    	 $studentsList .= $attendance_info->checkin;
    }else{
    	 $studentsList .= $schooltime_info->checkin_timing;
    }
    
     $studentsList .=  '">
    <span class="input-group-text">
        <span class="far fa-clock"></span>
    </span>
</div>
    </div>
   	</div> 
    </td><td><div class="funkyradio">
      <div class="funkyradio-default">
    	<input type="radio"';
    	if($attendance_info){
    		if( $attendance_info->status == 'EL'){
    		$studentsList .=  ' checked="checked"';
    		}
    	}
    	   $studentsList .=  ' class="toggle_option" id="'.$studentsinfo->student_id.'_early_left_toggle" value="EL" name="'.$studentsinfo->student_id.'_status"> 
    	<label for="'.$studentsinfo->student_id.'_early_left_toggle">EL</label> <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
    <input type="text" class="form-control"   name="'.$studentsinfo->student_id.'_checkout_date" value="';
    if($attendance_info){
    	 $studentsList .= $attendance_info->checkout;
    }else{
    	 $studentsList .= $schooltime_info->checkout_timing;
    }
    
     $studentsList .=  '">
    <span class="input-group-text">
        <span class="far fa-clock"></span>
    </span>
</div>
    </div>
   	</div><script>
$(function(){
$("input[name="'.$studentsinfo->student_id.'_checkin_date").click(function () {
    $("#'.$studentsinfo->student_id.'_late_comming_toggle").prop("checked", true);
}
});	
</script>
    </td>
    </tr>';
}
	$i++; 
 } 
}
$studentsList .= '</table></div><script>
$(function(){
$(".clockpicker").clockpicker();
});	
</script>'; 
$this->output->set_output($studentsList);	
	
	}

function delete(){
		check_permission('admin-del-attendance');
		$id = intval($this->input->get('id'));
		$this->db->trans_begin();
		// delete user
		$this->db->where('id', $id);
		$this->db->delete('classes');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Attendance Success'));
	}
}
// end this file
