<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;

class AttemptedQuizzes extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = db_connect();
        $this->session = session();
        helper(['url']);
    }

    public function index()
    {
        $studentId = (int) ($this->session->get('student_id') ?? 0);

        if (!$studentId) {
            return view('frontend/quizzes/attempted', [
                'attempted' => [],
                'err' => 'Student not set.'
            ]);
        }

        $attempted = $this->db->query("
            SELECT q.title, q.quiz_id,
                   qa.attempt_id, qa.attempt_no, qa.score_obtained, qa.status, qa.submitted_at
            FROM quiz_attempts qa
            JOIN quizzes q ON q.quiz_id = qa.quiz_id
            WHERE qa.student_id = ?
            ORDER BY qa.submitted_at DESC, qa.attempt_id DESC
        ", [$studentId])->getResult();

        return view('frontend/quizzes/attempted', compact('attempted'));
    }
}
