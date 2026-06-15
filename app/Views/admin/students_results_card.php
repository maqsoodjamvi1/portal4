<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php
/** @var array|object $exams */
/** @var array $sectionsclassinfo */
/** @var object $schoolinfo */

helper('url');
$csrfTokenName  = csrf_token();
$csrfTokenValue = csrf_hash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Students Results</title>
  <meta name="<?= esc($csrfTokenName) ?>" content="<?= esc($csrfTokenValue) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('resource/fontawesome/css/all.min.css') ?>">

  <style>

    /* ========== TABLE CONTAINER FOR HORIZONTAL SCROLL ========== */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.table-responsive table {
    min-width: 800px; /* Adjust based on your content */
    width: 100%;
    margin-bottom: 0;
}

/* ========== PRINT-SPECIFIC TABLE HANDLING ========== */
@media print {
    .table-responsive {
        overflow-x: visible !important;
        overflow: visible !important;
    }
    
    .table-responsive table {
        min-width: 100% !important;
        width: 100% !important;
        table-layout: fixed !important; /* Forces columns to fit within page width */
    }
    
    /* Adjust column widths for print */
    .table th, .table td {
        word-wrap: break-word;
        white-space: normal;
        padding: 4px 2px;
        font-size: 10px; /* Smaller font for print */
    }
    
    /* Make subject column narrower if needed */
    .table th:first-child, .table td:first-child {
        width: 15%;
    }
    
    /* Make data columns narrower */
    .table th:not(:first-child), .table td:not(:first-child) {
        width: auto;
        min-width: 40px;
    }
}
    /* ========== BASE STYLES ========== */
    body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-size:14px;margin:0;padding:0;background:#fff;color:#000}
    h1{font-size:36px;text-align:center;margin-bottom:5px}
    h2{font-size:28px;text-align:center;margin:10px 0}
    .table{width:100%;border-collapse:collapse;margin-top:20px}
    .table th,.table td{border:1px solid #999;padding:6px;text-align:center;font-size:13px}
    .table th{background-color:#004085;color:#fff}

    /* ========== RESULT CARD STYLING ========== */
    .result-card-wrapper{position:relative;min-height:1200px;page-break-after:always;overflow:hidden}
    .result-watermark img{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);opacity:.07;max-width:500px;max-height:500px;z-index:0;filter:grayscale(100%);-webkit-print-color-adjust:exact;print-color-adjust:exact;pointer-events:none}
    .student-result-card{position:relative;z-index:2;padding:15px;border:1px solid #ccc;border-radius:8px;background-color:#fdfdfd;page-break-inside:avoid}
    .printable-header{overflow:hidden;border-bottom:1px solid #000;margin-bottom:10px;padding-bottom:10px}
    .printable-header img{width:100px;height:auto;object-fit:contain}

    /* ========== COLORS / HEADINGS ========== */
    .bg-subject{background-color:#004085;color:#fff}
    .bg-exam-title{background-color:#17a2b8;color:#000}
    .bg-exam-sub,.bg-exam-sub-one{background-color:#e9ecef;color:#000;text-align:left}
    .heading,.heading2{background-color:#800000;color:#fff;text-align:center;font-weight:700;padding:8px;font-size:18px;line-height:24px;border:2px solid #000;margin-top:20px}

    /* ========== SIGNATURE & FOOTER ========== */
    .signature-section{margin-top:50px;display:flex;justify-content:space-between;font-size:14px;padding:0 20px}
    .signature-box{text-align:center;width:45%}
    .signature-line{margin-top:40px;border-top:1px solid #000}
    .footer-note{font-size:12px;color:#777;text-align:center;margin-top:30px}

    /* ========== PRINT STYLES ========== */
    @media print{
      body{margin:0;padding:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}
      @page{size:A4;margin:10mm}
      .table th,.table td,.heading,.heading2,.student-result-card,.printable-header,.signature-section{-webkit-print-color-adjust:exact;print-color-adjust:exact}
      .result-watermark img{opacity:.07!important}
      .no-print,.no-print *{display:none!important}
      .btn,.form-group,select,label,input{display:none!important}
    }

    /* ========== TOGGLE PANEL (Screen Only) ========== */
    .column-toggle-container{background:#f8f9fa;padding:15px;border-radius:5px;margin-bottom:20px;border:1px solid #dee2e6}

    /* minor utilities */
    .overlay{position:fixed;inset:0;background:rgba(255,255,255,.6);display:flex;align-items:center;justify-content:center;z-index:9999}
  </style>
</head>
<body>

<?= view('components/page_header', [
    'title' => 'Students Results',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students Results', 'active' => true],
    ],
]) ?>


<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students-results') ?>">Add Results</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students-results-card') ?>">View Result Cards</a></li>
          </ul>
        </div>

        <div class="card-body">
          <div class="filters-panel no-print">
            <!-- Exams -->
            <div class="form-group">
              <label><strong>Select Exams</strong></label>
              <ul class="list-group list-group-horizontal flex-wrap">
                <?php if (!empty($exams)): foreach ($exams as $exam): ?>
                  <li class="list-group-item">
                    <div class="icheck-primary d-inline">
                      <input type="checkbox" class="examids" id="eid<?= esc($exam->eid) ?>" value="<?= esc($exam->eid) ?>">
                      <label for="eid<?= esc($exam->eid) ?>"> <?= esc($exam->exam_name) ?> </label>
                    </div>
                  </li>
                <?php endforeach; endif; ?>
              </ul>
            </div>

            <!-- Column Toggles -->
            <div class="column-toggle-container">
              <label><strong>Show Columns:</strong></label>
              <div class="row">
                <div class="col-md-4">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showMarks" checked>
                    <label for="showMarks">Marks (Obt/Total)</label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showPercentage" checked>
                    <label for="showPercentage">Percentage</label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showGrades" checked>
                    <label for="showGrades">Grades</label>
                  </div>
                </div>
                <div class="col-md-4 mt-2">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showAttendance" checked>
                    <label for="showAttendance">Show Attendance</label>
                  </div>
                </div>
                <div class="col-md-4 mt-2">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showPosition" checked>
                    <label for="showPosition">Show Position</label>
                  </div>
                </div>
                <div class="col-md-4 mt-2">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showSignatureLine" checked>
                    <label for="showSignatureLine">Show Signature Line</label>
                  </div>
                </div>
                <div class="col-md-4 mt-2">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showCampus" checked>
                    <label for="showCampus">Show Campus</label>
                  </div>
                </div>
                <div class="col-md-4 mt-2">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showWebsite" checked>
                    <label for="showWebsite">Show Website</label>
                  </div>
                </div>
                <div class="col-md-4 mt-2">
                  <div class="icheck-primary d-inline">
                    <input type="checkbox" id="showLocation" checked>
                    <label for="showLocation">Show Location</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Add this after the class selection dropdown -->
<div class="row">
  <div class="col-lg-6 form-group">
    <div class="form-check">
      <input type="checkbox" class="form-check-input" id="showAllStudents" name="showAllStudents">
      <label class="form-check-label" for="showAllStudents"><strong>Show All Students</strong></label>
      <small class="form-text text-muted">If checked, shows students with status 1 or 4. Otherwise shows only status 1.</small>
    </div>
  </div>
</div>

            <!-- Extra Options -->
            <div class="row mt-3">
              <div class="col-md-4 form-group">
                <label for="rowHeight">Row Height (px):</label>
                <input type="number" id="rowHeight" name="rowHeight" value="30" min="20" max="100" class="form-control" style="width:120px;">
              </div>
              <div class="col-md-4 form-group align-self-center">
                <div class="icheck-primary d-inline">
                  <input type="checkbox" id="useShortName" name="useShortName" value="1">
                  <label for="useShortName">Use Short Subject Name</label>
                </div>
              </div>
            </div>

            <!-- Class Selection (IMPORTANT: value must be cls_sec_id) -->
            <div class="row">
              <div class="col-lg-6 form-group">
                <label><strong>Select Class</strong></label>
                <select class="form-control" name="cls_sec_id" id="cls_sec_id">
                  <option value="">Select Class-Section</option>
                  <?php if (!empty($sectionsclassinfo)): ?>
                    <?php foreach ($sectionsclassinfo as $row):
                      // Expected keys: cls_sec_id, class_name/class_short_name, section_name
                      $classLabel = !empty($row['class_short_name'] ?? '') ? $row['class_short_name'] : ($row['class_name'] ?? '');
                      if (preg_match('/^\d+$/', (string)$classLabel)) { $classLabel = 'Grade '.$classLabel; }
                      $display = trim($classLabel.' - '.($row['section_name'] ?? ''));
                    ?>
                      <option value="<?= esc($row['cls_sec_id']) ?>"><?= esc($display) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <small class="no-print text-danger">🔧 Before printing or exporting to PDF, enable “Background graphics” in the print dialog.</small>

            <!-- Actions -->
            <div class="form-group text-end mt-2">
              <button type="button" class="btn btn-primary" id="ViewResult">View Result Card</button>
              <button type="button" class="btn btn-success" onclick="window.print();">Export to PDF</button>
            </div>
          </div><!-- /filters-panel -->

          <div id="loader-1" class="overlay text-center" style="display:none;">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
          </div>

          <div id="resultContainer"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- jQuery (assumed in layout; include if needed) -->
<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script>
(function () {
  function csrf() {
    var meta = document.querySelector('meta[name="<?= esc($csrfTokenName) ?>"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function selectedExamIds() {
    var ids = [];
    document.querySelectorAll('.examids:checked').forEach(function (el) {
      ids.push(el.value);
    });
    return ids;
  }

  document.getElementById('ViewResult').addEventListener('click', function () {
    var clsSecId = document.getElementById('cls_sec_id').value;
    var exams    = selectedExamIds();
    var showAllStudents = document.getElementById('showAllStudents').checked ? '1' : '0';

    if (!clsSecId) {
      alert('Please select a Class-Section.');
      return;
    }
    if (exams.length === 0) {
      alert('Please select at least one exam.');
      return;
    }

    var payload = new FormData();
    payload.append('<?= esc($csrfTokenName) ?>', csrf());

payload.append('showAllStudents', showAllStudents);
    payload.append('cls_sec_id', clsSecId);
    exams.forEach(function (id) { payload.append('examids[]', id); });

    // Flags
    payload.append('showMarks',        document.getElementById('showMarks').checked ? '1' : '0');
    payload.append('showPercentage',   document.getElementById('showPercentage').checked ? '1' : '0');
    payload.append('showGrades',       document.getElementById('showGrades').checked ? '1' : '0');
    payload.append('showAttendance',   document.getElementById('showAttendance').checked ? '1' : '0');
    payload.append('showPosition',     document.getElementById('showPosition').checked ? '1' : '0');
    payload.append('showSignatureLine',document.getElementById('showSignatureLine').checked ? '1' : '0');
    payload.append('showCampus',       document.getElementById('showCampus').checked ? '1' : '0');
    payload.append('showWebsite',      document.getElementById('showWebsite').checked ? '1' : '0');
    payload.append('showLocation',     document.getElementById('showLocation').checked ? '1' : '0');

    // Options
    payload.append('rowHeight',  document.getElementById('rowHeight').value || '30');
    payload.append('useShortName', document.getElementById('useShortName').checked ? '1' : '0');

    // Optional debug:
    // payload.append('debug', '1');

    var loader = document.getElementById('loader-1');
    loader.style.display = 'flex';

    fetch('<?= base_url('admin/students-results-card/data') ?>', {
      method: 'POST',
      body: payload,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (r) { return r.text(); })
    .then(function (html) {
      document.getElementById('resultContainer').innerHTML = html;
    })
    .catch(function () {
      alert('Failed to load result card. Please try again.');
    })
    .finally(function () {
      loader.style.display = 'none';
    });
  });
})();


</script>

</body>
</html>
<?= $this->endSection() ?>