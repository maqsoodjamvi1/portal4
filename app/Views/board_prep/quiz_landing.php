<?= $this->extend('board_prep/layout') ?>

<?= $this->section('content') ?>
<?php
$quizzes = is_array($allQuizzes ?? null) ? $allQuizzes : (is_array($featuredQuizzes ?? null) ? $featuredQuizzes : []);

$classes = [];
$subjects = [];
$boards = [];
foreach ($quizzes as $quiz) {
    $grade = trim((string) ($quiz->prep_grade_level ?? ''));
    if ($grade !== '') {
        $classes[$grade] = board_prep_grade_label($grade);
    }

    $subject = trim((string) ($quiz->subject_name ?? ''));
    if ($subject !== '') {
        $subjects[$subject] = $subject;
    }

    $board = trim((string) ($quiz->board_name ?? ''));
    if ($board !== '') {
        $boards[$board] = $board;
    }
}
asort($classes);
asort($subjects);
asort($boards);

$quizCount = count($quizzes);
$subjectCount = count($subjects);
$boardCount = count($boards);
?>

<nav class="navbar navbar-expand-lg navbar-dark board-prep-nav">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= board_prep_url('') ?>">
      <i class="fas fa-book-reader me-1"></i><?= esc($productName ?? 'Live Education Quiz') ?>
    </a>
    <div class="d-flex gap-2 ms-auto">
      <a href="<?= esc($loginUrl) ?>" class="btn btn-sm btn-outline-light">Log in</a>
      <a href="<?= esc($signupUrl) ?>" class="btn btn-sm btn-light">Sign up</a>
    </div>
  </div>
</nav>

<main class="container py-4 leq-dashboard-home">
  <section class="bp-dashboard-hero board-prep-card mb-4">
    <div class="bp-dashboard-hero__board leq-home-hero">
      <div class="bp-board-logo-wrap bp-board-logo-wrap--placeholder">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <div class="bp-board-title-wrap">
        <p class="bp-board-eyebrow mb-1">Free practice quizzes</p>
        <h1 class="bp-board-title"><?= esc($productName ?? 'Live Education Quiz') ?></h1>
        <p class="leq-home-hero__copy mb-0">
          Select your class, subject, and board. Play instantly as a guest, or sign up before playing to save your results.
        </p>
      </div>
    </div>

    <div class="bp-dashboard-hero__student">
      <div class="row g-3">
        <div class="col-4">
          <div class="board-prep-stat bp-stat-card h-100 text-center">
            <div class="text-muted small bp-stat-label">Quizzes</div>
            <div class="h3 mb-0 bp-stat-value"><?= (int) $quizCount ?></div>
          </div>
        </div>
        <div class="col-4">
          <div class="board-prep-stat bp-stat-card h-100 text-center">
            <div class="text-muted small bp-stat-label">Subjects</div>
            <div class="h3 mb-0 bp-stat-value"><?= (int) $subjectCount ?></div>
          </div>
        </div>
        <div class="col-4">
          <div class="board-prep-stat bp-stat-card h-100 text-center">
            <div class="text-muted small bp-stat-label">Boards</div>
            <div class="h3 mb-0 bp-stat-value"><?= (int) $boardCount ?></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="board-prep-card leq-filter-card mb-4">
    <div class="card-head d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h2 class="h5 mb-0"><i class="fas fa-filter me-2"></i>Find a quiz</h2>
      <span class="small opacity-75"><span id="leqVisibleCount"><?= (int) $quizCount ?></span> quiz<?= $quizCount === 1 ? '' : 'zes' ?> shown</span>
    </div>
    <div class="p-3">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label small text-muted fw-semibold" for="leqClassFilter">Class</label>
          <select id="leqClassFilter" class="form-select leq-filter">
            <option value="">All classes</option>
            <?php foreach ($classes as $key => $label) : ?>
              <option value="<?= esc(strtolower($key), 'attr') ?>"><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small text-muted fw-semibold" for="leqSubjectFilter">Subject</label>
          <select id="leqSubjectFilter" class="form-select leq-filter">
            <option value="">All subjects</option>
            <?php foreach ($subjects as $subject) : ?>
              <option value="<?= esc(strtolower($subject), 'attr') ?>"><?= esc($subject) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small text-muted fw-semibold" for="leqBoardFilter">Board</label>
          <select id="leqBoardFilter" class="form-select leq-filter">
            <option value="">All boards</option>
            <?php foreach ($boards as $board) : ?>
              <option value="<?= esc(strtolower($board), 'attr') ?>"><?= esc($board) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="leqResetFilters">
          <i class="fas fa-undo me-1"></i>Reset
        </button>
        <a href="<?= esc($signupUrl) ?>" class="btn btn-sm btn-bp-primary">
          <i class="fas fa-save me-1"></i>Sign up to save results
        </a>
      </div>
    </div>
  </section>

  <section class="leq-quiz-section">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
      <div class="pe-2 mb-2">
        <h2 class="h5 mb-1 fw-bold">All quizzes</h2>
        <p class="text-muted small mb-0">Guest play is open. Saved result history requires signup or login.</p>
      </div>
      <a href="<?= esc($loginUrl) ?>" class="btn btn-sm btn-outline-secondary mb-1">
        <i class="fas fa-tachometer-alt me-1"></i>Login dashboard
      </a>
    </div>

    <?php if ($quizzes === []) : ?>
      <div class="alert alert-info mb-0">No quizzes are published yet. Check back soon.</div>
    <?php else : ?>
      <div class="leq-quiz-grid" id="leqQuizGrid">
        <?php foreach ($quizzes as $quiz) : ?>
          <?php
            $grade = trim((string) ($quiz->prep_grade_level ?? ''));
            $subject = trim((string) ($quiz->subject_name ?? ''));
            $board = trim((string) ($quiz->board_name ?? ''));
          ?>
          <article
            class="board-prep-card leq-quiz-card"
            data-class="<?= esc(strtolower($grade), 'attr') ?>"
            data-subject="<?= esc(strtolower($subject), 'attr') ?>"
            data-board="<?= esc(strtolower($board), 'attr') ?>"
          >
            <div class="leq-quiz-card__body">
              <div class="leq-quiz-card__badges">
                <?php if ($grade !== '') : ?>
                  <span class="badge text-bg-light border"><?= esc(board_prep_grade_label($grade)) ?></span>
                <?php endif; ?>
                <?php if ($subject !== '') : ?>
                  <span class="badge text-bg-light border"><?= esc($subject) ?></span>
                <?php endif; ?>
                <?php if ($board !== '') : ?>
                  <span class="badge text-bg-light border"><?= esc($board) ?></span>
                <?php endif; ?>
              </div>
              <h3><?= esc($quiz->title) ?></h3>
              <div class="small text-muted">
                <?= (int) ($quiz->questions_count ?? 0) ?> questions
                <?php if ((int) ($quiz->time_limit_sec ?? 0) > 0) : ?>
                  | <?= (int) ceil($quiz->time_limit_sec / 60) ?> min limit
                <?php endif; ?>
              </div>
            </div>
            <div class="leq-quiz-card__actions">
              <a href="<?= board_prep_url('quizzes/guest/' . (int) $quiz->quiz_id) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-play me-1"></i>Play guest
              </a>
              <a href="<?= esc($signupUrl) ?>" class="btn btn-sm btn-bp-primary">
                Save results
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="alert alert-secondary mt-3 d-none" id="leqNoResults">No quizzes match these filters.</div>
    <?php endif; ?>
  </section>
