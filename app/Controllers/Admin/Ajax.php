<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\ParentModel;


class Ajax extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }


   public function check_parent_cnic()
  {
    // Log received data for debugging
    log_message('debug', 'Received POST data: ' . print_r($this->request->getPost(), true));
    
    // Validate AJAX request
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(405)->setJSON(['error' => 'Method Not Allowed']);
    }

    // Get input data
    $post = $this->request->getPost();
    $cnic = $post['cnic'] ?? null;
    $campus_id = $post['campus_id'] ?? null;

    // Validate inputs
    if (empty($cnic) || empty($campus_id)) {
        log_message('error', 'Missing parameters: CNIC: '.$cnic.', Campus ID: '.$campus_id);
        return $this->response->setStatusCode(400)->setJSON(['error' => 'CNIC and Campus ID are required']);
    }

    // Sanitize CNIC format
    $clean_cnic = preg_replace('/[^0-9\-]/', '', $cnic);
    
    // Validate CNIC format
    if (!preg_match('/^\d{5}-\d{7}-\d{1}$/', $clean_cnic)) {
        return $this->response->setJSON([
            'exists' => false, 
            'message' => 'Invalid CNIC format. Valid format: XXXXX-XXXXXXX-X'
        ]);
    }

    try {
        // Load parent model using CodeIgniter's service
        $parentModel = model('ParentModel');
        
        // Check if parent exists
        $parent = $parentModel->where('father_cnic', $clean_cnic)
                              ->where('campus_id', $campus_id)
                              ->first();

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
            'error' => 'Server error while processing request'
        ]);
    }
}
    
    public function index(): void
    {
        // Optional default 
    }

    public function selectClassFee(): ResponseInterface
    {
        $campusid = session('member_campusid');
        $section_id = $this->request->getPost('section_id');
        $schoolinfo = getSchoolInfo();
        $session_id = session('member_sessionid');

        $amount = 0;
        $query = $this->db->query(
            "SELECT amount FROM fee_amount WHERE fee_type_id = 
                (SELECT fee_type_id FROM fee_type WHERE system_id=? AND is_monthly_fee=1 AND s_flag=1) 
                AND class_id = 
                (SELECT class_id FROM class_section WHERE status=1 AND cls_sec_id=?) 
                AND campus_id=? AND session_id=?",
            [$schoolinfo->system_id, $section_id, $campusid, $session_id]
        );
        $row = $query->getRow();
        if ($row) {
            $amount = $row->amount;
        }else{
            $amount = 0;
        }

        return $this->response->setBody($amount);
    }

    public function updatestudentstatus(): ResponseInterface
    {
        $schoolinfo = getSchoolInfo();
        $user_id = session('member_userid');
        $date = date('Y-m-d H:i:s');

        $student_id = $this->request->getPost('studentID');
        $student_fee = $this->request->getPost('student_fee');
        $class_fee = $this->request->getPost('classFee');
        $cls_sec_id = $this->request->getPost('cls_secID');
        $sessionid = session('member_sessionid');
        $campusid = session('member_campusid');

        if (empty($cls_sec_id)) {
            return $this->response->setJSON(['error' => true, 'msg' => 'Class section is required']);
        }

        $classSection = $this->db->table('class_section')
            ->where(['cls_sec_id' => $cls_sec_id, 'campus_id' => $campusid, 'status' => 1])
            ->get()->getRow();

        $feeType = $this->db->table('fee_type')
            ->where(['system_id' => $schoolinfo->system_id, 'is_monthly_fee' => 1, 's_flag' => 1])
            ->get()->getRow();

        $feeAmount = $this->db->table('fee_amount')
            ->where([
                'session_id' => $sessionid,
                'fee_type_id' => $feeType->fee_type_id ?? 0,
                'class_id' => $classSection->class_id ?? 0
            ])->get()->getRow();

        $feeDiscount = $feeAmount ? ((float) $feeAmount->amount - (float) $student_fee) : 0;

        $this->db->transStart();

        $this->db->table('students')->where('student_id', $student_id)->update([
            'discounted_amount' => trim($feeDiscount),
            'status' => 1,
            'updated_date' => $date,
            'user_id' => $user_id
        ]);

        $stdinfo = $this->db->table('student_class')
            ->where(['student_id' => $student_id, 'session_id' => $sessionid])
            ->get()->getRow();

        $studentclass = [
            'student_id' => $student_id,
            'session_id' => $sessionid,
            'cls_sec_id' => $cls_sec_id,
            'status' => 1,
            'updated_date' => $date,
            'user_id' => $user_id
        ];

        if ($stdinfo) {
            $this->db->table('student_class')
                ->where(['student_id' => $student_id, 'session_id' => $sessionid])
                ->update($studentclass);
        } else {
            $this->db->table('student_class')->insert($studentclass);
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Successfully updated']);
    }

    public function changeCampus(): ResponseInterface
    {
        $id = trim($this->request->getPost('id'));
        $row = $this->db->table('campus')->where('campus_id', $id)->get()->getRowArray();

        if ($row) {
            $this->session->set(['member_campusid' => $row['campus_id']]);
            return $this->response->setBody('true');
        }

        return $this->response->setBody('false');
    }

    public function selectSession(): ResponseInterface
    {
        $session_id = trim($this->request->getPost('session_id'));

        $academic_session_info = $this->db->table('academic_session')
            ->where('session_id', $session_id)
            ->get()->getRow();
 

        if ($academic_session_info) {
            $this->session->set(['member_sessionid' => $academic_session_info->session_id]);
            return $this->response->setBody('true');
        }

        return $this->response->setBody('false');
    }

    public function setboolattributeteachers(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table($tblname)->where('tid', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setboolattribute2(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table($tblname)->where('student_id', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setboolattributeexam(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table($tblname)->where('eid', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setboolattributetest(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table($tblname)->where('t_series_id', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setboolattributeSchoolType(): ResponseInterface
    {
        $campusid = session('member_campusid');
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table('school_timing_types')->where('campus_id', $campusid)->update(['status' => 0]);
        $this->db->table($tblname)->where('type_id', $id)->set($sfield, $sval)->update();

        return $this->response->setBody('success');
    }

    public function setfeetypestatus(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table($tblname)->where('fee_type_id', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setboolattribute(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table($tblname)->where('id', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setboolIndexable(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table($tblname)->where('content_id', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setboolattributeIsTrial(): ResponseInterface
    {
        $db = db_connect('timeschool_trail');
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $db->table($tblname)->where('bill_id', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setboolattributeFee(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $schoolinfo = getSchoolInfo();

        $this->db->table($tblname)->set($sfield, 0)->update();

        $id = (int) $this->request->getPost('id');
        $this->db->table($tblname)->where(['system_id' => $schoolinfo->system_id, 'fee_type_id' => $id])->set($sfield, $sval)->update();

        return $this->response->setBody('success');
    }

    public function setboolattributenotice(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = (int) $this->request->getPost('id');

        $this->db->table($tblname)->where('notice_id', $id)->set($sfield, $sval)->update();
        return $this->response->setBody('success');
    }

    public function setfieldvalue(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $id = $this->request->getPost('id');

        $this->db->table($tblname)
            ->where(['id' => $id, 'site_id' => $this->site_id ?? 0])
            ->set($sfield, $sval)
            ->update();

        return $this->response->setBody('success');
    }

    public function setunique(): ResponseInterface
    {
        $tblname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $rowid = $this->request->getPost('rowid');

        $this->db->transStart();

        $this->db->table($tblname)->update([$sfield => 0]);
        $this->db->table($tblname)->where('id', $rowid)->update([$sfield => 1]);

        $this->db->transComplete();

        return $this->response->setBody('success');
    }

    public function set_sortid(): ResponseInterface
    {
        $tbname = $this->request->getPost('tbname');
        $sfield = $this->request->getPost('tbfield');
        $sval = $this->request->getPost('tbfieldvalue');
        $rowid = $this->request->getPost('rowid');

        $campusid = session('member_campusid');

        $this->db->table($tbname)
            ->where(['id' => $rowid, 'campus_id' => $campusid])
            ->update([$sfield => $sval]);

        return $this->response->setBody('success');
    }

    public function genkey(): void
    {
        // Implement if needed
    }

    public function selectsectionbyClass(): ResponseInterface
    {
        $campusid = session('member_campusid');
        $class_id = $this->request->getPost('class_id');

        $sections_info = $this->db->table('class_section')
            ->where(['class_id' => $class_id, 'campus_id' => $campusid, 'status' => 1])
            ->get()->getResult();

        $sections = '';
        foreach ($sections_info as $row) {
            $classinfo = $this->db->table('classes')->where('class_id', $class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $row->section_id)->get()->getRow();
            $sections .= "<option value='{$row->cls_sec_id}'>{$sectioninfo->section_name}({$classinfo->class_name})</option>";
        }

        return $this->response->setBody($sections);
    }

    public function selectExam(): ResponseInterface
    {
        $session_id = $this->request->getPost('session_id');
        $campus_id = session('member_campusid');

        $exam_info = $this->db->table('exam')
            ->where(['session_id' => $session_id, 'campus_id' => $campus_id])
            ->get()->getResult();

        $exam = '';
        foreach ($exam_info as $row) {
            $exam .= "<option value='{$row->eid}'>{$row->exam_name}</option>";
        }

        return $this->response->setBody($exam);
    }

    public function selectsubjectbySection(): ResponseInterface
    {
        $section_id = $this->request->getPost('section_id');
        $userid = session('member_userid');
        $currentrole = currentUserRoles();
        $classsubjects = '<option value="">Select Subject</option>';

        if (in_array(5, $currentrole)) {
            $section_subjects_info = $this->db->query(
                "SELECT * FROM teacher_subjects WHERE tid = {$userid} AND status=1 AND sec_sub_id IN
                (SELECT sec_sub_id FROM section_subjects WHERE status=1 AND cls_sec_id = {$section_id})"
            )->getResult();

            foreach ($section_subjects_info as $section_subjects) {
                $section_info = $this->db->table('section_subjects')
                    ->where(['status' => 1, 'sec_sub_id' => $section_subjects->sec_sub_id])
                    ->get()->getRow();
                $subjects_info = $this->db->table('allsubject')->where('sid', $section_info->subject_id)->get()->getRow();
                if ($subjects_info) {
                    $classsubjects .= "<option value='{$subjects_info->sid}'>{$subjects_info->subject_name}</option>";
                }
            }
        } else {
            $section_subjects_info = $this->db->table('section_subjects')
                ->where(['status' => 1, 'cls_sec_id' => $section_id])
                ->get()->getResult();

            foreach ($section_subjects_info as $section_subjects) {
                $subjects_info = $this->db->table('allsubject')->where('sid', $section_subjects->subject_id)->get()->getRow();
                if ($subjects_info) {
                    $classsubjects .= "<option value='{$subjects_info->sid}'>{$subjects_info->subject_name}</option>";
                }
            }
        }

        return $this->response->setBody($classsubjects);
    }

    public function selectSectionSubjectbySection(): ResponseInterface
    {
        $section_id = $this->request->getPost('section_id');
        $userid = session('member_userid');
        $currentrole = currentUserRoles();

        $classsubjects = '<option value="">Select Subject</option>';

        if (in_array(5, $currentrole)) {
            $section_subjects_info = $this->db->query(
                "SELECT * FROM teacher_subjects WHERE status=1 AND tid = {$userid} AND sec_sub_id IN
                (SELECT sec_sub_id FROM section_subjects WHERE status=1 AND cls_sec_id = {$section_id})"
            )->getResult();

            foreach ($section_subjects_info as $section_subjects) {
                $section_info = $this->db->table('section_subjects')
                    ->where(['status' => 1, 'sec_sub_id' => $section_subjects->sec_sub_id])
                    ->get()->getRow();

                $subjects_info = $this->db->table('allsubject')->where('sid', $section_info->subject_id)->get()->getRow();
                if ($subjects_info) {
                    $classsubjects .= "<option value='{$section_subjects->sec_sub_id}'>{$subjects_info->subject_name}</option>";
                }
            }
        } else {
            $section_subjects_info = $this->db->table('section_subjects')
                ->where(['status' => 1, 'cls_sec_id' => $section_id])
                ->get()->getResult();

            foreach ($section_subjects_info as $section_subjects) {
                $subjects_info = $this->db->table('allsubject')->where('sid', $section_subjects->subject_id)->get()->getRow();
                if ($subjects_info) {
                    $classsubjects .= "<option value='{$section_subjects->sec_sub_id}'>{$subjects_info->subject_name}</option>";
                }
            }
        }

        return $this->response->setBody($classsubjects);
    }

    public function selecttermbySession(): ResponseInterface
    {
        $schoolinfo = getSchoolInfo();
        $session_id = $this->request->getPost('session_id');

        $terms_session_info = $this->db->table('terms_session')
            ->where(['session_id' => $session_id, 'system_id' => $schoolinfo->system_id])
            ->get()->getResult();

        $termsession = '<option value="">Select Terms</option>';
        foreach ($terms_session_info as $terms_session) {
            $terms_info = $this->db->table('terms')->where('term_id', $terms_session->term_id)->get()->getRow();
            if ($terms_info) {
                $termsession .= "<option value='{$terms_session->term_session_id}'>{$terms_info->name}</option>";
            }
        }

        return $this->response->setBody($termsession);
    }

    public function selectcategoriesbysubject(): ResponseInterface
    {
        $subject_id = $this->request->getPost('subject_id');

        $ecategories_info = $this->db->table('ecategories')->where('e_sub_id', $subject_id)->get()->getResult();

        $categoriestopics = '<option value="">Select Category </option>';
        foreach ($ecategories_info as $category) {
            $categoriestopics .= "<option value='{$category->sub_cat_id}'>{$category->cat_title}</option>";
        }

        return $this->response->setBody($categoriestopics);
    }

    public function selecttopicbycategories(): ResponseInterface
    {
        $sub_cat_id = $this->request->getPost('cat_id');

        $category_topic_info = $this->db->table('esub_cat_topic')->where('sub_cat_id', $sub_cat_id)->get()->getResult();

        $categoriestopics = '<option value="">Select Topic </option>';
        foreach ($category_topic_info as $category_topic) {
            $categoriestopics .= "<option value='{$category_topic->sub_cat_topic_id}'>{$category_topic->topic}</option>";
        }

        return $this->response->setBody($categoriestopics);
    }

    public function selectSkillsbyTopic(): ResponseInterface
    {
        $topic_id = $this->request->getPost('topic_id');

        $topic_skills_info = $this->db->table('topic_skills')->where('sub_cat_topic_id', $topic_id)->get()->getResult();

        $topicsskills = '<option value="">Select Toppic Skills</option>';
        foreach ($topic_skills_info as $topic_skills) {
            $topicsskills .= "<option value='{$topic_skills->topic_skills_id}'>{$topic_skills->topic_skill}</option>";
        }

        return $this->response->setBody($topicsskills);
    }

    public function selectmulTermsWeeks(): ResponseInterface
    {
        $session_id = session('member_sessionid');
        $term_session_ids = $this->request->getPost('termsession_id');

        if (!is_array($term_session_ids)) {
            $term_session_ids = [$term_session_ids];
        }

        $term_session_str = implode(',', array_map('intval', $term_session_ids));

        $terms_week_info = $this->db->query("SELECT * FROM term_weeks WHERE term_session_id IN ({$term_session_str})")
            ->getResult();

        $termweek = '';
        foreach ($terms_week_info as $term_week) {
            if (trim($term_week->week_name) !== '') {
                $termweek .= "<option value='{$term_week->term_weeks_id}'>{$term_week->week_name}</option>";
            }
        }

        return $this->response->setBody($termweek);
    }

    public function selectTermWeeks(): ResponseInterface
    {
        $term_session_id = $this->request->getPost('term_id');

        $terms_week_info = $this->db->table('term_weeks')
            ->where('term_session_id', $term_session_id)
            ->get()->getResult();

        $termweek = '';
        foreach ($terms_week_info as $term_week) {
            if (trim($term_week->week_name) !== '') {
                $termweek .= "<option value='{$term_week->term_weeks_id}'>{$term_week->week_name}</option>";
            }
        }

        return $this->response->setBody($termweek);
    }

    public function selectClassSubCat(): ResponseInterface
    {
        $class_id = $this->request->getPost('class_id');
        $subject_id = $this->request->getPost('subject_id');

        $subcat = '';
        $sub_category = $this->db->table('sub_category')
            ->where('sub_id', $subject_id)
            ->get()->getResult();

        foreach ($sub_category as $row) {
            $subjectcategory = $this->db->table('class_sub_cat')
                ->where(['class_id' => $class_id, 'sub_cat_id' => $row->sub_cat_id])
                ->get()->getRow();

            $checked = ($subjectcategory && $row->sub_cat_id == $subjectcategory->sub_cat_id) ? 'checked' : '';

            $subcat .= "<label><input type='checkbox' name='sub_cat_id[]' value='{$row->sub_cat_id}' {$checked}> {$row->cat_name}</label><br>";
        }

        return $this->response->setBody($subcat);
    }

    public function check_username(): ResponseInterface
    {
        $username = $this->request->getGet('username');
        $userinfo = $this->db->table('users')->where('username', $username)->get()->getRow();
        return $this->response->setBody($userinfo ? 'false' : 'true');
    }

    public function check_value(): ResponseInterface
    {
        $schoolinfo = getSchoolInfo();
        $field = $this->request->getGet('field');
        $table = $this->request->getGet('table');

        if ($table && $field) {
            $field_value = $this->request->getGet($field);
            $info = $this->db->table($table)
                ->where($field, $field_value)
                ->where('system_id', $schoolinfo->system_id)
                ->get()->getRow();

            return $this->response->setBody($info ? 'false' : 'true');
        }

        return $this->response->setBody('true');
    }

   public function check_emp_value(): ResponseInterface
{
    $field = $this->request->getGet('field');
    $table = $this->request->getGet('table');
    $id = $this->request->getGet('id'); // Get the ID for edit mode

    if ($table && $field) {
        $field_value = $this->request->getGet('value'); // Note: using 'value' parameter
        
        $builder = $this->db->table($table)->where($field, $field_value);
        
        // If ID is provided, exclude that record (for edit mode)
        if (!empty($id) && is_numeric($id)) {
            $builder->where('id !=', $id);
        }
        
        $info = $builder->get()->getRow();
        
        // Return 'false' if exists (invalid), 'true' if doesn't exist (valid)
        return $this->response->setBody($info ? 'false' : 'true');
    }

    return $this->response->setBody('true');
}

    public function check_fee_month(): ResponseInterface
    {
        $campus_id = session('member_campusid');
        $field_value = $this->request->getGet('fee_month');

        $query = "SELECT * FROM fee_chalan WHERE fee_month = ? AND student_id IN (SELECT student_id FROM students WHERE campus_id = ?)";
        $info = $this->db->query($query, [$field_value, $campus_id])->getRow();

        return $this->response->setBody($info ? 'false' : 'true');
    }

    public function check_father_cinic(): ResponseInterface
    {
        $campus_id = session('member_campusid');
        $father_cnic = trim($this->request->getPost('father_cnic'));

        if ($father_cnic) {
            $query = "SELECT * FROM parents WHERE father_cnic = ? AND parent_id IN (SELECT parent_id FROM students WHERE campus_id = ?)";
            $info = $this->db->query($query, [$father_cnic, $campus_id])->getRow();

            if ($info) {
                return $this->response->setJSON([
                    'parent_id' => $info->parent_id,
                    'religion' => $info->religion,
                    'f_name' => $info->f_name,
                    'father_contact' => $info->father_contact,
                    'father_email' => $info->father_email,
                    'father_occupation' => $info->father_occupation,
                    'father_office_address' => $info->father_office_address,
                    'address_line1' => $info->address_line1,
                    'city' => $info->city,
                    'm_name' => $info->m_name,
                    'mother_contact' => $info->mother_contact,
                    'hear_source' => $info->hear_source,
                    'emergency_contact_person' => $info->emergency_contact_person,
                    'emergency_contact' => $info->emergency_contact,
                    'a_address' => $info->a_address
                ]);
            }

            return $this->response->setBody('null');
        }

        return $this->response->setBody('true');
    }

    public function pay_fee(): ResponseInterface
    {
        $chalan_id = $this->request->getPost('chalan_id');
        $studentid = $this->request->getPost('studentid');
        $paid_date = DateTime::createFromFormat('d/m/Y', $this->request->getPost('paid_date'))->format('Y-m-d');
        $fee_amount = $this->request->getPost('fee_amount');
        $fine = $this->request->getPost('fine');
        $fineamount = $this->request->getPost('fineamount');
        $paid_amount = $this->request->getPost('paid_amount');
        $discountAmount = $this->request->getPost('discountAmount');
        $user_id = session('member_userid');
        $date = date('Y-m-d');

        $this->db->transStart();

        $chalaninfo = $this->db->table('fee_chalan')->where('chalan_id', $chalan_id)->get()->getRow();

        if (!empty($fineamount)) {
            $status = $fine === 'paywithfine' ? 'paid' : ($fine === 'paywithoutfine' ? 'unpaid' : 'discounted');

            $this->db->table('fee_chalan')->insert([
                'student_id' => $studentid,
                'issue_date' => $chalaninfo->issue_date,
                'due_date' => $chalaninfo->due_date,
                'fee_month' => $chalaninfo->fee_month,
                'amount' => $fineamount,
                'discount' => 0,
                'status' => $status,
                'fee_type_id' => 0,
                'paid_date' => $paid_date,
                'user_id' => $user_id,
                'created_date' => $date,
                'updated_date' => $date
            ]);
        }

        if ($paid_amount == $fee_amount) {
            $this->db->table('fee_chalan')->where('chalan_id', $chalan_id)->update([
                'paid_date' => $paid_date,
                'status' => 'paid',
                'user_id' => $user_id,
                'updated_date' => $date
            ]);
        } elseif ($discountAmount == $fee_amount) {
            $this->db->table('fee_chalan')->where('chalan_id', $chalan_id)->update([
                'paid_date' => $paid_date,
                'status' => 'discounted',
                'user_id' => $user_id,
                'updated_date' => $date
            ]);
        } else {
            $updatedamount = $fee_amount - ($paid_amount + $discountAmount);
            $paidDiscounted = $paid_amount + $discountAmount;

            if ($updatedamount == 0) {
                $dbpayable = $chalaninfo->discount + $paid_amount;
                $this->db->table('fee_chalan')->where('chalan_id', $chalan_id)->update([
                    'issue_date' => $chalaninfo->issue_date,
                    'due_date' => $chalaninfo->due_date,
                    'fee_month' => $chalaninfo->fee_month,
                    'amount' => $dbpayable,
                    'status' => 'paid',
                    'paid_date' => $paid_date,
                    'user_id' => $user_id,
                    'updated_date' => $date
                ]);
            } else {
                $dbpayable2 = $chalaninfo->amount - $paidDiscounted;
                $this->db->table('fee_chalan')->where('chalan_id', $chalan_id)->update([
                    'issue_date' => $chalaninfo->issue_date,
                    'due_date' => $chalaninfo->due_date,
                    'fee_month' => $chalaninfo->fee_month,
                    'amount' => $dbpayable2,
                    'user_id' => $user_id,
                    'updated_date' => $date
                ]);

                $this->db->table('fee_chalan')->insert([
                    'student_id' => $studentid,
                    'issue_date' => $chalaninfo->issue_date,
                    'due_date' => $chalaninfo->due_date,
                    'fee_month' => $chalaninfo->fee_month,
                    'amount' => $paid_amount,
                    'discount' => 0,
                    'status' => 'paid',
                    'fee_type_id' => $chalaninfo->fee_type_id,
                    'paid_date' => $paid_date,
                    'user_id' => $user_id,
                    'created_date' => $date,
                    'updated_date' => $date
                ]);
            }

            if (!empty($discountAmount)) {
                $this->db->table('fee_chalan')->insert([
                    'student_id' => $studentid,
                    'issue_date' => $chalaninfo->issue_date,
                    'due_date' => $chalaninfo->due_date,
                    'fee_month' => $chalaninfo->fee_month,
                    'amount' => $discountAmount,
                    'discount' => 0,
                    'status' => 'discounted',
                    'fee_type_id' => $chalaninfo->fee_type_id,
                    'paid_date' => $paid_date,
                    'user_id' => $user_id,
                    'created_date' => $date,
                    'updated_date' => $date
                ]);
            }
        }

        $this->db->transComplete();

        return $this->response->setBody('Chalan Paid Successfully');
    }

    public function get_students(): ResponseInterface
    {
        $eid = (int) $this->request->getPost('eid');
        $session_id = (int) $this->request->getPost('session_id');
        $campus_id = (int) $this->request->getPost('campus_id');
        $id = (int) $this->request->getPost('section_id');
        $subject_id = (int) $this->request->getPost('subject_id');

        $studentsList = '';

        $classsectioninfo = $this->db->table('class_section')
            ->where(['cls_sec_id' => $id, 'status' => 1])
            ->get()->getRow();

        $studentsresults = $this->db->query(
            "SELECT t1.campus_id, t2.student_id, t2.obtained_marks, t2.subject_id
            FROM exam t1, studentsresults t2
            WHERE t2.eid = {$eid} AND t2.class_id = {$classsectioninfo->class_id}
            AND t1.campus_id = {$campus_id}
            GROUP BY t2.student_id, t2.subject_id, t2.obtained_marks
            ORDER BY t2.class_id ASC"
        )->getResult();

        $classsubjectsinfo = $this->db->table('section_subjects')
            ->where(['cls_sec_id' => $id, 'status' => 1])
            ->get()->getResult();

        $studentsList .= '<input type="hidden" name="eeid" value="' . ($studentsresults ? $eid : 0) . '">';
        $studentsList .= '<input type="hidden" name="session_id" value="' . $session_id . '">';
        $studentsList .= '<input type="hidden" name="campus_id" value="' . $campus_id . '">';
        $studentsList .= '<input type="hidden" name="eid" value="' . $eid . '">';
        $studentsList .= '<input type="hidden" name="class_id" value="' . $id . '">';

        $classstudents = $this->db->query("SELECT * FROM student_class WHERE status = 1 AND cls_sec_id = {$id}")->getResult();
        $classesinfo = $this->db->table('classes')->where('class_id', $id)->get()->getRow();
        $session_id_info = $this->db->table('academic_session')->where('session_id', $session_id)->get()->getRow();
        $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
        $exam_info = $this->db->table('exam')->where('eid', $eid)->get()->getRow();

        $studentsList .= "<table class='table' style='width:100%;'><tr>
            <th>Session</th><th>{$session_id_info->session_name}</th>
            <th>Campus</th><th>{$campus_info->campus_name}</th>
            <th>Exam</th><th>{$exam_info->exam_name}</th>
            <th>Class</th><th>{$classesinfo->class_name}</th>
        </tr></table>";

        $studentsList .= '<table class="table" style="width:100%;">';
        $studentsList .= '<tr><td>#</td><th style="width:5%;">Photo</th><th style="width:12%">Student</th>';

        $width = count($classsubjectsinfo) > 0 ? (int)(85 / count($classsubjectsinfo)) : 85;

        foreach ($classsubjectsinfo as $subject) {
            $studentsubject = $this->db->table('allsubject')->where('sid', $subject->subject_id)->get()->getRow();
            $datesheetinfo = $this->db->table('datesheet')
                ->where(['cls_sec_id' => $id, 'sec_sub_id' => $subject->subject_id, 'eid' => $eid])
                ->get()->getRow();
            $total_marks = $datesheetinfo->total_marks ?? '';
            $studentsList .= "<th style='width:{$width}%;'>{$studentsubject->subject_short_name}<br>{$total_marks}</th>";
        }
        $studentsList .= '</tr>';

        $i = 1;
        foreach ($classstudents as $row) {
            $studentsinfo = $this->db->table('students')->where('student_id', $row->student_id)->get()->getRow();
            if (!$studentsinfo) continue;

            $studentName = $studentsinfo->first_name . ' ' . $studentsinfo->last_name;
            $imgpath = FCPATH . 'uploads/' . $studentsinfo->profile_photo;

            $profile_photo = ($studentsinfo->profile_photo && file_exists($imgpath))
                ? "<img style='width:50px;height:50px;text-align:center;display:block;border-radius:30px;margin:0 auto;' src='" . base_url("uploads/{$studentsinfo->profile_photo}") . "'>"
                : "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";

            $studentsList .= "<tr><td>{$i}</td><td>{$profile_photo}</td>
                <td><b>{$studentsinfo->reg_no}</b><br>{$studentName}
                <input type='hidden' name='student_id[]' value='{$studentsinfo->student_id}' class='form-control'></td>";
            $i++;

            foreach ($classsubjectsinfo as $subject) {
                $resultsdetail = $this->db->table('studentsresults')
                    ->where(['student_id' => $row->student_id, 'subject_id' => $subject->subject_id, 'eid' => $eid])
                    ->get()->getRow();
                $obtained_marks = $resultsdetail->obtained_marks ?? '';

                $studentsList .= "<td style='width:{$width}%;'><input type='text' style='height:25px;padding:0 5px !important;' 
                    name='obtained_marks[{$row->student_id}][{$subject->subject_id}]' value='{$obtained_marks}' class='form-control'></td>";
            }
            $studentsList .= '</tr>';
        }
        $studentsList .= '</table>';

        return $this->response->setBody($studentsList);
    }


public function studentsOptions()
{
    // --- Inputs
    $teamId     = (int) ($this->request->getPost('team_id') ?? 0);   // preferred
    $eventIdIn  = (int) ($this->request->getPost('event_id') ?? 0);  // fallback
    $houseIdIn  = (int) ($this->request->getPost('house_id') ?? 0);  // fallback

    $q          = trim((string) ($this->request->getPost('q') ?? ''));
    $classId    = (int) ($this->request->getPost('class_id') ?? 0);
    $sectionId  = (int) ($this->request->getPost('section_id') ?? 0);
    $campusId   = (int) ($this->request->getPost('campus_id') ?? (session('member_campusid') ?? 0));
    $sessionId  = (int) ($this->request->getPost('session_id') ?? (session('member_sessionid') ?? 0));

    // --- Force table to "students" only
    $studentsTbl = 'students';

    // --- Resolve event, house, event gender from team (preferred)
    $eventId     = 0;
    $houseId     = 0;
    $eventGender = ''; // 'male' | 'female'

    if ($teamId > 0) {
        $row = $this->db->table('sports_teams t')
            ->select('t.event_id, t.house_id, e.gender AS event_gender')
            ->join('sports_events e', 'e.event_id = t.event_id', 'left')
            ->where('t.team_id', $teamId)
            ->get()->getRowArray();

        if ($row) {
            $eventId     = (int) ($row['event_id'] ?? 0);
            $houseId     = (int) ($row['house_id'] ?? 0);
            $eventGender = strtolower(trim((string) ($row['event_gender'] ?? '')));
        }
    } else {
        // fallback via posted event_id + house_id
        $eventId = $eventIdIn;
        $houseId = $houseIdIn;
        if ($eventId > 0) {
            $ev = $this->db->table('sports_events')->select('gender')->where('event_id', $eventId)->get()->getRowArray();
            if ($ev) $eventGender = strtolower(trim((string) ($ev['gender'] ?? '')));
        }
    }

    // Sanitize event gender to only 'male' or 'female'; if anything else, skip gender filter
    $eg = in_array($eventGender, ['male','female'], true) ? $eventGender : '';

    // Must know event, house, session
    if ($eventId <= 0 || $houseId <= 0 || $sessionId <= 0) {
        return $this->response->setBody('<option value="">-- Select Student (choose team/event & house) --</option>');
    }

    // Optional: campus/session columns on students table
    $hasStudentCampus  = $this->db->query("SHOW COLUMNS FROM {$studentsTbl} LIKE 'campus_id'")->getNumRows() > 0;
    $hasStudentSession = $this->db->query("SHOW COLUMNS FROM {$studentsTbl} LIKE 'session_id'")->getNumRows() > 0;

    // Build exclusion WHERE for taken students:
    // Prefer event-wide exclusion (covers same team + other teams).
    // If for any reason eventId isn't known but teamId is, fall back to same-team exclusion.
    $escEventId = $this->db->escape($eventId);
    $escTeamId  = $this->db->escape($teamId);
    if ($teamId > 0 && $eventId > 0) {
        $takenWhere = "t2.event_id = {$escEventId}";
    } elseif ($teamId > 0) {
        $takenWhere = "stm.team_id = {$escTeamId}";
    } else {
        $takenWhere = "t2.event_id = {$escEventId}";
    }

    // Build SQL with ONLY the `students` table
    $sql =
        "SELECT
            nit.student_id,
            nit.first_name, nit.last_name,
            s.date_of_birth, s.profile_photo,
            c.class_name, sec.section_name,
            TIMESTAMPDIFF(YEAR,  s.date_of_birth, CURDATE())          AS age_years,
            MOD(TIMESTAMPDIFF(MONTH, s.date_of_birth, CURDATE()), 12) AS age_months
         FROM (
           -- candidate students from the same house (+ optional campus/session) and gender rule
           SELECT s0.student_id, s0.first_name, s0.last_name
           FROM {$studentsTbl} s0
           WHERE s0.house_id = ".$this->db->escape($houseId).
           ($hasStudentCampus  && $campusId  > 0 ? " AND s0.campus_id  = ".$this->db->escape($campusId)  : "").
           ($hasStudentSession && $sessionId > 0 ? " AND s0.session_id = ".$this->db->escape($sessionId) : "").
           ($eg !== '' ? " AND LOWER(TRIM(s0.gender)) = ".$this->db->escape($eg) : "").
        ") AS nit
         -- remove those already taken by ANY team in the SAME event (including the same team)
         LEFT JOIN (
           SELECT stm.student_id
           FROM sports_team_members stm
           JOIN sports_teams t2 ON t2.team_id = stm.team_id
           WHERE {$takenWhere}
           GROUP BY stm.student_id
         ) AS taken ON taken.student_id = nit.student_id

         JOIN {$studentsTbl} s ON s.student_id = nit.student_id
         LEFT JOIN student_class sc ON sc.student_id = nit.student_id AND sc.session_id = ".$this->db->escape($sessionId)."
         LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
         LEFT JOIN classes c        ON c.class_id    = cs.class_id
         LEFT JOIN sections sec     ON sec.section_id= cs.section_id
         WHERE taken.student_id IS NULL";

    // Optional class/section filters
    if ($classId > 0)   { $sql .= " AND cs.class_id   = ".$this->db->escape($classId);   }
    if ($sectionId > 0) { $sql .= " AND cs.section_id = ".$this->db->escape($sectionId); }

    // Optional search by name
    if ($q !== '') {
        $like = $this->db->escape('%'.$q.'%');
        $sql .= " AND (nit.first_name LIKE {$like} OR nit.last_name LIKE {$like})";
    }

    $sql .= " ORDER BY nit.first_name ASC, nit.last_name ASC";

    // Execute
    $rows = $this->db->query($sql)->getResultArray();

    // Build <option> list: "Name — 8Y + 6M | Class–Section"
    $options = '<option value="">-- Select Student --</option>';
    foreach ($rows as $r) {
        $sid  = (int) ($r['student_id'] ?? 0);
        $name = trim(($r['first_name'] ?? '').' '.($r['last_name'] ?? ''));
        if ($name === '') $name = 'ID '.$sid;

        $y = (int) ($r['age_years']  ?? 0);
        $m = (int) ($r['age_months'] ?? 0);
        $ageStr = $y && $m ? "{$y}Y + {$m}M" : ($y ? "{$y}Y" : ($m ? "{$m}M" : ''));

        $classTxt   = trim((string) ($r['class_name']   ?? ''));
        $sectionTxt = trim((string) ($r['section_name'] ?? ''));
        $csLabel    = ($classTxt !== '' || $sectionTxt !== '') ? trim($classTxt.($sectionTxt !== '' ? ' - '.$sectionTxt : '')) : '';

        $bits = [];
        if ($ageStr !== '')  $bits[] = $ageStr;
        if ($csLabel !== '') $bits[] = $csLabel;

        $label = $name.(empty($bits) ? '' : (' — '.implode(' | ', $bits)));
        $options .= '<option value="'.$sid.'">'.esc($label).'</option>';
    }

    return $this->response
        ->setHeader('Content-Type', 'text/html; charset=utf-8')
        ->setBody($options);
}

/**
 * Return age like "8Y + 6M" (empty string if DOB missing/invalid)
 */


private function formatAge(?string $dob): string
{
    if (!$dob) return '';
    try {
        $d = new \DateTime($dob);
        $now = new \DateTime('today');
        if ($d > $now) return '';
        $diff = $d->diff($now);
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . 'Y';
        if ($diff->m > 0) $parts[] = $diff->m . 'M';
        if (empty($parts)) $parts[] = '0M';
        // "8Y + 6M" or "10M"
        return count($parts) > 1 ? ($parts[0] . ' + ' . $parts[1]) : $parts[0];
    } catch (\Throwable $e) {
        return '';
    }
}

 private function ageRoundedYears(?string $dob): string
    {
        if (!$dob) return '';
        try {
            $d = new \DateTime($dob);
            $now = new \DateTime('today');
            if ($d > $now) return '';
            $diff = $d->diff($now);
            $years  = (int) $diff->y;
            $months = (int) $diff->m;

            $rounded = $years + ($months >= 6 ? 1 : 0);
            return $rounded . ' Years';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Build a short "Class-Section" like "PG-A".
     * Uses classes.class_short_name (fallback to class_name),
     * and first letter of sections.section_name if short not available.
     */
    private function classSectionShort(?string $cShort, ?string $cName, ?string $secName): string
    {
        $class = trim($cShort ?: $cName ?: '');
        $sec   = '';
        if (!empty($secName)) {
            $sec = strtoupper(mb_substr(trim($secName), 0, 1));
        }
        return trim($class . ($sec ? '-' . $sec : ''));
    }


 public function individualStudentsCards()
    {
        $eventId = (int) $this->request->getPost('event_id');
        $houseId = (int) $this->request->getPost('house_id');
        $campusId  = (int) (session('member_campusid')  ?? 0);
        $sessionId = (int) (session('member_sessionid') ?? 0);

        if ($eventId <= 0 || $houseId <= 0) {
            return $this->response->setJSON(['ok' => false, 'html' => '<div class="text-muted">Select event and house.</div>']);
        }

        // Event gender: boys|girls|mixed -> male|female|all
        $ev = $this->db->table('sports_events')->select('gender')->where('event_id', $eventId)->get()->getRowArray();
        $eg = strtolower(trim($ev['gender'] ?? 'mixed'));
        $genderValue = ($eg === 'boys') ? 'male' : (($eg === 'girls') ? 'female' : 'mixed');

        // Build WHERE for gender
        $genderSql = '';
        if ($genderValue !== 'mixed') {
            $genderSql = " AND LOWER(TRIM(s.gender)) = " . $this->db->escape($genderValue) . " ";
        }

        // Exclude already taken in this event
        $sql = "
            SELECT
               s.student_id, s.first_name, s.last_name, s.date_of_birth, s.profile_photo,
               c.class_short_name, c.class_name,
               sec.section_name
            FROM students s
            LEFT JOIN student_class sc ON sc.student_id = s.student_id
                                         AND " . ($sessionId > 0 ? "sc.session_id = " . $this->db->escape($sessionId) : "1=1") . "
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c        ON c.class_id    = cs.class_id
            LEFT JOIN sections sec     ON sec.section_id= cs.section_id
            LEFT JOIN (
               SELECT se.student_id
               FROM sports_entries se
               WHERE se.event_id = " . $this->db->escape($eventId) . "
                 AND se.student_id IS NOT NULL
               GROUP BY se.student_id
            ) AS taken ON taken.student_id = s.student_id
            WHERE taken.student_id IS NULL
              AND s.house_id = " . $this->db->escape($houseId) . "
              " . ($campusId > 0 ? " AND s.campus_id = " . $this->db->escape($campusId) : "") . "
              " . ($sessionId > 0 ? " AND s.session_id = " . $this->db->escape($sessionId) : "") . "
              $genderSql
            ORDER BY s.first_name ASC, s.last_name ASC
        ";

        $rows = $this->db->query($sql)->getResultArray();

        // Build cards HTML
        $cards = [];
        $baseUpload = rtrim(base_url('upload'), '/');
        foreach ($rows as $r) {
            $name  = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            $age   = $this->ageRoundedYears($r['date_of_birth'] ?? null);
            $cls   = $this->classSectionShort($r['class_short_name'] ?? null, $r['class_name'] ?? null, $r['section_name'] ?? null);

            $photo = trim((string)($r['profile_photo'] ?? ''));
            $img   = $photo ? "{$baseUpload}/" . ltrim($photo, '/\\') : base_url('resource/img/avatar-student.png');

            $cards[] = '
              <div class="std-card" data-id="'.(int)$r['student_id'].'">
                <div class="std-photo">
                  <img src="'.esc($img).'" alt="'.esc($name).'" onerror="this.src=\''.base_url('resource/img/avatar-student.png').'\';">
                </div>
                <div class="std-name">'.esc($name).'</div>
                <div class="std-age">'.esc($age).'</div>
                <div class="std-class">'.esc($cls).'</div>
                <button type="button" class="btn btn-xs btn-outline-primary pick-btn">Select</button>
              </div>
            ';
        }

        $html = '
          <div id="students-grid">
            '.(count($cards) ? implode('', $cards) : '<div class="text-muted p-2">No eligible students found.</div>').'
          </div>
        ';

        return $this->response->setJSON(['ok' => true, 'html' => $html]);
    }
    
public function individualStudentsOptions()
{
    $sessionId = (int) (session('member_sessionid') ?? 0);
    $campusId  = (int) (session('member_campusid')  ?? 0);

    $eventId = (int) $this->request->getPost('event_id');
    $houseId = (int) $this->request->getPost('house_id');

    if ($eventId <= 0 || $houseId <= 0) {
        return $this->response->setBody('<option value="">-- Select Student --</option>');
    }

    // Get event gender filter if any
    $event = $this->db->table('sports_events')->where('event_id', $eventId)->get()->getRowArray();
    $gender = strtolower(trim($event['gender'] ?? ''));

    // Exclude students already in this event
    $subAlready = $this->db->table('sports_event_entries')
        ->select('student_id')
        ->where('event_id', $eventId)
        ->where('student_id IS NOT NULL', null, false)
        ->get()->getResultArray();
    $excludeIds = array_values(array_filter(array_map(fn($r)=> (int)$r['student_id'], $subAlready)));

    $builder = $this->db->table('students s')
        ->select("s.student_id, s.reg_no, CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) AS full_name", false)
        ->where('s.house_id', $houseId);

    if ($gender === 'male' || $gender === 'female') {
        $builder->where("LOWER(TRIM(s.gender))", $gender);
    }
    if (!empty($excludeIds)) {
        $builder->whereNotIn('s.student_id', $excludeIds);
    }

    // (Optional) order by class then name: join student_class → class_section → classes (session-scoped)
    $builder
        ->join('student_class sc', 'sc.student_id = s.student_id' . ($sessionId ? ' AND sc.session_id = '.$this->db->escape($sessionId) : ''), 'left')
        ->join('class_section cs','cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c',       'c.class_id   = cs.class_id',    'left')
        ->orderBy('c.class_id','ASC')
        ->orderBy('s.first_name','ASC');

    $rows = $builder->get()->getResultArray();

    $opts = '<option value="">-- Select Student --</option>';
    foreach ($rows as $r) {
        $label = trim(($r['full_name'] ?? '') . ' — ' . ($r['reg_no'] ?? ''));
        $opts .= '<option value="'.(int)$r['student_id'].'">'.esc($label).'</option>';
    }
    return $this->response->setBody($opts);
}
    /**
     * POST /admin/ajax/houses-options  (optional helper)
     * Returns: HTML <option value="house_id">House Name</option>...
     */
    public function housesOptions()
    {
        $rows = $this->db->table('sports_houses')->orderBy('house_name', 'ASC')->get()->getResultArray();
        $options = '<option value="">-- Select House --</option>';
        foreach ($rows as $r) {
            $options .= '<option value="'.(int)$r['house_id'].'">'.esc($r['house_name']).'</option>';
        }
        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->setBody($options);
    }

}
