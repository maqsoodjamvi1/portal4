<?php

/**
 * AdminAcademicSetup
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
// Academic Setup Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('academic-setup', 'AcademicSetup::index');
    $routes->get('academic-setup/bootstrap-data', 'AcademicSetup::bootstrapData');
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
    $routes->post('campus-management/get-owner', 'CampusManagement::getCampusOwner');
    $routes->post('campus-management/update-expiry', 'CampusManagement::updateExpiry');
    $routes->post('campus-management/reset-owner-password', 'CampusManagement::resetOwnerPassword');
    $routes->post('campus-management/get-payment-history', 'CampusManagement::getPaymentHistory');
    $routes->post('campus-management/activate-owner', 'CampusManagement::activateOwner');
    $routes->post('campus-management/assign-owner-director-system', 'CampusManagement::assignOwnerDirectorSystem');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
    $routes->get('quiz-ai', 'QuizAi::index');
    $routes->post('quiz-ai/generate', 'QuizAi::generate');
    $routes->post('quiz-ai/save', 'QuizAi::save');

    $routes->get('math-crossword', 'MathCrossword::index');
    $routes->post('math-crossword/generate', 'MathCrossword::generate');
    $routes->get('math-crossword/library', 'MathCrossword::library');
    $routes->get('math-crossword/reprint/(:num)', 'MathCrossword::reprint/$1');
    $routes->match(['get', 'post'], 'math-crossword/assign', 'MathCrossword::assign');
    $routes->get('math-crossword/report/(:num)', 'MathCrossword::report/$1');
    $routes->get('math-crossword/vocab-topics', 'MathCrossword::vocabTopicsAjax');
    $routes->get('math-crossword/assignments-ajax', 'MathCrossword::assignmentsAjax');

    $routes->get('math-worksheet', 'MathWorksheet::index');
    $routes->post('math-worksheet/generate', 'MathWorksheet::generate');
    $routes->get('math-worksheet/library', 'MathWorksheet::library');
    $routes->get('math-worksheet/reprint/(:num)', 'MathWorksheet::reprint/$1');

    $routes->get('word-search', 'WordSearch::index');
    $routes->post('word-search/generate', 'WordSearch::generate');
    $routes->get('word-search/library', 'WordSearch::library');
    $routes->get('word-search/reprint/(:num)', 'WordSearch::reprint/$1');
    $routes->match(['get', 'post'], 'word-search/assign', 'WordSearch::assign');
    $routes->get('word-search/report/(:num)', 'WordSearch::report/$1');
    $routes->get('word-search/vocab-topics', 'WordSearch::vocabTopicsAjax');
    $routes->get('word-search/assignments-ajax', 'WordSearch::assignmentsAjax');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {

    // ===============================
    // Question Bank – Topics Manager
    // ===============================
    $routes->get('qb-topics', 'QbTopics::index');          // page
    $routes->get('qb-topics/data', 'QbTopics::data');      // ajax load
    $routes->post('qb-topics/save', 'QbTopics::saveBulk'); // ajax save

    $routes->get('qb-board-publishers', 'QbBoardPublishers::index');
    $routes->get('qb-board-publishers/list', 'QbBoardPublishers::listJson');
    $routes->post('qb-board-publishers/save', 'QbBoardPublishers::save');
    $routes->post('qb-board-publishers/delete/(:num)', 'QbBoardPublishers::delete/$1');

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
    $routes->get('profile/edit', 'Profile::edit');
    $routes->get('profile/editself', 'Profile::editself');
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
