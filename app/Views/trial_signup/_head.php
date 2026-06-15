<?php
$pageTitle = $pageTitle ?? ($productName . ' — Start Your Free Trial');
$itiBase    = $itiBase ?? base_url('resource/intl-tel-input');
$itiCdnBase = $itiCdnBase ?? 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build';
$embed      = ! empty($embed);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($pageTitle) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
  <?php if (! empty($loadIntlTel)): ?>
  <link rel="stylesheet" href="<?= esc($itiBase) ?>/css/intlTelInput.css" id="itiCssLocal"
        onerror="this.onerror=null;this.href='<?= esc($itiCdnBase) ?>/css/intlTelInput.css'">
  <?php endif; ?>
  <link rel="stylesheet" href="<?= base_url('assets/css/trial_signup.css') ?>?v=4">
</head>
<body class="trial-signup-page<?= $embed ? ' trial-signup-page--embed' : '' ?>">
<?php if (! $embed) : ?>
<header class="trial-topbar">
  <a href="https://timesoftsol.com/" class="brand-lockup" target="_blank" rel="noopener">
    <span class="brand-name">TIME Soft Solution</span>
    <span class="brand-tag">Empowered by innovators</span>
  </a>
  <a href="<?= base_url('admin/login') ?>">Sign in</a>
</header>
<?php endif; ?>
