<?php

namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Admin\StudentsModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;
use DateTime;


/**
 * Students Management
 *
 * @author      Maqsood Ahmed
 * @copyright   Copyright (c) 2018-2019 TIME Soft Solutions
 * @email       maqsoodjamvi@gmail.com
 */
class Students extends BaseController
{
    use ResponseTrait;

    protected $studentsModel;
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'permission']);
        helper('school');
        //$this->studentsModel = model('StudentsModel');
       $this->studentsModel = new StudentsModel();
      //  $this->ParentModel = new ParentModel();

        $this->db = \Config\Database::connect();
        
        // Check permission
        check_permission('admin-students');
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        $currentrole = currentUserRoles();
        
        if(in_array(5, $currentrole)){
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = getAllClassSection();
        }

        $data['sectionsclassinfo'] = $sectionsclassinfo;
        return view('admin/students', $data);
    }

/**
 * Display readmit student form
 */


public function readmit()
{
    check_permission('admin-edit-student');
    
    $data['title'] = 'Readmit Student';
    $data['campus_id'] = session('member_campusid');
    $data['session_id'] = session('member_sessionid');
    
    // Get fee types for the form
    $schoolinfo = getSchoolInfo();
    $data['fee_types'] = $this->db->table('fee_type')
        ->where('system_id', $schoolinfo->system_id)
        ->where('status', 1)
        ->orderBy('is_monthly_fee', 'DESC')
        ->orderBy('fee_type_name', 'ASC')
        ->get()
        ->getResult();
    
    // Get class sections for selection
    $data['sectionsclassinfo'] = userClassSections();
    
    return view('admin/students/readmit', $data);
}

/**
 * Search for dropped students (status = 4)
 */


public function search_drop_students()
{
    $search = $this->request->getPost('search');
    $campus_id = $this->request->getPost('campus_id');
    $search_type = $this->request->getPost('search_type');
    
    // Debug log
    log_message('debug', 'Search term: ' . $search);
    log_message('debug', 'Campus ID: ' . $campus_id);
    log_message('debug', 'Search type: ' . $search_type);
    
    if (strlen($search) < 3) {
        return $this->response->setJSON(['success' => false, 'data' => []]);
    }
    
    // First, check if there are ANY dropped students in this campus
    $totalDropped = $this->db->table('students')
        ->where('campus_id', $campus_id)
        ->whereIn('status', [0, 4])
        ->countAllResults();
    
    log_message('debug', 'Total dropped students in campus: ' . $totalDropped);
    
    $query = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.leaving_date, s.leaving_reason, 
                  p.f_name as father_name, p.father_cnic')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->where('s.campus_id', $campus_id)
        ->whereIn('s.status', [0, 3,4,5]);
    
    // Convert search term to lowercase for case-insensitive search
    $search_lower = strtolower($search);
    
    // Use LOWER() for case-insensitive matching
    if ($search_type == 'father') {
        $query->where("LOWER(p.f_name) LIKE '%" . $this->db->escapeLikeString($search_lower) . "%'", null, false);
        log_message('debug', 'Searching father name: ' . $search);
    } else {
        $query->groupStart()
            ->where("LOWER(s.first_name) LIKE '%" . $this->db->escapeLikeString($search_lower) . "%'", null, false)
            ->orWhere("LOWER(s.last_name) LIKE '%" . $this->db->escapeLikeString($search_lower) . "%'", null, false)
            ->orWhere("LOWER(CONCAT(s.first_name, ' ', s.last_name)) LIKE '%" . $this->db->escapeLikeString($search_lower) . "%'", null, false)
        ->groupEnd();
        log_message('debug', 'Searching student name: ' . $search);
    }
    
    $results = $query->limit(15)->get()->getResult();
    
    // Get the SQL query for debugging
    $sql = $this->db->getLastQuery();
    log_message('debug', 'SQL: ' . $sql);
    log_message('debug', 'Results found: ' . count($results));
    
    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'student_id' => $row->student_id,
            'student_name' => trim($row->first_name . ' ' . $row->last_name),
            'reg_no' => $row->reg_no,
            'father_name' => $row->father_name,
            'leaving_date' => $row->leaving_date ? date('d/m/Y', strtotime($row->leaving_date)) : 'N/A',
            'leaving_reason' => $row->leaving_reason ?? 'N/A',
            'previous_class' => 'N/A'
        ];
    }
    
    return $this->response->setJSON([
        'success' => true, 
        'data' => $data,
        'debug_total' => $totalDropped,
        'debug_search_term' => $search
    ]);
}



/**
 * Get outstanding fee entries for a student
 */
public function get_outstanding_fee()
{
    $student_id = $this->request->getPost('student_id');
    $campus_id = session('member_campusid');
    
    // Get all unpaid fee challans
    $outstanding = $this->db->table('fee_chalan fc')
        ->select('fc.chalan_id, fc.fee_month, fc.amount, fc.discount, 
                  (fc.amount - fc.discount) as net_amount, fc.status, fc.payment_status,
                  ft.fee_type_name, ft.fee_type_id')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id')
        ->where('fc.student_id', $student_id)
        ->where('fc.status', 'unpaid')
        ->where('fc.campus_id', $campus_id)
        ->orderBy('fc.fee_month', 'ASC')
        ->get()
        ->getResultArray();
    
    // Get partial payment records for these fee entries
    foreach ($outstanding as &$fee) {
        $fee['partial_payments'] = $this->db->table('fee_payment_history')
            ->select('payment_id, amount_paid, payment_date, payment_method, transaction_id')
            ->where('student_id', $student_id)
            ->where('fee_month', $fee['fee_month'])
            ->where('fee_type_id', $fee['fee_type_id'])
            ->where('status', 'partial')
            ->orderBy('payment_date', 'ASC')
            ->get()
            ->getResultArray();
        
        $fee['total_paid'] = array_sum(array_column($fee['partial_payments'], 'amount_paid'));
        $fee['remaining'] = $fee['net_amount'] - $fee['total_paid'];
    }
    
    // Get total outstanding balance
    $total_outstanding = $this->db->table('fee_chalan')
        ->select('SUM(amount - discount) as total')
        ->where('student_id', $student_id)
        ->where('status', 'unpaid')
        ->get()
        ->getRow()
        ->total ?? 0;
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $outstanding,
        'total_outstanding' => $total_outstanding
    ]);
}

/**
 * Process fee payment (full, partial, or discount)
 */
public function process_fee_payment()
{
    $csrf_token = $this->request->getHeaderLine('X-CSRF-TOKEN');
    if ($csrf_token && $csrf_token !== csrf_hash()) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid CSRF token'
        ])->setStatusCode(403);
    }
    
    $json_input = $this->request->getJSON(true);
    if ($json_input) {
        $student_id = $json_input['student_id'] ?? null;
        $payment_type = $json_input['payment_type'] ?? 'full'; // full, partial, discount
        $fee_entries = $json_input['fee_entries'] ?? [];
        $payment_date = $json_input['payment_date'] ?? date('Y-m-d');
        $payment_method = $json_input['payment_method'] ?? 'cash';
        $remarks = $json_input['remarks'] ?? '';
    } else {
        $student_id = $this->request->getPost('student_id');
        $payment_type = $this->request->getPost('payment_type');
        $fee_entries = json_decode($this->request->getPost('fee_entries'), true);
        $payment_date = $this->request->getPost('payment_date');
        $payment_method = $this->request->getPost('payment_method');
        $remarks = $this->request->getPost('remarks');
    }
    
    $user_id = session('member_userid');
    $date = date('Y-m-d H:i:s');
    $campus_id = session('member_campusid');
    
    try {
        $this->db->transBegin();
        
        foreach ($fee_entries as $entry) {
            $chalan_id = $entry['chalan_id'];
            $fee_month = $entry['fee_month'];
            $fee_type_id = $entry['fee_type_id'];
            $original_amount = $entry['net_amount'];
            $paid_amount = $entry['paid_amount'] ?? 0;
            $discount_amount = $entry['discount_amount'] ?? 0;
            
            // Get current fee chalan
            $feeChalan = $this->db->table('fee_chalan')
                ->where('chalan_id', $chalan_id)
                ->get()
                ->getRow();
            
            if (!$feeChalan) {
                continue;
            }
            
            $current_paid = $this->db->table('fee_payment_history')
                ->select('SUM(amount_paid) as total_paid')
                ->where('student_id', $student_id)
                ->where('fee_month', $fee_month)
                ->where('fee_type_id', $fee_type_id)
                ->get()
                ->getRow()
                ->total_paid ?? 0;
            
            $remaining = ($feeChalan->amount - $feeChalan->discount) - $current_paid;
            
            if ($payment_type == 'discount') {
                // Apply discount to the remaining amount
                $new_discount = $feeChalan->discount + $discount_amount;
                $this->db->table('fee_chalan')
                    ->where('chalan_id', $chalan_id)
                    ->update([
                        'discount' => $new_discount,
                        'updated_date' => $date,
                        'user_id' => $user_id
                    ]);
                
                // Check if fully discounted
                if (($feeChalan->amount - $new_discount) <= 0) {
                    $this->db->table('fee_chalan')
                        ->where('chalan_id', $chalan_id)
                        ->update([
                            'status' => 'paid',
                            'payment_status' => 'completed',
                            'paid_date' => $payment_date,
                            'updated_date' => $date
                        ]);
                }
                
            } elseif ($payment_type == 'partial' || $payment_type == 'full') {
                // Record payment
                $this->db->table('fee_payment_history')->insert([
                    'student_id' => $student_id,
                    'fee_month' => $fee_month,
                    'fee_type_id' => $fee_type_id,
                    'amount_paid' => $paid_amount,
                    'amount_due' => $original_amount,
                    'payment_date' => $payment_date,
                    'payment_method' => $payment_method,
                    'transaction_id' => 'TXN_' . time() . '_' . $student_id,
                    'status' => ($remaining - $paid_amount) <= 0 ? 'paid' : 'partial',
                    'remarks' => $remarks,
                    'created_date' => $date,
                    'user_id' => $user_id
                ]);
                
                // Update fee chalan status if fully paid
                if (($remaining - $paid_amount) <= 0) {
                    $this->db->table('fee_chalan')
                        ->where('chalan_id', $chalan_id)
                        ->update([
                            'status' => 'paid',
                            'payment_status' => 'completed',
                            'paid_date' => $payment_date,
                            'updated_date' => $date
                        ]);
                }
            }
        }
        
        // Check if all fees are now paid
        $remaining_total = $this->db->table('fee_chalan')
            ->select('SUM(amount - discount) as total')
            ->where('student_id', $student_id)
            ->where('status', 'unpaid')
            ->get()
            ->getRow()
            ->total ?? 0;
        
        $this->db->transCommit();
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Payment processed successfully',
            'remaining_total' => $remaining_total,
            'all_cleared' => ($remaining_total <= 0)
        ]);
        
    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', 'Process Fee Payment Error: ' . $e->getMessage());
        
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ])->setStatusCode(500);
    }
}
/**
 * Get student details for readmission
 */
public function get_student_readmit_info()
{
    $student_id = $this->request->getPost('student_id');
    $campus_id = $this->request->getPost('campus_id');
    
    // Get student details
    $student = $this->db->table('students s')
        ->select('s.*, p.f_name as father_name, p.father_cnic, p.m_name, p.father_contact, p.address_line1')
        ->join('parents p', 'p.parent_id = s.parent_id')
        ->where('s.student_id', $student_id)
        ->get()
        ->getRow();
    
    if (!$student) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Student not found']);
    }
    
    // Get the MOST RECENT previous class (last entry by sc_id)
    $previous_class = $this->db->table('student_class sc')
        ->select('c.class_name, sec.section_name, cs.class_id, cs.cls_sec_id')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections sec', 'sec.section_id = cs.section_id')
        ->where('sc.student_id', $student_id)
        ->orderBy('sc.sc_id', 'DESC')
        ->limit(1)
        ->get()
        ->getRow();
    
    // Get outstanding fee balance if any
    $outstanding_fee = $this->db->table('fee_chalan')
        ->select('SUM(amount) as total_due')
        ->where('student_id', $student_id)
        ->where('status', 'unpaid')
        ->get()
        ->getRow();
    
    return $this->response->setJSON([
        'success' => true,
        'student' => $student,
        'previous_class' => $previous_class,
        'outstanding_balance' => $outstanding_fee->total_due ?? 0
    ]);
}

