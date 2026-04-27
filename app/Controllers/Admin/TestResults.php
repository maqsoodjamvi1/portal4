<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class TestResults extends BaseController
{
    protected $db;
    protected $session;
    protected $data = [];

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();

        helper(['url', 'form']);
        // If your helpers live elsewhere, also: helper(['permissions', 'school']);
    }

    public function index()
    {
        if (function_exists('check_permission')) {
            check_permission('admin-test-results');
        }

        // ✅ Provide sections for the <select> in admin/test_results.php
        $this->data['sectionsclassinfo'] = $this->getSectionsClassInfo();

        return view('admin/test_results', $this->data);
    }

    public function data()
    {
        if (function_exists('check_permission')) {
            check_permission('admin-test-results');
        }

        $request   = $this->request;
        $draw      = (int) ($request->getPost('draw') ?? 0);
        $length    = (int) ($request->getPost('length') ?? 10);
        $start     = (int) ($request->getPost('start') ?? 0);
        $searchArr = $request->getPost('search');
        $keyword   = is_array($searchArr) ? trim((string) ($searchArr['value'] ?? '')) : '';

        $campusid  = (int) $this->session->get('member_campusid');
        $user_id   = (int) $this->session->get('member_userid');

        $response                   = new stdClass();
        $response->draw             = $draw;
        $response->data             = [];
        $response->recordsTotal     = 0;
        $response->recordsFiltered  = 0;

        // Count
        $builder = $this->db->table('test_results A')->select('COUNT(A.test_result_id) AS ccount', false);
        $builder->where('A.campus_id', $campusid);
        if ($keyword !== '') {
            $builder->where('A.text', $keyword); // kept identical to CI3
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = (int) ($q->ccount ?? 0);

        // Data list
        $list = $this->db->table('test_results A')->select('A.*');
        $list->where('A.campus_id', $campusid);
        if ($keyword !== '') {
            $list->where('A.text', $keyword);
        }
        $list->orderBy('A.test_result_id', 'DESC')
             ->limit($length, $start);

        $results = $list->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;

        foreach ($results as $row) {
            $data               = [];
            $data['student_id'] = $row->student_id;

            $studentsinfo = $this->db->table('students')->where('student_id', $row->student_id)->get()->getRow();
            $classSectioninfo = $this->db->table('class_section')->where('cls_sec_id', $row->cls_sec_id)->get()->getRow();

            $classesinfo = null;
            if ($classSectioninfo) {
                $classesinfo = $this->db->table('classes')->where('class_id', $classSectioninfo->class_id)->get()->getRow();
            }

            $Sectionsubjectinfo = $this->db->table('section_subjects')
                ->where('sec_sub_id', $row->sec_sub_id)
                ->where('status', 1)
                ->get()->getRow();

            $subject_name = '';
            if ($Sectionsubjectinfo) {
                $allsubjectinfo = $this->db->table('allsubject')
                    ->where('sid', $Sectionsubjectinfo->subject_id)
                    ->get()->getRow();
                $subject_name = $allsubjectinfo->subject_name ?? '';
            }

            $test_info = $this->db->table('tests')->where('test_id', $row->test_id)->get()->getRow();

            if ($studentsinfo) {
                $class_name               = $classesinfo->class_name ?? '';
                $data['student']          = trim(($studentsinfo->first_name ?? '') . ' ' . ($studentsinfo->last_name ?? ''));
                $data['class']            = $class_name;
                $data['subject']          = $subject_name;
                $data['obtained_marks']   = $row->obtained_marks;
                $data['test_title']       = $test_info->test_title ?? '';
                $data['test_date']        = $test_info->test_date ?? '';
                $response->data[]         = $data;
            }
        }

        return $this->response->setJSON($response);
    }


// app/Controllers/Admin/TestResults.php
public function cardData()
{
    helper(['text', 'url']);

    $cls_sec_id    = (int) $this->request->getPost('cls_sec_id');
    $subject_id    = (int) $this->request->getPost('subject_id'); // optional
    $start_in      = trim((string) $this->request->getPost('start_date'));
    $end_in        = trim((string) $this->request->getPost('end_date'));

    $campus_id  = (int) ($this->session->get('member_campusid')  ?? 0);
    $session_id = (int) ($this->session->get('member_sessionid') ?? 0);

    if ($cls_sec_id <= 0) {
        return '<div class="alert alert-danger">Class section not selected.</div>';
    }

    // ---- Date window (current term fallback)
    $today      = date('Y-m-d');
    $start_date = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_in) ? $start_in : null);
    $end_date   = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_in)   ? $end_in   : null);

    if (!$start_date || !$end_date) {
        $term = $this->db->table('terms_session')
            ->select('start_date, end_date')
            ->where('session_id', $session_id)
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->get()->getRow();

        if ($term) {
            $start_date = $term->start_date;
            $end_date   = $term->end_date;
        } else {
            $as = $this->db->table('academic_session')
                ->select('start_date, end_date')
                ->where('session_id', $session_id)
                ->get()->getRow();
            $start_date = $as->start_date ?? $today;
            $end_date   = $as->end_date   ?? $today;
        }
    }

    // ---- Campus system -> grading policy
    $campus = $this->db->table('campus')->select('system_id')
        ->where('campus_id', $campus_id)->get()->getRow();
    $system_id = (int)($campus->system_id ?? 0);

    $policies = $this->db->table('grading_policy gp')
        ->select('gp.mark_from, gp.mark_to, g.name AS grade_name, g.detail AS grade_detail')
        ->join('grades g', 'g.gid = gp.gid', 'left')
        ->where('gp.system_id', $system_id)
        ->orderBy('gp.mark_from', 'DESC')
        ->get()->getResult();

    $assignGrade = function (?float $percent) use ($policies) : array {
        if ($percent === null) return ['-', ''];
        foreach ($policies as $p) {
            $from = (float)$p->mark_from; $to = (float)$p->mark_to;
            if ($percent >= $from && $percent <= $to) {
                return [ (string)($p->grade_name ?? '-'), (string)($p->grade_detail ?? '') ];
            }
        }
        return ['-', ''];
    };

    // ---- Students in class-section
    $students = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.reg_no, s.profile_photo, s.parent_id, p.f_name, p.father_contact, p.whatsapp')
        ->join('student_class sc', 'sc.student_id = s.student_id', 'inner')
        ->join('parents p', 'p.parent_id = s.parent_id', 'left')
        ->where('sc.cls_sec_id', $cls_sec_id)
        ->where('sc.session_id', $session_id)
        ->where('s.campus_id', $campus_id)
        ->where('s.status', 1)
        ->orderBy('s.first_name', 'ASC')
        ->get()->getResult();

    if (!$students) {
        return '<div class="alert alert-warning">No students found for this class section.</div>';
    }

    // ---- Tests (with syllabus)
    $testsQB = $this->db->table('tests t')
        ->select('t.test_id, t.sec_sub_id, t.test_date, t.total_marks, t.syllabus')
        ->where('t.cls_sec_id', $cls_sec_id)
        ->where('t.session_id', $session_id)
        ->where('t.test_date >=', $start_date)
        ->where('t.test_date <=', $end_date);

    if ($subject_id > 0) {
        $testsQB->join('section_subjects ss', 'ss.sec_sub_id = t.sec_sub_id', 'inner')
                ->where('ss.subject_id', $subject_id);
    }

    $tests = $testsQB->orderBy('t.test_date', 'ASC')->get()->getResult();
    if (!$tests) {
        return '<div class="alert alert-info">No tests scheduled for this class in the selected period.</div>';
    }

    // ---- Subject names
    $subjectMap = $this->db->table('section_subjects ss')
        ->select('ss.sec_sub_id, a.subject_name')
        ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
        ->where('ss.cls_sec_id', $cls_sec_id)
        ->get()->getResult();
    $secSubToName = [];
    foreach ($subjectMap as $r) $secSubToName[(int)$r->sec_sub_id] = $r->subject_name ?? 'Subject';

    // ---- All results for these tests
    $testIds = array_map(static fn($t) => (int)$t->test_id, $tests);
    $resByStudentTest = [];
    if ($testIds) {
        $rows = $this->db->table('test_results tr')
            ->select('tr.student_id, tr.test_id, tr.obtained_marks, tr.remarks')
            ->whereIn('tr.test_id', $testIds)
            ->get()->getResult();
        foreach ($rows as $r) {
            $resByStudentTest[(int)$r->student_id][(int)$r->test_id] = [
                'marks'   => (float)$r->obtained_marks,
                'remarks' => (string)($r->remarks ?? '')
            ];
        }
    }

    // ---- Attendance summary (absents + total)
    $studentIds = array_map(static fn($s) => (int)$s->student_id, $students);
    $attRows = $this->db->table('attendance')
        ->select('student_id, SUM(CASE WHEN status="A" THEN 1 ELSE 0 END) AS absents, COUNT(*) AS total_classes', false)
        ->whereIn('student_id', $studentIds)
        ->where('date >=', $start_date)
        ->where('date <=', $end_date)
        ->groupBy('student_id')
        ->get()->getResult();
    $attMap = [];
    foreach ($attRows as $a) {
        $attMap[(int)$a->student_id] = [
            'absents'       => (int)($a->absents ?? 0),
            'total_classes' => (int)($a->total_classes ?? 0),
        ];
    }

    // ---- Group tests per subject
    $testsBySubject = [];
    foreach ($tests as $t) $testsBySubject[(int)$t->sec_sub_id][] = $t;

    // ---- Helpers
    $fmtDay  = static fn($d) => date('D', strtotime($d));
    $fmtDate = static fn($d) => date('d M Y', strtotime($d));

    $photoUrl = function($raw) {
        $raw = trim((string)$raw);
        if ($raw === '') return base_url('public/dist/img/avatar.png');
        if (str_starts_with($raw, 'http') || str_starts_with($raw, '/')) return $raw;
        return base_url('uploads/' . $raw); // adjust if needed
    };

    // ---- Build UI
    $cards = [];
    foreach ($students as $s) {
        $fullName = trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));
        $photo    = $photoUrl($s->profile_photo ?? '');
        $att      = $attMap[(int)$s->student_id] ?? ['absents' => 0, 'total_classes' => 0];

        $card  = '<div class="card shadow-sm mb-4" style="border-radius:14px;border:1px solid #e8e8e8;">';
        $card .=   '<div class="card-body">';

        // Student header
        $card .= '<div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">';
        $card .=   '<div class="d-flex align-items-center" style="gap:12px;">';
        $card .=     '<img src="'.esc($photo).'" onerror="this.onerror=null;this.src=\''.esc(base_url('public/dist/img/avatar.png')).'\';" alt="photo" style="width:56px;height:56px;object-fit:cover;border-radius:50%;border:1px solid #eee;">';
        $card .=     '<div>';
        $card .=       '<div style="font-weight:600;font-size:1.05rem;">'.esc($fullName).'</div>';
        $card .=       '<div class="text-muted" style="font-size:.9rem;">Reg # '.esc($s->reg_no).' • Father: '.esc($s->f_name ?? '—').' • '.esc($s->father_contact ?? $s->whatsapp ?? '—').'</div>';
        $card .=       '<div class="text-muted" style="font-size:.85rem;">Term: '.esc(date('d M Y', strtotime($start_date))).' → '.esc(date('d M Y', strtotime($end_date))).'</div>';
        $card .=     '</div>';
        $card .=   '</div>';
        $card .=   '<div class="text-nowrap">';
        $card .=     '<span class="badge badge-light" style="border:1px solid #ddd;">Total Classes: <b>'.(int)$att['total_classes'].'</b></span> ';
        $card .=     '<span class="badge badge-light" style="border:1px solid #ddd;">Absents: <b>'.(int)$att['absents'].'</b></span>';
        $card .=   '</div>';
        $card .= '</div>';

        // Subject groups
        foreach ($testsBySubject as $sec_sub_id => $subjectTests) {
            $subjectName = $secSubToName[$sec_sub_id] ?? 'Subject';

            $card .= '<div class="mt-3 p-3" style="border:1px dashed #e3e3e3;border-radius:12px;">';
            $card .=   '<div style="font-weight:600;margin-bottom:8px;">'.esc($subjectName).'</div>';

            // compact grid of mini-cards
            $card .=   '<div class="row">';

            $obtSum = 0; $maxSum = 0;

            foreach ($subjectTests as $t) {
                $sc   = $resByStudentTest[$s->student_id][$t->test_id] ?? null;
                $got  = $sc ? (float)$sc['marks'] : null;
                $perc = ($got !== null && (int)$t->total_marks > 0) ? round(($got * 100.0) / (int)$t->total_marks, 1) : null;
                [$grade, $gdetail] = $assignGrade($perc);

                if ($got !== null) { $obtSum += $got; $maxSum += (int)$t->total_marks; }

                $day  = $fmtDay($t->test_date);
                $date = $fmtDate($t->test_date);
                $syll = trim((string)($t->syllabus ?? ''));

                $card .= '<div class="col-sm-6 col-lg-4 mb-2">';
                $card .=   '<div class="border rounded p-2 h-100" style="background:#fafafa;">';

                // Row 1: Day + Date (as header)
                $card .=     '<div style="font-weight:600;">'.esc($day).' '.esc($date).'</div>';

                // Optional small syllabus line
                if ($syll !== '') {
                    $card .= '<div class="text-muted" style="font-size:.85rem;word-break:break-word;">Syllabus: '.esc($syll).'</div>';
                }

                // Row 2: Marks + %
                $card .=     '<div class="d-flex justify-content-between" style="margin-top:6px;">';
                $card .=       '<div><span class="badge badge-light" style="border:1px solid #ddd;">'
                             .   ($got === null ? '— / '.(int)$t->total_marks : ((int)$got.' / '.(int)$t->total_marks))
                             . '</span></div>';
                $card .=       '<div><span class="badge badge-info">'.($perc === null ? '— %' : ($perc.'%')).'</span></div>';
                $card .=     '</div>';

                // Row 3: Grade
                $title = $gdetail ? ' title="'.esc($gdetail, 'attr').'" data-toggle="tooltip"' : '';
                $card .=     '<div class="mt-1">Grade: <span'.$title.' class="badge badge-success" style="min-width:44px;">'.esc($grade).'</span></div>';

                $card .=   '</div>';
                $card .= '</div>';
            }

            $card .=   '</div>'; // row of mini-cards

            // Subject totals
            if ($maxSum > 0) {
                $overall = round($obtSum * 100.0 / $maxSum, 1);
                [$overallGrade, $overallDetail] = $assignGrade($overall);
                $titleOverall = $overallDetail ? ' title="'.esc($overallDetail, 'attr').'" data-toggle="tooltip"' : '';
                $card .= '<div class="mt-2 text-right">'
                      .    '<span class="badge badge-primary" style="border-radius:8px;">Total: '.(int)$obtSum.' / '.(int)$maxSum.'</span> '
                      .    '<span class="badge badge-success" style="border-radius:8px;">'.$overall.'%</span> '
                      .    '<span'.$titleOverall.' class="badge badge-dark" style="border-radius:8px;">Grade: '.esc($overallGrade).'</span>'
                      .  '</div>';
            } else {
                $card .= '<div class="mt-2 text-muted" style="font-size:.9rem;">No scored tests yet in this subject.</div>';
            }

            $card .= '</div>'; // subject wrap
        }

        $card .=   '</div>'; // body
        $card .= '</div>';   // card
        $cards[] = $card;
    }

    $html  = '<style>
      .badge { font-size:.88rem; }
      @media print { .card { page-break-inside: avoid; } }
    </style>';
    $html .= implode('', $cards);
    $html .= '<div class="mt-3">
                <button class="btn btn-secondary" onclick="window.print()">
                  <i class="fas fa-print"></i> Print
                </button>
              </div>
              <script>$(function(){ $(\'[data-toggle="tooltip"]\').tooltip(); });</script>';

    return $html;
}


    public function add()
    {
        if (function_exists('check_permission')) {
            check_permission('admin-add-test-result');
        }

        $campusid  = (int) $this->session->get('member_campusid');
        $sessionid = (int) $this->session->get('member_sessionid');

        $this->data['sessionData'] = [
            'campusid'  => $campusid,
            'sessionid' => $sessionid,
        ];

        $this->data['infostudents']      = $this->db->table('students')->where('status', 1)->get()->getResult();
        $this->data['sectionsclassinfo'] = $this->getSectionsClassInfo(); // ✅ also fix here
        $this->data['campusinfo']        = $this->db->table('campus')->where('campus_id', $campusid)->get()->getResult();

        $this->data['testSeriesinfo'] = $this->db->table('test_series')
            ->where('campus_id', $campusid)
            ->where('session_id', $sessionid)
            ->get()->getResult();

        $this->data['academic_session'] = $this->db->table('academic_session')
            ->where('session_id', $sessionid)
            ->get()->getResult();

        $this->data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

        return view('admin/test_results_edit', $this->data);
    }


private function getSectionsClassInfo(): array
    {
        $currentrole = function_exists('currentUserRoles') ? (array) currentUserRoles() : [];
        if (is_array($currentrole) && in_array(5, $currentrole, true)) {
            $raw = function_exists('teacherSubjectSections') ? (array) teacherSubjectSections() : [];
        } else {
            $raw = function_exists('userClassSections') ? (array) userClassSections() : [];
        }

        $out = [];
        foreach ($raw as $r) {
            if (is_object($r)) {
                $r = (array) $r;
            }
            $out[] = [
                'cls_sec_id'   => (int) ($r['cls_sec_id']   ?? $r['section_id'] ?? $r['id'] ?? 0),
                'class_name'   => (string) ($r['class_name'] ?? $r['classname'] ?? $r['class'] ?? ''),
                'section_name' => (string) ($r['section_name'] ?? $r['sectionname'] ?? $r['section'] ?? ''),
            ];
        }

        // remove empties / zeros
        return array_values(array_filter($out, static fn($row) => !empty($row['cls_sec_id'])));
    }

    public function edit()
    {
        if (function_exists('check_permission')) {
            check_permission('admin-edit-test-result');
        }

        $id        = (int) $this->request->getGet('id');
        $campusid  = (int) $this->session->get('member_campusid');
        $sessionid = (int) $this->session->get('member_sessionid');

        $this->data['sessionData'] = [
            'campusid'  => $campusid,
            'sessionid' => $sessionid,
        ];

        $this->data['info']          = $this->db->table('subject_results')->where('student_id', $id)->get()->getRow();
        $this->data['infostudents']  = $this->db->table('students')->get()->getResult();
        $this->data['classesinfo']   = $this->db->table('classes')->get()->getResult();
        $this->data['subjectinfo']   = $this->db->table('allsubject')->get()->getResult();

        return view('admin/test_results_edit', $this->data);
    }
public function save()
{
    if (function_exists('check_permission')) {
        check_permission('admin-add-test-result');
    }

    $req        = $this->request;
    $session_id = (int) $this->session->get('member_sessionid');
    $campus_id  = (int) $this->session->get('member_campusid');
    $user_id    = (int) $this->session->get('member_userid');
    $now        = date('Y-m-d H:i:s');

    // -------- Inputs --------
    $cls_sec_id    = (int) ($req->getPost('cls_sec_id') ?? 0);
    $subject_id    = (int) ($req->getPost('subject_id') ?? 0);
    $test_date     = (string) ($req->getPost('test_date') ?? '');
    $syllabus      = (string) ($req->getPost('test_syllabus') ?? '');

    // NEW: evaluation mode / options
    $evaluation_mode = (string) ($req->getPost('evaluation_mode') ?? 'marks');   // 'marks' | 'options'
    $options_json    = (string) ($req->getPost('options_json') ?? '[]');

    // Arrays (index-aligned)
    $studentIds   = array_values((array) ($req->getPost('student_id') ?? []));
    $marksArray   = array_values((array) ($req->getPost('obtained_marks') ?? []));
    $remarksArray = array_values((array) ($req->getPost('remarks') ?? []));

    // Optional hidden IDs
    $posted_test_id = (int) ($req->getPost('test_id') ?? 0);
    $posted_sec_sub = (int) ($req->getPost('sec_sub_id') ?? 0);

    // teacher-entered total marks (may be overridden by options)
    $total_marks_in = $req->getPost('test_marks');
    $total_marks    = is_numeric($total_marks_in) ? (int) $total_marks_in : null;

    // -------- Basic validation --------
    if (!$cls_sec_id || !$subject_id || !$test_date || empty($studentIds)) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Missing required fields (class, subject, date, or students).'
        ]);
    }

    // If using pre-defined options, set total_marks to the largest option value when needed
    if ($evaluation_mode === 'options') {
        $opts = json_decode($options_json, true);
        if (is_array($opts) && count($opts)) {
            $maxVal = 0;
            foreach ($opts as $o) {
                $v = isset($o['value']) && is_numeric($o['value']) ? (float) $o['value'] : 0;
                if ($v > $maxVal) $maxVal = $v;
            }
            if (!$total_marks || $total_marks < $maxVal) {
                $total_marks = (int) ceil($maxVal);
            }
        }
    }

    if ($total_marks === null) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Total marks are required and must be numeric.'
        ]);
    }

    // -------- Resolve sec_sub_id --------
    $sec_sub_id = $posted_sec_sub;
    if ($sec_sub_id <= 0) {
        $row = $this->db->table('section_subjects')
            ->select('sec_sub_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('subject_id', $subject_id)
            ->where('status', 1)
            ->get()->getRow();
        $sec_sub_id = (int) ($row->sec_sub_id ?? 0);
    }
    if ($sec_sub_id <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'No section-subject mapping found for the selected class & subject.'
        ]);
    }

    // Keep arrays aligned (defensive)
    $count = min(count($studentIds), count($marksArray));
    if ($count === 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'No student rows to save.'
        ]);
    }

    $this->db->transStart();

    // -------- UPSERT: tests --------
    $testsTbl = $this->db->table('tests');
    $test_id  = 0;

    // if a posted id exists and valid, reuse
    if ($posted_test_id > 0) {
        $exists = $testsTbl->select('test_id')
            ->where('test_id', $posted_test_id)
            ->limit(1)->get()->getRow();
        if ($exists) $test_id = (int) $exists->test_id;
    }

    // otherwise find by unique key
    if ($test_id <= 0) {
        $found = $testsTbl->select('test_id')
            ->where([
                'campus_id'  => $campus_id,
                'session_id' => $session_id,
                'cls_sec_id' => $cls_sec_id,
                'sec_sub_id' => $sec_sub_id,
                'test_date'  => $test_date,
            ])->limit(1)->get()->getRow();
        if ($found) $test_id = (int) $found->test_id;
    }

    // update/insert
    if ($test_id > 0) {
        $testsTbl->where('test_id', $test_id)->update([
            'total_marks'  => $total_marks,
            'syllabus'     => $syllabus,
            'updated_date' => $now,
            'user_id'      => $user_id,
        ]);
    } else {
        $testsTbl->insert([
            'campus_id'    => $campus_id,
            'session_id'   => $session_id,
            'cls_sec_id'   => $cls_sec_id,
            'sec_sub_id'   => $sec_sub_id,
            'test_date'    => $test_date,
            'total_marks'  => $total_marks,
            'syllabus'     => $syllabus,
            'created_date' => $now,
            'updated_date' => $now,
            'user_id'      => $user_id,
        ]);
        $test_id = (int) $this->db->insertID();
    }

    if ($test_id <= 0) {
        $this->db->transComplete();
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Could not create or locate the test record.'
        ]);
    }

    // -------- UPSERT: test_results --------
    $resultsTbl = $this->db->table('test_results');
    $inserted   = 0;
    $updated    = 0;

    for ($i = 0; $i < $count; $i++) {
        $sid = (int) ($studentIds[$i] ?? 0);
        if ($sid <= 0) continue;

        // normalize marks (numeric only)
        $obt = $marksArray[$i] ?? '';
        $obt = is_numeric($obt) ? (float) $obt : 0.0;
        if ($obt < 0) $obt = 0.0;
        if ($obt > $total_marks) $obt = (float) $total_marks;

        // per-student remarks (optional)
        $remark = isset($remarksArray[$i]) ? trim((string) $remarksArray[$i]) : '';
        $remark = ($remark === '') ? null : $remark;

        // exists?
        $existing = $resultsTbl->select('test_result_id')
            ->where([
                'test_id'    => $test_id,
                'student_id' => $sid,
                'cls_sec_id' => $cls_sec_id,
                'sec_sub_id' => $sec_sub_id,
            ])->limit(1)->get()->getRow();

        if ($existing) {
            $resultsTbl->where('test_result_id', (int) $existing->test_result_id)->update([
                'obtained_marks' => $obt,
                'remarks'        => $remark,
                'updated_date'   => $now,
                'user_id'        => $user_id,
            ]);
            $updated++;
        } else {
            $resultsTbl->insert([
                'cls_sec_id'     => $cls_sec_id,
                'sec_sub_id'     => $sec_sub_id,
                'test_id'        => $test_id,
                'student_id'     => $sid,
                'obtained_marks' => $obt,
                'remarks'        => $remark,
                'created_date'   => $now,
                'updated_date'   => $now,
                'user_id'        => $user_id,
            ]);
            $inserted++;
        }
    }

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Database error while saving.'
        ]);
    }

    return $this->response->setJSON([
        'success'      => true,
        'msg'          => 'Saved successfully.',
        'test_id'      => $test_id,
        'inserted'     => $inserted,
        'updated'      => $updated,
        'total_marks'  => $total_marks,           // returned for UI consistency
        'eval_mode'    => $evaluation_mode,       // echo back
    ]);
}



