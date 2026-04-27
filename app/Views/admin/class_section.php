<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Toastr for notifications -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<style>
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
.class-sidebar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.class-sidebar h5 {
    font-weight: 500;
    letter-spacing: 0.5px;
    border-bottom: 2px solid rgba(255,255,255,0.2);
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.class-list {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 5px;
}

.class-list::-webkit-scrollbar {
    width: 5px;
}

.class-list::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}

.class-list::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
}

.class-item {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.class-item:hover {
    background: rgba(255,255,255,0.2);
    transform: translateX(5px);
}

.class-item.active {
    background: white;
    color: var(--dark);
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.class-item.active .badge {
    background: var(--primary);
    color: white;
}

.class-item .badge {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
}

/* Section Cards */
.section-card {
    background: white;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    overflow: hidden;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.section-card:hover {
    box-shadow: 0 10px 25px rgba(67, 97, 238, 0.1);
    border-color: var(--primary-light);
}

.section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header h5 {
    margin: 0;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header .badge {
    background: rgba(255,255,255,0.2);
    font-size: 0.9rem;
    padding: 5px 12px;
    border-radius: 20px;
}

.section-content {
    padding: 20px;
}

/* Section Grid */
.sections-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.section-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 15px;
    transition: all 0.2s ease;
    position: relative;
}

.section-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.05);
    border-color: var(--primary);
}

.section-item.assigned {
    background: #e8f5e9;
    border-color: var(--success);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.section-checkbox {
    width: 22px;
    height: 22px;
    cursor: pointer;
    accent-color: var(--primary);
    transition: transform 0.2s;
}

.section-checkbox:hover {
    transform: scale(1.1);
}

.section-name {
    font-weight: 600;
    color: var(--dark);
    flex: 1;
    font-size: 1.1rem;
}

/* Stats */
.student-count {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.9rem;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.student-count i {
    color: var(--primary);
}

/* Teacher Section */
.teacher-section {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px dashed #dee2e6;
}

.teacher-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    font-size: 0.9rem;
    background: white;
    padding: 8px 12px;
    border-radius: 20px;
}

.teacher-info i {
    color: var(--primary);
}

.teacher-name {
    flex: 1;
    font-weight: 500;
}

.edit-teacher-btn {
    background: none;
    border: none;
    color: var(--primary);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 15px;
    transition: all 0.2s;
}

.edit-teacher-btn:hover {
    background: rgba(67, 97, 238, 0.1);
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
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
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 1.5rem;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark);
    line-height: 1.2;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
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

/* Tooltips */
[data-tooltip] {
    position: relative;
    cursor: help;
}

[data-tooltip]:before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 5px 10px;
    background: var(--dark);
    color: white;
    font-size: 0.8rem;
    border-radius: 5px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s;
    z-index: 1000;
}

[data-tooltip]:hover:before {
    opacity: 1;
    visibility: visible;
    bottom: calc(100% + 5px);
}

/* Responsive */
@media (max-width: 768px) {
    .class-sidebar {
        margin-bottom: 20px;
    }
    
    .class-list {
        max-height: 300px;
    }
    
    .sections-grid {
        grid-template-columns: 1fr;
    }
    
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
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-layer-group mr-2"></i> Class Sections Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Class Sections</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <!-- Loading State -->
        <div id="loader" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading class sections...</p>
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
                    <input type="text" id="sectionSearch" placeholder="Search sections..." onkeyup="filterSections()">
                </div>
                <button class="filter-btn active" onclick="filterByStatus('all')" id="filterAll">
                    <i class="fas fa-list"></i> All Sections
                </button>
                <button class="filter-btn" onclick="filterByStatus('assigned')" id="filterAssigned">
                    <i class="fas fa-check-circle text-success"></i> Assigned
                </button>
                <button class="filter-btn" onclick="filterByStatus('unassigned')" id="filterUnassigned">
                    <i class="fas fa-times-circle text-danger"></i> Unassigned
                </button>
                <button class="filter-btn" onclick="filterByStatus('hasStudents')" id="filterHasStudents">
                    <i class="fas fa-users text-primary"></i> Has Students
                </button>
            </div>

            <div class="row">
                <!-- Classes Sidebar -->
                <div class="col-md-3">
                    <div class="class-sidebar">
                        <h5>
                            <i class="fas fa-graduation-cap mr-2"></i> Classes
                            <span class="badge badge-light float-right" id="totalClasses">0</span>
                        </h5>
                        <div class="class-list" id="classList"></div>
                    </div>
                </div>

                <!-- Sections Content -->
                <div class="col-md-9">
                    <div id="selectedClassInfo" class="mb-3">
                        <h3 id="selectedClassName" class="d-inline-block"></h3>
                        <span class="badge badge-info ml-2" id="sectionCount"></span>
                    </div>
                    <div id="sectionsContainer"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Teacher Assignment Modal -->
<div class="modal fade" id="teacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    Assign Class Teacher
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalClsSecId">
                <div class="form-group">
                    <label>Select Teacher:</label>
                    <select class="form-control" id="modalTeacherSelect">
                        <option value="">Choose a teacher...</option>
                    </select>
                </div>
                <div id="modalSectionInfo" class="small text-muted mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTeacherAssignment()">
                    <i class="fas fa-save mr-1"></i> Save Assignment
                </button>
            </div>
        </div>
    </div>
</div>

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
    classes: [],
    sections: [],
    assignments: {},
    studentCounts: {},
    teachers: {},
    currentClassId: null,
    currentFilter: 'all',
    searchTerm: ''
};

$(document).ready(function() {
    loadData();
});

function loadData() {
    $('#loader').show();
    $('#mainContent').hide();
    
    $.ajax({
        url: '<?= base_url('admin/class-section/getData') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                appData.classes = response.data.classes || [];
                appData.sections = response.data.sections || [];
                appData.assignments = response.data.assignments || {};
               appData.studentCounts = {};

Object.entries(response.data.studentCounts || {}).forEach(([k,v])=>{
    appData.studentCounts[k] = Number(v);
});
                appData.teachers = response.data.teachers || {};
                
                renderClassList();
                updateStats();
                
                // Select first class if available
                if (appData.classes.length > 0) {
                    selectClass(appData.classes[0].class_id);
                }
                
                $('#loader').hide();
                $('#mainContent').fadeIn();
            } else {
                showError('Failed to load data');
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr.responseText);
            showError('Network error occurred');
        }
    });
}

