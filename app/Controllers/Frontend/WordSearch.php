<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Libraries\WordSearch\WordSearchSetService;

class WordSearch extends BaseController
{
    private WordSearchSetService $setService;

    public function __construct()
    {
        $this->setService = new WordSearchSetService();
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

        return ['student_id' => $studentId, 'cls_sec_id' => $clsSecId];
    }

    public function index()
    {
        if (! $this->setService->tablesReady()) {
            return view('frontend/word_search/index', [
                'assignments' => [],
                'tablesReady' => false,
            ]);
        }

        $ctx = $this->resolveStudentContext();
        if ($ctx === null) {
            return redirect()->to(site_url('student/login'))->with('error', 'Please log in as a student.');
        }

        return view('frontend/word_search/index', [
            'assignments' => $this->setService->studentAssignments($ctx['student_id'], $ctx['cls_sec_id']),
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
        $assignment = $db->table('word_search_assignments wa')
            ->select('wa.*, ws.title, ws.grade, ws.campus_id')
            ->join('word_search_sets ws', 'ws.id = wa.set_id')
            ->where('wa.id', $assignmentId)
            ->where('wa.cls_sec_id', $ctx['cls_sec_id'])
            ->where('wa.status', 1)
            ->get()
            ->getRowArray();

        if ($assignment === null) {
            return redirect()->to(site_url('student/word-search'))->with('error', 'Assignment not found.');
        }

        if (! empty($assignment['due_date']) && $assignment['due_date'] < date('Y-m-d')) {
            return redirect()->to(site_url('student/word-search'))->with('error', 'This assignment is past its due date.');
        }

        $loaded = $this->setService->loadSet((int) $assignment['set_id'], (int) $assignment['campus_id']);
        if ($loaded === null) {
            return redirect()->to(site_url('student/word-search'))->with('error', 'Worksheet not found.');
        }

        if ($this->setService->hasCompletedAttempt($assignmentId, $ctx['student_id'])) {
            return redirect()->to(site_url('student/word-search/result/' . $assignmentId));
        }

        $puzzles     = $this->setService->puzzlesForStudent($loaded['puzzles']);
        $puzzleCount = count($puzzles);
        $puzzleIndex = max(0, min($puzzleCount - 1, (int) ($this->request->getGet('p') ?? 0)));

        return view('frontend/word_search/play', [
            'assignment'  => $assignment,
            'puzzles'     => $puzzles,
            'title'       => $assignment['title'] ?? 'Word Search',
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
        $foundRaw     = $this->request->getPost('found');
        if (! is_array($foundRaw)) {
            $foundRaw = [];
        }

        $db = \Config\Database::connect();
        $assignment = $db->table('word_search_assignments')
            ->where('id', $assignmentId)
            ->where('cls_sec_id', $ctx['cls_sec_id'])
            ->where('status', 1)
            ->get()
            ->getRowArray();

        if ($assignment === null) {
            return redirect()->to(site_url('student/word-search'))->with('error', 'Assignment not found.');
        }

        if (! empty($assignment['due_date']) && $assignment['due_date'] < date('Y-m-d')) {
            return redirect()->to(site_url('student/word-search'))->with('error', 'This assignment is past its due date.');
        }

        $loaded = $this->setService->loadSet((int) $assignment['set_id'], (int) $assignment['campus_id']);
        if ($loaded === null) {
            return redirect()->to(site_url('student/word-search'))->with('error', 'Worksheet not found.');
        }

        if ($this->setService->hasCompletedAttempt($assignmentId, $ctx['student_id'])) {
            return redirect()->to(site_url('student/word-search/result/' . $assignmentId))
                ->with('error', 'You have already submitted this word search.');
        }

        $submission = ['found' => $foundRaw];
        $result     = $this->setService->submitAttempt($assignmentId, $ctx['student_id'], $submission, $loaded['puzzles']);

        if (! empty($result['already_submitted'])) {
            return redirect()->to(site_url('student/word-search/result/' . $assignmentId))
                ->with('error', 'You have already submitted this word search.');
        }

        session()->setFlashdata('word_search_score', $result);

        return redirect()->to(site_url('student/word-search/result/' . $assignmentId));
    }

    public function result(int $assignmentId)
    {
        $ctx = $this->resolveStudentContext();
        if ($ctx === null) {
            return redirect()->to(site_url('student/login'));
        }

        $score = session()->getFlashdata('word_search_score');
        if (! is_array($score)) {
            $score = $this->setService->getLatestAttempt($assignmentId, $ctx['student_id']);
        }

        if (! is_array($score)) {
            return redirect()->to(site_url('student/word-search'))->with('error', 'No submission found.');
        }

        $assignment = \Config\Database::connect()
            ->table('word_search_assignments wa')
            ->select('wa.*, ws.title')
            ->join('word_search_sets ws', 'ws.id = wa.set_id', 'left')
            ->where('wa.id', $assignmentId)
            ->where('wa.cls_sec_id', $ctx['cls_sec_id'])
            ->get()
            ->getRowArray();

        return view('frontend/word_search/result', [
            'score'        => $score,
            'assignmentId' => $assignmentId,
            'title'        => $assignment['title'] ?? 'Word Search',
        ]);
    }
}
