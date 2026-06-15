<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MemberAcl;
use App\Libraries\MenuPermissionCatalog;
use App\Libraries\RoleMenuAccess;
use App\Models\RoleModel;
use App\Models\RoleNameModel;
use App\Models\PermissionModel;
use CodeIgniter\API\ResponseTrait;

class Roles extends BaseController
{
    use ResponseTrait;

    protected $roleModel;
    protected $roleNameModel;
    protected $permissionModel;
    protected $helpers = ['form', 'url', 'permission'];

    public function __construct()
    {
        check_permission('admin-roles');
        $this->roleModel = new RoleModel();
        $this->roleNameModel = new RoleNameModel();
        $this->permissionModel = new PermissionModel();
    }

/**
 * Simple test method to check permissions data
 */
public function test_perms()
{
    $permissions = $this->db->table('permissions')
        ->select('id, permName, permKey, parent_id')
        ->orderBy('sortid', 'ASC')
        ->get()
        ->getResult();

    // Build simple tree
    $tree = $this->buildSimpleTreeArray($permissions, 0);

    return $this->response->setJSON($tree);
}

private function buildSimpleTreeArray($permissions, $parentId)
{
    $result = [];
    foreach ($permissions as $perm) {
        if ($perm->parent_id == $parentId) {
            $node = [
                'id' => $perm->id,
                'name' => $perm->permName,
                'permKey' => $perm->permKey,
                'open' => true
            ];
            $children = $this->buildSimpleTreeArray($permissions, $perm->id);
            if (!empty($children)) {
                $node['children'] = $children;
            }
            $result[] = $node;
        }
    }
    return $result;
}
    /**
     * Display roles list
     */
   public function index()
{
    helper('role');

    $data = [
        'title' => 'Role Management',
        'total_roles' => $this->roleModel->countAll(),
    ];
    return view('admin/roles/index', $data);
}
    /**
     * Get roles data for DataTable
     */
   /**
 * Get roles data for DataTable
 */
public function data()
{
    $draw = $this->request->getPost('draw');
    $start = $this->request->getPost('start');
    $length = $this->request->getPost('length');
    $search = $this->request->getPost('search')['value'] ?? '';

    helper('role');
    $rolePlanId = getRolePlanId();

    // Get total records
    $totalRecords = $this->roleModel->countAll();

    // Build query — annual package (plan 3) roles only
    $builder = $this->db->table('roles r')
        ->select('r.id, r.role_name_id, r.plan_id, r.issys, rn.rolename as role_name')
        ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'left')
        ->where('r.plan_id', $rolePlanId);

    // Apply search if provided
    if (!empty($search)) {
        $builder->like('rn.rolename', $search);
    }

    // Get filtered count
    $filteredRecords = $builder->countAllResults(false);

    // Get data with limit
    $builder->orderBy('r.id', 'DESC')
        ->limit($length, $start);

    $roles = $builder->get()->getResult();

    $response = [
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => []
    ];

    foreach ($roles as $row) {
        $response['data'][] = [
            'id' => $row->id,
            'roleName' => $row->role_name,
            'issys' => $row->issys,
            'actions' => $this->getActionButtons($row->id, $row->issys)
        ];
    }

    return $this->response->setJSON($response);
}

/**
 * Get action buttons for role
 */
private function getActionButtons($id, $isSys = 0)
{
    $buttons = '<div class="btn-group btn-group-sm" role="group">';

    // Edit button
    $buttons .= '<a href="' . base_url('admin/roles/edit/' . $id) . '" class="btn btn-info" title="Edit Role">
                    <i class="fas fa-edit"></i>
                 </a>';

    // Delete button - only if not system role
    if ($isSys != 1) {
        $buttons .= '<button type="button" onclick="deleteRole(' . $id . ')" class="btn btn-danger" title="Delete Role">
                        <i class="fas fa-trash"></i>
                     </button>';
    } else {
        $buttons .= '<button type="button" class="btn btn-secondary" disabled title="System role cannot be deleted">
                        <i class="fas fa-lock"></i>
                     </button>';
    }

    $buttons .= '</div>';
    return $buttons;
}
    /**
     * Add new role form
     */
  /**
 * Add new role form
 */
