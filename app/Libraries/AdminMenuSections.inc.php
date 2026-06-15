<?php

/**
 * AUTO-GENERATED — do not edit by hand.
 * Edit app/Libraries/Menu/*.php then run: php spark menu:build
 */
$sections = [];

// Dashboard
  $sections[] = [
    'key'   => 'dashboard',
    'label' => 'Dashboard',
    'icon'  => 'fas fa-tachometer-alt',
    'url'   => $link('admin/dashboard'),
    'match' => 'admin/dashboard',
    'visible' => true,
  ];

  $schoolinfoMenu = function_exists('getSchoolInfo') ? getSchoolInfo() : null;
  $menuSystemId   = (int) ($schoolinfoMenu->system_id ?? 0);
  $menuCampusId   = (int) (session()->get('member_campusid') ?? 0);
  $menuUserId     = (int) (session()->get('member_userid') ?? 0);
  if ($menuSystemId > 0 && $menuUserId > 0
      && ! \App\Libraries\SchoolSetupProgress::isTeacher($menuUserId, $menuCampusId)
      && ! \App\Libraries\SchoolSetupProgress::isComplete($menuSystemId, $menuCampusId)) {
      $sections[] = [
          'key'     => 'getting-started',
          'label'   => 'Getting Started',
          'icon'    => 'fas fa-route',
          'url'     => $link('admin/getting-started'),
          'match'   => 'admin/getting-started',
          'visible' => true,
      ];
  }

// Profiles
// Get the logged-in user ID from session
$session = session();
$currentUserId = $session->get('member_userid') ?: $session->get('user_id');
$currentUserName = $session->get('first_name') . ' ' . $session->get('last_name');

// Profiles with dynamic URL
$profiles = [
    [
        'key' => 'profiles.my-profile',
        'label' => 'My Profile' . ($currentUserName ? ' (' . $currentUserName . ')' : ''),
        'icon' => 'fas fa-user-circle',
        'url' => base_url('admin/users/view/' . $currentUserId),
        'match' => 'admin/users/view',
        'perms' => []
    ],

    [
        'key' => 'profiles.campus-profile',
        'label' => 'Campus Profile',
        'icon' => 'fas fa-school',
        'url' => base_url('admin/profile-campus'),
        'match' => 'admin/profile-campus',
        'perms' => ['admin-campus', 'admin-add-campus-profile']
    ],
    [
        'key' => 'profiles.system-profile',
        'label' => 'System Profile',
        'icon' => 'fas fa-cogs',
        'url' => base_url('admin/profile-system'),
        'match' => 'admin/profile-system',
        'perms' => ['admin-add-system-profile', 'admin-view-global-session']
    ],
];

$sections[] = [
    'key' => 'profiles',
    'label' => 'Profiles',
    'icon' => 'fas fa-th',
    'children' => $profiles,
    'visible' => true
];


$healthItems = [
    [
        'key' => 'health.bmi-dashboard',
        'label' => 'BMI Dashboard',
        'icon' => 'fas fa-heartbeat',
        'url' => $link('admin/health/bmi-dashboard'),
        'match' => 'admin/health/bmi-dashboard',
        'perms' => ['admin-health-bmi', 'admin-health-bmi-dashboard']
    ],

      [
        'key' => 'health.alerts',
        'label' => 'Health Alerts',
        'icon' => 'fas fa-bell',
        'url' => base_url('admin/health/alerts'),
        'match' => 'admin/health/alerts',
        'perms' => ['admin-health-alerts']
    ],

    [
        'key' => 'health.bmi-records',
        'label' => 'BMI Records',
        'icon' => 'fas fa-chart-line',
        'url' => $link('admin/health/bmi-records'),
        'match' => 'admin/health/bmi-records',
        'perms' => ['admin-health-bmi', 'admin-health-bmi-records']
    ],
    [
        'key' => 'health.bulk-bmi-update',
        'label' => 'Bulk BMI Update',
        'icon' => 'fas fa-upload',
        'url' => $link('admin/students_bulk_info_date_of_birth'),
        'match' => 'admin/students_bulk_info_date_of_birth',
        'perms' => ['admin-health-bmi', 'admin-edit-student']
    ],
    [
        'key' => 'health.nutrition-suggestions',
        'label' => 'Nutrition Suggestions',
        'icon' => 'fas fa-apple-alt',
        'url' => $link('admin/health/nutrition-suggestions'),
        'match' => 'admin/health/nutrition-suggestions',
        'perms' => ['admin-health-bmi', 'admin-health-nutrition']
    ],
    [
        'key' => 'health.bmi-reports',
        'label' => 'BMI Reports',
        'icon' => 'fas fa-file-alt',
        'url' => $link('admin/health/bmi-reports'),
        'match' => 'admin/health/bmi-reports',
        'perms' => ['admin-health-bmi', 'admin-health-reports']
    ],
];

$sections[] = [
    'key' => 'health',
    'label' => 'Health & BMI',
    'icon' => 'fas fa-heartbeat',
    'children' => $healthItems,
    'visible' => (bool) array_filter($healthItems, static fn ($i) => $canAny($i['perms'] ?? [])),
];
  // Sessions
  $sessionsItems = [
  [
  'key'   => 'sessions.calendar-builder',
  'label' => 'Calendar Builder',
  'icon'  => 'fa fa-project-diagram',
  'url'   => $link('admin/academic-calendar/builder'),
  'match' => 'admin/academic-calendar/builder',
  'perms' => ['admin-academic-session'],
],
[
        'key'   => 'academic.setup',
        'label' => 'Academic Setup Wizard',
        'icon'  => 'fa fa-chalkboard-teacher',
        'url'   => $link('admin/academic-setup'),
        'match' => 'admin/academic-setup',
        'perms' => ['admin-academic-session'],
    ],



    // ['key'=>'sessions.academic-sessions','label'=>'Academic Sessions','icon'=>'fa fa-calendar','url'=>$link('admin/academic_session'),'match'=>'admin/academic_session','perms'=>['admin-academic-session']],
    // ['key'=>'sessions.terms','label'=>'Terms','icon'=>'fa fa-list','url'=>$link('admin/terms'),'match'=>'admin/terms','perms'=>['admin-terms']],
    // ['key'=>'sessions.term-sessions','label'=>'Term Sessions','icon'=>'fa fa-list','url'=>$link('admin/terms_session'),'match'=>'admin/terms_session','perms'=>['admin-terms-sessions']],
    // ['key'=>'sessions.term-weeks','label'=>'Term Weeks','icon'=>'fa fa-list','url'=>$link('admin/term_weeks'),'match'=>'admin/term_weeks','perms'=>['admin-term-weeks']],
];

  $sections[] = [
    'key'=>'sessions',
    'label'=>'Sessions',
    'icon'=>'fas fa-cogs',
    'children'=>$sessionsItems,
    'visible'=> (bool) array_filter($sessionsItems, fn($i)=>$canAny($i['perms'] ?? []))
  ];

  // Students / Admissions
  $studentsItems = [
    ['key'=>'students.enrolled-print','label'=>'Enrolled Students (Print)','icon'=>'fas fa-users','url'=>$link('admin/students_print?status=1'),'match'=>'admin/students_print','perms'=>['admin-students']],
    ['key'=>'students.class-section-strength','label'=>'Class / Section Strength Report','icon'=>'fas fa-chart-bar','url'=>$link('admin/class-section-strength-report'),'match'=>'admin/class-section-strength-report','perms'=>['admin-students']],
    ['key'=>'students.readmit','label'=>'Readmit Students','icon'=>'fas fa-user-plus','url'=>$link('admin/students/readmit'),'match'=>'admin/students/readmit','perms'=>['admin-students']],
    ['key'=>'students.admission','label'=>'Admission','icon'=>'fas fa-user-plus','url'=>$link('admin/students/add'),'match'=>'admin/students/add','perms'=>['admin-students']],
    ['key'=>'students.add-bulk','label'=>'Add Bulk Students','icon'=>'fas fa-layer-group','url'=>$link('admin/addbulkstudents/add'),'match'=>'admin/addbulkstudents','perms'=>['admin-students']],
    ['key'=>'students.id-card','label'=>'Student ID Card','icon'=>'far fa-id-card','url'=>$link('admin/student_id_card'),'match'=>'admin/student_id_card','perms'=>['admin-student-id-cards']],
    ['key'=>'students.id-card-new','label'=>'Student ID Card (New)','icon'=>'far fa-address-card','url'=>$link('admin/student_id_card_new'),'match'=>'admin/student_id_card_new','perms'=>['admin-student-id-cards']],
    ['key'=>'students.promotion','label'=>'Promotion','icon'=>'fas fa-angle-double-up','url'=>$link('admin/student_class'),'match'=>'admin/student_class','perms'=>['admin-student-class']],
    ['key'=>'students.attachment-types','label'=>'Attachment Types','icon'=>'fas fa-paperclip','url'=>$link('admin/attachment_types'),'match'=>'admin/attachment_types','perms'=>['admin-attachment-types']],
    ['key'=>'students.data-verification','label'=>'Data Verification Form','icon'=>'fas fa-user-check','url'=>$link('admin/student_data_verification_form'),'match'=>'admin/student_data_verification_form','perms'=>['admin-students']],
    ['key'=>'students.fee-verification','label'=>'Fee Verification Form','icon'=>'fas fa-file-invoice-dollar','url'=>$link('admin/student_data_verification_form/student_fee_verification'),'match'=>'admin/student_data_verification_form/student_fee_verification','perms'=>['admin-students']],
  ];
  $sections[] = [
    'key'=>'students',
    'label'=>'Students',
    'icon'=>'fas fa-user-graduate',
    'children'=>$studentsItems,
    'visible'=> (bool) array_filter($studentsItems, fn($i)=>$canAny($i['perms']))
  ];
