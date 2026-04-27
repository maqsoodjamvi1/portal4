<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // Defaults for start/end
  $oldStart = old('start_at');
  $oldEnd   = old('end_at');

  $defaultStartAt = $oldStart ?: date('Y-m-d\TH:i');
  $defaultEndAt   = $oldEnd   ?: date('Y-m-d\TH:i');
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1 class="mb-0">Create Quiz</h1>
        <small class="text-muted">
          Configure quiz window, behaviour and select questions from Question Bank.
        </small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/quizzes') ?>">Quizzes</a></li>
          <li class="breadcrumb-item active">Create</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="card card-outline card-primary shadow-sm">
    <form action="<?= base_url('admin/quizzes/store') ?>" method="post" id="quizCreateForm">
      <?= csrf_field() ?>

      <!-- Hidden campus & session -->
      <input type="hidden" name="campus_id"  value="<?= (int)($campusId ?? 0) ?>">
      <input type="hidden" name="session_id" value="<?= (int)($sessionId ?? 0) ?>">

      <div class="card-body">

        <!-- =========================
             BLOCK 1: Term / Class / Subject
             ========================= -->
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-light py-2">
            <h3 class="card-title mb-0">Target Class & Subject</h3>
          </div>
          <div class="card-body pb-2">
            <div class="form-row">
              <!-- Term -->
              <div class="form-group col-lg-4 col-md-6 col-sm-12">
                <label for="term_session_id">Term <span class="text-danger">*</span></label>
                <select class="form-control" id="term_session_id" name="term_session_id" required>
                  <option value="">-- Select Term --</option>
                  <?php foreach (($terms ?? []) as $t):
                    $tsid  = (int)($t->term_session_id ?? 0);
                    $tname = $t->term_name ?? ('Term ' . (int)($t->term_id ?? 0));
                    $sel   = (old('term_session_id') == $tsid) ? 'selected' : '';
                  ?>
                    <option value="<?= $tsid ?>" <?= $sel ?>><?= esc($tname) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (isset($validation) && $validation->hasError('term_session_id')): ?>
                  <small class="text-danger"><?= $validation->getError('term_session_id') ?></small>
                <?php endif; ?>
              </div>

              <!-- Class Section -->
              <div class="form-group col-lg-4 col-md-6 col-sm-12">
                <label for="cls_sec_id">Class Section <span class="text-danger">*</span></label>
                <select name="cls_sec_id" id="cls_sec_id" class="form-control" required>
                  <option value="">-- Select Class Section --</option>
                  <?php foreach (($classSections ?? []) as $cs):
                    $id    = (int)($cs['cls_sec_id'] ?? 0);
                    $label = $cs['label'] ?? ('ClassSection ' . $id);
                    $sel   = (old('cls_sec_id') == $id) ? 'selected' : '';
                  ?>
                    <option value="<?= $id ?>" <?= $sel ?>><?= esc($label) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (isset($validation) && $validation->hasError('cls_sec_id')): ?>
                  <small class="text-danger"><?= $validation->getError('cls_sec_id') ?></small>
                <?php endif; ?>
              </div>

              <!-- Subject (sec_sub_id) -->
              <div class="form-group col-lg-4 col-md-6 col-sm-12">
                <label for="subject_id">Subject <span class="text-danger">*</span></label>
                <select class="form-control" id="subject_id" name="subject_id" required>
                  <option value="">-- Select Subject --</option>
                  <?php if (old('subject_id')): ?>
                    <option value="<?= (int)old('subject_id') ?>" selected>Previously selected</option>
                  <?php endif; ?>
                </select>
                <?php if (isset($validation) && $validation->hasError('subject_id')): ?>
                  <small class="text-danger"><?= $validation->getError('subject_id') ?></small>
                <?php endif; ?>
              </div>
            </div>

            <!-- Topics row (moved up) -->
            <div id="topicFilters"
                 class="border rounded px-3 py-2 mt-2 d-none"
                 style="background:#f9fafb;">
              <!-- filled by JS -->
            </div>
          </div>
        </div>

        <!-- =========================
             BLOCK 2: Existing Quizzes
             ========================= -->
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-light py-2">
            <h3 class="card-title mb-0">Existing Quizzes for Selected Term / Class / Subject</h3>
          </div>
          <div class="card-body" id="existingQuizzesWrap">
            <p class="text-muted mb-0">
              Select Term, Class Section, and Subject to see already created quizzes.
            </p>
          </div>
        </div>

        <!-- =========================
             BLOCK 3: Quiz Settings (full width)
             ========================= -->
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-light py-2">
            <h3 class="card-title mb-0">Quiz Settings</h3>
          </div>
          <div class="card-body">

            <!-- Row 1: Title + Start + End -->
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="title">Quiz Title <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control"
                       id="title"
                       name="title"
                       value="<?= esc(old('title')) ?>"
                       placeholder="e.g. English Grammar Quiz - Grade 7"
                       required>
                <?php if (isset($validation) && $validation->hasError('title')): ?>
                  <small class="text-danger"><?= $validation->getError('title') ?></small>
                <?php endif; ?>
              </div>

              <div class="form-group col-md-4">
                <label for="start_at">Start At</label>
                <input type="datetime-local"
                       class="form-control"
                       id="start_at"
                       name="start_at"
                       value="<?= esc($defaultStartAt) ?>">
              </div>

              <div class="form-group col-md-4">
                <label for="end_at">End At</label>
                <input type="datetime-local"
                       class="form-control"
                       id="end_at"
                       name="end_at"
                       value="<?= esc($defaultEndAt) ?>">
                <small id="quizDurationText" class="form-text text-muted">Duration: --</small>
              </div>
            </div>

            <!-- Row 2: Time / Attempts / Marks / Neg / Total Q -->
            <div class="form-row">
              <div class="form-group col-md-2">
                <label for="time_limit_min">Time (min)</label>
                <input type="number"
                       class="form-control text-center"
                       id="time_limit_min"
                       name="time_limit_min"
                       value="<?= esc(old('time_limit_min', '0')) ?>"
                       min="0"
                       step="1">
              </div>

              <div class="form-group col-md-2">
                <label for="max_attempts">Attempts</label>
                <input type="number"
                       class="form-control text-center"
                       id="max_attempts"
                       name="max_attempts"
                       value="<?= esc(old('max_attempts', 1)) ?>"
                       min="1"
                       step="1">
              </div>

              <div class="form-group col-md-2">
                <label for="per_question_marks">Marks / Q</label>
                <input type="number"
                       class="form-control text-center"
                       id="per_question_marks"
                       name="per_question_marks"
                       value="<?= esc(old('per_question_marks', '1')) ?>"
                       min="0"
                       step="0.5">
              </div>

              <div class="form-group col-md-3">
                <label for="negative_mark_per_q">Negative / Q</label>
                <input type="number"
                       class="form-control text-center"
                       id="negative_mark_per_q"
                       name="negative_mark_per_q"
                       value="<?= esc(old('negative_mark_per_q', '0')) ?>"
                       min="0"
                       step="0.25">
              </div>

              <div class="form-group col-md-3">
                <label for="questions_count">Total Questions</label>
                <input type="number"
                       class="form-control text-center font-weight-bold"
                       id="questions_count"
                       name="questions_count"
                       value="<?= esc(old('questions_count', '0')) ?>"
                       readonly>
              </div>
            </div>

            <!-- Row 3: Per-type Question Counts -->
            <div class="form-row border rounded p-3 bg-light">
              <div class="form-group col-md-2">
                <label for="count_mcq_single">MCQ Single</label>
                <input type="number"
                       id="count_mcq_single"
                       name="count_mcq_single"
                       class="form-control qb-type-count text-center"
                       value="<?= esc(old('count_mcq_single', '0')) ?>"
                       min="0"
                       step="1">
              </div>

              <div class="form-group col-md-2">
                <label for="count_mcq_multi">MCQ Multiple</label>
                <input type="number"
                       id="count_mcq_multi"
                       name="count_mcq_multi"
                       class="form-control qb-type-count text-center"
                       value="<?= esc(old('count_mcq_multi', '0')) ?>"
                       min="0"
                       step="1">
              </div>

              <div class="form-group col-md-2">
                <label for="count_tf">True / False</label>
                <input type="number"
                       id="count_tf"
                       name="count_tf"
                       class="form-control qb-type-count text-center"
                       value="<?= esc(old('count_tf', '0')) ?>"
                       min="0"
                       step="1">
              </div>

              <div class="form-group col-md-2">
                <label for="count_fill">Fill Blanks</label>
                <input type="number"
                       id="count_fill"
                       name="count_fill"
                       class="form-control qb-type-count text-center"
                       value="<?= esc(old('count_fill', '0')) ?>"
                       min="0"
                       step="1">
              </div>

              <div class="form-group col-md-2">
                <label for="count_short">Short Answer</label>
                <input type="number"
                       id="count_short"
                       name="count_short"
                       class="form-control qb-type-count text-center"
                       value="<?= esc(old('count_short', '0')) ?>"
                       min="0"
                       step="1">
              </div>

              <div class="form-group col-md-2">
                <label for="count_match">Match</label>
                <input type="number"
                       id="count_match"
                       name="count_match"
                       class="form-control qb-type-count text-center"
                       value="<?= esc(old('count_match', '0')) ?>"
                       min="0"
                       step="1">
              </div>
            </div>

          </div>
        </div>

        <!-- =========================
             BLOCK 4: Behaviour Toggles (full width)
             ========================= -->
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-light py-2">
            <h3 class="card-title mb-0">Quiz Behaviour Settings</h3>
          </div>
          <div class="card-body">
            <div class="form-row">
              <div class="form-group col-md-3">
                <label class="d-block">Shuffle Questions</label>
                <div class="custom-control custom-switch">
                  <input type="checkbox"
                         class="custom-control-input"
                         id="shuffle_questions"
                         name="shuffle_questions"
                         value="1"
                         <?= old('shuffle_questions', '1') ? 'checked' : '' ?>>
                  <label class="custom-control-label" for="shuffle_questions">Enable</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">Shuffle Options</label>
                <div class="custom-control custom-switch">
                  <input type="checkbox"
                         class="custom-control-input"
                         id="shuffle_options"
                         name="shuffle_options"
                         value="1"
                         <?= old('shuffle_options', '1') ? 'checked' : '' ?>>
                  <label class="custom-control-label" for="shuffle_options">Enable</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">Show Solution</label>
                <div class="custom-control custom-switch">
                  <input type="checkbox"
                         class="custom-control-input"
                         id="show_solution"
                         name="show_solution"
                         value="1"
                         <?= old('show_solution', '1') ? 'checked' : '' ?>>
                  <label class="custom-control-label" for="show_solution">After submit</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">WiFi Only</label>
                <div class="custom-control custom-switch">
                  <input type="checkbox"
                         class="custom-control-input"
                         id="wifi_only"
                         name="wifi_only"
                         value="1"
                         <?= old('wifi_only') ? 'checked' : '' ?>>
                  <label class="custom-control-label" for="wifi_only">Restrict</label>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-3">
                <label class="d-block">Urdu Quiz?</label>
                <div class="custom-control custom-switch">
                  <input type="checkbox"
                         class="custom-control-input"
                         id="is_urdu"
                         name="is_urdu"
                         value="1"
                         <?= old('is_urdu') ? 'checked' : '' ?>>
                  <label class="custom-control-label" for="is_urdu">Store in is_urdu (0/1)</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">Order by Question Type</label>
                <div class="custom-control custom-switch">
                  <input type="checkbox"
                         class="custom-control-input"
                         id="is_order_by_qtype"
                         name="is_order_by_qtype"
                         value="1"
                         <?= old('is_order_by_qtype') ? 'checked' : '' ?>>
                  <label class="custom-control-label" for="is_order_by_qtype">Group by type</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">Publish</label>
                <div class="custom-control custom-switch">
                  <input type="checkbox"
                         class="custom-control-input"
                         id="is_published"
                         name="is_published"
                         value="1"
                         <?= old('is_published', '1') ? 'checked' : '' ?>>
                  <label class="custom-control-label" for="is_published">Visible to students</label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- =========================
             BLOCK 5: Instructions (full width)
             ========================= -->
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-light py-2">
            <h3 class="card-title mb-0">Instructions (Optional)</h3>
          </div>
          <div class="card-body">
            <div class="form-group mb-0">
              <label for="instructions" class="mb-1">
                Instructions shown to students before starting the quiz
              </label>
              <textarea class="form-control"
                        id="instructions"
                        name="instructions"
                        rows="4"
                        placeholder="Write clear guidelines, time rules, allowed materials, etc."><?= esc(old('instructions')) ?></textarea>
            </div>
          </div>
        </div>

        <!-- =========================
             BLOCK 6: Question Bank
             ========================= -->
     <!-- =========================
     BLOCK 6: Question Bank (Nested Cards)
     ========================= -->
