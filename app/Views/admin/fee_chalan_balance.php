<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  helper('form');
  // Default: today (DD/MM/YYYY) – adjust if your backend expects another format
  $today = date('d/m/Y');
?>
<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-7">
        <h1 class="mb-0">Balance</h1>
        <small class="text-muted">Professional balance report with print/export</small>
      </div>
      <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Balance</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <!-- FILTERS -->
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filters</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-outline-secondary" id="btnToday">Today</button>
          <button class="btn btn-sm btn-outline-secondary" id="btnThisMonth">This Month</button>
          <button class="btn btn-sm btn-outline-secondary" id="btnLastMonth">Last Month</button>
          <button class="btn btn-sm btn-outline-secondary" id="btnYTD">YTD</button>
        </div>
      </div>
      <div class="card-body">
        <form id="filterForm" class="form-row">
          <div class="form-group col-md-4">
            <label class="mb-1">Date Paid From</label>
            <div class="input-group date" id="datepicker_from" data-target-input="nearest">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
              </div>
              <input type="text" class="form-control datetimepicker-input" data-target="#datepicker_from"
                     name="paid_date_from" id="paid_date_from" value="<?= esc($today) ?>" required>
            </div>
            <small class="text-muted">Format: DD/MM/YYYY</small>
          </div>

          <div class="form-group col-md-4">
            <label class="mb-1">Date Paid To</label>
            <div class="input-group date" id="datepicker_to" data-target-input="nearest">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
              </div>
              <input type="text" class="form-control datetimepicker-input" data-target="#datepicker_to"
                     name="paid_date_to" id="paid_date_to" value="<?= esc($today) ?>" required>
            </div>
            <small class="text-muted">Must be the same or after “From”.</small>
          </div>

          <div class="form-group col-md-4 d-flex align-items-end">
            <div>
              <button type="button" id="btnView" class="btn btn-primary mr-2">
                <i class="fas fa-search mr-1"></i> View
              </button>
              <button type="button" id="btnReset" class="btn btn-default">
                <i class="fas fa-undo mr-1"></i> Reset
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- REPORT + ACTIONS -->
    <div class="card shadow-sm" id="reportCard">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">
          <i class="fas fa-file-invoice-dollar mr-1"></i> Balance Report
        </h3>
        <div>
          <button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="btnPrint">
            <i class="fas fa-print mr-1"></i> Print
          </button>
          <!-- If you add an export endpoint, wire it here -->
          <!-- <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExportCsv">
            <i class="fas fa-file-csv mr-1"></i> CSV
          </button> -->
        </div>
      </div>

      <!-- PRINT HEADER (hidden on screen, shown on print) -->
      <div id="printHeader" class="p-3 border-bottom d-none">
        <div class="d-flex justify-content-between flex-wrap">
          <div>
            <h4 class="mb-1">Balance Report</h4>
            <div class="text-muted small">
              Filter Range: <span id="phRange">—</span>
            </div>
          </div>
          <div class="text-right">
            <div class="small text-muted">Printed on: <span id="phDate">—</span></div>
          </div>
        </div>
      </div>

      <div class="card-body">
        <!-- Summary tiles (optional; you can also render these from your AJAX partial) -->
        <div id="summaryTiles" class="row d-none">
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box">
              <span class="info-box-icon bg-success"><i class="fas fa-arrow-down"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Total Received</span>
                <span class="info-box-number" id="tileReceived">—</span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box">
              <span class="info-box-icon bg-danger"><i class="fas fa-arrow-up"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Total Outstanding</span>
                <span class="info-box-number" id="tileOutstanding">—</span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box">
              <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Students Paid</span>
                <span class="info-box-number" id="tilePaidStudents">—</span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="info-box">
              <span class="info-box-icon bg-warning"><i class="fas fa-user-clock"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Students Pending</span>
                <span class="info-box-number" id="tilePendingStudents">—</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Results -->
        <div id="resultsArea" class="table-responsive">
          <div class="text-center text-muted py-5" id="placeholder">
            Select a date range and click <strong>View</strong> to see the report.
          </div>
          <div id="totalfeeinfo"></div>
        </div>
      </div>
    </div>

  </div>

  <!-- LOADER -->
  <div id="loader-1" style="display:none;position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:9999;background:rgba(255,255,255,.6);">
    <div style="position:absolute;top:45%;left:50%;transform:translate(-50%,-50%);">
      <div class="spinner-border text-primary" role="status"></div>
      <div class="mt-2">Loading…</div>
    </div>
  </div>
</section>

<style>
/* Print: show print header, hide nav/filters/buttons */
@media print {
  .main-header, .main-sidebar, .main-footer, .content-header,
  .card-header .btn, .card-header .card-tools,
  #filterForm, .card-header .breadcrumb, .breadcrumb,
  #placeholder { display: none !important; }

  #reportCard { border: 0 !important; box-shadow: none !important; }
  #printHeader { display: block !important; }
  .content-wrapper { margin: 0 !important; }
  .table { font-size: 12px; }
}
</style>

