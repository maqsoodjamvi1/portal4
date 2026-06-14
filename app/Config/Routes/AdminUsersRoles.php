<?php

/**
 * AdminUsersRoles
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Salary Settings
    $routes->get('salary-settings', 'SalarySettings::index');
    $routes->post('salary-settings/save', 'SalarySettings::save');
    $routes->post('salary-settings/generate', 'SalarySettings::generateMonthly');
    $routes->get('salary-settings/bulk-adjustment', 'SalarySettings::bulkAdjustment');
    $routes->post('salary-settings/bulk-adjustment/load', 'SalarySettings::loadBulkAdjustment');
    $routes->post('salary-settings/bulk-adjustment/generate', 'SalarySettings::generateBulkAdjustment');
    $routes->get('salary-settings/bulk-adjustment/print', 'SalarySettings::printBulkSlips');
    $routes->get('salary-settings/bulk-adjustment/export', 'SalarySettings::exportBulkSlips');

    // Salary Reports
    $routes->get('salary-reports', 'SalarySettings::reports');
    $routes->get('salary-slips', 'SalarySlips::index');


    // Advance Salary
     $routes->get('advance-salary', 'AdvanceSalary::index');
    $routes->post('advance-salary/request', 'AdvanceSalary::request');
    $routes->get('advance-salary/approve/(:num)', 'AdvanceSalary::approve/$1');   // Changed to GET
    $routes->get('advance-salary/reject/(:num)', 'AdvanceSalary::reject/$1');     // Changed to GET
    $routes->get('advance-salary/details/(:num)', 'AdvanceSalary::details/$1');   // AJAX endpoint


    // Bonuses
    $routes->get('bonuses', 'Bonuses::index');
    $routes->post('bonuses/add', 'Bonuses::add');
    $routes->post('bonuses/delete/(:num)', 'Bonuses::delete/$1');

    // Employee Salary Management
    $routes->post('users/update-salary', 'Users::updateSalary');
    $routes->post('users/save-salary-rules', 'Users::saveSalaryRules');
    $routes->get('users/view-salary-slip/(:num)/(:num)', 'Users::viewSalarySlip/$1/$2');
    $routes->post('users/update-payment-status', 'Users::updatePaymentStatus');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // QR Code routes
    $routes->get('test-qr-generate', 'TeacherQr::testGenerate');
    $routes->get('qr/generate/(:num)', 'TeacherQr::generate/$1');
    $routes->get('qr/generate-all', 'TeacherQr::generate');
    $routes->get('qr/view/(:num)', 'TeacherQr::view/$1');
    $routes->get('qr/download-all', 'TeacherQr::downloadAll');
    $routes->get('qr/print/(:num)', 'TeacherQr::print/$1');

    // Attendance routes




    $routes->get('attendance/scan', 'Attendance::scan');
    $routes->post('attendance/process', 'Attendance::process');



    $routes->get('attendance/today-stats', 'Attendance::todayStats');
    $routes->get('attendance/manual', 'Attendance::manual');
$routes->post('attendance/manual', 'Attendance::manual');
$routes->get('attendance/report', 'Attendance::report');
  $routes->get('attendance/summary', 'Attendance::summary');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('roles/debug_permissions', 'Roles::debug_permissions');
    $routes->post('roles/get_permissions_direct', 'Roles::get_permissions_direct');
    $routes->post('roles/get_permissions_list', 'Roles::get_permissions_list');
    $routes->post('roles/get_role_by_name', 'Roles::get_role_by_name');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    // ============ ROLES ROUTES ============
    $routes->get('roles', 'Roles::index');
    $routes->post('roles/data', 'Roles::data');
    $routes->get('roles/add', 'Roles::add');
    $routes->get('roles/edit/(:num)', 'Roles::edit/$1');
    $routes->get('roles/edit', 'Roles::edit');
    $routes->post('roles/save', 'Roles::save');
    $routes->delete('roles/delete/(:num)', 'Roles::delete/$1');
    $routes->get('roles/delete', 'Roles::delete');
    $routes->post('roles/perm_data', 'Roles::permData');
    $routes->post('roles/menu_perm_data', 'Roles::menuPermData');
    $routes->post('roles/save_menu_access', 'Roles::saveMenuAccess');
    $routes->get('roles/getPermissionTree', 'Roles::getPermissionTree');

    // ============ PERMISSIONS ROUTES ============
    $routes->get('permissions', 'Permissions::index');
    $routes->post('permissions/data', 'Permissions::data');
    $routes->post('permissions/data2', 'Permissions::data2');
    $routes->get('permissions/getTreeData', 'Permissions::getTreeData');
    $routes->get('permissions/add', 'Permissions::add');
    $routes->get('permissions/edit/(:num)', 'Permissions::edit/$1');
    $routes->get('permissions/edit', 'Permissions::edit');
    $routes->post('permissions/save', 'Permissions::save');
    $routes->delete('permissions/delete/(:num)', 'Permissions::delete/$1');
    $routes->get('permissions/delete', 'Permissions::delete');
    $routes->post('permissions/bulkDelete', 'Permissions::bulkDelete');
    $routes->get('permissions/test', 'Permissions::test');
    $routes->get('permissions/test_insert', 'Permissions::test_insert');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->post('login/changePassword', 'Login::changePassword');
    $routes->post('users/editPassword', 'Users::editPassword');
    $routes->post('calendar/save', 'Calendar::save');
    $routes->post('login/findPassword', 'Login::findPassword');
    $routes->post('permissions/save', 'Permissions::save');
    $routes->post('profile/updatePassword', 'Profile::updatePassword');
    $routes->post('profile/update_password', 'Profile::update_password');
    $routes->post('roles/save', 'Roles::save');
    $routes->post('users/setPerms', 'Users::setPerms');
    $routes->post('users/save', 'Users::save');

    $routes->get('roles/data', 'Roles::data');
    $routes->get('users/data', 'Users::data');
    $routes->get('users/qr/(:num)', 'Users::qr/$1');
    $routes->get('test-qr-system', 'TestQRSystem::index');

    $routes->get('users', 'Users::index');
    $routes->get('users/add', 'Users::add');
    $routes->get('users/edit/(:num)', 'Users::edit/$1');
    $routes->get('users_bulk_info', 'UsersBulkInfo::index');
    $routes->post('users_bulk_info/load_rows', 'UsersBulkInfo::loadRows');
    $routes->post('users_bulk_info/save_row', 'UsersBulkInfo::saveRow');
    $routes->post('users_bulk_info/save_batch_new', 'UsersBulkInfo::saveBatchNew');
    $routes->get('users_bulk_info/search', 'UsersBulkInfo::searchEmployees');
    $routes->get('users/debug-role-mapping/(:num)', 'Users::debugRoleMapping/$1');

    // Status management
    $routes->post('users/toggleStatus', 'Users::toggleStatus');

    // ===== EMPLOYEE DETAIL VIEWS (Tab-based) =====

    // Profile view (main tab)
    $routes->get('users/view/(:num)', 'Users::view/$1');

    // Subjects view tab
    $routes->get('users/subjects/(:num)', 'Users::subjects/$1');

    // Timetable view tab
    $routes->get('users/timetable/(:num)', 'Users::timetable/$1');

    // Salary history list tab
    $routes->get('users/salary/(:num)', 'Users::salary/$1');

    // ===== SALARY SLIP ROUTES =====

    // View specific salary slip
    $routes->get('users/salarySlip/(:num)/(:num)', 'Users::salarySlip/$1/$2');

    // ===== OPTIONAL ROUTES (Add if needed) =====

    // Quick view modal data (AJAX)
    $routes->get('users/quickView/(:num)', 'Users::quickView/$1');

    // Export functionality
    $routes->get('users/export', 'Users::export');
    $routes->get('users/export/salary/(:num)', 'Users::exportSalary/$1');

    // Attendance view tab
    $routes->get('users/attendance/(:num)', 'Users::attendance/$1');

    // Leaves view tab
    $routes->get('users/leaves/(:num)', 'Users::leaves/$1');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    $routes->get('quiz', 'Quiz::index');
    $routes->post('quiz/generate', 'Quiz::generate');
    $routes->post('quiz/save', 'Quiz::save');

    $routes->get('logout', 'Logout::index');
    $routes->get('login', 'Login::index');
    //$routes->post('login', 'Login::index');
    $routes->post('login/submit', 'Login::submit');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('getting-started', 'GettingStarted::index');
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    // Specific paths first; bare `question-bank` must be last (overview also at admin/question-bank/overview above).
    $routes->get('question-bank/list', 'QuestionBank::list');
    $routes->get('question-bank/subjects', 'QuestionBank::subjects');
    $routes->get('question-bank/topics', 'QuestionBank::topics');
    $routes->get('question-bank/proof-read', 'QuestionBank::proofRead');
    $routes->get('question-bank/summary', 'QuestionBank::summary');
    $routes->get('question-bank/summary-all', 'QuestionBank::summary_all');
    $routes->get('question-bank/questions-json', 'QuestionBank::questionsJson');
    $routes->post('question-bank/save', 'QuestionBank::save');
    $routes->post('question-bank/save-ajax', 'QuestionBank::saveAjax');
    $routes->post('question-bank/delete', 'QuestionBank::deleteQuestion');
    $routes->get('question-bank/question/(:num)', 'QuestionBank::questionOne/$1');
    $routes->post('question-bank/save-topic', 'QuestionBank::saveTopic');
    $routes->post('question-bank/parse-mcqs', 'QuestionBank::parseJsonMcqs');
    $routes->get('question-bank', 'QuestionBank::index');

    $routes->get('question-paper', 'QuestionPaper::index');
    $routes->get('question-paper/ajax/qb-summary', 'QuestionPaper::ajaxSummary');
    $routes->post('question-paper/ajax/qb-questions', 'QuestionPaper::ajaxQuestions');
    $routes->post('question-paper/preview', 'QuestionPaper::preview');
    $routes->post('question-paper/print', 'QuestionPaper::print');
    $routes->post('question-paper/print-key', 'QuestionPaper::printKey');
    $routes->post('question-paper/print-versions', 'QuestionPaper::printVersions');
    $routes->post('question-paper/download-word', 'QuestionPaper::downloadWord');
    $routes->post('question-paper/download-word-key', 'QuestionPaper::downloadWordKey');
    $routes->get('question-paper/templates', 'QuestionPaper::templates');
    $routes->post('question-paper/templates/save', 'QuestionPaper::saveTemplate');
    $routes->get('question-paper/templates/(:num)', 'QuestionPaper::loadTemplate/$1');
    $routes->post('question-paper/templates/delete/(:num)', 'QuestionPaper::deleteTemplate/$1');

    $routes->post('question-paper/store', 'QuestionPaper::store');
    $routes->get('question-paper/ajax/by-filters', 'QuestionPaper::ajaxByFilters');
    $routes->get('question-paper/print-settings/(:num)', 'QuestionPaper::printSettings/$1');
    $routes->post('question-paper/print-saved/(:num)', 'QuestionPaper::printSaved/$1');
    $routes->post('question-paper/print-saved-key/(:num)', 'QuestionPaper::printSavedKey/$1');

    $routes->get('assessment-builder', 'AssessmentBuilder::index');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('vocab-bank', 'VocabBank::index');
    $routes->post('vocab-bank/save', 'VocabBank::save');

    $routes->get('vocab-bank/list', 'VocabBank::list');
    // AJAX
    $routes->get('vocab-bank/get-all-subjects', 'VocabBank::getAllSubjects');
    $routes->get('vocab-bank/topics', 'VocabBank::topics');

    $routes->post('vocab-bank/save-topic', 'VocabBank::saveTopic');
$routes->get('vocab-bank/summary', 'VocabBank::summary');
    // AI
   // $routes->post('question-bank/ai-generate', 'QuestionBank::aiGenerate');
    $routes->post('vocab-bank/parse-mcqs', 'VocabBank::parseJsonMcqs');
       $routes->get('vocab-bank/getCount',  'VocabBank::getCount');

         $routes->get('vocab-bank/report', 'VocabBank::report');
         $routes->get('vocab-bank/listofwords', 'VocabBank::listofwords');
    $routes->get('vocab-bank/report-data', 'VocabBank::reportData');

});
