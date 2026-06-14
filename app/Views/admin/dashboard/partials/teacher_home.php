<?= view('admin/dashboard/partials/_teacher_kpis') ?>
<?= view('admin/dashboard/partials/action_center', ['actionCenter' => $actionCenter ?? []]) ?>

<div class="dash-main-grid dash-main-grid--teacher-work">
    <div class="dash-stack">
        <?= view('admin/dashboard/partials/_teacher_classes') ?>
    </div>
    <div class="dash-stack">
        <?= view('admin/dashboard/partials/_teacher_sidebar') ?>
    </div>
</div>
