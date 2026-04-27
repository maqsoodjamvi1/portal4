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
 


class Student_registration extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-student-registration');
	}

	/**
	 * Index Page for this controller.
	*/

	public function index()
	{	
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->view('student_registration', $this->template_data);
	}


	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		
		$this->db->select('count(A.ad_student_id) as ccount', FALSE);
		$this->db->from('admission_students A');
		if($keyword){
			$this->db->where('(A.name=' . $this->db->escape($keyword) .  ')');
		}
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;
		
		$this->db->select('A.*');
		$this->db->from('admission_students A');
		
		if($keyword){
			$this->db->where('(A.name=' . $this->db->escape($keyword) .  ')');
		}
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();

		$response->recordsFiltered = $response->recordsTotal;
		$response->data = array();
		foreach($results as $row){

			$data = array();
			$data['id'] = $row->ad_student_id;
			$data['name'] = $row->name;
			$data['father_name'] = $row->father_name;
			$data['email'] = $row->email;
			$data['mobile_no'] = $row->mobile_no;
			$data['discipline'] = $row->discipline;
			$data['semester'] = $row->semester;
			$data['section'] = $row->section;
			$data['arid_no'] = $row->arid_no;
			$response->data[] = $data;
		}

		$this->output->set_output(json_encode($response));
	}

