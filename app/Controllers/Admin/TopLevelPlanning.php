<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TopLevelPlanning extends BaseController
{
    protected $db;
    protected $session;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'server_helper', 'role']);
    }

    private function isTeacherUser(): bool
    {
        return isCurrentUserTeacher();
    }

    private function teacherCanPlanClassSubject(int $classId, int $subjectId): bool
    {
        if (! $this->isTeacherUser()) {
            return true;
        }

        $teacherId = (int) session('member_userid');
        $campusId  = (int) session('member_campusid');
        if ($teacherId <= 0 || $classId <= 0 || $subjectId <= 0) {
            return false;
        }

        foreach (getTeacherSubjectsInClass($classId, $campusId, $teacherId) as $subject) {
            $sid = (int) ($subject->subject_id ?? $subject->sid ?? 0);
            if ($sid === $subjectId) {
                return true;
            }
        }

        return false;
    }



   public function view()
    {
        check_permission('admin-add-top-level-planning');
        
        $campus_id = session('member_campusid');
        $sessionid = session('member_sessionid');
        $isTeacher = $this->isTeacherUser();
        
        // Get all terms for the current session
        $terms = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date, t.name AS term_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.session_id', $sessionid)
            ->where('t.status', 1)
            ->orderBy('ts.start_date', 'ASC')
            ->get()
            ->getResult();
        
        // Get all classes
        $classes = $this->db->table('classes')
            ->where('status', 1)
            ->orderBy('class_id', 'ASC')
            ->get()
            ->getResult();
        
        // Get all subjects
        $schoolinfo = getSchoolInfo();
        $subjects = $this->db->table('allsubject')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)
            ->orderBy('subject_name', 'ASC')
            ->get()
            ->getResult();
        
        $data = [
            'title' => 'View Top Level Planning',
            'terms' => $terms,
            'classes' => $classes,
            'subjects' => $subjects,
            'isTeacher' => $isTeacher,
            'current_session_id' => $sessionid,
            'campus_id' => $campus_id
        ];
        
        return view('admin/top_level_planning/view', $data);
    }

    public function getViewData()
    {
        $view_type = $this->request->getPost('view_type');
        $term_ids = $this->request->getPost('term_ids');
        $campus_id = session('member_campusid');
        $session_id = session('member_sessionid');
        
        $html = '';
        
        if ($view_type == 'term_wise') {
            $html = $this->getTermWiseView($term_ids, $campus_id, $session_id);
        } elseif ($view_type == 'class_wise') {
            $html = $this->getClassWiseView($term_ids, $campus_id, $session_id);
        } elseif ($view_type == 'subject_wise') {
            $html = $this->getSubjectWiseView($term_ids, $campus_id, $session_id);
        }
        
        return $this->response->setJSON(['html' => $html]);
    }

    /**
     * Subject Wise View - Table format with 1 subject per row
     */
 

 /**
 * Subject Wise View - Table format with 1 subject per row
 * Consistent column widths across all subjects
 */
