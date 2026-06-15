<?php if (! empty($hifzSummary) && is_array($hifzSummary)): ?>
<div class="card parent-dash-hifz-summary mb-3">
    <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h6 class="mb-1"><i class="fas fa-book-quran text-success me-1"></i> <?= esc(lang('ParentPortal.hifz_summary_title')) ?></h6>
            <div class="small text-muted"><?= esc(lang('ParentPortal.hifz_summary_section', [$hifzSummary['section'] ?? ''])) ?></div>
            <div class="small"><?= esc(lang('ParentPortal.hifz_summary_para', [$hifzSummary['current_para'] ?? ''])) ?></div>
        </div>
        <a href="<?= esc($hifzSummary['url'] ?? base_url('student/dashboard/section/hifz')) ?>" class="btn btn-sm btn-outline-success mt-2 mt-md-0">
            <?= esc(lang('ParentPortal.hifz_view_details')) ?>
        </a>
    </div>
</div>
<?php endif; ?>
