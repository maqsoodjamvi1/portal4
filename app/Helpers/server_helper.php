<?php

/**
 * Common Helper Functions
 * 
 * @author      Maqsood Jamvi
 * @copyright   Copyright (c) 2018~2099 timesoftsol.com
 * @email       maqsoodjamvi@gmail.com
 * @filesource
 */

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

// ============================================
// SECTION 1: SERIALIZATION FUNCTIONS
// ============================================

if (!function_exists('maybe_unserialize')) {
    /**
     * Unserialize value if it was serialized
     */
    function maybe_unserialize($original)
    {
        if (is_serialized($original)) {
            return @unserialize($original);
        }
        return $original;
    }
}

if (!function_exists('is_serialized')) {
    /**
     * Check if data is serialized
     */
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
    /**
     * Check if data is a serialized string
     */
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
    /**
     * Serialize data if needed
     */
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

// ============================================
// SECTION 2: RESPONSE & JSON FUNCTIONS
// ============================================

if (!function_exists('json_response')) {
    /**
     * Send JSON response
     *
     * @param array|object $obj      Payload
     * @param string       $callback Optional JSONP callback name
     * @param int          $status   HTTP status code (default 200)
     */
    function json_response($obj, $callback = '', $status = 200)
    {
        $response = Services::response();
        $json = json_encode($obj);

        if ($callback) {
            $output = $callback . '(' . $json . ')';
        } else {
            $output = $json;
        }

        $response->setStatusCode((int) $status);
        $response->setBody($output);
        $response->setContentType('application/json');
        $response->send();
        exit;
    }
}

if (! function_exists('admin_login_required_url')) {
    function admin_login_required_url(): string
    {
        return base_url('admin/login?reason=login_required');
    }
}

if (! function_exists('admin_session_expired_login_url')) {
    function admin_session_expired_login_url(): string
    {
        return base_url('admin/login?reason=session_expired');
    }
}

if (! function_exists('admin_client_has_session_cookie')) {
    function admin_client_has_session_cookie(?RequestInterface $request = null): bool
    {
        $request    = $request ?? service('request');
        $cookieName = config('Session')->cookieName ?? 'ci_session';

        return $request->getCookie($cookieName) !== null;
    }
}

if (! function_exists('admin_request_from_admin_area')) {
    /**
     * True when the client was likely already using the admin panel (link click / form / AJAX).
     */
    function admin_request_from_admin_area(?RequestInterface $request = null): bool
    {
        $request = $request ?? service('request');
        $referer = (string) $request->getHeaderLine('Referer');
        if ($referer === '') {
            return false;
        }

        $adminBase = rtrim(base_url('admin'), '/');

        return str_contains($referer, $adminBase);
    }
}

if (! function_exists('admin_request_wants_json')) {
    function admin_request_wants_json(?RequestInterface $request = null): bool
    {
        $request = $request ?? service('request');

        if ($request->isAJAX()) {
            return true;
        }

        $accept = strtolower($request->getHeaderLine('Accept'));
        if ($accept === '') {
            return false;
        }

        // Full-page navigation sends text/html first — do not treat as JSON API.
        if (str_contains($accept, 'text/html')) {
            return false;
        }

        return str_contains($accept, 'application/json') || str_contains($accept, '+json');
    }
}

if (! function_exists('admin_login_required_payload')) {
    /**
     * @return array{success: bool, code: string, msg: string, redirect: string}
     */
    function admin_login_required_payload(): array
    {
        return [
            'success'  => false,
            'code'     => 'auth_required',
            'msg'      => 'Please sign in to continue.',
            'redirect' => admin_login_required_url(),
        ];
    }
}

if (! function_exists('admin_session_expired_payload')) {
    /**
     * @return array{success: bool, code: string, msg: string, redirect: string}
     */
    function admin_session_expired_payload(): array
    {
        return [
            'success'  => false,
            'code'     => 'session_expired',
            'msg'      => 'Your session has expired. Please sign in again.',
            'redirect' => admin_session_expired_login_url(),
        ];
    }
}

if (! function_exists('admin_login_required_response')) {
    function admin_login_required_response(?RequestInterface $request = null): ResponseInterface
    {
        $request = $request ?? service('request');
        $payload = admin_login_required_payload();

        if (admin_request_wants_json($request)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON($payload);
        }

        return redirect()->to($payload['redirect'])
            ->with('login_required', $payload['msg']);
    }
}

if (! function_exists('admin_session_expired_response')) {
    /**
     * Return 401 JSON for AJAX/JSON clients, or redirect to login for full-page requests.
     */
    function admin_session_expired_response(?RequestInterface $request = null): ResponseInterface
    {
        $request = $request ?? service('request');
        $payload = admin_session_expired_payload();

        if (admin_request_wants_json($request)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON($payload);
        }

        return redirect()->to($payload['redirect'])
            ->with('session_expired', $payload['msg']);
    }
}

if (! function_exists('admin_auth_failure_response')) {
    /**
     * Pick login-required vs session-expired based on whether the user was already in admin.
     */
    function admin_auth_failure_response(?RequestInterface $request = null): ResponseInterface
    {
        $request = $request ?? service('request');

        if (admin_request_from_admin_area($request) || admin_client_has_session_cookie($request)) {
            return admin_session_expired_response($request);
        }

        return admin_login_required_response($request);
    }
}

// ============================================
// SECTION 3: SCHOOL & CAMPUS INFORMATION
// ============================================

if (!function_exists('getSchoolInfo')) {
    /**
     * Get school/system information
     */
    function getSchoolInfo()
    {
        static $cached = null;
        static $campusKey = null;

        $campusid = session()->get('member_campusid');
        if (!$campusid) {
            return null;
        }

        if ($campusKey === $campusid && $cached !== null) {
            return $cached;
        }

        $db = \Config\Database::connect();
        $query = $db->query(
            'SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id = ?)',
            [$campusid]
        );
        $cached = $query->getRow();
        $campusKey = $campusid;

        return $cached;
    }
}

if (!function_exists('getMonthlyFeeStudentCounts')) {
    /**
     * Count students with non-zero monthly fee for a given month.
     * Paid = any payment (full paid/discounted status, partial split, or ledger receipt).
     *
     * @return array{fee_month: string, total_students: int, paid_students: int, unpaid_students: int}
     */
    function getMonthlyFeeStudentCounts(int $campusId, ?string $feeMonth = null): array
    {
        $feeMonth = $feeMonth ?: date('Y-m');
        $defaults = [
            'fee_month'       => $feeMonth,
            'total_students'  => 0,
            'paid_students'   => 0,
            'unpaid_students' => 0,
        ];

        if ($campusId <= 0) {
            return $defaults;
        }

        try {
            $db = \Config\Database::connect();

            $campus = $db->table('campus')
                ->select('system_id')
                ->where('campus_id', $campusId)
                ->get()
                ->getRow();
            $systemId = (int) ($campus->system_id ?? 0);
            if ($systemId <= 0) {
                return $defaults;
            }

            $feeTypeRows = $db->table('fee_type')
                ->select('fee_type_id')
                ->where('system_id', $systemId)
                ->where('is_monthly_fee', 1)
                ->get()
                ->getResultArray();

            $feeTypeIds = array_values(array_filter(array_map('intval', array_column($feeTypeRows, 'fee_type_id'))));
            if ($feeTypeIds === []) {
                return $defaults;
            }

            $feeTypeIn  = implode(',', $feeTypeIds);
            $hasFti     = $db->tableExists('finance_transaction_items');
            $paidExtra  = $hasFti
                ? ', MAX(CASE WHEN EXISTS (
                        SELECT 1 FROM finance_transaction_items fti
                        WHERE fti.chalan_id = fc.chalan_id
                   ) THEN 1 ELSE 0 END) AS has_payment_record'
                : ', 0 AS has_payment_record';

            $sql = "
                SELECT
                    fc.student_id,
                    SUM(fc.amount - fc.discount) AS total_net,
                    MAX(CASE WHEN fc.status IN ('paid', 'discounted') THEN 1 ELSE 0 END) AS has_paid_status,
                    COUNT(*) AS chalan_count
                    {$paidExtra}
                FROM fee_chalan fc
                INNER JOIN students s ON s.student_id = fc.student_id
                WHERE fc.fee_month = ?
                  AND s.campus_id = ?
                  AND s.status = 1
                  AND fc.fee_type_id IN ({$feeTypeIn})
                GROUP BY fc.student_id
                HAVING total_net > 0
            ";

            $rows  = $db->query($sql, [$feeMonth, $campusId])->getResultArray();
            $total = count($rows);
            $paid  = 0;

            foreach ($rows as $row) {
                $isPaid = (int) ($row['has_paid_status'] ?? 0) === 1
                    || (int) ($row['chalan_count'] ?? 0) > 1
                    || (int) ($row['has_payment_record'] ?? 0) === 1;
                if ($isPaid) {
                    $paid++;
                }
            }

            return [
                'fee_month'       => $feeMonth,
                'total_students'  => $total,
                'paid_students'   => $paid,
                'unpaid_students' => max(0, $total - $paid),
            ];
        } catch (\Throwable $e) {
            return $defaults;
        }
    }
}

