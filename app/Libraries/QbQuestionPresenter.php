<?php

namespace App\Libraries;

/**
 * Normalizes qb_questions rows for JSON, preview, and print views.
 */
class QbQuestionPresenter
{
    /**
     * @param object|array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function normalize($row): array
    {
        $r    = is_array($row) ? $row : (array) $row;
        $type = (string) ($r['question_type'] ?? 'mcq');

        $out = [
            'id'                 => (int) ($r['id'] ?? 0),
            'class_id'           => (int) ($r['class_id'] ?? 0),
            'subject_id'         => (int) ($r['subject_id'] ?? 0),
            'topic_id'           => (int) ($r['topic_id'] ?? 0),
            'class_name'         => $r['class_name'] ?? '',
            'subject_name'       => $r['subject_name'] ?? '',
            'topic_name'         => $r['topic_name'] ?? '',
            'question_type'      => $type,
            'difficulty'         => (string) ($r['difficulty'] ?? 'normal'),
            'question_media'     => (string) ($r['question_media'] ?? 'text'),
            'question'           => (string) ($r['question'] ?? ''),
            'question_image'     => $r['question_image'] ?? null,
            'question_image_alt' => (string) ($r['question_image_alt'] ?? ''),
            'option_a'           => $r['option_a'] ?? null,
            'option_b'           => $r['option_b'] ?? null,
            'option_c'           => $r['option_c'] ?? null,
            'option_d'           => $r['option_d'] ?? null,
            'correct_option'     => $r['correct_option'] ?? null,
            'answer_text'        => $r['answer_text'] ?? null,
            'options_json_raw'   => $r['options_json'] ?? null,
            'correct_options'    => null,
            'match_pairs'        => null,
            'is_drag'            => (int) ($r['is_drag'] ?? 0),
        ];

        $img = $r['question_image'] ?? '';
        if (is_string($img) && $img !== '') {
            $base = basename($img);
            $out['question_image_public_url'] = base_url('media/qb/' . rawurlencode($base));
        } else {
            $out['question_image_public_url'] = null;
        }

        if ($type === 'mcq_multi') {
            $decoded = json_decode((string) ($r['options_json'] ?? ''), true);
            if (is_array($decoded)) {
                $opts = $decoded['options'] ?? [];
                if (is_array($opts)) {
                    $out['option_a'] = $opts['A'] ?? $out['option_a'];
                    $out['option_b'] = $opts['B'] ?? $out['option_b'];
                    $out['option_c'] = $opts['C'] ?? $out['option_c'];
                    $out['option_d'] = $opts['D'] ?? $out['option_d'];
                }
                $cm = $decoded['correct_multi'] ?? [];
                $out['correct_options'] = is_array($cm)
                    ? array_values(array_unique(array_map('strtoupper', array_map('strval', $cm))))
                    : [];
            } else {
                $out['correct_options'] = [];
            }
        }

        if ($type === 'match') {
            $decoded = json_decode((string) ($r['options_json'] ?? ''), true);
            $out['match_pairs'] = is_array($decoded) ? $decoded : [];
        }

        return $out;
    }

    /**
     * @param list<object|array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public static function normalizeMany(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[] = self::normalize($row);
        }

        return $out;
    }
}
