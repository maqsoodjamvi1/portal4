<?php

namespace App\Libraries;

use Config\Trial;

class TrialProvisioningService
{
    protected $db;

    public function __construct(?Trial $config = null)
    {
        $this->db     = \Config\Database::connect();
        $this->config = $config ?? config('Trial');
    }

    protected Trial $config;

    /**
     * Create a new trial school tenant: system, campus, director user, and bill.
     *
     * @return array{success:bool,msg?:string,system_id?:int,campus_id?:int,user_id?:int,username?:string}
     */
    public function provision(array $input, bool $passwordAlreadyHashed = false): array
    {
        $schoolName = trim((string) ($input['school_name'] ?? ''));
        $firstName  = trim((string) ($input['first_name'] ?? ''));
        $lastName   = trim((string) ($input['last_name'] ?? ''));
        $phone      = trim((string) ($input['phone_no'] ?? ''));
        $email      = strtolower(trim((string) ($input['email'] ?? '')));
        $password   = (string) ($input['password'] ?? '');

        if ($schoolName === '' || $firstName === '' || $lastName === '' || $phone === '' || $email === '' || $password === '') {
            return ['success' => false, 'msg' => 'All fields are required.'];
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'msg' => 'Please enter a valid email address.'];
        }

        if ($this->userExists($email, $email)) {
            return ['success' => false, 'msg' => 'An account with this email already exists. Please sign in or use a different email.'];
        }

        $username = $this->ensureUniqueUsername($this->deriveUsername($email));

        $planId = (int) $this->config->defaultPlanId;
        $plan   = $this->db->table('system_plans')->where('plan_id', $planId)->get()->getRow();
        if (! $plan) {
            return ['success' => false, 'msg' => 'Trial plan is not configured. Please contact support.'];
        }

        $installPlan = $this->getYearlyInstallPlan();
        if (! $installPlan) {
            return ['success' => false, 'msg' => 'Billing installment plan is not configured. Please contact support.'];
        }

        $installPlanId = (int) ($installPlan->install_id ?? 0);
        if ($installPlanId <= 0) {
            return ['success' => false, 'msg' => 'Billing installment plan is not configured. Please contact support.'];
        }

        $ownerName         = trim($firstName . ' ' . $lastName);
        $shortName         = $this->deriveShortName($schoolName);
        $date              = date('Y-m-d H:i:s');
        $expiryDate        = date('Y-m-d', strtotime('+' . (int) $this->config->trialDays . ' days'));
        $hashedPass        = $passwordAlreadyHashed
            ? $password
            : password_hash($password, PASSWORD_DEFAULT);
        $campusDirectorId  = $this->resolveRoleId($planId, ['Campus Director', 'Director Campus', 'Campus director']);
        if ($campusDirectorId <= 0) {
            return ['success' => false, 'msg' => 'Campus Director role is not configured for the trial plan. Please contact support.'];
        }

        $this->db->transStart();

        $this->db->table('system')->insert([
            'system_name'     => $schoolName,
            'owner_name'      => $ownerName,
            'mob_number'      => $phone,
            'address'         => '',
            'city'            => '',
            'reg_text'        => '',
            'created_date'    => $date,
            'user_id'         => 0,
        ]);
        $systemId = (int) $this->db->insertID();

        if ($systemId <= 0) {
            $this->db->transRollback();

            return ['success' => false, 'msg' => 'Could not create school profile. Please try again.'];
        }

        $campusData = [
            'system_id'    => $systemId,
            'campus_name'  => $schoolName,
            'short_name'   => $shortName,
            'mobile_no'    => $phone,
            'location'     => '',
            'created_date' => $date,
            'user_id'      => 0,
        ];
        if ($this->tableHasColumn('campus', 's_flag')) {
            $campusData['s_flag'] = 1;
        }
        $this->db->table('campus')->insert($campusData);
        $campusId = (int) $this->db->insertID();

        if ($campusId <= 0) {
            $this->db->transRollback();

            return ['success' => false, 'msg' => 'Could not create campus. Please try again.'];
        }

        $this->db->table('users')->insert([
            'campus_id'    => $campusId,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'email'        => $email,
            'username'     => $username,
            'password'     => $hashedPass,
            'mobile_no'    => $phone,
            'address'      => '',
            'created_date' => $date,
            'user_id'      => 0,
            'status'       => 1,
        ]);
        $userId = (int) $this->db->insertID();

        if ($userId <= 0) {
            $this->db->transRollback();

            return ['success' => false, 'msg' => 'Could not create director account. Please try again.'];
        }

