<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AcademicCalendar extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text', 'server']);
        check_permission('admin-academic-session');
    }

    /**
     * Highest session_id for this system (most recently created session row).
     */
    private function getLatestAcademicSessionId(int $systemId): ?int
    {
        if ($systemId <= 0) {
            return null;
        }

        $row = $this->db->table('academic_session')
            ->select('session_id')
            ->where('system_id', $systemId)
            ->orderBy('session_id', 'DESC')
            ->get(1)
            ->getRow();

        return $row ? (int) $row->session_id : null;
    }

    /**
     * Session where start_date <= today <= end_date (system scoped).
     * If multiple rows match, highest session_id wins.
     */
    private function getCurrentAcademicSessionId(int $systemId): ?int
    {
        if ($systemId <= 0) {
            return null;
        }
        $today = date('Y-m-d');
        $row   = $this->db->table('academic_session')
            ->select('session_id')
            ->where('system_id', $systemId)
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->orderBy('session_id', 'DESC')
            ->get(1)
            ->getRow();

        return $row ? (int) $row->session_id : null;
    }

    private function isAcademicSessionCurrentlyActive(int $systemId, int $sessionId): bool
    {
        $current = $this->getCurrentAcademicSessionId($systemId);

        return $current !== null && $current === $sessionId;
    }

    /**
     * One-screen builder: session + terms dates
     */