public function get_fee_history()
{
    $student_id = $this->request->getPost('student_id');
    $campus_id = $this->request->getPost('campus_id');
    
    // Get all academic sessions the student was enrolled in
    $student_sessions = $this->db->table('student_class sc')
        ->select('sc.session_id, ac.session_name, ac.start_date, ac.end_date, 
                  c.class_name, c.class_short_name, sec.section_name, sec.short_name as section_short_name')
        ->join('academic_session ac', 'ac.session_id = sc.session_id')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections sec', 'sec.section_id = cs.section_id')
        ->where('sc.student_id', $student_id)
        ->orderBy('ac.start_date', 'ASC')
        ->get()
        ->getResult();
    
    // Get all paid monthly fee records
    $payments = $this->db->table('fee_chalan fc')
        ->select('fc.*, ft.fee_type_name, ft.is_monthly_fee, fc.fee_month')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id')
        ->where('fc.student_id', $student_id)
        ->where('fc.status', 'paid')
        ->where('ft.is_monthly_fee', 1)
        ->where('ft.status', 1)
        ->orderBy('fc.fee_month', 'ASC')
        ->get()
        ->getResult();
    
    // Get non-monthly fee payments
    $non_monthly_payments = $this->db->table('fee_chalan fc')
        ->select('fc.*, ft.fee_type_name, ft.is_monthly_fee, 
                  DATE_FORMAT(fc.paid_date, "%Y-%m-%d") as paid_date')
        ->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id')
        ->where('fc.student_id', $student_id)
        ->where('fc.status', 'paid')
        ->where('ft.is_monthly_fee', 0)
        ->orderBy('fc.paid_date', 'ASC')
        ->get()
        ->getResult();
    
    // Months array
    $months = [
        '01' => 'Jan',
        '02' => 'Feb', 
        '03' => 'Mar',
        '04' => 'Apr',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'Aug',
        '09' => 'Sep',
        '10' => 'Oct',
        '11' => 'Nov',
        '12' => 'Dec'
    ];
    
    // Organize data by session
    $fee_history = [];
    $all_non_monthly_types = [];
    
    foreach ($student_sessions as $session) {
        $session_year = date('Y', strtotime($session->start_date));
        
        // Get class display name (use short_name if available)
        $class_display = !empty($session->class_short_name) ? $session->class_short_name : $session->class_name;
        $section_display = !empty($session->section_short_name) ? $session->section_short_name : $session->section_name;
        $class_section_display = $class_display . ($section_display ? ' - ' . $section_display : '');
        
        // Initialize monthly amounts array (12 months)
        $monthly_amounts = [];
        foreach ($months as $month_num => $month_name) {
            $monthly_amounts[$month_num] = 0;
        }
        
        // Fill in monthly payments for this session
        foreach ($payments as $payment) {
            $payment_year = substr($payment->fee_month, 0, 4);
            $payment_month = substr($payment->fee_month, 5, 2);
            
            if ($payment_year == $session_year) {
                $amount = round($payment->amount - $payment->discount);
                $monthly_amounts[$payment_month] += $amount;
            }
        }
        
        // Get non-monthly payments for this session
        $session_non_monthly = [];
        foreach ($non_monthly_payments as $payment) {
            $payment_year = date('Y', strtotime($payment->paid_date));
            if ($payment_year == $session_year) {
                $fee_type = $payment->fee_type_name;
                $amount = round($payment->amount - $payment->discount);
                $session_non_monthly[$fee_type] = ($session_non_monthly[$fee_type] ?? 0) + $amount;
                if (!in_array($fee_type, $all_non_monthly_types)) {
                    $all_non_monthly_types[] = $fee_type;
                }
            }
        }
        
        $fee_history[] = [
            'session_name' => $session->session_name,
            'session_year' => $session_year,
            'class_section' => $class_section_display,
            'monthly_amounts' => $monthly_amounts,
            'non_monthly' => $session_non_monthly
        ];
    }
    
    // Calculate totals (rounded)
    $total_paid = 0;
    $total_discount = 0;
    foreach ($payments as $payment) {
        $total_paid += round($payment->amount);
        $total_discount += round($payment->discount);
    }
    foreach ($non_monthly_payments as $payment) {
        $total_paid += round($payment->amount);
        $total_discount += round($payment->discount);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'fee_history' => $fee_history,
        'months' => $months,
        'non_monthly_types' => $all_non_monthly_types,
        'total_paid' => $total_paid,
        'total_discount' => $total_discount,
        'total_payments' => count($payments) + count($non_monthly_payments)
    ]);
}

/**
 * Process readmission
 */
public function process_readmission()
{
    // CSRF Check for JSON
    $csrf_token = $this->request->getHeaderLine('X-CSRF-TOKEN');
    if ($csrf_token && $csrf_token !== csrf_hash()) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid CSRF token'
        ])->setStatusCode(403);
    }

    log_message('debug', '=== PROCESS_READMISSION START ===');
    
    $user_id = session('member_userid');
    $date = date('Y-m-d H:i:s');
    $campus_id = session('member_campusid');
    $session_id = session('member_sessionid');
    
    // Handle JSON input
    $json_input = $this->request->getJSON(true);
    if ($json_input) {
        $student_id = $json_input['student_id'] ?? null;
        $cls_sec_id = $json_input['cls_sec_id'] ?? null;
        $readmission_date = $json_input['readmission_date'] ?? null;
        $fee_items = $json_input['fee_data'] ?? [];
    } else {
        $student_id = $this->request->getPost('student_id');
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $readmission_date = $this->request->getPost('readmission_date');
        $fee_data = $this->request->getPost('fee_data');
        $fee_items = json_decode($fee_data, true);
    }
    
    log_message('debug', 'Student ID: ' . $student_id);
    log_message('debug', 'Fee items count: ' . (is_array($fee_items) ? count($fee_items) : 0));
    
    // Parse readmission date
    $readmission_date_obj = !empty($readmission_date) ? 
        DateTime::createFromFormat('d/m/Y', $readmission_date) : 
        new DateTime();
    
    if (!$readmission_date_obj) {
        $readmission_date_obj = new DateTime();
    }
    $readmission_date_formatted = $readmission_date_obj->format('Y-m-d');
    $fee_month = $readmission_date_obj->format('Y-m');
    
    // Get class_id from cls_sec_id
    $class_id = null;
    if ($cls_sec_id) {
        $classSecInfo = $this->db->table('class_section')
            ->select('class_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->get()
            ->getRow();
        $class_id = $classSecInfo ? $classSecInfo->class_id : null;
    }
    
    // ========== CALCULATE MONTHLY DISCOUNT ==========
    $monthly_discount_amount = 0;
    $standard_monthly_fee = 0;
    $user_monthly_amount = 0;
    
    // Get the standard monthly fee for this class
    $schoolinfo = getSchoolInfo();
    $monthlyFeeType = $this->db->table('fee_type')
        ->select('fee_type_id')
        ->where('system_id', $schoolinfo->system_id)
        ->where('is_monthly_fee', 1)
        ->where('status', 1)
        ->get()
        ->getRow();
    
    if ($monthlyFeeType && $class_id) {
        $stdMonthlyFee = $this->db->table('fee_amount')
            ->select('amount')
            ->where('class_id', $class_id)
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('fee_type_id', $monthlyFeeType->fee_type_id)
            ->get()
            ->getRow();
        
        if ($stdMonthlyFee) {
            $standard_monthly_fee = (float)$stdMonthlyFee->amount;
            log_message('debug', 'Standard monthly fee: ' . $standard_monthly_fee);
        }
    }
    
    // Find the monthly fee amount entered by user and calculate discount
    if (is_array($fee_items) && count($fee_items) > 0) {
        foreach ($fee_items as $item) {
            $is_monthly = $item['is_monthly'] ?? false;
            $item_fee_type_id = (int)($item['fee_type_id'] ?? 0);
            
            // Check if this is monthly fee either by flag OR by matching fee_type_id
            $isMonthlyFee = $is_monthly || ($monthlyFeeType && $item_fee_type_id == $monthlyFeeType->fee_type_id);
            
            if ($isMonthlyFee) {
                $user_monthly_amount = (float)($item['amount'] ?? 0);
                $monthly_discount_amount = max(0, $standard_monthly_fee - $user_monthly_amount);
                log_message('debug', 'Found monthly fee item - Amount: ' . $user_monthly_amount . ', Discount: ' . $monthly_discount_amount);
                break;
            }
        }
    }
    
    log_message('debug', 'Standard monthly fee: ' . $standard_monthly_fee);
    log_message('debug', 'User entered monthly amount: ' . $user_monthly_amount);
    log_message('debug', 'Calculated monthly discount: ' . $monthly_discount_amount);
    // ========== END MONTHLY DISCOUNT CALCULATION ==========
    
    try {
        $this->db->transBegin();
        
        // Update student status to active AND store monthly discount
        $studentUpdateData = [
            'status' => 1,
            'leaving_date' => null,
            'leaving_reason' => null,
            'updated_date' => $date,
            'user_id' => $user_id
        ];
        
        // Update discounted_amount if there's a monthly discount
        if ($monthly_discount_amount > 0) {
            $studentUpdateData['discounted_amount'] = $monthly_discount_amount;
            log_message('debug', 'Setting discounted_amount to: ' . $monthly_discount_amount);
        } else {
            $studentUpdateData['discounted_amount'] = 0;
        }
        
        $this->db->table('students')
            ->where('student_id', $student_id)
            ->update($studentUpdateData);
        
        // ========== DELETE EXISTING SLC RECORDS FOR THIS STUDENT ==========
        // Check if SLC exists for this student and delete it
        $existingSlc = $this->db->table('school_leaving_certificates')
            ->where('student_id', $student_id)
            ->get()
            ->getRow();

        if ($existingSlc) {
            $deleteResult = $this->db->table('school_leaving_certificates')
                ->where('student_id', $student_id)
                ->delete();
            
            if ($deleteResult) {
                log_message('debug', 'Deleted existing SLC record for student ID: ' . $student_id . ' (SLC ID: ' . $existingSlc->id . ')');
            } else {
                log_message('debug', 'Failed to delete SLC record for student ID: ' . $student_id);
            }
        } else {
            log_message('debug', 'No existing SLC record found for student ID: ' . $student_id);
        }
        // ========== END SLC DELETION ==========
        
        // Update student_class for current session
        $existing = $this->db->table('student_class')
            ->where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->get()
            ->getRow();
        
        if ($existing) {
            $this->db->table('student_class')
                ->where('sc_id', $existing->sc_id)
                ->update([
                    'cls_sec_id' => $cls_sec_id,
                    'status' => 1,
                    'updated_date' => $date,
                    'user_id' => $user_id
                ]);
        } else {
            $this->db->table('student_class')->insert([
                'student_id' => $student_id,
                'session_id' => $session_id,
                'cls_sec_id' => $cls_sec_id,
                'status' => 1,
                'created_date' => $date,
                'user_id' => $user_id
            ]);
        }
        
        // ========== INVOICE LOGIC ==========
        // Check for existing invoice
        $invRes = $this->db->table('invoices')
            ->where('student_id', $student_id)
            ->where('fee_month', $fee_month)
            ->where('issue_date', $readmission_date_formatted)
            ->get();
        
        $existingInvoice = $invRes ? $invRes->getRow() : null;
        $invoice_no = $existingInvoice ? $existingInvoice->invoice_no : $this->generateInvoiceNumber($fee_month);
        
        if (!$existingInvoice) {
            $this->db->table('invoices')->insert([
                'student_id' => $student_id,
                'issue_date' => $readmission_date_formatted,
                'fee_month'  => $fee_month,
                'yr'         => date('y', strtotime($fee_month . '-01')),
                'invoice_no' => $invoice_no,
                'currency_code' => 'PKR',
                'exchange_rate' => 1.00000000,
                'grand_total_base' => 0,
                'grand_total_disp' => 0,
                'created_date' => $date,
                'updated_date' => $date,
                'user_id' => $user_id
            ]);
            log_message('debug', 'Invoice created: ' . $invoice_no);
        } else {
            log_message('debug', 'Using existing invoice: ' . $invoice_no);
        }
        
        // ========== FEE CHALAN INSERTION ==========
        $inserted_count = 0;
        
        if (is_array($fee_items) && count($fee_items) > 0) {
            foreach ($fee_items as $index => $item) {
                $fee_type_id = (int)($item['fee_type_id'] ?? 0);
                $amount = floatval($item['amount'] ?? 0);
                $discount = floatval($item['discount'] ?? 0);
                $item_fee_month = $item['fee_month'] ?? $fee_month;
                
                log_message('debug', 'Processing fee item - Type ID: ' . $fee_type_id . 
                           ', Amount: ' . $amount . 
                           ', Discount: ' . $discount);
                
                if (!$fee_type_id || $amount <= 0) {
                    log_message('debug', 'Skipping invalid fee item ' . ($index + 1));
                    continue;
                }
                
                // Check if already exists
                $exists = $this->db->table('fee_chalan')
                    ->where('student_id', $student_id)
                    ->where('fee_month', $item_fee_month)
                    ->where('fee_type_id', $fee_type_id)
                    ->where('invoice_no', $invoice_no)
                    ->countAllResults();
                
                if ((int)$exists > 0) {
                    log_message('debug', 'Fee chalan already exists for fee_type_id: ' . $fee_type_id);
                    continue;
                }
                
                // Parse dates
                $issue_date_str = $item['issue_date'] ?? $readmission_date;
                $due_date_str = $item['due_date'] ?? date('d/m/Y', strtotime('+10 days'));
                
                $issue_date = DateTime::createFromFormat('d/m/Y', $issue_date_str);
                $due_date = DateTime::createFromFormat('d/m/Y', $due_date_str);
                
                if (!$issue_date) $issue_date = $readmission_date_obj;
                if (!$due_date) $due_date = (new DateTime())->modify('+10 days');
                
                // Insert fee chalan
                $this->db->table('fee_chalan')->insert([
                    'student_id'     => $student_id,
                    'due_date'       => $due_date->format('Y-m-d'),
                    'issue_date'     => $issue_date->format('Y-m-d'),
                    'fee_month'      => $item_fee_month,
                    'fee_month_old'  => date('F Y', strtotime($item_fee_month . '-01')),
                    'amount'         => $amount,
                    'discount'       => $discount,
                    'status'         => 'unpaid',
                    'payment_status' => 'pending',
                    'fee_type_id'    => $fee_type_id,
                    'paid_date'      => '0000-00-00',
                    'created_date'   => $date,
                    'updated_date'   => $date,
                    'user_id'        => $user_id,
                    'acc_id'         => 0,
                    'currency_code'  => 'PKR',
                    'exchange_rate'  => 1.00000000,
                    'amount_base'    => $amount,
                    'invoice_no'     => $invoice_no
                ]);
                
                $inserted_count++;
                log_message('debug', 'Inserted fee chalan for fee_type_id: ' . $fee_type_id);
            }
        }
        
        // Update invoice grand total
        if ($inserted_count > 0) {
            $totalAmount = $this->db->table('fee_chalan')
                ->select('SUM(amount - discount) as total')
                ->where('student_id', $student_id)
                ->where('invoice_no', $invoice_no)
                ->get()
                ->getRow();
            
            if ($totalAmount && $totalAmount->total) {
                $this->db->table('invoices')
                    ->where('invoice_no', $invoice_no)
                    ->update([
                        'grand_total_base' => $totalAmount->total,
                        'grand_total_disp' => $totalAmount->total,
                        'updated_date' => $date
                    ]);
            }
        }
        
        $this->db->transCommit();
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Student readmitted successfully. ' . $inserted_count . ' fee entries created. Existing SLC has been removed.',
            'inserted_count' => $inserted_count,
            'invoice_no' => $invoice_no,
            'monthly_discount' => $monthly_discount_amount,
            'standard_monthly_fee' => $standard_monthly_fee,
            'user_monthly_amount' => $user_monthly_amount,
            'redirect' => site_url('admin/profile-student?id=' . $student_id)
        ]);
        
    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', 'Process Readmission Error: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ])->setStatusCode(500);
    }
}
/**
 * Generate invoice number (SAME AS YOUR EXISTING METHOD)
 * Format: YY-INV-XXXXX (e.g., 25-INV-00001)
 */
