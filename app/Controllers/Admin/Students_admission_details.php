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
 


class Students extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-students');
		$this->load->helper(array('form', 'url'));
		$this->load->model('students_model','students');

	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{	
		$currentrole = currentUserRoles();

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	
		
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->view('students', $this->template_data);
	}

	function data(){
	
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
	
		$list = $this->students->get_datatables();
		$data = array();
		$response = array();
		$no = $_POST['start'];
		foreach ($list as $row) {
			$no++;
			$data = array();
		
		if($row){
			$total_discount = 0;
			$payable = 0;
			$projectedfee = 0;
			$classinfo = '';
			$sectioninfo = '';
			$className = '';
			$sectionName = '';

			$unpaid = $this->db->query('SELECT SUM(amount)-SUM(discount) as total FROM `fee_chalan` WHERE status = "UnPaid" and student_id ='.$row->student_id)->row();
			$discount = $this->db->query('SELECT SUM(discount) as total_discount FROM `fee_chalan` WHERE status = "UnPaid" and student_id ='.$row->student_id)->row();
	
			if($discount){
				$total_discount = $discount->total_discount;
			}
		
			if($unpaid){
				$payable = $unpaid->total;
			}
		
			$this->db->where('student_id', $row->student_id);
			$this->db->where('status', $this->input->get('status'));
			$this->db->where('session_id', $sessionid);
			$studentclassinfo = $this->db->get('student_class')->row();
		
		if($studentclassinfo){

			$this->db->where('cls_sec_id', $studentclassinfo->cls_sec_id);
			$classsectioninfo = $this->db->get('class_section')->row();	
			
			$this->db->where('class_id', $classsectioninfo->class_id);
			$classinfo = $this->db->get('classes')->row();

			
			$this->db->where('section_id', $classsectioninfo->section_id);
			$sectionInfo = $this->db->get('sections')->row();
			if($sectionInfo){
				$sectionName = $sectionInfo->section_name;
			}

			$getclassfee = $this->db->query('SELECT * FROM `fee_amount` WHERE class_id='.$classsectioninfo->class_id.' and fee_type_id IN(select fee_type_id from fee_type where is_monthly_fee=1) and session_id='.$sessionid.' and campus_id='.$campusid)->row();
			
			if($getclassfee){	
		   		$projectedfee = ($getclassfee->amount - $row->discounted_amount);
			}

		}
		
		if($classinfo){
			$className = $classinfo->class_name;
		}
		
		$this->db->where('parent_id', $row->parent_id);
		$parentinfo = $this->db->get('parents')->row();
		//print_r($parentinfo);
		 $f_name = '';
		 $father_contact = '';
		 $mother_contact = '';
		 $emergency_contact = '';
		if($parentinfo){
			$address = $parentinfo->address_line1;
			$f_name = $parentinfo->f_name;
			$father_contact = $parentinfo->father_contact;
			$mother_contact = $parentinfo->mother_contact;
			$emergency_contact = $parentinfo->emergency_contact;
		}
			
			$data['id'] = $row->student_id;
			
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
			
			//print_r($row->date_of_birth);
			$age = date_diff(date_create($row->date_of_birth), date_create('now'))->y;
			
			$data['reg_no'] = $row->reg_no;
			$data['name'] = $row->first_name." ".$row->last_name;
			$data['f_name'] = $f_name;
			$data['age'] = $age." Years";
			$data['gender'] = $row->gender;
			$data['address'] = $address;
			$data['class'] = $className."(".$sectionName.")";
			$data['section'] = $sectionName;
			$data['contacts'] = "F:".$father_contact."<br>M:".$mother_contact."<br>E:".$emergency_contact; 
			$data['payable'] = $payable;
			$data['discounted_amount'] = $row->discounted_amount;
			$data['discounted'] = $total_discount;
			$data['projectedfee'] = $projectedfee;
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

	function add(){
		check_permission('admin-add-student');
		$schoolinfo = getSchoolInfo();
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');

		$campus_bill_info = $this->db->query('select * from campus_bills WHERE status=1 AND campus_id='.$campusid)->row();
		$max_student_id = $campus_bill_info->max_students;

		$max_no_of_students_info = $this->db->query('select no_of_students from number_of_students where id='.$max_student_id)->row();
		//print_r($max_no_of_students_info->no_of_students);
		$max_student_limit = $max_no_of_students_info->no_of_students;
		//exit;
		
		$students_info = $this->db->query('select count(student_id) as studentTotal from students WHERE student_id IN(SELECT student_id from student_class WHERE status=1)  AND campus_id='.$campusid)->row();		
		$noOfstudent = $students_info->studentTotal;

		if($noOfstudent >= $max_student_limit){
			$this->template_data['max_limit'] = '<div class="col-lg-12">Maximum Limit Exceeded</div>';	
		}else{
			$this->template_data['max_limit'] = '';	
		}


		$sessionData = array(
			'campusid' => $campusid,
			'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;
		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;
		
		
		$this->db->where('session_id', $sessionid);
		$academic_session = $this->db->get('academic_session')->row();
		
		$sessionName = explode('-' , $academic_session->session_name);
		 
		$sessionYear = ($sessionName[1]-1);
		
		$this->db->where('session_id', $sessionid);
		$this->db->order_by('student_id', 'desc');
		$last_row = $this->db->get('students')->result();
		$last_id = count($last_row)+1;
		
		
		$reg_no =  $sessionYear.'-'.$schoolinfo->short_name.'-'.$last_id;
		$this->template_data['reg_no'] = $reg_no;

	
		$currentrole = currentUserRoles();

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;			
		
		$this->load->view('students_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-student');
		$id = intval($this->input->get('id'));

		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$sessionData = array(
		'campusid' => $campusid,
		'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;
		$this->db->where('student_id', $id);
		$info = $this->db->get('students')->row();
		$this->template_data['info'] = $info;
		
		$this->db->where('parent_id', $info->parent_id);
		$parentsinfo = $this->db->get('parents')->row();
		$this->template_data['parentsinfo'] = $parentsinfo;
		
		
		// $currentrole = currentUserRoles();

		// if(in_array(5, $currentrole)){
		// 	$sectionsclassinfo = teacherSubjectSections();
		// }else{
			$sectionsclassinfo = userClassSections();
		// }

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;		
		
		$this->db->where('student_id', $id);
		$this->db->where('status', 1);
		$studentclassinfo = $this->db->get('student_class')->row();
		$this->template_data['studentclassinfo'] = $studentclassinfo;
		
		
		$classesinfo = $this->db->get('classes')->result();
		$this->template_data['classesinfo'] = $classesinfo;
		
		$academic_sessioninfo = $this->db->get('academic_session')->result();
		$this->template_data['academic_sessioninfo'] = $academic_sessioninfo;

		$this->load->view('students_edit', $this->template_data);
	}

	function save(){
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		$this->db->where('cls_sec_id', $this->input->post('section_id'));
		$clsSectionInfo = $this->db->get('class_section')->row();
		$class_id = $clsSectionInfo->class_id;
				
		$id = intval($this->input->post('id'));
		$parent_id = intval($this->input->post('parent_id'));
		$now = date('Y-m-d H:i:s');
		
		$date_of_birth = DateTime::createFromFormat('d/m/Y',$this->input->post('date_of_birth'));
	    $date_of_birth = $date_of_birth->format('Y-m-d');

	    $date_of_admission = DateTime::createFromFormat('d/m/Y',$this->input->post('date_of_admission'));
	    $date_of_admission = $date_of_admission->format('Y-m-d');
				
	
		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			if($id === 0){
				check_permission('admin-add-student');

				 header('Content-Type: application/json');
				  $config['upload_path']   = './uploads/';
				  $config['allowed_types'] = 'gif|jpg|png';
				  $config['max_size']      = 1024;
				  $this->load->library('upload', $config);
				  $this->upload->do_upload('image');
				  $data = $this->upload->data();
				  $imagename = $data['file_name'];

				$this->db->trans_begin();
				
				if($parent_id < 1){
				
				$data2 = array(
					'religion' => trim($this->input->post('religion')),
					'father_cnicnew' => trim($this->input->post('father_cnic')),
					'f_name' => trim($this->input->post('f_name')),
					'father_contact' => trim($this->input->post('father_contact')),
					'father_email' => trim($this->input->post('father_email')),
					'father_occupation' => trim($this->input->post('father_occupation')),
					'father_office_address' => trim($this->input->post('father_office_contact')),
					'm_name' => trim($this->input->post('m_name')),
					'mother_contact' => trim($this->input->post('mother_contact')),
					'address_line1' => trim($this->input->post('address_line1')),
					'hear_source' => trim($this->input->post('hear_source')),
					'emergency_contact_person' => trim($this->input->post('emergency_contact_person')),
					'emergency_contact' => trim($this->input->post('emergency_contact')),
					'a_address' => trim($this->input->post('a_address')),
					'city' => trim($this->input->post('city')),
					'password' => trim('$2y$11$devU5YfJe43QwVEdvRU3UevZO.vlbd3u56yeGYt2k1d2c56VYjm/a'),
					'created_date' => $date,
					'user_id' => $user_id
				);
		
				$this->db->insert('parents', $data2);
				$new_parent_id = $this->db->insert_id();
				
				$this->db->where('parent_id', $new_parent_id);
				$parentsinfo = $this->db->get('parents')->row();
				$parent_id = $parentsinfo->parent_id;
				}

				
				$data = array(
					'reg_no' => trim($this->input->post('reg_no')),
					'first_name' => trim($this->input->post('first_name')),
					'last_name' => trim($this->input->post('last_name')),
					'date_of_birth' => $date_of_birth,
					'parent_id' => $parent_id,
					'gender' => trim($this->input->post('gender')),
					'previous_school' => trim($this->input->post('previous_school')),
					'ps_city' => trim($this->input->post('ps_city')),
					'date_of_admission' => $date_of_admission,
					'class_id' => trim($class_id),
					'campus_id' => trim($this->input->post('campus_id')),
					'session_id' => trim($this->session->userdata('member_sessionid')),
					'cls_sec_id' => trim($this->input->post('section_id')),
					'discounted_amount' => trim($this->input->post('discounted_amount')),
					'major_injuries' => trim($this->input->post('major_injuries')),
					'health_conditions' => trim($this->input->post('health_conditions')),
					'profile_photo' => trim($imagename),
					'status' => 4,
					'created_date' => $date,
					'user_id' => $user_id

				);
				
				$this->db->insert('students', $data);
				$new_student_id = $this->db->insert_id();
				
				$this->db->where('student_id', $new_student_id);
				$stdinfo = $this->db->get('students')->row();
				
				$studentclass = array(
					'student_id' => $stdinfo->student_id,
					'session_id' => $stdinfo->session_id,
					'cls_sec_id' => $stdinfo->cls_sec_id,
					'status' => 4,
					'created_date' => $date,
					'user_id' => $user_id
				);
				$this->db->insert('student_class', $studentclass);
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Add Student Success'));
			}else{
				check_permission('admin-edit-student');
				header('Content-Type: application/json');
			    $config['upload_path']   = './uploads/';
			    $config['allowed_types'] = 'gif|jpg|png';
			    $config['max_size']      = 1024;
		      	$this->load->library('upload', $config);
			  	$this->upload->do_upload('image');
			  	$data = $this->upload->data();
				if($data['file_name']){
			  		$imagename = $data['file_name'];
				}else{
					$imagename = trim($this->input->post('image'));
				}
      			$this->db->trans_begin();
				
				$data = array(
					'first_name' => trim($this->input->post('first_name')),
					'last_name' => trim($this->input->post('last_name')),
					'parent_id' => trim($this->input->post('parent_id')),
					'date_of_birth' => $date_of_birth,
					'gender' => trim($this->input->post('gender')),
					'previous_school' => trim($this->input->post('previous_school')),
					'ps_city' => trim($this->input->post('ps_city')),
					'date_of_admission' => $date_of_admission,
					'campus_id' => trim($this->input->post('campus_id')),
					'discounted_amount' => trim($this->input->post('discounted_amount')),
					'major_injuries' => trim($this->input->post('major_injuries')),
					'health_conditions' => trim($this->input->post('health_conditions')),
					'profile_photo' => trim($imagename),
					'updated_date' => $date,
					'user_id' => $user_id
				
				);
				
				$data2 = array(
					'religion' => trim($this->input->post('religion')),
					'father_cnicnew' => trim($this->input->post('father_cnic')),
					'f_name' => trim($this->input->post('f_name')),
					'father_contact' => trim($this->input->post('father_contact')),
					'father_email' => trim($this->input->post('father_email')),
					'father_occupation' => trim($this->input->post('father_occupation')),
					'father_office_address' => trim($this->input->post('father_office_contact')),
					'm_name' => trim($this->input->post('m_name')),
					'mother_contact' => trim($this->input->post('mother_contact')),
					'address_line1' => trim($this->input->post('address_line1')),
					'hear_source' => trim($this->input->post('hear_source')),
					'emergency_contact_person' => trim($this->input->post('emergency_contact_person')),
					'emergency_contact' => trim($this->input->post('emergency_contact')),
					'a_address' => trim($this->input->post('a_address')),
					'city' => trim($this->input->post('city')),
					'updated_date' => $date,
					'user_id' => $user_id
				);
				
				$studentclass = array(
					'cls_sec_id' => trim($this->input->post('section_id')),
					'updated_date' => $date,
					'user_id' => $user_id
				); 
				
				$this->db->where('student_id', $id);
				$this->db->where('status', 1);
				$this->db->update('student_class', $studentclass);

				$this->db->where('student_id', $id);
				$this->db->update('students', $data);
				
				$this->db->where('parent_id', $parent_id);
				$this->db->update('parents', $data2);
				
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Student Success'));
			}

		}
	}

	function get_parentinfo(){
		$campusid = $this->session->userdata('member_campusid');
		$term = $this->input->post('term');		
		$parentssinfo = $this->db->query("select * from parents where (f_name like '%".$term['term']."%' )  ")->result_array();
		 // Initialize Array with fetched data

     $data = array();
     foreach($parentssinfo as $parent){
     	$classstudents = $this->db->query("select * from students where parent_id = ".$parent['parent_id'])->row();
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

	function delete(){
		check_permission('admin-del-student');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

		// delete user
		$this->db->where('student_id', $id);
		$this->db->delete('students');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Student Success'));
	}
}
// end this file