/** Insert or update a single test_results row */
private function upsertTestResult(int $campus_id, int $studentID, int $t_series_id, int $test_id, int $cls_sec_id, int $sec_sub_id, float $obtainedmarks, ?string $comment, int $currResultID = 0): void
{
    $data = [
        'campus_id'      => $campus_id,
        'student_id'     => $studentID,
        't_series_id'    => $t_series_id,
        'test_id'        => $test_id,
        'cls_sec_id'     => $cls_sec_id,
        'sec_sub_id'     => $sec_sub_id,
        'obtained_marks' => $obtainedmarks,
        'comment'        => $remarks,
    ];

    if ($currResultID > 0) {
        $this->db->table('test_results')
            ->where('test_result_id', $currResultID)
            ->where('student_id', $studentID)
            ->update($data);
    } else {
        $this->db->table('test_results')->insert($data);
    }
}





public function get_subjects()
{
    if (function_exists('check_permission')) {
        check_permission('admin-add-test-result');
    }

    if (!$this->request->is('post')) {
        return $this->response->setStatusCode(405)
            ->setJSON(['success' => false, 'message' => 'POST required']);
    }

    $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
    if (!$cls_sec_id) {
        return $this->response->setJSON(['success' => false, 'subjects' => [], 'message' => 'cls_sec_id is required']);
    }

    // section_subjects → allsubject
    $rows = $this->db->table('section_subjects ss')
        ->select('s.sid, s.subject_name, ss.sec_sub_id')
        ->join('allsubject s', 's.sid = ss.subject_id')
        ->where('ss.cls_sec_id', $cls_sec_id)
        ->where('ss.status', 1)
        ->orderBy('s.subject_name', 'ASC')
        ->get()->getResultArray();

    // Your JS expects: success + subjects[{sid, subject_name}]
    return $this->response->setJSON([
        'success'  => true,
        'subjects' => array_map(static function ($r) {
            return [
                'sid'          => (int) ($r['sid'] ?? 0),
                'subject_name' => (string) ($r['subject_name'] ?? ''),
                // returning sec_sub_id too (handy if you ever need it)
                'sec_sub_id'   => (int) ($r['sec_sub_id'] ?? 0),
            ];
        }, $rows),
    ]);
}


