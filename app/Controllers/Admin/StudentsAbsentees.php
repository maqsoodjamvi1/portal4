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
    public function index()
    {
        return view('admin/students_absentees', $this->template_data);
    }

    /**
     * Section rows with is_off, timings, and has_attendance for a calendar date (same logic as add() view).
     */
    protected function buildSectionsClassInfoForDate(string $date, int $campusid): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $allSections = getAllClassSection();
        if ($campusid < 1) {
            return [];
        }

        $timestamp = strtotime($date);
        $dayName = date('l', $timestamp);

        $activeTimingType = $this->db->table('school_timing_types')
            ->select('type_id')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->orderBy('type_id', 'ASC')
            ->get()
            ->getRow();

        $activeTypeId = $activeTimingType ? $activeTimingType->type_id : null;

        $sectionsclassinfo = [];
        foreach ($allSections as $section) {
            $timingQuery = $this->db->table('school_timings')
                ->select('checkin_timing, checkout_timing')
                ->where('cls_sec_id', $section['cls_sec_id'])
                ->where('dayname', $dayName);

            if ($activeTypeId) {
                $timingQuery = $timingQuery->where('type_id', $activeTypeId);
            }

            $timingResult = $timingQuery->get();

            $isOff = false;
            $checkin = null;
            $checkout = null;

            $timing = ($timingResult && $timingResult->getRow()) ? $timingResult->getRow() : null;

            if ($timing) {
                $checkin = $timing->checkin_timing;
                $checkout = $timing->checkout_timing;
                $isOff = ($checkin === $checkout || ($checkin === null && $checkout === null));
            } else {
                $isOff = true;
            }

            $attendanceResult = $this->db->table('attendance')
                ->select('COUNT(DISTINCT attendance.student_id) AS count', false)
                ->join('student_class sc', 'sc.student_id = attendance.student_id')
                ->where('sc.cls_sec_id', $section['cls_sec_id'])
                ->where('attendance.date', $date)
                ->where('sc.status', 1)
                ->get();

            $hasAttendance = false;
            if ($attendanceResult && $attendanceResult->getRow()) {
                $row = $attendanceResult->getRow();
                $hasAttendance = (int) ($row->count ?? 0) > 0;
            }

            $sectionsclassinfo[] = [
                'cls_sec_id'        => $section['cls_sec_id'],
                'section_id'        => $section['cls_sec_id'],
                'sectionclassname'  => $section['sectionclassname'],
                'is_off'            => $isOff,
                'checkin'           => $checkin,
                'checkout'          => $checkout,
                'has_attendance'    => $hasAttendance,
            ];
        }

        return $sectionsclassinfo;
    }

    public function sections_for_date()
    {
        check_permission('admin-add-student-absentees');
        $date = trim((string) $this->request->getPost('date'));
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        $campusid = (int) ($this->request->getPost('campus_id') ?? session('member_campusid'));
        if ($campusid < 1) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid campus']);
        }

        return $this->response->setJSON([
            'success'  => true,
            'sections' => $this->buildSectionsClassInfoForDate($date, $campusid),
        ]);
    }

       public function add()
    {
        check_permission('admin-add-student-absentees');

        $schoolinfo = getSchoolInfo();
        $campusid   = (int) session('member_campusid');
        $sessionid  = (int) session('member_sessionid');

        // Allow ?date=YYYY-MM-DD (default: today)
        $date = $this->request->getGet('date');
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $this->template_data['sessionData'] = [
            'campusid'  => $campusid,
            'sessionid' => $sessionid,
            'date'      => $date,
        ];

        $this->template_data['infostudents'] = $this->db->table('students')->get()->getResult();

        // Get all class sections using helper
        $allSections = getAllClassSection();
        
        // Get classes list using helper data
        $classesMap = [];
        $classesInfo = [];
        foreach ($allSections as $section) {
            if (!isset($classesMap[$section['class_id']])) {
                $classesMap[$section['class_id']] = true;
                $classesInfo[] = (object)[
                    'class_id' => $section['class_id'],
                    'class_name' => $section['class_name']
                ];
            }
        }
        $this->template_data['classesinfo'] = $classesInfo;

        $this->template_data['sectionsclassinfo'] = $this->buildSectionsClassInfoForDate($date, $campusid);

        $this->template_data['campusinfo'] = $this->db->table('campus')
            ->where('campus_id', $campusid)
            ->get()
            ->getResult();

        $this->template_data['examinfo'] = $this->db->table('exam')
            ->where('campus_id', $campusid)
            ->where('session_id', $sessionid)
            ->get()
            ->getResult();

        $this->template_data['academic_session'] = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()
            ->getResult();

        $this->template_data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

        return view('admin/students_absentees_edit', $this->template_data);
    }

    public function check_and_load_attendance()
    {
        $req        = $this->request;
        $cls_sec_id = (int) ($req->getPost('cls_sec_id') ?? $req->getPost('section_id') ?? 0);
        $class_id   = (int) $req->getPost('class_id');
        $campus_id  = (int) $req->getPost('campus_id');
        $datevalue  = trim($req->getPost('date') ?? '');

        if (!$datevalue || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datevalue)) {
            $datevalue = date('Y-m-d');
        }

        $timestamp = strtotime($datevalue);
        $dayName = date('l', $timestamp);

        // Check if the day is ON for this class/section
        $isDayOn = $this->checkIfDayIsOn($cls_sec_id, $class_id, $campus_id, $dayName);
        
        if (!$isDayOn) {
            return $this->response->setJSON([
                'has_records' => false,
                'is_off' => true,
                'html' => '<div class="alert alert-warning"><i class="fas fa-calendar-times"></i> This day is OFF. No attendance can be marked.</div>',
                'echo' => [
                    'cls_sec_id' => $cls_sec_id,
                    'class_id'   => $class_id,
                    'campus_id'  => $campus_id,
                    'datevalue'  => $datevalue,
                ],
            ]);
        }

        // Check if attendance records already exist
        $hasRecords = $this->checkAttendanceExists($cls_sec_id, $class_id, $datevalue);

        $html = '';
        if ($hasRecords) {
            $resp = $this->get_students_byclass();
            $html = (is_object($resp) && method_exists($resp, 'getBody')) ? $resp->getBody() : (string) $resp;
        } else {
            $html = $this->getLoadAttendanceButtonHtml($cls_sec_id, $class_id, $datevalue);
        }

        return $this->response->setJSON([
            'has_records' => $hasRecords,
            'is_off' => false,
            'html' => $html,
            'echo' => [
                'cls_sec_id' => $cls_sec_id,
                'class_id'   => $class_id,
                'campus_id'  => $campus_id,
                'datevalue'  => $datevalue,
            ],
        ]);
    }
