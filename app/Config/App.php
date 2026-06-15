<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     * Set `app.baseURL` in project root `.env` on production (trailing slash required).
     * If unset, the default below is used (local XAMPP).
     */
    public string $baseURL = 'http://localhost/school-management-system/public/';

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT !== 'production') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }
    }

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     * e.g. ['demo.yourdomain.com', 'trial.yourdomain.com']
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     */
    public string $indexPage = 'index.php';

    /**
     * --------------------------------------------------------------------------
     * URI Protocol
     * --------------------------------------------------------------------------
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * --------------------------------------------------------------------------
     * Allowed URL Characters
     * --------------------------------------------------------------------------
     */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * --------------------------------------------------------------------------
     * Locale
     * --------------------------------------------------------------------------
     */
    // public string $defaultLocale   = 'en';
    // public bool   $negotiateLocale = false;
    // public array  $supportedLocales = ['en'];
    // app/Config/App.php
    public string $defaultLocale     = 'en';
    public array  $supportedLocales  = ['en','ur','ar'];
    public bool   $negotiateLocale   = false; // we'll enforce session instead of browser


    /**
     * --------------------------------------------------------------------------
     * Timezone
     * --------------------------------------------------------------------------
     */
    public string $appTimezone = 'Asia/Karachi';

    /**
     * --------------------------------------------------------------------------
     * Charset
     * --------------------------------------------------------------------------
     */
    public string $charset = 'UTF-8';

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     * Set to true if you serve strictly over HTTPS (recommended in production).
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     */
    public array $proxyIPs = [];

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     */
    public bool $CSPEnabled = false;

    // --------------------------------------------------------------------------
    // 🟦 Cookie Settings (affect session cookie too)
    // --------------------------------------------------------------------------

    /**
     * Leave prefix empty unless you need to avoid collisions.
     */
    public string $cookiePrefix   = '';
    /**
     * If you use subdomains (e.g., demo.yourdomain.com), set: '.yourdomain.com'
     * Keep empty for localhost.
     */
    public string $cookieDomain   = '';
    public string $cookiePath     = '/';
    /**
     * If your site runs on HTTPS, set to true.
     */
    public bool   $cookieSecure   = false;
    public bool   $cookieHTTPOnly = true;
    /**
     * 'Lax' is safe for most sites. Use 'None' only when you need cross-site
     * cookies AND you are on HTTPS (then cookieSecure must be true).
     */
    public string $cookieSameSite = 'Lax';

    // --------------------------------------------------------------------------
    // 🟩 Session Settings (make session persistent & stable)
    // --------------------------------------------------------------------------

    /**
     * Use FileHandler unless you specifically use database cluster sessions.
     * For DB sessions, set to: 'CodeIgniter\Session\Handlers\DatabaseHandler'
     */
    public string $sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler';

    /**
     * The name of the session cookie.
     */
    public string $sessionCookieName = 'ci_session';

    /**
     * Session lifetime (in seconds).
     * You had ~10 years; keep it if you truly want “never expires”.
     * A saner long setting is 30 days: 60*60*24*30
     */
    public int $sessionExpiration = 315360000; // ~10 years

    /**
     * IMPORTANT: Must be a writable directory (NOT empty).
     * Using WRITEPATH keeps session files out of OS /tmp cleanups.
     * Ensure folder exists: writable/session
     */
    public string $sessionSavePath = WRITEPATH . 'session';

    /**
     * Match session to IP. Usually false (users on mobile networks change IPs).
     */
    public bool $sessionMatchIP = false;

    /**
     * How often (seconds) to regenerate the session ID.
     * Higher value reduces cookie churn for “always logged-in”.
     */
    /** @deprecated Use Config\Session::$timeToUpdate (kept in sync for reference). */
    public int $sessionTimeToUpdate = 1800; // 30 minutes

    /**
     * Whether to destroy old session data on regenerate.
     */
    public bool $sessionRegenerateDestroy = false;
}
