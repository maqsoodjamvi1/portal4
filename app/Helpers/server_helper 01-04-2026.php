<?php

/**
 * Common Function
 *
 * @author      Maqsood Jamvi
 * @copyright   Copyright (c) 2018~2099 timesoftsol.com
 * @email       maqsoodjamvi@gmail.com
 * @filesource
 */

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

if (!function_exists('maybe_unserialize')) {
    function maybe_unserialize($original)
    {
        if (is_serialized($original)) {
            return @unserialize($original);
        }
        return $original;
    }
}

if (!function_exists('is_serialized')) {
    function is_serialized($data, $strict = true)
    {
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);
        if ($data === 'N;') return true;
        if (strlen($data) < 4) return false;
        if ($data[1] !== ':') return false;

        if ($strict) {
            $lastc = $data[strlen($data) - 1];
            if ($lastc !== ';' && $lastc !== '}') return false;
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            if ($semicolon === false && $brace === false) return false;
            if ($semicolon !== false && $semicolon < 3) return false;
            if ($brace !== false && $brace < 4) return false;
        }

        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ($data[strlen($data) - 2] !== '"') return false;
                } elseif (strpos($data, '"') === false) {
                    return false;
                }
            case 'a':
            case 'O':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }
}

if (!function_exists('is_serialized_string')) {
    function is_serialized_string($data)
    {
        if (!is_string($data)) return false;

        $data = trim($data);
        $length = strlen($data);
        return $length >= 4
            && $data[0] === 's'
            && $data[1] === ':'
            && $data[$length - 2] === '"'
            && $data[$length - 1] === ';';
    }
}

if (!function_exists('maybe_serialize')) {
    function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }

        if (is_serialized($data, false)) {
            return serialize($data);
        }

        return $data;
    }
}

if (!function_exists('json_response')) {
    function json_response($obj, $callback = '')
    {
        $response = Services::response();
        $json = json_encode($obj);

        if ($callback) {
            $output = $callback . '(' . $json . ')';
        } else {
            $output = $json;
        }

        $response->setBody($output);
        $response->setContentType('application/json');
        $response->send();
        exit;
    }
}


if (!function_exists('roles_list')) {
    function roles_list()
    {
        $db = Database::connect();
        $session = session();
        $user = service('userdata')['user'];
        $userroleids = implode(', ', $user->userRoles);
        $results = [];

        $currentuserroles = $db->query("SELECT * FROM roles WHERE id IN($userroleids)")->getResult();

        $campusBill = $db->query("SELECT * FROM campus_bills WHERE status=1 AND campus_id={$user->campus_id}")->getRow();
        $plan_id = $campusBill->plan_id;

        foreach ($currentuserroles as $value) {
            $builder = $db->table('roles');
            $builder->groupStart();
            $builder->where('plan_id', $plan_id);
            $builder->orWhere('id', $value->id);
            $builder->groupEnd();
            $results = $builder->get()->getResult();
        }

        return $results;
    }
}

if (!function_exists('getClassSection')) {
    function getClassSection($id)
    {
        $db = \Config\Database::connect();
        $campusid = (int) (session('member_campusid') ?? 0);

        // Use single JOIN query for better performance
        $builder = $db->table('class_section cs');
        $builder->select(
            'cs.cls_sec_id,
             cs.class_id,
             cs.section_id,
             c.class_name,
             c.class_short_name,
             s.section_name,
             s.short_name AS section_short_name,
             CONCAT(COALESCE(c.class_short_name, c.class_name), " - ", COALESCE(s.short_name, s.section_name)) AS sectionclassname,
             CONCAT(COALESCE(c.class_short_name, c.class_name), " - ", s.section_name) AS sectionclassname_full'
        );
        $builder->join('classes c', 'c.class_id = cs.class_id', 'inner');
        $builder->join('sections s', 's.section_id = cs.section_id', 'inner');
        $builder->where('cs.status', 1)
                ->where('cs.campus_id', $campusid)
                ->where('cs.cls_sec_id', (int)$id);

        $result = $builder->get()->getRow();

        if ($result) {
            return [
                'cls_sec_id'           => (int)$result->cls_sec_id,
                'class_id'             => (int)$result->class_id,
                'class_name'           => $result->class_name,
                'class_short_name'     => $result->class_short_name ?? $result->class_name,
                'section_id'           => (int)$result->section_id,
                'section_name'         => $result->section_name,
                'short_name'   => $result->section_short_name ?? $result->section_name,
                'sectionclassname'     => $result->sectionclassname,           // Uses short_name if available
                'sectionclassname_full'=> $result->sectionclassname_full       // Uses full section_name
            ];
        }

        return [];
    }
}

