<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use DateInterval;
use DateTimeImmutable;

class Attendance extends BaseController
{
    /**
     * @var array{table: string, studentCol: string, dateCol: string, statusCol: ?string, lcCol: ?string, elCol: ?string}|null
     */
    private $attMeta = null;

    public function __construct()
    {
        helper(['url', 'server', 'parent_portal']);
    }

    public function index()
    {
        $auth = $this->session->get('auth');
        if (! $auth || empty($auth['logged_in'])) {
            return redirect()->route('login');
        }

        $role = $auth['role'] ?? '';
        $sid  = (int) ($this->session->get('active_student_id') ?? 0);

        if ($role === 'student') {
            $sid = (int) ($auth['student_id'] ?? $sid);
        }

        if ($role === 'parent' && $sid <= 0) {
            $kids = \parent_portal_get_children((int) $auth['user_id']);
            if (! empty($kids)) {
                $sid = (int) $kids[0]['student_id'];
                $this->session->set('active_student_id', $sid);
            }
        }

        if (! $sid) {
            return redirect()->route('dashboard')->with('error', 'No active student selected.');
        }

        $this->assertParentOwnsStudentOrFail($sid);

        $sessionInfo = $this->getCurrentAcademicSessionForStudent($sid); // for header + fallback
        $systemId    = $this->getSystemIdForStudent($sid);

        // Build session/class history from student_class (can include past years)
        $history = $this->db->table('student_class sc')
            ->select('sc.session_id, sc.cls_sec_id, as.session_name, as.start_date, as.end_date, c.class_short_name, sec.section_name')
            ->join('academic_session as', 'as.session_id = sc.session_id', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->where('sc.student_id', $sid)
            ->where('sc.session_id IS NOT NULL', null, false)
            ->orderBy('sc.session_id', 'DESC')
            ->get()
            ->getResultArray();

        $sessions = [];
        foreach ($history as $row) {
            $sessId = (int) ($row['session_id'] ?? 0);
            if ($sessId <= 0 || isset($sessions[$sessId])) {
                continue;
            }
            $sessions[$sessId] = [
                'session_id'      => $sessId,
                'session_name'    => (string) ($row['session_name'] ?? ('Session ' . $sessId)),
                'start_date'      => (string) ($row['start_date'] ?? ''),
                'end_date'        => (string) ($row['end_date'] ?? ''),
                'class_short'     => (string) ($row['class_short_name'] ?? ''),
                'section_name'    => (string) ($row['section_name'] ?? ''),
                'cls_sec_id'      => (int) ($row['cls_sec_id'] ?? 0),
                'terms'           => [],
            ];
        }

        if ($systemId > 0 && ! empty($sessions)) {
            $termRows = $this->db->table('terms_session ts')
                ->select('ts.term_session_id, ts.session_id, ts.start_date, ts.end_date, t.name AS term_name')
                ->join('terms t', 't.term_id = ts.term_id', 'inner')
                ->where('ts.system_id', $systemId)
                ->whereIn('ts.session_id', array_keys($sessions))
                ->orderBy('ts.start_date', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($termRows as $tr) {
                $sessId = (int) ($tr['session_id'] ?? 0);
                if (! isset($sessions[$sessId])) {
                    continue;
                }
                $sessions[$sessId]['terms'][] = [
                    'term_session_id' => (int) ($tr['term_session_id'] ?? 0),
                    'term_name'       => (string) ($tr['term_name'] ?? 'Term'),
                    'start_date'      => (string) ($tr['start_date'] ?? ''),
                    'end_date'        => (string) ($tr['end_date'] ?? ''),
                ];
            }
        }

        $currentSessionId = (int) ($sessionInfo['session_id'] ?? 0);

        // One attendance fetch for all term ranges (summary for every session/term)
        $fullByDate = [];
        $minD = null;
        $maxD = null;
        foreach ($sessions as $sess) {
            foreach ($sess['terms'] ?? [] as $t) {
                $ds = $this->normalizeDateKey((string) ($t['start_date'] ?? ''));
                $de = $this->normalizeDateKey((string) ($t['end_date'] ?? ''));
                if ($ds === null || $de === null) {
                    continue;
                }
                if ($minD === null || $ds < $minD) {
                    $minD = $ds;
                }
                if ($maxD === null || $de > $maxD) {
                    $maxD = $de;
                }
            }
        }
        if ($minD !== null && $maxD !== null) {
            $fullByDate = $this->fetchAttendanceByDateRange($sid, $minD, $maxD);
        }

        foreach ($sessions as $sessId => &$sess) {
            $sess['is_current'] = ($currentSessionId > 0 && (int) $sessId === $currentSessionId);
            foreach ($sess['terms'] as &$term) {
                $term['summary'] = $this->summarizeTermWeekdays(
                    (string) ($term['start_date'] ?? ''),
                    (string) ($term['end_date'] ?? ''),
                    $fullByDate
                );
            }
            unset($term);
        }
        unset($sess);

        $children = ($role === 'parent') ? \parent_portal_get_children((int) $auth['user_id']) : [];

        return view('frontend/attendance/index', [
            'role'               => $role,
            'name'               => $auth['name'] ?? '',
            'studentId'          => $sid,
            'children'           => $children,
            'session_info'       => $sessionInfo,
            'sessions'           => array_values($sessions),
            'current_session_id' => $currentSessionId,
        ]);
    }

    /**
     * AJAX: Render weeks grid for all weeks within a term_session.
     */
    public function termWeeks(int $termSessionId = 0)
    {
        $auth = $this->session->get('auth');
        if (! $auth || empty($auth['logged_in'])) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'message' => 'Unauthorized']);
        }

