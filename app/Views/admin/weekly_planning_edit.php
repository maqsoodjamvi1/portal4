<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Weekly Planning</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Weekly Planning</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline">
        
        <div class="card-body">
          <?= form_open(base_url('admin/weekly_planning/save'), 'role="form" id="weekly-planning-form"') ?>
          <input type="hidden" name="selected_class_id" id="selected_class_id" value="">
          <input type="hidden" name="selected_section_id" id="selected_section_id" value="">
          <input type="hidden" name="session_id" id="session_id" value="<?= $current_session_id ?>">
          
          <!-- Filter Section -->
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="term_session_id">Select Term</label>
                <select class="form-control" name="term_session_id" id="term_session_id" required>
                  <option value="">Select Term</option>
                  <?php if(isset($terms_session_info) && !empty($terms_session_info)):
                    foreach ($terms_session_info as $termvalue): ?>
                      <option value="<?= $termvalue->term_session_id ?>" <?= ($default_term_session_id == $termvalue->term_session_id) ? 'selected' : '' ?>>
                        <?= $termvalue->term_name ?>
                      </option>
                  <?php endforeach; endif; ?>
                </select>
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="form-group">
                <label for="section_id">Select Section</label>
                <select class="form-control select2" name="section_id" id="section_id" required>
                  <option value="">Select Section</option>
                  <?php if(isset($sectionsclassinfo) && !empty($sectionsclassinfo)):
                    foreach ($sectionsclassinfo as $secionvalue): ?>
                      <option value="<?= $secionvalue['cls_sec_id'] ?>" data-class-id="<?= $secionvalue['class_id'] ?>">
                        <?= $secionvalue['sectionclassname'] ?>
                      </option>
                  <?php endforeach; endif; ?>
                </select>
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="form-group">
                <label for="subject_id">Select Subject</label>
                <select class="form-control" name="subject_id" id="subject_id" required>
                  <option value="">First select a section</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <div id="loader-1" class="text-center" style="display: none;">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i> Loading...
              </div>
            </div>
          </div>
          
          <hr>
          
          <!-- Top Level Planning Section -->
          <div id="top-level-planning" style="display: none;">
            <div class="alert alert-info">
              <h5><i class="icon fas fa-info-circle"></i> Top Level Planning</h5>
              <div id="top-level-content"></div>
            </div>
          </div>
          
          <!-- Weekly Planning Cards Section -->
          <div id="weekly-planning-container" style="display: none;"></div>
          
          <!-- Save Button -->
          <div class="row" id="save-button-container" style="display: none;">
            <div class="col-md-12">
              <hr>
              <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                  <i class="fas fa-save"></i> Save All Changes
                </button>
                <button type="reset" class="btn btn-default btn-lg">Reset</button>
                <button type="button" class="btn btn-default btn-lg" onclick="history.go(-1);">Cancel</button>
              </div>
            </div>
          </div>
          
          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="text/javascript">
// Auto-save functionality
let autoSaveTimer = null;
let isDirty = false;

function markAsDirty() {
  isDirty = true;
  if (autoSaveTimer) clearTimeout(autoSaveTimer);
  autoSaveTimer = setTimeout(function() {
    if (isDirty) autoSave();
  }, 5000);
}

function autoSave() {
  if (!isDirty) return;
  
  var formData = $('#weekly-planning-form').serialize();
  
  $.ajax({
    url: $('#weekly-planning-form').attr('action'),
    type: "POST",
    data: formData,
    success: function(response) {
      if (response.success) {
        toastr.success('Auto-saved successfully');
        isDirty = false;
      } else {
        toastr.warning('Auto-save failed');
      }
    },
    error: function() {
      toastr.error('Auto-save failed');
    }
  });
}

$(document).ready(function() {
  // Initialize select2
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }
});

// Term change handler
$("#term_session_id").change(function() {
  var term_session_id = $(this).val();
  if (term_session_id && term_session_id != '') {
    var section_id = $('#section_id').val();
    var subject_id = $('#subject_id').val();
    if (section_id && section_id != '' && subject_id && subject_id != '') {
      loadWeeklyPlanning();
    }
  }
});

