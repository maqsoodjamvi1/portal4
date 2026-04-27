<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TopLevelPlanningSubject extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-top-level-planning');
    }

    public function index()
    {
        return view('admin/top_level_planning', $this->template_data);
    }

    public function data()
    {
        $response = new \stdClass();
        $draw = $this->request->getPost('draw');
        $search = $this->request->getPost('search');
        $campus_id = $this->session->get('member_campusid');
        $keyword = $search ? $search['value'] : '';

        // Total records
        $builder = $this->db->table('top_level_planning A');
        $builder->selectCount('A.tlp_id', 'ccount');
        $builder->where('A.campus_id', $campus_id);
        if ($keyword) $builder->where('A.class_name', $keyword);
        $q = $builder->get()->getRow();

        $response->draw = $draw;
        $response->recordsTotal = $q->ccount;

        // Fetch data
        $builder = $this->db->table('top_level_planning A');
        $builder->select('A.*');
        $builder->where('A.campus_id', $campus_id);
        if ($keyword) $builder->where('A.class_name', $keyword);
        $builder->orderBy('A.tlp_id', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];
        foreach ($results as $row) {
            $subjectinfo = $this->db->table('allsubject')->where('sid', $row->subject_id)->get()->getRow();
            $classinfo = $this->db->table('classes')->where('class_id', $row->class_id)->get()->getRow();
            $terms_session_info = $this->db->table('terms_session')->where('term_session_id', $row->term_session_id)->get()->getRow();

            $session_info = $this->db->table('academic_session')->where('session_id', $terms_session_info->session_id ?? 0)->get()->getRow();
            $terms_info = $this->db->table('terms')->where('term_id', $terms_session_info->term_id ?? 0)->get()->getRow();

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
    $sessionid  = (int) $this->session->get('member_sessionid'); // fallback
    $campusid   = (int) $this->session->get('member_campusid');
    $schoolinfo = getSchoolInfo(); // expects ->system_id

    // ---- 1) Academic sessions (current & forward) ----
    $academic_session = $this->db->table('academic_session')
        ->where('system_id', $schoolinfo->system_id)
        ->orderBy('start_date', 'ASC')
        ->get()
        ->getResult();
    $this->template_data['academic_session'] = $academic_session;

    // Current session by date (fallback to logged-in session if no date match)
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

    // ---- 2) Term sessions for the chosen session ----
    $termSessionInfo = [];
    $termSessionsQB  = $this->db->table('terms_session')
        ->where('session_id', $current_session_id)
        ->orderBy('term_id', 'ASC');
    $termSessions = $termSessionsQB->get()->getResult();

    foreach ($termSessions as $ts) {
        $t = $this->db->table('terms')->where('term_id', $ts->term_id)->get()->getRow();
        $termSessionInfo[] = [
            'term_session_id' => (int) $ts->term_session_id,
            'term_name'       => $t->name ?? ('Term ' . $ts->term_id),
        ];
    }
    $this->template_data['termSessionInfo'] = $termSessionInfo;

    // Current term by date (assumes columns start_date/end_date on terms_session; adjust if your schema differs)
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

    // ---- 3) Subjects for this system (all) ----
    $subjectInfo = $this->db->table('allsubject')
        ->where('system_id', $schoolinfo->system_id)
        ->orderBy('subject_name', 'ASC')
        ->get()
        ->getResult();

    // Optional: restrict for teachers (role id 5). Uncomment if needed.
    /*
    $currentRoles = currentUserRoles();
    if (in_array(5, $currentRoles, true)) {
        $subjectInfo = $this->db->query("
            SELECT DISTINCT s.sid, s.subject_name, s.subject_short_name
            FROM section_subjects ss
            JOIN allsubject s ON s.sid = ss.subject_id
            JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
            WHERE ss.status = 1
              AND cs.campus_id = ?
              AND s.system_id = ?
            ORDER BY s.subject_name ASC
        ", [$campusid, $schoolinfo->system_id])->getResult();
    }
    */

    $this->template_data['subjectInfo'] = $subjectInfo;

    // ---- 4) Preselects for the view (auto-select current session & term) ----
    $this->template_data['preselect'] = [
        'session_id'      => $current_session_id,
        'term_session_id' => $current_term_session_id,
        'subject_id'      => (int)($this->request->getGet('subject_id') ?? 0),
    ];

    return view('admin/top_level_planning_subject_edit', $this->template_data);
}


public function autosave()
{
    check_permission('admin-top-level-planning'); // same gate

    $term_session_id = (int)$this->request->getPost('term_session_id');
    $subject_id      = (int)$this->request->getPost('subject_id');
    $class_id        = (int)$this->request->getPost('class_id');
    $objective       = (string)$this->request->getPost('objective'); // syllabus text/html
    $posted_tlpid    = $this->request->getPost('tlp_id');
    $synch           = (int)($this->request->getPost('synch') ?? 0);

    $campusid   = (int)$this->session->get('member_campusid');
    $user_id    = (int)$this->session->get('member_userid');
    $now        = date('Y-m-d H:i:s');
    $schoolinfo = getSchoolInfo();

    if (!$term_session_id || !$subject_id || !$class_id) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Missing term/subject/class.'
        ]);
    }

    // campuses to apply (sync to all campuses in the same system if requested)
    $campusIds = [$campusid];
    if ($synch === 1) {
        $campusIds = array_map(
            fn($c) => (int)$c->campus_id,
            $this->db->table('campus')->where('system_id', $schoolinfo->system_id)->get()->getResult()
        );
    }

    $this->db->transStart();

    $action = 'update';
    $newTlpId = null;

    foreach ($campusIds as $cid) {
        $baseUpdate = [
            'objective'    => $objective,
            'updated_date' => $now,
            'user_id'      => $user_id,
        ];

        $didUpdate = false;

        // If caller provided tlp_id and it belongs to this campus, try direct update
        if (!empty($posted_tlpid)) {
            $aff = $this->db->table('top_level_planning')
                ->where('tlp_id', (int)$posted_tlpid)
                ->where('campus_id', $cid)
                ->update($baseUpdate);

            if ($aff && $this->db->affectedRows() >= 0) {
                $didUpdate = true; // treat identical content as successful update
            }
        }

        if (!$didUpdate) {
            // Find existing by composite key
            $existing = $this->db->table('top_level_planning')
                ->select('tlp_id')
                ->where([
                    'class_id'        => $class_id,
                    'subject_id'      => $subject_id,
                    'term_session_id' => $term_session_id,
                    'campus_id'       => $cid,
                ])->get()->getRow();

            if ($existing) {
                $this->db->table('top_level_planning')
                    ->where('tlp_id', (int)$existing->tlp_id)
                    ->update($baseUpdate);
                if ($cid === $campusid) {
                    $newTlpId = (int)$existing->tlp_id;
                }
                $action = 'update';
            } else {
                // INSERT (no set_lock here anymore)
                $insertData = $baseUpdate + [
                    'class_id'        => $class_id,
                    'subject_id'      => $subject_id,
                    'term_session_id' => $term_session_id,
                    'campus_id'       => $cid,
                    'created_date'    => $now,
                ];
                $this->db->table('top_level_planning')->insert($insertData);
                if ($cid === $campusid) {
                    $newTlpId = (int)$this->db->insertID();
                }
                $action = 'insert';
            }
        }
    }

    $this->db->transComplete();

    if (!$this->db->transStatus()) {
        return $this->response->setJSON(['success' => false, 'message' => 'DB error.']);
    }

    return $this->response->setJSON([
        'success' => true,
        'action'  => $action,
        'tlp_id'  => $newTlpId, // may be null if only updated and no new insert
        'message' => $action === 'insert' ? 'Created.' : 'Saved.',
    ]);
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

        return view('admin/top_level_planning_edit', $this->template_data);
    }


