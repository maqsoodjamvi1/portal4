<?php if (empty($boardPrepAudienceSupported)) return; ?>
<div class="card mb-3 border-0 shadow-sm border-start border-success" style="border-start-width:4px !important;">
  <div class="card-header bg-light py-2">
    <h3 class="card-title mb-0">Board exam prep portal</h3>
  </div>
  <div class="card-body pb-2">
    <p class="text-muted small mb-3">
      This quiz is created for <strong>prep.timesoftsol.com</strong> students only.
      It is <strong>published automatically</strong>, has <strong>no start/end window</strong>, and allows <strong>unlimited attempts</strong>.
    </p>
    <input type="hidden" name="audience" value="board_prep">
    <input type="hidden" name="is_published" value="1">
    <div class="row">
      <div class="form-group col-md-6 mb-md-0">
        <label for="prep_grade_level">Prep class / grade <span class="text-danger">*</span></label>
        <select name="prep_grade_level" id="prep_grade_level" class="form-control" required>
          <option value="">— Select —</option>
          <?php foreach (($boardPrepGrades ?? []) as $gKey => $gLabel) : ?>
            <option value="<?= esc($gKey) ?>" <?= (string) old('prep_grade_level', $quizDefaults['prep_grade_level'] ?? '') === (string) $gKey ? 'selected' : '' ?>><?= esc($gLabel) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6 d-flex align-items-center">
        <p class="text-muted small mb-0">
          <i class="fas fa-university me-1"></i>
          Select the <strong>board</strong> in the Question Bank section below (first column) to load class, subject, and topics for that board.
        </p>
      </div>
    </div>
  </div>
</div>
