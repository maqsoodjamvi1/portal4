<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Question Paper Generator',
    'icon' => 'fas fa-file-alt',
    'actionsHtml' => '<div class="text-sm-right">'
        . '<a href="' . esc(site_url('admin/quizzes'), 'attr') . '" class="btn btn-outline-secondary btn-sm">'
        . '<i class="fas fa-gamepad"></i> Quizzes (student)</a></div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Question Paper', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card sms-card card-outline card-success mb-3">
        <div class="card-body py-2 px-3">
          <div class="qp-templates-bar d-flex flex-wrap align-items-center gap-2">
            <span class="small fw-bold text-nowrap mb-0"><i class="fas fa-bookmark text-success"></i> Saved templates</span>
            <div id="qpTemplateList" class="qp-template-chips d-flex flex-wrap align-items-center gap-1 flex-grow-1 min-width-0">
              <span class="text-muted small">Loading…</span>
            </div>
            <input type="hidden" id="template_id" value="">
            <input type="text" class="form-control form-control-sm qp-template-name-input" id="template_name" placeholder="Template name to save">
            <button type="button" class="btn btn-success btn-sm text-nowrap" id="btnSaveTemplate">
              <i class="fas fa-save"></i> Save
            </button>
          </div>
        </div>
      </div>
      <div class="card card-primary">
        <div class="card-header"><h3 class="card-title mb-0">1. Select topics</h3></div>
        <div class="card-body">
          <p class="text-muted small mb-2">Choose class → subject → topics. Only questions from selected topics are used.</p>
          <div id="qpTypeCounts" class="alert alert-light py-2 small mb-3">Select topics to see available counts.</div>

          <div class="row mb-3">
            <div class="col-12">
              <label class="d-block small fw-bold mb-1">Question types</label>
              <?php
              $types = [
                  'mcq' => 'MCQ (single)', 'mcq_multi' => 'MCQ (multi)', 'tf' => 'True / False',
                  'fill' => 'Fill blank', 'short' => 'Short', 'descriptive' => 'Descriptive', 'match' => 'Match',
              ];
              foreach ($types as $val => $lbl): ?>
                <label class="me-3"><input type="checkbox" class="qp-type-check" value="<?= esc($val) ?>" checked> <?= esc($lbl) ?></label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-12">
              <label class="d-block small fw-bold mb-1">Difficulty (optional)</label>
              <?php foreach (['easy', 'normal', 'hard'] as $d): ?>
                <label class="me-3"><input type="checkbox" class="qp-diff-check" value="<?= $d ?>"> <?= ucfirst($d) ?></label>
              <?php endforeach; ?>
              <span class="text-muted small">Leave unchecked = all difficulties</span>
            </div>
          </div>

          <?= view('admin/question_paper/partials/qb_browser', ['boardPublishers' => $boardPublishers ?? []]) ?>
        </div>
      </div>

      <div class="card card-info">
        <div class="card-header"><h3 class="card-title mb-0">2. Choose questions</h3></div>
        <div class="card-body">
          <input type="hidden" id="selection_mode" value="auto">
          <div class="btn-group btn-group-sm mb-3">
            <button type="button" class="btn btn-outline-primary qp-selection-tab active" data-mode="auto">Auto by counts</button>
            <button type="button" class="btn btn-outline-primary qp-selection-tab" data-mode="manual">Pick manually</button>
            <button type="button" class="btn btn-outline-primary qp-selection-tab" data-mode="all">All in pool</button>
          </div>

          <div id="qpAutoPanel">
            <label class="d-block small fw-bold mb-1">Sections — count &amp; marks by question type</label>
            <p class="text-muted small mb-2 mb-md-1">Each type with a non-zero count becomes a section (A, B, C…). Set section marks; per-question marks are calculated automatically.</p>
            <div class="qp-count-row">
              <?php
              $countFields = [
                  'mcq' => 'MCQ', 'mcq_multi' => 'Multi', 'tf' => 'T/F', 'fill' => 'Fill',
                  'short' => 'Short', 'descriptive' => 'Desc', 'match' => 'Match',
              ];
              foreach ($countFields as $k => $lbl): ?>
                <div class="qp-count-field" data-type="<?= esc($k) ?>">
                  <label class="qp-count-label" for="count_<?= $k ?>"><?= esc($lbl) ?></label>
                  <input type="number" class="form-control qp-count-input qp-type-count" id="count_<?= $k ?>" min="0" max="99" value="0" inputmode="numeric" title="<?= esc($lbl) ?> count">
                  <label class="qp-marks-label" for="marks_<?= $k ?>">Marks</label>
                  <input type="number" class="form-control qp-marks-input" id="marks_<?= $k ?>" min="0" max="999" value="0" inputmode="numeric" disabled title="Section marks for <?= esc($lbl) ?>">
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mt-2 small text-muted">
              <span id="qpSectionSummary">0 sections</span>
              <span class="mx-2">·</span>
              <span>Total marks: <strong id="qpTotalMarksDisplay">0</strong></span>
            </div>
            <div id="qpDescChoicePanel" class="qp-desc-choice-panel border rounded p-3 mt-3 d-none">
              <label class="d-block small fw-bold mb-2">Descriptive section — student choice</label>
              <div class="row align-items-end">
                <div class="form-group col-md-4 mb-2">
                  <label for="descriptive_choice_mode" class="small mb-1">Choice type</label>
                  <select class="form-control form-control-sm" id="descriptive_choice_mode">
                    <option value="none">No choice instruction</option>
                    <option value="attempt_any">Attempt any N questions</option>
                    <option value="pairs">OR pairs (one per pair)</option>
                  </select>
                </div>
                <div class="form-group col-md-4 mb-2" id="desc_attempt_any_wrap">
                  <label for="descriptive_attempt_any_count" class="small mb-1">How many to attempt</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">Any</span>
                    <input type="number" class="form-control" id="descriptive_attempt_any_count" min="1" max="99" value="6">
                    <span class="input-group-text">questions</span>
                  </div>
                </div>
              </div>
              <div id="desc_pairs_wrap" class="d-none">
                <p class="small text-muted mb-2">Each pair shows two questions with <strong>OR</strong> between them; students attempt one from each pair. Use question numbers 1…N in the descriptive section order.</p>
                <div id="desc_pairs_list" class="mb-2"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnDescAddPair"><i class="fas fa-plus"></i> Add pair</button>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="btnDescAutoPair">Auto-pair 1–2, 3–4…</button>
              </div>
              <input type="hidden" id="descriptive_pairs_json" value="[]">
            </div>
          </div>

          <div id="qpManualPanel" style="display:none;">
            <div class="d-flex gap-2 mb-2">
              <input type="search" class="form-control form-control-sm" id="qpManualSearch" placeholder="Search questions…">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="qpManualSelectAll">All</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="qpManualClear">Clear</button>
            </div>
            <div id="qpManualSelectionSummary" class="small mb-2 text-muted">No questions selected.</div>
            <div id="qpManualList" class="border rounded" style="max-height:320px;overflow-y:auto;"></div>
            <label class="mt-2 small"><input type="checkbox" id="fixed_questions" value="1"> Lock question set when saving template</label>
          </div>
        </div>
      </div>

      <div class="card card-secondary">
        <div class="card-header"><h3 class="card-title mb-0">3. Paper layout &amp; header</h3></div>
        <div class="card-body">
          <p class="text-muted small mb-2">School name and logo on the printed paper come from your system settings (not editable here).</p>
          <div class="row">
            <div class="form-group col-md-12">
              <label for="paper_title">Paper title</label>
              <input type="text" class="form-control" id="paper_title" placeholder="e.g. Mid Term Examination 2026">
            </div>
            <div class="form-group col-md-4">
              <label for="paper_subject">Subject</label>
              <input type="text" class="form-control" id="paper_subject">
            </div>
            <div class="form-group col-md-4">
              <label for="paper_class">Class</label>
              <input type="text" class="form-control" id="paper_class">
            </div>
            <div class="form-group col-md-4">
              <label>Total marks</label>
              <div class="form-control-plaintext border rounded px-2 py-1 bg-light" id="total_marks_display">0</div>
              <small class="text-muted">Sum of section marks from step 2</small>
            </div>
            <div class="form-group col-md-4">
              <label for="exam_date">Date</label>
              <input type="text" class="form-control" id="exam_date" placeholder="e.g. 15 June 2026">
            </div>
            <div class="form-group col-md-4">
              <label for="exam_time">Time</label>
              <input type="text" class="form-control" id="exam_time">
            </div>
            <div class="form-group col-md-4">
              <label for="duration">Duration</label>
              <input type="text" class="form-control" id="duration" placeholder="e.g. 1 hour 30 min">
            </div>
            <div class="form-group col-12">
              <label for="instructions">Instructions</label>
              <textarea class="form-control" id="instructions" rows="2" placeholder="Read all questions carefully…"></textarea>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <label class="d-block small fw-bold">Student fields on paper</label>
              <label class="me-3"><input type="checkbox" id="show_name" checked> Name</label>
              <label class="me-3"><input type="checkbox" id="show_roll" checked> Roll no</label>
              <label class="me-3"><input type="checkbox" id="show_section"> Section</label>
            </div>
            <div class="col-md-6">
              <label for="paper_mode">Output</label>
              <select class="form-control" id="paper_mode">
                <option value="student">Student paper (no answers)</option>
                <option value="key">Answer key only</option>
                <option value="both">Student paper + answer key</option>
              </select>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="form-group col-md-3">
              <label for="columns">Columns</label>
              <select class="form-control" id="columns">
                <option value="1">1 column</option>
                <option value="2">2 columns</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label for="font_size">Font size</label>
              <select class="form-control" id="font_size">
                <option value="small">Small</option>
                <option value="normal" selected>Normal</option>
                <option value="large">Large</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label class="d-block">Descriptive / long-answer space</label>
              <label class="mb-2">
                <input type="checkbox" id="descriptive_answer_space">
                Include blank lines on question paper
              </label>
              <div id="descriptive_lines_wrap" class="d-none">
                <label for="descriptive_lines" class="small text-muted mb-0">Number of lines per question</label>
                <input type="number" class="form-control form-control-sm" id="descriptive_lines" min="1" max="12" value="6" disabled>
              </div>
              <small class="form-text text-muted">Leave unchecked when students write descriptive answers on a separate answer sheet (question text only).</small>
            </div>
            <div class="form-group col-md-3">
              <label for="versions">Shuffled versions</label>
              <input type="number" class="form-control" id="versions" min="1" max="4" value="1">
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <label class="me-3"><input type="checkbox" id="show_topics" checked> Show topic headings</label>
              <label class="me-3"><input type="checkbox" id="mcq_inline" checked> MCQ options inline</label>
              <label class="me-3"><input type="checkbox" id="page_break_topic"> Page break before each section</label>
              <label class="me-3"><input type="checkbox" id="shuffle_questions"> Shuffle questions</label>
              <label class="me-3"><input type="checkbox" id="shuffle_mcq_options"> Shuffle MCQ options</label>
              <label class="me-3"><input type="checkbox" id="show_question_marks"> Show marks on each question</label>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-info" id="btnPreview"><i class="fas fa-file-alt"></i> Generate Paper</button>
          <button type="button" class="btn btn-primary" id="btnPrint"><i class="fas fa-print"></i> Print paper</button>
          <button type="button" class="btn btn-outline-primary" id="btnPrintKey"><i class="fas fa-key"></i> Print answer key</button>
          <button type="button" class="btn btn-outline-secondary" id="btnPrintVersions"><i class="fas fa-copy"></i> Print versions</button>
          <button type="button" class="btn btn-success" id="btnDownloadWord" title="Editable Word file (.docx or .doc)"><i class="fas fa-file-word"></i> Download Word</button>
          <button type="button" class="btn btn-outline-success" id="btnDownloadWordKey"><i class="fas fa-file-word"></i> Word answer key</button>
          <span id="qpPreviewMeta" class="align-self-center text-muted small ms-2"></span>
        </div>
      </div>

      <div id="qpPreviewArea" class="card d-none">
        <div class="card-header"><h3 class="card-title mb-0">Generated paper</h3></div>
        <div class="card-body" style="background:#f4f6f9;"></div>
      </div>
    </div>
  </div>
