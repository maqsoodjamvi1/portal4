<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TopLevelPlanningSections extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-top-level-planning');
    }

    public function index()
    {
        return view('admin/top_level_planning', $this->template_data ?? []);
    }

    public function data()
    {
        $response = new \stdClass();
        $draw = $this->request->getPost('draw');
        $search = $this->request->getPost('search');
        $campus_id = $this->session->get('member_campusid');
        $keyword = '';
        if ($search) {
            $keyword = $search['value'];
        }

        // Count total records
        $builder = $this->db->table('top_level_planning A');
        $builder->selectCount('A.tlp_id', 'ccount');
        $builder->where('A.campus_id', $campus_id);
        if ($keyword) {
            $builder->where('A.class_name', $keyword);
        }
        $q = $builder->get()->getRow();

        $response->draw = $draw;
        $response->recordsTotal = $q->ccount;

        // Data query
        $builder = $this->db->table('top_level_planning A');
        $builder->select('A.*');
        $builder->where('A.campus_id', $campus_id);
        if ($keyword) {
            $builder->where('A.class_name', $keyword);
        }
        $builder->orderBy('A.tlp_id', 'DESC');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $subjectinfo = $this->db->table('allsubject')->where('sid', $row->subject_id)->get()->getRow();
            $classinfo = $this->db->table('classes')->where('class_id', $row->class_id)->get()->getRow();
            $terms_session_info = $this->db->table('terms_session')->where('term_session_id', $row->term_session_id)->get()->getRow();

            $session_info = $this->db->table('academic_session')->where('session_id', $terms_session_info->session_id)->get()->getRow();
            $terms_info = $this->db->table('terms')->where('term_id', $terms_session_info->term_id)->get()->getRow();

            $data = [
                'id' => $row->tlp_id,
                'session_name' => $session_info->session_name ?? '',
                'term_name' => $terms_info->name ?? '',
                'class_name' => $classinfo->class_name ?? '',
                'subject' => $subjectinfo->subject_name ?? '',
                'objective' => $row->objective,
            ];
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }


public function add()
{
    check_permission('admin-add-top-level-planning');

    $today      = date('Y-m-d');
    $sessionid  = (int) ($this->session->get('member_sessionid') ?? 0); // fallback if no "current by date"
    $schoolinfo = getSchoolInfo(); // ->system_id

    // 1) All academic sessions (for dropdown)
    $academic_session = $this->db->table('academic_session')
        ->where('system_id', $schoolinfo->system_id)
        ->orderBy('start_date', 'ASC')
        ->get()
        ->getResult();
    $this->template_data['academic_session'] = $academic_session;

    // Pick current session by date; fallback to logged-in session
    $currentSession = $this->db->table('academic_session')
        ->where('system_id', $schoolinfo->system_id)
        ->groupStart()
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
        ->groupEnd()
        ->orderBy('start_date', 'DESC')
        ->get()
        ->getRow();

    $current_session_id = (int) ($currentSession->session_id ?? $sessionid);

    // 2) Term sessions for that session (for dropdown)
    $termSessions = $this->db->table('terms_session')
        ->where('session_id', $current_session_id)
        ->orderBy('term_id', 'ASC')
        ->get()
        ->getResult();

    $termSessionInfo = [];
    foreach ($termSessions as $ts) {
        $t = $this->db->table('terms')
            ->select('name')
            ->where('term_id', $ts->term_id)
            ->get()
            ->getRow();

        $termSessionInfo[] = [
            'term_session_id' => (int) $ts->term_session_id,
            'term_name'       => $t->name ?? ('Term ' . $ts->term_id),
        ];
    }
    $this->template_data['termSessionInfo'] = $termSessionInfo;

    // Current term by date within this session; fallback to first term if none matches today
    $currentTerm = $this->db->table('terms_session')
        ->where('session_id', $current_session_id)
        ->groupStart()
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
        ->groupEnd()
        ->orderBy('start_date', 'DESC')
        ->get()
        ->getRow();

    $current_term_session_id = (int) ($currentTerm->term_session_id ?? ($termSessionInfo[0]['term_session_id'] ?? 0));

    // 3) Sections list (teacher vs others)
    $currentrole = currentUserRoles();
    if (in_array(5, $currentrole, true)) {
        $sectionsclassinfo = teacherSubjectSections();
    } else {
        $sectionsclassinfo = userClassSections();
    }
    $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;

    // 4) Preselects for the view – use these to mark selected options
    $this->template_data['preselect'] = [
        'session_id'      => $current_session_id,
        'term_session_id' => $current_term_session_id,
    ];

    return view('admin/top_level_planning_sections_edit', $this->template_data);
}



    public function edit()
    {
        check_permission('admin-edit-top-level-planning');
        $id = intval($this->request->getGet('id'));

        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $sessionData = [
            'campusid' => $campusid,
            'sessionid' => $sessionid
        ];
        $this->template_data['sessionData'] = $sessionData;

        $info = $this->db->table('allsubject')->where('sid', $id)->get()->getRow();
        $this->template_data['info'] = $info;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $academic_session = $this->db->table('academic_session')->get()->getResult();
        $this->template_data['academic_session'] = $academic_session;

        return view('admin/top_level_planning_sections_edit', $this->template_data);
    }


