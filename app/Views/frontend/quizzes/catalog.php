<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
$isParent = (($role ?? '') === 'parent');
$activeId = (int) ($active_student_id ?? 0);
$sidQs    = $isParent && $activeId > 0 ? ('?sid=' . $activeId) : '';
$statusLabels = [
    'open'     => ['Open', 'success'],
    'upcoming' => ['Upcoming', 'info'],
    'ended'    => ['Ended', 'secondary'],
    'maxed'    => ['Attempts used', 'warning'],
];
$termGroups           = $termGroups ?? [];
$currentTermSessionId = (int) ($currentTermSessionId ?? 0);
$hasAnyQuiz           = ! empty($quizzes);
$hasAnyResult         = ! empty($hasAnyResult);
$resultsUrl           = site_url('student/quizzes/my-results') . $sidQs;

$renderCatalogQuizCard = static function ($q) use ($sidQs, $statusLabels, $resultsUrl): void {
    $status       = $q->catalog_status ?? 'open';
    $attemptsUsed = (int) ($q->attempts_used ?? 0);
    $maxAttempts  = (int) ($q->max_attempts ?? 0);
    $isAdaptive   = ! empty($q->is_adaptive);
    $canStart     = ($status === 'open');
    $practiceOk   = ($status === 'open' || $status === 'maxed' || $isAdaptive);
    $subjectLabel = trim((string) ($q->subject_short_name ?? $q->subject_name ?? 'Subject'));
    if ($subjectLabel === '') {
        $subjectLabel = 'Subject';
    }
    $quizTitle = trim((string) ($q->title ?? 'Quiz'));
    $qid       = (int) $q->quiz_id;

    $startNormal = site_url('student/quizzes/start/' . $qid) . $sidQs;
    $startKids   = site_url('student/quizzes/start/' . $qid)
        . ($sidQs !== '' ? $sidQs . '&kids=1' : '?kids=1');
    $practiceUrl = site_url('student/quizzes/practice/' . $qid) . $sidQs;

    $attemptQs = (int) ($q->attempt_questions ?? 0);
    $st        = $statusLabels[$status] ?? ['Unknown', 'secondary'];
    $levels    = $q->levels ?? [];
    $stuAttempts = $q->student_attempts ?? [];
    $hasResults  = $stuAttempts !== [];
    ?>
    <div class="col-12 col-sm-6 col-lg-4 mb-3">
        <article class="card quiz-catalog-card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge text-bg-<?=  esc($st[1]) ?> quiz-catalog-status"><?= esc($st[0]) ?></span>
                    <?php if ($isAdaptive): ?>
                        <span class="badge text-bg-primary">Adaptive</span>
                    <?php endif; ?>
                </div>

                <h6 class="quiz-catalog-title fw-bold text-dark mb-1" title="<?= esc($quizTitle) ?>">
                    <?= esc($quizTitle) ?>
                </h6>
                <p class="quiz-catalog-subject text-muted small mb-2 mb-md-3" title="<?= esc($subjectLabel) ?>">
                    <i class="fas fa-book-open me-1"></i><?= esc($subjectLabel) ?>
                </p>

                <ul class="list-unstyled small quiz-catalog-meta mb-2">
                    <li class="mb-1">
                        <i class="far fa-clock text-primary me-1"></i>
                        <strong>Duration:</strong>
                        <?= esc($q->duration_label ?? 'No time limit') ?>
                        <?php if (! empty($q->duration_detail)): ?>
                            <span class="text-muted d-block ps-4"><?= esc($q->duration_detail) ?></span>
                        <?php endif; ?>
                    </li>
                    <li class="mb-1">
                        <i class="fas fa-list-ol text-primary me-1"></i>
                        <strong>Questions:</strong>
                        <?php if ($isAdaptive && $attemptQs > 0): ?>
                            <?= (int) $attemptQs ?> per level
                            <?php if ((int) ($q->questions_count ?? 0) > 0): ?>
                                <span class="text-muted">(<?= (int) $q->questions_count ?> total in quiz)</span>
                            <?php endif; ?>
                        <?php elseif ($attemptQs > 0): ?>
                            <?= (int) $attemptQs ?> to attempt
                        <?php else: ?>
                            <span class="text-muted">Not set</span>
                        <?php endif; ?>
                    </li>
                    <li class="mb-1">
                        <i class="fas fa-redo text-primary me-1"></i>
                        <strong>Attempts:</strong>
                        <?= $attemptsUsed ?> / <?= $maxAttempts > 0 ? $maxAttempts : '∞' ?>
                    </li>
                </ul>

                <?php if ($isAdaptive && $levels !== []): ?>
                    <div class="quiz-catalog-levels mb-3">
                        <div class="small fw-bold text-secondary mb-1">Levels</div>
                        <div class="d-flex flex-wrap" style="gap:6px;">
                            <?php foreach ($levels as $lvl): ?>
                                <span class="quiz-level-chip" title="Pass <?= esc((string) $lvl['pass_pct']) ?>%">
                                    <?= esc($lvl['level_name']) ?>
                                    <?php if ((int) ($lvl['questions'] ?? 0) > 0): ?>
                                        <small class="text-muted">· <?= (int) $lvl['questions'] ?> Q</small>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($q->last_score !== null): ?>
                    <p class="small text-muted mb-2">
                        Last score: <strong><?= esc((string) $q->last_score) ?></strong>
                    </p>
                <?php endif; ?>

                <?php if ($hasResults): ?>
                    <div class="quiz-card-results mb-3 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="small fw-bold text-secondary mb-1">Your results</div>
                        <ul class="list-unstyled small mb-2">
                            <?php foreach ($stuAttempts as $att): ?>
                                <li class="d-flex justify-content-between py-1 border-bottom">
                                    <span>Attempt <?= (int) ($att['attempt_no'] ?? 0) ?></span>
                                    <span>
                                        <?php if ($att['percentage'] !== null): ?>
                                            <strong><?= esc((string) $att['percentage']) ?>%</strong>
                                        <?php else: ?>
                                            <strong><?= esc((string) ($att['score'] ?? 0)) ?></strong>
                                        <?php endif; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= esc($resultsUrl) ?>#quiz-results-<?= $qid ?>" class="btn btn-outline-info btn-sm w-100 mb-0">
                            <i class="fas fa-chart-bar me-1"></i> View results
                        </a>
                    </div>
                <?php endif; ?>

                <div class="mt-auto quiz-catalog-actions">
                    <div class="small text-muted fw-bold mb-1">Start quiz</div>
                    <div class="btn-group-vertical w-100" role="group">
                        <?php if ($canStart): ?>
                            <a class="btn btn-primary btn-sm mb-1" href="<?= esc($startNormal) ?>">
                                <i class="fas fa-play me-1"></i> Normal quiz
                            </a>
                            <a class="btn btn-warning btn-sm text-dark mb-1" href="<?= esc($startKids) ?>">
                                <i class="fas fa-child me-1"></i> Kids mode
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary btn-sm mb-1" disabled title="Quiz not available">
                                <i class="fas fa-play me-1"></i> Normal quiz
                            </button>
                            <button type="button" class="btn btn-warning btn-sm text-dark mb-1" disabled>
                                <i class="fas fa-child me-1"></i> Kids mode
                            </button>
                        <?php endif; ?>
                        <?php if ($practiceOk): ?>
                            <a class="btn btn-outline-secondary btn-sm" href="<?= esc($practiceUrl) ?>">
                                <i class="fas fa-dumbbell me-1"></i> Practice (no score)
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="fas fa-dumbbell me-1"></i> Practice (no score)
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </article>
    </div>
    <?php
};
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
                <p class="text-muted small mb-0 mt-1 w-100">Choose how you want to take each quiz: normal, practice (no score), or kids-friendly layout.</p>
                <?php if (! empty($sessionName)): ?>
                    <p class="text-muted small mb-0 mt-1 w-100">
                        <i class="fas fa-graduation-cap me-1"></i><?= esc($sessionName) ?>
                    </p>
                <?php endif; ?>
                <?php if ($hasAnyResult): ?>
                    <a href="<?= esc($resultsUrl) ?>" class="btn btn-info btn-sm mt-2">
                        <i class="fas fa-chart-bar me-1"></i> My quiz results
                    </a>
                <?php endif; ?>
            </div>

            <?php if (! empty($err)): ?>
                <div class="alert alert-warning mt-3"><?= esc($err) ?></div>
            <?php endif; ?>

            <?php if (empty($termGroups) && ! $hasAnyQuiz): ?>
                <div class="alert alert-info mt-3 mb-0 text-center">
                    No published quizzes for this class yet.
                </div>
            <?php elseif (empty($termGroups)): ?>
                <div class="row mt-3 quiz-catalog-grid">
                    <?php foreach ($quizzes as $q): ?>
                        <?php $renderCatalogQuizCard($q); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="quiz-term-accordion mt-3" id="quizTermsAcc">
                    <?php foreach ($termGroups as $group): ?>
                        <?php
                        $tsid        = (int) ($group['term_session_id'] ?? 0);
                        $termKey     = 'quiz_term_' . $tsid;
                        $isCurrent   = ! empty($group['is_current']);
                        $expanded    = $isCurrent;
                        $termLabel   = trim((string) ($group['term_name'] ?? 'Term'));
                        $termShort   = trim((string) ($group['term_short'] ?? ''));
                        if ($termShort !== '' && $termShort !== $termLabel) {
                            $termLabel = $termShort . ' — ' . $termLabel;
                        }
                        $termQuizzes = $group['quizzes'] ?? [];
                        $quizCount   = count($termQuizzes);
                        ?>
                        <div class="card quiz-term-card mb-2 border-0 shadow-sm">
                            <div class="card-header p-0 border-0 bg-white" id="<?= esc($termKey) ?>_h">
                                <button class="btn btn-link w-100 text-start quiz-term-toggle d-flex justify-content-between align-items-center py-3 px-3"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?= esc($termKey) ?>_b"
                                        aria-expanded="<?= $expanded ? 'true' : 'false' ?>"
                                        aria-controls="<?= esc($termKey) ?>_b">
                                    <div>
                                        <strong class="text-dark"><?= esc($termLabel) ?></strong>
                                        <?php if ($isCurrent): ?>
                                            <span class="badge text-bg-primary ms-2">Current term</span>
                                        <?php endif; ?>
                                        <?php if (! empty($group['start_date']) && ! empty($group['end_date'])): ?>
                                            <div class="text-muted small mt-1">
                                                <?= esc(substr((string) $group['start_date'], 0, 10)) ?>
                                                → <?= esc(substr((string) $group['end_date'], 0, 10)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center flex-shrink-0 ms-2">
                                        <span class="badge text-bg-light border me-2"><?= (int) $quizCount ?> quiz<?= $quizCount === 1 ? '' : 'zes' ?></span>
                                        <i class="fas fa-chevron-down text-muted quiz-term-chevron"></i>
                                    </div>
                                </button>
                            </div>
                            <div id="<?= esc($termKey) ?>_b"
                                 class="collapse<?= $expanded ? ' show' : '' ?>"
                                 aria-labelledby="<?= esc($termKey) ?>_h">
                                <div class="card-body pt-0 pb-3 px-2 px-md-3">
                                    <?php if ($termQuizzes === []): ?>
                                        <p class="text-muted small mb-0 py-2 px-2">No quizzes published for this term yet.</p>
                                    <?php else: ?>
                                        <div class="row quiz-catalog-grid">
                                            <?php foreach ($termQuizzes as $q): ?>
                                                <?php $renderCatalogQuizCard($q); ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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
.quiz-catalog-card {
  border-radius: 14px;
  transition: box-shadow .2s ease, transform .2s ease;
}
.quiz-catalog-card:hover {
  box-shadow: 0 8px 24px rgba(0,0,0,.1) !important;
}
.quiz-catalog-title {
  font-size: 1rem;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.quiz-catalog-subject {
  line-height: 1.2;
}
.quiz-catalog-meta li {
  line-height: 1.45;
}
.quiz-catalog-status {
  font-size: .7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
}
.quiz-level-chip {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 999px;
  background: #eef2ff;
  color: #4338ca;
  font-size: .72rem;
  font-weight: 600;
  border: 1px solid #c7d2fe;
}
.quiz-catalog-actions .btn {
  text-align: left;
  font-weight: 600;
}
.quiz-term-card {
  border-radius: 14px;
  overflow: hidden;
}
.quiz-term-toggle {
  text-decoration: none !important;
  color: inherit;
}
.quiz-term-toggle:hover,
.quiz-term-toggle:focus {
  text-decoration: none !important;
  color: inherit;
  background: #f8fafc;
}
.quiz-term-toggle[aria-expanded="true"] .quiz-term-chevron {
  transform: rotate(180deg);
}
.quiz-term-chevron {
  transition: transform .2s ease;
}
@media (min-width: 768px) {
  .quiz-catalog-actions .btn-group-vertical {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 6px;
  }
  .quiz-catalog-actions .btn {
    flex: 1 1 auto;
    margin-bottom: 0 !important;
  }
}
</style>

<?= $this->endSection() ?>
