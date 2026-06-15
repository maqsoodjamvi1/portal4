<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Config\Database;

class DatesheetController extends BaseController
{
    protected $db;
    protected $session;
    
    public function __construct()
    {
        $this->db = Database::connect();
        $this->session = session();

        helper(['url', 'form']);
    }
    
  

   public function index()
    {
        log_message('error', 'DATESHEET CONTROLLER HIT');
        
        // 1) Auth check
        $auth = $this->session->get('auth');

        if (!$auth || empty($auth['logged_in'])) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $role = $auth['role'];
        $userId = (int) $auth['user_id'];
        
        // 2) For parents: Get all children
        if ($role === 'parent') {
            $children = $this->getParentChildren($userId);

            $activeStudentId = (int) ($this->session->get('active_student_id') ?? 0);
            if ($activeStudentId <= 0 && ! empty($children)) {
                $activeStudentId = (int) $children[0]['student_id'];
                $this->session->set('active_student_id', $activeStudentId);
            }
            
            $data = [
                'role' => $role,
                'name' => $auth['name'] ?? 'User',
                'title' => 'Exam Datesheet',
                'children' => $children,
                'active_student_id' => $activeStudentId,
                'is_parent' => true
            ];
            
            // If a student is selected, get their datesheet
            if ($activeStudentId > 0) {
                $studentDatesheet = $this->getStudentDatesheet($activeStudentId);
                $data = array_merge($data, $studentDatesheet);
            }
            
            return view('frontend/datesheet/parent_view', $data);
            
        } 
        // 3) For students: Get their datesheet directly
        elseif ($role === 'student') {
            $studentDatesheet = $this->getStudentDatesheet($userId);
            
            return view('frontend/datesheet/student_view', array_merge([
                'role' => $role,
                'name' => $auth['name'] ?? 'User',
                'title' => 'My Exam Datesheet',
                'is_parent' => false,
                'children' => []
            ], $studentDatesheet));
        }
        
        return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
    }
    
