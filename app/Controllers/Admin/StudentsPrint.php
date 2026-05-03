<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class StudentsPrint extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

  public function index(): string
{
    $campusId = (int) session('member_campusid');

    // Classes that have at least one active section in this campus
    $classes = $this->db->table('classes c')
        ->select('DISTINCT c.class_id, c.class_name', false)
        ->join('class_section cs', 'cs.class_id = c.class_id', 'inner')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_id', 'ASC')
        ->get()
        ->getResultArray();

    // Active class sections for this campus
    $classSections = $this->db->table('class_section cs')
        ->select('
            cs.cls_sec_id,
            cs.class_id,
            cs.section_id,
            CONCAT(c.class_name, " - ", s.section_name) AS label
        ', false)
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('s.section_name', 'ASC')
        ->get()
        ->getResultArray();

    // ========== ADD SECTIONS CLASS INFO FOR READMISSION ==========
    $sectionsclassinfo = $this->db->table('class_section cs')
        ->select('
            cs.cls_sec_id,
            CONCAT(c.class_name, " - ", s.section_name) AS label
        ', false)
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_name', 'ASC')
        ->orderBy('s.section_name', 'ASC')
        ->get()
        ->getResultArray();
    // ========== END SECTIONS CLASS INFO ==========

    // Calculate stats
    $totalStudents = $this->db->table('students')
        ->where('campus_id', $campusId)
        ->countAllResults();
    
    $currentStudents = $this->db->table('students')
        ->where('campus_id', $campusId)
        ->where('status', 1)
        ->countAllResults();
    
    $droppedStudents = $this->db->table('students')
        ->where('campus_id', $campusId)
        ->where('status !=', 1)
        ->countAllResults();
    
    $slcCount = $this->db->table('school_leaving_certificates slc')
        ->join('students s', 's.student_id = slc.student_id')
        ->where('s.campus_id', $campusId)
        ->countAllResults();

    return view('admin/students_print', [
        'classes'           => $classes,
        'classSections'     => $classSections,
        'sectionsclassinfo' => $sectionsclassinfo,  // ADD THIS LINE
        'stats'             => [
            'total_students'   => $totalStudents,
            'current_students' => $currentStudents,
            'dropped_students' => $droppedStudents,
            'slc_count'        => $slcCount
        ]
    ]);
}


public function data(): \CodeIgniter\HTTP\ResponseInterface
{
    $req = $this->request;
    $draw = (int) $req->getPost('draw');
    $start = (int) $req->getPost('start');
    $length = (int) $req->getPost('length');
    
    $searchName = trim((string) $req->getPost('search_name'));
    $searchFather = trim((string) $req->getPost('search_father'));
    $classId = (string) $req->getPost('class_id');
    $clsSecId = (int) $req->getPost('cls_sec_id');
    
    $showAll = $req->getPost('show_all') === 'true' ? true : false;
    $showAllParam = $req->getPost('show_all');
$showAll = ($showAllParam === 'true' || $showAllParam === '1' || $showAllParam === true);

    $sessionId = (int) session('member_sessionid');
    $campusId = (int) session('member_campusid');

    // Base query
    $base = $this->db->table('students s')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->join(
            'student_class sc',
            'sc.student_id = s.student_id AND sc.session_id = ' . (int) $sessionId,
            'left'
        )
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->join('classes ac', 'ac.class_id = s.class_id', 'left')
        ->where('s.campus_id', $campusId);

    // Apply status filter - show only current students by default
    if (!$showAll) {
        $base->where('s.status', 1);
    }
    // If showAll is true, show all students (no status filter)

    // recordsTotal (NO filters except campus)
    $totalQ = clone $base;
    $recordsTotal = (int) ($totalQ->select('COUNT(DISTINCT s.student_id) AS cnt', false)->get()->getRow('cnt') ?? 0);

    // Apply filters on a clone
    $filtered = clone $base;

    // Name filter
    if ($searchName !== '') {
        $escaped = $this->db->escapeLikeString($searchName);
        $filtered->groupStart()
            ->like('s.first_name', $escaped, 'both')
            ->orLike('s.last_name', $escaped, 'both')
            ->orWhere("CONCAT(s.first_name, ' ', s.last_name) LIKE " . $this->db->escape('%' . $escaped . '%') . " ESCAPE '!'", null, false)
            ->groupEnd();
    }

    // Father name filter
    if ($searchFather !== '') {
        $ft = $this->db->escapeLikeString($searchFather);
        $filtered->like('p.f_name', $ft, 'both');
    }

    // Class filter
    if ($classId !== '' && ctype_digit((string)$classId)) {
        $filtered->where('c.class_id', (int) $classId);
    }

    // Class Section filter
    if ($clsSecId > 0) {
        $filtered->where('cs.cls_sec_id', $clsSecId);
    }

    // recordsFiltered
    $countQ = clone $filtered;
    $recordsFiltered = (int) ($countQ->select('COUNT(DISTINCT s.student_id) AS cnt', false)->get()->getRow('cnt') ?? 0);

    // Data query
    $builder = clone $filtered;

    $builder->select("
        s.student_id,
        s.profile_photo,
        s.reg_no,
        s.first_name,
        s.last_name,
        s.status,
        CASE s.status 
            WHEN 1 THEN 'Current' 
            WHEN 4 THEN 'Dropped'
            ELSE 'Other'
        END AS status_text,
        p.parent_id,
        p.f_name AS father_name,
        p.father_cnic,
        s.std_cnic,
        s.gender,
        s.date_of_birth AS dob,
        TIMESTAMPDIFF(YEAR, s.date_of_birth, CURDATE()) AS age,
        c.class_name,
        sec.section_name,
        s.discounted_amount,
        s.date_of_admission,
        p.father_contact,
        p.mother_contact,
        p.emergency_contact,
        p.whatsapp,
        p.address_line1,
        s.previous_school,
        s.ps_city,
        s.health_conditions,
        s.major_injuries,
        s.class_id AS admission_class_id,
        ac.class_name AS admission_class,
        p.caste,
        s.gr_no,
        s.gr_date,
        s.std_type,
        CASE s.std_type WHEN 1 THEN 'Daycare' WHEN 2 THEN 'Boarding' ELSE '' END AS std_type_text,
        p.religion,
        p.father_email,
        p.father_occupation,
        p.father_office_address,
        p.m_name,
        p.city,
        p.hear_source,
        p.emergency_contact_person,
        p.relationship,
        (SELECT COUNT(*) FROM school_leaving_certificates slc WHERE slc.student_id = s.student_id) as has_slc,
        (SELECT slc.id FROM school_leaving_certificates slc WHERE slc.student_id = s.student_id ORDER BY slc.id DESC LIMIT 1) as slc_id
    ", false);

    // Ordering (same as before)
    $order = $req->getPost('order');
    $dtCols = $req->getPost('columns') ?? [];
    $orderable = [
        'rownum' => null,
        'profile_photo' => null,
        'student_id' => 's.student_id',
        'reg_no' => 's.reg_no',
        'student_name' => ['s.first_name', 's.last_name'],
        'father_name' => 'p.f_name',
        'father_cnic' => 'p.father_cnic',
        'std_cnic' => 's.std_cnic',
        'gender' => 's.gender',
        'dob' => 's.date_of_birth',
        'age' => 'age',
        'class_name' => 'c.class_name',
        'section_name' => 'sec.section_name',
        'discounted_amount' => 's.discounted_amount',
        'date_of_admission' => 's.date_of_admission',
        'father_contact' => 'p.father_contact',
        'mother_contact' => 'p.mother_contact',
        'emergency_contact' => 'p.emergency_contact',
        'whatsapp_contact' => 'p.whatsapp',
        'address' => 'p.address_line1',
        'previous_school' => 's.previous_school',
        'ps_city' => 's.ps_city',
        'admission_class_id' => 's.class_id',
        'admission_class' => 'ac.class_name',
        'caste' => 's.caste',
        'gr_no' => 's.gr_no',
        'gr_date' => 's.gr_date',
        'std_type' => 's.std_type',
        'religion' => 'p.religion',
        'father_email' => 'p.father_email',
        'father_occupation' => 'p.father_occupation',
        'father_office_address' => 'p.father_office_address',
        'm_name' => 'p.m_name',
        'city' => 'p.city',
        'hear_source' => 'p.hear_source',
        'emergency_contact_person' => 'p.emergency_contact_person',
        'relationship' => 'p.relationship',
        'status' => 's.status',
        'status_text' => 'status_text'
    ];

    if (is_array($order)) {
        foreach ($order as $ord) {
            $idx = (int) ($ord['column'] ?? -1);
            $dir = (strtolower($ord['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';
            $dataKey = $dtCols[$idx]['data'] ?? '';
            if ($dataKey === 'student_name') {
                $builder->orderBy('s.first_name', $dir)->orderBy('s.last_name', $dir);
            } elseif (!empty($orderable[$dataKey])) {
                $col = $orderable[$dataKey];
                if (is_array($col)) {
                    foreach ($col as $c) {
                        $builder->orderBy($c, $dir);
                    }
                } elseif ($col !== null) {
                    $builder->orderBy($col, $dir);
                }
            }
        }
    } else {
        $builder->orderBy('c.class_name', 'ASC')
            ->orderBy('sec.section_name', 'ASC')
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC');
    }

    // Paging
    if ($length > 0) {
        $builder->limit($length, $start);
    }

    // Fetch rows
    $rows = $builder->get()->getResultArray();

    // Build response rows (same as before)
    $data = [];
    $i = 0;
    foreach ($rows as $r) {
        $raw = (string)($r['profile_photo'] ?? '');
        $src = $this->resolvePhotoUrl($raw) ?? $this->defaultAvatarUrl();
        $ultimate = $this->defaultAvatarUrl();
        $img = '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="Photo"
                  onerror="this.onerror=null;this.src=\'' . htmlspecialchars($ultimate, ENT_QUOTES, 'UTF-8') . '\';"
                  style="width:38px;height:38px;border-radius:4px;object-fit:cover">';

        $data[] = [
            'rownum' => $start + (++$i),
            'profile_photo' => $img,
            'student_id' => (int)($r['student_id'] ?? 0),
            'parent_id' => (int)($r['parent_id'] ?? 0),
            'reg_no' => (string)($r['reg_no'] ?? ''),
            'student_name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
            'father_name' => (string)($r['father_name'] ?? ''),
            'father_cnic' => (string)($r['father_cnic'] ?? ''),
            'std_cnic' => (string)($r['std_cnic'] ?? ''),
            'gender' => (string)($r['gender'] ?? ''),
            'dob' => (string)($r['dob'] ?? ''),
            'age' => (int)($r['age'] ?? 0),
            'has_slc' => (int)($r['has_slc'] ?? 0),
            'slc_id' => (int)($r['slc_id'] ?? 0),
            'class_name' => (string)($r['class_name'] ?? ''),
            'section_name' => (string)($r['section_name'] ?? ''),
            'discounted_amount' => number_format((float)($r['discounted_amount'] ?? 0)),
            'date_of_admission' => (string)($r['date_of_admission'] ?? ''),
            'father_contact' => (string)($r['father_contact'] ?? ''),
            'mother_contact' => (string)($r['mother_contact'] ?? ''),
            'emergency_contact' => (string)($r['emergency_contact'] ?? ''),
            'whatsapp_contact' => (string)($r['whatsapp'] ?? ''),
            'address' => (string)($r['address_line1'] ?? ''),
            'previous_school' => (string)($r['previous_school'] ?? ''),
            'ps_city' => (string)($r['ps_city'] ?? ''),
            'health_condition' => (string)($r['health_conditions'] ?? ''),
            'major_injuries' => (string)($r['major_injuries'] ?? ''),
            'admission_class_id' => (int)($r['admission_class_id'] ?? 0),
            'admission_class' => (string)($r['admission_class'] ?? ''),
            'caste' => (string)($r['caste'] ?? ''),
            'gr_no' => (string)($r['gr_no'] ?? ''),
            'gr_date' => (string)($r['gr_date'] ?? ''),
            'std_type' => (string)($r['std_type_text'] ?? ''),
            'std_type_id' => (int)($r['std_type'] ?? 0),
            'religion' => (string)($r['religion'] ?? ''),
            'father_email' => (string)($r['father_email'] ?? ''),
            'father_occupation' => (string)($r['father_occupation'] ?? ''),
            'father_office_address' => (string)($r['father_office_address'] ?? ''),
            'm_name' => (string)($r['m_name'] ?? ''),
            'city' => (string)($r['city'] ?? ''),
            'hear_source' => (string)($r['hear_source'] ?? ''),
            'emergency_contact_person' => (string)($r['emergency_contact_person'] ?? ''),
            'relationship' => (string)($r['relationship'] ?? ''),
            'status' => (int)($r['status'] ?? 1),
            'status_text' => (string)($r['status_text'] ?? 'Current')
        ];
    }

    return $this->response->setJSON([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data,
    ]);
}
    private function defaultAvatarUrl(): string
    {
        $candidates = [
            'assets/img/avatar.png',
            'assets/images/avatar.png',
            'images/avatar.png',
            'img/avatar.png',
        ];

        foreach ($candidates as $rel) {
            $disk = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
            if (is_file($disk)) {
                return base_url($rel) . '?v=' . filemtime($disk);
            }
        }

        $svg = rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">' .
            '<circle cx="32" cy="24" r="14" fill="#d1d5db"/>' .
            '<rect x="8" y="40" width="48" height="20" rx="10" fill="#e5e7eb"/>' .
            '</svg>'
        );
        return 'data:image/svg+xml;charset=UTF-8,' . $svg;
    }

    private function resolvePhotoUrl(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        if (preg_match('~^https?://~i', $raw)) {
            return $raw;
        }

        $webFolder = 'uploads';
        $diskFolder = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . $webFolder . DIRECTORY_SEPARATOR;

        $path = ltrim($raw, '/\\');
        if (stripos($path, $webFolder . '/') === 0) {
            $path = substr($path, strlen($webFolder) + 1);
        }

        $dirName = dirname($path);
        $baseName = basename($path);
        $hasDir = ($dirName !== '.' && $dirName !== '');

        $nameNoExt = pathinfo($baseName, PATHINFO_FILENAME);
        $origExt = pathinfo($baseName, PATHINFO_EXTENSION);
        $origExtLc = strtolower($origExt);

        $coreExts = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];
        $candidates = [];

        if ($origExt !== '') {
            $candidates[] = [$nameNoExt, $origExt];
            $candidates[] = [$nameNoExt, strtolower($origExt)];
            $candidates[] = [$nameNoExt, strtoupper($origExt)];

            if ($origExtLc === 'jpg') {
                $candidates[] = [$nameNoExt, 'jpeg'];
                $candidates[] = [$nameNoExt, 'JPEG'];
            } elseif ($origExtLc === 'jpeg') {
                $candidates[] = [$nameNoExt, 'jpg'];
                $candidates[] = [$nameNoExt, 'JPG'];
            }
        }

        foreach ($coreExts as $e) {
            $candidates[] = [$nameNoExt, $e];
            $candidates[] = [$nameNoExt, strtoupper($e)];
        }

        foreach ($candidates as [$n, $e]) {
            $rel = $hasDir ? ($dirName . '/' . $n . '.' . $e) : ($n . '.' . $e);
            $disk = $diskFolder . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
            if (is_file($disk)) {
                $segments = array_map('rawurlencode', explode('/', trim($rel, '/')));
                return base_url($webFolder . '/' . implode('/', $segments));
            }
        }

        $fallbackDisk = $diskFolder . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (is_file($fallbackDisk)) {
            $segments = array_map('rawurlencode', explode('/', trim($path, '/')));
            return base_url($webFolder . '/' . implode('/', $segments));
        }

        return null;
    }

    public function defaultView()
    {
        $userId = (int) session('member_userid');
        $pageKey = $this->request->getGet('page') ?: 'students_browse';

        $row = $this->db->table('user_view_prefs')
            ->select('state_json')
            ->where(['user_id' => $userId, 'page_key' => $pageKey])
            ->get()->getRowArray();

        if ($row && !empty($row['state_json'])) {
            return $this->response->setJSON(['success' => true, 'state' => json_decode($row['state_json'], true)]);
        }

        $default = [
            'visible' => array_fill(0, 20, true),
            'order' => [[11, 'asc'], [12, 'asc'], [4, 'asc']],
            'length' => 25,
        ];
        $default['visible'][2] = false;
        $default['visible'][19] = false;

        return $this->response->setJSON(['success' => true, 'state' => $default]);
    }

    public function saveView()
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405);
        }
        $userId = (int) session('member_userid');
        if ($userId <= 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Not authenticated']);
        }

        $pageKey = $this->request->getPost('page') ?: 'students_browse';
        $stateJson = (string) $this->request->getPost('state');

        $decoded = json_decode($stateJson, true);
        if (!is_array($decoded) || !isset($decoded['visible'], $decoded['order'], $decoded['length'])) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid state payload']);
        }

        if (isset($decoded['visible'][2])) $decoded['visible'][2] = false;
        $stateJson = json_encode($decoded);

        $sql = "INSERT INTO user_view_prefs (user_id, page_key, state_json, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE state_json = VALUES(state_json), updated_at = NOW()";
        $ok = $this->db->query($sql, [$userId, $pageKey, $stateJson]);

        $payload = ['success' => (bool) $ok];
        if (function_exists('csrf_hash')) {
            $payload['csrf'] = csrf_hash();
        }
        return $this->response->setJSON($payload);
    }

    /**
 * Get statistics for dashboard cards
 */


    