private function generateInvoiceNumber($fee_month)
{
    $db = \Config\Database::connect();

    // Validate fee_month format (YYYY-MM)
    if (empty($fee_month) || !preg_match('/^\d{4}-\d{2}$/', $fee_month)) {
        throw new \InvalidArgumentException('Invalid fee month format');
    }

    try {
        $feeDate = DateTime::createFromFormat('Y-m', $fee_month);
        if (!$feeDate) {
            throw new \RuntimeException('Invalid fee_month format: ' . $fee_month);
        }

        $yr = $feeDate->format('y'); // Last 2 digits of year (e.g., "25" for 2025)

        // Find the highest existing invoice number for this year
        $lastInvoice = $db->table('invoices')
            ->select('invoice_no')
            ->like('invoice_no', $yr . '-INV-', 'after')
            ->orderBy('invoice_no', 'DESC')
            ->get()
            ->getRow();

        if ($lastInvoice && $lastInvoice->invoice_no) {
            // Extract the numeric part and increment
            $parts = explode('-', $lastInvoice->invoice_no);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            // No invoices yet for this year - start from 1
            $nextNumber = 1;
        }

        // Format the invoice number with 5 digits (e.g., "25-INV-00001")
        $invoice_no = $yr . '-INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return $invoice_no;

    } catch (\Exception $e) {
        log_message('error', 'Invoice number generation failed: ' . $e->getMessage());
        throw new \RuntimeException('Failed to generate invoice number: ' . $e->getMessage());
    }
}


public function search_siblings()
{
    $search = $this->request->getPost('search');
    $campus_id = $this->request->getPost('campus_id');
    
    if (strlen($search) < 3) {
        return $this->response->setJSON(['success' => false, 'data' => []]);
    }
    
    $results = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.parent_id, p.father_cnic, p.f_name, p.m_name, p.father_contact, p.address_line1, p.city, c.class_name')
        ->join('parents p', 'p.parent_id = s.parent_id')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . session('member_sessionid'))
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', 1)
        ->groupStart()
            ->like('s.first_name', $search, 'both')
            ->orLike('s.last_name', $search, 'both')
            ->orLike('CONCAT(s.first_name, " ", s.last_name)', $search, 'both')
        ->groupEnd()
        ->limit(10)
        ->get()
        ->getResultArray();
    
    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'student_id' => $row['student_id'],
            'student_name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'parent_id' => $row['parent_id'],
            'father_cnic' => $row['father_cnic'] ?? '',
            'f_name' => $row['f_name'] ?? '',
            'm_name' => $row['m_name'] ?? '',
            'father_contact' => $row['father_contact'] ?? '',
            'address_line1' => $row['address_line1'] ?? '',
            'city' => $row['city'] ?? '',
            'class_name' => $row['class_name'] ?? ''
        ];
    }
    
    return $this->response->setJSON(['success' => true, 'data' => $data]);
}

public function get_parent_info()
{
    $father_cnic = $this->request->getPost('father_cnic');
    $parent_id = $this->request->getPost('parent_id');
    $campus_id = $this->request->getPost('campus_id');
    
    $query = $this->db->table('parents');
    
    if ($parent_id) {
        $query->where('parent_id', $parent_id);
    } elseif ($father_cnic) {
        $query->where('father_cnic', $father_cnic);
    } else {
        return $this->response->setJSON(['success' => false]);
    }
    
    $parent = $query->get()->getRowArray();
    
    if (!$parent) {
        return $this->response->setJSON(['success' => false]);
    }
    
    // Get existing children
    $children = $this->db->table('students s')
        ->select('CONCAT(s.first_name, " ", s.last_name) as name, c.class_name as class')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . session('member_sessionid'))
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->where('s.parent_id', $parent['parent_id'])
        ->where('s.campus_id', $campus_id)
        ->get()
        ->getResultArray();
    
    return $this->response->setJSON([
        'success' => true,
        'parent' => $parent,
        'children' => $children
    ]);
}


public function data()
{
    $campusid = session('member_campusid');
    $sessionid = session('member_sessionid');
    $status = $this->request->getGet('status');

    $filters = [
        'status'     => $status,
        'student_id' => $this->request->getPost('student_id'),
        'parent_id'  => $this->request->getPost('parent_id'),
        'cls_sec_id' => $this->request->getPost('cls_sec_id'),
        'session_id' => $sessionid,
        'campus_id'  => $campusid,
    ];

    $searchTerm  = $this->request->getPost('search')['value'] ?? '';
    $start       = $this->request->getPost('start') ?? 0;
    $length      = $this->request->getPost('length') ?? 10;
    $orderColumn = $this->request->getPost('order')[0]['column'] ?? null;
    $orderDir    = $this->request->getPost('order')[0]['dir'] ?? 'asc';

    $list = $this->studentsModel->getDatatables($filters, $searchTerm, $start, $length, $orderColumn, $orderDir);
    $recordsFiltered = $this->studentsModel->countFiltered($filters, $searchTerm, $orderColumn, $orderDir);
    $recordsTotal    = $this->studentsModel->countAll($campusid);

    $response = [];
    $no = $start;

    foreach ($list as $row) {
        $no++;
        $data = [];

        $total_discount = 0;
        $payable = 0;
        $projectedfee = 0;
        $className = '';
        $sectionName = '';
        $class_fee = '';

        $unpaid = $this->db->query('SELECT SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE status = "UnPaid" AND student_id ='.$row->student_id)->getRow();
        $discount = $this->db->query('SELECT SUM(discount) as total_discount FROM fee_chalan WHERE status = "UnPaid" AND student_id ='.$row->student_id)->getRow();

        if ($discount) $total_discount = $discount->total_discount;
        if ($unpaid) $payable = $unpaid->total;

        $studentclassinfo = ($status == 3)
            ? $this->db->query('SELECT * FROM student_class WHERE student_id = '.$row->student_id.' ORDER BY sc_id DESC')->getRow()
            : $this->db->table('student_class')->where('student_id', $row->student_id)->where('session_id', $sessionid)->get()->getRow();

        if ($studentclassinfo) {
            $classsectioninfo = $this->db->table('class_section')->where('cls_sec_id', $studentclassinfo->cls_sec_id)->get()->getRow();
            if ($classsectioninfo) {
                $classinfo = $this->db->table('classes')->where('class_id', $classsectioninfo->class_id)->get()->getRow();
                $sectioninfo = $this->db->table('sections')->where('section_id', $classsectioninfo->section_id)->get()->getRow();
                if ($sectioninfo) $sectionName = $sectioninfo->section_name;
                if ($classinfo) $className = $classinfo->class_name;

                $getclassfee = $this->db->query('SELECT * FROM fee_amount WHERE class_id='.$classsectioninfo->class_id.' AND fee_type_id IN (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1 AND s_flag=1) AND session_id='.$sessionid.' AND campus_id='.$campusid)->getRow();
                if ($getclassfee) {
                    $projectedfee = ($getclassfee->amount - $row->discounted_amount);
                    $class_fee = $getclassfee->amount;
                }
            }
        }

        $parentinfo = $this->db->table('parents')->where('parent_id', $row->parent_id)->get()->getRow();
        $data['f_name'] = $parentinfo->f_name ?? '';
        $data['father_cnic'] = $parentinfo->father_cnic ?? '';
        $data['parent_id'] = $parentinfo->parent_id ?? '';
        $data['address'] = $parentinfo->address_line1 ?? '';
        $data['contacts'] = isset($parentinfo)
            ? "F:".$parentinfo->father_contact."<br>M:".$parentinfo->mother_contact."<br>E:".$parentinfo->emergency_contact."<br>W:".$parentinfo->whatsapp
            : '';

        $data['sr_id'] = $no;
        $data['id'] = $row->student_id;

        $imgurl = FCPATH."uploads/".$row->profile_photo;
        $data['profile_photo'] = ($row->profile_photo && file_exists($imgurl))
            ? "<img style='width:50px;height:50px;text-align:center;display:block;border-radius:30px;margin:0 auto;' src='".base_url("uploads/".$row->profile_photo)."' >"
            : "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";

        $age = date_diff(date_create($row->date_of_birth), date_create('now'))->y;

        $data['reg_no'] = $row->reg_no;
        $data['name'] = $row->first_name." ".$row->last_name;
        $data['age'] = $age." Years"; 
        $data['gender'] = $row->gender;
        $data['std_cnic'] = $row->std_cnic;
        $data['class'] = $className . " (" . $sectionName . ")";
        $data['section'] = $sectionName;
        $data['payable'] = $payable;
        $data['class_fee'] = $class_fee;
        $data['discounted'] = $total_discount;
        $data['projectedfee'] = $projectedfee;

        $response[] = $data;
    }

    return $this->respond([
        "draw" => intval($this->request->getPost('draw')),
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "data" => $response,
    ]);
}

private function defaultAdmissionFields(): array
{
    return [
      // keep your essentials here (can be tuned)
      'reg_no','gr_no','date_of_admission',
      'full_name','date_of_birth','gender',
      'father_cnic','f_name','father_contact',
      'section_id','fee_month','fee_issue_date','fee_due_date'
    ];
}

