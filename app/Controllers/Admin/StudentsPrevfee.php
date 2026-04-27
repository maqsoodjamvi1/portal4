<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class StudentsPrevfee extends BaseController
{
    protected $db;
    protected $session;
    protected $students;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-students');
        // Load model if needed
        // $this->students = new \App\Models\StudentsModel();
    }

    public function index()
    {
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $currentrole = currentUserRoles();
        $sectionsclassinfo = in_array(5, $currentrole)
            ? teacherSubjectSections()
            : userClassSections();

        $data['sectionsclassinfo'] = $sectionsclassinfo;
        $data['campus_info'] = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();

        return view('admin/students_prevfee', $data);
    }

    public function data()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $month = $this->request->getPost('month');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $currentrole = currentUserRoles();
        $sectionsclassinfo = in_array(5, $currentrole)
            ? teacherSubjectSections()
            : userClassSections();

        $classSection = $this->db->table('class_section')->where('cls_sec_id', $cls_sec_id)->get()->getRow();
        $currrentSessions = $this->db->table('academic_session')->where('session_id', $sessionid)->get()->getRow();
        $academicSessions = $this->db->table('academic_session')->where('session_id !=', $sessionid)->where('system_id', $schoolinfo->system_id)->orderBy('session_id', 'DESC')->limit(4)->get()->getResult();

        $feeinfoAmount = $this->db->query(
            'SELECT * FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1 AND s_flag=1 AND system_id=?) AND campus_id=? AND session_id=? AND class_id=?',
            [$schoolinfo->system_id, $campusid, $sessionid, $classSection->class_id]
        )->getRow();

        if ($cls_sec_id) {
            $student_class = $this->db->query(
                'SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1  AND campus_id=?) AND session_id =? AND cls_sec_id =? ORDER BY cls_sec_id ASC',
                [$campusid, $sessionid, $cls_sec_id]
            )->getResult();
        } else {
            $student_class = $this->db->query(
                'SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND campus_id=?) AND session_id =? ORDER BY cls_sec_id ASC',
                [$campusid, $sessionid]
            )->getResult();
        }

        $studentsList = '';
        $currrentSessionsName = $currrentSessions->session_name ?? '';
        $studentsList .= '<table class="table table-striped table-bordered table-hover" id="students-datatable"  style="font-size:10px;width: 100%;"><thead><tr><th style="width: 55px !important;" nowrap>#</th><th style="width: 140px !important;">Reg No</th><th style="width:150px;" nowrap>Name</th>';
        $studentsList .= '<td style="width:185px;font-size:12px;">Current Session<br>(' . $currrentSessionsName . ')</td>';
        foreach ($academicSessions as $sessionValue) {
            $studentsList .= '<td style="width:150px;">' . $sessionValue->session_name . '</td>';
        }
        $studentsList .= '</tr></thead><tbody>';

        foreach ($student_class as $studentinfo) {
            $list = $this->db->table('students')->where('campus_id', $campusid)->where('student_id', $studentinfo->student_id)->where('status', 1)->get()->getResult();
            foreach ($list as $key => $value) {
                $feeAmount = $feeinfoAmount ? ($feeinfoAmount->amount - $value->discounted_amount) : '';

                $parentinfo = $this->db->table('parents')->where('parent_id', $value->parent_id)->get()->getRow();

                $f_name = $parentinfo->f_name ?? '';
                $studentsList .= '<tr><th nowrap><input type="hidden" value="' . $value->student_id . '" id="student_id' . $value->student_id . '" name="student_id">' . $value->student_id . '</th>';
                $studentsList .= '<th style="width: 55px !important;">' . $value->reg_no . '</th>
                    <th nowrap>' . $value->first_name . ' ' . $value->last_name . '<br>c/o ' . $f_name . '</th>';
                $studentsList .= '<th>' . $feeAmount . '</th>';
                foreach ($academicSessions as $sessionValue) {
                    $sessionNameArr = explode('-', $sessionValue->session_name);
                    $feeMonth = '0' . $month . '/' . $sessionNameArr[0];

                    $feeinfo = $this->db->query(
                        'SELECT SUM(amount-discount) as total FROM fee_chalan WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE is_monthly_fee=1 AND s_flag=1 AND system_id=?) AND fee_month=? AND student_id=? ORDER BY chalan_id DESC',
                        [$schoolinfo->system_id, $feeMonth, $value->student_id]
                    )->getRow();
                    $balance = $feeinfo->total ?? 0;
                    $studentsList .= '<th nowrap>' . $balance . '</th>';
                }
                $studentsList .= '</tr>';
            }
        }
        $studentsList .= '</tbody></table>';

        return $this->response->setBody($studentsList)->setContentType('text/html');
    }

    public function selectClassFee()
    {
        $campusid = $this->session->get('member_campusid');
        $section_id = $this->request->getPost('section_id');
        $schoolinfo = getSchoolInfo();
        $session_id = $this->session->get('member_sessionid');
        $amount = 0;
        $feemonth_balance = $this->db->query(
            'SELECT amount FROM fee_amount WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id=? AND is_monthly_fee=1 AND s_flag=1) AND class_id = (SELECT class_id FROM class_section WHERE cls_sec_id=?) AND campus_id=? AND session_id=?',
            [$schoolinfo->system_id, $section_id, $campusid, $session_id]
        )->getRow();
        if ($feemonth_balance) {
            $amount = $feemonth_balance->amount;
        }
        return $this->response->setBody((string)$amount)->setContentType('text/plain');
    }

    public function saveStudent()
    {
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $now = date('Y-m-d H:i:s');

        $studentsInfo = $this->request->getPost('student_id');
        $sectionID = $this->request->getPost('section_id');
        $fee_plan = $this->request->getPost('fee_plan');
        $currentBalance = $this->request->getPost('current_balance');
        $previousBalance = $this->request->getPost('previous_balance');
        $studentRate = $this->request->getPost('discounted_amount');

        $feeTypeInfo = $this->db->table('fee_type')
            ->where('system_id', $schoolinfo->system_id)
            ->where('is_monthly_fee', 1)
            ->where('s_flag', 1)
            ->get()->getRow();

        $ClassSectioninfo = $this->db->table('class_section')
            ->where('cls_sec_id', $sectionID)
            ->get()->getRow();

        $amountInfo = $this->db->table('fee_amount')
            ->where('class_id', $ClassSectioninfo->class_id)
            ->where('session_id', $sessionid)
            ->where('fee_type_id', $feeTypeInfo->fee_type_id)
            ->where('campus_id', $campusid)
            ->get()->getRow();

        $data = [
            'discounted_amount' => trim($amountInfo->amount - $studentRate),
            'fee_plan' => trim($fee_plan),
            'updated_date' => $date,
            'user_id' => $user_id
        ];

        $this->db->table('students')->where('student_id', $studentsInfo)->update($data);

        $dataClass = [
            'cls_sec_id' => trim($ClassSectioninfo->cls_sec_id),
            'updated_date' => $date,
            'user_id' => $user_id
        ];
        $this->db->table('student_class')->where('student_id', $studentsInfo)->where('session_id', $sessionid)->update($dataClass);

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
        } elseif (!empty($prevfeeChalaninfo)) {
            $feeData = [
                'amount' => $previousBalance,
                'discount' => 0,
                'updated_date' => $date,
                'user_id' => $user_id
            ];
            $this->db->table('fee_chalan')->where('chalan_id', $prevfeeChalaninfo->chalan_id)->update($feeData);
        }

        if (empty($feeChalaninfo) && $currentBalance > 0) {
            $feeData = [
                'fee_type_id' => $feeTypeInfo->fee_type_id,
                'student_id' => $studentsInfo,
                'issue_date' => $issuedate,
                'due_date' => $duedate,
                'fee_month' => $fee_month,
                'amount' => $currentBalance,
                'discount' => 0,
                'status' => 'unpaid',
                'created_date' => $date,
                'user_id' => $user_id
            ];
            $this->db->table('fee_chalan')->insert($feeData);
        } elseif (!empty($feeChalaninfo)) {
            $feeData = [
                'amount' => $currentBalance,
                'discount' => 0,
                'updated_date' => $date,
                'user_id' => $user_id
            ];
            $this->db->table('fee_chalan')->where('chalan_id', $feeChalaninfo->chalan_id)->update($feeData);
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Edit Student Success']);
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
