<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AdminPasswordResetMailer;
use App\Libraries\MemberCurrentUser;

class Login extends BaseController
{
    /**
     * Show login page
     */
    public function index()
    {
        // If already logged in, check if setup is complete
        if (session()->get('IsAuthorized')) {
            // Check if user needs to be redirected to setup
            $redirectUrl = $this->getRedirectAfterLogin();
            
            // Always redirect to the determined URL (could be dashboard or setup page)
            return redirect()->to($redirectUrl);
        }

        return view('admin/login');
    }

    /**
     * Handle login submit (AJAX or normal form)
     */
    public function submit()
    {
        helper(['form']);

        $request    = $this->request;
        $validation = service('validation');
        $session    = session();

        $validation->setRules([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username' => ['required' => 'Please enter your username.'],
            'password' => ['required' => 'Please enter your password.'],
        ]);

        if (! $validation->withRequest($request)->run()) {
            $errors = $validation->getErrors();

            if ($request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please fix the errors and try again.',
                    'code'    => 'validation_failed',
                    'errors'  => $errors,
                ]);
            }

            $session->setFlashdata('perr', implode(' ', $errors));
            return redirect()->back()->withInput();
        }

        $username = $request->getPost('username');
        $password = $request->getPost('password');

        $member = new \App\Libraries\MemberCurrentUser();
        $user   = $member->login($username, $password);

        if (! $user) {
            $errorMsg  = $session->getFlashdata('perr') ?? 'Login failed. Please check your details.';
            $errorCode = $session->getFlashdata('perr_code') ?? 'login_failed';

            if ($request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMsg,
                    'code'    => $errorCode,
                ]);
            }

            $session->setFlashdata('perr', $errorMsg);
            return redirect()->back()->withInput();
        }

        // Get redirect URL based on setup completion
        try {
            $redirectUrl = $this->getRedirectAfterLogin();
        } catch (\Exception $e) {
            log_message('error', 'Error in getRedirectAfterLogin: ' . $e->getMessage());
            
            if ($request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => base_url('admin/dashboard'),
                ]);
            }
            return redirect()->to(base_url('admin/dashboard'));
        }

        if ($request->isAJAX()) {
            return $this->response->setJSON([
                'success'      => true,
                'message'      => 'Login successful',
                'redirect'     => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl);
    }

    /**
     * Helper method to determine redirect URL after login
     */
    private function getRedirectAfterLogin()
    {
        $session = session();

        if ($this->currentUserHasRoleNameId(5)) {
            return base_url('admin/dashboard');
        }

        $regText = $session->get('member_reg_text');
        if (empty($regText)) {
            return base_url('admin/profile-system');
        }
        
        // Get school info
        $schoolinfo = null;
        if (function_exists('getSchoolInfo')) {
            try {
                $schoolinfo = getSchoolInfo();
            } catch (\Exception $e) {
                log_message('error', 'getSchoolInfo error: ' . $e->getMessage());
                $schoolinfo = null;
            }
        }
        
        $systemId = 0;
        $campusId = 0;
        
        if ($schoolinfo && isset($schoolinfo->system_id)) {
            $systemId = (int)$schoolinfo->system_id;
        }
        
        $campusId = (int)$session->get('member_campusid');
        if (!$campusId && $schoolinfo && isset($schoolinfo->campus_id)) {
            $campusId = (int)$schoolinfo->campus_id;
        }
        
        if ($systemId > 0) {
            try {
                $nextUrl = \App\Libraries\SchoolSetupProgress::nextStepUrl($systemId, $campusId);
                if ($nextUrl !== null) {
                    return $nextUrl;
                }
            } catch (\Exception $e) {
                log_message('error', 'SchoolSetupProgress error in getRedirectAfterLogin: ' . $e->getMessage());

                return base_url('admin/dashboard');
            }
        }

        return base_url('admin/dashboard');
    }

    private function currentUserHasRoleNameId(int $roleNameId): bool
    {
        $session = session();
        $userId = (int) $session->get('member_userid');
        $campusId = (int) $session->get('member_campusid');

        if ($userId <= 0 || $roleNameId <= 0) {
            return false;
        }

        $db = db_connect();
        $planId = $this->getCampusPlanId($campusId);

        // Current mapping: user_roles.roleID stores roles.id.
        $primary = $db->table('user_roles ur')
            ->join('roles r', 'r.id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
            ->where('ur.userID', $userId)
            ->where('r.role_name_id', $roleNameId)
            ->countAllResults();

        if ($primary > 0) {
            return true;
        }

        // Legacy mapping: user_roles.roleID stores roles.role_name_id.
        $legacy = $db->table('user_roles ur')
            ->join('roles r', 'r.role_name_id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
            ->where('ur.userID', $userId)
            ->where('r.role_name_id', $roleNameId)
            ->countAllResults();

        return $legacy > 0;
    }

    private function getCampusPlanId(int $campusId): int
    {
        if ($campusId <= 0) {
            return 0;
        }

        $row = db_connect()->table('campus_bills')
            ->select('plan_id')
            ->where('status', 1)
            ->where('campus_id', $campusId)
            ->orderBy('campus_expiry', 'DESC')
            ->get()
            ->getRow();

        return (int) ($row->plan_id ?? 0);
    }

    /**
     * Forgot password (request reset)
     */
    public function findpassword()
    {
        $request    = $this->request;
        $validation = service('validation');
        $db         = db_connect();

        if ($request->getMethod() === 'post') {
            $validation->setRules([
                'username' => 'required',
            ], [
                'username' => [
                    'required' => 'Please enter your username or email.',
                ],
            ]);

            if (! $validation->withRequest($request)->run()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please enter your username or email.',
                    'code'    => 'validation_failed',
                    'errors'  => $validation->getErrors(),
                ]);
            }

            $username = $request->getPost('username');

            $builder = $db->table('users');
            $builder->groupStart()
                ->where('username', $username)
                ->orWhere('email', $username)
                ->groupEnd();

            $row = $builder->get()->getRow();

            if ($row) {
                $token = bin2hex(random_bytes(32));
                $db->table('forget_pwd')->where('user_id', (int) $row->id)->delete();
                $db->table('forget_pwd')->insert([
                    'user_id'       => (int) $row->id,
                    'random_string' => $token,
                ]);

                $resetUrl = base_url('admin/login/change_password')
                    . '?user_id=' . (int) $row->id
                    . '&token_code=' . rawurlencode($token);

                $email = trim((string) ($row->email ?? ''));
                if ($email !== '') {
                    $mailer = new AdminPasswordResetMailer();
                    $mailer->sendResetLink(
                        $email,
                        $resetUrl,
                        trim((string) ($row->name ?? $row->username ?? ''))
                    );
                } else {
                    log_message('warning', 'Password reset requested for user id {id} with no email.', ['id' => $row->id]);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'If this account exists, password reset instructions have been sent.',
                    'code'    => 'reset_link_sent',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'We could not find an account with that username or email.',
                'code'    => 'user_not_found',
            ]);
        }

        return view('admin/findpassword');
    }

    /**
     * Change password via reset link
     */
    public function change_password()
    {
        $request    = $this->request;
        $db         = db_connect();
        $validation = service('validation');

        if ($request->getMethod() === 'post') {
            $validation->setRules([
                'password'        => 'required|min_length[6]',
                'confirmpassword' => 'required|matches[password]',
            ], [
                'password' => [
                    'required'   => 'Please enter a new password.',
                    'min_length' => 'Password must be at least 6 characters long.',
                ],
                'confirmpassword' => [
                    'required' => 'Please confirm your password.',
                    'matches'  => 'Passwords do not match.',
                ],
            ]);

            if (! $validation->withRequest($request)->run()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please fix the errors and try again.',
                    'code'    => 'validation_failed',
                    'errors'  => $validation->getErrors(),
                ]);
            }

            $user_id  = (int) $request->getPost('user_id');
            $password = password_hash($request->getPost('password'), PASSWORD_BCRYPT);

            $resetUserId = (int) session()->get('password_reset_user_id');
            $resetOk     = (bool) session()->get('password_reset_verified');
            $resetExpiry = (int) session()->get('password_reset_expires');

            if ($user_id > 0 && $resetOk && $resetUserId === $user_id && $resetExpiry > time()) {
                $db->table('users')
                    ->where('id', $user_id)
                    ->update(['password' => $password]);

                session()->remove(['password_reset_user_id', 'password_reset_verified', 'password_reset_expires']);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Your password has been updated successfully.',
                    'code'    => 'password_updated',
                ]);
            }

            if ($user_id > 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Your reset session has expired. Please request a new password reset link.',
                    'code'    => 'reset_expired',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid user.',
                'code'    => 'invalid_user',
            ]);
        }

        // GET: verify token and show form
        $user_id    = (int) $request->getGet('user_id');
        $token_code = $request->getGet('token_code');

        if ($user_id && $token_code) {
            $builder = $db->table('forget_pwd');
            $builder->where([
                'user_id'       => $user_id,
                'random_string' => $token_code,
            ]);

            $row = $builder->get()->getRow();

            if ($row) {
                $db->table('forget_pwd')->delete(['id' => $row->id]);

                session()->set([
                    'password_reset_user_id'    => (int) $row->user_id,
                    'password_reset_verified'   => true,
                    'password_reset_expires'    => time() + 3600,
                ]);

                $data = [
                    'success' => true,
                    'user_id' => $row->user_id,
                    'message' => '',
                ];

                return view('admin/changepassword', $data);
            }
        }

        $data = [
            'success' => false,
            'message' => 'Invalid or expired password reset link.',
        ];

        return view('admin/changepassword', $data);
    }
}