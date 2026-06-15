<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Libraries\Crossword\CrosswordAdaptiveService;
use App\Libraries\Crossword\CrosswordSetService;

class Crossword extends BaseController
{
    private CrosswordSetService $setService;

    public function __construct()
    {
        $this->setService = new CrosswordSetService();
        helper(['form', 'url']);
    }

    /** @return array{student_id:int, cls_sec_id:int}|null */
    private function resolveStudentContext(): ?array
    {
        $session   = session();
        $studentId = (int) ($session->get('active_student_id') ?? $session->get('student_id') ?? 0);

        if ($studentId <= 0) {
            return null;
        }

        $clsSecId = (int) ($session->get('cls_sec_id') ?? 0);

        if ($clsSecId <= 0) {
            $row = \Config\Database::connect()
                ->table('student_class')
                ->select('cls_sec_id')
                ->where('student_id', $studentId)
                ->where('status', 1)
                ->orderBy('session_id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($row !== null) {
                $clsSecId = (int) ($row['cls_sec_id'] ?? 0);
            }
        }

        if ($clsSecId <= 0) {
            return null;
        }

        return [
            'student_id' => $studentId,
            'cls_sec_id' => $clsSecId,
        ];
    }

    public function index()
    {
        if (! $this->setService->tablesReady()) {
            return view('frontend/crossword/index', [
                'assignments'  => [],
                'tablesReady'  => false,
            ]);
        }

        $ctx = $this->resolveStudentContext();
        if ($ctx === null) {
            return redirect()->to(site_url('student/login'))->with('error', 'Please log in as a student.');
        }

        $assignments = $this->setService->studentAssignments($ctx['student_id'], $ctx['cls_sec_id']);

        return view('frontend/crossword/index', [
            'assignments' => $assignments,
            'tablesReady' => true,
        ]);
    }

    public function play(int $assignmentId)
    {
        $ctx = $this->resolveStudentContext();
        if ($ctx === null) {
            return redirect()->to(site_url('student/login'));
        }

        $db = \Config\Database::connect();
        $assignment = $db->table('crossword_assignments ca')
            ->select('ca.*, cs.title, cs.puzzle_type, cs.grade, cs.campus_id')
            ->join('crossword_sets cs', 'cs.id = ca.set_id')
            ->where('ca.id', $assignmentId)
            ->where('ca.cls_sec_id', $ctx['cls_sec_id'])
            ->where('ca.status', 1)
            ->get()
            ->getRowArray();

        if ($assignment === null) {
            return redirect()->to(site_url('student/crossword'))->with('error', 'Assignment not found.');
        }

        if (! empty($assignment['due_date']) && $assignment['due_date'] < date('Y-m-d')) {
            return redirect()->to(site_url('student/crossword'))->with('error', 'This crossword assignment is past its due date.');
        }

        $loaded = $this->setService->loadSet((int) $assignment['set_id'], (int) $assignment['campus_id']);
        if ($loaded === null) {
            return redirect()->to(site_url('student/crossword'))->with('error', 'Worksheet not found.');
        }

        if ($this->setService->hasCompletedAttempt($assignmentId, $ctx['student_id'])) {
            return redirect()->to(site_url('student/crossword/result/' . $assignmentId));
        }

        $puzzleCount = count($loaded['puzzles']);
        $puzzleIndex = max(0, min($puzzleCount - 1, (int) ($this->request->getGet('p') ?? 0)));

        return view('frontend/crossword/play', [
            'assignment'  => $assignment,
            'puzzles'     => $loaded['puzzles'],
            'title'       => $assignment['title'] ?? 'Crossword',
            'puzzleIndex' => $puzzleIndex,
            'puzzleCount' => $puzzleCount,
        ]);
    }

    public function submit()
    {
        $ctx = $this->resolveStudentContext();
        if ($ctx === null) {
            return redirect()->to(site_url('student/login'))->with('error', 'Invalid submission.');
        }

        $assignmentId = (int) $this->request->getPost('assignment_id');
        $answers      = $this->request->getPost('answers');
        if (! is_array($answers)) {
            $answers = [];
        }

        $db = \Config\Database::connect();
        $assignment = $db->table('crossword_assignments')
            ->where('id', $assignmentId)
            ->where('cls_sec_id', $ctx['cls_sec_id'])
            ->where('status', 1)
            ->get()
            ->getRowArray();

        if ($assignment === null) {
            return redirect()->to(site_url('student/crossword'))->with('error', 'Assignment not found.');
        }

        if (! empty($assignment['due_date']) && $assignment['due_date'] < date('Y-m-d')) {
            return redirect()->to(site_url('student/crossword'))->with('error', 'This assignment is past its due date.');
        }

        $loaded = $this->setService->loadSet((int) $assignment['set_id'], (int) $assignment['campus_id']);
        if ($loaded === null) {
            return redirect()->to(site_url('student/crossword'))->with('error', 'Worksheet not found.');
        }

        if ($this->setService->hasCompletedAttempt($assignmentId, $ctx['student_id'])) {
            return redirect()->to(site_url('student/crossword/result/' . $assignmentId))
                ->with('error', 'You have already submitted this crossword.');
        }

        $result = $this->setService->submitAttempt($assignmentId, $ctx['student_id'], $answers, $loaded['puzzles']);

        if (! empty($result['already_submitted'])) {
            return redirect()->to(site_url('student/crossword/result/' . $assignmentId))
                ->with('error', 'You have already submitted this crossword.');
        }

        $adaptive = new CrosswordAdaptiveService();
        $weak     = $adaptive->detectWeakOperations($loaded['puzzles'], $answers);
        if ($weak !== []) {
            session()->setFlashdata('crossword_weak_ops', $weak);
        }

        session()->setFlashdata('crossword_score', $result);

        return redirect()->to(site_url('student/crossword/result/' . $assignmentId));
    }

    public function result(int $assignmentId)
    {
        $ctx = $this->resolveStudentContext();
        if ($ctx === null) {
            return redirect()->to(site_url('student/login'));
        }

        $score = session()->getFlashdata('crossword_score');
        if (! is_array($score)) {
            $score = $this->setService->getLatestAttempt($assignmentId, $ctx['student_id']);
        }

        if (! is_array($score)) {
            return redirect()->to(site_url('student/crossword'))->with('error', 'No submission found for this assignment.');
        }

        $assignment = \Config\Database::connect()
            ->table('crossword_assignments ca')
            ->select('ca.*, cs.title')
            ->join('crossword_sets cs', 'cs.id = ca.set_id', 'left')
            ->where('ca.id', $assignmentId)
            ->where('ca.cls_sec_id', $ctx['cls_sec_id'])
            ->get()
            ->getRowArray();

        return view('frontend/crossword/result', [
            'score'        => $score,
            'assignmentId' => $assignmentId,
            'title'        => $assignment['title'] ?? 'Crossword',
            'weakOps'      => session()->getFlashdata('crossword_weak_ops') ?? [],
        ]);
    }
}
