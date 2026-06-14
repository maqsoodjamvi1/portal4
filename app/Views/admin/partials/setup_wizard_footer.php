<?php
/**
 * Unified prev / next / finish footer for setup wizards.
 *
 * @var string      $hint
 * @var bool        $show_prev
 * @var bool        $show_next
 * @var bool        $show_finish
 * @var string      $prev_label
 * @var string      $next_label
 * @var string      $finish_label
 * @var string|null $prev_tab   fee mode: tab id for data attribute
 * @var string|null $next_tab
 */
$hint = $hint ?? 'Changes are saved when you click Next, Previous, or another step.';
$showPrev = ! empty($show_prev);
$showNext = ! empty($show_next);
$showFinish = ! empty($show_finish);
$prevLabel = $prev_label ?? 'Previous';
$nextLabel = $next_label ?? 'Next';
$finishLabel = $finish_label ?? 'Finish Setup';
$prevTab = $prev_tab ?? '';
$nextTab = $next_tab ?? '';
?>
<div class="setup-footer setup-wizard-footer">
  <?php if ($hint !== '') : ?>
  <p class="setup-hint mb-0"><?= $hint ?></p>
  <?php endif; ?>
  <div class="setup-footer-actions">
    <?php if ($showPrev) : ?>
    <button type="button" class="btn btn-outline-secondary setup-wizard-prev prev-step"<?= $prevTab !== '' ? ' data-wizard-tab="' . esc($prevTab, 'attr') . '"' : '' ?>>
      <i class="fas fa-arrow-left me-1"></i> <?= esc($prevLabel) ?>
    </button>
    <?php endif; ?>
    <?php if ($showNext) : ?>
    <button type="button" class="btn btn-primary setup-wizard-next next-step"<?= $nextTab !== '' ? ' data-wizard-tab="' . esc($nextTab, 'attr') . '"' : '' ?>>
      <?= esc($nextLabel) ?> <i class="fas fa-arrow-right ms-1"></i>
    </button>
    <?php endif; ?>
    <?php if ($showFinish) : ?>
    <button type="button" class="btn btn-success setup-wizard-finish" id="finishSetupBtn">
      <?= esc($finishLabel) ?> <i class="fas fa-check ms-1"></i>
    </button>
    <?php endif; ?>
  </div>
</div>
