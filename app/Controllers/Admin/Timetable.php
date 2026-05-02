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

// public function timetable_add()
// {
//     // Load users with teacher role
//     $teachers = $this->db->table('users u')
//         ->join('user_roles ur', 'u.id = ur.userID')
//         ->join('roles r', 'ur.roleID = r.id')
//         ->where('r.role_name_id', 'teacher') // Assuming 'teacher' is used in role_name_id
//         ->select('u.id, u.first_name, u.last_name')
//         ->orderBy('u.first_name', 'ASC')
//         ->get()
//         ->getResultArray();

//     // Build teacher list with full name
//     $teacherList = [];
//     foreach ($teachers as $teacher) {
//         $teacherList[] = [
//             'id' => $teacher['id'],
//             'name' => trim($teacher['first_name'] . ' ' . $teacher['last_name'])
//         ];
//     }

//     $data = [
//         'teachers' => $teacherList,
//         'selectedTeacher' => $this->request->getGet('teacher_id')
//     ];

//     return view('admin/timetable_add', $data);
// }

public function timetable_add()
{
    $teachers = [
        ['id' => 1, 'name' => 'Ali Ahmed'],
        ['id' => 2, 'name' => 'Sara Khan'],
    ];

    $sections = [
        ['cls_sec_id' => 1, 'class_name' => 'Grade 4', 'section_name' => 'A'],
        ['cls_sec_id' => 2, 'class_name' => 'Grade 5', 'section_name' => 'B'],
    ];

    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    $slots = [
        (object)[ 'slot_id' => 1, 'start_time' => '08:00:00', 'end_time' => '08:45:00' ],
        (object)[ 'slot_id' => 2, 'start_time' => '08:50:00', 'end_time' => '09:35:00' ],
    ];

    return view('admin/timetable_add', [
        'teachers' => $teachers,
        'selectedTeacher' => $this->request->getGet('teacher_id'),
        'sections' => $sections,
        'days' => $days,
        'slots' => $slots
    ]);
}
//     public function timetable_add()
// {
//     // Load teachers
//     $teacherQuery = $this->db->table('users u')
//         ->join('user_roles ur', 'u.id = ur.userID')
//         ->join('roles r', 'ur.roleID = r.id')
//         ->where('r.role_name_id', 'teacher')
//         ->select('u.id, u.first_name, u.last_name')
//         ->orderBy('u.first_name', 'ASC')
//         ->get()
//         ->getResultArray();

//     $teachers = [];
//     foreach ($teacherQuery as $teacher) {
//         $teachers[] = [
//             'id' => $teacher['id'],
//             'name' => trim($teacher['first_name'] . ' ' . $teacher['last_name'])
//         ];
//     }

//     // Dummy data for other required variables
//     $sections = []; // or fetch from DB
//     $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
//     $slots = []; // or fetch from DB

//     // Send to view
//     return view('admin/timetable_add', [
//         'teachers' => $teachers,
//         'selectedTeacher' => $this->request->getGet('teacher_id'),
//         'sections' => $sections,
//         'days' => $days,
//         'slots' => $slots
//     ]);
// }


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
    
    $db = \Config\Database::connect();

    try {
 $latestSql = "
  SELECT t1.*
  FROM teacher_subjects t1
  JOIN (
    SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
    FROM teacher_subjects
    WHERE status = 1
    GROUP BY cls_sec_id, sec_sub_id
  ) t2
    ON t2.cls_sec_id = t1.cls_sec_id
   AND t2.sec_sub_id = t1.sec_sub_id
   AND t2.max_sst   = t1.sst
";

$subjects = $db->table('section_subjects ss')
  ->select('ss.sec_sub_id, ss.subject_id, s.subject_name, u.first_name, u.last_name, ts.tid AS teacher_id')
  ->join('allsubject s', 's.sid = ss.subject_id')
  ->join("($latestSql) ts", 'ts.sec_sub_id = ss.sec_sub_id AND ts.cls_sec_id = ss.cls_sec_id', 'left')
  ->join('users u', 'u.id = ts.tid AND u.status = 1', 'left') // change u.id→u.user_id if your schema uses that
  ->where('ss.cls_sec_id', $cls_sec_id)
  ->where('ss.status', 1)
  ->orderBy('ss.sec_sub_id', 'ASC')
  ->get()->getResultArray();

        // Timetable for the class section
        $timetableRows = $db->table('time_table')
            ->select('day, slot_id, subject_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->get()->getResultArray();

        // Grouping timetable by day/slot
        $timetable = [];
        foreach ($timetableRows as $row) {
            $timetable[$row['day']][$row['slot_id']] = $row;
        }

        // Count teacher subject assignments
        $teacherLoad = [];
        foreach ($timetableRows as $row) {
            $sub = array_filter($subjects, fn($s) => $s['subject_id'] == $row['subject_id']);
            if (!empty($sub)) {
                $s = array_values($sub)[0];
                if (!empty($s['teacher_id'])) {
                    $key = $s['teacher_id'];
                    $name = trim($s['first_name'] . ' ' . $s['last_name']);
                    if (!isset($teacherLoad[$key])) {
                        $teacherLoad[$key] = ['name' => $name, 'count' => 0];
                    }
                    $teacherLoad[$key]['count']++;
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'subjects' => $subjects,
            'timetable' => $timetable,
            'teacherLoad' => array_values($teacherLoad),
        ]);
    } catch (\Throwable $e) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => $e->getMessage()
        ]);
    }
}


