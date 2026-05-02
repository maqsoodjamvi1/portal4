<?php

namespace Config;

use CodeIgniter\Router\Router;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();
$routes->get('admin', 'Home::getAdmin');
$routes->get('/', 'Home::index');

// Default settings
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

$routes->get('qr-debug', 'QrDebug::index');


$routes->get('admin/salary-debug', 'Admin\SalaryDebug::index');
// Salary Management Routes

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Health Alerts Routes
    $routes->get('health/alerts', 'HealthBmi::alerts');
    $routes->post('health/alerts/data', 'HealthBmi::getAlertsData');
    $routes->post('health/alerts/mark-read/(:num)', 'HealthBmi::markAlertRead/$1');
    $routes->post('health/alerts/mark-all-read', 'HealthBmi::markAllAlertsRead');
    $routes->get('health/alerts/send-notifications', 'HealthBmi::sendAlertNotifications');
});


$routes->get('language/set/(:any)', 'LanguageController::set/$1');
$routes->get('language/set', 'LanguageController::set');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Salary Settings

    
    // ==================== HEALTH & BMI MODULE ====================
    
    // BMI Dashboard & Overview
    $routes->get('health/bmi-dashboard', 'HealthBmi::dashboard');
    $routes->get('health/bmi-stats-data', 'HealthBmi::getStatsData');
    $routes->post('process-teacher-attendance', 'Dashboard::processTeacherAttendance');
$routes->get('get-recent-attendance', 'Dashboard::getRecentAttendance');

      $routes->get('health/growth-charts', 'HealthBmi::growthCharts');
    $routes->get('health/growth-charts/data/(:num)', 'HealthBmi::getGrowthChartData/$1');


    // BMI Records Management
    $routes->get('health/bmi-records', 'HealthBmi::records');
    $routes->get('health/bmi-records/data', 'HealthBmi::getRecordsData');
    $routes->post('health/bmi-records/save', 'HealthBmi::saveRecord');
    $routes->get('health/bmi-records/delete/(:num)', 'HealthBmi::deleteRecord/$1');
    
    // Bulk BMI Update (existing route)
  
    
    // Health Alerts
    $routes->get('health/alerts', 'HealthBmi::alerts');
    $routes->get('health/alerts/data', 'HealthBmi::getAlertsData');
    $routes->post('health/alerts/mark-read/(:num)', 'HealthBmi::markAlertRead/$1');
    $routes->post('health/alerts/mark-all-read', 'HealthBmi::markAllAlertsRead');
    $routes->get('health/alerts/send-notifications', 'HealthBmi::sendAlertNotifications');
    
    // Nutrition Suggestions
    $routes->get('health/nutrition-suggestions', 'HealthBmi::nutritionSuggestions');
    $routes->get('health/nutrition-suggestions/data', 'HealthBmi::getNutritionSuggestionsData');
    $routes->post('health/nutrition-suggestions/add', 'HealthBmi::addNutritionSuggestion');
    $routes->post('health/nutrition-suggestions/update/(:num)', 'HealthBmi::updateNutritionSuggestion/$1');
    $routes->post('health/nutrition-suggestions/delete/(:num)', 'HealthBmi::deleteNutritionSuggestion/$1');
    
    // BMI Reports
    $routes->get('health/bmi-reports', 'HealthBmi::reports');
    $routes->post('health/bmi-reports/generate', 'HealthBmi::generateReport');
    $routes->get('health/bmi-reports/export-excel', 'HealthBmi::exportExcel');
    $routes->get('health/bmi-reports/export-pdf', 'HealthBmi::exportPdf');
    
    // Growth Charts
    $routes->get('health/growth-charts', 'HealthBmi::growthCharts');
    $routes->get('health/growth-charts/data/(:num)', 'HealthBmi::getGrowthChartData/$1');
    
    // Student BMI (for student profile)
    $routes->post('students/update-bmi', 'HealthBmi::updateStudentBmi');
    $routes->get('students/bmi-history/(:num)', 'HealthBmi::getBmiHistory/$1');
    

    $routes->post('students/get_outstanding_fee', 'Students::get_outstanding_fee');
$routes->post('students/process_fee_payment', 'Students::process_fee_payment');
$routes->post('students/mark_fee_paid', 'Students::mark_fee_paid');
$routes->post('students/update_basicinfo', 'Students::update_basicinfo');
    // Class-wise BMI Report
    $routes->get('health/class-bmi-report', 'HealthBmi::classBmiReport');
    $routes->post('health/class-bmi-report/data', 'HealthBmi::getClassBmiReportData');
    
    // BMI Statistics API (for dashboard widget)
    $routes->get('health/bmi-statistics', 'HealthBmi::getStatistics');
    
    // Individual Student BMI View
    $routes->get('students/bmi-view/(:num)', 'HealthBmi::viewStudentBmi/$1');
    $routes->post('students/bmi-add-record/(:num)', 'HealthBmi::addBmiRecord/$1');
    
    // Bulk Import BMI Data via Excel
    $routes->get('health/bmi-bulk-import', 'HealthBmi::bulkImport');
    $routes->post('health/bmi-bulk-import/upload', 'HealthBmi::uploadBulkBmi');
    $routes->get('health/bmi-bulk-import/download-template', 'HealthBmi::downloadTemplate');
});




$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Salary Settings
    $routes->get('salary-settings', 'SalarySettings::index');
    $routes->post('salary-settings/save', 'SalarySettings::save');
    $routes->post('salary-settings/generate', 'SalarySettings::generateMonthly');
    
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
 

 $routes->group('admin', function($routes) {
    // ... existing routes ...
    $routes->get('roles/debug_permissions', 'Admin\Roles::debug_permissions');
    $routes->post('roles/get_permissions_direct', 'Admin\Roles::get_permissions_direct');
     
      $routes->post('roles/get_permissions_list', 'Roles::get_permissions_list');
        $routes->post('roles/get_role_by_name', 'Admin\Roles::get_role_by_name');
});

$routes->group('admin', function($routes) {
    
    // ============ ROLES ROUTES ============
    // List routes
    $routes->get('roles', 'Admin\Roles::index');
    $routes->post('roles/data', 'Admin\Roles::data');

    
    // Add/Edit routes
    $routes->get('roles/add', 'Admin\Roles::add');
    $routes->get('roles/edit/(:num)', 'Admin\Roles::edit/$1');
    $routes->get('roles/edit', 'Admin\Roles::edit'); // For GET parameter style
    
    // Save route
    $routes->post('roles/save', 'Admin\Roles::save');
    
    // Delete route
    $routes->delete('roles/delete/(:num)', 'Admin\Roles::delete/$1');
    $routes->get('roles/delete', 'Admin\Roles::delete'); // For GET parameter style (if needed)
    
    // Permission tree routes
    $routes->post('roles/perm_data', 'Admin\Roles::permData');
    $routes->get('roles/getPermissionTree', 'Admin\Roles::getPermissionTree');
    
    // ============ PERMISSIONS ROUTES ============
    // List routes
    $routes->get('permissions', 'Admin\Permissions::index');
    $routes->post('permissions/data', 'Admin\Permissions::data');
    $routes->post('permissions/data2', 'Admin\Permissions::data2');
    $routes->get('permissions/getTreeData', 'Admin\Permissions::getTreeData');
    
    // Add/Edit routes
    $routes->get('permissions/add', 'Admin\Permissions::add');
    $routes->get('permissions/edit/(:num)', 'Admin\Permissions::edit/$1');
    $routes->get('permissions/edit', 'Admin\Permissions::edit');
    
    // Save route
    $routes->post('permissions/save', 'Admin\Permissions::save');
    
    // Delete routes
    $routes->delete('permissions/delete/(:num)', 'Admin\Permissions::delete/$1');
    $routes->get('permissions/delete', 'Admin\Permissions::delete');
    $routes->post('permissions/bulkDelete', 'Admin\Permissions::bulkDelete');
    
    // Permission test route (for debugging)
    $routes->get('permissions/test', 'Admin\Permissions::test');
    $routes->get('permissions/test_insert', 'Admin\Permissions::test_insert');
    
    
});

$routes->group('admin', function($routes) {
    // Existing routes - keep these
    $routes->post('login/changePassword', 'Admin\Login::changePassword');
    $routes->post('users/editPassword', 'Admin\Users::editPassword');
    $routes->post('calendar/save', 'Admin\Calendar::save');
    $routes->post('login/findPassword', 'Admin\Login::findPassword');
    $routes->post('permissions/save', 'Admin\Permissions::save');
    $routes->post('profile/updatePassword', 'Admin\Profile::updatePassword');
    $routes->post('roles/save', 'Admin\Roles::save');
    $routes->post('users/setPerms', 'Admin\Users::setPerms');
    $routes->post('users/save', 'Admin\Users::save');
    
    $routes->get('roles/data', 'Admin\Roles::data');
    $routes->get('users/data', 'Admin\Users::data');
    $routes->get('users/qr/(:num)', 'Admin\Users::qr/$1');
    $routes->get('test-qr-system', 'TestQRSystem::index');
    
    // ===== USER MANAGEMENT ROUTES =====
    
    // Main listing page
    $routes->get('users', 'Admin\Users::index');
    
    // Add/Edit operations
    $routes->get('users/add', 'Admin\Users::add');
    $routes->get('users/edit/(:num)', 'Admin\Users::edit/$1');
    
    // Status management
    $routes->post('users/toggleStatus', 'Admin\Users::toggleStatus');
    
    // ===== EMPLOYEE DETAIL VIEWS (Tab-based) =====
    
    // Profile view (main tab)
    $routes->get('users/view/(:num)', 'Admin\Users::view/$1');
    
    // Subjects view tab
    $routes->get('users/subjects/(:num)', 'Admin\Users::subjects/$1');
    
    // Timetable view tab
    $routes->get('users/timetable/(:num)', 'Admin\Users::timetable/$1');
    
    // Salary history list tab
    $routes->get('users/salary/(:num)', 'Admin\Users::salary/$1');
    
    // ===== SALARY SLIP ROUTES =====
    
    // View specific salary slip
    $routes->get('users/salarySlip/(:num)/(:num)', 'Admin\Users::salarySlip/$1/$2');
    
    // ===== OPTIONAL ROUTES (Add if needed) =====
    
    // Quick view modal data (AJAX)
    $routes->get('users/quickView/(:num)', 'Admin\Users::quickView/$1');
    
    // Export functionality
    $routes->get('users/export', 'Admin\Users::export');
    $routes->get('users/export/salary/(:num)', 'Admin\Users::exportSalary/$1');
    
    // Attendance view tab
    $routes->get('users/attendance/(:num)', 'Admin\Users::attendance/$1');
    
    // Leaves view tab
    $routes->get('users/leaves/(:num)', 'Admin\Users::leaves/$1');
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
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('question-bank', 'QuestionBank::index');
    $routes->post('question-bank/save', 'QuestionBank::save');

    $routes->get('question-bank/list', 'QuestionBank::list');
    // AJAX
    $routes->get('question-bank/subjects', 'QuestionBank::subjects');
    $routes->get('question-bank/topics', 'QuestionBank::topics');
    
    $routes->post('question-bank/save-topic', 'QuestionBank::saveTopic');



$routes->get('question-bank/summary', 'QuestionBank::summary');
   $routes->get('question-bank/summary-all', 'QuestionBank::summary_all');
    $routes->post('admin/question-bank/parse-mcqs', 'Admin\QuestionBank::parseJsonMcqs');
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

// Academic Setup Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('academic-setup', 'AcademicSetup::index');
    $routes->post('academic-setup/save-classes', 'AcademicSetup::saveClasses');
    $routes->post('academic-setup/save-sections', 'AcademicSetup::saveSections');
    $routes->post('academic-setup/save-subjects', 'AcademicSetup::saveSubjects');
    $routes->post('academic-setup/save-class-sections', 'AcademicSetup::saveClassSections');
    $routes->post('academic-setup/save-section-subjects', 'AcademicSetup::saveSectionSubjects');
    $routes->get('academic-setup/fetch-classes', 'AcademicSetup::fetchClasses');
    $routes->get('academic-setup/fetch-sections', 'AcademicSetup::fetchSections');
    $routes->get('academic-setup/fetch-subjects', 'AcademicSetup::fetchSubjects');
    $routes->get('academic-setup/get-class-sections-data', 'AcademicSetup::getClassSectionsData');
    $routes->get('academic-setup/get-section-subjects-data', 'AcademicSetup::getSectionSubjectsData');
    $routes->get('academic-setup/check-fee-types', 'AcademicSetup::checkFeeTypes');
});




// Add to your admin routes group
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    // Campus Management Routes
    $routes->get('campus-management', 'CampusManagement::index');
    $routes->post('campus-management/data', 'CampusManagement::data');
    $routes->post('campus-management/delete', 'CampusManagement::delete');
    $routes->post('campus-management/get-details', 'CampusManagement::getDetails');
    $routes->get('campus-management/export', 'CampusManagement::export');
    $routes->post('campus-management/toggle-status', 'CampusManagement::toggleStatus');
    $routes->get('campus-settings', 'CampusManagement::settings');
    $routes->post('campus-settings/save', 'CampusManagement::saveSettings');
    $routes->post('campus-management/clean-orphaned-files', 'CampusManagement::cleanOrphanedFiles');
    $routes->post('campus-management/bulk-delete', 'CampusManagement::bulkDelete');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('quiz-ai', 'QuizAi::index');
    $routes->post('quiz-ai/generate', 'QuizAi::generate');
    $routes->post('quiz-ai/save', 'QuizAi::save');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    // ===============================
    // Question Bank – Topics Manager
    // ===============================
    $routes->get('qb-topics', 'QbTopics::index');          // page
    $routes->get('qb-topics/data', 'QbTopics::data');      // ajax load
    $routes->post('qb-topics/save', 'QbTopics::saveBulk'); // ajax save

});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    // ===============================
    // Question Bank – Topics Manager
    // ===============================
    $routes->get('vocab-topics', 'VocabTopics::index');          // page
    $routes->get('vocab-topics/data', 'VocabTopics::data');      // ajax load
    $routes->post('vocab-topics/save', 'VocabTopics::saveBulk'); // ajax save

});