// some fields you never want hidden (server safety)
private function lockedRequiredFields(): array
{
    return ['reg_no','full_name','gender','section_id','fee_issue_date','fee_due_date','fee_month'];
}

// GET (AJAX) – read prefs
public function getAdmissionPrefs(int $userId, int $campusId): array
{
    $db = \Config\Database::connect();
    $row = $db->table('user_form_prefs')
              ->where([
                 'user_id'   => $userId,
                 'campus_id' => $campusId,
                 'form_key'  => 'admission'
              ])->get()->getRow();

    $defaultsVisible = [
      // show everything by default (or list what you want always on)
      'reg_no','gr_no','gr_date','date_of_admission','full_name','date_of_birth','gender',
      'student_cnic','previous_school','previous_school_city','health_condition','major_injuries',
      'father_cnic','f_name','father_contact','father_email','father_occupation','father_office_address',
      'm_name','mother_contact','whatsapp_contact','address_line1','city','hear_source',
      'emergency_contact_person','emergency_contact','a_address',
      'section_id','fee_month','fee_issue_date','fee_due_date'
    ];

    $visible = $row ? json_decode($row->visible_json, true) : $defaultsVisible;
    if (!is_array($visible)) $visible = $defaultsVisible;

    // Optional per-user required list; otherwise empty
    $required = $row && $row->required_json ? json_decode($row->required_json, true) : [];

    return [
      'visible'  => array_values(array_unique($visible)),
      'required' => array_values(array_unique($required)),
    ];
}


// POST (AJAX) – save prefs
public function save_admission_prefs()
{
    $userId   = (int) session('member_userid');
    $campusId = (int) session('member_campusid');
    $formKey  = 'admission_form_v1';

    $posted = (array) $this->request->getPost('visible'); // array of field keys

    // ensure locked fields are always included
    $must = $this->lockedRequiredFields();
    $visible = array_values(array_unique(array_merge($posted, $must)));

    $payload = ['visible_json' => json_encode($visible)];
    $builder = $this->db->table('user_form_prefs');

    $exists = $builder->where(['user_id'=>$userId,'campus_id'=>$campusId,'form_key'=>$formKey])->get()->getRowArray();
    if ($exists) {
        $builder->where('id', $exists['id'])->update($payload);
    } else {
        $payload += ['user_id'=>$userId,'campus_id'=>$campusId,'form_key'=>$formKey];
        $builder->insert($payload);
    }

    return $this->response->setJSON(['ok'=>true,'visible'=>$visible]);
}
    public function add()
    {
        check_permission('admin-add-student');
        $schoolinfo = getSchoolInfo();
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');

        $campus_bill_info = $this->db->query('select * from campus_bills WHERE status=1 AND campus_id='.$campusid)->getRow();
        $max_student_limit = $campus_bill_info->max_students;

        $students_info = $this->db->query('select count(student_id) as studentTotal from students WHERE student_id IN(SELECT student_id from student_class WHERE status=1) AND campus_id='.$campusid)->getRow();
        $noOfstudent = $students_info->studentTotal;

        if($noOfstudent >= $max_student_limit) {
            $data['max_limit'] = '<div class="col-lg-12">Maximum Limit Exceeded</div>';
        } else {
            $data['max_limit'] = '';
        }

        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        $data['sessionData'] = $sessionData;
        
        $classesinfo = $this->db->table('classes')->get()->getResult();
        $data['classesinfo'] = $classesinfo;

        $fee_plans = $this->db->table('fee_plans')->get()->getResult();
        $data['fee_plans'] = $fee_plans;
        
        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()
            ->getRow();

        $sessionName = explode('-', $academic_session->session_name);
        $sessionYear = ($sessionName[1]-1);
        
        $last_row = $this->db->table('students')
            ->where('session_id', $sessionid)
            ->orderBy('student_id', 'desc')
            ->get()
            ->getRow();

        if($last_row) {
            $regArr = explode('-', $last_row->reg_no);
            $last_id = end($regArr) + 1;
        } else {
            $last_id = 1;
        }
        
        $reg_no = $sessionYear.'-'.$schoolinfo->reg_text.'-'.$last_id;
        $data['reg_no'] = $reg_no;

        if(empty($schoolinfo->reg_text)) {
            echo '<div style="min-height: 150px;text-align: center;padding-top: 20px;font-size: 18px;text-decoration: blink;color: red;"><div class="col-lg-12">Enter School Short Name in system profile</div>';
            echo "<a href='admin.php#/profile_system'>Click Here</a></div>";
            exit;
        }

        $currentrole = currentUserRoles();

        if(in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $data['sectionsclassinfo'] = $sectionsclassinfo;
        

        $attachementTypesInfo = $this->db->table('attachement_types')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)
            ->get()
            ->getResult();
        $data['attachementTypesInfo'] = $attachementTypesInfo;
        
        return view('admin/studentstabs/basic_info', $data);
    }



  public function edit()
{
    check_permission('admin-edit-student');

    // Accept id from ?id=..., POST, or /students/edit/{id}
    $id = (int) ($this->request->getGet('id')
        ?? $this->request->getVar('id')
        ?? service('uri')->getSegment(3));

    if ($id <= 0) {
        return redirect()->back()->with('error', 'Missing or invalid student id.');
    }

    $schoolinfo = getSchoolInfo();
    $campusid   = (int) session()->get('member_campusid');
    $sessionid  = (int) session()->get('member_sessionid');

    $data['sessionData'] = [
        'campusid'  => $campusid,
        'sessionid' => $sessionid,
    ];

    // ---- Student (scoped to campus when available)
    $qb = $this->db->table('students')->where('student_id', $id);
    if ($campusid) { $qb->where('campus_id', $campusid); }
    $info = $qb->get()->getRow();

    if (!$info) {
        return redirect()->back()->with('error', 'Student not found for this campus.');
    }

    // Back-compat and explicit names for partials
    $data['info']    = $info;
    $data['student'] = $info;

    // ---- Parent
    $parentsinfo = $this->db->table('parents')
        ->where('parent_id', (int) $info->parent_id)
        ->get()->getRow();

    $data['parentsinfo'] = $parentsinfo;
    $data['parent']      = $parentsinfo;

    // ---- Student class (for current session)
    $studentclassinfo = $this->db->table('student_class')
        ->where('student_id', $id)
        ->where('session_id', $sessionid)
        ->get()->getRow();

    $data['studentclassinfo'] = $studentclassinfo;

    // ---- Resolve class_id (for fee lookups)
    $classId  = null;
    $clsSecId = null;

    if ($studentclassinfo) {
        $clsSecId = (int) $studentclassinfo->cls_sec_id;
        $row = $this->db->table('class_section')->select('class_id')
            ->where('cls_sec_id', $clsSecId)->get()->getRow();
        $classId = $row ? (int) $row->class_id : null;
    } else {
        // Fallbacks from students row
        $classId  = $info->class_id ? (int) $info->class_id : null;
        $clsSecId = $info->cls_sec_id ? (int) $info->cls_sec_id : null;
        if (!$classId && $clsSecId) {
            $row = $this->db->table('class_section')->select('class_id')
                ->where('cls_sec_id', $clsSecId)->get()->getRow();
            $classId = $row ? (int) $row->class_id : null;
        }
    }

    // ---- Fee amounts (monthly & transport)
    $classesFee      = 0.0;
    $transportAmount = 0.0;

    if ($classId) {
        // Monthly fee type
        $monthlyType = $this->db->table('fee_type')->select('fee_type_id')
            ->where('system_id', $schoolinfo->system_id)
            ->where('is_monthly_fee', 1)
            ->where('s_flag', 1)
            ->get()->getRow();

        if ($monthlyType) {
            $row = $this->db->table('fee_amount')->select('amount')
                ->where([
                    'campus_id'   => $campusid,
                    'class_id'    => $classId,
                    'session_id'  => $sessionid,
                    'fee_type_id' => $monthlyType->fee_type_id,
                ])->get()->getRow();
            if ($row) { $classesFee = (float) $row->amount; }
        }

        // Transport fee type
        $transportType = $this->db->table('fee_type')->select('fee_type_id')
            ->where('system_id', $schoolinfo->system_id)
            ->where('is_transport_fee', 1)
            ->get()->getRow();

        if ($transportType) {
            $row = $this->db->table('fee_amount')->select('amount')
                ->where([
                    'campus_id'   => $campusid,
                    'class_id'    => $classId,
                    'session_id'  => $sessionid,
                    'fee_type_id' => $transportType->fee_type_id,
                ])->get()->getRow();
            if ($row) { $transportAmount = (float) $row->amount; }
        }
    }

    $data['classesfee']   = $classesFee;
    $data['transportfee'] = $transportAmount;

    // ---- Lists for dropdowns etc.
    $data['sectionsclassinfo']    = userClassSections();
    $data['classesinfo']          = $this->db->table('classes')->where('status', 1)->get()->getResult();
    $data['academic_sessioninfo'] = $this->db->table('academic_session')->get()->getResult();
    $data['attachementTypesInfo'] = $this->db->table('attachement_types')
        ->where('system_id', $schoolinfo->system_id)->get()->getResult();

    return view('admin/students_edit', $data);
}



  public function get_studentinfo()   // ← must be public
    {
        $campusid  = session('member_campusid') ?? $this->session->userdata('member_campusid');
        $sessionid = session('member_sessionid') ?? $this->session->userdata('member_sessionid');

        $status = (int) ($this->request->getPost('status') ?? 1);

        // normalize Select2 param
        $termRaw = $this->request->getPost('term') ?? $this->request->getPost('q');
        $term = is_array($termRaw) ? (string)($termRaw['term'] ?? '') : (string)($termRaw ?? '');
        $term = trim($term);

        $b = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, p.f_name AS father_name, sc.student_id AS sc_id')
            ->join('parents p', 'p.parent_id = s.parent_id', 'left')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = '.$this->db->escape($sessionid), 'left')
            ->where('s.campus_id', $campusid)
            ->where('s.status', $status)
            ->limit(20)
            ->orderBy('s.first_name','ASC');

        if ($term !== '') {
            $b->groupStart()->like('s.first_name', $term)->orLike('s.last_name', $term)->groupEnd();
        }

        $rows = $b->get()->getResult();
        $out = [];
        foreach ($rows as $r) {
            if (empty($r->sc_id)) continue; // keep behavior: only with class row
            $text = trim($r->first_name.' '.$r->last_name);
            if (!empty($r->father_name)) $text .= ' c/o '.$r->father_name;
            $out[] = ['id' => (int)$r->student_id, 'text' => $text];
        }

        return $this->response->setJSON($out);
    }

