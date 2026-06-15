<?php

namespace Config;

/**
 * Default permission bundles per role.
 *
 * Modules are resolved against live rows in `permissions.permKey`.
 * Roles reference modules; the seeder expands them to perm IDs per plan.
 */
class RolePermissionMap
{
    /**
     * Permission bundles keyed by module id.
     *
     * @var array<string, array{keys?: list<string>, prefixes?: list<string>}>
     */
    public static array $modules = [
        'rbac' => [
            'keys' => [
                'admin-roles',
                'admin-add-role',
                'admin-edit-role',
                'admin-del-role',
                'admin-permissions',
                'admin-add-permission',
                'admin-edit-permission',
                'admin-del-permission',
            ],
        ],
        /** Billing & Admin sidebar — Super Admin only. */
        'billing_admin' => [
            'keys' => [
                'admin-bill-type',
                'admin-bill-amount',
                'admin-bill-plan-months',
                'admin-campus-chalan-pay',
                'admin-pay-campus-bill',
                'admin-campus-plans',
                'admin-pay-system-bill',
                'admin-ci-session_view',
            ],
            'prefixes' => [
                'admin-add-bill',
                'admin-edit-bill',
                'admin-del-bill',
            ],
        ],
        'system' => [
            'keys' => [
                'admin-pay-system-bill',
                'admin-custom-campus',
                'admin-add-custom-campus',
                'admin-campus-plans',
                'admin-add-campus-plan',
                'admin-add-system-profile',
                'admin-view-global-session',
                'admin-campus-bill',
                'admin-ci-session_view',
            ],
        ],
        'campus' => [
            'prefixes' => ['admin-campus'],
            'keys' => [
                'admin-add-campus-profile',
                'admin-enquiry',
                'admin-add-enquiry',
                'admin-campus-settings',
            ],
        ],
        'dashboard' => [
            'prefixes' => ['admin-db-'],
        ],
        'dashboard_teacher' => [
            'keys' => [
                'admin-db-teacher',
                'admin-db-attendance',
                'admin-db-session',
                'admin-db-term',
                'admin-db-week',
            ],
        ],
        'dashboard_finance' => [
            'keys' => [
                'admin-db-fee-collection',
                'admin-db-expense',
                'admin-db-session',
                'admin-db-term',
            ],
        ],
        'dashboard_ops' => [
            'keys' => [
                'admin-db-attendance',
                'admin-db-students',
                'admin-db-teacher',
                'admin-db-session',
                'admin-db-term',
                'admin-db-week',
                'admin-db-exam',
                'admin-health-bmi',
                'admin-health-bmi-dashboard',
                'admin-health-alerts',
            ],
        ],
        'academic_setup' => [
            'prefixes' => [
                'admin-academic-session',
                'admin-add-academic-session',
                'admin-edit-academic-session',
                'admin-del-academic-session',
                'admin-terms',
                'admin-add-terms',
                'admin-edit-terms',
                'admin-del-terms',
                'admin-terms-sessions',
                'admin-add-terms-session',
                'admin-edit-terms-session',
                'admin-del-terms-session',
                'admin-term-weeks',
                'admin-add-term-week',
                'admin-edit-term-week',
            ],
        ],
        'classes' => [
            'prefixes' => [
                'admin-classes',
                'admin-sections',
                'admin-subjects',
                'admin-class-section',
                'admin-class-subjects',
                'admin-section-subjects',
                'admin-add-class',
                'admin-edit-class',
                'admin-del-class',
                'admin-add-section',
                'admin-edit-section',
                'admin-add-subject',
                'admin-edit-subject',
                'admin-add-class-section',
                'admin-edit-class-section',
                'admin-add-class-subjects',
                'admin-edit-class-subjects',
                'admin-add-section-subjects',
                'admin-edit-section-subjects',
                'admin-class-subject-category',
                'admin-add-class-subject-category',
                'admin-edit-class-subject-category',
                'admin-subject-category',
                'admin-add-subject-category',
                'admin-edit-subject-category',
            ],
        ],
        'students' => [
            'prefixes' => [
                'admin-students',
                'admin-add-student',
                'admin-edit-student',
                'admin-del-student',
                'admin-attachment-types',
                'admin-add-attachment-types',
                'admin-edit-attachment-types',
            ],
            'keys' => [
                'admin-student-class',
                'admin-add-student-class',
                'admin-edit-student-class',
                'admin-student-id-cards',
                'admin-students-contact-list',
                'admin-students-results',
                'admin-students-subject-results',
                'admin-studentsbulk',
                'admin-result-cards',
                'admin-admit-datesheet',
                'admin-add-student-registration',
                'admin-student-registration',
            ],
        ],
        'students_read' => [
            'keys' => [
                'admin-students',
                'admin-students-contact-list',
                'admin-student-class',
            ],
        ],
        'staff' => [
            'prefixes' => [
                'admin-users',
                'admin-add-user',
                'admin-edit-user',
                'admin-employees',
                'admin-add-employee',
                'admin-edit-employee',
                'admin-teacher-subjects',
                'admin-add-teacher-subject',
                'admin-edit-teacher-subject',
                'admin-teacher-sections',
                'admin-add-teacher-section',
                'admin-edit-teacher-section',
            ],
        ],
        'staff_teacher_assign' => [
            'prefixes' => [
                'admin-teacher-subjects',
                'admin-add-teacher-subject',
                'admin-edit-teacher-subject',
                'admin-teacher-sections',
                'admin-add-teacher-section',
                'admin-edit-teacher-section',
            ],
        ],
        'messages' => [
            'prefixes' => [
                'admin-messages',
                'admin-bulk-messages',
                'admin-defaulter-message',
                'admin-result-message',
                'admin-update-message-templates',
                'admin-add-messages',
                'admin-edit-messages',
            ],
        ],
        'attendance' => [
            'prefixes' => [
                'admin-student-attendance',
                'admin-add-student-attendance',
                'admin-edit-student-attendance',
                'admin-del-attendance',
                'admin-add-student-absentees',
                'admin-add-student-latecomming',
                'admin-add-student-earlyleft',
                'admin-add-student-leaves',
                'admin-student-leaves',
                'admin-attendance-monthly-report',
                'admin-emp-attendance-monthly-report',
                'admin-add-employee-attendance',
                'admin-edit-employee-attendance',
            ],
        ],
        'attendance_entry' => [
            'prefixes' => [
                'admin-add-student-attendance',
                'admin-edit-student-attendance',
                'admin-add-student-absentees',
                'admin-add-student-latecomming',
                'admin-add-student-earlyleft',
                'admin-add-student-leaves',
                'admin-student-leaves',
                'admin-student-attendance',
            ],
        ],
        'fee_setup' => [
            'prefixes' => [
                'admin-fee-type',
                'admin-fee-amount',
                'admin-fee-plan-months',
                'admin-add-fee-type',
                'admin-edit-fee-type',
                'admin-del-fee-type',
                'admin-add-fee-amount',
                'admin-edit-fee-amount',
                'admin-add-fee-plan-months',
                'admin-edit-fee-plan-months',
                'admin-fee-setup',
                'admin-fee-management',
                'admin-add-transport-fee-type',
                'admin-edit-transport-fee-type',
                'admin-transport-fee-type',
            ],
        ],
        'fee_ops' => [
            'prefixes' => [
                'admin-fee-chalan',
                'admin-add-fee-chalan',
                'admin-edit-fee-chalan',
                'admin-del-fee-chalan',
                'admin-fee-chalan-balance',
                'admin-fee-chalan-pay',
                'admin-add-fee-chalan-pay',
                'admin-edit-fee-chalan-pay',
                'admin-fee-chalan-pdf',
                'admin-fee-sibling-history',
                'admin-defaulter-message',
            ],
        ],
        'accounts' => [
            'prefixes' => [
                'admin-accounts',
                'admin-account-heads',
                'admin-account-expenses',
                'admin-account-reports',
                'admin-add-account-heads',
                'admin-edit-account-heads',
                'admin-add-account-expenses',
                'admin-edit-account-expenses',
                'admin-asset-heads',
                'admin-assets',
                'admin-add-asset-heads',
                'admin-edit-asset-heads',
                'admin-add-assets',
                'admin-edit-assets',
                'admin-assets-report',
                'admin-expense-reports',
                'admin-monthly-expense-reports',
                'admin-profit-loss-reports',
                'admin-finance-accounts',
                'admin-add-finance-accounts',
                'admin-edit-finance-accounts',
                'admin-cash-flow-report',
            ],
        ],
        'accounts_expenses' => [
            'prefixes' => [
                'admin-account-expenses',
                'admin-add-account-expenses',
                'admin-edit-account-expenses',
                'admin-expense-reports',
                'admin-monthly-expense-reports',
            ],
        ],
        'exams' => [
            'prefixes' => [
                'admin-exams',
                'admin-datesheet',
                'admin-add-datesheet',
                'admin-edit-datesheet',
                'admin-del-datesheet',
                'admin-grades',
                'admin-add-grades',
                'admin-edit-grades',
                'admin-grading-policy',
                'admin-add-grading-policy',
                'admin-edit-grading-policy',
                'admin-tests',
                'admin-add-tests',
                'admin-edit-tests',
                'admin-test-series',
                'admin-add-test-series',
                'admin-edit-test-series',
                'admin-add-test-result',
                'admin-test-result-cards',
            ],
        ],
        'results' => [
            'prefixes' => [
                'admin-students-results',
                'admin-students-subject-results',
                'admin-add-students-result',
                'admin-edit-students-result',
                'admin-add-students-subject-results',
                'admin-edit-students-subject-results',
                'admin-students-weekly-progress',
                'admin-add-students-weekly-progress',
                'admin-edit-students-weekly-progress',
                'admin-add-results-compilation',
            ],
            'keys' => ['admin-result-cards', 'admin-results'],
        ],
        'planning' => [
            'prefixes' => [
                'admin-top-level-planning',
                'admin-add-top-level-planning',
                'admin-edit-top-level-planning',
                'admin-weekly-planning',
                'admin-add-weekly-planning',
                'admin-edit-weekly-planning',
                'admin-lesson-plan',
                'admin-add-lesson-plan',
                'admin-edit-lesson-plan',
                'admin-worksheet',
                'admin-add-worksheet',
                'admin-edit-worksheet',
            ],
        ],
        'planning_view' => [
            'prefixes' => [
                'admin-weekly-planning',
                'admin-top-level-planning',
            ],
        ],
        'diary' => [
            'prefixes' => [
                'admin-classdairy',
                'admin-add-classdairy',
                'admin-edit-classdairy',
            ],
        ],
        'timetable' => [
            'prefixes' => [
                'admin-timetable',
                'admin-add-timetable',
                'admin-timetable-edit',
                'admin-slots',
                'admin-add-slot',
                'admin-edit-slot',
                'admin-school-timing',
                'admin-add-school-timing-type',
                'admin-edit-school-timing-type',
            ],
        ],
        'notices' => [
            'prefixes' => [
                'admin-notices',
                'admin-add-notices',
                'admin-edit-notices',
                'admin-student-notices',
                'admin-add-student-notices',
                'admin-edit-student-notices',
                'admin-student-complaints',
                'admin-add-student-complaint',
            ],
        ],
        'reports_academic' => [
            'keys' => [
                'admin-attendance-monthly-report',
                'admin-emp-attendance-monthly-report',
                'admin-activity',
                'admin-activity-review',
            ],
        ],
        'reports_finance' => [
            'prefixes' => [
                'admin-student-fee-report',
                'admin-defaulter-student-fee-report',
                'admin-family-fee-report',
                'admin-family-fee-history',
                'admin-report-by-fee-type',
                'admin-report-by-student-fee',
                'admin-expense-reports',
                'admin-monthly-expense-reports',
                'admin-profit-loss-reports',
                'admin-assets-report',
            ],
        ],
        'reports_finance_ops' => [
            'prefixes' => [
                'admin-student-fee-report',
                'admin-family-fee-report',
                'admin-family-fee-history',
                'admin-report-by-student-fee',
                'admin-expense-reports',
                'admin-monthly-expense-reports',
            ],
        ],
        'quizzes' => [
            'prefixes' => [
                'admin-quiz',
                'admin-add-quiz',
                'admin-edit-quiz',
                'admin-del-quiz',
                'admin-quiz-questions',
                'admin-add-quiz-question',
                'admin-add-quiz-questions',
                'admin-view-quiz-result',
                'admin-questions',
                'admin-add-question',
                'admin-add-questions',
                'admin-edit-question',
            ],
        ],
        /** Quizzes list + vocabulary report only (directors / principal). */
        'quizzes_director' => [
            'prefixes' => [
                'admin-quiz',
                'admin-add-quiz',
                'admin-edit-quiz',
                'admin-del-quiz',
                'admin-quiz-questions',
                'admin-add-quiz-question',
                'admin-add-quiz-questions',
                'admin-view-quiz-result',
            ],
            'keys' => [
                'admin-vocab-report',
            ],
        ],
        /** QB, vocab setup, math crossword — Super Admin menu only. */
        'quizzes_elearning_super' => [
            'keys' => [
                'admin-question-bank-overview',
                'admin-question-bank-proof',
                'admin-question-bank-ai',
                'admin-question-paper',
                'admin-qb-topics',
                'admin-qb-board-publishers',
                'admin-vocab-topics',
                'admin-vocab-bank',
                'admin-vocab-words',
                'admin-math-crossword',
                'admin-math-worksheet',
                'admin-word-search',
            ],
            'prefixes' => [
                'admin-add-questions',
                'admin-edit-questions',
                'admin-del-questions',
                'admin-topic-skills',
            ],
        ],
        'quizzes_teacher' => [
            'keys' => [
                'admin-quiz',
                'admin-quiz-assign',
            ],
            'prefixes' => [
                'admin-quiz',
                'admin-add-quiz',
                'admin-edit-quiz',
                'admin-view-quiz-result',
                'admin-questions',
                'admin-add-question',
                'admin-edit-question',
            ],
        ],
        'academy' => [
            'prefixes' => ['admin-academy'],
        ],
        'health' => [
            'keys' => [
                'admin-health-bmi',
                'admin-health-bmi-dashboard',
                'admin-health-bmi-records',
                'admin-health-alerts',
                'admin-health-nutrition',
                'admin-health-reports',
            ],
        ],
        'employee_face' => [
            'keys' => [
                'admin-employee-face-attendance',
                'admin-employee-face-management',
            ],
        ],
        'math_crossword' => [
            'keys' => ['admin-math-crossword'],
        ],
        'math_worksheet' => [
            'keys' => ['admin-math-worksheet'],
        ],
        'word_search' => [
            'keys' => ['admin-word-search'],
        ],
        'question_bank' => [
            'keys' => [
                'admin-question-bank-overview',
                'admin-question-bank-proof',
                'admin-question-bank-ai',
                'admin-question-paper',
            ],
        ],
        'sports' => [
            'keys' => [
                'admin-sports-events',
                'admin-sports-teams',
                'admin-sports-mapping',
                'admin-sports-reports',
            ],
        ],
        'hifz' => [
            'keys' => [
                'admin-hifz-sections',
                'admin-hifz-students',
                'admin-hifz-teachers',
                'admin-hifz-recitation',
                'admin-hifz-reports',
            ],
        ],
        'quiz_battles' => [
            'keys' => ['admin-quiz-battles'],
        ],
        'users_bulk' => [
            'keys' => ['admin-users-bulk-info'],
        ],
    ];

