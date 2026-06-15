<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class Classdiary extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'server_helper', 'role', 'school']);
        check_permission('admin-classdairy');
    }

    private function isTeacherUser(): bool
    {
        return isCurrentUserTeacher();
    }

    private function canTeacherEditDiary(int $clsSecId, int $secSubId): bool
    {
        if (! $this->isTeacherUser()) {
            return true;
        }

        $teacherId = (int) $this->session->get('member_userid');
        if ($teacherId <= 0 || $clsSecId <= 0 || $secSubId <= 0) {
            return false;
        }

        return $this->db->table('teacher_subjects')
            ->where('tid', $teacherId)
            ->where('cls_sec_id', $clsSecId)
            ->where('sec_sub_id', $secSubId)
            ->where('status', 1)
            ->countAllResults() > 0;
    }

    private function teacherDiaryDeniedResponse(string $message)
    {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => $message,
        ]);
    }

    public function index()
    {
        return view('admin/classdiary', $this->template_data);
    }

    public function data()
{
    $campusid      = (int) session('member_campusid');
    $sessionid     = (int) session('member_sessionid');
    $term_weeks_id = (int) $this->request->getPost('term_weeks_id');

    if (!$term_weeks_id) {
        return '<div class="col-lg-12 bg-danger text-center">Select Term Week</div>';
    }

    // 1) Get week range
    $tw = $this->db->table('term_weeks')
        ->select('start_date, end_date')
        ->where('term_weeks_id', $term_weeks_id)
        ->get()->getRowArray();

    if (!$tw) {
        return '<div class="col-lg-12 bg-danger text-center">Invalid Term Week</div>';
    }

    $start = $tw['start_date'];
    $end   = $tw['end_date'];

    // Build date list once
    $period = new \DatePeriod(
        new \DateTime($start),
        new \DateInterval('P1D'),
        (new \DateTime($end))->modify('+1 day')
    );
    $dates = [];
    $dayByDate = [];
    foreach ($period as $dt) {
        $d = $dt->format('Y-m-d');
        $dates[] = $d;
        $dayByDate[$d] = date('l', strtotime($d)); // Monday/Tuesday...
    }

    // 2) Sections (minimal columns)
    $sections = $this->db->table('class_section')
        ->select('cls_sec_id')
        ->where(['campus_id' => $campusid, 'status' => 1])
        ->get()->getResultArray();

    if (!$sections) {
        return view('admin/classdiary_result', ['data' => []]);
    }

    $clsSecIds = array_column($sections, 'cls_sec_id');

    // 3) Subjects per section (batch)
    $secSubjects = $this->db->table('section_subjects')
        ->select('sec_sub_id, cls_sec_id, subject_id')
        ->whereIn('cls_sec_id', $clsSecIds)
        ->where('status', 1)
        ->get()->getResultArray();

    $subjectsBySection = [];
    $subjectIds = [];
    $secSubIds  = [];
    foreach ($secSubjects as $row) {
        $subjectsBySection[$row['cls_sec_id']][] = $row;
        $subjectIds[$row['subject_id']] = true;
        $secSubIds[] = $row['sec_sub_id'];
    }

    // Subject names in one hit
    $subNames = [];
    if ($subjectIds) {
        $rows = $this->db->table('allsubject')
            ->select('sid, subject_name')
            ->whereIn('sid', array_keys($subjectIds))
            ->get()->getResultArray();
        foreach ($rows as $r) {
            $subNames[$r['sid']] = $r['subject_name'];
        }
    }

    // 4) Working days per section (campus-scoped, no timing types)
    $allowedDaysBySection = buildAllowedWorkingDaysMap(
        getSchoolTimingsForSections($clsSecIds, (int) $campusid)
    );

    // 5) All diary entries for the range + those subjects (batch)
    $diaries = [];
    if (!empty($secSubIds)) {
        $rows = $this->db->table('classdairy')
            ->select('sec_sub_id, date, detail')
            ->where('term_weeks_id', $term_weeks_id)
            ->whereIn('sec_sub_id', $secSubIds)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->get()->getResultArray();

        foreach ($rows as $r) {
            $diaries[$r['sec_sub_id']][$r['date']] = $r['detail'];
        }
    }

    // Session name once (same for all)
    $session_name = $this->db->table('academic_session')
        ->where('session_id', $sessionid)
        ->get()->getRow('session_name');

    // 6) Assemble output (no DB in inner loops)
    $data = [];
    foreach ($sections as $sec) {
        $clsSecId = (int) $sec['cls_sec_id'];
        $resultcard = [];

        foreach ($subjectsBySection[$clsSecId] ?? [] as $sub) {
            $subject_name = $subNames[$sub['subject_id']] ?? ('Subject ' . $sub['subject_id']);
            $sec_sub_id   = $sub['sec_sub_id'];

            foreach ($dates as $d) {
                $day = $dayByDate[$d];
                if (!isset($allowedDaysBySection[$clsSecId][$day])) {
                    continue; // no timings that day → skip
                }
                if (isset($diaries[$sec_sub_id][$d])) {
                    $resultcard[$subject_name][$d] = $diaries[$sec_sub_id][$d];
                }
            }
        }

        // If you only want sections that have at least one entry, keep this guard:
        // if (empty($resultcard)) continue;

        // If getClassSection() runs its own queries per call, consider replacing it
        // with a prejoin; keeping it here if you need its formatting.
        $sectioninfo = getClassSection($clsSecId);

        $data[] = [
            'class'        => $sectioninfo['sectionclassname'] ?? ('Section ' . $clsSecId),
            'cls_sec_id'   => $clsSecId,
            'session_name' => $session_name,
            'week_dates'   => $dates,
            'result'       => $resultcard,
        ];
    }

    return view('admin/classdiary_result', ['data' => $data]);
}



