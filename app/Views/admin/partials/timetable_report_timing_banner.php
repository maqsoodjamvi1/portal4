<?php
$t = $timing_type_name ?? '';
$d = $working_days_display ?? '';
if ($t === '' && $d === '') {
    return;
}
?>
<div class="alert alert-info mb-3 small tt-timing-banner">
    <?php if ($t !== ''): ?>
        <div><strong>School timing:</strong> <?= esc($t !== '' ? $t : 'Campus schedule') ?></div>
    <?php endif; ?>
    <?php if ($d !== ''): ?>
        <div class="mt-1"><strong>School days in this report</strong> (check-in ≠ check-out): <?= esc($d) ?></div>
    <?php endif; ?>
</div>
