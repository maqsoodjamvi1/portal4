<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsLeaderboard extends BaseController
{
    protected $db;
    // ?? Points mapping (adjust here if needed)
    protected int $P1 = 5; // 1st
    protected int $P2 = 3; // 2nd
    protected int $P3 = 1; // 3rd

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url','form']);
    }

    public function index()
    {
        $campusId  = (int) (session('member_campusid')  ?? 0);
        $sessionId = (int) (session('member_sessionid') ?? 0);

        // Filters dropdown data
        $houses = $this->db->table('sports_houses')->select('house_id, house_name, color_code')
            ->orderBy('house_name','ASC')->get()->getResultArray();

        $events = $this->db->table('sports_events')->select('event_id, event_name, event_date')
            ->orderBy('event_date','ASC')->orderBy('event_name','ASC')->get()->getResultArray();

        return view('admin/sports/leaderboard', [
            'campus_id' => $campusId,
            'session_id'=> $sessionId,
            'houses'    => $houses,
            'events'    => $events,
            'p1' => $this->P1,
            'p2' => $this->P2,
            'p3' => $this->P3,
        ]);
    }

    // --------- HOUSE LEADERBOARD (cards) ----------
    public function houseData()
    {
        $campusId  = (int) (session('member_campusid')  ?? 0);
        $sessionId = (int) (session('member_sessionid') ?? 0);

        $eventId = (int) ($this->request->getPost('event_id') ?? 0);
        $houseId = (int) ($this->request->getPost('house_id') ?? 0);

        if ($sessionId <= 0 || $campusId <= 0) {
            return $this->response->setJSON(['ok'=>true,'rows'=>[]]);
        }

        // Build query
        $b = $this->db->table('sports_event_results r')
            ->select("
                r.house_id,
                h.house_name,
                h.color_code,
                SUM(CASE r.position WHEN 1 THEN {$this->P1} WHEN 2 THEN {$this->P2} WHEN 3 THEN {$this->P3} ELSE 0 END) AS total_points,
                SUM(CASE WHEN r.position = 1 THEN 1 ELSE 0 END) AS firsts,
                SUM(CASE WHEN r.position = 2 THEN 1 ELSE 0 END) AS seconds,
                SUM(CASE WHEN r.position = 3 THEN 1 ELSE 0 END) AS thirds,
                COUNT(*) AS podiums
            ", false)
            ->join('sports_houses h', 'h.house_id = r.house_id', 'left')
            ->where('r.session_id', $sessionId)
            ->where('r.campus_id', $campusId);

        if ($eventId > 0) {
            $b->where('r.event_id', $eventId);
        }
        if ($houseId > 0) {
            $b->where('r.house_id', $houseId);
        }

        $rows = $b->groupBy('r.house_id')
                  ->orderBy('total_points', 'DESC')
                  ->orderBy('firsts', 'DESC')
                  ->orderBy('seconds','DESC')
                  ->orderBy('thirds','DESC')
                  ->get()->getResultArray();

        return $this->response->setJSON(['ok'=>true,'rows'=>$rows]);
    }

    // --------- STUDENT LEADERBOARD (cards) ----------
    public function studentData()
    {
        $campusId  = (int) (session('member_campusid')  ?? 0);
        $sessionId = (int) (session('member_sessionid') ?? 0);

        $eventId = (int) ($this->request->getPost('event_id') ?? 0);
        $houseId = (int) ($this->request->getPost('house_id') ?? 0);

        if ($sessionId <= 0 || $campusId <= 0) {
            return $this->response->setJSON(['ok'=>true,'rows'=>[]]);
        }

        // Sum student points + counts per position
        $b = $this->db->table('sports_event_results r')
            ->select("
                s.student_id,
                s.first_name, s.last_name, s.profile_photo, s.date_of_birth,
                h.house_id, h.house_name, h.color_code,
                COALESCE(c.class_short_name, c.class_name) AS class_short,
                SUM(CASE r.position WHEN 1 THEN {$this->P1} WHEN 2 THEN {$this->P2} WHEN 3 THEN {$this->P3} ELSE 0 END) AS total_points,
                SUM(CASE WHEN r.position = 1 THEN 1 ELSE 0 END) AS firsts,
                SUM(CASE WHEN r.position = 2 THEN 1 ELSE 0 END) AS seconds,
                SUM(CASE WHEN r.position = 3 THEN 1 ELSE 0 END) AS thirds,
                COUNT(*) AS podiums
            ", false)
            ->join('students s', 's.student_id = r.student_id', 'left')
            ->join('sports_houses h', 'h.house_id = r.house_id', 'left')
            // class locked to current session
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = '.$this->db->escape($sessionId), 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->where('r.session_id', $sessionId)
            ->where('r.campus_id', $campusId);

        if ($eventId > 0) {
            $b->where('r.event_id', $eventId);
        }
        if ($houseId > 0) {
            $b->where('r.house_id', $houseId);
        }

        $rows = $b->groupBy('s.student_id')
                  // Sort primarily by points, then by # of 1sts, then name
                  ->orderBy('total_points','DESC')
                  ->orderBy('firsts','DESC')
                  ->orderBy('s.first_name','ASC')
                  ->orderBy('s.last_name','ASC')
                  ->limit(1000)
                  ->get()->getResultArray();

        return $this->response->setJSON(['ok'=>true,'rows'=>$rows]);
    }
}
