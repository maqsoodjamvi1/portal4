<?php

/**

 * Persistent setup progress banner (shown until calendar + academic + fee setup complete).

 */

use App\Libraries\SchoolSetupProgress;



if (! session()->get('IsAuthorized')) {

    return;

}



$uri = service('uri');

$path = trim($uri->getPath(), '/');

if (str_contains($path, 'admin/login') || str_contains($path, 'signup')) {

    return;

}



helper('server');

$schoolinfo = function_exists('getSchoolInfo') ? getSchoolInfo() : null;

if (! $schoolinfo || empty($schoolinfo->system_id)) {

    return;

}



$userId = (int) session()->get('member_userid');

$campusId = (int) (session()->get('member_campusid') ?? 0);

if ($campusId <= 0 && ! empty($schoolinfo->campus_id)) {

    $campusId = (int) $schoolinfo->campus_id;

}



$systemId = (int) $schoolinfo->system_id;



// Skip for teachers (same as Login redirect).

if (SchoolSetupProgress::isTeacher($userId, $campusId)) {

    return;

}



$setup = SchoolSetupProgress::getStatus($systemId, $campusId);

if ($setup['is_complete']) {

    return;

}



$completed = (int) $setup['completed_count'];

$total = (int) $setup['total'];

$nextTitle = $setup['next_step_title'] ?? lang('SchoolSetup.continue_setup');

$nextUrl = $setup['next_step_url'] ?? base_url('admin/getting-started');

$hubUrl = base_url('admin/getting-started');

?>

<link rel="stylesheet" href="<?= base_url('assets/css/school_setup_guide.css') ?>?v=2">

<div class="admin-setup-guide no-print" role="status">

  <p class="admin-setup-guide__text mb-0">

    <i class="fas fa-tasks me-1"></i>

    <?= lang('SchoolSetup.banner_setup') ?> <strong><?= lang('SchoolSetup.banner_of_complete', [$completed, $total]) ?></strong>

    <?php if ($nextTitle !== ''): ?>

      — <strong><?= lang('SchoolSetup.banner_pending', [$nextTitle]) ?></strong>

    <?php endif; ?>

    <span class="setup-mini-dots">

      <?php foreach ($setup['steps'] as $step): ?>

        <?php

        $dotClass = 'setup-mini-dot';

        if (! empty($step['complete'])) {

            $dotClass .= ' is-done';

        } elseif (($setup['next_step_id'] ?? '') === ($step['id'] ?? '')) {

            $dotClass .= ' is-current';

        }

        ?>

        <span class="<?= $dotClass ?>" title="<?= esc($step['title'] ?? '') ?>"></span>

      <?php endforeach; ?>

    </span>

  </p>

  <div class="admin-setup-guide__actions">

    <a href="<?= esc($hubUrl) ?>" class="btn-hub"><?= lang('SchoolSetup.banner_view_all') ?></a>

    <a href="<?= esc($nextUrl) ?>" class="btn btn-continue"><?= lang('SchoolSetup.banner_continue') ?></a>

  </div>

</div>