// Faculty


$facultyItems = [
    // Main Employee Management
    ['key'=>'faculty.employees.list','label'=>'All Employees','icon'=>'fa fa-list','url'=>$link('admin/users?status=1'),'match'=>'admin/users','perms'=>['admin-users']],
        ['key'=>'faculty.employees.add','label'=>'Add Employee','icon'=>'fa fa-user-plus','url'=>$link('admin/users/add'),'match'=>'admin/users/add','perms'=>['admin-users']],
        ['key'=>'faculty.employees.bulk','label'=>'Bulk employee info','icon'=>'fa fa-users-cog','url'=>$link('admin/users_bulk_info'),'match'=>'admin/users_bulk_info','perms'=>['admin-users']],
    ['key'=>'faculty.teacher-subjects','label'=>'Assign Subjects to Teachers','icon'=>'fa fa-chalkboard-teacher','url'=>$link('admin/teacher_subjects'),'match'=>'admin/teacher_subjects','perms'=>['admin-teacher-subjects','admin-add-teacher-subject']],

    // ===== SALARY MANAGEMENT SECTION =====
    ['key'=>'faculty.salary.settings','label'=>'Salary Settings','icon'=>'fa fa-cog','url'=>$link('admin/salary-settings'),'match'=>'admin/salary-settings','perms'=>['admin-users','admin-salary-settings']],
    ['key'=>'faculty.salary.bulk','label'=>'Bulk Salary Adjustment','icon'=>'fa fa-users-cog','url'=>$link('admin/salary-settings/bulk-adjustment'),'match'=>'admin/salary-settings/bulk-adjustment','perms'=>['admin-users','admin-salary-settings']],
    ['key'=>'faculty.salary.generate','label'=>'Generate Monthly Salary','icon'=>'fa fa-calculator','url'=>$link('admin/salary-settings'),'match'=>'admin/salary-settings','perms'=>['admin-users','admin-salary-settings']],
    ['key'=>'faculty.salary.reports','label'=>'Salary Reports','icon'=>'fa fa-chart-line','url'=>$link('admin/salary-reports'),'match'=>'admin/salary-reports','perms'=>['admin-users','admin-salary-reports']],
    ['key'=>'faculty.salary.advance','label'=>'Advance Salary','icon'=>'fa fa-hand-holding-usd','url'=>$link('admin/advance-salary'),'match'=>'admin/advance-salary','perms'=>['admin-users','admin-salary-advance']],
    ['key'=>'faculty.salary.bonus','label'=>'Bonuses','icon'=>'fa fa-gift','url'=>$link('admin/bonuses'),'match'=>'admin/bonuses','perms'=>['admin-users','admin-salary-bonuses']],
    ['key'=>'faculty.salary.slips','label'=>'Salary Slips','icon'=>'fa fa-file-invoice-dollar','url'=>$link('admin/salary-slips'),'match'=>'admin/salary-slips','perms'=>['admin-users','admin-salary-slips']],

    // ===== QR CODE ATTENDANCE SECTION =====
    ['key'=>'faculty.qr-scanner','label'=>'QR Attendance Scanner','icon'=>'fa fa-qrcode','url'=>$link('admin/attendance/scan'),'match'=>'admin/attendance/scan','perms'=>['admin-users']],
    ['key'=>'faculty.attendance-manual','label'=>'Manual Attendance','icon'=>'fa fa-pen','url'=>$link('admin/attendance/manual'),'match'=>'admin/attendance/manual','perms'=>['admin-users']],
    ['key'=>'faculty.attendance-report','label'=>'Attendance Report','icon'=>'fa fa-chart-bar','url'=>$link('admin/attendance/report'),'match'=>'admin/attendance/report','perms'=>['admin-users']],
    ['key'=>'faculty.attendance-summary','label'=>'Daily Summary','icon'=>'fa fa-table','url'=>$link('admin/attendance/summary'),'match'=>'admin/attendance/summary','perms'=>['admin-users']],

    // QR Code Management
    ['key'=>'faculty.qr-generate-all','label'=>'Generate All QR Codes','icon'=>'fa fa-plus-circle','url'=>$link('admin/qr/generate-all'),'match'=>'admin/qr/generate-all','perms'=>['admin-users']],
    ['key'=>'faculty.qr-download-all','label'=>'Download All QR Codes','icon'=>'fa fa-download','url'=>$link('admin/qr/download-all'),'match'=>'admin/qr/download-all','perms'=>['admin-users']],

    ['key'=>'faculty.employee-timing','label'=>'Employee Timing','icon'=>'fa fa-hourglass-half','url'=>$link('admin/emp_timing/add'),'match'=>'admin/emp_timing','perms'=>['admin-add-teacher-section']],

    // Employee Details Views
    ['key'=>'faculty.profile','label'=>'Employee Profile','icon'=>'fa fa-id-card','url'=>'javascript:void(0)','match'=>'admin/users/view','disabled'=>true,'perms'=>['admin-users']],
    ['key'=>'faculty.salary','label'=>'Employee Salary Details','icon'=>'fa fa-money-bill-wave','url'=>'javascript:void(0)','match'=>'admin/users/salary','disabled'=>true,'perms'=>['admin-users']],
    ['key'=>'faculty.attendance','label'=>'Attendance Records','icon'=>'fa fa-check-circle','url'=>$link('admin/attendance/report'),'match'=>'admin/attendance/report|admin/users/attendance','perms'=>['admin-users']],
    // FIXED: Removed the extra closing parenthesis and added proper closing
    ['key'=>'faculty.leaves','label'=>'Leave Applications','icon'=>'fa fa-plane','url'=>$link('admin/users?status=1'),'match'=>'admin/users/leaves','perms'=>['admin-users']]
];

$sections[] = [
    'key' => 'faculty',
    'label' => 'Faculty',
    'icon' => 'fa fa-users',
    'children' => $facultyItems,
    'visible' => (bool) array_filter($facultyItems, fn($i) => $canAny($i['perms']))
];
  // Exams & Tests

$examsItems = [
    ['key'=>'quiz.quiz','label'=>'Add Quiz','icon'=>'fa fa-list','url'=>$link('admin/quiz'),'match'=>'admin/quiz','perms'=>['admin-exams']],

    ['key'=>'exams.exam','label'=>'Exam','icon'=>'fa fa-list','url'=>$link('admin/exam'),'match'=>'admin/exam','perms'=>['admin-exams']],



    ['key'=>'exams.datesheet','label'=>'Date Sheet','icon'=>'fa fa-calendar','url'=>$link('admin/datesheet'),'match'=>'admin/datesheet','perms'=>['admin-datesheet']],
    ['key'=>'exams.results-add','label'=>'Results','icon'=>'fa fa-list','url'=>$link('admin/students-results/add'),'match'=>'admin/students-results','perms'=>['admin-students-results']],
    ['key'=>'exams.results-list','label'=>'Results List','icon'=>'fa fa-list','url'=>$link('admin/students-results-list'),'match'=>'admin/students-results-list','perms'=>['admin-students-results']],
    ['key'=>'exams.subject-results','label'=>'Subject Results','icon'=>'fa fa-list','url'=>$link('admin/students-subject-results/add'),'match'=>'admin/students-subject-results','perms'=>['admin-students-subject-results']],
    ['key'=>'exams.grades','label'=>'Grades & Policy','icon'=>'fa fa-layer-group','url'=>$link('admin/grades/setup'),'match'=>'admin/grades','perms'=>['admin-grades','admin-grading-policy']],

    [
    'key'   => 'quiz.play-admin',
    'label' => 'Play Quiz (Admin)',
    'icon'  => 'fa fa-gamepad',
    'url'   => $link('admin/quiz-assign'),
    'match' => 'admin/quiz-assign',
    'perms' => ['admin-quiz', 'admin-quiz-assign']
],

    [
    'key'   => 'quiz.exam-marks-report',
    'label' => 'Exam Quiz Marks',
    'icon'  => 'fa fa-table',
    'url'   => $link('admin/quizzes/exam-marks-report'),
    'match' => 'admin/quizzes/exam-marks-report',
    'perms' => ['admin-quiz'],
],


  ];
  $testsItems = [
    ['key'=>'tests.results','label'=>'Add Tests Results','icon'=>'fa fa-list','url'=>$link('admin/test-results'),'match'=>'admin/test-results','perms'=>['admin-test-series']],
    ['key'=>'tests.series-result-card','label'=>'Tests Series Results Card','icon'=>'fa fa-list','url'=>$link('admin/test-series-result-card'),'match'=>'admin/test-series-result-card','perms'=>['admin-test-series']],
  ];
  $sections[] = [
    'key'=>'exams-tests',
    'label'=>'Exams & Tests',
    'icon'=>'fas fa-diagnoses',
    'children'=>array_merge($examsItems, $testsItems),
    'visible'=> (bool) array_filter(array_merge($examsItems, $testsItems), fn($i)=>$canAny($i['perms']))
  ];


