<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Parent/student portal authentication settings.
 */
class Portal extends BaseConfig
{
    /** Max failed login attempts per IP within the rate-limit window. */
    public int $loginRateLimitAttempts = 10;

    /** Rate-limit window in seconds (default: 15 minutes). */
    public int $loginRateLimitWindow = 900;
}
