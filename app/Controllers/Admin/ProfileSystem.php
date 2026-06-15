<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ProfileSystem extends BaseController
{
    protected $db;
    protected $session;
    protected $validation;

    public function __construct()
    {
        $this->db         = \Config\Database::connect();
        $this->session    = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        helper(['form', 'filesystem', 'text', 'custom']); // Ensure 'custom' contains `json_response()`
    }

    public function index()
    {
        $schoolinfo = getSchoolInfo(); // Assuming still available globally
        $sms_settings_info = $this->db->table('sms_settings')
                                      ->where('campus_id', $schoolinfo->system_id)
                                      ->get()
                                      ->getRow();

        return view('admin/profile_system', [
            'info'               => $schoolinfo,
            'sms_settings_info' => $sms_settings_info
        ]);
    }

    public function save()
    {
        $request    = service('request');
        $user_id    = $this->session->get('member_userid');
        $schoolinfo = getSchoolInfo();
        $id         = (int) $request->getPost('id');
        $date       = date('Y-m-d H:i:s');

        $image      = $this->uploadImage('image');
        $image2     = $this->uploadImage('image2');

        $this->db->transStart();

        $data = [
            'system_name'      => trim($request->getPost('system_name')),
            'address'          => trim($request->getPost('address')),
            'city'             => trim($request->getPost('city')),
            'state'            => trim($request->getPost('state')),
            'zip'              => trim($request->getPost('zip')),
            'country'          => trim($request->getPost('country')),
            'owner_name'       => trim($request->getPost('owner_name')),
            'landline_number'  => trim($request->getPost('landline_number')),
            'mob_number'       => trim($request->getPost('mob_number')),
            'reg_text'         => trim($request->getPost('reg_text')),
            'slogan'           => trim($request->getPost('slogan')),
            'updated_date'     => $date,
            'user_id'          => $user_id
        ];

        $this->db->table('system')->where('system_id', $id)->update($data);

        if (!empty($image)) {
            $this->db->table('system')->where('system_id', $id)->update([
                'logo'         => $image,
                'updated_date' => $date,
                'user_id'      => $user_id
            ]);
        }

        if (!empty($image2)) {
            $this->db->table('system')->where('system_id', $id)->update([
                'chalan_header' => $image2,
                'updated_date'  => $date,
                'user_id'       => $user_id
            ]);
        }

        $this->db->transComplete();

        $session = $this->db->table('academic_session')
                            ->where('system_id', $schoolinfo->system_id)
                            ->get()
                            ->getRow();

        if (empty($session->session_id)) {
            return $this->response->setJSON(['session_id' => false, 'msg' => 'Update System Success']);
        }

        json_response(['success' => true, 'msg' => 'Update System Success']);
    }

    public function updateRegText()
    {
        $systemID = (int) $this->request->getPost('systemID');
        $reg_text = strtoupper(trim((string) $this->request->getPost('reg_text')));

        if ($systemID <= 0) {
            json_response(['success' => false, 'msg' => lang('SchoolSetup.reg_text_invalid_school')]);
        }

        if (! preg_match('/^[A-Z0-9]{2,3}$/', $reg_text)) {
            json_response(['success' => false, 'msg' => lang('SchoolSetup.reg_text_invalid')]);
        }

        $this->db->table('system')->where('system_id', $systemID)->update(['reg_text' => $reg_text]);

        $this->session->set(['member_reg_text' => $reg_text]);

        json_response(['success' => true, 'msg' => lang('SchoolSetup.reg_text_saved')]);
    }

    public function update_password()
    {
        $rules = ['password' => 'required'];
        if (!$this->validate($rules)) {
            json_response(['success' => false, 'msg' => validation_list_errors()]);
        }

        $user_id = (int) $this->request->getPost('user_id');
        $data    = ['password' => password_hash(trim($this->request->getPost('password')), PASSWORD_BCRYPT)];

        $this->db->table('users')->where('id', $user_id)->update($data);

        json_response(['success' => true, 'msg' => 'Change Password Success']);
    }

    private function uploadImage(string $field)
    {
        $file = $this->request->getFile($field);
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/system-logo', $newName);
            return $newName;
        }
        return null;
    }
}
