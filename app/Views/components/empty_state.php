<?php
/**
 * @var string|null $icon
 * @var string      $title
 * @var string|null $message
 * @var string|null $actionHtml
 */
$icon = $icon ?? 'fas fa-inbox';
$title = $title ?? 'No data';
$message = $message ?? null;
$actionHtml = $actionHtml ?? null;
?>
<div class="sms-empty-state" role="status">
  <div class="sms-empty-state__icon"><i class="<?= esc($icon, 'attr') ?>" aria-hidden="true"></i></div>
  <div class="sms-empty-state__title"><?= esc($title) ?></div>
  <?php if ($message): ?>
    <p class="mb-2"><?= esc($message) ?></p>
  <?php endif; ?>
  <?= $actionHtml ?? '' ?>
</div>