// Admin Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('permissions', 'Permissions::index');
    $routes->get('permissions/add', 'Permissions::add');
    $routes->get('permissions/edit/(:num)', 'Permissions::edit/$1');
    $routes->match(['GET', 'POST'], 'permissions/save', 'Permissions::save');
    $routes->get('permissions/delete', 'Permissions::delete');
    //$routes->get('permissions/data', 'Permissions::data');
    $routes->match(['GET', 'POST'], 'permissions/data', 'Permissions::data');
    $routes->match(['GET', 'POST'], 'permissions/data2', 'Permissions::data2');

    // $routes->get('academic_session', 'AcademicSession::index');
    // $routes->get('academic_session/add', 'AcademicSession::add');
    // $routes->post('academic_session/data', 'AcademicSession::data'); 
    // $routes->post('academic_session/save', 'AcademicSession::save'); 

});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Academic Session Routes
    $routes->get('academic_session', 'AcademicSession::index'); // List view
    $routes->get('academic_session/add', 'AcademicSession::add'); // Add form
    //$routes->get('academic_session/edit/(:num)', 'AcademicSession::edit/$1'); // Edit form with session_id
    $routes->get('academic_session/edit/(:num)', 'AcademicSession::edit/$1');

    $routes->post('academic_session/data', 'AcademicSession::data'); // Data for DataTables
    $routes->post('academic_session/save', 'AcademicSession::save'); // Save (create/update)

    $routes->get('academic_session/delete', 'AcademicSession::delete'); // Delete session (by ID via GET param)
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('profile', 'Profile::index');
    $routes->post('profile/save', 'Profile::save');
    $routes->post('profile/update-password', 'Profile::update_password'); 
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('terms', 'Terms::index');
    $routes->post('terms/data', 'Terms::data');
    $routes->get('terms/add', 'Terms::add');
    $routes->get('terms/edit', 'Terms::edit');
    $routes->post('terms/save', 'Terms::save');
    $routes->get('terms/delete', 'Terms::delete');
    $routes->post('terms/toggle-status', 'Terms::toggleStatus');


    
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('terms_session', 'TermsSession::index');
    $routes->post('terms_session/data', 'TermsSession::data');
    $routes->post('terms_session/data2', 'TermsSession::data2');
    $routes->get('terms_session/add', 'TermsSession::add');
    $routes->get('terms_session/edit', 'TermsSession::edit');
    $routes->post('terms_session/save', 'TermsSession::save');
    $routes->get('terms_session/delete', 'TermsSession::delete');
});

// Admin Term Weeks Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('term_weeks', 'TermWeeks::index');
    $routes->post('term_weeks/data', 'TermWeeks::data');
    $routes->get('term_weeks/add', 'TermWeeks::add');
    $routes->get('term_weeks/edit', 'TermWeeks::edit');
    $routes->post('term_weeks/save', 'TermWeeks::save');
    $routes->post('term_weeks/generate_term_weeks', 'TermWeeks::generate_term_weeks');
    $routes->get('term_weeks/delete', 'TermWeeks::delete');
});

// Admin Classes Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('classes', 'Classes::index');
    $routes->post('classes/data', 'Classes::data');
    $routes->get('classes/add', 'Classes::add');
    $routes->get('classes/edit', 'Classes::edit'); // expects ?id= in URL
    $routes->post('classes/save', 'Classes::save');
    
    $routes->post('classes/toggle-status', 'Classes::toggleStatus');
});

// Admin Sections Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('sections', 'Sections::index');
    $routes->post('sections/data', 'Sections::data');
    $routes->get('sections/add', 'Sections::add');
    $routes->get('sections/edit', 'Sections::edit'); // optional ?id= param handled inside
    $routes->post('sections/save', 'Sections::save');
    $routes->get('sections/delete', 'Sections::delete'); // expects ?id= in URL
     $routes->post('sections/toggle-status', 'Sections::toggleStatus');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    $routes->get('class-section', 'ClassSection::index');
    $routes->get('class-section/getData', 'ClassSection::getData');
    $routes->post('class-section/update', 'ClassSection::update');
    $routes->get('class-section/getTeachers', 'ClassSection::getTeachers');
    $routes->post('class-section/assignTeacher', 'ClassSection::assignTeacher');

});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Subjects module routes
    $routes->get('subjects', 'Subjects::index');
    $routes->post('subjects/data', 'Subjects::data');
    $routes->get('subjects/add', 'Subjects::add');
    $routes->get('subjects/edit', 'Subjects::edit'); // expects ?id=...
    $routes->post('subjects/save', 'Subjects::save');
    $routes->get('subjects/delete', 'Subjects::delete'); // expects ?id=...
    $routes->post('subjects/toggle-status', 'Subjects::toggleStatus');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('section_subjects', 'SectionSubjects::index');
    $routes->get('section_subjects/getData', 'SectionSubjects::getData');
    $routes->post('section_subjects/update', 'SectionSubjects::update');
    $routes->post('section_subjects/assignTeacher', 'SectionSubjects::assignTeacher');
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students', 'Students::index');
    $routes->post('students/data', 'Students::data');
    $routes->get('students/add', 'Students::add');
    $routes->get('students/edit', 'Students::edit');
    $routes->get('students/delete', 'Students::delete');
    $routes->get('students/getStatus', 'Students::getStatus');


  // Readmit Student Routes
    $routes->get('students/readmit', 'Students::readmit');
    $routes->post('students/search_drop_students', 'Students::search_drop_students');
    $routes->post('students/get_student_readmit_info', 'Students::get_student_readmit_info');
    $routes->post('students/process_readmission', 'Students::process_readmission');
    $routes->post('students/get_fee_history', 'Students::get_fee_history');


    $routes->post('students/save_basicinfo', 'Students::save_basicinfo');
    $routes->post('students/save_admission', 'Students::save_admission');
    $routes->post('students/update_basicinfo', 'Students::update_basicinfo');
    $routes->post('students/search_siblings', 'Students::search_siblings');
$routes->post('students/get_parent_info', 'Students::get_parent_info');

   // $routes->post('admin/students/save_admission', 'Admin\\Students::save_admission');
   
    $routes->post('students/get_fee_amount', 'Students::get_fee_amount');
    $routes->post('ajax/get_class_fee_amounts', 'Students::get_class_fee_amounts');

    $routes->post('students/save_contactinfo', 'Students::save_contactinfo');
    $routes->post('students/save_generalinfo', 'Students::save_generalinfo');
    $routes->post('students/save_attachment', 'Students::save_attachment');
    $routes->post('students/check_parent_cnic', 'Students::check_parent_cnic');

    $routes->post('students/updateDiscounts', 'Students::updateDiscounts');
    $routes->get('students/addbulk', 'Students::addbulk');
    $routes->post('students/uploadImage', 'Students::uploadImage');
    $routes->post('students/get-sibling', 'Students::getSibling');

    $routes->post('students/get_parentinfo', 'Students::get_parentinfo');
    $routes->post('admin/fee-chalan-pay/get-studentinfo', 'Admin\FeeChalanPay::getStudentinfo');
    $routes->post('admin/fee-chalan-pay1/get-studentinfo', 'Admin\FeeChalanPay1::getStudentinfo');
     $routes->get('fee-chalan-pay1', 'FeeChalan::pay', ['as' => 'fee_chalan_pay1']);
     $routes->post('admin/fee-chalan-pay/get-chalans', 'Admin\FeeChalanPay::get_chalans');



    $routes->post('students/getParentInfo', 'Students::getParentInfo');
    $routes->post('students/update-parent-info', 'Students::updateParentInfo');

    $routes->match(['GET', 'POST'], 'students/import', 'Students::import');
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // ... your existing routes ...
    
    // Delete Fee Chalan routes
    $routes->get('delete-fee-chalan', 'DeleteFeeChalan::index');
    $routes->post('delete-fee-chalan/delete', 'DeleteFeeChalan::delete');
    $routes->post('delete-fee-chalan/delete-selected', 'DeleteFeeChalan::deleteSelected');
});


// Alternative routes (optional)
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('students/search-by-name', 'StudentsBulkParentInfo::searchByName');
    $routes->post('students/by-parent', 'StudentsBulkParentInfo::byParent');
});






$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // existing…
    $routes->get('students_bulk_make_current', 'StudentsBulkMakeCurrent::index');
  
    $routes->get('students_bulk_make_current/search-by-name', StudentsBulkMakeCurrent::class.'::searchByName', ['as' => 'students_search_by_name']);

    // ✅ FIXED: no ::class here
    $routes->match(['POST','GET'],
        'students_bulk_make_current/data',
        'StudentsBulkMakeCurrent::data',
        ['as' => 'students_bulk_make_current']
    );


    $routes->post('students_bulk_make_current/make-current', StudentsBulkMakeCurrent::class.'::makeCurrent', ['as' => 'make_current']);
    $routes->post('students_bulk_make_current/save_student_info', 'StudentsBulkMakeCurrent::saveStudentInfo', ['as' => 'students_bulk_info_save']);

    // already-added canonical routes
   
    $routes->post('students_bulk_make_current/by-parent',     'StudentsBulkMakeCurrent::byParent',     ['as' => 'students_by_parent']);

    $routes->get('students_bulk_make_current/search-by-name', 'StudentsBulkMakeCurrent::searchByName');
    // NEW: aliases so /admin/students/... also works
    $routes->get('students/search-by-name', 'StudentsBulkMakeCurrent::searchByName');
    $routes->post('students/by-parent',     'StudentsBulkMakeCurrent::byParent');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // existing…
    $routes->get('students_bulk_info', 'StudentsBulkInfo::index');
    $routes->match(['POST','GET'], 'students_bulk_info/lookup_parent_by_cnic', 'StudentsBulkInfo::lookup_parent_by_cnic', ['as' => 'lookup_parent_by_cnic']);
    $routes->post('students_bulk_info/data', 'StudentsBulkInfo::data');
    $routes->post('students_bulk_info/save_student_info', 'StudentsBulkInfo::saveStudentInfo');

    // already-added canonical routes
    $routes->get('students_bulk_info/search-by-name', 'StudentsBulkInfo::searchByName');
    $routes->post('students_bulk_info/by-parent',     'StudentsBulkInfo::byParent',     ['as' => 'students_by_parent']);

    // NEW: aliases so /admin/students/... also works
    $routes->get('students/search-by-name', 'StudentsBulkInfo::searchByName');
    $routes->post('students/by-parent',     'Students_bulk_info::byParent');
});




$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // existing…
    $routes->get('students_bulk_info_date_of_birth', 'StudentsBulkInfoDateOfBirth::index');
    
    $routes->post('students_bulk_info_date_of_birth/data', 'StudentsBulkInfoDateOfBirth::data');


    $routes->post('students_bulk_info_date_of_birth/save_student_info', 'StudentsBulkInfoDateOfBirth::saveStudentInfo');

    // already-added canonical routes
    $routes->get('students_bulk_info_date_of_birth/search-by-name', 'StudentsBulkInfoDateOfBirth::searchByName', ['as' => 'students_search_by_name']);
    $routes->post('students_bulk_info_date_of_birth/by-parent',     'StudentsBulkInfoDateOfBirth::byParent',     ['as' => 'students_by_parent']);

    // NEW: aliases so /admin/students/... also works
    $routes->get('students/search-by-name', 'StudentsBulkInfoDateOfBirth::searchByName');
    $routes->post('students/by-parent',     'StudentsBulkInfoDateOfBirth::byParent');
});




$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // existing…
    $routes->get('students_bulk_fee_info', 'StudentsBulkFeeInfo::index');
    
    $routes->match(['POST','GET'], 'students_bulk_fee_info/lookup_parent_by_cnic', 'StudentsBulkFeeInfo::lookup_parent_by_cnic', ['as' => 'lookup_parent_by_cnic']);
    $routes->post('students_bulk_fee_info/data', 'StudentsBulkFeeInfo::data');
    $routes->post('students_bulk_fee_info/save_student_info', 'StudentsBulkFeeInfo::saveStudentInfo', ['as' => 'students_bulk_fee_info_save']);

    // already-added canonical routes
    $routes->get('students_bulk_fee_info/search-by-name', 'StudentsBulkFeeInfo::searchByName', ['as' => 'students_search_by_name']);
    $routes->post('students_bulk_fee_info/by-parent',     'StudentsBulkFeeInfo::byParent',     ['as' => 'students_by_parent']);

    // NEW: aliases so /admin/students/... also works
    $routes->get('students/search-by-name', 'StudentsBulkFeeInfo::searchByName');
    $routes->post('students/by-parent',     'Students_bulk_fee_info::byParent');
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Backward-compat: accept both underscore and hyphen variants
    $routes->get('students_print', 'StudentsPrint::index');
    $routes->get('students-print', 'StudentsPrint::index');
    $routes->get('students_print/default-view', 'StudentsPrint::defaultView');
    $routes->post('students_print/save-view', 'StudentsPrint::saveView');
    $routes->post('students_print/data', 'StudentsPrint::data');
    $routes->post('students-print/data', 'StudentsPrint::data');
    $routes->get('students_print/stats', 'StudentsPrint::stats');  // <-- FIXED: Use GET or POST consistently
    $routes->post('students/get_class_fee_amounts', 'Students::get_class_fee_amounts');
    $routes->post('students_print/stats', 'StudentsPrint::stats');  // <-- FIXED: Changed from Students_print::stats to StudentsPrint::stats
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('addbulkstudents/add', 'AddBulkStudents::add');
    $routes->post('addbulkstudents/save', 'AddBulkStudents::save');

    // accept both hyphen and underscore, so it never breaks again
    $routes->post('addbulkstudents/select-student-by-class-section',  'AddBulkStudents::selectStudentByClassSection'); // hyphen
    $routes->post('addbulkstudents/drop-student', 'AddBulkStudents::dropStudent');

    $routes->post('addbulkstudents/generate-slc', 'AddBulkStudents::generateSlc');
    $routes->post('addbulkstudents/check-existing-slc', 'AddBulkStudents::checkExistingSlc');
    $routes->get('addbulkstudents/download-slc/(:num)', 'AddBulkStudents::downloadSlc/$1');

    $routes->post('addbulkstudents/search-slc', 'AddBulkStudents::searchSlc');

    $routes->get('slc/view/(:num)', 'AddBulkStudents::viewSlc/$1');

    $routes->post('addbulkstudents/update-student-info', 'AddBulkStudents::updateStudentInfo');
    $routes->post('addbulkstudents/get-edit-form', 'AddBulkStudents::getEditForm');

    $routes->get('addbulkstudents/edit', 'AddBulkStudents::edit');
    $routes->post('addbulkstudents/get-school-settings', 'AddBulkStudents::getSchoolSettings');
    $routes->post('addbulkstudents/get-student-details', 'AddBulkStudents::getStudentDetails');
    $routes->post('addbulkstudents/select-student-by-class_section',  'AddBulkStudents::selectStudentByClassSection'); // underscore
    
    // ========== ADD THIS NEW ROUTE ==========
    $routes->post('addbulkstudents/update-student-name', 'AddBulkStudents::updateStudentName');
});


// Activity Report Routes
$routes->group('admin/activity-report', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('teacher-report', 'ActivityReport::teacherReport');
    $routes->get('principal-report', 'ActivityReport::principalReport');
    $routes->post('add-media-link', 'ActivityReport::addActivityMediaLink');
    $routes->post('submit-review', 'ActivityReport::submitReview');
    $routes->get('get-activity-details', 'ActivityReport::getActivityDetails');
});

