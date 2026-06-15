<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;




class StudentsSubjectResults extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        check_permission('admin-students-subject-results');
        return view('admin/students_subject_results', []);
    }

public function saveMark()
{
    $this->requireResultEntryAccess();
    $r = $this->request;

    $student_id   = (int) $r->getPost('student_id');
    $cls_sec_id   = (int) $r->getPost('cls_sec_id');
    $sec_sub_id   = (int) $r->getPost('sec_sub_id');
    $session_id   = (int) $r->getPost('session_id');
    $campus_id    = (int) $r->getPost('campus_id');
    $obtained_raw = $r->getPost('obtained_marks');

    if (!$student_id || !$cls_sec_id || !$sec_sub_id) {
        return $this->response->setJSON(['success'=>false,'message'=>'Missing required fields.']);
    }

    if (!$this->canCurrentUserEnterResult($cls_sec_id, $sec_sub_id)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'You can only enter results for subjects assigned to you.'
        ]);
    }

    $eid = (int) ($this->getActiveExamEid($campus_id, $session_id) ?? 0);
    if (!$eid) { $eid = (int) ($this->getActiveExamEid($campus_id, null) ?? 0); }
    if (!$eid)  { return $this->response->setJSON(['success'=>false,'message'=>'No active exam found.']); }

    // normalize & clamp
    $obtained_marks = ($obtained_raw === '' || $obtained_raw === null) ? null : (float) $obtained_raw;
    $tmRow = $this->db->table('datesheet')->select('total_marks')
        ->where(['eid'=>$eid,'cls_sec_id'=>$cls_sec_id,'sec_sub_id'=>$sec_sub_id])
        ->get(1)->getRowArray();
    if ($tmRow !== null && isset($tmRow['total_marks']) && $obtained_marks !== null) {
        $tm = (float) $tmRow['total_marks'];
        $obtained_marks = max(0.0, min($tm, $obtained_marks));
    }

    // EXISTENCE CHECK (don’t rely on affectedRows())
    $exists = $this->db->table('subject_results')
        ->select('result_id')
        ->where([
            'eid'        => $eid,
            'student_id' => $student_id,
            'cls_sec_id' => $cls_sec_id,
            'sec_sub_id' => $sec_sub_id,
        ])->get(1)->getRow();

    $this->db->transStart();
    $action = 'update';
    $result_id = 0;

    if ($exists) {
        $result_id = (int) $exists->result_id;
        $this->db->table('subject_results')->where('result_id', $result_id)->update([
            'obtained_marks' => $obtained_marks,
            'session_id'     => $session_id ?: null,
        ]);
    } else {
        $this->db->table('subject_results')->insert([
            'student_id'     => $student_id,
            'eid'            => $eid,
            'session_id'     => $session_id ?: null,
            'cls_sec_id'     => $cls_sec_id,
            'sec_sub_id'     => $sec_sub_id,
            'obtained_marks' => $obtained_marks,
        ]);
        $result_id = (int) $this->db->insertID();
        $action    = 'insert';
    }

    $this->db->transComplete();
    if (!$this->db->transStatus()) {
        return $this->response->setJSON(['success'=>false,'message'=>'Could not save. Please try again.']);
    }

    return $this->response->setJSON([
        'success'   => true,
        'message'   => $action === 'insert' ? 'Mark added successfully.' : 'Mark updated successfully.',
        'action'    => $action,
        'result_id' => $result_id,
        // Optional: return fresh CSRF if you use CSRF regenerate
        // 'csrf' => ['name'=>csrf_token(),'hash'=>csrf_hash()],
    ]);
}


