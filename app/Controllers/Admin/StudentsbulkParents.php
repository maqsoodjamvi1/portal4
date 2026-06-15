<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use stdClass;

class StudentsbulkParents extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-students');
        helper(['form', 'url']);
    }

    public function index()
    {
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = $this->userClassSections();
        }

        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
        $this->template_data['campus_info'] = $campus_info;

        return view('admin/studentsbulkparents', $this->template_data);
    }


    public function readmit()
{
    check_permission('admin-edit-student');
    
    $data['title'] = 'Readmit Student';
    $data['campus_id'] = session('member_campusid');
    $data['session_id'] = session('member_sessionid');
    
    // Get fee types for the form
    $data['fee_types'] = $this->db->table('fee_type')
        ->where('system_id', getSchoolInfo()->system_id)
        ->where('status', 1)
        ->orderBy('is_monthly_fee', 'DESC')
        ->orderBy('fee_type_name', 'ASC')
        ->get()
        ->getResult();
    
    // Get class sections for selection
    $data['sectionsclassinfo'] = userClassSections();
    
    return view('admin/students/readmit', $data);
}

public function search_drop_students()
{
    $search = $this->request->getPost('search');
    $campus_id = $this->request->getPost('campus_id');
    $search_type = $this->request->getPost('search_type'); // 'name' or 'father'
    
    if (strlen($search) < 3) {
        return $this->response->setJSON(['success' => false, 'data' => []]);
    }
    
    $query = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.leaving_date, s.leaving_reason, 
                  p.f_name as father_name, p.father_cnic, c.class_name, sec.section_name')
        ->join('parents p', 'p.parent_id = s.parent_id')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $this->request->getPost('session_id'), 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', 4); // Status 4 = Left/ Dropped students
    
    if ($search_type == 'father') {
        $query->like('p.f_name', $search, 'both');
    } else {
        $query->groupStart()
            ->like('s.first_name', $search, 'both')
            ->orLike('s.last_name', $search, 'both')
            ->orLike('CONCAT(s.first_name, " ", s.last_name)', $search, 'both')
        ->groupEnd();
    }
    
    $results = $query->limit(15)->get()->getResult();
    
    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'student_id' => $row->student_id,
            'student_name' => trim($row->first_name . ' ' . $row->last_name),
            'reg_no' => $row->reg_no,
            'father_name' => $row->father_name,
            'leaving_date' => $row->leaving_date ? date('d/m/Y', strtotime($row->leaving_date)) : 'N/A',
            'leaving_reason' => $row->leaving_reason ?? 'N/A',
            'previous_class' => ($row->class_name ? $row->class_name . ' - ' . $row->section_name : 'N/A')
        ];
    }
    
    return $this->response->setJSON(['success' => true, 'data' => $data]);
}

