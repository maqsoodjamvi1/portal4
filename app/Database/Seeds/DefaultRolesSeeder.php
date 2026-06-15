<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Seeds the standard school-management role hierarchy.
 *
 * Run: php spark db:seed DefaultRolesSeeder
 *
 * Safe to run multiple times — inserts missing rows and updates parent_id / detail.
 */
class DefaultRolesSeeder extends Seeder
{
    /** @var array<string, array{detail: string, parent: string|null, issys: bool, aliases?: list<string>}> */
    private array $roleCatalog = [
        'Super Admin' => [
            'detail' => 'Full system access across all campuses and modules.',
            'parent' => null,
            'issys'  => true,
            'aliases' => ['Super Administrator', 'System Admin'],
        ],
        'Director System' => [
            'detail' => 'Multi-campus system configuration and oversight.',
            'parent' => 'Super Admin',
            'issys'  => true,
            'aliases' => ['System Director'],
        ],
        'Director Campus' => [
            'detail' => 'Full access within one campus.',
            'parent' => 'Director System',
            'issys'  => false,
            'aliases' => ['Campus Director', 'Campus director'],
        ],
        'Principal' => [
            'detail' => 'Academic and staff oversight for the campus.',
            'parent' => 'Director Campus',
            'issys'  => false,
        ],
        'Academic Coordinator' => [
            'detail' => 'Classes, diary, exams, attendance coordination.',
            'parent' => 'Principal',
            'issys'  => false,
            'aliases' => ['Academic Cordinator', 'Academic Co-ordinator'],
        ],
        'Teacher' => [
            'detail' => 'Class diary, attendance, quiz and results for assigned classes.',
            'parent' => 'Principal',
            'issys'  => false,
            'aliases' => ['Faculty'],
        ],
        'Director Finance' => [
            'detail' => 'Fee setup, accounts, and financial reports.',
            'parent' => 'Director Campus',
            'issys'  => false,
            'aliases' => ['Finance Director'],
        ],
        'Accountant' => [
            'detail' => 'Fee collection, daily collection, and expense entry.',
            'parent' => 'Director Finance',
            'issys'  => false,
            'aliases' => ['Accountent'],
        ],
    ];

    public function run(): void
    {
        if (! $this->db->tableExists('role_name')) {
            throw new \RuntimeException('Table role_name does not exist. Run migrations first: php spark migrate');
        }

        $this->normalizeLegacyCampusDirector();

        $roleNameIds = [];
        foreach (array_keys($this->roleCatalog) as $roleName) {
            $roleNameIds[$roleName] = $this->upsertRoleName($roleName);
        }

        foreach ($this->roleCatalog as $roleName => $meta) {
            $parentName = $meta['parent'] ?? null;
            $parentId   = ($parentName && isset($roleNameIds[$parentName]))
                ? (int) $roleNameIds[$parentName]
                : 0;

            $this->db->table('role_name')
                ->where('role_name_id', (int) $roleNameIds[$roleName])
                ->update([
                    'parent_id' => $parentId,
                    'detail'    => $meta['detail'],
                ]);
        }

        $planIds = $this->loadPlanIds();
        if ($planIds === []) {
            $this->log('No system_plans rows found — role_name rows seeded, but no roles (plan instances) created.');
            $this->log('Add plans first, then re-run this seeder.');
            return;
        }

        $rolesCreated = 0;
        $rolesSkipped = 0;

        foreach ($planIds as $planId) {
            foreach ($this->roleCatalog as $roleName => $meta) {
                $roleNameId = (int) $roleNameIds[$roleName];
                $issys      = ! empty($meta['issys']) ? 1 : 0;

                $existing = $this->db->table('roles')
                    ->where('role_name_id', $roleNameId)
                    ->where('plan_id', $planId)
                    ->limit(1)
                    ->get()
                    ->getRow();

                if ($existing) {
                    $this->db->table('roles')
                        ->where('id', (int) $existing->id)
                        ->update(['issys' => $issys]);
                    $rolesSkipped++;
                    continue;
                }

                $this->db->table('roles')->insert([
                    'role_name_id' => $roleNameId,
                    'plan_id'      => $planId,
                    'issys'        => $issys,
                ]);
                $rolesCreated++;
            }
        }

        $this->log('Default roles seeded successfully.');
        $this->log('role_name rows: ' . count($this->roleCatalog));
        $this->log('plans processed: ' . count($planIds));
        $this->log('roles created: ' . $rolesCreated . ', existing updated/skipped: ' . $rolesSkipped);
        $this->printHierarchy($roleNameIds);
    }

