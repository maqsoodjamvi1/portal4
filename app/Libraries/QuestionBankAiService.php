<?php

namespace App\Libraries;

/**
 * Gemini-based question generation for QB AI Generate page.
 */
class QuestionBankAiService
{
    public const MAX_PER_TYPE = 50;
    public const MAX_TOTAL    = 60;
    public const BATCH_SIZE   = 12;

    /**
     * @param array{
     *   class_name?: string,
     *   subject_name?: string,
     *   topic_name?: string,
     *   topic_description?: string,
     *   extra_instructions?: string,
     * } $context
     * @param array{mcq?: int, tf?: int, fill?: int, short?: int, descriptive?: int, match?: int} $counts
     * @return array{status: string, questions?: list<array>, errors?: list<string>, provider?: string, model?: string, message?: string}
     */
    public function generate(array $context, array $counts): array
    {
        $counts = $this->sanitizeCounts($counts);
        $total  = array_sum($counts);

        if ($total <= 0) {
            return ['status' => 'error', 'message' => 'Set at least one question count greater than zero.'];
        }

        if ($total > self::MAX_TOTAL) {
            return [
                'status'  => 'error',
                'message' => 'Total questions cannot exceed ' . self::MAX_TOTAL . ' per generation.',
            ];
        }

        $apiKey = getenv('google.api_key');
        if (!$apiKey) {
            return ['status' => 'error', 'message' => 'Gemini API is not configured (google.api_key in .env).'];
        }

        $model    = getenv('qb.ai.model') ?: 'gemini-2.5-pro';
        $batches  = $this->buildBatches($counts);
        $merged   = [];
        $errors   = [];
        $provider = 'gemini';

        foreach ($batches as $batchIndex => $batchCounts) {
            $prompt = $this->buildPrompt($context, $batchCounts);
            [$text, $raw] = $this->callGemini($prompt, $model);

            if ($text === null || trim($text) === '') {
                $errors[] = 'Batch ' . ($batchIndex + 1) . ' failed: ' . $this->summarizeApiError($raw);
                continue;
            }

            $items = $this->decodeJsonQuestions($text);
            if ($items === null) {
                $errors[] = 'Batch ' . ($batchIndex + 1) . ' returned invalid JSON.';
                continue;
            }

            foreach ($items as $idx => $item) {
                if (!is_array($item)) {
                    $errors[] = 'Batch ' . ($batchIndex + 1) . ', item #' . ($idx + 1) . ' is not an object.';
                    continue;
                }
                $norm = $this->normalizeQuestionItem($item);
                if ($norm === null) {
                    $errors[] = 'Batch ' . ($batchIndex + 1) . ', item #' . ($idx + 1) . ' failed validation.';
                    continue;
                }
                $merged[] = $norm;
            }
        }

        if ($merged === []) {
            return [
                'status'   => 'error',
                'message'  => 'No valid questions were generated.',
                'errors'   => $errors,
                'provider' => $provider,
                'model'    => $model,
            ];
        }

        return [
            'status'    => 'ok',
            'questions' => $merged,
            'errors'    => $errors,
            'provider'  => $provider,
            'model'     => $model,
            'count'     => count($merged),
        ];
    }

    /**
     * @param array{mcq?: int, tf?: int, fill?: int, short?: int, descriptive?: int, match?: int} $counts
     * @return array{mcq: int, tf: int, fill: int, short: int, descriptive: int, match: int}
     */
    public function sanitizeCounts(array $counts): array
    {
        $out = ['mcq' => 0, 'tf' => 0, 'fill' => 0, 'short' => 0, 'descriptive' => 0, 'match' => 0];
        foreach ($out as $key => $_) {
            $v = (int) ($counts[$key] ?? 0);
            $out[$key] = max(0, min(self::MAX_PER_TYPE, $v));
        }

        return $out;
    }

    /**
     * @param array{mcq: int, tf: int, fill: int, short: int, descriptive: int, match: int} $counts
     * @return list<array{mcq: int, tf: int, fill: int, short: int, descriptive: int, match: int}>
     */
    private function buildBatches(array $counts): array
    {
        $batches = [];
        $remaining = $counts;

        while (array_sum($remaining) > 0) {
            $batch = ['mcq' => 0, 'tf' => 0, 'fill' => 0, 'short' => 0, 'descriptive' => 0, 'match' => 0];
            $slots = self::BATCH_SIZE;

            foreach (['mcq', 'tf', 'fill', 'short', 'descriptive', 'match'] as $type) {
                if ($slots <= 0 || $remaining[$type] <= 0) {
                    continue;
                }
                $take = min($remaining[$type], $slots);
                $batch[$type] = $take;
                $remaining[$type] -= $take;
                $slots -= $take;
            }

            if (array_sum($batch) === 0) {
                break;
            }
            $batches[] = $batch;
        }

        return $batches;
    }

