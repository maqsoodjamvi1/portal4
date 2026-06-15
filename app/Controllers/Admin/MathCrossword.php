<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Crossword\CrosswordAdaptiveService;
use App\Libraries\Crossword\CrosswordGeneratorFactory;
use App\Libraries\Crossword\CrosswordSetService;

class MathCrossword extends BaseController
{
    private CrosswordSetService $setService;

    public function __construct()
    {
        $this->setService = new CrosswordSetService();
        helper(['form', 'url', 'school']);
        check_permission('admin-math-crossword');
    }

    public function index()
    {
        $campusId = (int) (session('member_campusid') ?? 0);

        return view('admin/math_crossword/index', [
            'gradeOptions'      => $this->gradeOptions(),
            'operationOptions'  => $this->operationOptions(),
            'difficultyOptions' => $this->difficultyOptions(),
            'puzzleTypeOptions' => CrosswordGeneratorFactory::labels(),
            'classes'           => $this->getClasses($campusId),
            'vocabTopics'       => $this->getVocabTopics($campusId),
            'qbTopics'          => $this->getQbTopics($campusId),
            'savedSets'         => $this->setService->listSets($campusId),
            'classSections'     => $this->getClassSections($campusId),
            'tablesReady'       => $this->setService->tablesReady(),
        ]);
    }