public function add()
{
    // check_permission('admin-add-classdairy');

    $campus_id = session('member_campusid');
    $sessionid = session('member_sessionid'); 
    $schoolinfo = getSchoolInfo();
    $today = date('Y-m-d');
    $isTeacher = $this->isTeacherUser();
    $teacher_id = (int) session('member_userid');

    $this->template_data['sessionData'] = [
        'campusid'  => $campus_id,
        'sessionid' => $sessionid,
    ];

    // ===== Load Terms of Current Session =====
    $terms_session_info = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, ts.term_id, ts.session_id, ts.start_date, ts.end_date, ts.status, t.name AS term_name')
        ->join('terms t', 't.term_id = ts.term_id')
        ->where('ts.session_id', $sessionid)
        ->orderBy('ts.start_date', 'ASC')
        ->get()->getResult();

    $this->template_data['terms_session_info'] = $terms_session_info;

    // ===== Detect Current Term =====
    $currentTerm = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date')
        ->where('ts.session_id', $sessionid)
        ->where('ts.start_date <=', $today)
        ->where('ts.end_date >=', $today)
        ->orderBy('ts.start_date', 'ASC')
        ->get()->getRow();

    if (!$currentTerm) {
        // Upcoming term
        $currentTerm = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date')
            ->where('ts.session_id', $sessionid)
            ->where('ts.start_date >', $today)
            ->orderBy('ts.start_date', 'ASC')
            ->get()->getRow();

        // Latest past term
        if (!$currentTerm) {
            $currentTerm = $this->db->table('terms_session ts')
                ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date')
                ->where('ts.session_id', $sessionid)
                ->where('ts.end_date <', $today)
                ->orderBy('end_date', 'DESC')
                ->get()->getRow();
        }
    }

    $default_term_session_id = $currentTerm->term_session_id ?? null;

    // ===== Load Weeks of Selected Term =====
    $term_weeks_info = [];
    $default_term_weeks_id = null;

    if ($default_term_session_id) {
        $term_weeks_info = $this->db->table('term_weeks')
            ->where('term_session_id', $default_term_session_id)
            ->orderBy('start_date', 'ASC')
            ->get()->getResult();

        // Detect current week
        $currentWeek = $this->db->table('term_weeks')
            ->where('term_session_id', $default_term_session_id)
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->get()->getRow();

        if (!$currentWeek) {
            // Upcoming week
            $currentWeek = $this->db->table('term_weeks')
                ->where('term_session_id', $default_term_session_id)
                ->where('start_date >', $today)
                ->orderBy('start_date', 'ASC')
                ->get()->getRow();

            // Latest past week
            if (!$currentWeek) {
                $currentWeek = $this->db->table('term_weeks')
                    ->where('term_session_id', $default_term_session_id)
                    ->where('end_date <', $today)
                    ->orderBy('end_date', 'DESC')
                    ->get()->getRow();
            }
        }

        $default_term_weeks_id = $currentWeek->term_weeks_id ?? null;
    }

    $this->template_data['term_weeks_info']         = $term_weeks_info;
    $this->template_data['default_term_session_id'] = $default_term_session_id;
    $this->template_data['default_term_weeks_id']   = $default_term_weeks_id;

    // ===== Load Sections Based on User Role =====
    $sectionsclassinfo = [];
    
    if ($isTeacher && $teacher_id) {
        // TEACHER: Get only sections where teacher teaches any subject
        $sectionsclassinfo = getTeacherSubjectSections();
    } else {
        // ADMIN/NON-TEACHER: Get all sections for the campus
        $sectionsclassinfo = getAllClassSection(); // Or use your existing function
    }
    
    $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

    // ===== Load Subjects Based on User Role =====
    if ($isTeacher && $teacher_id) {
        $sql = "SELECT DISTINCT
                    a.sid,
                    a.subject_name,
                    a.subject_short_name
                FROM teacher_subjects ts
                INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                INNER JOIN allsubject a ON a.sid = ss.subject_id
                WHERE ts.tid = ?
                    AND ts.status = 1
                    AND ss.status = 1
                    AND a.status = 1
                ORDER BY a.subject_name ASC";

        $this->template_data['subjectinfo'] = $this->db->query($sql, [$teacher_id])->getResult();
    } else {
        // ADMIN/NON-TEACHER: Get all subjects
        $this->template_data['subjectinfo'] = $this->db
            ->table('allsubject')
            ->where('status', 1)
            ->get()
            ->getResult();
    }
    
    $this->template_data['termsinfo'] = $this->db->table('terms')
        ->where('system_id', $schoolinfo->system_id)
        ->get()->getResult();

    $this->template_data['preselect_cls_sec_id'] = (int) ($this->request->getGet('cls_sec_id') ?? 0);
    $this->template_data['isTeacher'] = $isTeacher;

    return view('admin/classdiary_edit', $this->template_data);
}

public function selectSectionSubjectbySection()
{
    if ($this->request->getMethod(true) !== 'POST') {
        return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
    }

    // Accept either name from the view; both represent cls_sec_id
    $cls_sec_id = (int) ($this->request->getPost('cls_sec_id') ?: $this->request->getPost('section_id'));
    if ($cls_sec_id <= 0) {
        return $this->response->setBody('<option value="">Select Subject</option>');
    }

    $isTeacher = $this->isTeacherUser();
    $tid = (int) (session('member_userid') ?? 0);

    if ($isTeacher && $tid > 0) {
        $assigned = $this->db->table('teacher_subjects')
            ->where('tid', $tid)
            ->where('cls_sec_id', $cls_sec_id)
            ->where('status', 1)
            ->countAllResults();

        if ($assigned === 0) {
            return $this->response->setBody('<option value="">No assigned subjects</option>');
        }
    }

    $rows = [];
    
    try {
        if ($isTeacher && $tid > 0) {
            // For teachers: Get subjects they teach in this section
            $builder = $this->db->table('teacher_subjects ts')
                ->select('ss.sec_sub_id, ss.subject_id, a.subject_name, a.subject_short_name')
                ->join('section_subjects ss', 'ss.sec_sub_id = ts.sec_sub_id', 'inner')
                ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
                ->where('ss.cls_sec_id', $cls_sec_id)
                ->where('ss.status', 1)
                ->where('ts.status', 1)
                ->where('ts.tid', $tid)
                ->groupBy('ss.subject_id')  // Add GROUP BY to remove duplicates
                ->orderBy('a.subject_name', 'ASC');
            
            $query = $builder->get();
            
            if ($query && $query->getResult()) {
                $rows = $query->getResultArray();
            }
        } else {
            // For admin: Get all subjects in the section
            $builder = $this->db->table('section_subjects ss')
                ->select('ss.sec_sub_id, ss.subject_id, a.subject_name, a.subject_short_name')
                ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
                ->where('ss.cls_sec_id', $cls_sec_id)
                ->where('ss.status', 1)
                ->groupBy('ss.subject_id')  // Add GROUP BY to remove duplicates
                ->orderBy('a.subject_name', 'ASC');
            
            $query = $builder->get();
            
            if ($query && $query->getResult()) {
                $rows = $query->getResultArray();
            }
        }
    } catch (\Exception $e) {
        // Log error if needed
        log_message('error', 'selectSectionSubjectbySection error: ' . $e->getMessage());
        $rows = [];
    }

    // Build the <option> list
    $html = '<option value="">Select Subject</option>';
    
    if (!empty($rows)) {
        $displayedSubjects = []; // Track displayed subjects to avoid duplicates
        foreach ($rows as $r) {
            $name = trim((string) ($r['subject_name'] ?? ''));
            $subjectId = (int) ($r['subject_id'] ?? 0);
            
            // Skip if subject name is empty or already displayed
            if ($name === '' || in_array($subjectId, $displayedSubjects)) {
                continue;
            }
            
            $displayedSubjects[] = $subjectId;
            $html .= '<option value="' . (int) $r['sec_sub_id'] . '">' . esc($name) . '</option>';
        }
    }
    
    if (empty($rows) || count($displayedSubjects) === 0) {
        $html .= '<option value="">No subjects assigned</option>';
    }

    return $this->response->setBody($html);
}

    public function edit()
    {
        check_permission('admin-edit-classdairy');
        $id = intval($this->request->getGet('id'));

        $info = $this->db->table('classdairy')->where('did', $id)->get()->getRow();
        $this->template_data['info'] = $info;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $subjectinfo = $this->db->table('allsubject')->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        return view('admin/classdiary_edit', $this->template_data);
    }

