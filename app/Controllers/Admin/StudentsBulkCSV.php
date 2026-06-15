<?php
namespace App\Controllers\Admin;

    use App\Controllers\BaseController;
    use App\Models\Admin\StudentsModel;
    use CodeIgniter\Database\BaseConnection;
    use stdClass;
    use DateTime;

    class StudentsBulkCSV extends BaseController
    {
        protected $db;
        protected $session;
        protected $studentsModel;
        protected $template_data = [];

        public function __construct()
        {
            $this->db = \Config\Database::connect();
            $this->session = session();
            check_permission('admin-students');
            helper(['form', 'url']);
            $this->studentsModel = new StudentsModel();
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
                $sectionsclassinfo = userClassSections();
            }

            $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

            $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
            $this->template_data['campus_info'] = $campus_info;

            return view('admin/enroll_students', $this->template_data);
        }


       
    public function addbulk()
    {
        check_permission('admin-add-student');

        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $this->template_data = [];

        // Get campus info
        $campus_info = $this->db->table('campus')->where('campus_id', $campusid)->get()->getRow();
        $this->template_data['campus_info'] = $campus_info;

        // Max student limit
        $campus_bill_info = $this->db->table('campus_bills')->where(['status' => 1, 'campus_id' => $campusid])->get()->getRow();
        $max_student_limit = $campus_bill_info->max_students ?? 0;

        // Current student count
        $students_info = $this->db->query("SELECT COUNT(student_id) AS studentTotal FROM students 
                                           WHERE student_id IN (SELECT student_id FROM student_class WHERE status=1) 
                                           AND campus_id = $campusid")->getRow();
        $noOfstudent = $students_info->studentTotal ?? 0;

        $this->template_data['max_limit'] = ($noOfstudent >= $max_student_limit) 
            ? '<div class="col-lg-12">Maximum Limit Exceeded</div>' 
            : '';

        $this->template_data['sessionData'] = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];

        // Classes
        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        // Academic session
        $academic_session = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();
        $sessionName = explode('-', $academic_session->session_name);
        $sessionYear = $sessionName[1] - 1;

        // Campus info
        $campusInfo = $this->db->table('campus')->where('campus_id', $campusid)->get()->getRow();
        $this->template_data['campusInfo'] = $campusInfo;

        // Last student
        $last_row = $this->db->table('students')->where('session_id', $sessionid)->orderBy('student_id', 'desc')->get()->getRow();
        $last_id = 1;

        if ($last_row) {
            $regArr = explode('-', $last_row->reg_no);
            $last_id = isset($regArr[2]) ? ((int)$regArr[2] + 1) : 1;
        }

        $reg_no = $sessionYear . '-' . $schoolinfo->reg_text . '-' . $last_id;
        $this->template_data['reg_no'] = $reg_no;

        if (empty($schoolinfo->reg_text)) {
            echo '<div class="col-lg-12">Reg Text Field is required in system profile</div>';
            echo "<a href='" . base_url('admin/profile_system') . "'>Click Here</a>";
            exit;
        }

        $currentrole = currentUserRoles();

        $sectionsclassinfo = in_array(5, $currentrole)
            ? teacherSubjectSections()
            : userClassSections();

        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        // Attachment types
        $attachementTypesInfo = $this->db->table('attachement_types')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $this->template_data['attachementTypesInfo'] = $attachementTypesInfo;

        // Load view
        return view('admin/students_bulk_csv', $this->template_data);
    }

        public function enrollStudentInfo()
        {
            $campusid = $this->session->get('member_campusid');
            $sessionid = $this->session->get('member_sessionid');
            $user_id = $this->session->get('member_userid');
            $date = date('Y-m-d H:i:s');

            $studentID = $this->request->getPost('student_id');
            $s_flag = $this->request->getPost('s_flag');
            $data = [
                's_flag' => trim($s_flag),
                'updated_date' => $date,
                'user_id' => $user_id
            ];

            $this->db->table('students')
                ->where('student_id', $studentID)
                ->where('campus_id', $campusid)
                ->update($data);

            // Optionally send a JSON response
            return $this->response->setJSON(['status' => 'success']);
        }

 private function normalize_cnic(string $cnic): string
    {
        return preg_replace('/\D/', '', trim($cnic));
    }

