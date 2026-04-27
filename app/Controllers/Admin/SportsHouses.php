<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsHouses extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = db_connect();
        $this->session = session();
        helper(['form','url']);
    }

    public function index()
    {
        // View that displays cards instead of table
        return view('admin/sports/houses_list_cards');
    }

    public function data()
    {
        $campusId  = (int) (session('member_campusid')  ?? 0);
        $sessionId = (int) (session('member_sessionid') ?? 0);

        // Detect optional campus/session columns on students
        $hasStudentCampus  = $this->db->query("SHOW COLUMNS FROM students LIKE 'campus_id'")->getNumRows() > 0;
        $hasStudentSession = $this->db->query("SHOW COLUMNS FROM students LIKE 'session_id'")->getNumRows() > 0;

        // Build join filter for house > student relation
        $on = "s.house_id = h.house_id";
        if ($hasStudentCampus && $campusId > 0) {
            $on .= " AND s.campus_id = ".$this->db->escape($campusId);
        }
        if ($hasStudentSession && $sessionId > 0) {
            $on .= " AND s.session_id = ".$this->db->escape($sessionId);
        }

        // Main query - now using color_code
       $rows = $this->db->table('sports_houses h')
    ->select('h.house_id, h.house_name, h.color_code, h.status')
    ->select("COUNT(s.student_id) AS total_students", false)
    ->select("SUM(CASE WHEN LOWER(TRIM(s.gender)) = 'male' THEN 1 ELSE 0 END) AS male_count", false)
    ->select("SUM(CASE WHEN LOWER(TRIM(s.gender)) = 'female' THEN 1 ELSE 0 END) AS female_count", false)
    ->join('students s', 's.house_id = h.house_id', 'left')
    ->where('h.campus_id', $campusId)
    ->where('s.status', 1)
    ->groupBy('h.house_id')
    ->orderBy('h.house_name', 'ASC')
    ->get()->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }

    public function add()
    {
        return view('admin/sports/houses_form');
    }


