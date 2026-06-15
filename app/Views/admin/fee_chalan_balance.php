<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  helper('form');
  $schoolPrintName = isset($schoolinfo->system_name) && $schoolinfo->system_name !== ''
    ? $schoolinfo->system_name
    : 'School';
  // Default: today (DD/MM/YYYY) – adjust if your backend expects another format
  $today = date('d/m/Y');
?>
<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />

<div class="no-print">
<?= view('components/page_header', [
    'title' => 'Balance',
    'icon' => 'fas fa-balance-scale',
    'subtitle' => 'Collections grouped by paid date: each day shows the date, day total, then payer details.',
    'actionsHtml' => '<div class="text-sm-right">'
        . '<a href="' . esc(base_url('admin/fee-chalan-daily-collection'), 'attr') . '" class="btn btn-sm btn-outline-secondary">'
        . '<i class="fas fa-calendar-day me-1"></i> Daily collection summary</a></div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Balance', 'active' => true],
    ],
]) ?>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- FILTERS -->
    <div class="card sms-card card-primary card-outline shadow-sm no-print">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-outline-secondary" id="btnToday">Today</button>
          <button class="btn btn-sm btn-outline-secondary" id="btnThisMonth">This Month</button>
          <button class="btn btn-sm btn-outline-secondary" id="btnLastMonth">Last Month</button>
          <button class="btn btn-sm btn-outline-secondary" id="btnYTD">YTD</button>
        </div>
      </div>
      <div class="card-body">
        <form id="filterForm" class="row">
          <div class="form-group col-md-4">
            <label class="mb-1">Date Paid From</label>
            <div class="input-group date" id="datepicker_from" data-target-input="nearest">
              <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
              <input type="text" class="form-control"
                     name="paid_date_from" id="paid_date_from" value="<?= esc($today) ?>" required>
            </div>
            <small class="text-muted">Format: DD/MM/YYYY</small>
          </div>

          <div class="form-group col-md-4">
            <label class="mb-1">Date Paid To</label>
            <div class="input-group date" id="datepicker_to" data-target-input="nearest">
              <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
              <input type="text" class="form-control"
                     name="paid_date_to" id="paid_date_to" value="<?= esc($today) ?>" required>
            </div>
            <small class="text-muted">Must be the same or after “From”.</small>
          </div>

          <div class="form-group col-md-4 d-flex align-items-end">
            <div>
              <button type="button" id="btnView" class="btn btn-primary me-2">
                <i class="fas fa-search me-1"></i> View
              </button>
              <button type="button" id="btnReset" class="btn btn-secondary">
                <i class="fas fa-undo me-1"></i> Reset
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- REPORT + ACTIONS -->
    <div class="card shadow-sm" id="reportCard">
      <div class="card-header d-flex justify-content-between align-items-center no-print">
        <h3 class="card-title m-0">
          <i class="fas fa-file-invoice-dollar me-1"></i> Balance Report
        </h3>
        <div>
          <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="btnPrint">
            <i class="fas fa-print me-1"></i> Print
          </button>
          <!-- If you add an export endpoint, wire it here -->
          <!-- <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExportCsv">
            <i class="fas fa-file-csv me-1"></i> CSV
          </button> -->
        </div>
      </div>

      <!-- PRINT HEADER (hidden on screen, solid banner on print / A4) -->
      <div id="printHeader" class="fee-balance-print-banner d-none" aria-hidden="true">
        <div class="fee-balance-print-banner__inner">
          <div class="fee-balance-print-banner__school"><?= esc($schoolPrintName) ?></div>
          <div class="fee-balance-print-banner__title">Daily fee collections (by paid date)</div>
          <div class="fee-balance-print-banner__meta">
            <span>Date paid (from – to): <strong id="phRange">—</strong></span>
            <span class="fee-balance-print-banner__meta-sep"> · </span>
            <span>Printed: <strong id="phDate">—</strong></span>
          </div>
        </div>
      </div>

      <div class="card-body">
        <!-- Summary tiles (optional; you can also render these from your AJAX partial) -->
        <div id="summaryTiles" class="row d-none no-print">
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
/* Screen: keep print banner out of layout flow */
.fee-balance-print-banner { display: none !important; }

.fee-balance-totals-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px 20px;
  align-items: stretch;
  padding: 12px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
}