public function add()
{
    $this->requireResultEntryAccess();
    $campusid = $this->session->get('member_campusid');
    $sessionid = $this->session->get('member_sessionid');
    $sessionData = [
        'campusid' => $campusid,
        'sessionid' => $sessionid
    ];

    $data['sessionData'] = $sessionData;
    $data['infostudents'] = $this->db->table('students')->get()->getResult();

    $data['sectionsclassinfo'] = $this->shouldScopeToTeacherSubjects()
        ? $this->getCurrentTeacherSubjectSections()
        : userClassSections();

    $data['examinfo'] = $this->db->table('exam')
        ->where('campus_id', $campusid)
        ->where('session_id', $sessionid)
        ->get()->getResult();

    $data['academic_session'] = $this->db->table('academic_session')
        ->where('session_id', $sessionid)
        ->get()->getResult();

    $data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

    return view('admin/students_subject_results_edit', $data);
}
public function selectSectionSubjectbySection()
{
    $section_id = (int) $this->request->getPost('cls_sec_id');  // cls_sec_id
    $campus_id  = (int) $this->request->getPost('campus_id');
    $session_id = (int) $this->request->getPost('session_id');

    $userid     = (int) $this->session->get('member_userid');
    $isTeacher  = $this->shouldScopeToTeacherSubjects();

    $schoolinfo = getSchoolInfo();
    $system_id  = (int) ($schoolinfo->system_id ?? 0);

    // Resolve ACTIVE exam id (status=0) for this campus/session (unchanged logic)
    $eid = (int) ($this->getActiveExamEid($campus_id, $session_id) ?? 0);
    if (!$eid) $eid = (int) ($this->getActiveExamEid($campus_id, null) ?? 0);

    // Fast-fail & default option
    if (!$section_id || !$eid) {
        return $this->response->setJSON([
            'html' => '<option value="">Select Subject</option>',
            'meta' => ['eid' => $eid, 'cls' => $section_id]
        ]);
    }

    if ($isTeacher && !$this->teacherHasSubjectInSection($userid, $section_id)) {
        return $this->response->setJSON([
            'html' => '<option value="">No assigned subjects</option>',
            'meta' => ['eid' => $eid, 'cls' => $section_id]
        ]);
    }

    // -------- micro cache (60s): avoids repeated identical queries while user flips UI --------
    $cacheKey = "secsub_opts:v2:e{$eid}:c{$campus_id}:s{$session_id}:cl{$section_id}:u{$userid}:t".(int)$isTeacher;
    if (function_exists('apcu_fetch')) {
        $cached = apcu_fetch($cacheKey);
        if ($cached !== false) {
            return $this->response->setJSON(['html' => $cached]);
        }
    }

    // -------- SQL: use EXISTS so the optimizer can stop early on first match --------
    // section_subjects has (cls_sec_id,status) index; datesheet has (eid,cls_sec_id,sec_sub_id) unique key,
    // so these predicates are sargable and fast.  
    $params = [
        'eid'        => $eid,
        'cls_sec_id' => $section_id,
        'system_id'  => $system_id,
        'tid'        => $userid,
    ];

    // Base WHERE for all users
    $where = "
        ss.cls_sec_id = :cls_sec_id:
        AND ss.status = 1
        AND EXISTS (
            SELECT 1
              FROM datesheet d
             WHERE d.eid        = :eid:
               AND d.cls_sec_id = ss.cls_sec_id
               AND d.sec_sub_id = ss.sec_sub_id
               AND COALESCE(d.total_marks,0) <> 0
        )
    ";

    // Teacher extra filter via EXISTS (avoids growing the result set with an inner join)
    if ($isTeacher) {
        $where .= "
        AND EXISTS (
            SELECT 1
              FROM teacher_subjects ts
             WHERE ts.sec_sub_id = ss.sec_sub_id
               AND ts.cls_sec_id = ss.cls_sec_id
               AND ts.tid        = :tid:
               AND ts.status     = 1
        )";
    }

    // Optional scope by system (cheap filter on allsubject; you already have idx on system_id). :contentReference[oaicite:2]{index=2}
    $where .= " AND a.system_id = :system_id:";

    // Minimal select, deterministic sort
    $sql = "
        SELECT ss.sec_sub_id, a.subject_name
          FROM section_subjects ss
          JOIN allsubject a ON a.sid = ss.subject_id
         WHERE {$where}
         ORDER BY a.subject_name ASC
    ";

    $rows = $this->db->query($sql, $params)->getResult();

    // Build options (fast string build)
    $out = '<option value="">Select Subject</option>';
    foreach ($rows as $r) {
        $label = htmlspecialchars($r->subject_name ?? '', ENT_QUOTES, 'UTF-8');
        $out  .= "<option value=\"{$r->sec_sub_id}\">{$label}</option>";
    }

    if (function_exists('apcu_store')) {
        apcu_store($cacheKey, $out, 60); // 60s tiny cache
    }

    return $this->response->setJSON(['html' => $out]);
}


private function getActiveExamEid(int $campus_id, ?int $session_id = null): ?int
{
    $b = $this->db->table('exam')->select('eid')
        ->where('campus_id', $campus_id)
        ->where('status', 0);

    // If your exam table has session_id, uncomment the next line:
    if ($session_id) { $b->where('session_id', $session_id); }

    // Pick the most recent exam; adjust orderBy if you have exam_date, etc.
    $row = $b->orderBy('eid', 'DESC')->get(1)->getRow();
    return $row->eid ?? null;
}



// public function get_students()
// {
//     $request    = $this->request;

//     $session_id = (int) $request->getPost('session_id');
//     $campus_id  = (int) $request->getPost('campus_id');
//     $cls_sec_id = (int) $request->getPost('cls_sec_id');
//     // UI sends sec_sub_id in "sub_id"
//     $sec_sub_id = (int) $request->getPost('sub_id');
//     $sortOrder   = (string) $request->getPost('sort_order');
//     if ($sortOrder !== 'reg_no') {
//         $sortOrder = 'name';
//     }

//     // --- Resolve active exam (unannounced) for this campus [+session if provided]
//     $eid = (int) ($this->getActiveExamEid($campus_id, $session_id) ?? 0);
//     if (!$eid) { $eid = (int) ($this->getActiveExamEid($campus_id, null) ?? 0); }
//     if (!$eid) {
//         return $this->response->setBody("<div class='alert alert-warning mb-2'>No active (status=0) exam found for this campus/session.</div>");
//     }
//     if ($cls_sec_id === 0) {
//         return $this->response->setBody("<div class='alert alert-danger mb-2'>Select Class Section</div>");
//     }
//     if (!$this->canCurrentUserEnterResult($cls_sec_id, $sec_sub_id)) {
//         return $this->response->setBody("<div class='alert alert-danger mb-2'>You can only view students for subjects assigned to you.</div>");
//     }

//     // --- Subject meta (short name) + datesheet (exam_date, total_marks)
//     $subjectMeta = $this->db->table('section_subjects ss')
//         ->select('ss.subject_id, a.subject_short_name')
//         ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
//         ->where('ss.sec_sub_id', $sec_sub_id)
//         ->where('ss.status', 1)
//         ->get()->getRowArray() ?: ['subject_id'=>0,'subject_short_name'=>''];

//     $subject_id2        = (int) ($subjectMeta['subject_id'] ?? 0);
//     $subject_short_name = (string) ($subjectMeta['subject_short_name'] ?? '');

