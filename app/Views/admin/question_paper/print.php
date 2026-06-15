<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= esc($config['header']['title'] ?? 'Question Paper') ?></title>
  <?= view('admin/question_paper/partials/print_styles') ?>
</head>
<body class="qp-print-sheet qp-font-<?= esc($config['layout']['font_size'] ?? 'normal') ?>">
  <div class="no-print">
    <span>Question paper — print or save as PDF from browser</span>
    <button type="button" onclick="window.print()">Print</button>
    <button type="button" onclick="window.close()">Close</button>
  </div>

  <div class="qp-cols-<?= (int) ($config['layout']['columns'] ?? 1) ?>">
    <?= view('admin/question_paper/partials/paper_header', ['config' => $config]) ?>
    <div class="qp-questions-body">
      <?= view('admin/question_paper/partials/questions_block', [
          'questions'    => $questions,
          'config'       => $config,
          'showAnswers'  => false,
          'typeSections' => $typeSections ?? null,
      ]) ?>
    </div>
  </div>

  <?php if (($config['layout']['paper_mode'] ?? '') === 'both'): ?>
    <div class="qp-page-break"></div>
    <h2 style="text-align:center;margin:16px 0;">Answer Key</h2>
    <div class="qp-questions-body">
      <?= view('admin/question_paper/partials/questions_block', [
          'questions' => $questions,
          'config' => $config,
          'showAnswers' => true,
          'typeSections' => $typeSections ?? null,
      ]) ?>
    </div>
  <?php endif; ?>
</body>
</html>