$quizzesItems = [
    [
        'key'   => 'question-bank.question-bank',
        'label' => 'QB Overview',
        'icon'  => 'fa fa-sitemap',
        'url'   => $link('admin/question-bank/overview'),
        'match' => 'admin/question-bank/overview',
        'perms' => ['admin-question-bank-overview'],
    ],
    [
        'key'   => 'question-bank.question-bank-form',
        'label' => 'QB Add / Edit',
        'icon'  => 'fa fa-edit',
        'url'   => $link('admin/question-bank/form'),
        'match' => 'admin/question-bank/form',
        'perms' => ['admin-questions'],
    ],
    [
        'key'   => 'question-bank.ai-generate',
        'label' => 'QB AI Generate',
        'icon'  => 'fa fa-magic',
        'url'   => $link('admin/question-bank-ai'),
        'match' => 'admin/question-bank-ai',
        'perms' => ['admin-question-bank-ai'],
    ],
    [
        'key'   => 'question-paper.generator',
        'label' => 'Question Paper',
        'icon'  => 'fa fa-file-alt',
        'url'   => $link('admin/question-paper'),
        'match' => 'admin/question-paper',
        'perms' => ['admin-question-paper'],
    ],
    [
        'key'   => 'assessment-builder',
        'label' => 'Assessment Builder',
        'icon'  => 'fa fa-layer-group',
        'url'   => $link('admin/assessment-builder'),
        'match' => 'admin/assessment-builder',
        'perms' => ['admin-quiz', 'admin-question-paper'],
    ],
    [
        'key'   => 'question-bank.topics',
        'label' => 'QB Topics',
        'icon'  => 'fa fa-tags',
        'url'   => $link('admin/qb-topics'),
        'match' => 'admin/qb-topics',
        'perms' => ['admin-qb-topics'],
    ],
    [
        'key'   => 'question-bank.board-publishers',
        'label' => 'Boards / Publisher',
        'icon'  => 'fa fa-university',
        'url'   => $link('admin/qb-board-publishers'),
        'match' => 'admin/qb-board-publishers',
        'perms' => ['admin-qb-board-publishers'],
    ],
    [
        'key'   => 'vocabulary-bank.topics',
        'label' => 'Vocab Topics',
        'icon'  => 'fa fa-tags',
        'url'   => $link('admin/vocab-topics'),
        'match' => 'admin/vocab-topics',
        'perms' => ['admin-vocab-topics'],
    ],
    [
        'key'   => 'vocab-bank.vocab-bank',
        'label' => 'Vocabulary Bank',
        'icon'  => 'fa fa-list',
        'url'   => $link('admin/vocab-bank'),
        'match' => 'admin/vocab-bank',
        'perms' => ['admin-vocab-bank'],
    ],
    [
        'key'   => 'vocab-bank.report',
        'label' => 'Vocabulary Report',
        'icon'  => 'fa fa-table',
        'url'   => $link('admin/vocab-bank/report'),
        'match' => 'admin/vocab-bank/report',
        'perms' => ['admin-vocab-report'],
    ],
    [
        'key'   => 'vocab-bank.listofwords',
        'label' => 'Vocabulary words',
        'icon'  => 'fa fa-table',
        'url'   => $link('admin/vocab-bank/listofwords'),
        'match' => 'admin/vocab-bank/listofwords',
        'perms' => ['admin-vocab-words'],
    ],
    [
        'key'   => 'quizzes.quizzes',
        'label' => 'Quizzes',
        'icon'  => 'fa fa-calendar',
        'url'   => $link('admin/quizzes'),
        'match' => 'admin/quizzes',
        'perms' => ['admin-quiz'],
        'director_quizzes_menu' => true,
    ],
    [
        'key'   => 'quizzes.create-board-prep',
        'label' => 'Board Prep Quizzes',
        'icon'  => 'fa fa-book-reader',
        'url'   => $link('admin/quizzes/create-board-prep'),
        'match' => 'admin/quizzes/create-board-prep',
        'perms' => ['admin-quiz'],
    ],
    [
        'key'   => 'quizzes.math-crossword',
        'label' => 'Math Crossword',
        'icon'  => 'fa fa-th',
        'url'   => $link('admin/math-crossword'),
        'match' => 'admin/math-crossword',
        'perms' => ['admin-math-crossword'],
    ],
    [
        'key'   => 'quizzes.math-worksheet',
        'label' => 'Math Worksheet',
        'icon'  => 'fa fa-calculator',
        'url'   => $link('admin/math-worksheet'),
        'match' => 'admin/math-worksheet',
        'perms' => ['admin-math-worksheet'],
    ],
    [
        'key'   => 'quizzes.word-search',
        'label' => 'Word Puzzle',
        'icon'  => 'fa fa-search',
        'url'   => $link('admin/word-search'),
        'match' => 'admin/word-search',
        'perms' => ['admin-word-search'],
    ],
];



  helper('role');
  $sections[] = [
    'key'=>'quizzes',
    'label'=>'Quizzes',
    'icon'=>'fas fa-diagnoses',
    'children'=>$quizzesItems,
    'visible'=> (bool) array_filter(
        $quizzesItems,
        static fn ($i) => quizzesMenuItemVisible($i, $canAny)
    ),
  ];

// Attendance
$attendanceOpsItems = [
    ['key'=>'attendance.employees-attendance','label'=>'Employees Attendance','icon'=>'fa fa-cubes','url'=>$link('admin/employees_attendance/add'),'match'=>'admin/employees_attendance','perms'=>['admin-add-student-attendance']],
    ['key'=>'attendance.absentees','label'=>'Absentees','icon'=>'far fa-clock','url'=>$link('admin/students_absentees/add'),'match'=>'admin/students_absentees','perms'=>['admin-add-student-absentees']],
    ['key'=>'attendance.face-attendance','label'=>'Face Attendance','icon'=>'fa fa-camera','url'=>$link('admin/face-attendance'),'match'=>'admin/face-attendance','perms'=>['admin-emp-attendance-monthly-report']],
    ['key'=>'attendance.face-management','label'=>'Face Management','icon'=>'fa fa-user-check','url'=>$link('admin/face-management'),'match'=>'admin/face-management','perms'=>['admin-emp-attendance-monthly-report']],
    ['key'=>'attendance.employee-face-scanner','label'=>'Employee Face Scanner','icon'=>'fa fa-camera','url'=>$link('admin/employee-face-attendance'),'match'=>'admin/employee-face-attendance','perms'=>['admin-employee-face-attendance']],
    ['key'=>'attendance.employee-face-enrollment','label'=>'Employee Face Enrollment','icon'=>'fa fa-user-check','url'=>$link('admin/employee-face-management'),'match'=>'admin/employee-face-management','perms'=>['admin-employee-face-management']],
];

$attendanceApprovalItems = [
    ['key'=>'attendance.emp-leaves-add','label'=>'Create Employee Leaves','icon'=>'fa fa-cubes','url'=>$link('admin/employee_leaves/add'),'match'=>'admin/employee_leaves/add','perms'=>['admin-add-student-attendance']],
    ['key'=>'attendance.emp-leaves','label'=>'Employee Leaves Applications','icon'=>'fa fa-cubes','url'=>$link('admin/employee_leaves'),'match'=>'admin/employee_leaves','perms'=>['admin-add-student-attendance'],'badge'=>['key'=>'pending_emp_leaves','class'=>'badge-danger']],
    ['key'=>'attendance.std-leaves-add','label'=>'Create Leaves Applications','icon'=>'far fa-clock','url'=>$link('admin/students_leaves/add'),'match'=>'admin/students_leaves/add','perms'=>['admin-add-student-leaves']],
    ['key'=>'attendance.std-leaves','label'=>'Leaves Applications','icon'=>'far fa-clock','url'=>$link('admin/students_leaves'),'match'=>'admin/students_leaves','perms'=>['admin-student-leaves'],'badge'=>['key'=>'pending_std_leaves','class'=>'badge-danger']],
];