function renderClassList() {
    let html = '';
    
    appData.classes.forEach((classInfo) => {
        // Count sections for this class
        let assignedCount = 0;
        let studentCount = 0;
        
        appData.sections.forEach(section => {
            const key = classInfo.class_id + '_' + section.section_id;
            if (appData.assignments[key]) {
                assignedCount++;
                const clsSecId = appData.assignments[key];
                if (appData.studentCounts[clsSecId]) {
                  studentCount += parseInt(appData.studentCounts[clsSecId]) || 0;
                }
            }
        });
        
        const totalSections = appData.sections.length;
        const progress = totalSections > 0 ? Math.round((assignedCount / totalSections) * 100) : 0;
        
        html += `
            <div class="class-item" onclick="selectClass(${classInfo.class_id})" data-class-id="${classInfo.class_id}">
                <div>
                    <strong>#${classInfo.class_id} - ${escapeHtml(classInfo.class_short_name || classInfo.class_name)}</strong>
                    <div class="small">
                        ${assignedCount}/${totalSections} sections
                    </div>
                </div>
                <div class="text-right">
                    <span class="badge">${studentCount} students</span>
                </div>
            </div>
        `;
    });
    
    $('#classList').html(html);
    $('#totalClasses').text(appData.classes.length);
}

function selectClass(classId) {
    $('.class-item').removeClass('active');
    $(`.class-item[data-class-id="${classId}"]`).addClass('active');
    
    appData.currentClassId = classId;
    const classInfo = appData.classes.find(c => c.class_id == classId);
    
    if (classInfo) {
        $('#selectedClassName').text(`#${classInfo.class_id} - ${escapeHtml(classInfo.class_short_name || classInfo.class_name)}`);
        renderSections();
    }
}