// Section change handler
$("#section_id").change(function() {
  var section_id = $(this).val();
  var selectedOption = $(this).find('option:selected');
  var class_id = selectedOption.data('class-id');
  
  if (section_id && section_id != '') {
    $("#selected_class_id").val(class_id);
    $("#selected_section_id").val(section_id);
    $("#loader-1").show();
    
    $.ajax({
      url: '<?= base_url('admin/weekly_planning/getSubjectsBySection') ?>',
      type: "POST",
      data: { section_id: section_id },
      dataType: 'json',
      success: function(res) {
        $("#subject_id").html(res.html);
        $("#loader-1").hide();
        $("#weekly-planning-container").html('').hide();
        $("#top-level-planning").hide();
        $("#save-button-container").hide();
      },
      error: function(xhr, status, error) {
        $("#loader-1").hide();
        console.log("Error loading subjects:", error);
        toastr.error('Error loading subjects');
      }
    });
  } else {
    $("#subject_id").html('<option value="">First select a section</option>');
    $("#selected_class_id").val('');
    $("#selected_section_id").val('');
    $("#weekly-planning-container").html('').hide();
    $("#top-level-planning").hide();
    $("#save-button-container").hide();
  }
});

// Subject change handler
$("#subject_id").change(function() {
  var subject_id = $(this).val();
  if (subject_id && subject_id != '') {
    loadWeeklyPlanning();
  } else {
    $("#weekly-planning-container").html('').hide();
    $("#top-level-planning").hide();
    $("#save-button-container").hide();
  }
});

// Main function to load weekly planning
function loadWeeklyPlanning() {
  var term_session_id = $('#term_session_id').val();
  var subject_id = $('#subject_id').val();
  var section_id = $('#section_id').val();
  var selected_class_id = $('#selected_class_id').val();
  
  if (!term_session_id || term_session_id == '') {
    toastr.warning('Please select a Term first');
    return;
  }
  
  if (!section_id || section_id == '') {
    toastr.warning('Please select a Section first');
    return;
  }
  
  if (!subject_id || subject_id == '') {
    toastr.warning('Please select a Subject first');
    return;
  }
  
  if (!selected_class_id || selected_class_id == '') {
    toastr.warning('Please re-select the section');
    return;
  }
  
  $("#loader-1").show();
  
  // Load Top Level Planning
  $.ajax({
    url: '/admin/weekly_planning/get-top-level-planning',
    type: "POST",
    data: {
      term_session_id: term_session_id,
      section_id: section_id,
      subject_id: subject_id,
      selected_class_id: selected_class_id
    },
    dataType: 'json',
    success: function(topLevelRes) {
      if (topLevelRes && topLevelRes.objective && topLevelRes.objective != '') {
        var topLevelHtml = '';
        
        if (topLevelRes.found_in_class == 'other') {
          topLevelHtml += '<div class="alert alert-warning mb-2">';
          topLevelHtml += '<i class="fas fa-info-circle"></i> ';
          topLevelHtml += 'Note: Top level planning from class <strong>' + (topLevelRes.source_class_name || 'another class') + '</strong> is being displayed. ';
          topLevelHtml += 'You can create class-specific planning if needed.';
          topLevelHtml += '</div>';
        }
        
        topLevelHtml += '<p><strong>Objective:</strong> ' + topLevelRes.objective + '</p>';
        
        $("#top-level-content").html(topLevelHtml);
        $("#top-level-planning").show();
      } else {
        var noPlanHtml = '<p class="text-muted">No top level planning available for this subject in this term.</p>';
        noPlanHtml += '<button type="button" class="btn btn-sm btn-info mt-2" onclick="createTopLevelPlanning()">';
        noPlanHtml += '<i class="fas fa-plus"></i> Create Top Level Planning';
        noPlanHtml += '</button>';
        $("#top-level-content").html(noPlanHtml);
        $("#top-level-planning").show();
      }
    },
    error: function() {
      $("#top-level-content").html('<p class="text-muted">Could not load top level planning.</p>');
      $("#top-level-planning").show();
    }
  });
  
  // Load Weekly Planning
  $.ajax({
    url: '/admin/weekly_planning/get-weekly-planning',
    type: "POST",
    data: {
      term_session_id: term_session_id,
      section_id: section_id,
      subject_id: subject_id,
      selected_class_id: selected_class_id
    },
    dataType: 'json',
    success: function(res) {
      $("#weekly-planning-container").html(res.html).show();
      $("#save-button-container").show();
      $("#loader-1").hide();
      
      // Initialize Summernote for all editors
      $('.weekly-editor').each(function() {
        $(this).summernote({
          height: 200,
          toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview', 'help']]
          ],
          callbacks: {
            onChange: function() {
              markAsDirty();
            }
          }
        });
      });
    },
    error: function(xhr, status, error) {
      $("#loader-1").hide();
      console.log("Error loading weekly planning:", error);
      toastr.error('Error loading weekly planning');
    }
  });
}

