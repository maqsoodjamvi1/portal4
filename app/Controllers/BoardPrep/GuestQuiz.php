<?php

namespace App\Controllers\BoardPrep;

/**
 * Public guest quiz player for liveeducationquiz.com.
 *
 * Guests can play any published board-prep quiz without logging in.
 * Nothing is stored — the score is computed statelessly on submit and the
 * result screen invites the guest to sign up to save and track results.
 */
class GuestQuiz extends BoardPrepBaseController
{
    /** Render the guest quiz player (questions only, no correct answers exposed). */
    public function play(int $quizId)
    {
        $quizId = (int) $quizId;
        $quiz   = $this->loadPublishedQuiz($quizId);
        if (! $quiz) {
            return redirect()->to(board_prep_url('dashboard'))->with('error', 'Quiz not available.');
        }

        $questions = $this->loadQuestions($quizId);
        if ($questions === []) {
            return redirect()->to(board_prep_url('dashboard'))->with('error', 'This quiz has no questions yet.');
        }

        // Strip correct answers before sending to the browser.
        $publicQuestions = [];
        foreach ($questions as $i => $q) {
            $publicQuestions[] = [
                'n'        => $i + 1,
                'id'       => (int) $q['id'],
                'question' => (string) $q['question'],
                'options'  => array_map(static fn ($o) => ['key' => $o['key'], 'text' => $o['text']], $q['options']),
            ];
        }

        return view('board_prep/quizzes/guest_play', [
            'productName' => $this->boardPrepConfig()->productName,
            'quiz'        => $quiz,
            'questions'   => $publicQuestions,
            'timeLimit'   => (int) ($quiz->time_limit_sec ?? 0),
            'signupUrl'   => board_prep_url('signup'),
            'loginUrl'    => board_prep_url('login'),
            'dashboardUrl'=> board_prep_url('dashboard'),
            'scoreUrl'    => board_prep_url('quizzes/guest/score'),
        ]);
    }

    /** Stateless scoring — compute the score, store nothing, prompt signup. */
    public function score()
    {
        $quizId = (int) $this->request->getPost('quiz_id');
        $quiz   = $this->loadPublishedQuiz($quizId);
        if (! $quiz) {
            return redirect()->to(board_prep_url('dashboard'))->with('error', 'Quiz not available.');
        }

        $questions = $this->loadQuestions($quizId);
        if ($questions === []) {
            return redirect()->to(board_prep_url('dashboard'))->with('error', 'This quiz has no questions yet.');
        }

        $answers = (array) ($this->request->getPost('answers') ?? []);

        $correct = 0;
        $wrong   = 0;
        $blank   = 0;
        $review  = [];

        foreach ($questions as $i => $q) {
            $qid      = (int) $q['id'];
            $given    = strtoupper(trim((string) ($answers[$qid] ?? '')));
            $correctK = strtoupper(trim((string) $q['correct']));

            if ($given === '') {
                $blank++;
                $state = 'blank';
            } elseif ($correctK !== '' && $given === $correctK) {
                $correct++;
                $state = 'correct';
            } else {
                $wrong++;
                $state = 'wrong';
            }

            $review[] = [
                'n'        => $i + 1,
                'question' => (string) $q['question'],
                'options'  => $q['options'],
                'given'    => $given,
                'correct'  => $correctK,
                'state'    => $state,
            ];
        }

        $total   = count($questions);
        $percent = $total > 0 ? round(($correct / $total) * 100, 1) : 0.0;
        $showSol = (int) ($quiz->show_solution ?? 0) === 1;

        return view('board_prep/quizzes/guest_result', [
            'productName' => $this->boardPrepConfig()->productName,
            'quiz'        => $quiz,
            'total'       => $total,
            'correct'     => $correct,
            'wrong'       => $wrong,
            'blank'       => $blank,
            'percent'     => $percent,
            'review'      => $review,
            'showSolution'=> $showSol,
            'signupUrl'   => board_prep_url('signup'),
            'loginUrl'    => board_prep_url('login'),
            'dashboardUrl'=> board_prep_url('dashboard'),
            'replayUrl'   => board_prep_url('quizzes/guest/' . (int) $quizId),
        ]);
    }

    /** Load a published board-prep quiz or null. */
    private function loadPublishedQuiz(int $quizId)
    {
        if ($quizId <= 0 || ! $this->db->fieldExists('audience', 'quizzes')) {
            return null;
        }

        return $this->db->table('quizzes')
            ->where('quiz_id', $quizId)
            ->where('is_published', 1)
            ->whereIn('audience', ['board_prep', 'both'])
            ->get()
            ->getRow();
    }

    /**
     * Load quiz questions with normalized options + correct key.
     *
     * @return list<array{id:int,question:string,options:list<array{key:string,text:string}>,correct:string}>
     */
    private function loadQuestions(int $quizId): array
    {
        $rows = $this->db->table('quiz_questions qq')
            ->select('qq.question_id, qq.order_index,
                      q.question, q.question_type, q.correct_option, q.options_json,
                      q.option_a, q.option_b, q.option_c, q.option_d')
            ->join('qb_questions q', 'q.id = qq.question_id', 'left')
            ->where('qq.quiz_id', $quizId)
            ->orderBy('qq.order_index IS NULL, qq.order_index ASC, qq.question_id ASC', '', false)
            ->get()
            ->getResult();

        $out = [];
        foreach ($rows as $r) {
            $options = $this->buildOptions($r);
            if ($options === []) {
                continue; // skip non-MCQ / malformed
            }
            $out[] = [
                'id'       => (int) $r->question_id,
                'question' => (string) ($r->question ?? ''),
                'options'  => $options,
                'correct'  => strtoupper(trim((string) ($r->correct_option ?? ''))),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{key:string,text:string}>
     */
    private function buildOptions(object $r): array
    {
        $opts = [];
        $map  = ['A' => $r->option_a ?? '', 'B' => $r->option_b ?? '', 'C' => $r->option_c ?? '', 'D' => $r->option_d ?? ''];
        foreach ($map as $key => $text) {
            $text = trim((string) $text);
            if ($text !== '') {
                $opts[] = ['key' => $key, 'text' => $text];
            }
        }

        if ($opts === [] && ! empty($r->options_json)) {
            $decoded = json_decode((string) $r->options_json, true);
            if (is_array($decoded)) {
                $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                $i = 0;
                foreach ($decoded as $val) {
                    $text = is_array($val) ? (string) ($val['text'] ?? $val['option'] ?? '') : (string) $val;
                    $text = trim($text);
                    if ($text !== '' && isset($letters[$i])) {
                        $opts[] = ['key' => $letters[$i], 'text' => $text];
                        $i++;
                    }
                }
            }
        }

        return $opts;
    }
}
