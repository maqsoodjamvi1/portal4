<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ClassSection extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'custom']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        check_permission('admin-class-section');
        return view('admin/class_section');
    }

    public function getData()
    {
        $campus_id = $this->session->get('member_campusid');
        
        if (!$campus_id) {
            return $this->response->setJSON(['error' => 'No campus ID']);
        }

        $schoolinfo = getSchoolInfo();
        $system_id = $schoolinfo->system_id;

        // Get current session ID
        $currentSession = $this->db->table('academic_session')
            ->where('system_id', $system_id)
            ->orderBy('session_id', 'DESC')
            ->get()
            ->getRow();

        $session_id = $currentSession ? $currentSession->session_id : 0;

        // Get all classes ordered by class_id
        $classes = $this->db->table('classes')
            ->select('class_id, class_name, class_short_name')
            ->where('system_id', $system_id)
            ->where('status', 1)
            ->orderBy('class_id', 'ASC')
            ->get()->getResult();

        // Get all sections
        $sections = $this->db->table('sections')
            ->select('section_id, section_name, short_name')
            ->where('system_id', $system_id)
            ->where('status', 1)
            ->orderBy('section_id', 'ASC')
            ->get()->getResult();

        // Get existing class-section assignments
        $assignments = $this->db->table('class_section')
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->get()->getResult();

        $assignmentMap = [];
        $clsSecMap = [];
        foreach ($assignments as $a) {
            $key = $a->class_id . '_' . $a->section_id;
            $assignmentMap[$key] = $a->cls_sec_id;
            $clsSecMap[$a->cls_sec_id] = $a;
        }

        // Get student counts per class-section
        $studentCounts = [];
        if (!empty($clsSecMap)) {
            $clsSecIds = array_keys($clsSecMap);
            $countQuery = $this->db->table('student_class sc')
    ->select('sc.cls_sec_id, COUNT(DISTINCT sc.student_id) as student_count')
    ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
    ->whereIn('sc.cls_sec_id', $clsSecIds)
    ->where('sc.session_id', $session_id)
    ->where('sc.status', 1)
    ->where('cs.campus_id', $campus_id)
    ->groupBy('sc.cls_sec_id')
    ->get()->getResult();

            foreach ($countQuery as $row) {
                $studentCounts[$row->cls_sec_id] = $row->student_count;
            }
        }

        // Get class teachers
        $teacherMap = [];
        if (!empty($clsSecMap)) {
            $teacherQuery = $this->db->table('teacher_section ts')
                ->select('ts.cls_sec_id, u.id as teacher_id, u.first_name, u.last_name')
                ->join('users u', 'u.id = ts.tid')
                ->whereIn('ts.cls_sec_id', array_keys($clsSecMap))
                ->where('ts.status', 1)
                ->get()->getResult();

            foreach ($teacherQuery as $row) {
                $teacherMap[$row->cls_sec_id] = [
                    'id' => $row->teacher_id,
                    'name' => trim($row->first_name . ' ' . ($row->last_name ?? ''))
                ];
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'classes' => $classes,
                'sections' => $sections,
                'assignments' => $assignmentMap,
                'studentCounts' => $studentCounts,
                'teachers' => $teacherMap,
                'clsSecMap' => $clsSecMap
            ]
        ]);
    }

    public function update()
    {
        $campus_id = $this->session->get('member_campusid');
        $class_id = (int) $this->request->getPost('class_id');
        $section_id = (int) $this->request->getPost('section_id');
        $status = (int) $this->request->getPost('status');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        // Check if assignment exists
        $existing = $this->db->table('class_section')
            ->where([
                'class_id' => $class_id,
                'section_id' => $section_id,
                'campus_id' => $campus_id
            ])->get()->getRow();

        if ($existing) {
            // Check if there are students enrolled (if trying to deactivate)
            if ($status == 0) {
                $studentCount = $this->db->table('student_class')
                    ->where('cls_sec_id', $existing->cls_sec_id)
                    ->where('status', 1)
                    ->countAllResults();

                if ($studentCount > 0) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Cannot deactivate: {$studentCount} student(s) enrolled in this section"
                    ]);
                }
            }

            // Update status
            $this->db->table('class_section')
                ->where('cls_sec_id', $existing->cls_sec_id)
                ->update([
                    'status' => $status,
                    'updated_date' => $date,
                    'user_id' => $user_id
                ]);

            $clsSecId = $existing->cls_sec_id;
        } else {
            if ($status == 1) {
                // Insert new assignment
                $this->db->table('class_section')->insert([
                    'class_id' => $class_id,
                    'section_id' => $section_id,
                    'campus_id' => $campus_id,
                    'status' => 1,
                    'created_date' => $date,
                    'user_id' => $user_id
                ]);
                $clsSecId = $this->db->insertID();
            } else {
                $clsSecId = null;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'cls_sec_id' => $clsSecId,
            'message' => $status ? 'Section assigned to class' : 'Section unassigned from class'
        ]);
    }

    public function assignTeacher()
    {
        $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
        $teacher_id = (int) $this->request->getPost('teacher_id');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        // Delete existing teacher assignment
        $this->db->table('teacher_section')
            ->where('cls_sec_id', $cls_sec_id)
            ->delete();

        if ($teacher_id) {
            // Insert new teacher assignment
            $this->db->table('teacher_section')->insert([
                'cls_sec_id' => $cls_sec_id,
                'tid' => $teacher_id,
                'status' => 1,
                'created_date' => $date,
                'user_id' => $user_id
            ]);
        }

        // Get teacher name
        $teacher = null;
        if ($teacher_id) {
            $teacher = $this->db->table('users')
                ->select('first_name, last_name')
                ->where('id', $teacher_id)
                ->get()->getRow();
        }

        return $this->response->setJSON([
            'success' => true,
            'teacher_name' => $teacher ? trim($teacher->first_name . ' ' . ($teacher->last_name ?? '')) : ''
        ]);
    }

    public function getTeachers()
    {
        $campus_id = $this->session->get('member_campusid');
        
        $teachers = $this->db->table('users')
            ->select('id, first_name, last_name')
            ->where('campus_id', $campus_id)
            ->where('status', 1)
            ->orderBy('first_name')
            ->get()->getResult();

        return $this->response->setJSON($teachers);
    }
}