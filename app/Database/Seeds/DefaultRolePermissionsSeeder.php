<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;
use Config\RolePermissionMap;

/**
 * Maps default permissions to each standard role (per plan).
 *
 * Run after DefaultRolesSeeder:
 *   php spark db:seed DefaultRolePermissionsSeeder
 *
 * Merge mode (default): grants missing permissions; does not revoke manual changes.
 * Set REPLACE_EXISTING=true below to wipe and rebuild role_perms for seeded roles only.
 */
class DefaultRolePermissionsSeeder extends Seeder
{
    /** When true, deletes existing role_perms for each seeded role before applying defaults. */
    private const REPLACE_EXISTING = false;

    public function run(): void
    {
        foreach (['role_name', 'roles', 'permissions', 'role_perms'] as $table) {
            if (! $this->db->tableExists($table)) {
                throw new \RuntimeException("Table {$table} does not exist. Run migrations and DefaultRolesSeeder first.");
            }
        }

        $permIndex = $this->loadPermissionIndex();
        if ($permIndex === []) {
            $this->log('No permissions found in database — seed permissions first (Admin → Permissions).');
            return;
        }

        $planIds = $this->loadPlanIds();
        if ($planIds === []) {
            $this->log('No system_plans found — cannot attach permissions to plan roles.');
            return;
        }

        $roleNameIds = $this->loadRoleNameIds();
        $missingRoles = array_diff(RolePermissionMap::$canonicalRoleNames, array_keys($roleNameIds));
        if ($missingRoles !== []) {
            $this->log('Missing role_name rows (run DefaultRolesSeeder first): ' . implode(', ', $missingRoles));
        }

        $totals = [
            'roles_processed' => 0,
            'allows_upserted' => 0,
            'skipped_unknown' => 0,
            'missing_role_row' => 0,
        ];

        foreach ($planIds as $planId) {
            foreach (RolePermissionMap::$roles as $roleName => $roleConfig) {
                $roleNameId = (int) ($roleNameIds[$roleName] ?? 0);
                if ($roleNameId <= 0) {
                    continue;
                }

                $roleRow = $this->db->table('roles')
                    ->where('role_name_id', $roleNameId)
                    ->where('plan_id', $planId)
                    ->limit(1)
                    ->get()
                    ->getRow();

                if (! $roleRow) {
                    $totals['missing_role_row']++;
                    continue;
                }

                $roleId = (int) $roleRow->id;
                $allowedKeys = $this->resolveAllowedKeys($roleConfig, $permIndex);
                $allowedIds  = $this->keysToIds($allowedKeys, $permIndex, $totals);

                if (self::REPLACE_EXISTING) {
                    $this->db->table('role_perms')->where('roleID', $roleId)->delete();
                }

                $upserted = $this->grantPermissions($roleId, $allowedIds);
                $totals['roles_processed']++;
                $totals['allows_upserted'] += $upserted;

                $this->log(sprintf(
                    'Plan #%d · %s (role #%d): %d permission(s)',
                    $planId,
                    $roleName,
                    $roleId,
                    count($allowedIds)
                ));
            }
        }

        $this->clearPermissionCaches();

        $this->log('');
        $this->log('Default role permissions seeded.');
        $this->log('Roles processed: ' . $totals['roles_processed']);
        $this->log('Allow grants upserted: ' . $totals['allows_upserted']);
        if ($totals['missing_role_row'] > 0) {
            $this->log('Skipped (no roles row for plan): ' . $totals['missing_role_row']);
        }
        if ($totals['skipped_unknown'] > 0) {
            $this->log('Config keys not in DB (ignored): ' . $totals['skipped_unknown']);
        }
    }

    /**
     * @return array<string, int> permKey (lowercase) => id
     */
    private function loadPermissionIndex(): array
    {
        $rows = $this->db->table('permissions')
            ->select('id, permKey')
            ->get()
            ->getResultArray();

        $index = [];
        foreach ($rows as $row) {
            $key = strtolower(trim((string) ($row['permKey'] ?? '')));
            if ($key !== '') {
                $index[$key] = (int) $row['id'];
            }
        }

        return $index;
    }

