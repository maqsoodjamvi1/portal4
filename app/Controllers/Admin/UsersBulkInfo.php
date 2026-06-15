<?php

namespace App\Controllers\Admin;

/**
 * Bulk edit / quick-add employees for the current campus (same permission as employee list).
 * Full single-employee form remains the place for roles, subjects, class-teacher, and password rules.
 */
class UsersBulkInfo extends Users
{
    public function __construct()
    {
        parent::__construct();
        check_permission('admin-users');
    }

    public function index()
    {
        $campusId = (int) $this->session->get('member_campusid');

        return view('admin/users_bulk_info', [
            'assignableRoles' => $this->getAssignableRoles($campusId),
            'campus_id'       => $campusId,
        ]);
    }

    /**
     * JSON: quick search for typeahead (name, username, email).
     */
    public function searchEmployees()
    {
        $q        = trim((string) $this->request->getGet('q'));
        $limit    = max(1, min(40, (int) ($this->request->getGet('limit') ?: 20)));
        $campusId = (int) $this->session->get('member_campusid');

        if ($q === '' || strlen($q) < 2) {
            return $this->response->setJSON(['results' => []]);
        }

        $rows = $this->db->table('users u')
            ->select('u.id, u.first_name, u.last_name, u.username, u.email, u.status')
            ->where('u.campus_id', $campusId)
            ->groupStart()
                ->like('u.first_name', $q)
                ->orLike('u.last_name', $q)
                ->orLike('u.username', $q)
                ->orLike('u.email', $q)
            ->groupEnd()
            ->orderBy('u.first_name', 'ASC')
            ->orderBy('u.last_name', 'ASC')
            ->limit($limit)
            ->get()
            ->getResult();

        $results = [];
        foreach ($rows as $r) {
            $name = trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? ''));
            if ($name === '') {
                $name = (string) ($r->username ?? 'Employee');
            }
            $suffix = ((int) ($r->status ?? 0) === 1) ? '' : ' (dropped)';
            $results[] = [
                'id'   => (int) $r->id,
                'text' => $name . ' — ' . ($r->username ?? '') . $suffix,
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    /**
     * POST: return HTML table rows for employees matching filters.
     */
    public function loadRows()
    {
        $campusId = (int) $this->session->get('member_campusid');
        $status   = trim((string) $this->request->getPost('status'));
        if ($status === '') {
            $status = '1';
        }
        $q         = trim((string) $this->request->getPost('q'));
        $singleId  = (int) $this->request->getPost('employee_id');
        $maxRows   = 150;

        $builder = $this->db->table('users u')
            ->select('u.*', false)
            ->where('u.campus_id', $campusId);

        if ($singleId > 0) {
            $builder->where('u.id', $singleId);
        } else {
            if ($status === 'all') {
                // no status filter
            } elseif ($status === '0') {
                $builder->where('u.status', 0);
            } else {
                $builder->where('u.status', 1);
            }
            if ($q !== '') {
                $builder->groupStart()
                    ->like('u.first_name', $q)
                    ->orLike('u.last_name', $q)
                    ->orLike('u.username', $q)
                    ->orLike('u.email', $q)
                    ->orLike('u.cnic', $q)
                    ->groupEnd();
            }
        }

        $employees = $builder
            ->orderBy('u.first_name', 'ASC')
            ->orderBy('u.last_name', 'ASC')
            ->limit($maxRows)
            ->get()
            ->getResult();

        $html = '';
        $i    = 1;
        foreach ($employees as $emp) {
            $salaryVal = $emp->basic_salary ?? '';
            $html .= view('admin/partials/users_bulk_employee_row', [
                'emp'         => $emp,
                'salaryValue' => $salaryVal !== '' && $salaryVal !== null ? (string) $salaryVal : '',
                'sno'         => $i++,
            ]);
        }

        if ($html === '') {
            $html = '<div class="alert alert-light border text-center text-muted mb-0">No employees match the current filters.</div>';
        }

        return $this->response->setBody($html);
    }

    /**
     * POST: update one employee row (whitelist fields only).
     */
    public function saveRow()
    {
        $campusId = (int) $this->session->get('member_campusid');
        $actorId  = (int) $this->session->get('member_userid');
        $id       = (int) $this->request->getPost('id');

        if ($id <= 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Employee id is required.']);
        }

        $user = $this->db->table('users')
            ->where('id', $id)
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();

        if (! $user) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Employee not found in this campus.']);
        }

        $allowed = $this->allowedBulkEditFields();
        $selected = array_values(array_unique(array_filter((array) $this->request->getPost('selected_fields'))));
        $apply    = array_intersect($selected, array_keys($allowed));

        if (empty($apply)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Select at least one column to update.']);
        }

        $rules = [];
        foreach ($apply as $field) {
            $rules[$field] = $allowed[$field]['rules'];
        }

        $validation = \Config\Services::validation();
        if (! $validation->setRules($rules)->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Validation failed',
                'errors'  => $validation->getErrors(),
            ]);
        }

        $data = ['updated_date' => date('Y-m-d H:i:s')];

        foreach ($apply as $field) {
            $col = $allowed[$field]['column'];
            if ($field === 'cnic') {
                $norm = $this->normalizeCnic((string) $this->request->getPost('cnic'));
                $data[$col] = $norm;
                continue;
            }
            if ($field === 'salary') {
                $raw = $this->request->getPost('salary');
                if ($raw === '' || $raw === null) {
                    $data['basic_salary'] = null;
                } else {
                    $data['basic_salary'] = (float) $raw;
                }
                continue;
            }
            $val = $this->request->getPost($field);
            if (is_string($val)) {
                $val = trim($val);
            }
            $data[$col] = ($val === '') ? null : $val;
        }

        $this->db->transStart();

        try {
            $ok = $this->db->table('users')->where('id', $id)->where('campus_id', $campusId)->update($data);
            if (! $ok) {
                throw new \RuntimeException('Update failed.');
            }

            if (in_array('salary', $apply, true) && array_key_exists('basic_salary', $data) && $data['basic_salary'] !== null) {
                $oldBasic = (float) ($user->basic_salary ?? 0);
                $newBasic = (float) $data['basic_salary'];
                if ($newBasic !== $oldBasic) {
                    $salaryModel = new \App\Models\SalaryModel();
                    $salaryModel->recordIncrement(
                        $id,
                        $campusId,
                        $oldBasic,
                        $newBasic,
                        'Updated via bulk employee info',
                        $actorId
                    );
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();

            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }

        return $this->response->setJSON([
            'success' => true,
            'msg'     => 'Saved.',
            'fields'  => $apply,
        ]);
    }

    /**
     * POST: create multiple employees (JSON `rows` array). Each row validated; earlier rows are not rolled back if a later row fails.
     */
    public function saveBatchNew()
    {
        $campusId = (int) $this->session->get('member_campusid');
        $actorId  = (int) $this->session->get('member_userid');

        $raw = $this->request->getPost('rows');
        if (is_string($raw)) {
            $rows = json_decode($raw, true);
        } elseif (is_array($raw)) {
            $rows = $raw;
        } else {
            $rows = [];
        }

        if (! is_array($rows) || empty($rows)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'No rows submitted.']);
        }

        if (count($rows) > 25) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Maximum 25 employees per batch.']);
        }

        $out = [];

        foreach ($rows as $idx => $row) {
            if (! is_array($row)) {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'Invalid row'];
                continue;
            }

            $username = trim((string) ($row['username'] ?? ''));
            $email    = trim((string) ($row['email'] ?? ''));
            $password = (string) ($row['password'] ?? '');
            $confirm  = (string) ($row['confirm_password'] ?? '');
            $fname    = trim((string) ($row['first_name'] ?? ''));
            $lname    = trim((string) ($row['last_name'] ?? ''));
            $cnic     = $this->normalizeCnic((string) ($row['cnic'] ?? ''));
            $cnicDigits = preg_replace('/\D+/', '', $cnic);
            if (strlen($cnicDigits) !== 13) {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'CNIC must be 13 digits.'];
                continue;
            }
            $roleIds  = $row['role_ids'] ?? [];
            if (! is_array($roleIds)) {
                $roleIds = [$roleIds];
            }
            $roleIds = array_values(array_unique(array_filter(array_map('intval', $roleIds))));
            $planId  = $this->getCampusPlanId($campusId);
            if ($planId > 0 && $roleIds !== []) {
                helper('role');
                $roleIds = normalizeRoleIdsForPlan($roleIds, $planId);
            }

            if ($username === '' || $email === '' || $fname === '' || $cnic === '') {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'Username, email, first name, and CNIC are required.'];
                continue;
            }
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'Invalid email.'];
                continue;
            }
            if (strlen($password) < 6) {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'Password must be at least 6 characters.'];
                continue;
            }
            if ($password !== $confirm) {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'Passwords do not match.'];
                continue;
            }
            if (empty($roleIds)) {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'Select at least one role.'];
                continue;
            }

            foreach ($roleIds as $rid) {
                if (! $this->canAssignRole($rid, $campusId)) {
                    $out[] = ['index' => $idx, 'success' => false, 'msg' => 'You cannot assign one of the selected roles.'];
                    continue 2;
                }
            }

            if ($this->db->table('users')->where('username', $username)->get(1)->getRow()) {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'Username already taken.'];
                continue;
            }
            if ($this->db->table('users')->where('email', $email)->get(1)->getRow()) {
                $out[] = ['index' => $idx, 'success' => false, 'msg' => 'Email already taken.'];
                continue;
            }

            $joining = trim((string) ($row['joining_date'] ?? ''));
            if ($joining === '') {
                $joining = date('Y-m-d');
            }

            $salRaw = $row['salary'] ?? '';
            $basic  = ($salRaw === '' || $salRaw === null) ? null : (float) $salRaw;

            $insert = [
                'campus_id'             => $campusId,
                'username'              => $username,
                'email'                 => $email,
                'password'              => password_hash($password, PASSWORD_DEFAULT),
                'first_name'            => $fname,
                'last_name'             => $lname,
                'cnic'                  => $cnic,
                'designation'           => trim((string) ($row['designation'] ?? '')) ?: null,
                'joining_date'          => $joining,
                'mobile_no'             => trim((string) ($row['mobile_no'] ?? '')) ?: null,
                'status'                => 1,
                'created_date'          => date('Y-m-d H:i:s'),
                'updated_date'          => date('Y-m-d H:i:s'),
                'contract_type'         => 'permanent',
                'salary_payment_method' => 'bank',
            ];

            if ($basic !== null) {
                $insert['basic_salary'] = $basic;
            }

            $this->db->transStart();
            try {
                if (! $this->db->table('users')->insert($insert)) {
                    throw new \RuntimeException('Insert failed.');
                }
                $newId = (int) $this->db->insertID();

                foreach ($roleIds as $rid) {
                    $this->db->table('user_roles')->insert([
                        'userID'  => $newId,
                        'roleID'  => $rid,
                        'addDate' => date('Y-m-d H:i:s'),
                    ]);
                }

                if ($basic !== null && $basic > 0) {
                    $salaryModel = new \App\Models\SalaryModel();
                    $salaryModel->recordIncrement(
                        $newId,
                        $campusId,
                        0,
                        $basic,
                        'Initial salary on bulk employee creation',
                        $actorId
                    );
                }

                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    throw new \RuntimeException('Transaction failed.');
                }

                $out[] = ['index' => $idx, 'success' => true, 'msg' => 'Created', 'id' => $newId];
            } catch (\Throwable $e) {
                $this->db->transRollback();
                $out[] = ['index' => $idx, 'success' => false, 'msg' => $e->getMessage()];
            }
        }

        $total   = count($rows);
        $okCount = count(array_filter($out, static fn ($r) => ! empty($r['success'])));

        return $this->response->setJSON([
            'success'        => $okCount > 0,
            'all_succeeded'  => $okCount === $total,
            'msg'            => $okCount . ' of ' . $total . ' created successfully.',
            'results'        => $out,
        ]);
    }

    protected function allowedBulkEditFields(): array
    {
        return [
            'first_name'                => ['column' => 'first_name', 'rules' => 'permit_empty|max_length[100]'],
            'last_name'                 => ['column' => 'last_name', 'rules' => 'permit_empty|max_length[100]'],
            'designation'             => ['column' => 'designation', 'rules' => 'permit_empty|max_length[150]'],
            'cnic'                      => ['column' => 'cnic', 'rules' => 'required|max_length[25]'],
            'f_name'                    => ['column' => 'f_name', 'rules' => 'permit_empty|max_length[100]'],
            'dob'                       => ['column' => 'dob', 'rules' => 'permit_empty|valid_date'],
            'gender'                    => ['column' => 'gender', 'rules' => 'permit_empty|in_list[male,female]'],
            'marital_status'            => ['column' => 'marital_status', 'rules' => 'permit_empty|in_list[single,married,divorced]'],
            'qualification'             => ['column' => 'qualification', 'rules' => 'permit_empty|max_length[255]'],
            'experience'              => ['column' => 'experience', 'rules' => 'permit_empty|max_length[100]'],
            'skills'                    => ['column' => 'skills', 'rules' => 'permit_empty|max_length[500]'],
            'address'                   => ['column' => 'address', 'rules' => 'permit_empty|max_length[500]'],
            'mobile_no'                 => ['column' => 'mobile_no', 'rules' => 'permit_empty|max_length[30]'],
            'mobile_no2'                => ['column' => 'mobile_no2', 'rules' => 'permit_empty|max_length[30]'],
            'emergency_contact_person'  => ['column' => 'emergency_contact_person', 'rules' => 'permit_empty|max_length[100]'],
            'emergency_contact_no'      => ['column' => 'emergency_contact_no', 'rules' => 'permit_empty|max_length[30]'],
            'bank_name'                 => ['column' => 'bank_name', 'rules' => 'permit_empty|max_length[150]'],
            'account_title'             => ['column' => 'account_title', 'rules' => 'permit_empty|max_length[150]'],
            'account_number'            => ['column' => 'account_number', 'rules' => 'permit_empty|max_length[64]'],
            'branch_code'               => ['column' => 'branch_code', 'rules' => 'permit_empty|max_length[32]'],
            'bank_address'              => ['column' => 'bank_address', 'rules' => 'permit_empty|max_length[255]'],
            'joining_date'              => ['column' => 'joining_date', 'rules' => 'permit_empty|valid_date'],
            'salary'                    => ['column' => 'basic_salary', 'rules' => 'permit_empty|decimal'],
            'contract_type'             => ['column' => 'contract_type', 'rules' => 'permit_empty|in_list[permanent,contract,probation]'],
            'salary_payment_method'     => ['column' => 'salary_payment_method', 'rules' => 'permit_empty|in_list[bank,cash,cheque]'],
            'contract_start'            => ['column' => 'contract_start', 'rules' => 'permit_empty|valid_date'],
            'contract_end'              => ['column' => 'contract_end', 'rules' => 'permit_empty|valid_date'],
        ];
    }

    protected function normalizeCnic(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('/^\d{5}-\d{7}-\d$/', $raw)) {
            return $raw;
        }
        $d = preg_replace('/\D+/', '', $raw);
        if (strlen($d) === 13) {
            return substr($d, 0, 5) . '-' . substr($d, 5, 7) . '-' . substr($d, 12, 1);
        }

        return $raw;
    }
}