        $role = $auth['role'] ?? '';
        $sid  = (int) ($this->session->get('active_student_id') ?? 0);
        if ($role === 'student') {
            $sid = (int) ($auth['student_id'] ?? $sid);
        }
        if (! $sid) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'No active student']);
        }

        $this->assertParentOwnsStudentOrFail($sid);

        $termSessionId = (int) $termSessionId;
        if ($termSessionId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Invalid term']);
        }

        $systemId = $this->getSystemIdForStudent($sid);
        if ($systemId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'System not found']);
        }

        $term = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, ts.session_id, ts.start_date, ts.end_date, t.name AS term_name')
            ->join('terms t', 't.term_id = ts.term_id', 'inner')
            ->where('ts.term_session_id', $termSessionId)
            ->where('ts.system_id', $systemId)
            ->get()
            ->getRowArray();

        if (! $term) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'message' => 'Term not found']);
        }

        $current = $this->getCurrentAcademicSessionForStudent($sid);
        if (! $current || (int) ($term['session_id'] ?? 0) !== (int) ($current['session_id'] ?? 0)) {
            return $this->response->setStatusCode(403)->setJSON([
                'ok'      => false,
                'message' => 'Weekly attendance detail is only available for the current academic session.',
            ]);
        }

        $weeksRows = $this->db->table('term_weeks')
            ->select('term_weeks_id, week_no, start_date, end_date')
            ->where('term_session_id', $termSessionId)
            ->where('system_id', $systemId)
            ->orderBy('start_date', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($weeksRows)) {
            $html = '<div class="alert alert-info mb-0 text-center">No weeks found for this term.</div>';
            return $this->response->setJSON(['ok' => true, 'html' => $html]);
        }

        // Fetch attendance only for this term range (fast)
        $byDate = $this->fetchAttendanceByDateRange($sid, (string) ($term['start_date'] ?? ''), (string) ($term['end_date'] ?? ''));

        $weeks = [];
        foreach ($weeksRows as $wr) {
            $start = $this->parseYmd((string) ($wr['start_date'] ?? ''));
            $end   = $this->parseYmd((string) ($wr['end_date'] ?? ''));
            if (! $start || ! $end) {
                continue;
            }
            $weeks[] = [
                'week_label' => 'Week ' . (int) ($wr['week_no'] ?? 0) . ' · ' . $start->format('j M') . ' – ' . $end->format('j M Y'),
                'days'       => $this->buildMonFriGridForRange($start->format('Y-m-d'), $end->format('Y-m-d'), $byDate),
            ];
        }

        $html = view('frontend/attendance/_weeks', ['weeks' => $weeks]);

        return $this->response->setJSON(['ok' => true, 'html' => $html]);
    }

    /**
     * @return array{session_id: int, session_name: string, start_date: string, end_date: string}|null
     */
    private function getCurrentAcademicSessionForStudent(int $studentId): ?array
    {
        $student = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();

        if (! $student || empty($student['campus_id'])) {
            return null;
        }

        $campus = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', (int) $student['campus_id'])
            ->get()
            ->getRowArray();

        if (! $campus || empty($campus['system_id'])) {
            return null;
        }

        $systemId = (int) $campus['system_id'];

        $session = $this->db->table('academic_session')
            ->select('session_id, session_name, start_date, end_date')
            ->where('system_id', $systemId)
            ->where('CURDATE() BETWEEN start_date AND end_date', null, false)
            ->orderBy('start_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! $session) {
            $session = $this->db->table('academic_session')
                ->select('session_id, session_name, start_date, end_date')
                ->where('system_id', $systemId)
                ->orderBy('start_date', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        if (! $session) {
            return null;
        }

        return [
            'session_id'   => (int) $session['session_id'],
            'session_name' => (string) ($session['session_name'] ?? ''),
            'start_date'   => (string) ($session['start_date'] ?? ''),
            'end_date'     => (string) ($session['end_date'] ?? ''),
        ];
    }

    /**
     * Weekday (Mon–Fri) stats for a term; end date capped to today for ongoing terms.
     *
     * @param array<string, string> $byDate
     * @return array{
     *   working_days: int,
     *   present: int,
     *   absent: int,
     *   leave: int,
     *   late: int,
     *   early_leave: int,
     *   no_record: int,
     *   present_pct: float|null,
     *   attendance_rate_pct: float|null
     * }
     */
    private function summarizeTermWeekdays(string $startYmd, string $endYmd, array $byDate): array
    {
        $out = [
            'working_days'        => 0,
            'present'             => 0,
            'absent'              => 0,
            'leave'               => 0,
            'late'                => 0,
            'early_leave'         => 0,
            'no_record'           => 0,
            'present_pct'         => null,
            'attendance_rate_pct' => null,
        ];

        $start = $this->parseYmd(substr(trim($startYmd), 0, 10));
        $end   = $this->parseYmd(substr(trim($endYmd), 0, 10));
        if ($start === null || $end === null || $start > $end) {
            return $out;
        }

        $today = new DateTimeImmutable('today');
        if ($end > $today) {
            $end = $today;
        }
        if ($start > $end) {
            return $out;
        }

        $cursor = $start;
        while ($cursor <= $end) {
            $n = (int) $cursor->format('N');
            if ($n >= 6) {
                $cursor = $cursor->add(new DateInterval('P1D'));
                continue;
            }

            $ymd = $cursor->format('Y-m-d');
            $code = $byDate[$ymd] ?? null;
            $code = $code !== null ? strtoupper(trim((string) $code)) : '';

            $out['working_days']++;

            if ($code === '' || $code === '?') {
                $out['no_record']++;
            } elseif ($code === 'P') {
                $out['present']++;
            } elseif ($code === 'A') {
                $out['absent']++;
            } elseif ($code === 'L') {
                $out['leave']++;
            } elseif ($code === 'LC') {
                $out['late']++;
            } elseif ($code === 'EL') {
                $out['early_leave']++;
            } else {
                $out['no_record']++;
            }

            $cursor = $cursor->add(new DateInterval('P1D'));
        }

        $wd = $out['working_days'];
        if ($wd > 0) {
            $attended                     = $out['present'] + $out['late'] + $out['early_leave'];
            $out['present_pct']           = round(100.0 * $out['present'] / $wd, 1);
            $out['attendance_rate_pct']   = round(100.0 * $attended / $wd, 1);
        }

        return $out;
    }

    private function normalizeDateKey(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '' || strpos($raw, '0000-00-00') === 0) {
            return null;
        }
        $t = strtotime($raw);

        return $t ? date('Y-m-d', $t) : null;
    }

    /**
     * @return list<array{date: ?string, weekday: string, day_num: ?int, code: ?string, in_session: bool}>
     */
    private function buildMonFriGridForRange(string $startYmd, string $endYmd, array $byDate): array
    {
        $start = $this->parseYmd($startYmd);
        $end   = $this->parseYmd($endYmd);
        if (! $start || ! $end || $start > $end) {
            return [];
        }

        // Align to Monday of the start week
        $cursor = $start;
        $dow    = (int) $cursor->format('N');
        if ($dow > 1) {
            $cursor = $cursor->sub(new DateInterval('P' . ($dow - 1) . 'D'));
        }

        $sessionStart = $start->format('Y-m-d');
        $sessionEnd   = $end->format('Y-m-d');

        $days = [];
        for ($i = 0; $i < 5; $i++) {
            $day = $cursor->add(new DateInterval('P' . $i . 'D'));
            $ymd = $day->format('Y-m-d');
            $in  = ($ymd >= $sessionStart && $ymd <= $sessionEnd);
            $days[] = [
                'date'       => $in ? $ymd : null,
                'weekday'    => $day->format('D'),
                'day_num'    => $in ? (int) $day->format('j') : null,
                'code'       => $in ? ($byDate[$ymd] ?? null) : null,
                'in_session' => $in,
            ];
        }

        return $days;
    }

    /**
     * @return array<string,string> Y-m-d => code
     */
    private function fetchAttendanceByDateRange(int $studentId, string $startDate, string $endDate): array
    {
        $meta = $this->getAttendanceMeta();
        if ($meta === null) {
            return [];
        }

        $start = $this->normalizeDateKey($startDate) ?? '';
        $end   = $this->normalizeDateKey($endDate) ?? '';

        $select = [
            "a.{$meta['studentCol']} AS student_id",
            "a.{$meta['dateCol']} AS attendance_date",
        ];
        if (! empty($meta['statusCol'])) {
            $select[] = "a.{$meta['statusCol']} AS status";
        }
        if (! empty($meta['lcCol'])) {
            $select[] = "a.{$meta['lcCol']} AS lc_duration";
        }
        if (! empty($meta['elCol'])) {
            $select[] = "a.{$meta['elCol']} AS el_duration";
        }

        $b = $this->db->table($meta['table'] . ' a')
            ->select(implode(', ', $select))
            ->where("a.{$meta['studentCol']}", $studentId);

        if ($start !== '' && $end !== '') {
            $b->where("a.{$meta['dateCol']} >=", $start)
              ->where("a.{$meta['dateCol']} <=", $end);
        }

        $q = $b->orderBy("a.{$meta['dateCol']}", 'ASC')->get();
        if ($q === false) {
            return [];
        }

        $rows = $q->getResultArray();
        if (($meta['statusCol'] ?? '') === 'present') {
            foreach ($rows as &$r) {
                $r['status'] = ((string) ($r['status'] ?? '') === '1' || ($r['status'] ?? null) === 1) ? 'P' : 'A';
            }
            unset($r);
        }

        $byDate = [];
        foreach ($rows as $r) {
            $d = $this->normalizeDateKey((string) ($r['attendance_date'] ?? ''));
            if ($d === null) {
                continue;
            }
            if (! isset($byDate[$d])) {
                $byDate[$d] = $this->rowToDisplayCode($r, ! empty($meta['lcCol']), ! empty($meta['elCol']));
            }
        }

        return $byDate;
    }

    /**
     * @return array{table: string, studentCol: string, dateCol: string, statusCol: ?string, lcCol: ?string, elCol: ?string}|null
     */
    private function getAttendanceMeta(): ?array
    {
        if ($this->attMeta !== null) {
            return $this->attMeta;
        }

        $c     = $this->db;
        $table = null;
        foreach (['attendance', 'student_attendance', 'attendance_register'] as $t) {
            if ($c->tableExists($t)) {
                $table = $t;
                break;
            }
        }
        if ($table === null) {
            return null;
        }

        try {
            $fields = $c->getFieldNames($table);
        } catch (\Throwable $e) {
            return null;
        }

        $has = static function (string $name) use ($fields): bool {
            return in_array($name, $fields, true);
        };

        $colStudentId = $has('student_id') ? 'student_id' : ($has('std_id') ? 'std_id' : ($has('studentid') ? 'studentid' : null));
        $colDate      = $has('attendance_date') ? 'attendance_date' : ($has('att_date') ? 'att_date' : ($has('date') ? 'date' : null));
        $colStatus    = $has('status') ? 'status' : ($has('attendance_status') ? 'attendance_status' : ($has('present') ? 'present' : null));
        $colLc        = $has('lc_duration') ? 'lc_duration' : null;
        $colEl        = $has('el_duration') ? 'el_duration' : null;

        if ($colStudentId === null || $colDate === null) {
            return null;
        }

        $this->attMeta = [
            'table'      => $table,
            'studentCol' => $colStudentId,
            'dateCol'    => $colDate,
            'statusCol'  => $colStatus,
            'lcCol'      => $colLc,
            'elCol'      => $colEl,
        ];

        return $this->attMeta;
    }

    private function getSystemIdForStudent(int $studentId): int
    {
        $student = $this->db->table('students')->select('campus_id')->where('student_id', $studentId)->get()->getRowArray();
        if (! $student || empty($student['campus_id'])) {
            return 0;
        }
        $campus = $this->db->table('campus')->select('system_id')->where('campus_id', (int) $student['campus_id'])->get()->getRowArray();
        if (! $campus || empty($campus['system_id'])) {
            return 0;
        }
        return (int) $campus['system_id'];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function rowToDisplayCode(array $row, bool $hasLc, bool $hasEl): string
    {
        $lc = $hasLc ? (float) ($row['lc_duration'] ?? 0) : 0.0;
        $el = $hasEl ? (float) ($row['el_duration'] ?? 0) : 0.0;

        if ($el > 0) {
            return 'EL';
        }
        if ($lc > 0) {
            return 'LC';
        }

        $st = strtoupper(trim((string) ($row['status'] ?? '')));
        $st = str_replace([' ', '-', '_'], '', $st);

        if (in_array($st, ['P', 'PRESENT', 'PR'], true)) {
            return 'P';
        }
        if (in_array($st, ['A', 'ABSENT', 'AB'], true)) {
            return 'A';
        }
        if (in_array($st, ['L', 'LEAVE', 'LV'], true)) {
            return 'L';
        }
        if (strlen($st) === 1) {
            return $st;
        }

        return '?';
    }

    /**
     * @param array<string, string>|null $sessionInfo
     * @param array<string, string>      $byDate Y-m-d => code
     * @return list<array{week_label: string, days: list<array{date: ?string, weekday: string, day_num: ?int, code: ?string, in_session: bool}>}>
     */
    private function buildWorkingWeeksGrid(?array $sessionInfo, array $byDate): array
    {
        $todayYmd = (new DateTimeImmutable('today'))->format('Y-m-d');

        if ($sessionInfo !== null && ! empty($sessionInfo['start_date']) && ! empty($sessionInfo['end_date'])) {
            $start = $this->parseYmd($sessionInfo['start_date']);
            $end   = $this->parseYmd($sessionInfo['end_date']);
        } else {
            $end   = new DateTimeImmutable('today');
            $start = $end->sub(new DateInterval('P119D'));
        }

        if ($start === null || $end === null) {
            return [];
        }

        if ($end->format('Y-m-d') > $todayYmd) {
            $end = new DateTimeImmutable($todayYmd);
        }

        if ($start > $end) {
            return [];
        }

        $sessionStart = $start->format('Y-m-d');
        $sessionEnd   = $end->format('Y-m-d');

        $cursor = $start;
        $dow    = (int) $cursor->format('N');
        if ($dow > 1) {
            $cursor = $cursor->sub(new DateInterval('P' . ($dow - 1) . 'D'));
        }

        $weeksChrono = [];
        while ($cursor <= $end) {
            $monday = $cursor;
            $days   = [];

            for ($i = 0; $i < 5; $i++) {
                $day = $monday->add(new DateInterval('P' . $i . 'D'));
                $ymd = $day->format('Y-m-d');

                $inSession = ($ymd >= $sessionStart && $ymd <= $sessionEnd);
                $days[]    = [
                    'date'       => $inSession ? $ymd : null,
                    'weekday'    => $day->format('D'),
                    'day_num'    => $inSession ? (int) $day->format('j') : null,
                    'code'       => $inSession ? ($byDate[$ymd] ?? null) : null,
                    'in_session' => $inSession,
                ];
            }

            $any = false;
            foreach ($days as $d) {
                if (! empty($d['in_session'])) {
                    $any = true;
                    break;
                }
            }

            if ($any) {
                $friday = $monday->add(new DateInterval('P4D'));
                $weeksChrono[] = [
                    'week_label' => $monday->format('j M') . ' – ' . $friday->format('j M Y'),
                    'days'       => $days,
                ];
            }

            $cursor = $cursor->add(new DateInterval('P7D'));
        }

        return array_reverse($weeksChrono);
    }

    private function parseYmd(string $raw): ?DateTimeImmutable
    {
        $raw = substr(trim($raw), 0, 10);
        $dt  = DateTimeImmutable::createFromFormat('Y-m-d', $raw);
        if ($dt instanceof DateTimeImmutable) {
            return $dt;
        }
        $t = strtotime($raw);

        return $t ? (new DateTimeImmutable('@' . $t))->setTime(0, 0, 0) : null;
    }

    private function assertParentOwnsStudentOrFail(int $studentId): void
    {
        $auth = session('auth');
        if (! $auth || ($auth['role'] ?? '') !== 'parent') {
            return;
        }
        $row = $this->db->table('students')->select('student_id')->where('student_id', $studentId)->where('parent_id', (int) $auth['user_id'])->get()->getRowArray();
        if (! $row) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }
}
