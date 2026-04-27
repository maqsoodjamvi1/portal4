<?php
namespace App\Controllers\Admin;



/**
 * Pay System Bill Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Pay_system_bill extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-pay-system-bill');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('pay_system_bill', $this->template_data);
	}

	// function data(){
	// 	$response = new stdClass;
	// 	$response->draw = $this->input->post('draw');
	// 	$search = $this->input->post('search');
	// 	$schoolinfo = getSchoolInfo();
	// 	$campusid = $this->session->userdata('member_campusid');

	// 	$system_status = $_GET['system_status'];

	// 	$keyword = '';
	// 	if($search) $keyword = $search['value'];
	// 	$this->db->select('count(A.campus_id) as ccount', FALSE);
	// 	$this->db->from('campus_bills A');
	// 	if($system_status && $system_status != 'inactive'  && $system_status != 'active'){
	// 		$this->db->where('(A.system_status="'.$system_status.'")');
	// 	}
	// 	if($system_status && $system_status == 'inactive'){
	// 		$this->db->where('(A.campus_expiry < NOW())');
	// 	}
	// 	if($system_status && $system_status == 'active'){
	// 		$this->db->where('(A.campus_expiry > NOW())');
	// 	}
	// 	if($keyword){
	// 		$this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
	// 	}
	// 	$q = $this->db->get()->row();
	// 	$response->recordsTotal = $q->ccount;
	// 	$this->db->select('A.*');
	// 	$this->db->from('campus_bills A');
	// 	if($system_status && $system_status != 'inactive'  && $system_status != 'active'){
	// 		$this->db->where('(A.system_status="'.$system_status.'")');
	// 	}
	// 	if($system_status && $system_status == 'inactive'){
	// 		$this->db->where('(A.campus_expiry < NOW())');
	// 	}
	// 	if($system_status && $system_status == 'active'){
	// 		$this->db->where('(A.campus_expiry > NOW())');
	// 	}
	// 	if($keyword){
	// 		$this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
	// 	}
	// 	$this->db->order_by('A.campus_id', 'desc');
	// 	$this->db->limit($this->input->post('length'), $this->input->post('start'));
	// 	$results = $this->db->get()->result();
	// 	$response->recordsFiltered = $response->recordsTotal;
	// 	$response->data = array();
	// 	foreach($results as $row){
			
	// 		$this->db->where('campus_id', $row->campus_id);
	// 		$campus_info = $this->db->get('campus')->row();	
	// 	if($campus_info){
	// 		$this->db->where('system_id', $campus_info->system_id);
	// 		$systemInfo = $this->db->get('system')->row();	

	// 		$this->db->where('campus_id', $row->campus_id);
	// 		$user_info = $this->db->get('users')->row();	
			
	// 		if($user_info){
	// 			$userID = $user_info->id;
	// 			$username = $user_info->username;
	// 			$pass = $user_info->wpwd;
	// 		}else{
	// 			$userID = '';
	// 			$username = '';
	// 			$pass = '';
	// 		}
			
	// 	$system_name = '';
	// 	if($systemInfo){

	// 		$system_name = $systemInfo->system_name;
	// 		$campus_name = '';
	// 		$campus_phone = '';

	// 		if($campus_info){
	// 			$campus_name = $campus_info->campus_name;
	// 			$campus_phone = $campus_info->mobile_no;
	// 			$location = $campus_info->location;
	// 		}

	// 		$this->db->where('plan_id', $row->plan_id);
	// 		$system_plansinfo = $this->db->get('system_plans')->row();	
			
	// 		$plan_name = '';
	// 		if($system_plansinfo){
	// 			$plan_name = $system_plansinfo->plan_name;
	// 		}

	// 	$strSteps = '<div class="config_steps">';
        
   //      $campus_id = $row->campus_id;
       
   //      $this->db->where('system_id', $campus_info->system_id);
   //      $academic_session_info = $this->db->get('academic_session')->row();

   //      if(empty($academic_session_info->session_id)){
   //         $strSteps .= "1.Academic Session, ";  
   //      }
        
   //      $this->db->where('system_id', $campus_info->system_id);
   //      $term_info = $this->db->get('terms')->row();
   //      if(empty($term_info->term_id)){
   //         $strSteps .= "2.Terms, ";  
   //      }
        
   //      $this->db->where('system_id', $campus_info->system_id);
   //      $terms_session_info = $this->db->get('terms_session')->row();
        
   //      if(empty($terms_session_info->term_session_id)){
   //         $strSteps .= "3. Terms Session, ";  
   //      }
        
   //      $this->db->where('system_id', $campus_info->system_id);
   //      $classes_info = $this->db->get('classes')->row();
   //      if(empty($classes_info->class_id)){
   //         $strSteps .= "4. Classes, ";  
   //      }
        
   //      $this->db->where('system_id', $campus_info->system_id);
   //      $sections_info = $this->db->get('sections')->row();
   //      if(empty($sections_info->section_id)){
   //         $strSteps .= "5. Sections, ";  
   //      }
         
   //      $classSections_info = $this->db->query('SELECT * FROM class_section WHERE campus_id='.$campus_id)->row();
        
   //      if(empty($classSections_info->cls_sec_id)){
   //        $strSteps .= "6. Class Section, ";  
   //      }
        
   //      $this->db->where('system_id', $campus_info->system_id);
   //      $subjects_info = $this->db->get('allsubject')->row();
   //      if(empty($subjects_info->sid)){
   //        $strSteps .= "7. Subjects, ";  
   //      }
       
   //      $SectionsSubject_info = $this->db->query('SELECT * FROM section_subjects WHERE subject_id IN (SELECT sid FROM allsubject WHERE system_id ='.$campus_info->system_id.') AND cls_sec_id IN(SELECT cls_sec_id FROM class_section WHERE campus_id='.$campus_id.')')->row();

   //      if(empty($SectionsSubject_info->sec_sub_id)){
   //        $strSteps .= "8. Section Subjects, ";  
   //      }
        
   //      $this->db->where('system_id', $campus_info->system_id);
   //      $fee_type_info = $this->db->get('fee_type')->row();

   //      if(empty($fee_type_info->fee_type_id)){
   //        $strSteps .= "9. Fee Types, ";  
   //      }
       
   //      $this->db->where('campus_id', $campus_id);
   //      $fee_amount_info = $this->db->get('fee_amount')->row();   
   //      if(empty($fee_amount_info->amount_id)){
   //        $strSteps .= "10. Fee Structure";  
   //      }

   //      $this->db->where('campus_id', $campus_id);
   //      $student_info = $this->db->get('students')->row();   
   //      if(empty($student_info->student_id)){
   //        $strSteps .= "11. Add Student";  
   //      }

   //     $total_student_info = $this->db->query('select count(*) as total from students where status=1 and campus_id='.$campus_id)->row(); 
   //     if(!empty($total_student_info)){
   //        $strSteps .= "<br>Total Student:".$total_student_info->total;  
   //     }
      
   //     $last_student_info = $this->db->query('select * from students where status=1 and campus_id='.$campus_id.' ORDER BY student_id desc')->row(); 
       
   //     if(!empty($last_student_info)){
   //        $strSteps .= "<br>Last Student:".$last_student_info->created_date;  
   //        $last_chalan_info = $this->db->query('select * from fee_chalan where status="unpaid" and student_id='.$last_student_info->student_id.' ORDER BY chalan_id desc')->row(); 
       
	//        if(!empty($last_chalan_info)){
	//           $strSteps .= "<br>Last Challan:".$last_chalan_info->created_date;  
	//         }
   //      }  

   //    $strSteps .= "</div>";  

	// 		$data = array();
	// 		$data['id'] = $row->bill_id;
	// 		$data['bill_status'] = $row->bill_status;
	// 		$data['campus_id'] = $row->campus_id;
	// 		$data['campus_name'] = $campus_name;
	// 		$data['system_name'] = $system_name;
	// 		$data['location'] = $location;
	// 		$data['plan_name'] = $plan_name;
	// 		$data['campus_phone'] = $campus_phone;
	// 		$data['created_date'] = $row->created_date;
	// 		$data['campus_expiry'] = '<span style="color:red">'.$row->campus_expiry.'</span>';
	// 		$data['remaining_steps'] = $strSteps;
	// 		$data['bill_amount'] = $row->bill_amount;
	// 		$data['system_status'] = $row->system_status;
	// 		$data['message'] = $systemInfo->status_message;
	// 		$data['system_id'] = $systemInfo->system_id;
	// 		$data['userID'] = $userID;
	// 		$data['username'] = $username;
	// 		$data['pass'] = $pass;
	// 		$response->data[] = $data;
	// 		}
	// 	}
	// }
	// 	$this->output->set_output(json_encode($response));
	// }
	function data(){
    $response = new stdClass;
    $response->draw = $this->input->post('draw');
    $search = $this->input->post('search');
    $schoolinfo = getSchoolInfo();
    $campusid = $this->session->userdata('member_campusid');

    $system_status = $_GET['system_status'];

    $keyword = '';
    if($search) $keyword = $search['value'];
    $this->db->select('count(A.campus_id) as ccount', FALSE);
    $this->db->from('campus_bills A');
    if($system_status && $system_status != 'inactive'  && $system_status != 'active'){
        $this->db->where('(A.system_status="'.$system_status.'")');
    }
    if($system_status && $system_status == 'inactive'){
        $this->db->where('(A.campus_expiry < NOW())');
    }
    if($system_status && $system_status == 'active'){
        $this->db->where('(A.campus_expiry > NOW())');
    }
    if($keyword){
        $this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
    }
    $q = $this->db->get()->row();
    $response->recordsTotal = $q->ccount;
    $this->db->select('A.*');
    $this->db->from('campus_bills A');
    if($system_status && $system_status != 'inactive'  && $system_status != 'active'){
        $this->db->where('(A.system_status="'.$system_status.'")');
    }
    if($system_status && $system_status == 'inactive'){
        $this->db->where('(A.campus_expiry < NOW())');
    }
    if($system_status && $system_status == 'active'){
        $this->db->where('(A.campus_expiry > NOW())');
    }
    if($keyword){
        $this->db->where('(A.campus_name=' . $this->db->escape($keyword) .  ')');
    }
    $this->db->order_by('A.campus_id', 'desc');
    $this->db->limit($this->input->post('length'), $this->input->post('start'));
    $results = $this->db->get()->result();
    $response->recordsFiltered = $response->recordsTotal;
    $response->data = array();
    foreach($results as $row){
        
        $this->db->where('campus_id', $row->campus_id);
        $campus_info = $this->db->get('campus')->row();    
    if($campus_info){
        $this->db->where('system_id', $campus_info->system_id);
        $systemInfo = $this->db->get('system')->row();    

        $this->db->where('campus_id', $row->campus_id);
        $user_info = $this->db->get('users')->row();    
        
        if($user_info){
            $userID = $user_info->id;
            $username = $user_info->username;
            $pass = $user_info->wpwd;
        }else{
            $userID = '';
            $username = '';
            $pass = '';
        }
        
    $system_name = '';
    if($systemInfo){

        $system_name = $systemInfo->system_name;
        $campus_name = '';
        $campus_phone = '';

        if($campus_info){
            $campus_name = $campus_info->campus_name;
            $campus_phone = $campus_info->mobile_no;
            $location = $campus_info->location;
        }

        $this->db->where('plan_id', $row->plan_id);
        $system_plansinfo = $this->db->get('system_plans')->row();    
        
        $plan_name = '';
        if($system_plansinfo){
            $plan_name = $system_plansinfo->plan_name;
        }

    $strSteps = '<div class="config_steps">';
    
    $campus_id = $row->campus_id;
    
    $this->db->where('system_id', $campus_info->system_id);
    $academic_session_info = $this->db->get('academic_session')->row();

    if(empty($academic_session_info->session_id)){
       $strSteps .= "1.Academic Session, ";  
    }
    
    $this->db->where('system_id', $campus_info->system_id);
    $term_info = $this->db->get('terms')->row();
    if(empty($term_info->term_id)){
       $strSteps .= "2.Terms, ";  
    }
    
    $this->db->where('system_id', $campus_info->system_id);
    $terms_session_info = $this->db->get('terms_session')->row();
    
    if(empty($terms_session_info->term_session_id)){
       $strSteps .= "3. Terms Session, ";  
    }
    
    $this->db->where('system_id', $campus_info->system_id);
    $classes_info = $this->db->get('classes')->row();
    if(empty($classes_info->class_id)){
       $strSteps .= "4. Classes, ";  
    }
    
    $this->db->where('system_id', $campus_info->system_id);
    $sections_info = $this->db->get('sections')->row();
    if(empty($sections_info->section_id)){
       $strSteps .= "5. Sections, ";  
    }
     
    $classSections_info = $this->db->query('SELECT * FROM class_section WHERE campus_id='.$campus_id)->row();
    
    if(empty($classSections_info->cls_sec_id)){
      $strSteps .= "6. Class Section, ";  
    }
    
    $this->db->where('system_id', $campus_info->system_id);
    $subjects_info = $this->db->get('allsubject')->row();
    if(empty($subjects_info->sid)){
      $strSteps .= "7. Subjects, ";  
    }
   
    $SectionsSubject_info = $this->db->query('SELECT * FROM section_subjects WHERE subject_id IN (SELECT sid FROM allsubject WHERE system_id ='.$campus_info->system_id.') AND cls_sec_id IN(SELECT cls_sec_id FROM class_section WHERE campus_id='.$campus_id.')')->row();

    if(empty($SectionsSubject_info->sec_sub_id)){
      $strSteps .= "8. Section Subjects, ";  
    }
    
    $this->db->where('system_id', $campus_info->system_id);
    $fee_type_info = $this->db->get('fee_type')->row();

    if(empty($fee_type_info->fee_type_id)){
      $strSteps .= "9. Fee Types, ";  
    }
   
    $this->db->where('campus_id', $campus_id);
    $fee_amount_info = $this->db->get('fee_amount')->row();   
    if(empty($fee_amount_info->amount_id)){
      $strSteps .= "10. Fee Structure";  
    }

    $this->db->where('campus_id', $campus_id);
    $student_info = $this->db->get('students')->row();   
    if(empty($student_info->student_id)){
      $strSteps .= "11. Add Student";  
    }

   $total_student_info = $this->db->query('select count(*) as total from students where status="1" and campus_id='.$campus_id)->row(); 
   if(!empty($total_student_info)){
      $strSteps .= "<br>Total Student:".$total_student_info->total;  
   }
  
   $last_student_info = $this->db->query('select * from students where status="1" and campus_id='.$campus_id.' ORDER BY student_id desc')->row(); 
   
   if(!empty($last_student_info)){
      $strSteps .= "<br>Last Student:".$last_student_info->created_date;  
      $last_chalan_info = $this->db->query('select * from fee_chalan where status="unpaid" and student_id='.$last_student_info->student_id.' ORDER BY chalan_id desc')->row(); 
   
       if(!empty($last_chalan_info)){
          $strSteps .= "<br>Last Challan:".$last_chalan_info->created_date;  
        }
    }  

  $strSteps .= "</div>";  

        $data = array();
        $data['id'] = $row->bill_id;
        $data['bill_status'] = $row->bill_status;
        $data['campus_id'] = $row->campus_id;
        $data['campus_name'] = $campus_name;
        $data['system_name'] = $system_name;
        $data['location'] = $location;
        $data['plan_name'] = $plan_name;
        $data['campus_phone'] = $campus_phone;
        $data['created_date'] = $row->created_date;
        $data['campus_expiry'] = '<span style="color:red">'.$row->campus_expiry.'</span>';
        $data['remaining_steps'] = $strSteps;
        $data['bill_amount'] = $row->bill_amount;
        $data['system_status'] = $row->system_status;
        $data['message'] = $systemInfo->status_message;
        $data['system_id'] = $systemInfo->system_id;
        $data['userID'] = $userID;
        $data['username'] = $username;
        $data['pass'] = $pass;
        $response->data[] = $data;
        }
    }
    }
    $this->output->set_output(json_encode($response));
}

	function updateStatusMessage(){
		//$this->db->db_select('timeschool_trail');
		$systemID = $this->input->post('messagesystemID');
		$message = $this->input->post('message');
		
		$data = array(
			'status_message' => $message
		);

		$this->db->where('system_id', $systemID);
		$this->db->update('system', $data);
		
		json_response(array('success' => TRUE, 'msg' => 'Message Updated'));
	}

	function updateBillStatus(){
		$billID = $this->input->post('billID');
		$status = $this->input->post('status');
		
		$data = array(
			'system_status' => $status
		);
		
		$this->db->where('bill_id', $billID);
		$this->db->update('campus_bills', $data);

		json_response(array('success' => TRUE, 'msg' => 'Status Updated'));
	}

	function reset_password(){
		$userid = $this->input->post('userid');
		//$status = $this->input->post('status');
		
		$data = array(
			'wpwd' => '12345678',
			'password' => trim('$2y$11$devU5YfJe43QwVEdvRU3UevZO.vlbd3u56yeGYt2k1d2c56VYjm/a'),
		);
		
		$this->db->where('id', $userid);
		$this->db->update('users', $data);

		json_response(array('success' => TRUE, 'msg' => 'Status Updated'));
	}


	function getMessage(){
		//$this->db->db_select('timeschool_trail');
		$systemID = $this->input->post('systemID');

		$this->db->where('system_id', $systemID);
		$system_info = $this->db->get('system')->row();	

		echo $system_info->status_message;
	}

	
	function getLoginSMS(){
		//$this->db->db_select('timeschool_trail');
		$campusID = $this->input->post('campusID');
		
		$this->db->where('campus_id', $campusID);
		$campus_info = $this->db->get('campus')->row();

		$this->db->where('system_id', $campus_info->system_id);
		$system_info = $this->db->get('system')->row();	

		$this->db->where('campus_id', $campusID);
		$user_info = $this->db->get('users')->row();

		$sms = 'Free trial account of '.$system_info->system_name.' on TIME Soft Solution. 
	   username: '.$user_info->email.'
	   password: '.$user_info->wpwd.'

	   For Login plz click on
	   https://portal.timesoftsol.com/admin.php

	   Feel free to contact us more detail

		TIME Soft Solution
		Islamabad, Pakistan';

		echo $sms;
	}

	function updateLoginSms(){
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		//$this->db->db_select('timeschool_trail');
		
		$campusID = $this->input->post('smscampusID');
		$login_message = $this->input->post('login_message');
		
		$this->db->where('campus_id', $campusID);
		$campus_info = $this->db->get('campus')->row();

		$mobile = '';
		if($campus_info){
			$mobile = $campus_info->mobile_no;
		}

		//$this->db->db_select('admin_timessportal_live');
		if(!empty($mobile)){
			$data = array(
					'mobile' => $mobile,
					'message' => trim($login_message),
					'campus_id' => trim(1),
					'status' => 0,
					'user_id' => $user_id, 
					'created_date' => $date
					);

			$this->db->insert('sms', $data);
		}

		json_response(array('success' => TRUE, 'msg' => 'Message Updated'));
	}
	
	function getReminderSMS(){
		//$this->db->db_select('timeschool_trail');
		$campusID = $this->input->post('campusID');
		
		$this->db->where('campus_id', $campusID);
		$campus_info = $this->db->get('campus')->row();

		$this->db->where('system_id', $campus_info->system_id);
		$system_info = $this->db->get('system')->row();	

		$this->db->where('campus_id', $campusID);
		$user_info = $this->db->get('users')->row();

		$campus_id = $campus_info->campus_id;
       
		$sms = $system_info->system_name.' 
	   username: '.$user_info->email.'
	   password: '.$user_info->wpwd.'
	   From TIME Soft Solution
	   https://portal.timesoftsol.com/admin.php';

		echo $sms;
	}

	function updateReminderSms(){
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		//$this->db->db_select('timeschool_trail');
		$campusID = $this->input->post('remindersmscampusID');
		$reminder_message = $this->input->post('reminder_message');

		
		$this->db->where('campus_id', $campusID);
		$campus_info = $this->db->get('campus')->row();
		
		$mobile = '';
		if($campus_info){
			$mobile = $campus_info->mobile_no;
		}
		//$this->db->db_select('admin_timessportal_live');
		if(!empty($mobile)){
			$data = array(
					'mobile' => $mobile,
					'message' => trim($reminder_message),
					'campus_id' => trim(1),
					'status' => 0,
					'user_id' => $user_id, 
					'created_date' => $date
					);

		$this->db->insert('sms', $data);
		}

		json_response(array('success' => TRUE, 'msg' => 'Message Updated'));
	}


	function view(){
		$bill_id = $this->input->get('id');
		//$this->db->db_select('timeschool_trail');
		
		$this->db->where('bill_id', $bill_id);
		$systemCampusInfo = $this->db->get('campus_bills')->row();
		$this->template_data['campusbillinfo'] = $systemCampusInfo;
		
		
		$this->db->where('campus_id', $systemCampusInfo->campus_id);
		$campus_info = $this->db->get('campus')->row();	
		$this->template_data['campusinfo'] = $campus_info;

		$this->db->where('campus_id', $systemCampusInfo->campus_id);
		$user_info = $this->db->get('users')->row();	
		$this->template_data['user_info'] = $user_info;
		//print_r($user_info);


		$this->db->where('plan_id', $systemCampusInfo->plan_id);
		$system_plansinfo = $this->db->get('system_plans')->row();
		$this->template_data['systemPlaninfo'] = $system_plansinfo;	


		$this->db->where('install_id', $systemCampusInfo->install_id);
		$system_installment_planinfo = $this->db->get('system_installment_plan')->row();
		$this->template_data['installmentPlaninfo'] = $system_installment_planinfo;
		
		
		$this->db->where('id', $systemCampusInfo->max_students);
		$number_of_students = $this->db->get('number_of_students')->row();
		$this->template_data['number_of_students'] = $number_of_students;
		
		$this->db->where('id', $systemCampusInfo->max_fee);
		$max_student_feeinfo = $this->db->get('max_student_fee')->row();
		$this->template_data['max_student_feeinfo'] = $max_student_feeinfo;
		
		$this->load->view('view_system_bill', $this->template_data);
	}

	function save(){
		$bill_id = intval($this->input->post('bill_id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
	
		//$this->db->db_select('timeschool_trail');

		$this->db->where('bill_id', $bill_id);
		$billInfo = $this->db->get('campus_bills')->row();

		
		$this->db->where('campus_id', $billInfo->campus_id);
		$campusInfo = $this->db->get('campus')->row();

		$this->db->where('system_id', $campusInfo->system_id);
		$systemInfo = $this->db->get('system')->row();
		//print_r($systemInfo);
		//exit;
		if($systemInfo){	
	
		$userInfo = $this->db->query('SELECT * FROM users WHERE campus_id='.$campusInfo->campus_id.' AND id IN(SELECT userID FROM user_roles WHERE roleID=2)')->row();
	
		$data  = array(
			'bill_status' => 'paid', 
		);

		$this->db->where('bill_id', $bill_id);
		$this->db->update('campus_bills',$data);

		//$this->db->db_select('admin_timessportal_live');
 
		$this->db->trans_begin();	
		
		$data  = array( 
			'system_name' => $systemInfo->system_name,
			'reg_text' => $systemInfo->reg_text,
			'address' => $systemInfo->address,
			'owner_name' => $systemInfo->owner_name,
			'mob_number' => $systemInfo->mob_number,
			'city' => $systemInfo->city,
			'created_date' => $date,
			'user_id' => $user_id,
		); 


		$this->db->insert('system', $data);
		$last_system_id = $this->db->insert_id();
		

		if($this->db->affected_rows() > 0){

		    $dataCampus = array(
	   			'system_id' => $last_system_id,
	   			'campus_name' => $campusInfo->campus_name,
	   			'short_name' => $campusInfo->short_name,
	   			'location' => $campusInfo->location,
	   			'mobile_no' => $campusInfo->mobile_no,
	   			's_flag' => 1,
	   			'created_date' => $date,
	   			'user_id' => $user_id,
		   	);

		  $this->db->insert('campus', $dataCampus);
			$last_campus_id = $this->db->insert_id();
			  
		if($this->db->affected_rows() > 0){

			$dataUsers = array(
				'campus_id' => $last_campus_id,
				'first_name' => $userInfo->first_name,
				'last_name' => $userInfo->last_name,
				'email' => $userInfo->email,
				'username' => $userInfo->email,
				'password' => $userInfo->password,
				'mobile_no' => $userInfo->mobile_no,
				'address' => $userInfo->address,
				'created_date' => $date, 
				'user_id' => $user_id,
			);

			
		$this->db->insert('users', $dataUsers);
		$last_user_id = $this->db->insert_id();
		print_r($this->db->error());
			
		if($this->db->affected_rows() > 0){

     		$bill_issue_date = date('Y-m-d');
	      	$next_due_date = date('Y-m-d', strtotime("+".$billInfo->install_id." month"));

		    $dataUserRole = array(
		    	'userID' => $last_user_id, 
		    	'roleID' => 2, 
		    );

		    $this->db->insert('user_roles', $dataUserRole);

		    $dataUserRole2 = array(
		    	'userID' => $last_user_id, 
		    	'roleID' => 3, 
		    );

		    $this->db->insert('user_roles', $dataUserRole2);
		
		   	$dataCampusBills = array(
		   		'campus_id' => $last_campus_id,
		   		'plan_id' => $billInfo->plan_id,
		   		'install_id' => $billInfo->install_id,
		   		'max_students' => $billInfo->max_students,
		   		'max_fee' => $billInfo->max_fee, 
		   		'bill_amount' => $billInfo->bill_amount,
		   		'status' => 1,
		   		'bill_status' => 'paid',
		   		'campus_expiry' => $next_due_date,
		   		'bill_issue_date' => $bill_issue_date,
		   		'created_date' => $date,
		   		'user_id' => $user_id,
		   	);
		     
		   // print_r($dataCampusBills);
		    $this->db->insert('campus_bills', $dataCampusBills);
			$campus_bill_id = $this->db->insert_id();
			//print_r($this->db->error());
			//exit;
        	$this->db->trans_complete();
			json_response(array('success' => TRUE, 'msg' => 'Bill Paid'));
			
		}
	}		
	}
}	
	
}

	function calculateCampusBill(){
		$max_fee = 0;
		$max_students = 0;
		$plan = $this->input->post('plan');
		if($this->input->post('max_fee')){
			$max_fee = $this->input->post('max_fee');
		}
		if($this->input->post('max_students')){
			$max_students = $this->input->post('max_students');
		}
		
		$installment_plan = $this->input->post('installment_plan');
		$currentDate = date('Y-m-d');

		$next_due_date = date('Y-m-d', strtotime("+30 days"));		

		$this->db->where('plan_id', $plan);
		$systemPlan = $this->db->get('system_plans')->row();

		$this->db->where('install_id', $installment_plan);
		$installmentPlan = $this->db->get('system_installment_plan')->row();

		$this->db->where('id', $max_students);
		$numberOfStudents = $this->db->get('number_of_students')->row();

		$this->db->where('id', $max_fee);
		$maxFee = $this->db->get('max_student_fee')->row();

		$monthlyBill = ($systemPlan->factor*$installmentPlan->discount_factor*$maxFee->max_fee*$numberOfStudents->charged);

		$installmentBill = ($systemPlan->factor*$installmentPlan->discount_factor*$installmentPlan->month_count*$maxFee->max_fee*$numberOfStudents->charged);
		
		echo $monthlyBill."/Month<br>";
		echo $installmentBill."/".$installmentPlan->install_name."<br><input type='hidden' name='bill_amount' value='".$installmentBill."'>";

	}

	function delete(){
		check_permission('admin-del-user');
		$id = intval($this->input->get('id'));
		$this->db->trans_begin();
		// delete user
		$this->db->where('id', $id);
		$this->db->delete('classes');
		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Campus Success'));
	}
}
// end this file