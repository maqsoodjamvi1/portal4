<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Top Level Planning',
    'icon' => 'fas fa-project-diagram',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Top Level Planning', 'url' => base_url('admin/top_level_planning')],
        ['label' => 'Edit', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card sms-card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group me-2"></i>
                        Enter Top Level Planning
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?= form_open(base_url('admin/top_level_planning/save'), 'role="form" id="planning-form"') ?>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Choose how you want to enter planning:
                                <ul class="mt-2 mb-0">
                                    <li><strong>Class Wise</strong> - Enter planning for all subjects of a specific class</li>
                                    <li><strong>Subject Wise</strong> - Enter planning for a specific subject across all classes</li>
                                    <li><strong>Term Wise</strong> - Enter planning for all classes and subjects in a term</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="entry_type">Entry Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="entry_type" id="entry_type" required>
                                    <option value="">Select Entry Type</option>
                                    <option value="class_wise">Class Wise</option>
                                    <option value="subject_wise">Subject Wise</option>
                                    <option value="term_wise">Term Wise</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="term_session_id">Select Term <span class="text-danger">*</span></label>
                                <select class="form-control" name="term_session_id" id="term_session_id" required>
                                    <option value="">Select Term</option>
                                    <?php foreach ($terms as $term): ?>
                                        <option value="<?= $term->term_session_id ?>">
                                            <?= esc($term->term_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3" id="class_container" style="display: none;">
                            <div class="form-group">
                                <label for="class_id">Select Class <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="class_id" id="class_id">
                                    <option value="">Select Class</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?= $section['class_id'] ?>">
                                            <?= esc($section['class_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3" id="subject_container" style="display: none;">
                            <div class="form-group">
                                <label for="subject_id">Select Subject <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="subject_id" id="subject_id">
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?= $subject->sid ?>">
                                            <?= esc($subject->subject_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" id="load_planning_btn" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Load Planning
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="loader" class="text-center" style="display: none;">
                        <i class="fas fa-2x fa-spinner fa-spin"></i> Loading...
                    </div>
                    
                    <div id="planning_container" style="display: none;"></div>
                    
                    <div class="row" id="save_button" style="display: none;">
                        <div class="col-md-12">
                            <hr>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Save All Changes
                                </button>
                                <button type="reset" class="btn btn-secondary btn-lg">Reset</button>
                            </div>
                        </div>
                    </div>
                    
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Initialize select2
    $('.select2').select2({ width: '100%' });
    
    // Handle entry type change
    $('#entry_type').change(function() {
        var type = $(this).val();
        
        $('#class_container').hide();
        $('#subject_container').hide();
        $('#planning_container').hide();
        $('#save_button').hide();
        
        if (type == 'class_wise') {
            $('#class_container').show();
        } else if (type == 'subject_wise') {
            $('#subject_container').show();
        }
    });
    
    // Load planning form
    $('#load_planning_btn').click(function() {
        var entry_type = $('#entry_type').val();
        var term_session_id = $('#term_session_id').val();
        var class_id = $('#class_id').val();
        var subject_id = $('#subject_id').val();
        
        if (!entry_type) {
            toastr.warning('Please select entry type');
            return;
        }
        
        if (!term_session_id) {
            toastr.warning('Please select term');
            return;
        }
        
        if (entry_type == 'class_wise' && !class_id) {
            toastr.warning('Please select class');
            return;
        }
        
        if (entry_type == 'subject_wise' && !subject_id) {
            toastr.warning('Please select subject');
            return;
        }
        
        $('#loader').show();
        $('#planning_container').hide();
        $('#save_button').hide();
        
        $.ajax({
            url: '<?= base_url('admin/top_level_planning/getPlanningForm') ?>',
            type: 'POST',
            data: {
                entry_type: entry_type,
                term_session_id: term_session_id,
                class_id: class_id,
                subject_id: subject_id
            },
            dataType: 'json',
            success: function(res) {
                $('#planning_container').html(res.html).show();
                $('#save_button').show();
                $('#loader').hide();
                
                // Initialize Summernote for all editors
                $('.summernote').each(function() {
                    $(this).summernote({
                        height: 200,
                        toolbar: [
                            ['style', ['bold', 'italic', 'underline', 'clear']],
                            ['font', ['strikethrough', 'superscript', 'subscript']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ]
                    });
                });
            },
            error: function() {
                $('#loader').hide();
                toastr.error('Error loading planning form');
            }
        });
    });
    
    // Form submit handler
    $('#planning-form').submit(function(e) {
        e.preventDefault();
        
        // Sync Summernote content
        $('.summernote').each(function() {
            var content = $(this).summernote('code');
            $(this).val(content);
        });
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success(res.msg);
                    $('#planning_container').hide();
                    $('#save_button').hide();
                    $('#entry_type').val('');
                    $('#class_container').hide();
                    $('#subject_container').hide();
                    $('#class_id').val('').trigger('change');
                    $('#subject_id').val('').trigger('change');
                } else {
                    toastr.error(res.msg);
                }
            },
            error: function() {
                toastr.error('Error saving data');
            }
        });
    });
});
</script>

<style>
.summernote {
    width: 100%;
}
.card {
    margin-bottom: 20px;
}
.accordion .card-header {
    background-color: #f8f9fa;
}
.accordion .btn-link {
    color: #007bff;
    text-decoration: none;
    width: 100%;
    text-align: left;
}
.accordion .btn-link:hover {
    text-decoration: none;
}
</style>

<?= $this->endSection() ?>