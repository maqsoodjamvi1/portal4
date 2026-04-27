<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StudentModel;
use App\Models\SLCModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\GenericModel;

class AddBulkStudents extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        helper(['form', 'url', 'custom']);

        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-students');
    }

   public function add()
{
    // Check if this is an edit request (has edit_student parameter)
    $editStudent = $this->request->getGet('edit_student');
    $returnUrl = $this->request->getGet('return_url');
    
    // For edit mode, we might want to check a different permission or skip
    if ($editStudent) {
        // Option 1: Check a different permission for editing
        // check_permission('admin-edit-student');
        
        // Option 2: Skip permission check for edit mode (if you're confident about security)
        // No permission check needed here - but be careful!
        
        // Option 3: Use the same permission but log for debugging
        // check_permission('admin-add-student');
    } else {
        // For adding new students, check the permission
        check_permission('admin-add-student');
    }

    $user_id = $this->session->get('member_userid');
    $campusid = $this->session->get('member_campusid');
    $sessionid = $this->session->get('member_sessionid');
    $schoolinfo = getSchoolInfo();

    $this->template_data['campus_info'] = $this->db->table('campus')->where('campus_id', $campusid)->get()->getRow();
    $campus_bill_info = $this->db->table('campus_bills')->where(['status' => 1, 'campus_id' => $campusid])->get()->getRow();
    $max_student_limit = $campus_bill_info->max_students ?? 0;

    $students_info = $this->db->query("SELECT COUNT(student_id) AS studentTotal FROM students WHERE student_id IN (SELECT student_id FROM student_class WHERE status = 1) AND campus_id = {$campusid}")->getRow();
    $noOfstudent = $students_info->studentTotal ?? 0;

    $this->template_data['max_limit'] = $noOfstudent >= $max_student_limit ? '<div class="col-lg-12">Maximum Limit Exceeded</div>' : '';
    $this->template_data['sessionData'] = ['campusid' => $campusid, 'sessionid' => $sessionid];

    $this->template_data['classesinfo'] = $this->db->table('classes')->get()->getResult();
    $academic_session = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();
    $sessionName = explode('-', $academic_session->session_name);
    $sessionYear = $sessionName[1] - 1;

    $last_row = $this->db->table('students')->where('session_id', $sessionid)->orderBy('student_id', 'desc')->get()->getRow();
    $last_id = $last_row ? ((int)explode('-', $last_row->reg_no)[2] + 1) : 1;

    if (empty($schoolinfo->reg_text)) {
        echo '<div class="col-lg-12">Reg Text Field is required in system profile</div>';
        echo "<a href='admin/profile_system'>Click Here</a>";
        exit;
    }

    $this->template_data['reg_no'] = "{$sessionYear}-{$schoolinfo->reg_text}-{$last_id}";
    $this->template_data['sectionsclassinfo'] = $this->userClassSections();
    $this->template_data['current_session'] = $academic_session->session_name ?? '';
    
    // Add edit parameters to template data
    $this->template_data['edit_student'] = $editStudent;
    $this->template_data['return_url'] = $returnUrl;
    
    return view('admin/addbulkstudents_edit', $this->template_data);
}



 public function viewSlc($slcId)
    {
        $db = \Config\Database::connect();
        
        // Get SLC data
        $slc = $db->table('school_leaving_certificates')
            ->where('id', $slcId)
            ->get()
            ->getRowArray();
        
        if (!$slc) {
            return redirect()->back()->with('error', 'SLC not found');
        }
        
        $studentId = $slc['student_id'];
        
        // Get student details
        $student = $db->table('students s')
            ->select('s.*, p.f_name, p.m_name, p.father_contact, p.mother_contact, p.emergency_contact, p.religion, p.city as nationality')
            ->join('parents p', 'p.parent_id = s.parent_id', 'left')
            ->where('s.student_id', $studentId)
            ->get()
            ->getRowArray();
        
        // Get ADMISSION CLASS (earliest/lowest sc_id from student_class)
        $admissionClassQuery = $db->query("
            SELECT c.class_name 
            FROM student_class sc
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c ON c.class_id = cs.class_id
            WHERE sc.student_id = ?
            ORDER BY sc.sc_id ASC
            LIMIT 1
        ", [$studentId]);
        
        $admissionClass = $admissionClassQuery->getRowArray();
        
        // Get the last/ most recent class
        $classInfo = $db->query("
            SELECT c.class_name, sec.section_name, sc.session_id, ac.session_name
            FROM student_class sc
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c ON c.class_id = cs.class_id
            LEFT JOIN sections sec ON sec.section_id = cs.section_id
            LEFT JOIN academic_session ac ON ac.session_id = sc.session_id
            WHERE sc.student_id = ?
            ORDER BY sc.sc_id DESC, sc.session_id DESC
            LIMIT 1
        ", [$studentId])->getRowArray();
        
        // Check fee balance
        $balanceQuery = $db->query("SELECT COALESCE(SUM(amount - discount), 0) as total_balance FROM fee_chalan WHERE student_id = ? AND status = 'unpaid'", [$studentId]);
        $balanceResult = $balanceQuery->getRowArray();
        $outstandingBalance = $balanceResult['total_balance'] ?? 0;
        
        // Check if fee is skipped
        $skipFee = $slc['skip_fee'] ?? false;
        $hasDues = !$skipFee && $outstandingBalance > 0;
        
        // Get campus ID from session
        $campusId = (int) $this->session->get('member_campusid');
        
        // Get system information
        $school = getSchoolInfo();
        
        // Get campus information with signature
        $campus = $db->table('campus')
            ->select('campus.*, principal_signature')
            ->where('campus_id', $campusId)
            ->get()
            ->getRowArray();
        
        // Prepare data for view
        $data = [
            'slc' => $slc,
            'student' => $student,
            'admission_class' => $admissionClass['class_name'] ?? null,
            'class' => $classInfo,
            'school' => $school,
            'campus' => $campus,
            'principal_signature' => $campus['principal_signature'] ?? null,
            'outstanding_balance' => $outstandingBalance,
            'has_dues' => $hasDues,
            'skip_fee' => $skipFee,
            'title' => 'School Leaving Certificate'
        ];
        
        return view('admin/slc_view', $data);
    }



public function getEditForm()
{
    if (!$this->request->isAJAX()) {
        return redirect()->back();
    }
    
    $studentId = $this->request->getPost('student_id');
    
    if (!$studentId) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No student ID provided']);
    }
    
    $db = \Config\Database::connect();
    
    // Get student details with parent information
    $student = $db->query("
        SELECT s.*, 
               p.f_name, 
               p.m_name, 
               p.religion,
               p.city as nationality,
               p.father_contact,
               p.mother_contact,
               p.emergency_contact
        FROM students s
        LEFT JOIN parents p ON p.parent_id = s.parent_id
        WHERE s.student_id = ?
    ", [$studentId])->getRowArray();
    
    // Get the last class from student_class
    $classInfo = $db->query("
        SELECT c.class_name, sec.section_name
        FROM student_class sc
        LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        LEFT JOIN classes c ON c.class_id = cs.class_id
        LEFT JOIN sections sec ON sec.section_id = cs.section_id
        WHERE sc.student_id = ?
        ORDER BY sc.sc_id DESC
        LIMIT 1
    ", [$studentId])->getRowArray();
    
    // Get SLC data (leaving date, reason, conduct)
    $slcData = $db->query("
        SELECT leaving_date, leaving_reason, conduct
        FROM school_leaving_certificates
        WHERE student_id = ?
        ORDER BY id DESC
        LIMIT 1
    ", [$studentId])->getRowArray();
    

  

    // Check fee balance
    $balanceQuery = $db->query("SELECT SUM(amount - discount) as total_balance FROM fee_chalan WHERE student_id = ? AND status = 'unpaid'"  , [$studentId]);
    $balanceResult = $balanceQuery->getRowArray();
    $outstandingBalance = $balanceResult['total_balance'] ?? 0;
    $hasDues = $outstandingBalance > 0;
    
    // Pass data to the view
    $data = [
        'student' => $student,
        'class' => $classInfo,
        'slc' => $slcData,
        'student_id' => $studentId,
        'has_dues' => $hasDues,
        'outstanding_balance' => $outstandingBalance
    ];
    
    return view('admin/slc_edit_form', $data);
}


/**
 * Search SLC records by student name
 */
/**
 * Search SLC records by student name
 */

/**
 * Search SLC records by student name - filtered by campus
 */
public function searchSlc()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
    }
    
    $query = $this->request->getPost('query');
    $exact = $this->request->getPost('exact') === '1';
    
    // Get current user's campus ID from session
    $campusId = (int) session('member_campusid');
    
    if (empty($campusId)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No campus assigned']);
    }
    
    if (empty($query) || strlen($query) < 2) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Query too short']);
    }
    
    $db = \Config\Database::connect();
    
    // Clean the query for LIKE clause
    $searchTerm = $db->escapeLikeString($query);
    
    // Search in school_leaving_certificates table - WITH CAMPUS FILTER
    $slcBuilder = $db->table('school_leaving_certificates slc')
        ->select("
            slc.id,
            slc.slc_no,
            slc.full_name,
            slc.first_name,
            slc.last_name,
            slc.student_id,
            slc.created_at,
            'slc' as source,
            slc.class_name,
            slc.section_name
        ")
        ->join('students s', 's.student_id = slc.student_id', 'inner')
        ->where('s.campus_id', $campusId); // CRITICAL: Filter by campus
    
    // Apply search condition
    $slcBuilder->groupStart()
        ->like('slc.full_name', $searchTerm, 'both')
        ->orLike('slc.first_name', $searchTerm, 'both')
        ->orLike('slc.last_name', $searchTerm, 'both')
        ->groupEnd();
    
    $slcResults = $slcBuilder->limit(20)->get()->getResultArray();
    
    // Also search in students table for students without SLC - WITH CAMPUS FILTER
    $studentBuilder = $db->table('students s')
        ->select("
            s.student_id as id,
            NULL as slc_no,
            CONCAT(s.first_name, ' ', s.last_name) as full_name,
            s.first_name,
            s.last_name,
            s.student_id,
            NULL as created_at,
            'student' as source,
            s.status,
            c.class_name,
            sec.section_name,
            s.reg_no
        ")
        ->join('class_section cs', 'cs.cls_sec_id = s.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.campus_id', $campusId) // CRITICAL: Filter by campus
        ->groupStart()
            ->like('s.first_name', $searchTerm, 'both')
            ->orLike('s.last_name', $searchTerm, 'both')
            ->orWhere("CONCAT(s.first_name, ' ', s.last_name) LIKE '%" . $searchTerm . "%'")
        ->groupEnd()
        ->limit(10);
    
    $studentResults = $studentBuilder->get()->getResultArray();
    
    // Log for debugging
    log_message('debug', 'Campus ID: ' . $campusId);
    log_message('debug', 'Search term: ' . $searchTerm);
    log_message('debug', 'SLC results count: ' . count($slcResults));
    log_message('debug', 'Student results count: ' . count($studentResults));
    
    // Merge results
    $results = array_merge($slcResults, $studentResults);
    
    return $this->response->setJSON([
        'success' => true,
        'results' => $results
    ]);
}



public function getStudentDetails()
{
    try {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Invalid request - not AJAX'
            ]);
        }
        
        $studentId = $this->request->getPost('student_id');
        
        if (!$studentId) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'No student ID provided'
            ]);
        }
        
        $db = \Config\Database::connect();
        
        // Get basic student info with parent details
        $sql = "SELECT s.*, 
                       p.f_name,
                       p.m_name,
                       p.religion,
                       p.city as nationality,
                       p.father_contact,
                       p.mother_contact,
                       p.emergency_contact
                FROM students s
                LEFT JOIN parents p ON p.parent_id = s.parent_id
                WHERE s.student_id = ?";
        
        $query = $db->query($sql, [$studentId]);
        
        if ($query && $query->getNumRows() > 0) {
            $student = $query->getRowArray();
            
            // Get the last/ most recent class from student_class table
            $classQuery = $db->query("
                SELECT c.class_name, sec.section_name, sc.session_id, ac.session_name
                FROM student_class sc
                LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
                LEFT JOIN classes c ON c.class_id = cs.class_id
                LEFT JOIN sections sec ON sec.section_id = cs.section_id
                LEFT JOIN academic_session ac ON ac.session_id = sc.session_id
                WHERE sc.student_id = ?
                ORDER BY sc.sc_id DESC, sc.session_id DESC
                LIMIT 1
            ", [$studentId]);
            
            if ($classQuery && $classQuery->getNumRows() > 0) {
                $classData = $classQuery->getRowArray();
                $student['class_name'] = $classData['class_name'] ?? '';
                $student['section_name'] = $classData['section_name'] ?? '';
                $student['session_name'] = $classData['session_name'] ?? '';
            } else {
                // If no class found in student_class, try to get from students table
                $defaultClassQuery = $db->query("
                    SELECT c.class_name, sec.section_name
                    FROM students s
                    LEFT JOIN class_section cs ON cs.cls_sec_id = s.cls_sec_id
                    LEFT JOIN classes c ON c.class_id = cs.class_id
                    LEFT JOIN sections sec ON sec.section_id = cs.section_id
                    WHERE s.student_id = ?
                ", [$studentId]);
                
                if ($defaultClassQuery && $defaultClassQuery->getNumRows() > 0) {
                    $classData = $defaultClassQuery->getRowArray();
                    $student['class_name'] = $classData['class_name'] ?? '';
                    $student['section_name'] = $classData['section_name'] ?? '';
                }
            }
            
            // Add full_name and current_class
            $student['full_name'] = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
            $student['current_class'] = trim(($student['class_name'] ?? '') . ' ' . ($student['section_name'] ?? ''));
            
            return $this->response->setJSON([
                'success' => true,
                'student' => $student
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Student not found'
            ]);
        }
        
    } catch (\Exception $e) {
        log_message('error', 'Exception in getStudentDetails: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Server error'
        ]);
    }
}
    /**
     * Update student info before SLC generation
     */
