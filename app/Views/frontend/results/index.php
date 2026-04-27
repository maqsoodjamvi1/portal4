<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Results</h4>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead class="table-light"><tr><th>#</th><th>Exam</th><th>Obtained</th><th>Total</th><th>Grade</th><th>Posted</th></tr></thead>
    <tbody>
      <?php if (empty($results)): ?>
        <tr><td colspan="6" class="text-center text-muted">No results available.</td></tr>
      <?php else: foreach ($results as $i=>$r): ?>
        <tr>
          <td><?= $i+1 ?></td><td>#<?= esc($r['exam_id']) ?></td>
          <td><?= esc($r['obtain_total_mark']) ?></td><td><?= esc($r['exam_total_mark']) ?></td>
          <td><span class="badge bg-secondary"><?= esc($r['grade'] ?? '-') ?></span></td>
          <td><?= esc($r['created_at'] ?? '-') ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