if (!function_exists('getAdminHeaderMetrics')) {
    /**
     * Sidebar/header badge counts (cached per campus to avoid repeated queries every page).
     *
     * @return array{unread_messages:int,pending_emp_leaves:int,pending_std_leaves:int,unpaid_fee_chalans:int,monthly_fee_total_students:int,monthly_fee_paid_students:int,monthly_fee_month:string}
     */
    function getAdminHeaderMetrics(int $campusId): array
    {
        $defaults = [
            'unread_messages'           => 0,
            'pending_emp_leaves'        => 0,
            'pending_std_leaves'        => 0,
            'unpaid_fee_chalans'        => 0,
            'monthly_fee_total_students'=> 0,
            'monthly_fee_paid_students' => 0,
            'monthly_fee_month'         => date('Y-m'),
        ];

        if ($campusId <= 0) {
            return $defaults;
        }

        $cache = \Config\Services::cache();
        $key   = 'admin_header_metrics_' . $campusId;
        $hit   = $cache->get($key);

        if (is_array($hit)) {
            return array_merge($defaults, $hit);
        }

        $safeCount = static function (string $sql, array $binds = []): int {
            try {
                $db  = \Config\Database::connect();
                $q   = $db->query($sql, $binds);
                $row = $q ? $q->getRow() : null;

                return (int) ($row->c ?? 0);
            } catch (\Throwable $e) {
                return 0;
            }
        };

        $metrics = [
            'unread_messages' => $safeCount(
                "SELECT COUNT(*) c FROM messages
                 WHERE (is_read = 0 OR is_read IS NULL)
                   AND (? = 0 OR campus_id = ?)",
                [$campusId, $campusId]
            ),
            'pending_emp_leaves' => $safeCount(
                "SELECT COUNT(*) c FROM employee_leaves
                 WHERE (status = 'Pending' OR status = 0 OR approved = 0 OR COALESCE(approved,0) = 0)
                   AND (? = 0 OR campus_id = ?)",
                [$campusId, $campusId]
            ),
            'pending_std_leaves' => $safeCount(
                "SELECT COUNT(*) c FROM students_leaves
                 WHERE (status = 'Pending' OR status = 0 OR approved = 0 OR COALESCE(approved,0) = 0)
                   AND (? = 0 OR campus_id = ?)",
                [$campusId, $campusId]
            ),
        ];

        $monthlyFeeCounts = getMonthlyFeeStudentCounts($campusId);
        $metrics['unpaid_fee_chalans']         = (int) ($monthlyFeeCounts['unpaid_students'] ?? 0);
        $metrics['monthly_fee_total_students'] = (int) ($monthlyFeeCounts['total_students'] ?? 0);
        $metrics['monthly_fee_paid_students']  = (int) ($monthlyFeeCounts['paid_students'] ?? 0);
        $metrics['monthly_fee_month']          = (string) ($monthlyFeeCounts['fee_month'] ?? date('Y-m'));

        $cache->save($key, $metrics, 60);

        return $metrics;
    }
}

