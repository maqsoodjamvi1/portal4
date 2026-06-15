<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<div class="container py-5 text-center">
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-warning"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <h2><?= esc($title ?? 'Word Search') ?> — Submitted</h2>
  <p class="text-muted">This word search can only be played once.</p>
  <p class="display-4 text-success fw-bold"><?= (int) ($score['score'] ?? 0) ?>%</p>
  <p>You found <strong><?= (int) ($score['correct'] ?? 0) ?></strong> out of <strong><?= (int) ($score['total'] ?? 0) ?></strong> words.</p>
  <a href="<?= site_url('student/word-search') ?>" class="btn btn-primary mt-3">Back to assignments</a>
</div>

<?= $this->endSection() ?>
