<?php

/**
 * AdminQuizzes
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('quizzes', 'Quizzes::index');
    $routes->get('quizzes/create', 'Quizzes::create');
    $routes->post('quizzes/store', 'Quizzes::store');
    $routes->get('quizzes/create-board-prep', 'Quizzes::createBoardPrep');
    $routes->post('quizzes/store-board-prep', 'Quizzes::storeBoardPrep');
    $routes->post('quizzes/store-board-prep-bulk', 'Quizzes::storeBoardPrepBulk');

    $routes->get('quizzes/(:num)/results', 'Quizzes::results/$1');
    $routes->get('quizzes/(:num)/review', 'Quizzes::review/$1');
    $routes->get('quizzes/class-results/(:num)', 'Quizzes::classResults/$1');
    $routes->get('quizzes/class-summary/(:num)', 'Quizzes::classSummary/$1');
    $routes->get('quizzes/class-export/(:num)', 'Quizzes::classExport/$1');
    $routes->get('quizzes/edit/(:num)', 'Quizzes::edit/$1');
    $routes->get('quizzes/(:num)/edit', 'Quizzes::edit/$1');
    $routes->post('quizzes/update/(:num)', 'Quizzes::update/$1');
    $routes->get('quizzes/exam-marks-report', 'Quizzes::examMarksReport');
    $routes->get('quizzes/ajax/exams-current-term', 'Quizzes::ajaxExamsForCurrentTerm');
    $routes->get('quizzes/ajax/exam-marks-matrix', 'Quizzes::ajaxExamMarksMatrix');

    $routes->group('quizzes/ajax', static function ($routes) {
        $routes->get('class-sections', 'Quizzes::ajaxClassSections');
        $routes->get('section-subjects/(:num)', 'Quizzes::ajaxSectionSubjects/$1');
        $routes->get('by-filters', 'Quizzes::ajaxByFilters');
        $routes->get('terms', 'Quizzes::ajaxTermsBySession');
        $routes->get('qb-subjects', 'Quizzes::ajaxQbSubjects');
        $routes->get('qb-topics', 'Quizzes::ajaxQbTopics');
        $routes->get('qb-summary', 'Quizzes::ajaxQbSummary');
        $routes->get('board-prep-subjects', 'Quizzes::ajaxBoardPrepSubjects');
        $routes->get('board-prep-topics', 'Quizzes::ajaxBoardPrepTopics');
        $routes->post('qb-questions', 'Quizzes::ajaxQbQuestions');
    });

    $routes->get('quizzes/ajaxQbTopicsBySecSub/(:num)', 'Quizzes::ajaxQbTopicsBySecSub/$1');
    $routes->get('quizzes/ajaxQbQuestionsBySecSub/(:num)', 'Quizzes::ajaxQbQuestionsBySecSub/$1');
    $routes->post('quizzes/ajaxQbQuestionsBySecSub/(:num)', 'Quizzes::ajaxQbQuestionsBySecSub/$1');
});
