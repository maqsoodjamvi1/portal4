<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\Frontend\AuthModel;
use Config\Database;

class Dashboard extends BaseController
{
    protected $session;
    protected $db;
    protected $authModel;

    public function __construct()
    {
        $this->session   = session();
        $this->db        = Database::connect();
        $this->authModel = new AuthModel();
        helper(['url']);
    }

public function index()
{
    $auth = $this->session->get('auth');
    if (!$auth || empty($auth['logged_in'])) {
        return redirect()->route('login');
    }

    $role   = $auth['role'];
    $name   = $auth['name'] ?? '';
    $active = (int) ($this->session->get('active_student_id') ?? 0);

    // ==========================
    // PARENT DASHBOARD
    // ==========================
    if ($role === 'parent') {
        $parentId = (int) $auth['user_id'];
        $children = $this->authModel->getChildren($parentId);

        // If no active student set yet, default to first child
        if (!$active && !empty($children)) {
            $active = (int) $children[0]['student_id'];
            $this->session->set('active_student_id', $active);
        }

        // Quizzes for all children
        $quizzes = $this->getParentQuizzes($parentId);

        // Outstanding fee summary (your existing helper)
        $feeSummary = $this->getParentOutstandingFees($children);

        // Sports events for all children (expects $children array)
        $sportsEvents = $this->getParentSportsEvents($children);

        // Active academic session + its terms (if helper added)
        $activeSessionData = $this->getActiveSessionWithTerms(1);

        // Attendance for the currently active child (current term)
        $activeAttendance = null;
        if ($active > 0) {
            // make sure you have getStudentAttendanceForActiveTerm() helper in this controller
            $activeAttendance = $this->getStudentAttendanceForActiveTerm($active, 1);
        }

        return view('frontend/dashboard/parent', [
            'role'              => 'parent',
            'name'              => $name,
            'children'          => $children,
            'activeStudentId'   => $active,

            'quizzes'           => $quizzes,
            'feeSummary'        => $feeSummary,
            'sportsEvents'      => $sportsEvents,
            'activeSessionData' => $activeSessionData,
            'activeAttendance'  => $activeAttendance,
        ]);
    }

    // ==========================
    // STUDENT DASHBOARD (basic for now)
    // ==========================
    return view('frontend/dashboard/student', [
        'role'            => 'student',
        'name'            => $name,
        'activeStudentId' => $active,
    ]);
}

protected function getStudentAttendanceForActiveTerm(int $studentId, int $systemId = 1): array
{
    $result = [
        'term_id'        => null,
        'term_name'      => null,
        'term_start'     => null,
        'term_end'       => null,
        'total_days'     => 0,
        'present_days'   => 0,
        'percentage'     => 0,
    ];

    if ($studentId <= 0) {
        return $result;
    }

    // 1) Find active session
    $row = $this->db->table('academic_session')
        ->select('session_id, session_name, start_date, end_date')
        ->where('system_id', $systemId)
        ->where('start_date <=', date('Y-m-d'))
        ->where('end_date >=', date('Y-m-d'))
        ->get()
        ->getRowArray();

    if (!$row) {
        return $result;   // No active session
    }

    $sessionId = (int)$row['session_id'];

    // 2) Find active term inside that session
    $term = $this->db->table('terms_session ts')
        ->select('ts.term_id, ts.start_date, ts.end_date, t.name AS term_name')
        ->join('terms t', 't.term_id = ts.term_id')
        ->where('ts.session_id', $sessionId)
        ->where('ts.start_date <=', date('Y-m-d'))
        ->where('ts.end_date >=', date('Y-m-d'))
        ->get()
        ->getRowArray();

    if (!$term) {
        return $result;  // No running term
    }

    $termId    = (int)$term['term_id'];
    $startDate = $term['start_date'];
    $endDate   = $term['end_date'];

    // STORE DEBUG INFO
    $result['term_id']    = $termId;
    $result['term_name']  = $term['term_name'];
    $result['term_start'] = $startDate;
    $result['term_end']   = $endDate;

    // 3) Attendance query
    $attRows = $this->db->table('attendance')
        ->select('status, COUNT(*) AS total')
        ->where('student_id', $studentId)
        ->where('date >=', $startDate)
        ->where('date <=', $endDate)
        ->groupBy('status')
        ->get()
        ->getResultArray();

    $total = 0;
    $present = 0;

    foreach ($attRows as $r) {
        $cnt = (int)$r['total'];
        $total += $cnt;
        if (strtolower($r['status']) === 'present') {
            $present += $cnt;
        }
    }

    $result['total_days']   = $total;
    $result['present_days'] = $present;
    $result['percentage']   = $total > 0 ? round(($present / $total) * 100, 2) : 0;

    return $result;
}


