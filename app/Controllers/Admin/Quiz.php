<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Quiz extends BaseController
{
    public function index()
    {
        return view('admin/quiz_builder');
    }

    /**
     * AJAX: get AI MCQs from ChatGPT / DeepSeek
     */

    public function generate()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX only']);
    }

    $prompt   = $this->request->getPost('prompt') ?? '';
    $mcqCount = (int) ($this->request->getPost('mcq_count') ?? 5);

    if ($prompt === '') {
        return $this->response->setJSON(['error' => 'Prompt is required']);
    }

    $fullPrompt = "You are a quiz generator. Generate exactly {$mcqCount} multiple choice questions in JSON ONLY.
Every item must have: question, option_a, option_b, option_c, option_d, correct_option (A/B/C/D).
Topic: {$prompt}
Return JSON array ONLY.";

    // 👇 call AI
    [$aiJson, $rawResp] = $this->callOpenAIWithDebug($fullPrompt);

    if (!$aiJson) {
        // return raw response so we can see the error
        return $this->response->setJSON([
            'error'    => 'AI did not respond',
            'raw_resp' => $rawResp,      // check this in browser console
        ]);
    }

    // try decode
    $data = json_decode($aiJson, true);
    if (!is_array($data)) {
        $clean = $this->extractJson($aiJson);
        $data  = json_decode($clean, true);
    }

    if (!is_array($data)) {
        return $this->response->setJSON([
            'error'    => 'AI returned invalid JSON',
            'raw_resp' => $aiJson,
        ]);
    }

    return $this->response->setJSON([
        'mcqs' => $data,
    ]);
}

private function callOpenAIWithDebug(string $prompt): array
{
    $apiKey = getenv('openai.api_key');
    if (!$apiKey) {
        return [null, 'Missing openai.api_key in .env'];
    }

    $url = 'https://api.openai.com/v1/chat/completions';

    $payload = [
        'model' => 'gpt-4o-mini',  // change if your account doesn’t have it
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
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return [null, 'cURL error: ' . $curlErr];
    }

    // if API returned error JSON
    $decoded = json_decode($response, true);
    if (isset($decoded['error'])) {
        return [null, $response];
    }

    // normal
    $content = $decoded['choices'][0]['message']['content'] ?? null;
    return [$content, $response];
}

//     public function generate()
//     {
//         if (!$this->request->isAJAX()) {
//             return $this->response->setStatusCode(400)->setJSON(['error' => 'AJAX only']);
//         }

//         $prompt   = $this->request->getPost('prompt') ?? '';
//         $mcqCount = (int) ($this->request->getPost('mcq_count') ?? 5);

//         if ($prompt === '') {
//             return $this->response->setJSON(['error' => 'Prompt is required']);
//         }

//         // 1) Build a strict prompt so AI returns JSON
//         $fullPrompt = "You are a quiz generator. Generate exactly {$mcqCount} multiple choice questions in JSON ONLY. 
// Every item must have: question, option_a, option_b, option_c, option_d, correct_option (A/B/C/D).
// Topic: {$prompt}
// Return JSON like:
// [
//   {
//     \"question\": \"...\",
//     \"option_a\": \"...\",
//     \"option_b\": \"...\",
//     \"option_c\": \"...\",
//     \"option_d\": \"...\",
//     \"correct_option\": \"A\"
//   }
// ]";

//         // 2) Call AI (ChatGPT or DeepSeek)
//         // ? You must set your key in .env
//         // openai.api_key=sk-xxxxxx
//         $aiJson = $this->callOpenAI($fullPrompt);    // or $this->callDeepSeek($fullPrompt);

//         if (!$aiJson) {
//             return $this->response->setJSON(['error' => 'AI did not respond']);
//         }

//         // try to decode
//         $data = json_decode($aiJson, true);

//         if (!is_array($data)) {
//             // sometimes model wraps json in text, try to clean
//             $clean = $this->extractJson($aiJson);
//             $data  = json_decode($clean, true);
//         }

//         if (!is_array($data)) {
//             return $this->response->setJSON(['error' => 'AI returned invalid JSON', 'raw' => $aiJson]);
//         }

//         // return to frontend
//         return $this->response->setJSON([
//             'mcqs' => $data,
//         ]);
//     }

    /**
     * Save quiz to DB (you said you'll save yourself)
     */
    public function save()
    {
        // here you will loop on posted arrays and insert
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

        return redirect()->to('admin/quiz')->with('msg', 'Quiz saved');
    }

    /**
     * Call OpenAI / ChatGPT
     */
    private function callOpenAI(string $prompt): ?string
    {
        $apiKey = getenv('openai.api_key'); // put in .env

        if (!$apiKey) {
            return null;
        }

        $url = 'https://api.openai.com/v1/chat/completions';

        $payload = [
            'model'    => 'gpt-4o-mini', // or gpt-4o, or whatever you have
            'messages' => [
                ['role' => 'system', 'content' => 'You output JSON only.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'temperature' => 0.5,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return null;
        }

        $decoded = json_decode($response, true);
        return $decoded['choices'][0]['message']['content'] ?? null;
    }

    /**
     * If AI returns: "Here is JSON: ```json ...```"
     */
    private function extractJson(string $text): string
    {
        if (preg_match('/\{.*\}|\[.*\]/s', $text, $m)) {
            return $m[0];
        }
        return $text;
    }
}
