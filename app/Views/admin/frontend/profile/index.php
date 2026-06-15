<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
$isParent = (($role ?? '') === 'parent');
$student  = $student ?? [];
$s        = static function ($k) use ($student) {
    return isset($student[$k]) && $student[$k] !== null && $student[$k] !== ''
        ? esc((string) $student[$k])
        : '—';
};
$dob = $student['date_of_birth'] ?? '';
$dobOut = '—';
if ($dob && strpos((string) $dob, '0000-00-00') !== 0) {
    $t = strtotime((string) $dob);
    $dobOut = $t ? esc(date('d M Y', $t)) : esc((string) $dob);
}
?>
<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<section class="content parent-subpage-content pt-2">
    <div class="container-fluid px-2 px-md-3">
        <?php if ($isParent): ?>
            <div class="ds-page-layout">
                <aside class="ds-page-layout__filter ds-sticky-filter-mobile" aria-label="Student selection">
                    <div class="ds-datesheet-filter">
                        <?= view('frontend/partials/parent_child_selector', [
                            'children'        => $children ?? [],
                            'activeStudentId' => (int) ($active_student_id ?? 0),
                            'returnPath'      => $return_path ?? 'student/profile',
                        ]) ?>
                    </div>
                </aside>
                <div class="ds-page-layout__content">
        <?php endif; ?>

        <div class="parent-subpage-panel">
            <div class="parent-subpage-title-row">
                <h2 class="parent-subpage-title mb-0">
                    <i class="fa fa-user-circle text-primary me-2"></i> Profile
                </h2>
            </div>

            <div class="row mt-3">
                <div class="col-md-4 text-center mb-4 mb-md-0">
                    <?php if (! empty($photo_url)): ?>
                        <img src="<?= esc($photo_url) ?>" alt="" class="img-fluid rounded shadow" style="max-width: 220px;">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center shadow"
                             style="width: 160px; height: 160px;">
                            <i class="fa fa-user fa-4x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <h4 class="mt-3 mb-0"><?= $s('first_name') ?> <?= $s('last_name') ?></h4>
                    <p class="text-muted mb-0"><?= $s('reg_no') ?></p>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered table-sm bg-white">
                        <tbody>
                        <tr><th style="width: 40%;">Class</th><td><?= $s('class_name') ?></td></tr>
                        <tr><th>Section</th><td><?= $s('section_name') ?></td></tr>
                        <tr><th>Campus</th><td><?= $s('campus_name') ?></td></tr>
                        <tr><th>Date of birth</th><td><?= $dobOut ?></td></tr>
                        <tr><th>Gender</th><td><?= $s('gender') ?></td></tr>
                        </tbody>
                    </table>
                    <p class="text-muted small mb-0">
                        To update address, contacts, or documents, please contact the school office.
                    </p>
                </div>
            </div>
        </div>

        <?php if ($isParent): ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
