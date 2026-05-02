<?php
$title = $title ?? 'Bulk Student Update';
$subtitle = $subtitle ?? $title;
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1><?= esc($title) ?></h1>
      </div>
      <div class="col-sm-6 text-right">
        <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active"><?= esc($subtitle) ?></li>
        </ol>
      </div>
    </div>
  </div>
</section>
