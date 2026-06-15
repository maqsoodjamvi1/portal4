<?php

/**
 * Role-name helpers (plan-aware, legacy user_roles compatible).
 *
 * Prefer canonical names from DefaultRolesSeeder / RolePermissionMap:
 * Super Admin, Director System, Director Campus, Principal,
 * Academic Coordinator, Teacher, Director Finance, Accountant
 */

if (! function_exists('roleCanonicalNames')) {
    /**
     * @return array<string, list<string>>
     */
    function roleCanonicalNames(): array
    {
        return [
            'Super Admin'          => ['Super Admin', 'Super Administrator', 'System Admin'],
            'CEO'                  => ['CEO', 'Chief Executive Officer'],
            'Director System'      => ['Director System', 'System Director'],
            'Director Campus'      => ['Director Campus', 'Campus Director', 'Campus director'],
            'Principal'            => ['Principal'],
            'Academic Coordinator' => ['Academic Coordinator', 'Academic Cordinator', 'Academic Co-ordinator'],
            'Teacher'              => ['Teacher', 'Faculty'],
            'Director Finance'     => ['Director Finance', 'Finance Director'],
            'Accountant'           => ['Accountant', 'Accountent'],
        ];
    }
}

if (! function_exists('getRolePlanId')) {
    /**
     * Canonical SaaS plan for roles/permissions (annual package only).
     */
    function getRolePlanId(): int
    {
        return getSystemPlanId();
    }
}

if (! function_exists('getSystemPlanId')) {
    function getSystemPlanId(): int
    {
        static $planId = null;
        if ($planId !== null) {
            return $planId;
        }

        $planId = (int) (config('Trial')->defaultPlanId ?? 3);

        return $planId > 0 ? $planId : 3;
    }
}

if (! function_exists('getAnnualInstallMonthCount')) {
    function getAnnualInstallMonthCount(): int
    {
        $months = (int) (config('Trial')->defaultInstallMonthCount ?? 12);

        return $months > 0 ? $months : 12;
    }
}

