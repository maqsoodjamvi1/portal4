<?php
/** Quiz settings modal — timing, marks, behaviour (used inside #quizCreateForm) */
$qd = $quizDefaults ?? [];
$dtLocal = static function ($v): string {
    if ($v === null || $v === '') { return ''; }
    $ts = strtotime((string) $v);
    if ($ts === false) { return ''; }
    return date('Y-m-d\TH:i', $ts);
};
$oldStart = old('start_at');
$oldEnd = old('end_at');
$defaultStartAt = $oldStart ?: ($dtLocal($qd['start_at'] ?? null) ?: date('Y-m-d\TH:i'));
$defaultEndAt = $oldEnd ?: ($dtLocal($qd['end_at'] ?? null) ?: date('Y-m-d\TH:i'));
$chk = static function (string $field, array $qd, bool $defaultOn = false): bool {
    $v = old($field);
    if ($v !== null && $v !== '') { return (bool) $v; }
    if (array_key_exists($field, $qd)) { return (bool) (int) ($qd[$field] ?? 0); }
    return $defaultOn;
};
?>
<div class="modal fade" id="quizSettingsModal" tabindex="-1" role="dialog" aria-labelledby="quizSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="quizSettingsModalLabel">
          <i class="fas fa-cog me-1"></i> Configure quiz settings
        </h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Defaults come from your last saved quiz settings. Change anything here before saving the quiz.</p>

        <input type="hidden" name="is_adaptive" value="0">

        <h6 class="fw-bold text-uppercase text-muted small mb-2">Schedule &amp; marks</h6>
        <div class="row">
          <div class="form-group col-md-6">
            <label for="start_at">Start at</label>
            <input type="datetime-local" class="form-control" id="start_at" name="start_at" value="<?= esc($defaultStartAt) ?>">
          </div>
          <div class="form-group col-md-6">
            <label for="end_at">End at</label>
            <input type="datetime-local" class="form-control" id="end_at" name="end_at" value="<?= esc($defaultEndAt) ?>">
            <small id="quizDurationText" class="form-text text-muted">Duration: --</small>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-3 col-6">
            <label for="time_limit_min">Time (min)</label>
            <input type="number" class="form-control text-center" id="time_limit_min" name="time_limit_min"
                   value="<?= esc(old('time_limit_min', (string) ($qd['time_limit_min'] ?? '0'))) ?>" min="0" step="1">
          </div>
          <div class="form-group col-md-3 col-6">
            <label for="max_attempts">Attempts</label>
            <input type="number" class="form-control text-center" id="max_attempts" name="max_attempts"
                   value="<?= esc(old('max_attempts', (string) ($qd['max_attempts'] ?? '1'))) ?>" min="1" step="1">
          </div>
          <div class="form-group col-md-3 col-6">
            <label for="per_question_marks">Marks / Q</label>
            <input type="number" class="form-control text-center" id="per_question_marks" name="per_question_marks"
                   value="<?= esc(old('per_question_marks', (string) ($qd['per_question_marks'] ?? '1'))) ?>" min="0" step="0.5">
          </div>
          <div class="form-group col-md-3 col-6">
            <label for="negative_mark_per_q">Negative / Q</label>
            <input type="number" class="form-control text-center" id="negative_mark_per_q" name="negative_mark_per_q"
                   value="<?= esc(old('negative_mark_per_q', (string) ($qd['negative_mark_per_q'] ?? '0'))) ?>" min="0" step="0.25">
          </div>
        </div>

        <hr>

        <h6 class="fw-bold text-uppercase text-muted small mb-2">Behaviour &amp; visibility</h6>

        <?php if (!empty($examQuizColumnReady) && !empty($unannouncedExam)): ?>
        <div class="alert alert-info py-2 mb-3" id="examQuizInfoBox">
          <div class="form-check form-check">
            <input type="checkbox" class="form-check-input" id="link_to_exam" name="link_to_exam" value="1"
                   <?= $chk('link_to_exam', $qd, false) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold" for="link_to_exam">
              Online exam quiz (admin-only until exam is announced)
            </label>
          </div>
          <small class="d-block mt-1 text-muted">
            Links this quiz to the current unannounced exam:
            <strong><?= esc($unannouncedExam['exam_name'] ?? 'Exam') ?></strong>
            (<?= esc($unannouncedExam['exam_start_date'] ?? '') ?> – <?= esc($unannouncedExam['exam_end_date'] ?? '') ?>).
            Use <a href="<?= site_url('admin/quiz-assign') ?>">Quiz Assign</a> to test as a student.
          </small>
        </div>
        <?php elseif (!empty($examQuizColumnReady)): ?>
        <div class="alert alert-secondary py-2 mb-3">
          <small class="text-muted mb-0">No unannounced exam in the current session.</small>
        </div>
        <?php endif; ?>

        <div class="row">
          <div class="form-group col-md-4 col-6">
            <label class="d-block">Shuffle questions</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="shuffle_questions" name="shuffle_questions" value="1"
                     <?= $chk('shuffle_questions', $qd, true) ? 'checked' : '' ?>>
              <label class="form-check-label" for="shuffle_questions">Enable</label>
            </div>
          </div>
          <div class="form-group col-md-4 col-6">
            <label class="d-block">Shuffle options</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="shuffle_options" name="shuffle_options" value="1"
                     <?= $chk('shuffle_options', $qd, true) ? 'checked' : '' ?>>
              <label class="form-check-label" for="shuffle_options">Enable</label>
            </div>
          </div>
          <div class="form-group col-md-4 col-6">
            <label class="d-block">Show solution</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="show_solution" name="show_solution" value="1"
                     <?= $chk('show_solution', $qd, true) ? 'checked' : '' ?>>
              <label class="form-check-label" for="show_solution">After submit</label>
            </div>
          </div>
          <div class="form-group col-md-4 col-6">
            <label class="d-block">WiFi only</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="wifi_only" name="wifi_only" value="1"
                     <?= $chk('wifi_only', $qd, false) ? 'checked' : '' ?>>
              <label class="form-check-label" for="wifi_only">Restrict</label>
            </div>
          </div>
          <div class="form-group col-md-4 col-6">
            <label class="d-block">Urdu quiz</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="is_urdu" name="is_urdu" value="1"
                     <?= $chk('is_urdu', $qd, false) ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_urdu">Urdu layout</label>
            </div>
          </div>
          <div class="form-group col-md-4 col-6">
            <label class="d-block">Order by type</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="is_order_by_qtype" name="is_order_by_qtype" value="1"
                     <?= $chk('is_order_by_qtype', $qd, false) ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_order_by_qtype">Group by type</label>
            </div>
          </div>
          <div class="form-group col-md-4 col-6">
            <label class="d-block">Publish</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1"
                     <?= $chk('is_published', $qd, true) ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_published">Visible to students</label>
            </div>
            <small id="publishHint" class="form-text text-muted d-none">Disabled for exam quizzes until announced.</small>
          </div>
        </div>

        <hr>

        <h6 class="fw-bold text-uppercase text-muted small mb-2">Instructions (optional)</h6>
        <div class="form-group mb-0">
          <label for="quiz_instructions" class="visually-hidden">Instructions</label>
          <textarea class="form-control" id="quiz_instructions" name="instructions" rows="4"
                    placeholder="Guidelines, time rules, allowed materials, etc."><?= esc(old('instructions', $qd['instructions'] ?? '')) ?></textarea>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>
