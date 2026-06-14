<?php
/** @var bool $isTrialHost */
/** @var bool $isDemoHost */
/** @var bool $hasAppBanner */

if (empty($hasAppBanner)) {
    return;
}
?>
<?php if (!empty($isTrialHost)): ?>
<div class="admin-app-banner admin-app-banner--trial no-print" role="status">
  <i class="fas fa-clock me-1" aria-hidden="true"></i>
  Trial account — data is temporary. Pay your bill to keep this data on a live account.
</div>
<?php elseif (!empty($isDemoHost)): ?>
<div class="admin-app-banner admin-app-banner--demo no-print" role="status">
  <span>Demo environment</span>
  <a href="<?= base_url('signup') ?>" class="btn btn-sm btn-danger ms-2">Create your own school</a>
</div>
<?php endif; ?>
