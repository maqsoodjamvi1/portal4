<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
/* Dashboard Layout Styles */
.dashboard-section {
    margin-bottom: 25px;
}
.section-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e9ecef;
}
.info-box {
    min-height: 110px !important;
    transition: transform 0.2s;
}
.info-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.config_steps li {
    color: red;
    margin-bottom: 5px;
}
canvas {
    max-width: 100%;
    height: auto !important;
}
.card-body {
    position: relative;
    min-height: 280px;
}
#pieChart, #stackedBarChart, #pieChartAttendance, #stackedBarChartSection {
    display: block !important;
    width: 100% !important;
}

/* Teacher Sections Card Styles */
.teacher-section-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.teacher-section-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.teacher-section-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 12px 15px;
    border-bottom: none;
}

.teacher-section-card .card-header h5 {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.teacher-section-card .card-body {
    padding: 12px;
}

/* Subject badges container - limited to 2 rows */
.subjects-container {
    max-height: 70px; /* Approximately 2 rows of badges */
    overflow-y: auto;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}

/* Hide scrollbar for better appearance */
.subjects-container::-webkit-scrollbar {
    width: 3px;
}

.subjects-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.subjects-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.subject-badge {
    background: #e9ecef;
    color: #495057;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.subject-badge i {
    font-size: 10px;
    color: #17a2b8;
}

.subject-badge:hover {
    background: #17a2b8;
    color: white;
}

.subject-badge:hover i {
    color: white;
}

/* Class Teacher Badge */
.class-teacher-badge {
    background: #28a745;
    color: white;
    padding: 3px 8px;
    border-radius: 15px;
    font-size: 10px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
}

.class-teacher-badge i {
    font-size: 10px;
}

/* Card Footer Buttons */
.teacher-section-card .card-footer {
    background: #f8f9fa;
    padding: 10px 12px;
    border-top: 1px solid #e9ecef;
}

.teacher-section-card .card-footer .btn-sm {
    padding: 4px 10px;
    font-size: 11px;
    border-radius: 20px;
}

.teacher-section-card .card-footer .btn-outline-primary {
    border-color: #667eea;
    color: #667eea;
}

.teacher-section-card .card-footer .btn-outline-primary:hover {
    background: #667eea;
    color: white;
}

.teacher-section-card .card-footer .btn-outline-success {
    border-color: #28a745;
    color: #28a745;
}

.teacher-section-card .card-footer .btn-outline-success:hover {
    background: #28a745;
    color: white;
}

/* No subjects message */
.no-subjects-message {
    font-size: 11px;
    color: #6c757d;
    text-align: center;
    padding: 8px;
    margin: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .teacher-section-card .card-header h5 {
        font-size: 12px;
    }
    
    .subject-badge {
        padding: 3px 8px;
        font-size: 10px;
    }
    
    .subjects-container {
        max-height: 60px;
    }
    
    .teacher-section-card .card-footer .btn-sm {
        padding: 3px 8px;
        font-size: 10px;
    }
}

/* For very small screens, show fewer subjects */
@media (max-width: 480px) {
    .subjects-container {
        max-height: 50px;
    }
    
    .subject-badge {
        font-size: 9px;
        padding: 2px 6px;
    }
}

/* Teacher Sections Card Styles - COMPACT & CONTENT-HEIGHT */
.teacher-section-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    border-radius: 10px;
    overflow: hidden;
    height: auto !important; /* Remove h-100 effect */
    min-height: auto !important;
}

.teacher-section-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Compact Card Header */
.teacher-section-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 8px 12px;
    border-bottom: none;
}