@media (max-width: 520px) {
  .fee-balance-totals-row {
    grid-template-columns: 1fr;
  }
}

.fee-balance-total-item {
  min-width: 0;
  padding: 4px 8px;
  border-end: 1px solid #e2e8f0;
}

.fee-balance-total-item:last-child,
.fee-balance-total-item--balance {
  border-end: 0;
  text-align: right;
}

@media (max-width: 520px) {
  .fee-balance-total-item,
  .fee-balance-total-item--balance {
    border-end: 0;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 10px;
  }

  .fee-balance-total-item:last-child {
    border-bottom: 0;
    padding-bottom: 4px;
  }
}

.fee-balance-totals .fee-balance-total-label {
  display: block;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  margin-bottom: 4px;
}

.fee-balance-totals .fee-balance-total-value {
  display: block;
  font-size: 1.15rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
}

.fee-balance-total-item--balance .fee-balance-total-value {
  color: #9f1239;
}

.fee-balance-day-block {
  border-start: 4px solid #3c8dbc;
  padding-left: 12px;
}

.fee-balance-day-head-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 8px 16px;
  margin-bottom: 10px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e2e8f0;
}

.fee-balance-day-heading {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1a365d;
  margin: 0;
  flex: 1 1 auto;
  min-width: 0;
}

.fee-balance-day-balance {
  flex: 0 1 auto;
  text-align: right;
  white-space: nowrap;
}

@media (max-width: 520px) {
  .fee-balance-day-balance {
    text-align: left;
    white-space: normal;
    width: 100%;
  }
}

.fee-balance-day-total-label {
  display: inline-block;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  margin-right: 8px;
  vertical-align: middle;
}

.fee-balance-day-total-value {
  display: inline-block;
  font-size: 1.1rem;
  font-weight: 700;
  color: #155724;
  vertical-align: middle;
}