public function bagpack()
{
    if (function_exists('check_permission')) {
        check_permission('admin-classdairy');
    }

    $campusId  = (int) session('member_campusid');
    $systemId  = (int) session('member_systemid');
    $sessionId = (int) session('member_sessionid');
    $today     = date('Y-m-d');

    // ---- Term Sessions ----
    $qb = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, ts.term_id, ts.session_id, ts.start_date, ts.end_date, ts.status, t.name AS term_name')
        ->join('terms t', 't.term_id = ts.term_id', 'left')
        ->where('ts.session_id', $sessionId)
        ->orderBy('ts.start_date', 'ASC');

    if ($systemId > 0) {
        $qb->where('ts.system_id', $systemId);
    }
    // If your table has campus_id and you want to filter by campus:
    // if ($campusId > 0) {
    //     $qb->where('ts.campus_id', $campusId);
    // }

    $terms_session_info = $qb->get()->getResult();

    // Try to detect the current running term for "today"
    $currentTerm = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date')
        ->where('ts.session_id', $sessionId)
        ->where('ts.start_date <=', $today)
        ->where('ts.end_date >=', $today)
        ->orderBy('ts.start_date', 'ASC')
        ->get()->getRow();

    // Decide which term_session_id to use as default
    $default_term_session_id = 0;
    if ($currentTerm) {
        $default_term_session_id = (int) $currentTerm->term_session_id;
    } elseif (! empty($terms_session_info)) {
        $default_term_session_id = (int) $terms_session_info[0]->term_session_id;
    }

    // ---- Term Weeks for the DEFAULT term session ----
    $term_weeks_info       = [];
    $default_term_weeks_id = 0;

    if ($default_term_session_id > 0) {
        $term_weeks_info = $this->db->table('term_weeks')
            ->select('term_weeks_id, week_name, start_date, end_date')
            ->where('term_session_id', $default_term_session_id)
            ->orderBy('start_date', 'ASC')
            ->get()
            ->getResult();

        if (! empty($term_weeks_info)) {
            $default_term_weeks_id = (int) $term_weeks_info[0]->term_weeks_id;
        }
    }

    // ---- Sections ----
    $sectionsclassinfo = $this->db->table('class_section cs')
        ->select([
            'cs.cls_sec_id',
            "CONCAT(c.class_name, ' - ', s.section_name) AS sectionclassname"
        ])
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections s', 's.section_id = cs.section_id', 'left')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('s.section_name', 'ASC')
        ->get()
        ->getResultArray();

    $data = [
        'terms_session_info'      => $terms_session_info,
        'default_term_session_id' => $default_term_session_id,
        'term_weeks_info'         => $term_weeks_info,
        'default_term_weeks_id'   => $default_term_weeks_id,
        'sectionsclassinfo'       => $sectionsclassinfo,
    ];

    return view('admin/classdiary_bagpack', $data);
}

public function term_weeks_options()
{
    $termSessionId = (int) $this->request->getPost('term_session_id');

    if (! $termSessionId) {
        return $this->response->setBody('<option value="">Select Term Week</option>');
    }

    $weeks = $this->db->table('term_weeks')
        ->select('term_weeks_id, week_name, start_date, end_date')
        ->where('term_session_id', $termSessionId)
        ->orderBy('start_date', 'ASC')
        ->get()
        ->getResult();

    if (empty($weeks)) {
        return $this->response->setBody('<option value="">No weeks found</option>');
    }

    $html = '';
    foreach ($weeks as $w) {
        $label = $w->week_name
               . ' (' . date('d M', strtotime($w->start_date))
               . ' - ' . date('d M', strtotime($w->end_date)) . ')';
        $html .= '<option value="'.$w->term_weeks_id.'">'.esc($label).'</option>';
    }

    return $this->response->setBody($html);
}




/**
 * AJAX: Return bag-pack HTML for selected week + class section
 * Uses classdairy.is_book, classdairy.is_notebook
 */

