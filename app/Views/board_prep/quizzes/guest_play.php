<?= $this->extend('board_prep/layout') ?>

<?= $this->section('content') ?>
<?php
$questionCount = is_array($questions ?? null) ? count($questions) : 0;
$minutes       = (int) $timeLimit > 0 ? (int) ceil((int) $timeLimit / 60) : 0;
?>

<nav class="navbar navbar-expand-lg navbar-dark board-prep-nav">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= esc($dashboardUrl) ?>">
      <i class="fas fa-book-reader me-1"></i><?= esc($productName ?? 'Live Education Quiz') ?>
    </a>
    <div class="d-flex gap-2 ms-auto">
      <a href="<?= esc($loginUrl) ?>" class="btn btn-sm btn-outline-light">Log in</a>
      <a href="<?= esc($signupUrl) ?>" class="btn btn-sm btn-light">Sign up</a>
    </div>
  </div>
</nav>

<main class="container py-4 bp-quiz-play">
  <section class="bp-dashboard-hero board-prep-card mb-4">
    <div class="bp-dashboard-hero__board bp-quiz-play__hero">
      <div class="bp-board-logo-wrap bp-board-logo-wrap--placeholder">
        <i class="fas fa-question-circle"></i>
      </div>
      <div class="bp-board-title-wrap">
        <a href="<?= esc($dashboardUrl) ?>" class="bp-quiz-play__back">
          <i class="fas fa-arrow-left me-1"></i> All quizzes
        </a>
        <p class="bp-board-eyebrow mb-1">Guest practice</p>
        <h1 class="bp-board-title"><?= esc($quiz->title) ?></h1>
      </div>
    </div>

    <div class="bp-dashboard-hero__student bp-quiz-play__meta">
      <div class="row g-3">
        <div class="col-4">
          <div class="board-prep-stat bp-stat-card h-100 text-center">
            <div class="text-muted small bp-stat-label">Questions</div>
            <div class="h3 mb-0 bp-stat-value"><?= (int) $questionCount ?></div>
          </div>
        </div>
        <div class="col-4">
          <div class="board-prep-stat bp-stat-card h-100 text-center">
            <div class="text-muted small bp-stat-label">Answered</div>
            <div class="h3 mb-0 bp-stat-value"><span id="bpAnsweredCount">0</span></div>
          </div>
        </div>
        <div class="col-4">
          <div class="board-prep-stat bp-stat-card h-100 text-center">
            <div class="text-muted small bp-stat-label"><?= (int) $timeLimit > 0 ? 'Time left' : 'Limit' ?></div>
            <div class="h3 mb-0 bp-stat-value"><?= (int) $timeLimit > 0 ? '<span id="bpTimer">--:--</span>' : 'Open' ?></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="alert alert-warning d-flex align-items-start">
    <i class="fas fa-info-circle me-2 mt-1"></i>
    <div>
      <strong>Guest mode:</strong> this attempt shows an instant score but is not saved.
      <a href="<?= esc($signupUrl) ?>" class="fw-semibold">Sign up</a> or
      <a href="<?= esc($loginUrl) ?>" class="fw-semibold">log in</a> before playing to store results.
    </div>
  </div>

  <div class="bp-play-progress board-prep-card mb-3" aria-live="polite">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <strong class="small text-muted">Quiz progress</strong>
      <span class="small text-muted"><span id="bpProgressText">0</span>% complete</span>
    </div>
    <div class="bp-play-progress__track" aria-hidden="true">
      <div class="bp-play-progress__bar" id="bpProgressBar"></div>
    </div>
  </div>

  <form method="post" action="<?= esc($scoreUrl) ?>" id="bpGuestForm">
    <?= csrf_field() ?>
    <input type="hidden" name="quiz_id" value="<?= (int) $quiz->quiz_id ?>">

    <?php foreach ($questions as $q) : ?>
      <article class="board-prep-card bp-question-card mb-3" data-question-card>
        <div class="bp-question-card__head">
          <span class="bp-question-number"><?= (int) $q['n'] ?></span>
          <h2><?= esc($q['question']) ?></h2>
        </div>

        <div class="bp-answer-list">
          <?php foreach ($q['options'] as $opt) : ?>
            <?php
              $inputId = 'q' . (int) $q['id'] . '_' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $opt['key']);
            ?>
            <label class="bp-answer-option" for="<?= esc($inputId, 'attr') ?>">
              <input class="bp-answer-input" type="radio"
                     name="answers[<?= (int) $q['id'] ?>]"
                     id="<?= esc($inputId, 'attr') ?>"
                     value="<?= esc($opt['key'], 'attr') ?>">
              <span class="bp-answer-key"><?= esc($opt['key']) ?></span>
              <span class="bp-answer-text"><?= esc($opt['text']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </article>
    <?php endforeach; ?>

    <div class="bp-submit-bar board-prep-card">
      <a href="<?= esc($dashboardUrl) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-times me-1"></i> Exit quiz
      </a>
      <button type="submit" class="btn btn-bp-primary btn-lg">
        <i class="fas fa-check me-1"></i> Submit and see score
      </button>
    </div>
  </form>
</main>

<style>
  .bp-quiz-play {
    max-width: 980px;
  }
  .bp-quiz-play__hero {
    align-items: flex-start;
  }
  .bp-quiz-play__back {
    display: inline-flex;
    align-items: center;
    color: rgba(255,255,255,.88);
    text-decoration: none;
    font-size: .85rem;
    margin-bottom: .5rem;
  }
  .bp-quiz-play__back:hover {
    color: #fff;
  }
  .bp-quiz-play__meta {
    padding-top: 1rem;
  }
  .bp-play-progress {
    position: sticky;
    top: 0;
    z-index: 20;
    padding: .85rem 1rem;
  }
  .bp-play-progress__track {
    height: 9px;
    overflow: hidden;
    border-radius: 999px;
    background: #e1ebe6;
  }
  .bp-play-progress__bar {
    width: 0%;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, var(--bp-primary), var(--bp-accent));
    transition: width .2s ease;
  }
  .bp-question-card {
    padding: 1rem;
  }
  .bp-question-card.is-answered {
    box-shadow: 0 8px 30px rgba(26, 95, 74, .12);
  }
  .bp-question-card__head {
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr);
    gap: .85rem;
    align-items: start;
    margin-bottom: 1rem;
  }
  .bp-question-number {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e8f4ef;
    color: var(--bp-primary);
    font-weight: 800;
  }
  .bp-question-card h2 {
    margin: 0;
    color: #1a3d32;
    font-size: 1.05rem;
    line-height: 1.45;
    letter-spacing: 0;
  }
  .bp-answer-list {
    display: grid;
    gap: .6rem;
  }
  .bp-answer-option {
    position: relative;
    display: grid;
    grid-template-columns: 36px minmax(0, 1fr);
    gap: .75rem;
    align-items: center;
    min-height: 56px;
    margin: 0;
    padding: .7rem .85rem;
    border: 1px solid #e5ece8;
    border-radius: 8px;
    background: #f8fbf9;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
  }
  .bp-answer-option:hover {
    border-color: var(--bp-primary);
    background: #f1f8f5;
  }
  .bp-answer-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }
  .bp-answer-key {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #d7e4de;
    color: var(--bp-primary);
    font-weight: 800;
  }
  .bp-answer-text {
    color: #243b32;
    line-height: 1.4;
    word-break: break-word;
  }
  .bp-answer-input:checked + .bp-answer-key {
    background: var(--bp-primary);
    border-color: var(--bp-primary);
    color: #fff;
  }
  .bp-answer-option.is-selected {
    border-color: var(--bp-primary);
    background: #edf8f3;
    box-shadow: 0 5px 16px rgba(26, 95, 74, .12);
  }
  .bp-submit-bar {
    position: sticky;
    bottom: 0;
    z-index: 30;
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    align-items: center;
    padding: .85rem;
    border-radius: 8px 8px 0 0;
  }
  @media (max-width: 767.98px) {
    .bp-question-card {
      padding: .85rem;
    }
    .bp-question-card__head {
      grid-template-columns: 38px minmax(0, 1fr);
      gap: .65rem;
    }
    .bp-question-number {
      width: 38px;
      height: 38px;
    }
    .bp-answer-option {
      grid-template-columns: 34px minmax(0, 1fr);
      padding: .7rem;
    }
    .bp-submit-bar {
      flex-direction: column-reverse;
      align-items: stretch;
    }
    .bp-submit-bar .btn {
      width: 100%;
    }
  }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var total = <?= (int) $questionCount ?>;
  var answeredEl = document.getElementById('bpAnsweredCount');
  var progressTextEl = document.getElementById('bpProgressText');
  var progressEl = document.getElementById('bpProgressBar');
  var form = document.getElementById('bpGuestForm');

  function updateProgress() {
    var answered = 0;
    document.querySelectorAll('[data-question-card]').forEach(function (card) {
      var checked = card.querySelector('input[type="radio"]:checked');
      card.classList.toggle('is-answered', !!checked);
      if (checked) answered++;
    });
    document.querySelectorAll('.bp-answer-option').forEach(function (label) {
      var input = label.querySelector('.bp-answer-input');
      label.classList.toggle('is-selected', !!input && input.checked);
    });
    var pct = total > 0 ? Math.round((answered / total) * 100) : 0;
    if (answeredEl) answeredEl.textContent = answered;
    if (progressTextEl) progressTextEl.textContent = pct;
    if (progressEl) progressEl.style.width = pct + '%';
  }

  document.querySelectorAll('.bp-answer-input').forEach(function (input) {
    input.addEventListener('change', updateProgress);
  });
  updateProgress();

  <?php if ((int) $timeLimit > 0) : ?>
  var remaining = <?= (int) $timeLimit ?>;
  var timerEl = document.getElementById('bpTimer');
  function renderTimer() {
    var m = Math.floor(remaining / 60), s = remaining % 60;
    if (timerEl) timerEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
  }
  renderTimer();
  var timer = setInterval(function () {
    remaining--;
    if (remaining <= 0) {
      clearInterval(timer);
      if (form) form.submit();
      return;
    }
    renderTimer();
  }, 1000);
  <?php endif; ?>
})();
</script>
<?= $this->endSection() ?>