public function members()
{
    if (! $this->request->isAJAX()) {
        return $this->response->setJSON([
            'ok'  => false,
            'msg' => 'Invalid request type',
        ]);
    }

    $houseId   = (int) $this->request->getPost('house_id');
    $campusId  = (int) (session('member_campusid')  ?? 0);
    $sessionId = (int) (session('member_sessionid') ?? 0);

    if ($houseId <= 0) {
        return $this->response->setJSON([
            'ok'  => false,
            'msg' => 'Invalid house id',
        ]);
    }

    if ($campusId <= 0 || $sessionId <= 0) {
        return $this->response->setJSON([
            'ok'  => false,
            'msg' => 'Campus / Session not found in session',
        ]);
    }

    $maleStudents   = [];
    $femaleStudents = [];

    // --------------------------------------------------------
    // 1) Load house students (also fetch DOB fields + db_status)
    // --------------------------------------------------------
    $rows = $this->db->table('students s')
        ->select("
            s.student_id,
            s.first_name,
            s.last_name,
            s.gender,
            s.date_of_birth,
            s.date_of_birth_age,
            s.db_status,
            c.class_short_name,
            sec.short_name AS section_name
        ")
        ->join('student_class sc', 'sc.student_id = s.student_id', 'inner')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
        ->where('s.house_id', $houseId)
        ->where('s.status', 1)
        ->where('sc.session_id', $sessionId)
        ->where('s.campus_id', $campusId)
        ->where('sc.status', 1)
        ->orderBy('s.first_name ASC')
        ->get()
        ->getResult();

    // If no students, return empty but OK
    if (empty($rows)) {
        return $this->response->setJSON([
            'ok'     => true,
            'male'   => [],
            'female' => [],
        ]);
    }

    // --------------------------------------------------------
    // 2) Build list of student IDs for participation count
    // --------------------------------------------------------
    $studentIds = [];
    foreach ($rows as $r) {
        $sid = (int) $r->student_id;
        if ($sid > 0) {
            $studentIds[] = $sid;
        }
    }

    $eventCountMap = [];

    if (!empty($studentIds)) {
        $eventRows = $this->db->table('sports_event_entries')
            ->select('student_id, COUNT(*) AS total_events')
            ->where('session_id', $sessionId)
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->get()
            ->getResult();

        foreach ($eventRows as $er) {
            $eventCountMap[(int)$er->student_id] = (int)$er->total_events;
        }
    }

    // --------------------------------------------------------
    // 3) Helper: calculate age in years with custom rounding
    // --------------------------------------------------------
    $today = new \DateTime(); // you can also use session date if needed

    $calcAge = function($dobStr) use ($today) {
        if (empty($dobStr) || $dobStr === '0000-00-00') {
            return null;
        }

        try {
            $dob = new \DateTime($dobStr);
        } catch (\Exception $e) {
            return null;
        }

        $diff   = $today->diff($dob);
        $years  = (int) $diff->y;
        $months = (int) $diff->m;

        // Rounding rule:
        // 0–5 months  -> floor year (keep $years)
        // 6–11 months -> ceil year  (years + 1)
        if ($months >= 6) {
            $years++;
        }

        return $years;
    };

    // --------------------------------------------------------
    // 4) Build response arrays with age + event counts
    // --------------------------------------------------------
    foreach ($rows as $r) {
        $studentId = (int) $r->student_id;

        // Choose DOB source:
        // if db_status = 1 use date_of_birth_age, else date_of_birth
        $dobSource = null;
        if ((int)$r->db_status === 1 && !empty($r->date_of_birth_age)) {
            $dobSource = $r->date_of_birth_age;
        } else {
            $dobSource = $r->date_of_birth;
        }

        $ageYears   = $calcAge($dobSource);
        $eventCount = $eventCountMap[$studentId] ?? 0;

        $item = [
            'student_id'    => $studentId,
            'name'          => trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')),
            'gender'        => strtolower((string) ($r->gender ?? '')),
            'class'         => $r->class_short_name ?? '',
            'section'       => $r->section_name ?? '',
            'age_years'     => $ageYears,         // <-- calculated age
            'event_count'   => (int) $eventCount, // <-- participation count
        ];

        if ($item['gender'] === 'female' || $item['gender'] === 'f') {
            $femaleStudents[] = $item;
        } else {
            $maleStudents[] = $item;
        }
    }

    return $this->response->setJSON([
        'ok'     => true,
        'male'   => $maleStudents,
        'female' => $femaleStudents,
    ]);
}


    public function edit($id)
    {
        $row = $this->db->table('sports_houses')
            ->where('house_id', (int)$id)
            ->get()->getRowArray();

        return view('admin/sports/houses_form', compact('row'));
    }

    public function save()
    {
        $campusId  = (int) session('member_campusid');
        $sessionId = (int) session('member_sessionid');

        $payload = [
            'campus_id'  => $campusId,
            'session_id' => $sessionId,
            'house_name' => trim((string)$this->request->getPost('house_name')),
            'color_code' => trim((string)$this->request->getPost('color_code')), // <-- updated
            'status'     => (int) $this->request->getPost('status') ?: 1,
            'user_id'    => (int) session('id'),
        ];

        $id = (int) $this->request->getPost('house_id');

        if ($id) {
            $this->db->table('sports_houses')->where('house_id', $id)->update($payload);
        } else {
            $this->db->table('sports_houses')->insert($payload);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    public function toggleStatus()
    {
        $id  = (int) $this->request->getPost('house_id');
        $row = $this->db->table('sports_houses')->where('house_id', $id)->get()->getRowArray();

        if (!$row) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'House not found']);
        }

        $new = (int)!((int)$row['status']);
        $this->db->table('sports_houses')->where('house_id', $id)->update(['status' => $new]);

        return $this->response->setJSON(['ok'=>true,'status'=>$new]);
    }
}