private function getSubjectWiseView($term_ids, $campus_id, $session_id, $forPrint = false)
{
    if (empty($term_ids)) {
        return '<div class="alert alert-warning">Please select at least one term</div>';
    }
    
    if (!is_array($term_ids)) {
        $term_ids = [$term_ids];
    }
    
    // Get terms info
    $termsInfo = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
        ->join('terms t', 't.term_id = ts.term_id')
        ->whereIn('ts.term_session_id', $term_ids)
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResult();
    
    if (empty($termsInfo)) {
        return '<div class="alert alert-warning">Selected terms not found</div>';
    }
    
    // Get planning for selected terms
    $planning = $this->db->table('top_level_planning tlp')
        ->select('tlp.*, c.class_name, c.class_short_name, a.subject_name, ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
        ->join('classes c', 'c.class_id = tlp.class_id')
        ->join('allsubject a', 'a.sid = tlp.subject_id')
        ->join('terms_session ts', 'ts.term_session_id = tlp.term_session_id')
        ->join('terms t', 't.term_id = ts.term_id')
        ->whereIn('tlp.term_session_id', $term_ids)
        ->where('tlp.campus_id', $campus_id)
        ->where('tlp.objective IS NOT NULL')
        ->where('tlp.objective !=', '')
        ->orderBy('a.subject_name', 'ASC')
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResult();
    
    if (empty($planning)) {
        return '<div class="alert alert-warning">No top level planning found with objectives for selected terms.</div>';
    }
    
    // Group by subject, then by class, then by term
    $groupedBySubject = [];
    foreach ($planning as $item) {
        $subjectId = $item->subject_id;
        $subjectName = $item->subject_name;
        $classId = $item->class_id;
        $className = $item->class_short_name ?? $item->class_name;
        $termId = $item->term_session_id;
        $termName = $item->term_name;
        
        if (!isset($groupedBySubject[$subjectId])) {
            $groupedBySubject[$subjectId] = [
                'subject_name' => $subjectName,
                'classes' => []
            ];
        }
        
        if (!isset($groupedBySubject[$subjectId]['classes'][$classId])) {
            $groupedBySubject[$subjectId]['classes'][$classId] = [
                'class_name' => $className,
                'terms' => []
            ];
        }
        
        // Store objective for this term - strip HTML tags
        $groupedBySubject[$subjectId]['classes'][$classId]['terms'][$termId] = [
            'term_name' => $termName,
            'objective' => strip_tags($item->objective)
        ];
    }
    
    // Calculate column width based on number of terms
    $termCount = count($termsInfo);
    $classColumnWidth = '120px'; // Reduced width for short class names (max 7-8 chars)
    $termColumnWidth = $termCount > 0 ? 'calc((100% - ' . $classColumnWidth . ')/' . $termCount . ')' : 'auto';
    
    // Build HTML with Table format
    $html = $forPrint ? '' : '<div class="subject-wise-view">';
    
    // Terms summary
    $html .= '<div class="report-header">';
    $html .= '<div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center;">';
    $html .= '<div><i class="fas fa-calendar-alt" style="color:#2c5282; margin-right:8px;"></i><strong>Selected Terms:</strong></div>';
    $html .= '<div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:5px;">';
    foreach ($termsInfo as $term) {
        $html .= '<span class="term-badge-summary">' . esc($term->term_name) . ' (' . date('d M Y', strtotime($term->start_date)) . ' - ' . date('d M Y', strtotime($term->end_date)) . ')</span>';
    }
    $html .= '</div></div></div>';
    
    // Subjects Container - 1 subject per row (full width)
    $html .= '<div class="subjects-container">';
    
    foreach ($groupedBySubject as $subjectId => $subjectData) {
        $bodyId = 'subjectBody_' . $subjectId;
        
        $html .= '<div class="subject-main-card" data-card-id="' . $subjectId . '">';
        $html .= '<div class="subject-main-card-header" data-bs-target="' . $bodyId . '">';
        $html .= '<span><i class="fas fa-book-open" style="margin-right: 10px;"></i> ' . esc($subjectData['subject_name']) . '</span>';
        $html .= '<span style="display:flex; align-items:center; gap:10px;">';
        $html .= '<span class="class-count-badge">' . count($subjectData['classes']) . ' Class(es)</span>';
        $html .= '<i class="fas fa-chevron-down expand-icon"></i>';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '<div class="subject-main-card-body" id="' . $bodyId . '">';
        
        // Build Table with fixed column widths
        $html .= '<div class="table-responsive">';
        $html .= '<table class="planning-table" style="table-layout: fixed; width: 100%;">';
        
        // Table Header with consistent widths
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th style="width: ' . $classColumnWidth . ';">Class</th>';
        foreach ($termsInfo as $term) {
            $html .= '<th style="width: ' . $termColumnWidth . ';">' . esc($term->term_name) . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';
        
        // Table Body
        $html .= '<tbody>';
        
        foreach ($subjectData['classes'] as $classId => $classData) {
            $html .= '<tr>';
            $html .= '<td class="class-name-cell"><strong><i class="fas fa-graduation-cap"></i> ' . esc($classData['class_name']) . '</strong></td>';
            
            foreach ($termsInfo as $term) {
                $termId = $term->term_session_id;
                $objective = '';
                
                if (isset($classData['terms'][$termId])) {
                    $objective = nl2br(esc($classData['terms'][$termId]['objective']));
                } else {
                    $objective = '<span class="text-muted"><em>No objective set</em></span>';
                }
                
                $html .= '<td class="objective-cell">' . $objective . '</td>';
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= $forPrint ? '' : '</div>';
    
    return $html;
}

/**
 * Class Wise View - Table format with 1 class per row
 * Consistent column widths across all classes
 */
private function getClassWiseView($term_ids, $campus_id, $session_id, $forPrint = false)
{
    if (empty($term_ids)) {
        return '<div class="alert alert-warning">Please select at least one term</div>';
    }
    
    if (!is_array($term_ids)) {
        $term_ids = [$term_ids];
    }
    
    // Get terms info
    $termsInfo = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
        ->join('terms t', 't.term_id = ts.term_id')
        ->whereIn('ts.term_session_id', $term_ids)
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResult();
    
    if (empty($termsInfo)) {
        return '<div class="alert alert-warning">Selected terms not found</div>';
    }
    
    // Get planning for selected terms
    $planning = $this->db->table('top_level_planning tlp')
        ->select('tlp.*, c.class_name, c.class_short_name, a.subject_name, ts.term_session_id, t.name as term_name')
        ->join('classes c', 'c.class_id = tlp.class_id')
        ->join('allsubject a', 'a.sid = tlp.subject_id')
        ->join('terms_session ts', 'ts.term_session_id = tlp.term_session_id')
        ->join('terms t', 't.term_id = ts.term_id')
        ->whereIn('tlp.term_session_id', $term_ids)
        ->where('tlp.campus_id', $campus_id)
        ->where('tlp.objective IS NOT NULL')
        ->where('tlp.objective !=', '')
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('a.subject_name', 'ASC')
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResult();
    
    if (empty($planning)) {
        return '<div class="alert alert-warning">No top level planning found with objectives for selected terms.</div>';
    }
    
    // Group by class and then by subject and term
    $groupedByClass = [];
    foreach ($planning as $item) {
        $classId = $item->class_id;
        $className = $item->class_short_name ?? $item->class_name;
        $subjectId = $item->subject_id;
        $subjectName = $item->subject_name;
        $termId = $item->term_session_id;
        
        if (!isset($groupedByClass[$classId])) {
            $groupedByClass[$classId] = [
                'class_name' => $className,
                'subjects' => []
            ];
        }
        
        if (!isset($groupedByClass[$classId]['subjects'][$subjectId])) {
            $groupedByClass[$classId]['subjects'][$subjectId] = [
                'subject_name' => $subjectName,
                'terms' => []
            ];
        }
        
        $groupedByClass[$classId]['subjects'][$subjectId]['terms'][$termId] = strip_tags($item->objective);
    }
    
    // Calculate column width based on number of terms
    $termCount = count($termsInfo);
    $subjectColumnWidth = '160px'; // Width for subject column
    $termColumnWidth = $termCount > 0 ? 'calc((100% - ' . $subjectColumnWidth . ')/' . $termCount . ')' : 'auto';
    
    // Build HTML
    $html = $forPrint ? '' : '<div class="class-wise-view">';
    
    // Terms summary
    $html .= '<div class="report-header">';
    $html .= '<div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center;">';
    $html .= '<div><i class="fas fa-calendar-alt" style="color:#2c5282; margin-right:8px;"></i><strong>Selected Terms:</strong></div>';
    $html .= '<div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:5px;">';
    foreach ($termsInfo as $term) {
        $html .= '<span class="term-badge-summary">' . esc($term->term_name) . ' (' . date('d M Y', strtotime($term->start_date)) . ' - ' . date('d M Y', strtotime($term->end_date)) . ')</span>';
    }
    $html .= '</div></div></div>';
    
    // Classes Container
    $html .= '<div class="classes-container">';
    
    foreach ($groupedByClass as $classId => $classData) {
        $bodyId = 'classBody_' . $classId;
        
        $html .= '<div class="class-main-card" data-card-id="' . $classId . '">';
        $html .= '<div class="class-main-card-header" data-bs-target="' . $bodyId . '">';
        $html .= '<span><i class="fas fa-users" style="margin-right: 10px;"></i> ' . esc($classData['class_name']) . '</span>';
        $html .= '<span style="display:flex; align-items:center; gap:10px;">';
        $html .= '<span class="subject-count-badge">' . count($classData['subjects']) . ' Subject(s)</span>';
        $html .= '<i class="fas fa-chevron-down expand-icon"></i>';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '<div class="class-main-card-body" id="' . $bodyId . '">';
        
        // Build Table with fixed column widths
        $html .= '<div class="table-responsive">';
        $html .= '<table class="planning-table" style="table-layout: fixed; width: 100%;">';
        
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th style="width: ' . $subjectColumnWidth . ';">Subject</th>';
        foreach ($termsInfo as $term) {
            $html .= '<th style="width: ' . $termColumnWidth . ';">' . esc($term->term_name) . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';
        
        $html .= '<tbody>';
        
        foreach ($classData['subjects'] as $subjectId => $subjectData) {
            $html .= '<tr>';
            $html .= '<td class="subject-name-cell"><strong><i class="fas fa-book"></i> ' . esc($subjectData['subject_name']) . '</strong></td>';
            
            foreach ($termsInfo as $term) {
                $termId = $term->term_session_id;
                $objective = isset($subjectData['terms'][$termId]) ? $subjectData['terms'][$termId] : '';
                
                if (!empty($objective)) {
                    $html .= '<td class="objective-cell">' . nl2br(esc($objective)) . '</td>';
                } else {
                    $html .= '<td class="objective-cell"><span class="text-muted"><em>No objective set</em></span></td>';
                }
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= $forPrint ? '' : '</div>';
    
    return $html;
}

public function getSubjectsBySection()
{
    $section_id = $this->request->getPost('section_id');
    $isTeacher = $this->isTeacherUser();
    
    if (!$section_id) {
        return $this->response->setJSON(['html' => '<option value="">Invalid section</option>']);
    }
    
    $options = '<option value="">Select Subject</option>';
    
    if ($isTeacher) {
        // Teacher: Get only subjects they teach in this section
        $teacher_id = session('member_userid');
        
        if ($teacher_id) {
            // Raw SQL query for teacher subjects in section
            $sql = "SELECT DISTINCT ss.subject_id, a.subject_name
                    FROM teacher_subjects ts
                    INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                    INNER JOIN allsubject a ON a.sid = ss.subject_id
                    WHERE ts.tid = ?
                        AND ts.cls_sec_id = ?
                        AND ts.status = 1
                        AND ss.status = 1
                    ORDER BY a.subject_name ASC";
            
            $subjects = $this->db->query($sql, [$teacher_id, $section_id])->getResult();
            
            foreach ($subjects as $subject) {
                $options .= '<option value="' . $subject->subject_id . '">' 
                          . esc($subject->subject_name) . '</option>';
            }
        }
    } else {
        // Admin: Get all subjects in this section
        $sql = "SELECT DISTINCT ss.subject_id, a.subject_name
                FROM section_subjects ss
                INNER JOIN allsubject a ON a.sid = ss.subject_id
                WHERE ss.cls_sec_id = ?
                    AND ss.status = 1
                ORDER BY a.subject_name ASC";
        
        $subjects = $this->db->query($sql, [$section_id])->getResult();
        
        foreach ($subjects as $subject) {
            $options .= '<option value="' . $subject->subject_id . '">' 
                      . esc($subject->subject_name) . '</option>';
        }
    }
    
    return $this->response->setJSON(['html' => $options]);
}

    public function getSubjectsByClass()
    {
        $class_id = (int) $this->request->getPost('class_id');
        $campus_id = (int) session('member_campusid');
        $teacher_id = (int) session('member_userid');
        
        if ($class_id <= 0) {
            return $this->response->setJSON(['html' => '<option value="">Select Subject</option>']);
        }
        
        $options = '<option value="">Select Subject</option>';

        if ($this->isTeacherUser() && $teacher_id > 0) {
            $subjects = getTeacherSubjectsInClass($class_id, $campus_id, $teacher_id);
            if ($subjects === []) {
                $options .= '<option value="" disabled>No subjects assigned in this class</option>';
            }
        } else {
            $subjects = $this->db->table('section_subjects ss')
                ->select('DISTINCT ss.subject_id, a.subject_name')
                ->join('allsubject a', 'a.sid = ss.subject_id')
                ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id')
                ->where('cs.class_id', $class_id)
                ->where('cs.campus_id', $campus_id)
                ->where('ss.status', 1)
                ->orderBy('a.subject_name', 'ASC')
                ->get()
                ->getResult();
        }
        
        foreach ($subjects as $subject) {
            $subjectId = (int) ($subject->subject_id ?? $subject->sid ?? 0);
            if ($subjectId <= 0) {
                continue;
            }
            $options .= '<option value="' . $subjectId . '">' 
                      . esc($subject->subject_name) . '</option>';
        }
        
        return $this->response->setJSON(['html' => $options]);
    }

    public function getClassesBySubject()
{
    $subject_id = $this->request->getPost('subject_id');
    $campus_id = session('member_campusid');
    $isTeacher = $this->isTeacherUser();
    
    if (!$subject_id) {
        return $this->response->setJSON(['html' => '<option value="">Select Class</option>']);
    }
    
    $options = '<option value="">Select Class</option>';
    
    if ($isTeacher) {
        // Teacher: Get only classes where they teach this subject
        $teacher_id = session('member_userid');
        
        if ($teacher_id) {
            $sql = "SELECT DISTINCT c.class_id, c.class_name, c.class_short_name
                    FROM teacher_subjects ts
                    INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                    INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                    INNER JOIN classes c ON c.class_id = cs.class_id
                    WHERE ts.tid = ?
                        AND ss.subject_id = ?
                        AND cs.campus_id = ?
                        AND ts.status = 1
                        AND ss.status = 1
                        AND cs.status = 1
                    ORDER BY c.class_id ASC";
            
            $classes = $this->db->query($sql, [$teacher_id, $subject_id, $campus_id])->getResult();
            
            foreach ($classes as $class) {
                $className = $class->class_short_name ?? $class->class_name;
                $options .= '<option value="' . $class->class_id . '">' 
                          . esc($className) . '</option>';
            }
        }
        
        if (empty($classes)) {
            $options .= '<option value="" disabled>No classes found where you teach this subject</option>';
        }
    } else {
        // Admin: Get all classes for this subject
        $sql = "SELECT DISTINCT c.class_id, c.class_name, c.class_short_name
                FROM section_subjects ss
                INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                INNER JOIN classes c ON c.class_id = cs.class_id
                WHERE ss.subject_id = ?
                    AND cs.campus_id = ?
                    AND ss.status = 1
                    AND cs.status = 1
                ORDER BY c.class_id ASC";
        
        $classes = $this->db->query($sql, [$subject_id, $campus_id])->getResult();
        
        foreach ($classes as $class) {
            $className = $class->class_short_name ?? $class->class_name;
            $options .= '<option value="' . $class->class_id . '">' 
                      . esc($className) . '</option>';
        }
    }
    
    return $this->response->setJSON(['html' => $options]);
}
    
  public function save()
{
    try {
        $postData = $this->request->getPost();
        $campus_id = session('member_campusid');
        $user_id = session('member_userid');
        $isAutoSave = $this->request->getPost('auto_save') ?? false;
        
        $savedCount = 0;
        $errors = [];
        
        if (isset($postData['planning']) && is_array($postData['planning'])) {
            foreach ($postData['planning'] as $key => $data) {
                $class_id = $data['class_id'] ?? null;
                $subject_id = $data['subject_id'] ?? null;
                $term_session_id = $data['term_session_id'] ?? null;
                $objective = $data['objective'] ?? '';
                
                // Skip if required fields are missing
                if (!$class_id || !$subject_id || !$term_session_id) {
                    continue;
                }

                if (! $this->teacherCanPlanClassSubject((int) $class_id, (int) $subject_id)) {
                    $errors[] = 'You are not assigned to teach this subject in the selected class.';
                    continue;
                }
                
                // Clean objective - remove excessive whitespace
                $objective = trim($objective);
                
                // Check if record exists
                $existing = $this->db->table('top_level_planning')
                    ->where('class_id', $class_id)
                    ->where('subject_id', $subject_id)
                    ->where('term_session_id', $term_session_id)
                    ->where('campus_id', $campus_id)
                    ->get()
                    ->getRow();
                
                $saveData = [
                    'class_id' => $class_id,
                    'subject_id' => $subject_id,
                    'term_session_id' => $term_session_id,
                    'campus_id' => $campus_id,
                    'objective' => $objective,
                    'updated_date' => date('Y-m-d H:i:s'),
                    'user_id' => $user_id
                ];
                
                if ($existing) {
                    // Only update if objective has changed (for auto-save efficiency)
                    if ($existing->objective != $objective) {
                        $this->db->table('top_level_planning')
                            ->where('tlp_id', $existing->tlp_id)
                            ->update($saveData);
                        $savedCount++;
                    }
                } else {
                    // Only insert if objective is not empty
                    if (!empty($objective)) {
                        $saveData['created_date'] = date('Y-m-d H:i:s');
                        $this->db->table('top_level_planning')->insert($saveData);
                        $savedCount++;
                    }
                }
            }
            
            if ($savedCount === 0 && $errors !== []) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg' => $errors[0],
                ]);
            }

            $message = $isAutoSave ? 'Auto-saved (' . $savedCount . ' updated)' : 'Top Level Planning saved successfully (' . $savedCount . ' records)';
            if ($errors !== []) {
                $message .= '. ' . count($errors) . ' item(s) skipped (not your assigned subject/class).';
            }
            
            return $this->response->setJSON([
                'success' => true,
                'msg' => $message,
                'saved_count' => $savedCount,
                'auto_save' => $isAutoSave
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'No data to save'
        ]);
        
    } catch (\Exception $e) {
        log_message('error', 'Error saving top level planning: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error saving: ' . $e->getMessage()
        ]);
    }
}

  private function getCampusName($campus_id)
    {
        $campus = $this->db->table('campus')
            ->select('campus_name')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();
        
        return $campus ? $campus->campus_name : 'N/A';
    }

    private function getSessionName($session_id)
    {
        $session = $this->db->table('academic_session')
            ->select('session_name')
            ->where('session_id', $session_id)
            ->get()
            ->getRow();
        
        return $session ? $session->session_name : 'N/A';
    }



  

    /**
     * Subject Wise View - Returns HTML with cards (4 per row)
     * Cards are closed by default, only one opens at a time (accordion)
     * Printable - all content visible when printing
     */


    private function getTermWiseView($term_ids, $campus_id, $session_id, $forPrint = false)
    {
        if (empty($term_ids)) {
            return '<div class="alert alert-warning">Please select at least one term</div>';
        }
        
        if (!is_array($term_ids)) {
            $term_ids = [$term_ids];
        }
        
        // Get terms info
        $termsInfo = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
            ->join('terms t', 't.term_id = ts.term_id')
            ->whereIn('ts.term_session_id', $term_ids)
            ->orderBy('ts.start_date', 'ASC')
            ->get()
            ->getResult();
        
        if (empty($termsInfo)) {
            return '<div class="alert alert-warning">Selected terms not found</div>';
        }
        
        // Get planning for selected terms
        $planning = $this->db->table('top_level_planning tlp')
            ->select('tlp.*, c.class_name, c.class_short_name, a.subject_name, ts.term_session_id, t.name as term_name')
            ->join('classes c', 'c.class_id = tlp.class_id')
            ->join('allsubject a', 'a.sid = tlp.subject_id')
            ->join('terms_session ts', 'ts.term_session_id = tlp.term_session_id')
            ->join('terms t', 't.term_id = ts.term_id')
            ->whereIn('tlp.term_session_id', $term_ids)
            ->where('tlp.campus_id', $campus_id)
            ->where('tlp.objective IS NOT NULL')
            ->where('tlp.objective !=', '')
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('a.subject_name', 'ASC')
            ->get()
            ->getResult();
        
        if (empty($planning)) {
            return '<div class="alert alert-warning">No top level planning found with objectives for selected terms.</div>';
        }
        
        // Group by term and class
        $groupedData = [];
        foreach ($planning as $item) {
            $termId = $item->term_session_id;
            $classId = $item->class_id;
            $className = $item->class_short_name ?? $item->class_name;
            
            if (!isset($groupedData[$termId])) {
                $groupedData[$termId] = [
                    'term_name' => $item->term_name,
                    'classes' => []
                ];
            }
            
            if (!isset($groupedData[$termId]['classes'][$classId])) {
                $groupedData[$termId]['classes'][$classId] = [
                    'class_name' => $className,
                    'subjects' => []
                ];
            }
            
            $groupedData[$termId]['classes'][$classId]['subjects'][] = $item;
        }
        
        // Build HTML
        $html = $forPrint ? '' : '<div class="term-wise-view">';
        
        $html .= '<div class="report-header" style="background:#f4f7fc; border-start:4px solid #2c5282; padding:1rem; margin-bottom:1.5rem; border-radius:12px;">';
        $html .= '<strong><i class="fas fa-calendar"></i> Term Wise Planning</strong>';
        $html .= '</div>';
        
        foreach ($groupedData as $termId => $termData) {
            $html .= '<div class="term-section" style="margin-bottom:30px;">';
            $html .= '<h3 class="term-title" style="background:#1e6f5c; color:white; padding:10px 16px; border-radius:12px; font-size:1.2rem;"><i class="fas fa-calendar-week"></i> ' . esc($termData['term_name']) . '</h3>';
            
            foreach ($termData['classes'] as $classId => $classData) {
                $html .= '<div class="class-section" style="margin-bottom:20px;">';
                $html .= '<h4 class="class-title" style="background:#e9f0f5; padding:8px 14px; border-start:4px solid #2c7da0; margin:12px 0 10px 0; font-size:1rem;"><i class="fas fa-chalkboard-user"></i> ' . esc($classData['class_name']) . '</h4>';
                $html .= '<div class="subjects-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px,1fr)); gap:15px;">';
                
                foreach ($classData['subjects'] as $subject) {
                    $html .= '<div class="subject-card" style="border:1px solid #e2edf7; border-radius:14px; background:white; overflow:hidden;">';
                    $html .= '<div class="subject-header" style="background:#f8fafc; padding:10px 14px; border-bottom:1px solid #e2edf7; font-weight:600; color:#1e4663;">' . esc($subject->subject_name) . '</div>';
                    $html .= '<div class="subject-body" style="padding:12px 14px;">';
                    $html .= '<div class="objective-text" style="font-size:13px; line-height:1.5; color:#2d3e50;">' . nl2br(esc($subject->objective)) . '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                
                $html .= '</div>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= $forPrint ? '' : '</div>';
        
        return $html;
    }

  
public function printReport()
{
    $view_type = $this->request->getGet('view_type');
    $term_ids = $this->request->getGet('term_ids');
    $campus_id = session('member_campusid');
    $session_id = session('member_sessionid');
    
    if (!is_array($term_ids)) {
        $term_ids = [$term_ids];
    }
    
    $schoolinfo = getSchoolInfo();
    $schoolName = $schoolinfo->name ?? 'School Name';
    
    // Get terms info for column calculation
    $termsInfo = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
        ->join('terms t', 't.term_id = ts.term_id')
        ->whereIn('ts.term_session_id', $term_ids)
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResult();
    
    $termCount = count($termsInfo);
    $classColumnWidth = '120px'; // Reduced for short class names
    $subjectColumnWidth = '160px';
    $termColumnWidth = $termCount > 0 ? 'calc((100% - 120px)/' . $termCount . ')' : 'auto';
    $termColumnWidthClass = $termCount > 0 ? 'calc((100% - 160px)/' . $termCount . ')' : 'auto';
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <title>Top Level Planning Report</title>
        <meta charset="UTF-8">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 14px;
                line-height: 1.5;
                color: #333;
                padding: 20px;
                background: white;
            }
            
            .print-container {
                max-width: 1400px;
                margin: 0 auto;
                background: white;
            }
            
            /* Header Styles */
            .print-header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 15px;
                border-bottom: 2px solid #333;
            }
            
            .print-header h1 {
                font-size: 24px;
                margin-bottom: 8px;
                color: #2c3e66;
            }
            
            .print-header h3 {
                font-size: 18px;
                margin-bottom: 8px;
                color: #555;
                font-weight: normal;
            }
            
            .print-header p {
                font-size: 13px;
                color: #666;
                margin-top: 5px;
            }
            
            /* Report Info */
            .report-info {
                margin-bottom: 25px;
                padding: 15px;
                background: #f5f5f5;
                border-start: 4px solid #2c3e66;
                border-radius: 8px;
            }
            
            .report-info p {
                margin: 8px 0;
                font-size: 14px;
            }
            
            .report-info strong {
                font-size: 15px;
            }
            
            /* Term Badges */
            .term-badges {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin: 12px 0 5px 0;
            }
            
            .term-badge {
                background: #e9ecef;
                padding: 6px 14px;
                border-radius: 25px;
                font-size: 13px;
                color: #2c5282;
                display: inline-block;
            }
            
            /* Subject/Class Cards for Print */
            .print-subject-card,
            .print-class-card {
                margin-bottom: 30px;
                page-break-inside: avoid;
                break-inside: avoid;
                border: 1px solid #ddd;
                border-radius: 10px;
                overflow: hidden;
                background: white;
            }
            
            .print-card-header {
                background: #2c3e66;
                color: white;
                padding: 14px 18px;
                font-size: 16px;
                font-weight: bold;
            }
            
            .print-card-header i {
                margin-right: 8px;
            }
            
            .print-card-body {
                padding: 18px;
                background: white;
            }
            
            /* Table Styles for Print */
            .print-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
                table-layout: fixed;
            }
            
            .print-table th {
                background: #f0f4f8;
                border: 1px solid #aaa;
                padding: 12px 10px;
                text-align: left;
                font-weight: bold;
                font-size: 14px;
            }
            
            .print-table td {
                border: 1px solid #aaa;
                padding: 12px 10px;
                vertical-align: top;
                font-size: 14px;
            }
            
            .print-table .row-label {
                background: #f8fafc;
                font-weight: bold;
            }
            
            .objective-text {
                line-height: 1.5;
                font-size: 14px;
            }
            
            .no-objective {
                color: #999;
                font-style: italic;
                font-size: 13px;
            }
            
            /* Footer */
            .print-footer {
                margin-top: 35px;
                padding-top: 12px;
                text-align: center;
                font-size: 11px;
                color: #999;
                border-top: 1px solid #ddd;
            }
            
            /* Page Break Control */
            .page-break {
                page-break-before: always;
            }
            
            /* Responsive for print */
            @media print {
                body {
                    padding: 0.2in;
                    margin: 0;
                    font-size: 12px;
                }
                
                .print-container {
                    max-width: 100%;
                }
                
                .print-header h1 {
                    font-size: 20px;
                }
                
                .print-header h3 {
                    font-size: 16px;
                }
                
                .print-card-header {
                    font-size: 14px;
                    padding: 10px 15px;
                }
                
                .print-card-body {
                    padding: 12px;
                }
                
                .print-table {
                    font-size: 12px;
                }
                
                .print-table th,
                .print-table td {
                    padding: 8px 8px;
                    font-size: 12px;
                }
                
                .print-subject-card,
                .print-class-card {
                    break-inside: avoid;
                    page-break-inside: avoid;
                    margin-bottom: 20px;
                }
                
                .print-table th,
                .print-table td {
                    border: 1px solid #000 !important;
                }
                
                .term-badge {
                    font-size: 11px;
                    padding: 4px 10px;
                }
                
                .report-info {
                    padding: 10px;
                    margin-bottom: 15px;
                }
                
                .report-info p {
                    font-size: 12px;
                }
                
                .objective-text {
                    font-size: 12px;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-container">
            <div class="print-header">
                <h1>' . esc($schoolName) . '</h1>
                <h3>Top Level Planning Report</h3>
                <p>Generated on: ' . date('d-m-Y h:i A') . '</p>
            </div>';
    
    if ($view_type == 'subject_wise') {
        $html .= $this->getSubjectWiseViewPrint($term_ids, $campus_id, $session_id, $termsInfo);
    } elseif ($view_type == 'class_wise') {
        $html .= $this->getClassWiseViewPrint($term_ids, $campus_id, $session_id, $termsInfo);
    } elseif ($view_type == 'term_wise') {
        $html .= $this->getTermWiseViewPrint($term_ids, $campus_id, $session_id, $termsInfo);
    }
    
    $html .= '
            <div class="print-footer">
                <p>This is a system generated report. No signature required.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $this->response->setBody($html);
}

// Print version of Subject Wise View with consistent column widths
private function getSubjectWiseViewPrint($term_ids, $campus_id, $session_id, $termsInfo)
{
    if (empty($term_ids)) {
        return '<div class="alert alert-warning">Please select at least one term</div>';
    }
    
    if (!is_array($term_ids)) {
        $term_ids = [$term_ids];
    }
    
    // Get planning data
    $planning = $this->db->table('top_level_planning tlp')
        ->select('tlp.*, c.class_name, c.class_short_name, a.subject_name, ts.term_session_id, t.name as term_name')
        ->join('classes c', 'c.class_id = tlp.class_id')
        ->join('allsubject a', 'a.sid = tlp.subject_id')
        ->join('terms_session ts', 'ts.term_session_id = tlp.term_session_id')
        ->join('terms t', 't.term_id = ts.term_id')
        ->whereIn('tlp.term_session_id', $term_ids)
        ->where('tlp.campus_id', $campus_id)
        ->where('tlp.objective IS NOT NULL')
        ->where('tlp.objective !=', '')
        ->orderBy('a.subject_name', 'ASC')
        ->orderBy('c.class_id', 'ASC')
        ->get()
        ->getResult();
    
    if (empty($planning)) {
        return '<div class="alert alert-warning">No top level planning found.</div>';
    }
    
    // Group by subject
    $groupedBySubject = [];
    foreach ($planning as $item) {
        $subjectId = $item->subject_id;
        $subjectName = $item->subject_name;
        $classId = $item->class_id;
        $className = $item->class_short_name ?? $item->class_name;
        $termId = $item->term_session_id;
        
        if (!isset($groupedBySubject[$subjectId])) {
            $groupedBySubject[$subjectId] = [
                'subject_name' => $subjectName,
                'classes' => []
            ];
        }
        
        if (!isset($groupedBySubject[$subjectId]['classes'][$classId])) {
            $groupedBySubject[$subjectId]['classes'][$classId] = [
                'class_name' => $className,
                'terms' => []
            ];
        }
        
        $groupedBySubject[$subjectId]['classes'][$classId]['terms'][$termId] = strip_tags($item->objective);
    }
    
    // Calculate column widths
    $termCount = count($termsInfo);
    $classColumnWidth = '120px';
    $termColumnWidth = $termCount > 0 ? 'calc((100% - 120px)/' . $termCount . ')' : 'auto';
    
    // Build HTML for print
    $html = '<div class="report-info">';
    $html .= '<p><strong>Report Type:</strong> Subject Wise Planning</p>';
    $html .= '<div><strong>Selected Terms:</strong></div>';
    $html .= '<div class="term-badges">';
    foreach ($termsInfo as $term) {
        $html .= '<span class="term-badge">' . esc($term->term_name) . '</span>';
    }
    $html .= '</div></div>';
    
    foreach ($groupedBySubject as $subjectId => $subjectData) {
        $html .= '<div class="print-subject-card">';
        $html .= '<div class="print-card-header">';
        $html .= '<i class="fas fa-book-open"></i> ' . esc($subjectData['subject_name']);
        $html .= ' <span style="font-size: 13px; font-weight: normal;">(' . count($subjectData['classes']) . ' Classes)</span>';
        $html .= '</div>';
        $html .= '<div class="print-card-body">';
        
        // Build Table with fixed column widths
        $html .= '<table class="print-table" style="table-layout: fixed; width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th style="width: ' . $classColumnWidth . ';">Class</th>';
        foreach ($termsInfo as $term) {
            $html .= '<th style="width: ' . $termColumnWidth . ';">' . esc($term->term_name) . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($subjectData['classes'] as $classId => $classData) {
            $html .= '<tr>';
            $html .= '<td class="row-label"><strong>' . esc($classData['class_name']) . '</strong></td>';
            
            foreach ($termsInfo as $term) {
                $termId = $term->term_session_id;
                $objective = isset($classData['terms'][$termId]) ? $classData['terms'][$termId] : '';
                
                if (!empty($objective)) {
                    $html .= '<td class="objective-text">' . nl2br(esc($objective)) . '</td>';
                } else {
                    $html .= '<td class="no-objective">— No objective set —</td>';
                }
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div></div>';
    }
    
    return $html;
}

// Print version of Class Wise View with consistent column widths
private function getClassWiseViewPrint($term_ids, $campus_id, $session_id, $termsInfo)
{
    if (empty($term_ids)) {
        return '<div class="alert alert-warning">Please select at least one term</div>';
    }
    
    if (!is_array($term_ids)) {
        $term_ids = [$term_ids];
    }
    
    // Get planning data
    $planning = $this->db->table('top_level_planning tlp')
        ->select('tlp.*, c.class_name, c.class_short_name, a.subject_name, ts.term_session_id, t.name as term_name')
        ->join('classes c', 'c.class_id = tlp.class_id')
        ->join('allsubject a', 'a.sid = tlp.subject_id')
        ->join('terms_session ts', 'ts.term_session_id = tlp.term_session_id')
        ->join('terms t', 't.term_id = ts.term_id')
        ->whereIn('tlp.term_session_id', $term_ids)
        ->where('tlp.campus_id', $campus_id)
        ->where('tlp.objective IS NOT NULL')
        ->where('tlp.objective !=', '')
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('a.subject_name', 'ASC')
        ->get()
        ->getResult();
    
    if (empty($planning)) {
        return '<div class="alert alert-warning">No top level planning found.</div>';
    }
    
    // Group by class
    $groupedByClass = [];
    foreach ($planning as $item) {
        $classId = $item->class_id;
        $className = $item->class_short_name ?? $item->class_name;
        $subjectId = $item->subject_id;
        $subjectName = $item->subject_name;
        $termId = $item->term_session_id;
        
        if (!isset($groupedByClass[$classId])) {
            $groupedByClass[$classId] = [
                'class_name' => $className,
                'subjects' => []
            ];
        }
        
        if (!isset($groupedByClass[$classId]['subjects'][$subjectId])) {
            $groupedByClass[$classId]['subjects'][$subjectId] = [
                'subject_name' => $subjectName,
                'terms' => []
            ];
        }
        
        $groupedByClass[$classId]['subjects'][$subjectId]['terms'][$termId] = strip_tags($item->objective);
    }
    
    // Calculate column widths
    $termCount = count($termsInfo);
    $subjectColumnWidth = '160px';
    $termColumnWidth = $termCount > 0 ? 'calc((100% - 160px)/' . $termCount . ')' : 'auto';
    
    // Build HTML for print
    $html = '<div class="report-info">';
    $html .= '<p><strong>Report Type:</strong> Class Wise Planning</p>';
    $html .= '<div><strong>Selected Terms:</strong></div>';
    $html .= '<div class="term-badges">';
    foreach ($termsInfo as $term) {
        $html .= '<span class="term-badge">' . esc($term->term_name) . '</span>';
    }
    $html .= '</div></div>';
    
    foreach ($groupedByClass as $classId => $classData) {
        $html .= '<div class="print-class-card">';
        $html .= '<div class="print-card-header">';
        $html .= '<i class="fas fa-users"></i> ' . esc($classData['class_name']);
        $html .= ' <span style="font-size: 13px; font-weight: normal;">(' . count($classData['subjects']) . ' Subjects)</span>';
        $html .= '</div>';
        $html .= '<div class="print-card-body">';
        
        // Build Table with fixed column widths
        $html .= '<table class="print-table" style="table-layout: fixed; width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th style="width: ' . $subjectColumnWidth . ';">Subject</th>';
        foreach ($termsInfo as $term) {
            $html .= '<th style="width: ' . $termColumnWidth . ';">' . esc($term->term_name) . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($classData['subjects'] as $subjectId => $subjectData) {
            $html .= '<tr>';
            $html .= '<td class="row-label"><strong>' . esc($subjectData['subject_name']) . '</strong></td>';
            
            foreach ($termsInfo as $term) {
                $termId = $term->term_session_id;
                $objective = isset($subjectData['terms'][$termId]) ? $subjectData['terms'][$termId] : '';
                
                if (!empty($objective)) {
                    $html .= '<td class="objective-text">' . nl2br(esc($objective)) . '</td>';
                } else {
                    $html .= '<td class="no-objective">— No objective set —</td>';
                }
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div></div>';
    }
    
    return $html;
}

// Print version of Term Wise View
private function getTermWiseViewPrint($term_ids, $campus_id, $session_id, $termsInfo)
{
    if (empty($term_ids)) {
        return '<div class="alert alert-warning">Please select at least one term</div>';
    }
    
    if (!is_array($term_ids)) {
        $term_ids = [$term_ids];
    }
    
    // Get planning data
    $planning = $this->db->table('top_level_planning tlp')
        ->select('tlp.*, c.class_name, c.class_short_name, a.subject_name, ts.term_session_id, t.name as term_name')
        ->join('classes c', 'c.class_id = tlp.class_id')
        ->join('allsubject a', 'a.sid = tlp.subject_id')
        ->join('terms_session ts', 'ts.term_session_id = tlp.term_session_id')
        ->join('terms t', 't.term_id = ts.term_id')
        ->whereIn('tlp.term_session_id', $term_ids)
        ->where('tlp.campus_id', $campus_id)
        ->where('tlp.objective IS NOT NULL')
        ->where('tlp.objective !=', '')
        ->orderBy('ts.start_date', 'ASC')
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('a.subject_name', 'ASC')
        ->get()
        ->getResult();
    
    if (empty($planning)) {
        return '<div class="alert alert-warning">No top level planning found.</div>';
    }
    
    // Group by term and class
    $groupedByTerm = [];
    foreach ($planning as $item) {
        $termId = $item->term_session_id;
        $termName = $item->term_name;
        $classId = $item->class_id;
        $className = $item->class_short_name ?? $item->class_name;
        
        if (!isset($groupedByTerm[$termId])) {
            $groupedByTerm[$termId] = [
                'term_name' => $termName,
                'classes' => []
            ];
        }
        
        if (!isset($groupedByTerm[$termId]['classes'][$classId])) {
            $groupedByTerm[$termId]['classes'][$classId] = [
                'class_name' => $className,
                'subjects' => []
            ];
        }
        
        $groupedByTerm[$termId]['classes'][$classId]['subjects'][] = $item;
    }
    
    // Build HTML for print
    $html = '<div class="report-info">';
    $html .= '<p><strong>Report Type:</strong> Term Wise Planning</p>';
    $html .= '<div><strong>Selected Terms:</strong></div>';
    $html .= '<div class="term-badges">';
    foreach ($termsInfo as $term) {
        $html .= '<span class="term-badge">' . esc($term->term_name) . '</span>';
    }
    $html .= '</div></div>';
    
    foreach ($groupedByTerm as $termId => $termData) {
        $html .= '<div class="print-subject-card">';
        $html .= '<div class="print-card-header" style="background: #1e6f5c;">';
        $html .= '<i class="fas fa-calendar-alt"></i> ' . esc($termData['term_name']);
        $html .= '</div>';
        $html .= '<div class="print-card-body">';
        
        foreach ($termData['classes'] as $classId => $classData) {
            $html .= '<h4 style="margin: 18px 0 12px 0; padding: 8px 12px; background: #f0f4f8; border-start: 3px solid #2c7da0; font-size: 15px;">' . esc($classData['class_name']) . '</h4>';
            
            $html .= '<table class="print-table" style="width: 100%; border-collapse: collapse;">';
            $html .= '<thead><tr><th style="width: 30%;">Subject</th><th>Objective</th></tr></thead>';
            $html .= '<tbody>';
            
            foreach ($classData['subjects'] as $subject) {
                $html .= '<tr>';
                $html .= '<td class="row-label"><strong>' . esc($subject->subject_name) . '</strong></td>';
                $html .= '<td class="objective-text">' . nl2br(esc(strip_tags($subject->objective))) . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
        }
        
        $html .= '</div></div>';
    }
    
    return $html;
}



public function add()
{
    check_permission('admin-add-top-level-planning');
    
    $campus_id = session('member_campusid');
    $sessionid = session('member_sessionid');
    $schoolinfo = getSchoolInfo();
    $isTeacher = $this->isTeacherUser();
    $teacher_id = session('member_userid');
    
    // Get all terms for the current session
    $terms = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date, t.name as term_name')
        ->join('terms t', 't.term_id = ts.term_id')
        ->where('ts.session_id', $sessionid)
        ->where('t.status', 1)
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResult();
    
    // Get classes based on user role
    $classes = [];
    
    if ($isTeacher && $teacher_id) {
        // TEACHER: Get classes from teacher's subject sections (using teacher_subjects)
        $teacherSections = getTeacherSubjectSections();
        $classIds = [];
        
        foreach ($teacherSections as $section) {
            $classId = $section['class_id'];
            if (!in_array($classId, $classIds)) {
                $classIds[] = $classId;
                $classes[] = (object)[
                    'class_id' => $section['class_id'],
                    'class_name' => $section['class_name'],
                    'class_short_name' => $section['class_short_name'] ?? $section['class_name']
                ];
            }
        }
        
        // Sort classes by class_id
        usort($classes, function($a, $b) {
            return $a->class_id - $b->class_id;
        });
        
    } else {
        // ADMIN/NON-TEACHER: Get all classes for this campus
        $sql = "SELECT DISTINCT 
                    c.class_id, 
                    c.class_name, 
                    c.class_short_name
                FROM class_section cs
                INNER JOIN classes c ON c.class_id = cs.class_id
                WHERE cs.campus_id = ?
                    AND cs.status = 1
                    AND c.status = 1
                ORDER BY c.class_id ASC";
        
        $classes = $this->db->query($sql, [$campus_id])->getResult();
    }
    
    // Get subjects based on user role
    $subjects = [];
    
    if ($isTeacher && $teacher_id) {
        // TEACHER: Get subjects from teacher_subjects directly
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
        
        $subjects = $this->db->query($sql, [$teacher_id])->getResult();
        
    } else {
        // ADMIN/NON-TEACHER: Get all subjects
        $subjects = $this->db->table('allsubject')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)
            ->orderBy('subject_name', 'ASC')
            ->get()
            ->getResult();
    }
    
    $data = array_merge(ui_asset_defaults(['uiNeedsSummernote' => true]), [
        'title' => 'Top Level Planning',
        'terms' => $terms,
        'classes' => $classes,
        'subjects' => $subjects,
        'current_session_id' => $sessionid,
        'isTeacher' => $isTeacher,
        'campus_id' => $campus_id,
    ]);
    
    return view('admin/top_level_planning/add', $data);
}



public function getPlanningForm()
{
    try {
        $term_session_id = $this->request->getPost('term_session_id');
        $class_id = $this->request->getPost('class_id');
        $subject_id = $this->request->getPost('subject_id');
        $entry_type = $this->request->getPost('entry_type');
        $campus_id = session('member_campusid');
        
        // Validate required parameters
        if ($entry_type == 'term_wise') {
            // For term wise, we need class_id and subject_id (term_session_id is not used)
            if (!$class_id || !$subject_id) {
                return $this->response->setJSON(['html' => '<div class="alert alert-warning">Please select both class and subject</div>']);
            }
            return $this->getTermWiseForm($term_session_id, $class_id, $subject_id, $campus_id);
        } 
        elseif ($entry_type == 'class_wise') {
            if (!$class_id) {
                return $this->response->setJSON(['html' => '<div class="alert alert-warning">Please select a class</div>']);
            }
            return $this->getClassWiseForm($term_session_id, $class_id, $campus_id);
        } 
        elseif ($entry_type == 'subject_wise') {
            if (!$subject_id) {
                return $this->response->setJSON(['html' => '<div class="alert alert-warning">Please select a subject</div>']);
            }
            return $this->getSubjectWiseForm($term_session_id, $subject_id, $campus_id);
        } 
        else {
            return $this->response->setJSON(['html' => '<div class="alert alert-warning">Invalid entry type</div>']);
        }
        
    } catch (\Exception $e) {
        log_message('error', 'Error in getPlanningForm: ' . $e->getMessage());
        return $this->response->setJSON([
            'html' => '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>'
        ]);
    }
}

private function getClassWiseForm($term_session_id, $class_id, $campus_id)
{
    // Validate inputs
    if (!$term_session_id || !$class_id || !$campus_id) {
        return $this->response->setJSON(['html' => '<div class="alert alert-warning">Missing required parameters.</div>']);
    }
    
    // Get term info
    $termInfo = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
        ->join('terms t', 't.term_id = ts.term_id')
        ->where('ts.term_session_id', $term_session_id)
        ->get()
        ->getRow();
    
    if (!$termInfo) {
        return $this->response->setJSON(['html' => '<div class="alert alert-warning">Term not found.</div>']);
    }
    
    $isTeacher = $this->isTeacherUser();
    $teacher_id = session('member_userid');
    
    // Get subjects based on user role
    if ($isTeacher && $teacher_id) {
        $subjects = getTeacherSubjectsInClass($class_id, $campus_id, $teacher_id);
        $infoMessage = 'Showing only subjects you teach in this class.';
    } else {
        $sql = "SELECT DISTINCT 
                    a.sid as subject_id, 
                    a.subject_name,
                    a.subject_short_name
                FROM section_subjects ss
                INNER JOIN allsubject a ON a.sid = ss.subject_id
                INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
                WHERE cs.class_id = ?
                    AND cs.campus_id = ?
                    AND ss.status = 1
                    AND cs.status = 1
                ORDER BY a.subject_name ASC";
        
        $subjects = $this->db->query($sql, [$class_id, $campus_id])->getResult();
        $infoMessage = '';
    }
    
    if (empty($subjects)) {
        $message = $isTeacher ? 'You are not assigned to teach any subject in this class.' : 'No subjects found for this class.';
        return $this->response->setJSON(['html' => '<div class="alert alert-warning">' . $message . '</div>']);
    }
    
    // Get existing planning data
    $existingRecords = $this->db->table('top_level_planning')
        ->where('class_id', $class_id)
        ->where('term_session_id', $term_session_id)
        ->where('campus_id', $campus_id)
        ->get()
        ->getResult();
    
    $existingData = [];
    foreach ($existingRecords as $record) {
        $existingData[$record->subject_id] = $record->objective;
    }
    
    $html = '<div class="class-wise-form">';
    $html .= '<div class="alert alert-info mb-3">';
    $html .= '<i class="fas fa-info-circle"></i> Enter planning for <strong>' . htmlspecialchars($termInfo->term_name) . '</strong>';
    if ($infoMessage) {
        $html .= '<br><small class="text-muted">' . $infoMessage . '</small>';
    }
    $html .= '</div>';
    
    $html .= '<div class="planning-cards">';
    
    $index = 0;
    foreach ($subjects as $subject) {
        $objective = isset($existingData[$subject->subject_id]) ? $existingData[$subject->subject_id] : '';
        
        $html .= '<div class="planning-card">';
        $html .= '<div class="planning-card-header">';
        $html .= '<h5><i class="fas fa-book"></i> ' . htmlspecialchars($subject->subject_name) . '</h5>';
        $html .= '</div>';
        $html .= '<div class="planning-card-body">';
        
        $html .= '<input type="hidden" name="planning[' . $index . '][class_id]" value="' . $class_id . '">';
        $html .= '<input type="hidden" name="planning[' . $index . '][subject_id]" value="' . $subject->subject_id . '">';
        $html .= '<input type="hidden" name="planning[' . $index . '][term_session_id]" value="' . $term_session_id . '">';
        
        $html .= '<div class="form-group">';
        $html .= '<label>Objective / Planning</label>';
        $html .= '<textarea class="form-control summernote planning-item" name="planning[' . $index . '][objective]" rows="4">' . htmlspecialchars($objective) . '</textarea>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $index++;
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $this->response->setJSON(['html' => $html]);
}



private function getSubjectWiseForm($term_session_id, $subject_id, $campus_id)
{
    // Validate inputs
    if (!$term_session_id || !$subject_id || !$campus_id) {
        return $this->response->setJSON(['html' => '<div class="alert alert-warning">Missing required parameters.</div>']);
    }
    
    // Get term info
    $termInfo = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
        ->join('terms t', 't.term_id = ts.term_id')
        ->where('ts.term_session_id', $term_session_id)
        ->get()
        ->getRow();
    
    if (!$termInfo) {
        return $this->response->setJSON(['html' => '<div class="alert alert-warning">Term not found.</div>']);
    }
    
    $isTeacher = $this->isTeacherUser();
    $teacher_id = session('member_userid');
    
    // Get classes based on user role
    if ($isTeacher && $teacher_id) {
        $classes = getTeacherClassesForSubject($teacher_id, $subject_id, $campus_id);
        $infoMessage = 'Showing only classes where you teach this subject.';
    } else {
        $classes = getAllClassesForSubject($subject_id, $campus_id);
        $infoMessage = '';
    }
    
    if (empty($classes)) {
        $message = $isTeacher ? 'You are not assigned to teach this subject in any class.' : 'No classes found for this subject.';
        return $this->response->setJSON(['html' => '<div class="alert alert-warning">' . $message . '</div>']);
    }
    
    // Get existing planning data
    $existingRecords = $this->db->table('top_level_planning')
        ->where('subject_id', $subject_id)
        ->where('term_session_id', $term_session_id)
        ->where('campus_id', $campus_id)
        ->get()
        ->getResult();
    
    $existingData = [];
    foreach ($existingRecords as $record) {
        $existingData[$record->class_id] = $record->objective;
    }
    
    $html = '<div class="subject-wise-form">';
    $html .= '<div class="alert alert-info mb-3">';
    $html .= '<i class="fas fa-info-circle"></i> Enter planning for all classes of <strong>' . htmlspecialchars($termInfo->term_name) . '</strong>';
    if ($infoMessage) {
        $html .= '<br><small class="text-muted">' . $infoMessage . '</small>';
    }
    $html .= '</div>';
    
    $html .= '<div class="planning-cards">';
    
    $index = 0;
    foreach ($classes as $class) {
        $objective = isset($existingData[$class->class_id]) ? $existingData[$class->class_id] : '';
        $className = $class->class_short_name ?? $class->class_name;
        
        $html .= '<div class="planning-card">';
        $html .= '<div class="planning-card-header">';
        $html .= '<h5><i class="fas fa-graduation-cap"></i> ' . htmlspecialchars($className) . '</h5>';
        $html .= '</div>';
        $html .= '<div class="planning-card-body">';
        
        $html .= '<input type="hidden" name="planning[' . $index . '][class_id]" value="' . $class->class_id . '">';
        $html .= '<input type="hidden" name="planning[' . $index . '][subject_id]" value="' . $subject_id . '">';
        $html .= '<input type="hidden" name="planning[' . $index . '][term_session_id]" value="' . $term_session_id . '">';
        
        $html .= '<div class="form-group">';
        $html .= '<label>Objective / Planning</label>';
        $html .= '<textarea class="form-control summernote planning-item" name="planning[' . $index . '][objective]" rows="4">' . htmlspecialchars($objective) . '</textarea>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $index++;
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $this->response->setJSON(['html' => $html]);
}


private function getTermWiseForm($term_session_id, $class_id, $subject_id, $campus_id)
{
    // Validate inputs
    if (!$class_id || !$subject_id || !$campus_id) {
        return $this->response->setJSON(['html' => '<div class="alert alert-warning">Missing required parameters.</div>']);
    }

    if (! $this->teacherCanPlanClassSubject((int) $class_id, (int) $subject_id)) {
        return $this->response->setJSON([
            'html' => '<div class="alert alert-warning">You can only add planning for subjects you teach in the selected class.</div>',
        ]);
    }
    
    // Get all terms for the session
    $session_id = session('member_sessionid');
    
    $termsQuery = $this->db->table('terms_session ts')
        ->select('ts.term_session_id, t.name as term_name, ts.start_date, ts.end_date')
        ->join('terms t', 't.term_id = ts.term_id')
        ->where('ts.session_id', $session_id)
        ->where('t.status', 1)
        ->orderBy('ts.start_date', 'ASC')
        ->get();
    
    if (!$termsQuery) {
        return $this->response->setJSON(['html' => '<div class="alert alert-danger">Database error: ' . ($this->db->error()['message'] ?? 'Unknown error') . '</div>']);
    }
    
    $terms = $termsQuery->getResult();
    
    if (empty($terms)) {
        return $this->response->setJSON(['html' => '<div class="alert alert-warning">No terms found.</div>']);
    }
    
    // Get class and subject names
    $classInfo = $this->db->table('classes')
        ->select('class_name, class_short_name')
        ->where('class_id', $class_id)
        ->get()
        ->getRow();
    
    $subjectInfo = $this->db->table('allsubject')
        ->select('subject_name')
        ->where('sid', $subject_id)
        ->get()
        ->getRow();
    
    // Get existing planning data
    $existingRecordsQuery = $this->db->table('top_level_planning')
        ->where('class_id', $class_id)
        ->where('subject_id', $subject_id)
        ->where('campus_id', $campus_id)
        ->get();
    
    $existingData = [];
    if ($existingRecordsQuery) {
        $existingRecords = $existingRecordsQuery->getResult();
        foreach ($existingRecords as $record) {
            $existingData[$record->term_session_id] = $record->objective;
        }
    }
    
    $html = '<div class="term-wise-form">';
    $html .= '<div class="alert alert-info mb-3">';
    $html .= '<i class="fas fa-info-circle"></i> Enter planning for <strong>' . htmlspecialchars($classInfo->class_short_name ?? $classInfo->class_name) . '</strong> - <strong>' . htmlspecialchars($subjectInfo->subject_name) . '</strong> across all terms';
    $html .= '</div>';
    
    $html .= '<div class="planning-cards">';
    
    $index = 0;
    foreach ($terms as $term) {
        $objective = isset($existingData[$term->term_session_id]) ? $existingData[$term->term_session_id] : '';
        
        $html .= '<div class="planning-card">';
        $html .= '<div class="planning-card-header">';
        $html .= '<h5><i class="fas fa-calendar-alt"></i> ' . htmlspecialchars($term->term_name) . '</h5>';
        $html .= '<small class="term-date">' . date('d M Y', strtotime($term->start_date)) . ' - ' . date('d M Y', strtotime($term->end_date)) . '</small>';
        $html .= '</div>';
        $html .= '<div class="planning-card-body">';
        
        // Hidden inputs for auto-save
        $html .= '<input type="hidden" name="planning[' . $index . '][class_id]" value="' . $class_id . '">';
        $html .= '<input type="hidden" name="planning[' . $index . '][subject_id]" value="' . $subject_id . '">';
        $html .= '<input type="hidden" name="planning[' . $index . '][term_session_id]" value="' . $term->term_session_id . '">';
        
        $html .= '<div class="form-group">';
        $html .= '<label>Objective / Planning</label>';
        $html .= '<textarea class="form-control summernote planning-item" name="planning[' . $index . '][objective]" rows="4">' . htmlspecialchars($objective) . '</textarea>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $index++;
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $this->response->setJSON(['html' => $html]);
}
}