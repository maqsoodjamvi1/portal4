<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsMapping extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = db_connect();
        $this->session = session();
        helper(['url']);
    }

    public function index()
    {
        // You likely already have a helper for class_section; we only render the page.
        return view('admin/sports/mapping_board');
    }

    /**
     * AJAX: Build board for a given class_section.
     * - Students come from student_class (sc)
     * - Current assignment from students.house_id
     * - Houses from sports_houses (status = 1, campus match)
     */
    public function board()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid']);
        }

        $clsSecId = (int) $this->request->getPost('cls_sec_id');
        if ($clsSecId <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Select a class-section']);
        }

        $campusId  = (int) ($this->session->get('member_campusid') ?? 1);
        $sessionId = (int) ($this->session->get('member_sessionid') ?? 1);

        // Houses (active, campus-scoped)
        $houses = $this->db->table('sports_houses')
            ->select('house_id, house_name, color_code')
            ->where('status', 1)
            ->where('campus_id', $campusId)
            ->orderBy('house_name', 'ASC')
            ->get()->getResultArray();

        // Students for this class_section (via student_class)
        // house assignment comes from students.house_id
        $qb = $this->db->table('students s')
            ->select('s.student_id, s.reg_no, s.first_name, s.last_name, s.house_id')
            ->join('student_class sc', 'sc.student_id = s.student_id', 'inner')
            ->where('sc.cls_sec_id', $clsSecId)
            ->where('sc.session_id', $sessionId)
            ->where('sc.status', 1);

        // Optional campus filters if present
        $hasSCampus = $this->db->query("SHOW COLUMNS FROM student_class LIKE 'campus_id'")->getNumRows() > 0;
        if ($hasSCampus) {
            $qb->where('sc.campus_id', $campusId);
        }
        $hasSStuCampus = $this->db->query("SHOW COLUMNS FROM students LIKE 'campus_id'")->getNumRows() > 0;
        if ($hasSStuCampus) {
            $qb->where('s.campus_id', $campusId);
        }

        $students = $qb->orderBy('s.first_name', 'ASC')->orderBy('s.last_name', 'ASC')->get()->getResultArray();

        // Buckets
        $houseBuckets = [];
        foreach ($houses as $h) {
            $hid = (int) $h['house_id'];
            $houseBuckets[$hid] = [
                'house_id'   => $hid,
                'house_name' => $h['house_name'],
                'short_name' => $h['short_name'] ?? '',
                'color_code' => (string) ($h['color_code'] ?? ''),
                'students'   => [],
            ];
        }

        $unassigned = [];
        foreach ($students as $st) {
            $sid = (int) $st['student_id'];
            $hid = (int) ($st['house_id'] ?? 0);

            $card = [
                'student_id'   => $sid,
                'student_name' => trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? '')) ?: ($st['first_name'] ?? ''),
                'reg_no'       => $st['reg_no'] ?? '',
            ];

            if ($hid > 0 && isset($houseBuckets[$hid])) {
                $houseBuckets[$hid]['students'][] = $card;
            } else {
                $unassigned[] = $card;
            }
        }

        // Counts
        $counts = ['unassigned' => count($unassigned)];
        foreach ($houseBuckets as $hid => $bucket) {
            $counts[$hid] = count($bucket['students']);
        }

        return $this->response->setJSON([
            'ok'         => true,
            'houses'     => array_values($houseBuckets),
            'unassigned' => $unassigned,
            'counts'     => $counts,
        ]);
    }


public function houseSheet()
{
    // Use your existing helpers to fill Class-Section select in the view
    $currentRole       = (array) (function_exists('currentUserRoles') ? currentUserRoles() : []);
    $sectionsClassInfo = in_array(5, $currentRole, true)
        ? (function_exists('teacherSubjectSections') ? teacherSubjectSections() : [])
        : (function_exists('userClassSections') ? userClassSections() : []);

    // Houses for legend colors
    $campusId  = (int) ($this->session->get('member_campusid') ?? 1);
    $sessionId = (int) ($this->session->get('member_sessionid') ?? 1);

    $houses = $this->db->table('sports_houses')
        ->select('house_id, house_name, color_code')
        ->where('status', 1)
        ->where('campus_id', $campusId)
        
        
        ->orderBy('house_name', 'ASC')
        ->get()->getResultArray();

    return view('admin/sports/house_sheet', [
        'sectionsclassinfo' => $sectionsClassInfo,
        'houses'            => $houses,
    ]);
}

/**
 * AJAX: return per-house student lists for a class_section (read-only “sheet”)
 * Uses students.house_id and student_class membership.
 */
