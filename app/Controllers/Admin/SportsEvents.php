<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsEvents extends BaseController
{
    protected $db;
    protected string $tbl = 'sports_events';

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url','form']);
    }

    public function index()
    {
        // Use the new card view
        return view('admin/sports/events_list_cards');
    }


public function bulk()
{
    // Basic values to seed the selectors (today + defaults)
    return view('admin/sports/events_bulk', [
        // pass anything you need
    ]);
}

public function bulkFetch()
{
    $type  = strtolower(trim((string)$this->request->getPost('event_type')));
    $gender= strtolower(trim((string)$this->request->getPost('gender')));
    $date  = trim((string)$this->request->getPost('event_date'));

    $campusId  = (int) (session('member_campusid')  ?? 0);
    $sessionId = (int) (session('member_sessionid') ?? 0);

    if (!in_array($type, ['individual','team'], true)) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid event_type']);
    }
    if (!in_array($gender, ['male','female','mixed'], true)) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid gender']);
    }
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid event_date']);
    }

    $qb = $this->db->table($this->tbl)
        ->select('event_id,event_name, per_house_count, min_age, max_age')
        ->where('event_type', $type)
        ->where('gender',     $gender)
        ->where('event_date', $date);

    if ($campusId > 0)  $qb->where('campus_id',  $campusId);
    if ($sessionId > 0) $qb->where('session_id', $sessionId);

    $rows = $qb->orderBy('event_id','ASC')->get()->getResultArray();

    return $this->response->setJSON(['ok'=>true, 'rows'=>$rows]);
}


public function bulkLoad()
{
    $type  = strtolower((string)$this->request->getPost('event_type'));
    $gen   = strtolower((string)$this->request->getPost('gender'));
    $date  = (string)$this->request->getPost('event_date');

    if (!in_array($type, ['individual','team'], true) ||
        !in_array($gen, ['male','female','mixed'], true) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid filters']);
    }

    $campusId  = (int) (session('member_campusid')  ?? 0);
    $sessionId = (int) (session('member_sessionid') ?? 0);

    $qb = $this->db->table($this->tbl)
        ->select('event_id, event_name, per_house_count, min_age, max_age')
        ->where('event_type', $type)
        ->where('gender', $gen)
        ->where('event_date', $date);

    if ($campusId > 0)  $qb->where('campus_id',  $campusId);
    if ($sessionId > 0) $qb->where('session_id', $sessionId);

    $rows = $qb->orderBy('event_id','ASC')->get()->getResultArray();

    return $this->response->setJSON(['ok'=>true,'rows'=>$rows]);
}

/**
 * AJAX: save many rows at once
 * POST:
 *   event_type, gender, event_date
 *   rows[][event_name], rows[][per_house_count], rows[][min_age], rows[][max_age]
 *
 * Upsert rule (within current campus/session):
 * unique key is (event_type, gender, event_date, event_name)
 */