//     $ds = $this->db->table('datesheet')
//         ->select('did,total_marks,sec_sub_id,exam_date')
//         ->where(['eid'=>$eid, 'cls_sec_id'=>$cls_sec_id, 'sec_sub_id'=>$sec_sub_id])
//         ->get()->getRow();

//     $total_marks = (int)($ds->total_marks ?? 0);
//     $exam_date   = (string)($ds->exam_date ?? ''); // yyyy-mm-dd

//     // --- Class students (include profile photo)
//     $studentQuery = $this->db->table('student_class sc')
//         ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, s.reg_no')
//         ->join('students s', 's.student_id = sc.student_id')
//         ->where([
//             'sc.session_id' => $session_id,
//             'sc.cls_sec_id' => $cls_sec_id,
//             'sc.status'     => 1,
//         ]);
//     if ($sortOrder === 'reg_no') {
//         $studentQuery->orderBy('s.reg_no', 'ASC')
//             ->orderBy('s.first_name', 'ASC')
//             ->orderBy('s.last_name', 'ASC');
//     } else {
//         $studentQuery->orderBy('s.first_name', 'ASC')
//             ->orderBy('s.last_name', 'ASC');
//     }
//     $students = $studentQuery->get()->getResult();

//     // Build an array of IDs for bulk attendance lookup
//     $studentIds = array_map(static function($r){ return (int)$r->student_id; }, $students);

//     // --- Bulk attendance for the exam date (if we have a date)
//     $attByStudent = [];
//     if ($exam_date && !empty($studentIds)) {
//         $attRows = $this->db->table('attendance')
//             ->select('student_id, status')
//             ->where('date', $exam_date)
//             ->whereIn('student_id', $studentIds)
//             ->get()->getResult();
//         foreach ($attRows as $ar) {
//             $attByStudent[(int)$ar->student_id] = strtoupper((string)$ar->status);
//         }
//     }

//     // --- Existing results (map by student_id)
//     $resultRows = $this->db->table('subject_results')
//         ->select('result_id, student_id, obtained_marks')
//         ->where([
//             'eid'        => $eid,
//             'cls_sec_id' => $cls_sec_id,
//             'sec_sub_id' => $sec_sub_id,
//         ])
//         ->get()->getResult();

//     $resultsByStudent = [];
//     foreach ($resultRows as $r) {
//         $resultsByStudent[(int)$r->student_id] = [
//             'result_id'      => (int)$r->result_id,
//             'obtained_marks' => is_null($r->obtained_marks) ? 0 : $r->obtained_marks,
//         ];
//     }

//     // --- Header info (chips)
//     $session_info      = $this->db->table('academic_session')->where('session_id', $session_id)->get()->getRow();
//     $campus_info       = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
//     $exam_info         = $this->db->table('exam')->where('eid', $eid)->get()->getRow();
//     $classSectioninfo  = getClassSection($cls_sec_id);

//     $chips = [
//         ['label' => 'Session', 'value' => ($session_info->session_name ?? '')],
//         ['label' => 'Campus',  'value' => ($campus_info->campus_name  ?? '')],
//         ['label' => 'Exam',    'value' => ($exam_info->exam_name      ?? '')],
//         ['label' => 'Class',   'value' => ($classSectioninfo['sectionclassname'] ?? '')],
//     ];

//     $subjectChip = '';
//     if ($subject_short_name !== '') {
//         $subjectChip = '<span class="ssr-chip ssr-chip-accent">'
//             . '<span class="ssr-chip-label">'.htmlspecialchars($subject_short_name).'</span>'
//             . (($total_marks) ? '<span class="ssr-chip-meta">Total: '.$total_marks.'</span>' : '')
//             . (($exam_date)   ? '<span class="ssr-chip-meta">'.htmlspecialchars($exam_date).'</span>' : '')
//             . '</span>';
//     }

//     // ---------- Build HTML (desktop table + mobile flex rows; avatar included; no H-scroll) ----------
//     $studentsList  = '<style>
// /* Shell */
// .ssr-card{border:1px solid #d8e0ee;border-radius:14px;box-shadow:0 4px 14px rgba(16,24,40,.06);overflow:hidden;background:#fff;margin-bottom:1rem}
// .ssr-head{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;padding:.75rem .9rem;border-bottom:1px solid #e6edf7;background:linear-gradient(180deg,#f9fbff,#f6f9ff)}
// .ssr-chip{display:inline-flex;align-items:center;gap:.5rem;border:1px solid #d8e0ee;border-radius:999px;padding:.25rem .6rem;background:#fff;font-size:.82rem;color:#0f172a}
// .ssr-chip .ssr-chip-key{font-weight:600;color:#334155}
// .ssr-chip-accent{border-color:#c7d7fe;background:#eef2ff}
// .ssr-chip-accent .ssr-chip-label{font-weight:700;color:#1e3a8a}
// .ssr-chip-accent .ssr-chip-meta{margin-left:.45rem;font-size:.76rem;color:#475569}
// .ssr-toolbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;padding:.5rem .9rem;border-bottom:1px dashed #e6edf7;background:#fff}
// .ssr-legend{display:flex;flex-wrap:wrap;gap:.4rem}
// .ssr-body{padding:.6rem .8rem}
// .badge{font-size:.7rem;padding:.22rem .4rem;border-radius:.5rem}