if (! function_exists('getAnnualInstallPlan')) {
    /**
     * Annual billing installment row from system_installment_plan.
     */
    function getAnnualInstallPlan(): ?object
    {
        static $row = null;
        if ($row !== null) {
            return $row ?: null;
        }

        $db     = db_connect();
        $months = getAnnualInstallMonthCount();

        $found = $db->table('system_installment_plan')
            ->where('month_count', $months)
            ->get()
            ->getRow();

        if ($found) {
            $row = $found;

            return $row;
        }

        $found = $db->table('system_installment_plan')
            ->orderBy('month_count', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        $row = $found ?: false;

        return $found;
    }
}

if (! function_exists('getCampusPlanId')) {
    function getCampusPlanId(?int $campusId = null): int
    {
        $campusId = $campusId ?? (int) (session()->get('member_campusid') ?? 0);
        if ($campusId <= 0) {
            return getRolePlanId();
        }

        static $cache = [];
        if (isset($cache[$campusId])) {
            return $cache[$campusId];
        }

        $row = db_connect()->table('campus_bills')
            ->select('plan_id')
            ->where('status', 1)
            ->where('campus_id', $campusId)
            ->orderBy('campus_expiry', 'DESC')
            ->get()
            ->getRow();

        $planId = (int) ($row->plan_id ?? 0);
        $cache[$campusId] = $planId > 0 ? $planId : getRolePlanId();

        return $cache[$campusId];
    }
}

if (! function_exists('getRoleNameId')) {
    /**
     * Resolve role_name.role_name_id by canonical role label (cached per request).
     */
    function getRoleNameId(string $canonicalName): int
    {
        static $cache = [];
        $key = strtolower(trim($canonicalName));
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $aliases = roleCanonicalNames();
        $search  = $aliases[$canonicalName] ?? [$canonicalName];

        $db  = db_connect();
        $row = $db->table('role_name')
            ->select('role_name_id')
            ->whereIn('rolename', $search)
            ->orderBy('role_name_id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        $cache[$key] = (int) ($row->role_name_id ?? 0);

        return $cache[$key];
    }
}

if (! function_exists('getTeacherRoleNameId')) {
    function getTeacherRoleNameId(): int
    {
        static $id = null;
        if ($id === null) {
            $id = getRoleNameId('Teacher');
        }

        return (int) $id;
    }
}

if (! function_exists('userHasRoleName')) {
    /**
     * True when user has the given canonical role on the campus plan.
     */
    function userHasRoleName(string $canonicalName, ?int $userId = null, ?int $planId = null): bool
    {
        $roleNameId = getRoleNameId($canonicalName);
        if ($roleNameId <= 0) {
            return false;
        }

        return userHasRoleNameId($roleNameId, $userId, $planId);
    }
}

if (! function_exists('userHasRoleNameId')) {
    function userHasRoleNameId(int $roleNameId, ?int $userId = null, ?int $planId = null): bool
    {
        $userId = $userId ?? (int) (session()->get('member_userid') ?? 0);
        if ($userId <= 0 || $roleNameId <= 0) {
            return false;
        }

        if ($planId === null) {
            $planId = getRolePlanId();
        }

        $db = db_connect();

        $primary = $db->table('user_roles ur')
            ->join('roles r', 'r.id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . (int) $planId : ''), 'inner')
            ->where('ur.userID', $userId)
            ->where('r.role_name_id', $roleNameId)
            ->countAllResults();

        if ($primary > 0) {
            return true;
        }

        return $db->table('user_roles ur')
            ->join('roles r', 'r.role_name_id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . (int) $planId : ''), 'inner')
            ->where('ur.userID', $userId)
            ->where('r.role_name_id', $roleNameId)
            ->countAllResults() > 0;
    }
}

if (! function_exists('currentUserRoleNames')) {
    /**
     * Canonical role labels for all roles assigned to a user (plan-aware).
     *
     * @return list<string>
     */
    function currentUserRoleNames(?int $userId = null): array
    {
        $userId = $userId ?? (int) (session()->get('member_userid') ?? 0);
        if ($userId <= 0) {
            return [];
        }

        $acl     = new \App\Libraries\MemberAcl($userId);
        $roleIds = $acl->getUserRoles();
        if ($roleIds === []) {
            return [];
        }

        $rows = db_connect()
            ->table('roles r')
            ->select('rn.rolename')
            ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
            ->whereIn('r.id', $roleIds)
            ->get()
            ->getResultArray();

        $names = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['rolename'] ?? ''));
            if ($name !== '') {
                $names[$name] = $name;
            }
        }

        return array_values($names);
    }
}

if (! function_exists('userHasAnyRoleNameLike')) {
    function userHasAnyRoleNameLike(string $needle, ?int $userId = null): bool
    {
        $needle = strtolower(trim($needle));
        if ($needle === '') {
            return false;
        }

        foreach (currentUserRoleNames($userId) as $name) {
            if (strpos(strtolower($name), $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('isCurrentUserTeacher')) {
    function isCurrentUserTeacher(?int $userId = null): bool
    {
        return userHasRoleName('Teacher', $userId);
    }
}

if (! function_exists('isSelfProfile')) {
    function isSelfProfile(int $targetUserId): bool
    {
        $sessionUserId = (int) (session()->get('member_userid') ?? 0);

        return $targetUserId > 0 && $sessionUserId > 0 && $targetUserId === $sessionUserId;
    }
}

if (! function_exists('canViewEmployeeProfile')) {
    function canViewEmployeeProfile(int $targetUserId): bool
    {
        if ($targetUserId <= 0) {
            return false;
        }

        if (isSelfProfile($targetUserId)) {
            return true;
        }

        helper('permission');

        return function_exists('hasPermission') && hasPermission('admin-users');
    }
}

if (! function_exists('canFullEditEmployeeProfile')) {
    function canFullEditEmployeeProfile(): bool
    {
        helper('permission');

        return function_exists('hasPermission') && hasPermission('admin-edit-user');
    }
}

if (! function_exists('canSelfEditLimitedProfile')) {
    function canSelfEditLimitedProfile(): bool
    {
        return (int) (session()->get('member_userid') ?? 0) > 0;
    }
}

if (! function_exists('getTeacherUserIds')) {
    /**
     * User IDs with Teacher role (optionally limited to a campus).
     *
     * @return list<int>
     */
    function getTeacherUserIds(?int $planId = null, ?int $campusId = null): array
    {
        $teacherRoleNameId = getTeacherRoleNameId();
        if ($teacherRoleNameId <= 0) {
            return [];
        }

        if ($planId === null) {
            $planId = getRolePlanId();
        }

        $db = db_connect();
        $ids = [];

        $primary = $db->table('user_roles ur')
            ->distinct()
            ->select('ur.userID')
            ->join('roles r', 'r.id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . (int) $planId : ''), 'inner')
            ->where('r.role_name_id', $teacherRoleNameId)
            ->get()
            ->getResultArray();

        $legacy = $db->table('user_roles ur')
            ->distinct()
            ->select('ur.userID')
            ->join('roles r', 'r.role_name_id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . (int) $planId : ''), 'inner')
            ->where('r.role_name_id', $teacherRoleNameId)
            ->get()
            ->getResultArray();

        foreach (array_merge($primary, $legacy) as $row) {
            $uid = (int) ($row['userID'] ?? 0);
            if ($uid > 0) {
                $ids[$uid] = $uid;
            }
        }

        if ($campusId !== null && $campusId > 0 && $ids !== []) {
            $filtered = $db->table('users')
                ->select('id')
                ->where('campus_id', $campusId)
                ->whereIn('id', array_values($ids))
                ->get()
                ->getResultArray();

            $ids = [];
            foreach ($filtered as $row) {
                $ids[(int) $row['id']] = (int) $row['id'];
            }
        }

        return array_values($ids);
    }
}

if (! function_exists('normalizeRoleIdsForPlan')) {
    /**
     * Map stored role IDs (roles.id or legacy role_name_id) to roles.id for a plan.
     *
     * @param list<int> $roleIds
     * @return list<int>
     */
    function normalizeRoleIdsForPlan(array $roleIds, int $planId): array
    {
        $planId = (int) $planId;
        if ($planId <= 0 || $roleIds === []) {
            return [];
        }

        $db = db_connect();
        $normalized = [];

        foreach ($roleIds as $rawId) {
            $rawId = (int) $rawId;
            if ($rawId <= 0) {
                continue;
            }

            $roleNameId = 0;

            $byId = $db->table('roles')
                ->select('role_name_id, plan_id')
                ->where('id', $rawId)
                ->limit(1)
                ->get()
                ->getRow();

            if ($byId) {
                $roleNameId = (int) ($byId->role_name_id ?? 0);
            } else {
                $roleNameId = $rawId;
            }

            if ($roleNameId <= 0) {
                continue;
            }

            $resolved = $db->table('roles')
                ->select('id')
                ->where('role_name_id', $roleNameId)
                ->where('plan_id', $planId)
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()
                ->getRow();

            $resolvedId = (int) ($resolved->id ?? 0);
            if ($resolvedId > 0) {
                $normalized[$resolvedId] = $resolvedId;
            }
        }

        return array_values($normalized);
    }
}

if (! function_exists('resolveRoleIdForPlan')) {
    /**
     * roles.id for a canonical role on a plan (first match).
     */
    function resolveRoleIdForPlan(string $canonicalName, ?int $planId = null): int
    {
        $roleNameId = getRoleNameId($canonicalName);
        if ($roleNameId <= 0) {
            return 0;
        }

        if ($planId === null) {
            $planId = getRolePlanId();
        }

        $row = db_connect()->table('roles')
            ->select('id')
            ->where('role_name_id', $roleNameId)
            ->where('plan_id', (int) $planId)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        return (int) ($row->id ?? 0);
    }
}

if (! function_exists('resolveSuperAdminRoleId')) {
    /**
     * roles.id for Super Admin (or Director System fallback) on the current plan.
     */
    function resolveSuperAdminRoleId(?int $planId = null): int
    {
        foreach (['Super Admin', 'Director System'] as $name) {
            $id = resolveRoleIdForPlan($name, $planId);
            if ($id > 0) {
                return $id;
            }
        }

        return 0;
    }
}

if (! function_exists('roleClassSections')) {
    /**
     * Class sections assigned via role_classes (RBAC scoping).
     *
     * @return array<int, array<string, mixed>>
     */
    function roleClassSections(?int $userId = null): array
    {
        $userId = $userId ?? (int) (session()->get('member_userid') ?? 0);
        if ($userId <= 0) {
            return [];
        }

        static $cache = [];
        if (isset($cache[$userId])) {
            return $cache[$userId];
        }

        $acl  = new \App\Libraries\MemberAcl($userId);
        $rows = $acl->getUserClassSections($userId);

        if ($rows === []) {
            $cache[$userId] = [];
            return [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            $id = (int) ($row['cls_sec_id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $normalized[$id] = [
                'cls_sec_id'       => $id,
                'section_id'       => (int) ($row['section_id'] ?? 0),
                'class_id'         => (int) ($row['class_id'] ?? 0),
                'class_name'       => (string) ($row['class_name'] ?? ''),
                'section_name'     => (string) ($row['section_name'] ?? ''),
                'sectionclassname' => (string) ($row['sectionclassname'] ?? ''),
            ];
        }

        $cache[$userId] = array_values($normalized);

        return $cache[$userId];
    }
}

if (! function_exists('userIsSuperAdmin')) {
    function userIsSuperAdmin(?int $userId = null): bool
    {
        return userHasRoleName('Super Admin', $userId);
    }
}

if (! function_exists('userCanAccessDirectorQuizzesSubmenu')) {
    /**
     * Quizzes list only under the Quizzes menu (Director System / Campus; not QB/vocab setup).
     */
    function userCanAccessDirectorQuizzesSubmenu(?int $userId = null): bool
    {
        if (userIsSuperAdmin($userId)) {
            return true;
        }

        foreach (['CEO', 'Director System', 'Director Campus'] as $roleName) {
            if (userHasRoleName($roleName, $userId)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('quizzesMenuItemVisible')) {
    /**
     * Sidebar visibility for items under the Quizzes menu group.
     *
     * @param array{key?: string, perms?: list<string>, super_admin_only?: bool, director_quizzes_menu?: bool} $item
     */
    function quizzesMenuItemVisible(array $item, callable $canAny, ?int $userId = null): bool
    {
        $userId      = $userId ?? (int) (session()->get('member_userid') ?? 0);
        $menuKey     = trim((string) ($item['key'] ?? ''));
        $isSuper     = userIsSuperAdmin($userId);

        if (! empty($item['super_admin_only']) && $isSuper) {
            return true;
        }

        $menuGranted = $menuKey !== ''
            && \App\Libraries\RoleMenuAccess::isMenuGrantedForUser($menuKey, $userId);

        $perms      = $item['perms'] ?? [];
        $hasPerms   = $perms !== [] && $canAny($perms);
        $permDenied = $perms !== [] && ! $hasPerms && ! $menuGranted;

        if ($permDenied) {
            return false;
        }

        if (! empty($item['super_admin_only'])) {
            if ($menuGranted || $hasPerms) {
                return true;
            }

            if (userCanAccessDirectorQuizzesSubmenu($userId)
                && \App\Libraries\RoleMenuAccess::isMenuGrantedByAnyRole('quizzes', $userId)) {
                return true;
            }

            return false;
        }

        if (! empty($item['director_quizzes_menu'])) {
            if (userCanAccessDirectorQuizzesSubmenu($userId)) {
                return true;
            }

            return $menuGranted;
        }

        return $perms === [] || $hasPerms || $menuGranted;
    }
}

if (! function_exists('scopedClassSections')) {
    /**
     * Teacher-scoped sections, role-scoped sections, or all campus sections.
     *
     * @return array<int, array<string, mixed>>
     */
    function scopedClassSections(?int $userId = null): array
    {
        if (isCurrentUserTeacher($userId)) {
            return teacherSubjectSections($userId);
        }

        $roleScoped = roleClassSections($userId);
        if ($roleScoped !== []) {
            return $roleScoped;
        }

        return userClassSections($userId);
    }
}
