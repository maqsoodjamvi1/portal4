<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Attendance</h4>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead class="table-light"><tr><th>#</th><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="4" class="text-center text-muted">No attendance data.</td></tr>
      <?php else: foreach ($rows as $i=>$r): $s=strtolower((string)($r['status']??'')); ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= esc($r['attendance_date'] ?? '-') ?></td>
          <td><span class="badge bg-<?= $s==='present'?'success':($s==='absent'?'danger':($s==='leave'?'warning text-dark':'secondary')) ?>"><?= esc(ucfirst($s ?: 'N/A')) ?></span></td>
          <td><?= esc($r['remarks'] ?? '-') ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
