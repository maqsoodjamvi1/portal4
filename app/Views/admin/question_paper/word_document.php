<?php
/**
 * MS Word–compatible HTML export (opens as editable .doc in Microsoft Word).
 *
 * @var array<string, mixed> $config
 * @var list<array<string, mixed>> $typeSections
 * @var bool $showAnswers
 * @var bool $includeAnswerKey
 */
$h = $config['header'] ?? [];
$layout = $config['layout'] ?? [];
$layout['mcq_inline'] = true;
$config['layout'] = $layout;
$title = (string) ($h['title'] ?? 'Question Paper');
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<title><?= esc($title) ?></title>
<!--[if gte mso 9]>
<xml>
  <w:WordDocument>
    <w:View>Print</w:View>
    <w:Zoom>100</w:Zoom>
    <w:DoNotOptimizeForBrowser/>
  </w:WordDocument>
</xml>
<![endif]-->
<?= view('admin/question_paper/partials/print_styles') ?>
<style>
body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; margin: 0; }
p { margin: 0; padding: 0; line-height: 115%; }
.qp-cols-2 .qp-questions-body { column-count: 1; }
.qp-question-card { margin: 0 0 4pt; padding: 0; }
.qp-q-head { margin: 0; padding: 0; line-height: 115%; }
.qp-mcq-inline-line { margin: 0; padding: 0; line-height: 115%; }
.qp-mcq-inline-line .qp-mcq-opt { display: inline; margin-right: 14pt; }
.qp-section-title { margin: 12pt 0 2pt; }
.qp-pair-card { margin-bottom: 12pt; }
.qp-q-head-mcq, .qp-q-head-mcq .qp-q-text, .qp-q-head-mcq .qp-q-num { font-weight: 700; }
</style>
</head>
<body class="qp-print-sheet qp-font-<?= esc($layout['font_size'] ?? 'normal') ?>">
  <div class="qp-cols-<?= (int) ($layout['columns'] ?? 1) ?>">
    <?= view('admin/question_paper/partials/paper_header', ['config' => $config]) ?>
    <div class="qp-questions-body">
      <?= view('admin/question_paper/partials/questions_block', [
          'questions'    => [],
          'config'       => $config,
          'showAnswers'  => $showAnswers,
          'typeSections' => $typeSections,
      ]) ?>
    </div>
  </div>
  <?php if ($includeAnswerKey && ($layout['paper_mode'] ?? '') === 'both'): ?>
    <p style="page-break-before:always;text-align:center;font-weight:bold;font-size:14pt;">Answer Key</p>
    <div class="qp-questions-body">
      <?= view('admin/question_paper/partials/questions_block', [
          'questions'    => [],
          'config'       => $config,
          'showAnswers'  => true,
          'typeSections' => $typeSections,
      ]) ?>
    </div>
  <?php endif; ?>
</body>
</html>