public function save_admission()
{
    helper(['form', 'text']);

    $user_id   = (int) session('member_userid');
    $sessionid = (int) session('member_sessionid');
    $campus_id = (int) session('member_campusid');
    $date      = date('Y-m-d H:i:s');
    $todayYmd  = date('Y-m-d');
    $db        = $this->db;

    try {
        // -------------------------------------------------------
        // 0) Backward-compat: old forms posting `section_id`
        //    Normalize to `cls_sec_id` before validation.
        // -------------------------------------------------------
        if ($this->request->getPost('cls_sec_id') === null && $this->request->getPost('section_id') !== null) {
            $post = $this->request->getPost();
            $post['cls_sec_id'] = $post['section_id'];
            $this->request->setGlobal('post', $post);
        }

        // -------------------------------------------------------
        // 1) Load per-user field preferences (visible/required)
        // -------------------------------------------------------
        if (method_exists($this, 'getAdmissionPrefs')) {
            $prefs = $this->getAdmissionPrefs($user_id, $campus_id);
        } else {
            $prefs = [
                'visible'  => [
                    'reg_no','gr_no','gr_date','date_of_admission','full_name','date_of_birth','gender',
                    'student_cnic','previous_school','previous_school_city','health_condition','major_injuries',
                    'father_cnic','f_name','father_contact','father_email','father_occupation','father_office_address',
                    'm_name','mother_contact','whatsapp_contact','address_line1','city','hear_source',
                    'emergency_contact_person','emergency_contact','a_address',
                    'cls_sec_id','fee_month','fee_issue_date','fee_due_date'
                ],
                'required' => []
            ];
        }
        $visible = array_flip((array) ($prefs['visible']  ?? []));
        $userReq = array_flip((array) ($prefs['required'] ?? []));
        
        // -------------------------------------------------------
        // 2) Build dynamic validation rules
        // -------------------------------------------------------
        $hardRequired = [
            // 'date_of_admission' removed from hard required
            'reg_no','full_name','gender','cls_sec_id',
            'date_of_birth','fee_issue_date','fee_due_date','fee_month'
        ];

        $baseRules = [
            // Row 1
            'reg_no'            => 'permit_empty|alpha_numeric_punct|max_length[50]',
            'gr_no'             => 'permit_empty|alpha_numeric_punct|max_length[50]',
            'gr_date'           => 'permit_empty|valid_date[d/m/Y]|max_length[10]',
            'date_of_admission' => 'permit_empty|valid_date[d/m/Y]|max_length[10]',

            // Row 2 - NEW: full_name instead of separate first/last
            'full_name'         => 'permit_empty|max_length[150]',
            'date_of_birth'     => 'permit_empty|valid_date[d/m/Y]|max_length[10]',
            'gender'            => 'permit_empty|in_list[male,female]',
            'student_cnic'      => 'permit_empty|regex_match[/^\d{5}-\d{7}-\d{1}$/]|max_length[15]',

            // Row 3
            'previous_school'        => 'permit_empty|max_length[150]',
            'previous_school_city'   => 'permit_empty|max_length[100]',
            'health_condition'       => 'permit_empty|max_length[150]',
            'major_injuries'         => 'permit_empty|max_length[150]',

            // Parent/Guardian
            'father_cnic'            => 'permit_empty|regex_match[/^\d{5}-\d{7}-\d{1}$/]|max_length[15]',
            'f_name'                 => 'permit_empty|max_length[120]',
            'father_contact'         => 'permit_empty|max_length[20]',
            'father_email'           => 'permit_empty|valid_email|max_length[150]',
            'father_occupation'      => 'permit_empty|max_length[120]',
            'father_office_address'  => 'permit_empty|max_length[200]',
            'm_name'                 => 'permit_empty|max_length[120]',
            'mother_contact'         => 'permit_empty|max_length[20]',
            'whatsapp_contact'       => 'permit_empty|max_length[20]',
            'address_line1'          => 'permit_empty|max_length[200]',
            'city'                   => 'permit_empty|max_length[100]',
            'hear_source'            => 'permit_empty|max_length[120]',
            'emergency_contact_person'=> 'permit_empty|max_length[120]',
            'emergency_contact'      => 'permit_empty|max_length[20]',
            'a_address'              => 'permit_empty|max_length[200]',

            // Fee & section
            'cls_sec_id'        => 'permit_empty|is_natural_no_zero',
            'fee_month'         => 'permit_empty|regex_match[/^\d{4}-(0[1-9]|1[0-2])$/]|max_length[7]',
            'fee_issue_date'    => 'permit_empty|valid_date[d/m/Y]|max_length[10]',
            'fee_due_date'      => 'permit_empty|valid_date[d/m/Y]|max_length[10]',
        ];

        $rules = [];
        foreach ($baseRules as $field => $rule) {
            $isHardReq = in_array($field, $hardRequired, true);
            $isUserReq = isset($userReq[$field]);

            if (!isset($visible[$field])) {
                $rules[$field] = preg_replace('/\brequired\b\|?/', '', $rule) ?: 'permit_empty';
                continue;
            }

            if ($isHardReq || $isUserReq) {
                $rule = ltrim($rule, '|');
                if (strpos($rule, 'required') === false) {
                    $rule = 'required|' . $rule;
                }
            } else {
                $rule = preg_replace('/\brequired\b\|?/', '', $rule) ?: 'permit_empty';
            }
            $rules[$field] = $rule;
        }

        // -------------------------------------------------------
        // 3) Validate BEFORE parsing dates
        // -------------------------------------------------------
        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Validation failed',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        // -------------------------------------------------------
        // 4) Safe date normalization + defaults when hidden/empty
        // -------------------------------------------------------
        $raw_issue = $this->request->getPost('fee_issue_date');
        $raw_due   = $this->request->getPost('fee_due_date');
        $raw_month = $this->request->getPost('fee_month');
        $create_invoice = ($raw_issue || $raw_due || $raw_month);

        $raw_doA = $this->request->getPost('date_of_admission');
        $date_of_admission = $this->parseDateStrict('Admission Date', $raw_doA, true);
        if (!isset($visible['date_of_admission']) || empty($date_of_admission)) {
            $date_of_admission = $todayYmd;
        }

        $raw_grd = $this->request->getPost('gr_date');
        $gr_date = $this->parseDateStrict('G.R. Date', $raw_grd, true);
        if (!isset($visible['gr_date']) || empty($gr_date)) {
            $gr_date = $todayYmd;
        }

        $date_of_birth = $this->parseDateStrict('Date of Birth', $this->request->getPost('date_of_birth'));

        $fee_issue_date = $create_invoice ? $this->parseDateStrict('Fee Issue Date', $raw_issue) : null;
        $fee_due_date   = $create_invoice ? $this->parseDateStrict('Fee Due Date',   $raw_due)   : null;
        $fee_month      = $create_invoice ? $this->parseMonthStrict('Fee Month',     $raw_month) : null;

        $cls_sec_id   = (int) $this->request->getPost('cls_sec_id');
        $father_cnic  = trim((string) $this->request->getPost('father_cnic'));
        $posted_gr_no = trim((string) $this->request->getPost('gr_no'));
        
        // -------------------------------------------------------
        // NEW: Split full_name into first_name and last_name
        // -------------------------------------------------------
        $full_name = trim((string) $this->request->getPost('full_name'));
        $first_name = '';
        $last_name = '';
        
        if (!empty($full_name)) {
            $nameParts = explode(' ', $full_name, 2);
            $first_name = $nameParts[0];
            $last_name = isset($nameParts[1]) ? $nameParts[1] : '';
        }

        // -------------------------------------------------------
        // Resolve class_id (from selected cls_sec_id) and verify campus
        // -------------------------------------------------------
        $row = $db->table('class_section')
                  ->select('class_id, campus_id')
                  ->where('cls_sec_id', $cls_sec_id)
                  ->get()->getRow();

        if (!$row) {
            throw new \Exception('Invalid class section selected');
        }
        if ((int)$row->campus_id !== $campus_id) {
            throw new \Exception('Selected class-section does not belong to this campus');
        }
        $class_id = (int) $row->class_id;
        if (!$class_id) {
            throw new \Exception('Class not linked to the selected class-section');
        }

        // -------------------------------------------------------
        // 5) Single TX on the SAME connection
        // -------------------------------------------------------
        $db->transException(true)->transStart();

        // Parent
        $parent_id  = $this->handleParentData(
            $father_cnic, $campus_id, $user_id, $date
        );

        // Student - NOW PASSING first_name and last_name
        $student_id = $this->handleStudentData(
            $parent_id, 
            $campus_id, 
            $class_id, 
            $cls_sec_id, 
            $sessionid,
            $date_of_admission, 
            $date_of_birth, 
            $gr_date, 
            $user_id, 
            $date,
            $first_name,   // Add this
            $last_name     // Add this
        );

        // Default gr_no = 0 when hidden or empty
        if (!isset($visible['gr_no']) || $posted_gr_no === '' || $posted_gr_no === null) {
            $db->table('students')->where('student_id', $student_id)->update(['gr_no' => 0]);
        }

        // Student-class map
        $this->handleStudentClass($student_id, $sessionid, $cls_sec_id, $user_id, $date);

        // Fees / invoice
        if ($create_invoice) {
            $result = $this->handleInvoiceAndFee(
                $db,
                $student_id,
                $class_id,
                $campus_id,
                $sessionid,
                $fee_issue_date,
                $fee_due_date,
                $fee_month,
                $user_id,
                $date
            );

            if (empty($result['success'])) {
                throw new \Exception(
                    'Failed to create invoice/fee rows: ' .
                    (!empty($result['reason']) ? $result['reason'] : 'unknown') .
                    (!empty($result['stats'])  ? ' | stats=' . json_encode($result['stats']) : '')
                );
            }
        }

        $db->transComplete();

        // -------------------------------------------------------
        // 6) Generate Chalan URL for the new student
        // -------------------------------------------------------
        $chalan_url = '';
        if ($student_id && $create_invoice) {
            $chalan_url = site_url('admin/fee-chalan/generate?' . http_build_query([
                'search' => $student_id,
                'view_type' => 'student_three_copy',
                'show_discount' => 'yes',
                'fee_month' => $fee_month ?? date('Y-m'),
                'show_payment_history' => 0,
                'fine_after_due_date' => 0
            ]));
        }

        // -------------------------------------------------------
        // 7) Response
        // -------------------------------------------------------
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'    => true,
                'msg'        => 'Student admission record saved successfully',
                'student_id' => $student_id,
                'chalan_url' => $chalan_url,
                'redirect'   => site_url('admin/students/add'),
            ]);
        }

        return redirect()->to(site_url('admin/students/add'))
                         ->with('success', 'Student admission record saved successfully')
                         ->with('chalan_url', $chalan_url);

    } catch (\Throwable $e) {
        if ($db->transStatus() === false) {
            try { $db->transRollback(); } catch (\Throwable $ignored) {}
        }
        $err = $db->error();
        log_message('error', 'save_admission failed: '.$e->getMessage().' | DBERR: '.json_encode($err));

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Error: '.$e->getMessage(). (!empty($err['code']) ? (' (DB '.$err['code'].': '.$err['message'].')') : '')
            ]);
        }

        return redirect()->back()->withInput()->with('error',
            'Error: '.$e->getMessage(). (!empty($err['code']) ? (' (DB '.$err['code'].': '.$err['message'].')') : '')
        );
    }
}


public function update_basicinfo()
{
    try {
        $student_id = $this->request->getPost('student_id');
        $parent_id = $this->request->getPost('parent_id');
        
        // Parse dates using existing method
        $gr_date = null;
        if (!empty($this->request->getPost('gr_date'))) {
            $gr_date = $this->parseDateStrict('GR Date', $this->request->getPost('gr_date'), true);
        }
        
        $date_of_admission = null;
        if (!empty($this->request->getPost('date_of_admission'))) {
            $date_of_admission = $this->parseDateStrict('Admission Date', $this->request->getPost('date_of_admission'), true);
        }
        
        $date_of_birth = null;
        if (!empty($this->request->getPost('date_of_birth'))) {
            $date_of_birth = $this->parseDateStrict('Date of Birth', $this->request->getPost('date_of_birth'), true);
        }
        
        // Update students table
        $this->db->table('students')->where('student_id', $student_id)->update([
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'gender' => $this->request->getPost('gender'),
            'gr_no' => $this->request->getPost('gr_no'),
            'gr_date' => $gr_date,
            'date_of_admission' => $date_of_admission,
            'date_of_birth' => $date_of_birth,
            'student_cnic' => $this->request->getPost('student_cnic'),
            'previous_school' => $this->request->getPost('previous_school'),
            'ps_city' => $this->request->getPost('ps_city'),
            'health_conditions' => $this->request->getPost('health_conditions'),
            'major_injuries' => $this->request->getPost('major_injuries'),
            'religion' => $this->request->getPost('religion'),
            'cls_sec_id' => $this->request->getPost('cls_sec_id'),
            'updated_date' => date('Y-m-d H:i:s')
        ]);
        
        // Update parents table
        $this->db->table('parents')->where('parent_id', $parent_id)->update([
            'father_cnic' => $this->request->getPost('father_cnic'),
            'f_name' => $this->request->getPost('f_name'),
            'father_contact' => $this->request->getPost('father_contact'),
            'father_email' => $this->request->getPost('father_email'),
            'father_occupation' => $this->request->getPost('father_occupation'),
            'father_office_address' => $this->request->getPost('father_office_address'),
            'm_name' => $this->request->getPost('m_name'),
            'mother_contact' => $this->request->getPost('mother_contact'),
            'whatsapp' => $this->request->getPost('whatsapp_contact'),
            'address_line1' => $this->request->getPost('address_line1'),
            'city' => $this->request->getPost('city'),
            'hear_source' => $this->request->getPost('hear_source'),
            'emergency_contact_person' => $this->request->getPost('emergency_contact_person'),
            'emergency_contact' => $this->request->getPost('emergency_contact'),
            'relationship' => $this->request->getPost('relationship'),
            'a_address' => $this->request->getPost('a_address'),
            'caste' => $this->request->getPost('caste'),
            'updated_date' => date('Y-m-d H:i:s')
        ]);
        
        // Update student_class table for current session
        $sessionid = (int) session('member_sessionid');
        $this->db->table('student_class')
            ->where('student_id', $student_id)
            ->where('session_id', $sessionid)
            ->update(['cls_sec_id' => $this->request->getPost('cls_sec_id')]);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'msg' => 'Student information updated successfully'
            ]);
        }
        
       return redirect()->to(site_url('admin/students/edit?id=' . $student_id))
                         ->with('success', 'Student information updated successfully');

    } catch (\Exception $e) {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
        }
        
        return redirect()->back()->with('error', $e->getMessage());
    }
}