public function builder()
{
    $schoolinfo = getSchoolInfo();
    $systemId   = $schoolinfo->system_id;

    $isNewSession   = $this->request->getGet('new') === '1' || $this->request->getGet('mode') === 'new';
    $sessionIdParam = (int) $this->request->getGet('session_id');

    // Get all previous sessions for dropdown
    $allSessions = $this->db->table('academic_session')
        ->where('system_id', $systemId)
        ->orderBy('session_id', 'DESC')
        ->get()->getResult();

    // Term counts for all sessions in one query (avoid N+1)
    $sessionTermCounts = [];
    if (! empty($allSessions)) {
        $sessionIds = array_map(static fn ($s) => (int) $s->session_id, $allSessions);
        $countRows   = $this->db->table('terms_session')
            ->select('session_id, COUNT(*) AS c', false)
            ->where('system_id', $systemId)
            ->whereIn('session_id', $sessionIds)
            ->groupBy('session_id')
            ->get()
            ->getResult();
        foreach ($countRows as $cr) {
            $sessionTermCounts[(int) $cr->session_id] = (int) $cr->c;
        }
    }

    $currentSessionId = $this->getCurrentAcademicSessionId($systemId);
    $latestSessionId  = $this->getLatestAcademicSessionId($systemId);

    $latestSession = ! empty($allSessions) ? $allSessions[0] : null;

    // Fetch terms from the latest session (template when creating a new session)
    $previousTerms = [];
    if ($latestSession) {
        $previousTerms = $this->db->table('terms_session ts')
            ->select('ts.*, t.name, t.short_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.system_id', $systemId)
            ->where('ts.session_id', $latestSession->session_id)
            ->orderBy('ts.start_date', 'ASC')
            ->get()->getResult();
    }

    $sessionRow    = null;
    $isEditing     = false;
    $existingTerms = [];
    $termsCount    = 3;

    $isFirstSession = empty($allSessions);

    if ($isNewSession) {
        // Explicit "New session" — create flow (save inserts a new row)
    } elseif ($sessionIdParam > 0) {
        $sessionRow = $this->db->table('academic_session')
            ->where('system_id', $systemId)
            ->where('session_id', $sessionIdParam)
            ->get()->getRow();

        if ($sessionRow) {
            $isEditing = true;
        }
    } elseif ($latestSession) {
        // Default: load the latest academic session for editing
        $sessionRow = $latestSession;
        $isEditing  = true;
    }

    if ($isEditing && $sessionRow) {
        $existingTerms = $this->db->table('terms_session ts')
            ->select('ts.*, t.name, t.short_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.system_id', $systemId)
            ->where('ts.session_id', (int) $sessionRow->session_id)
            ->orderBy('ts.start_date', 'ASC')
            ->get()->getResult();

        $termsCount = count($existingTerms) ?: 3;
    }

    // New session: pre-fill from previous session structure
    if (! $isEditing && ! $isFirstSession && ! empty($previousTerms)) {
        $existingTerms = $previousTerms;
        $termsCount    = count($previousTerms);
    }

    if (! $isEditing) {
        if (! empty($allSessions)) {
            $nextSessionName = $this->generateNextSessionName($allSessions);
            $session_name    = $nextSessionName;

            $prevEndDate = $allSessions[0]->end_date;

            $startDateTime = new \DateTime($prevEndDate);
            $startDateTime->modify('+1 day');

            $dow = (int) $startDateTime->format('N');
            if ($dow !== 1) {
                $offset = 8 - $dow;
                $startDateTime->modify("+{$offset} day");
            }
            $start_date = $startDateTime->format('Y-m-d');

            $endDateTime = clone $startDateTime;
            $endDateTime->modify('+1 year')->modify('-1 day');

            $dowEnd = (int) $endDateTime->format('N');
            if ($dowEnd !== 7) {
                $offsetEnd = 7 - $dowEnd;
                $endDateTime->modify("+{$offsetEnd} day");
            }
            $end_date = $endDateTime->format('Y-m-d');
        } else {
            $start_date   = $this->getNextMonday();
            $end_date     = $this->getEndDateFromStart($start_date);
            $session_name = date('Y') . '-' . substr(date('Y') + 1, -2);
        }
        $session_id = 0;
    } else {
        $session_id   = (int) $sessionRow->session_id;
        $session_name = $sessionRow->session_name;
        $start_date   = $sessionRow->start_date;
        $end_date     = $sessionRow->end_date;
    }

    $weekTypes = $this->db->table('week_type')
        ->orderBy('type_id', 'ASC')
        ->get()->getResult();

    $termsMaster = $this->db->table('terms')
        ->where('system_id', $systemId)
        ->where('status', 1)
        ->orderBy('term_id', 'ASC')
        ->get()->getResult();

    $termSessionsByTerm = [];
    if ($isEditing && $sessionRow) {
        $tsRows = $this->db->table('terms_session')
            ->where('system_id', $systemId)
            ->where('session_id', $sessionRow->session_id)
            ->orderBy('term_session_id', 'ASC')
            ->get()->getResult();

        foreach ($tsRows as $r) {
            $termSessionsByTerm[(int) $r->term_id] = $r;
        }
    }

    return view('admin/academic_calendar_builder', [
        'mode'                 => $isEditing ? 'edit' : 'add',
        'session_id'           => $session_id,
        'session_name'         => $session_name,
        'start_date'           => $start_date,
        'end_date'             => $end_date,
        'terms'                => $termsMaster,
        'termSessionsByTerm'   => $termSessionsByTerm,
        'weekTypes'            => $weekTypes,
        'allSessions'          => $allSessions,
        'existingTerms'        => $existingTerms,
        'termsCount'           => $termsCount,
        'isFirstSession'       => $isFirstSession,
        'isEditing'            => $isEditing,
        'isNewSession'         => $isNewSession,
        'sessionTermCounts'    => $sessionTermCounts,
        'current_session_id'   => $currentSessionId,
        'latest_session_id'    => $latestSessionId,
    ]);
}
    /**
     * Generate next session name based on existing sessions
     */
 /**
 * Generate next session name based on existing sessions
 */
