<?php

namespace App\Common\Libraries;

use Config\Database;
use Config\Services;

class MemberCurrentUser
{
    private $db;
    private $session;
    private $request;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->session = Services::session();
        $this->request = Services::request();
        helper(['security']);
    }

    public function user()
    {
        $userid = $this->session->get('member_userid');

        if ($userid) {
            $user = $this->db->table('users')->where('id', $userid)->get()->getRow();

            if ($user) {
                $memberAcl = new MemberAcl($user->id);
                $user->userRoles = $memberAcl->getUserRoles();
                $user->userPerms = $memberAcl->getPermArr('mini');
                return $user;
            }
        }

        return false;
    }

    public function login($username, $password)
    {
        $user = $this->db->table('users')
            ->where('username', $username)
            ->where('status', 1)
            ->get()
            ->getRow();

        $currentDate = date('Y-m-d');

        if ($user && password_verify($password, $user->password)) {

            $campus = $this->db->table('campus')->where('campus_id', $user->campus_id)->get()->getRow();
            if (!$campus) {
                $this->session->setFlashdata('perr', 'Invalid Campus');
                return false;
            }

            $school = $this->db->table('system')->where('system_id', $campus->system_id)->get()->getRow();
            $campusBill = $this->db->table('campus_bills')->where(['status' => 1, 'campus_id' => $user->campus_id])->get()->getRow();

            if (!$campusBill || $currentDate > $campusBill->campus_expiry) {
                $this->session->setFlashdata('perr', 'Campus Status Disabled');
                return false;
            }

            // Term session
            $termSession = $this->db->query(
                "SELECT * FROM terms_session WHERE system_id = ? AND ? BETWEEN start_date AND end_date",
                [$campus->system_id, $currentDate]
            )->getRow();

            // Academic session
            $sessionInfo = $this->db->query(
                "SELECT * FROM academic_session WHERE system_id = ? AND ? BETWEEN start_date AND end_date",
                [$campus->system_id, $currentDate]
            )->getRow();

            if (!$sessionInfo) {
                $sessionInfo = $this->db->table('academic_session')
                    ->where('system_id', $campus->system_id)
                    ->get()
                    ->getRow();
            }

            $sessionId = $sessionInfo->session_id ?? 0;
            $termId = $termSession->term_id ?? 0;
            $termSessionId = $termSession->term_session_id ?? 0;

            // Update login info
            $this->db->table('users')->where('id', $user->id)->update([
                'cur_login_time' => date('Y-m-d H:i:s'),
                'cur_login_ip' => $this->request->getIPAddress(),
                'cur_login_area' => '',
                'last_login_ip' => $user->cur_login_ip,
                'last_login_area' => $user->cur_login_area,
                'last_login_time' => $user->cur_login_time,
                'login_times' => $user->login_times + 1
            ]);

            // Set session data
            $this->session->set([
                'member_userid' => $user->id,
                'IsAuthorized' => true,
                'member_campusid' => $user->campus_id,
                'member_sessionid' => $sessionId,
                'member_termid' => $termId,
                'member_termsessionid' => $termSessionId,
                'member_reg_text' => $school->reg_text ?? '',
            ]);

            return $user;
        }

        $this->session->setFlashdata('perr', 'Invalid username or password');
        return false;
    }

    public function logout()
    {
        $this->session->remove([
            'member_userid',
            'IsAuthorized',
            'member_campusid',
            'member_sessionid',
            'member_termid',
            'member_termsessionid',
            'member_reg_text'
        ]);
        $this->session->destroy();
        return true;
    }

    public function hasPermission($permKey)
    {
        $userid = $this->session->get('member_userid');
        if (!$userid) {
            return false;
        }

        $memberAcl = new MemberAcl($userid);
        return $memberAcl->hasPermission($permKey);
    }
}
