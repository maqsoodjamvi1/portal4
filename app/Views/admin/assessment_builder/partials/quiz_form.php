<?php

/** Assessment Builder — quiz save block */

$qd = $quizDefaults ?? [];

?>

<div class="ab-save-block ab-save-block--quiz h-100">

<form action="<?= base_url('admin/quizzes/store') ?>" method="post" id="quizCreateForm" class="quiz-create-master mb-0 h-100 d-flex flex-column">

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



  <div class="ab-save-block__head">

    <span class="ab-save-block__title"><i class="fas fa-gamepad"></i> Quiz</span>

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



    <div class="row">

      <div class="form-group col-md-4 col-12 mb-2">

        <label for="term_session_id" class="ab-label">Term <span class="text-danger">*</span></label>

        <select class="form-control form-control-sm" id="term_session_id" name="term_session_id" required>

          <option value="">Select term</option>

          <?php foreach (($terms ?? []) as $t):

            $tsid  = (int) ($t->term_session_id ?? 0);

            $tname = $t->term_name ?? ('Term ' . (int) ($t->term_id ?? 0));

            $sel   = ((string) old('term_session_id', $qd['term_session_id'] ?? '')) === (string) $tsid ? 'selected' : '';

          ?>

            <option value="<?= $tsid ?>" <?= $sel ?>><?= esc($tname) ?></option>

          <?php endforeach; ?>

        </select>

      </div>

      <div class="form-group col-md-4 col-12 mb-2">

        <label for="cls_sec_id" class="ab-label">Class <span class="text-danger">*</span></label>

        <select name="cls_sec_id" id="cls_sec_id" class="form-control form-control-sm" required>

          <option value="">Select class</option>

          <?php foreach (($classSections ?? []) as $cs):

            $id    = (int) ($cs['cls_sec_id'] ?? 0);

            $label = $cs['label'] ?? ('Section ' . $id);

            $sel   = ((string) old('cls_sec_id', $qd['cls_sec_id'] ?? '')) === (string) $id ? 'selected' : '';

          ?>

            <option value="<?= $id ?>" <?= $sel ?>><?= esc($label) ?></option>

          <?php endforeach; ?>

        </select>

      </div>

      <div class="form-group col-md-4 col-12 mb-2">

        <label for="subject_id" class="ab-label">Subject <span class="text-danger">*</span></label>

        <select class="form-control form-control-sm" id="subject_id" name="subject_id" required>

          <option value="">Select subject</option>

          <?php $prefSub = (int) old('subject_id', $qd['sec_sub_id'] ?? 0);

          if ($prefSub > 0): ?>

            <option value="<?= $prefSub ?>" selected>Loading…</option>

          <?php endif; ?>

        </select>

      </div>

    </div>



    <div id="quizSettingsSummary" class="ab-settings-summary small text-muted mb-2"></div>



    <div class="mt-auto pt-2">

      <button type="submit" class="btn btn-success w-100 ab-generate-btn">

        <i class="fas fa-bolt me-1"></i> Generate Quiz

      </button>

    </div>

  </div>



  <?= view('admin/assessment_builder/partials/quiz_settings_modal', get_defined_vars()) ?>

</form>

</div>
