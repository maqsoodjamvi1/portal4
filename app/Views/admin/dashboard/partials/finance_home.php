<?php $financeOnly = true; ?>
<?= view('admin/dashboard/partials/_overview_kpis', ['financeOnly' => true]) ?>
<?= view('admin/dashboard/partials/_finance_analytics', ['forceFinanceAnalytics' => true]) ?>
