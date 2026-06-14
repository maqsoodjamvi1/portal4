<?php

/**
 * AdminMisc
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

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



// Student portal routes: see Config/Routes/StudentPortal.php (loaded at end of this file).

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

     $routes->post('quizzes/(:num)/toggle-published', '\App\Controllers\Admin\Quizzes::togglePublished/$1');

     $routes->post('quizzes/update-single-question', 'Quizzes::updateSingleQuestion');

     $routes->post('quizzes/delete-question', 'Quizzes::deleteQuestion');


     $routes->get('quizzes/edit/(:num)', 'Quizzes::edit/$1');
     $routes->get('quizzes/(:num)/edit', 'Quizzes::edit/$1');
     $routes->post('quizzes/update/(:num)', 'Quizzes::update/$1');
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
$routes->get('quiz/review/(:num)', 'Frontend\Quizzes::review/$1');
$routes->post('quiz/submit', 'Frontend\Quizzes::submit');
$routes->post('quiz/save-answer', 'Frontend\Quizzes::saveAnswer');
$routes->post('quiz/submit-level', 'Frontend\Quizzes::submitLevel');
$routes->post('quiz/move-to-next-level', 'Frontend\Quizzes::moveToNextLevel');
$routes->post('quiz/retry-current-level', 'Frontend\Quizzes::retryCurrentLevel');
$routes->post('quiz/complete-adaptive-quiz', 'Frontend\Quizzes::completeAdaptiveQuiz');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('quiz-assign', 'QuizAssign::index');
    $routes->get('load_subjects/(:num)', 'QuizAssign::load_subjects/$1');

    $routes->get('load_quizzes_by_clssec/(:num)', 'QuizAssign::load_quizzes_by_clssec/$1');

    $routes->get('load_quizzes/(:num)', 'QuizAssign::load_quizzes/$1');
    $routes->post('generate-link', 'QuizAssign::generateImpersonationLink');
    $routes->post('reset-quiz-attempt', 'QuizAssign::resetQuizAttempt');
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
// Student battles: Config/Routes/StudentPortal.php



/*
|--------------------------------------------------------------------------
| Admin – Quiz Battles (Read Only)
|--------------------------------------------------------------------------
*/
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'adminpermission'], function ($routes) {
    $routes->get('quiz-battles', 'QuizBattles::index');
    $routes->get('quiz-battles/view/(:num)', 'QuizBattles::view/$1');
});