public function load_attendance_records()
{
    $section_id = $this->request->getPost('section_id');
    $class_id = $this->request->getPost('class_id');
    $campus_id = (int) $this->request->getPost('campus_id');
    $datevalue = trim($this->request->getPost('date') ?? '');
    $user_id = session('member_userid');
    
    if (!$datevalue || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datevalue)) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid date']);
    }
    
    $timestamp = strtotime($datevalue);
    $dayName = date('l', $timestamp);
    $current_date = date('Y-m-d H:i:s');
    
    // Get active timing type for this campus
    $activeTimingType = $this->db->table('school_timing_types')
        ->select('type_id')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRow();
    
    $activeTypeId = $activeTimingType ? $activeTimingType->type_id : null;
    
    // Get students based on selection
    if ($section_id > 0) {
        $students = $this->db->table('student_class sc')
            ->select('sc.student_id, sc.cls_sec_id')
            ->where('sc.status', 1)
            ->where('sc.cls_sec_id', $section_id)
            ->get()
            ->getResult();
    } else {
        $students = $this->db->table('student_class sc')
            ->select('sc.student_id, sc.cls_sec_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where('sc.status', 1)
            ->where('cs.class_id', $class_id)
            ->where('cs.campus_id', $campus_id)
            ->get()
            ->getResult();
    }
    
    if (empty($students)) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'No students found for this class/section']);
    }
    
    $inserted = 0;
    $existing = 0;
    $skippedLC = 0;
    
    foreach ($students as $student) {
        // Get school timing for this student's section using active type_id
        $timingQuery = $this->db->table('school_timings')
            ->select('checkin_timing, checkout_timing')
            ->where('cls_sec_id', $student->cls_sec_id)
            ->where('dayname', $dayName);
        
        if ($activeTypeId) {
            $timingQuery = $timingQuery->where('type_id', $activeTypeId);
        }
        
        $timingResult = $timingQuery->get();
        $timing = ($timingResult && $timingResult->getRow()) ? $timingResult->getRow() : null;
        
        if (!$timing || $timing->checkin_timing === null || $timing->checkout_timing === null) {
            continue; // Skip if no valid timing found
        }
        
        // Check if attendance already exists
        $existingRecord = $this->db->table('attendance')
            ->select('status')
            ->where('student_id', $student->student_id)
            ->where('date', $datevalue)
            ->get()
            ->getRow();
        
        if ($existingRecord) {
            $existing++;
            
            // Skip if status is LC (Late Coming) - preserve LC status
            if ($existingRecord->status === 'LC') {
                $skippedLC++;
            }
            continue; // Skip insertion for any existing record
        }
        
        // Insert new record only if no record exists
        $data = [
            'student_id'   => $student->student_id,
            'date'         => $datevalue,
            'status'       => 'P', // Default to Present
            'checkin'      => $timing->checkin_timing,
            'checkout'     => $timing->checkout_timing,
            'lc_duration'  => 0,
            'el_duration'  => 0,
            'user_id'      => $user_id,
            'created_date' => $current_date
        ];
        
        $this->db->table('attendance')->insert($data);
        $inserted++;
    }
    
    $studentListView = $this->get_students_byclass();
    
    $message = "$inserted new records inserted, $existing already existed";
    if ($skippedLC > 0) {
        $message .= " ($skippedLC LC records preserved)";
    }
    
    return $this->response->setJSON([
        'status' => 'success',
        'message' => $message,
        'html' => $studentListView->getBody()
    ]);
}

    public function get_students_byclass()
    {
        $eid        = $this->request->getPost('eid');
        $session_id = (int) $this->request->getPost('session_id');
        $campus_id  = (int) $this->request->getPost('campus_id');
        $cls_sec_id = (int) ($this->request->getPost('cls_sec_id') ?? $this->request->getPost('section_id') ?? 0);
        $class_id   = (int) $this->request->getPost('class_id');
        $datevalue  = trim($this->request->getPost('date') ?? '');

        if (!$datevalue || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datevalue)) {
            $datevalue = date('Y-m-d');
        }

        $timestamp = strtotime($datevalue);
        $day       = date('l', $timestamp);
        $today     = date('Y-m-d');
        $todayDay  = date('l');

        // Get class/section info using helper
        $classInfo = null;
        if ($cls_sec_id > 0) {
            $sectionData = getClassSection($cls_sec_id);
            if (!empty($sectionData)) {
                $classInfo = (object)[
                    'class_name' => $sectionData['class_name'],
                    'section_name' => $sectionData['section_name']
                ];
            }
        }

        // Get students based on selection
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
            $output = '<div class="alert alert-info mb-0">No students found for the selected class/section.</div>';
            return $this->response->setBody($output);
        }

        $studentIds = array_map(static fn($r) => (int)$r['student_id'], $classstudents);
        $studentIds = array_values(array_unique(array_filter($studentIds)));

        $attRows = [];
        if (!empty($studentIds)) {
            $attRows = $this->db->table('attendance')
                ->select('student_id, status, checkin, checkout')
                ->where('date', $datevalue)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->getResultArray();
        }

        if (empty($attRows)) {
            $output = '<div class="card"><div class="card-body">';
            $output .= '<div class="d-flex justify-content-between align-items-center">';
            $output .= '<h5 class="mb-0">Attendance</h5>';
            $output .= '<span class="text-muted small">' . esc($datevalue) . ' (' . esc($day) . ')</span>';
            $output .= '</div><hr class="my-2">';
            $output .= '<div class="alert alert-warning mb-0">No attendance records found. Click "Load Attendance" to initialize.</div>';
            $output .= '</div></div>';
            return $this->response->setBody($output);
        }

        $attMap = [];
        $cnt = ['P' => 0, 'A' => 0, 'L' => 0, 'LC' => 0];

        foreach ($attRows as $a) {
            $sid = (int)$a['student_id'];
            $attMap[$sid] = $a;
            $s = strtoupper(trim($a['status'] ?? 'A'));
            if (!isset($cnt[$s])) {
                $s = 'A';
            }
            $cnt[$s]++;
        }

        $stuRows = $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, s.reg_no, s.parent_id, p.f_name')
            ->join('parents p', 'p.parent_id = s.parent_id', 'left')
            ->whereIn('s.student_id', $studentIds)
            ->get()
            ->getResultArray();

        $stuMap = [];
        foreach ($stuRows as $s) {
            $stuMap[(int)$s['student_id']] = $s;
        }

        $output = '<div id="attWrap" class="container-fluid p-0">';
        $output .= '<style>
          #attWrap .col-sno, #attWrap .col-photo { display: none; }
          #attWrap .view-options .form-check { margin-right: 10px; }
          #attWrap .att-choice.att-lg .btn { font-size: 1rem; padding: .5rem .8rem; border-width: 2px; }
          #attWrap .att-choice.att-lg .btn input { display:none; }
          #attWrap .att-selected .badge { font-size: .95rem; padding: .45rem .6rem; }
        </style>';

        $output .= '<div class="card mb-3">
         
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
              <div class="col-md-3"><strong>Class:</strong> ' . ($classInfo ? esc($classInfo->class_name . ' - ' . $classInfo->section_name) : 'N/A') . '</div>
              <div class="col-md-3"><strong>Att. Date:</strong> ' . esc($datevalue) . ' (' . esc($day) . ')</div>
              <div class="col-md-3"><strong>Today:</strong> ' . esc($today) . ' (' . esc($todayDay) . ')</div>
              <div class="col-md-3"><strong>Total Records:</strong> ' . count($attMap) . '</div>
            </div>
            <div class="row">
              <div class="col-sm-3"><div class="alert alert-success p-2 mb-2"><strong>P:</strong> <span id="cntP">' . $cnt['P'] . '</span></div></div>
              <div class="col-sm-3"><div class="alert alert-danger p-2 mb-2"><strong>A:</strong> <span id="cntA">' . $cnt['A'] . '</span></div></div>
              <div class="col-sm-3"><div class="alert alert-warning p-2 mb-2"><strong>L:</strong> <span id="cntL">' . $cnt['L'] . '</span></div></div>
              <div class="col-sm-3"><div class="alert alert-info p-2 mb-2"><strong>LC:</strong> <span id="cntLC">' . $cnt['LC'] . '</span></div></div>
            </div>
          </div>
        </div>';

        $output .= '<div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="attTable" class="table table-hover table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="col-sno" width="5%">#</th>
                                <th class="col-photo" width="15%">Photo</th>
                                <th>Student</th>
                                <th width="20%">Status</th>
                            </tr>
                        </thead>
                        <tbody>';

        $sno = 1;
        foreach ($studentIds as $sid) {
            if (!isset($attMap[$sid])) {
                continue;
            }
            $stu = $stuMap[$sid] ?? null;
            if (!$stu) {
                continue;
            }

            $studentName = trim(($stu['first_name'] ?? '') . ' ' . ($stu['last_name'] ?? ''));

            $profile_photo = '';
            $imgPath = FCPATH . 'uploads/' . ($stu['profile_photo'] ?? '');
            if (!empty($stu['profile_photo']) && is_file($imgPath)) {
                $profile_photo = '<img class="img-thumbnail" style="width:60px;height:60px;border-radius:50%;" src="' . base_url('uploads/' . $stu['profile_photo']) . '" alt="Photo">';
            } else {
                $profile_photo = '<div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width:60px;height:60px;">'
                               . '<i class="fas fa-user text-muted" style="font-size:24px;"></i>'
                               . '</div>';
            }

            $status = strtoupper(trim($attMap[$sid]['status'] ?? 'A'));
            if (!in_array($status, ['P', 'A', 'L', 'LC'], true)) {
                $status = 'A';
            }

            $statusLabel = ($status === 'P' ? 'Present' : ($status === 'A' ? 'Absent' : ($status === 'L' ? 'Leave' : 'Late Coming')));
            $badgeClass = ($status === 'P' ? 'badge-success' : ($status === 'A' ? 'badge-danger' : ($status === 'L' ? 'badge-warning' : 'badge-info')));

            $output .= '<tr>';
            $output .= '<td class="align-middle col-sno">' . ($sno++) . '</td>';
            $output .= '<td class="align-middle text-center col-photo">' . $profile_photo . '<input type="hidden" name="student_id[]" value="' . (int)$sid . '"></td>';
            $output .= '<td class="align-middle">' . esc($studentName) . '</td>';
            $output .= '<td class="align-middle text-center">
              <div class="att-selected mb-1">
                <span class="badge badge-status ' . $badgeClass . '">' . $statusLabel . '</span>
              </div>
              <div class="btn-group btn-group-toggle att-choice att-lg" data-toggle="buttons" role="group">
                <label class="btn btn-outline-success ' . ($status === 'P' ? 'active' : '') . ' px-3 py-2">
                  <input type="radio" name="status[' . $sid . ']" value="P" data-sid="' . $sid . '" data-date="' . esc($datevalue) . '" ' . ($status === 'P' ? 'checked' : '') . '> P
                </label>
                <label class="btn btn-outline-danger ' . ($status === 'A' ? 'active' : '') . ' px-3 py-2">
                  <input type="radio" name="status[' . $sid . ']" value="A" data-sid="' . $sid . '" data-date="' . esc($datevalue) . '" ' . ($status === 'A' ? 'checked' : '') . '> A
                </label>
                <label class="btn btn-outline-warning ' . ($status === 'L' ? 'active' : '') . ' px-3 py-2">
                  <input type="radio" name="status[' . $sid . ']" value="L" data-sid="' . $sid . '" data-date="' . esc($datevalue) . '" ' . ($status === 'L' ? 'checked' : '') . '> L
                </label>
                <label class="btn btn-outline-info ' . ($status === 'LC' ? 'active' : '') . ' px-3 py-2">
                  <input type="radio" name="status[' . $sid . ']" value="LC" data-sid="' . $sid . '" data-date="' . esc($datevalue) . '" ' . ($status === 'LC' ? 'checked' : '') . '> LC
                </label>
              </div>
            </td>';
            $output .= '</tr>';
        }

        $output .= '</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>';

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
      url: "' . base_url('admin/students_absentees/update_attendance_status') . '",
      type: "POST",
      data: { student_id: studentId, attendanceDate: dateVal, status: status }
    });
  }

  $("#attWrap div.att-choice input[type=radio]").off("change").on("change", function(){
    var $inp = $(this);
    var sid  = $inp.data("sid");
    var d    = $inp.data("date");
    var st   = $inp.val();
    postUpdate(sid, d, st).done(function(){ updateRowBadge($inp, st); recalcSummary(); })
      .fail(function(){ alert("Error updating attendance"); recalcSummary(); });
  });

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

  
    public function toggle_attendance_status()
    {
        $student_id = $this->request->getPost('student_id');
        $attendanceDate = $this->request->getPost('attendanceDate');
        $user_id = $this->session->get('member_userid');
        $currentDate = date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');

        if (empty($student_id) || empty($attendanceDate)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Student ID or Date is missing.'
            ]);
        }

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

        $newStatus = ($attendance->status === 'P') ? 'A' : 'P';

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

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    /**
     * Check if the day is ON (has valid school timing where checkin != checkout)
     */
    /**
 * Check if the day is ON (has valid school timing where checkin != checkout)
 */