$routes->get('admin/debug-teacher-activities', 'Admin\ActivityReport::debugTeacherActivities');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_bulk_cnic', 'Students_bulk_cnic::index');
    $routes->post('students_bulk_cnic/data', 'Students_bulk_cnic::data');
    $routes->post('students_bulk_cnic/update-parent-info', 'Students_bulk_cnic::updateParentInfo');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('studentsbulkcsv', 'Studentsbulkcsv::index');
    $routes->post('studentsbulkcsv/data', 'Studentsbulkcsv::data');
    
      $routes->post('studentsbulkcsv/import', 'StudentsBulkCSV::import'); // ✅ exact match
     $routes->get('studentsbulkcsv/addbulk', 'StudentsBulkCSV::addbulk');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) { 
    $routes->get('students_bulk_contacts', 'Students_bulk_contacts::index');
    $routes->post('students_bulk_contacts/data', 'Students_bulk_contacts::data');
    $routes->post('students_bulk_contacts/savestudentcontacts', 'Students_bulk_contacts::saveStudentContacts');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    // Page
    $routes->get('student_class', 'StudentClass::index');

    // Existing (underscore)
    $routes->post('student_class/data',               'StudentClass::data');
    $routes->get('student_class/add',                 'StudentClass::add');
    $routes->get('student_class/edit',                'StudentClass::edit');
    $routes->post('student_class/save',               'StudentClass::save');
    $routes->post('student_class/getStdCurrentClass', 'StudentClass::getStdCurrentClass');

    // New APIs (underscore)
    $routes->post('student_class/fetch-students', 'StudentClass::fetchStudents');
    $routes->post('student_class/move',           'StudentClass::move');
    $routes->post('student_class/move-bulk',      'StudentClass::moveBulk');

    // New APIs (dashed aliases) — to match your JS calls
    $routes->post('student-class/fetch-students', 'StudentClass::fetchStudents');
    $routes->post('student-class/move',           'StudentClass::move');
    $routes->post('student-class/move-bulk',      'StudentClass::moveBulk');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('student_data_verification_form', 'StudentDataVerificationForm::index');
    $routes->post('student_data_verification_form/data', 'StudentDataVerificationForm::data');
    $routes->get('student_data_verification_form/student_fee_verification', 'StudentDataVerificationForm::student_fee_verification');
    $routes->post('student_data_verification_form/data2', 'StudentDataVerificationForm::data2');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('users', 'Users::index');
    $routes->post('users/data', 'Users::data');
    $routes->get('users/add', 'Users::add');
    $routes->get('users/edit/(:num)', 'Users::edit/$1');
    $routes->post('users/save', 'Users::save'); // Add this if you're saving from the add/edit form
    $routes->get('users/get-teacher-subjects/(:num)', 'Users::getTeacherSubjects/$1');
$routes->post('users/assign-subject', 'Users::assignSubject');

$routes->get('users/get-teacher-classes/(:num)', 'Users::getTeacherClasses/$1');
$routes->post('users/assign-class-teacher', 'Users::assignClassTeacher');


    $routes->get('users/getEmployeeImage/(:any)', 'Users::getEmployeeImage/$1', ['filter' => false]);


    $routes->get('users/delete/(:num)', 'Users::delete/$1');
    $routes->get('users/edit_password', 'Users::edit_password');
    $routes->post('users/edit_password', 'Users::edit_password');
    $routes->get('users/set_perms', 'Users::set_perms');
    $routes->post('users/set_perms', 'Users::set_perms');
    $routes->post('users/perm_data', 'Users::perm_data');
});
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Teacher Subjects routes
    $routes->get('teacher_subjects', 'TeacherSubjects::index');
    $routes->get('teacher_subjects/getData', 'TeacherSubjects::getData');
    $routes->get('teacher_subjects/getSectionTeachers', 'TeacherSubjects::getSectionTeachers'); // ADD THIS
    $routes->post('teacher_subjects/saveAll', 'TeacherSubjects::saveAll'); // ADD THIS
    $routes->post('teacher_subjects/data', 'TeacherSubjects::data');
    $routes->get('teacher_subjects/add', 'TeacherSubjects::add');
    $routes->get('teacher_subjects/edit/(:num)', 'TeacherSubjects::edit/$1');
    $routes->post('teacher_subjects/save', 'TeacherSubjects::save');
    $routes->get('teacher_subjects/delete/(:num)', 'TeacherSubjects::delete/$1');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('teacher_section', 'TeacherSection::index');
    $routes->post('teacher_section/data', 'TeacherSection::data');
    $routes->get('teacher_section/add', 'TeacherSection::add');
    $routes->get('teacher_section/edit/(:num)', 'TeacherSection::edit/$1');
    $routes->post('teacher_section/save', 'TeacherSection::save');
    $routes->post('teacher_section/selectteachersection', 'TeacherSection::selectteachersection');
    $routes->get('teacher_section/delete/(:num)', 'TeacherSection::delete/$1');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('emp_timing', 'EmpTiming::index');
    $routes->post('emp_timing/data', 'EmpTiming::data');
    $routes->get('emp_timing/add', 'EmpTiming::add');
    $routes->get('emp_timing/edit', 'EmpTiming::edit'); // optional: use segment for ID if needed
    $routes->post('emp_timing/save', 'EmpTiming::save');
    $routes->get('emp_timing/delete', 'EmpTiming::delete'); // optional: use segment for ID
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('fee_type', 'FeeType::index');
    $routes->get('fee_type/add', 'FeeType::add');
    $routes->get('fee_type/edit', 'FeeType::edit');
    $routes->post('fee_type/save', 'FeeType::save');
    $routes->get('fee_type/delete', 'FeeType::delete');
    $routes->post('fee_type/data', 'FeeType::data');
     $routes->post('fee_type/data2', 'FeeType::data2');
     
     $routes->post('fee_type/set-monthly-fee', 'FeeType::setMonthlyFee');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('school_wizard', 'School_Wizard::index');
    $routes->post('school_wizard/saveWizardData', 'School_Wizard::saveWizardData');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('schoolsetup', 'School_Wizard::index');
    $routes->post('schoolsetup/saveStep1Class', 'School_Wizard::saveStep1Class');
    $routes->post('schoolsetup/saveStep2Section', 'School_Wizard::saveStep2Section');
    $routes->post('schoolsetup/saveWizardData', 'School_Wizard::saveWizardData'); // Final Submit
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('fee_amount', 'FeeAmount::index');               // Loads fee_amount view
    $routes->get('fee_amount/add', 'FeeAmount::add');             // Loads add form
    $routes->get('fee_amount/edit', 'FeeAmount::edit');           // Loads edit form
    $routes->post('fee_amount/save', 'FeeAmount::save');          // Form submission (save)
    $routes->post('fee_amount/data', 'FeeAmount::data');          // AJAX request for fee structure table
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // …your other routes…

    // Main URL you’re calling
    $routes->get('fee-chalan-pay1', 'FeeChalan::pay1', ['as' => 'fee_chalan_pay1']);

    // Optional spellings/fallbacks (uncomment if you might hit these)
    // $routes->get('fee-challan-pay1', 'FeeChalan::pay1');      // “challan” spelling
    // $routes->get('fee-chalan/pay1',  'FeeChalan::pay1');      // slash variant
    // If your class is actually FeeChallan (double “l”), use:
    // $routes->get('fee-chalan-pay1', 'FeeChallan::pay1');
});



// Fee Chalan Routes
// Group all admin routes 

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    
    $routes->get('fee-chalan', 'FeeChalan::index');
    
    // New unified chalan generator (GET = browser / links; POST = AJAX from student profile, etc.)
    $routes->get('fee-chalan/generate', 'FeeChalan::generate');
    $routes->post('fee-chalan/generate', 'FeeChalan::generate');
    
    // AJAX search endpoints
    $routes->post('fee-chalan/search-students', 'FeeChalan::searchStudents');
    $routes->post('fee-chalan/search-families', 'FeeChalan::searchFamilies');
    $routes->get('fee-chalan/get-sections', 'FeeChalan::getSections');
    $routes->post('fee-chalan/get-edit-form', 'FeeChalan::getEditForm');
$routes->post('fee-chalan/save-edit', 'FeeChalan::saveEdit');

     $routes->get('fee-chalan/get-sections-by-class', 'FeeChalan::getSectionsByClass');
      $routes->post('fee-chalan/bulk_chalan_generation', 'FeeChalan::bulkChalanGeneration');
   //  $routes->get('fee-chalan/bulk_chalan_stream', 'FeeChalan::bulkChalanStream'); 
     $routes->get('fee-chalan/bulk_chalan_stream', 'FeeChalan::bulk_chalan_stream');
       $routes->get('fee-chalan/add', 'FeeChalan::add');
       $routes->post('fee-chalan/delete', 'FeeChalan::delete');
$routes->post('fee-chalan/delete-all-today', 'FeeChalan::deleteAllToday');
    // Keep existing routes for backward compatibility
    $routes->get('fee-chalan/pdf', 'FeeChalan::threeCopyPdf');
    $routes->get('fee-chalan/thermal-copy', 'FeeChalan::thermalCopy');
    $routes->get('fee-chalan/single-copy', 'FeeChalan::singleCopy');
    $routes->get('fee-chalan/without-discount', 'FeeChalan::withoutDiscount');
    $routes->get('fee-chalan/familywise', 'FeeChalan::familywise');
    $routes->get('fee-chalan/familywise/single-copy', 'FeeChalan::familywiseSingleCopy');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    
    $routes->get('print-fee-chalan', 'PrintFeeChalan::index');
    
     $routes->post('print-fee-chalan/data',           'PrintFeeChalan::data',             ['as' => 'print_fee_chalan_data']); // ✅ fixed path
    $routes->get('print-fee-chalan/add', 'FeeChalan::add');
    $routes->get('print-fee-chalan/thermal-copy', 'PrintFeeChalan::thermalCopy');
    $routes->get('print-fee-chalan/single-copy', 'PrintFeeChalan::singleCopy');
    $routes->get('print-fee-chalan/pdf', 'PrintFeeChalan::threeCopyPdf');
    $routes->get('print-fee-chalan/without-discount', 'PrintFeeChalan::withoutDiscount');
    $routes->post('print-fee-chalan/set-default-template', 'PrintFeeChalan::setDefaultTemplate');
    $routes->get('print-fee-chalan/familywise', 'PrintFeeChalan::familywise');
    $routes->get('print-fee-chalan/familywise/single-copy', 'PrintFeeChalan::familywiseSingleCopy');
    $routes->get('print-fee-chalan/hostel', 'PrintFeeChalan::hostel');
    $routes->get('print-fee-chalan/with-header', 'PrintFeeChalan::withHeader');
});





$routes->post('admin/fee-chalan-pay/getStudentCardAjax', 'Admin\FeeChalanPay::getStudentCardAjax');
$routes->post('admin/fee-chalan-pay1/getStudentCardAjax', 'Admin\FeeChalanPay1::getStudentCardAjax');
$routes->get('admin/fee-chalan-pay', 'Admin\FeeChalanPay::index');
$routes->get('admin/fee-chalan-pay1', 'Admin\FeeChalanPay1::index');
$routes->post('admin/fee-chalan-pay/get-studentinfo', 'Admin\FeeChalanPay::get_studentinfo');
$routes->post('admin/fee-chalan-pay1/get-studentinfo', 'Admin\FeeChalanPay1::get_studentinfo');


$routes->post('admin/fee-chalan-pay/get-chalans', 'Admin\FeeChalanPay::get_chalans');
$routes->post('admin/fee-chalan-pay1/get-chalans', 'Admin\FeeChalanPay1::get_chalans');

$routes->post('admin/fee-chalan-pay/generateStudentFeeCard', 'Admin\FeeChalanPay::generate-student-fee-card');
$routes->post('admin/fee-chalan-pay1/generateStudentFeeCard', 'Admin\FeeChalanPay1::generate-student-fee-card');
// FeeChalanPay Controller Routes


$routes->post('admin/fee-chalan-pay/markFeeAsPaid', 'Admin\FeeChalanPay::markFeeAsPaid');
$routes->post('admin/fee-chalan-pay/mark-multiple-fees-as-paid', 'Admin\FeeChalanPay::markMultipleFeesAsPaid');

$routes->post('admin/fee-chalan-pay/addPartialFeeToPool', 'Admin\FeeChalanPay::addPartialFeeToPool');
$routes->post('admin/fee-chalan-pay/get-unpaid-fees', 'Admin\FeeChalanPay::getUnpaidFees');
$routes->post('admin/fee-chalan-pay/get-family-unpaid-fees', 'Admin\FeeChalanPay::getFamilyUnpaidFees');
$routes->post('admin/fee-chalan-pay/get-family-fee-history', 'Admin\FeeChalanPay::getFamilyFeeHistory');
$routes->post('admin/fee-chalan-pay/get-parent-fee-summary', 'Admin\FeeChalanPay::getParentFeeSummary');
$routes->post('admin/fee-chalan-pay/get-monthly-paid-fees', 'Admin\FeeChalanPay::getMonthlyPaidFees');
$routes->post('admin/fee-chalan-pay/make-unpaid', 'Admin\FeeChalanPay::makeUnpaid');

$routes->post('admin/fee-chalan-pay/getAdvanceFeeStudentsAjax', 'Admin\FeeChalanPay::getAdvanceFeeStudentsAjax');
$routes->post('admin/fee-chalan-pay/saveAdvanceFee', 'Admin\FeeChalanPay::saveAdvanceFee');

$routes->post('admin/fee-chalan-pay/markSingleFeeAsPaid', 'Admin\FeeChalanPay::markSingleFeeAsPaid');
$routes->group('admin/fee-chalan-pay', ['namespace' => 'App\Controllers\Admin'], function($routes) {       
    $routes->post('data', 'FeeChalanPay::data'); // DataTable listing

    $routes->get('add', 'FeeChalanPay::add'); // Add form
    $routes->get('edit/(:num)', 'FeeChalanPay::edit/$1'); // Edit form
    $routes->post('get-students-list', 'FeeChalanPay::get_students_list'); // Get student fee view (HTML)
    $routes->post('adv-fee', 'FeeChalanPay::advFee'); // Advance fee payment
    $routes->post('pay-fee-all', 'FeeChalanPay::payFeeAll'); // Pay all unpaid fee for a parent
    $routes->post('update-paid-fee', 'FeeChalanPay::updatePaidFee'); // Mark fee as unpaid again
    $routes->post('send-sms', 'FeeChalanPay::sendSMS'); // Send SMS on fee payment
    $routes->post('updateStudentDiscount', 'FeeChalanPay::updateStudentDiscount');
    $routes->post('get-parent-info', 'FeeChalanPay::get_parentinfo'); // Select2 parent info
});


$routes->group('admin/fee-chalan-pay1', ['namespace' => 'App\Controllers\Admin'], function($routes) {   
  
    $routes->post('data', 'FeeChalanPay1::data'); // DataTable listing
    $routes->get('add', 'FeeChalanPay1::add'); // Add form
    $routes->get('edit/(:num)', 'FeeChalanPay::edit/$1'); // Edit form
    $routes->post('get-students-list', 'FeeChalanPay::get_students_list'); // Get student fee view (HTML)
    $routes->post('adv-fee', 'FeeChalanPay::advFee'); // Advance fee payment
    $routes->post('pay-fee-all', 'FeeChalanPay::payFeeAll'); // Pay all unpaid fee for a parent
    $routes->post('update-paid-fee', 'FeeChalanPay::updatePaidFee'); // Mark fee as unpaid again
    $routes->post('send-sms', 'FeeChalanPay::sendSMS'); // Send SMS on fee payment
    $routes->post('updateStudentDiscount', 'FeeChalanPay::updateStudentDiscount');
    $routes->post('get-parent-info', 'FeeChalanPay::get_parentinfo'); // Select2 parent info
});


