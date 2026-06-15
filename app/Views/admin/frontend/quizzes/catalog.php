<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
$isParent = (($role ?? '') === 'parent');
$activeId = (int) ($active_student_id ?? 0);
$sidQs    = $isParent && $activeId > 0 ? ('?sid=' . $activeId) : '';
?>
<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<section class="content parent-subpage-content pt-2">
    <div class="container-fluid px-2 px-md-3">
        <?php if ($isParent): ?>
            <div class="ds-page-layout">
                <aside class="ds-page-layout__filter ds-sticky-filter-mobile" aria-label="Student selection">
                    <div class="ds-datesheet-filter">
                        <?= view('frontend/partials/parent_child_selector', [
                            'children'        => $children ?? [],
                            'activeStudentId' => $activeId,
                            'returnPath'      => 'student/quizzes/all',
                        ]) ?>
                    </div>
                </aside>
                <div class="ds-page-layout__content">
        <?php endif; ?>

        <div class="parent-subpage-panel">
            <div class="parent-subpage-title-row flex-wrap">
                <h2 class="parent-subpage-title mb-0">
                    <i class="fa fa-question-circle text-primary me-2"></i> All quizzes
                </h2>
            </div>

            <?php if (! empty($err)): ?>
                <div class="alert alert-warning mt-3"><?= esc($err) ?></div>
            <?php endif; ?>

            <?php if (empty($quizzes)): ?>
                <div class="alert alert-info mt-3 mb-0 text-center">
                    No published quizzes for this class yet.
                </div>
            <?php else: ?>
                <div class="row mt-3 quiz-catalog-grid">
                    <?php foreach ($quizzes as $q): ?>
                        <?php
                        $status       = $q->catalog_status ?? 'open';
                        $attemptsUsed = (int) ($q->attempts_used ?? 0);
                        $maxAttempts  = (int) ($q->max_attempts ?? 0);
                        $canStart     = ($status === 'open');
                        $practiceOk   = ($status === 'open' || $status === 'maxed');
                        $subjectLabel = trim((string) ($q->subject_short_name ?? $q->subject_name ?? 'Subject'));
                        if ($subjectLabel === '') {
                            $subjectLabel = 'Subject';
                        }
                        $startUrl    = site_url('student/quizzes/start/' . (int) $q->quiz_id . $sidQs);
                        $practiceUrl = site_url('student/quizzes/practice/' . (int) $q->quiz_id . $sidQs);
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 mb-2">
                            <div class="card quiz-catalog-card border shadow-sm h-100">
                                <div class="card-body d-flex flex-column p-2">
                                    <h6 class="quiz-catalog-subject mb-2 mb-md-1 fw-bold text-dark" title="<?= esc($subjectLabel) ?>">
                                        <?= esc($subjectLabel) ?>
                                    </h6>
                                    <div class="small text-muted mb-2">
                                        Attempts <?= $attemptsUsed ?> / <?= $maxAttempts ?>
                                    </div>
                                    <div class="mt-auto d-flex flex-nowrap quiz-catalog-actions">
                                        <?php if ($canStart): ?>
                                            <a class="btn btn-primary btn-sm flex-fill me-1" href="<?= $startUrl ?>">Start</a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary btn-sm flex-fill me-1" disabled title="Not available">Start</button>
                                        <?php endif; ?>
                                        <?php if ($practiceOk): ?>
                                            <a class="btn btn-outline-secondary btn-sm flex-fill" href="<?= $practiceUrl ?>">Practice</a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" disabled title="Not available">Practice</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($isParent): ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.quiz-catalog-subject {
    font-size: 0.9rem;
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
}
.quiz-catalog-actions .btn {
    padding: 0.22rem 0.3rem;
    font-size: 0.72rem;
}
</style>

<?= $this->endSection() ?>
