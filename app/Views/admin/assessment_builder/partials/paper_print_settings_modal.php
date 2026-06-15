<?php
/** Print layout modal — inside #paperSaveForm (field names match question-paper print endpoints) */
$typeKeys = ['mcq', 'mcq_multi', 'tf', 'fill', 'short', 'descriptive', 'match'];
?>
<div class="modal fade" id="paperPrintSettingsModal" tabindex="-1" role="dialog" aria-labelledby="paperPrintSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="paperPrintSettingsModalLabel">
          <i class="fas fa-print me-1"></i> Print settings
        </h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Paper title comes from the field above. These options apply when the paper opens for printing.</p>

        <h6 class="fw-bold text-uppercase text-muted small mb-2">Exam header</h6>
        <div class="row">
          <div class="form-group col-md-4">
            <label for="exam_date">Date</label>
            <input type="text" class="form-control form-control-sm" id="exam_date" name="exam_date" placeholder="e.g. 15 Jun 2026">
          </div>
          <div class="form-group col-md-4">
            <label for="exam_time">Time</label>
            <input type="text" class="form-control form-control-sm" id="exam_time" name="exam_time" placeholder="e.g. 9:00 AM">
          </div>
          <div class="form-group col-md-4">
            <label for="duration">Duration</label>
            <input type="text" class="form-control form-control-sm" id="duration" name="duration" placeholder="e.g. 2 hours">
          </div>
          <div class="form-group col-12 mb-2">
            <label for="instructions">Instructions</label>
            <textarea class="form-control form-control-sm" id="instructions" name="instructions" rows="2"
                      placeholder="General instructions for students"></textarea>
          </div>
        </div>

        <div class="row mb-2">
          <div class="col-md-6">
            <label class="d-block small fw-bold mb-1">Student fields on paper</label>
            <label class="me-3 small"><input type="checkbox" id="show_name" name="show_name" value="1" checked> Name</label>
            <label class="me-3 small"><input type="checkbox" id="show_roll" name="show_roll" value="1" checked> Roll no</label>
            <label class="me-3 small"><input type="checkbox" id="show_section" name="show_section" value="1"> Section</label>
          </div>
          <div class="form-group col-md-6 mb-0">
            <label for="paper_mode">Output</label>
            <select class="form-control form-control-sm" id="paper_mode" name="paper_mode">
              <option value="student" selected>Student paper (no answers)</option>
              <option value="key">Answer key only</option>
              <option value="both">Student paper + answer key</option>
            </select>
          </div>
        </div>

        <hr>

        <h6 class="fw-bold text-uppercase text-muted small mb-2">Layout</h6>
        <div class="row">
          <div class="form-group col-md-4">
            <label for="columns">Columns</label>
            <select class="form-control form-control-sm" id="columns" name="columns">
              <option value="1">1 column</option>
              <option value="2" selected>2 columns</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label for="font_size">Font size</label>
            <select class="form-control form-control-sm" id="font_size" name="font_size">
              <option value="small">Small</option>
              <option value="normal" selected>Normal</option>
              <option value="large">Large</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label for="versions">Shuffled versions</label>
            <input type="number" class="form-control form-control-sm" id="versions" name="versions" min="1" max="4" value="1">
          </div>
        </div>

        <div class="mb-2">
          <?php
          $checks = [
              'show_topics'               => 'Show topic headings',
              'mcq_inline'                => 'MCQ options inline',
              'page_break_topic'          => 'Page break before each section',
              'shuffle_questions'         => 'Shuffle questions',
              'shuffle_mcq_options'       => 'Shuffle MCQ options',
              'show_question_marks'       => 'Show marks on each question',
              'descriptive_answer_space'  => 'Blank lines for descriptive answers',
              'group_by_topic'            => 'Group by topic',
          ];
          foreach ($checks as $key => $lbl):
            $defaultOn = in_array($key, ['show_question_marks'], true);
          ?>
            <label class="me-3 small mb-1 d-inline-block">
              <input type="checkbox" id="<?= esc($key) ?>" name="<?= esc($key) ?>" value="1" <?= $defaultOn ? 'checked' : '' ?>>
              <?= esc($lbl) ?>
            </label>
          <?php endforeach; ?>
        </div>

        <input type="hidden" id="descriptive_lines" name="descriptive_lines" value="4">
        <?php foreach ($typeKeys as $k): ?>
          <input type="hidden" name="marks_<?= esc($k) ?>" id="ps_marks_<?= esc($k) ?>" value="0">
        <?php endforeach; ?>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary" id="btnPaperPrintSettingsDone" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>
