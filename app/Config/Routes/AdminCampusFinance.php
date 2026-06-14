<?php

/**
 * AdminCampusFinance
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
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
    $routes->get('/', 'DefaulterMessage::index');                 // Load main page
    $routes->post('data', 'DefaulterMessage::data');              // Fetch filtered defaulter data
    $routes->post('save', 'DefaulterMessage::save');              // Save student messages
    $routes->get('parent_sms', 'DefaulterMessage::parent_sms');   // Load parent SMS view
    $routes->post('saveparent', 'DefaulterMessage::saveparent');  // Save messages for parents
    $routes->get('delete', 'DefaulterMessage::delete');           // Delete class entry (if used)
});

$routes->group('admin/result-message', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'ResultMessage::index');     // Load main view
    $routes->post('data', 'ResultMessage::data');  // Load message form
    $routes->post('save', 'ResultMessage::save');  // Save SMS messages
});

$routes->group('admin/students-list', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'StudentsList::index');                // Load students contact list view
    $routes->post('data', 'StudentsList::data');            // Load DataTables student rows
    $routes->post('get-parentinfo', 'StudentsList::get_parentinfo'); // AJAX: parent autocomplete
    $routes->post('get-studentinfo', 'StudentsList::get_studentinfo'); // AJAX: student autocomplete
});

$routes->group('admin/students-w-result-list', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'StudentsWithResultList::index');                     // Load students result list view
    $routes->post('data', 'StudentsWithResultList::data');                 // Load DataTables student rows
    $routes->post('get-parentinfo', 'StudentsWithResultList::getParentInfo'); // AJAX: parent autocomplete
    $routes->post('get-studentinfo', 'StudentsWithResultList::getStudentInfo'); // AJAX: student autocomplete
});

$routes->group('admin/family-chalan-whatsapp', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('/', 'FamilyChalanWhatsapp::index');              // Loads main view
    $routes->post('data', 'FamilyChalanWhatsapp::data');           // DataTables response
    $routes->post('get-parentinfo', 'FamilyChalanWhatsapp::get_parentinfo'); // Parent autocomplete
    $routes->post('get-studentinfo', 'FamilyChalanWhatsapp::get_studentinfo'); // Student autocomplete
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
    $routes->post('profile-student/student-health-data', 'ProfileStudent::studentHealthData');
    $routes->post('profile-student/student-result-data', 'ProfileStudent::studentResultData');
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
    // $routes->post('check_parent_cnic', 'Ajax::check_parent_cnic');
    $routes->post('select-class-fee', 'Ajax::selectClassFee');
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
    $routes->get('student_id_card_new', 'StudentIdCardNew::index');
    $routes->post('student_id_card_new/data', 'StudentIdCardNew::data');
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

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes) {
    $routes->get('parents_prevfee', 'Parents_prevfee::index');
    $routes->post('parents_prevfee/data', 'Parents_prevfee::data');
    $routes->post('parents_prevfee/selectClassFee', 'Parents_prevfee::selectClassFee');
    $routes->post('parents_prevfee/saveStudent', 'Parents_prevfee::saveStudent');
    $routes->post('parents_prevfee/save', 'Parents_prevfee::save');
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
    $routes->get('expense_head/edit', 'ExpenseHead::edit'); // expects ?id=XX
    $routes->post('expense_head/save', 'ExpenseHead::save');
    $routes->get('expense_head/delete', 'ExpenseHead::delete'); // expects ?id=XX
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('expenses', 'Expenses::index');
    $routes->post('expenses/data', 'Expenses::data');
    $routes->post('expenses/get-expenses', 'Expenses::getExpenses');
    $routes->get('expenses/add', 'Expenses::add');
    $routes->get('expenses/edit', 'Expenses::edit'); // expects ?id=XX
    $routes->post('expenses/save', 'Expenses::save');
    $routes->get('expenses/delete', 'Expenses::delete'); // expects ?id=XX
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('asset_heads', 'AssetHeads::index');
    $routes->post('asset_heads/data', 'AssetHeads::data');
    $routes->get('asset_heads/add', 'AssetHeads::add');
    $routes->get('asset_heads/edit', 'AssetHeads::edit'); // expects ?id=XX
    $routes->post('asset_heads/save', 'AssetHeads::save');
    $routes->get('asset_heads/delete', 'AssetHeads::delete'); // expects ?id=XX
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
    $routes->get('grades/setup', 'Grades::setup');
    $routes->post('grades/save-setup', 'Grades::saveSetup');
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

    // Batch save (you already have this and can keep it)
    $routes->post('students-subject-results/save', 'StudentsSubjectResults::save');

    // 🔹 NEW: per-field auto-save endpoint (uses active exam automatically)
    $routes->post('students-subject-results/save-mark', 'StudentsSubjectResults::saveMark');

    $routes->post('students-subject-results/select-section-subject-by-section', 'StudentsSubjectResults::selectSectionSubjectbySection');
    $routes->post('students-subject-results/get-students', 'StudentsSubjectResults::get_students');
    $routes->post('students-subject-results/save-mark', 'StudentsSubjectResults::saveMark');
});

// Students Results - Admin Panel
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('students_results', 'StudentsResults::index');           // Show main results page
    $routes->post('students_results/data', 'StudentsResults::data');      // Data endpoint for DataTables/ajax
    $routes->get('students_results/add', 'StudentsResults::add');         // Add new student result form
    $routes->get('students_results/edit', 'StudentsResults::edit');       // Edit form (expects ?id= in URL)
    $routes->post('students_results/save', 'StudentsResults::save');      // Save (insert/update)
    $routes->post('students_results/get_students', 'StudentsResults::get_students'); // Get students/subjects form HTML
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('test_series', 'TestSeries::index');
    $routes->post('test_series/data', 'TestSeries::data');
    $routes->get('test_series/add', 'TestSeries::add');
    $routes->get('test_series/edit', 'TestSeries::edit'); // expects ?id=
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
    $routes->get('tests/listtests', 'Tests::listTests'); // expects ?id=
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('test-results', 'TestResults::index');

    $routes->post('test_results/data', 'TestResults::data');
    $routes->get('test_results/add', 'TestResults::add');
    $routes->get('test_results/edit', 'TestResults::edit'); // expects ?id=
    $routes->post('test_results/save', 'TestResults::save');
    $routes->post('test_results/get_students', 'TestResults::get_students');
    $routes->post('admin/test-result-card/data', 'TestResults::cardData');
    $routes->post('test_results/get_subjects', 'TestResults::get_subjects');
    $routes->post('test_results/update', 'TestResults::update');
    $routes->post('test_results/list-tests',     'TestResults::listTests');   // <-- points to TestResults
    $routes->post('test_results/delete-test',    'TestResults::deleteTest');

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
        $routes->get('studentsbulkparents', 'StudentsbulkParents::index');
        $routes->post('studentsbulkparents/data', 'StudentsbulkParents::data');
        $routes->post('studentsbulkparents/selectclassfee', 'StudentsbulkParents::selectClassFee');
        $routes->post('studentsbulkparents/savestudent', 'StudentsbulkParents::saveStudent');
        $routes->post('studentsbulkparents/save', 'StudentsbulkParents::save');
            $routes->post('studentsbulkparents/get-siblings', 'StudentsbulkParents::getSiblings');

$routes->post('studentsbulkparents/relink', 'StudentsbulkParents::relink');

 $routes->post('studentsbulkparents/get-siblings', 'StudentsbulkParents::getSiblings');
    $routes->post('studentsbulkparents/relink', 'StudentsbulkParents::relink');
    $routes->post('studentsbulkparents/get-student-parent', 'StudentsbulkParents::getStudentParent');
    $routes->get('studentsbulkparents/search-parents-by-name', 'StudentsbulkParents::searchParentsByName');
    $routes->post('studentsbulkparents/get-parent-details', 'StudentsbulkParents::getParentDetails');


    });


$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('classwise_results', 'Classwise_results::index');
    $routes->post('classwise_results/data', 'Classwise_results::data');
});

$routes->post('studentsbulkparents/get-student-parent', 'StudentsbulkParents::getStudentParent');
$routes->get('studentsbulkparents/search-parents-by-name', 'StudentsbulkParents::searchParentsByName');
$routes->post('studentsbulkparents/get-parent-details', 'StudentsbulkParents::getParentDetails');
$routes->post('studentsbulkparents/relink', 'StudentsbulkParents::relink');
