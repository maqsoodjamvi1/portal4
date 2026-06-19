<?php

namespace App\Controllers\Admin;



use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TeacherQrModel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class Users extends BaseController
{
    protected $db;
    protected $session;
    protected $currentUserRole;
    protected $currentUserLevel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->session = session();
        helper(['form', 'url', 'role']);

        // Get current user's role and level
        $this->setCurrentUserRoleInfo();
    }

    private function requireAdminUsersPermission(bool $json = false): ?\CodeIgniter\HTTP\ResponseInterface
    {
        helper('permission');
        if (function_exists('hasPermission') && hasPermission('admin-users')) {
            return null;
        }

        if ($json) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'You do not have permission for this action.',
            ])->setStatusCode(403);
        }

        return redirect()->to(base_url('admin/dashboard'))
            ->with('error', 'You do not have permission to access this page.');
    }

    private function requireFullEditPermission(bool $json = false): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if (canFullEditEmployeeProfile()) {
            return null;
        }

        if ($json) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Only administrators can fully edit employee profiles.',
            ])->setStatusCode(403);
        }

        if (canSelfEditLimitedProfile()) {
            return redirect()->to(base_url('admin/profile/edit'))
                ->with('error', 'Use Update My Details to change your contact information.');
        }

        return redirect()->to(base_url('admin/dashboard'))
            ->with('error', 'You do not have permission to edit employee profiles.');
    }

    private function assertCanViewProfile(int $targetUserId): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if (canViewEmployeeProfile($targetUserId)) {
            return null;
        }

        return redirect()->to(base_url('admin/dashboard'))
            ->with('error', 'You do not have permission to view this profile.');
    }


    /**
 * Salary Management Tab
 */
public function salary($id)
{
    $id = (int) $id;
    if ($deny = $this->assertCanViewProfile($id)) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $user = $db->table('users')->where('id', $id)->get()->getRow();
    
    if (!$user) {
        return redirect()->to('admin/dashboard')->with('error', 'User not found');
    }
    
    $salaryModel = new \App\Models\SalaryModel();
    
    // Get current salary info
    $currentSalary = $user->basic_salary ?? 0;
    
    // Get salary history
    $salaryHistory = $salaryModel->getSalaryHistory($id);
    
    // Get salary slips
    $salarySlips = $salaryModel->getSalarySlips($id);
    
    // Get employee rules
    $employeeRules = $salaryModel->getEmployeeRules($id);
    
    // Get campus settings
    $campusSettings = $salaryModel->getCampusSettings($user->campus_id);
    
    return view('admin/user_views/users_view', $this->mergeUserViewSidebarData([
        'user' => $user,
        'currentSalary' => $currentSalary,
        'salaryHistory' => $salaryHistory,
        'salarySlips' => $salarySlips,
        'employeeRules' => $employeeRules,
        'campusSettings' => $campusSettings,
        'activeTab' => 'salary'
    ]));
}

/**
 * Update employee basic salary
 */
public function updateSalary()
{
    if ($deny = $this->requireFullEditPermission(true)) {
        return $deny;
    }

    helper(['permission', 'role']);
    $db = $this->db ?? \Config\Database::connect();
    $userId = $this->request->getPost('user_id');
    $newSalary = $this->request->getPost('basic_salary');
    $reason = $this->request->getPost('increment_reason');
    
    $user = $db->table('users')->where('id', $userId)->get()->getRow();
    if (!$user) {
        return $this->response->setJSON(['success' => false, 'msg' => 'User not found']);
    }
    
    $oldSalary = $user->basic_salary ?? 0;
    
    // Begin transaction
    $db->transStart();
    
    try {
        // Update user's basic salary
        $db->table('users')
            ->where('id', $userId)
            ->update([
                'basic_salary' => $newSalary,
                'updated_date' => date('Y-m-d H:i:s')
            ]);
        
        // Record in salary history
        $salaryModel = new \App\Models\SalaryModel();
        $salaryModel->recordIncrement(
            $userId,
            $user->campus_id,
            $oldSalary,
            $newSalary,
            $reason,
            session()->get('member_userid')
        );
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            throw new \Exception('Transaction failed');
        }
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Salary updated successfully'
        ]);
        
    } catch (\Exception $e) {
        $db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Save employee salary rules
 */
public function saveSalaryRules()
{
    if ($deny = $this->requireFullEditPermission(true)) {
        return $deny;
    }

    helper(['permission', 'role']);
    $db = $this->db ?? \Config\Database::connect();
    $userId = $this->request->getPost('user_id');
    $user = $db->table('users')->where('id', $userId)->get()->getRow();
    
    if (!$user) {
        return $this->response->setJSON(['success' => false, 'msg' => 'User not found']);
    }
    
    $salaryModel = new \App\Models\SalaryModel();
    
    $data = [
        'apply_deduction' => $this->request->getPost('apply_deduction') ? 1 : 0,
        'custom_daily_salary' => $this->request->getPost('custom_daily_salary') ?: null,
        'security_deduction_waived' => $this->request->getPost('security_deduction_waived') ? 1 : 0,
        'bonus_eligible' => $this->request->getPost('bonus_eligible') ? 1 : 0,
        'notes' => $this->request->getPost('notes')
    ];
    
    $result = $salaryModel->saveEmployeeRules(
        $userId,
        $user->campus_id,
        $data,
        session()->get('member_userid')
    );
    
    if ($result) {
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Salary rules saved successfully'
        ]);
    } else {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Failed to save salary rules'
        ]);
    }
}

/**
 * Legacy route alias — redirects to viewSalarySlip.
 */
public function salarySlip($id, $slipId)
{
    return redirect()->to(base_url('admin/users/view-salary-slip/' . $id . '/' . $slipId));
}

/**
 * Export salary slips for an employee as CSV.
 */
public function exportSalary($id)
{
    $id = (int) $id;
    helper('role');
    if (isSelfProfile($id) && ! canFullEditEmployeeProfile()) {
        return redirect()->to(base_url('admin/users/salary/' . $id))
            ->with('error', 'You do not have permission to export salary data.');
    }
    if ($deny = $this->requireAdminUsersPermission()) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $user = $db->table('users')->where('id', $id)->get()->getRow();

    if (!$user) {
        return redirect()->to('admin/users')->with('error', 'User not found');
    }

    $salaryModel = new \App\Models\SalaryModel();
    $slips = $salaryModel->getSalarySlips($id);

    $filename = 'salary-slips-' . preg_replace('/[^a-z0-9_-]+/i', '-', $user->username ?? (string) $id) . '.csv';

    $output = fopen('php://temp', 'r+');
    fputcsv($output, [
        'Slip No', 'Month', 'Year', 'Basic Salary', 'Net Salary',
        'Total Earnings', 'Total Deductions', 'Payment Status', 'Payment Date',
    ]);

    foreach ($slips as $slip) {
        fputcsv($output, [
            $slip->slip_no,
            $slip->month,
            $slip->year,
            $slip->basic_salary,
            $slip->net_salary,
            $slip->total_earnings,
            $slip->total_deductions,
            $slip->payment_status,
            $slip->payment_date ?? '',
        ]);
    }

    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);

    return $this->response
        ->setHeader('Content-Type', 'text/csv')
        ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
        ->setBody($csv);
}

/**
 * View salary slip
 */
public function viewSalarySlip($id, $slipId)
{
    $id = (int) $id;
    if ($deny = $this->assertCanViewProfile($id)) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $session = session();
    
    $user = $db->table('users')->where('id', $id)->get()->getRow();
    
    if (!$user) {
        return redirect()->to('admin/users')->with('error', 'User not found');
    }
    
    $salaryModel = new \App\Models\SalaryModel();
    $slip = $salaryModel->getSalarySlip($slipId);
    
    if (!$slip) {
        return redirect()->back()->with('error', 'Salary slip not found');
    }
    
    // Get school info
    $schoolinfo = $this->getDynamicSchoolInfo($user->campus_id);
    
    // Get logo separately (from system table)
    $finalLogo = '';
    $system_id = null;
    
    if ($user->campus_id) {
        $campus = $db->table('campus')
            ->select('system_id')
            ->where('campus_id', $user->campus_id)
            ->get()
            ->getRow();
        
        if ($campus && $campus->system_id) {
            $system_id = $campus->system_id;
            $system = $db->table('system')
                ->select('logo, system_name, address, short_name')
                ->where('system_id', $system_id)
                ->get()
                ->getRow();
            
            if ($system && $system->logo) {
                $finalLogo = $system->logo;
            }
            
            // Also get system info for the layout
            if (!isset($schoolinfo->system_id)) {
                $schoolinfo->system_id = $system_id;
            }
            if (!isset($schoolinfo->logo)) {
                $schoolinfo->logo = $finalLogo;
            }
        }
    }
    
    // Get attendance for the month
    $attendance = $db->table('attendance_employee')
        ->where('emp_id', $id)
        ->where('MONTH(date)', $slip->month)
        ->where('YEAR(date)', $slip->year)
        ->get()
        ->getResult();
    
    $presentCount = 0;
    $absentCount = 0;
    $lateCount = 0;
    
    foreach ($attendance as $att) {
        if ($att->status == 'present') $presentCount++;
        elseif ($att->status == 'absent') $absentCount++;
        elseif ($att->status == 'late') $lateCount++;
    }
    
    // Also fetch campus info for the layout if needed
    $campusInfo = $db->table('campus')
        ->select('campus_id, campus_name, location, short_name')
        ->where('campus_id', $user->campus_id)
        ->get()
        ->getRow();
    
    return view('admin/user_views/salary_slip', [
        'user' => $user,
        'slip' => $slip,
        'schoolinfo' => $schoolinfo,
        'finalLogo' => $finalLogo,
        'system' => $system ?? null,  // Pass system for layout
        'campus' => $campusInfo,      // Pass campus for layout
        'attendance' => [
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'total' => count($attendance)
        ]
    ]);
}

/**
 * Get dynamic school information
 * This method should be defined in your BaseController or Users controller
 */
/**
 * Get dynamic school information
 */
private function getDynamicSchoolInfo($campus_id = null)
{
    $db = \Config\Database::connect();
    $session = session();
    
    // Default school info
    $schoolinfo = (object)[
        'school_name' => 'School Management System',
        'system_name' => '',
        'address' => '',
        'phone' => '',
        'email' => '',
        'logo' => ''
    ];
    
    // Get campus_id from session if not provided
    if (!$campus_id) {
        $campus_id = $session->get('member_campusid');
    }
    
    if ($campus_id) {
        // Get campus info - NOTE: campus table does NOT have logo column
        $campus = $db->table('campus')
            ->select('campus_id, system_id, campus_name, location, landline, mobile_no, web_url')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();
        
        if ($campus) {
            $schoolinfo->school_name = $campus->campus_name;
            $schoolinfo->campus_name = $campus->campus_name;
            $schoolinfo->address = $campus->location;
            $schoolinfo->phone = $campus->landline ?? $campus->mobile_no;
            
            // Get system info (logo is in system table, not campus)
            if ($campus->system_id) {
                $system = $db->table('system')
                    ->select('system_name, address, landline_number, mob_number, logo')
                    ->where('system_id', $campus->system_id)
                    ->get()
                    ->getRow();
                
                if ($system) {
                    $schoolinfo->system_name = $system->system_name;
                    if (empty($schoolinfo->address)) {
                        $schoolinfo->address = $system->address;
                    }
                    if (empty($schoolinfo->phone)) {
                        $schoolinfo->phone = $system->landline_number ?? $system->mob_number;
                    }
                    // Logo is only in system table
                    $schoolinfo->logo = $system->logo ?? '';
                }
            }
        }
    }
    
    return $schoolinfo;
}
/**
 * Update payment status
 */
