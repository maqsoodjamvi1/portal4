<?php
/** @var string|null $permKey */
/** @var list<string>|null $permKeys */
$permKeys = $permKeys ?? (isset($permKey) ? [(string) $permKey] : []);
$permKeys = array_values(array_filter(array_map('strval', $permKeys)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access denied</title>
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.min.css') ?>">
</head>
<body class="hold-transition">
<div class="wrapper" style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f4f6f9;">
    <div class="card shadow-sm" style="max-width:520px;width:100%;">
        <div class="card-body text-center p-4">
            <div class="text-warning mb-3" style="font-size:3rem;"><i class="fas fa-lock"></i></div>
            <h1 class="h4 mb-2"><?= esc(lang('SchoolSetup.error_403_title')) ?></h1>
            <p class="text-muted mb-3"><?= esc(lang('SchoolSetup.error_403_body')) ?></p>
            <?php if ($permKeys !== []): ?>
            <div class="alert alert-light text-start small mb-3">
                <strong><?= esc(lang('SchoolSetup.error_403_required')) ?></strong>
                <ul class="mb-0 ps-3">
                    <?php foreach ($permKeys as $k): ?>
                    <li><code><?= esc($k) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-primary"><i class="fas fa-tachometer-alt me-1"></i> <?= esc(lang('SchoolSetup.error_403_dashboard')) ?></a>
            <a href="<?= base_url('admin/roles') ?>" class="btn btn-outline-secondary ms-1"><i class="fas fa-user-shield me-1"></i> <?= esc(lang('SchoolSetup.error_403_roles')) ?></a>
        </div>
    </div>
</div>
</body>
</html>