if (!function_exists('getCampusInfo')) {
    /**
     * Get campus information
     */
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

if (!function_exists('reportHeader')) {
    /**
     * Generate report header HTML
     */
    function reportHeader()
    {
        $db = Database::connect();
        $session = session();
        $campusid = (int) $session->get('member_campusid');
        $campusRow = $db->table('campus')->select('system_id')->where('campus_id', $campusid)->get()->getRow();
        $systemId  = (int) ($campusRow->system_id ?? 0);
        $schoolinfo = $systemId > 0
            ? $db->table('system')->where('system_id', $systemId)->get()->getRow()
            : null;
        if ($schoolinfo === null) {
            return '';
        }

        $html = '';

        $html .= '<div class="row"><div style="border: 1px dashed;margin: 9px;width: 100%;padding: 16px;border-radius: 10px;text-align: center;"><div class="row">';
        $html .= '<div class="col-lg-3"><img style="max-height:150px;max-width:100%;" src="' . base_url('system-logo/' . ($schoolinfo->logo ?? '')) . '"></div>';
        $html .= '<div class="col-lg-9"><h1>' . esc($schoolinfo->system_name ?? '') . '</h1></div>';
        $html .= '</div></div></div>';

        return $html;
    }
}

// ============================================
// SECTION 4: USER & ROLE FUNCTIONS
// ============================================

if (!function_exists('currentUserRoles')) {
    /**
     * Role name IDs for all roles assigned to the current user (plan-aware).
     * Used across the app for checks like in_array(5, currentUserRoles()) (Teacher).
     *
     * @return list<int>
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

        $rows = Database::connect()
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

if (!function_exists('getLoginUser')) {
    /**
     * Get current logged in user
     */
    function getLoginUser()
    {
        $session = \Config\Services::session();
        $db = \Config\Database::connect();

        $userId = $session->get('member_userid');
        if (!$userId) {
            return null;
        }

        $builder = $db->table('users'); 
        $user = $builder->where('id', $userId)->get()->getRow();

        return $user;
    }
}

if (!function_exists('roles_list')) {
    /**
     * Get list of roles
     */
    function roles_list()
    {
        $db = Database::connect();
        $session = session();
        $user = service('userdata')['user'];
        $roleIds = array_values(array_filter(array_map('intval', (array) ($user->userRoles ?? []))));
        $results = [];

        $currentuserroles = $roleIds === []
            ? []
            : $db->table('roles')->whereIn('id', $roleIds)->get()->getResult();

        $campusBill = $db->table('campus_bills')
            ->where('status', 1)
            ->where('campus_id', (int) $user->campus_id)
            ->get()
            ->getRow();
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



if (!function_exists('getStudentPhotoUrl')) {
    /**
     * Get student photo URL from various possible locations
     */
    function getStudentPhotoUrl($photoFile)
    {
        $fallback = base_url('resource/img/avatar-student.png');

        if (empty($photoFile)) {
            return $fallback;
        }

        $photoFile = trim((string) $photoFile);

        // Fix legacy/wrong URLs like https://site.com/WhatsApp_Image.jpeg (missing uploads/)
        if (preg_match('#^https?://#i', $photoFile)) {
            $path = parse_url($photoFile, PHP_URL_PATH) ?? '';
            if ($path !== '' && preg_match('#^/[^/]+$#', $path)) {
                $photoFile = 'uploads/' . basename($path);
            } else {
                return $photoFile;
            }
        }

        $photoFile = ltrim($photoFile, '/');

        if (! str_contains($photoFile, '/')) {
            $photoFile = 'uploads/' . $photoFile;
        } elseif (! str_starts_with($photoFile, 'uploads/')) {
            $photoFile = 'uploads/' . basename($photoFile);
        }

        $basename = basename($photoFile);
        $paths    = [
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $basename,
            rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_profiles' . DIRECTORY_SEPARATOR . $basename,
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'student_photos' . DIRECTORY_SEPARATOR . $basename,
        ];

        foreach ($paths as $fullPath) {
            if (is_file($fullPath)) {
                return base_url('uploads/' . $basename);
            }
        }

        return base_url('uploads/' . $basename);
    }
}


// ============================================
// SECTION 5: CLASS & SECTION FUNCTIONS
// ============================================

if (!function_exists('getClassSection')) {
    /**
     * Get class section by ID
     */
    function getClassSection($id)
    {
        $db = \Config\Database::connect();
        $campusid = (int) (session('member_campusid') ?? 0);

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
                'short_name'           => $result->section_short_name ?? $result->section_name,
                'sectionclassname'     => $result->sectionclassname,
                'sectionclassname_full'=> $result->sectionclassname_full
            ];
        }

        return [];
    }
}

if (!function_exists('getAllClassSection')) {
    /**
     * Get all class sections
     */
    function getAllClassSection()
    {
        $db = \Config\Database::connect();
        $campusid = (int) (session('member_campusid') ?? 0);

        $builder = $db->table('class_section cs');
        $builder->select(
            'cs.cls_sec_id,
             cs.section_id,
             cs.class_id,
             c.class_id,
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
                ->orderBy('c.class_id', 'ASC')
                ->orderBy('s.section_id', 'ASC');

        $rows = $builder->get()->getResultArray();

        $sectionsclassinfo = [];
        foreach ($rows as $r) {
            $sectionsclassinfo[] = [
                'cls_sec_id'           => (int)$r['cls_sec_id'],
                'class_id'             => (int)$r['class_id'],
                'class_name'           => $r['class_name'],
                'class_short_name'     => $r['class_short_name'] ?? $r['class_name'],
                'section_id'           => (int)$r['section_id'],
                'section_name'         => $r['section_name'],
                'section_short_name'   => $r['section_short_name'] ?? $r['section_name'],
                'sectionclassname'     => $r['sectionclassname'],
                'sectionclassname_full'=> $r['sectionclassname_full']
            ];
        }
        return $sectionsclassinfo;
    }
}

if (!function_exists('userClassSections')) {
    /**
     * Class sections visible to the user (campus-wide or role-scoped).
     */
    function userClassSections($user_id = null)
    {
        $scoped = roleClassSections($user_id);
        if ($scoped !== []) {
            return $scoped;
        }

        $db = \Config\Database::connect();
        $session = session();

        $campus_id = $session->get('member_campusid');
        if (!$campus_id) {
            return [];
        }

        $builder = $db->table('class_section cs');
        $builder->select(
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

        return $builder->get()->getResultArray();
    }
}

// ============================================
// SECTION 6: TEACHER FUNCTIONS
// ============================================

if (!function_exists('teacherSubjectSections')) {
    /**
     * Class sections assigned to the current (or given) teacher.
     *
     * @return array<int, array<string, mixed>>
     */
    function teacherSubjectSections(?int $user_id = null): array
    {
        $db      = \Config\Database::connect();
        $session = session();

        $campus_id = (int) $session->get('member_campusid');
        $user_id   = $user_id ?? (int) $session->get('member_userid');

        if ($user_id <= 0) {
            return [];
        }

        $builder = $db->table('teacher_section ts');
        $builder->select(
            'cs.cls_sec_id,
             cs.section_id,
             c.class_id,
             c.class_name,
             s.section_name,
             CONCAT(c.class_name, " - ", s.section_name) AS sectionclassname'
        );
        $builder->join('class_section cs', 'cs.cls_sec_id = ts.cls_sec_id');
        $builder->join('classes c', 'c.class_id = cs.class_id');
        $builder->join('sections s', 's.section_id = cs.section_id');
        $builder->where('ts.tid', $user_id);
        $builder->where('ts.status', 1);
        $builder->where('cs.status', 1);

        if ($campus_id > 0) {
            $builder->where('cs.campus_id', $campus_id);
        }

        $builder->groupBy('cs.cls_sec_id');
        $builder->orderBy('c.class_id, s.section_id');

        $teacherSections = $builder->get()->getResultArray();
        $roleSections    = roleClassSections($user_id);

        if ($roleSections === []) {
            return $teacherSections;
        }

        return mergeClassSectionRows($teacherSections, $roleSections);
    }
}

if (!function_exists('mergeClassSectionRows')) {
    /**
     * @param array<int, array<string, mixed>> $a
     * @param array<int, array<string, mixed>> $b
     * @return array<int, array<string, mixed>>
     */
    function mergeClassSectionRows(array $a, array $b): array
    {
        $merged = [];

        foreach (array_merge($a, $b) as $row) {
            $id = (int) ($row['cls_sec_id'] ?? 0);
            if ($id > 0) {
                $merged[$id] = $row;
            }
        }

        return array_values($merged);
    }
}

if (!function_exists('getTeacherSubjectsInClass')) {
    /**
     * Get all subjects taught by teacher in a specific class
     * @param int $class_id Class ID
     * @param int $campus_id Campus ID
     * @param int $teacher_id Teacher ID
     * @return array Array of subject objects
     */
    function getTeacherSubjectsInClass($class_id, $campus_id, $teacher_id)
    {
        $db = \Config\Database::connect();
        
        if (!$class_id || !$campus_id || !$teacher_id) {
            return [];
        }
        
        $sql = "SELECT DISTINCT 
                    a.sid as subject_id, 
                    a.subject_name,
                    a.subject_short_name
                FROM teacher_subjects ts
                INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                INNER JOIN allsubject a ON a.sid = ss.subject_id
                WHERE cs.class_id = ?
                    AND cs.campus_id = ?
                    AND ts.tid = ?
                    AND ts.status = 1
                    AND ss.status = 1
                    AND cs.status = 1
                    AND a.status = 1
                ORDER BY a.subject_name ASC";
        
        return $db->query($sql, [$class_id, $campus_id, $teacher_id])->getResult();
    }
}


if (!function_exists('getTeacherClassesForSubject')) {
    /**
     * Get classes where teacher teaches a specific subject
     * @param int $teacher_id Teacher ID
     * @param int $subject_id Subject ID
     * @param int $campus_id Campus ID
     * @return array Array of class objects
     */
    function getTeacherClassesForSubject($teacher_id, $subject_id, $campus_id)
    {
        $db = \Config\Database::connect();
        
        if (!$teacher_id || !$subject_id || !$campus_id) {
            return [];
        }
        
        $sql = "SELECT DISTINCT 
                    c.class_id, 
                    c.class_name, 
                    c.class_short_name
                FROM teacher_subjects ts
                INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                INNER JOIN classes c ON c.class_id = cs.class_id
                WHERE ts.tid = ?
                    AND ss.subject_id = ?
                    AND cs.campus_id = ?
                    AND ts.status = 1
                    AND ss.status = 1
                    AND cs.status = 1
                    AND c.status = 1
                ORDER BY c.class_id ASC";
        
        return $db->query($sql, [$teacher_id, $subject_id, $campus_id])->getResult();
    }
}

if (!function_exists('getAllClassesForSubject')) {
    /**
     * Get all classes that have a subject (for admin)
     * @param int $subject_id Subject ID
     * @param int $campus_id Campus ID
     * @return array Array of class objects
     */
    function getAllClassesForSubject($subject_id, $campus_id)
    {
        $db = \Config\Database::connect();
        
        if (!$subject_id || !$campus_id) {
            return [];
        }
        
        $sql = "SELECT DISTINCT 
                    c.class_id, 
                    c.class_name, 
                    c.class_short_name
                FROM section_subjects ss
                INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                INNER JOIN classes c ON c.class_id = cs.class_id
                WHERE ss.subject_id = ?
                    AND cs.campus_id = ?
                    AND ss.status = 1
                    AND cs.status = 1
                    AND c.status = 1
                ORDER BY c.class_id ASC";
        
        return $db->query($sql, [$subject_id, $campus_id])->getResult();
    }
}

if (!function_exists('teacherSubjectsInSection')) {
    /**
     * Get teacher subjects in a section
     */
    function teacherSubjectsInSection(int $cls_sec_id): array
    {
        $db      = Database::connect();
        $session = session();
        $tid     = (int) ($session->get('member_userid') ?? 0);

        if ($tid <= 0 || $cls_sec_id <= 0) return [];

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

if (!function_exists('getTeacherSubjectSections')) {
    /**
     * Get teacher's subject sections from teacher_subjects table
     * Returns sections where teacher teaches any subject
     */
    function getTeacherSubjectSections(): array
    {
        $db = \Config\Database::connect();
        $session = session();

        $campus_id = (int) $session->get('member_campusid');
        $teacher_id = (int) $session->get('member_userid');

        if (!$teacher_id || !$campus_id) {
            return [];
        }

        $sql = "SELECT DISTINCT 
                    cs.cls_sec_id,
                    cs.section_id,
                    c.class_id,
                    c.class_name,
                    c.class_short_name,
                    s.section_name,
                    s.short_name AS section_short_name,
                    CONCAT(COALESCE(c.class_short_name, c.class_name), ' - ', COALESCE(s.short_name, s.section_name)) AS sectionclassname
                FROM teacher_subjects ts
                INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                INNER JOIN classes c ON c.class_id = cs.class_id
                INNER JOIN sections s ON s.section_id = cs.section_id
                WHERE ts.tid = ?
                    AND cs.campus_id = ?
                    AND ts.status = 1
                    AND ss.status = 1
                    AND cs.status = 1
                    AND c.status = 1
                    AND s.status = 1
                ORDER BY c.class_id ASC, s.section_id ASC";
        
        return $db->query($sql, [$teacher_id, $campus_id])->getResultArray();
    }
}

if (! function_exists('getTeacherAllowedClassSectionIds')) {
    /**
     * @return list<int>
     */
    function getTeacherAllowedClassSectionIds(): array
    {
        $sections = getTeacherSubjectSections();
        $ids      = [];

        foreach ($sections as $row) {
            $id = (int) ($row['cls_sec_id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }
}

if (! function_exists('teacherCanViewClassSection')) {
    function teacherCanViewClassSection(int $clsSecId, ?int $teacherId = null): bool
    {
        if ($clsSecId <= 0) {
            return false;
        }

        $teacherId = $teacherId ?? (int) (session()->get('member_userid') ?? 0);
        if ($teacherId <= 0) {
            return false;
        }

        $db = \Config\Database::connect();

        return $db->table('teacher_subjects')
            ->where('tid', $teacherId)
            ->where('cls_sec_id', $clsSecId)
            ->where('status', 1)
            ->countAllResults() > 0;
    }
}


// SECTION 7: get all subjects

if (!function_exists('getSectionSubjects')) {
    /**
     * Get all subjects in a section (for admin/director)
     */
    function getSectionSubjects(int $cls_sec_id): array
    {
        $db = \Config\Database::connect();
        
        $subjects = $db->table('section_subjects ss')
            ->select('ss.subject_id, a.subject_name, a.subject_short_name')
            ->join('allsubject a', 'a.sid = ss.subject_id')
            ->where('ss.cls_sec_id', $cls_sec_id)
            ->where('ss.status', 1)
            ->orderBy('a.subject_name', 'ASC')
            ->get()
            ->getResultArray();
        
        return $subjects;
    }
}


// Add this to server_helper.php

// Add these functions to your server_helper.php file

if (!function_exists('getTeacherClasses')) {
    /**
     * Get unique classes where teacher teaches
     * @param int $teacher_id Teacher user ID
     * @param int $campus_id Campus ID
     * @return array Array of class objects
     */
    function getTeacherClasses($teacher_id, $campus_id)
    {
        $db = \Config\Database::connect();
        
        if (!$teacher_id || !$campus_id) {
            return [];
        }
        
        $sql = "SELECT DISTINCT 
                    c.class_id, 
                    c.class_name, 
                    c.class_short_name
                FROM teacher_subjects ts
                INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                INNER JOIN classes c ON c.class_id = cs.class_id
                WHERE ts.tid = ?
                    AND cs.campus_id = ?
                    AND ts.status = 1
                    AND ss.status = 1
                    AND cs.status = 1
                    AND c.status = 1
                ORDER BY c.class_id ASC";
        
        return $db->query($sql, [$teacher_id, $campus_id])->getResult();
    }
}

if (!function_exists('getTeacherSubjects')) {
    /**
     * Get unique subjects where teacher teaches
     * @param int $teacher_id Teacher user ID
     * @param int $campus_id Campus ID
     * @return array Array of subject objects
     */
    function getTeacherSubjects($teacher_id, $campus_id)
    {
        $db = \Config\Database::connect();
        
        if (!$teacher_id || !$campus_id) {
            return [];
        }
        
        $sql = "SELECT DISTINCT 
                    a.sid, 
                    a.subject_name, 
                    a.subject_short_name
                FROM teacher_subjects ts
                INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                INNER JOIN allsubject a ON a.sid = ss.subject_id
                WHERE ts.tid = ?
                    AND cs.campus_id = ?
                    AND ts.status = 1
                    AND ss.status = 1
                    AND cs.status = 1
                    AND a.status = 1
                ORDER BY a.subject_name ASC";
        
        return $db->query($sql, [$teacher_id, $campus_id])->getResult();
    }
}

// ============================================
// SECTION 7: STUDENT FUNCTIONS
// ============================================

if (!function_exists('getStudentsBySection')) {
    /**
     * Get students by class section ID
     */
    function getStudentsBySection(int $section_id): array
    {
        $db = \Config\Database::connect();

        $studentClassRows = $db->table('student_class')
            ->where('cls_sec_id', $section_id)
            ->where('status', 1)
            ->get()
            ->getResult();

        if (empty($studentClassRows)) {
            return [];
        }

        $studentIds = array_column($studentClassRows, 'student_id');

        $students = $db->table('students')
            ->whereIn('student_id', $studentIds)
            ->orderBy('first_name', 'ASC')
            ->get()
            ->getResult();

        return $students;
    }
}


if (!function_exists('employeeProfileImage')) {
    /**
     * Get employee profile image HTML
     */
    function employeeProfileImage($photo = null, $returnHtml = true, $defaultImage = 'resource/adminlte/dist/img/emp-avatar.jpg', $imageClass = 'img-circle elevation-2', $imageStyle = 'width:140px;height:140px;object-fit:cover;')
    {
        // Get the photo URL using the helper
        $imageUrl = getEmployeePhotoUrl($photo, $defaultImage);
        
        if ($returnHtml) {
            return '<img src="' . $imageUrl . '" alt="Profile Photo" class="' . $imageClass . '" style="' . $imageStyle . '">';
        }
        
        return $imageUrl;
    }
}

if (!function_exists('getEmployeePhotoUrl')) {
    /**
     * Get employee photo URL only
     */
    function getEmployeePhotoUrl($photo = null, $defaultImage = 'resource/adminlte/dist/img/emp-avatar.jpg')
    {
        // If photo is provided and not empty
        if (!empty($photo)) {
            // Build the path to check if file exists in writable directory
            $filePath = WRITEPATH . 'uploads/employees-img/' . $photo;
            
            // Debug logging (check your logs at writable/logs/)
            log_message('debug', 'getEmployeePhotoUrl - Looking for photo: ' . $photo);
            log_message('debug', 'getEmployeePhotoUrl - Full path: ' . $filePath);
            log_message('debug', 'getEmployeePhotoUrl - File exists: ' . (file_exists($filePath) ? 'YES' : 'NO'));
            
            if (file_exists($filePath)) {
                $url = base_url('admin/getEmployeeImage/' . $photo);
                log_message('debug', 'getEmployeePhotoUrl - Returning URL: ' . $url);
                return $url;
            } else {
                log_message('error', 'getEmployeePhotoUrl - Photo file NOT FOUND: ' . $filePath);
            }
        } else {
            log_message('debug', 'getEmployeePhotoUrl - No photo provided, using default');
        }
        
        return base_url($defaultImage);
    }
}

if (!function_exists('studentProfileImage')) {
    /**
     * Get student profile image HTML (your existing function updated)
     */
    function studentProfileImage($photo)
    {
        $imageUrl = !empty($photo) ? base_url('uploads/' . $photo) : base_url('uploads/default.png');
        return '<img src="' . $imageUrl . '" alt="Student Photo" class="img-fluid">';
    }
}

// ============================================
// SECTION 8: TERM & ACADEMIC FUNCTIONS
// ============================================

if (!function_exists('termSessions')) {
    /**
     * Get term sessions
     */
    function termSessions()
    {
        $db = Database::connect();
        $session = session();
        $sessionid = (int) $session->get('member_sessionid');
        $campusid  = (int) $session->get('member_campusid');

        $schoolinfo = \App\Libraries\SafeQuery::systemForCampus($db, $campusid);
        if ($schoolinfo === null) {
            return [];
        }

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
    /**
     * Get term session by ID
     */
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

// ============================================
// SECTION 9: PERMISSION FUNCTIONS
// ============================================

if (!function_exists('currentUserIsSuperAdmin')) {
    /**
     * Memoized Super Admin check for the current request.
     * Super Admins bypass all permission gates (full menu + full access).
     */
    function currentUserIsSuperAdmin(): bool
    {
        static $cached = null;
        if ($cached === null) {
            helper('role');
            $cached = function_exists('userIsSuperAdmin') ? userIsSuperAdmin() : false;
        }
        return $cached;
    }
}

if (!function_exists('check_permission')) {
    /**
     * Check permission and redirect if not allowed
     */
    function check_permission($permKey, $json = true)
    {
        if (currentUserIsSuperAdmin()) {
            return;
        }

        $user = \App\Libraries\MemberCurrentUser::user();

        if (! $user) {
            $request = service('request');

            if ($json && admin_request_wants_json($request)) {
                $payload = admin_request_from_admin_area($request) || admin_client_has_session_cookie($request)
                    ? admin_session_expired_payload()
                    : admin_login_required_payload();
                json_response($payload, '', 401);
            }

            admin_auth_failure_response($request)->send();
            exit;
        }

        $perms = isset($user->userPerms) ? $user->userPerms : [];
        $permKey = strtolower((string) $permKey);

        if (isset($perms[$permKey]) && $perms[$permKey]) {
            return;
        }

        if ($json) {
            json_response([
                'success'  => false,
                'code'     => 'forbidden',
                'perm_key' => $permKey,
                'msg'      => 'You do not have permission to operate: ' . $permKey,
            ], '', 403);
        }

        echo view('admin/errors/html/error_403', [
            'permKey'  => $permKey,
            'permKeys' => [$permKey],
        ]);
        exit;
    }
}

if (! function_exists('check_any_permission')) {
    function check_any_permission(array $permKeys, $json = true)
    {
        if (currentUserIsSuperAdmin()) {
            return;
        }

        $user = \App\Libraries\MemberCurrentUser::user();

        if (! $user) {
            check_permission('', $json);

            return;
        }

        $perms = isset($user->userPerms) ? $user->userPerms : [];
        foreach ($permKeys as $permKey) {
            $permKey = strtolower((string) $permKey);
            if (isset($perms[$permKey]) && $perms[$permKey]) {
                return;
            }
        }

        if ($json) {
            json_response([
                'success'   => false,
                'code'      => 'forbidden',
                'perm_keys' => array_map('strtolower', $permKeys),
                'msg'       => 'You do not have permission for this action.',
            ], '', 403);
        }

        echo view('admin/errors/html/error_403', [
            'permKey'  => $permKeys[0] ?? '',
            'permKeys' => array_map('strtolower', $permKeys),
        ]);
        exit;
    }
}

if (!function_exists('hasPermission')) {
    /**
     * Check if user has permission
     */
    function hasPermission($permKey)
    {
        if (currentUserIsSuperAdmin()) {
            return true;
        }
        $user = \App\Libraries\MemberCurrentUser::user();
        $perms = $user->userPerms ?? [];
        $permKey = strtolower((string) $permKey);
        return isset($perms[$permKey]) && $perms[$permKey];
    }
}

if (!function_exists('hasAnyPermission')) {
    /**
     * @param list<string> $permKeys
     */
    function hasAnyPermission(array $permKeys): bool
    {
        foreach ($permKeys as $permKey) {
            if (hasPermission($permKey)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('addPermission')) {
    /**
     * Add new permission
     */
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

if (!function_exists('permissions_list')) {
    /**
     * Get all permissions
     */
    function permissions_list()
    {
        $db = Database::connect();
        return $db->table('permissions')->get()->getResult();
    }
}

// ============================================
// SECTION 10: FORMATTING FUNCTIONS
// ============================================

if (!function_exists('calculateAge')) {
    /**
     * Calculate age from date of birth
     */
    function calculateAge($dob)
    {
        if (!$dob) return null;
        $dob = new \DateTime($dob);
        $now = new \DateTime();
        return $dob->diff($now)->y;
    }
}

if (!function_exists('formatContacts')) {
    /**
     * Format parent contacts
     */
    function formatContacts($parentinfo)
    {
        $contacts = [];
        if (!empty($parentinfo->phone1)) $contacts[] = $parentinfo->phone1;
        if (!empty($parentinfo->phone2)) $contacts[] = $parentinfo->phone2;
        if (!empty($parentinfo->email)) $contacts[] = $parentinfo->email;
        return implode(' / ', $contacts);
    }
}

if (!function_exists('dateFormat')) {
    /**
     * Format date: 15 Mar 2025
     */
    function dateFormat($originalDate)
    {
        return date("d M Y", strtotime($originalDate));
    }
}

if (!function_exists('dayDateFormat')) {
    /**
     * Format date with day: Mon, 15 Mar 2025
     */
    function dayDateFormat($originalDate)
    {
        return date("D, d M Y", strtotime($originalDate));
    }
}

if (!function_exists('systemDateFormat')) {
    /**
     * Format date for database: 2025-03-15
     */
    function systemDateFormat($originalDate)
    {
        return date("Y-m-d", strtotime($originalDate));
    }
}

if (!function_exists('str_cut')) {
    /**
     * Cut string to specified length with ellipsis
     */
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

// ============================================
// SECTION 11: CACHE FUNCTIONS
// ============================================

if (!function_exists('cxp_update_cache')) {
    /**
     * Clear cache
     */
    function cxp_update_cache($site_id = 0, $cachekey = '')
    {
        helper('filesystem');
        delete_files(WRITEPATH . 'cache/', false, true);
    }
}