<?php
namespace App\Controllers\Admin;



/**
 * Fee Chalan Pay Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2018-2019 TIME Soft Solutions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Fee_chalan_pay2 extends MY_Controller {
	function __construct(){
		parent::__construct();
		check_permission('admin-fee-chalan-pay');
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
			//print_r($sectionsclassinfo);
		}

		$this->template_data['sectionsclassinfo'] = $sectionsclassinfo;	

	  
	  $this->load->view('fee/parent_fee_view', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');
		$search = $this->input->post('search');
		$keyword = '';
		if($search) $keyword = $search['value'];
		
		$this->db->select('count(A.chalan_id) as ccount', FALSE);
		$this->db->from('fee_chalan A');
		$q = $this->db->get()->row();
		$response->recordsTotal = $q->ccount;

		$this->db->select('A.*');
		$this->db->from('fee_chalan A');
		$this->db->order_by('A.chalan_id', 'desc');
		$this->db->limit($this->input->post('length'), $this->input->post('start'));
		$results = $this->db->get()->result();
		$response->recordsFiltered = $response->recordsTotal;

		$response->data = array();
		foreach($results as $row){
		$this->db->where('fee_type_id', $row->fee_type_id);
		$fee_type_info = $this->db->get('fee_type')->row();

		$this->db->where('student_id',  $row->student_id);
		$student_info = $this->db->get('students')->row();

			$data = array();
			$data['id'] = $row->chalan_id;
			$data['student_name'] = $student_info->first_name." ".$student_info->last_name;
			$data['due_date'] = $row->due_date;
			$data['issue_date'] = $row->issue_date;
			$data['fee_month'] = $row->fee_month;
			$data['amount'] = $row->amount;
			$data['status'] = $row->status;
			$data['discount'] = $row->discount;
			$data['paiddate'] = $row->paid_date;
			$data['fee_name'] = $fee_type_info->fee_type_name;
						
			$response->data[] = $data;

		}

		return $response;
		//$this->output->set_output(json_encode($response));

	}

	



function get_students_list() {
    $ci =& get_instance();
    $ci->load->database();
    
    // Get session data
    $campus_id = $ci->session->userdata('member_campusid');
    $session_id = $ci->session->userdata('member_sessionid');
    
    // Get system_id - new improved logic
    $system_id = $ci->session->userdata('system_id');
    if (!$system_id) {
        // Derive system_id from campus if not in session
        $campus_info = $ci->db->get_where('campus', ['campus_id' => $campus_id])->row();
        if (!$campus_info || !$campus_info->system_id) {
            die("System configuration error: Could not determine organization");
        }
        $system_id = $campus_info->system_id;
        $ci->session->set_userdata('system_id', $system_id); // Store for future requests
    }

    // Get school info
    $schoolinfo = $ci->db->get_where('system', ['system_id' => $system_id])->row();
    if (!$schoolinfo) {
        die("Invalid system configuration. Check database records.");
    }

    // Get campus info
    $campus_info = $ci->db->get_where('campus', ['campus_id' => $campus_id])->row();

    // Get parent ID
    $parent_id = $this->determine_parent_id($ci, $campus_id);
    if (!$parent_id) die("Family information not found");

    // Get student data
    $studentslistinfo = $ci->db
        ->select('students.*, cs.class_id, cs.section_id, cls.class_name, sec.section_name')
        ->join('student_class sc', 'sc.student_id = students.student_id AND sc.session_id = '.$session_id, 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes cls', 'cls.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where(['students.campus_id' => $campus_id, 'students.status' => 1, 'parent_id' => $parent_id])
        ->get('students')
        ->result();

    // Get fee summaries
    $fee_summaries = $this->get_fee_summaries($ci, $campus_id, $parent_id);
    
    // Generate modals and JS
    $modals_js = $this->generate_modals_and_js($ci, $studentslistinfo, $campus_id, $session_id, $schoolinfo, $parent_id, $fee_summaries);

    // Build fee list HTML
    $feeList = $this->build_fee_list_html($ci, $studentslistinfo, $campus_info, $session_id, $fee_summaries, $modals_js);

    $ci->output->set_output($feeList);
}

// Helper functions
private function determine_parent_id($ci, $campus_id) {
    // If searching by parent, directly get parent_id from POST
    return $ci->input->post('parent_id') ?? 
           $ci->db->select('parent_id')->get_where('students', [
               'student_id' => $ci->input->post('student_id'), 
               'campus_id' => $campus_id
           ])->row()->parent_id;
}


private function get_fee_summaries($ci, $campus_id, $parent_id) {
    $current_date = date('Y-m-d'); // Get current date

    // Retrieve student IDs for the given parent and campus
    $ci->db->select('student_id');
    $ci->db->from('students');
    $ci->db->where('parent_id', $parent_id);
    $ci->db->where('campus_id', $campus_id);
    $query = $ci->db->get();
    $students = $query->result_array();
    $student_ids = array_column($students, 'student_id');

    if (empty($student_ids)) {
        return [
            'total_unpaid' => 0,
            'paid_today' => 0,
            'discounted_today' => 0,
            'balance' => 0
        ];
    }

    // Calculate Total Unpaid (all unpaid fees)
    $ci->db->select_sum('(amount - discount)', 'total_unpaid');
    $ci->db->from('fee_chalan');
    $ci->db->where_in('student_id', $student_ids);
    $ci->db->where('status', 'UnPaid');
    $total_unpaid_result = $ci->db->get()->row();
    $total_unpaid = $total_unpaid_result->total_unpaid ?? 0;

    // Calculate Paid Today (paid on current date)
    $ci->db->select_sum('(amount - discount)', 'paid_today');
    $ci->db->from('fee_chalan');
    $ci->db->where_in('student_id', $student_ids);
    $ci->db->where('status', 'paid');
    $ci->db->where('paid_date', $current_date);
    $paid_today_result = $ci->db->get()->row();
    $paid_today = $paid_today_result->paid_today ?? 0;

    // Calculate Discounted Today (discounted on current date)
    $ci->db->select_sum('(amount - discount)', 'discounted_today');
    $ci->db->from('fee_chalan');
    $ci->db->where_in('student_id', $student_ids);
    $ci->db->where('status', 'discounted');
    $ci->db->where('paid_date', $current_date);
    $discounted_today_result = $ci->db->get()->row();
    $discounted_today = $discounted_today_result->discounted_today ?? 0;

    // Calculate Balance (total_unpaid - today's payments and discounts)
    $balance = $total_unpaid - ($paid_today + $discounted_today);

    return [
        'total_unpaid' => $total_unpaid + $paid_today,
        'paid_today' => $paid_today,
        'discounted_today' => $discounted_today,
        'balance' => $balance
    ];
}

private function generate_modals_and_js($ci, $students, $campus_id, $session_id, $schoolinfo, $parent_id, $summaries) {
    $modals_js = '<div id="payAdvanceFee" class="modal fade" role="dialog">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Advance Fee</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body"><form id="AdvanceFee"><div class="row">';

    foreach ($students as $student) {
        $advanceFee = $ci->db->query("SELECT amount FROM fee_chalan 
            WHERE student_id={$student->student_id} 
            AND fee_type_id=(SELECT fee_type_id FROM fee_type WHERE s_flag=1 AND fee_type_id=194)")
            ->row()->amount ?? 0;

        $modals_js .= '<div class="col-lg-6 mb-2">'.$student->first_name.' '.$student->last_name.' '.$student->class_name.'</div>
            <div class="col-lg-6 mb-2">
                <input type="hidden" class="studentIDs" value="'.$student->student_id.'" name="student_id[]">
                <input type="text" class="form-control discounts" value="'.$advanceFee.'" name="advance_amount[]">
            </div>';
    }

    $modals_js .= '</div></form></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="advFeePay" class="btn btn-primary">Pay Advance Fee</button>
            </div>
        </div></div>
    </div>

    <div id="updatediscount" class="modal fade" role="dialog">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header">   
                <h5 class="modal-title">Update Student Fee</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body"><form id="discountUpdate"><div class="row">';

    $totalFee = 0;
    foreach ($students as $student) {
       $feeInfo = $ci->db->query("
    SELECT amount 
    FROM fee_amount 
    WHERE campus_id = $campus_id 
      AND session_id = $session_id 
      AND fee_type_id = (
          SELECT fee_type_id 
          FROM fee_type 
          WHERE is_monthly_fee = 1 
            AND s_flag = 1 
            AND system_id = {$schoolinfo->system_id}
      ) 
      AND class_id = {$student->class_id}
")->row();

        if($feeInfo) {
            $totalFee += $feeInfo->amount - $student->discounted_amount;
            $modals_js .= '<div class="col-lg-6 mb-2">'.$student->first_name.' '.$student->last_name.' '.$student->class_name.'</div>
                <div class="col-lg-6 mb-2">
                    <input type="hidden" class="studentIDs" value="'.$student->student_id.'" name="student_id[]>
                    <input type="hidden" class="studentClassFee" value="'.$feeInfo->amount.'" name="student_class_fee[]>
                    <input type="text" class="form-control discounts" value="'.($feeInfo->amount - $student->discounted_amount).'" name="discounted_amount[]">
                </div>';
        }
    }

    $modals_js .= '<div class="col-lg-6">Total Fee</div><div class="col-lg-6">'.$totalFee.'</div>
            </div></form></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="discUpdate" class="btn btn-primary">Update</button>
            </div>
        </div></div>
    </div>

    <script>
    $(document).ready(function() {
        $("#advFeePay").click(function() {
            $.ajax({
                url: "admin.php?c=fee_chalan_pay&m=AdvFee",
                type: "POST",
                data: $("#AdvanceFee").serialize(),
                success: function(res) {
                    toastr.success("Updated Successfully");
                    $("#updatediscount").modal("hide");
                }
            });
        });

        $("#discUpdate").click(function() {
            $.ajax({
                url: "admin.php?c=students&m=updateDiscounts",
                type: "POST",
                data: $("#discountUpdate").serialize(),
                success: function(res) {
                    toastr.success("Updated Successfully");
                    $("#updatediscount").modal("hide");
                }
            });
        });

        // Payment handling JS
        $(".pay-button").on("click", function() {
            var chalanId = $(this).data("chalanid");
            var studentId = $(this).data("studentid");
            var feeAmount = $(this).data("feeamount");
            
            $("#payfee #ChalanID").val(chalanId);
            $("#payfee #studentID").val(studentId);
            $("#payfee #feeAmount").val(feeAmount);
        });
    });
    </script>';

    return $modals_js;
}

function get_studentinfo() {
    $campusid = $this->session->userdata('member_campusid');
    $search_term = $this->input->post('term')['term'];
    $cls_sec_id = $this->input->post('flag');

    $this->db->select('
        students.student_id,
        CONCAT(students.first_name, " ", COALESCE(students.last_name, "")) AS student_name,
        parents.f_name AS father_name,
        CONCAT(classes.class_name, " ", sections.section_name) AS section_name
    ');
    $this->db->from('students');
    $this->db->join('parents', 'parents.parent_id = students.parent_id', 'left');
    $this->db->join('student_class', 'student_class.student_id = students.student_id AND student_class.status = 1', 'left');
    $this->db->join('class_section', 'class_section.cls_sec_id = student_class.cls_sec_id', 'left');
    $this->db->join('classes', 'classes.class_id = class_section.class_id', 'left');
    $this->db->join('sections', 'sections.section_id = class_section.section_id', 'left');
    
    $this->db->group_start();
    $this->db->like('students.first_name', $search_term);
    $this->db->or_like('students.last_name', $search_term);
    $this->db->group_end();
    
    $this->db->where('students.status', 1);
    $this->db->where('students.campus_id', $campusid);

    if ($cls_sec_id && is_numeric($cls_sec_id)) {
        $this->db->where('student_class.cls_sec_id', $cls_sec_id);
    }

    $this->db->group_by('students.student_id');
    $query = $this->db->get();

    $data = [];
    foreach ($query->result_array() as $row) {
        $data[] = [
            'id' => $row['student_id'],
            'text' => "{$row['student_name']} c/o {$row['father_name']} {$row['section_name']}"
        ];
    }

    return json_response($data);
}

private function build_fee_list_html($ci, $students, $campus_info, $session_id, $summaries) {
    $html = '<table class="table table-bordered" style="width:100%;margin-bottom:20px;">
        <tr style="background: #367fa9;color: #fff;">
            <th>Student</th>
            <th>Fee Type</th>
            <th>Amount</th>
            <th>Operation</th>
        </tr>';

    foreach ($students as $student) {
        $html .= $this->build_student_row($ci, $student, $campus_info, $session_id);
    }

    $html .= sprintf('


        <tr>
            <td colspan="2"></td>
            <th>Total</th>
            <th>%s/-</th>
        </tr>
        <tr>
            <td colspan="2"></td>
            <th>Paid</th>
            <th>%s/-</th>
        </tr>
        <tr>
            <td colspan="2"></td>
            <th>Discount</th>
            <th>%s/-</th>
        </tr>
        <tr>
            <td colspan="2"></td>
            <th>Balance</th>
            <th>%s/-</th>
        </tr>
    </table>',


    number_format($summaries['total_unpaid']),
    number_format($summaries['paid_today']),
    number_format($summaries['discounted_today']),
    number_format($summaries['balance']));

    return $html;
}

private function build_student_row($ci, $student, $campus_info, $session_id) {
    $row_html = '';
    $fee_chalans = $ci->db->query("SELECT * FROM fee_chalan 
        WHERE student_id={$student->student_id} 
        AND status='unpaid'")->result();

    foreach ($fee_chalans as $chalan) {
        $fee_type = $ci->db->get_where('fee_type', ['fee_type_id' => $chalan->fee_type_id])->row();
        $fine = $this->calculate_late_fee($chalan, $campus_info);
        
        $row_html .= '<tr>
            <td>'.$this->get_student_photo($student).'</td>
            <td>
                '.$student->first_name.' '.$student->last_name.'<br>
                '.$student->class_name.'<br>
                '.$fee_type->fee_type_name.' of '.$this->format_fee_month($chalan->fee_month).'<br>
                Due: '.date("d M Y", strtotime($chalan->due_date)).'
            </td>
            <td>'.($chalan->amount - $chalan->discount).'/-</td>
            <td>
                <button class="btn btn-primary pay-button" 
                    data-toggle="modal" 
                    data-target="#payfee"
                    data-chalanid="'.$chalan->chalan_id.'"
                    data-studentid="'.$student->student_id.'"
                    data-feeamount="'.($chalan->amount - $chalan->discount).'">
                    Pay
                </button>
                <a class="btn btn-primary" 
                    href="/admin.php#/fee_chalan_single?m=add&id='.$student->student_id.'">
                    Generate Chalan
                </a>
            </td>
        </tr>';
    }

    return $row_html;
}

// Helper functions
private function calculate_late_fee($chalan, $campus_info) {
    if (date("m/Y") == $chalan->fee_month) {
        $date1 = new DateTime(date("Y-m-d"));
        $date2 = new DateTime($chalan->due_date);
        $diff = $date2->diff($date1)->format("%R%a");
        return $diff < 0 ? abs($diff * $campus_info->late_fee_fine) : 0;
    }
    return 0;
}

private function format_fee_month($fee_month) {
    if (!$fee_month) return '';
    list($month, $year) = explode('/', $fee_month);
    return date("M", mktime(0, 0, 0, $month, 1)) . ' ' . substr($year, -2);
}

private function get_student_photo($student) {
    if ($student->profile_photo && file_exists(FCPATH."uploads/".$student->profile_photo)) {
        return '<img style="width:50px;height:50px;border-radius:30px;margin:0 auto;" 
                src="'.base_url("uploads/".$student->profile_photo).'">';
    }
    return '<i class="fa fa-user" style="font-size:40px;display:block;text-align:center;"></i>';
}

function get_parentinfo() {
    $campusid = $this->session->userdata('member_campusid');
    $term = $this->input->post('term')['term'];

    $this->db->select('p.parent_id, p.f_name, GROUP_CONCAT(CONCAT(s.first_name, " ", s.last_name) SEPARATOR ", ") as children');
    $this->db->from('parents p');
    $this->db->join('students s', 's.parent_id = p.parent_id AND s.campus_id = p.campus_id', 'left');
    $this->db->like('p.f_name', $term, 'both');
    $this->db->where('p.campus_id', $campusid);
    $this->db->group_by('p.parent_id');
    $query = $this->db->get();

    $data = [];
    foreach ($query->result_array() as $row) {
        $data[] = [
            'id' => $row['parent_id'],
            'text' => $row['f_name'] . " (Children: " . $row['children'] . ")"
        ];
    }
    return json_response($data);
}

function add(){
		check_permission('admin-add-fee-chalan-pay');
		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;

		$this->load->view('fee_chalan_edit2', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-fee-chalan-pay');
		$chalan_type_id = intval($this->input->get('id'));
		
		$this->db->where('chalan_type_id', $chalan_type_id);
		$info = $this->db->get('chalan_type')->row();
		$this->template_data['info'] = $info;
		
		$fee_type_info = $this->db->get('fee_type')->result();
		$this->template_data['fee_type_info'] = $fee_type_info;
		$this->load->view('fee_chalan_edit2', $this->template_data);
	}

	

	function AdvFee(){
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		$paid_date = date('Y-m-d');
		$campusid = $this->session->userdata('member_campusid');
		
		$student_ids = $this->input->post('student_id');
		$advance_amount = $this->input->post('advance_amount');
	
	

	foreach ($student_ids as $key => $value) {
		
		$advance_amount_value =  $advance_amount[$key];

		$studentFeeInfo = $this->db->query('select * from fee_chalan WHERE student_id='.$value.' AND fee_type_id=(select fee_type_id from fee_type WHERE s_flag = 1 and fee_type_id=194)')->row(); 
	     	
		if($studentFeeInfo){
			$data = array(
				'amount' => $advance_amount_value,	
				'paid_date' => $paid_date,
				'updated_date' => $date,
				'user_id' => $user_id
			);
			$this->db->where('chalan_id', $studentFeeInfo->chalan_id);
			$this->db->update('fee_chalan', $data);
		}else{

			$data = array(
				'student_id' => $value,
				'fee_type_id' => 194,
				'amount' => $advance_amount_value,	
				'status' => 'paid',
				'paid_date' => $paid_date,
				'created_date' => $date,
				'user_id' => $user_id
			);

			$this->db->insert('fee_chalan', $data);

		}
		
		
	}
}

function sendSMS(){
	$this->load->library('parser');
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');
	$campusid = $this->session->userdata('member_campusid');
	$schoolinfo = getSchoolInfo();
	$schoolName = $schoolinfo->system_name;
	
	$parent_id = $this->input->post('parent_id');
	//$paid_date = DateTime::createFromFormat('d/m/Y',$this->input->post('datePaid'));
	//$paid_date = $paid_date->format('Y-m-d');

	$parentsInfo = $this->db->query('SELECT * from parents where parent_id='.$parent_id)->row();

	$FeeChalanInfo = $this->db->query('SELECT SUM(amount-discount)  AS total FROM fee_chalan WHERE student_id IN(SELECT student_id from students WHERE campus_id ='.$campusid.' AND parent_id='.$parent_id.' AND status=1) AND paid_date = "'.date('Y-m-d').'" AND status = "paid"')->row();

	if($FeeChalanInfo->total > 0 && $parentsInfo){
	 $message = 'Dear '.$parentsInfo->f_name.' your child Fee Rs '.$FeeChalanInfo->total.' has received.
    '.$schoolName;
	
		if($parentsInfo->father_contact){
			$mobile = $parentsInfo->father_contact;
		}else{
			$mobile = $parentsInfo->mother_contact;
		}
		
	if(!empty($mobile)){	
		$data = array(
			'mobile' => $mobile,
			'message' => trim($message),
			'campus_id' => trim($campusid),
			'parent_id' => $parentsInfo->parent_id,
			'status' => 0,
			'user_id' => $user_id, 
			'created_date' => $date
		);

		$this->db->insert('sms', $data);	

		json_response(array('success' => TRUE, 'msg' => 'Message Sent Successfully'));
	}
	}else{
		json_response(array('error' => TRUE, 'msg' => 'No Fee Paid Today'));
	}
	
}


function payFeeAll(){
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');
	$campusid = $this->session->userdata('member_campusid');
	
	$parent_id = $this->input->post('parent_id');
	//$datePaid = $this->input->post('datePaid');
	$paid_date = DateTime::createFromFormat('d/m/Y',$this->input->post('datePaid'));
	$paid_date = $paid_date->format('Y-m-d');

	$FeeChalanInfo = $this->db->query('SELECT * from fee_chalan where student_id IN(SELECT student_id from students WHERE parent_id='.$parent_id.' AND status=1) AND status="unpaid"')->result();

	foreach ($FeeChalanInfo as $key => $value) {
	
		$data = array(
		'status' => 'paid',
		'paid_date' => $paid_date,
		'updated_date' => $date,
		'user_id' => $user_id
		);

	$this->db->where('chalan_id', $value->chalan_id);
	$this->db->update('fee_chalan', $data);

	//print_r($this->db->error());
	
	
	}
	
}

function updatePaidFee(){
	$user_id = $this->session->userdata['member_userid'];
	$date = date('Y-m-d H:i:s');

	$data = array(
		'status' => 'unpaid',
		'updated_date' => $date,
		'user_id' => $user_id
	);
	$this->db->where('chalan_id', $this->input->post('challan_id'));
	$this->db->where('paid_date', date('Y-m-d'));
	$this->db->update('fee_chalan', $data);
}

}
// end this file