<?php $uiNeedsSummernote = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Add Top Level Planning',
    'icon' => 'fas fa-plus',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Top Level Planning', 'url' => base_url('admin/top_level_planning')],
        ['label' => 'Add', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
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
                        
                        <div class="col-md-3" id="term_container">
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
                                    <?php if (isset($classes) && !empty($classes)): ?>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class->class_id ?>">
                                                <?= esc($class->class_short_name ?? $class->class_name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No classes available</option>
                                    <?php endif; ?>
                                </select>
                                <?php if ($isTeacher && empty($classes)): ?>
                                    <small class="text-muted">You are not assigned to any class.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-3" id="subject_container" style="display: none;">
                            <div class="form-group">
                                <label for="subject_id">Select Subject <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="subject_id" id="subject_id">
                                    <?php if ($isTeacher): ?>
                                        <option value="">Select class first</option>
                                    <?php else: ?>
                                        <option value="">Select Subject</option>
                                        <?php if (isset($subjects) && !empty($subjects)): ?>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?= $subject->sid ?? $subject->subject_id ?>">
                                                    <?= esc($subject->subject_name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" disabled>No subjects available</option>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if ($isTeacher && empty($subjects)): ?>
                                    <small class="text-muted">You are not assigned to any subject.</small>
                                <?php elseif ($isTeacher): ?>
                                    <small class="text-muted">Subjects are filtered by the selected class.</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($isTeacher && !empty($subjects)): ?>
                        <template id="teacher-all-subjects-options">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject->sid ?? $subject->subject_id ?>">
                                    <?= esc($subject->subject_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </template>
                        <?php endif; ?>
                        
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
                    
                   
                    
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// ============================================
// AUTO-SAVE FUNCTIONALITY
// ============================================

let autoSaveTimer = null;
let isSaving = false;
let lastSavedData = null;

// Function to get current form data from summernote editors
function getCurrentFormData() {
    let planningData = [];
    
    // Find all planning cards and get their content
    $('.planning-card').each(function() {
        let $card = $(this);
        
        // Find hidden inputs within this card
        let class_id = $card.find('input[name$="[class_id]"]').val();
        let subject_id = $card.find('input[name$="[subject_id]"]').val();
        let term_session_id = $card.find('input[name$="[term_session_id]"]').val();
        
        // Find the summernote editor within this card
        let $editor = $card.find('.summernote');
        let objective = '';
        
        if ($editor.length && $.fn.summernote && $editor.next('.note-editor').length) {
            objective = $editor.summernote('code');
        } else {
            objective = $card.find('textarea').val() || '';
        }
        
        // Debug log
        console.log('Found data in card:', {
            class_id: class_id,
            subject_id: subject_id,
            term_session_id: term_session_id,
            objective_length: objective.length
        });
        
        if (class_id && subject_id && term_session_id) {
            planningData.push({
                class_id: class_id,
                subject_id: subject_id,
                term_session_id: term_session_id,
                objective: objective
            });
        }
    });
    
    return planningData;
}

// Function to check if data has changed
function hasDataChanged(currentData) {
    if (!lastSavedData) return true;
    if (currentData.length !== lastSavedData.length) return true;
    
    for (let i = 0; i < currentData.length; i++) {
        if (currentData[i].objective !== lastSavedData[i].objective) {
            console.log('Change detected at index ' + i);
            return true;
        }
    }
    return false;
}

// Auto-save function
function autoSave() {
    if (isSaving) {
        console.log('Auto-save already in progress');
        return;
    }
    
    let currentData = getCurrentFormData();
    
    if (currentData.length === 0) {
        console.log('No data to save');
        return;
    }
    
    if (!hasDataChanged(currentData)) {
        console.log('No changes detected');
        return;
    }
    
    console.log('Saving ' + currentData.length + ' items...');
    
    // Show auto-save indicator
    if ($('#autoSaveIndicator').length === 0) {
        $('body').append(
            '<div id="autoSaveIndicator" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: none;">' +
                '<div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); padding: 10px 15px; border-start: 4px solid #17a2b8;">' +
                    '<span id="autoSaveStatus"><i class="fas fa-spinner fa-spin"></i> Saving...</span>' +
                '</div>' +
            '</div>'
        );
    }
    
    $('#autoSaveIndicator').fadeIn(200);
    $('#autoSaveStatus').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    isSaving = true;
    
    $.ajax({
        url: baseUrl + '/admin/top_level_planning/save',
        type: 'POST',
        data: {
            planning: currentData,
            auto_save: true
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                lastSavedData = JSON.parse(JSON.stringify(currentData));
                $('#autoSaveStatus').html('<i class="fas fa-check-circle" style="color: #28a745;"></i> Saved at ' + new Date().toLocaleTimeString());
                
                setTimeout(function() {
                    $('#autoSaveIndicator').fadeOut(500);
                }, 2000);
                
                if (res.msg && res.msg !== '') toastr.success(res.msg);
            } else {
                $('#autoSaveStatus').html('<i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> Save failed');
                setTimeout(function() {
                    $('#autoSaveIndicator').fadeOut(500);
                }, 2000);
                if (res.msg && res.msg !== '') toastr.error(res.msg);
            }
        },
        error: function(xhr, status, error) {
            console.error('Auto-save error:', error);
            $('#autoSaveStatus').html('<i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> Error saving');
            setTimeout(function() {
                $('#autoSaveIndicator').fadeOut(500);
            }, 2000);
        },
        complete: function() {
            isSaving = false;
        }
    });
}

// Debounced auto-save
let debounceTimer;
function triggerAutoSave() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function() {
        autoSave();
    }, 2000);
}

// Define baseUrl
var baseUrl = '<?= base_url() ?>';
var isTeacher = <?= !empty($isTeacher) ? 'true' : 'false' ?>;

function resetTeacherSubjectDropdown(mode) {
    if (!isTeacher) {
        return;
    }

    if (mode === 'subject_wise') {
        var template = document.getElementById('teacher-all-subjects-options');
        if (template) {
            $('#subject_id').html(template.innerHTML).trigger('change.select2');
        }
        return;
    }

    $('#subject_id').html('<option value="">Select class first</option>').trigger('change.select2');
}

function loadSubjectsForClass(classId) {
    if (!classId) {
        resetTeacherSubjectDropdown('term_wise');
        return;
    }

    $.ajax({
        url: baseUrl + '/admin/top_level_planning/getSubjectsByClass',
        type: 'POST',
        data: { class_id: classId },
        dataType: 'json',
        success: function(res) {
            $('#subject_id').html(res.html).trigger('change.select2');
        },
        error: function() {
            toastr.error('Could not load subjects for the selected class');
        }
    });
}

$(document).ready(function() {
    // Initialize select2
    $('.select2').select2({ width: '100%' });
    
    // Entry type change handler
    $("#entry_type").change(function() {
        var type = $(this).val();
        
        $('#term_container').show();
        $('#class_container').hide();
        $('#subject_container').hide();
        $('#planning_container').hide();
        $('#save_button').hide();
        
        if (type == 'class_wise') {
            $('#term_container').show();
            $('#class_container').show();
            $('#subject_container').hide();
        } else if (type == 'subject_wise') {
            $('#term_container').show();
            $('#class_container').hide();
            $('#subject_container').show();
            resetTeacherSubjectDropdown('subject_wise');
        } else if (type == 'term_wise') {
            $('#term_container').hide();
            $('#class_container').show();
            $('#subject_container').show();
            resetTeacherSubjectDropdown('term_wise');
            if ($('#class_id').val()) {
                loadSubjectsForClass($('#class_id').val());
            }
        }
    });

    $('#class_id').on('change', function() {
        if (!$('#subject_container').is(':visible')) {
            return;
        }

        if (isTeacher || $('#entry_type').val() === 'term_wise') {
            loadSubjectsForClass($(this).val());
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
        
        if (entry_type == 'class_wise' && (!term_session_id || !class_id)) {
            toastr.warning('Please select both term and class');
            return;
        }
        
        if (entry_type == 'subject_wise' && (!term_session_id || !subject_id)) {
            toastr.warning('Please select both term and subject');
            return;
        }
        
        if (entry_type == 'term_wise' && (!class_id || !subject_id)) {
            toastr.warning('Please select both class and subject');
            return;
        }
        
        $('#loader').show();
        $('#planning_container').hide();
        $('#save_button').hide();
        
        $.ajax({
            url: baseUrl + '/admin/top_level_planning/getPlanningForm',
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
    
    // Initialize Summernote for all editors (skip if already initialized)
    if ($.fn.summernote) {
        $('.summernote').each(function() {
            if ($(this).next('.note-editor').length) {
                return;
            }
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
            
            $(this).on('summernote.change', function() {
                triggerAutoSave();
            });
        });
    }
    
    // Auto-save when typing in plain textareas (or if Summernote is unavailable)
    $('#planning_container').find('textarea').on('input', function() {
        triggerAutoSave();
    });
    
    // Store initial data for change detection after a short delay
    setTimeout(function() {
        lastSavedData = getCurrentFormData();
        console.log('Initial data stored, ready for auto-save. Found ' + lastSavedData.length + ' items.');
        if (lastSavedData.length > 0) {
            console.log('First item sample:', lastSavedData[0]);
        }
    }, 1000);
},
            error: function(xhr, status, error) {
                $('#loader').hide();
                console.log('Error:', error);
                toastr.error('Error loading planning form');
            }
        });
    });
    
    // Form submit handler
    $('#planning-form').submit(function(e) {
        e.preventDefault();
        
        // Sync Summernote content back to textareas before submit
        if ($.fn.summernote) {
            $('.summernote').each(function() {
                if ($(this).next('.note-editor').length) {
                    $(this).val($(this).summernote('code'));
                }
            });
        }
        
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
                    $('#term_session_id').val('');
                    lastSavedData = null;
                } else {
                    toastr.error(res.msg);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', error);
                toastr.error('Error saving data');
            }
        });
    });
});
</script>