$routes->group('admin/fee-chalan-pay2', ['namespace' => 'App\Controllers\Admin'], function($routes) {   
    
    $routes->get('/', 'FeeChalanPay2::index');
    $routes->post('data', 'FeeChalanPay2::data'); // DataTable listing
    $routes->get('add', 'FeeChalanPay2::add'); // Add form
    $routes->get('edit/(:num)', 'FeeChalanPay2::edit/$1'); // Edit form
    $routes->post('get-students-list', 'FeeChalanPay2::get_students_list'); // Get student fee view (HTML)
    $routes->post('adv-fee', 'FeeChalanPay2::advFee'); // Advance fee payment
    $routes->post('pay-fee-all', 'FeeChalanPay2::payFeeAll'); // Pay all unpaid fee for a parent
    $routes->post('update-paid-fee', 'FeeChalanPay2::updatePaidFee'); // Mark fee as unpaid again
    $routes->post('send-sms', 'FeeChalanPay2::sendSMS'); // Send SMS on fee payment
    $routes->post('get-student-info', 'FeeChalanPay2::get_studentinfo'); // Select2 student info
    $routes->post('get-parent-info', 'FeeChalanPay2::get_parentinfo'); // Select2 parent info
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('fee-chalan-balance', 'FeeChalanBalance::index');
    $routes->post('fee-chalan-balance/get-total-fee', 'FeeChalanBalance::getTotalfee');
    $routes->post('fee-chalan-balance/get-total-fee-by-month', 'FeeChalanBalance::getTotalfeebymonth');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('exam', 'Exam::index');
    $routes->post('exam/data', 'Exam::data');
    $routes->get('exam/add', 'Exam::add');
    $routes->get('exam/edit', 'Exam::edit'); // expects ?id= in query
    $routes->post('exam/save', 'Exam::save');
    $routes->post('exam/save-edit', 'Exam::save_edit');
    $routes->post('exam/getTermDateRange', 'Exam::getTermDateRange', ['as' => 'term_range']);
    $routes->post('exam/save_edit', 'Exam::save_edit');  
    $routes->post('exam/getDateRange', 'Exam::getDateRange', ['as' => 'exam_getDateRange']);
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->post('datesheet/fetchsummary', 'Datesheet::fetchsummary');
    // you likely already have:
    // $routes->post('datesheet/fetchgrid', 'Datesheet::fetchgrid');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    // Add Syllabus main page
    $routes->get('datesheet/add-syllabus', 'Datesheet::addSyllabus');

    // Load subjects + existing syllabus for a class section (AJAX)
    
     $routes->post('datesheet/fetch-syllabus-grid', 'Datesheet::fetchSyllabusGrid');

    // Save syllabus (single row) (AJAX)
    $routes->post('datesheet/saveSyllabus', 'Datesheet::saveSyllabus');

    // Save syllabus (bulk) (AJAX)
    $routes->post('datesheet/saveSyllabusBulk', 'Datesheet::saveSyllabusBulk');
     $routes->post('datesheet/loadTlp', 'Datesheet::loadTlp');
});

$routes->group('admin/datesheet', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('/', 'Datesheet::index');
    $routes->get('without-syllabus', 'Datesheet::datesheet_without_syllabus');
    $routes->get('edit', 'Datesheet::edit');
    $routes->get('add', 'Datesheet::add');
    $routes->get('addSyllabus', 'Datesheet::addSyllabus'); // stub
    $routes->post('save', 'Datesheet::save');
    $routes->post('select-subjects', 'Datesheet::selectSubjects');
    $routes->post('data', 'Datesheet::getData'); // <-- Added this line
    $routes->post('fetchgrid', 'Datesheet::fetchgrid'); // <-- Added this line
    $routes->post('savegrid', 'Datesheet::savegrid'); // <-- Added this line
    $routes->post('save-single', 'Datesheet::saveSingle');   
    $routes->post('save-instructions', 'Datesheet::saveInstructions'); 

$routes->get('debug-exam-status', 'Datesheet::debugExamStatus');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('datesheet_without_syllabus', 'DatesheetWithoutSyllabus::index');
    $routes->get('datesheet_without_syllabus/data', 'DatesheetWithoutSyllabus::data');
    $routes->get('datesheet_without_syllabus/add', 'DatesheetWithoutSyllabus::add');
    $routes->get('datesheet_without_syllabus/edit', 'DatesheetWithoutSyllabus::edit');
    $routes->post('datesheet_without_syllabus/save', 'DatesheetWithoutSyllabus::save');
    $routes->post('datesheet_without_syllabus/select-subjects', 'DatesheetWithoutSyllabus::selectSubjects');
});


// Admin - Students Results
$routes->group('admin/students-results', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('/', 'StudentsResults::index');
    $routes->post('data', 'StudentsResults::data');
    $routes->get('add', 'StudentsResults::add');
    $routes->get('edit/(:num)', 'StudentsResults::edit/$1');
    $routes->post('save', 'StudentsResults::save');
    $routes->post('get-students', 'StudentsResults::get_students');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    // keep this for your existing (PascalCase) link:
    $routes->get('ClasswiseMonthlyStrengthReport', 'ClasswiseMonthlyStrengthReport::index');

    // preferred, human-friendly path + named routes:
    $routes->get('classwise-monthly-strength-report', 'ClasswiseMonthlyStrengthReport::index', ['as' => 'admin.strength.index']);
    $routes->post('classwise-monthly-strength-report/data', 'ClasswiseMonthlyStrengthReport::data', ['as' => 'admin.strength.data']);
    $routes->get('classwise-monthly-strength-report/print', 'ClasswiseMonthlyStrengthReport::print', ['as' => 'admin.strength.print']);
});



// Admin group
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // Preferred kebab-case URLs
    $routes->get('datesheet-report',       'DatesheetReport::index', ['as' => 'datesheet_index']);
    $routes->get('datesheet-report/add',   'DatesheetReport::add',   ['as' => 'datesheet_add']);
    $routes->post('datesheet-report/save', 'DatesheetReport::save',  ['as' => 'datesheet_save']);
    $routes->post('datesheet-report/data', 'DatesheetReport::data',  ['as' => 'datesheet_data']);

    // Legacy underscore aliases (so /admin/datesheet_report/add keeps working)
    $routes->get('datesheet_report',       'DatesheetReport::index');
    $routes->get('datesheet_report/add',   'DatesheetReport::add');
    $routes->post('datesheet_report/save', 'DatesheetReport::save');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students-results-list', 'StudentsResultsList::index');
    $routes->post('students-results-list/get-students', 'StudentsResultsList::get_students');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students-results-list', 'StudentsResultsList::index');
    $routes->post('students-results-list/get-students', 'StudentsResultsList::get_students');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_results_card', 'StudentsResultsCard::index');
    $routes->post('students-results-card/data', 'StudentsResultsCard::data');
});

$routes->get('students-results-card/search-students', 'StudentsResultsCard::searchStudents');

$routes->group('admin/employees_attendance', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('/', 'EmployeesAttendance::index');
    $routes->post('data', 'EmployeesAttendance::data');
    $routes->get('add', 'EmployeesAttendance::add');
    $routes->get('edit', 'EmployeesAttendance::edit'); // expects ?id=123
    $routes->post('save', 'EmployeesAttendance::save');
    $routes->post('get_employees', 'EmployeesAttendance::get_employees');
    $routes->get('delete', 'EmployeesAttendance::delete'); // expects ?id=123
});

$routes->group('admin/employee_leaves', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('/', 'EmployeeLeaves::index');
    $routes->post('data', 'EmployeeLeaves::data');
    $routes->post('approveleave', 'EmployeeLeaves::approveleave');
    $routes->post('rejectleave', 'EmployeeLeaves::rejectleave');
    $routes->post('save', 'EmployeeLeaves::save');
    $routes->get('add', 'EmployeeLeaves::add');
    $routes->get('edit', 'EmployeeLeaves::edit');
    $routes->get('delete', 'EmployeeLeaves::delete');
    $routes->post('get_employee', 'EmployeeLeaves::get_employee');
    $routes->post('get_employeeinfo', 'EmployeeLeaves::get_employeeinfo');
});


// Combined Admin Routes Group
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    
    // Students Attendance Routes
    $routes->get('students_attendance', 'StudentsAttendance::index');
    $routes->post('students_attendance/data', 'StudentsAttendance::data');
    $routes->get('students_attendance/add', 'StudentsAttendance::add');
    $routes->get('students_attendance/report', 'StudentsAttendance::report');
    $routes->get('students_attendance/edit', 'StudentsAttendance::edit');
    $routes->post('students_attendance/save', 'StudentsAttendance::save');
    $routes->post('students_attendance/get_students_byclass', 'StudentsAttendance::get_students_byclass');
    $routes->post('students_attendance/get_students_byabsentees', 'StudentsAttendance::get_students_byabsentees');
    $routes->get('students_attendance/delete', 'StudentsAttendance::delete');
    $routes->get('students_attendance/students_attendance_detail', 'StudentsAttendance::students_attendance_detail', ['as' => 'students_attendance_detail']);
    $routes->match(['get','post'], 'students_attendance/report_cards', 'StudentsAttendance::report_cards');
    $routes->post('students_attendance/sections_by_class', 'StudentsAttendance::sections_by_class');
    
    // Attendance Monthly Report Routes
    $routes->get('attendance-monthly-report', 'AttendanceMonthlyReport::index');
    $routes->post('attendance-monthly-report/get-students-byclass', 'AttendanceMonthlyReport::get_students_byclass');
    
    // For Students Session Report
    // REMOVE THIS LINE: return view('attendance-monthly-report/student_session_report', $data);
    // ADD the missing route:
    $routes->get('attendance-monthly-report/student-session-report', 'AttendanceMonthlyReport::studentSessionReport');
    $routes->get('attendance-monthly-report/student-wise-report', 'AttendanceMonthlyReport::studentWiseSessionReport');
    $routes->post('attendance-monthly-report/get-students-by-section', 'AttendanceMonthlyReport::getStudentsBySection');
    $routes->post('attendance-monthly-report/get-student-attendance-data', 'AttendanceMonthlyReport::getStudentAttendanceData');
    $routes->post('attendance-monthly-report/get-student-info', 'AttendanceMonthlyReport::getStudentInfo');
    $routes->post('attendance-monthly-report/get-student-details', 'AttendanceMonthlyReport::getStudentDetails');
}); // Only ONE closing brace here


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    
        $routes->match(['get', 'post'], 'fee-collection-session-wise', 'FeeCollectionSessions::index');
   

});

// Admin Group Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // Students Absentees (existing)
    $routes->get('students_absentees',                         'StudentsAbsentees::index');
    $routes->get('students_absentees/add',                     'StudentsAbsentees::add');
    $routes->get('students_absentees/edit',                    'StudentsAbsentees::edit');
    $routes->post('students_absentees/save',                   'StudentsAbsentees::save');
    $routes->post('students_absentees/data',                   'StudentsAbsentees::data');
    $routes->post('students_absentees/get_students_byclass',   'StudentsAbsentees::get_students_byclass');
    $routes->post('students_absentees/update_attendance_status','StudentsAbsentees::update_attendance_status');
    $routes->get('students_absentees/delete',                  'StudentsAbsentees::delete');

    $routes->post('students_absentees/search_students_by_name', 'StudentsAbsentees::search_students_by_name');
$routes->get('students_absentees/get_students_for_dropdown', 'StudentsAbsentees::get_students_for_dropdown');




$routes->post('students_absentees/update_attendance_status_single', 'StudentsAbsentees::update_attendance_status_single');


$routes->post('students_absentees/load_attendance_records', 'StudentsAbsentees::load_attendance_records');

$routes->post('students_absentees/save_late_attendance', 'StudentsAbsentees::save_late_attendance');

    // NEW: search-by-name + parent-based flow
    $routes->get('students_absentees/search-by-name',                 'StudentsAbsentees::searchByName', ['as' => 'absentees_search_by_name']);
    $routes->post('students_absentees/by-parent',                     'StudentsAbsentees::byParent',      ['as' => 'absentees_by_parent']);
    $routes->post('students_absentees/check_and_load_attendance_by_parent', 'StudentsAbsentees::check_and_load_attendance_by_parent');
    $routes->post('students_absentees/mark_and_show_students_by_parent',    'StudentsAbsentees::mark_and_show_students_by_parent');

    // Existing helpers
    $routes->post('students_absentees/mark_and_show_students',  'StudentsAbsentees::mark_and_show_students');
    $routes->post('students_absentees/toggle_attendance_status','StudentsAbsentees::toggle_attendance_status');
    $routes->post('students_absentees/load_existing_attendance','StudentsAbsentees::load_existing_attendance');
    $routes->post('students_absentees/check_and_load_attendance','StudentsAbsentees::check_and_load_attendance');
});




