<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row align-items-center mb-2">
      <div class="col-sm-7">
        <h1 class="mb-0">Daily Fee Collection</h1>
        <small class="text-muted">Net = Total Collection − Discount</small>
      </div>
      <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Daily Collection</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <!-- Filters -->
    <div class="card shadow-sm">
      <div class="card-body pb-2">
        <div class="row g-2 align-items-end">
          <div class="col-12 col-md-3">
            <label for="month" class="mb-1 font-weight-bold">Month</label>
            <select class="form-control" id="month">
              <option value="">Select Month</option>
              <?php foreach ($months as $key => $name): ?>
                <option value="<?= (int)$key ?>" <?= ((int)$key === (int)$selected_month) ? 'selected' : '' ?>>
                  <?= esc($name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <label for="year" class="mb-1 font-weight-bold">Year</label>
            <select class="form-control" id="year">
              <option value="">Select Year</option>
              <?php foreach ($years as $y): ?>
                <option value="<?= (int)$y ?>" <?= ((int)$y === (int)$selected_year) ? 'selected' : '' ?>>
                  <?= (int)$y ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 col-md-6 text-md-right mt-2 mt-md-0">
            <div class="btn-group mb-2" role="group" aria-label="Quick ranges">
              <button class="btn btn-outline-primary btn-sm preset" data-preset="this">This Month</button>
              <button class="btn btn-outline-secondary btn-sm preset" data-preset="last">Last Month</button>
              <button class="btn btn-outline-info btn-sm preset" data-preset="ytd">YTD</button>
            </div>
            <div class="btn-group mb-2 ml-md-2" role="group" aria-label="Actions">
              <button id="btnRefresh" class="btn btn-primary btn-sm">
                <i class="fas fa-sync-alt mr-1"></i>Refresh
              </button>
              <button id="btnExport" class="btn btn-success btn-sm">
                <i class="fas fa-file-csv mr-1"></i>Export CSV
              </button>
              <button id="btnPrint" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print mr-1"></i>Print
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer py-2">
        <small class="text-muted">
          Choose month & year, or use a preset. CSV and print reflect the current selection.
        </small>
      </div>
    </div>

    <!-- KPIs -->
    <div class="row">
      <div class="col-12 col-md-4">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3 id="kpi-total">0</h3>
            <p>Total Collection</p>
          </div>
          <div class="icon"><i class="fas fa-donate"></i></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3 id="kpi-discount">0</h3>
            <p>Total Discount</p>
          </div>
          <div class="icon"><i class="fas fa-badge-percent"></i></div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="small-box bg-success">
          <div class="inner">
            <h3 id="kpi-net">0</h3>
            <p>Net Collection</p>
          </div>
          <div class="icon"><i class="fas fa-cash-register"></i></div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-body position-relative p-0">
       <div id="plr-loader" class="plr-loader">
  <i class="fas fa-2x fa-sync-alt fa-spin"></i>
</div>

        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0" id="report-table" style="display:none; min-width:640px;">
            <thead class="thead-dark">
              <tr>
                <th style="width: 160px;">Date</th>
                <th class="text-right">Total Collection</th>
                <th class="text-right">Total Discount</th>
                <th class="text-right">Net Collection</th>
              </tr>
            </thead>
            <tbody id="report-body"></tbody>
            <tfoot>
              <tr class="bg-light font-weight-bold">
                <td>Grand Totals</td>
                <td id="grand-total" class="text-right">0</td>
                <td id="grand-discount" class="text-right">0</td>
                <td id="grand-net" class="text-right">0</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div id="empty-state" class="p-4 text-center text-muted" style="display:none;">
          <i class="far fa-calendar-times fa-2x d-block mb-2"></i>
          No data found for the selected period.
        </div>

        <div id="error-state" class="p-4 text-center text-danger" style="display:none;">
          <i class="fas fa-exclamation-triangle fa-2x d-block mb-2"></i>
          Failed to load data. Please try again.
          <div class="small mt-2" id="error-detail"></div>
        </div>
      </div>
    </div>

  </div>
</section>

<style>
  /* Improve small-box contrast on AdminLTE */
  .small-box .icon { opacity: .3; }
  @media (max-width: 575.98px) {
    .small-box .icon { display: none; }
  }
  /* Make table header sticky on small screens for better UX */
  @media (max-width: 768px) {
    .table-responsive { max-height: 60vh; }
    #report-table thead th {
      position: sticky; top: 0; z-index: 2;
      background: #343a40; color: #fff;
    }
  }

    #plr-loader { display:none; }

    /* Smooth, JS-togglable overlay */
.plr-loader{
  position:absolute;
  inset:0;
  background: rgba(255,255,255,.7);
  z-index: 50;
  display: grid;           /* center the spinner without Bootstrap utilities */
  place-items: center;
  opacity: 0;              /* hidden by default */
  pointer-events: none;    /* clicks pass through */
  transition: opacity .15s ease-in-out;
}
.plr-loader.on{
  opacity: 1;              /* visible when .on is present */
  pointer-events: auto;
}
</style>

