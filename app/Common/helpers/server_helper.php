<?php
/**
 * Common Function - CI4 Version
 */

use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Services;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;

helper(['filesystem', 'url', 'session']);

// START: Serialization Helpers
if (!function_exists('maybe_unserialize')) {
    function maybe_unserialize($original)
    {
        if (is_serialized($original)) return @unserialize($original);
        return $original;
    }
}

if (!function_exists('is_serialized')) {
    function is_serialized($data, $strict = true)
    {
        if (!is_string($data)) return false;
        $data = trim($data);
        if ($data === 'N;') return true;
        if (strlen($data) < 4) return false;
        if ($data[1] !== ':') return false;

        $lastc = substr($data, -1);
        if ($strict && !in_array($lastc, [';', '}'])) return false;

        $token = $data[0];
        return match ($token) {
            's' => (bool) preg_match('/^s:[0-9]+:"[^"\\]*";$/s', $data),
            'a', 'O' => (bool) preg_match("/^{$token}:[0-9]+:/s", $data),
            'b', 'i', 'd' => (bool) preg_match("/^{$token}:[0-9.E-]+;" . ($strict ? '$' : ''), $data),
            default => false,
        };
    }
}

if (!function_exists('is_serialized_string')) {
    function is_serialized_string($data)
    {
        if (!is_string($data)) return false;
        $data = trim($data);
        return $data[0] === 's' && $data[1] === ':' && $data[-1] === ';' && $data[-2] === '"';
    }
}

if (!function_exists('maybe_serialize')) {
    function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data)) return serialize($data);
        if (is_serialized($data, false)) return serialize($data);
        return $data;
    }
}
// END: Serialization Helpers

// START: JSON Response
if (!function_exists('json_response')) {
    function json_response($data, $callback = '')
    {
        $response = Services::response();
        $request = Services::request();
        $json = json_encode($data);
        if ($callback === '') {
            $response->setHeader('Content-Type', 'application/json');
            $response->setBody($json);
        } else {
            $response->setHeader('Content-Type', 'application/javascript');
            $response->setBody($request->getGet($callback) . '(' . $json . ')');
        }
        $response->send();
        exit;
    }
}
// END: JSON Response