public function bulkSave()
{
    $typeRaw = strtolower(trim((string)$this->request->getPost('event_type')));
    $gender  = strtolower(trim((string)$this->request->getPost('gender')));
    $date    = trim((string)$this->request->getPost('event_date'));

    // arrays
    $idsArr  = $this->request->getPost('event_id') ?? [];            // <-- hidden inputs
    $names   = $this->request->getPost('event_name') ?? [];
    $phcArr  = $this->request->getPost('per_house_count') ?? [];
    $minArr  = $this->request->getPost('min_age') ?? [];
    $maxArr  = $this->request->getPost('max_age') ?? [];

    $campusId  = (int) (session('member_campusid')  ?? 0);
    $sessionId = (int) (session('member_sessionid') ?? 0);

    // Map to ENUM('Individual','Team')
    $type = $typeRaw === 'team' ? 'team' : 'individual'; // exact case per schema  (ENUM)
    if (!in_array($gender, ['male','female','mixed'], true)) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid gender']);
    }
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid event_date (Y-m-d)']);
    }

    // sanity lengths
    if (!is_array($names) || !is_array($phcArr) || !is_array($minArr) || !is_array($maxArr)) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid payload: arrays missing']);
    }
    $n = count($names);
    if (!($n === count($phcArr) && $n === count($minArr) && $n === count($maxArr))) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid payload: row arrays are not same length']);
    }
    // idsArr is optional (existing rows), but if present should match length
    if (is_array($idsArr) && count($idsArr) && count($idsArr) !== $n) {
        return $this->response->setJSON(['ok'=>false,'msg'=>'Invalid payload: event_id length mismatch']);
    }

    // Row-level validation and normalization
    $rows = [];
    $rowErrors = [];
    for ($i=0; $i<$n; $i++) {
        $ename = trim((string)$names[$i]);
        $phc   = trim((string)$phcArr[$i]);
        $min   = trim((string)$minArr[$i]);
        $max   = trim((string)$maxArr[$i]);
        $eid   = isset($idsArr[$i]) && $idsArr[$i] !== '' ? (int)$idsArr[$i] : null;

        if ($ename === '') { $rowErrors[$i] = 'Event name is required'; continue; }

        $perHouse = ($phc === '' ? null : max(0, (int)$phc));
        $minAge   = ($min === '' ? null : max(0, (int)$min));
        $maxAge   = ($max === '' ? null : max(0, (int)$max));
        if ($minAge !== null && $maxAge !== null && $minAge > $maxAge) {
            $rowErrors[$i] = 'Min age cannot be greater than Max age'; continue;
        }

        $rows[] = [
            'i'              => $i,
            'event_id'       => $eid,            // may be null for new
            'campus_id'      => $campusId ?: null,
            'session_id'     => $sessionId ?: null,
            'event_name'     => $ename,
            'event_type'     => $type,          // EXACT case per ENUM
            'gender'         => $gender,
            'event_date'     => $date,
            // Optional columns in your custom schema (safe to keep; ignored if not present)
            'per_house_count'=> $perHouse,
            'min_age'        => $minAge,
            'max_age'        => $maxAge,
        ];
    }
    if ($rowErrors) {
        return $this->response->setJSON(['ok'=>false, 'msg'=>'Row validation failed', 'rowErrors'=>$rowErrors]);
    }

    $tbl = $this->tbl; // 'sports_events'
    $ev  = $this->db->table($tbl);

    $saved   = 0;
    $updated = 0;
    $errors  = [];

    $this->db->transStart();
    foreach ($rows as $r) {
        $i = $r['i'];
        $eid = $r['event_id'];
        $payload = $r; unset($payload['i'], $payload['event_id']);

        if ($eid) {
            // UPDATE by primary key
            $ok = $ev->where('event_id', $eid)->update($payload);
            if (!$ok) {
                $errors[$i] = $this->db->error();
            } else {
                $updated += $this->db->affectedRows() >= 0 ? 1 : 0;
            }
        } else {
            // Try INSERT
            $ok = $ev->insert($payload);
            if (!$ok) {
                $err = $this->db->error();
                // Duplicate? Upsert by unique key (campus_id, session_id, event_name)
                if (!empty($err['code']) && (int)$err['code'] === 1062) {
                    $ok2 = $ev
                        ->where('campus_id', $payload['campus_id'])
                        ->where('session_id', $payload['session_id'])
                        ->where('event_name', $payload['event_name'])
                        ->update($payload);
                    if ($ok2) {
                        $updated++;
                        continue;
                    }
                }
                $errors[$i] = $err;
            } else {
                $saved += $this->db->affectedRows() > 0 ? 1 : 0;
            }
        }
    }
    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        // expose first real DB error if any
        $dberr = $this->db->error();
        return $this->response->setJSON(['ok'=>false,'msg'=>'DB transaction failed','dberr'=>$dberr ?: $errors]);
    }

    return $this->response->setJSON([
        'ok'      => empty($errors),
        'saved'   => $saved,
        'updated' => $updated,
        'errors'  => $errors
    ]);
}

    public function add()
    {
        return view('admin/sports/events_bulk');
    }

    public function edit($id)
    {
        $row = $this->db->table($this->tbl)
            ->select('event_id, campus_id, event_name, event_type, gender, event_date, per_house_count')
            ->where('event_id', (int)$id)
            ->get()->getRowArray();

        return view('admin/sports/events_form', compact('row'));
    }