    public function generate()
    {
        $puzzleType = (string) $this->request->getPost('puzzle_type');
        $allowed    = array_keys(CrosswordGeneratorFactory::labels());
        if (! in_array($puzzleType, $allowed, true)) {
            $puzzleType = 'math_square';
        }

        $rules = [
            'grade'        => 'required|integer|greater_than[0]|less_than[6]',
            'difficulty'   => 'required|in_list[easy,medium,hard]',
            'puzzle_count' => 'required|integer|greater_than[0]|less_than[13]',
            'per_page'     => 'required|integer|in_list[1,2,4]',
            'puzzle_type'  => 'required|in_list[' . implode(',', $allowed) . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $operations = $this->request->getPost('operations');
        if ($puzzleType !== 'vocab') {
            if (! is_array($operations) || $operations === []) {
                return redirect()->back()->withInput()->with('error', 'Select at least one operation (+, −, ×, or ÷).');
            }
        }

        $allowedOps = ['+', '-', '×', '÷'];
        $operations = is_array($operations)
            ? array_values(array_intersect($operations, $allowedOps))
            : ['+', '-'];

        if ($puzzleType === 'vocab') {
            $topicIds = $this->request->getPost('topic_ids');
            if (! is_array($topicIds) || $topicIds === []) {
                return redirect()->back()->withInput()->with('error', 'Select at least one Vocab Bank topic for vocabulary crosswords.');
            }
        }

        $grade       = (int) $this->request->getPost('grade');
        $difficulty  = (string) $this->request->getPost('difficulty');
        $puzzleCount = (int) $this->request->getPost('puzzle_count');
        $perPage     = (int) $this->request->getPost('per_page');
        $withKey     = (bool) $this->request->getPost('answer_key');
        $title       = trim((string) $this->request->getPost('worksheet_title'));
        $saveSet     = (bool) $this->request->getPost('save_set');
        $bulkClass   = (int) $this->request->getPost('bulk_cls_sec_id');
        $qbTopicId   = (int) $this->request->getPost('qb_topic_id');
        $weakOps     = [];

        $options = [
            'grade'      => $grade,
            'operations' => $operations,
            'difficulty' => $difficulty,
        ];

        if ($puzzleType === 'vocab') {
            $options['topic_ids']  = array_map('intval', $this->request->getPost('topic_ids') ?? []);
            $options['class_id']   = (int) $this->request->getPost('vocab_class_id');
            $options['subject_id'] = (int) $this->request->getPost('vocab_subject_id');
        }

        if ($qbTopicId > 0 && $puzzleType === 'vocab') {
            $adaptive = new CrosswordAdaptiveService();
            $linked   = $adaptive->vocabTopicIdsFromQbTopic($qbTopicId, (int) ($options['class_id'] ?? $grade));
            if ($linked !== []) {
                $options['topic_ids'] = array_values(array_unique(array_merge($options['topic_ids'] ?? [], $linked)));
            }
        }

        $generator = CrosswordGeneratorFactory::make($puzzleType);
        $puzzles   = [];
        $failed    = 0;
        $seen      = [];

        $generateOne = function () use ($generator, $options, $weakOps, &$seen): ?array {
            $opts = $options;
            if ($weakOps !== []) {
                $adaptive = new CrosswordAdaptiveService();
                $opts['operations'] = $adaptive->biasOperations($opts['operations'] ?? ['+', '-'], $weakOps);
            }
            for ($try = 0; $try < 50; $try++) {
                $p = $generator->generate($opts);
                if ($p === null) {
                    continue;
                }
                $hash = md5(json_encode($p['cells'] ?? []));
                if (! isset($seen[$hash])) {
                    $seen[$hash] = true;

                    return $p;
                }
            }

            return $generator->generate($opts);
        };

        $campusId   = (int) (session('member_campusid') ?? 0);
        $clsSecName = '';

        if ($bulkClass > 0 && $this->setService->tablesReady()) {
            $students = $this->studentsForClass($bulkClass);
            if ($students === []) {
                return redirect()->back()->withInput()->with('error', 'No active students found for bulk generation.');
            }
            $clsSecName = $this->classSectionLabel($bulkClass);
            $bulkPuzzles = [];
            foreach ($students as $stu) {
                $p = $generateOne();
                if ($p === null) {
                    $failed++;
                    continue;
                }
                $p['student_name']   = trim(($stu['first_name'] ?? '') . ' ' . ($stu['last_name'] ?? ''));
                $p['roll_no']        = $stu['reg_no'] ?? '';
                $p['profile_photo']  = $stu['profile_photo'] ?? '';
                $p['class_name']     = $clsSecName;
                $bulkPuzzles[]       = $p;
            }
            if ($bulkPuzzles === []) {
                return redirect()->back()->withInput()->with('error', 'Could not build bulk puzzles.');
            }
            $puzzles   = $bulkPuzzles;
            $perPage   = 1;
            $puzzleCount = count($puzzles);
        } else {
            for ($i = 0; $i < $puzzleCount; $i++) {
                $puzzle = $generateOne();
                if ($puzzle === null) {
                    $failed++;
                    continue;
                }
                $puzzles[] = $puzzle;
            }
        }

        if ($puzzles === []) {
            return redirect()->back()->withInput()->with('error', 'Could not build puzzles with these settings. Try easier difficulty or fewer operations.');
        }

        if ($failed > 0) {
            session()->setFlashdata('warning', "{$failed} puzzle(s) could not be generated and were skipped.");
        }

        $schoolMeta = $this->schoolMeta();
        $campusMeta = $this->campusMeta($campusId);
        $settings   = [
            'puzzle_type' => $puzzleType,
            'grade'       => $grade,
            'difficulty'  => $difficulty,
            'operations'  => $operations,
            'per_page'    => $perPage,
        ];

        if ($saveSet && $this->setService->tablesReady()) {
            $setId = $this->setService->saveSet(
                $title !== '' ? $title : CrosswordGeneratorFactory::make($puzzleType)->label(),
                (int) (session('member_campusid') ?? 0),
                (int) (session('member_userid') ?? 0),
                $settings,
                $puzzles
            );
            session()->setFlashdata('success', "Worksheet saved to library (#{$setId}).");
        }

        return view('admin/math_crossword/print', array_merge($schoolMeta, $campusMeta, [
            'puzzles'        => $puzzles,
            'grade'          => $grade,
            'perPage'        => $perPage,
            'withAnswerKey'  => $withKey,
            'worksheetTitle' => $title !== '' ? $title : $this->defaultTitle($puzzleType),
            'operations'     => $operations,
            'difficulty'     => $difficulty,
            'puzzleType'     => $puzzleType,
            'printDate'      => date('d M Y'),
            'clsSecName'     => $clsSecName,
        ]));
    }

    public function library()
    {
        $campusId = (int) (session('member_campusid') ?? 0);

        return view('admin/math_crossword/library', [
            'savedSets'   => $this->setService->listSets($campusId),
            'tablesReady' => $this->setService->tablesReady(),
        ]);
    }

    public function reprint(int $setId)
    {
        $campusId = (int) (session('member_campusid') ?? 0);
        $loaded   = $this->setService->loadSet($setId, $campusId);
        if ($loaded === null) {
            return redirect()->to(site_url('admin/math-crossword/library'))->with('error', 'Worksheet not found.');
        }

        $set      = $loaded['set'];
        $settings = json_decode($set['settings_json'] ?? '{}', true) ?? [];

        $campusId = (int) (session('member_campusid') ?? 0);

        return view('admin/math_crossword/print', array_merge($this->schoolMeta(), $this->campusMeta($campusId), [
            'puzzles'        => $loaded['puzzles'],
            'grade'          => (int) ($set['grade'] ?? 1),
            'perPage'        => (int) ($settings['per_page'] ?? 4),
            'withAnswerKey'  => true,
            'worksheetTitle' => $set['title'] ?? 'Crossword Worksheet',
            'operations'     => $settings['operations'] ?? ['+', '-'],
            'difficulty'     => $settings['difficulty'] ?? 'medium',
            'puzzleType'     => $set['puzzle_type'] ?? 'math_square',
            'printDate'      => date('d M Y'),
            'studentName'    => $set['student_name'] ?? null,
            'clsSecName'     => '',
        ]));
    }

    public function assign()
    {
        $campusId = (int) (session('member_campusid') ?? 0);

        if ($this->request->is('post')) {
            $setId    = (int) $this->request->getPost('set_id');
            $clsSecId = (int) $this->request->getPost('cls_sec_id');
            $dueDate  = trim((string) $this->request->getPost('due_date')) ?: null;

            if ($setId <= 0 || $clsSecId <= 0) {
                return redirect()->back()->withInput()->with('error', 'Select a saved worksheet and class section.');
            }

            if (! $this->setService->tablesReady()) {
                return redirect()->back()->withInput()->with('error', 'Crossword tables not installed. Run migrations.');
            }

            if ($this->setService->loadSet($setId, $campusId) === null) {
                return redirect()->back()->withInput()->with('error', 'Worksheet not found. Save it to the library first, then assign.');
            }

            if (! $this->classSectionBelongsToCampus($clsSecId, $campusId)) {
                return redirect()->back()->withInput()->with('error', 'Invalid class section for your campus.');
            }

            if ($this->assignmentAlreadyExists($setId, $clsSecId, $campusId)) {
                return redirect()->back()->withInput()->with('error', 'This crossword is already assigned to the selected class section.');
            }

            $assignmentId = $this->setService->assignToClass(
                $setId,
                $clsSecId,
                $campusId,
                (int) (session('member_userid') ?? 0),
                $dueDate
            );

            if ($assignmentId <= 0) {
                return redirect()->back()->withInput()->with('error', 'Could not save assignment. Please try again.');
            }

            $redirectUrl = site_url('admin/math-crossword/assign')
                . '?set_id=' . $setId
                . '&cls_sec_id=' . $clsSecId;

            return redirect()->to($redirectUrl)
                ->with('success', "Crossword assigned to class (assignment #{$assignmentId}). Students can solve it from the student portal.")
                ->with('assignment_id', $assignmentId);
        }

        $filterSetId = (int) ($this->request->getGet('set_id') ?? 0);
        $filterCls   = (int) ($this->request->getGet('cls_sec_id') ?? 0);

        return view('admin/math_crossword/assign', [
            'savedSets'         => $this->setService->listSets($campusId),
            'assignments'       => $this->fetchAssignments($campusId, $filterSetId, $filterCls),
            'allAssignments'    => $this->fetchAssignments($campusId),
            'classSections'     => $this->getClassSections($campusId),
            'tablesReady'       => $this->setService->tablesReady(),
            'preselectedSetId'  => $filterSetId,
            'preselectedClsSec' => $filterCls,
        ]);
    }

    public function report(int $assignmentId)
    {
        $attempts = $this->setService->attemptReport($assignmentId);

        return view('admin/math_crossword/report', [
            'attempts'     => $attempts,
            'assignmentId' => $assignmentId,
        ]);
    }

    public function assignmentsAjax()
    {
        $campusId  = (int) (session('member_campusid') ?? 0);
        $setId     = (int) $this->request->getGet('set_id');
        $clsSecId  = (int) $this->request->getGet('cls_sec_id');

        if (! $this->setService->tablesReady()) {
            return $this->response->setJSON(['assignments' => []]);
        }

        return $this->response->setJSON([
            'assignments' => $this->fetchAssignments($campusId, $setId, $clsSecId),
        ]);
    }

    public function vocabTopicsAjax()
    {
        $classId   = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');
        $campusId  = (int) (session('member_campusid') ?? 0);

        return $this->response->setJSON($this->getVocabTopics($campusId, $classId, $subjectId));
    }

    /** @return array<int, string> */
    private function gradeOptions(): array
    {
        return [1 => 'Grade 1', 2 => 'Grade 2', 3 => 'Grade 3', 4 => 'Grade 4', 5 => 'Grade 5'];
    }

    /** @return array<string, string> */
    private function operationOptions(): array
    {
        return [
            '+' => 'Addition (+)',
            '-' => 'Subtraction (−)',
            '×' => 'Multiplication (×)',
            '÷' => 'Division (÷)',
        ];
    }

    /** @return array<string, string> */
    private function difficultyOptions(): array
    {
        return ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];
    }

