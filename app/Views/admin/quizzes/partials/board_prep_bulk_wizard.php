<?php

/** Steps 2–4: chapters, grouping, question counts, preview */

?>

<div id="bpBulkWizard" class="bp-bulk-locked">

  <div class="card card-outline card-info mb-3" id="bpChaptersCard">

    <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between">

      <h3 class="card-title mb-0">2. Chapters &amp; grouping</h3>

      <div class="btn-group btn-group-sm mt-1 mt-md-0" id="bpChapterSelectBtns">

        <button type="button" class="btn btn-outline-secondary" id="bpSelectAllChapters">All</button>

        <button type="button" class="btn btn-outline-secondary" id="bpSelectNoneChapters">None</button>

      </div>

    </div>

    <div class="card-body py-3">

      <p class="text-muted small mb-3">

        Each chapter (topic) can become its own quiz, or combine chapters in pairs — e.g. Ch 1+2, Ch 3+4.

      </p>



      <div class="row mb-3">

        <div class="form-group col-12 mb-2">

          <label class="fw-bold small d-block mb-1">How should quizzes be grouped?</label>

          <div class="btn-group btn-group-toggle flex-wrap" data-bs-toggle="buttons" id="bpGroupingMode">

            <label class="btn btn-outline-primary btn-sm active">

              <input type="radio" name="grouping_mode" value="per_chapter" autocomplete="off" checked> One quiz per chapter

            </label>

            <label class="btn btn-outline-primary btn-sm">

              <input type="radio" name="grouping_mode" value="pairs" autocomplete="off"> Pairs (1+2, 3+4…)

            </label>

            <label class="btn btn-outline-primary btn-sm">

              <input type="radio" name="grouping_mode" value="all_one" autocomplete="off"> All chapters → one quiz

            </label>

          </div>

        </div>

      </div>



      <div id="bpChaptersLoading" class="text-center text-muted py-4 d-none">

        <i class="fas fa-spinner fa-spin me-1"></i> Loading chapters…

      </div>

      <div id="bpChaptersEmpty" class="alert alert-warning py-2 small mb-0 d-none">

        No chapters found for this board, grade, and subject. Add topics under Question Bank → Topics.

      </div>



      <div class="table-responsive" id="bpChaptersTableWrap">

        <table class="table table-sm table-hover table-striped mb-0" id="bpChaptersTable">

          <thead class="table-light" id="bpChaptersHead"></thead>

          <tbody id="bpChaptersBody"></tbody>

        </table>

      </div>

    </div>

  </div>



  <div class="card card-outline card-info mb-3" id="bpCountsCard">

    <div class="card-header py-2">

      <h3 class="card-title mb-0">3. Questions per quiz <span class="text-muted fw-normal small">(same for every quiz)</span></h3>

    </div>

    <div class="card-body py-3">

      <p class="text-muted small mb-3">

        Set how many questions of each type every quiz should contain. &ldquo;Min available&rdquo; shows the tightest limit across all quiz groups you are about to create.

      </p>

      <div class="table-responsive">

        <table class="table table-sm table-bordered mb-0 bp-type-counts-table" id="bpTypeCountsTable">

          <thead class="table-light" id="bpTypeCountsHead"></thead>

          <tbody id="bpTypeCountsBody"></tbody>

        </table>

      </div>

      <p id="bpTypeCountsEmpty" class="text-muted small mb-0 d-none">No question types available for the selected chapters.</p>

    </div>

  </div>



  <div class="card card-outline card-success mb-3" id="bpPreviewCard">

    <div class="card-header py-2">

      <h3 class="card-title mb-0">4. Preview &amp; create</h3>

    </div>

    <div class="card-body py-3">

      <div class="form-group mb-3">

        <label for="bp_title_pattern" class="fw-bold small">Quiz title pattern</label>

        <input type="text" class="form-control form-control-sm" id="bp_title_pattern" name="title_pattern"

               form="bpBulkForm" value="{subject} – {chapters}" maxlength="200">

        <small class="form-text text-muted">

          Placeholders: <code>{subject}</code>, <code>{chapters}</code> (combined chapter names), <code>{chapter}</code> (first chapter only).

        </small>

      </div>



      <div id="bpPreviewSummary" class="alert alert-secondary py-2 small mb-3">

        Select board, grade, subject, and chapters to see a preview.

      </div>



      <div class="table-responsive mb-0" id="bpPreviewTableWrap">

        <table class="table table-sm table-bordered mb-0" id="bpPreviewTable">

          <thead class="table-light">

            <tr>

              <th style="width:2.5rem">#</th>

              <th>Quiz title</th>

              <th>Chapters</th>

              <th class="text-center" style="width:5rem">Questions</th>

              <th style="width:8rem">Status</th>

            </tr>

          </thead>

          <tbody id="bpPreviewBody"></tbody>

        </table>

      </div>

    </div>

  </div>

</div>
