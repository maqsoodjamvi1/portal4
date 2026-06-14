<?php
/**
 * Dashboard setup alert aligned with SchoolSetupProgress (not "Step 1 of 10").
 *
 * @var array|null $setupProgress From SchoolSetupProgress::getStatus()
 */
$setupProgress = $setupProgress ?? null;
if ($setupProgress === null || ! empty($setupProgress['is_complete'])) {
    return;
}

$completed = (int) ($setupProgress['completed_count'] ?? 0);
$total     = (int) ($setupProgress['total'] ?? 3);
$percent   = (int) ($setupProgress['percent'] ?? 0);
$nextTitle = $setupProgress['next_step_title'] ?? lang('SchoolSetup.continue_setup');
$nextUrl   = $setupProgress['next_step_url'] ?? base_url('admin/getting-started');
$hubUrl    = base_url('admin/getting-started');
?>
<div class="dash-config-alert">
    <i class="fas fa-tasks me-1"></i>
    <?= lang('SchoolSetup.banner_setup') ?>
    <strong><?= lang('SchoolSetup.banner_of_complete', [$completed, $total]) ?></strong>
    (<?= $percent ?>%)
    <?php if ($nextTitle !== ''): ?>
        - <strong><?= lang('SchoolSetup.banner_pending', [$nextTitle]) ?></strong>
    <?php endif; ?>
    <a href="<?= esc($nextUrl) ?>" class="ms-2"><?= lang('SchoolSetup.banner_continue') ?></a>
    <a href="<?= esc($hubUrl) ?>" class="ms-2 text-muted"><?= lang('SchoolSetup.banner_view_all') ?></a>
</div>
