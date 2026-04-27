<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Weekly_planning extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        
         helper(['form', 'url', 'server_helper']);
        check_permission('admin-weekly-planning');
    }

    public function index()
    {
        return view('admin/weekly_planning', $this->template_data);
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $schoolinfo = getSchoolInfo();
        $search = $this->request->getPost('search');
        $keyword = $search['value'] ?? '';

        // Count
        $builder = $this->db->table('weekly_planning A')->selectCount('A.wp_id', 'ccount');
        if ($keyword) $builder->where('A.short_title', $keyword);
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Data
        $builder = $this->db->table('weekly_planning A')->select('A.*');
        if ($keyword) $builder->where('A.short_title', $keyword);
        $builder->orderBy('A.wp_id', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];
        foreach ($results as $row) {
            $subjectinfo = $this->db->table('allsubject')->where('sid', $row->subject_id)->get()->getRow();
            $classinfo = $this->db->table('classes')->where('class_id', $row->class_id)->get()->getRow();
            $term_week_info = $this->db->table('term_weeks')->where('term_weeks_id', $row->term_week_id)->get()->getRow();
            $term_week_name = $term_week_info ? $term_week_info->week_name : '';

            $data = [
                'id' => $row->wp_id,
                'week_name' => $term_week_name,
                'subject_name' => $subjectinfo->subject_name ?? '',
                'class_name' => $classinfo->class_name ?? '',
                'objectives' => $row->objectives
            ];
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

 public function add()
    {
        check_permission('admin-add-weekly-planning');
        
        $campus_id = session('member_campusid');
        $sessionid = session('member_sessionid');
        $schoolinfo = getSchoolInfo();
        $today = date('Y-m-d');
        $userRoles = currentUserRoles();
        $isTeacher = in_array(5, $userRoles);
        
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
            ->get()
            ->getResult();
        
        $this->template_data['terms_session_info'] = $terms_session_info;
        
        // ===== Detect Current Term =====
        $currentTerm = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date')
            ->where('ts.session_id', $sessionid)
            ->where('ts.start_date <=', $today)
            ->where('ts.end_date >=', $today)
            ->orderBy('ts.start_date', 'ASC')
            ->get()
            ->getRow();
        
        if (!$currentTerm) {
            $currentTerm = $this->db->table('terms_session ts')
                ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date')
                ->where('ts.session_id', $sessionid)
                ->where('ts.start_date >', $today)
                ->orderBy('ts.start_date', 'ASC')
                ->get()
                ->getRow();
                
            if (!$currentTerm) {
                $currentTerm = $this->db->table('terms_session ts')
                    ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date')
                    ->where('ts.session_id', $sessionid)
                    ->where('ts.end_date <', $today)
                    ->orderBy('end_date', 'DESC')
                    ->get()
                    ->getRow();
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
                ->get()
                ->getResult();
            
            $currentWeek = $this->db->table('term_weeks')
                ->where('term_session_id', $default_term_session_id)
                ->where('start_date <=', $today)
                ->where('end_date >=', $today)
                ->get()
                ->getRow();
            
            if (!$currentWeek) {
                $currentWeek = $this->db->table('term_weeks')
                    ->where('term_session_id', $default_term_session_id)
                    ->where('start_date >', $today)
                    ->orderBy('start_date', 'ASC')
                    ->get()
                    ->getRow();
                    
                if (!$currentWeek) {
                    $currentWeek = $this->db->table('term_weeks')
                        ->where('term_session_id', $default_term_session_id)
                        ->where('end_date <', $today)
                        ->orderBy('end_date', 'DESC')
                        ->get()
                        ->getRow();
                }
            }
            
            $default_term_weeks_id = $currentWeek->term_weeks_id ?? null;
        }
        
        $this->template_data['term_weeks_info'] = $term_weeks_info;
        $this->template_data['default_term_session_id'] = $default_term_session_id;
        $this->template_data['default_term_weeks_id'] = $default_term_weeks_id;
        
        // ===== Load Sections based on user role =====
        // For teachers: only sections where they teach
        // For others: all sections
        if ($isTeacher) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = getAllClassSection();
        }
        $this->template_data['sectionsclassinfo'] = $sectionsclassinfo;
        
        // ===== Load All Subjects =====
        $subjectinfo = $this->db->table('allsubject')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)
            ->orderBy('subject_name', 'ASC')
            ->get()
            ->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;
        
        // ===== Pass role info to view =====
        $this->template_data['isTeacher'] = $isTeacher;
        $this->template_data['current_session_id'] = $sessionid;
        
        return view('admin/weekly_planning_edit', $this->template_data);
    }


public function getSubjectsBySection()
{
    $section_id = $this->request->getPost('section_id');
    $userRoles = currentUserRoles();
    $isTeacher = in_array(5, $userRoles);
    
    if (!$section_id) {
        return $this->response->setJSON(['html' => '<option value="">Invalid section</option>']);
    }
    
    $options = '<option value="">Select Subject</option>';
    
    if ($isTeacher) {
        // Teacher: Get subjects they teach
        $subjects = teacherSubjectsInSection($section_id);
    } else {
        // Admin/Director: Get all subjects
        $subjects = getSectionSubjects($section_id);
    }
    
    if (!empty($subjects)) {
        foreach ($subjects as $subject) {
            $subjectId = is_array($subject) ? $subject['subject_id'] : $subject->subject_id;
            $subjectName = is_array($subject) ? $subject['subject_name'] : $subject->subject_name;
            
            $options .= '<option value="' . $subjectId . '">' 
                      . esc($subjectName) . '</option>';
        }
    } else {
        $options .= '<option value="" disabled>' . ($isTeacher ? 'No subjects assigned' : 'No subjects found') . '</option>';
    }
    
    return $this->response->setJSON(['html' => $options]);
}


 public function getCurrentSessionTerm()
    {
        $current_date = date('Y-m-d');
        $campusid = $this->session->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        
        $currentSession = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->where('start_date <=', $current_date)
            ->where('end_date >=', $current_date)
            ->where('status', 1)
            ->orderBy('session_id', 'desc')
            ->get()->getRow();
        
        $currentTerm = null;
        if ($currentSession) {
            $currentTerm = $this->db->table('terms_session')
                ->where('system_id', $schoolinfo->system_id)
                ->where('session_id', $currentSession->session_id)
                ->where('start_date <=', $current_date)
                ->where('end_date >=', $current_date)
                ->where('status', 1)
                ->orderBy('term_session_id', 'desc')
                ->get()->getRow();
        }
        
        return $this->response->setJSON([
            'session_id' => $currentSession->session_id ?? null,
            'term_session_id' => $currentTerm->term_session_id ?? null,
            'session_name' => $currentSession->session_name ?? null,
            'term_name' => $currentTerm->term_name ?? null
        ]);
    }

  public function getTopLevelPlanning()
    {
        $term_session_id = $this->request->getPost('term_session_id');
        $section_id = $this->request->getPost('section_id');
        $subject_id = $this->request->getPost('subject_id');
        $selected_class_id = $this->request->getPost('selected_class_id');
        
        // Get class ID from section
        $classSection = getClassSection($section_id);
        $class_id = $classSection['class_id'] ?? null;
        
        // Check if top level planning exists for this specific class
        $topLevel = $this->db->table('top_level_planning')
            ->where('class_id', $class_id)
            ->where('subject_id', $subject_id)
            ->where('term_session_id', $term_session_id)
            ->where('campus_id', session('member_campusid'))
            ->get()
            ->getRow();
        
        $response = [
            'objective' => '',
            'found_in_class' => 'none'
        ];
        
        if ($topLevel) {
            $response['objective'] = $topLevel->objective;
            $response['found_in_class'] = 'same';
        } else {
            // Check if exists in another class
            $otherTopLevel = $this->db->table('top_level_planning')
                ->where('subject_id', $subject_id)
                ->where('term_session_id', $term_session_id)
                ->where('campus_id', session('member_campusid'))
                ->get()
                ->getRow();
            
            if ($otherTopLevel) {
                $sourceClass = $this->db->table('classes')
                    ->where('class_id', $otherTopLevel->class_id)
                    ->get()
                    ->getRow();
                
                $response['objective'] = $otherTopLevel->objective;
                $response['found_in_class'] = 'other';
                $response['source_class_name'] = $sourceClass->class_name ?? 'another class';
            }
        }
        
        return $this->response->setJSON($response);
    }
    

    public function edit()
    {
        check_permission('admin-edit-weekly-planning');
        $id = intval($this->request->getGet('id'));

        $info = $this->db->table('classdairy')->where('did', $id)->get()->getRow();
        $this->template_data['info'] = $info;

        $classesinfo = $this->db->table('classes')->get()->getResult();
        $this->template_data['classesinfo'] = $classesinfo;

        $subjectinfo = $this->db->table('allsubject')->get()->getResult();
        $this->template_data['subjectinfo'] = $subjectinfo;

        return view('admin/weekly_planning_edit', $this->template_data);
    }

   public function getWeeklyPlanning()
{
    $term_session_id = $this->request->getPost('term_session_id');
    $section_id = $this->request->getPost('section_id');
    $subject_id = $this->request->getPost('subject_id');
    $selected_class_id = $this->request->getPost('selected_class_id');
    $campus_id = session('member_campusid');
    
    $classSection = getClassSection($section_id);
    $class_id = $classSection['class_id'] ?? null;
    
    $weeks = $this->db->table('term_weeks')
        ->where('term_session_id', $term_session_id)
        ->orderBy('week_no', 'ASC')
        ->get()
        ->getResult();
    
    if (empty($weeks)) {
        return $this->response->setJSON([
            'html' => '<div class="alert alert-warning">No weeks found for this term.</div>'
        ]);
    }
    
    $existingPlans = $this->db->table('weekly_planning')
        ->whereIn('term_week_id', array_column($weeks, 'term_weeks_id'))
        ->where('class_id', $class_id)
        ->where('subject_id', $subject_id)
        ->where('campus_id', $campus_id)
        ->get()
        ->getResult();
    
    $existingMap = [];
    foreach ($existingPlans as $plan) {
        $existingMap[$plan->term_week_id] = $plan;
    }
    
    // Group weeks into rows of 3
    $html = '';
    $weekCount = count($weeks);
    
    for ($i = 0; $i < $weekCount; $i += 3) {
        $html .= '<div class="weekly-row">';
        
        // Display up to 3 weeks in this row
        for ($j = $i; $j < min($i + 3, $weekCount); $j++) {
            $week = $weeks[$j];
            $plan = $existingMap[$week->term_weeks_id] ?? null;
            $objectives = htmlspecialchars($plan->objectives ?? '');
            
            $weekName = !empty($week->week_name) ? esc($week->week_name) : 'Week ' . $week->week_no;
            $startDate = date('d M Y', strtotime($week->start_date));
            $endDate = date('d M Y', strtotime($week->end_date));
            
            $html .= '<div class="weekly-card">';
            $html .= '<div class="weekly-card-header">';
            $html .= '<h4>' . $weekName . '</h4>';
            $html .= '<div class="week-date">' . $startDate . ' - ' . $endDate . '</div>';
            $html .= '</div>';
            $html .= '<div class="weekly-card-body">';
            $html .= '<input type="hidden" name="plan[' . $week->term_weeks_id . '][term_week_id]" value="' . $week->term_weeks_id . '">';
            $html .= '<div class="form-group">';
            $html .= '<label><i class="fas fa-bullseye text-primary mr-1"></i> Objectives / Learning Outcomes</label>';
            $html .= '<textarea class="form-control weekly-editor" name="plan[' . $week->term_weeks_id . '][objectives]" rows="8">' . $objectives . '</textarea>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    
    return $this->response->setJSON(['html' => $html]);
}
    /**
     * Save weekly planning
     */
  public function save()
{
    try {
        $postData = $this->request->getPost();
        $campus_id = session('member_campusid');
        $user_id = session('member_userid');
        
        $class_id = $postData['selected_class_id'] ?? null;
        $subject_id = $postData['subject_id'] ?? null;
        
        if (!$class_id || !$subject_id) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Missing class or subject information']);
        }
        
        if (isset($postData['plan']) && is_array($postData['plan'])) {
            foreach ($postData['plan'] as $weekData) {
                $term_week_id = $weekData['term_week_id'] ?? null;
                $objectives = $weekData['objectives'] ?? '';
                
                if (!$term_week_id) continue;
                
                $existing = $this->db->table('weekly_planning')
                    ->where('term_week_id', $term_week_id)
                    ->where('class_id', $class_id)
                    ->where('subject_id', $subject_id)
                    ->where('campus_id', $campus_id)
                    ->get()
                    ->getRow();
                
                $data = [
                    'term_week_id' => $term_week_id,
                    'class_id' => $class_id,
                    'subject_id' => $subject_id,
                    'campus_id' => $campus_id,
                    'objectives' => $objectives,
                    'updated_date' => date('Y-m-d H:i:s'),
                    'user_id' => $user_id
                ];
                
                if ($existing) {
                    $this->db->table('weekly_planning')
                        ->where('wp_id', $existing->wp_id)
                        ->update($data);
                } else {
                    $data['created_date'] = date('Y-m-d H:i:s');
                    $this->db->table('weekly_planning')->insert($data);
                }
            }
        }
        
        return $this->response->setJSON(['success' => true, 'msg' => 'Weekly planning saved successfully']);
        
    } catch (\Exception $e) {
        log_message('error', 'Error saving weekly planning: ' . $e->getMessage());
        return $this->response->setJSON(['success' => false, 'msg' => 'Error saving: ' . $e->getMessage()]);
    }
}
    
}
