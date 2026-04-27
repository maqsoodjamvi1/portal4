<?php

/**
 * Permission Helper
 * 
 * Helper functions for permission management
 * 
 * @author      Maqsood Jamvi
 * @copyright   Copyright (c) 2020~2099 timesoftsol.com
 * @email       maqsoodjamvi@gmail.com
 */

if (!function_exists('get_user_permissions')) {
    /**
     * Get all permissions for a user (from both user_perms and role_perms)
     * 
     * @param int $user_id User ID
     * @param int|null $campus_id Campus ID (optional)
     * @return array Array of permission objects
     */
    function get_user_permissions($user_id, $campus_id = null)
    {
        $db = \Config\Database::connect();
        $cache = \Config\Services::cache();
        
        // Try to get from cache first
        $cacheKey = "user_permissions_{$user_id}_{$campus_id}";
        $cached = $cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        // Get direct user permissions
        $userPerms = $db->table('user_perms')
            ->select('permissions.*')
            ->join('permissions', 'permissions.id = user_perms.permID')
            ->where('user_perms.userID', $user_id)
            ->where('user_perms.value', 1)
            ->get()
            ->getResult();
        
        // Get role-based permissions
        $rolePerms = $db->table('user_roles')
            ->select('permissions.*')
            ->join('role_perms', 'role_perms.roleID = user_roles.roleID')
            ->join('permissions', 'permissions.id = role_perms.permID')
            ->where('user_roles.userID', $user_id)
            ->where('role_perms.value', 1)
            ->get()
            ->getResult();
        
        // Merge and remove duplicates
        $allPermissions = array_merge($userPerms, $rolePerms);
        $uniquePermissions = [];
        
        foreach ($allPermissions as $perm) {
            if (!isset($uniquePermissions[$perm->permKey])) {
                $uniquePermissions[$perm->permKey] = $perm;
            }
        }
        
        // Cache for 5 minutes (300 seconds)
        $cache->save($cacheKey, $uniquePermissions, 300);
        
        return $uniquePermissions;
    }
}

if (!function_exists('check_permission')) {
    /**
     * Check if user has a specific permission
     * 
     * @param string $permKey Permission key (e.g., 'admin-permissions')
     * @param int|null $user_id User ID (uses session if null)
     * @return bool True if user has permission
     */
    function check_permission($permKey, $user_id = null)
    {
        // Skip permission check for CLI or if specifically allowed
        if (is_cli()) {
            return true;
        }
        
        // Check if permission check is disabled (for development)
        if (defined('DISABLE_PERMISSION_CHECK') && DISABLE_PERMISSION_CHECK === true) {
            return true;
        }
        
        $session = session();
        $user_id = $user_id ?: $session->get('user_id');
        
        if (!$user_id) {
            return false;
        }
        
        // Get user permissions
        $permissions = get_user_permissions($user_id);
        
        // Check exact permission match
        if (isset($permissions[$permKey])) {
            return true;
        }
        
        // Check for wildcard permissions
        foreach ($permissions as $perm) {
            // Full wildcard
            if ($perm->permKey === '*' || $perm->permKey === 'all' || $perm->permKey === 'super-admin') {
                return true;
            }
            
            // Module wildcards (e.g., 'admin-*')
            if (strpos($perm->permKey, '*') !== false) {
                $pattern = '/^' . str_replace('*', '.*', preg_quote($perm->permKey, '/')) . '$/';
                if (preg_match($pattern, $permKey)) {
                    return true;
                }
            }
            
            // Hierarchical permissions (e.g., 'admin.users' matches 'admin.users.view')
            if (strpos($perm->permKey, '.') !== false && strpos($permKey, '.') !== false) {
                if (strpos($permKey, $perm->permKey) === 0) {
                    return true;
                }
            }
        }
        
        return false;
    }
}

if (!function_exists('can')) {
    /**
     * Alias for check_permission (shorter syntax)
     * 
     * @param string $permKey Permission key
     * @return bool
     */
    function can($permKey)
    {
        return check_permission($permKey);
    }
}

if (!function_exists('cannot')) {
    /**
     * Check if user does NOT have a permission
     * 
     * @param string $permKey Permission key
     * @return bool
     */
    function cannot($permKey)
    {
        return !check_permission($permKey);
    }
}

if (!function_exists('get_permission_tree')) {
    /**
     * Get permission tree structure
     * 
     * @param int $parent_id Parent permission ID
     * @param int $level Current level (for indentation)
     * @return array Tree structure
     */
    function get_permission_tree($parent_id = 0, $level = 0)
    {
        $db = \Config\Database::connect();
        
        $permissions = $db->table('permissions')
            ->where('parent_id', $parent_id)
            ->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
            
        $tree = [];
        foreach ($permissions as $perm) {
            $perm->level = $level;
            $perm->children = get_permission_tree($perm->id, $level + 1);
            $tree[] = $perm;
        }
        
        return $tree;
    }
}

if (!function_exists('get_permissions_list')) {
    /**
     * Get all permissions as a flat list
     * 
     * @return array List of permissions
     */
    function get_permissions_list()
    {
        $db = \Config\Database::connect();
        $cache = \Config\Services::cache();
        
        $cached = $cache->get('permissions_list');
        if ($cached !== null) {
            return $cached;
        }
        
        $permissions = $db->table('permissions')
            ->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
            
        $cache->save('permissions_list', $permissions, 300);
        
        return $permissions;
    }
}

