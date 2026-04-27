<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<style>
    /* Enhanced Diary Card Styles */
    .diary-card {
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: #fff;
    }
    
    .diary-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .diary-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .diary-date {
        font-size: 18px;
        font-weight: 600;
    }
    
    .diary-date i {
        margin-right: 8px;
    }
    
    .diary-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .badge-feature {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(255,255,255,0.2);
        color: white;
    }
    
    .badge-feature i {
        margin-right: 5px;
    }
    
    .diary-body {
        padding: 20px;
    }
    
    /* Section Styles */
    .diary-section {
        margin-bottom: 25px;
        border-left: 4px solid #e0e0e0;
        padding-left: 15px;
    }
    
    .diary-section h6 {
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
        font-size: 16px;
    }
    
    .diary-section h6 i {
        margin-right: 8px;
        color: #667eea;
    }
    
    .diary-section-content {
        color: #555;
        line-height: 1.6;
        font-size: 14px;
    }
    
    /* Tasks Grid */
    .tasks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }
    
    .task-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }
    
    .task-card:hover {
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .task-icon {
        font-size: 20px;
        margin-right: 10px;
    }
    
    .task-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .task-caption {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    
    /* Quiz Card */
    .quiz-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
    }
    
    .quiz-card h6 {
        color: white;
        margin-bottom: 10px;
    }
    
    .quiz-card .quiz-title {
        font-size: 14px;
        font-weight: 500;
    }
    
    /* Activity Card */
    .activity-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    
    .activity-header {
        background: #f8f9fa;
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .activity-name {
        font-weight: 600;
        color: #333;
    }
    
    .activity-type-badge {
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 20px;
        background: #e9ecef;
        color: #666;
    }
    
    .activity-type-badge.discussion { background: #d4edda; color: #155724; }
    .activity-type-badge.group-work { background: #d1ecf1; color: #0c5460; }
    .activity-type-badge.presentation { background: #fff3cd; color: #856404; }
    .activity-type-badge.lab { background: #d4edda; color: #155724; }
    .activity-type-badge.lecture { background: #cce5ff; color: #004085; }
    
    .activity-body {
        padding: 15px;
    }
    
    .activity-description {
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .activity-meta {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #888;
        margin-top: 10px;
    }
    
    .activity-task-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        background: #f8f9fa;
        padding: 4px 10px;
        border-radius: 20px;
        margin-right: 8px;
        margin-top: 8px;
    }
    
    /* Bag Pack Items */
    .bagpack-items {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .bagpack-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
    }
    
    /* No Data Message */
    .no-data-message {
        text-align: center;
        padding: 40px;
        color: #999;
        background: #fafafa;
        border-radius: 8px;
    }
    
    /* Print Styles */
    @media print {
        .diary-header {
            background: #f5f5f5 !important;
            color: #333 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .badge-feature {
            background: #e0e0e0 !important;
            color: #333 !important;
        }
        
        .task-card, .activity-card, .quiz-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .tasks-grid {
            grid-template-columns: 1fr;
        }
        
        .diary-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .bagpack-items {
            flex-direction: column;
            gap: 8px;
        }
    }
</style>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-book-open"></i> Daily Diary</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Daily Diary</li>
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
              <a class="nav-link" href="<?= base_url('admin/classdiary/add') ?>">
                <i class="fas fa-plus-circle"></i> Add Daily Diary
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="<?= base_url('admin/classdiary-view') ?>">
                <i class="fas fa-eye"></i> Class Diary
              </a>
            </li>
          </ul>
        </div>

        <div class="card-body">
          <div class="col-lg-12">
            <form id="diaryFilterForm" method="post" action="<?= base_url('admin/classdiary-view/data') ?>">
              <div class="row">
                <!-- Terms -->
                <div class="col-lg-3">
                  <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Term</label>
                    <select class="form-control select2" name="term_id" id="term_id">
                      <option value="">-- Select Term --</option>
                      <?php foreach ($terms_session_info as $value): ?>
                        <option value="<?= $value->term_session_id; ?>"
                          <?= isset($current_term_session_id) && (int)$current_term_session_id === (int)$value->term_session_id ? 'selected' : '' ?>>
                          <?= esc($value->term_name); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- Term Weeks -->
                <div class="col-lg-3">
                  <div class="form-group">
                    <label><i class="fas fa-week"></i> Week</label>
                    <select class="form-control" name="term_weeks_id" id="term_weeks">
                      <option value="">-- Select Term First --</option>
                    </select>
                  </div>
                </div>
                
                <!-- Section -->
                <div class="col-lg-3">
                  <div class="form-group">
                    <label><i class="fas fa-users"></i> Section</label>
                    <select class="form-control select2" name="section_id" id="section_id">
                      <option value="">-- All Sections --</option>
                      <?php if (isset($sections)): ?>
                        <?php foreach ($sections as $section): ?>
                          <option value="<?= $section['cls_sec_id'] ?>">
                            <?= esc($section['section_name']) ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>
                </div>

                <!-- View Button -->
                <div class="col-lg-3 d-flex align-items-end">
                  <button type="submit" id="viewBtn" class="btn btn-primary btn-block" style="height:42px;">
                    <i class="fas fa-search"></i> View Diary
                  </button>
                </div>
              </div>

              <!-- Enhanced Report Options -->
              <div class="row mt-3 mb-3 print-hide">
                <div class="col-md-12">
                  <div class="card">
                    <div class="card-header bg-light">
                      <h5 class="mb-0">
                        <i class="fas fa-sliders-h"></i> Report Options
                        <button type="button" class="btn btn-sm btn-outline-secondary ml-2" id="toggleAllOptions">
                          <i class="fas fa-check-double"></i> Select All
                        </button>
                      </h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input option-checkbox" name="show_homework" id="show_homework" value="1" checked>
                            <label class="form-check-label" for="show_homework">
                              <i class="fas fa-pencil-alt text-primary"></i> Home Work
                            </label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input option-checkbox" name="show_classwork" id="show_classwork" value="1" checked>
                            <label class="form-check-label" for="show_classwork">
                              <i class="fas fa-chalkboard text-success"></i> Class Work
                            </label>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input option-checkbox" name="show_audio" id="show_audio" value="1" checked>
                            <label class="form-check-label" for="show_audio">
                              <i class="fas fa-headphones text-info"></i> Audio Tasks
                            </label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input option-checkbox" name="show_video" id="show_video" value="1" checked>
                            <label class="form-check-label" for="show_video">
                              <i class="fas fa-video text-danger"></i> Video Tasks
                            </label>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input option-checkbox" name="show_picture" id="show_picture" value="1" checked>
                            <label class="form-check-label" for="show_picture">
                              <i class="fas fa-image text-warning"></i> Picture Tasks
                            </label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input option-checkbox" name="show_quiz" id="show_quiz" value="1" checked>
                            <label class="form-check-label" for="show_quiz">
                              <i class="fas fa-question-circle text-purple"></i> Quizzes
                            </label>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input option-checkbox" name="show_activities" id="show_activities" value="1" checked>
                            <label class="form-check-label" for="show_activities">
                              <i class="fas fa-tasks text-orange"></i> Activities
                            </label>
                          </div>
                          <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input option-checkbox" name="show_bagpack" id="show_bagpack" value="1" checked>
                            <label class="form-check-label" for="show_bagpack">
                              <i class="fas fa-bag-shopping text-secondary"></i> Bag Pack
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>

            <!-- Results Container -->
            <div id="termweekdates" class="mt-3"></div>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$(document).ready(function() {
    // Load weeks when term is selected
    $('#term_id').on('change', function() {
        var termId = $(this).val();
        if (termId) {
            $.ajax({
                url: '<?= base_url('admin/classdiary-view/getWeeks') ?>',
                type: 'POST',
                data: { term_id: termId },
                dataType: 'json',
                success: function(response) {
                    var weeksSelect = $('#term_weeks');
                    weeksSelect.empty();
                    weeksSelect.append('<option value="">-- Select Week --</option>');
                    
                    if (response.weeks && response.weeks.length > 0) {
                        $.each(response.weeks, function(key, week) {
                            var startDate = new Date(week.start_date);
                            var endDate = new Date(week.end_date);
                            var dateRange = formatDate(startDate) + ' - ' + formatDate(endDate);
                            weeksSelect.append('<option value="' + week.term_weeks_id + '">Week ' + week.week_no + ' (' + dateRange + ')</option>');
                        });
                        
                        <?php if (isset($current_term_week_id) && $current_term_week_id): ?>
                        weeksSelect.val('<?= $current_term_week_id ?>');
                        <?php endif; ?>
                    } else {
                        weeksSelect.append('<option value="">No weeks found</option>');
                    }
                },
                error: function() {
                    toastr.error('Error loading weeks');
                }
            });
        } else {
            $('#term_weeks').empty().append('<option value="">-- Select Term First --</option>');
        }
    });
    
    // Load initial weeks if term is pre-selected
    var initialTermId = $('#term_id').val();
    if (initialTermId) {
        $('#term_id').trigger('change');
    }
    
    // Toggle all options
    var allChecked = true;
    $('#toggleAllOptions').on('click', function() {
        var checkboxes = $('.option-checkbox');
        allChecked = !allChecked;
        checkboxes.prop('checked', allChecked);
        $(this).html(allChecked ? '<i class="fas fa-check-double"></i> Select All' : '<i class="fas fa-square"></i> Deselect All');
    });
    
    // Handle form submission
    $('#diaryFilterForm').on('submit', function(e) {
        e.preventDefault();
        
        var termWeeksId = $('#term_weeks').val();
        if (!termWeeksId) {
            toastr.warning('Please select a term week');
            return false;
        }
        
        // Show loading indicator
        $('#termweekdates').html(`
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-pulse fa-3x text-primary"></i>
                <p class="mt-3">Loading diary entries...</p>
            </div>
        `);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#termweekdates').html(response);
                // Initialize any tooltips or popovers in the response
                $('[data-toggle="tooltip"]').tooltip();
            },
            error: function(xhr, status, error) {
                $('#termweekdates').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error loading diary data: ${error}
                    </div>
                `);
                toastr.error('Failed to load diary data');
            }
        });
    });
    
    // Format date helper
    function formatDate(date) {
        var day = date.getDate().toString().padStart(2, '0');
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var year = date.getFullYear();
        return day + '-' + month + '-' + year;
    }
    
    // Trigger initial load if week is selected
    if ($('#term_weeks').val()) {
        $('#viewBtn').trigger('click');
    }
});
</script>

<?= $this->endSection() ?>