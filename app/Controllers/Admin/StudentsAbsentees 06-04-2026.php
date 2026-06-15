<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use stdClass;

/**
 * Students Absentees Manage
 *
 * @author      Maqsood Ahmed
 * @copyright   Copyright (c) 2018-2019 TIME Soft Solutions
 * @email       maqsoodjamvi@gmail.com
 */
class StudentsAbsentees extends BaseController
{
    protected $session;
    protected $db;

    function __construct()
    {
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        check_permission('admin-add-student-absentees');
    }

    /**
     * Index Page for this controller.
     */
   

    public function class_section_attendance()
{
    // This should return the original class/section attendance view
    // You can reuse your existing add() method logic
    return $this->add(); // Or create a dedicated view
}

public function search_attendance()
{
    // This should return the search by name view
    // You can create a simplified version or reuse existing code
    return view('admin/students_absentees_search', $this->template_data);
}


/**
 * Main attendance page with tabs (Class/Section, Search, Face Recognition)
 */
public function index()
{
    check_permission('admin-add-student-absentees');
    
    $schoolinfo = getSchoolInfo();
    $campusid   = (int) session('member_campusid');
    $sessionid  = (int) session('member_sessionid');
    
    $date = $this->request->getGet('date');
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }
    
    $this->template_data['sessionData'] = [
        'campusid'  => $campusid,
        'sessionid' => $sessionid,
        'date'      => $date,
    ];
    
    // Get classes for class/section tab
    $this->template_data['classesinfo'] = $this->db->table('classes c')
        ->distinct()
        ->select('c.*')
        ->join('class_section cs', 'cs.class_id = c.class_id', 'inner')
        ->where('c.system_id', $schoolinfo->system_id)
        ->where('cs.campus_id', $campusid)
        ->where('cs.status', 1)
        ->orderBy('c.class_name', 'ASC')
        ->get()
        ->getResult();
    
    // Get sections for class/section tab
    $sections = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, s.section_id, s.section_name, c.class_name')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->where('cs.campus_id', $campusid)
        ->where('cs.status', 1)
        ->orderBy('c.class_name', 'ASC')
        ->orderBy('s.section_name', 'ASC')
        ->get()
        ->getResultArray();
    
    $this->template_data['sectionsclassinfo'] = array_map(static function (array $r) {
        return [
            'cls_sec_id' => (int) $r['cls_sec_id'],
            'section_id' => (int) $r['cls_sec_id'],
            'sectionclassname' => trim(($r['class_name'] ?? '') . ' (' . ($r['section_name'] ?? '') . ')'),
        ];
    }, $sections);
    
    $this->template_data['campusinfo'] = $this->db->table('campus')
        ->where('campus_id', $campusid)
        ->get()
        ->getResult();
    
    $this->template_data['academic_session'] = $this->db->table('academic_session')
        ->where('session_id', $sessionid)
        ->get()
        ->getResult();
    
    return view('admin/students_absentees_main', $this->template_data);
}

/**
 * Original add method - for class/section attendance only (used by AJAX)
 */

public function add()
{
    check_permission('admin-add-student-absentees');

    $schoolinfo = getSchoolInfo();
    $campusid   = (int) session('member_campusid');
    $sessionid  = (int) session('member_sessionid');

    $date = $this->request->getGet('date');
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    $this->template_data['sessionData'] = [
        'campusid'  => $campusid,
        'sessionid' => $sessionid,
        'date'      => $date,
    ];

    $db = \Config\Database::connect();

    // FIXED: Get ALL classes, not just those with mark_attendance
    $classes = $db->table('classes c')
        ->select('c.*')
        ->join('class_section cs', 'cs.class_id = c.class_id', 'inner')
        ->where('c.system_id', $schoolinfo->system_id)
        ->where('cs.campus_id', $campusid)
        ->where('cs.status', 1)
        ->groupBy('c.class_id')
        ->orderBy('c.class_name', 'ASC')
        ->get()
        ->getResult();
    
    $this->template_data['classesinfo'] = $classes;

    // FIXED: Get ALL sections
    $sections = $db->table('class_section cs')
        ->select('cs.cls_sec_id, s.section_id, s.section_name, c.class_name')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->where('cs.campus_id', $campusid)
        ->where('cs.status', 1)
        ->orderBy('c.class_name', 'ASC')
        ->orderBy('s.section_name', 'ASC')
        ->get()
        ->getResultArray();

    $this->template_data['sectionsclassinfo'] = array_map(static function (array $r) {
        return [
            'cls_sec_id'        => (int) $r['cls_sec_id'],
            'section_id'        => (int) $r['cls_sec_id'],
            'sectionclassname'  => trim(($r['class_name'] ?? '') . ' (' . ($r['section_name'] ?? '') . ')'),
        ];
    }, $sections);

    $this->template_data['campusinfo'] = $db->table('campus')
        ->where('campus_id', $campusid)
        ->get()
        ->getResult();

    $this->template_data['examinfo'] = $db->table('exam')
        ->where('campus_id', $campusid)
        ->where('session_id', $sessionid)
        ->get()
        ->getResult();

    $this->template_data['academic_session'] = $db->table('academic_session')
        ->where('session_id', $sessionid)
        ->get()
        ->getResult();

    $this->template_data['subjectinfo'] = $db->table('allsubject')->get()->getResult();

    return view('admin/students_absentees_edit', $this->template_data);
}

/**
 * Face Recognition Content Only (No layout, just the content)
 */
public function face_recognition_content()
{
    $campus_id = (int) $this->request->getGet('campus_id');
    $session_id = (int) $this->request->getGet('session_id');
    $date = $this->request->getGet('date');
    
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }
    
    $data = [
        'campus_id' => $campus_id,
        'session_id' => $session_id,
        'date_value' => $date
    ];
    
    return view('admin/students_absentees_face_content', $data);
}

/**
 * Face Recognition page (standalone)
 */
