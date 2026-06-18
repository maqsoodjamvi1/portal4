<?php

namespace App\Controllers\BoardPrep;

use App\Libraries\BoardPrepProvisioningService;
use App\Libraries\BoardPrepQuizCatalogService;

class Auth extends BoardPrepBaseController
{
    public function landing()
    {
        if (board_prep_auth()) {
            return redirect()->to(board_prep_url('dashboard'));
        }

        // Public quiz site (liveeducationquiz.com) gets the marketing quiz landing.
        if (board_prep_is_public_quiz_host()) {
            $quizzes = (new BoardPrepQuizCatalogService())->loadAllPublished();

            return view('board_prep/quiz_landing', [
                'productName'     => board_prep_product_name(),
                'featuredQuizzes' => array_slice($quizzes, 0, 6),
                'allQuizzes'      => $quizzes,
                'dashboardUrl'    => board_prep_url('dashboard'),
                'signupUrl'       => board_prep_url('signup'),
                'loginUrl'        => board_prep_url('login'),
            ]);
        }

        return view('board_prep/landing', [
            'productName' => board_prep_product_name(),
        ]);
    }

    public function login()
    {
        if (board_prep_auth()) {
            return redirect()->to(board_prep_url('dashboard'));
        }

        return view('board_prep/login', [
            'productName' => board_prep_product_name(),
            'error'       => session()->getFlashdata('error'),
            'success'     => session()->getFlashdata('success'),
        ]);
    }

    public function doLogin()
    {
        if (! $this->request->is('post')) {
            return redirect()->to(board_prep_url('login'));
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        $service = new BoardPrepProvisioningService();
        $result  = $service->authenticate($username, $password);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->withInput()->with('error', $result['msg'] ?? 'Login failed.');
        }

        $service->establishSession($result['user']);

        return redirect()->to(board_prep_url('dashboard'));
    }

    public function logout()
    {
        session()->remove('board_prep_auth');
        session()->remove('auth');
        session()->remove('student_id');

        return redirect()->to(board_prep_url('login'))->with('success', 'You have been logged out.');
    }
}
