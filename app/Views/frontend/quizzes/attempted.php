<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <h1 class="mb-3">Attempted Quizzes</h1>
 
</section>

<section class="content">
  <?php if (!empty($err)): ?>
    <div class="alert alert-warning"><?= esc($err) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead>
          <tr>
            <th>Quiz</th>
            <th>Score</th>
            <th>Status</th>
            <th>Submitted</th>
            <th style="width:110px;">Review</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($attempted)): ?>
          <?php foreach ($attempted as $a): ?>
            <tr>
              <td><?= esc($a->title) ?></td>
              <td><?= esc($a->score_obtained) ?></td>
              <td>
                <span class="badge <?= $a->status==='submitted'?'badge-success':'badge-secondary' ?>">
                  <?= esc(ucfirst($a->status)) ?>
                </span>
              </td>
              <td><?= esc($a->submitted_at ?? '-') ?></td>
              <td>
                <a class="btn btn-info btn-sm" href="<?= site_url('student/quizzes/review/'.$a->attempt_id) ?>">
                  Open
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center p-3 text-muted">No attempts to show.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