function renderSections() {
    if (!appData.currentClassId) return;
    
    const classInfo = appData.classes.find(c => c.class_id == appData.currentClassId);
    let html = '<div class="sections-grid">';
    
    appData.sections.forEach(section => {
        const key = appData.currentClassId + '_' + section.section_id;
        const clsSecId = appData.assignments[key];
        const isAssigned = !!clsSecId;
       const studentCount = clsSecId ? parseInt(appData.studentCounts[clsSecId]) || 0 : 0;
        const teacher = clsSecId && appData.teachers[clsSecId] ? appData.teachers[clsSecId] : null;
        
        // Apply filters
        if (appData.currentFilter === 'assigned' && !isAssigned) return;
        if (appData.currentFilter === 'unassigned' && isAssigned) return;
        if (appData.currentFilter === 'hasStudents' && studentCount === 0) return;
        
        // Apply search
        if (appData.searchTerm) {
            const sectionName = (section.section_name || '').toLowerCase();
            const shortName = (section.short_name || '').toLowerCase();
            const term = appData.searchTerm.toLowerCase();
            if (!sectionName.includes(term) && !shortName.includes(term)) return;
        }
        
        html += `
            <div class="section-item ${isAssigned ? 'assigned' : ''}" data-section-id="${section.section_id}">
                <div class="section-header">
                    <input type="checkbox" 
                           class="section-checkbox" 
                           data-class-id="${appData.currentClassId}"
                           data-section-id="${section.section_id}"
                           data-cls-sec-id="${clsSecId || ''}"
                           ${isAssigned ? 'checked' : ''}
                           onchange="toggleSection(this)"
                           ${studentCount > 0 ? 'disabled' : ''}>
                    <span class="section-name">
                        ${escapeHtml(section.short_name || section.section_name)}
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="student-count" data-tooltip="Students enrolled">
                        <i class="fas fa-users"></i> ${studentCount}
                    </span>
                    ${isAssigned ? `
                        <button class="btn btn-sm btn-link text-primary" 
                                onclick="openTeacherModal(${clsSecId}, '${escapeHtml(section.section_name)}', event)"
                                ${studentCount > 0 ? 'disabled' : ''}>
                            <i class="fas fa-chalkboard-teacher"></i> Set Teacher
                        </button>
                    ` : ''}
                </div>
                
                ${teacher ? `
                    <div class="teacher-section">
                        <div class="teacher-info">
                            <i class="fas fa-user-circle"></i>
                            <span class="teacher-name" id="teacher-${clsSecId}">
                                ${escapeHtml(teacher.name)}
                            </span>
                            <button class="edit-teacher-btn" 
                                    onclick="openTeacherModal(${clsSecId}, '${escapeHtml(section.section_name)}', event)"
                                    data-tooltip="Change Teacher">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                ` : isAssigned ? `
                    <div class="teacher-section">
                        <div class="teacher-info">
                            <i class="fas fa-user-circle"></i>
                            <span class="teacher-name" id="teacher-${clsSecId}">No teacher assigned</span>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    });
    
    html += '</div>';
    
    const assignedCount = appData.sections.filter(s => {
        const key = appData.currentClassId + '_' + s.section_id;
        return appData.assignments[key];
    }).length;
    
    $('#sectionCount').text(`${assignedCount}/${appData.sections.length} sections assigned`);
    $('#sectionsContainer').html(html);
}

function toggleSection(checkbox) {
    const $cb = $(checkbox);
    const classId = $cb.data('class-id');
    const sectionId = $cb.data('section-id');
    const status = $cb.prop('checked') ? 1 : 0;
    
    $cb.prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('admin/class-section/update') ?>',
        type: 'POST',
        data: {
            class_id: classId,
            section_id: sectionId,
            status: status
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                const key = classId + '_' + sectionId;
                if (status === 1) {
                    appData.assignments[key] = res.cls_sec_id;
                    toastr.success('Section assigned to class');
                } else {
                    delete appData.assignments[key];
                    toastr.success('Section unassigned from class');
                }
                
                // Refresh the view
                selectClass(classId);
                updateStats();
            } else {
                $cb.prop('checked', !status);
                toastr.error(res.message || 'Update failed');
            }
        },
        error: function() {
            $cb.prop('checked', !status);
            toastr.error('Network error occurred');
        },
        complete: function() {
            $cb.prop('disabled', false);
        }
    });
}

