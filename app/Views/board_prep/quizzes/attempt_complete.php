<?php $navActive = 'dashboard'; ?>

<?= $this->extend('board_prep/app_layout') ?>

<?= $this->section('main') ?>

<div class="board-prep-card text-center p-4 p-md-5 mx-auto" style="max-width:520px;">

  <div class="mb-3"><i class="fas fa-check-circle text-success" style="font-size:3rem;"></i></div>

  <h2 class="h4 mb-2">Quiz completed</h2>

  <p class="text-muted mb-1"><?= esc($quiz->title ?? 'Quiz') ?></p>

  <?php if (! empty($subjectName)) : ?>

    <p class="small text-muted mb-3"><?= esc($subjectName) ?></p>

  <?php endif; ?>

  <div class="board-prep-stat text-start mb-4">

    <div class="d-flex justify-content-between mb-2">

      <span class="text-muted">Attempt</span>

      <strong>#<?= (int) ($attempt->attempt_no ?? 1) ?></strong>

    </div>

    <div class="d-flex justify-content-between mb-2">

      <span class="text-muted">Score</span>

      <strong><?= round((float) ($attempt->score_obtained ?? 0), 1) ?> / <?= round((float) ($totalMarks ?? 0), 1) ?></strong>

    </div>

    <div class="d-flex justify-content-between">

      <span class="text-muted">Percentage</span>

      <strong class="text-success"><?= $scorePercent !== null ? esc($scorePercent) . '%' : '—' ?></strong>

    </div>

  </div>

  <p class="small text-muted mb-3">Returning to your dashboard in <span id="bpRedirectSec">8</span> seconds…</p>

  <a href="<?= board_prep_url('dashboard') ?>" class="btn btn-bp-primary btn-lg">Back to dashboard</a>

  <?php if (! empty($canReview)) : ?>

    <a href="<?= board_prep_url('quizzes/review/' . (int) $attempt->attempt_id) ?>" class="btn btn-outline-secondary btn-lg ms-2 mt-2 mt-md-0">Review answers</a>

  <?php endif; ?>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script>

(function () {

  var sec = 8;

  var el = document.getElementById('bpRedirectSec');

  var t = setInterval(function () {

    sec--;

    if (el) el.textContent = String(sec);

    if (sec <= 0) {

      clearInterval(t);

      window.location.href = <?= json_encode(board_prep_url('dashboard')) ?>;

    }

  }, 1000);

})();

</script>

<?= $this->endSection() ?>
