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

$diaryBase = base_url('student/dashboard/section/diary');

$nSel = $diaryViewDate !== '' ? (int) date('N', strtotime($diaryViewDate)) : 1;
$abbrKeys = [
    1 => 'day_abbr_mon', 2 => 'day_abbr_tue', 3 => 'day_abbr_wed', 4 => 'day_abbr_thu',
    5 => 'day_abbr_fri', 6 => 'day_abbr_sat', 7 => 'day_abbr_sun',
];
$selDayAbbr = lang('ParentPortal.' . ($abbrKeys[$nSel] ?? 'day_abbr_mon'));
$tsSel = $diaryViewDate !== '' ? strtotime($diaryViewDate) : false;
$selY = $tsSel ? (int) date('Y', $tsSel) : (int) date('Y');
$selDateShort = $tsSel
    ? (($selY !== (int) date('Y')) ? date('j M Y', $tsSel) : date('j M', $tsSel))
    : '';
?>

<div class="card border-0 pp-diary-card shadow-sm mb-4">
    <div class="card-body p-0">
        <?php if ($activeStudentId <= 0): ?>
            <div class="p-3">
                <div class="alert alert-warning mb-0"><?= esc(lang('ParentPortal.diary_select_student')) ?></div>
            </div>
        <?php else: ?>
            <div class="pp-diary-meta-bar px-3 pt-3 pb-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <?php if ($diaryTermBounds !== null): ?>
                    <div class="small text-muted">
                        <?= esc(lang('ParentPortal.diary_term_label')) ?>
                        <?= esc($diaryTermBounds['start'] ?? '') ?> — <?= esc($diaryTermBounds['end'] ?? '') ?>
                    </div>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
                <?php if (empty($diaryNav['is_viewing_today'])): ?>
                    <a href="<?= esc($diaryNav['today'] ?? $diaryBase . '?date=' . rawurlencode(date('Y-m-d'))) ?>"
                       class="btn btn-sm btn-outline-primary">
                        <?= esc(lang('ParentPortal.diary_nav_today')) ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($diaryTermBounds === null): ?>
                <p class="px-3 mb-2 small text-warning mb-0">
                    <i class="fa fa-info-circle me-1"></i><?= esc(lang('ParentPortal.diary_no_term_bounds')) ?>
                </p>
            <?php endif; ?>

            <div class="px-3 pb-2">
                <p class="small text-muted mb-2 mb-sm-3"><?= esc(lang('ParentPortal.diary_week_levels_hint')) ?></p>

                <div class="pp-diary-week-toolbar d-flex align-items-stretch justify-content-between gap-2 mb-2">
                    <?php if (! empty($dpPrev)): ?>
                        <a href="<?= esc($dpPrev, 'attr') ?>" class="btn pp-diary-nav-btn d-flex flex-column align-items-center justify-content-center text-decoration-none flex-shrink-0"
                           aria-label="<?= esc(lang('ParentPortal.diary_nav_prev_week')) ?>">
                            <i class="fa fa-chevron-left pp-diary-nav-btn__icon" aria-hidden="true"></i>
                            <span class="pp-diary-nav-btn__text"><?= esc(lang('ParentPortal.prayer_prev')) ?></span>
                        </a>
                    <?php else: ?>
                        <span class="btn pp-diary-nav-btn pp-diary-nav-btn--disabled d-flex flex-column align-items-center justify-content-center flex-shrink-0" aria-disabled="true" tabindex="-1">
                            <i class="fa fa-chevron-left pp-diary-nav-btn__icon" aria-hidden="true"></i>
                            <span class="pp-diary-nav-btn__text"><?= esc(lang('ParentPortal.prayer_prev')) ?></span>
                        </span>
                    <?php endif; ?>

                    <div class="pp-diary-week-pill flex-grow-1 d-flex align-items-center justify-content-center text-center px-2">
                        <span class="pp-diary-week-pill__label fw-semibold"><?= esc((string) ($dp['week_pill'] ?? '')) ?></span>
                    </div>

                    <?php if (! empty($dpNext)): ?>
                        <a href="<?= esc($dpNext, 'attr') ?>" class="btn pp-diary-nav-btn d-flex flex-column align-items-center justify-content-center text-decoration-none flex-shrink-0"
                           aria-label="<?= esc(lang('ParentPortal.diary_nav_next_week')) ?>">
                            <span class="pp-diary-nav-btn__text"><?= esc(lang('ParentPortal.prayer_next')) ?></span>
                            <i class="fa fa-chevron-right pp-diary-nav-btn__icon" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <span class="btn pp-diary-nav-btn pp-diary-nav-btn--disabled d-flex flex-column align-items-center justify-content-center flex-shrink-0" aria-disabled="true" tabindex="-1">
                            <span class="pp-diary-nav-btn__text"><?= esc(lang('ParentPortal.prayer_next')) ?></span>
                            <i class="fa fa-chevron-right pp-diary-nav-btn__icon" aria-hidden="true"></i>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (empty($dpDays)): ?>
                    <p class="small text-muted mb-3"><?= esc(lang('ParentPortal.diary_no_days_in_week')) ?></p>
                <?php else: ?>
                <div class="pp-diary-day-scroll mb-3">
                    <div class="pp-diary-day-row d-flex flex-nowrap gap-1 gap-sm-2 justify-content-between">
                        <?php foreach ($dpDays as $day): ?>
                            <?php
                            $d = (string) ($day['date'] ?? '');
                            $href = $diaryBase . '?date=' . rawurlencode($d);
                            $enabled = ! empty($day['enabled']);
                            $isSel = ! empty($day['is_selected']);
                            $isTod = ! empty($day['is_today']);
                            $abbr = (string) ($day['day_abbr'] ?? ($day['day_short'] ?? ''));
                            $dshort = (string) ($day['date_short'] ?? '');
                            if ($dshort === '' && $d !== '') {
                                $t = strtotime($d);
                                $dshort = $t ? ((date('Y', $t) !== date('Y')) ? date('j M Y', $t) : date('j M', $t)) : '';
                            }
                            $btnClass = $isSel ? 'pp-diary-day-chip--active' : 'pp-diary-day-chip--idle';
                            ?>
                            <?php if ($enabled): ?>
                                <a href="<?= esc($href, 'attr') ?>"
                                   class="pp-diary-day-chip <?= esc($btnClass) ?> text-center text-decoration-none flex-grow-1 flex-shrink-0">
                                    <span class="pp-diary-day-chip__dow d-block"><?= esc($abbr) ?></span>
                                    <span class="pp-diary-day-chip__date d-block"><?= esc($dshort) ?></span>
                                    <?php if ($isTod): ?>
                                        <span class="pp-diary-day-chip__badge"><?= esc(lang('ParentPortal.diary_nav_today_badge')) ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php else: ?>
                                <span class="pp-diary-day-chip pp-diary-day-chip--disabled text-center flex-grow-1 flex-shrink-0">
                                    <span class="pp-diary-day-chip__dow d-block"><?= esc($abbr) ?></span>
                                    <span class="pp-diary-day-chip__date d-block"><?= esc($dshort) ?></span>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <h3 class="pp-diary-section-heading h6 mb-3 pb-2 border-bottom">
                    <i class="fa fa-book-open text-primary me-2" aria-hidden="true"></i>
                    <span class="fw-bold"><?= esc(lang('ParentPortal.diary_heading_diary')) ?></span>
                    <span class="text-muted fw-normal"> · </span>
                    <span class="fw-semibold"><?= esc($selDayAbbr) ?></span>
                    <span class="text-muted fw-normal"> </span>
                    <span class="text-muted"><?= esc($selDateShort) ?></span>
                    <?php if (! empty($diaryNav['is_viewing_today'])): ?>
                        <span class="badge bg-primary ms-1 align-middle"><?= esc(lang('ParentPortal.diary_nav_today_badge')) ?></span>
                    <?php endif; ?>
                </h3>

                <?= view('frontend/dashboard/partials/diary_day_entries', [
                    'activeStudentId' => $activeStudentId,
                    'diaryDate'       => $diaryViewDate,
                    'diaryDayName'    => $diaryViewDayName,
                    'diaryEntries'    => $diaryEntries,
                    'showOuterCard'   => false,
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
