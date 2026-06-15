<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Math Crossword Worksheets',
    'icon' => 'fas fa-th',
    'subtitle' => 'Math squares, missing operators, mini grids & vocabulary crosswords — printable on A4.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quizzes', 'url' => base_url('admin/quizzes')],
        ['label' => 'Math Crossword', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php $errors = session('errors') ?? []; ?>
    <?php if (! empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if (empty($tablesReady)): ?>
      <div class="alert alert-warning">
        <i class="fas fa-database"></i> Run database migration <code>2026-06-08-120000_CreateCrosswordTables</code> to enable save, assign &amp; student portal features.
      </div>
    <?php else: ?>
      <div class="alert alert-info mb-3">
        <strong>Assign to students:</strong> Generate → check <strong>Save to library</strong> →
        <a href="<?= site_url('admin/math-crossword/assign') ?>">Assign to class</a> →
        students solve at <a href="<?= site_url('student/crossword') ?>" target="_blank">Student Portal → Crossword</a>.
      </div>
    <?php endif; ?>

    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-th me-1"></i> Worksheet settings</h3>
        <div>
          <a href="<?= site_url('admin/math-crossword/library') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-folder-open"></i> Library</a>
          <a href="<?= site_url('admin/math-crossword/assign') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-users"></i> Assign</a>
        </div>
      </div>

      <form action="<?= site_url('admin/math-crossword/generate') ?>" method="post" target="_blank" id="mathCrosswordForm">
        <?= csrf_field() ?>

        <div class="card-body">
          <div class="row">
            <div class="col-md-4 form-group">
              <label for="puzzle_type">Puzzle type <span class="text-danger">*</span></label>
              <select name="puzzle_type" id="puzzle_type" class="form-control" required>
                <?php foreach ($puzzleTypeOptions as $val => $label): ?>
                  <option value="<?= esc($val) ?>" <?= old('puzzle_type', 'math_square') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4 form-group">
              <label for="grade">Grade <span class="text-danger">*</span></label>
              <select name="grade" id="grade" class="form-control" required>
                <option value="">— Select grade —</option>
                <?php foreach ($gradeOptions as $val => $label): ?>
                  <option value="<?= (int) $val ?>" <?= (string) old('grade') === (string) $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4 form-group">
              <label for="difficulty">Difficulty <span class="text-danger">*</span></label>
              <select name="difficulty" id="difficulty" class="form-control" required>
                <?php foreach ($difficultyOptions as $val => $label): ?>
                  <option value="<?= esc($val) ?>" <?= old('difficulty', 'medium') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div id="mathOptionsBlock">
            <div class="form-group">
              <label>Operations <span class="text-danger">*</span></label>
              <div class="d-flex flex-wrap" style="gap:1rem;">
                <?php
                  $oldOps = old('operations') ?? ['+', '-'];
                  if (! is_array($oldOps)) { $oldOps = ['+', '-']; }
                ?>
                <?php foreach ($operationOptions as $val => $label): ?>
                  <div class="form-check form-check">
                    <input type="checkbox" class="form-check-input" name="operations[]"
                           id="op_<?= esc(md5($val)) ?>" value="<?= esc($val) ?>"
                           <?= in_array($val, $oldOps, true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="op_<?= esc(md5($val)) ?>"><?= esc($label) ?></label>
                  </div>
                <?php endforeach; ?>
              </div>
              <small class="text-muted" id="gradeOpHint">Grades 1–2 usually use + and − only.</small>
            </div>
          </div>

          <div id="vocabOptionsBlock" class="d-none border rounded p-3 mb-3 bg-light">
            <h6 class="fw-bold">Vocabulary source (Vocab Bank)</h6>
            <div class="row">
              <div class="col-md-4 form-group">
                <label for="vocab_class_id">Class</label>
                <select name="vocab_class_id" id="vocab_class_id" class="form-control">
                  <option value="">— Any —</option>
                  <?php foreach ($classes as $cls): ?>
                    <option value="<?= (int) $cls['class_id'] ?>"><?= esc($cls['class_name'] ?? '') ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-8 form-group">
                <label>Topics <span class="text-danger">*</span></label>
                <select name="topic_ids[]" id="topic_ids" class="form-control" multiple size="5">
                  <?php foreach ($vocabTopics as $t): ?>
                    <option value="<?= (int) $t['id'] ?>"><?= esc($t['topic_name'] ?? '') ?></option>
                  <?php endforeach; ?>
                </select>
                <small class="text-muted">Hold Ctrl to select multiple topics.</small>
              </div>
            </div>
          </div>

          <div id="qbLinkBlock" class="form-group">
            <label for="qb_topic_id">Link Question Bank topic (optional)</label>
            <select name="qb_topic_id" id="qb_topic_id" class="form-control">
              <option value="">— None —</option>
              <?php foreach ($qbTopics as $qt): ?>
                <option value="<?= (int) $qt['id'] ?>"><?= esc($qt['topic_name'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">When set with vocab crossword, matching Vocab Bank topics are included automatically.</small>
          </div>

          <div class="row">
            <div class="col-md-3 form-group">
              <label for="puzzle_count">Puzzles to generate</label>
              <input type="number" name="puzzle_count" id="puzzle_count" class="form-control" min="1" max="10" value="<?= esc(old('puzzle_count', '4')) ?>" required>
            </div>
            <div class="col-md-3 form-group">
              <label for="per_page">Puzzles per A4 page</label>
              <select name="per_page" id="per_page" class="form-control" required>
                <option value="4" selected>4 per page (2×2)</option>
                <option value="2">2 per page</option>
                <option value="1">1 per page (largest)</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label for="worksheet_title">Worksheet title</label>
              <input type="text" name="worksheet_title" id="worksheet_title" class="form-control" maxlength="80" placeholder="Maths Puzzle Across–Down" value="<?= esc(old('worksheet_title', '')) ?>">
            </div>
            <div class="col-md-3 form-group">
              <label for="bulk_cls_sec_id">Bulk: one per student</label>
              <select name="bulk_cls_sec_id" id="bulk_cls_sec_id" class="form-control">
                <option value="">— Off —</option>
                <?php foreach ($classSections as $cs): ?>
                  <option value="<?= (int) $cs['cls_sec_id'] ?>"><?= esc($cs['class_name'] . ' - ' . $cs['section_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Unique puzzle + name per student.</small>
            </div>
          </div>

          <div class="form-check form-check mb-2">
            <input type="checkbox" class="form-check-input" name="answer_key" id="answer_key" value="1" <?= old('answer_key') ? 'checked' : '' ?>>
            <label class="form-check-label" for="answer_key">Include answer key on separate pages</label>
          </div>
          <?php if (! empty($tablesReady)): ?>
          <div class="form-check form-check mb-2">
            <input type="checkbox" class="form-check-input" name="save_set" id="save_set" value="1">
            <label class="form-check-label" for="save_set">Save to worksheet library after generating</label>
          </div>
          <?php endif; ?>
        </div>

        <div class="card-footer">
          <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Generate &amp; Print</button>
          <span class="text-muted small ms-2">Opens in a new tab — A4 portrait.</span>
        </div>
      </form>
    </div>

    <?php if (! empty($savedSets)): ?>
    <div class="card card-outline card-secondary">
      <div class="card-header"><h3 class="card-title">Recent saved worksheets</h3></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Title</th><th>Type</th><th>Grade</th><th>Date</th><th></th></tr></thead>
          <tbody>
            <?php foreach (array_slice($savedSets, 0, 5) as $set): ?>
            <tr>
              <td><?= esc($set['title'] ?? '') ?></td>
              <td><?= esc($set['puzzle_type'] ?? '') ?></td>
              <td><?= (int) ($set['grade'] ?? 0) ?></td>
              <td><?= esc($set['created_at'] ?? '') ?></td>
              <td><a href="<?= site_url('admin/math-crossword/reprint/' . (int) $set['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Re-print</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<script>
(function () {
  const typeSel = document.getElementById('puzzle_type');
  const mathBlock = document.getElementById('mathOptionsBlock');
  const vocabBlock = document.getElementById('vocabOptionsBlock');
  const gradeSel = document.getElementById('grade');
  const hint = document.getElementById('gradeOpHint');
  const opMul = document.getElementById('op_<?= esc(md5('×')) ?>');
  const opDiv = document.getElementById('op_<?= esc(md5('÷')) ?>');

  function syncTypePanels() {
    const isVocab = typeSel.value === 'vocab';
    mathBlock.classList.toggle('d-none', isVocab);
    vocabBlock.classList.toggle('d-none', !isVocab);
    if (typeSel.value === 'mini_5x5') {
      document.getElementById('per_page').value = '4';
    }
  }

  function syncGradeHints() {
    const g = parseInt(gradeSel.value, 10) || 0;
    if (g <= 2) {
      hint.textContent = 'Grades 1–2: addition and subtraction are recommended.';
      if (opMul) opMul.checked = false;
      if (opDiv) opDiv.checked = false;
    } else if (g === 3) {
      hint.textContent = 'Grade 3: +, −, and × are typical.';
      if (opDiv) opDiv.checked = false;
    } else {
      hint.textContent = 'Grades 4–5: all four operations are available.';
    }
  }

  typeSel.addEventListener('change', syncTypePanels);
  gradeSel.addEventListener('change', syncGradeHints);
  syncTypePanels();
  syncGradeHints();

  document.getElementById('mathCrosswordForm').addEventListener('submit', function (e) {
    if (typeSel.value === 'vocab') {
      const topics = document.getElementById('topic_ids').selectedOptions;
      if (!topics.length) {
        e.preventDefault();
        alert('Select at least one Vocab Bank topic.');
      }
      return;
    }
    const checked = this.querySelectorAll('input[name="operations[]"]:checked');
    if (!checked.length) {
      e.preventDefault();
      alert('Please select at least one operation.');
    }
  });
})();
</script>

<?= $this->endSection() ?>
