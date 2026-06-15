<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
$isParent = (($role ?? '') === 'parent');
$viewFamily = ! empty($view_family);
$returnPath = $return_path ?? 'student/fees';
$showStudent = ! empty($show_student_column);
$summary = $summary ?? ['paid' => 0.0, 'unpaid' => 0.0, 'total_net' => 0.0];
$paymentMonths = $payment_history_months ?? [];
$detailMonths = $fee_detail_months ?? [];

$statusBadge = static function (string $raw): array {
    $s = strtolower(str_replace([' ', '-', '_'], '', $raw));
    if ($s === 'paid') {
        return ['label' => lang('ParentPortal.fees_status_paid'), 'class' => 'success', 'extra' => ''];
    }
    if ($s === 'discounted') {
        return ['label' => lang('ParentPortal.fees_status_discounted'), 'class' => 'info', 'extra' => ''];
    }
    if ($s === 'unpaid') {
        return ['label' => lang('ParentPortal.fees_status_unpaid'), 'class' => 'warning', 'extra' => 'text-dark'];
    }

    return ['label' => ucfirst(trim($raw)) ?: '—', 'class' => 'secondary', 'extra' => ''];
};

$fmtDate = static function ($v): string {
    if ($v === null || $v === '') {
        return '—';
    }
    $s = trim((string) $v);
    if ($s === '' || strpos($s, '0000-00-00') === 0) {
        return '—';
    }
    $t = strtotime($s);

    return $t ? date('d M Y', $t) : esc($s);
};
?>