public function get_bagpack()
{
    $term_weeks_id = (int) $this->request->getPost('term_weeks');
    $cls_sec_id    = (int) $this->request->getPost('section_id');

    if (!$term_weeks_id || !$cls_sec_id) {
        return $this->response->setBody(
            '<div class="alert alert-warning">Select <b>Term Week</b> and <b>Class Section</b> first.</div>'
        );
    }

    // ---------- WEEK INFO ----------
    $week = $this->db->table('term_weeks')
        ->where('term_weeks_id', $term_weeks_id)
        ->get()->getRow();

    if (!$week) {
        return $this->response->setBody(
            '<div class="alert alert-danger">Invalid term week selected.</div>'
        );
    }

    // ---------- CLASS + SECTION ----------
    $cs = $this->db->table('class_section cs')
        ->select("cs.cls_sec_id, c.class_name, c.class_short_name, sec.section_name, sec.short_name as section_short_name")
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('cs.cls_sec_id', $cls_sec_id)
        ->get()->getRow();

    $gradeLabel = $cs ? trim(($cs->class_name ?? '') . ' - ' . ($cs->section_name ?? '')) : 'Selected Class';

    // =====================================================================
    // GET SCHOOL TIMINGS TO DETERMINE WORKING DAYS
    // =====================================================================

    $campusId = (int) session('member_campusid');
    $allowedMap = buildAllowedWorkingDaysMap(
        getSchoolTimingsForSections([(int) $cls_sec_id], $campusId)
    );

    $isWorkingDay = static function ($date) use ($allowedMap, $cls_sec_id): bool {
        $dayName = date('l', strtotime($date));

        return isWorkingDayForSection((int) $cls_sec_id, $dayName, $allowedMap);
    };
    
    $getNextWorkingDay = function($startDate) use ($isWorkingDay) {
        $date = new \DateTime($startDate);
        for ($i = 1; $i <= 7; $i++) {
            $checkDate = clone $date;
            $checkDate->modify("+{$i} days");
            if ($isWorkingDay($checkDate->format('Y-m-d'))) {
                return $checkDate;
            }
        }
        return null;
    };

    // =====================================================================
    // COLLECT WEEKLY DIARY - NOW INCLUDING ALL CONTENT TYPES
    // =====================================================================
    
    $queryEndDate = date('Y-m-d', strtotime($week->end_date . ' +7 days'));
    
    $rows = $this->db->table('classdairy cd')
        ->select("
            cd.did,
            cd.date,
            cd.detail        AS homework,
            cd.other_detail  AS classwork,
            cd.is_book,
            cd.is_notebook,
            cd.is_audio,
            cd.is_video,
            cd.is_picture,
            cd.audio_caption,
            cd.video_caption,
            cd.picture_caption,
            cd.quiz_id,
            cd.activities,
            cd.sec_sub_id,
            a.subject_name,
            a.subject_short_name
        ")
        ->join('section_subjects ss', 'ss.sec_sub_id = cd.sec_sub_id', 'left')
        ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
        ->where('cd.cls_sec_id', $cls_sec_id)
        ->where('cd.date >=', $week->start_date)
        ->where('cd.date <=', $queryEndDate)
        ->orderBy('cd.date', 'ASC')
        ->orderBy('a.subject_name', 'ASC')
        ->get()->getResult();

    $cleanHtml = static function (?string $html): string {
        if ($html === null) return '';
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    };

    // Group data by date
    $byDate = [];

    foreach ($rows as $r) {
        $date = $r->date;
        if (!isset($byDate[$date])) {
            $byDate[$date] = [
                'homework'      => [],
                'classwork'     => [],
                'bagpack'       => [],
                'audio_tasks'   => [],
                'video_tasks'   => [],
                'picture_tasks' => [],
                'quizzes'       => [],
                'activities'    => [],
                'diary'         => [],
            ];
        }

        $subject = trim($r->subject_name ?? $r->subject_short_name ?? 'Subject');
        if ($subject === '') $subject = 'Subject';

        $hwText = $cleanHtml($r->homework);
        $cwText = $cleanHtml($r->classwork);

        // Class Work
        if ($cwText !== '') {
            $byDate[$date]['classwork'][] = ['subject' => $subject, 'content' => $cwText];
        }
        
        // Home Work
        if ($hwText !== '') {
            $byDate[$date]['homework'][] = ['subject' => $subject, 'content' => $hwText];
        }

        // Bag Pack Items
        if ((int)$r->is_book === 1) {
            $byDate[$date]['bagpack'][] = $subject . ' Book';
        }
        if ((int)$r->is_notebook === 1) {
            $byDate[$date]['bagpack'][] = $subject . ' Notebook';
        }
        
        // Audio Tasks
        if ((int)$r->is_audio === 1) {
            $byDate[$date]['audio_tasks'][] = [
                'subject' => $subject,
                'caption' => $r->audio_caption ?? 'Listen to the assigned audio'
            ];
        }
        
        // Video Tasks
        if ((int)$r->is_video === 1) {
            $byDate[$date]['video_tasks'][] = [
                'subject' => $subject,
                'caption' => $r->video_caption ?? 'Watch the assigned video'
            ];
        }
        
        // Picture Tasks
        if ((int)$r->is_picture === 1) {
            $byDate[$date]['picture_tasks'][] = [
                'subject' => $subject,
                'caption' => $r->picture_caption ?? 'Complete the picture activity'
            ];
        }
        
        // Quiz
        if (!empty($r->quiz_id)) {
            $quizInfo = $this->db->table('quizzes')
                ->select('quiz_id, title, time_limit_sec, max_attempts, questions_count, instructions')
                ->where('quiz_id', $r->quiz_id)
                ->get()->getRow();
            
            if ($quizInfo) {
                $byDate[$date]['quizzes'][] = [
                    'subject' => $subject,
                    'quiz' => $quizInfo
                ];
            }
        }
        
        // Activities (JSON)
        if (!empty($r->activities)) {
            $activities = json_decode($r->activities, true);
            if (!empty($activities)) {
                $byDate[$date]['activities'][] = [
                    'subject' => $subject,
                    'activities' => $activities
                ];
            }
        }
        
        // Combined Diary entry for backward compatibility
        $diaryEntry = '';
        if ($cwText !== '') {
            $diaryEntry .= "CW: " . $cwText;
        }
        if ($hwText !== '') {
            if ($diaryEntry !== '') $diaryEntry .= "\n";
            $diaryEntry .= "HW: " . $hwText;
        }
        if ($diaryEntry !== '') {
            $byDate[$date]['diary'][] = ['subject' => $subject, 'content' => $diaryEntry];
        }
    }

    // =====================================================================
    // BUILD ORDERED WEEK DATES
    // =====================================================================
    $begin    = new \DateTime($week->start_date);
    $end      = (new \DateTime($week->end_date))->modify('+1 day');
    $period   = new \DatePeriod($begin, new \DateInterval('P1D'), $end);
    
    $allDates = [];
    foreach ($period as $dt) {
        $allDates[] = $dt->format('Y-m-d');
    }

    // =====================================================================
    // BUILD WHATSAPP STYLE TEXT OUTPUT
    // =====================================================================
    $plain = [];
    $plain[] = "📚 *WEEKLY PLANNER* 📚";
    $plain[] = "🏫 *Class:* " . $gradeLabel;
    $plain[] = "📅 *Week:* " . date('d M Y', strtotime($week->start_date)) . " - " . date('d M Y', strtotime($week->end_date));
    $plain[] = "";
    $plain[] = "━━━━━━━━━━━━";
    $plain[] = "";

    $hasAny = false;
    $dailyTexts = [];

    foreach ($period as $index => $dt) {
        $date = $dt->format('Y-m-d');
        $dayName = $dt->format('l');
        $formattedDate = $dt->format('d M Y');
        
        if (!$isWorkingDay($date)) {
            continue;
        }
        
        // Get all content for this date
        $classwork = $byDate[$date]['classwork'] ?? [];
        $homework = $byDate[$date]['homework'] ?? [];
        $audioTasks = $byDate[$date]['audio_tasks'] ?? [];
        $videoTasks = $byDate[$date]['video_tasks'] ?? [];
        $pictureTasks = $byDate[$date]['picture_tasks'] ?? [];
        $quizzes = $byDate[$date]['quizzes'] ?? [];
        $activities = $byDate[$date]['activities'] ?? [];
        
        $nextWorkingDate = $getNextWorkingDay($date);
        $bagpack = [];
        $nextWorkingDayName = '';
        $nextWorkingFormattedDate = '';
        
        if ($nextWorkingDate) {
            $bagpackForDate = $nextWorkingDate->format('Y-m-d');
            $bagpack = $byDate[$bagpackForDate]['bagpack'] ?? [];
            $nextWorkingDayName = $nextWorkingDate->format('l');
            $nextWorkingFormattedDate = $nextWorkingDate->format('d M Y');
        }
        
        $dayText = [];
        $dayText[] = "📚 *DAILY PLANNER* 📚";
        $dayText[] = "🏫 *Class:* " . $gradeLabel;
        $dayText[] = "📅 *Date:* " . $formattedDate . " (" . $dayName . ")";
        $dayText[] = "";
        $dayText[] = "━━━━━━━━━━━━━━━━";
        $dayText[] = "";
        
        $hasDayContent = false;
        
        // Class Work Section
        if (!empty($classwork)) {
            $dayText[] = "📚 *Class Work:*";
            foreach ($classwork as $item) {
                $dayText[] = "▪️ *" . $item['subject'] . "*: " . $item['content'];
            }
            $dayText[] = "";
            $hasDayContent = true;
        }
        
        // Home Work Section
        if (!empty($homework)) {
            $dayText[] = "📝 *Home Work:*";
            foreach ($homework as $item) {
                $dayText[] = "▪️ *" . $item['subject'] . "*: " . $item['content'];
            }
            $dayText[] = "";
            $hasDayContent = true;
        }
        
        // Audio Tasks Section
        if (!empty($audioTasks)) {
            $dayText[] = "🎧 *Audio Tasks:*";
            foreach ($audioTasks as $item) {
                $dayText[] = "▪️ *" . $item['subject'] . "*: " . $item['caption'];
            }
            $dayText[] = "";
            $hasDayContent = true;
        }
        
        // Video Tasks Section
        if (!empty($videoTasks)) {
            $dayText[] = "📹 *Video Tasks:*";
            foreach ($videoTasks as $item) {
                $dayText[] = "▪️ *" . $item['subject'] . "*: " . $item['caption'];
            }
            $dayText[] = "";
            $hasDayContent = true;
        }
        
        // Picture Tasks Section
        if (!empty($pictureTasks)) {
            $dayText[] = "🖼️ *Picture Tasks:*";
            foreach ($pictureTasks as $item) {
                $dayText[] = "▪️ *" . $item['subject'] . "*: " . $item['caption'];
            }
            $dayText[] = "";
            $hasDayContent = true;
        }
        
        // Quizzes Section
        if (!empty($quizzes)) {
            $dayText[] = "📋 *Quizzes:*";
            foreach ($quizzes as $item) {
                $quiz = $item['quiz'];
                $dayText[] = "▪️ *" . $item['subject'] . "*: " . $quiz->title;
                if ($quiz->time_limit_sec > 0) {
                    $dayText[] = "   ⏱️ Time: " . floor($quiz->time_limit_sec / 60) . " min";
                }
                $dayText[] = "   ❓ Questions: " . $quiz->questions_count;
                $dayText[] = "   🔄 Attempts: " . $quiz->max_attempts;
            }
            $dayText[] = "";
            $hasDayContent = true;
        }
        
        // Activities Section
        if (!empty($activities)) {
            $dayText[] = "🎯 *Classroom Activities:*";
            foreach ($activities as $item) {
                $dayText[] = "▪️ *" . $item['subject'] . "*:";
                foreach ($item['activities'] as $activity) {
                    $activityName = $activity['name'] ?? 'Activity';
                    $activityType = $activity['type'] ?? '';
                    $duration = $activity['duration_minutes'] ?? '';
                    $desc = $activity['description'] ?? '';
                    $dayText[] = "   📌 " . $activityName . ($activityType ? " (" . ucfirst(str_replace('-', ' ', $activityType)) . ")" : "");
                    if ($duration) $dayText[] = "      ⏱️ " . $duration . " min";
                    if ($desc) $dayText[] = "      📝 " . substr($desc, 0, 100);
                }
            }
            $dayText[] = "";
            $hasDayContent = true;
        }
        
        // Bag Pack Section
        if (!empty($bagpack)) {
            $dayText[] = "🎒 *Bring on " . $nextWorkingDayName . " (" . $nextWorkingFormattedDate . "):*";
            $itemNum = 1;
            sort($bagpack);
            $uniqueBagpack = array_unique($bagpack);
            foreach ($uniqueBagpack as $item) {
                $dayText[] = $itemNum++ . ". " . $item;
            }
            $dayText[] = $itemNum++ . ". Diary";
            $dayText[] = "";
        } else if ($nextWorkingDate) {
            $dayText[] = "🎒 *Bring on " . $nextWorkingDayName . " (" . $nextWorkingFormattedDate . "):* No items to bring";
            $dayText[] = "";
        }
        
        if ($hasDayContent) {
            $dailyTexts[$date] = implode("\n", $dayText);
        }
        
        $hasAny = true;
        
        // Weekly View Header
        $plain[] = "📌 *" . strtoupper($dayName) . "* (" . $formattedDate . ")";
        $plain[] = "";
        
        // Class Work
        if (!empty($classwork)) {
            $plain[] = "📚 *Class Work:*";
            foreach ($classwork as $item) {
                $plain[] = "▪️ *" . $item['subject'] . "*: " . $item['content'];
            }
            $plain[] = "";
        }
        
        // Home Work
        if (!empty($homework)) {
            $plain[] = "📝 *Home Work:*";
            foreach ($homework as $item) {
                $plain[] = "▪️ *" . $item['subject'] . "*: " . $item['content'];
            }
            $plain[] = "";
        }
        
        // Audio Tasks
        if (!empty($audioTasks)) {
            $plain[] = "🎧 *Audio Tasks:*";
            foreach ($audioTasks as $item) {
                $plain[] = "▪️ *" . $item['subject'] . "*: " . $item['caption'];
            }
            $plain[] = "";
        }
        
        // Video Tasks
        if (!empty($videoTasks)) {
            $plain[] = "📹 *Video Tasks:*";
            foreach ($videoTasks as $item) {
                $plain[] = "▪️ *" . $item['subject'] . "*: " . $item['caption'];
            }
            $plain[] = "";
        }
        
        // Picture Tasks
        if (!empty($pictureTasks)) {
            $plain[] = "🖼️ *Picture Tasks:*";
            foreach ($pictureTasks as $item) {
                $plain[] = "▪️ *" . $item['subject'] . "*: " . $item['caption'];
            }
            $plain[] = "";
        }
        
        // Quizzes
        if (!empty($quizzes)) {
            $plain[] = "📋 *Quizzes:*";
            foreach ($quizzes as $item) {
                $quiz = $item['quiz'];
                $plain[] = "▪️ *" . $item['subject'] . "*: " . $quiz->title;
                $plain[] = "   ⏱️ " . floor($quiz->time_limit_sec / 60) . " min | ❓ " . $quiz->questions_count . " Qs | 🔄 " . $quiz->max_attempts . " attempts";
            }
            $plain[] = "";
        }
        
        // Activities
        if (!empty($activities)) {
            $plain[] = "🎯 *Classroom Activities:*";
            foreach ($activities as $item) {
                $plain[] = "▪️ *" . $item['subject'] . "*:";
                foreach ($item['activities'] as $activity) {
                    $activityName = $activity['name'] ?? 'Activity';
                    $desc = $activity['description'] ?? '';
                    $plain[] = "   📌 " . $activityName;
                    if ($desc) $plain[] = "      " . substr($desc, 0, 80);
                }
            }
            $plain[] = "";
        }
        
        // Bag Pack
        if (!empty($bagpack)) {
            $plain[] = "🎒 *Bring on " . $nextWorkingDayName . " (" . $nextWorkingFormattedDate . "):*";
            $itemNum = 1;
            sort($bagpack);
            $uniqueBagpack = array_unique($bagpack);
            foreach ($uniqueBagpack as $item) {
                $plain[] = $itemNum++ . ". " . $item;
            }
            $plain[] = $itemNum++ . ". Diary";
            $plain[] = "";
        }
        
        $plain[] = "━━━━━━━━━━━━━";
        $plain[] = "";
    }
    
    if (!$hasAny) {
        return $this->response->setBody(
            '<div class="alert alert-info">No diary data found for selected filters.</div>'
        );
    }

    $plainText = implode("\n", $plain);

    // =====================================================================
    // RENDER HTML OUTPUT
    // =====================================================================
    ob_start();
    ?>
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h3 class="card-title mb-1">
            <i class="fas fa-calendar-alt"></i> Weekly Planner – <?= esc($gradeLabel) ?>
          </h3>
          <div class="text-muted small">
            📅 Week: <?= esc(date('d M, Y', strtotime($week->start_date))) ?> — <?= esc(date('d M, Y', strtotime($week->end_date))) ?>
          </div>
        </div>
        <div>
          <button type="button" id="bagpack-copy-btn" class="btn btn-sm btn-success">
            <i class="fab fa-whatsapp"></i> Copy All for WhatsApp
          </button>
        </div>
      </div>

      <textarea id="bagpack-copy-text" class="d-none"><?= esc($plainText) ?></textarea>

      <div class="card-body">
        <?php 
        $hasDisplay = false;
        foreach ($period as $index => $dt):
            $date = $dt->format('Y-m-d');
            if (!$isWorkingDay($date)) continue;
            
            $dayName = $dt->format('l');
            $formattedDate = $dt->format('d M Y');
            
            $classwork = $byDate[$date]['classwork'] ?? [];
            $homework = $byDate[$date]['homework'] ?? [];
            $audioTasks = $byDate[$date]['audio_tasks'] ?? [];
            $videoTasks = $byDate[$date]['video_tasks'] ?? [];
            $pictureTasks = $byDate[$date]['picture_tasks'] ?? [];
            $quizzes = $byDate[$date]['quizzes'] ?? [];
            $activities = $byDate[$date]['activities'] ?? [];
            
            $nextWorkingDate = $getNextWorkingDay($date);
            $bagpack = [];
            $nextWorkingDayName = '';
            $nextWorkingFormattedDate = '';
            
            if ($nextWorkingDate) {
                $bagpackForDate = $nextWorkingDate->format('Y-m-d');
                $bagpack = $byDate[$bagpackForDate]['bagpack'] ?? [];
                $nextWorkingDayName = $nextWorkingDate->format('l');
                $nextWorkingFormattedDate = $nextWorkingDate->format('d M Y');
            }
            
            if (empty($classwork) && empty($homework) && empty($audioTasks) && empty($videoTasks) && 
                empty($pictureTasks) && empty($quizzes) && empty($activities) && empty($bagpack)) {
                continue;
            }
            
            $hasDisplay = true;
            $dayId = 'day_' . str_replace('-', '_', $date);
        ?>
        
        <div class="mb-4 p-3 border rounded" style="background-color: #f9f9f9;">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <h4 class="text-primary mb-0">
              📌 <?= esc($dayName) ?>
              <small class="text-muted">(<?= esc($formattedDate) ?>)</small>
            </h4>
            <?php if (isset($dailyTexts[$date])): ?>
              <button type="button" class="btn btn-sm btn-success btn-copy-day" data-day-id="<?= $dayId ?>">
                <i class="fab fa-whatsapp"></i> Copy Day for WhatsApp
              </button>
              <textarea id="<?= $dayId ?>-text" class="d-none"><?= esc($dailyTexts[$date]) ?></textarea>
            <?php endif; ?>
          </div>
          
          <!-- Class Work -->
          <?php if (!empty($classwork)): ?>
            <div class="mb-3">
              <h5 class="text-success"><i class="fas fa-chalkboard-teacher"></i> Class Work:</h5>
              <?php foreach ($classwork as $item): ?>
                <div class="ms-3 mb-2">
                  <strong><?= esc($item['subject']) ?>:</strong><br>
                  <div class="ms-4"><?= nl2br(esc($item['content'])) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Home Work -->
          <?php if (!empty($homework)): ?>
            <div class="mb-3">
              <h5 class="text-primary"><i class="fas fa-home"></i> Home Work:</h5>
              <?php foreach ($homework as $item): ?>
                <div class="ms-3 mb-2">
                  <strong><?= esc($item['subject']) ?>:</strong><br>
                  <div class="ms-4"><?= nl2br(esc($item['content'])) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Audio Tasks -->
          <?php if (!empty($audioTasks)): ?>
            <div class="mb-3">
              <h5 class="text-info"><i class="fas fa-headphones"></i> Audio Tasks:</h5>
              <?php foreach ($audioTasks as $item): ?>
                <div class="ms-3 mb-2">
                  <strong><?= esc($item['subject']) ?>:</strong><br>
                  <div class="ms-4"><?= esc($item['caption']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Video Tasks -->
          <?php if (!empty($videoTasks)): ?>
            <div class="mb-3">
              <h5 class="text-danger"><i class="fas fa-video"></i> Video Tasks:</h5>
              <?php foreach ($videoTasks as $item): ?>
                <div class="ms-3 mb-2">
                  <strong><?= esc($item['subject']) ?>:</strong><br>
                  <div class="ms-4"><?= esc($item['caption']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Picture Tasks -->
          <?php if (!empty($pictureTasks)): ?>
            <div class="mb-3">
              <h5 class="text-warning"><i class="fas fa-image"></i> Picture Tasks:</h5>
              <?php foreach ($pictureTasks as $item): ?>
                <div class="ms-3 mb-2">
                  <strong><?= esc($item['subject']) ?>:</strong><br>
                  <div class="ms-4"><?= esc($item['caption']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Quizzes -->
          <?php if (!empty($quizzes)): ?>
            <div class="mb-3">
              <h5 class="text-purple" style="color: #6f42c1;"><i class="fas fa-question-circle"></i> Quizzes:</h5>
              <?php foreach ($quizzes as $item): 
                $quiz = $item['quiz'];
              ?>
                <div class="ms-3 mb-2 p-2 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                  <strong><?= esc($item['subject']) ?>: <?= esc($quiz->title) ?></strong><br>
                  <small>
                    <?php if ($quiz->time_limit_sec > 0): ?>⏱️ <?= floor($quiz->time_limit_sec / 60) ?> min | <?php endif; ?>
                    ❓ <?= $quiz->questions_count ?> Questions | 🔄 <?= $quiz->max_attempts ?> Attempts
                  </small>
                  <?php if (!empty($quiz->instructions)): ?>
                    <div class="mt-1 small">📌 <?= esc(substr($quiz->instructions, 0, 100)) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Activities -->
          <?php if (!empty($activities)): ?>
            <div class="mb-3">
              <h5 class="text-purple" style="color: #6f42c1;"><i class="fas fa-tasks"></i> Classroom Activities:</h5>
              <?php foreach ($activities as $item): ?>
                <div class="ms-3 mb-2">
                  <strong><?= esc($item['subject']) ?>:</strong>
                  <?php foreach ($item['activities'] as $activity): ?>
                    <div class="ms-4 mt-1 p-2 border rounded">
                      <strong>📌 <?= esc($activity['name'] ?? 'Activity') ?></strong>
                      <?php if (!empty($activity['type'])): ?>
                        <span class="badge text-bg-secondary ms-1"><?= esc($activity['type']) ?></span>
                      <?php endif; ?>
                      <?php if (!empty($activity['duration_minutes'])): ?>
                        <span class="badge text-bg-light ms-1">⏱️ <?= $activity['duration_minutes'] ?> min</span>
                      <?php endif; ?>
                      <?php if (!empty($activity['description'])): ?>
                        <div class="small text-muted mt-1"><?= esc(substr($activity['description'], 0, 150)) ?></div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          
          <!-- Bag Pack -->
          <?php if (!empty($bagpack)): ?>
            <div class="mt-3">
              <h5 class="text-warning"><i class="fas fa-bag-shopping"></i> Bring on <?= esc($nextWorkingDayName) ?> (<?= esc($nextWorkingFormattedDate) ?>):</h5>
              <ol class="ms-4">
                <?php 
                sort($bagpack);
                $uniqueBagpack = array_unique($bagpack);
                foreach ($uniqueBagpack as $item): ?>
                  <li><?= esc($item) ?></li>
                <?php endforeach; ?>
                <li>Diary</li>
              </ol>
            </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <?php if (!$hasDisplay): ?>
          <div class="alert alert-info">No data available for the selected filters.</div>
        <?php endif; ?>
      </div>
    </div>

    <script>
    (function(){
        function copyToClipboard(text, successMessage) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function(){
                    if (window.toastr) toastr.success(successMessage);
                    else alert('✓ ' + successMessage);
                }).catch(function(){
                    fallbackCopy(text, successMessage);
                });
            } else {
                fallbackCopy(text, successMessage);
            }
        }
        
        function fallbackCopy(text, successMessage) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                if (window.toastr) toastr.success(successMessage);
                else alert('✓ ' + successMessage);
            } catch(e){
                if (window.toastr) toastr.error('Copy failed');
                else alert('✗ Copy failed');
            }
            document.body.removeChild(textarea);
        }
        
        var weeklyBtn = document.getElementById('bagpack-copy-btn');
        var weeklyTa  = document.getElementById('bagpack-copy-text');
        if (weeklyBtn && weeklyTa) {
            weeklyBtn.addEventListener('click', function(){
                var text = weeklyTa.value || weeklyTa.textContent || '';
                if (!text.trim()) return;
                copyToClipboard(text, 'Weekly planner copied to clipboard!');
            });
        }
        
        var dayButtons = document.querySelectorAll('.btn-copy-day');
        dayButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var dayId = this.getAttribute('data-day-id');
                var textarea = document.getElementById(dayId + '-text');
                if (textarea) {
                    var text = textarea.value || textarea.textContent || '';
                    if (text.trim()) {
                        copyToClipboard(text, 'Daily planner copied to clipboard!');
                    }
                }
            });
        });
    })();
    </script>

    <?php
    return $this->response->setBody(ob_get_clean());
}
public function save()
{
    // Check if using new JSON format or old form data
    $diaryDataJson = $this->request->getPost('diary_data');
    
    if ($diaryDataJson) {
        // NEW FORMAT: JSON data from auto-save
        return $this->saveFromJson($diaryDataJson);
    }
    
    // OLD FORMAT: Fallback to existing method (for compatibility)
    return $this->saveFromFormData();
}

