<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Permissions extends BaseController
{
    use ResponseTrait;

    protected $helpers = ['form', 'url', 'permission'];

    public function __construct()
    {
        check_permission('admin-permissions');
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data = [
            'title' => 'Permission Management',
            'total_permissions' => $this->db->table('permissions')->countAll(),
            'total_roles' => $this->db->table('roles')->countAll()
        ];
        return view('admin/permissions', $data);
    }

    /**
     * Get permissions data for tree view - Debug version
     */
    public function data()
    {
        try {
            // Get permissions from database
            $permissions = $this->db->table('permissions')
                ->select('id, permName, permKey, parent_id, sortid')
                ->orderBy('sortid', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResult();
            
            // Check if we have data
            if (empty($permissions)) {
                // Return empty array as JSON
                return $this->response->setJSON([]);
            }
            
            // Build tree structure
            $tree = $this->buildTree($permissions);
            
            // Return as JSON
            return $this->response->setJSON($tree);
            
        } catch (\Exception $e) {
            log_message('error', 'Permissions data error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Build tree structure from flat permissions array
     */
    private function buildTree($permissions, $parentId = 0)
    {
        $tree = [];
        foreach ($permissions as $perm) {
            if ($perm->parent_id == $parentId) {
                $node = [
                    'id' => $perm->id,
                    'name' => $perm->permName,
                    'permKey' => $perm->permKey,
                    'parent_id' => $perm->parent_id,
                    'children' => $this->buildTree($permissions, $perm->id)
                ];
                $tree[] = $node;
            }
        }
        return $tree;
    }

    public function add()
    {
        check_permission('admin-add-permission');
        $parent_id = (int) $this->request->getGet('parent_id');
        
        // Get permission groups for dropdown
        $permissionGroups = $this->db->table('permissions')
            ->select('id, permName, parent_id')
            ->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
        
        // Build permission options recursively
        $perm_options = $this->buildPermissionOptions($permissionGroups, 0, 0, null, $parent_id);
        
        return view('admin/permissions_edit', [
            'parent_id' => $parent_id,
            'perm_options' => $perm_options
        ]);
    }

    public function edit()
    {
        check_permission('admin-edit-permission');
        $id = (int) $this->request->getGet('id');
        
        $info = $this->db->table('permissions')
            ->where('id', $id)
            ->get()
            ->getRow();
            
        if (!$info) {
            return redirect()->to('/admin/permissions')->with('error', 'Permission not found');
        }
        
        // Get permission groups for dropdown (excluding current permission)
        $permissionGroups = $this->db->table('permissions')
            ->select('id, permName, parent_id')
            ->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
        
        // Build permission options recursively
        $perm_options = $this->buildPermissionOptions($permissionGroups, 0, 0, $id, $info->parent_id);
        
        return view('admin/permissions_edit', [
            'info' => $info,
            'perm_options' => $perm_options
        ]);
    }

public function save()
{
    // Get POST data
    $id = (int) $this->request->getPost('id');
    
    // Debug: Log received data
    log_message('debug', 'Save permission called with ID: ' . $id);
    log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
    
    $validation = \Config\Services::validation();
    
    $validation->setRules([
        'permName' => 'required|trim|min_length[3]|max_length[255]',
        'permKey' => 'required|trim|min_length[3]|max_length[255]|is_unique[permissions.permKey,id,' . ($id ?? 0) . ']',
        'parent_id' => 'permit_empty|integer',
        'sortid' => 'permit_empty|integer'
    ]);

    if (!$validation->withRequest($this->request)->run()) {
        $errors = $validation->getErrors();
        log_message('error', 'Validation errors: ' . json_encode($errors));
        
        return $this->response->setJSON([
            'success' => false, 
            'errors' => $errors,
            'msg' => 'Validation failed'
        ]);
    }

    $db = \Config\Database::connect();
    $db->transStart();

    $data = [
        'parent_id' => (int) $this->request->getPost('parent_id'),
        'permName'  => trim($this->request->getPost('permName')),
        'permKey'   => trim($this->request->getPost('permKey')),
        'sortid'    => (int) $this->request->getPost('sortid'),
        'updated_date' => date('Y-m-d H:i:s')
    ];

    if ($id === 0) {
        // Add new permission
        check_permission('admin-add-permission');
        $data['created_date'] = date('Y-m-d H:i:s');
        
        log_message('debug', 'Inserting new permission: ' . json_encode($data));
        
        $result = $db->table('permissions')->insert($data);
        
        if (!$result) {
            $db->transRollback();
            log_message('error', 'Failed to insert permission: ' . json_encode($db->error()));
            
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Database insert failed: ' . $db->error()['message']
            ]);
        }
        
        $new_perm_id = $db->insertID();
        log_message('debug', 'New permission ID: ' . $new_perm_id);
        
        // Assign to admin role by default (role ID 5)
        $roleData = [
            'roleID' => 5,
            'permID' => $new_perm_id,
            'value'  => 1,
            'add_date' => date('Y-m-d H:i:s')
        ];
        
        log_message('debug', 'Assigning to role: ' . json_encode($roleData));
        
        $roleResult = $db->table('role_perms')->insert($roleData);
        
        if (!$roleResult) {
            $db->transRollback();
            log_message('error', 'Failed to assign to role: ' . json_encode($db->error()));
            
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Failed to assign permission to role'
            ]);
        }
        
        $db->transComplete();
        
        // Clear cache
        if (function_exists('cxp_update_cache')) {
            cxp_update_cache();
        }
        
        return $this->response->setJSON([
            'success' => true, 
            'msg' => 'Permission added successfully',
            'id' => $new_perm_id
        ]);
    } else {
        // Update existing permission
        check_permission('admin-edit-permission');
        
        if ($data['parent_id'] == $id) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Cannot set permission as its own parent'
            ]);
        }
        
        log_message('debug', 'Updating permission ID ' . $id . ': ' . json_encode($data));
        
        $result = $db->table('permissions')
            ->where('id', $id)
            ->update($data);
            
        if (!$result) {
            $db->transRollback();
            log_message('error', 'Failed to update permission: ' . json_encode($db->error()));
            
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Database update failed'
            ]);
        }
        
        $db->transComplete();
        
        // Clear cache
        if (function_exists('cxp_update_cache')) {
            cxp_update_cache();
        }
        
        return $this->response->setJSON([
            'success' => true, 
            'msg' => 'Permission updated successfully'
        ]);
    }
}

    public function delete()
    {
        check_permission('admin-del-permission');
        $id = (int) $this->request->getGet('id');

        if (!$id) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Invalid permission ID'
            ]);
        }
        
        $hasChildren = $this->db->table('permissions')
            ->where('parent_id', $id)
            ->countAllResults();
            
        if ($hasChildren > 0) {
            return $this->response->setJSON([
                'success' => false, 
                'msg' => 'Cannot delete permission with child permissions.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();
        
        $db->table('role_perms')->where('permID', $id)->delete();
        $db->table('user_perms')->where('permID', $id)->delete();
        $db->table('permissions')->where('id', $id)->delete();
        
        $db->transComplete();

        if (function_exists('cxp_update_cache')) {
            cxp_update_cache();
        }
        
        return $this->response->setJSON([
            'success' => true, 
            'msg' => 'Permission deleted successfully'
        ]);
    }

    protected function buildPermissionOptions($permissions, $parentId = 0, $level = 0, $excludeId = null, $selectedId = null)
    {
        $options = '';
        foreach ($permissions as $perm) {
            if ($perm->parent_id == $parentId) {
                if ($excludeId && $perm->id == $excludeId) {
                    continue;
                }
                $prefix = str_repeat(' - ', $level);
                if ($level > 0) {
                    $prefix = '└─ ' . $prefix;
                }
                $selected = ($selectedId == $perm->id) ? 'selected="selected"' : '';
                $options .= "<option value='{$perm->id}' {$selected}>{$prefix}{$perm->permName}</option>";
                $options .= $this->buildPermissionOptions($permissions, $perm->id, $level + 1, $excludeId, $selectedId);
            }
        }
        return $options;
    }
}   