<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<section class="content parent-subpage-content pt-2 parent-portal-fees">
    <div class="container-fluid px-2 px-md-3">
        <?php if ($isParent): ?>
            <div class="ds-page-layout">
                <aside class="ds-page-layout__filter ds-sticky-filter-mobile" aria-label="<?= esc(lang('ParentPortal.fees_aria_student_selection'), 'attr') ?>">
                    <div class="ds-datesheet-filter">
                        <?= view('frontend/partials/parent_child_selector', [
                            'children'        => $children ?? [],
                            'activeStudentId' => (int) (session('active_student_id') ?? 0),
                            'returnPath'      => $returnPath,
                        ]) ?>
                    </div>
                </aside>

                <div class="ds-page-layout__content" aria-label="<?= esc(lang('ParentPortal.fees_aria_content'), 'attr') ?>">
                    <div class="fee-scope-bar card border-0 shadow-sm mb-3">
                        <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <span class="text-muted small mb-0"><?= esc(lang('ParentPortal.fees_view_scope')) ?></span>
                            <div class="btn-group btn-group-sm fee-scope-toggle" role="group">
                                <a href="<?= esc(base_url('student/fees')) ?>"
                                   class="btn btn-outline-primary <?= ! $viewFamily ? 'active' : '' ?>"><?= esc(lang('ParentPortal.fees_selected_student')) ?></a>
                                <a href="<?= esc(base_url('student/fees?view=family')) ?>"
                                   class="btn btn-outline-primary <?= $viewFamily ? 'active' : '' ?>"><?= esc(lang('ParentPortal.fees_whole_family')) ?></a>
                            </div>
                        </div>
                    </div>

                    <div class="parent-subpage-panel fee-portal-content-panel">
        <?php else: ?>
                    <div class="parent-subpage-panel fee-portal-content-panel">
        <?php endif; ?>
            <div class="parent-subpage-title-row align-items-center">
                <h2 class="parent-subpage-title mb-0"><?= esc(lang('ParentPortal.fees_title')) ?></h2>
                <?php if ($isParent && $viewFamily): ?>
                    <span class="badge text-bg-secondary"><?= esc(lang('ParentPortal.fees_badge_all_children')) ?></span>
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="fee-stat-card fee-stat-card--paid border rounded-lg p-3 h-100">
                        <div class="small text-uppercase fw-bold text-success mb-1"><?= esc(lang('ParentPortal.fees_stat_paid_label')) ?></div>
                        <div class="h4 mb-0 fw-bold"><?= number_to_currency((float) ($summary['paid'] ?? 0), 'PKR') ?></div>
                        <div class="small text-muted mt-1 mb-0"><?= esc(lang('ParentPortal.fees_stat_paid_desc')) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fee-stat-card fee-stat-card--unpaid border rounded-lg p-3 h-100">
                        <div class="small text-uppercase fw-bold text-warning mb-1"><?= esc(lang('ParentPortal.fees_stat_unpaid_label')) ?></div>
                        <div class="h4 mb-0 fw-bold"><?= number_to_currency((float) ($summary['unpaid'] ?? 0), 'PKR') ?></div>
                        <div class="small text-muted mt-1 mb-0"><?= esc(lang('ParentPortal.fees_stat_unpaid_desc')) ?></div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs fee-portal-tabs mb-3" id="feePortalTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-history" data-bs-toggle="tab" href="#pane-history" role="tab" aria-controls="pane-history" aria-selected="true"><?= esc(lang('ParentPortal.fees_tab_history')) ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-detail" data-bs-toggle="tab" href="#pane-detail" role="tab" aria-controls="pane-detail" aria-selected="false"><?= esc(lang('ParentPortal.fees_tab_detail')) ?></a>
                </li>
            </ul>

            <div class="tab-content" id="feePortalTabContent">
                <div class="tab-pane fade show active" id="pane-history" role="tabpanel" aria-labelledby="tab-history">
                    <?php if (empty($paymentMonths)): ?>
                        <p class="text-muted text-center py-4 mb-0"><?= esc(lang('ParentPortal.fees_empty_history')) ?></p>
                    <?php else: ?>
                        <p class="text-muted small mb-3"><?= lang('ParentPortal.fees_help_history') ?></p>
                        <div class="accordion fee-month-accordion" id="accPaymentHistory">
                            <?php foreach ($paymentMonths as $idx => $block): ?>
                                <?php
                                $mid = 'ph-' . preg_replace('/[^a-zA-Z0-9]/', '-', (string) ($block['month_key'] ?? 'm')) . '-' . $idx;
                                $isFirst = ($idx === 0);
                                ?>
                                <div class="card fee-month-card mb-2">
                                    <div class="card-header p-0 bg-white" id="head-<?= esc($mid, 'attr') ?>">
                                        <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center text-decoration-none py-3 px-3 <?= $isFirst ? '' : 'collapsed' ?>"
                                                type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($mid, 'attr') ?>"
                                                aria-expanded="<?= $isFirst ? 'true' : 'false' ?>" aria-controls="<?= esc($mid, 'attr') ?>">
                                            <span class="fw-bold text-dark"><?= esc($block['month_label'] ?? '') ?></span>
                                            <span class="text-nowrap ms-2">
                                                <?php $phCount = count($block['items'] ?? []); ?>
                                                <span class="badge text-bg-light border"><?= (int) $phCount ?> <?= $phCount === 1 ? esc(lang('ParentPortal.fees_payment')) : esc(lang('ParentPortal.fees_payments')) ?></span>
                                                <span class="badge text-bg-success ms-1"><?= number_to_currency((float) ($block['month_total'] ?? 0), 'PKR') ?></span>
                                            </span>
                                        </button>
                                    </div>
                                    <div id="<?= esc($mid, 'attr') ?>" class="collapse <?= $isFirst ? 'show' : '' ?>" aria-labelledby="head-<?= esc($mid, 'attr') ?>">
                                        <div class="card-body pt-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped mb-0">
                                                    <thead>
                                                    <tr>
                                                        <?php if ($showStudent): ?><th><?= esc(lang('ParentPortal.fees_th_student')) ?></th><?php endif; ?>
                                                        <th><?= esc(lang('ParentPortal.fees_th_fee_type')) ?></th>
                                                        <th><?= esc(lang('ParentPortal.fees_th_fee_month')) ?></th>
                                                        <th class="text-end"><?= esc(lang('ParentPortal.fees_th_net')) ?></th>
                                                        <th><?= esc(lang('ParentPortal.fees_th_paid_on')) ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($block['items'] ?? [] as $r): ?>
                                                        <tr>
                                                            <?php if ($showStudent): ?>
                                                                <td><?= esc($r['student_name'] ?? '') ?></td>
                                                            <?php endif; ?>
                                                            <td><?= esc($r['fee_type_name'] ?? '—') ?></td>
                                                            <td><?= esc($r['fee_month'] ?? '—') ?></td>
                                                            <td class="text-end"><?= number_to_currency(((float) ($r['amount'] ?? 0)) - ((float) ($r['discount'] ?? 0)), 'PKR') ?></td>
                                                            <td><?= $fmtDate($r['paid_date'] ?? null) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="pane-detail" role="tabpanel" aria-labelledby="tab-detail">
                    <?php if (empty($detailMonths)): ?>
                        <p class="text-muted text-center py-4 mb-0"><?= esc(lang('ParentPortal.fees_empty_detail')) ?></p>
                    <?php else: ?>
                        <p class="text-muted small mb-3"><?= lang('ParentPortal.fees_help_detail') ?></p>
                        <div class="accordion fee-month-accordion" id="accFeeDetail">
                            <?php foreach ($detailMonths as $idx => $block): ?>
                                <?php
                                $mid = 'fd-' . preg_replace('/[^a-zA-Z0-9]/', '-', (string) ($block['month_key'] ?? 'm')) . '-' . $idx;
                                $isFirst = ($idx === 0);
                                ?>
                                <div class="card fee-month-card mb-2">
                                    <div class="card-header p-0 bg-white" id="head-<?= esc($mid, 'attr') ?>">
                                        <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center text-decoration-none py-3 px-3 <?= $isFirst ? '' : 'collapsed' ?>"
                                                type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($mid, 'attr') ?>"
                                                aria-expanded="<?= $isFirst ? 'true' : 'false' ?>" aria-controls="<?= esc($mid, 'attr') ?>">
                                            <span class="fw-bold text-dark"><?= esc($block['month_label'] ?? '') ?></span>
                                            <span class="text-nowrap ms-2">
                                                <?php $fdCount = count($block['items'] ?? []); ?>
                                                <span class="badge text-bg-light border"><?= (int) $fdCount ?> <?= $fdCount === 1 ? esc(lang('ParentPortal.fees_line')) : esc(lang('ParentPortal.fees_lines')) ?></span>
                                                <span class="badge text-bg-primary ms-1"><?= number_to_currency((float) ($block['month_total'] ?? 0), 'PKR') ?></span>
                                            </span>
                                        </button>
                                    </div>
                                    <div id="<?= esc($mid, 'attr') ?>" class="collapse <?= $isFirst ? 'show' : '' ?>" aria-labelledby="head-<?= esc($mid, 'attr') ?>">
                                        <div class="card-body pt-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped mb-0">
                                                    <thead>
                                                    <tr>
                                                        <?php if ($showStudent): ?><th><?= esc(lang('ParentPortal.fees_th_student')) ?></th><?php endif; ?>
                                                        <th><?= esc(lang('ParentPortal.fees_th_fee_type')) ?></th>
                                                        <th class="text-end"><?= esc(lang('ParentPortal.fees_th_amount')) ?></th>
                                                        <th class="text-end"><?= esc(lang('ParentPortal.fees_th_discount')) ?></th>
                                                        <th class="text-end"><?= esc(lang('ParentPortal.fees_th_net')) ?></th>
                                                        <th><?= esc(lang('ParentPortal.fees_th_status')) ?></th>
                                                        <th><?= esc(lang('ParentPortal.fees_th_due')) ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($block['items'] ?? [] as $r): ?>
                                                        <?php $sb = $statusBadge((string) ($r['status'] ?? '')); ?>
                                                        <tr>
                                                            <?php if ($showStudent): ?>
                                                                <td><?= esc($r['student_name'] ?? '') ?></td>
                                                            <?php endif; ?>
                                                            <td><?= esc($r['fee_type_name'] ?? '—') ?></td>
                                                            <td class="text-end"><?= number_to_currency((float) ($r['amount'] ?? 0), 'PKR') ?></td>
                                                            <td class="text-end"><?= number_to_currency((float) ($r['discount'] ?? 0), 'PKR') ?></td>
                                                            <td class="text-end fw-bold"><?= number_to_currency(((float) ($r['amount'] ?? 0)) - ((float) ($r['discount'] ?? 0)), 'PKR') ?></td>
                                                            <td><span class="badge text-bg-<?=  esc($sb['class'], 'attr') ?> <?= esc($sb['extra'] ?? '', 'attr') ?>"><?= esc($sb['label']) ?></span></td>
                                                            <td><?= $fmtDate($r['due_date'] ?? null) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