// /* Desktop table */
// .table.ssr-table{width:100%;border-collapse:collapse;margin:0}
// .table.ssr-table th,.table.ssr-table td{border:1px solid #e6edf7;padding:8px 10px;vertical-align:middle;font-size:.92rem}
// .table.ssr-table th{background:#f8fafc;font-weight:600}
// .col-idx{width:3.25rem;text-align:center}
// .col-student{width:auto}
// .col-marks{width:7rem;text-align:center}

// /* Name cell layout (desktop) */
// .sr-wrap{display:flex;align-items:center;gap:10px;min-width:0}
// .sr-idx{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:999px;background:#eef2ff;color:#1e3a8a;font-weight:700;font-size:.85rem;flex:0 0 28px}
// .sr-avatar{width:40px;height:40px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb;flex:0 0 40px;background:#f3f4f6}
// .sr-avatar.fallback{display:flex;align-items:center;justify-content:center;color:#94a3b8;font-weight:700}
// .sr-student-text{display:flex;flex-direction:column;align-items:flex-start;gap:2px;min-width:0;flex:1 1 auto}
// .sr-name{font-weight:600;color:#0f172a;line-height:1.25;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
// .sr-reg{font-size:.78rem;color:#64748b;line-height:1.2;word-break:break-word;max-width:100%}
// .sr-badges{display:flex;flex-wrap:wrap;gap:4px;margin-top:2px}

// /* Input (2-digit friendly) */
// input.ssr-mark{width:65px;height:32px;padding:0 4px;text-align:center;border-radius:6px;font-size:.92rem;display:inline-block}

// /* Mobile: convert each row into a single flex line (index + avatar + name left, marks right) */
// @media (max-width:768px){
//   .ssr-head{gap:.35rem;padding:.55rem .65rem}
//   .ssr-chip{font-size:.75rem;padding:.2rem .45rem}
//   .ssr-toolbar{flex-direction:column;align-items:stretch;padding:.45rem .65rem}
//   .ssr-toolbar .ssr-note{text-align:center}
//   .ssr-body{padding:.5rem}
//   .table.ssr-table thead{display:none}
//   .table.ssr-table, .table.ssr-table tbody{display:block}
//   .table.ssr-table tr{
//     display:flex; align-items:flex-start; justify-content:space-between; gap:10px;
//     padding:.55rem .6rem; margin:0 0 .8rem 0; background:#fff;
//     border:1px solid #e6edf7; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.05);
//   }
//   .table.ssr-table td{border:none;padding:0}
//   .td-left{display:flex;align-items:flex-start;gap:10px;min-width:0;flex:1 1 auto}
//   .sr-idx{width:26px;height:26px;font-size:.8rem;flex:0 0 26px;margin-top:4px}
//   .sr-avatar{width:36px;height:36px;flex:0 0 36px;margin-top:2px}
//   .sr-name{font-size:.92rem;white-space:normal;overflow:visible;text-overflow:clip}
//   .sr-reg{font-size:.8rem}
//   .td-right{flex:0 0 auto;align-self:center;padding-top:4px!important}
//   input.ssr-mark{width:52px;height:30px;font-size:.9rem}
// }
// @media (max-width:400px){
//   .sr-avatar{width:32px;height:32px;flex:0 0 32px}
//   .sr-idx{width:24px;height:24px;font-size:.75rem;flex:0 0 24px}
// }

// /* Footer */
// .ssr-footer{display:flex;justify-content:flex-end;padding:.6rem .9rem;border-top:1px solid #e6edf7;background:#fafcff}
// .ssr-note{font-size:.8rem;color:#475569}
// </style>';

//     $studentsList .= '<div class="ssr-card">';

//     // Header chips
//     $studentsList .= '<div class="ssr-head">';
//     foreach ($chips as $c) {
//         $studentsList .= '<span class="ssr-chip"><span class="ssr-chip-key">'
//             .htmlspecialchars($c['label']).':</span><span class="ssr-chip-val">'
//             .htmlspecialchars($c['value']).'</span></span>';
//     }
//     $studentsList .= $subjectChip;
//     $studentsList .= '</div>';

//     // Toolbar
//     $studentsList .= '<div class="ssr-toolbar">
//       <div class="ssr-legend">
//         <span class="badge text-bg-light border">Autosave</span>
//         <span class="badge text-bg-success">Present</span>
//         <span class="badge text-bg-danger">Absent</span>
//       </div>
//       <div class="text-muted small ssr-note">Tip: Tab / Shift+Tab to move</div>
//     </div>';

//     // Body + hidden helpers
//     $studentsList .= '<div class="ssr-body">';
//     $studentsList .= '
//       <input type="hidden" name="session_id" value="'.$session_id.'">
//       <input type="hidden" name="campus_id" value="'.$campus_id.'">
//       <input type="hidden" name="eid" value="'.$eid.'">
//       <input type="hidden" name="class_id" value="'.$cls_sec_id.'">
//       <input type="hidden" name="sec_sub_id['.$subject_id2.']" value="'.$sec_sub_id.'">
//     ';

//     // Table (desktop) – becomes flex rows on mobile automatically
//     $studentsList .= '<table class="table table-sm table-hover ssr-table">
//       <thead>
//         <tr>
//           <th class="col-idx">#</th>
//           <th class="col-student">Student <span class="text-muted fw-normal">(Reg No.)</span></th>
//           <th class="col-marks">'.htmlspecialchars($subject_short_name ?: 'Marks').'</th>
//         </tr>
//       </thead>
//       <tbody>';

