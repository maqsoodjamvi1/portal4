<?php
$__langNav = session('language') ?? 'en';
$__homeLabel = ($__langNav === 'ur') ? 'ڈیش بورڈ' : (($__langNav === 'ar') ? 'الرئيسية' : 'Dashboard');
?><!doctype html>
<html lang="<?= esc($__langNav) ?>" dir="<?= in_array($__langNav, ['ar', 'ur'], true) ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="utf-8">
  <title><?= esc($title ?? 'Quiz') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#6759ff">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap">
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">

  <style>
    html, body.quiz-fullscreen-mode {
      height: 100%;
      margin: 0;
      overflow: hidden;
      background: #f7fbff;
    }
    .quiz-fullscreen-shell {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      min-height: 100dvh;
      width: 100%;
    }
    .quiz-fullscreen-main {
      flex: 1 1 auto;
      min-height: 0;
      overflow: hidden;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
  </style>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260615b') ?>"></script>
</head>
<body class="quiz-fullscreen-mode quiz-attempt-page <?= in_array($__langNav, ['ar', 'ur'], true) ? 'rtl-support' : '' ?>">
<div class="quiz-fullscreen-shell">
  <main class="quiz-fullscreen-main" role="main">
    <?= $this->renderSection('content') ?>
  </main>
</div>
<?= $this->renderSection('scripts') ?>
</body>
</html>
