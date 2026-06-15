<?php

namespace App\Libraries;

use App\Models\UserMenuPrefsModel;
use Config\Database;

/**
 * Syncs menu visibility prefs between DB (hidden[]), session map, and client localStorage.
 */
class UserMenuPrefsLibrary
{
    /**
     * @return array<string, bool> key => visible (true = show)
     */
    public static function loadMapForUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        if (! Database::connect()->tableExists('user_menu_prefs')) {
            return [];
        }

        $m   = new UserMenuPrefsModel();
        $row = $m->find($userId);
        if (! $row) {
            return [];
        }

        $raw = $row['prefs'] ?? null;
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        return self::hiddenArrayToMap(is_array($raw) ? $raw : []);
    }

    /**
     * @param array<string, bool>|array{hidden?: list<string>} $input
     * @return list<string>
     */
    public static function toHiddenList(array $input): array
    {
        if (isset($input['hidden']) && is_array($input['hidden'])) {
            return array_values(array_unique(array_map('strval', $input['hidden'])));
        }

        $hidden = [];
        foreach ($input as $key => $visible) {
            if ($key === 'hidden') {
                continue;
            }
            if ($visible === false || $visible === 0 || $visible === '0') {
                $hidden[] = (string) $key;
            }
        }

        return array_values(array_unique($hidden));
    }

    /**
     * @param array{hidden?: list<string>}|array<string, bool> $stored
     * @return array<string, bool>
     */
    public static function hiddenArrayToMap(array $stored): array
    {
        if (isset($stored['hidden']) && is_array($stored['hidden'])) {
            $map = [];
            foreach ($stored['hidden'] as $key) {
                $map[(string) $key] = false;
            }

            return $map;
        }

        $map = [];
        foreach ($stored as $key => $visible) {
            if ($key === 'hidden') {
                continue;
            }
            $map[(string) $key] = ($visible !== false && $visible !== 0 && $visible !== '0');
        }

        return $map;
    }

    public static function applyToSession(int $userId): void
    {
        $map = self::loadMapForUser($userId);
        session()->set('menu_prefs', $map);
    }

    /**
     * @param array<string, bool>|array{hidden?: list<string>} $prefs
     */
    public static function saveForUser(int $userId, array $prefs): void
    {
        if ($userId <= 0 || ! Database::connect()->tableExists('user_menu_prefs')) {
            return;
        }

        $hidden = self::toHiddenList($prefs);
        $m      = new UserMenuPrefsModel();
        $m->save([
            'user_id'    => $userId,
            'prefs'      => json_encode(['hidden' => $hidden]),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        session()->set('menu_prefs', self::hiddenArrayToMap(['hidden' => $hidden]));
    }

    /**
     * Parse POST/JSON body from menu prefs modal.
     *
     * @return array<string, bool>|null
     */
    public static function parseRequestPayload($request): ?array
    {
        $json = $request->getJSON(true);
        if (is_array($json)) {
            if (isset($json['hidden'])) {
                return self::hiddenArrayToMap($json);
            }
            if (isset($json['prefs'])) {
                $decoded = is_string($json['prefs']) ? json_decode($json['prefs'], true) : $json['prefs'];

                return is_array($decoded) ? self::hiddenArrayToMap($decoded) : null;
            }

            return self::hiddenArrayToMap($json);
        }

        $prefsRaw = $request->getPost('prefs');
        if ($prefsRaw !== null && $prefsRaw !== '') {
            $decoded = is_string($prefsRaw) ? json_decode($prefsRaw, true) : $prefsRaw;

            return is_array($decoded) ? self::hiddenArrayToMap($decoded) : null;
        }

        $hidden = $request->getPost('hidden');
        if (is_array($hidden)) {
            return self::hiddenArrayToMap(['hidden' => $hidden]);
        }

        return null;
    }
}