//     $i = 1;
//     foreach ($students as $s) {
//         $studentName = trim(($s->first_name ?? '').' '.($s->last_name ?? ''));
//         $regNo       = trim((string)($s->reg_no ?? ''));
//         $regHtml     = $regNo !== ''
//             ? '<span class="sr-reg">Reg No: '.htmlspecialchars($regNo).'</span>'
//             : '<span class="sr-reg text-muted">Reg No: —</span>';

//         // avatar (do NOT expand card; fixed size)
//         $avatarHtml = '';
//         $photoRel   = trim((string)($s->profile_photo ?? ''));
//         if ($photoRel !== '') {
//             $abs = FCPATH . 'uploads/' . $photoRel;
//             if (is_file($abs)) {
//                 $avatarHtml = "<img class='sr-avatar' loading='lazy' src='".base_url('uploads/'.$photoRel)."' alt='photo'>";
//             }
//         }
//         if ($avatarHtml === '') {
//             // fallback with initials
//             $initial = strtoupper(mb_substr($studentName, 0, 1));
//             $avatarHtml = "<div class='sr-avatar fallback' aria-hidden='true'>{$initial}</div>";
//         }

//         $res = $resultsByStudent[$s->student_id] ?? ['result_id'=>0,'obtained_marks'=>0];
//         $rid = (int)$res['result_id'];
//         $obm = $res['obtained_marks'];

//         $attStatus = $attByStudent[$s->student_id] ?? '';
//         $isAbsent  = ($attStatus === 'A');
//         $disabledAttr = $isAbsent ? ' disabled ' : '';

//         $badge = $isAbsent
//             ? '<span class="badge text-bg-danger">Absent</span>'
//             : ($attStatus ? '<span class="badge text-bg-success">Present</span>' : '');
//         $badgesWrap = $badge !== '' ? '<div class="sr-badges">'.$badge.'</div>' : '';

//         // One row: left (idx+avatar+name+reg) | right (marks)
//         $studentsList .= '<tr>
//           <td class="td-left" colspan="2">
//             <span class="sr-idx">'.($i++).'</span>
//             '.$avatarHtml.'
//             <div class="sr-student-text">
//               <span class="sr-name">'.htmlspecialchars($studentName).'</span>
//               '.$regHtml.$badgesWrap.'
//             </div>
//             <input type="hidden" name="student_id[]" value="'.$s->student_id.'">
//           </td>
//           <td class="td-right">
//             <input type="hidden" name="result_id['.$s->student_id.']['.$sec_sub_id.']" value="'.$rid.'">
//             <input type="number" step="1" min="0" max="'.$total_marks.'"
//               name="obtained_marks['.$s->student_id.']['.$sec_sub_id.']"
//               value="'.$obm.'"
//               class="form-control form-control-sm ssr-mark mark-input text-center"
//               inputmode="numeric" pattern="[0-9]*"
//               data-sec-sub-id="'.$sec_sub_id.'"'.$disabledAttr.'>
//           </td>
//         </tr>';
//     }

//     $studentsList .= '</tbody></table>';
//     $studentsList .= '</div>'; // ssr-body

//     $studentsList .= '<div class="ssr-footer"><div class="ssr-note">Marks save automatically on blur.</div></div>';
//     $studentsList .= '</div>'; // ssr-card

//     return $this->response->setBody($studentsList);
// }