public function import()
{
    helper(['form', 'url']);
    $db       = \Config\Database::connect();
    $session  = session();
    $campusid = session('member_campusid');
    $sessionid= session('member_sessionid');
    $user_id  = session('member_userid');

    $schoolinfo = getSchoolInfo();
    $date = date('Y-m-d H:i:s');
    $date_of_admission = date('Y-m-d');

    $validation = \Config\Services::validation();
    $validation->setRules([
        'file' => 'uploaded[file]|ext_in[file,csv]'
    ]);

    // Decide response mode once
    $isAjax = $this->request->isAJAX();

    if (!$validation->withRequest($this->request)->run()) {
        $msg = "Invalid file, please select only CSV file.";
        if ($isAjax) {
            return $this->response->setJSON(['success' => false, 'msg' => $msg]);
        }
        $session->setFlashdata('error', $msg);
        return redirect()->back();
    }

    $file = $this->request->getFile('file');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $csvData = array_map('str_getcsv', file($file->getTempName()));
        $headers = array_map('trim', array_shift($csvData));
        $csvData = array_map(function ($row) use ($headers) {
            return array_combine($headers, array_map('trim', $row));
        }, $csvData);

        // (Optional) counters for a friendly summary
        $totalRows      = count($csvData);
        $studentsAdded  = 0;
        $parentsCreated = 0;

        try {
            foreach ($csvData as $row) {
                $section_id = $row['section_code'];

                $ClassSectioninfo = $db->table('class_section')
                    ->where(['cls_sec_id' => $section_id, 'campus_id' => $campusid, 'status' => 1])
                    ->get()->getRow();

                if (!$ClassSectioninfo) {
                    throw new \Exception("Class code '{$section_id}' not found or inactive in this campus.");
                }

                $academic_session = $db->table('academic_session')
                    ->where('session_id', $sessionid)
                    ->get()->getRow();

                $sessionYear = explode('-', $academic_session->session_name)[1] - 1;

                $last_row = $db->table('students')
                    ->where(['session_id' => $sessionid, 'campus_id' => $campusid])
                    ->orderBy('student_id', 'desc')
                    ->get()->getResult();

                $last_id = count($last_row) + 1;
                $reg_no  = $sessionYear . '-' . $schoolinfo->reg_text . '-' . $last_id;

                if (empty($schoolinfo->reg_text)) {
                    throw new \Exception("Reg Text required in system profile");
                }

                $father_cnic = $row['cnic'];
                $parentInfo  = $db->table('parents')
                    ->where(['father_cnic' => $father_cnic, 'campus_id' => $campusid])
                    ->get()->getRow();

                if ($parentInfo) {
                    $parent_id = $parentInfo->parent_id;
                } else {
                    $parentData = [
                        'father_cnic'       => $father_cnic,
                        'f_name'            => $row['father_name'],
                        'father_contact'    => $row['contact1'],
                        'mother_contact'    => $row['contact3'],
                        'emergency_contact' => $row['contact2'],
                        'religion'          => 'Islam',
                        'password'          => password_hash('default_password', PASSWORD_BCRYPT),
                        'campus_id'         => $campusid,
                        'created_date'      => $date,
                        'updated_date'      => $date,
                        'user_id'           => $user_id
                    ];

                    try {
                        $db->table('parents')->insert($parentData);
                        $parent_id = $db->insertID();
                        $parentsCreated++;
                    } catch (\Exception $e) {
                        log_message('error', "Parent insert failed for CNIC: {$father_cnic} - Error: " . $e->getMessage());
                        throw $e;
                    }
                }

                $feeTypeInfo = $db->table('fee_type')
                    ->where([
                        'system_id'     => $schoolinfo->system_id,
                        'is_monthly_fee'=> 1,
                        's_flag'        => 1,
                        'std_type'      => 1
                    ])->get()->getRow();

                $feeAmountInfo = $db->table('fee_amount')
                    ->where([
                        'session_id' => $sessionid,
                        'fee_type_id'=> $feeTypeInfo->fee_type_id,
                        'class_id'   => $ClassSectioninfo->class_id,
                        'campus_id'  => $campusid
                    ])->get()->getRow();

                $studentclassFee = (int) ($row['student_fee'] ?? 0);
                $feeDiscount     = $feeAmountInfo ? ((float)$feeAmountInfo->amount - (float)$studentclassFee) : 0;

                $studentData = [
                    'reg_no'            => $reg_no,
                    'first_name'        => $row['first_name'],
                    'last_name'         => $row['last_name'],
                    'parent_id'         => $parent_id,
                    'date_of_admission' => $date_of_admission,
                    'campus_id'         => $campusid,
                    'session_id'        => $sessionid,
                    'class_id'          => $ClassSectioninfo->class_id,
                    'discounted_amount' => $feeDiscount,
                    'fee_plan'          => 0,
                    'status'            => 1,
                    'cls_sec_id'        => $section_id,
                    's_flag'            => 1,
                    'gr_date'           => $date,
                    'std_type'          => 1,
                    'gr_no'             => 0,
                    'created_date'      => $date,
                    'updated_date'      => $date,
                    'user_id'           => $user_id
                ];
                $db->table('students')->insert($studentData);
                $new_student_id = $db->insertID();
                $studentsAdded++;

                $db->table('student_class')->insert([
                    'student_id'   => $new_student_id,
                    'session_id'   => $sessionid,
                    'cls_sec_id'   => $section_id,
                    'status'       => 1,
                    'created_date' => $date,
                    'updated_date' => $date,
                    'user_id'      => $user_id
                ]);

                // Prepare data for invoice and fee handling
                $fee_month  = date('Y-m');
                $issue_date = date('Y-m-d');
                $due_date   = date('Y-m-d', strtotime('+10 days'));
                $std_type_id= 1;

                $feeTypes = $db->table('fee_type')
                    ->where([
                        'system_id'     => $schoolinfo->system_id,
                        'is_monthly_fee'=> 1,
                        's_flag'        => 1,
                        'std_type'      => 1
                    ])->get()->getResultArray();

                // Create invoice + fee chalans (uses your corrected function)
                $this->handleInvoiceAndFee(
                    $new_student_id,
                    $ClassSectioninfo->class_id,
                    $std_type_id,
                    $campusid,
                    $sessionid,
                    $issue_date,
                    $due_date,
                    $fee_month,
                    $user_id,
                    $date,
                    $feeTypes,
                    $feeDiscount,
                    1
                );
            }

            // Friendly success message
            $msg = "Import completed: {$studentsAdded} student(s) added"
                 . ($parentsCreated ? ", {$parentsCreated} new parent(s) created." : ".");

            if ($isAjax) {
                return $this->response->setJSON(['success' => true, 'msg' => $msg]);
            }
            $session->setFlashdata('success', $msg);
            return redirect()->back();

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'msg' => $msg]);
            }
            $session->setFlashdata('error', $msg);
            return redirect()->back();
        }
    }

    // Fallback (no valid file)
    $msg = "Please choose a CSV file to upload.";
    if ($isAjax) {
        return $this->response->setJSON(['success' => false, 'msg' => $msg]);
    }
    $session->setFlashdata('error', $msg);
    return redirect()->back();
}



