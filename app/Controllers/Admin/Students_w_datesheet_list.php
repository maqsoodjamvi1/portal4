<?php
namespace App\Controllers\Admin;


/**
 * Students Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */
 


class Students_w_datesheet_list extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-students-contact-list');
		$this->load->helper(array('form', 'url'));
		$this->load->model('students_model_result_list','students');

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

		$where = "session_id=".$sessionid." AND campus_id=".$campus_id;
		$this->db->where($where);	
		$exams = $this->db->get('exam')->result();
		

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	
		$this->template_data['exams'] = $exams;
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->view('students_w_datesheet_list', $this->template_data);
	}

	function data(){
	
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$list = $this->students->get_datatables();
		
		$exam_id = $_GET['exam_id'];
		
		$data = array();
		$response = array();
		$no = $_POST['start'];
		$nCount = 0;
		foreach ($list as $row) {
			$no++;
			$nCount++;
			$data = array();
		
		if($row){
			$total_discount = 0;
			$payable = 0;
			$projectedfee = 0;
			$classinfo = '';
			$sectioninfo = '';
			$className = '';
			$sectionName = '';

			$this->db->where('student_id', $row->student_id);
			$this->db->where('status', $this->input->get('status'));
			$this->db->where('session_id', $sessionid);
			$studentclassinfo = $this->db->get('student_class')->row();
		
		if($studentclassinfo){
			$sectionInfo = '';
			$this->db->where('cls_sec_id', $studentclassinfo->cls_sec_id);
			$classsectioninfo = $this->db->get('class_section')->row();	
			
			if($classsectioninfo){
				$this->db->where('class_id', $classsectioninfo->class_id);
				$classinfo = $this->db->get('classes')->row();
			}

			if($classsectioninfo){
				$this->db->where('section_id', $classsectioninfo->section_id);
				$sectionInfo = $this->db->get('sections')->row();
			}
			if($sectionInfo){
				$sectionName = $sectionInfo->section_name;
			}

		}
		
		if($classinfo){
			$className = $classinfo->class_short_name;
		}
		
		$this->db->where('parent_id', $row->parent_id);
		$parentinfo = $this->db->get('parents')->row();
		//print_r($parentinfo);
		 $f_name = '';
		 $father_contact = '';
		 $mother_contact = '';
		 $emergency_contact = '';
		 $whatsapp_contact = '';
		 $f_cnic = '';
		 $pkey = '';
		 $pid = '';
		if($parentinfo){
			$address = $parentinfo->address_line1;
			$pid = $parentinfo->parent_id;
			$pkey = $parentinfo->pkey;
			$f_name = $parentinfo->f_name;
			$f_cnic = $parentinfo->father_cnicnew;
			$father_contact = $parentinfo->father_contact;
			$mother_contact = $parentinfo->mother_contact;
			$emergency_contact = $parentinfo->emergency_contact;
			$whatsapp_contact = $parentinfo->whatsapp;
		}
			
			$data['id'] = $row->student_id;
			$data['sr_no'] = $nCount;
			
			$imgurl = FCPATH."uploads/".$row->profile_photo;
			
			if($row->profile_photo){
			if(file_exists($imgurl)){

				$data['profile_photo'] = "<img style='width:50px;height:50px;text-align: center;display: block;border-radius: 30px;margin: 0 auto;' src='".base_url("uploads/".$row->profile_photo)."' >";
						
			}else{

				$data['profile_photo'] = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
			}
			}else{
				$data['profile_photo'] = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
			}
			
			$age = date_diff(date_create($row->date_of_birth), date_create('now'))->y;
			$url = rawurlencode('https://'.$schoolinfo->domain.'.timesoftsol.com/students_datesheet_card/?pid='.$pid.'&session_id='.$sessionid.'&exam_id='.$exam_id);
			$data['reg_no'] = $row->reg_no;
			$data['name'] = $row->first_name." ".$row->last_name;
			$data['f_name'] = $f_name;
			$data['age'] = $age." Years";
			$data['gender'] = $row->gender;
			$data['address'] = $address;
			$data['class'] = $className."-".$sectionName;
			$data['section'] = $sectionName;
			
			$data['w_contacts'] = '<a target="_blank" class="btn btn-success btn-sm" href="https://wa.me/'.$whatsapp_contact.'?text='.$url.'"><i class="fab fa-whatsapp"></i> Send</a>';

			$response[] = $data;
		  }
		}
		$output = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => $this->students->count_all(),
				"recordsFiltered" => $this->students->count_filtered(),
				"data" => $response,
			);
		
		$this->output->set_output(json_encode($output));
	}

	function get_parentinfo(){
		$campusid = $this->session->userdata('member_campusid');
		$term = $this->input->post('term');		
		$parentssinfo = $this->db->query("select * from parents where (f_name like '%".$term['term']."%' )  AND campus_id= ".$campusid)->result_array();
		 // Initialize Array with fetched data

	     $data = array();
	     foreach($parentssinfo as $parent){
	     	$classstudents = $this->db->query("select * from students where parent_id = ".$parent['parent_id'].' AND campus_id= '.$campusid)->row();
	     	if($classstudents){
	     		 $data[] = array("id" => $parent['parent_id'], "text" => $parent['f_name']);
	     	}
	     }

		return json_response($data);	 
	}


function get_studentinfo(){
	//print_r($_POST);
		$campusid = $this->session->userdata('member_campusid');
		$term = $this->input->post('term');		
		$status = $this->input->post('status');		
		//echo "select * from students where (first_name like '%".$term['term']."%' OR last_name like '%".$term['term']."%') AND status=".$status." AND campus_id=".$campusid;
		$studentsinfo = $this->db->query("select * from students where (first_name like '%".$term['term']."%' OR last_name like '%".$term['term']."%') AND status=".$status." AND campus_id=".$campusid)->result_array();
		 // Initialize Array with fetched data 
     $data = array();
     foreach($studentsinfo as $student){
     	$classstudents = $this->db->query("select * from student_class where  student_id = ".$student['student_id'])->row();
     	$parentsInfo = $this->db->query("select f_name from parents where  parent_id = ".$student['parent_id'])->row();

     	
     	$stdInfotxt = $student['first_name']." ".$student['last_name']." c/o ".$parentsInfo->f_name;

     	if($classstudents){
     		 $data[] = array("id"=>$student['student_id'], "text"=>$stdInfotxt);
     	}
     }
	return json_response($data);	 
  }

}
// end this file
