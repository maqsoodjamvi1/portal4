<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<style>
  .ws-play-layout { display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start; }
  .ws-grid-table { border-collapse: collapse; user-select: none; touch-action: none; }
  .ws-grid-table td { width: 36px; height: 36px; border: 1px solid #333; text-align: center; vertical-align: middle; font-weight: 700; font-size: 16px; cursor: pointer; }
  .ws-grid-table td.selecting { background: #dbeafe; }
  .ws-grid-table td.found { background: #bbf7d0; }
  .ws-word-list { min-width: 220px; }
  .ws-word-list li { margin-bottom: 8px; font-size: 15px; }
  .ws-word-list li.found-word { text-decoration: line-through; color: #16a34a; }
  .ws-word-list .clue { display: block; font-size: 12px; color: #666; font-weight: 400; }
  .puzzle-pane { display: none; }
  .puzzle-pane.active { display: block; }
</style>

<div class="container py-4">
  <h2><?= esc($title) ?></h2>
  <p class="text-muted">Click and drag on the grid to find words. They may be hidden horizontally, vertically, or diagonally.</p>
  <?php if (! empty($assignment['due_date'])): ?>
    <p class="text-muted"><strong>Due:</strong> <?= esc($assignment['due_date']) ?></p>
  <?php endif; ?>

  <form method="post" action="<?= site_url('student/word-search/submit') ?>" id="wordSearchForm">
    <?= csrf_field() ?>
    <input type="hidden" name="assignment_id" value="<?= (int) ($assignment['id'] ?? 0) ?>">

    <div class="mb-3 fw-bold">
      Puzzle <span id="currentPuzzleNum"><?= (int) $puzzleIndex + 1 ?></span> of <?= (int) $puzzleCount ?>
    </div>

    <?php foreach ($puzzles as $pi => $puzzle): ?>
      <?php
        $rows  = (int) ($puzzle['rows'] ?? 15);
        $grid  = $puzzle['grid'] ?? [];
        $words = $puzzle['words'] ?? [];
      ?>
      <div class="puzzle-pane<?= $pi === $puzzleIndex ? ' active' : '' ?>" data-puzzle-index="<?= (int) $pi ?>">
        <div class="ws-play-layout">
          <div>
            <table class="ws-grid-table" id="grid_<?= (int) $pi ?>" data-puzzle="<?= (int) $pi ?>">
              <?php for ($r = 0; $r < $rows; $r++): ?>
              <tr>
                <?php for ($c = 0; $c < $rows; $c++): ?>
                <td data-r="<?= $r ?>" data-c="<?= $c ?>"><?= esc($grid[$r][$c] ?? '') ?></td>
                <?php endfor; ?>
              </tr>
              <?php endfor; ?>
            </table>
          </div>
          <div class="ws-word-list">
            <h5>Find these words:</h5>
            <ul id="wordList_<?= (int) $pi ?>">
              <?php foreach ($words as $w): ?>
              <li data-word-id="<?= (int) ($w['id'] ?? 0) ?>" data-word="<?= esc($w['word'] ?? '') ?>">
                <strong><?= esc($w['word'] ?? '') ?></strong>
                <?php if (! empty($w['clue'])): ?><span class="clue"><?= esc($w['clue']) ?></span><?php endif; ?>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <?php foreach ($words as $w): ?>
          <input type="hidden" class="found-input" name="found[<?= (int) $pi ?>][]" value="" data-puzzle="<?= (int) $pi ?>" data-word-id="<?= (int) ($w['id'] ?? 0) ?>" disabled>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-2">
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
          <i class="fas fa-check"></i> Submit
        </button>
        <a href="<?= site_url('student/word-search') ?>" class="btn btn-secondary btn-lg ms-2">Back</a>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  const total = <?= (int) $puzzleCount ?>;
  let current = <?= (int) $puzzleIndex ?>;
  const panes = document.querySelectorAll('.puzzle-pane');
  const prevBtn = document.getElementById('prevPuzzleBtn');
  const nextBtn = document.getElementById('nextPuzzleBtn');
  const submitBtn = document.getElementById('submitBtn');
  const counter = document.getElementById('currentPuzzleNum');
  const form = document.getElementById('wordSearchForm');

  const foundByPuzzle = {};
  for (let i = 0; i < total; i++) foundByPuzzle[i] = new Set();

  function syncHiddenInputs(pi) {
    document.querySelectorAll('.found-input[data-puzzle="' + pi + '"]').forEach(inp => {
      const id = parseInt(inp.dataset.wordId, 10);
      const on = foundByPuzzle[pi].has(id);
      inp.disabled = !on;
      inp.value = on ? String(id) : '';
    });
  }

  function puzzleComplete(pi) {
    const pane = document.querySelector('.puzzle-pane[data-puzzle-index="' + pi + '"]');
    if (!pane) return true;
    const totalWords = pane.querySelectorAll('#wordList_' + pi + ' li').length;
    return foundByPuzzle[pi].size >= totalWords;
  }

  function showPuzzle(index) {
    current = Math.max(0, Math.min(total - 1, index));
    panes.forEach(p => p.classList.toggle('active', parseInt(p.dataset.puzzleIndex, 10) === current));
    if (counter) counter.textContent = String(current + 1);
    if (prevBtn) prevBtn.disabled = current === 0;
    if (nextBtn) nextBtn.style.display = current >= total - 1 ? 'none' : '';
    if (submitBtn) submitBtn.style.display = current >= total - 1 ? '' : 'none';
    const url = new URL(window.location.href);
    url.searchParams.set('p', String(current));
    window.history.replaceState({}, '', url);
  }

  function setupGrid(table) {
    const pi = parseInt(table.dataset.puzzle, 10);
    let dragging = false;
    let startCell = null;
    let selectedCells = [];

    function clearSelection() {
      table.querySelectorAll('td.selecting').forEach(td => td.classList.remove('selecting'));
      selectedCells = [];
    }

    function cellsInLine(r1, c1, r2, c2) {
      const dr = r2 - r1;
      const dc = c2 - c1;
      if (dr === 0 && dc === 0) return [[r1, c1]];
      const steps = Math.max(Math.abs(dr), Math.abs(dc));
      if (dr !== 0 && dc !== 0 && Math.abs(dr) !== Math.abs(dc)) return null;
      const stepR = dr === 0 ? 0 : dr / steps;
      const stepC = dc === 0 ? 0 : dc / steps;
      const cells = [];
      for (let i = 0; i <= steps; i++) {
        cells.push([r1 + stepR * i, c1 + stepC * i]);
      }
      return cells;
    }

    function getCell(r, c) {
      return table.querySelector('td[data-r="' + r + '"][data-c="' + c + '"]');
    }

    function highlightCells(cells) {
      clearSelection();
      cells.forEach(([r, c]) => {
        const td = getCell(r, c);
        if (td) { td.classList.add('selecting'); selectedCells.push([r, c]); }
      });
    }

    function lettersFromCells(cells) {
      return cells.map(([r, c]) => {
        const td = getCell(r, c);
        return td ? td.textContent.trim() : '';
      }).join('');
    }

    function tryMatchSelection(cells) {
      const forward = lettersFromCells(cells);
      const reverse = forward.split('').reverse().join('');
      const list = document.querySelectorAll('#wordList_' + pi + ' li');
      list.forEach(li => {
        const id = parseInt(li.dataset.wordId, 10);
        const word = (li.dataset.word || '').toUpperCase();
        if (foundByPuzzle[pi].has(id)) return;
        if (forward === word || reverse === word) {
          foundByPuzzle[pi].add(id);
          li.classList.add('found-word');
          cells.forEach(([r, c]) => {
            const td = getCell(r, c);
            if (td) td.classList.add('found');
          });
          syncHiddenInputs(pi);
        }
      });
    }

    table.addEventListener('mousedown', e => {
      const td = e.target.closest('td');
      if (!td) return;
      dragging = true;
      startCell = [parseInt(td.dataset.r, 10), parseInt(td.dataset.c, 10)];
      highlightCells([startCell]);
      e.preventDefault();
    });

    table.addEventListener('mouseover', e => {
      if (!dragging || !startCell) return;
      const td = e.target.closest('td');
      if (!td) return;
      const end = [parseInt(td.dataset.r, 10), parseInt(td.dataset.c, 10)];
      const line = cellsInLine(startCell[0], startCell[1], end[0], end[1]);
      if (line) highlightCells(line);
    });

    table.addEventListener('mouseup', () => {
      if (!dragging) return;
      dragging = false;
      if (selectedCells.length) tryMatchSelection(selectedCells);
      clearSelection();
      startCell = null;
    });

    table.addEventListener('mouseleave', () => {
      if (dragging) {
        dragging = false;
        if (selectedCells.length) tryMatchSelection(selectedCells);
        clearSelection();
        startCell = null;
      }
    });
  }

  document.querySelectorAll('.ws-grid-table').forEach(setupGrid);

  if (prevBtn) prevBtn.addEventListener('click', () => showPuzzle(current - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => {
    if (!puzzleComplete(current)) {
      alert('Find all words on this puzzle before continuing.');
      return;
    }
    showPuzzle(current + 1);
  });

  if (form) {
    form.addEventListener('submit', e => {
      for (let i = 0; i < total; i++) {
        if (!puzzleComplete(i)) {
          e.preventDefault();
          showPuzzle(i);
          alert('Find all words on puzzle ' + (i + 1) + ' before submitting.');
          return;
        }
      }
    });
  }

  showPuzzle(current);
})();
</script>

<?= $this->endSection() ?>