if (!function_exists('get_role_permissions')) {
    /**
     * Get permissions for a specific role
     * 
     * @param int $role_id Role ID
     * @return array Array of permission objects
     */
    function get_role_permissions($role_id)
    {
        $db = \Config\Database::connect();
        $cache = \Config\Services::cache();
        
        $cacheKey = "role_permissions_{$role_id}";
        $cached = $cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $permissions = $db->table('role_perms')
            ->select('permissions.*')
            ->join('permissions', 'permissions.id = role_perms.permID')
            ->where('role_perms.roleID', $role_id)
            ->where('role_perms.value', 1)
            ->orderBy('permissions.sortid', 'ASC')
            ->get()
            ->getResult();
            
        $cache->save($cacheKey, $permissions, 300);
        
        return $permissions;
    }
}

if (!function_exists('assign_permission_to_role')) {
    /**
     * Assign permission to role
     * 
     * @param int $role_id Role ID
     * @param int $perm_id Permission ID
     * @param int $value Permission value (1 = allow, 0 = deny)
     * @return bool Success status
     */
    function assign_permission_to_role($role_id, $perm_id, $value = 1)
    {
        $db = \Config\Database::connect();
        
        try {
            // Check if already exists
            $exists = $db->table('role_perms')
                ->where('roleID', $role_id)
                ->where('permID', $perm_id)
                ->get()
                ->getRow();
                
            if ($exists) {
                // Update existing
                $db->table('role_perms')
                    ->where('roleID', $role_id)
                    ->where('permID', $perm_id)
                    ->update(['value' => $value]);
            } else {
                // Insert new
                $db->table('role_perms')->insert([
                    'roleID' => $role_id,
                    'permID' => $perm_id,
                    'value' => $value,
                    'add_date' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Clear cache
            clear_permission_cache();
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Failed to assign permission: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('assign_permission_to_user')) {
    /**
     * Assign permission directly to user
     * 
     * @param int $user_id User ID
     * @param int $perm_id Permission ID
     * @param int $value Permission value (1 = allow, 0 = deny)
     * @return bool Success status
     */
    function assign_permission_to_user($user_id, $perm_id, $value = 1)
    {
        $db = \Config\Database::connect();
        
        try {
            // Check if already exists
            $exists = $db->table('user_perms')
                ->where('userID', $user_id)
                ->where('permID', $perm_id)
                ->get()
                ->getRow();
                
            if ($exists) {
                // Update existing
                $db->table('user_perms')
                    ->where('userID', $user_id)
                    ->where('permID', $perm_id)
                    ->update(['value' => $value]);
            } else {
                // Insert new
                $db->table('user_perms')->insert([
                    'userID' => $user_id,
                    'permID' => $perm_id,
                    'value' => $value,
                    'addDate' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Clear cache
            clear_permission_cache($user_id);
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Failed to assign permission: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('clear_permission_cache')) {
    /**
     * Clear permission cache
     * 
     * @param int|null $user_id Specific user ID or null for all
     */
    function clear_permission_cache($user_id = null)
    {
        $cache = \Config\Services::cache();
        
        if ($user_id) {
            // Clear specific user's cache
            $cache->delete("user_permissions_{$user_id}_");
            $cache->delete("user_permissions_{$user_id}_null");
        } else {
            // Clear all permission caches (use cache prefix)
            $cache->deleteMatching('user_permissions_*');
            $cache->delete('permissions_list');
            $cache->delete('permissions_tree');
            $cache->deleteMatching('role_permissions_*');
        }
        
        // Call existing update function if it exists
        if (function_exists('cxp_update_cache')) {
            cxp_update_cache();
        }
    }
}

if (!function_exists('permissions_list')) {
    /**
     * Get permissions list (compatible with existing code)
     * 
     * @return array List of permissions
     */
    function permissions_list()
    {
        return get_permissions_list();
    }
}

if (!function_exists('get_user_permissions_by_module')) {
    /**
     * Get permissions grouped by module
     * 
     * @param int $user_id User ID
     * @return array Permissions grouped by parent module
     */
    function get_user_permissions_by_module($user_id)
    {
        $permissions = get_user_permissions($user_id);
        $allPermissions = get_permissions_list();
        
        $grouped = [];
        
        foreach ($allPermissions as $perm) {
            if ($perm->parent_id == 0) {
                // This is a module/group
                $grouped[$perm->id] = [
                    'module' => $perm,
                    'permissions' => []
                ];
            }
        }
        
        foreach ($permissions as $perm) {
            if ($perm->parent_id > 0 && isset($grouped[$perm->parent_id])) {
                $grouped[$perm->parent_id]['permissions'][] = $perm;
            } elseif ($perm->parent_id == 0) {
                // This is a module itself
                if (!isset($grouped[$perm->id])) {
                    $grouped[$perm->id] = [
                        'module' => $perm,
                        'permissions' => []
                    ];
                }
            }
        }
        
        return $grouped;
    }
}

if (!function_exists('has_any_permission')) {
    /**
     * Check if user has any of the given permissions
     * 
     * @param array $permKeys Array of permission keys
     * @param int|null $user_id User ID
     * @return bool True if user has at least one permission
     */
    function has_any_permission($permKeys, $user_id = null)
    {
        foreach ($permKeys as $permKey) {
            if (check_permission($permKey, $user_id)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('has_all_permissions')) {
    /**
     * Check if user has all of the given permissions
     * 
     * @param array $permKeys Array of permission keys
     * @param int|null $user_id User ID
     * @return bool True if user has all permissions
     */
    function has_all_permissions($permKeys, $user_id = null)
    {
        foreach ($permKeys as $permKey) {
            if (!check_permission($permKey, $user_id)) {
                return false;
            }
        }
        return true;
    }
}