    /**
     * @param array{
     *   class_name?: string,
     *   subject_name?: string,
     *   topic_name?: string,
     *   topic_description?: string,
     *   extra_instructions?: string,
     * } $context
     * @param array{mcq: int, tf: int, fill: int, short: int, descriptive: int, match: int} $counts
     */
    public function buildPrompt(array $context, array $counts): string
    {
        $className = trim((string) ($context['class_name'] ?? ''));
        $subject   = trim((string) ($context['subject_name'] ?? ''));
        $topic     = trim((string) ($context['topic_name'] ?? ''));
        $desc      = trim((string) ($context['topic_description'] ?? ''));
        $extra     = trim((string) ($context['extra_instructions'] ?? ''));

        $lines = [];
        if ($counts['mcq'] > 0) {
            $lines[] = "- Exactly {$counts['mcq']} multiple-choice questions (type \"mcq\") with fields: question, option_a, option_b, option_c, option_d, correct_option (A/B/C/D). "
                . 'Exactly one correct option; distribute correct_option evenly across A, B, C, D.';
        }
        if ($counts['tf'] > 0) {
            $lines[] = "- Exactly {$counts['tf']} true/false questions (type \"tf\") with fields: question, answer_text (\"True\" or \"False\" only).";
        }
        if ($counts['fill'] > 0) {
            $lines[] = "- Exactly {$counts['fill']} fill-in-the-blank questions (type \"fill\") with fields: question (use ____ for blank), answer_text.";
        }
        if ($counts['short'] > 0) {
            $lines[] = "- Exactly {$counts['short']} short-answer questions (type \"short\") with fields: question, answer_text (brief phrase).";
        }
        if ($counts['descriptive'] > 0) {
            $lines[] = "- Exactly {$counts['descriptive']} descriptive questions (type \"descriptive\") with fields: question, answer_text. "
                . 'answer_text must be a model/guideline answer of 3 to 5 full sentences (about 4–8 lines when printed). '
                . 'These are for manual teacher marking; student answers will vary, but students may compare their work to this guideline.';
        }
        if ($counts['match'] > 0) {
            $lines[] = "- Exactly {$counts['match']} matching questions (type \"match\") with fields: question, match_pairs as array of {\"left\":\"...\",\"right\":\"...\"} (at least 3 pairs each).";
        }

        $typeBlock = implode("\n", $lines);

        $urduHint = '';
        if ($this->impliesUrdu($subject, $topic, $extra)) {
            $urduHint = "\n- Write Urdu questions in proper Urdu script where applicable.\n";
        }

        $islamiatHint = '';
        if ($this->impliesIslamiat($subject, $topic)) {
            $islamiatHint = "\n- For Islamiat: use only well-known, syllabus-safe facts. Do NOT invent Quran ayah numbers, hadith references, or controversial rulings.\n";
        }

        $ctx = '';
        if ($className !== '') {
            $ctx .= "Class/Grade: {$className}. ";
        }
        if ($subject !== '') {
            $ctx .= "Subject: {$subject}. ";
        }
        if ($topic !== '') {
            $ctx .= "Topic: {$topic}. ";
        }
        if ($desc !== '') {
            $ctx .= "Topic notes: {$desc}. ";
        }
        if ($extra !== '') {
            $ctx .= "Additional instructions: {$extra}. ";
        }

        return <<<PROMPT
You are a school question bank assistant for exams and quizzes.
{$ctx}
{$urduHint}{$islamiatHint}
Generate questions for the topic above. Requirements:
{$typeBlock}

Rules:
- All questions must be age-appropriate, factually correct, and aligned with the stated grade and subject.
- Do NOT include explanations or solutions.
- Return ONLY a valid JSON array of question objects (no markdown, no code fences, no comments).
- Each object MUST include "type" as one of: mcq, tf, fill, short, descriptive, match.
PROMPT;
    }

    private function impliesUrdu(string $subject, string $topic, string $extra): bool
    {
        $hay = mb_strtolower($subject . ' ' . $topic . ' ' . $extra);

        return str_contains($hay, 'urdu') || str_contains($hay, 'اردو');
    }

