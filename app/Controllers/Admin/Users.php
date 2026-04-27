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
        helper(['form', 'url']);
        
        // Get current user's role and level
        $this->setCurrentUserRoleInfo();
    }


    /**
 * Salary Management Tab
 */
public function salary($id)
{
    $db = $this->db ?? \Config\Database::connect();
    $user = $db->table('users')->where('id', $id)->get()->getRow();
    
    if (!$user) {
        return redirect()->to('admin/users')->with('error', 'User not found');
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
    
    return view('admin/user_views/users_view', [
        'user' => $user,
        'currentSalary' => $currentSalary,
        'salaryHistory' => $salaryHistory,
        'salarySlips' => $salarySlips,
        'employeeRules' => $employeeRules,
        'campusSettings' => $campusSettings,
        'activeTab' => 'salary'
    ]);
}

/**
 * Update employee basic salary
 */
public function updateSalary()
{
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
 * View salary slip
 */
public function viewSalarySlip($id, $slipId)
{
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
            // Log the payment transaction
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
    
    return view('admin/user_views/users_view', $data);
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
            // First, remove this subject from any other teacher
            $db->table('teacher_subjects')
                ->where('sec_sub_id', $secSubId)
                ->where('status', 1)
                ->update(['status' => 0, 'updated_date' => date('Y-m-d H:i:s')]);
            
            // Then assign to new teacher
            $db->table('teacher_subjects')->insert([
                'tid' => $teacherId,
                'sec_sub_id' => $secSubId,
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
        'levelNames' => $this->getAllLevelNames(),
        'availableSubjects' => $availableSubjects,
        'availableClasses' => $availableClasses,
        'selectedSubjects' => [],
        'selectedClassTeachers' => []
    ]);
}



public function edit($id)
{
    $db = $this->db ?? \Config\Database::connect();
    $campusId = $this->session->get('member_campusid');
    
    $user = $db->table('users')->where('id', $id)->get()->getRow();
    
    if (!$user) {
        return redirect()->to('admin/users')->with('error', 'User not found');
    }
    
    $empSalary = $db->table('emp_salary')->where(['emp_id' => $id, 'status' => 1])->get()->getRow();

    // Get user's current roles
    $userRoles = $db->table('user_roles ur')
        ->select('ur.roleID, r.issys, rn.rolename, rn.detail')
        ->join('roles r', 'ur.roleID = r.id')
        ->join('role_name rn', 'r.role_name_id = rn.role_name_id')
        ->where('ur.userID', $id)
        ->get()
        ->getResult();
    
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

    $assignableRoles = $this->getAssignableRoles();
    $levelNames = $this->getAllLevelNames();
    
    // Get available subjects and classes
    $availableSubjects = $this->getAvailableSubjects($campusId, $id);
    $availableClasses = $this->getAvailableClassTeachers($campusId, $id);

    return view('admin/users_edit', [
        'info' => $user,
        'emp_salary_info' => $empSalary,
        'assignableRoles' => $assignableRoles,
        'selectedRoleIds' => $selectedRoleIds,
        'selectedRoleDetails' => $selectedRoleDetails,
        'currentUserLevel' => $this->currentUserLevel,
        'currentUserRoleName' => $this->currentUserRole->rolename ?? 'Unknown',
        'canAssignMultipleRoles' => $this->canAssignMultipleRoles(),
        'levelNames' => $levelNames,
        'availableSubjects' => $availableSubjects,
        'availableClasses' => $availableClasses,
        'selectedSubjects' => $selectedSubjects,
        'selectedClassTeachers' => $selectedClassTeachers
    ]);
}

/**
 * Update the save method to handle subject and class assignments
 */
public function save()
{
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
        if (empty($selectedRoles) && $this->request->getPost('role_id')) {
            $selectedRoles = [$this->request->getPost('role_id')];
        }
        
        // Get selected subjects and classes
        $selectedSubjects = $this->request->getPost('subjects') ?: [];
        $selectedClassTeachers = $this->request->getPost('class_teachers') ?: [];
        
        // Validate role permissions
        if (!empty($selectedRoles)) {
            foreach ($selectedRoles as $roleId) {
                if (!$this->canAssignRole($roleId)) {
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
                $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
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
                
                // Save salary to emp_salary table
                $salary = $this->request->getPost('salary');
                if ($salary) {
                    $db->table('emp_salary')->insert([
                        'emp_id' => $newUserId,
                        'salary' => $salary,
                        'status' => 1,
                        'date' => date('Y-m-d'),
                        'created_date' => date('Y-m-d H:i:s'),
                        'user_id' => $userId
                    ]);
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
                if (!$db->table('users')->where('id', $id)->update($data)) {
                    throw new \Exception('Failed to update user data');
                }
                $newUserId = $id;
                
                // Handle role assignments
                if (!empty($selectedRoles)) {
                    $existingRoles = $db->table('user_roles')
                        ->where('userID', $id)
                        ->get()
                        ->getResult();
                    
                    $existingRoleIds = array_map(function($role) {
                        return $role->roleID;
                    }, $existingRoles);
                    
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
                }
                
                // Update salary
                $salary = $this->request->getPost('salary');
                if ($salary) {
                    $existingSalary = $db->table('emp_salary')
                        ->where(['emp_id' => $id, 'status' => 1])
                        ->get()
                        ->getRow();
                        
                    if ($existingSalary) {
                        $db->table('emp_salary')
                            ->where('salary_id', $existingSalary->salary_id)
                            ->update([
                                'salary' => $salary,
                                'updated_date' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        $db->table('emp_salary')->insert([
                            'emp_id' => $id,
                            'salary' => $salary,
                            'status' => 1,
                            'date' => date('Y-m-d'),
                            'created_date' => date('Y-m-d H:i:s'),
                            'user_id' => $userId
                        ]);
                    }
                }
                
                // Save subject assignments if method exists
                if (method_exists($this, 'saveSubjectAssignments')) {
                    $subjectResult = $this->saveSubjectAssignments($id, $selectedSubjects);
                    if (!$subjectResult['success']) {
                        throw new \Exception($subjectResult['message']);
                    }
                }
                
                // Save class teacher assignments if method exists
                if (method_exists($this, 'saveClassTeacherAssignments')) {
                    $classResult = $this->saveClassTeacherAssignments($id, $selectedClassTeachers);
                    if (!$classResult['success']) {
                        throw new \Exception($classResult['message']);
                    }
                }
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Database transaction failed');
            }
            
            log_message('debug', 'User saved successfully. ID: ' . $newUserId);
            
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
    $db = $this->db ?? \Config\Database::connect();
    $campusId = $this->session->get('member_campusid');
    
    // Get current assignments
    $currentSubjects = $db->table('teacher_subjects')
        ->select('sec_sub_id')
        ->where('tid', $teacherId)
        ->where('status', 1)
        ->get()
        ->getResult();
    
    $selectedSubjectIds = array_map(function($item) {
        return $item->sec_sub_id;
    }, $currentSubjects);
    
    // Get available subjects with assignment status
    $subjects = $db->table('section_subjects ss')
        ->select('ss.sec_sub_id, cs.cls_sec_id, c.class_name, s.section_name, sub.subject_name,
                  (SELECT tid FROM teacher_subjects WHERE sec_sub_id = ss.sec_sub_id AND status = 1 LIMIT 1) as assigned_teacher_id,
                  (SELECT CONCAT(u.first_name, " ", u.last_name) FROM teacher_subjects ts 
                   JOIN users u ON ts.tid = u.id 
                   WHERE ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1 LIMIT 1) as assigned_teacher_name')
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
        $response[] = [
            'sec_sub_id' => $subject->sec_sub_id,
            'cls_sec_id' => $subject->cls_sec_id,
            'class_name' => $subject->class_name,
            'section_name' => $subject->section_name,
            'subject_name' => $subject->subject_name,
            'is_selected' => in_array($subject->sec_sub_id, $selectedSubjectIds),
            'assigned_teacher_name' => $subject->assigned_teacher_name,
            'assigned_teacher_id' => $subject->assigned_teacher_id
        ];
    }
    
    return $this->response->setJSON($response);
}

/**
 * AJAX endpoint to assign/unassign subject to teacher
 */

public function assignSubject()
{
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
        $status = $this->request->getGet('status') ?? '1';
        return view('admin/users', ['status' => $status]);
    }

private function getAssignableRoles()
{
    $campusId = $this->session->get('member_campusid');
    $campusBill = $this->db->table('campus_bills')
        ->where(['status' => 1, 'campus_id' => $campusId])
        ->get()
        ->getRow();
    $planId = $campusBill->plan_id ?? 1;

    // Get all roles with their level
    $builder = $this->db->table('roles r')
        ->select('r.id, r.role_name_id, r.issys, rn.rolename, rn.detail')
        ->join('role_name rn', 'r.role_name_id = rn.role_name_id')
        ->where('r.plan_id', $planId);

    $allRoles = $builder->get()->getResult();
    
    // Calculate level for each role and add as property
    $rolesWithLevel = [];
    foreach ($allRoles as $role) {
        $role->level = $this->getRoleLevel($role);
        $rolesWithLevel[] = $role;
    }
    
    // Sort roles by level (lower level = higher position)
    usort($rolesWithLevel, function($a, $b) {
        if ($a->level == $b->level) {
            return strcmp($a->rolename, $b->rolename);
        }
        return $a->level - $b->level;
    });

    // If current user is not Super Admin, filter roles they can assign
    if ($this->currentUserLevel > 1) {
        $assignableRoles = [];
        foreach ($rolesWithLevel as $role) {
            // Can only assign roles at the same level or lower (higher number = lower level)
            if ($role->level >= $this->currentUserLevel) {
                $assignableRoles[] = $role;
            }
        }
        return $assignableRoles;
    }

    // Super Admin can assign all roles except Super Admin itself
    return array_filter($rolesWithLevel, function($role) {
        // Super Admin can't create another Super Admin
        return !($role->issys == 1 && stripos($role->rolename, 'super') !== false);
    });
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

    public function data()
    {
        $campusId = $this->session->get('member_campusid');
        $status = $this->request->getVar('status');
        $draw = $this->request->getVar('draw');
        $start = $this->request->getVar('start');
        $length = $this->request->getVar('length');
        $searchValue = $this->request->getVar('search')['value'] ?? '';

        // Get total records
        $builder = $this->db->table('users');
        $builder->select('COUNT(id) as total');
        $builder->where('campus_id', $campusId);
        $builder->where('status', $status);

        if ($searchValue !== '') {
            $builder->groupStart()
                ->like('username', $searchValue)
                ->orLike('email', $searchValue)
                ->orLike('first_name', $searchValue)
                ->orLike('last_name', $searchValue)
                ->orLike('mobile_no', $searchValue)
                ->groupEnd();
        }

        $totalRecords = $builder->get()->getRow()->total;

        // Get filtered data
        $builder = $this->db->table('users');
        $builder->select('id, username, email,photo, first_name, last_name, mobile_no, mobile_no2, emergency_contact_no, status, designation');
        $builder->where('campus_id', $campusId);
        $builder->where('status', $status);

        if ($searchValue !== '') {
            $builder->groupStart()
                ->like('username', $searchValue)
                ->orLike('email', $searchValue)
                ->orLike('first_name', $searchValue)
                ->orLike('last_name', $searchValue)
                ->orLike('mobile_no', $searchValue)
                ->groupEnd();
        }

        $builder->orderBy('id', 'DESC');
        $builder->limit($length, $start);
        $users = $builder->get()->getResult();

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => []
        ];

        foreach ($users as $user) {
            // Get user role
            $roleName = $this->getUserRole($user->id);
            
            $response['data'][] = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => trim($user->first_name . ' ' . $user->last_name),
                'email' => $user->email,
                'role' => $roleName,
                'mobile_no' => $user->mobile_no,
                'designation' => $user->designation ?? '',
                'status' => $user->status
            ];
        }

        return $this->response->setJSON($response);
    }

    private function getUserRole($userId)
    {
        $userRoles = $this->db->table('user_roles')->where('userID', $userId)->get()->getRow();
        if (!$userRoles) return 'No Role';
        
        $campusBill = $this->db->table('campus_bills')
            ->where(['status' => 1, 'campus_id' => $this->session->get('member_campusid')])
            ->get()
            ->getRow();
        $planId = $campusBill->plan_id ?? null;
        
        $role = $this->db->table('roles')
            ->where(['role_name_id' => $userRoles->roleID ?? 0, 'plan_id' => $planId])
            ->get()
            ->getRow();
        
        if ($role) {
            $roleData = $this->db->table('role_name')
                ->where('role_name_id', $role->role_name_id)
                ->get()
                ->getRow();
            return $roleData->rolename ?? 'Unknown';
        }
        
        return 'Unknown';
    }

   

    private function canAssignMultipleRoles()
    {
        // Super Admin can assign multiple roles
        if ($this->currentUserLevel <= 2) {
            return true;
        }
        return false;
    }

  
    private function canAssignRole($roleId)
    {
        $role = $this->db->table('roles r')
            ->select('r.*, rn.rolename')
            ->join('role_name rn', 'r.role_name_id = rn.role_name_id')
            ->where('r.id', $roleId)
            ->get()
            ->getRow();
        
        if (!$role) return false;
        
        $roleLevel = $this->getRoleLevel($role);
        
        // Can assign roles at same level or lower
        return $roleLevel >= $this->currentUserLevel;
    }

    public function toggleStatus()
    {
        $userId = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        
        $this->db->table('users')->where('id', $userId)->update([
            'status' => $status,
            'updated_date' => date('Y-m-d H:i:s')
        ]);
        
        return $this->response->setJSON(['success' => true]);
    }

    // View Methods

public function view($id)
{
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
            $data['subjects'] = $db->table('teacher_subjects')
                ->select('teacher_subjects.*, allsubject.subject_name, classes.class_name, sections.section_name')
                ->join('allsubject', 'allsubject.sid = teacher_subjects.sec_sub_id')
                ->join('class_section', 'class_section.cls_sec_id = teacher_subjects.cls_sec_id')
                ->join('classes', 'classes.class_id = class_section.class_id')
                ->join('sections', 'sections.section_id = class_section.section_id')
                ->where('teacher_subjects.tid', $id)
                ->where('teacher_subjects.status', 1)
                ->get()
                ->getResult();
            break;
            
        case 'salary':
            $data['salaries'] = $db->table('emp_salary')
                ->where('emp_id', $id)
                ->orderBy('date', 'DESC')
                ->get()
                ->getResult();
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
    
    return view('admin/user_views/users_view', $data);
}

    public function subjects($id)
    {
        $db = $this->db ?? \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        
        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found');
        }
        
        // Get subjects taught
        $subjects = $db->table('teacher_subjects ts')
            ->select('s.subject_name, c.class_name, sec.section_name, ts.created_date')
            ->join('section_subjects ss', 'ts.sec_sub_id = ss.sec_sub_id')
            ->join('allsubject s', 'ss.subject_id = s.sid')
            ->join('class_section cs', 'ss.cls_sec_id = cs.cls_sec_id')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('sections sec', 'cs.section_id = sec.section_id')
            ->where('ts.tid', $id)
            ->where('ts.status', 1)
            ->orderBy('c.class_id, sec.section_id, s.subject_name')
            ->get()
            ->getResult();
        
        // Get class teacher assignments
        $classTeacherInfo = $db->table('teacher_section ts')
            ->select('c.class_name, sec.section_name, ts.created_date, ts.cls_sec_id')
            ->join('class_section cs', 'ts.cls_sec_id = cs.cls_sec_id')
            ->join('classes c', 'cs.class_id = c.class_id')
            ->join('sections sec', 'cs.section_id = sec.section_id')
            ->where('ts.tid', $id)
            ->where('ts.status', 1)
            ->get()
            ->getResult();
        
        return view('admin/user_views/users_view', [
            'user' => $user,
            'subjects' => $subjects,
            'classTeacherInfo' => $classTeacherInfo,
            'activeTab' => 'subjects'
        ]);
    }

    public function timetable($id)
    {
        $db = $this->db ?? \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        
        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found');
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
        
        return view('admin/user_views/users_view', [
            'user' => $user,
            'schedule' => $schedule,
            'days' => $days,
            'activeTab' => 'timetable'
        ]);
    }

  
}