public function face_recognition()
{
    check_permission('admin-add-student-absentees');

    $schoolinfo = getSchoolInfo();
    $campusid   = (int) session('member_campusid');
    $sessionid  = (int) session('member_sessionid');

    $date = $this->request->getGet('date');
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    $this->template_data['sessionData'] = [
        'campusid'  => $campusid,
        'sessionid' => $sessionid,
        'date'      => $date,
    ];

    $this->template_data['campusinfo'] = $this->db->table('campus')
        ->where('campus_id', $campusid)
        ->get()
        ->getResult();

    $this->template_data['academic_session'] = $this->db->table('academic_session')
        ->where('session_id', $sessionid)
        ->get()
        ->getResult();

    return view('admin/students_absentees_face', $this->template_data);
}
    public function data()
    {
        $response = new stdClass;
        $response->draw = $this->request->getPost('draw');
        $sessionid = $this->session->get('member_sessionid');

        $search = $this->request->getPost('search');
        $keyword = '';
        if ($search) $keyword = $search['value'];
        
        $response->recordsTotal = $this->db->table('attendance')->countAllResults();

        $results = $this->db->table('attendance')->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;

        $this->db->table('academic_session')->where('session_id', $sessionid);
        $academic_session = $this->db->get()->getRow();

        $response->data = array();
        foreach ($results as $row) {
            $data = array();
            $allsubjectinfo = array();
            $data['id'] = $row->cid;
            
            $this->db->table('students')->where('student_id', $row->student_id);
            $studentsinfo = $this->db->get()->getRow();

            $this->db->table('student_class')->where('student_id', $row->student_id);
            $studentclass = $this->db->get()->getRow();

            $this->db->table('classes')->where('class_id', $studentclass->class_id);
            $classesinfo = $this->db->get()->getRow();

            $terms_session = $this->db->query("SELECT * FROM terms_session where session_id = " . $sessionid . " and '" . $row->date . "' between start_date and end_date")->getResult();
            if ($terms_session) {
                $this->db->table('terms')->where('term_id', $terms_session[0]->term_id);
                $termsinfo = $this->db->get()->getRow();
                $term_name = $termsinfo->name;
            } else {
                $term_name = '';
            }

            $data['student'] = $studentsinfo->first_name . " " . $studentsinfo->last_name;
            $data['class'] = $classesinfo->class_name;
            $data['session_name'] = $academic_session->session_name;
            $data['term_name'] = $term_name;
            $data['date'] = $row->date;
            $data['detail'] = $row->detail;
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }



public function check_and_load_attendance()
{
    $req        = $this->request;
    $cls_sec_id = (int) ($req->getPost('cls_sec_id') ?? $req->getPost('section_id') ?? 0); // <= fallback
    $class_id   = (int) $req->getPost('class_id');
    $campus_id  = (int) $req->getPost('campus_id');
    $datevalue  = trim($req->getPost('date') ?? '');

    if (!$datevalue || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datevalue)) {
        $datevalue = date('Y-m-d');
    }

    log_message('debug', 'ATTN input => cls_sec_id={a}, section_id_raw={b}, class_id={c}, campus_id={d}, date={e}', [
        'a' => $cls_sec_id,
        'b' => $req->getPost('section_id'),
        'c' => $class_id,
        'd' => $campus_id,
        'e' => $datevalue,
    ]);

    $hasRecords = false;
    if ($cls_sec_id > 0) {
        $hasRecords = $this->db->table('mark_attendance')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('date', $datevalue)
            ->countAllResults() > 0;
    } elseif ($class_id > 0) {
        $ids = $this->db->table('class_section')
            ->select('cls_sec_id')
            ->where('class_id', $class_id)
            ->where('campus_id', $campus_id)
            ->get()->getResultArray();
        $idList = array_column($ids, 'cls_sec_id');
        if ($idList) {
            $hasRecords = $this->db->table('mark_attendance')
                ->whereIn('cls_sec_id', $idList)
                ->where('date', $datevalue)
                ->countAllResults() > 0;
        }
    }

    $html = '';
    if ($hasRecords) {
        $resp = $this->get_students_byclass();
        $html = (is_object($resp) && method_exists($resp, 'getBody')) ? $resp->getBody() : (string) $resp;
    }

    return $this->response->setJSON([
        'has_records' => $hasRecords,
        'html'        => $html,
        'echo'        => [
            'cls_sec_id' => $cls_sec_id,
            'class_id'   => $class_id,
            'campus_id'  => $campus_id,
            'datevalue'  => $datevalue,
        ],
    ]);
}


public function mark_and_show_students()
{
    // Get input values
    $section_id = $this->request->getPost('section_id');
    $class_id = $this->request->getPost('class_id');
    $campus_id = $this->request->getPost('campus_id');
    $datevalue = $this->request->getPost('date');
    $user_id = session('member_userid');
    
    $current_date = date('Y-m-d H:i:s');

    // Get day name from date
    $timestamp = strtotime($datevalue);
    $day = date('l', $timestamp);
    $dayName = ucfirst(strtolower($day));

    // Fetch students
    if ($section_id > 0) {
        $sql = "SELECT * FROM student_class WHERE status = 1 AND cls_sec_id = ?";
        $classstudents = $this->db->query($sql, [$section_id])->getResult();
    } else {
        $sql = "SELECT * FROM student_class WHERE status = 1 AND cls_sec_id IN (
                    SELECT cls_sec_id FROM class_section WHERE class_id = ? AND campus_id = ?
                )";
        $classstudents = $this->db->query($sql, [$class_id, $campus_id])->getResult();
    }

    if (empty($classstudents)) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'No students found.']);
    }

    $inserted = 0;
    $updated = 0;

    // Track unique cls_sec_ids for inserting into mark_attendance
    $clsSecIds = [];

    foreach ($classstudents as $row) {
    // Track section ID
    $clsSecIds[$row->cls_sec_id] = true;

    // Get school timing for each student
    $timingQuery = $this->db->query("
        SELECT st.checkin_timing, st.checkout_timing 
        FROM school_timings st
        WHERE st.type_id = (
            SELECT type_id 
            FROM school_timing_types 
            WHERE campus_id = ? AND STATUS = 1
            LIMIT 1
        )
        AND st.cls_sec_id = ?
        AND st.DAYNAME = ?
    ", [$campus_id, $row->cls_sec_id, $dayName]);

    $timingResult = $timingQuery->getRow();

    $checkinTime = $timingResult ? $timingResult->checkin_timing : null;
    $checkoutTime = $timingResult ? $timingResult->checkout_timing : null;

    if (!$checkinTime || !$checkoutTime) {
        continue; // Skip student if timings not found
    }

    // Check if attendance record already exists
    $existing = $this->db->table('attendance')
        ->where('student_id', $row->student_id)
        ->where('date', $datevalue)
        ->get()
        ->getRow();

    if ($existing) {
        // Only update updated_date and user_id for existing records
        $this->db->table('attendance')
            ->where('student_id', $row->student_id)
            ->where('date', $datevalue)
            ->update([
                'updated_date' => $current_date,
                'user_id' => $user_id
            ]);
        $updated++;
    } else {
        // Insert new record with all data
        $data = [
            'student_id'   => $row->student_id,
            'date'         => $datevalue,
            'status'       => 'P',
            'checkin'      => $checkinTime,
            'checkout'     => $checkoutTime,
            'lc_duration'  => 0,
            'el_duration'  => 0,
            'user_id'      => $user_id,
            'created_date' => $current_date
        ];
        
        $this->db->table('attendance')->insert($data);

        if ($this->db->affectedRows() > 0) {
            $inserted++;
        }
    }
}

    // Insert or update mark_attendance per cls_sec_id
    foreach (array_keys($clsSecIds) as $cls_sec_id) {
          $existingMark = $this->db->table('mark_attendance')
        ->where('cls_sec_id',  (int)$cls_sec_id)
        ->where('date',        $datevalue)
        ->get()
        ->getRow();

        $markData = [
        'status'       => 'marked',        // <-- flip from pending to marked
        'user_id'      => $user_id,
        'updated_date' => $current_date,
    ];

         if ($existingMark) {
        // Update existing (pending) row to marked
        $this->db->table('mark_attendance')
            ->where('cls_sec_id', (int)$cls_sec_id)
            ->where('date',       $datevalue)
            ->update($markData);
        } 
    }
$studentListView = $this->get_students_byclass();
    
    if ($this->request->isAJAX()) {
        return $this->response->setJSON([
            'status' => 'success',
            'html' => $studentListView->getBody()
        ]);
    }
    
    return $studentListView;
}