$attendanceReportItems = [
    ['key'=>'attendance.emp-attendance-report','label'=>'Employees Attendance Report','icon'=>'fa fa-cubes','url'=>$link('admin/emp_attendance_monthlyreport'),'match'=>'admin/emp_attendance_monthlyreport','perms'=>['admin-emp-attendance-monthly-report']],
    ['key'=>'attendance.student-monthly-report','label'=>'Students Monthly Report','icon'=>'fa fa-calendar-alt','url'=>$link('admin/attendance-monthly-report'),'match'=>'admin/attendance-monthly-report','perms'=>['admin-emp-attendance-monthly-report']],
    ['key'=>'attendance.student-session-report','label'=>'Students Session Report','icon'=>'fa fa-calendar','url'=>$link('admin/attendance-monthly-report/student-session-report'),'match'=>'admin/attendance-monthly-report/student-session-report','perms'=>['admin-attendance-monthly-report']],
    ['key'=>'attendance.working-days-report','label'=>'Working Days Report','icon'=>'fa fa-calendar-check','url'=>$link('admin/attendance-working-days-report'),'match'=>'admin/attendance-working-days-report','perms'=>['admin-emp-attendance-monthly-report']],
    ['key'=>'attendance.student-daily-report','label'=>'Student Daily Report','icon'=>'fa fa-list-alt','url'=>$link('admin/student-daily-report'),'match'=>'admin/student-daily-report','perms'=>['admin-student-attendance']],
    ['key'=>'attendance.student-weekly-non-present-report','label'=>'Student Weekly Non-Present Report','icon'=>'fa fa-table','url'=>$link('admin/student-weekly-non-present-report'),'match'=>'admin/student-weekly-non-present-report','perms'=>['admin-student-attendance']],
];

$attendanceItems = array_merge(
    [['label'=>'— Operations —','icon'=>'','header'=>true]],
    $attendanceOpsItems,
    [['label'=>'— Approvals —','icon'=>'','header'=>true]],
    $attendanceApprovalItems,
    [['label'=>'— Monitoring & Reports —','icon'=>'','header'=>true]],
    $attendanceReportItems
);
  $sections[] = [
    'key'=>'attendance',
    'label'=>'Attendance',
    'icon'=>'far fa-address-card',
    'children'=>$attendanceItems,
    'visible'=> (bool) array_filter(array_merge($attendanceOpsItems, $attendanceApprovalItems, $attendanceReportItems), fn($i)=>$canAny($i['perms']))
  ];

  // Time Table
  $timetableItems = [
    ['key'=>'timetable.generator','label'=>'Timetable Generator','icon'=>'fas fa-cogs','url'=>$link('admin/timetable/generator'),'match'=>'admin/timetable/generator','perms'=>['admin-timetable']],
    ['key'=>'timetable.report','label'=>'Timetable Report (Class/Teacher)','icon'=>'far fa-file-alt','url'=>$link('admin/timetable/report'),'match'=>'admin/timetable/report','perms'=>['admin-timetable']],
    ['key'=>'timetable.school-timing','label'=>'School Timing','icon'=>'far fa-clock','url'=>$link('admin/school_timing/add'),'match'=>'admin/school_timing','perms'=>['admin-school-timing']],
    ['key'=>'timetable.slots','label'=>'Slots','icon'=>'far fa-clock','url'=>$link('admin/slots'),'match'=>'admin/slots','perms'=>['admin-slots']],
  ];
  $sections[] = [
    'key'=>'timetable',
    'label'=>'Time Table',
    'icon'=>'far fa-clock',
    'children'=>$timetableItems,
    'visible'=> (bool) array_filter($timetableItems, fn($i)=>$canAny($i['perms']))
  ];



// Academics
$academicsItems = [
    [
        'key' => 'academics.top-level-planning.add',
        'label' => 'Add Top Level Planning',
        'icon' => 'fa fa-plus-circle',
        'url' => $link('admin/top_level_planning/add'),
        'match' => 'admin/top_level_planning/add',
        'perms' => ['admin-add-top-level-planning']
    ],
    [
        'key' => 'academics.top-level-planning.view',
        'label' => 'View Top Level Planning',
        'icon' => 'fa fa-eye',
        'url' => $link('admin/top_level_planning/view'),
        'match' => 'admin/top_level_planning/view',
        'perms' => ['admin-add-top-level-planning']
    ],
    [
        'key' => 'academics.weekly-planning-add',
        'label' => 'Add Weekly Planning',
        'icon' => 'fa fa-plus-circle',
        'url' => $link('admin/weekly_planning/add'),
        'match' => 'admin/weekly_planning/add',
        'parent' => 'academics.weekly-planning',
        'perms' => ['admin-add-weekly-planning']
    ],
    [
        'key' => 'academics.weekly-planning-report',
        'label' => 'Weekly Planning Report',
        'icon' => 'fa fa-chart-bar',
        'url' => $link('admin/weekly_planning_report'),
        'match' => 'admin/weekly_planning_report',
        'perms' => ['admin-weekly-planning']
    ],
    [
        'key' => 'academics.daily-diary',
        'label' => 'Daily Diary',
        'icon' => 'fa fa-list',
        'url' => $link('admin/classdairy-view'),
        'match' => 'admin/classdairy-view',
        'perms' => ['admin-classdairy']
    ],
    [
        'key' => 'academics.bag-pack',
        'label' => 'Bag Pack',
        'icon' => 'fa fa-shopping-bag',
        'url' => $link('admin/bagpack'),
        'match' => 'admin/bagpack',
        'perms' => ['admin-classdairy']
    ],

    // ========== ACTIVITY REPORT SECTION ==========
    [
        'key' => 'academics.activity-report.hdr',
        'label' => '— Activity Reports —',
        'icon' => '',
        'header' => true,
        'perms' => [],
    ],
    [
        'key' => 'academics.activity-report',
        'label' => 'Activity Reports',
        'icon' => 'fa fa-clipboard-list',
        'url' => '#',
        'match' => 'admin/activity-report',
        'perms' => ['admin-classdairy', 'admin-activity-review']
    ],
    [
        'key' => 'academics.activity-report.teacher',
        'label' => 'My Activity Report',
        'icon' => 'fa fa-chalkboard-teacher',
        'url' => $link('admin/activity-report/teacher-report'),
        'match' => 'admin/activity-report/teacher-report',
        'parent' => 'academics.activity-report',
        'perms' => ['admin-classdairy']
    ],
    [
        'key' => 'academics.activity-report.principal',
        'label' => 'Review Activities',
        'icon' => 'fa fa-star',
        'url' => $link('admin/activity-report/principal-report'),
        'match' => 'admin/activity-report/principal-report',
        'parent' => 'academics.activity-report',
        'perms' => ['admin-activity-review']
    ],
    [
        'key' => 'academics.activity-report.summary',
        'label' => 'Activity Summary',
        'icon' => 'fa fa-chart-pie',
        'url' => $link('admin/activity-report/summary'),
        'match' => 'admin/activity-report/summary',
        'parent' => 'academics.activity-report',
        'perms' => ['admin-activity-review']
    ],

    // ========== STUDENT RECORDINGS REVIEW SECTION ==========
    [
        'key' => 'academics.recordings.hdr',
        'label' => '— Student Recordings —',
        'icon' => '',
        'header' => true,
        'perms' => [],
    ],
    [
        'key' => 'academics.recordings',
        'label' => 'Student Recordings',
        'icon' => 'fa fa-microphone-alt',
        'url' => $link('admin/recordings'),
        'match' => 'admin/recordings',
        'perms' => ['admin-classdairy']
    ],
    [
        'key' => 'academics.recordings.pending',
        'label' => 'Pending Reviews',
        'icon' => 'fa fa-clock',
        'url' => $link('admin/recordings?tab=pending'),
        'match' => 'admin/recordings',
        'parent' => 'academics.recordings',
        'perms' => ['admin-classdairy']
    ],
    [
        'key' => 'academics.recordings.audio',
        'label' => 'Audio Submissions',
        'icon' => 'fa fa-microphone',
        'url' => $link('admin/recordings?tab=audio'),
        'match' => 'admin/recordings',
        'parent' => 'academics.recordings',
        'perms' => ['admin-classdairy']
    ],
    [
        'key' => 'academics.recordings.video',
        'label' => 'Video Submissions',
        'icon' => 'fa fa-video',
        'url' => $link('admin/recordings?tab=video'),
        'match' => 'admin/recordings',
        'parent' => 'academics.recordings',
        'perms' => ['admin-classdairy']
    ],
    [
        'key' => 'academics.recordings.progress',
        'label' => 'Student Progress',
        'icon' => 'fa fa-chart-line',
        'url' => $link('admin/recordings/student-progress'),
        'match' => 'admin/recordings/student-progress',
        'parent' => 'academics.recordings',
        'perms' => ['admin-classdairy']
    ],
    [
        'key' => 'academics.recordings.analytics',
        'label' => 'Performance Analytics',
        'icon' => 'fa fa-chart-bar',
        'url' => $link('admin/recordings/analytics'),
        'match' => 'admin/recordings/analytics',
        'parent' => 'academics.recordings',
        'perms' => ['admin-classdairy']
    ]
];