.teacher-section-card .card-header h5 {
    font-size: 13px;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.teacher-section-card .card-header h5 i {
    font-size: 11px;
    margin-right: 5px;
}

/* Compact Card Body - Minimal Padding */
.teacher-section-card .card-body {
    padding: 8px 10px !important;
}

/* Subjects container - exactly 2 rows max */
.subjects-container {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 5px;
    max-height: 52px;
    overflow-y: auto;
}

/* Compact Subject Badges */
.subject-badge {
    background: #eef2fa;
    color: #2c3e66;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
}

.subject-badge i {
    font-size: 9px;
    color: #667eea;
}

/* Class Teacher Badge - Compact */
.class-teacher-badge {
    background: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    margin-top: 4px;
    width: fit-content;
}

.class-teacher-badge i {
    font-size: 9px;
}

/* No subjects message - Compact */
.no-subjects-message {
    font-size: 10px;
    color: #6c757d;
    text-align: center;
    padding: 4px 0;
    margin: 0;
}

/* Compact Card Footer */
.teacher-section-card .card-footer {
    background: #f8f9fa;
    padding: 6px 10px !important;
    border-top: 1px solid #e9ecef;
}

.teacher-section-card .card-footer .btn-sm {
    padding: 3px 8px;
    font-size: 10px;
    border-radius: 15px;
}

.teacher-section-card .card-footer .btn-sm i {
    font-size: 9px;
    margin-right: 3px;
}

/* Remove h-100 class effect */
.teacher-section-card.h-100 {
    height: auto !important;
}

/* Row margin adjustment */
.row {
    margin-bottom: 0;
}

/* Column margin for spacing */
[class*="col-"] {
    margin-bottom: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .teacher-section-card .card-header {
        padding: 6px 10px;
    }
    
    .teacher-section-card .card-header h5 {
        font-size: 11px;
    }
    
    .teacher-section-card .card-body {
        padding: 6px 8px !important;
    }
    
    .subject-badge {
        padding: 2px 6px;
        font-size: 9px;
    }
    
    .subjects-container {
        max-height: 48px;
        gap: 4px;
    }
    
    .teacher-section-card .card-footer {
        padding: 4px 8px !important;
    }
    
    .teacher-section-card .card-footer .btn-sm {
        padding: 2px 6px;
        font-size: 9px;
    }
}   

/* ============================================ */
/* FIX: Reduce Outer Card Height */
/* ============================================ */

/* Target the card that contains the teacher sections */
.dashboard-section .card {
    margin-bottom: 20px;
}

/* Reduce the card-body min-height that's causing excessive height */
.dashboard-section .card .card-body {
    min-height: auto !important;
    padding: 15px !important;
}

/* Specifically target the teacher sections card */
.dashboard-section .card .card-body .row {
    margin-bottom: 0;
}

/* Make the subject cards more compact */
.dashboard-section .teacher-section-card {
    height: auto !important;
    min-height: auto !important;
}

.dashboard-section .teacher-section-card .card-header {
    padding: 6px 10px !important;
}

.dashboard-section .teacher-section-card .card-header h5 {
    font-size: 12px !important;
    white-space: normal !important;
    line-height: 1.3;
}

.dashboard-section .teacher-section-card .card-body {
    padding: 6px 10px !important;
}

.dashboard-section .subjects-container {
    max-height: 36px !important;
    overflow-y: hidden !important;
    gap: 4px;
    margin-bottom: 3px;
}

.dashboard-section .subject-badge {
    padding: 2px 6px !important;
    font-size: 9px !important;
}

.dashboard-section .class-teacher-badge {
    margin-top: 2px !important;
    padding: 1px 5px !important;
    font-size: 8px !important;
}

.dashboard-section .teacher-section-card .card-footer {
    padding: 4px 8px !important;
}

.dashboard-section .teacher-section-card .card-footer .btn-sm {
    padding: 2px 6px !important;
    font-size: 9px !important;
}

/* Reduce column bottom margin */
.dashboard-section .col-md-6.col-lg-4 {
    margin-bottom: 12px !important;
}
/* Class Teacher Card Highlighting */
.class-teacher-card {
    border: 2px solid #ffc107 !important;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2) !important;
    position: relative;
    overflow: visible !important;
}

.class-teacher-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #ffc107, #ff9800);
}

.class-teacher-card .card-header {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
}

