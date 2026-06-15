<?= $this->extend('board_prep/app_layout') ?>
<?= $this->section('main') ?>
<h2 class="mb-2"><?= esc($attempt->title ?? 'Quiz result') ?></h2>
<p class="text-muted mb-3">
  <?= esc($attempt->subject_name ?? '') ?>
  · Score: <strong><?= round((float) ($attempt->score_percent ?? 0), 1) ?>%</strong>
  · Submitted: <?= esc($attempt->submitted_at ?? '') ?>
</p>
<a href="<?= board_prep_url('quizzes/review/' . (int) $attempt->attempt_id) ?>" class="btn btn-bp-primary mb-3">Review answers</a>
<a href="<?= board_prep_url('results') ?>" class="btn btn-outline-secondary mb-3 ms-2">All results</a>
<?= $this->endSection() ?>