$sections[] = [
    'key' => 'academics',
    'label' => 'Academics',
    'icon' => 'far fa-address-card',
    'children' => $academicsItems,
    'visible' => (bool) array_filter($academicsItems, fn($i) => $canAny($i['perms'] ?? []))
];

  // Communication

$commItems = [
    ['key'=>'communication.templates','label'=>'Message Templates','icon'=>'fas fa-comment-dots','url'=>$link('admin/message-templates'),'match'=>'admin/message-templates','perms'=>['admin-update-message-templates']],
    ['key'=>'communication.messages','label'=>'Messages','icon'=>'fas fa-comments','url'=>$link('admin/messages'),'match'=>'admin/messages','perms'=>['admin-messages'],'badge'=>['key'=>'unread_messages','class'=>'badge-warning']],
    ['key'=>'communication.bulk-excel-sms','label'=>'Bulk Excel SMS','icon'=>'fas fa-file-upload','url'=>$link('admin/bulksms'),'match'=>'admin/bulksms','perms'=>['admin-bulk-messages']],
    ['key'=>'communication.defaulter-sms','label'=>'Defaulter SMS','icon'=>'fas fa-exclamation-circle','url'=>$link('admin/defaulter-message'),'match'=>'admin/defaulter-message','perms'=>['admin-defaulter-message']],
    ['key'=>'communication.result-sms','label'=>'Result SMS','icon'=>'fas fa-poll','url'=>$link('admin/result-message'),'match'=>'admin/result-message','perms'=>['admin-result-message']],

    ['key'=>'communication.wa-test-series','label'=>'Send Test Series Result (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/students_list?status=1'),'match'=>'admin/students_list','perms'=>['admin-result-message','admin-messages']],
    ['key'=>'communication.wa-result','label'=>'Send Result (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/students_w_result_list?status=1'),'match'=>'admin/students_w_result_list','perms'=>['admin-result-message','admin-messages']],
    ['key'=>'communication.wa-fee-chalan','label'=>'Send Fee Chalan (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/family_chalan_whatsapp'),'match'=>'frontend/family_chalan_whatsapp','perms'=>['admin-result-message','admin-messages']],
    ['key'=>'communication.wa-daily-diary','label'=>'Send Daily Diary (WA)','icon'=>'fab fa-whatsapp','url'=>$link('frontend/family_diary_whatsapp'),'match'=>'frontend/family_diary_whatsapp','perms'=>['admin-result-message','admin-messages']],
    ['key'=>'communication.absentees-report','label'=>'Students Absentees Report','icon'=>'far fa-address-card','url'=>$link('admin/students_attendance/report'),'match'=>'admin/students_attendance/report','perms'=>['admin-add-student-attendance']],
  ];
  $sections[] = [
    'key'=>'communication',
    'label'=>'Communication',
    'icon'=>'fas fa-sms',
    'badge_sum_children'=>true,
    'children'=>$commItems,
    'visible'=> (bool) array_filter($commItems, fn($i)=>$canAny($i['perms'] ?? ['admin-messages']))
  ];

  // Finance
  $feeItems = [


    ['key'=>'finance.fee.setup','label'=>'Fee Configuration','icon'=>'fas fa-sliders-h','url'=>$link('admin/fee_setup'),'match'=>'admin/fee_setup|admin/fee_type|admin/fee_amount','perms'=>['admin-fee-type','admin-fee-amount','admin-add-fee-type','admin-add-fee-amount']],
    ['key'=>'finance.fee.plan-months','label'=>'Fee Plan Months','icon'=>'fas fa-calendar-alt','url'=>$link('admin/fee_plan_months'),'match'=>'admin/fee_plan_months','perms'=>['admin-fee-plan-months','admin-add-fee-plan-months']],
    ['key'=>'finance.fee.generate-chalan','label'=>'Generate Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan/add'),'match'=>'admin/fee-chalan/add','perms'=>['admin-fee-chalan']],
    ['key'=>'finance.fee.print-chalan','label'=>'Print Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan'),'match'=>'admin/fee-chalan$','perms'=>['admin-fee-chalan']],
 /* [
    'key' => 'finance.fee.chalan',
    'label' => 'Fee Chalan',
    'icon' => 'fas fa-file-invoice',
    'url' => $link('admin/fee-chalan/generate'),
    'match' => 'admin/fee-chalan/generate|admin/fee-chalan$',
    'perms' => ['admin-fee-chalan'],
    'badge' => 'New'
],
    ['key'=>'finance.fee.print-chalan-new','label'=>'Print Fee Chalan new','icon'=>'fas fa-file-invoice','url'=>$link('admin/print-fee-chalan'),'match'=>'admin/print-fee-chalan$','perms'=>['admin-fee-chalan']],*/
    ['key'=>'finance.fee.pay-chalan','label'=>'Pay Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan-pay'),'match'=>'admin/fee-chalan-pay','perms'=>['admin-fee-chalan'],'badge'=>['key'=>'unpaid_fee_chalans','class'=>'badge-info']],
    ['key'=>'finance.fee.advance-fee','label'=>'Update Advance Fee','icon'=>'fas fa-piggy-bank','url'=>$link('admin/advance-fee'),'match'=>'admin/advance-fee','perms'=>['admin-fee-chalan']],


    /*
    ['key'=>'finance.fee.pay-chalan1','label'=>'Pay Fee Chalan1','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan-pay1'),'match'=>'admin/fee-chalan-pay1','perms'=>['admin-fee-chalan'],'badge'=>['key'=>'unpaid_fee_chalans','class'=>'badge-info']],  */
    ['key'=>'finance.fee.delete-chalan','label'=>'Delete Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/delete-fee-chalan'),'match'=>'admin/delete-fee-chalan','perms'=>['admin-del-fee-chalan']],
    ['key'=>'finance.fee.monthly-balance','label'=>'Monthly Balance','icon'=>'far fa-money-bill-alt','url'=>$link('admin/fee-chalan-balance'),'match'=>'admin/fee-chalan-balance','perms'=>['admin-fee-chalan-balance']],
    ['key'=>'finance.fee.daily-collection','label'=>'Daily Collection Summary','icon'=>'fas fa-calendar-day','url'=>$link('admin/fee-chalan-daily-collection'),'match'=>'admin/fee-chalan-daily-collection','perms'=>['admin-fee-chalan-balance']],
  ];
  $accountsItems = [
    ['key'=>'finance.accounts.finance-accounts','label'=>'Finance Accounts','icon'=>'fas fa-wallet','url'=>$link('admin/campus-finance-accounts'),'match'=>'admin/campus-finance-accounts','perms'=>['admin-finance-accounts','admin-accounts']],
    ['key'=>'finance.accounts.expense-heads','label'=>'Expense Heads','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expense_head'),'match'=>'admin/expense_head','perms'=>['admin-account-heads']],
    ['key'=>'finance.accounts.expenses','label'=>'Expenses','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expenses'),'match'=>'admin/expenses','perms'=>['admin-account-expenses']],
    ['key'=>'finance.accounts.asset-heads','label'=>'Asset Heads','icon'=>'fas fa-money-check-alt','url'=>$link('admin/asset_heads'),'match'=>'admin/asset_heads','perms'=>['admin-asset-heads']],
    ['key'=>'finance.accounts.assets','label'=>'Assets','icon'=>'fas fa-money-check-alt','url'=>$link('admin/assets'),'match'=>'admin/assets','perms'=>['admin-assets']],
  ];
  $sections[] = [
    'key'=>'finance',
    'label'=>'Finance',
    'icon'=>'fas fa-receipt',
    'children'=>array_merge(
      [['label'=>'— Fee Management —','icon'=>'','header'=>true]],
      $feeItems,
      [['label'=>'— Accounts —','icon'=>'','header'=>true]],
      $accountsItems
    ),
    'visible'=> (bool) array_filter(array_merge($feeItems, $accountsItems), fn($i)=>$canAny($i['perms'] ?? []))
  ];


