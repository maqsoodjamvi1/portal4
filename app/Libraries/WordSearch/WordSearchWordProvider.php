<?php

namespace App\Libraries\WordSearch;

/**
 * Loads vocabulary words from vocab_bank and optional manual lists.
 */
class WordSearchWordProvider
{
    /**
     * @param array<string, mixed> $options
     * @return list<array{word:string, clue:string}>
     */
    public function fetchWords(array $options): array
    {
        $fromBank   = $this->fetchFromVocabBank($options);
        $fromManual = $this->parseManualWords((string) ($options['manual_words'] ?? ''));

        $seen = [];
        $out  = [];

        foreach (array_merge($fromBank, $fromManual) as $entry) {
            $word = $entry['word'];
            if (isset($seen[$word])) {
                continue;
            }
            $seen[$word] = true;
            $out[]       = $entry;
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $options
     * @return list<array{word:string, clue:string}>
     */
    private function fetchFromVocabBank(array $options): array
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

        return $this->normalizeEntries($rows);
    }

    /**
     * @return list<array{word:string, clue:string}>
     */
    private function parseManualWords(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $out   = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line, 2));
            $word  = strtoupper(preg_replace('/[^A-Za-z]/', '', $parts[0]));
            if (strlen($word) < 3 || strlen($word) > 15) {
                continue;
            }

            $clue = $parts[1] ?? '';
            if ($clue === '') {
                $clue = 'Find this word';
            }

            $out[] = ['word' => $word, 'clue' => $clue];
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array{word:string, clue:string}>
     */
    private function normalizeEntries(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            $word = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) ($row['word'] ?? '')));
            if (strlen($word) < 3 || strlen($word) > 15) {
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
}
