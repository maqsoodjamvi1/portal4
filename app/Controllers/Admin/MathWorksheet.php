<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MathWorksheet\GradeConfig;
use App\Libraries\MathWorksheet\MathWorksheetGenerator;
use App\Libraries\MathWorksheet\MathWorksheetSetService;
use App\Libraries\MathWorksheet\NumberRangeConfig;

class MathWorksheet extends BaseController
{
    private MathWorksheetSetService $setService;
    private MathWorksheetGenerator $generator;
    private GradeConfig $gradeConfig;
    private NumberRangeConfig $numberRangeConfig;

    public function __construct()
    {
        $this->setService        = new MathWorksheetSetService();
        $this->generator         = new MathWorksheetGenerator();
        $this->gradeConfig       = new GradeConfig();
        $this->numberRangeConfig = new NumberRangeConfig();
        helper(['form', 'url', 'school']);
        check_permission('admin-math-worksheet');
    }

    public function index()
    {
        $campusId = (int) (session('member_campusid') ?? 0);

        return view('admin/math_worksheet/index', [
            'digitOptions'        => $this->digitOptions(),
            'operationOptions'    => $this->operationOptions(),
            'layoutOptions'       => $this->layoutOptions(),
            'missingStyleOptions' => $this->missingStyleOptions(),
            'perPageOptions'      => $this->perPageOptions(),
            'savedSets'           => $this->setService->listSets($campusId),
            'classSections'       => $this->getClassSections($campusId),
            'tablesReady'         => $this->setService->tablesReady(),
        ]);
    }