public function stats(): \CodeIgniter\HTTP\ResponseInterface
{
    $campusId = (int) $this->request->getPost('campus_id');
    $sessionId = (int) $this->request->getPost('session_id');
    
    // Debug: Log received values
    log_message('debug', 'Stats called - campus_id: ' . $campusId . ', session_id: ' . $sessionId);
    
    // If values are empty, try getting from session
    if ($campusId === 0) {
        $campusId = session('member_campusid');
        log_message('debug', 'campus_id from session: ' . $campusId);
    }
    
    if ($sessionId === 0) {
        $sessionId = session('member_sessionid');
        log_message('debug', 'session_id from session: ' . $sessionId);
    }
    
    // Total students (all statuses)
    $totalStudents = $this->db->table('students')
        ->where('campus_id', $campusId)
        ->countAllResults();
    
    // Current students (status = 1)
    $currentStudents = $this->db->table('students')
        ->where('campus_id', $campusId)
        ->where('status', 1)
        ->countAllResults();
    
    // Dropped students (status != 1)
    $droppedStudents = $this->db->table('students')
        ->where('campus_id', $campusId)
        ->where('status !=', 1)
        ->countAllResults();
    
    // SLC issued count
    $slcCount = $this->db->table('school_leaving_certificates slc')
        ->join('students s', 's.student_id = slc.student_id')
        ->where('s.campus_id', $campusId)
        ->countAllResults();
    
    // Debug: Log results
    log_message('debug', 'Stats results - total: ' . $totalStudents . ', current: ' . $currentStudents);
    
    return $this->response->setJSON([
        'success' => true,
        'total_students' => $totalStudents,
        'current_students' => $currentStudents,
        'dropped_students' => $droppedStudents,
        'slc_count' => $slcCount
    ]);
}


// In your Students_print controller

public function autocomplete_student()
{
    $term = $this->request->getGet('term');
    $campus_id = session('campus_id');
    
    if (strlen($term) < 3) {
        return $this->response->setJSON([]);
    }
    
    $db = \Config\Database::connect();
    $builder = $db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, c.class_name')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', '1')
        ->groupStart()
            ->like('s.first_name', $term, 'after')
            ->orLike('s.last_name', $term, 'after')
            ->orLike('CONCAT(s.first_name, " ", s.last_name)', $term, 'after')
        ->groupEnd()
        ->limit(10);
    
    $results = $builder->get()->getResult();
    
    return $this->response->setJSON($results);
}

public function autocomplete_father()
{
    $term = $this->request->getGet('term');
    $campus_id = session('campus_id');
    
    if (strlen($term) < 3) {
        return $this->response->setJSON([]);
    }
    
    $db = \Config\Database::connect();
    $builder = $db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, p.f_name as father_name, CONCAT(s.first_name, " ", s.last_name) as student_name')
        ->join('parents p', 'p.parent_id = s.parent_id')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', '1')
        ->like('p.f_name', $term, 'after')
        ->limit(10);
    
    $results = $builder->get()->getResult();
    
    return $this->response->setJSON($results);
}


}