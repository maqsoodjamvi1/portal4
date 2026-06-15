<?php

namespace App\Controllers;

class Home extends BaseController
{
    /**
     * Site root — never show the CodeIgniter welcome page.
     * Send admins/staff to dashboard or login; parent/student portal users to their dashboard or login.
     */
    public function index()
    {
        $session = session();

        if ($session->get('IsAuthorized') || $session->get('member_userid')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        if ($session->get('auth.logged_in')) {
            return redirect()->to(base_url('student/dashboard'));
        }

        $host = (string) ($this->request->getServer('HTTP_HOST') ?? '');
        if ($host === 'trial.timesoftsol.com') {
            return redirect()->to(base_url('signup'));
        }

        return redirect()->to(base_url('admin/login'));
    }
}