public function save()
{
    // Basic inputs
    $term_session_id = (int)$this->request->getPost('term_session_id');
    $subject_id      = (int)$this->request->getPost('subject_id');
    $class_ids       = (array)$this->request->getPost('class_id');   // array, aligned with $syllabus
    $syllabus        = (array)$this->request->getPost('syllabus');   // array of html/text per class
    $tlp_id          = (array)($this->request->getPost('tlp_id') ?? []); // may be empty or partial
    $synch           = (int)($this->request->getPost('synch') ?? 0);

    $campusid   = (int)$this->session->get('member_campusid');
    $user_id    = (int)$this->session->get('member_userid');
    $now        = date('Y-m-d H:i:s');
    $schoolinfo = getSchoolInfo();

    if (empty($subject_id) || empty($term_session_id) || empty($class_ids)) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Please select Session/Term/Subject and at least one class.']);
    }

    // When syncing, we apply the same operation to every campus in this system.
    $campusIds = [$campusid];
    if ($synch === 1) {
        $campusIds = array_map(
            fn($c) => (int)$c->campus_id,
            $this->db->table('campus')->where('system_id', $schoolinfo->system_id)->get()->getResult()
        );
    }

    $inserted = 0; $updated = 0;

    $this->db->transStart();

    foreach ($class_ids as $i => $class_id) {
        $class_id = (int)$class_id;
        if ($class_id <= 0) { continue; }

        $objective     = (string)($syllabus[$i] ?? '');
        $subject_audio = (string)($this->request->getPost("audio_url{$i}") ?? '');
        $set_lock      = (int)($this->request->getPost("lock_{$class_id}") ?? 0);
        $posted_tlpid  = isset($tlp_id[$i]) && $tlp_id[$i] !== '' ? (int)$tlp_id[$i] : null;

        // Build the data chunks used in insert/update
        $baseUpdate = [
            'objective'    => $objective,
            'set_lock'     => $set_lock,
            'updated_date' => $now,
            'user_id'      => $user_id,
        ];
        if ($subject_audio !== '') {
            $baseUpdate['audio_url'] = $subject_audio;
        }

        foreach ($campusIds as $cid) {

            // 1) If a tlp_id was posted and it belongs to THIS campus, update directly.
            //    Otherwise fall back to composite-key lookup.
            $didUpdate = false;

            if ($posted_tlpid) {
                $aff = $this->db->table('top_level_planning')
                    ->where('tlp_id', $posted_tlpid)
                    ->where('campus_id', $cid)
                    ->update($baseUpdate);
                if ($aff && $this->db->affectedRows() >= 0) {
                    // If row existed, affectedRows may be 0 when data is identical; still count as update.
                    $didUpdate = ($this->db->affectedRows() >= 0);
                    if ($didUpdate) { $updated++; }
                }
            }

            if (!$didUpdate) {
                // 2) Try to find an existing row by UNIQUE composite key
                $exists = $this->db->table('top_level_planning')
                    ->select('tlp_id')
                    ->where([
                        'class_id'       => $class_id,
                        'subject_id'     => $subject_id,
                        'term_session_id'=> $term_session_id,
                        'campus_id'      => $cid,
                    ])->get()->getRow();

                if ($exists) {
                    // UPDATE by found id
                    $this->db->table('top_level_planning')
                        ->where('tlp_id', (int)$exists->tlp_id)
                        ->update($baseUpdate);
                    $updated++;
                } else {
                    // INSERT new
                    $insertData = $baseUpdate + [
                        'class_id'        => $class_id,
                        'subject_id'      => $subject_id,
                        'term_session_id' => $term_session_id,
                        'campus_id'       => $cid,
                        'created_date'    => $now,
                        // If not syncing to this campus explicitly, keep set_lock from UI;
                        // when syncing we generally start unlocked on other campuses:
                        'set_lock'        => ($cid === $campusid ? $set_lock : 0),
                    ];
                    $this->db->table('top_level_planning')->insert($insertData);
                    $inserted++;
                }
            }
        }
    }

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Database error while saving.']);
    }

    return $this->response->setJSON([
        'success' => true,
        'msg'     => "Saved successfully. Updated: {$updated}, Inserted: {$inserted}."
    ]);
}