<div class="card mt-3">
  <div class="card-header d-flex justify-content-between align-items-center py-2">
    <div>
      <h3 class="card-title mb-0">Question Bank</h3>
      <small class="text-muted d-block">
        Browse: Class → Subject → Topic. Click Topic card(s) to load questions.
      </small>
    </div>
    <div>
      <button type="button" class="btn btn-xs btn-outline-primary" id="qbSelectAll">
        Select All Questions
      </button>
      <button type="button" class="btn btn-xs btn-outline-secondary ml-1" id="qbClearAll">
        Unselect All Questions
      </button>
    </div>
  </div>

  <div class="card-body">

    <!-- NESTED CARDS CONTAINER -->
    <div id="qbNestedWrap" class="mb-2">
      <!-- filled by JS -->
    </div>

    <div id="qbEmptyHint" class="p-3 text-muted border rounded">
      Loading Question Bank...
    </div>

    <!-- QUESTIONS TABLE -->
    <div class="table-responsive d-none mt-3" id="qbTableWrap">
      <table class="table table-sm table-striped mb-0" id="qbTable">
        <thead>
          <tr>
            <th style="width:40px;">
              <input type="checkbox" id="qbCheckMaster">
            </th>
            <th style="width:40px;">#</th>
            <th style="width:80px;">Q.ID</th>
            <th style="width:110px;">Class</th>
            <th style="width:130px;">Subject</th>
            <th style="width:150px;">Topic</th>
            <th style="width:80px;">Type</th>
            <th>Question</th>
            <th style="width:90px;">Difficulty</th>
            <th style="width:150px;">Created At</th>
          </tr>
        </thead>
        <tbody><!-- filled by JS --></tbody>
      </table>
    </div>

  </div>
