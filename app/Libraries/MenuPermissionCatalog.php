<?php

namespace App\Libraries;

use Config\AdminControllerPermissions;

/**
 * Maps admin sidebar menu items to permission keys for menu-based role editing.
 */
class MenuPermissionCatalog
{
    /** @var array<string, list<string>>|null */
    private static ?array $keyToMenuLabels = null;

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $itemIndex = null;

    /** @var array<string, list<string>>|null section key => child menu keys */
    private static ?array $sectionChildKeys = null;

    /**
     * Full menu tree for the role editor (all items, ignoring current user perms).
     *
     * @return list<array<string, mixed>>
     */
    public static function getCatalog(): array
    {
        self::buildIndexes();

        $sections = AdminMenuBuilder::build([
            'link'   => static fn (string $path): string => base_url($path),
            'can'    => static fn (string $perm): bool => true,
            'canAny' => static fn (array $perms): bool => true,
        ]);

        $out = [];
        foreach ($sections as $section) {
            $entry = self::normalizeSection($section);
            if ($entry !== null) {
                $out[] = $entry;
            }
        }

        return $out;
    }

    /**
     * Per-menu-key state for a role (enabled / locked / shared warnings).
     *
     * @return array<string, array{enabled: bool, locked: bool, sharedWith: list<string>}>
     */
    public static function getStateForRole(int $roleId): array
    {
        self::buildIndexes();

        $overrides      = RoleMenuAccess::getMapForRole($roleId);
        $hasOverrides   = $overrides !== [];
        $allowedPermIds = self::loadAllowedPermIds($roleId);
        $allowedKeys    = self::loadAllowedPermKeys($allowedPermIds);

        $state = [];
        foreach (self::$itemIndex as $key => $item) {
            $permKeys = $item['permKeys'];
            $locked   = $permKeys === [];

            if (array_key_exists($key, $overrides)) {
                $enabled = (int) $overrides[$key] === 1;
            } elseif ($locked) {
                $enabled = true;
            } elseif ($hasOverrides) {
                // Role has saved menu access — default hidden unless explicitly allowed.
                $enabled = false;
            } else {
                $enabled = self::roleHasAnyPermKey($allowedKeys, $permKeys);
            }

            $sharedWith = [];
            foreach ($permKeys as $pk) {
                foreach (self::$keyToMenuLabels[$pk] ?? [] as $label) {
                    if ($label !== $item['label']) {
                        $sharedWith[$label] = true;
                    }
                }
            }

            $state[$key] = [
                'enabled'    => $enabled,
                'locked'     => $locked,
                'sharedWith' => array_values(array_keys($sharedWith)),
            ];
        }

        foreach (self::getSectionKeys() as $sectionKey) {
            if (array_key_exists($sectionKey, $overrides)) {
                $state[$sectionKey] = [
                    'enabled'    => (int) $overrides[$sectionKey] === 1,
                    'locked'     => false,
                    'sharedWith' => [],
                    'isSection'  => true,
                ];
                continue;
            }

            $childEnabled = false;
            foreach (self::$sectionChildKeys[$sectionKey] ?? [] as $itemKey) {
                if (($state[$itemKey]['enabled'] ?? false) === true) {
                    $childEnabled = true;
                    break;
                }
            }

            $state[$sectionKey] = [
                'enabled'    => $childEnabled,
                'locked'     => false,
                'sharedWith' => [],
                'isSection'  => true,
            ];
        }

        return $state;
    }

