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
 


class Hostel_defaulters_list extends MY_Controller {

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
		$schoolinfo = getSchoolInfo();
		$currentrole = currentUserRoles();

		if(in_array(5, $currentrole)){
			$sectionsclassinfo = teacherSubjectSections();
		}else{
			$sectionsclassinfo = userClassSections();
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	

		$fee_types = $this->db->from('fee_type')->where('system_id', (int) $schoolinfo->system_id)->get()->result();
		$this->template_data['fee_types'] = $fee_types;	
		
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->view('hostel_defaulters_list', $this->template_data);
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

	function data(){
	
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');
		$schoolinfo = getSchoolInfo();

		$sessionInfo = $this->db->from('academic_session')->where('session_id', (int) $sessionid)->get()->row();
		$dateArr = explode('-',$sessionInfo->start_date);
		$session_year = $dateArr[0];
		
		$months = rtrim($this->nb_mois($sessionInfo->start_date,$sessionInfo->end_date),',');

		$listData = $this->students->get_datatables();
		
		$list = $listData['query_result'];
		$fee_month = $listData['fee_month'];
		$fee_type_id = $listData['fee_type_id'];
		if(!empty($fee_month)){
			$monthdate = '"'.'0'.$fee_month.'/'.$session_year.'"';
		}else{
		 	$monthdate = $months; //date("m/Y");
		}

		$data = array();
		$response = array();
		$no = $_POST['start'];
		foreach ($list as $row) {
			
		$data = array();
		
		if($row){

			$total_discount = 0;
			$payable = 0;
			$projectedfee = 0;
			$classinfo = '';
			$sectioninfo = '';
			$className = '';
			$sectionName = '';

			$feeMonths = array_map(static function (string $m): string {
				return trim($m, '"\' ');
			}, array_filter(explode(',', (string) $monthdate)));

			if(!empty($fee_type_id)){
				$feeInfo = $this->db->from('fee_type')->where('fee_type_id', (int) $fee_type_id)->get()->row();
				$columnName = $feeInfo->fee_type_name;
				$currentMonthBuilder = $this->db->table('fee_chalan')
					->select('SUM(amount) - SUM(discount) as total', false)
					->where('status', 'UnPaid')
					->where('student_id', (int) $row->student_id)
					->where('fee_type_id', (int) $fee_type_id);
				if ($feeMonths !== []) {
					$currentMonthBuilder->whereIn('fee_month', $feeMonths);
				}
				$currentMonthUnpaid = $currentMonthBuilder->get()->row();
			}else{
				$hostelFeeSub = $this->db->select('fee_type_id')->from('fee_type')
					->where('is_monthly_fee', 1)->where('h_flag', 1)->where('system_id', (int) $schoolinfo->system_id)
					->get_compiled_select();
				$columnName = $monthdate;
				$currentMonthBuilder = $this->db->table('fee_chalan')
					->select('SUM(amount) - SUM(discount) as total', false)
					->where('status', 'UnPaid')
					->where('student_id', (int) $row->student_id)
					->where("fee_type_id IN ($hostelFeeSub)", null, false);
				if ($feeMonths !== []) {
					$currentMonthBuilder->whereIn('fee_month', $feeMonths);
				}
				$currentMonthUnpaid = $currentMonthBuilder->get()->row();
			}
			// echo "<pre>";
			// print_r($this->db->last_query());
			// echo "</pre>";

			
			$unpaid = $this->db->table('fee_chalan')
				->select('SUM(amount) - SUM(discount) as total', false)
				->where('status', 'UnPaid')
				->where('student_id', (int) $row->student_id)
				->get()->row();

			$discount = $this->db->table('fee_chalan')
				->select('SUM(discount) as total_discount', false)
				->where('status', 'UnPaid')
				->where('student_id', (int) $row->student_id)
				->get()->row();
	
			if($discount){
				$total_discount = $discount->total_discount;
			}
		
			if($unpaid){
				$payable = $unpaid->total;
			}

		if(!empty($payable) && !empty($currentMonthUnpaid->total)){
			$no++;
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

			// $getclassfee = $this->db->query('SELECT * FROM `fee_amount` WHERE class_id='.$classsectioninfo->class_id.' and fee_type_id IN(select fee_type_id from fee_type where is_monthly_fee=1) and session_id='.$sessionid.' and campus_id='.$campusid)->row();
			
			// if($getclassfee){	
		 //   		$projectedfee = ($getclassfee->amount - $row->discounted_amount);
			// }

		}

		$this->db->where('status', 1);
		$this->db->where('session_id', $sessionid);
		$this->db->where('student_id', $row->student_id);
		$h_student_beds = $this->db->get('h_student_bed')->row();

		$this->db->where('block_room_id', $h_student_beds->block_room_id);
		$h_block_rooms = $this->db->get('h_block_rooms')->row();


		$this->db->where('room_id', $h_block_rooms->room_id);
		$h_rooms = $this->db->get('h_rooms')->row();

		$h_room_bed_count = $this->db->table('h_room_beds')
			->selectCount('*', 'total')
			->where('block_room_id', (int) $h_block_rooms->block_room_id)
			->groupBy('block_room_id')
			->get()->row();

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
		 $whatsapp_contact = '';
		if($parentinfo){
			$address = $parentinfo->address_line1;
			$f_name = $parentinfo->f_name;
			$father_contact = $parentinfo->father_contact;
			$mother_contact = $parentinfo->mother_contact;
			$emergency_contact = $parentinfo->emergency_contact;
			$whatsapp_contact = $parentinfo->whatsapp;
		}
			
			$data['id'] = $no;//$row->student_id;
			
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
			//$age = date_diff(date_create($row->date_of_birth), date_create('now'))->y;
			
			//$data['reg_no'] = $row->reg_no;
			$data['name'] = $row->first_name." ".$row->last_name;
			$data['f_name'] = $f_name;
			//$data['age'] = $age." Years";
			//$data['gender'] = $row->gender;
			$data['address'] = $address;
			//$data['room_beds'] = $h_room_bed_count->total;
			$data['class'] = "R#".$h_block_rooms->room_no.'<br>('.$h_room_bed_count->total.' Beds)';
			$data['section'] = $sectionName;
			$data['f_contacts'] = $father_contact;
			$data['m_contacts'] = $mother_contact;
			$data['e_contacts'] = $emergency_contact;
			$data['w_contacts'] = $whatsapp_contact;
			$data['monthly_unpaid'] = $currentMonthUnpaid->total ? : 0;
			$data['previous_balance'] = ($payable - $currentMonthUnpaid->total);
			$data['payable'] = ($payable);
			$data['columnName'] = $columnName;
			//$data['discounted_amount'] = $row->discounted_amount;
			//$data['discounted'] = $total_discount;
			//$data['projectedfee'] = $projectedfee;
			$response[] = $data;
		}
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

		$campus_bill_info = $this->db->from('campus_bills')->where('status', 1)->where('campus_id', (int) $campusid)->get()->row();
		$max_student_id = (int) ($campus_bill_info->max_students ?? 0);

		$max_no_of_students_info = $this->db->select('no_of_students')->from('number_of_students')->where('id', $max_student_id)->get()->row();
		//print_r($max_no_of_students_info->no_of_students);
		$max_student_limit = $max_no_of_students_info->no_of_students;
		//exit;
		
		$activeStudentSub = $this->db->select('student_id')->from('student_class')->where('status', 1)->get_compiled_select();
		$students_info = $this->db->table('students')
			->selectCount('student_id', 'studentTotal')
			->where('campus_id', (int) $campusid)
			->where("student_id IN ($activeStudentSub)", null, false)
			->get()->row();		
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


	function get_parentinfo(){
		$campusid = $this->session->userdata('member_campusid');
		$term = $this->input->post('term');		
		$searchTerm = trim((string) ($term['term'] ?? ''));
		$this->db->from('parents');
		$this->db->where('campus_id', (int) $campusid);
		if ($searchTerm !== '') {
			$this->db->like('f_name', $searchTerm);
		}
		$parentssinfo = $this->db->get()->result_array();
		 // Initialize Array with fetched data

     $data = array();
     foreach($parentssinfo as $parent){
     	$classstudents = $this->db->from('students')->where('parent_id', (int) $parent['parent_id'])->where('campus_id', (int) $campusid)->get()->row();
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
			$searchTerm = trim((string) ($term['term'] ?? ''));
			$this->db->from('students');
			$this->db->where('status', (int) $status);
			$this->db->where('campus_id', (int) $campusid);
			if ($searchTerm !== '') {
				$this->db->group_start();
				$this->db->like('first_name', $searchTerm);
				$this->db->or_like('last_name', $searchTerm);
				$this->db->group_end();
			}
			$studentsinfo = $this->db->get()->result_array();
			 // Initialize Array with fetched data 
	     $data = array();
	     foreach($studentsinfo as $student){
	     	$classstudents = $this->db->from('student_class')->where('student_id', (int) $student['student_id'])->get()->row();
	     	$parentsInfo = $this->db->select('f_name')->from('parents')->where('parent_id', (int) $student['parent_id'])->get()->row();

	     	
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
