<?php

namespace Config;

/**
 * Maps Admin controllers to permission keys for centralized enforcement.
 */
class AdminControllerPermissions
{
    /** @var list<string> No login required */
    public static array $public = [
        'Login',
    ];

    /** @var list<string> Login required; no specific permission */
    public static array $sessionOnly = [
        'Dashboard',
        'GettingStarted',
        'Fallback',
        'Profile',
        'ProfileStudent',
        'Logout',
    ];

    /**
     * Controller short name => permission key (or list — any match allows access).
     *
     * @var array<string, string|list<string>>
     */
    /** Controllers restricted to Super Admin (Quizzes tooling + Billing & Admin). */
    /**
     * Super-admin tooling controllers → sidebar menu_key (role menu editor).
     *
     * @var array<string, string>
     */
    public static array $superAdminControllerMenuKeys = [
        'QuestionBank'   => 'question-bank.question-bank',
        'QuestionBankAi' => 'question-bank.ai-generate',
        'QuestionPaper'  => 'question-paper.generator',
        'AssessmentBuilder' => 'assessment-builder',
        'QbTopics'            => 'question-bank.topics',
        'QbBoardPublishers'   => 'question-bank.board-publishers',
        'VocabTopics'    => 'vocabulary-bank.topics',
        'MathCrossword'  => 'quizzes.math-crossword',
        'MathWorksheet'  => 'quizzes.math-worksheet',
        'WordSearch'     => 'quizzes.word-search',
    ];

    public static array $superAdminOnlyControllers = [
        'QuestionBank',
        'QuestionBankAi',
        'QuestionPaper',
        'QbTopics',
        'QbBoardPublishers',
        'VocabTopics',
        'MathCrossword',
        'MathWorksheet',
        'WordSearch',
        'Bill_type',
        'Bill_amount',
        'Bill_plan_months',
        'Campus_chalan_pay',
        'Pay_campus_bill',
        'Pay_system_bill',
        'Campus_plans',
        'Ci_session_view',
        'Roles',
        'Permissions',
        'CampusManagement',
    ];

    /**
     * VocabBank actions allowed for Principal (not Director System / Campus).
     *
     * @var array<string, list<string>>
     */
    public static array $vocabBankDirectorMethods = [
        'report',
        'reportdata',
    ];

