<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Campus extends BaseController
{
    protected $db;
    protected $session;

    /** Default SaaS package for new campuses */
    private const DEFAULT_CAMPUS_PLAN_ID = 3;

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
        $yearlyInstall = $this->getYearlyInstallPlan();
        $data = [
            'system_plansinfo' => $this->db->table('system_plans')->get()->getResult(),
            'system_installment_planinfo' => $this->db->table('system_installment_plan')->get()->getResult(),
            'number_of_students' => $this->db->table('number_of_students')->get()->getResult(),
            'max_student_feeinfo' => $this->db->table('max_student_fee')->get()->getResult(),
            'schoolinfo' => getSchoolInfo(),
            'default_plan' => $this->db->table('system_plans')->where('plan_id', self::DEFAULT_CAMPUS_PLAN_ID)->get()->getRow(),
            'yearly_install' => $yearlyInstall,
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
            'system_plansinfo' => $campus_bill ? $this->db->table('system_plans')->where('plan_id', $campus_bill->plan_id)->get()->getResult() : [],
            'system_installment_planinfo' => $campus_bill ? $this->db->table('system_installment_plan')->where('install_id', $campus_bill->install_id)->get()->getResult() : [],
            'number_of_students' => $campus_bill ? $this->db->table('number_of_students')->where('id', $campus_bill->max_students)->get()->getResult() : [],
            'max_student_feeinfo' => $campus_bill ? $this->db->table('max_student_fee')->where('id', $campus_bill->max_fee)->get()->getResult() : [],
            'schoolinfo' => getSchoolInfo(),
            'info' => $this->db->table('campus')->where('campus_id', $id)->get()->getRow(),
            'default_plan' => null,
            'yearly_install' => null,
        ];

        return view('admin/campus_edit', $data);
    }

    public function save()
    {
        $request = $this->request;
        $id = (int) $request->getPost('id');
        $user_id = session()->get('member_userid');
        $date = date('Y-m-d H:i:s');

        if ($id === 0) {
            check_permission('admin-add-campus');

            $validation = \Config\Services::validation();
            $validation->setRules([
                'campus_name' => 'required|min_length[2]|max_length[255]',
                'first_name'  => 'required|min_length[1]|max_length[100]',
                'last_name'   => 'required|min_length[1]|max_length[100]',
                'username'    => 'required|min_length[3]|max_length[100]|regex_match[/^[A-Za-z0-9_.-]+$/]',
                'email'       => 'required|valid_email|max_length[255]',
                'password'    => 'required|min_length[6]|max_length[255]',
                'repassword'  => 'required|matches[password]',
            ]);

            if (! $validation->withRequest($request)->run()) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => implode(' ', array_values($validation->getErrors())),
                ]);
            }

            $username = trim((string) $request->getPost('username'));
            $email = trim($request->getPost('email'));
            $existingByEmail = $this->db->table('users')
                ->select('id')
                ->groupStart()
                    ->where('email', $email)
                    ->orWhere('username', $email)
                ->groupEnd()
                ->limit(1)
                ->get()
                ->getRow();
            if ($existingByEmail) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => 'Email is already used by another user.',
                ]);
            }
            $existingByUsername = $this->db->table('users')
                ->select('id')
                ->groupStart()
                    ->where('username', $username)
                    ->orWhere('email', $username)
                ->groupEnd()
                ->limit(1)
                ->get()
                ->getRow();
            if ($existingByUsername) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => 'Username is not available. Please choose another one.',
                ]);
            }

            $planId = self::DEFAULT_CAMPUS_PLAN_ID;
            $system_plans_info = $this->db->table('system_plans')->where('plan_id', $planId)->get()->getRow();
            if (! $system_plans_info) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => 'Default plan (ID ' . $planId . ') is not configured in system_plans.',
                ]);
            }

            // Yearly cycle: prefer installment row with month_count 12; bill.install_id stores months (see Pay_campus_bill renewal).
            $yearlyInstall = $this->getYearlyInstallPlan();
            $subscriptionMonths = 12;
            $installmentPlanId = null;
            if ($yearlyInstall) {
                $installmentPlanId = (int) ($yearlyInstall->install_id ?? 0);
                if ((int) ($yearlyInstall->month_count ?? 0) > 0) {
                    $subscriptionMonths = (int) $yearlyInstall->month_count;
                }
            }
            if (! $installmentPlanId) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => 'Yearly installment plan is not configured correctly.',
                ]);
            }

            $campusExpiry = date('Y-m-d', strtotime('+' . $subscriptionMonths . ' months'));

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
                'email'      => $email,
                'username'   => $username,
                'password'   => $password,
                'mobile_no'  => trim($request->getPost('mobile_no')),
                'address'    => trim($request->getPost('location')),
                'created_date' => $date,
                'user_id'    => $user_id,
                'status'     => 1,
            ];

            $this->db->table('users')->insert($dataUsers);
            $last_user_id = $this->db->insertID();

            if (! $last_user_id) {
                $this->db->transRollback();

                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => 'Could not create campus director user.',
                ]);
            }

            helper('role');
            $directorRoleId = resolveRoleIdForPlan('Director System', $planId);
            if ($directorRoleId <= 0) {
                $directorRoleId = $this->resolveCampusDirectorRoleId($planId);
            }

            $this->db->table('user_roles')->insert([
                'userID'  => $last_user_id,
                'roleID'  => $directorRoleId,
                'addDate' => $date,
            ]);

            // status=1 required by MemberCurrentUser login.
            $this->db->table('campus_bills')->insert([
                'campus_id'       => $new_campus_id,
                'plan_id'         => $planId,
                'install_id'      => $installmentPlanId,
                'max_students'    => $system_plans_info->student_limit,
                'max_fee'         => $system_plans_info->fee_limit,
                'status'          => 1,
                'campus_expiry'   => $campusExpiry,
                'bill_amount'     => $system_plans_info->price,
                'bill_status'     => 'unpaid',
                'bill_issue_date' => $date,
                'created_date'    => $date,
                'user_id'         => $user_id,
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $dbError = $this->db->error();
                $dbErrorMessage = trim((string) ($dbError['message'] ?? ''));
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => $dbErrorMessage !== ''
                        ? ('Database error while creating campus: ' . $dbErrorMessage)
                        : 'Database error while creating campus. Please try again.',
                ]);
            }

            return $this->response->setJSON(['success' => true, 'msg' => 'Campus and campus director account created successfully.']);
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

    public function checkUsername()
    {
        check_permission('admin-add-campus');
        $username = trim((string) $this->request->getPost('username'));

        if ($username === '') {
            return $this->response->setJSON([
                'success' => false,
                'available' => false,
                'msg' => 'Username is required.',
            ]);
        }

        if (! preg_match('/^[A-Za-z0-9_.-]{3,100}$/', $username)) {
            return $this->response->setJSON([
                'success' => false,
                'available' => false,
                'msg' => 'Use 3-100 characters: letters, numbers, dot, underscore or hyphen.',
            ]);
        }

        $exists = $this->db->table('users')
            ->select('id')
            ->groupStart()
                ->where('username', $username)
                ->orWhere('email', $username)
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRow();

        return $this->response->setJSON([
            'success' => true,
            'available' => $exists ? false : true,
            'msg' => $exists ? 'Username is already taken.' : 'Username is available.',
        ]);
    }

    public function get_packages()
    {
        helper('role');

        $maxStudents = (int) ($this->request->getPost('max_students') ?? 0);
        $planId      = getSystemPlanId();

        $builder = $this->db->table('system_plans')->where('plan_id', $planId);
        if ($maxStudents > 0) {
            $builder->where('student_limit', $maxStudents);
        }

        $plan = $builder->get()->getRow();
        if (! $plan) {
            $plan = $this->db->table('system_plans')->where('plan_id', $planId)->get()->getRow();
        }

        if (! $plan) {
            echo '<p class="text-muted">Annual package is not configured.</p>';
            exit;
        }

        $billing = esc($plan->price) . '/Annum';
        $html    = '<table class="table">';
        $html   .= '<tr><td><input type="checkbox" name="plan_id" value="' . (int) $plan->plan_id . '" checked></td>';
        $html   .= '<td>' . esc($plan->plan_name) . ' (Annual)</td>';
        $html   .= '<td>Max Students: ' . (int) $plan->student_limit . '</td>';
        $html   .= '<td>Max Fee: ' . esc($plan->fee_limit) . '</td>';
        $html   .= '<td>' . $billing . '</td></tr>';
        $html   .= '</table>';
        echo $html;
        exit;
    }

    public function calculateCampusBill()
    {
        helper('role');

        $maxFee      = (int) ($this->request->getPost('max_fee') ?? 0);
        $maxStudents = (int) ($this->request->getPost('max_students') ?? 0);

        $systemPlan  = $this->db->table('system_plans')->where('plan_id', getSystemPlanId())->get()->getRow();
        $installPlan = getAnnualInstallPlan();
        $studentsRow = $this->db->table('number_of_students')->where('id', $maxStudents)->get()->getRow();
        $maxFeeRow   = $this->db->table('max_student_fee')->where('id', $maxFee)->get()->getRow();

        if (! $systemPlan || ! $installPlan || ! $studentsRow || ! $maxFeeRow) {
            echo '<span class="text-danger">Unable to calculate bill. Check fee and student limits.</span>';
            return;
        }

        $monthlyBill     = $systemPlan->factor * $installPlan->discount_factor * $maxFeeRow->max_fee * $studentsRow->no_of_students;
        $installmentBill = $monthlyBill * $installPlan->month_count;

        echo esc($installmentBill) . '/' . esc($installPlan->install_name)
            . ' (Annual)<br><input type="hidden" name="bill_amount" value="' . esc($installmentBill) . '">'
            . '<input type="hidden" name="plan_id" value="' . (int) $systemPlan->plan_id . '">'
            . '<input type="hidden" name="installment_plan" value="' . (int) $installPlan->install_id . '">';
    }

    public function delete()
    {
        check_permission('admin-del-user');
        $id = (int) $this->request->getGet('id');
        $this->db->table('classes')->where('id', $id)->delete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Campus Success']);
    }

    /**
     * Installment row used for yearly billing (12 months preferred).
     */
    private function getYearlyInstallPlan(): ?object
    {
        helper('role');

        return getAnnualInstallPlan();
    }

    /**
     * Fallback: plan-scoped roles.id for Director Campus when Director System is unavailable.
     */
    private function resolveCampusDirectorRoleId(int $planId): int
    {
        helper('role');

        foreach (['Director Campus', 'Campus Director', 'Campus director'] as $label) {
            $id = resolveRoleIdForPlan($label, $planId);
            if ($id > 0) {
                return $id;
            }
        }

        $fallback = $this->db->table('roles r')
            ->select('r.id')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
            ->groupStart()
                ->like('rn.rolename', 'Campus')
                ->like('rn.rolename', 'Director')
            ->groupEnd()
            ->where('r.plan_id', $planId)
            ->orderBy('r.id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        return (int) ($fallback->id ?? 0);
    }
}