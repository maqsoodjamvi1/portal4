<?= $this->extend('board_prep/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4" style="max-width: 820px;">

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center py-4">
      <div class="text-muted small text-uppercase">Your score</div>
      <div class="display-4 fw-bold <?= $percent >= 50 ? 'text-success' : 'text-danger' ?>"><?= $percent ?>%</div>
      <div class="h5 mb-3"><?= esc($quiz->title) ?></div>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <span class="badge text-bg-success px-3 py-2"><?= (int) $correct ?> correct</span>
        <span class="badge text-bg-danger px-3 py-2"><?= (int) $wrong ?> wrong</span>
        <span class="badge text-bg-secondary px-3 py-2"><?= (int) $blank ?> unanswered</span>
        <span class="badge text-bg-light border px-3 py-2"><?= (int) $total ?> total</span>
      </div>
    </div>
  </div>

  <div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between">
    <div class="pe-3">
      <strong><i class="fas fa-save me-1"></i>This result wasn't saved.</strong>
      <div class="small">Sign up to store your scores, review answers anytime, and track your progress.</div>
    </div>
    <div class="mt-2 mt-md-0">
      <a href="<?= esc($loginUrl) ?>" class="btn btn-outline-primary btn-sm">Log in</a>
      <a href="<?= esc($signupUrl) ?>" class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i>Sign up to save</a>
    </div>
  </div>

  <?php if (! empty($showSolution)) : ?>
    <h2 class="h6 text-muted mt-4 mb-2">Review answers</h2>
    <?php foreach ($review as $r) : ?>
      <div class="card border-0 shadow-sm mb-2">
        <div class="card-body py-2">
          <div class="fw-semibold mb-2">
            <?= (int) $r['n'] ?>. <?= esc($r['question']) ?>
            <?php if ($r['state'] === 'correct') : ?>
              <span class="badge text-bg-success ms-1">Correct</span>
            <?php elseif ($r['state'] === 'wrong') : ?>
              <span class="badge text-bg-danger ms-1">Wrong</span>
            <?php else : ?>
              <span class="badge text-bg-secondary ms-1">Skipped</span>
            <?php endif; ?>
          </div>
          <?php foreach ($r['options'] as $opt) : ?>
            <?php
              $isCorrect = ($opt['key'] === $r['correct']);
              $isGiven   = ($opt['key'] === $r['given']);
              $cls = $isCorrect ? 'text-success fw-semibold' : ($isGiven ? 'text-danger' : 'text-muted');
            ?>
            <div class="small <?= $cls ?>">
              <strong><?= esc($opt['key']) ?>.</strong> <?= esc($opt['text']) ?>
              <?php if ($isCorrect) : ?><i class="fas fa-check ms-1"></i><?php endif; ?>
              <?php if ($isGiven && ! $isCorrect) : ?><i class="fas fa-times ms-1"></i> (your answer)<?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="d-flex justify-content-between mt-4 mb-5">
    <a href="<?= esc($dashboardUrl) ?>" class="btn btn-outline-secondary"><i class="fas fa-th-list me-1"></i>More quizzes</a>
    <a href="<?= esc($replayUrl) ?>" class="btn btn-outline-primary"><i class="fas fa-redo me-1"></i>Play again</a>
  </div>

</div>
<?= $this->endSection() ?>
