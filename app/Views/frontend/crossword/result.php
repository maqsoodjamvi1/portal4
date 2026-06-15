<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<div class="container py-5 text-center">
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-warning"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <h2><?= esc($title ?? 'Crossword') ?> — Submitted</h2>
  <p class="text-muted">This crossword can only be played once.</p>
  <p class="display-4 text-success fw-bold"><?= (int) ($score['score'] ?? 0) ?>%</p>
  <p>You got <strong><?= (int) ($score['correct'] ?? 0) ?></strong> out of <strong><?= (int) ($score['total'] ?? 0) ?></strong> correct.</p>

  <?php if (! empty($weakOps)): ?>
    <div class="alert alert-info mt-4">
      <strong>Keep practising:</strong> review operations
      <?= esc(implode(', ', $weakOps)) ?>.
    </div>
  <?php endif; ?>

  <a href="<?= site_url('student/crossword') ?>" class="btn btn-primary mt-3">Back to assignments</a>
</div>

<?= $this->endSection() ?>
