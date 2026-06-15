<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<div class="container py-4">
  <h2><i class="fas fa-th"></i> Crossword Assignments</h2>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <?php if (empty($tablesReady ?? true)): ?>
    <div class="alert alert-warning">Crossword feature is not available yet. Please contact your school administrator.</div>
  <?php elseif (empty($assignments)): ?>
    <div class="alert alert-info">No crossword assignments yet. Your teacher will assign crosswords from the admin panel.</div>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($assignments as $a): ?>
        <?php $completed = $a['last_score'] !== null && $a['last_score'] !== ''; ?>
        <a href="<?= $completed ? site_url('student/crossword/result/' . (int) $a['id']) : site_url('student/crossword/play/' . (int) $a['id']) ?>"
           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
          <div>
            <strong><?= esc($a['title'] ?? '') ?></strong>
            <br><small class="text-muted"><?= esc($a['puzzle_type'] ?? '') ?> · Grade <?= (int) ($a['grade'] ?? 0) ?></small>
            <?php if (! empty($a['due_date'])): ?>
              <br><small>Due: <?= esc($a['due_date']) ?></small>
            <?php endif; ?>
          </div>
          <?php if ($completed): ?>
            <span class="badge text-bg-success">Completed · <?= (int) $a['last_score'] ?>%</span>
          <?php else: ?>
            <span class="badge text-bg-primary">Start</span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