// START: Permission & Access
if (!function_exists('check_permission')) {
    function check_permission($permKey, $json = true)
    {
        $session = session();
        $user = $session->get('user');
        $perms = $user['userPerms'] ?? [];
        if (!isset($perms[$permKey]) || !$perms[$permKey]) {
            if ($json) json_response(['success' => false, 'msg' => 'You do not have permission to operate: ' . $permKey]);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission($permKey)
    {
        $session = session();
        $user = $session->get('user');
        return isset($user['userPerms'][$permKey]) && $user['userPerms'][$permKey];
    }
}

if (!function_exists('addPermission')) {
    function addPermission($permName, $permKey, $parent_id, $permType = 0, $rel_id = 0)
    {
        $db = db_connect();
        $data = [
            'permName' => $permName,
            'permKey' => $permKey,
            'parent_id' => $parent_id,
            'permType' => $permType,
            'rel_id' => $rel_id,
        ];
        $db->table('permissions')->insert($data);
        return $db->insertID();
    }
}

if (!function_exists('permissions_list')) {
    function permissions_list()
    {
        return db_connect()->table('permissions')->get()->getResult();
    }
}
// END: Permission & Access

// START: Utilities
if (!function_exists('cxp_update_cache')) {
    function cxp_update_cache($site_id = 0, $cachekey = '')
    {
        delete_files(WRITEPATH . 'cache/', false, true);
    }
}

if (!function_exists('str_cut')) {
    function str_cut($str, $sublen, $etc = '...')
    {
        if (strlen($str) <= $sublen) return $str;
        $output = '';
        $i = 0;
        while ($i < $sublen) {
            $char = substr($str, $i, 1);
            $length = (ord($char) >= 224) ? 3 : ((ord($char) >= 192) ? 2 : 1);
            $output .= substr($str, $i, $length);
            $i += $length;
        }
        return $output . $etc;
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat($originalDate)
    {
        return date("d M Y", strtotime($originalDate));
    }
}

if (!function_exists('dayDateFormat')) {
    function dayDateFormat($originalDate)
    {
        return date("D, d M Y", strtotime($originalDate));
    }
}

if (!function_exists('systemDateFormat')) {
    function systemDateFormat($originalDate)
    {
        return date("Y-m-d", strtotime($originalDate));
    }
}  

// START: Database-Based Role, Session, and Class Functions

if (!function_exists('teacherSubjectSections')) {
    function teacherSubjectSections()
    {
        $db = db_connect();
        $session = session();
        $userid = $session->get('member_userid');
        $sectionsclassinfo = [];

        $results = $db->query("SELECT * FROM class_section WHERE status=1 AND cls_sec_id IN (SELECT cls_sec_id FROM teacher_subjects WHERE status=1 AND tid = {$userid})")->getResult();

        foreach ($results as $section) {
            $class = $db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sec = $db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $class->class_short_name . " (" . $sec->section_name . ")"
            ];
        }

        return $sectionsclassinfo;
    }
}

if (!function_exists('userClassSections')) {
    function userClassSections()
    {
        $db = db_connect();
        $session = session();
        $campusid = $session->get('member_campusid');
        $userid = $session->get('member_userid');
        $currentrole = currentUserRoles();

        $query = in_array(5, $currentrole)
            ? "SELECT * FROM class_section WHERE status = 1 AND cls_sec_id IN(SELECT cls_sec_id FROM teacher_section WHERE status = 1 AND tid = {$userid})"
            : "SELECT * FROM class_section WHERE status=1 AND campus_id = {$campusid}";

        $results = $db->query($query)->getResult();
        $sectionsclassinfo = [];

        foreach ($results as $section) {
            $class = $db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sec = $db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $class->class_short_name . " (" . $sec->section_name . ")"
            ];
        }

        return $sectionsclassinfo;
    }
}

if (!function_exists('currentUserRoles')) {
    /**
     * @return list<int> role_name_id values for all assigned roles (plan-aware)
     */
    function currentUserRoles()
    {
        $userid = (int) (session()->get('member_userid') ?? 0);
        if ($userid <= 0) {
            return [];
        }

        $acl     = new \App\Libraries\MemberAcl($userid);
        $roleIds = $acl->getUserRoles();
        if ($roleIds === []) {
            return [];
        }

        $rows = db_connect()
            ->table('roles')
            ->select('role_name_id')
            ->whereIn('id', $roleIds)
            ->get()
            ->getResultArray();

        $nameIds = [];
        foreach ($rows as $row) {
            $nid = (int) ($row['role_name_id'] ?? 0);
            if ($nid > 0) {
                $nameIds[$nid] = $nid;
            }
        }

        return array_values($nameIds);
    }
}

if (!function_exists('reportHeader')) {
    function reportHeader()
    {
        $db = db_connect();
        $session = session();
        $campusid = $session->get('member_campusid');

        $schoolinfo = $db->query("SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id={$campusid})")->getRow();
        $html = '<div class="row"><div style="border: 1px dashed;margin: 9px;width: 100%;padding: 16px;border-radius: 10px;text-align: center;"><div class="row">';
        $html .= '<div class="col-lg-3"><img style="max-height:150px;max-width:100%;" src="' . base_url('system-logo/' . $schoolinfo->logo) . '"></div>';
        $html .= '<div class="col-lg-9"><h1>' . $schoolinfo->system_name . '</h1></div>';
        $html .= '</div></div></div>';

        return $html;
    }
}

if (!function_exists('termSessions')) {
    function termSessions()
    {
        $db = db_connect();
        $session = session();
        $sessionid = $session->get('member_sessionid');
        $campusid = $session->get('member_campusid');

        $schoolinfo = $db->query("SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id={$campusid})")->getRow();
        $results = $db->table('terms_session')
            ->where('session_id', $sessionid)
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $output = [];
        foreach ($results as $row) {
            $term = $db->table('terms')->where('term_id', $row->term_id)->get()->getRow();
            $output[] = [
                'term_session_id' => $row->term_session_id,
                'name' => $term->name,
            ];
        }
        return $output;
    }
}

if (!function_exists('termSessionsById')) {
    function termSessionsById($id)
    {
        $db = db_connect();
        $termsession = $db->table('terms_session')->where('term_session_id', $id)->get()->getRow();
        $term = $db->table('terms')->where('term_id', $termsession->term_id)->get()->getRow();
        $sessioninfo = $db->table('academic_session')->where('session_id', $termsession->session_id)->get()->getRow();

        return $sessioninfo->session_name . ' (' . $term->name . ')';
    }
}