public function updateSlot()
{
    $cls_sec_id  = (int) $this->request->getPost('cls_sec_id');
    $day         = trim((string) $this->request->getPost('day'));
    $slot_id     = (int) $this->request->getPost('slot_id');
    $subject_raw = $this->request->getPost('subject_id');
    $subject_id  = ($subject_raw === '' || $subject_raw === null) ? null : (int)$subject_raw;
    $allowSameSubjectDay = ((int)$this->request->getPost('allow_same_subject_day') === 1);

    $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    if ($cls_sec_id <= 0 || $day === '' || !in_array($day, $validDays, true) || $slot_id <= 0) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid input.']);
    }

    $db = \Config\Database::connect();

    // If subject is NULL/empty → delete the slot
    if ($subject_id === null) {
        $db->table('time_table')
           ->where(['cls_sec_id' => $cls_sec_id, 'day' => $day, 'slot_id' => $slot_id])
           ->delete();

        return $this->response->setJSON([
            'success' => true,
            'msg'     => 'Slot cleared',
            'teacherLoad' => $this->calculateTeacherLoad($cls_sec_id),
        ]);
    }

    // 1) Ensure this subject exists in this section (and get sec_sub_id)
    $secSub = $db->table('section_subjects')
        ->select('sec_sub_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('subject_id', $subject_id)
        ->where('status', 1)
        ->get()->getRow();

    if (!$secSub) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Subject is not offered in this section.'
        ]);
    }
    $sec_sub_id = (int)$secSub->sec_sub_id;

    // 2) Prevent same subject twice in same day (optional constraint)
    if (!$allowSameSubjectDay) {
        $dup = $db->table('time_table')
            ->select('slot_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('day', $day)
            ->where('subject_id', $subject_id)
            ->where('slot_id !=', $slot_id)   // allow replacing same cell
            ->get()->getRow();

        if ($dup) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => "This subject is already scheduled on {$day} (slot {$dup->slot_id})."
            ]);
        }
    }

    // 3) Resolve the latest teacher for this (section, subject)
    $teacherRow = $db->query("
        SELECT ts.tid
        FROM teacher_subjects ts
        WHERE ts.cls_sec_id = ? AND ts.sec_sub_id = ? AND ts.status = 1
        ORDER BY ts.sst DESC
        LIMIT 1
    ", [$cls_sec_id, $sec_sub_id])->getRow();

    $tid = $teacherRow->tid ?? null;

    // 4) If a teacher is assigned, block cross-section conflicts at same day/slot
    if ($tid) {
        $conflict = $db->query("
            SELECT 1
            FROM time_table tt
            JOIN section_subjects ss
              ON ss.cls_sec_id = tt.cls_sec_id
             AND ss.subject_id = tt.subject_id
            JOIN (
                SELECT t1.cls_sec_id, t1.sec_sub_id, t1.tid
                FROM teacher_subjects t1
                JOIN (
                    SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
                    FROM teacher_subjects
                    WHERE status = 1
                    GROUP BY cls_sec_id, sec_sub_id
                ) t2
                  ON t2.cls_sec_id = t1.cls_sec_id
                 AND t2.sec_sub_id = t1.sec_sub_id
                 AND t2.max_sst   = t1.sst
            ) cur
              ON cur.cls_sec_id = ss.cls_sec_id
             AND cur.sec_sub_id = ss.sec_sub_id
            WHERE tt.day = ?
              AND tt.slot_id = ?
              AND cur.tid = ?
              AND NOT (tt.cls_sec_id = ? AND tt.day = ? AND tt.slot_id = ?)  -- ignore current cell
            LIMIT 1
        ", [$day, $slot_id, $tid, $cls_sec_id, $day, $slot_id])->getRow();

        if ($conflict) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Teacher conflict: this teacher is already scheduled in another class at the same day/slot.'
            ]);
        }
    }

    // 5) Enforce one row per natural key (cls_sec_id, day, slot_id)
    // Delete any legacy duplicates first, then insert the current subject.
    $db->table('time_table')
        ->where([
            'cls_sec_id' => $cls_sec_id,
            'day'        => $day,
            'slot_id'    => $slot_id
        ])
        ->delete();

    $db->table('time_table')->insert([
        'cls_sec_id'    => $cls_sec_id,
        'day'           => $day,
        'slot_id'       => $slot_id,
        'subject_id'    => $subject_id,
        'created_date'  => date('Y-m-d H:i:s'),
        'updated_date'  => date('Y-m-d H:i:s'),
        'user_id'       => (int)($this->session->get('member_userid') ?? 0),
    ]);

    return $this->response->setJSON([
        'success'     => true,
        'msg'         => 'Slot updated',
        'teacherLoad' => $this->calculateTeacherLoad($cls_sec_id),
    ]);
}

