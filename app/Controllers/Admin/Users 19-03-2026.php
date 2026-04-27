<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Users extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = db_connect();
        $this->session = session();
        helper(['form', 'url']);
    }

    public function index()
    {
        return view('admin/users');
    }

    public function data()
    {
        $campusId = $this->session->get('member_campusid');
        $userId = $this->session->get('member_userid');
        $status = $this->request->getVar('status');
        $draw = $this->request->getVar('draw');
        $start = $this->request->getVar('start');
        $length = $this->request->getVar('length');
        $searchValue = $this->request->getVar('search')['value'] ?? '';

        $builder = $this->db->table('users');
        $builder->select('count(id) as ccount');
        $builder->where('campus_id', $campusId);
        $builder->where('status', $status);

        if ($searchValue !== '') {
            $builder->groupStart()
                ->like('username', $searchValue)
                ->orLike('email', $searchValue)
                ->groupEnd();
        }

        $totalRecords = $builder->get()->getRow()->ccount;

        $builder = $this->db->table('users');
        $builder->select('*');
        $builder->where('campus_id', $campusId);
        $builder->where('status', $status);

        if ($searchValue !== '') {
            $builder->groupStart()
                ->like('username', $searchValue)
                ->orLike('email', $searchValue)
                ->groupEnd();
        }

        $builder->orderBy('id', 'DESC');
        $builder->limit($length, $start);
        $users = $builder->get()->getResult();

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => []
        ];

        foreach ($users as $user) {
            $userRoles = $this->db->table('user_roles')->where('userID', $user->id)->get()->getRow();
            $campusBill = $this->db->table('campus_bills')->where(['status' => 1, 'campus_id' => $user->campus_id])->get()->getRow();
            $planId = $campusBill->plan_id ?? null;

            $role = $this->db->table('roles')
                ->where(['role_name_id' => $userRoles->roleID ?? 0, 'plan_id' => $planId])
                ->get()->getRow();

            if ($role) {
                $roleName = $this->db->table('role_name')
                    ->where('role_name_id', $role->role_name_id)
                    ->get()->getRow();

                $response['data'][] = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $roleName->rolename ?? '',
                    'mobile_no' => $user->mobile_no,
                    'mobile_no2' => $user->mobile_no2,
                    'emergency_contact_no' => $user->emergency_contact_no,
                    'status' => $user->status
                ];
            }
        }

        return $this->response->setJSON($response);
    }

   public function add()
{
    $db          = $this->db ?? \Config\Database::connect();
    $adminUserId = (int) (session()->get('member_userid') ?? 0);
    $planId      = 1; // hardcoded as requested

    return view('admin/users_edit', [
        // your existing payload...
        'user_types'      => $db->table('user_type')->get()->getResult(),
        // NEW for the role dropdown:
        'assignableRoles' => $this->buildAssignableRolesForAdmin($adminUserId, $planId, $db),
        'selectedRoleId'  => null,
    ]);
}

   public function edit($id)
{
    $db          = $this->db ?? \Config\Database::connect();
    $user        = $db->table('users')->where('id', $id)->get()->getRow();
    $empSalary   = $db->table('emp_salary')->where(['emp_id' => $id, 'status' => 1])->get()->getRow();

    // current admin context
    $adminUserId = (int) (session()->get('member_userid') ?? 0);
    $planId      = 1; // hardcoded

    // current role (if multiple, pick the first)
    $curRoleRow     = $db->table('user_roles')->select('roleID')->where('userID', $id)->get()->getFirstRow();
    $selectedRoleId = $curRoleRow ? (int) $curRoleRow->roleID : null;

    return view('admin/users_edit', [
        'info'             => $user,
        'emp_salary_info'  => $empSalary,
        // NEW for the role dropdown:
        'assignableRoles'  => $this->buildAssignableRolesForAdmin($adminUserId, $planId, $db),
        'selectedRoleId'   => $selectedRoleId,
    ]);
}

