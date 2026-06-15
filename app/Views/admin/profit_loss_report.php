<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Campus Cash Flow / P&L',
    'icon' => 'fas fa-chart-line',
    'subtitle' => 'Monthly summary, account balances, and daily fee collection',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Cash Flow / P&L', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">

    <!-- Filters -->
    <div class="card sms-card shadow-sm">
      <div class="card-body pb-2">
        <div class="row g-2 align-items-end">
          <div class="col-12 col-md-3">
            <label for="month" class="mb-1 fw-bold">Month</label>
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
            <label for="year" class="mb-1 fw-bold">Year</label>
            <select class="form-control" id="year">
              <option value="">Select Year</option>
              <?php foreach ($years as $y): ?>
                <option value="<?= (int)$y ?>" <?= ((int)$y === (int)$selected_year) ? 'selected' : '' ?>>
                  <?= (int)$y ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <div class="btn-group mb-2" role="group" aria-label="Quick ranges">
              <button class="btn btn-outline-primary btn-sm preset" data-preset="this">This Month</button>
              <button class="btn btn-outline-secondary btn-sm preset" data-preset="last">Last Month</button>
              <button class="btn btn-outline-info btn-sm preset" data-preset="ytd">YTD</button>
            </div>
            <div class="btn-group mb-2 ms-md-2" role="group" aria-label="Actions">
              <button id="btnRefresh" class="btn btn-primary btn-sm">
                <i class="fas fa-sync-alt me-1"></i>Refresh
              </button>
              <button id="btnExport" class="btn btn-success btn-sm">
                <i class="fas fa-file-csv me-1"></i>Export CSV
              </button>
              <button id="btnPrint" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print me-1"></i>Print
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

    <!-- Monthly P&L KPIs -->
    <div class="row" id="pnl-summary-row">
      <div class="col-6 col-md-3">
        <div class="small-box bg-success">
          <div class="inner"><h3 id="kpi-income">0</h3><p>Fee Income</p></div>
          <div class="icon"><i class="fas fa-arrow-down"></i></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="small-box bg-danger">
          <div class="inner"><h3 id="kpi-outflow">0</h3><p>Total Outflow</p></div>
          <div class="icon"><i class="fas fa-arrow-up"></i></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="small-box bg-info">
          <div class="inner"><h3 id="kpi-net-pnl">0</h3><p>Net Surplus</p></div>
          <div class="icon"><i class="fas fa-balance-scale"></i></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="small-box bg-warning">
          <div class="inner"><h3 id="kpi-cash-hand">0</h3><p>Campus Cash Balance</p></div>
          <div class="icon"><i class="fas fa-wallet"></i></div>
        </div>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-lg-6">
        <div class="card card-outline card-secondary">
          <div class="card-header py-2"><strong>Expenses by head</strong></div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0" id="expense-head-table">
              <thead><tr><th>Head</th><th class="text-end">Amount</th></tr></thead>
              <tbody id="expense-head-body"><tr><td colspan="2" class="text-muted text-center">—</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card card-outline card-secondary">
          <div class="card-header py-2"><strong>Account balances</strong></div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0" id="account-balance-table">
              <thead><tr><th>Account</th><th class="text-end">Balance</th></tr></thead>
              <tbody id="account-balance-body"><tr><td colspan="2" class="text-muted text-center">—</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <h5 class="mb-2">Daily fee collection</h5>

    <!-- Daily KPIs -->
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
            <thead class="table-dark">
              <tr>
                <th style="width: 160px;">Date</th>
                <th class="text-end">Total Collection</th>
                <th class="text-end">Total Discount</th>
                <th class="text-end">Net Collection</th>
              </tr>
            </thead>
            <tbody id="report-body"></tbody>
            <tfoot>
              <tr class="bg-light fw-bold">
                <td>Grand Totals</td>
                <td id="grand-total" class="text-end">0</td>
                <td id="grand-discount" class="text-end">0</td>
                <td id="grand-net" class="text-end">0</td>
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
  const URL_SUMMARY = "<?= base_url('admin/profit-loss-report/monthly-summary') ?>";
  const URL_EXPORT = "<?= base_url('admin/profit-loss-report/export') ?>";
  const CSRF_NAME = "<?= csrf_token() ?>";
  const CSRF_HASH = "<?= csrf_hash() ?>";

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

  function fetchMonthlySummary(m, y) {
    $.post(URL_SUMMARY, { month: m, year: y }, function(res) {
      if (!res || !res.success || !res.summary) return;
      const s = res.summary;
      $('#kpi-income').text(fmtNum(s.income));
      $('#kpi-outflow').text(fmtNum(s.total_outflow));
      $('#kpi-net-pnl').text(fmtNum(s.net));
      $('#kpi-cash-hand').text(fmtNum(s.campus_cash_balance));

      const $eh = $('#expense-head-body').empty();
      const heads = s.expenses_by_head || [];
      if (!heads.length) {
        $eh.append('<tr><td colspan="2" class="text-muted text-center">No expenses</td></tr>');
      } else {
        heads.forEach(h => {
          $eh.append(`<tr><td>${escapeHtml(h.head_title)}</td><td class="text-end">${fmtNum(h.total)}</td></tr>`);
        });
        if (parseFloat(s.salary_outflow) > 0) {
          $eh.append(`<tr><td>Salary (paid)</td><td class="text-end">${fmtNum(s.salary_outflow)}</td></tr>`);
        }
      }

      const $ab = $('#account-balance-body').empty();
      const accs = s.accounts || [];
      if (!accs.length) {
        $ab.append('<tr><td colspan="2" class="text-muted text-center">No finance accounts — <a href="<?= base_url('admin/campus-finance-accounts') ?>">Set up</a></td></tr>');
      } else {
        accs.forEach(a => {
          $ab.append(`<tr><td>${escapeHtml(a.account_name)} <small class="text-muted">${escapeHtml(a.account_type)}</small></td><td class="text-end">${fmtNum(a.balance)}</td></tr>`);
        });
      }
      const petty = s.petty_cash || [];
      petty.forEach(p => {
        $ab.append(`<tr><td><em>Petty:</em> ${escapeHtml(p.user_name)}</td><td class="text-end">${fmtNum(p.balance)}</td></tr>`);
      });
    }, 'json');
  }

  function fmtNum(n) {
    const x = parseFloat(n) || 0;
    return x.toLocaleString(undefined, { maximumFractionDigits: 0 });
  }

  function fetchReport() {
    const m = parseInt($month.val(), 10);
    const y = parseInt($year.val(), 10);
    if (!m || !y) { showState('idle'); return; }

    fetchMonthlySummary(m, y);
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
           <td class="text-end">${escapeHtml(r.total || '0')}</td>
           <td class="text-end">${escapeHtml(r.discount || '0')}</td>
           <td class="text-end fw-bold">${escapeHtml(r.net || '0')}</td>
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
    if (!m || !y) {
      return (window.toastr && toastr.warning) ? toastr.warning('Select month and year.') : alert('Select month and year.');
    }
    const $form = $('<form method="post"></form>').attr('action', URL_EXPORT);
    $form.append($('<input type="hidden">').attr('name', CSRF_NAME).val(CSRF_HASH));
    $form.append($('<input type="hidden" name="month">').val(m));
    $form.append($('<input type="hidden" name="year">').val(y));
    $('body').append($form);
    $form.trigger('submit');
    $form.remove();
  }
  function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

  // Initial load
  fetchReport();
})();
</script>


<?= $this->endSection() ?>
