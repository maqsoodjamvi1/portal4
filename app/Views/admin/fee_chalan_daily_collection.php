<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  helper('form');
  $schoolPrintName = isset($schoolinfo->system_name) && $schoolinfo->system_name !== ''
    ? $schoolinfo->system_name
    : 'School';
  $today = date('d/m/Y');
?>
<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />

<div class="no-print">
<?= view('components/page_header', [
    'title' => 'Daily collection summary',
    'icon' => 'fas fa-calendar-day',
    'subtitle' => 'One row per paid date — total collected only (no payer or challan detail).',
    'actionsHtml' => '<div class="text-sm-right">'
        . '<a href="' . esc(base_url('admin/fee-chalan-balance'), 'attr') . '" class="btn btn-sm btn-outline-primary">'
        . '<i class="fas fa-file-invoice-dollar me-1"></i> Detailed balance report</a></div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Daily collection', 'active' => true],
    ],
]) ?>
</div>

<section class="content">
  <div class="container-fluid">

    <div class="card sms-card card-primary card-outline shadow-sm no-print">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnToday">Today</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnThisMonth">This Month</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnLastMonth">Last Month</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnYTD">YTD</button>
        </div>
      </div>
      <div class="card-body">
        <form id="filterForm" class="row">
          <div class="form-group col-md-4">
            <label class="mb-1">Date paid from</label>
            <div class="input-group date" id="datepicker_from" data-target-input="nearest">
              <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
              <input type="text" class="form-control"
                     name="paid_date_from" id="paid_date_from" value="<?= esc($today) ?>" required>
            </div>
            <small class="text-muted">Format: DD/MM/YYYY</small>
          </div>
          <div class="form-group col-md-4">
            <label class="mb-1">Date paid to</label>
            <div class="input-group date" id="datepicker_to" data-target-input="nearest">
              <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
              <input type="text" class="form-control"
                     name="paid_date_to" id="paid_date_to" value="<?= esc($today) ?>" required>
            </div>
            <small class="text-muted">Must be on or after “From”.</small>
          </div>
          <div class="form-group col-md-4 d-flex align-items-end">
            <button type="button" id="btnView" class="btn btn-primary me-2">
              <i class="fas fa-search me-1"></i> View
            </button>
            <button type="button" id="btnReset" class="btn btn-secondary">
              <i class="fas fa-undo me-1"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card shadow-sm" id="reportCard">
      <div class="card-header d-flex justify-content-between align-items-center no-print">
        <h3 class="card-title m-0">
          <i class="fas fa-calendar-day me-1"></i> Daily totals
        </h3>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPrint">
          <i class="fas fa-print me-1"></i> Print
        </button>
      </div>

      <div id="printHeader" class="fee-balance-print-banner d-none" aria-hidden="true">
        <div class="fee-balance-print-banner__inner">
          <div class="fee-balance-print-banner__school"><?= esc($schoolPrintName) ?></div>
          <div class="fee-balance-print-banner__title">Daily fee collection summary</div>
          <div class="fee-balance-print-banner__meta">
            <span>Paid date range: <strong id="phRange">—</strong></span>
            <span class="fee-balance-print-banner__meta-sep"> · </span>
            <span>Printed: <strong id="phDate">—</strong></span>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div id="resultsArea" class="table-responsive">
          <div class="text-center text-muted py-5" id="placeholder">
            Select a date range and click <strong>View</strong>.
          </div>
          <div id="dailyCollResults"></div>
        </div>
      </div>
    </div>

  </div>

  <div id="loader-1" class="no-print" style="display:none;position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:9999;background:rgba(255,255,255,.6);">
    <div style="position:absolute;top:45%;left:50%;transform:translate(-50%,-50%);">
      <div class="spinner-border text-primary" role="status"></div>
      <div class="mt-2">Loading…</div>
    </div>
  </div>
</section>

<style>
.fee-balance-print-banner { display: none !important; }

.daily-coll-tfoot th {
  background: #e2e8f0 !important;
  color: #0f172a !important;
  font-weight: 700 !important;
  border-top: 2px solid #94a3b8 !important;
}

