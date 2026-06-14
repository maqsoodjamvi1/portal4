<?php



/**

 * Board prep portal routes (prep.timesoftsol.com or /prep/* on portal4).

 *

 * @var \CodeIgniter\Router\RouteCollection $routes

 */



helper('board_prep');



$cfg = board_prep_config();



$usePathPrefix = ($boardPrepUsePathPrefix ?? false)

    || ($cfg->enablePathPrefix && ! board_prep_is_prep_subdomain());



// CI4 applies the URL prefix via group() first argument — not options['prefix'].

$groupName = $usePathPrefix ? trim($cfg->pathPrefix, '/') : '';



$groupOptions = ['namespace' => 'App\\Controllers\\BoardPrep'];



$routes->group($groupName, $groupOptions, static function ($routes) {

    $routes->get('/', 'Auth::landing');

    $routes->get('login', 'Auth::login');

    $routes->post('login', 'Auth::doLogin', ['filter' => 'csrf']);

    $routes->get('logout', 'Auth::logout');

    $routes->get('signup', 'Signup::index');

    $routes->post('signup/submit', 'Signup::submit', ['filter' => 'csrf']);

    $routes->get('api/captcha', '\App\Controllers\Api\Captcha::index');

});



$authGroup = array_merge($groupOptions, ['filter' => 'boardprepauth']);

$routes->group($groupName, $authGroup, static function ($routes) {

    $routes->get('dashboard', 'Dashboard::index');

    $routes->get('profile', 'Profile::index');
    $routes->post('profile/update', 'Profile::update', ['filter' => 'csrf']);

    $routes->get('quizzes', 'QuizCatalog::index');

    $routes->get('quizzes/start/(:num)', 'Quizzes::start/$1');

    $routes->get('quizzes/practice/(:num)', 'Quizzes::practice/$1');

    $routes->post('quizzes/save-answer', 'Quizzes::saveAnswer', ['filter' => 'csrf']);

    $routes->post('quizzes/submit', 'Quizzes::submit', ['filter' => 'csrf']);

    $routes->get('quizzes/review/(:num)', 'Quizzes::review/$1');

    $routes->get('quizzes/complete/(:num)', 'Quizzes::complete/$1');

    $routes->get('quizzes/results/(:num)', 'Quizzes::results/$1');

    $routes->post('quizzes/submit-level', 'Quizzes::submitLevel', ['filter' => 'csrf']);

    $routes->post('quizzes/move-to-next-level', 'Quizzes::moveToNextLevel', ['filter' => 'csrf']);

    $routes->post('quizzes/retry-current-level', 'Quizzes::retryCurrentLevel', ['filter' => 'csrf']);

    $routes->post('quizzes/complete-adaptive-quiz', 'Quizzes::completeAdaptiveQuiz', ['filter' => 'csrf']);

    $routes->get('quizzes/adaptive/(:num)', 'Quizzes::startAdaptive/$1');



    $routes->get('results', 'Results::index');

    $routes->get('results/subjects', 'Results::subjects');

    $routes->get('results/quiz/(:num)', 'Results::quiz/$1');

});
