<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsTeams extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url', 'form']);
    }

    /**
     * Teams landing + "Add Team" (Event dropdown).
     * Optional filter: /admin/sports/teams?event_id=123
     */
    public function index()
    {
        // Only TEAM-type events (case-insensitive)
        $events = $this->db->table('sports_events')
            ->select('event_id, event_name, event_type')
            ->where("LOWER(event_type) = 'team'", null, false)
            ->orderBy('event_name', 'ASC')
            ->get()->getResultArray();

        $eventId = (int) ($this->request->getGet('event_id') ?? 0);
        $event   = null;

        if ($eventId > 0) {
            $event = $this->db->table('sports_events')
                ->select('*')
                ->where('event_id', $eventId)
                ->get()->getRowArray();

            // Validate selected event is TEAM-type
            if (!$event || strtolower($event['event_type'] ?? '') !== 'team') {
                return redirect()->to(base_url('admin/sports/teams'))
                    ->with('error', 'Selected event is not a team event.');
            }
        }

        $teamsQB = $this->db->table('sports_teams t')
            ->select('t.*, h.house_name, e.event_name')
            ->join('sports_houses h', 'h.house_id = t.house_id', 'left')
            ->join('sports_events e', 'e.event_id = t.event_id', 'left');

        if ($eventId > 0) {
            $teamsQB->where('t.event_id', $eventId);
        } else {
            // Small recent list when nothing is filtered
            $teamsQB->orderBy('t.team_id', 'DESC')->limit(20);
        }

        $teams = $teamsQB->orderBy('t.team_name', 'ASC')->get()->getResultArray();

        $houses = $this->db->table('sports_houses')
            ->orderBy('house_name', 'ASC')
            ->get()->getResultArray();

        return view('admin/sports/teams_index', [
            'event'   => $event,   // may be null
            'events'  => $events,  // dropdown (team-only)
            'teams'   => $teams,
            'houses'  => $houses,
        ]);
    }

    /**
     * View scoped to a single event id (team-only)
     */
    public function byEvent(int $eventId)
    {
        $event = $this->db->table('sports_events')
            ->select('*')
            ->where('event_id', $eventId)
            ->get()->getRowArray();

        if (!$event) {
            return redirect()->to(base_url('admin/sports/teams'));
        }

        if (strtolower($event['event_type'] ?? '') !== 'team') {
            return redirect()->to(base_url('admin/sports/teams'))
                ->with('error', 'Selected event is not a team event.');
        }

        $events = $this->db->table('sports_events')
            ->select('event_id, event_name, event_type')
            ->where("LOWER(event_type) = 'team'", null, false)
            ->orderBy('event_name', 'ASC')
            ->get()->getResultArray();

        $teams = $this->db->table('sports_teams t')
            ->select('t.*, h.house_name, e.event_name')
            ->join('sports_houses h', 'h.house_id = t.house_id', 'left')
            ->join('sports_events e', 'e.event_id = t.event_id', 'left')
            ->where('t.event_id', $eventId)
            ->orderBy('t.team_name', 'ASC')
            ->get()->getResultArray();

        $houses = $this->db->table('sports_houses')
            ->orderBy('house_name', 'ASC')
            ->get()->getResultArray();

        return view('admin/sports/teams_index', [
            'event'   => $event,
            'events'  => $events,
            'teams'   => $teams,
            'houses'  => $houses,
        ]);
    }

    /**
     * Create a team (ensure event is TEAM-type; unique name per event)
     */
    public function save()
    {
        $eventId   = (int) $this->request->getPost('event_id');
        $houseId   = (int) $this->request->getPost('house_id');
        $teamName  = trim((string) $this->request->getPost('team_name'));
        $coachName = trim((string) $this->request->getPost('coach_name'));
        $userId    = (int) (session('id') ?? 0) ?: null;

        if ($eventId <= 0 || $houseId <= 0 || $teamName === '') {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Select event, house and enter a team name.']);
        }

        // Ensure event exists and is TEAM-type
        $ev = $this->db->table('sports_events')
            ->select('event_id, event_type')
            ->where('event_id', $eventId)
            ->get()->getRowArray();

        if (!$ev || strtolower($ev['event_type'] ?? '') !== 'team') {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Event is not a team event.']);
        }

        // Uniqueness per event
        $dupe = $this->db->table('sports_teams')
            ->where(['event_id' => $eventId, 'team_name' => $teamName])
            ->countAllResults();

        if ($dupe > 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'A team with this name already exists for the selected event.']);
        }

        $ok = $this->db->table('sports_teams')->insert([
            'event_id'   => $eventId,
            'house_id'   => $houseId,
            'team_name'  => $teamName,
            'coach_name' => $coachName ?: null,
            'user_id'    => $userId,
        ]);

        return $this->response->setJSON(['ok' => (bool)$ok, 'msg' => $ok ? null : 'Insert failed.']);
    }

    public function delete()
    {
        $teamId = (int) $this->request->getPost('team_id');
        if ($teamId <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid team id.']);
        }
        $this->db->table('sports_teams')->delete(['team_id' => $teamId]);
        return $this->response->setJSON(['ok' => true]);
    }
}
