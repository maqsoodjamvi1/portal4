<?= $this->extend('board_prep/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4" style="max-width: 820px;">

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
      <a href="<?= esc($dashboardUrl) ?>" class="text-decoration-none small"><i class="fas fa-arrow-left me-1"></i>All quizzes</a>
      <h1 class="h4 mb-0 mt-1"><?= esc($quiz->title) ?></h1>
      <span class="badge text-bg-warning mt-1">Guest mode · results are not saved</span>
    </div>
    <?php if ((int) $timeLimit > 0) : ?>
      <div class="text-end">
        <div class="small text-muted">Time left</div>
        <div class="h4 mb-0" id="bpTimer">--:--</div>
      </div>
    <?php endif; ?>
  </div>

  <div class="alert alert-warning d-flex align-items-center py-2">
    <i class="fas fa-info-circle me-2"></i>
    <div class="small">You're playing as a guest. <a href="<?= esc($signupUrl) ?>">Sign up</a> to save your score and track progress.</div>
  </div>

  <form method="post" action="<?= esc($scoreUrl) ?>" id="bpGuestForm">
    <?= csrf_field() ?>
    <input type="hidden" name="quiz_id" value="<?= (int) $quiz->quiz_id ?>">

    <?php foreach ($questions as $q) : ?>
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <div class="fw-semibold mb-2"><?= (int) $q['n'] ?>. <?= esc($q['question']) ?></div>
          <?php foreach ($q['options'] as $opt) : ?>
            <div class="form-check">
              <input class="form-check-input" type="radio"
                     name="answers[<?= (int) $q['id'] ?>]"
                     id="q<?= (int) $q['id'] ?>_<?= esc($opt['key'], 'attr') ?>"
                     value="<?= esc($opt['key'], 'attr') ?>">
              <label class="form-check-label" for="q<?= (int) $q['id'] ?>_<?= esc($opt['key'], 'attr') ?>">
                <strong><?= esc($opt['key']) ?>.</strong> <?= esc($opt['text']) ?>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-between align-items-center mb-5">
      <a href="<?= esc($dashboardUrl) ?>" class="btn btn-link text-muted">Cancel</a>
      <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-check me-1"></i>Submit &amp; see score</button>
    </div>
  </form>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ((int) $timeLimit > 0) : ?>
<script>
(function () {
  var remaining = <?= (int) $timeLimit ?>;
  var el = document.getElementById('bpTimer');
  var form = document.getElementById('bpGuestForm');
  function render() {
    var m = Math.floor(remaining / 60), s = remaining % 60;
    el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
  }
  render();
  var t = setInterval(function () {
    remaining--;
    if (remaining <= 0) { clearInterval(t); form.submit(); return; }
    render();
  }, 1000);
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