</div>

    <div id="qbEmptyHint" class="p-3 text-muted border rounded">
      Select Question Classes / Subjects to load questions from the question bank.
    </div>

      <!-- SUMMARY TABLE -->
    <div id="qbSummaryWrap" class="mb-2 d-none">
      <table class="table table-sm table-bordered mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width:120px;">Class</th>
            <th style="width:150px;">Subject</th>
            <th>Topic</th>
            <th style="width:80px;" class="text-right">Questions</th>
          </tr>
        </thead>
        <tbody id="qbSummaryBody">
          <!-- filled by JS -->
        </tbody>
      </table>
    </div>

    <div class="table-responsive d-none" id="qbTableWrap">
     <table class="table table-sm table-striped mb-0" id="qbTable">
  <thead>
    <tr>
      <th style="width:40px;">
        <input type="checkbox" id="qbCheckMaster">
      </th>
      <th style="width:40px;">#</th>
      <th style="width:80px;">Q.ID</th>
      <th style="width:110px;">Class</th>
      <th style="width:130px;">Subject</th>
      <th style="width:150px;">Topic</th>
      <th style="width:80px;">Type</th>
      <th>Question</th>
      <th style="width:90px;">Difficulty</th>
      <th style="width:150px;">Created At</th>
    </tr>
  </thead>
        <tbody>
          <!-- filled by JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>

      </div><!-- /.card-body -->

      <div class="card-footer d-flex justify-content-between">
        <a href="<?= base_url('admin/quizzes') ?>" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save mr-1"></i> Save Quiz
        </button>
      </div>
    </form>
  </div>
</section>