    public static array $map = [
        'Users'                   => 'admin-users',
        'UsersBulkInfo'           => 'admin-users',
        'QuestionBank'            => ['admin-questions', 'admin-question-bank-overview'],
        'QuestionBankAi'          => ['admin-question-bank-ai', 'admin-questions', 'admin-question-bank-overview', 'admin-exams'],
        'QuestionPaper'           => ['admin-question-paper', 'admin-exams', 'admin-questions'],
        'AssessmentBuilder'       => ['admin-quiz', 'admin-question-paper', 'admin-exams', 'admin-questions'],
        'QbTopics'                => 'admin-qb-topics',
        'QbBoardPublishers'       => 'admin-qb-board-publishers',
        'VocabTopics'             => 'admin-vocab-topics',
        'VocabBank'               => 'admin-vocab-bank',
        'Quizzes'                 => 'admin-quiz',
        'QuizAssign'              => ['admin-quiz', 'admin-quiz-assign'],
        'QuizBattles'             => 'admin-quiz-battles',
        'FeeChalan'               => 'admin-fee-chalan',
        'FeeChalanPay'            => 'admin-fee-chalan-pay',
        'Fee_chalan_pay'          => 'admin-fee-chalan-pay',
        'FeeChalanPay1'           => 'admin-fee-chalan-pay',
        'FeeChalanPay2'           => 'admin-fee-chalan-pay',
        'PrintFeeChalan'          => 'admin-fee-chalan',
        'AdvanceFee'              => 'admin-fee-chalan',
        'FeeChalanBalance'        => 'admin-fee-chalan-balance',
        'FeeSetup'                => ['admin-fee-type', 'admin-fee-amount', 'admin-fee-plan-months', 'admin-fee-setup'],
        'FeeManagement'           => 'admin-fee-management',
        'Exam'                    => 'admin-exams',
        'HealthBmi'               => 'admin-health-bmi',
        'Ajax'                    => 'admin-dashboard',
        'UploadsProxy'            => 'admin-dashboard',
        'StudentsPrint'           => 'admin-students',
        'MathCrossword'           => 'admin-math-crossword',
        'MathWorksheet'           => 'admin-math-worksheet',
        'WordSearch'              => ['admin-word-search', 'admin-math-crossword', 'admin-exams'],
        'SalaryDebug'             => 'admin-users',
        'SalarySettings'          => ['admin-users', 'admin-salary-settings'],
        'SalarySlips'             => ['admin-users', 'admin-salary-slips'],
        'AdvanceSalary'           => ['admin-users', 'admin-salary-advance'],
        'Bonuses'                 => ['admin-users', 'admin-salary-bonuses'],
        'StudentsResultsList'     => 'admin-students-results',
        'Ci_session_view'         => 'admin-ci-session_view',
        'CampusManagement'        => 'admin-permissions',
        'Permissions'             => 'admin-permissions',
        'Roles'                   => 'admin-roles',
        'Students'                => 'admin-students',
        'StudentsAbsentees'       => 'admin-add-student-absentees',
        'StudentsLateComming'     => 'admin-add-student-latecomming',
        'StudentsEarlyLeft'       => 'admin-add-student-earlyleft',
        'Students_leaves'         => ['admin-add-student-leaves', 'admin-student-attendance', 'admin-add-student-attendance'],
        'AcademicSetup'           => 'admin-academic-session',
        'AcademicCalendar'        => 'admin-academic-session',
        'Classdiary'              => 'admin-classdairy',
        'DiaryAnalytics'          => 'admin-classdairy',
        'Timetable'               => 'admin-timetable',
        'Datesheet'               => 'admin-datesheet',
        'DatesheetReport'         => 'admin-datesheet-report',
        'Campus'                  => 'admin-campus',
        'EmployeesAttendance'     => 'admin-student-attendance',
        'EmployeeFaceAttendance'  => ['admin-employee-face-attendance', 'admin-employee-face-management'],
        'FaceAttendance'          => 'admin-emp-attendance-monthly-report',
        'StudentIdCardNew'        => 'admin-student-id-cards',
        'StudentsBulkPhotos'      => 'admin-students',
        'SportsEvents'            => 'admin-sports-events',
        'SportsTeams'             => 'admin-sports-teams',
        'SportsMapping'           => 'admin-sports-mapping',
        'SportsReports'           => 'admin-sports-reports',
        'HifzSections'            => 'admin-hifz-sections',
        'HifzStudents'            => 'admin-hifz-students',
        'HifzTeachers'            => 'admin-hifz-teachers',
        'HifzRecitation'          => 'admin-hifz-recitation',
        'HifzReports'             => 'admin-hifz-reports',
        'GradesSetup'             => 'admin-grades',
        'TeacherSubjects'         => ['admin-teacher-subjects', 'admin-add-teacher-subject'],
        'TeacherSection'          => ['admin-teacher-sections', 'admin-add-teacher-section'],
    ];

