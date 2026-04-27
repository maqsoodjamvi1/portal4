<?php
namespace App\Libraries;

use App\Models\AiQuizModel;

class AiQuizEngine
{
    protected AiQuizModel $model;

    public function __construct()
    {
        $this->model = new AiQuizModel();
    }

    public function evaluateLevel(
        int $studentId,
        int $quizId,
        int $levelId,
        int $attemptId
    ): array {

        $m = $this->model->getLevelMetrics(
            $studentId,
            $quizId,
            $levelId,
            $attemptId
        );

        // SAFE defaults (no undefined keys ever)
        $accuracy    = (float)($m['accuracy']    ?? 0);
        $timeScore   = (float)($m['timeScore']   ?? 0);
        $consistency = (float)($m['consistency'] ?? 0);
        $improvement = (float)($m['improvement'] ?? 0);

        $aiScore = round(
            (0.45 * $accuracy) +
            (0.20 * $timeScore) +
            (0.20 * $consistency) +
            (0.15 * $improvement),
            2
        );

        $decision = match (true) {
            $aiScore >= 85 => 'ADVANCE_FAST',
            $aiScore >= 70 => 'ADVANCE',
            $aiScore >= 55 => 'REPEAT_SAME',
            $aiScore >= 40 => 'REPEAT_EASIER',
            default        => 'HOLD_REVIEW',
        };

        $this->model->logAiDecision(
            $studentId,
            $quizId,
            $levelId,
            $aiScore,
            $accuracy,
            $timeScore,
            $consistency,
            $improvement,
            $decision
        );

        return [
            'ai_score' => $aiScore,
            'decision' => $decision,
        ];
    }
}
