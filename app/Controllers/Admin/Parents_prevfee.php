<?php
namespace App\Controllers\Admin;


/**
 * Students Previous Fee Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */
 


class Parents_prevfee extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-defaulter-student-fee-report');
		$this->load->helper(array('form', 'url'));

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

	if(in_array(5, $currentrole)){
		$sectionsclassinfo = teacherSubjectSections();
	}else{
		$sectionsclassinfo = userClassSections();
	}

	$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

	$campus_info = $this->db->from('campus')->where('campus_id', (int) $campus_id)->get()->row();
	$this->template_data['campus_info'] = $campus_info;

	$this->load->view('parents_prevfee', $this->template_data);

	}

// function data(){

// 	$cls_sec_id = $this->input->post('cls_sec_id');
// 	$month = $this->input->post('month');
// 	$campusid = $this->session->userdata('member_campusid');
// 	$sessionid = $this->session->userdata('member_sessionid');
// 	$schoolinfo = getSchoolInfo();

// 	$currentrole = currentUserRoles();

// 	if(in_array(5, $currentrole)){
// 		$sectionsclassinfo = teacherSubjectSections();
// 	}else{
// 		$sectionsclassinfo = userClassSections();
// 	}

// 	$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

	
// $parentsInfo = $this->db->query('SELECT * FROM parents WHERE parent_id IN(SELECT parent_id FROM students WHERE status=1  AND campus_id='.$campusid.')')->result();	
// $currrentSessions = $this->db->query('SELECT * FROM academic_session WHERE session_id='.$sessionid)->row();

// $academicSessions = $this->db->query('SELECT * FROM academic_session WHERE session_id != '.$sessionid.' AND system_id='.$schoolinfo->system_id.' ORDER BY session_id DESC LIMIT 4')->result();	

// 	$studentsList = '';
// 	$currentMonthDisplay = date("M Y");
// 	$prevMonthDisplay = date("M Y", strtotime("-1 months"));
// 	$studentsList .= '<table class="table table-striped table-bordered table-hover" id="students-datatable"  style="font-size:10px;width: 100%;"><thead><tr><th style="width: 55px !important;" nowrap>#</th><th style="width:150px;" nowrap>Father Name</th>';
// 	$studentsList .= '<td style="width:185px;font-size:12px;">Current Session<br>('.$currrentSessions->session_name.')</td>';
//    foreach($academicSessions as $sessionValue){
//    	$studentsList .= '<td style="width:150px;">'.$sessionValue->session_name.'</td>';
//    }
           
//   $studentsList .= '</tr></thead><tbody>';

// foreach ($parentsInfo as $key => $value) {
		
// 	$classSection = $this->db->query('SELECT class_id FROM class_section WHERE cls_sec_id IN(select cls_sec_id from student_class where student_id IN(SELECT student_id FROM students WHERE parent_id='.$value->parent_id.'  AND campus_id='.$campusid.'))')->result();
	
// 	$studentsOfParent = $this->db->query('select cls_sec_id from student_class where status=1 AND student_id IN(SELECT student_id FROM students WHERE parent_id='.$value->parent_id.' AND status=1  AND campus_id='.$campusid.')')->result();
// 	$feeinfoAmount = 0;
// 	foreach ($studentsOfParent as $studentOfParent) {
// 		$singlefeeinfoAmount = $this->db->query('select SUM(amount) as total_amount from fee_amount where fee_type_id = (SELECT fee_type_id from fee_type where is_monthly_fee=1 AND s_flag=1 AND system_id='.$schoolinfo->system_id.') AND campus_id = '.$campusid.' AND  session_id='.$sessionid.' AND class_id IN(SELECT class_id FROM class_section WHERE cls_sec_id IN('.$studentOfParent->cls_sec_id.'))')->row();
// 		$feeinfoAmount = $feeinfoAmount + $singlefeeinfoAmount->total_amount;
// 	}
	
	
// 	// echo "<pre>";
// 	// echo "parent_id:".$value->parent_id."<br>";
// 	// print_r($feeinfoAmount);
// 	// echo "</pre>";
// 	$fee_plans = $this->db->get('fee_plans')->result();
	