/**
 * Add new role form
 */
public function add()
{
    if (function_exists('check_permission')) {
        check_permission('admin-add-role');
    }

    // Get role names
    $roleNames = $this->db->table('role_name')
        ->orderBy('rolename', 'ASC')
        ->get()
        ->getResult();

    helper('role');

    $data = [
        'title' => 'Add Role',
        'role_names' => $roleNames,
        'plan_id' => getRolePlanId(),
        'role_id' => 0
    ];

    return view('admin/roles/form', $data);
}

/**
 * Edit role form
 */


public function edit($id = null)
{
    if (function_exists('check_permission')) {
        check_permission('admin-edit-role');
    }

    $id = $id ?: $this->request->getGet('id');

    $role = $this->roleModel->find($id);

    if (!$role) {
        return redirect()->to('/admin/roles')->with('error', 'Role not found');
    }

    // Get role names
    $roleNames = $this->db->table('role_name')
        ->orderBy('rolename', 'ASC')
        ->get()
        ->getResult();

    helper('role');

    $data = [
        'title' => 'Edit Role',
        'role' => $role,
        'role_names' => $roleNames,
        'plan_id' => getRolePlanId(),
        'role_id' => $id
    ];

    return view('admin/roles/form', $data);
}

public function permData()
{
    $roleId = (int) $this->request->getPost('roleid');
    // Enforce two-state model in DB: Allow(1) / Deny(0).
    $this->normalizeRolePermValuesToBinary();

    // Get all permissions
    $permissions = $this->db->table('permissions')
        ->orderBy('sortid', 'ASC')
        ->orderBy('id', 'ASC')
        ->get()
        ->getResult();

    if (empty($permissions)) {
        return $this->response->setJSON([]);
    }

    // Get role permissions - ALL rows for this role (allow / deny / ignore stored explicitly)
    $rolePerms = [];
    if ($roleId > 0) {
        $rolePermsResult = $this->db->table('role_perms')
            ->where('roleID', $roleId)
            ->get()
            ->getResult();

        foreach ($rolePermsResult as $rp) {
            $rolePerms[(int) $rp->permID] = $rp->value;
        }
    }

    $tree = $this->buildSimpleTreeWithValues($permissions, 0, $rolePerms);

    return $this->response->setJSON($tree);
}

public function menuPermData()
{
    if (function_exists('check_permission')) {
        check_permission('admin-edit-role');
    }

    $roleId = (int) $this->request->getPost('roleid');

    return $this->response->setJSON([
        'sections'     => MenuPermissionCatalog::getCatalog(),
        'state'        => MenuPermissionCatalog::getStateForRole($roleId),
        'items'        => MenuPermissionCatalog::getItemIndex(),
        'hasOverrides' => RoleMenuAccess::getMapForRole($roleId) !== [],
    ]);
}

/**
 * Auto-save menu Show/Hide toggles (no form submit required).
 */
public function saveMenuAccess()
{
    if (function_exists('check_permission')) {
        check_permission('admin-edit-role');
    }

    $roleId     = (int) $this->request->getPost('roleid');
    $menuAccess = $this->request->getPost('menu_access');

    if ($roleId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Invalid role.',
        ]);
    }

    if (! RoleMenuAccess::tableExists()) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Menu access storage is not set up. Please run database migrations.',
        ]);
    }

    if (! is_array($menuAccess)) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Invalid menu access data.',
        ]);
    }

    if (! RoleMenuAccess::saveForRole($roleId, $menuAccess)) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Menu access was not saved. Reload the page and try again (incomplete data).',
        ]);
    }

    $this->clearAclCachesForRoleUsers($roleId);

    return $this->response->setJSON([
        'success'   => true,
        'msg'       => 'Menu access saved.',
        'csrf_hash' => csrf_hash(),
    ]);
}

