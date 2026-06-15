<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Libraries\AdaptiveQuizService;
use App\Libraries\ExamQuizService;
use Config\Database;

/**
 * Lists all published quizzes for the active student's class section (parent or student portal).
 */
class StudentQuizCatalog extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = Database::connect();
        $this->session = session();
        helper(['url', 'parent_portal']);
    }

    public function index()
    {
        $auth = $this->session->get('auth');
        if (! $auth || empty($auth['logged_in'])) {
            return redirect()->route('login');
        }

        $role     = $auth['role'] ?? '';
        $parentId = (int) ($auth['user_id'] ?? 0);

        $studentId = 0;
        if ($role === 'parent') {
            $studentId = (int) ($this->session->get('active_student_id') ?? 0);
            if ($studentId <= 0 && \function_exists('parent_portal_get_children')) {
                $kids = \parent_portal_get_children($parentId);
                if (! empty($kids)) {
                    $studentId = (int) $kids[0]['student_id'];
                    $this->session->set('active_student_id', $studentId);
                }
            }
            if ($studentId <= 0) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a child from the dashboard first.');
            }
            $owns = $this->db->table('students')
                ->where('student_id', $studentId)
                ->where('parent_id', $parentId)
                ->countAllResults() > 0;
            if (! $owns) {
                return redirect()->route('dashboard')->with('error', 'You do not have access to this student.');
            }
        } elseif ($role === 'student') {
            $studentId = (int) ($this->session->get('student_id') ?? 0);
            if ($studentId <= 0) {
                return redirect()->route('login')->with('error', 'Student information not found.');
            }
        } else {
            return redirect()->route('login');
        }

        $clsSecId = $this->resolveClsSecId($studentId);
        $children = ($role === 'parent') ? \parent_portal_get_children($parentId) : [];

        if (! $clsSecId) {
            return view('frontend/quizzes/catalog', [
                'title'                => 'Quizzes',
                'role'                 => $role,
                'children'             => $children,
                'active_student_id'    => $studentId,
                'quizzes'              => [],
                'termGroups'           => [],
                'currentTermSessionId' => 0,
                'sessionName'          => '',
                'err'                  => 'Class / section is not assigned for this student.',
            ]);
        }

        $extraCols = '';
        if ($this->db->fieldExists('is_adaptive', 'quizzes')) {
            $extraCols .= ', q.is_adaptive';
        }
        if ($this->db->fieldExists('kids_mode', 'quizzes')) {
            $extraCols .= ', q.kids_mode';
        }

        $portalVisibilitySql = (new ExamQuizService())->portalVisibilitySql('q');

        $sql = "
            SELECT
                q.quiz_id,
                q.title,
                q.start_at,
                q.end_at,
                q.max_attempts,
                q.sec_sub_id,
                q.term_session_id,
                q.questions_count,
                q.time_limit_sec
                {$extraCols},
                s.subject_name,
                s.subject_short_name,
                CONCAT(t.short_name, ' - ', acs.session_name) AS term_session_label,
                COUNT(qa.attempt_id) AS attempts_used
            FROM quizzes q
            LEFT JOIN quiz_attempts qa
                   ON qa.quiz_id = q.quiz_id
                  AND qa.student_id = ?
                  AND qa.status = 'submitted'
            LEFT JOIN section_subjects ss ON ss.sec_sub_id = q.sec_sub_id
            LEFT JOIN allsubject s ON s.sid = ss.subject_id
            LEFT JOIN terms_session ts ON ts.term_session_id = q.term_session_id
            LEFT JOIN terms t ON t.term_id = ts.term_id
            LEFT JOIN academic_session acs ON acs.session_id = ts.session_id
            WHERE q.cls_sec_id = ?
              AND q.is_published = 1
              {$portalVisibilitySql}
            GROUP BY
                q.quiz_id,
                q.title,
                q.start_at,
                q.end_at,
                q.max_attempts,
                q.sec_sub_id,
                q.term_session_id,
                q.questions_count,
                q.time_limit_sec
                {$extraCols},
                s.subject_name,
                s.subject_short_name,
                ts.term_session_id,
                t.short_name,
                acs.session_name
            ORDER BY COALESCE(q.start_at, '1970-01-01') DESC, q.quiz_id DESC
        ";

        $rows = $this->db->query($sql, [$studentId, $clsSecId])->getResult();
        $adaptive = new AdaptiveQuizService($this->db);

        foreach ($rows as $row) {
            $this->enrichQuizRow($row, $studentId, $adaptive);
        }

        $sessionInfo          = $this->getCurrentAcademicSessionForStudent($studentId);
        $systemId             = $this->getSystemIdForStudent($studentId);
        $currentSessionId     = (int) ($sessionInfo['session_id'] ?? 0);
        $currentTermSessionId = $this->resolveCurrentTermSessionId($currentSessionId, $systemId);
        $attemptsByQuiz       = $this->loadStudentQuizAttempts($studentId, $clsSecId);
        foreach ($rows as $row) {
            $row->student_attempts = $attemptsByQuiz[(int) $row->quiz_id] ?? [];
        }

        $termGroups           = $this->buildTermQuizGroups($rows, $currentSessionId, $systemId, $currentTermSessionId);
        $hasAnyResult         = $attemptsByQuiz !== [];

        return view('frontend/quizzes/catalog', [
            'title'                   => 'Quizzes',
            'role'                    => $role,
            'children'                => $children,
            'active_student_id'       => $studentId,
            'quizzes'                 => $rows,
            'termGroups'              => $termGroups,
            'currentTermSessionId'    => $currentTermSessionId,
            'sessionName'             => (string) ($sessionInfo['session_name'] ?? ''),
            'hasAnyResult'            => $hasAnyResult,
            'err'                     => '',
        ]);
    }

    /**
     * Quiz results grouped by term → subject → quiz → attempts.
     */
    public function results()
    {
        $ctx = $this->resolvePortalContext();
        if ($ctx === null) {
            $auth = $this->session->get('auth');
            if (! empty($auth['logged_in']) && ($auth['role'] ?? '') === 'parent') {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a child from the dashboard first.');
            }

            return redirect()->route('login');
        }

        $studentId = (int) $ctx['student_id'];
        $role      = (string) $ctx['role'];
        $children  = $ctx['children'];
        $clsSecId  = (int) $ctx['cls_sec_id'];
        $sidQs     = (string) $ctx['sid_qs'];

        if ($clsSecId <= 0) {
            return view('frontend/quizzes/catalog_results', [
                'title'                => 'Quiz Results',
                'role'                 => $role,
                'children'             => $children,
                'active_student_id'    => $studentId,
                'sidQs'                => $sidQs,
                'resultTermGroups'     => [],
                'currentTermSessionId' => 0,
                'sessionName'          => '',
                'err'                  => 'Class / section is not assigned for this student.',
            ]);
        }

        $sessionInfo          = $this->getCurrentAcademicSessionForStudent($studentId);
        $systemId             = $this->getSystemIdForStudent($studentId);
        $currentSessionId     = (int) ($sessionInfo['session_id'] ?? 0);
        $currentTermSessionId = $this->resolveCurrentTermSessionId($currentSessionId, $systemId);
        $attemptsByQuiz       = $this->loadStudentQuizAttempts($studentId, $clsSecId);
        $resultTermGroups     = $this->buildResultTermGroups(
            $attemptsByQuiz,
            $currentSessionId,
            $systemId,
            $currentTermSessionId
        );

        return view('frontend/quizzes/catalog_results', [
            'title'                => 'Quiz Results',
            'role'                 => $role,
            'children'             => $children,
            'active_student_id'    => $studentId,
            'sidQs'                => $sidQs,
            'resultTermGroups'     => $resultTermGroups,
            'currentTermSessionId' => $currentTermSessionId,
            'sessionName'          => (string) ($sessionInfo['session_name'] ?? ''),
            'err'                  => '',
        ]);
    }

    /**
     * @return array{student_id: int, role: string, parent_id: int, children: array, cls_sec_id: int, sid_qs: string}|null
     */
    private function resolvePortalContext(): ?array
    {
        $auth = $this->session->get('auth');
        if (! $auth || empty($auth['logged_in'])) {
            return null;
        }

        $role     = $auth['role'] ?? '';
        $parentId = (int) ($auth['user_id'] ?? 0);
        $studentId = 0;

        if ($role === 'parent') {
            $studentId = (int) ($this->session->get('active_student_id') ?? 0);
            if ($studentId <= 0 && \function_exists('parent_portal_get_children')) {
                $kids = \parent_portal_get_children($parentId);
                if (! empty($kids)) {
                    $studentId = (int) $kids[0]['student_id'];
                    $this->session->set('active_student_id', $studentId);
                }
            }
            if ($studentId <= 0) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a child from the dashboard first.');
            }
            $owns = $this->db->table('students')
                ->where('student_id', $studentId)
                ->where('parent_id', $parentId)
                ->countAllResults() > 0;
            if (! $owns) {
                return redirect()->route('dashboard')->with('error', 'You do not have access to this student.');
            }
        } elseif ($role === 'student') {
            $studentId = (int) ($this->session->get('student_id') ?? 0);
            if ($studentId <= 0) {
                return null;
            }
        } else {
            return null;
        }

        $children = ($role === 'parent') ? \parent_portal_get_children($parentId) : [];
        $sidQs    = ($role === 'parent' && $studentId > 0) ? ('?sid=' . $studentId) : '';

        return [
            'student_id' => $studentId,
            'role'       => $role,
            'parent_id'  => $parentId,
            'children'   => $children,
            'cls_sec_id' => $this->resolveClsSecId($studentId),
            'sid_qs'     => $sidQs,
        ];
    }

    /**
     * @param list<object> $rows
     * @return list<array{term_session_id: int, term_name: string, term_short: string, start_date: string, end_date: string, is_current: bool, quizzes: list<object>}>
     */
    private function buildTermQuizGroups(
        array $rows,
        int $sessionId,
        int $systemId,
        int $currentTermSessionId
    ): array {
        $terms = $this->loadSessionTerms($sessionId, $systemId);
        $byTerm = [];

        foreach ($terms as $t) {
            $tsid = (int) ($t['term_session_id'] ?? 0);
            $byTerm[$tsid] = [
                'term_session_id' => $tsid,
                'term_name'       => (string) ($t['term_name'] ?? 'Term'),
                'term_short'      => (string) ($t['term_short'] ?? ''),
                'start_date'      => (string) ($t['start_date'] ?? ''),
                'end_date'        => (string) ($t['end_date'] ?? ''),
                'is_current'      => $tsid > 0 && $tsid === $currentTermSessionId,
                'quizzes'         => [],
            ];
        }

        $other = [];

        foreach ($rows as $row) {
            $tsid = (int) ($row->term_session_id ?? 0);
            if ($tsid > 0 && isset($byTerm[$tsid])) {
                $byTerm[$tsid]['quizzes'][] = $row;
            } else {
                $other[] = $row;
            }
        }

        $groups = array_values($byTerm);

        if ($other !== []) {
            $groups[] = [
                'term_session_id' => 0,
                'term_name'       => 'Other',
                'term_short'      => '',
                'start_date'      => '',
                'end_date'        => '',
                'is_current'      => false,
                'quizzes'         => $other,
            ];
        }

        return $groups;
    }

    /**
     * @return list<array{term_session_id: int, term_id: int, term_name: string, term_short: string, start_date: string, end_date: string}>
     */
    private function loadSessionTerms(int $sessionId, int $systemId): array
    {
        if ($sessionId <= 0 || $systemId <= 0) {
            return [];
        }

        return $this->db->table('terms_session ts')
            ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date, t.name AS term_name, t.short_name AS term_short')
            ->join('terms t', 't.term_id = ts.term_id', 'inner')
            ->where('ts.session_id', $sessionId)
            ->where('ts.system_id', $systemId)
            ->orderBy('ts.start_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function resolveCurrentTermSessionId(int $sessionId, int $systemId): int
    {
        if ($sessionId <= 0 || $systemId <= 0) {
            return 0;
        }

        $today = date('Y-m-d');

        $current = $this->db->table('terms_session')
            ->select('term_session_id')
            ->where('session_id', $sessionId)
            ->where('system_id', $systemId)
            ->where('DATE(start_date) <=', $today)
            ->where('DATE(end_date) >=', $today)
            ->orderBy('start_date', 'ASC')
            ->get()
            ->getRowArray();

        if ($current) {
            return (int) ($current['term_session_id'] ?? 0);
        }

        $past = $this->db->table('terms_session')
            ->select('term_session_id')
            ->where('session_id', $sessionId)
            ->where('system_id', $systemId)
            ->where('DATE(start_date) <=', $today)
            ->orderBy('start_date', 'DESC')
            ->get()
            ->getRowArray();

        if ($past) {
            return (int) ($past['term_session_id'] ?? 0);
        }

        $upcoming = $this->db->table('terms_session')
            ->select('term_session_id')
            ->where('session_id', $sessionId)
            ->where('system_id', $systemId)
            ->where('DATE(start_date) >', $today)
            ->orderBy('start_date', 'ASC')
            ->get()
            ->getRowArray();

        return (int) ($upcoming['term_session_id'] ?? 0);
    }

    /**
     * @return array{session_id: int, session_name: string, start_date: string, end_date: string}|null
     */
    private function getCurrentAcademicSessionForStudent(int $studentId): ?array
    {
        $systemId = $this->getSystemIdForStudent($studentId);
        if ($systemId <= 0) {
            return null;
        }

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

    private function getSystemIdForStudent(int $studentId): int
    {
        $student = $this->db->table('students')
            ->select('campus_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRowArray();

        if (! $student || empty($student['campus_id'])) {
            return 0;
        }

        $campus = $this->db->table('campus')
            ->select('system_id')
            ->where('campus_id', (int) $student['campus_id'])
            ->get()
            ->getRowArray();

        return (int) ($campus['system_id'] ?? 0);
    }

    private function enrichQuizRow(object $row, int $studentId, AdaptiveQuizService $adaptive): void
    {
        $quizId       = (int) $row->quiz_id;
        $attemptsUsed = (int) ($row->attempts_used ?? 0);
        $row->attempts_used      = $attemptsUsed;
        $row->remaining_attempts = max(0, ((int) $row->max_attempts) - $attemptsUsed);

        $row->questions_db = (int) $this->db->table('quiz_questions')
            ->where('quiz_id', $quizId)
            ->countAllResults();

        $row->questions_count = (int) ($row->questions_count ?? 0);
        $row->is_adaptive     = (int) ($row->is_adaptive ?? 0) === 1;
        $row->levels          = [];
        $row->level_count     = 0;

        if ($row->is_adaptive && $this->db->tableExists('quiz_levels')) {
            $levels = $adaptive->getLevels($quizId);
            $row->level_count = count($levels);

            foreach ($levels as $lvl) {
                $levelId   = (int) ($lvl->level_id ?? 0);
                $qPerLevel = method_exists($adaptive, 'resolvePerLevelQuestionCap')
                    ? $adaptive->resolvePerLevelQuestionCap($quizId, $levelId, $row)
                    : $this->resolvePerLevelQuestionCap($quizId, $levelId, $row, $lvl, count($levels));
                $passPct   = $lvl->passing_percentage ?? $lvl->min_pass_percentage ?? 60;
                $row->levels[] = [
                    'level_no'   => (int) ($lvl->level_no ?? 0),
                    'level_name' => ! empty($lvl->level_name)
                        ? (string) $lvl->level_name
                        : ('Level ' . (int) ($lvl->level_no ?? 0)),
                    'pass_pct'   => (float) $passPct,
                    'questions'  => $qPerLevel,
                ];
            }
        }

        $row->attempt_questions = $this->resolveAttemptQuestionCount($row);
        $row->duration_label    = $this->formatDurationLabel($row);
        $row->duration_detail   = $this->formatDurationDetail($row);

        $attempt = $this->db->table('quiz_attempts')
            ->where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'DESC')
            ->get(1)->getRow();

        $row->last_score    = null;
        $row->correct_count = 0;
        $row->wrong_count   = 0;

        if ($attempt) {
            $row->last_score = (float) $attempt->score_obtained;

            $res = $this->db->table('quiz_attempt_answers')
                ->select('
                    SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) AS correct_count,
                    SUM(CASE WHEN is_correct = 0 THEN 1 ELSE 0 END) AS wrong_count
                ')
                ->where('attempt_id', $attempt->attempt_id)
                ->get()->getRow();

            if ($res) {
                $row->correct_count = (int) ($res->correct_count ?? 0);
                $row->wrong_count   = (int) ($res->wrong_count ?? 0);
            }
        }

        $row->catalog_status = $this->buildCatalogStatus($row);
    }

    /**
     * Matches AdaptiveQuizService::assignQuestionsForLevel cap (fallback when library not updated).
     */
    private function resolvePerLevelQuestionCap(
        int $quizId,
        int $levelId,
        object $quiz,
        object $levelRow,
        int $levelCount
    ): int {
        $bankCount = 0;
        if ($this->db->fieldExists('level_id', 'quiz_questions')) {
            $bankCount = (int) $this->db->table('quiz_questions')
                ->where(['quiz_id' => $quizId, 'level_id' => $levelId])
                ->countAllResults();
        }

        $perLevelLimit = 0;
        if (isset($levelRow->questions_count) && (int) $levelRow->questions_count > 0) {
            $perLevelLimit = (int) $levelRow->questions_count;
        } elseif (! empty($quiz->is_adaptive)) {
            $quizTotal = (int) ($quiz->questions_count ?? 0);
            if ($quizTotal > 0 && $levelCount > 0) {
                $perLevelLimit = (int) ceil($quizTotal / $levelCount);
            }
        }

        if ($perLevelLimit <= 0) {
            return $bankCount;
        }

        return $bankCount > 0 ? min($perLevelLimit, $bankCount) : $perLevelLimit;
    }

    /**
     * Questions the student will face on one graded attempt (or per level if adaptive).
     */
    private function resolveAttemptQuestionCount(object $row): int
    {
        $quizId    = (int) $row->quiz_id;
        $configured = (int) ($row->questions_count ?? 0);
        $inBank     = (int) ($row->questions_db ?? 0);

        if (! empty($row->is_adaptive) && ! empty($row->levels)) {
            $perLevel = array_column($row->levels, 'questions');
            $perLevel = array_map('intval', array_filter($perLevel, static fn ($n) => (int) $n > 0));
            if ($perLevel !== []) {
                $unique = array_unique($perLevel);
                if (count($unique) === 1) {
                    return (int) $unique[0];
                }

                return (int) max($perLevel);
            }
            if ($configured > 0 && $row->level_count > 0) {
                return (int) max(1, (int) ceil($configured / $row->level_count));
            }
        }

        if ($configured > 0) {
            return min($configured, $inBank > 0 ? $inBank : $configured);
        }

        return $inBank;
    }

    private function formatDurationLabel(object $row): string
    {
        $sec = (int) ($row->time_limit_sec ?? 0);
        if ($sec <= 0) {
            return 'No time limit';
        }

        $mins = (int) floor($sec / 60);
        $rem  = $sec % 60;
        if ($mins > 0 && $rem > 0) {
            return $mins . ' min ' . $rem . ' sec';
        }
        if ($mins > 0) {
            return $mins . ' min';
        }

        return $sec . ' sec';
    }

    private function formatDurationDetail(object $row): string
    {
        $sec = (int) ($row->time_limit_sec ?? 0);
        if ($sec <= 0) {
            return '';
        }

        if (! empty($row->is_adaptive) && (int) ($row->level_count ?? 0) > 1) {
            $levels = (int) $row->level_count;
            $total  = $sec * $levels;
            $mins   = (int) ceil($total / 60);

            return '~' . $mins . ' min total (' . $levels . ' levels × ' . $this->formatDurationLabel($row) . ')';
        }

        return 'Per attempt';
    }

    private function resolveClsSecId(int $studentId): int
    {
        $row = $this->db->table('student_class')
            ->select('cls_sec_id')
            ->where('student_id', $studentId)
            ->where('status', 1)
            ->orderBy('sc_id', 'DESC')
            ->get()
            ->getRow();

        return $row ? (int) $row->cls_sec_id : 0;
    }

    /**
     * @param object $q quiz row with start_at, end_at, max_attempts, attempts_used
     */
    private function buildCatalogStatus(object $q): string
    {
        $now     = date('Y-m-d H:i:s');
        $startAt = $q->start_at ?? null;
        $endAt   = $q->end_at ?? null;

        $hasStart  = ! empty($startAt) && $startAt !== '0000-00-00 00:00:00';
        $hasEnd    = ! empty($endAt) && $endAt !== '0000-00-00 00:00:00';
        $isForever = $hasStart && $hasEnd && ($startAt === $endAt);

        if (! $isForever) {
            if ($hasStart && $startAt > $now) {
                return 'upcoming';
            }
            if ($hasEnd && $endAt < $now && empty($q->is_adaptive)) {
                return 'ended';
            }
        }

        $maxA = (int) ($q->max_attempts ?? 0);
        $used = (int) ($q->attempts_used ?? 0);
        if ($maxA > 0 && $used >= $maxA && empty($q->is_adaptive)) {
            return 'maxed';
        }

        return 'open';
    }

    /**
     * @return array<int, list<array<string, mixed>>>
     */
    private function loadStudentQuizAttempts(int $studentId, int $clsSecId): array
    {
        if ($studentId <= 0 || $clsSecId <= 0) {
            return [];
        }

        $rows = $this->db->query("
            SELECT
                qa.attempt_id,
                qa.quiz_id,
                qa.attempt_no,
                qa.score_obtained,
                qa.status,
                qa.submitted_at,
                qa.started_at,
                q.title AS quiz_title,
                q.term_session_id,
                q.sec_sub_id,
                COALESCE(s.subject_short_name, s.subject_name, 'General') AS subject_label,
                ss.subject_id
            FROM quiz_attempts qa
            INNER JOIN quizzes q ON q.quiz_id = qa.quiz_id
            LEFT JOIN section_subjects ss ON ss.sec_sub_id = q.sec_sub_id
            LEFT JOIN allsubject s ON s.sid = ss.subject_id
            WHERE qa.student_id = ?
              AND q.cls_sec_id = ?
              AND qa.status = 'submitted'
            ORDER BY qa.submitted_at DESC, qa.attempt_no DESC
        ", [$studentId, $clsSecId])->getResultArray();

        if ($rows === []) {
            return [];
        }

        $attemptIds = array_map(static fn ($r) => (int) $r['attempt_id'], $rows);
        $statsMap   = $this->loadAttemptAnswerStats($attemptIds);
        $marksMap   = $this->loadAttemptTotalMarks($attemptIds);

        $byQuiz = [];
        foreach ($rows as $r) {
            $attemptId = (int) $r['attempt_id'];
            $quizId    = (int) $r['quiz_id'];
            $stats     = $statsMap[$attemptId] ?? ['total_q' => 0, 'correct' => 0, 'wrong' => 0];
            $maxMarks  = (float) ($marksMap[$attemptId] ?? 0);
            $score     = (float) ($r['score_obtained'] ?? 0);
            if ($maxMarks <= 0) {
                $maxMarks = $score > 0 ? $score : 0;
            }
            $pct = ($maxMarks > 0) ? round(($score / $maxMarks) * 100, 1) : null;

            $byQuiz[$quizId][] = [
                'attempt_id'    => $attemptId,
                'attempt_no'    => (int) ($r['attempt_no'] ?? 0),
                'score'         => $score,
                'max_marks'     => $maxMarks,
                'percentage'    => $pct,
                'correct'       => (int) ($stats['correct'] ?? 0),
                'wrong'         => (int) ($stats['wrong'] ?? 0),
                'total_q'       => (int) ($stats['total_q'] ?? 0),
                'submitted_at'  => (string) ($r['submitted_at'] ?? ''),
                'review_url'    => site_url('student/quizzes/review/' . $attemptId),
                'quiz_title'    => (string) ($r['quiz_title'] ?? ''),
                'term_session_id' => (int) ($r['term_session_id'] ?? 0),
                'subject_id'    => (int) ($r['subject_id'] ?? 0),
                'subject_label' => (string) ($r['subject_label'] ?? 'General'),
            ];
        }

        foreach ($byQuiz as $quizId => $list) {
            usort($list, static fn ($a, $b) => ((int) $a['attempt_no']) <=> ((int) $b['attempt_no']));
            $byQuiz[$quizId] = $list;
        }

        return $byQuiz;
    }

    /**
     * @param list<int> $attemptIds
     * @return array<int, array{total_q: int, correct: int, wrong: int}>
     */
    private function loadAttemptAnswerStats(array $attemptIds): array
    {
        if ($attemptIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($attemptIds), '?'));
        $rows         = $this->db->query("
            SELECT
                attempt_id,
                COUNT(*) AS total_q,
                SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) AS correct,
                SUM(CASE WHEN is_correct = 0 AND (
                    selected_option IS NOT NULL AND selected_option != ''
                    OR selected_options IS NOT NULL AND selected_options != '' AND selected_options != '[]'
                    OR answer_text IS NOT NULL AND TRIM(answer_text) != ''
                ) THEN 1 ELSE 0 END) AS wrong
            FROM quiz_attempt_answers
            WHERE attempt_id IN ({$placeholders})
            GROUP BY attempt_id
        ", $attemptIds)->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['attempt_id']] = [
                'total_q' => (int) ($r['total_q'] ?? 0),
                'correct' => (int) ($r['correct'] ?? 0),
                'wrong'   => (int) ($r['wrong'] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * @param list<int> $attemptIds
     * @return array<int, float>
     */
    private function loadAttemptTotalMarks(array $attemptIds): array
    {
        if ($attemptIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($attemptIds), '?'));
        $rows         = $this->db->query("
            SELECT attempt_id, SUM(marks) AS total_marks
            FROM quiz_attempt_questions
            WHERE attempt_id IN ({$placeholders})
            GROUP BY attempt_id
        ", $attemptIds)->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['attempt_id']] = (float) ($r['total_marks'] ?? 0);
        }

        return $out;
    }

    /**
     * @param array<int, list<array<string, mixed>>> $attemptsByQuiz
     * @return list<array<string, mixed>>
     */
    private function buildResultTermGroups(
        array $attemptsByQuiz,
        int $sessionId,
        int $systemId,
        int $currentTermSessionId
    ): array {
        $terms = $this->loadSessionTerms($sessionId, $systemId);
        $byTerm = [];

        foreach ($terms as $t) {
            $tsid = (int) ($t['term_session_id'] ?? 0);
            $byTerm[$tsid] = [
                'term_session_id' => $tsid,
                'term_name'       => (string) ($t['term_name'] ?? 'Term'),
                'term_short'      => (string) ($t['term_short'] ?? ''),
                'start_date'      => (string) ($t['start_date'] ?? ''),
                'end_date'        => (string) ($t['end_date'] ?? ''),
                'is_current'      => $tsid > 0 && $tsid === $currentTermSessionId,
                'summary'         => $this->emptyResultSummary(),
                'subjects'        => [],
            ];
        }

        $otherTerm = [
            'term_session_id' => 0,
            'term_name'       => 'Other',
            'term_short'      => '',
            'start_date'      => '',
            'end_date'        => '',
            'is_current'      => false,
            'summary'         => $this->emptyResultSummary(),
            'subjects'        => [],
        ];

        foreach ($attemptsByQuiz as $quizId => $attempts) {
            if ($attempts === []) {
                continue;
            }
            $first       = $attempts[0];
            $tsid        = (int) ($first['term_session_id'] ?? 0);
            $subjectId   = (int) ($first['subject_id'] ?? 0);
            $subjectKey  = $subjectId > 0 ? (string) $subjectId : 'sub_' . md5((string) ($first['subject_label'] ?? 'General'));

            if (! isset($byTerm[$tsid]) && $tsid > 0) {
                $byTerm[$tsid] = [
                    'term_session_id' => $tsid,
                    'term_name'       => 'Term',
                    'term_short'      => '',
                    'start_date'      => '',
                    'end_date'        => '',
                    'is_current'      => $tsid === $currentTermSessionId,
                    'summary'         => $this->emptyResultSummary(),
                    'subjects'        => [],
                ];
            }

            if ($tsid > 0 && isset($byTerm[$tsid])) {
                $this->appendQuizToResultTermBucket($byTerm[$tsid], $quizId, $attempts, $first, $subjectId, $subjectKey);
            } else {
                $this->appendQuizToResultTermBucket($otherTerm, $quizId, $attempts, $first, $subjectId, $subjectKey);
            }
        }

        foreach ($byTerm as &$term) {
            $term['subjects'] = array_values($term['subjects']);
            $this->finalizeResultSummary($term['summary']);
            foreach ($term['subjects'] as &$sub) {
                $this->finalizeResultSummary($sub['summary']);
            }
            unset($sub);
        }
        unset($term);

        $this->finalizeResultSummary($otherTerm['summary']);
        $otherTerm['subjects'] = array_values($otherTerm['subjects']);
        foreach ($otherTerm['subjects'] as &$sub) {
            $this->finalizeResultSummary($sub['summary']);
        }
        unset($sub);

        $groups = array_values($byTerm);
        if ($otherTerm['subjects'] !== [] || ($otherTerm['summary']['attempt_count'] ?? 0) > 0) {
            $groups[] = $otherTerm;
        }

        return $groups;
    }

    /**
     * @param array<string, mixed> $bucket
     * @param list<array<string, mixed>> $attempts
     */
    private function appendQuizToResultTermBucket(
        array &$bucket,
        int $quizId,
        array $attempts,
        array $first,
        int $subjectId,
        string $subjectKey
    ): void {
        if (! isset($bucket['subjects'][$subjectKey])) {
            $bucket['subjects'][$subjectKey] = [
                'subject_id'    => $subjectId,
                'subject_label' => (string) ($first['subject_label'] ?? 'General'),
                'summary'       => $this->emptyResultSummary(),
                'quizzes'       => [],
            ];
        }

        $bestPct = null;
        foreach ($attempts as $att) {
            if ($att['percentage'] !== null) {
                $bestPct = $bestPct === null
                    ? (float) $att['percentage']
                    : max($bestPct, (float) $att['percentage']);
            }
        }

        $bucket['subjects'][$subjectKey]['quizzes'][] = [
            'quiz_id'         => (int) $quizId,
            'quiz_title'      => (string) ($first['quiz_title'] ?? 'Quiz'),
            'attempts'        => $attempts,
            'attempt_count'   => count($attempts),
            'best_percentage' => $bestPct,
            'latest_score'    => (float) ($attempts[0]['score'] ?? 0),
        ];

        $this->accumulateResultSummary($bucket['subjects'][$subjectKey]['summary'], $attempts);
        $this->accumulateResultSummary($bucket['summary'], $attempts);
    }

    /**
     * @return array{attempt_count: int, quiz_count: int, avg_percentage: ?float, best_percentage: ?float}
     */
    private function emptyResultSummary(): array
    {
        return [
            'attempt_count'   => 0,
            'quiz_count'      => 0,
            'avg_percentage'  => null,
            'best_percentage' => null,
            '_pct_sum'        => 0,
            '_pct_n'          => 0,
            '_quiz_ids'       => [],
        ];
    }

    /**
     * @param array<string, mixed> $summary
     * @param list<array<string, mixed>> $attempts
     */
    private function accumulateResultSummary(array &$summary, array $attempts): void
    {
        foreach ($attempts as $att) {
            $summary['attempt_count']++;
            if ($att['percentage'] !== null) {
                $summary['_pct_sum'] += (float) $att['percentage'];
                $summary['_pct_n']++;
                $summary['best_percentage'] = $summary['best_percentage'] === null
                    ? (float) $att['percentage']
                    : max((float) $summary['best_percentage'], (float) $att['percentage']);
            }
        }
    }

    /**
     * @param array<string, mixed> $summary
     */
    private function finalizeResultSummary(array &$summary): void
    {
        $n = (int) ($summary['_pct_n'] ?? 0);
        if ($n > 0) {
            $summary['avg_percentage'] = round(((float) $summary['_pct_sum']) / $n, 1);
        }
        unset($summary['_pct_sum'], $summary['_pct_n'], $summary['_quiz_ids']);
    }
}
