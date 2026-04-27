<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<section class="content-header no-print">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Students Results List</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students Results List</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0 no-print">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_results_list') ?>">Student Results List</a></li>
          </ul>
        </div>

        <div class="card-body">
          <div class="tab-content">
            <!-- filters -->
            <input type="hidden" name="session_id" id="session_id" value="<?= $sessionData['sessionid'] ?? '' ?>">
            <input type="hidden" name="campus_id" id="campus_id" value="<?= $sessionData['campusid'] ?? '' ?>">

            <div class="row no-print">
              <!-- Student Type Selection -->
              <div class="col-lg-3">
                <div class="form-group">
                  <label for="student_type">Student Type</label>
                  <select name="student_type" id="student_type" class="form-control">
                    <option value="current">Current Active Students Only</option>
                    <option value="session_based">Session Based Students (Historical)</option>
                  </select>
                  <small class="text-muted" id="student_type_hint">
                    Showing only currently active students
                  </small>
                </div>
              </div>

              <div class="col-lg-2">
                <div class="form-group">
                  <label for="term">Exam</label>
                  <select name="eid" id="eid" class="form-control">
                    <?php foreach($examinfo as $exam){ ?>
                      <option value="<?= $exam->eid; ?>"><?= $exam->exam_name; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              
              <div class="col-lg-3">
                <div class="form-group pull-left">
                  <label for="class">Sections</label>
                  <select class="form-control select2" name="cls_sec_id" id="cls_sec_id">
                    <option value="0">Select Section</option>
                    <?php if(isset($sectionsclassinfo)){
                      foreach ($sectionsclassinfo as $row) { ?>
                        <option value="<?= esc($row['cls_sec_id']) ?>">
                          <?= esc($row['sectionclassname']) ?>
                        </option>
                      <?php }} ?>
                  </select>
                </div>
              </div>

              <!-- PRINT BTN -->
              <div class="col-lg-2">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="button" onclick="printResultSheet()" class="btn btn-primary btn-block">
                    <i class="fa fa-print"></i> Print
                  </button>
                </div>
              </div>
            </div>

            <!-- PRINTABLE AREA -->
            <div id="print-area">
              <!-- report header (shown on print only) -->
              <div class="print-header d-none d-print-block">
                <h4 class="mb-0"><?= esc($campusinfo[0]->campus_name ?? 'School / Campus') ?></h4>
                <p class="mb-0">Students Result Sheet</p>
                <p class="mb-1">
                  Exam: <span id="ph-exam"></span> |
                  Section: <span id="ph-section"></span> |
                  Student Type: <span id="ph-student-type"></span> |
                  Session: <?= esc($academic_session[0]->session_name ?? '') ?>
                </p>
                <hr class="mt-1 mb-2">
              </div>

              <!-- AJAX table container -->
              <div id="students_list_container"></div>
            </div>

          </div><!-- /.tab-content -->
        </div><!-- /.card-body -->
      </div>
    </div>
  </div>
</section>

<!-- AJAX SCRIPT -->
<script>
  $(document).ready(function() {
      // Update hint text when student type changes
      $("#student_type").on('change', function() {
          var type = $(this).val();
          if (type === 'current') {
              $('#student_type_hint').text('Showing only currently active students (status=1)');
          } else {
              $('#student_type_hint').text('Showing students based on selected session (ignores current status)');
          }
          loadResultTable();
      });
      
      // Load on filter change
      $("#cls_sec_id, #eid, #student_type").on('change', function () {
          loadResultTable();
      });
      
      // Initial load if section is selected
      if ($('#cls_sec_id').val() !== '0' && $('#eid').val()) {
          loadResultTable();
      }
  });

  function loadResultTable() {
      const payload = {
          eid: $('#eid').val(),
          session_id: $('#session_id').val(),
          campus_id: $('#campus_id').val(),
          cls_sec_id: $('#cls_sec_id').val(),
          student_type: $('#student_type').val(),
          "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
      };
      
      $("#students_list_container").html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
      
      $.post('<?= base_url("/admin/students-results-list/get-students") ?>', payload, function (res) {
          $("#students_list_container").html(res);
          
          // Update print header placeholders
          $('#ph-exam').text($('#eid option:selected').text());
          $('#ph-section').text($('#cls_sec_id option:selected').text());
          $('#ph-student-type').text($('#student_type option:selected').text());
      }).fail(function() {
          $("#students_list_container").html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
      });
  }

  function printResultSheet(){
      window.print();
  }
</script>

<!-- PRINT CSS -->
<style>
@media screen {
  #print-area {
    overflow-x: auto;
  }
}

@media print {
  .main-sidebar,
  .main-header,
  .main-footer,
  .no-print {
    display: none !important;
  }

  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background: #fff;
  }

  @page {
    size: A4 landscape;
    margin: 10mm 8mm 10mm 8mm;
  }

  #print-area {
    margin: 0;
  }

  .result-table {
    width: 100% !important;
    font-size: 11px;
  }

  .result-table thead {
    display: table-header-group;
  }

  .print-header {
    text-align: center;
  }
}
</style>

<?= $this->endSection() ?>