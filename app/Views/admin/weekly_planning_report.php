<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Weekly Planning Report</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Weekly Planning Report</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-chart-line"></i> Filter Weekly Planning
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <form id="report-filter-form" class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label for="term_session_id">Term <span class="text-danger">*</span></label>
                <select name="term_session_id" id="term_session_id" class="form-control" required>
                  <option value="">Select Term</option>
                  <?php foreach($terms as $term): ?>
                    <option value="<?= $term->term_session_id ?>"><?= $term->term_name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="form-group">
                <label for="section_id">Class/Section</label>
                <select name="section_id" id="section_id" class="form-control select2">
                  <option value="">All Classes</option>
                  <?php foreach($sections as $section): ?>
                    <option value="<?= $section->cls_sec_id ?>"><?= $section->class_name ?> - <?= $section->section_name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="form-group">
                <label for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" class="form-control select2">
                  <option value="">All Subjects</option>
                  <?php foreach($subjects as $subject): ?>
                    <option value="<?= $subject->sid ?>"><?= $subject->subject_name ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="form-group">
                <label for="week_id">Week</label>
                <select name="week_id" id="week_id" class="form-control">
                  <option value="">All Weeks</option>
                </select>
              </div>
            </div>
          </form>
          
          <div class="row mt-2">
            <div class="col-12">
              <button type="button" id="btn-view-report" class="btn btn-primary">
                <i class="fas fa-search"></i> Generate Report
              </button>
              <button type="button" id="btn-reset" class="btn btn-default">
                <i class="fas fa-undo"></i> Reset
              </button>
              <div class="btn-group float-right">
                <button type="button" id="btn-export-pdf" class="btn btn-danger" disabled>
                  <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button type="button" id="btn-export-excel" class="btn btn-success" disabled>
                  <i class="fas fa-file-excel"></i> Excel
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="col-12">
      <div id="report-loader" class="text-center" style="display: none;">
        <div class="spinner-border text-primary" role="status">
          <span class="sr-only">Loading...</span>
        </div>
        <p>Loading report data...</p>
      </div>
      
      <div id="report-results" style="display: none;"></div>
    </div>
  </div>
</section>

<style>
/* Existing styles... */

.week-card {
  transition: all 0.3s ease;
  border: 1px solid #e0e0e0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.week-card.empty-week {
  opacity: 0.85;
}

.week-card.empty-week .card-header {
  background: linear-gradient(135deg, #a0a0a0 0%, #808080 100%) !important;
}

.week-card.empty-week:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  opacity: 1;
}

.week-card .card-header {
  padding: 12px 15px;
}

.week-card .objectives-content {
  min-height: 150px;
  max-height: 250px;
  overflow-y: auto;
  font-size: 14px;
  line-height: 1.6;
  color: #2c3e50;
}

.objectives-text {
  color: #2c3e50;
}

.objectives-text ul,
.objectives-text ol {
  padding-left: 20px;
  margin-bottom: 0;
}

.objectives-text p {
  margin-bottom: 8px;
}

.objectives-text h1, 
.objectives-text h2, 
.objectives-text h3,
.objectives-text h4 {
  margin-top: 0;
  margin-bottom: 10px;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.subject-section {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 1px solid #e9ecef;
}

.subject-section h5 {
  margin-bottom: 15px;
  padding-bottom: 10px;
  font-weight: 600;
}

.card-header h4 {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.card-header h4 i {
  margin-right: 8px;
}

/* Scrollbar styling for objectives content */
.objectives-content::-webkit-scrollbar {
  width: 6px;
}

.objectives-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.objectives-content::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.objectives-content::-webkit-scrollbar-thumb:hover {
  background: #555;
}

@media print {
  .card-tools, .btn, .form-group, #report-filter-form {
    display: none !important;
  }
  
  .week-card {
    break-inside: avoid;
    page-break-inside: avoid;
  }
  
  .week-card.empty-week .card-header {
    background: #e0e0e0 !important;
    color: #000 !important;
    print-color-adjust: exact;
  }
}
</style>
<style>
.report-container {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.week-card {
  transition: all 0.3s ease;
  border: 1px solid #e0e0e0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.week-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.week-card .card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.week-card .objectives-content {
  min-height: 150px;
  max-height: 200px;
  overflow-y: auto;
  font-size: 14px;
  line-height: 1.6;
}

.week-card .objectives-content ul,
.week-card .objectives-content ol {
  padding-left: 20px;
}

.week-card .card-footer {
  background-color: #f8f9fa;
}

.subject-section {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.subject-section h5 {
  color: #4a5568;
  margin-bottom: 15px;
}

.card-header h4 {
  font-size: 18px;
  font-weight: 600;
}

@media print {
  .card-tools, .btn, .form-group, #report-filter-form {
    display: none !important;
  }
  
  .week-card {
    break-inside: avoid;
    page-break-inside: avoid;
  }
}

/* Add to your view's style section */
.weeks-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 10px;
}

.week-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 300px;
}

.week-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px;
    text-align: center;
    flex-shrink: 0;
}

