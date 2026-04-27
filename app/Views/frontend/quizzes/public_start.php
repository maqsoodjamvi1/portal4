<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<div class="container py-4" style="max-width:600px">
  <h2 class="mb-3">?? Start Quiz</h2>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-1"><?= esc($quiz->title) ?></h4>

      <p class="text-muted small mb-3">
        Enter your name or nickname. It will appear on the leaderboard.
      </p>

      <form method="post" action="<?= esc($publicQuizUrl) ?>">
        <?= csrf_field() ?>

        <div class="form-group">
          <label for="public_name">Your Name / Nickname</label>
          <input
            type="text"
            name="public_name"
            id="public_name"
            class="form-control"
            value="<?= esc(old('public_name')) ?>"
            required
          >
        </div>

        <button type="submit" class="btn btn-success">
          <i class="fa fa-play"></i> Start Quiz
        </button>
        <a href="<?= site_url('public/quizzes') ?>" class="btn btn-link">Back</a>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