/**
 * Save from JSON format (used by auto-save)
 */
private function saveFromJson($diaryDataJson)
{
    $termWeeksId = (int) $this->request->getPost('term_weeks');
    $secSubId    = (int) $this->request->getPost('sec_sub_id');
    $sectionId   = (int) $this->request->getPost('section_id');
    
    if (!$termWeeksId || !$secSubId || !$sectionId) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Missing required fields (section/subject/week).'
        ]);
    }

    if (! $this->canTeacherEditDiary($sectionId, $secSubId)) {
        return $this->teacherDiaryDeniedResponse('You can only add diary entries for subjects assigned to you.');
    }
    
    $diaryData = json_decode($diaryDataJson, true);
    if (empty($diaryData)) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'No diary data received.'
        ]);
    }
    
    $user_id = (int) $this->session->get('member_userid');
    $today = date('Y-m-d H:i:s');
    $savedIds = [];
    $inserted = 0;
    $updated = 0;
    
    $this->db->transBegin();
    
    foreach ($diaryData as $data) {
        $dateVal = $data['date'] ?? '';
        if (empty($dateVal)) {
            continue;
        }
        
        $did = (int) ($data['did'] ?? 0);
        
        // Prepare activities JSON
        $activities = $data['activities'] ?? [];
        $hasActivities = !empty($activities) ? 1 : 0;
        $activitiesJson = !empty($activities) ? json_encode($activities) : null;
        
        $saveData = [
            'date'          => $dateVal,
            'cls_sec_id'    => $sectionId,
            'sec_sub_id'    => $secSubId,
            'term_weeks_id' => $termWeeksId,
            'detail'        => $data['detail'] ?? '',           // Homework
            'other_detail'  => $data['other_detail'] ?? '',     // Class Work
            'is_audio'      => (int) ($data['is_audio'] ?? 0),
            'is_video'      => (int) ($data['is_video'] ?? 0),
            'is_picture'    => (int) ($data['is_picture'] ?? 0),
            'is_book'       => (int) ($data['is_book'] ?? 1),
            'is_notebook'   => (int) ($data['is_notebook'] ?? 1),
            'audio_caption' => $data['audio_caption'] ?? '',
            'video_caption' => $data['video_caption'] ?? '',
            'picture_caption' => $data['picture_caption'] ?? '',
            'quiz_id'       => !empty($data['quiz_id']) ? (int) $data['quiz_id'] : null,
            'activities'    => $activitiesJson,
            'has_activities'=> $hasActivities,
            'user_id'       => $user_id,
            'updated_date'  => $today
        ];
        
        $ok = true;
        
        if ($did > 0) {
            // Update by did
            check_permission('admin-edit-classdairy');
            $ok = $this->db->table('classdairy')
                ->where('did', $did)
                ->update($saveData);
            if ($ok) {
                $updated++;
                $savedIds[] = $did;
            }
        } else {
            // Check if exists by unique keys
            $existing = $this->db->table('classdairy')
                ->select('did')
                ->where([
                    'term_weeks_id' => $termWeeksId,
                    'sec_sub_id'    => $secSubId,
                    'date'          => $dateVal,
                ])
                ->get()
                ->getRow();
            
            if ($existing) {
                // Update existing
                check_permission('admin-edit-classdairy');
                $ok = $this->db->table('classdairy')
                    ->where('did', (int)$existing->did)
                    ->update($saveData);
                if ($ok) {
                    $updated++;
                    $savedIds[] = (int)$existing->did;
                }
            } else {
                // Insert new
                check_permission('admin-add-classdairy');
                $saveData['created_date'] = $today;
                $ok = $this->db->table('classdairy')->insert($saveData);
                if ($ok) {
                    $inserted++;
                    $savedIds[] = $this->db->insertID();
                }
            }
        }
        
        $err = $this->db->error();
        if (!$ok || !empty($err['code'])) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Save failed: ' . ($err['message'] ?? 'Unknown error')
            ]);
        }
    }
    
    $this->db->transCommit();
    
    return $this->response->setJSON([
        'success' => true,
        'msg' => "Saved. Inserted: {$inserted}, Updated: {$updated}.",
        'saved_ids' => $savedIds
    ]);
}