function updatestudentselectedstatus(){

	$schoolinfo = getSchoolInfo();
	$student_fee = 0;
	$class_fee = 0;
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');
	$student_id = $this->input->post('studentID');
	$student_fee = $this->input->post('student_fee');
	$class_fee = $this->input->post('classFee');
	$sessionid = $this->session->userdata('member_sessionid');	
	$campusid = $this->session->userdata('member_campusid');				

	{

		$this->db->where('ad_student_id', $student_id);
		$ad_student_info = $this->db->get('admission_students')->row();
		$feeDiscount = 0;
		$feeDiscount = ((float)$class_fee -  (float)$student_fee);
		$issuedate = date('Y-m-d');
		$duedate =  date('Y-m-d',strtotime('+7 day')); 
		$feeMonth = date('m/Y');
		$this->db->trans_begin();
		if($ad_student_info){
			$data = array(
				'parent_id' => trim($ad_student_info->parent_id),
				'campus_id' => trim($campusid),
				'gender' => trim($ad_student_info->gender),
				'reg_no' => trim($ad_student_info->reg_no),
				'name' => trim($ad_student_info->first_name),
				'date_of_birth' => trim($ad_student_info->date_of_birth),
				'class_id' => trim($ad_student_info->class_id),
				'session_id' => trim($sessionid),
				// 'discounted_amount' => trim($feeDiscount),
				'status' => 1,
				'updated_date' => $date,
				'user_id' => $user_id
			);
			
			$this->db->insert('students', $data);
			$new_student_id = $this->db->insert_id();

			if($new_student_id){
				$data = array(
				'fee_type_id' => 2,
				'student_id' => $new_student_id,
				'issue_date' => $issuedate,
				'due_date' => $duedate,
				'fee_month' => $feeMonth,
				'amount' => $class_fee,
				'discount' => $feeDiscount,
				'status' => 'unpaid',
				'created_date' => $date,
				'user_id' => $user_id
				);
				
			$this->db->insert('fee_chalan', $data);
			$new_chalan_id = $this->db->insert_id();

			}
		}
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Successfully updated'));
	}
	
}	

	function add(){
		check_permission('admin-add-student-registration');
		$schoolinfo = getSchoolInfo();
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');

		$sessionData = array(
			'campusid' => $campusid,
			'sessionid' => $sessionid
		);
		$this->template_data['sessionData'] = $sessionData;
		
		$adm_classes_info = $this->db->query("SELECT * from admission_phases where status=1 and campus_id=".$campusid)->result();
		$adm_classes = array();
		foreach ($adm_classes_info as $key => $adm_classes_value) {
			$data = $this->db->query("SELECT * from admission_slot_panels where campus_id=".$campusid." AND capacity > 0 and  class_id=".$adm_classes_value->class_id)->row();
			if($data){
				$classes_info = $this->db->query('select * from classes where class_id='.$adm_classes_value->class_id)->row();
				$adm_classes[] = array(
					'class_id' => $classes_info->class_id,
					'class_name' => $classes_info->class_name
				);
			}
		}
		
		$this->template_data['classinfo'] = $adm_classes;	

		$this->load->view('student_registration_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-student-registration');
		$id = intval($this->input->get('id'));
		$schoolinfo = getSchoolInfo();
		
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		
		$sessionData = array(
		'campusid' => $campusid,
		'sessionid' => $sessionid
		);
		
		$this->template_data['sessionData'] = $sessionData;

		$this->db->where('ad_student_id', $id);
		$info = $this->db->get('admission_students')->row();
		$this->template_data['info'] = $info;	
		
		
		$this->db->where('parent_id', $info->parent_id);
		$parentsinfo = $this->db->get('parents')->row();
		$this->template_data['parentsinfo'] = $parentsinfo;
		
		$adm_classes_info = $this->db->query("SELECT * from admission_phases where status=1 and campus_id=".$campusid)->result();
		$adm_classes = array();
		foreach ($adm_classes_info as $key => $adm_classes_value) {
			$data = $this->db->query("SELECT * from admission_slot_panels where campus_id=".$campusid." AND class_id=".$adm_classes_value->class_id)->row();
			if($data){
				$classes_info = $this->db->query('select * from classes where class_id='.$adm_classes_value->class_id)->row();
				$adm_classes[] = array(
					'class_id' => $classes_info->class_id,
					'class_name' => $classes_info->class_name
				);
			}
		}
		
		$this->template_data['classinfo'] = $adm_classes;	
	
		$this->load->view('student_registration_edit', $this->template_data);
	}

	function save(){
		$user_id = $this->session->userdata['member_userid'];
		$sessionid = $this->session->userdata('member_sessionid');
		$campus_id = $this->session->userdata('member_campusid');
		$schoolinfo = getSchoolInfo();

		$this->db->where('campus_id', $campus_id);
      	$campusInfo = $this->db->get('campus')->row();

		$date = date('Y-m-d H:i:s');
		
		$id = intval($this->input->post('id'));

		$parent_id = intval($this->input->post('parent_id'));
		
		$now = date('Y-m-d H:i:s');

	   //$date_of_admission = systemDateFormat($this->input->post('date_of_admission'));
	   $date_of_admission = date('Y-m-d');
	   
	  $class_id = $this->input->post('class_id');
      $gender = $this->input->post('gender');

      $student_name = $this->input->post('student_name');
      $father_name = $this->input->post('father_name');
      $cnic = $this->input->post('cnic');
      $email = '';//$this->input->post('email');
      
      $dob = $this->input->post('date_of_birth');
      $student_cnic = '';
      if($this->input->post('student_cnic')){
      	$student_cnic = $this->input->post('student_cnic');
      }
      
      $father_contact = $this->input->post('father_phone');
      $mother_contact = $this->input->post('mother_phone');
      $landline = $this->input->post('landline');
      $address = $this->input->post('address');
      $parent_id = intval($this->input->post('parent_id'));

      // $this->db->where('father_cnicnew', $cnic);
      // $parentInfo = $this->db->get('parents')->row();

      $admissionPhaseInfo = $this->db->query('SELECT * from admission_phases where campus_id='.$campus_id.' and class_id='.$class_id.' and status=1')->row();
     
      if(empty($admissionPhaseInfo)){
      	json_response(array('error' => TRUE ,'msg' => 'Admission phase closed'));
		exit;
      }  

      $admissionslotInfo = $this->db->query('SELECT * from admission_slot_panels where campus_id='.$campus_id.' and class_id='.$class_id.' and status=1 and capacity > 0 order by panel_id,slot_id ASC')->row();
       
      if(empty($admissionslotInfo)){
      	json_response(array('error' => TRUE ,'msg' => 'Add admission slots to register student'));
		exit;
      } 
      

		$this->form_validation->set_rules('student_name', 'Student Name', 'trim|required');
		
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			if($id === 0){
				check_permission('admin-add-student-registration');
				$this->db->trans_begin();
				
				if($parent_id > 0){
					//$parent_id = $parent_id;
				
				}else{

					 $dataParent = array(
	                'father_cnicnew' => $cnic,
	                'f_name' => $father_name,
	                'father_email' => $email,
	                'password' => trim('$2y$11$devU5YfJe43QwVEdvRU3UevZO.vlbd3u56yeGYt2k1d2c56VYjm/a'),
	                'father_contact' => $father_contact,
	                'mother_contact' => $mother_contact,
	                'emergency_contact' => $landline,
	                'address_line1' => $address,
	                'created_date' => $date,
	                'status' => 1,
	                'user_id' => $user_id
	            );

				$this->db->insert('parents', $dataParent);
				$new_parent_id = $this->db->insert_id();
				$this->db->where('parent_id', $new_parent_id);
				$parentsinfo = $this->db->get('parents')->row();
				$parent_id = $parentsinfo->parent_id;
				
				}

				$this->db->where('class_id', $class_id);
        		$classInfo = $this->db->get('classes')->row();
        	
        	// $this->db->order_by('ad_student_id', 'desc');
	        // $last_row = $this->db->get('admission_students')->row();

	        $this->db->where('campus_id', $campus_id);
       		$this->db->where('class_id', $class_id);
        	$this->db->order_by('ad_student_id', 'desc');
        	$last_row = $this->db->get('admission_students')->row();
	        //print_r($last_row);
	         $regdate = strtotime(date('Y'));
				$new_date = strtotime('+ 1 year', $regdate);
				$next_year = date('Y', $new_date);
	        if($last_row){

	            $regArr = explode('-' , $last_row->reg_no);
	           
	            $last_id = (int)trim($regArr[4]) + 1;

	        }else{
	            $last_id = 101;
	        }
	        $regG = '';
	        if($gender == 'b'){
	        		$regG = 'B';
	        }

	        if($gender == 'g'){
	        		$regG = 'G';
	        }

	        $reg_no = substr($next_year, -2)."-".$campusInfo->short_name."-".$classInfo->class_short_name."-".$regG."-".$last_id;

			$dataStudent = array(
            'parent_id' => $parent_id,
            'first_name' => $student_name,
            'reg_no' => $reg_no,
            'date_of_birth' => $dob,
            'gender' => $gender,
            'student_cnic' => $student_cnic,
            'class_id' =>  $class_id,
            'campus_id' => $campus_id,
            'created_date' => $date,
            'status' => 1,
            'user_id' => $user_id
        	);
       
        $this->db->insert('admission_students', $dataStudent);
		  $new_student_id = $this->db->insert_id();
			
			if (!empty($this->db->insert_id()) && $this->db->insert_id() > 0) {
	             
	            if($admissionPhaseInfo && $admissionslotInfo){
	                if($admissionPhaseInfo->phase_id && $admissionslotInfo->slot_panel_id){
	                    $admissionRegistration = array(
	                        'ad_std_id' => $this->db->insert_id(),
	                        'campus_id' => $campus_id,
	                        'phase_id' => $admissionPhaseInfo->phase_id,
	                        'slot_panel_id' => $admissionslotInfo->slot_panel_id,
	                    );
	                    $this->db->insert('admission_registration', $admissionRegistration);

	                    $admissionslotInfoData = array(
	                        'capacity' => ($admissionslotInfo->capacity -1),
	                    );

	                    $this->db->where('slot_panel_id', $admissionslotInfo->slot_panel_id);
	                    $msg = $this->db->update('admission_slot_panels', $admissionslotInfoData);
		                }
		            }
		           
		        }		
				
			$this->db->trans_complete();
			$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Student Registration Successful')));
				
				}else{
					check_permission('admin-edit-student-registration');
					$this->db->trans_begin();

					if(empty($cnic)){
						json_response(array('error' => TRUE ,'msg' => 'Father CNIC required'));
						exit;
					}

				
				$dataStudent = array(
	            'first_name' => $student_name,
	            'date_of_birth' => $dob,
	            'gender' => $gender,
	            'student_cnic' => $student_cnic,
	            'class_id' =>  $class_id,
	            'updated_date' => $date,
	            'user_id' => $user_id
        		);
       
				$dataParent = array(
                'father_cnicnew' => $cnic,
                'f_name' => $father_name,
                'father_email' => $email,
                'father_contact' => $father_contact,
                'mother_contact' => $mother_contact,
                'emergency_contact' => $landline,
                'address_line1' => $address,
                'updated_date' => $date,
                'status' => 1,
                'user_id' => $user_id
            );
				
				$this->db->where('ad_student_id', $id);
				$this->db->update('admission_students', $dataStudent);
				
				$this->db->where('parent_id', $parent_id);
				$this->db->update('parents', $dataParent);
				
				$this->db->trans_complete();
				//json_response(array('success' => TRUE,'student_id' => $id ,'msg' => 'Edit Student Success'));
				$this->output->set_output(json_encode(array('success' => TRUE, 'msg' => 'Student Registration Update Successful')));
			}

		}
	}


	function get_parentinfo(){
		$campusid = $this->session->userdata('member_campusid');
		$term = $this->input->post('term');	

		$parentssinfo = $this->db->query("select * from parents where (f_name like '%".$term['term']."%' ) ")->result_array();
		 // Initialize Array with fetched data

     $data = array();
     foreach($parentssinfo as $parent){
     		$data[] = array("id" => $parent['parent_id'], "text" => $parent['f_name']);
     }

		return json_response($data);	 
	}


