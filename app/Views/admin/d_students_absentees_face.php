<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$campus_id = $sessionData['campusid'];
$session_id = $sessionData['sessionid'];
$date_value = $sessionData['date'];
?>

<style>
/* Tab Styles */
.nav-tabs .nav-link {
    padding: 10px 20px;
    font-size: 16px;
}

.nav-tabs .nav-link i {
    margin-right: 8px;
}

.tab-pane {
    padding: 20px 0;
}

/* Class/Section Attendance Styles */
.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
    margin-bottom: 20px;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

/* Search Styles */
.search-container {
    max-width: 600px;
    margin: 0 auto 30px;
}

.search-input-group {
    display: flex;
    gap: 10px;
}

.search-input-group input {
    flex: 1;
    padding: 12px;
    font-size: 16px;
    border: 2px solid #ddd;
    border-radius: 8px;
}

.search-input-group button {
    padding: 12px 24px;
}

.family-card {
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.family-header {
    background: #f8f9fa;
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid #ddd;
}

.sibling-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}

.sibling-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.sibling-photo {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.status-buttons .btn {
    margin: 0 2px;
    padding: 5px 12px;
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
}

.badge-success { background: #28a745; color: white; }
.badge-danger { background: #dc3545; color: white; }
.badge-warning { background: #ffc107; color: #856404; }
.badge-info { background: #17a2b8; color: white; }

/* Face Recognition Styles */
.face-container {
    max-width: 800px;
    margin: 0 auto;
}

.video-wrapper {
    position: relative;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
}

#video {
    width: 100%;
    height: auto;
    background: #000;
    transform: scaleX(-1);
}

#canvas {
    display: none;
}

.face-overlay {
    position: absolute;
    bottom: 20px;
    left: 0;
    right: 0;
    text-align: center;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 10px;
}

.camera-controls {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.recognition-result {
    margin-top: 20px;
    padding: 20px;
    border-radius: 12px;
    display: none;
}

.recognition-result.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    display: block;
}

.recognition-result.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    display: block;
}

.recognition-result.info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
    display: block;
}

.student-card {
    background: white;
    border-radius: 12px;
    padding: 15px;
    margin-top: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.face-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #007bff;
}

.status-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
}

.status-late {
    background: #ffc107;
    color: #856404;
}

.status-present {
    background: #28a745;
    color: white;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.video-wrapper-small {
    background: #000;
    border-radius: 8px;
    overflow: hidden;
}

#register_video {
    width: 100%;
    height: auto;
    background: #000;
    transform: scaleX(-1);
}

.model-loading {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.9);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-direction: column;
}