public function save(): \CodeIgniter\HTTP\ResponseInterface
{
    $req   = $this->request;
    $db    = $this->db ?? \Config\Database::connect();
    $ses   = session();

    $id        = (int) $req->getPost('id'); // 0 => create
    $user_id   = (int) ($ses->get('member_userid') ?? 0);
    $campus_id = (int) ($ses->get('member_campusid') ?? 0);
    $now       = date('Y-m-d H:i:s');

    // VALIDATION (add role_id requirement)
    $rules = [
        'username' => 'required|alpha_dash|min_length[3]|max_length[30]' . ($id ? '|is_unique[users.username,id,{id}]' : '|is_unique[users.username]'),
        'email'    => 'required|valid_email' . ($id ? '|is_unique[users.email,id,{id}]' : '|is_unique[users.email]'),
        'cnic'     => 'required|regex_match[/^\d{5}-\d{7}-\d{1}$/]',
        'role_id'  => 'required|integer',
    ];
    if ($id === 0) {
        $rules['password'] = 'required|min_length[6]';
    }
    if ($req->getFile('image')) {
        $rules['image'] = 'if_exist|max_size[image,1024]|ext_in[image,gif,jpg,jpeg,png]|mime_in[image,image/gif,image/jpeg,image/png]';
    }
    if (! $this->validate($rules)) {
        return $this->response->setJSON(['success' => false, 'msg' => implode("\n", $this->validator->getErrors())]);
    }

    // COLLECT
    $payload = [
        'username'                 => trim((string) $req->getPost('username')),
        'email'                    => trim((string) $req->getPost('email')),
        'campus_id'                => $campus_id ?: null,
        'status'                   => 1,
        'first_name'               => trim((string) $req->getPost('first_name')),
        'last_name'                => trim((string) $req->getPost('last_name')),
        'dob'                      => (string) $req->getPost('dob'),
        'f_name'                   => trim((string) $req->getPost('f_name')),
        'cnic'                     => trim((string) $req->getPost('cnic')),
        'gender'                   => (string) $req->getPost('gender'),
        'marital_status'           => (string) $req->getPost('marital_status'),
        'joining_date'             => (string) $req->getPost('joining_date'),
        'mobile_no'                => trim((string) $req->getPost('mobile_no')),
        'mobile_no2'               => trim((string) $req->getPost('mobile_no2')),
        'address'                  => trim((string) $req->getPost('address')),
        'emergency_contact_person' => trim((string) $req->getPost('emergency_contact_person')),
        'emergency_contact_no'     => trim((string) $req->getPost('emergency_contact_no')),
        'qualification'            => trim((string) $req->getPost('qualification')),
        'experience'               => trim((string) $req->getPost('experience')),
        'skills'                   => trim((string) $req->getPost('skills')),
        'contract_start'           => (string) $req->getPost('contract_start'),
        'contract_end'             => (string) $req->getPost('contract_end'),
        'designation'              => trim((string) $req->getPost('designation')),
    ];
    $password = (string) $req->getPost('password');
    if ($id === 0 && $password !== '') $payload['password'] = password_hash($password, PASSWORD_DEFAULT);
    if ($id > 0  && $password !== '') $payload['password'] = password_hash($password, PASSWORD_DEFAULT);

    // Photo
    $file = $req->getFile('image');
    if ($file && $file->isValid() && ! $file->hasMoved()) {
        $dir = FCPATH . 'employees-img';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $newName = $file->getRandomName();
        $file->move($dir, $newName);
        $payload['photo'] = $newName;
    }

    $salary  = trim((string) $req->getPost('salary'));
    $roleId  = (int) $req->getPost('role_id');

    // **Enforce role assignment rules on the server** (hardcoded plan_id = 1)
    $planId       = 1;
    $assignable   = $this->buildAssignableRolesForAdmin($user_id, $planId, $db);
    $assignableId = array_map(fn($r) => (int)$r['id'], $assignable);
    if (! in_array($roleId, $assignableId, true)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'You are not allowed to assign the selected role.']);
    }

    // Persist
    $db->transStart();

    if (function_exists('check_permission')) {
        if ($id === 0)  { check_permission('admin-add-user'); }
        else            { check_permission('admin-edit-user'); }
    }

    $usersTbl     = $db->table('users');
    $empSalaryTbl = $db->table('emp_salary');
    $userRolesTbl = $db->table('user_roles');

    if ($id === 0) {
        $payload['user_id']      = $user_id ?: null;
        $payload['created_date'] = $now;
        $usersTbl->insert($payload);
        $newUserId = (int) $db->insertID();

        if ($salary !== '') {
            $empSalaryTbl->insert([
                'emp_id'       => $newUserId,
                'salary'       => $salary,
                'status'       => 1,
                'date'         => date('Y-m-d'),
                'created_date' => date('Y-m-d'),
                'user_id'      => $user_id ?: null,
            ]);
        }

        // Set exactly one role (no default role-2 anymore)
        $userRolesTbl->insert(['userID' => $newUserId, 'roleID' => $roleId]);

        $db->transComplete();
        if (! $db->transStatus()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Add Employee failed.']);
        }
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Employee Success']);
    }
    else {
        $payload['user_id']      = $user_id ?: null;
        $payload['updated_date'] = $now;
        $usersTbl->where('id', $id)->update($payload);

        if ($salary !== '') {
            $empSalaryTbl->where('emp_id', $id)->update([
                'salary'       => $salary,
                'updated_date' => date('Y-m-d'),
                'user_id'      => $user_id ?: null,
            ]);
        }

        // Replace existing roles with the selected one
        $userRolesTbl->where('userID', $id)->delete();
        $userRolesTbl->insert(['userID' => $id, 'roleID' => $roleId]);

        $db->transComplete();
        if (! $db->transStatus()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Edit Employee failed.']);
        }
        return $this->response->setJSON(['success' => true, 'msg' => 'Edit Employee Success']);
    }
}


