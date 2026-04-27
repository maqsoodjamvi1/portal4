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
            $sectionsclassinfo = userClassSections();
        }

        $data['sectionsclassinfo'] = $sectionsclassinfo;
        return view('admin/students', $data);
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
            ->get()
            ->getResult();
        $data['attachementTypesInfo'] = $attachementTypesInfo;
        
        return view('admin/students_edit', $data);
    }

    // ... (previous code remains the same)

  
   

public function save_admission()
{
    helper(['form', 'text']);
    $user_id = session('member_userid');
    $sessionid = session('member_sessionid');
    $campus_id = session('member_campusid');
    $date = date('Y-m-d H:i:s');

    // Validation rules
    $validation = \Config\Services::validation();
    $validation->setRules([
        'first_name' => 'required|max_length[255]',
        'father_cnic' => 'required|max_length[15]',
        'f_name' => 'required|max_length[255]',
        'date_of_admission' => 'required|valid_date[d/m/Y]',
        'date_of_birth' => 'required|valid_date[d/m/Y]',
        'section_id' => 'required|is_natural_no_zero',
        'fee_issue_date' => 'required|valid_date[d/m/Y]',
        'fee_due_date' => 'required|valid_date[d/m/Y]',
        'fee_month' => 'required'
    ]);

    if (!$validation->withRequest($this->request)->run()) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => implode('<br>', $validation->getErrors())
        ]);
    }

    try {
        // Date formatting
        $date_of_admission = DateTime::createFromFormat('d/m/Y', $this->request->getPost('date_of_admission'))->format('Y-m-d');
        $gr_date = $this->request->getPost('gr_date') ? 
            DateTime::createFromFormat('d/m/Y', $this->request->getPost('gr_date'))->format('Y-m-d') : null;
        $date_of_birth = DateTime::createFromFormat('d/m/Y', $this->request->getPost('date_of_birth'))->format('Y-m-d');
        
        $fee_issue_date = DateTime::createFromFormat('d/m/Y', $this->request->getPost('fee_issue_date'))->format('Y-m-d');
        $fee_due_date = DateTime::createFromFormat('d/m/Y', $this->request->getPost('fee_due_date'))->format('Y-m-d');
        $fee_month = $this->request->getPost('fee_month');
        
        $cls_sec_id = (int) $this->request->getPost('section_id');
        $father_cnic = trim($this->request->getPost('father_cnic'));
        $class_id = $this->db->table('class_section')
            ->select('class_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->get()
            ->getRow()
            ->class_id ?? 0;

        if (!$class_id) {
            throw new \Exception('Invalid class section selected');
        }

        $this->db->transStart();

        // === Parent Handling ===
        $parent_id = $this->handleParentData($father_cnic, $campus_id, $user_id, $date);

        // === Student Data ===
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
            $date
        );

        // === Student Class Assignment ===
        $this->handleStudentClass($student_id, $sessionid, $cls_sec_id, $user_id, $date);

        // === Invoice and Fee Handling ===
        $this->handleInvoiceAndFee(
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

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \Exception('Database transaction failed');
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Student admission record saved successfully',
            'student_id' => $student_id
        ]);

    } catch (\Throwable $e) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

private function handleInvoiceAndFee($student_id, $class_id, $campus_id, $session_id, 
                                    $issue_date, $due_date, $fee_month, $user_id, $date)
{
    // Create invoice
    $invoice_no = $this->generateInvoiceNumber();
    $invoice_data = [
        'student_id' => $student_id,
        'issue_date' => $issue_date,
        'fee_month' => $fee_month,
        'yr' => date('y', strtotime($fee_month)),
        'invoice_no' => $invoice_no,
        
        'created_date' => $date
    ];
    
    $this->db->table('invoices')->insert($invoice_data);
    $invoice_id = $this->db->insertID();
    
    // Process fee items
    $fee_type_ids = $this->request->getPost('fee_type_id');
    $student_amounts = $this->request->getPost('student_amount');
    
    foreach ($fee_type_ids as $index => $fee_type_id) {
        $student_amount = (float) $student_amounts[$index] ?? 0;
        
        // Get default amount for this fee type
        $default_amount = $this->db->table('fee_amount')
            ->select('amount')
            ->where('class_id', $class_id)
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->where('fee_type_id', $fee_type_id)
            ->get()
            ->getRow()
            ->amount ?? 0;
            
        $discount = $default_amount - $student_amount;
        
        // Create fee chalan record
        $fee_data = [
            'student_id' => $student_id,
            'due_date' => $due_date,
            'issue_date' => $issue_date,
            'fee_month' => $fee_month,
            'amount' => $student_amount,
            'discount' => $discount,
            'status' => 'unpaid',
            'payment_status' => 'pending',
            'fee_type_id' => $fee_type_id,
            'paid_date' => null,
            'created_date' => $date,
            'updated_date' => $date,
            'user_id' => $user_id,
            'invoice_no' => $invoice_no,
            'acc_id' => 0, // Default account
            'currency_code' => 'PKR' // Default currency
        ];
        
        $this->db->table('fee_chalan')->insert($fee_data);
    }
}

