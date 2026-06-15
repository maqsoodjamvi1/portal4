<?php
/**
 * Accessible Bootstrap 5 modal shell.
 *
 * @var string       $id
 * @var string       $title
 * @var string       $bodyHtml
 * @var string|null  $footerHtml
 * @var string       $size         modal-sm|modal-lg|modal-xl|''
 * @var bool         $fullscreenMobile
 * @var string|null  $headerClass  e.g. bg-danger text-white
 */
$id = $id ?? 'smsModal';
$title = $title ?? '';
$bodyHtml = $bodyHtml ?? '';
$footerHtml = $footerHtml ?? null;
$size = $size ?? '';
$fullscreenMobile = !empty($fullscreenMobile);
$headerClass = $headerClass ?? '';
$labelId = $id . 'Label';
$modalClass = 'modal fade sms-modal' . ($fullscreenMobile ? ' sms-modal--fullscreen-sm' : '');
$dialogClass = 'modal-dialog' . ($size ? ' ' . esc($size, 'attr') : '');
?>
<div class="<?= esc($modalClass, 'attr') ?>" id="<?= esc($id, 'attr') ?>" tabindex="-1" role="dialog" aria-labelledby="<?= esc($labelId, 'attr') ?>" aria-hidden="true">
  <div class="<?= $dialogClass ?>" role="document">
    <div class="modal-content">
      <div class="modal-header <?= esc($headerClass, 'attr') ?>">
        <h5 class="modal-title" id="<?= esc($labelId, 'attr') ?>"><?= esc($title) ?></h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"><?= $bodyHtml ?></div>
      <?php if ($footerHtml !== null): ?>
        <div class="modal-footer"><?= $footerHtml ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>