    /**
     * Default role → permission mapping.
     *
     * @var array<string, array{
     *     all?: bool,
     *     modules?: list<string>,
     *     include_keys?: list<string>,
     *     exclude_modules?: list<string>,
     *     exclude_keys?: list<string>,
     *     exclude_prefixes?: list<string>
     * }>
     */
    public static array $roles = [
        'Super Admin' => [
            'all' => true,
        ],
        'Director System' => [
            'all' => true,
            'exclude_modules' => [
                'rbac',
                'system',
                'quizzes_elearning_super',
                'question_bank',
                'math_crossword',
                'math_worksheet',
                'word_search',
            ],
            'exclude_prefixes' => [
                'admin-question',
            ],
        ],
        'Director Campus' => [
            'all'            => true,
            'exclude_modules'=> [
                'rbac',
                'system',
                'quizzes_elearning_super',
                'question_bank',
                'math_crossword',
                'math_worksheet',
                'word_search',
            ],
            'exclude_prefixes' => [
                'admin-question',
            ],
        ],
        'Principal' => [
            'modules' => [
                'dashboard_ops',
                'dashboard',
                'campus',
                'academic_setup',
                'classes',
                'students',
                'messages',
                'attendance',
                'exams',
                'results',
                'planning',
                'diary',
                'timetable',
                'notices',
                'reports_academic',
                'staff',
                'quizzes_director',
                'academy',
                'health',
                'employee_face',
                'sports',
                'hifz',
            ],
            'exclude_keys' => [
                'admin-pay-system-bill',
                'admin-custom-campus',
                'admin-add-system-profile',
                'admin-view-global-session',
            ],
        ],
        'Academic Coordinator' => [
            'modules' => [
                'dashboard',
                'academic_setup',
                'classes',
                'students',
                'attendance',
                'exams',
                'results',
                'planning',
                'diary',
                'timetable',
                'notices',
                'reports_academic',
                'staff_teacher_assign',
                'quizzes',
                'health',
                'question_bank',
                'hifz',
            ],
        ],
        'Teacher' => [
            'modules' => [
                'dashboard_teacher',
                'diary',
                'attendance_entry',
                'planning_view',
                'results',
                'students_read',
                'quizzes_teacher',
                'health',
            ],
            'include_keys' => [
                'admin-add-top-level-planning',
                'admin-quiz',
                'admin-quiz-assign',
            ],
            'exclude_keys' => [
                'admin-del-attendance',
                'admin-add-students-weekly-progress',
            ],
        ],
        'Director Finance' => [
            'modules' => [
                'dashboard_finance',
                'campus',
                'fee_setup',
                'fee_ops',
                'accounts',
                'reports_finance',
            ],
            'exclude_keys' => ['admin-add-system-profile', 'admin-view-global-session'],
        ],
        'Accountant' => [
            'modules' => [
                'dashboard_finance',
                'fee_ops',
                'accounts_expenses',
                'reports_finance_ops',
            ],
        ],
    ];

    /** Canonical role names (must match DefaultRolesSeeder). */
    public static array $canonicalRoleNames = [
        'Super Admin',
        'Director System',
        'Director Campus',
        'Principal',
        'Academic Coordinator',
        'Teacher',
        'Director Finance',
        'Accountant',
    ];
}
