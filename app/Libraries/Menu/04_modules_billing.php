<?php

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
