<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleNameModel extends Model
{
    protected $table = 'role_name';
    protected $primaryKey = 'role_name_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = ['rolename', 'detail', 'parent_id'];

    protected $useTimestamps = false;

    /**
     * Get all role names
     */
    public function getAllRoleNames()
    {
        return $this->orderBy('rolename', 'ASC')->findAll();
    }

    /**
     * Get role name by ID
     */
    public function getRoleName($id)
    {
        return $this->find($id);
    }
}