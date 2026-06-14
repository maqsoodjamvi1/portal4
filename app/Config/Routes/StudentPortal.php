<?php

/**
 * Parent / student portal routes (loaded from Config/Routes.php).
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

$routes->group('student', ['namespace' => 'App\Controllers\Frontend'], static function ($routes) {
    $routes->get('login', 'Auth::showLogin', ['as' => 'login']);
    $routes->post('login', 'Auth::doLogin', ['as' => 'login.post']);
    $routes->get('logout', 'Auth::logout', ['as' => 'logout']);
});

$routes->group('student', ['namespace' => 'App\Controllers\Frontend', 'filter' => 'portalauth'], static function ($routes) {
    $routes->get('fees', 'Fees::index', ['as' => 'fees.index']);
    $routes->get('results', 'Results::index', ['as' => 'results.index']);
    $routes->get('attendance', 'Attendance::index', ['as' => 'attendance.index']);
    $routes->get('attendance/term-weeks/(:num)', 'Attendance::termWeeks/$1');
    $routes->get('events', 'Events::index', ['as' => 'events.index']);
    $routes->get('datesheet', 'DatesheetController::index');
    $routes->post('datesheet/switch/(:num)', 'DatesheetController::switchStudent/$1');
    $routes->get('vocabbank/data', 'VocabBank::getVocabularyData');
    $routes->get('vocabbank', 'VocabBank::index');

    $routes->get('dashboard/section/(:segment)', 'Dashboard::parentSection/$1');
    $routes->get('dashboard', 'Dashboard::index', ['as' => 'dashboard']);
    $routes->get('switch/(:num)', 'Dashboard::switchStudent/$1', ['as' => 'switch.student']);
    $routes->post('upload-audio', 'Dashboard::uploadAudioRecording');
    $routes->post('upload-video', 'Dashboard::uploadVideoRecording');
    $routes->post('upload-picture', 'Dashboard::uploadPicture');
    $routes->get('classdiary', 'Dashboard::classDiary', ['as' => 'classdiary']);
    $routes->get('diary/by-date', 'Dashboard::getDiaryByDate');
    $routes->get('get-prayer-status', 'Dashboard::getPrayerStatus');
    $routes->get('get-prayer-week', 'Dashboard::getPrayerWeekStatus');
    $routes->post('save-prayer', 'Dashboard::savePrayer');
    $routes->get('get-prayer-stats', 'Dashboard::getPrayerStats');
    $routes->get('get-bmi-data/(:num)', 'Dashboard::getBmiData/$1');
    $routes->get('get-bmi-history/(:num)', 'Dashboard::getBmiHistory/$1');
    $routes->get('get-bmi-suggestions/(:any)', 'Dashboard::getBmiSuggestions/$1');
    $routes->get('profile', 'PortalProfile::index', ['as' => 'portal.profile']);

    $routes->get('quizzes/all', 'StudentQuizCatalog::index');
    $routes->get('quizzes/my-results', 'StudentQuizCatalog::results');
    $routes->get('quizzes', 'Quizzes::index');
    $routes->get('quizzes/start/(:num)', 'Quizzes::start/$1');
    $routes->get('quizzes/practice/(:num)', 'Quizzes::practice/$1');
    $routes->post('quizzes/save-answer', 'Quizzes::saveAnswer');
    $routes->post('quizzes/submit', 'Quizzes::submit');
    $routes->get('quizzes/review/(:num)', 'Quizzes::review/$1');
    $routes->get('quizzes/results/(:num)', 'Quizzes::results/$1');
    $routes->get('quizzes/pending', 'PendingQuizzes::index');
    $routes->get('quizzes/attempted', 'AttemptedQuizzes::index');
    $routes->post('quizzes/submit-level', 'Quizzes::submitLevel');
    $routes->post('quizzes/move-to-next-level', 'Quizzes::moveToNextLevel');
    $routes->post('quizzes/retry-current-level', 'Quizzes::retryCurrentLevel');
    $routes->post('quizzes/complete-adaptive-quiz', 'Quizzes::completeAdaptiveQuiz');
    $routes->get('quizzes/adaptive/(:num)', 'Quizzes::startAdaptive/$1');

    $routes->get('crossword', 'Crossword::index');
    $routes->get('crossword/play/(:num)', 'Crossword::play/$1');
    $routes->post('crossword/submit', 'Crossword::submit');
    $routes->get('crossword/result/(:num)', 'Crossword::result/$1');

    $routes->get('word-search', 'WordSearch::index');
    $routes->get('word-search/play/(:num)', 'WordSearch::play/$1');
    $routes->post('word-search/submit', 'WordSearch::submit');
    $routes->get('word-search/result/(:num)', 'WordSearch::result/$1');

    $routes->get('battles', 'Battles::index');
    $routes->get('battles/data', 'Battles::data');
    $routes->post('battles/create', 'Battles::create');
    $routes->post('battles/accept', 'Battles::accept');
    $routes->post('battles/decline', 'Battles::decline');
    $routes->get('battles/play/(:num)', 'Battles::play/$1');
    $routes->post('battles/submit/(:num)', 'Battles::submit/$1');
    $routes->get('battles/view/(:num)', 'Battles::view/$1');
});
