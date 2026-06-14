<?php

namespace Config;

use CodeIgniter\Router\Router;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();
$routes->get('/', 'Home::index');

// Default settings (fallback must not serve welcome_message)
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);


// Domain route files (split from monolithic Routes.php — load order matters)
helper('board_prep');

if (board_prep_is_prep_subdomain()) {
    // prep.timesoftsol.com — board prep only at /signup, /login, etc.
    $boardPrepUsePathPrefix = false;
    require __DIR__ . '/Routes/BoardPrep.php';
} else {
    require __DIR__ . '/Routes/PublicRoutes.php';

    // Board prep under /prep/* (register early so it is never skipped)
    $boardPrepUsePathPrefix = true;
    require __DIR__ . '/Routes/BoardPrep.php';
    require __DIR__ . '/Routes/AdminPriority.php';
    require __DIR__ . '/Routes/AdminHealthSalary.php';
    require __DIR__ . '/Routes/AdminUsersRoles.php';
    require __DIR__ . '/Routes/AdminAcademicSetup.php';
    require __DIR__ . '/Routes/AdminClasses.php';
    require __DIR__ . '/Routes/AdminStudents.php';
    require __DIR__ . '/Routes/AdminFees.php';
    require __DIR__ . '/Routes/AdminExams.php';
    require __DIR__ . '/Routes/AdminAttendance.php';
    require __DIR__ . '/Routes/AdminCampusFinance.php';
    require __DIR__ . '/Routes/AdminQuizzes.php';
    require __DIR__ . '/Routes/AdminSports.php';
    require __DIR__ . '/Routes/AdminMisc.php';

    require __DIR__ . '/Routes/AdminHifz.php';
    require __DIR__ . '/Routes/AdminReports.php';
    require __DIR__ . '/Routes/AdminBilling.php';

    // Legacy dispatcher + admin/(:segment) fallback — must load after explicit admin routes
    require __DIR__ . '/Routes/AdminLegacy.php';

    $routes->get('media/qb/(:any)', 'Media::qbQuestionImage/$1');

    require __DIR__ . '/Routes/StudentPortal.php';
}
