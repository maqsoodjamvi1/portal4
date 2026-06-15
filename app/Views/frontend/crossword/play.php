<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
  $puzzleCount = (int) ($puzzleCount ?? count($puzzles ?? []));
  $startIndex  = max(0, min($puzzleCount - 1, (int) ($puzzleIndex ?? 0)));
  $playBase    = site_url('student/crossword/play/' . (int) ($assignment['id'] ?? 0));
?>

<style>
  .puzzle-pane { display: none; }
  .puzzle-pane.active { display: block; }
  .crossword-cell { width: 42px; height: 42px; padding: 2px; vertical-align: middle; }
  .crossword-cell.block { background: #333; }
  .crossword-cell.blank { background: #ddd; }
  .crossword-cell input { height: 36px; font-weight: bold; }
  .puzzle-nav { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 20px; }
  .puzzle-progress { font-size: 1.1rem; font-weight: 600; }
</style>

<div class="container py-4">
  <h2><?= esc($title) ?></h2>
  <p class="text-muted">Solve one puzzle at a time. Use Next and Previous to move between puzzles, then submit on the last puzzle.</p>
  <?php if (! empty($assignment['due_date'])): ?>
    <p class="text-muted"><strong>Due:</strong> <?= esc($assignment['due_date']) ?></p>
  <?php endif; ?>

  <form method="post" action="<?= site_url('student/crossword/submit') ?>" id="crosswordForm">
    <?= csrf_field() ?>
    <input type="hidden" name="assignment_id" value="<?= (int) ($assignment['id'] ?? 0) ?>">

    <div class="puzzle-progress mb-3">
      Puzzle <span id="currentPuzzleNum"><?= $startIndex + 1 ?></span> of <?= $puzzleCount ?>
    </div>

    <?php foreach ($puzzles as $pi => $puzzle): ?>
      <?php
        $cells = $puzzle['cells'] ?? [];
        $size  = (int) ($puzzle['size'] ?? 7);
      ?>
      <div class="card mb-3 puzzle-pane<?= $pi === $startIndex ? ' active' : '' ?>" data-puzzle-index="<?= (int) $pi ?>">
        <div class="card-header"><strong>Puzzle <?= $pi + 1 ?></strong></div>
        <div class="card-body overflow-auto">
          <table class="table table-bordered text-center mb-0" style="width:auto;margin:0 auto;">
            <?php for ($r = 0; $r < $size; $r++): ?>
            <tr>
              <?php for ($c = 0; $c < $size; $c++):
                $cell = $cells[$r][$c] ?? ['type' => 'blank'];
                $type = $cell['type'] ?? 'blank';
                $val  = $cell['value'] ?? '';
                $isAnswer = ! empty($cell['answer']);
                $key = "{$pi}_{$r}_{$c}";
              ?>
              <td class="crossword-cell <?= esc($type) ?>">
                <?php if ($isAnswer && in_array($type, ['result', 'operator', 'letter'], true)): ?>
                  <input type="text" name="answers[<?= esc($key) ?>]" maxlength="<?= $type === 'letter' ? 1 : 6 ?>"
                         class="form-control form-control-sm text-center p-0 puzzle-answer" data-puzzle="<?= (int) $pi ?>"
                         autocomplete="off">
                <?php elseif ($type === 'block' || $type === 'blank'): ?>
                  &nbsp;
                <?php else: ?>
                  <?= esc((string) $val) ?>
                <?php endif; ?>
              </td>
              <?php endfor; ?>
            </tr>
            <?php endfor; ?>
          </table>

          <?php if (! empty($puzzle['clues'])): ?>
            <div class="mt-3 small">
              <?php foreach (['across' => 'Across', 'down' => 'Down'] as $ck => $cl): ?>
                <?php if (! empty($puzzle['clues'][$ck])): ?>
                  <p class="mb-1"><strong><?= $cl ?>:</strong></p>
                  <ul>
                    <?php foreach ($puzzle['clues'][$ck] as $clue): ?>
                      <li><?= (int) ($clue['num'] ?? 0) ?>. <?= esc($clue['clue'] ?? '') ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="puzzle-nav">
      <div>
        <button type="button" class="btn btn-outline-secondary btn-lg" id="prevPuzzleBtn" disabled>
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button type="button" class="btn btn-primary btn-lg ms-2" id="nextPuzzleBtn"<?= $puzzleCount <= 1 ? ' style="display:none;"' : '' ?>>
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      <div>
        <button type="submit" class="btn btn-success btn-lg" id="submitBtn"<?= $puzzleCount > 1 ? ' style="display:none;"' : '' ?>>
          <i class="fas fa-check"></i> Submit answers
        </button>
        <a href="<?= site_url('student/crossword') ?>" class="btn btn-secondary btn-lg ms-2">Back</a>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  const total = <?= (int) $puzzleCount ?>;
  let current = <?= (int) $startIndex ?>;
  const panes = document.querySelectorAll('.puzzle-pane');
  const prevBtn = document.getElementById('prevPuzzleBtn');
  const nextBtn = document.getElementById('nextPuzzleBtn');
  const submitBtn = document.getElementById('submitBtn');
  const counter = document.getElementById('currentPuzzleNum');
  const form = document.getElementById('crosswordForm');

  function inputsForPuzzle(index) {
    return form.querySelectorAll('.puzzle-answer[data-puzzle="' + index + '"]');
  }

  function puzzleFilled(index) {
    const inputs = inputsForPuzzle(index);
    if (!inputs.length) return true;
    for (let i = 0; i < inputs.length; i++) {
      if (inputs[i].value.trim() === '') return false;
    }
    return true;
  }

  function showPuzzle(index) {
    current = Math.max(0, Math.min(total - 1, index));
    panes.forEach(function (pane) {
      pane.classList.toggle('active', parseInt(pane.dataset.puzzleIndex, 10) === current);
    });
    if (counter) counter.textContent = String(current + 1);
    if (prevBtn) prevBtn.disabled = current === 0;
    if (nextBtn) nextBtn.style.display = current >= total - 1 ? 'none' : '';
    if (submitBtn) submitBtn.style.display = current >= total - 1 ? '' : 'none';

    const url = new URL(window.location.href);
    url.searchParams.set('p', String(current));
    window.history.replaceState({}, '', url);
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', function () {
      showPuzzle(current - 1);
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      if (!puzzleFilled(current)) {
        alert('Please fill all answer boxes on this puzzle before continuing.');
        return;
      }
      showPuzzle(current + 1);
    });
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      for (let i = 0; i < total; i++) {
        if (!puzzleFilled(i)) {
          e.preventDefault();
          showPuzzle(i);
          alert('Please complete puzzle ' + (i + 1) + ' before submitting.');
          return;
        }
      }
    });
  }

  showPuzzle(current);
})();
</script>

<?= $this->endSection() ?>