    /**
     * If legacy "Campus Director" exists but "Director Campus" does not, rename it.
     */
    private function normalizeLegacyCampusDirector(): void
    {
        $canonical = $this->findRoleNameRow('Director Campus');
        if ($canonical) {
            return;
        }

        foreach (['Campus Director', 'Campus director'] as $legacyName) {
            $legacy = $this->findRoleNameRow($legacyName);
            if (! $legacy) {
                continue;
            }

            $this->db->table('role_name')
                ->where('role_name_id', (int) $legacy->role_name_id)
                ->update(['rolename' => 'Director Campus']);

            $this->log('Renamed legacy role "' . $legacyName . '" → "Director Campus".');
            return;
        }
    }

    private function upsertRoleName(string $canonicalName): int
    {
        $meta = $this->roleCatalog[$canonicalName];

        $existing = $this->findRoleNameRow($canonicalName);
        if ($existing) {
            $this->db->table('role_name')
                ->where('role_name_id', (int) $existing->role_name_id)
                ->update(['detail' => $meta['detail']]);
            return (int) $existing->role_name_id;
        }

        foreach ($meta['aliases'] ?? [] as $alias) {
            $aliasRow = $this->findRoleNameRow($alias);
            if ($aliasRow) {
                $this->db->table('role_name')
                    ->where('role_name_id', (int) $aliasRow->role_name_id)
                    ->update([
                        'rolename' => $canonicalName,
                        'detail'   => $meta['detail'],
                    ]);
                $this->log('Merged alias "' . $alias . '" into "' . $canonicalName . '".');
                return (int) $aliasRow->role_name_id;
            }
        }

        $this->db->table('role_name')->insert([
            'rolename'  => $canonicalName,
            'detail'    => $meta['detail'],
            'parent_id' => 0,
        ]);

        $id = (int) $this->db->insertID();
        $this->log('Created role_name: ' . $canonicalName . ' (#' . $id . ')');
        return $id;
    }

    private function findRoleNameRow(string $name): ?object
    {
        return $this->db->table('role_name')
            ->where('rolename', $name)
            ->limit(1)
            ->get()
            ->getRow();
    }

    /** @return list<int> */
    private function loadPlanIds(): array
    {
        $planId = (int) (config('Trial')->defaultPlanId ?? 3);

        return $planId > 0 ? [$planId] : [];
    }

    /** @param array<string, int> $roleNameIds */
    private function printHierarchy(array $roleNameIds): void
    {
        $this->log('');
        $this->log('Role hierarchy:');
        $this->printRoleBranch('Super Admin', $roleNameIds, 0);
    }

    /** @param array<string, int> $roleNameIds */
    private function printRoleBranch(string $name, array $roleNameIds, int $depth): void
    {
        $prefix = str_repeat('  ', $depth) . ($depth > 0 ? '└─ ' : '');
        $id     = $roleNameIds[$name] ?? 0;
        $this->log($prefix . $name . ($id ? ' (#' . $id . ')' : ''));

        foreach ($this->roleCatalog as $childName => $meta) {
            if (($meta['parent'] ?? null) === $name) {
                $this->printRoleBranch($childName, $roleNameIds, $depth + 1);
            }
        }
    }

    private function log(string $message): void
    {
        if (is_cli()) {
            CLI::write($message);
            return;
        }
        log_message('info', '[DefaultRolesSeeder] ' . $message);
    }
}