// 	$list = $this->db->query('select SUM(discounted_amount) as total_discount from students where status=1 and parent_id='.$value->parent_id)->row(); 
// 	// echo "<pre>";
// 	// print_r($list);
// 	// echo "</pre>";
// 	//echo "<pre>";
	
//    if($feeinfoAmount){
// 		$feeAmount = ($feeinfoAmount - $list->total_discount);
// 	}else{
// 		$feeAmount = '';
// 	}
	

// 	$this->db->where('parent_id', $value->parent_id);
// 	$parentinfo = $this->db->get('parents')->row();

// 	$this->db->where('parent_id', $value->parent_id);
// 	$this->db->where('status', 1);
// 	$studentsInfo = $this->db->get('students')->result();
// 	$students = '';
// 	foreach($studentsInfo as $studentsValue){
// 	 	$students .= '<small>'.$studentsValue->first_name." ".$studentsValue->last_name.'</small>, ';
// 	}
	
// 				$currentMonth = date("m/Y");
// 				$prevMonth = date("m/Y", strtotime("-1 months"));
				
// 				 $f_name = '';
// 				 $father_contact = '';
// 				 $mother_contact = '';
// 				 $emergency_contact = '';
// 				 $whatsapp_contact = '';
// 				 $address = '';
// 				 $balance = 0;
// 				 $prevbalance = 0;
 
// 				if($parentinfo){
// 					$address = $parentinfo->address_line1;
// 					$f_name = $parentinfo->f_name;
// 					$father_contact = $parentinfo->father_contact;
// 					$mother_contact = $parentinfo->mother_contact;
// 					$whatsapp_contact = $parentinfo->whatsapp;
// 					$emergency_contact = $parentinfo->emergency_contact;
// 				} 	

//           $studentsList .= '<tr><th nowrap>'.$value->parent_id.'</th>';
//           $studentsList .= '<th nowrap>'.$f_name.'<br>';
//           $studentsList .= rtrim($students,', ');
//           $studentsList .= '</th>';
//           $studentsList .= '<th>'.$feeAmount.'</th>';    

//  			foreach($academicSessions as $sessionValue){
//  				$sessionNameArr = explode('-', $sessionValue->session_name);

//  				$feeMonth =  '0'.$month.'/'.$sessionNameArr[0];

//  				$feeinfo = $this->db->query('select SUM(amount-discount) as total from fee_chalan where fee_type_id = (SELECT fee_type_id from fee_type where is_monthly_fee=1 AND s_flag=1 AND system_id='.$schoolinfo->system_id.') AND fee_month="'.$feeMonth.'" AND student_id IN(select student_id from students where status=1 and parent_id='.$value->parent_id.') ORDER BY chalan_id DESC')->row();

// 				 if($feeinfo){
// 				 	$balance = $feeinfo->total;
// 				 } 

//              $studentsList .=  '<th nowrap>';
//              $studentsList .=  $balance;
//              $studentsList .= '</th>';
// 			}
                
//         $studentsList .= '</tr>';
     
//           // } 
//        }
// 	$studentsList .= '</tbody></table>';

// 	echo $studentsList;			
// }	


