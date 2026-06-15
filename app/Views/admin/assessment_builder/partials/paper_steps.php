<?php

/** Assessment Builder — paper save block */

$qd = $quizDefaults ?? [];

?>

<div class="ab-save-block ab-save-block--paper h-100">

<form action="<?= base_url('admin/question-paper/store') ?>" method="post" id="paperSaveForm" class="mb-0 h-100 d-flex flex-column">

  <?= csrf_field() ?>

  <input type="hidden" name="selection_mode" id="paper_selection_mode" value="auto">

  <input type="hidden" name="paper_subject" id="paper_subject" value="">

  <input type="hidden" name="paper_class" id="paper_class" value="">

  <div id="paperFormFieldsWrap"></div>



  <div class="ab-save-block__head">

    <span class="ab-save-block__title"><i class="fas fa-file-alt"></i> Question paper</span>

    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paperPrintSettingsModal">

      <i class="fas fa-print"></i> Print settings

    </button>

  </div>



  <div class="ab-save-block__body flex-grow-1 d-flex flex-column">

    <div class="form-group mb-2">

      <label for="paper_title" class="ab-label">Title <span class="text-danger">*</span></label>

      <input type="text" class="form-control" id="paper_title" name="paper_title"

             placeholder="Paper title" required>

    </div>



    <div class="row">

      <div class="form-group col-md-4 col-12 mb-2">

        <label for="paper_term_session_id" class="ab-label">Term <span class="text-danger">*</span></label>

        <select class="form-control form-control-sm" id="paper_term_session_id" name="term_session_id" required>

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

        <label for="paper_cls_sec_id" class="ab-label">Class <span class="text-danger">*</span></label>

        <select name="cls_sec_id" id="paper_cls_sec_id" class="form-control form-control-sm" required>

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

        <label for="paper_subject_id" class="ab-label">Subject <span class="text-danger">*</span></label>

        <select class="form-control form-control-sm" id="paper_subject_id" name="subject_id" required>

          <option value="">Select subject</option>

          <?php $prefSub = (int) old('subject_id', $qd['sec_sub_id'] ?? 0);

          if ($prefSub > 0): ?>

            <option value="<?= $prefSub ?>" selected>Loading…</option>

          <?php endif; ?>

        </select>

      </div>

    </div>



    <div id="paperPrintSettingsSummary" class="ab-settings-summary small text-muted mb-2"></div>



    <div class="mt-auto pt-2">

      <button type="submit" class="btn btn-primary w-100 ab-generate-btn" id="btnSavePaper">

        <i class="fas fa-bolt me-1"></i> Generate Paper

      </button>

    </div>

  </div>



  <?= view('admin/assessment_builder/partials/paper_print_settings_modal') ?>

</form>

</div>
