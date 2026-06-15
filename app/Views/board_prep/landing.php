<?= $this->extend('board_prep/layout') ?>
<?= $this->section('content') ?>
<section class="board-prep-hero text-center">
  <div class="container">
    <h1 class="display-4 fw-bold mb-3"><?= esc($productName) ?></h1>
    <p class="lead mb-4">Practice board-style quizzes for SSC &amp; HSSC — FBISE, RBISE, LBISE, Sindh &amp; KPK boards.</p>
    <a href="<?= board_prep_url('signup') ?>" class="btn btn-warning btn-lg me-2">Create free account</a>
    <a href="<?= board_prep_url('login') ?>" class="btn btn-outline-light btn-lg">Sign in</a>
  </div>
</section>
<section class="container py-5">
  <div class="row">
    <div class="col-md-4 mb-3"><div class="board-prep-stat"><h5>Class-wise quizzes</h5><p class="mb-0 text-muted">SSC-I, SSC-II, HSSC-I, HSSC-II</p></div></div>
    <div class="col-md-4 mb-3"><div class="board-prep-stat"><h5>Board aligned</h5><p class="mb-0 text-muted">Content filtered by your selected board</p></div></div>
    <div class="col-md-4 mb-3"><div class="board-prep-stat"><h5>Track progress</h5><p class="mb-0 text-muted">Quiz-wise, subject-wise &amp; overall results</p></div></div>
  </div>
</section>
<?= $this->endSection() ?>