// sports

// Sports
$sportsItems = [
  [
    'key'   => 'sports.houses',
    'label' => 'Houses',
    'icon'  => 'fas fa-flag',
    'url'   => $link('admin/sports/houses'),
    'match' => 'admin/sports/houses',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],
  [
    'key'   => 'sports.mapping',
    'label' => 'Assign Students to Houses',
    'icon'  => 'fas fa-users-cog',
    'url'   => $link('admin/sports/mapping'),
    'match' => 'admin/sports/mapping',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],
  [
    'key'   => 'sports.mentors',
    'label' => 'House Mentors',
    'icon'  => 'fas fa-user-friends',
    'url'   => $link('admin/sports/mentors'),
    'match' => 'admin/sports/mentors',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],


  [
  'key'   => 'sports.bulk_events',
  'label' => 'Bulk Events',
  'icon'  => 'fas fa-layer-group',
  'url'   => $link('admin/sports/bulk-events'),
  'match' => 'admin/sports/bulk-events*',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
],

[
  'key'   => 'sports.results',
  'label' => 'Event Results',
  'icon'  => 'fas fa-trophy',
  'url'   => $link('admin/sports/results'),
  'match' => 'admin/sports/results*',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],   // or your correct permission key
],

  // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ New Items for Teams / Members / Entries â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
  [
    'key'   => 'sports.teams',
    'label' => 'Teams',
    'icon'  => 'fas fa-users',
    'url'   => $link('admin/sports/teams'),
    'match' => 'admin/sports/teams',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],
  [
    'key'   => 'sports.team-members',
    'label' => 'Team Members',
    'icon'  => 'fas fa-user-plus',
    'url'   => $link('admin/sports/teams'),
    'match' => 'admin/sports/team-members',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],
  [
    'key'   => 'sports.entries',
    'label' => 'Event Entries',
    'icon'  => 'far fa-file-alt',
    'url'   => $link('admin/sports/events'),
    'match' => 'admin/sports/entries',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],

  // âœ… NEW: Seats (Per House) UI

  // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  [
    'key'   => 'sports.rules',
    'label' => 'Scoring Rules',
    'icon'  => 'fas fa-list-ol',
    'url'   => $link('admin/sports/rules'),
    'match' => 'admin/sports/rules',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],
  [
    'key'   => 'sports.leaderboard',
    'label' => 'Leaderboard',
    'icon'  => 'fas fa-trophy',
    'url'   => $link('admin/sports/leaderboard'),
    'match' => 'admin/sports/leaderboard',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],
  [
    'key'   => 'sports.house-sheet',
    'label' => 'House Result Sheet',
    'icon'  => 'far fa-file-alt',
    'url'   => $link('admin/sports/house-sheet'),
    'match' => 'admin/sports/house-sheet',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],
  [
    'key'   => 'sports.reports.events',
    'label' => 'Events & Participants Report',
    'icon'  => 'fas fa-clipboard-list',
    'url'   => $link('admin/sports/reports/events'),
    'match' => 'admin/sports/reports/events',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
  ],
  [
    'key'   => 'sports.events.order',
    'label' => 'Arrange Sports Events',
    'icon'  => 'fas fa-arrows-alt',
    'url'   => $link('admin/sports/events/order'),
    'match' => 'admin/sports/events/order',
    'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
],

  [
  'key'   => 'sports.entries-seats',
  'label' => 'Event Seats (Per House)',
  'icon'  => 'fas fa-chair',
  'url'   => $link('admin/sports/entries/seats'), // opens selector (index)
  'match' => 'admin/sports/entries/seats',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
],

[
  'key'   => 'sports.reports.house-members',
  'label' => 'House Members Report',
  'icon'  => 'fas fa-id-badge',
  'url'   => $link('admin/sports/reports/house-members'),
  'match' => 'admin/sports/reports/house-members',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
],

[
  'key'   => 'sports.results',
  'label' => 'Event Results',
  'icon'  => 'fas fa-trophy',
  'url'   => $link('admin/sports/results'),
  'match' => 'admin/sports/results*',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],   // or your correct permission key
],

[
  'key'   => 'sports.report_points',
  'label' => 'Points Report',
  'icon'  => 'fas fa-medal',
  'url'   => $link('admin/sports/report-points'),
  'match' => 'admin/sports/report-points*',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
],

[
  'key'   => 'sports.participation',
  'label' => 'Student Participation',
  'icon'  => 'fas fa-users',
  'url'   => $link('admin/sports/participation-report'),
  'match' => 'admin/sports/participation-report*',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
],

[
  'key'   => 'sports.leaderboard',
  'label' => 'Leaderboard',
  'icon'  => 'fas fa-trophy',
  'url'   => $link('admin/sports/leaderboard'),
  'match' => 'admin/sports/leaderboard*',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
],

[
  'key'   => 'sports.age_report',
  'label' => 'Age Report',
  'icon'  => 'fas fa-user-clock',
  'url'   => $link('admin/sports/age-report'),
  'match' => 'admin/sports/age-report*',
  'perms' => ['admin-sports-events', 'admin-sports-teams', 'admin-sports-mapping', 'admin-sports-reports'],
],
];

$sections[] = [
  'key'      => 'sports',
  'label'    => 'Sports',
  'icon'     => 'fas fa-medal',
  'children' => $sportsItems,
  'visible'  => (bool) array_filter($sportsItems, fn($i) => $canAny($i['perms'] ?? [])),
];

