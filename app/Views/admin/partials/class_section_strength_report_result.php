<div class="strength-report-wrap">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h5 class="mb-1"><?= esc($viewLabel ?? 'Strength Report') ?></h5>
            <div class="small text-muted">
                Session: <strong><?= esc($sessionLabel ?? '') ?></strong>
                <?php if (! empty($campusLabel)): ?>
                    &middot; Campus: <strong><?= esc($campusLabel) ?></strong>
                <?php endif; ?>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="printStrengthReportBtn">
            <i class="fas fa-print me-1"></i> Print
        </button>
    </div>

    <div class="strength-report-print-header d-none d-print-block text-center mb-3">
        <?php if (! empty($campusLabel)): ?>
            <div class="fw-bold"><?= esc($campusLabel) ?></div>
        <?php endif; ?>
        <h4 class="mb-1"><?= esc($viewLabel ?? 'Strength Report') ?></h4>
        <div class="small">
            Session: <?= esc($sessionLabel ?? '') ?>
            &middot; Printed: <?= esc($printedAt ?? date('d M Y, h:i A')) ?>
        </div>
    </div>

    <div class="table-responsive">
        <?= $tableHtml ?? '' ?>
    </div>
</div>
