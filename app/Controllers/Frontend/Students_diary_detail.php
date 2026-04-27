<?php
namespace App\Controllers\Frontend;


/**
 * Students Attendance Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 *filesource
 */



class Students_diary_detail extends MY_Controller {

	function __construct(){
		parent::__construct();
	}

	/**
	 * Index Page for this controller.
	 */
	 
	public function index()
	{		
		$this->load->view('templates/header');
		$this->load->view('students_diary_detail');
		$this->load->view('templates/footer');
	}

function get_students_diary(){

	$systemInfo = getSchoolInfoFront();

    //$campus_id = $this->session->userdata('member_campusid');
	$session_id = $systemInfo['session_id'];

   //$this->load->library('parser');
   $parent_id = $this->input->post('parent_id');
   $campus_id = $this->input->post('campus_id');
   $term_session_id= $systemInfo['term_session_id'];
   $term_id= $systemInfo['term_id'];

   $this->db->where('term_session_id', $term_session_id);
   $terms_session_info = $this->db->get('terms_session')->row();

   $this->db->where('term_id', $term_id);
   $terms_info = $this->db->get('terms')->row();

   $timestamp = strtotime(date('Y-m-d'));
   $day = date('l', $timestamp);
   $dateDiary = date('d-m-Y', $timestamp);

   $data = array();
   
   $studentsList = $terms_info->name.': '.date('d-m-Y',strtotime($terms_session_info->start_date))." To ".date('d-m-Y',strtotime($terms_session_info->end_date))."<br>";
   $studentsList .= "Date: ".$dateDiary.' '.$day;  	
   
   $classstudents = $this->db->query("select * from student_class where  status=1 and student_id IN(select student_id from students where parent_id=".$parent_id." AND campus_id=".$campus_id.")")->result();
   	
    $studentsList .= '</tr>';
	$i=1;
	   
	foreach($classstudents as $row){
	
		$classdiary_info = $this->db->query('SELECT * FROM classdairy  WHERE cls_sec_id='.$row->cls_sec_id.' AND `date`="'.date('Y-m-d').'"')->result();

		// print_r($classdiary_info);
		// exit;

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

	if($classdiary_info){

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
		   

		$studentsList .= $profile_photo.' Student Name: '.$studentName."<br>Class: ".$StudentClass."<br>";
		
		
		$studentsList .= '<div class="table-responsive"><table class="table table-bordered" style="width:100%;table-layout:fixed;
  "><tr><th style="width:15%;">Detail</th>';

		foreach ($classdiary_info as $key => $value) {   

			$this->db->where('sec_sub_id', $value->sec_sub_id);
			$sectionSubjectinfo = $this->db->get('section_subjects')->row();
			$subject_name = '';
			if($sectionSubjectinfo){			
				$this->db->where('sid', $sectionSubjectinfo->subject_id);
				$subjectinfo = $this->db->get('allsubject')->row();
				$subject_name = $subjectinfo->subject_name;
			}

			
		
		    $studentsList .= '<tr>';

		    $studentsList .= '<td style=" vertical-align:middle;padding:2 4px;font-weight:bold;">'.$subject_name.'</td></tr><tr><td style="text-align:center; vertical-align:middle;padding:0 4px;word-break: break-all;" nowrap>'.$value->detail.'</td></tr>';
		}

	    $studentsList .= '</table></div><style>p {
    margin: 0 0 10px;
    word-break: break-all;
    display: inline-block;
}
* { /* this works for all but td */
  word-wrap:break-word;
}
</style>'; 

		}

		$i++; 
	 } 
	
	$this->output->set_output($studentsList);	
	
	}	

}
// end this file
