<?php

/**
 * AdminHealthSalary
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->get('language/set/(:any)', 'LanguageController::set/$1');
$routes->get('language/set', 'LanguageController::set');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Salary Settings


    // ==================== HEALTH & BMI MODULE ====================

    // BMI Dashboard & Overview
    $routes->get('health/bmi-dashboard', 'HealthBmi::dashboard');
    $routes->get('health/bmi-stats-data', 'HealthBmi::getStatsData');
    $routes->post('process-teacher-attendance', 'Dashboard::processTeacherAttendance');
$routes->get('get-recent-attendance', 'Dashboard::getRecentAttendance');

      $routes->get('health/growth-charts', 'HealthBmi::growthCharts');
    $routes->get('health/growth-charts/data/(:num)', 'HealthBmi::getGrowthChartData/$1');


    // BMI Records Management
    $routes->get('health/bmi-records', 'HealthBmi::records');
    $routes->get('health/bmi-records/data', 'HealthBmi::getRecordsData');
    $routes->post('health/bmi-records/save', 'HealthBmi::saveRecord');
    $routes->get('health/bmi-records/delete/(:num)', 'HealthBmi::deleteRecord/$1');

    // Add these lines to your Routes.php
$routes->post('health/bmi-records/getRecordsData', 'HealthBmi::getRecordsData');
$routes->post('health/bmi-records/saveRecord', 'HealthBmi::saveRecord');
$routes->post('health/bmi-records/data', '\HealthBmi::getRecordsData'); // For backward compatibility

    // Bulk BMI Update (existing route)


    // Health Alerts
    $routes->get('health/alerts', 'HealthBmi::alerts');
    $routes->get('health/alerts/data', 'HealthBmi::getAlertsData');
    $routes->post('health/alerts/mark-read/(:num)', 'HealthBmi::markAlertRead/$1');
    $routes->post('health/alerts/mark-all-read', 'HealthBmi::markAllAlertsRead');
    $routes->get('health/alerts/send-notifications', 'HealthBmi::sendAlertNotifications');

    // Nutrition Suggestions
    $routes->get('health/nutrition-suggestions', 'HealthBmi::nutritionSuggestions');
    $routes->get('health/nutrition-suggestions/data', 'HealthBmi::getNutritionSuggestionsData');
    $routes->post('health/nutrition-suggestions/add', 'HealthBmi::addNutritionSuggestion');
    $routes->post('health/nutrition-suggestions/update/(:num)', 'HealthBmi::updateNutritionSuggestion/$1');
    $routes->post('health/nutrition-suggestions/delete/(:num)', 'HealthBmi::deleteNutritionSuggestion/$1');

    // BMI Reports
    $routes->get('health/bmi-reports', 'HealthBmi::reports');
    $routes->post('health/bmi-reports/generate', 'HealthBmi::generateReport');
    $routes->get('health/bmi-reports/export-excel', 'HealthBmi::exportExcel');
    $routes->get('health/bmi-reports/export-pdf', 'HealthBmi::exportPdf');

    // Growth Charts
    $routes->get('health/growth-charts', 'HealthBmi::growthCharts');
    $routes->get('health/growth-charts/data/(:num)', 'HealthBmi::getGrowthChartData/$1');

    // Student BMI (for student profile)
    $routes->post('students/update-bmi', 'HealthBmi::updateStudentBmi');
    $routes->get('students/bmi-history/(:num)', 'HealthBmi::getBmiHistory/$1');


    $routes->post('students/get_outstanding_fee', 'Students::get_outstanding_fee');
$routes->post('students/process_fee_payment', 'Students::process_fee_payment');
$routes->post('students/mark_fee_paid', 'Students::mark_fee_paid');
$routes->post('students/update_basicinfo', 'Students::update_basicinfo');
    // Class-wise BMI Report
    $routes->get('health/class-bmi-report', 'HealthBmi::classBmiReport');
    $routes->post('health/class-bmi-report/data', 'HealthBmi::getClassBmiReportData');

    // BMI Statistics API (for dashboard widget)
    $routes->get('health/bmi-statistics', 'HealthBmi::getStatistics');

    // Individual Student BMI View
    $routes->get('students/bmi-view/(:num)', 'HealthBmi::viewStudentBmi/$1');
    $routes->post('students/bmi-add-record/(:num)', 'HealthBmi::addBmiRecord/$1');

    // Bulk Import BMI Data via Excel
    $routes->get('health/bmi-bulk-import', 'HealthBmi::bulkImport');
    $routes->post('health/bmi-bulk-import/upload', 'HealthBmi::uploadBulkBmi');
    $routes->get('health/bmi-bulk-import/download-template', 'HealthBmi::downloadTemplate');
});