private function getCurrentUserRoleIds(int $userId, \CodeIgniter\Database\BaseConnection $db): array
{
    return array_map(
        'intval',
        array_column(
            $db->table('user_roles')->select('roleID')->where('userID', $userId)->get()->getResultArray(),
            'roleID'
        )
    );
}

private function loadRoleTree(\CodeIgniter\Database\BaseConnection $db): array
{
    $rows = $db->table('role_name')->select('role_name_id, rolename, parent_id')->get()->getResultArray();
    $byId = $children = [];
    foreach ($rows as $r) {
        $id = (int) $r['role_name_id'];
        $byId[$id] = ['id' => $id, 'name' => $r['rolename'], 'parent_id' => (int) $r['parent_id']];
        $children[(int)$r['parent_id']][] = $id;
    }
    return ['byId' => $byId, 'children' => $children];
}

private function descendantsIncludingSelf(array $children, int $rootId): array
{
    $seen = [];
    $stack = [$rootId];
    while ($stack) {
        $cur = array_pop($stack);
        if (isset($seen[$cur])) continue;
        $seen[$cur] = true;
        if (!empty($children[$cur])) {
            foreach ($children[$cur] as $kid) $stack[] = $kid;
        }
    }
    return array_keys($seen);
}

private function depthOf(array $byId, int $id): int
{
    $d = 0;
    while (isset($byId[$id]) && $byId[$id]['parent_id'] > 0) {
        $id = $byId[$id]['parent_id']; $d++;
    }
    return $d;
}

private function rolesAllowedForPlan(int $planId, \CodeIgniter\Database\BaseConnection $db): array
{
    // If your roles table uses a different FK name, adjust 'role_name_id' here.
    $rows = $db->table('roles')->select('role_name_id')->where('plan_id', $planId)->get()->getResultArray();
    return array_map('intval', array_column($rows, 'role_name_id'));
}