$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_latecomming',             'StudentsLateComming::index');
    $routes->post('students_latecomming/data',       'StudentsLateComming::data');
    $routes->get('students_latecomming/add',         'StudentsLateComming::add');
    $routes->post('students_latecomming/save',       'StudentsLateComming::save');
    $routes->get('students_latecomming/delete',      'StudentsLateComming::delete');
    $routes->post('students_latecomming/get_studentinfo',       'StudentsLateComming::get_studentinfo');
    $routes->post('students_latecomming/get_students_byclass',  'StudentsLateComming::get_students_byclass');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students-late-comming', 'StudentsLateComming::index');
    $routes->post('students-late-comming/data', 'StudentsLateComming::data');
    $routes->get('students-late-comming/add', 'StudentsLateComming::add');
    $routes->post('students-late-comming/save', 'StudentsLateComming::save');
    $routes->get('students-late-comming/edit', 'StudentsLateComming::edit');
    $routes->get('students-late-comming/delete', 'StudentsLateComming::delete');

    // AJAX endpoints
    $routes->post('students-late-comming/get-studentinfo', 'StudentsLateComming::get_studentinfo');
    $routes->post('students-late-comming/get-students-byclass', 'StudentsLateComming::get_students_byclass');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students-early-left', 'StudentsEarlyLeft::index');
    $routes->post('students-early-left/data', 'StudentsEarlyLeft::data');
    $routes->get('students-early-left/add', 'StudentsEarlyLeft::add');
    $routes->post('students-early-left/save', 'StudentsEarlyLeft::save');
    $routes->get('students-early-left/edit', 'StudentsEarlyLeft::edit');
    $routes->get('students-early-left/delete', 'StudentsEarlyLeft::delete');

    // AJAX/JSON Endpoints
    $routes->post('students-early-left/get-studentinfo', 'StudentsEarlyLeft::get_studentinfo');
    $routes->post('students-early-left/get-students-byclass', 'StudentsEarlyLeft::get_students_byclass');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_leaves', 'Students_leaves::index');
    $routes->post('students_leaves/data', 'Students_leaves::data');
    $routes->post('students_leaves/save', 'Students_leaves::save');
    $routes->get('students_leaves/add', 'Students_leaves::add');
    $routes->get('students_leaves/edit', 'Students_leaves::edit');
    $routes->get('students_leaves/delete', 'Students_leaves::delete');
    $routes->post('students_leaves/approveleave', 'Students_leaves::approveleave');
    $routes->post('students_leaves/rejectleave', 'Students_leaves::rejectleave');
    $routes->post('students_leaves/get_studentinfo', 'Students_leaves::get_studentinfo');
    $routes->post('students_leaves/get_students_byclass', 'Students_leaves::get_students_byclass');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('timetable/index', 'Timetable::index');
    $routes->get('timetable/add', 'Timetable::add');
    $routes->post('timetable/data', 'Timetable::data');
    
    $routes->get('timetable/edit', 'Timetable::edit');
    $routes->post('timetable/save', 'Timetable::save');
    $routes->get('timetable/delete', 'Timetable::delete');
    
    $routes->post('timetable/fetch-table', 'Timetable::fetchTable');
    $routes->post('timetable/save-slot', 'Timetable::saveSlot');
    $routes->post('timetable/get-subjects', 'Timetable::getSubjects');
    $routes->post('timetable/get-subjects-timetable', 'Timetable::getSubjectsTimetable');
    $routes->post('timetable/get-subject-constraints', 'Timetable::getSubjectConstraints');
    $routes->post('timetable/update-slot', 'Timetable::updateSlot');
    $routes->get('timetable/report', 'Timetable::report');
    $routes->post('timetable/report-data', 'Timetable::reportData');
    $routes->get('timetable/report-export', 'Timetable::reportExport');
    $routes->get('timetable/teachers', 'Timetable::viewTeacherTimetable');
$routes->get('timetable/teacher', 'Timetable::getTeacherTimetable');
$routes->get('timetable/time-table-add-new', 'Timetable::timeTableAddNew');

$routes->post('timetable/clear', 'Timetable::clear');
    
});




$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('school_timing', 'School_timing::index');
    $routes->post('school_timing/data', 'School_timing::data');
    $routes->get('school_timing/add', 'School_timing::add');
    $routes->get('school_timing/edit', 'School_timing::edit');
    $routes->post('school_timing/save', 'School_timing::save');
    $routes->get('school_timing/delete', 'School_timing::delete');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    // School Timing Type Routes
    $routes->get('school_timming_type', 'School_timming_type::index');
    $routes->post('school_timming_type/data', 'School_timming_type::data');
    $routes->get('school_timming_type/add', 'School_timming_type::add');
    $routes->get('school_timming_type/edit', 'School_timming_type::edit');
    $routes->post('school_timming_type/save', 'School_timming_type::save');
    $routes->post('school_timming_type/getDateRange', 'School_timming_type::getDateRange');
    $routes->get('school_timming_type/delete', 'School_timming_type::delete');

    // Student Fee Summary Report Routes
 $routes->get('student_fee_summary', 'ReportController::studentFeeSummary');
    $routes->get('student_fee_summary/export_excel', 'ReportController::exportStudentFeeReportExcel');

});
// In your admin routes group
// In your admin routes group


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    
    // Main dashboard
    $routes->get('recordings', 'Recordings::index');
    
    // Filter endpoints
    $routes->get('recordings/get-pending-counts', 'Recordings::getPendingCounts');
    $routes->post('recordings/get-subjects-by-section', 'Recordings::getSubjectsBySection');
    $routes->get('recordings/get-filtered-pending-audio', 'Recordings::getFilteredPendingAudio');
    $routes->get('recordings/get-filtered-pending-video', 'Recordings::getFilteredPendingVideo');
    
    // Review endpoints
    $routes->post('recordings/review-audio', 'Recordings::reviewAudio');
    $routes->post('recordings/review-video', 'Recordings::reviewVideo');
    $routes->post('recordings/bulk-review', 'Recordings::bulkReview');
    
    // Student Progress endpoints
    $routes->get('recordings/student-progress', 'Recordings::studentProgress');
    $routes->get('recordings/student-details/(:num)', 'Recordings::studentDetails/$1');
    
    // Export endpoint
    $routes->get('recordings/export', 'Recordings::export');
});

$routes->group('parent', ['namespace' => 'App\Controllers\Parent'], function($routes) {
    $routes->get('attendance', 'Attendance::index');
    $routes->post('attendance/getChildAttendance', 'Attendance::getChildAttendance');
    // Fix: Use correct parameter matching
    $routes->get('attendance/share/(:any)', 'Attendance::share/$1');
      $routes->get('attendance/view/(:any)', 'Attendance::view/$1');
       $routes->get('attendance/debugChildren/(:any)', 'Attendance::debugChildren/$1');
         $routes->get('attendance/debugOffDays/(:num)', 'Attendance::debugOffDays/$1'); 
         $routes->get('attendance/debugTimings/(:num)', 'Attendance::debugTimings/$1');
          $routes->get('recordings/student-progress', 'Recordings::studentProgress');
    $routes->get('recordings/student-details/(:num)', 'Recordings::studentDetails/$1');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Attendance Report Routes
    $routes->get('attendance-report', 'AttendanceReport::index');
    $routes->post('attendance-report/data', 'AttendanceReport::getReportData');
    $routes->post('attendance-report/export', 'AttendanceReport::exportExcel');
       $routes->post('attendance-report/getWeeklyAttendance', 'AttendanceReport::getWeeklyAttendance'); 

        $routes->post('attendance-report/generateShareToken', 'AttendanceReport::generateShareToken');
    
    // Monthly Working Days Report - NEW
    $routes->get('attendance-working-days-report', 'AttendanceReport::workingDaysReport');
    $routes->post('attendance-working-days-report/data', 'AttendanceReport::getWorkingDaysReportData');
    $routes->post('attendance-working-days-report/export', 'AttendanceReport::exportWorkingDaysReport');
});


// Top Level Planning Routes
$routes->group('admin/top_level_planning', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('add', 'TopLevelPlanning::add');
    $routes->post('save', 'TopLevelPlanning::save');
    $routes->post('getSubjectsByClass', 'TopLevelPlanning::getSubjectsByClass');
    $routes->post('getClassesBySubject', 'TopLevelPlanning::getClassesBySubject');
    $routes->post('getPlanningForm', 'TopLevelPlanning::getPlanningForm');
    $routes->get('view', 'TopLevelPlanning::view');
    $routes->post('getViewData', 'TopLevelPlanning::getViewData');
    $routes->get('printReport', 'TopLevelPlanning::printReport');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {

    $routes->get('face-attendance', 'FaceAttendance::index');
    $routes->post('face-attendance/mark', 'FaceAttendance::mark');

    $routes->get('face-management', 'FaceAttendance::management');

    $routes->get('face-management/get-students', 'FaceAttendance::getStudents');

    $routes->get('face-management/data', 'FaceAttendance::data');
    $routes->post('face-management/delete', 'FaceAttendance::delete');
    $routes->post('face-management/enroll', 'FaceAttendance::enroll');

});

// Top Level Planning Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('top_level_planning', 'Top_level_planning::index');
    

 $routes->match(['get','post'], 'top_level_planning_gradewise', 'Top_level_planning_gradewise::index');
$routes->get('top_level_planning/data', 'Top_level_planning::data');
    $routes->get('top_level_planning/add', 'Top_level_planning::add');
    $routes->get('top_level_planning/edit', 'Top_level_planning::edit');
    $routes->post('top_level_planning/save', 'Top_level_planning::save');
    $routes->post('top_level_planning/selectSubjectsforTopLevelPlanning', 'Top_level_planning::selectSubjectsforTopLevelPlanning');
    $routes->post('ajax/selectsubjectby-section', 'Ajax::selectsubjectby_section');
    $routes->post('top_level_planning/getSubjectsBySection', 'Top_level_planning::getSubjectsBySection');
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {

    // Top Level Planning Sections
    $routes->get('top_level_planning_sections', 'TopLevelPlanningSections::index');
    $routes->get('top_level_planning_sections/add', 'TopLevelPlanningSections::add', ['as' => 'top_level_planning_sections_add']);
    $routes->post('top_level_planning_sections/save', 'TopLevelPlanningSections::save', ['as' => 'top_level_planning_sections_save']);

    // (If you also have the non-“sections” controller)
    
    $routes->get('top_level_planning/add', 'TopLevelPlanning::add', ['as' => 'top_level_planning_add']);
    $routes->post('top_level_planning/save', 'TopLevelPlanning::save', ['as' => 'top_level_planning_save']);
    $routes->post(
        'top_level_planning_sections/select-term',
        'TopLevelPlanningSections::selectTermforTopLevelPlanning',
        ['as' => 'tlp_sections_select_term']
    );

});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('top_level_planning_subject',        'TopLevelPlanningSubject::index');
    $routes->get('top_level_planning_subject/add',    'TopLevelPlanningSubject::add',  ['as' => 'tlp_subject_add']);
    $routes->post('top_level_planning_subject/save',  'TopLevelPlanningSubject::save', ['as' => 'tlp_subject_save']);
    $routes->post('top_level_planning_subject/select-subjects', 'TopLevelPlanningSubject::selectSubjectsforTopLevelPlanning', ['as' => 'tlp_subject_select_subjects']);
    $routes->get('top_level_planning_subject/edit',   'TopLevelPlanningSubject::edit', ['as' => 'tlp_subject_edit']);
    $routes->get('top_level_planning_subject/delete', 'TopLevelPlanningSubject::delete', ['as' => 'tlp_subject_delete']);

     $routes->post('top_level_planning_subject/select-subjects', 'TopLevelPlanningSubject::selectSubjects');
    $routes->post('top_level_planning_subject/autosave',        'TopLevelPlanningSubject::autosave');

    // If you also use this in the page:
    $routes->post('ajax/select-exam', 'Ajax::selectExam');

    // Backward-compat for any old JS still using the long path:
    $routes->post(
        'top_level_planning_subject/select-subjects-for-top-level-planning',
        'TopLevelPlanningSubject::selectSubjectsforTopLevelPlanning'
    );


});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('scheme_of_studies_view', 'Scheme_of_studies_view::index');
    $routes->post('scheme_of_studies_view/data', 'Scheme_of_studies_view::data');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Existing routes
    $routes->get('weekly_planning_view', 'WeeklyPlanningView::index');
    $routes->post('weekly_planning_view/data', 'WeeklyPlanningView::data');
    
    // Routes for Weekly_planning controller
    $routes->get('weekly_planning', 'Weekly_planning::index');
    $routes->post('weekly_planning/data', 'Weekly_planning::data');
    $routes->get('weekly_planning/add', 'Weekly_planning::add');
    $routes->get('weekly_planning/edit', 'Weekly_planning::edit');
    $routes->post('weekly_planning/save', 'Weekly_planning::save');
    $routes->post('weekly_planning/get-weekly-planning', 'Weekly_planning::getWeeklyPlanning');
    $routes->post('weekly_planning/get-top-level-planning', 'Weekly_planning::getTopLevelPlanning');
    $routes->get('weekly_planning/delete', 'Weekly_planning::delete');
    
    // Add this route for subject selection (same pattern as top level planning)
    $routes->post('weekly_planning/getSubjectsBySection', 'Weekly_planning::getSubjectsBySection');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Weekly Planning Report Routes
    $routes->get('weekly_planning_report', 'WeeklyPlanningReport::index');
    $routes->post('weekly_planning_report/getData', 'WeeklyPlanningReport::getData');
    $routes->post('weekly_planning_report/getWeeks', 'WeeklyPlanningReport::getWeeks');
    $routes->post('weekly_planning_report/exportPdf', 'WeeklyPlanningReport::exportPdf');
    $routes->post('weekly_planning_report/exportExcel', 'WeeklyPlanningReport::exportExcel');
     $routes->get('weekly_planning_report/exportPdf', 'WeeklyPlanningReport::exportPdf');
    $routes->get('weekly_planning_report/exportExcel', 'WeeklyPlanningReport::exportExcel');
    
    // Existing routes...
});

$routes->post('admin/ajax/getclassby-section', 'Admin\Weekly_planning::getClassBySection');

$routes->post('admin/ajax/get-current-session-term', 'Admin\Weekly_planning::getCurrentSessionTerm');
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('wp_objectives', 'WpObjectives::index');
    $routes->post('wp_objectives/data', 'WpObjectives::data');
    $routes->get('wp_objectives/add', 'WpObjectives::add');
    $routes->get('wp_objectives/edit', 'WpObjectives::edit');
    $routes->post('wp_objectives/save', 'WpObjectives::save');
    $routes->get('wp_objectives/delete', 'WpObjectives::delete');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('wp-subjects-objectives', 'WpSubjectsObjectives::index');
    $routes->post('wp-subjects-objectives/data', 'WpSubjectsObjectives::data');
    $routes->post('wp-subjects-objectives/data2', 'WpSubjectsObjectives::data2');
    $routes->post('wp-subjects-objectives/update', 'WpSubjectsObjectives::updateWpSubjectObjective');
    $routes->get('wp-subjects-objectives/add', 'WpSubjectsObjectives::add');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('weekly-progress', 'WpStdWeeeklyProgress::index');
    $routes->post('weekly-progress/data', 'WpStdWeeeklyProgress::data');
    $routes->get('weekly-progress/add', 'WpStdWeeeklyProgress::add');
    $routes->get('weekly-progress/edit', 'WpStdWeeeklyProgress::edit');
    $routes->post('weekly-progress/save', 'WpStdWeeeklyProgress::save');
    $routes->post('weekly-progress/select-section-subject', 'WpStdWeeeklyProgress::selectSectionSubjectbySection');
    $routes->post('weekly-progress/get-students', 'WpStdWeeeklyProgress::get_students');
    
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('wp-results-card', 'WpResultsCard::index');
    $routes->post('wp-results-card/data', 'WpResultsCard::data');
});


$routes->group('admin/ajax', ['namespace'=>'App\Controllers\Admin'], static function($routes){
    $routes->get('user-menu-prefs',  'UserMenuPrefs::get');
    $routes->post('user-menu-prefs', 'UserMenuPrefs::save');
});
// app/Config/Routes.php


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // ---- Classdiary (CRUD + AJAX) ----
    $routes->get('classdiary', 'Classdiary::index', ['as' => 'classdiary_index']);
    $routes->get('classdiary/add','Classdiary::add', ['as' => 'classdiary_add']);
    $routes->post('classdiary/data','Classdiary::data', ['as' => 'classdiary_data']);
    $routes->get('classdiary/edit','Classdiary::edit', ['as' => 'classdiary_edit']);
    $routes->post('classdiary/save','Classdiary::save', ['as' => 'classdiary_save']);
      $routes->get('bagpack', 'Classdiary::bagpack', ['as'=>'bagpack']);
