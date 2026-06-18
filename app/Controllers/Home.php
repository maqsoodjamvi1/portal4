<?php

namespace App\Controllers;

use App\Libraries\BoardPrepQuizCatalogService;

class Home extends BaseController
{
    /**
     * Site root — never show the CodeIgniter welcome page.
     * Send admins/staff to dashboard or login; parent/student portal users to their dashboard or login.
     */
    public function index()
    {
        $host = strtolower((string) ($this->request->getServer('HTTP_HOST') ?? ''));
        $host = preg_replace('/:\d+$/', '', $host) ?: $host;

        // Dedicated public quiz domain: root must render, not redirect to itself.
        if ($host === 'liveeducationquiz.com' || $host === 'www.liveeducationquiz.com') {
            helper('board_prep');

            if (board_prep_auth()) {
                return redirect()->to(board_prep_url('dashboard'));
            }

            return view('board_prep/quiz_landing', [
                'productName'     => board_prep_product_name(),
                'featuredQuizzes' => array_slice((new BoardPrepQuizCatalogService())->loadAllPublished(), 0, 6),
                'dashboardUrl'    => board_prep_url('dashboard'),
                'signupUrl'       => board_prep_url('signup'),
                'loginUrl'        => board_prep_url('login'),
            ]);
        }

        $session = session();

        if ($session->get('IsAuthorized') || $session->get('member_userid')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        if ($session->get('auth.logged_in')) {
            return redirect()->to(base_url('student/dashboard'));
        }

        if ($host === 'trial.timesoftsol.com') {
            return redirect()->to(base_url('signup'));
        }

        return redirect()->to(base_url('admin/login'));
    }
}