// if (!function_exists('getAllClassSection')) {
//    function getAllClassSection()
// {
//     $db = \Config\Database::connect();
//     $campusid = (int) (session('member_campusid') ?? 0);

//     // Single joined query — no per-row lookups
//     $builder = $db->table('class_section cs');


//     $builder->select(
//             // include both identifiers + nice label
//             'cs.cls_sec_id,
//              cs.section_id,
//              c.class_id,
//              c.class_name,
//              s.section_name,
//              CONCAT(c.class_name, " - ", s.section_name) AS sectionclassname'
//         );
//     $builder->join('classes c', 'c.class_id = cs.class_id', 'inner');
//     $builder->join('sections s', 's.section_id = cs.section_id', 'inner');
//     $builder->where('cs.status', 1)
//             ->where('cs.campus_id', $campusid)
//             ->orderBy('c.class_id', 'ASC')
//             ->orderBy('s.section_id', 'ASC');

//     $rows = $builder->get()->getResultArray();

//     // Normalize output with keys your view expects
//     $sectionsclassinfo = [];
//     foreach ($rows as $r) {
//         $sectionsclassinfo[] = [
//             'cls_sec_id'        => (int)$r['cls_sec_id'],
//             'class_name'        => (int)$r['class_name'],  
//             'section_id'        => (int)$r['section_id'],  
//             'section_name'        => (int)$r['section_name'],  
            
//             // keep a preformatted label if you need it elsewhere
//             'sectionclassname'  => ($r['class_short_name'] ?? $r['class_name']) . ' (' . $r['section_name'] . ')',
//         ];
//     }
//     return $sectionsclassinfo;
// }
// }


if (!function_exists('getClassSection')) {
    function getClassSection($id)
    {
        $db = Database::connect();
        $session = session();
        $campusid = $session->get('member_campusid');

        $section = $db->query("SELECT * FROM class_section WHERE campus_id = $campusid AND status=1 AND cls_sec_id=$id")->getRow();

        if ($section) {
            $classinfo = $db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $db->table('sections')->where('section_id', $section->section_id)->get()->getRow();

            return [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_short_name . " ({$sectioninfo->section_name})"
            ];
        }

        return [];
    }
}



if (!function_exists('teacherSubjectsInSection')) {
    function teacherSubjectsInSection(int $cls_sec_id): array
    {
        $db      = Database::connect();
        $session = session();
        $tid     = (int) ($session->get('member_userid') ?? 0);

        if ($tid <= 0 || $cls_sec_id <= 0) return [];

        // If your teacher_subjects table does NOT have cls_sec_id, remove "AND ss.cls_sec_id = ts.cls_sec_id"
        return $db->table('teacher_subjects ts')
            ->select('ss.sec_sub_id, ss.subject_id, a.subject_name, a.subject_short_name')
            ->join('section_subjects ss', 'ss.sec_sub_id = ts.sec_sub_id AND ss.cls_sec_id = ts.cls_sec_id', 'inner')
            ->join('allsubject a',        'a.sid = ss.subject_id', 'left')
            ->where('ts.tid', $tid)
            ->where('ts.cls_sec_id', $cls_sec_id)
            ->where('ts.status', 1)
            ->where('ss.status', 1)
            ->orderBy('a.subject_name', 'ASC')
            ->get()->getResultArray();
    }
}

// get user class sections used in add diary
if (!function_exists('teacherSubjectSections')) {
    function teacherSubjectSections(): array
    {
        $db      = \Config\Database::connect();
        $session = session();

        $campus_id = (int) $session->get('member_campusid');
        $user_id   = (int) $session->get('member_userid');

        if (!$user_id) return [];

        $builder = $db->table('teacher_section ts');
        $builder->select(
            // include both identifiers + nice label
            'cs.cls_sec_id,
             cs.section_id,
             c.class_id,
             c.class_name,
             s.section_name,
             CONCAT(c.class_name, " - ", s.section_name) AS sectionclassname'
        );
        $builder->join('class_section cs', 'cs.cls_sec_id = ts.cls_sec_id');
        $builder->join('classes c',       'c.class_id  = cs.class_id');
        $builder->join('sections s',      's.section_id = cs.section_id');

        $builder->where('ts.tid', $user_id);
        $builder->where('ts.status', 1);
        $builder->where('cs.status', 1);
        $builder->where('cs.campus_id', $campus_id);

        // avoid duplicates if a teacher is mapped multiple times
        $builder->groupBy('cs.cls_sec_id');
        $builder->orderBy('c.class_id, s.section_id');

        return $builder->get()->getResultArray();
    }
}

