<?php
// expects: $range = ['start' => 'YYYY-MM-DD', 'end' => 'YYYY-MM-DD'] (or null)
?>
<?php if (!empty($range)): ?>
  <div class="small text-muted mb-1">Exam Date Range</div>
  <div><strong><?= esc(date('d-m-Y', strtotime($range['start']))) ?></strong>
    &nbsp;→&nbsp;
    <strong><?= esc(date('d-m-Y', strtotime($range['end']))) ?></strong>
  </div>
<?php else: ?>
  <div class="text-danger">No date range found.</div>
<?php endif; ?>
