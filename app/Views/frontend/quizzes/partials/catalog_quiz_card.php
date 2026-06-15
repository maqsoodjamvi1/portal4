<?php
/**
 * @var object $q
 * @var string $sidQs
 * @var array<string, array{0: string, 1: string}> $statusLabels
 */
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