    /**
     * Method-specific overrides: controller => method (lowercase) => key(s).
     *
     * @var array<string, array<string, string|list<string>>>
     */
    public static array $methodMap = [
        'Users' => [
            'add'       => 'admin-add-user',
            'edit'      => 'admin-edit-user',
            'save'      => ['admin-add-user', 'admin-edit-user'],
            'delete'    => 'admin-del-user',
            'view'      => [],
            'subjects'  => [],
            'timetable' => [],
            'salary'    => [],
        ],
        'Profile' => [
            'editself'        => [],
            'saveself'        => [],
            'update_password' => [],
        ],
        'Students' => [
            'add'    => 'admin-add-student',
            'edit'   => 'admin-edit-student',
            'save'   => ['admin-add-student', 'admin-edit-student'],
            'delete' => 'admin-del-student',
        ],
        'Roles' => [
            'add'    => 'admin-add-role',
            'edit'   => 'admin-edit-role',
            'delete' => 'admin-del-role',
        ],
        'Permissions' => [
            'add'    => 'admin-add-permission',
            'edit'   => 'admin-edit-permission',
            'delete' => 'admin-del-permission',
        ],
        'FeeChalanPay' => [
            'add'  => 'admin-add-fee-chalan-pay',
            'edit' => 'admin-edit-fee-chalan-pay',
        ],
        'EmployeeFaceAttendance' => [
            'management' => 'admin-employee-face-management',
            'enroll'     => 'admin-employee-face-management',
            'delete'     => 'admin-employee-face-management',
            'index'      => 'admin-employee-face-attendance',
            'mark'       => 'admin-employee-face-attendance',
        ],
        'MathCrossword' => [
            'generate'          => 'admin-math-crossword',
            'library'         => 'admin-math-crossword',
            'reprint'         => 'admin-math-crossword',
            'assign'          => 'admin-math-crossword',
            'report'          => 'admin-math-crossword',
            'vocabtopicsajax' => 'admin-math-crossword',
            'assignmentsajax' => 'admin-math-crossword',
        ],
        'MathWorksheet' => [
            'generate' => 'admin-math-worksheet',
            'library'  => 'admin-math-worksheet',
            'reprint'  => 'admin-math-worksheet',
        ],
        'WordSearch' => [
            'generate'          => ['admin-word-search', 'admin-math-crossword', 'admin-exams'],
            'library'           => ['admin-word-search', 'admin-math-crossword', 'admin-exams'],
            'reprint'           => ['admin-word-search', 'admin-math-crossword', 'admin-exams'],
            'assign'            => ['admin-word-search', 'admin-math-crossword', 'admin-exams'],
            'report'            => ['admin-word-search', 'admin-math-crossword', 'admin-exams'],
            'vocabtopicsajax'   => ['admin-word-search', 'admin-math-crossword', 'admin-exams'],
            'assignmentsajax'   => ['admin-word-search', 'admin-math-crossword', 'admin-exams'],
        ],
        'VocabBank' => [
            'listofwords' => 'admin-vocab-words',
            'report'      => 'admin-vocab-report',
            'reportdata'  => 'admin-vocab-report',
        ],
        'SalarySettings' => [
            'reports' => ['admin-users', 'admin-salary-reports'],
            'generatemonthly' => ['admin-users', 'admin-salary-settings'],
            'save' => ['admin-users', 'admin-salary-settings'],
            'bulkadjustment' => ['admin-users', 'admin-salary-settings'],
            'loadbulkadjustment' => ['admin-users', 'admin-salary-settings'],
            'generatebulkadjustment' => ['admin-users', 'admin-salary-settings'],
            'printbulkslips' => ['admin-users', 'admin-salary-settings'],
            'exportbulkslips' => ['admin-users', 'admin-salary-settings'],
        ],
        'Ajax' => [
            'updatestudentstatus'      => 'admin-edit-student',
            'setboolattributefee'      => 'admin-fee-chalan',
            'setfeetypestatus'         => 'admin-fee-type',
            'setboolattributeexam'     => 'admin-exams',
            'setboolattributetest'     => 'admin-exams',
            'setboolattributeschooltype' => 'admin-users',
            'setboolattributeistrial'  => 'admin-users',
            'setboolattributenotice'   => 'admin-messages',
            'setfieldvalue'            => 'admin-users',
            'setunique'                => 'admin-users',
            'changecampus'             => 'admin-users',
            'selectsession'            => 'admin-academic-session',
            'selectclassfee'           => 'admin-fee-chalan',
            'dismissoptionalmodules'   => 'admin-dashboard',
        ],
        'HealthBmi' => [
            'dashboard'             => 'admin-health-bmi-dashboard',
            'records'               => 'admin-health-bmi-records',
            'alerts'                => 'admin-health-alerts',
            'nutritionSuggestions'  => 'admin-health-nutrition',
            'reports'               => 'admin-health-reports',
            'saveRecord'            => 'admin-edit-student',
            'addNutritionSuggestion'=> 'admin-health-nutrition',
            'updateNutritionSuggestion' => 'admin-health-nutrition',
            'deleteNutritionSuggestion' => 'admin-health-nutrition',
            'generateReport'        => 'admin-health-reports',
            'updateStudentBmi'      => 'admin-edit-student',
        ],
    ];