public function getSubjectConstraints()
{
    $cls_sec_id = (int) $this->request->getPost('cls_sec_id');
    $subject_id = (int) $this->request->getPost('subject_id');
    $allowSameSubjectDay = ((int)$this->request->getPost('allow_same_subject_day') === 1);

    if ($cls_sec_id <= 0 || $subject_id <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Invalid input.'
        ]);
    }

    $db = \Config\Database::connect();
    $blocked = [];
    $blockedKeys = [];

    // (A) Teacher conflict blocked slots (same teacher already teaching another class at day/slot)
    $teacherId = $this->resolveTeacherForSectionSubject($cls_sec_id, $subject_id);
    if ($teacherId) {
        $teacherBusy = $db->query("
            SELECT tt.day, tt.slot_id
            FROM time_table tt
            JOIN section_subjects ss
              ON ss.cls_sec_id = tt.cls_sec_id
             AND ss.subject_id = tt.subject_id
            JOIN (
                SELECT t1.cls_sec_id, t1.sec_sub_id, t1.tid
                FROM teacher_subjects t1
                JOIN (
                    SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
                    FROM teacher_subjects
                    WHERE status = 1
                    GROUP BY cls_sec_id, sec_sub_id
                ) t2
                  ON t2.cls_sec_id = t1.cls_sec_id
                 AND t2.sec_sub_id = t1.sec_sub_id
                 AND t2.max_sst   = t1.sst
            ) cur
              ON cur.cls_sec_id = ss.cls_sec_id
             AND cur.sec_sub_id = ss.sec_sub_id
            WHERE cur.tid = ?
              AND tt.cls_sec_id != ?
        ", [$teacherId, $cls_sec_id])->getResultArray();

        foreach ($teacherBusy as $r) {
            $k = $r['day'] . '|' . $r['slot_id'];
            if (isset($blockedKeys[$k])) {
                continue;
            }
            $blockedKeys[$k] = true;
            $blocked[] = [
                'day' => $r['day'],
                'slot_id' => (int)$r['slot_id'],
                'reason' => 'Teacher already occupied in another class.'
            ];
        }
    }

    // (B) Same-subject-per-day restriction (optional)
    if (!$allowSameSubjectDay) {
        $sameDayRows = $db->table('time_table')
            ->select('day, slot_id')
            ->where('cls_sec_id', $cls_sec_id)
            ->where('subject_id', $subject_id)
            ->get()
            ->getResultArray();

        foreach ($sameDayRows as $r) {
            $sameDay = (string)$r['day'];
            // block all other slots on that day
            $allSlots = $db->table('slots')
                ->select('slot_id')
                ->where('campus_id', (int)$this->session->get('member_campusid'))
                ->get()
                ->getResultArray();
            foreach ($allSlots as $s) {
                if ((int)$s['slot_id'] === (int)$r['slot_id']) {
                    continue; // keep already placed slot selectable
                }
                $k = $sameDay . '|' . (int)$s['slot_id'];
                if (isset($blockedKeys[$k])) {
                    continue;
                }
                $blockedKeys[$k] = true;
                $blocked[] = [
                    'day' => $sameDay,
                    'slot_id' => (int)$s['slot_id'],
                    'reason' => 'Same subject already exists on this day.'
                ];
            }
        }
    }

    return $this->response->setJSON([
        'success' => true,
        'blocked' => $blocked
    ]);
}

