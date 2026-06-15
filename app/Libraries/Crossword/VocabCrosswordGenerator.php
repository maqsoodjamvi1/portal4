<?php

namespace App\Libraries\Crossword;

/**
 * Builds a vocabulary crossword from vocab_bank topic words.
 */
class VocabCrosswordGenerator implements CrosswordGeneratorInterface
{
    private const GRID = 13;

    public function type(): string
    {
        return 'vocab';
    }

    public function label(): string
    {
        return 'Vocabulary Crossword (from Vocab Bank)';
    }

    /**
     * @param array{topic_ids?:int[], class_id?:int, subject_id?:int, word_count?:int} $options
     */
    public function generate(array $options): ?array
    {
        $entries = $this->fetchWords($options);
        if (count($entries) < 3) {
            return null;
        }

        $limit = max(3, min(8, (int) ($options['word_count'] ?? 6)));
        $entries = array_slice($entries, 0, $limit);

        usort($entries, static fn ($a, $b) => strlen($b['word']) <=> strlen($a['word']));

        $placed = $this->placeWords($entries);
        if ($placed === null) {
            return null;
        }

        return [
            'type'  => $this->type(),
            'size'  => self::GRID,
            'cells' => $placed['cells'],
            'clues' => $placed['clues'],
            'meta'  => ['word_count' => count($placed['clues']['across']) + count($placed['clues']['down'])],
        ];
    }

    /**
     * @param array<string, mixed> $options
     * @return list<array{word:string, clue:string}>
     */
    private function fetchWords(array $options): array
    {
        $topicIds = $options['topic_ids'] ?? [];
        if (! is_array($topicIds) || $topicIds === []) {
            return [];
        }

        $topicIds = array_values(array_filter(array_map('intval', $topicIds), static fn ($id) => $id > 0));
        if ($topicIds === []) {
            return [];
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('vocab_bank')) {
            return [];
        }

        $builder = $db->table('vocab_bank')
            ->select('word, meaning_en, example_sentence')
            ->whereIn('topic_id', $topicIds);

        if (! empty($options['class_id'])) {
            $builder->where('class_id', (int) $options['class_id']);
        }
        if (! empty($options['subject_id'])) {
            $builder->where('subject_id', (int) $options['subject_id']);
        }

        $rows = $builder->orderBy('word', 'ASC')->get()->getResultArray();
        $out  = [];

        foreach ($rows as $row) {
            $word = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) ($row['word'] ?? '')));
            if (strlen($word) < 3 || strlen($word) > 10) {
                continue;
            }
            $clue = trim((string) ($row['meaning_en'] ?? ''));
            if ($clue === '') {
                $clue = trim((string) ($row['example_sentence'] ?? ''));
            }
            if ($clue === '') {
                $clue = 'Vocabulary word';
            }
            $out[] = ['word' => $word, 'clue' => $clue];
        }

        return $out;
    }

    /**
     * @param list<array{word:string, clue:string}> $entries
     * @return array{cells:array, clues:array{across:array, down:array}}|null
     */
    private function placeWords(array $entries): ?array
    {
        $size  = self::GRID;
        $grid  = array_fill(0, $size, array_fill(0, $size, null));
        $clues = ['across' => [], 'down' => []];
        $num   = 1;

        $first = $entries[0]['word'];
        $startC = (int) floor(($size - strlen($first)) / 2);
        $startR = (int) floor($size / 2);

        for ($i = 0, $len = strlen($first); $i < $len; $i++) {
            $grid[$startR][$startC + $i] = $first[$i];
        }
        $clues['across'][] = ['num' => $num++, 'clue' => $entries[0]['clue'], 'answer' => $first, 'r' => $startR, 'c' => $startC, 'dir' => 'across'];

        for ($wi = 1, $wCount = count($entries); $wi < $wCount; $wi++) {
            $word = $entries[$wi]['word'];
            $placed = false;

            for ($r = 0; $r < $size && ! $placed; $r++) {
                for ($c = 0; $c < $size && ! $placed; $c++) {
                    $ch = $grid[$r][$c];
                    if ($ch === null) {
                        continue;
                    }
                    for ($i = 0, $len = strlen($word); $i < $len; $i++) {
                        if ($word[$i] !== $ch) {
                            continue;
                        }
                        // Try vertical
                        $vr = $r - $i;
                        $vc = $c;
                        if ($this->canPlace($grid, $word, $vr, $vc, true)) {
                            for ($j = 0; $j < $len; $j++) {
                                $grid[$vr + $j][$vc] = $word[$j];
                            }
                            $clues['down'][] = ['num' => $num++, 'clue' => $entries[$wi]['clue'], 'answer' => $word, 'r' => $vr, 'c' => $vc, 'dir' => 'down'];
                            $placed = true;
                            break;
                        }
                        // Try horizontal
                        $hr = $r;
                        $hc = $c - $i;
                        if ($this->canPlace($grid, $word, $hr, $hc, false)) {
                            for ($j = 0; $j < $len; $j++) {
                                $grid[$hr][$hc + $j] = $word[$j];
                            }
                            $clues['across'][] = ['num' => $num++, 'clue' => $entries[$wi]['clue'], 'answer' => $word, 'r' => $hr, 'c' => $hc, 'dir' => 'across'];
                            $placed = true;
                            break;
                        }
                    }
                }
            }

            if (! $placed) {
                continue;
            }
        }

        if (count($clues['across']) + count($clues['down']) < 3) {
            return null;
        }

        $cells = array_fill(0, $size, array_fill(0, $size, ['type' => 'blank']));
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($grid[$r][$c] !== null) {
                    $cells[$r][$c] = ['type' => 'letter', 'value' => '', 'answer' => true, 'solution' => $grid[$r][$c]];
                } else {
                    $cells[$r][$c] = ['type' => 'block'];
                }
            }
        }

        return ['cells' => $cells, 'clues' => $clues];
    }

    /**
     * @param array<int, array<int, string|null>> $grid
     */
    private function canPlace(array $grid, string $word, int $r, int $c, bool $vertical): bool
    {
        $size = self::GRID;
        $len  = strlen($word);

        if ($vertical) {
            if ($r < 0 || $r + $len > $size) {
                return false;
            }
            if ($r > 0 && $grid[$r - 1][$c] !== null) {
                return false;
            }
            if ($r + $len < $size && $grid[$r + $len][$c] !== null) {
                return false;
            }
            for ($i = 0; $i < $len; $i++) {
                $cell = $grid[$r + $i][$c];
                if ($cell !== null && $cell !== $word[$i]) {
                    return false;
                }
                if ($cell === null) {
                    if ($c > 0 && $grid[$r + $i][$c - 1] !== null) {
                        return false;
                    }
                    if ($c + 1 < $size && $grid[$r + $i][$c + 1] !== null) {
                        return false;
                    }
                }
            }

            return true;
        }

        if ($c < 0 || $c + $len > $size) {
            return false;
        }
        if ($c > 0 && $grid[$r][$c - 1] !== null) {
            return false;
        }
        if ($c + $len < $size && $grid[$r][$c + $len] !== null) {
            return false;
        }
        for ($i = 0; $i < $len; $i++) {
            $cell = $grid[$r][$c + $i];
            if ($cell !== null && $cell !== $word[$i]) {
                return false;
            }
            if ($cell === null) {
                if ($r > 0 && $grid[$r - 1][$c + $i] !== null) {
                    return false;
                }
                if ($r + 1 < $size && $grid[$r + 1][$c + $i] !== null) {
                    return false;
                }
            }
        }

        return true;
    }
}
