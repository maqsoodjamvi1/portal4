<?php

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