@media print {
  @page {
    size: A4;
    margin: 12mm 14mm 14mm 14mm;
  }

  html, body {
    height: auto !important;
    background: #fff !important;
    color: #111 !important;
  }

  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .no-print,
  .main-header, .main-sidebar, .main-footer, .content-header,
  .card-header .btn, #filterForm, .breadcrumb, #placeholder, #loader-1,
  .control-sidebar, .preloader {
    display: none !important;
  }

  .content-wrapper, .wrapper { margin: 0 !important; padding: 0 !important; }
  .content-wrapper > .content { padding: 0 !important; }
  .content .container-fluid { padding: 0 !important; max-width: 100% !important; }
  #reportCard { border: 0 !important; box-shadow: none !important; margin: 0 !important; }
  #reportCard .card-body { padding: 0 !important; }

  #printHeader.fee-balance-print-banner {
    display: block !important;
    margin: 0 0 10pt 0 !important;
    page-break-after: avoid;
  }

  .fee-balance-print-banner__inner {
    background: #0f172a !important;
    color: #fff !important;
    padding: 12pt 14pt;
  }

  .fee-balance-print-banner__school {
    font-size: 11pt;
    font-weight: 700;
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

  .fee-balance-print-banner__meta { font-size: 9pt; opacity: 0.95; }
  .fee-balance-print-banner__meta-sep { opacity: 0.6; }

  .daily-coll-summary {
    background: #f1f5f9 !important;
    border: 0.5pt solid #cbd5e1 !important;
    page-break-after: avoid;
    margin-bottom: 8pt !important;
    padding: 8pt 10pt !important;
  }

  .daily-coll-report-inner { font-size: 9.5pt; }

  .fee-balance-print-table {
    width: 100% !important;
    font-size: 9pt !important;
    border-collapse: collapse !important;
  }

  .fee-balance-print-table thead { display: table-header-group; }

  .fee-balance-print-table th {
    background: #1e293b !important;
    color: #fff !important;
    border: 1pt solid #0f172a !important;
    padding: 5pt 6pt !important;
  }

  .fee-balance-print-table td {
    border: 0.5pt solid #cbd5e1 !important;
    padding: 4pt 6pt !important;
  }

  .fee-balance-print-table tbody tr:nth-child(even) td {
    background: #f8fafc !important;
  }

  .daily-coll-tfoot th {
    background: #e2e8f0 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  #resultsArea { overflow: visible !important; }
  a[href]:after { content: none !important; }
}
</style>

<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script>
(function(){
  const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };

  function karachiNow() {
    try { return new Date().toLocaleString('en-GB', { timeZone: 'Asia/Karachi' }); }
    catch (e) { return new Date().toLocaleString(); }
  }

  function formatRangeLabel(from, to) {
    return (from || '—') + '  to  ' + (to || '—');
  }

  function parseDMY(s) {
    if (!s) return null;
    const m = String(s).match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!m) return null;
    const d = new Date(+m[3], (+m[2]) - 1, +m[1], 12, 0, 0);
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
    const dd = ('0' + d.getDate()).slice(-2);
    const mm = ('0' + (d.getMonth() + 1)).slice(-2);
    const yy = d.getFullYear();
    if ($el.attr('type') === 'date') {
      $el.val(`${yy}-${mm}-${dd}`);
    } else {
      $el.val(`${dd}/${mm}/${yy}`);
    }
  }

  $('#btnToday').on('click', function () {
    const d = new Date();
    setPicker($('#paid_date_from'), d);
    setPicker($('#paid_date_to'), d);
  });
  $('#btnThisMonth').on('click', function () {
    const d = new Date();
    setPicker($('#paid_date_from'), new Date(d.getFullYear(), d.getMonth(), 1));
    setPicker($('#paid_date_to'), new Date(d.getFullYear(), d.getMonth() + 1, 0));
  });
  $('#btnLastMonth').on('click', function () {
    const d = new Date();
    setPicker($('#paid_date_from'), new Date(d.getFullYear(), d.getMonth() - 1, 1));
    setPicker($('#paid_date_to'), new Date(d.getFullYear(), d.getMonth(), 0));
  });
  $('#btnYTD').on('click', function () {
    const d = new Date();
    setPicker($('#paid_date_from'), new Date(d.getFullYear(), 0, 1));
    setPicker($('#paid_date_to'), d);
  });

  // Use jQuery UI datepicker only (stable with DD/MM/YYYY values)
  $(function () {
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
    if (fd > td) { toastr.error('“Date paid from” must be on or before “Date paid to”.'); return false; }
    return true;
  }

  function updatePrintHeader(from, to) {
    $('#phDate').text(karachiNow());
    $('#phRange').text(formatRangeLabel(from, to));
  }

  function loadReport() {
    if (!validateRange()) return;
    const paid_date_from = toDMY($('#paid_date_from').val());
    const paid_date_to = toDMY($('#paid_date_to').val());
    showLoader(true);
    $('#placeholder').addClass('d-none');

    $.ajax({
      url: '<?= base_url('admin/fee-chalan-daily-collection/data') ?>',
      type: 'POST',
      data: { paid_date_from, paid_date_to, [CSRF.name]: CSRF.hash },
      success: function (res) {
        $('#dailyCollResults').html(res || '<p class="text-muted">No data.</p>');
        updatePrintHeader(paid_date_from, paid_date_to);
      },
      error: function (xhr) {
        $('#dailyCollResults').html('<p class="text-danger">Failed to load report.</p>');
        console.error(xhr.responseText);
      },
      complete: function () { showLoader(false); }
    });
  }

  $('#btnView').on('click', loadReport);
  $('#btnReset').on('click', function () {
    $('#paid_date_from').val('<?= esc($today) ?>');
    $('#paid_date_to').val('<?= esc($today) ?>');
    $('#dailyCollResults').empty();
    $('#placeholder').removeClass('d-none');
  });

  $('#btnPrint').on('click', function () {
    updatePrintHeader($('#paid_date_from').val(), $('#paid_date_to').val());
    window.print();
  });
})();
</script>

<?= $this->endSection() ?>
