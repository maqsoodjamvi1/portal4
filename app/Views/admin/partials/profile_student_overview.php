<?php
$info = $student;
$parentInfo = $parent;
$currentClass = $current_class_label ?? null;
$sessionName = $current_session_name ?? null;
$profilePhoto = $profile_photo_html ?? '';
$editUrl = $edit_student_url ?? '#';
$systemName = $schoolinfo->system_name ?? 'School';
$slogan = $schoolinfo->slogan ?? '';
$logo = $schoolinfo->logo ?? '';
$status = (int)($info->status ?? 0);
$statusLabel = $status === 1 ? 'Active' : 'Inactive';
$statusClass = $status === 1 ? 'success' : 'secondary';
?>

<div class="sp-overview card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-start">
            <div class="col-auto text-center sp-photo-wrap">
                <div class="sp-photo mx-auto mb-2 mb-md-0">
                    <?= $profilePhoto ?>
                </div>
                <div class="sp-quick-actions d-none d-md-block mt-2">
                    <a href="<?= esc($editUrl) ?>" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-edit"></i> Edit student
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <h3 class="sp-name mb-1"><?= esc(trim(($info->first_name ?? '') . ' ' . ($info->last_name ?? ''))) ?: 'Student' ?></h3>
                        <div class="sp-meta text-muted small">
                            <?php if (!empty($info->reg_no)): ?>
                                <span class="me-3"><i class="fas fa-id-badge"></i> Reg: <strong><?= esc($info->reg_no) ?></strong></span>
                            <?php endif; ?>
                            <?php if (!empty($info->family_id ?? null)): ?>
                                <span class="me-3"><i class="fas fa-users"></i> Family: <?= esc($info->family_id) ?></span>
                            <?php endif; ?>
                            <span class="badge text-bg-<?=  esc($statusClass) ?>"><?= esc($statusLabel) ?></span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap sp-actions-top">
                        <a href="<?= esc($editUrl) ?>" class="btn btn-primary btn-sm d-md-none me-1"><i class="fas fa-edit"></i> Edit</a>
                        <button type="button" class="btn btn-outline-secondary btn-sm no-print" onclick="window.print()"><i class="fas fa-print"></i></button>
                    </div>
                </div>

                <?php if ($currentClass || $sessionName): ?>
                    <div class="alert alert-light border py-2 px-3 mb-3 sp-session-banner">
                        <i class="fas fa-graduation-cap text-primary"></i>
                        <?php if ($currentClass): ?>
                            <strong><?= esc($currentClass) ?></strong>
                        <?php endif; ?>
                        <?php if ($sessionName): ?>
                            <span class="text-muted mx-1">·</span>
                            <span class="text-muted"><?= esc($sessionName) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-6">
                        <h6 class="text-uppercase text-muted fw-bold small sp-section-title"><i class="fas fa-user me-1"></i> Student</h6>
                        <dl class="row sp-dl small mb-0">
                            <dt class="col-sm-4">CNIC</dt><dd class="col-sm-8"><?= esc($info->std_cnic ?? '—') ?></dd>
                            <dt class="col-sm-4">Date of birth</dt><dd class="col-sm-8"><?= esc($info->date_of_birth ?? '—') ?></dd>
                            <dt class="col-sm-4">Gender</dt><dd class="col-sm-8 text-capitalize"><?= esc($info->gender ?? '—') ?></dd>
                            <dt class="col-sm-4">Admission</dt><dd class="col-sm-8"><?= esc($info->date_of_admission ?? '—') ?></dd>
                        </dl>
                    </div>
                    <div class="col-lg-6 mt-3 mt-lg-0">
                        <h6 class="text-uppercase text-muted fw-bold small sp-section-title"><i class="fas fa-users me-1"></i> Parents</h6>
                        <dl class="row sp-dl small mb-0">
                            <dt class="col-sm-4">Father</dt><dd class="col-sm-8"><?= esc($parentInfo->f_name ?? '—') ?></dd>
                            <dt class="col-sm-4">Father CNIC</dt><dd class="col-sm-8"><?= esc($parentInfo->father_cnic ?? '—') ?></dd>
                            <dt class="col-sm-4">Contact</dt><dd class="col-sm-8"><?= esc($parentInfo->father_contact ?? '—') ?></dd>
                            <dt class="col-sm-4">Mother</dt><dd class="col-sm-8"><?= esc($parentInfo->m_name ?? '—') ?></dd>
                        </dl>
                    </div>
                </div>

                <hr class="my-3">

                <div class="row">
                    <div class="col-lg-12">
                        <h6 class="text-uppercase text-muted fw-bold small sp-section-title"><i class="fas fa-phone-alt me-1"></i> Contact</h6>
                        <dl class="row sp-dl small mb-0">
                            <dt class="col-sm-4 col-lg-2">WhatsApp</dt><dd class="col-sm-8 col-lg-4"><?= esc($parentInfo->whatsapp ?? '—') ?></dd>
                            <dt class="col-sm-4 col-lg-2">Email</dt><dd class="col-sm-8 col-lg-4"><?= esc($parentInfo->father_email ?? '—') ?></dd>
                            <dt class="col-sm-4 col-lg-2">City</dt><dd class="col-sm-8 col-lg-4"><?= esc($parentInfo->city ?? '—') ?></dd>
                            <dt class="col-sm-4 col-lg-2">Address</dt><dd class="col-sm-8 col-lg-10"><?= esc($parentInfo->address_line1 ?? $parentInfo->Address_line1 ?? '—') ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm sp-school-strip no-print">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <?php if (!empty($logo)): ?>
                <div class="col-auto">
                    <img src="<?= esc($logo) ?>" alt="" class="sp-school-logo">
                </div>
            <?php endif; ?>
            <div class="col">
                <div class="fw-bold"><?= esc($systemName) ?></div>
                <?php if ($slogan): ?><div class="small text-muted"><?= esc($slogan) ?></div><?php endif; ?>
            </div>
            <div class="col-auto small text-muted text-md-end">
                <?php if (!empty($schoolinfo->mob_number)): ?>
                    <div><i class="fas fa-phone"></i> <?= esc($schoolinfo->mob_number) ?></div>
                <?php endif; ?>
                <?php if (!empty($schoolinfo->address)): ?>
                    <div><i class="fas fa-map-marker-alt"></i> <?= esc(trim(($schoolinfo->address ?? '') . ', ' . ($schoolinfo->city ?? '') . ', ' . ($schoolinfo->country ?? ''), ' ,')) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
