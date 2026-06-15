// app/Views/admin/test_result_card.php
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?= view('components/page_header', [
    'title' => 'Test Result Card',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Test Result Card', 'active' => true],
    ],
]) ?>

<section class="content"><div class="container-fluid">It works.</div></section>
<?= $this->endSection() ?>