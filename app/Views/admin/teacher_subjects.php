<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Toastr for notifications -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>

/* Teacher item badges */
.filter-item .text-bg-info {
    background-color: #17a2b8;
    color: white;
    padding: 3px 6px;
    font-size: 0.7rem;
}

.filter-item .text-bg-success {
    background-color: #28a745;
    color: white;
    padding: 3px 6px;
    font-size: 0.7rem;
}

.filter-item .text-bg-warning {
    background-color: #ffc107;
    color: #212529;
    padding: 3px 6px;
    font-size: 0.7rem;
}

.filter-item .text-bg-primary {
    background-color: var(--primary);
    color: white;
    padding: 4px 8px;
    font-size: 0.8rem;
    border-radius: 20px;
}

.filter-item .small {
    margin-top: 4px;
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}
	/* Section Header with Inline Incharge */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #dee2e6;
    flex-wrap: wrap;
    gap: 10px;
}

.section-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-incharge-inline {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.2s;
}

.section-incharge-inline.border-primary {
    background: #e7f0ff;
    border: 1px solid var(--primary) !important;
}

.incharge-label {
    font-size: 0.85rem;
    color: var(--primary);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Responsive adjustment */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .section-incharge-inline {
        width: 100%;
        justify-content: space-between;
    }
    
    .section-incharge-inline .teacher-select-wrapper {
        flex: 1;
    }
}
/* Section Incharge Row */
.section-incharge-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    margin-bottom: 12px;
    background: #f0f4fa;
    border-radius: 6px;
    border: 1px solid #dde3ed;
    transition: all 0.2s;
}

.section-incharge-row.border-primary {
    border-color: var(--primary);
    background: #e7f0ff;
}

