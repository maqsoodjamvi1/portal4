<?php

namespace App\Controllers\BoardPrep;

use App\Libraries\BoardPrepProfileService;

class Profile extends BoardPrepBaseController
{
    public function index()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $auth   = board_prep_auth();
        $userId = (int) ($auth['user_id'] ?? 0);
        $svc    = new BoardPrepProfileService();
        $profile = $svc->loadForUser($userId);

        if (! $profile) {
            return redirect()->to(board_prep_url('dashboard'))
                ->with('error', 'Profile could not be loaded.');
        }

        helper('server');

        return view('board_prep/profile', [
            'auth'        => $auth,
            'profile'     => $profile,
            'productName' => board_prep_product_name(),
            'navActive'   => 'profile',
            'success'     => session()->getFlashdata('success'),
            'error'       => session()->getFlashdata('error'),
        ]);
    }

    public function update()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to(board_prep_url('profile'));
        }

        $auth   = board_prep_auth();
        $userId = (int) ($auth['user_id'] ?? 0);
        $svc    = new BoardPrepProfileService();

        $result = $svc->update($userId, $this->request->getPost(), $this->request->getFile('profile_photo'));

        if (! ($result['success'] ?? false)) {
            return redirect()->to(board_prep_url('profile'))
                ->withInput()
                ->with('error', $result['msg'] ?? 'Could not save profile.');
        }

        $profile = $result['profile'] ?? null;
        if ($profile) {
            session()->set('board_prep_auth', array_merge($auth, [
                'father_name'  => (string) ($profile->father_name ?? ''),
                'display_name' => (string) ($profile->display_name ?? $auth['display_name'] ?? ''),
            ]));
        }

        return redirect()->to(board_prep_url('profile'))
            ->with('success', 'Profile updated successfully.');
    }
}
