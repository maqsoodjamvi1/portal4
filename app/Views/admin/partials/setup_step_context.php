<?php

/**

 * Context strip for setup wizard pages.

 *

 * Expected vars:

 *   $setup_step_id — calendar|academic|fee

 *   $setup (optional) — full status from SchoolSetupProgress::getStatus()

 */

use App\Libraries\SchoolSetupProgress;



if (! isset($setup) || ! is_array($setup)) {

    $ctx = SchoolSetupProgress::forCurrentUser();

    $setup = $ctx['status'] ?? ['steps' => [], 'total' => 3];

}



$stepId = $setup_step_id ?? '';

$steps = $setup['steps'] ?? [];

$currentNum = 0;

$stepTitle = '';



foreach ($steps as $s) {

    if (($s['id'] ?? '') === $stepId) {

        $currentNum = (int) ($s['number'] ?? 0);

        $stepTitle = (string) ($s['title'] ?? '');

        break;

    }

}



if ($currentNum <= 0) {

    return;

}

?>

<link rel="stylesheet" href="<?= base_url('assets/css/school_setup_guide.css') ?>?v=2">

<div class="setup-step-context">

  <p class="setup-step-context__label mb-0">

    <?= lang('SchoolSetup.context_step', [$currentNum, (int) ($setup['total'] ?? 3)]) ?>

    <?php if ($stepTitle !== ''): ?> &middot; <?= esc($stepTitle) ?><?php endif; ?>

  </p>

  <div class="setup-step-context__dots" aria-hidden="true">

    <?php foreach ($steps as $s): ?>

      <?php

      $dotClass = 'setup-step-context__dot';

      if (! empty($s['complete'])) {

          $dotClass .= ' is-done';

      } elseif (($s['id'] ?? '') === $stepId) {

          $dotClass .= ' is-current';

      }

      ?>

      <span class="<?= $dotClass ?>" title="<?= esc($s['title'] ?? '') ?>"></span>

    <?php endforeach; ?>

  </div>

  <a href="<?= base_url('admin/getting-started') ?>" class="setup-step-context__link">

    <i class="fas fa-list-ul me-1"></i> <?= lang('SchoolSetup.context_all_steps') ?>

  </a>

</div>