public function updateStudentInfo()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request - not AJAX']);
    }
    
    $studentId = $this->request->getPost('student_id');
    
    if (!$studentId) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'No student ID provided']);
    }
    
    log_message('debug', 'updateStudentInfo called for student_id: ' . $studentId);
    
    $db = \Config\Database::connect();
    
    $updateData = [
        'first_name' => $this->request->getPost('first_name'),
        'last_name' => $this->request->getPost('last_name'),
        'date_of_birth' => $this->request->getPost('dob'),
        'date_of_admission' => $this->request->getPost('admission_date'),
        'updated_date' => date('Y-m-d H:i:s')
    ];
    
    // Remove empty values
    $updateData = array_filter($updateData, function($value) {
        return $value !== null && $value !== '';
    });
    
    try {
        $db->transStart();
        
        // Update students table
        $db->table('students')
            ->where('student_id', $studentId)
            ->update($updateData);
        
        // Get the parent_id from student
        $student = $db->table('students')
            ->select('parent_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();
        
        // Update parent information if parent_id exists
        if ($student && !empty($student['parent_id'])) {
            $parentUpdate = [];
            
            $fatherName = $this->request->getPost('father_name');
            $motherName = $this->request->getPost('mother_name');
            $religion = $this->request->getPost('religion');
            $nationality = $this->request->getPost('nationality');
            
            if (!empty($fatherName)) $parentUpdate['f_name'] = $fatherName;
            if (!empty($motherName)) $parentUpdate['m_name'] = $motherName;
            if (!empty($religion)) $parentUpdate['religion'] = $religion;
            if (!empty($nationality)) $parentUpdate['city'] = $nationality;
            
            if (!empty($parentUpdate)) {
                $db->table('parents')
                    ->where('parent_id', $student['parent_id'])
                    ->update($parentUpdate);
            }
        }
        
        // Update SLC data
        $leavingDate = $this->request->getPost('leaving_date');
        $leavingReason = $this->request->getPost('leaving_reason');
        $conduct = $this->request->getPost('conduct');
        $skipPendingFee = $this->request->getPost('skip_pending_fee');
        
        $slcRecord = $db->table('school_leaving_certificates')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();
        
        if ($slcRecord) {
            $slcUpdate = [];
            if ($leavingDate) $slcUpdate['leaving_date'] = $leavingDate;
            if ($leavingReason) $slcUpdate['leaving_reason'] = $leavingReason;
            if ($conduct) $slcUpdate['conduct'] = $conduct;
            
            if ($skipPendingFee !== null) {
                $slcUpdate['skip_fee'] = ($skipPendingFee == '1') ? 1 : 0;
            }
            
            $slcUpdate['updated_at'] = date('Y-m-d H:i:s');
            
            if (!empty($slcUpdate)) {
                $db->table('school_leaving_certificates')
                    ->where('student_id', $studentId)
                    ->update($slcUpdate);
            }
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            throw new \Exception('Transaction failed');
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Student information updated successfully',
            'student_id' => $studentId
        ]);
        
    } catch (\Exception $e) {
        $db->transRollback();
        log_message('error', 'Update student info error: ' . $e->getMessage());
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

public function update_student_name()
{
    $student_id = $this->request->getPost('student_id');
    $first_name = $this->request->getPost('first_name');
    $last_name = $this->request->getPost('last_name');
    $user_id = session('member_userid');
    $date = date('Y-m-d H:i:s');
    
    if (!$student_id || !$first_name) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Student ID and First Name are required'
        ]);
    }
    
    $this->db->table('students')
        ->where('student_id', $student_id)
        ->update([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'updated_date' => $date,
            'user_id' => $user_id
        ]);
    
    return $this->response->setJSON([
        'success' => true,
        'msg' => 'Student name updated successfully'
    ]);
}
/**
 * Generate SLC without dropping (for preview/edit flow)
 */
