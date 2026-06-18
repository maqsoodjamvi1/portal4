<?= $this->extend('board_prep/layout') ?>
<?= $this->section('content') ?>

<nav class="navbar navbar-expand-lg navbar-dark board-prep-nav">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= board_prep_url('') ?>">
      <i class="fas fa-book-reader me-1"></i><?= esc($productName ?? 'Live Education Quiz') ?>
    </a>
    <div class="d-flex gap-2 ms-auto">
      <a href="<?= board_prep_url('') ?>" class="btn btn-sm btn-outline-light">Quizzes</a>
      <a href="<?= board_prep_url('signup') ?>" class="btn btn-sm btn-light">Sign up</a>
    </div>
  </div>
</nav>

<main class="container py-4 bp-auth-page">
  <section class="bp-dashboard-hero board-prep-card mb-4">
    <div class="bp-dashboard-hero__board">
      <div class="bp-board-logo-wrap bp-board-logo-wrap--placeholder">
        <i class="fas fa-sign-in-alt"></i>
      </div>
      <div class="bp-board-title-wrap">
        <p class="bp-board-eyebrow mb-1">Student dashboard</p>
        <h1 class="bp-board-title">Log in to save quiz results</h1>
        <p class="bp-auth-hero-copy mb-0">Guest play is open, but saved attempts, averages, and subject-wise progress need an account.</p>
      </div>
    </div>
  </section>

  <div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
      <div class="board-prep-card">
        <div class="card-head"><h2 class="h5 mb-0"><i class="fas fa-lock me-2"></i>Account login</h2></div>
        <div class="p-4">
          <?php if (! empty($error)) : ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>
          <?php if (! empty($success)) : ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
          <?= form_open(board_prep_url('login'), ['autocomplete' => 'off']) ?>
          <?= csrf_field() ?>
          <div class="form-group mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required value="<?= esc(old('username')) ?>">
          </div>
          <div class="form-group mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-bp-primary w-100">Sign in</button>
          <?= form_close() ?>
          <p class="text-center mt-3 mb-0">No account? <a href="<?= board_prep_url('signup') ?>">Sign up free</a></p>
        </div>
      </div>
    </div>
  </div>
</main>

<footer class="board-prep-footer text-center py-3 text-muted small">
  &copy; <?= date('Y') ?> <?= esc($productName ?? 'Live Education Quiz') ?> - Quiz practice and saved results
</footer>

<style>
  .bp-auth-page {
    max-width: 980px;
  }
  .bp-auth-hero-copy {
    max-width: 720px;
    color: rgba(255,255,255,.9);
  }
</style>

<?= $this->endSection() ?>
