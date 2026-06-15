<?php

/** Bulk board prep form — settings + submit */

$qd = $quizDefaults ?? [];

?>

<form action="<?= base_url('admin/quizzes/store-board-prep-bulk') ?>" method="post" id="bpBulkForm" class="mb-0">

  <?= csrf_field() ?>

  <input type="hidden" name="campus_id" value="<?= (int) ($campusId ?? 0) ?>">

  <input type="hidden" name="session_id" value="<?= (int) ($sessionId ?? 0) ?>">

  <input type="hidden" name="groups_json" id="bp_groups_json" value="">

  <?php if (! empty($boardPrepAudienceSupported)): ?>

  <input type="hidden" name="audience" value="board_prep">

  <?php endif; ?>



  <div class="card card-outline card-success">

    <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">

      <div class="mb-2 mb-md-0">

        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#quizSettingsModal">

          <i class="fas fa-cog"></i> Quiz settings

        </button>

        <span id="bpSettingsSummary" class="text-muted small ms-2"></span>

      </div>

      <button type="submit" class="btn btn-success" id="bpBulkSubmitBtn" disabled>

        <i class="fas fa-bolt me-1"></i> <span id="bpBulkSubmitLabel">Create quizzes</span>

      </button>

    </div>

  </div>



  <?= view('admin/assessment_builder/partials/quiz_settings_modal', get_defined_vars()) ?>

</form>
