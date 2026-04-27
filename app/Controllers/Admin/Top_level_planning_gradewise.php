<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Top_level_planning_gradewise extends BaseController
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
    $req = $this->request;

    $campus_id = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');

    if (!$campus_id || !$sessionid) {
        return redirect()->to(site_url('admin/dashboard'))
                         ->with('error', 'Campus or Session is missing.');
    }

    $session_name = $this->getSessionName($sessionid);

    // -------- Filter inputs (POST) --------
    $selected_terms = array_values(array_unique(array_map(
        'intval', (array) ($req->getPost('terms') ?? [])
    )));
    $selected_classes = array_values(array_unique(array_map(
        'intval', (array) ($req->getPost('classes') ?? [])
    )));
    $selected_subjects = array_values(array_unique(array_map(
        'intval', (array) ($req->getPost('subjects') ?? [])
    )));

    // -------- Build filter options + defaults --------
    $filter_data = $this->getFilterOptions($campus_id, $sessionid);

    if (empty($selected_terms) && empty($selected_classes) && empty($selected_subjects)) {
        $selected_terms    = array_column($filter_data['all_terms']    ?? [], 'term_session_id');
        $selected_classes  = array_column($filter_data['all_classes']  ?? [], 'class_id');
        $selected_subjects = array_column($filter_data['all_subjects'] ?? [], 'sid');
    }

    // -------- Fetch grouped report data --------
    $grouped_data = $this->getFilteredPlanningData(
        $campus_id,
        $sessionid,
        $selected_terms,
        $selected_classes,
        $selected_subjects
    );
    $this->cleanAndOrganizeData($grouped_data);

    // -------- AJAX: return only the results fragment (no layout) --------
    $isAjax = $req->isAJAX() || (int) $req->getPost('is_ajax') === 1;
    if ($isAjax) {
        return $this->response->setBody(
            view('admin/top_level_planning_gradewise/_results_fragment', [
                 'grouped_data'      => $grouped_data,
    
            ])
        );
    }

    // -------- Full page render --------
    return view('admin/top_level_planning_gradewise/index', [
        'filter_data'      => $filter_data,
        'selected_filters' => [
            'terms'    => $selected_terms,
            'classes'  => $selected_classes,
            'subjects' => $selected_subjects,
        ],
        'grouped_data'     => $grouped_data,
        'session_name'     => $session_name,
    ]);
}

/**
 * Get filter options from database
 */
