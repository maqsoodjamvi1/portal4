<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Timetable extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->session = session();
        $this->db = \Config\Database::connect();
        check_permission('admin-timetable');
    }

    public function index()
    {
        //check_permission('admin-view-timetable');
        return $this->viewTimetable();
    }

public function add()
{
    check_permission('admin-add-timetable');
    $campus_id = $this->session->get('member_campusid');

    // Get class sections with proper joins
    $builder = $this->db->table('class_section cs');
    $builder->select('cs.cls_sec_id, c.class_name, s.section_name');
    $builder->join('classes c', 'c.class_id = cs.class_id');
    $builder->join('sections s', 's.section_id = cs.section_id');
    $builder->where('cs.campus_id', $campus_id);
    $builder->where('cs.status', 1);
    $builder->orderBy('c.class_name, s.section_name');
    
    $sections = $builder->get()->getResultArray();

    // Debug output (remove in production)
    // print_r($sections); exit;

    // Get all slots for this campus
    $slots = $this->db->table('slots')
        ->where('campus_id', $campus_id)
        ->orderBy('start_time', 'ASC')
        ->get()
        ->getResult();

    $data = [
        'title' => 'Add Timetable',
        'sections' => $sections,
        'slots' => $slots,
        'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    ];

    return view('admin/timetable_add', $data);
}

    public function viewTimetable()
    {
        $campus_id = $this->session->get('member_campusid');
        
        // Get class sections with eager loading
        $builder = $this->db->table('class_section cs');
        $builder->select('cs.cls_sec_id, c.class_name, s.section_name');
        $builder->join('classes c', 'c.class_id = cs.class_id');
        $builder->join('sections s', 's.section_id = cs.section_id');
        $builder->where(['cs.campus_id' => $campus_id, 'cs.status' => 1]);
        
        $sections = $builder->get()->getResultArray();

        $data = [
            'title' => 'Timetable Management',
            'sections' => $sections
        ];

        return view('admin/timetable_view', $data);
    }



    public function manage()
    {
        check_permission('admin-timetable-edit');
        $campus_id = $this->session->get('member_campusid');

        // Get class sections with eager loading
        $builder = $this->db->table('class_section cs');
        $builder->select('cs.cls_sec_id, c.class_name, s.section_name');
        $builder->join('classes c', 'c.class_id = cs.class_id');
        $builder->join('sections s', 's.section_id = cs.section_id');
        $builder->where(['cs.campus_id' => $campus_id, 'cs.status' => 1]);
        
        $sections = $builder->get()->getResultArray();

        // Get all slots for this campus
        $slots = $this->db->table('slots')
            ->where('campus_id', $campus_id)
            ->orderBy('start_time', 'ASC')
            ->get()
            ->getResult();

        $data = [
            'title' => 'Manage Timetable',
            'sections' => $sections,
            'slots' => $slots,
            'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
        ];

        return view('admin/timetable_edit', $data);
    }


public function getSubjects()
{
    $cls_sec_id = $this->request->getPost('cls_sec_id');
    log_message('debug', "Received cls_sec_id = $cls_sec_id");

    try {
        // Step 1: Get all section subjects with their teacher info (if assigned)
        $results = $this->db->table('section_subjects ss')
            ->select('
                ss.subject_id, 
                ss.sec_sub_id, 
                s.subject_name, 
                u.id as teacher_id, 
                u.first_name, 
                u.last_name
            ')
            ->join('allsubject s', 's.sid = ss.subject_id')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1', 'left')
            ->join('users u', 'u.id = ts.tid', 'left')
            ->where('ss.cls_sec_id', $cls_sec_id)
            ->where('ss.status', 1)
            ->get()
            ->getResultArray();

       // log_message('debug', 'Fetched subjects (with possible teacher data): ' . print_r($results, true));

        return $this->response->setJSON([
            'success' => true,
            'subjects' => $results
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Error fetching subjects: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Database error: ' . $e->getMessage()
        ]);
    }
}






    public function getTimetable()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $campus_id = $this->session->get('member_campusid');

        // Get all slots
        $slots = $this->db->table('slots')
            ->where('campus_id', $campus_id)
            ->orderBy('start_time', 'ASC')
            ->get()
            ->getResultArray();

        // Get timetable data
        $timetable = $this->db->table('time_table tt')
            ->select('tt.*, s.subject_name, u.first_name as teacher_first_name, u.last_name as teacher_last_name')
            ->join('allsubject s', 's.sid = tt.subject_id')
            ->join('users u', 'u.id = s.teacher_id', 'left')
            ->where('tt.cls_sec_id', $cls_sec_id)
            ->get()
            ->getResultArray();

        // Organize by day and slot
        $organized = [];
        foreach ($timetable as $entry) {
            $organized[$entry['day']][$entry['slot_id']] = $entry;
        }

        return $this->response->setJSON([
            'success' => true,
            'slots' => $slots,
            'timetable' => $organized
        ]);
    }

    public function saveSlot()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $day = $this->request->getPost('day');
        $slot_id = $this->request->getPost('slot_id');
        $subject_id = $this->request->getPost('subject_id');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        // Check for teacher conflict
        $conflict = $this->checkTeacherConflict($cls_sec_id, $day, $slot_id, $subject_id);
        if ($conflict['conflict']) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => $conflict['message']
            ]);
        }

        // Check if slot already exists
        $existing = $this->db->table('time_table')
            ->where(['cls_sec_id' => $cls_sec_id, 'day' => $day, 'slot_id' => $slot_id])
            ->get()
            ->getRow();

        $data = [
            'cls_sec_id' => $cls_sec_id,
            'day' => $day,
            'slot_id' => $slot_id,
            'subject_id' => $subject_id,
            'user_id' => $user_id,
            'updated_date' => $date
        ];

        if ($existing) {
            // Update existing
            $this->db->table('time_table')
                ->where('time_table_id', $existing->time_table_id)
                ->update($data);
        } else {
            // Insert new
            $data['created_date'] = $date;
            $this->db->table('time_table')->insert($data);
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Timetable updated successfully'
        ]);
    }

   
    public function clearSlot()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $day = $this->request->getPost('day');
        $slot_id = $this->request->getPost('slot_id');

        $this->db->table('time_table')
            ->where(['cls_sec_id' => $cls_sec_id, 'day' => $day, 'slot_id' => $slot_id])
            ->delete();

        return $this->response->setJSON(['success' => true]);
    }

    protected function checkTeacherConflict($cls_sec_id, $day, $slot_id, $subject_id)
{
    // Step 1: Find sec_sub_id for the given class section and subject
    $sectionSubject = $this->db->table('section_subjects')
        ->select('sec_sub_id')
        ->where([
            'cls_sec_id' => $cls_sec_id,
            'subject_id' => $subject_id
        ])
        ->get()
        ->getRow();

    if (!$sectionSubject) {
        return ['conflict' => false]; // No mapping found
    }

    $sec_sub_id = $sectionSubject->sec_sub_id;

    // Step 2: Find the teacher assigned to this sec_sub_id
    $teacherRecord = $this->db->table('teacher_subjects')
        ->select('tid')
        ->where([
            'cls_sec_id' => $cls_sec_id,
            'sec_sub_id' => $sec_sub_id,
            'status' => 1
        ])
        ->get()
        ->getRow();

    if (!$teacherRecord || !$teacherRecord->tid) {
        return ['conflict' => false]; // No active teacher assigned
    }

    $teacher_id = $teacherRecord->tid;

    // Step 3: Check for teacher conflict
    $conflict = $this->db->table('time_table tt')
        ->select('c.class_name, s.section_name, sub.subject_name')
        ->join('class_section cs', 'cs.cls_sec_id = tt.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->join('allsubject sub', 'sub.sid = tt.subject_id')
        ->join('section_subjects ss', 'ss.subject_id = sub.sid AND ss.cls_sec_id = cs.cls_sec_id')
        ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1')
        ->where('tt.day', $day)
        ->where('tt.slot_id', $slot_id)
        ->where('ts.tid', $teacher_id)
        ->where('tt.cls_sec_id !=', $cls_sec_id)
        ->get()
        ->getRow();

    if ($conflict) {
        return [
            'conflict' => true,
            'message' => sprintf(
                'Teacher conflict: This teacher is already assigned to %s (%s - %s) at this time',
                $conflict->subject_name,
                $conflict->class_name,
                $conflict->section_name
            )
        ];
    }

    return ['conflict' => false];
}


    public function getTeacherSchedule()
    {
        $teacher_id = $this->request->getPost('teacher_id');
        
        $schedule = $this->db->table('time_table tt')
            ->select('tt.day, sl.start_time, sl.end_time, c.class_name, s.section_name, sub.subject_name')
            ->join('slots sl', 'sl.slot_id = tt.slot_id')
            ->join('class_section cs', 'cs.cls_sec_id = tt.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->join('allsubject sub', 'sub.sid = tt.subject_id')
            ->where('sub.teacher_id', $teacher_id)
            ->orderBy('tt.day, sl.start_time')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'schedule' => $schedule
        ]);
    }

   public function getSubjectsTimetable()
{
    $cls_sec_id = $this->request->getPost('cls_sec_id');
    $campus_id = $this->session->get('member_campusid');

    try {
        // Subjects (include all section subjects even without teachers)
        $subjects = $this->db->table('section_subjects ss')
            ->select('
                ss.subject_id, 
                ss.sec_sub_id, 
                s.subject_name, 
                u.id AS teacher_id, 
                u.first_name, 
                u.last_name
            ')
            ->join('allsubject s', 's.sid = ss.subject_id')
            ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1', 'left')
            ->join('users u', 'u.id = ts.tid', 'left')
            ->where('ss.cls_sec_id', $cls_sec_id)
            ->where('ss.status', 1)
            ->get()
            ->getResultArray();

        // Timetable
        $timetable = $this->db->table('time_table tt')
            ->select('tt.*, s.subject_name')
            ->join('allsubject s', 's.sid = tt.subject_id')
            ->where('tt.cls_sec_id', $cls_sec_id)
            ->get()
            ->getResultArray();

        // Organize timetable by [day][slot_id]
        $organized = [];
        foreach ($timetable as $entry) {
            $organized[$entry['day']][$entry['slot_id']] = $entry;
        }

        return $this->response->setJSON([
            'success' => true,
            'subjects' => $subjects,
            'timetable' => $organized
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Error in getSubjectsTimetable: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Server error.'
        ]);
    }
}


// Update a single timetable slot
public function updateSlot()
{
    $clsSecId = $this->request->getPost('cls_sec_id');
    $day = $this->request->getPost('day');
    $slotId = $this->request->getPost('slot_id');
    $subjectId = $this->request->getPost('subject_id');

    try {
        // Delete any existing entry for this slot
        $this->db->table('time_table')
            ->where('cls_sec_id', $clsSecId)
            ->where('day', $day)
            ->where('slot_id', $slotId)
            ->delete();

        // Insert new entry if subject is selected
        if ($subjectId) {
            $this->db->table('time_table')->insert([
                'cls_sec_id' => $clsSecId,
                'day' => $day,
                'slot_id' => $slotId,
                'subject_id' => $subjectId,
                'user_id' => session()->get('user_id')
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Slot updated successfully'
        ]);

    } catch (\Exception $e) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// Save entire timetable
public function save()
{
    $clsSecId = $this->request->getPost('cls_sec_id');
    $timetable = json_decode($this->request->getPost('timetable'), true);

    try {
        // Start transaction
        $this->db->transStart();

        // Clear existing timetable
        $this->db->table('time_table')
            ->where('cls_sec_id', $clsSecId)
            ->delete();

        // Insert new timetable entries
        foreach ($timetable as $day => $slots) {
            foreach ($slots as $slotId => $data) {
                $this->db->table('time_table')->insert([
                    'cls_sec_id' => $clsSecId,
                    'day' => $day,
                    'slot_id' => $slotId,
                    'subject_id' => $data['subject_id'],
                    'user_id' => session()->get('user_id')
                ]);
            }
        }

        // Complete transaction
        $this->db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Timetable saved successfully'
        ]);

    } catch (\Exception $e) {
        $this->db->transRollback();
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// Clear entire timetable
public function clear()
{
    $clsSecId = $this->request->getPost('cls_sec_id');

    try {
        $this->db->table('time_table')
            ->where('cls_sec_id', $clsSecId)
            ->delete();

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Timetable cleared successfully'
        ]);

    } catch (\Exception $e) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// Helper function to get subject name
private function getSubjectName($subjectId)
{
    $subject = $this->db->table('a_subject')
        ->select('subject_name')
        ->where('sid', $subjectId)
        ->get()
        ->getRow();

    return $subject ? $subject->subject_name : '';
}
}