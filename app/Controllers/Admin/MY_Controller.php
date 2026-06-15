<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Legacy\Ci3DbAdapter;
use App\Libraries\Legacy\LegacyInputAdapter;
use App\Libraries\Legacy\LegacyLoader;
use App\Libraries\Legacy\LegacyOutputAdapter;
use App\Libraries\Legacy\LegacySessionAdapter;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base for legacy CI3-style admin controllers (snake_case, $this->load->view, etc.).
 */
class MY_Controller extends BaseController
{
    /** @var Ci3DbAdapter */
    public $db;

    /** @var LegacySessionAdapter */
    public $session;

    /** @var LegacyInputAdapter */
    public $input;

    /** @var LegacyLoader */
    public $load;

    /** @var LegacyOutputAdapter */
    public $output;

    public function __construct()
    {
        $this->bootstrapLegacyEnvironment();
    }

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->bootstrapLegacyEnvironment();
    }

    /**
     * Wire CI3-style adapters. Must stay inside this class (request/response/template_data are protected).
     */
    protected function bootstrapLegacyEnvironment(): void
    {
        if (! defined('BASEPATH')) {
            define('BASEPATH', ROOTPATH);
        }

        $request  = service('request');
        $response = service('response');

        $this->db      = new Ci3DbAdapter(\Config\Database::connect());
        $this->session = new LegacySessionAdapter();
        $this->input   = new LegacyInputAdapter($request);
        $this->load    = new LegacyLoader();
        $this->output  = new LegacyOutputAdapter($response);

        helper(['campus', 'url', 'form', 'session', 'server', 'file', 'date', 'auth', 'language', 'role', 'permission']);
        $this->template_data['cdn_server'] = base_url();
    }
}