/* Print: A4, margins, solid heading, table */
@media print {
  @page {
    size: A4;
    margin: 12mm 14mm 14mm 14mm;
  }

  html,
  body {
    height: auto !important;
    background: #fff !important;
    color: #111 !important;
  }

  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .no-print,
  .main-header,
  .main-sidebar,
  .main-footer,
  .content-header,
  .card-header .btn,
  .card-header .card-tools,
  #filterForm,
  .card-header .breadcrumb,
  .breadcrumb,
  #placeholder,
  #loader-1,
  .control-sidebar,
  .preloader {
    display: none !important;
  }

  .content-wrapper,
  .main-footer,
  .wrapper {
    margin: 0 !important;
    padding: 0 !important;
  }

  .content-wrapper > .content {
    padding: 0 !important;
  }

  .content .container-fluid {
    padding: 0 !important;
    max-width: 100% !important;
  }

  #reportCard {
    border: 0 !important;
    box-shadow: none !important;
    margin: 0 !important;
  }

  #reportCard .card-body {
    padding: 0 !important;
  }

  #printHeader.fee-balance-print-banner {
    display: block !important;
    margin: 0 0 10pt 0 !important;
    padding: 0 !important;
    border: 0 !important;
    page-break-after: avoid;
  }

  .fee-balance-print-banner__inner {
    background: #0f172a !important;
    color: #fff !important;
    padding: 12pt 14pt;
    border-radius: 0;
  }

  .fee-balance-print-banner__school {
    font-size: 11pt;
    font-weight: 700;
    letter-spacing: 0.02em;
    margin-bottom: 4pt;
  }

  .fee-balance-print-banner__title {
    font-size: 13pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 6pt;
    border-bottom: 2pt solid #38bdf8 !important;
    padding-bottom: 5pt;
  }

  .fee-balance-print-banner__meta {
    font-size: 9pt;
    opacity: 0.95;
  }

  .fee-balance-print-banner__meta-sep {
    opacity: 0.6;
  }

  .fee-balance-report-inner {
    font-size: 9.5pt;
  }

  .fee-balance-totals {
    margin-bottom: 8pt !important;
    page-break-after: avoid;
  }

  .fee-balance-totals-row {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 8pt 12pt !important;
    padding: 8pt 10pt !important;
    background: #f1f5f9 !important;
    border: 0.5pt solid #cbd5e1 !important;
    border-radius: 0 !important;
  }

  .fee-balance-total-item {
    border-end: 0.5pt solid #cbd5e1 !important;
    padding: 4pt 8pt !important;
    text-align: left !important;
  }

  .fee-balance-total-item--balance {
    border-end: 0 !important;
    text-align: right !important;
  }

  .fee-balance-totals .fee-balance-total-label {
    color: #334155 !important;
    font-size: 7pt !important;
    margin-bottom: 2pt !important;
  }

  .fee-balance-totals .fee-balance-total-value {
    font-size: 10.5pt !important;
    font-weight: 700 !important;
  }

  .fee-balance-total-item--balance .fee-balance-total-value {
    color: #9f1239 !important;
  }

  .fee-balance-campus-line {
    color: #555 !important;
  }

  .fee-balance-day-block {
    border-start: 3pt solid #0ea5e9 !important;
    padding-left: 10pt !important;
    margin-bottom: 12pt !important;
    page-break-inside: auto;
  }

  .fee-balance-day-head-row {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 4pt 10pt !important;
    margin-bottom: 6pt !important;
    padding-bottom: 5pt !important;
    border-bottom: 0.5pt solid #cbd5e1 !important;
    page-break-after: avoid;
  }

  .fee-balance-day-heading {
    font-size: 10.5pt !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    margin: 0 !important;
    flex: 1 1 55% !important;
    min-width: 0 !important;
  }

  .fee-balance-day-balance {
    flex: 0 1 auto !important;
    text-align: right !important;
    margin-left: auto !important;
  }

  .fee-balance-day-total-label {
    color: #475569 !important;
    font-size: 7.5pt !important;
    margin-right: 6pt !important;
  }

  .fee-balance-day-total-value {
    font-size: 10.5pt !important;
    font-weight: 700 !important;
    color: #14532d !important;
  }

  .fee-balance-table-wrap {
    overflow: visible !important;
  }

  .fee-balance-print-table {
    width: 100% !important;
    font-size: 9pt !important;
    border-collapse: collapse !important;
    page-break-inside: auto;
  }

  .fee-balance-print-table thead {
    display: table-header-group;
  }

  .fee-balance-print-table th {
    background: #1e293b !important;
    color: #fff !important;
    border: 1pt solid #0f172a !important;
    padding: 5pt 6pt !important;
    font-weight: 600;
    vertical-align: middle;
  }

  .fee-balance-print-table td {
    border: 0.5pt solid #cbd5e1 !important;
    padding: 4pt 6pt !important;
    vertical-align: top;
  }

  .fee-balance-print-table tbody tr {
    page-break-inside: avoid;
  }

  .fee-balance-print-table tbody tr:nth-child(even) td {
    background: #f8fafc !important;
  }

  #resultsArea {
    overflow: visible !important;
  }

  .table {
    font-size: inherit;
  }

  a[href]:after {
    content: none !important;
  }
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

  function parseISO(s) {
    if (!s) return null;
    const m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return null;
    const d = new Date(+m[1], (+m[2]) - 1, +m[3], 12, 0, 0);
    return isNaN(d.getTime()) ? null : d;
  }

  function parseInputDate(s) {
    return parseDMY(s) || parseISO(s);
  }

  function toDMY(s) {
    const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return s || '';
    return `${m[3]}/${m[2]}/${m[1]}`;
  }

  function setPicker($el, d) {
    const dd = ('0'+d.getDate()).slice(-2);
    const mm = ('0'+(d.getMonth()+1)).slice(-2);
    const yy = d.getFullYear();
    if ($el.attr('type') === 'date') {
      $el.val(`${yy}-${mm}-${dd}`);
    } else {
      $el.val(`${dd}/${mm}/${yy}`);
    }
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

  // Use jQuery UI datepicker only (stable with DD/MM/YYYY values)
  $(function(){
    if ($.fn.datepicker) {
      $('#paid_date_from, #paid_date_to').datepicker({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true
      });
    }
  });

  function showLoader(on) { $('#loader-1').toggle(!!on); }

  function validateRange() {
    const f = $('#paid_date_from').val().trim();
    const t = $('#paid_date_to').val().trim();
    const fd = parseInputDate(f), td = parseInputDate(t);
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
    const paid_date_from = toDMY($('#paid_date_from').val());
    const paid_date_to   = toDMY($('#paid_date_to').val());

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