public function get_students()
{
    $request    = $this->request;

    $session_id = (int) $request->getPost('session_id');
    $campus_id  = (int) $request->getPost('campus_id');
    $cls_sec_id = (int) $request->getPost('cls_sec_id');
    // UI sends sec_sub_id in "sub_id"
    $sec_sub_id = (int) $request->getPost('sub_id');
    $sortOrder   = (string) $request->getPost('sort_order');
    if ($sortOrder !== 'reg_no') {
        $sortOrder = 'name';
    }

    // --- Resolve active exam (unannounced) for this campus [+session if provided]
    $eid = (int) ($this->getActiveExamEid($campus_id, $session_id) ?? 0);
    if (!$eid) { $eid = (int) ($this->getActiveExamEid($campus_id, null) ?? 0); }
    if (!$eid) {
        return $this->response->setBody("<div class='alert alert-warning mb-2'>No active (status=0) exam found for this campus/session.</div>");
    }
    if ($cls_sec_id === 0) {
        return $this->response->setBody("<div class='alert alert-danger mb-2'>Select Class Section</div>");
    }
    if (!$this->canCurrentUserEnterResult($cls_sec_id, $sec_sub_id)) {
        return $this->response->setBody("<div class='alert alert-danger mb-2'>You can only view students for subjects assigned to you.</div>");
    }

    // --- Subject meta (short name) + datesheet (exam_date, total_marks)
    $subjectMeta = $this->db->table('section_subjects ss')
        ->select('ss.subject_id, a.subject_short_name')
        ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
        ->where('ss.sec_sub_id', $sec_sub_id)
        ->where('ss.status', 1)
        ->get()->getRowArray() ?: ['subject_id'=>0,'subject_short_name'=>''];

    $subject_id2        = (int) ($subjectMeta['subject_id'] ?? 0);
    $subject_short_name = (string) ($subjectMeta['subject_short_name'] ?? '');

    $ds = $this->db->table('datesheet')
        ->select('did,total_marks,sec_sub_id,exam_date')
        ->where(['eid'=>$eid, 'cls_sec_id'=>$cls_sec_id, 'sec_sub_id'=>$sec_sub_id])
        ->get()->getRow();

    $total_marks = (int)($ds->total_marks ?? 0);
    $exam_date   = (string)($ds->exam_date ?? ''); // yyyy-mm-dd

    // --- Class students (include profile photo)
    $studentQuery = $this->db->table('student_class sc')
        ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, s.reg_no')
        ->join('students s', 's.student_id = sc.student_id')
        ->where([
            'sc.session_id' => $session_id,
            'sc.cls_sec_id' => $cls_sec_id,
            'sc.status'     => 1,
        ]);
    if ($sortOrder === 'reg_no') {
        $studentQuery->orderBy('s.reg_no', 'ASC')
            ->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC');
    } else {
        $studentQuery->orderBy('s.first_name', 'ASC')
            ->orderBy('s.last_name', 'ASC');
    }
    $students = $studentQuery->get()->getResult();

    // --- REMOVED: Attendance lookup and checking
    // Teacher can now enter marks for all students regardless of attendance

    // --- Existing results (map by student_id)
    $resultRows = $this->db->table('subject_results')
        ->select('result_id, student_id, obtained_marks')
        ->where([
            'eid'        => $eid,
            'cls_sec_id' => $cls_sec_id,
            'sec_sub_id' => $sec_sub_id,
        ])
        ->get()->getResult();

    $resultsByStudent = [];
    foreach ($resultRows as $r) {
        $resultsByStudent[(int)$r->student_id] = [
            'result_id'      => (int)$r->result_id,
            'obtained_marks' => is_null($r->obtained_marks) ? 0 : $r->obtained_marks,
        ];
    }

    // --- Header info (chips)
    $session_info      = $this->db->table('academic_session')->where('session_id', $session_id)->get()->getRow();
    $campus_info       = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
    $exam_info         = $this->db->table('exam')->where('eid', $eid)->get()->getRow();
    $classSectioninfo  = getClassSection($cls_sec_id);

    $chips = [
        ['label' => 'Session', 'value' => ($session_info->session_name ?? '')],
        ['label' => 'Campus',  'value' => ($campus_info->campus_name  ?? '')],
        ['label' => 'Exam',    'value' => ($exam_info->exam_name      ?? '')],
        ['label' => 'Class',   'value' => ($classSectioninfo['sectionclassname'] ?? '')],
    ];

    $subjectChip = '';
    if ($subject_short_name !== '') {
        $subjectChip = '<span class="ssr-chip ssr-chip-accent">'
            . '<span class="ssr-chip-label">'.htmlspecialchars($subject_short_name).'</span>'
            . (($total_marks) ? '<span class="ssr-chip-meta">Total: '.$total_marks.'</span>' : '')
            . (($exam_date)   ? '<span class="ssr-chip-meta">'.htmlspecialchars($exam_date).'</span>' : '')
            . '</span>';
    }

    // ---------- Build HTML (desktop table + mobile flex rows; avatar included; all fields editable) ----------
    $studentsList  = '<style>
/* Shell */
.ssr-card{border:1px solid #d8e0ee;border-radius:14px;box-shadow:0 4px 14px rgba(16,24,40,.06);overflow:hidden;background:#fff;margin-bottom:1rem}
.ssr-head{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;padding:.75rem .9rem;border-bottom:1px solid #e6edf7;background:linear-gradient(180deg,#f9fbff,#f6f9ff)}
.ssr-chip{display:inline-flex;align-items:center;gap:.5rem;border:1px solid #d8e0ee;border-radius:999px;padding:.25rem .6rem;background:#fff;font-size:.82rem;color:#0f172a}
.ssr-chip .ssr-chip-key{font-weight:600;color:#334155}
.ssr-chip-accent{border-color:#c7d7fe;background:#eef2ff}
.ssr-chip-accent .ssr-chip-label{font-weight:700;color:#1e3a8a}
.ssr-chip-accent .ssr-chip-meta{margin-left:.45rem;font-size:.76rem;color:#475569}
.ssr-toolbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;padding:.5rem .9rem;border-bottom:1px dashed #e6edf7;background:#fff}
.ssr-legend{display:flex;flex-wrap:wrap;gap:.4rem}
.ssr-body{padding:.6rem .8rem}
.badge{font-size:.7rem;padding:.22rem .4rem;border-radius:.5rem}

/* Desktop table */
.table.ssr-table{width:100%;border-collapse:collapse;margin:0}
.table.ssr-table th,.table.ssr-table td{border:1px solid #e6edf7;padding:8px 10px;vertical-align:middle;font-size:.92rem}
.table.ssr-table th{background:#f8fafc;font-weight:600}
.col-idx{width:3.25rem;text-align:center}
.col-student{width:auto}
.col-marks{width:7rem;text-align:center}

/* Name cell layout (desktop) */
.sr-wrap{display:flex;align-items:center;gap:10px;min-width:0}
.sr-idx{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:999px;background:#eef2ff;color:#1e3a8a;font-weight:700;font-size:.85rem;flex:0 0 28px}
.sr-avatar{width:40px;height:40px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb;flex:0 0 40px;background:#f3f4f6}
.sr-avatar.fallback{display:flex;align-items:center;justify-content:center;color:#94a3b8;font-weight:700}
.sr-student-text{display:flex;flex-direction:column;align-items:flex-start;gap:2px;min-width:0;flex:1 1 auto}
.sr-name{font-weight:600;color:#0f172a;line-height:1.25;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}
.sr-reg{font-size:.78rem;color:#64748b;line-height:1.2;word-break:break-word;max-width:100%}
.sr-badges{display:flex;flex-wrap:wrap;gap:4px;margin-top:2px}

/* Input (2-digit friendly) */
input.ssr-mark{width:65px;height:32px;padding:0 4px;text-align:center;border-radius:6px;font-size:.92rem;display:inline-block}

/* Mobile: convert each row into a single flex line (index + avatar + name left, marks right) */
@media (max-width:768px){
  .ssr-head{gap:.35rem;padding:.55rem .65rem}
  .ssr-chip{font-size:.75rem;padding:.2rem .45rem}
  .ssr-toolbar{flex-direction:column;align-items:stretch;padding:.45rem .65rem}
  .ssr-toolbar .ssr-note{text-align:center}
  .ssr-body{padding:.5rem}
  .table.ssr-table thead{display:none}
  .table.ssr-table, .table.ssr-table tbody{display:block}
  .table.ssr-table tr{
    display:flex; align-items:flex-start; justify-content:space-between; gap:10px;
    padding:.55rem .6rem; margin:0 0 .8rem 0; background:#fff;
    border:1px solid #e6edf7; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,.05);
  }
  .table.ssr-table td{border:none;padding:0}
  .td-left{display:flex;align-items:flex-start;gap:10px;min-width:0;flex:1 1 auto}
  .sr-idx{width:26px;height:26px;font-size:.8rem;flex:0 0 26px;margin-top:4px}
  .sr-avatar{width:36px;height:36px;flex:0 0 36px;margin-top:2px}
  .sr-name{font-size:.92rem;white-space:normal;overflow:visible;text-overflow:clip}
  .sr-reg{font-size:.8rem}
  .td-right{flex:0 0 auto;align-self:center;padding-top:4px!important}
  input.ssr-mark{width:52px;height:30px;font-size:.9rem}
}
@media (max-width:400px){
  .sr-avatar{width:32px;height:32px;flex:0 0 32px}
  .sr-idx{width:24px;height:24px;font-size:.75rem;flex:0 0 24px}
}

/* Footer */
.ssr-footer{display:flex;justify-content:flex-end;padding:.6rem .9rem;border-top:1px solid #e6edf7;background:#fafcff}
.ssr-note{font-size:.8rem;color:#475569}
</style>';

    $studentsList .= '<div class="ssr-card">';

    // Header chips
    $studentsList .= '<div class="ssr-head">';
    foreach ($chips as $c) {
        $studentsList .= '<span class="ssr-chip"><span class="ssr-chip-key">'
            .htmlspecialchars($c['label']).':</span><span class="ssr-chip-val">'
            .htmlspecialchars($c['value']).'</span></span>';
    }
    $studentsList .= $subjectChip;
    $studentsList .= '</div>';

    // Toolbar (updated legend - removed Present/Absent badges)
    $studentsList .= '<div class="ssr-toolbar">
      <div class="ssr-legend">
        <span class="badge text-bg-light border">Autosave</span>
        <span class="badge text-bg-info">Enter marks for all students</span>
      </div>
      <div class="text-muted small ssr-note">Tip: Tab / Shift+Tab to move</div>
    </div>';

    // Body + hidden helpers
    $studentsList .= '<div class="ssr-body">';
    $studentsList .= '
      <input type="hidden" name="session_id" value="'.$session_id.'">
      <input type="hidden" name="campus_id" value="'.$campus_id.'">
      <input type="hidden" name="eid" value="'.$eid.'">
      <input type="hidden" name="class_id" value="'.$cls_sec_id.'">
      <input type="hidden" name="sec_sub_id['.$subject_id2.']" value="'.$sec_sub_id.'">
    ';

    // Table (desktop) – becomes flex rows on mobile automatically
    $studentsList .= '<table class="table table-sm table-hover ssr-table">
      <thead>
        <tr>
          <th class="col-idx">#</th>
          <th class="col-student">Student <span class="text-muted fw-normal">(Reg No.)</span></th>
          <th class="col-marks">'.htmlspecialchars($subject_short_name ?: 'Marks').'</th>
        </tr>
      </thead>
      <tbody>';

    $i = 1;
    foreach ($students as $s) {
        $studentName = trim(($s->first_name ?? '').' '.($s->last_name ?? ''));
        $regNo       = trim((string)($s->reg_no ?? ''));
        $regHtml     = $regNo !== ''
            ? '<span class="sr-reg">Reg No: '.htmlspecialchars($regNo).'</span>'
            : '<span class="sr-reg text-muted">Reg No: —</span>';

        // avatar (do NOT expand card; fixed size)
        $avatarHtml = '';
        $photoRel   = trim((string)($s->profile_photo ?? ''));
        if ($photoRel !== '') {
            $abs = FCPATH . 'uploads/' . $photoRel;
            if (is_file($abs)) {
                $avatarHtml = "<img class='sr-avatar' loading='lazy' src='".base_url('uploads/'.$photoRel)."' alt='photo'>";
            }
        }
        if ($avatarHtml === '') {
            // fallback with initials
            $initial = strtoupper(mb_substr($studentName, 0, 1));
            $avatarHtml = "<div class='sr-avatar fallback' aria-hidden='true'>{$initial}</div>";
        }

        $res = $resultsByStudent[$s->student_id] ?? ['result_id'=>0,'obtained_marks'=>0];
        $rid = (int)$res['result_id'];
        $obm = $res['obtained_marks'];

        // REMOVED: All attendance checking and badge display
        // All mark fields are now editable (no disabled attribute)

        // One row: left (idx+avatar+name+reg) | right (marks)
        $studentsList .= '<tr>
          <td class="td-left" colspan="2">
            <span class="sr-idx">'.($i++).'</span>
            '.$avatarHtml.'
            <div class="sr-student-text">
              <span class="sr-name">'.htmlspecialchars($studentName).'</span>
              '.$regHtml.'
            </div>
            <input type="hidden" name="student_id[]" value="'.$s->student_id.'">
           </td>
          <td class="td-right">
            <input type="hidden" name="result_id['.$s->student_id.']['.$sec_sub_id.']" value="'.$rid.'">
            <input type="number" step="1" min="0" max="'.$total_marks.'"
              name="obtained_marks['.$s->student_id.']['.$sec_sub_id.']"
              value="'.$obm.'"
              class="form-control form-control-sm ssr-mark mark-input text-center"
              inputmode="numeric" pattern="[0-9]*"
              data-sec-sub-id="'.$sec_sub_id.'">
           </td>
        </tr>';
    }

    $studentsList .= '</tbody></table>';
    $studentsList .= '</div>'; // ssr-body

    $studentsList .= '<div class="ssr-footer"><div class="ssr-note">Marks save automatically on blur. All students are editable.</div></div>';
    $studentsList .= '</div>'; // ssr-card

    return $this->response->setBody($studentsList);
}


private function requireResultEntryAccess(): void
{
    if (!$this->shouldScopeToTeacherSubjects()) {
        check_permission('admin-add-students-result');
    }
}

private function shouldScopeToTeacherSubjects(): bool
{
    if (in_array(5, currentUserRoles(), true)) {
        return true;
    }

    if ($this->currentUserHasRoleNameId(5)) {
        return true;
    }

    $teacherId = (int) $this->session->get('member_userid');
    if ($teacherId <= 0) {
        return false;
    }

    return (bool) $this->db->table('teacher_subjects')
        ->where('tid', $teacherId)
        ->where('status', 1)
        ->limit(1)
        ->countAllResults();
}

private function canCurrentUserEnterResult(int $clsSecId, int $secSubId): bool
{
    if (!$this->shouldScopeToTeacherSubjects()) {
        return true;
    }

    $teacherId = (int) $this->session->get('member_userid');
    if ($teacherId <= 0 || $clsSecId <= 0 || $secSubId <= 0) {
        return false;
    }

    return (bool) $this->db->table('teacher_subjects')
        ->where('tid', $teacherId)
        ->where('cls_sec_id', $clsSecId)
        ->where('sec_sub_id', $secSubId)
        ->where('status', 1)
        ->countAllResults();
}

private function teacherHasSubjectInSection(int $teacherId, int $clsSecId): bool
{
    if ($teacherId <= 0 || $clsSecId <= 0) {
        return false;
    }

    return (bool) $this->db->table('teacher_subjects')
        ->where('tid', $teacherId)
        ->where('cls_sec_id', $clsSecId)
        ->where('status', 1)
        ->countAllResults();
}

private function getCurrentTeacherSubjectSections(): array
{
    $teacherId = (int) $this->session->get('member_userid');
    $campusId = (int) $this->session->get('member_campusid');

    if ($teacherId <= 0 || $campusId <= 0) {
        return [];
    }

    return $this->db->table('teacher_subjects ts')
        ->distinct()
        ->select('
            cs.cls_sec_id,
            cs.section_id,
            c.class_id,
            c.class_name,
            s.section_name,
            CONCAT(
                COALESCE(NULLIF(c.class_short_name, ""), c.class_name),
                " (",
                COALESCE(NULLIF(s.short_name, ""), s.section_name),
                ")"
            ) AS sectionclassname
        ')
        ->join('class_section cs', 'cs.cls_sec_id = ts.cls_sec_id', 'inner')
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->where('ts.tid', $teacherId)
        ->where('ts.status', 1)
        ->where('cs.status', 1)
        ->where('cs.campus_id', $campusId)
        ->groupBy('cs.cls_sec_id, cs.section_id, c.class_id, c.class_name, s.section_name, c.class_short_name, s.short_name')
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('s.section_id', 'ASC')
        ->get()
        ->getResultArray();
}

private function isCurrentTeacher(): bool
{
    return $this->shouldScopeToTeacherSubjects();
}

private function currentUserHasRoleNameId(int $roleNameId): bool
{
    $userId = (int) $this->session->get('member_userid');
    $campusId = (int) $this->session->get('member_campusid');

    if ($userId <= 0 || $roleNameId <= 0) {
        return false;
    }

    $planId = $this->getCampusPlanId($campusId);

    $primary = $this->db->table('user_roles ur')
        ->join('roles r', 'r.id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
        ->where('ur.userID', $userId)
        ->where('r.role_name_id', $roleNameId)
        ->countAllResults();

    if ($primary > 0) {
        return true;
    }

    $legacy = $this->db->table('user_roles ur')
        ->join('roles r', 'r.role_name_id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
        ->where('ur.userID', $userId)
        ->where('r.role_name_id', $roleNameId)
        ->countAllResults();

    return $legacy > 0;
}

private function getCampusPlanId(int $campusId): int
{
    if ($campusId <= 0) {
        return 0;
    }

    $row = $this->db->table('campus_bills')
        ->select('plan_id')
        ->where('status', 1)
        ->where('campus_id', $campusId)
        ->orderBy('campus_expiry', 'DESC')
        ->get()
        ->getRow();

    return (int) ($row->plan_id ?? 0);
}

}
