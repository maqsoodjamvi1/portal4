<?php

/**
 * AdminClasses
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
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
