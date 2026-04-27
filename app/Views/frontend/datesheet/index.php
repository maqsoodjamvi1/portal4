<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Datesheet</h4><p class="text-muted">Class ID: <?= esc($class_id ?? '-') ?></p>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead class="table-light"><tr><th>#</th><th>Exam</th><th>Subject</th><th>Date</th><th>Start</th><th>End</th><th>Room</th></tr></thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="7" class="text-center text-muted">No datesheet available.</td></tr>
      <?php else: foreach ($rows as $i=>$r): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td>#<?= esc($r['exam_id']) ?></td>
          <td><?= esc($r['subject_id']) ?></td>
          <td><?= esc($r['exam_date']) ?></td>
          <td><?= esc($r['start_time']) ?></td>
          <td><?= esc($r['end_time']) ?></td>
          <td><?= esc($r['room'] ?? '-') ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
