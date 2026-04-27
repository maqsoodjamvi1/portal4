<?php
namespace App\Controllers\Admin;


/**
 * Students Complaint Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 *filesource
 */



class Attendance_termreport extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-student-attendance');
	}

	/**
	 * Index Page for this controller.
	 */
	 
	public function index()
	{
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

		$this->load->view('attendance_termreport', $this->template_data);
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

			$this->db->query('Delete from attendance where student_id='.$student_id.' AND date="'.$this->input->post('date').'"');

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


	$list = array();
	$d = date('d', strtotime('last day of this month', strtotime($datevalue))); // get max date of current month: 28, 29, 30 or 31.

	for ($i = 1; $i <= $d; $i++) {
	    $list[] = $datevalue . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
	}

	
	   
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
	
	
	   $studentsList .= '<div class="table-responsive"><table class="table" style="width:98%;">
	   <tr><th style="width:15%;">Photo</th><th style="width:15%;">Name</th>';
	   foreach ($list as $key => $date) {
	   		$studentsList .= '<th class="verticalTableHeader"><p>'.$date.'</p></th>';  	
	     }  
	   $studentsList .= '</tr>';
  	   $i=1;
	   
	foreach($classstudents as $row){   

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
		   
		  $studentsList .= '<tr><td style=" vertical-align:middle; word-break: break-word;"> '.$profile_photo.'</td>';
		  $studentsList .= '<td style=" vertical-align:middle;">'.$studentName.'<br>'.$studentsinfo->reg_no.'</td>';
	    foreach ($list as $key => $date) {
			$this->db->where('student_id', $row->student_id);
			$this->db->where('date', $date);
			$attendance_info = $this->db->get('attendance')->row();
			//print_r($attendance_info);	
			$attendance_status = '-';
			if($attendance_info){
				$attendance_status = $attendance_info->status;
			}
			$studentsList .='<td>'.$attendance_status.'</td>';
		}	

     $studentsList .='</tr>';
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
