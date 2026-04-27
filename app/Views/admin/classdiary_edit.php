<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
  .font-weight-600 { font-weight: 600; }
  .cd-day-card {
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
  }
  .cd-day-card .card-header {
    background: #f5f5f5;
    padding: 12px 15px;
    font-weight: 600;
  }
  .cd-day-card .card-body {
    padding: 15px;
  }
  .form-group-compact {
    margin-bottom: 12px;
  }
  .form-group-compact label {
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 13px;
  }
  .plain-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    resize: vertical;
  }
  .task-caption-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    margin-top: 8px;
  }
  .toggle-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 15px;
  }
  .toggle-item {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .toggle-item label {
    margin: 0;
    font-weight: normal;
  }
  .quiz-select {
    margin-top: 10px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
    max-width: 300px;
  }
  .mode-toggle .btn {
    padding: 4px 12px;
    font-size: 12px;
  }
  .subject-badge {
    background: #4f46e5;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    display: inline-block;
    margin-bottom: 10px;
  }
  .btn-sm-icon {
    padding: 4px 8px;
    font-size: 12px;
  }
  
  /* Activity Styles */
  .activity-item {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 12px;
    margin-bottom: 12px;
    border-radius: 6px;
    transition: all 0.2s;
  }
  .activity-item:hover {
    background: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  .activity-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 8px;
  }
  .activity-name {
    font-weight: 600;
    color: #007bff;
    font-size: 14px;
  }
  .activity-badge {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 12px;
    background: #6c757d;
    color: white;
  }
  .activity-badge.discussion { background: #28a745; }
  .activity-badge.group-work { background: #17a2b8; }
  .activity-badge.presentation { background: #ffc107; color: #333; }
  .activity-badge.lab { background: #fd7e14; }
  .activity-badge.lecture { background: #6f42c1; }
  .activity-badge.other { background: #6c757d; }
  
  .activity-description {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
  }
  .activity-tasks {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 8px;
  }
  .task-icon {
    font-size: 12px;
    color: #28a745;
    margin-right: 4px;
  }
  .task-label {
    font-size: 11px;
    color: #666;
  }
  .btn-add-activity {
    margin-top: 10px;
    width: 100%;
    border: 2px dashed #007bff;
    background: transparent;
    color: #007bff;
    transition: all 0.2s;
  }
  .btn-add-activity:hover {
    background: #007bff;
    color: white;
  }
  
  /* Modal Styles */
  .modal-activity .modal-lg {
    max-width: 800px;
  }
  .form-section-title {
    font-size: 14px;
    font-weight: 600;
    margin: 15px 0 10px 0;
    padding-bottom: 5px;
    border-bottom: 2px solid #007bff;
  }
  .task-type-toggle {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
  }
  .task-type-toggle .btn {
    flex: 1;
  }
  .task-field {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 15px;
    display: none;
  }
  .task-field.active {
    display: block;
  }
</style>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Class Diary</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Class Diary</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/classdiary-view') ?>">View Diary</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="<?= base_url('admin/classdiary-add') ?>">Add / Edit Diary</a>
            </li>
          </ul>
        </div>

        <div class="card-body">
          <div class="tab-content">
            <?php echo form_open(base_url('admin/classdiary/save'), 'role="form" id="classdairy-edit-form"'); ?>
            <?php echo form_hidden('id', $id ?? ''); ?>
            
            <!-- Selection Row -->
            <div class="row">
              <div class="col-lg-3">
                <div class="form-group">
                  <label>Term Session</label>
                  <select name="term_id" id="term_id" class="form-control">
                    <?php foreach ($terms_session_info as $ts): ?>
                      <option value="<?= $ts->term_session_id ?>" <?= ($ts->term_session_id == ($default_term_session_id ?? 0) ? 'selected' : '') ?>>
                        <?= esc($ts->term_name) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="form-group">
                  <label>Week</label>
                  <select name="term_weeks" id="term_weeks" class="form-control">
                    <?php foreach ($term_weeks_info as $w): ?>
                      <option value="<?= $w->term_weeks_id ?>" <?= ($w->term_weeks_id == ($default_term_weeks_id ?? 0) ? 'selected' : '') ?>>
                        <?= esc($w->week_name) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="form-group">
                  <label>Section</label>
                  <select class="form-control select2" name="section_id" id="section_id">
                    <option value="0">Select Section</option>
                    <?php if (isset($sectionsclassinfo)) {
                        foreach ($sectionsclassinfo as $secionvalue) { ?>
                          <option value="<?= esc($secionvalue['cls_sec_id']) ?>">
                            <?= esc($secionvalue['sectionclassname']) ?>
                          </option>
                      <?php }
                    } ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="form-group">
                  <label>Subject</label>
                  <select class="form-control" name="sec_sub_id" id="sec_sub_id">
                    <option value="">Select Subject</option>
                  </select>
                </div>
              </div>
            </div>

            <div id="loader-1" class="overlay text-center" style="display: none;">
              <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>

            <div id="termweekdates"></div>

            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<div class="modal fade modal-activity" id="activityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="activityModalLabel">
          <i class="fas fa-calendar-alt"></i> Add Classroom Activity
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="activity_current_date" value="">
        <input type="hidden" id="activity_temp_id" value="">
        
        <div class="form-group">
          <label>Activity Name <span class="text-danger">*</span></label>
          <input type="text" id="activity_name" class="form-control form-control-lg" placeholder="e.g., Group Discussion on Photosynthesis">
        </div>
        
        <div class="row">
          <div class="col-12 col-sm-6">
            <div class="form-group">
              <label>Activity Type</label>
              <select id="activity_type" class="form-control">
                <option value="discussion">📝 Group Discussion</option>
                <option value="group-work">👥 Group Work</option>
                <option value="presentation">🎤 Student Presentation</option>
                <option value="lab">🔬 Lab Work</option>
                <option value="lecture">📖 Lecture</option>
                <option value="other">📌 Other</option>
              </select>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="form-group">
              <label>Duration (minutes)</label>
              <input type="number" id="activity_duration" class="form-control" value="20" min="5" max="120">
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label>Activity Description</label>
          <textarea id="activity_description" class="form-control" rows="4" 
                    placeholder="Describe what students will do in this activity..."></textarea>
        </div>
        
        <div class="form-group">
          <label>Learning Objectives</label>
          <textarea id="activity_objectives" class="form-control" rows="3" 
                    placeholder="What will students learn from this activity?"></textarea>
        </div>
        
        <div class="form-group">
          <label>Materials Required</label>
          <input type="text" id="activity_materials" class="form-control" 
                 placeholder="List materials needed (comma separated)">
        </div>
        
        <!-- Video Task Section -->
        <div class="card mt-3">
          <div class="card-header bg-light p-2">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-video text-primary"></i> 
                <strong>Video Task</strong>
                <small class="text-muted">(Optional)</small>
              </div>
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="enable_video_task">
                <label class="custom-control-label" for="enable_video_task">Enable</label>
              </div>
            </div>
          </div>
          <div id="video_task_section" class="card-body" style="display: none;">
            <div class="form-group">
              <label>Video URL</label>
              <input type="url" id="activity_video_url" class="form-control" 
                     placeholder="https://www.youtube.com/watch?v=...">
            </div>
            <div class="form-group mb-0">
              <label>Caption / Instructions</label>
              <input type="text" id="activity_video_caption" class="form-control" 
                     placeholder="e.g., Watch this video before the activity">
            </div>
          </div>
        </div>
        
        <!-- Audio Task Section -->
        <div class="card mt-3">
          <div class="card-header bg-light p-2">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-headphones text-success"></i> 
                <strong>Audio Task</strong>
                <small class="text-muted">(Optional)</small>
              </div>
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="enable_audio_task">
                <label class="custom-control-label" for="enable_audio_task">Enable</label>
              </div>
            </div>
          </div>
          <div id="audio_task_section" class="card-body" style="display: none;">
            <div class="form-group">
              <label>Audio URL</label>
              <input type="url" id="activity_audio_url" class="form-control" 
                     placeholder="https://example.com/audio.mp3">
            </div>
            <div class="form-group mb-0">
              <label>Caption / Instructions</label>
              <input type="text" id="activity_audio_caption" class="form-control" 
                     placeholder="e.g., Listen to this explanation">
            </div>
          </div>
        </div>
        
        <!-- Group Activity Settings -->
        <div class="card mt-3">
          <div class="card-header bg-light p-2">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <i class="fas fa-users text-warning"></i> 
                <strong>Group Settings</strong>
                <small class="text-muted">(Optional)</small>
              </div>
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="enable_group_activity">
                <label class="custom-control-label" for="enable_group_activity">Group Activity</label>
              </div>
            </div>
          </div>
          <div id="group_activity_section" class="card-body" style="display: none;">
            <div class="form-group">
              <label>Group Size</label>
              <input type="number" id="activity_group_size" class="form-control" value="4" min="2" max="10">
            </div>
            <div class="form-group mb-0">
              <label>Group Instructions</label>
              <textarea id="activity_group_instructions" class="form-control" rows="3" 
                        placeholder="Instructions for group work..."></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex flex-column flex-sm-row gap-2">
        <button type="button" class="btn btn-secondary order-sm-1" data-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn btn-primary order-sm-0" onclick="saveActivityToDay()">
          <i class="fas fa-save"></i> Add Activity
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* Mobile Responsive Modal Styles */
@media (max-width: 767.98px) {
  .modal-activity .modal-dialog {
    margin: 0.5rem;
    max-width: calc(100% - 1rem);
  }
  
  .modal-activity .modal-content {
    border-radius: 12px;
    max-height: 95vh;
    overflow-y: auto;
  }
  
  .modal-activity .modal-body {
    padding: 1rem;
  }
  
  .modal-activity .form-control,
  .modal-activity .form-control-lg {
    font-size: 16px; /* Prevents zoom on mobile */
  }
  
  .modal-activity .card-header {
    padding: 0.75rem !important;
  }
  
  .modal-activity .card-header .d-flex {
    flex-wrap: wrap;
    gap: 8px;
  }
  
  .modal-activity .custom-switch {
    padding-left: 2rem;
  }
  
  .modal-footer {
    padding: 0.75rem;
    gap: 8px;
  }
  
  .modal-footer .btn {
    width: 100%;
    padding: 10px;
    font-size: 14px;
  }
  
  /* Improve touch targets */
  .modal-activity button,
  .modal-activity .custom-control-label,
  .modal-activity input,
  .modal-activity select,
  .modal-activity textarea {
    touch-action: manipulation;
  }
  
  /* Increase tap target size */
  .custom-control-label::before,
  .custom-control-label::after {
    top: 0.15rem;
    width: 1.5rem;
    height: 1.5rem;
  }
  
  .custom-switch .custom-control-label::after {
    width: calc(1.5rem - 4px);
    height: calc(1.5rem - 4px);
  }
}

/* Tablet and small desktop adjustments */
@media (min-width: 768px) and (max-width: 991.98px) {
  .modal-activity .modal-dialog {
    max-width: 90%;
    margin: 1.75rem auto;
  }
}

/* Prevent body scroll when modal is open on mobile */
.modal-open {
  overflow: hidden;
  position: fixed;
  width: 100%;
}

/* Better spacing for form groups on mobile */
@media (max-width: 576px) {
  .form-group {
    margin-bottom: 1rem;
  }
  
  .form-group label {
    font-size: 13px;
    margin-bottom: 0.25rem;
  }
  
  textarea.form-control {
    font-size: 14px;
  }
  
  /* Stack buttons on mobile */
  .modal-footer {
    flex-direction: column-reverse;
  }
  
  .modal-footer .btn {
    margin: 4px 0;
  }
}

/* Smooth transitions */
.modal-activity .card {
  transition: all 0.3s ease;
  border: 1px solid #e0e0e0;
}

.modal-activity .card-header {
  cursor: pointer;
  transition: background 0.2s ease;
}

.modal-activity .card-header:hover {
  background: #e9ecef !important;
}

/* Touch-friendly scrollbar */
.modal-activity .modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-activity .modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.modal-activity .modal-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

/* Better focus states for mobile */
.modal-activity .form-control:focus,
.modal-activity .btn:focus {
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
}

/* Loading state for buttons */
.btn-primary:active {
  transform: scale(0.98);
}
</style>

<script>
// Make card headers clickable to toggle sections (optional enhancement)
$(document).ready(function() {
  // Click on card header to toggle the section (for better mobile experience)
  $('#video_task_section').closest('.card').find('.card-header').click(function(e) {
    if ($(e.target).is('input, label, .custom-control, .custom-switch')) return;
    $('#enable_video_task').trigger('click');
  });
  
  $('#audio_task_section').closest('.card').find('.card-header').click(function(e) {
    if ($(e.target).is('input, label, .custom-control, .custom-switch')) return;
    $('#enable_audio_task').trigger('click');
  });
  
  $('#group_activity_section').closest('.card').find('.card-header').click(function(e) {
    if ($(e.target).is('input, label, .custom-control, .custom-switch')) return;
    $('#enable_group_activity').trigger('click');
  });
});
</script>
<script>
// ============================================
// CONFIGURATION
// ============================================
const URL_SELECT_SUBJECTS  = "<?= base_url('admin/classdiary/select-section-subject-by-section') ?>";
const URL_GET_CLASSDIARY   = "<?= base_url('admin/classdiary/get-classdiary') ?>";
const URL_SAVE             = "<?= base_url('admin/classdiary/save') ?>";
const URL_GET_QUIZZES      = "<?= base_url('admin/classdiary/get-quizzes-by-subject') ?>";

const CSRF_NAME = "<?= csrf_token() ?>";
let   CSRF_HASH = "<?= csrf_hash() ?>";

let saveTimeout = null;
let isSaving = false;

// Activity management variables
let currentEditingDate = null;
let currentEditingActivityId = null;

function addCsrf(payload){
    if (CSRF_NAME && CSRF_HASH) payload[CSRF_NAME] = CSRF_HASH;
    return payload;
}

function refreshCsrfFromXHR(xhr){
    const t = xhr && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
    if (t) { CSRF_HASH = t; }
}

function showOverlay(on){ $('#loader-1').toggle(!!on); }

// ============================================
// LOAD SUBJECTS BY SECTION
// ============================================
function loadSubjectsForSection(sectionId) {
    const $subject = $('#sec_sub_id');
    if (!sectionId || sectionId == 0) {
        $subject.html('<option value="">Select Subject</option>');
        return;
    }

    const payload = addCsrf({ cls_sec_id: sectionId });
    $subject.html('<option value="">Loading...</option>');

    $.ajax({
        url: URL_SELECT_SUBJECTS,
        type: 'POST',
        data: payload,
        success: function(html, status, xhr) {
            refreshCsrfFromXHR(xhr);
            $subject.html(html || '<option value="">No subjects found</option>');
        },
        error: function(xhr) {
            $subject.html('<option value="">Error loading subjects</option>');
            alert('Failed to load subjects.');
        }
    });
}

// ============================================
// SAVE ALL DIARY DATA
// ============================================
function saveAllDiaryData() {
    if (isSaving) return;
    
    const termWeeksId = $('#term_weeks').val();
    const secSubId = $('#sec_sub_id').val();
    const sectionId = $('#section_id').val();

    if (!termWeeksId || !secSubId || !sectionId) {
        return;
    }

    const diaryData = [];
    
    $('#termweekdates .cd-day-card').each(function() {
        const $card = $(this);
        const dateVal = $card.data('date');
        const didVal = $card.find('.diary-id').val() || 0;
        
        // Text content
        const homework = $card.find('.homework-textarea').val() || '';
        const classwork = $card.find('.classwork-textarea').val() || '';
        
        // Toggle states
        const isAudio = $card.find('.toggle-audio').is(':checked') ? 1 : 0;
        const isVideo = $card.find('.toggle-video').is(':checked') ? 1 : 0;
        const isPicture = $card.find('.toggle-picture').is(':checked') ? 1 : 0;
        const isQuiz = $card.find('.toggle-quiz').is(':checked') ? 1 : 0;
        const isBook = $card.find('.toggle-book').is(':checked') ? 1 : 0;
        const isNotebook = $card.find('.toggle-notebook').is(':checked') ? 1 : 0;
        
        // Captions
        const audioCaption = $card.find('.audio-caption-input').val() || '';
        const videoCaption = $card.find('.video-caption-input').val() || '';
        const pictureCaption = $card.find('.picture-caption-input').val() || '';
        
        // Quiz selection
        const quizId = $card.find('.quiz-select-field').val() || null;
        
        // Activities
        const activities = $card.data('activities') || [];
        const hasActivities = activities.length > 0 ? 1 : 0;
        
        diaryData.push({
            did: didVal,
            date: dateVal,
            detail: homework,
            other_detail: classwork,
            is_audio: isAudio,
            is_video: isVideo,
            is_picture: isPicture,
            is_quiz: isQuiz,
            is_book: isBook,
            is_notebook: isNotebook,
            audio_caption: audioCaption,
            video_caption: videoCaption,
            picture_caption: pictureCaption,
            quiz_id: quizId,
            has_activities: hasActivities,
            activities: activities
        });
    });

    if (diaryData.length === 0) return;

    const payload = addCsrf({
        term_weeks: termWeeksId,
        sec_sub_id: secSubId,
        section_id: sectionId,
        diary_data: JSON.stringify(diaryData)
    });

    isSaving = true;
    
    $('.autosave-pill').show().removeClass('saved error').addClass('saving');
    $('.autosave-text').text('Saving...');
    $('.autosave-dot').removeClass('autosave-dot').addClass('autosave-spin');

    $.ajax({
        url: URL_SAVE,
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: function(res, status, xhr) {
            refreshCsrfFromXHR(xhr);
            if (res && res.success) {
                if (res.saved_ids) {
                    for (const [index, id] of res.saved_ids.entries()) {
                        if (id && id > 0) {
                            $('#termweekdates .cd-day-card').eq(index).find('.diary-id').val(id);
                        }
                    }
                }
                
                $('.autosave-pill').removeClass('saving').addClass('saved');
                $('.autosave-text').text('Saved');
                $('.autosave-spin').removeClass('autosave-spin').addClass('autosave-dot');
                
                setTimeout(() => {
                    $('.autosave-pill').fadeOut(500);
                }, 1500);
            } else {
                $('.autosave-pill').removeClass('saving').addClass('error');
                $('.autosave-text').text('Error');
                setTimeout(() => {
                    $('.autosave-pill').fadeOut(500);
                }, 2000);
            }
        },
        error: function(xhr) {
            $('.autosave-pill').removeClass('saving').addClass('error');
            $('.autosave-text').text('Error');
            setTimeout(() => {
                $('.autosave-pill').fadeOut(500);
            }, 2000);
        },
        complete: function() {
            isSaving = false;
        }
    });
}

function triggerAutoSave() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        saveAllDiaryData();
    }, 800);
}

// Make functions globally available
window.saveAllDiaryData = saveAllDiaryData;
window.triggerAutoSave = triggerAutoSave;

// ============================================
// LOAD CLASS DIARY
// ============================================
function getclassdiary() {
    const termWeeksId = $('#term_weeks').val();
    const secSubId    = $('#sec_sub_id').val();
    const sectionId   = $('#section_id').val();

    if (!termWeeksId) { alert('Please select a Term Week.'); return; }
    if (!secSubId)    { alert('Please select a Subject.'); return; }
    if (!sectionId || sectionId == 0) { alert('Please select a Section.'); return; }

    const payload = addCsrf({ 
        term_weeks: termWeeksId, 
        sec_sub_id: secSubId,
        section_id: sectionId
    });

    showOverlay(true);

    $.ajax({
        url: URL_GET_CLASSDIARY,
        type: 'POST',
        data: payload,
        success: function(html, status, xhr) {
            refreshCsrfFromXHR(xhr);
            $('#termweekdates').html(html || '<div class="alert alert-info">No diary found.</div>');
            bindAllEvents();
        },
        error: function(xhr) {
            $('#termweekdates').html('<div class="alert alert-danger">Failed to load class diary.</div>');
        },
        complete: function() { showOverlay(false); }
    });
}

// ============================================
// BIND ALL EVENTS FOR AUTO-SAVE
// ============================================
function bindAllEvents() {
    // Textarea input events
    $('#termweekdates').off('input', '.plain-textarea').on('input', '.plain-textarea', function() {
        triggerAutoSave();
    });
    
    // Toggle change events
    $('#termweekdates').off('change', '.toggle-audio, .toggle-video, .toggle-picture, .toggle-quiz, .toggle-book, .toggle-notebook').on('change', '.toggle-audio, .toggle-video, .toggle-picture, .toggle-quiz, .toggle-book, .toggle-notebook', function() {
        const $card = $(this).closest('.cd-day-card');
        const $quizSelect = $card.find('.quiz-select-field');
        const secSubId = $('#sec_sub_id').val();
        
        if ($(this).hasClass('toggle-audio')) {
            $card.find('.audio-caption-group').toggle($(this).is(':checked'));
        }
        if ($(this).hasClass('toggle-video')) {
            $card.find('.video-caption-group').toggle($(this).is(':checked'));
        }
        if ($(this).hasClass('toggle-picture')) {
            $card.find('.picture-caption-group').toggle($(this).is(':checked'));
        }
        if ($(this).hasClass('toggle-quiz')) {
            $card.find('.quiz-group').toggle($(this).is(':checked'));
            if ($(this).is(':checked') && secSubId) {
                loadQuizzesBySubject(secSubId, $quizSelect);
            } else if (!$(this).is(':checked')) {
                $quizSelect.val('');
            }
        }
        
        triggerAutoSave();
    });
    
    // Caption input events
    $('#termweekdates').off('input', '.task-caption-input').on('input', '.task-caption-input', function() {
        triggerAutoSave();
    });
    
    // Quiz select change
    $('#termweekdates').off('change', '.quiz-select-field').on('change', '.quiz-select-field', function() {
        triggerAutoSave();
    });
}

function loadQuizzesBySubject(secSubId, $selectElement) {
    const payload = addCsrf({ sec_sub_id: secSubId });
    
    $.ajax({
        url: URL_GET_QUIZZES,
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: function(res) {
            refreshCsrfFromXHR(res);
            if (res.success && res.quizzes.length > 0) {
                let options = '<option value="">Select Quiz</option>';
                res.quizzes.forEach(quiz => {
                    options += `<option value="${quiz.quiz_id}">${escapeHtml(quiz.title)}</option>`;
                });
                $selectElement.html(options);
            } else {
                $selectElement.html('<option value="">No quizzes available</option>');
            }
        },
        error: function() {
            $selectElement.html('<option value="">Error loading quizzes</option>');
        }
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ============================================
// ACTIVITY MANAGEMENT FUNCTIONS
// ============================================

function openActivityModal(date, activityId = null) {
    currentEditingDate = date;
    currentEditingActivityId = activityId;
    
    // Reset form
    $('#activity_name').val('');
    $('#activity_type').val('discussion');
    $('#activity_duration').val(20);
    $('#activity_description').val('');
    $('#activity_objectives').val('');
    $('#activity_materials').val('');
    
    // Reset toggles
    $('#enable_video_task').prop('checked', false);
    $('#enable_audio_task').prop('checked', false);
    $('#enable_group_activity').prop('checked', false);
    $('#video_task_section').hide();
    $('#audio_task_section').hide();
    $('#group_activity_section').hide();
    
    $('#activity_video_url').val('');
    $('#activity_video_caption').val('');
    $('#activity_audio_url').val('');
    $('#activity_audio_caption').val('');
    $('#activity_group_size').val(4);
    $('#activity_group_instructions').val('');
    
    // If editing existing activity, load its data
    if (activityId) {
        const $card = $(`.cd-day-card[data-date="${date}"]`);
        const activities = $card.data('activities') || [];
        const activity = activities.find(a => a.activity_id == activityId);
        
        if (activity) {
            $('#activity_name').val(activity.name);
            $('#activity_type').val(activity.type);
            $('#activity_duration').val(activity.duration_minutes);
            $('#activity_description').val(activity.description || '');
            $('#activity_objectives').val(activity.learning_objective || '');
            $('#activity_materials').val(activity.materials ? activity.materials.join(', ') : '');
            
            if (activity.video_task && activity.video_task.enabled) {
                $('#enable_video_task').prop('checked', true);
                $('#video_task_section').show();
                $('#activity_video_url').val(activity.video_task.url || '');
                $('#activity_video_caption').val(activity.video_task.caption || '');
            }
            
            if (activity.audio_task && activity.audio_task.enabled) {
                $('#enable_audio_task').prop('checked', true);
                $('#audio_task_section').show();
                $('#activity_audio_url').val(activity.audio_task.url || '');
                $('#activity_audio_caption').val(activity.audio_task.caption || '');
            }
            
            if (activity.group_activity && activity.group_activity.enabled) {
                $('#enable_group_activity').prop('checked', true);
                $('#group_activity_section').show();
                $('#activity_group_size').val(activity.group_activity.group_size || 4);
                $('#activity_group_instructions').val(activity.group_activity.instructions || '');
            }
        }
    }
    
    $('#activityModal').modal('show');
}

function saveActivityToDay() {
    // Validate
    const activityName = $('#activity_name').val().trim();
    if (!activityName) {
        if (typeof toastr !== 'undefined') {
            toastr.error('Please enter activity name');
        } else {
            alert('Please enter activity name');
        }
        return;
    }
    
    // Build activity object
    const activity = {
        activity_id: currentEditingActivityId || 'act_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
        name: activityName,
        type: $('#activity_type').val(),
        duration_minutes: parseInt($('#activity_duration').val()) || 20,
        description: $('#activity_description').val(),
        learning_objective: $('#activity_objectives').val(),
        materials: $('#activity_materials').val().split(',').map(m => m.trim()).filter(m => m)
    };
    
    // Video task
    if ($('#enable_video_task').is(':checked')) {
        activity.video_task = {
            enabled: true,
            url: $('#activity_video_url').val(),
            caption: $('#activity_video_caption').val()
        };
    }
    
    // Audio task
    if ($('#enable_audio_task').is(':checked')) {
        activity.audio_task = {
            enabled: true,
            url: $('#activity_audio_url').val(),
            caption: $('#activity_audio_caption').val()
        };
    }
    
    // Group activity
    if ($('#enable_group_activity').is(':checked')) {
        activity.group_activity = {
            enabled: true,
            group_size: parseInt($('#activity_group_size').val()) || 4,
            instructions: $('#activity_group_instructions').val()
        };
    }
    
    // Add/update activity in the day card
    const $card = $(`.cd-day-card[data-date="${currentEditingDate}"]`);
    let activities = $card.data('activities') || [];
    
    if (currentEditingActivityId) {
        // Update existing
        const index = activities.findIndex(a => a.activity_id == currentEditingActivityId);
        if (index !== -1) activities[index] = activity;
    } else {
        // Add new
        activities.push(activity);
    }
    
    $card.data('activities', activities);
    
    // Re-render activities
    renderActivities($card, activities);
    
    // Mark that card has activities
    $card.find('.has-activities-indicator').val(activities.length > 0 ? '1' : '0');
    
    // Close modal
    $('#activityModal').modal('hide');
    
    // Clear current editing variables
    const editedActivityId = currentEditingActivityId;
    currentEditingDate = null;
    currentEditingActivityId = null;
    
    // Trigger auto-save after modal is closed
    setTimeout(function() {
        if (typeof window.saveAllDiaryData === 'function') {
            window.saveAllDiaryData();
        }
    }, 200);
    
    // Show success message
    if (typeof toastr !== 'undefined') {
        toastr.success('Activity ' + (editedActivityId ? 'updated' : 'added') + ' successfully');
    }
}

function deleteActivity(date, activityId) {
    if (!confirm('Are you sure you want to delete this activity?')) return;
    
    const $card = $(`.cd-day-card[data-date="${date}"]`);
    let activities = $card.data('activities') || [];
    activities = activities.filter(a => a.activity_id != activityId);
    
    $card.data('activities', activities);
    
    renderActivities($card, activities);
    
    if (activities.length === 0) {
        $card.find('.has-activities-indicator').val('0');
    }
    
    // Trigger auto-save
    setTimeout(function() {
        if (typeof window.saveAllDiaryData === 'function') {
            window.saveAllDiaryData();
        }
    }, 100);
    
    if (typeof toastr !== 'undefined') {
        toastr.success('Activity deleted successfully');
    }
}

function renderActivities($card, activities) {
    const $container = $card.find('.activities-container');
    if (!$container.length) return;
    
    const date = $card.data('date');
    
    if (!activities || activities.length === 0) {
        $container.html(`
            <div class="no-activities-placeholder text-center py-3" style="border: 1px dashed #ddd; border-radius: 8px; background: #fafafa;">
                <i class="fa fa-calendar-plus fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">No classroom activities planned for this day.</p>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                        onclick="openActivityModal('${date}')">
                    <i class="fa fa-plus"></i> Add Activity
                </button>
            </div>
        `);
        return;
    }
    
    let html = '';
    for (let i = 0; i < activities.length; i++) {
        const activity = activities[i];
        const typeLabel = {
            'discussion': 'Group Discussion',
            'group-work': 'Group Work',
            'presentation': 'Presentation',
            'lab': 'Lab Work',
            'lecture': 'Lecture',
            'other': 'Activity'
        }[activity.type] || 'Activity';
        
        const typeIcon = {
            'discussion': 'fa-comments',
            'group-work': 'fa-users',
            'presentation': 'fa-chalkboard',
            'lab': 'fa-flask',
            'lecture': 'fa-microphone-alt',
            'other': 'fa-star'
        }[activity.type] || 'fa-tasks';
        
        const typeClass = {
            'discussion': 'discussion',
            'group-work': 'group-work',
            'presentation': 'presentation',
            'lab': 'lab',
            'lecture': 'lecture',
            'other': 'other'
        }[activity.type] || 'other';
        
        html += `
            <div class="activity-item" data-activity-id="${activity.activity_id}">
                <div class="activity-header">
                    <div class="activity-title">
                        <i class="fa ${typeIcon}"></i>
                        <span class="activity-name">${escapeHtml(activity.name)}</span>
                        <span class="activity-badge ${typeClass} ml-2">${typeLabel}</span>
                        ${activity.duration_minutes ? `<span class="badge badge-light ml-1"><i class="fa fa-clock-o"></i> ${activity.duration_minutes} min</span>` : ''}
                    </div>
                    <div class="activity-actions">
                        <button type="button" class="btn btn-sm btn-link text-primary p-0 mr-2" 
                                onclick="openActivityModal('${date}', '${activity.activity_id}')"
                                title="Edit Activity">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" 
                                onclick="deleteActivity('${date}', '${activity.activity_id}')"
                                title="Delete Activity">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
        `;
        
        if (activity.description) {
            html += `<div class="activity-description">${escapeHtml(activity.description)}</div>`;
        }
        
        if (activity.learning_objective) {
            html += `<div class="activity-objective small text-muted mt-1">
                        <i class="fa fa-bullseye"></i> <strong>Objective:</strong> ${escapeHtml(activity.learning_objective)}
                    </div>`;
        }
        
        if (activity.materials && activity.materials.length > 0) {
            html += `<div class="activity-materials small text-muted mt-1">
                        <i class="fa fa-cubes"></i> <strong>Materials:</strong> ${escapeHtml(activity.materials.join(', '))}
                    </div>`;
        }
        
        if (activity.video_task && activity.video_task.enabled || 
            activity.audio_task && activity.audio_task.enabled || 
            activity.group_activity && activity.group_activity.enabled) {
            html += `<div class="activity-tasks mt-2">`;
            
            if (activity.video_task && activity.video_task.enabled) {
                html += `<span class="task-badge video-task" title="Video Task">
                            <i class="fa fa-video-camera"></i> Video Task`;
                if (activity.video_task.caption) {
                    html += `<small class="text-muted d-block ml-4">${escapeHtml(activity.video_task.caption)}</small>`;
                }
                html += `</span>`;
            }
            
            if (activity.audio_task && activity.audio_task.enabled) {
                html += `<span class="task-badge audio-task" title="Audio Task">
                            <i class="fa fa-headphones"></i> Audio Task`;
                if (activity.audio_task.caption) {
                    html += `<small class="text-muted d-block ml-4">${escapeHtml(activity.audio_task.caption)}</small>`;
                }
                html += `</span>`;
            }
            
            if (activity.group_activity && activity.group_activity.enabled) {
                html += `<span class="task-badge group-task" title="Group Activity">
                            <i class="fa fa-users"></i> Group Work (${activity.group_activity.group_size} students/group)`;
                if (activity.group_activity.instructions) {
                    html += `<small class="text-muted d-block ml-4">${escapeHtml(activity.group_activity.instructions)}</small>`;
                }
                html += `</span>`;
            }
            
            html += `</div>`;
        }
        
        html += `</div>`;
    }
    
    $container.html(html);
}

// Make activity functions globally available
window.openActivityModal = openActivityModal;
window.saveActivityToDay = saveActivityToDay;
window.deleteActivity = deleteActivity;

// ============================================
// INITIALIZATION
// ============================================
$(function() {
    // Modal toggle handlers
    $('#enable_video_task').change(function() {
        $('#video_task_section').toggle($(this).is(':checked'));
    });
    $('#enable_audio_task').change(function() {
        $('#audio_task_section').toggle($(this).is(':checked'));
    });
    $('#enable_group_activity').change(function() {
        $('#group_activity_section').toggle($(this).is(':checked'));
    });
    
    // Load subjects when section changes
    $('#section_id').on('change', function() {
        loadSubjectsForSection($(this).val());
    });

    // Auto-load diary when subject or week changes
    $('#sec_sub_id, #term_weeks').on('change', function() {
        if ($('#sec_sub_id').val() && $('#term_weeks').val() && $('#section_id').val()) {
            getclassdiary();
        }
    });

    // Initial load if section already selected
    const initialSection = $('#section_id').val();
    if (initialSection && initialSection != 0) {
        loadSubjectsForSection(initialSection);
    }
});
</script>


<?= $this->endSection() ?>