    /**
     * Outstanding fees for all children of this parent.
     *
     * Uses:
     *  SELECT SUM(amount - discount)
     *  FROM fee_chalan
     *  WHERE student_id = ? AND status = 'unpaid'
     */
  protected function getParentOutstandingFees(array $children): array
{
    $result = [
        'total_outstanding' => 0.0,
        'by_student'        => [],
    ];

    if (empty($children)) {
        return $result;
    }

    $studentIds = [];
    foreach ($children as $c) {
        if (!empty($c['student_id'])) {
            $studentIds[] = (int) $c['student_id'];
        }
    }

    if (empty($studentIds)) {
        return $result;
    }

    $rows = $this->db->table('fee_chalan')
        ->select('student_id, SUM(amount - discount) AS outstanding')
        ->whereIn('student_id', $studentIds)
        ->where('status', 'unpaid')
        ->groupBy('student_id')
        ->get()
        ->getResultArray();

    $outstandingMap = [];
    $total          = 0.0;

    foreach ($rows as $r) {
        $sid   = (int) $r['student_id'];
        $value = (float) $r['outstanding'];
        $outstandingMap[$sid] = $value;
        $total += $value;
    }

    $byStudent = [];
    foreach ($children as $c) {
        $sid   = (int) $c['student_id'];
        $due   = $outstandingMap[$sid] ?? 0.0;

        $byStudent[$sid] = [
            'student_id'    => $sid,
            'name'          => $c['name'] ?? '',
            'reg_no'        => $c['reg_no'] ?? '',
            'profile_photo' => $c['profile_photo'] ?? '', // expect like "usama.jpeg"
            'outstanding'   => $due,
        ];
    }

    $result['total_outstanding'] = $total;
    $result['by_student']        = $byStudent;

    return $result;
}
    /**
     * Fetch quizzes for all children of a parent.
     * (Same as earlier, with student_photo_url and top_scorers[photo_url])
     */
   protected function getParentQuizzes(int $parentId): array
{
    if ($parentId <= 0) {
        return [];
    }

    $db        = $this->db;
    $sessionId = (int) ($this->session->get('member_sessionid') ?? 0);

    $sql = "
        SELECT
            s.student_id,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
            s.profile_photo AS student_photo_file,

            sc.cls_sec_id,
            c.class_name,
            se.section_name,

            q.quiz_id,
            q.max_attempts,
            q.title,
            subj.subject_name,

            GROUP_CONCAT(DISTINCT qt.topic_name ORDER BY qt.topic_name SEPARATOR ', ') AS topics,
            q.start_at,
            q.end_at,
            q.time_limit_sec,
            q.questions_count,
            q.count_mcq_single,
            q.count_mcq_multi,
            q.count_tf,
            q.count_short,
            q.count_fill,
            q.count_match,

            (
  SELECT COUNT(*)
  FROM quiz_attempts qa
  WHERE qa.quiz_id = q.quiz_id
    AND qa.student_id = s.student_id
    AND qa.status = 'submitted'
) AS attempts_count,

            (
  SELECT MAX(qa2.score_obtained)
  FROM quiz_attempts qa2
  WHERE qa2.quiz_id = q.quiz_id
    AND qa2.student_id = s.student_id
    AND qa2.status = 'submitted'
) AS best_score,

            (
  SELECT qa3.attempt_id
  FROM quiz_attempts qa3
  WHERE qa3.quiz_id = q.quiz_id
    AND qa3.student_id = s.student_id
    AND qa3.status = 'submitted'
  ORDER BY qa3.attempt_id DESC
  LIMIT 1
) AS last_attempt_id,

          (
  SELECT GROUP_CONCAT(qa4.attempt_id ORDER BY qa4.attempt_id ASC SEPARATOR ',')
  FROM quiz_attempts qa4
  WHERE qa4.quiz_id = q.quiz_id
    AND qa4.student_id = s.student_id
    AND qa4.status = 'submitted'
) AS attempt_ids_csv

        FROM students s
        JOIN student_class sc  ON sc.student_id = s.student_id AND sc.status = 1
        JOIN class_section cs  ON cs.cls_sec_id = sc.cls_sec_id
        JOIN classes c         ON c.class_id    = cs.class_id
        JOIN sections se       ON se.section_id = cs.section_id

        JOIN section_subjects ss ON ss.cls_sec_id = cs.cls_sec_id AND ss.status = 1
        JOIN quizzes q           ON q.sec_sub_id  = ss.sec_sub_id
        JOIN allsubject subj     ON subj.sid      = ss.subject_id

        LEFT JOIN quiz_topics qtp ON qtp.quiz_id = q.quiz_id
        LEFT JOIN qb_topics qt    ON qt.id       = qtp.topic_id

       WHERE s.parent_id = :parentId:
AND EXISTS (
    SELECT 1
    FROM quiz_attempts qa5
    WHERE qa5.quiz_id = q.quiz_id
      AND qa5.student_id = s.student_id
      AND qa5.status = 'submitted'
)
    ";

    $params = ['parentId' => $parentId];
    if ($sessionId > 0) {
        $sql .= " AND sc.session_id = :sessionId: ";
        $params['sessionId'] = $sessionId;
    }

    $sql .= "
        GROUP BY
            s.student_id,
            s.profile_photo,
            sc.cls_sec_id,
            c.class_name,
            se.section_name,
            q.quiz_id,
            q.title,
            subj.subject_name,
            q.end_at,
            q.time_limit_sec,
            q.questions_count,
            q.count_mcq_single,
            q.count_mcq_multi,
            q.count_tf,
            q.count_short,
            q.count_fill,
            q.count_match

        ORDER BY s.student_id, q.quiz_id DESC
    ";

    $rows = $db->query($sql, $params)->getResultArray();

    $topCache = [];
    $now      = new \DateTime();

    foreach ($rows as &$row) {
        $quizId = (int)($row['quiz_id'] ?? 0);

        // ---- Build attempt_ids as PHP array for the view ----
        $attemptIds = [];
        if (!empty($row['attempt_ids_csv'])) {
            $attemptIds = array_map('intval', explode(',', $row['attempt_ids_csv']));
        }
        $row['attempt_ids'] = $attemptIds;
        unset($row['attempt_ids_csv']);

        // ---- Top scorers cache (unchanged) ----
        if ($quizId > 0 && ! isset($topCache[$quizId])) {
           $topCache[$quizId] = $db->query("
    SELECT
        s.student_id,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        s.profile_photo AS student_photo_file,
        qa.score_obtained
    FROM quiz_attempts qa
    JOIN students s ON s.student_id = qa.student_id
    WHERE qa.quiz_id = ?
      AND qa.status = 'submitted'
      AND qa.score_obtained IS NOT NULL
    ORDER BY qa.score_obtained DESC, qa.attempt_id ASC
    LIMIT 3
", [$quizId])->getResultArray();
        }

        $rawTop = $topCache[$quizId] ?? [];
        if (count($rawTop) >= 3) {
            $topScorers = [];
            foreach ($rawTop as $ts) {
                $file = $ts['student_photo_file'] ?? '';
                $file = ltrim((string)$file, '/');
                $photoUrl = $file
                    ? base_url('uploads/' . $file)
                    : base_url('resource/img/avatar-student.png');

                $topScorers[] = [
                    'student_id'     => (int)$ts['student_id'],
                    'student_name'   => $ts['student_name'],
                    'score_obtained' => (float)$ts['score_obtained'],
                    'photo_url'      => $photoUrl,
                ];
            }
            $row['top_scorers'] = $topScorers;
        } else {
            $row['top_scorers'] = [];
        }

        // Student photo url for quiz card
        $studentFile = $row['student_photo_file'] ?? '';
        $studentFile = ltrim((string)$studentFile, '/');
        $row['student_photo_url'] = $studentFile
            ? base_url('uploads/' . $studentFile)
            : base_url('resource/img/avatar-student.png');

        // Duration / status / remaining time
$secs = (int) ($row['time_limit_sec'] ?? 0);
$row['duration_minutes'] = $secs > 0 ? (int) ceil($secs / 60) : 0;

$attempts      = (int) ($row['attempts_count'] ?? 0);
$statusStudent = $attempts > 0 ? 'attempted' : 'not_started';

$remainingStr = '-';
$quizStatus   = 'live';

$startAt = $row['start_at'] ?? null;
$endAt   = $row['end_at']   ?? null;

$hasStart = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
$hasEnd   = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';

// ✅ Forever rule: if start_at == end_at then ignore time constraints
$isForever = ($hasStart && $hasEnd && $startAt === $endAt);

if ($isForever) {
    $quizStatus   = 'live';
    $remainingStr = 'Always Available';
} else {

    // (optional) If quiz has not started yet -> treat as NOT available
    if ($hasStart) {
        try {
            $start = new \DateTime($startAt);
            if ($start->getTimestamp() > $now->getTimestamp()) {
                $quizStatus   = 'closed';      // or 'upcoming' if you want
                $remainingStr = 'Not Started';
            }
        } catch (\Exception $e) {}
    }

    // if started, check end time
    if ($quizStatus !== 'closed' && $hasEnd) {
        try {
            $end     = new \DateTime($endAt);
            $diffSec = $end->getTimestamp() - $now->getTimestamp();

            if ($diffSec <= 0) {
                $remainingStr = 'Ended';
                $quizStatus   = 'closed';
            } else {
                $days  = intdiv($diffSec, 86400);
                $rem   = $diffSec % 86400;
                $hours = intdiv($rem, 3600);
                $rem   = $rem % 3600;
                $mins  = intdiv($rem, 60);

                $parts = [];
                if ($days > 0)  $parts[] = $days . 'd';
                if ($hours > 0) $parts[] = $hours . 'h';
                if ($mins > 0 || empty($parts)) $parts[] = $mins . 'm';

                $remainingStr = implode(' ', $parts);
                $quizStatus   = 'live';
            }
        } catch (\Exception $e) {
            $remainingStr = '-';
        }
    }
}

$row['remaining_time']     = $remainingStr;
$row['quiz_status']        = $quizStatus;
$row['student_quiz_state'] = $statusStudent;

    }
    unset($row);

    return $rows;
}

    /**
     * Sports events where ANY child of the parent participated,
     * PLUS all participants of those events.
     *
     * Returns array keyed by event_id:
     *  [
     *    event_id => [
     *      'event_id'    => int,
     *      'event_name'  => string,
     *      'event_date'  => string,
     *      'event_type'  => string,
     *      'participants'=> [
     *          [
     *            'student_id'   => int,
     *            'student_name' => string,
     *            'class_label'  => string,
     *            'photo_url'    => string,
     *          ], ...
     *      ]
     *    ],
     *    ...
     *  ]
     */


    protected function getParentSportsEvents(array $children): array
    {
        if (empty($children)) {
            return [];
        }

        $studentIds = [];
        foreach ($children as $c) {
            if (!empty($c['student_id'])) {
                $studentIds[] = (int) $c['student_id'];
            }
        }
        if (empty($studentIds)) {
            return [];
        }

        // 1) Find all events where any of parent's children participated
        $eventRows = $this->db->table('sports_event_entries se')
            ->select('e.event_id, e.event_name, e.event_date, e.event_type')
            ->join('sports_events e', 'e.event_id = se.event_id')
            ->where('e.status', 1)
            ->whereIn('se.student_id', $studentIds)
            ->distinct()
            ->orderBy('e.event_date', 'DESC')
            ->orderBy('e.event_name', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($eventRows)) {
            return [];
        }

        $eventMap = [];
        $eventIds = [];
        foreach ($eventRows as $er) {
            $eid = (int) $er['event_id'];
            $eventIds[] = $eid;
            $eventMap[$eid] = [
                'event_id'    => $eid,
                'event_name'  => $er['event_name'] ?? '',
                'event_date'  => $er['event_date'] ?? '',
                'event_type'  => $er['event_type'] ?? '',
                'participants'=> [],
            ];
        }

        // 2) For those events, fetch ALL participants (not only the children)
        $rows = $this->db->table('sports_event_entries se')
            ->select("
                e.event_id,
                e.event_name,
                e.event_date,
                e.event_type,
                s.student_id,
                s.first_name,
                s.last_name,
                s.profile_photo,
                c.class_name,
                sec.section_name
            ")
            ->join('sports_events e', 'e.event_id = se.event_id')
            ->join('students s', 's.student_id = se.student_id')
            ->join('student_class sc', 'sc.student_id = s.student_id AND sc.status = 1', 'left')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
            ->whereIn('e.event_id', $eventIds)
            ->orderBy('e.event_date', 'DESC')
            ->orderBy('e.event_id', 'DESC')
            ->orderBy('s.first_name', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $r) {
            $eid = (int) $r['event_id'];
            if (!isset($eventMap[$eid])) {
                continue;
            }

            $fullName = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            if ($fullName === '') {
                $fullName = 'ID ' . $r['student_id'];
            }

            $classLabel = trim(($r['class_name'] ?? '') . ' ' . ($r['section_name'] ?? ''));
            $photoFile  = $r['profile_photo'] ?? '';
            $photoFile  = ltrim((string)$photoFile, '/');
            $photoUrl   = $photoFile !== ''
                ? base_url('uploads/' . $photoFile)            // uploads/usama.jpeg
                : base_url('resource/img/avatar-student.png');

            $eventMap[$eid]['participants'][] = [
                'student_id'   => (int) $r['student_id'],
                'student_name' => $fullName,
                'class_label'  => $classLabel,
                'photo_url'    => $photoUrl,
            ];
        }

        return $eventMap;
    }

    public function switchStudent(int $studentId)
    {
        $auth = $this->session->get('auth');
        if (!$auth || $auth['role'] !== 'parent') {
            return redirect()->route('dashboard');
        }

        $row = $this->db->table('a_students')
            ->select('student_id')
            ->where('student_id', $studentId)
            ->where('parent_id', (int) $auth['user_id'])
            ->get()
            ->getRowArray();

        if ($row) {
            $this->session->set('active_student_id', (int) $studentId);
        }
        return redirect()->route('dashboard');
    }

    protected function getActiveSessionWithTerms(int $systemId = 1): ?array
{
    $db    = $this->db;
    $today = date('Y-m-d');

    // 1) Find active academic session for today
    $session = $db->table('academic_session')
        ->select('session_id, session_name, start_date, end_date')
        ->where('system_id', $systemId)
        ->where('start_date <=', $today)
        ->where('end_date >=', $today)
        ->orderBy('start_date', 'DESC')
        ->get()
        ->getRowArray();

    if (!$session) {
        return null; // no active session found
    }

    $sessionId = (int) $session['session_id'];

    // 2) Fetch all terms of this session from terms_session + terms
    // Adjust column names if your DB uses different names (term_id / id / name etc.)
    $terms = $db->table('terms_session ts')
        ->select('
            ts.term_session_id,
            ts.term_id,
            ts.start_date,
            ts.end_date,
            t.name AS term_name
        ')
        ->join('terms t', 't.term_id = ts.term_id', 'left') // change to t.id if needed
        ->where('ts.session_id', $sessionId)
        ->orderBy('ts.start_date', 'ASC')
        ->get()
        ->getResultArray();

    return [
        'session' => $session,
        'terms'   => $terms,
    ];
}

}
