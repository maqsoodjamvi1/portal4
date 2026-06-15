<?php

namespace App\Libraries\MathWorksheet;

/**
 * Persists and loads math worksheet sets for the library.
 */
class MathWorksheetSetService
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct(?\CodeIgniter\Database\BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('math_worksheet_sets')
            && $this->db->tableExists('math_worksheet_problems');
    }

    /**
     * @param array<string, mixed> $settings
     * @param list<array<string, mixed>> $problems
     */
    public function saveSet(
        string $title,
        int $campusId,
        int $userId,
        array $settings,
        array $problems,
        ?string $studentName = null
    ): int {
        $this->db->transStart();

        $this->db->table('math_worksheet_sets')->insert([
            'campus_id'     => $campusId,
            'title'         => $title,
            'grade'         => (int) ($settings['number_config']['operand_a']['whole_digits'] ?? 0),
            'layout'        => (string) ($settings['layout'] ?? 'horizontal'),
            'problem_count' => count($problems),
            'settings_json' => json_encode($settings),
            'student_name'  => $studentName,
            'created_by'    => $userId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $setId = (int) $this->db->insertID();

        $this->db->table('math_worksheet_problems')->insert([
            'set_id'        => $setId,
            'problems_json' => json_encode($problems),
        ]);

        $this->db->transComplete();

        return $setId;
    }

    /** @return list<array<string, mixed>> */
    public function listSets(int $campusId, int $limit = 50): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('math_worksheet_sets')
            ->where('campus_id', $campusId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /** @return array{set:array, problems:list<array>}|null */
    public function loadSet(int $setId, int $campusId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $set = $this->db->table('math_worksheet_sets')
            ->where('id', $setId)
            ->where('campus_id', $campusId)
            ->get()
            ->getRowArray();

        if ($set === null) {
            return null;
        }

        $row = $this->db->table('math_worksheet_problems')
            ->where('set_id', $setId)
            ->get()
            ->getRowArray();

        $problems = [];
        if ($row !== null) {
            $problems = json_decode($row['problems_json'] ?? '[]', true) ?? [];
        }

        return ['set' => $set, 'problems' => $problems];
    }
}
