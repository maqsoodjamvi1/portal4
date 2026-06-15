<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class StudentsPrint extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        check_permission('admin-students');
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

    // Active class sections (filters + readmission modal — single query)
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
        ->orderBy('s.section_id', 'ASC')
        ->get()
        ->getResultArray();

    $sectionsclassinfo = $classSections;

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

    $statusParam = strtolower((string) $this->request->getGet('status'));
    $initialShowAll = ($statusParam === '0' || $statusParam === 'all' || $statusParam === 'false');

    return view('admin/students_print', [
        'classes'            => $classes,
        'classSections'      => $classSections,
        'sectionsclassinfo'  => $sectionsclassinfo,
        'stats'              => [
            'total_students'   => $totalStudents,
            'current_students' => $currentStudents,
            'dropped_students' => $droppedStudents,
            'slc_count'        => $slcCount,
        ],
        'initial_show_all'   => $initialShowAll,
    ]);
}


public function data(): \CodeIgniter\HTTP\ResponseInterface
{
    $req = $this->request;
    $draw = (int) $req->getPost('draw');
    $start = (int) $req->getPost('start');
    $length = (int) $req->getPost('length');
    $exportAll = ($req->getPost('export_all') === '1' || $req->getPost('export_all') === 1);
    
    $searchName = trim((string) $req->getPost('search_name'));
    $searchFather = trim((string) $req->getPost('search_father'));
    $classId = (string) $req->getPost('class_id');
    $clsSecId = (int) $req->getPost('cls_sec_id');
    
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
        ->join(
            '(SELECT student_id, COUNT(*) AS slc_cnt, MAX(id) AS slc_last_id FROM school_leaving_certificates GROUP BY student_id) slc_sum',
            'slc_sum.student_id = s.student_id',
            'left'
        )
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
        IFNULL(slc_sum.slc_cnt, 0) AS has_slc,
        slc_sum.slc_last_id AS slc_id
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
        'caste' => 'p.caste',
        'health_condition' => 's.health_conditions',
        'major_injuries' => 's.major_injuries',
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
        $builder->orderBy('s.student_id', 'ASC');
    }

    // Paging (export_all or "All" page length = -1 → return full filtered set)
    $noLimit = $exportAll || $length < 0;
    if (! $noLimit && $length > 0) {
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
            'rownum' => $exportAll ? (++$i) : ($start + (++$i)),
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

    /**
     * Print-ready contact list (HTML) using the same filters as the directory grid.
     * mode=class: grouped by class_id (all sections under each class heading). mode=family: grouped by parent.
     */
    public function contactListPrint()
    {
        $sessionId = (int) session('member_sessionid');
        $campusId = (int) session('member_campusid');
        if ($campusId <= 0) {
            return redirect()->to(base_url('admin/students_print'))
                ->with('error', 'Please select a campus before printing the contact list.');
        }

        $mode = strtolower((string) $this->request->getGet('mode'));
        if (! in_array($mode, ['class', 'family'], true)) {
            $mode = 'class';
        }

        $searchName = trim((string) $this->request->getGet('search_name'));
        $searchFather = trim((string) $this->request->getGet('search_father'));
        $classId = (string) $this->request->getGet('class_id');
        $clsSecId = (int) $this->request->getGet('cls_sec_id');
        $showAllParam = $this->request->getGet('show_all');
        $showAll = ($showAllParam === 'true' || $showAllParam === '1' || $showAllParam === 1);

        $base = $this->db->table('students s')
            ->join('parents p', 'p.parent_id = s.parent_id', 'left')
            ->join(
                'student_class sc',
                'sc.student_id = s.student_id AND sc.session_id = ' . (int) $sessionId . ' AND sc.status = 1',
                'left'
            )
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('s.campus_id', $campusId);

        if (! $showAll) {
            $base->where('s.status', 1);
        }

        if ($searchName !== '') {
            $escaped = $this->db->escapeLikeString($searchName);
            $base->groupStart()
                ->like('s.first_name', $escaped, 'both')
                ->orLike('s.last_name', $escaped, 'both')
                ->orWhere("CONCAT(s.first_name, ' ', s.last_name) LIKE " . $this->db->escape('%' . $escaped . '%') . " ESCAPE '!'", null, false)
                ->groupEnd();
        }

        if ($searchFather !== '') {
            $ft = $this->db->escapeLikeString($searchFather);
            $base->like('p.f_name', $ft, 'both');
        }

        if ($classId !== '' && ctype_digit((string) $classId)) {
            $base->where('c.class_id', (int) $classId);
        }

        if ($clsSecId > 0) {
            $base->where('cs.cls_sec_id', $clsSecId);
        }

        $base->select("
            s.student_id,
            s.reg_no,
            s.first_name,
            s.last_name,
            s.status,
            CASE s.status WHEN 1 THEN 'Current' WHEN 4 THEN 'Dropped' ELSE 'Other' END AS status_text,
            IFNULL(p.parent_id, 0) AS parent_id,
            p.f_name AS father_name,
            p.m_name AS mother_name,
            IFNULL(c.class_id, 0) AS class_id,
            IFNULL(sec.section_id, 0) AS section_id,
            c.class_name,
            sec.section_name,
            p.father_contact,
            p.mother_contact,
            p.emergency_contact,
            p.whatsapp,
            p.address_line1,
            p.city,
            p.emergency_contact_person,
            p.relationship
        ", false);

        if ($mode === 'family') {
            $base->orderBy('IFNULL(p.parent_id, s.student_id)', 'ASC', false)
                ->orderBy('c.class_id', 'ASC')
                ->orderBy('sec.section_id', 'ASC')
                ->orderBy('s.first_name', 'ASC')
                ->orderBy('s.last_name', 'ASC');
        } else {
            // Class/section print: must be sorted by class_id then section_id so the view
            // can emit one heading per group without duplicate sections.
            $base->orderBy('c.class_id', 'ASC', false)
                ->orderBy('sec.section_id', 'ASC', false)
                ->orderBy('s.first_name', 'ASC')
                ->orderBy('s.last_name', 'ASC');
        }

        $rows = $base->get()->getResultArray();

        [$schoolName, $schoolLogo, $campusLabel] = $this->resolveContactListBranding($campusId);

        $filterSummary = [];
        if (! $showAll) {
            $filterSummary[] = 'Current students only';
        } else {
            $filterSummary[] = 'All statuses';
        }
        if ($classId !== '' && ctype_digit((string) $classId)) {
            $cn = $this->db->table('classes')->select('class_name')->where('class_id', (int) $classId)->get()->getRow('class_name');
            if ($cn) {
                $filterSummary[] = 'Class: ' . $cn;
            }
        }
        if ($clsSecId > 0) {
            $secRow = $this->db->table('class_section cs')
                ->select('CONCAT(c.class_name, " - ", s.section_name) AS lbl', false)
                ->join('classes c', 'c.class_id = cs.class_id')
                ->join('sections s', 's.section_id = cs.section_id')
                ->where('cs.cls_sec_id', $clsSecId)
                ->get()->getRow('lbl');
            if ($secRow) {
                $filterSummary[] = 'Section: ' . $secRow;
            }
        }
        if ($searchName !== '') {
            $filterSummary[] = 'Student search: ' . $searchName;
        }
        if ($searchFather !== '') {
            $filterSummary[] = 'Father search: ' . $searchFather;
        }

        return view('admin/students_print_contact_list', [
            'school_name'     => $schoolName,
            'school_logo'     => $schoolLogo,
            'campus_label'    => $campusLabel,
            'mode'            => $mode,
            'rows'            => $rows,
            'row_count'       => count($rows),
            'filter_summary'  => $filterSummary,
            'printed_at'      => date('d M Y, H:i'),
            'printed_by'      => trim((string) (session('member_name') ?? session('member_username') ?? '')),
        ]);
    }

    /**
     * Print-ready roster: one row per class section, student names comma-separated.
     */
    public function sectionRosterPrint()
    {
        $sessionId = (int) session('member_sessionid');
        $campusId = (int) session('member_campusid');
        if ($campusId <= 0) {
            return redirect()->to(base_url('admin/students_print'))
                ->with('error', 'Please select a campus before printing the section roster.');
        }

        $classId = (string) $this->request->getGet('class_id');
        $clsSecId = (int) $this->request->getGet('cls_sec_id');

        $builder = $this->db->table('class_section cs')
            ->select("
                cs.cls_sec_id,
                CONCAT(c.class_name, ' - ', sec.section_name) AS section_label,
                c.class_id,
                sec.section_id
            ", false)
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'inner')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1);

        if ($classId !== '' && ctype_digit((string) $classId)) {
            $builder->where('cs.class_id', (int) $classId);
        }

        if ($clsSecId > 0) {
            $builder->where('cs.cls_sec_id', $clsSecId);
        }

        $sections = $builder
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('sec.section_id', 'ASC')
            ->get()
            ->getResultArray();

        $rows = [];
        $totalStudents = 0;

        foreach ($sections as $section) {
            $students = $this->db->table('student_class sc')
                ->select("CONCAT(s.first_name, ' ', s.last_name) AS student_name, s.gender", false)
                ->join('students s', 's.student_id = sc.student_id', 'inner')
                ->where('sc.cls_sec_id', (int) $section['cls_sec_id'])
                ->where('sc.session_id', $sessionId)
                ->where('sc.status', 1)
                ->where('s.status', 1)
                ->where('s.campus_id', $campusId)
                ->orderBy('s.first_name', 'ASC')
                ->orderBy('s.last_name', 'ASC')
                ->get()
                ->getResultArray();

            $maleNames = [];
            $femaleNames = [];
            $otherNames = [];

            foreach ($students as $student) {
                $name = trim((string) ($student['student_name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $gender = strtolower(trim((string) ($student['gender'] ?? '')));
                if (in_array($gender, ['male', 'm', 'boy'], true)) {
                    $maleNames[] = $name;
                } elseif (in_array($gender, ['female', 'f', 'girl'], true)) {
                    $femaleNames[] = $name;
                } else {
                    $otherNames[] = $name;
                }
            }

            $count = count($maleNames) + count($femaleNames) + count($otherNames);
            $totalStudents += $count;

            $rows[] = [
                'section_label'  => (string) ($section['section_label'] ?? ''),
                'male_names'     => $maleNames,
                'female_names'   => $femaleNames,
                'other_names'    => $otherNames,
                'student_count'  => $count,
                'male_count'     => count($maleNames),
                'female_count'   => count($femaleNames),
            ];
        }

        [$schoolName, $schoolLogo, $campusLabel] = $this->resolveContactListBranding($campusId);

        $sessionRow = $this->db->table('academic_session')
            ->select('session_name')
            ->where('session_id', $sessionId)
            ->get()
            ->getRow('session_name');

        $filterSummary = ['Current students only', 'Session: ' . trim((string) ($sessionRow ?? ''))];
        if ($classId !== '' && ctype_digit((string) $classId)) {
            $cn = $this->db->table('classes')->select('class_name')->where('class_id', (int) $classId)->get()->getRow('class_name');
            if ($cn) {
                $filterSummary[] = 'Class: ' . $cn;
            }
        }
        if ($clsSecId > 0) {
            $secRow = $this->db->table('class_section cs')
                ->select('CONCAT(c.class_name, " - ", s.section_name) AS lbl', false)
                ->join('classes c', 'c.class_id = cs.class_id')
                ->join('sections s', 's.section_id = cs.section_id')
                ->where('cs.cls_sec_id', $clsSecId)
                ->get()->getRow('lbl');
            if ($secRow) {
                $filterSummary[] = 'Section: ' . $secRow;
            }
        }

        return view('admin/students_print_section_roster', [
            'school_name'     => $schoolName,
            'school_logo'     => $schoolLogo,
            'campus_label'    => $campusLabel,
            'rows'            => $rows,
            'section_count'   => count($rows),
            'student_count'   => $totalStudents,
            'filter_summary'  => $filterSummary,
            'printed_at'      => date('d M Y, H:i'),
            'printed_by'      => trim((string) (session('member_name') ?? session('member_username') ?? '')),
        ]);
    }

    /**
     * School name + logo for print reports (scoped to the user's campus / system).
     *
     * @return array{0: string, 1: string, 2: string} [schoolName, logoUrl, campusLabel]
     */
    private function resolveContactListBranding(int $campusId): array
    {
        $schoolName = 'School';
        $schoolLogo = '';
        $campusLabel = '';

        if ($campusId > 0) {
            $campusRow = $this->db->table('campus')
                ->select('campus_name, system_id')
                ->where('campus_id', $campusId)
                ->get()
                ->getRow();

            if ($campusRow) {
                $campusLabel = trim((string) ($campusRow->campus_name ?? ''));
                $systemId = (int) ($campusRow->system_id ?? 0);
                if ($systemId > 0) {
                    $schoolRow = $this->db->table('system')
                        ->select('system_name, logo')
                        ->where('system_id', $systemId)
                        ->get()
                        ->getRow();
                    if ($schoolRow) {
                        $name = trim((string) ($schoolRow->system_name ?? ''));
                        if ($name !== '') {
                            $schoolName = $name;
                        }
                        $logoFile = trim((string) ($schoolRow->logo ?? ''));
                        if ($logoFile !== '') {
                            $schoolLogo = base_url('system-logo/' . $logoFile);
                        }
                    }
                }
            }
        }

        return [$schoolName, $schoolLogo, $campusLabel];
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
            'order' => [[2, 'asc']],
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

    if ($campusId === 0) {
        $campusId = (int) session('member_campusid');
    }

    if ($sessionId === 0) {
        $sessionId = (int) session('member_sessionid');
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
    $campus_id = (int) (session('member_campusid') ?: $this->request->getGet('campus_id'));
    
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
    $campus_id = (int) (session('member_campusid') ?: $this->request->getGet('campus_id'));
    
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

public function bonafideCertificate()
{
    $studentId = (int) $this->request->getGet('student_id');
    $campusId  = (int) session('member_campusid');
    $sessionId = (int) session('member_sessionid');
    $loginUserId = (int) session('member_userid');

    if ($studentId <= 0 || $campusId <= 0) {
        return redirect()->to(base_url('admin/students_print'))->with('error', 'Invalid student selection.');
    }

    $student = $this->db->table('students s')
        ->select("
            s.student_id, s.class_id, s.reg_no, s.first_name, s.last_name, s.date_of_birth, s.status,
            p.f_name AS father_name,
            c.class_name,
            sec.section_name
        ", false)
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . (int) $sessionId, 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.student_id', $studentId)
        ->where('s.campus_id', $campusId)
        ->get()
        ->getRowArray();

    if (!$student) {
        return redirect()->to(base_url('admin/students_print'))->with('error', 'Student not found.');
    }

    $school = $this->db->table('system')->get()->getRowArray();
    $schoolInfoObj = function_exists('getSchoolInfo') ? getSchoolInfo() : null;
    $campusInfoObj = function_exists('getCampusInfo') ? getCampusInfo() : null;
    $schoolName = trim((string)($school['system_name'] ?? 'School'));

    $recipientMode = strtolower((string) $this->request->getGet('recipient_mode'));
    $recipientName = trim((string) $this->request->getGet('recipient_name'));
    $purposeText = trim((string) $this->request->getGet('purpose'));

    $toggles = [
        'show_reg_no'     => $this->asBool($this->request->getGet('show_reg_no'), true),
        'show_father'     => $this->asBool($this->request->getGet('show_father'), true),
        'show_class'      => $this->asBool($this->request->getGet('show_class'), true),
        'show_dob'        => $this->asBool($this->request->getGet('show_dob'), true),
        'show_current_fee'=> $this->asBool($this->request->getGet('show_current_fee'), true),
        'show_monthly_fee'=> $this->asBool($this->request->getGet('show_monthly_fee'), true),
        'show_issue_date' => $this->asBool($this->request->getGet('show_issue_date'), true),
    ];

    $student = $this->resolveBonafideClassSection($student, $studentId, $campusId, $sessionId);

    $currentFee = $this->fetchCurrentFeeAmount($studentId);
    $monthlyFee = $this->fetchStudentMonthlyFee($student, $campusId, $sessionId, (int)($school['system_id'] ?? 0));
    $fullName   = trim((string)($student['first_name'] ?? '') . ' ' . (string)($student['last_name'] ?? ''));
    $classText  = trim((string)($student['class_name'] ?? ''));
    $section    = trim((string)($student['section_name'] ?? ''));
    if ($section !== '') {
        $classText .= ($classText !== '' ? ' - ' : '') . $section;
    }

    $principalName = '';
    if ($loginUserId > 0) {
        $loginUser = $this->db->table('users')
            ->select('first_name, last_name, username')
            ->where('id', $loginUserId)
            ->get()
            ->getRowArray();
        if ($loginUser) {
            $principalName = trim(((string)($loginUser['first_name'] ?? '')) . ' ' . ((string)($loginUser['last_name'] ?? '')));
            if ($principalName === '') {
                $principalName = trim((string)($loginUser['username'] ?? ''));
            }
        }
    }

    $schoolPhone = $this->pickFirstNonEmpty(
        $school['phone'] ?? null,
        $school['mobile_no'] ?? null,
        $school['landline'] ?? null,
        is_object($schoolInfoObj) ? ($schoolInfoObj->phone ?? null) : null,
        is_object($schoolInfoObj) ? ($schoolInfoObj->mobile_no ?? null) : null,
        is_object($schoolInfoObj) ? ($schoolInfoObj->landline ?? null) : null,
        is_object($campusInfoObj) ? ($campusInfoObj->mobile_no ?? null) : null,
        is_object($campusInfoObj) ? ($campusInfoObj->landline ?? null) : null
    );

    $schoolEmail = $this->pickFirstNonEmpty(
        $school['email'] ?? null,
        $school['school_email'] ?? null,
        is_object($schoolInfoObj) ? ($schoolInfoObj->email ?? null) : null,
        is_object($schoolInfoObj) ? ($schoolInfoObj->school_email ?? null) : null,
        is_object($campusInfoObj) ? ($campusInfoObj->email ?? null) : null
    );

    $schoolAddress = $this->pickFirstNonEmpty(
        $school['address'] ?? null,
        is_object($schoolInfoObj) ? ($schoolInfoObj->address ?? null) : null,
        is_object($campusInfoObj) ? ($campusInfoObj->location ?? null) : null
    );

    return view('admin/bonafide_certificate', [
        'school_name'     => $schoolName !== '' ? $schoolName : 'School',
        'student'         => $student,
        'student_name'    => $fullName,
        'class_text'      => $classText,
        'recipient_mode'  => ($recipientMode === 'custom') ? 'custom' : 'twmc',
        'recipient_name'  => $recipientName,
        'toggles'         => $toggles,
        'issue_date'      => date('d M Y'),
        'dob_display'     => $this->formatBonafideDobDisplay($student['date_of_birth'] ?? ''),
        'current_fee'     => $currentFee,
        'monthly_fee'     => $monthlyFee,
        'school_logo'     => !empty($school['logo']) ? base_url('system-logo/' . $school['logo']) : '',
        'school_phone'    => $schoolPhone,
        'school_address'  => $schoolAddress,
        'school_email'    => $schoolEmail,
        'purpose_text'    => $purposeText,
        'principal_name'  => $principalName,
    ]);
}

private function pickFirstNonEmpty(...$values): string
{
    foreach ($values as $value) {
        $v = trim((string)($value ?? ''));
        if ($v !== '') {
            return $v;
        }
    }

    return '';
}

private function resolveBonafideClassSection(array $student, int $studentId, int $campusId, int $sessionId): array
{
    $className = trim((string)($student['class_name'] ?? ''));
    $sectionName = trim((string)($student['section_name'] ?? ''));

    if ($className !== '' && $sectionName !== '') {
        return $student;
    }

    // 1) Try current session enrollment first.
    $row = $this->db->table('student_class sc')
        ->select('c.class_name, sec.section_name')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('sc.student_id', $studentId)
        ->where('sc.session_id', $sessionId)
        ->where('sc.status', 1)
        ->limit(1)
        ->get()
        ->getRowArray();

    if ($row) {
        $student['class_name'] = $className !== '' ? $className : trim((string)($row['class_name'] ?? ''));
        $student['section_name'] = $sectionName !== '' ? $sectionName : trim((string)($row['section_name'] ?? ''));
        $className = trim((string)($student['class_name'] ?? ''));
        $sectionName = trim((string)($student['section_name'] ?? ''));
    }

    // 2) Fallback to latest active enrollment across sessions.
    if ($className === '' || $sectionName === '') {
        $latest = $this->db->table('student_class sc')
            ->select('c.class_name, sec.section_name')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('sc.student_id', $studentId)
            ->where('sc.status', 1)
            ->orderBy('sc.session_id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($latest) {
            $student['class_name'] = $className !== '' ? $className : trim((string)($latest['class_name'] ?? ''));
            $student['section_name'] = $sectionName !== '' ? $sectionName : trim((string)($latest['section_name'] ?? ''));
            $className = trim((string)($student['class_name'] ?? ''));
        }
    }

    // 3) Final fallback: students.class_id if enrollment records are missing.
    if ($className === '' && !empty($student['class_id'])) {
        $classRow = $this->db->table('classes')
            ->select('class_name')
            ->where('class_id', (int) $student['class_id'])
            ->limit(1)
            ->get()
            ->getRowArray();
        if ($classRow) {
            $student['class_name'] = trim((string)($classRow['class_name'] ?? ''));
        }
    }

    return $student;
}

private function fetchCurrentFeeAmount(int $studentId): float
{
    $row = $this->db->table('fee_chalan')
        ->select('SUM(amount - IFNULL(discount,0)) AS due_amount', false)
        ->where('student_id', $studentId)
        ->where('status', 'unpaid')
        ->get()
        ->getRowArray();

    return (float)($row['due_amount'] ?? 0);
}

private function asBool($value, bool $default = false): bool
{
    if ($value === null || $value === '') {
        return $default;
    }

    if (is_bool($value)) {
        return $value;
    }

    $v = strtolower(trim((string)$value));
    return in_array($v, ['1', 'true', 'yes', 'on'], true);
}

/** e.g. 03/15/2012 (Fifteen March Two Thousand Twelve) */
private function formatBonafideDobDisplay(?string $raw): string
{
    $raw = trim((string) $raw);
    if ($raw === '' || $raw === '0000-00-00' || $raw === '00/00/0000') {
        return '';
    }

    $ts = strtotime($raw);
    if ($ts === false) {
        return '';
    }

    $numeric = date('m/d/Y', $ts);
    $day     = (int) date('j', $ts);
    $month   = date('F', $ts);
    $year    = (int) date('Y', $ts);
    $words   = $this->bonafideNumberToWords($day) . ' ' . $month . ' ' . $this->bonafideYearToWords($year);

    return $numeric . ' (' . $words . ')';
}

private function bonafideNumberToWords(int $number): string
{
    static $words = [
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        21 => 'Twenty One', 22 => 'Twenty Two', 23 => 'Twenty Three', 24 => 'Twenty Four', 25 => 'Twenty Five',
        26 => 'Twenty Six', 27 => 'Twenty Seven', 28 => 'Twenty Eight', 29 => 'Twenty Nine', 30 => 'Thirty',
        31 => 'Thirty One',
    ];

    if (isset($words[$number])) {
        return $words[$number];
    }

    if ($number < 100) {
        $tens = (int) (floor($number / 10) * 10);
        $ones = $number % 10;
        $parts = [];
        if ($tens > 0 && isset($words[$tens])) {
            $parts[] = $words[$tens];
        }
        if ($ones > 0 && isset($words[$ones])) {
            $parts[] = $words[$ones];
        }

        return implode(' ', $parts);
    }

    return (string) $number;
}

private function bonafideYearToWords(int $year): string
{
    if ($year >= 2000 && $year < 2100) {
        $remainder = $year - 2000;
        if ($remainder === 0) {
            return 'Two Thousand';
        }

        return 'Two Thousand ' . $this->bonafideNumberToWords($remainder);
    }

    if ($year >= 1900 && $year < 2000) {
        $remainder = $year - 1900;
        if ($remainder === 0) {
            return 'Nineteen Hundred';
        }

        return 'Nineteen Hundred ' . $this->bonafideNumberToWords($remainder);
    }

    return $this->bonafideNumberToWords($year);
}

private function fetchStudentMonthlyFee(array $student, int $campusId, int $sessionId, int $systemId): float
{
    $classId = (int)($student['class_id'] ?? 0);
    $studentId = (int)($student['student_id'] ?? 0);
    if ($classId <= 0 || $studentId <= 0 || $campusId <= 0 || $sessionId <= 0 || $systemId <= 0) {
        return 0.0;
    }

    $monthlyType = $this->db->table('fee_type')
        ->select('fee_type_id')
        ->where([
            'system_id'      => $systemId,
            'status'         => 1,
            's_flag'         => 1,
            'is_monthly_fee' => 1,
        ])
        ->get()
        ->getRowArray();

    if (!$monthlyType) {
        return 0.0;
    }

    $stdRow = $this->db->table('fee_amount')
        ->select('amount')
        ->where([
            'class_id'    => $classId,
            'campus_id'   => $campusId,
            'session_id'  => $sessionId,
            'fee_type_id' => (int)$monthlyType['fee_type_id'],
        ])
        ->get()
        ->getRowArray();

    $studentRow = $this->db->table('students')
        ->select('discounted_amount')
        ->where('student_id', $studentId)
        ->get()
        ->getRowArray();

    $standard = (float)($stdRow['amount'] ?? 0);
    $discount = (float)($studentRow['discounted_amount'] ?? 0);
    $monthly = $standard - $discount;
    if ($monthly < 0) {
        $monthly = 0;
    }

    return $monthly;
}


}