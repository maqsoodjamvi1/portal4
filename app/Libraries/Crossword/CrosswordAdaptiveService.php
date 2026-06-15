<?php

namespace App\Libraries\Crossword;

/**
 * Adaptive / topic-aware crossword generation helpers (Phase 5).
 */
class CrosswordAdaptiveService
{
    /**
     * Bias operations toward weak areas from prior student attempts.
     *
     * @param string[] $baseOps
     * @param list<string> $weakOps e.g. ['÷', '×']
     * @return string[]
     */
    public function biasOperations(array $baseOps, array $weakOps): array
    {
        if ($weakOps === [] || $baseOps === []) {
            return $baseOps;
        }

        $weakOps = array_values(array_intersect($weakOps, $baseOps));
        if ($weakOps === []) {
            return $baseOps;
        }

        // Duplicate weak ops so random selection favours them ~60%.
        $weighted = array_merge($baseOps, $weakOps, $weakOps);

        return array_values(array_unique($weighted));
    }

    /**
     * Derive weak operations from a student's crossword attempt answers vs puzzles.
     *
     * @param list<array<string, mixed>> $puzzles
     * @param array<string, mixed> $answers
     * @return list<string>
     */
    public function detectWeakOperations(array $puzzles, array $answers): array
    {
        $missed = [];

        foreach ($puzzles as $pi => $puzzle) {
            if (($puzzle['type'] ?? '') !== 'missing_operator') {
                continue;
            }
            $cells = $puzzle['cells'] ?? [];
            foreach ($cells as $r => $row) {
                foreach ($row as $c => $cell) {
                    if (($cell['type'] ?? '') !== 'operator' || empty($cell['answer'])) {
                        continue;
                    }
                    $key   = "{$pi}_{$r}_{$c}";
                    $given = trim((string) ($answers[$key] ?? ''));
                    $expect = (string) ($cell['value'] ?? '');
                    if ($given !== $expect) {
                        $missed[] = $expect;
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($missed)));
    }

    /**
     * Fetch vocab topic IDs linked to a Question Bank topic (same class/subject naming).
     */
    public function vocabTopicIdsFromQbTopic(int $qbTopicId, int $classId): array
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('qb_topics') || ! $db->tableExists('vocab_topics')) {
            return [];
        }

        $qb = $db->table('qb_topics')->where('id', $qbTopicId)->get()->getRowArray();
        if ($qb === null) {
            return [];
        }

        $name = trim((string) ($qb['topic_name'] ?? $qb['name'] ?? ''));
        if ($name === '') {
            return [];
        }

        $rows = $db->table('vocab_topics')
            ->select('id')
            ->where('class_id', $classId)
            ->like('topic_name', $name, 'both')
            ->get()
            ->getResultArray();

        return array_map(static fn ($r) => (int) $r['id'], $rows);
    }
}
