<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsReports extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url']);
    }

    /**
     * Report page (renders view)
     */


    public function deleteStudent()
{
    $studentId = (int) $this->request->getPost('student_id');
    
    $sessionId = (int) (session('member_sessionid') ?? 0);

    if (! $studentId || ! $sessionId) {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Invalid student or session.'
        ]);
    }

    $db = \Config\Database::connect();
    $builder = $db->table('sports_event_entries');

    $builder->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->delete();

    return $this->response->setJSON([
        'status'  => 'ok',
        'deleted' => $db->affectedRows(),
    ]);
}
    public function events()
{
    $campusId  = (int) (session('member_campusid')  ?? 0);
    $sessionId = (int) (session('member_sessionid') ?? 0);

    $qb = $this->db->table('sports_houses')
        ->select('house_id, house_name, color_code');

    if ($campusId > 0) {
        $qb->where('campus_id', $campusId);
    }
    

    $houses = $qb
        ->orderBy('house_name', 'ASC')
        ->get()
        ->getResultArray();

    return view('admin/sports/reports_events', [
        'houses' => $houses,
    ]);
}

    public function houseMembers()
    {
        $sessionId = (int)(session('member_sessionid') ?? 0);

        // List houses
        $houses = $this->db->table('sports_houses')
            ->select('house_id, house_name, color_code')
            ->orderBy('house_name', 'ASC')
            ->get()->getResultArray();

        return view('admin/sports/house_members', [
            'houses'    => $houses,
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * AJAX: returns members of a house (male & female separately), ordered by age (oldest → youngest)
     * POST: house_id
     * Returns: { ok:true, summary:{total,male,female}, male:[...], female:[...] }
     */
    public function houseMembersData()
    {
        $houseId   = (int)$this->request->getPost('house_id');
        $sessionId = (int)(session('member_sessionid') ?? 0);

        if ($houseId <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid house']);
        }

        // NOTE:
        // We fetch DOB columns + db_status and calculate age in PHP using calculateAgeYears()
        $baseSql = "
            SELECT
                s.student_id,
                s.first_name,
                s.last_name,
                s.profile_photo,
                s.date_of_birth,
                s.date_of_birth_age,
                s.db_status,
                COALESCE(c.class_short_name, c.class_name) AS class_short,
                (
                    SELECT COUNT(*)
                    FROM sports_event_entries e
                    WHERE e.student_id = s.student_id
                      AND (:sessionId: = 0 OR e.session_id = :sessionId:)
                ) AS participation_count
            FROM students s
            LEFT JOIN student_class sc
              ON sc.student_id = s.student_id
             AND (:sessionId: = 0 OR sc.session_id = :sessionId:)
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c       ON c.class_id    = cs.class_id
            WHERE s.status   = 1
              AND s.house_id = :houseId:
              AND LOWER(s.gender) = :gender:
        ";

        $male   = $this->db->query($baseSql, [
            'houseId'   => $houseId,
            'sessionId' => $sessionId,
            'gender'    => 'male',
        ])->getResultArray();

        $female = $this->db->query($baseSql, [
            'houseId'   => $houseId,
            'sessionId' => $sessionId,
            'gender'    => 'female',
        ])->getResultArray();

        // Attach age_years using your calculateAgeYears() rule (db_status / date_of_birth / date_of_birth_age)
        $male   = $this->attachAgeAndSort($male);
        $female = $this->attachAgeAndSort($female);

        $summary = [
            'male'   => count($male),
            'female' => count($female),
            'total'  => count($male) + count($female),
        ];

        return $this->response->setJSON([
            'ok'      => true,
            'summary' => $summary,
            'male'    => $male,
            'female'  => $female,
        ]);
    }

    /**
     * Helper: attach age_years (using db_status + DOB fields) and sort.
     * - Uses date_of_birth if db_status = 0
     * - Uses date_of_birth_age if db_status = 1
     * - Calls calculateAgeYears() with a SINGLE date string (no array → avoids TypeError)
     */
    private function attachAgeAndSort(array $rows): array
    {
        // Compute age_years for each row
        foreach ($rows as &$row) {
            $dbStatus = (int)($row['db_status'] ?? 0);
            $dobToUse = null;

            if ($dbStatus === 1 && !empty($row['date_of_birth_age']) && $row['date_of_birth_age'] !== '0000-00-00') {
                $dobToUse = $row['date_of_birth_age'];
            } else {
                $dobToUse = !empty($row['date_of_birth']) && $row['date_of_birth'] !== '0000-00-00'
                    ? $row['date_of_birth']
                    : null;
            }

            $row['age_years'] = $this->calculateAgeYears($dobToUse);
            $row['participation_count'] = (int)($row['participation_count'] ?? 0);
            $row['class_short'] = $row['class_short'] ?? '';
        }
        unset($row);

        // Sort: age_years ASC, then class_short, then first_name+last_name
        usort($rows, function ($a, $b) {
            $aAge = (int)($a['age_years'] ?? 0);
            $bAge = (int)($b['age_years'] ?? 0);
            if ($aAge !== $bAge) {
                return $aAge <=> $bAge;
            }

            $aClass = (string)($a['class_short'] ?? '');
            $bClass = (string)($b['class_short'] ?? '');
            if ($aClass !== $bClass) {
                return strcmp($aClass, $bClass);
            }

            $aName = trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
            $bName = trim(($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? ''));
            return strcmp($aName, $bName);
        });

        return $rows;
    }

    /**
     * Data provider for the events report (JSON)
     * (unchanged except for any minor formatting)
     */

public function eventsData()
{
       $campusId  = (int) (session('member_campusid')  ?? 0);
    $sessionId = (int) (session('member_sessionid') ?? 0);

    // 1) Fetch events (include event_time + order column)
    $qb = $this->db->table('sports_events')
        ->select('event_id, campus_id, session_id, event_name, event_type, gender, event_date, event_time, `order`');

    if ($campusId > 0)  { $qb->where('campus_id',  $campusId); }
    if ($sessionId > 0) { $qb->where('session_id', $sessionId); }

    // ✅ Only active events
    $qb->where('status', 1);

    // Order by "order" column first, then date
    $qb->orderBy('`order` ASC', '', false);

    $events = $qb->get()->getResultArray();

    // Fallback: only campus filter if nothing found (optional)
    if (! $events && $campusId > 0 && $sessionId > 0) {
        $events = $this->db->table('sports_events')
            ->select('event_id, campus_id, session_id, event_name, event_type, gender, event_date, event_time, `order`')
            ->where('campus_id', $campusId)
            ->where('status', 1)        // ✅ filter here as well
            ->orderBy('`order` ASC', '', false)
            ->get()->getResultArray();
    }
    if (! $events) {
        return $this->response->setJSON([
            'data'  => [],
            'debug' => ['reason' => 'no_events', 'campusId' => $campusId, 'sessionId' => $sessionId],
        ]);
    }

    $eventIds = array_map(static fn($e) => (int)$e['event_id'], $events);
    $inIds    = implode(',', array_map('intval', $eventIds));

    // Preload class/section mapping for participants
    $classMap = [];
    if ($sessionId > 0) {
        $sqlClasses = "
            SELECT sc.student_id, c.class_name, c.class_short_name, sec.section_name
            FROM student_class sc
            JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            JOIN classes c        ON c.class_id    = cs.class_id
            JOIN sections sec     ON sec.section_id= cs.section_id
            WHERE sc.session_id = ?
        ";
        foreach ($this->db->query($sqlClasses, [$sessionId])->getResultArray() as $r) {
            $sid = (int)$r['student_id'];
            $classMap[$sid] = [
                'class_name'       => $r['class_name'] ?? '',
                'class_short_name' => $r['class_short_name'] ?? '',
                'section_name'     => $r['section_name'] ?? '',
            ];
        }
    }

    // ========== NEW: global participation tracking (per student) ==========
    $studentEventCount  = []; // student_id => number of different events
    $studentEventNames  = []; // student_id => [event_name, ...]

    // 2) Individual participants grouped by house (with result position)
    $individual = []; // [event_id] => [ {house_id, ..., students:[{...}]}, ... ]
    if ($inIds !== '') {
        $sqlInd = "
            SELECT 
                se.event_id,
                se.house_id,
                h.house_name,
                h.color_code,
                s.student_id,
                s.first_name,
                s.last_name,
                s.date_of_birth,
                s.profile_photo,
                r.position AS result_position,
                ev.event_name
            FROM sports_event_entries se
            JOIN students s      ON s.student_id = se.student_id
            LEFT JOIN sports_houses h ON h.house_id = se.house_id
            LEFT JOIN sports_event_results r
              ON r.event_id   = se.event_id
             AND r.student_id = se.student_id
             " . ($sessionId > 0 ? " AND r.session_id = " . $this->db->escape($sessionId) : "") . "
            JOIN sports_events ev ON ev.event_id = se.event_id
            WHERE se.student_id IS NOT NULL
              AND se.event_id IN ($inIds)
            ORDER BY se.event_id, h.house_name, s.first_name, s.last_name
        ";

        foreach ($this->db->query($sqlInd)->getResultArray() as $r) {
            $eid = (int)$r['event_id'];
            $hid = (int)$r['house_id'];
            $sid = (int)$r['student_id'];

            // ---- build student array (without participation fields yet) ----
            $student = [
                'student_id'    => $sid,
                'first_name'    => (string)$r['first_name'],
                'last_name'     => (string)$r['last_name'],
                'profile_photo' => (string)($r['profile_photo'] ?? ''),
                'date_of_birth' => (string)($r['date_of_birth'] ?? ''),
                'position'      => isset($r['result_position']) ? (int)$r['result_position'] : 0,
            ];

            if (isset($classMap[$sid])) {
                $student['class_name']        = $classMap[$sid]['class_name'];
                $student['section_name']      = $classMap[$sid]['section_name'];
                $student['class_short_name']  = $classMap[$sid]['class_short_name'];
            } else {
                $student['class_name']        = '';
                $student['section_name']      = '';
                $student['class_short_name']  = '';
            }

            // ---- collect per-student participation (events) ----
            $evName = (string)$r['event_name'];
            if ($sid > 0 && $evName !== '') {
                if (!isset($studentEventNames[$sid])) {
                    $studentEventNames[$sid] = [];
                }
                if (!in_array($evName, $studentEventNames[$sid], true)) {
                    $studentEventNames[$sid][] = $evName;
                }
            }

            if (! isset($individual[$eid])) {
                $individual[$eid] = [];
            }

            $found = false;
            foreach ($individual[$eid] as &$houseBucket) {
                if ((int)$houseBucket['house_id'] === $hid) {
                    $houseBucket['students'][] = $student;
                    $found = true;
                    break;
                }
            }
            unset($houseBucket);

            if (! $found) {
                $individual[$eid][] = [
                    'house_id'   => $hid,
                    'house_name' => (string)$r['house_name'],
                    'color_code' => (string)$r['color_code'],
                    'students'   => [$student],
                ];
            }
        }
    }

    // 3) Team events: teams and members (with result position)
    $teamsByEvent = []; // [event_id] => [ {team...members:[...]}, ... ]
    if ($inIds !== '') {
        $sqlTeams = "
            SELECT t.team_id, t.event_id, t.team_name, t.house_id, h.house_name, h.color_code
            FROM sports_teams t
            LEFT JOIN sports_houses h ON h.house_id = t.house_id
            WHERE t.event_id IN ($inIds)
            ORDER BY t.event_id, t.team_name
        ";
        foreach ($this->db->query($sqlTeams)->getResultArray() as $r) {
            $eid = (int)$r['event_id'];
            if (! isset($teamsByEvent[$eid])) {
                $teamsByEvent[$eid] = [];
            }
            $teamsByEvent[$eid][ (int)$r['team_id'] ] = [
                'team_id'    => (int)$r['team_id'],
                'team_name'  => (string)$r['team_name'],
                'house_id'   => (int)$r['house_id'],
                'house_name' => (string)$r['house_name'],
                'color_code' => (string)$r['color_code'],
                'members'    => [],
            ];
        }

        $sqlMembers = "
            SELECT 
                stm.team_id,
                t.event_id,
                s.student_id,
                s.first_name,
                s.last_name,
                s.date_of_birth,
                s.profile_photo,
                stm.is_captain,
                r.position AS result_position,
                ev.event_name
            FROM sports_team_members stm
            JOIN sports_teams t ON t.team_id = stm.team_id
            JOIN students s     ON s.student_id = stm.student_id
            LEFT JOIN sports_event_results r
              ON r.event_id   = t.event_id
             AND r.student_id = s.student_id
             " . ($sessionId > 0 ? " AND r.session_id = " . $this->db->escape($sessionId) : "") . "
            JOIN sports_events ev ON ev.event_id = t.event_id
            WHERE t.event_id IN ($inIds)
            ORDER BY t.event_id, stm.team_id, s.first_name, s.last_name
        ";

        foreach ($this->db->query($sqlMembers)->getResultArray() as $r) {
            $eid  = (int)$r['event_id'];
            $tid  = (int)$r['team_id'];
            $sid  = (int)$r['student_id'];

            $stud = [
                'student_id'    => $sid,
                'first_name'    => (string)$r['first_name'],
                'last_name'     => (string)$r['last_name'],
                'date_of_birth' => (string)($r['date_of_birth'] ?? ''),
                'profile_photo' => (string)($r['profile_photo'] ?? ''),
                'is_captain'    => (int)$r['is_captain'] ? 1 : 0,
                'position'      => isset($r['result_position']) ? (int)$r['result_position'] : 0,
            ];

            if (isset($classMap[$sid])) {
                $stud['class_name']        = $classMap[$sid]['class_name'];
                $stud['section_name']      = $classMap[$sid]['section_name'];
                $stud['class_short_name']  = $classMap[$sid]['class_short_name'];
            } else {
                $stud['class_name']        = '';
                $stud['section_name']      = '';
                $stud['class_short_name']  = '';
            }

            // collect per-student participation
            $evName = (string)$r['event_name'];
            if ($sid > 0 && $evName !== '') {
                if (!isset($studentEventNames[$sid])) {
                    $studentEventNames[$sid] = [];
                }
                if (!in_array($evName, $studentEventNames[$sid], true)) {
                    $studentEventNames[$sid][] = $evName;
                }
            }

            if (isset($teamsByEvent[$eid][$tid])) {
                $teamsByEvent[$eid][$tid]['members'][] = $stud;
            }
        }

        foreach ($teamsByEvent as $eid => $teams) {
            $teamsByEvent[$eid] = array_values($teams);
        }
    }

    // ========== build final per-student participation count ==========
    foreach ($studentEventNames as $sid => $evNames) {
        // unique events per student
        $studentEventNames[$sid] = array_values(array_unique($evNames));
        $studentEventCount[$sid] = count($studentEventNames[$sid]);
    }

    // Attach participation summary into $individual and $teamsByEvent
    if (!empty($studentEventCount)) {
        // individual
        foreach ($individual as $eid => &$houseList) {
            foreach ($houseList as &$hb) {
                if (!isset($hb['students']) || !is_array($hb['students'])) {
                    continue;
                }
                foreach ($hb['students'] as &$stu) {
                    $sid = (int)($stu['student_id'] ?? 0);
                    $stu['participation_count']  = $studentEventCount[$sid]  ?? 0;
                    $stu['participation_events'] = $studentEventNames[$sid]  ?? [];
                }
                unset($stu);
            }
            unset($hb);
        }
        unset($houseList);

        // teams
        foreach ($teamsByEvent as $eid => &$teamsList) {
            foreach ($teamsList as &$team) {
                if (!isset($team['members']) || !is_array($team['members'])) {
                    continue;
                }
                foreach ($team['members'] as &$stu) {
                    $sid = (int)($stu['student_id'] ?? 0);
                    $stu['participation_count']  = $studentEventCount[$sid]  ?? 0;
                    $stu['participation_events'] = $studentEventNames[$sid]  ?? [];
                }
                unset($stu);
            }
            unset($team);
        }
        unset($teamsList);
    }

    // 4) Assemble final payload per event
    $out = [];
    foreach ($events as $e) {
        $eid  = (int)$e['event_id'];
        $type = strtolower((string)$e['event_type']);

        // Format date: 15-11-2025 (Saturday)
        $dateDisplay = '';
        if (! empty($e['event_date']) && $e['event_date'] !== '0000-00-00') {
            try {
                $dt = new \DateTime($e['event_date']);
                $dateDisplay = $dt->format('d-m-Y (l)');
            } catch (\Throwable $ex) {
                $dateDisplay = $e['event_date'];
            }
        }

        // Format time: 10:30 AM
        $timeDisplay = '';
        if (! empty($e['event_time']) && $e['event_time'] !== '00:00:00') {
            try {
                $t = \DateTime::createFromFormat('H:i:s', $e['event_time']);
                if ($t) {
                    $timeDisplay = $t->format('h:i A');
                }
            } catch (\Throwable $ex) {
                $timeDisplay = $e['event_time'];
            }
        }

        $row = [
            'event_id'          => $eid,
            'event_name'        => $e['event_name'],
            'event_type'        => $type,
            'gender'            => strtolower((string)$e['gender']),
            'event_date'        => $dateDisplay,   // formatted date+day
            'event_time'        => $timeDisplay,   // formatted time
            'participants_total'=> 0,              // fill below
        ];

        if ($type === 'team') {
            $row['teams'] = $teamsByEvent[$eid] ?? [];
            $row['teams_count'] = isset($row['teams']) ? count($row['teams']) : 0;
            $row['members_count'] = 0;
            foreach ($row['teams'] as $tTeam) {
                $row['members_count'] += isset($tTeam['members']) ? count($tTeam['members']) : 0;
            }
            $row['participants_total'] = $row['members_count'];
        } else {
            $row['houses'] = $individual[$eid] ?? [];
            $row['event_total'] = 0;
            foreach ($row['houses'] as &$h) {
                $h['total'] = isset($h['students']) ? count($h['students']) : 0;
                $row['event_total'] += $h['total'];
            }
            unset($h);
            $row['participants_total'] = $row['event_total'];
        }

        $out[] = $row;
    }

    return $this->response->setJSON(['data' => $out]);
}


    /**
     * YOUR calculateAgeYears METHOD (unchanged as requested)
     */
    private function calculateAgeYears(?string $dob): int
    {
        /**
         * NEW LOGIC:
         * When this function is called, $dob will actually receive
         * an ARRAY in this format:
         *
         * [
         *   'db_status' => 0|1,
         *   'date_of_birth' => 'YYYY-mm-dd',
         *   'date_of_birth_age' => 'YYYY-mm-dd'
         * ]
         *
         * To preserve function signature, we detect & extract values here.
         */

        if (is_array($dob)) {
            $dbStatus = (int)($dob['db_status'] ?? 0);

            // choose the correct DOB based on db_status
            if ($dbStatus === 1) {
                $dob = $dob['date_of_birth_age'] ?? null;
            } else {
                $dob = $dob['date_of_birth'] ?? null;
            }
        }

        // Now $dob is a single date string ("YYYY-mm-dd")
        if (!$dob || $dob === '0000-00-00') {
            return 0;
        }

        try {
            $d = new \DateTime($dob);
            $n = new \DateTime();

            $diff = $d->diff($n);

            $years  = (int) $diff->y;
            $months = (int) $diff->m;

            // Rounding rule:
            if ($months >= 6) {
                $years += 1;
            }

            return $years;

        } catch (\Throwable $e) {
            return 0;
        }
    }
}
