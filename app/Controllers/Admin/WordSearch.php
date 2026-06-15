<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\WordSearch\WordSearchGenerator;
use App\Libraries\WordSearch\WordSearchSetService;

class WordSearch extends BaseController
{
    private WordSearchSetService $setService;
    private WordSearchGenerator $generator;

    public function __construct()
    {
        $this->setService = new WordSearchSetService();
        $this->generator  = new WordSearchGenerator();
        helper(['form', 'url', 'school']);
        check_any_permission(['admin-word-search', 'admin-math-crossword', 'admin-exams']);
    }

    public function index()
    {
        $campusId = (int) (session('member_campusid') ?? 0);

        return view('admin/word_search/index', [
            'gradeOptions'    => $this->gradeOptions(),
            'classes'         => $this->getClasses($campusId),
            'vocabTopics'     => $this->getVocabTopics($campusId),
            'savedSets'       => $this->setService->listSets($campusId),
            'classSections'   => $this->getClassSections($campusId),
            'tablesReady'     => $this->setService->tablesReady(),
        ]);
    }

    public function generate()
    {
        $rules = [
            'grade'          => 'required|integer|greater_than[0]|less_than[6]',
            'word_count'     => 'required|integer|greater_than[2]|less_than[21]',
            'direction_mode' => 'required|in_list[hv,hvd]',
            'grid_size'      => 'permit_empty|integer|greater_than[9]|less_than[21]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $topicIds    = $this->request->getPost('topic_ids');
        $manualWords = trim((string) $this->request->getPost('manual_words'));
        if ((! is_array($topicIds) || $topicIds === []) && $manualWords === '') {
            return redirect()->back()->withInput()->with('error', 'Select Vocab Bank topic(s) or enter manual words.');
        }

        $grade          = (int) $this->request->getPost('grade');
        $wordCount      = (int) $this->request->getPost('word_count');
        $directionMode  = (string) $this->request->getPost('direction_mode');
        $gridSize       = (int) $this->request->getPost('grid_size');
        $withKey        = (bool) $this->request->getPost('answer_key');
        $title          = trim((string) $this->request->getPost('worksheet_title'));
        $saveSet        = (bool) $this->request->getPost('save_set');
        $bulkClass      = (int) $this->request->getPost('bulk_cls_sec_id');
        $campusId       = (int) (session('member_campusid') ?? 0);
        $clsSecName     = '';

        $genOptions = [
            'topic_ids'      => is_array($topicIds) ? array_map('intval', $topicIds) : [],
            'class_id'       => (int) $this->request->getPost('vocab_class_id'),
            'subject_id'     => (int) $this->request->getPost('vocab_subject_id'),
            'manual_words'   => $manualWords,
            'word_count'     => $wordCount,
            'direction_mode' => $directionMode,
            'grid_size'      => $gridSize,
        ];

        $puzzles = [];
        $failed  = 0;

        if ($bulkClass > 0) {
            $students = $this->studentsForClass($bulkClass);
            if ($students === []) {
                return redirect()->back()->withInput()->with('error', 'No active students found for bulk generation.');
            }
            $clsSecName = $this->classSectionLabel($bulkClass);
            foreach ($students as $stu) {
                $p = $this->generator->generate($genOptions);
                if ($p === null) {
                    $failed++;
                    continue;
                }
                $p['student_name']  = trim(($stu['first_name'] ?? '') . ' ' . ($stu['last_name'] ?? ''));
                $p['roll_no']       = $stu['reg_no'] ?? '';
                $p['profile_photo'] = $stu['profile_photo'] ?? '';
                $p['class_name']    = $clsSecName;
                $puzzles[]          = $p;
            }
            if ($puzzles === []) {
                return redirect()->back()->withInput()->with('error', 'Could not build word search puzzles for bulk generation.');
            }
        } else {
            $p = $this->generator->generate($genOptions);
            if ($p === null) {
                return redirect()->back()->withInput()->with('error', 'Could not build puzzle. Add more words or reduce word count.');
            }
            $puzzles[] = $p;
        }

        if ($failed > 0) {
            session()->setFlashdata('warning', "{$failed} student puzzle(s) could not be generated and were skipped.");
        }

        $worksheetTitle = $title !== '' ? $title : 'Vocabulary Word Search';
        $settings       = array_merge($genOptions, [
            'grade'          => $grade,
            'direction_mode' => $directionMode,
        ]);

        if ($saveSet && $this->setService->tablesReady()) {
            $userId = (int) (session('member_userid') ?? 0);
            $saved  = 0;
            foreach ($puzzles as $puzzle) {
                $setTitle = $worksheetTitle;
                if (! empty($puzzle['student_name'])) {
                    $setTitle .= ' — ' . $puzzle['student_name'];
                }
                $this->setService->saveSet(
                    $setTitle,
                    $campusId,
                    $userId,
                    $settings,
                    [$puzzle],
                    $puzzle['student_name'] ?? null
                );
                $saved++;
            }
            session()->setFlashdata('success', "{$saved} worksheet(s) saved to library.");
        }

        return view('admin/word_search/print', array_merge($this->schoolMeta(), $this->campusMeta($campusId), [
            'puzzles'        => $puzzles,
            'grade'          => $grade,
            'withAnswerKey'  => $withKey,
            'worksheetTitle' => $worksheetTitle,
            'directionMode'  => $directionMode,
            'printDate'      => date('d M Y'),
            'clsSecName'     => $clsSecName,
        ]));
    }

    public function library()
    {
        $campusId = (int) (session('member_campusid') ?? 0);

        return view('admin/word_search/library', [
            'savedSets'   => $this->setService->listSets($campusId),
            'tablesReady' => $this->setService->tablesReady(),
        ]);
    }

    public function reprint(int $setId)
    {
        $campusId = (int) (session('member_campusid') ?? 0);
        $loaded   = $this->setService->loadSet($setId, $campusId);
        if ($loaded === null) {
            return redirect()->to(site_url('admin/word-search/library'))->with('error', 'Worksheet not found.');
        }

        $set      = $loaded['set'];
        $settings = json_decode($set['settings_json'] ?? '{}', true) ?? [];

        return view('admin/word_search/print', array_merge($this->schoolMeta(), $this->campusMeta($campusId), [
            'puzzles'        => $loaded['puzzles'],
            'grade'          => (int) ($set['grade'] ?? 1),
            'withAnswerKey'  => true,
            'worksheetTitle' => $set['title'] ?? 'Word Search',
            'directionMode'  => $settings['direction_mode'] ?? 'hvd',
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
                return redirect()->back()->withInput()->with('error', 'Word search tables not installed. Run migrations.');
            }
            if ($this->setService->loadSet($setId, $campusId) === null) {
                return redirect()->back()->withInput()->with('error', 'Worksheet not found.');
            }
            if (! $this->classSectionBelongsToCampus($clsSecId, $campusId)) {
                return redirect()->back()->withInput()->with('error', 'Invalid class section for your campus.');
            }
            if ($this->setService->assignmentExists($setId, $clsSecId)) {
                return redirect()->back()->withInput()->with('error', 'This word search is already assigned to the selected class section.');
            }

            $assignmentId = $this->setService->assignToClass(
                $setId,
                $clsSecId,
                $campusId,
                (int) (session('member_userid') ?? 0),
                $dueDate
            );

            if ($assignmentId <= 0) {
                return redirect()->back()->withInput()->with('error', 'Could not save assignment.');
            }

            return redirect()->to(site_url('admin/word-search/assign') . '?set_id=' . $setId . '&cls_sec_id=' . $clsSecId)
                ->with('success', "Word search assigned (assignment #{$assignmentId}).")
                ->with('assignment_id', $assignmentId);
        }

        $filterSetId = (int) ($this->request->getGet('set_id') ?? 0);
        $filterCls   = (int) ($this->request->getGet('cls_sec_id') ?? 0);

        return view('admin/word_search/assign', [
            'savedSets'         => $this->setService->listSets($campusId),
            'assignments'       => $this->setService->listAssignmentsForCampus($campusId, $filterSetId, $filterCls),
            'allAssignments'    => $this->setService->listAssignmentsForCampus($campusId),
            'classSections'     => $this->getClassSections($campusId),
            'tablesReady'       => $this->setService->tablesReady(),
            'preselectedSetId'  => $filterSetId,
            'preselectedClsSec' => $filterCls,
        ]);
    }

    public function report(int $assignmentId)
    {
        return view('admin/word_search/report', [
            'attempts'     => $this->setService->attemptReport($assignmentId),
            'assignmentId' => $assignmentId,
        ]);
    }

    public function assignmentsAjax()
    {
        $campusId = (int) (session('member_campusid') ?? 0);
        $setId    = (int) $this->request->getGet('set_id');
        $clsSecId = (int) $this->request->getGet('cls_sec_id');

        if (! $this->setService->tablesReady()) {
            return $this->response->setJSON(['assignments' => []]);
        }

        return $this->response->setJSON([
            'assignments' => $this->setService->listAssignmentsForCampus($campusId, $setId, $clsSecId),
        ]);
    }

    public function vocabTopicsAjax()
    {
        $classId   = (int) $this->request->getGet('class_id');
        $subjectId = (int) $this->request->getGet('subject_id');

        return $this->response->setJSON($this->getVocabTopics((int) (session('member_campusid') ?? 0), $classId, $subjectId));
    }

    /** @return array<int, string> */
    private function gradeOptions(): array
    {
        return [1 => 'Grade 1', 2 => 'Grade 2', 3 => 'Grade 3', 4 => 'Grade 4', 5 => 'Grade 5'];
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

        return $row === null ? '' : trim(($row['class_name'] ?? '') . ' - ' . ($row['section_name'] ?? ''), ' -');
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
    private function getClasses(int $campusId): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('classes c')
            ->select('c.class_id, c.class_name')
            ->where('c.status', 1);

        if ($campusId > 0 && $db->fieldExists('campus_id', 'class_section')) {
            $builder->join('class_section cs', 'cs.class_id = c.class_id')
                ->where('cs.campus_id', $campusId)
                ->where('cs.status', 1)
                ->groupBy('c.class_id, c.class_name');
        }

        return $builder->orderBy('c.class_id')->get()->getResultArray();
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