public function updatePaymentStatus()
{
    if ($deny = $this->requireFullEditPermission(true)) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $session = session();
    
    // Get POST data
    $slipId = $this->request->getPost('slip_id');
    $status = $this->request->getPost('payment_status');
    $paymentMethod = $this->request->getPost('payment_method');
    $transactionId = $this->request->getPost('transaction_id');
    $paymentDate = $this->request->getPost('payment_date');
    $bankName = $this->request->getPost('bank_name');
    $remarks = $this->request->getPost('remarks');
    
    // Debug logging
    log_message('debug', 'Update Payment Status called with slip_id: ' . $slipId);
    log_message('debug', 'Payment method: ' . $paymentMethod);
    
    // Validate input
    if (!$slipId) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid salary slip ID'
        ]);
    }
    
    if (!$status) {
        $status = 'paid'; // Default if not provided
    }
    
    // Check if slip exists
    $slip = $db->table('salary_slips')
        ->where('slip_id', $slipId)
        ->get()
        ->getRow();
    
    if (!$slip) {
        log_message('error', 'Salary slip not found: ' . $slipId);
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Salary slip not found'
        ]);
    }
    
    // If already paid, prevent duplicate
    if ($slip->payment_status == 'paid') {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'This salary slip is already marked as paid on ' . date('d-M-Y', strtotime($slip->payment_date))
        ]);
    }
    
    // Prepare payment details
    $paymentDetails = [
        'payment_method' => $paymentMethod,
        'transaction_id' => $transactionId,
        'remarks' => $remarks
    ];
    
    // Add bank name to remarks if provided
    if ($bankName) {
        $paymentDetails['remarks'] = ($remarks ? $remarks . "\n" : '') . "Bank: $bankName";
    }
    
    // Update payment status using the model
    try {
        $salaryModel = new \App\Models\SalaryModel();
        
        $result = $salaryModel->updatePaymentStatus(
            $slipId,
            $status,
            $paymentDetails
        );
        
        if ($result) {
            $campusId = (int) session('member_campusid');
            $paidFromAccount = (int) $this->request->getPost('paid_from_account_id');
            $finance = new \App\Libraries\CampusFinanceService($db);
            $netSalary = (float) ($slip->net_salary ?? 0);
            if ($finance->campusHasFinanceAccounts($campusId) && $netSalary > 0) {
                $finance->recordSalaryPayment(
                    (int) $slipId,
                    $campusId,
                    $netSalary,
                    $paidFromAccount,
                    (int) session('member_userid'),
                    'Salary slip #' . ($slip->slip_no ?? $slipId)
                );
            }

            $salaryModel->logPaymentTransaction(
                $slipId,
                $slip->user_id,
                $slip->net_salary,
                $paymentMethod,
                $transactionId,
                $paymentDetails['remarks']
            );
            
            return $this->response->setJSON([
                'success' => true,
                'msg' => 'Salary slip marked as paid successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Failed to update payment status'
            ]);
        }
        
    } catch (\Exception $e) {
        log_message('error', 'Payment update error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
    /**
 * Get available subjects for assignment
 */

    /**
 * Display QR code tab for employee
 */

    /**
 * Display QR code tab for employee
 */

    /**
 * Display QR code tab for employee
 */
public function qr($id)
{
    $db = $this->db ?? \Config\Database::connect();
    
    // Get user data
    $user = $db->table('users')->where('id', $id)->get()->getRow();
    
    if (!$user) {
        return redirect()->to('admin/users')->with('error', 'User not found');
    }
    
    // Set the active tab to 'qr'
    $tab = 'qr';
    
    // Prepare data array
    $data = [
        'user' => $user,
        'activeTab' => $tab,
        'tab' => $tab
    ];
    
    // Get QR code data if exists
    $qrData = $db->table('teacher_qr_codes')
        ->where('teacher_id', $id)
        ->get()
        ->getRow();
    
    // If QR code doesn't exist, generate it
    if (!$qrData) {
        $qr_string = 'TCHR_' . $user->id . '_' . md5($user->email . time());
        
        $db->table('teacher_qr_codes')->insert([
            'teacher_id' => $user->id,
            'qr_code' => $qr_string,
            'campus_id' => $user->campus_id,
            'generated_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]);
        
        $qrData = $db->table('teacher_qr_codes')
            ->where('teacher_id', $id)
            ->get()
            ->getRow();
    }
    
    $data['qr'] = $qrData;
    
    // Generate QR code image using the correct method for v6.0.9
    try {
        // Use constructor method (not create)
        $qrCode = new \Endroid\QrCode\QrCode($qrData->qr_code);
        
        // Set options if methods exist
        if (method_exists($qrCode, 'setSize')) {
            $qrCode->setSize(200);
        }
        if (method_exists($qrCode, 'setMargin')) {
            $qrCode->setMargin(10);
        }
        
        // Use writer to generate image
        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        
        // Convert to base64 for display in HTML
        $data['qr_image_base64'] = 'data:image/png;base64,' . base64_encode($result->getString());
        
    } catch (\Exception $e) {
        log_message('error', 'QR generation error in Users controller: ' . $e->getMessage());
        $data['qr_error'] = $e->getMessage();
    }
    
    // Get recent attendance for this teacher
    $data['recent_attendance'] = $db->table('attendance_employee')
        ->where('emp_id', $id)
        ->orderBy('date', 'DESC')
        ->limit(5)
        ->get()
        ->getResult();
    
    return view('admin/user_views/users_view', $this->mergeUserViewSidebarData($data));
}

// In your controller or a helper file



/**
 * Get employee photo URL
 * @param string $photo
 * @return string
 */



/**
 * Serve employee images from writable directory
 */
/**
 * Serve employee images from writable directory
 */
public function getEmployeeImage($filename)
{
    // Security: Only allow image files
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        $this->response->setStatusCode(403);
        $this->response->setBody('Forbidden');
        return $this->response;
    }
    
    // Build the path to the image
    $path = WRITEPATH . 'uploads/employees-img/' . $filename;
    
    // Check if file exists
    if (!file_exists($path)) {
        // Return default avatar
        $defaultPath = FCPATH . 'resource/adminlte/dist/img/emp-avatar.jpg';
        if (file_exists($defaultPath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $defaultPath);
            finfo_close($finfo);
            
            $this->response->setHeader('Content-Type', $mime);
            $this->response->setBody(file_get_contents($defaultPath));
            return $this->response;
        }
        
        $this->response->setStatusCode(404);
        $this->response->setBody('Image not found');
        return $this->response;
    }
    
    // Get the file MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $path);
    finfo_close($finfo);
    
    // Read the file content
    $imageContent = file_get_contents($path);
    
    // Clear any previous output
    ob_clean();
    
    // Set headers for image delivery
    $this->response->setHeader('Content-Type', $mime);
    $this->response->setHeader('Content-Length', filesize($path));
    $this->response->setHeader('Cache-Control', 'public, max-age=86400');
    $this->response->setBody($imageContent);
    
    // Prevent any view rendering
    return $this->response;
}


/**
 * Skip filters for getEmployeeImage method
 */
public function _remap($method, ...$params)
{
    if ($method === 'getEmployeeImage') {
        // Skip CSRF and auth filters for image serving
        return $this->$method(...$params);
    }
    return $this->$method(...$params);
}


private function getAvailableSubjects($campusId, $currentTeacherId = null)
{
    $db = $this->db ?? \Config\Database::connect();
    
    // Get all class sections
    $classes = $db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_name, s.section_name')
        ->join('classes c', 'cs.class_id = c.class_id')
        ->join('sections s', 'cs.section_id = s.section_id')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_name, s.section_name')
        ->get()
        ->getResult();
    
    $availableSubjects = [];
    
    foreach ($classes as $class) {
        // Get subjects for this class section
        $subjects = $db->table('section_subjects ss')
            ->select('ss.sec_sub_id, s.subject_name, s.sid as subject_id, 
                     (SELECT tid FROM teacher_subjects WHERE sec_sub_id = ss.sec_sub_id AND status = 1 LIMIT 1) as current_teacher_id,
                     (SELECT CONCAT(u.first_name, " ", u.last_name) FROM teacher_subjects ts 
                      JOIN users u ON ts.tid = u.id 
                      WHERE ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1 LIMIT 1) as current_teacher_name')
            ->join('allsubject s', 'ss.subject_id = s.sid')
            ->where('ss.cls_sec_id', $class->cls_sec_id)
            ->where('ss.status', 1)
            ->get()
            ->getResult();
        
        foreach ($subjects as $subject) {
            $availableSubjects[] = [
                'cls_sec_id' => $class->cls_sec_id,
                'class_name' => $class->class_name,
                'section_name' => $class->section_name,
                'sec_sub_id' => $subject->sec_sub_id,
                'subject_id' => $subject->subject_id,
                'subject_name' => $subject->subject_name,
                'current_teacher_id' => $subject->current_teacher_id,
                'current_teacher_name' => $subject->current_teacher_name,
                'is_assigned_to_current' => ($subject->current_teacher_id == $currentTeacherId),
                'is_assigned' => !empty($subject->current_teacher_id)
            ];
        }
    }
    
    return $availableSubjects;
}

/**
 * Get available class teacher assignments
 */
private function getAvailableClassTeachers($campusId, $currentTeacherId = null)
{
    $db = $this->db ?? \Config\Database::connect();
    
    // Get all class sections
    $classes = $db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_name, s.section_name,
                 (SELECT tid FROM teacher_section WHERE cls_sec_id = cs.cls_sec_id AND status = 1 LIMIT 1) as current_teacher_id,
                 (SELECT CONCAT(u.first_name, " ", u.last_name) FROM teacher_section ts 
                  JOIN users u ON ts.tid = u.id 
                  WHERE ts.cls_sec_id = cs.cls_sec_id AND ts.status = 1 LIMIT 1) as current_teacher_name')
        ->join('classes c', 'cs.class_id = c.class_id')
        ->join('sections s', 'cs.section_id = s.section_id')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_id, s.section_id')
        ->get()
        ->getResult();
    
    $availableClasses = [];
    
    foreach ($classes as $class) {
        $availableClasses[] = [
            'cls_sec_id' => $class->cls_sec_id,
            'class_name' => $class->class_name,
            'section_name' => $class->section_name,
            'current_teacher_id' => $class->current_teacher_id,
            'current_teacher_name' => $class->current_teacher_name,
            'is_assigned_to_current' => ($class->current_teacher_id == $currentTeacherId),
            'is_assigned' => !empty($class->current_teacher_id)
        ];
    }
    
    return $availableClasses;
}

/**
 * Save subject assignments
 */
private function saveSubjectAssignments($teacherId, $selectedSubjects)
{
    $db = $this->db ?? \Config\Database::connect();
    
    // Begin transaction
    $db->transStart();
    
    try {
        // Get current assignments
        $currentSubjects = $db->table('teacher_subjects')
            ->where('tid', $teacherId)
            ->where('status', 1)
            ->get()
            ->getResult();
        
        $currentSubjectIds = array_map(function($item) {
            return $item->sec_sub_id;
        }, $currentSubjects);
        
        // Subjects to add (selected but not currently assigned)
        $subjectsToAdd = array_diff($selectedSubjects, $currentSubjectIds);
        
        // Subjects to remove (currently assigned but not selected)
        $subjectsToRemove = array_diff($currentSubjectIds, $selectedSubjects);
        
        // Remove old assignments
        if (!empty($subjectsToRemove)) {
            foreach ($subjectsToRemove as $secSubId) {
                $db->table('teacher_subjects')
                    ->where('tid', $teacherId)
                    ->where('sec_sub_id', $secSubId)
                    ->update(['status' => 0, 'updated_date' => date('Y-m-d H:i:s')]);
            }
        }
        
        // Add new assignments - and remove from other teachers
        foreach ($subjectsToAdd as $secSubId) {
            $sectionSubject = $db->table('section_subjects')
                ->select('cls_sec_id')
                ->where('sec_sub_id', $secSubId)
                ->where('status', 1)
                ->get()
                ->getRow();

            if (! $sectionSubject) {
                continue;
            }

            // First, remove this subject from any other teacher
            $db->table('teacher_subjects')
                ->where('sec_sub_id', $secSubId)
                ->where('status', 1)
                ->update(['status' => 0, 'updated_date' => date('Y-m-d H:i:s')]);
            
            // Then assign to new teacher
            $db->table('teacher_subjects')->insert([
                'tid' => $teacherId,
                'sec_sub_id' => $secSubId,
                'cls_sec_id' => $sectionSubject->cls_sec_id,
                'status' => 1,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id' => session()->get('member_userid')
            ]);
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            throw new \Exception('Failed to save subject assignments');
        }
        
        return ['success' => true, 'message' => 'Subject assignments updated successfully'];
        
    } catch (\Exception $e) {
        $db->transRollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Save class teacher assignments
 */
private function saveClassTeacherAssignments($teacherId, $selectedClasses)
{
    $db = $this->db ?? \Config\Database::connect();
    
    // Begin transaction
    $db->transStart();
    
    try {
        // Get current assignments
        $currentClasses = $db->table('teacher_section')
            ->where('tid', $teacherId)
            ->where('status', 1)
            ->get()
            ->getResult();
        
        $currentClassIds = array_map(function($item) {
            return $item->cls_sec_id;
        }, $currentClasses);
        
        // Classes to add (selected but not currently assigned)
        $classesToAdd = array_diff($selectedClasses, $currentClassIds);
        
        // Classes to remove (currently assigned but not selected)
        $classesToRemove = array_diff($currentClassIds, $selectedClasses);
        
        // Remove old assignments
        if (!empty($classesToRemove)) {
            foreach ($classesToRemove as $clsSecId) {
                $db->table('teacher_section')
                    ->where('tid', $teacherId)
                    ->where('cls_sec_id', $clsSecId)
                    ->update(['status' => 0, 'updated_date' => date('Y-m-d H:i:s')]);
            }
        }
        
        // Add new assignments - and remove from other teachers
        foreach ($classesToAdd as $clsSecId) {
            // First, remove this class from any other teacher
            $db->table('teacher_section')
                ->where('cls_sec_id', $clsSecId)
                ->where('status', 1)
                ->update(['status' => 0, 'updated_date' => date('Y-m-d H:i:s')]);
            
            // Then assign to new teacher
            $db->table('teacher_section')->insert([
                'tid' => $teacherId,
                'cls_sec_id' => $clsSecId,
                'detail' => 'Class Teacher',
                'status' => 1,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s'),
                'user_id' => session()->get('member_userid')
            ]);
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            throw new \Exception('Failed to save class teacher assignments');
        }
        
        return ['success' => true, 'message' => 'Class teacher assignments updated successfully'];
        
    } catch (\Exception $e) {
        $db->transRollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Add these variables to the edit/add view
 */
public function add()
{
    if ($deny = $this->requireAdminUsersPermission()) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $campusId = $this->session->get('member_campusid');
    
    $assignableRoles = $this->getAssignableRoles();
    
    // Get available subjects and classes for assignment
    $availableSubjects = $this->getAvailableSubjects($campusId);
    $availableClasses = $this->getAvailableClassTeachers($campusId);
    
    return view('admin/users_edit', [
        'user_types' => $db->table('user_type')->get()->getResult(),
        'assignableRoles' => $assignableRoles,
        'selectedRoleIds' => [],
        'selectedRoleDetails' => [],
        'currentUserLevel' => $this->currentUserLevel,
        'currentUserRoleName' => $this->currentUserRole->rolename ?? 'Unknown',
        'canAssignMultipleRoles' => $this->canAssignMultipleRoles(),
        'requireOldPasswordForPasswordChange' => $this->currentUserRequiresOldPasswordForUserPasswordChange(),
        'levelNames' => $this->getAllLevelNames(),
        'availableSubjects' => $availableSubjects,
        'availableClasses' => $availableClasses,
        'selectedSubjects' => [],
        'selectedClassTeachers' => []
    ]);
}



public function edit($id)
{
    $id = (int) $id;
    if ($deny = $this->requireFullEditPermission()) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $sessionCampusId = (int) $this->session->get('member_campusid');
    
    $user = $db->table('users')->where('id', $id)->get()->getRow();
    
    if (!$user) {
        return redirect()->to('admin/users')->with('error', 'User not found');
    }
    $campusId = (int) ($user->campus_id ?? $sessionCampusId);
    $planId = $this->getCampusPlanId($campusId);
    
    // Get user's current roles (strictly scoped to the edited user's campus plan)
    $userRolesPrimary = $db->table('user_roles ur')
        ->select('r.id as roleID, r.issys, rn.rolename, rn.detail')
        ->join('roles r', 'ur.roleID = r.id AND r.plan_id = ' . (int) $planId, 'inner')
        ->join('role_name rn', 'r.role_name_id = rn.role_name_id')
        ->where('ur.userID', $id)
        ->get()
        ->getResult();

    // Legacy fallback: old rows where user_roles.roleID stored role_name_id.
    $userRolesLegacy = $db->table('user_roles ur')
        ->select('r.id as roleID, r.issys, rn.rolename, rn.detail')
        ->join('roles r', 'ur.roleID = r.role_name_id AND r.plan_id = ' . (int) $planId, 'inner')
        ->join('role_name rn', 'r.role_name_id = rn.role_name_id')
        ->where('ur.userID', $id)
        ->get()
        ->getResult();

    $userRoles = [];
    $seenRoleIds = [];
    foreach (array_merge($userRolesPrimary, $userRolesLegacy) as $role) {
        $rid = (int) ($role->roleID ?? 0);
        if ($rid > 0 && !isset($seenRoleIds[$rid])) {
            $seenRoleIds[$rid] = true;
            $userRoles[] = $role;
        }
    }
    
    $selectedRoleIds = array_map(function($role) {
        return (int) $role->roleID;
    }, $userRoles);
    
    $selectedRoleDetails = [];
    foreach ($userRoles as $role) {
        $selectedRoleDetails[$role->roleID] = [
            'name' => $role->rolename,
            'issys' => $role->issys,
            'detail' => $role->detail
        ];
    }

    // Get current subject assignments
    $currentSubjects = $db->table('teacher_subjects')
        ->select('sec_sub_id')
        ->where('tid', $id)
        ->where('status', 1)
        ->get()
        ->getResult();
    
    $selectedSubjects = array_map(function($item) {
        return $item->sec_sub_id;
    }, $currentSubjects);
    
    // Get current class teacher assignments
    $currentClasses = $db->table('teacher_section')
        ->select('cls_sec_id')
        ->where('tid', $id)
        ->where('status', 1)
        ->get()
        ->getResult();
    
    $selectedClassTeachers = array_map(function($item) {
        return $item->cls_sec_id;
    }, $currentClasses);

    $assignableRoles = $this->getAssignableRoles($campusId);
    $levelNames = $this->getAllLevelNames();
    
    // Get available subjects and classes
    $availableSubjects = $this->getAvailableSubjects($campusId, $id);
    $availableClasses = $this->getAvailableClassTeachers($campusId, $id);

    return view('admin/users_edit', [
        'info' => $user,
        'assignableRoles' => $assignableRoles,
        'selectedRoleIds' => $selectedRoleIds,
        'selectedRoleDetails' => $selectedRoleDetails,
        'currentUserLevel' => $this->currentUserLevel,
        'currentUserRoleName' => $this->currentUserRole->rolename ?? 'Unknown',
        'canAssignMultipleRoles' => $this->canAssignMultipleRoles(),
        'requireOldPasswordForPasswordChange' => $this->currentUserRequiresOldPasswordForUserPasswordChange(),
        'levelNames' => $levelNames,
        'availableSubjects' => $availableSubjects,
        'availableClasses' => $availableClasses,
        'selectedSubjects' => $selectedSubjects,
        'selectedClassTeachers' => $selectedClassTeachers
    ]);
}

public function checkAvailability()
{
    $field = (string) $this->request->getGet('field');
    $value = trim((string) $this->request->getGet('value'));
    $id = (int) $this->request->getGet('id');

    if (!in_array($field, ['username', 'email'], true)) {
        return $this->response->setJSON([
            'success' => false,
            'available' => false,
            'msg' => 'Invalid availability check'
        ]);
    }

    if ($value === '') {
        return $this->response->setJSON([
            'success' => true,
            'available' => false,
            'msg' => ucfirst($field) . ' is required'
        ]);
    }

    if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return $this->response->setJSON([
            'success' => true,
            'available' => false,
            'msg' => 'Invalid email format'
        ]);
    }

    $builder = $this->db->table('users')->where($field, $value);
    if ($id > 0) {
        $builder->where('id !=', $id);
    }

    $exists = (bool) $builder->get()->getRow();
    $label = $field === 'email' ? 'Email' : 'Username';

    return $this->response->setJSON([
        'success' => true,
        'available' => !$exists,
        'msg' => $exists ? $label . ' is already taken' : $label . ' is available'
    ]);
}

public function debugRoleMapping($id)
{
    $db = $this->db ?? \Config\Database::connect();

    $user = $db->table('users')->where('id', $id)->get()->getRow();
    if (!$user) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'User not found',
            'user_id' => (int) $id,
        ]);
    }

    $sessionCampusId = (int) $this->session->get('member_campusid');
    $userCampusId = (int) ($user->campus_id ?? 0);
    $sessionPlanId = $this->getCampusPlanId($sessionCampusId);
    $userPlanId = $this->getCampusPlanId($userCampusId);

    $userRoleRows = $db->table('user_roles')
        ->select('userID, roleID, addDate')
        ->where('userID', $id)
        ->orderBy('roleID', 'ASC')
        ->get()
        ->getResultArray();

    $matchedByRoleId = $db->table('user_roles ur')
        ->select('ur.userID, ur.roleID as stored_roleID, r.id as resolved_role_id, r.role_name_id, r.plan_id, r.issys, rn.rolename, rn.detail')
        ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . (int) $userPlanId, 'inner')
        ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
        ->where('ur.userID', $id)
        ->orderBy('rn.rolename', 'ASC')
        ->get()
        ->getResultArray();

    $matchedByRoleNameId = $db->table('user_roles ur')
        ->select('ur.userID, ur.roleID as stored_roleID, r.id as resolved_role_id, r.role_name_id, r.plan_id, r.issys, rn.rolename, rn.detail')
        ->join('roles r', 'r.role_name_id = ur.roleID AND r.plan_id = ' . (int) $userPlanId, 'inner')
        ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
        ->where('ur.userID', $id)
        ->orderBy('rn.rolename', 'ASC')
        ->get()
        ->getResultArray();

    $assignableRoles = array_map(static function ($role) {
        return [
            'id' => (int) ($role->id ?? 0),
            'role_name_id' => (int) ($role->role_name_id ?? 0),
            'plan_id' => (int) ($role->plan_id ?? 0),
            'rolename' => (string) ($role->rolename ?? ''),
            'issys' => (int) ($role->issys ?? 0),
            'level' => (int) ($role->level ?? 0),
        ];
    }, $this->getAssignableRoles($userCampusId));

    $groupByRoleName = static function (array $rows, string $nameKey = 'rolename', string $idKey = 'resolved_role_id') {
        $grouped = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row[$nameKey] ?? ''));
            if ($name === '') {
                continue;
            }
            if (!isset($grouped[$name])) {
                $grouped[$name] = [
                    'count' => 0,
                    'resolved_role_ids' => [],
                    'role_name_ids' => [],
                    'stored_role_ids' => [],
                ];
            }
            $grouped[$name]['count']++;
            $resolvedRoleId = (int) ($row[$idKey] ?? 0);
            $roleNameId = (int) ($row['role_name_id'] ?? 0);
            $storedRoleId = (int) ($row['stored_roleID'] ?? 0);
            if ($resolvedRoleId > 0 && !in_array($resolvedRoleId, $grouped[$name]['resolved_role_ids'], true)) {
                $grouped[$name]['resolved_role_ids'][] = $resolvedRoleId;
            }
            if ($roleNameId > 0 && !in_array($roleNameId, $grouped[$name]['role_name_ids'], true)) {
                $grouped[$name]['role_name_ids'][] = $roleNameId;
            }
            if ($storedRoleId > 0 && !in_array($storedRoleId, $grouped[$name]['stored_role_ids'], true)) {
                $grouped[$name]['stored_role_ids'][] = $storedRoleId;
            }
        }
        ksort($grouped);
        return $grouped;
    };

    $assignableGrouped = [];
    foreach ($assignableRoles as $row) {
        $name = trim((string) ($row['rolename'] ?? ''));
        if ($name === '') {
            continue;
        }
        if (!isset($assignableGrouped[$name])) {
            $assignableGrouped[$name] = [
                'count' => 0,
                'role_ids' => [],
                'role_name_ids' => [],
            ];
        }
        $assignableGrouped[$name]['count']++;
        if (!in_array((int) $row['id'], $assignableGrouped[$name]['role_ids'], true)) {
            $assignableGrouped[$name]['role_ids'][] = (int) $row['id'];
        }
        if (!in_array((int) $row['role_name_id'], $assignableGrouped[$name]['role_name_ids'], true)) {
            $assignableGrouped[$name]['role_name_ids'][] = (int) $row['role_name_id'];
        }
    }
    ksort($assignableGrouped);

    $payload = [
        'success' => true,
        'debug_for_user_id' => (int) $id,
        'session_context' => [
            'session_campus_id' => $sessionCampusId,
            'session_plan_id' => $sessionPlanId,
            'current_user_level' => (int) ($this->currentUserLevel ?? 0),
            'current_user_role' => (string) ($this->currentUserRole->rolename ?? ''),
        ],
        'target_user_context' => [
            'target_user_id' => (int) $user->id,
            'target_username' => (string) ($user->username ?? ''),
            'target_campus_id' => $userCampusId,
            'target_plan_id' => $userPlanId,
        ],
        'user_roles_table_rows' => $userRoleRows,
        'resolved_matches_by_roles_id' => $matchedByRoleId,
        'resolved_matches_by_roles_role_name_id_legacy' => $matchedByRoleNameId,
        'grouped_duplicates' => [
            'by_roles_id_match' => $groupByRoleName($matchedByRoleId),
            'by_legacy_role_name_id_match' => $groupByRoleName($matchedByRoleNameId),
            'assignable_roles_on_edit' => $assignableGrouped,
        ],
        'assignable_roles_used_in_edit' => $assignableRoles,
    ];

    return $this->response
        ->setHeader('Content-Type', 'application/json')
        ->setBody(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

/**
 * Update the save method to handle subject and class assignments
 */
public function save()
{
    if ($deny = $this->requireFullEditPermission(true)) {
        return $deny;
    }

    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $db = $this->db ?? \Config\Database::connect();
    $campusId = $this->session->get('member_campusid');
    $userId = $this->session->get('member_userid');
    
    $id = $this->request->getPost('id');
    
    try {
        // Log the request
        log_message('debug', 'Save request received. ID: ' . $id);
        
        // Get selected roles
        $selectedRoles = $this->request->getPost('role_ids') ?: [];
        if (!is_array($selectedRoles)) {
            $selectedRoles = [$selectedRoles];
        }
        if (empty($selectedRoles) && $this->request->getPost('role_id')) {
            $selectedRoles = [$this->request->getPost('role_id')];
        }
        $selectedRoles = array_values(array_unique(array_filter(array_map('intval', $selectedRoles))));

        $targetCampusId = (int) $campusId;
        if (! empty($id)) {
            $existingTarget = $db->table('users')->select('campus_id')->where('id', (int) $id)->get()->getRow();
            if ($existingTarget) {
                $targetCampusId = (int) ($existingTarget->campus_id ?? $targetCampusId);
            }
        }
        $targetPlanId = $this->getCampusPlanId($targetCampusId);
        if ($targetPlanId > 0 && ! empty($selectedRoles)) {
            helper('role');
            $selectedRoles = normalizeRoleIdsForPlan($selectedRoles, $targetPlanId);
        }

        if (empty($id) && empty($selectedRoles)) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Please select at least one role before saving employee.'
            ]);
        }
        
        // Get selected subjects and classes
        $selectedSubjects = $this->request->getPost('subjects') ?: [];
        $selectedClassTeachers = $this->request->getPost('class_teachers') ?: [];
        
        // Validate role permissions
        if (!empty($selectedRoles)) {
            foreach ($selectedRoles as $roleId) {
                if (!$this->canAssignRole($roleId, $targetCampusId)) {
                    return $this->response->setJSON([
                        'success' => false, 
                        'msg' => 'You do not have permission to assign one of the selected roles'
                    ]);
                }
            }
        }
        
        // Validate unique email and username
        $username = $this->request->getPost('username');
        $email = $this->request->getPost('email');
        $rawPassword = (string) $this->request->getPost('password');
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');
        $confirmNewPassword = (string) $this->request->getPost('confirm_new_password');
        $currentPassword = (string) $this->request->getPost('current_password');
        
        if (empty($username)) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Username is required'
            ]);
        }
        
        if (empty($email)) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Email is required'
            ]);
        }
        
        if (empty($id)) {
            if (strlen($rawPassword) < 6) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg' => 'Password is required and must be at least 6 characters'
                ]);
            }
            if ($rawPassword !== $confirmPassword) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg' => 'Password and confirm password do not match'
                ]);
            }
        } elseif ($newPassword !== '' || $confirmNewPassword !== '') {
            if (strlen($newPassword) < 6) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg' => 'New password must be at least 6 characters'
                ]);
            }
            if ($newPassword !== $confirmNewPassword) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg' => 'New password and confirm password do not match'
                ]);
            }
            if ($this->currentUserRequiresOldPasswordForUserPasswordChange() && $currentPassword === '') {
                return $this->response->setJSON([
                    'success' => false,
                    'msg' => 'Current password is required for your role to change password'
                ]);
            }
        }
        
        $usernameCheck = $db->table('users')
            ->where('username', $username)
            ->where('id !=', $id)
            ->get()
            ->getRow();
        
        if ($usernameCheck) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Username already exists. Please choose a different username.'
            ]);
        }
        
        $emailCheck = $db->table('users')
            ->where('email', $email)
            ->where('id !=', $id)
            ->get()
            ->getRow();
        
        if ($emailCheck) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Email already exists. Please use a different email address.'
            ]);
        }
        
        // Prepare user data
        $data = [
            'campus_id' => $campusId,
            'username' => $username,
            'email' => $email,
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'cnic' => $this->request->getPost('cnic'),
            'f_name' => $this->request->getPost('f_name'),
            'dob' => $this->request->getPost('dob'),
            'gender' => $this->request->getPost('gender'),
            'marital_status' => $this->request->getPost('marital_status'),
            'joining_date' => $this->request->getPost('joining_date'),
            'mobile_no' => $this->request->getPost('mobile_no'),
            'mobile_no2' => $this->request->getPost('mobile_no2'),
            'address' => $this->request->getPost('address'),
            'emergency_contact_person' => $this->request->getPost('emergency_contact_person'),
            'emergency_contact_no' => $this->request->getPost('emergency_contact_no'),
            'qualification' => $this->request->getPost('qualification'),
            'experience' => $this->request->getPost('experience'),
            'skills' => $this->request->getPost('skills'),
            'designation' => $this->request->getPost('designation'),
            'bank_name' => $this->request->getPost('bank_name'),
            'account_title' => $this->request->getPost('account_title'),
            'branch_code' => $this->request->getPost('branch_code'),
            'account_number' => $this->request->getPost('account_number'),
            'bank_address' => $this->request->getPost('bank_address'),
            'contract_start' => $this->request->getPost('contract_start'),
            'contract_end' => $this->request->getPost('contract_end'),
            'basic_salary' => $this->request->getPost('salary'),
            'contract_type' => $this->request->getPost('contract_type'),
            'salary_payment_method' => $this->request->getPost('salary_payment_method'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        // Handle avatar upload
        $file = $this->request->getFile('image');
        $existingPhoto = $this->request->getPost('existing_photo');
        $uploadedPhotoName = null; // Track if a new photo was uploaded

        if ($file && $file->isValid() && !$file->hasMoved()) {
            try {
                // Validate file
                if ($file->getSize() > 2 * 1024 * 1024) {
                    throw new \Exception('File size exceeds 2MB limit');
                }
                
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!in_array($file->getMimeType(), $allowedTypes)) {
                    throw new \Exception('Only JPG and PNG images are allowed');
                }
                
                // Generate unique filename
                $newName = $file->getRandomName();
                
                // Use the same pattern as student uploads
                $uploadPath = FCPATH . 'uploads/employees/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                // Delete old photo if exists (to clean up unused files)
                if (!empty($existingPhoto) && file_exists($uploadPath . $existingPhoto)) {
                    unlink($uploadPath . $existingPhoto);
                    log_message('debug', 'Deleted old photo: ' . $existingPhoto);
                }
                
                // Move file
                if ($file->move($uploadPath, $newName)) {
                    $data['photo'] = $newName;
                    $uploadedPhotoName = $newName;
                    log_message('debug', 'File uploaded successfully: ' . $newName . ' to ' . $uploadPath);
                } else {
                    throw new \Exception('Failed to move uploaded file');
                }
            } catch (\Exception $e) {
                log_message('error', 'File upload error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'msg' => 'File upload error: ' . $e->getMessage()
                ]);
            }
        }
        
        // Begin transaction
        $db->transStart();
        
        try {
            $newUserId = null;
            
            if (empty($id)) {
                // Insert new user
                $data['password'] = password_hash($rawPassword, PASSWORD_DEFAULT);
                $data['created_date'] = date('Y-m-d H:i:s');
                $data['status'] = 1;
                
                if (!$db->table('users')->insert($data)) {
                    throw new \Exception('Failed to insert user data');
                }
                $newUserId = $db->insertID();
                
                // Assign roles for new user
                if (!empty($selectedRoles)) {
                    foreach ($selectedRoles as $roleId) {
                        $db->table('user_roles')->insert([
                            'userID' => $newUserId,
                            'roleID' => $roleId,
                            'addDate' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
                
                // Record initial salary in history when set
                $salary = $this->request->getPost('salary');
                if ($salary && (float) $salary > 0) {
                    $salaryModel = new \App\Models\SalaryModel();
                    $salaryModel->recordIncrement(
                        $newUserId,
                        (int) $data['campus_id'],
                        0,
                        (float) $salary,
                        'Initial salary on employee creation',
                        $userId
                    );
                }
                
                // Save subject assignments if method exists
                if (!empty($selectedSubjects) && method_exists($this, 'saveSubjectAssignments')) {
                    $subjectResult = $this->saveSubjectAssignments($newUserId, $selectedSubjects);
                    if (!$subjectResult['success']) {
                        throw new \Exception($subjectResult['message']);
                    }
                }
                
                // Save class teacher assignments if method exists
                if (!empty($selectedClassTeachers) && method_exists($this, 'saveClassTeacherAssignments')) {
                    $classResult = $this->saveClassTeacherAssignments($newUserId, $selectedClassTeachers);
                    if (!$classResult['success']) {
                        throw new \Exception($classResult['message']);
                    }
                }
                
            } else {
                // Update existing user
                $existingUser = $db->table('users')->where('id', $id)->get()->getRow();
                if (!$existingUser) {
                    throw new \Exception('User not found');
                }

                if ($newPassword !== '' || $confirmNewPassword !== '') {
                    if ($this->currentUserRequiresOldPasswordForUserPasswordChange()) {
                        $actor = $db->table('users')->where('id', $userId)->get()->getRow();
                        if (!$actor || empty($actor->password) || !password_verify($currentPassword, $actor->password)) {
                            throw new \Exception('Current password is incorrect');
                        }
                    }
                    $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }

                if (!$db->table('users')->where('id', $id)->update($data)) {
                    throw new \Exception('Failed to update user data');
                }
                $newUserId = $id;
                
                // Handle role assignments (add, replace, or remove all)
                $existingRoles = $db->table('user_roles')
                    ->where('userID', $id)
                    ->get()
                    ->getResult();
                
                $existingRoleIds = array_map(function($role) {
                    return (int) $role->roleID;
                }, $existingRoles);
                $selectedRoles = array_map('intval', (array) $selectedRoles);
                
                $rolesToAdd = array_diff($selectedRoles, $existingRoleIds);
                $rolesToRemove = array_diff($existingRoleIds, $selectedRoles);
                
                foreach ($rolesToAdd as $roleId) {
                    $db->table('user_roles')->insert([
                        'userID' => $id,
                        'roleID' => $roleId,
                        'addDate' => date('Y-m-d H:i:s')
                    ]);
                }
                
                if (!empty($rolesToRemove)) {
                    $db->table('user_roles')
                        ->where('userID', $id)
                        ->whereIn('roleID', $rolesToRemove)
                        ->delete();
                }
                
                // Record salary change in history when basic_salary changes
                $salary = $this->request->getPost('salary');
                if ($salary !== null && $salary !== '') {
                    $oldBasic = (float) ($existingUser->basic_salary ?? 0);
                    $newBasic = (float) $salary;
                    if ($newBasic !== $oldBasic) {
                        $salaryModel = new \App\Models\SalaryModel();
                        $salaryModel->recordIncrement(
                            (int) $id,
                            (int) ($existingUser->campus_id ?? $this->session->get('member_campusid')),
                            $oldBasic,
                            $newBasic,
                            'Updated via employee edit form',
                            $userId
                        );
                    }
                }
                
                // Subject/class assignments are saved via AJAX toggles on the edit form.
                // Only bulk-sync when explicit POST arrays are sent (legacy/add flows).
                if (! empty($selectedSubjects) && method_exists($this, 'saveSubjectAssignments')) {
                    $subjectResult = $this->saveSubjectAssignments($id, $selectedSubjects);
                    if (! $subjectResult['success']) {
                        throw new \Exception($subjectResult['message']);
                    }
                }

                if (! empty($selectedClassTeachers) && method_exists($this, 'saveClassTeacherAssignments')) {
                    $classResult = $this->saveClassTeacherAssignments($id, $selectedClassTeachers);
                    if (! $classResult['success']) {
                        throw new \Exception($classResult['message']);
                    }
                }
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Database transaction failed');
            }
            
            log_message('debug', 'User saved successfully. ID: ' . $newUserId);

            $acl = new \App\Libraries\MemberAcl((int) $newUserId);
            $acl->clearUserCaches((int) $newUserId);
            \App\Libraries\RoleMenuAccess::clearCacheForUser((int) $newUserId);
            if ((int) $newUserId === (int) session()->get('member_userid')) {
                \App\Libraries\MemberCurrentUser::clearCache();
            }

            // Prepare success response
            $responseData = [
                'success' => true, 
                'msg' => 'Employee saved successfully',
                'id' => $newUserId
            ];
            
            // Add photo URL to response if a new photo was uploaded
            if (!empty($uploadedPhotoName)) {
                $responseData['photo_url'] = base_url('uploads/employees/' . $uploadedPhotoName);
                $responseData['photo_filename'] = $uploadedPhotoName;
            } elseif (!empty($data['photo']) && empty($id)) {
                // For new user with photo
                $responseData['photo_url'] = base_url('uploads/employees/' . $data['photo']);
                $responseData['photo_filename'] = $data['photo'];
            }
            
            return $this->response->setJSON($responseData);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Save error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Database error: ' . $e->getMessage()
            ]);
        }
        
    } catch (\Exception $e) {
        log_message('error', 'General error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false, 
            'msg' => 'Server error: ' . $e->getMessage()
        ]);
    }
}