protected function getFilterOptions($campus_id, $sessionid)
{
    return [
        'all_terms' => $this->db->query("
            SELECT ts.term_session_id, t.name AS term_name 
            FROM terms_session ts
            JOIN terms t ON t.term_id = ts.term_id
            WHERE ts.session_id = ?
            ORDER BY ts.term_id ASC
        ", [$sessionid])->getResultArray(),

        'all_classes' => $this->db->query("
            SELECT DISTINCT c.class_id, c.class_name 
            FROM classes c
            JOIN class_section cs ON cs.class_id = c.class_id
            WHERE cs.campus_id = ? AND cs.status = 1
            ORDER BY c.class_id ASC
        ", [$campus_id])->getResultArray(),

        'all_subjects' => $this->db->query("
            SELECT DISTINCT s.sid, s.subject_name, s.subject_short_name 
            FROM section_subjects ss
            JOIN allsubject s ON s.sid = ss.subject_id
            JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
            WHERE cs.campus_id = ? AND cs.status = 1
            ORDER BY s.subject_name ASC
        ", [$campus_id])->getResultArray()
    ];
}

/**
 * Get filtered planning data from database
 */
protected function getFilteredPlanningData($campus_id, $sessionid, $terms, $classes, $subjects)
{
    // Build WHERE conditions
    $where = [
        "tlp.campus_id = $campus_id",
        "ts.session_id = $sessionid"
    ];

    if (!empty($terms)) {
        $where[] = "ts.term_session_id IN (".implode(',', array_map('intval', $terms)).")";
    }
    if (!empty($classes)) {
        $where[] = "tlp.class_id IN (".implode(',', array_map('intval', $classes)).")";
    }
    if (!empty($subjects)) {
        $where[] = "tlp.subject_id IN (".implode(',', array_map('intval', $subjects)).")";
    }

    $query = $this->db->query("
        SELECT 
            tlp.tlp_id,
            c.class_id,
            c.class_name,
            s.sid AS subject_id,
            s.subject_name,
            s.subject_short_name,
            tlp.objective,
            tlp.updated_date,
            tlp.created_date,
            ts.term_session_id,
            t.name AS term_name
        FROM top_level_planning tlp
        JOIN classes c ON c.class_id = tlp.class_id
        JOIN allsubject s ON s.sid = tlp.subject_id
        JOIN terms_session ts ON ts.term_session_id = tlp.term_session_id
        JOIN terms t ON t.term_id = ts.term_id
        WHERE ".implode(' AND ', $where)."
        ORDER BY c.class_id ASC, s.subject_name ASC, ts.term_id ASC
    ");

    return $this->groupQueryResults($query->getResult());
}

/**
 * Group query results into structured format
 */
protected function groupQueryResults($results)
{
    $grouped_data = [];
    $all_terms_in_data = [];

    foreach ($results as $row) {
        $key = $row->class_id;
        $cleaned_objective = trim(strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($row->objective))));
        
        if (!isset($grouped_data[$key])) {
            $grouped_data[$key] = [
                'class_id' => $row->class_id,
                'class_name' => $row->class_name,
                'subjects' => [],
                'terms' => []
            ];
        }
        
        if (!isset($grouped_data[$key]['subjects'][$row->subject_id])) {
            $grouped_data[$key]['subjects'][$row->subject_id] = [
                'subject_name' => $row->subject_name,
                'subject_short_name' => $row->subject_short_name,
                'objectives' => []
            ];
        }
        
        if (!empty($cleaned_objective)) {
            $grouped_data[$key]['subjects'][$row->subject_id]['objectives'][$row->term_session_id] = [
                'text' => $cleaned_objective,
                'tlp_id' => $row->tlp_id,
                'updated_date' => $row->updated_date,
                'created_date' => $row->created_date
            ];
        }
        
        if (!isset($grouped_data[$key]['terms'][$row->term_session_id])) {
            $grouped_data[$key]['terms'][$row->term_session_id] = $row->term_name;
            $all_terms_in_data[$row->term_session_id] = $row->term_name;
        }
    }

    return $grouped_data;
}

/**
 * Clean and organize the grouped data
 */
protected function cleanAndOrganizeData(&$grouped_data)
{
    foreach ($grouped_data as &$class_data) {
        $terms_with_data = [];
        
        // Find terms that have objectives
        foreach ($class_data['subjects'] as $subject) {
            $terms_with_data = array_merge($terms_with_data, array_keys($subject['objectives']));
        }
        $terms_with_data = array_unique($terms_with_data);
        
        // Remove terms without objectives
        foreach (array_diff(array_keys($class_data['terms']), $terms_with_data) as $term_to_remove) {
            unset($class_data['terms'][$term_to_remove]);
        }
        
        // Remove subjects without objectives
        $class_data['subjects'] = array_filter($class_data['subjects'], function($subject) {
            return !empty($subject['objectives']);
        });
    }
    unset($class_data); // Clear reference
    
    // Remove empty classes
    $grouped_data = array_filter($grouped_data, function($class_data) {
        return !empty($class_data['subjects']) && !empty($class_data['terms']);
    });
    
    ksort($grouped_data);
}

/**
 * Prepare template data for views
 */
protected function prepareTemplateData($filter_data, $selected_terms, $selected_classes, $selected_subjects, $grouped_data, $sessionid)
{
    $this->template_data = [
        'filter_data' => $filter_data,
        'selected_filters' => [
            'terms' => $selected_terms,
            'classes' => $selected_classes,
            'subjects' => $selected_subjects
        ],
        'grouped_data' => $grouped_data,
        'campus_name' => session('member_campusname'),
        'session_name' => $this->getSessionName($sessionid)
    ];
}

/**
 * Get session name from database
 */
protected function getSessionName($sessionid)
{
    $session = $this->db->table('academic_session')
        ->select('session_name')
        ->where('session_id', $sessionid)
        ->get()
        ->getRow();
        
    return $session->session_name ?? '';
}

    public function edit($tlp_id)
    {
        check_permission('admin-top-level-planning');
        $row = $this->db->table('top_level_planning tlp')
            ->select('tlp.*, c.class_id, s.sid as subject_id')
            ->join('classes c', 'c.class_id = tlp.class_id')
            ->join('allsubject s', 's.sid = tlp.subject_id')
            ->where('tlp.tlp_id', $tlp_id)
            ->get()->getRow();

        if (!$row) {
            session()->setFlashdata('error', 'Objective not found');
            return redirect()->to(base_url('top-level-planning-gradewise'));
        }
        $data['objective'] = $row;
        return view('admin/edit_objective_view', $data);
    }

    public function update($tlp_id)
    {
        check_permission('admin-top-level-planning');
        $validation = \Config\Services::validation();
        $validation->setRules([
            'objective' => 'required'
        ]);
        if (!$validation->withRequest($this->request)->run()) {
            session()->setFlashdata('error', implode('<br>', $validation->getErrors()));
            return redirect()->to(base_url('top-level-planning-gradewise/edit/'.$tlp_id));
        }
        $original = $this->db->table('top_level_planning')->where('tlp_id', $tlp_id)->get()->getRow();
        if (!$original) {
            session()->setFlashdata('error', 'Invalid record');
            return redirect()->to(base_url('top-level-planning-gradewise'));
        }

        $data = [
            'objective' => $this->request->getPost('objective'),
            'class_id' => $original->class_id,
            'subject_id' => $original->subject_id,
            'updated_date' => date('Y-m-d H:i:s'),
            'user_id' => $this->session->get('member_id')
        ];
        $this->db->table('top_level_planning')->where('tlp_id', $tlp_id)->update($data);

        session()->setFlashdata('success', 'Objective updated successfully');
        return redirect()->to(base_url('top-level-planning-gradewise'));
    }
}
