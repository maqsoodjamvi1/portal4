<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <h1 class="mb-3">Quiz Results</h1>
</section>

<section class="content">
  <div class="card">
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead>
          <tr>
            <th style="width:80px;">Attempt</th>
            <th>Student</th>
            <th>Attempt #</th>
            <th>Status</th>
            <th>Score</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($attempts)): ?>
            <?php foreach ($attempts as $a): ?>
              <tr>
                <td><?= (int)$a->attempt_id ?></td>
                <td><?= esc($a->student_name ?? $a->student_id) ?></td>
                <td><?= (int)$a->attempt_no ?></td>
                <td><span class="badge <?= $a->status==='submitted'?'badge-success':'badge-secondary' ?>"><?= esc($a->status) ?></span></td>
                <td><?= esc($a->score_obtained) ?></td>
                <td><?= esc($a->submitted_at) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center p-4">No attempts yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
