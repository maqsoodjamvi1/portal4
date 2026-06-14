<?php
/**
 * Shared setup wizard step navigation.
 *
 * @var list<array<string,mixed>> $steps  id, step, label, icon?, hint?, count_id?
 * @var string|int                $active_step
 * @var string                    $mode  'js' | 'link'
 * @var int                       $total_steps
 * @var string                    $progress_label_id  optional DOM id for step label
 * @var string                    $progress_bar_id    optional DOM id for progress fill
 */
$steps = $steps ?? [];
$activeStep = (string) ($active_step ?? '');
$mode = ($mode ?? 'js') === 'link' ? 'link' : 'js';
$totalSteps = (int) ($total_steps ?? count($steps));
$progressLabelId = $progress_label_id ?? 'wizardStepLabel';
$progressBarId = $progress_bar_id ?? 'wizardProgressBar';
$activeNum = 1;

foreach ($steps as $s) {
    if ((string) ($s['id'] ?? '') === $activeStep) {
        $activeNum = (int) ($s['step'] ?? 1);
        break;
    }
}
$pct = $totalSteps > 0 ? (int) round(($activeNum / $totalSteps) * 100) : 0;
?>
<div class="setup-wizard-progress">
  <span class="setup-wizard-progress__label">Setup progress</span>
  <span class="badge text-bg-primary setup-wizard-progress__badge" id="<?= esc($progressLabelId) ?>">
    Step <?= $activeNum ?> of <?= $totalSteps ?>
  </span>
  <div class="setup-wizard-progress__bar" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
    <div class="setup-wizard-progress__bar-fill" id="<?= esc($progressBarId) ?>" style="width: <?= $pct ?>%;"></div>
  </div>
</div>
<nav class="setup-wizard-nav fee-setup-nav" role="tablist" aria-label="Setup steps">
  <?php foreach ($steps as $tab) :
    $tabId = (string) ($tab['id'] ?? '');
    $isActive = $tabId === $activeStep;
    $stepNum = (int) ($tab['step'] ?? 0);
    $label = (string) ($tab['label'] ?? '');
    $icon = (string) ($tab['icon'] ?? 'fa-circle');
    $hint = (string) ($tab['hint'] ?? '');
    $countId = (string) ($tab['count_id'] ?? '');
    $href = (string) ($tab['href'] ?? '');
    $linkClass = 'setup-wizard-nav__link fee-setup-nav__link step-nav';
    if ($isActive) {
        $linkClass .= ' is-active active';
    }
    ?>
  <?php if ($mode === 'link' && $href !== '') : ?>
  <a
    class="<?= esc($linkClass) ?>"
    href="<?= esc($href) ?>"
    data-wizard-tab="<?= esc($tabId) ?>"
    role="tab"
    aria-selected="<?= $isActive ? 'true' : 'false' ?>"
    id="step<?= $stepNum ?>Indicator"
  >
  <?php else : ?>
  <button
    type="button"
    class="<?= esc($linkClass) ?> btn btn-link border-0"
    data-go-step="<?= $stepNum ?>"
    data-wizard-tab="<?= esc($tabId) ?>"
    role="tab"
    aria-selected="<?= $isActive ? 'true' : 'false' ?>"
    id="step<?= $stepNum ?>Indicator"
  >
  <?php endif; ?>
    <span class="setup-wizard-nav__step fee-setup-nav__step"><?= $stepNum ?></span>
    <span class="setup-wizard-nav__label fee-setup-nav__label">
      <?php if ($icon !== '') : ?><i class="fas <?= esc($icon) ?> setup-wizard-nav__icon fee-setup-nav__icon"></i><?php endif; ?>
      <?= esc($label) ?>
    </span>
    <?php if ($hint !== '') : ?>
    <span class="setup-wizard-nav__hint"><?= esc($hint) ?></span>
    <?php endif; ?>
    <?php if ($countId !== '') : ?>
    <span class="setup-wizard-nav__count step-count" id="<?= esc($countId) ?>">0</span>
    <?php endif; ?>
  <?php if ($mode === 'link' && $href !== '') : ?>
  </a>
  <?php else : ?>
  </button>
  <?php endif; ?>
  <?php endforeach; ?>
</nav>
