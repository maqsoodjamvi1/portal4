<?= $this->extend('board_prep/layout') ?>

<?= $this->section('content') ?>
<?php
$questionCount = is_array($questions ?? null) ? count($questions) : 0;
$minutes       = (int) $timeLimit > 0 ? (int) ceil((int) $timeLimit / 60) : 0;
?>

<main class="bp-play-page">
  <section class="bp-play-hero">
    <div class="container bp-play-hero__inner">
      <div class="bp-play-hero__copy">
        <a href="<?= esc($dashboardUrl) ?>" class="bp-play-back">
          <i class="fas fa-arrow-left"></i>
          <span>All quizzes</span>
        </a>
        <div class="bp-play-kicker">
          <span><i class="fas fa-star"></i> Guest practice</span>
          <span>Result not saved</span>
        </div>
        <h1><?= esc($quiz->title) ?></h1>
        <p>Answer at your own pace, submit when ready, and sign up before future attempts to save your progress.</p>
      </div>

      <aside class="bp-play-status" aria-label="Quiz status">
        <?php if ((int) $timeLimit > 0) : ?>
          <div class="bp-play-status__item bp-play-status__item--timer">
            <span class="bp-play-status__label">Time left</span>
            <strong id="bpTimer">--:--</strong>
          </div>
        <?php else : ?>
          <div class="bp-play-status__item">
            <span class="bp-play-status__label">Mode</span>
            <strong>No timer</strong>
          </div>
        <?php endif; ?>
        <div class="bp-play-status__item">
          <span class="bp-play-status__label">Questions</span>
          <strong><?= (int) $questionCount ?></strong>
        </div>
        <?php if ($minutes > 0) : ?>
          <div class="bp-play-status__item">
            <span class="bp-play-status__label">Limit</span>
            <strong><?= (int) $minutes ?> min</strong>
          </div>
        <?php endif; ?>
      </aside>
    </div>
  </section>

  <section class="container bp-play-shell">
    <div class="bp-play-alert">
      <i class="fas fa-lock"></i>
      <div>
        <strong>Guest score is temporary.</strong>
        <span><a href="<?= esc($signupUrl) ?>">Sign up</a> or <a href="<?= esc($loginUrl) ?>">log in</a> before playing if you want results stored.</span>
      </div>
    </div>

    <div class="bp-play-progress" aria-live="polite">
      <div class="bp-play-progress__text">
        <span id="bpAnsweredCount">0</span> of <?= (int) $questionCount ?> answered
      </div>
      <div class="bp-play-progress__track" aria-hidden="true">
        <div class="bp-play-progress__bar" id="bpProgressBar"></div>
      </div>
    </div>

    <form method="post" action="<?= esc($scoreUrl) ?>" id="bpGuestForm" class="bp-question-form">
      <?= csrf_field() ?>
      <input type="hidden" name="quiz_id" value="<?= (int) $quiz->quiz_id ?>">

      <?php foreach ($questions as $q) : ?>
        <article class="bp-question-card" data-question-card>
          <div class="bp-question-card__head">
            <span class="bp-question-number"><?= (int) $q['n'] ?></span>
            <h2><?= esc($q['question']) ?></h2>
          </div>

          <div class="bp-answer-grid">
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

      <div class="bp-submit-bar">
        <a href="<?= esc($dashboardUrl) ?>" class="btn btn-outline-secondary">
          <i class="fas fa-times me-1"></i> Exit
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="fas fa-paper-plane me-1"></i> Submit quiz
        </button>
      </div>
    </form>
  </section>
</main>

