<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/school_setup_wizard.css') ?>?v=1">
<link rel="stylesheet" href="<?= base_url('assets/css/fee_setup.css') ?>?v=6">

<?= view('components/page_header', [
    'title' => 'Fee Plan Months',
    'icon' => 'fas fa-calendar-alt',
    'subtitle' => 'Choose which months apply to each fee plan for challan generation. Optional — configure when your school needs custom billing months.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Configuration', 'url' => base_url('admin/fee_setup')],
        ['label' => 'Fee Plan Months', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid fee-setup-page">
    <div class="setup-wizard-shell fee-setup-shell">
      <div class="setup-wizard-body fee-setup-body">
        <?= view('admin/fee_setup/_tab_months', [
            'billing_months' => $billing_months ?? [],
            'fee_plans'      => $fee_plans ?? [],
            'plan_count'     => $plan_count ?? 0,
            'active_slots'   => $active_slots ?? 0,
            'standalone'     => true,
        ]) ?>
      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
