<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;
use DateTime;

class FeeChalanSingle extends BaseController
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
        return view('admin/add_single_chalan', []);
    }

    public function pdf($student_id = null)
    {
        $student_id = $student_id ?? (int) $this->request->getGet('student_id');
        if (!$student_id) {
            return redirect()->back()->with('error', 'Student ID is required.');
        }

        return view('admin/chalanview/fee_chalan_single_pdf', [
            'student_id' => $student_id,
        ]);
    }

    
    public function add()
    {
        check_permission('admin-add-fee-chalan');
        $student_id = $this->request->getGet('id');
        $session_id = session('member_sessionid');
        $campus_id = session('member_campusid');
        $schoolinfo = getSchoolInfo();

        $info = $this->db->table('students')->where('student_id', $student_id)->get()->getRow();
        $data['info'] = $info;

        $studentclassinfo = $this->db->table('student_class')
            ->where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->get()->getRow();

        $classesinfo = $this->db->query('SELECT * FROM classes WHERE class_id IN (SELECT class_id FROM class_section WHERE cls_sec_id=' . $studentclassinfo->cls_sec_id . ')')->getRow();

        $feetypeinfo = $this->db->table('fee_type')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        $feedata = [];

        foreach ($feetypeinfo as $value) {
            $feeamountinfo = $this->db->table('fee_amount')
                ->where('fee_type_id', $value->fee_type_id)
                ->where('campus_id', $campus_id)
                ->where('session_id', $session_id)
                ->where('class_id', $classesinfo->class_id)
                ->get()->getRow();

            $amount = $feeamountinfo ? $feeamountinfo->amount : 0;

            $feedata[] = [
                'fee_type_id' => $value->fee_type_id,
                'fee_type_name' => $value->fee_type_name,
                'is_monthly_fee' => $value->is_monthly_fee,
                'amount' => $amount,
            ];
        }

        $data['fee_type_info'] = $feedata;
        return view('admin/add_single_chalan', $data);
    }

    public function save()
    {
        $db = $this->db;
        $user_id = session('member_userid');
        $date = date('Y-m-d');

        $fee_type = $this->request->getPost('fee_type_name');
        $fee_amount = $this->request->getPost('fee_amount');

        $issuedate = DateTime::createFromFormat('d/m/Y', $this->request->getPost('issue_date'))->format('Y-m-d');
        $duedate = DateTime::createFromFormat('d/m/Y', $this->request->getPost('due_date'))->format('Y-m-d');

        $arrMonth = explode('-', $this->request->getPost('fee_month'));
        $fee_month = $arrMonth[1] . '/' . $arrMonth[0];

        $id = intval($this->request->getPost('id'));
        $session_id = session('member_sessionid');

        $db->transBegin();

        $updateData = ['status' => 1];
        $db->table('students')->where('student_id', $id)->where('session_id', $session_id)->update($updateData);
        $db->table('student_class')->where('student_id', $id)->where('session_id', $session_id)->update($updateData);

        $student_class = $db->table('student_class')->where(['status' => 1, 'student_id' => $id, 'session_id' => $session_id])->get()->getResult();

        check_permission('admin-add-fee-chalan');

        foreach ($student_class as $students) {
            $studentinfo = $db->table('students')->where('student_id', $students->student_id)->get()->getRow();
            $discounted_amount = $studentinfo ? 0 : 0;

            foreach ($fee_type as $fee_type_id) {
                $isDiscount = $db->table('fee_type')->where('fee_type_id', $fee_type_id)->where('is_monthly_fee', 1)->get()->getRow();
                $discount = $isDiscount ? $discounted_amount : 0;
                $fee_type_amount = $fee_amount[$fee_type_id];

                $feeChalaninfo = $db->table('fee_chalan')
                    ->where('fee_type_id', $fee_type_id)
                    ->where('student_id', $students->student_id)
                    ->where('fee_month', $fee_month)
                    ->get()->getRow();

                if (empty($feeChalaninfo) && $fee_type_amount > 0) {
                    $data = [
                        'fee_type_id' => $fee_type_id,
                        'student_id' => $students->student_id,
                        'issue_date' => $issuedate,
                        'due_date' => $duedate,
                        'fee_month' => $fee_month,
                        'amount' => $fee_type_amount,
                        'discount' => $discount,
                        'status' => 'unpaid',
                        'created_date' => $date,
                        'user_id' => $user_id
                    ];
                    $db->table('fee_chalan')->insert($data);
                }
            }
        }

        $db->transComplete();
        return json_response(['success' => true, 'msg' => 'Add Chalan Success']);
    }

    public function download()
    {
        $data = $this->data();
        return view('admin/chalanview/fee_chalan_single_pdf', ['data' => $data]);
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $campus_id = session('member_campusid');
        $student_id = $this->request->getGet('id');
        $schoolinfo = getSchoolInfo();
        $result = $this->db->query("SELECT t1.cls_sec_id, t2.student_id, t2.parent_id, t2.campus_id, t2.reg_no, t2.first_name, t2.last_name, t2.parent_id 
            FROM student_class t1, students t2 
            WHERE t1.status = 1 AND t2.student_id = $student_id AND t1.student_id = $student_id")->getResult();

        $response->recordsTotal = count((array) $result);
        $student_data = [];

        foreach ($result as $row) {
            // $this->db->table('fee_chalan')->where('student_id', $row->student_id)->where('status', 'unpaid')->orderBy('fee_month', 'asc');
            // $chalan_info = $this->db->get()->getRow();
            $chalan_info = $this->db->table('fee_chalan')
		    ->where('student_id', $row->student_id)
		    ->where('status', 'unpaid')
		    ->orderBy('fee_month', 'asc')
		    ->get()
		    ->getRow();

            $unpaid_total = $this->db->query("SELECT SUM(fee.amount) - SUM(fee.discount) AS total 
                FROM fee_chalan fee WHERE student_id = $row->student_id AND status = 'unpaid'")->getRow();

            if ($unpaid_total->total) {
                $classSectioninfo = getClassSection($row->cls_sec_id);
                $campusinfo = $this->db->table('campus')->where('campus_id', $row->campus_id)->get()->getRow();

                $fee_chalan = $this->db->table('fee_chalan')->where('student_id', $row->student_id)->where('status', 'unpaid')->orderBy('fee_month', 'asc')->get()->getResult();
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
                        'fee_name' => $fee_type_info->fee_type_name,
                        'is_monthly_fee' => $fee_type_info->is_monthly_fee,
                    ];
                }

                $parentinfo = $this->db->table('parents')->where('parent_id', $row->parent_id)->get()->getRow();

                $student_data[] = [
                    'campus_name' => $campusinfo->campus_name ?? '',
                    'system_name' => $schoolinfo->system_name,
                    'chalan_no' => $FChalanNum->chalan_id ?? '',
                    'logo' => $schoolinfo->logo,
                    'location' => $campusinfo->location ?? '',
                    'bank_name' => $campusinfo->bank_name ?? '',
                    'bank_address' => $campusinfo->bank_address ?? '',
                    'bank_code' => $campusinfo->bank_code ?? '',
                    'bank_acc' => $campusinfo->bank_acc ?? '',
                    'chalan_h_msg' => $campusinfo->chalan_h_msg ?? '',
                    'chalan_f_msg' => $campusinfo->chalan_f_msg ?? '',
                    'student_id' => $row->student_id,
                    'reg_no' => $row->reg_no,
                    'student_name' => $row->first_name . ' ' . $row->last_name,
                    'family_no' => $parentinfo->parent_id,
                    'f_name' => $parentinfo->f_name,
                    'class_name' => $classSectioninfo['sectionclassname'],
                    'fee_month' => $chalan_info->fee_month,
                    'issue_date' => $chalan_info->issue_date,
                    'due_date' => $chalan_info->due_date,
                    'student_fee' => $student_fee,
                    'fee_fine' => [],
                ];
            }
        }

        return $student_data;
    }
}
