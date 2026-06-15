<?php
/** @var array<string,mixed> $summary */
$summary = $summary ?? [];
$wd = (int) ($summary['working_days'] ?? 0);
$pPct = $summary['present_pct'] ?? null;
$arPct = $summary['attendance_rate_pct'] ?? null;
?>
<div class="att-term-summary row g-0 text-center text-md-start">
    <div class="col-6 col-md-3 mb-2 mb-md-0">
        <div class="att-kpi-label text-muted small"><?= lang('ParentPortal.attendance_working_days') ?></div>
        <div class="att-kpi-value fw-bold"><?= $wd ?></div>
    </div>
    <div class="col-6 col-md-3 mb-2 mb-md-0">
        <div class="att-kpi-label text-muted small"><?= lang('ParentPortal.attendance_present_pct') ?></div>
        <div class="att-kpi-value fw-bold text-success"><?= $pPct !== null ? esc($pPct) . '%' : '—' ?></div>
    </div>
    <div class="col-6 col-md-3 mb-2 mb-md-0">
        <div class="att-kpi-label text-muted small"><?= lang('ParentPortal.attendance_rate_pct') ?></div>
        <div class="att-kpi-value fw-bold text-primary"><?= $arPct !== null ? esc($arPct) . '%' : '—' ?></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="att-kpi-label text-muted small d-none d-md-block">&nbsp;</div>
        <div class="small text-muted">
            <?= lang('ParentPortal.attendance_legend_absent') ?>: <strong><?= (int) ($summary['absent'] ?? 0) ?></strong>
            <span class="mx-1">·</span>
            <?= lang('ParentPortal.attendance_leave') ?>: <strong><?= (int) ($summary['leave'] ?? 0) ?></strong>
            <span class="mx-1">·</span>
            <?= lang('ParentPortal.attendance_no_record') ?>: <strong><?= (int) ($summary['no_record'] ?? 0) ?></strong>
        </div>
    </div>
</div>
<?php if ($wd > 0 && $pPct !== null): ?>
<?php $wPct = min(100.0, max(0.0, (float) $pPct)); ?>
<div class="progress mt-2 att-term-progress" style="height: 8px;">
    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $wPct ?>%;" aria-valuenow="<?= $wPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<?php endif; ?>