    /**
     * Longest-prefix wins. Controller class basename must start with prefix.
     *
     * @var array<string, string|list<string>>
     */
    public static array $prefixRules = [
        'StudentsBulk'       => 'admin-students',
        'Students'           => 'admin-students',
        'Student'            => 'admin-students',
        'UsersBulk'          => 'admin-users',
        'User'               => 'admin-users',
        'FeePlan'            => 'admin-fee-plan-months',
        'FeeAmount'          => 'admin-fee-amount',
        'FeeType'            => 'admin-fee-type',
        'FeeSetup'           => 'admin-fee-type',
        'FeeChalan'          => 'admin-fee-chalan',
        'Fee_'               => 'admin-fee-chalan',
        'Fee'                => 'admin-fee-type',
        'Parents'            => 'admin-students',
        'Parent'             => 'admin-students',
        'Teacher'            => 'admin-users',
        'Teachers'           => 'admin-users',
        'Emp'                => 'admin-add-teacher-section',
        'Employee'           => 'admin-student-attendance',
        'Attendance'         => 'admin-add-student-attendance',
        'Account'            => 'admin-accounts',
        'Quiz'               => 'admin-quiz',
        'Question'           => 'admin-questions',
        'Exam'               => 'admin-exams',
        'Test'               => 'admin-test-series',
        'Term'               => 'admin-terms',
        'Class'              => 'admin-classes',
        'Section'            => 'admin-sections',
        'Subject'            => 'admin-subjects',
        'Message'            => 'admin-messages',
        'Notice'             => 'admin-notices',
        'Report'             => 'admin-reports',
        'ProfitLoss'         => 'admin-profit-loss-reports',
        'ProfitLossReport'   => ['admin-cash-flow-report', 'admin-profit-loss-reports'],
        'CampusFinanceAccounts' => ['admin-finance-accounts', 'admin-accounts'],
        'Weekly'             => 'admin-weekly-planning',
        'TopLevel'           => 'admin-top-level-planning',
        'Top_level'          => 'admin-top-level-planning',
        'Scheme'             => 'admin-scheme-of-studies',
        'Worksheet'          => 'admin-worksheet',
        'Video_'             => 'admin-video-lecture',
        'Vocab'              => 'admin-exams',
        'Wp'                 => 'admin-wp-objectives',
        'Grading'            => 'admin-grading-policy',
        'Grade'              => 'admin-grades',
        'School'             => 'admin-school-timing',
        'Slot'               => 'admin-slots',
        'Vehicle'            => 'admin-vehicles',
        'Transport'          => 'admin-transport-fee-type',
        'Pay_'               => 'admin-pay-campus-bill',
        'Pay'                => 'admin-pay-campus-bill',
        'Page'               => 'admin-pages',
        'Recording'          => 'admin-recordings',
        'Academy'            => 'admin-academy',
        'Award'              => 'admin-award-list',
        'Defaulter'          => 'admin-defaulter-student-fee-report',
        'AddBulk'            => 'admin-students',
        'Sports'             => 'admin-sports-events',
        'Salary'             => 'admin-users',
        'AdvanceSalary'      => 'admin-salary-advance',
        'Health'             => 'admin-health-bmi',
        'H_'                 => 'admin-students',
        'Audio'              => 'admin-audio-lecture',
        'Lesson'             => 'admin-lesson-plan',
        'Html2pdf'           => 'admin-students',
    ];

    public static function isPublic(string $controller): bool
    {
        return in_array($controller, self::$public, true);
    }

    public static function isSessionOnly(string $controller): bool
    {
        return in_array($controller, self::$sessionOnly, true);
    }

    public static function menuKeyForSuperAdminController(string $controller): ?string
    {
        return self::$superAdminControllerMenuKeys[$controller] ?? null;
    }

    public static function isSuperAdminOnlyController(string $controller, string $method = 'index'): bool
    {
        if (in_array($controller, self::$superAdminOnlyControllers, true)) {
            return true;
        }

        if ($controller !== 'VocabBank') {
            return false;
        }

        $method = strtolower(trim($method));

        return ! in_array($method, self::$vocabBankDirectorMethods, true);
    }

