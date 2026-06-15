<?php

namespace App\Libraries;

use App\Models\Frontend\AuthModel;
use Config\Portal;

/**
 * Portal login: password verification, rate limiting, session hardening.
 */
class PortalAuthService
{
    public function __construct(
        private ?AuthModel $authModel = null,
        private ?Portal $config = null,
    ) {
        $this->authModel = $authModel ?? new AuthModel();
        $this->config    = $config ?? config('Portal');
    }

    public function checkLoginRateLimit(): bool
    {
        $throttler = service('throttler');
        $key       = 'portal_login_' . md5((string) service('request')->getIPAddress());

        return $throttler->check(
            $key,
            (int) $this->config->loginRateLimitAttempts,
            (int) $this->config->loginRateLimitWindow,
        );
    }

    public function verifyPassword(string $plain, ?string $storedHash): bool
    {
        return $this->authModel->verifyPortalPassword($plain, $storedHash);
    }

    /**
     * Regenerate session ID after successful authentication (mitigates fixation).
     */
    public function regenerateSession(): void
    {
        session()->regenerate(true);
    }
}
