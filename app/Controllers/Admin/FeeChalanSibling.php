<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class FeeChalanSibling extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        check_permission('admin-fee-chalan');
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data = $this->data();
        $viewData['data'] = $data;
        return view('admin/chalanview/fee_chalan_sibling_pdf', $viewData);
    }

    public function data()
    {
        $student_data = [];
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $campus_id = session('member_campusid');
        $parent_id = $this->request->getGet('parent_id');
        $schoolinfo = getSchoolInfo();
        $keyword = '';

        $stdresult = $this->db->table('students')
            ->select('student_id')
            ->where(['campus_id' => (int) $campus_id, 'parent_id' => (int) $parent_id])
            ->get()
            ->getResult();

        if (!empty($stdresult)) {
            foreach ($stdresult as $studentinfo) {
                $query = "
                    SELECT t1.cls_sec_id, t2.student_id, t2.parent_id, t2.campus_id, t2.reg_no, 
                           t2.first_name, t2.last_name 
                    FROM student_class t1, students t2 
                    WHERE t1.status = 1 
                    AND t2.student_id = {$studentinfo->student_id} 
                    AND t1.student_id = {$studentinfo->student_id}
                ";
                $result = $this->db->query($query)->getResult();

                $response->recordsTotal = count((array)$result);
                $response->student_data = [];

                foreach ($result as $row) {
                    $builder = $this->db->table('fee_chalan');
                    $builder->where('student_id', $row->student_id);
                    $builder->where('status', 'unpaid');
                    $builder->orderBy('fee_month', 'asc');
                    $chalan_info = $builder->get()->getRow();

                    $unpaid_total = $this->db->query(
                        "SELECT SUM(amount) - SUM(discount) AS total FROM fee_chalan WHERE student_id = ? AND status = 'unpaid'",
                        [(int) $row->student_id]
                    )->getRow();

                    if ($unpaid_total && $unpaid_total->total) {
                        $classSectioninfo = getClassSection($row->cls_sec_id);

                        $campusinfo = $this->db->table('campus')->where('campus_id', $row->campus_id)->get()->getRow();

                        $campus_name = $campusinfo->campus_name ?? '';
                        $location = $campusinfo->location ?? '';
                        $bank_name = $campusinfo->bank_name ?? '';
                        $bank_address = $campusinfo->bank_address ?? '';
                        $bank_code = $campusinfo->bank_code ?? '';
                        $bank_acc = $campusinfo->bank_acc ?? '';
                        $chalan_h_msg = $campusinfo->chalan_h_msg ?? '';
                        $chalan_f_msg = $campusinfo->chalan_f_msg ?? '';

                        $fee_chalan = $this->db->table('fee_chalan')
                            ->where('student_id', $row->student_id)
                            ->where('status', 'unpaid')
                            ->orderBy('fee_month', 'asc')
                            ->get()->getResult();

                        $FChalanNum = $this->db->table('fee_chalan')
                            ->select('chalan_id')
                            ->where(['student_id' => (int) $row->student_id, 'status' => 'unpaid'])
                            ->orderBy('chalan_id', 'DESC')
                            ->get()
                            ->getRow();

                        $student_fee = [];
                        foreach ($fee_chalan as $chalanvalue) {
                            $fee_type_info = $this->db->table('fee_type')->where('fee_type_id', $chalanvalue->fee_type_id)->get()->getRow();

                            $student_fee[] = [
                                'id' => $chalanvalue->chalan_id,
                                'amount' => $chalanvalue->amount,
                                'status' => $chalanvalue->status,
                                'discount' => $chalanvalue->discount,
                                'paiddate' => $chalanvalue->paid_date,
                                'fee_month' => $chalanvalue->fee_month,
                                'fee_name' => $fee_type_info->fee_type_name ?? '',
                                'is_monthly_fee' => $fee_type_info->is_monthly_fee ?? ''
                            ];
                        }

                        $parentinfo = $this->db->table('parents')->where('parent_id', $row->parent_id)->get()->getRow();

                        $student_data[] = [
                            'campus_name' => $campus_name,
                            'system_name' => $schoolinfo->system_name,
                            'chalan_no' => $FChalanNum->chalan_id ?? '',
                            'logo' => $schoolinfo->logo,
                            'location' => $location,
                            'bank_name' => $bank_name,
                            'bank_address' => $bank_address,
                            'bank_code' => $bank_code,
                            'bank_acc' => $bank_acc,
                            'chalan_h_msg' => $chalan_h_msg,
                            'chalan_f_msg' => $chalan_f_msg,
                            'student_id' => $row->student_id,
                            'reg_no' => $row->reg_no,
                            'student_name' => $row->first_name . ' ' . $row->last_name,
                            'family_no' => $parentinfo->parent_id ?? '',
                            'f_name' => $parentinfo->f_name ?? '',
                            'class_name' => $classSectioninfo['sectionclassname'] ?? '',
                            'fee_month' => $chalan_info->fee_month ?? '',
                            'issue_date' => $chalan_info->issue_date ?? '',
                            'due_date' => $chalan_info->due_date ?? '',
                            'student_fee' => $student_fee,
                            'fee_fine' => []
                        ];
                    }
                }
            }
        }

        return $student_data;
    }
}