/**
 * Map DB role_perms.value to UI flag '1' | '0' (Allow / Deny).
 */
private function mapRolePermValueToChk($value): string
{
    if ($value === null || $value === '') {
        return '0';
    }
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    if (is_int($value) || is_float($value)) {
        if ((int) $value === 1) {
            return '1';
        }
        if ((int) $value === 0) {
            return '0';
        }

        return '0';
    }
    $s = strtolower(trim((string) $value));
    if ($s === '1' || $s === 'true' || $s === 'yes' || $s === 'allow') {
        return '1';
    }
    if ($s === '0' || $s === 'false' || $s === 'no' || $s === 'deny') {
        return '0';
    }
    if ($s === 'x' || $s === 'ignore' || $s === 'null') {
        return '0';
    }
    return '0';
}

/**
 * One-way normalization for legacy data: treat Ignore as Deny.
 */
private function normalizeRolePermValuesToBinary(): void
{
    $this->db->table('role_perms')
        ->set('value', 0)
        ->groupStart()
            ->where('value IS NULL', null, false)
            ->orWhere('value', '')
            ->orWhere('LOWER(CAST(value AS CHAR))', 'x')
            ->orWhere('LOWER(CAST(value AS CHAR))', 'ignore')
            ->orWhere('LOWER(CAST(value AS CHAR))', 'null')
            ->orWhere('LOWER(CAST(value AS CHAR))', 'false')
            ->orWhere('LOWER(CAST(value AS CHAR))', 'deny')
        ->groupEnd()
        ->update();
}

