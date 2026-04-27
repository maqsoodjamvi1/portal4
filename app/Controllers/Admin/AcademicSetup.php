<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AcademicSetup extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text']);
        check_permission('admin-academic-session');
    }

   public function index()
{
    $schoolinfo = getSchoolInfo();
    $systemId = $schoolinfo->system_id ?? 0;
    
    // Get campus_id from session or from the schoolinfo if available
    $campusId = $this->session->get('campus_id') ?? 0;
    
    // If campus_id not in session, try to get from schoolinfo
    if (!$campusId && isset($schoolinfo->campus_id)) {
        $campusId = $schoolinfo->campus_id;
    }
    
    // If still no campus_id, get the first campus for this system
    if (!$campusId && $systemId) {
        try {
            $campusQuery = $this->db->table('campus')
                ->where('system_id', $systemId);
            
            $campusResult = $campusQuery->get();
            
            if ($campusResult && $campusResult->getNumRows() > 0) {
                $campus = $campusResult->getFirstRow();
                if ($campus && isset($campus->campus_id)) {
                    $campusId = $campus->campus_id;
                    // Store in session for future use
                    $this->session->set('campus_id', $campusId);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching campus: ' . $e->getMessage());
            // Continue without campus_id
        }
    }

    $data = [
        'system_id' => $systemId,
        'campus_id' => $campusId,
        'classes' => $this->getClasses($systemId),
        'sections' => $this->getSections($systemId),
        'subjects' => $this->getSubjects($systemId),
        'class_sections' => $campusId ? $this->getClassSections($campusId) : [],
        'section_subjects' => $campusId ? $this->getSectionSubjects($campusId) : []
    ];

    return view('admin/academic_setup', $data);
}


public function checkFeeTypes()
{
    $systemId = $this->request->getGet('system_id');
    
    if (!$systemId) {
        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
    }
    
    $count = $this->db->table('fee_type')
        ->where('system_id', $systemId)
        ->countAllResults();
    
    return $this->response->setJSON([
        'success' => true,
        'has_fee_types' => $count > 0,
        'count' => $count
    ]);
}


public function fetchClasses()
{
    $schoolinfo = getSchoolInfo();
    $systemId = $schoolinfo->system_id ?? 0;
    
    $classes = $this->getClasses($systemId);
    
    return $this->response->setJSON(['success' => true, 'data' => $classes]);
}

public function fetchSections()
{
    $schoolinfo = getSchoolInfo();
    $systemId = $schoolinfo->system_id ?? 0;
    
    $sections = $this->getSections($systemId);
    
    return $this->response->setJSON(['success' => true, 'data' => $sections]);
}

public function fetchSubjects()
{
    $schoolinfo = getSchoolInfo();
    $systemId = $schoolinfo->system_id ?? 0;
    
    $subjects = $this->getSubjects($systemId);
    
    return $this->response->setJSON(['success' => true, 'data' => $subjects]);
}

    private function getClasses($systemId)
    {
        if (!$systemId) return [];
        
        return $this->db->table('classes')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('class_name', 'ASC')
            ->get()->getResult();
    }

    private function getSections($systemId)
    {
        if (!$systemId) return [];
        
        return $this->db->table('sections')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('section_name', 'ASC')
            ->get()->getResult();
    }

    private function getSubjects($systemId)
    {
        if (!$systemId) return [];
        
        return $this->db->table('allsubject')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('subject_name', 'ASC')
            ->get()->getResult();
    }

    private function getClassSections($campusId)
    {
        if (!$campusId) return [];
        
        return $this->db->table('class_section cs')
            ->select('cs.*, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->get()->getResult();
    }

    private function getSectionSubjects($campusId)
    {
        if (!$campusId) return [];
        
        return $this->db->table('section_subjects ss')
            ->select('ss.*, c.class_name, s.section_name, sub.subject_name, cs.cls_sec_id')
            ->join('class_section cs', 'cs.cls_sec_id = ss.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->join('allsubject sub', 'sub.sid = ss.subject_id')
            ->where('cs.campus_id', $campusId)
            ->where('ss.status', 1)
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->orderBy('sub.subject_name', 'ASC')
            ->get()->getResult();
    }

    public function saveClasses()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $classes = $this->request->getPost('classes');
        
        if (empty($classes)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please add at least one class']);
        }

        $this->db->transBegin();

        try {
            foreach ($classes as $class) {
                $classData = [
                    'system_id' => $systemId,
                    'class_name' => $class['name'],
                    'class_short_name' => $class['short_name'] ?? '',
                    'detail' => $class['detail'] ?? '',
                    'status' => 1,
                    'created_date' => $now,
                    'user_id' => $userId
                ];
                
                $this->db->table('classes')->insert($classData);
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Classes saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function saveSections()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $sections = $this->request->getPost('sections');
        
        if (empty($sections)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please add at least one section']);
        }

        $this->db->transBegin();

        try {
            foreach ($sections as $section) {
                $sectionData = [
                    'system_id' => $systemId,
                    'section_name' => $section['name'],
                    'short_name' => $section['short_name'] ?? '',
                    'status' => 1,
                    'created_date' => $now,
                    'user_id' => $userId
                ];
                
                $this->db->table('sections')->insert($sectionData);
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Sections saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function saveSubjects()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $subjects = $this->request->getPost('subjects');
        
        if (empty($subjects)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please add at least one subject']);
        }

        $this->db->transBegin();

        try {
            foreach ($subjects as $subject) {
                $subjectData = [
                    'system_id' => $systemId,
                    'subject_name' => $subject['name'],
                    'subject_short_name' => $subject['short_name'] ?? '',
                    'status' => 1,
                    'created_date' => $now,
                    'user_id' => $userId
                ];
                
                $this->db->table('allsubject')->insert($subjectData);
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Subjects saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function saveClassSections()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $campusId = $this->session->get('campus_id') ?? 0;
        
        // If campus_id not in session, try to get from schoolinfo
        if (!$campusId && isset($schoolinfo->campus_id)) {
            $campusId = $schoolinfo->campus_id;
        }
        
        if (!$campusId) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Campus not found']);
        }
        
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $assignments = $this->request->getPost('assignments');
        
        if (empty($assignments)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please assign classes to sections']);
        }

        $this->db->transBegin();

        try {
            // Delete existing assignments for this campus
            $this->db->table('class_section')
                ->where('campus_id', $campusId)
                ->delete();
            
            // Insert new assignments
            foreach ($assignments as $assignment) {
                $classSectionData = [
                    'campus_id' => $campusId,
                    'class_id' => $assignment['class_id'],
                    'section_id' => $assignment['section_id'],
                    'status' => 1,
                    'created_date' => $now,
                    'user_id' => $userId
                ];
                
                $this->db->table('class_section')->insert($classSectionData);
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Class-Section assignments saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function saveSectionSubjects()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $campusId = $this->session->get('campus_id') ?? 0;
        
        if (!$campusId) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Campus not found']);
        }
        
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $assignments = $this->request->getPost('assignments');
        
        if (empty($assignments)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please assign subjects to sections']);
        }

        $this->db->transBegin();

        try {
            // Get all class_section IDs for this campus
            $classSections = $this->db->table('class_section')
                ->select('cls_sec_id')
                ->where('campus_id', $campusId)
                ->get()->getResult();
            
            $clsSecIds = array_map(function($cs) { return $cs->cls_sec_id; }, $classSections);
            
            if (!empty($clsSecIds)) {
                // Delete existing assignments
                $this->db->table('section_subjects')
                    ->whereIn('cls_sec_id', $clsSecIds)
                    ->delete();
            }
            
            // Insert new assignments
            foreach ($assignments as $assignment) {
                $sectionSubjectData = [
                    'cls_sec_id' => $assignment['cls_sec_id'],
                    'subject_id' => $assignment['subject_id'],
                    'status' => 1,
                    'created_date' => $now,
                    'user_id' => $userId
                ];
                
                $this->db->table('section_subjects')->insert($sectionSubjectData);
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Subject assignments saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

   


    public function getClassSectionsData()
    {
        $campusId = $this->session->get('campus_id') ?? 0;
        
        if (!$campusId) {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }
        
        $data = $this->getClassSections($campusId);
        
        return $this->response->setJSON(['success' => true, 'data' => $data]);
    }

    public function getSectionSubjectsData()
    {
        $campusId = $this->session->get('campus_id') ?? 0;
        
        if (!$campusId) {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }
        
        $data = $this->getSectionSubjects($campusId);
        
        return $this->response->setJSON(['success' => true, 'data' => $data]);
    }
}