</section>

<style>
  .qb-browser-row { min-height: 360px; }
  .qb-browser-col { display: flex; flex-direction: column; min-height: 360px; }
  .qb-col-header {
    padding: 0.5rem 0.75rem; font-weight: 600; font-size: 0.85rem;
    background: #f8f9fa; border-bottom: 1px solid #dee2e6;
    display: flex; justify-content: space-between; align-items: center;
  }
  .qb-scroll-list { flex: 1; overflow-y: auto; max-height: 360px; min-height: 280px; }
  .qb-list-item {
    display: block; width: 100%; text-align: left; border: none; border-bottom: 1px solid #f1f3f5;
    background: #fff; padding: 0.55rem 0.75rem; font-size: 0.9rem; cursor: pointer;
  }
  .qb-list-item:hover { background: #f8f9fa; }
  .qb-list-item.active { background: #e7f1ff; border-start: 3px solid #007bff; font-weight: 600; }
  .qb-topic-row {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.45rem 0.75rem;
    border-bottom: 1px solid #f1f3f5; cursor: pointer; font-size: 0.9rem; margin: 0;
  }
  .qb-topic-row.selected { background: #e7f1ff; }
  .qp-preview-wrap { background: #fff; padding: 1rem; border: 1px solid #dee2e6; border-radius: 0.35rem; }
  .qp-preview-wrap .qp-paper-header { text-align: center; border: 2px solid #000; padding: 10px; margin-bottom: 12px; }
  .qp-preview-wrap .qp-header-top { display: grid; grid-template-columns: 80px 1fr 80px; align-items: center; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #ccc; }
  .qp-preview-wrap .qp-school-logo { max-height: 56px; max-width: 80px; display: block; margin: 0; object-fit: contain; }
  .qp-preview-wrap .qp-school-heading-row { text-align: center; }
  .qp-preview-wrap .qp-school { font-size: 1.55em; font-weight: 700; margin: 0; text-align: center; }
  .qp-preview-wrap .qp-school-sub { font-size: 0.95em; text-align: center; }
  .qp-preview-wrap .qp-title { font-size: 1.45em; font-weight: 700; text-align: center; }
  .qp-preview-wrap .qp-meta-primary { display: flex; justify-content: space-between; width: 100%; }
  .qp-preview-wrap .qp-q-marks { margin-left: 0.2em; }
  .qp-preview-wrap .qp-q-num { margin-right: 6px; }
  .qp-count-row {
    display: flex;
    flex-wrap: nowrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 0.5rem;
    padding-bottom: 4px;
  }
  .qp-count-field {
    flex: 1 1 0;
    min-width: 4.5rem;
    max-width: 6.5rem;
    text-align: center;
  }
  .qp-count-field.qp-marks-inactive .qp-marks-wrap { opacity: 0.35; }
  .qp-count-label, .qp-marks-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 600;
    margin-bottom: 2px;
    white-space: nowrap;
    color: #495057;
  }
  .qp-count-input, .qp-marks-input {
    width: 100%;
    max-width: 4.25rem;
    margin: 0 auto 4px;
    padding: 0.25rem 0.2rem;
    text-align: center;
    font-size: 0.9rem;
    height: calc(1.5em + 0.45rem + 2px);
  }
  .qp-marks-input { margin-bottom: 0; }
  .qp-count-input::-webkit-outer-spin-button,
  .qp-count-input::-webkit-inner-spin-button,
  .qp-marks-input::-webkit-outer-spin-button,
  .qp-marks-input::-webkit-inner-spin-button { margin: 0; }
  .qp-templates-bar { min-height: 2rem; }
  .qp-template-name-input { width: 100%; max-width: 220px; min-width: 140px; }
  .qp-template-chips .qp-tpl-chip {
    display: inline-flex;
    align-items: stretch;
    border-radius: 0.2rem;
    overflow: hidden;
    border: 1px solid #ced4da;
    background: #fff;
  }
  .qp-template-chips .qp-tpl-chip .btn { border-radius: 0; padding: 0.15rem 0.45rem; font-size: 0.8rem; line-height: 1.3; }
  .qp-template-chips .qp-tpl-chip .qp-load-tpl { border: none; border-end: 1px solid #dee2e6; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .qp-template-chips .qp-tpl-chip .qp-del-tpl { border: none; padding-left: 0.35rem; padding-right: 0.35rem; }
  .qp-desc-choice-panel { background: #f8fafc; }
  .qp-desc-pair-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem 0.5rem;
    margin-bottom: 0.5rem;
    padding: 0.35rem 0.5rem;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
  }
  .qp-desc-pair-row .qp-pair-or-label { font-weight: 700; color: #495057; }
  .qp-preview-wrap .qp-section-choice-note { text-align: center; font-weight: 600; font-style: italic; margin-bottom: 0.75rem; }
  .qp-preview-wrap .qp-or-divider { text-align: center; font-weight: 700; margin: 0.35rem 0; }
</style>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
window.QP_CONFIG = {
  csrfName: <?= json_encode(csrf_token()) ?>,
  csrfHash: <?= json_encode(csrf_hash()) ?>,
  summaryUrl: <?= json_encode(base_url('admin/question-paper/ajax/qb-summary')) ?>,
  summaryFallbackUrl: <?= json_encode(base_url('admin/quizzes/ajax/qb-summary')) ?>,
  questionsUrl: <?= json_encode(base_url('admin/question-paper/ajax/qb-questions')) ?>,
  previewUrl: <?= json_encode(base_url('admin/question-paper/preview')) ?>,
  printUrl: <?= json_encode(base_url('admin/question-paper/print')) ?>,
  printKeyUrl: <?= json_encode(base_url('admin/question-paper/print-key')) ?>,
  printVersionsUrl: <?= json_encode(base_url('admin/question-paper/print-versions')) ?>,
  downloadWordUrl: <?= json_encode(base_url('admin/question-paper/download-word')) ?>,
  downloadWordKeyUrl: <?= json_encode(base_url('admin/question-paper/download-word-key')) ?>,
  templatesUrl: <?= json_encode(base_url('admin/question-paper/templates')) ?>,
  saveTemplateUrl: <?= json_encode(base_url('admin/question-paper/templates/save')) ?>,
  loadTemplateUrl: <?= json_encode(base_url('admin/question-paper/templates')) ?>,
  deleteTemplateUrl: <?= json_encode(base_url('admin/question-paper/templates/delete')) ?>,
};
</script>
<script src="<?= base_url('assets/js/question_paper_qb_browser.js') ?>?v=4"></script>
<script src="<?= base_url('assets/js/question_paper_builder.js') ?>?v=11"></script>
<?= $this->endSection() ?>
