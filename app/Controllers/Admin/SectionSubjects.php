<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class SectionSubjects extends BaseController
{
    protected $db;
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index(): string
    {
        check_permission('admin-section-subjects');
        return view('admin/section_subjects');
    }

    public function getData()
    {
        $campus_id = session('member_campusid');
        
        if (!$campus_id) {
            return $this->response->setJSON(['error' => 'No campus ID']);
        }

        // Get system info
        $system = $this->db->table('system')->get()->getRow();
        $system_id = $system->system_id ?? null;

        // Fetch classes with their sections - GROUPED BY CLASS
        $classes = $this->db->table('classes c')
            ->select('c.class_id, c.class_name, c.class_short_name')
            ->where('c.system_id', $system_id)
            ->where('c.status', 1)
            ->orderBy('c.class_id')
            ->get()->getResult();

        $classData = [];
        foreach ($classes as $class) {
            // Get sections for this class
            $sections = $this->db->table('class_section cs')
                ->select('cs.cls_sec_id, s.section_id, s.section_name, s.short_name as section_short_name')
                ->join('sections s', 's.section_id = cs.section_id')
                ->where('cs.class_id', $class->class_id)
                ->where('cs.campus_id', $campus_id)
                ->where('cs.status', 1)
                ->where('s.status', 1)
                ->orderBy('s.section_name')
                ->get()->getResult();

            if (!empty($sections)) {
                $classData[] = [
                    'class' => $class,
                    'sections' => $sections
                ];
            }
        }

        // Get subjects
        $subjects = $this->db->table('allsubject')
            ->select('sid, subject_name, subject_short_name')
            ->where('system_id', $system_id)
            ->where('status', 1)
            ->orderBy('subject_name')
            ->get()->getResult();

        // Get assignments
        $assignments = $this->db->table('section_subjects')
            ->where('status', 1)
            ->get()->getResult();
        
        $assignMap = [];
        foreach ($assignments as $a) {
            $assignMap[$a->cls_sec_id][$a->subject_id] = $a->sec_sub_id;
        }

        // Get teachers
        $teachers = $this->db->table('users')
            ->select('id, first_name, last_name')
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->orderBy('first_name')
            ->get()->getResult();

        // Get teacher assignments
        $teacherAssignments = $this->db->table('teacher_subjects t')
            ->select('t.cls_sec_id, t.sec_sub_id, u.id as teacher_id, u.first_name, u.last_name')
            ->join('users u', 'u.id = t.tid')
            ->where('t.status', 1)
            ->get()->getResult();
        
        $teacherMap = [];
        foreach ($teacherAssignments as $ta) {
            $teacherMap[$ta->cls_sec_id][$ta->sec_sub_id] = [
                'id' => $ta->teacher_id,
                'name' => trim($ta->first_name . ' ' . ($ta->last_name ?? ''))
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'classes' => $classData,
                'subjects' => $subjects,
                'assignments' => $assignMap,
                'teachers' => $teachers,
                'teacherAssignments' => $teacherMap
            ]
        ]);
    }

    public function update()
    {
        $clsSecId = $this->request->getPost('cls_sec_id');
        $subjectId = $this->request->getPost('subject_id');
        $status = (int) $this->request->getPost('status');
        $user_id = session('member_userid');

        $existing = $this->db->table('section_subjects')
            ->where('cls_sec_id', $clsSecId)
            ->where('subject_id', $subjectId)
            ->get()->getRow();

        if ($existing) {
            $this->db->table('section_subjects')
                ->where('sec_sub_id', $existing->sec_sub_id)
                ->update([
                    'status' => $status,
                    'updated_date' => date('Y-m-d H:i:s')
                ]);
            $recordId = $existing->sec_sub_id;
        } else {
            $this->db->table('section_subjects')->insert([
                'cls_sec_id' => $clsSecId,
                'subject_id' => $subjectId,
                'status' => $status,
                'user_id' => $user_id,
                'created_date' => date('Y-m-d H:i:s')
            ]);
            $recordId = $this->db->insertID();
        }

        return $this->response->setJSON([
            'success' => true,
            'record_id' => $recordId
        ]);
    }

    public function assignTeacher()
    {
        $clsSecId = $this->request->getPost('cls_sec_id');
        $subjectId = $this->request->getPost('subject_id');
        $teacherId = $this->request->getPost('teacher_id');

        $sectionSubject = $this->db->table('section_subjects')
            ->where('cls_sec_id', $clsSecId)
            ->where('subject_id', $subjectId)
            ->where('status', 1)
            ->get()->getRow();

        if (!$sectionSubject) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Subject not assigned to this section'
            ]);
        }

        // Delete existing assignments
        $this->db->table('teacher_subjects')
            ->where('cls_sec_id', $clsSecId)
            ->where('sec_sub_id', $sectionSubject->sec_sub_id)
            ->delete();

        if ($teacherId) {
            $this->db->table('teacher_subjects')->insert([
                'cls_sec_id' => $clsSecId,
                'sec_sub_id' => $sectionSubject->sec_sub_id,
                'tid' => $teacherId,
                'status' => 1,
                'created_date' => date('Y-m-d H:i:s'),
                'user_id' => session('member_userid')
            ]);
        }

        $teacher = null;
        if ($teacherId) {
            $teacher = $this->db->table('users')
                ->select('first_name, last_name')
                ->where('id', $teacherId)
                ->get()->getRow();
        }

        return $this->response->setJSON([
            'success' => true,
            'teacher_name' => $teacher ? trim($teacher->first_name . ' ' . ($teacher->last_name ?? '')) : ''
        ]);
    }
}