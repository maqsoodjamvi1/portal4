<?php

namespace Config;

use CodeIgniter\Router\Router;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();

// ===============================
// LOCALIZATION SETUP
// ===============================
 
// Language switcher route
$routes->get('language/switch/(:any)', 'Language::switch/$1');

// Default settings
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// ===============================
// LOCALE-BASED ROUTES
// ===============================
$routes->group('{locale}', ['filter' => 'locale'], function($routes) {

    $routes->get('test-locale', function () {
        return 'Locale: ' . service('request')->getLocale();
    }, ['filter' => 'locale']);
    
    // Admin routes with locale prefix
    $routes->get('admin', 'Home::getAdmin');
    
    $routes->group('admin', function($routes) {
        $routes->post('login/changePassword', 'Admin\Login::changePassword');
        $routes->post('users/editPassword', 'Admin\Users::editPassword');
        $routes->post('calendar/save', 'Admin\Calendar::save');
        $routes->post('login/findPassword', 'Admin\Login::findPassword');
        $routes->post('permissions/save', 'Admin\Permissions::save');
        $routes->post('profile/updatePassword', 'Admin\Profile::updatePassword');
        $routes->post('roles/save', 'Admin\Roles::save');
        $routes->post('users/setPerms', 'Admin\Users::setPerms');
        $routes->post('users/save', 'Admin\Users::save');

        $routes->get('roles/data', 'Admin\Roles::data');
        $routes->get('users/data', 'Admin\Users::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('logout', 'Logout::index');
        $routes->get('login', 'Login::index');
        $routes->post('login/submit', 'Login::submit');
        $routes->get('dashboard', 'Dashboard::index');
    });

    // Admin Routes
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('permissions', 'Permissions::index');
        $routes->get('permissions/add', 'Permissions::add');
        $routes->get('permissions/edit/(:num)', 'Permissions::edit/$1');
        $routes->match(['get', 'post'], 'permissions/save', 'Permissions::save');
        $routes->get('permissions/delete', 'Permissions::delete');
        $routes->match(['get', 'post'], 'permissions/data', 'Permissions::data');
        $routes->match(['get', 'post'], 'permissions/data2', 'Permissions::data2');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        // Academic Session Routes
        $routes->get('academic_session', 'AcademicSession::index');
        $routes->get('academic_session/add', 'AcademicSession::add');
        $routes->get('academic_session/edit/(:num)', 'AcademicSession::edit/$1');
        $routes->post('academic_session/data', 'AcademicSession::data');
        $routes->post('academic_session/save', 'AcademicSession::save');
        $routes->get('academic_session/delete', 'AcademicSession::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('profile', 'Profile::index');
        $routes->post('profile/save', 'Profile::save');
        $routes->post('profile/update-password', 'Profile::update_password'); 
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('terms', 'Terms::index');
        $routes->post('terms/data', 'Terms::data');
        $routes->get('terms/add', 'Terms::add');
        $routes->get('terms/edit', 'Terms::edit');
        $routes->post('terms/save', 'Terms::save');
        $routes->get('terms/delete', 'Terms::delete');
        $routes->post('terms/toggle-status', 'Terms::toggleStatus');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('terms_session', 'TermsSession::index');
        $routes->post('terms_session/data', 'TermsSession::data');
        $routes->post('terms_session/data2', 'TermsSession::data2');
        $routes->get('terms_session/add', 'TermsSession::add');
        $routes->get('terms_session/edit', 'TermsSession::edit');
        $routes->post('terms_session/save', 'TermsSession::save');
        $routes->get('terms_session/delete', 'TermsSession::delete');
    });

    // Admin Term Weeks Routes
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('term_weeks', 'TermWeeks::index');
        $routes->post('term_weeks/data', 'TermWeeks::data');
        $routes->get('term_weeks/add', 'TermWeeks::add');
        $routes->get('term_weeks/edit', 'TermWeeks::edit');
        $routes->post('term_weeks/save', 'TermWeeks::save');
        $routes->post('term_weeks/generate_term_weeks', 'TermWeeks::generate_term_weeks');
        $routes->get('term_weeks/delete', 'TermWeeks::delete');
    });

    // Admin Classes Routes
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('classes', 'Classes::index');
        $routes->post('classes/data', 'Classes::data');
        $routes->get('classes/add', 'Classes::add');
        $routes->get('classes/edit', 'Classes::edit');
        $routes->post('classes/save', 'Classes::save');
        $routes->post('classes/toggle-status', 'Classes::toggleStatus');
    });

    // Admin Sections Routes
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('sections', 'Sections::index');
        $routes->post('sections/data', 'Sections::data');
        $routes->get('sections/add', 'Sections::add');
        $routes->get('sections/edit', 'Sections::edit');
        $routes->post('sections/save', 'Sections::save');
        $routes->get('sections/delete', 'Sections::delete');
        $routes->post('sections/toggle-status', 'Sections::toggleStatus');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('class_section', 'ClassSection::index');
        $routes->post('class_section/data', 'ClassSection::data');
        $routes->post('class-section/data2', 'ClassSection::data2');
        $routes->post('class-section/update-class-section', 'ClassSection::updateClassSection');
        $routes->get('class_section/add', 'ClassSection::add');
        $routes->get('class_section/edit', 'ClassSection::edit');
        $routes->post('class_section/save', 'ClassSection::save');
        $routes->get('class_section/delete', 'ClassSection::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        // Subjects module routes
        $routes->get('subjects', 'Subjects::index');
        $routes->post('subjects/data', 'Subjects::data');
        $routes->get('subjects/add', 'Subjects::add');
        $routes->get('subjects/edit', 'Subjects::edit');
        $routes->post('subjects/save', 'Subjects::save');
        $routes->get('subjects/delete', 'Subjects::delete');
        $routes->post('subjects/toggle-status', 'Subjects::toggleStatus');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('section_subjects', 'SectionSubjects::index');
        $routes->post('section_subjects/data', 'SectionSubjects::data');
        $routes->post('section_subjects/data2', 'SectionSubjects::data2');
        $routes->post('section_subjects/update', 'SectionSubjects::update');
        $routes->get('section_subjects/add', 'SectionSubjects::add');
        $routes->get('section_subjects/edit', 'SectionSubjects::edit');
        $routes->post('section_subjects/save', 'SectionSubjects::save');
        $routes->get('section_subjects/delete', 'SectionSubjects::delete');
        $routes->post('section_subjects/get-row', 'SectionSubjects::getRow');
        $routes->post('section_subjects/assign_teacher', 'SectionSubjects::assign_teacher');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students', 'Students::index');
        $routes->post('students/data', 'Students::data');
        $routes->get('students/add', 'Students::add');
        $routes->get('students/edit', 'Students::edit');
        $routes->get('students/delete', 'Students::delete');
        $routes->get('students/getStatus', 'Students::getStatus');

        $routes->post('students/save_basicinfo', 'Students::save_basicinfo');
        $routes->post('students/save_admission', 'Students::save_admission');
        $routes->post('students/get_fee_amount', 'Students::get_fee_amount');
        $routes->post('ajax/get_class_fee_amounts', 'Students::get_class_fee_amounts');

        $routes->post('students/save_contactinfo', 'Students::save_contactinfo');
        $routes->post('students/save_generalinfo', 'Students::save_generalinfo');
        $routes->post('students/save_attachment', 'Students::save_attachment');
        $routes->post('students/check_parent_cnic', 'Students::check_parent_cnic');

        $routes->post('students/updateDiscounts', 'Students::updateDiscounts');
        $routes->get('students/addbulk', 'Students::addbulk');
        $routes->post('students/uploadImage', 'Students::uploadImage');
        $routes->post('students/get-sibling', 'Students::getSibling');

        $routes->post('students/get_parentinfo', 'Students::get_parentinfo');
        $routes->post('admin/fee-chalan-pay/get-studentinfo', 'Admin\FeeChalanPay::getStudentinfo');
        $routes->post('admin/fee-chalan-pay1/get-studentinfo', 'Admin\FeeChalanPay1::getStudentinfo');
        $routes->get('fee-chalan-pay1', 'FeeChalan::pay', ['as' => 'fee_chalan_pay1']);
        $routes->post('admin/fee-chalan-pay/get-chalans', 'Admin\FeeChalanPay::get_chalans');

        $routes->post('students/getParentInfo', 'Students::getParentInfo');
        $routes->post('students/update-parent-info', 'Students::updateParentInfo');

        $routes->match(['get', 'post'], 'students/import', 'Students::import');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        $routes->get('students_bulk_parent_info', 'StudentsBulkParentInfo::index');
        $routes->match(['post','get'], 'students_bulk_parent_info/lookup_parent_by_cnic', 'StudentsBulkParentInfo::lookup_parent_by_cnic', ['as' => 'lookup_parent_by_cnic']);
        $routes->post('students_bulk_parent_info/data', 'StudentsBulkParentInfo::data', ['as' => 'students_bulk_info_data']);
        $routes->post('students_bulk_parent_info', 'StudentsBulkParentInfo::saveStudentInfo');
        $routes->get('students_bulk_parent_info/search-by-name', 'StudentsBulkParentInfo::searchByName', ['as' => 'students_search_by_name']);
        $routes->post('students_bulk_parent_info/by-parent', 'StudentsBulkParentInfo::byParent', ['as' => 'students_by_parent']);
        $routes->get('students/search-by-name', 'StudentsBulkParentInfo::searchByName');
        $routes->post('students/by-parent', 'StudentsBulkParentInfo::byParent');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        $routes->get('students_bulk_make_current', 'StudentsBulkMakeCurrent::index');
        $routes->get('students_bulk_make_current/search-by-name', 'StudentsBulkMakeCurrent::searchByName', ['as' => 'students_search_by_name']);
        $routes->match(['post','get'], 'students_bulk_make_current/data', 'StudentsBulkMakeCurrent::data', ['as' => 'students_bulk_make_current']);
        $routes->post('students_bulk_make_current/make-current', 'StudentsBulkMakeCurrent::makeCurrent', ['as' => 'make_current']);
        $routes->post('students_bulk_make_current/save_student_info', 'StudentsBulkMakeCurrent::saveStudentInfo', ['as' => 'students_bulk_info_save']);
        $routes->post('students_bulk_make_current/by-parent', 'StudentsBulkMakeCurrent::byParent', ['as' => 'students_by_parent']);
        $routes->get('students_bulk_make_current/search-by-name', 'StudentsBulkMakeCurrent::searchByName');
        $routes->get('students/search-by-name', 'StudentsBulkMakeCurrent::searchByName');
        $routes->post('students/by-parent', 'StudentsBulkMakeCurrent::byParent');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        $routes->get('students_bulk_info', 'StudentsBulkInfo::index');
        $routes->match(['post','get'], 'students_bulk_info/lookup_parent_by_cnic', 'StudentsBulkInfo::lookup_parent_by_cnic', ['as' => 'lookup_parent_by_cnic']);
        $routes->post('students_bulk_info/data', 'StudentsBulkInfo::data');
        $routes->post('students_bulk_info/save_student_info', 'StudentsBulkInfo::saveStudentInfo');
        $routes->get('students_bulk_info/search-by-name', 'StudentsBulkInfo::searchByName', ['as' => 'students_search_by_name']);
        $routes->post('students_bulk_info/by-parent', 'StudentsBulkInfo::byParent', ['as' => 'students_by_parent']);
        $routes->get('students/search-by-name', 'StudentsBulkInfo::searchByName');
        $routes->post('students/by-parent', 'StudentsBulkInfo::byParent');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        $routes->get('students_bulk_fee_info', 'StudentsBulkFeeInfo::index');
        $routes->match(['post','get'], 'students_bulk_fee_info/lookup_parent_by_cnic', 'StudentsBulkFeeInfo::lookup_parent_by_cnic', ['as' => 'lookup_parent_by_cnic']);
        $routes->post('students_bulk_fee_info/data', 'StudentsBulkFeeInfo::data');
        $routes->post('students_bulk_fee_info/save_student_info', 'StudentsBulkFeeInfo::saveStudentInfo', ['as' => 'students_bulk_fee_info_save']);
        $routes->get('students_bulk_fee_info/search-by-name', 'StudentsBulkFeeInfo::searchByName', ['as' => 'students_search_by_name']);
        $routes->post('students_bulk_fee_info/by-parent', 'StudentsBulkFeeInfo::byParent', ['as' => 'students_by_parent']);
        $routes->get('students/search-by-name', 'StudentsBulkFeeInfo::searchByName');
        $routes->post('students/by-parent', 'StudentsBulkFeeInfo::byParent');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_print', 'StudentsPrint::index');
        $routes->get('students-print', 'StudentsPrint::index');
        $routes->get('students_print/default-view', 'StudentsPrint::defaultView');
        $routes->post('students_print/save-view', 'StudentsPrint::saveView');
        $routes->post('students_print/data', 'StudentsPrint::data');
        $routes->post('students-print/data', 'StudentsPrint::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('addbulkstudents/add', 'AddBulkStudents::add');
        $routes->post('addbulkstudents/save', 'AddBulkStudents::save');
        $routes->post('addbulkstudents/select-student-by-class-section', 'AddBulkStudents::selectStudentByClassSection');
        $routes->post('addbulkstudents/select-student-by-class_section', 'AddBulkStudents::selectStudentByClassSection');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_bulk_cnic', 'Students_bulk_cnic::index');
        $routes->post('students_bulk_cnic/data', 'Students_bulk_cnic::data');
        $routes->post('students_bulk_cnic/update-parent-info', 'Students_bulk_cnic::updateParentInfo');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('studentsbulkcsv', 'Studentsbulkcsv::index');
        $routes->post('studentsbulkcsv/data', 'Studentsbulkcsv::data');
        $routes->post('studentsbulkcsv/import', 'StudentsBulkCSV::import');
        $routes->get('studentsbulkcsv/addbulk', 'StudentsBulkCSV::addbulk');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) { 
        $routes->get('students_bulk_contacts', 'Students_bulk_contacts::index');
        $routes->post('students_bulk_contacts/data', 'Students_bulk_contacts::data');
        $routes->post('students_bulk_contacts/savestudentcontacts', 'Students_bulk_contacts::saveStudentContacts');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('student_class', 'StudentClass::index');
        $routes->post('student_class/data', 'StudentClass::data');
        $routes->get('student_class/add', 'StudentClass::add');
        $routes->get('student_class/edit', 'StudentClass::edit');
        $routes->post('student_class/save', 'StudentClass::save');
        $routes->post('student_class/getStdCurrentClass', 'StudentClass::getStdCurrentClass');
        $routes->post('student_class/fetch-students', 'StudentClass::fetchStudents');
        $routes->post('student_class/move', 'StudentClass::move');
        $routes->post('student_class/move-bulk', 'StudentClass::moveBulk');
        $routes->post('student-class/fetch-students', 'StudentClass::fetchStudents');
        $routes->post('student-class/move', 'StudentClass::move');
        $routes->post('student-class/move-bulk', 'StudentClass::moveBulk');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('student_data_verification_form', 'StudentDataVerificationForm::index');
        $routes->post('student_data_verification_form/data', 'StudentDataVerificationForm::data');
        $routes->get('student_data_verification_form/student_fee_verification', 'StudentDataVerificationForm::student_fee_verification');
        $routes->post('student_data_verification_form/data2', 'StudentDataVerificationForm::data2');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('users', 'Users::index');
        $routes->post('users/data', 'Users::data');
        $routes->get('users/add', 'Users::add');
        $routes->get('users/edit/(:num)', 'Users::edit/$1');
        $routes->post('users/save', 'Users::save');
        $routes->get('users/delete/(:num)', 'Users::delete/$1');
        $routes->get('users/edit_password', 'Users::edit_password');
        $routes->post('users/edit_password', 'Users::edit_password');
        $routes->get('users/set_perms', 'Users::set_perms');
        $routes->post('users/set_perms', 'Users::set_perms');
        $routes->post('users/perm_data', 'Users::perm_data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('teacher_subjects', 'TeacherSubjects::index');
        $routes->post('teacher_subjects/data', 'TeacherSubjects::data');
        $routes->get('teacher_subjects/add', 'TeacherSubjects::add');
        $routes->get('teacher_subjects/edit/(:num)', 'TeacherSubjects::edit/$1');
        $routes->post('teacher_subjects/save', 'TeacherSubjects::save');
        $routes->get('teacher_subjects/delete/(:num)', 'TeacherSubjects::delete/$1');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('teacher_section', 'TeacherSection::index');
        $routes->post('teacher_section/data', 'TeacherSection::data');
        $routes->get('teacher_section/add', 'TeacherSection::add');
        $routes->get('teacher_section/edit/(:num)', 'TeacherSection::edit/$1');
        $routes->post('teacher_section/save', 'TeacherSection::save');
        $routes->post('teacher_section/selectteachersection', 'TeacherSection::selectteachersection');
        $routes->get('teacher_section/delete/(:num)', 'TeacherSection::delete/$1');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('emp_timing', 'EmpTiming::index');
        $routes->post('emp_timing/data', 'EmpTiming::data');
        $routes->get('emp_timing/add', 'EmpTiming::add');
        $routes->get('emp_timing/edit', 'EmpTiming::edit');
        $routes->post('emp_timing/save', 'EmpTiming::save');
        $routes->get('emp_timing/delete', 'EmpTiming::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('fee_type', 'FeeType::index');
        $routes->get('fee_type/add', 'FeeType::add');
        $routes->get('fee_type/edit', 'FeeType::edit');
        $routes->post('fee_type/save', 'FeeType::save');
        $routes->get('fee_type/delete', 'FeeType::delete');
        $routes->post('fee_type/data', 'FeeType::data');
        $routes->post('fee_type/data2', 'FeeType::data2');
        $routes->post('fee_type/set-monthly-fee', 'FeeType::setMonthlyFee');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('school_wizard', 'School_Wizard::index');
        $routes->post('school_wizard/saveWizardData', 'School_Wizard::saveWizardData');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('schoolsetup', 'School_Wizard::index');
        $routes->post('schoolsetup/saveStep1Class', 'School_Wizard::saveStep1Class');
        $routes->post('schoolsetup/saveStep2Section', 'School_Wizard::saveStep2Section');
        $routes->post('schoolsetup/saveWizardData', 'School_Wizard::saveWizardData');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('fee_amount', 'FeeAmount::index');
        $routes->get('fee_amount/add', 'FeeAmount::add');
        $routes->get('fee_amount/edit', 'FeeAmount::edit');
        $routes->post('fee_amount/save', 'FeeAmount::save');
        $routes->post('fee_amount/data', 'FeeAmount::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        $routes->get('fee-chalan-pay1', 'FeeChalan::pay1', ['as' => 'fee_chalan_pay1']);
    });

    // Fee Chalan Routes
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('fee-chalan', 'FeeChalan::index');
        $routes->get('fee-chalan/edit/(:num)', 'FeeChalan::edit/$1');
        $routes->post('fee-chalan/save', 'FeeChalan::save');
        $routes->get('fee_chalan/save', 'FeeChalan::save');
        $routes->post('fee-chalan/data', 'FeeChalan::data');
        $routes->get('fee-chalan/add', 'FeeChalan::add');
        $routes->post('fee_chalan/bulk_chalan_generation', 'FeeChalan::bulkChalanGeneration');
        $routes->get('fee_chalan/bulk_chalan_stream', 'FeeChalan::bulk_chalan_stream');

        // Additional views for printing chalans
        $routes->get('fee-chalan/thermal-copy', 'FeeChalan::thermalCopy');
        $routes->get('fee-chalan/single-copy', 'FeeChalan::singleCopy');
        $routes->get('fee-chalan/pdf', 'FeeChalan::threeCopyPdf');
        $routes->get('fee-chalan/without-discount', 'FeeChalan::withoutDiscount');
        $routes->get('fee-chalan/familywise', 'FeeChalan::familywise');
        $routes->get('fee-chalan/familywise/single-copy', 'FeeChalan::familywiseSingleCopy');
        $routes->get('fee-chalan/hostel', 'FeeChalan::hostel');
        $routes->get('fee-chalan/with-header', 'FeeChalan::withHeader');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('print-fee-chalan', 'PrintFeeChalan::index');
        $routes->post('print-fee-chalan/data', 'PrintFeeChalan::data', ['as' => 'print_fee_chalan_data']);
        $routes->get('print-fee-chalan/add', 'PrintFeeChalan::add');
        $routes->get('print-fee-chalan/thermal-copy', 'PrintFeeChalan::thermalCopy');
        $routes->get('print-fee-chalan/single-copy', 'PrintFeeChalan::singleCopy');
        $routes->get('print-fee-chalan/pdf', 'PrintFeeChalan::threeCopyPdf');
        $routes->get('print-fee-chalan/without-discount', 'PrintFeeChalan::withoutDiscount');
        $routes->post('print-fee-chalan/set-default-template', 'PrintFeeChalan::setDefaultTemplate');
        $routes->get('print-fee-chalan/familywise', 'PrintFeeChalan::familywise');
        $routes->get('print-fee-chalan/familywise/single-copy', 'PrintFeeChalan::familywiseSingleCopy');
        $routes->get('print-fee-chalan/hostel', 'PrintFeeChalan::hostel');
        $routes->get('print-fee-chalan/with-header', 'PrintFeeChalan::withHeader');
    });

    // Fee Chalan Pay Routes
    $routes->post('admin/fee-chalan-pay/getStudentCardAjax', 'Admin\FeeChalanPay::getStudentCardAjax');
    $routes->post('admin/fee-chalan-pay1/getStudentCardAjax', 'Admin\FeeChalanPay1::getStudentCardAjax');
    $routes->get('admin/fee-chalan-pay', 'Admin\FeeChalanPay::index');
    $routes->get('admin/fee-chalan-pay1', 'Admin\FeeChalanPay1::index');
    $routes->post('admin/fee-chalan-pay/get-studentinfo', 'Admin\FeeChalanPay::get_studentinfo');
    $routes->post('admin/fee-chalan-pay1/get-studentinfo', 'Admin\FeeChalanPay1::get_studentinfo');
    $routes->post('admin/fee-chalan-pay/get-chalans', 'Admin\FeeChalanPay::get_chalans');
    $routes->post('admin/fee-chalan-pay1/get-chalans', 'Admin\FeeChalanPay1::get_chalans');
    $routes->post('admin/fee-chalan-pay/generateStudentFeeCard', 'Admin\FeeChalanPay::generate-student-fee-card');
    $routes->post('admin/fee-chalan-pay1/generateStudentFeeCard', 'Admin\FeeChalanPay1::generate-student-fee-card');

    $routes->post('admin/fee-chalan-pay/markFeeAsPaid', 'Admin\FeeChalanPay::markFeeAsPaid');
    $routes->post('admin/fee-chalan-pay/mark-multiple-fees-as-paid', 'Admin\FeeChalanPay::markMultipleFeesAsPaid');
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
        $routes->post('data', 'FeeChalanPay::data');
        $routes->get('add', 'FeeChalanPay::add');
        $routes->get('edit/(:num)', 'FeeChalanPay::edit/$1');
        $routes->post('get-students-list', 'FeeChalanPay::get_students_list');
        $routes->post('adv-fee', 'FeeChalanPay::advFee');
        $routes->post('pay-fee-all', 'FeeChalanPay::payFeeAll');
        $routes->post('update-paid-fee', 'FeeChalanPay::updatePaidFee');
        $routes->post('send-sms', 'FeeChalanPay::sendSMS');
        $routes->post('updateStudentDiscount', 'FeeChalanPay::updateStudentDiscount');
        $routes->post('get-parent-info', 'FeeChalanPay::get_parentinfo');
    });

    $routes->group('admin/fee-chalan-pay1', ['namespace' => 'App\Controllers\Admin'], function($routes) {   
        $routes->post('data', 'FeeChalanPay1::data');
        $routes->get('add', 'FeeChalanPay1::add');
        $routes->get('edit/(:num)', 'FeeChalanPay::edit/$1');
        $routes->post('get-students-list', 'FeeChalanPay::get_students_list');
        $routes->post('adv-fee', 'FeeChalanPay::advFee');
        $routes->post('pay-fee-all', 'FeeChalanPay::payFeeAll');
        $routes->post('update-paid-fee', 'FeeChalanPay::updatePaidFee');
        $routes->post('send-sms', 'FeeChalanPay::sendSMS');
        $routes->post('updateStudentDiscount', 'FeeChalanPay::updateStudentDiscount');
        $routes->post('get-parent-info', 'FeeChalanPay::get_parentinfo');
    });

    $routes->group('admin/fee-chalan-pay2', ['namespace' => 'App\Controllers\Admin'], function($routes) {   
        $routes->get('/', 'FeeChalanPay2::index');
        $routes->post('data', 'FeeChalanPay2::data');
        $routes->get('add', 'FeeChalanPay2::add');
        $routes->get('edit/(:num)', 'FeeChalanPay2::edit/$1');
        $routes->post('get-students-list', 'FeeChalanPay2::get_students_list');
        $routes->post('adv-fee', 'FeeChalanPay2::advFee');
        $routes->post('pay-fee-all', 'FeeChalanPay2::payFeeAll');
        $routes->post('update-paid-fee', 'FeeChalanPay2::updatePaidFee');
        $routes->post('send-sms', 'FeeChalanPay2::sendSMS');
        $routes->post('get-student-info', 'FeeChalanPay2::get_studentinfo');
        $routes->post('get-parent-info', 'FeeChalanPay2::get_parentinfo');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('fee-chalan-balance', 'FeeChalanBalance::index');
        $routes->post('fee-chalan-balance/get-total-fee', 'FeeChalanBalance::getTotalfee');
        $routes->post('fee-chalan-balance/get-total-fee-by-month', 'FeeChalanBalance::getTotalfeebymonth');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('exam', 'Exam::index');
        $routes->post('exam/data', 'Exam::data');
        $routes->get('exam/add', 'Exam::add');
        $routes->get('exam/edit', 'Exam::edit');
        $routes->post('exam/save', 'Exam::save');
        $routes->post('exam/save-edit', 'Exam::save_edit');
        $routes->post('exam/getTermDateRange', 'Exam::getTermDateRange', ['as' => 'term_range']);
        $routes->post('exam/save_edit', 'Exam::save_edit');  
        $routes->post('exam/getDateRange', 'Exam::getDateRange', ['as' => 'exam_getDateRange']);
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->post('datesheet/fetchsummary', 'Datesheet::fetchsummary');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('datesheet/add-syllabus', 'Datesheet::addSyllabus');
        $routes->post('datesheet/fetch-syllabus-grid', 'Datesheet::fetchSyllabusGrid');
        $routes->post('datesheet/saveSyllabus', 'Datesheet::saveSyllabus');
        $routes->post('datesheet/saveSyllabusBulk', 'Datesheet::saveSyllabusBulk');
        $routes->post('datesheet/loadTlp', 'Datesheet::loadTlp');
    });

    $routes->group('admin/datesheet', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('/', 'Datesheet::index');
        $routes->get('without-syllabus', 'Datesheet::datesheet_without_syllabus');
        $routes->get('edit', 'Datesheet::edit');
        $routes->get('add', 'Datesheet::add');
        $routes->get('addSyllabus', 'Datesheet::addSyllabus');
        $routes->post('save', 'Datesheet::save');
        $routes->post('select-subjects', 'Datesheet::selectSubjects');
        $routes->post('data', 'Datesheet::getData');
        $routes->post('fetchgrid', 'Datesheet::fetchgrid');
        $routes->post('savegrid', 'Datesheet::savegrid');
        $routes->post('save-single', 'Datesheet::saveSingle');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('datesheet_without_syllabus', 'DatesheetWithoutSyllabus::index');
        $routes->get('datesheet_without_syllabus/data', 'DatesheetWithoutSyllabus::data');
        $routes->get('datesheet_without_syllabus/add', 'DatesheetWithoutSyllabus::add');
        $routes->get('datesheet_without_syllabus/edit', 'DatesheetWithoutSyllabus::edit');
        $routes->post('datesheet_without_syllabus/save', 'DatesheetWithoutSyllabus::save');
        $routes->post('datesheet_without_syllabus/select-subjects', 'DatesheetWithoutSyllabus::selectSubjects');
    });

    // Admin - Students Results
    $routes->group('admin/students-results', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('/', 'StudentsResults::index');
        $routes->post('data', 'StudentsResults::data');
        $routes->get('add', 'StudentsResults::add');
        $routes->get('edit/(:num)', 'StudentsResults::edit/$1');
        $routes->post('save', 'StudentsResults::save');
        $routes->post('get-students', 'StudentsResults::get_students');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
        $routes->get('ClasswiseMonthlyStrengthReport', 'ClasswiseMonthlyStrengthReport::index');
        $routes->get('classwise-monthly-strength-report', 'ClasswiseMonthlyStrengthReport::index', ['as' => 'admin.strength.index']);
        $routes->post('classwise-monthly-strength-report/data', 'ClasswiseMonthlyStrengthReport::data', ['as' => 'admin.strength.data']);
        $routes->get('classwise-monthly-strength-report/print', 'ClasswiseMonthlyStrengthReport::print', ['as' => 'admin.strength.print']);
    });

    // Admin group
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        $routes->get('datesheet-report', 'DatesheetReport::index', ['as' => 'datesheet_index']);
        $routes->get('datesheet-report/add', 'DatesheetReport::add', ['as' => 'datesheet_add']);
        $routes->post('datesheet-report/save', 'DatesheetReport::save', ['as' => 'datesheet_save']);
        $routes->post('datesheet-report/data', 'DatesheetReport::data', ['as' => 'datesheet_data']);
        $routes->get('datesheet_report', 'DatesheetReport::index');
        $routes->get('datesheet_report/add', 'DatesheetReport::add');
        $routes->post('datesheet_report/save', 'DatesheetReport::save');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students-results-list', 'StudentsResultsList::index');
        $routes->post('students-results-list/get-students', 'StudentsResultsList::get_students');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_results_card', 'StudentsResultsCard::index');
        $routes->post('students-results-card/data', 'StudentsResultsCard::data');
    });

    $routes->group('admin/employees_attendance', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('/', 'EmployeesAttendance::index');
        $routes->post('data', 'EmployeesAttendance::data');
        $routes->get('add', 'EmployeesAttendance::add');
        $routes->get('edit', 'EmployeesAttendance::edit');
        $routes->post('save', 'EmployeesAttendance::save');
        $routes->post('get_employees', 'EmployeesAttendance::get_employees');
        $routes->get('delete', 'EmployeesAttendance::delete');
    });

    $routes->group('admin/employee_leaves', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('/', 'EmployeeLeaves::index');
        $routes->post('data', 'EmployeeLeaves::data');
        $routes->post('approveleave', 'EmployeeLeaves::approveleave');
        $routes->post('rejectleave', 'EmployeeLeaves::rejectleave');
        $routes->post('save', 'EmployeeLeaves::save');
        $routes->get('add', 'EmployeeLeaves::add');
        $routes->get('edit', 'EmployeeLeaves::edit');
        $routes->get('delete', 'EmployeeLeaves::delete');
        $routes->post('get_employee', 'EmployeeLeaves::get_employee');
        $routes->post('get_employeeinfo', 'EmployeeLeaves::get_employeeinfo');
    });

    // Students Attendance Routes
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function($routes) {
        $routes->get('students_attendance', 'StudentsAttendance::index');
        $routes->post('students_attendance/data', 'StudentsAttendance::data');
        $routes->get('students_attendance/add', 'StudentsAttendance::add');
        $routes->get('students_attendance/report', 'StudentsAttendance::report');
        $routes->get('students_attendance/edit', 'StudentsAttendance::edit');
        $routes->post('students_attendance/save', 'StudentsAttendance::save');
        $routes->post('students_attendance/get_students_byclass', 'StudentsAttendance::get_students_byclass');
        $routes->post('students_attendance/get_students_byabsentees', 'StudentsAttendance::get_students_byabsentees');
        $routes->get('students_attendance/delete', 'StudentsAttendance::delete');
        $routes->get('students_attendance/students_attendance_detail', 'StudentsAttendance::students_attendance_detail', ['as' => 'students_attendance_detail']);

        $routes->match(['get','post'], 'students_attendance/report_cards', 'StudentsAttendance::report_cards');
        $routes->post('students_attendance/sections_by_class', 'StudentsAttendance::sections_by_class');
    });

    // Admin Group Routes
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        // Students Absentees
        $routes->get('students_absentees', 'StudentsAbsentees::index');
        $routes->get('students_absentees/add', 'StudentsAbsentees::add');
        $routes->get('students_absentees/edit', 'StudentsAbsentees::edit');
        $routes->post('students_absentees/save', 'StudentsAbsentees::save');
        $routes->post('students_absentees/data', 'StudentsAbsentees::data');
        $routes->post('students_absentees/get_students_byclass', 'StudentsAbsentees::get_students_byclass');
        $routes->post('students_absentees/update_attendance_status', 'StudentsAbsentees::update_attendance_status');
        $routes->get('students_absentees/delete', 'StudentsAbsentees::delete');

        // NEW: search-by-name + parent-based flow
        $routes->get('students_absentees/search-by-name', 'StudentsAbsentees::searchByName', ['as' => 'absentees_search_by_name']);
        $routes->post('students_absentees/by-parent', 'StudentsAbsentees::byParent', ['as' => 'absentees_by_parent']);
        $routes->post('students_absentees/check_and_load_attendance_by_parent', 'StudentsAbsentees::check_and_load_attendance_by_parent');
        $routes->post('students_absentees/mark_and_show_students_by_parent', 'StudentsAbsentees::mark_and_show_students_by_parent');

        // Existing helpers
        $routes->post('students_absentees/mark_and_show_students', 'StudentsAbsentees::mark_and_show_students');
        $routes->post('students_absentees/toggle_attendance_status', 'StudentsAbsentees::toggle_attendance_status');
        $routes->post('students_absentees/load_existing_attendance', 'StudentsAbsentees::load_existing_attendance');
        $routes->post('students_absentees/check_and_load_attendance', 'StudentsAbsentees::check_and_load_attendance');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_latecomming', 'StudentsLateComming::index');
        $routes->post('students_latecomming/data', 'StudentsLateComming::data');
        $routes->get('students_latecomming/add', 'StudentsLateComming::add');
        $routes->post('students_latecomming/save', 'StudentsLateComming::save');
        $routes->get('students_latecomming/delete', 'StudentsLateComming::delete');
        $routes->post('students_latecomming/get_studentinfo', 'StudentsLateComming::get_studentinfo');
        $routes->post('students_latecomming/get_students_byclass', 'StudentsLateComming::get_students_byclass');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students-late-comming', 'StudentsLateComming::index');
        $routes->post('students-late-comming/data', 'StudentsLateComming::data');
        $routes->get('students-late-comming/add', 'StudentsLateComming::add');
        $routes->post('students-late-comming/save', 'StudentsLateComming::save');
        $routes->get('students-late-comming/edit', 'StudentsLateComming::edit');
        $routes->get('students-late-comming/delete', 'StudentsLateComming::delete');

        // AJAX endpoints
        $routes->post('students-late-comming/get-studentinfo', 'StudentsLateComming::get_studentinfo');
        $routes->post('students-late-comming/get-students-byclass', 'StudentsLateComming::get_students_byclass');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students-early-left', 'StudentsEarlyLeft::index');
        $routes->post('students-early-left/data', 'StudentsEarlyLeft::data');
        $routes->get('students-early-left/add', 'StudentsEarlyLeft::add');
        $routes->post('students-early-left/save', 'StudentsEarlyLeft::save');
        $routes->get('students-early-left/edit', 'StudentsEarlyLeft::edit');
        $routes->get('students-early-left/delete', 'StudentsEarlyLeft::delete');

        // AJAX/JSON Endpoints
        $routes->post('students-early-left/get-studentinfo', 'StudentsEarlyLeft::get_studentinfo');
        $routes->post('students-early-left/get-students-byclass', 'StudentsEarlyLeft::get_students_byclass');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_leaves', 'Students_leaves::index');
        $routes->post('students_leaves/data', 'Students_leaves::data');
        $routes->post('students_leaves/save', 'Students_leaves::save');
        $routes->get('students_leaves/add', 'Students_leaves::add');
        $routes->get('students_leaves/edit', 'Students_leaves::edit');
        $routes->get('students_leaves/delete', 'Students_leaves::delete');
        $routes->post('students_leaves/approveleave', 'Students_leaves::approveleave');
        $routes->post('students_leaves/rejectleave', 'Students_leaves::rejectleave');
        $routes->post('students_leaves/get_studentinfo', 'Students_leaves::get_studentinfo');
        $routes->post('students_leaves/get_students_byclass', 'Students_leaves::get_students_byclass');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('timetable/index', 'Timetable::index');
        $routes->get('timetable/add', 'Timetable::add');
        $routes->post('timetable/data', 'Timetable::data');
        $routes->get('timetable/edit', 'Timetable::edit');
        $routes->post('timetable/save', 'Timetable::save');
        $routes->get('timetable/delete', 'Timetable::delete');
        $routes->post('timetable/fetch-table', 'Timetable::fetchTable');
        $routes->post('timetable/save-slot', 'Timetable::saveSlot');
        $routes->post('timetable/get-subjects', 'Timetable::getSubjects');
        $routes->post('timetable/get-subjects-timetable', 'Timetable::getSubjectsTimetable');
        $routes->post('timetable/update-slot', 'Timetable::updateSlot');
        $routes->get('timetable/teachers', 'Timetable::viewTeacherTimetable');
        $routes->get('timetable/teacher', 'Timetable::getTeacherTimetable');
        $routes->get('timetable/time-table-add-new', 'Timetable::timeTableAddNew');
        $routes->post('timetable/clear', 'Timetable::clear');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('school_timing', 'School_timing::index');
        $routes->post('school_timing/data', 'School_timing::data');
        $routes->get('school_timing/add', 'School_timing::add');
        $routes->get('school_timing/edit', 'School_timing::edit');
        $routes->post('school_timing/save', 'School_timing::save');
        $routes->get('school_timing/delete', 'School_timing::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        // School Timing Type Routes
        $routes->get('school_timming_type', 'School_timming_type::index');
        $routes->post('school_timming_type/data', 'School_timming_type::data');
        $routes->get('school_timming_type/add', 'School_timming_type::add');
        $routes->get('school_timming_type/edit', 'School_timming_type::edit');
        $routes->post('school_timming_type/save', 'School_timming_type::save');
        $routes->post('school_timming_type/getDateRange', 'School_timming_type::getDateRange');
        $routes->get('school_timming_type/delete', 'School_timming_type::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        // Top Level Planning Routes
        $routes->get('top_level_planning', 'Top_level_planning::index');
        $routes->post('top_level_planning/data', 'Top_level_planning::data');
        $routes->get('top_level_planning/add', 'Top_level_planning::add');
        $routes->get('top_level_planning/edit', 'Top_level_planning::edit');
        $routes->get('top_level_planning/delete', 'Top_level_planning::delete');
        $routes->post('top_level_planning/save', 'Top_level_planning::save');
        $routes->get('top_level_planning_gradewise', 'Top_level_planning_gradewise::index');
        $routes->post('top_level_planning_gradewise', 'Top_level_planning_gradewise::index');
        $routes->post('top_level_planning/select-subjectsfor-top-level-planning', 'Top_level_planning::selectSubjectsforTopLevelPlanning');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        // Top Level Planning Sections
        $routes->get('top_level_planning_sections', 'TopLevelPlanningSections::index');
        $routes->get('top_level_planning_sections/add', 'TopLevelPlanningSections::add', ['as' => 'top_level_planning_sections_add']);
        $routes->post('top_level_planning_sections/save', 'TopLevelPlanningSections::save', ['as' => 'top_level_planning_sections_save']);
        $routes->get('top_level_planning', 'TopLevelPlanning::index');
        $routes->get('top_level_planning/add', 'TopLevelPlanning::add', ['as' => 'top_level_planning_add']);
        $routes->post('top_level_planning/save', 'TopLevelPlanning::save', ['as' => 'top_level_planning_save']);
        $routes->post('top_level_planning_sections/select-term', 'TopLevelPlanningSections::selectTermforTopLevelPlanning', ['as' => 'tlp_sections_select_term']);
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        $routes->get('top_level_planning_subject', 'TopLevelPlanningSubject::index');
        $routes->get('top_level_planning_subject/add', 'TopLevelPlanningSubject::add', ['as' => 'tlp_subject_add']);
        $routes->post('top_level_planning_subject/save', 'TopLevelPlanningSubject::save', ['as' => 'tlp_subject_save']);
        $routes->post('top_level_planning_subject/select-subjects', 'TopLevelPlanningSubject::selectSubjectsforTopLevelPlanning', ['as' => 'tlp_subject_select_subjects']);
        $routes->get('top_level_planning_subject/edit', 'TopLevelPlanningSubject::edit', ['as' => 'tlp_subject_edit']);
        $routes->get('top_level_planning_subject/delete', 'TopLevelPlanningSubject::delete', ['as' => 'tlp_subject_delete']);
        $routes->post('top_level_planning_subject/select-subjects', 'TopLevelPlanningSubject::selectSubjects');
        $routes->post('top_level_planning_subject/autosave', 'TopLevelPlanningSubject::autosave');
        $routes->post('ajax/select-exam', 'Ajax::selectExam');
        $routes->post('top_level_planning_subject/select-subjects-for-top-level-planning', 'TopLevelPlanningSubject::selectSubjectsforTopLevelPlanning');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('scheme_of_studies_view', 'Scheme_of_studies_view::index');
        $routes->post('scheme_of_studies_view/data', 'Scheme_of_studies_view::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('weekly_planning_view', 'WeeklyPlanningView::index');
        $routes->post('weekly_planning_view/data', 'WeeklyPlanningView::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('wp_objectives', 'WpObjectives::index');
        $routes->post('wp_objectives/data', 'WpObjectives::data');
        $routes->get('wp_objectives/add', 'WpObjectives::add');
        $routes->get('wp_objectives/edit', 'WpObjectives::edit');
        $routes->post('wp_objectives/save', 'WpObjectives::save');
        $routes->get('wp_objectives/delete', 'WpObjectives::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('wp-subjects-objectives', 'WpSubjectsObjectives::index');
        $routes->post('wp-subjects-objectives/data', 'WpSubjectsObjectives::data');
        $routes->post('wp-subjects-objectives/data2', 'WpSubjectsObjectives::data2');
        $routes->post('wp-subjects-objectives/update', 'WpSubjectsObjectives::updateWpSubjectObjective');
        $routes->get('wp-subjects-objectives/add', 'WpSubjectsObjectives::add');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('weekly-progress', 'WpStdWeeeklyProgress::index');
        $routes->post('weekly-progress/data', 'WpStdWeeeklyProgress::data');
        $routes->get('weekly-progress/add', 'WpStdWeeeklyProgress::add');
        $routes->get('weekly-progress/edit', 'WpStdWeeeklyProgress::edit');
        $routes->post('weekly-progress/save', 'WpStdWeeeklyProgress::save');
        $routes->post('weekly-progress/select-section-subject', 'WpStdWeeeklyProgress::selectSectionSubjectbySection');
        $routes->post('weekly-progress/get-students', 'WpStdWeeeklyProgress::get_students');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('wp-results-card', 'WpResultsCard::index');
        $routes->post('wp-results-card/data', 'WpResultsCard::data');
    });

    $routes->group('admin/ajax', ['namespace'=>'App\Controllers\Admin'], static function($routes){
        $routes->get('user-menu-prefs', 'UserMenuPrefs::get');
        $routes->post('user-menu-prefs', 'UserMenuPrefs::save');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        // ---- Classdiary (CRUD + AJAX) ----
        $routes->get('classdiary', 'Classdiary::index', ['as' => 'classdiary_index']);
        $routes->get('classdiary/add','Classdiary::add', ['as' => 'classdiary_add']);
        $routes->post('classdiary/data','Classdiary::data', ['as' => 'classdiary_data']);
        $routes->get('classdiary/edit','Classdiary::edit', ['as' => 'classdiary_edit']);
        $routes->post('classdiary/save','Classdiary::save', ['as' => 'classdiary_save']);
        $routes->post('classdiary/select-section-subject-by-section', 'Classdiary::selectSectionSubjectbySection', ['as' => 'classdiary_select_section_subject_by_section']);
        $routes->post('classdiary/get-classdiary', 'Classdiary::get_classdiary', ['as' => 'classdiary_get']);
        $routes->post('classdairy/get-classdiary', 'Classdiary::get_classdiary');
        $routes->get('classdiary-view','ClassdiaryView::index', ['as' => 'classdiary_view']);
        $routes->post('classdiary-view/data','ClassdiaryView::data', ['as' => 'classdiary_view_data']);
        $routes->get('classdairy-view','ClassdiaryView::index');
        $routes->post('classdairy-view/data','ClassdiaryView::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('student-notices', 'StudentNotices::index');
        $routes->post('student-notices/data', 'StudentNotices::data');
        $routes->get('student-notices/add', 'StudentNotices::add');
        $routes->get('student-notices/edit', 'StudentNotices::edit');
        $routes->post('student-notices/save', 'StudentNotices::save');
        $routes->post('student-notices/get-noticeinfo', 'StudentNotices::get_noticeinfo');
        $routes->get('student-notices/delete', 'StudentNotices::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students-complaints', 'StudentsComplaints::index');
        $routes->post('students-complaints/data', 'StudentsComplaints::data');
        $routes->get('students-complaints/add', 'StudentsComplaints::add');
        $routes->get('students-complaints/edit', 'StudentsComplaints::edit');
        $routes->post('students-complaints/save', 'StudentsComplaints::save');
        $routes->post('students-complaints/get-students-byclass', 'StudentsComplaints::get_students_byclass');
        $routes->get('students-complaints/delete', 'StudentsComplaints::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        // Attendance Monthly Report
        $routes->get('attendance-monthly-report', 'AttendanceMonthlyReport::index');
        $routes->post('attendance-monthly-report/get-students-byclass', 'AttendanceMonthlyReport::get_students_byclass');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        // Campus Management
        $routes->get('campus', 'Campus::index');
        $routes->post('campus/data', 'Campus::data');
        $routes->get('campus/add', 'Campus::add');
        $routes->get('campus/edit', 'Campus::edit');
        $routes->post('campus/save', 'Campus::save');
        $routes->post('campus/get-packages', 'Campus::get_packages');
        $routes->post('campus/calculate-campus-bill', 'Campus::calculateCampusBill');
        $routes->get('campus/delete', 'Campus::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        // GET /admin/profile-campus
        $routes->get('profile-campus', 'ProfileCampus::index');
        // POST /admin/profile_campus/save
        $routes->post('profile_campus/save', 'ProfileCampus::save', ['as' => 'profile_campus_save']);
        // POST /admin/profile_campus/update-password
        $routes->post('profile_campus/update-password', 'ProfileCampus::update_password', ['as' => 'profile_campus_update_password']);
    });

    // Admin group
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
        // Preferred slugs
        $routes->get('profit-loss-report', 'ProfitLossReport::index', ['as' => 'profit_loss_index']);
        $routes->post('profit-loss-report/daily', 'ProfitLossReport::getDailyCollection', ['as' => 'profit_loss_daily']);
        // Legacy/underscore aliases
        $routes->get('profit_loss_report', 'ProfitLossReport::index');
        $routes->post('profit_loss_report/get_daily_collection', 'ProfitLossReport::getDailyCollection');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('profile-system', 'ProfileSystem::index');
        $routes->post('profile-system/save', 'ProfileSystem::save');
        $routes->post('profile-system/update-reg-text', 'ProfileSystem::updateRegText');
        $routes->post('profile-system/update-password', 'ProfileSystem::update_password');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('admission-enquiry', 'AdmissionEnquiry::index');
        $routes->post('admission-enquiry/data', 'AdmissionEnquiry::data');
        $routes->get('admission-enquiry/add', 'AdmissionEnquiry::add');
        $routes->get('admission-enquiry/edit', 'AdmissionEnquiry::edit');
        $routes->post('admission-enquiry/save', 'AdmissionEnquiry::save');
        $routes->get('admission-enquiry/delete', 'AdmissionEnquiry::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('message-templates', 'MessageTemplates::index');
        $routes->post('message-templates/save', 'MessageTemplates::save');
    });

    $routes->group('admin/messages', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('/', 'Messages::index');
        $routes->post('data', 'Messages::data');
        $routes->get('add', 'Messages::add');
        $routes->post('save', 'Messages::save');
        $routes->get('delete', 'Messages::delete');
    });

    $routes->group('admin/bulksms', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('/', 'BulkSms::index');
        $routes->post('data', 'BulkSms::data');
        $routes->get('add', 'BulkSms::add');
        $routes->get('edit', 'BulkSms::edit');
        $routes->post('save', 'BulkSms::save');
        $routes->get('delete', 'BulkSms::delete');
    });

    $routes->group('admin/defaulter-message', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'DefaulterMessage::index');
        $routes->post('data', 'DefaulterMessage::data');
        $routes->post('save', 'DefaulterMessage::save');
        $routes->get('parent_sms', 'DefaulterMessage::parent_sms');
        $routes->post('saveparent', 'DefaulterMessage::saveparent');
        $routes->get('delete', 'DefaulterMessage::delete');
    });

    $routes->group('admin/result-message', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'ResultMessage::index');
        $routes->post('data', 'ResultMessage::data');
        $routes->post('save', 'ResultMessage::save');
    });

    $routes->group('admin/students-list', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'StudentsList::index');
        $routes->post('data', 'StudentsList::data');
        $routes->post('get-parentinfo', 'StudentsList::get_parentinfo');
        $routes->post('get-studentinfo', 'StudentsList::get_studentinfo');
    });

    $routes->group('admin/students-w-result-list', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'StudentsWithResultList::index');
        $routes->post('data', 'StudentsWithResultList::data');
        $routes->post('get-parentinfo', 'StudentsWithResultList::getParentInfo');
        $routes->post('get-studentinfo', 'StudentsWithResultList::getStudentInfo');
    });

    $routes->group('admin/family-chalan-whatsapp', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
        $routes->get('/', 'FamilyChalanWhatsapp::index');
        $routes->post('data', 'FamilyChalanWhatsapp::data');
        $routes->post('get-parentinfo', 'FamilyChalanWhatsapp::get_parentinfo');
        $routes->post('get-studentinfo', 'FamilyChalanWhatsapp::get_studentinfo');
    });

    $routes->group('Frontend', ['namespace' => 'App\Controllers\Frontend'], static function ($routes) {
        $routes->get('family_diary_whatsapp', 'FamilyDiaryWhatsapp::index');
        $routes->post('family_diary_whatsapp/data', 'FamilyDiaryWhatsapp::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('profile-student', 'ProfileStudent::index');
        $routes->post('profile-student/data', 'ProfileStudent::data');
        $routes->post('profile-student/save', 'ProfileStudent::save');
        $routes->post('profile-student/update-password', 'ProfileStudent::updatePassword');
        $routes->post('profile-student/student-fee-data', 'ProfileStudent::singleStudentFeedata');
        $routes->post('profile-student/student-attendance-data', 'ProfileStudent::singleStudentAttendancedata');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('fee-chalan-single', 'FeeChalanSingle::index');
        $routes->get('fee-chalan-single/add', 'FeeChalanSingle::add');
        $routes->post('fee-chalan-single/save', 'FeeChalanSingle::save');
        $routes->get('fee-chalan-single/download', 'FeeChalanSingle::download');
        $routes->match(['get', 'post'], 'fee-chalan-single/data', 'FeeChalanSingle::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('leaving-certificate', 'LeavingCertificate::index');
        $routes->match(['get', 'post'], 'leaving-certificate/data', 'LeavingCertificate::data');
        $routes->get('leaving-certificate/add', 'LeavingCertificate::add');
        $routes->get('leaving-certificate/edit', 'LeavingCertificate::edit');
        $routes->post('leaving-certificate/save', 'LeavingCertificate::save');
        $routes->get('leaving-certificate/download', 'LeavingCertificate::download');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('fee-chalan-sibling', 'FeeChalanSibling::index');
        $routes->match(['get', 'post'], 'fee-chalan-sibling/data', 'FeeChalanSibling::data');
    });

    // File: app/Config/Routes.php or inside a Route group for 'admin'
    $routes->group('admin/ajax', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('index', 'Ajax::index');
        $routes->post('setboolattribute', 'Ajax::setboolattribute');
        // POST Methods
        $routes->post('select-class-fee', 'Ajax::selectClassFee');
        $routes->post('updatestudentstatus', 'Ajax::updatestudentstatus');
        $routes->post('change-campus', 'Ajax::changeCampus');
        $routes->post('select-session', 'Ajax::selectSession');
        $routes->post('setboolattributeteachers', 'Ajax::setboolattributeteachers');
        $routes->post('setboolattribute2', 'Ajax::setboolattribute2');
        $routes->post('setboolattributeexam', 'Ajax::setboolattributeexam');
        $routes->post('setboolattributetest', 'Ajax::setboolattributetest');
        $routes->post('setboolattributeSchoolType', 'Ajax::setboolattributeSchoolType');
        $routes->post('setfeetypestatus', 'Ajax::setfeetypestatus');
        $routes->post('setboolattribute', 'Ajax::setboolattribute');
        $routes->post('setboolIndexable', 'Ajax::setboolIndexable');
        $routes->post('setboolattributeIsTrial', 'Ajax::setboolattributeIsTrial');
        $routes->post('setboolattributeFee', 'Ajax::setboolattributeFee');
        $routes->post('setboolattributenotice', 'Ajax::setboolattributenotice');
        $routes->post('setfieldvalue', 'Ajax::setfieldvalue');
        $routes->post('setunique', 'Ajax::setunique');
        $routes->post('set_sortid', 'Ajax::set_sortid');
        $routes->post('selectsectionby-class', 'Ajax::selectsectionbyClass');
        $routes->post('select-exam', 'Ajax::selectExam');
        $routes->post('selectsubjectby-section', 'Ajax::selectsubjectbySection');
        $routes->post('select-section-subjectby-section', 'Ajax::selectSectionSubjectbySection');
        $routes->post('selecttermby-session', 'Ajax::selecttermbySession');
        $routes->post('selectcategoriesbysubject', 'Ajax::selectcategoriesbysubject');
        $routes->post('selecttopicbycategories', 'Ajax::selecttopicbycategories');
        $routes->post('select-skillsby-topic', 'Ajax::selectSkillsbyTopic');
        $routes->post('selectmul-terms-weeks', 'Ajax::selectmulTermsWeeks');
        $routes->post('select-term-weeks', 'Ajax::selectTermWeeks');
        $routes->post('select-class-sub-cat', 'Ajax::selectClassSubCat');
        $routes->post('pay_fee', 'Ajax::pay_fee');
        $routes->post('get_students', 'Ajax::get_students');
        $routes->post('check_father_cinic', 'Ajax::check_father_cinic');

        // GET Methods
        $routes->get('check_username', 'Ajax::check_username');
        $routes->get('check_value', 'Ajax::check_value');
        $routes->get('check_emp_value', 'Ajax::check_emp_value');
        $routes->get('check_fee_month', 'Ajax::check_fee_month');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('student_id_card', 'StudentIdCard::index');
        $routes->post('student_id_card/data', 'StudentIdCard::data');
        $routes->get('student_id_card/vertical', 'StudentIdCard::vertical');
        $routes->post('student_id_card/data_vertical', 'StudentIdCard::data_vertical');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_contact_list', 'StudentsContactList::index');
        $routes->post('students_contact_list/data', 'StudentsContactList::data');
        $routes->get('students_contact_list/add', 'StudentsContactList::add');
        $routes->get('students_contact_list/edit', 'StudentsContactList::edit');
        $routes->post('students_contact_list/save', 'StudentsContactList::save');
        $routes->post('students_contact_list/get_parentinfo', 'StudentsContactList::get_parentinfo');
        $routes->post('students_contact_list/get_studentinfo', 'StudentsContactList::get_studentinfo');
        $routes->get('students_contact_list/delete', 'StudentsContactList::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        // Defaulter students list main page
        $routes->get('students_defaulters_list', 'StudentsDefaultersList::index');
        // Data AJAX (datatable or API)
        $routes->post('students_defaulters_list/data', 'StudentsDefaultersList::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        // Defaulter Fee Report main view
        $routes->get('defaulter_students_fee_report', 'DefaulterStudentsFeeReport::index');
        // Data (AJAX/post)
        $routes->post('defaulter_students_fee_report/data', 'DefaulterStudentsFeeReport::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_prevfee', 'StudentsPrevfee::index');
        $routes->post('students_prevfee/data', 'StudentsPrevfee::data');
        $routes->post('students_prevfee/selectClassFee', 'StudentsPrevfee::selectClassFee');
        $routes->post('students_prevfee/saveStudent', 'StudentsPrevfee::saveStudent');
        $routes->post('students_prevfee/save', 'StudentsPrevfee::save');
    });

    // Admin Attachment Types routes (CI4 style)
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('attachment_types', 'AttachmentTypes::index');
        $routes->post('attachment_types/data', 'AttachmentTypes::data');
        $routes->get('attachment_types/add', 'AttachmentTypes::add');
        $routes->get('attachment_types/edit', 'AttachmentTypes::edit');
        $routes->post('attachment_types/save', 'AttachmentTypes::save');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('fee_plan_months', 'FeePlanMonths::index');
        $routes->post('fee_plan_months/data', 'FeePlanMonths::data');
        $routes->post('fee_plan_months/data2', 'FeePlanMonths::data2');
        $routes->post('fee_plan_months/updateFeePlanMonth', 'FeePlanMonths::updateFeePlanMonth');
        $routes->get('fee_plan_months/add', 'FeePlanMonths::add');
        $routes->get('fee_plan_months/edit', 'FeePlanMonths::edit');
        $routes->post('fee_plan_months/save', 'FeePlanMonths::save');
    });

    // Admin Routes for Student Fee Report
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('student_fee_report', 'StudentFeeReport::index');
        $routes->post('student_fee_report/single-student-feedata', 'StudentFeeReport::singleStudentFeedata');
        $routes->post('student_fee_report/data2', 'StudentFeeReport::data2');
        $routes->post('student_fee_report/get_studentinfo', 'StudentFeeReport::get_studentinfo');
        $routes->get('student_fee_report/report_by_fee_type', 'StudentFeeReport::report_by_fee_type');
        $routes->get('student_fee_report/report_by_fee_student', 'StudentFeeReport::report_by_fee_student');
        $routes->get('student_fee_report/edit', 'StudentFeeReport::edit');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('parents_paidfee', 'ParentsPaidfee::index');
        $routes->post('parents_paidfee/data', 'ParentsPaidfee::data');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('parents_balancefee', 'ParentsBalancefee::index');
        $routes->post('parents_balancefee/data', 'ParentsBalancefee::data');
        $routes->post('parents_balancefee/update_fee_status', 'ParentsBalancefee::update_fee_status');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('fee_chalan_month', 'FeeChalanMonth::index');
        $routes->post('fee_chalan_month/getTotalfee', 'FeeChalanMonth::getTotalfee');
        $routes->post('fee_chalan_month/getTotalfeebymonth', 'FeeChalanMonth::getTotalfeebymonth');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('family_fee_history', 'FamilyFeeHistory::index');
        $routes->post('family_fee_history/data', 'FamilyFeeHistory::data');
        $routes->post('family_fee_history/get_parentinfo', 'FamilyFeeHistory::get_parentinfo');
        $routes->post('family_fee_history/get_studentinfo', 'FamilyFeeHistory::get_studentinfo');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('family_fee_report', 'FamilyFeeReport::index');
        $routes->post('family_fee_report/single_student_feedata', 'FamilyFeeReport::singleStudentFeedata');
        $routes->post('family_fee_report/data2', 'FamilyFeeReport::data2');
        $routes->post('family_fee_report/data', 'FamilyFeeReport::data');
        $routes->get('family_fee_report/report_by_fee_type', 'FamilyFeeReport::report_by_fee_type');
        $routes->get('family_fee_report/report_by_fee_student', 'FamilyFeeReport::report_by_fee_student');
        $routes->get('family_fee_report/edit', 'FamilyFeeReport::edit');
        $routes->post('family_fee_report/get_studentinfo', 'FamilyFeeReport::get_studentinfo');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('expense_head', 'ExpenseHead::index');
        $routes->post('expense_head/data', 'ExpenseHead::data');
        $routes->get('expense_head/add', 'ExpenseHead::add');
        $routes->get('expense_head/edit', 'ExpenseHead::edit');
        $routes->post('expense_head/save', 'ExpenseHead::save');
        $routes->get('expense_head/delete', 'ExpenseHead::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('expenses', 'Expenses::index');
        $routes->post('expenses/data', 'Expenses::data');
        $routes->post('expenses/get-expenses', 'Expenses::getExpenses');
        $routes->get('expenses/add', 'Expenses::add');
        $routes->get('expenses/edit', 'Expenses::edit');
        $routes->post('expenses/save', 'Expenses::save');
        $routes->get('expenses/delete', 'Expenses::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('asset_heads', 'AssetHeads::index');
        $routes->post('asset_heads/data', 'AssetHeads::data');
        $routes->get('asset_heads/add', 'AssetHeads::add');
        $routes->get('asset_heads/edit', 'AssetHeads::edit');
        $routes->post('asset_heads/save', 'AssetHeads::save');
        $routes->get('asset_heads/delete', 'AssetHeads::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('assets', 'Assets::index');
        $routes->post('assets/data', 'Assets::data');
        $routes->post('assets/get-assets', 'Assets::getAssets');
        $routes->get('assets/add', 'Assets::add');
        $routes->post('assets/save', 'Assets::save');
        $routes->get('assets/delete', 'Assets::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('datesheet2', 'Datesheet2::index');
        $routes->get('datesheet2/datesheet_without_syllabus', 'Datesheet2::datesheet_without_syllabus');
        $routes->get('datesheet2/data', 'Datesheet2::data');
        $routes->get('datesheet2/add', 'Datesheet2::add');
        $routes->get('datesheet2/edit', 'Datesheet2::edit');
        $routes->post('datesheet2/save', 'Datesheet2::save');
        $routes->post('datesheet2/selectSubjects', 'Datesheet2::selectSubjects');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('grades', 'Grades::index');
        $routes->post('grades/data', 'Grades::data');
        $routes->get('grades/add', 'Grades::add');
        $routes->get('grades/edit', 'Grades::edit');
        $routes->post('grades/save', 'Grades::save');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('grading-policy', 'GradingPolicy::index');
        $routes->post('grading-policy/data', 'GradingPolicy::data');
        $routes->post('grading-policy/data2', 'GradingPolicy::data2');
        $routes->get('grading-policy/add', 'GradingPolicy::add');
        $routes->get('grading-policy/edit', 'GradingPolicy::edit');
        $routes->post('grading-policy/save', 'GradingPolicy::save');
        $routes->get('grading-policy/delete', 'GradingPolicy::delete');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students-subject-results', 'StudentsSubjectResults::index');
        $routes->post('students-subject-results/data', 'StudentsSubjectResults::data');
        $routes->get('students-subject-results/add', 'StudentsSubjectResults::add');
        $routes->get('students-subject-results/edit', 'StudentsSubjectResults::edit');
        // Batch save
        $routes->post('students-subject-results/save', 'StudentsSubjectResults::save');
        // per-field auto-save endpoint
        $routes->post('students-subject-results/save-mark', 'StudentsSubjectResults::saveMark');
        $routes->post('students-subject-results/select-section-subject-by-section', 'StudentsSubjectResults::selectSectionSubjectbySection');
        $routes->post('students-subject-results/get-students', 'StudentsSubjectResults::get_students');
        $routes->post('students-subject-results/save-mark', 'StudentsSubjectResults::saveMark');
    });

    // Students Results - Admin Panel
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_results', 'StudentsResults::index');
        $routes->post('students_results/data', 'StudentsResults::data');
        $routes->get('students_results/add', 'StudentsResults::add');
        $routes->get('students_results/edit', 'StudentsResults::edit');
        $routes->post('students_results/save', 'StudentsResults::save');
        $routes->post('students_results/get_students', 'StudentsResults::get_students');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('test_series', 'TestSeries::index');
        $routes->post('test_series/data', 'TestSeries::data');
        $routes->get('test_series/add', 'TestSeries::add');
        $routes->get('test_series/edit', 'TestSeries::edit');
        $routes->post('test_series/save', 'TestSeries::save');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('tests', 'Tests::index');
        $routes->post('tests/data', 'Tests::data');
        $routes->get('tests/add', 'Tests::add');
        $routes->post('tests/save', 'Tests::save');
        $routes->post('tests/selecttestslist', 'Tests::selectTestsList');
        $routes->post('tests/selectsubjectbysection', 'Tests::selectSubjectbySection');
        $routes->get('tests/delete', 'Tests::delete'); 
        $routes->get('tests/listtests', 'Tests::listTests');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('test-results', 'TestResults::index');
        $routes->post('test_results/data', 'TestResults::data');
        $routes->get('test_results/add', 'TestResults::add');
        $routes->get('test_results/edit', 'TestResults::edit');
        $routes->post('test_results/save', 'TestResults::save');
        $routes->post('test_results/get_students', 'TestResults::get_students');
        $routes->post('admin/test-result-card/data', 'TestResults::cardData');
        $routes->post('test_results/get_subjects', 'TestResults::get_subjects');
        $routes->post('test_results/update', 'TestResults::update');
        $routes->post('test_results/list-tests', 'TestResults::listTests');
        $routes->post('test_results/delete-test', 'TestResults::deleteTest'); 
        $routes->get('test-result-card', 'TestResults::card', ['as' => 'test_result_card']);
        $routes->post('test-result-card/data', 'TestResults::cardData');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('test-series-result-card', 'TestSeriesResultCard::index');
        $routes->post('test_series_result_card/data', 'TestSeriesResultCard::data');
        $routes->post('test-result-card/list-tests', 'TestSeriesResultCard::listTests');
        $routes->post('test-result-card/delete-test', 'TestSeriesResultCard::deleteTest');
    });

    // Admin Students Enroll routes
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('students_enroll', 'StudentsEnroll::index');
        $routes->post('students_enroll/data', 'StudentsEnroll::data');
        $routes->post('students_enroll/enrollstudentinfo', 'StudentsEnroll::enrollStudentInfo');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('studentsbulk', 'Studentsbulk::index');
        $routes->post('studentsbulk/data', 'Studentsbulk::data');
        $routes->post('studentsbulk/selectclassfee', 'Studentsbulk::selectClassFee');
        $routes->post('studentsbulk/savestudent', 'Studentsbulk::saveStudent');
        $routes->post('studentsbulk/save', 'Studentsbulk::save');
    });

    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
        $routes->get('classwise_results', 'Classwise_results::index');
        $routes->post('classwise_results/data', 'Classwise_results::data');
    });

    // Public routes with localization
    $routes->group('', ['namespace' => 'App\Controllers\Frontend'], function($routes) {
        $routes->get('/', 'Main::index', ['as' => 'site_home']);
        $routes->get('login', 'Auth::login', ['as' => 'site_login']);
        $routes->get('events', 'Events::index', ['as' => 'site_events']);
        $routes->get('contact', 'Contact::index', ['as' => 'site_contact']);
    });

});

