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
    private static bool $userLoaded = false;

    /** @var object|false|null */
    private static $cachedUser = null;

    private static $ci;

    public function __construct()
    {
        self::$ci = Services::session();
        helper('security');
    }

    /**
     * Clear per-request user cache (call after login/logout).
     */
    public static function clearCache(): void
    {
        self::$userLoaded = false;
        self::$cachedUser = null;
    }

    /**
     * Return current logged in user with roles & perms
     */
    public static function user()
    {
        if (self::$userLoaded) {
            return self::$cachedUser;
        }

        self::$userLoaded = true;
        self::$cachedUser = false;

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
                self::$cachedUser = $user1;
            }
        }

        return self::$cachedUser;
    }

    /**
     * Login: validates user, campus, billing, sets session
     * Does NOT block on missing academic session.
     */
    public static function login(string $username, string $password)
    {
        self::clearCache();

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

        // 1) Load user by username or email
        $user = $db->table('users')
                   ->groupStart()
                       ->where('username', $username)
                       ->orWhere('email', $username)
                   ->groupEnd()
                   ->get()
                   ->getRow();

        if (! $user) {
            return $fail(
                'We could not find an account with that username or email.',
                'invalid_username'
            );
        }

        // 2) Check if user is active — campus owner (first user of campus) may log in when status is inactive
        if ((int) $user->status !== 1 && ! self::isCampusOwnerUser($db, $user)) {
            return $fail(
                'Your account is inactive. Please contact the school administration to activate your user account.',
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

        // New session ID without destroying old data immediately (fewer races with parallel tabs / AJAX).
        $session->regenerate(false);
        $session->set([
            'member_userid'        => $user->id,
            'IsAuthorized'         => true,
            'member_campusid'      => $user->campus_id,
            'member_sessionid'     => $sessionId,
            'member_termsessionid' => $termSessionId,
            'member_termid'        => $termId,
            'member_reg_text'      => $school->reg_text ?? '',
            'member_auth_set_at'   => time(),
        ]);

        // Restore last campus / session workspace selection when valid for this system.
        \App\Libraries\UserWorkspacePrefs::applyToSession((int) $user->id, (int) $campus->system_id);

        \App\Libraries\UserMenuPrefsLibrary::applyToSession((int) $user->id);

        // Refresh ACL cache at login so role/permission changes apply immediately.
        $acl = new \App\Libraries\MemberAcl((int) $user->id);
        $acl->clearUserCaches((int) $user->id);

        return $user;
    }

    /**
     * Establish session for a user without password check (trusted flows e.g. post email verification signup).
     */
    public static function loginByUserId(int $userId)
    {
        self::clearCache();

        $session = session();
        $db      = db_connect();
        $request = service('request');
        $today   = date('Y-m-d');

        $fail = function (string $message, string $code = '') use ($session) {
            $session->setFlashdata('perr', $message);
            if ($code !== '') {
                $session->setFlashdata('perr_code', $code);
            }
            session_write_close();

            return false;
        };

        $user = $db->table('users')->where('id', $userId)->get()->getRow();
        if (! $user) {
            return $fail('Account could not be loaded. Please sign in manually.', 'invalid_username');
        }

        if ((int) $user->status !== 1 && ! self::isCampusOwnerUser($db, $user)) {
            return $fail(
                'Your account is inactive. Please contact the school administration to activate your user account.',
                'user_inactive'
            );
        }

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

        $termSessionRow = $db->query(
            'SELECT * FROM terms_session 
             WHERE system_id = ? 
               AND ? BETWEEN start_date AND end_date',
            [$campus->system_id, $today]
        )->getRow();

        $sessionId     = (int) ($sessionRow->session_id ?? 0);
        $termSessionId = (int) ($termSessionRow->term_session_id ?? 0);
        $termId        = (int) ($termSessionRow->term_id ?? 0);

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

        $session->regenerate(false);
        $session->set([
            'member_userid'        => $user->id,
            'IsAuthorized'         => true,
            'member_campusid'      => $user->campus_id,
            'member_sessionid'     => $sessionId,
            'member_termsessionid' => $termSessionId,
            'member_termid'        => $termId,
            'member_reg_text'      => $school->reg_text ?? '',
            'member_auth_set_at'   => time(),
        ]);

        \App\Libraries\UserWorkspacePrefs::applyToSession((int) $user->id, (int) $campus->system_id);
        \App\Libraries\UserMenuPrefsLibrary::applyToSession((int) $user->id);

        $acl = new \App\Libraries\MemberAcl((int) $user->id);
        $acl->clearUserCaches((int) $user->id);

        return $user;
    }

    /**
     * Campus owner = first user created for that campus (director account).
     */
    private static function isCampusOwnerUser($db, object $user): bool
    {
        $campusId = (int) ($user->campus_id ?? 0);
        if ($campusId <= 0) {
            return false;
        }

        $owner = $db->table('users')
            ->select('id')
            ->where('campus_id', $campusId)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        return $owner && (int) $owner->id === (int) $user->id;
    }

    /**
     * Logout current user
     */
    public static function logout()
    {
        self::clearCache();

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
