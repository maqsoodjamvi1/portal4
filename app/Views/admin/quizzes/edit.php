<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $q = $quiz;
  $dtLocal = static function ($v): string {
    if ($v === null || $v === '') {
      return '';
    }
    $ts = strtotime((string) $v);
    return $ts ? date('Y-m-d\TH:i', $ts) : '';
  };
  $timeLimitMin = (int) ($q->time_limit_sec ?? 0) > 0
    ? (int) round(((int) $q->time_limit_sec) / 60)
  : 0;
  $chk = static function (string $field, $obj, bool $default = false): bool {
    $v = old($field);
    if ($v !== null && $v !== '') {
      return (bool) $v;
    }
    if (isset($obj->$field)) {
      return (bool) (int) $obj->$field;
    }
    return $default;
  };
  $val = static function (string $field, $obj, $default = '') {
    $o = old($field);
    if ($o !== null && $o !== '') {
      return $o;
    }
    return $obj->$field ?? $default;
  };
  $prefSub = (int) old('subject_id', (int) ($q->sec_sub_id ?? 0));
  $linkExamChecked = $chk('link_to_exam', (object) ['link_to_exam' => ($linkedExamId ?? 0) > 0 ? 1 : 0]);
?>

<?= view('components/page_header', [
    'title' => 'Edit Quiz',
    'icon' => 'fas fa-edit',
    'subtitle' => 'Quiz #' . (int) $quizId . ' — ' . ($q->title ?? ''),
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quizzes', 'url' => base_url('admin/quizzes')],
        ['label' => 'Edit', 'active' => true],
    ],
]) ?>

