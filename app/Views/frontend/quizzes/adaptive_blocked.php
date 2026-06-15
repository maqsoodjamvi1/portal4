<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<section class="content parent-subpage-content pt-3">
  <div class="container-fluid px-2 px-md-3">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
          <div class="card-body text-center p-4 p-md-5">
            <div style="font-size:3rem;" class="mb-3 text-success">
              <i class="fas fa-trophy"></i>
            </div>
            <h3 class="mb-2"><?= esc($quiz->title ?? 'Quiz') ?></h3>
            <p class="text-muted mb-4"><?= esc($message ?? '') ?></p>
            <div class="d-flex flex-column flex-sm-row justify-content-center" style="gap:8px;">
              <a href="<?= esc($catalogUrl ?? base_url('student/quizzes/all')) ?>" class="btn btn-primary">
                <i class="fas fa-list me-1"></i> Back to all quizzes
              </a>
              <a href="<?= esc($practiceUrl ?? '#') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-dumbbell me-1"></i> Practice mode
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>

