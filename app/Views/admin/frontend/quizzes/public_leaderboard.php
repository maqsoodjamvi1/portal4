<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<div class="container py-4" style="max-width:800px">
  <h2 class="mb-3">?? Leaderboard</h2>
  <h4 class="mb-3"><?= esc($quiz->title) ?></h4>

  <?php if (empty($rows)): ?>
    <div class="alert alert-light border">
      No public attempts have been submitted yet.
    </div>
  <?php else: ?>
    <table class="table table-sm table-striped table-bordered">
      <thead class="table-light">
        <tr>
          <th style="width:60px">Rank</th>
          <th>Player</th>
          <th style="width:120px">Score</th>
          <th style="width:160px">Submitted At</th>
        </tr>
      </thead>
      <tbody>
        <?php $rank = 1; ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td>#<?= $rank++ ?></td>
            <td><?= esc($r->public_name ?: 'Anonymous') ?></td>
            <td><?= esc($r->score_board) ?></td>
            <td><?= esc($r->submitted_at ?: '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <a href="<?= site_url('public/quizzes') ?>" class="btn btn-link">? Back to Public Quizzes</a>
</div>

<?= $this->endSection() ?>
