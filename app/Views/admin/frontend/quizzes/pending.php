<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <h1 class="mb-3">Pending Quizzes</h1>
</section>

<section class="content">

  <?php if (!empty($err)): ?>
    <div class="alert alert-warning"><?= esc($err) ?></div>
  <?php endif; ?>

  <!-- Filters: Term & Subject -->
  <div class="card mb-3">
    <div class="card-body">
      <form method="get" class="mb-0">
        <div class="row">
          <div class="form-group col-md-4">
            <label for="term_filter">Term / Session</label>
            <select name="term_session_id" id="term_filter" class="form-control">
              <option value="">All Terms</option>
              <?php if (!empty($termOptions)): ?>
                <?php foreach ($termOptions as $t): ?>
                  <option value="<?= (int)$t->term_session_id ?>"
                    <?= (!empty($currentTerm) && (int)$currentTerm === (int)$t->term_session_id) ? 'selected' : '' ?>>
                    <?= esc($t->term_session_label) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="form-group col-md-4">
            <label for="subject_filter">Subject</label>
            <select name="subject_id" id="subject_filter" class="form-control">
              <option value="">All Subjects</option>
              <?php if (!empty($subjectOptions)): ?>
                <?php foreach ($subjectOptions as $s): ?>
                  <option value="<?= (int)$s->subject_id ?>"
                    <?= (!empty($currentSubject) && (int)$currentSubject === (int)$s->subject_id) ? 'selected' : '' ?>>
                    <?= esc($s->subject_label) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="form-group col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">
              <i class="fa fa-filter"></i> Apply
            </button>
            <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
              Reset
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Quiz Cards -->
  <?php if (!empty($unattempted)): ?>
    <div class="row">
      <?php foreach ($unattempted as $q): ?>
        <?php
          $attemptsUsed  = (int) ($q->attempts_used ?? 0);
          $maxAttempts   = (int) ($q->max_attempts ?? 0);
          $remaining     = max(0, $maxAttempts - $attemptsUsed);
          $questionsCfg  = (int) ($q->questions_count ?? 0);
          $questionsDb   = (int) ($q->questions_db ?? 0);
          $subjectLabel  = $q->subject_short_name ?? $q->subject_name ?? 'Subject';
          $termLabel     = $q->term_session_label ?? '';
          $hasResult     = ($attemptsUsed > 0 && $q->last_score !== null);
        ?>
        <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
          <div class="card shadow-sm quiz-card h-100">
            <div class="card-body d-flex flex-column">

              <!-- Title + Subject -->
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title mb-0" style="font-size:1.05rem;">
                  <?= esc($q->title) ?>
                </h5>
                <span class="badge text-bg-info ms-2">
                  <?= esc($subjectLabel) ?>
                </span>
              </div>

              <!-- Term / Session -->
              <?php if (!empty($termLabel)): ?>
                <p class="mb-1">
                  <small class="text-muted">Term / Session:</small>
                  <br>
                  <span><?= esc($termLabel) ?></span>
                </p>
              <?php endif; ?>

              <!-- Window -->
              <p class="mb-2 small">
                <span class="text-muted d-block">Quiz Window</span>
                <?php if ($q->start_at): ?>
                  <span><strong>From:</strong> <?= esc($q->start_at) ?></span><br>
                <?php endif; ?>
                <?php if ($q->end_at): ?>
                  <span><strong>To:</strong> <?= esc($q->end_at) ?></span>
                <?php endif; ?>
                <?php if (!$q->start_at && !$q->end_at): ?>
                  <span class="text-muted">Open (no time limit)</span>
                <?php endif; ?>
              </p>

              <!-- Attempts info -->
              <div class="quiz-meta mb-2 small">
                <div>
                  <strong>Attempts:</strong>
                  <?= $attemptsUsed ?> / <?= $maxAttempts ?>
                  <span class="badge text-bg-<?=  $remaining > 0 ? 'success' : 'secondary' ?> ms-1">
                    <?= $remaining ?> left
                  </span>
                </div>
              </div>

              <!-- Questions info -->
              <div class="quiz-meta mb-2 small">
                <div>
                  <strong>Questions:</strong>
                  <?= $questionsCfg ?>
                  <?php if ($questionsDb && $questionsDb !== $questionsCfg): ?>
                    <span class="text-warning">
                      (DB: <?= $questionsDb ?>)
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Result info -->
              <div class="quiz-result small mb-3">
                <strong>Last Result:</strong><br>
                <?php if ($hasResult): ?>
                  <span>Correct: <?= (int) ($q->correct_count ?? 0) ?></span>,
                  <span>Wrong: <?= (int) ($q->wrong_count ?? 0) ?></span>,
                  <span>Score: <?= (float) ($q->last_score ?? 0) ?></span>
                <?php else: ?>
                  <span class="text-muted">Not attempted yet</span>
                <?php endif; ?>
              </div>

              <!-- Action -->
              <div class="mt-auto">
                <a class="btn btn-primary w-100"
                   href="<?= site_url('student/quizzes/start/' . $q->quiz_id) ?>">
                  Start Quiz
                </a>

                <a href="<?= base_url('student/quizzes/practice/' . $q->quiz_id) ?>"
           class="btn btn-sm btn-info">
           🎮 Practice
        </a>
              </div>

            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="card-body text-center text-muted">
        No pending quizzes.
      </div>
    </div>
  <?php endif; ?>

</section>

<style>
  .quiz-card {
    border-radius: 0.5rem;
  }
  .quiz-card .card-body {
    padding: 1rem 1.1rem;
  }
  @media (max-width: 575.98px) {
    .quiz-card .card-body {
      padding: 0.9rem;
    }
  }
</style>

<?= $this->endSection() ?>