function data() {
    $cls_sec_id = $this->input->post('cls_sec_id');
    $month = $this->input->post('month');
    $campusid = $this->session->userdata('member_campusid');
    $sessionid = $this->session->userdata('member_sessionid');
    $schoolinfo = getSchoolInfo();
    $system_id = $schoolinfo->system_id;

    // Fetch academic sessions in one query
    $this->db->where('session_id', $sessionid);
    $currrentSessions = $this->db->get('academic_session')->row();

    $this->db->where('session_id !=', $sessionid);
    $this->db->where('system_id', $system_id);
    $this->db->order_by('session_id', 'DESC');
    $this->db->limit(4);
    $academicSessions = $this->db->get('academic_session')->result();

    // Fetch parents and their students' fee data in a single query
    $this->db->select('
        p.parent_id,
        p.f_name,
        p.father_contact,
        p.mother_contact,
        p.whatsapp,
        p.emergency_contact,
        p.address_line1,
        GROUP_CONCAT(CONCAT(s.first_name, " ", s.last_name) SEPARATOR ", ") as students,
        COALESCE(SUM(fa.amount), 0) as total_fee,
        COALESCE(SUM(s.discounted_amount), 0) as total_discount
    ');
    $this->db->from('parents p');
    $this->db->join('students s', 'p.parent_id = s.parent_id AND s.status = 1 AND s.campus_id = '.$campusid, 'inner');
    $this->db->join('student_class sc', 's.student_id = sc.student_id AND sc.status = 1', 'left');
    $this->db->join('class_section cs', 'sc.cls_sec_id = cs.cls_sec_id', 'left');
    $this->db->join('fee_amount fa', 'fa.class_id = cs.class_id AND fa.campus_id = '.$campusid.' AND fa.session_id = '.$sessionid.' AND fa.fee_type_id = (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee = 1 AND s_flag = 1 AND system_id = '.$system_id.')', 'left');
    $this->db->where('p.campus_id', $campusid);
    $this->db->group_by('p.parent_id');
    $parentsData = $this->db->get()->result();

    // Precompute historical fees for all parents
    $historicalFees = [];
    foreach ($academicSessions as $session) {
        $sessionYears = explode('-', $session->session_name);
        $feeMonth = '0'.$month.'/'.$sessionYears[0];
        
        $this->db->select('parent_id, SUM(amount - discount) as total');
        $this->db->from('fee_chalan fc');
        $this->db->join('students s', 'fc.student_id = s.student_id');
        $this->db->where('fc.fee_month', $feeMonth);
        $this->db->where('fc.fee_type_id', '(SELECT fee_type_id FROM fee_type WHERE is_monthly_fee = 1 AND s_flag = 1 AND system_id = '.$system_id.')', FALSE);
        $this->db->group_by('s.parent_id');
        $result = $this->db->get()->result();
        
        foreach ($result as $row) {
            $historicalFees[$row->parent_id][$session->session_id] = $row->total;
        }
    }

    // Build HTML table
    $currentSessionName = $currrentSessions->session_name ?? 'Current Session';

    $studentsList = '<table class="table table-striped table-bordered table-hover" id="students-datatable" style="font-size:10px;width:100%;">
        <thead><tr>
            <th nowrap>#</th>
            <th nowrap>Father Name</th>
            <th>'.esc($currentSessionName).'</th>';
    
    foreach ($academicSessions as $session) {
        $studentsList .= '<th>'.$session->session_name.'</th>';
    }
    
    $studentsList .= '</tr></thead><tbody>';
    
    foreach ($parentsData as $parent) {
        $currentFee = $parent->total_fee - $parent->total_discount;
        
        $studentsList .= '<tr>
            <td>'.$parent->parent_id.'</td>
            <td>'.$parent->f_name.'<br><small>'.$parent->students.'</small></td>
            <td>'.$currentFee.'</td>';
        
        foreach ($academicSessions as $session) {
            $fee = isset($historicalFees[$parent->parent_id][$session->session_id]) ? $historicalFees[$parent->parent_id][$session->session_id] : 0;
            $studentsList .= '<td>'.$fee.'</td>';
        }
        
        $studentsList .= '</tr>';
    }
    
    $studentsList .= '</tbody></table>';
    
    echo $studentsList;
}	

function selectClassFee(){
		$campusid = $this->session->userdata('member_campusid');
		$section_id = $this->input->post('section_id');
		$schoolinfo = getSchoolInfo();
		$session_id = $this->session->userdata('member_sessionid');
		$amount = 0;
		$monthlyFeeType = $this->db->select('fee_type_id')->from('fee_type')
			->where('system_id', (int) $schoolinfo->system_id)
			->where('is_monthly_fee', 1)
			->where('s_flag', 1)
			->get()->row();
		$classSection = $this->db->select('class_id')->from('class_section')->where('cls_sec_id', (int) $section_id)->get()->row();
		$feemonth_balance = ($monthlyFeeType && $classSection) ? $this->db->from('fee_amount')
			->select('amount')
			->where('fee_type_id', (int) $monthlyFeeType->fee_type_id)
			->where('class_id', (int) $classSection->class_id)
			->where('campus_id', (int) $campusid)
			->where('session_id', (int) $session_id)
			->get()->row() : null;
		if($feemonth_balance){
			$amount = $feemonth_balance->amount;
		}
		
		echo $amount; 

	}

function saveStudent(){
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$schoolinfo = getSchoolInfo();
	
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	
	$now = date('Y-m-d H:i:s');

	$studentsInfo = $this->input->post('student_id');
	$sectionID = $this->input->post('section_id');
	$fee_plan = $this->input->post('fee_plan');
	
	$currentBalance = $this->input->post('current_balance');
	$previousBalance = $this->input->post('previous_balance');
	$studentRate = $this->input->post('discounted_amount');
	//$classFee = $this->input->post('std_cls_fee');

	$this->db->where('system_id',$schoolinfo->system_id);
	$this->db->where('is_monthly_fee',1);
	$this->db->where('s_flag',1);
	$feeTypeInfo = $this->db->get('fee_type')->row();

	$this->db->where('cls_sec_id', $sectionID);
	$ClassSectioninfo = $this->db->get('class_section')->row();

	$this->db->where('class_id', $ClassSectioninfo->class_id);
	$this->db->where('session_id', $sessionid);
	$this->db->where('fee_type_id', $feeTypeInfo->fee_type_id);
	$this->db->where('campus_id', $campusid);
	$amountInfo = $this->db->get('fee_amount')->row();

	$data = array(
			'discounted_amount' => trim($amountInfo->amount - $studentRate),
			'fee_plan' => trim($fee_plan),
			'updated_date' => $date,
			'user_id' => $user_id
		);

	$this->db->where('student_id', $studentsInfo);
	$this->db->update('students', $data);

	$dataClass = array(
			'cls_sec_id' => trim($ClassSectioninfo->cls_sec_id),
			'updated_date' => $date,
			'user_id' => $user_id
		);


	$this->db->where('student_id', $studentsInfo);
	$this->db->where('session_id', $sessionid);
	$this->db->update('student_class', $dataClass);

	
	$fee_month = date("m/Y");
	$prev_fee_month = date("m/Y", strtotime("-1 months"));
	$issuedate= date('Y-m-d');
	//$issuedate = date('Y-m-d', strtotime("-1 months"));
	$duedate= Date('Y-m-d', strtotime('+10 days'));	

	$this->db->where('fee_type_id', $feeTypeInfo->fee_type_id);
	$this->db->where('student_id', $studentsInfo);
	$this->db->where('fee_month', $prev_fee_month);
	$this->db->where('status', 'unpaid');
	$prevfeeChalaninfo = $this->db->get('fee_chalan')->row();

	$this->db->where('fee_type_id', $feeTypeInfo->fee_type_id);
	$this->db->where('student_id', $studentsInfo);
	$this->db->where('fee_month', $fee_month);
	$this->db->where('status', 'unpaid');
	$feeChalaninfo = $this->db->get('fee_chalan')->row();
			
	if(empty($prevfeeChalaninfo) && $previousBalance > 0){	
			$feeData = array(
				'fee_type_id' => $feeTypeInfo->fee_type_id,
				'student_id' => $studentsInfo,
				'issue_date' => $issuedate,
				'due_date' => $duedate,
				'fee_month' => $prev_fee_month,
				'amount' => $previousBalance,
				'discount' => 0,
				'status' => 'unpaid',
				'created_date' => $date,
				'user_id' => $user_id
				);
		
			$this->db->insert('fee_chalan', $feeData);
			$new_chalan_id = $this->db->insert_id();

	}else if(!empty($prevfeeChalaninfo)){

		$feeData = array(
				'amount' => $previousBalance,
				'discount' => 0,
				'updated_date' => $date,
				'user_id' => $user_id
				);

			$this->db->where('chalan_id', $prevfeeChalaninfo->chalan_id);
			$this->db->update('fee_chalan', $feeData);
			$new_chalan_id = $this->db->insert_id();
	}

	if(empty($feeChalaninfo) && $currentBalance > 0){	
			$feeData = array(
				'fee_type_id' => $feeTypeInfo->fee_type_id,
				'student_id' => $studentsInfo,
				'issue_date' => $issuedate,
				'due_date' => $duedate,
				'fee_month' => $fee_month,
				'amount' => $currentBalance,
				'discount' => 0,
				'status' => 'unpaid',
				'created_date' => $date,
				'user_id' => $user_id
				);
			
			$this->db->insert('fee_chalan', $feeData);
			$new_chalan_id = $this->db->insert_id();

	}else if(!empty($feeChalaninfo)){

		$feeData = array(
				'amount' => $currentBalance,
				'discount' => 0,
				'updated_date' => $date,
				'user_id' => $user_id
				);

		$this->db->where('chalan_id', $feeChalaninfo->chalan_id);
		$this->db->update('fee_chalan', $feeData);
		$new_chalan_id = $this->db->insert_id();
	}

	json_response(array('success' => TRUE, 'msg' => 'Edit Student Success'));
}

function save(){
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');
	$studentsInfo = $this->input->post('student_id');
	$sectionIDs = $this->input->post('section_id');
	$previousBalance = $this->input->post('previous_balance');
	$currentBalance = $this->input->post('current_balance');
	$discountedAmounts = $this->input->post('discounted_amount');

	$schoolinfo = getSchoolInfo();
	
	$campusid = $this->session->userdata('member_campusid');
	$sessionid = $this->session->userdata('member_sessionid');
	
	$now = date('Y-m-d H:i:s');
	
    	{
			check_permission('admin-edit-student');
			//$this->db->trans_begin();
			
		foreach($studentsInfo as $key => $student){

			$section_id = $sectionIDs[$key];

			$this->db->where('cls_sec_id', $section_id);
			$ClassSectioninfo = $this->db->get('class_section')->row();

			$prevBalace = $previousBalance[$key];
			$discountedAmount = $discountedAmounts[$key];

			$data = array(
				'class_id' => trim($ClassSectioninfo->class_id),
				'discounted_amount' => trim($discountedAmount),
				'status' => 1,
				'updated_date' => $date,
				'user_id' => $user_id
			);

			// echo "<pre>";
			// print_r($data);
			// echo "</pre>";
			$this->db->where('student_id', $student);
			$this->db->update('students', $data);

			$studentclass = array(
				'student_id' => $student,
				'session_id' => $sessionid,
				'cls_sec_id' => $section_id,
				'status' => 1,
				'created_date' => $date,
				'user_id' => $user_id
			);

			$this->db->insert('student_class', $studentclass);
			
			$this->db->where('system_id',$schoolinfo->system_id);
			$this->db->where('is_monthly_fee',1);
			$this->db->where('s_flag',1);
			$feeTypeInfo = $this->db->get('fee_type')->row();
			
			$fee_month = date('m/Y');	
			$issuedate= date('Y-m-d');;
			$duedate=Date('Y-m-d', strtotime('+10 days'));	

			$this->db->where('fee_type_id', $feeTypeInfo->fee_type_id);
			$this->db->where('student_id', $student);
			$this->db->where('fee_month', $fee_month);
			$feeChalaninfo = $this->db->get('fee_chalan')->row();
				
			if(empty($feeChalaninfo)){	
				$feeData = array(
					'fee_type_id' => $feeTypeInfo->fee_type_id,
					'student_id' => $student,
					'issue_date' => $issuedate,
					'due_date' => $duedate,
					'fee_month' => $fee_month,
					'amount' => $prevBalace,
					'discount' => 0,
					'status' => 'unpaid',
					'created_date' => $date,
					'user_id' => $user_id
					);
				// echo "<pre>";
				// print_r($feeData);
				// echo "</pre>";
				$this->db->insert('fee_chalan', $feeData);
				$new_chalan_id = $this->db->insert_id();
			}
		// 	echo "1 Entry";
		// exit;
			}
			
			//$this->db->trans_complete();
			json_response(array('success' => TRUE, 'msg' => 'Edit Student Success'));
		}

	
}


}
// end this file
