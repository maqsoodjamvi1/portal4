<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Admin\StudentsModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;



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
        //$this->studentsModel = model('StudentsModel');
        $this->studentsModel = new StudentsModel();
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

    public function updateDiscounts()
    {
        $student_ids = $this->request->getPost('student_id');
        $discounted_amounts = $this->request->getPost('discounted_amount');
        $student_class_fee = $this->request->getPost('student_class_fee');
        
        foreach ($student_ids as $key => $value) {
            $discounted_amount = ($student_class_fee[$key] - $discounted_amounts[$key]);
            $data = [
                'discounted_amount' => $discounted_amount
            ];

            $this->db->table('students')
                ->where('student_id', $value)
                ->update($data);
        }
        
        return $this->respond(['success' => true]);
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

    public function addbulk()
    {
        check_permission('admin-add-student');
        $schoolinfo = getSchoolInfo();
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');

        $campus_info = $this->db->query('select * from campus WHERE campus_id='.$campusid)->getRow();
        $data['campus_info'] = $campus_info;

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

        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()
            ->getRow();

        $campusInfo = $this->db->table('campus')
            ->where('campus_id', $campusid)
            ->get()
            ->getRow();
        $data['campusInfo'] = $campusInfo;
        
        $sessionName = explode('-', $academic_session->session_name);
        $sessionYear = ($sessionName[1]-1);
        
        $last_row = $this->db->table('students')
            ->where('session_id', $sessionid)
            ->orderBy('student_id', 'desc')
            ->get()
            ->getRow();

        if($last_row) {
            $regArr = explode('-', $last_row->reg_no);
            $last_id = $regArr[2] + 1;
        } else {
            $last_id = 1;
        }

        $reg_no = $sessionYear.'-'.$schoolinfo->reg_text.'-'.$last_id;
        $data['reg_no'] = $reg_no;

        if(empty($schoolinfo->reg_text)) {
            echo '<div class="col-lg-12">Reg Text Field is required in system profile</div>';
            echo "<a href='admin.php#/profile_system'>Click Here</a>";
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
        
        return view('admin/students_editbulk', $data);
    }

    public function getSibling()
    {
        $strSibling = '';
        $schoolinfo = getSchoolInfo();
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
    
        $parent_id = $this->request->getPost('parentID');

        $Studentsinfo = $this->db->table('parents')
            ->where('parent_id', $parent_id)
            ->get()
            ->getResult();

        if($Studentsinfo) {
            $strSibling .= '<table class="table"><tr><th>Name</th><th>Parent</th><th>Class</th><th>Student Fee</th></tr>';
            foreach($Studentsinfo as $value) {
                $parentsinfo = $this->db->table('parents')
                    ->where('parent_id', $value->parent_id)
                    ->get()
                    ->getRow();

                $studentclassinfo = $this->db->table('student_class')
                    ->where('student_id', $value->student_id)
                    ->get()
                    ->getRow();

                if($studentclassinfo) {
                    $sectionInfo = '';
                    $projectedfee = '';
                    
                    $classsectioninfo = $this->db->query('select * from class_section where cls_sec_id='.$studentclassinfo->cls_sec_id)->getRow();
                    
                    if($classsectioninfo) {
                        $classinfo = $this->db->table('classes')
                            ->where('class_id', $classsectioninfo->class_id)
                            ->get()
                            ->getRow();
                    }

                    if($classinfo) {
                        $sectionInfo = $this->db->table('sections')
                            ->where('section_id', $classsectioninfo->section_id)
                            ->get()
                            ->getRow();
                    }

                    if($sectionInfo) {
                        $sectionName = $sectionInfo->section_name;
                    }

                    if($classsectioninfo) {
                        $getclassfee = $this->db->query('SELECT * FROM `fee_amount` WHERE class_id='.$classsectioninfo->class_id.' and fee_type_id IN(select fee_type_id from fee_type where is_monthly_fee=1 and s_flag=1) and session_id='.$sessionid.' and campus_id='.$campusid)->getRow();
                    }
                    if($getclassfee) {
                        $projectedfee = ($getclassfee->amount - $value->discounted_amount);
                    }
                }
                
                if($classinfo) {
                    $className = $classinfo->class_name;
                }

                $strSibling .= '<tr><td>'.$value->first_name.' '.$value->last_name.'</td><td>'.$parentsinfo->f_name.'</td>
                <td>'.$className.'('.$sectionName.')</td><td>'.$projectedfee.'/-</td> </tr>';
            }

            $strSibling .= '<table>';
        }
        
        echo $strSibling;
    }

    public function edit()
    {
        check_permission('admin-edit-student');
        $id = (int)$this->request->getGet('id');
        $schoolinfo = getSchoolInfo();
        
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
        
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        
        $data['sessionData'] = $sessionData;
        $info = $this->db->table('students')
            ->where('student_id', $id)
            ->get()
            ->getRow();
        $data['info'] = $info;
        
        $parentsinfo = $this->db->table('parents')
            ->where('parent_id', $info->parent_id)
            ->get()
            ->getRow();
        $data['parentsinfo'] = $parentsinfo;
        
        $sectionsclassinfo = userClassSections();
        $data['sectionsclassinfo'] = $sectionsclassinfo;
        
        $studentclassinfo = $this->db->table('student_class')
            ->where('student_id', $id)
            ->where('session_id', $sessionid)
            ->get()
            ->getRow();
        $data['studentclassinfo'] = $studentclassinfo;

        $fee_plans = $this->db->table('fee_plans')->get()->getResult();
        $data['fee_plans'] = $fee_plans;

        $amount = 0;
        $Transportamount = 0;
        $schoolinfo = getSchoolInfo();
        $session_id = session('member_sessionid');

        if($studentclassinfo) {
            $section_id = $studentclassinfo->cls_sec_id;
                    
            $feemonth_balance = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.' AND is_monthly_fee=1 and s_flag=1) AND class_id = (SELECT class_id FROM class_section WHERE cls_sec_id='.$section_id.') AND campus_id='.$campusid.' AND session_id='.$session_id)->getRow();

            $transportFee = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.' AND is_transport_fee=1) AND class_id = (SELECT class_id FROM class_section WHERE cls_sec_id='.$section_id.') AND campus_id='.$campusid.' AND session_id='.$session_id)->getRow();
        } else {
            $feemonth_balance = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.' AND is_monthly_fee=1 and s_flag=1) AND class_id = '.$info->class_id.' AND campus_id='.$campusid.' AND session_id='.$session_id)->getRow();

            $transportFee = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.' AND is_transport_fee=1) AND class_id = '.$info->class_id.' AND campus_id='.$campusid.' AND session_id='.$session_id)->getRow();
        }

        if($feemonth_balance) {
            $amount = $feemonth_balance->amount;
        }

        $data['classesfee'] = $amount;

        if($transportFee) {
            $Transportamount = $transportFee->amount;
        }

        $data['transportfee'] = $Transportamount;
        
        $classesinfo = $this->db->table('classes')->get()->getResult();
        $data['classesinfo'] = $classesinfo;
        
        $academic_sessioninfo = $this->db->table('academic_session')->get()->getResult();
        $data['academic_sessioninfo'] = $academic_sessioninfo;

        $attachementTypesInfo = $this->db->table('attachement_types')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();
        $data['attachementTypesInfo'] = $attachementTypesInfo;

        return view('admin/students_edit', $data);
    }

  public function save_attachment(): ResponseInterface
{
    helper(['form', 'url']);
    $this->response->setHeader('Content-Type', 'application/json');

    $file = $this->request->getFile('file');

    $student_id     = $this->request->getPost('student_id');
    $a_type_id      = $this->request->getPost('a_type_id');
    $attachement_id = $this->request->getPost('attachement_id');
    $user_id        = session('member_userid') ?? 0;
    $date           = date('Y-m-d H:i:s');

    if ($file && $file->isValid() && !$file->hasMoved()) {

        // Set a new file name
        $newName = $file->getRandomName();
        $uploadPath = FCPATH . 'studentattachements/';

        // Create folder if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Move file to target path
        $file->move($uploadPath, $newName);

        $db = \Config\Database::connect(    );
        $attachmentTable = $db->table('attachements');

        if (empty($attachement_id) || $attachement_id == 0) {
            // Insert new attachment
            $data = [
                'a_type_id'        => trim($a_type_id),
                'attachement_name' => $file->getClientName(),
                'attachement_path' => $newName,
                'student_id'       => trim($student_id),
                'created_date'     => $date,
                'user_id'          => $user_id
            ];
            $attachmentTable->insert($data);
        } else {
            // Update existing, delete old file
            $existing = $attachmentTable->where('attachement_id', $attachement_id)->get()->getRow();
            if ($existing && file_exists($uploadPath . $existing->attachement_path)) {
                unlink($uploadPath . $existing->attachement_path);
            }

            $data = [
                'a_type_id'        => trim($a_type_id),
                'attachement_name' => $file->getClientName(),
                'attachement_path' => $newName,
                'student_id'       => trim($student_id),
                'created_date'     => $date,
                'user_id'          => $user_id
            ];
            $attachmentTable->where('attachement_id', $attachement_id)->update($data);
        }

        return $this->response->setJSON([
            'success' => true,
            'msg'     => 'Uploaded Successfully'
        ]);
    }

    return $this->response->setJSON([
        'success' => false,
        'msg'     => 'File upload failed or invalid file'
    ]);
}

    public function save_basicinfo()
    {
        $user_id = session('member_userid');
        $sessionid = session('member_sessionid');
        $campus_id = session('member_campusid');
        $schoolinfo = getSchoolInfo();

        $date = date('Y-m-d H:i:s');
        $cls_sec_id = $this->request->getPost('section_id');

        $clsSectionInfo = $this->db->table('class_section')
            ->where('cls_sec_id', $cls_sec_id)
            ->get()
            ->getRow();
        
        $class_id = $clsSectionInfo->class_id;

        $id = (int)$this->request->getPost('id');
        $parent_id = (int)$this->request->getPost('parent_id');
        $now = date('Y-m-d H:i:s');

        $date_of_admission = \DateTime::createFromFormat('d/m/Y', $this->request->getPost('date_of_admission'));
        $date_of_admission = $date_of_admission->format('Y-m-d');

        $gr_date = \DateTime::createFromFormat('d/m/Y', $this->request->getPost('gr_date'));
        $gr_date = $gr_date->format('Y-m-d');
        
        $validation = \Config\Services::validation();
        $validation->setRule('first_name', 'First Name', 'trim|required');
        
        if(!$validation->withRequest($this->request)->run()) {
            return $this->respond(['success' => false, 'msg' => $validation->getErrors()]);
        } else {
            if($id === 0) {
                check_permission('admin-add-student');
                $this->db->transStart();
                
                if($parent_id < 1) {
                    $data2 = [
                        'father_cnic' => trim($this->request->getPost('father_cnic')),
                        'f_name' => trim($this->request->getPost('f_name')),
                        'religion' => trim($this->request->getPost('religion')),
                        'password' => trim('$2y$11$devU5YfJe43QwVEdvRU3UevZO.vlbd3u56yeGYt2k1d2c56VYjm/a'),
                        'campus_id' => $campus_id,
                        'created_date' => $date,
                        'user_id' => $user_id
                    ];
            
                    $this->db->table('parents')->insert($data2);
                    $new_parent_id = $this->db->insertID();
                    
                   $parentsinfo = $this->db->table('parents')
    ->where('parent_id', $new_parent_id)
    ->get()
    ->getRow();

if ($parentsinfo) {
    $parent_id = $parentsinfo->parent_id;
} else {
    // Optional: handle the error properly
    log_message('error', 'Parent not found for ID: ' . $new_parent_id);
    throw new \Exception('Parent record not found.');
}

                  
                }

                $feeAmountInfo = $this->db->table('fee_amount')
                    ->where('class_id', $class_id)
                    ->where('session_id', $sessionid)
                    ->get()
                    ->getRow();
                
                $stdFeeDiscount = 0;
                $stdTransFeeDiscount = 0;
                if($feeAmountInfo) {
                    $amount = $feeAmountInfo->amount;
                    $stdFeeDiscount = ($amount - $this->request->getPost('discounted_amount'));
                }

                $feeAmountInfo = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.' AND is_monthly_fee=1 and s_flag=1) AND class_id = '.$class_id.' AND campus_id='.$campus_id.' AND session_id='.$sessionid)->getRow();

                if($feeAmountInfo) {
                    $amount = $feeAmountInfo->amount;
                    $stdFeeDiscount = ($amount - $this->request->getPost('discounted_amount'));
                }

                $feeTransAmountInfo = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.' AND is_transport_fee=1) AND class_id = '.$class_id.' AND campus_id='.$campus_id.' AND session_id='.$sessionid)->getRow();

                if($feeTransAmountInfo) {
                    $amountTrans = $feeTransAmountInfo->amount;
                    $stdTransFeeDiscount = ($amountTrans - $this->request->getPost('transport_fee'));
                }

                $data = [
                    'reg_no' => trim($this->request->getPost('reg_no')),
                    'first_name' => trim($this->request->getPost('first_name')),
                    'last_name' => trim($this->request->getPost('last_name')),
                    'std_cnic' => trim($this->request->getPost('std_cnic')),
                    'parent_id' => $parent_id,
                    'gender' => trim($this->request->getPost('gender')),
                    'date_of_admission' => $date_of_admission,
                    'caste' => trim($this->request->getPost('caste')),
                    'gr_no' => trim($this->request->getPost('gr_no')),
                    'gr_date' => $gr_date,
                    'class_id' => trim($class_id),
                    'cls_sec_id' => $cls_sec_id,
                    'campus_id' => trim($this->request->getPost('campus_id')),
                    //'session_id' => trim($this->session->userdata('member_sessionid')),
                    'session_id' => trim(session('member_sessionid')),
                    'discounted_amount' => trim($stdFeeDiscount),
                    'fee_plan' => trim($this->request->getPost('fee_plan')),
                    'status' => 4,
                    's_flag' => 1,
                    'created_date' => $date,
                    'user_id' => $user_id
                ];
                $this->db->table('students')->insert($data);
                $new_student_id = $this->db->insertID();
                
                $stdinfo = $this->db->table('students')
                    ->where('student_id', $new_student_id)
                    ->get()
                    ->getRow();
                 
                $studentclass = [
                    'student_id' => $stdinfo->student_id,
                    'session_id' => $stdinfo->session_id,
                    'cls_sec_id' => $cls_sec_id,
                    'status' => 4,
                    'created_date' => $date,
                    'user_id' => $user_id
                ];
                $this->db->table('student_class')->insert($studentclass);

                $this->db->transComplete();
                return $this->respond(['type' => 'success', 'student_id' => $new_student_id, 'message' => "<div class='alert alert-success'>Successfully Created</div>"]);
            } else {
                check_permission('admin-edit-student');
                $this->db->transStart();

                if(empty($this->request->getPost('father_cnic'))) {
                    return $this->respond(['error' => true, 'msg' => 'Father CNIC required']);
                }

                $feeAmountInfo = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.' AND is_monthly_fee=1 and s_flag=1) AND class_id = '.$class_id.' AND campus_id='.$campus_id.' AND session_id='.$sessionid)->getRow();
                
                $stdFeeDiscount = 0;
                $stdTransFeeDiscount = 0;
                if(!empty($feeAmountInfo)) {
                    $amount = $feeAmountInfo->amount;
                    $stdFeeDiscount = ($amount - $this->request->getPost('discounted_amount'));
                }

                $feeTransAmountInfo = $this->db->query('SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id='.$schoolinfo->system_id.' AND is_transport_fee=1) AND class_id = '.$class_id.' AND campus_id='.$campus_id.' AND session_id='.$sessionid)->getRow();

                if($feeTransAmountInfo) {
                    $amountTrans = $feeTransAmountInfo->amount;
                    $stdTransFeeDiscount = ($amountTrans - $this->request->getPost('transport_fee'));
                }
                
                $data = [
                    'first_name' => trim($this->request->getPost('first_name')),
                    'last_name' => trim($this->request->getPost('last_name')),
                    'std_cnic' => trim($this->request->getPost('std_cnic')),
                    'gender' => trim($this->request->getPost('gender')),
                    'date_of_admission' => $date_of_admission,
                    'caste' => trim($this->request->getPost('caste')),
                    'gr_no' => trim($this->request->getPost('gr_no')),
                    'gr_date' => $gr_date,
                    'campus_id' => trim($this->request->getPost('campus_id')),
                    'fee_plan' => trim($this->request->getPost('fee_plan')),
                    'discounted_amount' => trim($stdFeeDiscount),
                    'updated_date' => $date,
                    'user_id' => $user_id
                ];
                
                $data2 = [
                    'religion' => trim($this->request->getPost('religion')),
                    'father_cnic' => trim($this->request->getPost('father_cnic')),
                    'f_name' => trim($this->request->getPost('f_name')),
                    'updated_date' => $date,
                    'user_id' => $user_id
                ];

                $ClassSectioninfo = $this->db->query('select * from student_class where student_id='.$id.' AND session_id='.$sessionid)->getRow();
                
                if($ClassSectioninfo) {
                    $studentclass = [
                        'cls_sec_id' => trim($this->request->getPost('section_id')),
                        'updated_date' => $date,
                        'user_id' => $user_id
                    ];
                    
                    $this->db->table('student_class')
                        ->where('student_id', $id)
                        ->where('status', 1)
                        ->update($studentclass);
                } else {
                    $stdinfo = $this->db->table('students')
                        ->where('student_id', $id)
                        ->get()
                        ->getRow();
                    
                    $studentclass = [
                        'student_id' => $stdinfo->student_id,
                        'session_id' => $stdinfo->session_id,
                        'cls_sec_id' => $this->request->getPost('section_id'),
                        'status' => $stdinfo->status,
                        'created_date' => $date,
                        'user_id' => $user_id
                    ];
                    
                    $this->db->table('student_class')->insert($studentclass);
                }

                $this->db->table('students')
                    ->where('student_id', $id)
                    ->update($data);
                
                $this->db->table('parents')
                    ->where('parent_id', $parent_id)
                    ->update($data2);
                
                $this->db->transComplete();
                session()->setFlashdata('success', 'Student record successfully created.');
                return redirect()->to(base_url('admin/students/edit/' . $new_student_id));
            }
        }
    }

    public function save_contactinfo()
    {
        $parser = \Config\Services::parser();
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
        $user_id = session('member_userid');
        $date = date('Y-m-d H:i:s');

        $campusInfo = $this->db->query('select * from campus where campus_id='.$campusid)->getRow();
                
        $id = (int)$this->request->getPost('id');
        $parent_id = (int)$this->request->getPost('parent_id');
        $now = date('Y-m-d H:i:s');
        
        check_permission('admin-edit-student');
        $this->db->transStart();
        
        $data2 = [
            'father_contact' => trim($this->request->getPost('father_contact')),
            'father_email' => trim($this->request->getPost('father_email')),
            'father_occupation' => trim($this->request->getPost('father_occupation')),
            'father_office_address' => trim($this->request->getPost('father_office_contact')),
            'm_name' => trim($this->request->getPost('m_name')),
            'mother_contact' => trim($this->request->getPost('mother_contact')),
            'address_line1' => trim($this->request->getPost('address_line1')),
            'emergency_contact_person' => trim($this->request->getPost('emergency_contact_person')),
            'emergency_contact' => trim($this->request->getPost('emergency_contact')),
            'whatsapp' => trim($this->request->getPost('whatsapp_contact')),
            'a_address' => trim($this->request->getPost('a_address')),
            'city' => trim($this->request->getPost('city')),
            'updated_date' => $date,
            'user_id' => $user_id
        ];

        $this->db->table('parents')
            ->where('parent_id', $parent_id)
            ->update($data2);
        
        if($campusInfo->welcome_sms) {
            $template = $campusInfo->welcome_sms;
            $parentsInfo = $this->db->query('select * from parents where parent_id ='.$parent_id)->getRow();
            
            if($parentsInfo) {
                $studentInfo = $this->db->table('students')
                    ->where('student_id', $id)
                    ->get()
                    ->getRow();

                $stdClassSectioninfo = $this->db->query('select * from student_class where student_id='.$id.' AND session_id='.$sessionid)->getRow();

                $classSectionInfo = $this->db->query('select * from class_section where cls_sec_id='.$stdClassSectioninfo->cls_sec_id)->getRow();

                $classInfo = $this->db->table('classes')
                    ->where('class_id', $classSectionInfo->class_id)
                    ->get()
                    ->getRow();

                $SectionInfo = $this->db->table('sections')
                    ->where('section_id', $classSectionInfo->section_id)
                    ->get()
                    ->getRow();

                $StudentClass = $classInfo->class_name.'('.$SectionInfo->section_name.')';

                $mobile = $parentsInfo->father_contact;
                $smsDate = date('Y-m-d');

                if(!empty($mobile)) {
                    $dataMessage = [
                        'first_name' => $studentInfo->first_name,
                        'last_name' => $studentInfo->last_name,
                        'father_name' => $parentsInfo->f_name,
                        'class' => $StudentClass,
                        'date' => $smsDate
                    ];

                    $parsedMessage = $parser->setData($dataMessage)->renderString($template);
                    
                    $data = [
                        'mobile' => $mobile,
                        'message' => trim($parsedMessage),
                        'campus_id' => trim($campusid),
                        'parent_id' => $parentsInfo->parent_id,
                        'status' => 0,
                        'user_id' => $user_id,
                        'created_date' => $date
                    ];

                    $this->db->table('sms')->insert($data);
                }
            }
        }

        $this->db->transComplete();
        
        return $this->response->setJSON(['success' => true, 'msg' => 'Saved successfully.']);
    }

    public function save_generalinfo()
    {
        $user_id = session('member_userid');
        $date = date('Y-m-d H:i:s');
        
        $date_of_birth = \DateTime::createFromFormat('d/m/Y', $this->request->getPost('date_of_birth'));
        $date_of_birth = $date_of_birth->format('Y-m-d');
                
        $id = (int)$this->request->getPost('id');
        $parent_id = (int)$this->request->getPost('parent_id');
        $now = date('Y-m-d H:i:s');
        
        check_permission('admin-edit-student');
        
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'gif|jpg|jpeg|png|svg|webp';
        $config['max_size'] = 1024;
        
        $upload = \Config\Services::upload();
        $upload->initialize($config);
        
        $upload->do_upload('image');
        $data = $upload->data();
        
        if($data['file_name']) {
            $imagename = $data['file_name'];
        } else {
            $imagename = trim($this->request->getPost('image'));
        }

        $this->db->transStart();
        
        $data = [
            'date_of_birth' => $date_of_birth,
            'previous_school' => trim($this->request->getPost('previous_school')),
            'ps_city' => trim($this->request->getPost('ps_city')),
            'major_injuries' => trim($this->request->getPost('major_injuries')),
            'health_conditions' => trim($this->request->getPost('health_conditions')),
            'profile_photo' => trim($imagename),
            'updated_date' => $date,
            'user_id' => $user_id
        ];
        
        $data2 = [
            'hear_source' => trim($this->request->getPost('hear_source')),
            'updated_date' => $date,
            'user_id' => $user_id
        ];
        
        $this->db->table('students')
            ->where('student_id', $id)
            ->update($data);
        
        $this->db->table('parents')
            ->where('parent_id', $parent_id)
            ->update($data2);
        
        $this->db->transComplete();
        return $this->respond(['success' => true, 'msg' => 'General information updated successfully']);
    }

    public function get_parentinfo()
    {
        $campusid = session('member_campusid');
        $term = $this->request->getPost('term');
        
        $parentssinfo = $this->db->query("select * from parents where (f_name like '%".$term['term']."%' ) AND campus_id= ".$campusid)->getResultArray();
        
        $data = [];
        foreach($parentssinfo as $parent) {
            $classstudents = $this->db->query("select * from students where status=1 and parent_id = ".$parent['parent_id'].' AND campus_id= '.$campusid)->getRow();
            if($classstudents) {
                $data[] = ["id" => $parent['parent_id'], "text" => $parent['f_name']];
            }
        }

        return $this->respond($data);
    }

    public function get_studentinfo()
    {
        $campusid = session('member_campusid');
        $term = $this->request->getPost('term');
        $status = $this->request->getPost('status');
        
        $studentsinfo = $this->db->query("select * from students where (first_name like '%".$term['term']."%' OR last_name like '%".$term['term']."%') AND status=".$status." AND campus_id=".$campusid)->getResultArray();
        
        $data = [];
        foreach($studentsinfo as $student) {
            $classstudents = $this->db->query("select * from student_class where student_id = ".$student['student_id'])->getRow();
            $parentsInfo = $this->db->query("select f_name from parents where parent_id = ".$student['parent_id'])->getRow();
            $fatherName = '';
            
            if($parentsInfo) {
                $fatherName = $parentsInfo->f_name;
            }

            $stdInfotxt = $student['first_name']." ".$student['last_name']." c/o ".$fatherName;

            if($classstudents) {
                $data[] = ["id" => $student['student_id'], "text" => $stdInfotxt];
            }
        }
        
        return $this->respond($data);
    }

    public function import()
    {
        $data = [];
        $memData = [];
        $schoolinfo = getSchoolInfo();
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
        $user_id = session('member_userid');
        $date = date('Y-m-d H:i:s');
        $date_of_admission = date('Y-m-d');

        $validation = \Config\Services::validation();
        $validation->setRule('file', 'CSV file', 'uploaded[file]|ext_in[file,csv]');
        
        if($validation->withRequest($this->request)->run()) {
            $insertCount = $updateCount = $rowCount = $notAddCount = 0;
            
            $file = $this->request->getFile('file');
            
            if($file->isValid() && !$file->hasMoved()) {
                $reader = new \App\Libraries\CSVReader();
                $csvData = $reader->parse_csv($file->getRealPath());
                
                if(!empty($csvData)) {
                    foreach($csvData as $row) {
                        $section_id = $row['section_code'];

                        $ClassSectioninfo = $this->db->table('class_section')
                            ->where('cls_sec_id', $section_id)
                            ->where('campus_id', $campusid)
                            ->where('status', 1)
                            ->get()
                            ->getRow();

                        if(empty($ClassSectioninfo)) {
                            return $this->respond(['type' => 'error', 'message' => "<div class='alert alert-danger'>Wrong Class Code.</div>"]);
                        }

                        $academic_session = $this->db->table('academic_session')
                            ->where('session_id', $sessionid)
                            ->get()
                            ->getRow();
                        
                        $sessionName = explode('-', $academic_session->session_name);
                        $sessionYear = ($sessionName[1]-1);
                        
                        $last_row = $this->db->table('students')
                            ->where('session_id', $sessionid)
                            ->where('campus_id', $campusid)
                            ->orderBy('student_id', 'desc')
                            ->get()
                            ->getRow();

                        if($last_row) {
                            $regArr = explode('-', $last_row->reg_no);
                            $last_id = $regArr[2] + 1;
                        } else {
                            $last_id = 1;
                        }

                        $reg_no = $sessionYear.'-'.$schoolinfo->reg_text.'-'.$last_id;
                        
                        if(empty($schoolinfo->reg_text)) {
                            echo '<div style="min-height: 150px;text-align: center;padding-top: 20px;font-size: 18px;text-decoration: blink;color: red;"><div class="col-lg-12">Enter School Short Name in system profile</div>';
                            echo "<a href='admin.php#/profile_system'>Click Here</a></div>";
                            exit;
                        }
                        
                        $parentInfo = $this->db->table('parents')
                            ->where('father_cnicn', $row['cnic'])
                            ->where('campus_id', $campusid)
                            ->get()
                            ->getRow();
                        
                        if(empty($parentInfo)) {
                            $data2 = [
                                'father_cnic' => trim($row['cnic']),
                                'f_name' => trim($row['father_name']),
                                'father_contact' => trim($row['contact1']),
                                'mother_contact' => trim($row['contact3']),
                                'emergency_contact' => trim($row['contact2']),
                                'religion' => trim('Islam'),
                                'password' => trim('$2y$11$devU5YfJe43QwVEdvRU3UevZO.vlbd3u56yeGYt2k1d2c56VYjm/a'),
                                'campus_id' => $campusid,
                                'created_date' => $date,
                                'user_id' => $user_id
                            ];
                    
                            $this->db->table('parents')->insert($data2);
                            $new_parent_id = $this->db->insertID();
                        } else {
                            $new_parent_id = $parentInfo->parent_id;
                        }

                      $parentsinfo = $this->db->table('parents')
    ->where('parent_id', $new_parent_id)
    ->get()
    ->getRow();

if ($parentsinfo) {
    $parent_id = $parentsinfo->parent_id;
} else {
    // Optional: handle the error properly
    log_message('error', 'Parent not found for ID: ' . $new_parent_id);
    throw new \Exception('Parent record not found.');
}
                        
                      //  $parent_id = $parentsinfo ? $parentsinfo->parent_id : 0;

                        $feeTypeInfo = $this->db->table('fee_type')
                            ->where('system_id', $schoolinfo->system_id)
                            ->where('is_monthly_fee', 1)
                            ->where('s_flag', 1)
                            ->get()
                            ->getRow();

                        $feeAmountInfo = $this->db->table('fee_amount')
                            ->where('session_id', $sessionid)
                            ->where('fee_type_id', $feeTypeInfo->fee_type_id)
                            ->where('class_id', $ClassSectioninfo->class_id)
                            ->get()
                            ->getRow();
                        
                        $studentclassFee = (int)$row['student_fee'];
                        $feeDiscount = $feeAmountInfo ? ((float)$feeAmountInfo->amount - (float)$studentclassFee) : 0;
                        
                        $data = [
                            'reg_no' => trim($reg_no),
                            'first_name' => trim($row['first_name']),
                            'last_name' => trim($row['last_name']),
                            'parent_id' => $parent_id,
                            'date_of_admission' => $date_of_admission,
                            'campus_id' => trim($this->request->getPost('campus_id')),
                            'session_id' => trim($sessionid),
                            'class_id' => trim($ClassSectioninfo->class_id),
                            'discounted_amount' => trim($feeDiscount),
                            'fee_plan' => 0,
                            'status' => 1,
                            's_flag' => 1,
                            'created_date' => $date,
                            'user_id' => $user_id
                        ];

                        $this->db->table('students')->insert($data);
                        $new_student_id = $this->db->insertID();
                        
                        $studentclass = [
                            'student_id' => $new_student_id,
                            'session_id' => $sessionid,
                            'cls_sec_id' => $section_id,
                            'status' => 1,
                            'created_date' => $date,
                            'user_id' => $user_id
                        ];

                        $this->db->table('student_class')->insert($studentclass);
                        
                        if($row['arrears'] != 0) {
                            $fee_month = date('m/Y');
                            $issuedate = date('Y-m-d');
                            $duedate = date('Y-m-d', strtotime('+10 days'));

                            $feeData = [
                                'fee_type_id' => $feeTypeInfo->fee_type_id,
                                'student_id' => $new_student_id,
                                'issue_date' => $issuedate,
                                'due_date' => $duedate,
                                'fee_month' => $fee_month,
                                'amount' => $row['arrears'],
                                'discount' => 0,
                                'status' => 'unpaid',
                                'created_date' => $date,
                                'user_id' => $user_id
                            ];
                            
                            $this->db->table('fee_chalan')->insert($feeData);
                        }
                    }
                }
            } else {
                return $this->respond(['type' => 'error', 'message' => "<div class='alert alert-danger'>Error on file upload, please try again.</div>"]);
            }
        } else {
            return $this->respond(['type' => 'error', 'message' => "<div class='alert alert-danger'>Invalid file, please select only CSV file.</div>"]);
        }

        return $this->respond(['type' => 'success', 'message' => "<div class='alert alert-success'>Successfully Uploaded</div>"]);
    }

    public function getParentInfo()
    {
        $father_cnic = $this->request->getPost('f_cnic');
        $campusid = session('member_campusid');
    
        $parentInfo = $this->db->query('select * from parents WHERE father_cnic="'.$father_cnic.'" AND parent_id IN(SELECT parent_id from students WHERE campus_id='.$campusid.')')->getRow();
        if($parentInfo) {
            echo $parentInfo->f_name;
        }
    }

    public function updateParentInfo()
    {
        $campusid = session('member_campusid');
        $sessionid = session('member_sessionid');
        $user_id = session('member_userid');
        $date = date('Y-m-d H:i:s');
        
        $studentID = $this->request->getPost('student_id');
        $father_cnic = $this->request->getPost('f_cnic');
        $father_name = $this->request->getPost('father_name');

        $parentInfo = $this->db->query('select * from parents WHERE father_cnic="'.$father_cnic.'" AND parent_id IN(SELECT parent_id from students WHERE campus_id='.$campusid.')')->getRow();
        
        if($parentInfo) {
            $data = [
                'parent_id' => trim($parentInfo->parent_id),
                'updated_date' => $date,
                'user_id' => $user_id
            ];
            
            $this->db->table('students')
                ->where('student_id', $studentID)
                ->where('campus_id', $campusid)
                ->update($data);
        } else {
            $dataParent = [
                'f_name' => trim($father_name),
                'father_cnic' => trim($father_cnic),
                'password' => trim('$2y$11$devU5YfJe43QwVEdvRU3UevZO.vlbd3u56yeGYt2k1d2c56VYjm/a'),
                'campus_id' => $campusid,
                'created_date' => $date,
                'user_id' => $user_id,
            ];

            $this->db->table('parents')->insert($dataParent);
            $new_parent_id = $this->db->insertID();

            $data = [
                'parent_id' => trim($new_parent_id),
                'updated_date' => $date,
                'user_id' => $user_id
            ];
            
            $this->db->table('students')
                ->where('student_id', $studentID)
                ->where('campus_id', $campusid)
                ->update($data);
        }
    }

    public function getStatus()
	{
	    return $this->response->setJSON(['status' => 'ok', 'message' => 'getStatus stub']);
	}


    public function uploadImage()
    {
        $response = service('response');
        $response->setContentType('application/json');
        
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = 2048;
        
        $upload = \Config\Services::upload();
        $upload->initialize($config);
        
        if (!$upload->do_upload('file')) {
            $error = ['error' => $upload->display_errors()];
            return $this->respond($error);
        } else {
            $data = $upload->data();
            $success = ['success' => $data['file_name']];
            return $this->respond($success);
        }
    }

    // ... (Continue with remaining methods)

    public function delete()
    {
        check_permission('admin-del-student');
        $id = (int)$this->request->getGet('id');

        $user_id = session('member_userid');
        $date = date('Y-m-d H:i:s');

        $this->db->transStart();

        $data = [
            'status' => 5,
            'updated_date' => $date,
            'user_id' => $user_id
        ];

        $this->db->table('student_class')
            ->where('student_id', $id)
            ->where('status', 1)
            ->update($data);

        $this->db->table('students')
            ->where('student_id', $id)
            ->update($data);

        $this->db->transComplete();
        
        return $this->respond(['success' => true, 'msg' => 'Delete Student Success']);
    }
}