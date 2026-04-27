<?php
namespace App\Controllers\Admin;


/**
 * Fee Type Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class A_teacher_subjects extends BaseController {

	function __construct(){
		parent::__construct();
		check_permission('a-admin-teacher-subjects');
	}
	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('campus_id', $campusid);
		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('a_teacher_subjects', $this->template_data);
	}

	function data(){
		$campusid = $this->session->userdata('member_campusid');
		$subject_id = $this->input->post('subject_id');

		$infoteachers = $this->db->query('select * FROM users WHERE campus_id='.$campusid.' AND status=1 AND id IN (select userID from user_roles WHERE roleID=5)')->result_array();

		$this->template_data['infoteachers'] = $infoteachers;

		$this->db->where('campus_id', $campusid);
		$classSubjectinfo = $this->db->get('a_subject_group')->result();

		$subjectinfo = $this->db->get('allsubject')->result();

$data = '';
$data .= "<style>
			
.tdclass{
	padding:3px 8px;
	text-align:center;
}
.verticalTableHeader {
    margin: 0;
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
    margin:0 -100%;
    display:inline-block;
}
.verticalTableHeader p:before{
    content:'';
    width:0;
    padding-top:110%;/* takes width as reference, + 10% for faking some extra padding */
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
</style><div class='table-box'><table class='' border='1'><thead><tr><th></th>";
	if(isset($infoteachers)){
		foreach ($infoteachers as  $teachersvalue) { 
			$data .= "<th style='height:100px;vertical-align:middle;'><p class='verticalTableHeader'>".$teachersvalue['first_name']." ".$teachersvalue['last_name']."</p></th>";
	}
} 
$data .= "</tr></thead><tbody>";	

	if(isset($classSubjectinfo)){
		foreach ($classSubjectinfo as  $subjectClassvalue) { 
			
		$this->db->where('cls_sub_id', $subjectClassvalue->cls_sub_id);
		$clssubjectinfo = $this->db->get('a_class_subjects')->row();
			
		$this->db->where('sid', $clssubjectinfo->subject_id);
		$subjectinfo = $this->db->get('allsubject')->row();		
		
		$this->db->where('class_id', $clssubjectinfo->class_id);
		$classinfo = $this->db->get('classes')->row();
		
		$this->db->where('group_id', $subjectClassvalue->group_id);
		$groupinfo = $this->db->get('a_groups')->row();	

		$cls_sub_group = $classinfo->class_short_name.' '.$subjectinfo->subject_short_name.' '.$groupinfo->short_name;
		

		$this->db->where('cls_sub_group_id', $subjectClassvalue->cls_sub_group_id);
		$info = $this->db->get('a_group_teacher')->row();

		$teachersecionArr = 0;
		$tg_id = 0;
		if($info){
			$teachersecionArr = $info->tid;
			$tg_id = $info->gt_id;
			$cls_sub_group_id = $info->cls_sub_group_id;
		}
					
	if($tg_id){
					
		$this->db->where('cls_sub_group_id',$cls_sub_group_id);
		//$this->db->where('status',1);
		$currentteachersubject = $this->db->get('a_teacher_subjects')->row();
						
		$data .= "<tr><th class='tdclass'><input type='hidden' name='sst[]' value='".$tg_id."' >".$cls_sub_group."<input type='hidden' name='section_id[]' value='".$subjectClassvalue->cls_sub_group_id."' /></th>";
	foreach($infoteachers as $teacher){
				
		$data .= "<td class='tdclass'><input style='position:relative;z-index:1000;' type='radio' "; 
		if($cls_sub_group_id == $subjectClassvalue->cls_sub_group_id){
				$data .= " checked='checked'";
			}
		$data .= " value='tsvalue_".$subjectClassvalue->cls_sub_group_id."_".$teacher['id']."' name='".$subjectClassvalue->cls_sub_group_id."_ts_id'></td>";
			
		} 
	$data .= "</tr>";
		
	}
	} 
		
	} 				
	$data .= "</tbody></table></div>";

	$this->output->set_output($data);
}

function add(){
		check_permission('admin-add-teacher-subject');
		$campusid = $this->session->userdata('member_campusid');
		
		$this->db->where('campus_id', $campusid);
		$subjectinfo = $this->db->get('a_subject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;

		$this->load->view('a_teacher_subjects_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-teacher-subject');
		$id = intval($this->input->get('id'));
		$campusid = $this->session->userdata('member_campusid');

		$this->db->where('sst', $id);
		$this->db->where('campus_id', $campusid);
		$info = $this->db->get('teacher_subjects')->row();
		$this->template_data['info'] = $info;
		
		$this->db->where('campus_id', $campusid);
		$infoteachers = $this->db->get('teachers')->result();
		$this->template_data['infoteachers'] = $infoteachers;

		$classinfo = $this->db->get('classes')->result();
		$this->template_data['classinfo'] = $classinfo;		

		$subjectinfo = $this->db->get('allsubject')->result();
		$this->template_data['subjectinfo'] = $subjectinfo;
		
		$this->load->view('teacher_subjects_edit', $this->template_data);
	}



	function save(){
		
		$ids = $this->input->post('sst');
		$campus_id = intval($this->session->userdata['member_campusid']);
		$user_id = $this->session->userdata['member_userid'];
		$section_ids = $this->input->post('section_id');
		$subject_id = $this->input->post('sub_id'); 
		$date = date('Y-m-d');

				
		check_permission('admin-add-teacher-subject');
			
		$this->db->trans_begin();
			
		foreach($section_ids as $sectionid){
		
			$this->db->where('subject_id',$subject_id);
			$this->db->where('cls_sec_id',$sectionid);
			$this->db->where('status',1);
			$currentsectionsubject = $this->db->get('section_subjects')->row();
			
				
			if($currentsectionsubject){
				
				$tsvalue = $this->input->post($sectionid.'_ts_id');
				if(!empty($tsvalue)){
				$valueArr = explode('_',$tsvalue);
				
				$sec_id = $valueArr[1];
				$teacher_id = $valueArr[2];

				$existingTeacherRec = $this->db->query('select * from teacher_subjects WHERE status=1 AND sec_sub_id='.$currentsectionsubject->sec_sub_id)->result();
				//print_r($existingTeacherRec);
				if($existingTeacherRec){
				$this->db->query('update teacher_subjects set status= 0 WHERE status=1 AND sec_sub_id='.$currentsectionsubject->sec_sub_id);
				}

				$data = array(
					'tid' => $teacher_id,
					'sec_sub_id' => $currentsectionsubject->sec_sub_id,
					'cls_sec_id' => $sec_id,
					'status' => 1,
					'created_date' => $date,
					'user_id' => $user_id
				);
				$this->db->insert('teacher_subjects', $data);
				$new_user_id = $this->db->insert_id();
				
				}
			}
		}
		
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Add Teacher Subject Success'));
		
		
	}

	function delete(){
		check_permission('admin-del-user');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();
		// delete user
		$this->db->where('sst', $id);
		$this->db->delete('teacher_subjects');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Classes Success'));
	}



}
// end this file
