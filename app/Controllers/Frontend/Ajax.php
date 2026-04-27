<?php
namespace App\Controllers\Frontend;


/**
 * Ajax Request
 *
 * @author		Maqsood Jamvi
 * @copyright	Copyright (c) 2022 The Prep School
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends BaseController{

	function __construct(){
		parent::__construct();
	}

	function index(){
		// sleep(1);
		// $this->output->set_output('true');
	}

	function getAgeCriteria(){
		$class_id = 0;
		$max_date = '';
		$min_date = '';

		if($this->input->post('class_id')){
		  $class_id = $this->input->post('class_id');
		}

		$adm_criteria_info = $this->db->query('select * from admission_age_criteria where class_id='.$class_id)->row();

		if($adm_criteria_info){
			$age_till_date = '2023-02-28';
		    $max_date=date('Y-m-d', strtotime(-$adm_criteria_info->min_age.' month', strtotime($age_till_date)) );
		    $min_date=date('Y-m-d', strtotime(-$adm_criteria_info->max_age.' month', strtotime($age_till_date)) );
	    }

		$strDate = '<div class="form-group">
            <div class="input-group date" id="datetimepicker3">
               <input name="dob" type="text" class="form-control" required />
               <span class="input-group-addon">
               <span class="glyphicon glyphicon-calendar"></span>
               </span>
            </div>
         </div>
        <script type="text/javascript">
         $(function () {
             $("#datetimepicker3").datetimepicker({
               viewMode: "years",
               format: "YYYY-MM-DD",
               maxDate: new Date("'.$max_date.'"),
               minDate: new Date("'.$min_date.'")
             });
         });
      </script>';
      echo $strDate;

	}

	function getGenderCriteria(){
		$class_id = 0;

		if($this->input->post('class_id')){
		  $class_id = $this->input->post('class_id');
		}

		$strGender = '';
		if($class_id == 4){
			$strGender = '<label><input type="radio" checked name="gender" required value="b"> Boy</label>';
		}else if($class_id == 5){
			$strGender = '<label><input type="radio" checked name="gender" required value="g"> Girl</label>';
		}else{
			$strGender = '<label><input type="radio" name="gender" required value="b"> Boy</label>&nbsp;&nbsp;<label><input type="radio" name="gender" required value="g"> Girl</label>';
		}

		echo $strGender;
	}

	function get_campus_classes(){
		$campus_id = 0;

		if($this->input->post('campus')){
		  $campus_id = $this->input->post('campus');
		}

		$adm_classes_info = $this->db->query("SELECT * from admission_phases where ph_id IN(select ph_id from phases where status=1) AND status=1 and campus_id=".$campus_id)->result();

		// $adm_classes_info = $this->db->query('select * from admission_class where admission_status=1 and campus_id='.$campus_id)->result();
		$nCount = 0;
		$adm_classes = '<option value="">Select Class</option>';
		foreach ($adm_classes_info as $key => $adm_classes_value) {
			$data = $this->db->query("SELECT * from admission_slot_panels where campus_id=".$campus_id." AND capacity > 0 and  class_id=".$adm_classes_value->class_id)->row();
			if($data){
				$classes_info = $this->db->query('select * from classes where class_id='.$adm_classes_value->class_id)->row();
				$adm_classes .= '<option value="'.$classes_info->class_id.'">'.$classes_info->class_name.'</option>';
			$nCount++;	
			}
		}
		// if($nCount == 0){
		// 	echo "No seat available";
		// }else

		// {
			echo $adm_classes;	
		//}
		
		exit;
	}

	function check_parent_value(){
		//$campus_id = $this->session->userdata('member_campusid');
		$field = $this->input->get('field');
		$table = $this->input->get('table');
		if($table && $field){
			$field_value = $this->input->get('cnic');
			$this->db->where($field, $field_value);
			$info = $this->db->get($table)->row();

			if($info){
				$this->output->set_output('false');
			}else{
				$this->output->set_output('true');
			}
		}else{
			$this->output->set_output('true');
		}
	}

	function check_candiate_cnic(){
		//$campus_id = $this->session->userdata('member_campusid');
		$field = $this->input->get('field');
		$table = $this->input->get('table');
		if($table && $field){
			$field_value = $this->input->get('cnic');
			$this->db->where($field, $field_value);
			$info = $this->db->get($table)->row();

			if($info){
				$this->output->set_output('false');
			}else{
				$this->output->set_output('true');
			}
		}else{
			$this->output->set_output('true');
		}
	}

	function check_parent_email(){
		//$campus_id = $this->session->userdata('member_campusid');
		$field = $this->input->get('field');
		$table = $this->input->get('table');
		if($table && $field){
			$field_value = $this->input->get('email');
			$this->db->where($field, $field_value);
			$info = $this->db->get($table)->row();
			
			if($info){
				$this->output->set_output('false');
			}else{
				$this->output->set_output('true');
			}
		}else{
			$this->output->set_output('true');
		}
	}

	function selectSession(){
	
		$session_id = $this->input->post('session_id');
	    
		$this->db->where('session_id', $session_id);
		$academic_session_info = $this->db->get('academic_session')->row();

		$sess_data = [
				 'session_id' => $academic_session_info->session_id,
		];
	
		$this->session->set_userdata($sess_data);
		return true;
		
	}
	function changeStudent(){
		$id = $this->input->post('id');
		
		$querystd = $this->db->get_where('students', array('student_id'=>$id));	
		if($querystd->num_rows() > 0)
            $studentData = $querystd->row_array();
        else
            $studentData = array();

		
		$query = $this->db->get_where('student_class', array('student_id'=>$id));	
		if($query->num_rows() > 0)
            $classData = $query->row_array();
        else
            $classData = array();
			
		$classData = $classData['class_id'];	
		
		$studentName = $studentData['first_name']." ".$studentData['last_name'];	
		
		$sec_id = $classData['section_id'];		
		
		$query2 = $this->db->get_where('sections', array('sec_id'=>$sec_id));
		
		if($query2->num_rows() > 0)
            $sectionData = $query2->row_array();
        else
            $sectionData = array();
			
		$campus_id = $sectionData['campus_id'];	
				
	    $sess_data = [
				 'id'		=> $id,
				 'student_name' => $studentName,
				 'class_id'		=> $class_id,
				 'campus_id'		=> $campus_id,
				 ];
		$this->session->set_userdata($sess_data);
		return true;
	}
	
	function selectExam(){
	
		$campus_id = $this->session->userdata['campus_id'];	
	    $session_id = $this->input->post('session_id');
	    
		$this->db->where('session_id', $session_id);
		$this->db->where('campus_id', $campus_id);
		$exam_info = $this->db->get('exam')->result();
		$exam = '';
		foreach($exam_info as $row){
		 $exam .= "<option value='".$row->eid."'>".$row->exam_name."</option>";
		 }
		$this->output->set_output($exam);
		
	}

}
// end file
