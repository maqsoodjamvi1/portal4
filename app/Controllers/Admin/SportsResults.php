<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsResults extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url','form','text']);
    }

    public function index()
    {
        // pre-fill event dropdown (same as your other pages)
        $campusId  = (int)(session('member_campusid')  ?? 0);
        $sessionId = (int)(session('member_sessionid') ?? 0);
        $cols = 'event_id, event_name, gender, event_type, event_date, per_house_count, team_size';

        $qb = $this->db->table('sports_events')->select($cols);
        if ($campusId > 0)  $qb->where('campus_id',  $campusId);
        if ($sessionId > 0) $qb->where('session_id', $sessionId);

        $events = $qb->orderBy('event_date','DESC')->orderBy('event_id','DESC')->get()->getResultArray();

        return view('admin/sports/results_index', ['events' => $events]);
    }

    // For dynamic filter refresh (optional; index already provides initial list)
    public function listEvents()
    {
        $campusId  = (int)(session('member_campusid')  ?? 0);
        $sessionId = (int)(session('member_sessionid') ?? 0);

        $cols = 'event_id, event_name, gender, event_type, event_date, per_house_count, team_size';
        $qb = $this->db->table('sports_events')->select($cols);
        if ($campusId > 0)  $qb->where('campus_id',  $campusId);
        if ($sessionId > 0) $qb->where('session_id', $sessionId);

        $rows = $qb->orderBy('event_date','DESC')->orderBy('event_id','DESC')->get()->getResultArray();
        return $this->response->setJSON(['ok'=>true, 'events'=>$rows]);
    }

    /**
     * Returns rows for results UI.
     * Input: event_id (optional; if 0/empty => all events)
     * Output: list of event blocks, each block => header + rows
     * For individual: one row per student
     * For team: one row per (event_id, house_id, team_id) with member list
     */
    public function rows()
    {
        $eventId = (int)$this->request->getPost('event_id');
        $campusId  = (int)(session('member_campusid')  ?? 0);
        $sessionId = (int)(session('member_sessionid') ?? 0);

        // Get target events
        $evb = $this->db->table('sports_events e')
            ->select('e.event_id, e.event_name, e.gender, e.event_type, e.event_date, e.per_house_count, e.team_size');
        if ($eventId > 0) $evb->where('e.event_id', $eventId);
        if ($campusId > 0)  $evb->where('e.campus_id',  $campusId);
        if ($sessionId > 0) $evb->where('e.session_id', $sessionId);
        $events = $evb->orderBy('e.event_date','DESC')->orderBy('e.event_id','DESC')->get()->getResultArray();

        $out = [];
        foreach ($events as $ev) {
            $isTeam = strtolower((string)$ev['event_type']) === 'team';

            if ($isTeam) {
                // One row per team; fetch teams by grouping entries
                $rows = $this->db->table('sports_event_entries se')
                    ->select('se.event_id, se.house_id, se.team_id')
                    ->select('h.house_name')
                    ->select('MIN(se.entry_id) AS any_entry_id', false)
                    ->join('sports_houses h', 'h.house_id = se.house_id', 'left')
                    ->where('se.event_id', $ev['event_id'])
                    ->where('se.team_id IS NOT NULL', null, false)
                    ->groupBy('se.event_id, se.house_id, se.team_id')
                    ->orderBy('h.house_name', 'ASC')
                    ->orderBy('se.team_id', 'ASC')
                    ->get()->getResultArray();

                // Attach members and existing result (position/points) if any (read once per team via any member)
                foreach ($rows as &$r) {
                    $members = $this->db->table('sports_event_entries se')
                        ->select('se.entry_id, se.student_id, s.first_name, s.last_name, s.profile_photo, s.date_of_birth')
                        ->select('COALESCE(c.class_short_name, c.class_name) AS class_short', false)
                        ->join('students s', 's.student_id=se.student_id', 'left')
                        ->join('student_class sc','sc.student_id = s.student_id' . ($sessionId ? ' AND sc.session_id = '.$this->db->escape($sessionId) : ''), 'left')
                        ->join('class_section cs','cs.cls_sec_id = sc.cls_sec_id', 'left')
                        ->join('classes c','c.class_id = cs.class_id', 'left')
                        ->where([
                            'se.event_id' => $ev['event_id'],
                            'se.house_id' => $r['house_id'],
                            'se.team_id'  => $r['team_id'],
                        ])->get()->getResultArray();

                    // participation count per student in this session
                    foreach ($members as &$m) {
                        $m['participation_count'] = (int) $this->db->table('sports_event_entries se2')
                            ->where('se2.student_id', $m['student_id'])
                            ->where($sessionId ? 'se2.session_id = '.$this->db->escape($sessionId) : '1=1', null, false)
                            ->countAllResults();
                    }

                    // existing result (if any) — take from results on this team (any member)
                    $res = $this->db->table('sports_event_results r')
                        ->select('r.position, r.points, r.rank_shared')
                        ->where([
                            'r.event_id' => $ev['event_id'],
                            'r.house_id' => $r['house_id'],
                            'r.team_id'  => $r['team_id'],
                        ])
                        ->orderBy('r.position','ASC')
                        ->get()->getRowArray();

                    $r['members'] = $members;
                    $r['result']  = $res ?: null; // ['position'=>1|2|3, 'points'=>.., 'rank_shared'=>0|1]
                }
            } else {
                // Individual: one row per participant (entry)
                $rows = $this->db->table('sports_event_entries se')
                    ->select('se.entry_id, se.event_id, se.house_id, se.team_id, se.student_id')
                    ->select('h.house_name')
                    ->select('s.first_name, s.last_name, s.profile_photo, s.date_of_birth')
                    ->select('COALESCE(c.class_short_name, c.class_name) AS class_short', false)
                    ->join('students s', 's.student_id=se.student_id', 'left')
                    ->join('sports_houses h', 'h.house_id = se.house_id', 'left')
                    ->join('student_class sc','sc.student_id = s.student_id' . ($sessionId ? ' AND sc.session_id = '.$this->db->escape($sessionId) : ''), 'left')
                    ->join('class_section cs','cs.cls_sec_id = sc.cls_sec_id', 'left')
                    ->join('classes c','c.class_id = cs.class_id', 'left')
                    ->where('se.event_id', $ev['event_id'])
                    ->orderBy('h.house_name','ASC')->orderBy('s.first_name','ASC')
                    ->get()->getResultArray();

                foreach ($rows as &$r) {
                    $r['participation_count'] = (int) $this->db->table('sports_event_entries se2')
                        ->where('se2.student_id', $r['student_id'])
                        ->where($sessionId ? 'se2.session_id = '.$this->db->escape($sessionId) : '1=1', null, false)
                        ->countAllResults();

                    $res = $this->db->table('sports_event_results r')
                        ->select('r.position, r.points, r.rank_shared')
                        ->where([
                            'r.event_id'   => $ev['event_id'],
                            'r.student_id' => $r['student_id'],
                        ])->get()->getRowArray();

                    $r['result'] = $res ?: null;
                }
            }

            $out[] = [
                'event' => $ev,
                'rows'  => $rows,
            ];
        }

        return $this->response->setJSON(['ok'=>true, 'blocks'=>$out]);
    }

    /**
     * Set/Change position (1,2,3) for a unit:
     * - Individual: unit = (event_id, student_id)
     * - Team: unit = (event_id, house_id, team_id)  // applied to ALL members
     * Also writes points from rules table.
     */
    public function setPosition()
    {
        $eventId   = (int)$this->request->getPost('event_id');
        $position  = (int)$this->request->getPost('position'); // 1/2/3
        $rankShared= (int)$this->request->getPost('rank_shared'); // 0/1 (optional)
        $unitType  = trim((string)$this->request->getPost('unit_type')); // 'individual' | 'team'
        $houseId   = (int)$this->request->getPost('house_id'); // for team
        $teamId    = (int)$this->request->getPost('team_id');  // for team
        $studentId = (int)$this->request->getPost('student_id'); // for individual

        if ($eventId<=0 || !in_array($position,[1,2,3],true)) {
            return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid input']);
        }

        $ev = $this->db->table('sports_events')->select('event_type')->where('event_id',$eventId)->get()->getRowArray();
        if (!$ev) return $this->response->setJSON(['ok'=>false,'msg'=>'Event not found']);
        $evType = strtolower((string)$ev['event_type'] ?? 'individual');

        $campusId  = (int)(session('member_campusid')  ?? 0) ?: null;
        $sessionId = (int)(session('member_sessionid') ?? 0) ?: null;

        $points = $this->resolvePoints($campusId, $sessionId, $evType, $position);

        $this->db->transStart();

        // Ensure uniqueness of position per event (clear previous holder of this position)
        if ($evType === 'team') {
            $this->db->table('sports_event_results')
                ->where(['event_id'=>$eventId, 'position'=>$position])
                ->delete();

            // Insert for all members of that team
            $members = $this->db->table('sports_event_entries')
                ->select('student_id, house_id, team_id')
                ->where(['event_id'=>$eventId, 'house_id'=>$houseId, 'team_id'=>$teamId])
                ->get()->getResultArray();

            // safety: if no members, rollback
            if (!$members) {
                $this->db->transRollback();
                return $this->response->setJSON(['ok'=>false,'msg'=>'Team has no members']);
            }

            // clear previous results for this team (any position)
            $this->db->table('sports_event_results')
                ->where(['event_id'=>$eventId, 'house_id'=>$houseId, 'team_id'=>$teamId])
                ->delete();

            foreach ($members as $m) {
                $this->db->table('sports_event_results')->insert([
                    'campus_id'  => $campusId,
                    'session_id' => $sessionId,
                    'event_id'   => $eventId,
                    'team_id'    => (int)$m['team_id'],
                    'student_id' => (int)$m['student_id'],
                    'team_name'  => null,
                    'team_name_legacy' => null,
                    'house_id'   => (int)$m['house_id'],
                    'position'   => $position,
                    'points'     => $points,
                    'rank_shared'=> $rankShared ? 1 : 0,
                ]);
            }
        } else {
            // Individual unit
            if ($studentId <= 0) {
                $this->db->transRollback();
                return $this->response->setJSON(['ok'=>false,'msg'=>'Missing student']);
            }

            // clear previous owner of that position in this event
            $this->db->table('sports_event_results')->where(['event_id'=>$eventId, 'position'=>$position])->delete();
            // clear any previous result for this student in this event
            $this->db->table('sports_event_results')->where(['event_id'=>$eventId, 'student_id'=>$studentId])->delete();

            // need house_id for nice summaries
            $entry = $this->db->table('sports_event_entries')->select('house_id')
                ->where(['event_id'=>$eventId, 'student_id'=>$studentId])->get()->getRowArray();

            $this->db->table('sports_event_results')->insert([
                'campus_id'  => $campusId,
                'session_id' => $sessionId,
                'event_id'   => $eventId,
                'team_id'    => null,
                'student_id' => $studentId,
                'team_name'  => null,
                'team_name_legacy' => null,
                'house_id'   => (int)($entry['house_id'] ?? 0) ?: null,
                'position'   => $position,
                'points'     => $points,
                'rank_shared'=> $rankShared ? 1 : 0,
            ]);
        }

        $this->db->transComplete();
        if (!$this->db->transStatus()) {
            return $this->response->setJSON(['ok'=>false,'msg'=>'DB transaction failed']);
        }

        return $this->response->setJSON(['ok'=>true]);
    }

    public function clearPosition()
    {
        $eventId  = (int)$this->request->getPost('event_id');
        $unitType = trim((string)$this->request->getPost('unit_type')); // team|individual
        $houseId  = (int)$this->request->getPost('house_id');
        $teamId   = (int)$this->request->getPost('team_id');
        $studentId= (int)$this->request->getPost('student_id');

        if ($eventId<=0) return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid input']);

        if ($unitType === 'team') {
            $this->db->table('sports_event_results')->where([
                'event_id'=>$eventId, 'house_id'=>$houseId, 'team_id'=>$teamId
            ])->delete();
        } else {
            if ($studentId<=0) return $this->response->setJSON(['ok'=>false,'msg'=>'Missing student']);
            $this->db->table('sports_event_results')->where([
                'event_id'=>$eventId, 'student_id'=>$studentId
            ])->delete();
        }

        return $this->response->setJSON(['ok'=>true]);
    }

    /**
     * Resolve points from sports_scoring_rules with fallback:
     * exact campus+session -> campus only -> session only -> global (NULL,NULL) -> default 0
     */
    protected function resolvePoints(?int $campusId, ?int $sessionId, string $eventType, int $positionNo): int
    {
        $eventType = strtolower($eventType)==='team' ? 'team' : 'individual';
        $c = $campusId ?: null;
        $s = $sessionId ?: null;

        // Try combos in order
        $tries = [
            ['campus_id'=>$c, 'session_id'=>$s],
            ['campus_id'=>$c, 'session_id'=>null],
            ['campus_id'=>null, 'session_id'=>$s],
            ['campus_id'=>null, 'session_id'=>null],
        ];

        foreach ($tries as $t) {
            $qb = $this->db->table('sports_scoring_rules')
                ->select('points')
                ->where('position_no', $positionNo)
                ->where('event_type', $eventType);

            $t['campus_id'] === null ? $qb->where('campus_id IS NULL', null, false) : $qb->where('campus_id', $t['campus_id']);
            $t['session_id'] === null ? $qb->where('session_id IS NULL', null, false) : $qb->where('session_id', $t['session_id']);

            $row = $qb->get()->getRowArray();
            if ($row) return (int)$row['points'];
        }
        return 0;
    }
}
