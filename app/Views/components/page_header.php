<?php
/**
 * Page header: title, optional subtitle, breadcrumbs, actions.
 *
 * @var string       $title
 * @var string|null  $subtitle
 * @var string|null  $icon       Font Awesome class e.g. fas fa-users
 * @var array        $breadcrumbs [['label'=>'Dashboard','url'=>base_url(...)], ['label'=>'Roles','active'=>true]]
 * @var string|null  $actionsHtml Raw HTML for right-side buttons (col-sm-6)
 */
$title = $title ?? '';
$subtitle = $subtitle ?? null;
$icon = $icon ?? null;
$breadcrumbs = $breadcrumbs ?? [];
$actionsHtml = $actionsHtml ?? null;
?>
<section class="content-header sms-page-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="<?= $actionsHtml ? 'col-sm-6' : 'col-sm-12' ?>">
        <h1 class="sms-page-header__title mb-1">
          <?php if ($icon): ?><i class="<?= esc($icon, 'attr') ?>"></i><?php endif; ?>
          <?= esc($title) ?>
        </h1>
        <?php if ($subtitle): ?>
          <p class="sms-page-header__subtitle"><?= esc($subtitle) ?></p>
        <?php endif; ?>
      </div>
      <?php if ($actionsHtml): ?>
        <div class="col-sm-6"><?= $actionsHtml ?></div>
      <?php endif; ?>
    </div>
    <?php if (!empty($breadcrumbs)): ?>
      <div class="row">
        <div class="col-12">
          <ol class="breadcrumb float-sm-right mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
              <?php if (!empty($crumb['active'])): ?>
                <li class="breadcrumb-item active"><?= esc($crumb['label'] ?? '') ?></li>
              <?php else: ?>
                <li class="breadcrumb-item">
                  <a href="<?= esc($crumb['url'] ?? '#', 'attr') ?>"><?= esc($crumb['label'] ?? '') ?></a>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
