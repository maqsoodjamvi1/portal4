<?php

namespace App\Commands;

use App\Libraries\RoleMenuAccess;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Debug Quizzes menu visibility for a user (writes to debug-465f25.log).
 *
 * Usage: php spark debug:quizzes-menu [user_id]
 */
class DebugQuizzesMenuVisibility extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'debug:quizzes-menu';
    protected $description = 'Simulate quizzesMenuItemVisible for all Quizzes items';
    protected $usage       = 'debug:quizzes-menu [user_id]';

    public function run(array $params): void
    {
        helper('role');

        $userId = isset($params[0]) ? (int) $params[0] : (int) (session()->get('member_userid') ?? 0);
        if ($userId <= 0) {
            $userId = (int) (db_connect()->table('users')->select('id')->orderBy('id', 'ASC')->limit(1)->get()->getRow()->id ?? 0);
        }

        $acl    = new \App\Libraries\MemberAcl($userId);
        $canAny = static function (array $perms) use ($acl): bool {
            foreach ($perms as $p) {
                if ($acl->hasPermission($p)) {
                    return true;
                }
            }

            return false;
        };

        $items = [
            ['key' => 'question-bank.topics', 'label' => 'QB Topics', 'perms' => ['admin-qb-topics']],
            ['key' => 'vocabulary-bank.topics', 'label' => 'Vocab Topics', 'perms' => ['admin-vocab-topics']],
            ['key' => 'vocab-bank.vocab-bank', 'label' => 'Vocabulary Bank', 'perms' => ['admin-vocab-bank']],
            ['key' => 'vocab-bank.listofwords', 'label' => 'Vocabulary words', 'perms' => ['admin-vocab-words']],
            ['key' => 'question-bank.question-bank', 'label' => 'QB Overview', 'perms' => ['admin-question-bank-overview']],
            ['key' => 'quizzes.quizzes', 'label' => 'Quizzes', 'perms' => ['admin-quiz'], 'director_quizzes_menu' => true],
        ];

        CLI::write("Testing user #{$userId}, isSuperAdmin: " . (userIsSuperAdmin($userId) ? 'yes' : 'no'), 'white');

        foreach ($items as $item) {
            $menuKey = trim((string) ($item['key'] ?? ''));
            $menuOk  = $menuKey === '' || RoleMenuAccess::isAllowedForUser($menuKey, $userId);
            $permOk  = empty($item['perms']) || $canAny($item['perms']);
            if (! empty($item['director_quizzes_menu'])) {
                $visible = quizzesMenuItemVisible($item, $canAny, $userId);
            } else {
                $visible = $menuOk && $permOk;
            }
            CLI::write(($item['label'] ?? '') . ': ' . ($visible ? 'VISIBLE' : 'HIDDEN'), $visible ? 'green' : 'red');
        }

        CLI::write('Done.', 'yellow');
    }
}
