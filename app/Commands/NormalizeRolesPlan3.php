<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Remap all user_roles to annual plan 3 role ids (by role_name_id).
 *
 * Usage:
 *   php spark roles:normalize-plan3
 *   php spark roles:normalize-plan3 11
 */
class NormalizeRolesPlan3 extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'roles:normalize-plan3';
    protected $description = 'Remap user_roles from plan 1/2 role ids to plan 3 equivalents';
    protected $usage       = 'roles:normalize-plan3 [user_id]';

    public function run(array $params): void
    {
        helper('role');

        $db         = db_connect();
        $targetPlan = getRolePlanId();
        $userFilter = isset($params[0]) ? (int) $params[0] : 0;

        $builder = $db->table('user_roles ur')
            ->select('ur.id as user_role_row_id, ur.userID, ur.roleID as stored_role_id');

        if ($userFilter > 0) {
            $builder->where('ur.userID', $userFilter);
        }

        $rows = $builder->get()->getResultArray();

        if ($rows === []) {
            CLI::write('No user_roles rows found.', 'green');

            return;
        }

        $updated = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($rows as $row) {
            $rowId    = (int) ($row['user_role_row_id'] ?? 0);
            $userId   = (int) ($row['userID'] ?? 0);
            $storedId = (int) ($row['stored_role_id'] ?? 0);

            if ($rowId <= 0 || $userId <= 0 || $storedId <= 0) {
                $skipped++;
                continue;
            }

            $normalized = normalizeRoleIdsForPlan([$storedId], $targetPlan);
            $newRoleId  = (int) ($normalized[0] ?? 0);

            if ($newRoleId <= 0) {
                CLI::write("Skip user #{$userId}: cannot resolve role {$storedId} for plan {$targetPlan}", 'yellow');
                $skipped++;
                continue;
            }

            if ($newRoleId === $storedId) {
                $skipped++;
                continue;
            }

            $dup = $db->table('user_roles')
                ->where('userID', $userId)
                ->where('roleID', $newRoleId)
                ->where('id !=', $rowId)
                ->limit(1)
                ->get()
                ->getRow();

            if ($dup) {
                $db->table('user_roles')->where('id', $rowId)->delete();
                CLI::write("User #{$userId}: removed duplicate row {$storedId} (already has {$newRoleId})", 'cyan');
                $updated++;
                continue;
            }

            $ok = $db->table('user_roles')
                ->where('id', $rowId)
                ->update(['roleID' => $newRoleId]);

            if ($ok) {
                CLI::write("User #{$userId}: {$storedId} -> {$newRoleId} (plan {$targetPlan})", 'green');
                $updated++;
                \App\Libraries\RoleMenuAccess::clearCacheForUser($userId);
                (new \App\Libraries\MemberAcl($userId))->clearUserCaches($userId);
            } else {
                CLI::write("User #{$userId}: failed to update row {$rowId}", 'red');
                $errors++;
            }
        }

        CLI::newLine();
        CLI::write("Done. Updated: {$updated}, skipped: {$skipped}, errors: {$errors}", 'white');
        CLI::write('Ask affected users to log out and log back in.', 'yellow');
    }
}
