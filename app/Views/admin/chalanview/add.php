<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<section class="content-header">
  <div class="container-fluid d-flex justify-content-between">
    <h1><?= esc($pageTitle ?? 'Generate Fee Chalan') ?></h1>
    <ol class="breadcrumb float-sm-right">
      <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
      <li class="breadcrumb-item active"><?= esc($pageTitle ?? 'Generate Fee Chalan') ?></li>
    </ol>
  </div>
</section>
<section class="content">
  <div class="card card-primary card-outline card-tabs">
    <div class="card-body">
      <?= $this->include('admin/fee_chalan_add') ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