// ===============================
// LEGACY ROUTES (without locale for backward compatibility)
// ===============================

// Keep ALL original routes here for backward compatibility
// This ensures URLs without locale still work

$routes->get('admin', 'Home::getAdmin');

$routes->group('admin', function($routes) {
    $routes->post('login/changePassword', 'Admin\Login::changePassword');
    $routes->post('users/editPassword', 'Admin\Users::editPassword');
    $routes->post('calendar/save', 'Admin\Calendar::save');
    $routes->post('login/findPassword', 'Admin\Login::findPassword');
    $routes->post('permissions/save', 'Admin\Permissions::save');
    $routes->post('profile/updatePassword', 'Admin\Profile::updatePassword');
    $routes->post('roles/save', 'Admin\Roles::save');
    $routes->post('users/setPerms', 'Admin\Users::setPerms');
    $routes->post('users/save', 'Admin\Users::save');

    $routes->get('roles/data', 'Admin\Roles::data');
    $routes->get('users/data', 'Admin\Users::data');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('logout', 'Logout::index');
    $routes->get('login', 'Login::index');
    $routes->post('login/submit', 'Login::submit');
    $routes->get('dashboard', 'Dashboard::index');
});

// Admin Routes (legacy)
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('permissions', 'Permissions::index');
    $routes->get('permissions/add', 'Permissions::add');
    $routes->get('permissions/edit/(:num)', 'Permissions::edit/$1');
    $routes->match(['get', 'post'], 'permissions/save', 'Permissions::save');
    $routes->get('permissions/delete', 'Permissions::delete');
    $routes->match(['get', 'post'], 'permissions/data', 'Permissions::data');
    $routes->match(['get', 'post'], 'permissions/data2', 'Permissions::data2');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Academic Session Routes
    $routes->get('academic_session', 'AcademicSession::index');
    $routes->get('academic_session/add', 'AcademicSession::add');
    $routes->get('academic_session/edit/(:num)', 'AcademicSession::edit/$1');
    $routes->post('academic_session/data', 'AcademicSession::data');
    $routes->post('academic_session/save', 'AcademicSession::save');
    $routes->get('academic_session/delete', 'AcademicSession::delete');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('profile', 'Profile::index');
    $routes->post('profile/save', 'Profile::save');
    $routes->post('profile/update-password', 'Profile::update_password'); 
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('terms', 'Terms::index');
    $routes->post('terms/data', 'Terms::data');
    $routes->get('terms/add', 'Terms::add');
    $routes->get('terms/edit', 'Terms::edit');
    $routes->post('terms/save', 'Terms::save');
    $routes->get('terms/delete', 'Terms::delete');
    $routes->post('terms/toggle-status', 'Terms::toggleStatus');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('terms_session', 'TermsSession::index');
    $routes->post('terms_session/data', 'TermsSession::data');
    $routes->post('terms_session/data2', 'TermsSession::data2');
    $routes->get('terms_session/add', 'TermsSession::add');
    $routes->get('terms_session/edit', 'TermsSession::edit');
    $routes->post('terms_session/save', 'TermsSession::save');
    $routes->get('terms_session/delete', 'TermsSession::delete');
});