    /**
     * @return list<string>
     */
    public static function getSectionKeys(): array
    {
        $sections = AdminMenuBuilder::build([
            'link'   => static fn (string $path): string => base_url($path),
            'can'    => static fn (string $perm): bool => true,
            'canAny' => static fn (array $perms): bool => true,
        ]);

        $keys = [];
        foreach ($sections as $section) {
            $key = trim((string) ($section['key'] ?? ''));
            if ($key !== '') {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Flat index keyed by menu item key (for JS sync).
     *
     * @return array<string, array{label: string, permKeys: list<string>, permIds: list<int>}>
     */
    public static function getItemIndex(): array
    {
        self::buildIndexes();

        return self::$itemIndex;
    }

    private static function buildIndexes(): void
    {
        if (self::$itemIndex !== null) {
            return;
        }

        self::$itemIndex         = [];
        self::$keyToMenuLabels   = [];
        self::$sectionChildKeys  = [];
        $permKeyToId             = self::loadPermKeyToIdMap();

        $sections = AdminMenuBuilder::build([
            'link'   => static fn (string $path): string => base_url($path),
            'can'    => static fn (string $perm): bool => true,
            'canAny' => static fn (array $perms): bool => true,
        ]);

        foreach ($sections as $section) {
            self::indexSectionChildren($section);
            self::walkSection($section, $permKeyToId);
        }
    }

    /**
     * @param array<string, mixed> $section
     */
    private static function indexSectionChildren(array $section): void
    {
        $sectionKey = trim((string) ($section['key'] ?? ''));
        if ($sectionKey === '' || empty($section['children'])) {
            return;
        }

        $keys = [];
        foreach ($section['children'] as $child) {
            if (! self::isCatalogMenuItem($child)) {
                continue;
            }

            $key = trim((string) ($child['key'] ?? ''));
            if ($key !== '') {
                $keys[] = $key;
            }
        }

        self::$sectionChildKeys[$sectionKey] = $keys;
    }

    /**
     * @param array<string, mixed> $section
     */
    private static function walkSection(array $section, array $permKeyToId): void
    {
        if (! empty($section['children'])) {
            foreach ($section['children'] as $child) {
                self::walkChild($child, $permKeyToId);
            }
            return;
        }

        self::registerItem($section, $permKeyToId);
    }

    /**
     * @param array<string, mixed> $child
     */
    private static function walkChild(array $child, array $permKeyToId): void
    {
        if (! self::isCatalogMenuItem($child)) {
            return;
        }

        self::registerItem($child, $permKeyToId);
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function isCatalogMenuItem(array $item): bool
    {
        if (! empty($item['header']) || ! empty($item['disabled'])) {
            return false;
        }

        return trim((string) ($item['key'] ?? '')) !== '';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function registerItem(array $item, array $permKeyToId): void
    {
        $key = (string) $item['key'];
        if ($key === '') {
            return;
        }

        $label    = trim(preg_replace('/^[\s\-\|├└─]+/u', '', (string) ($item['label'] ?? '')) ?? '');
        $permKeys = self::resolveItemPermKeys($item);
        $permIds  = [];

        foreach ($permKeys as $pk) {
            if (isset($permKeyToId[$pk])) {
                $permIds[] = $permKeyToId[$pk];
            }
            self::$keyToMenuLabels[$pk][] = $label;
        }

        self::$itemIndex[$key] = [
            'label'    => $label,
            'permKeys' => $permKeys,
            'permIds'  => array_values(array_unique($permIds)),
        ];
    }

    /**
     * @param array<string, mixed> $section
     * @return array<string, mixed>|null
     */
    private static function normalizeSection(array $section): ?array
    {
        if (! empty($section['super_admin_only'])) {
            return null;
        }

        $children = [];
        if (! empty($section['children'])) {
            foreach ($section['children'] as $child) {
                $normalized = self::normalizeChild($child);
                if ($normalized !== null) {
                    $children[] = $normalized;
                }
            }
        }

        if ($children === [] && empty($section['key'])) {
            return null;
        }

        return [
            'key'      => $section['key'] ?? '',
            'label'    => $section['label'] ?? '',
            'icon'     => $section['icon'] ?? '',
            'children' => $children,
            'isSection' => true,
        ];
    }

    /**
     * @param array<string, mixed> $child
     * @return array<string, mixed>|null
     */
    private static function normalizeChild(array $child): ?array
    {
        if (! empty($child['header'])) {
            return [
                'header' => true,
                'label'  => $child['label'] ?? '',
            ];
        }

        if (! self::isCatalogMenuItem($child)) {
            return null;
        }

        $key = (string) $child['key'];
        self::buildIndexes();
        $meta = self::$itemIndex[$key] ?? ['permKeys' => [], 'permIds' => []];

        $sharedWith = [];
        foreach ($meta['permKeys'] as $pk) {
            foreach (self::$keyToMenuLabels[$pk] ?? [] as $label) {
                if ($label !== ($child['label'] ?? '')) {
                    $sharedWith[$label] = true;
                }
            }
        }

        return [
            'key'        => $key,
            'label'      => $child['label'] ?? '',
            'icon'       => $child['icon'] ?? '',
            'permKeys'   => $meta['permKeys'],
            'permIds'    => $meta['permIds'],
            'locked'     => $meta['permKeys'] === [],
            'sharedWith' => array_values(array_keys($sharedWith)),
            'superAdminOnly'      => ! empty($child['super_admin_only']),
            'directorQuizzesMenu' => ! empty($child['director_quizzes_menu']),
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @return list<string>
     */
    private static function resolveItemPermKeys(array $item): array
    {
        $keys = [];
        foreach ($item['perms'] ?? [] as $p) {
            $pk = strtolower(trim((string) $p));
            if ($pk !== '') {
                $keys[$pk] = true;
            }
        }

        $match = trim((string) ($item['match'] ?? ''), '/');
        if ($match !== '') {
            foreach (self::permKeysFromMatch($match) as $pk) {
                $keys[$pk] = true;
            }
        }

        return array_keys($keys);
    }

    /**
     * @return list<string>
     */
    private static function permKeysFromMatch(string $match): array
    {
        $match = trim($match, '/');
        if (! preg_match('#^admin/([^/]+)(?:/(.+))?$#', $match, $m)) {
            return [];
        }

        $segment = $m[1];
        $method  = isset($m[2]) && $m[2] !== '' ? strtolower($m[2]) : 'index';

        $aliases = [
            'employee-face-management' => ['controller' => 'EmployeeFaceAttendance', 'method' => 'management'],
            'employee-face-attendance' => ['controller' => 'EmployeeFaceAttendance', 'method' => 'index'],
            'employees_attendance'     => ['controller' => 'EmployeesAttendance', 'method' => 'index'],
            'students_absentees'       => ['controller' => 'StudentsAbsentees', 'method' => 'index'],
            'top_level_planning'       => ['controller' => 'TopLevelPlanning', 'method' => 'index'],
        ];

        if (isset($aliases[$segment])) {
            $controller = $aliases[$segment]['controller'];
            $method     = $aliases[$segment]['method'];
        } else {
            $resolved = self::controllerFromSegment($segment);
            if ($resolved === null) {
                return [];
            }
            $controller = $resolved;
        }

        return AdminControllerPermissions::resolveKeys($controller, $method);
    }

    private static function controllerFromSegment(string $segment): ?string
    {
        $candidates = [];

        if (str_contains($segment, '-')) {
            $parts      = explode('-', $segment);
            $candidates[] = implode('', array_map('ucfirst', $parts));
        }

        if (str_contains($segment, '_')) {
            $candidates[] = str_replace('_', '', ucwords($segment, '_'));
        } else {
            $candidates[] = ucfirst($segment);
        }

        foreach (array_unique($candidates) as $name) {
            if (class_exists('App\\Controllers\\Admin\\' . $name)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @return array<string, int>
     */
    private static function loadPermKeyToIdMap(): array
    {
        $db  = \Config\Database::connect();
        $map = [];
        $rows = $db->table('permissions')->select('id, permKey')->get()->getResult();

        foreach ($rows as $row) {
            $key = strtolower(trim((string) ($row->permKey ?? '')));
            if ($key !== '') {
                $map[$key] = (int) $row->id;
            }
        }

        return $map;
    }

    /**
     * @return array<int, true>
     */
    private static function loadAllowedPermIds(int $roleId): array
    {
        if ($roleId <= 0) {
            return [];
        }

        $db     = \Config\Database::connect();
        $out    = [];
        $rows   = $db->table('role_perms')
            ->where('roleID', $roleId)
            ->where('value', 1)
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $out[(int) $row->permID] = true;
        }

        return $out;
    }

    /**
     * @param array<int, true> $allowedPermIds
     * @return array<string, true>
     */
    private static function loadAllowedPermKeys(array $allowedPermIds): array
    {
        if ($allowedPermIds === []) {
            return [];
        }

        $db  = \Config\Database::connect();
        $out = [];
        $rows = $db->table('permissions')
            ->select('id, permKey')
            ->whereIn('id', array_keys($allowedPermIds))
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $key = strtolower(trim((string) ($row->permKey ?? '')));
            if ($key !== '') {
                $out[$key] = true;
            }
        }

        return $out;
    }

    /**
     * @param array<string, true> $allowedKeys
     * @param list<string> $permKeys
     */
    private static function roleHasAnyPermKey(array $allowedKeys, array $permKeys): bool
    {
        foreach ($permKeys as $pk) {
            if (isset($allowedKeys[strtolower($pk)])) {
                return true;
            }
        }

        return false;
    }
}