    /** @return array<string, int> */
    private function loadRoleNameIds(): array
    {
        $rows = $this->db->table('role_name')
            ->select('role_name_id, rolename')
            ->whereIn('rolename', RolePermissionMap::$canonicalRoleNames)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['rolename']] = (int) $row['role_name_id'];
        }

        return $map;
    }

    /** @return list<int> */
    private function loadPlanIds(): array
    {
        $planId = (int) (config('Trial')->defaultPlanId ?? 3);

        return $planId > 0 ? [$planId] : [];
    }

    /**
     * @param array<string, int> $permIndex
     * @return list<string>
     */
    private function resolveAllowedKeys(array $roleConfig, array $permIndex): array
    {
        $allowed = [];

        if (! empty($roleConfig['all'])) {
            $allowed = array_keys($permIndex);
        } else {
            foreach ($roleConfig['modules'] ?? [] as $moduleKey) {
                $allowed = array_merge($allowed, $this->expandModule($moduleKey, $permIndex));
            }
            foreach ($roleConfig['include_keys'] ?? [] as $key) {
                $allowed[] = strtolower(trim($key));
            }
        }

        $allowed = array_values(array_unique(array_filter($allowed)));

        $excludeKeys = [];
        foreach ($roleConfig['exclude_modules'] ?? [] as $moduleKey) {
            $excludeKeys = array_merge($excludeKeys, $this->expandModule($moduleKey, $permIndex));
        }
        foreach ($roleConfig['exclude_keys'] ?? [] as $key) {
            $excludeKeys[] = strtolower(trim($key));
        }

        $excludePrefixes = $roleConfig['exclude_prefixes'] ?? [];
        if ($excludePrefixes !== []) {
            foreach (array_keys($permIndex) as $permKey) {
                foreach ($excludePrefixes as $prefix) {
                    if (str_starts_with($permKey, strtolower($prefix))) {
                        $excludeKeys[] = $permKey;
                        break;
                    }
                }
            }
        }

        $excludeKeys = array_flip(array_unique($excludeKeys));

        return array_values(array_filter(
            $allowed,
            static fn (string $key): bool => ! isset($excludeKeys[$key])
        ));
    }

    /**
     * @param array<string, int> $permIndex
     * @return list<string>
     */
    private function expandModule(string $moduleKey, array $permIndex): array
    {
        $module = RolePermissionMap::$modules[$moduleKey] ?? null;
        if ($module === null) {
            $this->log('Unknown module in map: ' . $moduleKey);
            return [];
        }

        $matched = [];

        foreach ($module['keys'] ?? [] as $key) {
            $normalized = strtolower(trim($key));
            if ($normalized !== '') {
                $matched[] = $normalized;
            }
        }

        foreach ($module['prefixes'] ?? [] as $prefix) {
            $prefix = strtolower($prefix);
            foreach (array_keys($permIndex) as $permKey) {
                if (str_starts_with($permKey, $prefix)) {
                    $matched[] = $permKey;
                }
            }
        }

        return array_values(array_unique($matched));
    }

    /**
     * @param list<string> $keys
     * @param array<string, int> $permIndex
     * @return list<int>
     */
    private function keysToIds(array $keys, array $permIndex, array &$totals): array
    {
        $ids = [];
        foreach ($keys as $key) {
            if (isset($permIndex[$key])) {
                $ids[] = $permIndex[$key];
                continue;
            }
            $totals['skipped_unknown']++;
        }

        return array_values(array_unique($ids));
    }

    /** @param list<int> $permIds */
    private function grantPermissions(int $roleId, array $permIds): int
    {
        if ($permIds === []) {
            return 0;
        }

        $existing = $this->db->table('role_perms')
            ->select('permID, value')
            ->where('roleID', $roleId)
            ->whereIn('permID', $permIds)
            ->get()
            ->getResultArray();

        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[(int) $row['permID']] = (string) $row['value'];
        }

        $now = date('Y-m-d H:i:s');
        $upserted = 0;

        foreach ($permIds as $permId) {
            if (isset($existingMap[$permId]) && $existingMap[$permId] === '1') {
                continue;
            }

            if (isset($existingMap[$permId])) {
                $this->db->table('role_perms')
                    ->where('roleID', $roleId)
                    ->where('permID', $permId)
                    ->update(['value' => 1]);
            } else {
                $this->db->table('role_perms')->insert([
                    'roleID'   => $roleId,
                    'permID'   => $permId,
                    'value'    => 1,
                    'add_date' => $now,
                ]);
            }

            $upserted++;
        }

        return $upserted;
    }

    private function clearPermissionCaches(): void
    {
        if (function_exists('cxp_update_cache')) {
            cxp_update_cache();
        }

        \App\Libraries\MemberAcl::clearDictionaryCaches();
    }

    private function log(string $message): void
    {
        if (is_cli()) {
            CLI::write($message);
            return;
        }
        log_message('info', '[DefaultRolePermissionsSeeder] ' . $message);
    }
}
