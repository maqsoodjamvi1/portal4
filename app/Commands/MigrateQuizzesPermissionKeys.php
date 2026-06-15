<?php

namespace App\Commands;

use App\Libraries\MemberAcl;
use App\Libraries\RoleMenuAccess;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Grant new Quizzes permission keys to roles that already have admin-exams.
 *
 * Usage:
 *   php spark permissions:migrate-quizzes-keys
 *   php spark permissions:migrate-quizzes-keys 218
 */
class MigrateQuizzesPermissionKeys extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'permissions:migrate-quizzes-keys';
    protected $description = 'Copy admin-exams grants to new Quizzes-specific permission keys';
    protected $usage       = 'permissions:migrate-quizzes-keys [role_id]';

    /** @var list<string> */
    private array $newKeys = [
        'admin-qb-topics',
        'admin-vocab-topics',
        'admin-vocab-bank',
        'admin-vocab-words',
    ];

    public function run(array $params): void
    {
        $db         = db_connect();
        $roleFilter = isset($params[0]) ? (int) $params[0] : 0;

        $permIdByKey = $this->loadPermissionIds($db);
        $examsPermId = (int) ($permIdByKey['admin-exams'] ?? 0);

        if ($examsPermId <= 0) {
            CLI::error('Permission admin-exams not found. Run: php spark db:seed DefaultPermissionsSeeder');

            return;
        }

        $missing = [];
        foreach ($this->newKeys as $key) {
            if ((int) ($permIdByKey[$key] ?? 0) <= 0) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            CLI::error('Missing permission rows: ' . implode(', ', $missing));
            CLI::write('Run: php spark db:seed DefaultPermissionsSeeder', 'yellow');

            return;
        }

        $builder = $db->table('role_perms rp')
            ->select('DISTINCT rp.roleID', false)
            ->where('rp.permID', $examsPermId)
            ->where('rp.value', 1);

        if ($roleFilter > 0) {
            $builder->where('rp.roleID', $roleFilter);
        }

        $roleIds = array_map(
            static fn (array $row): int => (int) ($row['roleID'] ?? 0),
            $builder->get()->getResultArray()
        );
        $roleIds = array_values(array_filter($roleIds, static fn (int $id): bool => $id > 0));

        if ($roleIds === []) {
            CLI::write('No roles with admin-exams granted.', 'yellow');

            return;
        }

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;
        $date     = date('Y-m-d H:i:s');

        foreach ($roleIds as $roleId) {
            foreach ($this->newKeys as $key) {
                $permId = (int) $permIdByKey[$key];
                $existing = $db->table('role_perms')
                    ->where('roleID', $roleId)
                    ->where('permID', $permId)
                    ->get()
                    ->getRow();

                if ($existing) {
                    if ((int) ($existing->value ?? 0) === 1) {
                        $skipped++;
                        continue;
                    }

                    $db->table('role_perms')
                        ->where('roleID', $roleId)
                        ->where('permID', $permId)
                        ->update(['value' => 1, 'add_date' => $date]);
                    $updated++;
                    continue;
                }

                $db->table('role_perms')->insert([
                    'roleID'   => $roleId,
                    'permID'   => $permId,
                    'value'    => 1,
                    'add_date' => $date,
                ]);
                $inserted++;
            }

            RoleMenuAccess::clearCacheForRole($roleId);

            foreach ($this->userIdsForRole($db, $roleId) as $userId) {
                (new MemberAcl($userId))->clearUserCaches($userId);
                RoleMenuAccess::clearCacheForUser($userId);
            }
        }

        CLI::newLine();
        CLI::write("Roles processed: " . count($roleIds), 'white');
        CLI::write("Inserted: {$inserted}, updated: {$updated}, already granted: {$skipped}", 'green');
        CLI::write('Ask affected users to log out and log back in.', 'yellow');
    }

    /**
     * @return array<string, int>
     */
    private function loadPermissionIds($db): array
    {
        $keys   = array_merge(['admin-exams'], $this->newKeys);
        $rows   = $db->table('permissions')
            ->select('id, permKey')
            ->whereIn('permKey', $keys)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $key = strtolower(trim((string) ($row['permKey'] ?? '')));
            if ($key !== '') {
                $map[$key] = (int) ($row['id'] ?? 0);
            }
        }

        return $map;
    }

    /**
     * @return list<int>
     */
    private function userIdsForRole($db, int $roleId): array
    {
        if ($roleId <= 0) {
            return [];
        }

        $ids = [];
        foreach ($db->table('user_roles')->select('userID')->where('roleID', $roleId)->get()->getResultArray() as $row) {
            $uid = (int) ($row['userID'] ?? 0);
            if ($uid > 0) {
                $ids[$uid] = $uid;
            }
        }

        return array_values($ids);
    }
}
