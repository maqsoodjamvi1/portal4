<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url(); ?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />

<style>

	.student-result-card {
    page-break-inside: avoid;
    margin-bottom: 20px;
    clear: both;
}
.printable-header {
    overflow: hidden;
    border-bottom: 1px solid #000;
    margin-bottom: 10px;
    padding-bottom: 10px;
    clear: both;
}
.printable-header h1 {
    margin: 10px 0;
}

.list-group-item {
    width: 33% !important;
    float: left !important;
    padding: 5px 10px !important;
    border: none;
}
table {
    background-color: transparent;
    border: 2px solid #000;
    margin-top: 10px;
    width: 100%;
}
.table-bordered th, .table-bordered td {
    border: 1px solid #333;
    text-align: center;
    vertical-align: middle;
}
.heading, .heading2 {
    border: 2px solid #000;
    background-color: #800000;
    color: #fff;
    text-align: center;
    font-weight: bold;
    padding: 8px;
    font-size: 18px;
    line-height: 24px;
    width: 100%;
    float: left;
}
.printable-header {
     overflow: hidden;
    border-bottom: 1px solid #000;
    margin-bottom: 10px;
    padding-bottom: 10px;
}
.printable-header img {
    height: 90px;
    width: 90px;
    object-fit: cover;
    border: 1px solid #000;
}
@media print {
	.btn, .btn-primary { display: none !important; }
    .student-result-card { page-break-after: always; }
    body { -webkit-print-color-adjust: exact !important; }
    body {
        -webkit-print-color-adjust: exact;
    }
    .no-print, .no-print * {
        display: none !important;
    }
    .heading, .heading2 {
        background-color: #800000 !important;
    }
}
</style>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Students Results</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#/">Dashboard</a></li>
          <li class="breadcrumb-item active">Students Results</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="#/students_results?m=add">Add Results</a></li>
            <li class="nav-item"><a class="nav-link active" href="#/students_results_card">View Result Cards</a></li>
          </ul>
        </div>
        <div class="card-body">
          <div class="no-print">
            <div class="form-group">
              <label><strong>Select Exams</strong></label>
              <ul class="list-group list-group-horizontal">
                <?php foreach ($exams as $exam): ?>
                  <li class="list-group-item">
                    <div class="icheck-primary d-inline">
                      <input type="checkbox" class="examids" id="eid<?= $exam->eid ?>" name="exam_id" value="<?= $exam->eid ?>">
                      <label for="eid<?= $exam->eid ?>"> <?= $exam->exam_name ?> </label>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div style="text-align: right; margin-bottom: 10px;">
    <button onclick="window.print();" class="btn btn-primary">Export to PDF</button>
</div>

            <div class="row">
              <div class="col-lg-6 form-group">
                <label><strong>Select Class</strong></label>
                <select class="form-control" name="cls_sec_id" id="cls_sec_id">
                  <option value="">All Classes</option>
                  <?php if (!empty($sectionsclassinfo)): ?>
                    <?php foreach ($sectionsclassinfo as $section): ?>
                      <option value="<?= $section['section_id'] ?>"> <?= $section['sectionclassname'] ?> </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <div class="form-group text-end">
              <button type="button" class="btn btn-primary" id="ViewResutlt">View Result Card</button>
            </div>
          </div>

          <div id="loader-1" class="overlay text-center" style="display: none;">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
          </div>

          <div id="resultContainer"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="<?php echo base_url(); ?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script>
$(function() {
  $('#ViewResutlt').click(function() {
    $('#loader-1').show();

    var academic_result = $('.academic_results:checked').map(function() {
      return this.value;
    }).get();

    var examids = $('.examids:checked').map(function() {
      return this.value;
    }).get();

    var cls_sec_id = $('#cls_sec_id').val();

    $.ajax({
      url: 'admin.php?c=students_results_card&m=data',
      type: 'POST',
      data: {
        academic_result: academic_result,
        examids: examids,
        cls_sec_id: cls_sec_id
      },
      success: function(res) {
        $('#resultContainer').html(res);
        $('#loader-1').hide();
      },
      error: function() {
        alert('Failed to load result card. Please try again.');
        $('#loader-1').hide();
      }
    });
  });
});
</script>

<?= $this->endSection() ?>