public function get_students_byclass()
{
    $eid        = $this->request->getPost('eid');
    $session_id = (int) $this->request->getPost('session_id');
    $campus_id  = (int) $this->request->getPost('campus_id');
    $cls_sec_id = (int) ($this->request->getPost('cls_sec_id') ?? $this->request->getPost('section_id') ?? 0); // 0 = by class
    $class_id   = (int) $this->request->getPost('class_id');
    $datevalue  = trim($this->request->getPost('date') ?? '');

    if (!$datevalue || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datevalue)) {
        $datevalue = date('Y-m-d');
    }

    $timestamp = strtotime($datevalue);
    $day       = date('l', $timestamp);
    $today     = date('Y-m-d');
    $todayDay  = date('l');

    $classInfo = $this->db->table('class_section cs')
        ->select('c.class_name, s.section_name')
        ->join('classes c',  'c.class_id = cs.class_id',   'left')
        ->join('sections s', 's.section_id = cs.section_id','left')
        ->where('cs.cls_sec_id', $cls_sec_id)
        ->get()->getRow();

    if ($cls_sec_id > 0) {
        $classstudents = $this->db->query(
            "SELECT student_id FROM student_class WHERE status = 1 AND cls_sec_id = ?",
            [$cls_sec_id]
        )->getResultArray();
    } else {
        $classstudents = $this->db->query(
            "SELECT sc.student_id
               FROM student_class sc
              WHERE sc.status = 1
                AND sc.cls_sec_id IN (
                      SELECT cs.cls_sec_id
                        FROM class_section cs
                       WHERE cs.class_id = ?
                         AND cs.campus_id = ?
                    )",
            [$class_id, $campus_id]
        )->getResultArray();
    }

    if (empty($classstudents)) {
        $output  = '<div class="alert alert-info mb-0">No students found for the selected class/section.</div>';
        return $this->response->setBody($output);
    }

    $studentIds = array_map(static fn ($r) => (int)$r['student_id'], $classstudents);
    $studentIds = array_values(array_unique(array_filter($studentIds)));

    $attRows = [];
    if (!empty($studentIds)) {
        $attRows = $this->db->table('attendance')
            ->select('student_id, status, checkin, checkout')
            ->where('date', $datevalue)
            ->whereIn('student_id', $studentIds)
            ->get()->getResultArray();
    }

    if (empty($attRows)) {
        $output  = '<div class="card"><div class="card-body">';
        $output .= '<div class="d-flex justify-content-between align-items-center">';
        $output .= '<h5 class="mb-0">Attendance</h5>';
        $output .= '<span class="text-muted small">'.esc($datevalue).' ('.esc($day).')</span>';
        $output .= '</div><hr class="my-2">';
        $output .= '<div class="alert alert-warning mb-0">Load Student List for Attendance Marking on <em>'.esc($datevalue).'</em>.</div>';
        $output .= '</div></div>';
        return $this->response->setBody($output);
    }

    $attMap = [];
    $cnt = ['P' => 0, 'A' => 0, 'L' => 0, 'LC' => 0];

    foreach ($attRows as $a) {
        $sid = (int)$a['student_id'];
        $attMap[$sid] = $a;
        $s = strtoupper(trim($a['status'] ?? 'A'));
        if (!isset($cnt[$s])) { $s = 'A'; }
        $cnt[$s]++;
    }

    $stuRows = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, s.reg_no, s.parent_id, p.f_name')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->whereIn('s.student_id', $studentIds)
        ->get()->getResultArray();

    $stuMap = [];
    foreach ($stuRows as $s) {
        $stuMap[(int)$s['student_id']] = $s;
    }

    $output  = '<div id="attWrap" class="container-fluid p-0">';
    $output .= '<style>
      #attWrap .col-sno, #attWrap .col-photo { display: none; }
      #attWrap .view-options .form-check { margin-right: 10px; }
      /* Make P/A/L/LC control bigger & more tappable */
      #attWrap .att-choice.att-lg .btn { font-size: 1rem; padding: .5rem .8rem; border-width: 2px; }
      #attWrap .att-choice.att-lg .btn input { display:none; } /* cleaner look */
      #attWrap .att-selected .badge { font-size: .95rem; padding: .45rem .6rem; }
    </style>';

    $output .= '<div class="card mb-3">
  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Attendance</h5>
    <div class="btn-group btn-group-sm" role="group" aria-label="Bulk mark">
      <button type="button" class="btn btn-outline-light bulk-mark" data-status="P" title="Mark all Present">All P</button>
      <button type="button" class="btn btn-outline-light bulk-mark" data-status="A" title="Mark all Absent">All A</button>
      <button type="button" class="btn btn-outline-light bulk-mark" data-status="L" title="Mark all Leave">All L</button>
      <button type="button" class="btn btn-outline-light bulk-mark" data-status="LC" title="Mark all Late Coming">All LC</button>
    </div>
  </div>
  <div class="card-body">
    <div class="d-flex justify-content-end align-items-center flex-wrap view-options mb-2">
      <div class="form-check form-check-inline mb-1">
        <input class="form-check-input" type="checkbox" id="toggleSno">
        <label class="form-check-label" for="toggleSno">Show S.No</label>
      </div>
      <div class="form-check form-check-inline mb-1">
        <input class="form-check-input" type="checkbox" id="togglePhoto">
        <label class="form-check-label" for="togglePhoto">Show Photo</label>
      </div>
    </div>

    <div class="row mb-2">
      <div class="col-md-3"><strong>Class:</strong> '.($classInfo ? esc($classInfo->class_name.' - '.$classInfo->section_name) : 'N/A').'</div>
      <div class="col-md-3"><strong>Att. Date:</strong> '.esc($datevalue).' ('.esc($day).')</div>
      <div class="col-md-3"><strong>Today:</strong> '.esc($today).' ('.esc($todayDay).')</div>
      <div class="col-md-3"><strong>Total Records:</strong> '.count($attMap).'</div>
    </div>
    <div class="row">
      <div class="col-sm-3"><div class="alert alert-success p-2 mb-2"><strong>P:</strong> <span id="cntP">'.$cnt['P'].'</span></div></div>
      <div class="col-sm-3"><div class="alert alert-danger  p-2 mb-2"><strong>A:</strong> <span id="cntA">'.$cnt['A'].'</span></div></div>
      <div class="col-sm-3"><div class="alert alert-warning p-2 mb-2"><strong>L:</strong> <span id="cntL">'.$cnt['L'].'</span></div></div>
      <div class="col-sm-3"><div class="alert alert-info    p-2 mb-2"><strong>LC:</strong> <span id="cntLC">'.$cnt['LC'].'</span></div></div>
    </div>
  </div>