@media (max-width: 768px) {
    .camera-controls button {
        flex: 1;
        font-size: 14px;
    }
    
    .student-card {
        text-align: center;
    }
    
    .filter-row {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .sibling-row {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .sibling-info {
        flex-direction: column;
    }
    
    .search-input-group {
        flex-direction: column;
    }
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Students Attendance</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="attendanceTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="class-section-tab" data-toggle="pill" href="#class-section-view" role="tab">
                            <i class="fas fa-chalkboard"></i> By Class/Section
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="search-tab" data-toggle="pill" href="#search-view" role="tab">
                            <i class="fas fa-search"></i> Search by Name
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="face-tab" data-toggle="pill" href="#face-view" role="tab">
                            <i class="fas fa-camera"></i> Face Recognition
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    
                    <!-- ============================================ -->
                    <!-- TAB 1: Class/Section Attendance View -->
                    <!-- ============================================ -->
                    <div class="tab-pane fade show active" id="class-section-view" role="tabpanel">
                        <div id="loader-1" class="overlay" style="display: none; position: relative; min-height: 100px;">
                            <div class="d-flex justify-content-center align-items-center" style="height: 100px;">
                                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                            </div>
                        </div>
                        
                        <input type="hidden" name="campus_id" id="campus_id" value="<?= $campus_id ?>" />
                        <input type="hidden" name="session_id" id="session_id" value="<?= $session_id ?>" />
                        
                        <div class="filter-row">
                            <div class="filter-group">
                                <label><i class="fas fa-graduation-cap"></i> Select Class</label>
                                <select class="form-control select2" name="class_id" id="class_id">
                                    <option value="0">Select Class</option>
                                    <?php if(isset($classesinfo) && $classesinfo): ?>
                                        <?php foreach ($classesinfo as $classvalue): ?>
                                            <option value="<?= $classvalue->class_id ?>"><?= $classvalue->class_name ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="0">No classes available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label><i class="fas fa-layer-group"></i> Or Select Section</label>
                                <input type="hidden" name="cls_sec_id" id="cls_sec_id" value="0">
                                <select class="form-control select2" name="section_id" id="section_id">
                                    <option value="0">Select Section</option>
                                    <?php if(isset($sectionsclassinfo) && $sectionsclassinfo): ?>
                                        <?php foreach ($sectionsclassinfo as $row): ?>
                                            <option value="<?= esc($row['cls_sec_id']) ?>"><?= esc($row['sectionclassname']) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="0">No sections available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label><i class="fas fa-calendar-alt"></i> Date</label>
                                <input type="date" name="date" id="date" required value="<?= $date_value ?>" class="form-control">
                            </div>
                            
                            <div class="filter-group">
                                <label>&nbsp;</label>
                                <button type="button" onclick="loadAttendanceByClass();" class="btn btn-primary btn-block">
                                    <i class="fas fa-users"></i> Load Students
                                </button>
                            </div>
                        </div>
                        
                        <div id="students_list_container"></div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- TAB 2: Search by Name View -->
                    <!-- ============================================ -->
                    <div class="tab-pane fade" id="search-view" role="tabpanel">
                        <div class="search-container">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-alt"></i> Attendance Date</label>
                                        <input type="date" id="search_date" class="form-control" value="<?= $date_value ?>">
                                    </div>
                                    <div class="search-input-group">
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="search_name" 
                                               placeholder="Search by student name (minimum 3 characters)..."
                                               autocomplete="off">
                                        <button class="btn btn-primary btn-lg" type="button" id="btnSearch">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-info-circle"></i> 
                                        Shows all siblings of matching students. Type at least 3 characters.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div id="search_results_container">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-search fa-3x mb-3"></i>
                                <p>Enter a student name (minimum 3 characters) to search and mark attendance.</p>
                                <p class="small">All siblings of the matching student will be displayed together.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- TAB 3: Face Recognition View (Lazy Loaded) -->
                    <!-- ============================================ -->
                    <div class="tab-pane fade" id="face-view" role="tabpanel">
                        <div class="face-container">
                            <!-- Loading Indicator -->
                            <div id="modelLoading" class="model-loading" style="display: none;">
                                <div class="loading-spinner mb-3"></div>
                                <h4>Loading Face Recognition Models...</h4>
                                <p class="text-muted">First time may take 10-15 seconds</p>
                            </div>
                            
                            <!-- Date Selection -->
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Attendance Date</label>
                                <input type="date" id="attendance_date" class="form-control" value="<?= $date_value ?>">
                            </div>
                            
                            <!-- Camera Feed - Initially Empty -->
                            <div class="video-wrapper">
                                <video id="video" autoplay playsinline style="display: none;"></video>
                                <canvas id="canvas"></canvas>
                                <div class="face-overlay">
                                    <span id="camera_status">Click "Start Camera" to begin face recognition</span>
                                </div>
                            </div>
                            
                            <!-- Camera Controls -->
                            <div class="camera-controls">
                                <button id="start_camera_btn" class="btn btn-success btn-lg">
                                    <i class="fas fa-video"></i> Start Camera
                                </button>
                                <button id="capture_btn" class="btn btn-primary btn-lg" disabled>
                                    <i class="fas fa-camera"></i> Capture & Recognize
                                </button>
                                <button id="register_mode_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#registerModal">
                                    <i class="fas fa-user-plus"></i> Register New Face
                                </button>
                                <button id="reset_camera_btn" class="btn btn-secondary btn-lg" style="display: none;">
                                    <i class="fas fa-sync-alt"></i> Reset Camera
                                </button>
                            </div>
                            
                            <!-- Recognition Result -->
                            <div id="recognition_result" class="recognition-result"></div>
                        </div>
                        
                        <!-- Registered Students List -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-users"></i> Registered Students
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="registered_students_list" class="row">
                                            <div class="col-12 text-center text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Loading registered students...
                                            </div>
                                        </div>
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

<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register Student Face</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Select Student</label>
                            <select id="register_student_id" class="form-control">
                                <option value="">-- Select Student --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Capture Face</label>
                            <div class="video-wrapper-small">
                                <video id="register_video" autoplay playsinline style="width:100%; border-radius:8px;"></video>
                            </div>
                            <button id="capture_register_btn" class="btn btn-success btn-block mt-2">
                                <i class="fas fa-camera"></i> Capture Photo
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>Preview</label>
                        <div id="register_preview" class="text-center p-3 border rounded" style="min-height: 250px;">
                            <img id="captured_image_preview" src="" style="max-width:100%; border-radius:8px; display:none;">
                            <p class="text-muted" id="preview_placeholder">Captured photo will appear here</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="save_registration_btn" class="btn btn-primary">Save Registration</button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// TAB 1: Class/Section View Functions
// ============================================

$(document).ready(function() {
    $('#section_id').on('change', function () {
        $('#cls_sec_id').val(this.value || 0);
        if (parseInt(this.value, 10) > 0) {
            $('#class_id').val('0').trigger('change.select2');
        }
    }).trigger('change');

    $('#class_id').on('change', function () {
        if (parseInt(this.value, 10) > 0) {
            $('#section_id').val('0').trigger('change.select2');
            $('#cls_sec_id').val(0);
        }
    });
});

function loadAttendanceByClass() {
    var campus_id = $('#campus_id').val();
    var section_id = $('#section_id').val();
    var class_id = $('#class_id').val();
    var date = $('#date').val();

    $("#students_list_container").html('');
    $("#loader-1").show();

    if (!date || (!class_id && (!section_id || section_id == '0'))) {
        $("#loader-1").hide();
        $("#students_list_container").html('<div class="alert alert-warning">Please select a class or section and date.</div>');
        return;
    }

    $.ajax({
        url: '/admin/students_absentees/check_and_load_attendance',
        type: "POST",
        dataType: 'json',
        data: {
            section_id: section_id,
            class_id: class_id,
            campus_id: campus_id,
            date: date
        },
        success: function(response) {
            if (response.has_records && response.html) {
                $("#students_list_container").html(response.html);
            } else {
                $("#students_list_container").html('<div class="alert alert-info">No attendance records found. Click "Load Students" to initialize.</div>');
            }
            $("#loader-1").hide();
        },
        error: function() {
            $("#students_list_container").html('<div class="alert alert-danger">Error loading attendance data.</div>');
            $("#loader-1").hide();
        }
    });
}

window.markpresent = function() {
    $("#loader-1").show();

    var campus_id = $('#campus_id').val();
    var section_id = $('#section_id').val();
    var class_id = $('#class_id').val();
    var date = $('#date').val();

    if (!campus_id || !date || (!class_id && !section_id)) {
        alert("Please select all required fields.");
        $("#loader-1").hide();
        return;
    }

    $.ajax({
        url: '/admin/students_absentees/mark_and_show_students',
        type: "POST",
        data: {
            section_id: section_id,
            class_id: class_id,
            campus_id: campus_id,
            date: date
        },
        success: function (res) {
            if (typeof res === 'object') {
                $("#students_list_container").html(res.html);
            } else {
                $("#students_list_container").html(res);
            }
            $("#loader-1").hide();
        },
        error: function () {
            $("#loader-1").hide();
            alert("Something went wrong while marking attendance.");
        }
    });
}

// ============================================
// TAB 2: Search by Name Functions
// ============================================

let searchTimeout;

$('#search_name').on('keyup', function() {
    clearTimeout(searchTimeout);
    let keyword = $(this).val();
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
                <i class="fas fa-search fa-3x mb-3"></i>
                <p>Enter a student name (minimum 3 characters) to search and mark attendance.</p>
                <p class="small">All siblings of the matching student will be displayed together.</p>
            </div>
        `);
    }
});

$('#btnSearch').on('click', function() {
    performSearch();
});

$('#search_date').on('change', function() {
    if ($('#search_name').val().length >= 3) {
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
        url: '/admin/students_absentees/search_students_by_name',
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
    
    let html = `<div class="mb-3"><div class="alert alert-success"><i class="fas fa-users"></i> Found ${families.length} family/families with ${totalStudents} student(s)</div></div>`;
    
    families.forEach((family, idx) => {
        html += `<div class="family-card">
            <div class="family-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-family text-primary"></i> <strong>${escapeHtml(family.family_name)}</strong> <span class="badge badge-secondary ml-2">${family.sibling_count} children</span></div>
                    <div>
                        <button class="btn btn-sm btn-outline-success bulk-family-mark" data-parent-id="${family.parent_id}" data-status="P"><i class="fas fa-check-circle"></i> All Present</button>
                        <button class="btn btn-sm btn-outline-danger bulk-family-mark" data-parent-id="${family.parent_id}" data-status="A"><i class="fas fa-times-circle"></i> All Absent</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0"><div class="list-group list-group-flush">`;
        
        family.siblings.forEach((student) => {
            html += `<div class="sibling-row" data-student-id="${student.student_id}">
                <div class="sibling-info">
                    <div class="sibling-photo"><i class="fas fa-user"></i></div>
                    <div><div class="font-weight-bold">${escapeHtml(student.name)}</div><div class="small text-muted">Reg: ${escapeHtml(student.reg_no || 'N/A')} | Class: ${escapeHtml(student.class_name)}</div></div>
                </div>
                <div class="sibling-status">
                    <div class="status-buttons btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-sm btn-outline-success ${student.status === 'P' ? 'active' : ''}" data-status="P"><input type="radio" name="status_${student.student_id}" value="P" ${student.status === 'P' ? 'checked' : ''}> P</label>
                        <label class="btn btn-sm btn-outline-danger ${student.status === 'A' ? 'active' : ''}" data-status="A"><input type="radio" name="status_${student.student_id}" value="A" ${student.status === 'A' ? 'checked' : ''}> A</label>
                        <label class="btn btn-sm btn-outline-warning ${student.status === 'L' ? 'active' : ''}" data-status="L"><input type="radio" name="status_${student.student_id}" value="L" ${student.status === 'L' ? 'checked' : ''}> L</label>
                        <label class="btn btn-sm btn-outline-info ${student.status === 'LC' ? 'active' : ''}" data-status="LC"><input type="radio" name="status_${student.student_id}" value="LC" ${student.status === 'LC' ? 'checked' : ''}> LC</label>
                    </div>
                    <span class="status-badge badge-${student.status_class}">${student.status_label}</span>
                </div>
            </div>`;
        });
        
        html += `</div></div></div>`;
    });
    
    $('#search_results_container').html(html);
    attachSearchEventHandlers(date);
}

function attachSearchEventHandlers(date) {
    $('.sibling-row .btn-group label').off('click').on('click', function(e) {
        e.preventDefault();
        let $label = $(this);
        let $row = $label.closest('.sibling-row');
        let studentId = $row.data('student-id');
        let newStatus = $label.data('status');
        
        $label.addClass('active').siblings().removeClass('active');
        $label.find('input[type="radio"]').prop('checked', true);
        
        let statusText = getStatusText(newStatus);
        let statusClass = getStatusClass(newStatus);
        $row.find('.status-badge').removeClass('badge-success badge-danger badge-warning badge-info').addClass(`badge-${statusClass}`).text(statusText);
        
        $.ajax({
            url: '/admin/students_absentees/update_attendance_status_single',
            type: "POST",
            dataType: 'json',
            data: { student_id: studentId, attendanceDate: date, status: newStatus },
            success: function(response) {
                if (response.success) toastr.success(`Attendance updated to ${response.status_label}`);
                else toastr.error(response.message || 'Error updating attendance');
            },
            error: function() { toastr.error('Server error. Please try again.'); }
        });
    });
    
    $('.bulk-family-mark').off('click').on('click', function() {
        let $btn = $(this);
        let parentId = $btn.data('parent-id');
        let newStatus = $btn.data('status');
        let $familyCard = $btn.closest('.family-card');
        let $rows = $familyCard.find('.sibling-row');
        
        $rows.each(function() {
            let $row = $(this);
            let studentId = $row.data('student-id');
            let $targetLabel = $row.find(`.btn-group label[data-status="${newStatus}"]`);
            $targetLabel.addClass('active').siblings().removeClass('active');
            $targetLabel.find('input').prop('checked', true);
            
            let statusText = getStatusText(newStatus);
            let statusClass = getStatusClass(newStatus);
            $row.find('.status-badge').removeClass('badge-success badge-danger badge-warning badge-info').addClass(`badge-${statusClass}`).text(statusText);
            
            $.ajax({
                url: '/admin/students_absentees/update_attendance_status_single',
                type: "POST",
                dataType: 'json',
                data: { student_id: studentId, attendanceDate: date, status: newStatus }
            });
        });
        toastr.success(`All students marked as ${getStatusText(newStatus)}`);
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

// ============================================
// TAB 3: Face Recognition Functions (Lazy Loaded)
// ============================================

let video = null;
let registerVideo = null;
let stream = null;
let registerStream = null;
let capturedFaceData = null;
let faceRecognitionInitialized = false;

// Only initialize face recognition when tab is clicked
$('#face-tab').on('shown.bs.tab', function (e) {
    if (!faceRecognitionInitialized) {
        initializeFaceRecognition();
    }
});

function initializeFaceRecognition() {
    faceRecognitionInitialized = true;
    loadRegisteredStudents();
    
    // Load face-api.js models when needed
    loadFaceApiModels();
}

// Load Face API Models
async function loadFaceApiModels() {
    const loadingDiv = $('#modelLoading');
    loadingDiv.show();
    
    try {
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights';
        
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        
        console.log('Face models loaded');
        $('#camera_status').text('Models loaded. Click "Start Camera" to begin.');
        
    } catch (error) {
        console.error('Error loading models:', error);
        $('#camera_status').text('Error loading face models. Please refresh.');
    } finally {
        loadingDiv.hide();
    }
}

// Start Camera (only when user clicks button)
async function startCamera() {
    const statusSpan = $('#camera_status');
    const videoElement = $('#video')[0];
    
    try {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        
        statusSpan.text('Requesting camera permission...');
        
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        videoElement.srcObject = stream;
        videoElement.style.display = 'block';
        await videoElement.play();
        
        statusSpan.text('✅ Camera ready. Click "Capture & Recognize"');
        statusSpan.css('color', '#28a745');
        
        $('#start_camera_btn').hide();
        $('#capture_btn').prop('disabled', false);
        $('#reset_camera_btn').show();
        
    } catch (err) {
        statusSpan.text('❌ Unable to access camera. Please allow camera permissions.');
        statusSpan.css('color', '#dc3545');
        console.error('Camera error:', err);
    }
}

// Capture and recognize face
async function captureAndRecognize() {
    if (!video || !stream) {
        showResult('Please start the camera first', 'error');
        return;
    }
    
    const canvas = $('#canvas')[0];
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    const imageData = canvas.toDataURL('image/jpeg', 0.8);
    showResult('Processing...', 'info');
    
    try {
        const response = await fetch('<?= base_url("admin/students_absentees/recognize_face") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                face_data: imageData,
                date: $('#attendance_date').val(),
                campus_id: <?= $campus_id ?>,
                session_id: <?= $session_id ?>
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showResult(`
                <div class="student-card text-center">
                    <i class="fas fa-user-check fa-3x text-success mb-2"></i>
                    <h4>${escapeHtml(result.student.name)}</h4>
                    <p><strong>Time:</strong> ${result.time}</p>
                    <p><span class="status-badge ${result.is_late ? 'status-late' : 'status-present'}">${result.status_label}</span></p>
                </div>
            `, 'success');
            setTimeout(() => $('#recognition_result').hide(), 3000);
        } else {
            showResult(`
                <div class="text-center">
                    <i class="fas fa-user-slash fa-3x text-danger mb-2"></i>
                    <p>${escapeHtml(result.message)}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="$('#registerModal').modal('show'); loadStudentsForRegistration();">
                        <i class="fas fa-user-plus"></i> Register New Face
                    </button>
                </div>
            `, 'error');
        }
    } catch (error) {
        showResult('Network error. Please try again.', 'error');
    }
}

function showResult(message, type) {
    const resultDiv = $('#recognition_result');
    resultDiv.removeClass('success error info').addClass(type).html(message).show();
    if (type === 'info') setTimeout(() => resultDiv.hide(), 5000);
}

async function loadRegisteredStudents() {
    try {
        const response = await fetch(`<?= base_url("admin/students_absentees/get_registered_faces") ?>?campus_id=<?= $campus_id ?>&session_id=<?= $session_id ?>`);
        const result = await response.json();
        const container = $('#registered_students_list');
        
        if (result.data && result.data.length > 0) {
            container.html('');
            result.data.forEach(student => {
                container.append(`
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="face-preview mx-auto mb-2" style="background:#e9ecef; width:100px; height:100px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                                </div>
                                <h6>${escapeHtml(student.first_name)} ${escapeHtml(student.last_name)}</h6>
                                <small>${escapeHtml(student.reg_no)}</small>
                            </div>
                        </div>
                    </div>
                `);
            });
        } else {
            container.html('<div class="col-12 text-center text-muted">No registered faces found.</div>');
        }
    } catch (error) { console.error(error); }
}

async function loadStudentsForRegistration() {
    const select = $('#register_student_id');
    select.html('<option value="">Loading students...</option>');
    try {
        const response = await fetch(`<?= base_url("admin/students_absentees/get_students_for_dropdown") ?>?campus_id=<?= $campus_id ?>&session_id=<?= $session_id ?>`);
        const result = await response.json();
        select.html('<option value="">-- Select Student --</option>');
        if (result.success && result.data) {
            result.data.forEach(student => {
                select.append(`<option value="${student.student_id}">${escapeHtml(student.first_name)} ${escapeHtml(student.last_name)} (${escapeHtml(student.reg_no)})</option>`);
            });
        }
    } catch (error) { select.html('<option value="">Error loading students</option>'); }
}

async function initRegisterCamera() {
    try {
        if (registerStream) registerStream.getTracks().forEach(track => track.stop());
        registerStream = await navigator.mediaDevices.getUserMedia({ video: true });
        registerVideo = $('#register_video')[0];
        if (registerVideo) registerVideo.srcObject = registerStream;
    } catch (err) { console.error(err); }
}

function resetCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    $('#video')[0].style.display = 'none';
    $('#start_camera_btn').show();
    $('#capture_btn').prop('disabled', true);
    $('#reset_camera_btn').hide();
    $('#camera_status').text('Click "Start Camera" to begin face recognition');
    $('#camera_status').css('color', 'white');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Face Recognition Event Listeners
$(document).ready(function() {
    $('#start_camera_btn').on('click', startCamera);
    $('#capture_btn').on('click', captureAndRecognize);
    $('#reset_camera_btn').on('click', resetCamera);
    
    $('#capture_register_btn').on('click', function() {
        if (!registerVideo) return;
        const canvas = document.createElement('canvas');
        canvas.width = registerVideo.videoWidth;
        canvas.height = registerVideo.videoHeight;
        canvas.getContext('2d').drawImage(registerVideo, 0, 0, canvas.width, canvas.height);
        capturedFaceData = canvas.toDataURL('image/jpeg', 0.8);
        $('#captured_image_preview').attr('src', capturedFaceData).show();
        $('#preview_placeholder').hide();
    });
    
    $('#save_registration_btn').on('click', async function() {
        const studentId = $('#register_student_id').val();
        if (!studentId) { alert('Please select a student'); return; }
        if (!capturedFaceData) { alert('Please capture a face photo first'); return; }
        try {
            const response = await fetch('<?= base_url("admin/students_absentees/register_face") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    student_id: studentId,
                    campus_id: <?= $campus_id ?>,
                    face_data: capturedFaceData
                })
            });
            const result = await response.json();
            if (result.success) {
                alert('Face registered successfully!');
                $('#registerModal').modal('hide');
                loadRegisteredStudents();
                capturedFaceData = null;
                $('#captured_image_preview').hide();
                $('#preview_placeholder').show();
            } else {
                alert('Registration failed: ' + result.message);
            }
        } catch (error) {
            alert('Registration failed. Please try again.');
        }
    });
    
    $('#registerModal').on('show.bs.modal', function() {
        loadStudentsForRegistration();
        setTimeout(initRegisterCamera, 500);
    });
    
    $('#registerModal').on('hidden.bs.modal', function() {
        if (registerStream) {
            registerStream.getTracks().forEach(track => track.stop());
            registerStream = null;
        }
        capturedFaceData = null;
        $('#captured_image_preview').hide();
        $('#preview_placeholder').show();
    });
});
</script>

<?= $this->endSection() ?>