    private function impliesIslamiat(string $subject, string $topic): bool
    {
        $hay = mb_strtolower($subject . ' ' . $topic);

        return str_contains($hay, 'islamiat') || str_contains($hay, 'islamic') || str_contains($hay, 'islam');
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public function decodeJsonQuestions(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $clean = trim($text);
        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```[a-zA-Z0-9]*\s*/', '', $clean);
            $clean = preg_replace('/```\s*$/', '', $clean);
            $clean = trim($clean);
        }

        $data = json_decode($clean, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return null;
        }

        if (isset($data['questions']) && is_array($data['questions'])) {
            return $data['questions'];
        }

        if (array_is_list($data)) {
            return $data;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>|null
     */
    public function normalizeQuestionItem(array $item): ?array
    {
        $type = strtolower(trim((string) ($item['type'] ?? $item['question_type'] ?? '')));

        if ($type === '') {
            if (!empty($item['option_a']) || !empty($item['options'])) {
                $type = 'mcq';
            } elseif (isset($item['answer_text']) && in_array(strtolower((string) $item['answer_text']), ['true', 'false'], true)) {
                $type = 'tf';
            } elseif (!empty($item['match_pairs']) || !empty($item['pairs'])) {
                $type = 'match';
            } else {
                $type = 'fill';
            }
        }

        if (in_array($type, ['mcq_multi', 'multi', 'multiple'], true)) {
            $type = 'mcq_multi';
        }

        $question = trim((string) ($item['question'] ?? ''));
        if ($question === '') {
            return null;
        }

        $out = [
            'question_type'  => $type,
            'question'       => $question,
            'question_media' => 'text',
            'difficulty'     => 'normal',
        ];

        if ($type === 'mcq') {
            $opts = $item['options'] ?? [];
            $out['option_a'] = trim((string) ($item['option_a'] ?? $opts['A'] ?? $opts['a'] ?? ''));
            $out['option_b'] = trim((string) ($item['option_b'] ?? $opts['B'] ?? $opts['b'] ?? ''));
            $out['option_c'] = trim((string) ($item['option_c'] ?? $opts['C'] ?? $opts['c'] ?? ''));
            $out['option_d'] = trim((string) ($item['option_d'] ?? $opts['D'] ?? $opts['d'] ?? ''));
            $correct = strtoupper(trim((string) ($item['correct_option'] ?? 'A')));
            $out['correct_option'] = in_array($correct, ['A', 'B', 'C', 'D'], true) ? $correct : 'A';
            if ($out['option_a'] === '' && $out['option_b'] === '' && $out['option_c'] === '' && $out['option_d'] === '') {
                return null;
            }
        } elseif ($type === 'tf') {
            $ans = trim((string) ($item['answer_text'] ?? ''));
            $out['answer_text'] = strtolower($ans) === 'true' ? 'True' : 'False';
        } elseif ($type === 'fill' || $type === 'short') {
            $out['answer_text'] = trim((string) ($item['answer_text'] ?? ''));
            if ($out['answer_text'] === '') {
                return null;
            }
        } elseif ($type === 'descriptive') {
            $out['answer_text'] = trim((string) ($item['answer_text'] ?? $item['model_answer'] ?? ''));
            if ($out['answer_text'] === '' || mb_strlen($out['answer_text']) < 40) {
                return null;
            }
        } elseif ($type === 'match') {
            $rawPairs = $item['match_pairs'] ?? $item['pairs'] ?? [];
            $pairs = [];
            if (is_array($rawPairs)) {
                foreach ($rawPairs as $p) {
                    if (is_array($p) && isset($p['left'], $p['right'])) {
                        $pairs[] = ['left' => trim((string) $p['left']), 'right' => trim((string) $p['right'])];
                    } elseif (is_array($p) && count($p) >= 2) {
                        $pairs[] = ['left' => trim((string) $p[0]), 'right' => trim((string) $p[1])];
                    }
                }
            }
            if (count($pairs) < 2) {
                return null;
            }
            $out['match_pairs'] = $pairs;
            $out['is_drag'] = '0';
        } else {
            return null;
        }

        return $out;
    }

    private function callGemini(string $prompt, string $model): array
    {
        $apiKey = getenv('google.api_key');
        if (!$apiKey) {
            return [null, 'Gemini not configured'];
        }

        $model = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $model) ?: 'gemini-2.5-pro';
        $url   = 'https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent?key=' . urlencode($apiKey);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [null, 'cURL: ' . $err];
        }

        $decoded = json_decode((string) $response, true);
        if (isset($decoded['error'])) {
            return [null, $response];
        }

        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return [$text, $response];
    }

    private function summarizeApiError(?string $raw): string
    {
        if ($raw === null || $raw === '') {
            return 'Empty API response';
        }
        $decoded = json_decode($raw, true);
        if (isset($decoded['error']['message'])) {
            return (string) $decoded['error']['message'];
        }

        return 'API request failed';
    }
}