private function checkIfDayIsOn($cls_sec_id, $class_id, $campus_id, $dayName)
{
    $db = \Config\Database::connect();
    
    // Get active timing type for this campus
    $activeTimingType = $db->table('school_timing_types')
        ->select('type_id')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRow();
    
    $activeTypeId = $activeTimingType ? $activeTimingType->type_id : null;
    
    if ($cls_sec_id > 0) {
        $timingQuery = $db->table('school_timings')
            ->select('checkin_timing, checkout_timing')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('dayname', $dayName);
        
        if ($activeTypeId) {
            $timingQuery = $timingQuery->where('type_id', $activeTypeId);
        }
        
        $timingResult = $timingQuery->get();
        $timing = ($timingResult && $timingResult->getRow()) ? $timingResult->getRow() : null;
        
        if (!$timing) {
            return false;
        }
        
        // Day is ON only if checkin and checkout are both set and different
        return ($timing->checkin_timing !== null && 
                $timing->checkout_timing !== null && 
                $timing->checkin_timing !== $timing->checkout_timing);
        
    } elseif ($class_id > 0) {
        $sections = $db->table('class_section')
            ->select('cls_sec_id')
            ->where('class_id', $class_id)
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->get()
            ->getResultArray();
        
        foreach ($sections as $section) {
            $timingQuery = $db->table('school_timings')
                ->select('checkin_timing, checkout_timing')
                ->where('cls_sec_id', $section['cls_sec_id'])
                ->where('dayname', $dayName);
            
            if ($activeTypeId) {
                $timingQuery = $timingQuery->where('type_id', $activeTypeId);
            }
            
            $timingResult = $timingQuery->get();
            $timing = ($timingResult && $timingResult->getRow()) ? $timingResult->getRow() : null;
            
            if ($timing && 
                $timing->checkin_timing !== null && 
                $timing->checkout_timing !== null && 
                $timing->checkin_timing !== $timing->checkout_timing) {
                return true;
            }
        }
        return false;
    }
    
    return false;
}

    /**
     * Check if attendance records exist
     */
  /**
 * Check if attendance records with status 'P' (Present) exist
 * This determines whether the "Load Attendance" button should be shown
 */