</div>';

    $output .= '<div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="attTable" class="table table-hover table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="col-sno"   width="5%">#</th>
                            <th class="col-photo" width="15%">Photo</th>
                            <th>Student</th>
                            <th width="20%">Status</th>
                        </tr>
                    </thead>
                    <tbody>';

    $sno = 1;
    foreach ($studentIds as $sid) {
        if (!isset($attMap[$sid])) continue;
        $stu = $stuMap[$sid] ?? null;
        if (!$stu) continue;

        $studentName = trim(($stu['first_name'] ?? '') . ' ' . ($stu['last_name'] ?? ''));

        $profile_photo = '';
        $imgPath = FCPATH . 'uploads/' . ($stu['profile_photo'] ?? '');
        if (!empty($stu['profile_photo']) && is_file($imgPath)) {
            $profile_photo = '<img class="img-thumbnail" style="width:60px;height:60px;border-radius:50%;" src="' . base_url('uploads/' . $stu['profile_photo']) . '" alt="Photo">';
        } else {
            $profile_photo = '<div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width:60px;height:60px;">'
                           .     '<i class="fas fa-user text-muted" style="font-size:24px;"></i>'
                           . '</div>';
        }

        $status = strtoupper(trim($attMap[$sid]['status'] ?? 'A'));
        if (!in_array($status, ['P','A','L','LC'], true)) $status = 'A';

        // status label + badge class
        $statusLabel = ($status==='P'?'Present':($status==='A'?'Absent':($status==='L'?'Leave':'Late Coming')));
        $badgeClass  = ($status==='P'?'badge-success':($status==='A'?'badge-danger':($status==='L'?'badge-warning':'badge-info')));

        $output .= '<tr>';
        $output .= '<td class="align-middle col-sno">' . ($sno++) . '</td>';
        $output .= '<td class="align-middle text-center col-photo">'.$profile_photo.'<input type="hidden" name="student_id[]" value="' . (int) $sid . '"></td>';
        $output .= '<td class="align-middle">' . esc($studentName) . '</td>';

        // Selected status badge (TOP) + larger controls
        $output .= '<td class="align-middle text-center">
          <div class="att-selected mb-1">
            <span class="badge badge-status '.$badgeClass.'">'.$statusLabel.'</span>
          </div>
          <div class="btn-group btn-group-toggle att-choice att-lg" data-bs-toggle="buttons" role="group" aria-label="Status">
            <label class="btn btn-outline-success '.($status==='P'?'active':'').' px-3 py-2">
              <input type="radio" name="status['.$sid.']" value="P"  data-sid="'.$sid.'" data-date="'.esc($datevalue).'" '.($status==='P'?'checked':'').'> P
            </label>
            <label class="btn btn-outline-danger  '.($status==='A'?'active':'').' px-3 py-2">
              <input type="radio" name="status['.$sid.']" value="A"  data-sid="'.$sid.'" data-date="'.esc($datevalue).'" '.($status==='A'?'checked':'').'> A
            </label>
            <label class="btn btn-outline-warning '.($status==='L'?'active':'').' px-3 py-2">
              <input type="radio" name="status['.$sid.']" value="L"  data-sid="'.$sid.'" data-date="'.esc($datevalue).'" '.($status==='L'?'checked':'').'> L
            </label>
            <label class="btn btn-outline-info    '.($status==='LC'?'active':'').' px-3 py-2">
              <input type="radio" name="status['.$sid.']" value="LC" data-sid="'.$sid.'" data-date="'.esc($datevalue).'" '.($status==='LC'?'checked':'').'> LC
            </label>
          </div>
        </td>';

        $output .= '</tr>';
    }

    $output .= '</tbody></table></div></div></div></div>';

    $output .= '<script>
(function(){
  function recalcSummary(){
    var p=0,a=0,l=0,lc=0;
    $("#attWrap div.att-choice input[type=radio]:checked").each(function(){
      var v = $(this).val();
      if(v==="P") p++; else if(v==="A") a++; else if(v==="L") l++; else if(v==="LC") lc++;
    });
    $("#cntP").text(p); $("#cntA").text(a); $("#cntL").text(l); $("#cntLC").text(lc);
  }

  function statusInfo(st){
    if(st==="P") return {text:"Present", cls:"badge-success"};
    if(st==="A") return {text:"Absent",  cls:"badge-danger"};
    if(st==="L") return {text:"Leave",   cls:"badge-warning"};
    return {text:"Late Coming", cls:"badge-info"};
  }

  function updateRowBadge($input, st){
    var info = statusInfo(st);
    var $row = $input.closest("tr");
    var $b   = $row.find(".badge-status");
    $b.text(info.text).removeClass("badge-success badge-danger badge-warning badge-info").addClass(info.cls);
  }

  function setRowStatus($input, status){
    var $group = $input.closest(".att-choice");
    $group.find(\'input[type=radio][value="\'+status+\'"]\').prop("checked", true).parent().addClass("active")
          .siblings().removeClass("active");
    updateRowBadge($input, status);
    recalcSummary();
  }

  function postUpdate(studentId, dateVal, status){
    return $.ajax({
      url: "'.base_url('admin/students_absentees/update_attendance_status').'",
      type: "POST",
      data: { student_id: studentId, attendanceDate: dateVal, status: status }
    });
  }

  // Per-row change
  $("#attWrap div.att-choice input[type=radio]").off("change").on("change", function(){
    var $inp = $(this);
    var sid  = $inp.data("sid");
    var d    = $inp.data("date");
    var st   = $inp.val();
    postUpdate(sid, d, st).done(function(){ updateRowBadge($inp, st); recalcSummary(); })
      .fail(function(){ alert("Error updating attendance"); recalcSummary(); });
  });

  // Bulk mark (All P/A/L/LC)
  $("#attWrap .bulk-mark").off("click").on("click", function(){
    var target = $(this).data("status");
    var $radios = $("#attWrap div.att-choice input[type=radio][value=\'"+target+"\']");
    var reqs = [];
    $radios.each(function(){
      var $inp = $(this);
      if(!$inp.is(":checked")){
        setRowStatus($inp, target);
        reqs.push(postUpdate($inp.data("sid"), $inp.data("date"), target));
      }
    });
    if(reqs.length){ $.when.apply($, reqs).always(recalcSummary); } else { recalcSummary(); }
  });

  // Column visibility toggles (persist in localStorage)
  function applyColumnVisibility() {
    var showSno   = localStorage.getItem("att_show_sno") === "1";
    var showPhoto = localStorage.getItem("att_show_photo") === "1";
    $("#toggleSno").prop("checked", showSno);
    $("#togglePhoto").prop("checked", showPhoto);
    $("#attWrap .col-sno").toggle(showSno);
    $("#attWrap .col-photo").toggle(showPhoto);
  }
  $("#toggleSno").off("change").on("change", function(){
    localStorage.setItem("att_show_sno", this.checked ? "1" : "0");
    $("#attWrap .col-sno").toggle(this.checked);
  });
  $("#togglePhoto").off("change").on("change", function(){
    localStorage.setItem("att_show_photo", this.checked ? "1" : "0");
    $("#attWrap .col-photo").toggle(this.checked);
  });

  applyColumnVisibility();
  recalcSummary();
})();
</script>';

    return $this->response->setBody($output);
}



/**
 * Search students by name (AJAX endpoint)
 * Returns matching students with their siblings
 */


/**
 * Update attendance for a single student (AJAX)
 */