.week-card .card-header strong {
    font-size: 14px;
    display: block;
    margin-bottom: 5px;
}

.week-card .card-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    background: #fafafa;
}

.week-card .card-footer {
    flex-shrink: 0;
}

/* Responsive */
@media (max-width: 1200px) {
    .weeks-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .weeks-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script type="text/javascript">
$(document).ready(function() {
  // Initialize select2
  if ($.fn.select2) {
    $('.select2').select2({
      width: '100%',
      placeholder: 'Select option'
    });
  }
  
  // Load weeks when term changes
  $('#term_session_id').change(function() {
    var term_session_id = $(this).val();
    if (term_session_id) {
      $.ajax({
        url: '<?= base_url('admin/weekly_planning_report/getWeeks') ?>',
        type: 'POST',
        data: { term_session_id: term_session_id },
        success: function(res) {
          $('#week_id').html(res);
        }
      });
    } else {
      $('#week_id').html('<option value="">All Weeks</option>');
    }
  });
  
  // Generate report
  $('#btn-view-report').click(function() {
    var term_session_id = $('#term_session_id').val();
    
    if (!term_session_id) {
      toastr.warning('Please select a term');
      return;
    }
    
    var formData = {
      term_session_id: term_session_id,
      section_id: $('#section_id').val(),
      subject_id: $('#subject_id').val(),
      week_id: $('#week_id').val()
    };
    
    $('#report-loader').show();
    $('#report-results').hide();
   // Export PDF
$('#btn-export-pdf').click(function() {
    if ($(this).prop('disabled')) return;
    
    var term_session_id = $('#term_session_id').val();
    if (!term_session_id) {
        toastr.warning('Please select a term first');
        return;
    }
    
    var params = {
        term_session_id: term_session_id,
        section_id: $('#section_id').val(),
        subject_id: $('#subject_id').val(),
        week_id: $('#week_id').val()
    };
    
    // Open in new window
    var url = '<?= base_url('admin/weekly_planning_report/exportPdf') ?>?' + $.param(params);
    window.open(url, '_blank');
});

// Export Excel
$('#btn-export-excel').click(function() {
    if ($(this).prop('disabled')) return;
    
    var term_session_id = $('#term_session_id').val();
    if (!term_session_id) {
        toastr.warning('Please select a term first');
        return;
    }
    
    var params = {
        term_session_id: term_session_id,
        section_id: $('#section_id').val(),
        subject_id: $('#subject_id').val(),
        week_id: $('#week_id').val()
    };
    
    // Open in same window for download
    var url = '<?= base_url('admin/weekly_planning_report/exportExcel') ?>?' + $.param(params);
    window.location.href = url;
});
    
    $.ajax({
      url: '<?= base_url('admin/weekly_planning_report/getData') ?>',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        $('#report-loader').hide();
        
        if (response.success) {
          $('#report-results').html(response.html).show();
          $('#btn-export-pdf, #btn-export-excel').prop('disabled', false);
          
          // Scroll to results
          $('html, body').animate({
            scrollTop: $('#report-results').offset().top - 100
          }, 500);
        } else {
          toastr.error(response.message || 'No data found');
          $('#report-results').html('<div class="alert alert-warning">' + (response.message || 'No data found') + '</div>').show();
        }
      },
      error: function() {
        $('#report-loader').hide();
        toastr.error('Error loading report data');
      }
    });
  });
  
  // Reset filters
  $('#btn-reset').click(function() {
    $('#term_session_id').val('').trigger('change');
    $('#section_id').val('').trigger('change');
    $('#subject_id').val('').trigger('change');
    $('#week_id').html('<option value="">All Weeks</option>');
    $('#report-results').hide().empty();
    $('#btn-export-pdf, #btn-export-excel').prop('disabled', true);
    toastr.info('Filters reset');
  });
  
  // Export PDF
  $('#btn-export-pdf').click(function() {
    if ($(this).prop('disabled')) return;
    
    var formData = {
      term_session_id: $('#term_session_id').val(),
      section_id: $('#section_id').val(),
      subject_id: $('#subject_id').val(),
      week_id: $('#week_id').val()
    };
    
    $.ajax({
      url: '<?= base_url('admin/weekly_planning_report/exportPdf') ?>',
      type: 'POST',
      data: formData,
      success: function(response) {
        if (response.success) {
          toastr.success('PDF export initiated');
          // Open PDF in new window or download
          window.open('<?= base_url('admin/weekly_planning_report/downloadPdf') ?>?' + $.param(formData), '_blank');
        } else {
          toastr.error(response.message);
        }
      }
    });
  });
  
  // Export Excel
  $('#btn-export-excel').click(function() {
    if ($(this).prop('disabled')) return;
    
    var formData = {
      term_session_id: $('#term_session_id').val(),
      section_id: $('#section_id').val(),
      subject_id: $('#subject_id').val(),
      week_id: $('#week_id').val()
    };
    
    window.location.href = '<?= base_url('admin/weekly_planning_report/exportExcel') ?>?' + $.param(formData);
  });
});
</script>

<?= $this->endSection() ?>