function get_studentinfo(){
	//print_r($_POST);
		$campusid = $this->session->userdata('member_campusid');
		$term = $this->input->post('term');		
		//echo "select * from students where (first_name like '%".$term['term']."%' OR last_name like '%".$term['term']."%') AND status=".$status." AND campus_id=".$campusid;
		$studentsinfo = $this->db->query("select * from admission_students where (first_name like '%".$term['term']."%' OR last_name like '%".$term['term']."%') AND campus_id=".$campusid)->result_array();
		 // Initialize Array with fetched data 
     $data = array();
     foreach($studentsinfo as $student){
     	
     	$parentsInfo = $this->db->query("select f_name from parents where  parent_id = ".$student['parent_id'])->row();
     	$father_name = '';
     	if($parentsInfo){
     		$father_name  = "c/o ".$parentsInfo->f_name;
     	}
     	
     	$stdInfotxt = $student['first_name']." ".$student['last_name'].' '.$father_name;

     	$data[] = array("id"=>$student['ad_student_id'], "text"=>$stdInfotxt);
     	
     }
	return json_response($data);	 
}

function delete(){
		check_permission('admin-del-student-registration');
		$id = intval($this->input->get('id'));

		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');

		$this->db->trans_begin();


		$data = array(
					'status' => 5,
					'updated_date' => $date,
					'user_id' => $user_id
		);	

		// delete user
		$this->db->where('student_id', $id);
		$this->db->where('status', 1);
		$this->db->update('student_class',$data);
		// delete user
		$this->db->where('student_id', $id);
		$this->db->update('students',$data);

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Student Success'));
	}
}
// end this file
