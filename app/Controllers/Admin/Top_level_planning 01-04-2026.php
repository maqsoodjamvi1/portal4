<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class Top_level_planning extends BaseController
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
        return view('admin/top_level_planning', $this->template_data);
    }

    public function data()
    {
        $response = new stdClass;
        $response->draw = $this->request->getPost('draw');
        $search = $this->request->getPost('search');
        $campus_id = $this->session->get('member_campusid');
        $keyword = !empty($search['value']) ? $search['value'] : '';

        $builder = $this->db->table('top_level_planning A');
        $builder->selectCount('A.tlp_id', 'ccount')->where('A.campus_id', $campus_id);
        if ($keyword) {
            $builder->where('A.class_name', $keyword);
        }
        $response->recordsTotal = $builder->get()->getRow()->ccount;

        $builder = $this->db->table('top_level_planning A');
        $builder->select('A.*')->where('A.campus_id', $campus_id);
        if ($keyword) {
            $builder->where('A.class_name', $keyword);
        }
        $builder->orderBy('A.tlp_id', 'desc')
                ->limit($this->request->getPost('length'), $this->request->getPost('start'));

        $results = $builder->get()->getResult();
        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $subjectinfo = $this->db->table('allsubject')->where('sid', $row->subject_id)->get()->getRow();
            $classinfo = $this->db->table('classes')->where('class_id', $row->class_id)->get()->getRow();
            $terms_session_info = $this->db->table('terms_session')->where('term_session_id', $row->term_session_id)->get()->getRow();
            $session_info = $this->db->table('academic_session')->where('session_id', $terms_session_info->session_id)->get()->getRow();
            $terms_info = $this->db->table('terms')->where('term_id', $terms_session_info->term_id)->get()->getRow();

            $response->data[] = [
                'id' => $row->tlp_id,
                'session_name' => $session_info->session_name,
                'term_name' => $terms_info->name,
                'class_name' => $classinfo->class_name,
                'subject' => $subjectinfo->subject_name,
                'objective' => $row->objective
            ];
        }

        return $this->response->setJSON($response);
    }

 public function add()
{
    helper('server_helper');
    check_permission('admin-add-top-level-planning');

    $session     = session();
    $today       = date('Y-m-d');
    $schoolinfo  = getSchoolInfo(); // expects ->system_id
    $fallbackSid = (int) ($session->get('member_sessionid') ?? 0);

    // Terms (unchanged)
    $this->template_data['terminfo'] = $this->db->table('terms')->get()->getResult();

    // Sections (teacher vs others) — unchanged
    $this->template_data['sectionsclassinfo'] = in_array(5, currentUserRoles(), true)
        ? teacherSubjectSections()
        : userClassSections();

    // All academic sessions for this system (ordered for nice dropdown)
    $academic_session = $this->db->table('academic_session')
        ->where('system_id', $schoolinfo->system_id)
        ->orderBy('start_date', 'ASC')
        ->get()
        ->getResult();
    $this->template_data['academic_session'] = $academic_session;

    // Find the current session by date; fallback to logged-in session id
    $currentSession = $this->db->table('academic_session')
        ->where('system_id', $schoolinfo->system_id)
        ->groupStart()
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
        ->groupEnd()
        ->orderBy('start_date', 'DESC')
        ->get()
        ->getRow();

    $current_session_id = (int) ($currentSession->session_id ?? $fallbackSid);

    // Hand the preselect to the view so the option can be marked "selected"
    $this->template_data['preselect'] = [
        'session_id' => $current_session_id,
    ];

    return view('admin/top_level_planning_edit', $this->template_data);
}


    public function edit()
    {
        check_permission('admin-edit-top-level-planning');
        $id = (int)$this->request->getGet('id');

        $this->template_data['sessionData'] = [
            'campusid' => $this->session->get('member_campusid'),
            'sessionid' => $this->session->get('member_sessionid')
        ];

        $this->template_data['info'] = $this->db->table('allsubject')->where('sid', $id)->get()->getRow();
        $this->template_data['classesinfo'] = $this->db->table('classes')->get()->getResult();
        $this->template_data['academic_session'] = $this->db->table('academic_session')->get()->getResult();

        return view('admin/top_level_planning_edit', $this->template_data);
    }

    

    public function save()
    {
        $tlp_ids = $this->request->getPost('tlp_id');
        $term_ids = $this->request->getPost('term_id');
        $syllabus = $this->request->getPost('syllabus');
        $subject_id = $this->request->getPost('subject_id');
        $campusid = $this->session->get('member_campusid');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        $classsectioninfo = $this->db->table('class_section')
            ->where('cls_sec_id', $this->request->getPost('section_id'))
            ->where('status', 1)
            ->get()->getRow();

        if (!$classsectioninfo) {
            return $this->response->setJSON(['error' => true, 'msg' => 'Invalid class section.']);
        }

        foreach ($term_ids as $index => $term_session_id) {
            $data = [
                'class_id' => $classsectioninfo->class_id,
                'term_session_id' => $term_session_id,
                'subject_id' => $subject_id,
                'objective' => $syllabus[$index],
                'audio_url' => $this->request->getPost('audio_url' . $index),
                'set_lock' => $this->request->getPost('lock_' . $term_session_id) ? 1 : 0,
                'updated_date' => $date,
                'user_id' => $user_id,
                'campus_id' => $campusid
            ];

            if (!empty($tlp_ids[$index])) {
                $this->db->table('top_level_planning')->where('tlp_id', $tlp_ids[$index])->update($data);
            } else {
                $data['created_date'] = $date;
                $this->db->table('top_level_planning')->insert($data);
            }
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Top Level Planning saved successfully.']);
    }

 public function selectSubjectsforTopLevelPlanning()
{
    // Add error handling
    try {
        $sessionid = $this->request->getPost('session_id');
        $section_id = $this->request->getPost('section_id');
        $subject_id = $this->request->getPost('subject_id');
        $campusid = $this->session->get('member_campusid');

        // Validate required parameters
        if (empty($sessionid) || empty($section_id) || empty($subject_id)) {
            return $this->response->setJSON([
                'error' => true, 
                'msg' => 'Missing required parameters'
            ]);
        }

        $classsectioninfo = $this->db->table('class_section')
            ->where(['cls_sec_id' => $section_id, 'status' => 1])
            ->get()
            ->getRow();

        if (!$classsectioninfo) {
            return $this->response->setJSON([
                'error' => true, 
                'msg' => 'Invalid class section'
            ]);
        }

        $terms_sessions = $this->db->table('terms_session')
            ->where('session_id', $sessionid)
            ->get()
            ->getResult();

        if (empty($terms_sessions)) {
            return $this->response->setJSON([
                'error' => true, 
                'msg' => 'No terms found for this session'
            ]);
        }

        $output = '<table class="table table-sm table-striped table-bordered">';
        $output .= '<thead class="bg-light">';
        $output .= '<tr><th width="20%">Terms</th><th width="70%">Syllabus</th><th width="10%">Lock</th></tr>';
        $output .= '</thead><tbody>';
        
        foreach ($terms_sessions as $index => $term) {
            $planning = $this->db->table('top_level_planning')
                ->where([
                    'subject_id' => $subject_id,
                    'class_id' => $classsectioninfo->class_id,
                    'term_session_id' => $term->term_session_id,
                    'campus_id' => $campusid
                ])
                ->get()
                ->getRow();

            $term_info = $this->db->table('terms')
                ->where('term_id', $term->term_id)
                ->get()
                ->getRow();
                
            $term_name = $term_info ? $term_info->name : 'Term ' . $term->term_id;
            $syllabus = $planning->objective ?? '';
            $lock = (isset($planning->set_lock) && $planning->set_lock == 1) ? "checked" : "";

            $output .= "<tr>";
            $output .= "<td>";
            $output .= "<input type='hidden' name='term_id[]' value='{$term->term_session_id}'>";
            $output .= "<input type='hidden' name='tlp_id[]' value='" . ($planning->tlp_id ?? '') . "'>";
            $output .= htmlspecialchars($term_name);
            $output .= "</td>";
            $output .= "<td>";
            $output .= "<textarea name='syllabus[]' class='form-control editor' rows='4'>{$syllabus}</textarea>";
            $output .= "</td>";
            $output .= "<td class='text-center'>";
            $output .= "<div class='custom-control custom-switch'>";
            $output .= "<input type='checkbox' class='custom-control-input' name='lock_{$term->term_session_id}' id='lock_{$term->term_session_id}' value='1' {$lock}>";
            $output .= "<label class='custom-control-label' for='lock_{$term->term_session_id}'></label>";
            $output .= "</div>";
            $output .= "</td>";
            $output .= "</tr>";
        }
        
        $output .= '</tbody></table>';

        return $this->response->setBody($output);
        
    } catch (\Exception $e) {
        log_message('error', 'Error in selectSubjectsforTopLevelPlanning: ' . $e->getMessage());
        return $this->response->setBody('<div class="alert alert-danger">Error loading editors: ' . $e->getMessage() . '</div>');
    }
}


public function getSubjectsBySection()
{
    $section_id = $this->request->getPost('section_id');
    
    if (empty($section_id)) {
        return $this->response->setBody('<option value=""></option>');
    }
    
    // Get class_id from section using class_section table
    $classSection = $this->db->table('class_section')
        ->where('cls_sec_id', $section_id)
        ->where('status', 1)
        ->get()
        ->getRow();
    
    if (!$classSection) {
        log_message('error', 'No class section found for id: ' . $section_id);
        return $this->response->setBody('<option value=""></option>');
    }
    
    // Get subjects from section_subjects table (this is the correct table)
    $subjects = $this->db->table('section_subjects')
        ->select('section_subjects.subject_id, allsubject.subject_name')
        ->join('allsubject', 'allsubject.sid = section_subjects.subject_id', 'left')
        ->where('section_subjects.cls_sec_id', $section_id)
        ->where('section_subjects.status', 1)
        ->groupBy('section_subjects.subject_id')
        ->get()
        ->getResult();
    
    // Build options HTML
    $options = '<option value=""></option>';
    if (!empty($subjects)) {
        foreach ($subjects as $subject) {
            $subject_id = $subject->subject_id;
            $subject_name = isset($subject->subject_name) ? $subject->subject_name : 'Subject ' . $subject_id;
            $options .= '<option value="' . htmlspecialchars($subject_id) . '">' 
                       . htmlspecialchars($subject_name) 
                       . '</option>';
        }
    } else {
        // If no subjects found, add a disabled option
        $options .= '<option value="" disabled>No subjects assigned to this section</option>';
        log_message('info', 'No subjects found for section_id: ' . $section_id);
    }
    
    return $this->response->setBody($options);
}

}