private function buildAssignableRolesForAdmin(int $adminUserId, int $planId, \CodeIgniter\Database\BaseConnection $db): array
{
    $tree         = $this->loadRoleTree($db);
    $byId         = $tree['byId'];
    $children     = $tree['children'];
    $adminRoleIds = $this->getCurrentUserRoleIds($adminUserId, $db);
    $planRoleIds  = $this->rolesAllowedForPlan($planId, $db);

    if (empty($adminRoleIds)) {
        // If admin has no role, show nothing (you can relax this to: $planRoleIds)
        $allowedIds = [];
    } else {
        $descUnion = [];
        foreach ($adminRoleIds as $rid) {
            foreach ($this->descendantsIncludingSelf($children, $rid) as $x) {
                $descUnion[$x] = true;
            }
        }
        $allowedIds = array_values(array_intersect(array_keys($descUnion), $planRoleIds));
    }

    $out = [];
    foreach ($allowedIds as $rid) {
        if (!isset($byId[$rid])) continue;
        $depth = $this->depthOf($byId, $rid);
        $out[] = [
            'id'    => $rid,
            'name'  => str_repeat('— ', $depth) . $byId[$rid]['name'],
            'depth' => $depth
        ];
    }

    usort($out, function($a, $b){
        if ($a['depth'] === $b['depth']) return strcasecmp($a['name'], $b['name']);
        return $a['depth'] <=> $b['depth'];
    });

    return $out;
}


    public function delete($id)
    {
        $this->db->transStart();

        $this->db->table('user_perms')->where('userID', $id)->delete();
        $this->db->table('user_roles')->where('userID', $id)->delete();
        $this->db->table('users')->where('id', $id)->delete();

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete User Success']);
    }

    public function edit_password()
    {
        if ($this->request->getMethod() === 'post') {
            $this->validate([
                'password' => 'required|min_length[6]'
            ]);

            $userId = $this->request->getPost('user_id');
            $password = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);

            $this->db->table('users')->where('id', $userId)->update(['password' => $password]);

            return $this->response->setJSON(['success' => true, 'msg' => 'Password updated']);
        }

        $userId = $this->request->getGet('user_id');
        return view('admin/edit_password', ['user_id' => $userId]);
    }

    public function set_perms()
    {
        if ($this->request->getMethod() === 'post') {
            $userId = $this->request->getPost('user_id');

            foreach ($this->request->getPost() as $key => $value) {
                if (str_starts_with($key, 'perm_')) {
                    $permId = str_replace('perm_', '', $key);

                    if ($value === 'x') {
                        $this->db->table('user_perms')->where(['userID' => $userId, 'permID' => $permId])->delete();
                    } else {
                        $this->db->table('user_perms')->replace([
                            'userID' => $userId,
                            'permID' => $permId,
                            'value' => $value
                        ]);
                    }
                }
            }

            return $this->response->setJSON(['success' => true, 'msg' => 'Permissions updated']);
        }

        $userId = $this->request->getGet('user_id');
        $user = $this->db->table('users')->where('id', $userId)->get()->getRow();

        return view('admin/set_perms', [
            'user_id' => $userId,
            'info' => $user
        ]);
    }

    public function perm_data()
    {
        $permissions = $this->db->table('permissions')->orderBy('parent_id')->get()->getResult();
        $grouped = [];
        foreach ($permissions as $perm) {
            $grouped[$perm->parent_id][] = $perm;
        }

        $userId = $this->request->getPost('user_id');
        $acl = new \App\Libraries\Member_acl($userId);
        $rPerms = $acl->getPermArr();

        $output = '[' . $this->loop_parent($grouped, 0, $rPerms) . ']';
        return $this->response->setBody($output);
    }

    private function loop_parent($perms, $parentId, $rPerms)
    {
        $output = '';
        if (!isset($perms[$parentId])) return $output;

        foreach ($perms[$parentId] as $row) {
            $permKey = $row->permKey;
            $selhtml = "<select name='perm_{$row->id}'>";

            $selhtml .= "<option value='1'" . ((isset($rPerms[$permKey]) && $rPerms[$permKey]['value'] === '1' && !$rPerms[$permKey]['inheritted']) ? " selected" : "") . ">Allow</option>";
            $selhtml .= "<option value='0'" . ((isset($rPerms[$permKey]) && $rPerms[$permKey]['value'] === false && !$rPerms[$permKey]['inheritted']) ? " selected" : "") . ">Deny</option>";

            $iVal = '';
            if (!isset($rPerms[$permKey]) || $rPerms[$permKey]['inheritted']) {
                $iVal = isset($rPerms[$permKey]) ? ($rPerms[$permKey]['value'] ? '(Allow)' : '(Deny)') : '(Deny)';
                $selhtml .= "<option value='x' selected>Inherit {$iVal}</option>";
            } else {
                $selhtml .= "<option value='x'>Inherit</option>";
            }

            $selhtml .= '</select>';

            if (isset($perms[$row->id])) {
                $output .= "{id:{$row->id}, name:'{$row->permName}', select:'{$selhtml}', children:[";
                $output .= $this->loop_parent($perms, $row->id, $rPerms);
                $output .= "]},";
            } else {
                $output .= "{id:{$row->id}, name:'{$row->permName}', select:'{$selhtml}'},";
            }
        }
        return rtrim($output, ',');
    }
}
