<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$header = 'Students Attendance';
$campus_id = $sessionData['campusid'];
$session_id = $sessionData['sessionid'];
$date_value = $sessionData['date'];
$session_label = $academic_session[0]->session_name ?? ('Session ' . $session_id);
?>

<style>
/* ============================================
   COMPLETE RESPONSIVE STYLES - Mobile & Tablet
   ============================================ */

/* Base responsive adjustments */
@media (max-width: 768px) {
    /* Tab navigation */
    .nav-tabs .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }
    
    /* Form controls */
    .attendance-controls .btn,
    .btn-group-sm>.btn, .btn-sm {
        padding: 0.35rem 0.5rem;
        font-size: 0.75rem;
    }
    
    /* Search input group */
    .search-input-group {
        flex-direction: column;
        width: 100%;
    }
    
    .search-input-group input {
        width: 100%;
        margin-bottom: 0.5rem;
        border-radius: 0.375rem !important;
    }
    
    .search-input-group .input-group-text {
        width: 100%;
    }
    
    .search-input-group .input-group-text button {
        width: 100%;
        border-radius: 0.375rem !important;
    }
    
    /* Compact attendance filters — keep class+section on one row */
    .att-filter-compact .att-filter-row1,
    .att-filter-compact .att-filter-row2 {
        flex-wrap: nowrap;
        margin-left: -4px;
        margin-right: -4px;
    }
    .att-filter-compact .att-filter-row1 > [class*="col-"],
    .att-filter-compact .att-filter-row2 > [class*="col-"] {
        padding-left: 4px;
        padding-right: 4px;
    }
    .att-filter-compact .select2-container {
        width: 100% !important;
    }
    .att-filter-compact .select2-selection--single {
        height: 36px !important;
        min-height: 36px;
    }
    .att-filter-compact .select2-selection__rendered {
        line-height: 34px !important;
        font-size: 0.85rem;
        padding-left: 8px !important;
    }
    .att-filter-compact .form-control {
        height: 36px;
        font-size: 0.85rem;
    }
    #btnLoadAttendance {
        height: 36px;
        padding: 0;
    }
    
    /* Student list - mobile optimized */
    .student-item {
        flex-direction: column;
        text-align: center;
        padding: 0.75rem;
    }
    
    .student-info {
        margin-bottom: 0.75rem;
        justify-content: center;
        text-align: center;
    }
    
    .student-avatar {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .student-details {
        text-align: center;
    }
    
    .student-status {
        width: 100%;
        justify-content: center !important;
    }
    
    /* Status buttons group */
    .status-buttons-group {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .status-buttons-group .btn {
        flex: 1;
        min-width: 70px;
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
    
    /* Family/sibling cards */
    .family-card .card-header {
        padding: 0.75rem;
    }
    
    .family-header-content {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch !important;
    }
    
    .family-header-left {
        text-align: center;
    }
    
    .family-header-right {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .family-header-right .btn {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Sibling row */
    .sibling-row {
        flex-direction: column;
        padding: 0.75rem;
        gap: 0.75rem;
    }
    
    .sibling-info {
        width: 100%;
        justify-content: center;
        text-align: center;
        gap: 0.75rem;
    }
    
    .sibling-info .ms-3 {
        margin-left: 0 !important;
    }
    
    .sibling-photo {
        width: 50px;
        height: 50px;
        margin: 0 auto;
    }
    
    .sibling-status {
        width: 100%;
        text-align: center !important;
    }
    
    /* Bulk actions */
    .bulk-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .bulk-actions .btn {
        width: 100%;
    }
    
    /* Datepicker */
    .datepicker-input {
        width: 100% !important;
    }
    
    /* Cards and containers */
    .card-body {
        padding: 0.75rem;
    }
    
    /* Day status info */
    .day-status-info {
        font-size: 0.8rem;
        padding: 0.5rem;
    }
    
    /* Alert messages */
    .alert {
        font-size: 0.85rem;
        padding: 0.6rem 0.8rem;
    }
}

/* Tablet specific (768px - 992px) */
@media (min-width: 769px) and (max-width: 992px) {
    .filter-row {
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    
    .filter-row .form-group {
        flex: 1;
        min-width: 180px;
    }
    
    .sibling-row {
        padding: 0.75rem;
    }
    
    .student-status .btn-group .btn {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
    }
}

/* Desktop improvements */
@media (min-width: 993px) {
    .search-input-group {
        max-width: 80%;
    }
}

/* Common styles */
.sibling-photo {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 50%;
    background-color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.family-header {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.family-header:hover {
    background-color: #f8f9fa;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
}

.status-badge.text-bg-success { background-color: #28a745; color: white; }
.status-badge.text-bg-danger { background-color: #dc3545; color: white; }
.status-badge.text-bg-warning { background-color: #ffc107; color: #212529; }
.status-badge.text-bg-info { background-color: #17a2b8; color: white; }

/* Loading spinner */
.loading-spinner {
    display: inline-block;
    width: 1.5rem;
    height: 1.5rem;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Row updating animation */
.sibling-row.updating,
.student-item.updating {
    background-color: rgba(52, 152, 219, 0.1);
    transition: background-color 0.2s;
}

/* Search result card transition */
.search-result-card {
    transition: all 0.2s ease;
}

/* Status buttons group */
.status-buttons-group {
    display: flex;
    gap: 0.25rem;
}

.status-buttons-group .btn {
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.status-buttons-group .btn.active {
    font-weight: bold;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);
}

/* Student list item */
.student-item {
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.student-item:last-child {
    border-bottom: none;
}

/* Day status info */
.day-status-info {
    background-color: #f0f7ff;
    border: 1px solid #b8daff;
    border-radius: 8px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}
.day-status-info .att-day-status-text {
    font-weight: 600;
    color: #1a3a5c;
}
.att-school-hours {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    color: #0d47a1;
    border: 1px solid #90caf9;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 0.85rem;
    font-weight: 600;
}
.att-school-hours i {
    color: #1976d2;
}
.day-status-info.is-off-day {
    background: #fff8e6;
    border-color: #ffc107;
}
.day-status-info.is-off-day .att-day-status-text {
    color: #856404;
}

/* Attendance stats bar (AJAX-loaded) */
.att-stats-row {
    display: flex;
    gap: 6px;
    margin-bottom: 10px;
}
.att-stat {
    flex: 1;
    text-align: center;
    border-radius: 8px;
    padding: 6px 4px;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.2;
}
.att-stat b {
    display: block;
    font-size: 1.15rem;
    font-weight: 700;
}
.att-stat-p { background: #d4edda; color: #155724; }
.att-stat-a { background: #f8d7da; color: #721c24; }
.att-stat-l { background: #fff3cd; color: #856404; }
.att-stat-lc { background: #d1ecf1; color: #0c5460; }

/* Mobile student attendance rows */
@media (max-width: 768px) {
    #attWrap #attTable thead { display: none; }
    #attWrap #attTable tbody tr {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        border-bottom: 1px solid #dee2e6;
        padding: 8px 10px;
        gap: 8px;
    }
    #attWrap #attTable tbody td {
        border: none;
        padding: 0;
    }
    #attWrap #attTable .col-sno,
    #attWrap #attTable .col-photo { display: none !important; }
    #attWrap #attTable td:nth-child(3) {
        flex: 1 1 40%;
        font-weight: 600;
        font-size: 0.9rem;
        text-align: left;
    }
    #attWrap #attTable td:nth-child(4) {
        flex: 1 1 55%;
        text-align: right;
    }
    #attWrap .att-selected { display: none; }
    #attWrap .att-choice.att-lg {
        display: flex;
        flex-wrap: nowrap;
        gap: 4px;
        justify-content: flex-end;
    }
    #attWrap .att-choice.att-lg .btn {
        flex: 1;
        min-width: 0;
        padding: 0.35rem 0.25rem !important;
        font-size: 0.75rem !important;
    }
}

/* Responsive table for class view */
.students-table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Touch friendly buttons */
.btn, .nav-link, .family-header, .sibling-row .btn-group label {
    cursor: pointer;
    touch-action: manipulation;
}

/* Improved select2 on mobile */
@media (max-width: 768px) {
    .select2-container--default .select2-selection--single {
        height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
}

/* Classes Container - 1 class per row (full width) */
.classes-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 20px;
}

/* Level 1: Class Main Card - Full width */
.class-main-card {
    border: 1px solid #e0e7ef;
    border-radius: 12px;
    background: white;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
    width: 100%;
}

.class-main-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.class-main-card-header {
    background: #2c3e66;
    color: white;
    padding: 14px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 16px;
    transition: background 0.2s;
}

.class-main-card-header:hover {
    background: #1f2c4b;
}

.class-main-card-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
    padding: 0;
    background: #ffffff;
}

.class-main-card-body.open {
    max-height: 2000px;
    padding: 20px;
}

.subject-count-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
}

.planning-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.planning-table th {
    background-color: #f0f4f8;
    padding: 12px;
    text-align: left;
    border: 1px solid #dce5ef;
    font-weight: 600;
    color: #1e4663;
}

.planning-table td {
    padding: 12px;
    border: 1px solid #e0e7ef;
    vertical-align: top;
}

.subject-name-cell {
    background-color: #f8fafc;
    font-weight: 500;
    color: #2c3e66;
}

.objective-cell {
    line-height: 1.5;
    color: #2d3e50;
}

/* Report Header */
.report-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-start: 4px solid #2c7da0;
}

.expand-icon {
    transition: transform 0.2s;
    font-size: 12px;
}

/* Responsive */
@media (max-width: 768px) {
    .planning-table {
        font-size: 11px;
    }
    
    .planning-table th,
    .planning-table td {
        padding: 8px;
    }
}

/* Print Styles */
@media print {
    .no-print, .btn, .card-tools, .form-group, .alert-info, 
    #clear_btn, #view_btn, #print_btn, select, .select2-container,
    .card-header .btn-tool, #collapseAllBtn {
        display: none !important;
    }
    
    .class-main-card-body {
        max-height: none !important;
        display: block !important;
        padding: 15px !important;
    }
    
    .class-main-card-header {
        background: #e0e7f0 !important;
        color: #1f3a5f !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .planning-table th {
        background: #e0e7f0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

/* ============================================ */
/* TOP LEVEL PLANNING VIEWS - Subject & Class Wise */
/* ============================================ */

/* Subjects Container - 1 subject per row (full width) */
.subjects-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 20px;
}

/* Level 1: Subject Main Card - Full width */
.subject-main-card {
    border: 1px solid #e0e7ef;
    border-radius: 12px;
    background: white;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
    width: 100%;
}

.subject-main-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.subject-main-card-header {
    background: #2c3e66;
    color: white;
    padding: 14px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 16px;
    transition: background 0.2s;
}

.subject-main-card-header:hover {
    background: #1f2c4b;
}

.subject-main-card-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
    padding: 0;
    background: #ffffff;
}

.subject-main-card-body.open {
    max-height: 2000px;
    padding: 20px;
}

.class-count-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}

/* Classes Container - 1 class per row (full width) */
.classes-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 20px;
}

/* Level 1: Class Main Card - Full width */
.class-main-card {
    border: 1px solid #e0e7ef;
    border-radius: 12px;
    background: white;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
    width: 100%;
}

.class-main-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.class-main-card-header {
    background: #2c3e66;
    color: white;
    padding: 14px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 16px;
    transition: background 0.2s;
}

.class-main-card-header:hover {
    background: #1f2c4b;
}

.class-main-card-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
    padding: 0;
    background: #ffffff;
}

.class-main-card-body.open {
    max-height: 2000px;
    padding: 20px;
}

.subject-count-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}

/* Table Styles for both views */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.planning-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    background: white;
}

.planning-table th {
    background-color: #f0f4f8;
    padding: 12px;
    text-align: left;
    border: 1px solid #dce5ef;
    font-weight: 600;
    color: #1e4663;
}

.planning-table td {
    padding: 12px;
    border: 1px solid #e0e7ef;
    vertical-align: top;
}

.class-name-cell,
.subject-name-cell {
    background-color: #f8fafc;
    font-weight: 500;
    color: #2c3e66;
    width: 180px;
}

.objective-cell {
    line-height: 1.5;
    color: #2d3e50;
    word-wrap: break-word;
    white-space: normal;
}

.text-muted em {
    color: #999;
    font-style: italic;
}

/* Report Header */
.report-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-start: 4px solid #2c7da0;
}

.expand-icon {
    transition: transform 0.2s;
    font-size: 12px;
}

/* Responsive Tables */
@media (max-width: 768px) {
    .planning-table {
        font-size: 11px;
    }
    
    .planning-table th,
    .planning-table td {
        padding: 8px;
    }
    
    .class-name-cell,
    .subject-name-cell {
        width: 120px;
    }
    
    .subject-main-card-body.open,
    .class-main-card-body.open {
        padding: 12px;
    }
}

/* Print Styles - Clean and Printable */
@media print {
    /* Hide non-printable elements */
    .no-print, 
    .btn, 
    .card-tools, 
    .form-group, 
    .alert-info, 
    #clear_btn, 
    #view_btn, 
    #print_btn, 
    select, 
    .select2-container,
    .card-header .btn-tool, 
    #collapseAllBtn,
    .nav-tabs,
    .breadcrumb,
    .content-header .btn,
    button {
        display: none !important;
    }
    
    /* Force all cards to be fully expanded in print */
    .subject-main-card-body,
    .class-main-card-body {
        max-height: none !important;
        display: block !important;
        padding: 10px !important;
        overflow: visible !important;
    }
    
    /* Card borders for print */
    .subject-main-card,
    .class-main-card {
        border: 1px solid #ccc !important;
        margin-bottom: 20px !important;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    
    /* Header colors for print */
    .subject-main-card-header,
    .class-main-card-header {
        background: #e0e7f0 !important;
        color: #1f3a5f !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Table header for print */
    .planning-table th {
        background: #e0e7f0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Ensure table borders print properly */
    .planning-table,
    .planning-table th,
    .planning-table td {
        border: 1px solid #aaa !important;
    }
    
    /* Page setup */
    body {
        padding: 0.2in !important;
        margin: 0 !important;
        background: white !important;
    }
    
    /* Report header for print */
    .report-header {
        border: 1px solid #ccc !important;
        background: #f9f9f9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>

<?= view('components/page_header', [
    'title' => 'Students Attendance',
    'icon' => 'fas fa-user-check',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Absentees', 'url' => base_url('admin/students-absentees')],
        ['label' => 'Edit', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-12">
            <!-- Tabbed Interface -->
            <div class="card card-primary card-outline">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="attendanceTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="class-section-tab" data-bs-toggle="pill" href="#class-section-view" role="tab">
                                <i class="fas fa-chalkboard"></i> <span class="d-none d-sm-inline">By Class/Section</span><span class="d-inline d-sm-none">Class</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="search-tab" data-bs-toggle="pill" href="#search-view" role="tab">
                                <i class="fas fa-search"></i> <span class="d-none d-sm-inline">Search by Name</span><span class="d-inline d-sm-none">Search</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <div class="tab-content">
                        <!-- ============================================ -->
                        <!-- TAB 1: Class/Section View (Fully Responsive) -->
                        <!-- ============================================ -->
                        <div class="tab-pane fade show active" id="class-section-view" role="tabpanel">
                            <?= form_open(base_url('admin/students_attendance/save'), 'role="form" id="attendance-form"') ?>
                            <?= form_hidden('id', '') ?>
                            <input type="hidden" name="campus_id" id="campus_id" value="<?= $campus_id ?>" />
                            <input type="hidden" name="session_id" id="session_id" value="<?= $session_id ?>" />
                            
                            <div id="loader-class" class="overlay" style="display: none; position: relative;">
                                <div class="text-center py-5">
                                    <div class="loading-spinner mx-auto mb-2"></div>
                                    <p>Loading students...</p>
                                </div>
                            </div>
                            
                            <!-- Compact filter: row1 class+section, row2 date+load -->
                            <div class="att-filter-compact mb-2">
                                <input type="hidden" name="cls_sec_id" id="cls_sec_id" value="0">
                                <div class="row att-filter-row1 mb-1">
                                    <div class="col-6">
                                        <select class="form-control select2 w-100" name="class_id" id="class_id" title="Class">
                                            <option value="0">Class</option>
                                            <?php if (isset($classesinfo)): ?>
                                                <?php foreach ($classesinfo as $classvalue): ?>
                                                    <option value="<?= $classvalue->class_id ?>"><?= esc($classvalue->class_name) ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select class="form-control select2 w-100" name="section_id" id="section_id" title="Section">
                                            <option value="0">Section</option>
                                            <?php foreach (($sectionsclassinfo ?? []) as $row): ?>
                                                <option value="<?= esc($row['cls_sec_id']) ?>"
                                                        data-is-off="<?= $row['is_off'] ? '1' : '0' ?>"
                                                        data-checkin="<?= esc($row['checkin']) ?>"
                                                        data-checkout="<?= esc($row['checkout']) ?>"
                                                        data-has-attendance="<?= $row['has_attendance'] ? '1' : '0' ?>">
                                                    <?= esc($row['sectionclassname']) ?>
                                                    <?php if ($row['is_off']): ?>[OFF]<?php else: ?>[ON]<?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row att-filter-row2">
                                    <div class="col-10">
                                        <input type="date" name="date" id="date" required value="<?= $date_value ?>" class="form-control w-100 datepicker-input">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-primary w-100" id="btnLoadAttendance" onclick="loadAttendanceByClass()" title="Load attendance">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Day Status Info -->
                            <div id="dayStatusInfo" class="mb-2" style="display: none;">
                                <div class="alert alert-sm p-2 mb-0 day-status-info" id="dayStatusAlert">
                                    <span id="dayStatusText" class="att-day-status-text"></span>
                                    <span id="timingInfo" class="att-school-hours" style="display:none;"></span>
                                </div>
                            </div>
                            
                            <!-- Students List Container -->
                            <div class="students-table-responsive" id="students_list_container">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-3x mb-2 opacity-50"></i>
                                    <p>Select a class/section and date to load attendance</p>
                                </div>
                            </div>
                            
                            <?= form_close() ?>
                        </div>
                        
                        <!-- ============================================ -->
                        <!-- TAB 2: Search by Name View (Fully Responsive) -->
                        <!-- ============================================ -->
                        <div class="tab-pane fade" id="search-view" role="tabpanel">
                            <div class="search-container">
                                <!-- Search Form - Responsive -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card bg-light border-0 shadow-sm">
                                            <div class="card-body p-3 p-sm-4">
                                                <div class="row align-items-end g-3">
                                                    <div class="col-12 col-md-7 mb-3 mb-md-0">
                                                        <label class="form-label fw-bold mb-1">Student Name</label>
                                                        <div class="search-input-group">
                                                            <input type="text" 
                                                                   class="form-control form-control-lg" 
                                                                   id="search_name" 
                                                                   placeholder="Type student name (min. 3 characters)..."
                                                                   autocomplete="off">
                                                            <button class="btn btn-primary btn-lg mt-2 mt-sm-0 ms-sm-2" type="button" id="btnSearch">
                                                                <i class="fas fa-search"></i> Search
                                                            </button>
                                                        </div>
                                                        <small class="text-muted mt-2 d-block">
                                                            <i class="fas fa-info-circle"></i> Shows all siblings of matching students
                                                        </small>
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label fw-bold mb-1">Attendance Date</label>
                                                        <input type="date" id="search_date" class="form-control" value="<?= $date_value ?>">
                                                    </div>
                                                    <div class="col-12 col-md-2">
                                                        <button type="button" class="btn btn-outline-secondary w-100 mt-2 mt-md-0" id="btnResetSearch">
                                                            <i class="fas fa-undo-alt"></i> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Search Results Container -->
                                <div id="search_results_container">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                                        <p class="mb-1">Enter a student name (minimum 3 characters) to search</p>
                                        <p class="small">All siblings will be displayed together for easy attendance marking</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>


    // ============================================
// TOP LEVEL PLANNING - Accordion Functions
// ============================================

// Initialize accordion for Subject Wise View
function initSubjectWiseAccordion() {
    // Initially all bodies are closed
    $('.subject-main-card-body').removeClass('open').css('max-height', '0');
    $('.expand-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    
    // Subject card click handler
    $('.subject-main-card-header').off('click').on('click', function(e) {
        e.stopPropagation();
        var targetBodyId = $(this).data('target');
        var currentBody = $('#' + targetBodyId);
        var isOpen = currentBody.hasClass('open');
        
        if (isOpen) {
            currentBody.removeClass('open').css('max-height', '0');
            $(this).find('.expand-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            currentBody.addClass('open');
            currentBody.css('max-height', currentBody[0].scrollHeight + 'px');
            $(this).find('.expand-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });
}

// Initialize accordion for Class Wise View
function initClassWiseAccordion() {
    // Initially all bodies are closed
    $('.class-main-card-body').removeClass('open').css('max-height', '0');
    $('.expand-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    
    // Class card click handler
    $('.class-main-card-header').off('click').on('click', function(e) {
        e.stopPropagation();
        var targetBodyId = $(this).data('target');
        var currentBody = $('#' + targetBodyId);
        var isOpen = currentBody.hasClass('open');
        
        if (isOpen) {
            currentBody.removeClass('open').css('max-height', '0');
            $(this).find('.expand-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            currentBody.addClass('open');
            currentBody.css('max-height', currentBody[0].scrollHeight + 'px');
            $(this).find('.expand-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });
}

// Collapse all cards function
function collapseAllCards() {
    $('.subject-main-card-body, .class-main-card-body').each(function() {
        $(this).removeClass('open').css('max-height', '0');
        $(this).siblings('.subject-main-card-header, .class-main-card-header').find('.expand-icon')
            .removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });
}


// ============================================
// TAB 1: Class/Section View Functions
// ============================================

/** When true, section <select> change does not auto-load attendance (used while rebuilding options after date change). */
var suppressSectionAttendanceLoad = false;

/**
 * Rebuild section dropdown for the selected date so [OFF]/[ON] and data-* match school_timings for that weekday.
 */
function refreshSectionOptionsForDate(done) {
    var date = $('#date').val();
    var campus_id = $('#campus_id').val();
    if (!date) {
        if (typeof done === 'function') {
            done();
        }
        return;
    }
    $.ajax({
        url: '<?= base_url('admin/students_absentees/sections_for_date') ?>',
        type: 'POST',
        dataType: 'json',
        data: { date: date, campus_id: campus_id },
        success: function (resp) {
            if (!resp || !resp.success || !resp.sections) {
                if (typeof done === 'function') {
                    done();
                }
                return;
            }
            var prev = $('#section_id').val();
            var $sel = $('#section_id');
            suppressSectionAttendanceLoad = true;
            $sel.empty();
            $sel.append($('<option></option>').val('0').text('Section'));
            resp.sections.forEach(function (row) {
                var isOff = !!row.is_off;
                var hasAtt = !!row.has_attendance;
                var chk = row.checkin != null ? String(row.checkin) : '';
                var co = row.checkout != null ? String(row.checkout) : '';
                $('<option></option>')
                    .val(row.cls_sec_id)
                    .text(row.sectionclassname + (isOff ? ' [OFF]' : ' [ON]'))
                    .attr('data-is-off', isOff ? '1' : '0')
                    .attr('data-has-attendance', hasAtt ? '1' : '0')
                    .attr('data-checkin', chk)
                    .attr('data-checkout', co)
                    .appendTo($sel);
            });
            if (prev && $sel.find('option[value="' + prev + '"]').length) {
                $sel.val(prev);
            } else {
                $sel.val('0');
            }
            $sel.trigger('change.select2');
            suppressSectionAttendanceLoad = false;
            $('#cls_sec_id').val($sel.val() || 0);
            if (typeof done === 'function') {
                done();
            }
        },
        error: function () {
            suppressSectionAttendanceLoad = false;
            if (typeof done === 'function') {
                done();
            }
        }
    });
}

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        width: '100%'
    });
    
    $('#section_id').on('change', function () {
        $('#cls_sec_id').val(this.value || 0);
        updateDayStatusInfo();
        if (!suppressSectionAttendanceLoad && parseInt(this.value, 10) > 0) {
            loadAttendanceByClass();
        }
    });

    $('#class_id').on('change', function () {
        updateDayStatusInfo();
        if (parseInt($('#section_id').val(), 10) <= 0 && parseInt(this.value, 10) > 0) {
            loadAttendanceByClass();
        }
    });
    
    $('#date').on('change', function() {
        refreshSectionOptionsForDate(function () {
            updateDayStatusInfo();
            var sectionId = $('#section_id').val();
            var classId = $('#class_id').val();
            if ((sectionId && sectionId != '0') || (classId && classId != '0')) {
                loadAttendanceByClass();
            }
        });
    });

    var preselectClsSecId = <?= (int) ($preselect_cls_sec_id ?? 0) ?>;
    if (preselectClsSecId > 0) {
        refreshSectionOptionsForDate(function () {
            var $sel = $('#section_id');
            if ($sel.find('option[value="' + preselectClsSecId + '"]').length) {
                suppressSectionAttendanceLoad = true;
                $sel.val(String(preselectClsSecId)).trigger('change.select2');
                $('#cls_sec_id').val(preselectClsSecId);
                suppressSectionAttendanceLoad = false;
                updateDayStatusInfo();
                loadAttendanceByClass();
            }
        });
    }
    
    // Reset search button
    $('#btnResetSearch').on('click', function() {
        $('#search_name').val('');
        $('#search_date').val('<?= $date_value ?>');
        $('#search_results_container').html(`
            <div class="text-center text-muted py-5">
                <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                <p class="mb-1">Enter a student name (minimum 3 characters) to search</p>
                <p class="small">All siblings will be displayed together for easy attendance marking</p>
            </div>
        `);
    });
});

function formatAttTime(t) {
    if (!t) return '';
    return String(t).replace(/:\d{2}$/, '').substring(0, 5);
}

function updateDayStatusInfo() {
    var sectionId = $('#section_id').val();
    var date = $('#date').val();
    var $dayStatusInfo = $('#dayStatusInfo');
    var $alert = $('#dayStatusAlert');
    var $timing = $('#timingInfo');

    if (!date || !sectionId || sectionId == '0') {
        $dayStatusInfo.hide();
        return;
    }

    var $selected = $('#section_id option:selected');
    var isOff = String($selected.attr('data-is-off') || $selected.data('isOff') || '0') === '1';
    var hasAttendance = String($selected.attr('data-has-attendance') || $selected.data('hasAttendance') || '0') === '1';
    var checkin = $selected.attr('data-checkin') || $selected.data('checkin') || '';
    var checkout = $selected.attr('data-checkout') || $selected.data('checkout') || '';

    $dayStatusInfo.show();
    $alert.toggleClass('is-off-day', isOff);

    if (isOff) {
        $('#dayStatusText').html('Day is OFF — no attendance can be marked.');
        $timing.hide().html('');
    } else {
        $('#dayStatusText').html(hasAttendance ? 'Existing records' : 'Ready for marking');
        if (checkin && checkout) {
            $timing.show().html('<i class="far fa-clock"></i> ' + formatAttTime(checkin) + ' – ' + formatAttTime(checkout));
        } else {
            $timing.hide().html('');
        }
    }
}

function loadAttendanceByClass() {
    var campus_id = $('#campus_id').val();
    var section_id = parseInt($('#section_id').val(), 10) || 0;
    var class_id = parseInt($('#class_id').val(), 10) || 0;
    var date = $('#date').val();

    $("#students_list_container").html('');
    $("#loader-class").show();

    if (!date || (section_id <= 0 && class_id <= 0)) {
        $("#loader-class").hide();
        $("#students_list_container").html('<div class="alert alert-warning py-2 mb-0">Select a class or section and date.</div>');
        return;
    }

    updateDayStatusInfo();

    // Section wins when both are selected
    var postClassId = section_id > 0 ? 0 : class_id;
    var postSectionId = section_id > 0 ? section_id : 0;

    $.ajax({
        url: '<?= base_url('admin/students_absentees/check_and_load_attendance') ?>',
        type: "POST",
        dataType: 'json',
        data: (window.adminCsrfPayload || function (d) { return d; })({
            section_id: postSectionId,
            class_id: postClassId,
            campus_id: campus_id,
            session_id: $('#session_id').val(),
            date: date
        }),
        success: function(response) {
            if (response.is_off) {
                $("#students_list_container").html(response.html);
            } else if (response.has_records && response.html) {
                $("#students_list_container").html(response.html);
                attachClassViewEventHandlers(date);
            } else if (!response.has_records && response.html) {
                $("#students_list_container").html(response.html);
                attachClassViewEventHandlers(date);
            } else {
                $("#students_list_container").html('<div class="alert alert-info">No attendance records found.</div>');
            }
            $("#loader-class").hide();
        },
        error: function(xhr, status) {
            if (status !== 'abort' && window.refreshAdminCsrf) {
                refreshAdminCsrf(xhr);
            }
            $("#students_list_container").html('<div class="alert alert-danger">Error loading attendance data. Please try again.</div>');
            $("#loader-class").hide();
        }
    });
}

function loadAttendanceData() {
    $("#loader-class").show();
    var campus_id = $("#campus_id").val();
    var section_id = $("#section_id").val();
    var class_id = $("#class_id").val();
    var date = $("#date").val();
    
    $.ajax({
        url: "<?= base_url('admin/students_absentees/load_attendance_records') ?>",
        type: "POST",
        data: {
            section_id: section_id,
            class_id: class_id,
            campus_id: campus_id,
            session_id: $('#session_id').val(),
            date: date
        },
        success: function(response) {
            if (response.status === "success") {
                $("#students_list_container").html(response.html);
                attachClassViewEventHandlers(date);
                if (section_id && section_id != '0') {
                    $('#section_id option:selected').data('has-attendance', '1');
                }
                updateDayStatusInfo();
            } else {
                $("#students_list_container").html('<div class="alert alert-danger">' + response.message + '</div>');
            }
            $("#loader-class").hide();
        },
        error: function() {
            $("#students_list_container").html('<div class="alert alert-danger">Error loading attendance data.</div>');
            $("#loader-class").hide();
        }
    });
}

function attachClassViewEventHandlers(date) {
    // Handle individual status changes in class view
    $('.student-status .btn-group label').off('click').on('click', function(e) {
        e.preventDefault();
        let $label = $(this);
        let $row = $label.closest('.student-item');
        let studentId = $row.data('student-id');
        let newStatus = $label.data('status');
        
        $label.addClass('active').siblings().removeClass('active');
        $label.find('input').prop('checked', true);
        
        let statusText = getStatusText(newStatus);
        let statusClass = getStatusClass(newStatus);
        $row.find('.status-badge')
            .removeClass('text-bg-success text-bg-danger text-bg-warning text-bg-info')
            .addClass(`badge-${statusClass}`)
            .text(statusText);
        
        $row.addClass('updating');
        
        $.ajax({
            url: '<?= base_url('admin/students_absentees/update_attendance_status') ?>',
            type: "POST",
            dataType: 'json',
            data: {
                student_id: studentId,
                attendanceDate: date,
                status: newStatus
            },
            success: function(response) {
                if (!response.success) {
                    toastr.error(response.msg || 'Error updating attendance');
                } else {
                    toastr.success(`Updated to ${response.label}`);
                }
            },
            error: function() {
                toastr.error('Server error. Please try again.');
            },
            complete: function() {
                $row.removeClass('updating');
            }
        });
    });
}

// ============================================
// TAB 2: Search by Name Functions
// ============================================

let searchTimeout;

$('#search_name').on('keyup', function() {
    clearTimeout(searchTimeout);
    let keyword = $(this).val().trim();
    if (keyword.length >= 3) {
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500);
    } else if (keyword.length > 0 && keyword.length < 3) {
        $('#search_results_container').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Please enter at least 3 characters to search.
            </div>
        `);
    } else if (keyword.length === 0) {
        $('#search_results_container').html(`
            <div class="text-center text-muted py-5">
                <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                <p class="mb-1">Enter a student name (minimum 3 characters) to search</p>
                <p class="small">All siblings will be displayed together for easy attendance marking</p>
            </div>
        `);
    }
});

$('#btnSearch').on('click', function() {
    performSearch();
});

$('#search_date').on('change', function() {
    if ($('#search_name').val().trim().length >= 3) {
        performSearch();
    }
});

function performSearch() {
    let keyword = $('#search_name').val().trim();
    let date = $('#search_date').val();
    let campus_id = $('#campus_id').val();
    let session_id = $('#session_id').val();
    
    if (keyword.length < 3) {
        toastr.warning('Please enter at least 3 characters to search.');
        return;
    }
    
    $('#search_results_container').html(`
        <div class="text-center py-5">
            <div class="loading-spinner mx-auto mb-3"></div>
            <p>Searching for "${escapeHtml(keyword)}"...</p>
        </div>
    `);
    
    $.ajax({
        url: '<?= base_url('admin/students_absentees/search_students_by_name') ?>',
        type: "POST",
        dataType: 'json',
        data: {
            keyword: keyword,
            date: date,
            campus_id: campus_id,
            session_id: session_id
        },
        success: function(response) {
            if (response.success) {
                if (response.data && response.data.length > 0) {
                    renderSearchResults(response.data, date);
                } else {
                    $('#search_results_container').html(`
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            No active students found matching "${escapeHtml(keyword)}".
                        </div>
                    `);
                }
            } else {
                $('#search_results_container').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        ${escapeHtml(response.message || 'An error occurred.')}
                    </div>
                `);
            }
        },
        error: function() {
            $('#search_results_container').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Error searching for students. Please try again.
                </div>
            `);
        }
    });
}

function renderSearchResults(families, date) {
    if (!families || families.length === 0) {
        $('#search_results_container').html(`<div class="alert alert-info">No results found.</div>`);
        return;
    }
    
    let totalStudents = families.reduce((sum, f) => sum + f.siblings.length, 0);
    
    let html = `
        <div class="mb-3">
            <div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center">
                <span><i class="fas fa-users"></i> ${families.length} family/ies, ${totalStudents} student(s)</span>
                <small class="text-muted">Date: ${date}</small>
            </div>
        </div>
    `;
    
    families.forEach((family, idx) => {
        html += `
            <div class="card family-card mb-3 search-result-card">
                <div class="card-header bg-light family-header" data-parent-id="${family.parent_id}">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 family-header-content">
                        <div class="family-header-left">
                            <i class="fas fa-users text-primary"></i>
                            <strong>${escapeHtml(family.family_name)}</strong>
                            <span class="badge text-bg-secondary ms-2">${family.sibling_count} child${family.sibling_count !== 1 ? 'ren' : ''}</span>
                        </div>
                        <div class="family-header-right">
                            <button class="btn btn-sm btn-outline-warning bulk-family-mark" data-parent-id="${family.parent_id}" data-status="L">
                                <i class="fas fa-calendar-times"></i> <span class="d-none d-sm-inline">All </span>Leave
                            </button>
                            <button class="btn btn-sm btn-outline-info bulk-family-mark" data-parent-id="${family.parent_id}" data-status="LC">
                                <i class="fas fa-clock"></i> <span class="d-none d-sm-inline">All </span>Late
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
        `;
        
        family.siblings.forEach((student) => {
            let photoHtml = student.profile_photo 
                ? `<img src="<?= base_url('uploads/') ?>${student.profile_photo}" class="sibling-photo" alt="Photo" onerror="this.src='<?= base_url('assets/dist/img/avatar.png') ?>'">`
                : `<div class="sibling-photo bg-secondary d-flex align-items-center justify-content-center text-white"><i class="fas fa-user"></i></div>`;
            
            html += `
                <div class="sibling-row d-flex flex-wrap align-items-center justify-content-between p-3 border-bottom" data-student-id="${student.student_id}">
                    <div class="d-flex flex-wrap align-items-center sibling-info gap-3">
                        ${photoHtml}
                        <div class="student-details">
                            <div class="fw-bold">${escapeHtml(student.name)}</div>
                            <div class="small text-muted">
                                Reg: ${escapeHtml(student.reg_no || 'N/A')} | Class: ${escapeHtml(student.class_name)}
                            </div>
                        </div>
                    </div>
                    <div class="sibling-status mt-2 mt-sm-0">
                        <div class="status-buttons-group btn-group-toggle" data-bs-toggle="buttons">
                            <label class="btn btn-sm btn-outline-warning ${student.status === 'L' ? 'active' : ''}" data-status="L">
                                <input type="radio" name="status_${student.student_id}" value="L" ${student.status === 'L' ? 'checked' : ''}> Leave
                            </label>
                            <label class="btn btn-sm btn-outline-info ${student.status === 'LC' ? 'active' : ''}" data-status="LC">
                                <input type="radio" name="status_${student.student_id}" value="LC" ${student.status === 'LC' ? 'checked' : ''}> Late
                            </label>
                        </div>
                        <span class="status-badge ms-2 badge-${student.status_class}">
                            ${student.status_label}
                        </span>
                    </div>
                </div>
            `;
        });
        
        html += `</div></div></div>`;
    });
    
    $('#search_results_container').html(html);
    attachSearchEventHandlers(date);
}

function attachSearchEventHandlers(date) {
    // Individual status change
    $('.sibling-row .status-buttons-group label').off('click').on('click', function(e) {
        e.preventDefault();
        let $label = $(this);
        let $row = $label.closest('.sibling-row');
        let studentId = $row.data('student-id');
        let newStatus = $label.data('status');
        
        $label.addClass('active').siblings().removeClass('active');
        $label.find('input').prop('checked', true);
        
        let statusText = getStatusText(newStatus);
        let statusClass = getStatusClass(newStatus);
        $row.find('.status-badge')
            .removeClass('text-bg-success text-bg-danger text-bg-warning text-bg-info')
            .addClass(`badge-${statusClass}`)
            .text(statusText);
        
        $row.addClass('updating');
        
        $.ajax({
            url: '<?= base_url('admin/students_absentees/update_attendance_status') ?>',
            type: "POST",
            dataType: 'json',
            data: {
                student_id: studentId,
                attendanceDate: date,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(`Updated to ${response.label}`);
                } else {
                    toastr.error(response.msg || 'Error updating attendance');
                }
            },
            error: function() {
                toastr.error('Server error. Please try again.');
            },
            complete: function() {
                $row.removeClass('updating');
            }
        });
    });
    
    // Bulk family marking
    $('.bulk-family-mark').off('click').on('click', function() {
        let $btn = $(this);
        let parentId = $btn.data('parent-id');
        let newStatus = $btn.data('status');
        let $familyCard = $btn.closest('.family-card');
        let $rows = $familyCard.find('.sibling-row');
        let requests = [];
        
        $rows.each(function() {
            let $row = $(this);
            let studentId = $row.data('student-id');
            
            let $targetLabel = $row.find(`.status-buttons-group label[data-status="${newStatus}"]`);
            $targetLabel.addClass('active').siblings().removeClass('active');
            $targetLabel.find('input').prop('checked', true);
            
            let statusText = getStatusText(newStatus);
            let statusClass = getStatusClass(newStatus);
            $row.find('.status-badge')
                .removeClass('text-bg-success text-bg-danger text-bg-warning text-bg-info')
                .addClass(`badge-${statusClass}`)
                .text(statusText);
            
            $row.addClass('updating');
            
            requests.push(
                $.ajax({
                    url: '<?= base_url('admin/students_absentees/update_attendance_status') ?>',
                    type: "POST",
                    dataType: 'json',
                    data: {
                        student_id: studentId,
                        attendanceDate: date,
                        status: newStatus
                    }
                }).always(function() {
                    $row.removeClass('updating');
                })
            );
        });
        
        $.when.apply($, requests).done(function() {
            toastr.success(`All marked as ${getStatusText(newStatus)}`);
        }).fail(function() {
            toastr.error('Some updates may have failed.');
        });
    });
}

function getStatusText(status) {
    const map = { 'P': 'Present', 'A': 'Absent', 'L': 'Leave', 'LC': 'Late Coming' };
    return map[status] || 'Absent';
}

function getStatusClass(status) {
    const map = { 'P': 'success', 'A': 'danger', 'L': 'warning', 'LC': 'info' };
    return map[status] || 'danger';
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
</script>

<?= $this->endSection() ?>