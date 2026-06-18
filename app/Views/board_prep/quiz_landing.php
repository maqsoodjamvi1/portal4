<?= $this->extend('board_prep/layout') ?>

<?= $this->section('content') ?>

<header class="leq-topbar">
  <div class="container leq-topbar__inner">
    <a href="<?= board_prep_url('') ?>" class="leq-brand">
      <span class="leq-brand__mark"><i class="fas fa-bolt"></i></span>
      <span><?= esc($productName ?? 'Live Education Quiz') ?></span>
    </a>
    <nav class="leq-topbar__actions" aria-label="Account">
      <a href="<?= esc($loginUrl) ?>" class="btn btn-outline-secondary btn-sm">Log in</a>
      <a href="<?= esc($signupUrl) ?>" class="btn btn-primary btn-sm">Sign up</a>
    </nav>
  </div>
</header>

<main>
  <section class="leq-hero">
    <div class="container leq-hero__grid">
      <div class="leq-hero__copy">
        <span class="leq-kicker"><i class="fas fa-play-circle"></i> Free quiz practice</span>
        <h1><?= esc($productName ?? 'Live Education Quiz') ?></h1>
        <p>
          Browse published quizzes, play instantly as a guest, and create a free account
          when you want your scores saved in your results dashboard.
        </p>
        <div class="leq-hero__actions">
          <a href="<?= esc($dashboardUrl) ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-th-list me-1"></i> Browse quizzes
          </a>
          <a href="<?= esc($loginUrl) ?>" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-tachometer-alt me-1"></i> Login dashboard
          </a>
        </div>
        <div class="leq-save-note">
          <i class="fas fa-lock"></i>
          Guest play is open. Result history is stored only after signup or login.
        </div>
      </div>

      <div class="leq-hero__panel" aria-label="Quiz flow">
        <div class="leq-flow-step">
          <span>1</span>
          <div>
            <strong>Choose a quiz</strong>
            <small>Open the quiz dashboard and pick any subject.</small>
          </div>
        </div>
        <div class="leq-flow-step">
          <span>2</span>
          <div>
            <strong>Play as guest</strong>
            <small>Answer questions and see an instant score.</small>
          </div>
        </div>
        <div class="leq-flow-step">
          <span>3</span>
          <div>
            <strong>Sign up to save</strong>
            <small>Saved attempts and progress charts require an account.</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if (! empty($featuredQuizzes)) : ?>
    <section class="leq-section">
      <div class="container">
        <div class="leq-section__head">
          <div>
            <span class="leq-section__eyebrow">Start now</span>
            <h2>Featured quizzes</h2>
          </div>
          <a href="<?= esc($dashboardUrl) ?>" class="btn btn-outline-primary btn-sm">View all quizzes</a>
        </div>

        <div class="leq-quiz-grid">
          <?php foreach ($featuredQuizzes as $quiz) : ?>
            <article class="leq-quiz-card">
              <div class="leq-quiz-card__meta">
                <?php if (! empty($quiz->subject_name)) : ?>
                  <span><?= esc($quiz->subject_name) ?></span>
                <?php endif; ?>
                <?php if (! empty($quiz->board_name)) : ?>
                  <span><?= esc($quiz->board_name) ?></span>
                <?php endif; ?>
              </div>
              <h3><?= esc($quiz->title) ?></h3>
              <p>
                <?= (int) ($quiz->questions_count ?? 0) ?> questions
                <?php if ((int) ($quiz->time_limit_sec ?? 0) > 0) : ?>
                  | <?= (int) ceil($quiz->time_limit_sec / 60) ?> min
                <?php endif; ?>
              </p>
              <div class="leq-quiz-card__actions">
                <a href="<?= board_prep_url('quizzes/guest/' . (int) $quiz->quiz_id) ?>" class="btn btn-outline-primary btn-sm">
                  <i class="fas fa-play me-1"></i> Play guest
                </a>
                <a href="<?= esc($signupUrl) ?>" class="btn btn-primary btn-sm">
                  Save results
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="leq-account-band">
    <div class="container leq-account-band__inner">
      <div>
        <h2>Want your results stored?</h2>
        <p>Create an account or log in before taking a quiz. Your completed attempts will appear in the results dashboard.</p>
      </div>
      <div class="leq-account-band__actions">
        <a href="<?= esc($signupUrl) ?>" class="btn btn-light"><i class="fas fa-user-plus me-1"></i> Sign up free</a>
        <a href="<?= esc($loginUrl) ?>" class="btn btn-outline-light">Log in</a>
      </div>
    </div>
  </section>
