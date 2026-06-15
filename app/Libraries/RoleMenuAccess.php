<?php

namespace App\Libraries;

/**
 * Per-role sidebar menu visibility (independent of shared permission keys).
 */
class RoleMenuAccess
{
    private static ?bool $tableExists = null;

    public static function tableExists(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }

        $db = \Config\Database::connect();
        if ($db->tableExists('role_menu_access')) {
            self::$tableExists = true;

            return true;
        }

        self::ensureTable();
        self::$tableExists = $db->tableExists('role_menu_access');

        return self::$tableExists;
    }

    private static function ensureTable(): void
    {
        $db    = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if ($db->tableExists('role_menu_access')) {
            return;
        }

        $forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'menu_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'allowed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'updated_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $forge->addKey('id', true);
        $forge->addUniqueKey(['role_id', 'menu_key']);
        $forge->addKey('role_id');
        $forge->createTable('role_menu_access', true);
    }

    /**
     * @return array<string, int> menu_key => 0|1
     */
    public static function getMapForRole(int $roleId): array
    {
        if ($roleId <= 0 || ! self::tableExists()) {
            return [];
        }

        $map  = [];
        $rows = \Config\Database::connect()
            ->table('role_menu_access')
            ->where('role_id', $roleId)
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $key = trim((string) ($row->menu_key ?? ''));
            if ($key !== '') {
                $map[$key] = (int) ($row->allowed ?? 0) === 1 ? 1 : 0;
            }
        }

        return $map;
    }

    /**
     * @param array<string, mixed> $menuAccess menu_key => 0|1|'0'|'1'|bool
     */
    public static function saveForRole(int $roleId, array $menuAccess, bool $enforceGuard = true): bool
    {
        if ($roleId <= 0 || ! self::tableExists()) {
            return false;
        }

        $db   = \Config\Database::connect();
        $date = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($menuAccess as $menuKey => $value) {
            $menuKey = trim((string) $menuKey);
            if ($menuKey === '') {
                continue;
            }

            $allowed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($allowed === null) {
                $allowed = ((string) $value === '1');
            }

            $rows[] = [
                'role_id'      => $roleId,
                'menu_key'     => $menuKey,
                'allowed'      => $allowed ? 1 : 0,
                'updated_date' => $date,
            ];
        }

        if ($rows === []) {
            return false;
        }

        if ($enforceGuard) {
            $itemCount    = count(MenuPermissionCatalog::getItemIndex());
            $sectionCount = count(MenuPermissionCatalog::getSectionKeys());
            $expectedMin  = (int) ceil(($itemCount + $sectionCount) * 0.8);
            if ($expectedMin > 0 && count($rows) < $expectedMin) {
                return false;
            }
        }

        $db->transStart();

        foreach ($rows as $row) {
            $existing = $db->table('role_menu_access')
                ->where('role_id', $roleId)
                ->where('menu_key', $row['menu_key'])
                ->get()
                ->getRow();

            if ($existing) {
                $db->table('role_menu_access')
                    ->where('id', (int) $existing->id)
                    ->update([
                        'allowed'      => $row['allowed'],
                        'updated_date' => $row['updated_date'],
                    ]);
            } else {
                $db->table('role_menu_access')->insert($row);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return false;
        }

        self::clearCacheForRole($roleId);

        return true;
    }

    public static function isAllowedForUser(string $menuKey, ?int $userId = null): bool
    {
        $menuKey = trim($menuKey);
        if ($menuKey === '' || ! self::tableExists()) {
            return true;
        }

        $map = self::getUserMenuAccessMap($userId);
        if ($map === []) {
            return true;
        }

        if (array_key_exists($menuKey, $map)) {
            if ((int) $map[$menuKey] === 1) {
                return true;
            }

            if (self::isQuizzesSectionMenuKey($menuKey) && self::isMenuGrantedByAnyRole($menuKey, $userId)) {
                return true;
            }

            return false;
        }

        // No override on this key for this user — use permission keys on the menu item.
        return true;
    }

    /**
     * True when any assigned role has an explicit allowed=1 override for this menu key.
     */
    public static function isExplicitlyAllowedForUser(string $menuKey, ?int $userId = null): bool
    {
        $menuKey = trim($menuKey);
        if ($menuKey === '' || ! self::tableExists()) {
            return false;
        }

        $map = self::getUserMenuAccessMap($userId);
        if ($map === []) {
            return false;
        }

        return array_key_exists($menuKey, $map) && (int) $map[$menuKey] === 1;
    }

    /**
     * Menu granted via role menu editor (any assigned role may grant Quizzes items).
     */
    public static function isMenuGrantedForUser(string $menuKey, ?int $userId = null): bool
    {
        return self::isMenuGrantedByAnyRole($menuKey, $userId);
    }

    /**
     * True when any assigned role grants this menu key (per-role OR, not union deny).
     */
    public static function isMenuGrantedByAnyRole(string $menuKey, ?int $userId = null): bool
    {
        $menuKey = trim($menuKey);
        if ($menuKey === '' || ! self::tableExists()) {
            return false;
        }

        foreach (self::resolveUserRoleIds($userId) as $roleId) {
            $roleMap = self::getMapForRole($roleId);
            if ($roleMap === []) {
                continue;
            }

            if (isset($roleMap[$menuKey]) && (int) $roleMap[$menuKey] === 1) {
                return true;
            }

            if (self::isQuizzesSectionMenuKey($menuKey)
                && isset($roleMap['quizzes'])
                && (int) $roleMap['quizzes'] === 1
                && (! isset($roleMap[$menuKey]) || (int) $roleMap[$menuKey] !== 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * All plan-normalized role ids for a user (CEO + Teacher, etc.).
     *
     * @return list<int>
     */
    public static function resolveUserRoleIds(?int $userId = null): array
    {
        $userId = $userId ?? (int) (session()->get('member_userid') ?? 0);
        if ($userId <= 0) {
            return [];
        }

        helper('role');
        $planId = getRolePlanId();
        $acl    = new MemberAcl($userId);
        $ids    = $acl->getUserRoles();

        $rawRows = \Config\Database::connect()
            ->table('user_roles')
            ->select('roleID')
            ->where('userID', $userId)
            ->get()
            ->getResultArray();

        $rawIds = [];
        foreach ($rawRows as $row) {
            $rid = (int) ($row['roleID'] ?? 0);
            if ($rid > 0) {
                $rawIds[] = $rid;
            }
        }

        if ($rawIds !== []) {
            $ids = array_values(array_unique(array_merge(
                $ids,
                normalizeRoleIdsForPlan($rawIds, $planId)
            )));
        }

        return array_values(array_filter($ids, static fn (int $id): bool => $id > 0));
    }

    public static function isQuizzesSectionMenuKey(string $menuKey): bool
    {
        if ($menuKey === 'quizzes') {
            return true;
        }

        foreach (['question-bank.', 'question-paper.', 'vocabulary-bank.', 'vocab-bank.', 'quizzes.'] as $prefix) {
            if (str_starts_with($menuKey, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Union menu overrides for a user (cached).
     *
     * @return array<string, int> menu_key => 0|1
     */
    public static function getUserMenuAccessMap(?int $userId = null): array
    {
        if (! self::tableExists()) {
            return [];
        }

        $userId = $userId ?? (int) (session()->get('member_userid') ?? 0);
        if ($userId <= 0) {
            return [];
        }

        $cacheKey = 'role_menu_access_user_' . $userId;
        $cache    = \Config\Services::cache();
        try {
            $map = $cache->get($cacheKey);
        } catch (\Throwable $e) {
            $cache->delete($cacheKey);
            $map = null;
        }

        if (! is_array($map)) {
            $map = self::buildUserMenuAccessMap($userId);
            $cache->save($cacheKey, $map, 300);
        }

        return $map;
    }

    public static function clearCacheForUser(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        \Config\Services::cache()->delete('role_menu_access_user_' . $userId);
    }

    /**
     * Union menu overrides across all assigned roles.
     * - Any role with allowed=1 → visible (subject to permission keys).
     * - Deny only when every assigned role has an explicit allowed=0 override.
     * - If only some roles define overrides and none allow, fall through (not in map).
     *
     * @return array<string, int> menu_key => 0|1
     */
    private static function buildUserMenuAccessMap(int $userId): array
    {
        $roleIds = self::resolveUserRoleIds($userId);
        if ($roleIds === []) {
            return [];
        }

        $roleCount = count($roleIds);

        $rows = \Config\Database::connect()
            ->table('role_menu_access')
            ->whereIn('role_id', $roleIds)
            ->get()
            ->getResult();

        $byKey = [];
        foreach ($rows as $row) {
            $key = trim((string) ($row->menu_key ?? ''));
            if ($key === '') {
                continue;
            }

            $byKey[$key][] = (int) ($row->allowed ?? 0) === 1 ? 1 : 0;
        }

        $map = [];
        foreach ($byKey as $key => $values) {
            if (in_array(1, $values, true)) {
                $map[$key] = 1;
                continue;
            }

            if (count($values) >= $roleCount) {
                $map[$key] = 0;
            }
        }

        return $map;
    }

    public static function clearCacheForRole(int $roleId): void
    {
        if ($roleId <= 0 || ! self::tableExists()) {
            return;
        }

        $db      = \Config\Database::connect();
        $userIds = $db->table('user_roles')
            ->select('userID')
            ->where('roleID', $roleId)
            ->get()
            ->getResultArray();

        $cache = \Config\Services::cache();
        foreach ($userIds as $row) {
            $uid = (int) ($row['userID'] ?? 0);
            if ($uid > 0) {
                $cache->delete('role_menu_access_user_' . $uid);
            }
        }

        $legacyUserIds = $db->table('user_roles ur')
            ->select('ur.userID')
            ->join('roles r', 'r.role_name_id = ur.roleID')
            ->where('r.id', $roleId)
            ->get()
            ->getResultArray();

        foreach ($legacyUserIds as $row) {
            $uid = (int) ($row['userID'] ?? 0);
            if ($uid > 0) {
                $cache->delete('role_menu_access_user_' . $uid);
            }
        }
    }
}