function openTeacherModal(clsSecId, sectionName, event) {
    event.stopPropagation();
    
    $('#modalClsSecId').val(clsSecId);
    $('#modalSectionInfo').text(`Assigning teacher for Section: ${sectionName}`);
    
    // Load teachers
    $.ajax({
        url: '<?= base_url('admin/class-section/getTeachers') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(teachers) {
            let options = '<option value="">Choose a teacher...</option>';
            teachers.forEach(t => {
                const name = t.first_name + (t.last_name ? ' ' + t.last_name : '');
                const currentTeacher = appData.teachers[clsSecId];
                const selected = currentTeacher && currentTeacher.id == t.id ? 'selected' : '';
                options += `<option value="${t.id}" ${selected}>${escapeHtml(name)}</option>`;
            });
            
            $('#modalTeacherSelect').html(options).select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#teacherModal')
            });
            
            $('#teacherModal').modal('show');
        }
    });
}

function saveTeacherAssignment() {
    const clsSecId = $('#modalClsSecId').val();
    const teacherId = $('#modalTeacherSelect').val();
    
    $('#teacherModal').modal('hide');
    
    $.ajax({
        url: '<?= base_url('admin/class-section/assignTeacher') ?>',
        type: 'POST',
        data: {
            cls_sec_id: clsSecId,
            teacher_id: teacherId
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                if (teacherId) {
                    appData.teachers[clsSecId] = {
                        id: teacherId,
                        name: res.teacher_name
                    };
                    toastr.success('Teacher assigned successfully');
                } else {
                    delete appData.teachers[clsSecId];
                    toastr.success('Teacher removed');
                }
                
                // Update the teacher display
                const teacherSpan = $(`#teacher-${clsSecId}`);
                if (teacherSpan.length) {
                    if (res.teacher_name) {
                        teacherSpan.text(res.teacher_name);
                    } else {
                        teacherSpan.text('No teacher assigned');
                    }
                }
            } else {
                toastr.error(res.message || 'Failed to assign teacher');
            }
        },
        error: function() {
            toastr.error('Network error occurred');
        }
    });
}

function filterSections() {
    appData.searchTerm = $('#sectionSearch').val();
    if (appData.currentClassId) {
        renderSections();
    }
}

function filterByStatus(filter) {
    appData.currentFilter = filter;
    
    // Update active button
    $('.filter-btn').removeClass('active');
    $(`#filter${filter.charAt(0).toUpperCase() + filter.slice(1)}`).addClass('active');
    
    if (appData.currentClassId) {
        renderSections();
    }
}

function updateStats() {
    let totalClasses = appData.classes.length;
    let totalSections = appData.sections.length;
    let totalAssignments = Object.keys(appData.assignments).length;
    let totalStudents = Object.values(appData.studentCounts).reduce((a, b) => a + b, 0);
    let totalTeachers = new Set(Object.values(appData.teachers).map(t => t.id)).size;
    
    const statsHtml = `
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-school"></i></div>
            <div class="stat-value">${totalClasses}</div>
            <div class="stat-label">Classes</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-columns"></i></div>
            <div class="stat-value">${totalSections}</div>
            <div class="stat-label">Sections</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle text-success"></i></div>
            <div class="stat-value">${totalAssignments}</div>
            <div class="stat-label">Assignments</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value">${totalStudents}</div>
            <div class="stat-label">Students</div>
        </div>
    `;
    
    $('#statsContainer').html(statsHtml);
}

function showError(message) {
    $('#loader').hide();
    $('#errorMessage').show().html(`
        <i class="fas fa-exclamation-triangle mr-2"></i>
        ${message}
        <button class="btn btn-sm btn-outline-danger ml-3" onclick="location.reload()">
            <i class="fas fa-redo mr-1"></i> Retry
        </button>
    `);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?= $this->endSection() ?>