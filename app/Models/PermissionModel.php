<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['permKey', 'permName', 'parent_id', 'lft', 'rgt', 'root_id', 'sortid', 'issys', 'permType', 'rel_id'];

    protected $useTimestamps = false;

    /**
     * Get all permissions as tree structure
     */
    public function getPermissionTree($parentId = 0, $level = 0)
    {
        $permissions = $this->where('parent_id', $parentId)
            ->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $tree = [];
        foreach ($permissions as $perm) {
            $perm->level = $level;
            $perm->children = $this->getPermissionTree($perm->id, $level + 1);
            $tree[] = $perm;
        }

        return $tree;
    }

    /**
     * Get all permissions as flat list
     */
    public function getAllPermissions()
    {
        return $this->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Get permissions with parent info
     */
    public function getPermissionsWithParent()
    {
        return $this->select('p.*, pp.permName as parent_name')
            ->join('permissions pp', 'pp.id = p.parent_id', 'left')
            ->orderBy('p.sortid', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->findAll();
    }

    /**
     * Build permission options for dropdown
     */
    public function getPermissionOptions($excludeId = null, $selectedId = null)
    {
        $permissions = $this->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
        
        return $this->buildOptionsRecursive($permissions, 0, 0, $excludeId, $selectedId);
    }

    private function buildOptionsRecursive($permissions, $parentId = 0, $level = 0, $excludeId = null, $selectedId = null)
    {
        $options = '';
        foreach ($permissions as $perm) {
            if ($perm->parent_id == $parentId) {
                if ($excludeId && $perm->id == $excludeId) {
                    continue;
                }
                $prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
                if ($level > 0) {
                    $prefix = '+- ' . $prefix;
                }
                $selected = ($selectedId == $perm->id) ? 'selected="selected"' : '';
                $options .= "<option value='{$perm->id}' {$selected}>{$prefix}{$perm->permName}</option>";
                $options .= $this->buildOptionsRecursive($permissions, $perm->id, $level + 1, $excludeId, $selectedId);
            }
        }
        return $options;
    }
}