<?php

namespace App\Models;

use CodeIgniter\Model;

class AiQuizModel extends Model
{
    protected $table = 'ai_quiz_decisions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'student_id',
        'quiz_id',
        'level_id',
        'ai_score',
        'accuracy_score',
        'time_score',
        'consistency_score',
        'improvement_score',
        'decision',
        'explanation',
        'created_at',
    ];

    /**
     * IMPORTANT:
     * DO NOT create model instances here.
     * Use $this->db directly.
     */
    public function getLevelMetrics(
        int $studentId,
        int $quizId,
        int $levelId,
        int $attemptId
    ): array {

        /**
         * Lightweight calculations only.
         * NO recursion
         * NO new Model()
         */

        // Example (safe placeholders)
        return [
            'accuracy'    => 80.0,
            'timeScore'   => 70.0,
            'consistency' => 75.0,
            'improvement' => 10.0,
        ];
    }

    public function logAiDecision(
        int $studentId,
        int $quizId,
        int $levelId,
        float $aiScore,
        float $accuracy,
        float $timeScore,
        float $consistency,
        float $improvement,
        string $decision
    ): void {

        $this->insert([
            'student_id'        => $studentId,
            'quiz_id'           => $quizId,
            'level_id'          => $levelId,
            'ai_score'          => $aiScore,
            'accuracy_score'    => $accuracy,
            'time_score'        => $timeScore,
            'consistency_score' => $consistency,
            'improvement_score' => $improvement,
            'decision'          => $decision,
            'explanation'       => $this->decisionExplanation($decision),
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
    }

    private function decisionExplanation(string $decision): string
    {
        return match ($decision) {
            'ADVANCE_FAST'  => 'Excellent performance. Fast-tracked.',
            'ADVANCE'       => 'Good performance. Proceeding.',
            'REPEAT_SAME'   => 'Retry recommended.',
            'REPEAT_EASIER' => 'Practice easier questions.',
            default         => 'Review concepts required.',
        };
    }
}
