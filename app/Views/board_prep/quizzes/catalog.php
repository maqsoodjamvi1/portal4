<?php $navActive = 'quizzes'; ?>
<?= $this->extend('board_prep/app_layout') ?>
<?= $this->section('main') ?>
<h2 class="mb-3">Quizzes for your class</h2>
<?php if (! empty($err)) : ?>
  <div class="alert alert-warning"><?= esc($err) ?></div>
<?php elseif (empty($quizzes)) : ?>
  <div class="alert alert-info">No quizzes are published yet for your class and board. Check back soon.</div>
<?php else : ?>
  <?php foreach ($quizzes as $quiz) : ?>
    <div class="quiz-list-item d-flex flex-wrap justify-content-between align-items-center">
      <div class="mb-2 mb-md-0">
        <strong><?= esc($quiz->title) ?></strong>
        <?php if (! empty($quiz->subject_name)) : ?>
          <span class="badge text-bg-secondary ms-1"><?= esc($quiz->subject_name) ?></span>
        <?php endif; ?>
        <?php if (! empty($quiz->board_name)) : ?>
          <span class="badge text-bg-light border ms-1"><?= esc($quiz->board_name) ?></span>
        <?php endif; ?>
        <div class="small text-muted mt-1">
          <?= (int) ($quiz->questions_count ?? 0) ?> questions
          <?php if ((int) ($quiz->time_limit_sec ?? 0) > 0) : ?>
            · <?= (int) ceil($quiz->time_limit_sec / 60) ?> min
          <?php endif; ?>
          <?php if (isset($quiz->best_percent) && $quiz->best_percent !== null) : ?>
            · Best: <?= round((float) $quiz->best_percent, 1) ?>%
          <?php endif; ?>
        </div>
      </div>
      <div>
        <?php if (! empty($quiz->can_start)) : ?>
          <a href="<?= board_prep_url('quizzes/start/' . (int) $quiz->quiz_id) ?>" class="btn btn-bp-primary btn-sm">Attempt</a>
        <?php else : ?>
          <span class="text-muted small">Max attempts reached</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?= $this->endSection() ?>
