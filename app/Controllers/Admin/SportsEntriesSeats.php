<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsEntriesSeats extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url','form','text']);
    }

    /**
     * Single-page UI: event selector + seats on the same view
     * GET admin/sports/entries/seats
     */
  public function index()
{
    $campusId  = (int)(session('member_campusid')  ?? 0);
    $sessionId = (int)(session('member_sessionid') ?? 0);

    $cols = 'event_id, event_name, gender, event_type, event_date, per_house_count, min_age, max_age, team_size';

    $qb = $this->db->table('sports_events')->select($cols);
    if ($campusId > 0)  { $qb->where('campus_id',  $campusId); }
    if ($sessionId > 0) { $qb->where('session_id', $sessionId); }

    $events = $qb->orderBy('event_date','DESC')
                 ->orderBy('event_id','DESC')
                 ->get()->getResultArray();

    // Fallback: if filters returned nothing, show all events so the select isn't empty
    if (empty($events) && ($campusId > 0 || $sessionId > 0)) {
        $events = $this->db->table('sports_events')
            ->select($cols)
            ->orderBy('event_date','DESC')
            ->orderBy('event_id','DESC')
            ->get()->getResultArray();
    }

    return view('admin/sports/entries_seats_onepage', ['events' => $events]);
}
    /**
     * AJAX: load event header + houses + current cart in one call
     * POST: event_id
     * Returns: { ok, event, houses, cart }
     */
    public function meta()
    {
        $eventId = (int)$this->request->getPost('event_id');
        if ($eventId <= 0) return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid event']);

        $event = $this->db->table('sports_events')->where('event_id', $eventId)->get()->getRowArray();
        if (!$event) return $this->response->setJSON(['ok'=>false,'msg'=>'Event not found']);

        $event['per_house_count'] = (int)($event['per_house_count'] ?? 0);
        $event['gender']          = strtolower((string)($event['gender'] ?? ''));

        $houses = $this->db->table('sports_houses')
            ->select('house_id, house_name')
            ->orderBy('house_name','ASC')
            ->limit(4)
            ->get()->getResultArray();

        $cart = $this->getCartByHouse($eventId);

        return $this->response->setJSON([
            'ok'     => true,
            'event'  => $event,
            'houses' => $houses,
            'cart'   => $cart
        ]);
    }

    /**
     * AJAX: members list with gender + age-range + not already in event
     * Also returns participation_count per student for current session.
     */
public function members()
{
    $eventId = (int)$this->request->getPost('event_id');
    $houseId = (int)$this->request->getPost('house_id');
    $q       = trim((string)$this->request->getPost('q'));

    if ($eventId <= 0 || $houseId <= 0) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid request']);
    }

    $event = $this->db->table('sports_events')
        ->where('event_id', $eventId)
        ->get()
        ->getRowArray();

    if (!$event) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Event not found']);
    }

    $gender    = strtolower((string)($event['gender'] ?? ''));
    $minAge    = (int)($event['min_age'] ?? 0);
    $maxAge    = (int)($event['max_age'] ?? 0);
    $sessionId = (int)(session('member_sessionid') ?? 0);

    /**
     * Age rounding in SQL:
     * months = TIMESTAMPDIFF(MONTH, dob, CURDATE())
     * years  = FLOOR(months/12)
     * if (months % 12 > 6) years++
     */
    $ageExpr = "
        FLOOR(TIMESTAMPDIFF(MONTH, s.date_of_birth, CURDATE()) / 12)
        + CASE 
            WHEN MOD(TIMESTAMPDIFF(MONTH, s.date_of_birth, CURDATE()), 12) > 6 
              THEN 1 
              ELSE 0 
          END
    ";

    $b = $this->db->table('students s')
        ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, s.date_of_birth, s.gender, s.status')
        ->select('COALESCE(c.class_short_name, c.class_name) AS class_short', false)
        // rounded age in years (same logic as JS)
        ->select("$ageExpr AS age_years_rounded", false)
        // participation_count in current session
        ->select(
            "(SELECT COUNT(*) FROM sports_event_entries se2
              WHERE se2.student_id = s.student_id" .
              ($sessionId ? " AND se2.session_id = " . $this->db->escape($sessionId) : "") .
            ") AS participation_count",
            false
        )
        ->join(
            'student_class sc',
            'sc.student_id = s.student_id' . ($sessionId ? ' AND sc.session_id = ' . $this->db->escape($sessionId) : ''),
            'left'
        )
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c',       'c.class_id = cs.class_id',      'left')
        ->where('s.status', 1)
        ->where('s.house_id', $houseId);

    // Gender filter: must match event gender (if male/female)
    if ($gender === 'male' || $gender === 'female') {
        $b->where('LOWER(s.gender)', $gender);
    }

    // Name search
    if ($q !== '') {
        $b->groupStart()
            ->like('s.first_name', $q)
            ->orLike('s.last_name', $q)
          ->groupEnd();
    }

    // Age range based on rounded age (using HAVING on alias)
    if ($minAge > 0) {
        $b->having('age_years_rounded >=', $minAge);
    }
    if ($maxAge > 0) {
        $b->having('age_years_rounded <=', $maxAge);
    }

    // Exclude already in this event
    $b->where("
        NOT EXISTS (
            SELECT 1 
            FROM sports_event_entries e
            WHERE e.event_id = " . $this->db->escape($eventId) . "
              AND e.student_id = s.student_id
        )
    ", null, false);

    $rows = $b->orderBy('c.class_id', 'ASC')
              ->orderBy('s.first_name', 'ASC')
              ->limit(500)
              ->get()
              ->getResultArray();

    return $this->response->setJSON(['ok' => true, 'data' => $rows]);
}