/**
 * Save from traditional form data (existing method - kept for compatibility)
 */
private function saveFromFormData()
{
    // Base arrays
    $dates         = (array) $this->request->getPost('date');
    $details       = (array) $this->request->getPost('detail');
    $otherDetails  = (array) $this->request->getPost('other_detail');
    $ids           = $this->request->getPost('did');
    $ids           = is_array($ids) ? $ids : (($ids && (int)$ids > 0) ? [$ids] : []);
    
    // Toggle arrays
    $isBooks       = (array) $this->request->getPost('is_book');
    $isNotebooks   = (array) $this->request->getPost('is_notebook');
    $isAudios      = (array) $this->request->getPost('is_audio');
    $isVideos      = (array) $this->request->getPost('is_video');
    $isPictures    = (array) $this->request->getPost('is_picture');
    $isQuizzes     = (array) $this->request->getPost('is_quiz');
    $audioCaptions = (array) $this->request->getPost('audio_caption');
    $videoCaptions = (array) $this->request->getPost('video_caption');
    $pictureCaptions = (array) $this->request->getPost('picture_caption');
    $quizIds       = (array) $this->request->getPost('quiz_id');
    
    $cls_sec_id    = (int) $this->request->getPost('section_id');
    $sec_sub_id    = (int) $this->request->getPost('sec_sub_id');
    $term_weeks_id = (int) $this->request->getPost('term_weeks');
    
    if (!$cls_sec_id || !$sec_sub_id || !$term_weeks_id) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Missing required fields (section/subject/week).'
        ]);
    }

    if (! $this->canTeacherEditDiary($cls_sec_id, $sec_sub_id)) {
        return $this->teacherDiaryDeniedResponse('You can only add diary entries for subjects assigned to you.');
    }

    if (empty($dates)) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'No dates submitted.'
        ]);
    }
    
    $user_id = (int) $this->session->get('member_userid');
    $today = date('Y-m-d H:i:s');
    $inserted = 0;
    $updated = 0;
    
    $this->db->transBegin();
    
    for ($i = 0, $n = count($dates); $i < $n; $i++) {
        $dateVal = trim($dates[$i] ?? '');
        if ($dateVal === '') {
            continue;
        }
        
        $did = isset($ids[$i]) ? (int)$ids[$i] : 0;
        
        // Get toggle values (defaults)
        $isBook     = isset($isBooks[$i]) ? (int)$isBooks[$i] : 1;
        $isNotebook = isset($isNotebooks[$i]) ? (int)$isNotebooks[$i] : 1;
        $isAudio    = isset($isAudios[$i]) ? (int)$isAudios[$i] : 0;
        $isVideo    = isset($isVideos[$i]) ? (int)$isVideos[$i] : 0;
        $isPicture  = isset($isPictures[$i]) ? (int)$isPictures[$i] : 0;
        $isQuiz     = isset($isQuizzes[$i]) ? (int)$isQuizzes[$i] : 0;
        
        $audioCaption   = $audioCaptions[$i] ?? '';
        $videoCaption   = $videoCaptions[$i] ?? '';
        $pictureCaption = $pictureCaptions[$i] ?? '';
        $quizId         = isset($quizIds[$i]) && $quizIds[$i] ? (int)$quizIds[$i] : null;
        
        $base = [
            'date'          => $dateVal,
            'cls_sec_id'    => $cls_sec_id,
            'sec_sub_id'    => $sec_sub_id,
            'term_weeks_id' => $term_weeks_id,
            'detail'        => trim($details[$i] ?? ''),
            'other_detail'  => trim($otherDetails[$i] ?? ''),
            'is_book'       => $isBook,
            'is_notebook'   => $isNotebook,
            'is_audio'      => $isAudio,
            'is_video'      => $isVideo,
            'is_picture'    => $isPicture,
            'is_quiz'       => $isQuiz,
            'audio_caption' => $audioCaption,
            'video_caption' => $videoCaption,
            'picture_caption' => $pictureCaption,
            'quiz_id'       => $quizId,
            'user_id'       => $user_id,
            'updated_date'  => $today
        ];
        
        $ok = true;
        
        if ($did > 0) {
            check_permission('admin-edit-classdairy');
            $ok = $this->db->table('classdairy')
                ->where('did', $did)
                ->update($base);
            if ($ok) $updated++;
        } else {
            $existing = $this->db->table('classdairy')
                ->select('did')
                ->where([
                    'term_weeks_id' => $term_weeks_id,
                    'sec_sub_id'    => $sec_sub_id,
                    'date'          => $dateVal,
                ])
                ->get()
                ->getRow();
            
            if ($existing) {
                check_permission('admin-edit-classdairy');
                $ok = $this->db->table('classdairy')
                    ->where('did', (int)$existing->did)
                    ->update($base);
                if ($ok) $updated++;
            } else {
                check_permission('admin-add-classdairy');
                $base['created_date'] = $today;
                $ok = $this->db->table('classdairy')->insert($base);
                if ($ok) $inserted++;
            }
        }
        
        $err = $this->db->error();
        if (!$ok || !empty($err['code'])) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Save failed: ' . ($err['message'] ?? 'Unknown error')
            ]);
        }
    }
    
    $this->db->transCommit();
    
    return $this->response->setJSON([
        'success' => true,
        'msg' => "Class Diary saved. Inserted: {$inserted}, Updated: {$updated}."
    ]);
}