public function update_attendance_status_single()
{
    $student_id     = (int) $this->request->getPost('student_id');
    $attendanceDate = trim($this->request->getPost('attendanceDate') ?? '');
    $statusRaw      = strtoupper(trim($this->request->getPost('status') ?? 'A'));
    
    $allowed = ['P', 'A', 'L', 'LC'];
    $status  = in_array($statusRaw, $allowed, true) ? $statusRaw : 'A';
    
    if (!$attendanceDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendanceDate)) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid date']);
    }
    
    // Update or insert attendance record
    $exists = $this->db->table('attendance')
        ->where('student_id', $student_id)
        ->where('date', $attendanceDate)
        ->countAllResults();
    
    if ($exists) {
        $this->db->table('attendance')
            ->where('student_id', $student_id)
            ->where('date', $attendanceDate)
            ->update(['status' => $status]);
    } else {
        $this->db->table('attendance')->insert([
            'student_id' => $student_id,
            'date'       => $attendanceDate,
            'status'     => $status
        ]);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'message' => 'Attendance updated successfully',
        'status' => $status,
        'status_label' => $this->getStatusLabel($status),
        'status_class' => $this->getStatusClass($status)
    ]);
}


/**
 * Search students by name (AJAX endpoint)
 * Returns matching students with their siblings
 */
public function search_students_by_name()
{
    $campus_id  = (int) $this->request->getPost('campus_id');
    $session_id = (int) $this->request->getPost('session_id');
    $datevalue  = trim($this->request->getPost('date') ?? '');
    $keyword    = trim($this->request->getPost('keyword') ?? '');
    
    if (!$datevalue || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datevalue)) {
        $datevalue = date('Y-m-d');
    }
    
    if (strlen($keyword) < 3) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Please enter at least 3 characters to search.'
        ]);
    }
    
    // Search active students by name (first_name or last_name)
    // Using direct query to avoid builder issues
    $sql = "SELECT s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.parent_id, s.class_id, s.status
            FROM students s
            INNER JOIN student_class sc ON sc.student_id = s.student_id
            WHERE s.campus_id = ? 
            AND s.status = '1'
            AND sc.session_id = ?
            AND (s.first_name LIKE ? OR s.last_name LIKE ?)
            GROUP BY s.student_id
            LIMIT 50";
    
    $like = '%' . $keyword . '%';
    $students = $this->db->query($sql, [$campus_id, $session_id, $like, $like])->getResultArray();
    
    if (empty($students)) {
        return $this->response->setJSON([
            'success' => true,
            'data' => [],
            'message' => 'No students found matching "' . esc($keyword) . '"'
        ]);
    }
    
    // Get unique parent_ids to fetch siblings
    $parent_ids = array_unique(array_column($students, 'parent_id'));
    $parent_ids = array_filter($parent_ids);
    
    $all_student_ids = [];
    $siblings_map = [];
    
    if (!empty($parent_ids)) {
        // Fetch all siblings for these parents
        $placeholders = implode(',', array_fill(0, count($parent_ids), '?'));
        $siblings_sql = "SELECT s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.parent_id, s.class_id, s.status
                         FROM students s
                         INNER JOIN student_class sc ON sc.student_id = s.student_id
                         WHERE s.campus_id = ? 
                         AND s.status = '1'
                         AND sc.session_id = ?
                         AND s.parent_id IN ($placeholders)
                         GROUP BY s.student_id";
        
        $params = array_merge([$campus_id, $session_id], $parent_ids);
        $all_siblings = $this->db->query($siblings_sql, $params)->getResultArray();
        
        foreach ($all_siblings as $sib) {
            $all_student_ids[] = (int)$sib['student_id'];
            $parent_id = (int)$sib['parent_id'];
            if (!isset($siblings_map[$parent_id])) {
                $siblings_map[$parent_id] = [];
            }
            $siblings_map[$parent_id][] = $sib;
        }
    } else {
        $all_student_ids = array_column($students, 'student_id');
    }
    
    // Fetch class names for each student using cls_sec_id
    $class_names = [];
    if (!empty($all_student_ids)) {
        $placeholders = implode(',', array_fill(0, count($all_student_ids), '?'));
        $class_sql = "SELECT sc.student_id, c.class_name
                      FROM student_class sc
                      LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
                      LEFT JOIN classes c ON c.class_id = cs.class_id
                      WHERE sc.student_id IN ($placeholders)
                      AND sc.session_id = ?";
        
        $params = array_merge($all_student_ids, [$session_id]);
        $class_data = $this->db->query($class_sql, $params)->getResultArray();
        
        foreach ($class_data as $cd) {
            $class_names[$cd['student_id']] = $cd['class_name'] ?? 'N/A';
        }
    }
    
    // Fetch existing attendance for these students on the given date
    $attendance_map = [];
    if (!empty($all_student_ids)) {
        $placeholders = implode(',', array_fill(0, count($all_student_ids), '?'));
        $att_sql = "SELECT student_id, status FROM attendance WHERE date = ? AND student_id IN ($placeholders)";
        $params = array_merge([$datevalue], $all_student_ids);
        $attendance = $this->db->query($att_sql, $params)->getResultArray();
        
        foreach ($attendance as $att) {
            $attendance_map[(int)$att['student_id']] = strtoupper($att['status']);
        }
    }
    
    // Build response data
    $response_data = [];
    $processed_parents = [];
    
    foreach ($students as $student) {
        $parent_id = (int)$student['parent_id'];
        
        // Skip if we've already processed this parent's siblings
        if (isset($processed_parents[$parent_id])) {
            continue;
        }
        $processed_parents[$parent_id] = true;
        
        $siblings = $siblings_map[$parent_id] ?? [$student];
        
        $sibling_list = [];
        foreach ($siblings as $sib) {
            $sid = (int)$sib['student_id'];
            $current_status = $attendance_map[$sid] ?? 'A';
            
            $sibling_list[] = [
                'student_id'    => $sid,
                'name'          => trim(($sib['first_name'] ?? '') . ' ' . ($sib['last_name'] ?? '')),
                'reg_no'        => $sib['reg_no'] ?? '',
                'class_name'    => $class_names[$sid] ?? 'N/A',
                'profile_photo' => $sib['profile_photo'] ?? '',
                'status'        => $current_status,
                'status_label'  => $this->getStatusLabel($current_status),
                'status_class'  => $this->getStatusClass($current_status)
            ];
        }
        
        // Get primary student name for the family
        $primary_student = $siblings[0] ?? $student;
        $primary_name = trim(($primary_student['first_name'] ?? '') . ' ' . ($primary_student['last_name'] ?? ''));
        
        $response_data[] = [
            'parent_id'     => $parent_id,
            'family_name'   => $primary_name,
            'siblings'      => $sibling_list,
            'sibling_count' => count($sibling_list)
        ];
    }
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $response_data,
        'total_families' => count($response_data),
        'total_students' => count($all_student_ids)
    ]);
}

/**
 * Helper: Get status label
 */
private function getStatusLabel($status)
{
    $map = ['P' => 'Present', 'A' => 'Absent', 'L' => 'Leave', 'LC' => 'Late Coming'];
    return $map[$status] ?? 'Absent';
}

/**
 * Helper: Get status CSS class
 */
private function getStatusClass($status)
{
    $map = ['P' => 'success', 'A' => 'danger', 'L' => 'warning', 'LC' => 'info'];
    return $map[$status] ?? 'danger';
}

