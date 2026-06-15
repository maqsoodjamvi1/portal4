<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php helper(['server', 'language']); ?>
<?php
$ppDayShortToKey = [
    'Sun' => 'day_sunday',
    'Mon' => 'day_monday',
    'Tue' => 'day_tuesday',
    'Wed' => 'day_wednesday',
    'Thu' => 'day_thursday',
    'Fri' => 'day_friday',
    'Sat' => 'day_saturday',
];
$ppDsSyllView = json_encode(lang('ParentPortal.datesheet_view_syllabus'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
$ppDsSyllHide = json_encode(lang('ParentPortal.datesheet_hide_syllabus'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<section class="content parent-subpage-content pt-2 parent-portal-datesheet">
    <div class="container-fluid px-2 px-md-3">
        <div class="ds-page-layout">
            <aside class="ds-page-layout__filter ds-sticky-filter-mobile" aria-label="<?= esc(lang('ParentPortal.datesheet_aria_student_selection')) ?>">
                <div class="ds-datesheet-filter">
                    <?= view('frontend/partials/parent_child_selector', [
                        'children'         => $children ?? [],
                        'activeStudentId'  => (int) ($active_student_id ?? 0),
                        'returnPath'       => 'student/datesheet',
                    ]) ?>
                </div>
            </aside>

            <div class="ds-page-layout__content" aria-label="<?= esc(lang('ParentPortal.datesheet_aria_schedule')) ?>">
        <?php if ($active_student_id > 0 && isset($student)): ?>
            <div id="datesheet-section" class="ds-datesheet-content parent-subpage-panel ds-datesheet-content-panel" aria-label="<?= esc(lang('ParentPortal.datesheet_aria_panel')) ?>">
                <div class="parent-subpage-title-row align-items-center flex-wrap">
                    <h2 class="parent-subpage-title mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        <?php if (! empty($exam_name)): ?>
                            <?= esc(lang('ParentPortal.datesheet_page_title_exam', ['exam' => (string) $exam_name])) ?>
                        <?php else: ?>
                            <?= lang('ParentPortal.datesheet_page_title') ?>
                        <?php endif; ?>
                    </h2>
                    <?php
                    $__cn = trim((string) ($student['class_name'] ?? ''));
                    $__sn = trim((string) ($student['section_name'] ?? ''));
                    $__clsBadge = ($__cn !== '' && $__sn !== '') ? ($__cn . ' - ' . $__sn) : ($__cn !== '' ? $__cn : $__sn);
                    ?>
                    <?php if ($__clsBadge !== ''): ?>
                        <span class="badge text-bg-primary ms-md-2 mt-1 mt-md-0"><?= esc($__clsBadge) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($datesheet) && empty($error)): ?>
                    <div class="datesheet-timeline">
                                <?php
                                $counter = 0;
                                ?>

                                <?php foreach ($datesheet as $date => $exams): ?>
                                    <?php
                                        $dateObj = new DateTime($date);
                                        $dayShort = $dateObj->format('D');
                                        $dayKey = $ppDayShortToKey[$dayShort] ?? 'day_sunday';
                                        $dayFull = lang('ParentPortal.' . $dayKey);
                                        $formattedDate = $dateObj->format('d F Y');
                                        $dateLine = $formattedDate . ' ' . $dayFull;
                                        $paperCount = count($exams);
                                        $counter++;
                                    ?>

                                    <div class="timeline-item mb-4" id="date-<?= $date ?>">
                                        <div class="timeline-date bg-primary text-white p-3 rounded-top">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                <h5 class="mb-0 pe-2"><i class="far fa-calendar me-2"></i><?= esc($dateLine) ?></h5>
                                                <span class="badge text-bg-light mt-1 mt-sm-0"><?= $paperCount === 1 ? lang('ParentPortal.datesheet_badge_one_paper') : lang('ParentPortal.datesheet_badge_n_papers', ['count' => (int) $paperCount]) ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="timeline-exams border rounded-bottom">
                                            <?php foreach ($exams as $exam): ?>
                                                <div class="exam-card p-3 border-bottom">
                                                    <div class="exam-card-head-ds">
                                                        <div class="exam-card-head-main min-w-0">
                                                            <h6 class="exam-card-subject mb-0">
                                                                <i class="fas fa-book me-2 text-primary"></i><?= esc($exam['subject_name']) ?>
                                                            </h6>
                                                            <?php if ((int) ($exam['total_marks'] ?? 0) > 0): ?>
                                                                <div class="exam-card-marks text-muted small text-nowrap">
                                                                    <span class="d-none d-sm-inline"><i class="fas fa-star text-warning me-1"></i><?= esc(lang('ParentPortal.datesheet_total_marks')) ?> <?= (int) $exam['total_marks'] ?></span>
                                                                    <span class="d-sm-none" title="<?= esc(lang('ParentPortal.datesheet_total_marks_title')) ?>"><i class="fas fa-star text-warning me-1"></i><?= (int) $exam['total_marks'] ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <button class="btn btn-outline-info btn-sm syllabus-toggle exam-card-head-btn text-nowrap"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#syllabus-<?= $date . '-' . $exam['subject_id'] ?>"
                                                                aria-expanded="false"
                                                                aria-controls="syllabus-<?= $date . '-' . $exam['subject_id'] ?>">
                                                            <i class="fas fa-book-open me-1 syllabus-toggle-icon" aria-hidden="true"></i><span class="syllabus-toggle-label"><?= lang('ParentPortal.datesheet_view_syllabus') ?></span>
                                                        </button>
                                                    </div>
                                                    <?php if (!empty($exam['start_time'])): ?>
                                                        <div class="mt-2 small text-muted">
                                                            <i class="far fa-clock me-1"></i>
                                                            <?= date('h:i A', strtotime($exam['start_time'])) ?>
                                                            <?php if (!empty($exam['end_time'])): ?>
                                                                &ndash; <?= date('h:i A', strtotime($exam['end_time'])) ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($exam['room_no'])): ?>
                                                        <div class="mt-1 small text-muted">
                                                            <i class="fas fa-door-open me-1"></i><?= esc(lang('ParentPortal.datesheet_room')) ?>: <?= esc($exam['room_no']) ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Syllabus (Collapsible) -->
                                                    <div class="collapse mt-3" id="syllabus-<?= $date . '-' . $exam['subject_id'] ?>">
                                                        <div class="card card-body bg-light">
                                                            <h6 class="text-primary mb-2"><i class="fas fa-list me-1"></i> <?= lang('ParentPortal.datesheet_exam_syllabus') ?></h6>
                                                            <?php 
                                                                $syllabus = $exam['syllabus'] ?? '';
                                                                $isUrdu = preg_match('/\p{Arabic}/u', $syllabus);
                                                                $syllClass = $isUrdu ? 'urdu-text' : 'english-text';
                                                            ?>
                                                            <div class="<?= $syllClass ?>">
                                                                <?= nl2br(esc(strip_tags(html_entity_decode($syllabus)))) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                        <?php else: ?>
                            <div class="alert alert-info text-center mb-0">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h5><?= lang('ParentPortal.datesheet_no_data_title') ?></h5>
                                <p class="mb-0"><?= esc($error ?? lang('ParentPortal.datesheet_no_data_default')) ?></p>
                            </div>
                        <?php endif; ?>
            </div>
        <?php elseif ($active_student_id == 0): ?>
            <div class="parent-subpage-panel ds-datesheet-content-panel text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4><?= lang('ParentPortal.datesheet_select_student_title') ?></h4>
                    <p class="text-muted mb-0"><?= lang('ParentPortal.datesheet_select_student_help') ?></p>
            </div>
        <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Timeline */
.timeline-item {
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}

@media (max-width: 768px) {
    .timeline-item::before {
        left: 0;
    }
}

.timeline-date {
    position: relative;
    z-index: 2;
}

/* Exam Cards */
.exam-card {
    background: #fff;
    transition: all 0.3s ease;
}

.exam-card:hover {
    background: #f8f9fa;
}

/* Syllabus */
.urdu-text {
    direction: rtl;
    text-align: right;
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Segoe UI', sans-serif;
    font-size: 1.1rem;
    line-height: 1.8;
}

.english-text {
    direction: ltr;
    text-align: left;
}

/* Avatar */
.avatar-placeholder {
    font-size: 1.5rem;
}

/* Subject + marks (left); View Syllabus (right) — uses row space on mobile and desktop */
.exam-card-head-ds {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.5rem 0.75rem;
}
.exam-card-head-main {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.exam-card-head-btn {
    flex-shrink: 0;
    align-self: center;
    margin-inline-start: auto;
}
.exam-card-head-ds .syllabus-toggle {
    padding: 0.25rem 0.55rem;
    font-size: 0.8rem;
}

/* Print Styles */
@media print {
    .parent-dash-students-card,
    .content-header,
    .breadcrumb,
    .syllabus-toggle {
        display: none !important;
    }
    
    .timeline-item {
        break-inside: avoid;
    }
    
    .collapse {
        display: block !important;
    }
}
</style>

<script>
var PP_DS_SYLL_VIEW = <?= $ppDsSyllView ?>;
var PP_DS_SYLL_HIDE = <?= $ppDsSyllHide ?>;
$(document).ready(function() {
    $('.datesheet-timeline .collapse').on('shown.bs.collapse', function() {
        var sel = '#' + $(this).attr('id');
        var $btn = $('.syllabus-toggle[data-bs-target="' + sel + '"]');
        $btn.attr('aria-expanded', 'true');
        $btn.find('.syllabus-toggle-icon').removeClass('fa-book-open').addClass('fa-book');
        $btn.find('.syllabus-toggle-label').text(PP_DS_SYLL_HIDE);
    }).on('hidden.bs.collapse', function() {
        var sel = '#' + $(this).attr('id');
        var $btn = $('.syllabus-toggle[data-bs-target="' + sel + '"]');
        $btn.attr('aria-expanded', 'false');
        $btn.find('.syllabus-toggle-icon').removeClass('fa-book').addClass('fa-book-open');
        $btn.find('.syllabus-toggle-label').text(PP_DS_SYLL_VIEW);
    });
});
</script>

<?= $this->endSection() ?>