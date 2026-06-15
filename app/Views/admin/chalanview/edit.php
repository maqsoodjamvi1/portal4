<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?= view('components/page_header', [
    'title' => $pageTitle ?? 'Edit Fee Chalan',
    'icon' => 'fas fa-edit',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Chalan', 'url' => base_url('admin/fee_chalan')],
        ['label' => 'Edit', 'active' => true],
    ],
]) ?>
<section class="content">
  <div class="card sms-card card-primary card-outline card-tabs">
    <div class="card-body">
      <?= $this->include('admin/fee_chalan_add') ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