<style>
.term-wise-grid {
    width: 100%;
}

.term-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px 20px -10px;
}

.term-card {
    flex: 1;
    min-width: calc(33.333% - 20px);
    margin: 0 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    background: #fff;
}

.term-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 15px;
}

.term-card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.term-date {
    font-size: 11px;
    opacity: 0.9;
    margin-top: 5px;
}

.term-card-body {
    padding: 15px;
    background: #fff;
}

.term-card-body .form-group {
    margin-bottom: 0;
}

.term-card-body label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 13px;
    display: block;
}

.term-card-body textarea {
    font-size: 13px;
}

/* Responsive: On tablets, show 2 per row */
@media (max-width: 992px) {
    .term-card {
        min-width: calc(50% - 20px);
        margin-bottom: 20px;
    }
    .term-row {
        margin-bottom: 0;
    }
}

/* Responsive: On mobile, show 1 per row */
@media (max-width: 768px) {
    .term-card {
        min-width: calc(100% - 20px);
        margin-bottom: 20px;
    }
}

.alert-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    margin-bottom: 20px;
}

.alert-info i {
    margin-right: 10px;
}

.summernote {
    width: 100%;
}

.card {
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .term-card-header h5 {
        font-size: 16px;
    }
}

/* Auto-save indicator */
#autoSaveIndicator {
    font-size: 13px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    z-index: 9999;
}