/**
 * AJAX endpoint to get available subjects for a teacher
 */
public function getTeacherSubjects($teacherId)
{
    if ($deny = $this->requireFullEditPermission(true)) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $teacherId = (int) $teacherId;

    $userRow = $db->table('users')->select('campus_id')->where('id', $teacherId)->get()->getRow();
    $campusId = (int) ($userRow->campus_id ?? $this->session->get('member_campusid'));
    
    // Get current subject assignments
    $currentSubjects = $db->table('teacher_subjects')
        ->select('sec_sub_id')
        ->where('tid', $teacherId)
        ->where('status', 1)
        ->get()
        ->getResult();
    
    $selectedSubjectIds = array_map(static function ($item) {
        return (int) $item->sec_sub_id;
    }, $currentSubjects);

    $currentClassSections = $db->table('teacher_section')
        ->select('cls_sec_id')
        ->where('tid', $teacherId)
        ->where('status', 1)
        ->get()
        ->getResult();

    $selectedClassSectionIds = array_map(static function ($item) {
        return (int) $item->cls_sec_id;
    }, $currentClassSections);
    
    // Get available subjects with assignment status
    $subjects = $db->table('section_subjects ss')
        ->select('ss.sec_sub_id, cs.cls_sec_id, c.class_name, s.section_name, sub.subject_name,
                  (SELECT tid FROM teacher_subjects WHERE sec_sub_id = ss.sec_sub_id AND status = 1 LIMIT 1) as assigned_teacher_id,
                  (SELECT CONCAT(u.first_name, " ", u.last_name) FROM teacher_subjects ts 
                   JOIN users u ON ts.tid = u.id 
                   WHERE ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1 LIMIT 1) as assigned_teacher_name,
                  (SELECT tid FROM teacher_section WHERE cls_sec_id = cs.cls_sec_id AND status = 1 LIMIT 1) as section_class_teacher_id,
                  (SELECT CONCAT(u.first_name, " ", u.last_name) FROM teacher_section ts
                   JOIN users u ON ts.tid = u.id
                   WHERE ts.cls_sec_id = cs.cls_sec_id AND ts.status = 1 LIMIT 1) as section_class_teacher_name')
        ->join('class_section cs', 'ss.cls_sec_id = cs.cls_sec_id')
        ->join('classes c', 'cs.class_id = c.class_id')
        ->join('sections s', 'cs.section_id = s.section_id')
        ->join('allsubject sub', 'ss.subject_id = sub.sid')
        ->where('cs.campus_id', $campusId)
        ->where('ss.status', 1)
        ->where('cs.status', 1)
        ->orderBy('c.class_id, s.section_id, sub.subject_name')
        ->get()
        ->getResult();
    
    $response = [];
    foreach ($subjects as $subject) {
        $clsSecId = (int) $subject->cls_sec_id;
        $response[] = [
            'sec_sub_id' => (int) $subject->sec_sub_id,
            'cls_sec_id' => $clsSecId,
            'class_name' => $subject->class_name,
            'section_name' => $subject->section_name,
            'subject_name' => $subject->subject_name,
            'is_selected' => in_array((int) $subject->sec_sub_id, $selectedSubjectIds, true),
            'assigned_teacher_name' => $subject->assigned_teacher_name,
            'assigned_teacher_id' => (int) ($subject->assigned_teacher_id ?? 0),
            'is_class_teacher' => in_array($clsSecId, $selectedClassSectionIds, true),
            'section_class_teacher_id' => (int) ($subject->section_class_teacher_id ?? 0),
            'section_class_teacher_name' => $subject->section_class_teacher_name,
        ];
    }
    
    return $this->response->setJSON($response);
}

/**
 * AJAX endpoint to assign/unassign subject to teacher
 */

public function assignSubject()
{
    if ($deny = $this->requireFullEditPermission(true)) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $teacherId = $this->request->getPost('teacher_id');
    $secSubId = $this->request->getPost('sec_sub_id');
    $action = $this->request->getPost('action'); // 'assign' or 'unassign'
    
    $db->transStart();
    
    try {
        if ($action == 'assign') {
            // First, get the cls_sec_id from section_subjects table
            $sectionSubject = $db->table('section_subjects')
                ->select('cls_sec_id')
                ->where('sec_sub_id', $secSubId)
                ->where('status', 1)
                ->get()
                ->getRow();
            
            if (!$sectionSubject) {
                throw new \Exception('Section subject not found');
            }
            
            $clsSecId = $sectionSubject->cls_sec_id;
            
            // Remove this subject from any other teacher first
            $db->table('teacher_subjects')
                ->where('sec_sub_id', $secSubId)
                ->where('status', 1)
                ->update([
                    'status' => 0, 
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
            
            // Check if assignment already exists (but inactive)
            $existing = $db->table('teacher_subjects')
                ->where('tid', $teacherId)
                ->where('sec_sub_id', $secSubId)
                ->get()
                ->getRow();
            
            if ($existing) {
                // Reactivate existing record
                $db->table('teacher_subjects')
                    ->where('tid', $teacherId)
                    ->where('sec_sub_id', $secSubId)
                    ->update([
                        'status' => 1,
                        'cls_sec_id' => $clsSecId,
                        'updated_date' => date('Y-m-d H:i:s'),
                        'user_id' => $this->session->get('member_userid')
                    ]);
            } else {
                // Insert new assignment with cls_sec_id
                $db->table('teacher_subjects')->insert([
                    'tid' => $teacherId,
                    'sec_sub_id' => $secSubId,
                    'cls_sec_id' => $clsSecId,  // ← ADD THIS LINE
                    'status' => 1,
                    'created_date' => date('Y-m-d H:i:s'),
                    'updated_date' => date('Y-m-d H:i:s'),
                    'user_id' => $this->session->get('member_userid')
                ]);
            }
        } else {
            // Unassign - just update status, don't delete
            $db->table('teacher_subjects')
                ->where('tid', $teacherId)
                ->where('sec_sub_id', $secSubId)
                ->update([
                    'status' => 0, 
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            throw new \Exception('Transaction failed');
        }
        
        return $this->response->setJSON(['success' => true]);
        
    } catch (\Exception $e) {
        $db->transRollback();
        return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
    }
}
/**
 * AJAX endpoint to get available classes for class teacher assignment
 */
public function getTeacherClasses($teacherId)
{
    if ($deny = $this->requireFullEditPermission(true)) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $campusId = $this->session->get('member_campusid');
    
    // Get current assignments
    $currentClasses = $db->table('teacher_section')
        ->select('cls_sec_id')
        ->where('tid', $teacherId)
        ->where('status', 1)
        ->get()
        ->getResult();
    
    $selectedClassIds = array_map(function($item) {
        return $item->cls_sec_id;
    }, $currentClasses);
    
    // Get all available classes with assignment status
    $classes = $db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_name, s.section_name,
                  (SELECT tid FROM teacher_section WHERE cls_sec_id = cs.cls_sec_id AND status = 1 LIMIT 1) as assigned_teacher_id,
                  (SELECT CONCAT(u.first_name, " ", u.last_name) FROM teacher_section ts 
                   JOIN users u ON ts.tid = u.id 
                   WHERE ts.cls_sec_id = cs.cls_sec_id AND ts.status = 1 LIMIT 1) as assigned_teacher_name')
        ->join('classes c', 'cs.class_id = c.class_id')
        ->join('sections s', 'cs.section_id = s.section_id')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_id, s.section_id')
        ->get()
        ->getResult();
    
    $response = [];
    foreach ($classes as $class) {
        $response[] = [
            'cls_sec_id' => $class->cls_sec_id,
            'class_name' => $class->class_name,
            'section_name' => $class->section_name,
            'is_selected' => in_array($class->cls_sec_id, $selectedClassIds),
            'assigned_teacher_name' => $class->assigned_teacher_name,
            'assigned_teacher_id' => $class->assigned_teacher_id
        ];
    }
    
    return $this->response->setJSON($response);
}

/**
 * AJAX endpoint to assign/unassign class teacher
 */
public function assignClassTeacher()
{
    if ($deny = $this->requireFullEditPermission(true)) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    $teacherId = $this->request->getPost('teacher_id');
    $clsSecId = $this->request->getPost('cls_sec_id');
    $action = $this->request->getPost('action'); // 'assign' or 'unassign'
    $userId = $this->session->get('member_userid');
    
    $db->transStart();
    
    try {
        if ($action == 'assign') {
            // First, remove this class from any other teacher
            $db->table('teacher_section')
                ->where('cls_sec_id', $clsSecId)
                ->where('status', 1)
                ->update([
                    'status' => 0, 
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
            
            // Then assign to new teacher
            $existing = $db->table('teacher_section')
                ->where('tid', $teacherId)
                ->where('cls_sec_id', $clsSecId)
                ->get()
                ->getRow();
            
            if ($existing) {
                // Reactivate existing record
                $db->table('teacher_section')
                    ->where('tid', $teacherId)
                    ->where('cls_sec_id', $clsSecId)
                    ->update([
                        'status' => 1,
                        'updated_date' => date('Y-m-d H:i:s'),
                        'user_id' => $userId
                    ]);
            } else {
                // Insert new record
                $db->table('teacher_section')->insert([
                    'tid' => $teacherId,
                    'cls_sec_id' => $clsSecId,
                    'detail' => 'Class Teacher',
                    'status' => 1,
                    'created_date' => date('Y-m-d H:i:s'),
                    'updated_date' => date('Y-m-d H:i:s'),
                    'user_id' => $userId
                ]);
            }
        } else {
            // Unassign
            $db->table('teacher_section')
                ->where('tid', $teacherId)
                ->where('cls_sec_id', $clsSecId)
                ->update([
                    'status' => 0, 
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            throw new \Exception('Transaction failed');
        }
        
        return $this->response->setJSON(['success' => true]);
        
    } catch (\Exception $e) {
        $db->transRollback();
        return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
    }
}

private function handleAvatarUpload($userId = null)
{
    // Handle avatar upload
$file = $this->request->getFile('image');
$existingPhoto = $this->request->getPost('existing_photo');

if ($file && $file->isValid() && !$file->hasMoved()) {
    try {
        // Validate file
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \Exception('File size exceeds 2MB limit');
        }
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \Exception('Only JPG and PNG images are allowed');
        }
        
        // Generate unique filename
        $newName = $file->getRandomName();
        
        $uploadPath = ROOTPATH . 'uploads/employees/'; 
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        // Delete old photo if exists
        if (!empty($existingPhoto) && file_exists($uploadPath . $existingPhoto)) {
            unlink($uploadPath . $existingPhoto);
        }
        
        // Move file
        if ($file->move($uploadPath, $newName)) {
            $data['employeePhoto'] = $this->getEmployeePhoto($photo);
            $data['photo'] = $newName;
            log_message('debug', 'File uploaded successfully: ' . $newName);
        } else {
            throw new \Exception('Failed to move uploaded file');
        }
    } catch (\Exception $e) {
        log_message('error', 'File upload error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'File upload error: ' . $e->getMessage()
        ]);
    }
} else {
    // Keep existing photo if no new file uploaded
    if (!empty($existingPhoto)) {
        $data['photo'] = $existingPhoto;
    }
}
}


private function getEmployeePhoto($photo)
{
    if (empty($photo)) {
        return base_url('resource/adminlte/dist/img/emp-avatar.jpg');
    }
    
    // Return the URL to your controller method
    return base_url('admin/getEmployeeImage/' . $photo);
}

    private function setCurrentUserRoleInfo()
    {
        $userId = $this->session->get('member_userid');
        
        // Get current user's role
        $userRole = $this->db->table('user_roles ur')
            ->select('r.*, rn.rolename, rn.role_name_id as role_name_id_val')
            ->join('roles r', 'ur.roleID = r.id')
            ->join('role_name rn', 'r.role_name_id = rn.role_name_id')
            ->where('ur.userID', $userId)
            ->get()
            ->getRow();
        
        $this->currentUserRole = $userRole;
        
        // Determine user level based on role hierarchy
        $this->currentUserLevel = $this->getRoleLevel($userRole);
    }

    private function getRoleLevel($role)
    {
        if (!$role) return 999; // Unknown role gets lowest priority
        
        // Define role hierarchy based on issys and role_name
        // Super Admin (issys=1) gets highest level (1)
        if ($role->issys == 1 && stripos($role->rolename, 'super') !== false) {
            return 1;
        }
        // Admin (issys=1) gets level 2
        if ($role->issys == 1) {
            return 2;
        }
        // Manager level roles
        if (stripos($role->rolename, 'manager') !== false || 
            stripos($role->rolename, 'coordinator') !== false) {
            return 3;
        }
        // Teacher level roles
        if (stripos($role->rolename, 'teacher') !== false || 
            stripos($role->rolename, 'faculty') !== false) {
            return 4;
        }
        // Staff level roles
        if (stripos($role->rolename, 'staff') !== false || 
            stripos($role->rolename, 'assistant') !== false) {
            return 5;
        }
        
        return 6; // Default lowest level
    }

 
    public function index()
    {
        if ($deny = $this->requireAdminUsersPermission()) {
            return $deny;
        }

        $status = $this->request->getGet('status') ?? '1';
        $roleFilter = $this->request->getGet('role_filter') ?? 'all';
        return view('admin/users', [
            'status'       => $status,
            'role_filter'  => $roleFilter,
        ]);
    }

protected function getAssignableRoles(?int $campusId = null)
{
    $campusId = $campusId ?: (int) $this->session->get('member_campusid');
    $planId = $this->getCampusPlanId($campusId);
    if ($planId <= 0) {
        return [];
    }

    // One row per role_name_id for the campus plan (prevents duplicate labels).
    $allRoles = $this->db->table('roles r')
        ->select('MIN(r.id) as id, r.role_name_id, MAX(r.issys) as issys, rn.rolename, rn.detail')
        ->join('role_name rn', 'r.role_name_id = rn.role_name_id')
        ->where('r.plan_id', $planId)
        ->groupBy('r.role_name_id, rn.rolename, rn.detail')
        ->get()
        ->getResult();

    if (empty($allRoles)) {
        return [];
    }

    // Tree rule: only descendants of current user's role_name(s), excluding self.
    $allowedRoleNameIds = $this->getDescendantRoleNameIdsForCurrentUser($planId, false);
    if (empty($allowedRoleNameIds)) {
        return [];
    }

    $assignable = [];
    foreach ($allRoles as $role) {
        if (!in_array((int) $role->role_name_id, $allowedRoleNameIds, true)) {
            continue;
        }
        $role->id = (int) $role->id;
        $role->role_name_id = (int) $role->role_name_id;
        $role->issys = (int) $role->issys;
        $role->level = $this->getRoleLevel($role);
        $assignable[] = $role;
    }

    usort($assignable, function ($a, $b) {
        return ((int) $a->role_name_id) <=> ((int) $b->role_name_id);
    });

    return $assignable;
}

private function loadRoleNameTree(): array
{
    $rows = $this->db->table('role_name')
        ->select('role_name_id, parent_id')
        ->get()
        ->getResultArray();

    $children = [];
    foreach ($rows as $row) {
        $id = (int) ($row['role_name_id'] ?? 0);
        $parent = (int) ($row['parent_id'] ?? 0);
        if ($id <= 0) {
            continue;
        }
        if (!isset($children[$parent])) {
            $children[$parent] = [];
        }
        $children[$parent][] = $id;
    }

    return $children;
}

private function collectDescendants(array $children, int $rootId, bool $includeSelf = false): array
{
    $seen = [];
    $stack = [$rootId];

    while (!empty($stack)) {
        $current = array_pop($stack);
        if (isset($seen[$current])) {
            continue;
        }
        $seen[$current] = true;
        foreach (($children[$current] ?? []) as $kid) {
            $stack[] = (int) $kid;
        }
    }

    if (!$includeSelf) {
        unset($seen[$rootId]);
    }

    return array_map('intval', array_keys($seen));
}

private function getCurrentUserRoleNameIdsForPlan(int $planId): array
{
    $userId = (int) $this->session->get('member_userid');
    if ($userId <= 0 || $planId <= 0) {
        return [];
    }

    // Primary mapping: user_roles.roleID -> roles.id.
    $primary = $this->db->table('user_roles ur')
        ->distinct()
        ->select('r.role_name_id')
        ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
        ->where('ur.userID', $userId)
        ->get()
        ->getResultArray();

    $roleNameIds = array_map(static function ($row) {
        return (int) ($row['role_name_id'] ?? 0);
    }, $primary);
    $roleNameIds = array_values(array_filter($roleNameIds));

    if (!empty($roleNameIds)) {
        return array_values(array_unique($roleNameIds));
    }

    // Legacy mapping: user_roles.roleID -> roles.role_name_id.
    $legacy = $this->db->table('user_roles ur')
        ->distinct()
        ->select('r.role_name_id')
        ->join('roles r', 'r.role_name_id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
        ->where('ur.userID', $userId)
        ->get()
        ->getResultArray();

    $roleNameIds = array_map(static function ($row) {
        return (int) ($row['role_name_id'] ?? 0);
    }, $legacy);

    return array_values(array_unique(array_filter($roleNameIds)));
}

private function getDescendantRoleNameIdsForCurrentUser(int $planId, bool $includeSelf = false): array
{
    $rootRoleNameIds = $this->getCurrentUserRoleNameIdsForPlan($planId);
    if (empty($rootRoleNameIds)) {
        return [];
    }

    $children = $this->loadRoleNameTree();
    $allowed = [];

    foreach ($rootRoleNameIds as $rootId) {
        foreach ($this->collectDescendants($children, (int) $rootId, $includeSelf) as $desc) {
            $allowed[$desc] = true;
        }
    }

    return array_map('intval', array_keys($allowed));
}


private function getRoleLevelName($level)
{
    $levels = $this->getAllLevelNames();
    return $levels[$level] ?? 'Level ' . $level;
}

private function getAllLevelNames()
{
    return [
        1 => '🔹 Super Admin',
        2 => '🔸 Administrator',
        3 => '📋 Manager/Coordinator',
        4 => '📚 Teacher/Faculty',
        5 => '👥 Staff/Assistant',
        6 => '🔰 Support Staff',
        999 => '📌 Custom Role'
    ];
}

    /**
     * Server-side list for employee DataTables (status + optional teachers-only).
     *
     * @param mixed $status '1' active, '0' dropped, 'all' both
     */
    private function usersListBuilder(int $campusId, $status, string $roleFilter, string $searchValue)
    {
        $planId = $this->getCampusPlanId($campusId);

        $builder = $this->db->table('users u');
        $builder->where('u.campus_id', $campusId);

        if ($status !== 'all' && $status !== '') {
            $builder->where('u.status', (int) $status);
        }

        if ($searchValue !== '') {
            $builder->groupStart()
                ->like('u.username', $searchValue)
                ->orLike('u.email', $searchValue)
                ->orLike('u.first_name', $searchValue)
                ->orLike('u.last_name', $searchValue)
                ->orLike('u.mobile_no', $searchValue)
                ->groupEnd();
        }

        if ($roleFilter === 'teachers') {
            if ($planId <= 0) {
                $builder->where('1 = 0', null, false);
            } else {
                $sub = $this->db->table('user_roles ur')
                    ->select('ur.userID')
                    ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
                    ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
                    ->groupStart()
                    ->where('LOWER(rn.rolename) LIKE', '%teacher%', false)
                    ->orWhere('LOWER(rn.rolename) LIKE', '%faculty%', false)
                    ->groupEnd()
                    ->groupBy('ur.userID');
                $inSql = $sub->getCompiledSelect();
                $builder->where('u.id IN (' . $inSql . ')', null, false);
            }
        }

        return $builder;
    }

    protected function getCampusPlanId(int $campusId): int
    {
        helper('role');

        return getRolePlanId();
    }

    private function countUsersList(int $campusId, $status, string $roleFilter, string $searchValue): int
    {
        $b = $this->usersListBuilder($campusId, $status, $roleFilter, $searchValue);

        return (int) $b->countAllResults(false);
    }

    public function data()
    {
        $campusId = (int) $this->session->get('member_campusid');
        $planId = $this->getCampusPlanId($campusId);
        $status = $this->request->getVar('status');
        $roleFilter = $this->request->getVar('role_filter') ?? 'all';
        if (! in_array($roleFilter, ['all', 'teachers'], true)) {
            $roleFilter = 'all';
        }

        $draw = $this->request->getVar('draw');
        $start = (int) $this->request->getVar('start');
        $length = (int) $this->request->getVar('length');
        $searchValue = trim($this->request->getVar('search')['value'] ?? '');

        if ($status === null || $status === '') {
            $status = '1';
        }

        $recordsTotal = $this->countUsersList($campusId, $status, $roleFilter, '');
        $recordsFiltered = $this->countUsersList($campusId, $status, $roleFilter, $searchValue);

        $builderData = $this->usersListBuilder($campusId, $status, $roleFilter, $searchValue);
        $builderData->select('u.id, u.username, u.email, u.photo, u.first_name, u.last_name, u.mobile_no, u.mobile_no2, u.emergency_contact_no, u.status, u.designation');
        $builderData->orderBy('u.id', 'DESC');
        if ($length > 0) {
            $builderData->limit($length, $start);
        }
        $users = $builderData->get()->getResult();

        $response = [
            'draw'            => intval($draw),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => [],
        ];

        $userIds = array_map(static function ($user) {
            return (int) $user->id;
        }, $users);
        $userRolesMap = $this->getUserRolesMap($userIds, $planId);

        foreach ($users as $user) {
            $roleNames = $userRolesMap[(int) $user->id] ?? [];
            $roleDisplay = !empty($roleNames) ? implode(', ', $roleNames) : 'No Role';
            $fullName = trim($user->first_name . ' ' . $user->last_name);
            if ($fullName === '') {
                $fullName = trim((string) ($user->username ?? '')) ?: 'Employee';
            }

            $mobileAlt = trim((string) ($user->mobile_no2 ?? ''));
            if ($mobileAlt === '') {
                $mobileAlt = trim((string) ($user->emergency_contact_no ?? ''));
            }

            $response['data'][] = [
                'id'          => $user->id,
                'username'    => $user->username,
                'full_name'   => $fullName,
                'email'       => $user->email,
                'role'        => $roleDisplay,
                'mobile_no'   => $user->mobile_no,
                'mobile_alt'  => $mobileAlt,
                'designation' => $user->designation ?? '',
                'status'      => $user->status,
                'photo_url'   => !empty($user->photo) ? $this->getEmployeePhoto($user->photo) : '',
            ];
        }

        return $this->response->setJSON($response);
    }

    private function getUserRolesMap(array $userIds, int $planId): array
    {
        $map = [];
        foreach ($userIds as $uid) {
            $map[(int) $uid] = [];
        }

        if (empty($userIds)) {
            return $map;
        }

        // Primary mapping: user_roles.roleID -> roles.id (scoped to active campus plan).
        if ($planId > 0) {
            $rows = $this->db->table('user_roles ur')
                ->distinct()
                ->select('ur.userID, rn.rolename')
                ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
                ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
                ->whereIn('ur.userID', $userIds)
                ->orderBy('rn.rolename', 'ASC')
                ->get()
                ->getResult();
        } else {
            $rows = [];
        }

        foreach ($rows as $row) {
            $uid = (int) ($row->userID ?? 0);
            $name = trim((string) ($row->rolename ?? ''));
            if ($uid > 0 && $name !== '' && !in_array($name, $map[$uid] ?? [], true)) {
                $map[$uid][] = $name;
            }
        }

        // Legacy fallback: user_roles.roleID stored as roles.role_name_id.
        $needFallback = array_filter($map, static function ($roles) {
            return empty($roles);
        });

        if (!empty($needFallback) && $planId > 0) {
            $fallbackUserIds = array_keys($needFallback);
            $legacyRows = $this->db->table('user_roles ur')
                ->distinct()
                ->select('ur.userID, rn.rolename')
                ->join('roles r', 'r.role_name_id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
                ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
                ->whereIn('ur.userID', $fallbackUserIds)
                ->orderBy('rn.rolename', 'ASC')
                ->get()
                ->getResult();

            foreach ($legacyRows as $row) {
                $uid = (int) ($row->userID ?? 0);
                $name = trim((string) ($row->rolename ?? ''));
                if ($uid > 0 && $name !== '' && !in_array($name, $map[$uid] ?? [], true)) {
                    $map[$uid][] = $name;
                }
            }
        }

        return $map;
    }

    private function getUserRoleDetails(int $userId, int $planId): array
    {
        if ($userId <= 0 || $planId <= 0) {
            return [];
        }

        $roles = [];
        $seenIds = [];

        $primary = $this->db->table('user_roles ur')
            ->distinct()
            ->select('r.id, r.issys, rn.rolename, rn.detail')
            ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
            ->where('ur.userID', $userId)
            ->orderBy('rn.rolename', 'ASC')
            ->get()
            ->getResult();

        foreach ($primary as $role) {
            $rid = (int) ($role->id ?? 0);
            if ($rid > 0 && !isset($seenIds[$rid])) {
                $seenIds[$rid] = true;
                $role->level = $this->getRoleLevel($role);
                $roles[] = $role;
            }
        }

        if (empty($roles)) {
            $legacy = $this->db->table('user_roles ur')
                ->distinct()
                ->select('r.id, r.issys, rn.rolename, rn.detail')
                ->join('roles r', 'r.role_name_id = ur.roleID AND r.plan_id = ' . (int) $planId, 'inner')
                ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
                ->where('ur.userID', $userId)
                ->orderBy('rn.rolename', 'ASC')
                ->get()
                ->getResult();

            foreach ($legacy as $role) {
                $rid = (int) ($role->id ?? 0);
                if ($rid > 0 && !isset($seenIds[$rid])) {
                    $seenIds[$rid] = true;
                    $role->level = $this->getRoleLevel($role);
                    $roles[] = $role;
                }
            }
        }

        usort($roles, static function ($a, $b) {
            $levelCmp = ((int) ($a->level ?? 999)) <=> ((int) ($b->level ?? 999));
            if ($levelCmp !== 0) {
                return $levelCmp;
            }

            return strcasecmp((string) ($a->rolename ?? ''), (string) ($b->rolename ?? ''));
        });

        return $roles;
    }

    /**
     * Subject assignments for teacher profile (campus-scoped, correct joins).
     *
     * @return list<object>
     */
    private function getTeacherProfileSubjects(int $teacherId, int $campusId): array
    {
        if ($teacherId <= 0 || $campusId <= 0) {
            return [];
        }

        return $this->db->table('teacher_subjects ts')
            ->select('s.subject_name, c.class_name, sec.section_name, ts.created_date, ts.cls_sec_id, ts.sec_sub_id')
            ->join('section_subjects ss', 'ts.sec_sub_id = ss.sec_sub_id')
            ->join('allsubject s', 'ss.subject_id = s.sid')
            ->join('class_section cs', 'ss.cls_sec_id = cs.cls_sec_id')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('sections sec', 'cs.section_id = sec.section_id')
            ->where('ts.tid', $teacherId)
            ->where('ts.status', 1)
            ->where('ss.status', 1)
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('sec.section_id', 'ASC')
            ->orderBy('s.subject_name', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Distinct class-teacher (section incharge) assignments for teacher profile.
     *
     * @return list<object>
     */
    private function getTeacherProfileClassIncharges(int $teacherId, int $campusId): array
    {
        if ($teacherId <= 0 || $campusId <= 0) {
            return [];
        }

        return $this->db->table('teacher_section ts')
            ->select('ts.cls_sec_id, c.class_name, sec.section_name, MIN(ts.created_date) as created_date')
            ->join('class_section cs', 'ts.cls_sec_id = cs.cls_sec_id')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('sections sec', 'cs.section_id = sec.section_id')
            ->where('ts.tid', $teacherId)
            ->where('ts.status', 1)
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->groupBy('ts.cls_sec_id, c.class_name, sec.section_name, c.class_id, sec.section_id')
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('sec.section_id', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * @param list<object> $subjects
     * @param list<object> $classTeacherInfo
     *
     * @return array{
     *   totalSubjects: int,
     *   totalClasses: int,
     *   totalClassIncharges: int,
     *   totalResponsibilities: int
     * }
     */
    private function buildTeacherProfileStats(array $subjects, array $classTeacherInfo): array
    {
        $subjectSectionIds = [];
        foreach ($subjects as $subject) {
            $sectionId = (int) ($subject->cls_sec_id ?? 0);
            if ($sectionId > 0) {
                $subjectSectionIds[$sectionId] = true;
            }
        }

        $classTeacherSectionIds = [];
        foreach ($classTeacherInfo as $row) {
            $sectionId = (int) ($row->cls_sec_id ?? 0);
            if ($sectionId > 0) {
                $classTeacherSectionIds[$sectionId] = true;
            }
        }

        $allSectionIds = $subjectSectionIds + $classTeacherSectionIds;

        return [
            'totalSubjects'         => count($subjects),
            'totalClasses'          => count($subjectSectionIds),
            'totalClassIncharges'   => count($classTeacherSectionIds),
            'totalResponsibilities' => count($allSectionIds),
        ];
    }

    /**
     * @return array{subjects: list<object>, classTeacherInfo: list<object>, teacherProfileStats: array}
     */
    private function loadTeacherProfileAssignmentData(object $user): array
    {
        $teacherId = (int) ($user->id ?? 0);
        $campusId  = (int) ($user->campus_id ?? 0);
        $subjects  = $this->getTeacherProfileSubjects($teacherId, $campusId);
        $classTeacherInfo = $this->getTeacherProfileClassIncharges($teacherId, $campusId);

        return [
            'subjects'            => $subjects,
            'classTeacherInfo'    => $classTeacherInfo,
            'teacherProfileStats' => $this->buildTeacherProfileStats($subjects, $classTeacherInfo),
        ];
    }

    private function isProfileSalaryReadOnly(object $user): bool
    {
        helper('role');

        return isCurrentUserTeacher((int) ($user->id ?? 0));
    }

    private function mergeUserViewSidebarData(array $data): array
    {
        $user = $data['user'] ?? null;
        if (!$user) {
            $data['userRoleNames'] = [];
            $data['userRoles'] = [];
            $data['passwordDisplay'] = '';
            $data['passwordIsEncrypted'] = false;
            return $data;
        }

        $userId = (int) ($user->id ?? 0);
        $campusId = (int) ($user->campus_id ?? 0);
        $data['salaryReadOnly']      = $this->isProfileSalaryReadOnly($user);
        $data['isSelfProfile']       = isSelfProfile($userId);
        $data['canFullEditProfile']  = canFullEditEmployeeProfile();
        $planId = $this->getCampusPlanId($campusId);
        $roleMap = $this->getUserRolesMap([$userId], $planId);
        $userRoles = $this->getUserRoleDetails($userId, $planId);
        $storedPassword = (string) ($user->password ?? '');
        $data['userRoleNames'] = $roleMap[$userId] ?? [];
        $data['userRoles'] = $userRoles;
        $data['passwordIsEncrypted'] = $this->isStoredPasswordHash($storedPassword);
        $data['passwordDisplay']     = ($data['isSelfProfile'] || ! canFullEditEmployeeProfile() || $data['passwordIsEncrypted'])
            ? ''
            : $storedPassword;

        if (empty($data['photoUrl'])) {
            $photoUrl = base_url('resource/adminlte/dist/img/emp-avatar.jpg');
            if (!empty($user->photo)) {
                $photoPath = FCPATH . 'uploads/employees/' . $user->photo;
                if (file_exists($photoPath)) {
                    $photoUrl = base_url('uploads/employees/' . $user->photo);
                }
            }
            $data['photoUrl'] = $photoUrl;
        }

        return $data;
    }

    private function isStoredPasswordHash(string $stored): bool
    {
        if ($stored === '') {
            return false;
        }

        return (password_get_info($stored)['algo'] ?? 0) > 0;
    }

    private function canAssignMultipleRoles()
    {
        // Super Admin can assign multiple roles
        if ($this->currentUserLevel <= 2) {
            return true;
        }
        return false;
    }

    private function currentUserRequiresOldPasswordForUserPasswordChange(): bool
    {
        $roles = $this->getCurrentUserRoleNames();
        if (empty($roles)) {
            return true;
        }

        $isTeacher = false;
        $hasPrivilegedRole = false;

        foreach ($roles as $name) {
            $n = strtolower(trim((string) $name));
            if ($n === '') {
                continue;
            }
            if (strpos($n, 'teacher') !== false || strpos($n, 'faculty') !== false) {
                $isTeacher = true;
            }
            if (strpos($n, 'principal') !== false || strpos($n, 'director campus') !== false || strpos($n, 'director system') !== false) {
                $hasPrivilegedRole = true;
            }
        }

        if ($hasPrivilegedRole) {
            return false;
        }

        return $isTeacher;
    }

    private function getCurrentUserRoleNames(): array
    {
        $userId = (int) $this->session->get('member_userid');
        if ($userId <= 0) {
            return [];
        }

        $rows = $this->db->table('user_roles ur')
            ->distinct()
            ->select('rn.rolename')
            ->join('roles r', 'r.id = ur.roleID', 'left')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'left')
            ->where('ur.userID', $userId)
            ->get()
            ->getResultArray();

        $names = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['rolename'] ?? ''));
            if ($name !== '' && !in_array($name, $names, true)) {
                $names[] = $name;
            }
        }

        return $names;
    }

  
    protected function canAssignRole($roleId, ?int $campusId = null)
    {
        $role = $this->db->table('roles r')
            ->select('r.id, r.plan_id, r.role_name_id, r.issys, rn.rolename')
            ->join('role_name rn', 'r.role_name_id = rn.role_name_id')
            ->where('r.id', $roleId)
            ->get()
            ->getRow();

        if (!$role) return false;

        $campusId = $campusId ?: (int) $this->session->get('member_campusid');
        $campusPlanId = $this->getCampusPlanId($campusId);
        if ($campusPlanId > 0 && (int) $role->plan_id !== $campusPlanId) {
            return false;
        }

        $allowedRoleNameIds = $this->getDescendantRoleNameIdsForCurrentUser((int) $role->plan_id, false);
        return in_array((int) $role->role_name_id, $allowedRoleNameIds, true);
    }

    public function toggleStatus()
    {
        $userId = (int) $this->request->getPost('id');
        $status = (int) $this->request->getPost('status');
        $campusId = (int) $this->session->get('member_campusid');

        $user = $this->db->table('users')
            ->where('id', $userId)
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();

        if (! $user) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Employee not found']);
        }

        $this->db->table('users')->where('id', $userId)->where('campus_id', $campusId)->update([
            'status'       => $status ? 1 : 0,
            'updated_date' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    // View Methods

public function view($id)
{
    $id = (int) $id;
    if ($deny = $this->assertCanViewProfile($id)) {
        return $deny;
    }

    $db = $this->db ?? \Config\Database::connect();
    
    // Get user data WITHOUT campus filter first
    $user = $db->table('users')->where('id', $id)->get()->getRow();
    
    if (!$user) {
        return redirect()->to('admin/dashboard')->with('error', 'User not found');
    }
    
    // Generate photo URL - CHECKING IN CORRECT DIRECTORY
    $photoUrl = base_url('resource/adminlte/dist/img/emp-avatar.jpg');
    if (!empty($user->photo)) {
        // Check in uploads/employees/ directory (where your save method saves)
        $photoPath = FCPATH . 'uploads/employees/' . $user->photo;
        if (file_exists($photoPath)) {
            $photoUrl = base_url('uploads/employees/' . $user->photo);
        }
    }
    
    // Get the requested tab from URL parameter - DEFAULT TO PROFILE
    $tab = $this->request->getGet('tab') ?? 'profile';
    
    // Prepare data array
    $data = [
        'user' => $user,
        'photoUrl' => $photoUrl,  // Pass the correct photo URL
        'activeTab' => $tab
    ];

    // ONLY load QR data if tab is 'qr'
    if ($tab == 'qr') {
        $qrData = $db->table('teacher_qr_codes')
            ->where('teacher_id', $id)
            ->get()
            ->getRow();
        
        if (!$qrData) {
            $qr_string = 'TCHR_' . $user->id . '_' . md5($user->email . time());
            $db->table('teacher_qr_codes')->insert([
                'teacher_id' => $user->id,
                'qr_code' => $qr_string,
                'campus_id' => $user->campus_id,
                'generated_at' => date('Y-m-d H:i:s'),
                'is_active' => 1
            ]);
            $qrData = $db->table('teacher_qr_codes')
                ->where('teacher_id', $id)
                ->get()
                ->getRow();
        }
        
        $data['qr'] = $qrData;
        
        // Generate QR image for display
        try {
            if (class_exists('\\Endroid\\QrCode\\QrCode')) {
                $qrCode = new \Endroid\QrCode\QrCode($qrData->qr_code);
                if (method_exists($qrCode, 'setSize')) {
                    $qrCode->setSize(200);
                }
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qrCode);
                $data['qr_image_base64'] = 'data:image/png;base64,' . base64_encode($result->getString());
            }
        } catch (\Exception $e) {
            log_message('error', 'QR generation error: ' . $e->getMessage());
            $data['qr_error'] = $e->getMessage();
        }
    }
    
    // For other tabs, load their respective data
    switch ($tab) {
        case 'subjects':
            $assignmentData = $this->loadTeacherProfileAssignmentData($user);
            $data['subjects'] = $assignmentData['subjects'];
            $data['classTeacherInfo'] = $assignmentData['classTeacherInfo'];
            $data['teacherProfileStats'] = $assignmentData['teacherProfileStats'];
            break;
            
        case 'salary':
            $salaryModel = new \App\Models\SalaryModel();
            $data['currentSalary'] = $user->basic_salary ?? 0;
            $data['salaryHistory'] = $salaryModel->getSalaryHistory($id);
            $data['salarySlips'] = $salaryModel->getSalarySlips($id);
            $data['employeeRules'] = $salaryModel->getEmployeeRules($id);
            $data['campusSettings'] = $salaryModel->getCampusSettings($user->campus_id);
            break;
            
        case 'attendance':
            $data['attendance'] = $db->table('attendance_employee')
                ->where('emp_id', $id)
                ->orderBy('date', 'DESC')
                ->limit(30)
                ->get()
                ->getResult();
            break;
    }
    
    return view('admin/user_views/users_view', $this->mergeUserViewSidebarData($data));
}

    public function subjects($id)
    {
        $id = (int) $id;
        if ($deny = $this->assertCanViewProfile($id)) {
            return $deny;
        }

        $db = $this->db ?? \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        
        if (!$user) {
            return redirect()->to('admin/dashboard')->with('error', 'User not found');
        }
        
        $assignmentData = $this->loadTeacherProfileAssignmentData($user);

        return view('admin/user_views/users_view', $this->mergeUserViewSidebarData([
            'user' => $user,
            'subjects' => $assignmentData['subjects'],
            'classTeacherInfo' => $assignmentData['classTeacherInfo'],
            'teacherProfileStats' => $assignmentData['teacherProfileStats'],
            'activeTab' => 'subjects'
        ]));
    }

    public function timetable($id)
    {
        $id = (int) $id;
        if ($deny = $this->assertCanViewProfile($id)) {
            return $deny;
        }

        $db = $this->db ?? \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        
        if (!$user) {
            return redirect()->to('admin/dashboard')->with('error', 'User not found');
        }
        
        $timeTable = $db->table('time_table tt')
            ->select('tt.*, c.class_name, sec.section_name, s.subject_name, sl.start_time, sl.end_time, sl.slot_name')
            ->join('class_section cs', 'tt.cls_sec_id = cs.cls_sec_id')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('sections sec', 'cs.section_id = sec.section_id')
            ->join('slots sl', 'tt.slot_id = sl.slot_id')
            ->join('allsubject s', 'tt.subject_id = s.sid')
            ->where('tt.user_id', $id)
            ->orderBy('FIELD(tt.day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
            ->orderBy('sl.start_time')
            ->get()
            ->getResult();
        
        $schedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($days as $day) {
            $schedule[$day] = [];
        }
        
        foreach ($timeTable as $entry) {
            $schedule[$entry->day][] = $entry;
        }
        
        return view('admin/user_views/users_view', $this->mergeUserViewSidebarData([
            'user' => $user,
            'schedule' => $schedule,
            'days' => $days,
            'activeTab' => 'timetable'
        ]));
    }

  
}