public function add()
{
    $eventId   = (int)$this->request->getPost('event_id');
    $houseId   = (int)$this->request->getPost('house_id');
    $studentId = (int)$this->request->getPost('student_id');

    if ($eventId<=0 || $houseId<=0 || $studentId<=0) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid request']);
    }

    $event = $this->db->table('sports_events')
        ->select('event_id, gender, event_type, per_house_count, team_size')
        ->where('event_id',$eventId)->get()->getRowArray();
    if (!$event) return $this->response->setJSON(['ok'=>false,'msg'=>'Event not found']);

    $gender   = strtolower((string)($event['gender'] ?? ''));
    $evtType  = strtolower((string)($event['event_type'] ?? 'individual'));
    $capTeams = (int)($event['per_house_count'] ?? 0);
    $teamSize = (int)($event['team_size'] ?? 0);

    $stu = $this->db->table('students')
        ->select('gender, status, house_id')
        ->where('student_id',$studentId)->get()->getRowArray();
    if (!$stu) return $this->response->setJSON(['ok'=>false,'msg'=>'Student not found']);
    if ((int)$stu['house_id'] !== $houseId) return $this->response->setJSON(['ok'=>false,'msg'=>'Student not in this house']);
    if ((int)$stu['status'] !== 1) return $this->response->setJSON(['ok'=>false,'msg'=>'Inactive student']);
    if ($gender === 'male' || $gender === 'female') {
        if (strtolower((string)$stu['gender']) !== $gender) {
            return $this->response->setJSON(['ok'=>false,'msg'=>'Gender mismatch for this event']);
        }
    }

    // Already in this event?
    $exists = $this->db->table('sports_event_entries')
        ->where(['event_id'=>$eventId,'student_id'=>$studentId])
        ->countAllResults();
    if ($exists > 0) return $this->response->setJSON(['ok'=>false,'msg'=>'Already added to this event']);

    // Build base payload
    $payload = [
        'campus_id'  => (int)(session('member_campusid') ?? 0) ?: null,
        'session_id' => (int)(session('member_sessionid') ?? 0) ?: null,
        'event_id'   => $eventId,
        'student_id' => $studentId,
        'house_id'   => $houseId,
        'is_captain' => 0,
        'user_id'    => (int)(session('id') ?? 0) ?: null,
    ];

    if ($evtType === 'team') {
        if ($capTeams <= 0 || $teamSize <= 0) {
            return $this->response->setJSON(['ok'=>false,'msg'=>'Team configuration missing (teams or team size)']);
        }

        // Total seats used in this house for this event
        $used = (int)$this->db->table('sports_event_entries')
            ->where(['event_id'=>$eventId,'house_id'=>$houseId])
            ->countAllResults();
        $maxSeats = $capTeams * $teamSize;
        if ($used >= $maxSeats) {
            return $this->response->setJSON(['ok'=>false,'msg'=>'All teams are full for this house']);
        }

        // Count by team_id (1..capTeams); ignore NULL team_id
        $rows = $this->db->table('sports_event_entries')
            ->select('team_id, COUNT(*) AS cnt')
            ->where(['event_id'=>$eventId,'house_id'=>$houseId])
            ->where('team_id IS NOT NULL', null, false)
            ->groupBy('team_id')
            ->get()->getResultArray();

        $byTeam = [];
        foreach ($rows as $r) $byTeam[(int)$r['team_id']] = (int)$r['cnt'];

        // Find the first team with free seat
        $chosenTeam = null;
        for ($t=1; $t <= $capTeams; $t++) {
            if (($byTeam[$t] ?? 0) < $teamSize) { $chosenTeam = $t; break; }
        }
        if ($chosenTeam === null) {
            // All numbered teams full but 'used < maxSeats' means there must be NULL rows
            // fall back to team 1 unless you want to rebalance differently
            $chosenTeam = 1;
        }

        $payload['team_id'] = $chosenTeam;
    } else {
        // Individual: per_house_count = seats per house
        $capSeats = (int)($event['per_house_count'] ?? 0);
        $houseCount = $this->db->table('sports_event_entries')
            ->where(['event_id'=>$eventId,'house_id'=>$houseId])
            ->countAllResults();
        if ($capSeats > 0 && $houseCount >= $capSeats) {
            return $this->response->setJSON(['ok'=>false,'msg'=>'House cart is full']);
        }
        $payload['team_id'] = null; // not used
    }

   $ok = $this->db->table('sports_event_entries')->insert($payload);

