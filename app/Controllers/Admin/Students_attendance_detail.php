<?php
namespace App\Controllers\Admin;


/**
 * Students Attendance Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 *filesource
 */



class Students_attendance_detail extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-student-attendance');
	}

	/**
	 * Index Page for this controller.
	 */
	 
	public function index()
	{		
		$this->load->view('students_attendance_detail', $this->template_data);
	}

function get_students_byabsentees(){

	    $campus_id = $this->session->userdata('member_campusid');
		$session_id = $this->session->userdata('member_sessionid');

	   $this->load->library('parser');
	   $parent_id = $this->input->post('parent_id');
	   $term_session_id= $this->session->userdata('member_termsessionid');
	   $term_id= $this->session->userdata('member_termid');

	   $this->db->where('term_session_id', $term_session_id);
	   $terms_session_info = $this->db->get('terms_session')->row();
	   //print_r($terms_session_info);

	   $this->db->where('term_id', $term_id);
	   $terms_info = $this->db->get('terms')->row();

	   $this->db->where('campus_id', $campus_id);
	   $info = $this->db->get('campus')->row();

	   $attendance_sms = $info->attendance_sms;
	   $template = $attendance_sms;	
	  
	   
	   
   $data = array();
   $studentsList = '';
      	
   	$classstudents = $this->db->query("select * from student_class where  status=1 and student_id IN(select student_id from students where parent_id=".$parent_id." AND campus_id=".$campus_id.")")->result();
   	
    $studentsList .= '</tr>';
	$i=1;
	   
	foreach($classstudents as $row){

		$total_absentess_info = $this->db->query('SELECT COUNT(attendance_id) AS total_absentees FROM attendance  WHERE student_id='.$row->student_id.' AND DATE BETWEEN "'.$terms_session_info->start_date.'" AND "'.$terms_session_info->end_date.'" AND status="A"')->row();

		$total_el_info = $this->db->query('SELECT COUNT(attendance_id) AS total_el FROM attendance  WHERE student_id='.$row->student_id.' AND DATE BETWEEN "'.$terms_session_info->start_date.'" AND "'.$terms_session_info->end_date.'" AND status="EL"')->row();

		$total_lc_info = $this->db->query('SELECT COUNT(attendance_id) AS total_lc FROM attendance  WHERE student_id='.$row->student_id.' AND DATE BETWEEN "'.$terms_session_info->start_date.'" AND "'.$terms_session_info->end_date.'" AND status="LC"')->row();

		$total_presents_info = $this->db->query('SELECT COUNT(attendance_id) AS total_presents FROM attendance  WHERE student_id='.$row->student_id.' AND DATE BETWEEN "'.$terms_session_info->start_date.'" AND "'.$terms_session_info->end_date.'"  AND status="P"')->row();
	
		$absentess_info = $this->db->query('SELECT * FROM attendance  WHERE student_id='.$row->student_id.' AND DATE BETWEEN "'.$terms_session_info->start_date.'" AND "'.$terms_session_info->end_date.'" AND status != "O" AND status != "P"')->result();

		$this->db->where('student_id', $row->student_id);
		$studentsinfo = $this->db->get('students')->row();
		
		$this->db->where('cls_sec_id', $row->cls_sec_id);
		$classsectioninfo = $this->db->get('class_section')->row();
		
		if($classsectioninfo){			
			$this->db->where('class_id', $classsectioninfo->class_id);
			$classinfo = $this->db->get('classes')->row();
		}

		if($classinfo){
			$this->db->where('section_id', $classsectioninfo->section_id);
			$sectionInfo = $this->db->get('sections')->row();
		}

		if($sectionInfo){
			$sectionName = $sectionInfo->section_name;
		}

		if($classinfo){
			$className = $classinfo->class_name;
		}

		$StudentClass = $className." (".$sectionName.")";

	if($absentess_info){

		$this->db->where('parent_id', $studentsinfo->parent_id);
		$parentssinfo = $this->db->get('parents')->row();

	    $studentName = $studentsinfo->first_name." ".$studentsinfo->last_name;	

	     $imgurl = FCPATH."uploads/".$studentsinfo->profile_photo;
			if($studentsinfo->profile_photo){   
			if(file_exists($imgurl)){

				$profile_photo = "<img style='width:70px;height:70px;text-align:center;display: block;border-radius: 35px;margin: 0 auto;' src='".base_url("uploads/".$studentsinfo->profile_photo)."' >";
						
			}else{

				$profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
			}
			}else{
				
				$profile_photo = "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";
			}
		   

		$studentsList .= $profile_photo.' Student Name: '.$studentName."<br>";
		$studentsList .= $terms_info->name.': '.date('d-m-Y',strtotime($terms_session_info->start_date))." To ".date('d-m-Y',strtotime($terms_session_info->end_date))."<br>";
		$studentsList .= 'A= '.$total_absentess_info->total_absentees." P=".$total_presents_info->total_presents." EL=".$total_el_info->total_el." LC=".$total_lc_info->total_lc;
		$studentsList .= '<div class="table-responsive"><table class="table table-bordered" style="width:100%;"><tr><th style="width:15%;">Date</th><th style="width:15%;">Day</th><th style="width:15%;">Status</th>';

		foreach ($absentess_info as $key => $value) {   
			//print_r($value);
			$timestamp = strtotime($value->date);
		    $day = date('l', $timestamp);
		    $dateAttendance = date('d-m-Y', $timestamp);
		
		  $studentsList .= '<tr><td style=" vertical-align:middle; word-break: break-word;text-align:center;padding:0 4px;"> '.$dateAttendance.'</td>';
		  $studentsList .= '<td style=" vertical-align:middle;padding:0 4px;">'.$day.'</td><td style="text-align:center; vertical-align:middle;padding:0 4px;">'.$value->status.'</td>
	    </tr>';
		}
	    $studentsList .= '</table></div>'; 

		}

		$i++; 
	 } 
	
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
