<?php
namespace App\Controllers\Admin;


/**
 * Datesheet Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Datesheet_classwise extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-datesheet');
	}

	/**
	 * Index Page for this controller.
	*/
	
	public function index()
	{

	$campus_id = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');

	$schoolinfo = getSchoolInfo();

	$currentrole = currentUserRoles();

	if(in_array(5, $currentrole)){
		$sectionsclassinfo = teacherSubjectSections();
	}else{ 
		$sectionsclassinfo = userClassSections();
	}


	$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;			

	$data =  $this->data();

	$this->template_data['data'] = $data;
	$this->load->view('datesheet_classwise', $this->template_data);

	}


	/**
	 * Without Syllabus Page for this controller.
	*/
	
	public function datesheet_without_syllabus()
	{

		$campus_id = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$currentrole = currentUserRoles();

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{ 
			$sectionsclassinfo = userClassSections();
		}


		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;			

		$data =  $this->data();

		$this->template_data['data'] = $data;
		$this->load->view('datesheet_without_syllabus', $this->template_data);

	}
public function data() {
    $cls_sec_id = $this->input->get('cls_sec_id');
    $schoolinfo = getSchoolInfo();
    $campus_id = $this->session->userdata('member_campusid');
    $sessionid = $this->session->userdata('member_sessionid');

    $data = array();

    $this->db->where('campus_id', $campus_id);
    $campusinfo = $this->db->get('campus')->row();

    // Fetch exam info
    $this->db->where('status', 0);
    $this->db->where('session_id', $sessionid);
    $this->db->where('campus_id', $campus_id);
    $examinfo = $this->db->get('exam')->row();
    $exam_name = $examinfo ? $examinfo->exam_name : '';
    $eid = $examinfo ? $examinfo->eid : null;

    // Get classes to process
    $cls_sec_ids = array();
    if (!empty($cls_sec_id)) {
        $cls_sec_ids[] = $cls_sec_id;
    } else {
        $this->db->select('DISTINCT(cls_sec_id)');
        $this->db->where('session_id', $sessionid);
        $this->db->where('status', 1);
        $this->db->order_by("cls_sec_id", "ASC");
        $classes = $this->db->get('student_class')->result();
        foreach ($classes as $class) {
            $cls_sec_ids[] = $class->cls_sec_id;
        }
    }

    foreach ($cls_sec_ids as $current_cls_sec_id) {
        $classSectioninfo = getClassSection($current_cls_sec_id);
        $subjectdatesheet = array();

        // Get datesheet for current section
        if ($eid) {
            $this->db->where('eid', $eid);
            $this->db->where('cls_sec_id', $current_cls_sec_id);
            $this->db->order_by("exam_date", "ASC");
            $datesheetinfo = $this->db->get('datesheet')->result();
            
            // Process only if datesheet exists
            if (!empty($datesheetinfo)) {
                foreach ($datesheetinfo as $datesheet) {
                    $Secsubjects = $this->db->get_where('section_subjects', array(
                        'sec_sub_id' => $datesheet->sec_sub_id,
                        'status' => 1
                    ))->row();

                    if ($Secsubjects) {
                        $academicsubjects = $this->db->get_where('allsubject', array('sid' => $Secsubjects->subject_id))->row();
                        if ($academicsubjects) {
                            $subjectdatesheet[] = array(
                                'exam_date' => date('j-M-Y', strtotime($datesheet->exam_date)),
                                'dayOfWeek' => date("l", strtotime($datesheet->exam_date)),
                                'subjectname' => $academicsubjects->subject_name,
                                'total_marks' => $datesheet->total_marks,
                                'syllabus' => $datesheet->syllabus
                            );
                        }
                    }
                }

                // Add to data only if subjects exist
                if (!empty($subjectdatesheet)) {

                	$data[] = array(
					    'class' => $classSectioninfo['sectionclassname'],
					    'campus_name' => $schoolinfo->system_name,
					    'campus_location' => $campusinfo->location, // Use 'location' from the campus table
					    'terms' => $exam_name,
					    'datesheetbysubject' => $subjectdatesheet 
					);
                    // $data[] = array(
                    //     'class' => $classSectioninfo['sectionclassname'],
                    //     'campus_name' => $schoolinfo->system_name,
                    //     'campus_location' => $schoolinfo->campus_address, // Corrected from location to campus_address
                    //     'terms' => $exam_name,
                    //     'datesheetbysubject' => $subjectdatesheet
                    // );
                }
            }
        }
    }

    // Removed the erroneous duplicate data assignment here

    return $data;
}


	// public function data(){

	// 	$hide_marks = '';
	// 	$cls_sec_id = $this->input->get('cls_sec_id');
	// 	$hide_marks = $this->input->get('hide_marks');
	// 	$schoolinfo = getSchoolInfo();
	// 	$campus_id = $this->session->userdata('member_campusid');
	// 	$sessionid = $this->session->userdata('member_sessionid');
	// 	$termsessionid = $this->session->userdata('member_termsessionid');
	// 	$termid = $this->session->userdata('member_termid');
	// 	//print($termid);
	// 	$data = array();

	// 	$sessionData = array(
	// 	'campusid' => $campus_id,
	// 	'sessionid' => $sessionid 
	// 	);
	// 	$this->template_data['sessionData'] = $sessionData;
	// 	if(empty($cls_sec_id)){
	// 		return;
	// 	}
		
	// 	if($cls_sec_id){
	// 		$student_class = $this->db->query('SELECT t1.cls_sec_id,t2.student_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE t1.student_id = t2.student_id  and t1.session_id='.$sessionid.' AND t1.status=1 AND t1.cls_sec_id='.$cls_sec_id.' AND t2.campus_id='.$campus_id.' order by t1.cls_sec_id asc')->result(); 
	// 	}else{
	// 		$student_class = $this->db->query('SELECT t1.cls_sec_id,t2.student_id, t2.campus_id,t2.reg_no,t2.first_name,t2.last_name,t2.parent_id FROM student_class t1, students t2 WHERE t1.student_id = t2.student_id and t1.status=1 and t1.session_id='.$sessionid.' AND t2.campus_id='.$campus_id.' order by t1.cls_sec_id asc')->result(); 
	// 	}
			
	// foreach ($student_class as $studentinfo) {

	// 	$this->db->where('cls_sec_id', $studentinfo->cls_sec_id);
	// 	$this->db->where('status', 1);
	// 	$class_subjects = $this->db->get('section_subjects')->result();

	// 	$this->db->where('student_id', $studentinfo->student_id);
	// 	$student_info = $this->db->get('students')->row();
		
	// 	$this->db->where('campus_id', $campus_id);
	// 	$campus_info = $this->db->get('campus')->row();

	// 	$studentsFee = $this->db->query('SELECT SUM(amount-discount) AS feeTotal FROM fee_chalan WHERE status="unpaid" AND student_id IN(select student_id from students where campus_id='.$campus_id.' AND parent_id='.$student_info->parent_id.' AND status=1)')->row();


	// if($student_info){
		
	// 	$this->db->where('parent_id', $student_info->parent_id);
	// 	$parent_info = $this->db->get('parents')->row();
		
	// 	$classSectioninfo = getClassSection($studentinfo->cls_sec_id);
	// 	//print_r($class_subjects);

	// 	$subjectdatesheet = array();
	// 	//foreach($class_subjects as $subect_id){	
	// 		//print_r($subect_id);

	// 	   	// $this->db->where('sid', $subect_id->subject_id);
	// 	   	// $subjects = $this->db->get('allsubject')->row();  

	// 		//$this->db->where('term_id',$termid);
	// 		$this->db->where('status',0);
	// 		$this->db->where('session_id',$sessionid);	
	// 		$this->db->where('campus_id',$campus_id);	
	// 		$examinfo = $this->db->get('exam')->row();
			
	// 		//print_r($examinfo);
	// 		$exam_name = '';
	// 		if($examinfo){
	// 		$exam_name = $examinfo->exam_name; 
			
	// 		//$this->db->where('sec_sub_id',$subect_id->sec_sub_id);
	// 		$this->db->where('eid',$examinfo->eid);
	// 		$this->db->where('cls_sec_id',$studentinfo->cls_sec_id);
	// 		$this->db->order_by("exam_date", "ASC");
	// 		$datesheetinfo = $this->db->get('datesheet')->result();
			
	// 		//print_r($datesheetinfo);
			
			
	// 	foreach($datesheetinfo as $datesheet){	
			
	// 		$this->db->where('sec_sub_id', $datesheet->sec_sub_id);
	// 		$this->db->where('status', 1);
	// 		$Secsubjects = $this->db->get('section_subjects')->row();
			
	// 		// if(empty($Secsubjects)){
	// 		// 	return;
	// 		// }
	// 		if($Secsubjects){
	// 		$this->db->where('sid', $Secsubjects->subject_id);
	// 		$academicsubjects = $this->db->get('allsubject')->row();
			
	// 		if($academicsubjects){
				
	// 			$exam_date = DateTime::createFromFormat('Y-m-d',$datesheet->exam_date);
	//    			$exam_date = $exam_date->format('j-M-Y');
	
	// 			$subjectname = $academicsubjects->subject_name;
	// 		    $dayOfWeek = date("l", strtotime($datesheet->exam_date));
	// 			if($datesheet->total_marks > 0){
	// 				if($hide_marks == ''){
	// 					$subjectdatesheet[] = array(
	// 						'exam_date' => $exam_date,
	// 						'dayOfWeek' => $dayOfWeek,
	// 						'subjectname' => $subjectname,
	// 						'total_marks' => $datesheet->total_marks,
	// 						'syllabus' => $datesheet->syllabus,
										
	// 					);
	// 				}else{	
	// 					$subjectdatesheet[] = array(
	// 						'exam_date' => $exam_date,
	// 						'dayOfWeek' => $dayOfWeek,
	// 						'subjectname' => $subjectname,
	// 						'syllabus' => $datesheet->syllabus,
										
	// 					);
	// 				}
	// 			}
			
	// 			}
			  
	// 		}
	// 		}
	// 	}
	// 		// echo "<pre>";
	// 		// 	print_r($subjectdatesheet);
	// 		// 	echo "</pre>";
	// 	//}
	// 	//$terms = array();
	// 	$f_name = '';
	// 	$father_contact = '';
	// 	$mother_contact = '';
	// 	if($parent_info){
	// 	$f_name = $parent_info->f_name;
	// 	$father_contact = $parent_info->father_contact;
	// 	$mother_contact = $parent_info->mother_contact;
	// 	}


	// 	$data[] = array(
	// 	'class' => $classSectioninfo['sectionclassname'],	
	// 	'campus_name' => $schoolinfo->system_name,
	// 	'campus_location' => $campus_info->location,
	// 	'name' => $student_info->first_name." ".$student_info->last_name,
	// 	'profile_photo' => $student_info->profile_photo, 	
	// 	'f_name' => $f_name,
	// 	'father_contact' => $father_contact,
	// 	'mother_contact' => $mother_contact,
	// 	'reg_no' => $student_info->reg_no,
	// 	'terms' => $exam_name,
	// 	'datesheetbysubject' => $subjectdatesheet,
	// 	'remaining_dues' => $studentsFee->feeTotal
	// 	);
		  
	// 	}

	// }
	
	// return $data;
	
	// }
	
	// function add(){
	// 	check_permission('admin-add-datesheet');
	// 	$schoolinfo = getSchoolInfo();
	// 	$sessionid = $this->session->userdata('member_sessionid');
	// 	$campusid = $this->session->userdata('member_campusid');
		
	// 	$this->db->where('system_id', $schoolinfo->system_id);
	// 	$terminfo = $this->db->get('terms')->result();
 // 		$this->template_data['terminfo'] = $terminfo;

 // 		$this->db->where('campus_id', $campusid); 
	// 	$this->db->where('session_id', $sessionid);
	// 	$examinfo = $this->db->get('exam')->result();
 // 		$this->template_data['examinfo'] = $examinfo;

		
	// 	$sectioninfo = userClassSections();
 // 		$this->template_data['sectioninfo'] = $sectioninfo;
	
	// 	$this->db->where('session_id', $sessionid);	
	// 	$this->db->where('system_id', $schoolinfo->system_id);	
	// 	$academic_session = $this->db->get('academic_session')->result();
		
 // 		$this->template_data['academic_session'] = $academic_session;
	// 	$this->load->view('datesheet_edit', $this->template_data);
	// }

	// function edit(){
	// 	check_permission('admin-edit-datesheet');
	// 	$id = intval($this->input->get('id'));
		
	// 	$campusid = $this->session->userdata('member_campusid');
	// 	$sessionid = $this->session->userdata('member_sessionid');
	// 	$sessionData = array(
	// 	'campusid' => $campusid,
	// 	'sessionid' => $sessionid
	// 	);
	// 	$this->template_data['sessionData'] = $sessionData;

	// 	$this->db->where('id', $id);
	// 	$info = $this->db->get('allsubject')->row();
	// 	$this->template_data['info'] = $info;
		
	// 	$classesinfo = $this->db->get('classes')->result();
 // 		$this->template_data['classesinfo'] = $classesinfo;
	
	// 	$academic_session = $this->db->get('academic_session')->result();
 // 		$this->template_data['academic_session'] = $academic_session;

	// 	$this->load->view('datesheet_edit', $this->template_data);
	// }

	// function save(){
	// 	$user_id = $this->session->userdata['member_userid'];
	// 	$date = date('Y-m-d H:i:s');
	// 	$id = intval($this->input->post('eeid'));
	// 	$dids = $this->input->post('did');
	// 	$sec_sub_ids = $this->input->post('sec_sub_id');
	// 	$total_marks = $this->input->post('total_marks');
	// 	$exam_date = $this->input->post('exam_date');
	// 	$syllabus = $this->input->post('syllabus');
		
	// 	$campusid = $this->session->userdata('member_campusid');
	// 	$sessionid = $this->session->userdata('member_sessionid');
	// 	$schoolinfo = getSchoolInfo();

		
	// 	$this->db->trans_begin();
	// 	for($i=0; $i < count($sec_sub_ids); $i++){
				
	// 		 $sec_sub_id = $sec_sub_ids[$i];
	// 		 $examdate = $exam_date[$i];
	// 		 $did = $dids[$i];

	// 		 if($examdate){
	// 		 	$subjectexamdate = DateTime::createFromFormat('d/m/Y',$examdate);
	// 		 	$subjectexamdate = $subjectexamdate->format('Y-m-d');
	// 		 }else{
	// 		 	json_response(array('error' => TRUE, 'msg' => 'Select Exam Date'));
	// 		 	exit;
	// 		 }
	// 		 $totalmarks = $total_marks[$i];
	// 		 $sub_syllabus = $syllabus[$i];

	// 		if($did == 0){ 
	// 				check_permission('admin-add-datesheet');	
	// 				$data = array(
	// 					'eid' => intval($this->input->post('eid')),
	// 					'cls_sec_id' => intval($this->input->post('section_id')),
	// 					'sec_sub_id' => $sec_sub_id,
	// 					'exam_date' => $subjectexamdate,
	// 					'total_marks' => $totalmarks,
	// 					'syllabus' => $sub_syllabus,
	// 					'created_date' => $date,
	// 					'user_id' => $user_id
	// 				);
					
	// 				$this->db->insert('datesheet', $data);
	// 				$new_user_id = $this->db->insert_id();
	// 		}else{
	// 			check_permission('admin-edit-datesheet');
	// 			$data = array(
	// 				'eid' => trim($this->input->post('eeid')),
	// 				'cls_sec_id' => intval($this->input->post('section_id')),
	// 				'sec_sub_id' => $sec_sub_id,
	// 				'exam_date' => $subjectexamdate,
	// 				'total_marks' => $totalmarks,
	// 				'syllabus' => $sub_syllabus,
	// 				'updated_date' => $date,
	// 				'user_id' => $user_id
	// 			);
				
	// 			$this->db->where('did', $did);
	// 			$this->db->update('datesheet', $data);
	// 		}
					
	// 		}
				
	// 		$this->db->trans_complete();
	// 		json_response(array('success' => TRUE, 'msg' => 'Add Datesheet Success'));

		
	// }

	function selectSubjects(){
	
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$section_id = $this->input->post('section_id');
	  
	    $this->db->where('cls_sec_id', $section_id);
	    $this->db->where('status', 1);
		$subject_info = $this->db->get('section_subjects')->result(); 
				
		$eid = $this->input->post('eid');
		if(empty($eid)){
			echo "<div class='text-danger'>Exam is not selected</div><br>";
			exit;
		} 
		$this->db->where('eid', $eid);
		$examinfo = $this->db->get('exam')->row(); 
		if($examinfo){
		$examStartDate = DateTime::createFromFormat('Y-m-d' ,$examinfo->exam_start_date);
		$subjectexamdate = $examStartDate->format('d/m/Y');

		}else{
		$subjectexamdate = '';	
		}

		$this->db->where('term_id', $examinfo->term_id);
		$this->db->where('session_id', $sessionid);
		$terms_session_info = $this->db->get('terms_session')->row(); 

		$eeid = 0;
	    $this->db->where('cls_sec_id', $section_id);
		$this->db->where('eid', $examinfo->eid);
		$examDatesheet = $this->db->get('datesheet')->row();
		if($examDatesheet){
		 $eeid = $examDatesheet->eid;
		}
	
		$subjectList = '';
		{
		
		$subjectList .= '<input type="hidden" name="eeid"  value="'.$eeid.'">';
		$subjectList .= '<table class="table"><tr><th style="width:5%;">Subject</th><th  style="width:10%;">Total Marks</th><th style="width:17%;">Exam Date</th><th  style="width:50%;">Syllabus</th></tr>';
		$i = 1;

		foreach($subject_info as $subject){
			//print_r($subject);

			$this->db->where('cls_sec_id', $subject->cls_sec_id);
			$this->db->where('status', 1);
			$class_section_info = $this->db->get('class_section')->row(); 

			$this->db->where('sec_sub_id', $subject->sec_sub_id);
			$this->db->where('eid', $examinfo->eid);
			$datesheet_info = $this->db->get('datesheet')->row(); 
			$papersyllabus = '';
			$totalmarks = '';
		
			$i++;
			$did = 0;
			if($datesheet_info){
				$did = $datesheet_info->did;
				$papersyllabus = $datesheet_info->syllabus;
				$totalmarks = $datesheet_info->total_marks;
				
				
				
				$subjectexamdate = DateTime::createFromFormat('Y-m-d' ,$datesheet_info->exam_date);
				$subjectexamdate = $subjectexamdate->format('d/m/Y');

			}else{
				$this->db->where('subject_id', $subject->subject_id);
				$this->db->where('term_session_id', $terms_session_info->term_session_id);
				$this->db->where('class_id', $class_section_info->class_id);
				$this->db->where('campus_id', $campusid);
				$toplevelinfo = $this->db->get('top_level_planning')->row();
				if($toplevelinfo){
					$papersyllabus = $toplevelinfo->objective;
				}
			}

			$this->db->where('sid', $subject->subject_id);
			$subjectinfo = $this->db->get('allsubject')->result_array();
			if(!empty($subjectinfo)){
			
			$subject_name = $subjectinfo[0]['subject_name'];
			$subject_id = $subjectinfo[0]['sid'];
					
			$subjectList .= "<tr><td><input type='hidden' name='did[]'  value='".$did."'><input type='hidden' name='sec_sub_id[]'  value='".$subject->sec_sub_id."'>".$subject_name."</td><td><input type='text' name='total_marks[]' value='".$totalmarks."' class='form-control'></td><td>
				<div class='input-group date' id='datepicker".$subject->sec_sub_id."' data-target-input='nearest'>
                        <input type='text' name='exam_date[]'  value='".$subjectexamdate."'  class='form-control datetimepicker-input' data-target='#datepicker".$subject->sec_sub_id."'/>
                        <div class='input-group-append' data-target='#datepicker".$subject->sec_sub_id."' data-toggle='datetimepicker'>
                            <div class='input-group-text'><i class='fa fa-calendar'></i></div>
                        </div>
                  </td><td><textarea name='syllabus[]' class='form-control editor222'>".$papersyllabus."</textarea></td></tr>
			<script>
				$(function(){
				 $('#datepicker".$subject->sec_sub_id."').datetimepicker({
				      format: 'DD/MM/YYYY',
				    });
				});
				$('.editor222').summernote();
				</script>";
			}
		
		}
	
		$subjectList .= "</table><script>
		$(document).ready(function() {
			// first row checkboxes
			$('tr td:first-child input[type=\"checkbox\"]').click( function() {
			   $(this).closest('tr').find(\":input:not(:first)\").attr('disabled', !this.checked);
			});
		});	
		</script>";
		}
		$this->output->set_output($subjectList);
		
	}

}
// end this file