    /**
     * Resolve permission keys for a controller action (any match allows access).
     *
     * @return list<string>
     */
    public static function resolveKeys(string $controller, string $method = 'index'): array
    {
        if (self::isPublic($controller) || self::isSessionOnly($controller)) {
            return [];
        }

        $method = strtolower(trim($method));

        if (isset(self::$methodMap[$controller][$method])) {
            return self::normalizeKeys(self::$methodMap[$controller][$method]);
        }

        $base = self::resolveBaseKey($controller);
        if ($base === null || $base === '') {
            return [];
        }

        $keys = self::normalizeKeys($base);

        $actionKeys = self::deriveActionKeys($keys[0], $method);
        if ($actionKeys !== []) {
            $keys = array_values(array_unique(array_merge($keys, $actionKeys)));
        }

        return $keys;
    }

    /**
     * Backward-compatible single-key resolver (first key only).
     */
    public static function resolve(string $controller, string $method = 'index'): ?string
    {
        $keys = self::resolveKeys($controller, $method);

        return $keys[0] ?? null;
    }

    /**
     * Collect all keys referenced in this config (for seeding).
     *
     * @return list<string>
     */
    public static function collectMappedKeys(): array
    {
        $keys = [];

        $collect = static function ($value) use (&$keys, &$collect): void {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $collect($item);
                }
                return;
            }

            if (is_string($value) && str_starts_with($value, 'admin-')) {
                $keys[strtolower($value)] = true;
            }
        };

        foreach (self::$map as $value) {
            $collect($value);
        }
        foreach (self::$prefixRules as $value) {
            $collect($value);
        }
        foreach (self::$methodMap as $methods) {
            foreach ($methods as $value) {
                $collect($value);
            }
        }

        return array_keys($keys);
    }

    /**
     * Resolve legacy ?c= dispatcher names to Admin controller class FQCN.
     */
    public static function resolveLegacyControllerClass(string $controllerName): ?string
    {
        $namespace  = 'App\\Controllers\\Admin\\';
        $candidates = [
            $namespace . str_replace('_', '', ucwords($controllerName, '_')),
            $namespace . ucfirst($controllerName),
        ];

        foreach (array_unique($candidates) as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    private static function resolveBaseKey(string $controller): ?string
    {
        if (isset(self::$map[$controller])) {
            $mapped = self::$map[$controller];

            return is_array($mapped) ? ($mapped[0] ?? null) : $mapped;
        }

        $rules = self::$prefixRules;
        uksort($rules, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($rules as $prefix => $perm) {
            if (str_starts_with($controller, $prefix)) {
                return is_array($perm) ? ($perm[0] ?? null) : $perm;
            }
        }

        if (ENVIRONMENT !== 'production') {
            log_message('warning', 'AdminControllerPermissions: unmapped controller {controller}', [
                'controller' => $controller,
            ]);
        }

        return self::guessPermissionKey($controller);
    }

    /**
     * @param string|list<string> $keys
     * @return list<string>
     */
    private static function normalizeKeys(string|array $keys): array
    {
        $list = is_array($keys) ? $keys : [$keys];
        $out  = [];

        foreach ($list as $key) {
            $key = strtolower(trim((string) $key));
            if ($key !== '') {
                $out[] = $key;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Derive likely write-action keys from HTTP method name.
     *
     * @return list<string>
     */
    private static function deriveActionKeys(string $baseKey, string $method): array
    {
        if ($method === '' || $method === 'index') {
            return [];
        }

        $entity = preg_replace('/^admin-/', '', $baseKey) ?? $baseKey;
        $entity = preg_replace('/s$/', '', $entity) ?? $entity;
        $derived = [];

        if (preg_match('/^(add|create|insert|save|store|enroll|generate|import|upload|bulk|toggle|mark|activate|reset)/', $method)) {
            $derived[] = 'admin-add-' . $entity;
        }
        if (preg_match('/^(edit|update|modify)/', $method)) {
            $derived[] = 'admin-edit-' . $entity;
        }
        if (preg_match('/^(delete|remove|del)/', $method)) {
            $derived[] = 'admin-del-' . $entity;
            $derived[] = 'admin-delete-' . $entity;
        }

        return array_values(array_unique($derived));
    }

    private static function guessPermissionKey(string $controller): string
    {
        $normalized = str_replace('_', '-', $controller);
        $kebab      = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1-$2', $normalized));

        return 'admin-' . $kebab;
    }
}
