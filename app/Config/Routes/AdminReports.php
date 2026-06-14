<?php

/**
 * Admin report routes (extracted from Routes.php for maintainability).
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('student-daily-report', 'StudentDailyReport::index');
    $routes->post('student-daily-report/data', 'StudentDailyReport::data');
    $routes->post('student-daily-report/export', 'StudentDailyReport::exportCsv');

    $routes->get('class-section-strength-report', 'ClassSectionStrengthReport::index');
    $routes->post('class-section-strength-report/data', 'ClassSectionStrengthReport::data');
    $routes->post('class-section-strength-report/export', 'ClassSectionStrengthReport::exportCsv');

    $routes->get('profit-loss-report', 'ProfitLossReport::index', ['as' => 'profit_loss_index']);
    $routes->post('profit-loss-report/daily', 'ProfitLossReport::getDailyCollection', ['as' => 'profit_loss_daily']);
    $routes->get('profit_loss_report', 'ProfitLossReport::index');
    $routes->post('profit_loss_report/get_daily_collection', 'ProfitLossReport::getDailyCollection');
    $routes->post('profit_loss_report/monthly-summary', 'ProfitLossReport::getMonthlySummary');
    $routes->post('profit-loss-report/monthly-summary', 'ProfitLossReport::getMonthlySummary');
    $routes->post('profit-loss-report/export', 'ProfitLossReport::exportCsv');
    $routes->post('profit_loss_report/export', 'ProfitLossReport::exportCsv');

    $routes->post('fee-reminders/run', 'FeeReminders::run');
});
