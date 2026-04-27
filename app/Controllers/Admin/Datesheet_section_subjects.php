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



class Datesheet_section_subjects extends MY_Controller {

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
		$this->load->view('datesheet', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();
 
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.sec_sub_id) as ccount', FALSE);
		$this->db->from('section_subjects A');
		$this->db->where('(A.cls_sec_id IN(select cls_sec_id from class_section where status=1 AND campus_id=' . $this->db->escape($campusid) . '))');
				
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('section_subjects A');
		$this->db->where('(A.cls_sec_id IN(select cls_sec_id from class_section where status=1 AND campus_id=' . $this->db->escape($campusid) . '))');
		
		$this->db->order_by('A.sec_sub_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
		
			$classSection = getClassSection($row->cls_sec_id);
			
			$this->db->where('sid', $row->subject_id);
			$subjectsinfo = $this->db->get('allsubject')->row();	
		
			$data = array();
			$data['id'] = $row->sec_sub_id;
			$data['section_name'] = $classSection['sectionclassname'];
			$data['short_name'] = $subjectsinfo->subject_name;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function data2(){
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();
		
		$this->db->where('campus_id', $campusid);
		$this->db->where('status', 1);
		$classsectioninfo = $this->db->get('class_section')->result();
		$sectionsclassinfo = array();
		foreach($classsectioninfo as $section){
		
		$this->db->where('class_id', $section->class_id);
		$classinfo = $this->db->get('classes')->row();

		$this->db->where('section_id', $section->section_id);
		$sectioninfo = $this->db->get('sections')->row();
		
		$sectionsclassinfo[] = array(
		'section_id' => $section->cls_sec_id,
		'sectionclassname' => $classinfo->class_short_name." (".$sectioninfo->short_name.")"
		);
		
		}
		//$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		$this->db->where('system_id', $schoolinfo->system_id);
	  	$subjectinfo = $this->db->get('allsubject')->result();
		$data = '';
		$data .= '<section class="section2">
		<div class="table-box"><table class="table" style="margin-bottom:0px;"><thead><tr class="header"><th></th>';
          		if(isset($subjectinfo)){
				foreach ($subjectinfo as  $subjectvalue) { 
            		$data .= '<th><input type="hidden" name="subjects[]"  value="'.$subjectvalue->sid.'"  />'.$subjectvalue->subject_short_name.'</th>';
            	 } 
            } 
        $data .= '</tr></thead><tbody>';
          	if(isset($sectionsclassinfo)){
				foreach ($sectionsclassinfo as  $sectionvalue) { 
				$data .= '<tr><th style="line-height:1;"><input type="hidden" name="sections[]"  value="'.$sectionvalue["section_id"].'"  />'.$sectionvalue["sectionclassname"].'
				</th>';
              	if(isset($subjectinfo)){ 
					foreach ($subjectinfo as  $subjectvalue) { 

						$this->db->where('subject_id', $subjectvalue->sid);
						$this->db->where('cls_sec_id', $sectionvalue['section_id']);
						$this->db->where('status', 1);
						$sectionsubjects = $this->db->get('section_subjects')->row();

						$data .= '<td  style="text-align:center;vertical-align:middle;padding:3px 8px;line-height:1;">';
						if($sectionsubjects){
							// $data .= '<input type="checkbox" ';
            			// 	if($sectionsubjects->status == 1){
            			// 		$data .= ' checked ';
            			// 	}
            			// 	$data .= ' class="setSecSub setlock_'.$sectionvalue["section_id"].' setlock_'.$subjectvalue->sid.'" name="'.$sectionvalue["section_id"].'_'.$subjectvalue->sid.'_section_subjects[]"  value="'.$sectionvalue["section_id"].'_'.$subjectvalue->sid.'"  />';
            			$data .= '<input type="text" name="total_marks">';
            			$data .= '<input type="date" name="total_marks">';
						}else{
         				//$data .= '<input type="checkbox" class="setSecSub setlock_'.$sectionvalue["section_id"].' setlock_'.$subjectvalue->sid.'"  name="'.$sectionvalue["section_id"].'_'.$subjectvalue->sid.'_section_subjects[]"  value="'.$sectionvalue["section_id"].'_'.$subjectvalue->sid.'"  />';
         			}
         			$data .= '</td>';
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


</style><script type="text/javascript">
		$(function(){
         $(".setSecSub").on("change",function() {
            
            if(this.checked){
            	var status = 1;
            }else{
            	var status = 0;
            }

            var section_subject_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "admin.php?c=section_subjects&m=updateSectionSubject", 
                data: {section_subject_id:section_subject_id,status:status},
                success:function(res){
            		toastr.success(res.msg);
			  	} 
            });

           });  
      }); 
      </script>';

		$this->output->set_output($data);
	}


	function updateSectionSubject(){
		$campusid = $this->session->userdata('member_campusid');
		$status = $this->input->post('status');
		$section_subject_ids = $this->input->post('section_subject_id');
		$SecSubArr = explode('_', $section_subject_ids);

		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');

		$cls_sec_id = $SecSubArr[0];
		$subject_id = $SecSubArr[1];

		
		$this->db->where('cls_sec_id', $cls_sec_id);
		$this->db->where('subject_id', $subject_id);
		$sectionSubject = $this->db->get('section_subjects')->row();

		if($sectionSubject){

			$data = array(
				'user_id' => $user_id,
				'updated_date' => $date,
				'status' => $status
			);

			$this->db->where('cls_sec_id', $cls_sec_id);
			$this->db->where('subject_id', $subject_id);
			$this->db->update('section_subjects', $data);

		}else{
			$data = array(
				'cls_sec_id' => $cls_sec_id,
				'subject_id' =>  $subject_id,
				'user_id' => $user_id,
				'created_date' => $date,
				'status' => 1
			);
			$this->db->insert('section_subjects', $data);
		}
		
		json_response(array('success' => TRUE, 'msg' => 'Add Section Subject Success'));
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

		$this->load->view('datesheet_section_subjects_edit', $this->template_data);
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
		
		$this->load->view('datesheet_section_subjects_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('id'));
		$campus_id = $this->session->userdata['member_campusid'];
		$subjects = $this->input->post('subjects');	
		$sections = $this->input->post('sections');	
		$schoolinfo = getSchoolInfo();
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');
				
		check_permission('admin-add-section-subjects');
		$this->db->trans_begin();
		foreach ($sections as $key => $section_id) {
			foreach($subjects as $subject_id){
			$ssvalue = $this->input->post($section_id.'_'.$subject_id.'_section_subjects');
				if(!empty($ssvalue)){		
					$data = array(
						'subject_id' => $subject_id,
						'cls_sec_id' =>  $section_id,
						'user_id' => $user_id,
						'created_date' => $date
					);
					$this->db->insert('section_subjects', $data);
				}
			}
		}
		
		$new_user_id = $this->db->insert_id();

		$this->db->trans_complete();
		$this->db->where('system_id', $schoolinfo->system_id);
		$fee_type_info = $this->db->get('fee_type')->row();			
		if(empty($fee_type_info->fee_type_id)){
			$this->output->set_output(json_encode(array('fee_type_id' => FALSE, 'msg' => 'Add Section Subjects Success')));
		}else{
			json_response(array('success' => TRUE, 'msg' => 'Add Section Subjects Success'));
		}
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