/* Class Teacher Name Badge in Header */
.class-teacher-name-badge {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.class-teacher-name-badge i {
    font-size: 9px;
}

/* Card Header flex layout */
.teacher-section-card .card-header .d-flex {
    flex-wrap: wrap;
    gap: 8px;
}

/* Responsive: On small screens, wrap the header content */
@media (max-width: 576px) {
    .teacher-section-card .card-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .class-teacher-name-badge {
        margin-top: 5px;
    }
}

.class-teacher-name-badge {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.class-teacher-name-badge i {
    font-size: 9px;
}
</style>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    
    <!-- Configuration Steps (Visible to all) -->
    <div class="mb-4">
        <ul class="config_steps">
            <?php 
            $session = \Config\Services::session();
            $campus_id = $session->get('member_campusid');
            $schoolinfo = getSchoolInfo();
            $db = \Config\Database::connect();
            
            $academic_session_info = $db->table('academic_session')
                ->where('system_id', $schoolinfo->system_id)
                ->get()
                ->getRow();
                
            if(empty($academic_session_info)): ?>
                <li>Step 1 Of 10: Add <strong>Academic Session</strong> to complete system configuration. 
                    <a href="<?= base_url('admin/academic-calendar/builder') ?>" class="text-decoration-underline">Click here</a>
                </li>
            <?php endif; ?>
        </ul>  
    </div>


<!-- ============================================ -->
<!-- TEACHER SPECIFIC SECTION: My Classes & Subjects -->
<!-- ============================================ -->
<?php if ($isTeacher && !empty($teacherSections)): ?>
<div class="dashboard-section">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                        My Classes & Subjects
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($teacherSections as $section): 
                            $sectionSubjects = $subjectsPerSection[$section->cls_sec_id] ?? [];
                            $isClassTeacher = false;
                            $classTeacherName = '';
                            
                            // Get class teacher info from the map
                            if (isset($classTeacherMap[$section->cls_sec_id])) {
                                $classTeacherName = $classTeacherMap[$section->cls_sec_id]['name'];
                                // Check if the logged-in user is the class teacher for this section
                                if ($classTeacherMap[$section->cls_sec_id]['id'] == $user_id) {
                                    $isClassTeacher = true;
                                }
                            } else {
                                $classTeacherName = 'Not Assigned';
                            }
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card teacher-section-card shadow-sm <?= $isClassTeacher ? 'class-teacher-card' : '' ?>">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <h5 class="card-title mb-0" title="<?= esc($section->class_short_name ?? $section->class_name) ?> - <?= esc($section->section_short_name ?? $section->section_name) ?>">
                                            <i class="fas fa-users mr-2"></i>
                                            <?= esc($section->class_short_name ?? $section->class_name) ?> - <?= esc($section->section_short_name ?? $section->section_name) ?>
                                        </h5>
                                        <span class="class-teacher-name-badge">
                                            <i class="fas fa-chalkboard-teacher"></i> 
                                            <?= esc($classTeacherName) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($sectionSubjects)): ?>
                                        <div class="subjects-container">
                                            <?php foreach ($sectionSubjects as $subject): ?>
                                                <span class="subject-badge" title="<?= esc($subject->subject_name) ?>">
                                                    <i class="fas fa-book"></i> 
                                                    <?= esc($subject->subject_short_name ?? $subject->subject_name) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="no-subjects-message">
                                            <i class="fas fa-info-circle"></i> No subjects assigned
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="<?= base_url('admin/classdiary/add?cls_sec_id=' . $section->cls_sec_id) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-book-open mr-1"></i> Class Diary
                                    </a>
                                    <a href="<?= base_url('admin/top_level_planning/add?class_id=' . $section->class_id) ?>" class="btn btn-sm btn-outline-success ml-2">
                                        <i class="fas fa-layer-group mr-1"></i> Planning
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif ($isTeacher && empty($teacherSections)): ?>
<div class="dashboard-section">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        No Classes Assigned
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">You are not assigned to any class as a class teacher or subject teacher.</p>
                    <p class="text-muted mt-2">Please contact the administrator to assign your classes.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
    <!-- ============================================ -->
    <!-- SECTION 1: COMMON WIDGETS (Visible to All) -->
    <!-- ============================================ -->
    <div class="dashboard-section">
        <div class="row">
            <!-- Widget 1: Students -->
            <?php if(hasPermission('admin-db-students')): ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="<?= base_url('admin/students?status=1') ?>">  
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-user-graduate"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Students</span>
                            <span class="info-box-number"><?= $noOfstudent ?? 0 ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Widget 2: Teachers/Faculty -->
            <?php if(hasPermission('admin-db-teacher')): ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="<?= base_url('admin/users') ?>">  
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-chalkboard-teacher"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Faculty</span>
                            <span class="info-box-number"><?= $infoteachers ?? 0 ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Widget 3: Academic Session -->
            <?php if(hasPermission('admin-db-session') && isset($academic_session)): ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="<?= base_url('admin/academic_session') ?>">  
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="far fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Session</span>
                            <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                                <?= $academic_session->session_name ?? 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Widget 4: Current Term -->
            <?php if(hasPermission('admin-db-term') && isset($termInfo)): ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="<?= base_url('admin/terms') ?>">  
                    <div class="info-box">
                        <span class="info-box-icon bg-purple"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Current Term</span>
                            <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                                <?= $termInfo->name ?? 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="row mt-3">
            <!-- Widget 5: Current Week -->
            <?php if(hasPermission('admin-db-week') && isset($termWeeksInfo)): ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="<?= base_url('admin/weeks') ?>"> 
                    <div class="info-box">
                        <span class="info-box-icon bg-teal"><i class="fas fa-calendar-week"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Current Week</span>
                            <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                                <?= $termWeeksInfo->week_name ?? 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Widget 6: Current Exam -->
            <?php if(hasPermission('admin-db-exam') && isset($examsInfo)): ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="<?= base_url('admin/exams') ?>"> 
                    <div class="info-box">
                        <span class="info-box-icon bg-orange"><i class="fas fa-file-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Current Exam</span>
                            <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                                <?= $examsInfo->exam_name ?? 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Widget 7: Today's Attendance (Student) -->
            <?php if(hasPermission('admin-db-attendance')): ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="<?= base_url('admin/students_attendance/add') ?>"> 
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-receipt"></i></span> 
                        <div class="info-box-content">
                            <span class="info-box-text">Today's Attendance</span>
                            <span class="info-box-number" style="font-size: 12px; font-weight: normal;">
                                Present: <?= $attendance['present'] ?? 0 ?><br>
                                Absent: <?= $attendance['absent'] ?? 0 ?><br>
                                Leaves: <?= $attendance['leaves'] ?? 0 ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- SECTION 2: ATTENDANCE SECTION (Role Based) -->
    <!-- ============================================ -->
    <div class="dashboard-section">
        <div class="row">
            <div class="col-md-12">
                <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas <?= $showEmployeeAttendance ? 'fa-chalkboard-teacher' : 'fa-user-check' ?> mr-2"></i>
                                <?= $showEmployeeAttendance ? 'Staff Attendance Today' : 'My Recent Attendance' ?>
                            </h3>
                            <div class="text-right">
                                <div id="currentDateTimeCompact" class="d-flex align-items-center">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <span id="currentDayCompact" class="mr-2">Monday</span>
                                    <span id="currentDateCompact" class="mr-2">24 Mar 2026</span>
                                    <i class="fas fa-clock ml-2 mr-1"></i>
                                    <span id="currentTimeCompact">10:30:45 AM</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($showEmployeeAttendance): ?>
                            <!-- ADMIN VIEW: All Employees Attendance -->
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Attendance Summary Stats -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="bg-white text-dark rounded p-3 text-center">
                                                <h4 class="mb-0"><?= $totalEmployees ?? 0 ?></h4>
                                                <small>Total Staff</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-white text-dark rounded p-3 text-center">
                                                <h4 class="mb-0 text-success"><?= $presentCount ?? 0 ?></h4>
                                                <small>Present</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-white text-dark rounded p-3 text-center">
                                                <h4 class="mb-0 text-warning"><?= $lateCount ?? 0 ?></h4>
                                                <small>Late Arrivals</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="bg-white text-dark rounded p-3 text-center">
                                                <h4 class="mb-0 text-danger"><?= ($totalEmployees - $presentCount) ?? 0 ?></h4>
                                                <small>Absent</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Present Employees List -->
                                    <div class="bg-white rounded p-3 mb-3">
                                        <h6 class="text-dark mb-3">
                                            <i class="fas fa-user-check text-success mr-2"></i> 
                                            Present Today (<?= count($teacherAttendance ?? []) ?>)
                                        </h6>
                                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                            <table class="table table-sm table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Check In</th>
                                                        <th>Check Out</th>
                                                        <th>Status</th>
                                                        <th>Duration</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($teacherAttendance)): ?>
                                                        <?php foreach ($teacherAttendance as $emp): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?= esc($emp->first_name . ' ' . $emp->last_name) ?></strong>
                                                                    <br><small class="text-muted"><?= esc($emp->designation ?? 'Employee') ?></small>
                                                                </td>
                                                                <td>
                                                                    <?php if ($emp->checkin): ?>
                                                                        <i class="fas fa-clock text-success mr-1"></i>
                                                                        <?= date('h:i A', strtotime($emp->checkin)) ?>
                                                                    <?php else: ?>-<?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($emp->checkout): ?>
                                                                        <i class="fas fa-clock text-danger mr-1"></i>
                                                                        <?= date('h:i A', strtotime($emp->checkout)) ?>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-warning">Active</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php 
                                                                    $statusClass = $emp->status == 'present' ? 'success' : ($emp->status == 'late' ? 'warning' : 'secondary');
                                                                    ?>
                                                                    <span class="badge badge-<?= $statusClass ?>">
                                                                        <?= ucfirst($emp->status ?? 'present') ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if ($emp->checkin && $emp->checkout): ?>
                                                                        <?php 
                                                                        $hours = floor($emp->lc_duration / 60);
                                                                        $minutes = $emp->lc_duration % 60;
                                                                        echo $hours . 'h ' . $minutes . 'm';
                                                                        ?>
                                                                    <?php else: ?>-<?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">
                                                                <i class="fas fa-info-circle mr-1"></i> No attendance records for today
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Absent Employees List -->
                                    <?php if (!empty($absentEmployees)): ?>
                                    <div class="bg-white rounded p-3">
                                        <h6 class="text-dark mb-3">
                                            <i class="fas fa-user-clock text-danger mr-2"></i> 
                                            Absent Today (<?= count($absentEmployees) ?>)
                                        </h6>
                                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Designation</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($absentEmployees as $emp): ?>
                                                        <tr>
                                                            <td><strong><?= esc($emp->first_name . ' ' . $emp->last_name) ?></strong></td>
                                                            <td><small class="text-muted"><?= esc($emp->designation ?? 'Employee') ?></small></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <!-- TEACHER VIEW: Their Own Attendance History -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="bg-white rounded p-3">
                                        <div class="mb-3">
                                            <h6 class="text-dark mb-0">
                                                <i class="fas fa-history text-primary mr-2"></i> 
                                                My Recent Attendance (Last 10 Records)
                                            </h6>
                                        </div>
                                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                            <table class="table table-sm table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Check In</th>
                                                        <th>Check Out</th>
                                                        <th>Duration</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($teacherAttendance)): ?>
                                                        <?php foreach ($teacherAttendance as $att): ?>
                                                            <tr>
                                                                <td><strong><?= date('d M Y', strtotime($att->date)) ?></strong></td>
                                                                <td>
                                                                    <?php if ($att->checkin): ?>
                                                                        <i class="fas fa-clock text-success mr-1"></i>
                                                                        <?= date('h:i A', strtotime($att->checkin)) ?>
                                                                    <?php else: ?>-<?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($att->checkout): ?>
                                                                        <i class="fas fa-clock text-danger mr-1"></i>
                                                                        <?= date('h:i A', strtotime($att->checkout)) ?>
                                                                    <?php else: ?>-<?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($att->checkin && $att->checkout): ?>
                                                                        <?php 
                                                                        $hours = floor($att->lc_duration / 60);
                                                                        $minutes = $att->lc_duration % 60;
                                                                        echo $hours . 'h ' . $minutes . 'm';
                                                                        ?>
                                                                    <?php else: ?>-<?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php 
                                                                    $statusClass = $att->status == 'present' ? 'success' : ($att->status == 'late' ? 'warning' : 'secondary');
                                                                    ?>
                                                                    <span class="badge badge-<?= $statusClass ?>">
                                                                        <?= ucfirst($att->status ?? 'present') ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">
                                                                <i class="fas fa-info-circle mr-1"></i> No attendance records found
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- QR Scanner for teachers only -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#qrScannerModal" style="border-radius: 50px; padding: 10px 20px; font-weight: bold;">
                                            <i class="fas fa-camera me-2"></i>
                                            Scan QR Code for Today's Attendance
                                        </button>
                                        <p class="mt-2 text-white-50 mb-0">
                                            <small><i class="fas fa-info-circle me-1"></i> Scan the QR code to mark your attendance</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- SECTION 3: HEALTH SECTION (Visible to All) -->
    <!-- ============================================ -->
    <div class="dashboard-section">
        <div class="row">
            <div class="col-md-6">
                <div class="info-box bmi-widget">
                    <span class="info-box-icon bg-gradient-heartbeat" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-heartbeat"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">BMI Health Status</span>
                        <span class="info-box-number" style="font-size: 14px;"><?= $bmiStats->total ?? 0 ?> Students Assessed</span>
                        <div class="bmi-progress mt-2">
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <?php 
                                $total = ($bmiStats->total ?? 1);
                                $underweightPercent = ($bmiStats->underweight ?? 0) / $total * 100;
                                $normalPercent = ($bmiStats->normal ?? 0) / $total * 100;
                                $overweightPercent = ($bmiStats->overweight ?? 0) / $total * 100;
                                $obesePercent = ($bmiStats->obese ?? 0) / $total * 100;
                                ?>
                                <div class="progress-bar bg-info" style="width: <?= $underweightPercent ?>%"></div>
                                <div class="progress-bar bg-success" style="width: <?= $normalPercent ?>%"></div>
                                <div class="progress-bar bg-warning" style="width: <?= $overweightPercent ?>%"></div>
                                <div class="progress-bar bg-danger" style="width: <?= $obesePercent ?>%"></div>
                            </div>
                        </div>
                        <div class="row mt-2 text-center">
                            <div class="col-3"><span class="bmi-category-label"><i class="fas fa-circle text-info"></i> Underweight<br><strong><?= $bmiStats->underweight ?? 0 ?></strong></span></div>
                            <div class="col-3"><span class="bmi-category-label"><i class="fas fa-circle text-success"></i> Normal<br><strong><?= $bmiStats->normal ?? 0 ?></strong></span></div>
                            <div class="col-3"><span class="bmi-category-label"><i class="fas fa-circle text-warning"></i> Overweight<br><strong><?= $bmiStats->overweight ?? 0 ?></strong></span></div>
                            <div class="col-3"><span class="bmi-category-label"><i class="fas fa-circle text-danger"></i> Obese<br><strong><?= $bmiStats->obese ?? 0 ?></strong></span></div>
                        </div>
                        <div class="mt-2"><a href="<?= base_url('admin/students/bmi-report') ?>" class="small-box-footer">View Detailed Report <i class="fas fa-arrow-circle-right"></i></a></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box bmi-widget">
                    <span class="info-box-icon bg-gradient-orange" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                        <i class="fas fa-bell"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Health Alerts</span>
                        <span class="info-box-number" style="font-size: 14px;"><?= $healthAlertsCount ?? 0 ?> Pending Alerts</span>
                        <?php if (!empty($recentHealthAlerts)): ?>
                            <div class="mt-2" style="max-height: 80px; overflow-y: auto;">
                                <?php foreach ($recentHealthAlerts as $alert): ?>
                                    <div class="health-alert-item"><span class="alert-badge alert-badge-<?= $alert->alert_type ?>"></span><small><strong><?= esc($alert->student_name) ?></strong> - <?= esc($alert->alert_type) ?></small></div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="mt-2 text-muted text-center"><i class="fas fa-check-circle text-success"></i> No pending health alerts</div>
                        <?php endif; ?>
                        <div class="mt-2"><a href="<?= base_url('admin/students/health-alerts') ?>" class="small-box-footer">View All Alerts <i class="fas fa-arrow-circle-right"></i></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- SECTION 4: ADMIN ONLY CHARTS (Fee & Strength) -->
    <!-- ============================================ -->
    <?php if (!$isTeacher): // Only show for Admin/Director/Principal ?>
    <div class="dashboard-section">
        <!-- Session Selector for Fee Collection -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i> Fee Collection Report</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Session</label>
                                    <select class="form-control select2" id="sessionSelector" style="width: 100%;">
                                        <option value="">Last 12 Months (Default)</option>
                                        <?php if (isset($allSessions) && !empty($allSessions)): ?>
                                            <?php foreach ($allSessions as $session): ?>
                                                <option value="<?= $session->session_id ?>" <?= (isset($selectedSessionId) && $selectedSessionId == $session->session_id) ? 'selected' : '' ?>>
                                                    <?= esc($session->session_name) ?> (<?= date('M Y', strtotime($session->start_date)) ?> - <?= date('M Y', strtotime($session->end_date)) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option disabled>No sessions available</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1: Fee Collection Bar Chart (Full Width) -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Fee Collection - <?= isset($chartTitle) ? esc($chartTitle) : 'Monthly Report' ?></h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="stackedBarChart" style="min-height: 300px; height: 300px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Two Equal Charts (Pie Chart and Strength Chart) -->
        <div class="row mt-4">
            <!-- Monthly Fee Collection Pie Chart -->
            <?php if(hasPermission('admin-db-fee-collection') && isset($monthlyFee)): ?>
            <div class="col-md-6">
                <div class="card card-danger h-100">
                    <div class="card-header">
                        <h3 class="card-title"><?= $monthlyFee->fee_type_name ?> Collection (<?= date('M Y') ?>)</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <div style="width: 100%; max-width: 280px; margin: 0 auto;">
                            <canvas id="pieChart" style="min-height: 250px; height: 250px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Student Strength Chart -->
            <div class="col-md-6">
                <div class="card card-primary h-100">
                    <div class="card-header">
                        <h3 class="card-title">Student Strength by Class</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="stackedBarChartSection" style="min-height: 250px; height: 250px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 3: Today's Attendance (Student) -->
        <div class="row mt-4">
            <?php if (hasPermission('admin-db-attendance')): ?>
            <div class="col-md-6">
                <div class="card card-info h-100">
                    <div class="card-header">
                        <h3 class="card-title">Today's Attendance (<?= date('D j M Y') ?>)</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <div style="width: 100%; max-width: 280px; margin: 0 auto;">
                            <canvas id="pieChartAttendance" style="min-height: 250px; height: 250px; width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; // End Admin Only Section ?>
    
</section>



<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-qrcode me-2"></i> Scan Campus QR Code</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-reader-results" class="mt-3"></div>
                <div class="mt-3 alert alert-info"><i class="fas fa-info-circle me-2"></i><small>Point your camera at the QR code displayed in the admin office</small></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Load Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Session selector change handler
// Initialize Charts with Consistent Sizing
document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // Monthly Fee Collection Pie Chart
    // ============================================
    const pieCtx = document.getElementById('pieChart')?.getContext('2d');
    if (pieCtx) {
        const paidFee = <?= json_encode($PaidFee_info ?? 0) ?>;
        const unpaidFee = <?= json_encode($RemainingBalance_info ?? 0) ?>;
        
        if (paidFee > 0 || unpaidFee > 0) {
            new Chart(pieCtx, {
                type: 'pie',
                data: { 
                    labels: ['Paid', 'Unpaid'], 
                    datasets: [{ 
                        data: [paidFee, unpaidFee], 
                        backgroundColor: ['#28a745', '#dc3545'], 
                        borderWidth: 0,
                        hoverOffset: 10
                    }] 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: true,
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            labels: { font: { size: 12 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = paidFee + unpaidFee;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: ${value.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // Show message if no data
            document.getElementById('pieChart')?.parentElement?.insertAdjacentHTML('beforeend', 
                '<div class="text-center text-muted p-4">No fee data available</div>');
        }
    }
    
    // ============================================
    // Fee Collection Bar Chart (Stacked)
    // ============================================
    const barCtx = document.getElementById('stackedBarChart')?.getContext('2d');
    if (barCtx) {
        const months = <?= json_encode($prStr ?? []) ?>;
        const paid = <?= json_encode($paidStr ?? []) ?>;
        const unpaid = <?= json_encode($unpaidStr ?? []) ?>;
        
        if (months.length > 0) {
            new Chart(barCtx, {
                type: 'bar',
                data: { 
                    labels: months, 
                    datasets: [
                        { label: 'Paid', data: paid, backgroundColor: '#28a745', borderRadius: 4 },
                        { label: 'Unpaid', data: unpaid, backgroundColor: '#dc3545', borderRadius: 4 }
                    ] 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: true,
                    scales: { 
                        x: { stacked: true, grid: { display: false } }, 
                        y: { 
                            stacked: true, 
                            ticks: { 
                                callback: function(v) { return v.toLocaleString(); },
                                stepSize: 5000
                            },
                            title: { display: true, text: 'Amount (PKR)' }
                        } 
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    
    // ============================================
    // Today's Attendance Pie Chart
    // ============================================
    const attendanceCtx = document.getElementById('pieChartAttendance')?.getContext('2d');
    if (attendanceCtx) {
        const present = <?= json_encode($attendance['present'] ?? 0) ?>;
        const absent = <?= json_encode($attendance['absent'] ?? 0) ?>;
        const leaves = <?= json_encode($attendance['leaves'] ?? 0) ?>;
        
        if (present > 0 || absent > 0 || leaves > 0) {
            new Chart(attendanceCtx, {
                type: 'pie',
                data: { 
                    labels: ['Present', 'Absent', 'Leaves'], 
                    datasets: [{ 
                        data: [present, absent, leaves], 
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107'], 
                        borderWidth: 0,
                        hoverOffset: 10
                    }] 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: true,
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            labels: { font: { size: 12 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = present + absent + leaves;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('pieChartAttendance')?.parentElement?.insertAdjacentHTML('beforeend', 
                '<div class="text-center text-muted p-4">No attendance data available</div>');
        }
    }
    
    // ============================================
    // Student Strength Bar Chart
    // ============================================
    const strengthCtx = document.getElementById('stackedBarChartSection')?.getContext('2d');
    if (strengthCtx) {
        const classes = <?= json_encode($clsArr ?? []) ?>;
        const male = <?= json_encode($stdMArr ?? []) ?>;
        const female = <?= json_encode($stdFArr ?? []) ?>;
        
        if (classes.length > 0) {
            new Chart(strengthCtx, {
                type: 'bar',
                data: { 
                    labels: classes, 
                    datasets: [
                        { label: 'Male', data: male, backgroundColor: '#007bff', borderRadius: 4 },
                        { label: 'Female', data: female, backgroundColor: '#fd7e14', borderRadius: 4 }
                    ] 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: true,
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            ticks: { stepSize: 1, precision: 0 },
                            title: { display: true, text: 'Number of Students' }
                        },
                        x: { grid: { display: false } }
                    }, 
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw} students`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('stackedBarChartSection')?.parentElement?.insertAdjacentHTML('beforeend', 
                '<div class="text-center text-muted p-4">No student data available</div>');
        }
    }
});
// QR Attendance Scanner Functions
document.addEventListener('DOMContentLoaded', function() {
    let html5QrCode = null;
    let isScanning = false;
    
    function updateDateTime() {
        const now = new Date();
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        document.getElementById('currentDayCompact').textContent = days[now.getDay()];
        document.getElementById('currentDateCompact').textContent = `${now.getDate()} ${monthNames[now.getMonth()]} ${now.getFullYear()}`;
        let hours = now.getHours();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        document.getElementById('currentTimeCompact').textContent = `${hours}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')} ${ampm}`;
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    function loadAttendanceStatus() {
        const container = document.getElementById('attendanceStatusContainer');
        if (!container) return;
        container.innerHTML = `<div class="bg-white text-dark rounded p-3 text-center"><div class="spinner-border text-primary spinner-border-sm"></div><span class="ms-2">Loading status...</span></div>`;
        fetch('/admin/get-recent-attendance', { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                const today = new Date();
                const todayStr = `${today.getDate()} ${['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][today.getMonth()]} ${today.getFullYear()}`;
                let attendanceArray = Array.isArray(data) ? data : (data.attendance || []);
                const todayAtt = attendanceArray.find(a => a.date === todayStr);
                if (todayAtt && todayAtt.checkin && todayAtt.checkin !== '-') {
                    if (todayAtt.checkout && todayAtt.checkout !== '-') {
                        container.innerHTML = `<div class="bg-white text-dark rounded p-3" style="border-left:4px solid #28a745"><div class="d-flex justify-content-between"><div><i class="fas fa-check-circle text-success"></i><span class="fw-bold ms-1">Completed</span></div><div><small><i class="fas fa-sign-in-alt text-info"></i> ${todayAtt.checkin} <i class="fas fa-sign-out-alt text-warning"></i> ${todayAtt.checkout}</small></div></div></div>`;
                    } else {
                        const isLate = todayAtt.status === 'late';
                        container.innerHTML = `<div class="bg-white text-dark rounded p-3" style="border-left:4px solid ${isLate ? '#ffc107' : '#17a2b8'}"><div class="d-flex justify-content-between"><div><i class="fas fa-clock ${isLate ? 'text-warning' : 'text-info'}"></i><span class="fw-bold ms-1">Checked In</span>${isLate ? '<span class="badge bg-warning ms-2">Late</span>' : '<span class="badge bg-info ms-2">On Time</span>'}</div><div class="text-end"><span class="fw-bold">${todayAtt.checkin}</span><br><small>Scan again to checkout</small></div></div></div>`;
                    }
                } else {
                    container.innerHTML = `<div class="bg-white text-dark rounded p-3" style="border-left:4px solid #6c757d"><div class="d-flex justify-content-between"><div><i class="fas fa-hourglass-half text-secondary"></i><span class="ms-1">Not Checked In</span></div><div><i class="fas fa-qrcode text-primary"></i><small>Scan QR code</small></div></div></div>`;
                }
            })
            .catch(() => { if(container) container.innerHTML = `<div class="bg-white text-dark rounded p-3 text-center text-danger">Unable to load status</div>`; });
    }
    
    function loadRecentAttendance() {
        const container = document.getElementById('recentAttendanceList');
        if (!container) return;
        container.innerHTML = '<div class="text-center text-muted py-2"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        fetch('/admin/get-recent-attendance', { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                let attendanceArray = Array.isArray(data) ? data : (data.attendance || []);
                if (attendanceArray.length === 0) { container.innerHTML = '<div class="text-center text-muted py-2">No records found</div>'; return; }
                let html = `<table class="table table-sm table-hover mb-0" style="font-size:12px"><thead class="table-light"><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead><tbody>`;
                attendanceArray.slice(0, 8).forEach(att => {
                    let statusBadge = att.status === 'late' ? '<span class="badge bg-warning">Late</span>' : (att.status === 'present' ? '<span class="badge bg-success">Present</span>' : '<span class="badge bg-secondary">-</span>');
                    html += `<tr><td><strong>${att.date || '-'}</strong></td><td><i class="fas fa-sign-in-alt text-info"></i> ${att.checkin || '-'}</td><td><i class="fas fa-sign-out-alt text-warning"></i> ${att.checkout || '-'}</td><td>${statusBadge}</td></tr>`;
                });
                html += `</tbody></table>`;
                container.innerHTML = html;
            })
            .catch(() => { container.innerHTML = '<div class="alert alert-danger mb-0 py-1">Error loading history</div>'; });
    }
    
function startScanner() {
    if (isScanning) return;
    
    const readerElement = document.getElementById('qr-reader');
    if (!readerElement) {
        showToast('error', 'Scanner element not found');
        return;
    }
    
    if (typeof Html5Qrcode === 'undefined') {
        const resultsDiv = document.getElementById('qr-reader-results');
        if (resultsDiv) {
            resultsDiv.innerHTML = '<div class="alert alert-danger">QR Scanner library failed to load. Please refresh the page.</div>';
        }
        showToast('error', 'QR Scanner library not loaded');
        return;
    }
    
    try {
        html5QrCode = new Html5Qrcode("qr-reader");
        
        // INFINIX FIX: Use more compatible camera configuration
        const config = { 
            fps: 10, 
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            // Add these for better compatibility
            videoConstraints: {
                facingMode: "environment",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        // Try different camera IDs for Infinix
        const cameraId = { facingMode: "environment" };
        
        html5QrCode.start(cameraId, config, 
            (decodedText) => {
                // QR Code scanned successfully
                if (html5QrCode && isScanning) {
                    html5QrCode.stop().catch(e => console.error('Stop error:', e));
                    isScanning = false;
                }
                
                const resultsDiv = document.getElementById('qr-reader-results');
                if (resultsDiv) resultsDiv.innerHTML = '<div class="alert alert-info">Processing attendance...</div>';
                
                fetch('/admin/process-teacher-attendance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: 'qr_code=' + encodeURIComponent(decodedText)
                })
                .then(response => response.json())
                .then(data => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('qrScannerModal'));
                    if (modal) modal.hide();
                    
                    if (data.success) {
                        showToast('success', data.message || 'Attendance recorded successfully');
                        loadAttendanceStatus();
                        loadRecentAttendance();
                    } else {
                        showToast('error', data.message || 'Failed to record attendance');
                    }
                    if (resultsDiv) resultsDiv.innerHTML = '';
                })
                .catch(err => {
                    console.error('Error:', err);
                    showToast('error', 'Network error. Please try again.');
                    if (resultsDiv) resultsDiv.innerHTML = '';
                });
            },
            (errorMessage) => {
                // QR scanning error - ignore
                console.debug('QR Scan error:', errorMessage);
            }
        ).catch(err => {
            console.error('Scanner start error:', err);
            // Try fallback method for Infinix
            tryFallbackCamera();
        });
        
        isScanning = true;
    } catch (err) {
        console.error('Scanner initialization error:', err);
        showToast('error', 'Failed to initialize scanner: ' + (err.message || 'Unknown error'));
        isScanning = false;
    }
}

// Fallback camera method for Infinix phones
function tryFallbackCamera() {
    if (html5QrCode) {
        html5QrCode.stop().catch(() => {});
    }
    
    // Try with different camera constraints
    const config = { 
        fps: 10, 
        qrbox: { width: 250, height: 250 }
    };
    
    // Try with exact camera ID if available
    if (typeof navigator.mediaDevices !== 'undefined' && navigator.mediaDevices.enumerateDevices) {
        navigator.mediaDevices.enumerateDevices()
            .then(devices => {
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                if (videoDevices.length > 0) {
                    // Try the back camera (usually index 1)
                    const backCamera = videoDevices.find(device => 
                        device.label.toLowerCase().includes('back') || 
                        device.label.toLowerCase().includes('rear')
                    ) || videoDevices[videoDevices.length - 1];
                    
                    if (backCamera) {
                        return html5QrCode.start(backCamera.deviceId, config, 
                            (decodedText) => handleSuccessfulScan(decodedText),
                            (errorMessage) => console.debug('QR Scan error:', errorMessage)
                        );
                    }
                }
                throw new Error('No camera found');
            })
            .catch(err => {
                console.error('Camera enumeration failed:', err);
                showToast('error', 'Camera access failed. Please check permissions.');
                isScanning = false;
            });
    } else {
        showToast('error', 'Camera not supported on this device');
        isScanning = false;
    }
}
    
    function stopScanner() { if (html5QrCode && isScanning) { html5QrCode.stop(); isScanning = false; } }
    
    const modal = document.getElementById('qrScannerModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', startScanner);
        modal.addEventListener('hidden.bs.modal', function() { stopScanner(); document.getElementById('qr-reader-results').innerHTML = ''; });
    }
    
    loadAttendanceStatus();
    loadRecentAttendance();
});
</script>

<?= $this->endSection() ?>