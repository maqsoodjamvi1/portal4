<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Admin\FamilyFeeHistoryModel;

class FamilyChalanWhatsapp extends BaseController
{
    protected $studentsModel;
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        check_permission('admin-family-fee-history');
        $this->db = \Config\Database::connect();
        $this->studentsModel = new FamilyFeeHistoryModel();
    }

    public function index()
    {
        $currentrole = currentUserRoles();
        $sectionsclassinfo = in_array(5, $currentrole)
            ? teacherSubjectSections()
            : userClassSections();

        return view('admin/family_chalan_whatsapp', [
            'sectionsclassinfo' => $sectionsclassinfo
        ]); 
    }

    public function data()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $schoolInfo = getSchoolInfo();

        $list = $this->studentsModel->get_datatables();
        $response = [];

        foreach ($list['query_result'] as $row) {
            $data = [];

            $studentInfo = $this->db->table('students')
                ->where('status', 1)
                ->where('parent_id', $row->parent_id)
                ->orderBy('date_of_birth', 'asc')
                ->get()->getFirstRow();

            if (!$studentInfo) continue;

            $address = $row->address_line1;
            $fName = $row->f_name;
            $fatherContact = $row->father_contact;
            $motherContact = $row->mother_contact;
            $whatsappContact = $row->whatsapp;

            $unpaid = $this->db->query(
                "SELECT SUM(amount)-SUM(discount) as total FROM fee_chalan WHERE status = 'unpaid' AND student_id IN (SELECT student_id FROM students WHERE status = 1 AND parent_id = {$row->parent_id})"
            )->getRow();

            $paidOfMonth = $this->db->query(
                "SELECT SUM(amount - discount) AS total FROM fee_chalan WHERE student_id IN (SELECT student_id FROM students WHERE parent_id = {$row->parent_id}) AND status = 'paid' AND MONTHNAME(paid_date) = MONTHNAME(NOW()) AND YEAR(paid_date) = YEAR(NOW())"
            )->getRow();

            $studentClassInfo = $this->db->query(
                "SELECT * FROM student_class WHERE status = 1 AND session_id = {$sessionId} AND student_id IN (SELECT student_id FROM students WHERE parent_id = {$row->parent_id})"
            )->getRow();

            $className = '';
            $sectionName = '';

            if ($studentClassInfo) {
                $csInfo = $this->db->table('class_section')->where('cls_sec_id', $studentClassInfo->cls_sec_id)->get()->getRow();
                if ($csInfo) {
                    $classInfo = $this->db->table('classes')->where('class_id', $csInfo->class_id)->get()->getRow();
                    $sectionInfo = $this->db->table('sections')->where('section_id', $csInfo->section_id)->get()->getRow();
                    $className = $classInfo->class_name ?? '';
                    $sectionName = $sectionInfo->section_name ?? '';
                }
            }

            $url = rawurlencode("https://{$schoolInfo->domain}.timesoftsol.com/fee_chalan_sibling/?parent_id={$row->parent_id}");

            $payable = $unpaid->total ?? 0;
            $paidInMonth = $paidOfMonth->total ?? 0;
            $projectedFee = 0;

            $monthlyFee = $this->db->query(
                "SELECT SUM(amount) - SUM(discount) AS total FROM fee_chalan WHERE fee_type_id = (SELECT fee_type_id FROM fee_type WHERE system_id = {$schoolInfo->system_id} AND is_monthly_fee = 1 AND s_flag = 1) AND student_id IN (SELECT student_id FROM students WHERE parent_id = {$row->parent_id}) AND fee_month = '" . date('m/Y') . "'"
            )->getRow();

            if ($monthlyFee) {
                $projectedFee = $monthlyFee->total;
            }

            $response[] = [
                'id' => $row->parent_id,
                'reg_no' => $studentInfo->reg_no,
                'name' => $studentInfo->first_name . ' ' . $studentInfo->last_name,
                'f_name' => $fName,
                'gender' => $studentInfo->gender,
                'address' => $address,
                'class' => "$className($sectionName)",
                'section' => $sectionName,
                'f_contacts' => "F Contact: <a href='https://wa.me/{$fatherContact}?text={$url}'>{$fatherContact}</a><br>M Contact: <a href='https://wa.me/{$motherContact}?text={$url}'>{$motherContact}</a><br>W Contact: <a href='https://wa.me/{$whatsappContact}?text={$url}'>{$whatsappContact}</a>",
                'payable' => "<div style='float:right;'>{$payable}/-</div>",
                'projectedfee' => "<div style='float:right;'>{$projectedFee}/-</div>",
                'paid_in_month' => "<div style='float:right;'>{$paidInMonth}/-</div>",
                'previous_balance' => $payable + $paidInMonth
            ];
        }

        return $this->response->setJSON([
            'draw' => (int) $this->request->getPost('draw'),
            'recordsTotal' => $this->studentsModel->count_all(),
            'recordsFiltered' => $this->studentsModel->count_filtered(),
            'data' => $response,
        ]);
    }

    public function get_parentinfo()
    {
        $term = $this->request->getPost('term.term');
        $campusId = session('member_campusid');

        $builder = $this->db->table('parents')->where('campus_id', (int) $campusId);
        if ($term !== '') {
            $builder->like('f_name', $term);
        }
        $parents = $builder->get()->getResult();
        $data = [];

        foreach ($parents as $parent) {
            $studentExists = $this->db->table('students')->where('parent_id', $parent->parent_id)->countAllResults();
            if ($studentExists) {
                $data[] = ['id' => $parent->parent_id, 'text' => $parent->f_name];
            }
        }

        return $this->response->setJSON($data);
    }

    public function get_studentinfo()
    {
        $term = $this->request->getPost('term.term');
        $status = $this->request->getPost('status');
        $campusId = session('member_campusid');

        $builder = $this->db->table('students')
            ->where('status', (int) $status)
            ->where('campus_id', (int) $campusId);
        if ($term !== '') {
            $builder->groupStart()
                ->like('first_name', $term)
                ->orLike('last_name', $term)
                ->groupEnd();
        }
        $students = $builder->get()->getResult();
        $data = [];

        foreach ($students as $student) {
            $parent = $this->db->table('parents')->where('parent_id', $student->parent_id)->get()->getRow();
            $studentText = $student->first_name . ' ' . $student->last_name . ' c/o ' . ($parent->f_name ?? '');

            $data[] = ['id' => $student->student_id, 'text' => $studentText];
        }

        return $this->response->setJSON($data); 
    }
}
