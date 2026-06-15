<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<?php $isParent = (($role ?? '') === 'parent'); ?>

<div class="content-header parent-subpage-breadcrumb-bar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('student/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content parent-subpage-content">
    <div class="container-fluid">
        <?php if ($isParent): ?>
            <?= view('frontend/partials/parent_child_selector', [
                'children'        => $children ?? [],
                'activeStudentId' => (int) (session('active_student_id') ?? 0),
                'returnPath'      => 'student/results',
            ]) ?>
        <?php endif; ?>

        <div class="parent-subpage-panel">
            <div class="parent-subpage-title-row">
                <h2 class="parent-subpage-title">Results</h2>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light"><tr><th>#</th><th>Exam</th><th>Obtained</th><th>Total</th><th>Grade</th><th>Posted</th></tr></thead>
                    <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No results available.</td></tr>
                    <?php else: foreach ($results as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td><td>#<?= esc($r['exam_id']) ?></td>
                            <td><?= esc($r['obtain_total_mark']) ?></td><td><?= esc($r['exam_total_mark']) ?></td>
                            <td><span class="badge bg-secondary"><?= esc($r['grade'] ?? '-') ?></span></td>
                            <td><?= esc($r['created_at'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
