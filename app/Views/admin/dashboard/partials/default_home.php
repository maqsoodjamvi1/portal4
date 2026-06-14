<?php if ($isTeacher ?? false): ?>
<?= view('admin/dashboard/partials/_teacher_classes') ?>
<?php endif; ?>
<?= view('admin/dashboard/partials/_overview_kpis') ?>
<?= view('admin/dashboard/partials/_main_grid') ?>
<?php if (! ($isTeacher ?? false) && hasPermission('admin-db-fee-collection')): ?>
<?= view('admin/dashboard/partials/_finance_analytics') ?>
<?php endif; ?>