$routes->post('classdiary/get-bagpack', 'Classdiary::get_bagpack');
$routes->post('classdiary/get-quizzes-by-subject', 'Classdiary::getQuizzesBySubject');




    $routes->post(
        'classdiary/select-section-subject-by-section',
        'Classdiary::selectSectionSubjectbySection',
        ['as' => 'classdiary_select_section_subject_by_section']
    );
    // AJAX endpoint used by your $.ajax() call
    $routes->post('classdiary/get-classdiary', 'Classdiary::get_classdiary', ['as' => 'classdiary_get']);

    // Optional legacy typo path (keep if needed)
    $routes->post('classdairy/get-classdiary', 'Classdiary::get_classdiary');

    // ---- View (separate controller, different path to avoid overriding) ----
    $routes->get('classdiary-view','ClassdiaryView::index', ['as' => 'classdiary_view']);
    $routes->post('classdiary-view/data','ClassdiaryView::data', ['as' => 'classdiary_view_data']);


 
    // ✅ NEW ROUTE: Get weeks for a term (AJAX)
    $routes->post('classdiary-view/getWeeks','ClassdiaryView::getWeeks', ['as' => 'classdiary_view_get_weeks']);

    
    // ✅ add aliases for the typo you’re hitting in the browser:
    $routes->get('classdairy-view','ClassdiaryView::index');
    $routes->post('classdairy-view/data','ClassdiaryView::data');
      $routes->get('classdiary-view', 'ClassdiaryView::index');
    $routes->post('classdiary-view/getWeeks', 'ClassdiaryView::getWeeks');
    $routes->post('classdiary-view/getAllWeeks', 'ClassdiaryView::getAllWeeks');
    $routes->post('classdiary-view/data', 'ClassdiaryView::data');
    $routes->post('classdiary-view/getClassDiary', 'ClassdiaryView::getClassDiary');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Existing class diary routes
    
    
    
    
    
    // Diary Analytics routes (inside admin group)
    $routes->get('diary-analytics', 'DiaryAnalytics::index');
    $routes->get('diary-analytics/index', 'DiaryAnalytics::index');
    $routes->post('diary-analytics/getAnalytics', 'DiaryAnalytics::getAnalytics');
    $routes->post('diary-analytics/export', 'DiaryAnalytics::export');
});
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('student-notices', 'StudentNotices::index');
    $routes->post('student-notices/data', 'StudentNotices::data');
    $routes->get('student-notices/add', 'StudentNoticesStudentNotices::add');
    $routes->get('student-notices/edit', 'StudentNotices::edit'); // Uses ?id=123
    $routes->post('student-notices/save', 'StudentNotices::save');
    $routes->post('student-notices/get-noticeinfo', 'StudentNotices::get_noticeinfo');
    $routes->get('student-notices/delete', 'StudentNotices::delete'); // Uses ?id=123
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students-complaints', 'StudentsComplaints::index');
    $routes->post('students-complaints/data', 'StudentsComplaints::data');
    $routes->get('students-complaints/add', 'StudentsComplaints::add');
    $routes->get('students-complaints/edit', 'StudentsComplaints::edit');
    $routes->post('students-complaints/save', 'StudentsComplaints::save');
    $routes->post('students-complaints/get-students-byclass', 'StudentsComplaints::get_students_byclass');
    $routes->get('students-complaints/delete', 'StudentsComplaints::delete');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    // Attendance Monthly Report
    $routes->get('attendance-monthly-report', 'AttendanceMonthlyReport::index');
    $routes->post('attendance-monthly-report/get-students-byclass', 'AttendanceMonthlyReport::get_students_byclass');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    // Campus Management
    $routes->get('campus', 'Campus::index');
    $routes->post('campus/data', 'Campus::data');
    $routes->get('campus/add', 'Campus::add');
    $routes->get('campus/edit', 'Campus::edit');
    $routes->post('campus/save', 'Campus::save');
    $routes->post('campus/get-packages', 'Campus::get_packages');
    $routes->post('campus/calculate-campus-bill', 'Campus::calculateCampusBill');
    $routes->get('campus/delete', 'Campus::delete');
    
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('profile-campus', 'ProfileCampus::index');
     $routes->post('profile_campus/generate_qr', 'ProfileCampus::generate_qr');
    $routes->post('profile_campus/save', 'ProfileCampus::save', ['as' => 'profile_campus_save']);
    $routes->post(
        'profile_campus/update-password',
        'ProfileCampus::update_password',
        ['as' => 'profile_campus_update_password']
    );
});

// Admin group
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // Preferred slugs
    $routes->get('profit-loss-report', 'ProfitLossReport::index', ['as' => 'profit_loss_index']);
    $routes->post('profit-loss-report/daily', 'ProfitLossReport::getDailyCollection', ['as' => 'profit_loss_daily']);

    // Legacy/underscore aliases (so /admin/profit_loss_report keeps working)
    $routes->get('profit_loss_report', 'ProfitLossReport::index');
    $routes->post('profit_loss_report/get_daily_collection', 'ProfitLossReport::getDailyCollection');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('profile-system', 'ProfileSystem::index');
    $routes->post('profile-system/save', 'ProfileSystem::save');
    $routes->post('profile-system/update-reg-text', 'ProfileSystem::updateRegText');
    $routes->post('profile-system/update-password', 'ProfileSystem::update_password');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('admission-enquiry', 'AdmissionEnquiry::index');
    $routes->post('admission-enquiry/data', 'AdmissionEnquiry::data');
    $routes->get('admission-enquiry/add', 'AdmissionEnquiry::add');
    $routes->get('admission-enquiry/edit', 'AdmissionEnquiry::edit');
    $routes->post('admission-enquiry/save', 'AdmissionEnquiry::save');
    $routes->get('admission-enquiry/delete', 'AdmissionEnquiry::delete');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {    
    $routes->get('message-templates', 'MessageTemplates::index');
    $routes->post('message-templates/save', 'MessageTemplates::save');

});

$routes->group('admin/messages', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('/', 'Messages::index');
    $routes->post('data', 'Messages::data');
    $routes->get('add', 'Messages::add');
    $routes->post('save', 'Messages::save');
    $routes->get('delete', 'Messages::delete');
});

$routes->group('admin/bulksms', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('/', 'BulkSms::index');
    $routes->post('data', 'BulkSms::data');
    $routes->get('add', 'BulkSms::add');
    $routes->get('edit', 'BulkSms::edit');
    $routes->post('save', 'BulkSms::save');
    $routes->get('delete', 'BulkSms::delete');
});

$routes->group('admin/defaulter-message', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'DefaulterMessage::index');                 // Load main page
    $routes->post('data', 'DefaulterMessage::data');              // Fetch filtered defaulter data
    $routes->post('save', 'DefaulterMessage::save');              // Save student messages
    $routes->get('parent_sms', 'DefaulterMessage::parent_sms');   // Load parent SMS view
    $routes->post('saveparent', 'DefaulterMessage::saveparent');  // Save messages for parents
    $routes->get('delete', 'DefaulterMessage::delete');           // Delete class entry (if used)
});

$routes->group('admin/result-message', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'ResultMessage::index');     // Load main view
    $routes->post('data', 'ResultMessage::data');  // Load message form
    $routes->post('save', 'ResultMessage::save');  // Save SMS messages
});

$routes->group('admin/students-list', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'StudentsList::index');                // Load students contact list view
    $routes->post('data', 'StudentsList::data');            // Load DataTables student rows
    $routes->post('get-parentinfo', 'StudentsList::get_parentinfo'); // AJAX: parent autocomplete
    $routes->post('get-studentinfo', 'StudentsList::get_studentinfo'); // AJAX: student autocomplete
});

$routes->group('admin/students-w-result-list', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'StudentsWithResultList::index');                     // Load students result list view
    $routes->post('data', 'StudentsWithResultList::data');                 // Load DataTables student rows
    $routes->post('get-parentinfo', 'StudentsWithResultList::getParentInfo'); // AJAX: parent autocomplete
    $routes->post('get-studentinfo', 'StudentsWithResultList::getStudentInfo'); // AJAX: student autocomplete
});

$routes->group('admin/family-chalan-whatsapp', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'FamilyChalanWhatsapp::index');              // Loads main view
    $routes->post('data', 'FamilyChalanWhatsapp::data');           // DataTables response
    $routes->post('get-parentinfo', 'FamilyChalanWhatsapp::get_parentinfo'); // Parent autocomplete
    $routes->post('get-studentinfo', 'FamilyChalanWhatsapp::get_studentinfo'); // Student autocomplete
});

$routes->group('Frontend', ['namespace' => 'App\Controllers\Frontend'], static function ($routes) {
    $routes->get('family_diary_whatsapp', 'FamilyDiaryWhatsapp::index');
    $routes->post('family_diary_whatsapp/data', 'FamilyDiaryWhatsapp::data');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('profile-student', 'ProfileStudent::index');
    $routes->post('profile-student/data', 'ProfileStudent::data');
    $routes->post('profile-student/save', 'ProfileStudent::save');
    $routes->post('profile-student/update-password', 'ProfileStudent::updatePassword');
    $routes->post('profile-student/student-fee-data', 'ProfileStudent::singleStudentFeedata');
    $routes->post('profile-student/student-attendance-data', 'ProfileStudent::singleStudentAttendancedata');
    $routes->post('profile-student/student-health-data', 'ProfileStudent::studentHealthData');
    $routes->post('profile-student/student-result-data', 'ProfileStudent::studentResultData');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('fee-chalan-single', 'FeeChalanSingle::index');
    $routes->get('fee-chalan-single/add', 'FeeChalanSingle::add');
    $routes->post('fee-chalan-single/save', 'FeeChalanSingle::save');
    $routes->get('fee-chalan-single/download', 'FeeChalanSingle::download');
    $routes->match(['get', 'post'], 'fee-chalan-single/data', 'FeeChalanSingle::data');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('leaving-certificate', 'LeavingCertificate::index');
    $routes->match(['get', 'post'], 'leaving-certificate/data', 'LeavingCertificate::data');
    $routes->get('leaving-certificate/add', 'LeavingCertificate::add');
    $routes->get('leaving-certificate/edit', 'LeavingCertificate::edit');
    $routes->post('leaving-certificate/save', 'LeavingCertificate::save');
    $routes->get('leaving-certificate/download', 'LeavingCertificate::download');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('fee-chalan-sibling', 'FeeChalanSibling::index');
    $routes->match(['get', 'post'], 'fee-chalan-sibling/data', 'FeeChalanSibling::data');
});

// File: app/Config/Routes.php or inside a Route group for 'admin'

$routes->group('admin/ajax', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('index', 'Ajax::index');
    $routes->post('setboolattribute', 'Ajax::setboolattribute');

    // POST Methods
    // $routes->post('check_parent_cnic', 'Ajax::check_parent_cnic');
    $routes->post('select-class-fee', 'Ajax::selectClassFee');
    $routes->post('select-class-fee', 'Ajax::selectClassFee');
    $routes->post('updatestudentstatus', 'Ajax::updatestudentstatus');
    $routes->post('change-campus', 'Ajax::changeCampus');
    $routes->post('select-session', 'Ajax::selectSession');
    $routes->post('setboolattributeteachers', 'Ajax::setboolattributeteachers');
    $routes->post('setboolattribute2', 'Ajax::setboolattribute2');
    $routes->post('setboolattributeexam', 'Ajax::setboolattributeexam');
    $routes->post('setboolattributetest', 'Ajax::setboolattributetest');
    $routes->post('setboolattributeSchoolType', 'Ajax::setboolattributeSchoolType');
    $routes->post('setfeetypestatus', 'Ajax::setfeetypestatus');
    $routes->post('setboolattribute', 'Ajax::setboolattribute');
    $routes->post('setboolIndexable', 'Ajax::setboolIndexable');
    $routes->post('setboolattributeIsTrial', 'Ajax::setboolattributeIsTrial');
    $routes->post('setboolattributeFee', 'Ajax::setboolattributeFee');
    $routes->post('setboolattributenotice', 'Ajax::setboolattributenotice');
    $routes->post('setfieldvalue', 'Ajax::setfieldvalue');
    $routes->post('setunique', 'Ajax::setunique');
    $routes->post('set_sortid', 'Ajax::set_sortid');
    $routes->post('selectsectionby-class', 'Ajax::selectsectionbyClass');
    $routes->post('select-exam', 'Ajax::selectExam');
    $routes->post('selectsubjectby-section', 'Ajax::selectsubjectbySection');
    $routes->post('select-section-subjectby-section', 'Ajax::selectSectionSubjectbySection');
    $routes->post('selecttermby-session', 'Ajax::selecttermbySession');
    $routes->post('selectcategoriesbysubject', 'Ajax::selectcategoriesbysubject');
    $routes->post('selecttopicbycategories', 'Ajax::selecttopicbycategories');
    $routes->post('select-skillsby-topic', 'Ajax::selectSkillsbyTopic');
    $routes->post('selectmul-terms-weeks', 'Ajax::selectmulTermsWeeks');
    $routes->post('select-term-weeks', 'Ajax::selectTermWeeks');
    $routes->post('select-class-sub-cat', 'Ajax::selectClassSubCat');
    $routes->post('pay_fee', 'Ajax::pay_fee');
    $routes->post('get_students', 'Ajax::get_students');
    $routes->post('check_father_cinic', 'Ajax::check_father_cinic');


    // GET Methods
    $routes->get('check_username', 'Ajax::check_username');
    $routes->get('check_value', 'Ajax::check_value');
    $routes->get('check_emp_value', 'Ajax::check_emp_value');
    $routes->get('check_fee_month', 'Ajax::check_fee_month');
});
 

 $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('student_id_card', 'StudentIdCard::index');
    $routes->post('student_id_card/data', 'StudentIdCard::data');
    $routes->get('student_id_card/vertical', 'StudentIdCard::vertical');
    $routes->post('student_id_card/data_vertical', 'StudentIdCard::data_vertical');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_contact_list', 'StudentsContactList::index');
    $routes->post('students_contact_list/data', 'StudentsContactList::data');
    $routes->get('students_contact_list/add', 'StudentsContactList::add');
    $routes->get('students_contact_list/edit', 'StudentsContactList::edit');
    $routes->post('students_contact_list/save', 'StudentsContactList::save');
    $routes->post('students_contact_list/get_parentinfo', 'StudentsContactList::get_parentinfo');
    $routes->post('students_contact_list/get_studentinfo', 'StudentsContactList::get_studentinfo');
    $routes->get('students_contact_list/delete', 'StudentsContactList::delete');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Defaulter students list main page
    $routes->get('students_defaulters_list', 'StudentsDefaultersList::index');
    // Data AJAX (datatable or API)
    $routes->post('students_defaulters_list/data', 'StudentsDefaultersList::data');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Defaulter Fee Report main view
    $routes->get('defaulter_students_fee_report', 'DefaulterStudentsFeeReport::index');
    // Data (AJAX/post)
    $routes->post('defaulter_students_fee_report/data', 'DefaulterStudentsFeeReport::data');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_prevfee', 'StudentsPrevfee::index');
    $routes->post('students_prevfee/data', 'StudentsPrevfee::data');
    $routes->post('students_prevfee/selectClassFee', 'StudentsPrevfee::selectClassFee');
    $routes->post('students_prevfee/saveStudent', 'StudentsPrevfee::saveStudent');
    $routes->post('students_prevfee/save', 'StudentsPrevfee::save');
});

