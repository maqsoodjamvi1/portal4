<?php
namespace App\Controllers\Admin;


/**
 * Teacher Section Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */ 


class A_teacher_group extends BaseController {

	function __construct(){
		parent::__construct();
		check_permission('admin-teacher-sections');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('a_teacher_group', $this->template_data);
	}

	function data(){
		$campusid = $this->session->userdata('member_campusid');
		
		$infoteachers = $this->db->query('select * FROM users WHERE campus_id='.$campusid.' AND status=1 AND id IN (select userID from user_roles WHERE roleID=5)')->result_array();

		$this->template_data['infoteachers'] = $infoteachers;

		$this->db->where('campus_id', $campusid);
		$subjectClassInfo = $this->db->get('a_subject_group')->result();
		
		$data = "<style>
			.tdclass{
		    	padding:3px 8px;text-align:center;
		    }
		    .verticalTableHeader {
			    text-align:center;
			    white-space:nowrap;
			    g-origin:50% 50%;
			    -webkit-transform: rotate(90deg);
			    -moz-transform: rotate(90deg);
			    -ms-transform: rotate(90deg);
			    -o-transform: rotate(90deg);
			    transform: rotate(90deg);
			    
			}
			.verticalTableHeader p {
			    margin:0 -100% ;
			    display:inline-block;
			}
			.verticalTableHeader p:before{
			    content:'';
			    width:0;
			    padding-top:110%;
			    display:inline-block;
			    vertical-align:middle;
			}
			.table-box {
				overflow: scroll;
				height: 500px;	
			}
			table {width: 100%;}
			table th {	padding: 7px;background-color: #ddd;}
			table td {}
			table tr th{position: sticky;left: 0;}
		</style>";
	
		$data .= '<div class="table-box"><table border="1"><tr><th></th>';
		if(isset($infoteachers)){
			foreach ($infoteachers as  $teachersvalue) { 
				$data .= '<th style="height:100px;vertical-align:middle;"><p class="verticalTableHeader">'.$teachersvalue['first_name']." ".$teachersvalue['last_name'].'</p></th>';		
			}
			} 
			$data .= '</tr>';	
			if(isset($subjectClassInfo)){
				foreach ($subjectClassInfo as $key => $subjectClassvalue) {

				$this->db->where('cls_sub_id', $subjectClassvalue->cls_sub_id);
				$classSubjectinfo = $this->db->get('a_class_subjects')->row();


				$this->db->where('sid', $classSubjectinfo->subject_id);
				$subjectinfo = $this->db->get('allsubject')->row();
							
				
				$this->db->where('class_id', $classSubjectinfo->class_id);
				$classinfo = $this->db->get('classes')->row();
				

				$this->db->where('group_id', $subjectClassvalue->group_id);
				$groupinfo = $this->db->get('a_groups')->row();	

				
				$this->db->where('cls_sub_group_id', $subjectClassvalue->cls_sub_group_id);
				$this->db->where('status', 1);
				$info = $this->db->get('a_group_teacher')->row();
 
				$teachersecionArr = 0;
				$tg_id = 0;
				if($info){
					$teachersecionArr = $info->tid;
					$tg_id = $info->gt_id;
					$cls_sub_group_id = $info->cls_sub_group_id;
				}

				$data .= '<tr><th  class="tdclass"><input type="hidden" name="tg_id[]" value="'.$tg_id.'">'.$classinfo->class_name." (".$subjectinfo->subject_name." ".$groupinfo->group_name.')<input type="hidden" name="cls_sub_group_id[]" value="'.$subjectClassvalue->cls_sub_group_id.'" /></th>';
				foreach($infoteachers as $key => $teacher){ 
					//echo ;
				$data .= '<td   class="tdclass"><input type="radio" ';
				if($teachersecionArr == $teacher['id'] && $cls_sub_group_id == $subjectClassvalue->cls_sub_group_id){
				 $data .= 'checked=checked'; 
				  }  
				   $data .= ' value="tgvalue_'.$subjectClassvalue->cls_sub_group_id.'_'.$teacher['id'].'" name="'.$subjectClassvalue->cls_sub_group_id.'_tg_id"></td>';
					 } 
				$data .= '</tr>';	
				 } 
			 } 				
			$data .= '</table></div>';
		$this->output->set_output($data);
	}

	function add(){
		check_permission('admin-add-teacher-section');
		$campusid = $this->session->userdata('member_campusid');

		$info = $this->db->get('teacher_section')->result_array();
		$this->template_data['info'] = $info;

	
		$infoteachers = $this->db->query('select * FROM users WHERE campus_id='.$campusid.' AND id IN (select userID from user_roles WHERE roleID=5)')->result_array();
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

		$this->load->view('a_teacher_group_edit', $this->template_data);
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
		$this->load->view('a_teacher_group_edit', $this->template_data);
	}



	function save(){
		$ids = $this->input->post('tg_id');
		$campus_id = intval($this->session->userdata['member_campusid']);
		$cls_sub_group_ids = $this->input->post('cls_sub_group_id');
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d');

		check_permission('admin-add-teacher-section');
		$this->db->trans_begin();
	
		$i=0;	
		
		$this->db->query('update a_group_teacher set status= 0 WHERE tid IN(select id FROM users WHERE campus_id='.$campus_id.')');
		
		foreach($cls_sub_group_ids as $cls_sub_group_id){

		$id = $ids[$i];
		
		$tsvalue = $this->input->post($cls_sub_group_id.'_tg_id');
		if(!empty($tsvalue)){
			$valueArr = explode('_',$tsvalue);
		
			$sec_id = $valueArr[1];
			$teacher_id = $valueArr[2];
		
					$data = array(
						'tid' => $teacher_id,
						'cls_sub_group_id' => $sec_id,
						'status' => 1,
						'created_date' => $date,
						'user_id' => $user_id
					);

					
			$this->db->insert('a_group_teacher', $data);
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
	
}
// end this file