// In App\Controllers\Admin\TestSeriesResultCard.php
public function listTests()
{
    if (!$this->request->is('post')) {
        return $this->response->setStatusCode(405)->setBody('POST required');
    }

    $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
    $subject_id = (int) $this->request->getPost('subject_id');   // optional
    $start_date = trim((string) $this->request->getPost('start_date')); // optional
    $end_date   = trim((string) $this->request->getPost('end_date'));   // optional

    $campus_id  = (int) session('member_campusid');
    $session_id = (int) session('member_sessionid');

    if (!$cls_sec_id) {
        return $this->response->setBody('<div class="alert alert-info">Select a class section to see tests.</div>');
    }

    // We compute Test No per SUBJECT across the whole session (for continuity),
    // then filter by the user’s date/subject selection in the outer query.
    // MySQL 8 window functions are used for the numbering.
    $sql = "
        WITH all_tests AS (
            SELECT
                t.test_id,
                t.cls_sec_id,
                t.sec_sub_id,
                t.test_date,
                t.total_marks,
                t.syllabus,
                ss.subject_id,
                a.subject_name,
                ROW_NUMBER() OVER (
                    PARTITION BY ss.subject_id
                    ORDER BY t.test_date ASC, t.test_id ASC
                ) AS test_no
            FROM tests t
            JOIN section_subjects ss ON ss.sec_sub_id = t.sec_sub_id
            LEFT JOIN allsubject a   ON a.sid = ss.subject_id
            WHERE t.session_id = :session_id:
              AND t.campus_id  = :campus_id:
              AND t.cls_sec_id = :cls_sec_id:
        )
        SELECT *
        FROM all_tests
        WHERE 1=1
        " . ($subject_id ? " AND subject_id = :subject_id: " : "") . "
          " . ($start_date !== '' ? " AND test_date >= :start_date: " : "") . "
          " . ($end_date   !== '' ? " AND test_date <= :end_date:   " : "") . "
        ORDER BY subject_name ASC, test_no ASC
    ";

    $builder = $this->db->query($sql, array_filter([
        'session_id' => $session_id,
        'campus_id'  => $campus_id,
        'cls_sec_id' => $cls_sec_id,
        'subject_id' => $subject_id ?: null,
        'start_date' => $start_date ?: null,
        'end_date'   => $end_date   ?: null,
    ]));

    $rows = $builder->getResult();
    if (!$rows) {
        return $this->response->setBody('<div class="alert alert-warning mb-2">No tests found for the current selection.</div>');
    }

    // Build compact table
    $html  = '<div class="card mb-3"><div class="card-body p-2">';
    $html .= '<div class="d-flex align-items-center mb-2">';
    $html .= '<i class="fas fa-list-alt mr-2 text-secondary"></i><strong>Tests in Selection</strong>';
    $html .= '</div>';
    $html .= '<div class="table-responsive">';
    $html .= '<table class="table table-sm table-striped table-bordered mb-0" id="testsTable">';
    $html .= '<thead class="thead-light">';
    $html .= '<tr>';
    $html .= '<th style="white-space:nowrap">Test&nbsp;No</th>';
    $html .= '<th style="white-space:nowrap">Day &amp; Date</th>';
    $html .= '<th>Subject</th>';
    $html .= '<th style="white-space:nowrap">Total</th>';
    $html .= '<th>Syllabus</th>';
    $html .= '<th style="width:52px">Action</th>';
    $html .= '</tr></thead><tbody>';

    foreach ($rows as $r) {
        $dayDate = $r->test_date ? date('D d-M-Y', strtotime($r->test_date)) : '';
        $subj    = $r->subject_name ?? '-';

        $html .= '<tr data-test-id="'.(int)$r->test_id.'">';
        $html .= '<td class="text-center">'.(int)$r->test_no.'</td>';
        $html .= '<td>'.$dayDate.'</td>';
        $html .= '<td>'.esc($subj).'</td>';
        $html .= '<td class="text-center">'.(int)$r->total_marks.'</td>';
        $html .= '<td style="max-width:380px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">'.esc($r->syllabus).'</td>';
        $html .= '<td class="text-center">';
        $html .= '<button type="button" class="btn btn-sm btn-outline-danger js-del-test" title="Delete test"><i class="fas fa-trash-alt"></i></button>';
        $html .= '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div></div></div>';

    return $this->response->setBody($html);
}

public function deleteTest()
{
    if (function_exists('check_permission')) {
        check_permission('admin-add-test-result'); // or a more specific delete permission
    }
    if (!$this->request->is('post')) {
        return $this->response->setJSON(['success' => false, 'message' => 'POST required'])->setStatusCode(405);
    }

    $test_id = (int)$this->request->getPost('test_id');
    if (!$test_id) {
        return $this->response->setJSON(['success' => false, 'message' => 'test_id missing']);
    }

    $this->db->transStart();
    // Remove dependent marks first (if any)
    $this->db->table('test_results')->where('test_id', $test_id)->delete();
    // Delete the test
    $this->db->table('tests')->where('test_id', $test_id)->delete();
    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        return $this->response->setJSON(['success' => false, 'message' => 'Delete failed.']);
    }

    return $this->response->setJSON(['success' => true]);
}

