<?php

namespace Config;

/**
 * Canonical permission keys for seeding and RBAC audits.
 */
class PermissionKeyRegistry
{
    /**
     * Keys referenced in config but not covered by RolePermissionMap module keys alone.
     *
     * @var list<string>
     */
    public static array $extraKeys = [
        'admin-health-bmi',
        'admin-health-bmi-dashboard',
        'admin-health-bmi-records',
        'admin-health-alerts',
        'admin-health-nutrition',
        'admin-health-reports',
        'admin-employee-face-attendance',
        'admin-employee-face-management',
        'admin-math-crossword',
        'admin-math-worksheet',
        'admin-word-search',
        'student-crossword',
        'student-word-search',
        'admin-question-bank-overview',
        'admin-question-bank-proof',
        'admin-question-bank-ai',
        'admin-question-paper',
        'admin-users-bulk-info',
        'admin-students-bulk-photos',
        'admin-advance-fee',
        'admin-sports-events',
        'admin-sports-teams',
        'admin-sports-mapping',
        'admin-sports-reports',
        'admin-hifz-sections',
        'admin-hifz-students',
        'admin-hifz-teachers',
        'admin-hifz-recitation',
        'admin-hifz-reports',
        'admin-quiz-battles',
        'admin-quiz-assign',
        'admin-vocab-report',
        'admin-qb-topics',
        'admin-qb-board-publishers',
        'admin-vocab-topics',
        'admin-vocab-bank',
        'admin-vocab-words',
        'admin-salary-debug',
        'admin-salary-settings',
        'admin-salary-bulk-adjustment',
        'admin-salary-slips',
        'admin-salary-reports',
        'admin-salary-advance',
        'admin-salary-bonuses',
        'admin-add-salary-settings',
        'admin-edit-salary-settings',
        'admin-fee-setup',
        'admin-fee-chalan-daily-collection',
        'admin-timetable-generator',
        'admin-grades-setup',
        'admin-student-id-card-new',
        'admin-bonafide-certificate',
        'admin-diary-analytics',
        'admin-datesheet-report',
        'admin-test-result-message',
        'admin-audio-lecture',
        'admin-add-audio-lecture',
        'admin-edit-audio-lecture',
        'admin-del-audio-lecture',
        'admin-subject-category-topics',
        'admin-add-subject-category-topic',
        'admin-edit-subject-category-topic',
        'admin-del-subject-category-topic',
        'admin-rooms',
        'admin-add-rooms',
        'admin-edit-rooms',
        'admin-room-beds',
        'admin-add-room-beds',
        'admin-student-result-report',
        'admin-results',
        'admin-reports',
        'admin-scheme-of-studies',
        'admin-video-lecture',
        'admin-wp-objectives',
        'admin-award-list',
        'admin-pages',
        'admin-add-page',
        'admin-edit-page',
        'admin-del-page',
        'admin-recordings',
        'admin-campus-chalan-pay',
        'admin-add-campus-chalan-pay',
        'admin-edit-campus-chalan-pay',
        'admin-pay-campus-bill',
        'admin-ajax',
        'admin-finance-accounts',
        'admin-add-finance-accounts',
        'admin-edit-finance-accounts',
        'admin-cash-flow-report',
    ];

    /**
     * @return list<string> Lowercase unique permission keys
     */
    public static function allKeys(): array
    {
        $keys = [];

        foreach (self::$extraKeys as $key) {
            $keys[strtolower(trim($key))] = true;
        }

        foreach (RolePermissionMap::$modules as $module) {
            foreach ($module['keys'] ?? [] as $key) {
                $normalized = strtolower(trim($key));
                if ($normalized !== '') {
                    $keys[$normalized] = true;
                }
            }
        }

        foreach (AdminControllerPermissions::collectMappedKeys() as $key) {
            $keys[$key] = true;
        }

        foreach (self::scanCodebaseKeys() as $key) {
            $keys[$key] = true;
        }

        $sorted = array_keys($keys);
        sort($sorted);

        return $sorted;
    }

    /**
     * Human-readable label from permKey.
     */
    public static function labelFromKey(string $permKey): string
    {
        $label = preg_replace('/^admin-/', '', strtolower($permKey)) ?? $permKey;
        $label = str_replace('-', ' ', $label);

        return ucwords($label);
    }

    /**
     * Scan admin controllers/views for admin-* permission literals.
     *
     * @return list<string>
     */
    public static function scanCodebaseKeys(): array
    {
        $keys = [];
        $patterns = [
            '/check_permission\s*\(\s*[\'"](admin-[^\'"]+)[\'"]/',
            '/hasPermission\s*\(\s*[\'"](admin-[^\'"]+)[\'"]/',
            '/[\'"]perms[\'"]\s*=>\s*\[\s*[\'"](admin-[^\'"]+)[\'"]/',
        ];

        $roots = [
            APPPATH . 'Controllers/Admin',
            APPPATH . 'Views/admin',
            APPPATH . 'Views/layouts',
            APPPATH . 'Libraries',
        ];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $content = @file_get_contents($file->getPathname());
                if ($content === false) {
                    continue;
                }

                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        foreach ($matches[1] as $key) {
                            $normalized = strtolower(trim($key));
                            if ($normalized !== '') {
                                $keys[$normalized] = true;
                            }
                        }
                    }
                }
            }
        }

        return array_keys($keys);
    }
}
