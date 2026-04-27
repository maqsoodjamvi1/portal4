<?php
// app/Views/admin/layout.php

/**
 * Tries a list of candidate view names and returns the first that exists,
 * otherwise returns null so we can silently skip includes.
 */
function _firstExistingView(array $candidates): ?string {
    foreach ($candidates as $v) {
        $file = APPPATH . 'Views/' . str_replace('/', DIRECTORY_SEPARATOR, $v) . '.php';
        if (is_file($file)) return $v;
    }
    return null;
}

// 🔎 Adjust these arrays to match your project if needed
$headerCandidates = [
    'admin/header',            // app/Views/admin/header.php
    'admin/common/header',     // app/Views/admin/common/header.php
    'admin/partials/header',   // app/Views/admin/partials/header.php
    'admin/layout/header',     // app/Views/admin/layout/header.php
    'layouts/admin/header',    // app/Views/layouts/admin/header.php
];
$footerCandidates = [
    'admin/footer',
    'admin/common/footer',
    'admin/partials/footer',
    'admin/layout/footer',
    'layouts/admin/footer',
];

$headerView = _firstExistingView($headerCandidates);
$footerView = _firstExistingView($footerCandidates);

// Include header if found (no fatal if missing)
if ($headerView) {
    echo $this->include($headerView);
}
?>

<?= $this->renderSection('content') ?>

<?php // Optional per-page scripts block ?>
<?= $this->renderSection('scripts') ?>

<?php
// Include footer if found (no fatal if missing)
if ($footerView) {
    echo $this->include($footerView);
}