// Admin Term Weeks Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('term_weeks', 'TermWeeks::index');
    $routes->post('term_weeks/data', 'TermWeeks::data');
    $routes->get('term_weeks/add', 'TermWeeks::add');
    $routes->get('term_weeks/edit', 'TermWeeks::edit');
    $routes->post('term_weeks/save', 'TermWeeks::save');
    $routes->post('term_weeks/generate_term_weeks', 'TermWeeks::generate_term_weeks');
    $routes->get('term_weeks/delete', 'TermWeeks::delete');
});

// Admin Classes Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('classes', 'Classes::index');
    $routes->post('classes/data', 'Classes::data');
    $routes->get('classes/add', 'Classes::add');
    $routes->get('classes/edit', 'Classes::edit');
    $routes->post('classes/save', 'Classes::save');
    $routes->post('classes/toggle-status', 'Classes::toggleStatus');
});

// Admin Sections Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('sections', 'Sections::index');
    $routes->post('sections/data', 'Sections::data');
    $routes->get('sections/add', 'Sections::add');
    $routes->get('sections/edit', 'Sections::edit');
    $routes->post('sections/save', 'Sections::save');
    $routes->get('sections/delete', 'Sections::delete');
    $routes->post('sections/toggle-status', 'Sections::toggleStatus');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('class_section', 'ClassSection::index');
    $routes->post('class_section/data', 'ClassSection::data');
    $routes->post('class-section/data2', 'ClassSection::data2');
    $routes->post('class-section/update-class-section', 'ClassSection::updateClassSection');
    $routes->get('class_section/add', 'ClassSection::add');
    $routes->get('class_section/edit', 'ClassSection::edit');
    $routes->post('class_section/save', 'ClassSection::save');
    $routes->get('class_section/delete', 'ClassSection::delete');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Subjects module routes
    $routes->get('subjects', 'Subjects::index');
    $routes->post('subjects/data', 'Subjects::data');
    $routes->get('subjects/add', 'Subjects::add');
    $routes->get('subjects/edit', 'Subjects::edit');
    $routes->post('subjects/save', 'Subjects::save');
    $routes->get('subjects/delete', 'Subjects::delete');
    $routes->post('subjects/toggle-status', 'Subjects::toggleStatus');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('section_subjects', 'SectionSubjects::index');
    $routes->post('section_subjects/data', 'SectionSubjects::data');
    $routes->post('section_subjects/data2', 'SectionSubjects::data2');
    $routes->post('section_subjects/update', 'SectionSubjects::update');
    $routes->get('section_subjects/add', 'SectionSubjects::add');
    $routes->get('section_subjects/edit', 'SectionSubjects::edit');
    $routes->post('section_subjects/save', 'SectionSubjects::save');
    $routes->get('section_subjects/delete', 'SectionSubjects::delete');
    $routes->post('section_subjects/get-row', 'SectionSubjects::getRow');
    $routes->post('section_subjects/assign_teacher', 'SectionSubjects::assign_teacher');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students', 'Students::index');
    $routes->post('students/data', 'Students::data');
    $routes->get('students/add', 'Students::add');
    $routes->get('students/edit', 'Students::edit');
    $routes->get('students/delete', 'Students::delete');
    $routes->get('students/getStatus', 'Students::getStatus');

    $routes->post('students/save_basicinfo', 'Students::save_basicinfo');
    $routes->post('students/save_admission', 'Students::save_admission');
    $routes->post('students/get_fee_amount', 'Students::get_fee_amount');
    $routes->post('ajax/get_class_fee_amounts', 'Students::get_class_fee_amounts');

    $routes->post('students/save_contactinfo', 'Students::save_contactinfo');
    $routes->post('students/save_generalinfo', 'Students::save_generalinfo');
    $routes->post('students/save_attachment', 'Students::save_attachment');
    $routes->post('students/check_parent_cnic', 'Students::check_parent_cnic');

    $routes->post('students/updateDiscounts', 'Students::updateDiscounts');
    $routes->get('students/addbulk', 'Students::addbulk');
    $routes->post('students/uploadImage', 'Students::uploadImage');
    $routes->post('students/get-sibling', 'Students::getSibling');

    $routes->post('students/get_parentinfo', 'Students::get_parentinfo');
    $routes->post('admin/fee-chalan-pay/get-studentinfo', 'Admin\FeeChalanPay::getStudentinfo');
    $routes->post('admin/fee-chalan-pay1/get-studentinfo', 'Admin\FeeChalanPay1::getStudentinfo');
    $routes->get('fee-chalan-pay1', 'FeeChalan::pay', ['as' => 'fee_chalan_pay1']);
    $routes->post('admin/fee-chalan-pay/get-chalans', 'Admin\FeeChalanPay::get_chalans');

    $routes->post('students/getParentInfo', 'Students::getParentInfo');
    $routes->post('students/update-parent-info', 'Students::updateParentInfo');

    $routes->match(['get', 'post'], 'students/import', 'Students::import');
});

// Continue with ALL remaining routes from your original file...
// [Add all the remaining route groups here following the same pattern]

// API routes (typically don't need localization)
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // API routes would go here
});

// Settings route
$routes->post('settings/set-currency', 'Settings::setCurrency');

// Frontend routes (legacy)
$routes->group('', ['namespace' => 'App\Controllers\Frontend'], function($routes) {
    $routes->get('/', 'Main::index', ['as' => 'site_home']);
    $routes->get('login', 'Auth::login', ['as' => 'site_login']);
    $routes->get('events', 'Events::index', ['as' => 'site_events']);
    $routes->get('contact', 'Contact::index', ['as' => 'site_contact']);
});