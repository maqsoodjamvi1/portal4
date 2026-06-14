<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Cached dashboard aggregates and role-specific data loads.
 */
class DashboardDataService
{
    private const CACHE_TTL = 600;

    public function __construct(
        private BaseConnection $db,
        private int $campusId,
        private int $sessionId,
        private int $systemId,
    ) {}

    public function remember(string $suffix, callable $fn): mixed
    {
        if ($this->campusId <= 0) {
            return $fn();
        }

        $cache = \Config\Services::cache();
        $key   = 'dash_' . $suffix . '_' . $this->campusId . '_' . $this->sessionId;

        $hit = $cache->get($key);
        if ($hit !== null) {
            return $hit;
        }

        $val = $fn();
        $cache->save($key, $val, self::CACHE_TTL);

        return $val;
    }

    /**
     * @return array{
     *   teacherSections: array,
     *   teacherSubjects: array,
     *   subjectsPerSection: array,
     *   classTeacherSections: array,
     *   todayAttendance: object|null,
     *   classTeacherMap: array
     * }
     */
    public function loadTeacherContext(int $userId, string $today): array
    {
        $teacherSubjectSections = $this->db->query("
            SELECT DISTINCT
                cs.cls_sec_id,
                cs.class_id,
                cs.section_id,
                c.class_name,
                c.class_short_name,
                s.section_name,
                s.short_name as section_short_name
            FROM teacher_subjects ts
            INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
            INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
            INNER JOIN classes c ON c.class_id = cs.class_id
            INNER JOIN sections s ON s.section_id = cs.section_id
            WHERE ts.tid = ?
                AND cs.campus_id = ?
                AND ts.status = 1
                AND ss.status = 1
                AND cs.status = 1
                AND c.status = 1
                AND s.status = 1
            ORDER BY c.class_id ASC, s.section_id ASC
        ", [$userId, $this->campusId])->getResult();

        // Sections where this teacher is class teacher (subset of or separate from subject sections).
        $classTeacherSections = $this->db->table('teacher_section ts')
            ->select('cs.cls_sec_id, cs.class_id, cs.section_id, c.class_name, c.class_short_name, s.section_name, s.short_name as section_short_name, CONCAT(u.first_name, " ", u.last_name) as teacher_name, u.id as teacher_id')
            ->join('class_section cs', 'cs.cls_sec_id = ts.cls_sec_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->join('users u', 'u.id = ts.tid')
            ->where('ts.tid', $userId)
            ->where('ts.status', 1)
            ->where('cs.campus_id', $this->campusId)
            ->where('cs.status', 1)
            ->groupBy('cs.cls_sec_id')
            ->orderBy('c.class_id', 'ASC')
            ->get()
            ->getResult();

        $teacherSubjects      = [];
        $subjectsPerSection   = [];

        $teacherSubjects = $this->db->query("
            SELECT DISTINCT
                a.sid,
                a.subject_name,
                a.subject_short_name
            FROM teacher_subjects ts
            INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
            INNER JOIN allsubject a ON a.sid = ss.subject_id
            WHERE ts.tid = ?
                AND ts.status = 1
                AND ss.status = 1
                AND a.status = 1
            ORDER BY a.subject_name ASC
        ", [$userId])->getResult();

        foreach ($teacherSubjectSections as $section) {
            $subjectsPerSection[$section->cls_sec_id] = $this->db->query("
                SELECT a.sid, a.subject_name, a.subject_short_name
                FROM teacher_subjects ts
                INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
                INNER JOIN allsubject a ON a.sid = ss.subject_id
                WHERE ts.tid = ?
                    AND ss.cls_sec_id = ?
                    AND ts.status = 1
                    AND ss.status = 1
                ORDER BY a.subject_name ASC
            ", [$userId, $section->cls_sec_id])->getResult();
        }

        $todayAttendance = $this->db->table('attendance_employee')
            ->where('emp_id', $userId)
            ->where('date', $today)
            ->get()
            ->getRow();

        $classTeacherMap = [];
        $visibleSectionIds = array_map(
            static fn ($section) => (int) $section->cls_sec_id,
            $teacherSubjectSections
        );

        if ($visibleSectionIds !== []) {
            $classTeacherRows = $this->db->table('teacher_section ts')
                ->select('ts.cls_sec_id, CONCAT(u.first_name, " ", u.last_name) as teacher_name, u.id as teacher_id')
                ->join('users u', 'u.id = ts.tid')
                ->whereIn('ts.cls_sec_id', $visibleSectionIds)
                ->where('ts.status', 1)
                ->get()
                ->getResult();

            foreach ($classTeacherRows as $row) {
                $classTeacherMap[(int) $row->cls_sec_id] = [
                    'name' => $row->teacher_name ?? 'Not Assigned',
                    'id'   => (int) ($row->teacher_id ?? 0),
                ];
            }
        }

        return [
            'teacherSections'      => $teacherSubjectSections,
            'teacherSubjects'      => $teacherSubjects,
            'subjectsPerSection'   => $subjectsPerSection,
            'classTeacherSections' => $classTeacherSections,
            'todayAttendance'      => $todayAttendance,
            'classTeacherMap'      => $classTeacherMap,
        ];
    }

    /**
     * @return array{classes: list<string>, male_counts: list<int>, female_counts: list<int>}
     */
    public function loadClassStrength(int $academicSessionId): array
    {
        $classes = $this->db->table('classes c')
            ->select('c.class_id, c.class_short_name')
            ->where('c.system_id', $this->systemId)
            ->orderBy('c.class_id', 'ASC')
            ->get()
            ->getResult();

        $rows = $this->db->table('students s')
            ->select('cs.class_id, LOWER(TRIM(s.gender)) AS gender, COUNT(DISTINCT s.student_id) AS cnt', false)
            ->join('student_class sc', 'sc.student_id = s.student_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where('sc.session_id', $academicSessionId)
            ->where('sc.status', 1)
            ->where('s.campus_id', $this->campusId)
            ->where('s.status', 1)
            ->groupBy('cs.class_id, LOWER(TRIM(s.gender))')
            ->get()
            ->getResultArray();

        $byClass = [];
        foreach ($rows as $row) {
            $classId = (int) ($row['class_id'] ?? 0);
            if ($classId <= 0) {
                continue;
            }
            if (! isset($byClass[$classId])) {
                $byClass[$classId] = ['male' => 0, 'female' => 0];
            }
            $gender = (string) ($row['gender'] ?? '');
            $count  = (int) ($row['cnt'] ?? 0);
            if (in_array($gender, ['male', 'm'], true)) {
                $byClass[$classId]['male'] += $count;
            } elseif (in_array($gender, ['female', 'f'], true)) {
                $byClass[$classId]['female'] += $count;
            }
        }

        $labels = [];
        $male   = [];
        $female = [];

        foreach ($classes as $class) {
            $labels[] = $class->class_short_name;
            $male[]   = (int) ($byClass[$class->class_id]['male'] ?? 0);
            $female[] = (int) ($byClass[$class->class_id]['female'] ?? 0);
        }

        return [
            'classes'       => $labels,
            'male_counts'   => $male,
            'female_counts' => $female,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function loadPendingAttendance(string $today): array
    {
        $strengthSub = $this->db->table('student_class sc')
            ->select('sc.cls_sec_id, COUNT(sc.student_id) AS strength', false)
            ->join('students st', 'st.student_id = sc.student_id', 'inner')
            ->where('sc.session_id', $this->sessionId)
            ->where('st.campus_id', $this->campusId)
            ->where('st.status', 1)
            ->groupBy('sc.cls_sec_id')
            ->getCompiledSelect();

        $attSub = $this->db->table('attendance a')
            ->select("
                sc.cls_sec_id,
                SUM(CASE WHEN a.status = 'P' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN a.status = 'L' THEN 1 ELSE 0 END) AS leave_count,
                SUM(CASE WHEN a.el_duration > 0 THEN 1 ELSE 0 END) AS el_count,
                SUM(CASE WHEN a.lc_duration > 0 THEN 1 ELSE 0 END) AS lc_count
            ", false)
            ->join('student_class sc', 'sc.student_id = a.student_id', 'inner')
            ->where('a.date', $today)
            ->where('sc.session_id', $this->sessionId)
            ->groupBy('sc.cls_sec_id')
            ->getCompiledSelect();

        $teacherSub = $this->db->table('teacher_section ts')
            ->select("ts.cls_sec_id, MIN(CONCAT(u.first_name, ' ', u.last_name)) AS teacher_name", false)
            ->join('users u', 'u.id = ts.tid', 'inner')
            ->where('ts.status', 1)
            ->groupBy('ts.cls_sec_id')
            ->getCompiledSelect();

        return $this->db->table('mark_attendance ma')
            ->select("
                ma.cls_sec_id,
                c.class_name,
                s.section_name,
                tsub.teacher_name,
                ma.status,
                COALESCE(ss.strength, 0)        AS strength,
                COALESCE(att.present_count, 0)  AS present_count,
                COALESCE(att.absent_count, 0)   AS absent_count,
                COALESCE(att.leave_count, 0)    AS leave_count,
                COALESCE(att.el_count, 0)       AS el_count,
                COALESCE(att.lc_count, 0)       AS lc_count
            ", false)
            ->join('class_section cs', 'cs.cls_sec_id = ma.cls_sec_id', 'inner')
            ->join('classes c', 'c.class_id = cs.class_id', 'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->join("({$teacherSub}) tsub", 'tsub.cls_sec_id = ma.cls_sec_id', 'left')
            ->join("({$strengthSub}) ss", 'ss.cls_sec_id = ma.cls_sec_id', 'left')
            ->join("({$attSub}) att", 'att.cls_sec_id = ma.cls_sec_id', 'left')
            ->where('cs.campus_id', $this->campusId)
            ->where('cs.status', 1)
            ->where('ma.date', $today)
            ->where('ma.status', 'pending')
            ->groupBy('ma.cls_sec_id, c.class_name, s.section_name, tsub.teacher_name, ma.status, ss.strength, att.present_count, att.absent_count, att.leave_count, att.el_count, att.lc_count')
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return array{
     *   teacherAttendance: array,
     *   totalEmployees: int,
     *   presentCount: int,
     *   checkedOutCount: int,
     *   lateCount: int,
     *   absentEmployees: array,
     *   showEmployeeAttendance: bool
     * }
     */
    public function loadDirectorStaffAttendance(string $today): array
    {
        $teacherAttendance = $this->db->table('attendance_employee a')
            ->select('a.*, u.first_name, u.last_name, u.designation, u.photo')
            ->join('users u', 'a.emp_id = u.id')
            ->where('a.date', $today)
            ->where('u.campus_id', $this->campusId)
            ->where('u.status', 1)
            ->orderBy('a.checkin', 'ASC')
            ->get()
            ->getResult();

        $totalEmployees = $this->db->table('users')
            ->where('campus_id', $this->campusId)
            ->where('status', 1)
            ->countAllResults();

        $presentCount    = count($teacherAttendance);
        $checkedOutCount = $this->db->table('attendance_employee')
            ->where('date', $today)
            ->where('checkout IS NOT NULL')
            ->countAllResults();
        $lateCount = $this->db->table('attendance_employee')
            ->where('date', $today)
            ->where('status', 'late')
            ->countAllResults();

        $presentEmployeeIds = array_column($teacherAttendance, 'emp_id');
        $absentQuery        = $this->db->table('users')
            ->select('id, first_name, last_name, designation')
            ->where('campus_id', $this->campusId)
            ->where('status', 1)
            ->orderBy('first_name', 'ASC');

        if (! empty($presentEmployeeIds)) {
            $absentQuery->whereNotIn('id', $presentEmployeeIds);
        }

        return [
            'teacherAttendance'      => $teacherAttendance,
            'totalEmployees'         => $totalEmployees,
            'presentCount'           => $presentCount,
            'checkedOutCount'        => $checkedOutCount,
            'lateCount'              => $lateCount,
            'absentEmployees'        => $absentQuery->get()->getResult(),
            'showEmployeeAttendance' => true,
        ];
    }

    /**
     * @return array{teacherAttendance: array, showEmployeeAttendance: bool}
     */
    public function loadTeacherStaffAttendance(int $userId, int $limit = 3): array
    {
        return [
            'teacherAttendance' => $this->db->table('attendance_employee')
                ->where('emp_id', $userId)
                ->orderBy('date', 'DESC')
                ->limit($limit)
                ->get()
                ->getResult(),
            'showEmployeeAttendance' => false,
        ];
    }

    /**
     * Pending student attendance for sections where the teacher is class teacher.
     *
     * @param array<int, array{name: string, id: int}> $classTeacherMap
     * @param list<object>                             $teacherSections
     *
     * @return list<array<string, mixed>>
     */
    public function loadTeacherPendingAttendance(
        int $userId,
        string $today,
        array $classTeacherMap,
        array $teacherSections
    ): array {
        if ($teacherSections === []) {
            return [];
        }

        $classTeacherSectionIds = [];
        foreach ($teacherSections as $section) {
            $sectionId = (int) ($section->cls_sec_id ?? 0);
            if ($sectionId <= 0) {
                continue;
            }
            if (isset($classTeacherMap[$sectionId]) && (int) ($classTeacherMap[$sectionId]['id'] ?? 0) === $userId) {
                $classTeacherSectionIds[] = $sectionId;
            }
        }

        if ($classTeacherSectionIds === []) {
            return [];
        }

        $allPending = $this->loadPendingAttendance($today);

        return array_values(array_filter(
            $allPending,
            static fn (array $row): bool => in_array((int) ($row['cls_sec_id'] ?? 0), $classTeacherSectionIds, true)
        ));
    }

    /**
     * Sections where at least one assigned subject lacks a diary entry today.
     *
     * @return array{count: int, sections: list<int>}
     */
    public function loadTeacherDiaryMissing(int $userId, string $today): array
    {
        $assignments = $this->db->query("
            SELECT DISTINCT ss.cls_sec_id, ts.sec_sub_id
            FROM teacher_subjects ts
            INNER JOIN section_subjects ss ON ss.sec_sub_id = ts.sec_sub_id
            INNER JOIN class_section cs ON cs.cls_sec_id = ss.cls_sec_id
            WHERE ts.tid = ?
                AND ts.status = 1
                AND ss.status = 1
                AND cs.campus_id = ?
                AND cs.status = 1
        ", [$userId, $this->campusId])->getResultArray();

        if ($assignments === []) {
            return ['count' => 0, 'sections' => []];
        }

        $secSubIds = array_unique(array_map(static fn (array $row): int => (int) ($row['sec_sub_id'] ?? 0), $assignments));
        $secSubIds = array_values(array_filter($secSubIds, static fn (int $id): bool => $id > 0));

        if ($secSubIds === []) {
            return ['count' => 0, 'sections' => []];
        }

        $filledRows = $this->db->table('classdairy')
            ->select('sec_sub_id')
            ->where('date', $today)
            ->whereIn('sec_sub_id', $secSubIds)
            ->get()
            ->getResultArray();

        $filledIds = array_flip(array_map(static fn (array $row): int => (int) ($row['sec_sub_id'] ?? 0), $filledRows));

        $missingSections = [];
        foreach ($assignments as $assignment) {
            $secSubId = (int) ($assignment['sec_sub_id'] ?? 0);
            if ($secSubId <= 0 || isset($filledIds[$secSubId])) {
                continue;
            }
            $missingSections[(int) ($assignment['cls_sec_id'] ?? 0)] = true;
        }

        $sectionIds = array_values(array_filter(array_keys($missingSections), static fn (int $id): bool => $id > 0));

        return [
            'count'    => count($sectionIds),
            'sections' => $sectionIds,
        ];
    }

    /**
     * Subject-section pairs on the active exam datesheet with incomplete result entry.
     *
     * @return array{count: int}
     */
    public function loadTeacherResultsPending(int $userId, int $eid): array
    {
        if ($eid <= 0) {
            return ['count' => 0];
        }

        $rows = $this->db->query("
            SELECT
                d.cls_sec_id,
                d.sec_sub_id,
                (
                    SELECT COUNT(sc.student_id)
                    FROM student_class sc
                    INNER JOIN students st ON st.student_id = sc.student_id
                    WHERE sc.cls_sec_id = d.cls_sec_id
                        AND sc.session_id = ?
                        AND sc.status = 1
                        AND st.status = 1
                ) AS student_count,
                (
                    SELECT COUNT(sr.result_id)
                    FROM subject_results sr
                    WHERE sr.eid = ?
                        AND sr.cls_sec_id = d.cls_sec_id
                        AND sr.sec_sub_id = d.sec_sub_id
                        AND sr.obtained_marks IS NOT NULL
                ) AS result_count
            FROM datesheet d
            INNER JOIN section_subjects ss
                ON ss.sec_sub_id = d.sec_sub_id AND ss.cls_sec_id = d.cls_sec_id
            INNER JOIN teacher_subjects ts
                ON ts.sec_sub_id = ss.sec_sub_id AND ts.tid = ? AND ts.status = 1
            WHERE d.eid = ?
                AND ss.status = 1
            GROUP BY d.cls_sec_id, d.sec_sub_id
        ", [$this->sessionId, $eid, $userId, $eid])->getResultArray();

        $pending = 0;
        foreach ($rows as $row) {
            if ((int) ($row['student_count'] ?? 0) > (int) ($row['result_count'] ?? 0)) {
                $pending++;
            }
        }

        return ['count' => $pending];
    }

    /**
     * Count of published quizzes currently open for sections the teacher teaches.
     */
    public function loadTeacherQuizOpen(int $userId): int
    {
        $quizzes = $this->db->query("
            SELECT q.start_at, q.end_at
            FROM quizzes q
            INNER JOIN class_section cs ON cs.cls_sec_id = q.cls_sec_id
            INNER JOIN section_subjects ss ON ss.cls_sec_id = cs.cls_sec_id
            INNER JOIN teacher_subjects ts ON ts.sec_sub_id = ss.sec_sub_id AND ts.tid = ?
            WHERE cs.campus_id = ?
                AND q.is_published = 1
                AND ts.status = 1
                AND ss.status = 1
            GROUP BY q.quiz_id, q.start_at, q.end_at
        ", [$userId, $this->campusId])->getResult();

        $open = 0;
        foreach ($quizzes as $quiz) {
            if ($this->isQuizOpen($quiz)) {
                $open++;
            }
        }

        return $open;
    }

    private function isQuizOpen(object $quiz): bool
    {
        $nowTs = time();

        $toTs = static function ($dt) {
            if (! $dt) {
                return null;
            }
            $ts = strtotime((string) $dt);

            return $ts ?: null;
        };

        $startTs = $toTs($quiz->start_at ?? null);
        $endTs   = $toTs($quiz->end_at ?? null);

        if (! $startTs && ! $endTs) {
            return true;
        }

        if (($quiz->start_at ?? null) && ($quiz->end_at ?? null)
            && (string) $quiz->start_at === (string) $quiz->end_at) {
            return true;
        }

        if ($startTs && $nowTs < $startTs) {
            return false;
        }

        if ($endTs && $nowTs > $endTs) {
            return false;
        }

        return true;
    }

    /**
     * @param callable(): array $sessionsLoader
     * @param callable(): array $feeDataLoader
     * @param callable(): array $feeSummaryLoader
     *
     * @return array{allSessions: array, feeData: array, chartTitle: string, chartType: string, feeSummaryData: array}
     */
    /**
     * @param callable(): array $feeDataLoader Returns feeData, chartTitle, chartType keys
     *
     * @return array{allSessions: array, feeData: array, chartTitle: string, chartType: string, feeSummaryData: array}
     */
    public function loadFinanceBundle(?int $selectedSessionId, callable $sessionsLoader, callable $feeDataLoader, callable $feeSummaryLoader): array
    {
        $suffix = 'finance_' . ($selectedSessionId ?? 'last12');

        return $this->remember($suffix, static function () use ($sessionsLoader, $feeDataLoader, $feeSummaryLoader) {
            $chart = $feeDataLoader();

            return [
                'allSessions'    => $sessionsLoader(),
                'feeData'        => $chart['feeData'] ?? ['months' => [], 'paid' => [], 'unpaid' => []],
                'chartTitle'     => (string) ($chart['chartTitle'] ?? ''),
                'chartType'      => (string) ($chart['chartType'] ?? 'default'),
                'feeSummaryData' => $feeSummaryLoader(),
            ];
        });
    }
}
