<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;
use Config\PermissionKeyRegistry;

/**
 * Inserts missing permission rows from PermissionKeyRegistry.
 *
 * Run before DefaultRolePermissionsSeeder:
 *   php spark db:seed DefaultPermissionsSeeder
 */
class DefaultPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->db->tableExists('permissions')) {
            throw new \RuntimeException('Table permissions does not exist. Run migrations first.');
        }

        $keys = PermissionKeyRegistry::allKeys();
        if ($keys === []) {
            $this->log('No permission keys found in registry.');
            return;
        }

        $existing = $this->loadExistingKeys();
        $now      = date('Y-m-d H:i:s');
        $inserted = 0;
        $skipped  = 0;

        foreach ($keys as $key) {
            if (isset($existing[$key])) {
                $skipped++;
                continue;
            }

            $this->db->table('permissions')->insert([
                'permKey'      => $key,
                'permName'     => PermissionKeyRegistry::labelFromKey($key),
                'parent_id'    => 0,
                'lft'          => 0,
                'rgt'          => 0,
                'root_id'      => 0,
                'sortid'       => 0,
                'issys'        => 1,
                'permType'     => 0,
                'rel_id'       => 0,
                'created_date' => $now,
                'updated_date' => $now,
            ]);

            $existing[$key] = true;
            $inserted++;
        }

        $this->clearPermissionCaches();

        $this->log('Default permissions seeded.');
        $this->log('Inserted: ' . $inserted);
        $this->log('Already present: ' . $skipped);
        $this->log('Total registry keys: ' . count($keys));
    }

    /** @return array<string, true> */
    private function loadExistingKeys(): array
    {
        $rows = $this->db->table('permissions')
            ->select('permKey')
            ->get()
            ->getResultArray();

        $index = [];
        foreach ($rows as $row) {
            $key = strtolower(trim((string) ($row['permKey'] ?? '')));
            if ($key !== '') {
                $index[$key] = true;
            }
        }

        return $index;
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
        log_message('info', '[DefaultPermissionsSeeder] ' . $message);
    }
}
