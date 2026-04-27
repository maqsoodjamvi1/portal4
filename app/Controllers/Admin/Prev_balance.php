<?php
namespace App\Controllers\Admin;


/**
 * Prev Balance Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */ 


class Prev_balance extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-prev-balance');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('prev_balance_edit', $this->template_data);
	}

	function data(){
		$campusid = $this->session->userdata('member_campusid');
		
		$infostudents = $this->db->query('select * FROM students WHERE campus_id='.$campusid.' AND status=1')->result_array();
		$this->template_data['infostudents'] = $infostudents;

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
		$data = "";
	
$data .= '<table class="table" border="1"><tr><th>Student Name</th><th>Previous Balance</th></tr>';

		if(isset($infostudents)){
			foreach ($infostudents as  $studentsvalue) { 
				$data .= '<tr><td><input type="hidden" value="'.$studentsvalue['student_id'].'" name="student_id">'.$studentsvalue['first_name']." ".$studentsvalue['last_name'].'</td><td><input  class="form-control" type="text" name="prev_balance"></td>';
				$data .= '</tr>';		
			}
			} 
			
		$data .= '</table>';
		$this->output->set_output($data);
	}

	function add(){
		check_permission('admin-add-teacher-section');
		$campusid = $this->session->userdata('member_campusid');

		$info = $this->db->get('teacher_section')->result_array();
		$this->template_data['info'] = $info;

	
		$infoteachers = $this->db->query('select * FROM students WHERE campus_id='.$campusid.' ')->result_array();
		$this->template_data['infoteachers'] = $infoteachers;

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

		$this->load->view('prev_balance_edit', $this->template_data);
	}


	function edit(){
		check_permission('admin-edit-teacher-section');
		$id = intval($this->input->get('id'));
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('ts_id', $id);
		$info = $this->db->get('teacher_section')->row();
		$this->template_data['info'] = $info;
		//print_r($info);
		$this->db->where('campus_id', $campusid);
		$this->db->where('emp_type_id', 1);
		$infoteachers = $this->db->get('employees')->result();
		$this->template_data['infoteachers'] = $infoteachers;

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
		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;
		$this->load->view('teacher_section_edit', $this->template_data);
	}



	function save(){
		$ids = $this->input->post('ts_id');
		$campus_id = intval($this->session->userdata['member_campusid']);
		$section_ids = $this->input->post('section_id');
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');

		check_permission('admin-add-teacher-section');
		$this->db->trans_begin();
	
		$i=0;	
		$this->db->query('update teacher_section set status= 0 WHERE tid IN(select id FROM users WHERE campus_id='.$campus_id.')');
		foreach($section_ids as $sectionid){

		$id = $ids[$i];
		
		$tsvalue = $this->input->post($sectionid.'_ts_id');
		if(!empty($tsvalue)){
			$valueArr = explode('_',$tsvalue);
		
			$sec_id = $valueArr[1];
			$teacher_id = $valueArr[2];
		
					$data = array(
						'tid' => $teacher_id,
						'cls_sec_id' => $sec_id,
						'status' => 1,
						'created_date' => $date,
						'user_id' => $user_id
					);

					
			$this->db->insert('teacher_section', $data);
			$new_user_id = $this->db->insert_id();

		}
		$i++;
		}
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Add Teacher Section Success')); 
	}

	function selectteachersection(){
		$section_id = $this->input->post('section_id');
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('campus_id', $campusid);
		$this->db->where('emp_type_id', 1);
		$infoteachers = $this->db->get('employees')->result();

		$teacherslist = '';
		foreach ($infoteachers as $key => $value) {

		$this->db->where('sec_id', $section_id);
		$info = $this->db->get('teacher_section')->row();
		
		$teacherslist .= '<label style="font-weight:bold !important;" class="form-control"><input style="margin-top: -3px;margin-right: 8px;display: table-cell;vertical-align: middle;"  type="radio" name="tid"';
		if($info){
		 if($value->tid == $info->tid) { 
		 	$teacherslist .= 'checked="checked"'; 
		 	} 
		  }
		  $teacherslist .= 'value="'.$value->tid.'">'.$value->first_name." ".$value->last_name.'</label>';
			
		}
		echo $teacherslist;	
		//return $teacherslist;
	}
	function delete(){
		check_permission('admin-del-teacher-section');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('ts_id', $id);
		$this->db->delete('teacher_section');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Techer Subject Success'));
	}
}
// end this file
