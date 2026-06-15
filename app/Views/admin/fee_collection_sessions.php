<?php $uiNeedsDataTables = false; ?>
<?php $uiNeedsChart = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Session-wise Fee Collection',
    'icon' => 'fas fa-chart-area',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Session-wise Fee Collection', 'active' => true],
    ],
]) ?>

<style>
/* ===== Layout wrapper ===== */
.fee-report-wrap{
  display:flex;
  flex-direction:column;
  gap:20px;
}

/* ===== Filter / Toolbar Card ===== */
.fee-filter-card{
  border:1px solid #e5e7eb;
  border-radius:14px;
  background:#ffffff;
  box-shadow:0 2px 10px rgba(0,0,0,.04);
  padding:14px 16px 16px 16px;
}

.fee-filter-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:10px;
  flex-wrap:wrap;
  gap:6px;
}
.fee-filter-title{
  margin:0;
  font-weight:800;
  font-size:18px;
  color:#111827;
}
.fee-filter-sub{
  font-size:12px;
  color:#64748b;
}

.fee-type-box{
  max-height:160px;
  overflow-y:auto;
  border:1px solid #e5e7eb;
  border-radius:10px;
  padding:8px 10px;
  background:#f9fafb;
}
.fee-type-box label{
  display:inline-flex;
  align-items:center;
  margin-right:14px;
  margin-bottom:6px;
  font-size:13px;
  color:#111827;
}
.fee-type-box input[type="checkbox"]{
  margin-right:6px;
}

/* ===== Report card ===== */
.fee-report-card{
  border:1px solid #e5e7eb;
  border-radius:14px;
  background:#ffffff;
  box-shadow:0 2px 10px rgba(0,0,0,.04);
  padding:14px 16px 16px 16px;
  page-break-inside:avoid;
}

.fee-report-header{
  text-align:center;
  margin-bottom:10px;
}
.fee-report-header h3{
  margin:0 0 4px 0;
  font-weight:800;
  font-size:18px;
  color:#111827;
}
.fee-report-header .sub{
  font-size:12px;
  color:#64748b;
}

/* ===== Table container ===== */
.fee-table-scroll{
  overflow-x:auto;
  -webkit-overflow-scrolling:touch;
}

.fee-table{
  border-collapse:collapse;
  width:100%;
  font-size:12px;
}
.fee-table th,
.fee-table td{
  border:1px solid #e5e7eb;
  padding:7px 6px;          /* a bit more row height */
  line-height:1.4;
  text-align:right;
  color:#111827;
  white-space:nowrap;
}

/* Header cells */
.fee-table thead th{
  background:#f9fafb;
  font-weight:700;
  text-align:center;
}

/* First column: compact session name (e.g. 2024-25) */
.fee-table .col-session{
  width:80px;
  text-align:left;
  padding-left:6px;
  padding-right:6px;
}

/* Last column: compact averages */
.fee-table .col-row-total{
  width:90px;
  text-align:right;
}

/* Body session cell */
.fee-table tbody td.session-col{
  font-weight:700;
}

/* Footer cells */
.fee-table tfoot td{
  font-weight:800;
  background:#f3f4f6;
}


/* Buttons row */
.fee-btn-row{
  margin-top:10px;
  display:flex;
  flex-wrap:wrap;
  gap:8px;
}

/* Chart container */
.fee-chart-wrap{
  margin-top:24px;
}
.fee-chart-header{
  display:flex;
  flex-wrap:wrap;
  justify-content:space-between;
  align-items:center;
  margin-bottom:8px;
  gap:8px;
}
.fee-chart-header h5{
  font-size:14px;
  font-weight:700;
  margin:0;
  color:#111827;
}
.fee-chart-inner{
  position:relative;
  width:100%;
  max-width:100%;
  height:260px;
}
.fee-chart-month-select{
  max-width:180px;
}

/* Print-friendly tweaks */
@media print{
  body{
    -webkit-print-color-adjust:exact;
    print-color-adjust:exact;
  }
  .content-header,
  .main-header,
  .main-sidebar,
  .main-footer,
  .navbar,
  .btn,
  .breadcrumb,
  .fee-filter-card{       /* 🔒 hide filters in print */
    display:none !important;
  }
  .content-wrapper{
    margin:0 !important;
    padding:0 !important;
  }
  .fee-report-card{
    box-shadow:none;
    border:1px solid #e5e7eb;
  }
  .fee-chart-wrap{
    margin-top:10px;
     page-break-before: always;
  }
}
</style>