public function update_attendance_status()
{
    $student_id     = $this->request->getPost('student_id');
    $attendanceDate = trim($this->request->getPost('attendanceDate') ?? '');
    $statusRaw      = strtoupper(trim($this->request->getPost('status') ?? 'A'));

    // ✅ Allow 4 statuses: P, A, L, LC (fallback to A if anything else)
    $allowed = ['P','A','L','LC'];
    $status  = in_array($statusRaw, $allowed, true) ? $statusRaw : 'A';

    // (light) date sanity
    if (!$attendanceDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendanceDate)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid date']);
    }

    // ✅ 1. Update attendance status (with a safe upsert fallback)
    $tbl = $this->db->table('attendance');
    $tbl->where('student_id', $student_id)
        ->where('date', $attendanceDate)
        ->set(['status' => $status])
        ->update();

    // If nothing was updated (row missing), insert it once
    if ($this->db->affectedRows() === 0) {
        $exists = $this->db->table('attendance')
            ->where('student_id', $student_id)
            ->where('date', $attendanceDate)
            ->countAllResults();
        if ($exists == 0) {
            $this->db->table('attendance')->insert([
                'student_id' => $student_id,
                'date'       => $attendanceDate,
                'status'     => $status,
            ]);
        }
    }

    // ✅ 2. Fetch student info (unchanged)
    $studentInfo = $this->db->table('students')
        ->select('first_name, last_name, parent_id, class_id')
        ->where('student_id', $student_id)
        ->get()
        ->getRow();

    if (!$studentInfo) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Student not found']);
    }

    // ✅ 3. Fetch parent info (unchanged fields)
    $parentInfo = $this->db->table('parents')
        ->select('f_name, father_contact')
        ->where('parent_id', $studentInfo->parent_id)
        ->get()
        ->getRow();

    // ✅ 4. Fetch class name (unchanged)
    $StudentClass = $this->db->table('classes')
        ->select('class_name')
        ->where('class_id', $studentInfo->class_id)
        ->get()
        ->getRow('class_name');

    // ✅ 5. Fetch SMS template for attendance (unchanged)
    $info = $this->db->table('sms_settings')
        ->where('campus_id', session()->get('campus_id'))
        ->get()
        ->getRow();

    $template = $info->attendance_sms ?? '';

    // ✅ Map status code -> human label for {status} placeholder
    $statusLabelMap = [
        'P'  => 'Present',
        'A'  => 'Absent',
        'L'  => 'Leave',
        'LC' => 'Late Coming',
    ];
    $statusLabel = $statusLabelMap[$status] ?? 'Absent';

    // ✅ 6. Replace placeholders (unchanged style, just improved {status})
    $replacements = [
        '{first_name}'  => $studentInfo->first_name,
        '{last_name}'   => $studentInfo->last_name,
        '{date}'        => $attendanceDate,
        '{father_name}' => $parentInfo->f_name ?? '',
        '{class}'       => $StudentClass ?? '',
        '{status}'      => $statusLabel,             // ← now supports P/A/L/LC
        // (optional) add {status_code} if your template ever needs it
        '{status_code}' => $status,
    ];

    $parsedMessage = strtr($template, $replacements);

    // ✅ 7. Send SMS only if parent has contact
    // (Your parent field is father_contact; your old check used ->contact. Keep logic, fix field.)
    $smsTo = $parentInfo->father_contact ?? null; // primary
    if (!$smsTo && isset($parentInfo->contact)) { // backward-compat if you also store "contact"
        $smsTo = $parentInfo->contact;
    }

    return $this->response->setJSON([
        'success' => true,
        'msg'     => 'Attendance updated successfully',
        'status'  => $status,
        'label'   => $statusLabel,
    ]);
}



public function toggle_attendance_status()
{
    $student_id = $this->request->getPost('student_id');
    $attendanceDate = $this->request->getPost('attendanceDate');
    $user_id = $this->session->get('member_userid');
    $currentDate = date('Y-m-d');
    $timestamp = date('Y-m-d H:i:s');

   

    // Validate inputs
    if (empty($student_id) || empty($attendanceDate)) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Student ID or Date is missing.'
        ]);
    }

    // Run the query
    $builder = $this->db->table('attendance');
    $builder->where('student_id', $student_id);
    $builder->where('date', $attendanceDate);
    $attendance = $builder->get()->getRow();

   

    if (!$attendance) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Attendance record not found.'
        ]);
    }

    
    $recordCreatedDate = date('Y-m-d', strtotime($attendance->created_date));
    

    if ($recordCreatedDate !== $currentDate) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Attendance can only be changed on the day it was created.'
        ]);
    }

    // Toggle attendance
    $newStatus = ($attendance->status === 'P') ? 'A' : 'P';

    // Update DB
    $this->db->table('attendance')
        ->where('student_id', $student_id)
        ->where('date', $attendanceDate)
        ->update([
            'status' => $newStatus,
            'user_id' => $user_id,
            'updated_date' => $timestamp
        ]);

  
    return $this->response->setJSON([
        'status' => 'success',
        'message' => "Attendance updated to $newStatus",
        'new_status' => $newStatus
    ]);
}


/**
 * Face Recognition View
 */

/**
 * Register student face (upload and store face encoding)
 */
public function register_face()
{
    $student_id = (int) $this->request->getPost('student_id');
    $campus_id  = (int) $this->request->getPost('campus_id');
    $face_data  = $this->request->getPost('face_data'); // Base64 image data
    
    if (!$student_id || !$face_data) {
        return $this->response->setJSON(['success' => false, 'message' => 'Missing required data']);
    }
    
    // Decode base64 image
    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $face_data));
    
    if (!$image_data) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid image data']);
    }
    
    // Save image to server
    $upload_dir = WRITEPATH . 'uploads/face_registrations/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = 'face_' . $student_id . '_' . time() . '.jpg';
    $filepath = $upload_dir . $filename;
    file_put_contents($filepath, $image_data);
    
    // Check if face already registered
    $existing = $this->db->table('face_registrations')
        ->where('student_id', $student_id)
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();
    
    if ($existing) {
        // Update existing registration
        $this->db->table('face_registrations')
            ->where('registration_id', $existing->registration_id)
            ->update([
                'face_image_path' => 'uploads/face_registrations/' . $filename,
                'updated_date' => date('Y-m-d H:i:s')
            ]);
    } else {
        // Insert new registration
        $this->db->table('face_registrations')->insert([
            'student_id' => $student_id,
            'campus_id' => $campus_id,
            'face_image_path' => 'uploads/face_registrations/' . $filename,
            'created_date' => date('Y-m-d H:i:s'),
            'status' => 1
        ]);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'message' => 'Face registered successfully',
        'filename' => $filename
    ]);
}

/**
 * Recognize face and mark late attendance
 */
