<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\QbBoardPublisherService;

class AssessmentBuilder extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = db_connect();
        $this->session = session();
        helper(['form', 'url']);
        check_any_permission([
            'admin-quiz',
            'admin-question-paper',
            'admin-exams',
            'admin-questions',
        ], true);
    }

    public function index()
    {
        $quizData = $this->emptyQuizCreateDefaults();
        $canPaper = hasPermission('admin-question-paper') || hasPermission('admin-exams') || hasPermission('admin-questions');
        if (hasPermission('admin-quiz') || $canPaper) {
            $quizData = (new Quizzes())->getCreateViewData();
        }

        $boardService = new QbBoardPublisherService($this->db);
        $scope        = $this->templateScope();

        return view('admin/assessment_builder/index', array_merge($quizData, [
            'boardPublishers' => $boardService->listForSystem($scope['system_id'], true),
            'canQuiz'         => hasPermission('admin-quiz'),
            'canPaper'        => $canPaper,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyQuizCreateDefaults(): array
    {
        return [
            'campusId'               => (int) ($this->session->get('campus_id') ?? $this->session->get('member_campusid') ?? 0),
            'sessionId'              => (int) ($this->session->get('member_sessionid') ?? 0),
            'memberId'               => (int) ($this->session->get('member_id') ?? 0),
            'classSections'          => [],
            'clsSecLabels'           => [],
            'terms'                  => [],
            'quizDefaults'           => [],
            'quizDefaultsTableReady' => false,
            'examQuizColumnReady'    => false,
            'unannouncedExam'        => null,
        ];
    }

    /**
     * @return array{campus_id: int, system_id: int}
     */
    protected function templateScope(): array
    {
        $school   = getSchoolInfo();
        $systemId = is_object($school) ? (int) ($school->system_id ?? 0) : 0;

        return [
            'campus_id' => (int) ($this->session->get('campus_id') ?? $this->session->get('member_campusid') ?? 0),
            'system_id' => $systemId > 0 ? $systemId : 1,
        ];
    }
}