public function selectSubjectsforTopLevelPlanning()
{
    $sessionid       = (int) $this->request->getPost('session_id');
    $term_session_id = (int) $this->request->getPost('term_session_id');
    $subject_id      = (int) $this->request->getPost('subject_id');
    $campusid        = (int) $this->session->get('member_campusid');

    $classsectioninfo = $this->db->table('class_section')
        ->where('status', 1)
        ->where('campus_id', $campusid)
        ->get()->getResult();

    $html  = '<table class="table table-sm table-striped table-bordered align-middle">';
    $html .= '<thead class="bg-light"><tr><th style="width:28%;">Class (Section)</th><th>Syllabus</th></tr></thead><tbody>';

    foreach ($classsectioninfo as $row) {
        // Only sections that have this subject
        $sectionSubjectInfo = $this->db->table('section_subjects')
            ->where('cls_sec_id', $row->cls_sec_id)
            ->where('subject_id', $subject_id)
            ->where('status', 1)
            ->get()->getRow();
        if (!$sectionSubjectInfo) continue;

        // Current TLP row per class (term+subject+campus)
        $tlp = $this->db->table('top_level_planning')
            ->where('subject_id', $subject_id)
            ->where('class_id', $row->class_id)
            ->where('term_session_id', $term_session_id)
            ->where('campus_id', $campusid)
            ->get()->getRow();

      $class   = $this->db->table('classes')->where('class_id', $row->class_id)->get()->getRow();
$section = $this->db->table('sections')->where('section_id', $row->section_id)->get()->getRow();

$className   = (string)($class->class_name   ?? '');
$sectionName = (string)($section->section_name ?? '');
$clsSecId    = (int)$row->cls_sec_id;
$classId     = (int)$row->class_id;

// What you print in the left column
$label = trim($className . ' - ' . $sectionName . ' (ID: ' . $classId . ')');

        $html .= '<tr>';

        // Class label + hidden class id
        $html .= '<td>';
        $html .= '<input type="hidden" name="class_id[]" value="' . esc($row->class_id) . '">';
        $html .= esc($label);
        $html .= '</td>';

        // Syllabus editor (textarea with data-* for autosave)
       // Class/Section labels
$class   = $this->db->table('classes')->where('class_id', $row->class_id)->get()->getRow();
$section = $this->db->table('sections')->where('section_id', $row->section_id)->get()->getRow();

$className   = (string)($class->class_name   ?? '');
$sectionName = (string)($section->section_name ?? '');
$clsSecId    = (int)$row->cls_sec_id;
$classId     = (int)$row->class_id;

// What you print in the left column
$label = trim($className . ' - ' . $sectionName . ' (ID: ' . $classId . ')');

$html .= '<tr>';

// Left cell: text + hidden class id you already had
$html .= '<td>';
$html .= '<input type="hidden" name="class_id[]" value="' . esc($classId) . '">';
$html .= esc($label);
$html .= '</td>';

// Syllabus editor: now include class/section names as data-attrs
$objective = $tlp->objective ?? '';
$tlp_id    = $tlp->tlp_id ?? '';

$html .= '<td>';
$html .= '<textarea '
      . 'class="form-control editor js-autosave" rows="4" '
      . 'data-class-id="'    . esc($classId)    . '" '
      . 'data-cls-sec-id="'  . esc($clsSecId)   . '" '
      . 'data-class-name="'  . esc($className)  . '" '
      . 'data-section-name="'. esc($sectionName). '" '
      . 'data-tlp-id="'      . esc($tlp_id)     . '">'
      . esc($objective)
      . '</textarea>';
$html .= '<div class="small mt-1 text-muted js-save-hint" style="display:none;"></div>';
$html .= '</td>';

$html .= '</tr>';

    }

    $html .= '</tbody></table>';
    return $this->response->setBody($html);
}

    
}
