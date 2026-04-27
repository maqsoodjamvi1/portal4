<?php
namespace App\Controllers\Admin;


/**
 * Students Results Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Award_list_report extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-students-results');
	}

	/**
	 * Index Page for this controller.
	 */
	 
	public function index()
	{
		$this->load->view('award_list_report', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$response->recordsTotal = $this->db->count_all('subject_results');

		$results = $this->db->get('subject_results')->result();
		
		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			$data = array();
			$allsubjectinfo = array();
 		    $data['student_id'] =	$row->student_id;
			$this->db->where('student_id', $row->student_id);
			$studentsinfo = $this->db->get('students')->row();

			$this->db->where('cls_sec_id', $row->cls_sec_id);
			$classSectioninfo = $this->db->get('class_section')->row();

			$this->db->where('class_id', $classSectioninfo->class_id);
			$classesinfo = $this->db->get('classes')->row();

			$this->db->where('sec_sub_id', $row->sec_sub_id);
			$Sectionsubjectinfo = $this->db->get('section_subjects')->row();

			$this->db->where('sid', $Sectionsubjectinfo->subject_id);
			$allsubjectinfo = $this->db->get('allsubject')->row();

			$this->db->where('session_id', $row->session_id);
			$session_id_info = $this->db->get('academic_session')->row();
			
			if($studentsinfo){
			
			if($classesinfo){			
				$class_name = $classesinfo->class_name;		
			}else{
				$class_name = '';
			}
			
			$data['student'] = $studentsinfo->first_name." ".$studentsinfo->last_name;
			$data['class'] = $class_name;
			$data['subject'] = $allsubjectinfo->subject_name; 
			$data['obtained_marks'] = $row->obtained_marks;
			$data['session_id_info'] = $session_id_info->session_name;
			$response->data[] = $data;
		}
		}
		$this->output->set_output(json_encode($response));
	}

	function add(){
		check_permission('admin-add-students-result');
		
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$sessionData = array(
		'campusid' => $campusid,
		'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;

		$this->db->where('status', 1); 
		$infostudents = $this->db->get('students')->result();
		$this->template_data['infostudents'] = $infostudents;

		$currentrole = currentUserRoles();
		//print_r($currentrole);

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
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

		$this->load->view('award_list_report_edit', $this->template_data);
	}

	
	function edit(){
		check_permission('admin-edit-students-result');
		$id = intval($this->input->get('id'));
		
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$sessionData = array(
		'campusid' => $campusid,
		'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;

		$this->db->where('student_id', $id);
		$info = $this->db->get('subject_results')->row();

		$infostudents = $this->db->get('students')->result();
		$this->template_data['infostudents'] = $infostudents;

		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;

		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;


		$this->template_data['info'] = $info;
		$this->load->view('award_list_report_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('eeid'));
			
		check_permission('admin-add-students-result');
		$this->db->trans_begin();
			
		$studentID = '';
		$result_id = $this->input->post('result_id');
		$sec_sub_id = $this->input->post('sec_sub_id');
		
		foreach($this->input->post('obtained_marks')  as $key => $obtained_marks){
			
			$studentID = $key;
			$obtainedmarks =0;
			foreach($obtained_marks as $key => $obtainedmarks){
				
				$obtained_marks_subject = $key;
				
				$currResultID = $result_id[$studentID][$key];
				$sectionSubID = $sec_sub_id[$key];

				$data = array(
				'student_id' => $studentID,
				'eid' => intval($this->input->post('eid')),
				'session_id' => intval($this->input->post('session_id')),
				'cls_sec_id' => intval($this->input->post('cls_sec_id')),
				'sec_sub_id' => $sectionSubID,
				'obtained_marks' => $obtainedmarks
				);

			

				if($currResultID > 0){
					$this->db->where('result_id', $currResultID);
					$this->db->update('subject_results', $data);
				}else{
				 	$this->db->insert('subject_results', $data);
				 	$new_user_id = $this->db->insert_id();
				}
			}
		}
		
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Add Result Success'));
		
	}

	function get_students(){
	   
	   $eid = intval($this->input->post('eid'));
	   $session_id = intval($this->input->post('session_id'));
	   $campus_id = intval($this->input->post('campus_id'));
	   $id = intval($this->input->post('cls_sec_id'));
	 
	 	if(empty($eid)){
			echo "<div class='text-danger'>Exam is not selected</div><br>";
			exit;
		} 
		
	   if($id == 0){
	   		echo "<div style='background:red;color:#fff;margin:10px 0px; padding:5px;'>Select Class Section</div>";
	   		exit;
	   	}

	   
	   $data = array();
	   $studentsList = '';
  
	   $studentsresults = $this->db->query("SELECT t1.campus_id,t2.student_id,t2.obtained_marks,t2.sec_sub_id FROM exam t1,subject_results t2 WHERE t2.eid=".$eid." AND t2.cls_sec_id=".$id." AND t1.`campus_id`=".$campus_id." group by t2.student_id,t2.sec_sub_id,t2.obtained_marks order by t2.cls_sec_id asc")->result();
	   
	   $this->db->where('cls_sec_id', $id);
		 $classsubjectsinfo = $this->db->get('section_subjects')->result();
		if(!empty($studentsresults)){
	    	$studentsList .= '<input type="hidden" name="eeid"  value="'.$eid.'">';
	  }else{
		    $studentsList .= '<input type="hidden" name="eeid"  value="0">';
	  }

	  $studentsList .= '<input type="hidden" name="session_id"  value="'.$session_id.'">';
		$studentsList .= '<input type="hidden" name="campus_id"  value="'.$campus_id.'">';
		$studentsList .= '<input type="hidden" name="eid"  value="'.$eid.'">';
	  $studentsList .= '<input type="hidden" name="class_id"  value="'.$id.'">';
	   
	   {
	      	
	   $classstudents = $this->db->query("select * from student_class where  session_id=".$session_id." and status=1 and cls_sec_id=".$id)->result();
		
		$classSectioninfo = getClassSection($id);
		
	    $this->db->where('session_id', $session_id);
		$session_id_info = $this->db->get('academic_session')->row();
		
		$this->db->where('campus_id', $campus_id);
		$campus_info = $this->db->get('campus')->row();
		
		$this->db->where('eid', $eid);
		$exam_info = $this->db->get('exam')->row();

		
		$studentsList .= '<div class="table-box"><<table class="table" style="width:100%;"><tr><th>Session</th><th>'.$session_id_info->session_name.'</th><th>Campus</th><th>'.$campus_info->campus_name.'</th><th>Exam</th><th>'.$exam_info->exam_name.'</th><th>Class </th><th>'.$classSectioninfo['sectionclassname'].'</th></tr></table>';
	   $studentsList .= '<table class="table" style="width:100%;">
	   <thead><tr class="header"><th style="width:2%;">#</th><th style="width:5%;">Photo</th><th  style="width:12%">Student</th>';


//foreach($classSubjectsA as $classSectionValue){
  
	$this->db->where('cls_sec_id', $id);
	$this->db->where('eid', $eid);
	$datesheetinfo = $this->db->get('datesheet')->result();

	
if(!empty($datesheetinfo)){	
	$width = (85/count($datesheetinfo));	
	foreach ($datesheetinfo as $key => $value) {
	   	
	   	$currentrole = currentUserRoles();	
		
		if(in_array(5, $currentrole)){	
			$classsubjectsinfo = teacherSubjects($value->sec_sub_id);
			if($classsubjectsinfo){
				$subject_id = $classsubjectsinfo[0]['subject_id'];
				$subject_short_name = $classsubjectsinfo[0]['subject_short_name'];
			}else{
				$subject_id = '';
				$subject_short_name = '';
			}
		}else{
			$this->db->where('sec_sub_id', $value->sec_sub_id);
	  		$classsubjectsinfo = $this->db->get('section_subjects')->row();

	  		$this->db->where('sid', $classsubjectsinfo->subject_id);
			$studentsubject = $this->db->get('allsubject')->row();
			
			$subject_id = $classsubjectsinfo->subject_id;
			$subject_short_name = $studentsubject->subject_short_name;	
		}
	   
	  
		if(isset($value->total_marks)){
			$total_marks = $value->total_marks;
		}else{
			$total_marks = '';
		}
	   	 
	   	if($subject_short_name && $total_marks > 0){
	   	$studentsList .= '<th style="width:'.$width.'%;"><input type="hidden" name="cls_sec_id" value="'.$id.'"><input type="hidden" name="sec_sub_id['.$value->sec_sub_id.']" value="'.$value->sec_sub_id.'">'.$subject_short_name.'<br>'.$total_marks.'<script>
$(".resetSubCol'.$value->sec_sub_id.'").on("change", function() {
  if (this.checked) {
    $(".secSubRes'.$value->sec_sub_id.'").val(0);
  }
});
</script></th>';
	   	}
	   }  	   
	   $studentsList .= '</tr></thead><tbody>';
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
		   
		  $studentsList .= '<tr><td>'.$i.'</td><td> '.$profile_photo.' </td><td><b>'.$studentsinfo->reg_no.'</b><br>'.$studentName.'<input type="hidden" name="student_id[]" value="'.$studentsinfo->student_id.'" class="form-control"></td>';
	$i++;

	$currentrole2 = currentUserRoles();
		
	if(in_array(5, $currentrole2)){
	$userid = $this->session->userdata('member_userid');			
	
	$class_subjects = $this->db->query('SELECT * FROM section_subjects WHERE sec_sub_id IN (SELECT sec_sub_id FROM teacher_subjects WHERE status=1 AND cls_sec_id='.$id.' AND tid = '.$userid.')')->result();
	}else{
	//$this->db->where('cls_sec_id', $id);
	//$class_subjects = $this->db->get('section_subjects')->result();
	$this->db->where('cls_sec_id', $id);
	$this->db->where('eid', $eid);
	$datesheetinfo2 = $this->db->get('datesheet')->result();
	}
	if($datesheetinfo2){
	$textfieldwidth =  (80/count($datesheetinfo2));
	foreach ($datesheetinfo2 as $key => $value) {

		if(isset($value->total_marks)){
			$total_marks = $value->total_marks;
		}else{
			$total_marks = '';
		}

		if($total_marks > 0){

		  $this->db->where('student_id', $row->student_id);
			$this->db->where('sec_sub_id', $value->sec_sub_id);
			$this->db->where('eid', $eid);
		  $resultsdetail = $this->db->get('subject_results')->row();

		    $obtained_marks = 0;
		    $result_id = 0;
			
			if($resultsdetail){
				$result_id = $resultsdetail->result_id;	
				$obtained_marks = $resultsdetail->obtained_marks;
			}
	
		  $studentsList .= '<td style="width:'.$textfieldwidth.'%;">';
			// $studentsList .= print_r($resultsdetail);
		  $studentsList .= $obtained_marks;
		  }
		}
		}
		  $studentsList .= '</td></tr>';
		   }
		   
	   } 
	   }else{
	   	echo '<div class="alert alert-danger" role="alert">Create Datesheet To Enter Result</div>';
	   }
	}

$studentsList .= '</tbody></table></div><style type="text/css">
table {
  text-align: left;
  position: relative;
  border-collapse: collapse; 
}
th, td {
  padding: 0.25rem;
}
tr.red th {
  background: red;
  color: white;
}
tr.green th {
  background: green;
  color: white;
}
tr.purple th {
  background: purple;
  color: white;
}
th {
  background: white;
  position: sticky;
  top: 0; 
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
}
section{overflow:hidden;}
          .table-box {
	overflow: scroll;
	height: 700px;	
}
table {width: 100%;}

table th {}
table td {}

table tr th{position: sticky;left: 0;}

</style>';  

$this->output->set_output($studentsList);	
	
}

}
// end this file