public function houseSheetData()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid']);
    }

    $clsSecId = (int) $this->request->getPost('cls_sec_id');
    if ($clsSecId <= 0) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Select a class-section']);
    }

    $campusId  = (int) ($this->session->get('member_campusid') ?? 1);
    $sessionId = (int) ($this->session->get('member_sessionid') ?? 1);

    $houses = $this->db->table('sports_houses')
        ->select('house_id, house_name, short_name, color_code')
        ->where('status', 1)
        ->where('campus_id', $campusId)
        
        
        ->orderBy('house_name', 'ASC')
        ->get()->getResultArray();

    $studentsBuilder = $this->db->table('students s')
        ->select('s.student_id, s.reg_no, s.first_name, s.last_name, s.house_id')
        ->join('student_class sc', 'sc.student_id = s.student_id', 'inner')
        ->where('sc.cls_sec_id', $clsSecId)
        ->where('sc.session_id', $sessionId)
        ->orderBy('s.first_name', 'ASC')
        ->orderBy('s.last_name', 'ASC');

    // Optional campus scoping if present
    $scHasCampus = $this->db->query("SHOW COLUMNS FROM student_class LIKE 'campus_id'")->getNumRows() > 0;
    if ($scHasCampus) $studentsBuilder->where('sc.campus_id', $campusId);
    $sHasCampus  = $this->db->query("SHOW COLUMNS FROM students LIKE 'campus_id'")->getNumRows() > 0;
    if ($sHasCampus)  $studentsBuilder->where('s.campus_id', $campusId);

    $students = $studentsBuilder->get()->getResultArray();

    // Build buckets
    $byHouse = [];
    foreach ($houses as $h) {
        $byHouse[(int)$h['house_id']] = [
            'meta'     => $h,
            'students' => [],
        ];
    }
    $unassigned = [];

    foreach ($students as $st) {
        $name = trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''));
        $row  = [
            'student_id'   => (int)$st['student_id'],
            'reg_no'       => $st['reg_no'] ?? '',
            'student_name' => $name ?: ($st['first_name'] ?? ''),
        ];
        $hid = (int)($st['house_id'] ?? 0);
        if ($hid > 0 && isset($byHouse[$hid])) {
            $byHouse[$hid]['students'][] = $row;
        } else {
            $unassigned[] = $row;
        }
    }

    // Counts
    $counts = ['unassigned' => count($unassigned)];
    foreach ($byHouse as $hid => $bucket) {
        $counts[$hid] = count($bucket['students']);
    }

    return $this->response->setJSON([
        'ok'         => true,
        'houses'     => array_values(array_map(fn($v) => $v['meta'], $byHouse)),
        'byHouse'    => array_map(fn($v) => $v['students'], $byHouse),
        'unassigned' => $unassigned,
        'counts'     => $counts,
    ]);
}

    /**
     * AJAX: Move one student into a house (or unassign)
     * Persists in students.house_id
     */
   public function move()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid request']);
    }

    $studentId = (int) $this->request->getPost('student_id');
    $houseId   = (int) $this->request->getPost('house_id'); // 0 = unassign
    $clsSecId  = (int) $this->request->getPost('cls_sec_id');

    if ($studentId <= 0 || $clsSecId <= 0) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Missing student or class-section']);
    }

    $campusId  = (int) ($this->session->get('member_campusid') ?? 1);
    $sessionId = (int) ($this->session->get('member_sessionid') ?? 1);

    // 1) Ensure student belongs to this class_section (safety)
    $inSection = $this->db->table('student_class')
        ->select('student_id')
        ->where('student_id', $studentId)
        ->where('cls_sec_id', $clsSecId)
        ->where('session_id', $sessionId)
        ->limit(1)->get()->getRowArray();

    if (!$inSection) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Student not in the selected class-section']);
    }

    // 2) If assigning to a house (>0), make sure it exists (active & same campus)
    if ($houseId > 0) {
        $houseOk = $this->db->table('sports_houses')
            ->select('house_id')
            ->where('house_id', $houseId)
            ->where('status', 1)
            ->where('campus_id', $campusId)
            ->limit(1)->get()->getRowArray();

        if (!$houseOk) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid house']);
        }
    }

    // 3) Update students.house_id (keep WHERE minimal to avoid blocking updates)
    $this->db->transStart();

    $this->db->table('students')
        ->where('student_id', $studentId)
        ->update(['house_id' => $houseId]);  // 0 = unassigned

    $dbError = $this->db->error();
    $affected = $this->db->affectedRows();

    $this->db->transComplete();
    if ($this->db->transStatus() === false) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Transaction failed', 'dberr' => $dbError]);
    }

    if (!empty($dbError['code'])) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'DB error', 'dberr' => $dbError]);
    }

    // If 0 rows affected, it might already be set to this value — verify current value
    if ($affected === 0) {
        $cur = $this->db->table('students')
            ->select('house_id')
            ->where('student_id', $studentId)
            ->get()->getRowArray();
        if (!$cur) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Student not found after update']);
        }
        if ((int)($cur['house_id'] ?? 0) !== $houseId) {
            // Not updated and not matching -> treat as failure
            return $this->response->setJSON(['ok' => false, 'msg' => 'Update blocked (no matching row)']);
        }
        // else: already set; continue
    }

    // 4) Recompute counts for UI
    $houses = $this->db->table('sports_houses')
        ->select('house_id')
        ->where('status', 1)
        ->where('campus_id', $campusId)
        ->get()->getResultArray();
    $houseIds = array_map(fn($r) => (int)$r['house_id'], $houses);

    $rows = $this->db->table('students s')
        ->select('s.student_id, s.house_id')
        ->join('student_class sc', 'sc.student_id = s.student_id', 'inner')
        ->where('sc.cls_sec_id', $clsSecId)
        ->where('sc.session_id', $sessionId)
        ->get()->getResultArray();

    $counts = ['unassigned' => 0];
    foreach ($houseIds as $hid) $counts[$hid] = 0;

    foreach ($rows as $r) {
        $hid = (int) ($r['house_id'] ?? 0);
        if ($hid > 0 && array_key_exists($hid, $counts)) $counts[$hid]++;
        else $counts['unassigned']++;
    }

    return $this->response->setJSON(['ok' => true, 'counts' => $counts]);
}

}
