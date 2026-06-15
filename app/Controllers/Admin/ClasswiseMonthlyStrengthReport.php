<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ClasswiseMonthlyStrengthReport extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form','url']);
        // Use the permission your project already checks for:
        check_permission('admin-datesheet-report');
    }

    public function index()
{
    $db       = \Config\Database::connect();
    $systemId = (int)(getSchoolInfo()->system_id ?? 1);
    $campusId = (int)($this->request->getGet('campus_id') ?? 1);

    // 1) Fetch all sessions (columns)
    $sessions = $db->table('academic_session')
        ->select('session_id, session_name, start_date, end_date')
        ->where('system_id', $systemId)
        ->orderBy('start_date', 'ASC')
        ->get()->getResultArray();

    if (empty($sessions)) {
        return view('admin/classwise-monthly-strength/index', [
            'error' => 'No academic sessions found.',
        ]);
    }

    // Pretty labels like 2024-25
    foreach ($sessions as &$s) {
        $startYear = (int)date('Y', strtotime($s['start_date']));
        $nextYY    = sprintf('%02d', ($startYear + 1) % 100);
        $s['label'] = "{$startYear}-{$nextYY}";
        // normalize first-of-month for safe month steps
        $s['_start_ym'] = (new \DateTime($s['start_date']))->modify('first day of this month')->format('Y-m');
        $s['_end_ym']   = (new \DateTime($s['end_date']))->modify('first day of this month')->format('Y-m');
    }
    unset($s);

    $sessionIds   = array_map('intval', array_column($sessions, 'session_id'));
    $sessionIndex = array_flip($sessionIds); // for quick lookup

    // 2) Query: counts by SESSION + MONTH (join fee month date to session range)
    //    fee_chalan.fee_month is 'YYYY-MM' (VARCHAR(7)).
    $builder = $db->table('fee_chalan fc');
    $builder->select("
            s.session_id,
            DATE_FORMAT(STR_TO_DATE(CONCAT(fc.fee_month,'-01'), '%Y-%m-%d'), '%Y-%m') AS ym,
            COUNT(DISTINCT fc.student_id) AS cnt
        ", false)
        ->join('students st', 'st.student_id = fc.student_id', 'inner')
        ->join(
            'academic_session s',
            "s.system_id = ".$db->escape($systemId)."
             AND STR_TO_DATE(CONCAT(fc.fee_month,'-01'), '%Y-%m-%d') BETWEEN s.start_date AND s.end_date",
            'inner'
        )
        ->where('st.campus_id', $campusId)
        // ->where('fc.status', 'Paid') // uncomment if you only want paid
        ->groupBy('s.session_id, ym')
        ->orderBy('s.start_date', 'ASC')
        ->orderBy('ym', 'ASC');

    // DEBUG (optional)
    log_message('debug', (string) $builder->getCompiledSelect(false));

    $counts = []; // [session_id][ym] => cnt
    foreach ($builder->get()->getResultArray() as $r) {
        $sid = (int)$r['session_id'];
        $ym  = $r['ym'];
        $counts[$sid][$ym] = (int)$r['cnt'];
    }

    // 3) Build a 12-row grid per session.
    // Row labels: use the first session’s month sequence for display.
    $firstStart = new \DateTime($sessions[0]['_start_ym'].'-01');
    $labels = [];
    $rowKeys = []; // 0..11 → canonical month names (for display only)
    for ($i = 0; $i < 12; $i++) {
        $labels[$i]  = $firstStart->format('F');  // 'April', ...
        $rowKeys[$i] = $firstStart->format('Y-m'); // not used for lookup; just for a stable label set
        $firstStart->modify('+1 month');
    }

    // grid: [rowIndex 0..11][session_id] = cnt
    $grid = [];
    for ($i = 0; $i < 12; $i++) {
        $grid[$i] = array_fill_keys($sessionIds, 0);
    }

    // For each session, compute its 12-month sequence starting at session start,
    // and drop the counts we already grouped by (session_id, ym).
    foreach ($sessions as $sess) {
        $sid      = (int)$sess['session_id'];
        $cursor   = new \DateTime($sess['_start_ym'].'-01');
        for ($i = 0; $i < 12; $i++) {
            $ym = $cursor->format('Y-m');
            // Only count months that are actually inside the session window
            $inRange = ($ym >= $sess['_start_ym'] && $ym <= $sess['_end_ym']);
            $grid[$i][$sid] = $inRange ? ($counts[$sid][$ym] ?? 0) : 0;
            $cursor->modify('+1 month');
        }
    }

    // 4) Send to view:
    // - $labels: 12 row labels (Month 1..12, e.g., Apr..Mar)
    // - $sessions: columns with ->label like '2024-25'
    // - $grid[rowIndex][session_id]
    return view('admin/classwise-monthly-strength/index', [
        'campusId' => $campusId,
        'labels'   => $labels,     // ['April','May',...]
        'sessions' => $sessions,   // each has session_id + label 'YYYY-YY'
        'grid'     => $grid,       // [0..11][session_id] => int
    ]);
}



    public function print()
    {
        // Reuse same data as index so print page matches
        return $this->indexAsView('admin/classwise-monthly-strength/print');
    }

    /** ---------- Helpers ---------- */

    private function indexAsView(string $view)
    {
        $systemId  = (int)(getSchoolInfo()->system_id ?? 1);
        $campusId  = (int)($this->request->getGet('campus_id') ?? $this->session->get('member_campusid') ?? 1);

        [$sessions, $months] = $this->fetchSessionsAndMonths($systemId);
        $classes             = $this->fetchClassesWithActiveStudents($campusId);
        $tall                = $this->fetchTallStrength($systemId, $campusId);

        $grid = [];
        foreach ($classes as $cl) {
            $grid[$cl['class_id']] = [];
            foreach ($months as $ym) {
                $grid[$cl['class_id']][$ym] = array_fill_keys(array_column($sessions, 'session_id'), 0);
            }
        }
        foreach ($tall as $row) {
            $cid = (int)$row['class_id'];
            $ym  = $row['month_ym'];
            $sid = (int)$row['session_id'];
            if (isset($grid[$cid][$ym][$sid])) {
                $grid[$cid][$ym][$sid] = (int)$row['strength'];
            }
        }

        $data = [
            'campusId' => $campusId,
            'sessions' => $sessions,
            'months'   => $months,
            'classes'  => $classes,
            'grid'     => $grid,
        ];
        return view($view, $data);
    }

    /**
     * Get sessions for a system and generate the union of months (YYYY-MM)
     * across all sessions.
     */
    private function fetchSessionsAndMonths(int $systemId): array
    {
        $sessions = $this->db->table('academic_session')
            ->where('system_id', $systemId)
            ->orderBy('start_date', 'ASC')
            ->get()->getResultArray();

        // Build union of months across all sessions
        $monthSet = [];
        foreach ($sessions as $s) {
            $start = new \DateTime($s['start_date']);
            $end   = new \DateTime($s['end_date']);
            // normalize to first day of month
            $start->modify('first day of this month');
            $end->modify('first day of this month');

            while ($start <= $end) {
                $monthSet[$start->format('Y-m')] = true;
                $start->modify('+1 month');
            }
        }
        $months = array_keys($monthSet);
        sort($months);

        return [$sessions, $months];
    }

    /**
     * Only classes that actually have active students in this campus
     * (avoids empty tables).
     */
    private function fetchClassesWithActiveStudents(int $campusId): array
    {
        $sql = "
            SELECT DISTINCT c.class_id,
                   COALESCE(c.class_short_name, c.class_name) AS class_name
            FROM classes c
            JOIN class_section cs ON cs.class_id = c.class_id
            JOIN student_class_v3 sc ON sc.cls_sec_id = cs.cls_sec_id AND sc.status = 1
            JOIN students s ON s.student_id = sc.student_id AND s.status = 1 AND s.campus_id = ?
            ORDER BY c.class_id IS NULL,  c.class_name
        ";
        $rows = $this->db->query($sql, [$campusId])->getResultArray();
        if (!$rows) {
            // Fallback: show all classes if none matched (optional)
            $rows = $this->db->table('classes')
                     ->select('class_id, COALESCE(class_short_name, class_name) AS class_name', false)
                     ->orderBy('class_name')->get()->getResultArray();
        }
        return $rows;
    }

    /**
     * Return tall counts grouped by (class_id, session_id, month_ym).
     * fee_chalan.fee_month is assumed 'YYYY-MM' (varchar or char).
     *
     * We attach a fee_month to a session by checking the month date between
     * that session's start/end AND using the student's class mapping for that session.
     */
    private function fetchTallStrength(int $systemId, int $campusId): array
    {
        $sql = "
        SELECT
            c.class_id,
            sc.session_id,
            DATE_FORMAT(STR_TO_DATE(CONCAT(fc.fee_month,'-01'), '%Y-%m-%d'), '%Y-%m') AS month_ym,
            COUNT(DISTINCT fc.student_id) AS strength
        FROM fee_chalan fc
        JOIN students s
             ON s.student_id = fc.student_id
            AND s.status = 1
            AND s.campus_id = :campus:
        JOIN student_class_v3 sc
             ON sc.student_id = s.student_id
            AND sc.status = 1
        JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        JOIN classes c        ON c.class_id = cs.class_id
        JOIN academic_session asess
             ON asess.session_id = sc.session_id
            AND asess.system_id  = :system:
            AND STR_TO_DATE(CONCAT(fc.fee_month,'-01'), '%Y-%m-%d')
                BETWEEN asess.start_date AND asess.end_date
        GROUP BY c.class_id, sc.session_id, month_ym
        ";
        return $this->db->query($sql, ['campus' => $campusId, 'system' => $systemId])->getResultArray();
    }
}
