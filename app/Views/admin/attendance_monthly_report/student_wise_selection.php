<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Student Session Attendance Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Session Report</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Select Criteria</h3>
                </div>
                <form action="<?= base_url('admin/attendance-monthly-report/student-wise-report') ?>" method="get">
                    <div class="card-body">
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Session *</label>
                                    <select name="session_id" class="form-control" required>
                                        <option value="">Select Session</option>
                                        <?php foreach ($sessions as $sess): ?>
                                            <option value="<?= $sess->id ?>"><?= $sess->session_name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Class/Section *</label>
                                    <select name="section_id" class="form-control" required>
                                        <option value="">Select Section</option>
                                        <?php foreach ($sectionsclassinfo as $section): ?>
                                            <option value="<?= $section['section_id'] ?>">
                                                <?= $section['class_name'] ?> - <?= $section['section_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Student (Optional)</label>
                                    <select name="student_id" class="form-control">
                                        <option value="">All Students</option>
                                        <!-- Will be populated via AJAX when section is selected -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Load students when section is selected
    $('select[name="section_id"]').change(function() {
        var section_id = $(this).val();
        if (section_id) {
            $.ajax({
                url: "<?= base_url('admin/attendance-monthly-report/get-students-by-section') ?>",
                type: "POST",
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    section_id: section_id
                },
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">All Students</option>';
                    if(response.students) {
                        $.each(response.students, function(index, student) {
                            options += '<option value="' + student.student_id + '">' + 
                                      student.first_name + ' ' + student.last_name + 
                                      ' (' + student.reg_no + ')' + 
                                      '</option>';
                        });
                    }
                    $('select[name="student_id"]').html(options);
                }
            });
        }
    });
});
</script>

<?= $this->endSection() ?>