.incharge-label {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.incharge-label i {
    color: var(--primary);
}

/* Adjust section card padding for integrated layout */
.section-card {
    padding: 12px;
}

/* Make sure teacher selects in both sections align well */
.section-incharge-row .teacher-select-wrapper {
    min-width: 210px;
}

.section-incharge-row .teacher-select {
    width: 180px !important;
}

/* Class ID Badge */
.class-id-badge {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    margin-right: 8px;
}

/* Section ID Badge */
.section-id-badge {
    display: inline-block;
    background: #6c757d;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: 500;
    margin-right: 6px;
}

/* Section name with badge */
.section-name {
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 600;
    color: var(--dark);
    font-size: 1rem;
}

/* Subject grid — 4 columns (fewer vertical scroll); responsive fallbacks */
.subject-grid-4col {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-top: 10px;
    align-items: start;
}

@media (max-width: 1400px) {
    .subject-grid-4col {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 1100px) {
    .subject-grid-4col {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Compact Subject Items */
.subject-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 8px;
    background: white;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    transition: all 0.2s;
    min-height: 36px;
    font-size: 0.85rem;
}

.subject-item:hover {
    background: #e7f0ff;
    border-color: var(--primary);
}

.subject-name {
    font-weight: 500;
    color: var(--dark);
    font-size: 0.85rem;
    min-width: 60px;
    max-width: 90px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 6px;
}

.teacher-select-wrapper {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Compact Teacher Select */
.teacher-select {
    width: 120px !important;
    font-size: 0.8rem !important;
    height: 28px !important;
    padding: 2px 4px !important;
}

/* Select2 customization for compact view */
.select2-container--bootstrap-5 {
    width: 120px !important;
}

.select2-container--bootstrap-5 .select2-selection--single {
    height: 28px !important;
    padding: 2px 8px !important;
    font-size: 0.8rem !important;
}

.select2-container--bootstrap-5 .select2-selection__arrow {
    height: 26px !important;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    line-height: 22px !important;
    font-size: 0.8rem !important;
}

/* Compact Badge */
.text-bg-warning {
    background: #ffc107;
    color: #000;
    font-size: 0.65rem;
    padding: 2px 5px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
}

@media (max-width: 576px) {
    .subject-grid-4col {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 992px) {
    .subject-item {
        flex-wrap: wrap;
    }
    
    .teacher-select-wrapper {
        width: 100%;
        justify-content: space-between;
    }
    
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }
    
    .teacher-select {
        width: 100% !important;
    }
}

/* One section card per row — full width (auto-fill caused narrow cards when only one section) */
.sections-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
    width: 100%;
}

#sectionsContainer {
    width: 100%;
    max-width: 100%;
}

:root {
    --primary: #4361ee;
    --primary-light: #4895ef;
    --success: #06d6a0;
    --warning: #ffd166;
    --danger: #ef476f;
    --dark: #2b2d42;
    --light: #f8f9fa;
}

/* Layout */
.sidebar-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.sidebar-gradient h5 {
    font-weight: 500;
    letter-spacing: 0.5px;
    border-bottom: 2px solid rgba(255,255,255,0.2);
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.filter-list {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 5px;
}

.filter-list::-webkit-scrollbar {
    width: 5px;
}

.filter-list::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}

.filter-list::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
}

.filter-item {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 12px 15px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-item:hover {
    background: rgba(255,255,255,0.2);
    transform: translateX(5px);
}

.filter-item.active {
    background: white;
    color: var(--dark);
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.filter-item.active .badge {
    background: var(--primary);
    color: white;
}

.filter-item .badge {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
}

/* Class Cards */
.class-card {
    background: white;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    width: 100%;
    max-width: 100%;
}

.class-card:hover {
    box-shadow: 0 10px 30px rgba(67, 97, 238, 0.1);
    border-color: var(--primary-light);
}

.class-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.class-header h5 {
    margin: 0;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.class-header .badge {
    background: rgba(255,255,255,0.2);
    font-size: 0.9rem;
    padding: 5px 12px;
    border-radius: 20px;
}

.class-content {
    padding: 20px;
}

.section-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.section-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-color: var(--primary);
}

.section-card.complete {
    background: #e8f5e9;
    border-color: var(--success);
}

.section-card.partial {
    background: #fff3e0;
    border-color: #ffb74d;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #dee2e6;
}

.section-name {
    font-weight: 600;
    color: var(--dark);
    font-size: 1rem;
}

.section-progress {
    font-size: 0.8rem;
    padding: 3px 8px;
    border-radius: 12px;
    background: rgba(0,0,0,0.05);
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(67, 97, 238, 0.1);
}

.stat-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    color: white;
    font-size: 1.3rem;
}

.stat-value {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--dark);
    line-height: 1.2;
}

.stat-label {
    color: #6c757d;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Filter Bar */
.filter-bar {
    background: white;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

.filter-btn {
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 0.9rem;
    border: 1px solid #dee2e6;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
    color: #495057;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.filter-btn:hover {
    background: #f8f9fa;
    border-color: var(--primary);
    color: var(--primary);
}

.filter-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.filter-btn.active i {
    color: white;
}

/* Search Box */
.search-box {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.search-box input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    border: 1px solid #dee2e6;
    border-radius: 25px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
}

.subject-item.opacity-60,
.section-incharge-inline.opacity-60 {
    opacity: 0.55;
    transition: opacity 0.15s ease;
}

/* Badge styling */
.text-bg-warning {
    background: #ffc107;
    color: #000;
    font-size: 0.7rem;
    padding: 2px 6px;
    margin-left: 5px;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-bar {
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<!-- Page Header -->
<?= view('components/page_header', [
    'title' => 'Teacher & Class Incharge Assignment',
    'icon' => 'fas fa-chalkboard-teacher',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Teacher Assignments', 'active' => true],
    ],
]) ?>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <!-- Loading State -->
        <div id="loader" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading assignments...</p>
        </div>

        <!-- Error State -->
        <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>

        <!-- Main Content -->
        <div id="mainContent" style="display: none;">
            <!-- Stats Cards -->
            <div class="stats-grid" id="statsContainer"></div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchFilter" placeholder="Search by subject name…">
                </div>
                <button class="filter-btn active" onclick="filterBy('all')" id="filterAll">
                    <i class="fas fa-list"></i> All
                </button>
                <button class="filter-btn" onclick="filterBy('assigned')" id="filterAssigned">
                    <i class="fas fa-check-circle text-success"></i> Assigned
                </button>
                <button class="filter-btn" onclick="filterBy('unassigned')" id="filterUnassigned">
                    <i class="fas fa-times-circle text-danger"></i> Unassigned
                </button>
                <button class="filter-btn" onclick="filterBy('complete')" id="filterComplete">
                    <i class="fas fa-star text-warning"></i> Complete
                </button>
            </div>

            <p class="text-muted mb-3 small">
                <i class="fas fa-cloud-upload-alt me-1"></i>
                Assignments save automatically when you change a teacher. Use filters and search to narrow the list.
            </p>

            <div id="sectionsContainer"></div>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Toastr Configuration
toastr.options = {
    positionClass: 'toast-top-right',
    progressBar: true,
    timeOut: 3000,
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut'
};

// App State
let appData = {
    teachers: [],
    subjects: [],
    classSections: [],
    sectionSubjectMap: {},
    teacherSubjectMap: {},
    sectionTeacherMap: {},
    searchTerm: '',
    filterType: 'all',
    currentOpenClass: null
};

$(document).ready(function() {
    loadData();
});

function loadData(opts) {
    const silent = !!(opts && opts.silent);
    if (!silent) {
        $('#loader').show();
        $('#mainContent').hide();
    }

    $.ajax({
        url: '<?= base_url('admin/teacher_subjects/getData') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                appData.teachers = response.data.teachers || [];
                appData.subjects = response.data.subjects || [];
                appData.classSections = response.data.classSections || [];
                appData.sectionSubjectMap = response.data.sectionSubjectMap || {};
                appData.teacherSubjectMap = response.data.teacherSubjectMap || {};

                loadSectionTeacherData();

                updateStats();

                if (!silent) {
                    $('#loader').hide();
                    $('#mainContent').fadeIn();
                }

                renderSections();
            } else {
                if (!silent) showError('Failed to load data');
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr.responseText);
            if (!silent) showError('Network error occurred');
        }
    });
}

function loadSectionTeacherData() {
    $.ajax({
        url: '<?= base_url('admin/teacher_subjects/getSectionTeachers') ?>',
        type: 'GET',
        dataType: 'json',
        async: false, // Make it synchronous to ensure data is loaded
        success: function(response) {
            if (response.status === 'success') {
                appData.sectionTeacherMap = response.data.sectionTeacherMap || {};
            }
        }
    });
}

function renderSections() {
    // Group class sections by class
    const classesMap = {};
    appData.classSections.forEach(cs => {
        const className = cs.class_name || cs.class_short_name || 'Unknown Class';
        const classId = cs.class_id;
        
        if (!classesMap[className]) {
            classesMap[className] = {
                name: className,
                classId: classId,
                sections: []
            };
        }
        classesMap[className].sections.push(cs);
    });
    
    let html = '';
    
    // Sort classes by class ID
    const sortedClassEntries = Object.values(classesMap).sort((a, b) => (a.classId || 0) - (b.classId || 0));
    
    // Set first class as open by default
    if (!appData.currentOpenClass && sortedClassEntries.length > 0) {
        appData.currentOpenClass = sortedClassEntries[0].name;
    }
    
    sortedClassEntries.forEach(classData => {
        const className = classData.name;
        const classId = classData.classId;
        const classIdStr = className.replace(/\s/g, '');
        const isOpen = appData.currentOpenClass === className;
        
        html += `
            <div class="class-card">
                <div class="class-header" onclick="toggleClass('${className}')">
                    <h5>
                        <i class="fas fa-chevron-${isOpen ? 'down' : 'right'}" id="icon-${classIdStr}"></i>
                        <span class="class-id-badge">#${classId}</span>
                        ${escapeHtml(className)}
                    </h5>
                    <span class="badge">${classData.sections.length} sections</span>
                </div>
                <div class="class-content" id="content-${classIdStr}" style="display: ${isOpen ? 'block' : 'none'};">
                    <div class="sections-grid">
        `;
        
        // Sort sections by section ID
        const sortedSections = [...classData.sections].sort((a, b) => (a.section_id || 0) - (b.section_id || 0));
        
        sortedSections.forEach(section => {
            const sectionName = `${section.section_name || section.section_short_name || 'Section'}`;
            
            // Get subjects for this section (used for "complete" filter)
            const sectionSubjectsPreview = appData.sectionSubjectMap[section.cls_sec_id] || {};
            const previewSubjectIds = Object.keys(sectionSubjectsPreview);
            if (appData.filterType === 'complete' && previewSubjectIds.length > 0) {
                const allSubjectsStaffed = previewSubjectIds.every(subId => {
                    const ssid = sectionSubjectsPreview[subId];
                    const tid = (appData.teacherSubjectMap[section.cls_sec_id] || {})[ssid];
                    return !!tid;
                });
                if (!allSubjectsStaffed) {
                    return;
                }
            }

            // Get current section incharge
            const currentSectionTeacherId = appData.sectionTeacherMap[section.cls_sec_id] || null;
            const selectedSectionTeacherId = currentSectionTeacherId;
            
            // Get subjects for this section
            const sectionSubjects = appData.sectionSubjectMap[section.cls_sec_id] || {};
            const subjectIds = Object.keys(sectionSubjects);
            
            // Section card with inline incharge selection
            html += `
                <div class="section-card">
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="section-id-badge">#${section.section_id}</span>
                            <span class="section-name">${escapeHtml(sectionName)}</span>
                        </div>
                        
                        <!-- Section Incharge Selection Inline -->
                        <div class="section-incharge-inline" 
                             style="display: flex; align-items: center; gap: 8px;">
                            <span class="incharge-label" style="font-size: 0.85rem; color: var(--primary);">
                                <i class="fas fa-chalkboard-teacher"></i> Class Teacher:
                            </span>
                            <div class="teacher-select-wrapper" style="min-width: 180px;">
                                <select class="form-control form-control-sm section-teacher-select" 
                                        data-section-id="${section.cls_sec_id}"
                                        onchange="handleSectionTeacherChange(this, ${section.cls_sec_id})">
                                    <option value="">— Not Assigned —</option>
            `;
            
            // Add teacher options
            appData.teachers.forEach(teacher => {
                const teacherName = escapeHtml(teacher.first_name) + (teacher.last_name ? ' ' + escapeHtml(teacher.last_name) : '');
                const selected = teacher.id == selectedSectionTeacherId ? 'selected' : '';
                html += `<option value="${teacher.id}" ${selected}>${teacherName}</option>`;
            });
            
            html += `</select>`;
            
            html += `</div></div></div>`; // Close section-header and incharge inline
            
            if (subjectIds.length === 0) {
                html += `
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p class="small mt-1">No subjects assigned to this section</p>
                    </div>
                `;
            } else {
                // Count assigned teachers
                let assignedCount = 0;
                subjectIds.forEach(subjectId => {
                    const secSubId = sectionSubjects[subjectId];
                    const teacherId = (appData.teacherSubjectMap[section.cls_sec_id] || {})[secSubId];
                    if (teacherId) assignedCount++;
                });
                
                html += `<div class="subject-grid-4col">`;
                
                // Get subjects for this section
                const subjectsForSection = [];
                appData.subjects.forEach(subject => {
                    const secSubId = sectionSubjects[subject.sid];
                    if (!secSubId) return;
                    
                    const currentTeacherId = (appData.teacherSubjectMap[section.cls_sec_id] || {})[secSubId];
                    const selectedTeacherId = currentTeacherId;
                    
                    // Apply filters
                    if (appData.filterType === 'assigned' && !selectedTeacherId) return;
                    if (appData.filterType === 'unassigned' && selectedTeacherId) return;
                    if (appData.filterType === 'complete' && !selectedTeacherId) return;
                    
                    // Apply search
                    if (appData.searchTerm) {
                        const term = appData.searchTerm.toLowerCase();
                        const subjectMatch = (subject.subject_short_name || '').toLowerCase().includes(term) ||
                                           (subject.subject_name || '').toLowerCase().includes(term);
                        if (!subjectMatch) return;
                    }
                    
                    subjectsForSection.push({
                        subject: subject,
                        secSubId: secSubId,
                        currentTeacherId: currentTeacherId,
                        selectedTeacherId: selectedTeacherId
                    });
                });
                
                // Sort subjects
                subjectsForSection.sort((a, b) => {
                    const nameA = (a.subject.subject_short_name || a.subject.subject_name || '').toLowerCase();
                    const nameB = (b.subject.subject_short_name || b.subject.subject_name || '').toLowerCase();
                    return nameA.localeCompare(nameB);
                });
                
                subjectsForSection.forEach(item => {
                    html += renderSubjectItem(item, section.cls_sec_id);
                });
                
                html += `</div>`; // Close subject-grid-4col
            }
            
            html += `</div>`; // Close section-card
        });
        
        html += `</div></div></div>`;
    });
    
    $('#sectionsContainer').find('.teacher-select, .section-teacher-select').each(function () {
        const $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) {
            try {
                $el.select2('destroy');
            } catch (e) { /* ignore */ }
        }
    });

    $('#sectionsContainer').html(html);
    
    // Initialize Select2
    $('.teacher-select').select2({
        theme: 'bootstrap-5',
        width: '120px',
        placeholder: 'Select',
        allowClear: true,
        minimumResultsForSearch: 5
    });
    
    $('.section-teacher-select').select2({
        theme: 'bootstrap-5',
        width: '180px',
        placeholder: 'Select Class Teacher',
        allowClear: true,
        minimumResultsForSearch: 5
    });
}


function renderSubjectItem(item, clsSecId) {
    const subject = item.subject;
    const selectedTeacherId = item.selectedTeacherId;
    const displayName = subject.subject_short_name || subject.subject_name;
    
    let html = `
        <div class="subject-item" data-cls-sec="${clsSecId}" data-subject-id="${subject.sid}">
            <span class="subject-name" title="${escapeHtml(subject.subject_name)}">
                ${escapeHtml(displayName)}
            </span>
            <div class="teacher-select-wrapper">
                <select class="form-control form-control-sm teacher-select" 
                        onchange="handleTeacherChange(this, ${clsSecId}, ${subject.sid})">
                    <option value="">—</option>
    `;
    
    // Sort teachers
    const sortedTeachers = [...appData.teachers].sort((a, b) => {
        const nameA = (a.first_name + ' ' + (a.last_name || '')).toLowerCase();
        const nameB = (b.first_name + ' ' + (b.last_name || '')).toLowerCase();
        return nameA.localeCompare(nameB);
    });
    
    sortedTeachers.forEach(t => {
        const selected = t.id == selectedTeacherId ? 'selected' : '';
        const teacherName = escapeHtml(t.first_name) + (t.last_name ? ' ' + escapeHtml(t.last_name) : '');
        html += `<option value="${t.id}" ${selected}>${teacherName}</option>`;
    });
    
    html += `</select>`;
    
    html += `</div></div>`;
    
    return html;
}

function toggleClass(className) {
    if (appData.currentOpenClass === className) {
        appData.currentOpenClass = null;
    } else {
        appData.currentOpenClass = className;
    }
    renderSections();
}

function csrfPostFields() {
    const o = {};
    <?php if (function_exists('csrf_token')): ?>
    o['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
    <?php endif ?>
    return o;
}

function handleTeacherChange(select, clsSecId, subjectId) {
    const raw = $(select).val();
    const teacherId = raw ? parseInt(raw, 10) : null;
    const secSubId = (appData.sectionSubjectMap[clsSecId] || {})[subjectId];
    const prev = secSubId ? (appData.teacherSubjectMap[clsSecId] || {})[secSubId] : null;

    if (teacherId == prev) {
        return;
    }

    const $item = $(select).closest('.subject-item');
    $item.addClass('opacity-60');
    $(select).prop('disabled', true);

    $.ajax({
        url: '<?= base_url('admin/teacher_subjects/saveAll') ?>',
        type: 'POST',
        dataType: 'json',
        data: $.extend({
            subject_assignments: JSON.stringify([{
                type: 'subject',
                cls_sec_id: parseInt(clsSecId, 10),
                subject_id: parseInt(subjectId, 10),
                teacher_id: teacherId
            }]),
            section_assignments: JSON.stringify([])
        }, csrfPostFields()),
        success: function(response) {
            $item.removeClass('opacity-60');
            $(select).prop('disabled', false);
            if (response.success) {
                loadData({ silent: true });
            } else {
                toastr.error(response.message || 'Could not save');
                loadData({ silent: true });
            }
        },
        error: function() {
            $item.removeClass('opacity-60');
            $(select).prop('disabled', false);
            toastr.error('Could not save assignment');
            loadData({ silent: true });
        }
    });
}

function handleSectionTeacherChange(select, clsSecId) {
    const raw = $(select).val();
    const teacherId = raw ? parseInt(raw, 10) : null;
    const prev = appData.sectionTeacherMap[clsSecId] || null;

    if (teacherId == prev) {
        return;
    }

    const $box = $(select).closest('.section-incharge-inline');
    $box.addClass('opacity-60');
    $(select).prop('disabled', true);

    $.ajax({
        url: '<?= base_url('admin/teacher_subjects/saveAll') ?>',
        type: 'POST',
        dataType: 'json',
        data: $.extend({
            subject_assignments: JSON.stringify([]),
            section_assignments: JSON.stringify([{
                type: 'section',
                cls_sec_id: parseInt(clsSecId, 10),
                teacher_id: teacherId
            }])
        }, csrfPostFields()),
        success: function(response) {
            $box.removeClass('opacity-60');
            $(select).prop('disabled', false);
            if (response.success) {
                loadData({ silent: true });
            } else {
                toastr.error(response.message || 'Could not save');
                loadData({ silent: true });
            }
        },
        error: function() {
            $box.removeClass('opacity-60');
            $(select).prop('disabled', false);
            toastr.error('Could not save class teacher');
            loadData({ silent: true });
        }
    });
}

function filterBy(type) {
    appData.filterType = type;

    $('.filter-btn').removeClass('active');
    $(`#filter${type.charAt(0).toUpperCase() + type.slice(1)}`).addClass('active');

    renderSections();
}

$('#searchFilter').on('keyup', function() {
    appData.searchTerm = $(this).val();
    renderSections();
});
function updateStats() {
    let totalTeachers = appData.teachers.length;
    let totalSubjects = appData.subjects.length;
    let totalSections = appData.classSections.length;
    
    // Count subject assignments
    let subjectAssignments = 0;
    Object.values(appData.teacherSubjectMap).forEach(section => {
        subjectAssignments += Object.keys(section || {}).length;
    });
    
    // Count section incharges
    let sectionAssignments = Object.keys(appData.sectionTeacherMap).length;
    
    // Count unique teachers with assignments
    let teachersWithSubjects = new Set();
    let teachersWithSections = new Set();
    
    Object.values(appData.teacherSubjectMap).forEach(section => {
        Object.values(section || {}).forEach(teacherId => {
            if (teacherId) teachersWithSubjects.add(teacherId);
        });
    });
    
    Object.values(appData.sectionTeacherMap).forEach(teacherId => {
        if (teacherId) teachersWithSections.add(teacherId);
    });
    
    const statsHtml = `
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="stat-value">${totalTeachers}</div>
            <div class="stat-label">Total Teachers</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-value">${totalSubjects}</div>
            <div class="stat-label">Subjects</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="stat-value">${totalSections}</div>
            <div class="stat-label">Sections</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle text-success"></i></div>
            <div class="stat-value">${subjectAssignments}</div>
            <div class="stat-label">Subject Teachers</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users text-primary"></i></div>
            <div class="stat-value">${sectionAssignments}</div>
            <div class="stat-label">Class Incharges</div>
        </div>
    `;
    
    $('#statsContainer').html(statsHtml);
}

function showError(message) {
    $('#loader').hide();
    $('#errorMessage').show().html(`
        <i class="fas fa-exclamation-triangle me-2"></i>
        ${message}
        <button class="btn btn-sm btn-outline-danger ms-3" onclick="location.reload()">
            <i class="fas fa-redo me-1"></i> Retry
        </button>
    `);
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>

<?= $this->endSection() ?>