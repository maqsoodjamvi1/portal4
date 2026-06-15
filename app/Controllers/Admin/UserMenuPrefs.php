<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\UserMenuPrefsLibrary;

class UserMenuPrefs extends BaseController
{
    public function get()
    {
        $uid = (int) session('member_userid');
        if (! $uid) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        $map    = UserMenuPrefsLibrary::loadMapForUser($uid);
        $hidden = UserMenuPrefsLibrary::toHiddenList($map);

        return $this->response->setJSON([
            'prefs'  => ['hidden' => $hidden],
            'map'    => $map,
        ]);
    }

    public function save()
    {
        $uid = (int) session('member_userid');
        if (! $uid) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        $map = UserMenuPrefsLibrary::parseRequestPayload($this->request);
        if ($map === null) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid preferences payload']);
        }

        UserMenuPrefsLibrary::saveForUser($uid, $map);

        return $this->response->setJSON([
            'success' => true,
            'prefs'   => ['hidden' => UserMenuPrefsLibrary::toHiddenList($map)],
        ]);
    }
}