public function data()
{
    $campusId  = (int) (session('member_campusid')  ?? 0);
    $sessionId = (int) (session('member_sessionid') ?? 0);

    // ===== 1) PRIMARY QUERY (with filters if present)
    $qb = $this->db->table($this->tbl)
        ->select('event_id, campus_id, session_id, event_name, event_type, gender, event_date, per_house_count');

    if ($campusId > 0)  { $qb->where('campus_id',  $campusId); }
    if ($sessionId > 0) { $qb->where('session_id', $sessionId); }

    $sqlPrimary = $qb->getCompiledSelect(); // capture for debugging
    $events     = $qb->orderBy('event_date', 'ASC')->get()->getResultArray();

    // ===== 2) FALLBACK (campus only) if primary returned nothing
    $sqlFallback = null;
    if (!$events && $sessionId > 0) {
        $qb2 = $this->db->table($this->tbl)
            ->select('event_id, campus_id, session_id, event_name, event_type, gender, event_date, per_house_count');

        if ($campusId > 0) { $qb2->where('campus_id', $campusId); }

        $sqlFallback = $qb2->getCompiledSelect();
        $events      = $qb2->orderBy('event_date', 'ASC')->get()->getResultArray();
    }

    // If still nothing, return with debug now
    if (!$events) {
        return $this->response->setJSON([
            'data'  => [],
            'debug' => [
                'reason'      => 'No rows after primary (and fallback if applied).',
                'campusId'    => $campusId,
                'sessionId'   => $sessionId,
                'sql_primary' => $sqlPrimary,
                'sql_fallback'=> $sqlFallback,
                'events_count'=> 0,
            ]
        ]);
    }

    // ===== 3) AGGREGATES
    $eventIds = array_map(static fn($e) => (int)$e['event_id'], $events);
    $inIds    = implode(',', array_map('intval', $eventIds));

    $byHouse            = [];
    $teamsCount         = [];
    $teamMembersCount   = [];
    $sqlByHouse         = null;
    $sqlTeams           = null;
    $sqlTeamMembers     = null;

    if ($inIds !== '') {
        // 3a) Individual events: participants per house (color_code)
        $sqlByHouse = "
            SELECT se.event_id,
                   se.house_id,
                   h.house_name,
                   h.color_code,
                   COUNT(*) AS total
            FROM sports_event_entries se
            LEFT JOIN sports_houses h ON h.house_id = se.house_id
            WHERE se.student_id IS NOT NULL
              AND se.event_id IN ($inIds)
            GROUP BY se.event_id, se.house_id
        ";
        foreach ($this->db->query($sqlByHouse)->getResultArray() as $r) {
            $eid = (int)$r['event_id'];
            $byHouse[$eid][] = [
                'house_id'   => (int)$r['house_id'],
                'house_name' => (string)$r['house_name'],
                'color_code' => (string)$r['color_code'],
                'total'      => (int)$r['total'],
            ];
        }

        // 3b) Team events: teams count per event
        $sqlTeams = "
            SELECT event_id, COUNT(*) AS teams_count
            FROM sports_teams
            WHERE event_id IN ($inIds)
            GROUP BY event_id
        ";
        foreach ($this->db->query($sqlTeams)->getResultArray() as $r) {
            $teamsCount[(int)$r['event_id']] = (int)$r['teams_count'];
        }

        // 3c) Team events: total team members per event
        $sqlTeamMembers = "
            SELECT t.event_id, COUNT(*) AS members_count
            FROM sports_team_members stm
            JOIN sports_teams t ON t.team_id = stm.team_id
            WHERE t.event_id IN ($inIds)
            GROUP BY t.event_id
        ";
        foreach ($this->db->query($sqlTeamMembers)->getResultArray() as $r) {
            $teamMembersCount[(int)$r['event_id']] = (int)$r['members_count'];
        }
    }

    // ===== 4) SHAPE RESPONSE (for the card view)
    $out = [];
    foreach ($events as $e) {
        $eid  = (int)$e['event_id'];
        $type = strtolower((string)$e['event_type']);

        $row = [
            'event_id'         => $eid,
            'event_name'       => $e['event_name'],
            'event_type'       => $type,                                // 'individual' | 'team'
            'gender'           => strtolower((string)($e['gender'] ?? '')), // 'male'|'female'|'mixed'
            'event_date'       => $e['event_date'],
            'per_house_count' => $e['per_house_count'],
        ];

        if ($type === 'team') {
            $row['teams_count']        = $teamsCount[$eid]       ?? 0;
            $row['team_members_count'] = $teamMembersCount[$eid] ?? 0;
        } else {
            $row['counts_by_house']    = $byHouse[$eid]          ?? [];
        }

        $out[] = $row;
    }

    // ===== 5) RETURN with DEBUG
    return $this->response->setJSON([
        'data'  => $out,
        'debug' => [
            'campusId'          => $campusId,
            'sessionId'         => $sessionId,
            'sql_primary'       => $sqlPrimary,
            'sql_fallback'      => $sqlFallback,
            'sql_by_house'      => $sqlByHouse,
            'sql_teams'         => $sqlTeams,
            'sql_team_members'  => $sqlTeamMembers,
            'events_count'      => count($events),
            'out_events_count'  => count($out),
        ]
    ]);
}


