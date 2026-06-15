<?php

/**

 * Parent portal action center — unpaid fees, quizzes, diary.

 *

 * @var list<array{label: string, detail: string, url: string, icon: string, badge: int}> $parentActionCenter

 */

$parentActionCenter = $parentActionCenter ?? [];

if ($parentActionCenter === []) {

    return;

}

$isUrdu = strtolower(trim((string) (session()->get('language') ?: 'en'))) === 'ur';

?>

<section class="parent-action-center mb-3" id="alerts" aria-label="<?= $isUrdu ? 'اہم کارروائیاں' : 'Action center' ?>">

    <div class="d-flex align-items-center justify-content-between mb-2">

        <h2 class="h6 fw-bold mb-0 text-dark">

            <i class="fas fa-bolt text-warning me-1"></i>

            <?= esc(lang('ParentPortal.action_center_title')) ?>

        </h2>

    </div>



    <!-- Mobile: horizontal scroll chips -->

    <div class="parent-action-center__chips" role="list">

        <?php foreach ($parentActionCenter as $item): ?>

        <a href="<?= esc($item['url']) ?>" class="parent-action-chip" role="listitem">

            <span class="parent-action-chip__icon"><i class="<?= esc($item['icon'] ?? 'fas fa-circle') ?>"></i></span>

            <span class="parent-action-chip__text"><?= esc($item['label']) ?><?= ! empty($item['detail']) ? ' · ' . esc($item['detail']) : '' ?></span>

            <?php if (! empty($item['badge'])): ?>

            <span class="parent-action-chip__badge"><?= (int) $item['badge'] ?></span>

            <?php endif; ?>

        </a>

        <?php endforeach; ?>

    </div>



    <!-- Desktop: card grid -->

    <div class="parent-action-center__cards row g-0">

        <?php foreach ($parentActionCenter as $item): ?>

        <div class="col-12 col-sm-6 mb-2 pe-sm-1">

            <a href="<?= esc($item['url']) ?>" class="card border-0 shadow-sm h-100 text-decoration-none parent-action-card">

                <div class="card-body py-3 d-flex align-items-center">

                    <span class="parent-action-card__icon me-3"><i class="<?= esc($item['icon'] ?? 'fas fa-circle') ?>"></i></span>

                    <span class="flex-grow-1 min-w-0">

                        <span class="d-block fw-bold text-dark"><?= esc($item['label']) ?></span>

                        <span class="d-block small text-muted text-truncate"><?= esc($item['detail']) ?></span>

                    </span>

                    <?php if (! empty($item['badge'])): ?>

                    <span class="badge text-bg-danger ms-2"><?= (int) $item['badge'] ?></span>

                    <?php endif; ?>

                </div>

            </a>

        </div>

        <?php endforeach; ?>

    </div>

</section>

<style>

.parent-action-card { border-radius: 14px; transition: transform .15s ease, box-shadow .15s ease; }

.parent-action-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,23,42,.12) !important; }

.parent-action-card__icon { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg,#eef2ff,#e0e7ff); color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }

</style>


