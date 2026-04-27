<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class QuizBattles extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Admin battle list
     */
    public function index()
    {
        $rows = $this->db->table('quiz_battles qb')
            ->select('
                qb.*,
                q.title AS quiz_title,
                s1.first_name AS p1_name,
                s2.first_name AS p2_name
            ')
            ->join('quizzes q', 'q.quiz_id = qb.quiz_id', 'left')
            ->join('students s1', 's1.student_id = qb.player1_id', 'left')
            ->join('students s2', 's2.student_id = qb.player2_id', 'left')
            ->orderBy('qb.created_at', 'DESC')
            ->get()->getResultArray();

        return view('admin/quiz_battles/index', [
            'battles' => $rows,
        ]);
    }

    /**
     * Admin battle view
     */
    public function view($battleId)
    {
        $battle = $this->db->table('quiz_battles qb')
            ->select('
                qb.*,
                q.title AS quiz_title,
                s1.first_name AS p1_name,
                s2.first_name AS p2_name
            ')
            ->join('quizzes q', 'q.quiz_id = qb.quiz_id', 'left')
            ->join('students s1', 's1.student_id = qb.player1_id', 'left')
            ->join('students s2', 's2.student_id = qb.player2_id', 'left')
            ->where('qb.battle_id', $battleId)
            ->get()->getRow();

        if (! $battle) {
            return redirect()->to(base_url('admin/quiz-battles'));
        }

        return view('admin/quiz_battles/view', [
            'battle' => $battle,
        ]);
    }
}