// get all class sections used in add diary
if (!function_exists('userClassSections')) {
    function userClassSections($user_id = null)
    {
        $db = \Config\Database::connect();
        $session = session();

        $campus_id = $session->get('member_campusid');
        if (!$campus_id) {
            return [];
        }

        $builder = $db->table('class_section cs');
        $builder->select(
            // include both identifiers + nice label
            'cs.cls_sec_id,
             cs.section_id,
             c.class_id,
             c.class_name,
             s.section_name,
             CONCAT(c.class_name, " - ", s.section_name) AS sectionclassname'
        );
        $builder->join('classes c', 'c.class_id = cs.class_id');
        $builder->join('sections s', 's.section_id = cs.section_id');
        $builder->where('cs.campus_id', $campus_id);
        $builder->where('cs.status', 1);
        $builder->orderBy('c.class_id, s.section_id');

        return $builder->get()->getResultArray(); // arrays -> matches your view
    }
}

if (!function_exists('currentUserRoles')) {
    function currentUserRoles()
    {
        $db = Database::connect();
        $session = session();
        $userid = $session->get('member_userid');
        $resp = [];

        if ($userid) {
            $rows = $db->query("SELECT * FROM user_roles WHERE userID = $userid")->getResultArray();
            foreach ($rows as $row) {
                $resp[] = $row['roleID'];
            }
        }

        return $resp;
    }
}

if (!function_exists('reportHeader')) {
    function reportHeader()
    {
        $db = Database::connect();
        $session = session();
        $campusid = $session->get('member_campusid');
        $schoolinfo = $db->query("SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id = $campusid)")->getRow();
        $html = '';

        $html .= '<div class="row"><div style="border: 1px dashed;margin: 9px;width: 100%;padding: 16px;border-radius: 10px;text-align: center;"><div class="row">';
        $html .= '<div class="col-lg-3"><img style="max-height:150px;max-width:100%;" src="' . base_url('system-logo/' . $schoolinfo->logo) . '"></div>';
        $html .= '<div class="col-lg-9"><h1>' . $schoolinfo->system_name . '</h1></div>';
        $html .= '</div></div></div>';

        return $html;
    }
}

// if (!function_exists('getSchoolInfo')) {
//     function getSchoolInfo()
//     {
//         $db = Database::connect();
//         $session = session();
//         $campusid = $session->get('member_campusid');

//         return $db->query("SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id = $campusid)")->getRow();
//     }
// }
// function getSchoolInfo()
// {
//     //$system_id = session()->get('school_id');
//     $campusid = session()->get('member_campusid');
   
//     $db = \Config\Database::connect();
//     $builder = $db->table('system');
//     $builder->select('system_id, system_name, logo, address, state, zip, city, country, owner_name, landline_number, chalan_header, mob_number, slogan, reg_text, language');
//     $builder->where('system_id', $system_id);
//     $query = $builder->get();
//     return $query->getRow(); // returns an object or null
// }

// function getSchoolInfo()
// {
//     //$session = session();

//     $campusid = session()->get('member_campusid');
    
//     if (!$campusid) {
//         return null;
//     }

//     $db = \Config\Database::connect();
//     // You can use a single query (with subquery) as you did in CI3:
//     $query = $db->query(
//         'SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id=?)',
//         [$campusid]
//     );
//     return $query->getRow();
// }

if (!function_exists('getSchoolInfoo')) {
    function getSchoolInfo()
    {
        $campusid = session()->get('member_campusid');
        if (!$campusid) {
            return null;
        }

        $db = \Config\Database::connect();
        $query = $db->query(
            'SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id = ?)',
            [$campusid]
        );
        return $query->getRow(); // returns object or null
    }
}
 

if (!function_exists('getCampusInfo')) {
    function getCampusInfo()
    {
        $campusId = session('member_campusid');

        if (!$campusId) {
            return null;
        }

        $db = \Config\Database::connect();

        return $db->table('campus')
                  ->where('campus_id', $campusId)
                  ->get()
                  ->getRow();
    }
}

if (!function_exists('studentProfileImage')) {
    function studentProfileImage($photo)
    {
        return '<img src="'.base_url('uploads/students/' . ($photo ?: 'default.png')).'">';
    }
}

if (!function_exists('calculateAge')) {
    function calculateAge($dob)
    {
        if (!$dob) return null;
        $dob = new \DateTime($dob);
        $now = new \DateTime();
        return $dob->diff($now)->y;
    }
}

if (!function_exists('formatContacts')) {
    function formatContacts($parentinfo)
    {
        $contacts = [];
        if (!empty($parentinfo->phone1)) $contacts[] = $parentinfo->phone1;
        if (!empty($parentinfo->phone2)) $contacts[] = $parentinfo->phone2;
        if (!empty($parentinfo->email)) $contacts[] = $parentinfo->email;
        return implode(' / ', $contacts);
    }
}


