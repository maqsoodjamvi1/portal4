<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  // Defaults for start/end
  $oldStart = old('start_at');
  $oldEnd   = old('end_at');

  $defaultStartAt = $oldStart ?: date('Y-m-d\TH:i');
  $defaultEndAt   = $oldEnd   ?: date('Y-m-d\TH:i', time() + 86400);
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
            <div class="row">
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
            <div class="row">
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
            <div class="row">
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
                       class="form-control text-center fw-bold"
                       id="questions_count"
                       name="questions_count"
                       value="<?= esc(old('questions_count', '0')) ?>"
                       readonly>
              </div>
            </div>

            <!-- Row 3: Per-type Question Counts -->
            <div class="row border rounded p-3 bg-light">
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
            <div class="row">
              <div class="form-group col-md-3">
                <label class="d-block">Shuffle Questions</label>
                <div class="form-check form-switch">
                  <input type="checkbox"
                         class="form-check-input"
                         id="shuffle_questions"
                         name="shuffle_questions"
                         value="1"
                         <?= old('shuffle_questions', '1') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="shuffle_questions">Enable</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">Shuffle Options</label>
                <div class="form-check form-switch">
                  <input type="checkbox"
                         class="form-check-input"
                         id="shuffle_options"
                         name="shuffle_options"
                         value="1"
                         <?= old('shuffle_options', '1') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="shuffle_options">Enable</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">Show Solution</label>
                <div class="form-check form-switch">
                  <input type="checkbox"
                         class="form-check-input"
                         id="show_solution"
                         name="show_solution"
                         value="1"
                         <?= old('show_solution', '1') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="show_solution">After submit</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">WiFi Only</label>
                <div class="form-check form-switch">
                  <input type="checkbox"
                         class="form-check-input"
                         id="wifi_only"
                         name="wifi_only"
                         value="1"
                         <?= old('wifi_only') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="wifi_only">Restrict</label>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-3">
                <label class="d-block">Urdu Quiz?</label>
                <div class="form-check form-switch">
                  <input type="checkbox"
                         class="form-check-input"
                         id="is_urdu"
                         name="is_urdu"
                         value="1"
                         <?= old('is_urdu') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_urdu">Store in is_urdu (0/1)</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">Order by Question Type</label>
                <div class="form-check form-switch">
                  <input type="checkbox"
                         class="form-check-input"
                         id="is_order_by_qtype"
                         name="is_order_by_qtype"
                         value="1"
                         <?= old('is_order_by_qtype') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_order_by_qtype">Group by type</label>
                </div>
              </div>

              <div class="form-group col-md-3">
                <label class="d-block">Publish</label>
                <div class="form-check form-switch">
                  <input type="checkbox"
                         class="form-check-input"
                         id="is_published"
                         name="is_published"
                         value="1"
                         <?= old('is_published', '1') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_published">Visible to students</label>
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
        <div class="card mt-3">
          <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h3 class="card-title mb-0">Question Bank</h3>
            <div>
              <button type="button" class="btn btn-sm btn-outline-primary" id="qbSelectAll">
                Select All
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="qbClearAll">
                Unselect All
              </button>
            </div>
          </div>

          <div class="card-body p-0">
            <div id="qbEmptyHint" class="p-3 text-muted">
              Select Class Section and Subject to load questions from the question bank.
            </div>
            <div class="table-responsive d-none" id="qbTableWrap">
              <table class="table table-sm table-striped mb-0" id="qbTable">
                <thead>
                  <tr>
                    <th style="width:40px;">
                      <input type="checkbox" id="qbCheckMaster">
                    </th>
                    <th style="width:40px;">#</th>
                    <th style="width:90px;">Type</th>
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
          <i class="fas fa-save me-1"></i> Save Quiz
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
<script>
jQuery(function ($) {

  const baseUrl      = '<?= base_url() ?>';
  const $qbTableWrap = $('#qbTableWrap');
  const $qbEmptyHint = $('#qbEmptyHint');
  const $qbBody      = $('#qbTable tbody');
  const $qbMaster    = $('#qbCheckMaster');
  const $topicBox    = $('#topicFilters');

  function resetQuestionBank(msg) {
    $qbBody.empty();
    $qbTableWrap.addClass('d-none');
    $qbEmptyHint.text(msg || 'No questions loaded.').removeClass('d-none');
    $qbMaster.prop('checked', false);
  }

  function clearTopics(msg) {
    $topicBox.addClass('d-none').empty();
    if (msg) {
      $topicBox.removeClass('d-none').html('<span class="text-muted">'+msg+'</span>');
    }
  }

  function renderQuestionBank(rows) {
    if (!rows || !rows.length) {
      resetQuestionBank('No questions found in question bank for selected topics.');
      return;
    }

    $qbBody.empty();

    const typeLabels = {
      mcq:           'MCQ',
      mcq_single:    'MCQ',
      tf:            'True/False',
      true_false:    'True/False',
      short:         'Short Answer',
      short_answer:  'Short Answer',
      fill:          'Fill in the Blank',
      fill_blank:    'Fill in the Blank',
      match:         'Matching'
    };

    rows.forEach(function (q, idx) {
      const typeKey   = (q.question_type || '').toLowerCase();
      const typeLabel = typeLabels[typeKey] || (q.question_type || '-');

      let questionText = q.question || '';
      if (questionText.length > 180) {
        questionText = questionText.substr(0, 180) + '…';
      }

      const difficulty = q.difficulty || '-';
      const createdAt  = q.created_date || '';

      const rowHtml = `
        <tr>
          <td>
            <input type="checkbox"
                   class="qb-check"
                   name="question_ids[]"
                   value="${q.id}">
          </td>
          <td>${idx + 1}</td>
          <td>
            <span class="badge text-bg-info">${typeLabel}</span>
          </td>
          <td>${$('<div>').text(questionText).html()}</td>
          <td>
            <span class="badge text-bg-light">${difficulty}</span>
          </td>
          <td><small>${createdAt}</small></td>
        </tr>
      `;

      $qbBody.append(rowHtml);
    });

    $qbEmptyHint.addClass('d-none');
    $qbTableWrap.removeClass('d-none');
    $qbMaster.prop('checked', false);
  }

  function loadTopics(secSubId) {
    clearTopics();
    resetQuestionBank('Loading questions...');

    $.getJSON(baseUrl + 'admin/quizzes/ajaxQbTopicsBySecSub/' + secSubId, function (resp) {
      if (!resp || !resp.ok) {
        clearTopics('<span class="text-danger">Error loading topics.</span>');
        resetQuestionBank('Error loading questions.');
        return;
      }

      const topics = resp.topics || [];
      if (!topics.length) {
        clearTopics('<span class="badge text-bg-warning">No topics defined for this class &amp; subject.</span>');
        reloadQuestions(secSubId);
        return;
      }

      let html = '<div class="form-group mb-1 mb-0">';
      html += '<label class="mb-1"><strong>Topics:</strong></label><br>';

      topics.forEach(function (t) {
        html += `
          <label class="me-2 mb-1 badge text-bg-light">
            <input type="checkbox"
                   class="topic-filter me-1"
                   name="quiz_topic_ids[]"
                   value="${t.id}"
                   checked>
            ${$('<div>').text(t.topic_name).html()}
          </label>
        `;
      });

      html += '</div>';

      $topicBox.removeClass('d-none').html(html);

      $topicBox.off('change', '.topic-filter').on('change', '.topic-filter', function () {
        reloadQuestions(secSubId);
      });

      reloadQuestions(secSubId);
    }).fail(function () {
      clearTopics('<span class="text-danger">Error loading topics (network).</span>');
      resetQuestionBank('Error loading questions.');
    });
  }

  function reloadQuestions(secSubId) {
    const $checks   = $topicBox.find('.topic-filter');
    const hasTopics = $checks.length > 0;
    const selected  = [];

    $checks.filter(':checked').each(function () {
      selected.push($(this).val());
    });

    if (hasTopics && !selected.length) {
      $qbBody.html(
        '<tr><td colspan="6" class="text-center text-muted">No topic selected.</td></tr>'
      );
      $qbTableWrap.removeClass('d-none');
      $qbEmptyHint.addClass('d-none');
      $qbMaster.prop('checked', false);
      return;
    }

    $qbBody.html(
      '<tr><td colspan="6" class="text-center text-muted">Loading questions...</td></tr>'
    );
    $qbTableWrap.removeClass('d-none');
    $qbEmptyHint.addClass('d-none');
    $qbMaster.prop('checked', false);

    const data = {};
    if (selected.length) {
      data['topic_ids[]'] = selected;
    }

    $.ajax({
      url: baseUrl + 'admin/quizzes/ajaxQbQuestionsBySecSub/' + secSubId,
      method: 'POST',
      dataType: 'json',
      data: data,
      success: function (res) {
        if (!res || !res.ok) {
          alert((res && res.msg) || 'Error loading questions');
          resetQuestionBank('Error loading questions.');
          return;
        }

        renderQuestionBank(res.data || []);
      },
      error: function () {
        resetQuestionBank('Error loading questions (network).');
      }
    });
  }

  $(document).on('change', '#subject_id', function () {
    const secSubId = $(this).val();
    if (!secSubId) {
      clearTopics();
      resetQuestionBank('Select Class Section and Subject to load questions.');
      return;
    }
    loadTopics(secSubId);
  });

  if ($.fn.select2) {
    $(document).on('select2:select', '#subject_id', function () {
      $(this).trigger('change');
    });
  }

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
            ? '<span class="badge text-bg-success">Published</span>'
            : '<span class="badge text-bg-secondary">Draft</span>';

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
  }

  if (typeInputs.length && totalInput) {
    typeInputs.forEach(function (inp) {
      inp.addEventListener('input', recalcTotal);
    });
    recalcTotal();
  }

})();
</script>

<?= $this->endSection() ?>