private function checkAttendanceExists($cls_sec_id, $class_id, $datevalue)
{
    if ($cls_sec_id > 0) {
        // Check if any student in this section has status 'P' for this date
        $result = $this->db->table('attendance a')
            ->select('COUNT(*) as count')
            ->join('student_class sc', 'sc.student_id = a.student_id')
            ->where('sc.cls_sec_id', $cls_sec_id)
            ->where('sc.status', 1)
            ->where('a.date', $datevalue)
            ->where('a.status', 'P')
            ->get()
            ->getRow();
        
        return ($result && $result->count > 0);
        
    } elseif ($class_id > 0) {
        $ids = $this->db->table('class_section')
            ->select('cls_sec_id')
            ->where('class_id', $class_id)
            ->where('campus_id', session('member_campusid'))
            ->where('status', 1)
            ->get()
            ->getResultArray();
        $idList = array_column($ids, 'cls_sec_id');
        
        if (empty($idList)) {
            return false;
        }
        
        // Check if any student in any section of this class has status 'P' for this date
        $result = $this->db->table('attendance a')
            ->select('COUNT(*) as count')
            ->join('student_class sc', 'sc.student_id = a.student_id')
            ->whereIn('sc.cls_sec_id', $idList)
            ->where('sc.status', 1)
            ->where('a.date', $datevalue)
            ->where('a.status', 'P')
            ->get()
            ->getRow();
        
        return ($result && $result->count > 0);
    }
    
    return false;
}

    /**
     * Generate HTML for the "Load Attendance" button
     */
  private function getLoadAttendanceButtonHtml($cls_sec_id, $class_id, $datevalue)
{
    $campus_id = session('member_campusid');
    $timestamp = strtotime($datevalue);
    $dayName = date('l', $timestamp);
    
    // Get active timing type for this campus - use $this->db
    $activeTimingType = $this->db->table('school_timing_types')
        ->select('type_id')
        ->where('campus_id', $campus_id)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRow();
    
    $activeTypeId = $activeTimingType ? $activeTimingType->type_id : null;
    
    // Get student count
    if ($cls_sec_id > 0) {
        $studentCount = $this->db->table('student_class')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('status', 1)
            ->countAllResults();
        
        // Get timing info
        $timingQuery = $this->db->table('school_timings')
            ->select('checkin_timing, checkout_timing')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('dayname', $dayName);
        
        if ($activeTypeId) {
            $timingQuery = $timingQuery->where('type_id', $activeTypeId);
        }
        
        $timingResult = $timingQuery->get();
        $timing = ($timingResult && $timingResult->getRow()) ? $timingResult->getRow() : null;
    } else {
        $studentCount = $this->db->table('student_class sc')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where('cs.class_id', $class_id)
            ->where('sc.status', 1)
            ->countAllResults();
        
        $timing = null;
    }
    
    $html = '<div class="card">
        <div class="card-body text-center py-4">
            <div class="mb-3">
                <i class="fas fa-calendar-check fa-3x text-primary mb-2"></i>
                <h5>No attendance records found for ' . esc($datevalue) . '</h5>
                <p class="text-muted">Day: ' . esc($dayName) . '</p>';
    
    if ($timing && $timing->checkin_timing && $timing->checkout_timing) {
        $html .= '<p class="text-muted">School Timing: ' . esc($timing->checkin_timing) . ' - ' . esc($timing->checkout_timing) . '</p>';
    }
    
    $html .= '<p class="text-muted">Total students: ' . $studentCount . '</p>
                <button type="button" onclick="loadAttendanceData();" class="btn btn-primary btn-lg">
                    <i class="fas fa-download"></i> Load Attendance
                </button>
                <p class="text-muted mt-3 small">
                    <i class="fas fa-info-circle"></i> 
                    This will mark all students as <strong>Present</strong> with the default school timings.
                    You can edit individual statuses after loading.
                </p>
            </div>
        </div>
    </div>
    <script>
    function loadAttendanceData() {
        $("#loader-1").show();
        var campus_id = $("#campus_id").val();
        var section_id = $("#section_id").val();
        var class_id = $("#class_id").val();
        var date = $("#date").val();
        
        $.ajax({
            url: "' . base_url('admin/students_absentees/load_attendance_records') . '",
            type: "POST",
            data: {
                section_id: section_id,
                class_id: class_id,
                campus_id: campus_id,
                date: date
            },
            success: function(response) {
                if (response.status === "success") {
                    $("#students_list_container").html(response.html);
                } else {
                    $("#students_list_container").html(\'<div class="alert alert-danger">\' + response.message + \'</div>\');
                }
                $("#loader-1").hide();
            },
            error: function() {
                $("#students_list_container").html(\'<div class="alert alert-danger">Error loading attendance data.</div>\');
                $("#loader-1").hide();
            }
        });
    }
    </script>';
    
    return $html;
}
    /**
     * Search students by name - for the search tab
     */
public function search_students_by_name()
{
    $keyword = trim($this->request->getPost('keyword') ?? '');
    $date = trim($this->request->getPost('date') ?? date('Y-m-d'));
    $campus_id = (int) $this->request->getPost('campus_id');
    $session_id = (int) $this->request->getPost('session_id');
    $limit = (int) $this->request->getPost('limit') ?: 20;

    // Allow 2 characters for better mobile experience
    if (strlen($keyword) < 2) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Please enter at least 2 characters to search.'
        ]);
    }

    // Use FULLTEXT search for better performance (add FULLTEXT index to students table)
    // Alternative: Use LIKE with optimization
    $students = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.parent_id, s.class_id, s.std_type, p.f_name as father_name, p.father_contact')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->where('s.campus_id', $campus_id)
        ->where('s.status', 1)
        ->groupStart()
            ->like('s.first_name', $keyword, 'after') // 'after' is faster than 'both'
            ->orLike('s.last_name', $keyword, 'after')
            ->orLike('CONCAT(s.first_name, " ", s.last_name)', $keyword, 'after')
        ->groupEnd()
        ->limit($limit)
        ->get()
        ->getResultArray();

    if (empty($students)) {
        return $this->response->setJSON([
            'success' => true,
            'data' => [],
            'has_more' => false
        ]);
    }

    // Check if there might be more results
    $hasMore = count($students) >= $limit;

    // Get parent IDs
    $parentIds = array_unique(array_column($students, 'parent_id'));
    
    // Single optimized query for all siblings with JOINs
    $allSiblings = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.parent_id, s.class_id, s.std_type, p.f_name as father_name, c.class_name')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->join('classes c', 'c.class_id = s.class_id', 'left')
        ->whereIn('s.parent_id', $parentIds)
        ->where('s.campus_id', $campus_id)
        ->where('s.status', 1)
        ->orderBy('s.class_id', 'ASC')
        ->orderBy('s.first_name', 'ASC')
        ->get()
        ->getResultArray();

    // Get attendance in single query
    $studentIds = array_column($allSiblings, 'student_id');
    $attendanceData = [];
    if (!empty($studentIds)) {
        $attendance = $this->db->table('attendance')
            ->select('student_id, status, checkin, checkout')
            ->where('date', $date)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->getResultArray();
        foreach ($attendance as $att) {
            $attendanceData[$att['student_id']] = $att;
        }
    }

    // Group by parent_id
    $families = [];
    foreach ($allSiblings as $student) {
        $parentId = $student['parent_id'];
        if (!isset($families[$parentId])) {
            $families[$parentId] = [
                'parent_id' => $parentId,
                'family_name' => $student['father_name'] ?? 'Family',
                'sibling_count' => 0,
                'siblings' => []
            ];
        }
        
        $attRecord = $attendanceData[$student['student_id']] ?? null;
        $status = $attRecord['status'] ?? null;
        $statusInfo = $this->getStatusInfo($status);
        
        $families[$parentId]['siblings'][] = [
            'student_id' => $student['student_id'],
            'name' => trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')),
            'reg_no' => $student['reg_no'] ?? '',
            'profile_photo' => $student['profile_photo'] ? base_url($student['profile_photo']) : base_url('assets/img/default-avatar.png'),
            'class_name' => $student['class_name'] ?? 'N/A',
            'status' => $status,
            'status_label' => $statusInfo['label'],
            'status_class' => $statusInfo['class'],
            'std_type' => $student['std_type'] ?? 1,
            'checkin_time' => $attRecord['checkin'] ?? null,
            'checkout_time' => $attRecord['checkout'] ?? null
        ];
        $families[$parentId]['sibling_count']++;
    }

    return $this->response->setJSON([
        'success' => true,
        'data' => array_values($families),
        'has_more' => $hasMore,
        'total_families' => count($families),
        'total_students' => count($allSiblings)
    ]);
}