private function buildSimpleTreeWithValues($items, $parentId = 0, $rolePerms = [], $level = 0)
{
    $branch = [];

    foreach ($items as $item) {
        if ($item->parent_id == $parentId) {
            $chk = '0';

            if (array_key_exists((int) $item->id, $rolePerms)) {
                $chk = $this->mapRolePermValueToChk($rolePerms[(int) $item->id]);
            } else {
                $chk = '0';
            }

            $node = [
                'id' => $item->id,
                'name' => $item->permName,
                'permKey' => $item->permKey,
                'chk' => $chk,
                'children' => $this->buildSimpleTreeWithValues($items, $item->id, $rolePerms, $level + 1)
            ];

            $branch[] = $node;
        }
    }

    return $branch;
}


    /**
     * Get system plans
     */
    private function getSystemPlans()
    {
        return $this->db->table('system_plans')
            ->orderBy('plan_id', 'ASC')
            ->get()
            ->getResult();
    }


    public function get_role_by_name()
{
    $roleNameId = (int) $this->request->getPost('role_name_id');

    if ($roleNameId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No role name provided'
        ]);
    }

    helper('role');

    $role = $this->db->table('roles')
        ->where('role_name_id', $roleNameId)
        ->where('plan_id', getRolePlanId())
        ->orderBy('id', 'DESC')
        ->get()
        ->getRow();

    $roleName = $this->db->table('role_name')
        ->where('role_name_id', $roleNameId)
        ->get()
        ->getRow();

    if ($role) {
        return $this->response->setJSON([
            'success' => true,
            'role_id' => (int) $role->id,
            'role_name' => $roleName ? $roleName->rolename : '',
            'plan_id' => $role->plan_id,
            'message' => 'Role exists'
        ]);
    }

    return $this->response->setJSON([
        'success' => false,
        'role_id' => 0,
        'message' => 'No role found for selected role name and plan'
    ]);
}

    /**
     * Save role (add/edit)
     */
   public function save()
{
    // Validate and get data
    $id = $this->request->getPost('id');
    $role_name_id = $this->request->getPost('role_name_id');
    $permissions = $this->request->getPost('perms');
    $menuAccess  = $this->request->getPost('menu_access');

    helper('role');
    $plan_id = getRolePlanId();

    $this->db->transStart();

    if ($id) {
        // Update existing role
        $this->roleModel->update($id, [
            'role_name_id' => $role_name_id,
            'plan_id' => $plan_id
        ]);
        $roleId = $id;
    } else {
        // Insert new role
        $roleId = $this->roleModel->insert([
            'role_name_id' => $role_name_id,
            'plan_id' => $plan_id,
            'issys' => 0
        ]);
    }

    if ($roleId) {
        // Delete existing permissions for this role
        $this->db->table('role_perms')
            ->where('roleID', $roleId)
            ->delete();

        // Insert new permissions
        if ($permissions && is_array($permissions)) {
            $insertData = [];
            foreach ($permissions as $permId => $value) {
                $normalized = $this->mapRolePermValueToChk($value);
                $insertData[] = [
                    'roleID' => $roleId,
                    'permID' => $permId,
                    'value' => ($normalized === '1' ? 1 : 0),
                    'add_date' => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($insertData)) {
                $this->db->table('role_perms')->insertBatch($insertData);
            }
        }

        if (is_array($menuAccess) && ! RoleMenuAccess::saveForRole((int) $roleId, $menuAccess)) {
            $this->db->transComplete();

            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Failed to save menu access for this role.',
            ]);
        }
    }

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Failed to save role'
        ]);
    }

    $this->clearAclCachesForRoleUsers((int) $roleId);

    return $this->response->setJSON([
        'success' => true,
        'msg' => $id ? 'Role updated successfully' : 'Role created successfully',
        'role_id' => (int) $roleId
    ]);
}

    /**
     * Invalidate per-user permission and menu caches for everyone on this role.
     */
    private function clearAclCachesForRoleUsers(int $roleId): void
    {
        if ($roleId <= 0) {
            return;
        }

        $userIds = [];

        $rows = $this->db->table('user_roles')
            ->select('userID')
            ->where('roleID', $roleId)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $uid = (int) ($row['userID'] ?? 0);
            if ($uid > 0) {
                $userIds[$uid] = $uid;
            }
        }

        $legacyRows = $this->db->table('user_roles ur')
            ->select('ur.userID')
            ->join('roles r', 'r.role_name_id = ur.roleID')
            ->where('r.id', $roleId)
            ->get()
            ->getResultArray();

        foreach ($legacyRows as $row) {
            $uid = (int) ($row['userID'] ?? 0);
            if ($uid > 0) {
                $userIds[$uid] = $uid;
            }
        }

        foreach ($userIds as $uid) {
            (new MemberAcl($uid))->clearUserCaches($uid);
            RoleMenuAccess::clearCacheForUser($uid);
        }
    }

    /**
     * Save role permissions
     */
    private function saveRolePermissions($roleId)
    {
        $permissions = $this->request->getPost('perms');

        if ($permissions && is_array($permissions)) {
            // Delete existing permissions
            $this->db->table('role_perms')
                ->where('roleID', $roleId)
                ->delete();

            // Insert new permissions
            foreach ($permissions as $permId => $value) {
                if ($value == 1) {
                    $this->db->table('role_perms')->insert([
                        'roleID' => $roleId,
                        'permID' => $permId,
                        'value' => $value,
                        'add_date' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    /**
     * Delete role
     */
    public function delete($id = null)
    {
        check_permission('admin-del-role');

        $id = $id ?: $this->request->getGet('id');

        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid role ID'
            ]);
        }

        // Check if role is system role (issys = 1)
        $role = $this->roleModel->find($id);
        if ($role && $role->issys == 1) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Cannot delete system role'
            ]);
        }

        $this->db->transStart();

        // Delete role permissions
        $this->db->table('role_perms')->where('roleID', $id)->delete();

        // Delete user role assignments
        $this->db->table('user_roles')->where('roleID', $id)->delete();

        // Delete role
        $this->roleModel->delete($id);

        $this->db->transComplete();

        // Clear cache
        if (function_exists('cxp_update_cache')) {
            cxp_update_cache();
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Role deleted successfully'
        ]);
    }

    /**
     * Get permissions data for tree view
     */
    /**
 * Get permissions data for tree view (compatible with existing code)
 */
