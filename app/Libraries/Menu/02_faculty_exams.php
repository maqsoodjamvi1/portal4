<?php

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
