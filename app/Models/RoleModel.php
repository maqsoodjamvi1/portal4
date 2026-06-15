<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['role_name_id', 'plan_id', 'issys'];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_date';
    protected $updatedField = 'updated_date';

    // Validation rules
    protected $validationRules = [
        'role_name_id' => 'required|integer',
        'plan_id' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'role_name_id' => [
            'required' => 'Role name is required',
            'integer' => 'Invalid role name'
        ]
    ];

    /**
     * Get role with details
     */
    public function getRoleWithDetails($id = null)
    {
        $builder = $this->db->table('roles r')
            ->select('r.*, rn.rolename as role_name, sp.plan_name')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'left')
            ->join('system_plans sp', 'sp.plan_id = r.plan_id', 'left');

        if ($id) {
            return $builder->where('r.id', $id)->get()->getRow();
        }

        return $builder->orderBy('r.id', 'DESC')->get()->getResult();
    }

    /**
     * Get all roles with their permissions
     */
    public function getAllRolesWithPermissions()
    {
        $roles = $this->getRoleWithDetails();

        foreach ($roles as $role) {
            $role->permissions = $this->getRolePermissions($role->id);
        }

        return $roles;
    }

    /**
     * Get permissions for a specific role
     */
    public function getRolePermissions($roleId)
    {
        return $this->db->table('role_perms rp')
            ->select('p.*, rp.value')
            ->join('permissions p', 'p.id = rp.permID')
            ->where('rp.roleID', $roleId)
            ->where('rp.value', 1)
            ->get()
            ->getResult();
    }

    /**
     * Get role by name
     */
    public function getRoleByName($roleNameId)
    {
        return $this->where('role_name_id', $roleNameId)->first();
    }

    /**
     * Check if role has permission
     */
    public function roleHasPermission($roleId, $permKey)
    {
        return $this->db->table('role_perms rp')
            ->join('permissions p', 'p.id = rp.permID')
            ->where('rp.roleID', $roleId)
            ->where('p.permKey', $permKey)
            ->where('rp.value', 1)
            ->countAllResults() > 0;
    }
}
