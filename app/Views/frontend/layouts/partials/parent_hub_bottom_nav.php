<?php

$__langNav = session('language') ?? 'en';

$__logoutLabel = ($__langNav === 'ur') ? 'لاگ آؤٹ' : (($__langNav === 'ar') ? 'خروج' : 'Logout');

$__homeLabel = ($__langNav === 'ur') ? 'ہوم' : 'Home';

$__feesLabel = ($__langNav === 'ur') ? 'فیس' : 'Fees';

$__alertsLabel = ($__langNav === 'ur') ? 'الرٹ' : 'Alerts';

$__profileLabel = ($__langNav === 'ur') ? 'پروفائل' : 'Profile';



$uri = service('uri');

$path = '/' . trim($uri->getPath(), '/');

$isActive = static fn (string $needle): bool => $needle !== '' && str_starts_with($path, trim($needle, '/'));



$actionCenterCount = (int) ($actionCenterCount ?? 0);

$alertsUrl = ! empty($actionCenterFirstUrl)

    ? $actionCenterFirstUrl

    : base_url('student/dashboard#alerts');

?>

<nav class="parent-portal-bottomnav parent-bottomnav--five d-md-none" aria-label="<?= esc($__langNav === 'ur' ? 'فوری نیویگیشن' : 'Quick navigation') ?>">

  <a href="<?= base_url('student/dashboard') ?>" class="<?= $isActive('student/dashboard') && ! str_contains($path, 'student/dashboard/section') ? 'active' : '' ?>">

    <i class="fa fa-home" aria-hidden="true"></i>

    <span><?= esc($__homeLabel) ?></span>

  </a>

  <a href="<?= base_url('student/fees') ?>" class="<?= $isActive('student/fees') ? 'active' : '' ?>">

    <i class="fa fa-credit-card" aria-hidden="true"></i>

    <span><?= esc($__feesLabel) ?></span>

  </a>

  <a href="<?= esc($alertsUrl) ?>" class="parent-bottomnav-badge-wrap">

    <i class="fas fa-bell" aria-hidden="true"></i>

    <span><?= esc($__alertsLabel) ?></span>

    <?php if ($actionCenterCount > 0): ?>

    <span class="parent-bottomnav-badge" aria-label="<?= (int) $actionCenterCount ?> alerts"><?= min(9, $actionCenterCount) ?><?= $actionCenterCount > 9 ? '+' : '' ?></span>

    <?php endif; ?>

  </a>

  <a href="<?= base_url('student/profile') ?>" class="<?= $isActive('student/profile') ? 'active' : '' ?>">

    <i class="fa fa-user-circle" aria-hidden="true"></i>

    <span><?= esc($__profileLabel) ?></span>

  </a>

  <a href="<?= route_to('logout') ?>" aria-label="<?= esc($__logoutLabel) ?>">

    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>

    <span><?= esc($__logoutLabel) ?></span>

  </a>

</nav>