<!-- ===== Dynamic Subject Loader (cls_sec_id -> section-subjects -> sec_sub_id) ===== -->
<script>
(function () {
  const clsSecSel = document.getElementById('cls_sec_id');
  const subjSel   = document.getElementById('subject_id');

  function clearSubjects(label) {
    if (!subjSel) return;
    subjSel.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = label || '-- Select Subject --';
    subjSel.appendChild(opt);
  }

  function setLoading(on) {
    if (!subjSel) return;
    subjSel.disabled = !!on;
    if (on) clearSubjects('Loading...');
  }

  function loadSubjects(clsSecId, preselect = '<?= (int)old('subject_id') ?>') {
    if (!subjSel) return;

    if (!clsSecId) {
      clearSubjects('-- Select Subject --');
      return;
    }
    setLoading(true);

    fetch('<?= base_url('admin/quizzes/ajax/section-subjects') ?>/' + encodeURIComponent(clsSecId), {
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
      .then(r => r.json())
      .then(j => {
        clearSubjects('-- Select Subject --');

        if (j && j.ok && Array.isArray(j.data) && j.data.length) {
          j.data.forEach(row => {
            const opt = document.createElement('option');

            const value = (row.sec_sub_id !== undefined && row.sec_sub_id !== null)
              ? row.sec_sub_id
              : row.subject_id;

            const label = row.subject_name
              || row.name
              || row.subject_short_name
              || ('Subject ' + value);

            opt.value = value;
            opt.textContent = label;

            if (String(preselect) === String(value)) {
              opt.selected = true;
            }

            subjSel.appendChild(opt);
          });

          if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            jQuery(subjSel).trigger('change.select2');
          }
        } else {
          clearSubjects('No subjects found');
        }
      })
      .catch(() => clearSubjects('Error loading subjects'))
      .finally(() => setLoading(false));
  }

  if (clsSecSel) {
    clsSecSel.addEventListener('change', function () {
      loadSubjects(this.value, '');
    });
  }

  if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
    jQuery(function () {
      jQuery('#cls_sec_id').on('select2:select', function () {
        const v = jQuery(this).val();
        loadSubjects(v, '');
      });
    });
  }

  <?php if (old('cls_sec_id')): ?>
  loadSubjects('<?= (int)old('cls_sec_id') ?>');
  <?php endif; ?>
})();
</script>

<!-- =====================
     QB TOPICS + QUESTIONS LOADER
     ===================== -->
<!-- =====================
     QB TOPICS + QUESTIONS LOADER
     ===================== -->
<!-- =====================
     QB GROUP CARDS + QUESTIONS LOADER
     ===================== -->
<!-- =====================
     QB SUMMARY CARDS + QUESTIONS LOADER
     ===================== -->
<!-- =====================
     QB SUMMARY CARDS + QUESTIONS LOADER
     ===================== -->
