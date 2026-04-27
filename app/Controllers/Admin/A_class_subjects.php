<?php
namespace App\Controllers\Admin;


/**
 * Class Subjects Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class A_class_subjects extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-section-subjects');
		$this->load->library('session');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('a_class_subjects', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.cls_sub_id) as ccount', FALSE);
		$this->db->from('a_class_subjects A');
		$this->db->where('(A.class_id IN(select class_id from classes where system_id=' . $this->db->escape($schoolinfo->system_id) . '))');
		
		
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('a_class_subjects A');
		$this->db->where('(A.class_id IN(select class_id from classes where system_id=' . $this->db->escape($schoolinfo->system_id) . '))');
		
		
		$this->db->order_by('A.cls_sub_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
			//print_r($row);

			$this->db->where('class_id', $row->class_id);
			$classinfo = $this->db->get('a_classes')->row();
			
			$this->db->where('sid', $row->subject_id);
			$subjectsinfo = $this->db->get('a_subject')->row();	
		
			$data = array();
			$data['id'] = $row->cls_sub_id;
			$data['section_name'] = $classinfo->class_name;
			$data['short_name'] = $subjectsinfo->subject_name;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function data2(){
		$campus_id = $this->session->userdata('member_campusid');
		//$schoolinfo = getSchoolInfo();
		
		$this->db->where('campus_id', $campus_id);
		$classsinfo = $this->db->get('a_classes')->result();
		
		$this->db->where('campus_id', $campus_id);
	  	$subjectinfo = $this->db->get('a_subject')->result();
	  	
		$data = '';
		$data .= '<section class="section2">
		<div class="table-box"><table class="table" style="margin-bottom:0px;"><thead><tr class="header"><th></th>';
          		if(isset($subjectinfo)){
				foreach ($subjectinfo as  $subjectvalue) { 
            		$data .= '<th><input type="hidden" name="subjects[]"  value="'.$subjectvalue->sid.'"  />'.$subjectvalue->subject_short_name.'<br>All<br><lable> <input class="sectionSub" id="setclock_'.$subjectvalue->sid.'" type="checkbox"></lable></div><script> 
						$(function(){
						$("#setclock_'.$subjectvalue->sid.'").click(function(){
						if(this.checked){
						 $(".setlock_'.$subjectvalue->sid.'").prop("checked", true);
						}else{
							 $(".setlock_'.$subjectvalue->sid.'").prop("checked", false);
						}
						});
						
						});	 
						</script></th>';
            	 } 
            } 
        $data .= '</tr></thead><tbody>';
          	if(isset($classsinfo)){
				foreach ($classsinfo as  $sectionvalue) { 
				$data .= '<tr><th style="line-height:1;"><input type="hidden" name="a_classes[]"  value="'.$sectionvalue->class_id.'"  />'.$sectionvalue->class_name.'
				</th>';
              	if(isset($subjectinfo)){ 
					foreach ($subjectinfo as  $subjectvalue) { 

						$this->db->where('subject_id', $subjectvalue->sid);
						$sectionsubjects = $this->db->get('a_class_subjects')->row();
						
						if($sectionsubjects){
							$data .= '<td style="text-align:center;vertical-align:middle;padding:3px 8px;line-height:1;"><input  style="height:15px;width:15px;" type="checkbox" checked disabled /></td>';
						}else{
            				$data .= '<td  style="text-align:center;vertical-align:middle;padding:3px 8px;line-height:1;"><input type="checkbox" class="setlock_'.$sectionvalue->class_id.' setlock_'.$subjectvalue->sid.'" style="height:15px;width:15px;"
            				 name="'.$sectionvalue->class_id.'_'.$subjectvalue->sid.'_section_subjects[]"  value="1"  /></td>';
            		}
            	 	} 
             	} 
              	$data .= '</tr>';
              	} 
              } 
          	
          $data .= '</tbody></table></div></section><style>
          section{overflow:hidden;}
          .table-box {
			overflow: scroll;
			height: 500px;	
		}
		table {width: 100%;}

		table th {	padding: 7px;background-color: #ddd;}
		table td {}

		table tr th{position: sticky;left: 0;}

		</style>';

		$this->output->set_output($data);
	}

	function add(){
		check_permission('admin-add-section-subjects');
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
		'sectionclassname' => $classinfo->class_name." (".$sectioninfo->short_name.")"
		);
		
		}
		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		

	    $subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('a_class_subjects_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-section-subjects');
		$id = intval($this->input->get('id'));
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('cs_id', $id);
		$info = $this->db->get('section_subjects')->row();
		$this->template_data['info'] = $info;	
		
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
		
		$this->load->view('a_class_subjects_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata['member_campusid'];
		
		$subjects = $this->input->post('subjects');	
		$classes = $this->input->post('a_classes');	
		$schoolinfo = getSchoolInfo();
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
				
		check_permission('admin-add-section-subjects');
		$this->db->trans_begin();
		foreach ($classes as $key => $class_id) {

			foreach($subjects as $subject_id){
			
			$ssvalue = $this->input->post($class_id.'_'.$subject_id.'_section_subjects');
				if(!empty($ssvalue)){		
					$data = array(
						'subject_id' => $subject_id,
						'class_id' =>  $class_id,
						'user_id' => $user_id,
						'created_date' => $date
					);
					$this->db->insert('a_class_subjects', $data);
				}
			}
		}
		
		$new_user_id = $this->db->insert_id();

		$this->db->trans_complete();
		
		json_response(array('success' => TRUE, 'msg' => 'Add Section Subjects Success'));
		
	}

	function delete(){
		check_permission('admin-del-section-subjects');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('cs_id', $id);
		$this->db->delete('section_subjects');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Section Subjects Success'));
	}

}
// end this file
