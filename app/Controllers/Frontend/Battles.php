<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;

class Battles extends BaseController
{
    public function index()
    {
        // Just render the page (no heavy logic here)
        $quizId = (int) $this->request->getGet('quiz_id');
        return view('frontend/battles/index', ['quiz_id' => $quizId]);
    }

    public function data()
    {
        try {
            $quizRaw = (string) $this->request->getGet('quiz_id');

            // ✅ Fix wrong format: "10:340"
            // If user mistakenly sends "quiz_id=10:340", extract 10
            if (strpos($quizRaw, ':') !== false) {
                $quizRaw = explode(':', $quizRaw)[0];
            }

            $quizId = (int) $quizRaw;

            if ($quizId <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Invalid quiz_id'
                ]);
            }

            $studentId = (int) session('student_id');
            if ($studentId <= 0) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Student not logged in'
                ]);
            }

            // Ensure table exists (quick check)
            // If table missing, this will throw and be returned as JSON below.
            $rows = $this->db->table('quiz_battles')
                ->select('*')
                ->where('quiz_id', $quizId)
                ->groupStart()
                    ->where('created_by', $studentId)
                    ->orWhere('opponent_id', $studentId)
                ->groupEnd()
                ->orderBy('battle_id', 'DESC')
                ->limit(50)
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'rows'    => $rows
            ]);

        } catch (\Throwable $e) {
            // ✅ This prevents "500 Internal Server Error" with no details
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Server error in battles/data',
                'error'   => $e->getMessage(), // keep for debugging
            ]);
        }
    }

    public function create()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'AJAX required'
            ]);
        }

        try {
            $studentId = (int) session('student_id');
            if ($studentId <= 0) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Student not logged in'
                ]);
            }

            $quizId     = (int) $this->request->getPost('quiz_id');
            $opponentId = (int) $this->request->getPost('opponent_id');

            if ($quizId <= 0 || $opponentId <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Invalid quiz or opponent'
                ]);
            }

            if ($studentId === $opponentId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'You cannot battle yourself'
                ]);
            }

            $quiz = $this->db->table('quizzes')
                ->where('quiz_id', $quizId)
                ->where('is_published', 1)
                ->get()->getRow();

            if (! $quiz) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Quiz not found or not published'
                ]);
            }

            $this->db->table('quiz_battles')->insert([
                'quiz_id'     => $quizId,
                'created_by'  => $studentId,
                'opponent_id' => $opponentId,
                'status'      => 'pending',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            if (! $this->db->affectedRows()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'DB insert failed'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Battle created'
            ]);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Server error in battles/create',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
