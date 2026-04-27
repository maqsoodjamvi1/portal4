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
 


class Students_defaulters_list extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-students-contact-list');
		$this->load->helper(array('form', 'url'));
		$this->load->model('students_model_defaulters_list','students');

	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{	

		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();
		$currentrole = currentUserRoles();

		$sessionInfo = $this->db->query('SELECT * FROM `academic_session` WHERE session_id ='.$sessionid)->row();
		$dateArr = explode('-',$sessionInfo->start_date);
		$session_year = $dateArr[0];

		$months = $this->nb_mois2($sessionInfo->start_date,$sessionInfo->end_date);
		
		// echo "<pre>";
		// print_r($months);
		// echo "</pre>";
		$this->template_data['months'] = $months;

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	

		$fee_types = $this->db->query('SELECT * FROM `fee_type` WHERE system_id  ='.$schoolinfo->system_id)->result();
		$this->template_data['fee_types'] = $fee_types;	
		
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->view('students_defaulters_list', $this->template_data);
	}

	public function nb_mois2($date1, $date2)
	{
	    $begin = new DateTime( $date1 );
	    $end = new DateTime( $date2 );
	    $end = $end->modify( '+1 month' );

	    $interval = DateInterval::createFromDateString('1 month');

	    $period = new DatePeriod($begin, $interval, $end);
	    $counter = 0;
	    $monthList = array();
	    foreach($period as $dt) {
	        //$counter++;
	        $monthList[] = array(
	        	'id' => $dt->format("m/Y"),
	        	'value' => $dt->format("M/Y"),
	        );
	         //print_r($monthList);
	    }

	    return $monthList;
	}

	public function nb_mois($date1, $date2)
	{
	    $begin = new DateTime( $date1 );
	    $end = new DateTime( $date2 );
	    $end = $end->modify( '+1 month' );

	    $interval = DateInterval::createFromDateString('1 month');

	    $period = new DatePeriod($begin, $interval, $end);
	    $counter = 0;
	    $monthList = '';
	    foreach($period as $dt) {
	        //$counter++;
	        $monthList .= '"'.$dt->format("m/Y").'",';
	    }

	    return $monthList;
	}

	// function data(){
	// 	$this->load->library('parser');
	// 	$campusid = $this->session->userdata('member_campusid');
	// 	$sessionid = $this->session->userdata('member_sessionid');
	// 	$schoolinfo = getSchoolInfo();

	// 	$this->db->where('campus_id', $campusid);
	//     $info = $this->db->get('campus')->row();

	//     $student_fee_sms = $info->student_fee_sms;
	//     $template = $student_fee_sms;

	// 	$sessionInfo = $this->db->query('SELECT * FROM `academic_session` WHERE session_id ='.$sessionid)->row();
	// 	$dateArr = explode('-',$sessionInfo->start_date);
	// 	$session_year = $dateArr[0];
		
	// 	$months = rtrim($this->nb_mois($sessionInfo->start_date,$sessionInfo->end_date),',');

	// 	$listData = $this->students->get_datatables();
		
	// 	$list = $listData['query_result'];
	// 	$fee_month = $listData['fee_month'];
	// 	//print_r($fee_month);
	// 	//exit;
	// 	$fee_type_id = $listData['fee_type_id'];
	// 	if(!empty($fee_month)){
			
	// 		$monthdate = '"'.$fee_month.'"';
			
	// 	}else{
	// 	 	$monthdate = '"'.date("m/Y").'"';
	// 	}

	// 	$data = array();
	// 	$response = array();
	// 	$no = $_POST['start'];
	// 	foreach ($list as $row) {
			
	// 	$data = array();
		
	// 	if($row){

	// 		$total_discount = 0;
	// 		$payable = 0;
	// 		$projectedfee = 0;
	// 		$classinfo = '';
	// 		$sectioninfo = '';
	// 		$className = '';
	// 		$sectionName = '';

	// 		if(!empty($fee_type_id)){
	// 			$strQuery = ' and fee_type_id='.$fee_type_id.' and fee_month IN('.$monthdate.') ';
	// 			$strMonthQuery = '';
	// 			$feeInfo = $this->db->query('SELECT * FROM `fee_type` WHERE status=1 AND fee_type_id='.$fee_type_id)->row();
	// 			$columnName = $feeInfo->fee_type_name;
	// 		}else{
	// 			$strQuery = '';
	// 			$strQuery = ' and fee_month IN('.$monthdate.') ';
	// 			$strMonthQuery = '';

	// 			//$strMonthQuery = 'and fee_type_id=(select fee_type_id from fee_type where is_monthly_fee=1 and s_flag=1 and system_id='.$schoolinfo->system_id.') and fee_month IN('.$monthdate.') ';
	// 			$columnName = $monthdate;
	// 		}
	
	// 		$currentMonthUnpaid = $this->db->query('SELECT SUM(amount) - SUM(discount) as total FROM `fee_chalan` WHERE status = "UnPaid" '.$strMonthQuery.' and student_id ='.$row->student_id.' '.$strQuery)->row();
	// 		// echo "<pre>";
	// 		// print_r($this->db->last_query());
	// 		// echo "</pre>";
	// 		$unpaid = $this->db->query('SELECT SUM(amount)-SUM(discount) as total FROM `fee_chalan` WHERE status = "UnPaid" and student_id ='.$row->student_id)->row();
	// 		// echo "<br>";
	// 		// echo "<pre>";
	// 		// print_r($this->db->last_query());
	// 		// echo "</pre>";
 

	// 		$discount = $this->db->query('SELECT SUM(discount) as total_discount FROM `fee_chalan` WHERE status = "UnPaid" and student_id ='.$row->student_id)->row();
	
	// 		if($discount){
	// 			$total_discount = $discount->total_discount;
	// 		}
		
	// 		if($unpaid){
	// 			$payable = $unpaid->total;
	// 		}

	// 	if(!empty($payable) && !empty($currentMonthUnpaid->total)){
	// 		$no++;
	// 		$this->db->where('student_id', $row->student_id);
	// 		$this->db->where('status', $this->input->get('status'));
	// 		$this->db->where('session_id', $sessionid);
	// 		$studentclassinfo = $this->db->get('student_class')->row();
		
	// 	if($studentclassinfo){
	// 		$sectionInfo = '';
	// 		$this->db->where('cls_sec_id', $studentclassinfo->cls_sec_id);
	// 		$classsectioninfo = $this->db->get('class_section')->row();	
			
	// 		if($classsectioninfo){
	// 			$this->db->where('class_id', $classsectioninfo->class_id);
	// 			$classinfo = $this->db->get('classes')->row();
	// 		}

	// 		if($classsectioninfo){
	// 			$this->db->where('section_id', $classsectioninfo->section_id);
	// 			$sectionInfo = $this->db->get('sections')->row();
	// 		}
	// 		if($sectionInfo){
	// 			$sectionName = $sectionInfo->section_name;
	// 		}

	// 	}
		
	// 	if($classinfo){
	// 		$className = $classinfo->class_name;
	// 	}
		
	// 	$this->db->where('parent_id', $row->parent_id);
	// 	$parentinfo = $this->db->get('parents')->row();
	// 	//print_r($parentinfo);
	// 	 $f_name = '';
	// 	 $father_contact = '';
	// 	 $mother_contact = '';
	// 	 $emergency_contact = '';
	// 	 $whatsapp_contact = '';
	// 	if($parentinfo){
	// 		$address = $parentinfo->address_line1;
	// 		$f_name = $parentinfo->f_name;
	// 		$father_contact = $parentinfo->father_contact;
	// 		$mother_contact = $parentinfo->mother_contact;
	// 		$emergency_contact = $parentinfo->emergency_contact;
	// 		$whatsapp_contact = $parentinfo->whatsapp;
	// 	}
			
	// 		$data['id'] = $no;//$row->student_id;
			
	// 		$imgurl = FCPATH."uploads/".$row->profile_photo;
			
	// 		if($row->profile_photo){
	// 		if(file_exists($imgurl)){

	// 			$data['profile_photo'] = "<img style='width:50px;height:50px;text-align: center;display: block;border-radius: 30px;margin: 0 auto;' src='".base_url("uploads/".$row->profile_photo)."' >";
						
	// 		}else{

	// 			$data['profile_photo'] = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
	// 		}
	// 		}else{
	// 			$data['profile_photo'] = "<i style='font-size: 40px;text-align: center;display: block;' class='fa fa-user'></i>";
	// 		}
			
	// 		//print_r($row->date_of_birth);
	// 		//$age = date_diff(date_create($row->date_of_birth), date_create('now'))->y;

	// 	   //$studentName = $studentsinfo->first_name." ".$studentsinfo->last_name;	

	// 	   $smsDate = date('Y-m-d');

	// 		$dataMessage = array(
	// 		        'first_name' => $row->first_name,
	// 		        'last_name' => $row->last_name,
	// 		        'father_name' => $f_name,
	// 		        'class' => $className."(".$sectionName.")",
	// 		        'balance' => $payable,
	// 		        'date' => $smsDate
	// 		);


	// 	   $parsedMessage = $this->parser->parse_string($template, $data);

			
	// 		$data['name'] = $row->first_name." ".$row->last_name;
	// 		$data['f_name'] = $f_name;
	// 		$data['address'] = $address;
	// 		$data['class'] = $className."(".$sectionName.")";
	// 		$data['section'] = $sectionName;
	// 		$data['f_contacts'] = '<a href="https://wa.me/'.$father_contact.'?text='.$parsedMessage.'">'.$father_contact.'</a>';
	// 		$data['m_contacts'] = $mother_contact;
	// 		$data['e_contacts'] = $emergency_contact;
	// 		$data['w_contacts'] = $whatsapp_contact;
	// 		$data['monthly_unpaid'] = $currentMonthUnpaid->total ? : 0;
	// 		$data['previous_balance'] = ($payable - $currentMonthUnpaid->total);
	// 		$data['payable'] = ($payable);
	// 		$data['columnName'] = $columnName;
	// 		$response[] = $data;
	// 	}
	// 	}
	// 	}
	// 	$output = array(
	// 			"draw" => $_POST['draw'],
	// 			"recordsTotal" => $this->students->count_all(),
	// 			"recordsFiltered" => $this->students->count_filtered(),
	// 			"data" => $response,
	// 		);
		
	// 	$this->output->set_output(json_encode($output));
	// }

	public function data()
{
    $this->load->library('parser');

    $campusid = (int) $this->session->userdata('member_campusid');
    $sessionid = (int) $this->session->userdata('member_sessionid');

    $schoolinfo = getSchoolInfo();

    $info = $this->db->where('campus_id', $campusid)->get('campus')->row();
    $student_fee_sms = $info->student_fee_sms ?? '';
    $template = $student_fee_sms;

    $sessionInfo = $this->db->where('session_id', $sessionid)->get('academic_session')->row();
    $months = rtrim($this->nb_mois($sessionInfo->start_date ?? '', $sessionInfo->end_date ?? ''), ',');

    $listData = $this->students->get_defaulters_with_details();
    $list = $listData['query_result'];
    $fee_month = $listData['fee_month'];
    $fee_type_id = $listData['fee_type_id'];

    $monthdate = !empty($fee_month) ? '"' . $fee_month . '"' : '"' . date("m/Y") . '"';
    $columnName = !empty($fee_month) ? $fee_month : date('m/Y');

    // 🔵 Preload unpaid fees in bulk
    $feeRecords = $this->students->get_all_unpaid_fees($monthdate, $fee_type_id);
    $fee_lookup = [];
    foreach ($feeRecords as $fee) {
        $fee_lookup[$fee->student_id] = [
            'total_amount' => $fee->total_amount,
            'total_discount' => $fee->total_discount,
            'current_month_unpaid' => $fee->current_month_unpaid
        ];
    }

    $response = [];
    $no = $_POST['start'] ?? 0;

    foreach ($list as $row) {
        if (!$row) continue;

        $payable = $fee_lookup[$row->student_id]['total_amount'] ?? 0;
        $currentMonthUnpaid = $fee_lookup[$row->student_id]['current_month_unpaid'] ?? 0;

        if ($payable && $currentMonthUnpaid) {
            $no++;
            $smsDate = date('Y-m-d');

            $dataMessage = [
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'father_name' => $row->f_name,
                'class' => $row->class_name . "(" . $row->section_name . ")",
                'balance' => $payable,
                'date' => $smsDate
            ];
            $parsedMessage = $this->parser->parse_string($template, $dataMessage, true);

            $img = (!empty($row->profile_photo) && file_exists(FCPATH . "uploads/" . $row->profile_photo))
                ? "<img src='" . base_url("uploads/" . $row->profile_photo) . "' style='width:50px;height:50px;border-radius:30px;margin:0 auto;'>"
                : "<i class='fa fa-user' style='font-size:40px;text-align:center;display:block;'></i>";

            $response[] = [
                'id' => $no,
                'profile_photo' => $img,
                'name' => $row->first_name . " " . $row->last_name,
                'f_name' => $row->f_name,
                'address' => $row->address_line1,
                'class' => $row->class_name . "(" . $row->section_name . ")",
                'section' => $row->section_name,
                'f_contacts' => '<a href="https://wa.me/' . $row->father_contact . '?text=' . urlencode($parsedMessage) . '">' . $row->father_contact . '</a>',
                'm_contacts' => $row->mother_contact,
                'e_contacts' => $row->emergency_contact,
                'w_contacts' => $row->whatsapp,
                'monthly_unpaid' => $currentMonthUnpaid,
                'previous_balance' => ($payable - $currentMonthUnpaid),
                'payable' => $payable,
                'columnName' => $columnName
            ];
        }
    }

    $output = [
        "draw" => (int) $_POST['draw'],
        "recordsTotal" => $this->students->count_all(),
        "recordsFiltered" => $this->students->count_filtered(),
        "data" => $response,
    ];

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
		
		$this->load->view('students_contact_list_edit', $this->template_data);
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

		$this->load->view('students_contact_list_edit', $this->template_data);
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