    public function generate()
    {
        $rules = [
            'number_type'              => 'required|in_list[integer,decimal]',
            'layout'                   => 'required|in_list[horizontal,vertical]',
            'missing_style'            => 'required|in_list[result,operand_a,operand_b,mixed]',
            'problem_count'            => 'required|integer|greater_than[9]|less_than[101]',
            'per_page'                 => 'required|integer|in_list[10,15,20,25,30,40]',
            'operation_mix'            => 'permit_empty|in_list[mixed,separate]',
            'division_mode'            => 'permit_empty|in_list[whole,remainder]',
            'multiplication_mode'      => 'permit_empty|in_list[random,times_table]',
            'operand_digits'           => 'permit_empty|integer|greater_than[0]|less_than[6]',
            'operand_whole_digits'     => 'permit_empty|integer|greater_than[0]|less_than[4]',
            'operand_decimal_digits'   => 'permit_empty|integer|greater_than_equal_to[0]|less_than[5]',
            'operand_min'              => 'permit_empty|decimal',
            'operand_max'              => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $operations = $this->request->getPost('operations');
        if (! is_array($operations) || $operations === []) {
            return redirect()->back()->withInput()->with('error', 'Select at least one operation (+, −, ×, or ÷).');
        }

        $allowedOps = ['+', '-', '×', '÷'];
        $operations = array_values(array_intersect($operations, $allowedOps));

        $numberType         = (string) $this->request->getPost('number_type');
        $layout             = (string) $this->request->getPost('layout');
        $missingStyle       = (string) $this->request->getPost('missing_style');
        $problemCount       = (int) $this->request->getPost('problem_count');
        $perPage            = (int) $this->request->getPost('per_page');
        $withKey            = (bool) $this->request->getPost('answer_key');
        $title              = trim((string) $this->request->getPost('worksheet_title'));
        $saveSet            = (bool) $this->request->getPost('save_set');
        $bulkClass          = (int) $this->request->getPost('bulk_cls_sec_id');
        $operationMix       = (string) ($this->request->getPost('operation_mix') ?: 'mixed');
        $divisionMode       = (string) ($this->request->getPost('division_mode') ?: 'whole');
        $multiplicationMode = (string) ($this->request->getPost('multiplication_mode') ?: 'random');
        $noCarry            = (bool) $this->request->getPost('no_carry');
        $noBorrow           = (bool) $this->request->getPost('no_borrow');
        $noNegative         = (bool) $this->request->getPost('no_negative');

        if ($numberType === 'decimal' && $divisionMode === 'remainder') {
            return redirect()->back()->withInput()->with('error', 'Division with remainder is only available for whole numbers.');
        }

        $maxPerPage = $this->gradeConfig->maxPerPageForLayout($layout);
        if ($perPage > $maxPerPage) {
            return redirect()->back()->withInput()->with('error', "Vertical layout supports at most {$maxPerPage} problems per page.");
        }

        $numberConfig = $this->numberRangeConfig->parseFromInput($this->request->getPost());
        $rangeError   = $this->validateOperandRanges($numberConfig);
        if ($rangeError !== null) {
            return redirect()->back()->withInput()->with('error', $rangeError);
        }

        $genOptions = [
            'number_type'         => $numberType,
            'number_config'       => $numberConfig,
            'operations'          => $operations,
            'layout'              => $layout,
            'missing_style'       => $missingStyle,
            'operation_mix'       => $operationMix,
            'division_mode'       => $divisionMode,
            'multiplication_mode' => $multiplicationMode,
            'no_carry'            => $noCarry,
            'no_borrow'           => $noBorrow,
            'no_negative'         => $noNegative,
        ];

        $worksheets = [];
        $campusId   = (int) (session('member_campusid') ?? 0);
        $clsSecName = '';

        if ($bulkClass > 0) {
            $students = $this->studentsForClass($bulkClass);
            if ($students === []) {
                return redirect()->back()->withInput()->with('error', 'No active students found for bulk generation.');
            }

            $clsSecName = $this->classSectionLabel($bulkClass);
            $failed = 0;
            foreach ($students as $stu) {
                $problems = $this->generator->generate($problemCount, $genOptions);
                if ($problems === []) {
                    $failed++;
                    continue;
                }
                $worksheets[] = [
                    'problems'      => $problems,
                    'student_name'  => trim(($stu['first_name'] ?? '') . ' ' . ($stu['last_name'] ?? '')),
                    'roll_no'       => $stu['reg_no'] ?? '',
                    'profile_photo' => $stu['profile_photo'] ?? '',
                    'class_name'    => $clsSecName,
                ];
            }

            if ($worksheets === []) {
                return redirect()->back()->withInput()->with('error', 'Could not build worksheets for bulk generation.');
            }

            if ($failed > 0) {
                session()->setFlashdata('warning', "{$failed} student worksheet(s) could not be generated and were skipped.");
            }

            $perPage = $problemCount;
        } else {
            $problems = $this->generator->generate($problemCount, $genOptions);
            if ($problems === []) {
                return redirect()->back()->withInput()->with('error', 'Could not build problems with these settings. Widen the number ranges or reduce operations.');
            }

            $worksheets[] = [
                'problems'     => $problems,
                'student_name' => null,
                'roll_no'      => null,
            ];
        }

        $worksheetTitle = $title !== '' ? $title : 'Math Operations Worksheet';
        $numberSummary  = $this->numberRangeConfig->summaryLabel($numberConfig);
        $settings       = array_merge($genOptions, [
            'per_page'       => $perPage,
            'problem_count'  => $problemCount,
            'number_summary' => $numberSummary,
        ]);

        if ($saveSet && $this->setService->tablesReady()) {
            $campusId = (int) (session('member_campusid') ?? 0);
            $userId   = (int) (session('member_userid') ?? 0);
            $saved    = 0;

            foreach ($worksheets as $ws) {
                $setTitle = $worksheetTitle;
                if (! empty($ws['student_name'])) {
                    $setTitle .= ' — ' . $ws['student_name'];
                }
                $this->setService->saveSet(
                    $setTitle,
                    $campusId,
                    $userId,
                    $settings,
                    $ws['problems'],
                    $ws['student_name'] ?? null
                );
                $saved++;
            }

            session()->setFlashdata('success', "{$saved} worksheet(s) saved to library.");
        }

        return view('admin/math_worksheet/print', array_merge($this->schoolMeta(), $this->campusMeta($campusId), [
            'worksheets'     => $worksheets,
            'numberSummary'  => $numberSummary,
            'perPage'        => $perPage,
            'withAnswerKey'  => $withKey,
            'worksheetTitle' => $worksheetTitle,
            'operations'     => $operations,
            'layout'         => $layout,
            'printDate'      => date('d M Y'),
            'clsSecName'     => $clsSecName,
        ]));
    }

    public function library()
    {
        $campusId = (int) (session('member_campusid') ?? 0);

        return view('admin/math_worksheet/library', [
            'savedSets'   => $this->setService->listSets($campusId),
            'tablesReady' => $this->setService->tablesReady(),
        ]);
    }

    public function reprint(int $setId)
    {
        $campusId = (int) (session('member_campusid') ?? 0);
        $loaded   = $this->setService->loadSet($setId, $campusId);

        if ($loaded === null) {
            return redirect()->to(site_url('admin/math-worksheet/library'))->with('error', 'Worksheet not found.');
        }

        $set      = $loaded['set'];
        $settings = json_decode($set['settings_json'] ?? '{}', true) ?? [];

        return view('admin/math_worksheet/print', array_merge($this->schoolMeta(), $this->campusMeta($campusId), [
            'worksheets' => [[
                'problems'     => $loaded['problems'],
                'student_name' => $set['student_name'] ?? null,
                'roll_no'      => null,
            ]],
            'numberSummary'  => $settings['number_summary'] ?? 'Math worksheet',
            'perPage'        => (int) ($settings['per_page'] ?? 20),
            'withAnswerKey'  => true,
            'worksheetTitle' => $set['title'] ?? 'Math Operations Worksheet',
            'operations'     => $settings['operations'] ?? ['+', '-'],
            'layout'         => $set['layout'] ?? ($settings['layout'] ?? 'horizontal'),
            'printDate'      => date('d M Y'),
            'clsSecName'     => '',
        ]));
    }

    /** @param array<string, mixed> $numberConfig */
    private function validateOperandRanges(array $numberConfig): ?string
    {
        $spec = $numberConfig['operand_a'] ?? null;
        if (is_array($spec) && (float) $spec['min'] > (float) $spec['max']) {
            return 'Number range: minimum cannot be greater than maximum.';
        }

        return null;
    }

    /** @return array<int, string> */
    private function digitOptions(): array
    {
        return [
            1 => '1 digit (1–9)',
            2 => '2 digits (10–99)',
            3 => '3 digits (100–999)',
            4 => '4 digits (1,000–9,999)',
            5 => '5 digits (10,000–99,999)',
        ];
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
    private function layoutOptions(): array
    {
        return [
            'horizontal' => 'Horizontal (inline)',
            'vertical'   => 'Vertical (stacked)',
        ];
    }

    /** @return array<string, string> */
    private function missingStyleOptions(): array
    {
        return [
            'result'    => 'Find result (e.g. 12 + 7 = ___)',
            'operand_a' => 'Find first number',
            'operand_b' => 'Find second number',
            'mixed'     => 'Mixed (random per problem)',
        ];
    }

    /** @return array<int, int> */
    private function perPageOptions(): array
    {
        return [10 => 10, 15 => 15, 20 => 20, 25 => 25, 30 => 30, 40 => 40];
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
