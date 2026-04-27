<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
  @media print {
    .no-print { display:none !important; }
    .card { box-shadow:none !important; border:0 !important; }
  }
  .table thead th { background:#f0f3f5 !important; }
</style>

<div class="no-print mb-3">
  <a class="btn btn-outline-secondary" href="<?= base_url(route_to('admin.strength.index') . '?campus_id=' . $campusId) ?>">&larr; Back</a>
  <button class="btn btn-primary" onclick="window.print()">Print this page</button>
</div>

<div class="mb-3">
  <h2 class="m-0">Class-wise Monthly Strength</h2>
  <div class="text-muted small">
    Campus: <?= esc($campusId) ?> • Printed: <?= date('Y-m-d H:i') ?>
  </div>
</div>

<?php
$sessionHeaders = array_map(fn($s) => $s['session_name'], $sessions);
$sessionIds     = array_map(fn($s) => $s['session_id'],   $sessions);
$fmtMonth = function(string $ym) {
  $dt = \DateTime::createFromFormat('Y-m', $ym);
  return $dt ? $dt->format('M Y') : $ym;
};
?>

<?php foreach ($classes as $cl): ?>
  <h4 class="mt-4 mb-2"><?= esc($cl['class_name']) ?></h4>
  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead>
        <tr>
          <th style="min-width:130px">Month</th>
          <?php foreach ($sessionHeaders as $sn): ?>
            <th class="text-center"><?= esc($sn) ?></th>
          <?php endforeach; ?>
          <th class="text-center">Row Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($months as $ym): ?>
          <?php $rowTotal = 0; ?>
          <tr>
            <td><strong><?= esc($fmtMonth($ym)) ?></strong></td>
            <?php foreach ($sessionIds as $sid): ?>
              <?php $val = $grid[$cl['class_id']][$ym][$sid] ?? 0; $rowTotal += (int)$val; ?>
              <td class="text-center"><?= (int)$val ?></td>
            <?php endforeach; ?>
            <td class="text-center font-weight-bold"><?= (int)$rowTotal ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th>Total</th>
          <?php
            foreach ($sessionIds as $sid) {
              $col = 0;
              foreach ($months as $ym) $col += (int)($grid[$cl['class_id']][$ym][$sid] ?? 0);
              echo '<th class="text-center">'.(int)$col.'</th>';
            }
            $grand = 0;
            foreach ($months as $ym) foreach ($sessionIds as $sid) $grand += (int)($grid[$cl['class_id']][$ym][$sid] ?? 0);
          ?>
          <th class="text-center"><?= (int)$grand ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
<?php endforeach; ?>

<?= $this->endSection() ?>