<style>
  .bp-play-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #f7fbff 0%, #eef7f3 100%);
  }
  .bp-play-hero {
    background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);
    color: #fff;
  }
  .bp-play-hero__inner {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(240px, 340px);
    gap: 1.5rem;
    align-items: end;
    padding-top: 2rem;
    padding-bottom: 2rem;
  }
  .bp-play-back {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    color: rgba(255,255,255,.88);
    text-decoration: none;
    font-size: .92rem;
    margin-bottom: 1rem;
  }
  .bp-play-back:hover { color: #fff; }
  .bp-play-kicker {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: .8rem;
  }
  .bp-play-kicker span {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .25rem .65rem;
    border-radius: 999px;
    background: rgba(255,255,255,.16);
    color: #fff;
    font-size: .8rem;
    font-weight: 700;
  }
  .bp-play-hero h1 {
    max-width: 780px;
    margin: 0;
    font-size: clamp(1.75rem, 4vw, 3.25rem);
    line-height: 1.08;
    letter-spacing: 0;
    font-weight: 800;
  }
  .bp-play-hero p {
    max-width: 680px;
    margin: .8rem 0 0;
    color: rgba(255,255,255,.86);
    font-size: 1rem;
  }
  .bp-play-status {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .75rem;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 8px;
    padding: .85rem;
    backdrop-filter: blur(10px);
  }
  .bp-play-status__item {
    min-height: 74px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-radius: 8px;
    padding: .75rem;
    background: rgba(255,255,255,.15);
  }
  .bp-play-status__item--timer {
    grid-column: span 2;
  }
  .bp-play-status__label {
    font-size: .75rem;
    opacity: .8;
    text-transform: uppercase;
    font-weight: 700;
  }
  .bp-play-status strong {
    display: block;
    font-size: 1.55rem;
    line-height: 1.1;
  }
  .bp-play-shell {
    max-width: 920px;
    padding-top: 1.25rem;
    padding-bottom: 2rem;
  }
  .bp-play-alert {
    display: flex;
    gap: .8rem;
    align-items: flex-start;
    background: #fff8df;
    border: 1px solid #f2d984;
    color: #614a00;
    border-radius: 8px;
    padding: .85rem 1rem;
    margin-bottom: 1rem;
  }
  .bp-play-alert i {
    margin-top: .15rem;
  }
  .bp-play-alert strong,
  .bp-play-alert span {
    display: block;
  }
  .bp-play-alert a {
    color: #1d4ed8;
    font-weight: 700;
  }
  .bp-play-progress {
    position: sticky;
    top: 0;
    z-index: 10;
    background: rgba(247, 251, 255, .94);
    border: 1px solid #dbe7ef;
    border-radius: 8px;
    padding: .8rem;
    margin-bottom: 1rem;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
    backdrop-filter: blur(8px);
  }
  .bp-play-progress__text {
    color: #334155;
    font-weight: 700;
    margin-bottom: .45rem;
  }
  .bp-play-progress__track {
    height: 10px;
    overflow: hidden;
    border-radius: 999px;
    background: #dbeafe;
  }
  .bp-play-progress__bar {
    width: 0%;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #0f766e, #2563eb);
    transition: width .2s ease;
  }
  .bp-question-card {
    background: #fff;
    border: 1px solid #dfe8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
  }
  .bp-question-card.is-answered {
    border-color: rgba(15, 118, 110, .45);
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
    background: #e7f7f4;
    color: #0f766e;
    font-weight: 800;
    font-size: 1.1rem;
  }
  .bp-question-card h2 {
    margin: 0;
    color: #111827;
    font-size: 1.08rem;
    line-height: 1.45;
    letter-spacing: 0;
  }
  .bp-answer-grid {
    display: grid;
    gap: .65rem;
  }
  .bp-answer-option {
    position: relative;
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr);
    gap: .75rem;
    align-items: center;
    min-height: 58px;
    margin: 0;
    padding: .75rem .85rem;
    border: 1px solid #d8e3ec;
    border-radius: 8px;
    background: #f8fbfd;
    color: #1f2937;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
  }
  .bp-answer-option:hover {
    border-color: #60a5fa;
    background: #eef7ff;
  }
  .bp-answer-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }
  .bp-answer-key {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #d5e1eb;
    color: #2563eb;
    font-weight: 800;
  }
  .bp-answer-text {
    line-height: 1.4;
    word-break: break-word;
  }
  .bp-answer-input:checked + .bp-answer-key {
    background: #0f766e;
    border-color: #0f766e;
    color: #fff;
  }
  .bp-answer-option.is-selected {
    border-color: #0f766e;
    background: #ecfdf5;
    box-shadow: 0 8px 22px rgba(15, 118, 110, .12);
  }
  .bp-submit-bar {
    position: sticky;
    bottom: 0;
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    align-items: center;
    margin-top: 1.25rem;
    padding: .85rem;
    border: 1px solid #dfe8f0;
    border-radius: 8px 8px 0 0;
    background: rgba(255,255,255,.96);
    box-shadow: 0 -10px 30px rgba(15, 23, 42, .08);
    backdrop-filter: blur(8px);
  }
  @media (max-width: 767.98px) {
    .bp-play-hero__inner {
      grid-template-columns: 1fr;
      padding-top: 1.25rem;
      padding-bottom: 1.25rem;
    }
    .bp-play-status {
      grid-template-columns: 1fr 1fr;
    }
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
    if (answeredEl) answeredEl.textContent = answered;
    if (progressEl) progressEl.style.width = total > 0 ? ((answered / total) * 100) + '%' : '0%';
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