public function getQuizzesBySubject()
{
    $sec_sub_id = (int) $this->request->getPost('sec_sub_id');
    
    if (!$sec_sub_id) {
        return $this->response->setJSON([
            'success' => false,
            'quizzes' => []
        ]);
    }
    
    $quizzes = $this->db->table('quizzes')
        ->select('quiz_id, title')
        ->where('sec_sub_id', $sec_sub_id)
        ->where('is_published', 1)
        ->get()
        ->getResultArray();
    
    return $this->response->setJSON([
        'success' => true,
        'quizzes' => $quizzes
    ]);
}


public function get_classdiary()
{
    $term_weeks_id = (int) $this->request->getPost('term_weeks');
    $sec_sub_id    = (int) $this->request->getPost('sec_sub_id');
    $cls_sec_id    = (int) $this->request->getPost('section_id');

    if (!$sec_sub_id) {
        return $this->response->setBody('
        <div class="alert alert-warning d-flex align-items-center" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i>
          <div><strong>Select Class Section &amp; Subject</strong> to load the class diary.</div>
        </div>');
    }

    // If section_id not posted, infer from sec_sub_id
    if (!$cls_sec_id) {
        $sectionResult = $this->db->table('section_subjects')
            ->select('cls_sec_id')
            ->where('sec_sub_id', $sec_sub_id)
            ->get();
        
        if ($sectionResult && $sectionResult->getRow()) {
            $cls_sec_id = (int) $sectionResult->getRow('cls_sec_id');
        }
    }

    if ($cls_sec_id > 0 && ! $this->canTeacherEditDiary($cls_sec_id, $sec_sub_id)) {
        return $this->response->setBody('
        <div class="alert alert-danger d-flex align-items-center" role="alert">
          <i class="fas fa-ban me-2"></i>
          <div>You can only view diary entries for subjects assigned to you.</div>
        </div>');
    }

    $term_weeks = $this->db->table('term_weeks')
        ->where('term_weeks_id', $term_weeks_id)
        ->get()
        ->getRow();

    if (!$term_weeks) {
        return $this->response->setBody('
        <div class="alert alert-danger d-flex align-items-center" role="alert">
          <i class="fas fa-times-circle me-2"></i>
          <div>Invalid or missing <strong>Term Week</strong>. Please select a valid week.</div>
        </div>');
    }

    // Get subject name
    $subjectResult = $this->db->table('section_subjects ss')
        ->select('allsubject.subject_name')
        ->join('allsubject', 'allsubject.sid = ss.subject_id')
        ->where('ss.sec_sub_id', $sec_sub_id)
        ->get();
    
    $subject_name = 'General';
    if ($subjectResult && $subjectResult->getRow()) {
        $subject_name = $subjectResult->getRow()->subject_name ?? 'General';
    }

    // Get available quizzes for this subject
    $quizzes = $this->db->table('quizzes')
        ->select('quiz_id, title')
        ->where('sec_sub_id', $sec_sub_id)
        ->where('is_published', 1)
        ->get()
        ->getResultArray();

    // Get working weekdays for this section
    $campusId = (int) session('member_campusid');
    $allowedNums = getWorkingWeekdayNumbersForSection((int) $cls_sec_id, $campusId);
    $allowed = array_fill_keys($allowedNums, true);

    if ($allowed === []) {
        return $this->response->setBody('
        <div class="alert alert-info d-flex align-items-center" role="alert">
          <i class="far fa-calendar-times me-2"></i>
          <div>No working days configured for this section. Please set school timings first.</div>
        </div>');
    }

    // Build dates only for allowed weekdays
    $begin    = new \DateTime($term_weeks->start_date);
    $end      = (new \DateTime($term_weeks->end_date))->modify('+1 day');
    $interval = new \DateInterval('P1D');
    $period   = new \DatePeriod($begin, $interval, $end);

    $weekDays = [];

    foreach ($period as $value) {
        $date = $value->format('Y-m-d');
        $dowN = (int)$value->format('N');

        if (empty($allowed[$dowN])) {
            continue;
        }

        // FIXED: Added error handling for the query
        try {
            $query = $this->db->table('classdairy')
                ->select('did, detail, other_detail, is_audio, is_video, is_picture, is_book, is_notebook, audio_caption, video_caption, picture_caption, quiz_id, activities, has_activities')
                ->where('term_weeks_id', $term_weeks_id)
                ->where('sec_sub_id', $sec_sub_id)
                ->where('date', $date)
                ->get();

            if (!$query) {
                $classdairy = null;
            } else {
                $classdairy = $query->getRow();
            }
        } catch (\Exception $e) {
            // If activities column doesn't exist yet, fallback to without it
            $query = $this->db->table('classdairy')
                ->select('did, detail, other_detail, is_audio, is_video, is_picture, is_book, is_notebook, audio_caption, video_caption, picture_caption, quiz_id')
                ->where('term_weeks_id', $term_weeks_id)
                ->where('sec_sub_id', $sec_sub_id)
                ->where('date', $date)
                ->get();
            
            $classdairy = $query ? $query->getRow() : null;
        }

        $quizId = $classdairy->quiz_id ?? null;
        $activities = [];
        $hasActivities = 0;
        
        // Check if activities column exists and has data
        if ($classdairy && property_exists($classdairy, 'activities') && !empty($classdairy->activities)) {
            $activities = json_decode($classdairy->activities, true);
            if (!is_array($activities)) {
                $activities = [];
            }
            $hasActivities = !empty($activities) ? 1 : 0;
        } elseif ($classdairy && property_exists($classdairy, 'has_activities')) {
            $hasActivities = $classdairy->has_activities ?? 0;
        }
        
        $weekDays[] = [
            'date' => $date,
            'did' => $classdairy->did ?? 0,
            'detail' => $classdairy->detail ?? '',
            'other_detail' => $classdairy->other_detail ?? '',
            'is_audio' => $classdairy->is_audio ?? 0,
            'is_video' => $classdairy->is_video ?? 0,
            'is_picture' => $classdairy->is_picture ?? 0,
            'is_quiz' => !empty($quizId) ? 1 : 0,
            'is_book' => $classdairy->is_book ?? 1,
            'is_notebook' => $classdairy->is_notebook ?? 1,
            'audio_caption' => $classdairy->audio_caption ?? '',
            'video_caption' => $classdairy->video_caption ?? '',
            'picture_caption' => $classdairy->picture_caption ?? '',
            'quiz_id' => $quizId,
            'has_activities' => $hasActivities,
            'activities' => $activities
        ];
    }

    if (empty($weekDays)) {
        return $this->response->setBody('
        <div class="alert alert-info m-2">No days in this week match your section\'s working days. Please set school timings first.</div>');
    }

    // Return the new view
    return view('admin/classdiary_days', [
        'weekDays' => $weekDays,
        'subject_name' => $subject_name,
        'quizzes' => $quizzes,
        'term_weeks' => $term_weeks
    ]);
}

}