    private function defaultTitle(string $puzzleType): string
    {
        return match ($puzzleType) {
            'vocab'              => 'Vocabulary Crossword',
            'mini_5x5'           => 'Mini Maths Puzzle',
            'missing_operator'   => 'Missing Operator Puzzle',
            default              => 'Maths Puzzle Across–Down',
        };
    }

    /** @return array{schoolName:string, schoolLogo:?string} */
    private function schoolMeta(): array
    {
        $schoolInfo = function_exists('getSchoolInfo') ? getSchoolInfo() : null;
        $schoolName = 'School';
        $schoolLogo = null;

        if (is_object($schoolInfo)) {
            $schoolName = trim((string) ($schoolInfo->system_name ?? $schoolInfo->school_name ?? 'School'));
            $schoolLogo = $schoolInfo->logo ?? $schoolInfo->school_logo ?? null;
        } elseif (is_array($schoolInfo)) {
            $schoolName = trim((string) ($schoolInfo['system_name'] ?? $schoolInfo['school_name'] ?? 'School'));
            $schoolLogo = $schoolInfo['logo'] ?? $schoolInfo['school_logo'] ?? null;
        }

        if (is_string($schoolLogo) && $schoolLogo !== '' && ! filter_var($schoolLogo, FILTER_VALIDATE_URL)) {
            $schoolLogo = base_url('system-logo/' . ltrim($schoolLogo, '/'));
        }

        return [
            'schoolName' => $schoolName !== '' ? $schoolName : 'School',
            'schoolLogo' => is_string($schoolLogo) && $schoolLogo !== '' ? $schoolLogo : null,
        ];
    }

