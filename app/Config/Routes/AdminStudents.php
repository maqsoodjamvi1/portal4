<?php

/**
 * AdminStudents
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students', 'Students::index');
    $routes->post('students/data', 'Students::data');
    $routes->get('students/add', 'Students::add');
    $routes->get('students/edit', 'Students::edit');
    $routes->get('students/delete', 'Students::delete');
    $routes->get('students/getStatus', 'Students::getStatus');


  // Readmit Student Routes
    $routes->get('students/readmit', 'Students::readmit');
    $routes->post('students/search_drop_students', 'Students::search_drop_students');
    $routes->post('students/get_student_readmit_info', 'Students::get_student_readmit_info');
    $routes->post('students/process_readmission', 'Students::process_readmission');
    $routes->post('students/get_fee_history', 'Students::get_fee_history');


    $routes->post('students/save_basicinfo', 'Students::save_basicinfo');
    $routes->post('students/save_admission', 'Students::save_admission');
    $routes->post('students/update_basicinfo', 'Students::update_basicinfo');
    $routes->post('students/search_siblings', 'Students::search_siblings');
$routes->post('students/get_parent_info', 'Students::get_parent_info');

   // $routes->post('admin/students/save_admission', 'Admin\\Students::save_admission');

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
    $routes->post('admin/fee-chalan-pay/get-studentinfo', 'Admin\FeeChalanPay::get_studentinfo');
$routes->post('admin/fee-chalan-pay/get-student-info', 'Admin\FeeChalanPay::get_studentinfo');
    $routes->post('admin/fee-chalan-pay1/get-studentinfo', 'Admin\FeeChalanPay1::getStudentinfo');
     $routes->get('fee-chalan-pay1', 'FeeChalan::pay', ['as' => 'fee_chalan_pay1']);
     $routes->post('admin/fee-chalan-pay/get-chalans', 'Admin\FeeChalanPay::get_chalans');



    $routes->post('students/getParentInfo', 'Students::getParentInfo');
    $routes->post('students/update-parent-info', 'Students::updateParentInfo');

    $routes->match(['GET', 'POST'], 'students/import', 'Students::import');
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // ... your existing routes ...

    // Delete Fee Chalan routes
    $routes->get('delete-fee-chalan', 'DeleteFeeChalan::index');
    $routes->post('delete-fee-chalan/delete', 'DeleteFeeChalan::delete');
    $routes->post('delete-fee-chalan/delete-selected', 'DeleteFeeChalan::deleteSelected');
});


// Alternative routes (optional)
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('students/search-by-name', 'StudentsBulkParentInfo::searchByName');
    $routes->post('students/by-parent', 'StudentsBulkParentInfo::byParent');
});






$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // existing…
    $routes->get('students_bulk_make_current', 'StudentsBulkMakeCurrent::index');

    $routes->get('students_bulk_make_current/search-by-name', StudentsBulkMakeCurrent::class.'::searchByName', ['as' => 'students_search_by_name']);

    // ✅ FIXED: no ::class here
    $routes->match(['POST','GET'],
        'students_bulk_make_current/data',
        'StudentsBulkMakeCurrent::data',
        ['as' => 'students_bulk_make_current']
    );


    $routes->post('students_bulk_make_current/make-current', StudentsBulkMakeCurrent::class.'::makeCurrent', ['as' => 'make_current']);
    $routes->post('students_bulk_make_current/save_student_info', 'StudentsBulkMakeCurrent::saveStudentInfo', ['as' => 'students_bulk_info_save']);

    // already-added canonical routes

    $routes->post('students_bulk_make_current/by-parent',     'StudentsBulkMakeCurrent::byParent',     ['as' => 'students_by_parent']);

    $routes->get('students_bulk_make_current/search-by-name', 'StudentsBulkMakeCurrent::searchByName');
    // NEW: aliases so /admin/students/... also works
    $routes->get('students/search-by-name', 'StudentsBulkMakeCurrent::searchByName');
    $routes->post('students/by-parent',     'StudentsBulkMakeCurrent::byParent');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // existing…
    $routes->get('students_bulk_info', 'StudentsBulkInfo::index');
    $routes->match(['POST','GET'], 'students_bulk_info/lookup_parent_by_cnic', 'StudentsBulkInfo::lookup_parent_by_cnic', ['as' => 'lookup_parent_by_cnic']);
    $routes->post('students_bulk_info/data', 'StudentsBulkInfo::data');
    $routes->post('students_bulk_info/save_student_info', 'StudentsBulkInfo::saveStudentInfo');

    // already-added canonical routes
    $routes->get('students_bulk_info/search-by-name', 'StudentsBulkInfo::searchByName');
    $routes->post('students_bulk_info/by-parent',     'StudentsBulkInfo::byParent',     ['as' => 'students_by_parent']);

    // NEW: aliases so /admin/students/... also works
    $routes->get('students/search-by-name', 'StudentsBulkInfo::searchByName');
    $routes->post('students/by-parent',     'StudentsBulkInfo::byParent');
});




$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // existing…
    $routes->get('students_bulk_info_date_of_birth', 'StudentsBulkInfoDateOfBirth::index');

    $routes->post('students_bulk_info_date_of_birth/data', 'StudentsBulkInfoDateOfBirth::data');


    $routes->post('students_bulk_info_date_of_birth/save_student_info', 'StudentsBulkInfoDateOfBirth::saveStudentInfo');

    // already-added canonical routes
    $routes->get('students_bulk_info_date_of_birth/search-by-name', 'StudentsBulkInfoDateOfBirth::searchByName', ['as' => 'students_search_by_name']);
    $routes->post('students_bulk_info_date_of_birth/by-parent',     'StudentsBulkInfoDateOfBirth::byParent',     ['as' => 'students_by_parent']);

    // NEW: aliases so /admin/students/... also works
    $routes->get('students/search-by-name', 'StudentsBulkInfoDateOfBirth::searchByName');
    $routes->post('students/by-parent',     'StudentsBulkInfoDateOfBirth::byParent');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('students_bulk_photos', 'StudentsBulkPhotos::index');
    $routes->post('students_bulk_photos/data', 'StudentsBulkPhotos::data');
    $routes->post('students_bulk_photos/save_photo', 'StudentsBulkPhotos::savePhoto');
    $routes->get('students_bulk_photos/search-by-name', 'StudentsBulkPhotos::searchByName');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // existing…
    $routes->get('students_bulk_fee_info', 'StudentsBulkFeeInfo::index');

    $routes->match(['POST','GET'], 'students_bulk_fee_info/lookup_parent_by_cnic', 'StudentsBulkFeeInfo::lookup_parent_by_cnic', ['as' => 'lookup_parent_by_cnic']);
    $routes->post('students_bulk_fee_info/data', 'StudentsBulkFeeInfo::data');
    $routes->post('students_bulk_fee_info/save_student_info', 'StudentsBulkFeeInfo::saveStudentInfo', ['as' => 'students_bulk_fee_info_save']);

    // already-added canonical routes
    $routes->get('students_bulk_fee_info/search-by-name', 'StudentsBulkFeeInfo::searchByName', ['as' => 'students_search_by_name']);
    $routes->post('students_bulk_fee_info/by-parent',     'StudentsBulkFeeInfo::byParent',     ['as' => 'students_by_parent']);

    // NEW: aliases so /admin/students/... also works
    $routes->get('students/search-by-name', 'StudentsBulkFeeInfo::searchByName');
    $routes->post('students/by-parent',     'StudentsBulkFeeInfo::byParent');
});



$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Backward-compat: accept both underscore and hyphen variants
    $routes->get('students_print', 'StudentsPrint::index');
    $routes->get('students-print', 'StudentsPrint::index');
    $routes->get('students_print/default-view', 'StudentsPrint::defaultView');
    $routes->post('students_print/save-view', 'StudentsPrint::saveView');
    $routes->post('students_print/data', 'StudentsPrint::data');
    $routes->post('students-print/data', 'StudentsPrint::data');
    $routes->get('students_print/autocomplete-student', 'StudentsPrint::autocomplete_student');
    $routes->get('students_print/autocomplete-father', 'StudentsPrint::autocomplete_father');
    $routes->get('students-print/autocomplete-student', 'StudentsPrint::autocomplete_student');
    $routes->get('students-print/autocomplete-father', 'StudentsPrint::autocomplete_father');
    $routes->get('students_print/stats', 'StudentsPrint::stats');  // <-- FIXED: Use GET or POST consistently
    $routes->get('students_print/bonafide-certificate', 'StudentsPrint::bonafideCertificate');
    $routes->get('students_print/contact-list', 'StudentsPrint::contactListPrint');
    $routes->get('students-print/contact-list', 'StudentsPrint::contactListPrint');
    $routes->get('students_print/section-roster', 'StudentsPrint::sectionRosterPrint');
    $routes->get('students-print/section-roster', 'StudentsPrint::sectionRosterPrint');
    $routes->post('students/get_class_fee_amounts', 'Students::get_class_fee_amounts');
    $routes->post('students_print/stats', 'StudentsPrint::stats');  // <-- FIXED: Changed from Students_print::stats to StudentsPrint::stats
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('addbulkstudents/add', 'AddBulkStudents::add');
    $routes->post('addbulkstudents/save', 'AddBulkStudents::save');

    // accept both hyphen and underscore, so it never breaks again
    $routes->post('addbulkstudents/select-student-by-class-section',  'AddBulkStudents::selectStudentByClassSection'); // hyphen
    $routes->post('addbulkstudents/drop-student', 'AddBulkStudents::dropStudent');

    $routes->post('addbulkstudents/generate-slc', 'AddBulkStudents::generateSlc');
    $routes->post('addbulkstudents/check-existing-slc', 'AddBulkStudents::checkExistingSlc');
    $routes->get('addbulkstudents/download-slc/(:num)', 'AddBulkStudents::downloadSlc/$1');

    $routes->post('addbulkstudents/search-slc', 'AddBulkStudents::searchSlc');

    $routes->get('slc/view/(:num)', 'AddBulkStudents::viewSlc/$1');

    $routes->post('addbulkstudents/update-student-info', 'AddBulkStudents::updateStudentInfo');
    $routes->post('addbulkstudents/get-edit-form', 'AddBulkStudents::getEditForm');

    $routes->get('addbulkstudents/edit', 'AddBulkStudents::edit');
    $routes->post('addbulkstudents/get-school-settings', 'AddBulkStudents::getSchoolSettings');
    $routes->post('addbulkstudents/get-student-details', 'AddBulkStudents::getStudentDetails');
    $routes->post('addbulkstudents/select-student-by-class_section',  'AddBulkStudents::selectStudentByClassSection'); // underscore

    // ========== ADD THIS NEW ROUTE ==========
    $routes->post('addbulkstudents/update-student-name', 'AddBulkStudents::updateStudentName');
});


// Activity Report Routes
$routes->group('admin/activity-report', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('teacher-report', 'ActivityReport::teacherReport');
    $routes->get('principal-report', 'ActivityReport::principalReport');
    $routes->post('add-media-link', 'ActivityReport::addActivityMediaLink');
    $routes->post('submit-review', 'ActivityReport::submitReview');
    $routes->get('get-activity-details', 'ActivityReport::getActivityDetails');
});

$routes->get('admin/debug-teacher-activities', 'Admin\ActivityReport::debugTeacherActivities');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_bulk_cnic', 'Students_bulk_cnic::index');
    $routes->post('students_bulk_cnic/data', 'Students_bulk_cnic::data');
    $routes->post('students_bulk_cnic/update-parent-info', 'Students_bulk_cnic::updateParentInfo');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('studentsbulkcsv', 'Studentsbulkcsv::index');
    $routes->post('studentsbulkcsv/data', 'Studentsbulkcsv::data');

      $routes->post('studentsbulkcsv/import', 'StudentsBulkCSV::import'); // ✅ exact match
     $routes->get('studentsbulkcsv/addbulk', 'StudentsBulkCSV::addbulk');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_bulk_contacts', 'Students_bulk_contacts::index');
    $routes->post('students_bulk_contacts/data', 'Students_bulk_contacts::data');
    $routes->post('students_bulk_contacts/savestudentcontacts', 'Students_bulk_contacts::saveStudentContacts');
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    // Page
    $routes->get('student_class', 'StudentClass::index');

    // Existing (underscore)
    $routes->post('student_class/data',               'StudentClass::data');
    $routes->get('student_class/add',                 'StudentClass::add');
    $routes->get('student_class/edit',                'StudentClass::edit');
    $routes->post('student_class/save',               'StudentClass::save');
    $routes->post('student_class/getStdCurrentClass', 'StudentClass::getStdCurrentClass');

    // New APIs (underscore)
    $routes->post('student_class/fetch-students', 'StudentClass::fetchStudents');
    $routes->post('student_class/move',           'StudentClass::move');
    $routes->post('student_class/move-bulk',      'StudentClass::moveBulk');
    $routes->post('student_class/activate-class-enrollment', 'StudentClass::activateClassEnrollment');

    // New APIs (dashed aliases) — to match your JS calls
    $routes->post('student-class/fetch-students', 'StudentClass::fetchStudents');
    $routes->post('student-class/move',           'StudentClass::move');
    $routes->post('student-class/move-bulk',      'StudentClass::moveBulk');
    $routes->post('student-class/activate-class-enrollment', 'StudentClass::activateClassEnrollment');
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
    $routes->get('users/check-availability', 'Users::checkAvailability');
    $routes->post('users/save', 'Users::save'); // Add this if you're saving from the add/edit form
    $routes->get('users/get-teacher-subjects/(:num)', 'Users::getTeacherSubjects/$1');
    $routes->post('users/assign-subject', 'Users::assignSubject');
    $routes->get('users/get-teacher-classes/(:num)', 'Users::getTeacherClasses/$1');
    $routes->post('users/assign-class-teacher', 'Users::assignClassTeacher');
    $routes->get('users/getEmployeeImage/(:any)', 'Users::getEmployeeImage/$1');

    $routes->get('users/delete/(:num)', 'Users::delete/$1');
    $routes->get('users/edit_password', 'Users::edit_password');
    $routes->post('users/edit_password', 'Users::edit_password');
    $routes->get('users/set_perms', 'Users::set_perms');
    $routes->post('users/set_perms', 'Users::set_perms');
    $routes->post('users/perm_data', 'Users::perm_data');
});
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Teacher Subjects routes
    $routes->get('teacher_subjects', 'TeacherSubjects::index');
    $routes->get('teacher_subjects/getData', 'TeacherSubjects::getData');
    $routes->get('teacher_subjects/getSectionTeachers', 'TeacherSubjects::getSectionTeachers'); // ADD THIS
    $routes->post('teacher_subjects/saveAll', 'TeacherSubjects::saveAll'); // ADD THIS
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
    $routes->get('emp_timing/edit', 'EmpTiming::edit'); // optional: use segment for ID if needed
    $routes->post('emp_timing/save', 'EmpTiming::save');
    $routes->get('emp_timing/delete', 'EmpTiming::delete'); // optional: use segment for ID
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('fee_setup', 'FeeSetup::index');
    $routes->get('fee_type', 'FeeType::index');
    $routes->get('fee_type/add', 'FeeType::add');
    $routes->get('fee_type/edit', 'FeeType::edit');
    $routes->post('fee_type/save', 'FeeType::save');
    $routes->get('fee_type/delete', 'FeeType::delete');
    $routes->post('fee_type/data', 'FeeType::data');
     $routes->post('fee_type/data2', 'FeeType::data2');
     $routes->post('fee_type/toggle-status', 'FeeType::toggleStatus');
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
    $routes->post('schoolsetup/saveWizardData', 'School_Wizard::saveWizardData'); // Final Submit
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('fee_amount', 'FeeAmount::index');               // Loads fee_amount view
    $routes->get('fee_amount/add', 'FeeAmount::add');             // Loads add form
    $routes->get('fee_amount/edit', 'FeeAmount::edit');           // Loads edit form
    $routes->post('fee_amount/save', 'FeeAmount::save');          // Form submission (save)
    $routes->post('fee_amount/data', 'FeeAmount::data');          // AJAX request for fee structure table
});


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // …your other routes…

    // Main URL you’re calling
    $routes->get('fee-chalan-pay1', 'FeeChalan::pay1', ['as' => 'fee_chalan_pay1']);

    // Optional spellings/fallbacks (uncomment if you might hit these)
    // $routes->get('fee-challan-pay1', 'FeeChalan::pay1');      // “challan” spelling
    // $routes->get('fee-chalan/pay1',  'FeeChalan::pay1');      // slash variant
    // If your class is actually FeeChallan (double “l”), use:
    // $routes->get('fee-chalan-pay1', 'FeeChallan::pay1');
});