if (!function_exists('getLoginUser')) {
    function getLoginUser()
    {
        $session = \Config\Services::session();
        $db = \Config\Database::connect();

        $userId = $session->get('member_userid');
        if (!$userId) {
            return null;
        }

        // Change 'users' to 'employees' if your table is named that
        $builder = $db->table('users'); 
        $user = $builder->where('id', $userId)->get()->getRow();

        return $user;
    }
}

if (!function_exists('getStudentsBySection')) {
    /**
     * Get list of students by class section ID (cls_sec_id).
     *
     * @param int $section_id
     * @return array List of student objects
     */
    function getStudentsBySection(int $section_id): array
    {
        $db = \Config\Database::connect();

        // Get all student_class rows for this section
        $studentClassRows = $db->table('student_class')
            ->where('cls_sec_id', $section_id)
            ->where('status', 1)
            ->get()
            ->getResult();

        if (empty($studentClassRows)) {
            return [];
        }

        $studentIds = array_column($studentClassRows, 'student_id');

        // Fetch actual student data
        $students = $db->table('students')
            ->whereIn('student_id', $studentIds)
            ->orderBy('first_name', 'ASC')
            ->get()
            ->getResult();

        return $students;
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

if (!function_exists('termSessions')) {
    function termSessions()
    {
        $db = Database::connect();
        $session = session();
        $sessionid = $session->get('member_sessionid');
        $campusid = $session->get('member_campusid');

        $schoolinfo = $db->query("SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id = $campusid)")->getRow();

        $builder = $db->table('terms_session')->where('session_id', $sessionid)->where('system_id', $schoolinfo->system_id);
        $terms_session_info = $builder->get()->getResult();
        $termsessioninfo = [];

        foreach ($terms_session_info as $termsession) {
            $terminfo = $db->table('terms')->where('term_id', $termsession->term_id)->get()->getRow();
            $termsessioninfo[] = [
                'term_session_id' => $termsession->term_session_id,
                'name' => $terminfo->name
            ];
        }

        return $termsessioninfo;
    }
}

if (!function_exists('termSessionsById')) {
    function termSessionsById($id)
    {
        $db = Database::connect();
        $session = session();

        $termsession = $db->table('terms_session')->where('term_session_id', $id)->get()->getRow();
        $terminfo = $db->table('terms')->where('term_id', $termsession->term_id)->get()->getRow();
        $academicSesioninfo = $db->table('academic_session')->where('session_id', $termsession->session_id)->get()->getRow();

        return $academicSesioninfo->session_name . " ({$terminfo->name})";
    }
}

if (!function_exists('check_permission')) {
    function check_permission($permKey, $json = true)
    {
        $user = \App\Libraries\MemberCurrentUser::user();
        $perms = $user->userPerms ?? [];

        if (isset($perms[$permKey]) && $perms[$permKey]) {
            return;
        }

        if ($json) {
            json_response([
                'success' => false,
                'msg' => 'You do not have permission to operate: ' . $permKey
            ]);
        } else {
            $themePath = config('View')->admin_theme ?? 'admin/';
            echo view($themePath . 'member/500', ['errorString' => '500']);
            exit;
        }
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission($permKey)
    {
        $user = \App\Libraries\MemberCurrentUser::user();
        $perms = $user->userPerms ?? [];
        return isset($perms[$permKey]) && $perms[$permKey];
    }
}


if (!function_exists('addPermission')) {
    function addPermission($permName, $permKey, $parent_id, $permType = 0, $rel_id = 0)
    {
        $db = Database::connect();
        $data = [
            'permName' => $permName,
            'permKey' => $permKey,
            'parent_id' => $parent_id,
            'permType' => $permType,
            'rel_id' => $rel_id
        ];

        $db->table('permissions')->insert($data);
        return $db->insertID();
    }
}

if (!function_exists('str_cut')) {
    function str_cut($str, $sublen, $etc = '...')
    {
        if (strlen($str) <= $sublen) {
            return $str;
        }

        $i = 0;
        $stringLast = [];

        while ($i < $sublen) {
            $tmp = substr($str, $i, 1);
            $ord = ord($tmp);

            if ($ord >= 224) {
                $tmp = substr($str, $i, 3);
                $i += 3;
            } elseif ($ord >= 192) {
                $tmp = substr($str, $i, 2);
                $i += 2;
            } else {
                $i += 1;
            }

            $stringLast[] = $tmp;
        }

        return implode('', $stringLast) . $etc;
    }
}

if (!function_exists('permissions_list')) {
    function permissions_list()
    {
        $db = Database::connect();
        return $db->table('permissions')->get()->getResult();
    }
}

if (!function_exists('cxp_update_cache')) {
    function cxp_update_cache($site_id = 0, $cachekey = '')
    {
        helper('filesystem');
        delete_files(WRITEPATH . 'cache/', false, true);
    }
}
