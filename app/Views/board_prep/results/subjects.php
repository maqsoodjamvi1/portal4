<?= $this->extend('board_prep/app_layout') ?>
<?= $this->section('main') ?>
<h2 class="mb-3">Subject-wise results</h2>
<?php if (empty($subjects)) : ?>
  <p class="text-muted">No subject data yet.</p>
<?php else : ?>
  <div class="table-responsive bg-white rounded shadow-sm">
    <table class="table mb-0">
      <thead><tr><th>Subject</th><th>Attempts</th><th>Average</th><th>Best</th></tr></thead>
      <tbody>
        <?php foreach ($subjects as $row) : ?>
          <tr>
            <td><?= esc($row->subject_name ?? 'General') ?></td>
            <td><?= (int) ($row->attempts ?? 0) ?></td>
            <td><?= round((float) ($row->avg_percent ?? 0), 1) ?>%</td>
            <td><?= round((float) ($row->best_percent ?? 0), 1) ?>%</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<p class="mt-3"><a href="<?= board_prep_url('results') ?>">← Back to overall results</a></p>
<?= $this->endSection() ?>
