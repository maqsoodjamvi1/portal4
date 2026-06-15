<?php if (! empty($portalNotices) && is_array($portalNotices)): ?>
<div class="card parent-dash-notices mb-3">
    <div class="card-body py-3">
        <h6 class="mb-2"><i class="fas fa-bullhorn text-primary me-1"></i> <?= esc(lang('ParentPortal.notices_title')) ?></h6>
        <ul class="list-unstyled mb-0">
            <?php foreach ($portalNotices as $notice): ?>
            <li class="mb-2 pb-2 border-bottom">
                <strong><?= esc($notice['title'] ?? '') ?></strong>
                <?php if (! empty($notice['date'])): ?>
                    <small class="text-muted d-block"><?= esc($notice['date']) ?></small>
                <?php endif; ?>
                <?php if (! empty($notice['body'])): ?>
                    <div class="text-muted small mt-1"><?= esc($notice['body']) ?></div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
