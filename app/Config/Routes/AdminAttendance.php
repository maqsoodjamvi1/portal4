<?php

/**
 * AdminAttendance
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
// Legacy token/share routes for parent attendance links. Authenticated family portal uses /student/* (StudentPortal.php).
$routes->group('parent', ['namespace' => 'App\Controllers\Parent'], static function ($routes) {
    $routes->get('attendance/share/(:any)', 'Attendance::share/$1');
    $routes->get('attendance/view/(:any)', 'Attendance::view/$1');
    if (ENVIRONMENT === 'development') {
        $routes->get('attendance', 'Attendance::index');
        $routes->post('attendance/getChildAttendance', 'Attendance::getChildAttendance');
        $routes->get('attendance/debugChildren/(:any)', 'Attendance::debugChildren/$1');
        $routes->get('attendance/debugOffDays/(:num)', 'Attendance::debugOffDays/$1');
        $routes->get('attendance/debugTimings/(:num)', 'Attendance::debugTimings/$1');
    }
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

    $routes->get('student-weekly-non-present-report', 'StudentWeeklyNonPresentReport::index');
    $routes->post('student-weekly-non-present-report/data', 'StudentWeeklyNonPresentReport::data');
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

    $routes->get('employee-face-attendance', 'EmployeeFaceAttendance::index');
    $routes->post('employee-face-attendance/mark', 'EmployeeFaceAttendance::mark');

    $routes->get('employee-face-management', 'EmployeeFaceAttendance::management');
    $routes->get('employee-face-management/get-employees', 'EmployeeFaceAttendance::getEmployees');
    $routes->get('employee-face-management/data', 'EmployeeFaceAttendance::data');
    $routes->post('employee-face-management/delete', 'EmployeeFaceAttendance::delete');
    $routes->post('employee-face-management/enroll', 'EmployeeFaceAttendance::enroll');

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
    $routes->post('dismiss-optional-modules', 'Ajax::dismissOptionalModules');
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
    $routes->post('campus/check-username', 'Campus::checkUsername');
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

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('campus-finance-accounts', 'CampusFinanceAccounts::index');
    $routes->post('campus-finance-accounts/save-account', 'CampusFinanceAccounts::saveAccount');
    $routes->post('campus-finance-accounts/save-settings', 'CampusFinanceAccounts::saveSettings');
    $routes->get('campus-finance-accounts/balances', 'CampusFinanceAccounts::getBalances');
    $routes->get('campus-finance-accounts/accounts-json', 'CampusFinanceAccounts::accountsJson');
});
