<?php

namespace App\Libraries\Crossword;

/**
 * Persists and loads crossword worksheet sets, assignments, and attempts.
 */
class CrosswordSetService
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct(?\CodeIgniter\Database\BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('crossword_sets')
            && $this->db->tableExists('crossword_puzzles')
            && $this->db->tableExists('crossword_assignments')
            && $this->db->tableExists('crossword_attempts');
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

        $this->db->table('crossword_sets')->insert([
            'campus_id'     => $campusId,
            'title'         => $title,
            'puzzle_type'   => $settings['puzzle_type'] ?? 'math_square',
            'grade'         => (int) ($settings['grade'] ?? 0),
            'settings_json' => json_encode($settings),
            'student_name'  => $studentName,
            'created_by'    => $userId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $setId = (int) $this->db->insertID();

        foreach ($puzzles as $i => $puzzle) {
            $this->db->table('crossword_puzzles')->insert([
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

        $builder = $this->db->table('crossword_sets')
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

        $builder = $this->db->table('crossword_sets')->where('id', $setId);

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

        $rows = $this->db->table('crossword_puzzles')
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

        $ok = $this->db->table('crossword_assignments')->insert([
            'set_id'      => $setId,
            'campus_id'   => $campusId,
            'cls_sec_id'  => $clsSecId,
            'due_date'    => $dueDate !== '' && $dueDate !== null ? $dueDate : null,
            'status'      => 1,
            'assigned_by' => $userId,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        if (! $ok) {
            return 0;
        }

        return (int) $this->db->insertID();
    }

    public function assignmentExists(int $setId, int $clsSecId): bool
    {
        if (! $this->tablesReady()) {
            return false;
        }

        return $this->db->table('crossword_assignments')
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

        $builder = $this->db->table('crossword_assignments ca')
            ->select('ca.*, cs.title, cs.puzzle_type, cs.grade, c.class_name, s.section_name,
                (SELECT COUNT(*) FROM crossword_attempts WHERE assignment_id = ca.id) as attempt_count')
            ->join('crossword_sets cs', 'cs.id = ca.set_id')
            ->join('class_section cs2', 'cs2.cls_sec_id = ca.cls_sec_id', 'left')
            ->join('classes c', 'c.class_id = cs2.class_id', 'left')
            ->join('sections s', 's.section_id = cs2.section_id', 'left')
            ->where('ca.status', 1)
            ->orderBy('ca.created_at', 'DESC')
            ->limit($limit);

        if ($campusId > 0) {
            $builder->groupStart()
                ->where('ca.campus_id', $campusId)
                ->orWhere('ca.campus_id', 0)
                ->groupEnd();
        }

        if ($setId > 0) {
            $builder->where('ca.set_id', $setId);
        }

        if ($clsSecId > 0) {
            $builder->where('ca.cls_sec_id', $clsSecId);
        }

        return $builder->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function listAssignments(int $campusId, int $setId = 0, int $clsSecId = 0, int $limit = 100): array
    {
        return $this->listAssignmentsForCampus($campusId, $setId, $clsSecId, $limit);
    }

    public function hasCompletedAttempt(int $assignmentId, int $studentId): bool
    {
        if (! $this->tablesReady()) {
            return false;
        }

        return $this->db->table('crossword_attempts')
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

        $row = $this->db->table('crossword_attempts')
            ->where('assignment_id', $assignmentId)
            ->where('student_id', $studentId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        return [
            'score'   => (int) ($row['score'] ?? 0),
            'correct' => (int) ($row['correct_count'] ?? 0),
            'total'   => (int) ($row['total_count'] ?? 0),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function assignmentsForClass(int $clsSecId): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('crossword_assignments ca')
            ->select('ca.*, cs.title, cs.puzzle_type, cs.grade')
            ->join('crossword_sets cs', 'cs.id = ca.set_id')
            ->where('ca.cls_sec_id', $clsSecId)
            ->where('ca.status', 1)
            ->orderBy('ca.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function studentAssignments(int $studentId, int $clsSecId): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('crossword_assignments ca')
            ->select('ca.*, cs.title, cs.puzzle_type, cs.grade,
                (SELECT score FROM crossword_attempts WHERE assignment_id = ca.id AND student_id = ' . (int) $studentId . ' ORDER BY id DESC LIMIT 1) as last_score')
            ->join('crossword_sets cs', 'cs.id = ca.set_id')
            ->where('ca.cls_sec_id', $clsSecId)
            ->where('ca.status', 1)
            ->orderBy('ca.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @param array<string, mixed> $answers keyed by puzzleIndex_cellKey
     */
    public function submitAttempt(int $assignmentId, int $studentId, array $answers, array $puzzles): array
    {
        if ($this->hasCompletedAttempt($assignmentId, $studentId)) {
            return ['score' => 0, 'correct' => 0, 'total' => 0, 'already_submitted' => true];
        }

        $score     = $this->gradeAnswers($answers, $puzzles);
        $total     = $score['total'];
        $correct   = $score['correct'];
        $pct       = $total > 0 ? (int) round(($correct / $total) * 100) : 0;

        $this->db->table('crossword_attempts')->insert([
            'assignment_id' => $assignmentId,
            'student_id'    => $studentId,
            'answers_json'  => json_encode($answers),
            'score'         => $pct,
            'correct_count' => $correct,
            'total_count'   => $total,
            'submitted_at'  => date('Y-m-d H:i:s'),
        ]);

        return ['score' => $pct, 'correct' => $correct, 'total' => $total];
    }

    /**
     * @param array<string, mixed> $answers
     * @param list<array<string, mixed>> $puzzles
     * @return array{correct:int, total:int}
     */
    public function gradeAnswers(array $answers, array $puzzles): array
    {
        $correct = 0;
        $total   = 0;

        foreach ($puzzles as $pi => $puzzle) {
            $cells = $puzzle['cells'] ?? [];
            $size  = (int) ($puzzle['size'] ?? 7);
            for ($r = 0; $r < $size; $r++) {
                for ($c = 0; $c < $size; $c++) {
                    $cell = $cells[$r][$c] ?? null;
                    if ($cell === null || empty($cell['answer'])) {
                        continue;
                    }
                    $total++;
                    $key     = "{$pi}_{$r}_{$c}";
                    $given   = trim((string) ($answers[$key] ?? ''));
                    $expect  = trim((string) ($cell['value'] ?? $cell['solution'] ?? ''));
                    if ($given !== '' && strcasecmp($given, $expect) === 0) {
                        $correct++;
                    }
                }
            }
        }

        return ['correct' => $correct, 'total' => $total];
    }

    /** @return list<array<string, mixed>> */
    public function attemptReport(int $assignmentId): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('crossword_attempts ca')
            ->select('ca.*, CONCAT(s.first_name, " ", s.last_name) as student_name, s.reg_no')
            ->join('students s', 's.student_id = ca.student_id', 'left')
            ->where('ca.assignment_id', $assignmentId)
            ->orderBy('ca.submitted_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
