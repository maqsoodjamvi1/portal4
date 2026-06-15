<?php /** Assessment Builder — topics + question selection */ ?>

<div class="card card-primary mb-3" id="abChooseQuestions">

  <div class="card-header py-2">

    <h3 class="card-title mb-0">1. Topics &amp; questions</h3>

  </div>

  <div class="card-body py-3">

    <?= view('admin/assessment_builder/partials/qb_browser', ['boardPublishers' => $boardPublishers ?? []]) ?>



    <input type="hidden" id="selection_mode" value="auto">



    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-2">

      <div class="btn-group btn-group-sm">

        <button type="button" class="btn btn-outline-primary qp-selection-tab active" data-mode="auto">Auto</button>

        <button type="button" class="btn btn-outline-primary qp-selection-tab" data-mode="manual">Manual</button>

        <button type="button" class="btn btn-outline-primary qp-selection-tab" data-mode="all">All</button>

      </div>

    </div>



    <div id="qbSelectedPanel" class="ab-selected-topics mb-2 d-none">

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">

        <span class="small fw-bold text-primary"><span id="qbSelectedCount">0</span> topics selected</span>

        <button type="button" class="btn btn-sm btn-outline-danger" id="qbClearTopics">Clear all</button>

      </div>

      <div id="qbSelectedChips" class="qb-selected-chips"></div>

    </div>



    <div id="qpAutoPanel">

      <?php

      $countFields = [

          'mcq' => 'MCQ', 'mcq_multi' => 'Multi', 'tf' => 'T/F', 'fill' => 'Fill',

          'short' => 'Short', 'descriptive' => 'Desc', 'match' => 'Match',

      ];

      ?>

      <div class="qp-type-table-wrap">

        <table class="table table-sm table-bordered qp-type-table mb-0">

          <thead>

            <tr>

              <th class="qp-type-table-axis"></th>

              <?php foreach ($countFields as $k => $lbl): ?>

                <th class="qp-type-col" data-type="<?= esc($k) ?>"><?= esc($lbl) ?></th>

              <?php endforeach; ?>

              <th class="qp-type-total-col">Total</th>

            </tr>

          </thead>

          <tbody>

            <tr class="qp-bank-row">

              <th scope="row">In bank</th>

              <?php foreach ($countFields as $k => $lbl): ?>

                <td class="qp-type-col qp-bank-cell" data-type="<?= esc($k) ?>">

                  <span class="qp-bank-badge qp-bank-badge--empty" id="bank_count_<?= $k ?>">—</span>

                </td>

              <?php endforeach; ?>

              <td class="qp-total-cell qp-bank-total-cell">

                <span class="qp-bank-badge qp-bank-badge--empty" id="bank_count_total">—</span>

              </td>

            </tr>

            <tr class="qp-pick-row">

              <th scope="row">Pick</th>

              <?php foreach ($countFields as $k => $lbl): ?>

                <td class="qp-type-col qp-count-field" data-type="<?= esc($k) ?>">

                  <input type="number" class="form-control form-control-sm qp-type-count text-center"

                         id="count_<?= $k ?>" min="0" max="99" value="0" inputmode="numeric" disabled

                         aria-label="Pick <?= esc($lbl) ?>">

                </td>

              <?php endforeach; ?>

              <td class="qp-total-cell">

                <strong id="pick_count_total" class="qp-pick-total">0</strong>

              </td>

            </tr>

            <tr class="qp-marks-row">

              <th scope="row">Marks</th>

              <?php foreach ($countFields as $k => $lbl): ?>

                <td class="qp-type-col qp-marks-cell" data-type="<?= esc($k) ?>">

                  <input type="number" class="form-control form-control-sm qp-marks-input text-center"

                         id="marks_<?= $k ?>" min="0" max="999" value="0" inputmode="numeric" disabled

                         aria-label="<?= esc($lbl) ?> marks">

                </td>

              <?php endforeach; ?>

              <td class="qp-total-cell">

                <strong id="marks_count_total" class="qp-marks-total">0</strong>

              </td>

            </tr>

            <tr id="qpTypeTableEmpty" class="d-none">
              <th class="qp-type-table-axis"></th>
              <td colspan="7" class="text-muted small text-center py-3" id="qpTypeTableEmptyMsg">Select topics to see question types.</td>
              <td class="qp-total-cell">—</td>
            </tr>

          </tbody>

        </table>

      </div>

      <div class="small text-muted mt-1" id="qpSectionSummary">0 sections</div>



      <div id="qpDescChoicePanel" class="qp-desc-choice-panel border rounded p-3 mt-3 d-none">

        <div class="row align-items-end">

          <div class="form-group col-md-3 col-sm-6 mb-2">

            <label for="descriptive_choice_mode" class="small fw-bold mb-1">Descriptive choice</label>

            <select class="form-control form-control-sm" id="descriptive_choice_mode">

              <option value="none">None</option>

              <option value="attempt_any">Attempt any N</option>

              <option value="pairs">OR pairs</option>

            </select>

          </div>

          <div class="form-group col-md-2 col-sm-4 mb-2" id="desc_attempt_any_wrap">

            <label for="descriptive_attempt_any_count" class="small fw-bold mb-1">Attempt</label>

            <input type="number" class="form-control form-control-sm" id="descriptive_attempt_any_count" min="1" max="99" value="6">

          </div>

        </div>

        <div id="desc_pairs_wrap" class="d-none">

          <div id="desc_pairs_list" class="qp-desc-pairs-grid"></div>

          <div class="qp-desc-pairs-actions mt-2">

            <button type="button" class="btn btn-sm btn-outline-primary" id="btnDescAddPair"><i class="fas fa-plus"></i> Pair</button>

            <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="btnDescAutoPair">Auto-pair</button>

          </div>

        </div>

        <input type="hidden" id="descriptive_pairs_json" value="[]">

      </div>

    </div>



    <div id="qpManualPanel" style="display:none;">

      <div class="d-flex flex-wrap gap-2 mb-2">

        <input type="search" class="form-control form-control-sm flex-grow-1" id="qpManualSearch" placeholder="Search…" style="min-width:12rem;">

        <button type="button" class="btn btn-sm btn-outline-secondary" id="qpManualSelectAll">All</button>

        <button type="button" class="btn btn-sm btn-outline-secondary" id="qpManualClear">Clear</button>

      </div>

      <div id="qpManualSelectionSummary" class="small mb-1 text-muted">0 selected</div>

      <div id="qpManualList" class="border rounded" style="max-height:280px;overflow-y:auto;"></div>

      <label class="mt-1 small mb-0"><input type="checkbox" id="fixed_questions" value="1"> Lock set</label>

    </div>

  </div>

</div>