/**
 * Get permissions data for tree view - Simplified version
 */


/**
 * Build simple tree array
 */


private function buildSimpleTree($items, $parentId = 0, $rolePerms = [], $level = 0)
{
    $branch = [];

    foreach ($items as $item) {
        if ($item->parent_id == $parentId) {
            // Determine permission value
            $chk = 'x'; // Default to ignore

            // Check if this permission is assigned to the role
            if (isset($rolePerms[$item->id])) {
                $chk = '1'; // Allow
            } else {
                // Check for parent inheritance
                if ($level > 0 && isset($this->parentPermValues[$parentId])) {
                    $chk = $this->parentPermValues[$parentId];
                } else {
                    $chk = 'x';
                }
            }

            // Store parent value for children
            if (!isset($this->parentPermValues)) {
                $this->parentPermValues = [];
            }
            $this->parentPermValues[$item->id] = $chk;

            $node = [
                'id' => $item->id,
                'name' => $item->permName,
                'permKey' => $item->permKey,
                'chk' => $chk,
                'children' => $this->buildSimpleTree($items, $item->id, $rolePerms, $level + 1)
            ];

            $branch[] = $node;
        }
    }

    return $branch;
}

    /**
     * Get permission tree data (for role form)
     */

    /**
 * Get permissions directly (without async)
 */
/**
 * Get permissions directly (with error handling)
 */
/**
 * Get permissions directly for zTree
 */