if (!$ok) {
    // capture MySQL error + last query
    $err = $this->db->error(); // ['code' => int, 'message' => string]
    $lastSql = method_exists($this->db, 'getLastQuery') && $this->db->getLastQuery()
        ? $this->db->getLastQuery()->getQuery()
        : null;

    return $this->response->setJSON([
        'ok'    => false,
        'msg'   => 'Insert failed',
        'dberr' => $err,
        'sql'   => $lastSql,
        'payload' => $payload, // optional, helps verify values
    ]);
}

return $this->response->setJSON([
    'ok'   => true,
    'cart' => $this->getCartByHouse($eventId),
]);
}



    public function remove()
    {
        $entryId = (int)$this->request->getPost('entry_id');
        if ($entryId <= 0) return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid id']);

        $this->db->table('sports_event_entries')->delete(['entry_id'=>$entryId]);
        return $this->response->setJSON(['ok'=>true]);
    }

    public function cart()
    {
        $eventId = (int)$this->request->getPost('event_id'); // (fixed a stray bracket you had before)
        if ($eventId<=0) return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid event']);

        return $this->response->setJSON(['ok'=>true, 'cart'=>$this->getCartByHouse($eventId)]);
    }

   protected function getCartByHouse(int $eventId): array
{
    $sessionId = (int)(session('member_sessionid') ?? 0);

    $rows = $this->db->table('sports_event_entries e')
        ->select('e.entry_id, e.event_id, e.student_id, e.house_id, e.team_id') // include team_id
        ->select('s.first_name, s.last_name, s.profile_photo, s.date_of_birth')
        ->select('COALESCE(c.class_short_name, c.class_name) AS class_short', false)
        ->join('students s', 's.student_id=e.student_id', 'left')
        ->join('student_class sc','sc.student_id = s.student_id' . ($sessionId ? ' AND sc.session_id = '.$this->db->escape($sessionId) : ''), 'left')
        ->join('class_section cs','cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c','c.class_id = cs.class_id', 'left')
        ->where('e.event_id', $eventId)
        ->orderBy('e.house_id','ASC')
        ->orderBy('e.team_id','ASC')
        ->orderBy('c.class_id','ASC')
        ->orderBy('s.first_name','ASC')
        ->get()->getResultArray();

    $out = [];
    foreach ($rows as $r) {
        $hid = (int)$r['house_id'];
        $out[$hid][] = $r;
    }
    return $out;
}
}