// Improved teacher conflict check
protected function checkTeacherConflict($cls_sec_id, $day, $slot_id, $subject_id)
{
    // Get all teachers assigned to this subject in this class section
    $teachers = $this->db->table('teacher_subjects ts')
        ->join('section_subjects ss', 'ss.sec_sub_id = ts.sec_sub_id')
        ->where('ss.cls_sec_id', $cls_sec_id)
        ->where('ss.subject_id', $subject_id)
        ->where('ts.status', 1)
        ->select('ts.tid')
        ->get()
        ->getResult();

    if (empty($teachers)) {
        return ['conflict' => false]; // No teachers assigned
    }

    $teacherIds = array_column($teachers, 'tid');

    // Check if any of these teachers are already assigned at this time in other classes
    $conflict = $this->db->table('time_table tt')
        ->join('section_subjects ss', 'ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id')
        ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id AND ts.status = 1')
        ->join('classes c', 'c.class_id = (SELECT class_id FROM class_section WHERE cls_sec_id = tt.cls_sec_id)')
        ->join('sections s', 's.section_id = (SELECT section_id FROM class_section WHERE cls_sec_id = tt.cls_sec_id)')
        ->join('allsubject sub', 'sub.sid = tt.subject_id')
        ->where('tt.day', $day)
        ->where('tt.slot_id', $slot_id)
        ->whereIn('ts.tid', $teacherIds)
        ->where('tt.cls_sec_id !=', $cls_sec_id)
        ->select('c.class_name, s.section_name, sub.subject_name')
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


private function calculateTeacherLoad($cls_sec_id)
{
    $db = \Config\Database::connect();

    $subjects = $db->table('section_subjects ss')
        ->select('ss.sec_sub_id, ss.subject_id, t.id AS teacher_id, t.first_name, t.last_name')
        ->join('teacher_subjects ts', 'ts.sec_sub_id = ss.sec_sub_id AND ts.cls_sec_id = ss.cls_sec_id', 'left')
        ->join('users t', 't.id = ts.tid AND t.status = 1', 'left')
        ->where('ss.cls_sec_id', $cls_sec_id)
        ->get()
        ->getResultArray();

    $timetableRows = $db->table('time_table')
        ->where('cls_sec_id', $cls_sec_id)
        ->get()
        ->getResultArray();

    $teacherLoad = [];

    foreach ($timetableRows as $row) {
        $match = array_filter($subjects, fn($s) => $s['subject_id'] == $row['subject_id']);
        if ($match) {
            $s = array_values($match)[0];
            if (!empty($s['teacher_id'])) {
                $key = $s['teacher_id'];
                $name = trim($s['first_name'] . ' ' . $s['last_name']);
                if (!isset($teacherLoad[$key])) {
                    $teacherLoad[$key] = ['name' => $name, 'count' => 0];
                }
                $teacherLoad[$key]['count']++;
            }
        }
    }

    return array_values($teacherLoad);
}

private function resolveTeacherForSectionSubject(int $cls_sec_id, int $subject_id): ?int
{
    $secSub = $this->db->table('section_subjects')
        ->select('sec_sub_id')
        ->where('cls_sec_id', $cls_sec_id)
        ->where('subject_id', $subject_id)
        ->where('status', 1)
        ->get()->getRow();

    if (!$secSub) {
        return null;
    }

    $row = $this->db->query("
        SELECT ts.tid
        FROM teacher_subjects ts
        WHERE ts.cls_sec_id = ? AND ts.sec_sub_id = ? AND ts.status = 1
        ORDER BY ts.sst DESC
        LIMIT 1
    ", [$cls_sec_id, (int)$secSub->sec_sub_id])->getRow();

    return $row ? (int)$row->tid : null;
}

public function getTeacherTimetable($teacherId = null)
{
    $builder = $this->db->table('teacher_timetable_view');
    
    if ($teacherId) {
        $builder->where('teacher_id', $teacherId);
    }
    
    $timetable = $builder->orderBy('day_order', 'ASC')
                         ->orderBy('start_time', 'ASC')
                         ->get()
                         ->getResultArray();

    // Group by day for better presentation
    $grouped = [];
    foreach ($timetable as $row) {
        $grouped[$row['day']][] = $row;
    }

    return $this->response->setJSON([
        'success' => true,
        'data' => $grouped
    ]);
}

public function exportTeacherTimetablePDF($teacherId)
{
    $timetable = $this->db->table('teacher_timetable_view')
                         ->where('teacher_id', $teacherId)
                         ->orderBy('day_order', 'ASC')
                         ->orderBy('start_time', 'ASC')
                         ->get()
                         ->getResultArray();

    $mpdf = new \Mpdf\Mpdf();
    $html = $this->renderTimetableHTML($timetable);
    $mpdf->WriteHTML($html);
    $mpdf->Output('teacher_timetable.pdf', 'D');
}

public function exportTeacherTimetableICal($teacherId)
{
    $timetable = $this->db->table('teacher_timetable_view')
                         ->where('teacher_id', $teacherId)
                         ->get()
                         ->getResultArray();

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="teacher_timetable.ics"');

    echo "BEGIN:VCALENDAR\n";
    echo "VERSION:2.0\n";
    echo "PRODID:-//School System//Teacher Timetable//EN\n";
    
    foreach ($timetable as $entry) {
        echo "BEGIN:VEVENT\n";
        echo "SUMMARY:{$entry['class_name']} - {$entry['subject_name']}\n";
        echo "DTSTART:".date('Ymd\THis', strtotime("next {$entry['day']} {$entry['start_time']}"))."\n";
        echo "DTEND:".date('Ymd\THis', strtotime("next {$entry['day']} {$entry['end_time']}"))."\n";
        echo "RRULE:FREQ=WEEKLY\n";
        echo "LOCATION:{$entry['section_name']}\n";
        echo "END:VEVENT\n";
    }
    
    echo "END:VCALENDAR\n";
    exit;
}

public function report()
{
    check_permission('admin-timetable');
    $campusId = (int) $this->session->get('member_campusid');

    $sections = $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_name', 'ASC')
        ->orderBy('s.section_name', 'ASC')
        ->get()
        ->getResultArray();

    $teachers = $this->db->table('users u')
        ->select('u.id, u.first_name, u.last_name')
        ->join('user_roles ur', 'ur.userID = u.id', 'inner')
        ->where('u.campus_id', $campusId)
        ->where('u.status', 1)
        ->where('ur.roleID', 5) // teacher role
        ->orderBy('u.first_name', 'ASC')
        ->orderBy('u.last_name', 'ASC')
        ->get()
        ->getResultArray();

    $timingType = $this->getActiveTimingTypeForCampus($campusId);

    return view('admin/timetable_report', [
        'sections' => $sections,
        'teachers' => $teachers,
        'timing_type_name' => $timingType['type_name'] ?? '',
        'working_days_display' => '',
    ]);
}

/** Display options for timetable report grid (slot labels + class-wise teacher line). */
private function timetableReportDisplayOptionsFromPost(): array
{
    $post = $this->request->getPost();
    $slot = isset($post['show_slot_time']) ? (int)$post['show_slot_time'] === 1 : false;
    $teach = array_key_exists('show_teacher_with_subject', $post)
        ? ((int)$post['show_teacher_with_subject'] === 1)
        : true;

    return [
        'show_slot_time' => $slot,
        'show_teacher_with_subject' => $teach,
    ];
}

private function timetableReportDisplayOptionsFromGet(): array
{
    $st = $this->request->getGet('show_slot_time');
    $tt = $this->request->getGet('show_teacher_with_subject');

    return [
        'show_slot_time' => ($st === '1' || $st === 1),
        'show_teacher_with_subject' => ($tt === null || $tt === '')
            ? true
            : ((string)$tt === '1'),
    ];
}

public function reportData()
{
    try {
        check_permission('admin-timetable');
        $mode = trim((string)$this->request->getPost('mode'));
        $clsPost = $this->request->getPost('cls_sec_id');
        $teacherPost = $this->request->getPost('teacher_id');
        $allClasses = is_string($clsPost) && strtolower(trim((string)$clsPost)) === 'all';
        $allTeachers = is_string($teacherPost) && strtolower(trim((string)$teacherPost)) === 'all';

        $payload = $this->buildReportPayload($mode, $clsPost ?? '', $teacherPost ?? '', $allClasses, $allTeachers);
        if (!$payload['success']) {
            return $this->response->setJSON(['success' => false, 'msg' => $payload['msg']]);
        }

        $displayOpts = $this->timetableReportDisplayOptionsFromPost();

        $timingBanner = view('admin/partials/timetable_report_timing_banner', [
            'timing_type_name' => $payload['timing_type_name'] ?? '',
            'working_days_display' => $payload['working_days_display'] ?? '',
        ]);

        $html = '';
        $blocks = $payload['blocks'] ?? [];
        foreach ($blocks as $i => $block) {
            $html .= view('admin/partials/timetable_report_grid', array_merge([
                'title' => $block['title'],
                'mode' => $block['mode'],
                'days' => $block['days'],
                'slots' => $block['slots'],
                'matrix' => $block['matrix'],
                'report_header' => $this->buildReportHeader(),
                'is_export' => false,
                'show_outer_header' => ($i === 0),
                'timing_banner' => ($i === 0) ? $timingBanner : '',
            ], $displayOpts));
            if ($i < count($blocks) - 1) {
                $html .= '<hr class="tt-report-section-break my-4">';
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'html' => $html,
        ]);
    } catch (\Throwable $e) {
        log_message('error', 'Timetable reportData: {message}', ['message' => $e->getMessage()]);
        $msg = 'Unable to load timetable report right now.';
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            $msg .= ' ' . $e->getMessage();
        }
        return $this->response->setJSON([
            'success' => false,
            'msg' => $msg,
        ]);
    }
}

public function reportExport()
{
    check_permission('admin-timetable');

    $mode = trim((string)$this->request->getGet('mode'));
    $format = strtolower(trim((string)$this->request->getGet('format'))); // pdf|excel
    $clsGet = $this->request->getGet('cls_sec_id');
    $teacherGet = $this->request->getGet('teacher_id');
    $allClasses = is_string($clsGet) && strtolower(trim((string)$clsGet)) === 'all';
    $allTeachers = is_string($teacherGet) && strtolower(trim((string)$teacherGet)) === 'all';

    $payload = $this->buildReportPayload($mode, $clsGet ?? '', $teacherGet ?? '', $allClasses, $allTeachers);
    if (!$payload['success']) {
        return redirect()->back()->with('error', $payload['msg']);
    }

    $displayOpts = $this->timetableReportDisplayOptionsFromGet();

    $titleSafe = preg_replace('/[^A-Za-z0-9\-_]+/', '_', $payload['export_title'] ?? 'Timetable_Report');

    $timingBanner = view('admin/partials/timetable_report_timing_banner', [
        'timing_type_name' => $payload['timing_type_name'] ?? '',
        'working_days_display' => $payload['working_days_display'] ?? '',
    ]);

    $html = '';
    $blocks = $payload['blocks'] ?? [];
    foreach ($blocks as $i => $block) {
        $html .= view('admin/partials/timetable_report_grid', array_merge([
            'title' => $block['title'],
            'mode' => $block['mode'],
            'days' => $block['days'],
            'slots' => $block['slots'],
            'matrix' => $block['matrix'],
            'report_header' => $this->buildReportHeader(),
            'is_export' => true,
            'show_outer_header' => ($i === 0),
            'timing_banner' => ($i === 0) ? $timingBanner : '',
        ], $displayOpts));
        if ($i < count($blocks) - 1) {
            $html .= '<hr class="tt-report-section-break" style="margin:16px 0;border-top:1px solid #ccc;">';
        }
    }

    if ($format === 'excel') {
        $filename = $titleSafe . '.xls';
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody('<html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>');
    }

    if ($format === 'pdf') {
        if (class_exists('\Mpdf\Mpdf')) {
            $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML('<h3>' . esc($titleSafe) . '</h3>' . $html);
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $titleSafe . '.pdf"')
                ->setBody($mpdf->Output('', 'S'));
        }
        // Fallback: HTML print view if mPDF unavailable
        return $this->response->setBody('<html><head><meta charset="utf-8"></head><body onload="window.print()">' . $html . '</body></html>');
    }

    return redirect()->back()->with('error', 'Invalid export format.');
}

