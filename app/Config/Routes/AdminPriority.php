<?php

/**
 * AdminPriority
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
// Academic Setup wizard JSON (register early; greedy admin/(:.*) fallback must not win over /academic-setup/…)
$routes->get('admin/academic-wizard-bootstrap', 'AcademicSetup::bootstrapData', ['namespace' => 'App\Controllers\Admin']);

// Quiz publish toggle — explicit URI + FQCN so POST always resolves (same path as admin group routes)
$routes->post('admin/quizzes/(:num)/toggle-published', '\App\Controllers\Admin\Quizzes::togglePublished/$1');

// Question bank — explicit full paths (registered early so other admin route groups cannot shadow them)
$routes->get('admin/question-bank/overview', '\App\Controllers\Admin\QuestionBank::index');
$routes->get('admin/question-bank/form', '\App\Controllers\Admin\QuestionBank::form');
$routes->get('admin/question-bank/proof-read', '\App\Controllers\Admin\QuestionBank::proofRead');
$routes->get('admin/question-bank/question/(:num)', '\App\Controllers\Admin\QuestionBank::questionOne/$1');
$routes->post('admin/question-bank/save-ajax', '\App\Controllers\Admin\QuestionBank::saveAjax');
$routes->post('admin/question-bank/delete', '\App\Controllers\Admin\QuestionBank::deleteQuestion');
$routes->get('admin/question-bank-ai', '\App\Controllers\Admin\QuestionBankAi::index');
$routes->post('admin/question-bank-ai/generate', '\App\Controllers\Admin\QuestionBankAi::generate');
$routes->post('admin/question-bank-ai/save-topic', '\App\Controllers\Admin\QuestionBankAi::saveTopic');
$routes->post('admin/question-bank-ai/update-topic', '\App\Controllers\Admin\QuestionBankAi::updateTopic');

$routes->get('admin/question-paper', '\App\Controllers\Admin\QuestionPaper::index');
$routes->get('admin/question-paper/ajax/qb-summary', '\App\Controllers\Admin\QuestionPaper::ajaxSummary');
$routes->post('admin/question-paper/ajax/qb-questions', '\App\Controllers\Admin\QuestionPaper::ajaxQuestions');
$routes->post('admin/question-paper/preview', '\App\Controllers\Admin\QuestionPaper::preview');
$routes->post('admin/question-paper/print', '\App\Controllers\Admin\QuestionPaper::print');
$routes->post('admin/question-paper/print-key', '\App\Controllers\Admin\QuestionPaper::printKey');
$routes->post('admin/question-paper/print-versions', '\App\Controllers\Admin\QuestionPaper::printVersions');
$routes->post('admin/question-paper/download-word', '\App\Controllers\Admin\QuestionPaper::downloadWord');
$routes->post('admin/question-paper/download-word-key', '\App\Controllers\Admin\QuestionPaper::downloadWordKey');
$routes->get('admin/question-paper/templates', '\App\Controllers\Admin\QuestionPaper::templates');
$routes->post('admin/question-paper/templates/save', '\App\Controllers\Admin\QuestionPaper::saveTemplate');
$routes->get('admin/question-paper/templates/(:num)', '\App\Controllers\Admin\QuestionPaper::loadTemplate/$1');
$routes->post('admin/question-paper/templates/delete/(:num)', '\App\Controllers\Admin\QuestionPaper::deleteTemplate/$1');

$routes->post('admin/question-paper/store', '\App\Controllers\Admin\QuestionPaper::store');
$routes->get('admin/question-paper/ajax/by-filters', '\App\Controllers\Admin\QuestionPaper::ajaxByFilters');
$routes->get('admin/question-paper/print-settings/(:num)', '\App\Controllers\Admin\QuestionPaper::printSettings/$1');
$routes->post('admin/question-paper/print-saved/(:num)', '\App\Controllers\Admin\QuestionPaper::printSaved/$1');
$routes->post('admin/question-paper/print-saved-key/(:num)', '\App\Controllers\Admin\QuestionPaper::printSavedKey/$1');

$routes->get('admin/assessment-builder', '\App\Controllers\Admin\AssessmentBuilder::index');

$routes->get('admin/question-bank/subjects', '\App\Controllers\Admin\QuestionBank::subjects');
$routes->get('admin/question-bank/topics', '\App\Controllers\Admin\QuestionBank::topics');

// Campus management AJAX (register early so deploys always resolve these POST routes)
$routes->post('admin/campus-management/update-expiry', '\App\Controllers\Admin\CampusManagement::updateExpiry');
$routes->post('admin/campus-management/get-owner', '\App\Controllers\Admin\CampusManagement::getCampusOwner');
$routes->post('admin/campus-management/reset-owner-password', '\App\Controllers\Admin\CampusManagement::resetOwnerPassword');
$routes->post('admin/campus-management/get-payment-history', '\App\Controllers\Admin\CampusManagement::getPaymentHistory');
$routes->post('admin/campus-management/activate-owner', '\App\Controllers\Admin\CampusManagement::activateOwner');
$routes->post('admin/campus-management/assign-owner-director-system', '\App\Controllers\Admin\CampusManagement::assignOwnerDirectorSystem');

// Parent/student vocabulary — Wikimedia Commons thumbnails (explicit POST so CDNs/deploys never miss grouped routes)
$routes->post('student/vocabbank/commons-images', '\App\Controllers\Frontend\VocabBank::commonsImagesBatch');

// Math crossword worksheets (register early — before admin/(:segment) fallback)
$routes->get('admin/math-crossword', '\App\Controllers\Admin\MathCrossword::index');
$routes->post('admin/math-crossword/generate', '\App\Controllers\Admin\MathCrossword::generate');
$routes->get('admin/math-crossword/library', '\App\Controllers\Admin\MathCrossword::library');
$routes->get('admin/math-crossword/reprint/(:num)', '\App\Controllers\Admin\MathCrossword::reprint/$1');
$routes->match(['get', 'post'], 'admin/math-crossword/assign', '\App\Controllers\Admin\MathCrossword::assign');
$routes->get('admin/math-crossword/report/(:num)', '\App\Controllers\Admin\MathCrossword::report/$1');
$routes->get('admin/math-crossword/vocab-topics', '\App\Controllers\Admin\MathCrossword::vocabTopicsAjax');
$routes->get('admin/math-crossword/assignments-ajax', '\App\Controllers\Admin\MathCrossword::assignmentsAjax');

// Word search puzzles (register early — before admin/(:segment) fallback)
$routes->get('admin/word-search', '\App\Controllers\Admin\WordSearch::index');
$routes->post('admin/word-search/generate', '\App\Controllers\Admin\WordSearch::generate');
$routes->get('admin/word-search/library', '\App\Controllers\Admin\WordSearch::library');
$routes->get('admin/word-search/reprint/(:num)', '\App\Controllers\Admin\WordSearch::reprint/$1');
$routes->match(['get', 'post'], 'admin/word-search/assign', '\App\Controllers\Admin\WordSearch::assign');
$routes->get('admin/word-search/report/(:num)', '\App\Controllers\Admin\WordSearch::report/$1');
$routes->get('admin/word-search/vocab-topics', '\App\Controllers\Admin\WordSearch::vocabTopicsAjax');
$routes->get('admin/word-search/assignments-ajax', '\App\Controllers\Admin\WordSearch::assignmentsAjax');

// Math operations worksheets (register early — before admin/(:segment) fallback)
$routes->get('admin/math-worksheet', '\App\Controllers\Admin\MathWorksheet::index');
$routes->post('admin/math-worksheet/generate', '\App\Controllers\Admin\MathWorksheet::generate');
$routes->get('admin/math-worksheet/library', '\App\Controllers\Admin\MathWorksheet::library');
$routes->get('admin/math-worksheet/reprint/(:num)', '\App\Controllers\Admin\MathWorksheet::reprint/$1');

// Timetable report manual adjust AJAX (register early so POST always resolves on deploy)
$routes->post('admin/timetable/report-adjust-data', '\App\Controllers\Admin\Timetable::reportAdjustData');
$routes->post('admin/timetable/report-adjust-feasible', '\App\Controllers\Admin\Timetable::reportAdjustFeasibleSlots');
$routes->post('admin/timetable/report-adjust-place', '\App\Controllers\Admin\Timetable::reportAdjustPlace');
$routes->post('admin/timetable/report-adjust-clear', '\App\Controllers\Admin\Timetable::reportAdjustClear');
$routes->post('admin/timetable/report-adjust-teacher', '\App\Controllers\Admin\Timetable::reportAdjustTeacherTimetable');

// Employee face enrollment (register early so /data AJAX is never shadowed)
$routes->get('admin/employee-face-management', '\App\Controllers\Admin\EmployeeFaceAttendance::management');
$routes->get('admin/employee-face-management/get-employees', '\App\Controllers\Admin\EmployeeFaceAttendance::getEmployees');
$routes->get('admin/employee-face-management/data', '\App\Controllers\Admin\EmployeeFaceAttendance::data');
$routes->post('admin/employee-face-management/delete', '\App\Controllers\Admin\EmployeeFaceAttendance::delete');
$routes->post('admin/employee-face-management/enroll', '\App\Controllers\Admin\EmployeeFaceAttendance::enroll');
$routes->get('admin/employee-face-attendance', '\App\Controllers\Admin\EmployeeFaceAttendance::index');
$routes->post('admin/employee-face-attendance/mark', '\App\Controllers\Admin\EmployeeFaceAttendance::mark');

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