private function generateNextSessionName($allSessions)
{
    if (empty($allSessions)) {
        // No existing sessions, create first session
        $currentYear = date('Y');
        return $currentYear . '-' . substr($currentYear + 1, -2);
    }

    // Get the latest session
    $latestSession = $allSessions[0];
    $latestName = $latestSession->session_name;

    // Parse session name like "2024-25" or "2024-2025"
    if (preg_match('/(\d{4})-(\d{2,4})/', $latestName, $matches)) {
        $startYear = (int)$matches[1];
        $endYearPart = $matches[2];

        // If end year is 2 digits (e.g., 25 from 2024-25)
        if (strlen($endYearPart) == 2) {
            $nextStartYear = $startYear + 1;
            return $nextStartYear . '-' . substr($nextStartYear + 1, -2);
        }
        // If end year is 4 digits (e.g., 2025 from 2024-2025)
        else if (strlen($endYearPart) == 4) {
            $nextStartYear = $startYear + 1;
            $nextEndYear = $nextStartYear + 1;
            return $nextStartYear . '-' . $nextEndYear;
        }
    }

    // Fallback: try to extract years from session name
    if (preg_match('/(\d{4})/', $latestName, $matches)) {
        $lastYear = (int)$matches[1];
        $nextStartYear = $lastYear + 1;
        return $nextStartYear . '-' . substr($nextStartYear + 1, -2);
    }

    // Ultimate fallback
    $currentYear = date('Y');
    return $currentYear . '-' . substr($currentYear + 1, -2);
}

    /**
     * Get next Monday from today
     */
   /**
 * Get next Monday from a given date or today
 */
private function getNextMonday($fromDate = null)
{
    if ($fromDate) {
        $date = new \DateTime($fromDate);
    } else {
        $date = new \DateTime();
    }

    $dow = (int) $date->format('N');

    if ($dow !== 1) {
        $offset = 8 - $dow;
        $date->modify("+{$offset} day");
    }

    return $date->format('Y-m-d');
}

/**
 * Calculate end date from start date (approx 1 year, snapped to Sunday)
 */