public function generateSlc()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
    }
    
    $studentData = json_decode($this->request->getPost('student_data'), true);
    $dropOption = $this->request->getPost('drop_option');
    
    if (!$studentData) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid student data']);
    }
    
    $db = \Config\Database::connect();
    $db->transStart(); // Start transaction
    
    try {
        $studentId = $studentData['student_id'];
        
        // First, check if student exists
        $student = $db->table('students')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();
            
        if (!$student) {
            throw new \Exception('Student not found');
        }
        
        // Drop the student if option selected
        if ($dropOption === 'drop_with_slc') {
            // Update students table
            $db->table('students')
                ->where('student_id', $studentId)
                ->update([
                    'status' => 4,
                    'leaving_date' => $studentData['leaving_date'] ?? null,
                    'leaving_reason' => $studentData['leaving_reason'] ?? null,
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
            
            // Update student_class table
            $db->table('student_class')
                ->where('student_id', $studentId)
                ->update(['status' => 4]);
        }
        
        // Generate SLC number
        $year = date('Y');
        $prefix = 'SLC/' . $year . '/';
        
        // Get the last SLC number for this year
        $lastSlc = $db->table('school_leaving_certificates')
            ->select('slc_no')
            ->like('slc_no', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
        
        if ($lastSlc) {
            $lastNumber = intval(substr($lastSlc['slc_no'], strlen($prefix)));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        $slcNo = $prefix . $newNumber;
        
        // Get profile photo from student data
        $profilePhoto = $studentData['profile_photo'] ?? $student['profile_photo'] ?? '';
        
        // Prepare SLC data
        $slcData = [
            'student_id' => $studentId,
            'slc_no' => $slcNo,
            'full_name' => $studentData['full_name'] ?? '',
           
            'first_name' => $studentData['first_name'] ?? '',
            'last_name' => $studentData['last_name'] ?? '',
            'father_name' => $studentData['father_name'] ?? '',
            'mother_name' => $studentData['mother_name'] ?? '',
            'dob' => $studentData['date_of_birth'] ?? '',
            'religion' => $studentData['religion'] ?? '',
            'nationality' => $studentData['nationality'] ?? 'Pakistani',
            'admission_date' => $studentData['admission_date'] ?? '',
            'class_name' => $studentData['class_name'] ?? '',
            'section_name' => $studentData['section_name'] ?? '',
            'leaving_date' => $studentData['leaving_date'] ?? date('Y-m-d'),
            'leaving_reason' => $studentData['leaving_reason'] ?? 'On Request',
            'conduct' => $studentData['conduct'] ?? 'Good',
            'generated_by' => session()->get('user_id') ?? 0,
            'generated_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert SLC record
        $inserted = $db->table('school_leaving_certificates')->insert($slcData);
        
        if (!$inserted) {
            throw new \Exception('Failed to insert SLC record');
        }
        
        $slcId = $db->insertID();
        
        // Get the inserted record
        $insertedSlc = $db->table('school_leaving_certificates')
            ->where('id', $slcId)
            ->get()
            ->getRowArray();
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            throw new \Exception('Transaction failed');
        }
        
        // Get school settings
        $settings = [];
        $settingsQuery = $db->table('settings')->get();
        if ($settingsQuery) {
            foreach ($settingsQuery->getResultArray() as $row) {
                $settings[$row['key']] = $row['value'] ?? '';
            }
        }
        
        // Add additional data to response
        $insertedSlc['school_name'] = $settings['school_name'] ?? 'YOUR SCHOOL NAME';
        $insertedSlc['school_address'] = $settings['school_address'] ?? 'School Address';
        $insertedSlc['school_contact'] = $settings['school_contact'] ?? 'Contact Details';
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'SLC generated successfully',
            'slc' => $insertedSlc,
            'slc_id' => $slcId,
            'slc_number' => $slcNo
        ]);
        
    } catch (\Exception $e) {
        $db->transRollback();
        log_message('error', 'Generate SLC error: ' . $e->getMessage());
        log_message('error', 'Student data: ' . json_encode($studentData));
        
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

public function dropStudent()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
    }
    
    $studentId = $this->request->getPost('student_id');
    $dropOption = $this->request->getPost('drop_option');
    $leavingReason = $this->request->getPost('leaving_reason');
    $leavingDate = $this->request->getPost('leaving_date');
    
    $db = \Config\Database::connect();
    $db->transStart();
    
    try {
        // First, check what columns exist in the students table
        $columnsQuery = $db->query("SHOW COLUMNS FROM students");
        $columns = $columnsQuery->getResultArray();
        $studentColumns = array_column($columns, 'Field');
        
        // Build update data based on existing columns
        $studentUpdateData = ['status' => 4];
        
        // Only add these fields if they exist in the students table
        if (in_array('leaving_reason', $studentColumns)) {
            $studentUpdateData['leaving_reason'] = $leavingReason;
        }
        
        // Check for date column in students table
        if (in_array('leaving_date', $studentColumns)) {
            $studentUpdateData['leaving_date'] = $leavingDate;
        } elseif (in_array('date_of_leaving', $studentColumns)) {
            $studentUpdateData['date_of_leaving'] = $leavingDate;
        }
        
        if (in_array('updated_date', $studentColumns)) {
            $studentUpdateData['updated_date'] = date('Y-m-d H:i:s');
        }
        
        // Update students table
        $studentBuilder = $db->table('students');
        $studentBuilder->where('student_id', $studentId);
        $studentUpdated = $studentBuilder->update($studentUpdateData);
        
        if (!$studentUpdated) {
            throw new \Exception('Failed to update student status in students table');
        }
        
        // Update ALL records in student_class table for this student - status = 4
        $classBuilder = $db->table('student_class');
        $classBuilder->where('student_id', $studentId);
        
        // Check student_class table columns
        $columnsQuery = $db->query("SHOW COLUMNS FROM student_class");
        $columns = $columnsQuery->getResultArray();
        $classColumns = array_column($columns, 'Field');
        
        $classUpdateData = ['status' => 4];
        
        if (in_array('leaving_reason', $classColumns)) {
            $classUpdateData['leaving_reason'] = $leavingReason;
        }
        
        if (in_array('leaving_date', $classColumns)) {
            $classUpdateData['leaving_date'] = $leavingDate;
        } elseif (in_array('date_of_leaving', $classColumns)) {
            $classUpdateData['date_of_leaving'] = $leavingDate;
        }
        
        if (in_array('updated_date', $classColumns)) {
            $classUpdateData['updated_date'] = date('Y-m-d H:i:s');
        }
        
        $classBuilder->update($classUpdateData);
        $affectedRows = $db->affectedRows();
        
        $slcId = null;
        
        // Generate SLC if option selected
        if ($dropOption === 'drop_with_slc') {
            // Get student details with class and section for SLC
            $studentBuilder = $db->table('students s');
            $studentBuilder->select('s.*, c.class_name, sec.section_name, cs.cls_sec_id, p.f_name, p.m_name, p.religion, p.city');
            $studentBuilder->join('class_section cs', 'cs.cls_sec_id = s.cls_sec_id', 'left');
            $studentBuilder->join('classes c', 'c.class_id = cs.class_id', 'left');
            $studentBuilder->join('sections sec', 'sec.section_id = cs.section_id', 'left');
            $studentBuilder->join('parents p', 'p.parent_id = s.parent_id', 'left');
            $studentBuilder->where('s.student_id', $studentId);
            $query = $studentBuilder->get();
            
            if ($query && $query->getNumRows() > 0) {
                $student = $query->getRowArray();
                
                // Combine first_name and last_name for full name
                $fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
                
                // Check what columns exist in school_leaving_certificates table
                $slcColumnsQuery = $db->query("SHOW COLUMNS FROM school_leaving_certificates");
                $slcColumns = $slcColumnsQuery->getResultArray();
                $slcColumnNames = array_column($slcColumns, 'Field');
                
                $slcBuilder = $db->table('school_leaving_certificates');
                
                // Check if SLC already exists for this student
                $existingSlc = $slcBuilder->where('student_id', $studentId)->get()->getRowArray();
                
                // Prepare base SLC data with common fields
                $slcData = [
                    'student_id' => $studentId,
                    'full_name' => $fullName,
                    'first_name' => $student['first_name'] ?? '',
                    'last_name' => $student['last_name'] ?? '',
                    'father_name' => $student['f_name'] ?? '',
                    'mother_name' => $student['m_name'] ?? '',
                    'religion' => $student['religion'] ?? '',
                    'admission_date' => $student['date_of_admission'] ?? '',
                    'class_name' => $student['class_name'] ?? '',
                    'section_name' => $student['section_name'] ?? '',
                    'leaving_reason' => $leavingReason,
                    'conduct' => $this->request->getPost('conduct') ?? 'Good',
                    'generated_by' => session()->get('user_id'),
                    'generated_date' => date('Y-m-d H:i:s')
                ];
                
                // Handle date of birth column - check what it's called
                if (in_array('date_of_birth', $slcColumnNames)) {
                    $slcData['date_of_birth'] = $student['date_of_birth'] ?? '';
                } elseif (in_array('dob', $slcColumnNames)) {
                    $slcData['dob'] = $student['date_of_birth'] ?? '';
                }
                
                // Handle nationality column
                if (in_array('nationality', $slcColumnNames)) {
                    $slcData['nationality'] = $student['city'] ?? 'Pakistani';
                } elseif (in_array('city', $slcColumnNames)) {
                    $slcData['city'] = $student['city'] ?? 'Pakistani';
                }
                
                // Handle leaving date column - check what it's called
                if (in_array('leaving_date', $slcColumnNames)) {
                    $slcData['leaving_date'] = $leavingDate;
                } elseif (in_array('date_of_leaving', $slcColumnNames)) {
                    $slcData['date_of_leaving'] = $leavingDate;
                }
                
                // Handle skip_fee column if it exists
                if (in_array('skip_fee', $slcColumnNames)) {
                    $slcData['skip_fee'] = 0; // Default value
                }
                
                if ($existingSlc) {
                    // UPDATE existing SLC record
                    // Keep the existing slc_no
                    $slcData['slc_no'] = $existingSlc['slc_no'];
                    $slcData['updated_at'] = date('Y-m-d H:i:s');
                    
                    // Remove fields that shouldn't be updated
                    unset($slcData['student_id']); // Don't update student_id
                    unset($slcData['created_at']); // Don't update created_at
                    
                    $slcBuilder->where('id', $existingSlc['id'])->update($slcData);
                    $slcId = $existingSlc['id'];
                    
                    log_message('debug', 'Updated existing SLC record for student_id: ' . $studentId);
                } else {
                    // INSERT new SLC record
                    // Generate SLC number
                    $year = date('Y');
                    $slcCount = $slcBuilder->countAllResults() + 1;
                    $slcData['slc_no'] = 'SLC/' . $year . '/' . str_pad($slcCount, 4, '0', STR_PAD_LEFT);
                    $slcData['created_at'] = date('Y-m-d H:i:s');
                    $slcData['updated_at'] = date('Y-m-d H:i:s');
                    
                    $slcBuilder->insert($slcData);
                    $slcId = $db->insertID();
                    
                    log_message('debug', 'Inserted new SLC record for student_id: ' . $studentId);
                }
            }
        }
        
        $db->transCommit();
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Student dropped successfully. Updated ' . $affectedRows . ' records in student_class.',
            'slc_id' => $slcId
        ]);
        
    } catch (\Exception $e) {
        $db->transRollback();
        log_message('error', 'Drop student error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

    /**
     * Get student details for SLC update
     */
   /**
 * Get student details for SLC update
 */

   /**
 * Get student details for SLC update
 */


   /**
 * Get student details with parent information
 */


public function getSchoolSettings()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false]);
    }
    
    $db = \Config\Database::connect();
    
    // Get campus ID from session (same as in your index method)
    $campusId = (int) $this->session->get('member_campusid');
    
    // Get system information using the helper function - this returns an object (stdClass)
    $school = getSchoolInfo();
    
    // Get campus information - this returns an array from query
    $campus = $db->table('campus')
        ->where('campus_id', $campusId)
        ->get()
        ->getRowArray();
    
    // Since $school is an object, use object syntax (->) not array syntax ([])
    return $this->response->setJSON([
        'success' => true,
        'settings' => [
            // System information from getSchoolInfo() helper - using object syntax
            'school_name' => $school->system_name ?? 'YOUR SCHOOL NAME',
            'school_address' => $school->address ?? 'School Address',
            'school_contact' => $school->landline_number ?? 'Contact Details',
            'school_logo' => $school->logo ?? '/assets/images/school-logo.png',
            
            // Campus information - using array syntax (it's already an array)
            'campus_name' => $campus['campus_name'] ?? 'Main Campus',
            'campus_phone' => $campus['landline'] ?? $school->landline_number ?? 'N/A',
            'campus_location' => $campus['location'] ?? $school->address ?? 'N/A',
            
            // Additional useful info
            'system_id' => $school->id ?? null,
            'campus_id' => $campusId
        ]
    ]);
}
protected function userClassSections()
{
    $db = \Config\Database::connect();
    $campus_id = $this->session->get('member_campusid');

    return $db->table('class_section cs')
        ->select('cs.cls_sec_id, cs.section_id, CONCAT(c.class_name, " (", s.section_name, ")") as sectionclassname')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.status', 1)
        ->where('cs.campus_id', $campus_id)
        ->get()
        ->getResultArray(); // Must return array, not stdClass
}
public function selectStudentByClassSection()
{
    // ... (Keep your existing variable definitions and guards) ...
$cls_sec_id = (int) ($this->request->getVar('cls_sec_id') ?? 0);
    $sessionid  = (int) (session('member_sessionid') ?? 0);
    $campusid   = (int) (session('member_campusid') ?? 0);
    $sessionName = ''; // INITIALIZE HERE to prevent "Undefined variable" error

    // 2. Get current session name for SLC logic
    $currentSession = $this->db->table('academic_session')
        ->where('session_id', $sessionid)
        ->get()
        ->getRow();
    
    if ($currentSession) {
        $sessionName = $currentSession->session_name;
    }


    $students = $this->db->table('student_class sc')
        ->select('sc.student_id, sc.cls_sec_id, s.first_name, s.last_name, s.reg_no, s.status, s.slc_issued')
        ->join('students s', 's.student_id = sc.student_id', 'inner')
        ->where(['sc.cls_sec_id' => $cls_sec_id, 'sc.session_id' => $sessionid, 'sc.status' => 1, 's.campus_id' => $campusid])
        ->orderBy('s.first_name', 'ASC')
        ->get()
        ->getResult();

    ob_start(); ?>
    
    <input type="hidden" id="current_session" value="<?= esc($sessionName) ?>">
    
    <h5 class="mb-3">Existing Students</h5>
    <div class="table-responsive">
        <table id="studentsTable" class="table table-bordered table-sm table-hover">
            <thead class="bg-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Full Name</th>
                    <th style="width: 180px;">Registration No</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 100px;" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php $count = 1; foreach ($students as $s): 
                        $fullName = trim($s->first_name . ' ' . ($s->last_name ?? ''));
                    ?>
                    <tr>
                        <td class="text-center align-middle"><?= $count++ ?></td>
                        <td>
                            <input type="hidden" name="student_id[]" value="<?= (int)$s->student_id ?>">
                            <input type="text" class="form-control form-control-sm" name="full_name[]" value="<?= esc($fullName) ?>" required>
                        </td>
                        <td class="align-middle">
                            <span class="reg-no-display"><?= esc($s->reg_no ?? 'N/A') ?></span>
                            <input type="hidden" name="reg_no[]" value="<?= esc($s->reg_no ?? '') ?>">
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-success">Active</span>
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-drop-student" 
                                    data-student-id="<?= $s->student_id ?>" data-student-name="<?= esc($fullName) ?>">
                                <i class="fas fa-user-slash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No active students found in this section.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card mt-4 border-info">
        <div class="card-header bg-info text-white py-2">
            <h6 class="card-title mb-0">Add New Students (Quick Entry)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0" id="newStudentsTable">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Full Name</th>
                        <th style="width: 180px;">Registration No</th>
                        <th style="width: 100px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="newStudentsBody">
                    <tr>
                        <td class="text-center align-middle">1</td>
                        <td>
                            <input type="hidden" name="student_id[]" value="0">
                            <input type="text" class="form-control form-control-sm full-name-input" name="full_name[]" placeholder="Enter name and press TAB">
                        </td>
                        <td class="align-middle text-muted small">Auto-generated</td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-xs btn-link text-danger btn-remove-new-row"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent border-top-0">
            <button type="button" class="btn btn-outline-primary btn-sm" id="addNewStudentRow">
                <i class="fas fa-plus"></i> Add Another Row
            </button>
        </div>
    </div>

    <input type="hidden" name="cls_sec_id" value="<?= (int)$cls_sec_id ?>">

    <?php
    return $this->response->setBody(ob_get_clean());
}


