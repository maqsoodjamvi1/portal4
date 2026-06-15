<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<?php
$portalLang = strtolower(trim((string) (session('language') ?: 'en')));
$isUrdu     = ($portalLang === 'ur');
?>
<div class="content-header parent-subpage-breadcrumb-bar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('student/dashboard') ?>"><?= esc(lang('ParentPortal.breadcrumb_dashboard')) ?></a></li>
                    <li class="breadcrumb-item active"><?= esc($title ?? '') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content parent-subpage-content">
    <div class="container-fluid">
        <?= view('frontend/partials/parent_child_selector', [
            'children'         => $children ?? [],
            'activeStudentId'  => (int) ($activeStudentId ?? 0),
            'returnPath'       => $returnPath ?? ('student/dashboard/section/' . $segment),
        ]) ?>

        <div class="parent-subpage-title-row parent-subpage-title-row--solo<?= (($segment ?? '') === 'prayers') ? ' parent-subpage-title-row--prayers-tight' : '' ?><?= (($segment ?? '') === 'diary') ? ' parent-subpage-title-row--diary-tight' : '' ?>">
            <h2 class="parent-subpage-title">
                <?php if (($segment ?? '') === 'prayers'): ?>
                    <span class="parent-subpage-title-namaz">
                        <i class="fa fa-mosque parent-subpage-title-mosque" aria-hidden="true"></i><?= esc(lang('ParentPortal.section_prayers')) ?>
                    </span>
                <?php elseif (($segment ?? '') === 'diary'): ?>
                    <span class="parent-subpage-title-diary">
                        <i class="fa fa-book-open parent-subpage-title-book" aria-hidden="true"></i><?= esc(lang('ParentPortal.diary_heading_diary')) ?>
                    </span>
                <?php else: ?>
                    <?= esc($title ?? 'Portal') ?>
                <?php endif; ?>
            </h2>
        </div>

        <?= view('frontend/dashboard/panels/' . $segment, [
            'isUrdu'               => $isUrdu ?? false,
            'activeStudentId'      => (int) ($activeStudentId ?? 0),
            'studentInfo'          => $studentInfo ?? null,
            'bmiData'              => $bmiData ?? null,
            'bmiHistory'           => $bmiHistory ?? [],
            'bmiSuggestions'       => $bmiSuggestions ?? null,
            'todayDiary'           => $todayDiary ?? [],
            'diaryViewDate'        => $diaryViewDate ?? date('Y-m-d'),
            'diaryViewDayName'     => $diaryViewDayName ?? '',
            'diaryEntries'         => $diaryEntries ?? [],
            'diaryTermBounds'      => $diaryTermBounds ?? null,
            'diaryNav'             => $diaryNav ?? [],
            'diaryWeekPicker'      => $diaryWeekPicker ?? [
                'week_pill' => '',
                'prev_url' => null,
                'next_url' => null,
                'week_start' => '',
                'week_end' => '',
                'range_label' => '',
                'days' => [],
                'used_term_weeks' => false,
            ],
            'bagPackItems'         => $bagPackItems ?? [],
            'quizSchedule'         => $quizSchedule ?? [],
            'isEligibleForPrayer'  => $isEligibleForPrayer ?? false,
            'isMandatory'          => $isMandatory ?? false,
            'hifzData'             => $hifzData ?? ['enrolled' => false],
        ]) ?>

        <?php if ($segment === 'diary'): ?>
            <?= view('frontend/dashboard/partials/parent_diary_portal_scripts') ?>
        <?php elseif ($segment === 'prayers'): ?>
            <?= csrf_field() ?>
            <?= view('frontend/dashboard/partials/parent_prayer_portal_scripts', [
                'activeStudentId' => (int) ($activeStudentId ?? 0),
            ]) ?>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
