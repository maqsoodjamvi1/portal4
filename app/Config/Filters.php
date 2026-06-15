<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, class-string|list<class-string>>
     *
     * [filter_name => classname]
     * or [filter_name => [classname1, classname2, ...]]
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'prefCurrency' => \App\Filters\PreferredCurrencyFilter::class,
        'localization' => \App\Filters\LocalizationFilter::class, // ← Changed from 'locale'
        'authguard' => \App\Filters\AuthGuard::class,
        'portalauth' => \App\Filters\PortalAuthFilter::class,
        'boardprepauth' => \App\Filters\BoardPrepAuthFilter::class,
        'adminpermission' => \App\Filters\AdminPermissionFilter::class,
        'schoolsetup'     => \App\Filters\SchoolSetupFilter::class,
        'hifzschema'      => \App\Filters\HifzSchemaFilter::class,
        'csrfrefresh'     => \App\Filters\CsrfRefreshFilter::class,
    ];

    /**
     * List of special required filters.
     *
     * The filters listed here are special. They are applied before and after
     * other kinds of filters, and always applied even if a route does not exist.
     *
     * Filters set by default provide framework functionality. If removed,
     * those functions will no longer work.
     *
     * @see https://codeigniter.com/user_guide/incoming/filters.html#provided-filters
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps',
        ],
        'after' => [
            'performance',
        ],
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array<string, array<string, array<string, string>>>|array<string, list<string>>
     */
    public array $globals = [
        'before' => [
            // 'honeypot',
            'pagecache' => [
                'except' => [
                    'admin',
                    'admin/*',
                    'student',
                    'student/*',
                    'parent',
                    'parent/*',
                    'prep',
                    'prep/*',
                ],
            ],
            'csrf' => [
                'except' => [
                    'admin/ajax/*',
                    // Read-heavy admin AJAX (auth + RBAC still apply)
                    'admin/**/search*',
                    'admin/**/get-student*',
                    'admin/**/get_student*',
                    'admin/**/get-parent*',
                    'admin/**/get_parent*',
                    'admin/**/get_parentinfo*',
                    'admin/**/autocomplete*',
                    'admin/fee-chalan/search*',
                    'admin/fee-chalan/get-*',
                    'admin/fee-chalan-pay/get-*',
                    'admin/fee-chalan-pay/data',
                    'admin/students_print/*',
                    'admin/students-print/*',
                    'admin/students/search*',
                    'admin/students_absentees/*',
                    'api/captcha',
                    'prep/api/captcha',
                    'uploads/*',
                ],
            ],
            'prefCurrency',
            'localization',
        ],
        'after' => [
            'pagecache' => [
                'except' => [
                    'admin',
                    'admin/*',
                    'student',
                    'student/*',
                    'parent',
                    'parent/*',
                    'prep',
                    'prep/*',
                ],
            ],
            'csrfrefresh',
            // 'honeypot',
            'secureheaders',
        ],
    ];




    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'POST' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [
        'adminpermission' => [
            'before' => [
                'admin',
                'admin/*',
            ],
        ],
        'schoolsetup' => [
            'before' => [
                'admin',
                'admin/*',
            ],
        ],
        'hifzschema' => [
            'before' => [
                'admin/hifz',
                'admin/hifz/*',
            ],
        ],
    ];
    // shafiq nay comment kiya hay
    // // Protect the dashboard & student switch
    // public $filters = [
    //     'authguard' => [
    //         'before' => ['dashboard', 'student/switch/*'],
    //     ],
    // ];
}