        $this->db->table('user_roles')->insert([
            'userID'  => $userId,
            'roleID'  => $campusDirectorId,
            'addDate' => $date,
        ]);

        $billData = [
            'campus_id'       => $campusId,
            'plan_id'         => $planId,
            'install_id'      => $installPlanId,
            'max_students'    => $plan->student_limit ?? 0,
            'max_fee'         => $plan->fee_limit ?? 0,
            'status'          => 1,
            'campus_expiry'   => $expiryDate,
            'bill_amount'     => $plan->price ?? 0,
            'bill_status'     => 'unpaid',
            'bill_issue_date' => date('Y-m-d'),
            'created_date'    => $date,
            'user_id'         => 0,
        ];
        if ($this->tableHasColumn('campus_bills', 'system_status')) {
            $billData['system_status'] = 'testing';
        }
        $this->db->table('campus_bills')->insert($billData);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            $dbError = $this->db->error();
            $message = trim((string) ($dbError['message'] ?? ''));
            log_message('error', 'Trial signup provision failed: {msg}', ['msg' => $message]);

            return [
                'success' => false,
                'msg'     => $message !== ''
                    ? ('Account could not be created: ' . $message)
                    : 'Account could not be created. Please try again or contact support.',
            ];
        }

        return [
            'success'   => true,
            'system_id' => $systemId,
            'campus_id' => $campusId,
            'user_id'   => $userId,
            'id'        => $userId,
            'username'  => $username,
        ];
    }

    public function emailAlreadyRegistered(string $email): bool
    {
        $email = strtolower(trim($email));

        return $this->userExists($email, $email);
    }

    protected function userExists(string $email, string $username): bool
    {
        return (bool) $this->db->table('users')
            ->select('id')
            ->groupStart()
                ->where('email', $email)
                ->orWhere('username', $email)
                ->orWhere('username', $username)
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRow();
    }

    protected function deriveUsername(string $email): string
    {
        $local = strtolower((string) strstr($email, '@', true));
        $local = preg_replace('/[^a-z0-9_.-]/', '', $local) ?? '';
        if (strlen($local) >= 3) {
            return $local;
        }

        return $email;
    }

    protected function ensureUniqueUsername(string $base): string
    {
        $candidate = $base;
        $suffix    = 0;
        while ($this->usernameTaken($candidate)) {
            $suffix++;
            $candidate = substr($base, 0, 90) . $suffix;
        }

        return $candidate;
    }

    protected function usernameTaken(string $username): bool
    {
        return (bool) $this->db->table('users')
            ->select('id')
            ->groupStart()
                ->where('username', $username)
                ->orWhere('email', $username)
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRow();
    }

    protected function deriveShortName(string $schoolName): string
    {
        $slug = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $schoolName) ?? '');
        if (strlen($slug) >= 3) {
            return substr($slug, 0, 8);
        }

        return 'SCH' . substr((string) time(), -5);
    }

    /**
     * Same installment resolution as Admin\Campus campus create.
     */
    protected function getYearlyInstallPlan(): ?object
    {
        $row = $this->db->table('system_installment_plan')
            ->where('month_count', 12)
            ->get()
            ->getRow();
        if ($row) {
            return $row;
        }

        return $this->db->table('system_installment_plan')
            ->orderBy('month_count', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();
    }

    /**
     * Resolve user_roles.roleID as roles.id (primary ACL mapping).
     */
    protected function resolveRoleId(int $planId, array $candidates): int
    {
        foreach ($candidates as $label) {
            $role = $this->db->table('roles r')
                ->select('r.id')
                ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
                ->where('rn.rolename', $label)
                ->groupStart()
                    ->where('r.plan_id', $planId)
                    ->orWhere('r.issys', 1)
                ->groupEnd()
                ->orderBy('r.plan_id', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();
            if ($role) {
                return (int) $role->id;
            }
        }

        $fallback = $this->db->table('roles r')
            ->select('r.id')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
            ->groupStart()
                ->like('rn.rolename', 'Campus')
                ->like('rn.rolename', 'Director')
            ->groupEnd()
            ->groupStart()
                ->where('r.plan_id', $planId)
                ->orWhere('r.issys', 1)
            ->groupEnd()
            ->orderBy('r.plan_id', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        return $fallback ? (int) $fallback->id : 0;
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (! array_key_exists($key, $cache)) {
            $cache[$key] = in_array($column, $this->db->getFieldNames($table), true);
        }

        return $cache[$key];
    }
}