if ($hasHifz) {

$hifzItems = [
    [
      'key'   => 'hifz.sections',
      'label' => 'Hifz Sections',
      'icon'  => 'far fa-circle',
      'url'   => $link('admin/hifz/sections'),
      'match' => 'admin/hifz/sections*',
      'perms' => ['admin-hifz-sections'],
    ],
    [
      'key'   => 'hifz.students',
      'label' => 'Hifz Students',
      'icon'  => 'far fa-circle',
      'url'   => $link('admin/hifz/students'),
      'match' => 'admin/hifz/students*',
      'perms' => ['admin-hifz-students'],
    ],
    [
      'key'   => 'hifz.teachers',
      'label' => 'Assign Teachers',
      'icon'  => 'far fa-circle',
      'url'   => $link('admin/hifz/teachers'),
      'match' => 'admin/hifz/teachers*',
      'perms' => ['admin-hifz-teachers'],
    ],
    [
      'key'   => 'hifz.recitation',
      'label' => 'Daily Recitation',
      'icon'  => 'far fa-circle',
      'url'   => $link('admin/hifz/recitation'),
      'match' => 'admin/hifz/recitation*',
      'perms' => ['admin-hifz-recitation'],
    ],
    [
      'key'   => 'hifz.reports',
      'label' => 'Progress Reports',
      'icon'  => 'far fa-circle',
      'url'   => $link('admin/hifz/reports'),
      'match' => 'admin/hifz/reports*',
      'perms' => ['admin-hifz-reports'],
    ],
  ];
  $sections[] = [
    'key'      => 'hifz',
    'label'    => 'Hifz Program',
    'icon'     => 'fas fa-quran',
    'children' => $hifzItems,
    'visible'  => (bool) array_filter($hifzItems, fn($i) => $canAny($i['perms'] ?? [])),
  ];
}


  // Reports
  $reportsAttendanceItems = [
    ['key'=>'reports.attendance-report', 'label'=>'Attendance Report', 'icon'=>'fas fa-calendar-check', 'url'=>$link('admin/attendance-report'), 'match'=>'admin/attendance-report', 'perms'=>['admin-attendance-monthly-report']],
    ['key'=>'reports.attendance-monthly','label'=>'Attendance Monthly Reports','icon'=>'far fa-clock','url'=>$link('admin/attendance-monthly-report'),'match'=>'admin/attendance-monthly-report','perms'=>['admin-attendance-monthly-report']],
  ];
  $reportsFeeItems = [
    ['key'=>'reports.fee','label'=>'Fee Report','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report'),'match'=>'admin/student_fee_report','perms'=>['admin-student-fee-report']],
    ['key'=>'reports.student-fee-summary', 'label'=>'Student Fee Summary Report', 'icon'=>'fas fa-chart-pie', 'url'=>$link('admin/student_fee_summary'), 'match'=>'admin/student_fee_summary', 'perms'=>['admin-defaulter-student-fee-report']],
    ['key'=>'reports.defaulters-by-fee-type','label'=>'Defaulters Report by Fee Type','icon'=>'fas fa-users','url'=>$link('admin/defaulter_students_fee_report'),'match'=>'admin/defaulter_students_fee_report','perms'=>['admin-defaulter-student-fee-report']],
    ['key'=>'reports.student-prev-fee','label'=>'Student Prev Fee Report','icon'=>'fas fa-users','url'=>$link('admin/students_prevfee'),'match'=>'admin/students_prevfee','perms'=>['admin-defaulter-student-fee-report']],
    ['key'=>'reports.family-prev-fee','label'=>'Family Prev Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_prevfee'),'match'=>'admin/parents_prevfee','perms'=>['admin-defaulter-student-fee-report']],
    ['key'=>'reports.family-paid-fee','label'=>'Family Paid Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_paidfee'),'match'=>'admin/parents_paidfee','perms'=>['admin-student-fee-report']],
    ['key'=>'reports.family-balance-fee','label'=>'Family Balance Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_balancefee'),'match'=>'admin/parents_balancefee','perms'=>['admin-student-fee-report']],
    ['key'=>'reports.fee-by-month','label'=>'Fee Report By Month','icon'=>'fas fa-users','url'=>$link('admin/fee_chalan_month'),'match'=>'admin/fee_chalan_month','perms'=>['admin-student-fee-report']],
    ['key'=>'reports.family-fee','label'=>'Family Fee Report','icon'=>'fas fa-users','url'=>$link('admin/family_fee_report'),'match'=>'admin/family_fee_report','perms'=>['admin-family-fee-report']],
    ['key'=>'reports.by-fee-type','label'=>'Report By Fee Type','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report/report_by_fee_type'),'match'=>'admin/student_fee_report/report_by_fee_type','perms'=>['admin-report-by-fee-type']],
    ['key'=>'reports.by-student-fee','label'=>'Report By Student Fee','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report/report_by_fee_student'),'match'=>'admin/student_fee_report/report_by_fee_student','perms'=>['admin-report-by-student-fee']],
    ['key'   => 'reports.fee-collection-session-wise','label' => 'Fee Collection Session Wise','icon'  => 'fas fa-file-invoice-dollar','url'   => $link('admin/fee-collection-session-wise'),'match' => 'admin/fee-collection-session-wise','perms' => ['admin-profit-loss-reports']],
  ];
  $reportsAcademicItems = [
    ['key'=>'reports.classwise-result','label'=>'Class Wise Result','icon'=>'fas fa-users','url'=>$link('admin/classwise_results'),'match'=>'admin/classwise_results','perms'=>['admin-classwise-result-report']],
    ['key'=>'reports.student-results','label'=>'Student Results','icon'=>'fas fa-users','url'=>$link('admin/student_results'),'match'=>'admin/student_results','perms'=>['admin-students-result-report']],
    ['key'=>'reports.datesheet-report','label'=>'Datesheet Report','icon'=>'fas fa-users','url'=>$link('admin/datesheet_report/add'),'match'=>'admin/datesheet_report','perms'=>['admin-datesheet-report']],
    ['key'=>'reports.strength-report','label'=>'Strength Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/ClasswiseMonthlyStrengthReport'),'match'=>'admin/ClasswiseMonthlyStrengthReport','perms'=>['admin-profit-loss-reports']],
  ];
  $reportsFinanceItems = [
    ['key'=>'reports.expenses','label'=>'Expenses Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expense_report'),'match'=>'admin/expense_report','perms'=>['admin-expense-reports']],
    ['key'=>'reports.assets','label'=>'Assets Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/assets_report'),'match'=>'admin/assets_report','perms'=>['admin-assets-report']],
    ['key'=>'reports.profit-loss','label'=>'Cash Flow / P&amp;L','icon'=>'fas fa-chart-line','url'=>$link('admin/profit_loss_report'),'match'=>'admin/profit_loss_report','perms'=>['admin-cash-flow-report','admin-profit-loss-reports']],
  ];

  $reportsItems = array_merge(
    [['label'=>'— Attendance —','icon'=>'','header'=>true]],
    $reportsAttendanceItems,
    [['label'=>'— Fee & Billing —','icon'=>'','header'=>true]],
    $reportsFeeItems,
    [['label'=>'— Academic —','icon'=>'','header'=>true]],
    $reportsAcademicItems,
    [['label'=>'— Accounts —','icon'=>'','header'=>true]],
    $reportsFinanceItems
  );
  $sections[] = [
    'key'=>'reports',
    'label'=>'Reports',
    'icon'=>'far fa-address-card',
    'children'=>$reportsItems,
    'visible'=> (bool) array_filter(array_merge($reportsAttendanceItems, $reportsFeeItems, $reportsAcademicItems, $reportsFinanceItems), fn($i)=>$canAny($i['perms']))
  ];

  // Optional modules
  if ($hasTransport) {
    $transportItems = [
      ['key'=>'transport.vehicles','label'=>'Vehicles','icon'=>'fa fa-list','url'=>$link('admin/vehicles'),'match'=>'admin/vehicles','perms'=>['admin-vehicles']],
    ];
    $sections[] = [
      'key'=>'transport','label'=>'Transport','icon'=>'far fa-address-card','children'=>$transportItems,
      'visible'=> (bool) array_filter($transportItems, fn($i)=>$canAny($i['perms']))
    ];
  }
  if ($hasHostel) {
    $hostelItems = [
      ['key'=>'hostel.blocks','label'=>'Blocks','icon'=>'fa fa-list','url'=>$link('admin/h_blocks'),'match'=>'admin/h_blocks','perms'=>['admin-blocks']],
      ['key'=>'hostel.rooms','label'=>'Rooms','icon'=>'fa fa-list','url'=>$link('admin/h_rooms'),'match'=>'admin/h_rooms','perms'=>['admin-blocks']],
      ['key'=>'hostel.beds','label'=>'Beds','icon'=>'fa fa-list','url'=>$link('admin/h_beds'),'match'=>'admin/h_beds','perms'=>['admin-blocks']],
      ['key'=>'hostel.block-rooms','label'=>'Block Rooms','icon'=>'fa fa-list','url'=>$link('admin/h_block_rooms'),'match'=>'admin/h_block_rooms','perms'=>['admin-blocks']],
      ['key'=>'hostel.room-beds','label'=>'Rooms Beds','icon'=>'fa fa-list','url'=>$link('admin/h_room_beds'),'match'=>'admin/h_room_beds','perms'=>['admin-blocks']],
      ['key'=>'hostel.fee-amount','label'=>'Hostel Fee Amount','icon'=>'fa fa-list','url'=>$link('admin/h_fee_amount/add'),'match'=>'admin/h_fee_amount','perms'=>['admin-blocks']],
      ['key'=>'hostel.student-beds','label'=>'Student Beds','icon'=>'fa fa-list','url'=>$link('admin/h_student_beds/add'),'match'=>'admin/h_student_beds','perms'=>['admin-blocks']],
      ['key'=>'hostel.student-report','label'=>'Hostel Student Report','icon'=>'fa fa-list','url'=>$link('admin/h_student_report'),'match'=>'admin/h_student_report$','perms'=>['admin-blocks']],
      ['key'=>'hostel.student-report2','label'=>'Hostel Student Report2','icon'=>'fa fa-list','url'=>$link('admin/h_student_report/report2'),'match'=>'admin/h_student_report/report2','perms'=>['admin-blocks']],
      ['key'=>'hostel.defaulter','label'=>'Hostel Student Defaulter','icon'=>'fa fa-list','url'=>$link('admin/h_student_report/defaulter'),'match'=>'admin/h_student_report/defaulter','perms'=>['admin-blocks']],
    ];
    $sections[] = [
      'key'=>'hostel','label'=>'Hostel','icon'=>'fa fa-list','children'=>$hostelItems,
      'visible'=> (bool) array_filter($hostelItems, fn($i)=>$canAny($i['perms']))
    ];
  }
  if ($hasAcademy) {
    $academyItems = [
      ['key'=>'academy.groups','label'=>'A Groups','icon'=>'fa fa-list','url'=>$link('admin/a_groups'),'match'=>'admin/a_groups','perms'=>['admin-academy']],
      ['key'=>'academy.class-subjects','label'=>'Class Subjects','icon'=>'fa fa-list','url'=>$link('admin/a_section_subjects'),'match'=>'admin/a_section_subjects','perms'=>['admin-academy']],
      ['key'=>'academy.subject-groups','label'=>'Subject Groups','icon'=>'fa fa-list','url'=>$link('admin/a_subject_group/add'),'match'=>'admin/a_subject_group','perms'=>['admin-academy']],
      ['key'=>'academy.teacher-groups','label'=>'Teacher Groups','icon'=>'fa fa-list','url'=>$link('admin/a_teacher_group/add'),'match'=>'admin/a_teacher_group','perms'=>['admin-academy']],
      ['key'=>'academy.fee-amount','label'=>'A Fee Amount','icon'=>'fa fa-list','url'=>$link('admin/a_fee_amount/add'),'match'=>'admin/a_fee_amount','perms'=>['admin-academy']],
      ['key'=>'academy.students','label'=>'A Students','icon'=>'fa fa-list','url'=>$link('admin/students_bulk_academy_fee'),'match'=>'admin/students_bulk_academy_fee','perms'=>['admin-academy']],
    ];
    $sections[] = [
      'key'=>'academy','label'=>'Academy','icon'=>'fa fa-list','children'=>$academyItems,
      'visible'=> (bool) array_filter($academyItems, fn($i)=>$canAny($i['perms']))
    ];
  }

  $isDirectorSystemRole = function_exists('userHasAnyRoleNameLike')
      ? userHasAnyRoleNameLike('director system')
      : (strpos(strtolower(trim((string) ($role_name_info->rolename ?? ''))), 'director system') !== false);
  if ($can('admin-campus') || $isDirectorSystemRole) {
    $sections[] = ['key'=>'campus','label'=>'Campus','icon'=>'fa fa-home','url'=>$link('admin/campus'),'match'=>'admin/campus','visible'=>true];
  }

  $markSuperAdminOnly = static function (array $items): array {
      return array_map(static function (array $item): array {
          $item['super_admin_only'] = true;

          return $item;
      }, $items);
  };

  $billingItems = $markSuperAdminOnly([
    ['key'=>'billing.bill-amount','label'=>'Bill Amount','icon'=>'fa fa-list','url'=>$link('admin/bill_amount/add'),'match'=>'admin/bill_amount','perms'=>['admin-bill-amount']],
    ['key'=>'billing.plan-months','label'=>'Bill Plan Months','icon'=>'fa fa-list','url'=>$link('admin/bill_plan_months'),'match'=>'admin/bill_plan_months','perms'=>['admin-bill-plan-months']],
    ['key'=>'billing.pay-campus-chalan','label'=>'Pay Campus Chalan','icon'=>'fa fa-list','url'=>$link('admin/campus_chalan_pay'),'match'=>'admin/campus_chalan_pay','perms'=>['admin-campus-chalan-pay']],
    ['key'=>'billing.pay-campus-bill','label'=>'Pay Campus Bill','icon'=>'fa fa-home','url'=>$link('admin/pay_campus_bill'),'match'=>'admin/pay_campus_bill','perms'=>['admin-pay-campus-bill']],
    ['key'=>'billing.invoice','label'=>'Billing Invoice','icon'=>'fas fa-file-invoice','url'=>$link('admin/campus_plans'),'match'=>'admin/campus_plans','perms'=>['admin-campus-plans']],
    ['key'=>'billing.pay-system-bill','label'=>'Pay System Bill','icon'=>'fa fa-home','url'=>$link('admin/pay_system_bill'),'match'=>'admin/pay_system_bill','perms'=>['admin-pay-system-bill']],
    ['key'=>'billing.login-log','label'=>'Login Log','icon'=>'fa fa-home','url'=>$link('admin/ci_session_view'),'match'=>'admin/ci_session_view','perms'=>['admin-ci-session_view']],
    ['key'=>'billing.demo-login-log','label'=>'Demo Login Log','icon'=>'fa fa-home','url'=>$link('admin/ci_session_view_demo'),'match'=>'admin/ci_session_view_demo','perms'=>['admin-ci-session_view']],
  ]);
  $planMgmt = $markSuperAdminOnly([
    ['key'=>'admin.roles','label'=>'Roles','icon'=>'fa fa-users','url'=>$link('admin/roles'),'match'=>'admin/roles','perms'=>['admin-roles']],
    ['key'=>'admin.permissions','label'=>'Permissions','icon'=>'fa fa-users','url'=>$link('admin/permissions'),'match'=>'admin/permissions','perms'=>['admin-permissions']],
    ['key' => 'campus.management.report', 'label' => 'Campus Report', 'icon' => 'fas fa-chart-line', 'url' => $link('admin/campus-management'), 'match' => 'admin/campus-management', 'perms' => ['admin-permissions']],
    ['key' => 'campus.management.settings', 'label' => 'Campus Settings', 'icon' => 'fas fa-cog', 'url' => $link('admin/campus-settings'), 'match' => 'admin/campus-settings', 'perms' => ['admin-permissions']],
  ]);

  helper('role');
  $sections[] = [
    'key'=>'billing-admin',
    'label'=>'Billing & Admin',
    'icon'=>'far fa-address-card',
    'children'=>array_merge(
      [['label'=>'— Billing —','icon'=>'','header'=>true]],
      $billingItems,
      [['label'=>'— Plan Management —','icon'=>'','header'=>true]],
      $planMgmt
    ),
    'visible'=> (bool) array_filter(
        array_merge($billingItems, $planMgmt),
        static fn ($i) => quizzesMenuItemVisible($i, $canAny)
    ),
  ];

