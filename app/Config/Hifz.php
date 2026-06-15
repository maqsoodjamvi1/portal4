<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Hifz program settings.
 */
class Hifz extends BaseConfig
{
    /**
     * On each admin/hifz request, run pending app migrations (replaces manual php spark migrate).
     */
    /** Disabled in production — run `php spark migrate` on deploy instead. */
    public bool $autoMigrate = ENVIRONMENT !== 'production';

    /**
     * Add missing Hifz columns directly if migrations did not run (safe, idempotent).
     */
    public bool $autoEnsureColumns = true;

    /**
     * Seconds between full migration attempts (reduces load on live).
     */
    public int $migrateThrottleSeconds = 300;
}