/**
 * Latest active teacher assignment per (class section, section-subject), matching timetable UI logic.
 */
private function sqlLatestTeacherAssignments(): string
{
    return "
        SELECT t1.cls_sec_id, t1.sec_sub_id, t1.tid
        FROM teacher_subjects t1
        JOIN (
            SELECT cls_sec_id, sec_sub_id, MAX(sst) AS max_sst
            FROM teacher_subjects
            WHERE status = 1
            GROUP BY cls_sec_id, sec_sub_id
        ) t2
          ON t2.cls_sec_id = t1.cls_sec_id
         AND t2.sec_sub_id = t1.sec_sub_id
         AND t2.max_sst   = t1.sst
    ";
}

/** Active school_timing_types row for campus (status = 1). */
private function getActiveTimingTypeForCampus(int $campusId): ?array
{
    $row = $this->db->table('school_timing_types')
        ->select('type_id, type_name')
        ->where('campus_id', $campusId)
        ->where('status', 1)
        ->orderBy('type_id', 'ASC')
        ->get()
        ->getRowArray();

    return $row ?: null;
}

/** Calendar order for column headers. */
private function canonicalWeekdayOrder(): array
{
    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
}

/**
 * Day columns for one class section: only days where that section's school timing
 * (active type) has check-in and check-out set and different. No campus-wide union.
 */
