<?php
$workingDays = $workingDays ?? null;
$cntA        = $cntA ?? null;
$cntL        = $cntL ?? null;
$cntLC       = $cntLC ?? null;
$cntEL       = $cntEL ?? null;
$dues        = (float) ($dues ?? 0);

$hasMetrics = $workingDays !== null || $cntA !== null || $cntL !== null || $cntLC !== null || $cntEL !== null;
if (!$hasMetrics && $dues <= 0) {
    return;
}
?>
<div class="admit-attendance-strip english-text<?= $dues > 0 ? ' admit-attendance-strip--dues' : '' ?>">
  <?php if ($dues > 0): ?>
    <div class="due-badge" title="Remaining dues">
      <span><?= number_format($dues) ?></span>
    </div>
  <?php endif; ?>
  <span class="admit-attendance-title"><i class="fas fa-clipboard-check" aria-hidden="true"></i> Attendance Summary</span>
  <span class="admit-attendance-items">
    <?php if ($workingDays !== null): ?>
      <span class="admit-attendance-item" title="Attendance records in the exam term date range">
        <b>Total Working Day</b> <?= (int) $workingDays ?>
      </span>
    <?php endif; ?>
    <?php if ($cntA !== null): ?>
      <span class="admit-attendance-item" title="Absent (status A, distinct days)">
        <b>Absent Count</b> <?= (int) $cntA ?>
      </span>
    <?php endif; ?>
    <?php if ($cntL !== null): ?>
      <span class="admit-attendance-item" title="Late (status L)">
        <b>Late Count</b> <?= (int) $cntL ?>
      </span>
    <?php endif; ?>
    <?php if ($cntEL !== null): ?>
      <span class="admit-attendance-item" title="Early left (status EL)">
        <b>Early Left Count</b> <?= (int) $cntEL ?>
      </span>
    <?php endif; ?>
    <?php if ($cntLC !== null): ?>
      <span class="admit-attendance-item" title="Leave (status LC)">
        <b>Leave Count</b> <?= (int) $cntLC ?>
      </span>
    <?php endif; ?>
  </span>
</div>
