<?php
$uiNeedsDataTables = true;
$uiNeedsChart = true;
?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('pageStyles') ?>
<style>
  .fee-report-stat {
    border-radius: var(--sms-radius);
    border: none;
    box-shadow: var(--sms-shadow);
  }
  .fee-report-stat .card-body h3 { font-weight: 700; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => $title ?? 'Student Fee Summary Report',
    'icon' => 'fas fa-chart-pie',
    'subtitle' => 'Campus ID: ' . (int) ($campus_id ?? 0) . ' | Session ID: ' . (int) ($session_id ?? 0),
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Reports', 'url' => base_url('admin/reports')],
        ['label' => 'Student Fee Summary', 'active' => true],
    ],
    'actionsHtml' => '<div class="text-sm-right">'
        . '<a href="' . esc(base_url('admin/student_fee_summary/export_excel'), 'attr') . '" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Export</a> '
        . '<button type="button" onclick="window.print()" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Print</button>'
        . '</div>',
]) ?>

<section class="content">
  <div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-2">
      <div class="card text-white fee-report-stat" style="background:var(--sms-primary)">
        <div class="card-body">
          <h6 class="card-title">Total Students</h6>
          <h3><?= number_format($totals['total_active_students'] ?? 0) ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
      <div class="card text-white fee-report-stat" style="background:var(--sms-success)">
        <div class="card-body">
          <h6 class="card-title">Total Monthly Fee</h6>
          <h3>PKR <?= number_format($totals['total_net_monthly_fee'] ?? 0, 2) ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
      <div class="card text-white fee-report-stat" style="background:var(--sms-info)">
        <div class="card-body">
          <h6 class="card-title">Total Annual Fee</h6>
          <h3>PKR <?= number_format($totals['total_projected_annual_fee'] ?? 0, 2) ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
      <div class="card text-white fee-report-stat" style="background:var(--sms-warning);color:#1f2937">
        <div class="card-body">
          <h6 class="card-title">Avg Fee/Student</h6>
          <h3>PKR <?= number_format($totals['avg_fee_per_student'] ?? 0, 2) ?></h3>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-4"><div class="card sms-card"><div class="card-body"><h6>Total Discount</h6><h4 class="text-danger">PKR <?= number_format($totals['total_discount_given'] ?? 0, 2) ?></h4></div></div></div>
    <div class="col-md-4"><div class="card sms-card"><div class="card-body"><h6>Minimum Fee</h6><h4>PKR <?= number_format($totals['minimum_fee'] ?? 0, 2) ?></h4></div></div></div>
    <div class="col-md-4"><div class="card sms-card"><div class="card-body"><h6>Maximum Fee</h6><h4>PKR <?= number_format($totals['maximum_fee'] ?? 0, 2) ?></h4></div></div></div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card sms-card">
        <div class="card-header"><i class="fas fa-chart-bar"></i> Fee Distribution</div>
        <div class="card-body"><canvas id="feeChart" height="280"></canvas></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card sms-card">
        <div class="card-header"><i class="fas fa-chart-pie"></i> Students by Fee Range</div>
        <div class="card-body"><canvas id="studentChart" height="280"></canvas></div>
      </div>
    </div>
  </div>

  <?php
  ob_start();
  ?>
  <table class="table table-bordered table-striped table-hover mb-0">
    <thead class="table-light">
      <tr>
        <th>Fee Amount (PKR)</th>
        <th>Students</th>
        <th>Total Monthly (PKR)</th>
        <th>Projected Annual (PKR)</th>
        <th>% Students</th>
        <th>% Total Fee</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($summary) && is_array($summary)): ?>
        <?php foreach ($summary as $item): ?>
        <tr>
          <td class="text-end"><?= number_format($item['fee_amount'], 2) ?></td>
          <td class="text-center"><?= (int) $item['number_of_students'] ?></td>
          <td class="text-end"><?= number_format($item['total_monthly_fee_for_this_amount'], 2) ?></td>
          <td class="text-end"><?= number_format($item['projected_annual_fee_for_this_amount'], 2) ?></td>
          <td class="text-center"><?= esc($item['percentage_of_total_students']) ?>%</td>
          <td class="text-center"><?= esc($item['percentage_of_total_fee']) ?>%</td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center text-muted">No summary data for this session.</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot class="table-secondary">
      <tr class="fw-bold">
        <td class="text-end">TOTAL</td>
        <td class="text-center"><?= number_format($totals['total_active_students'] ?? 0) ?></td>
        <td class="text-end"><?= number_format($totals['total_net_monthly_fee'] ?? 0, 2) ?></td>
        <td class="text-end"><?= number_format($totals['total_projected_annual_fee'] ?? 0, 2) ?></td>
        <td class="text-center">100%</td>
        <td class="text-center">100%</td>
      </tr>
    </tfoot>
  </table>
  <?php $summaryTable = ob_get_clean(); ?>

  <?= view('components/data_table_card', [
      'title' => 'Fee Summary by Amount',
      'icon' => 'fas fa-table',
      'tableHtml' => $summaryTable,
  ]) ?>

  <?php ob_start(); ?>
  <table class="table table-bordered table-striped mb-0" id="classBreakdownTable">
    <thead class="table-light">
      <tr>
        <th>Class</th><th>Fee (PKR)</th><th>Students</th><th>Monthly (PKR)</th><th>Annual (PKR)</th><th>Avg Discount</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($class_breakdown)): foreach ($class_breakdown as $item): ?>
      <tr>
        <td><?= esc($item['class_name']) ?></td>
        <td class="text-end"><?= number_format($item['fee_amount'], 2) ?></td>
        <td class="text-center"><?= (int) $item['number_of_students'] ?></td>
        <td class="text-end"><?= number_format($item['total_monthly_fee'], 2) ?></td>
        <td class="text-end"><?= number_format($item['projected_annual_fee'], 2) ?></td>
        <td class="text-end"><?= number_format($item['avg_discount_per_student'], 2) ?></td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="6" class="text-center text-muted">No class breakdown</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php $classTable = ob_get_clean(); ?>
  <?= view('components/data_table_card', ['title' => 'Class-wise Breakdown', 'icon' => 'fas fa-school', 'tableHtml' => $classTable]) ?>

  <?php ob_start(); ?>
  <table class="table table-bordered table-striped mb-0" id="studentDetailsTable">
    <thead class="table-light">
      <tr>
        <th>Reg No</th><th>Name</th><th>Class</th><th>Section</th>
        <th>Standard</th><th>Discount</th><th>Net Monthly</th><th>Annual</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($student_details)): foreach ($student_details as $student): ?>
      <tr>
        <td><?= esc($student['reg_no']) ?></td>
        <td><?= esc($student['student_name']) ?></td>
        <td><?= esc($student['class_name']) ?></td>
        <td><?= esc($student['section_name']) ?></td>
        <td class="text-end"><?= number_format($student['standard_monthly_fee'], 2) ?></td>
        <td class="text-end text-danger"><?= number_format($student['discount_amount'], 2) ?></td>
        <td class="text-end fw-bold"><?= number_format($student['net_monthly_fee'], 2) ?></td>
        <td class="text-end"><?= number_format($student['projected_annual_fee'], 2) ?></td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="8" class="text-center text-muted">No student rows</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <?php $studentTable = ob_get_clean(); ?>
  <?= view('components/data_table_card', ['title' => 'Student Details', 'icon' => 'fas fa-users', 'tableHtml' => $studentTable]) ?>
</section>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
$(function() {
  $('#classBreakdownTable, #studentDetailsTable').DataTable({ pageLength: 25 });
  <?php if (!empty($summary) && is_array($summary)): ?>
  var feeAmounts = <?= json_encode(array_column($summary, 'fee_amount')) ?>;
  var studentCounts = <?= json_encode(array_column($summary, 'number_of_students')) ?>;
  var totalFees = <?= json_encode(array_column($summary, 'total_monthly_fee_for_this_amount')) ?>;
  if (document.getElementById('feeChart')) {
    new Chart(document.getElementById('feeChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: feeAmounts.map(function (a) { return 'PKR ' + Number(a).toLocaleString(); }),
        datasets: [{ label: 'Monthly Fee', data: totalFees, backgroundColor: 'rgba(60, 141, 188, 0.7)' }]
      },
      options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
  }
  if (document.getElementById('studentChart')) {
    new Chart(document.getElementById('studentChart').getContext('2d'), {
      type: 'pie',
      data: {
        labels: feeAmounts.map(function (a) { return 'PKR ' + Number(a).toLocaleString(); }),
        datasets: [{ data: studentCounts }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }
  <?php endif; ?>
});
</script>
<?= $this->endSection() ?>
