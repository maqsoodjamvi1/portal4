<?php

namespace App\Libraries\WordSearch;

/**
 * Persists word-search sets, assignments, and student attempts.
 */
class WordSearchSetService
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct(?\CodeIgniter\Database\BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('word_search_sets')
            && $this->db->tableExists('word_search_puzzles')
            && $this->db->tableExists('word_search_assignments')
            && $this->db->tableExists('word_search_attempts');
    }

    /**
     * @param array<string, mixed> $settings
     * @param list<array<string, mixed>> $puzzles
     */
    public function saveSet(
        string $title,
        int $campusId,
        int $userId,
        array $settings,
        array $puzzles,
        ?string $studentName = null
    ): int {
        $this->db->transStart();

        $this->db->table('word_search_sets')->insert([
            'campus_id'     => $campusId,
            'title'         => $title,
            'grade'         => (int) ($settings['grade'] ?? 0),
            'settings_json' => json_encode($settings),
            'student_name'  => $studentName,
            'created_by'    => $userId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $setId = (int) $this->db->insertID();

        foreach ($puzzles as $i => $puzzle) {
            $this->db->table('word_search_puzzles')->insert([
                'set_id'      => $setId,
                'sort_order'  => $i + 1,
                'puzzle_json' => json_encode($puzzle),
            ]);
        }

        $this->db->transComplete();

        return $setId;
    }

    /** @return list<array<string, mixed>> */
    public function listSets(int $campusId, int $limit = 50): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $builder = $this->db->table('word_search_sets')
            ->orderBy('created_at', 'DESC')
            ->limit($limit);

        if ($campusId > 0) {
            $builder->groupStart()
                ->where('campus_id', $campusId)
                ->orWhere('campus_id', 0)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /** @return array{set:array, puzzles:list<array>}|null */
    public function loadSet(int $setId, int $campusId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $builder = $this->db->table('word_search_sets')->where('id', $setId);

        if ($campusId > 0) {
            $builder->groupStart()
                ->where('campus_id', $campusId)
                ->orWhere('campus_id', 0)
                ->groupEnd();
        }

        $set = $builder->get()->getRowArray();
        if ($set === null) {
            return null;
        }

        $rows = $this->db->table('word_search_puzzles')
            ->where('set_id', $setId)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $puzzles = [];
        foreach ($rows as $row) {
            $puzzles[] = json_decode($row['puzzle_json'], true) ?? [];
        }

        return ['set' => $set, 'puzzles' => $puzzles];
    }

    public function assignToClass(int $setId, int $clsSecId, int $campusId, int $userId, ?string $dueDate = null): int
    {
        if (! $this->tablesReady()) {
            return 0;
        }

        $ok = $this->db->table('word_search_assignments')->insert([
            'set_id'      => $setId,
            'campus_id'   => $campusId,
            'cls_sec_id'  => $clsSecId,
            'due_date'    => $dueDate !== '' && $dueDate !== null ? $dueDate : null,
            'status'      => 1,
            'assigned_by' => $userId,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $ok ? (int) $this->db->insertID() : 0;
    }

    public function assignmentExists(int $setId, int $clsSecId): bool
    {
        if (! $this->tablesReady()) {
            return false;
        }

        return $this->db->table('word_search_assignments')
            ->where('set_id', $setId)
            ->where('cls_sec_id', $clsSecId)
            ->where('status', 1)
            ->countAllResults() > 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAssignmentsForCampus(int $campusId, int $setId = 0, int $clsSecId = 0, int $limit = 100): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $builder = $this->db->table('word_search_assignments wa')
            ->select('wa.*, ws.title, ws.grade, c.class_name, s.section_name,
                (SELECT COUNT(*) FROM word_search_attempts WHERE assignment_id = wa.id) as attempt_count')
            ->join('word_search_sets ws', 'ws.id = wa.set_id')
            ->join('class_section cs2', 'cs2.cls_sec_id = wa.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs2.class_id', 'left')
            ->join('sections s', 's.section_id = cs2.section_id', 'left')
            ->where('wa.status', 1)
            ->orderBy('wa.created_at', 'DESC')
            ->limit($limit);

        if ($campusId > 0) {
            $builder->groupStart()
                ->where('wa.campus_id', $campusId)
                ->orWhere('wa.campus_id', 0)
                ->groupEnd();
        }
        if ($setId > 0) {
            $builder->where('wa.set_id', $setId);
        }
        if ($clsSecId > 0) {
            $builder->where('wa.cls_sec_id', $clsSecId);
        }

        return $builder->get()->getResultArray();
    }

    public function hasCompletedAttempt(int $assignmentId, int $studentId): bool
    {
        if (! $this->tablesReady()) {
            return false;
        }

        return $this->db->table('word_search_attempts')
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->countAllResults() > 0;
    }

    /** @return array<string, mixed>|null */
    public function getLatestAttempt(int $assignmentId, int $studentId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('word_search_attempts')
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        $answers = json_decode($row['answers_json'] ?? '{}', true) ?? [];

        return [
            'score'   => (int) ($row['score'] ?? 0),
            'correct' => (int) ($row['correct_count'] ?? 0),
            'total'   => (int) ($row['total_count'] ?? 0),
            'found'   => $answers['found'] ?? [],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function studentAssignments(int $studentId, int $clsSecId): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('word_search_assignments wa')
            ->select('wa.*, ws.title, ws.grade,
                (SELECT score FROM word_search_attempts WHERE assignment_id = wa.id AND student_id = ' . (int) $studentId . ' ORDER BY id DESC LIMIT 1) as last_score')
            ->join('word_search_sets ws', 'ws.id = wa.set_id')
            ->where('wa.cls_sec_id', $clsSecId)
            ->where('wa.status', 1)
            ->orderBy('wa.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @param list<array<string, mixed>> $puzzles
     * @param array<string, mixed> $submission {found: int[], paths?: array}
     */
    public function submitAttempt(int $assignmentId, int $studentId, array $submission, array $puzzles): array
    {
        if ($this->hasCompletedAttempt($assignmentId, $studentId)) {
            return ['score' => 0, 'correct' => 0, 'total' => 0, 'already_submitted' => true];
        }

        $graded = $this->gradeAllPuzzles($submission, $puzzles);
        $pct    = $graded['total'] > 0
            ? (int) round(($graded['correct'] / $graded['total']) * 100)
            : 0;

        $this->db->table('word_search_attempts')->insert([
            'assignment_id' => $assignmentId,
            'student_id'    => $studentId,
            'answers_json'  => json_encode($submission),
            'score'         => $pct,
            'correct_count' => $graded['correct'],
            'total_count'   => $graded['total'],
            'submitted_at'  => date('Y-m-d H:i:s'),
        ]);

        return [
            'score'   => $pct,
            'correct' => $graded['correct'],
            'total'   => $graded['total'],
            'found'   => $graded['valid_found'],
        ];
    }

    /**
     * @param array<string, mixed> $submission
     * @param list<array<string, mixed>> $puzzles
     * @return array{correct:int, total:int, valid_found:list<int>}
     */
    public function gradeAllPuzzles(array $submission, array $puzzles): array
    {
        $foundByPuzzle = $submission['found'] ?? [];
        if (! is_array($foundByPuzzle)) {
            $foundByPuzzle = [];
        }

        $correct    = 0;
        $total      = 0;
        $validFound = [];

        foreach ($puzzles as $pi => $puzzle) {
            $wordIds = $foundByPuzzle[$pi] ?? $foundByPuzzle[(string) $pi] ?? [];
            if (! is_array($wordIds)) {
                $wordIds = [];
            }
            $graded = $this->gradeFoundWords($wordIds, $puzzle);
            $correct += $graded['correct'];
            $total   += $graded['total'];
            $validFound[$pi] = $graded['valid_ids'];
        }

        return ['correct' => $correct, 'total' => $total, 'valid_found' => $validFound];
    }

    /**
     * @param list<int|string> $foundWordIds
     * @param array<string, mixed> $puzzle
     * @return array{correct:int, total:int, valid_ids:list<int>}
     */
    public function gradeFoundWords(array $foundWordIds, array $puzzle): array
    {
        $words       = $puzzle['words'] ?? [];
        $placements  = $puzzle['placements'] ?? [];
        $validIds    = [];
        $placementMap = [];

        foreach ($placements as $p) {
            $placementMap[(int) ($p['word_id'] ?? -1)] = $p['cells'] ?? [];
        }

        $allowedIds = [];
        foreach ($words as $w) {
            $allowedIds[(int) ($w['id'] ?? -1)] = true;
        }

        foreach ($foundWordIds as $id) {
            $id = (int) $id;
            if ($id < 0 || ! isset($allowedIds[$id]) || ! isset($placementMap[$id])) {
                continue;
            }
            $validIds[] = $id;
        }

        $validIds = array_values(array_unique($validIds));

        return [
            'correct'   => count($validIds),
            'total'     => count($words),
            'valid_ids' => $validIds,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function attemptReport(int $assignmentId): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('word_search_attempts wa')
            ->select('wa.*, CONCAT(s.first_name, " ", s.last_name) as student_name, s.reg_no')
            ->join('students s', 's.student_id = wa.student_id', 'left')
            ->where('wa.assignment_id', $assignmentId)
            ->orderBy('wa.submitted_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Strip placements from puzzles for student-facing views.
     *
     * @param list<array<string, mixed>> $puzzles
     * @return list<array<string, mixed>>
     */
    public function puzzlesForStudent(array $puzzles): array
    {
        $out = [];
        foreach ($puzzles as $puzzle) {
            unset($puzzle['placements']);
            $out[] = $puzzle;
        }

        return $out;
    }
}
