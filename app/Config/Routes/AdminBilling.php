<?php



/**

 * Billing & plan admin (legacy snake_case URLs → PascalCase controllers).

 *

 * Route targets must match real class names (Linux is case-sensitive).

 *

 * @var \CodeIgniter\Router\RouteCollection $routes

 */



use Config\AdminControllerPermissions;



$billingSegments = [

    'bill_type',

    'bill_amount',

    'bill_plan_months',

    'campus_chalan_pay',

    'pay_campus_bill',

    'campus_plans',

    'pay_system_bill',

    'ci_session_view',

];



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) use ($billingSegments): void {

    foreach ($billingSegments as $segment) {

        $class = AdminControllerPermissions::resolveLegacyControllerClass($segment);

        if ($class === null) {

            log_message('error', 'AdminBilling: no controller for segment {segment}', ['segment' => $segment]);



            continue;

        }



        $handler = class_basename($class);



        $routes->get($segment, $handler . '::index');

        $routes->post($segment, $handler . '::index');

        $routes->get($segment . '/(:segment)', $handler . '::$1');

        $routes->post($segment . '/(:segment)', $handler . '::$1');

    }



    // Plan management (CI4 controllers — explicit so menu links never hit Fallback 404)

    $routes->get('roles', 'Roles::index');

    $routes->get('permissions', 'Permissions::index');

    $routes->get('campus-management', 'CampusManagement::index');

    $routes->get('campus-settings', 'CampusManagement::settings');

});
