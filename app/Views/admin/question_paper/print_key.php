<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= esc($config['header']['title'] ?? 'Answer Key') ?> — Key</title>
  <?= view('admin/question_paper/partials/print_styles') ?>
</head>
<body class="qp-print-sheet qp-font-<?= esc($config['layout']['font_size'] ?? 'normal') ?>">
  <div class="no-print">
    <span>Answer key</span>
    <button type="button" onclick="window.print()">Print</button>
    <button type="button" onclick="window.close()">Close</button>
  </div>

  <div class="qp-cols-<?= (int) ($config['layout']['columns'] ?? 1) ?>">
    <?= view('admin/question_paper/partials/paper_header', ['config' => $config]) ?>
    <h2 style="text-align:center;font-size:1.1em;margin:0 0 12px;">Answer Key</h2>
    <div class="qp-questions-body">
      <?= view('admin/question_paper/partials/questions_block', [
          'questions' => $questions,
          'config' => $config,
          'showAnswers' => true,
          'typeSections' => $typeSections ?? null,
      ]) ?>
    </div>
  </div>
</body>
</html>
