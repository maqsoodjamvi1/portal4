<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\QbBoardPublisherService;
use App\Libraries\QuestionPaperService;
use App\Libraries\QuestionPaperWordExporter;
use CodeIgniter\HTTP\ResponseInterface;

class QuestionPaper extends BaseController
{
    protected $db;
    protected $session;
    protected QuestionPaperService $paperService;

    public function __construct()
    {
        $this->db           = db_connect();
        $this->session      = session();
        $this->paperService = new QuestionPaperService($this->db);
        helper(['form', 'url']);
        check_any_permission([
            'admin-question-paper',
            'admin-exams',
            'admin-questions',
        ], true);
    }

    public function index()
    {
        $boardService = new QbBoardPublisherService($this->db);
        $scope        = $this->templateScope();

        return view('admin/question_paper/index', [
            'boardPublishers' => $boardService->listForSystem($scope['system_id'], true),
        ]);
    }

    public function ajaxSummary(): ResponseInterface
    {
        $classId   = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');
        $boardIds  = $this->intList($this->request->getGet('board_publisher_ids'));
        $res       = $this->paperService->fetchSummaryRows(
            $classId > 0 ? $classId : null,
            $subjectId > 0 ? $subjectId : null,
            $boardIds
        );

        if (!$res['ok']) {
            return $this->response->setJSON(['ok' => false, 'msg' => $res['msg'] ?? 'Failed']);
        }

        $rows = $res['data'] ?? [];

        return $this->response->setJSON([
            'ok'   => true,
            'data' => $this->paperService->buildSummaryTree($rows),
            'flat' => $rows,
        ]);
    }

    public function ajaxQuestions(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $filters = $this->parseFiltersFromRequest();
        $pool    = $this->paperService->fetchPool($filters);
        $counts  = $this->paperService->countByTypeInPool($filters);

        $list = [];
        foreach ($pool as $q) {
            $list[] = [
                'id'            => $q['id'],
                'question'      => $q['question'],
                'question_type' => $q['question_type'],
                'difficulty'    => $q['difficulty'],
                'topic_name'    => $q['topic_name'],
                'subject_name'  => $q['subject_name'],
                'class_name'    => $q['class_name'],
            ];
        }

        return $this->response->setJSON([
            'ok'     => true,
            'data'   => $list,
            'counts' => $counts,
        ]);
    }

    public function preview(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        [$config, $questions, $err] = $this->buildPaperFromRequest();
        if ($err !== null) {
            return $this->response->setJSON(['ok' => false, 'msg' => $err]);
        }

        $typeSections = $this->preparePaperSections($questions, $config);

        $html = view('admin/question_paper/partials/preview_body', [
            'config'       => $config,
            'questions'    => $questions,
            'typeSections' => $typeSections,
            'showAnswers'  => ($config['layout']['paper_mode'] ?? 'student') !== 'student',
        ]);

        return $this->response->setJSON([
            'ok'       => true,
            'html'     => $html,
            'count'    => count($questions),
        ]);
    }

    public function print()
    {
        return $this->renderPrintView(false);
    }

    public function printKey()
    {
        return $this->renderPrintView(true);
    }

    public function downloadWord()
    {
        return $this->sendWordDownload(false);
    }

    public function downloadWordKey()
    {
        return $this->sendWordDownload(true);
    }

    public function printVersions()
    {
        [$config, $questions, $err] = $this->buildPaperFromRequest();
        if ($err !== null) {
            return redirect()->to(base_url('admin/question-paper'))->with('error', $err);
        }

        $versions = max(1, min(4, (int) ($config['layout']['versions'] ?? 1)));
        $sets     = [];

        $pool = $this->paperService->fetchPool($config['filters'] ?? []);

        for ($v = 1; $v <= $versions; $v++) {
            $opts = $config;
            $opts['shuffle_questions']   = true;
            $opts['shuffle_mcq_options'] = true;

            if (($config['selection_mode'] ?? '') === 'manual' && !empty($config['question_ids'])) {
                $qs = $this->paperService->assemble(
                    $this->paperService->fetchByIds($config['question_ids']),
                    $opts
                );
            } else {
                $qs = $this->paperService->assemble($pool, $opts);
            }

            $versionConfig = $config;
            $sets[] = [
                'label'        => 'Version ' . $v,
                'questions'    => $qs,
                'typeSections' => $this->preparePaperSections($qs, $versionConfig),
                'config'       => $versionConfig,
            ];
        }

        return view('admin/question_paper/print_versions', [
            'config' => $config,
            'sets'   => $sets,
        ]);
    }