private function getStatusInfo($status)
{
    $status = strtolower(trim($status ?? ''));
    
    $statusMap = [
        'present' => ['label' => 'Present', 'class' => 'present'],
        'p' => ['label' => 'Present', 'class' => 'present'],
        'absent' => ['label' => 'Absent', 'class' => 'absent'],
        'a' => ['label' => 'Absent', 'class' => 'absent'],
        'late' => ['label' => 'Late', 'class' => 'late'],
        'l' => ['label' => 'Late', 'class' => 'late'],
        'leave' => ['label' => 'Leave', 'class' => 'leave'],
        'el' => ['label' => 'Early Leave', 'class' => 'early-leave'],
        'lc' => ['label' => 'Late Coming', 'class' => 'late-coming']
    ];
    
    return $statusMap[$status] ?? ['label' => 'Not Marked', 'class' => 'not-marked'];
}


public function update_attendance_status()
{
    $student_id     = $this->request->getPost('student_id');
    $attendanceDate = trim($this->request->getPost('attendanceDate') ?? '');
    $statusRaw      = strtoupper(trim($this->request->getPost('status') ?? 'P'));

    // Allowed statuses: P (Present), A (Absent), L (Leave), LC (Late Coming)
    $allowed = ['P', 'A', 'L', 'LC'];
    $status  = in_array($statusRaw, $allowed, true) ? $statusRaw : 'P';

    if (!$attendanceDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendanceDate)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid date']);
    }

    // Get student's section timing for LC records
    $timing = null;
    $studentClass = null;
    
    if ($status === 'LC') {
        // Get student's current class section
        $studentClass = $this->db->table('student_class sc')
            ->select('sc.cls_sec_id')
            ->where('sc.student_id', $student_id)
            ->where('sc.status', 1)
            ->orderBy('sc.sc_id', 'DESC')
            ->get()
            ->getRow();
        
        if ($studentClass) {
            $timestamp = strtotime($attendanceDate);
            $dayName = date('l', $timestamp);
            
            // Get active timing type
            $campus_id = session('member_campusid');
            $activeTimingType = $this->db->table('school_timing_types')
                ->select('type_id')
                ->where('campus_id', $campus_id)
                ->where('status', 1)
                ->orderBy('type_id', 'ASC')
                ->get()
                ->getRow();
            
            $activeTypeId = $activeTimingType ? $activeTimingType->type_id : null;
            
            $timingQuery = $this->db->table('school_timings')
                ->select('checkin_timing, checkout_timing')
                ->where('cls_sec_id', $studentClass->cls_sec_id)
                ->where('dayname', $dayName);
            
            if ($activeTypeId) {
                $timingQuery = $timingQuery->where('type_id', $activeTypeId);
            }
            
            $timingResult = $timingQuery->get();
            $timing = ($timingResult && $timingResult->getRow()) ? $timingResult->getRow() : null;
        }
    }

    $current_date = date('Y-m-d H:i:s');
    $user_id = session('member_userid');

    // Check if attendance record exists
    $existingRecord = $this->db->table('attendance')
        ->where('student_id', $student_id)
        ->where('date', $attendanceDate)
        ->get()
        ->getRow();

    if ($existingRecord) {
        // Update existing record
        $updateData = [
            'status' => $status,
            'user_id' => $user_id,
            'updated_date' => $current_date
        ];
        
        // If marking as LC and we have timing, update checkin/checkout
        if ($status === 'LC' && $timing) {
            $updateData['checkin'] = $timing->checkin_timing;
            $updateData['checkout'] = $timing->checkout_timing;
            $updateData['lc_duration'] = 5; // Default 5 minutes late, can be customized
        }
        
        // If marking as Absent, clear checkin/checkout
        if ($status === 'A') {
            $updateData['checkin'] = null;
            $updateData['checkout'] = null;
            $updateData['lc_duration'] = null;
        }
        
        // If marking as Present, set default checkin/checkout
        if ($status === 'P') {
            $updateData['checkin'] = date('H:i:s');
            $updateData['checkout'] = date('H:i:s', strtotime('+6 hours'));
        }
        
        $this->db->table('attendance')
            ->where('student_id', $student_id)
            ->where('date', $attendanceDate)
            ->update($updateData);
    } else {
        // Insert new record
        $data = [
            'student_id'   => $student_id,
            'date'         => $attendanceDate,
            'status'       => $status,
            'user_id'      => $user_id,
            'created_date' => $current_date
        ];
        
        // If marking as LC and we have timing, add checkin/checkout
        if ($status === 'LC' && $timing) {
            $data['checkin'] = $timing->checkin_timing;
            $data['checkout'] = $timing->checkout_timing;
            $data['lc_duration'] = 5;
        }
        
        // If marking as Present, set default checkin/checkout
        if ($status === 'P') {
            $data['checkin'] = date('H:i:s');
            $data['checkout'] = date('H:i:s', strtotime('+6 hours'));
        }
        
        $this->db->table('attendance')->insert($data);
    }

    $statusLabelMap = [
        'P'  => 'Present',
        'A'  => 'Absent',
        'L'  => 'Leave',
        'LC' => 'Late Coming'
    ];
    $statusLabel = $statusLabelMap[$status] ?? 'Present';

    return $this->response->setJSON([
        'success' => true,
        'msg'     => 'Attendance updated successfully',
        'status'  => $status,
        'label'   => $statusLabel,
    ]);
}


