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
    $day         = (int) $this->request->getPost('day');
    $slot_id     = (int) $this->request->getPost('slot_id');
    $subject_raw = $this->request->getPost('subject_id');
    $subject_id  = ($subject_raw === '' || $subject_raw === null) ? null : (int)$subject_raw;

    if ($cls_sec_id <= 0 || $day < 0 || $slot_id <= 0) {
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

    // 2) Prevent the same subject twice in the same day (same section)
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
            'msg'     => "This subject is already scheduled on day {$day} (slot {$dup->slot_id})."
        ]);
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
              AND NOT (tt.cls_sec_id = ? AND tt.subject_id = ?)  -- ignore the same cell we are replacing
            LIMIT 1
        ", [$day, $slot_id, $tid, $cls_sec_id, $subject_id])->getRow();

        if ($conflict) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Teacher conflict: this teacher is already scheduled in another class at the same day/slot.'
            ]);
        }
    }

    // 5) Upsert the slot (requires a UNIQUE key on (cls_sec_id, day, slot_id))
    $db->table('time_table')->replace([
        'cls_sec_id'  => $cls_sec_id,
        'day'         => $day,
        'slot_id'     => $slot_id,
        'subject_id'  => $subject_id,
    ]);

    return $this->response->setJSON([
        'success'     => true,
        'msg'         => 'Slot updated',
        'teacherLoad' => $this->calculateTeacherLoad($cls_sec_id),
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