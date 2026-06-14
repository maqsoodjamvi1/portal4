<?php

/**
 * Legacy admin entry points (prefer explicit routes in Routes.php).
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

/** @deprecated Use named routes under admin/* instead of ?c=&m= */
$routes->get('admin', 'AdminDispatcher::route');

$routes->get('admin/', 'Admin\Dashboard::index');
$routes->match(['get', 'post'], 'admin/(:segment)/(:segment)', 'Admin\Fallback::index');
$routes->match(['get', 'post'], 'admin/(:segment)/(:segment)/(:any)', 'Admin\Fallback::index');
$routes->match(['get', 'post'], 'admin/(:segment)', 'Admin\Fallback::index');
