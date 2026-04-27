<?php
namespace App\Controllers\Admin;


/**
 * Class Sections Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class A_subject_group extends BaseController { 

	function __construct(){
		parent::__construct();
		check_permission('admin-class-section');
		$this->load->library('session');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('a_subject_group', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campus_id = $this->session->userdata('member_campusid');

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		$this->db->select('count(A.cls_sub_group_id) as ccount', FALSE);
		$this->db->from('a_subject_group A');
		
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

	
		$this->db->select('A.*');
		$this->db->from('a_subject_group A');
		
		$this->db->order_by('A.cls_sub_group_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){

		$this->db->where('class_id', $row->class_id);
		$classesinfo = $this->db->get('classes')->row();		
	
		$this->db->where('class_id', $row->class_id);
		$classesinfo = $this->db->get('classes')->row();


	    $this->db->where('section_id', $row->section_id);
	    $sectionsinfo = $this->db->get('sections')->row();

			$data = array();
			$data['id'] = $row->cls_sec_id;
			$data['class_name'] = $classesinfo->class_name;
			$data['section_name'] = $sectionsinfo->section_name;
			$response->data[] = $data;

		}

		$this->output->set_output(json_encode($response));
	}

	function data2(){
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		$this->db->where('campus_id', $campusid);
	    $groupinfo = $this->db->get('a_groups')->result();
	   	
		$this->db->order_by('class_id', 'asc');
		$this->db->where('status', 1);
		$classSubjectinfo = $this->db->get('a_class_subjects')->result();

	    
		$data = '';
		$data .= '<table class="table"><tr><th></th>';
          		if(isset($groupinfo)){
				foreach ($groupinfo as  $groupvalue) { 
            		$data .= '<th><input type="hidden" name="group_id[]"  value="'.$groupvalue->group_id.'"  />'.$groupvalue->short_name.'<br>All<br><lable> <input class="sectionSub" id="setclock_'.$groupvalue->group_id.'" type="checkbox"></lable></div><script> 
						$(function(){
						$("#setclock_'.$groupvalue->group_id.'").click(function(){
						if(this.checked){
						 $(".setlock_'.$groupvalue->group_id.'").prop("checked", true);
						}else{
							 $(".setlock_'.$groupvalue->group_id.'").prop("checked", false);
						}
						});
						
						});	 
						</script></th>';
            	 } 
            } 
        $data .= '</tr>';
          	if(isset($classSubjectinfo)){
				foreach ($classSubjectinfo as  $classSubjectinfo) { 

				$this->db->where('sid', $classSubjectinfo->subject_id);
				$subjectinfo = $this->db->get('allsubject')->row();		
				
				$this->db->where('class_id', $classSubjectinfo->class_id);
				$classinfo = $this->db->get('classes')->row();
					
				$data .= '<tr><td><input type="hidden" name="cls_sub_id[]"  value="'.$classSubjectinfo->cls_sub_id.'"  />'.$classinfo->class_name.' ('.$subjectinfo->subject_name.')</td>';
              	if(isset($groupinfo)){
					foreach ($groupinfo as  $groupvalue) { 

						$this->db->where('cls_sub_id', $classSubjectinfo->cls_sub_id);
						$this->db->where('campus_id', $campusid);
						$this->db->where('group_id', $groupvalue->group_id);
						$subjectGroup = $this->db->get('a_subject_group')->row();

						if($subjectGroup){
							$data .= '<td><input type="checkbox" checked disabled /></td>';
						}else{
            				$data .= '<td><input type="checkbox" class="setlock_'.$groupvalue->group_id.'"  name="'.$classSubjectinfo->cls_sub_id.'_'.$groupvalue->group_id.'_class_section[]"  value="1"  /></td>';
            		}
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
          $data .= '</table>';

		$this->output->set_output($data);
	}

	function add(){
		check_permission('admin-add-class-section');
		$campusid = $this->session->userdata('member_campusid');
		
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

	   $subjectinfo = $this->db->get('a_subject')->result();
	   $this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('a_subject_group_edit', $this->template_data);
	}

	

	function save(){
		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata['member_campusid'];
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
		$schoolinfo = getSchoolInfo();
		$group_ids = $this->input->post('group_id');	
		$cls_sub_ids = $this->input->post('cls_sub_id');	
				
		check_permission('admin-add-class-section');
		$this->db->trans_begin();
		foreach ($cls_sub_ids as $key => $cls_sub_id) {
			foreach($group_ids as $group_id){
			$ssvalue = $this->input->post($cls_sub_id.'_'.$group_id.'_class_section');
				if(!empty($ssvalue)){		
					$data = array(
						'cls_sub_id' => $cls_sub_id,
						'group_id' =>  $group_id,
						'campus_id' =>  $campus_id,
						'user_id' => $user_id,
						'created_date' => $date
					);
					$this->db->insert('a_subject_group', $data);
				}
			}
		}
		
		$new_user_id = $this->db->insert_id();
		$this->db->trans_complete();
						
		json_response(array('success' => TRUE, 'msg' => 'Add Subject Group Success'));
		

	}

	function delete(){
		check_permission('admin-del-section-subjects');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('cs_id', $id);
		$this->db->delete('a_subject_group');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Section Subjects Success'));
	}

}
// end this file
