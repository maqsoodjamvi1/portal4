<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;

class Profile extends BaseController
{
    protected $db;
    protected $validation;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        return $this->showProfile();
    }

    public function edit()
    {
        return $this->showProfile();
    }

    /**
     * Legacy alias used by permission map / older links.
     */
    public function editself()
    {
        return $this->showProfile();
    }

    protected function showProfile()
    {
        helper(['form', 'url']);

        $userId = (int) (session()->get('member_userid') ?? 0);
        if ($userId <= 0) {
            return redirect()->to(base_url('admin/login'));
        }

        $user = $this->db->table('users')->where('id', $userId)->get()->getRow();
        if (! $user) {
            return redirect()->to(base_url('admin/dashboard'))
                ->with('error', 'User not found.');
        }

        return view('admin/profile', ['user' => $user]);
    }

    public function save()
    {
        helper(['form', 'role']);

        $id = (int) $this->request->getPost('id');
        $userId = (int) (session()->get('member_userid') ?? 0);

        if ($id <= 0 || $userId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Invalid user.',
            ])->setStatusCode(400);
        }

        if ($id !== $userId && ! canFullEditEmployeeProfile()) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'You can only update your own profile.',
            ])->setStatusCode(403);
        }

        $photoName = '';

        // Handle file upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'employees-img', $newName);
            $photoName = $newName;
        }

        $data = [
            'email' => trim($this->request->getPost('email')),
            'first_name' => trim($this->request->getPost('first_name')),
            'last_name' => trim($this->request->getPost('last_name')),
            'dob' => trim($this->request->getPost('dob')),
            'joining_date' => trim($this->request->getPost('joining_date')),
            'f_name' => trim($this->request->getPost('f_name')),
            'cnic' => trim($this->request->getPost('cnic')),
            'gender' => trim($this->request->getPost('gender')),
            'marital_status' => trim($this->request->getPost('marital_status')),
            'mobile_no' => trim($this->request->getPost('mobile_no')),
            'mobile_no2' => trim($this->request->getPost('mobile_no2')),
            'address' => trim($this->request->getPost('address')),
            'emergency_contact_person' => trim($this->request->getPost('emergency_contact_person')),
            'emergency_contact_no' => trim($this->request->getPost('emergency_contact_no')),
            'qualification' => trim($this->request->getPost('qualification')),
            'experience' => trim($this->request->getPost('experience')),
            'skills' => trim($this->request->getPost('skills')),
        ];

        $this->db->transStart();
        $this->db->table('users')->where('id', $id)->update($data);

        if (!empty($photoName)) {
            $this->db->table('users')->where('id', $id)->update(['photo' => $photoName]);
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Edit User Success']);
    }

    public function update_password()
    {
        helper(['form']);

        $rules = [
            'old_password' => 'required|min_length[6]',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => $this->validation->getErrors()
            ]);
        }

        $user_id = (int) $this->request->getPost('user_id');
        $sessionUserId = (int) (session()->get('member_userid') ?? 0);

        if ($user_id <= 0 || $sessionUserId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Invalid user.',
            ])->setStatusCode(400);
        }

        if ($user_id !== $sessionUserId) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'You can only change your own password.',
            ])->setStatusCode(403);
        }

        $oldPassword = trim((string) $this->request->getPost('old_password'));
        $newPassword = trim((string) $this->request->getPost('password'));

        $user = $this->db->table('users')->where('id', $user_id)->get()->getRow();
        if (!$user || empty($user->password) || !password_verify($oldPassword, $user->password)) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Current password is incorrect'
            ]);
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->db->table('users')->where('id', $user_id)->update(['password' => $passwordHash]);

        return $this->response->setJSON(['success' => true, 'msg' => 'Change Password Success']);
    }
}
