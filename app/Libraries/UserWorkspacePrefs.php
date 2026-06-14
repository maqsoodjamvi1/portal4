<?php

namespace App\Libraries;

use App\Models\UserWorkspacePrefsModel;
use Config\Database;

/**
 * Persist and restore per-user campus / academic session workspace selection.
 */
class UserWorkspacePrefs
{
    private static function ensureTable(): bool
    {
        $db = Database::connect();
        if ($db->tableExists('user_workspace_prefs')) {
            return true;
        }

        try {
            $db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS `user_workspace_prefs` (
  `user_id` int(11) unsigned NOT NULL,
  `campus_id` int(11) unsigned DEFAULT NULL,
  `session_id` int(11) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL);
        } catch (\Throwable $e) {
            log_message('error', 'user_workspace_prefs create failed: ' . $e->getMessage());
            return false;
        }

        return $db->tableExists('user_workspace_prefs');
    }

    public static function save(int $userId, ?int $campusId = null, ?int $sessionId = null): void
    {
        if ($userId <= 0) {
            return;
        }

        if (!static::ensureTable()) {
            return;
        }

        $model = new UserWorkspacePrefsModel();
        $existing = $model->find($userId);

        $payload = [
            'user_id'    => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($campusId !== null && $campusId > 0) {
            $payload['campus_id'] = $campusId;
        } elseif (is_array($existing) && isset($existing['campus_id'])) {
            $payload['campus_id'] = $existing['campus_id'];
        }

        if ($sessionId !== null && $sessionId > 0) {
            $payload['session_id'] = $sessionId;
        } elseif (is_array($existing) && isset($existing['session_id'])) {
            $payload['session_id'] = $existing['session_id'];
        }

        if ($existing) {
            $model->update($userId, $payload);
        } else {
            $model->insert($payload);
        }
    }

    /**
     * Apply saved workspace prefs to the active session when values are valid.
     */
    public static function applyToSession(int $userId, int $systemId): void
    {
        if ($userId <= 0 || $systemId <= 0) {
            return;
        }

        if (!static::ensureTable()) {
            return;
        }

        $db = Database::connect();
        $row = (new UserWorkspacePrefsModel())->find($userId);
        if (!$row) {
            return;
        }

        $session = session();
        $campusId  = (int) ($row['campus_id'] ?? 0);
        $sessionId = (int) ($row['session_id'] ?? 0);

        if ($campusId > 0) {
            $campus = $db->table('campus')
                ->where('campus_id', $campusId)
                ->where('system_id', $systemId)
                ->get()
                ->getRow();

            if ($campus) {
                $session->set('member_campusid', $campusId);
            }
        }

        if ($sessionId > 0) {
            $academicSession = $db->table('academic_session')
                ->where('session_id', $sessionId)
                ->where('system_id', $systemId)
                ->get()
                ->getRow();

            if ($academicSession) {
                $session->set('member_sessionid', $sessionId);
            }
        }
    }
}