<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script src="<?= base_url('resource/js/jquery.autocomplete.js') ?>"></script>
<script>
(function(){
  const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };

  function karachiNow() {
    try { return new Date().toLocaleString('en-GB', { timeZone: 'Asia/Karachi' }); }
    catch(e){ return new Date().toLocaleString(); }
  }

  function formatRangeLabel(from, to) {
    return (from || '—') + '  to  ' + (to || '—');
  }

  function parseDMY(s) {
    if (!s) return null;
    const m = String(s).match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!m) return null;
    const d = new Date(+m[3], (+m[2])-1, +m[1], 12, 0, 0);
    return isNaN(d.getTime()) ? null : d;
  }

  function setPicker($el, d) {
    const dd = ('0'+d.getDate()).slice(-2);
    const mm = ('0'+(d.getMonth()+1)).slice(-2);
    const yy = d.getFullYear();
    $el.val(`${dd}/${mm}/${yy}`);
  }

  // Quick range buttons
  $('#btnToday').on('click', function(){
    const d = new Date();
    setPicker($('#paid_date_from'), d);
    setPicker($('#paid_date_to'), d);
  });
  $('#btnThisMonth').on('click', function(){
    const d = new Date();
    const from = new Date(d.getFullYear(), d.getMonth(), 1);
    const to   = new Date(d.getFullYear(), d.getMonth()+1, 0);
    setPicker($('#paid_date_from'), from);
    setPicker($('#paid_date_to'), to);
  });
  $('#btnLastMonth').on('click', function(){
    const d = new Date();
    const from = new Date(d.getFullYear(), d.getMonth()-1, 1);
    const to   = new Date(d.getFullYear(), d.getMonth(), 0);
    setPicker($('#paid_date_from'), from);
    setPicker($('#paid_date_to'), to);
  });
  $('#btnYTD').on('click', function(){
    const d = new Date();
    const from = new Date(d.getFullYear(), 0, 1);
    const to   = new Date();
    setPicker($('#paid_date_from'), from);
    setPicker($('#paid_date_to'), to);
  });

  // Init pickers (keeps your original format)
  $(function(){
    $('#datepicker_from').datetimepicker({ format: 'DD/MM/YYYY' });
    $('#datepicker_to').datetimepicker({ format: 'DD/MM/YYYY' });
  });

  function showLoader(on) { $('#loader-1').toggle(!!on); }

  function validateRange() {
    const f = $('#paid_date_from').val().trim();
    const t = $('#paid_date_to').val().trim();
    const fd = parseDMY(f), td = parseDMY(t);
    if (!fd || !td) { toastr.error('Please enter valid dates (DD/MM/YYYY).'); return false; }
    if (fd > td) { toastr.error('“Date Paid From” must be on or before “Date Paid To”.'); return false; }
    return true;
  }

  function updatePrintHeader(from, to) {
    $('#phDate').text(karachiNow());
    $('#phRange').text(formatRangeLabel(from, to));
  }

  function clearResults() {
    $('#totalfeeinfo').empty();
    $('#summaryTiles').addClass('d-none');
    $('#placeholder').removeClass('d-none');
  }

  // Load report by date range
  function loadReport() {
    if (!validateRange()) return;
    const paid_date_from = $('#paid_date_from').val();
    const paid_date_to   = $('#paid_date_to').val();

    showLoader(true);
    $('#placeholder').addClass('d-none');

    $.ajax({
      url: '<?= base_url('admin/fee-chalan-balance/get-total-fee') ?>',
      type: 'POST',
      data: {
        paid_date_from,
        paid_date_to,
        [CSRF.name]: CSRF.hash
      },
      success: function(res) {
        $('#totalfeeinfo').html(res || '<div class="text-center text-muted py-3">No data.</div>');
        updatePrintHeader(paid_date_from, paid_date_to);

        // OPTIONAL: If your response includes totals in data-* attributes, show tiles:
        // Example (adapt to your partial): <div id="reportRoot" data-received="..." data-outstanding="..." ...>
        const $root = $('#totalfeeinfo').find('#reportRoot');
        if ($root.length) {
          $('#tileReceived').text($root.data('received') ?? '—');
          $('#tileOutstanding').text($root.data('outstanding') ?? '—');
          $('#tilePaidStudents').text($root.data('paid') ?? '—');
          $('#tilePendingStudents').text($root.data('pending') ?? '—');
          $('#summaryTiles').removeClass('d-none');
        } else {
          $('#summaryTiles').addClass('d-none');
        }
      },
      error: function(xhr) {
        $('#totalfeeinfo').html('<div class="text-center text-danger py-3">Failed to load the report.</div>');
        console.error(xhr.responseText);
      },
      complete: function() {
        showLoader(false);
      }
    });
  }

  // Buttons
  $('#btnView').on('click', loadReport);
  $('#btnReset').on('click', function(){
    $('#paid_date_from').val('<?= esc($today) ?>');
    $('#paid_date_to').val('<?= esc($today) ?>');
    clearResults();
  });

  // Print
  $('#btnPrint').on('click', function(){
    // Ensure header has up-to-date info
    updatePrintHeader($('#paid_date_from').val(), $('#paid_date_to').val());
    window.print();
  });

})();
</script>

<?= $this->endSection() ?>
