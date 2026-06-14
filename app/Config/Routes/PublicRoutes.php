<?php

/**
 * PublicRoutes
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
// When public/uploads is not web-writable, student photos are stored under writable/uploads/student_profiles;
// this route serves /uploads/{file} from either location (static public file wins before PHP on most servers).
$routes->get('uploads/(:segment)', 'UploadsProxy::serve/$1');

// Public trial signup + captcha (no admin auth)
$routes->get('signup', 'TrialSignup::index');
$routes->post('signup/submit', 'TrialSignup::submit', ['filter' => 'csrf']);
$routes->get('signup/verify', 'TrialSignup::verify');
$routes->post('signup/verify', 'TrialSignup::verifySubmit', ['filter' => 'csrf']);
$routes->post('signup/resend', 'TrialSignup::resend', ['filter' => 'csrf']);
$routes->get('signup/success', 'TrialSignup::success');
$routes->get('api/captcha', 'Api\Captcha::index');