public function save()
{
    check_permission('admin-add-student');

    $schoolinfo = getSchoolInfo();
    $campusid   = (int) $this->session->get('member_campusid');
    $sessionid  = (int) $this->session->get('member_sessionid');
    $user_id    = (int) $this->session->get('member_userid');
    $date       = date('Y-m-d H:i:s');

    $studentIds = (array) $this->request->getPost('student_id');
    $fullNames  = (array) $this->request->getPost('full_name');
    $regNos     = (array) $this->request->getPost('reg_no');
    $cls_sec_id = (int) $this->request->getPost('cls_sec_id');

    /* -----------------------------
       Filter out empty rows first
    ----------------------------- */
    $filteredData = [];
    foreach ($fullNames as $index => $fullName) {
        $fullName = trim((string) $fullName);
        $studentId = isset($studentIds[$index]) ? (int) $studentIds[$index] : 0;
        $regNo = isset($regNos[$index]) ? trim((string) $regNos[$index]) : '';
        
        // Only include rows that have either existing student or non-empty name
        if ($studentId > 0 || $fullName !== '') {
            $filteredData[] = [
                'student_id' => $studentId,
                'full_name' => $fullName,
                'reg_no' => $regNo,
                'original_index' => $index
            ];
        }
    }

    // If no valid rows, return error
    if (empty($filteredData)) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'No valid student data provided.'
        ]);
    }

    /* -----------------------------
       Resolve class_id safely
    ----------------------------- */
    $class_id = (int) ($this->request->getPost('class_id') ?? 0);
    if ($class_id <= 0 && $cls_sec_id > 0) {
        $row = $this->db->table('class_section')
            ->select('class_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('campus_id', $campusid)
            ->get()
            ->getRow();

        if (!$row) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Invalid class/section selected.'
            ]);
        }

        $class_id = (int) $row->class_id;
    }

    /* -----------------------------
       Generate reg number for new students if not provided
    ----------------------------- */
    $academic_session = $this->db->table('academic_session')
        ->where('session_id', $sessionid)
        ->get()
        ->getRow();

    if (!$academic_session) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Academic session not found.'
        ]);
    }

    $sessionName = explode('-', $academic_session->session_name);
    $sessionYear = ((int) $sessionName[1]) - 1;
    
    // Get next reg number sequence
    $last_count = $this->db->table('students')
        ->where([
            'session_id' => $sessionid,
            'campus_id'  => $campusid
        ])
        ->countAllResults();
    
    $reg_counter = $last_count + 1;

    /* -----------------------------
       Begin single bulk transaction
    ----------------------------- */
    $this->db->transBegin();
    
    $successCount = 0;
    $newStudentCount = 0;
    $updatedStudentCount = 0;

    foreach ($filteredData as $item) {
        $studentId = $item['student_id'];
        $fullName = $item['full_name'];
        $regNo = $item['reg_no'];
        $originalIndex = $item['original_index'] + 1; // For error messages

        /* -----------------------------
           Split full name by first space
        ----------------------------- */
        $nameParts = explode(' ', $fullName, 2);
        $first_name = $nameParts[0] ?? '';
        $last_name  = $nameParts[1] ?? '';

        /* -----------------------------
           VALIDATION (HARD BLOCK)
        ----------------------------- */
        if ($fullName === '') {
            $this->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Row ' . $originalIndex . ': Full name is required and cannot be empty.'
            ]);
        }

        if (mb_strlen($first_name) < 2) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Row ' . $originalIndex . ': First name part must be at least 2 characters.'
            ]);
        }

        /* -----------------------------
           INSERT NEW STUDENT (student_id = 0)
        ----------------------------- */
        if ($studentId === 0) {
            
            // Generate reg number if not provided
            if (empty($regNo)) {
                $regNo = "{$sessionYear}-{$schoolinfo->reg_text}-{$reg_counter}";
                $reg_counter++;
            } else {
                // Validate reg number format
                if (!preg_match('/^[A-Za-z0-9\-]+$/', $regNo)) {
                    $this->db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'msg'     => 'Row ' . $originalIndex . ': Invalid registration number format.'
                    ]);
                }
                
                // Check if reg number already exists
                $existingReg = $this->db->table('students')
                    ->where('reg_no', $regNo)
                    ->where('campus_id', $campusid)
                    ->get()
                    ->getRow();
                    
                if ($existingReg) {
                    $this->db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'msg'     => 'Row ' . $originalIndex . ': Registration number already exists.'
                    ]);
                }
            }

            /* -----------------------------
               Resolve dummy parent
            ----------------------------- */
            $parentInfo = $this->db->table('parents')
                ->where([
                    'father_cnic' => '00000-0000000-0',
                    'campus_id'   => $campusid
                ])
                ->get()
                ->getRow();

            if (!$parentInfo) {
                $this->db->table('parents')->insert([
                    'father_cnic'  => '00000-0000000-0',
                    'religion'     => 'Islam',
                    'campus_id'    => $campusid,
                    'password'     => password_hash('123456', PASSWORD_BCRYPT),
                    'created_date' => $date,
                    'user_id'      => $user_id,
                ]);
                $parent_id = $this->db->insertID();
            } else {
                $parent_id = $parentInfo->parent_id;
            }

            /* -----------------------------
               Insert student
            ----------------------------- */
            $studentData = [
                'parent_id'         => $parent_id,
                'reg_no'            => $regNo,
                'first_name'        => $first_name,
                'last_name'         => $last_name,
                'date_of_admission' => date('Y-m-d'),
                'campus_id'         => $campusid,
                'session_id'        => $sessionid,
                'class_id'          => $class_id,
                'cls_sec_id'        => $cls_sec_id,
                'status'            => 1,
                's_flag'            => 1,
                'created_date'      => $date,
                'user_id'           => $user_id
            ];
            
            $this->db->table('students')->insert($studentData);

            $new_student_id = $this->db->insertID();

            // Insert into student_class
            $this->db->table('student_class')->insert([
                'student_id'   => $new_student_id,
                'session_id'   => $sessionid,
                'cls_sec_id'   => $cls_sec_id,
                'status'       => 1,
                'created_date' => $date,
                'user_id'      => $user_id
            ]);

            // Log the creation
            log_message('info', "New student created: ID {$new_student_id}, Name: {$fullName}, Reg: {$regNo}");
            
            $newStudentCount++;
            $successCount++;
        }
        /* -----------------------------
           UPDATE EXISTING STUDENT (student_id > 0)
        ----------------------------- */
        else {
            // Check if student exists and belongs to this campus
            $existingStudent = $this->db->table('students')
                ->where([
                    'student_id' => $studentId,
                    'campus_id'  => $campusid
                ])
                ->get()
                ->getRow();
                
            if (!$existingStudent) {
                $this->db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => 'Row ' . $originalIndex . ': Student not found or access denied.'
                ]);
            }
            
            // Skip if student is dropped (status = 4)
            if ($existingStudent->status == 4) {
                continue;
            }
            
            // Validate reg number if changed
            $currentRegNo = $existingStudent->reg_no;
            if ($regNo !== $currentRegNo && !empty($regNo)) {
                // Check if new reg number already exists
                $duplicateReg = $this->db->table('students')
                    ->where('reg_no', $regNo)
                    ->where('student_id !=', $studentId)
                    ->where('campus_id', $campusid)
                    ->get()
                    ->getRow();
                    
                if ($duplicateReg) {
                    $this->db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'msg'     => 'Row ' . $originalIndex . ': Registration number already exists for another student.'
                    ]);
                }
            }
            
            // Prepare update data
            $updateData = [
                'first_name'   => $first_name,
                'last_name'    => $last_name,
                'updated_date' => $date,
                'user_id'      => $user_id
            ];
            
            // Only update reg_no if it's provided and different
            if (!empty($regNo) && $regNo !== $currentRegNo) {
                $updateData['reg_no'] = $regNo;
            }
            
            // Update student record
            $this->db->table('students')
                ->where('student_id', $studentId)
                ->update($updateData);
                
            // Log the update
            log_message('info', "Student updated: ID {$studentId}, New name: {$fullName}");
            
            $updatedStudentCount++;
            $successCount++;
        }
    }

    /* -----------------------------
       Finalize transaction
    ----------------------------- */
    if ($this->db->transStatus() === false) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Bulk student save failed. Transaction rolled back.'
        ]);
    }

    $this->db->transCommit();

    // Generate success message
    $msg = '';
    if ($newStudentCount > 0 && $updatedStudentCount > 0) {
        $msg = "{$newStudentCount} new student(s) added and {$updatedStudentCount} student(s) updated successfully.";
    } elseif ($newStudentCount > 0) {
        $msg = "{$newStudentCount} new student(s) added successfully.";
    } elseif ($updatedStudentCount > 0) {
        $msg = "{$updatedStudentCount} student(s) updated successfully.";
    } else {
        $msg = 'No changes made.';
    }

    return $this->response->setJSON([
        'success' => true,
        'msg'     => $msg,
        'count'   => $successCount,
        'new'     => $newStudentCount,
        'updated' => $updatedStudentCount
    ]);
}
}

// end this file

