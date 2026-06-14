<?php

/**
 * AdminFees
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
// Fee Chalan Routes
// Group all admin routes

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    $routes->get('fee-chalan', 'FeeChalan::index');

    // New unified chalan generator (GET = browser / links; POST = AJAX from student profile, etc.)
    $routes->get('fee-chalan/generate', 'FeeChalan::generate');
    $routes->post('fee-chalan/generate', 'FeeChalan::generate');

    // AJAX search endpoints
    $routes->post('fee-chalan/search-students', 'FeeChalan::searchStudents');
    $routes->post('fee-chalan/search-families', 'FeeChalan::searchFamilies');
    $routes->get('fee-chalan/get-sections', 'FeeChalan::getSections');
    $routes->post('fee-chalan/get-edit-form', 'FeeChalan::getEditForm');
$routes->post('fee-chalan/save-edit', 'FeeChalan::saveEdit');

     $routes->get('fee-chalan/get-sections-by-class', 'FeeChalan::getSectionsByClass');
      $routes->post('fee-chalan/bulk_chalan_generation', 'FeeChalan::bulkChalanGeneration');
   //  $routes->get('fee-chalan/bulk_chalan_stream', 'FeeChalan::bulkChalanStream');
     $routes->get('fee-chalan/bulk_chalan_stream', 'FeeChalan::bulk_chalan_stream');
       $routes->get('fee-chalan/add', 'FeeChalan::add');
       $routes->post('fee-chalan/delete', 'FeeChalan::delete');
$routes->post('fee-chalan/delete-all-today', 'FeeChalan::deleteAllToday');
    // Keep existing routes for backward compatibility
    $routes->get('fee-chalan/pdf', 'FeeChalan::threeCopyPdf');
    $routes->get('fee-chalan/thermal-copy', 'FeeChalan::thermalCopy');
    $routes->get('fee-chalan/single-copy', 'FeeChalan::singleCopy');
    $routes->get('fee-chalan/without-discount', 'FeeChalan::withoutDiscount');
    $routes->get('fee-chalan/familywise', 'FeeChalan::familywise');
    $routes->get('fee-chalan/familywise/single-copy', 'FeeChalan::familywiseSingleCopy');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    $routes->get('print-fee-chalan', 'PrintFeeChalan::index');

     $routes->post('print-fee-chalan/data',           'PrintFeeChalan::data',             ['as' => 'print_fee_chalan_data']); // ✅ fixed path
    $routes->get('print-fee-chalan/add', 'FeeChalan::add');
    $routes->get('print-fee-chalan/thermal-copy', 'PrintFeeChalan::thermalCopy');
    $routes->get('print-fee-chalan/single-copy', 'PrintFeeChalan::singleCopy');
    $routes->get('print-fee-chalan/pdf', 'PrintFeeChalan::threeCopyPdf');
    $routes->get('print-fee-chalan/without-discount', 'PrintFeeChalan::withoutDiscount');
    $routes->post('print-fee-chalan/set-default-template', 'PrintFeeChalan::setDefaultTemplate');
    $routes->get('print-fee-chalan/familywise', 'PrintFeeChalan::familywise');
    $routes->get('print-fee-chalan/familywise/single-copy', 'PrintFeeChalan::familywiseSingleCopy');
    $routes->get('print-fee-chalan/with-header', 'PrintFeeChalan::withHeader');
});





$routes->post('admin/fee-chalan-pay/getStudentCardAjax', 'Admin\FeeChalanPay::getStudentCardAjax');
$routes->post('admin/fee-chalan-pay1/getStudentCardAjax', 'Admin\FeeChalanPay1::getStudentCardAjax');
$routes->get('admin/fee-chalan-pay', 'Admin\FeeChalanPay::index');
$routes->get('admin/advance-fee', 'Admin\FeeChalanPay::advanceFee');
$routes->post('admin/advance-fee/save', 'Admin\FeeChalanPay::saveAdvanceBalances');
$routes->get('admin/fee-chalan-pay/advance-fee', 'Admin\FeeChalanPay::advanceFee');
$routes->post('admin/fee-chalan-pay/advance-fee-save', 'Admin\FeeChalanPay::saveAdvanceBalances');
$routes->get('admin/fee-chalan-pay1', 'Admin\FeeChalanPay1::index');
$routes->post('admin/fee-chalan-pay/get-studentinfo', 'Admin\FeeChalanPay::get_studentinfo');
$routes->post('admin/fee-chalan-pay/get-student-info', 'Admin\FeeChalanPay::get_studentinfo');
$routes->post('admin/fee-chalan-pay1/get-studentinfo', 'Admin\FeeChalanPay1::get_studentinfo');


$routes->post('admin/fee-chalan-pay/get-chalans', 'Admin\FeeChalanPay::get_chalans');
$routes->post('admin/fee-chalan-pay1/get-chalans', 'Admin\FeeChalanPay1::get_chalans');

$routes->post('admin/fee-chalan-pay/generateStudentFeeCard', 'Admin\FeeChalanPay::generate-student-fee-card');
$routes->post('admin/fee-chalan-pay1/generateStudentFeeCard', 'Admin\FeeChalanPay1::generate-student-fee-card');
// FeeChalanPay Controller Routes


$routes->post('admin/fee-chalan-pay/markFeeAsPaid', 'Admin\FeeChalanPay::markFeeAsPaid');
$routes->post('admin/fee-chalan-pay/mark-multiple-fees-as-paid', 'Admin\FeeChalanPay::markMultipleFeesAsPaid');
$routes->get('admin/fee-chalan-pay/finance-accounts', 'Admin\CampusFinanceAccounts::accountsJson');

$routes->post('admin/fee-chalan-pay/addPartialFeeToPool', 'Admin\FeeChalanPay::addPartialFeeToPool');
$routes->post('admin/fee-chalan-pay/get-unpaid-fees', 'Admin\FeeChalanPay::getUnpaidFees');
$routes->post('admin/fee-chalan-pay/get-family-unpaid-fees', 'Admin\FeeChalanPay::getFamilyUnpaidFees');
$routes->post('admin/fee-chalan-pay/get-family-fee-history', 'Admin\FeeChalanPay::getFamilyFeeHistory');
$routes->post('admin/fee-chalan-pay/get-parent-fee-summary', 'Admin\FeeChalanPay::getParentFeeSummary');
$routes->post('admin/fee-chalan-pay/get-monthly-paid-fees', 'Admin\FeeChalanPay::getMonthlyPaidFees');
$routes->post('admin/fee-chalan-pay/make-unpaid', 'Admin\FeeChalanPay::makeUnpaid');

$routes->post('admin/fee-chalan-pay/getAdvanceFeeStudentsAjax', 'Admin\FeeChalanPay::getAdvanceFeeStudentsAjax');
$routes->post('admin/fee-chalan-pay/saveAdvanceFee', 'Admin\FeeChalanPay::saveAdvanceFee');

$routes->post('admin/fee-chalan-pay/markSingleFeeAsPaid', 'Admin\FeeChalanPay::markSingleFeeAsPaid');
$routes->group('admin/fee-chalan-pay', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->post('data', 'FeeChalanPay::data'); // DataTable listing
    $routes->get('advance-fee', 'FeeChalanPay::advanceFee');
    $routes->post('advance-fee-save', 'FeeChalanPay::saveAdvanceBalances');

    $routes->get('add', 'FeeChalanPay::add'); // Add form
    $routes->get('edit/(:num)', 'FeeChalanPay::edit/$1'); // Edit form
    $routes->post('get-students-list', 'FeeChalanPay::get_students_list'); // Get student fee view (HTML)
    $routes->post('adv-fee', 'FeeChalanPay::advFee'); // Advance fee payment
    $routes->post('pay-fee-all', 'FeeChalanPay::payFeeAll'); // Pay all unpaid fee for a parent
    $routes->post('update-paid-fee', 'FeeChalanPay::updatePaidFee'); // Mark fee as unpaid again
    $routes->post('send-sms', 'FeeChalanPay::sendSMS'); // Send SMS on fee payment
    $routes->post('updateStudentDiscount', 'FeeChalanPay::updateStudentDiscount');
    $routes->post('get-parent-info', 'FeeChalanPay::get_parentinfo'); // Select2 parent info
});


$routes->group('admin/fee-chalan-pay1', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    $routes->post('data', 'FeeChalanPay1::data'); // DataTable listing
    $routes->get('add', 'FeeChalanPay1::add'); // Add form
    $routes->get('edit/(:num)', 'FeeChalanPay::edit/$1'); // Edit form
    $routes->post('get-students-list', 'FeeChalanPay::get_students_list'); // Get student fee view (HTML)
    $routes->post('adv-fee', 'FeeChalanPay::advFee'); // Advance fee payment
    $routes->post('pay-fee-all', 'FeeChalanPay::payFeeAll'); // Pay all unpaid fee for a parent
    $routes->post('update-paid-fee', 'FeeChalanPay::updatePaidFee'); // Mark fee as unpaid again
    $routes->post('send-sms', 'FeeChalanPay::sendSMS'); // Send SMS on fee payment
    $routes->post('updateStudentDiscount', 'FeeChalanPay::updateStudentDiscount');
    $routes->post('get-parent-info', 'FeeChalanPay::get_parentinfo'); // Select2 parent info
});


// Legacy alias: fee-chalan-pay2 pointed at removed FeeChalanPay2 controller — map to FeeChalanPay
$routes->group('admin/fee-chalan-pay2', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'FeeChalanPay::index');
    $routes->post('data', 'FeeChalanPay::data');
    $routes->get('add', 'FeeChalanPay::add');
    $routes->get('edit/(:num)', 'FeeChalanPay::edit/$1');
    $routes->post('get-students-list', 'FeeChalanPay::get_students_list');
    $routes->post('adv-fee', 'FeeChalanPay::advFee');
    $routes->post('pay-fee-all', 'FeeChalanPay::payFeeAll');
    $routes->post('update-paid-fee', 'FeeChalanPay::updatePaidFee');
    $routes->post('send-sms', 'FeeChalanPay::sendSMS');
    $routes->post('get-student-info', 'FeeChalanPay::get_studentinfo');
    $routes->post('get-parent-info', 'FeeChalanPay::get_parentinfo');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('fee-chalan-balance', 'FeeChalanBalance::index');
    $routes->post('fee-chalan-balance/get-total-fee', 'FeeChalanBalance::getTotalfee');
    $routes->post('fee-chalan-balance/get-total-fee-by-month', 'FeeChalanBalance::getTotalfeebymonth');
    $routes->get('fee-chalan-daily-collection', 'FeeChalanBalance::dailyCollection');
    $routes->post('fee-chalan-daily-collection/data', 'FeeChalanBalance::getDailyCollection');
    $routes->get('advance-fee', 'FeeChalanPay::advanceFee');
    $routes->post('advance-fee/save', 'FeeChalanPay::saveAdvanceBalances');
});
