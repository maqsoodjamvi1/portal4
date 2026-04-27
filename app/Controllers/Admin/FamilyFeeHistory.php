<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\FamilyFeeHistoryModel;

class FamilyFeeHistory extends BaseController
{
    protected $db;
    protected $session;
    protected $familyFeeHistoryModel;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        $this->familyFeeHistoryModel = new FamilyFeeHistoryModel();
        check_permission('admin-family-fee-history');
    }

    public function index()
    {
        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

        return view('admin/family_fee_history', $this->template_data);
    }

    public function data()
    {
        $campusid   = $this->session->get('member_campusid');
        $sessionid  = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();
        $projectedfee = 0;

        // Your DataTables logic here (replace with model method)
        $list = $this->familyFeeHistoryModel->get_datatables();

        $data = [];
        $response = [];
        $no = $this->request->getPost('start');

        foreach ($list['query_result'] as $row) {
            $no++;

            if (!empty($row)) {
                $total_discount = 0;
                $classinfo = '';
                $sectioninfo = '';
                $className = '';
                $sectionName = '';

                $unpaid = $this->db->query(
                    'SELECT SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE status = "unpaid" and student_id IN(SELECT student_id FROM students WHERE status=1 AND parent_id=?)',
                    [$row->parent_id]
                )->getRow();

                $discount = $this->db->query(
                    'SELECT SUM(discount) as total_discount FROM fee_chalan WHERE status = "unpaid" and student_id IN(SELECT student_id FROM students WHERE status=1 AND parent_id=?)',
                    [$row->parent_id]
                )->getRow();

                if ($discount) {
                    $total_discount = $discount->total_discount;
                }

                $studentclassinfo = $this->db->query(
                    'SELECT * FROM student_class WHERE status=1 AND session_id=? AND student_id IN(SELECT student_id FROM students WHERE parent_id=?)',
                    [$sessionid, $row->parent_id]
                )->getRow();

                if ($studentclassinfo) {
                    $classsectioninfo = $this->db->table('class_section')
                        ->where('cls_sec_id', $studentclassinfo->cls_sec_id)
                        ->get()->getRow();

                    $classinfo = $this->db->table('classes')
                        ->where('class_id', $classsectioninfo->class_id)
                        ->get()->getRow();

                    $sectionInfo = $this->db->table('sections')
                        ->where('section_id', $classsectioninfo->section_id)
                        ->get()->getRow();
                    if ($sectionInfo) {
                        $sectionName = $sectionInfo->section_name;
                    }

                    $getclassfee = $this->db->query(
                        'SELECT (SUM(amount)-SUM(discount)) AS total FROM fee_chalan WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id = ? AND is_monthly_fee = 1 AND s_flag=1) AND student_id IN (SELECT student_id FROM students WHERE parent_id=?) AND fee_month = ?',
                        [$schoolinfo->system_id, $row->parent_id, date('m/Y')]
                    )->getRow();

                    $paidOfMonth = $this->db->query(
                        "SELECT (SUM(amount)-SUM(discount)) AS total FROM fee_chalan WHERE student_id IN (SELECT student_id FROM students WHERE parent_id=?) AND STATUS='paid' AND (MONTHNAME(paid_date) = MONTHNAME(NOW())) AND (YEAR(paid_date) = YEAR(NOW()))",
                        [$row->parent_id]
                    )->getRow();

                    if ($getclassfee) {
                        $projectedfee = ($getclassfee->total);
                    }
                }

                if ($classinfo) {
                    $className = $classinfo->class_name;
                }

                $studentInfo = $this->db->query(
                    'SELECT * FROM students WHERE status=1 AND parent_id=? AND date_of_birth = (SELECT MIN(date_of_birth) FROM students WHERE parent_id=?)',
                    [$row->parent_id, $row->parent_id]
                )->getRow();

                if ($studentInfo) {
                    $address = $row->address_line1 ?? '';
                    $f_name = $row->f_name;
                    $father_contact = $row->father_contact;
                    $mother_contact = $row->mother_contact;
                    $emergency_contact = $row->emergency_contact;

                    $payable = (!empty($unpaid) && !empty($unpaid->total)) ? $unpaid->total : 0;
                    $paid_of_month = (!empty($paidOfMonth) && !empty($paidOfMonth->total)) ? $paidOfMonth->total : 0;

                    $prevBalance = ($payable + $paid_of_month);

                    $data['id'] = $row->parent_id;
                    $data['reg_no'] = $studentInfo->reg_no;
                    $data['name'] = $studentInfo->first_name . " " . $studentInfo->last_name;
                    $data['f_name'] = $f_name;
                    $data['gender'] = $studentInfo->gender;
                    $data['address'] = $address;
                    $data['class'] = $className . "(" . $sectionName . ")";
                    $data['section'] = $sectionName;
                    $data['f_contacts'] = $father_contact;
                    $data['m_contacts'] = $mother_contact;
                    $data['payable'] = "<div style='float:right;'>" . $payable . "/-</div>";
                    $data['projectedfee'] = "<div style='float:right;'>" . $projectedfee . "/-</div>";
                    $data['paid_in_month'] = "<div style='float:right;'>" . $paid_of_month . "/-</div>";
                    $data['previous_balance'] = $prevBalance;
                    $response[] = $data;
                }
            }
        }

        $output = [
            "draw" => intval($this->request->getPost('draw')),
            "recordsTotal" => $this->familyFeeHistoryModel->count_all(),
            "recordsFiltered" => $this->familyFeeHistoryModel->count_filtered(),
            "data" => $response,
        ];

        return $this->response->setJSON($output);
    }

    public function get_parentinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $parentssinfo = $this->db->query(
            "SELECT * FROM parents WHERE (f_name LIKE ?) ",
            ['%' . $term . '%']
        )->getResultArray();

        $data = [];
        foreach ($parentssinfo as $parent) {
            $classstudents = $this->db->table('students')
                ->where('parent_id', $parent['parent_id'])
                ->get()->getRow();
            if ($classstudents) {
                $data[] = ["id" => $parent['parent_id'], "text" => $parent['f_name']];
            }
        }

        return $this->response->setJSON($data);
    }

    public function get_studentinfo()
    {
        $campusid = $this->session->get('member_campusid');
        $term = $this->request->getPost('term')['term'] ?? '';
        $status = $this->request->getPost('status');

        $studentsinfo = $this->db->query(
            "SELECT * FROM students WHERE (first_name LIKE ? OR last_name LIKE ?) AND status=? AND campus_id=?",
            ['%' . $term . '%', '%' . $term . '%', $status, $campusid]
        )->getResultArray();

        $data = [];
        foreach ($studentsinfo as $student) {
            $classstudents = $this->db->table('student_class')
                ->where('student_id', $student['student_id'])
                ->get()->getRow();
            $parentsInfo = $this->db->table('parents')
                ->select('f_name')
                ->where('parent_id', $student['parent_id'])
                ->get()->getRow();

            $stdInfotxt = $student['first_name'] . " " . $student['last_name'] . " c/o " . ($parentsInfo->f_name ?? '');

            if ($classstudents) {
                $data[] = ["id" => $student['student_id'], "text" => $stdInfotxt];
            }
        }
        return $this->response->setJSON($data);
    }
}
// end this file
