<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsResultsReport extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url', 'form']);
    }

    /**
     * Route: GET admin/sports/reports/points
     * Renders the view (filters + containers). Data loads via AJAX -> data()
     */
    public function index()
    {
        $campusId  = (int) (session('member_campusid')  ?? 0);
        $sessionId = (int) (session('member_sessionid') ?? 0);

        // Events for current session (fallback to all if session not set)
        $builder = $this->db->table('sports_events')
            ->select('event_id, event_name, event_date')
            ->orderBy('event_date', 'DESC')
            ->orderBy('event_id', 'DESC');

        if ($campusId > 0)  $builder->where('campus_id', $campusId);
        if ($sessionId > 0) $builder->where('session_id', $sessionId);

        $events = $builder->get()->getResultArray();

        // If no events due to strict filters, show recent all (avoids empty dropdown).
        if (empty($events)) {
            $events = $this->db->table('sports_events')
                ->select('event_id, event_name, event_date')
                ->orderBy('event_date', 'DESC')
                ->orderBy('event_id', 'DESC')
                ->limit(100)
                ->get()->getResultArray();
        }

        return view('admin/sports/points', [
            'events' => $events,
        ]);
    }

    /**
     * Route: POST admin/sports/reports/points/data
     * Input:
     *   - event_id (optional, int) 0=all
     *   - order_by (optional, 'position'|'event'|'house')
     */
    public function data()
    {
        $campusId  = (int) (session('member_campusid')  ?? 0);
        $sessionId = (int) (session('member_sessionid') ?? 0);

        $eventId   = (int) $this->request->getPost('event_id');
        $orderBy   = trim((string) $this->request->getPost('order_by'));
        if (!in_array($orderBy, ['position','event','house'], true)) {
            $orderBy = 'position';
        }

        // ---- House totals (sum of points) ----
        // Points source: prefer r.points if >0, else derive from scoring rules by event_type/position
        // Schema refs: sports_event_results, sports_scoring_rules, sports_events, sports_houses
        $hb = $this->db->table('sports_event_results r')
            ->select('r.house_id, h.house_name, h.color_code')
            ->select('SUM( CASE WHEN r.points IS NOT NULL AND r.points > 0 THEN r.points ELSE COALESCE(scr.points,0) END ) AS total_points', false)
            ->join('sports_events e', 'e.event_id = r.event_id', 'left')
            ->join('sports_houses h', 'h.house_id = r.house_id', 'left')
            ->join(
                'sports_scoring_rules scr',
                "scr.campus_id = r.campus_id AND scr.session_id = r.session_id AND scr.position_no = r.position AND scr.event_type = e.event_type",
                'left'
            )
            ->where('r.session_id', $sessionId);

        if ($campusId > 0) $hb->where('r.campus_id', $campusId);
        if ($eventId > 0)  $hb->where('r.event_id', $eventId);

        $houseTotals = $hb->groupBy('r.house_id, h.house_name, h.color_code')
                          ->orderBy('total_points', 'DESC')
                          ->get()->getResultArray();

        // ---- Position holders list (cards) ----
        // Get each result row + student & event info + participation_count (in current session).
        $rb = $this->db->table('sports_event_results r')
            ->select('r.result_id, r.event_id, r.team_id, r.student_id, r.house_id, r.position, r.points, r.rank_shared')
            ->select('e.event_name, e.event_type')
            ->select('h.house_name, h.color_code')
            ->select('s.first_name, s.last_name, s.profile_photo, s.date_of_birth')
            ->select('COALESCE(c.class_short_name, c.class_name) AS class_short', false)
            ->select("(SELECT COUNT(*) FROM sports_event_entries se WHERE se.student_id = r.student_id AND se.session_id = ".$this->db->escape($sessionId).") AS participation_count", false)
            ->join('sports_events e', 'e.event_id = r.event_id', 'left')
            ->join('sports_houses h', 'h.house_id = r.house_id', 'left')
            ->join('students s', 's.student_id = r.student_id', 'left')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = '.$this->db->escape($sessionId), 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->where('r.session_id', $sessionId);

        if ($campusId > 0) $rb->where('r.campus_id', $campusId);
        if ($eventId > 0)  $rb->where('r.event_id', $eventId);

        // Ordering
        if ($orderBy === 'event') {
            $rb->orderBy('r.event_id', 'ASC')->orderBy('r.position', 'ASC');
        } elseif ($orderBy === 'house') {
            $rb->orderBy('r.house_id', 'ASC')->orderBy('r.position', 'ASC');
        } else { // position
            $rb->orderBy('r.position', 'ASC')->orderBy('r.event_id', 'ASC');
        }

        $rows = $rb->get()->getResultArray();

        return $this->response->setJSON([
            'ok'          => true,
            'houseTotals' => $houseTotals,
            'cards'       => $rows,
        ]);
    }
}
