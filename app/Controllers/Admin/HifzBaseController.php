<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

abstract class HifzBaseController extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'hifz']);
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        hifz_ensure_database_schema();
        $this->requireHifzCampus();
    }

    abstract protected function hifzPermission(): string;

    protected function bootHifzController(): void
    {
        check_permission($this->hifzPermission());
    }

    protected function requireHifzCampus(): void
    {
        if (campusHifzEnabled()) {
            return;
        }

        if ($this->request->isAJAX()) {
            json_response([
                'success' => false,
                'msg'     => 'Enable Hifz Program in Campus Profile → Services tab.',
            ]);
        }

        redirect()->to(base_url('admin/#/profile_campus'))
            ->with('error', 'Enable Hifz Program in Campus Profile → Services tab.')
            ->send();
        exit;
    }

    protected function campusId(): int
    {
        return (int) $this->session->get('member_campusid');
    }

    protected function sessionId(): int
    {
        return (int) $this->session->get('member_sessionid');
    }

    protected function userId(): int
    {
        return (int) $this->session->get('member_userid');
    }
}
