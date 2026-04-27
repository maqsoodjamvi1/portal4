<?php

namespace App\Controllers;

use App\Controllers\PublicBaseController;

class PublicQuiz extends PublicBaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * List LIVE public quizzes
     * URL: /public/quizzes
     */
    public function index()
    {
        $now = date('Y-m-d H:i:s');

        $rows = $this->db->table('quizzes q')
            ->select('
                q.quiz_id,
                q.title,
                
                q.start_at,
                q.end_at,
                q.time_limit_sec,
                a.subject_name
            ')
            ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'left')
            ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
            ->where('q.is_published', 1)
            ->where('q.is_public', 1)  // ← make sure this column exists (TINYINT(1) default 0)
            ->orderBy('q.quiz_id', 'DESC')
            ->get()
            ->getResultArray();

        $liveQuizzes = [];

        foreach ($rows as $row) {
            $startAt = $row['start_at'] ?? null;
            $endAt   = $row['end_at']   ?? null;

            $hasStart = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
            $hasEnd   = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';

            // "Forever" rule: start_at == end_at  → always available
            $isForever = $hasStart && $hasEnd && ($startAt === $endAt);

            $isLive = false;

            if ($isForever) {
                $isLive = true;
            } else {
                if ($hasStart && $startAt > $now) {
                    $isLive = false;
                } elseif ($hasEnd && $endAt < $now) {
                    $isLive = false;
                } else {
                    $isLive = true;
                }
            }

            if ($isLive) {
                $liveQuizzes[] = $row;
            }
        }

        return view('frontend/quizzes/public_list', [
            'quizzes' => $liveQuizzes,
        ]);
    }

    /**
     * Public start:
     * - GET  → show nickname form
     * - POST → create anonymous attempt + show quiz
     *
     * URL: /public/quiz/{quizId}
     */
    public function start($quizId)
    {
        $quizId  = (int) $quizId;
        $request = $this->request;

        if ($quizId <= 0) {
            return redirect()->to(base_url('public/quizzes'))
                ->with('error', 'Invalid quiz.');
        }

        // --- Load quiz ---
        $quiz = $this->db->table('quizzes')
            ->where('quiz_id', $quizId)
            ->get()
            ->getRow();

        if (!$quiz || !$quiz->is_published || !$quiz->is_public) {
            return redirect()->to(base_url('public/quizzes'))
                ->with('error', 'This quiz is not available for public access.');
        }

        // --- Availability rules (same "forever" logic) ---
        $now     = date('Y-m-d H:i:s');
        $startAt = $quiz->start_at ?? null;
        $endAt   = $quiz->end_at   ?? null;

        $hasStart  = !empty($startAt) && $startAt !== '0000-00-00 00:00:00';
        $hasEnd    = !empty($endAt)   && $endAt   !== '0000-00-00 00:00:00';
        $isForever = $hasStart && $hasEnd && ($startAt === $endAt);

        if (!$isForever) {
            if ($hasStart && $startAt > $now) {
                return redirect()->to(base_url('public/quizzes'))
                    ->with('error', 'Quiz has not started yet.');
            }
            if ($hasEnd && $endAt < $now) {
                return redirect()->to(base_url('public/quizzes'))
                    ->with('error', 'Quiz has ended.');
            }
        }

        // ========== GET → Show nickname form ==========
        if ($request->getMethod() === 'get') {
            return view('frontend/quizzes/public_start', [
                'quiz'          => $quiz,
                'publicQuizUrl' => site_url('public/quiz/' . $quizId),
            ]);
        }

        // ========== POST → validate nickname + create attempt ==========
        $publicName = trim((string) $request->getPost('public_name'));

        if ($publicName === '') {
            return redirect()->back()->withInput()->with('error', 'Please enter your name or nickname.');
        }

        $clientIp = (string) $request->getIPAddress();

        // Limit attempts per IP (using your existing quiz_attempts structure)
        $prevCount = $this->db->table('quiz_attempts')
            ->where([
                'quiz_id'    => $quizId,
                'student_id' => 0,           // 0 = public user
                'client_ip'  => $clientIp,
            ])
            ->countAllResults();

        $attemptNo = $prevCount + 1;

        if ((int) $quiz->max_attempts > 0 && $attemptNo > (int) $quiz->max_attempts) {
            return redirect()->to(base_url('public/quizzes'))
                ->with('error', 'You have reached the maximum number of attempts for this quiz.');
        }

        // Store the nickname inside score_board as simple JSON (optional)
        $meta = json_encode([
            'name' => $publicName,
        ]);

        // Insert attempt (anonymous)
        $this->db->table('quiz_attempts')->insert([
            'quiz_id'      => $quizId,
            'student_id'   => 0,
            'client_ip'    => $clientIp,
            'attempt_no'   => $attemptNo,
            'started_at'   => $now,
            'submitted_at' => null,
            'duration_sec' => 0,
            'score_board'  => $meta,  // ← stores the nickname for leaderboard / later use
            'status'       => 'in_progress',
        ]);

        $attemptId = (int) $this->db->insertID();

        // --- Load questions (same as student flow, simple version) ---
        $questions = $this->db->table('quiz_questions qq')
            ->select('
                qq.question_id,
                qq.order_index,
                qq.marks,
                q.question_type,
                q.question,
                q.correct_option,
                q.option_a,
                q.option_b,
                q.option_c,
                q.option_d,
                q.options_json,
                q.is_drag
            ')
            ->join('qb_questions q', 'q.id = qq.question_id', 'left')
            ->where('qq.quiz_id', $quizId)
            ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false)
            ->get()
            ->getResult();

        if (empty($questions)) {
            return redirect()->to(base_url('public/quizzes'))
                ->with('error', 'This quiz has no questions.');
        }

        // Re-index display order
        $order = 1;
        foreach ($questions as $q) {
            $q->order_index = $order++;
        }

        // Persist questions for this attempt
        $batch = [];
        foreach ($questions as $q) {
            $batch[] = [
                'attempt_id'    => $attemptId,
                'quiz_id'       => $quizId,
                'question_id'   => (int) $q->question_id,
                'display_order' => (int) $q->order_index,
                'marks'         => (float) ($q->marks ?? $quiz->per_question_marks ?? 1),
                'question_type' => (string) $q->question_type,
            ];
        }
        if (!empty($batch)) {
            $this->db->table('quiz_attempt_questions')->insertBatch($batch);
        }

        // Optional: topic chips (for header)
        $topicRows = $this->db->table('qb_topics')
            ->select('qb_topics.topic_name')
            ->join('quiz_topics', 'quiz_topics.topic_id = qb_topics.id', 'inner')
            ->where('quiz_topics.quiz_id', $quizId)
            ->orderBy('qb_topics.topic_name', 'ASC')
            ->get()
            ->getResultArray();

        $topicList    = array_column($topicRows, 'topic_name');
        $timeLimitSec = (int) ($quiz->time_limit_sec ?? 0);

        // Re-use same quiz template used for students
        return view('frontend/quizzes/template1', [
            'quiz'           => $quiz,
            'timeLimitSec'   => $timeLimitSec,
            'attemptId'      => $attemptId,
            'qq'             => $questions,
            'totalQuestions' => count($questions),
            'topicList'      => $topicList,
            'publicName'     => $publicName, // if you want to show it in header
        ]);
    }

    /**
     * Simple public leaderboard example (optional)
     * Interprets score_board as numeric or JSON – you can adjust later.
     */
    public function leaderboard($quizId)
    {
        $quizId = (int) $quizId;
        if ($quizId <= 0) {
            return redirect()->to(base_url('public/quizzes'))
                ->with('error', 'Invalid quiz.');
        }

        $quiz = $this->db->table('quizzes')
            ->select('quiz_id, title')
            ->where('quiz_id', $quizId)
            ->get()
            ->getRow();

        if (!$quiz) {
            return redirect()->to(base_url('public/quizzes'))
                ->with('error', 'Quiz not found.');
        }

        $rows = $this->db->table('quiz_attempts')
            ->select('score_board, started_at, submitted_at, client_ip')
            ->where('quiz_id', $quizId)
            ->where('student_id', 0)
            ->where('status', 'submitted')
            ->orderBy('score_board', 'DESC') // adjust if score_board is JSON later
            ->limit(50)
            ->get()
            ->getResult();

        // You can decode score_board JSON in the view if needed

        return view('frontend/quizzes/public_leaderboard', [
            'quiz' => $quiz,
            'rows' => $rows,
        ]);
    }
}
