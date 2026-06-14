<?php

/**
 * AdminExams
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('exam', 'Exam::index');
    $routes->post('exam/data', 'Exam::data');
    $routes->get('exam/add', 'Exam::add');
    $routes->get('exam/edit', 'Exam::edit'); // expects ?id= in query
    $routes->post('exam/save', 'Exam::save');
    $routes->post('exam/announce', 'Exam::announce');
    $routes->post('exam/delete', 'Exam::delete');
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
    $routes->get('search-students', 'Datesheet::searchStudentsByName');
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
    $routes->post('datesheet_report/data', 'DatesheetReport::data');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students-results-list', 'StudentsResultsList::index');
    $routes->post('students-results-list/get-students', 'StudentsResultsList::get_students');
    $routes->post('students-results-list/get-exams', 'StudentsResultsList::get_exams');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students-results-list', 'StudentsResultsList::index');
    $routes->post('students-results-list/get-students', 'StudentsResultsList::get_students');
    $routes->post('students-results-list/get-exams', 'StudentsResultsList::get_exams');
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
    // Redirects to students_absentees/add (see StudentsAttendance::add)
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
    $routes->post('students_absentees/activate_class_enrollment', 'StudentsAbsentees::activate_class_enrollment');
    $routes->post('students_absentees/sections_for_date', 'StudentsAbsentees::sections_for_date');
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
    $routes->post('timetable/bulk-update-slot-row', 'Timetable::bulkUpdateSlotRow');
    $routes->get('timetable/generator', 'Timetable::generator');
    $routes->get('timetable/generator-bootstrap', 'Timetable::generatorBootstrap');
    $routes->post('timetable/save-generator-constraints', 'Timetable::saveGeneratorConstraints');
    $routes->post('timetable/save-generator-slots', 'Timetable::saveGeneratorSlots');
    $routes->post('timetable/generate-from-constraints', 'Timetable::generateFromConstraints');
    $routes->post('timetable/manual-place-from-pool', 'Timetable::manualPlaceFromPool');
    $routes->get('timetable/report', 'Timetable::report');
    $routes->post('timetable/report-data', 'Timetable::reportData');
    $routes->post('timetable/report-adjust-data', 'Timetable::reportAdjustData');
    $routes->post('timetable/report-adjust-feasible', 'Timetable::reportAdjustFeasibleSlots');
    $routes->post('timetable/report-adjust-place', 'Timetable::reportAdjustPlace');
    $routes->post('timetable/report-adjust-clear', 'Timetable::reportAdjustClear');
    $routes->post('timetable/report-adjust-teacher', 'Timetable::reportAdjustTeacherTimetable');
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
    // School timing (legacy timing-type routes redirect to School_timing)
    $routes->get('school_timming_type', static fn () => redirect()->to(base_url('admin/school_timing/add')));
    $routes->get('school_timming_type/add', static fn () => redirect()->to(base_url('admin/school_timing/add')));
    $routes->get('school_timming_type/edit', static fn () => redirect()->to(base_url('admin/school_timing/add')));
    $routes->post('school_timming_type/data', 'School_timming_type::data');
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
