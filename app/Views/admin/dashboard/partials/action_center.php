<?php
/** @var list<array{label: string, detail: string, url: string, icon: string, badge: int}> $actionCenter */
$actionCenter = $actionCenter ?? [];
if ($actionCenter === []) {
    return;
}
?>
<section class="dash-section dash-action-center">
    <div class="dash-section__head">
        <h2 class="dash-section__title"><i class="fas fa-bolt"></i> <?= esc(lang('SchoolSetup.action_center_title')) ?></h2>
        <span class="dash-section__line"></span>
    </div>
    <div class="dash-action-center__grid">
        <?php foreach ($actionCenter as $item): ?>
        <a href="<?= esc($item['url']) ?>" class="dash-action-card">
            <div class="dash-action-card__icon"><i class="<?= esc($item['icon'] ?? 'fas fa-circle') ?>"></i></div>
            <div class="dash-action-card__body">
                <div class="dash-action-card__label"><?= esc($item['label']) ?></div>
                <div class="dash-action-card__detail text-muted small"><?= esc($item['detail']) ?></div>
            </div>
            <?php if (! empty($item['badge'])): ?>
            <span class="badge text-bg-danger dash-action-card__badge"><?= (int) $item['badge'] ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>