public function get_student_readmit_info()
{
    $student_id = $this->request->getPost('student_id');
    $session_id = $this->request->getPost('session_id');
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
    
    // Get previous class info
    $previous_class = $this->db->table('student_class sc')
        ->select('c.class_name, sec.section_name, cs.class_id, cs.cls_sec_id')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections sec', 'sec.section_id = cs.section_id')
        ->where('sc.student_id', $student_id)
        ->where('sc.session_id', $session_id)
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

public function process_readmission()
{
    $user_id = session('member_userid');
    $date = date('Y-m-d H:i:s');
    $today = date('Y-m-d');
    
    $student_id = $this->request->getPost('student_id');
    $cls_sec_id = $this->request->getPost('cls_sec_id');
    $readmission_date = $this->parseDateStrict('Readmission Date', $this->request->getPost('readmission_date'));
    $fee_data = $this->request->getPost('fee_data'); // JSON array of fee types and amounts
    
    try {
        $this->db->transBegin();
        
        // Update student status to active
        $this->db->table('students')
            ->where('student_id', $student_id)
            ->update([
                'status' => 1,
                'leaving_date' => null,
                'leaving_reason' => null,
                'updated_date' => $date,
                'user_id' => $user_id
            ]);
        
        // Update student_class for current session
        $session_id = session('member_sessionid');
        $existing = $this->db->table('student_class')
            ->where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->get()
            ->getRow();
        
        if ($existing) {
            $this->db->table('student_class')
                ->where('student_class_id', $existing->student_class_id)
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
        
        // Insert fee challans if provided
        if (!empty($fee_data)) {
            $fee_items = json_decode($fee_data, true);
            foreach ($fee_items as $item) {
                $fee_month = $item['fee_month'] ?? date('Y-m');
                $fee_type_id = $item['fee_type_id'];
                $amount = $item['amount'];
                $discount = $item['discount'] ?? 0;
                $issue_date = $this->parseDateStrict('Issue Date', $item['issue_date']);
                $due_date = $this->parseDateStrict('Due Date', $item['due_date']);
                
                // Check if challan already exists for this month and fee type
                $existing_challan = $this->db->table('fee_chalan')
                    ->where('student_id', $student_id)
                    ->where('fee_month', $fee_month)
                    ->where('fee_type_id', $fee_type_id)
                    ->where('status', 'unpaid')
                    ->get()
                    ->getRow();
                
                if ($existing_challan) {
                    $this->db->table('fee_chalan')
                        ->where('chalan_id', $existing_challan->chalan_id)
                        ->update([
                            'amount' => $amount,
                            'discount' => $discount,
                            'updated_date' => $date,
                            'user_id' => $user_id
                        ]);
                } else {
                    $this->db->table('fee_chalan')->insert([
                        'student_id' => $student_id,
                        'fee_type_id' => $fee_type_id,
                        'fee_month' => $fee_month,
                        'amount' => $amount,
                        'discount' => $discount,
                        'issue_date' => $issue_date,
                        'due_date' => $due_date,
                        'status' => 'unpaid',
                        'created_date' => $date,
                        'user_id' => $user_id
                    ]);
                }
            }
        }
        
        $this->db->transCommit();
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Student readmitted successfully',
            'redirect' => site_url('admin/students/edit?id=' . $student_id)
        ]);
        
    } catch (\Exception $e) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
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

public function data()
{
     $cls_sec_id = (int) ($this->request->getPost('cls_sec_id') ?? 0);
    $student_id = (int) ($this->request->getPost('student_id') ?? 0);
    
    $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
    
    // DEBUG: Log the incoming parameters
    log_message('debug', '=== DATA METHOD DEBUG ===');
    log_message('debug', 'cls_sec_id: ' . $cls_sec_id);
    log_message('debug', 'student_id: ' . $student_id);
    log_message('debug', 'campusid: ' . $campusid);
    log_message('debug', 'sessionid: ' . $sessionid);
    
    // Check if the class section exists
    $classCheck = $this->db->table('class_section')
        ->select('cls_sec_id, class_id, section_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->get()
        ->getRow();
    
    log_message('debug', 'Class section exists: ' . ($classCheck ? 'YES' : 'NO'));
    if ($classCheck) {
        log_message('debug', 'Class section details: ' . json_encode($classCheck));
    }
    
    // Check if there are any students in student_class for this session
    $studentCount = $this->db->table('student_class')
        ->where('session_id', $sessionid)
        ->where('cls_sec_id', $cls_sec_id)
        ->where('status', 1)
        ->countAllResults();
    
    log_message('debug', 'Students in student_class for this class/session: ' . $studentCount);
    
    // Check if there are any active students in this campus
    $activeStudents = $this->db->table('students')
        ->where('campus_id', $campusid)
        ->where('status', 1)
        ->countAllResults();
    
    log_message('debug', 'Active students in campus: ' . $activeStudents);
    
    // Build the query
    $sql = "SELECT 
                sc.student_id,
                sc.cls_sec_id,
                s.first_name,
                s.last_name,
                s.reg_no,
                p.parent_id,
                p.f_name,
                p.father_cnic,
                p.father_contact,
                p.m_name as mother_name,
                p.mother_contact,
                p.father_email,
                p.father_occupation,
                p.emergency_contact,
                p.whatsapp,
                p.address_line1 as address,
                c.class_name,
                sec.section_name
            FROM student_class sc
            INNER JOIN students s ON s.student_id = sc.student_id
            LEFT JOIN parents p ON p.parent_id = s.parent_id
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c ON c.class_id = cs.class_id
            LEFT JOIN sections sec ON sec.section_id = cs.section_id
            WHERE sc.session_id = ? 
            AND s.status = 1 
            AND s.campus_id = ?";
    
    $params = [$sessionid, $campusid];
    
    if ($cls_sec_id > 0) {
        $sql .= " AND sc.cls_sec_id = ?";
        $params[] = $cls_sec_id;
    }
    
    if ($student_id > 0) {
        $sql .= " AND sc.student_id = ?";
        $params[] = $student_id;
    }
    
    $sql .= " ORDER BY s.first_name ASC";
    
    log_message('debug', 'SQL: ' . $sql);
    log_message('debug', 'Params: ' . json_encode($params));
    
    $query = $this->db->query($sql, $params);
    
    if ($query === false) {
        $error = $this->db->error();
        log_message('error', 'Query failed: ' . print_r($error, true));
        echo '<div class="alert alert-danger">Database error. Check logs.</div>';
        return;
    }
    
    $students = $query->getResult();
    log_message('debug', 'Number of students found: ' . count($students));
    
    if (empty($students)) {
        // Show detailed message to help debug
        echo '<div class="alert alert-warning text-center py-5">
                <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                <strong>No students found for class ID: ' . $cls_sec_id . '</strong><br>
                <hr>
                <small class="text-muted">
                    Debug Info:<br>
                    Session ID: ' . $sessionid . '<br>
                    Campus ID: ' . $campusid . '<br>
                    Students in student_class table for this class: ' . $studentCount . '<br>
                    Active students in campus: ' . $activeStudents . '<br>
                    Class section exists: ' . ($classCheck ? 'Yes' : 'No') . '
                </small>
              </div>';
        return;
    }
    
    // Render cards
    $html = '';
    foreach ($students as $student) {
        $studentId = $student->student_id;
        $hasParent = !empty($student->parent_id) && $student->parent_id > 0;
        
        $html .= '
        <div class="card parent-card mb-3" data-student-id="' . $studentId . '" 
             data-linked="' . ($hasParent ? 'true' : 'false') . '"
             data-new="' . (!$hasParent ? 'true' : 'false') . '">
            <div class="card-header card-header-collapsible py-2 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center flex-wrap">
                    <i class="fas fa-chevron-down collapse-icon me-2"></i>
                    <strong>' . esc($student->first_name . ' ' . $student->last_name) . '</strong>
                    <span class="badge text-bg-secondary ms-2">' . esc($student->reg_no ?? 'No Reg') . '</span>
                    <span class="badge text-bg-info ms-2">' . esc($student->class_name ?? '') . ' - ' . esc($student->section_name ?? '') . '</span>
                    <span class="relink-status ms-2">' . ($hasParent ? '<span class="badge text-bg-success">Linked</span>' : '<span class="badge text-bg-warning">No Parent</span>') . '</span>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-info show-siblings me-1" 
                            data-student-id="' . $studentId . '" data-parent-id="' . ($student->parent_id ?? 0) . '"
                            ' . (!$hasParent ? 'disabled' : '') . '>
                        <i class="fas fa-users"></i> Siblings
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning relink-parent me-1" 
                            data-student-id="' . $studentId . '" data-student-name="' . esc($student->first_name . ' ' . $student->last_name) . '">
                        <i class="fas fa-link"></i> Relink
                    </button>
                    <button type="button" class="btn btn-sm btn-primary save-student" 
                            data-student-id="' . $studentId . '">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
            <div class="collapse-body">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Father Name</label>
                                <input type="text" class="form-control father-name" 
                                       value="' . esc($student->f_name ?? '') . '"
                                       placeholder="Enter father full name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-id-card"></i> Father CNIC</label>
                                <input type="text" class="form-control father-cnic" 
                                       value="' . esc($student->father_cnic ?? '') . '"
                                       placeholder="XXXXX-XXXXXXX-X">
                                <small class="text-muted cnic-hint d-block mt-1"></small>
                                <input type="hidden" class="parent-id" value="' . ($student->parent_id ?? '') . '">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Father Contact</label>
                               <input type="tel" class="form-control father-contact" 
       id="father_contact_' . $studentId . '"
       name="father_contact_' . $studentId . '"
       value="' . esc($student->father_contact ?? '') . '"
       placeholder="03XXXXXXXXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Father Email</label>
                                <input type="email" class="form-control father-email" 
                                       value="' . esc($student->father_email ?? '') . '"
                                       placeholder="father@example.com">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-female"></i> Mother Name</label>
                                <input type="text" class="form-control mother-name" 
                                       value="' . esc($student->mother_name ?? '') . '"
                                       placeholder="Enter mother full name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Mother Contact</label>
                             <input type="tel" class="form-control mother-contact" 
       id="mother_contact_' . $studentId . '"
       name="mother_contact_' . $studentId . '"
       value="' . esc($student->mother_contact ?? '') . '"
       placeholder="03XXXXXXXXX">
                            </div>
                        </div>
                    </div>

                    <!-- After the Mother Contact row, add these new rows -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label><i class="fas fa-phone-alt"></i> Emergency Contact</label>
            <input type="tel" class="form-control emergency-contact" 
                   name="emergency_contact_' . $studentId . '"
                   value="' . esc($student->emergency_contact ?? '') . '"
                   placeholder="Emergency Contact Number">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label><i class="fab fa-whatsapp"></i> WhatsApp Number 
                <small class="text-muted">
                   <i class="fas fa-copy whatsapp-copy-btn copy-father-to-whatsapp" 
   data-student-id="' . $studentId . '" 
   style="cursor:pointer;"
   title="Copy from Father Contact"></i>
                   <i class="fas fa-copy whatsapp-copy-btn copy-mother-to-whatsapp ms-2" 
   data-student-id="' . $studentId . '" 
   style="cursor:pointer;"
   title="Copy from Mother Contact"></i>
                </small>
            </label>
            <div class="input-group">
               <input type="tel" class="form-control whatsapp" 
       id="whatsapp_' . $studentId . '"
       name="whatsapp_' . $studentId . '"
       value="' . esc($student->whatsapp ?? '') . '"
       placeholder="WhatsApp Number">
            </div>
        </div>
    </div>
</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-briefcase"></i> Father Occupation</label>
                                <input type="text" class="form-control father-occupation" 
                                       value="' . esc($student->father_occupation ?? '') . '"
                                       placeholder="Occupation">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Address</label>
                                <textarea class="form-control address" rows="2" 
                                          placeholder="Complete address">' . esc($student->address ?? '') . '</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    echo $html;
}
public function getSiblings()
{
    $parentId = (int) $this->request->getPost('parent_id');
    $currentStudent = (int) $this->request->getPost('current_student');
    $campusid = (int) $this->session->get('member_campusid');
    $sessionid = (int) $this->session->get('member_sessionid');
    
    $parent = $this->db->table('parents')
        ->select('f_name')
        ->where('parent_id', $parentId)
        ->get()
        ->getRow();
    
    $siblings = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, c.class_name, sec.section_name')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $sessionid . ' AND sc.status = 1', 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.parent_id', $parentId)
        ->where('s.campus_id', $campusid)
        ->where('s.status', 1)
        ->where('s.student_id !=', $currentStudent)
        ->get()
        ->getResult();
    
    return $this->response->setJSON([
        'success' => true,
        'parent_name' => $parent->f_name ?? 'Unknown',
        'siblings' => $siblings
    ]);
}

public function relink()
{
    $data = json_decode($this->request->getBody(), true);
    $studentId = (int) ($data['student_id'] ?? 0);
    $newParentId = (int) ($data['parent_id'] ?? 0);
    $userId = (int) $this->session->get('member_userid');
    $date = date('Y-m-d H:i:s');
    
    if (!$studentId || !$newParentId) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid student or parent ID'
        ]);
    }
    
    $this->db->transBegin();
    
    try {
        // Get old parent_id for logging
        $oldStudent = $this->db->table('students')
            ->select('parent_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRow();
        
        // Update student with new parent_id
        $this->db->table('students')
            ->where('student_id', $studentId)
            ->update([
                'parent_id' => $newParentId,
                'updated_date' => $date,
                'user_id' => $userId
            ]);
        
        // Log the change (optional - you can create a log table)
        log_message('info', "Student {$studentId} relinked from parent {$oldStudent->parent_id} to {$newParentId} by user {$userId}");
        
        $this->db->transCommit();
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Student relinked successfully'
        ]);
        
    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', 'Relink error: ' . $e->getMessage());
        
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}
    public function selectClassFee()
    {
        $campusid = $this->session->get('member_campusid');
        $section_id = $this->request->getPost('section_id');
        $schoolinfo = getSchoolInfo();
        $session_id = $this->session->get('member_sessionid');
        $amount = 0;
        $feemonth_balance = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id=? AND is_monthly_fee=1 AND s_flag=1) AND class_id = (SELECT class_id FROM class_section WHERE cls_sec_id=?) AND campus_id=? AND session_id=?', [$schoolinfo->system_id, $section_id, $campusid, $session_id])->getRow();
        if ($feemonth_balance) {
            $amount = $feemonth_balance->amount;
        }

        echo $amount;
        exit;
    }
