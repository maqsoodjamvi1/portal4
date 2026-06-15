<?php

$activeStudentId = (int) ($activeStudentId ?? 0);

$hifzData        = $hifzData ?? ['enrolled' => false];

$enrolled        = ! empty($hifzData['enrolled']);

$recentLog       = $hifzData['recent_log'] ?? [];

?>



<div class="card border-0 shadow-sm mb-4">

  <div class="card-body">

    <?php if ($activeStudentId <= 0): ?>

      <div class="alert alert-warning mb-0"><?= esc(lang('ParentPortal.hifz_select_student')) ?></div>

    <?php elseif (! $enrolled): ?>

      <div class="text-center py-4 text-muted">

        <i class="fa fa-quran fa-2x mb-2 opacity-50"></i>

        <p class="mb-0"><?= esc(lang('ParentPortal.hifz_not_enrolled')) ?></p>

      </div>

    <?php else: ?>

      <div class="row g-3 mb-4">

        <div class="col-md-4">

          <div class="small text-muted"><?= esc(lang('ParentPortal.hifz_section')) ?></div>

          <div class="fw-semibold"><?= esc($hifzData['section_name'] ?? '—') ?></div>

          <div class="small text-muted mt-2"><?= esc(lang('ParentPortal.hifz_teacher')) ?></div>

          <div><?= esc($hifzData['teacher_name'] ?? '—') ?></div>

        </div>

        <div class="col-md-4">

          <div class="small text-muted"><?= esc(lang('ParentPortal.hifz_progress')) ?></div>

          <div class="fw-semibold">

            <?= esc($hifzData['current_para_label'] ?? (lang('ParentPortal.hifz_juz_label') . ' ' . (int) ($hifzData['current_juz'] ?? 0))) ?>

          </div>

          <div class="small text-muted mt-1">

            <?= esc(lang('ParentPortal.hifz_daily_target')) ?>:

            <?= (int) ($hifzData['mutalia_lines'] ?? 0) ?>

            <?= esc(lang('ParentPortal.hifz_lines_short')) ?> (Mutalia)

            · <?= (int) ($hifzData['manzil_paras_per_day'] ?? 1) ?> Manzil para(s)/day

          </div>

        </div>

        <div class="col-md-4">

          <div class="small text-muted"><?= esc(lang('ParentPortal.hifz_sequence')) ?></div>

          <div><?= esc($hifzData['sequence_label'] ?? 'Para-wise') ?></div>

          <?php if (! empty($hifzData['enrollment_date'])): ?>

            <div class="small text-muted mt-2"><?= esc(lang('ParentPortal.hifz_enrolled_since')) ?></div>

            <div><?= esc($hifzData['enrollment_date']) ?></div>

          <?php endif; ?>

        </div>

      </div>



      <h6 class="mb-3"><i class="fa fa-list me-1"></i> <?= esc(lang('ParentPortal.hifz_recent_recitation')) ?></h6>



      <?php if ($recentLog === []): ?>

        <div class="text-center py-3 text-muted">

          <p class="mb-0"><?= esc(lang('ParentPortal.hifz_empty_log')) ?></p>

        </div>

      <?php else: ?>

        <div class="table-responsive">

          <table class="table table-sm table-bordered mb-0">

            <thead class="table-light">

              <tr>

                <th><?= esc(lang('ParentPortal.hifz_col_date')) ?></th>

                <th><?= esc(lang('ParentPortal.hifz_col_sabaq')) ?></th>

                <th><?= esc(lang('ParentPortal.hifz_col_sabqi')) ?></th>

                <th><?= esc(lang('ParentPortal.hifz_col_mutalia')) ?></th>

                <th>Manzil</th>

                <th><?= esc(lang('ParentPortal.hifz_col_quality')) ?></th>

              </tr>

            </thead>

            <tbody>

              <?php foreach ($recentLog as $row): ?>

                <tr>

                  <td class="text-nowrap"><?= esc($row['date'] ?? '') ?></td>

                  <td class="small"><?= esc($row['sabaq_label'] ?? '—') ?></td>

                  <td class="small"><?= esc($row['sabqi_label'] ?? '—') ?></td>

                  <td class="small"><?= esc($row['mutalia_label'] ?? '—') ?></td>

                  <td class="small"><?= esc($row['manzil_label'] ?? '—') ?></td>

                  <td class="small">

                    <?= esc($row['sabaq_quality'] ?? '—') ?>

                    <?php if (! empty($row['manzil_quality']) && $row['manzil_quality'] !== '—'): ?>

                      · <?= esc($row['manzil_quality']) ?>

                    <?php endif; ?>

                  </td>

                </tr>

              <?php endforeach; ?>

            </tbody>

          </table>

        </div>

        <p class="small text-muted mt-2 mb-0"><?= esc(lang('ParentPortal.hifz_log_hint')) ?></p>

      <?php endif; ?>

    <?php endif; ?>

  </div>

</div>