private function getEndDateFromStart($startDate)
{
    $start = new \DateTime($startDate);
    $end = clone $start;
    $end->modify('+1 year')->modify('-1 day');

    // Snap to Sunday
    $dowEnd = (int) $end->format('N');
    if ($dowEnd !== 7) {
        $offsetEnd = 7 - $dowEnd;
        $end->modify("+{$offsetEnd} day");
    }

    return $end->format('Y-m-d');
}
    /**
     * Calculate end date (approx 1 year, snapped to Sunday)
     */


    /**
     * Save session + terms + generate weeks
     */
    public function save()
    {
        $schoolinfo = getSchoolInfo();
        $systemId   = $schoolinfo->system_id;
        $userId     = $this->session->get('member_userid');
        $now        = date('Y-m-d H:i:s');

        $request = $this->request;

        $sessionId   = (int) $request->getPost('session_id');
        $sessionName = trim((string)$request->getPost('session_name'));
        $startDate   = $request->getPost('start_date');
        $endDate     = $request->getPost('end_date');

        if ($sessionName === '' || $startDate === '' || $endDate === '') {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Session name, start date and end date are required.',
            ]);
        }

        if ($endDate < $startDate) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Session end date must be after start date.',
            ]);
        }

        // Collect terms from posted arrays
        $termNames  = (array) $request->getPost('term_name');
        $termShorts = (array) $request->getPost('term_short');
        $termStarts = (array) $request->getPost('term_start');
        $termEnds   = (array) $request->getPost('term_end');
        $termIds    = (array) $request->getPost('term_id'); // For existing terms

        // Week type selections
        $weekTypePost = (array) $request->getPost('week_type');
        $termSessionIdsPost = (array) $request->getPost('term_session_id');

        // Default week_type_id
        $defaultWeekTypeId = $this->getDefaultWeekTypeId($systemId);

        $preparedTerms = [];

        foreach ($termNames as $idx => $rawName) {
            $name  = trim((string) $rawName);
            $s     = $termStarts[$idx] ?? '';
            $e     = $termEnds[$idx]   ?? '';
            $short = trim((string)($termShorts[$idx] ?? ''));
            $termId = isset($termIds[$idx]) ? (int)$termIds[$idx] : 0;

            if ($name === '' && $s === '' && $e === '') {
                continue;
            }

            if ($name === '' || $s === '' || $e === '') {
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => 'Each term row must have Name, Start date and End date.',
                ]);
            }

            if ($s < $startDate || $e > $endDate) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => "Term \"{$name}\" dates must be inside the academic session range.",
                ]);
            }

            if ($e < $s) {
                return $this->response->setJSON([
                    'success' => false,
                    'msg'     => "Term \"{$name}\" end date must be after start date.",
                ]);
            }

            $preparedTerms[] = [
                'idx'              => $idx,
                'name'             => $name,
                'short'            => $short,
                'start'            => $s,
                'end'              => $e,
                'term_id'          => $termId,
                'term_session_id'  => isset($termSessionIdsPost[$idx]) ? (int) $termSessionIdsPost[$idx] : 0,
            ];
        }

        if (empty($preparedTerms)) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Please define at least one term with name and date range.',
            ]);
        }

        $this->db->transBegin();

        $isUpdate = $sessionId > 0;

        // 1) Save academic session
        if ($sessionId > 0) {
            $this->db->table('academic_session')
                ->where('system_id', $systemId)
                ->where('session_id', $sessionId)
                ->update([
                    'session_name' => $sessionName,
                    'start_date'   => $startDate,
                    'end_date'     => $endDate,
                    'updated_date' => $now,
                    'user_id'      => $userId,
                ]);
        } else {
            $this->db->table('academic_session')->insert([
                'system_id'    => $systemId,
                'session_name' => $sessionName,
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'created_date' => $now,
                'user_id'      => $userId,
            ]);
            $sessionId = $this->db->insertID();
        }

        // 2) Upsert terms + terms_session (on update: keep existing rows — no deletes)
        $termSessionMeta = [];

        foreach ($preparedTerms as $tRow) {
            $idx    = $tRow['idx'];
            $name   = $tRow['name'];
            $short  = $tRow['short'];
            $s      = $tRow['start'];
            $e      = $tRow['end'];
            $termId = $tRow['term_id'];
            $tsId   = (int) ($tRow['term_session_id'] ?? 0);

            if ($short === '') {
                $short = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
                if ($short === '') {
                    $short = 'T';
                }
                $short = substr($short, 0, 4);
            }

            if ($termId > 0) {
                $this->db->table('terms')
                    ->where('term_id', $termId)
                    ->update([
                        'name'         => $name,
                        'short_name'   => $short,
                        'updated_date' => $now,
                        'user_id'      => $userId,
                    ]);
            } else {
                $termRow = $this->db->table('terms')
                    ->where('system_id', $systemId)
                    ->where('name', $name)
                    ->get()->getRow();

                if ($termRow) {
                    $termId = (int) $termRow->term_id;

                    if ($termRow->short_name !== $short) {
                        $this->db->table('terms')
                            ->where('term_id', $termId)
                            ->update([
                                'short_name'   => $short,
                                'updated_date' => $now,
                                'user_id'      => $userId,
                            ]);
                    }
                } else {
                    $this->db->table('terms')->insert([
                        'system_id'    => $systemId,
                        'name'         => $name,
                        'short_name'   => $short,
                        'status'       => 1,
                        'created_date' => $now,
                        'user_id'      => $userId,
                    ]);
                    $termId = (int) $this->db->insertID();
                }
            }

            $existingTs = null;
            if ($tsId > 0) {
                $existingTs = $this->db->table('terms_session')
                    ->where('system_id', $systemId)
                    ->where('session_id', $sessionId)
                    ->where('term_session_id', $tsId)
                    ->get()->getRow();
            }
            if (! $existingTs && $termId > 0) {
                $existingTs = $this->db->table('terms_session')
                    ->where('system_id', $systemId)
                    ->where('session_id', $sessionId)
                    ->where('term_id', $termId)
                    ->get()->getRow();
            }

            if ($existingTs) {
                $tsId = (int) $existingTs->term_session_id;
                $this->db->table('terms_session')
                    ->where('term_session_id', $tsId)
                    ->update([
                        'term_id'    => $termId,
                        'start_date' => $s,
                        'end_date'   => $e,
                        'status'     => 1,
                    ]);
            } else {
                $this->db->table('terms_session')->insert([
                    'system_id'    => $systemId,
                    'term_id'      => $termId,
                    'session_id'   => $sessionId,
                    'start_date'   => $s,
                    'end_date'     => $e,
                    'status'       => 1,
                    'created_date' => $now,
                    'user_id'      => $userId,
                ]);
                $tsId = (int) $this->db->insertID();
            }

            $termSessionMeta[] = [
                'idx'             => $idx,
                'term_session_id' => $tsId,
                'term_id'         => $termId,
                'short_name'      => $short,
                'start_date'      => $s,
                'end_date'        => $e,
            ];
        }

        // 3) Sync term_weeks: add missing weeks, update types — never delete existing rows
        $sessionSuffix = substr($sessionName, -2);
        $this->syncTermWeeksForSession(
            $systemId,
            $userId,
            $now,
            $sessionSuffix,
            $termSessionMeta,
            $weekTypePost,
            $defaultWeekTypeId
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Transaction failed while saving academic calendar.',
            ]);
        }

        return $this->response->setJSON([
            'success'  => true,
            'msg'      => $isUpdate
                ? 'Academic calendar updated with terms and weeks.'
                : 'Academic calendar saved with terms and weeks.',
            'redirect' => base_url('admin/academic-calendar/builder'),
        ]);
    }

    private function getDefaultWeekTypeId($systemId)
    {
        $defaultWeekTypeId = 1;
        try {
            $wtRow = $this->db->table('week_type')
                ->where('system_id', $systemId)
                ->where('short_name', 'study')
                ->get()->getRow();
            if ($wtRow) {
                $defaultWeekTypeId = (int) $wtRow->type_id;
            }
        } catch (\Throwable $e) {
            // Silent fallback
        }
        return $defaultWeekTypeId;
    }

    /**
     * Add missing term_weeks and update week_type_id when provided — never delete rows.
     *
     * @param list<array{idx:int, term_session_id:int, term_id:int, short_name:string, start_date:string, end_date:string}> $termSessionMeta
     * @param array<int, array<int|string, mixed>> $weekTypePost
     */
    private function syncTermWeeksForSession(
        int $systemId,
        int $userId,
        string $now,
        string $sessionSuffix,
        array $termSessionMeta,
        array $weekTypePost,
        int $defaultWeekTypeId
    ): void {
        if ($termSessionMeta === []) {
            return;
        }

        $tsIds = array_values(array_unique(array_map(static fn ($r) => (int) $r['term_session_id'], $termSessionMeta)));
        $existingByTs = [];

        if ($tsIds !== []) {
            $weekRows = $this->db->table('term_weeks')
                ->whereIn('term_session_id', $tsIds)
                ->get()
                ->getResult();

            foreach ($weekRows as $w) {
                $tsKey = (int) $w->term_session_id;
                $noKey = (int) $w->week_no;
                $existingByTs[$tsKey][$noKey] = $w;
            }
        }

        foreach ($termSessionMeta as $row) {
            $formIdx       = $row['idx'];
            $termSessionId = (int) $row['term_session_id'];
            $shortName     = $row['short_name'] ?: ('T' . $row['term_id']);
            $tsStart       = $row['start_date'];
            $tsEnd         = $row['end_date'];

            $weekTypesForTerm = [];
            if (isset($weekTypePost[$formIdx]) && is_array($weekTypePost[$formIdx])) {
                $weekTypesForTerm = $weekTypePost[$formIdx];
            }

            $mStart = new \DateTime($tsStart);
            $mEnd   = new \DateTime($tsEnd);

            $dow = (int) $mStart->format('N');
            if ($dow !== 1) {
                $offset = 1 - $dow;
                if ($offset < 0) {
                    $offset += 7;
                }
                $mStart->modify("{$offset} day");
            }

            $weekNo = 1;

            while ($mStart <= $mEnd) {
                $weekStart = clone $mStart;
                $weekEnd   = clone $mStart;
                $weekEnd->modify('+6 day');

                if ($weekEnd > $mEnd) {
                    break;
                }

                $weekName   = $sessionSuffix . '-' . $shortName . '-W' . $weekNo;
                $weekTypeId = $defaultWeekTypeId;
                if (isset($weekTypesForTerm[$weekNo]) && $weekTypesForTerm[$weekNo] !== '') {
                    $weekTypeId = (int) $weekTypesForTerm[$weekNo];
                }

                $startStr = $weekStart->format('Y-m-d');
                $endStr   = $weekEnd->format('Y-m-d');
                $existing = $existingByTs[$termSessionId][$weekNo] ?? null;

                if ($existing) {
                    $update = [
                        'week_name' => $weekName,
                    ];
                    if (isset($weekTypesForTerm[$weekNo]) && $weekTypesForTerm[$weekNo] !== '') {
                        $update['week_type_id'] = $weekTypeId;
                    }
                    $this->db->table('term_weeks')
                        ->where('term_weeks_id', (int) $existing->term_weeks_id)
                        ->update($update);
                } else {
                    $this->db->table('term_weeks')->insert([
                        'term_session_id' => $termSessionId,
                        'system_id'       => $systemId,
                        'week_no'         => $weekNo,
                        'start_date'      => $startStr,
                        'end_date'        => $endStr,
                        'week_name'       => $weekName,
                        'week_type_id'    => $weekTypeId,
                        'created_date'    => $now,
                        'user_id'         => $userId,
                    ]);

                    $existingByTs[$termSessionId][$weekNo] = (object) [
                        'term_weeks_id' => (int) $this->db->insertID(),
                        'week_no'       => $weekNo,
                    ];
                }

                $weekNo++;
                $mStart->modify('+7 day');
            }
        }
    }

    public function ajaxQuickAdd()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'msg'     => 'Invalid request'
            ]);
        }

        $name      = trim($this->request->getPost('name') ?? '');
        $shortName = trim($this->request->getPost('short_name') ?? '');

        if ($name === '') {
            return $this->response->setJSON([
                'success'  => false,
                'msg'      => 'Please enter Term Name.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if ($shortName === '') {
            $shortName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
            if ($shortName === '') {
                $shortName = 'T';
            }
            $shortName = substr($shortName, 0, 4);
        }

        try {
            $school   = getSchoolInfo();
            $systemId = $school->system_id ?? 0;
            $userId   = $this->session->get('member_userid') ?? 0;
            $now      = date('Y-m-d H:i:s');

            $data = [
                'system_id'    => $systemId,
                'name'         => $name,
                'short_name'   => $shortName,
                'status'       => 1,
                'created_date' => $now,
                'user_id'      => $userId,
            ];

            $this->db->table('terms')->insert($data);
            $newId = $this->db->insertID();

            return $this->response->setJSON([
                'success'  => true,
                'msg'      => 'Term added.',
                'term'     => [
                    'term_id'    => (int) $newId,
                    'name'       => $name,
                    'short_name' => $shortName,
                ],
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ajaxQuickAdd term failed: {msg}', ['msg' => $e->getMessage()]);
            return $this->response->setJSON([
                'success'  => false,
                'msg'      => 'Server Error: ' . $e->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        }
    }
}
