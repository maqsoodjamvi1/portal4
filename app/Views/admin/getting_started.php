<?php

/** @var array $setup */

$steps = $setup['steps'] ?? [];

$percent = (int) ($setup['percent'] ?? 0);

$completed = (int) ($setup['completed_count'] ?? 0);

$total = (int) ($setup['total'] ?? 3);

$nextUrl = $setup['next_step_url'] ?? base_url('admin/getting-started');

$nextTitle = $setup['next_step_title'] ?? lang('SchoolSetup.continue_setup');

$currentLang = session()->get('language') ?? 'en';

?>

<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('pageStyles') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/school_setup_guide.css') ?>?v=3">

<?= $this->endSection() ?>



<?= $this->section('content') ?>



<?= view('components/page_header', [
    'title' => lang('SchoolSetup.hub_title'),
    'icon' => 'fas fa-route',
    'subtitle' => lang('SchoolSetup.hub_subtitle'),
    'actionsHtml' => '<div class="text-sm-right"><span class="badge text-bg-light border setup-header-badge">'
        . esc(lang('SchoolSetup.progress_count', [$completed, $total])) . '</span></div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => lang('SchoolSetup.hub_title'), 'active' => true],
    ],
]) ?>



<section class="content">

  <div class="container-fluid">

    <?php $setupRequiredMsg = session()->getFlashdata('setup_required'); ?>

    <?php if (! empty($setupRequiredMsg)): ?>

      <div class="alert alert-warning alert-dismissible fade show">

        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        <i class="fas fa-exclamation-triangle me-1"></i> <?= esc($setupRequiredMsg) ?>

      </div>

    <?php endif; ?>

    <div class="row justify-content-center">

      <div class="col-lg-9 col-xl-8">



        <div class="card shadow-sm setup-lang-card mb-4">

          <div class="card-body text-center py-4">

            <h5 class="mb-3"><i class="fas fa-globe text-primary"></i> <?= lang('SchoolSetup.choose_language') ?></h5>

            <div class="setup-lang-buttons btn-group btn-group-lg mb-2" role="group">

              <button type="button" class="btn btn-outline-primary<?= $currentLang === 'en' ? ' active' : '' ?>" onclick="changeLanguage('en')"><?= lang('SchoolSetup.lang_english') ?></button>

              <button type="button" class="btn btn-outline-primary<?= $currentLang === 'ur' ? ' active' : '' ?>" onclick="changeLanguage('ur')"><?= lang('SchoolSetup.lang_urdu') ?></button>

              <button type="button" class="btn btn-outline-primary<?= $currentLang === 'ar' ? ' active' : '' ?>" onclick="changeLanguage('ar')"><?= lang('SchoolSetup.lang_arabic') ?></button>

            </div>

            <p class="text-muted small mb-0"><?= lang('SchoolSetup.language_hint') ?></p>

          </div>

        </div>



        <div class="card shadow-sm setup-progress-card mb-4">

          <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">

              <strong class="text-navy"><?= lang('SchoolSetup.setup_progress') ?></strong>

              <span class="text-muted small"><?= $percent ?>%</span>

            </div>

            <div class="progress rounded-pill setup-progress-bar mb-3">

              <div class="progress-bar bg-primary" role="progressbar" style="width:<?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>

            </div>

            <a href="<?= esc($nextUrl) ?>" class="btn btn-primary btn-lg w-100 setup-continue-btn">

              <i class="fas fa-arrow-right me-1"></i> <?= lang('SchoolSetup.continue_setup_step', [$nextTitle]) ?>

            </a>

          </div>

        </div>



        <div class="setup-steps-list">

          <?php foreach ($steps as $step): ?>

            <?php

            $done = ! empty($step['complete']);

            $locked = ! empty($step['locked']);

            $isNext = ! $done && ! $locked && ($setup['next_step_id'] ?? '') === ($step['id'] ?? '');

            if ($locked) {

                $statusLabel = lang('SchoolSetup.status_locked');

                $statusClass = 'text-bg-secondary';

            } elseif ($done) {

                $statusLabel = lang('SchoolSetup.status_done');

                $statusClass = 'text-bg-success';

            } elseif ($isNext) {

                $statusLabel = lang('SchoolSetup.status_current');

                $statusClass = 'text-bg-primary';

            } else {

                $statusLabel = lang('SchoolSetup.status_pending');

                $statusClass = 'text-bg-secondary';

            }

            $subDone = (int) ($step['substeps_done'] ?? 0);

            $subTotal = (int) ($step['substeps_total'] ?? 0);

            $cardClass = $locked ? 'setup-step-card--locked' : ($isNext ? 'setup-step-card--active' : '');

            if ($done) {

                $cardClass .= ' setup-step-card--done';

            }

            $numClass = $locked ? 'is-locked' : ($done ? 'is-done' : ($isNext ? 'is-active' : ''));

            ?>

            <div class="card setup-step-card mb-3 <?= esc(trim($cardClass)) ?>">

              <div class="card-body">

                <div class="d-flex align-items-start">

                  <div class="setup-step-num <?= esc($numClass) ?>">

                    <?php if ($done): ?>

                      <i class="fas fa-check"></i>

                    <?php elseif ($locked): ?>

                      <i class="fas fa-lock"></i>

                    <?php else: ?>

                      <?= (int) ($step['number'] ?? 0) ?>

                    <?php endif; ?>

                  </div>

                  <div class="flex-grow-1 setup-step-body">

                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-1">

                      <h5 class="mb-0 setup-step-title">

                        <i class="fas <?= esc($step['icon'] ?? 'fa-circle') ?> text-muted me-1"></i>

                        <?= esc($step['title'] ?? '') ?>

                        <?php if ($subTotal > 0): ?>

                          <span class="setup-substep-progress text-muted small fw-normal ms-1">

                            — <?= lang('SchoolSetup.substep_progress', [$subDone, $subTotal]) ?>

                          </span>

                        <?php endif; ?>

                      </h5>

                      <span class="badge <?= $statusClass ?> mt-1"><?= esc($statusLabel) ?></span>

                    </div>

                    <p class="text-muted small mb-2"><?= esc($step['description'] ?? '') ?></p>



                    <?php if (! empty($step['substeps']) && is_array($step['substeps'])): ?>

                      <ul class="setup-substeps list-unstyled mb-2">

                        <?php foreach ($step['substeps'] as $sub): ?>

                          <?php

                          $subLocked = $locked || ! empty($sub['locked']);

                          $subDoneItem = ! empty($sub['complete']);

                          $subClass = 'setup-substep';

                          if ($subDoneItem) {

                              $subClass .= ' is-done';

                          }

                          if ($subLocked) {

                              $subClass .= ' is-locked';

                          }

                          ?>

                          <li class="<?= esc($subClass) ?>">

                            <?php if ($subDoneItem): ?>

                              <i class="fas fa-check-circle text-success"></i>

                            <?php else: ?>

                              <i class="far fa-circle text-muted"></i>

                            <?php endif; ?>

                            <?php if ($subLocked): ?>

                              <span><?= esc($sub['title'] ?? '') ?></span>

                            <?php else: ?>

                              <a href="<?= esc($sub['url'] ?? '#') ?>"><?= esc($sub['title'] ?? '') ?></a>

                            <?php endif; ?>

                          </li>

                        <?php endforeach; ?>

                      </ul>

                    <?php endif; ?>



                    <?php if ($locked): ?>

                      <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="<?= esc(lang('SchoolSetup.btn_locked')) ?>">

                        <i class="fas fa-lock me-1"></i> <?= lang('SchoolSetup.btn_locked') ?>

                      </button>

                    <?php else: ?>

                      <a href="<?= esc($step['url'] ?? '#') ?>" class="btn btn-sm <?= $done ? 'btn-outline-secondary' : 'btn-outline-primary' ?>">

                        <?= $done ? lang('SchoolSetup.btn_review') : lang('SchoolSetup.btn_open_step') ?> <i class="fas fa-external-link-alt ms-1"></i>

                      </a>

                    <?php endif; ?>

                  </div>

                </div>

              </div>

            </div>

          <?php endforeach; ?>

        </div>



        <p class="text-center text-muted small mb-0">

          <?= lang('SchoolSetup.help_footer') ?> <a href="mailto:support@timesoftsol.com">support@timesoftsol.com</a>

        </p>

      </div>

    </div>

  </div>

</section>



<?= $this->endSection() ?>
