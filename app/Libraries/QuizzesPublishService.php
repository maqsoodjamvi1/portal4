<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Toggle quiz published state.
 */
class QuizzesPublishService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * @return array{ok:bool, is_published?:int, error?:string, status?:int}
     */
    public function togglePublished(int $quizId): array
    {
        if ($quizId <= 0) {
            return ['ok' => false, 'error' => 'Invalid quiz id.', 'status' => 400];
        }

        $row = $this->db->table('quizzes')
            ->select('quiz_id, is_published')
            ->where('quiz_id', $quizId)
            ->get()
            ->getRow();

        if (! $row) {
            return ['ok' => false, 'error' => 'Quiz not found.', 'status' => 404];
        }

        $newPublished = ((int) ($row->is_published ?? 0) === 1) ? 0 : 1;

        $this->db->table('quizzes')
            ->where('quiz_id', $quizId)
            ->update(['is_published' => $newPublished]);

        return [
            'ok'           => true,
            'is_published' => $newPublished,
        ];
    }
}
