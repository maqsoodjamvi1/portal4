<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= esc($config['header']['title'] ?? 'Question Paper') ?> — Versions</title>
  <?= view('admin/question_paper/partials/print_styles') ?>
</head>
<body>
  <div class="no-print">
    <span>Multiple versions — each section shuffled differently</span>
    <button type="button" onclick="window.print()">Print all</button>
    <button type="button" onclick="window.close()">Close</button>
  </div>

  <?php foreach ($sets as $si => $set): ?>
    <?php if ($si > 0): ?><div class="qp-page-break"></div><?php endif; ?>
    <div class="qp-print-sheet qp-font-<?= esc($config['layout']['font_size'] ?? 'normal') ?>" style="margin-bottom:24px;">
      <div class="qp-cols-<?= (int) ($config['layout']['columns'] ?? 1) ?>">
        <?php $setConfig = $set['config'] ?? $config; ?>
        <?= view('admin/question_paper/partials/paper_header', ['config' => $setConfig]) ?>
        <div style="text-align:center;font-weight:700;margin-bottom:10px;"><?= esc($set['label'] ?? 'Version') ?></div>
        <div class="qp-questions-body">
          <?= view('admin/question_paper/partials/questions_block', [
              'questions' => $set['questions'] ?? [],
              'config' => $setConfig,
              'showAnswers' => false,
              'typeSections' => $set['typeSections'] ?? null,
          ]) ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</body>
</html>
