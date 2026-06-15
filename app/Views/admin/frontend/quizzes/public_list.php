<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<div class="container my-4" style="max-width: 900px;">
  <h2 class="mb-3">?? Public Quizzes</h2>
  <p class="text-muted mb-4">
    Anyone can attempt these quizzes – no login required.
  </p>

  <?php if (empty($quizzes)): ?>
    <div class="alert alert-info">
      No public quizzes are currently available.
    </div>
  <?php else: ?>
    <div class="row">
      <?php foreach ($quizzes as $q): ?>
        <div class="col-md-6 mb-3">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title mb-1">
                <?= esc($q['title']) ?>
              </h5>
              <small class="text-muted d-block mb-2">
                <?= esc($q['subject_name'] ?? '') ?>
              </small>

              <?php if (!empty($q['description'])): ?>
                <p class="card-text" style="font-size: 0.9rem;">
                  <?= esc($q['description']) ?>
                </p>
              <?php endif; ?>

              <p class="mb-1" style="font-size: 0.85rem;">
                <i class="fa fa-clock"></i>
                <?php
                  $mins = (int)ceil(($q['time_limit_sec'] ?? 0) / 60);
                  echo $mins > 0 ? $mins . ' minutes' : 'Self-paced';
                ?>
              </p>

              <a href="<?= site_url('public/quiz/' . $q['quiz_id']) ?>"
                 class="btn btn-primary btn-sm mt-2">
                ? Play Quiz
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
