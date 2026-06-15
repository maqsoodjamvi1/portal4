<?php $navActive = 'results'; ?>
<?= $this->extend('board_prep/app_layout') ?>
<?= $this->section('main') ?>
<h2 class="mb-3">Overall results</h2>
<div class="row mb-4">
  <div class="col-md-3 col-6 mb-3"><div class="board-prep-stat"><div class="small text-muted">Attempts</div><div class="h4 mb-0"><?= (int) ($overall['total_attempts'] ?? 0) ?></div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="board-prep-stat"><div class="small text-muted">Quizzes tried</div><div class="h4 mb-0"><?= (int) ($overall['quizzes_attempted'] ?? 0) ?></div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="board-prep-stat"><div class="small text-muted">Average</div><div class="h4 mb-0"><?= esc($overall['avg_percent'] ?? 0) ?>%</div></div></div>
  <div class="col-md-3 col-6 mb-3"><div class="board-prep-stat"><div class="small text-muted">Best score</div><div class="h4 mb-0"><?= esc($overall['best_percent'] ?? 0) ?>%</div></div></div>
</div>
<h5 class="mb-3">Recent attempts</h5>
<?php if (empty($recent)) : ?>
  <p class="text-muted">No completed quizzes yet. <a href="<?= board_prep_url('quizzes') ?>">Start practicing</a>.</p>
<?php else : ?>
  <div class="table-responsive bg-white rounded shadow-sm">
    <table class="table table-sm mb-0">
      <thead><tr><th>Quiz</th><th>Subject</th><th>Score</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recent as $row) : ?>
          <tr>
            <td><?= esc($row->title) ?></td>
            <td><?= esc($row->subject_name ?? '—') ?></td>
            <td><?= round((float) ($row->score_percent ?? 0), 1) ?>%</td>
            <td><?= esc($row->submitted_at ?? '') ?></td>
            <td><a href="<?= board_prep_url('results/quiz/' . (int) $row->attempt_id) ?>">Details</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<p class="mt-3"><a href="<?= board_prep_url('results/subjects') ?>">View subject-wise breakdown →</a></p>
<?= $this->endSection() ?>