    /** @return array{campusName:string, campusLocation:string} */
    private function campusMeta(int $campusId): array
    {
        if ($campusId <= 0) {
            return ['campusName' => '', 'campusLocation' => ''];
        }

        $row = \Config\Database::connect()
            ->table('campus')
            ->select('campus_name, location')
            ->where('campus_id', $campusId)
            ->limit(1)
            ->get()
            ->getRowArray();

        return [
            'campusName'     => trim((string) ($row['campus_name'] ?? '')),
            'campusLocation' => trim((string) ($row['location'] ?? '')),
        ];
    }

    private function classSectionLabel(int $clsSecId): string
    {
        if ($clsSecId <= 0) {
            return '';
        }

        $row = \Config\Database::connect()
            ->table('class_section cs')
            ->select('c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.cls_sec_id', $clsSecId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return '';
        }

        return trim(($row['class_name'] ?? '') . ' - ' . ($row['section_name'] ?? ''), ' -');
    }

    /** @return list<array<string, mixed>> */
    private function getClasses(int $campusId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('classes c')
            ->select('c.class_id, c.class_name')
            ->where('c.status', 1);

        // classes has no campus_id — scope via class_section when available
        if ($campusId > 0 && $db->fieldExists('campus_id', 'class_section')) {
            $builder->join('class_section cs', 'cs.class_id = c.class_id')
                ->where('cs.campus_id', $campusId)
                ->where('cs.status', 1)
                ->groupBy('c.class_id, c.class_name');
        }

        return $builder->orderBy('c.class_id')->get()->getResultArray();
    }

