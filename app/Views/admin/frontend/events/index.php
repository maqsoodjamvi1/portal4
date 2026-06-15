<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Events</h4>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead class="table-light"><tr><th>#</th><th>Title</th><th>Date</th><th>Time</th><th>Venue</th><th>Description</th></tr></thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-center text-muted">No events found.</td></tr>
      <?php else: foreach ($rows as $i=>$r): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= esc($r['title']) ?></td>
          <td><?= esc($r['event_date']) ?></td>
          <td><?= esc(($r['start_time'] ?? '').($r['end_time']? ' - '.$r['end_time'] : '')) ?></td>
          <td><?= esc($r['venue'] ?? '-') ?></td>
          <td><?= esc($r['description'] ?? '-') ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