    /**
     * Get all children for a parent
     */
    private function getParentChildren($parentId)
    {
        return $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, s.reg_no, 
                     cs.cls_sec_id, c.class_name, sec.section_name,
                     campus.campus_name')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->join('campus', 'campus.campus_id = s.campus_id', 'left')
            ->where('s.parent_id', $parentId)
            ->where('s.status', 1)
            ->groupBy('s.student_id')
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.first_name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get datesheet for a specific student
     */
    private function getStudentDatesheet($studentId)
    {
        // Get student info
        $student = $this->db->table('students s')
            ->select('s.*, c.class_name, sec.section_name, cs.cls_sec_id, 
                     campus.campus_name, campus.campus_id')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->join('campus', 'campus.campus_id = s.campus_id', 'left')
            ->where('s.student_id', $studentId)
            ->get()
            ->getRowArray();
            
        if (!$student) {
            return [
                'student' => null,
                'exam_name' => '',
                'datesheet' => [],
                'error' => 'Student not found'
            ];
        }
        
        // Get current session
        $sessionId = $this->getCurrentSessionId($studentId);
        
        if (!$sessionId) {
            return [
                'student' => $student,
                'exam_name' => '',
                'datesheet' => [],
                'error' => 'Academic session is not configured'
            ];
        }
        
        // Get active exam
        $exam = $this->db->table('exam')
            ->where('session_id', $sessionId)
            ->groupStart()
                ->where('campus_id', $student['campus_id'])
                ->orWhere('campus_id', 0)
                ->orWhere('campus_id IS NULL')
            ->groupEnd()
            ->whereIn('status', [0, 1])
            ->orderBy('eid', 'DESC')
            ->get()
            ->getRow();
        
        $datesheet = [];
        $examName = '';
        
        if ($exam && !empty($student['cls_sec_id'])) {
            $examName = $exam->exam_name ?? '';
            
            // Get datesheet data
           $datesheetData = $this->db->table('datesheet ds')
    ->select('ds.*, sub.subject_name, ss.subject_id')
    ->join('section_subjects ss', 'ss.sec_sub_id = ds.sec_sub_id AND ss.status = 1')
    ->join('allsubject sub', 'sub.sid = ss.subject_id')
    ->where('ds.eid', $exam->eid)
    ->where('ds.cls_sec_id', $student['cls_sec_id'])
    ->where('ds.total_marks !=', 0) // Add this line
    ->orderBy('ds.exam_date', 'ASC')
    ->get()
    ->getResultArray();
            
            // Group by date
            foreach ($datesheetData as $row) {
                $date = $row['exam_date'];
                $datesheet[$date][] = $row;
            }
        }
        
        return [
            'student' => $student,
            'exam_name' => $examName,
            'datesheet' => $datesheet,
            'session_id' => $sessionId,
            'error' => empty($datesheet) ? 'No datesheet available for current session' : null
        ];
    }
    
    /**
     * Get current session ID for student
     */
    private function getCurrentSessionId(int $studentId): ?int
    {
        // Get campus_id from student
        $student = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();
        
        if (!$student || empty($student['campus_id'])) {
            return null;
        }
        
        $campusId = (int) $student['campus_id'];
        
        // Get system_id from campus
        $campus = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', $campusId)
            ->get()
            ->getRowArray();
        
        if (!$campus || empty($campus['system_id'])) {
            return null;
        }
        
        $systemId = (int) $campus['system_id'];
        
        // Get current academic session
        $session = $this->db->table('academic_session')
            ->select('session_id')
            ->where('system_id', $systemId)
            ->where('CURDATE() BETWEEN start_date AND end_date', null, false)
            ->orderBy('start_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        
        return $session['session_id'] ?? null;
    }
    
    /**
     * AJAX endpoint to switch student
     */
    public function switchStudent($studentId)
    {
        $auth = $this->session->get('auth');
        
        if ($auth['role'] !== 'parent') {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }
        
        // Verify parent owns this student
        $exists = $this->db->table('students')
            ->where('student_id', $studentId)
            ->where('parent_id', $auth['user_id'])
            ->countAllResults();
            
        if ($exists) {
            $this->session->set('active_student_id', $studentId);
            return $this->response->setJSON(['success' => true]);
        }
        
        return $this->response->setJSON(['success' => false, 'message' => 'Student not found']);
    }
    /**
     * Get current session ID
     */
 


    
    /**
     * Fetch datesheet data in the required format
     */
    private function getDatesheetData($eid, $cls_sec_id)
    {
        if ($eid <= 0) {
            return [];
        }
        
        $datesheetinfo = $this->db->table('datesheet')
            ->where('eid', $eid)
            ->where('cls_sec_id', $cls_sec_id)
            ->orderBy('exam_date', 'ASC')
            ->orderBy('did', 'ASC')
            ->get()
            ->getResult();
        
        $subjectdatesheet = [];
        
        foreach ($datesheetinfo as $ds) {
            // Get subject information through section_subjects
            $secSub = $this->db->table('section_subjects')
                ->where('sec_sub_id', (int) $ds->sec_sub_id)
                ->where('status', 1)
                ->get()
                ->getRow();
                
            if (!$secSub) {
                continue;
            }
            
            // Get academic subject details
            $acadSub = $this->db->table('allsubject')
                ->where('sid', (int) $secSub->subject_id)
                ->get()
                ->getRow();
                
            if (!$acadSub) {
                continue;
            }
            
            // Format date and day
            $exam_date = \DateTime::createFromFormat('Y-m-d', (string)$ds->exam_date);
            $dateStr   = $exam_date ? $exam_date->format('d M Y') : (string)$ds->exam_date;
            $dayShort  = $exam_date ? $exam_date->format('D') : date('D', strtotime((string)$ds->exam_date));
            $dateDay   = $dateStr . ' (' . $dayShort . ')';
            
            // Format subject with marks (if total_marks > 0)
            $subject = (string)$acadSub->subject_name;
            $total_marks = (int)$ds->total_marks;
            if ($total_marks > 0) {
                $subject .= ' (' . $total_marks . ')';
            }
            
            // Add to array
            $subjectdatesheet[] = [
                'date_day' => $dateDay,
                'subject' => $subject,
                'syllabus' => (string)$ds->syllabus,
                'exam_date' => (string)$ds->exam_date,
                'day_short' => $dayShort,
                'subject_name' => (string)$acadSub->subject_name,
                'total_marks' => $total_marks,
                'start_time' => $ds->start_time ?? '',
                'end_time' => $ds->end_time ?? '',
                'room_no' => $ds->room_no ?? ''
            ];
        }
        
        return $subjectdatesheet;
    }
    
    /**
     * Get class section information
     */
    private function getClassSectionInfo($cls_sec_id)
    {
        $query = $this->db->table('class_section cs')
            ->select('cs.*, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.cls_sec_id', $cls_sec_id)
            ->get();
            
        $result = $query->getRowArray();
        
        if ($result) {
            $result['sectionclassname'] = $result['class_name'] . ' - ' . $result['section_name'];
        }
        
        return $result ?? [];
    }
    
    /**
     * Get student name
     */
    private function getStudentName($studentId)
    {
        $query = $this->db->table('students')
            ->select('first_name, last_name')
            ->where('student_id', $studentId)
            ->get();
            
        $student = $query->getRowArray();
        
        if ($student) {
            return trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        }
        
        return '';
    }
    
    /**
     * Verify that the parent owns the selected student
     */
    private function verifyParentOwnsStudent($studentId, $parentId)
    {
        $query = $this->db->table('students')
            ->select('student_id')
            ->where('student_id', $studentId)
            ->where('parent_id', $parentId)
            ->get();
            
        $student = $query->getRowArray();
        
        if (!$student) {
            // Parent doesn't own this student
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        return true;
    }
}