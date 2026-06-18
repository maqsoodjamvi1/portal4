<?= $this->extend('board_prep/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4" style="max-width: 960px;">

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <a href="<?= board_prep_url('') ?>" class="small text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Quiz landing</a>
      <h1 class="h3 mb-1 mt-1"><i class="fas fa-th-list text-primary me-2"></i>Quiz dashboard</h1>
      <p class="text-muted mb-0">Choose a quiz below. Guest play shows an instant score; saved results require signup or login.</p>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= esc($loginUrl) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-tachometer-alt me-1"></i>Login dashboard</a>
      <a href="<?= esc($signupUrl) ?>" class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i>Sign up to save</a>
    </div>
  </div>

  <?php if ($flash = session()->getFlashdata('error')) : ?>
    <div class="alert alert-warning"><?= esc($flash) ?></div>
  <?php endif; ?>

  <div class="alert alert-info d-flex align-items-center">
    <i class="fas fa-info-circle me-2"></i>
    <div>You can play any quiz below as a guest. Scores from guest mode are not stored; <strong>sign up or log in before playing</strong> to save results.</div>
  </div>

  <?php if (empty($subjectGroups)) : ?>
    <div class="alert alert-secondary mb-0">No quizzes are published yet. Please check back soon.</div>
  <?php else : ?>
    <div class="bp-subject-accordion" id="bpSubjectAccordion">
      <?php foreach ($subjectGroups as $i => $group) : ?>
        <?php
          $collapseId = 'bpSubject' . esc($group['subject_key'], 'attr');
          $expanded   = $i === 0;
        ?>
        <div class="card border-0 shadow-sm mb-2">
          <button
            class="btn btn-link w-100 text-start d-flex align-items-center justify-content-between <?= $expanded ? '' : 'collapsed' ?>"
            type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>"
            aria-expanded="<?= $expanded ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>">
            <span>
              <i class="fas fa-book-open text-muted me-2"></i>
              <strong><?= esc($group['subject_name']) ?></strong>
              <span class="badge text-bg-light border ms-2"><?= (int) $group['quiz_count'] ?> quiz<?= (int) $group['quiz_count'] === 1 ? '' : 'zes' ?></span>
            </span>
            <i class="fas fa-chevron-down"></i>
          </button>
          <div id="<?= $collapseId ?>" class="collapse <?= $expanded ? 'show' : '' ?>" data-bs-parent="#bpSubjectAccordion">
            <div class="card-body pt-0">
              <?php foreach ($group['quizzes'] as $quiz) : ?>
                <div class="d-flex flex-wrap justify-content-between align-items-center py-2 border-bottom">
                  <div class="mb-2 mb-md-0 pe-2">
                    <strong><?= esc($quiz->title) ?></strong>
                    <?php if (! empty($quiz->board_name)) : ?>
                      <span class="badge text-bg-light border ms-1"><?= esc($quiz->board_name) ?></span>
                    <?php endif; ?>
                    <div class="small text-muted mt-1">
                      <?= (int) ($quiz->questions_count ?? 0) ?> questions
                      <?php if ((int) ($quiz->time_limit_sec ?? 0) > 0) : ?>
                        | <?= (int) ceil($quiz->time_limit_sec / 60) ?> min limit
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="d-flex gap-2">
                    <a href="<?= board_prep_url('quizzes/guest/' . (int) $quiz->quiz_id) ?>" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-play me-1"></i>Play guest
                    </a>
                    <a href="<?= esc($signupUrl) ?>" class="btn btn-primary btn-sm">
                      <i class="fas fa-save me-1"></i>Save results
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<?= $this->endSection() ?>
