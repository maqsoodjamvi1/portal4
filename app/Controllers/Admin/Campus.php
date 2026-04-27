<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Campus extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-campus');
    }

    public function index()
    {
        return view('admin/campus', []);
    }

    public function data()
    {
        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search');
        $keyword = $search['value'] ?? '';

        $schoolinfo = getSchoolInfo();

        $builder = $this->db->table('campus A');
        $builder->selectCount('A.campus_id', 'ccount');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if (!empty($keyword)) {
            $builder->where('A.campus_name', $keyword);
        }
        $total = $builder->get()->getRow()->ccount;

        $builder = $this->db->table('campus A');
        $builder->select('A.*');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if (!empty($keyword)) {
            $builder->where('A.campus_name', $keyword);
        }
        $builder->orderBy('A.campus_id', 'desc');
        $builder->limit($length, $start);
        $results = $builder->get()->getResult();

        $data = [];
        foreach ($results as $row) {
            $bill = $this->db->table('campus_bills')->where('campus_id', $row->campus_id)->get()->getRow();
            $data[] = [
                'id' => $row->campus_id,
                'bill_id' => $bill->bill_id ?? '',
                'campus_name' => $row->campus_name,
                'short_name' => $row->short_name,
                'landline' => $row->landline,
                'mobile_no' => $row->mobile_no,
                'location' => $row->location
            ];
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    public function add()
    {
        check_permission('admin-add-campus');
        $data = [
            'system_plansinfo' => $this->db->table('system_plans')->get()->getResult(),
            'system_installment_planinfo' => $this->db->table('system_installment_plan')->get()->getResult(),
            'number_of_students' => $this->db->table('number_of_students')->get()->getResult(),
            'max_student_feeinfo' => $this->db->table('max_student_fee')->get()->getResult(),
            'schoolinfo' => getSchoolInfo()
        ];
        return view('admin/campus_edit', $data);
    }

    public function edit()
    {
        check_permission('admin-edit-campus');
        $id = (int) $this->request->getGet('id');
        $campus_bill = $this->db->table('campus_bills')->where(['campus_id' => $id, 'status' => 1])->get()->getRow();

        $data = [
            'campus_bills_info' => $campus_bill,
            'system_plansinfo' => $this->db->table('system_plans')->where('plan_id', $campus_bill->plan_id)->get()->getResult(),
            'system_installment_planinfo' => $this->db->table('system_installment_plan')->where('install_id', $campus_bill->install_id)->get()->getResult(),
            'number_of_students' => $this->db->table('number_of_students')->where('id', $campus_bill->max_students)->get()->getResult(),
            'max_student_feeinfo' => $this->db->table('max_student_fee')->where('id', $campus_bill->max_fee)->get()->getResult(),
            'schoolinfo' => getSchoolInfo(),
            'info' => $this->db->table('campus')->where('campus_id', $id)->get()->getRow()
        ];

        return view('admin/campus_edit', $data);
    }

    public function save()
    {
        $request = $this->request;
        $id = (int) $request->getPost('id');
        $user_id = session()->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $plan_id = $request->getPost('plan_id');

        if (!$plan_id) {
            return $this->response->setJSON(['error' => true, 'msg' => 'Select Package']);
        }

        $system_plans_info = $this->db->table('system_plans')->where('plan_id', $plan_id)->get()->getRow();

        if ($id === 0) {
            check_permission('admin-add-campus');
            $this->db->transStart();

            $data = [
                'system_id'   => trim($request->getPost('system_id')),
                'campus_name' => trim($request->getPost('campus_name')),
                'short_name'  => trim($request->getPost('short_name')),
                'mobile_no'   => trim($request->getPost('mobile_no')),
                'location'    => trim($request->getPost('location')),
                'created_date' => $date,
                'user_id'     => $user_id,
            ];

            $this->db->table('campus')->insert($data);
            $new_campus_id = $this->db->insertID();

            $password = password_hash(trim($request->getPost('password')), PASSWORD_BCRYPT);

            $dataUsers = [
                'campus_id'   => $new_campus_id,
                'first_name' => trim($request->getPost('first_name')),
                'last_name'  => trim($request->getPost('last_name')),
                'email'      => trim($request->getPost('email')),
                'username'   => trim($request->getPost('email')),
                'password'   => $password,
                'mobile_no'  => trim($request->getPost('mobile_no')),
                'address'    => trim($request->getPost('location')),
                'created_date' => $date,
                'user_id'    => $user_id,
            ];

            $this->db->table('users')->insert($dataUsers);
            $last_user_id = $this->db->insertID();

            if ($last_user_id) {
                $this->db->table('user_roles')->insert([
                    'userID' => $last_user_id,
                    'roleID' => 3,
                ]);

                $this->db->table('campus_bills')->insert([
                    'campus_id'      => $new_campus_id,
                    'plan_id'        => 3,
                    'install_id'     => $system_plans_info->month_count,
                    'max_students'   => $system_plans_info->student_limit,
                    'max_fee'        => $system_plans_info->fee_limit,
                    'status'         => 0,
                    'campus_expiry'  => date('Y-m-d', strtotime("+{$system_plans_info->month_count} month")),
                    'bill_amount'    => $system_plans_info->price,
                    'bill_status'    => 'unpaid',
                    'bill_issue_date'=> $date,
                    'created_date'   => $date,
                    'user_id'        => $user_id,
                ]);
            }

            $this->db->transComplete();
            return $this->response->setJSON(['success' => true, 'msg' => 'Add Campus Success']);
        } else {
            check_permission('admin-edit-campus');
            $this->db->transStart();

            $updateData = [
                'campus_name'   => trim($request->getPost('campus_name')),
                'short_name'    => trim($request->getPost('short_name')),
                'mobile_no'     => trim($request->getPost('mobile_no')),
                'location'      => trim($request->getPost('location')),
                'bank_name'     => trim($request->getPost('bank_name')),
                'bank_address'  => trim($request->getPost('bank_address')),
                'bank_code'     => trim($request->getPost('bank_code')),
                'bank_acc'      => trim($request->getPost('bank_acc')),
                'chalan_h_msg'  => trim($request->getPost('chalan_h_msg')),
                'chalan_f_msg'  => trim($request->getPost('chalan_f_msg')),
                'fine_type'     => trim($request->getPost('fine_type')),
                'late_fee_fine' => trim($request->getPost('late_fee_fine')),
                'fee_issue_date'=> trim($request->getPost('fee_issue_date')),
                'fee_due_date'  => trim($request->getPost('fee_due_date')),
                'updated_date'  => $date,
                'user_id'       => $user_id,
            ];

            $this->db->table('campus')->where('campus_id', $id)->update($updateData);
            $this->db->transComplete();

            return $this->response->setJSON(['success' => true, 'msg' => 'Edit Campus Success']);
        }
    }

    public function get_packages()
    {
        $request = $this->request;
        $max_fee = $request->getPost('max_fee') ?? 0;
        $max_students = $request->getPost('max_students') ?? 0;

        $plans = $this->db->table('system_plans')->where('student_limit', $max_students)->get()->getResult();

        $html = '<table class="table">';
        foreach ($plans as $plan) {
            $billing = ($plan->month_count == 1) ? "$plan->price/Month" : "$plan->price/Annum";
            $html .= "<tr><td><input type='checkbox' name='plan_id' value='{$plan->plan_id}'></td><td>{$plan->plan_name}</td><td>Max Students: {$plan->student_limit}</td><td>Max Fee: {$plan->fee_limit}</td><td>{$billing}</td></tr>";
        }
        $html .= '</table><script>$(document).on("click", "input[type=\'checkbox\']", function() { $("input[type=\'checkbox\']").not(this).prop("checked", false); });</script>';
        echo $html;
        exit;
    }

    public function calculateCampusBill()
    {
        $request = $this->request;
        $plan = $request->getPost('plan');
        $max_fee = $request->getPost('max_fee') ?? 0;
        $max_students = $request->getPost('max_students') ?? 0;
        $installment_plan = $request->getPost('installment_plan');

        $systemPlan = $this->db->table('system_plans')->where('plan_id', $plan)->get()->getRow();
        $installPlan = $this->db->table('system_installment_plan')->where('install_id', $installment_plan)->get()->getRow();
        $studentsRow = $this->db->table('number_of_students')->where('id', $max_students)->get()->getRow();
        $maxFeeRow = $this->db->table('max_student_fee')->where('id', $max_fee)->get()->getRow();

        $monthlyBill = $systemPlan->factor * $installPlan->discount_factor * $maxFeeRow->max_fee * $studentsRow->no_of_students;
        $installmentBill = $monthlyBill * $installPlan->month_count;

        echo "$monthlyBill/Month<br>$installmentBill/{$installPlan->install_name}<br><input type='hidden' name='bill_amount' value='$installmentBill'>";
    }

    public function delete()
    {
        check_permission('admin-del-user');
        $id = (int) $this->request->getGet('id');
        $this->db->table('classes')->where('id', $id)->delete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Campus Success']);
    }
}