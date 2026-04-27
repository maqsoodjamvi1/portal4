<?php

namespace App\Libraries;

use CodeIgniter\Config\Services;

/**
 * Member_Current_User
 *
 * @author      Maqsood Jamvi
 * @copyright   Copyright (c) 2020 timesoftsol.com
 * @email       maqsoodjamvi@gmail.com
 */

class MemberCurrentUser
{
    private static $user;
    private static $ci;

    public function __construct()
    {
        self::$ci = Services::session();
        helper('security');
    }

    /**
     * Return current logged in user with roles & perms
     */
    public static function user()
    {
        $session = Services::session();
        $db      = db_connect();

        if ($session->get('member_userid')) {
            $builder = $db->table('users')
                          ->where('id', $session->get('member_userid'));
            $user1 = $builder->get()->getRow();

            if ($user1) {
                $acl              = new \App\Libraries\MemberAcl($user1->id);
                $userRoles        = $acl->getUserRoles();
                $userPerms        = $acl->getPermArr('mini');
                $user1->userRoles = $userRoles;
                $user1->userPerms = $userPerms;
                return $user1;
            }

            return false;
        }

        return false;
    }

    /**
     * Login: validates user, campus, billing, sets session
     * Does NOT block on missing academic session.
     */
    public static function login(string $username, string $password)
    {
        $session = session();
        $db      = db_connect();
        $request = service('request');
        $today   = date('Y-m-d');

        // Helper for consistent failure
        $fail = function (string $message, string $code = '') use ($session) {
            $session->setFlashdata('perr', $message);
            if ($code !== '') {
                $session->setFlashdata('perr_code', $code);
            }
            session_write_close();
            return false;
        };

        // 1) Load user (by username only, as in your original)
        $user = $db->table('users')
                   ->where('username', $username)
                   ->get()
                   ->getRow();

        if (! $user) {
            return $fail(
                'We could not find an account with that username.',
                'invalid_username'
            );
        }

        // 2) Check if user is active
        if ((int) $user->status !== 1) {
            return $fail(
                'Your account is inactive. Please contact the school administration.',
                'user_inactive'
            );
        }

        // 3) Verify password
        if (! password_verify($password, $user->password)) {
            return $fail(
                'The password you entered is incorrect.',
                'invalid_password'
            );
        }

        // 4) Load campus
        $campus = $db->table('campus')
                     ->where('campus_id', $user->campus_id)
                     ->get()
                     ->getRow();

        if (! $campus) {
            return $fail(
                'Your campus is not configured. Please contact support.',
                'campus_not_found'
            );
        }

        // 5) Load school
        $school = $db->table('system')
                     ->where('system_id', $campus->system_id)
                     ->get()
                     ->getRow();

        if (! $school) {
            return $fail(
                'School profile is missing. Please contact support.',
                'school_not_found'
            );
        }

        // 6) Campus billing check (active bill + not expired)
        $campusBill = $db->table('campus_bills')
                         ->where('status', 1)
                         ->where('campus_id', $user->campus_id)
                         ->orderBy('campus_expiry', 'DESC')
                         ->get()
                         ->getRow();

        if (! $campusBill) {
            return $fail(
                'No active subscription found for your campus. Please contact the school administration.',
                'campus_bill_missing'
            );
        }

        if ($today > $campusBill->campus_expiry) {
            return $fail(
                'Your campus subscription has expired. Please contact the school administration.',
                'campus_expired'
            );
        }

        // 7) Academic session (DO NOT BLOCK LOGIN)
        // Try active session; if none, pick last defined session (by end_date)
        $sessionRow = $db->query(
            'SELECT * FROM academic_session 
             WHERE system_id = ? 
               AND ? BETWEEN start_date AND end_date',
            [$campus->system_id, $today]
        )->getRow();

        if (! $sessionRow) {
            $sessionRow = $db->query(
                'SELECT * FROM academic_session 
                 WHERE system_id = ?
                 ORDER BY end_date DESC
                 LIMIT 1',
                [$campus->system_id]
            )->getRow();
        }

        // 8) Term session (also optional)
        $termSessionRow = $db->query(
            'SELECT * FROM terms_session 
             WHERE system_id = ? 
               AND ? BETWEEN start_date AND end_date',
            [$campus->system_id, $today]
        )->getRow();

        $sessionId     = (int) ($sessionRow->session_id        ?? 0); // can be 0 if no session defined
        $termSessionId = (int) ($termSessionRow->term_session_id ?? 0);
        $termId        = (int) ($termSessionRow->term_id         ?? 0);

        // 9) Update login info
        $ip = $request->getIPAddress();

        $db->table('users')
           ->where('id', $user->id)
           ->update([
               'cur_login_time'  => date('Y-m-d H:i:s'),
               'cur_login_ip'    => $ip,
               'cur_login_area'  => $user->cur_login_area ?? '',
               'last_login_ip'   => $user->cur_login_ip,
               'last_login_area' => $user->cur_login_area,
               'last_login_time' => $user->cur_login_time,
               'login_times'     => (int) $user->login_times + 1,
           ]);

        // 10) Set session keys
        $session->set([
            'member_userid'        => $user->id,
            'IsAuthorized'         => true,
            'member_campusid'      => $user->campus_id,
            'member_sessionid'     => $sessionId,
            'member_termsessionid' => $termSessionId,
            'member_termid'        => $termId,
            'member_reg_text'      => $school->reg_text ?? '',
        ]);

        session_write_close();
        self::$user = $user;

        return $user;
    }

    /**
     * Logout current user
     */
    public static function logout()
    {
        $session = Services::session();
        $session->remove('member_userid');
        $session->destroy();
        session_unset();
        return true;
    }

    /**
     * Permission check helper
     */
    public static function hasPermission($permKey)
    {
        $acl = new \App\Libraries\MemberAcl();
        return $acl->hasPermission($permKey);
    }

    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
}
