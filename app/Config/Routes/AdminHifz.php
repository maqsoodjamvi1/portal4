<?php

/**
 * Hifz Quran Program routes (loaded from Config/Routes.php).
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

$routes->group('admin/hifz', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('sections', 'HifzSections::index');
    $routes->post('sections/data', 'HifzSections::data');
    $routes->get('sections/add', 'HifzSections::add');
    $routes->get('sections/edit', 'HifzSections::edit');
    $routes->post('sections/save', 'HifzSections::save');
    $routes->post('sections/bulk-save', 'HifzSections::bulkSave');
    $routes->post('sections/toggle-status', 'HifzSections::toggleStatus');

    $routes->get('students', 'HifzStudents::index');
    $routes->post('students/data', 'HifzStudents::data');
    $routes->post('students/save', 'HifzStudents::save');
    $routes->post('students/withdraw', 'HifzStudents::withdraw');
    $routes->post('students/move-section', 'HifzStudents::moveSection');

    $routes->get('teachers', 'HifzTeachers::index');
    $routes->post('teachers/data', 'HifzTeachers::data');
    $routes->post('teachers/save', 'HifzTeachers::save');
    $routes->get('recitation', 'HifzRecitation::index');
    $routes->post('recitation/load', 'HifzRecitation::load');
    $routes->post('recitation/save', 'HifzRecitation::save');
    $routes->post('recitation/sabqi-remove-para', 'HifzRecitation::sabqiRemovePara');
    $routes->get('reports', 'HifzReports::index');
    $routes->post('reports/section-data', 'HifzReports::sectionData');
    $routes->post('reports/student-data', 'HifzReports::studentData');
    $routes->post('reports/export-section', 'HifzReports::exportSectionCsv');
});