private function resolveWorkingDayNamesForSection(int $campusId, int $clsSecId): array
{
    $canonical = $this->canonicalWeekdayOrder();
    $type = $this->getActiveTimingTypeForCampus($campusId);
    if (!$type) {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }

    $typeId = (int)$type['type_id'];
    $rows = $this->db->table('school_timings')
        ->select('dayname, checkin_timing, checkout_timing')
        ->where('cls_sec_id', $clsSecId)
        ->where('type_id', $typeId)
        ->get()
        ->getResultArray();

    $found = [];
    foreach ($rows as $row) {
        $cin = $row['checkin_timing'] ?? null;
        $cout = $row['checkout_timing'] ?? null;
        if ($cin === null || $cout === null || $cin === '' || $cout === '') {
            continue;
        }
        if ((string)$cin === (string)$cout) {
            continue;
        }
        $dn = trim((string)($row['dayname'] ?? ''));
        foreach ($canonical as $c) {
            if (strcasecmp($dn, $c) === 0) {
                $found[$c] = true;
                break;
            }
        }
    }

    $ordered = [];
    foreach ($canonical as $c) {
        if (isset($found[$c])) {
            $ordered[] = $c;
        }
    }

    if (!empty($ordered)) {
        return $ordered;
    }

    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
}

/**
 * For a teacher: union of working days for each class section they teach (timetable rows on this campus).
 * Saturday appears only if at least one of those sections has Saturday "on" in school timing.
 */
private function resolveWorkingDayNamesForTeacher(int $campusId, int $teacherId): array
{
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();
    $sql = "
        SELECT DISTINCT tt.cls_sec_id
        FROM time_table tt
        INNER JOIN class_section cs ON cs.cls_sec_id = tt.cls_sec_id
        INNER JOIN section_subjects ss
            ON ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id AND ss.status = 1
        INNER JOIN ({$latestTeacherSql}) lts
            ON lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id
        WHERE lts.tid = ?
          AND cs.campus_id = ?
    ";
    $sectionRows = $this->db->query($sql, [$teacherId, $campusId])->getResultArray();

    $merged = [];
    foreach ($sectionRows as $sr) {
        $cid = (int)($sr['cls_sec_id'] ?? 0);
        if ($cid <= 0) {
            continue;
        }
        foreach ($this->resolveWorkingDayNamesForSection($campusId, $cid) as $d) {
            $merged[$d] = true;
        }
    }

    $canonical = $this->canonicalWeekdayOrder();
    $ordered = [];
    foreach ($canonical as $c) {
        if (isset($merged[$c])) {
            $ordered[] = $c;
        }
    }

    if (!empty($ordered)) {
        return $ordered;
    }

    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
}

private function fetchSlotsForCampus(int $campusId): array
{
    return $this->db->table('slots')
        ->where('campus_id', $campusId)
        ->orderBy('start_time', 'ASC')
        ->get()
        ->getResultArray();
}

private function fetchSectionsForCampus(int $campusId): array
{
    return $this->db->table('class_section cs')
        ->select('cs.cls_sec_id, c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.campus_id', $campusId)
        ->where('cs.status', 1)
        ->orderBy('c.class_name', 'ASC')
        ->orderBy('s.section_name', 'ASC')
        ->get()
        ->getResultArray();
}

private function fetchTeachersForCampus(int $campusId): array
{
    return $this->db->table('users u')
        ->select('u.id, u.first_name, u.last_name')
        ->join('user_roles ur', 'ur.userID = u.id', 'inner')
        ->where('u.campus_id', $campusId)
        ->where('u.status', 1)
        ->where('ur.roleID', 5)
        ->orderBy('u.first_name', 'ASC')
        ->orderBy('u.last_name', 'ASC')
        ->get()
        ->getResultArray();
}

private function initializeTimetableMatrix(array $days, array $slots): array
{
    $matrix = [];
    foreach ($days as $day) {
        foreach ($slots as $slot) {
            $matrix[$day][(int)$slot['slot_id']] = [];
        }
    }

    return $matrix;
}

