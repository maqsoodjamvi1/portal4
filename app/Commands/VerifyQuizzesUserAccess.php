<?php

namespace App\Commands;

use App\Libraries\RoleMenuAccess;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Verify Quizzes menu/permission grants for a user.
 *
 * Usage: php spark permissions:verify-quizzes-user 11
 */
class VerifyQuizzesUserAccess extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'permissions:verify-quizzes-user';
    protected $description = 'Check Quizzes menu and permission grants for a user';
    protected $usage       = 'permissions:verify-quizzes-user <user_id> [role_id]';

    public function run(array $params): void
    {
        helper('role');

        $userId  = isset($params[0]) ? (int) $params[0] : 0;
        $roleId  = isset($params[1]) ? (int) $params[1] : 218;

        if ($userId <= 0) {
            CLI::error('Usage: permissions:verify-quizzes-user <user_id> [role_id]');

            return;
        }

        $db = db_connect();

        CLI::write("User #{$userId} role IDs: " . implode(', ', RoleMenuAccess::resolveUserRoleIds($userId)), 'white');
        CLI::write(
            'QB Topics menu granted: ' . (RoleMenuAccess::isMenuGrantedByAnyRole('question-bank.topics', $userId) ? 'yes' : 'no'),
            'cyan'
        );

        $keys = ['admin-exams', 'admin-qb-topics', 'admin-vocab-topics', 'admin-vocab-bank', 'admin-vocab-words'];
        foreach ($keys as $key) {
            $perm = $db->table('permissions')->where('permKey', $key)->get()->getRow();
            if (! $perm) {
                CLI::write("{$key}: permission row missing", 'red');
                continue;
            }

            $rp = $db->table('role_perms')
                ->where('roleID', $roleId)
                ->where('permID', (int) $perm->id)
                ->get()
                ->getRow();

            CLI::write("Role {$roleId} {$key}: " . ((int) ($rp->value ?? 0) === 1 ? 'granted' : 'not granted'), 'yellow');
        }

        $menu = $db->table('role_menu_access')
            ->where('role_id', $roleId)
            ->where('menu_key', 'question-bank.topics')
            ->get()
            ->getRow();

        CLI::write(
            "Role {$roleId} menu question-bank.topics: " . ((int) ($menu->allowed ?? 0) === 1 ? 'Show' : 'Hide/missing'),
            'green'
        );

        $quizKeys = [
            'quizzes',
            'quizzes.quizzes',
            'question-bank.topics',
            'question-bank.question-bank',
            'vocabulary-bank.topics',
            'vocab-bank.vocab-bank',
            'vocab-bank.listofwords',
        ];

        CLI::write('--- Menu grant per key (any role) ---', 'white');
        foreach ($quizKeys as $key) {
            $granted = RoleMenuAccess::isMenuGrantedByAnyRole($key, $userId);
            $allowed = RoleMenuAccess::isAllowedForUser($key, $userId);
            CLI::write("{$key}: granted=" . ($granted ? 'yes' : 'no') . ', isAllowedForUser=' . ($allowed ? 'yes' : 'no'), $granted ? 'green' : 'red');
        }

        if ($db->tableExists('user_menu_prefs')) {
            $prefsRow = $db->table('user_menu_prefs')->where('user_id', $userId)->get()->getRow();
            if ($prefsRow && ! empty($prefsRow->prefs)) {
                $prefs = is_string($prefsRow->prefs) ? json_decode($prefsRow->prefs, true) : (array) $prefsRow->prefs;
                $hidden  = $prefs['hidden'] ?? [];
                CLI::write('User menu prefs hidden: ' . (empty($hidden) ? '(none)' : implode(', ', (array) $hidden)), 'yellow');
            } else {
                CLI::write('User menu prefs: (none)', 'yellow');
            }
        }

        $userRow = $db->table('users')->select('id, username, first_name')->where('id', $userId)->get()->getRow();
        if ($userRow) {
            CLI::write("Username: {$userRow->username} ({$userRow->first_name})", 'cyan');
        }

        helper('role');
        $canAny = static function (array $perms) use ($userId): bool {
            $acl = new \App\Libraries\MemberAcl($userId);

            foreach ($perms as $p) {
                if ($acl->hasPermission($p)) {
                    return true;
                }
            }

            return false;
        };

        $quizMenuItems = [
            ['key' => 'question-bank.topics', 'label' => 'QB Topics', 'perms' => ['admin-qb-topics']],
            ['key' => 'vocabulary-bank.topics', 'label' => 'Vocab Topics', 'perms' => ['admin-vocab-topics']],
            ['key' => 'vocab-bank.vocab-bank', 'label' => 'Vocabulary Bank', 'perms' => ['admin-vocab-bank']],
            ['key' => 'vocab-bank.listofwords', 'label' => 'Vocabulary words', 'perms' => ['admin-vocab-words']],
            ['key' => 'question-bank.question-bank', 'label' => 'QB Overview', 'perms' => ['admin-question-bank-overview']],
            ['key' => 'quizzes.quizzes', 'label' => 'Quizzes', 'perms' => ['admin-quiz'], 'director_quizzes_menu' => true],
        ];

        CLI::write('--- header menuItemVisible simulation (production path) ---', 'white');
        foreach ($quizMenuItems as $item) {
            $menuKey = trim((string) ($item['key'] ?? ''));
            if (! empty($item['director_quizzes_menu']) || ! empty($item['super_admin_only'])) {
                $visible = quizzesMenuItemVisible($item, $canAny, $userId);
            } else {
                $menuOk = $menuKey === '' || RoleMenuAccess::isAllowedForUser($menuKey, $userId);
                $perms  = $item['perms'] ?? [];
                $permOk = $perms === [] || $canAny($perms);
                $visible = $menuOk && $permOk;
            }
            CLI::write(($item['label'] ?? '') . ': ' . ($visible ? 'VISIBLE' : 'HIDDEN'), $visible ? 'green' : 'red');
        }
    }
}