public function saveStudent()
{
    $user_id = $this->session->get('member_userid');
    $date = date('Y-m-d H:i:s');

    $schoolinfo = getSchoolInfo();
    $campusid = $this->session->get('member_campusid');
    $sessionid = $this->session->get('member_sessionid');

    $studentsInfo = $this->request->getPost('student_id');
    $sectionID = $this->request->getPost('section_id');
    $fee_plan = $this->request->getPost('fee_plan');
    $currentBalance = $this->request->getPost('current_balance');
    $previousBalance = $this->request->getPost('previous_balance');
    
    // Get current student info from database (including the actual discount)
    $currentStudent = $this->db->table('students')
        ->where('student_id', $studentsInfo)
        ->get()
        ->getRow();

    if (empty($currentStudent)) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Student not found.'
        ]);
    }

    // Get the current discount from database (not from form)
    $studentDiscount = (float)($currentStudent->discounted_amount ?? 0);
    
    // Get current class info
    $currentClassId = $currentStudent->class_id;
    $currentClsSecId = $currentStudent->cls_sec_id;

    $feeTypeInfo = $this->db->table('fee_type')
        ->where('system_id', $schoolinfo->system_id)
        ->where('is_monthly_fee', 1)
        ->where('s_flag', 1)
        ->get()->getRow();

    if (empty($feeTypeInfo)) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Monthly fee type not configured. Please configure fee types first.'
        ]);
    }

    // Get new class section info
    $newClassSectioninfo = $this->db->table('class_section')
        ->where('cls_sec_id', $sectionID)
        ->get()->getRow();

    if (empty($newClassSectioninfo)) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid class section selected.'
        ]);
    }

    // Get old class fee amount
    $oldClassFee = 0;
    if ($currentClassId > 0) {
        $oldAmountInfo = $this->db->table('fee_amount')
            ->where('class_id', $currentClassId)
            ->where('session_id', $sessionid)
            ->where('fee_type_id', $feeTypeInfo->fee_type_id)
            ->where('campus_id', $campusid)
            ->get()->getRow();
        
        $oldClassFee = (float)($oldAmountInfo->amount ?? 0);
    }

    // Get new class fee amount
    $newAmountInfo = $this->db->table('fee_amount')
        ->where('class_id', $newClassSectioninfo->class_id)
        ->where('session_id', $sessionid)
        ->where('fee_type_id', $feeTypeInfo->fee_type_id)
        ->where('campus_id', $campusid)
        ->get()->getRow();

    if (empty($newAmountInfo)) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Fee amount not configured for this class. Please configure fee amounts first.'
        ]);
    }

    $newClassFee = (float)$newAmountInfo->amount;
    
    // Calculate the student's payable amount from old class
    // Payable Amount = Old Class Fee - Old Discount
    $studentPayableAmount = $oldClassFee - $studentDiscount;
    
    // Calculate the new discount based on the student's payable amount
    // New Discount = New Class Fee - Student Payable Amount
    $newDiscount = $newClassFee - $studentPayableAmount;
    
    // Ensure discount is not negative
    if ($newDiscount < 0) {
        $newDiscount = 0;
    }
    
    // Calculate the new payable amount for verification
    $newPayableAmount = $newClassFee - $newDiscount;

    // Log the calculation for debugging
    log_message('info', "Student ID: {$studentsInfo}");
    log_message('info', "Old Class ID: {$currentClassId}, Old Class Fee: {$oldClassFee}, Old Discount: {$studentDiscount}, Old Payable: {$studentPayableAmount}");
    log_message('info', "New Class ID: {$newClassSectioninfo->class_id}, New Class Fee: {$newClassFee}, New Discount: {$newDiscount}, New Payable: {$newPayableAmount}");

    // Update student data with new discount
    $data = [
        'discounted_amount' => $newDiscount, // Store the new discount amount
        'fee_plan' => trim($fee_plan),
        'class_id' => $newClassSectioninfo->class_id, // Update class_id
        'cls_sec_id' => $newClassSectioninfo->cls_sec_id, // Update cls_sec_id
        'updated_date' => $date,
        'user_id' => $user_id
    ];

    $this->db->table('students')->where('student_id', $studentsInfo)->update($data);

    // Update student_class table
    $dataClass = [
        'cls_sec_id' => trim($newClassSectioninfo->cls_sec_id),
        'updated_date' => $date,
        'user_id' => $user_id
    ];

    $this->db->table('student_class')
        ->where('student_id', $studentsInfo)
        ->where('session_id', $sessionid)
        ->update($dataClass);

    // Handle fee chalan updates
    $fee_month = date("m/Y");
    $prev_fee_month = date("m/Y", strtotime("-1 months"));
    $issuedate = date('Y-m-d');
    $duedate = date('Y-m-d', strtotime('+10 days'));

    $prevfeeChalaninfo = $this->db->table('fee_chalan')
        ->where('fee_type_id', $feeTypeInfo->fee_type_id)
        ->where('student_id', $studentsInfo)
        ->where('fee_month', $prev_fee_month)
        ->where('status', 'unpaid')
        ->get()->getRow();

    $feeChalaninfo = $this->db->table('fee_chalan')
        ->where('fee_type_id', $feeTypeInfo->fee_type_id)
        ->where('student_id', $studentsInfo)
        ->where('fee_month', $fee_month)
        ->where('status', 'unpaid')
        ->get()->getRow();

    if (empty($prevfeeChalaninfo) && $previousBalance > 0) {
        $feeData = [
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
        ];

        $this->db->table('fee_chalan')->insert($feeData);

    } else if (!empty($prevfeeChalaninfo)) {
        $feeData = [
            'amount' => $previousBalance,
            'discount' => 0,
            'updated_date' => $date,
            'user_id' => $user_id
        ];

        $this->db->table('fee_chalan')
            ->where('chalan_id', $prevfeeChalaninfo->chalan_id)
            ->update($feeData);
    }

    if (empty($feeChalaninfo) && $currentBalance > 0) {
        $feeData = [
            'fee_type_id' => $feeTypeInfo->fee_type_id,
            'student_id' => $studentsInfo,
            'issue_date' => $issuedate,
            'due_date' => $duedate,
            'fee_month' => $fee_month,
            'amount' => $newPayableAmount, // New payable amount
            'discount' => $newDiscount, // New discount amount
            'status' => 'unpaid',
            'created_date' => $date,
            'user_id' => $user_id
        ];

        $this->db->table('fee_chalan')->insert($feeData);

    } else if (!empty($feeChalaninfo)) {
        $feeData = [
            'amount' => $newPayableAmount, // Update with new payable amount
            'discount' => $newDiscount, // Update discount
            'updated_date' => $date,
            'user_id' => $user_id
        ];

        $this->db->table('fee_chalan')
            ->where('chalan_id', $feeChalaninfo->chalan_id)
            ->update($feeData);
    }

    return $this->response->setJSON([
        'success' => true, 
        'msg' => 'Student updated successfully',
        'old_class_fee' => $oldClassFee,
        'old_discount' => $studentDiscount,
        'old_payable' => $studentPayableAmount,
        'new_class_fee' => $newClassFee,
        'new_discount' => $newDiscount,
        'new_payable' => $newPayableAmount
    ]);
}

   public function save()
{
    $user_id = $this->session->get('member_userid');
    $date = date('Y-m-d H:i:s');
    $campusid = $this->session->get('member_campusid');
    
    // Debug: Log what we're receiving
    log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
    
    // Get the data properly - handle both single and bulk saves
    $bulkData = $this->request->getPost('bulk_data');
    $singleStudent = $this->request->getPost('student_id');
    $parentData = $this->request->getPost('parent_data');
    
    // Check if bulk_data is a JSON string and decode it
    if (is_string($bulkData) && !empty($bulkData)) {
        $bulkData = json_decode($bulkData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid bulk data format: ' . json_last_error_msg()
            ]);
        }
    }
    
    // Check if parent_data is a JSON string and decode it
    if (is_string($parentData) && !empty($parentData)) {
        $parentData = json_decode($parentData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid parent data format'
            ]);
        }
    }
    
    $this->db->transBegin();
    
    try {
        // Handle single student save
        if ($singleStudent && $parentData && is_array($parentData)) {
            $studentId = $singleStudent;
            
            // Get current parent_id
            $student = $this->db->table('students')
                ->select('parent_id')
                ->where('student_id', $studentId)
                ->get()
                ->getRow();
            
            if ($student && $student->parent_id) {
                // Update existing parent
                $updateData = [];
                if (!empty($parentData['f_name'])) $updateData['f_name'] = $parentData['f_name'];
                if (!empty($parentData['father_cnic'])) $updateData['father_cnic'] = $parentData['father_cnic'];
                if (!empty($parentData['father_contact'])) $updateData['father_contact'] = $parentData['father_contact'];
                if (!empty($parentData['mother_name'])) $updateData['m_name'] = $parentData['mother_name'];
                if (!empty($parentData['mother_contact'])) $updateData['mother_contact'] = $parentData['mother_contact'];
                if (!empty($parentData['father_email'])) $updateData['father_email'] = $parentData['father_email'];
                if (!empty($parentData['father_occupation'])) $updateData['father_occupation'] = $parentData['father_occupation'];
                if (!empty($parentData['address'])) $updateData['address_line1'] = $parentData['address'];
                
                if (!empty($updateData)) {
                    $updateData['updated_date'] = $date;
                    $updateData['user_id'] = $user_id;
                    
                    $this->db->table('parents')
                        ->where('parent_id', $student->parent_id)
                        ->update($updateData);
                }
            } else {
                // Create new parent
                $insertData = [
                    'f_name' => $parentData['f_name'] ?? '',
                    'father_cnic' => $parentData['father_cnic'] ?? '',
                    'father_contact' => $parentData['father_contact'] ?? '',
                    'm_name' => $parentData['mother_name'] ?? '',
                    'mother_contact' => $parentData['mother_contact'] ?? '',
                    'father_email' => $parentData['father_email'] ?? '',
                    'father_occupation' => $parentData['father_occupation'] ?? '',
                    'address_line1' => $parentData['address'] ?? '',
                    'campus_id' => $campusid,
                    'status' => 1,
                    'created_date' => $date,
                    'updated_date' => $date,
                    'user_id' => $user_id
                ];
                
                $this->db->table('parents')->insert($insertData);
                $newParentId = $this->db->insertID();
                
                // Update student with new parent_id
                $this->db->table('students')
                    ->where('student_id', $studentId)
                    ->update(['parent_id' => $newParentId]);
            }
        }
        
        // Handle bulk save - THIS IS LINE 690 WHERE THE ERROR OCCURS
        if ($bulkData && is_array($bulkData)) {
            foreach ($bulkData as $data) {
                if (!isset($data['student_id'])) {
                    continue; // Skip invalid entries
                }
                
                $studentId = $data['student_id'];
                
                $student = $this->db->table('students')
                    ->select('parent_id')
                    ->where('student_id', $studentId)
                    ->get()
                    ->getRow();
                
                if ($student && $student->parent_id) {
                    $updateData = [];
                    if (!empty($data['f_name'])) $updateData['f_name'] = $data['f_name'];
                    if (!empty($data['father_cnic'])) $updateData['father_cnic'] = $data['father_cnic'];
                    if (!empty($data['father_contact'])) $updateData['father_contact'] = $data['father_contact'];
                    if (!empty($data['mother_name'])) $updateData['m_name'] = $data['mother_name'];
                    if (!empty($data['mother_contact'])) $updateData['mother_contact'] = $data['mother_contact'];
                    if (!empty($data['father_email'])) $updateData['father_email'] = $data['father_email'];
                    if (!empty($data['father_occupation'])) $updateData['father_occupation'] = $data['father_occupation'];
                    if (!empty($data['address'])) $updateData['address_line1'] = $data['address'];
                    
                    if (!empty($updateData)) {
                        $updateData['updated_date'] = $date;
                        $updateData['user_id'] = $user_id;
                        
                        $this->db->table('parents')
                            ->where('parent_id', $student->parent_id)
                            ->update($updateData);
                    }
                }
            }
        }
        
        $this->db->transCommit();
        
        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Parent information updated successfully'
        ]);
        
    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', 'Save error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}
