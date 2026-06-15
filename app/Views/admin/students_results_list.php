<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="no-print">
<?= view('components/page_header', [
    'title' => 'Students Results List',
    'icon' => 'fas fa-list-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students Results List', 'active' => true],
    ],
]) ?>
</div>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card sms-card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0 no-print">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_results_list') ?>">Student Results List</a></li>
          </ul>
        </div>

        <div class="card-body">
          <div class="tab-content">
            <!-- filters (session: use top bar #sessionID) -->
            <input type="hidden" name="session_id" id="session_id" value="<?= (int) ($sessionData['sessionid'] ?? session('member_sessionid')) ?>">
            <input type="hidden" name="campus_id" id="campus_id" value="<?= $sessionData['campusid'] ?? '' ?>">

            <div class="row no-print">
              <div class="col-lg-2">
                <div class="form-group">
                  <label for="active_only" class="d-block">Students</label>
                  <div class="form-check form-check">
                    <input type="checkbox" class="form-check-input" id="active_only" name="active_only" value="1">
                    <label class="form-check-label" for="active_only">Active students only</label>
                  </div>
                  <small class="text-muted" id="active_only_hint">All students in this session</small>
                </div>
              </div>

              <div class="col-lg-2">
                <div class="form-group">
                  <label for="term">Exam</label>
                  <select name="eid" id="eid" class="form-control">
                    <?php foreach ($examinfo as $exam) { ?>
                      <option value="<?= $exam->eid; ?>"><?= $exam->exam_name; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="form-group float-start">
                  <label for="class">Sections</label>
                  <select class="form-control select2" name="cls_sec_id" id="cls_sec_id">
                    <option value="0">Select Section</option>
                    <?php if (isset($sectionsclassinfo)) {
                      foreach ($sectionsclassinfo as $row) { ?>
                        <option value="<?= esc($row['cls_sec_id']) ?>">
                          <?= esc($row['sectionclassname']) ?>
                        </option>
                      <?php }
                    } ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-2">
                <div class="form-group">
                  <label for="order_by">Order By</label>
                  <select name="order_by" id="order_by" class="form-control">
                    <option value="name">Name (A-Z)</option>
                    <option value="reg_no">Reg No</option>
                  </select>
                </div>
              </div>

              <div class="col-lg-1">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="button" onclick="printResultSheet()" class="btn btn-primary w-100">
                    <i class="fa fa-print"></i> Print
                  </button>
                </div>
              </div>
            </div>

            <!-- PRINTABLE AREA (A4 portrait) -->
            <div id="print-area" class="srl-print-portrait">
              <div id="srl-screen-header" class="srl-screen-header no-print d-none">
                <h4 class="srl-campus-name mb-1"><?= esc($campusinfo[0]->campus_name ?? 'School / Campus') ?></h4>
                <p class="mb-0 fw-bold">Students Result Sheet</p>
                <p class="srl-meta-line mb-2 text-muted small">
                  Exam: <span id="sh-exam"></span> |
                  Section: <span id="sh-section"></span> |
                  Students: <span id="sh-student-type"></span> |
                  Order: <span id="sh-order-by"></span> |
                  Session: <span id="sh-session-name"><?= esc($academic_session[0]->session_name ?? '') ?></span>
                </p>
              </div>

              <div class="print-header d-none d-print-block">
                <h4 class="mb-0"><?= esc($campusinfo[0]->campus_name ?? 'School / Campus') ?></h4>
                <p class="mb-0">Students Result Sheet</p>
                <p class="mb-1">
                  Exam: <span id="ph-exam"></span> |
                  Section: <span id="ph-section"></span> |
                  Students: <span id="ph-student-type"></span> |
                  Order: <span id="ph-order-by"></span> |
                  Session: <span id="ph-session-name"><?= esc($academic_session[0]->session_name ?? '') ?></span>
                </p>
                <hr class="mt-1 mb-2">
              </div>

              <div id="students_list_container"></div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  $(document).ready(function() {
      syncSessionFromHeader();

      $('#active_only').on('change', function() {
          $('#active_only_hint').text(
              $(this).is(':checked')
                  ? 'Only students with status = 1 (active)'
                  : 'All students enrolled in this session'
          );
          loadResultTable();
      });

      $("#cls_sec_id, #eid, #order_by").on('change', function () {
          loadResultTable();
      });

      if ($('#cls_sec_id').val() !== '0' && $('#eid').val()) {
          loadResultTable();
      }
  });

  function syncSessionFromHeader() {
      var headerSession = $('#sessionID').val();
      if (headerSession) {
          $('#session_id').val(headerSession);
      }
  }

  function currentSessionLabel() {
      var $header = $('#sessionID option:selected');
      return $header.length ? $header.text().trim() : '';
  }

  function loadResultTable() {
      syncSessionFromHeader();

      const payload = {
          eid: $('#eid').val(),
          session_id: $('#session_id').val(),
          campus_id: $('#campus_id').val(),
          cls_sec_id: $('#cls_sec_id').val(),
          active_only: $('#active_only').is(':checked') ? '1' : '0',
          order_by: $('#order_by').val(),
          "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
      };

      $("#students_list_container").html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');

      $.post('<?= base_url("/admin/students-results-list/get-students") ?>', payload, function (res) {
          $("#students_list_container").html(res);
          updateReportHeaders();
      }).fail(function() {
          $("#students_list_container").html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
          $('#srl-screen-header').addClass('d-none');
      });
  }

  function updateReportHeaders() {
      const exam = $('#eid option:selected').text();
      const section = $('#cls_sec_id option:selected').text();
      const orderBy = $('#order_by option:selected').text();

      $('#ph-exam, #sh-exam').text(exam);
      $('#ph-section, #sh-section').text(section);
      $('#ph-order-by, #sh-order-by').text(orderBy);
      $('#sh-session-name, #ph-session-name').text(currentSessionLabel());

      const activeLabel = $('#active_only').is(':checked') ? 'Active only' : 'All in session';
      $('#ph-student-type, #sh-student-type').text(activeLabel);

      const hasTable = $('#students_list_container .result-table').length > 0;
      $('#srl-screen-header').toggleClass('d-none', !hasTable);
  }

  function printResultSheet() {
      window.print();
  }
</script>

<style>
@media screen {
  #print-area {
    overflow-x: auto;
  }

  .srl-screen-header {
    text-align: center;
    padding: 14px 12px 10px;
    margin-bottom: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
  }

  .srl-screen-header .srl-campus-name {
    font-size: 1.35rem;
    font-weight: 700;
    color: #1e293b;
  }

  .srl-screen-header .srl-meta-line {
    line-height: 1.5;
  }

  #students_list_container .srl-report-sheet {
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    border-radius: 4px;
    overflow: hidden;
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
    size: A4 portrait;
    margin: 8mm 6mm;
  }

  #print-area,
  .srl-print-portrait {
    margin: 0;
    width: 100%;
  }

  .print-header {
    text-align: center;
    margin-bottom: 4px;
  }

  .print-header h4 {
    font-size: 14pt;
    margin-bottom: 2px;
  }

  .print-header p {
    font-size: 9pt;
    margin-bottom: 2px;
  }

  .content-wrapper,
  .content {
    margin: 0 !important;
    padding: 0 !important;
  }
}
</style>

<?= $this->endSection() ?>