</main>

<style>
  .leq-dashboard-home {
    max-width: 1120px;
  }
  .leq-home-hero__copy {
    max-width: 760px;
    color: rgba(255,255,255,.9);
  }
  .leq-filter-card .card-head {
    background: var(--bp-primary);
  }
  .leq-quiz-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
  }
  .leq-quiz-card {
    min-height: 230px;
    display: flex;
    flex-direction: column;
  }
  .leq-quiz-card__body {
    padding: 1rem;
    flex: 1;
  }
  .leq-quiz-card__badges {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
    margin-bottom: .75rem;
  }
  .leq-quiz-card h3 {
    margin: 0 0 .5rem;
    color: #1a3d32;
    font-size: 1.05rem;
    line-height: 1.35;
    letter-spacing: 0;
    font-weight: 700;
  }
  .leq-quiz-card__actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    justify-content: space-between;
    padding: .85rem 1rem;
    border-top: 1px solid #e5ece8;
    background: #f8fbf9;
  }
  @media (max-width: 991.98px) {
    .leq-quiz-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media (max-width: 575.98px) {
    .leq-quiz-grid {
      grid-template-columns: 1fr;
    }
    .leq-quiz-card__actions .btn {
      width: 100%;
    }
  }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var filters = {
    class: document.getElementById('leqClassFilter'),
    subject: document.getElementById('leqSubjectFilter'),
    board: document.getElementById('leqBoardFilter')
  };
  var cards = Array.prototype.slice.call(document.querySelectorAll('.leq-quiz-card'));
  var visibleCount = document.getElementById('leqVisibleCount');
  var noResults = document.getElementById('leqNoResults');
  var reset = document.getElementById('leqResetFilters');

  function matches(card, key, value) {
    return value === '' || (card.getAttribute('data-' + key) || '') === value;
  }

  function applyFilters() {
    var values = {
      class: filters.class ? filters.class.value : '',
      subject: filters.subject ? filters.subject.value : '',
      board: filters.board ? filters.board.value : ''
    };
    var shown = 0;
    cards.forEach(function (card) {
      var show = matches(card, 'class', values.class)
        && matches(card, 'subject', values.subject)
        && matches(card, 'board', values.board);
      card.classList.toggle('d-none', !show);
      if (show) shown++;
    });
    if (visibleCount) visibleCount.textContent = shown;
    if (noResults) noResults.classList.toggle('d-none', shown > 0);
  }

  Object.keys(filters).forEach(function (key) {
    if (filters[key]) filters[key].addEventListener('change', applyFilters);
  });
  if (reset) {
    reset.addEventListener('click', function () {
      Object.keys(filters).forEach(function (key) {
        if (filters[key]) filters[key].value = '';
      });
      applyFilters();
    });
  }
  applyFilters();
})();
</script>
<?= $this->endSection() ?>
