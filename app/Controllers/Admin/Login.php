<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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
        
        // Debug: Create a debug file to see what's happening
        $debug = [
            'time' => date('Y-m-d H:i:s'),
            'step' => 'start'
        ];
        
        // 1) Profile not set
        $regText = $session->get('member_reg_text');
        if (empty($regText)) {
            $debug['redirect'] = 'profile_system';
            $this->writeDebug($debug);
            return base_url('admin/profile_system');
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
        
        $debug['system_id'] = $systemId;
        $debug['campus_id'] = $campusId;
        
        if ($systemId > 0) {
            try {
                $db = db_connect();
                
                // Check classes table
                $classesCount = $db->table('classes')
                    ->where('system_id', $systemId)
                    ->where('status', 1)
                    ->countAllResults();
                $debug['classes_count'] = $classesCount;
                
                // Check sections table
                $sectionsCount = $db->table('sections')
                    ->where('system_id', $systemId)
                    ->where('status', 1)
                    ->countAllResults();
                $debug['sections_count'] = $sectionsCount;
                
                // Check subjects table
                $subjectsCount = $db->table('allsubject')
                    ->where('system_id', $systemId)
                    ->where('status', 1)
                    ->countAllResults();
                $debug['subjects_count'] = $subjectsCount;
                
                // Check class_section table
                $classSectionCount = 0;
                if ($campusId > 0) {
                    $classSectionCount = $db->table('class_section')
                        ->where('campus_id', $campusId)
                        ->where('status', 1)
                        ->countAllResults();
                }
                $debug['class_section_count'] = $classSectionCount;
                
                // Check section_subjects table
                $sectionSubjectsCount = 0;
                if ($campusId > 0 && $subjectsCount > 0) {
                    $subjectIds = $db->table('allsubject')
                        ->select('sid')
                        ->where('system_id', $systemId)
                        ->where('status', 1)
                        ->get()->getResultArray();
                    
                    if (!empty($subjectIds)) {
                        $subjectIdArray = array_column($subjectIds, 'sid');
                        $sectionSubjectsCount = $db->table('section_subjects')
                            ->whereIn('subject_id', $subjectIdArray)
                            ->countAllResults();
                    }
                }
                $debug['section_subjects_count'] = $sectionSubjectsCount;
                
                // If any academic setup table is empty, redirect to academic setup
                if ($classesCount == 0 || $sectionsCount == 0 || $subjectsCount == 0 || 
                    $classSectionCount == 0 || $sectionSubjectsCount == 0) {
                    $debug['redirect'] = 'academic-setup';
                    $debug['reason'] = 'Missing academic data';
                    $this->writeDebug($debug);
                    return base_url('admin/academic-setup');
                }
                
                // Check fee types
                $feeTypesCount = $db->table('fee_type')
                    ->where('system_id', $systemId)
                    ->where('status', 1)
                    ->countAllResults();
                $debug['fee_types_count'] = $feeTypesCount;
                
                if ($feeTypesCount == 0) {
                    $debug['redirect'] = 'fee_type';
                    $debug['reason'] = 'No fee types';
                    $this->writeDebug($debug);
                    return base_url('admin/fee_type');
                }
                
                // Check fee amounts
                if ($campusId > 0) {
                    $feeAmountsCount = $db->table('fee_amount')
                        ->where('campus_id', $campusId)
                        ->countAllResults();
                    $debug['fee_amounts_count'] = $feeAmountsCount;
                    
                    if ($feeAmountsCount == 0) {
                        $debug['redirect'] = 'fee_amount/add';
                        $debug['reason'] = 'No fee amounts';
                        $this->writeDebug($debug);
                        return base_url('admin/fee_amount/add');
                    }
                }
                
                // Check students
                if ($campusId > 0) {
                    $studentsCount = $db->table('students')
                        ->where('campus_id', $campusId)
                        ->countAllResults();
                    $debug['students_count'] = $studentsCount;
                    
                    if ($studentsCount == 0) {
                        $debug['redirect'] = 'addbulkstudents/add';
                        $debug['reason'] = 'No students';
                        $this->writeDebug($debug);
                        return base_url('admin/addbulkstudents/add');
                    }
                }
                
                // Check active academic session
                $today = date('Y-m-d');
                $activeSession = $db->query(
                    'SELECT * FROM academic_session 
                     WHERE system_id = ? 
                       AND ? BETWEEN start_date AND end_date',
                    [$systemId, $today]
                )->getRow();
                
                $debug['active_session_exists'] = ($activeSession ? 'Yes' : 'No');
                
                if (! $activeSession) {
                    $debug['redirect'] = 'academic-calendar/builder';
                    $debug['reason'] = 'No active session';
                    $this->writeDebug($debug);
                    return base_url('admin/academic-calendar/builder');
                }
                
            } catch (\Exception $e) {
                log_message('error', 'Database error in getRedirectAfterLogin: ' . $e->getMessage());
                $debug['error'] = $e->getMessage();
                $this->writeDebug($debug);
                // On database error, redirect to dashboard
                return base_url('admin/dashboard');
            }
        } else {
            $debug['redirect'] = 'dashboard (no system_id)';
            $debug['reason'] = 'System ID is 0';
            $this->writeDebug($debug);
        }
        
        $debug['redirect'] = 'dashboard';
        $debug['reason'] = 'All checks passed';
        $this->writeDebug($debug);
        
        return base_url('admin/dashboard');
    }

    /**
     * Write debug information to a file
     */
    private function writeDebug($data)
    {
        $file = WRITEPATH . 'login_debug_' . date('Y-m-d') . '.log';
        $logEntry = date('Y-m-d H:i:s') . " - " . json_encode($data) . PHP_EOL;
        @file_put_contents($file, $logEntry, FILE_APPEND);
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
                // TODO: generate token, send email, etc.
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

            if ($user_id > 0) {
                $db->table('users')
                    ->where('id', $user_id)
                    ->update(['password' => $password]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Your password has been updated successfully.',
                    'code'    => 'password_updated',
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
                // delete token so it can't be reused
                $db->table('forget_pwd')->delete(['id' => $row->id]);

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