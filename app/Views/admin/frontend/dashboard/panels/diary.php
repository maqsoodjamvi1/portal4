<?php
helper('language');

$activeStudentId = (int) ($activeStudentId ?? 0);
$diaryViewDate    = (string) ($diaryViewDate ?? date('Y-m-d'));
$diaryViewDayName = (string) ($diaryViewDayName ?? '');
$diaryEntries     = $diaryEntries ?? [];
$diaryTermBounds  = $diaryTermBounds;
$diaryNav         = $diaryNav ?? [
    'today' => base_url('student/dashboard/section/diary'),
    'is_viewing_today' => true,
];
$diaryWeekPicker = $diaryWeekPicker ?? [
    'week_pill' => '',
    'prev_url' => null,
    'next_url' => null,
    'week_start' => '',
    'week_end' => '',
    'range_label' => '',
    'days' => [],
];
$dp = $diaryWeekPicker;
$dpPrev = $dp['prev_url'] ?? null;
$dpNext = $dp['next_url'] ?? null;
$dpDays = $dp['days'] ?? [];

$fmtDate = $diaryViewDate !== '' ? date('M j, Y', strtotime($diaryViewDate)) : '';
$diaryBase = base_url('student/dashboard/section/diary');
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h5 class="mb-1">
                    <i class="fa fa-book-open me-2 text-primary"></i>
                    <?= esc(lang('ParentPortal.diary_heading_diary')) ?>
                </h5>
                <div class="text-muted small">
                    <strong><?= esc($diaryViewDayName) ?></strong>
                    <?= esc($fmtDate) ?>
                    <?php if (! empty($diaryNav['is_viewing_today'])): ?>
                        <span class="badge bg-primary ms-1"><?= esc(lang('ParentPortal.diary_nav_today_badge')) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1">
                <?php if ($diaryTermBounds !== null): ?>
                    <div class="text-end small text-muted" style="max-width: 280px;">
                        <?= esc(lang('ParentPortal.diary_term_label')) ?>
                        <?= esc($diaryTermBounds['start'] ?? '') ?> — <?= esc($diaryTermBounds['end'] ?? '') ?>
                    </div>
                <?php endif; ?>
                <?php if (empty($diaryNav['is_viewing_today'])): ?>
                    <a href="<?= esc($diaryNav['today'] ?? $diaryBase . '?date=' . rawurlencode(date('Y-m-d'))) ?>"
                       class="btn btn-sm btn-outline-primary">
                        <?= esc(lang('ParentPortal.diary_nav_today')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($diaryTermBounds === null): ?>
            <p class="mb-0 mt-2 small text-warning">
                <i class="fa fa-info-circle me-1"></i><?= esc(lang('ParentPortal.diary_no_term_bounds')) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="card-body pt-3">
        <?php if ($activeStudentId <= 0): ?>
            <div class="alert alert-warning mb-0"><?= esc(lang('ParentPortal.diary_select_student')) ?></div>
        <?php else: ?>
            <p class="small text-muted mb-3"><?= esc(lang('ParentPortal.diary_week_levels_hint')) ?></p>

            <div class="namaz-tracker-toolbar mb-3">
                <?php if (! empty($dpPrev)): ?>
                    <a href="<?= esc($dpPrev) ?>" class="btn namaz-tracker-nav text-decoration-none">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline"><?= esc(lang('ParentPortal.prayer_prev')) ?></span>
                    </a>
                <?php else: ?>
                    <span class="btn namaz-tracker-nav disabled opacity-50" aria-disabled="true" tabindex="-1">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline"><?= esc(lang('ParentPortal.prayer_prev')) ?></span>
                    </span>
                <?php endif; ?>

                <div class="namaz-tracker-week-pill text-center flex-grow-1 mx-2">
                    <span class="namaz-tracker-week-pill__label d-block fw-semibold"><?= esc((string) ($dp['week_pill'] ?? '')) ?></span>
                    <?php if (! empty($dp['range_label'])): ?>
                        <span class="small text-muted d-block"><?= esc((string) $dp['range_label']) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (! empty($dpNext)): ?>
                    <a href="<?= esc($dpNext) ?>" class="btn namaz-tracker-nav text-decoration-none">
                        <span class="d-none d-sm-inline"><?= esc(lang('ParentPortal.prayer_next')) ?></span>
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <span class="btn namaz-tracker-nav disabled opacity-50" aria-disabled="true" tabindex="-1">
                        <span class="d-none d-sm-inline"><?= esc(lang('ParentPortal.prayer_next')) ?></span>
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </span>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-between justify-content-sm-start mb-4">
                <?php foreach ($dpDays as $day): ?>
                    <?php
                    $d = (string) ($day['date'] ?? '');
                    $href = $diaryBase . '?date=' . rawurlencode($d);
                    $enabled = ! empty($day['enabled']);
                    $isSel = ! empty($day['is_selected']);
                    $isTod = ! empty($day['is_today']);
                    $btnClass = $isSel ? 'btn-primary' : 'btn-outline-secondary';
                    ?>
                    <?php if ($enabled): ?>
                        <a href="<?= esc($href) ?>"
                           class="btn btn-sm <?= esc($btnClass) ?> flex-grow-1 flex-sm-grow-0 text-center"
                           style="min-width: 5.5rem;">
                            <span class="d-block fw-semibold"><?= esc($day['day_name'] ?? '') ?></span>
                            <span class="d-block small text-muted"><?= esc($d) ?></span>
                            <?php if ($isTod): ?>
                                <span class="badge bg-light text-primary border mt-1"><?= esc(lang('ParentPortal.diary_nav_today_badge')) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-sm btn-outline-light text-muted disabled flex-grow-1 flex-sm-grow-0 text-center" style="min-width: 5.5rem;">
                            <span class="d-block fw-semibold"><?= esc($day['day_name'] ?? '') ?></span>
                            <span class="d-block small"><?= esc($d) ?></span>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <hr class="my-4">

            <?= view('frontend/dashboard/partials/diary_day_entries', [
                'activeStudentId' => $activeStudentId,
                'diaryDate'       => $diaryViewDate,
                'diaryDayName'    => $diaryViewDayName,
                'diaryEntries'    => $diaryEntries,
                'showOuterCard'   => false,
            ]) ?>
        <?php endif; ?>
    </div>
</div>
