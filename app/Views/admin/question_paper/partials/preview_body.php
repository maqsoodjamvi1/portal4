<?php
/** @var array $config @var list $questions @var bool $showAnswers @var list|null $typeSections */
$layout = $config['layout'] ?? [];
?>
<style>
.qp-preview-wrap .qp-mcq-list { list-style:none;padding:0;margin:6px 0 0; }
.qp-preview-wrap .qp-mcq-inline { display:flex;flex-wrap:wrap;gap:6px; }
.qp-preview-wrap .qp-mcq-opt { padding:4px 8px;border:1px solid #ccc;border-radius:4px; }
.qp-preview-wrap .qp-line { border-bottom:1px solid #333;height:1.4em;margin:4px 0; }
.qp-preview-wrap .qp-header-top { display:grid;grid-template-columns:80px 1fr 80px;align-items:center;margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #ccc; }
.qp-preview-wrap .qp-school-logo { max-height:56px;max-width:80px;display:block;margin:0;object-fit:contain; }
.qp-preview-wrap .qp-school-heading-row { text-align:center; }
.qp-preview-wrap .qp-school { font-size:1.55em;font-weight:700;margin:0;text-align:center; }
.qp-preview-wrap .qp-school-sub { font-size:0.95em;text-align:center; }
.qp-preview-wrap .qp-title { font-size:1.45em;font-weight:700;text-align:center;margin:6px 0; }
.qp-preview-wrap .qp-meta-primary { display:flex;justify-content:space-between;width:100%;font-size:1.02em;margin-top:6px; }
.qp-preview-wrap .qp-meta-primary .qp-meta-item:first-child { text-align:left; }
.qp-preview-wrap .qp-meta-primary .qp-meta-item:last-child { text-align:right; }
.qp-preview-wrap .qp-meta-primary .qp-meta-item { flex:1;text-align:center; }
.qp-preview-wrap .qp-section-title { text-align:center;font-weight:700;font-size:1.2em;margin:1rem 0 0.75rem;width:100%; }
.qp-preview-wrap .qp-section-choice-note { text-align:center;font-weight:600;font-style:italic;margin:-0.25rem 0 0.65rem; }
.qp-preview-wrap .qp-or-divider { text-align:center;font-weight:700;margin:0.35rem 0; }
.qp-preview-wrap .qp-or-divider span { display:inline-block;padding:0.1rem 1rem;border-top:1px solid #333;border-bottom:1px solid #333; }
.qp-preview-wrap .qp-pair-alt .qp-q-head { padding-left:1.25em; }
.qp-preview-wrap .qp-q-num { font-weight:700;margin-right:0.35em;text-transform:lowercase; }
.qp-preview-wrap .qp-q-marks { font-weight:600;font-size:0.92em;margin-left:0.2em; }
</style>
<div class="qp-preview-wrap qp-font-<?= esc($layout['font_size'] ?? 'normal') ?> qp-cols-<?= (int) ($layout['columns'] ?? 1) ?>">
  <?= view('admin/question_paper/partials/paper_header', ['config' => $config]) ?>
  <div class="qp-questions-body">
    <?= view('admin/question_paper/partials/questions_block', [
        'questions' => $questions,
        'config' => $config,
        'showAnswers' => $showAnswers,
        'typeSections' => $typeSections ?? null,
    ]) ?>
  </div>
</div>