<script>
jQuery(function ($) {

  console.log('QB loader (nested cards) JS initialized');

  const baseUrl       = '<?= base_url() ?>/';
  const $qbTableWrap  = $('#qbTableWrap');
  const $qbEmptyHint  = $('#qbEmptyHint');
  const $qbBody       = $('#qbTable tbody');
  const $qbMaster     = $('#qbCheckMaster');
  const $qbNestedWrap = $('#qbNestedWrap');

  // key format: class_id|subject_id|topic_id
  let selectedKeys = new Set();
  let summaryRows  = [];

  function keyOf(row) {
    return row.class_id + '|' + row.subject_id + '|' + row.topic_id;
  }

  function escHtml(s) {
    return $('<div>').text(s ?? '').html();
  }

  function getSelectedTermName() {
    const $termSel = $('#term_session_id');
    const txt = $termSel.find('option:selected').text();
    return (txt && txt.trim() && txt.indexOf('--') === -1) ? txt.trim() : 'Term';
  }

  function resetQuestionBank(msg) {
    $qbBody.empty();
    $qbTableWrap.addClass('d-none');
    $qbEmptyHint.text(msg || 'No questions loaded.').removeClass('d-none');
    $qbMaster.prop('checked', false);
  }

  // ---------- BUILD NESTED MAP ----------
  function buildNested(rows) {
    const map = {};
    (rows || []).forEach(r => {
      const cId = String(r.class_id);
      const sId = String(r.subject_id);
      const tId = String(r.topic_id);

      if (!map[cId]) {
        map[cId] = {
          class_id: r.class_id,
          class_name: r.class_name || ('Class #' + r.class_id),
          subjects: {}
        };
      }
      if (!map[cId].subjects[sId]) {
        map[cId].subjects[sId] = {
          subject_id: r.subject_id,
          subject_name: r.subject_name || ('Subject #' + r.subject_id),
          topics: []
        };
      }

      map[cId].subjects[sId].topics.push({
        class_id: r.class_id,
        subject_id: r.subject_id,
        topic_id: r.topic_id,
        topic_name: r.topic_name || ('Topic #' + r.topic_id),
        question_count: parseInt(r.question_count || 0, 10)
      });
    });

    // convert to arrays + sort
    const classes = Object.values(map);
    classes.sort((a,b) => String(a.class_name).localeCompare(String(b.class_name)));

    classes.forEach(c => {
      c.subjects = Object.values(c.subjects);
      c.subjects.sort((a,b) => String(a.subject_name).localeCompare(String(b.subject_name)));
      c.subjects.forEach(s => {
  s.topics.sort((a, b) => {
    return parseInt(a.topic_id, 10) - parseInt(b.topic_id, 10);
  });
});
    });

    return classes;
  }

  // ---------- RENDER NESTED CARDS ----------
  function renderNestedCards(rows) {
    summaryRows = rows || [];

    if (!summaryRows.length) {
      $qbNestedWrap.html(
        '<div class="alert alert-warning mb-0">No Question Bank records found.</div>'
      );
      resetQuestionBank('No questions available.');
      return;
    }

    const termName = getSelectedTermName();
    const nested = buildNested(summaryRows);

    let html = '';
    nested.forEach((cls, ci) => {
      const clsId = 'qbClass_' + cls.class_id;

      html += `
        <div class="card qb-class-card mb-2">
          <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <button type="button" class="btn btn-xs btn-light mr-2 qb-toggle"
                      data-toggle="collapse" data-target="#${clsId}"
                      aria-expanded="true" aria-controls="${clsId}">
                <i class="fas fa-chevron-right"></i>
              </button>
              <div>
                <div class="font-weight-bold">${escHtml(cls.class_name)}</div>
                <small class="text-muted">Class Card</small>
              </div>
            </div>
          </div>

          <div id="${clsId}" class="collapse">
            <div class="card-body py-2">
      `;

      cls.subjects.forEach((subj, si) => {
        const subjId = 'qbSubj_' + cls.class_id + '_' + subj.subject_id;

        html += `
          <div class="card qb-subject-card mb-2">
            <div class="card-header py-2 d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <button type="button" class="btn btn-xs btn-light mr-2 qb-toggle"
                        data-toggle="collapse" data-target="#${subjId}"
                        aria-expanded="true" aria-controls="${subjId}">
                  <i class="fas fa-chevron-right"></i>
                </button>
                <div class="font-weight-bold">${escHtml(subj.subject_name)}</div>
              </div>
              <span class="badge badge-light">Subject Card</span>
            </div>

            <div id="${subjId}" class="collapse">
              <div class="card-body py-2">
                <div class="row">
        `;

        subj.topics.forEach((t) => {
          const k = t.class_id + '|' + t.subject_id + '|' + t.topic_id;
          const active = selectedKeys.has(k) ? ' active' : '';

          html += `
            <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-2">
              <div class="qb-topic-card border rounded p-2 h-100${active}"
                   data-key="${escHtml(k)}"
                   data-class-id="${t.class_id}"
                   data-subject-id="${t.subject_id}"
                   data-topic-id="${t.topic_id}">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="qb-topic-title font-weight-bold text-truncate"
                       title="${escHtml(t.topic_name)}">${escHtml(t.topic_name)}</div>
                  <span class="badge badge-info ml-2">${t.question_count} Qs</span>
                </div>

                <div class="mt-1 d-flex justify-content-between align-items-center">
                  <small class="text-muted">
                    <i class="far fa-calendar-alt mr-1"></i>${escHtml(termName)}
                  </small>
                  <small class="text-muted">Topic Card</small>
                </div>
              </div>
            </div>
          `;
        });

        html += `
                </div>
              </div>
            </div>
          </div>
        `;
      });

      html += `
            </div>
          </div>
        </div>
      `;
    });

    $qbNestedWrap.html(html);

    // =======================
// ACCORDION RULES
// 1) Only one CLASS open at a time
// 2) Only one SUBJECT open at a time (within the opened class)
// Default: all closed (we removed "show" above)
// =======================

// When opening a class: close other classes + close any opened subjects everywhere
$qbNestedWrap.off('show.bs.collapse', '.qb-class-card > .collapse')
  .on('show.bs.collapse', '.qb-class-card > .collapse', function () {

    // close other classes
    $qbNestedWrap.find('.qb-class-card > .collapse.show').not(this).collapse('hide');

    // close all subject collapses (clean state)
    $qbNestedWrap.find('.qb-subject-card .collapse.show').collapse('hide');
  });

// When opening a subject: close other subjects within the SAME class
$qbNestedWrap.off('show.bs.collapse', '.qb-subject-card .collapse')
  .on('show.bs.collapse', '.qb-subject-card .collapse', function () {
    const $classBody = $(this).closest('.qb-class-card').find('> .collapse');
    // close other subjects in this class only
    $classBody.find('.qb-subject-card .collapse.show').not(this).collapse('hide');
  });

    $qbEmptyHint.text('Click Topic card(s) to load questions.');

    // Topic click → toggle selection
    $qbNestedWrap.off('click', '.qb-topic-card').on('click', '.qb-topic-card', function () {
      const $card = $(this);
      const key   = String($card.data('key'));

      if (selectedKeys.has(key)) {
        selectedKeys.delete(key);
        $card.removeClass('active');
      } else {
        selectedKeys.add(key);
        $card.addClass('active');
      }

      if (selectedKeys.size === 0) {
        resetQuestionBank('Select a topic card to load questions.');
      } else {
        reloadQuestionsFromSelected();
      }
    });

    // Toggle icon direction on collapse show/hide
    $qbNestedWrap.off('shown.bs.collapse hidden.bs.collapse', '.collapse')
      .on('shown.bs.collapse', '.collapse', function () {
        $(this).prev('.card-header').find('.qb-toggle i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
      })
      .on('hidden.bs.collapse', '.collapse', function () {
        $(this).prev('.card-header').find('.qb-toggle i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
      });
  }

  // ---------- RENDER QUESTIONS ----------
  function renderQuestions(rows) {
    if (!rows || !rows.length) {
      resetQuestionBank('No questions found for selected topics.');
      return;
    }

    const typeLabels = {
      mcq:           'MCQ',
      mcq_single:    'MCQ',
      mcq_multi:     'MCQ Multi',
      tf:            'True/False',
      true_false:    'True/False',
      short:         'Short Answer',
      short_answer:  'Short Answer',
      fill:          'Fill in the Blank',
      fill_blank:    'Fill in the Blank',
      match:         'Matching'
    };

    $qbBody.empty();

    rows.forEach(function (q, idx) {
      const typeKey   = (q.question_type || '').toLowerCase();
      const typeLabel = typeLabels[typeKey] || (q.question_type || '-');

      const classLabel   = q.class_name   || (q.class_id   ? ('Class #'   + q.class_id)   : '-');
      const subjectLabel = q.subject_name || (q.subject_id ? ('Subject #' + q.subject_id) : '-');
      const topicLabel   = q.topic_name   || (q.topic_id   ? ('Topic #'   + q.topic_id)   : '-');

      let questionText = q.question || '';
      if (questionText.length > 180) questionText = questionText.substr(0, 180) + '…';

      const difficulty = q.difficulty   || '-';
      const createdAt  = q.created_date || '';

      $qbBody.append(`
        <tr>
          <td><input type="checkbox" class="qb-check" name="question_ids[]" value="${q.id}"></td>
          <td>${idx + 1}</td>
          <td>${q.id}</td>
          <td>${escHtml(classLabel)}</td>
          <td>${escHtml(subjectLabel)}</td>
          <td>${escHtml(topicLabel)}</td>
          <td><span class="badge badge-info">${escHtml(typeLabel)}</span></td>
          <td>${escHtml(questionText)}</td>
          <td><span class="badge badge-light">${escHtml(difficulty)}</span></td>
          <td><small>${escHtml(createdAt)}</small></td>
        </tr>
      `);
    });

    $qbEmptyHint.addClass('d-none');
    $qbTableWrap.removeClass('d-none');
    $qbMaster.prop('checked', false);
  }

  // ---------- LOAD QUESTIONS BASED ON SELECTED TOPICS + TYPE COUNTS ----------
  function reloadQuestionsFromSelected() {
    if (!selectedKeys.size) {
      resetQuestionBank('Select a topic card to load questions.');
      return;
    }

    const classIds   = [];
    const subjectIds = [];
    const topicIds   = [];

    selectedKeys.forEach(function (key) {
      const parts = String(key).split('|');
      classIds.push(parts[0]);
      subjectIds.push(parts[1]);
      topicIds.push(parts[2]);
    });

    const mcqSingle = parseInt($('#count_mcq_single').val() || '0', 10);
    const mcqMulti  = parseInt($('#count_mcq_multi').val()  || '0', 10);
    const tf        = parseInt($('#count_tf').val()         || '0', 10);
    const fill      = parseInt($('#count_fill').val()       || '0', 10);
    const shortAns  = parseInt($('#count_short').val()      || '0', 10);
    const matchQ    = parseInt($('#count_match').val()      || '0', 10);

    const questionTypesSet = new Set();
    if (mcqSingle > 0) { questionTypesSet.add('mcq'); questionTypesSet.add('mcq_single'); }
    if (mcqMulti  > 0) { questionTypesSet.add('mcq_multi'); }
    if (tf        > 0) { questionTypesSet.add('tf'); questionTypesSet.add('true_false'); }
    if (fill      > 0) { questionTypesSet.add('fill'); questionTypesSet.add('fill_blank'); }
    if (shortAns  > 0) { questionTypesSet.add('short'); questionTypesSet.add('short_answer'); }
    if (matchQ    > 0) { questionTypesSet.add('match'); }

    const questionTypes = Array.from(questionTypesSet);

    if (!questionTypes.length) {
      resetQuestionBank('Set question type counts (MCQ / TF / Fill / Short / Match) before loading questions.');
      return;
    }

    $qbBody.html('<tr><td colspan="10" class="text-center text-muted">Loading questions...</td></tr>');
    $qbTableWrap.removeClass('d-none');
    $qbEmptyHint.addClass('d-none');
    $qbMaster.prop('checked', false);

    $.ajax({
      url     : baseUrl + 'admin/quizzes/ajax/qb-questions',
      method  : 'POST',
      dataType: 'json',
      data    : {
        class_ids      : classIds,
        subject_ids    : subjectIds,
        topic_ids      : topicIds,
        question_types : questionTypes
      },
      success : function (res) {
        if (!res || !res.ok) {
          resetQuestionBank((res && res.msg) || 'Error loading questions.');
          return;
        }
        renderQuestions(res.data || []);
      },
      error   : function (xhr, status, error) {
        console.error('QB questions ERROR:', status, error, xhr.responseText);
        resetQuestionBank('Error loading questions (network or server).');
      }
    });
  }

  window.qbReloadFromTypeFilters = reloadQuestionsFromSelected;

  // ---------- LOAD SUMMARY ----------
  function loadSummary() {
    $qbEmptyHint.text('Loading Question Bank...').removeClass('d-none');

    $.getJSON(baseUrl + 'admin/quizzes/ajax/qb-summary')
      .done(function (res) {
        if (!res || !res.ok) {
          $qbEmptyHint.text((res && res.msg) || 'Error loading Question Bank.').removeClass('d-none');
          return;
        }
        renderNestedCards(res.data || []);
      })
      .fail(function (xhr, status, error) {
        console.error('qb-summary ERROR:', status, error, xhr.responseText);
        $qbEmptyHint.text('Network error loading Question Bank.').removeClass('d-none');
      });
  }

  // ---------- CHECKBOX CONTROLS ----------
  $qbMaster.on('change', function () {
    const checked = $(this).is(':checked');
    $('.qb-check').prop('checked', checked);
  });

  $(document).on('change', '.qb-check', function () {
    const total   = $('.qb-check').length;
    const checked = $('.qb-check:checked').length;
    $qbMaster.prop('checked', total > 0 && total === checked);
  });

  $('#qbSelectAll').on('click', function () {
    $('.qb-check').prop('checked', true);
    $qbMaster.prop('checked', $('.qb-check').length > 0);
  });

  $('#qbClearAll').on('click', function () {
    $('.qb-check').prop('checked', false);
    $qbMaster.prop('checked', false);
  });

  // If term changes, refresh term label inside topic cards (re-render summary)
  $('#term_session_id').on('change', function () {
    // just re-render using cached summaryRows, no extra request
    renderNestedCards(summaryRows || []);
    if (selectedKeys.size) reloadQuestionsFromSelected();
  });

  resetQuestionBank('Select a topic card to load questions.');
  loadSummary();

});
</script>


<!-- ===== Start/End Auto Duration + Existing Quizzes + Total Q auto-sum ===== -->
<script>
(function() {
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  const startInput = document.getElementById('start_at');
  const endInput   = document.getElementById('end_at');
  const durBox     = document.getElementById('quizDurationText');

  function formatForInput(d) {
    const pad = n => String(n).padStart(2, '0');
    const yyyy = d.getFullYear();
    const mm   = pad(d.getMonth() + 1);
    const dd   = pad(d.getDate());
    const hh   = pad(d.getHours());
    const mi   = pad(d.getMinutes());
    return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
  }

  function updateDuration() {
    if (!startInput || !endInput || !durBox) return;

    if (!startInput.value || !endInput.value) {
      durBox.textContent = 'Duration: --';
      return;
    }
    const s = new Date(startInput.value);
    const e = new Date(endInput.value);
    if (isNaN(s.getTime()) || isNaN(e.getTime()) || e <= s) {
      durBox.textContent = 'Duration: --';
      return;
    }

    const diffMs    = e - s;
    const diffHours = Math.round(diffMs / 3600000);
    const days      = Math.floor(diffHours / 24);
    const hours     = diffHours % 24;

    let parts = [];
    if (days) {
      parts.push(days + ' day' + (days > 1 ? 's' : ''));
    }
    if (hours || !parts.length) {
      parts.push(hours + ' hour' + (hours !== 1 ? 's' : ''));
    }

    durBox.textContent = 'Duration: ' + parts.join(' ');
  }

  function applyStartDefault() {
    if (!startInput || !endInput) return;
    if (!startInput.value) return;

    const s = new Date(startInput.value);
    if (isNaN(s.getTime())) {
      return;
    }

    const e = new Date(s.getTime() + 86400000);
    endInput.value = formatForInput(e);
    updateDuration();
  }

  if (startInput && endInput) {
    // Initial duration text for defaults
    updateDuration();
    startInput.addEventListener('change', applyStartDefault);
    endInput.addEventListener('change', updateDuration);
  }

  // ===== Existing quizzes cards (6 per row) =====
  const termSel = document.getElementById('term_session_id');
  const clsSel  = document.getElementById('cls_sec_id');
  const subjSel = document.getElementById('subject_id');
  const wrap    = document.getElementById('existingQuizzesWrap');

  function refreshQuizzes() {
    if (!termSel || !clsSel || !subjSel || !wrap) return;

    const termId = termSel.value;
    const clsId  = clsSel.value;
    const secSub = subjSel.value;

    if (!termId || !clsId || !secSub) {
      wrap.innerHTML = '<p class="text-muted mb-0">Select Term, Class Section, and Subject to see already created quizzes.</p>';
      return;
    }

    wrap.innerHTML = '<p class="text-muted mb-0">Loading existing quizzes...</p>';

    const url = '<?= base_url('admin/quizzes/ajax/by-filters') ?>'
      + '?term_session_id=' + encodeURIComponent(termId)
      + '&cls_sec_id='      + encodeURIComponent(clsId)
      + '&sec_sub_id='      + encodeURIComponent(secSub);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(j => {
        if (!j || !j.ok || !Array.isArray(j.data) || !j.data.length) {
          wrap.innerHTML = '<p class="text-muted mb-0">No quizzes found for this combination.</p>';
          return;
        }

        let html = '<div class="row">';
        j.data.forEach(function (q) {
          const pubBadge = q.is_published
            ? '<span class="badge badge-success">Published</span>'
            : '<span class="badge badge-secondary">Draft</span>';

          const attempts = (q.max_attempts && q.max_attempts > 0)
            ? q.max_attempts
            : '∞';

          const qCount = q.questions_count || 'All';

          html += `
            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-6 mb-2">
              <div class="small-box bg-white border shadow-sm h-100">
                <div class="inner">
                  <h6 class="mb-1">${escapeHtml(q.title || 'Untitled quiz')}</h6>
                  <p class="mb-1">
                    ${pubBadge}
                  </p>
                  <p class="mb-1">
                    <small>Questions: ${qCount} &nbsp;|&nbsp; Attempts: ${attempts}</small>
                  </p>
                  <p class="mb-0"><small>Created: ${escapeHtml(q.created_date || '-')}</small></p>
                </div>
                <a href="<?= base_url('admin/quizzes') ?>/${encodeURIComponent(q.quiz_id)}/edit"
                   class="small-box-footer">
                  Edit <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
            </div>
          `;
        });
        html += '</div>';
        wrap.innerHTML = html;
      })
      .catch(function () {
        wrap.innerHTML = '<p class="text-danger mb-0">Error loading quizzes.</p>';
      });
  }

  function attachChange(el) {
    if (!el) return;
    el.addEventListener('change', refreshQuizzes);
  }

  attachChange(termSel);
  attachChange(clsSel);
  attachChange(subjSel);
  setTimeout(refreshQuizzes, 400);

  // ===== Auto total questions from per-type counts =====
    // ===== Auto total questions from per-type counts =====
    // ===== Auto total questions from per-type counts =====
  const typeInputs = document.querySelectorAll('.qb-type-count');
  const totalInput = document.getElementById('questions_count');

  function recalcTotal() {
    if (!totalInput || !typeInputs.length) return;
    let sum = 0;
    typeInputs.forEach(function (inp) {
      const v = parseInt(inp.value, 10);
      if (!isNaN(v) && v > 0) {
        sum += v;
      }
    });
    totalInput.value = sum;

    // ALSO refresh QB filter, if cards already selected and function exists
    if (window.qbReloadFromTypeFilters) {
      window.qbReloadFromTypeFilters();
    }
  }

  if (typeInputs.length && totalInput) {
    typeInputs.forEach(function (inp) {
      inp.addEventListener('input', recalcTotal);
      inp.addEventListener('change', recalcTotal);
    });
    recalcTotal();
  }

})();


 function renderSummary(rows) {
    if (!rows || !rows.length) {
      $qbSummaryBody.empty();
      $qbSummaryWrap.addClass('d-none');
      return;
    }

    // Group by Class + Subject + Topic
    const map = {};

    rows.forEach(function (q) {
      const className = q.class_name   || '-';
      const subject   = q.subject_name || '-';
      const topic     = q.topic_name   || '-';

      const key = className + '||' + subject + '||' + topic;

      if (!map[key]) {
        map[key] = {
          class_name:   className,
          subject_name: subject,
          topic_name:   topic,
          count:        0
        };
      }
      map[key].count++;
    });

    const items = Object.values(map);

    // Optional: sort nicely
    items.sort(function (a, b) {
      if (a.class_name !== b.class_name) {
        return a.class_name.localeCompare(b.class_name);
      }
      if (a.subject_name !== b.subject_name) {
        return a.subject_name.localeCompare(b.subject_name);
      }
      return a.topic_name.localeCompare(b.topic_name);
    });

    let html = '';
    items.forEach(function (row) {
      html += `
        <tr>
          <td>${$('<div>').text(row.class_name).html()}</td>
          <td>${$('<div>').text(row.subject_name).html()}</td>
          <td>${$('<div>').text(row.topic_name).html()}</td>
          <td class="text-right">${row.count}</td>
        </tr>
      `;
    });

    $qbSummaryBody.html(html);
    $qbSummaryWrap.removeClass('d-none');
  }


    // ---------- LOAD QUESTIONS BASED ON SELECTED CARDS + TYPE COUNTS ----------
  function reloadQuestionsFromSelected() {
    if (!selectedKeys.size) {
      resetQuestionBank('Select a card to load questions.');
      return;
    }

    const classIds   = [];
    const subjectIds = [];
    const topicIds   = [];

    selectedKeys.forEach(function (key) {
      const parts = String(key).split('|');
      classIds.push(parts[0]);
      subjectIds.push(parts[1]);
      topicIds.push(parts[2]);
    });

    // ===== READ PER-TYPE COUNTS FROM FORM & BUILD TYPE FILTER =====
    const mcqSingle = parseInt($('#count_mcq_single').val() || '0', 10);
    const mcqMulti  = parseInt($('#count_mcq_multi').val()  || '0', 10);
    const tf        = parseInt($('#count_tf').val()         || '0', 10);
    const fill      = parseInt($('#count_fill').val()       || '0', 10);
    const shortAns  = parseInt($('#count_short').val()      || '0', 10);
    const matchQ    = parseInt($('#count_match').val()      || '0', 10);

    const questionTypes = [];

    if (mcqSingle > 0) {
      // if you store as 'mcq_single'
      questionTypes.push('mcq_single');
    }
    if (mcqMulti > 0) {
      // if you store as 'mcq_multi'
      questionTypes.push('mcq_multi');
    }
    if (tf > 0) {
      // if you store as 'true_false'
      questionTypes.push('true_false');
    }
    if (fill > 0) {
      // if you store as 'fill_blank'
      questionTypes.push('fill_blank');
    }
    if (shortAns > 0) {
      // if you store as 'short_answer'
      questionTypes.push('short_answer');
    }
    if (matchQ > 0) {
      questionTypes.push('match');
    }

    // If all type counts are 0 => no filter, load nothing
    if (!questionTypes.length) {
      resetQuestionBank('Set question type counts (MCQ / TF / Fill / Short / Match) before loading questions.');
      return;
    }

    console.log('reloadQuestionsFromSelected =>', {
      class_ids: classIds,
      subject_ids: subjectIds,
      topic_ids: topicIds,
      question_types: questionTypes
    });

    $qbBody.html(
      '<tr><td colspan="10" class="text-center text-muted">Loading questions...</td></tr>'
    );
    $qbTableWrap.removeClass('d-none');
    $qbEmptyHint.addClass('d-none');
    $qbMaster.prop('checked', false);

    $.ajax({
      url     : baseUrl + 'admin/quizzes/ajax/qb-questions',
      method  : 'POST',
      dataType: 'json',
      data    : {
        class_ids      : classIds,
        subject_ids    : subjectIds,
        topic_ids      : topicIds,
        question_types : questionTypes
      },
      success : function (res) {
        console.log('qb-questions success:', res);
        if (!res || !res.ok) {
          resetQuestionBank((res && res.msg) || 'Error loading questions.');
          return;
        }
        renderQuestions(res.data || []);
      },
      error   : function (xhr, status, error) {
        console.error('QB questions ERROR:', status, error);
        console.error('QB questions server response:', xhr.responseText);
        resetQuestionBank('Error loading questions (network or server).');
      }
    });
  }

  // expose to other scripts
  window.qbReloadFromTypeFilters = reloadQuestionsFromSelected;

</script>

<style>
  .qb-summary-card {
    cursor: pointer;
    transition: all .15s ease-in-out;
  }
  .qb-summary-card.active {
    border-color: #007bff !important;
    box-shadow: 0 0 0 2px rgba(0,123,255,.25);
    background: #f0f7ff;
  }
  .qb-summary-count {
    font-weight: 700;
    font-size: 1.1rem;
  }

   .qb-class-card { border:1px solid #e9ecef; }
  .qb-subject-card { border:1px solid #f1f3f5; }

  .qb-topic-card{
    cursor:pointer;
    transition: all .15s ease-in-out;
    background:#fff;
  }
  .qb-topic-card:hover{
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0,0,0,.06);
    border-color:#cfe2ff !important;
  }
  .qb-topic-card.active{
    border-color:#007bff !important;
    box-shadow: 0 0 0 2px rgba(0,123,255,.18);
    background:#f0f7ff;
  }
  .qb-topic-title{
    max-width: 100%;
  }
</style>


<?= $this->endSection() ?>