<section class="content">
  <div class="container-fluid">

    <div class="fee-report-wrap">

      <!-- ========= FILTER CARD ========= -->
      <div class="fee-filter-card">
        <div class="fee-filter-header">
          <h3 class="fee-filter-title">Filters</h3>
          <div class="fee-filter-sub">
            Select fee types and generate session-wise collection report.
          </div>
        </div>

        <form method="post" action="">
          <?= csrf_field() ?>

          <div class="row">
            <div class="col-12">
              <label class="mb-1 fw-bold" style="font-size:13px;">Fee Types</label>

              <div class="d-flex align-items-center mb-1">
                <div class="form-check form-check me-3">
                  <input type="checkbox" class="form-check-input" id="chk_all_fee_types">
                  <label class="form-check-label" for="chk_all_fee_types" style="font-size:13px;">
                    Select All
                  </label>
                </div>
                <small class="text-muted" style="font-size:12px;">
                  Tick the fee types you want to include in the collection totals.
                </small>
              </div>

              <div class="fee-type-box">
                <?php if (!empty($feeTypes)): ?>
                  <?php foreach ($feeTypes as $ft): ?>
                    <?php
                      $fid     = (int) $ft['fee_type_id'];
                      $checked = in_array($fid, $selectedFeeTypes ?? []) ? 'checked' : '';
                    ?>
                    <label>
                      <input type="checkbox"
                             name="fee_types[]"
                             value="<?= esc($fid) ?>"
                             class="fee-type-item"
                             <?= $checked ?>>
                      <?= esc($ft['fee_type_name']) ?>
                    </label>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-muted" style="font-size:12px;">No active fee types found.</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="fee-btn-row">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="fas fa-filter"></i> Apply Filter
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </form>
      </div>

      <!-- ========= REPORT CARD ========= -->
      <div class="fee-report-card">

        <div class="fee-report-header">
          <h3>
            <?= isset($campus['campus_name']) ? esc($campus['campus_name']) : 'Campus' ?>
          </h3>
          <div class="sub">
            Session-wise Fee Collection Report
          </div>
          <?php if (!empty($selectedFeeTypes) && !empty($feeTypes)): ?>
            <div class="sub mt-1">
              Included Fee Types:
              <?php
                $names = [];
                foreach ($feeTypes as $ft) {
                  if (in_array((int) $ft['fee_type_id'], $selectedFeeTypes)) {
                    $names[] = $ft['fee_type_name'];
                  }
                }
                echo esc(implode(', ', $names));
              ?>
            </div>
          <?php endif; ?>
        </div>

        <?php if (empty($sessions) || empty($months)): ?>
          <div class="alert alert-info mb-0">
            No academic sessions or month configuration found for this campus/system.
          </div>
        <?php else: ?>
          <div class="fee-table-scroll">
            <table class="fee-table">
              <thead>
                <tr>
                  <!-- 1st column: session name -->
                  <th class="col-session">Session</th>

                  <?php foreach ($months as $m): ?>
                    <th style="width:55px;">
                      <div class="month-vertical">
                        <?= esc($m['label']) ?> <!-- Apr, May, ... -->
                      </div>
                    </th>
                  <?php endforeach; ?>

                  <!-- Last column: average per month -->
                  <th class="col-row-total">Avg / Month</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $grandTotalsByMonth = array_fill_keys(array_column($months, 'month_no'), 0.0);
                  $monthsCount        = count($months);
                ?>

                <?php foreach ($sessions as $s): ?>
                  <?php
                    $sid         = (int) $s['session_id'];
                    $sessionName = $s['session_name'] ?? ('Session #' . $sid);
                    $rowSum      = 0.0;
                  ?>
                  <tr>
                    <td class="session-col col-session">
                      <?= esc($sessionName) ?>
                    </td>

                    <?php foreach ($months as $m): ?>
                      <?php
                        $mNo = (int) $m['month_no'];
                        $val = isset($matrix[$sid][$mNo]) ? (float) $matrix[$sid][$mNo] : 0.0;

                        $rowSum                     += $val;
                        $grandTotalsByMonth[$mNo]   += $val;
                      ?>
                      <td>
                        <?= $val > 0 ? number_format($val, 0) : '-' ?>
                      </td>
                    <?php endforeach; ?>

                    <?php
                      $rowAvg = $monthsCount > 0 ? $rowSum / $monthsCount : 0.0;
                    ?>
                    <td class="col-row-total">
                      <?= $rowAvg > 0 ? number_format($rowAvg, 0) : '-' ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <?php
                  $grandOverallSum = array_sum($grandTotalsByMonth);
                  $grandOverallAvg = $monthsCount > 0 ? $grandOverallSum / $monthsCount : 0.0;
                ?>
                <tr>
                  <td class="col-session"><strong>Overall Avg</strong></td>
                  <?php foreach ($months as $m): ?>
                    <?php
                      $mNo = (int) $m['month_no'];
                      $val = (float) ($grandTotalsByMonth[$mNo] ?? 0.0);
                    ?>
                    <td>
                      <?= $val > 0 ? number_format($val, 0) : '-' ?>
                    </td>
                  <?php endforeach; ?>
                  <td class="col-row-total">
                    <?= $grandOverallAvg > 0 ? number_format($grandOverallAvg, 0) : '-' ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <?php
            // Prepare chart data: compare sessions per month
            $sessionLabels = [];
            foreach ($sessions as $s) {
              $sessionLabels[] = $s['session_name'] ?? ('Session #' . $s['session_id']);
            }

            // dataByMonth[month_no] = [values in same order as $sessionLabels]
            $dataByMonth = [];
            foreach ($months as $m) {
              $mNo  = (int) $m['month_no'];
              $row  = [];
              foreach ($sessions as $s) {
                $sid = (int) $s['session_id'];
                $row[] = isset($matrix[$sid][$mNo]) ? (float) $matrix[$sid][$mNo] : 0.0;
              }
              $dataByMonth[$mNo] = $row;
            }

            // Months metadata for JS
            $monthMeta = [];
            foreach ($months as $m) {
              $monthMeta[] = [
                'label'    => $m['label'],
                'month_no' => (int) $m['month_no'],
              ];
            }
          ?>

          <!-- ========= CHART UNDER TABLE ========= -->
          <div class="fee-chart-wrap">
            <div class="fee-chart-header">
              <h5>Compare Sessions by Month</h5>
              <div class="fee-chart-month-select">
                <label for="monthSelect" style="font-size:12px;margin-bottom:2px;">Select Month</label>
                <select id="monthSelect" class="form-control form-control-sm">
                  <?php foreach ($monthMeta as $m): ?>
                    <option value="<?= esc($m['month_no']) ?>">
                      <?= esc($m['label']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="fee-chart-inner">
              <canvas id="feeCollectionChart"></canvas>
            </div>
          </div>

        <?php endif; ?>

      </div>

    </div>
  </div>
</section>

<!-- Chart.js (from CDN, if not already loaded globally) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  (function() {
    // ===== Fee type "Select All" logic =====
    const chkAll = document.getElementById('chk_all_fee_types');
    const items  = document.querySelectorAll('.fee-type-item');

    if (chkAll && items.length) {
      const allChecked = Array.from(items).every(i => i.checked);
      chkAll.checked = allChecked;

      chkAll.addEventListener('change', function() {
        const checked = this.checked;
        items.forEach(function(i){ i.checked = checked; });
      });

      items.forEach(function(i){
        i.addEventListener('change', function(){
          const everyChecked = Array.from(items).every(ii => ii.checked);
          const someChecked  = Array.from(items).some(ii => ii.checked);
          chkAll.checked     = everyChecked;
          chkAll.indeterminate = !everyChecked && someChecked;
        });
      });
    }

    // ===== Chart: compare sessions for selected month =====
    var chartCanvas = document.getElementById('feeCollectionChart');
    var monthSelect = document.getElementById('monthSelect');

    if (chartCanvas && monthSelect) {
      var ctx           = chartCanvas.getContext('2d');
      var sessionLabels = <?= json_encode($sessionLabels ?? []) ?>;
      var monthMeta     = <?= json_encode($monthMeta ?? []) ?>;
      var dataByMonth   = <?= json_encode($dataByMonth ?? []) ?>; // keyed by month_no

      function getMonthLabel(mno) {
        for (var i = 0; i < monthMeta.length; i++) {
          if (parseInt(monthMeta[i].month_no) === parseInt(mno)) {
            return monthMeta[i].label;
          }
        }
        return '';
      }

      function getDataForMonth(mno) {
        var key = String(mno);
        if (dataByMonth.hasOwnProperty(key)) return dataByMonth[key];
        if (dataByMonth.hasOwnProperty(parseInt(mno))) return dataByMonth[parseInt(mno)];
        return [];
      }

      var initialMonthNo = monthSelect.value;
      var initialLabel   = getMonthLabel(initialMonthNo);
      var initialData    = getDataForMonth(initialMonthNo);

      var feeChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: sessionLabels,
          datasets: [{
            label: 'Collection (' + initialLabel + ')',
            data: initialData,
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(context) {
                  var v = context.parsed.y || 0;
                  return v.toLocaleString();
                }
              }
            }
          },
          scales: {
            x: {
              ticks: {
                autoSkip: false,
                maxRotation: 45,
                minRotation: 0
              }
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  try {
                    return value.toLocaleString();
                  } catch (e) {
                    return value;
                  }
                }
              }
            }
          }
        }
      });

      monthSelect.addEventListener('change', function() {
        var mNo   = this.value;
        var label = getMonthLabel(mNo);
        var data  = getDataForMonth(mNo);

        feeChart.data.datasets[0].label = 'Collection (' + label + ')';
        feeChart.data.datasets[0].data  = data;
        feeChart.update();
      });
    }
  })();
</script>

<?= $this->endSection() ?>