public function get_permissions_direct()
{
    try {
        $roleId = (int) $this->request->getPost('roleid');

        // Get all permissions
        $permissions = $this->db->table('permissions')
            ->select('id, permName, permKey, parent_id, sortid')
            ->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        if (empty($permissions)) {
            return $this->response->setJSON([]);
        }

        // Get role permissions if editing
        $rolePermissions = [];
        if ($roleId > 0) {
            $rolePerms = $this->db->table('role_perms')
                ->where('roleID', $roleId)
                ->where('value', 1)
                ->get()
                ->getResult();

            foreach ($rolePerms as $rp) {
                $rolePermissions[$rp->permID] = 1;
            }
        }

        // Build tree for zTree
        $tree = [];
        $permMap = [];

        // First, create a map of all permissions
        foreach ($permissions as $perm) {
            $permMap[$perm->id] = [
                'id' => $perm->id,
                'name' => $perm->permName,
                'permKey' => $perm->permKey,
                'parent_id' => $perm->parent_id,
                'chk' => isset($rolePermissions[$perm->id]) ? '1' : 'x',
                'open' => true,
                'children' => []
            ];
        }

        // Build the tree structure
        foreach ($permMap as $id => &$node) {
            if ($node['parent_id'] == 0) {
                $tree[] = &$node;
            } else {
                if (isset($permMap[$node['parent_id']])) {
                    $permMap[$node['parent_id']]['children'][] = &$node;
                }
            }
        }

        // Remove empty children arrays
        foreach ($permMap as &$node) {
            if (empty($node['children'])) {
                unset($node['children']);
            }
        }

        return $this->response->setJSON($tree);

    } catch (\Exception $e) {
        log_message('error', 'Error in get_permissions_direct: ' . $e->getMessage());
        return $this->response->setJSON([
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Build simple tree data for zTree
 */
private function buildSimpleTreeData($permissions, $parentId, $rolePermissions)
{
    $result = [];

    foreach ($permissions as $perm) {
        if ($perm->parent_id == $parentId) {
            $chk = 'x';
            if (isset($rolePermissions[$perm->id])) {
                $chk = '1';
            }

            $node = [
                'id' => $perm->id,
                'name' => $perm->permName,
                'permKey' => $perm->permKey,
                'parent_id' => $perm->parent_id,
                'chk' => $chk,
                'open' => true
            ];

            // Check for children
            $hasChildren = false;
            foreach ($permissions as $p) {
                if ($p->parent_id == $perm->id) {
                    $hasChildren = true;
                    break;
                }
            }

            if ($hasChildren) {
                $node['children'] = $this->buildSimpleTreeData($permissions, $perm->id, $rolePermissions);
                $node['isParent'] = true;
            }

            $result[] = $node;
        }
    }

    return $result;
}

/**
 * Debug method to check permissions
 */
public function debug_permissions()
{
    // Check if permissions table exists
    $tables = $this->db->listTables();
    $permissionsExists = in_array('permissions', $tables);

    // Get count of permissions
    $permissionCount = 0;
    $permissions = [];

    if ($permissionsExists) {
        $permissionCount = $this->db->table('permissions')->countAll();
        $permissions = $this->db->table('permissions')
            ->select('id, permName, permKey, parent_id')
            ->limit(10)
            ->get()
            ->getResult();
    }

    return $this->response->setJSON([
        'permissions_table_exists' => $permissionsExists,
        'permission_count' => $permissionCount,
        'sample_permissions' => $permissions,
        'database_error' => $this->db->error()
    ]);
}

/**
 * Get permissions as simple list (without tree)
 */
public function get_permissions_list()
{
    try {
        $roleId = (int) $this->request->getPost('roleid');

        // Get all permissions
        $permissions = $this->db->table('permissions')
            ->select('id, permName, permKey, parent_id, sortid')
            ->orderBy('sortid', 'ASC')
            ->orderBy('parent_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        if (empty($permissions)) {
            return $this->response->setJSON([
                'success' => true,
                'permissions' => []
            ]);
        }

        // Get role permissions
        $rolePermissions = [];
        if ($roleId > 0) {
            $rolePerms = $this->db->table('role_perms')
                ->where('roleID', $roleId)
                ->where('value', 1)
                ->get()
                ->getResult();

            foreach ($rolePerms as $rp) {
                $rolePermissions[$rp->permID] = 1;
            }
        }

        // Build hierarchical structure
        $permissionMap = [];
        $tree = [];

        // First, create a map
        foreach ($permissions as $perm) {
            $permissionMap[$perm->id] = [
                'id' => $perm->id,
                'permName' => $perm->permName,
                'permKey' => $perm->permKey,
                'parent_id' => $perm->parent_id,
                'chk' => isset($rolePermissions[$perm->id]) ? '1' : 'x',
                'children' => []
            ];
        }

        // Build tree
        foreach ($permissionMap as $id => &$node) {
            if ($node['parent_id'] == 0) {
                $tree[] = &$node;
            } else {
                if (isset($permissionMap[$node['parent_id']])) {
                    $permissionMap[$node['parent_id']]['children'][] = &$node;
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'permissions' => $tree,
            'total' => count($permissions)
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Error in get_permissions_list: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'error' => $e->getMessage(),
            'permissions' => []
        ]);
    }
}


    public function getPermissionTree()
    {
        $roleId = (int) $this->request->getGet('role_id');
        $permissions = $this->permissionModel->getAllPermissions();

        $rolePermissions = [];
        if ($roleId > 0) {
            $rolePerms = $this->roleModel->getRolePermissions($roleId);
            foreach ($rolePerms as $perm) {
                $rolePermissions[$perm->id] = 1;
            }
        }

        $tree = $this->buildPermissionTree($permissions, 0, $rolePermissions);

        return $this->response->setJSON($tree);
    }
}