private function handleInvoiceAndFee(
    $student_id, $class_id, $std_type_id, $campus_id, $session_id,
    $issue_date, $due_date, $fee_month, $user_id, $date,
    $feeTypes, $monthly_discount, $flag
) {
    $db = \Config\Database::connect();

    // Get or create invoice
    $existingInvoice = $db->table('invoices')
        ->where('student_id', $student_id)
        ->where('fee_month', $fee_month)
        ->where('issue_date', $issue_date)
        ->get()
        ->getRow();

    $invoice_no = $existingInvoice
        ? $existingInvoice->invoice_no
        : $this->generateInvoiceNumber($fee_month);

    if (!$existingInvoice) {
        $db->table('invoices')->insert([
            'student_id'   => $student_id,
            'issue_date'   => $issue_date,
            'fee_month'    => $fee_month,
            'yr'           => date('y', strtotime($fee_month)),
            'invoice_no'   => $invoice_no,
            'created_date' => $date,
            'updated_date' => $date,
            'user_id'      => $user_id
        ]);
    }

    $insertedCount = 0;

    foreach ($feeTypes as $fee) {
        $fee_type_id = $fee['fee_type_id'];

        // Skip if fee already exists for this invoice/month/type
        $exists = $db->table('fee_chalan')
            ->where('student_id', $student_id)
            ->where('fee_month', $fee_month)
            ->where('fee_type_id', $fee_type_id)
            ->where('invoice_no', $invoice_no)
            ->countAllResults();

        if ($exists) {
            continue;
        }

        // Fetch class fee (default amount) for this fee type
        $amountRow = $db->table('fee_amount')
            ->select('amount')
            ->where([
                'class_id'   => $class_id,
                'campus_id'  => $campus_id,
                'session_id' => $session_id,
                'fee_type_id'=> $fee_type_id
            ])->get()->getRow();

        if (!$amountRow) {
            continue;
        }

        $default_amount = (float)$amountRow->amount;

        // Only monthly fee gets the posted monthly discount; others = 0
        $discount = $fee['is_monthly_fee'] ? (float)($monthly_discount ?? 0) : 0.0;

        // Clamp discount: 0 <= discount <= default_amount
        if ($discount < 0) $discount = 0.0;
        if ($discount > $default_amount) $discount = $default_amount;

        // Net after discount (used only to decide whether to insert)
        $net = $default_amount - $discount;

        // If net is zero or negative, skip creating this chalan row
        if ($net <= 0) {
            continue;
        }

        // ✅ Insert with class fee in `amount` and difference in `discount`
        $db->table('fee_chalan')->insert([
            'student_id'     => $student_id,
            'due_date'       => $due_date,
            'issue_date'     => $issue_date,
            'fee_month'      => $fee_month,
            'fee_month_old'  => date('F Y', strtotime($fee_month)),
            'amount'         => $default_amount,   // ← store CLASS FEE here
            'discount'       => $discount,         // ← store DISCOUNT here
            'status'         => 'unpaid',
            'payment_status' => 'pending',
            'fee_type_id'    => $fee_type_id,
            'paid_date'      => '0000-00-00',
            'created_date'   => $date,
            'updated_date'   => $date,
            'user_id'        => $user_id,
            'acc_id'         => 0,
            'currency_code'  => 'PKR',
            'invoice_no'     => $invoice_no,
            // 'is_tampered'  => ($discount > 0 ? 1 : 0), // uncomment if you track this
        ]);

        $insertedCount++;
    }

    return $insertedCount > 0;
}


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
            ->like('invoice_no', $yr.'-INV-', 'after')
            ->orderBy('invoice_no', 'DESC')
            ->get()
            ->getRow();

        if ($lastInvoice) {
            // Extract the numeric part and increment
            $parts = explode('-', $lastInvoice->invoice_no);
            $lastNumber = (int)end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            // No invoices yet for this year - start from 1
            $nextNumber = 1;
        }

        // Format the invoice number (e.g., "25-INV-00001")
        $invoice_no = $yr . '-INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return $invoice_no;

    } catch (\Exception $e) {
        log_message('error', 'Invoice number generation failed: ' . $e->getMessage());
        throw new \RuntimeException('Failed to generate invoice number: ' . $e->getMessage());
    }
}


     public function file_check($str){
            $allowed_mime_types = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
            if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ""){
                $mime = get_mime_by_extension($_FILES['file']['name']);
                $fileAr = explode('.', $_FILES['file']['name']);
                $ext = end($fileAr);
                if(($ext == 'csv') && in_array($mime, $allowed_mime_types)){
                    return true;
                }else{
                    $this->form_validation->set_message('file_check', 'Please select only CSV file to upload.');
                    return false;
                }
            }else{
                $this->form_validation->set_message('file_check', 'Please select a CSV file to upload.');
                return false;
            }
        }
    }