private function parseDateStrict(string $label, $raw, bool $nullable = false): ?string
{
    $raw = trim((string)($raw ?? ''));
    if ($raw === '') {
        if ($nullable) return null;
        throw new \InvalidArgumentException("$label is required.");
    }

    // Reject MySQL zero date
    if ($raw === '0000-00-00') {
        if ($nullable) return null;
        throw new \InvalidArgumentException("$label is invalid (0000-00-00).");
    }

    // Accept several formats and normalize to Y-m-d
    $formats = ['d/m/Y','d-m-Y','Y-m-d','Y/m/d','m/d/Y'];
    foreach ($formats as $fmt) {
        $dt = \DateTime::createFromFormat($fmt, $raw);
        if ($dt) {
            $errs = \DateTime::getLastErrors();
            if (empty($errs['warning_count']) && empty($errs['error_count'])) {
                return $dt->format('Y-m-d');
            }
        }
    }

    // Fallback
    $ts = strtotime($raw);
    if ($ts !== false) return date('Y-m-d', $ts);

    throw new \InvalidArgumentException("$label has invalid date format: '$raw'.");
}

private function parseMonthStrict(string $label, $raw): string
{
    $raw = trim((string)($raw ?? ''));
    if ($raw === '') {
        throw new \InvalidArgumentException("$label is required.");
    }

    if (preg_match('/^\d{4}\-(0[1-9]|1[0-2])$/', $raw)) {
        return $raw; // already YYYY-MM
    }

    $try = ['Y-m','Y/m','m/Y','m-Y','F Y','M Y','d/m/Y','d-m-Y'];
    foreach ($try as $fmt) {
        $dt = \DateTime::createFromFormat($fmt, $raw);
        if ($dt) {
            $errs = \DateTime::getLastErrors();
            if (empty($errs['warning_count']) && empty($errs['error_count'])) {
                return $dt->format('Y-m');
            }
        }
    }

    $ts = strtotime($raw);
    if ($ts !== false) return date('Y-m', $ts);

    throw new \InvalidArgumentException("$label has invalid format: '$raw'. Use e.g. 2025-08 or Aug 2025.");
}   

private function normalizeFeeMonth(?string $in): string
{
    $in = trim((string)$in);

    // Already YYYY-MM?
    if (preg_match('/^\d{4}-\d{2}$/', $in)) {
        return $in;
    }

    // Try known patterns and convert to YYYY-MM
    $tryFormats = ['F Y', 'M Y', 'm/Y', 'm-Y', 'Y/m', 'Y-m', 'd/m/Y', 'd-m-Y'];
    foreach ($tryFormats as $fmt) {
        $dt = \DateTime::createFromFormat($fmt, $in);
        if ($dt) {
            return $dt->format('Y-m');
        }
    }

    // Fallback: let strtotime try
    $ts = strtotime($in);
    if ($ts) {
        return date('Y-m', $ts);
    }

    throw new \InvalidArgumentException('Invalid fee month format. Use e.g. "2025-08" or "Aug 2025".');
}

private function handleInvoiceAndFee(
    \CodeIgniter\Database\BaseConnection $db,
    int $student_id,
    int $class_id,
    int $campus_id,
    int $session_id,
    string $issue_date,
    string $due_date,
    string $fee_month,
    int $user_id,
    string $date,
    ?array $feeTypes = null,
    ?float $monthly_discount = null,
    int $std_type = 0
): array {
    $out = [
        'success' => false,
        'reason'  => '',
        'stats'   => [
            'feeTypes_count' => 0,
            'inserted'       => 0,
            'skipped_exists' => 0,
            'no_amount'      => 0,
            'non_positive'   => 0,
        ],
        'debug'   => [],
    ];

    try {
        // 0) Validate fee_month
        if (!is_string($fee_month) || !preg_match('/^\d{4}\-(0[1-9]|1[0-2])$/', $fee_month)) {
            throw new \InvalidArgumentException('Invalid fee_month: ' . (string) $fee_month);
        }

        // 1) Gather posted amounts (from the fee table in the view)
        $postedFeeTypeIds   = (array) $this->request->getPost('fee_type_id');
        $postedStudentAmts  = (array) $this->request->getPost('student_amount');
        $postedIsMonthlyArr = (array) $this->request->getPost('is_monthly');

        $postedMap = []; // fee_type_id => ['amount' => float, 'is_monthly' => bool]
        $n = max(count($postedFeeTypeIds), count($postedStudentAmts));
        for ($i = 0; $i < $n; $i++) {
            $ftid = (int) ($postedFeeTypeIds[$i] ?? 0);
            if ($ftid <= 0) continue;

            // sanitize to float
            $raw = (string) ($postedStudentAmts[$i] ?? '');
            $raw = preg_replace('/[^\d.\-]/', '', $raw);
            $studentAmt = (float) ($raw === '' ? 0 : $raw);

            $postedMap[$ftid] = [
                'amount'     => $studentAmt,
                'is_monthly' => !empty($postedIsMonthlyArr[$i]),
            ];
        }

        // 2) Auto-load fee types if not provided
        if ($feeTypes === null) {
            $system_id = (int) (getSchoolInfo()->system_id ?? 0);

            $feeTypes = $db->table('fee_type')
                ->select('fee_type_id, is_monthly_fee')
                ->where('system_id', $system_id)
                ->where('status', 1)
                ->where('s_flag', 1)
                ->where('std_type', 1)
                ->orderBy('is_monthly_fee', 'DESC')
                ->get()
                ->getResultArray() ?? [];

            $feeTypes = array_map(static function ($f) {
                return [
                    'fee_type_id'    => (int) ($f['fee_type_id'] ?? 0),
                    'is_monthly_fee' => !empty($f['is_monthly_fee']),
                    'plan_value'     => 1,
                ];
            }, $feeTypes);
        }

        $out['stats']['feeTypes_count'] = count($feeTypes);
        if ($out['stats']['feeTypes_count'] === 0) {
            $out['reason'] = 'No active fee types found for this system.';
            return $out;
        }

        // 3) Monthly discount default (legacy fallback)
        if ($monthly_discount === null) {
            $monthly_discount = (float) (
                $db->table('students')->select('discounted_amount')
                  ->where('student_id', $student_id)->get()->getRow()->discounted_amount ?? 0
            );
        }

        // 4) Ensure invoice exists (dedup by student+month+issue_date)
        $existingInvoice = $db->table('invoices')
            ->where('student_id', $student_id)
            ->where('fee_month',  $fee_month)
            ->where('issue_date', $issue_date)
            ->get()->getRow();

        $invoice_no = $existingInvoice ? $existingInvoice->invoice_no : null;
        if (!$existingInvoice) {
            $err = [];
            $attempts = 0;
            $maxAttempts = 5;
            do {
                $attempts++;
                $invoice_no = $this->generateInvoiceNumber($fee_month);

                $db->table('invoices')->insert([
                    'student_id'   => $student_id,
                    'issue_date'   => $issue_date,
                    'fee_month'    => $fee_month,
                    'yr'           => date('y', strtotime($fee_month . '-01')),
                    'invoice_no'   => $invoice_no,
                    'created_date' => $date,
                    'updated_date' => $date,
                ]);

                $err = $db->error();
                if (empty($err['code'])) {
                    break; // success
                }

                if ((int) $err['code'] === 1062) {
                    log_message('warning', "[invoices] DUP invoice_no={$invoice_no}, retry {$attempts}/{$maxAttempts}");
                    continue;
                } else {
                    log_message('error', "[DB][invoice_insert] code={$err['code']} msg={$err['message']}");
                    return ['success' => false, 'reason' => 'DB error on invoice insert (non-duplicate).'];
                }
            } while ($attempts < $maxAttempts);

            if (!empty($err['code']) && (int) $err['code'] === 1062) {
                return ['success' => false, 'reason' => 'Could not generate unique invoice number after retries.'];
            }
        }

        // 5) Insert fee_chalan rows
        foreach ($feeTypes as $fee) {
            $fee_type_id = (int) ($fee['fee_type_id'] ?? 0);
            if ($fee_type_id <= 0) { continue; }

            $isMonthly = !empty($fee['is_monthly_fee']);
            $pv        = max(1, (int) ($fee['plan_value'] ?? 1));

            // Skip duplicates
            $exists = $db->table('fee_chalan')
                ->where('student_id', $student_id)
                ->where('fee_month',  $fee_month)
                ->where('fee_type_id',$fee_type_id)
                ->where('invoice_no', $invoice_no)
                ->countAllResults();

            if ($exists) {
                $out['stats']['skipped_exists']++;
                continue;
            }

            // Base/class amount from setup
            $amountRow = $db->table('fee_amount')->select('amount')
                ->where('class_id',   $class_id)
                ->where('campus_id',  $campus_id)
                ->where('session_id', $session_id)
                ->where('fee_type_id',$fee_type_id)
                ->get()->getRow();

            if (!$amountRow) {
                $out['stats']['no_amount']++;
                $out['debug'][] = "No fee_amount for fee_type_id={$fee_type_id} class_id={$class_id} campus_id={$campus_id} session_id={$session_id}";
                continue;
            }

            $default_amount = (float) $amountRow->amount;
            // CHANGED: always compute base amount * plan_value for insertion
            $base_amount = $isMonthly ? ($default_amount * $pv) : $default_amount;

            // Prefer posted "student pay" amount if provided
            $postedAmt = $postedMap[$fee_type_id]['amount'] ?? null;

            if ($postedAmt !== null) {
                // CHANGED: user-entered = student pays; amount column should remain base/class fee
                $studentPays = max(0.0, (float)$postedAmt);
                $amountToInsert = $base_amount;                                       // <-- ALWAYS the configured class fee
                $discount       = max(0.0, $base_amount - $studentPays);              //     discount = base - studentPays
            } else {
                // Legacy fallback (no explicit user amount)
                $amountToInsert   = $base_amount;                                     // <-- keep class fee
                $perUnitDiscount  = (float) $monthly_discount;                        // legacy per-month discount
                $discount         = $isMonthly ? ($perUnitDiscount * $pv) : 0.0;
                if ($discount > $amountToInsert) $discount = $amountToInsert;        // cap
            }

            // CHANGED: positivity check should look at base amount, not user input
            if ($amountToInsert <= 0) {
                $out['stats']['non_positive']++;
                $out['debug'][] = "Non-positive base amount for fee_type_id={$fee_type_id} (base={$amountToInsert})";
                continue;
            }

            $db->table('fee_chalan')->insert([
                'student_id'     => $student_id,
                'due_date'       => $due_date,
                'issue_date'     => $issue_date,
                'fee_month'      => $fee_month,
                'fee_month_old'  => date('F Y', strtotime($fee_month . '-01')),
                'amount'         => $amountToInsert,     // CHANGED: insert class/base fee
                'discount'       => $discount,           // CHANGED: computed discount
                'status'         => 'Unpaid',
                'payment_status' => 'Pending',
                'fee_type_id'    => $fee_type_id,
                'paid_date'      => '0000-00-00',
                'created_date'   => $date,
                'updated_date'   => $date,
                'user_id'        => $user_id,
                'acc_id'         => 0,
                'currency_code'  => 'PKR',
                'invoice_no'     => $invoice_no,
            ]);

            if ($this->dbError($db, 'chalan_insert')) {
                $out['reason'] = 'DB error on fee_chalan insert.';
                return $out;
            }

            $out['stats']['inserted']++;
        }

        if ($out['stats']['inserted'] > 0) {
            $out['success'] = true;
            return $out;
        }

        // No rows inserted → explain
        if ($out['stats']['feeTypes_count'] === 0) {
            $out['reason'] = 'No fee types to process.';
        } elseif ($out['stats']['no_amount'] > 0) {
            $out['reason'] = 'No matching fee_amount configured for one or more fee types.';
        } elseif ($out['stats']['skipped_exists'] > 0) {
            $out['reason'] = 'All fee rows already existed (duplicates skipped).';
        } elseif ($out['stats']['non_positive'] > 0) {
            $out['reason'] = 'Calculated amount was non-positive for all rows.';
        } else {
            $out['reason'] = 'Nothing to insert (no eligible fee rows).';
        }

        log_message('error', 'handleInvoiceAndFee no-insert: ' . json_encode($out));
        return $out;

    } catch (\Throwable $e) {
        $out['reason'] = 'Exception: ' . $e->getMessage();
        log_message('error', 'handleInvoiceAndFee exception: ' . $e->getMessage());
        return $out;
    }
}



