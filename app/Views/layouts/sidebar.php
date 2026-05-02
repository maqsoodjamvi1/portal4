<?php
// sidebar.php - CLEANED VERSION (no navbar inside)
// Make sure your variables are available
$user = $user ?? null;
$role_name_info = $role_name_info ?? null;
$schoolinfo = $schoolinfo ?? null;
$campuses = $campuses ?? [];
$academic_sessions = $academic_sessions ?? [];
$curr_campus_id = $curr_campus_id ?? 0;
$curr_session_id = $curr_session_id ?? 0;
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-orange elevation-4" <?php if($_SERVER['HTTP_HOST'] == 'trial.timesoftsol.com'){ ?> style="top: 24px;" <?php } ?>>
  

  
  <!-- Sidebar -->
  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">
        <li>
          <div class="image text-center">
            <?php if($schoolinfo && !empty($schoolinfo->logo)): ?> 
              <img style="height: 70px; text-align: center;max-width: 100%;" src="<?= base_url() . 'system-logo/' . $schoolinfo->logo ?>">
            <?php endif; ?>
          </div>
        </li>
        <li class="nav-item">
          <a href="<?= site_url('') ?>" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-th"></i>
            <p>Profiles
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
             <li class="nav-item">
              <a href="<?php echo '#/profile';?>" class="nav-link">
                 <i class="nav-icon fas fa-th"></i>
                 <p>User Profile</p>
              </a>
            </li>
            <?php if(hasPermission('admin-add-campus-profile')){ ?> 
            <li class="nav-item">
              <a href="<?php echo '#/profile_campus';?>" class="nav-link">
                 <i class="nav-icon fas fa-th"></i>
                 <p>Campus Profile</p>
              </a>
            </li>
             <?php } ?>
            <?php if(hasPermission('admin-add-system-profile')){ ?> 
            <li class="nav-item">
              <a class="nav-link" href="<?php echo '#/profile_system';?>">
                 <i class="nav-icon fas fa-th"></i>
                 <p>System Profile</p>
              </a>
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php if(hasPermission('admin-enquiry')){ ?> 
        <li class="nav-item">
          <a href="<?php echo '#/admission_enquiry';?>" class="nav-link">
            <i class="nav-icon fas fa-th"></i>
            <p>Admission Enquiry
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-term-weeks') || hasPermission('admin-terms') || hasPermission('admin-add-academic-session') || hasPermission('admin-academic-session') || hasPermission('admin-terms-sessions')){ ?>
        <li class="nav-item">
          <a class="nav-link" href="#">
            <i class="nav-icon fas fa-cogs"></i> 
            <p>
              Sessions
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-academic-session')){ ?> 
            <li class="nav-item"> 
              <a href="<?php echo '#/academic_session';?>" class="nav-link"> 
                <i class="nav-icon fa fa-calendar"></i> 
                <p>Academic Sessions</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-terms')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/terms';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Terms</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-terms-sessions')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/terms_session';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Term Sessions</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-term-weeks')){ ?>  
            <li class="nav-item"> 
              <a href="<?php echo '#/term_weeks';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Term Weeks</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-classes') || hasPermission('admin-sections') || hasPermission('admin-subjects') || hasPermission('admin-class-subjects') ||  hasPermission('admin-class-section')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-list-alt" aria-hidden="true"></i> 
            <p>Classes
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-classes')){ ?>
            <li class="nav-item">
              <a href="<?php echo '#/classes';?>" class="nav-link">
                <i class="nav-icon fa fa-list"></i> 
                <p>Classes</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-sections')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/sections';?>" class="nav-link"> 
                <i class="nav-icon fa fa-flask"></i> 
                <p>Sections</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-class-section')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/class_section';?>" class="nav-link"> 
                <i class="nav-icon fa fa-flask"></i> 
                <p>Class Sections</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-subjects')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/subjects';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Subjects</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-section-subjects')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/section_subjects'; ?>" class="nav-link">
                <i class="nav-icon fa fa-list"></i> 
                <p>Section Subjects</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>   
        <?php if(hasPermission('admin-students') || hasPermission('admin-student-class') ){ ?>  
          <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Students
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-students')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students?status=1';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Enrolled Students
                </p> 
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_print?status=1';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Students Print List
                </p> 
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/students?m=add';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Admission
                </p> 
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/addbulkstudents?m=add';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Add Bulk Students 
                </p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-student-id-cards')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/student_id_card';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Student ID Card</p> 
              </a> 
            </li> 
            <?php } ?>
            <?php if(hasPermission('admin-students-contact-list')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_contact_list?status=1';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Contact List</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students-contact-list')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_defaulters_list?status=1';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Defaulters List</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-defaulter-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/defaulter_students_fee_report';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Defaulters Report by Fee Type</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-defaulter-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_prevfee';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Student Prev Fee Report</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-defaulter-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/parents_prevfee';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Family Prev Fee Report</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-student-class')){ ?>
            <li class="nav-item"> 
              <a href="#/student_class" class="nav-link"> 
                <i class="nav-icon fas fa-users"></i> 
                <p>Promotion</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-attachment-types')){ ?>
            <li class="nav-item"> 
              <a href="#/attachment_types" class="nav-link"> 
                <i class="nav-icon fas fa-users"></i> 
                <p>Attachment Types</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students')){ ?>
            <li class="nav-item"> 
              <a href="#/student_data_verification_form" class="nav-link"> 
                <i class="nav-icon fas fa-users"></i> 
                <p>Data Verification Form</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students')){ ?>
            <li class="nav-item"> 
              <a href="#/student_data_verification_form?m=fee_verification" class="nav-link"> 
                <i class="nav-icon fas fa-users"></i> 
                <p>Fee Verification Form</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-messages')){ ?>  
          <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Messages
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-update-message-templates')){ ?> 
            <li class="nav-item">
              <a class="nav-link" href="<?php echo '#/message_templates';?>">
                 <i class="nav-icon fas fa-th"></i>
                 <p>Message Templates</p>
              </a>
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-messages')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/messages';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Messages
                </p> 
              </a> 
            </li>
            <?php if(hasPermission('admin-bulk-messages')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/bulk_sms';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Bulk Excel SMS
                </p> 
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-defaulter-message')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/defaulter_message';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Defaulter SMS
                </p> 
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-result-message')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/result_message';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Result SMS
                </p> 
              </a> 
            </li>
          <?php } ?>
          <?php } ?>            
          </ul>
        </li>
        <?php } ?>
         <?php if(hasPermission('admin-messages')){ ?>  
          <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Whatsapp Messages
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
           
           <?php if(hasPermission('admin-messages')){ ?>
             <?php if(hasPermission('admin-result-message')){ ?>
              <li class="nav-item"> 
                <a href="<?php echo '#/students_list?status=1';?>" class="nav-link">  
                  <i class="nav-icon fas fa-users"></i> 
                  <p>Send Test Series Result
                  </p> 
                </a> 
              </li>
              <li class="nav-item"> 
                <a href="<?php echo '#/students_w_result_list?status=1';?>" class="nav-link">  
                  <i class="nav-icon fas fa-users"></i> 
                  <p>Send Result
                  </p> 
                </a> 
              </li>
              <li class="nav-item"> 
                <a href="<?php echo '#/family_chalan_whatsapp';?>" class="nav-link">  
                  <i class="nav-icon fas fa-users"></i> 
                  <p>Send Fee Chalan</p> 
                </a> 
              </li>
              <li class="nav-item"> 
                <a href="<?php echo '#/family_diary_whatsapp';?>" class="nav-link">  
                  <i class="nav-icon fas fa-users"></i> 
                  <p>Send Daily Diary</p> 
                </a> 
              </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-attendance')){ ?>
            <li class="nav-item"> 
              <a href="#/students_attendance?m=report" class="nav-link"> 
                <i class="nav-icon far fa-address-card"></i> 
                <p>Students Absentees Report</p> 
              </a> 
            </li>
            <?php } ?>
          
            <?php } ?>            
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-add-teacher-subject') || hasPermission('admin-add-teacher-section') || hasPermission('admin-users') || hasPermission('admin-permissions')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-user"></i>
            <p>Faculty
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-users')){ ?>
            <li class="nav-item">
              <a href="<?php echo '#/users?status=1';?>" class="nav-link">
                <i class="nav-icon fa fa-user"></i> 
                <p>Employees</p>
              </a>
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-teacher-subject')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/teacher_subjects?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-book"></i> 
                <p>Subject Teachers
                </p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-teacher-section')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/teacher_section?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-book"></i> 
                <p>Section Incharges</p> 
              </a> 
            </li>  
            <?php } ?> 
            <?php if(hasPermission('admin-add-teacher-section')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/emp_timing?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-clock"></i> 
                <p>Employee Timing</p> 
              </a> 
            </li>  
            <?php } ?> 
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-fee-type') || hasPermission('admin-fee-amount') || hasPermission('admin-fee-chalan') || hasPermission('admin-fee-chalan-balance')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-receipt"></i> 
            <p>Fee Management
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-fee-type')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/fee_type';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Fee Type</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-plan-months')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/fee_plan_months?m=add';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Fee Plan Months</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-amount')){?>
            <li class="nav-item"> 
              <a href="<?php echo '#/fee_amount?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-calendar"></i> 
                <p>Fee Structure</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-fee-chalan')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/fee_chalan';?>" class="nav-link"> 
                <i class="nav-icon fas fa-file-invoice"></i> 
                <p>Print Fee Chalan</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-chalan')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/fee_chalan?m=add';?>" class="nav-link"> 
                <i class="nav-icon fas fa-file-invoice"></i> 
                <p>Generate Fee Chalan</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-chalan')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/fee_chalan_pay';?>" class="nav-link"> 
                <i class="nav-icon fas fa-file-invoice"></i> 
                <p>Pay Fee Chalan</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-del-fee-chalan')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/delete_fee_chalan';?>" class="nav-link"> 
                <i class="nav-icon fas fa-file-invoice"></i> 
                <p>Delete Fee Chalan</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-chalan-balance')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/fee_chalan_balance';?>" class="nav-link"> 
                <i class="nav-icon far fa-money-bill-alt"></i> 
                <p>Monthly Balance</p> 
              </a> 
            </li>
            <?php } ?> 
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-accounts') || hasPermission('admin-account-heads') || hasPermission('admin-account-expenses') || hasPermission('admin-account-reports')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-receipt"></i> 
            <p>Accounts Management
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-account-heads')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/expense_head';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Expense Heads</p> 
              </a> 
            </li>
           <?php } ?> 
           <?php if(hasPermission('admin-account-expenses')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/expenses';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Expenses</p> 
              </a> 
            </li>
          <?php } ?>
          
            <?php if(hasPermission('admin-asset-heads')){ ?>
             <li class="nav-item"> 
              <a href="<?php echo '#/asset_heads';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Asset Heads</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-assets')){ ?>
             <li class="nav-item"> 
              <a href="<?php echo '#/assets';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Assets</p> 
              </a> 
            </li>
            <?php } ?> 
            </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-terms') || hasPermission('admin-terms-sessions') || hasPermission('admin-exams') || hasPermission('admin-datesheet') || hasPermission('admin-students-results') || hasPermission('admin-students-subject-results')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-diagnoses"></i> 
            <p>Exams
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-exams')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/exam';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Exams</p> 
              </a> 
            </li>  
            <?php } ?>
            <?php if(hasPermission('admin-datesheet')){ ?>  
            <li class="nav-item"> 
              <a class="nav-link" href="<?php echo '#/datesheet';?>" clas="nav-link"> 
                <i class="nav-icon fa fa-calendar"></i> 
                <p>Date Sheet</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students-results')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_results?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Results</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students-results')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_results_list';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Results List</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students-subject-results')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_subject_results?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Subject Results</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-grades')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/grades?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Grades</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-grading-policy')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/grading_policy';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Grading Policy</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

        <?php if(hasPermission('admin-test-series')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-diagnoses"></i> 
            <p>Tests
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-test-series')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/test_series';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Test Series</p> 
              </a> 
            </li>  
            <li class="nav-item"> 
              <a href="<?php echo '#/tests';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Tests</p> 
              </a> 
            </li>  
            <li class="nav-item"> 
              <a href="<?php echo '#/test_results';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Add Tests Results</p> 
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/test_series_result_card';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Tests Series Results Card</p> 
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/test_result_card';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Tests Results Card</p> 
              </a> 
            </li>
            <?php } ?>
           </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-add-student-attendance') || hasPermission('admin-add-student-absentees') || hasPermission('admin-add-student-latecomming') || hasPermission('admin-add-student-earlyleft') || hasPermission('admin-add-student-leaves') || hasPermission('admin-student-leaves')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p>Attendance
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-add-student-attendance')){?>
            <li class="nav-item"> 
              <a href="#/employees_attendance?m=add" class="nav-link"> 
                <i class="nav-icon fa fa-cubes"></i> 
                <p>Employees Attendance</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-attendance')){?>
            <li class="nav-item"> 
              <a href="#/employee_leaves?m=add" class="nav-link"> 
                <i class="nav-icon fa fa-cubes"></i> 
                <p>Create Employee Leaves Applications</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-attendance')){?>
            <li class="nav-item"> 
              <a href="#/employee_leaves" class="nav-link"> 
                <i class="nav-icon fa fa-cubes"></i> 
                <p>Employee Leaves Applications</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-emp-attendance-monthly-report')){?>
            <li class="nav-item"> 
              <a href="#/emp_attendance_monthlyreport" class="nav-link"> 
                <i class="nav-icon fa fa-cubes"></i> 
                <p>Employees Attendance Report</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-attendance')){ ?>
            <li class="nav-item"> 
              <a href="#/students_attendance?m=add" class="nav-link"> 
                <i class="nav-icon far fa-address-card"></i> 
                <p>Students Attendance</p> 
              </a> 
            </li>
            <?php } ?>
            
            <?php if(hasPermission('admin-add-student-absentees')){ ?> 
            <li class="nav-item"> 
              <a href="<?php echo '#/students_absentees?m=add'; ?>" class="nav-link">  <i class="nav-icon far fa-clock"></i> 
                <p>Absentees</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-latecomming')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_latecomming?m=add'; ?>" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>Late Commings</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-earlyleft')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_earlyleft?m=add'; ?>" class="nav-link">  <i class="nav-icon far fa-clock"></i> 
                <p>Early Left</p> 
              </a> 
            </li>
            <?php } ?> 
            
            <?php if(hasPermission('admin-add-student-leaves')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_leaves?m=add'; ?>" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>Create Leaves Applications</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-student-leaves')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_leaves'; ?>" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p> Leaves Applications</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-slots') || hasPermission('admin-school-timing') || hasPermission('admin-timetable')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-clock"></i> 
            <p>Time Table
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-timetable')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/timetable?m=add'; ?>" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>Time Table</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-school-timing')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/school_timing?m=add'; ?>" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>School Timing</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/school_timming_type'; ?>" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>School Timing Type</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-slots')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/slots'; ?>" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>Slots</p>
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-top-level-planning') || hasPermission('admin-weekly-planning') || hasPermission('admin-classdairy') ){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p> Academics
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-top-level-planning')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/top_level_planning';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Top Level Planning</p>
              </a> 
            </li>  
            <?php } ?>
            <?php if(hasPermission('admin-weekly-planning')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/scheme_of_studies_view';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Scheme Of Studies</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-weekly-planning')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/weekly_planning_view';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Weekly Planning</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/wp_objectives';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Weekly Objectives</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/wp_subjects_objectives?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Subjects Objectives</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/wp_std_weeekly_progress?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Add Student Weekly Progress</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/wp_results_card';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Student Weekly Progress</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-classdairy')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/classdairy_view'; ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Daily Diary</p>
               </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-notices') || hasPermission('admin-student-notices')){ ?>
        <li class="nav-item">
          <a href="#" nav-icon class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p>Announcements
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-notices')){ ?>
            <li class="nav-item"> 
              <a href="#/notices" class="nav-link"> 
                <i class="nav-icon fas fa-bell"></i> 
                <p>Notices</p>
              </a>
            </li>
            <?php } ?>      
            <?php if(hasPermission('admin-student-notices')){ ?>
            <li class="nav-item"> 
              <a href="#/student_notices" class="nav-link"> 
                <i class="nav-icon fas fa-bell"></i> 
                <p>Student Notices</p>
              </a>
            </li>
            <?php } ?>  
          </ul>
        </li> 
        <?php } ?> 

        <?php if(hasPermission('admin-student-complaints')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p>Complaints <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-student-complaints')){ ?>
            <li class="nav-item"> 
              <a href="#/students_complaints" class="nav-link">
                <i class="nav-icon fas fa-users"></i> 
                <p>Students Complaints</p>
              </a>
            </li>
            <?php } ?>  
          </ul>
        </li>
        <?php } ?>  

        <?php if(hasPermission('admin-attendance-monthly-report') || hasPermission('admin-student-fee-report')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p>Reports <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-attendance-monthly-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/attendance_monthlyreport'; ?>" class="nav-link">  <i class="nav-icon far fa-clock"></i> 
                <p>Attendance Monthly Reports</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/student_fee_report';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Fee Report</p> 
              </a> 
            </li>
           <?php } ?>
           <?php if(hasPermission('admin-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/parents_paidfee';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Family Paid Fee Report</p> 
              </a> 
            </li>
           <?php } ?>
            <?php if(hasPermission('admin-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/parents_balancefee';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Family Balance Fee Report</p> 
              </a> 
            </li>
           <?php } ?>
           
            <?php if(hasPermission('admin-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/fee_chalan_month';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Fee Report By Month</p> 
              </a> 
            </li>
           <?php } ?>
           <?php if(hasPermission('admin-family-fee-history')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/family_fee_history';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Family Fee Report</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-report-by-fee-type')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/student_fee_report?m=report_by_fee_type';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Report By Fee Type</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-report-by-student-fee')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/student_fee_report?m=report_by_fee_student';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Report By Student Fee</p> 
              </a> 
            </li>
            <?php } ?>
             <?php if(hasPermission('admin-family-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/family_fee_report';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Report By Family Fee</p> 
              </a> 
            </li>
          <?php } ?>
           <?php if(hasPermission('admin-classwise-result-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/classwise_results';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Class Wise Result</p> 
              </a> 
            </li>
          <?php } ?>
           <?php if(hasPermission('admin-students-result-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/student_results';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Student Results</p> 
              </a> 
            </li> 
           <?php } ?>
           <?php if(hasPermission('admin-datesheet-report')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/datesheet_report?m=add';?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Datesheet Report</p> 
              </a> 
            </li> 
           <?php } ?>
            <?php if(hasPermission('admin-expense-reports')){ ?>
             <li class="nav-item"> 
              <a href="<?php echo '#/expense_report';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Expenses Report</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-assets-report')){ ?>
             <li class="nav-item"> 
              <a href="<?php echo '#/assets_report';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Assets Report</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-profit-loss-reports')){ ?> 
             <li class="nav-item"> 
              <a href="<?php echo '#/profit_loss_report';?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Profit/Loss Report</p> 
              </a> 
            </li>
            <?php } ?> 
          </ul>
        </li>
        <?php } ?>  
         <?php if(hasPermission('admin-vehicles') || hasPermission('admin-transport-fee-type')){ ?>
         <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p> Transport
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-vehicles')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/vehicles';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Vehicles</p>
              </a> 
            </li>
          <?php } ?>
          <?php //if(hasPermission('admin-transport-fee-type')){ ?>
            <!-- <li class="nav-item"> 
              <a href="<?php //echo '#/transport_fee_type?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Transport Fee</p>
              </a> 
            </li> -->
          <?php //} ?>
          </ul>
        </li>
      <?php } ?>
        <?php if(hasPermission('admin-blocks')){ ?>
          <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-list"></i> 
            <p>Hostel <i class="right fas fa-angle-left">
              </i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item"> 
              <a href="<?php echo '#/h_blocks';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Blocks</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_rooms';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Rooms</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_beds';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Beds</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_block_rooms';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Block Rooms</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_room_beds';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Rooms Beds</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_fee_amount?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Hostel Fee Amount</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_student_beds?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Student Beds</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_student_report';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Hostel Student Report </p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_student_report?m=report2';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Hostel Student Report2</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/h_student_report?m=defaulter';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Hostel Student Defaulter</p>
              </a> 
            </li>
          </ul>
        </li>
        <?php } ?>

        <?php if(hasPermission('admin-academy')){ ?>
          <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-list"></i> 
            <p>Academy <i class="right fas fa-angle-left">
              </i></p>
          </a>
          <ul class="nav nav-treeview">
            
            <!-- <li class="nav-item"> 
              <a href="<?php echo '#/a_subjects';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Subjects</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/a_classes';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Classes</p>
              </a> 
            </li> -->

            <li class="nav-item"> 
              <a href="<?php echo '#/a_groups';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>A Groups</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/a_section_subjects';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Class Subjects</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/a_subject_group?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Subject Groups</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/a_teacher_group?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Teacher Groups</p>
              </a> 
            </li>
           <!--  <li class="nav-item"> 
              <a href="<?php echo '#/a_teacher_subjects?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Teacher Subjects</p>
              </a> 
            </li> -->
            <li class="nav-item"> 
              <a href="<?php echo '#/a_fee_amount?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>A Fee Amount</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php echo '#/students_bulk_academy_fee'; ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>A Students</p>
              </a> 
            </li>
          </ul>
        </li>
        <?php } ?>
        <?php //if(hasPermission('admin-subject-category') || hasPermission('admin-subject-category-topics') || hasPermission('admin-topic-skills') || hasPermission('admin-quiz')){ ?>
        <!-- <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-list"></i> 
            <p>E Learning <i class="right fas fa-angle-left">
              </i></p>
          </a> -->
         <!--  <ul class="nav nav-treeview"> -->
            <?php //if(hasPermission('admin-subjects')){ ?>
           <!--  <li class="nav-item"> 
              <a href="<?php //echo '#/esubjects';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>E Subjects</p>
              </a> 
            </li> -->
            <?php //} ?>
            <?php //if(hasPermission('admin-subject-category')){ ?>
           <!--  <li class="nav-item"> 
              <a href="<?php //echo '#/subject_cat';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>E Categories</p>
              </a> 
            </li> -->
            <?php //} ?>
            <?php //if(hasPermission('admin-subject-category-topics')){ ?>
            <!-- <li class="nav-item"> 
              <a href="<?php //echo '#/subject_cat_topic';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>E Topics</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?php //echo '#/worksheet';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Worksheets</p>
              </a> 
            </li> -->
            <?php //} ?>
            <?php //if(hasPermission('admin-topic-skills')){ ?>
           <!--  <li class="nav-item"> 
              <a href="<?php //echo '#/topic_skills';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Topic Skills</p>
              </a> 
            </li> -->
            <?php //} ?> 
          <!-- </ul>
        </li>  -->
        <?php //if(hasPermission('admin-quiz')){ ?>
        <!-- <li class="nav-item"> 
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-list"></i> 
            <p>Quiz <i class="right fas fa-angle-left">
              </i></p>
          </a>
           <ul class="nav nav-treeview">
            <li class="nav-item">
            <a href="<?php //echo '#/quiz';?>" class="nav-link"> 
            <i class="nav-icon fa fa-list"></i> 
            <p>Quiz List</p>
            </a> 
            </li>
            <li class="nav-item">
            <a href="<?php //echo '#/quiz_xml';?>" class="nav-link"> 
            <i class="nav-icon fa fa-list"></i> 
            <p>Quiz Xml</p>
            </a> 
            </li>
          </ul>
        </li> -->
        <?php //} ?>
        <?php //} ?>
        <?php if(hasPermission('admin-campus')){ ?>
        <li class="nav-item"> 
          <a href="<?php echo '#/campus';?>" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Campus 
              <i class="right fas fa-angle-left"></i>
            </p>
          </a> 
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-custom-campus')){ ?>
        <li class="nav-item"> 
          <a href="<?php echo '#/custom_campus?m=add';?>" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Custom Campus 
              <i class="right fas fa-angle-left"></i>
            </p>
          </a> 
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-users') || hasPermission('admin-roles')){ ?>
         <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p> Billing
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-bill-type')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/bill_type';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Bill Type</p>
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-bill-amount')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/bill_amount?m=add';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Bill Amount</p>
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-bill-plan-months')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/bill_plan_months';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Bill Plan Months</p>
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-campus-chalan-pay')){ ?>
            <li class="nav-item"> 
              <a href="<?php echo '#/campus_chalan_pay';?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Pay Campus Chalan</p>
              </a> 
            </li>
          <?php } ?>
          
          </ul>
        </li>
      <?php } ?>
        <?php if(hasPermission('admin-users') || hasPermission('admin-roles')){ ?>
        <li class="nav-header">
          Plan Management
        </li>
        <?php if(hasPermission('admin-roles')){?>
        <li class="nav-item">
          <a href="<?php echo '#/roles';?>" class="nav-link">
            <i class="nav-icon fa fa-users"></i> 
            <p>Roles</p>
          </a>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-permissions')){ ?>
        <li class="nav-item">
          <a href="<?php echo '#/permissions';?>" class="nav-link">
            <i class="nav-icon fa fa-users"></i> 
            <p>Permissions</p>
          </a>
        </li>
        <?php } ?>
        <?php } ?>
        <?php if(hasPermission('admin-pay-campus-bill')){ ?>
        <li class="nav-item"> 
          <a href="<?php echo '#/pay_campus_bill';?>" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Pay Campus Bill</p>
          </a> 
        </li> 
        <?php } ?>
        <?php if(hasPermission('admin-campus-plans')){ ?>
        <li class="nav-item"> 
          <a href="<?php echo '#/campus_plans';?>" class="nav-link"> 
            <i class="nav-icon fas fa-file-invoice"></i>
            <p>Billing Invoice</p>
          </a> 
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-pay-system-bill')){ ?>
        <li class="nav-item"> 
          <a href="<?php echo '#/pay_system_bill';?>" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Pay System Bill</p>
          </a> 
        </li> 
        <?php } ?>
        <?php if(hasPermission('admin-ci-session_view')){ ?>
        <li class="nav-item"> 
          <a href="<?php echo '#/ci_session_view';?>" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Login Log</p>
          </a> 
        </li> 
        <li class="nav-item"> 
          <a href="<?php echo '#/ci_session_view_demo';?>" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Demo Login Log</p>
          </a> 
        </li> 
        <?php } ?>
      </ul>
    <!-- /.sidebar -->
  </nav>
  </section>
</aside>
