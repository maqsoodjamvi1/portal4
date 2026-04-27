<?php
namespace App\Services;

class AttendanceService
{
    protected $db;
    protected $campusId;
    protected $sessionId;

    public function __construct()
    {
        $this->db = db_connect();
        $this->campusId = session('member_campusid');
        $this->sessionId = session('member_sessionid');
    }

    public function getTodaySummary()
    {
        $today = date('Y-m-d');

        $result = $this->db->table('attendance')
            ->select("
                SUM(CASE WHEN status='P' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status='A' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status='L' THEN 1 ELSE 0 END) as leaves
            ")
            ->where('date', $today)
            ->get()
            ->getRow();

        return $result ?: (object)[
            'present' => 0,
            'absent' => 0,
            'leaves' => 0
        ];
    }

    public function getPending()
    {
        $today = date('Y-m-d');

        return $this->db->table('mark_attendance')
            ->where('date', $today)
            ->where('status', 'pending')
            ->countAllResults();
    }
}