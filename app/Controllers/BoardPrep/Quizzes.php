<?php

namespace App\Controllers\BoardPrep;

/**
 * Board prep quiz attempts — reuses the student quiz engine with prep URL prefix.
 */
class Quizzes extends \App\Controllers\Frontend\Quizzes
{
    public function __construct()
    {
        parent::__construct();
        $this->boardPrepPortal = true;
        helper('board_prep');
    }

    public function index()
    {
        return redirect()->to(board_prep_url('dashboard'));
    }

    public function complete(int $attemptId)
    {
        if (! board_prep_auth()) {
            return redirect()->to(board_prep_url('login'));
        }

        $attemptId = (int) $attemptId;
        $studentId = board_prep_linked_student_id();
        if ($attemptId <= 0 || $studentId <= 0) {
            return redirect()->to(board_prep_url('dashboard'))->with('error', 'Invalid attempt.');
        }

        $attempt = $this->db->table('quiz_attempts')
            ->where('attempt_id', $attemptId)
            ->where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'completed'])
            ->get()
            ->getRow();

        if (! $attempt) {
            return redirect()->to(board_prep_url('dashboard'))->with('error', 'Attempt not found.');
        }

        $quiz = $this->db->table('quizzes')->where('quiz_id', (int) $attempt->quiz_id)->get()->getRow();
        if (! $quiz || ! $this->boardPrepQuizAllowed($quiz)) {
            return redirect()->to(board_prep_url('dashboard'))->with('error', 'Quiz not found.');
        }

        $attempt->quiz_id = (int) $quiz->quiz_id;
        $scorePercent     = board_prep_attempt_percent($attempt);
        $totalMarks       = board_prep_quiz_total_marks((int) $quiz->quiz_id);

        $subjectName = '';
        if ((int) ($quiz->sec_sub_id ?? 0) > 0) {
            $subRow = $this->db->table('section_subjects ss')
                ->select('s.subject_name')
                ->join('allsubject s', 's.sid = ss.subject_id', 'left')
                ->where('ss.sec_sub_id', (int) $quiz->sec_sub_id)
                ->get()
                ->getRow();
            $subjectName = (string) ($subRow->subject_name ?? '');
        }

        return view('board_prep/quizzes/attempt_complete', [
            'productName'  => config('BoardPrep')->productName,
            'quiz'         => $quiz,
            'attempt'      => $attempt,
            'subjectName'  => $subjectName,
            'scorePercent' => $scorePercent,
            'totalMarks'   => $totalMarks,
            'canReview'    => (int) ($quiz->show_solution ?? 0) === 1,
        ]);
    }
}
