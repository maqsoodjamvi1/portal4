<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use stdClass;

class Studentsbulk extends BaseController
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

        return view('admin/studentsbulk', $this->template_data);
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
    // Read the filter robustly (accept either name)
    $cls_sec_id = (int) ($this->request->getVar('cls_sec_id')
                 ?? $this->request->getVar('section_id')  // backward compat
                 ?? 0);

    $campusid  = (int) $this->session->get('member_campusid');
    $sessionid = (int) $this->session->get('member_sessionid');

    // Sections the user can see (unchanged)
    $currentrole = currentUserRoles();
    $sectionsclassinfo = in_array(5, $currentrole)
        ? teacherSubjectSections()
        : userClassSections();

    // --- Query students filtered by cls_sec_id when provided ---
    $params = [$campusid, $sessionid];
    $sql = 'SELECT sc.student_id, sc.cls_sec_id, s.first_name, s.last_name
              FROM student_class sc
              JOIN students s
                ON s.student_id = sc.student_id
               AND s.status = 1
               AND s.campus_id = ?
             WHERE sc.session_id = ?';

    if ($cls_sec_id > 0) {
        $sql .= ' AND sc.cls_sec_id = ?';
        $params[] = $cls_sec_id;
    }

    $sql .= ' ORDER BY s.first_name, s.last_name';

    $rows = $this->db->query($sql, $params)->getResult();

    // ---- render minimal table: Student | Class Section | Action ----
    $opt = function(array $sections, int $selected): string {
        $out = '';
        foreach ($sections as $sec) {
            $val = (int)($sec['cls_sec_id'] ?? $sec['section_id'] ?? 0);   // prefer cls_sec_id
            $text = $sec['sectionclassname']
                 ?? (($sec['class_name'] ?? '') . ' (' . ($sec['section_name'] ?? '') . ')');
            if (!$val || !$text) continue;
            $out .= '<option value="'.esc($val).'"'.($selected===$val?' selected':'').'>'.esc($text).'</option>';
        }
        return $out;
    };

    $html  = '<table class="table table-striped table-bordered table-hover" id="students-datatable" style="font-size:12px;width:100%">';
    $html .= '  <thead><tr><th>Student</th><th style="min-width:260px;">Class Section</th><th style="width:120px;">Action</th></tr></thead><tbody>';

    if ($rows) {
        foreach ($rows as $r) {
            $sid   = (int)$r->student_id;
            $csid  = (int)$r->cls_sec_id;
            $name  = trim(($r->first_name ?? '').' '.($r->last_name ?? ''));

            $html .= '<tr>';
            $html .= '  <td class="align-middle">'.esc($name ?: 'Unnamed').'</td>';
            $html .= '  <td class="align-middle">
                          <input type="hidden" name="student_id" value="'.$sid.'">
                          <select class="form-control form-control-sm" id="cls_sec_'.$sid.'">'.
                          $opt($sectionsclassinfo, $csid).
                         '</select>
                        </td>';
            $html .= '  <td class="align-middle">
                          <button type="button" class="btn btn-primary btn-sm js-save" data-id="'.$sid.'">Move</button>
                        </td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="3" class="text-center text-muted">No students found for the selected filter.</td></tr>';
    }

    $html .= '</tbody></table>
<script>
(function(){
  $("#students-datatable").off("click", ".js-save").on("click", ".js-save", function(){
    var $btn = $(this);
    var sid  = $btn.data("id");
    var csid = $("#cls_sec_"+sid).val();
    if(!csid){ toastr.warning("Please select a class section."); return; }
    $.ajax({
      url: "'.base_url('admin/studentsbulk/savestudent').'",
      type: "POST",
      data: {
        student_id: sid,
        cls_sec_id: csid,
        section_id: csid, // backward compat if server still reads section_id
        "'.csrf_token().'": "'.csrf_hash().'"
      },
      beforeSend: function(){ $btn.prop("disabled", true).text("Saving..."); },
      success: function(res){
        var json = res; try{ if(typeof res==="string") json = JSON.parse(res); }catch(e){}
        if(json && json.success){ toastr.success(json.msg || "Updated class section."); }
        else { toastr.error((json && json.msg) || "Failed to update."); }
      },
      error: function(){ toastr.error("Server error."); },
      complete: function(){ $btn.prop("disabled", false).text("Save"); }
    });
  });
})();
</script>';

    echo $html;
    exit;
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
        $studentsInfo = $this->request->getPost('student_id');
        $sectionIDs = $this->request->getPost('section_id');
        $previousBalance = $this->request->getPost('previous_balance');
        $currentBalance = $this->request->getPost('current_balance');
        $discountedAmounts = $this->request->getPost('discounted_amount');

        $schoolinfo = getSchoolInfo();

        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        check_permission('admin-edit-student');

        foreach ($studentsInfo as $key => $student) {

            $section_id = $sectionIDs[$key];

            $ClassSectioninfo = $this->db->table('class_section')->where('cls_sec_id', $section_id)->get()->getRow();

            $prevBalace = $previousBalance[$key];
            $discountedAmount = $discountedAmounts[$key];

            $data = [
                'class_id' => trim($ClassSectioninfo->class_id),
                'discounted_amount' => trim($discountedAmount),
                'status' => 1,
                'updated_date' => $date,
                'user_id' => $user_id
            ];

            $this->db->table('students')->where('student_id', $student)->update($data);

            $studentclass = [
                'student_id' => $student,
                'session_id' => $sessionid,
                'cls_sec_id' => $section_id,
                'status' => 1,
                'created_date' => $date,
                'user_id' => $user_id
            ];

            $this->db->table('student_class')->insert($studentclass);

            $feeTypeInfo = $this->db->table('fee_type')
                ->where('system_id', $schoolinfo->system_id)
                ->where('is_monthly_fee', 1)
                ->where('s_flag', 1)
                ->get()->getRow();

            $fee_month = date('m/Y');
            $issuedate = date('Y-m-d');
            $duedate = date('Y-m-d', strtotime('+10 days'));

            $feeChalaninfo = $this->db->table('fee_chalan')
                ->where('fee_type_id', $feeTypeInfo->fee_type_id)
                ->where('student_id', $student)
                ->where('fee_month', $fee_month)
                ->get()->getRow();

            if (empty($feeChalaninfo)) {
                $feeData = [
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
                ];
                $this->db->table('fee_chalan')->insert($feeData);
            }
        }
        return $this->response->setJSON(['success' => true, 'msg' => 'Edit Student Success']);
    }
}