private function generateInvoiceNumber()
{
    $currentYear = date('y'); // Last 2 digits of current year
    
    // Get the last invoice number for current year
    $lastInvoice = $this->db->table('invoices')
        ->select('invoice_no')
        ->where('yr', $currentYear)
        ->orderBy('invoice_no', 'DESC')
        ->get()
        ->getRow();
    
    $sequence = 1;
    if ($lastInvoice) {
        // Extract the numeric part after "INV-"
        $parts = explode('-', $lastInvoice->invoice_no);
        $lastNumber = (int) end($parts);
        $sequence = $lastNumber + 1;
    }
    
    return $currentYear . '-INV-' . $sequence;
}



// Add this AJAX function to your controller
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

    // Get all active fee types
    $fee_types = $this->db->table('fee_type')
        ->select('fee_type_id, fee_type_name as fee_type_title')
        ->where([
            'system_id' => $system_id,
            'status' => 1
        ])
        ->get()
        ->getResultArray();

    $fee_amounts = [];
    foreach ($fee_types as $fee) {
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
            'default_amount' => $amount ? $amount->amount : 0
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
        'user_id' => $user_id
    ];

    $this->db->table('parents')->insert($parent_data);
    return $this->db->insertID();
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

    $discounted_amount = $monthly_fee - (float) $this->request->getPost('discounted_amount');

    // Handle profile photo
    $profile_photo = $this->request->getPost('image') ?? '';
    $image = $this->request->getFile('image');
    if ($image && $image->isValid() && !$image->hasMoved()) {
        $newName = $image->getRandomName();
        $image->move('./uploads/', $newName);
        $profile_photo = $newName;
    }

    // Student data
    $student_data = [
        'reg_no' => trim($this->request->getPost('reg_no')),
        'first_name' => trim($this->request->getPost('first_name')),
        'last_name' => trim($this->request->getPost('last_name')) ?? '',
        'std_cnic' => trim($this->request->getPost('student_cnic')) ?? '',
        'parent_id' => $parent_id,
        'gender' => trim($this->request->getPost('gender')) ?? '',
        'date_of_admission' => $date_of_admission,
        'date_of_birth' => $date_of_birth,
        'caste' => trim($this->request->getPost('caste')) ?? '',
        'gr_no' => trim($this->request->getPost('gr_no')) ?? '',
        'gr_date' => $gr_date,
        'class_id' => $class_id,
        'cls_sec_id' => $cls_sec_id,
        'campus_id' => $campus_id,
        'session_id' => $sessionid,
        'discounted_amount' => $discounted_amount,
        'fee_plan' => (int) $this->request->getPost('fee_plan') ?? 1,
        'status' => 4, // Admission status
        's_flag' => 1,
        'profile_photo' => $profile_photo,
        'previous_school' => trim($this->request->getPost('previous_school')) ?? '',
        'ps_city' => trim($this->request->getPost('ps_city')) ?? '',
        'major_injuries' => trim($this->request->getPost('major_injuries')) ?? '',
        'health_conditions' => trim($this->request->getPost('health_conditions')) ?? '',
        'user_id' => $user_id,
        'created_date' => $date
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
        'status' => 4, // Admission status
        'created_date' => $date,
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
            WHERE is_monthly_fee = 1 AND system_id = {$school_info->system_id}
            LIMIT 1
        )
    ")->getRow();

    if ($feeRow) {
        return $this->response->setJSON(['success' => true, 'monthly_fee' => $feeRow->amount]);
    }

    return $this->response->setJSON(['success' => false, 'msg' => 'Fee not found.']);
}


public function get_parent_info()
{
    $cnic = $this->request->getPost('cnic');

    $builder = $this->db->table('parents');
    $parent = $builder->where('father_cnic', $cnic)->get()->getRowArray();

    if ($parent) {
        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'father_name'  => $parent['father_name'],
                'father_phone' => $parent['father_phone'],
                // Add more fields if necessary
            ]
        ]);
    }

    return $this->response->setJSON(['status' => 'not_found']);
}

   
  public function check_parent_cnic()
{
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
                ]
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