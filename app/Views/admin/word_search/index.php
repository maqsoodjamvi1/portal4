<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Word Puzzle (Word Search)',
    'icon' => 'fas fa-search',
    'subtitle' => 'Generate vocabulary word search puzzles from Vocab Bank — print, assign, and student portal.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quizzes', 'url' => base_url('admin/quizzes')],
        ['label' => 'Word Puzzle', 'active' => true],
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
      <div class="alert alert-warning">Run migration <code>2026-06-12-120000_CreateWordSearchTables</code> to enable save, assign &amp; student portal.</div>
    <?php else: ?>
      <div class="alert alert-info mb-3">
        Generate → check <strong>Save to library</strong> →
        <a href="<?= site_url('admin/word-search/assign') ?>">Assign to class</a> →
        students play at <a href="<?= site_url('student/word-search') ?>" target="_blank">Student Portal → Word Puzzle</a>.
      </div>
    <?php endif; ?>

    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-search me-1"></i> Word search settings</h3>
        <div>
          <a href="<?= site_url('admin/word-search/library') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-folder-open"></i> Library</a>
          <a href="<?= site_url('admin/word-search/assign') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-users"></i> Assign</a>
        </div>
      </div>

      <form action="<?= site_url('admin/word-search/generate') ?>" method="post" target="_blank">
        <?= csrf_field() ?>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 form-group">
              <label for="worksheet_title">Worksheet title</label>
              <input type="text" name="worksheet_title" id="worksheet_title" class="form-control" value="<?= esc(old('worksheet_title', 'Vocabulary Word Search')) ?>">
            </div>
            <div class="col-md-2 form-group">
              <label for="grade">Grade <span class="text-danger">*</span></label>
              <select name="grade" id="grade" class="form-control" required>
                <?php foreach ($gradeOptions as $val => $label): ?>
                  <option value="<?= (int) $val ?>" <?= (string) old('grade', '1') === (string) $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 form-group">
              <label for="word_count">Words in puzzle <span class="text-danger">*</span></label>
              <input type="number" name="word_count" id="word_count" class="form-control" min="3" max="20" value="<?= esc(old('word_count', '10')) ?>" required>
            </div>
            <div class="col-md-2 form-group">
              <label for="grid_size">Grid size</label>
              <select name="grid_size" id="grid_size" class="form-control">
                <option value="0">Auto</option>
                <?php foreach ([12, 15, 18, 20] as $sz): ?>
                  <option value="<?= $sz ?>" <?= (string) old('grid_size') === (string) $sz ? 'selected' : '' ?>><?= $sz ?>×<?= $sz ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 form-group">
              <label for="direction_mode">Directions <span class="text-danger">*</span></label>
              <select name="direction_mode" id="direction_mode" class="form-control" required>
                <option value="hvd" <?= old('direction_mode', 'hvd') === 'hvd' ? 'selected' : '' ?>>H + V + Diagonal</option>
                <option value="hv" <?= old('direction_mode') === 'hv' ? 'selected' : '' ?>>Horizontal + Vertical only</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 form-group">
              <label for="vocab_class_id">Class (filter topics)</label>
              <select id="vocab_class_id" class="form-control">
                <option value="">All classes</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?= (int) $c['class_id'] ?>"><?= esc($c['class_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-9 form-group">
              <label>Vocab Bank topics</label>
              <select name="topic_ids[]" id="topic_ids" class="form-control" multiple size="5">
                <?php foreach ($vocabTopics as $t): ?>
                  <option value="<?= (int) $t['id'] ?>"><?= esc($t['topic_name'] ?? '') ?> (Class <?= (int) ($t['class_id'] ?? 0) ?>)</option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Hold Ctrl/Cmd to select multiple topics.</small>
            </div>
          </div>

          <div class="form-group">
            <label for="manual_words">Manual words (optional, one per line; optional clue after |)</label>
            <textarea name="manual_words" id="manual_words" class="form-control" rows="3" placeholder="APPLE|A red fruit&#10;BANANA"><?= esc(old('manual_words')) ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-4 form-group">
              <label for="bulk_cls_sec_id">Bulk: one unique puzzle per student</label>
              <select name="bulk_cls_sec_id" id="bulk_cls_sec_id" class="form-control">
                <option value="">— None —</option>
                <?php foreach ($classSections as $cs): ?>
                  <option value="<?= (int) $cs['cls_sec_id'] ?>"><?= esc($cs['class_name'] . ' - ' . $cs['section_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-8 form-group d-flex align-items-end flex-wrap gap-3">
              <div class="form-check form-check">
                <input type="checkbox" class="form-check-input" name="save_set" id="save_set" value="1">
                <label class="form-check-label" for="save_set">Save to library</label>
              </div>
              <div class="form-check form-check">
                <input type="checkbox" class="form-check-input" name="answer_key" id="answer_key" value="1" checked>
                <label class="form-check-label" for="answer_key">Include answer key page</label>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-magic"></i> Generate &amp; Print</button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
(function () {
  const classSel = document.getElementById('vocab_class_id');
  const topicSel = document.getElementById('topic_ids');
  const url = <?= json_encode(site_url('admin/word-search/vocab-topics')) ?>;

  function loadTopics() {
    const cid = classSel ? classSel.value : '';
    fetch(url + '?class_id=' + encodeURIComponent(cid))
      .then(r => r.json())
      .then(rows => {
        if (!topicSel) return;
        topicSel.innerHTML = '';
        (rows || []).forEach(t => {
          const opt = document.createElement('option');
          opt.value = t.id;
          opt.textContent = (t.topic_name || '') + ' (Class ' + (t.class_id || '') + ')';
          topicSel.appendChild(opt);
        });
      });
  }

  if (classSel) classSel.addEventListener('change', loadTopics);
})();
</script>

<?= $this->endSection() ?>
