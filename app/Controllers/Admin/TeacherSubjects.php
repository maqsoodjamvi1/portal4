<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TeacherSubjects extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'custom']);
    }


public function add()
{
    check_permission('admin-add-teacher-subject');
    $campus_id = $this->session->get('member_campusid');
    $schoolinfo = getSchoolInfo();

    $teachers = $this->getTeachersByRoleNameId((int) $campus_id);

    $class_sections = $this->db->table('class_section')->where(['campus_id' => $campus_id, 'status' => 1])->get()->getResult();
    $sections = [];
    foreach ($class_sections as $cs) {
        $class = $this->db->table('classes')->where('class_id', $cs->class_id)->get()->getRow();
        $section = $this->db->table('sections')->where('section_id', $cs->section_id)->get()->getRow();
        $sections[] = [
            'section_id' => $cs->cls_sec_id,
            'sectionclassname' => $class->class_name . ' (' . $section->section_name . ')'
        ];
    }

    $subjects = $this->db->table('allsubject')
        ->where('system_id', $schoolinfo->system_id)
        ->get()->getResult();

    return view('admin/teacher_subjects', [
        'infoteachers' => $teachers,
        'sectionsclassinfo' => $sections,
        'subjectinfo' => $subjects,
        'info' => []
    ]);
}


    public function index()
    {
        check_permission('admin-teacher-subjects');
        return view('admin/teacher_subjects');
    }

 public function getData()
{
    $campus_id = $this->session->get('member_campusid');
    
    if (!$campus_id) {
        return $this->response->setJSON(['error' => 'No campus ID']);
    }

    $schoolinfo = getSchoolInfo();
    $system_id = $schoolinfo->system_id;

    // Get all teachers by role_name_id = 5, supporting both current and legacy user_roles mappings.
    $teachers = $this->getTeachersByRoleNameId((int) $campus_id);

    // Get all subjects
    $subjects = $this->db->table('allsubject')
        ->select('sid, subject_name, subject_short_name')
        ->where('system_id', $system_id)
        ->where('status', 1)
        ->orderBy('subject_name', 'ASC')
        ->get()->getResult();

    // Get all class sections with class and section names
    $classSections = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_id, c.class_name, c.class_short_name, s.section_id, s.section_name, s.short_name as section_short_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.campus_id', $campus_id)
        ->where('cs.status', 1)
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('s.section_id', 'ASC')
        ->get()->getResult();

    // Get section-subject assignments
    $sectionSubjects = $this->db->table('section_subjects')
        ->where('status', 1)
        ->get()->getResult();

    $sectionSubjectMap = [];
    foreach ($sectionSubjects as $ss) {
        $sectionSubjectMap[$ss->cls_sec_id][$ss->subject_id] = $ss->sec_sub_id;
    }

    // Get teacher-subject assignments (subject teachers)
    $teacherSubjects = $this->db->query("
        SELECT ts.* 
        FROM teacher_subjects ts
        JOIN class_section cs ON cs.cls_sec_id = ts.cls_sec_id
        WHERE ts.status = 1 AND cs.campus_id = ?
    ", [$campus_id])->getResult();

    $teacherSubjectMap = [];
    foreach ($teacherSubjects as $ts) {
        $teacherSubjectMap[$ts->cls_sec_id][$ts->sec_sub_id] = $ts->tid;
    }

    // Get section teacher assignments (class incharges)
    $sectionTeachers = $this->db->query("
        SELECT ts.* 
        FROM teacher_section ts
        JOIN class_section cs ON cs.cls_sec_id = ts.cls_sec_id
        WHERE ts.status = 1 AND cs.campus_id = ?
    ", [$campus_id])->getResult();

    $sectionTeacherMap = [];
    foreach ($sectionTeachers as $st) {
        $sectionTeacherMap[$st->cls_sec_id] = $st->tid;
    }

    // Get subject counts per section for progress tracking
    $subjectCounts = [];
    foreach ($classSections as $section) {
        $cls_sec_id = $section->cls_sec_id;
        $totalSubjects = isset($sectionSubjectMap[$cls_sec_id]) ? count($sectionSubjectMap[$cls_sec_id]) : 0;
        $assignedSubjects = 0;
        
        if (isset($teacherSubjectMap[$cls_sec_id])) {
            $assignedSubjects = count($teacherSubjectMap[$cls_sec_id]);
        }
        
        $subjectCounts[$cls_sec_id] = [
            'total' => $totalSubjects,
            'assigned' => $assignedSubjects
        ];
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => [
            'teachers' => $teachers,
            'subjects' => $subjects,
            'classSections' => $classSections,
            'sectionSubjectMap' => $sectionSubjectMap,
            'teacherSubjectMap' => $teacherSubjectMap,
            'sectionTeacherMap' => $sectionTeacherMap,
            'subjectCounts' => $subjectCounts
        ]
    ]);
}

    public function saveAll()
{
    $campus_id = $this->session->get('member_campusid');
    $user_id = $this->session->get('member_userid');
    $date = date('Y-m-d H:i:s');

    $subject_assignments = json_decode($this->request->getPost('subject_assignments'), true) ?: [];
    $section_assignments = json_decode($this->request->getPost('section_assignments'), true) ?: [];

    $this->db->transStart();

    // Save subject teacher assignments (existing logic)
    foreach ($subject_assignments as $assignment) {
        $cls_sec_id = $assignment['cls_sec_id'];
        $subject_id = $assignment['subject_id'];
        $teacher_id = $assignment['teacher_id'];

        // Get or create section_subject
        $sectionSubject = $this->db->table('section_subjects')
            ->where([
                'cls_sec_id' => $cls_sec_id,
                'subject_id' => $subject_id,
                'status' => 1
            ])->get()->getRow();

        if (!$sectionSubject) {
            $this->db->table('section_subjects')->insert([
                'cls_sec_id' => $cls_sec_id,
                'subject_id' => $subject_id,
                'status' => 1,
                'created_date' => $date,
                'user_id' => $user_id
            ]);
            $sec_sub_id = $this->db->insertID();
        } else {
            $sec_sub_id = $sectionSubject->sec_sub_id;
        }

        // Update or insert teacher_subject
        $existing = $this->db->table('teacher_subjects')
            ->where([
                'cls_sec_id' => $cls_sec_id,
                'sec_sub_id' => $sec_sub_id,
                'status' => 1
            ])->get()->getRow();

        if ($existing) {
            if ($teacher_id) {
                $this->db->table('teacher_subjects')
                    ->where('sst', $existing->sst)
                    ->update([
                        'tid' => $teacher_id,
                        'updated_date' => $date
                    ]);
            } else {
                $this->db->table('teacher_subjects')
                    ->where('sst', $existing->sst)
                    ->update(['status' => 0]);
            }
        } else if ($teacher_id) {
            $this->db->table('teacher_subjects')->insert([
                'cls_sec_id' => $cls_sec_id,
                'sec_sub_id' => $sec_sub_id,
                'tid' => $teacher_id,
                'status' => 1,
                'created_date' => $date,
                'user_id' => $user_id
            ]);
        }
    }

    // Save section teacher assignments
    foreach ($section_assignments as $assignment) {
        $cls_sec_id = $assignment['cls_sec_id'];
        $teacher_id = $assignment['teacher_id'];

        // Deactivate existing assignment
        $this->db->table('teacher_section')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('status', 1)
            ->update(['status' => 0]);

        if ($teacher_id) {
            // Insert new assignment
            $this->db->table('teacher_section')->insert([
                'cls_sec_id' => $cls_sec_id,
                'tid' => $teacher_id,
                'status' => 1,
                'created_date' => $date,
                'user_id' => $user_id
            ]);
        }
    }

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to save assignments'
        ]);
    }

    return $this->response->setJSON([
        'success' => true,
        'message' => 'All assignments saved successfully'
    ]);
}

    public function save()
    {
        $campus_id = $this->session->get('member_campusid');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        $assignments = $this->request->getPost('assignments');
        
        if (!$assignments) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No assignments data received'
            ]);
        }

        $assignments = json_decode($assignments, true);
        
        if (!is_array($assignments)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid assignments format'
            ]);
        }

        $this->db->transStart();

        foreach ($assignments as $assignment) {
            $cls_sec_id = $assignment['cls_sec_id'];
            $subject_id = $assignment['subject_id'];
            $teacher_id = $assignment['teacher_id'];

            // First, ensure section_subject exists
            $sectionSubject = $this->db->table('section_subjects')
                ->where([
                    'cls_sec_id' => $cls_sec_id,
                    'subject_id' => $subject_id,
                    'status' => 1
                ])->get()->getRow();

            if (!$sectionSubject) {
                // Create section_subject if it doesn't exist
                $this->db->table('section_subjects')->insert([
                    'cls_sec_id' => $cls_sec_id,
                    'subject_id' => $subject_id,
                    'status' => 1,
                    'created_date' => $date,
                    'user_id' => $user_id
                ]);
                $sec_sub_id = $this->db->insertID();
            } else {
                $sec_sub_id = $sectionSubject->sec_sub_id;
            }

            // Check if teacher assignment already exists
            $existing = $this->db->table('teacher_subjects')
                ->where([
                    'cls_sec_id' => $cls_sec_id,
                    'sec_sub_id' => $sec_sub_id,
                    'status' => 1
                ])->get()->getRow();

            if ($existing) {
                if ($existing->tid != $teacher_id) {
                    // Update existing assignment
                    $this->db->table('teacher_subjects')
                        ->where('sst', $existing->sst)
                        ->update([
                            'tid' => $teacher_id,
                            'updated_date' => $date,
                            'user_id' => $user_id
                        ]);
                }
            } else {
                // Create new teacher assignment
                $this->db->table('teacher_subjects')->insert([
                    'cls_sec_id' => $cls_sec_id,
                    'sec_sub_id' => $sec_sub_id,
                    'tid' => $teacher_id,
                    'status' => 1,
                    'created_date' => $date,
                    'user_id' => $user_id
                ]);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save assignments'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Teacher assignments saved successfully'
        ]);
    }