</main>

<style>
  .leq-topbar {
    background: #ffffff;
    border-bottom: 1px solid #e6eaef;
  }
  .leq-topbar__inner {
    min-height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }
  .leq-brand {
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    color: #172033;
    font-weight: 700;
    text-decoration: none;
  }
  .leq-brand:hover { color: #172033; }
  .leq-brand__mark {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #0f766e;
    color: #fff;
  }
  .leq-topbar__actions {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .leq-hero {
    background: #f7fafc;
    border-bottom: 1px solid #e6eaef;
  }
  .leq-hero__grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr);
    gap: 2rem;
    align-items: center;
    padding-top: 4.5rem;
    padding-bottom: 4.5rem;
  }
  .leq-kicker {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    margin-bottom: 1rem;
    color: #0f766e;
    font-weight: 700;
    font-size: .9rem;
  }
  .leq-hero h1 {
    margin: 0 0 1rem;
    color: #111827;
    font-size: clamp(2.25rem, 5vw, 4.75rem);
    line-height: 1;
    letter-spacing: 0;
    font-weight: 800;
  }
  .leq-hero p {
    max-width: 680px;
    color: #4b5563;
    font-size: 1.15rem;
    margin-bottom: 1.5rem;
  }
  .leq-hero__actions,
  .leq-account-band__actions {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
  }
  .leq-save-note {
    margin-top: 1rem;
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    color: #475569;
    background: #ffffff;
    border: 1px solid #d9e2ea;
    border-radius: 8px;
    padding: .7rem .85rem;
    font-size: .95rem;
  }
  .leq-hero__panel {
    background: #ffffff;
    border: 1px solid #dfe7ef;
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
  }
  .leq-flow-step {
    display: flex;
    gap: .85rem;
    padding: 1rem 0;
    border-bottom: 1px solid #edf1f5;
  }
  .leq-flow-step:last-child { border-bottom: 0; }
  .leq-flow-step span {
    flex: 0 0 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e7f7f4;
    color: #0f766e;
    font-weight: 800;
  }
  .leq-flow-step strong {
    display: block;
    color: #172033;
  }
  .leq-flow-step small {
    display: block;
    color: #64748b;
    margin-top: .15rem;
  }
  .leq-section {
    padding: 3.5rem 0;
    background: #ffffff;
  }
  .leq-section__head {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 1rem;
    margin-bottom: 1.25rem;
  }
  .leq-section__eyebrow {
    display: block;
    color: #0f766e;
    font-size: .8rem;
    font-weight: 700;
    text-transform: uppercase;
  }
  .leq-section h2,
  .leq-account-band h2 {
    margin: 0;
    color: #172033;
    font-size: 1.6rem;
    font-weight: 800;
  }
  .leq-quiz-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
  }
  .leq-quiz-card {
    min-height: 210px;
    display: flex;
    flex-direction: column;
    padding: 1rem;
    background: #ffffff;
    border: 1px solid #e1e7ee;
    border-radius: 8px;
  }
  .leq-quiz-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
    min-height: 28px;
  }
  .leq-quiz-card__meta span {
    border: 1px solid #d7e2ec;
    border-radius: 999px;
    padding: .15rem .55rem;
    color: #475569;
    font-size: .78rem;
  }
  .leq-quiz-card h3 {
    margin: .75rem 0 .35rem;
    color: #111827;
    font-size: 1rem;
    line-height: 1.35;
    font-weight: 700;
  }
  .leq-quiz-card p {
    color: #64748b;
    margin-bottom: 1rem;
  }
  .leq-quiz-card__actions {
    margin-top: auto;
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
  }
  .leq-account-band {
    background: #0f766e;
    color: #fff;
    padding: 2.25rem 0;
  }
  .leq-account-band__inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
  }
  .leq-account-band h2 { color: #fff; }
  .leq-account-band p {
    color: rgba(255,255,255,.86);
    margin: .35rem 0 0;
    max-width: 650px;
  }
  @media (max-width: 991.98px) {
    .leq-hero__grid,
    .leq-quiz-grid {
      grid-template-columns: 1fr;
    }
    .leq-hero__grid {
      padding-top: 3rem;
      padding-bottom: 3rem;
    }
  }
  @media (max-width: 767.98px) {
    .leq-section__head,
    .leq-account-band__inner {
      align-items: stretch;
      flex-direction: column;
    }
    .leq-hero__actions .btn,
    .leq-account-band__actions .btn,
    .leq-quiz-card__actions .btn {
      width: 100%;
    }
  }
</style>

<?= $this->endSection() ?>