/**
 * Get parent info for a student (for relink modal)
 */
public function getStudentParent()
{
    $studentId = (int) $this->request->getPost('student_id');
    
    $parent = $this->db->table('students s')
        ->select('p.*')
        ->join('parents p', 'p.parent_id = s.parent_id')
        ->where('s.student_id', $studentId)
        ->get()
        ->getRow();
    
    if ($parent) {
        return $this->response->setJSON([
            'success' => true,
            'parent' => $parent
        ]);
    }
    
    return $this->response->setJSON([
        'success' => false,
        'msg' => 'No parent found for this student'
    ]);
}

/**
 * Search parents by name (for relink modal)
 */
public function searchParentsByName()
{
    $q = trim((string) $this->request->getGet('q'));
    $limit = (int) ($this->request->getGet('limit') ?: 20);
    $campusid = (int) $this->session->get('member_campusid');
    
    if (strlen($q) < 2) {
        return $this->response->setJSON(['results' => []]);
    }
    
    $parents = $this->db->table('parents')
        ->select('parent_id, f_name, m_name, father_cnic')
        ->groupStart()
            ->like('f_name', $q, 'both')
            ->orLike('m_name', $q, 'both')
        ->groupEnd()
        ->where('campus_id', $campusid)
        ->where('status', 1)
        ->limit($limit)
        ->get()
        ->getResult();
    
    $results = [];
    foreach ($parents as $p) {
        $results[] = [
            'id' => (int) $p->parent_id,
            'text' => $p->f_name . ' (' . ($p->father_cnic ?: 'No CNIC') . ')'
        ];
    }
    
    return $this->response->setJSON(['results' => $results]);
}

/**
 * Get parent details with siblings (for relink modal)
 */
public function getParentDetails()
{
    $parentId = (int) $this->request->getPost('parent_id');
    $campusid = (int) $this->session->get('member_campusid');
    $sessionid = (int) $this->session->get('member_sessionid');
    
    // Get parent details
    $parent = $this->db->table('parents')
        ->where('parent_id', $parentId)
        ->get()
        ->getRow();
    
    if (!$parent) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Parent not found'
        ]);
    }
    
    // Get siblings (other students with same parent)
    $siblings = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, c.class_name, sec.section_name')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . $sessionid, 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.parent_id', $parentId)
        ->where('s.campus_id', $campusid)
        ->where('s.status', 1)
        ->get()
        ->getResult();
    
    return $this->response->setJSON([
        'success' => true,
        'parent' => $parent,
        'siblings' => $siblings
    ]);
}
}
