<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Remap user_roles.roleID to the correct roles.id for each user's campus plan.
 *
 * Usage:
 *   php spark roles:fix-plan
 *   php spark roles:fix-plan 3
 *   php spark roles:fix-plan 3 1875
 */
class FixUserRolesPlan extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'roles:fix-plan';
    protected $description = 'Remap user_roles to plan-scoped roles.id (fixes missing menus)';
    protected $usage       = 'roles:fix-plan [plan_id] [user_id]';

    public function run(array $params): void
    {
        helper('role');

        $db = db_connect();
        $planFilter = isset($params[0]) ? (int) $params[0] : getRolePlanId();
        $userFilter = isset($params[1]) ? (int) $params[1] : 0;

        $builder = $db->table('user_roles ur')
            ->select('ur.id as user_role_row_id, ur.userID, ur.roleID as stored_role_id, u.campus_id, cb.plan_id')
            ->join('users u', 'u.id = ur.userID', 'inner')
            ->join('campus_bills cb', 'cb.campus_id = u.campus_id AND cb.status = 1', 'inner')
            ->join('roles r', 'r.id = ur.roleID AND r.plan_id = cb.plan_id', 'left')
            ->where('r.id IS NULL', null, false);

        if ($planFilter > 0) {
            $builder->where('cb.plan_id', $planFilter);
        }
        if ($userFilter > 0) {
            $builder->where('ur.userID', $userFilter);
        }

        $rows = $builder->get()->getResultArray();

        if ($rows === []) {
            CLI::write('No mismatched user_roles rows found.', 'green');
            return;
        }

        $updated = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($rows as $row) {
            $rowId     = (int) ($row['user_role_row_id'] ?? 0);
            $userId    = (int) ($row['userID'] ?? 0);
            $storedId  = (int) ($row['stored_role_id'] ?? 0);
            $planId    = (int) ($row['plan_id'] ?? 0);

            if ($rowId <= 0 || $userId <= 0 || $storedId <= 0 || $planId <= 0) {
                $skipped++;
                continue;
            }

            $normalized = normalizeRoleIdsForPlan([$storedId], $planId);
            $newRoleId  = (int) ($normalized[0] ?? 0);

            if ($newRoleId <= 0) {
                CLI::write("Skip user #{$userId}: cannot resolve role {$storedId} for plan {$planId}", 'yellow');
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
                CLI::write("User #{$userId}: {$storedId} -> {$newRoleId} (plan {$planId})", 'green');
                $updated++;
            } else {
                CLI::write("User #{$userId}: failed to update row {$rowId}", 'red');
                $errors++;
            }
        }

        CLI::newLine();
        CLI::write("Done. Updated: {$updated}, skipped: {$skipped}, errors: {$errors}", 'white');
        CLI::write('Ask affected users to log out and log back in (or clear cache) to refresh menu permissions.', 'yellow');
    }
}
