<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Crossword Attempt Report',
    'icon' => 'fas fa-chart-bar',
    'breadcrumbs' => [
        ['label' => 'Math Crossword', 'url' => base_url('admin/math-crossword')],
        ['label' => 'Report', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>Student</th><th>Roll No</th><th>Score</th><th>Correct</th><th>Submitted</th></tr></thead>
          <tbody>
            <?php if (empty($attempts)): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">No attempts yet for assignment #<?= (int) $assignmentId ?>.</td></tr>
            <?php else: ?>
              <?php foreach ($attempts as $a): ?>
              <tr>
                <td><?= esc($a['student_name'] ?? '') ?></td>
                <td><?= esc($a['reg_no'] ?? '') ?></td>
                <td><?= (int) ($a['score'] ?? 0) ?>%</td>
                <td><?= (int) ($a['correct_count'] ?? 0) ?> / <?= (int) ($a['total_count'] ?? 0) ?></td>
                <td><?= esc($a['submitted_at'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