<script>
(function(){
  'use strict';

  const URL_DAILY = "<?= base_url('admin/profit-loss-report/daily') ?>";

  const $month  = $('#month');
  const $year   = $('#year');
  const $loader = $('#plr-loader');   // our overlay

  const $table  = $('#report-table');
  const $tbody  = $('#report-body');
  const $empty  = $('#empty-state');
  const $error  = $('#error-state');
  const $errDet = $('#error-detail');

  // helper: toggle loader
  function setLoading(on){ $loader.toggleClass('on', !!on); }

  // unchanged bindings...
  $('.preset').on('click', function(e){
    e.preventDefault();
    const p = $(this).data('preset');
    const now = new Date();
    let mm = now.getMonth() + 1;
    let yy = now.getFullYear();
    if (p === 'last') { mm -= 1; if (mm < 1) { mm = 12; yy -= 1; } }
    $month.val(mm); $year.val(yy);
    fetchReport();
  });
  $('#btnRefresh').on('click', e => { e.preventDefault(); fetchReport(); });
  $('#btnPrint').on('click',   e => { e.preventDefault(); window.print();   });
  $('#btnExport').on('click',  e => { e.preventDefault(); exportCSV();      });
  $month.add($year).on('change', fetchReport);

  function showState(state, detail) {
    // states: 'loading' | 'table' | 'empty' | 'error' | 'idle'
    setLoading(state === 'loading');
    $table.toggle(state === 'table');
    $empty.toggle(state === 'empty');
    $error.toggle(state === 'error');
    if (state === 'error') $errDet.text(detail || '');
    if (state === 'idle') { $table.hide(); $empty.hide(); $error.hide(); }
  }

  function fetchReport() {
    const m = parseInt($month.val(), 10);
    const y = parseInt($year.val(), 10);
    if (!m || !y) { showState('idle'); return; }

    showState('loading');

    $.ajax({
      url: URL_DAILY,
      method: 'POST',
      dataType: 'json',
      data: { month: m, year: y },
      success: function(res) {
        if (!res || res.success === false) {
          clearTable();
          updateKpis(0,0,0);
          showState('empty');
          return;
        }
        const rows   = Array.isArray(res.rows) ? res.rows : [];
        const totals = res.totals || { total:'0', discount:'0', net:'0' };
        renderRows(rows);
        updateTotals(totals);
        updateKpis(totals.total, totals.discount, totals.net);
        showState(rows.length ? 'table' : 'empty');
      },
      error: function(xhr) {
        showState('error', 'HTTP ' + xhr.status);
      },
      complete: function(){
        // safety: ensure loader is off even if showState didn't run for some reason
        setLoading(false);
      }
    });
  }

  function renderRows(rows) {
    $tbody.empty();
    rows.forEach(r => {
      $tbody.append(
        `<tr>
           <td>${escapeHtml(r.date || '')}</td>
           <td class="text-right">${escapeHtml(r.total || '0')}</td>
           <td class="text-right">${escapeHtml(r.discount || '0')}</td>
           <td class="text-right font-weight-bold">${escapeHtml(r.net || '0')}</td>
         </tr>`
      );
    });
  }

  function updateTotals(t) {
    $('#grand-total').text(t.total || '0');
    $('#grand-discount').text(t.discount || '0');
    $('#grand-net').text(t.net || '0');
  }
  function updateKpis(total, discount, net) {
    $('#kpi-total').text(total || '0');
    $('#kpi-discount').text(discount || '0');
    $('#kpi-net').text(net || '0');
  }
  function clearTable() {
    $tbody.empty();
    updateTotals({ total:'0', discount:'0', net:'0' });
  }
  function exportCSV() {
    const m = parseInt($month.val(), 10);
    const y = parseInt($year.val(), 10);
    if (!m || !y || !$table.is(':visible')) {
      return (window.toastr && toastr.info) ? toastr.info('Nothing to export.') : alert('Nothing to export.');
    }
    const rows = [];
    rows.push(['Date','Total Collection','Total Discount','Net Collection']);
    $('#report-body tr').each(function(){
      const r = [];
      $(this).find('td').each(function(){ r.push($(this).text().trim()); });
      rows.push(r);
    });
    rows.push(['Grand Totals',
      $('#grand-total').text().trim(),
      $('#grand-discount').text().trim(),
      $('#grand-net').text().trim()
    ]);
    const csv  = rows.map(r => r.map(csvEscape).join(',')).join('\r\n');
    const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `daily_collection_${y}-${String(m).padStart(2,'0')}.csv`;
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(url), 1500);
  }
  function csvEscape(s){ s = String(s ?? ''); return /[",\r\n]/.test(s) ? '"' + s.replace(/"/g,'""') + '"' : s; }
  function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

  // Initial load
  fetchReport();
})();
</script>


<?= $this->endSection() ?>