private function dbError(\CodeIgniter\Database\BaseConnection $db, string $tag): bool
{
    $err = $db->error();
    if (!empty($err['code'])) {
        log_message('error', "[DB][$tag] code={$err['code']} msg={$err['message']}");
        return true;
    }
    return false;
}


// private function generateInvoiceNumber(\CodeIgniter\Database\BaseConnection $db, string $fee_month): string
// {
//     if (!preg_match('/^\d{4}-\d{2}$/', $fee_month)) {
//         throw new \InvalidArgumentException('Invalid fee month format');
//     }

//     $yr   = date('y', strtotime($fee_month . '-01')); // '25'
//     $seed = 36000; // adjust to your baseline

//     // Try bump existing row atomically
//     $db->query(
//         "UPDATE invoice_sequences
//          SET last_sequence = LAST_INSERT_ID(last_sequence + 1)
//          WHERE yr = ?", [$yr]
//     );

//     if ($db->affectedRows() === 1) {
//         $row = $db->query("SELECT LAST_INSERT_ID() AS seq")->getRow();
//         if (!$row) {
//             throw new \RuntimeException('Sequence fetch failed');
//         }
//         return sprintf('%s-INV-%d', $yr, (int)$row->seq);
//     }

//     // Row for this year missing: create with seed; handle race by retrying update
//     try {
//         $db->query("INSERT INTO invoice_sequences (yr, last_sequence) VALUES (?, ?)", [$yr, $seed]);
//         return sprintf('%s-INV-%d', $yr, $seed);
//     } catch (\Throwable $e) {
//         // Someone else inserted – bump now
//         $db->query(
//             "UPDATE invoice_sequences
//              SET last_sequence = LAST_INSERT_ID(last_sequence + 1)
//              WHERE yr = ?", [$yr]
//         );
//         if ($db->affectedRows() !== 1) {
//             throw new \RuntimeException('Sequence update after race failed');
//         }
//         $row = $db->query("SELECT LAST_INSERT_ID() AS seq")->getRow();
//         if (!$row) throw new \RuntimeException('Sequence fetch after race failed');
//         return sprintf('%s-INV-%d', $yr, (int)$row->seq);
//     }
// }


// private function generateInvoiceNumber(string $fee_month): string
// {
//     // Independent connection so it won't roll back with your main TX
//     $db = \Config\Database::connect(null, false); // shared=false => new connection

//     if (!preg_match('/^\d{4}-\d{2}$/', $fee_month)) {
//         throw new \InvalidArgumentException('Invalid fee month format');
//     }

//     $yr   = date('y', strtotime($fee_month . '-01'));  // '25'
//     $seed = 36000; // start point for this year (adjust to your current max)

//     try {
//         // Try to atomically bump existing row
//         $db->query(
//             "UPDATE invoice_sequences
//              SET last_sequence = LAST_INSERT_ID(last_sequence + 1)
//              WHERE yr = ?", [$yr]
//         );

//         if ($db->affectedRows() === 1) {
//             $row = $db->query("SELECT LAST_INSERT_ID() AS seq")->getRow();
//             if (!$row) throw new \RuntimeException('Sequence fetch failed');
//             return sprintf('%s-INV-%d', $yr, (int)$row->seq);
//         }

//         // First time this year: create row with seed
//         try {
//             $db->query(
//                 "INSERT INTO invoice_sequences (yr, last_sequence) VALUES (?, ?)",
//                 [$yr, $seed]
//             );
//             return sprintf('%s-INV-%d', $yr, $seed);
//         } catch (\Throwable $e) {
//             // Race: someone else inserted first → bump now
//             $db->query(
//                 "UPDATE invoice_sequences
//                  SET last_sequence = LAST_INSERT_ID(last_sequence + 1)
//                  WHERE yr = ?", [$yr]
//             );
//             if ($db->affectedRows() !== 1) {
//                 throw new \RuntimeException('Sequence update after race failed');
//             }
//             $row = $db->query("SELECT LAST_INSERT_ID() AS seq")->getRow();
//             if (!$row) throw new \RuntimeException('Sequence fetch after race failed');
//             return sprintf('%s-INV-%d', $yr, (int)$row->seq);
//         }
//     } catch (\Throwable $e) {
//         log_message('error', 'Invoice number generation failed: ' . $e->getMessage());
//         throw new \RuntimeException('Failed to generate invoice number');
//     }
// }



public function get_class_fee_amounts()
{
    $cls_sec_id = $this->request->getPost('cls_sec_id');
    $campus_id = session('member_campusid');
    $session_id = session('member_sessionid');
    $system_id = getSchoolInfo()->system_id;

    // Get class_id from class_section
    $class_id = $this->db->table('class_section')
        ->select('class_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->get()
        ->getRow()
        ->class_id;

    if (!$class_id) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Class not found for the selected section'
        ]);
    }

    // Debugging log
    log_message('debug', 'debug fee campus_id: ' . $campus_id);
    log_message('debug', 'debug fee session_id: ' . $session_id);
    log_message('debug', 'debug fee system_id: ' . $system_id);
    log_message('debug', 'debug fee class_id: ' . ($class_id ?? 'NULL'));
    log_message('debug', 'debug fee cls_sec_id: ' . ($cls_sec_id ?? 'NULL'));


    // Get monthly fee type (only one should exist)
    $monthly_fee = $this->db->table('fee_type')
        ->select('fee_type_id, fee_type_name as fee_type_title')
        ->where([
            'system_id' => $system_id,
            'status' => 1,
            's_flag' => 1,
            'is_monthly_fee' => 1
        ])
        ->get()
        ->getRowArray();

    // Get all other active fee types (non-monthly)
    $other_fee_types = $this->db->table('fee_type')
        ->select('fee_type_id, fee_type_name as fee_type_title')
        ->where([
            'system_id' => $system_id,
            'status' => 1,
            's_flag' => 1,
            'is_monthly_fee' => 0
        ])
        ->get()
        ->getResultArray();

    $fee_amounts = [];

    // Process monthly fee first
    if ($monthly_fee) {
        $amount = $this->db->table('fee_amount')
            ->select('amount')
            ->where([
                'class_id' => $class_id,
                'campus_id' => $campus_id,
                'session_id' => $session_id,
                'fee_type_id' => $monthly_fee['fee_type_id']
            ])
            ->get()
            ->getRow();

        $fee_amounts[] = [
            'fee_type_id' => $monthly_fee['fee_type_id'],
            'fee_type_title' => $monthly_fee['fee_type_title'],
            'default_amount' => $amount ? $amount->amount : 0,
            'is_monthly' => true
        ];
    }

    // Process other fee types
    foreach ($other_fee_types as $fee) {
        $amount = $this->db->table('fee_amount')
            ->select('amount')
            ->where([
                'class_id' => $class_id,
                'campus_id' => $campus_id,
                'session_id' => $session_id,
                'fee_type_id' => $fee['fee_type_id']
            ])
            ->get()
            ->getRow();

        $fee_amounts[] = [
            'fee_type_id' => $fee['fee_type_id'],
            'fee_type_title' => $fee['fee_type_title'],
            'default_amount' => $amount ? $amount->amount : 0,
            'is_monthly' => false
        ];
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $fee_amounts
    ]);
}

private function handleParentData($father_cnic, $campus_id, $user_id, $date)
{
    // Check if parent exists
    $parent = $this->db->table('parents')
        ->where('father_cnic', $father_cnic)
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();

    if ($parent) {
        return $parent->parent_id;
    }

    // Create new parent
    $parent_data = [
        'father_cnic' => $father_cnic,
        'f_name' => trim($this->request->getPost('f_name')),
        'religion' => trim($this->request->getPost('religion')) ?? 'Islam',
        'father_contact' => trim($this->request->getPost('father_contact')) ?? '',
        'whatsapp' => trim($this->request->getPost('whatsapp_contact')) ?? '',
        'father_email' => trim($this->request->getPost('father_email')) ?? '',
        'father_occupation' => trim($this->request->getPost('father_occupation')) ?? '',
        'father_office_address' => trim($this->request->getPost('father_office_address')) ?? '',
        'm_name' => trim($this->request->getPost('m_name')) ?? '',
        'mother_contact' => trim($this->request->getPost('mother_contact')) ?? '',
        'address_line1' => trim($this->request->getPost('address_line1')) ?? '',
        'city' => trim($this->request->getPost('city')) ?? '',
        'hear_source' => trim($this->request->getPost('hear_source')) ?? '',
        'emergency_contact_person' => trim($this->request->getPost('emergency_contact_person')) ?? '',
        'relationship' => '',
        'a_address' => trim($this->request->getPost('a_address')) ?? '',
        'emergency_contact' => trim($this->request->getPost('emergency_contact')) ?? '',
        'password' => password_hash('123456', PASSWORD_BCRYPT),
        'campus_id' => $campus_id,
       'created_date' => $date,
        'updated_date' => $date,
        'user_id' => $user_id
    ];

    $this->db->table('parents')->insert($parent_data);
    return $this->db->insertID();
}

function addbulk(){

        check_permission('admin-add-student');
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->userdata('member_campusid');
        $sessionid = $this->session->userdata('member_sessionid');

        $campus_info = $this->db->query('select * from campus WHERE campus_id='.$campusid)->row();
        $this->template_data['campus_info'] = $campus_info;

        $campus_bill_info = $this->db->query('select * from campus_bills WHERE status=1 AND campus_id='.$campusid)->row();
        $max_student_limit = $campus_bill_info->max_students;
        
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

        $this->db->where('campus_id', $campusid);
        $campusInfo = $this->db->get('campus')->row();
        $this->template_data['campusInfo'] = $campusInfo;
        
        $sessionName = explode('-' , $academic_session->session_name);
         
        $sessionYear = ($sessionName[1]-1);
        
        //$this->db->where('session_id', $sessionid);
        //$this->db->order_by('student_id', 'desc');
        //$last_row = $this->db->get('students')->result();
        //$last_id = count($last_row)+1;

        $this->db->where('session_id', $sessionid);
        $this->db->order_by('student_id', 'desc');
        $last_row = $this->db->get('students')->row();
        //print_r($last_row);
        if($last_row){

            $regArr = explode('-' , $last_row->reg_no);
            $last_id = $regArr[2] + 1;

        }else{

            $last_id = 1;
        
        }

        $reg_no =  $sessionYear.'-'.$schoolinfo->reg_text.'-'.$last_id;
        
        //$reg_no =  $sessionYear.'-'.$schoolinfo->reg_text.'-'.$last_id;
        $this->template_data['reg_no'] = $reg_no;

        if(empty($schoolinfo->reg_text)){
            echo '<div class="col-lg-12">Reg Text Field is required in system profile</div>';
            echo "<a href='admin.php#/profile_system'>Click Here</a>";
            exit;   
        }

        $currentrole = currentUserRoles();

        if(in_array(5, $currentrole)){
            $sectionsclassinfo = teacherSubjectSections();
        }else{
            $sectionsclassinfo = userClassSections();
        }

        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;         
        
        $this->db->where('system_id', $schoolinfo->system_id);
        $attachementTypesInfo = $this->db->get('attachement_types')->result();
        $this->template_data['attachementTypesInfo'] = $attachementTypesInfo; 
        $this->load->view('students_editbulk', $this->template_data);
    
    }