// ===== Menu Quality Pass (captions + order + dedupe) =====
  $captionMap = [
      'time table' => 'Timetable',
      'school timing type' => 'Timing Type',
      'create employee leaves' => 'Create Employee Leave',
      'employee leaves applications' => 'Employee Leave Requests',
      'students monthly report' => 'Student Monthly Report',
      'students session report' => 'Student Session Report',
      'create leaves applications' => 'Create Student Leave',
      'leaves applications' => 'Student Leave Requests',
      'vocabulary words' => 'Vocabulary Words',
      'student prev fee report' => 'Student Previous Fee Report',
      'family prev fee report' => 'Family Previous Fee Report',
      'fee report by month' => 'Monthly Fee Report',
      'report by fee type' => 'Fee Type Report',
      'report by student fee' => 'Student Fee Report',
      'class wise result' => 'Class Result',
      'student results' => 'Student Result',
      'students absentees report' => 'Student Absentees Report',
      'send test series result (wa)' => 'Send Test Series Result (WhatsApp)',
      'send result (wa)' => 'Send Result (WhatsApp)',
      'send fee chalan (wa)' => 'Send Fee Chalan (WhatsApp)',
      'send daily diary (wa)' => 'Send Daily Diary (WhatsApp)',
      'a groups' => 'Academy Groups',
      'a fee amount' => 'Academy Fee Amount',
      'a students' => 'Academy Students',
  ];

  $normalizeLabel = function ($label) use ($captionMap) {
      $label = trim((string) $label);
      if ($label === '') {
          return $label;
      }
      $label = preg_replace('/^[\s\-\|├└─]+/u', '', $label);
      if (preg_match('/^[━\-\s]+$/u', $label)) {
          return '';
      }
      $key = strtolower($label);
      if (isset($captionMap[$key])) {
          return $captionMap[$key];
      }

      return preg_replace('/\s+/', ' ', $label);
  };

  $cleanMenuItems = function (array $items) use (&$cleanMenuItems, $normalizeLabel): array {
      $out  = [];
      $seen = [];
      foreach ($items as $item) {
          if (! is_array($item)) {
              continue;
          }
          $item['label'] = $normalizeLabel($item['label'] ?? '');
          if (empty($item['label']) && empty($item['header'])) {
              continue;
          }

          if (! empty($item['children']) && is_array($item['children'])) {
              $item['children'] = $cleanMenuItems($item['children']);
          }

          $signature = $item['key'] ?? (($item['url'] ?? '') . '|' . ($item['match'] ?? '') . '|' . ($item['label'] ?? ''));
          if (isset($seen[$signature])) {
              continue;
          }
          $seen[$signature] = true;
          $out[]            = $item;
      }

      return $out;
  };

  $sections = $cleanMenuItems($sections);

  $sectionOrder = [
      'dashboard'       => 5,
      'profiles'        => 8,
      'getting-started' => 9,
      'sessions'        => 10,
      'classes'         => 20,
      'students'        => 30,
      'faculty'         => 40,
      'health'          => 45,
      'exams-tests'     => 50,
      'quizzes'         => 55,
      'question-bank'   => 56,
      'attendance'      => 70,
      'timetable'       => 80,
      'academics'       => 90,
      'communication'   => 100,
      'finance'         => 110,
      'reports'         => 120,
      'sports'          => 130,
      'hifz'            => 135,
      'campus'          => 170,
      'custom-campus'   => 180,
      'billing-admin'   => 190,
  ];
  usort($sections, static function ($a, $b) use ($sectionOrder) {
      $ak = $a['key'] ?? '';
      $bk = $b['key'] ?? '';
      $ao = $sectionOrder[$ak] ?? 999;
      $bo = $sectionOrder[$bk] ?? 999;
      if ($ao === $bo) {
          return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
      }

      return $ao <=> $bo;
  });
