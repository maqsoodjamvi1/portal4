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



class A_section_subjects extends BaseController {

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
		$this->load->view('a_section_subjects', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$campusid = $this->session->userdata('member_campusid');
		//$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.cls_sub_id) as ccount', FALSE);
		$this->db->from('a_class_subjects A');
		$this->db->where('(A.campus_id=' . $this->db->escape($campusid) . ' and status=1)');
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('a_class_subjects A');
		$this->db->where('(A.campus_id=' . $this->db->escape($campusid) . ' and status=1)');
		
		$this->db->order_by('A.cls_sub_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
		
			$this->db->where('sid', $row->subject_id);
			$subjectsinfo = $this->db->get('allsubject')->row();

			$this->db->where('class_id', $row->class_id);
			$classinfo = $this->db->get('classes')->row();	
		
			$data = array();
			$data['id'] = $row->cls_sub_id;
			$data['section_name'] = $classinfo->class_name;
			$data['short_name'] = $subjectsinfo->subject_name;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

	function data2(){
		$campusid = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();
		
		$this->db->where('system_id', $schoolinfo->system_id);
		$classinfo = $this->db->get('classes')->result();
	
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
          	if(isset($classinfo)){
				foreach ($classinfo as  $classvalue) { 
				$data .= '<tr><th style="line-height:1;"><input type="hidden" name="class_id[]"  value="'.$classvalue->class_id.'"  />'.$classvalue->class_name.'
				</th>';
              	if(isset($subjectinfo)){ 
					foreach ($subjectinfo as  $subjectvalue) { 

						$this->db->where('subject_id', $subjectvalue->sid);
						$this->db->where('class_id', $classvalue->class_id);
						$classsubjects = $this->db->get('a_class_subjects')->row();

						$data .= '<td  style="text-align:center;vertical-align:middle;padding:3px 8px;line-height:1;">';
						if($classsubjects){
							$data .= '<input type="checkbox" ';
            				if($classsubjects->status == 1){
            					$data .= ' checked ';
            				}
            				$data .= ' class="setSecSub setlock_'.$classvalue->class_id.' setlock_'.$subjectvalue->sid.'" name="'.$classvalue->class_id.'_'.$subjectvalue->sid.'_section_subjects[]"  value="'.$classvalue->class_id.'_'.$subjectvalue->sid.'"  />';
						}else{
            				$data .= '<input type="checkbox" class="setSecSub setlock_'.$classvalue->class_id.' setlock_'.$subjectvalue->sid.'"  name="'.$classvalue->class_id.'_'.$subjectvalue->sid.'_class_subjects[]"  value="'.$classvalue->class_id.'_'.$subjectvalue->sid.'"  />';
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

            var class_subject_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "admin.php?c=a_section_subjects&m=updateSectionSubject", 
                data: {class_subject_id:class_subject_id,status:status},
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
		$class_subject_ids = $this->input->post('class_subject_id');
		$SecSubArr = explode('_', $class_subject_ids);

		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');

		$class_id = $SecSubArr[0];
		$subject_id = $SecSubArr[1];

		$this->db->where('class_id', $class_id);
		$this->db->where('subject_id', $subject_id);
		$classSubject = $this->db->get('a_class_subjects')->row();

		if($classSubject){

			$data = array(
				'user_id' => $user_id,
				'updated_date' => $date,
				'status' => $status
			);

			$this->db->where('class_id', $class_id);
			$this->db->where('subject_id', $subject_id);
			$this->db->update('a_class_subjects', $data);
			

		}else{
			$data = array(
				'class_id' => $class_id,
				'subject_id' =>  $subject_id,
				'campus_id' =>  $campusid,
				'user_id' => $user_id,
				'created_date' => $date,
				'status' => 1
			);
			$this->db->insert('a_class_subjects', $data);
			
		}
		
		json_response(array('success' => TRUE, 'msg' => 'Add Class Subject Success'));
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

		$this->load->view('a_section_subjects_edit', $this->template_data);
	}

}
// end this file