/**
 * Generate a secure share token for a student
 */

/**
 * Get status label for display
 */
private function getStatusLabel($status)
{
    $map = [
        'P' => 'Present',
        'A' => 'Absent', 
        'L' => 'Leave',
        'LC' => 'Late Coming'
    ];
    return $map[$status] ?? 'Absent';
}

/**
 * Get status CSS class for styling
 */
private function getStatusClass($status)
{
    $map = [
        'P' => 'success',
        'A' => 'danger',
        'L' => 'warning',
        'LC' => 'info'
    ];
    return $map[$status] ?? 'danger';
}

/**
 * Update attendance for a single student (AJAX)
 */
// public function update_attendance_status_single()
// {
//     $student_id     = (int) $this->request->getPost('student_id');
//     $attendanceDate = trim($this->request->getPost('attendanceDate') ?? '');
//     $statusRaw      = strtoupper(trim($this->request->getPost('status') ?? 'A'));
    
//     $allowed = ['P', 'A', 'L', 'LC'];
//     $status  = in_array($statusRaw, $allowed, true) ? $statusRaw : 'A';
    
//     if (!$attendanceDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendanceDate)) {
//         return $this->response->setJSON(['success' => false, 'message' => 'Invalid date']);
//     }
    
//     // Update or insert attendance record
//     $exists = $this->db->table('attendance')
//         ->where('student_id', $student_id)
//         ->where('date', $attendanceDate)
//         ->countAllResults();
    
//     if ($exists) {
//         $this->db->table('attendance')
//             ->where('student_id', $student_id)
//             ->where('date', $attendanceDate)
//             ->update(['status' => $status]);
//     } else {
//         $this->db->table('attendance')->insert([
//             'student_id' => $student_id,
//             'date'       => $attendanceDate,
//             'status'     => $status
//         ]);
//     }
    
//     return $this->response->setJSON([
//         'success' => true,
//         'message' => 'Attendance updated successfully',
//         'status' => $status,
//         'status_label' => $this->getStatusLabel($status),
//         'status_class' => $this->getStatusClass($status)
//     ]);
// }

/**
 * Helper: Get status label
 */


}