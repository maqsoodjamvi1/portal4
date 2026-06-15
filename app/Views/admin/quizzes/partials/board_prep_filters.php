<?php

/** Step 1 filters — board, class (system 1), subject */

$classes = $boardPrepClasses ?? [];

$prefClass = (int) old('class_id', 0);

$prefSub   = (int) old('subject_id', 0);

$prefBoard = (int) old('prep_board_publisher_id', 0);

?>

<div class="card card-outline card-info mb-3" id="bpFiltersCard">

  <div class="card-header py-2">

    <h3 class="card-title mb-0">1. Board, class &amp; subject</h3>

  </div>

  <div class="card-body py-3">

    <p class="text-muted small mb-3">Classes come from your main school system (System <?= (int) ($boardPrepSystemId ?? 1) ?>). Subjects load from class subjects for the selected class.</p>

    <div class="row">

      <div class="form-group col-md-4 col-12 mb-2">

        <label for="prep_board_publisher_id" class="fw-bold small">Board <span class="text-danger">*</span></label>

        <select class="form-control form-control-sm" id="prep_board_publisher_id" name="prep_board_publisher_id" form="bpBulkForm" required>

          <option value="">Select board</option>

          <?php foreach (($boardPublishers ?? []) as $bp):

            $id = (int) ($bp['id'] ?? 0);

            $code = trim((string) ($bp['short_code'] ?? ''));

            $lbl = $code !== '' ? $code : ($bp['name'] ?? ('Board ' . $id));

            $sel = $prefBoard === $id ? 'selected' : '';

          ?>

            <option value="<?= $id ?>" <?= $sel ?> title="<?= esc($bp['name'] ?? '') ?>"><?= esc($lbl) ?> — <?= esc($bp['name'] ?? '') ?></option>

          <?php endforeach; ?>

        </select>

      </div>

      <div class="form-group col-md-4 col-12 mb-2">

        <label for="bp_class_id" class="fw-bold small">Class <span class="text-danger">*</span></label>

        <select class="form-control form-control-sm" id="bp_class_id" name="class_id" form="bpBulkForm" required>

          <option value="">Select class</option>

          <?php foreach ($classes as $cls):

            $cid = (int) ($cls['class_id'] ?? 0);

            $cname = (string) ($cls['class_name'] ?? ('Class ' . $cid));

            $short = trim((string) ($cls['class_short_name'] ?? ''));

            $label = $short !== '' && $short !== $cname ? $cname . ' (' . $short . ')' : $cname;

            $sel = $prefClass === $cid ? 'selected' : '';

          ?>

            <option value="<?= $cid ?>" <?= $sel ?>><?= esc($label) ?></option>

          <?php endforeach; ?>

        </select>

      </div>

      <div class="form-group col-md-4 col-12 mb-2">

        <label for="subject_id" class="fw-bold small">Subject <span class="text-danger">*</span></label>

        <select class="form-control form-control-sm" id="subject_id" name="subject_id" form="bpBulkForm" required disabled>

          <option value="">Select class first</option>

          <?php if ($prefSub > 0): ?>

            <option value="<?= $prefSub ?>" selected>Loading…</option>

          <?php endif; ?>

        </select>

      </div>

    </div>

    <input type="hidden" id="bp_filter_class_id" value="<?= $prefClass > 0 ? $prefClass : 0 ?>">

    <input type="hidden" id="prep_grade_level" name="prep_grade_level" form="bpBulkForm" value="<?= esc(old('prep_grade_level', '')) ?>">

    <div id="bpFiltersHint" class="alert alert-warning py-2 small mb-0 d-none">

      Select board, class, and subject to load chapters.

    </div>

  </div>

</div>