public function recognize_face()
{
    $face_data = $this->request->getPost('face_data');
    $date      = $this->request->getPost('date') ?? date('Y-m-d');
    $campus_id = (int) $this->request->getPost('campus_id');
    $session_id = (int) $this->request->getPost('session_id');
    $current_time = date('H:i:s');
    
    if (!$face_data) {
        return $this->response->setJSON(['success' => false, 'message' => 'No face data received']);
    }
    
    // Decode base64 image
    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $face_data));
    
    if (!$image_data) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid image data']);
    }
    
    // Save temp image for processing
    $temp_dir = WRITEPATH . 'temp/';
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    $temp_filename = 'temp_face_' . time() . '.jpg';
    $temp_filepath = $temp_dir . $temp_filename;
    file_put_contents($temp_filepath, $image_data);
    
    // Get all registered faces for this campus
    $registered_faces = $this->db->table('face_registrations fr')
        ->select('fr.student_id, fr.face_image_path, s.first_name, s.last_name, s.reg_no')
        ->join('students s', 's.student_id = fr.student_id')
        ->where('fr.campus_id', $campus_id)
        ->where('fr.status', 1)
        ->where('s.status', '1')
        ->get()
        ->getResultArray();
    
    if (empty($registered_faces)) {
        unlink($temp_filepath);
        return $this->response->setJSON([
            'success' => false, 
            'message' => 'No registered faces found. Please register faces first.'
        ]);
    }
    
    // Simple face matching using image comparison
    // For production, use a proper face recognition library like:
    // - FaceNet, OpenFace, or cloud services (AWS Rekognition, Azure Face API)
    
    $best_match = null;
    $best_similarity = 0;
    
    foreach ($registered_faces as $face) {
        $registered_image_path = FCPATH . $face['face_image_path'];
        if (file_exists($registered_image_path)) {
            $similarity = $this->compare_faces($temp_filepath, $registered_image_path);
            if ($similarity > $best_similarity && $similarity > 0.6) { // 60% similarity threshold
                $best_similarity = $similarity;
                $best_match = $face;
            }
        }
    }
    
    // Clean up temp file
    if (file_exists($temp_filepath)) {
        unlink($temp_filepath);
    }
    
    if (!$best_match) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Face not recognized. Please ensure you are registered or try again.'
        ]);
    }
    
    // Check if student is active and in current session
    $student_check = $this->db->table('student_class sc')
        ->select('s.*')
        ->join('students s', 's.student_id = sc.student_id')
        ->where('sc.student_id', $best_match['student_id'])
        ->where('sc.session_id', $session_id)
        ->where('sc.status', 1)
        ->get()
        ->getRow();
    
    if (!$student_check) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Student is not enrolled in the current session.'
        ]);
    }
    
    // Get late threshold from settings
    $late_threshold = $this->db->table('attendance_settings')
        ->select('checkin, grace_period_minutes')
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();
    
    $threshold_time = $late_threshold->checkin ?? '08:15:00';
    $grace_minutes = $late_threshold->grace_period_minutes ?? 5;
    
    // Calculate if late
    $threshold_with_grace = date('H:i:s', strtotime($threshold_time) + ($grace_minutes * 60));
    $is_late = ($current_time > $threshold_with_grace);
    
    // Determine status
    $status = $is_late ? 'LC' : 'P';
    
    // Check if attendance already exists for today
    $existing_attendance = $this->db->table('attendance')
        ->where('student_id', $best_match['student_id'])
        ->where('date', $date)
        ->get()
        ->getRow();
    
    if ($existing_attendance) {
        // Update existing attendance
        $this->db->table('attendance')
            ->where('attendance_id', $existing_attendance->attendance_id)
            ->update([
                'status' => $status,
                'checkin' => $current_time,
                'lc_duration' => $is_late ? $this->calculate_late_minutes($current_time, $threshold_time) : 0,
                'updated_date' => date('Y-m-d H:i:s'),
                'updated_reason' => 'Face recognition update'
            ]);
    } else {
        // Insert new attendance
        $this->db->table('attendance')->insert([
            'student_id' => $best_match['student_id'],
            'date' => $date,
            'checkin' => $current_time,
            'status' => $status,
            'lc_duration' => $is_late ? $this->calculate_late_minutes($current_time, $threshold_time) : 0,
            'created_date' => date('Y-m-d H:i:s'),
            'user_id' => session('member_userid')
        ]);
    }
    
    // Get student class info for response
    $class_info = $this->db->table('student_class sc')
        ->select('c.class_name')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->where('sc.student_id', $best_match['student_id'])
        ->where('sc.session_id', $session_id)
        ->get()
        ->getRow();
    
    return $this->response->setJSON([
        'success' => true,
        'message' => $is_late ? 'Late Coming marked successfully' : 'Present marked successfully',
        'student' => [
            'id' => $best_match['student_id'],
            'name' => $best_match['first_name'] . ' ' . $best_match['last_name'],
            'reg_no' => $best_match['reg_no'],
            'class' => $class_info->class_name ?? 'N/A'
        ],
        'status' => $status,
        'status_label' => $status == 'LC' ? 'Late Coming' : 'Present',
        'time' => $current_time,
        'is_late' => $is_late
    ]);
}

/**
 * Save late attendance manually
 */
public function save_late_attendance()
{
    $student_id = (int) $this->request->getPost('student_id');
    $date = $this->request->getPost('date') ?? date('Y-m-d');
    $late_minutes = (int) $this->request->getPost('late_minutes');
    $remarks = $this->request->getPost('remarks');
    
    if (!$student_id) {
        return $this->response->setJSON(['success' => false, 'message' => 'Student ID required']);
    }
    
    $current_time = date('H:i:s');
    
    $existing = $this->db->table('attendance')
        ->where('student_id', $student_id)
        ->where('date', $date)
        ->get()
        ->getRow();
    
    if ($existing) {
        $this->db->table('attendance')
            ->where('attendance_id', $existing->attendance_id)
            ->update([
                'status' => 'LC',
                'lc_duration' => $late_minutes,
                'remarks' => $remarks,
                'updated_date' => date('Y-m-d H:i:s')
            ]);
    } else {
        $this->db->table('attendance')->insert([
            'student_id' => $student_id,
            'date' => $date,
            'status' => 'LC',
            'lc_duration' => $late_minutes,
            'remarks' => $remarks,
            'created_date' => date('Y-m-d H:i:s')
        ]);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'message' => 'Late attendance saved successfully'
    ]);
}

/**
 * Get registered faces list for dropdown
 */
public function get_registered_faces()
{
    $campus_id = (int) $this->request->getGet('campus_id');
    $session_id = (int) $this->request->getGet('session_id');
    
    $faces = $this->db->table('face_registrations fr')
        ->select('fr.student_id, fr.face_image_path, s.first_name, s.last_name, s.reg_no')
        ->join('students s', 's.student_id = fr.student_id')
        ->join('student_class sc', 'sc.student_id = s.student_id')
        ->where('fr.campus_id', $campus_id)
        ->where('sc.session_id', $session_id)
        ->where('fr.status', 1)
        ->where('s.status', '1')
        ->get()
        ->getResultArray();
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $faces
    ]);
}

/**
 * Compare two face images (simplified using perceptual hash)
 * For production, use a proper face recognition library
 */