// Admin Attachment Types routes (CI4 style)
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('attachment_types', 'AttachmentTypes::index');
    $routes->post('attachment_types/data', 'AttachmentTypes::data');
    $routes->get('attachment_types/add', 'AttachmentTypes::add');
    $routes->get('attachment_types/edit', 'AttachmentTypes::edit');
    $routes->post('attachment_types/save', 'AttachmentTypes::save');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('fee_plan_months', 'FeePlanMonths::index');
    $routes->post('fee_plan_months/data', 'FeePlanMonths::data');
    $routes->post('fee_plan_months/data2', 'FeePlanMonths::data2');
    $routes->post('fee_plan_months/updateFeePlanMonth', 'FeePlanMonths::updateFeePlanMonth');
    $routes->get('fee_plan_months/add', 'FeePlanMonths::add');
    $routes->get('fee_plan_months/edit', 'FeePlanMonths::edit');
    $routes->post('fee_plan_months/save', 'FeePlanMonths::save');
});

// Admin Routes for Student Fee Report
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('student_fee_report', 'StudentFeeReport::index');
    $routes->post('student_fee_report/single-student-feedata', 'StudentFeeReport::singleStudentFeedata');
    $routes->post('student_fee_report/data2', 'StudentFeeReport::data2');
    $routes->post('student_fee_report/get_studentinfo', 'StudentFeeReport::get_studentinfo');
    $routes->get('student_fee_report/report_by_fee_type', 'StudentFeeReport::report_by_fee_type');
    $routes->get('student_fee_report/report_by_fee_student', 'StudentFeeReport::report_by_fee_student');
    $routes->get('student_fee_report/edit', 'StudentFeeReport::edit');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('parents_paidfee', 'ParentsPaidfee::index');
    $routes->post('parents_paidfee/data', 'ParentsPaidfee::data');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('parents_balancefee', 'ParentsBalancefee::index');
    $routes->post('parents_balancefee/data', 'ParentsBalancefee::data');
    $routes->post('parents_balancefee/update_fee_status', 'ParentsBalancefee::update_fee_status');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('fee_chalan_month', 'FeeChalanMonth::index');
    $routes->post('fee_chalan_month/getTotalfee', 'FeeChalanMonth::getTotalfee');
    $routes->post('fee_chalan_month/getTotalfeebymonth', 'FeeChalanMonth::getTotalfeebymonth');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('family_fee_history', 'FamilyFeeHistory::index');
    $routes->post('family_fee_history/data', 'FamilyFeeHistory::data');
    $routes->post('family_fee_history/get_parentinfo', 'FamilyFeeHistory::get_parentinfo');
    $routes->post('family_fee_history/get_studentinfo', 'FamilyFeeHistory::get_studentinfo');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('family_fee_report', 'FamilyFeeReport::index');
    $routes->post('family_fee_report/single_student_feedata', 'FamilyFeeReport::singleStudentFeedata');
    $routes->post('family_fee_report/data2', 'FamilyFeeReport::data2');
    $routes->post('family_fee_report/data', 'FamilyFeeReport::data');
    $routes->get('family_fee_report/report_by_fee_type', 'FamilyFeeReport::report_by_fee_type');
    $routes->get('family_fee_report/report_by_fee_student', 'FamilyFeeReport::report_by_fee_student');
    $routes->get('family_fee_report/edit', 'FamilyFeeReport::edit');
    $routes->post('family_fee_report/get_studentinfo', 'FamilyFeeReport::get_studentinfo');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('expense_head', 'ExpenseHead::index');
    $routes->post('expense_head/data', 'ExpenseHead::data');
    $routes->get('expense_head/add', 'ExpenseHead::add');
    $routes->get('expense_head/edit', 'ExpenseHead::edit'); // expects ?id=XX
    $routes->post('expense_head/save', 'ExpenseHead::save');
    $routes->get('expense_head/delete', 'ExpenseHead::delete'); // expects ?id=XX
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('expenses', 'Expenses::index');
    $routes->post('expenses/data', 'Expenses::data');
    $routes->post('expenses/get-expenses', 'Expenses::getExpenses');
    $routes->get('expenses/add', 'Expenses::add');
    $routes->get('expenses/edit', 'Expenses::edit'); // expects ?id=XX
    $routes->post('expenses/save', 'Expenses::save');
    $routes->get('expenses/delete', 'Expenses::delete'); // expects ?id=XX
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('asset_heads', 'AssetHeads::index');
    $routes->post('asset_heads/data', 'AssetHeads::data');
    $routes->get('asset_heads/add', 'AssetHeads::add');
    $routes->get('asset_heads/edit', 'AssetHeads::edit'); // expects ?id=XX
    $routes->post('asset_heads/save', 'AssetHeads::save');
    $routes->get('asset_heads/delete', 'AssetHeads::delete'); // expects ?id=XX
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('assets', 'Assets::index');
    $routes->post('assets/data', 'Assets::data');
    $routes->post('assets/get-assets', 'Assets::getAssets');
    $routes->get('assets/add', 'Assets::add');
    $routes->post('assets/save', 'Assets::save');
    $routes->get('assets/delete', 'Assets::delete');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('datesheet2', 'Datesheet2::index');
    $routes->get('datesheet2/datesheet_without_syllabus', 'Datesheet2::datesheet_without_syllabus');
    $routes->get('datesheet2/data', 'Datesheet2::data');
    $routes->get('datesheet2/add', 'Datesheet2::add');
    $routes->get('datesheet2/edit', 'Datesheet2::edit');
    $routes->post('datesheet2/save', 'Datesheet2::save');
    $routes->post('datesheet2/selectSubjects', 'Datesheet2::selectSubjects');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('grades', 'Grades::index');
    $routes->post('grades/data', 'Grades::data');
    $routes->get('grades/add', 'Grades::add');
    $routes->get('grades/edit', 'Grades::edit');
    $routes->post('grades/save', 'Grades::save');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('grading-policy', 'GradingPolicy::index');
    $routes->post('grading-policy/data', 'GradingPolicy::data');
    $routes->post('grading-policy/data2', 'GradingPolicy::data2');
    $routes->get('grading-policy/add', 'GradingPolicy::add');
    $routes->get('grading-policy/edit', 'GradingPolicy::edit');
    $routes->post('grading-policy/save', 'GradingPolicy::save');
    $routes->get('grading-policy/delete', 'GradingPolicy::delete');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students-subject-results', 'StudentsSubjectResults::index');
    $routes->post('students-subject-results/data', 'StudentsSubjectResults::data');
    $routes->get('students-subject-results/add', 'StudentsSubjectResults::add');
    $routes->get('students-subject-results/edit', 'StudentsSubjectResults::edit');

    // Batch save (you already have this and can keep it)
    $routes->post('students-subject-results/save', 'StudentsSubjectResults::save');

    // 🔹 NEW: per-field auto-save endpoint (uses active exam automatically)
    $routes->post('students-subject-results/save-mark', 'StudentsSubjectResults::saveMark');

    $routes->post('students-subject-results/select-section-subject-by-section', 'StudentsSubjectResults::selectSectionSubjectbySection');
    $routes->post('students-subject-results/get-students', 'StudentsSubjectResults::get_students');
    $routes->post('students-subject-results/save-mark', 'StudentsSubjectResults::saveMark');
});

// Students Results - Admin Panel
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_results', 'StudentsResults::index');           // Show main results page
    $routes->post('students_results/data', 'StudentsResults::data');      // Data endpoint for DataTables/ajax
    $routes->get('students_results/add', 'StudentsResults::add');         // Add new student result form
    $routes->get('students_results/edit', 'StudentsResults::edit');       // Edit form (expects ?id= in URL)
    $routes->post('students_results/save', 'StudentsResults::save');      // Save (insert/update)
    $routes->post('students_results/get_students', 'StudentsResults::get_students'); // Get students/subjects form HTML
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('test_series', 'TestSeries::index');
    $routes->post('test_series/data', 'TestSeries::data');
    $routes->get('test_series/add', 'TestSeries::add');
    $routes->get('test_series/edit', 'TestSeries::edit'); // expects ?id=
    $routes->post('test_series/save', 'TestSeries::save');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('tests', 'Tests::index');
    $routes->post('tests/data', 'Tests::data');
    $routes->get('tests/add', 'Tests::add');
    $routes->post('tests/save', 'Tests::save');
    $routes->post('tests/selecttestslist', 'Tests::selectTestsList');
    $routes->post('tests/selectsubjectbysection', 'Tests::selectSubjectbySection');
    $routes->get('tests/delete', 'Tests::delete'); 
    $routes->get('tests/listtests', 'Tests::listTests'); // expects ?id=
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('test-results', 'TestResults::index');

    $routes->post('test_results/data', 'TestResults::data');
    $routes->get('test_results/add', 'TestResults::add');
    $routes->get('test_results/edit', 'TestResults::edit'); // expects ?id=
    $routes->post('test_results/save', 'TestResults::save');
    $routes->post('test_results/get_students', 'TestResults::get_students');
    $routes->post('admin/test-result-card/data', 'TestResults::cardData');
    $routes->post('test_results/get_subjects', 'TestResults::get_subjects');
    $routes->post('test_results/update', 'TestResults::update');
    $routes->post('test_results/list-tests',     'TestResults::listTests');   // <-- points to TestResults
    $routes->post('test_results/delete-test',    'TestResults::deleteTest'); 

    $routes->get('test-result-card', 'TestResults::card', ['as' => 'test_result_card']);
    $routes->post('test-result-card/data', 'TestResults::cardData');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('test-series-result-card', 'TestSeriesResultCard::index');
    $routes->post('test_series_result_card/data', 'TestSeriesResultCard::data');

    $routes->post('test-result-card/list-tests', 'TestSeriesResultCard::listTests');
    $routes->post('test-result-card/delete-test', 'TestSeriesResultCard::deleteTest');
});


// Admin Students Enroll routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_enroll', 'StudentsEnroll::index');
    $routes->post('students_enroll/data', 'StudentsEnroll::data');
    $routes->post('students_enroll/enrollstudentinfo', 'StudentsEnroll::enrollStudentInfo');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('studentsbulk', 'Studentsbulk::index');
    $routes->post('studentsbulk/data', 'Studentsbulk::data');
    $routes->post('studentsbulk/selectclassfee', 'Studentsbulk::selectClassFee');
    $routes->post('studentsbulk/savestudent', 'Studentsbulk::saveStudent');
    $routes->post('studentsbulk/save', 'Studentsbulk::save');
});



    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('studentsbulkparents', 'StudentsbulkParents::index');
        $routes->post('studentsbulkparents/data', 'StudentsbulkParents::data');
        $routes->post('studentsbulkparents/selectclassfee', 'StudentsbulkParents::selectClassFee');
        $routes->post('studentsbulkparents/savestudent', 'StudentsbulkParents::saveStudent');
        $routes->post('studentsbulkparents/save', 'StudentsbulkParents::save');
            $routes->post('studentsbulkparents/get-siblings', 'StudentsbulkParents::getSiblings');
    
$routes->post('studentsbulkparents/relink', 'StudentsbulkParents::relink');

 $routes->post('studentsbulkparents/get-siblings', 'StudentsbulkParents::getSiblings');
    $routes->post('studentsbulkparents/relink', 'StudentsbulkParents::relink');
    $routes->post('studentsbulkparents/get-student-parent', 'StudentsbulkParents::getStudentParent');
    $routes->get('studentsbulkparents/search-parents-by-name', 'StudentsbulkParents::searchParentsByName');
    $routes->post('studentsbulkparents/get-parent-details', 'StudentsbulkParents::getParentDetails');


    });


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('classwise_results', 'Classwise_results::index');
    $routes->post('classwise_results/data', 'Classwise_results::data');
});



$routes->post('studentsbulkparents/get-student-parent', 'StudentsbulkParents::getStudentParent');
$routes->get('studentsbulkparents/search-parents-by-name', 'StudentsbulkParents::searchParentsByName');
$routes->post('studentsbulkparents/get-parent-details', 'StudentsbulkParents::getParentDetails');
$routes->post('studentsbulkparents/relink', 'StudentsbulkParents::relink');
// Admin


$routes->group('admin', ['namespace'=>'App\Controllers\Admin'], function($routes){
  // Main quiz routes
  $routes->get ('quizzes',                'Quizzes::index');
  $routes->get ('quizzes/create',         'Quizzes::create');
  $routes->post('quizzes/store',          'Quizzes::store');
  
  // Results and reports routes
  $routes->get ('quizzes/(:num)/results', 'Quizzes::results/$1');
  $routes->get ('quizzes/(:num)/review',  'Quizzes::review/$1');
  $routes->get ('quizzes/class-results/(:num)', 'Quizzes::classResults/$1');
  $routes->get ('quizzes/class-summary/(:num)', 'Quizzes::classSummary/$1');
  $routes->get ('quizzes/class-export/(:num)',  'Quizzes::classExport/$1');
  
  // AJAX routes
  $routes->group('quizzes/ajax', function($routes) {
    $routes->get('class-sections', 'Quizzes::ajaxClassSections');
    $routes->get('section-subjects/(:num)', 'Quizzes::ajaxSectionSubjects/$1');
    $routes->get('by-filters', 'Quizzes::ajaxByFilters');
    $routes->get('terms', 'Quizzes::ajaxTermsBySession');
    $routes->get('qb-subjects', 'Quizzes::ajaxQbSubjects');
    $routes->get('qb-topics', 'Quizzes::ajaxQbTopics');
    $routes->get('qb-summary', 'Quizzes::ajaxQbSummary');
    $routes->post('qb-questions', 'Quizzes::ajaxQbQuestions');
  });
  
  // Specific AJAX routes (with different patterns)
  $routes->get('quizzes/ajaxQbTopicsBySecSub/(:num)', 'Quizzes::ajaxQbTopicsBySecSub/$1');
  $routes->get('quizzes/ajaxQbQuestionsBySecSub/(:num)', 'Quizzes::ajaxQbQuestionsBySecSub/$1');
  $routes->post('quizzes/ajaxQbQuestionsBySecSub/(:num)', 'Quizzes::ajaxQbQuestionsBySecSub/$1');
});


$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
        $routes->get('mapping',                 'SportsMapping::index');
        $routes->post('mapping/board',          'SportsMapping::board');
        $routes->post('mapping/move',          'SportsMapping::move');
        $routes->post('mapping/assign-one',     'SportsMapping::assignOne');
        $routes->post('mapping/assign-bulk',    'SportsMapping::assignBulk');
});



