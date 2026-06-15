<?php $navActive = 'dashboard'; ?>

<?= $this->extend('board_prep/app_layout') ?>

<?= $this->section('main') ?>

<section class="bp-dashboard-hero board-prep-card mb-4">
  <div class="bp-dashboard-hero__board">
    <?php if (! empty($boardLogoUrl)) : ?>
      <div class="bp-board-logo-wrap">
        <img src="<?= esc($boardLogoUrl, 'attr') ?>" alt="" class="bp-board-logo">
      </div>
    <?php else : ?>
      <div class="bp-board-logo-wrap bp-board-logo-wrap--placeholder">
        <i class="fas fa-university"></i>
      </div>
    <?php endif; ?>
    <div class="bp-board-title-wrap">
      <p class="bp-board-eyebrow mb-1">Your selected board</p>
      <h1 class="bp-board-title"><?= esc($boardName ?? ($auth['board_name'] ?? 'Board')) ?></h1>
    </div>
  </div>

  <div class="bp-dashboard-hero__student">
    <a href="<?= board_prep_url('profile') ?>" class="bp-student-chip" title="Manage profile">
      <img src="<?= esc($photoUrl ?? '', 'attr') ?>" alt="" class="bp-student-photo">
      <div class="bp-student-meta">
        <span class="bp-student-greeting">Welcome back</span>
        <strong class="bp-student-name"><?= esc($auth['display_name'] ?? '') ?></strong>
        <span class="badge text-bg-light bp-grade-badge"><?= esc($gradeLabel ?? '') ?></span>
      </div>
      <i class="fas fa-chevron-right bp-student-chevron d-none d-sm-inline"></i>
    </a>
  </div>
</section>

<div class="row bp-stats-row mb-4">
  <div class="col-4 col-md-4 mb-3 mb-md-0">
    <div class="board-prep-stat bp-stat-card h-100 text-center text-md-start">
      <div class="text-muted small bp-stat-label">Quizzes</div>
      <div class="h3 mb-0 bp-stat-value"><?= (int) ($stats['quizzes_available'] ?? 0) ?></div>
    </div>
  </div>
  <div class="col-4 col-md-4 mb-3 mb-md-0">
    <div class="board-prep-stat bp-stat-card h-100 text-center text-md-start">
      <div class="text-muted small bp-stat-label">Attempts</div>
      <div class="h3 mb-0 bp-stat-value"><?= (int) ($stats['attempts'] ?? 0) ?></div>
    </div>
  </div>
  <div class="col-4 col-md-4">
    <div class="board-prep-stat bp-stat-card h-100 text-center text-md-start">
      <div class="text-muted small bp-stat-label">Average</div>
      <div class="h3 mb-0 bp-stat-value"><?= esc($stats['avg_percent'] ?? 0) ?>%</div>
    </div>
  </div>
</div>

<section class="bp-practice-section">
  <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
    <div class="pe-2 mb-2">
      <h2 class="h5 mb-1 fw-bold">Practice by subject</h2>
      <p class="text-muted small mb-0">Published quizzes for your class — unlimited attempts.</p>
    </div>
    <div class="bp-quick-actions">
      <a href="<?= board_prep_url('results') ?>" class="btn btn-sm btn-outline-secondary mb-1">
        <i class="fas fa-chart-line me-1"></i><span class="d-none d-sm-inline">Results</span><span class="d-inline d-sm-none">Stats</span>
      </a>
    </div>
  </div>

  <?= view('board_prep/partials/subject_quiz_list', ['subjectGroups' => $subjectGroups ?? []]) ?>
</section>

<?= $this->endSection() ?>