#autoSaveIndicator i {
    margin-right: 8px;
}

/* Planning Table Styles */
.planning-table {
    width: 100%;
    border-collapse: collapse;
}

.planning-table th,
.planning-table td {
    padding: 12px;
    border: 1px solid #ddd;
    vertical-align: top;
}

.class-name-cell,
.subject-name-cell,
.term-name-cell {
    background-color: #f8f9fa;
    font-weight: 500;
}

/* Mobile Responsive Planning Cards */
.planning-cards {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.planning-card {
    background: #fff;
    border: 1px solid #e0e7ef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: box-shadow 0.2s ease;
}

.planning-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.planning-card-header {
    background: #2c3e66;
    color: white;
    padding: 15px 20px;
    border-bottom: 1px solid #e0e7ef;
}

.planning-card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.planning-card-header h5 i {
    font-size: 14px;
}

.planning-card-header .term-date {
    display: block;
    font-size: 12px;
    opacity: 0.8;
    margin-top: 5px;
    font-weight: normal;
}

.planning-card-body {
    padding: 20px;
}

.planning-card-body .form-group {
    margin-bottom: 0;
}

.planning-card-body label {
    font-weight: 600;
    color: #1e4663;
    margin-bottom: 10px;
    display: block;
    font-size: 14px;
}

.planning-card-body textarea {
    width: 100%;
    border: 1px solid #cfdfed;
    border-radius: 8px;
    padding: 10px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.planning-card-body textarea:focus {
    border-color: #2c7da0;
    outline: none;
    box-shadow: 0 0 0 2px rgba(44,125,160,0.1);
}

/* Summernote custom styling */
.note-editor.note-frame {
    border-radius: 8px;
    border-color: #cfdfed;
}

.note-editor.note-frame .note-editing-area .note-editable {
    min-height: 150px;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .planning-card-header {
        padding: 12px 15px;
    }
    
    .planning-card-header h5 {
        font-size: 14px;
    }
    
    .planning-card-body {
        padding: 15px;
    }
    
    .planning-card-body label {
        font-size: 13px;
    }
    
    .note-editor.note-frame .note-editing-area .note-editable {
        min-height: 120px;
        font-size: 13px;
    }
    
    .alert-info {
        font-size: 13px;
        padding: 10px;
    }
}

/* Tablet adjustments */
@media (min-width: 769px) and (max-width: 1024px) {
    .planning-card-header {
        padding: 14px 18px;
    }
    
    .planning-card-header h5 {
        font-size: 15px;
    }
}
</style>

<?= $this->endSection() ?>