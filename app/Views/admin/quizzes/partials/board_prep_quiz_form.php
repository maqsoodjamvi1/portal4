<?php
/** Board prep quiz save block — title + settings only (filters are in step 1) */
$qd = $quizDefaults ?? [];
?>
<div class="ab-save-block ab-save-block--quiz h-100">
<form action="<?= base_url('admin/quizzes/store-board-prep') ?>" method="post" id="quizCreateForm" class="quiz-create-master mb-0 h-100 d-flex flex-column">
  <?= csrf_field() ?>
  <input type="hidden" name="campus_id" value="<?= (int) ($campusId ?? 0) ?>">
  <input type="hidden" name="session_id" value="<?= (int) ($sessionId ?? 0) ?>">
  <input type="hidden" name="topic_keys_json" id="topic_keys_json" value="">
  <input type="hidden" name="questions_count" id="questions_count" value="<?= esc(old('questions_count', '0')) ?>">
  <input type="hidden" name="count_mcq_single" id="quiz_count_mcq_single" value="0">
  <input type="hidden" name="count_mcq_multi" id="quiz_count_mcq_multi" value="0">
  <input type="hidden" name="count_tf" id="quiz_count_tf" value="0">
  <input type="hidden" name="count_fill" id="quiz_count_fill" value="0">
  <input type="hidden" name="count_short" id="quiz_count_short" value="0">
  <input type="hidden" name="count_match" id="quiz_count_match" value="0">
  <div id="quizQuestionIdsWrap"></div>
  <div id="topicFilters" class="d-none"></div>

  <?php if (! empty($boardPrepAudienceSupported)): ?>
  <input type="hidden" name="audience" id="audience" value="<?= esc(old('audience', 'board_prep')) ?>">
  <?php endif; ?>

  <div class="ab-save-block__head">
    <span class="ab-save-block__title"><i class="fas fa-book-reader"></i> Board prep quiz</span>
    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quizSettingsModal">
      <i class="fas fa-cog"></i> Settings
    </button>
  </div>

  <div class="ab-save-block__body flex-grow-1 d-flex flex-column">
    <div class="form-group mb-2">
      <label for="title" class="ab-label">Title <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="title" name="title"
             value="<?= esc(old('title', $qd['title'] ?? '')) ?>" placeholder="Quiz title" required>
    </div>

    <div id="bpSelectionSummary" class="ab-settings-summary small text-muted mb-2">Select board, grade &amp; subject above, then pick topics.</div>
    <div id="quizSettingsSummary" class="ab-settings-summary small text-muted mb-2"></div>

    <div class="mt-auto pt-2">
      <button type="submit" class="btn btn-success w-100 ab-generate-btn" id="btnGenerateBoardPrepQuiz">
        <i class="fas fa-bolt me-1"></i> Generate Board Prep Quiz
      </button>
    </div>
  </div>

  <?= view('admin/assessment_builder/partials/quiz_settings_modal', get_defined_vars()) ?>
</form>
</div>
