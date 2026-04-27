<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class QuizAi extends BaseController
{
    public function index()
    {
        // reuse same view as before, or make a new one
        return view('admin/quiz_ai');
    }

    /**
     * AJAX endpoint: try Gemini -> DeepSeek -> OpenRouter
     */
    public function generate()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX only']);
        }

        $prompt    = $this->request->getPost('prompt') ?? '';
        $mcqCount = (int) ($this->request->getPost('mcq_count') ?? 5);

        if ($prompt === '') {
            return $this->response->setJSON(['error' => 'Prompt is required']);
        }

        // the prompt we want ALL providers to follow
        $finalPrompt = "You are a quiz generator for a school management system.
Generate exactly {$mcqCount} multiple-choice questions (MCQs) in JSON array format.
Each item must have: question, option_a, option_b, option_c, option_d, correct_option (A/B/C/D).
Topic: {$prompt}
Return JSON ONLY.";

        $errors = [];

        // 1) try GEMINI
        [$text, $raw] = $this->callGemini($finalPrompt);
        if ($text) {
            $mcqs = $this->decodeJsonMcqs($text);
            if ($mcqs !== null) {
                return $this->response->setJSON(['mcqs' => $mcqs, 'provider' => 'gemini']);
            }
            $errors[] = 'Gemini returned invalid JSON';
        } else {
            if ($raw) {
                $errors[] = 'Gemini error: ' . $raw;
            }
        }

        // 2) try DEEPSEEK
        [$text, $raw] = $this->callDeepSeek($finalPrompt);
        if ($text) {
            $mcqs = $this->decodeJsonMcqs($text);
            if ($mcqs !== null) {
                return $this->response->setJSON(['mcqs' => $mcqs, 'provider' => 'deepseek']);
            }
            $errors[] = 'DeepSeek returned invalid JSON';
        } else {
            if ($raw) {
                $errors[] = 'DeepSeek error: ' . $raw;
            }
        }

        // 3) try OPENROUTER
        [$text, $raw] = $this->callOpenRouter($finalPrompt);
        if ($text) {
            $mcqs = $this->decodeJsonMcqs($text);
            if ($mcqs !== null) {
                return $this->response->setJSON(['mcqs' => $mcqs, 'provider' => 'openrouter']);
            }
            $errors[] = 'OpenRouter returned invalid JSON';
        } else {
            if ($raw) {
                $errors[] = 'OpenRouter error: ' . $raw;
            }
        }

        // if all 3 failed
        return $this->response->setJSON([
            'error' => 'All AI providers failed',
            'details' => $errors,
        ]);
    }

    /**
     * Save to DB
     */
    public function save()
    {
        $questions = $this->request->getPost('question') ?? [];
        $a         = $this->request->getPost('option_a') ?? [];
        $b         = $this->request->getPost('option_b') ?? [];
        $c         = $this->request->getPost('option_c') ?? [];
        $d         = $this->request->getPost('option_d') ?? [];
        $correct   = $this->request->getPost('correct_option') ?? [];

        $db = db_connect();
        foreach ($questions as $i => $q) {
            if (trim($q) === '') {
                continue;
            }
            $db->table('quizzes_questions')->insert([
                'question'       => $q,
                'option_a'       => $a[$i] ?? '',
                'option_b'       => $b[$i] ?? '',
                'option_c'       => $c[$i] ?? '',
                'option_d'       => $d[$i] ?? '',
                'correct_option' => $correct[$i] ?? 'A',
                'created_date'   => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('admin/quiz-ai')->with('msg', 'Quiz saved');
    }

    /* -----------------------------------------------------------------
     * PROVIDERS
     * ----------------------------------------------------------------- */

    private function callGemini(string $prompt): array
    {
        $apiKey = getenv('google.api_key');
        
        if (!$apiKey) {
            return [null, 'Missing google.api_key in .env'];
        }

        // FIX: The model name 'gemini-1.5-flash-latest' caused a 404 error.
        // We are updating it to the correct, stable v1 model identifier: gemini-2.5-flash
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);
        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return [null, 'cURL error: ' . $curlErr];
        }

        $decoded = json_decode($response, true);
        $text    = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return [$text, $response];
    }

    private function callDeepSeek(string $prompt): array
    {
        $apiKey  = getenv('deepseek.api_key');
        $baseUrl = getenv('deepseek.base_url') ?: 'https://api.deepseek.com/v1';

        if (!$apiKey) {
            return [null, 'Missing deepseek.api_key in .env'];
        }

        $url = rtrim($baseUrl, '/') . '/chat/completions';

        $payload = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'You output JSON only.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.5,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: ' . 'Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return [null, 'cURL error: ' . $curlErr];
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['error'])) {
            return [null, $response];
        }

        $content = $decoded['choices'][0]['message']['content'] ?? null;
        return [$content, $response];
    }

    private function callOpenRouter(string $prompt): array
    {
        $apiKey  = getenv('openrouter.api_key');
        $baseUrl = getenv('openrouter.base_url') ?: 'https://openrouter.ai/api/v1';

        if (!$apiKey) {
            return [null, 'Missing openrouter.api_key in .env'];
        }

        $url = rtrim($baseUrl, '/') . '/chat/completions';

        $payload = [
            // pick a free/sponsored model
            'model' => 'mistralai/mistral-7b-instruct',
            'messages' => [
                ['role' => 'system', 'content' => 'You output JSON only.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                // Optional but recommended by OpenRouter:
                'HTTP-Referer: https://yourdomain.com',
                'X-Title: ITDS Quiz Builder',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return [null, 'cURL error: ' . $curlErr];
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['error'])) {
            return [null, $response];
        }

        $content = $decoded['choices'][0]['message']['content'] ?? null;
        return [$content, $response];
    }

    /* -----------------------------------------------------------------
     * HELPERS
     * ----------------------------------------------------------------- */

    private function decodeJsonMcqs(string $text): ?array
    {
        // try plain JSON
        $data = json_decode($text, true);
        if (is_array($data)) {
            return $data;
        }

        // try to extract JSON from markdown
        $clean = $this->extractJson($text);
        $data  = json_decode($clean, true);
        if (is_array($data)) {
            return $data;
        }

        return null;
    }

    private function extractJson(string $text): string
    {
        // Extracts the first JSON object or array that matches {...} or [...]
        if (preg_match('/\{.*\}|\[.*\]/s', $text, $m)) {
            return $m[0];
        }
        return $text;
    }
}