public function getSectionTeachers()
{
    $campus_id = $this->session->get('member_campusid');
    
    if (!$campus_id) {
        return $this->response->setJSON(['error' => 'No campus ID']);
    }

    // Get all teachers by role_name_id = 5, supporting both current and legacy user_roles mappings.
    $teachers = $this->getTeachersByRoleNameId((int) $campus_id);

    // Get all class sections with class and section names
    $classSections = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_id, c.class_name, c.class_short_name, s.section_id, s.section_name, s.short_name as section_short_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.campus_id', $campus_id)
        ->where('cs.status', 1)
        ->orderBy('c.class_id', 'ASC')
        ->orderBy('s.section_id', 'ASC')
        ->get()->getResult();

    // Get section teacher assignments (class incharges)
    $sectionTeachers = $this->db->query("
        SELECT ts.* 
        FROM teacher_section ts
        JOIN class_section cs ON cs.cls_sec_id = ts.cls_sec_id
        WHERE ts.status = 1 AND cs.campus_id = ?
    ", [$campus_id])->getResult();

    $sectionTeacherMap = [];
    foreach ($sectionTeachers as $st) {
        $sectionTeacherMap[$st->cls_sec_id] = $st->tid;
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => [
            'teachers' => $teachers,
            'classSections' => $classSections,
            'sectionTeacherMap' => $sectionTeacherMap
        ]
    ]);
}

