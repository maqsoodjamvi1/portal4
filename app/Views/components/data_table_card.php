<?php
/**
 * List card with toolbar and table slot.
 *
 * @var string       $title
 * @var string|null  $icon
 * @var string|null  $toolbarHtml  Card-tools HTML
 * @var string       $tableHtml      Table markup
 * @var string|null  $beforeTable    HTML above table (filters)
 * @var string|null  $footerHtml
 * @var string       $cardClass
 */
$title = $title ?? 'List';
$icon = $icon ?? 'fas fa-list';
$toolbarHtml = $toolbarHtml ?? null;
$tableHtml = $tableHtml ?? '';
$beforeTable = $beforeTable ?? null;
$footerHtml = $footerHtml ?? null;
$cardClass = $cardClass ?? 'card sms-card sms-index-card card-primary card-outline';
?>
<div class="<?= esc($cardClass, 'attr') ?>">
  <div class="card-header">
    <h3 class="card-title">
      <?php if ($icon): ?><i class="<?= esc($icon, 'attr') ?>"></i><?php endif; ?>
      <?= esc($title) ?>
    </h3>
    <?php if ($toolbarHtml): ?>
      <div class="card-tools"><?= $toolbarHtml ?></div>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <?= $beforeTable ?? '' ?>
    <div class="table-responsive">
      <?= $tableHtml ?>
    </div>
    <?= $footerHtml ?? '' ?>
  </div>
</div>
