<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$campus_id = $sessionData['campusid'];
$session_id = $sessionData['sessionid'];
$date_value = $sessionData['date'];
?>

<style>
.nav-tabs .nav-link {
    padding: 10px 20px;
    font-size: 16px;
}

.nav-tabs .nav-link i {
    margin-right: 8px;
}

.tab-pane {
    padding: 20px 0;
    min-height: 500px;
}

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

.loading-overlay {
    position: relative;
    min-height: 100px;
    display: none;
}

.loading-overlay .spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
    }
    .filter-group {
        width: 100%;
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
                    
                    <!-- TAB 1: Class/Section Attendance (Loaded via AJAX) -->
                    <div class="tab-pane fade show active" id="class-section-view" role="tabpanel">
                        <div id="class_section_container">
                            <div class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading attendance interface...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB 2: Search by Name (Loaded via AJAX) -->
                    <div class="tab-pane fade" id="search-view" role="tabpanel">
                        <div id="search_container">
                            <div class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading search interface...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB 3: Face Recognition (Loaded via AJAX when clicked) -->
                    <div class="tab-pane fade" id="face-view" role="tabpanel">
                        <div id="face_container">
                            <div class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading face recognition...</p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>
<script>
$(document).ready(function() {
    // Load Class/Section tab content immediately (NO face recognition)
    loadClassSectionTab();
    
    // Load Search tab content when clicked
    $('#search-tab').on('shown.bs.tab', function (e) {
        if ($('#search_container').html().indexOf('Loading search') !== -1) {
            loadSearchTab();
        }
    });
    
    // Load Face Recognition tab content ONLY when clicked - NO auto initialization
    $('#face-tab').on('shown.bs.tab', function (e) {
        if ($('#face_container').html().indexOf('Loading face') !== -1 || $('#face_container').html().indexOf('face-container') === -1) {
            loadFaceTab();
        }
    });
});

function loadClassSectionTab() {
    $.ajax({
        url: '<?= base_url("admin/students_absentees/add") ?>',
        type: 'GET',
        data: { date: '<?= $date_value ?>' },
        success: function(response) {
            $('#class_section_container').html(response);
        },
        error: function() {
            $('#class_section_container').html('<div class="alert alert-danger">Error loading attendance interface. Please refresh the page.</div>');
        }
    });
}

function loadSearchTab() {
    $.ajax({
        url: '<?= base_url("admin/students_absentees/search_attendance") ?>',
        type: 'GET',
        data: { date: '<?= $date_value ?>', campus_id: '<?= $campus_id ?>', session_id: '<?= $session_id ?>' },
        success: function(response) {
            $('#search_container').html(response);
        },
        error: function() {
            $('#search_container').html('<div class="alert alert-danger">Error loading search interface. Please refresh the page.</div>');
        }
    });
}

function loadFaceTab() {
    $('#face_container').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading face recognition...</p></div>');
    
    $.ajax({
        url: '<?= base_url("admin/students_absentees/face_recognition_content") ?>',
        type: 'GET',
        data: { date: '<?= $date_value ?>', campus_id: '<?= $campus_id ?>', session_id: '<?= $session_id ?>' },
        success: function(response) {
            $('#face_container').html(response);
        },
        error: function() {
            $('#face_container').html('<div class="alert alert-danger">Error loading face recognition. Please refresh the page.</div>');
        }
    });
}
</script>
<?= $this->endSection() ?>