public function save()
{


    $v = service('validation');
    $v->setRules([
        'event_id'        => 'permit_empty|integer',
        'event_name'      => 'required',
        'event_type'      => 'required|in_list[individual,team]',
        'gender'          => 'required|in_list[male,female,mixed]',
        'event_date'      => 'required|valid_date[Y-m-d]',
        // Note: use the actual DB column names:
        'per_house_count' => 'required',
        'min_age'         => 'required',
        'max_age'         => 'required',
    ]);

    if (!$v->withRequest($this->request)->run()) {
        return $this->response->setJSON(['ok' => false, 'errors' => $v->getErrors()]);
    }

    $campusId  = (int) (session('member_campusid')  ?? 1);
    $sessionId = (int) (session('member_sessionid') ?? 1);
    $id        = (int) ($this->request->getPost('event_id') ?? 0);

    // Build payload with correct keys
    $payload = [
        'campus_id'       => $campusId,
        'session_id'      => $sessionId,
        'event_name'      => $this->request->getPost('event_name'),
        'event_type'      => $this->request->getPost('event_type'),
        'gender'          => $this->request->getPost('gender'),
        'event_date'      => $this->request->getPost('event_date'),
        'per_house_count' => $this->request->getPost('per_house_count') !== '' ?  $this->request->getPost('per_house_count') : null,
        'min_age'         =>  $this->request->getPost('min_age')         !== '' ?  $this->request->getPost('min_age')         : null,
        'max_age'         =>  $this->request->getPost('max_age')         !== '' ?  $this->request->getPost('max_age')         : null,
    ];

    // Safety: if an old form still posts max_participants, map it
    if ($this->request->getPost('per_house_count') !== null && $payload['per_house_count'] === null) {
        $payload['per_house_count'] = (int) $this->request->getPost('per_house_count');
    }

    $qb = $this->db->table($this->tbl); // ensure $this->tbl === 'sports_events'

    // You don't need a transaction for a single row upsert; it also hides simple errors.
    if ($id > 0) {
        $qb->where('event_id', $id)->update($payload);
    } else {
        $qb->insert($payload);
        $id = (int) $this->db->insertID();
    }

    $dbErr    = $this->db->error();        // catch any 1054 etc.
    $affected = $this->db->affectedRows(); // may be 0 if no field actually changed

    if (!empty($dbErr['code'])) {
        // surface MySQL error cleanly
        return $this->response->setJSON(['ok' => false, 'msg' => 'DB error', 'dberr' => $dbErr]);
    }

    // Record exists?
    $row = $this->db->table($this->tbl)->select('event_id')->where('event_id', $id)->get()->getRowArray();
    if (!$row) {
        return $this->response->setJSON(['ok' => false, 'msg' => 'Record not found after save', 'affected' => $affected]);
    }

    return $this->response->setJSON([
        'ok'       => true,
        'id'       => $id,
        'affected' => $affected,   // could be 0 when no change; treat as success
    ]);
}

}