    public function templates(): ResponseInterface
    {
        $scope = $this->templateScope();
        $rows  = $this->db->table('question_paper_templates')
            ->where('campus_id', $scope['campus_id'])
            ->where('system_id', $scope['system_id'])
            ->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['ok' => true, 'data' => $rows]);
    }

    public function saveTemplate(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Template name is required.']);
        }

        [$config] = $this->parseConfigFromRequest();
        $topicKeysSave = $this->stringArrayPost('topic_keys_save');
        if ($topicKeysSave !== []) {
            $config['topic_keys'] = $topicKeysSave;
        } else {
            $tk = $this->request->getPost('topic_keys');
            if (is_array($tk)) {
                $config['topic_keys'] = array_map('strval', $tk);
            }
        }
        $fixed    = (int) $this->request->getPost('fixed_questions') === 1;
        $qIds     = $this->intArrayPost('question_ids');

        $scope = $this->templateScope();
        $id    = (int) $this->request->getPost('template_id');
        $data  = [
            'name'              => $name,
            'campus_id'         => $scope['campus_id'],
            'system_id'         => $scope['system_id'],
            'created_by'        => (int) ($this->session->get('member_id') ?? 0),
            'config_json'       => json_encode($config, JSON_UNESCAPED_UNICODE),
            'question_ids_json' => ($fixed && $qIds !== []) ? json_encode($qIds) : null,
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            unset($data['created_by']);
            $this->db->table('question_paper_templates')
                ->where('id', $id)
                ->where('campus_id', $scope['campus_id'])
                ->update($data);

            return $this->response->setJSON(['ok' => true, 'id' => $id, 'msg' => 'Template updated.']);
        }

        $data['created_at'] = $data['updated_at'];
        $this->db->table('question_paper_templates')->insert($data);

        return $this->response->setJSON([
            'ok'  => true,
            'id'  => (int) $this->db->insertID(),
            'msg' => 'Template saved.',
        ]);
    }

    public function loadTemplate(int $id): ResponseInterface
    {
        $scope = $this->templateScope();
        $row   = $this->db->table('question_paper_templates')
            ->where('id', $id)
            ->where('campus_id', $scope['campus_id'])
            ->get()
            ->getRowArray();

        if (!$row) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Template not found.']);
        }

        $config = json_decode((string) ($row['config_json'] ?? '{}'), true);
        if (!is_array($config)) {
            $config = [];
        }
        $qIds = json_decode((string) ($row['question_ids_json'] ?? ''), true);
        if (!is_array($qIds)) {
            $qIds = [];
        }

        return $this->response->setJSON([
            'ok'   => true,
            'data' => [
                'id'           => (int) $row['id'],
                'name'         => (string) $row['name'],
                'config'       => $config,
                'topic_keys'   => $config['topic_keys'] ?? [],
                'question_ids' => array_map('intval', $qIds),
            ],
        ]);
    }

    public function deleteTemplate(int $id): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $scope = $this->templateScope();
        $this->db->table('question_paper_templates')
            ->where('id', $id)
            ->where('campus_id', $scope['campus_id'])
            ->delete();

        return $this->response->setJSON(['ok' => true, 'msg' => 'Template deleted.']);
    }

    /**
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */
    private function storeBuilderResponse(bool $ok, string $msg, ?int $paperId = null)
    {
        if ($this->request->isAJAX()) {
            $payload = ['ok' => $ok, 'msg' => $msg];
            if ($paperId !== null) {
                $payload['id'] = $paperId;
            }

            return $this->response->setJSON($payload);
        }

        return redirect()->to(base_url('admin/assessment-builder'))->with($ok ? 'success' : 'error', $msg);
    }

    public function store()
    {
        if (!$this->request->is('post')) {
            return redirect()->to(base_url('admin/assessment-builder'));
        }

        if (!$this->db->tableExists('question_papers')) {
            return $this->storeBuilderResponse(false, 'Question papers table missing. Run: php spark migrate');
        }

        if (! $this->questionPapersHasTargetColumns()) {
            return $this->storeBuilderResponse(false, 'Run php spark migrate to add term/class/subject columns for question papers.');
        }

        [$config, $questions, $err] = $this->buildPaperFromRequest();
        if ($err !== null) {
            return $this->storeBuilderResponse(false, $err);
        }

        $title = trim((string) ($config['header']['title'] ?? $this->request->getPost('paper_title')));
        if ($title === '') {
            return $this->storeBuilderResponse(false, 'Paper title is required.');
        }

        $termSessionId = (int) $this->request->getPost('term_session_id');
        $clsSecId      = (int) $this->request->getPost('cls_sec_id');
        $secSubId      = (int) $this->request->getPost('subject_id');

        if ($termSessionId <= 0 || $clsSecId <= 0 || $secSubId <= 0) {
            return $this->storeBuilderResponse(false, 'Term, class, and subject are required for the question paper.');
        }

        $filters   = $config['filters'] ?? [];
        $classId   = (int) (($filters['class_ids'][0] ?? 0));
        $subjectId = (int) (($filters['subject_ids'][0] ?? 0));
        $scope     = $this->templateScope();
        $now       = date('Y-m-d H:i:s');

        if ($config['header']['title'] === '') {
            $config['header']['title'] = $title;
        }

        $config['target'] = [
            'term_session_id' => $termSessionId,
            'cls_sec_id'      => $clsSecId,
            'sec_sub_id'      => $secSubId,
        ];

        $this->db->transBegin();

        try {
            $row = [
                'title'           => $title,
                'campus_id'       => $scope['campus_id'],
                'system_id'       => $scope['system_id'],
                'session_id'      => (int) ($this->session->get('member_sessionid') ?? 0),
                'class_id'        => $classId,
                'subject_id'      => $subjectId,
                'paper_subject'   => trim((string) ($config['header']['subject'] ?? $this->request->getPost('paper_subject'))),
                'paper_class'     => trim((string) ($config['header']['class_label'] ?? $this->request->getPost('paper_class'))),
                'selection_mode'  => (string) ($config['selection_mode'] ?? 'auto'),
                'config_json'     => json_encode($config, JSON_UNESCAPED_UNICODE),
                'questions_count' => count($questions),
                'created_by'      => (int) ($this->session->get('member_id') ?? 0),
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            if ($this->questionPapersHasTargetColumns()) {
                $row['term_session_id'] = $termSessionId;
                $row['cls_sec_id']      = $clsSecId;
                $row['sec_sub_id']      = $secSubId;
            }

            $this->db->table('question_papers')->insert($row);

            $paperId = (int) $this->db->insertID();
            if ($paperId <= 0) {
                throw new \RuntimeException('Could not save question paper.');
            }

            $batch = [];
            $sort  = 1;
            foreach ($questions as $q) {
                $qid = (int) ($q['id'] ?? 0);
                if ($qid <= 0) {
                    continue;
                }
                $batch[] = [
                    'paper_id'    => $paperId,
                    'question_id' => $qid,
                    'sort_order'  => $sort++,
                ];
            }

            if ($batch === []) {
                throw new \RuntimeException('No questions to save.');
            }

            $this->db->table('question_paper_questions')->insertBatch($batch);
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'QuestionPaper::store - ' . $e->getMessage());

            return $this->storeBuilderResponse(false, 'Could not save question paper.');
        }

        $successMsg = 'Question paper saved with ' . count($questions) . ' question(s).';

        return $this->storeBuilderResponse(true, $successMsg, $paperId);
    }

    public function ajaxByFilters(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'msg' => 'Bad request']);
        }

        if (!$this->db->tableExists('question_papers')) {
            return $this->response->setJSON(['ok' => true, 'data' => []]);
        }

        $termSessionId = (int) $this->request->getGet('term_session_id');
        $clsSecId      = (int) $this->request->getGet('cls_sec_id');
        $secSubId      = (int) $this->request->getGet('sec_sub_id');
        $classId       = (int) $this->request->getGet('class_id');
        $subjectId     = (int) $this->request->getGet('subject_id');
        $scope         = $this->templateScope();

        $builder = $this->db->table('question_papers')
            ->select('id, title, questions_count, paper_subject, paper_class, created_at')
            ->where('campus_id', $scope['campus_id']);

        if ($this->questionPapersHasTargetColumns() && $termSessionId > 0 && $clsSecId > 0 && $secSubId > 0) {
            $builder->where('term_session_id', $termSessionId)
                ->where('cls_sec_id', $clsSecId)
                ->where('sec_sub_id', $secSubId);
        } elseif ($classId > 0 && $subjectId > 0) {
            $builder->where('class_id', $classId)
                ->where('subject_id', $subjectId);
        } else {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Missing filters']);
        }

        $rows = $builder->orderBy('created_at', 'DESC')
            ->limit(18)
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['ok' => true, 'data' => $rows]);
    }

    protected function questionPapersHasTargetColumns(): bool
    {
        return $this->db->tableExists('question_papers')
            && $this->db->fieldExists('term_session_id', 'question_papers')
            && $this->db->fieldExists('cls_sec_id', 'question_papers')
            && $this->db->fieldExists('sec_sub_id', 'question_papers');
    }

    public function printSettings(int $id)
    {
        $loaded = $this->loadSavedPaper($id);
        if ($loaded === null) {
            return redirect()->to(base_url('admin/assessment-builder'))
                ->with('error', 'Question paper not found.');
        }

        return view('admin/question_paper/print_settings', [
            'paper'  => $loaded['paper'],
            'config' => $loaded['config'],
        ]);
    }

    public function printSaved(int $id)
    {
        return $this->renderSavedPrintView($id, false);
    }

    public function printSavedKey(int $id)
    {
        return $this->renderSavedPrintView($id, true);
    }

    /**
     * @return array{paper: array<string, mixed>, questions: list<array<string, mixed>>, config: array<string, mixed>}|null
     */
    protected function loadSavedPaper(int $id): ?array
    {
        if (!$this->db->tableExists('question_papers')) {
            return null;
        }

        $scope = $this->templateScope();
        $row   = $this->db->table('question_papers')
            ->where('id', $id)
            ->where('campus_id', $scope['campus_id'])
            ->get()
            ->getRowArray();

        if (!$row) {
            return null;
        }

        $qRows = $this->db->table('question_paper_questions')
            ->select('question_id')
            ->where('paper_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $ids = array_values(array_filter(array_map(static fn ($r) => (int) ($r['question_id'] ?? 0), $qRows)));
        $questions = $ids !== [] ? $this->paperService->fetchByIds($ids) : [];

        $config = json_decode((string) ($row['config_json'] ?? '{}'), true);
        if (!is_array($config)) {
            $config = [];
        }

        return [
            'paper'      => $row,
            'questions'  => $questions,
            'config'     => $config,
        ];
    }

    /**
     * @return ResponseInterface|string
     */
    protected function renderSavedPrintView(int $id, bool $answerKeyOnly)
    {
        $loaded = $this->loadSavedPaper($id);
        if ($loaded === null) {
            return redirect()->to(base_url('admin/assessment-builder'))
                ->with('error', 'Question paper not found.');
        }

        $config    = $this->mergePrintSettingsIntoConfig($loaded['config'], $loaded['paper']);
        $questions = $loaded['questions'];

        if ($questions === []) {
            return redirect()->to(base_url('admin/question-paper/print-settings/' . $id))
                ->with('error', 'No questions found for this paper.');
        }

        if (!empty($config['layout']['shuffle_questions'])) {
            shuffle($questions);
        }

        $typeSections = $this->preparePaperSections($questions, $config);
        $view         = $answerKeyOnly ? 'admin/question_paper/print_key' : 'admin/question_paper/print';

        return view($view, [
            'config'       => $config,
            'questions'    => $questions,
            'typeSections' => $typeSections,
            'showAnswers'  => $answerKeyOnly || (($config['layout']['paper_mode'] ?? '') === 'key'),
        ]);
    }

    /**
     * @param array<string, mixed> $stored
     * @param array<string, mixed> $paperRow
     * @return array<string, mixed>
     */
    protected function mergePrintSettingsIntoConfig(array $stored, array $paperRow): array
    {
        if ($this->request->getMethod() === 'post') {
            [$posted] = $this->parseConfigFromRequest();
            $stored['header'] = array_merge($stored['header'] ?? [], $posted['header'] ?? []);
            $stored['layout'] = array_merge($stored['layout'] ?? [], $posted['layout'] ?? []);
            $stored['shuffle_questions']   = $posted['shuffle_questions'] ?? false;
            $stored['shuffle_mcq_options'] = $posted['shuffle_mcq_options'] ?? false;
        }

        if (empty($stored['header']['title'])) {
            $stored['header']['title'] = (string) ($paperRow['title'] ?? '');
        }

        return $stored;
    }

    /**
     * @return ResponseInterface|string
     */
    protected function renderPrintView(bool $answerKeyOnly)
    {
        [$config, $questions, $err] = $this->buildPaperFromRequest();
        if ($err !== null) {
            return redirect()->to(base_url('admin/question-paper'))->with('error', $err);
        }

        if ($questions === []) {
            return redirect()->to(base_url('admin/question-paper'))->with('error', 'No questions selected for this paper.');
        }

        $view = $answerKeyOnly ? 'admin/question_paper/print_key' : 'admin/question_paper/print';

        $typeSections = $this->preparePaperSections($questions, $config);

        return view($view, [
            'config'       => $config,
            'questions'    => $questions,
            'typeSections' => $typeSections,
            'showAnswers'  => $answerKeyOnly || (($config['layout']['paper_mode'] ?? '') === 'key'),
        ]);
    }

    /**
     * @param list<array<string, mixed>> $questions
     * @param array<string, mixed> $config
     * @return list<array<string, mixed>>
     */
    protected function preparePaperSections(array $questions, array &$config): array
    {
        $sections = $this->paperService->groupByTypeSections($questions);
        $sections = $this->paperService->enrichTypeSections(
            $sections,
            $config['section_marks'] ?? []
        );

        $total = 0.0;
        foreach ($sections as $s) {
            $total += (float) ($s['section_marks'] ?? 0);
        }

        if ($total > 0 && isset($config['header']) && is_array($config['header'])) {
            $config['header']['total_marks'] = self::formatMarksDisplay($total);
        }

        return $sections;
    }

    protected static function formatMarksDisplay(float $total): string
    {
        if (abs($total - round($total)) < 0.001) {
            return (string) (int) round($total);
        }

        return rtrim(rtrim(number_format($total, 1, '.', ''), '0'), '.');
    }

    /**
     * @return ResponseInterface|string
     */
    protected function sendWordDownload(bool $answerKeyOnly)
    {
        [$config, $questions, $err] = $this->buildPaperFromRequest();
        if ($err !== null) {
            return redirect()->to(base_url('admin/question-paper'))->with('error', $err);
        }

        if ($questions === []) {
            return redirect()->to(base_url('admin/question-paper'))->with('error', 'No questions selected for this paper.');
        }

        $typeSections = $this->preparePaperSections($questions, $config);
        $paperMode    = (string) ($config['layout']['paper_mode'] ?? 'student');

        if ($answerKeyOnly) {
            $showAnswers = true;
            $includeKey  = false;
        } else {
            $showAnswers = $paperMode === 'key';
            $includeKey  = $paperMode === 'both';
        }

        $export = (new QuestionPaperWordExporter())->export(
            $config,
            $typeSections,
            $showAnswers,
            $includeKey,
            $answerKeyOnly
        );

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!empty($export['path']) && is_file($export['path'])) {
            $tempPath = $export['path'];
            $response = $this->response->download($tempPath, null);

            if ($response === null) {
                @unlink($tempPath);

                return redirect()->to(base_url('admin/question-paper'))->with('error', 'Could not start Word download.');
            }

            $response->setFileName($export['filename']);
            register_shutdown_function(static function () use ($tempPath): void {
                if (is_string($tempPath) && is_file($tempPath)) {
                    @unlink($tempPath);
                }
            });

            return $response;
        }

        $filename = $export['filename'];
        $body     = (string) ($export['body'] ?? '');

        if ($body === '') {
            return redirect()->to(base_url('admin/question-paper'))->with('error', 'Could not build Word document.');
        }

        $response = $this->response->download($filename, $body, false);
        if ($response === null) {
            return redirect()->to(base_url('admin/question-paper'))->with('error', 'Could not start Word download.');
        }

        $response->setContentType($export['mime']);

        return $response;
    }

    /**
     * @return array{0: array<string, mixed>, 1: list<array<string, mixed>>, 2: ?string}
     */
    protected function buildPaperFromRequest(): array
    {
        [$config] = $this->parseConfigFromRequest();
        $filters  = $config['filters'] ?? [];

        if (empty($filters['topic_ids']) && empty($filters['class_ids'])) {
            return [[], [], 'Select at least one topic.'];
        }

        $pool = $this->paperService->fetchPool($filters);

        if (!empty($config['question_ids']) && !empty($config['fixed_questions'])) {
            $ids = $this->intList($config['question_ids']);
            $questions = $ids !== [] ? $this->paperService->fetchByIds($ids) : [];
            if (!empty($config['layout']['shuffle_questions'])) {
                shuffle($questions);
            }
        } elseif (($config['selection_mode'] ?? '') === 'manual' && !empty($config['question_ids'])) {
            $questions = $this->paperService->assemble($pool, $config);
        } else {
            $questions = $this->paperService->assemble($pool, $config);
        }

        if ($questions === []) {
            return [$config, [], 'No questions match your selection. Adjust topics, types, or counts.'];
        }

        return [$config, $questions, null];
    }

    /**
     * @return array{0: array<string, mixed>}
     */
    protected function parseConfigFromRequest(): array
    {
        $filters = $this->parseFiltersFromRequest();

        $counts = [];
        $sectionMarks = [];
        foreach (['mcq', 'mcq_multi', 'tf', 'fill', 'short', 'descriptive', 'match'] as $k) {
            $counts[$k]         = max(0, min(99, (int) $this->request->getPost('count_' . $k)));
            $sectionMarks[$k]   = max(0, min(999, (int) $this->request->getPost('marks_' . $k)));
        }

        $header = $this->applyPaperBranding([
            'title'         => trim((string) $this->request->getPost('paper_title')),
            'subject'       => trim((string) $this->request->getPost('paper_subject')),
            'class_label'   => trim((string) $this->request->getPost('paper_class')),
            'exam_date'     => trim((string) $this->request->getPost('exam_date')),
            'exam_time'     => trim((string) $this->request->getPost('exam_time')),
            'duration'      => trim((string) $this->request->getPost('duration')),
            'total_marks'   => '',
            'instructions'  => trim((string) $this->request->getPost('instructions')),
            'show_name'     => (int) $this->request->getPost('show_name') === 1,
            'show_roll'     => (int) $this->request->getPost('show_roll') === 1,
            'show_section'  => (int) $this->request->getPost('show_section') === 1,
        ]);

        $layout = [
            'paper_mode'          => in_array(
                (string) $this->request->getPost('paper_mode'),
                ['student', 'key', 'both'],
                true
            ) ? (string) $this->request->getPost('paper_mode') : 'student',
            'columns'             => (int) $this->request->getPost('columns') === 2 ? 2 : 1,
            'font_size'           => in_array(
                (string) $this->request->getPost('font_size'),
                ['small', 'normal', 'large'],
                true
            ) ? (string) $this->request->getPost('font_size') : 'normal',
            'show_topics'         => (int) $this->request->getPost('show_topics') !== 0,
            'mcq_inline'          => (int) $this->request->getPost('mcq_inline') !== 0,
            'descriptive_answer_space' => (int) $this->request->getPost('descriptive_answer_space') === 1,
            'descriptive_lines'        => max(1, min(12, (int) $this->request->getPost('descriptive_lines'))),
            'page_break_topic'    => (int) $this->request->getPost('page_break_topic') === 1,
            'shuffle_questions'   => (int) $this->request->getPost('shuffle_questions') === 1,
            'shuffle_mcq_options' => (int) $this->request->getPost('shuffle_mcq_options') === 1,
            'group_by_topic'      => (int) $this->request->getPost('group_by_topic') === 1,
            'show_question_marks' => (int) $this->request->getPost('show_question_marks') === 1,
            'versions'            => max(1, min(4, (int) $this->request->getPost('versions'))),
        ];

        if (!in_array($layout['font_size'], ['small', 'normal', 'large'], true)) {
            $layout['font_size'] = 'normal';
        }

        $layout['descriptive_choice'] = $this->parseDescriptiveChoiceFromRequest();

        $config = [
            'filters'         => $filters,
            'selection_mode'  => in_array(
                (string) $this->request->getPost('selection_mode'),
                ['auto', 'manual', 'all'],
                true
            ) ? (string) $this->request->getPost('selection_mode') : 'auto',
            'counts'          => $counts,
            'section_marks'   => $sectionMarks,
            'question_ids'    => $this->intArrayPost('question_ids'),
            'fixed_questions' => (int) $this->request->getPost('fixed_questions') === 1,
            'header'          => $header,
            'layout'          => $layout,
            'shuffle_questions'   => $layout['shuffle_questions'],
            'shuffle_mcq_options' => $layout['shuffle_mcq_options'],
            'group_by_topic'      => $layout['group_by_topic'],
        ];

        return [$config];
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseFiltersFromRequest(): array
    {
        $topicKeys = $this->request->getPost('topic_keys');
        if (!is_array($topicKeys)) {
            $topicKeys = $this->stringArrayPost('topic_keys_save');
        }
        $classIds  = [];
        $subjectIds = [];
        $topicIds  = [];

        if (is_array($topicKeys)) {
            foreach ($topicKeys as $key) {
                $parts = explode('|', (string) $key);
                if (count($parts) >= 3) {
                    $classIds[]   = (int) $parts[0];
                    $subjectIds[] = (int) $parts[1];
                    $topicIds[]   = (int) $parts[2];
                }
            }
        }

        $classIds   = array_values(array_unique(array_filter($classIds)));
        $subjectIds = array_values(array_unique(array_filter($subjectIds)));
        $topicIds   = array_values(array_unique(array_filter($topicIds)));

        if ($classIds === []) {
            $classIds = $this->intArrayPost('class_ids');
        }
        if ($subjectIds === []) {
            $subjectIds = $this->intArrayPost('subject_ids');
        }
        if ($topicIds === []) {
            $topicIds = $this->intArrayPost('topic_ids');
        }

        return [
            'class_ids'            => $classIds,
            'subject_ids'          => $subjectIds,
            'topic_ids'            => $topicIds,
            'board_publisher_ids'  => $this->intArrayPost('board_publisher_ids'),
            'question_types'       => $this->stringArrayPost('question_types'),
            'difficulties'         => $this->stringArrayPost('difficulties'),
        ];
    }

    /**
     * @return array{campus_id: int, system_id: int}
     */
    protected function templateScope(): array
    {
        $school = getSchoolInfo();
        $systemId = is_object($school) ? (int) ($school->system_id ?? 0) : 0;

        return [
            'campus_id' => (int) ($this->session->get('campus_id') ?? $this->session->get('member_campusid') ?? 0),
            'system_id' => $systemId > 0 ? $systemId : 1,
        ];
    }

    /**
     * @param mixed $list
     * @return list<int>
     */
    protected function intArrayPost(string $key): array
    {
        return $this->intList($this->request->getPost($key));
    }

    /**
     * @param mixed $list
     * @return list<string>
     */
    protected function stringArrayPost(string $key): array
    {
        $v = $this->request->getPost($key);
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $item) {
            $s = trim((string) $item);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param mixed $list
     * @return list<int>
     */
    protected function intList($list): array
    {
        if (!is_array($list)) {
            return [];
        }
        $out = [];
        foreach ($list as $v) {
            $n = (int) $v;
            if ($n > 0) {
                $out[] = $n;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * School name and logo always come from system settings (not user-editable on the form).
     *
     * @param array<string, mixed> $header
     * @return array<string, mixed>
     */
    /**
     * @return array{mode: string, attempt_any_count: int, pairs: list<array{0: int, 1: int}>}
     */
    protected function parseDescriptiveChoiceFromRequest(): array
    {
        $mode = (string) $this->request->getPost('descriptive_choice_mode');
        if (!in_array($mode, ['none', 'attempt_any', 'pairs'], true)) {
            $mode = 'none';
        }

        $pairs = [];
        $raw   = $this->request->getPost('descriptive_pairs_json');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $pair) {
                    if (!is_array($pair) || count($pair) < 2) {
                        continue;
                    }
                    $pairs[] = [(int) $pair[0], (int) $pair[1]];
                }
            }
        }

        return [
            'mode'              => $mode,
            'attempt_any_count' => max(0, min(99, (int) $this->request->getPost('descriptive_attempt_any_count'))),
            'pairs'             => $pairs,
        ];
    }

    protected function applyPaperBranding(array $header): array
    {
        $school     = getSchoolInfo();
        $schoolName = '';
        $logoUrl    = '';

        if (is_object($school)) {
            $schoolName = trim((string) ($school->system_name ?? $school->school_name ?? $school->name ?? ''));
            $logoFile   = trim((string) ($school->logo ?? ''));
            if ($logoFile !== '') {
                $logoUrl = base_url('system-logo/' . $logoFile);
            }
        }

        $header['school_name']      = $schoolName;
        $header['school_campus']    = trim((string) ($this->session->get('campus_name') ?? ''));
        $header['school_logo_url']  = $logoUrl;

        return $header;
    }
}
