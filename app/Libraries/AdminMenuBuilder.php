<?php

namespace App\Libraries;

/**
 * Builds admin sidebar menu sections and resolves breadcrumbs from the menu tree.
 */
class AdminMenuBuilder
{
    /**
     * @param array{
     *   link?: callable,
     *   can?: callable,
     *   canAny?: callable,
     *   hasTransport?: bool,
     *   hasHostel?: bool,
     *   hasAcademy?: bool,
     *   hasHifz?: bool,
     *   role_name_info?: object|null
     * } $ctx
     */
    public static function build(array $ctx): array
    {
        $link = $ctx['link'] ?? static fn (string $path): string => base_url($path);

        $can = $ctx['can'] ?? static fn (string $perm): bool => function_exists('hasPermission') ? hasPermission($perm) : false;

        $canAny = $ctx['canAny'] ?? static function (array $perms) use ($can): bool {
            foreach ($perms as $p) {
                if ($can($p)) {
                    return true;
                }
            }
            return false;
        };

        $hasTransport   = (bool) ($ctx['hasTransport'] ?? false);
        $hasHostel      = (bool) ($ctx['hasHostel'] ?? false);
        $hasAcademy     = (bool) ($ctx['hasAcademy'] ?? false);
        $hasHifz        = (bool) ($ctx['hasHifz'] ?? false);
        $role_name_info = $ctx['role_name_info'] ?? null;

        $sections = [];
        $menuFile = __DIR__ . '/AdminMenuSections.inc.php';
        if (! is_file($menuFile)) {
            return [];
        }
        include $menuFile;

        return is_array($sections) ? $sections : [];
    }

    /**
     * Resolve breadcrumb trail for the current admin path.
     *
     * @return list<array{label: string, url: string|null, active: bool}>
     */
    public static function resolveBreadcrumb(string $currentPath, array $sections): array
    {
        $currentPath = trim($currentPath, '/');

        if ($currentPath === '' || $currentPath === 'admin/dashboard') {
            return [
                ['label' => 'Dashboard', 'url' => base_url('admin/dashboard'), 'active' => true],
            ];
        }

        $bestTrail    = [];
        $bestMatchLen = -1;

        $scan = static function (array $items, array $ancestors) use (&$scan, &$bestTrail, &$bestMatchLen, $currentPath): void {
            foreach ($items as $item) {
                if (!empty($item['header'])) {
                    continue;
                }

                $label = trim(preg_replace('/^[\s\-\|├└─]+/u', '', (string) ($item['label'] ?? '')) ?? '');
                if ($label === '' || !empty($item['disabled'])) {
                    if (!empty($item['children'])) {
                        $scan($item['children'], $ancestors);
                    }
                    continue;
                }

                $match = trim((string) ($item['match'] ?? ''), '/');

                if (!empty($item['children'])) {
                    $groupTrail = array_merge($ancestors, [['label' => $label, 'url' => null]]);
                    $scan($item['children'], $groupTrail);
                }

                if ($match === '') {
                    continue;
                }

                foreach (array_filter(array_map('trim', explode('|', $match))) as $needle) {
                    if ($needle === '') {
                        continue;
                    }
                    if ($currentPath === $needle || str_starts_with($currentPath, $needle . '/')) {
                        $len = strlen($needle);
                        if ($len > $bestMatchLen) {
                            $bestMatchLen = $len;
                            $bestTrail = array_merge($ancestors, [
                                ['label' => $label, 'url' => $item['url'] ?? null],
                            ]);
                        }
                    }
                }
            }
        };

        foreach ($sections as $sec) {
            if (!empty($sec['children'])) {
                $scan($sec['children'], [['label' => (string) ($sec['label'] ?? ''), 'url' => null]]);
            } elseif (!empty($sec['match'])) {
                $scan([$sec], []);
            }
        }

        if ($bestTrail === []) {
            $fallback = ucwords(str_replace(['-', '_'], ' ', (string) basename($currentPath)));
            return [
                ['label' => 'Dashboard', 'url' => base_url('admin/dashboard'), 'active' => false],
                ['label' => $fallback, 'url' => null, 'active' => true],
            ];
        }

        array_unshift($bestTrail, ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')]);

        $last = count($bestTrail) - 1;
        foreach ($bestTrail as $i => &$crumb) {
            $crumb['active'] = ($i === $last);
        }
        unset($crumb);

        return $bestTrail;
    }

    /**
     * Flat list of navigable menu links for command palette / search.
     *
     * @return list<array{key: string, label: string, url: string, icon: string, section: string}>
     */
    public static function flattenNavIndex(array $sections, ?callable $canAny = null, ?callable $itemVisible = null): array
    {
        $index = [];

        $walk = static function (array $items, string $sectionLabel) use (&$walk, &$index, $canAny, $itemVisible): void {
            foreach ($items as $item) {
                if (! empty($item['header']) || ! empty($item['disabled'])) {
                    continue;
                }

                if ($itemVisible !== null) {
                    if (! $itemVisible($item)) {
                        if (! empty($item['children'])) {
                            $walk($item['children'], $sectionLabel);
                        }
                        continue;
                    }
                } elseif (! empty($item['perms']) && $canAny !== null) {
                    if (! $canAny($item['perms'])) {
                        if (! empty($item['children'])) {
                            $walk($item['children'], $sectionLabel);
                        }
                        continue;
                    }
                }

                $label = trim(preg_replace('/^[\s\-\|├└─]+/u', '', (string) ($item['label'] ?? '')) ?? '');
                $url   = trim((string) ($item['url'] ?? ''));

                if ($label !== '' && $url !== '' && $url !== '#' && ! str_starts_with($url, 'javascript')) {
                    $index[] = [
                        'key'     => (string) ($item['key'] ?? ''),
                        'label'   => $label,
                        'url'     => $url,
                        'icon'    => (string) ($item['icon'] ?? 'far fa-circle'),
                        'section' => $sectionLabel,
                    ];
                }

                if (! empty($item['children'])) {
                    $walk($item['children'], $sectionLabel);
                }
            }
        };

        foreach ($sections as $sec) {
            $sectionLabel = trim((string) ($sec['label'] ?? ''));
            if (! empty($sec['url']) && empty($sec['children'])) {
                $walk([$sec], $sectionLabel);
            }
            if (! empty($sec['children'])) {
                $walk($sec['children'], $sectionLabel);
            }
        }

        return $index;
    }

    /**
     * @param list<string> $favoriteKeys
     */
    public static function filterFavorites(array $sections, array $favoriteKeys): array
    {
        if ($favoriteKeys === []) {
            return [];
        }

        $lookup = array_fill_keys($favoriteKeys, true);
        $found  = [];

        $walk = static function (array $items) use (&$walk, &$found, $lookup): void {
            foreach ($items as $item) {
                $key = $item['key'] ?? null;
                if ($key && isset($lookup[$key]) && ! empty($item['url']) && empty($item['disabled'])) {
                    $found[$key] = $item;
                }
                if (! empty($item['children'])) {
                    $walk($item['children']);
                }
            }
        };

        foreach ($sections as $sec) {
            if (! empty($sec['key']) && isset($lookup[$sec['key']]) && ! empty($sec['url'])) {
                $found[$sec['key']] = $sec;
            }
            if (! empty($sec['children'])) {
                $walk($sec['children']);
            }
        }

        $ordered = [];
        foreach ($favoriteKeys as $key) {
            if (isset($found[$key])) {
                $ordered[] = $found[$key];
            }
        }

        return $ordered;
    }
}
