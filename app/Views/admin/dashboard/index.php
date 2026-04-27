<?php
$this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content">

    <?= $this->include('admin/dashboard/widgets/stats') ?>
    <?= $this->include('admin/dashboard/widgets/fee_chart') ?>
    <?= $this->include('admin/dashboard/widgets/attendance') ?>
    <?= $this->include('admin/dashboard/widgets/pending') ?>

</section>

<?= $this->endSection() ?>