private function getTeachersByRoleNameId(int $campusId): array
{
    if ($campusId <= 0) {
        return [];
    }

    $teacherUserIds = $this->getTeacherUserIds($this->getCampusPlanId($campusId));
    if (empty($teacherUserIds)) {
        return [];
    }

    return $this->db->table('users')
        ->select('id, first_name, last_name, email')
        ->where('campus_id', $campusId)
        ->where('status', 1)
        ->whereIn('id', $teacherUserIds)
        ->orderBy('first_name', 'ASC')
        ->orderBy('last_name', 'ASC')
        ->get()
        ->getResult();
}

private function getTeacherUserIds(int $planId): array
{
    $teacherRoleNameId = 5;
    $teacherUserIds = [];

    // Current mapping: user_roles.roleID stores roles.id.
    $primary = $this->db->table('user_roles ur')
        ->distinct()
        ->select('ur.userID')
        ->join('roles r', 'r.id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
        ->where('r.role_name_id', $teacherRoleNameId)
        ->get()
        ->getResultArray();

    // Legacy mapping: user_roles.roleID stores roles.role_name_id.
    $legacy = $this->db->table('user_roles ur')
        ->distinct()
        ->select('ur.userID')
        ->join('roles r', 'r.role_name_id = ur.roleID' . ($planId > 0 ? ' AND r.plan_id = ' . $planId : ''), 'inner')
        ->where('r.role_name_id', $teacherRoleNameId)
        ->get()
        ->getResultArray();

    foreach (array_merge($primary, $legacy) as $row) {
        $userId = (int) ($row['userID'] ?? 0);
        if ($userId > 0) {
            $teacherUserIds[$userId] = $userId;
        }
    }

    return array_values($teacherUserIds);
}

private function getCampusPlanId(int $campusId): int
{
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