// Function to create top level planning
function createTopLevelPlanning() {
  var term_session_id = $('#term_session_id').val();
  var section_id = $('#section_id').val();
  var subject_id = $('#subject_id').val();
  var subject_text = $('#subject_id option:selected').text();
  var class_id = $('#selected_class_id').val();
  
  if (!term_session_id || !section_id || !subject_id) {
    toastr.warning('Please select Term, Section and Subject first');
    return;
  }
  
  window.location.href = '/admin/top_level_planning/add?term_session_id=' + term_session_id + 
                        '&section_id=' + section_id + 
                        '&subject_id=' + subject_id + 
                        '&class_id=' + class_id +
                        '&subject_name=' + encodeURIComponent(subject_text);
}

// Form submit handler
$(function() {
  $('#weekly-planning-form').validate({
    rules: {},
    messages: {}
  });
  
  $('#weekly-planning-form').ajaxForm({
    beforeSubmit: function(formData, jqForm, options) {
      if (!$('#weekly-planning-form').valid()) return false;
      
      // Sync Summernote content
      $('.weekly-editor').each(function() {
        var editorId = $(this).attr('id');
        if (editorId) {
          var content = $(this).summernote('code');
          $(this).val(content);
        }
      });
      
      $('#submitBtn').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
      $('#submitBtn').prop('disabled', true);
      isDirty = false;
      return true;
    },
    success: function(responseText, statusText, xhr, form) {
      $('#submitBtn').html('<i class="fas fa-save"></i> Save All Changes');
      $('#submitBtn').prop('disabled', false);
      
      var json = typeof responseText === 'string' ? JSON.parse(responseText) : responseText;
      
      if (json.success) {
        toastr.success(json.msg);
        isDirty = false;
        setTimeout(function() {
          loadWeeklyPlanning();
        }, 1000);
      } else {
        toastr.error(json.msg);
      }
      return false;
    },
    error: function() {
      $('#submitBtn').html('<i class="fas fa-save"></i> Save All Changes');
      $('#submitBtn').prop('disabled', false);
      toastr.error('An error occurred while saving');
    }
  });
});

// Warn before leaving if unsaved changes
window.addEventListener('beforeunload', function(e) {
  if (isDirty) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    return e.returnValue;
  }
});
</script>

<style>
.alert-info {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  margin-bottom: 20px;
}

.alert-info h5 {
  color: white;
  margin-bottom: 10px;
}

.alert-info i {
  margin-right: 10px;
}

/* Weekly row layout - 3 cards in a row */
.weekly-row {
  display: flex;
  flex-wrap: wrap;
  margin: 0 -10px 30px -10px;
}

.weekly-card {
  flex: 1;
  min-width: calc(33.333% - 20px);
  margin: 0 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  background: #fff;
}

.weekly-card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 12px 15px;
  border-bottom: none;
}

.weekly-card-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.weekly-card-header .week-date {
  font-size: 11px;
  opacity: 0.9;
  margin-top: 5px;
}

.weekly-card-body {
  padding: 15px;
  background: #fff;
}

.weekly-card-body .form-group {
  margin-bottom: 0;
}

.weekly-card-body label {
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
  font-size: 13px;
  display: block;
}

.weekly-editor {
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 4px;
}

/* Responsive: On tablets, show 2 per row */
@media (max-width: 992px) {
  .weekly-card {
    min-width: calc(50% - 20px);
    margin-bottom: 20px;
  }
  .weekly-row {
    margin-bottom: 0;
  }
}

/* Responsive: On mobile, show 1 per row */
@media (max-width: 768px) {
  .weekly-card {
    min-width: calc(100% - 20px);
    margin-bottom: 20px;
  }
}
</style>

<?= $this->endSection() ?>