private function compare_faces($image1_path, $image2_path)
{
    // This is a simplified comparison using image hash
    // Replace with actual face recognition logic
    
    try {
        // Get image dimensions and basic hash
        $img1 = @imagecreatefromjpeg($image1_path);
        $img2 = @imagecreatefromjpeg($image2_path);
        
        if (!$img1 || !$img2) {
            return 0;
        }
        
        // Resize to 8x8 for simple comparison
        $img1_resized = imagecreatetruecolor(8, 8);
        $img2_resized = imagecreatetruecolor(8, 8);
        
        imagecopyresampled($img1_resized, $img1, 0, 0, 0, 0, 8, 8, imagesx($img1), imagesy($img1));
        imagecopyresampled($img2_resized, $img2, 0, 0, 0, 0, 8, 8, imagesx($img2), imagesy($img2));
        
        // Calculate hash
        $hash1 = $this->get_image_hash($img1_resized);
        $hash2 = $this->get_image_hash($img2_resized);
        
        imagedestroy($img1);
        imagedestroy($img2);
        imagedestroy($img1_resized);
        imagedestroy($img2_resized);
        
        // Calculate Hamming distance
        $distance = 0;
        for ($i = 0; $i < strlen($hash1); $i++) {
            if ($hash1[$i] !== $hash2[$i]) {
                $distance++;
            }
        }
        
        // Convert distance to similarity (0-1)
        $similarity = 1 - ($distance / strlen($hash1));
        
        return $similarity;
        
    } catch (Exception $e) {
        log_message('error', 'Face comparison error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get perceptual hash of image
 */
private function get_image_hash($image)
{
    $hash = '';
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Calculate average brightness
    $total = 0;
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $brightness = ($r + $g + $b) / 3;
            $total += $brightness;
        }
    }
    
    $avg = $total / ($width * $height);
    
    // Create hash
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $brightness = ($r + $g + $b) / 3;
            $hash .= ($brightness >= $avg) ? '1' : '0';
        }
    }
    
    return $hash;
}

/**
 * Calculate late minutes
 */
private function calculate_late_minutes($checkin_time, $threshold_time)
{
    $checkin_ts = strtotime($checkin_time);
    $threshold_ts = strtotime($threshold_time);
    
    if ($checkin_ts <= $threshold_ts) {
        return 0;
    }
    
    return round(($checkin_ts - $threshold_ts) / 60);
}

/**
 * Get active students for dropdown (AJAX)
 */
public function get_students_for_dropdown()
{
    $campus_id = (int) $this->request->getGet('campus_id');
    $session_id = (int) $this->request->getGet('session_id');
    
    $students = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no')
        ->join('student_class sc', 'sc.student_id = s.student_id')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', '1')
        ->where('sc.session_id', $session_id)
        ->where('sc.status', 1)
        ->groupBy('s.student_id')
        ->orderBy('s.first_name', 'ASC')
        ->get()
        ->getResultArray();
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $students
    ]);
}


/**
 * Get face descriptors for all registered students
 */
public function get_face_descriptors()
{
    $campus_id = (int) $this->request->getGet('campus_id');
    $session_id = (int) $this->request->getGet('session_id');
    
    $faces = $this->db->table('face_registrations fr')
        ->select('fr.student_id, fr.face_descriptor, s.first_name, s.last_name, s.reg_no')
        ->join('students s', 's.student_id = fr.student_id')
        ->join('student_class sc', 'sc.student_id = s.student_id')
        ->where('fr.campus_id', $campus_id)
        ->where('sc.session_id', $session_id)
        ->where('fr.status', 1)
        ->where('s.status', '1')
        ->groupBy('fr.student_id')
        ->get()
        ->getResultArray();
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $faces
    ]);
}

/**
 * Register face with descriptor (for Face-API.js)
 */
public function register_face_descriptor()
{
    $student_id = (int) $this->request->getPost('student_id');
    $campus_id = (int) $this->request->getPost('campus_id');
    $face_descriptor = $this->request->getPost('face_descriptor');
    $face_image = $this->request->getPost('face_image');
    
    if (!$student_id || !$face_descriptor) {
        return $this->response->setJSON(['success' => false, 'message' => 'Missing required data']);
    }
    
    // Save face image
    $image_path = '';
    if ($face_image) {
        $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $face_image));
        $upload_dir = WRITEPATH . 'uploads/face_registrations/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = 'face_' . $student_id . '_' . time() . '.jpg';
        $filepath = $upload_dir . $filename;
        file_put_contents($filepath, $image_data);
        $image_path = 'uploads/face_registrations/' . $filename;
    }
    
    // Check if already registered
    $existing = $this->db->table('face_registrations')
        ->where('student_id', $student_id)
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();
    
    if ($existing) {
        $this->db->table('face_registrations')
            ->where('registration_id', $existing->registration_id)
            ->update([
                'face_descriptor' => $face_descriptor,
                'face_image_path' => $image_path,
                'updated_date' => date('Y-m-d H:i:s')
            ]);
    } else {
        $this->db->table('face_registrations')->insert([
            'student_id' => $student_id,
            'campus_id' => $campus_id,
            'face_descriptor' => $face_descriptor,
            'face_image_path' => $image_path,
            'created_date' => date('Y-m-d H:i:s'),
            'status' => 1
        ]);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'message' => 'Face registered successfully'
    ]);
}

/**
 * Save face recognition attendance
 */
public function save_face_attendance()
{
    $student_id = (int) $this->request->getPost('student_id');
    $date = $this->request->getPost('date') ?? date('Y-m-d');
    $campus_id = (int) $this->request->getPost('campus_id');
    $session_id = (int) $this->request->getPost('session_id');
    $face_image = $this->request->getPost('face_image');
    
    $current_time = date('H:i:s');
    
    // Get late threshold
    $late_threshold = $this->db->table('attendance_settings')
        ->select('checkin, grace_period_minutes')
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();
    
    $threshold_time = $late_threshold->checkin ?? '08:15:00';
    $grace_minutes = $late_threshold->grace_period_minutes ?? 5;
    $threshold_with_grace = date('H:i:s', strtotime($threshold_time) + ($grace_minutes * 60));
    $is_late = ($current_time > $threshold_with_grace);
    $status = $is_late ? 'LC' : 'P';
    
    // Check existing attendance
    $existing = $this->db->table('attendance')
        ->where('student_id', $student_id)
        ->where('date', $date)
        ->get()
        ->getRow();
    
    if ($existing) {
        $this->db->table('attendance')
            ->where('attendance_id', $existing->attendance_id)
            ->update([
                'status' => $status,
                'checkin' => $current_time,
                'lc_duration' => $is_late ? $this->calculate_late_minutes($current_time, $threshold_time) : 0,
                'updated_date' => date('Y-m-d H:i:s'),
                'updated_reason' => 'Face recognition'
            ]);
    } else {
        $this->db->table('attendance')->insert([
            'student_id' => $student_id,
            'date' => $date,
            'checkin' => $current_time,
            'status' => $status,
            'lc_duration' => $is_late ? $this->calculate_late_minutes($current_time, $threshold_time) : 0,
            'created_date' => date('Y-m-d H:i:s'),
            'user_id' => session('member_userid')
        ]);
    }
    
    // Get student info
    $student = $this->db->table('students')
        ->select('first_name, last_name, reg_no')
        ->where('student_id', $student_id)
        ->get()
        ->getRow();
    
    return $this->response->setJSON([
        'success' => true,
        'student' => [
            'name' => $student->first_name . ' ' . $student->last_name,
            'reg_no' => $student->reg_no
        ],
        'time' => $current_time,
        'is_late' => $is_late,
        'status_label' => $is_late ? 'Late Coming' : 'Present'
    ]);
}

}


