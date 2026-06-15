<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Student Session Attendance Report',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Session Report', 'active' => true],
    ],
]) ?>


<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Select Student and Session</h3>
                </div>
                <form id="sessionReportForm" method="get" action="<?= base_url('admin/attendance-monthly-report/student-wise-report') ?>">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
    <div class="form-group">
        <label>Academic Session *</label>
        <select name="session_id" class="form-control select2" required style="width: 100%;">
            <option value="">Select Session</option>
            <?php if (!empty($sessions)): ?>
                <!-- Check if $sessions is an object or array -->
                <?php if (is_array($sessions)): ?>
                    <?php foreach ($sessions as $session): ?>
                        <option value="<?= $session->session_id ?>" <?= ($session_id == $session->session_id) ? 'selected' : '' ?>>
                            <?= esc($session->session_name) ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- If it's a single object -->
                    <option value="<?= $sessions->session_id ?>" selected>
                        <?= esc($sessions->session_name) ?>
                    </option>
                <?php endif; ?>
            <?php endif; ?>
        </select>
    </div>
</div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filter by Class/Section (Optional)</label>
                                    <select name="class_section_filter" id="class_section_filter" class="form-control select2" style="width: 100%;">
                                        <option value="">All Classes/Sections</option>
                                        <?php if (!empty($sectionsclassinfo)): ?>
                                            <?php foreach ($sectionsclassinfo as $section): ?>
                                                <option value="<?= $section['section_id'] ?>">
                                                    <?= esc($section['class_name'] ?? '') ?> - <?= esc($section['section_name'] ?? '') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Select Student *</label>
                                    <select name="student_id" id="student_select" class="form-control select2" required style="width: 100%;">
                                        <option value="">Search and select a student...</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Start typing student name, registration number, or father's name
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Report Options</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="report_type" id="monthly_view" value="monthly" checked>
                                        <label class="form-check-label" for="monthly_view">Monthly Grid View</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="report_type" id="detailed_view" value="detailed">
                                        <label class="form-check-label" for="detailed_view">Detailed Summary</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> Generate Report
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Student Info Preview Card (Optional) -->
<div class="row" id="studentPreviewCard" style="display: none;">
    <div class="col-md-12">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Student Information</h3>
            </div>
            <div class="card-body" id="studentPreviewContent">
                <!-- Student info will be loaded here -->
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for all select elements
    $('.select2').select2({
        theme: 'bootstrap-5'
    });
    
    // Initialize student select with AJAX search
    $('#student_select').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: "<?= base_url('admin/attendance-monthly-report/get-student-info') ?>",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    term: params.term,
                    flag: $('#class_section_filter').val() || ''
                };
            },
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        return {
                            id: item.id,
                            text: item.text
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        placeholder: 'Search student by name, reg number, or father name',
        allowClear: true
    });
    
    // Optional: Load student details when selected
    $('#student_select').on('select2:select', function(e) {
        var studentId = e.params.data.id;
        loadStudentPreview(studentId);
    });
    
    // Filter students when class/section filter changes
    $('#class_section_filter').change(function() {
        // Clear current selection and trigger new search
        $('#student_select').val(null).trigger('change');
        $('#student_select').select2('open');
    });
    
    // Function to load student preview
    function loadStudentPreview(studentId) {
        $.ajax({
            url: "<?= base_url('admin/attendance-monthly-report/get-student-details') ?>", // You'll need to create this method
            type: "POST",
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                student_id: studentId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var html = `
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <img src="${response.photo_url || '<?= base_url("assets/img/default-avatar.png") ?>'}" 
                                     class="img-circle" width="80" height="80" alt="Student Photo">
                            </div>
                            <div class="col-md-10">
                                <h4>${response.full_name}</h4>
                                <p><strong>Registration No:</strong> ${response.reg_no || 'N/A'}</p>
                                <p><strong>Father Name:</strong> ${response.father_name || 'N/A'}</p>
                                <p><strong>Class/Section:</strong> ${response.section_name || 'N/A'}</p>
                            </div>
                        </div>
                    `;
                    $('#studentPreviewContent').html(html);
                    $('#studentPreviewCard').show();
                }
            }
        });
    }
    
    // Form validation
    $('#sessionReportForm').submit(function(e) {
        if (!$('#student_select').val()) {
            e.preventDefault();
            alert('Please select a student');
            $('#student_select').select2('open');
            return false;
        }
    });
});
</script>

<?= $this->endSection() ?>