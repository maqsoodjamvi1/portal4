<?php

namespace App\Libraries;

class AdaptiveQuizService
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function isAdaptiveQuiz(object $quiz): bool
    {
        return (int) ($quiz->is_adaptive ?? 0) === 1;
    }

    public function tableHasColumn(string $table, string $column): bool
    {
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $table) || ! preg_match('/^[a-z][a-z0-9_]*$/', $column)) {
            return false;
        }

        return $this->db->fieldExists($column, $table);
    }

    /**
     * @return list<object>
     */
    public function getLevels(int $quizId): array
    {
        if (! $this->db->tableExists('quiz_levels')) {
            return [];
        }

        $builder = $this->db->table('quiz_levels')
            ->where('quiz_id', $quizId)
            ->orderBy('level_no', 'ASC');

        if ($this->tableHasColumn('quiz_levels', 'is_active')) {
            $builder->where('is_active', 1);
        }

        return $builder->get()->getResult();
    }

    public function getLevel(int $levelId): ?object
    {
        if ($levelId <= 0 || ! $this->db->tableExists('quiz_levels')) {
            return null;
        }

        return $this->db->table('quiz_levels')
            ->where('level_id', $levelId)
            ->get()
            ->getRow();
    }

    public function getHighestPassedLevelNo(int $studentId, int $quizId): int
    {
        if (! $this->db->tableExists('student_quiz_levels')) {
            return 0;
        }

        $row = $this->db->table('student_quiz_levels sql')
            ->select('MAX(ql.level_no) AS max_no')
            ->join('quiz_levels ql', 'ql.level_id = sql.level_id', 'inner')
            ->where([
                'sql.student_id' => $studentId,
                'sql.quiz_id'    => $quizId,
                'sql.passed'     => 1,
            ])
            ->get()
            ->getRow();

        return (int) ($row->max_no ?? 0);
    }

    public function hasNextLevel(int $quizId, int $studentId): bool
    {
        return $this->resolveNextLevel($quizId, $studentId) !== null;
    }

    public function resolveNextLevel(int $quizId, int $studentId): ?object
    {
        $levels = $this->getLevels($quizId);
        if ($levels === []) {
            return null;
        }

        $passedNo = $this->getHighestPassedLevelNo($studentId, $quizId);

        foreach ($levels as $lvl) {
            if ((int) $lvl->level_no > $passedNo) {
                return $lvl;
            }
        }

        return null;
    }

    protected function sessionLevelKey(int $attemptId): string
    {
        return 'adaptive_attempt_level_' . $attemptId;
    }

    protected function readAttemptLevelId(object $attempt): int
    {
        $attemptId = (int) $attempt->attempt_id;
        if ($this->tableHasColumn('quiz_attempts', 'level_id')) {
            $levelId = (int) ($attempt->level_id ?? 0);
            if ($levelId > 0) {
                return $levelId;
            }
        }

        return (int) (session()->get($this->sessionLevelKey($attemptId)) ?? 0);
    }

    protected function writeAttemptLevelId(int $attemptId, int $levelId): void
    {
        session()->set($this->sessionLevelKey($attemptId), $levelId);

        if ($levelId > 0 && $this->tableHasColumn('quiz_attempts', 'level_id')) {
            $this->db->table('quiz_attempts')
                ->where('attempt_id', $attemptId)
                ->update(['level_id' => $levelId]);
        }
    }

    /**
     * Attach the correct adaptive level to an in-progress attempt (no HTTP redirects).
     */
    public function attachLevelToAttempt(object $quiz, int $studentId, int $attemptId): ?object
    {
        $quizId = (int) $quiz->quiz_id;
        $levels = $this->getLevels($quizId);
        if ($levels === []) {
            return null;
        }

        $attempt = $this->db->table('quiz_attempts')
            ->where('attempt_id', $attemptId)
            ->get()
            ->getRow();

        if (! $attempt) {
            return null;
        }

        $target = $this->resolveNextLevel($quizId, $studentId);
        if ($target === null) {
            return null;
        }

        $levelId  = (int) $target->level_id;
        $passedNo = $this->getHighestPassedLevelNo($studentId, $quizId);

        $currentLevelId = $this->readAttemptLevelId($attempt);
        if ($currentLevelId > 0) {
            $currentLvl = $this->getLevel($currentLevelId);
            if ($currentLvl && (int) $currentLvl->level_no > $passedNo) {
                $levelId = $currentLevelId;
            }
        }

        $previousLevelId = $this->readAttemptLevelId($attempt);
        if ($levelId > 0 && $previousLevelId !== $levelId) {
            $this->db->table('quiz_attempt_questions')->where('attempt_id', $attemptId)->delete();
        }
        if ($levelId > 0) {
            $this->writeAttemptLevelId($attemptId, $levelId);
        }

        return $this->getLevel($levelId);
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public function gradeAttempt(int $attemptId, object $quiz, array $optmapPost = []): array
    {
        $rows = $this->db->table('quiz_attempt_questions qa')
            ->select('qa.question_id, qa.marks, q.*')
            ->join('qb_questions q', 'q.id = qa.question_id', 'left')
            ->where('qa.attempt_id', $attemptId)
            ->orderBy('qa.display_order', 'ASC')
            ->get()
            ->getResult();

        $answers = $this->db->table('quiz_attempt_answers')
            ->where('attempt_id', $attemptId)
            ->get()
            ->getResult();

        $ansByQ = [];
        foreach ($answers as $a) {
            $ansByQ[(int) $a->question_id] = $a;
        }

        $score    = 0.0;
        $maxMarks = 0.0;
        $negative = (float) ($quiz->negative_mark_per_q ?? 0);

        foreach ($rows as $row) {
            $maxMarks += (float) ($row->marks ?? 1);
            $qid     = (int) $row->question_id;
            $given   = $ansByQ[$qid] ?? null;
            $qType   = $row->question_type ?? 'mcq_single';
            $mapForQ = isset($optmapPost[$qid]) && is_array($optmapPost[$qid]) ? $optmapPost[$qid] : [];

            $awarded = $this->scoreOneQuestion($row, $given, $qType, $quiz, $mapForQ, $negative);
            $score  += $awarded;

            if ($given) {
                $this->db->table('quiz_attempt_answers')
                    ->where(['attempt_id' => $attemptId, 'question_id' => $qid])
                    ->update([
                        'is_correct'    => $awarded > 0 ? 1 : 0,
                        'marks_awarded' => $awarded,
                    ]);
            }
        }

        $score      = max(0, $score);
        $percentage = $maxMarks > 0 ? round(($score / $maxMarks) * 100, 1) : 0.0;

        return [$score, $maxMarks, $percentage];
    }

    protected function scoreOneQuestion(object $q, $given, string $qType, object $quiz, array $mapForQ, float $negativePerQ): float
    {
        $marks = (float) ($q->marks ?? 1);

        switch ($qType) {
            case 'mcq':
            case 'mcq_single':
                if ($given && $given->selected_option !== null && $given->selected_option !== '') {
                    $selectedNew  = strtoupper((string) $given->selected_option);
                    $selectedOrig = $selectedNew;
                    if (! empty($quiz->shuffle_options) && (int) $quiz->shuffle_options === 1 && ! empty($mapForQ) && isset($mapForQ[$selectedNew])) {
                        $selectedOrig = strtoupper((string) $mapForQ[$selectedNew]);
                    }

                    return strtoupper(trim((string) $q->correct_option)) === $selectedOrig ? $marks : (0 - $negativePerQ);
                }

                return 0.0;

            case 'tf':
            case 'true_false':
                if ($given && $given->answer_text !== null && $given->answer_text !== '') {
                    return strcasecmp(trim((string) $given->answer_text), trim((string) $q->answer_text)) === 0
                        ? $marks : (0 - $negativePerQ);
                }

                return 0.0;

            case 'fill':
            case 'fill_blank':
                if ($given && $given->answer_text !== null && $given->answer_text !== '') {
                    return strcasecmp(trim((string) $given->answer_text), trim((string) $q->answer_text)) === 0
                        ? $marks : (0 - $negativePerQ);
                }

                return 0.0;

            case 'mcq_multi':
                $json    = json_decode($q->options_json ?? '[]', true);
                $correct = [];
                if (! empty($json['correct_multi']) && is_array($json['correct_multi'])) {
                    $correct = array_map('strtoupper', $json['correct_multi']);
                }
                $selectedNewArr = $given ? (array) json_decode($given->selected_options ?? '[]', true) : [];
                $selectedOrigArr = [];
                foreach ($selectedNewArr as $letterNew) {
                    $letterNew  = strtoupper((string) $letterNew);
                    $letterOrig = $letterNew;
                    if (! empty($quiz->shuffle_options) && ! empty($mapForQ) && isset($mapForQ[$letterNew])) {
                        $letterOrig = strtoupper((string) $mapForQ[$letterNew]);
                    }
                    $selectedOrigArr[] = $letterOrig;
                }
                $selectedOrigArr = array_values(array_unique($selectedOrigArr));
                $correct         = array_values(array_unique($correct));
                sort($correct);
                sort($selectedOrigArr);

                return $correct === $selectedOrigArr ? $marks : 0.0;

            case 'match':
                if ($given && ! empty($q->options_json)) {
                    $correctPairs = json_decode($q->options_json, true);
                    $givenPairs   = json_decode($given->answer_text ?? '[]', true);
                    if (! is_array($correctPairs)) {
                        $correctPairs = [];
                    }
                    if (! is_array($givenPairs)) {
                        $givenPairs = [];
                    }
                    $correctMap = [];
                    foreach ($correctPairs as $p) {
                        $l = isset($p['left']) ? trim(mb_strtolower($p['left'])) : '';
                        $r = isset($p['right']) ? trim(mb_strtolower($p['right'])) : '';
                        if ($l !== '') {
                            $correctMap[$l] = $r;
                        }
                    }
                    $givenMap = [];
                    foreach ($givenPairs as $p) {
                        $l = isset($p['left']) ? trim(mb_strtolower($p['left'])) : '';
                        $v = isset($p['value']) ? trim(mb_strtolower($p['value'])) : '';
                        if ($l !== '') {
                            $givenMap[$l] = $v;
                        }
                    }
                    if (! empty($correctMap)) {
                        $allCorrect = true;
                        foreach ($correctMap as $l => $rRight) {
                            if (! array_key_exists($l, $givenMap) || $givenMap[$l] !== $rRight) {
                                $allCorrect = false;
                                break;
                            }
                        }
                        if ($allCorrect) {
                            foreach ($givenMap as $l => $_v) {
                                if (! array_key_exists($l, $correctMap)) {
                                    $allCorrect = false;
                                    break;
                                }
                            }
                        }

                        return $allCorrect ? $marks : (0 - $negativePerQ);
                    }
                }

                return 0.0;

            default:
                return 0.0;
        }
    }

    public function getPassingPercentage(object $level): float
    {
        if (isset($level->passing_percentage)) {
            return (float) $level->passing_percentage;
        }
        if (isset($level->min_pass_percentage)) {
            return (float) $level->min_pass_percentage;
        }

        return 60.0;
    }

    public function levelLabel(object $level): string
    {
        if (! empty($level->level_name)) {
            return (string) $level->level_name;
        }

        return 'Level ' . (int) ($level->level_no ?? 1);
    }

    /**
     * @return array<string, mixed>
     */
    public function finalizeLevelAttempt(object $quiz, object $attempt, object $level, array $optmapPost = []): array
    {
        [$score, $maxMarks, $percentage] = $this->gradeAttempt(
            (int) $attempt->attempt_id,
            $quiz,
            $optmapPost
        );

        $minPass = $this->getPassingPercentage($level);
        $passed  = $percentage >= $minPass;

        if (class_exists(AiQuizEngine::class)) {
            try {
                $engine   = new AiQuizEngine();
                $aiResult = $engine->evaluateLevel(
                    (int) $attempt->student_id,
                    (int) $attempt->quiz_id,
                    (int) $level->level_id,
                    (int) $attempt->attempt_id
                );
                if (! $passed && in_array($aiResult['decision'] ?? '', ['ADVANCE', 'ADVANCE_FAST'], true)) {
                    $passed = true;
                }
            } catch (\Throwable $e) {
                log_message('error', 'AdaptiveQuizService AI: ' . $e->getMessage());
            }
        }

        $this->db->table('quiz_attempts')
            ->where('attempt_id', (int) $attempt->attempt_id)
            ->update([
                'submitted_at'       => date('Y-m-d H:i:s'),
                'status'             => 'submitted',
                'score_obtained'     => $score,
                'active_attempt_key' => null,
            ]);

        if ($this->db->tableExists('student_quiz_levels')) {
            $this->db->table('student_quiz_levels')->insert([
                'student_id'   => (int) $attempt->student_id,
                'quiz_id'      => (int) $attempt->quiz_id,
                'level_id'     => (int) $level->level_id,
                'attempt_no'   => (int) ($attempt->attempt_no ?? 1),
                'raw_score'    => $score,
                'ai_score'     => $percentage,
                'decision'     => $passed ? 'ADVANCE' : 'REPEAT_SAME',
                'passed'       => $passed ? 1 : 0,
                'started_at'   => $attempt->started_at ?? null,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $levels    = $this->getLevels((int) $quiz->quiz_id);
        $currentNo = (int) ($level->level_no ?? 1);
        $nextLevel = null;

        foreach ($levels as $lvl) {
            if ((int) $lvl->level_no > $currentNo) {
                $nextLevel = $lvl;
                break;
            }
        }

        return [
            'passed'           => $passed,
            'percentage'       => $percentage,
            'min_pass'         => $minPass,
            'score'            => $score,
            'max_marks'        => $maxMarks,
            'has_next_level'   => $passed && $nextLevel !== null,
            'is_final_level'   => $passed && $nextLevel === null,
            'next_level_id'    => $nextLevel ? (int) $nextLevel->level_id : null,
            'next_level_no'    => $nextLevel ? (int) $nextLevel->level_no : null,
            'current_level_no' => $currentNo,
            'total_levels'     => count($levels),
            'level_label'      => $this->levelLabel($level),
        ];
    }

    public function createLevelAttempt(int $quizId, int $studentId, int $levelId, string $activeKey, string $clientIp): int
    {
        $data = [
            'quiz_id'            => $quizId,
            'student_id'         => $studentId,
            'attempt_no'         => $this->nextAttemptNo($quizId, $studentId),
            'started_at'         => date('Y-m-d H:i:s'),
            'status'             => 'in_progress',
            'client_ip'          => $clientIp,
            'active_attempt_key' => $activeKey,
        ];

        if ($this->tableHasColumn('quiz_attempts', 'level_id')) {
            $data['level_id'] = $levelId;
        }

        $this->db->table('quiz_attempts')->insert($data);

        return (int) $this->db->insertID();
    }

    protected function nextAttemptNo(int $quizId, int $studentId): int
    {
        $max = (int) $this->db->table('quiz_attempts')
            ->selectMax('attempt_no')
            ->where(['quiz_id' => $quizId, 'student_id' => $studentId])
            ->get()
            ->getRow()
            ->attempt_no;

        return $max + 1;
    }

    /**
     * How many questions a student receives for one adaptive level (matches assignQuestionsForLevel).
     */
    public function resolvePerLevelQuestionCap(int $quizId, int $levelId, object $quiz): int
    {
        $bankCount = 0;
        if ($this->tableHasColumn('quiz_questions', 'level_id')) {
            $bankCount = (int) $this->db->table('quiz_questions')
                ->where(['quiz_id' => $quizId, 'level_id' => $levelId])
                ->countAllResults();
        }

        $perLevelLimit = $this->computePerLevelQuestionLimit($quizId, $levelId, $quiz);

        if ($perLevelLimit <= 0) {
            return $bankCount;
        }

        if ($bankCount > 0) {
            return min($perLevelLimit, $bankCount);
        }

        return $perLevelLimit;
    }

    /**
     * Configured cap for one level before trimming to bank size.
     */
    private function computePerLevelQuestionLimit(int $quizId, int $levelId, object $quiz): int
    {
        $levelRow = $this->getLevel($levelId);
        $levelCap = 0;
        if ($levelRow && isset($levelRow->questions_count)) {
            $levelCap = (int) $levelRow->questions_count;
        }

        $perLevelLimit = $levelCap > 0 ? $levelCap : 0;
        if ($perLevelLimit <= 0 && $this->isAdaptiveQuiz($quiz)) {
            $levelCount = count($this->getLevels($quizId));
            $quizTotal  = (int) ($quiz->questions_count ?? 0);
            if ($quizTotal > 0 && $levelCount > 0) {
                $perLevelLimit = (int) ceil($quizTotal / $levelCount);
            }
        }

        return $perLevelLimit;
    }

    public function assignQuestionsForLevel(int $attemptId, int $quizId, int $levelId, object $quiz): int
    {
        $existing = $this->db->table('quiz_attempt_questions')
            ->where('attempt_id', $attemptId)
            ->countAllResults();

        if ($existing > 0) {
            return $existing;
        }

        $builder = $this->db->table('quiz_questions qq')
            ->select('qq.question_id, qq.order_index, q.question_type')
            ->join('qb_questions q', 'q.id = qq.question_id', 'left')
            ->where('qq.quiz_id', $quizId);

        if ($this->tableHasColumn('quiz_questions', 'level_id')) {
            $builder->where('qq.level_id', $levelId);
        }

        $questions = $builder
            ->orderBy('qq.order_index IS NULL, qq.order_index ASC', '', false)
            ->get()
            ->getResult();

        if ($questions === []) {
            return 0;
        }

        $perLevelLimit = $this->computePerLevelQuestionLimit($quizId, $levelId, $quiz);

        if ($perLevelLimit > 0 && count($questions) > $perLevelLimit) {
            if (! empty($quiz->shuffle_questions) && (int) $quiz->shuffle_questions === 1) {
                shuffle($questions);
            }
            $questions = array_slice($questions, 0, $perLevelLimit);
        } elseif (! empty($quiz->shuffle_questions) && (int) $quiz->shuffle_questions === 1) {
            shuffle($questions);
        }

        $batch = [];
        $order = 1;
        foreach ($questions as $q) {
            $batch[] = [
                'attempt_id'    => $attemptId,
                'quiz_id'       => $quizId,
                'question_id'   => (int) $q->question_id,
                'display_order' => $order++,
                'marks'         => (float) ($quiz->per_question_marks ?? 1),
                'question_type' => $q->question_type ?? 'mcq_single',
            ];
        }

        $this->db->table('quiz_attempt_questions')->insertBatch($batch);

        return count($batch);
    }
}
