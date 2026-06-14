<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\SchoolSetupProgress;

class GettingStarted extends BaseController
{
    public function index()
    {
        $ctx = SchoolSetupProgress::forCurrentUser();
        if ($ctx === null) {
            return redirect()->to(base_url('admin/login'));
        }

        $status = $ctx['status'];

        if ($status['is_complete']) {
            session()->setFlashdata('success', 'Your school setup is complete. Welcome to your dashboard!');

            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('admin/getting_started', [
            'setup' => $status,
        ]);
    }
}
