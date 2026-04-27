<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsEntries extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url', 'form']);
    }

    /**
     * Keep signature compatible with BaseController::index()
     * Route users to the Events page (entries are always per-event).
     */
    public function index()
    {
        
         return view('admin/sports/entries');
    }

    /**
     * Show entries for a specific event (team or individual mode)
     */
  public function byEvent(int $eventId)
{
    $eventId   = (int) $eventId;
    $sessionId = (int) (session('member_sessionid') ?? 0);
    $campusId  = (int) (session('member_campusid')  ?? 0);

    $event = $this->db->table('sports_events')->where('event_id', $eventId)->get()->getRowArray();
    if (!$event) {
        return redirect()->to(base_url('admin/sports/events'))->with('error','Event not found');
    }

    $subCount = "(SELECT COUNT(DISTINCT ee2.event_id)
                  FROM sports_event_entries ee2
                  WHERE ee2.student_id = s.student_id"
                  . ($sessionId ? " AND ee2.session_id = ".$this->db->escape($sessionId) : "")
                  . ($campusId  ? " AND ee2.campus_id  = ".$this->db->escape($campusId)  : "")
                . ")";

    $entries = $this->db->table('sports_event_entries e')
        ->select('e.entry_id, e.event_id, e.team_id, e.student_id, e.house_id')
        ->select('h.house_name, t.team_name')
        ->select("s.first_name, s.last_name, s.profile_photo, s.date_of_birth", false)
        // class via student_class -> class_section -> classes (session-scoped)
        ->select("COALESCE(c.class_short_name, c.class_name) AS class_short", false)
        // distinct events this student has participated in (scoped to your campus/session)
        ->select("$subCount AS participation_count", false)
        ->join('sports_houses h', 'h.house_id = e.house_id', 'left')
        ->join('sports_teams t',  't.team_id  = e.team_id',  'left')
        ->join('students s',      's.student_id = e.student_id', 'left')
        ->join('student_class sc','sc.student_id = s.student_id' . ($sessionId ? ' AND sc.session_id = '.$this->db->escape($sessionId) : ''), 'left')
        ->join('class_section cs','cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c',       'c.class_id   = cs.class_id',    'left')
        ->where('e.event_id', $eventId)
        ->orderBy('c.class_id','ASC')
        ->orderBy('s.first_name','ASC')
        ->get()->getResultArray();

    $teams = [];
    if (strtolower($event['mode'] ?? 'individual') === 'team') {
        $teams = $this->db->table('sports_teams')->where('event_id',$eventId)->orderBy('team_name','ASC')->get()->getResultArray();
    }

    return view('admin/sports/entries', [
        'entries' => $entries, // now includes class_short, participation_count
        'eventId' => $eventId,
        'event'   => $event,
        'teams'   => $teams,
    ]);
}


    /**
     * Add a participant (team for team mode, or student for individual mode)
     */
    public function add()
    {
        $campusId  = (int) (session('member_campusid') ?? 0) ?: null;
        $sessionId = (int) (session('member_sessionid') ?? 0) ?: null;
        $userId    = (int) (session('id') ?? 0) ?: null;

        $eventId   = (int) $this->request->getPost('event_id');
        if ($eventId <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid event.']);
        }

        // Determine event mode
       $event = $this->db->table('sports_events')->where('event_id', $eventId)->get()->getRowArray();
if (!$event) {
    return redirect()->to(base_url('admin/sports/events'))->with('error','Event not found');
}
// ensure compatibility with view's $isTeam check
$event['event_type'] = $event['mode'] ?? 'individual';

        $mode = $event['mode'] ?? 'individual';

        if ($mode === 'team') {
            // TEAM MODE: require team_id
            $teamId = $this->request->getPost('team_id');
            $teamId = ($teamId === '' || $teamId === null) ? null : (int) $teamId;
            if ($teamId === null) {
                return $this->response->setJSON(['ok' => false, 'msg' => 'Select a team.']);
            }

            // Validate team belongs to this event; derive house from team
            $team = $this->db->table('sports_teams')->where('team_id', $teamId)->get()->getRowArray();
            if (!$team || (int) $team['event_id'] !== $eventId) {
                return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid team for this event.']);
            }

            // Prevent duplicate team registration in same event (if you added uq on (event_id, team_id))
            $exists = $this->db->table('sports_event_entries')
                ->where(['event_id' => $eventId, 'team_id' => $teamId])
                ->countAllResults();
            if ($exists > 0) {
                return $this->response->setJSON(['ok' => false, 'msg' => 'This team is already added to the event.']);
            }

            $payload = [
                'campus_id'  => $campusId,
                'session_id' => $sessionId,
                'event_id'   => $eventId,
                'team_id'    => (int) $team['team_id'],
                'student_id' => null,
                'house_id'   => (int) $team['house_id'], // ensure consistency with team’s house
                'is_captain' => 0,
                'user_id'    => $userId,
            ];
        } else {
            // INDIVIDUAL MODE: require student + house
            $studentId = $this->request->getPost('student_id');
            $studentId = ($studentId === '' || $studentId === null) ? null : (int) $studentId;
            $houseId   = (int) $this->request->getPost('house_id');

            if ($studentId === null || $houseId <= 0) {
                return $this->response->setJSON(['ok' => false, 'msg' => 'Select student and house.']);
            }

            // Prevent duplicate student registration for the same event (many schemas already have a unique key)
            $exists = $this->db->table('sports_event_entries')
                ->where(['event_id' => $eventId, 'student_id' => $studentId])
                ->countAllResults();
            if ($exists > 0) {
                return $this->response->setJSON(['ok' => false, 'msg' => 'This student is already added to the event.']);
            }

            $payload = [
                'campus_id'  => $campusId,
                'session_id' => $sessionId,
                'event_id'   => $eventId,
                'student_id' => $studentId,
                'team_id'    => null,
                'house_id'   => $houseId,
                'is_captain' => 0,
                'user_id'    => $userId,
            ];
        }

        $ok = $this->db->table('sports_event_entries')->insert($payload);
        if (!$ok) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Insert failed.']);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Delete an entry (team or student row)
     */
    public function delete()
    {
        $entryId = (int) $this->request->getPost('entry_id');
        if ($entryId <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid entry id.']);
        }

        $this->db->table('sports_event_entries')->delete(['entry_id' => $entryId]);
        return $this->response->setJSON(['ok' => true]);
    }
}
