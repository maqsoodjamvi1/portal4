<?php

namespace App\Controllers\BoardPrep;

class Results extends BoardPrepBaseController
{
    public function index()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $auth      = board_prep_auth();
        $studentId = board_prep_linked_student_id();
        $overall   = $this->loadOverallStats($studentId);
        $recent    = $this->loadRecentAttempts($studentId, 20);

        return view('board_prep/results/overall', [
            'productName' => board_prep_product_name(),
            'auth'        => $auth,
            'overall'     => $overall,
            'recent'      => $recent,
        ]);
    }

    public function subjects()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $auth      = board_prep_auth();
        $studentId = board_prep_linked_student_id();
        $subjects  = $this->loadSubjectStats($studentId);

        return view('board_prep/results/subjects', [
            'productName' => board_prep_product_name(),
            'auth'        => $auth,
            'subjects'    => $subjects,
        ]);
    }

    public function quiz(int $attemptId)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $studentId = board_prep_linked_student_id();
        $attempt   = $this->db->table('quiz_attempts qa')
            ->select('qa.*, q.title, q.questions_count, q.time_limit_sec, s.subject_name')
            ->join('quizzes q', 'q.quiz_id = qa.quiz_id', 'inner')
            ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'left')
            ->join('allsubject s', 's.sid = ss.subject_id', 'left')
            ->where('qa.attempt_id', $attemptId)
            ->where('qa.student_id', $studentId)
            ->get()
            ->getRow();

        if (! $attempt) {
            return redirect()->to(board_prep_url('results'))->with('error', 'Result not found.');
        }

        $attempt->score_percent = board_prep_attempt_percent($attempt);

        $answers = $this->db->table('quiz_attempt_answers')
            ->where('attempt_id', $attemptId)
            ->get()
            ->getResult();

        return view('board_prep/results/quiz_detail', [
            'productName' => board_prep_product_name(),
            'auth'        => board_prep_auth(),
            'attempt'     => $attempt,
            'answers'     => $answers,
        ]);
    }

    /**
     * @return array{total_attempts:int,avg_percent:float,best_percent:float,quizzes_attempted:int}
     */
    private function loadOverallStats(int $studentId): array
    {
        $defaults = [
            'total_attempts'    => 0,
            'avg_percent'       => 0.0,
            'best_percent'      => 0.0,
            'quizzes_attempted' => 0,
        ];

        if ($studentId <= 0) {
            return $defaults;
        }

        $pctSql = board_prep_attempt_percent_sql('quiz_attempts');
        $res    = $this->db->table('quiz_attempts')
            ->select("COUNT(*) AS total_attempts, AVG({$pctSql}) AS avg_percent, MAX({$pctSql}) AS best_percent, COUNT(DISTINCT quiz_id) AS quizzes_attempted", false)
            ->where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'completed'])
            ->get();
        $row = $res ? $res->getRow() : null;

        if (! $row) {
            return $defaults;
        }

        return [
            'total_attempts'    => (int) ($row->total_attempts ?? 0),
            'avg_percent'       => round((float) ($row->avg_percent ?? 0), 1),
            'best_percent'      => round((float) ($row->best_percent ?? 0), 1),
            'quizzes_attempted' => (int) ($row->quizzes_attempted ?? 0),
        ];
    }

    /**
     * @return list<object>
     */
    private function loadRecentAttempts(int $studentId, int $limit = 20): array
    {
        if ($studentId <= 0) {
            return [];
        }

        $pctSql = board_prep_attempt_percent_sql('qa');

        return $this->db->table('quiz_attempts qa')
            ->select("qa.attempt_id, qa.quiz_id, qa.score_obtained, qa.submitted_at, qa.status, q.title, q.questions_count, s.subject_name, {$pctSql} AS score_percent", false)
            ->join('quizzes q', 'q.quiz_id = qa.quiz_id', 'inner')
            ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'left')
            ->join('allsubject s', 's.sid = ss.subject_id', 'left')
            ->where('qa.student_id', $studentId)
            ->whereIn('qa.status', ['submitted', 'completed'])
            ->orderBy('qa.submitted_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
    }

    /**
     * @return list<object>
     */
    private function loadSubjectStats(int $studentId): array
    {
        if ($studentId <= 0) {
            return [];
        }

        $pctSql = board_prep_attempt_percent_sql('qa');

        return $this->db->table('quiz_attempts qa')
            ->select("s.subject_name, s.subject_short_name, COUNT(qa.attempt_id) AS attempts, AVG({$pctSql}) AS avg_percent, MAX({$pctSql}) AS best_percent", false)
            ->join('quizzes q', 'q.quiz_id = qa.quiz_id', 'inner')
            ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'left')
            ->join('allsubject s', 's.sid = ss.subject_id', 'left')
            ->where('qa.student_id', $studentId)
            ->whereIn('qa.status', ['submitted', 'completed'])
            ->groupBy('s.sid, s.subject_name, s.subject_short_name')
            ->orderBy('s.subject_name', 'ASC')
            ->get()
            ->getResult();
    }
}
