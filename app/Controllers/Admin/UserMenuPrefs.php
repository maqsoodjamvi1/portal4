<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserMenuPrefsModel;

class UserMenuPrefs extends BaseController
{
    public function get()
    {
        $uid = (int) session('member_userid');
        if (!$uid) return $this->response->setStatusCode(401)->setJSON(['error'=>'Not logged in']);

        $m   = new UserMenuPrefsModel();
        $row = $m->find($uid);
        $prefs = $row ? (is_string($row['prefs']) ? json_decode($row['prefs'], true) : $row['prefs']) : ['hidden'=>[]];

        return $this->response->setJSON(['prefs' => $prefs]);
    }

    public function save()
    {
        $uid = (int) session('member_userid');
        if (!$uid) return $this->response->setStatusCode(401)->setJSON(['error'=>'Not logged in']);

        // Accept JSON or form
        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $hidden  = array_values(array_unique(array_map('strval', $payload['hidden'] ?? [])));

        // Optional: clamp to a whitelist of known keys (for safety)
        // $hidden = array_values(array_intersect($hidden, $this->knownMenuKeys()));

        $m = new UserMenuPrefsModel();
        $m->save(['user_id'=>$uid, 'prefs'=>json_encode(['hidden'=>$hidden])]);

        return $this->response->setJSON(['success'=>true]);
    }
}
?>