public function get_students()
{
    if (function_exists('check_permission')) {
        check_permission('admin-add-test-result');
    }
    if (!$this->request->is('post')) {
        return $this->response->setStatusCode(405)
            ->setJSON(['success' => false, 'message' => 'POST required']);
    }

    $cls_sec_id    = (int) $this->request->getPost('cls_sec_id');
    $subject_id    = (int) $this->request->getPost('subject_id');
    $test_date     = (string) $this->request->getPost('test_date');
    $session_id    = (int) $this->request->getPost('session_id');
    $campus_id     = (int) $this->request->getPost('campus_id');

    // NEW: order_by (name | student_id)
    $orderBy = $this->request->getPost('order_by');
    $orderBy = in_array($orderBy, ['name', 'student_id'], true) ? $orderBy : 'name';

    if (!$cls_sec_id || !$subject_id || !$test_date) {
        return $this->response->setJSON([
            'success'  => false,
            'message'  => 'cls_sec_id, subject_id and test_date are required',
            'students' => [],
        ]);
    }

    // 1) sec_sub_id
    $secRow = $this->db->table('section_subjects')
        ->select('sec_sub_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('subject_id', $subject_id)
        ->where('status', 1)
        ->get()->getRow();
    $sec_sub_id = $secRow->sec_sub_id ?? null;

    // 2) fetch existing test
    $test = null;
    if ($sec_sub_id) {
        $test = $this->db->table('tests')
            ->select('test_id, total_marks AS marks_total, syllabus AS syllabus_text')
            ->where('sec_sub_id', $sec_sub_id)
            ->where('cls_sec_id', $cls_sec_id)
            ->where('test_date', $test_date)
            ->where('campus_id', $campus_id)
            ->where('session_id', $session_id)
            ->get()->getRow();
    }

    $test_id_db  = $test->test_id ?? null;
    $marks_db    = (isset($test->marks_total) && is_numeric($test->marks_total)) ? (int) $test->marks_total : null;
    $syll_db     = $test->syllabus_text ?? null;
    $test_remark = $test->test_remarks ?? null;

    // 3) students in class-section
    $builder = $this->db->table('student_class sc')
        ->select('s.student_id, s.first_name, s.last_name')
        ->join('students s', 's.student_id = sc.student_id')
        ->where('sc.cls_sec_id', $cls_sec_id)
        ->where('sc.status', 1)
        ->where('s.status', 1);

    if ($campus_id) {
        $builder->where('s.campus_id', $campus_id);
    }

    // NEW: apply ordering
    if ($orderBy === 'student_id') {
        $builder->orderBy('s.student_id', 'ASC');
    } else {
        // default: by first_name + last_name, then student_id as a tiebreaker
        $builder->orderBy('s.first_name', 'ASC')
                ->orderBy('s.last_name', 'ASC')
                ->orderBy('s.student_id', 'ASC');
    }

    $students = $builder->get()->getResultArray();

    // 4) existing marks + per-student remarks
    $marksMap   = [];
    $remarksMap = [];
    if ($test_id_db) {
        $results = $this->db->table('test_results')
            ->select('student_id, obtained_marks, remarks')
            ->where('test_id', $test_id_db)
            ->get()->getResultArray();

        foreach ($results as $r) {
            $sid = (int) $r['student_id'];
            $marksMap[$sid]   = $r['obtained_marks'] === null ? '' : (0 + $r['obtained_marks']);
            $remarksMap[$sid] = $r['remarks'] ?? '';
        }
    }

    // 5) shape response
    $outStudents = [];
    foreach ($students as $st) {
        $sid = (int) $st['student_id'];
        $outStudents[] = [
            'student_id' => $sid,
            'first_name' => $st['first_name'] ?? '',
            'last_name'  => $st['last_name'] ?? '',
            'marks'      => $marksMap[$sid] ?? '',
            'remarks'    => $remarksMap[$sid] ?? '',
        ];
    }

    return $this->response->setJSON([
        'success'       => true,
        'students'      => $outStudents,
        'sec_sub_id'    => $sec_sub_id,
        'test_id'       => $test_id_db,
        'test_marks'    => $marks_db,
        'syllabus'      => $syll_db,
        'test_remarks'  => $test_remark,
    ]);
}



}