public function save()
{
    // Permissions — keep both since this method may insert and/or update in one go
    check_permission('admin-add-top-level-planning');
    check_permission('admin-edit-top-level-planning');

    // Inputs
    $term_session_id = (int)$this->request->getPost('term_session_id');
    $section_id      = (int)$this->request->getPost('section_id');
    $subject_ids     = (array)$this->request->getPost('subject_id');   // aligned with $syllabus
    $syllabus        = (array)$this->request->getPost('syllabus');      // aligned with subject_ids
    $tlp_ids         = (array)$this->request->getPost('tlp_id');        // may contain gaps/empties
    $synch           = (int)($this->request->getPost('synch') ?? 0);

    $campusid   = (int)$this->session->get('member_campusid');
    $user_id    = (int)$this->session->get('member_userid');
    $now        = date('Y-m-d H:i:s');
    $schoolinfo = getSchoolInfo();

    if ($term_session_id <= 0 || $section_id <= 0 || empty($subject_ids)) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Please select Session, Term and Section; at least one subject is required.'
        ]);
    }

    // Resolve class_id for the section
    $classsection = $this->db->table('class_section')
        ->where('cls_sec_id', $section_id)
        ->where('status', 1)
        ->get()->getRow();

    if (!$classsection) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Section not found or inactive.'
        ]);
    }
    $class_id = (int)$classsection->class_id;

    // Campus scope
    $campusIds = [$campusid];
    if ($synch === 1) {
        $campusIds = array_map(
            fn($c) => (int)$c->campus_id,
            $this->db->table('campus')
                     ->where('system_id', $schoolinfo->system_id)
                     ->get()->getResult()
        );
    }

    $inserted = 0;
    $updated  = 0;

    $this->db->transStart();

    foreach ($subject_ids as $i => $subject_id) {
        $subject_id = (int)$subject_id;
        if ($subject_id <= 0) { continue; }

        $objective = (string)($syllabus[$i] ?? '');
        // Checkbox name pattern: lock_{subject_id}
        $set_lock_current = (int)($this->request->getPost('lock_' . $subject_id) ? 1 : 0);
        // Optional legacy field; we no longer render/require it, but don’t break if posted:
        $subject_audio = (string)($this->request->getPost('audio_url' . $i) ?? '');
        $posted_tlpid  = isset($tlp_ids[$i]) && $tlp_ids[$i] !== '' ? (int)$tlp_ids[$i] : null;

        // Update payload (base). We’ll control set_lock per-campus below.
        $baseUpdate = [
            'objective'    => $objective,
            'updated_date' => $now,
            'user_id'      => $user_id,
        ];
        if ($subject_audio !== '') {
            $baseUpdate['audio_url'] = $subject_audio;
        }

        foreach ($campusIds as $cid) {

            $didUpdate = false;

            // 1) For the current campus we can trust posted tlp_id (if any).
            if ($cid === $campusid && $posted_tlpid) {
                $updateData = $baseUpdate + ['set_lock' => $set_lock_current];

                $this->db->table('top_level_planning')
                    ->where('tlp_id', $posted_tlpid)
                    ->where('campus_id', $cid)
                    ->update($updateData);

                // If row existed, affectedRows may be 0 when data is same; still count this as update
                if ($this->db->affectedRows() >= 0) {
                    $updated++;
                    $didUpdate = true;
                }
            }

            if ($didUpdate) {
                continue;
            }

            // 2) Composite key lookup (works for all campuses)
            $existing = $this->db->table('top_level_planning')
                ->select('tlp_id,set_lock')
                ->where([
                    'class_id'        => $class_id,
                    'subject_id'      => $subject_id,
                    'term_session_id' => $term_session_id,
                    'campus_id'       => $cid,
                ])->get()->getRow();

            if ($existing) {
                // Respect locks on OTHER campuses when syncing:
                if ($synch === 1 && $cid !== $campusid && (int)$existing->set_lock === 1) {
                    // Skip updating a locked row on another campus
                    continue;
                }

                // For current campus, allow set_lock change; for others keep their lock as-is.
                $updateData = $baseUpdate;
                if ($cid === $campusid) {
                    $updateData['set_lock'] = $set_lock_current;
                }

                $this->db->table('top_level_planning')
                    ->where('tlp_id', (int)$existing->tlp_id)
                    ->update($updateData);

                $updated++;
            } else {
                // 3) Insert new row
                $insertData = $baseUpdate + [
                    'class_id'        => $class_id,
                    'subject_id'      => $subject_id,
                    'term_session_id' => $term_session_id,
                    'campus_id'       => $cid,
                    'created_date'    => $now,
                    // Only current campus takes the UI lock; others default to unlocked
                    'set_lock'        => ($cid === $campusid ? $set_lock_current : 0),
                ];

                // When syncing, don’t insert into other campuses if you need to preserve
                // downstream locking; here we do insert (unlocked) which mirrors your subject flow.
                $this->db->table('top_level_planning')->insert($insertData);
                $inserted++;
            }
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
        'success' => true,
        'msg'     => "Saved successfully. Updated: {$updated}, Inserted: {$inserted}."
    ]);
}


    public function selectTermforTopLevelPlanning()
{
    $sessionid       = $this->request->getPost('session_id');
    $section_id      = $this->request->getPost('section_id');
    $term_session_id = $this->request->getPost('term_session_id');
    $campusid        = (int) $this->session->get('member_campusid');

    if (empty($section_id) || empty($term_session_id)) {
        return $this->response->setBody(
            '<div class="alert alert-warning mb-0">Please select Session, Term and Section.</div>'
        );
    }

    $classsectioninfo = $this->db->table('class_section')
        ->where('cls_sec_id', $section_id)
        ->where('status', 1)
        ->get()->getRow();

    if (!$classsectioninfo) {
        return $this->response->setBody(
            '<div class="alert alert-danger mb-0">Section not found or inactive.</div>'
        );
    }

    $section_subjects = $this->db->table('section_subjects')
        ->where('cls_sec_id', $section_id)
        ->where('status', 1)
        ->get()->getResult();

    if (!$section_subjects) {
        return $this->response->setBody(
            '<div class="alert alert-info mb-0">No subjects are mapped to the selected section.</div>'
        );
    }

    // Build clean table (Bootstrap classes added here for convenience)
    $html  = '<div class="table-responsive">';
    $html .= '<table class="table table-sm table-striped table-bordered align-middle mb-0">';
    $html .= '<thead class="bg-light"><tr>'
          .  '<th style="min-width:180px;">Subject</th>'
          .  '<th>Objective / Syllabus</th>'
          .  '<th style="width:90px;">Lock</th>'
          .  '</tr></thead><tbody>';

    foreach ($section_subjects as $row) {
        $subject_id = (int) $row->subject_id;

        // Subject name
        $sub = $this->db->table('allsubject')->where('sid', $subject_id)->get()->getRow();
        $subject_name = $sub->subject_name ?? ('Subject #'.$subject_id);

        // Existing TLP (this campus)
        $tlp = $this->db->table('top_level_planning')
            ->where('subject_id', $subject_id)
            ->where('class_id',   $classsectioninfo->class_id)
            ->where('term_session_id', $term_session_id)
            ->where('campus_id',  $campusid)
            ->get()->getRow();

        $tlp_id    = (int)($tlp->tlp_id ?? 0);
        $objective = (string)($tlp->objective ?? '');
        $locked    = (int)($tlp->set_lock ?? 0);

        $html .= '<tr data-subject-id="'. $subject_id .'">'
               .   '<td>'
               .     '<strong>'. htmlspecialchars($subject_name, ENT_QUOTES, 'UTF-8') .'</strong>'
               .     '<input type="hidden" name="subject_id[]" value="'. $subject_id .'">'
               .     '<input type="hidden" name="tlp_id[]" value="'. $tlp_id .'">'
               .   '</td>'
               .   '<td>'
               .     '<textarea name="syllabus[]" class="form-control editor" rows="6">'
               .       htmlspecialchars($objective, ENT_QUOTES, 'UTF-8')
               .     '</textarea>'
               .   '</td>'
               .   '<td class="text-center align-middle">'
               .     '<div class="custom-control custom-switch">'
               .       '<input type="checkbox" class="custom-control-input" id="lock_' . $subject_id . '" '
               .              'name="lock_' . $subject_id . '" value="1" '. ($locked ? 'checked' : '') .'>'
               .       '<label class="custom-control-label" for="lock_' . $subject_id . '"></label>'
               .     '</div>'
               .   '</td>'
               . '</tr>';
        // Note: No Video URL row, no iframe/thumbnail on purpose.
    }

    $html .= '</tbody></table></div>';

    return $this->response->setBody($html);
}


    public function delete()
    {
        check_permission('admin-del-top-level-planning');
        $id = intval($this->request->getGet('id'));

        $this->db->transBegin();
        $this->db->table('top_level_planning')->where('tlp_id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Top Level Planning Success']);
    }
}
// end file
