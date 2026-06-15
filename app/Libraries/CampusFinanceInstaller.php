<?php

namespace App\Libraries;

use Config\PermissionKeyRegistry;

/**
 * One-time / idempotent setup: finance tables + permission rows + role grants.
 */
class CampusFinanceInstaller
{
    protected $db;

    /** @var list<string> */
    private const FINANCE_PERM_KEYS = [
        'admin-finance-accounts',
        'admin-add-finance-accounts',
        'admin-edit-finance-accounts',
        'admin-cash-flow-report',
    ];

    public function __construct(?\CodeIgniter\Database\BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Run all setup steps; safe to call repeatedly.
     *
     * @return array{schema:bool,permissions:bool,roles:bool,messages:list<string>}
     */
    public function ensureAll(): array
    {
        $messages = [];
        $schema   = $this->ensureSchema($messages);
        $perms    = $this->ensurePermissionRows($messages);
        $roles    = $this->ensureRoleGrants($messages);

        return [
            'schema'      => $schema,
            'permissions' => $perms,
            'roles'       => $roles,
            'messages'    => $messages,
        ];
    }

    public function ensureSchema(array &$messages = []): bool
    {
        if ($this->db->tableExists('campus_finance_accounts')
            && $this->db->tableExists('finance_transactions')) {
            return true;
        }

        try {
            $migrate = \Config\Services::migrations();
            $migrate->setNamespace('App');
            $migrate->latest('App');
            $messages[] = 'Finance database tables created (migration).';

            return $this->db->tableExists('campus_finance_accounts');
        } catch (\Throwable $e) {
            log_message('error', 'CampusFinanceInstaller schema: ' . $e->getMessage());
            $messages[] = 'Could not create finance tables automatically. Run: php spark migrate';

            return false;
        }
    }

    public function ensurePermissionRows(array &$messages = []): bool
    {
        if (! $this->db->tableExists('permissions')) {
            $messages[] = 'Permissions table missing — skip permission seed.';

            return false;
        }

        $existing = [];
        foreach ($this->db->table('permissions')->select('permKey')->get()->getResult() as $row) {
            $existing[strtolower((string) $row->permKey)] = true;
        }

        $now     = date('Y-m-d H:i:s');
        $added   = 0;

        foreach (self::FINANCE_PERM_KEYS as $key) {
            $key = strtolower($key);
            if (isset($existing[$key])) {
                continue;
            }
            if (! in_array($key, PermissionKeyRegistry::allKeys(), true)
                && ! in_array($key, self::FINANCE_PERM_KEYS, true)) {
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
            $added++;
        }

        if ($added > 0) {
            $messages[] = "Inserted {$added} finance permission(s).";
        }

        return true;
    }

    /**
     * Grant finance permissions to roles that already have accounts / P&L access.
     */
    public function ensureRoleGrants(array &$messages = []): bool
    {
        if (! $this->db->tableExists('role_perms') || ! $this->db->tableExists('permissions')) {
            return false;
        }

        $permIdByKey = [];
        foreach ($this->db->table('permissions')->select('id, permKey')->get()->getResult() as $row) {
            $permIdByKey[strtolower((string) $row->permKey)] = (int) $row->id;
        }

        $grants = [
            'admin-finance-accounts'     => ['admin-accounts', 'admin-account-heads', 'admin-account-expenses'],
            'admin-add-finance-accounts' => ['admin-accounts', 'admin-add-account-expenses'],
            'admin-edit-finance-accounts'=> ['admin-accounts', 'admin-edit-account-expenses'],
            'admin-cash-flow-report'     => ['admin-profit-loss-reports', 'admin-account-reports'],
        ];

        $granted = 0;

        foreach ($grants as $newKey => $anchorKeys) {
            $newId = $permIdByKey[strtolower($newKey)] ?? 0;
            if ($newId <= 0) {
                continue;
            }

            $anchorIds = [];
            foreach ($anchorKeys as $ak) {
                $aid = $permIdByKey[strtolower($ak)] ?? 0;
                if ($aid > 0) {
                    $anchorIds[] = $aid;
                }
            }
            if ($anchorIds === []) {
                continue;
            }

            $roleIds = $this->db->table('role_perms')
                ->select('roleID')
                ->whereIn('permID', $anchorIds)
                ->where('value', 1)
                ->groupBy('roleID')
                ->get()
                ->getResultArray();

            foreach ($roleIds as $r) {
                $roleId = (int) ($r['roleID'] ?? 0);
                if ($roleId <= 0) {
                    continue;
                }

                $exists = $this->db->table('role_perms')
                    ->where('roleID', $roleId)
                    ->where('permID', $newId)
                    ->countAllResults();

                if ($exists > 0) {
                    continue;
                }

                $this->db->table('role_perms')->insert([
                    'roleID'   => $roleId,
                    'permID'   => $newId,
                    'value'    => 1,
                    'add_date' => date('Y-m-d H:i:s'),
                ]);
                $granted++;
            }
        }

        if ($granted > 0) {
            $messages[] = "Granted finance permissions on {$granted} role-permission link(s). Re-login to refresh menu.";
            $this->clearPermissionCaches();
        }

        return true;
    }

    private function clearPermissionCaches(): void
    {
        if (function_exists('cxp_update_cache')) {
            cxp_update_cache();
        }
        if (class_exists(\App\Libraries\MemberAcl::class)) {
            \App\Libraries\MemberAcl::clearDictionaryCaches();
        }
    }
}
