<?php

/**
 * AdminSports
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
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


$routes->group('admin/sports/results', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'SportsResults::index');
    $routes->post('list-events', 'SportsResults::listEvents');
    $routes->post('rows', 'SportsResults::rows');
    $routes->post('set-position', 'SportsResults::setPosition');
    $routes->post('clear-position', 'SportsResults::clearPosition');
});
