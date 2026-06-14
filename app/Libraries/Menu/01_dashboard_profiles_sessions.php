<?php
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
