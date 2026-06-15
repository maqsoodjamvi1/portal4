<?php
$title = $title ?? 'Bulk Student Update';
$subtitle = $subtitle ?? $title;
?>
<?= view('components/page_header', [
    'title' => $title,
    'subtitle' => $subtitle !== $title ? $subtitle : null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => $subtitle, 'active' => true],
    ],
]) ?>
