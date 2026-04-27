<?php

namespace App\Libraries;

use CodeIgniter\Config\Services;

/**
 * MemberAcl Library (CodeIgniter 4 version)
 *
 * @author Chaegumi
 * @copyright Copyright (c) 2013
 * @email chaegumi@qq.com
 */

class MemberAcl
{
    protected $perms = [];       // Stores the permissions for the user
    protected $userID = 0;       // Stores the ID of the current user
    protected $userRoles = [];   // Stores the roles of the current user
    protected $allPerms = [];

    protected $db;
    protected $cache;
    protected $session;

    public function __construct($userID = null)
    {
        $this->db = db_connect();
        $this->cache = \Config\Services::cache();
        $this->session = \Config\Services::session();

        $this->userID = $userID ?? floatval($this->session->get('member_userid'));

        $this->userRoles = $this->getUserRoles();
        $this->allPerms = $this->getAllPerms('full');
        $this->buildACL();
    }

    public function buildACL()
    {
        if (!$perms = $this->cache->get('uperms_' . $this->userID)) {
            if (count($this->userRoles) > 0) {
                $this->perms = array_merge($this->perms, $this->getRolePerms($this->userRoles));
            }

            $this->perms = array_merge($this->perms, $this->getUserPerms($this->userID));
            $this->cache->save('uperms_' . $this->userID, $this->perms, 3600);
        } else {
            $this->perms = $perms;
        }
    }

    public function getPermKeyFromID($permID)
    {
        return $this->allPerms[$permID]['Key'] ?? false;
    }

    public function getPermNameFromID($permID)
    {
        return $this->allPerms[$permID]['Name'] ?? false;
    }

    public function getPermFromID($permID)
    {
        return $this->allPerms[$permID] ?? false;
    }

    public function getRoleNameFromID($roleID)
    {
        $allroles = $this->getAllRoles('full');
        return $allroles[$roleID]['roleName'] ?? false;
    }


    public function getUserClassSections($userID = null)
    {
        $userID = $userID ?? $this->userID;
        if (!$userID) return [];
        
        // First try the new ACL system
        $query = $this->db->table('user_roles ur')
            ->join('roles r', 'r.id = ur.roleid')
            ->join('role_classes rc', 'rc.role_id = r.id')
            ->join('class_section cs', 'cs.id = rc.class_section_id')
            ->join('classes c', 'c.id = cs.class_id')
            ->join('sections s', 's.id = cs.section_id')
            ->where('ur.userID', $userID)
            ->select('cs.id as cls_sec_id, c.class_name, s.section_name, 
                     CONCAT(c.class_name, " - ", s.section_name) as sectionclassname')
            ->get();
        
        return $query->getResultArray();
    }


    public function getUserRoles()
    {
        $userid = floatval($this->userID);

        $userInfo = $this->db->query("SELECT * FROM users WHERE id = $userid")->getRow();
        if (!$userInfo) return [];

        $campusInfo = $this->db->query("SELECT * FROM campus_bills WHERE campus_id = $userInfo->campus_id AND status = 1")->getRow();
        if (!$campusInfo) return [];

        if (!$resp = $this->cache->get('user_roles_' . $userid)) {
            $query = "SELECT id FROM roles WHERE role_name_id IN (SELECT roleid FROM user_roles WHERE userID = $userid) AND plan_id = $campusInfo->plan_id";
            $data = $this->db->query($query)->getResultArray();
            $resp = array_column($data, 'id');
            $this->cache->save('user_roles_' . $userid, $resp, 3600);
        }

        return $resp;
    }

    public function getAllRoles($format = 'ids')
    {
        if (!$resp = $this->cache->get('allroles')) {
            $data = $this->db->query("SELECT * FROM roles ORDER BY roleName ASC")->getResultArray();
            $resp = [];
            foreach ($data as $row) {
                $resp[$row['id']] = strtolower($format) === 'full'
                    ? ["id" => $row['id'], "Name" => $row['roleName']]
                    : $row['id'];
            }
            $this->cache->save('allroles', $resp, 3600);
        }
        return $resp;
    }

    public function getAllPerms($format = 'ids')
    {
        if (!$resp = $this->cache->get('allperms')) {
            $data = $this->db->query("SELECT * FROM permissions ORDER BY permName ASC")->getResultArray();
            $resp = [];
            foreach ($data as $row) {
                if (strtolower($format) === 'full') {
                    $resp[$row['id']] = ['id' => $row['id'], 'Name' => $row['permName'], 'Key' => $row['permKey']];
                } else {
                    $resp[] = $row['id'];
                }
            }
            $this->cache->save('allperms', $resp, 3600);
        }
        return $resp;
    }

    public function getRolePerms($role)
    {
        $roleIDs = is_array($role) ? implode(',', $role) : floatval($role);
        $query = "SELECT * FROM role_perms WHERE roleID IN ($roleIDs) ORDER BY ID ASC";
        $data = $this->db->query($query)->getResultArray();

        $perms = [];
        foreach ($data as $row) {
            $perm = $this->getPermFromID($row['permID']);
            if (!$perm || !$perm['Key']) continue;
            $key = strtolower($perm['Key']);
            $perms[$key] = [
                'perm' => $key,
                'inheritted' => true,
                'value' => $row['value'] == '1',
                'Name' => $perm['Name'],
                'id' => $row['permID']
            ];
        }
        return $perms;
    }

    public function getUserPerms($userID)
    {
        if (!$userID) return [];

        if (!$perms = $this->cache->get('userperms_' . $userID)) {
            $data = $this->db->query("SELECT * FROM user_perms WHERE userID = $userID ORDER BY addDate ASC")->getResultArray();
            $perms = [];
            foreach ($data as $row) {
                $perm = $this->getPermFromID($row['permID']);
                if (!$perm || !$perm['Key']) continue;
                $key = strtolower($perm['Key']);
                $perms[$key] = [
                    'perm' => $key,
                    'inheritted' => false,
                    'value' => $row['value'] == '1',
                    'Name' => $perm['Name'],
                    'id' => $row['permID']
                ];
            }
            $this->cache->save('userperms_' . $userID, $perms, 3600);
        }

        return $perms;
    }

    public function userHasRole($roleID)
    {
        return in_array(floatval($roleID), array_map('floatval', $this->userRoles), true);
    }

    public function hasPermission($permKey)
    {
        $permKey = strtolower($permKey);
        return isset($this->perms[$permKey]) && ($this->perms[$permKey]['value'] === true || $this->perms[$permKey]['value'] === '1');
    }

    public function getPermArr($type = '')
    {
        if ($type === '') return $this->perms;

        $formatted = [];
        foreach ($this->perms as $k => $perm) {
            $formatted[$k] = $perm['value'] === true || $perm['value'] === '1';
        }
        return $formatted;
    }

    public function getUsername($userID)
    {
        $row = $this->db->query("SELECT username FROM users WHERE id = " . floatval($userID) . " LIMIT 1")->getRowArray();
        return $row['username'] ?? null;
    }
}