    private function assignmentAlreadyExists(int $setId, int $clsSecId, int $campusId): bool
    {
        if (method_exists($this->setService, 'assignmentExists')) {
            return $this->setService->assignmentExists($setId, $clsSecId);
        }

        return $this->fetchAssignments($campusId, $setId, $clsSecId) !== [];
    }

    /** @return list<array<string, mixed>> */
    private function fetchAssignments(int $campusId, int $setId = 0, int $clsSecId = 0): array
    {
        $svc = $this->setService;

        if (method_exists($svc, 'listAssignments')) {
            return $svc->listAssignments($campusId, $setId, $clsSecId);
        }

        $ref = new \ReflectionMethod($svc, 'listAssignmentsForCampus');
        if ($ref->getNumberOfParameters() >= 3) {
            return $svc->listAssignmentsForCampus($campusId, $setId, $clsSecId);
        }

        $rows = $svc->listAssignmentsForCampus($campusId, 200);

        return array_values(array_filter($rows, static function (array $row) use ($setId, $clsSecId): bool {
            if ($setId > 0 && (int) ($row['set_id'] ?? 0) !== $setId) {
                return false;
            }
            if ($clsSecId > 0 && (int) ($row['cls_sec_id'] ?? 0) !== $clsSecId) {
                return false;
            }

            return true;
        }));
    }

    private function classSectionBelongsToCampus(int $clsSecId, int $campusId): bool
    {
        if ($clsSecId <= 0) {
            return false;
        }

        $db = \Config\Database::connect();
        $builder = $db->table('class_section')
            ->where('cls_sec_id', $clsSecId)
            ->where('status', 1);

        if ($campusId > 0 && $db->fieldExists('campus_id', 'class_section')) {
            $builder->where('campus_id', $campusId);
        }

        return $builder->countAllResults() > 0;
    }

    /** @return list<array<string, mixed>> */
    private function getClassSections(int $campusId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('class_section cs')
            ->select('cs.cls_sec_id, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.status', 1);

        if ($campusId > 0 && $db->fieldExists('campus_id', 'class_section')) {
            $builder->where('cs.campus_id', $campusId);
        }

        return $builder->orderBy('c.class_id, s.section_id')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    private function getVocabTopics(int $campusId, int $classId = 0, int $subjectId = 0): array
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('vocab_topics')) {
            return [];
        }

        $b = $db->table('vocab_topics vt')
            ->select('vt.id, vt.topic_name, vt.class_id, vt.subject_id');

        if ($classId > 0) {
            $b->where('vt.class_id', $classId);
        }
        if ($subjectId > 0) {
            $b->where('vt.subject_id', $subjectId);
        }

        return $b->orderBy('vt.topic_name')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    private function getQbTopics(int $campusId): array
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('qb_topics')) {
            return [];
        }

        return $db->table('qb_topics')
            ->select('id, topic_name, class_id')
            ->orderBy('topic_name')
            ->limit(200)
            ->get()
            ->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    private function studentsForClass(int $clsSecId): array
    {
        return \Config\Database::connect()
            ->table('student_class sc')
            ->select('s.student_id, s.reg_no, s.first_name, s.last_name, s.profile_photo')
            ->join('students s', 's.student_id = sc.student_id')
            ->where('sc.cls_sec_id', $clsSecId)
            ->where('sc.status', 1)
            ->where('s.status', 1)
            ->orderBy('s.reg_no')
            ->get()
            ->getResultArray();
    }
}
