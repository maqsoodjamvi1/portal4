<?php

/**
 * Hifz schema bootstrap helpers (included from hifz_helper.php).
 */

if (! function_exists('hifz_ensure_database_schema')) {
    function hifz_ensure_database_schema(): void
    {
        static $done = false;

        if ($done) {
            return;
        }

        $done = true;

        try {
            (new \App\Libraries\HifzSchemaEnsurer())->ensure();
        } catch (\Throwable $e) {
            log_message('error', '[HifzSchema] ' . $e->getMessage());
        }
    }
}

if (! function_exists('campusHifzEnabled')) {
    function campusHifzEnabled(?object $campus = null): bool
    {
        $campus = $campus ?? getCampusInfo();

        return $campus && (int) ($campus->hfz_flag ?? 0) === 1;
    }
}
