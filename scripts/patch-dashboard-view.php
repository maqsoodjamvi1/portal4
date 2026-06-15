<?php

$path = dirname(__DIR__) . '/app/Views/admin/dashboard.php';
$lines = file($path);
$router = <<<'PHP'

<?php
$__dashRole = $dashboardLayoutRole ?? 'default';
$__dashHomeMap = [
    'teacher'   => 'teacher_home',
    'finance'   => 'finance_home',
    'principal' => 'principal_home',
];
$__dashHome = $__dashHomeMap[$__dashRole] ?? 'default_home';
echo view('admin/dashboard/partials/' . $__dashHome, get_defined_vars());
?>

PHP;
$out = array_merge(array_slice($lines, 0, 80), [$router], array_slice($lines, 535));
file_put_contents($path, implode('', $out));
echo 'Patched: ' . count($lines) . ' -> ' . count($out) . PHP_EOL;
