<?= $this->extend('board_prep/layout') ?>
<?= $this->section('content') ?>
<?php
$auth = board_prep_auth();
$navActive = $navActive ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark board-prep-nav">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= board_prep_url('dashboard') ?>">
      <i class="fas fa-book-reader me-1"></i><?= esc($productName ?? 'Board Exam Prep') ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bpNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="bpNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link <?= $navActive === 'dashboard' ? 'active' : '' ?>" href="<?= board_prep_url('dashboard') ?>">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $navActive === 'quizzes' ? 'active' : '' ?>" href="<?= board_prep_url('dashboard') ?>">Quizzes</a></li>
        <li class="nav-item"><a class="nav-link <?= $navActive === 'results' ? 'active' : '' ?>" href="<?= board_prep_url('results') ?>">Results</a></li>
        <li class="nav-item"><a class="nav-link <?= $navActive === 'profile' ? 'active' : '' ?>" href="<?= board_prep_url('profile') ?>">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= board_prep_url('results/subjects') ?>">By Subject</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= board_prep_url('logout') ?>">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4">
  <?php if (! empty($success)) : ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
  <?php endif; ?>
  <?php if (! empty($error)) : ?>
    <div class="alert alert-danger"><?= esc($error) ?></div>
  <?php endif; ?>
  <?= $this->renderSection('main') ?>
</main>
<footer class="board-prep-footer text-center py-3 text-muted small">
  &copy; <?= date('Y') ?> <?= esc($productName ?? 'Board Exam Prep') ?> — Board exam preparation
</footer>
<?= $this->endSection() ?>
