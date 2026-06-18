<?= $this->extend('board_prep/layout') ?>

<?= $this->section('content') ?>

<!-- Hero -->
<section class=" leq-hero text-white">
  <div class="container py-5">
    <div class="row align-items-center g-4 py-lg-4">
      <div class="col-lg-7">
        <span class="badge bg-light text-dark mb-3"><i class="fas fa-bolt text-warning me-1"></i> Free practice quizzes</span>
        <h1 class="display-5 fw-bold mb-3"><?= esc($productName ?? 'Live Education Quiz') ?></h1>
        <p class="lead mb-4 opacity-90">
          Test your knowledge with exam-style quizzes across every subject.
          Play instantly as a guest — or sign up free to save your scores and track your progress.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= esc($dashboardUrl) ?>" class="btn btn-light btn-lg fw-semibold">
            <i class="fas fa-play me-1"></i> Browse quizzes
          </a>
          <a href="<?= esc($signupUrl) ?>" class="btn btn-outline-light btn-lg">
            <i class="fas fa-user-plus me-1"></i> Sign up free
          </a>
        </div>
        <div class="small mt-3 opacity-75">
          Already have an account? <a href="<?= esc($loginUrl) ?>" class="text-white fw-semibold">Log in</a>
        </div>
      </div>
      <div class="col-lg-5 text-center d-none d-lg-block">
        <i class="fas fa-graduation-cap" style="font-size: 12rem; opacity:.18;"></i>
      </div>
    </div>
  </div>
</section>

<!-- Value props -->
<section class="container py-5">
  <div class="row g-4 text-center">
    <div class="col-md-4">
      <div class="leq-feature">
        <div class="leq-feature__icon bg-primary-subtle text-primary"><i class="fas fa-play"></i></div>
        <h3 class="h5 mt-3">Play instantly</h3>
        <p class="text-muted mb-0">No account needed to start. Pick a quiz and play right away as a guest.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="leq-feature">
        <div class="leq-feature__icon bg-success-subtle text-success"><i class="fas fa-save"></i></div>
        <h3 class="h5 mt-3">Save your results</h3>
        <p class="text-muted mb-0">Sign up free to store every score and revisit your answers anytime.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="leq-feature">
        <div class="leq-feature__icon bg-warning-subtle text-warning"><i class="fas fa-chart-line"></i></div>
        <h3 class="h5 mt-3">Track progress</h3>
        <p class="text-muted mb-0">Watch your averages climb across subjects as you practice more.</p>
      </div>
    </div>
  </div>
</section>

<!-- Popular quizzes -->
<?php if (! empty($featuredQuizzes)) : ?>
<section class="bg-light py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 mb-0">Popular quizzes</h2>
      <a href="<?= esc($dashboardUrl) ?>" class="btn btn-sm btn-outline-primary">View all</a>
    </div>
    <div class="row g-3">
      <?php foreach ($featuredQuizzes as $quiz) : ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex flex-column">
              <div class="mb-2">
                <?php if (! empty($quiz->subject_name)) : ?>
                  <span class="badge text-bg-light border"><?= esc($quiz->subject_name) ?></span>
                <?php endif; ?>
                <?php if (! empty($quiz->board_name)) : ?>
                  <span class="badge text-bg-light border"><?= esc($quiz->board_name) ?></span>
                <?php endif; ?>
              </div>
              <h3 class="h6 fw-semibold"><?= esc($quiz->title) ?></h3>
              <div class="small text-muted mb-3">
                <?= (int) ($quiz->questions_count ?? 0) ?> questions
                <?php if ((int) ($quiz->time_limit_sec ?? 0) > 0) : ?>
                  · <?= (int) ceil($quiz->time_limit_sec / 60) ?> min
                <?php endif; ?>
              </div>
              <a href="<?= board_prep_url('quizzes/guest/' . (int) $quiz->quiz_id) ?>" class="btn btn-sm btn-primary mt-auto">
                <i class="fas fa-play me-1"></i> Play as guest
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA band -->
<section class="leq-cta text-white text-center py-5">
  <div class="container">
    <h2 class="h3 fw-bold mb-2">Ready to test yourself?</h2>
    <p class="mb-4 opacity-90">Sign up free to save your results, or jump straight into a quiz as a guest.</p>
    <div class="d-flex justify-content-center flex-wrap gap-2">
      <a href="<?= esc($signupUrl) ?>" class="btn btn-light btn-lg fw-semibold"><i class="fas fa-user-plus me-1"></i> Create free account</a>
      <a href="<?= esc($dashboardUrl) ?>" class="btn btn-outline-light btn-lg">Browse quizzes</a>
    </div>
  </div>
</section>

<style>
  .leq-hero { background: linear-gradient(135deg,#4f46e5 0%,#7c3aed 50%,#2563eb 100%); }
  .leq-cta  { background: linear-gradient(135deg,#1e293b 0%,#334155 100%); }
  .leq-feature__icon {
    width:64px;height:64px;border-radius:50%;display:inline-flex;
    align-items:center;justify-content:center;font-size:1.5rem;
  }
  .leq-hero .opacity-90 { opacity:.9; }
</style>

<?= $this->endSection() ?>