private function handleStudentData($parent_id, $campus_id, $class_id, $cls_sec_id, $sessionid, 
                                  $date_of_admission, $date_of_birth, $gr_date, $user_id, $date)
{
    // Monthly Fee Discount Calculation
    $monthly_fee = $this->db->table('fee_amount')
        ->select('amount')
        ->join('fee_type', 'fee_type.fee_type_id = fee_amount.fee_type_id')
        ->where('fee_type.is_monthly_fee', 1)
        ->where('fee_amount.class_id', $class_id)
        ->where('fee_amount.campus_id', $campus_id)
        ->where('fee_amount.session_id', $sessionid)
        ->get()
        ->getRow()
        ->amount ?? 0;

    // Get all posted fee data
    $fee_type_ids = $this->request->getPost('fee_type_id') ?? [];
    $student_amounts = $this->request->getPost('student_amount') ?? [];
    $is_monthly_flags = $this->request->getPost('is_monthly') ?? [];

    // Find the monthly fee amount from the posted data
    $student_monthly_fee = $monthly_fee; // Default to full amount if not found
    foreach ($fee_type_ids as $index => $fee_type_id) {
        if (isset($is_monthly_flags[$index]) && $is_monthly_flags[$index] == '1') {
            $student_monthly_fee = (float) ($student_amounts[$index] ?? $monthly_fee);
            break; // Assuming only one monthly fee
        }
    }

    // Calculate discount (default amount - student amount)
    $discounted_amount = $monthly_fee - $student_monthly_fee;
    
    // Ensure discount is not negative
    if ($discounted_amount < 0) {
        $discounted_amount = 0;
    }

    // Handle profile photo
    $profile_photo = $this->request->getPost('image') ?? '';
    $image = $this->request->getFile('image');
    if ($image && $image->isValid() && !$image->hasMoved()) {
        $newName = $image->getRandomName();
        $image->move('./uploads/', $newName);
        $profile_photo = $newName;
    }

    // Get name data - your existing logic is already good
    $first_name = trim((string) $this->request->getPost('first_name'));
    $last_name  = trim((string) $this->request->getPost('last_name'));
    $full_name  = trim((string) $this->request->getPost('full_name'));

    // If first_name is empty but full_name exists, split full name
    // This handles the case when form submits full_name directly
    if (!$first_name && $full_name) {
        // Split full name into first + rest-as-last
        $parts = preg_split('/\s+/', $full_name, -1, PREG_SPLIT_NO_EMPTY);
        $first_name = $parts[0] ?? '';
        $last_name  = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }
    
    // If we still don't have a first name, use "Unknown" as fallback
    if (empty($first_name)) {
        $first_name = 'Unknown';
    }
    
    // Student data
    $student_data = [
        'reg_no' => trim($this->request->getPost('reg_no')),
        'first_name'        => $first_name,      // <- from above logic
        'last_name'         => $last_name,       // <- from above logic
        'std_cnic' => trim($this->request->getPost('student_cnic')) ?? '',
        'parent_id' => $parent_id,
        'gender' => trim($this->request->getPost('gender')) ?? '',
        'date_of_admission' => $date_of_admission,
        'date_of_birth' => $date_of_birth,
        'gr_no' => trim($this->request->getPost('gr_no')) ?? '',
        'gr_date' => $gr_date,
        'class_id' => $class_id,
        'cls_sec_id' => $cls_sec_id,
        'campus_id' => $campus_id,
        'session_id' => $sessionid,
        'discounted_amount' => $discounted_amount,
        'fee_plan' => (int) $this->request->getPost('fee_plan') ?? 1,
        'status' => 1, // Admission status
        's_flag' => 1,
        'a_flag' => 0,
        't_flag' => 0,
        'h_flag' => 0,
        'profile_photo' => $profile_photo,
        'previous_school' => trim($this->request->getPost('previous_school')) ?? '',
        'ps_city' => trim($this->request->getPost('ps_city')) ?? '',
        'major_injuries' => trim($this->request->getPost('major_injuries')) ?? '',
        'health_conditions' => trim($this->request->getPost('health_conditions')) ?? '',
        'std_type' => 1,
        'created_date' => $date,
        'updated_date' => $date,
        'user_id' => $user_id
    ];

    $this->db->table('students')->insert($student_data);
    return $this->db->insertID();
}


private function handleStudentClass($student_id, $sessionid, $cls_sec_id, $user_id, $date)
{
    $class_data = [
        'student_id' => $student_id,
        'session_id' => $sessionid,
        'cls_sec_id' => $cls_sec_id,
        'status' => 1, // Admission status
        'created_date' => $date,
        'updated_date' => $date,
        'user_id' => $user_id
    ];

    $this->db->table('student_class')->insert($class_data);
}

public function get_fee_amount()
{
    $section_id = $this->request->getPost('section_id');
    $campus_id = session('member_campusid');
    $session_id = session('member_sessionid');
    $school_info = getSchoolInfo();

    $classRow = $this->db->table('class_section')->select('class_id')->where('cls_sec_id', $section_id)->get()->getRow();
    if (!$classRow) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid section.']);
    }

    $feeRow = $this->db->query("
        SELECT amount FROM fee_amount 
        WHERE class_id = {$classRow->class_id}
        AND campus_id = {$campus_id}
        AND session_id = {$session_id}
        AND fee_type_id = (
            SELECT fee_type_id FROM fee_type 
            WHERE is_monthly_fee = 1 AND s_flag = 1 and system_id = {$school_info->system_id}
            LIMIT 1
        )
    ")->getRow();

    if ($feeRow) {
        return $this->response->setJSON(['success' => true, 'monthly_fee' => $feeRow->amount]);
    }

    return $this->response->setJSON(['success' => false, 'msg' => 'Fee not found.']);
}


public function getSibling()
{
    $strSibling = '';
    $schoolinfo = getSchoolInfo(); // Adjust if this is a global helper
    $campusid = session()->get('member_campusid');
    $sessionid = session()->get('member_sessionid');
    $parent_id = $this->request->getPost('parentID');

    $studentsinfo = $this->db->table('students')
        ->where('parent_id', $parent_id)
        ->get()
        ->getResult();

    if ($studentsinfo) {
        $strSibling .= '<table class="table"><tr><th>Name</th><th>Parent</th><th>Class</th><th>Student Fee</th></tr>';

        foreach ($studentsinfo as $value) {
            $parentsinfo = $this->db->table('parents')
                ->where('parent_id', $value->parent_id)
                ->get()
                ->getRow();

            $studentclassinfo = $this->db->table('student_class')
                ->where('student_id', $value->student_id)
                ->get()
                ->getRow();

            $className = $sectionName = $projectedfee = '';

            if ($studentclassinfo) {
                $classsectioninfo = $this->db->table('class_section')
                    ->where('cls_sec_id', $studentclassinfo->cls_sec_id)
                    ->get()
                    ->getRow();

                $classinfo = $sectionInfo = null;

                if ($classsectioninfo) {
                    $classinfo = $this->db->table('classes')
                        ->where('class_id', $classsectioninfo->class_id)
                        ->get()
                        ->getRow();

                    $sectionInfo = $this->db->table('sections')
                        ->where('section_id', $classsectioninfo->section_id)
                        ->get()
                        ->getRow();

                    if ($classinfo) {
                        $className = $classinfo->class_name;
                    }
                    if ($sectionInfo) {
                        $sectionName = $sectionInfo->section_name;
                    }

                    $getclassfee = $this->db->query(
                        'SELECT * FROM fee_amount WHERE class_id=? 
                            AND fee_type_id IN (
                                SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1 AND s_flag=1
                            ) 
                            AND session_id=? AND campus_id=?',
                        [$classsectioninfo->class_id, $sessionid, $campusid]
                    )->getRow();

                    if ($getclassfee) {
                        $projectedfee = ($getclassfee->amount - $value->discounted_amount);
                    }
                }
            }

            $strSibling .= '<tr>
                <td>' . esc($value->first_name . ' ' . $value->last_name) . '</td>
                <td>' . ($parentsinfo ? esc($parentsinfo->f_name) : '') . '</td>
                <td>' . $className . ' (' . $sectionName . ')</td>
                <td>' . $projectedfee . '/-</td>
            </tr>';
        }

        $strSibling .= '</table>';
    }

    return $this->response->setBody($strSibling);
}

   
public function check_parent_cnic()
{
    helper(['form', 'text']);
    $user_id = session('member_userid');
    $sessionid = session('member_sessionid');
    $campus_id = session('member_campusid');
    // Log session and request data for debugging
    log_message('debug', 'Session data: ' . print_r(session()->get(), true));
    log_message('debug', 'POST data: ' . print_r($this->request->getPost(), true));
    $db = \Config\Database::connect();
    
    // Validate AJAX request
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(405)->setJSON(['error' => 'Method Not Allowed']);
    }

    $builder = $db->table('parents');

    // Get input data
    $post = $this->request->getPost();
    $cnic = trim($this->request->getPost('cnic'));
    $campus_id = $post['campus_id'] ?? null;

    // Validate inputs
    if (empty($cnic)) {
        return $this->response->setStatusCode(400)->setJSON(['error' => 'CNIC is required']);
    }
    
    if (empty($campus_id)) {
        return $this->response->setStatusCode(400)->setJSON(['error' => 'Campus ID is required']);
    }
    
    // Convert to integer if needed
    if (!is_numeric($campus_id)) {
        $campus_id = (int) $campus_id;
    }

    // Validate CNIC format
    if (!preg_match('/^\d{5}-\d{7}-\d{1}$/', $cnic)) {
        return $this->response->setJSON([
            'exists' => false, 
            'message' => 'Invalid CNIC format. Valid format: XXXXX-XXXXXXX-X'
        ]);
    }

    try {
        // Clean CNIC format (remove dashes)
        $clean_cnic = $cnic;
        
        // Check if parent exists using Query Builder
        $parent = $builder->select('*')
                          ->where('father_cnic', $clean_cnic)
                          ->where('campus_id', $campus_id)
                          ->get()
                          ->getRow();

        if ($parent) {

            // Get current session ID (you may need to adjust this based on your application logic)
            $current_session_id = $sessionid; // Implement this method if needed
            
            // Get all children of this parent
            $children = $db->table('students')
                          ->select('students.student_id, students.first_name, students.last_name, students.discounted_amount, 
                                    classes.class_name, sections.section_name')
                          ->join('student_class', 'student_class.student_id = students.student_id')
                          ->join('class_section', 'class_section.cls_sec_id = student_class.cls_sec_id')
                          ->join('classes', 'classes.class_id = class_section.class_id')
                          ->join('sections', 'sections.section_id = class_section.section_id')
                          ->where('students.parent_id', $parent->parent_id)
                          ->where('students.campus_id', $campus_id)
                          ->where('student_class.session_id', $current_session_id)
                          ->where('students.status', '1') // Only active students
                          ->get()
                          ->getResult();

            // Calculate fee for each child
            $childrenWithFee = [];
            foreach ($children as $child) {
                // Get class fee
                $feeQuery = $db->query("
                    SELECT fa.amount 
                    FROM fee_amount fa
                    JOIN fee_type ft ON fa.fee_type_id = ft.fee_type_id
                    WHERE fa.campus_id = ?
                    AND fa.session_id = ?
                    AND ft.is_monthly_fee = 1 
                    AND ft.s_flag = 1
                    AND fa.class_id = (
                        SELECT cs.class_id 
                        FROM class_section cs
                        JOIN student_class sc ON sc.cls_sec_id = cs.cls_sec_id
                        WHERE sc.student_id = ?
                        AND sc.session_id = ?
                    )
                ", [$campus_id, $current_session_id, $child->student_id, $current_session_id]);

                $classFee = $feeQuery->getRow() ? $feeQuery->getRow()->amount : 0;
                
                // Calculate final fee (class fee - discount)
                $finalFee = $classFee - $child->discounted_amount;
                if ($finalFee < 0) $finalFee = 0;

                $childrenWithFee[] = [
                    'student_id' => $child->student_id,
                    'name' => $child->first_name . ' ' . $child->last_name,
                    'class' => $child->class_name . ' ' . $child->section_name,
                    'class_fee' => $classFee,
                    'discount' => $child->discounted_amount,
                    'final_fee' => $finalFee
                ];
            }

            return $this->response->setJSON([
                'exists' => true,
                'parent' => [
                    'parent_id' => $parent->parent_id,
                    'f_name' => $parent->f_name,
                    'father_contact' => $parent->father_contact,
                    'father_email' => $parent->father_email,
                    'father_occupation' => $parent->father_occupation,
                    'father_office_address' => $parent->father_office_address,
                    'm_name' => $parent->m_name,
                    'mother_contact' => $parent->mother_contact,
                    'whatsapp' => $parent->whatsapp,
                    'address_line1' => $parent->address_line1,
                    'city' => $parent->city,
                    'hear_source' => $parent->hear_source,
                    'emergency_contact_person' => $parent->emergency_contact_person,
                    'emergency_contact' => $parent->emergency_contact,
                    'a_address' => $parent->a_address,
                    'religion' => $parent->religion
                ],
                'children' => $childrenWithFee
            ]);
        }

        return $this->response->setJSON([
            'exists' => false,
            'message' => 'Parent not found in database'
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Parent CNIC check error: ' . $e->getMessage());
        return $this->response->setStatusCode(500)->setJSON([
            'error' => 'Server error while processing request: ' . $e->getMessage()
        ]);
    }
 }
  

}