$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    // Houses
    $routes->get('houses',                'SportsHouses::index');
    $routes->post('houses/data',          'SportsHouses::data');       // DataTables JSON
    $routes->get('houses/add',            'SportsHouses::add');
    $routes->get('houses/edit/(:num)',    'SportsHouses::edit/$1');
    $routes->post('houses/save',          'SportsHouses::save');
    $routes->post('houses/toggle',        'SportsHouses::toggleStatus');
     $routes->post('houses/members', 'SportsHouses::members'); // NEW

    // Student ↔ House mapping
    $routes->get('mapping',               'SportsMapping::index');
    $routes->post('mapping/assign',       'SportsMapping::assign');    // single/bulk
    $routes->post('mapping/import',       'SportsMapping::import');    // optional CSV

    // House mentors (teachers)
    $routes->get('mentors',               'SportsMentors::index');
    $routes->post('mentors/assign',       'SportsMentors::assign');
    $routes->post('mentors/remove',       'SportsMentors::remove');

    // Events
    $routes->get('events',                'SportsEvents::index');
    $routes->post('events/data',          'SportsEvents::data');
    $routes->get('events/add',            'SportsEvents::add');
    $routes->get('events/edit/(:num)',    'SportsEvents::edit/$1');
    $routes->post('events/save',          'SportsEvents::save');
    $routes->post('events/status',        'SportsEvents::updateStatus'); // scheduled/live/completed
      $routes->get ('bulk',        'SportsEvents::bulk');       // page
    $routes->post('events/bulk/fetch',  'SportsEvents::bulkFetch');  // AJAX: list existing by filters
    $routes->post('events/bulk/save',   'SportsEvents::bulkSave');  
    $routes->post('events/save',   'SportsEvents::Save');   
    // Event managers
    $routes->get('managers/(:num)',       'SportsManagers::index/$1'); // by event_id
    $routes->post('managers/assign',      'SportsManagers::assign');
    $routes->post('managers/remove',      'SportsManagers::remove');

    // Entries
   $routes->get('entries',               'SportsEntries::index');     // base -> redirects to Events
   $routes->get('entries/(:num)',        'SportsEntries::byEvent/$1'); // <-- was ::index/$1 (wrong)
   $routes->post('entries/add',          'SportsEntries::add');
   $routes->post('entries/delete',       'SportsEntries::delete');
   $routes->get('teams',                 'SportsTeams::index');        // landing with form

  $routes->get('teams/(:num)/members', 'SportsTeamMembers::byTeam/$1');
    $routes->get('teams',                 'SportsTeams::index'); 
       $routes->get('teams/event/(:num)',    'SportsTeams::byEvent/$1'); 
  $routes->post('teams/save',           'SportsTeams::save');         // create
  $routes->post('teams/delete',         'SportsTeams::delete');       // delete

 // Members (unchanged)
  $routes->get('teams/(:num)/members',  'SportsTeamMembers::index/$1');
    $routes->post('team-members/add',    'SportsTeamMembers::add');
    $routes->post('team-members/delete', 'SportsTeamMembers::delete');

    // --- Event entries (participants) ---
    // base index is fine if you already have it; this one shows the event entries page

    // Results
    $routes->get('results',        'SportsResults::index');  // event_id
     $routes->post('results/rows',          'SportsResults::rows'); 
       $routes->post('results/set-position','SportsResults::setPosition');
       $routes->post('results/clear-position','SportsResults::clearPosition');
    $routes->post('results/save',         'SportsResults::save');      // positions + points
    
    $routes->get('house-sheet/(:num)',    'SportsResults::houseSheet/$1'); // house_id

    $routes->get('house-sheet',          'SportsMapping::houseSheet');       // page
    $routes->post('house-sheet/data',    'SportsMapping::houseSheetData');   // ajax data


    // Scoring rules
    $routes->get('rules',                 'SportsRules::index');
    $routes->post('rules/save',           'SportsRules::save');

    // Adjustments
    $routes->get('adjustments',           'SportsAdjustments::index');
    $routes->post('adjustments/save',     'SportsAdjustments::save');
       $routes->get('events/order',         'SportsEventsOrder::index');         // page

  $routes->post('events/order/save', 'SportsEventsOrder::updateOrder'); 
   // ✅ matches method
$routes->post('events/order/time',   'SportsEventsOrder::updateTime');    // ✅ optional, for time

}); 



$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('bulk-events',        'Bulk_Events::index');
    $routes->post('bulk-events/load',  'Bulk_Events::load');
    $routes->post('bulk-events/save',  'Bulk_Events::save');
    
});

$routes->get('admin/sports/age-report', 'Admin\SportsAgeReport::index');


$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    // … your existing sports routes …

    // Reports (Events & Participants)
    $routes->group('reports', static function($routes) {
        $routes->get('events',       'SportsReports::events');      // GET page
        $routes->post('events/data', 'SportsReports::eventsData');  // POST data

    });
});




$routes->group('admin/sports/reports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    $routes->get('house-members',        'SportsReports::houseMembers');
    $routes->post('house-members/data',  'SportsReports::houseMembersData');

    // FIXED ROUTE
    $routes->post('events/delete-student', 'SportsReports::deleteStudent');
});

$routes->group('admin/ajax', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // returns HTML <option> list of students
    $routes->post('students-options', 'Ajax::studentsOptions');
    $routes->post('individual-students-options', 'Ajax::individualStudentsOptions');
    // optional: houses <option> list (useful elsewhere)
    $routes->post('houses-options',   'Ajax::housesOptions');
    $routes->post('individual-students-cards', 'Ajax::individualStudentsCards');

});


$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('report-points', 'SportsResultsReport::index');
    
      $routes->post('reports/points/data', 'SportsResultsReport::data');
});



$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('participation-report', 'SportsParticipationReport::index');
    $routes->post('participation-report/data', 'SportsParticipationReport::data');
});


$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    
    $routes->get('participation-report', 'SportsParticipationReport::index');
    $routes->post('participation-report/data', 'SportsParticipationReport::data');
});

$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('leaderboard', 'SportsLeaderboard::index');
    $routes->post('leaderboard/house', 'SportsLeaderboard::houseData');
    $routes->post('leaderboard/students', 'SportsLeaderboard::studentData');
});

$routes->group('admin/sports', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('entries/seats',                'SportsEntriesSeats::index');
    $routes->get('entries/seats/(:num)',         'SportsEntriesSeats::seats/$1');
    $routes->post('entries/seats/members',       'SportsEntriesSeats::members'); 
    $routes->post('entries/seats/add',           'SportsEntriesSeats::add');
    $routes->post('entries/seats/remove',        'SportsEntriesSeats::remove');
    $routes->post('entries/seats/cart',          'SportsEntriesSeats::cart');
     $routes->post('entries/seats/meta',     'SportsEntriesSeats::meta');  
});


$routes->group('admin/sports/results', static function($routes) {
    $routes->get('/',               'SportsResults::index');
    $routes->post('list-events',    'SportsResults::listEvents');       // filterable events list
    $routes->post('rows',           'SportsResults::rows');             // rows for one event id (or all)
    $routes->post('set-position',   'SportsResults::setPosition');      // set/change 1st/2nd/3rd
    $routes->post('clear-position', 'SportsResults::clearPosition');    // clear position
});


// app/Config/Routes.php
$routes->post('settings/set-currency', 'Settings::setCurrency');

// Additional public routes can be placed here
// ...

// If you have an API, you can group them under a common prefix
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // API routes would go here
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // Question bank JSON search used by the Create Quiz view
    $routes->get('bank/search', 'Bank::search');   // <-- this fixes the 404
});



$routes->group('student', ['namespace' => 'App\Controllers\Frontend'], static function($routes) {
    // Section pages (protected by authguard from earlier step)
    $routes->get('fees',      'Fees::index',      ['as' => 'fees.index']);
    $routes->get('results',   'Results::index',   ['as' => 'results.index']);
    $routes->get('attendance','Attendance::index',['as' => 'attendance.index']);

    // $routes->get('datesheet', 'Datesheet::index', ['as' => 'datesheet.index']);
    $routes->get('events',    'Events::index',    ['as' => 'events.index']);
     $routes->get('datesheet', 'DatesheetController::index');
     $routes->post('datesheet/switch/(:num)', 'DatesheetController::switchStudent/$1');
   
  $routes->get('vocabbank/data', 'VocabBank::getVocabularyData');
$routes->get('student/vocabbank', 'VocabBank::index');
$routes->get('vocabbank', 'VocabBank::index');
});

$routes->group('student', ['namespace' => 'App\Controllers\Frontend'], static function($routes) {
    // Auth
    $routes->get('login',  'Auth::showLogin', ['as' => 'login']);
    $routes->post('login', 'Auth::doLogin',   ['as' => 'login.post']);
    $routes->get('logout', 'Auth::logout',    ['as' => 'logout']);

    // Dashboard
    $routes->get('dashboard', 'Dashboard::index', ['as' => 'dashboard']);
    
    // Parent Dashboard Routes
    $routes->get('switch/(:num)', 'Dashboard::switchStudent/$1', ['as' => 'switch.student']);
    $routes->post('upload-audio', 'Dashboard::uploadAudioRecording');
    $routes->post('upload-video', 'Dashboard::uploadVideoRecording');
    $routes->post('upload-picture', 'Dashboard::uploadPicture');

$routes->get('get-prayer-status', 'Dashboard::getPrayerStatus');
$routes->post('save-prayer', 'Dashboard::savePrayer');
$routes->get('get-prayer-stats', 'Dashboard::getPrayerStats');

$routes->get('get-bmi-data/(:num)', 'Dashboard::getBmiData/$1');
$routes->get('get-bmi-history/(:num)', 'Dashboard::getBmiHistory/$1');
$routes->get('get-bmi-suggestions/(:any)', 'Dashboard::getBmiSuggestions/$1');

    
    // Quiz Routes
    $routes->get('quizzes', 'Quizzes::index');
    $routes->get('quizzes/start/(:num)', 'Quizzes::start/$1');
    $routes->get('quizzes/practice/(:num)', 'Quizzes::practice/$1');
    $routes->post('quizzes/save-answer', 'Quizzes::saveAnswer');
    $routes->post('quizzes/submit', 'Quizzes::submit');
    $routes->get('quizzes/review/(:num)', 'Quizzes::review/$1');
    $routes->get('quizzes/results/(:num)', 'Quizzes::results/$1');
    $routes->get('quizzes/pending', 'PendingQuizzes::index');
    $routes->get('quizzes/attempted', 'AttemptedQuizzes::index');

    // Adaptive Quiz Routes
    $routes->post('quizzes/submit-level', 'Quizzes::submitLevel');
    $routes->post('quizzes/move-to-next-level', 'Quizzes::moveToNextLevel');
    $routes->post('quizzes/retry-current-level', 'Quizzes::retryCurrentLevel');
    $routes->post('quizzes/complete-adaptive-quiz', 'Quizzes::completeAdaptiveQuiz');
    $routes->get('quizzes/adaptive/(:num)', 'Quizzes::startAdaptive/$1');
});



// OR OPTION B: Group route (if you have student group)
$routes->group('student', ['filter' => 'auth'], function($routes) {
   
    // ... other student routes
});




$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    // other admin quiz routes ...

    // Use fully-qualified controller so CI does NOT prepend the admin namespace
    $routes->get(
        'quizzes/review/(:num)',
        '\App\Controllers\Frontend\Quizzes::review/$1'
    );

});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    // ... existing routes ...

    // Existing generic print (keep it)
    $routes->get('quizzes/print/(:num)', 'Quizzes::print/$1');

    // NEW: per-student randomized versions
    $routes->get('quizzes/print-versions/(:num)', 'Quizzes::printVersions/$1');

     $routes->get('quizzes/print-all/(:num)', 'Quizzes::printAll/$1');
     $routes->get('quizzes/print-all-key/(:num)', 'Quizzes::printAllKey/$1');

     $routes->post('quizzes/update-single-question', 'Quizzes::updateSingleQuestion');

     $routes->post('quizzes/delete-question', 'Quizzes::deleteQuestion');

    
     $routes->get('quizzes/edit-questions/(:num)', 'Quizzes::editQuestions/$1');

$routes->post('quizzes/update-questions/(:num)', 'Quizzes::updateQuestions/$1');

  $routes->get('quizzes/delete-quiz/(:num)', 'Quizzes::deleteConfirm/$1');
    $routes->post('quizzes/delete-quiz/(:num)', 'Quizzes::deleteQuiz/$1');
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    // existing...
    // $routes->get('quizzes', 'Quizzes::index');
    // ...

    // 🆕 Printable quiz route
    $routes->get('quizzes/print/(:num)', 'Quizzes::print/$1');
});



$routes->get('quiz/start/(:num)', 'Frontend\Quizzes::start/$1');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('quiz-assign', 'QuizAssign::index');
    $routes->get('load_subjects/(:num)', 'QuizAssign::load_subjects/$1');

    $routes->get('load_quizzes_by_clssec/(:num)', 'QuizAssign::load_quizzes_by_clssec/$1');

    $routes->get('load_quizzes/(:num)', 'QuizAssign::load_quizzes/$1');
    $routes->post('generate-link', 'QuizAssign::generateImpersonationLink');
  $routes->get('load_students_for_quiz/(:num)/(:num)', 'QuizAssign::load_students_for_quiz/$1/$2');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
  $routes->get('academic-calendar/builder', 'AcademicCalendar::builder');
$routes->post('academic-calendar/save',   'AcademicCalendar::save');

$routes->post('academic-calendar/ajax-quick-add', 'AcademicCalendar::ajaxQuickAdd');
});


$routes->group('public', function($routes) {
    // List of all public live quizzes
    $routes->get('quizzes', 'PublicQuiz::index');

    // Play a public quiz (GET = form, POST = start)
    $routes->match(['get', 'post'], 'quiz/(:num)', 'PublicQuiz::start/$1');

    // Optional leaderboard
    $routes->get('quiz/(:num)/leaderboard', 'PublicQuiz::leaderboard/$1');
});

/*
|--------------------------------------------------------------------------
| Student – Quiz Battles (1v1)
|--------------------------------------------------------------------------
*/
$routes->group('student', ['namespace' => 'App\Controllers\Frontend'], function ($routes) {

    $routes->get('battles',                'Battles::index');
    
     $routes->get('battles/data', 'Battles::data');

    $routes->post('battles/create',        'Battles::create');
    $routes->post('battles/accept',        'Battles::accept');
    $routes->post('battles/decline',       'Battles::decline');

    $routes->get('battles/play/(:num)',    'Battles::play/$1');
    $routes->post('battles/submit/(:num)', 'Battles::submit/$1');

    $routes->get('battles/view/(:num)',    'Battles::view/$1');
});



/*
|--------------------------------------------------------------------------
| Admin – Quiz Battles (Read Only)
|--------------------------------------------------------------------------
*/
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'authguard'], function ($routes) {

    $routes->get('quiz-battles', 'QuizBattles::index');
    $routes->get('quiz-battles/view/(:num)', 'QuizBattles::view/$1');


});



$routes->get('admin', 'AdminDispatcher::route');
$routes->get('admin/(:any)', 'Admin\Fallback::index');
$routes->get('media/qb/(:any)', 'Media::qbQuestionImage/$1');