<section class="content">
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="alert alert-light border mb-3">
    <a href="<?= site_url('admin/quizzes/edit-questions/' . (int) $quizId) ?>" class="btn btn-sm btn-outline-primary">
      <i class="fas fa-edit"></i> Edit Questions
    </a>
    <span class="text-muted ms-2">Change question content separately on the questions screen.</span>
  </div>

  <div class="card card-outline card-primary shadow-sm">
    <form action="<?= base_url('admin/quizzes/update/' . (int) $quizId) ?>" method="post" id="quizEditForm">
      <?= csrf_field() ?>
      <input type="hidden" name="campus_id" value="<?= (int) ($campusId ?? 0) ?>">
      <input type="hidden" name="session_id" value="<?= (int) ($sessionId ?? 0) ?>">

      <div class="card-body">
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-light py-2">
            <h3 class="card-title mb-0">Target Class &amp; Subject</h3>
          </div>
          <div class="card-body pb-2">
            <div class="row">
              <div class="form-group col-lg-4 col-md-6">
                <label for="term_session_id">Term <span class="text-danger">*</span></label>
                <select class="form-control" id="term_session_id" name="term_session_id" required>
                  <option value="">-- Select Term --</option>
                  <?php foreach (($terms ?? []) as $t):
                    $tsid  = (int) ($t->term_session_id ?? 0);
                    $tname = $t->term_name ?? ('Term ' . (int) ($t->term_id ?? 0));
                    $sel   = (string) old('term_session_id', (string) ($q->term_session_id ?? '')) === (string) $tsid ? 'selected' : '';
                  ?>
                    <option value="<?= $tsid ?>" <?= $sel ?>><?= esc($tname) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (isset($validation) && $validation->hasError('term_session_id')): ?>
                  <small class="text-danger"><?= $validation->getError('term_session_id') ?></small>
                <?php endif; ?>
              </div>

              <div class="form-group col-lg-4 col-md-6">
                <label for="cls_sec_id">Class Section <span class="text-danger">*</span></label>
                <select name="cls_sec_id" id="cls_sec_id" class="form-control" required>
                  <option value="">-- Select Class Section --</option>
                  <?php foreach (($classSections ?? []) as $cs):
                    $id    = (int) ($cs['cls_sec_id'] ?? 0);
                    $label = $cs['label'] ?? ('#' . $id);
                    $sel   = (string) old('cls_sec_id', (string) ($q->cls_sec_id ?? '')) === (string) $id ? 'selected' : '';
                  ?>
                    <option value="<?= $id ?>" <?= $sel ?>><?= esc($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group col-lg-4 col-md-6">
                <label for="subject_id">Subject <span class="text-danger">*</span></label>
                <select class="form-control" id="subject_id" name="subject_id" required>
                  <option value="">-- Select Subject --</option>
                  <?php if ($prefSub > 0): ?>
                    <option value="<?= $prefSub ?>" selected>Loading…</option>
                  <?php endif; ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <?= view('admin/quizzes/partials/board_prep_targeting', [
            'boardPrepAudienceSupported' => $boardPrepAudienceSupported ?? false,
            'boardPrepGrades'            => $boardPrepGrades ?? [],
            'boardPublishers'            => $boardPublishers ?? [],
            'quiz'                       => $q,
        ]) ?>

        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-light py-2">
            <h3 class="card-title mb-0">Quiz Settings</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="form-group col-md-4">
                <label for="title">Quiz Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title"
                       value="<?= esc($val('title', $q)) ?>" required>
              </div>
              <div class="form-group col-md-4">
                <label for="start_at">Start At</label>
                <input type="datetime-local" class="form-control" id="start_at" name="start_at"
                       value="<?= esc(old('start_at', $dtLocal($q->start_at ?? null))) ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="end_at">End At</label>
                <input type="datetime-local" class="form-control" id="end_at" name="end_at"
                       value="<?= esc(old('end_at', $dtLocal($q->end_at ?? null))) ?>">
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-2">
                <label for="time_limit_min">Time (min)</label>
                <input type="number" class="form-control text-center" id="time_limit_min" name="time_limit_min"
                       value="<?= esc(old('time_limit_min', (string) $timeLimitMin)) ?>" min="0">
              </div>
              <div class="form-group col-md-2">
                <label for="max_attempts">Attempts</label>
                <input type="number" class="form-control text-center" id="max_attempts" name="max_attempts"
                       value="<?= esc(old('max_attempts', (string) ($q->max_attempts ?? 1))) ?>" min="1">
              </div>
              <div class="form-group col-md-2">
                <label for="per_question_marks">Marks / Q</label>
                <input type="number" class="form-control text-center" id="per_question_marks" name="per_question_marks"
                       value="<?= esc(old('per_question_marks', (string) ($q->per_question_marks ?? 1))) ?>" min="0" step="0.5">
              </div>
              <div class="form-group col-md-2">
                <label for="negative_mark_per_q">Negative / Q</label>
                <input type="number" class="form-control text-center" id="negative_mark_per_q" name="negative_mark_per_q"
                       value="<?= esc(old('negative_mark_per_q', (string) ($q->negative_mark_per_q ?? 0))) ?>" min="0" step="0.25">
              </div>
              <div class="form-group col-md-2">
                <label>Questions</label>
                <input type="text" class="form-control text-center" readonly
                       value="<?= (int) ($q->questions_count ?? 0) ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="instructions">Instructions</label>
              <textarea class="form-control" id="instructions" name="instructions" rows="3"><?= esc($val('instructions', $q)) ?></textarea>
            </div>
          </div>
        </div>

        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-light py-2">
            <h3 class="card-title mb-0">Quiz Behaviour</h3>
          </div>
          <div class="card-body">
            <?php if (!empty($examQuizColumnReady) && !empty($unannouncedExam)): ?>
            <div class="alert alert-info py-2 mb-3">
              <div class="form-check form-check">
                <input type="checkbox" class="form-check-input" id="link_to_exam" name="link_to_exam" value="1"
                  <?= $linkExamChecked ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold" for="link_to_exam">
                  Online exam quiz (admin-only until exam is announced)
                </label>
              </div>
              <small class="d-block mt-1 text-muted">
                Links to: <strong><?= esc($unannouncedExam['exam_name'] ?? 'Exam') ?></strong>
              </small>
            </div>
            <?php endif; ?>

            <div class="row">
              <div class="form-group col-md-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="shuffle_questions" name="shuffle_questions" value="1"
                    <?= $chk('shuffle_questions', $q, true) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="shuffle_questions">Shuffle Questions</label>
                </div>
              </div>
              <div class="form-group col-md-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="shuffle_options" name="shuffle_options" value="1"
                    <?= $chk('shuffle_options', $q, true) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="shuffle_options">Shuffle Options</label>
                </div>
              </div>
              <div class="form-group col-md-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="show_solution" name="show_solution" value="1"
                    <?= $chk('show_solution', $q, true) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="show_solution">Show Solution</label>
                </div>
              </div>
              <div class="form-group col-md-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="wifi_only" name="wifi_only" value="1"
                    <?= $chk('wifi_only', $q, false) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="wifi_only">Wi-Fi Only</label>
                </div>
              </div>
              <div class="form-group col-md-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1"
                    <?= $chk('is_published', $q, false) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_published">Published</label>
                </div>
              </div>
              <div class="form-group col-md-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="is_urdu" name="is_urdu" value="1"
                    <?= $chk('is_urdu', $q, false) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_urdu">Urdu Layout</label>
                </div>
              </div>
              <div class="form-group col-md-3">
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="is_order_by_qtype" name="is_order_by_qtype" value="1"
                    <?= $chk('is_order_by_qtype', $q, false) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_order_by_qtype">Order by Question Type</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        <a href="<?= base_url('admin/quizzes') ?>" class="btn btn-secondary ms-2">Cancel</a>
      </div>
    </form>
  </div>
</section>

<script>
(function () {
  const clsSel = document.getElementById('cls_sec_id');
  const subSel = document.getElementById('subject_id');
  const prefSub = <?= (int) $prefSub ?>;

  function loadSubjects(clsSecId, selectSecSubId) {
    if (!clsSecId || !subSel) return;
    subSel.innerHTML = '<option value="">Loading…</option>';
    fetch('<?= site_url('admin/quizzes/ajax/section-subjects/') ?>' + encodeURIComponent(clsSecId), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(r => r.json())
      .then(res => {
        subSel.innerHTML = '<option value="">-- Select Subject --</option>';
        if (!res.ok || !res.data) return;
        res.data.forEach(row => {
          const opt = document.createElement('option');
          opt.value = row.sec_sub_id;
          opt.textContent = row.name || row.subject_name || ('Subject ' + row.sec_sub_id);
          if (selectSecSubId && String(row.sec_sub_id) === String(selectSecSubId)) {
            opt.selected = true;
          }
          subSel.appendChild(opt);
        });
      })
      .catch(() => {
        subSel.innerHTML = '<option value="">Failed to load</option>';
      });
  }

  if (clsSel) {
    clsSel.addEventListener('change', function () {
      loadSubjects(this.value, 0);
    });
    if (clsSel.value) {
      loadSubjects(clsSel.value, prefSub);
    }
  }
})();
</script>

<?= $this->endSection() ?>