private function buildOneClassBlock(int $clsSecId, int $campusId, array $days, array $slots): ?array
{
    $exists = $this->db->table('class_section')
        ->where('cls_sec_id', $clsSecId)
        ->where('campus_id', $campusId)
        ->countAllResults();
    if ($exists < 1) {
        return null;
    }

    $section = $this->db->table('class_section cs')
        ->select('c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.cls_sec_id', $clsSecId)
        ->get()
        ->getRowArray();

    $title = 'Class Timetable Report: ' . (($section['class_name'] ?? 'Unknown') . ' - ' . ($section['section_name'] ?? ''));

    $matrix = $this->initializeTimetableMatrix($days, $slots);
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();

    $rows = $this->db->table('time_table tt')
        ->select("tt.time_table_id, tt.day, tt.slot_id, sub.subject_name, u.first_name AS teacher_first_name, u.last_name AS teacher_last_name")
        ->join('allsubject sub', 'sub.sid = tt.subject_id', 'inner')
        ->join('section_subjects ss', 'ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id', 'left')
        ->join("($latestTeacherSql) lts", 'lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id', 'left')
        ->join('users u', 'u.id = lts.tid', 'left')
        ->where('tt.cls_sec_id', $clsSecId)
        ->orderBy('tt.day', 'ASC')
        ->orderBy('tt.slot_id', 'ASC')
        ->orderBy('tt.time_table_id', 'DESC')
        ->get()
        ->getResultArray();

    foreach ($rows as $r) {
        $d = (string)$r['day'];
        $s = (int)$r['slot_id'];
        if (!isset($matrix[$d][$s])) {
            continue;
        }
        if (!empty($matrix[$d][$s])) {
            continue;
        }
        $matrix[$d][$s][] = [
            'subject_name' => (string)$r['subject_name'],
            'teacher_name' => trim((string)($r['teacher_first_name'] ?? '') . ' ' . (string)($r['teacher_last_name'] ?? '')),
            'class_label' => '',
        ];
    }

    return [
        'title' => $title,
        'mode' => 'class',
        'days' => $days,
        'slots' => $slots,
        'matrix' => $matrix,
    ];
}

private function buildOneTeacherBlock(int $teacherId, int $campusId, array $days, array $slots): ?array
{
    $teacher = $this->db->table('users')->select('first_name,last_name')->where('id', $teacherId)->get()->getRowArray();
    if ($teacher === null) {
        return null;
    }

    $title = 'Teacher Timetable Report: ' . trim((string)($teacher['first_name'] ?? '') . ' ' . (string)($teacher['last_name'] ?? ''));

    $matrix = $this->initializeTimetableMatrix($days, $slots);
    $latestTeacherSql = $this->sqlLatestTeacherAssignments();

    $rows = $this->db->table('time_table tt')
        ->select('tt.time_table_id, tt.day, tt.slot_id, sub.subject_name, c.class_name, s.section_name')
        ->join('class_section cs', 'cs.cls_sec_id = tt.cls_sec_id')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->join('allsubject sub', 'sub.sid = tt.subject_id', 'inner')
        ->join(
            'section_subjects ss',
            'ss.cls_sec_id = tt.cls_sec_id AND ss.subject_id = tt.subject_id AND ss.status = 1',
            'inner'
        )
        ->join("($latestTeacherSql) lts", 'lts.cls_sec_id = ss.cls_sec_id AND lts.sec_sub_id = ss.sec_sub_id', 'inner')
        ->where('lts.tid', $teacherId)
        ->where('cs.campus_id', $campusId)
        ->orderBy('tt.day', 'ASC')
        ->orderBy('tt.slot_id', 'ASC')
        ->orderBy('tt.time_table_id', 'DESC')
        ->get()
        ->getResultArray();

    foreach ($rows as $r) {
        $d = (string)$r['day'];
        $s = (int)$r['slot_id'];
        if (!isset($matrix[$d][$s])) {
            continue;
        }
        if (!empty($matrix[$d][$s])) {
            continue;
        }
        $matrix[$d][$s][] = [
            'subject_name' => (string)$r['subject_name'],
            'teacher_name' => '',
            'class_label' => trim((string)$r['class_name'] . ' - ' . (string)$r['section_name']),
        ];
    }

    return [
        'title' => $title,
        'mode' => 'teacher',
        'days' => $days,
        'slots' => $slots,
        'matrix' => $matrix,
    ];
}

private function finalizeReportPayload(array $blocks, string $mode, ?array $timingType, string $workingDaysBanner): array
{
    $timingName = $timingType['type_name'] ?? '';

    $exportTitle = 'Timetable_Report';
    if (count($blocks) === 1) {
        $exportTitle = preg_replace('/[^A-Za-z0-9\-_]+/', '_', $blocks[0]['title'] ?? 'Timetable_Report');
    } elseif ($mode === 'class') {
        $exportTitle = 'Timetable_All_Class_Sections';
    } elseif ($mode === 'teacher') {
        $exportTitle = 'Timetable_All_Teachers';
    }

    return [
        'success' => true,
        'mode' => $mode,
        'blocks' => $blocks,
        'timing_type_name' => $timingName,
        'working_days_display' => $workingDaysBanner,
        'export_title' => $exportTitle,
    ];
}

/**
 * @param int|string $clsSecId   Section id, or ignored when $allClasses is true
 * @param int|string $teacherId  User id, or ignored when $allTeachers is true
 */
