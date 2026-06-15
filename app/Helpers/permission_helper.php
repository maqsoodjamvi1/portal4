<?php

/**
 * RBAC utility helpers (non-enforcement).
 *
 * Permission enforcement lives in server_helper.php (check_permission, hasPermission).
 */

if (! function_exists('get_user_permissions')) {
    /**
     * @return array<string, object>
     */
    function get_user_permissions(int $user_id, ?int $campus_id = null): array
    {
        $acl   = new \App\Libraries\MemberAcl($user_id);
        $flat  = $acl->getPermArr('');
        $index = [];

        foreach ($flat as $key => $meta) {
            if (! ($meta['value'] ?? false)) {
                continue;
            }
            $index[$key] = (object) [
                'permKey'  => $key,
                'permName' => $meta['Name'] ?? $key,
            ];
        }

        return $index;
    }
}

if (! function_exists('get_permission_tree')) {
    function get_permission_tree(int $parent_id = 0, int $level = 0): array
    {
        $db = db_connect();

        $permissions = $db->table('permissions')
            ->where('parent_id', $parent_id)
            ->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        $tree = [];
        foreach ($permissions as $perm) {
            $perm->level    = $level;
            $perm->children = get_permission_tree((int) $perm->id, $level + 1);
            $tree[]         = $perm;
        }

        return $tree;
    }
}

if (! function_exists('get_permissions_list')) {
    function get_permissions_list(): array
    {
        $cache = \Config\Services::cache();
        $cached = $cache->get('permissions_list');
        if ($cached !== null) {
            return $cached;
        }

        $permissions = db_connect()->table('permissions')
            ->orderBy('sortid', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        $cache->save('permissions_list', $permissions, 300);

        return $permissions;
    }
}

if (! function_exists('clear_permission_cache')) {
    function clear_permission_cache(?int $user_id = null): void
    {
        $cache = \Config\Services::cache();

        if ($user_id) {
            (new \App\Libraries\MemberAcl($user_id))->clearUserCaches($user_id);
        }

        $cache->delete('permissions_list');
        $cache->delete('permissions_tree');
        \App\Libraries\MemberAcl::clearDictionaryCaches();

        if (function_exists('cxp_update_cache')) {
            cxp_update_cache();
        }
    }
}

if (! function_exists('permissions_list')) {
    function permissions_list(): array
    {
        return get_permissions_list();
    }
}

if (! function_exists('has_any_permission')) {
    /**
     * @param list<string> $permKeys
     */
    function has_any_permission(array $permKeys, ?int $user_id = null): bool
    {
        if ($user_id !== null) {
            $acl = new \App\Libraries\MemberAcl($user_id);
            foreach ($permKeys as $key) {
                if ($acl->hasPermission($key)) {
                    return true;
                }
            }

            return false;
        }

        return hasAnyPermission($permKeys);
    }
}

if (! function_exists('can')) {
    function can(string $permKey): bool
    {
        return hasPermission($permKey);
    }
}

if (! function_exists('cannot')) {
    function cannot(string $permKey): bool
    {
        return ! hasPermission($permKey);
    }
}