private function buildReportPayload(string $mode, $clsSecId, $teacherId, bool $allClasses = false, bool $allTeachers = false): array
{
    $campusId = (int)$this->session->get('member_campusid');
    $timingType = $this->getActiveTimingTypeForCampus($campusId);
    $slots = $this->fetchSlotsForCampus($campusId);

    if (empty($slots)) {
        return ['success' => false, 'msg' => 'No slots found for this campus.'];
    }

    if ($mode === 'class') {
        if ($allClasses) {
            $sections = $this->fetchSectionsForCampus($campusId);
            if (empty($sections)) {
                return ['success' => false, 'msg' => 'No class sections found.'];
            }
            $blocks = [];
            foreach ($sections as $sec) {
                $cid = (int)$sec['cls_sec_id'];
                $days = $this->resolveWorkingDayNamesForSection($campusId, $cid);
                $b = $this->buildOneClassBlock($cid, $campusId, $days, $slots);
                if ($b !== null) {
                    $blocks[] = $b;
                }
            }
            if (empty($blocks)) {
                return ['success' => false, 'msg' => 'Could not load class sections for this campus.'];
            }

            $banner = 'Each grid lists only that section\'s working days (check-in ≠ check-out for the active timing type).';

            return $this->finalizeReportPayload($blocks, 'class', $timingType, $banner);
        }

        $id = (int)$clsSecId;
        if ($id <= 0) {
            return ['success' => false, 'msg' => 'Please select a class section.'];
        }

        $days = $this->resolveWorkingDayNamesForSection($campusId, $id);
        $block = $this->buildOneClassBlock($id, $campusId, $days, $slots);
        if ($block === null) {
            return ['success' => false, 'msg' => 'Class section not found for this campus.'];
        }

        return $this->finalizeReportPayload([$block], 'class', $timingType, implode(', ', $days));
    }

    if ($mode === 'teacher') {
        if ($allTeachers) {
            $teachers = $this->fetchTeachersForCampus($campusId);
            if (empty($teachers)) {
                return ['success' => false, 'msg' => 'No teachers found for this campus.'];
            }
            $blocks = [];
            foreach ($teachers as $t) {
                $tid = (int)$t['id'];
                $days = $this->resolveWorkingDayNamesForTeacher($campusId, $tid);
                $b = $this->buildOneTeacherBlock($tid, $campusId, $days, $slots);
                if ($b !== null) {
                    $blocks[] = $b;
                }
            }
            if (empty($blocks)) {
                return ['success' => false, 'msg' => 'Could not build teacher reports.'];
            }

            $banner = 'Each grid uses that teacher\'s working days (union of sections they teach; check-in ≠ check-out).';

            return $this->finalizeReportPayload($blocks, 'teacher', $timingType, $banner);
        }

        $tid = (int)$teacherId;
        if ($tid <= 0) {
            return ['success' => false, 'msg' => 'Please select a teacher.'];
        }

        $days = $this->resolveWorkingDayNamesForTeacher($campusId, $tid);
        $block = $this->buildOneTeacherBlock($tid, $campusId, $days, $slots);
        if ($block === null) {
            return ['success' => false, 'msg' => 'Teacher not found.'];
        }

        return $this->finalizeReportPayload([$block], 'teacher', $timingType, implode(', ', $days));
    }

    return ['success' => false, 'msg' => 'Invalid report mode.'];
}

private function buildReportHeader(): array
{
    $campusId = (int)$this->session->get('member_campusid');
    $campus = $this->db->table('campus')
        ->select('campus_name')
        ->where('campus_id', $campusId)
        ->get()
        ->getRowArray();

    $schoolName = 'School Management System';
    if (function_exists('getSchoolInfo')) {
        $si = getSchoolInfo();
        if (!empty($si->school_name)) {
            $schoolName = (string)$si->school_name;
        } elseif (!empty($si->name)) {
            $schoolName = (string)$si->name;
        }
    }

    return [
        'school_name' => $schoolName,
        'campus_name' => (string)($campus['campus_name'] ?? ''),
        'generated_at' => date('d M Y h:i A'),
    ];
}

// Save entire timetable
public function save()
{
    $clsSecId = $this->request->getPost('cls_sec_id');
    $timetable = json_decode($this->request->getPost('timetable'), true);
    $allowSameSubjectDay = ((int)$this->request->getPost('allow_same_subject_day') === 1);
    $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    try {
        if (empty($clsSecId) || !is_array($timetable)) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Invalid timetable payload.'
            ]);
        }

        // Pre-validate duplicate subject per day (optional)
        if (!$allowSameSubjectDay) {
            foreach ($timetable as $day => $slots) {
                if (!in_array((string)$day, $validDays, true) || !is_array($slots)) {
                    continue;
                }
                $seen = [];
                foreach ($slots as $slotId => $data) {
                    $sid = (int)($data['subject_id'] ?? 0);
                    if ($sid <= 0) {
                        continue;
                    }
                    if (isset($seen[$sid])) {
                        return $this->response->setJSON([
                            'success' => false,
                            'msg' => "Subject duplication not allowed: same subject appears multiple times on {$day}."
                        ]);
                    }
                    $seen[$sid] = true;
                }
            }
        }

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