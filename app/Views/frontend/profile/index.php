<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
$isParent = (($role ?? '') === 'parent');
$student  = $student ?? [];
$s        = static function ($k) use ($student) {
    return isset($student[$k]) && $student[$k] !== null && $student[$k] !== ''
        ? esc((string) $student[$k])
        : '—';
};
$fmtDate = static function ($d) {
    if (! $d || strpos((string) $d, '0000-00-00') === 0) {
        return '—';
    }
    $t = strtotime((string) $d);

    return $t ? esc(date('d M Y', $t)) : esc((string) $d);
};
$dobOut      = $fmtDate($student['date_of_birth'] ?? '');
$admitOut    = $fmtDate($student['date_of_admission'] ?? '');
$grDateOut   = $fmtDate($student['gr_date'] ?? '');
$fullName    = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
$fullNameEsc = $fullName !== '' ? esc($fullName) : '—';
$classLine   = trim(implode(' · ', array_filter([
    $s('class_name') !== '—' ? (string) $student['class_name'] : '',
    $s('section_name') !== '—' ? (string) $student['section_name'] : '',
])));
$classLineEsc = $classLine !== '' ? esc($classLine) : '—';
$initials     = '';
if ($fullName !== '') {
    $parts = preg_split('/\s+/u', $fullName, -1, PREG_SPLIT_NO_EMPTY);
    $initials = strtoupper(mb_substr($parts[0] ?? '', 0, 1));
    if (isset($parts[1])) {
        $initials .= strtoupper(mb_substr($parts[1], 0, 1));
    }
}
?>
<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<section class="content parent-subpage-content pt-2 portal-profile-page">
    <div class="container-fluid px-2 px-md-3">
        <?php if ($isParent): ?>
            <div class="ds-page-layout">
                <aside class="ds-page-layout__filter ds-sticky-filter-mobile" aria-label="Student selection">
                    <div class="ds-datesheet-filter">
                        <?= view('frontend/partials/parent_child_selector', [
                            'children'        => $children ?? [],
                            'activeStudentId' => (int) ($active_student_id ?? 0),
                            'returnPath'      => $return_path ?? 'student/profile',
                        ]) ?>
                    </div>
                </aside>
                <div class="ds-page-layout__content">
        <?php endif; ?>

        <div class="parent-subpage-panel portal-profile-panel">
            <div class="parent-subpage-title-row portal-profile-title-row">
                <div>
                    <h2 class="parent-subpage-title mb-0">
                        <i class="fa fa-address-card text-primary me-2" aria-hidden="true"></i> Student profile
                    </h2>
                    <p class="portal-profile-lead mb-0">Official record summary — updates are managed by the school office.</p>
                </div>
                <a href="<?= esc(url_to('dashboard')) ?>" class="btn btn-outline-secondary btn-sm portal-profile-back">
                    <i class="fa fa-arrow-left me-1" aria-hidden="true"></i> Dashboard
                </a>
            </div>

            <div class="portal-profile-hero">
                <div class="portal-profile-hero__bg" aria-hidden="true"></div>
                <div class="portal-profile-hero__inner">
                    <div class="portal-profile-avatar-wrap">
                        <?php if (! empty($photo_url)): ?>
                            <img src="<?= esc($photo_url) ?>" alt="" class="portal-profile-avatar" width="168" height="168">
                        <?php else: ?>
                            <div class="portal-profile-avatar portal-profile-avatar--placeholder" aria-hidden="true">
                                <?php if ($initials !== ''): ?>
                                    <span><?= esc($initials) ?></span>
                                <?php else: ?>
                                    <i class="fa fa-user"></i>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="portal-profile-hero__text">
                        <p class="portal-profile-eyebrow">Registered learner</p>
                        <h3 class="portal-profile-name"><?= $fullNameEsc ?></h3>
                        <div class="portal-profile-meta">
                            <?php if ($s('reg_no') !== '—'): ?>
                                <span class="portal-profile-pill"><i class="fa fa-hashtag me-1" aria-hidden="true"></i><?= $s('reg_no') ?></span>
                            <?php endif; ?>
                            <?php if ($s('gr_no') !== '—'): ?>
                                <span class="portal-profile-pill portal-profile-pill--muted"><i class="fa fa-bookmark me-1" aria-hidden="true"></i>GR <?= $s('gr_no') ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="portal-profile-sub mb-0">
                            <i class="fa fa-school me-2 text-primary" aria-hidden="true"></i><?= $classLineEsc ?>
                            <?php if ($s('campus_name') !== '—'): ?>
                                <span class="portal-profile-sub__sep" aria-hidden="true"></span>
                                <?= $s('campus_name') ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="row portal-profile-kpis">
                <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                    <div class="portal-profile-kpi">
                        <span class="portal-profile-kpi__icon" aria-hidden="true"><i class="fa fa-chalkboard-teacher"></i></span>
                        <div>
                            <div class="portal-profile-kpi__label">Class</div>
                            <div class="portal-profile-kpi__value"><?= $s('class_name') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                    <div class="portal-profile-kpi">
                        <span class="portal-profile-kpi__icon" aria-hidden="true"><i class="fa fa-layer-group"></i></span>
                        <div>
                            <div class="portal-profile-kpi__label">Section</div>
                            <div class="portal-profile-kpi__value"><?= $s('section_name') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
                    <div class="portal-profile-kpi">
                        <span class="portal-profile-kpi__icon" aria-hidden="true"><i class="fa fa-birthday-cake"></i></span>
                        <div>
                            <div class="portal-profile-kpi__label">Date of birth</div>
                            <div class="portal-profile-kpi__value"><?= $dobOut ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="portal-profile-kpi">
                        <span class="portal-profile-kpi__icon" aria-hidden="true"><i class="fa fa-venus-mars"></i></span>
                        <div>
                            <div class="portal-profile-kpi__label">Gender</div>
                            <div class="portal-profile-kpi__value"><?= $s('gender') !== '—' ? esc(ucfirst((string) $student['gender'])) : '—' ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="portal-profile-card h-100">
                        <h4 class="portal-profile-card__title"><i class="fa fa-user-graduate me-2 text-primary" aria-hidden="true"></i> Personal</h4>
                        <dl class="portal-profile-dl">
                            <div><dt>Full name</dt><dd><?= $fullNameEsc ?></dd></div>
                            <div><dt>CNIC / B-Form</dt><dd><?= $s('std_cnic') ?></dd></div>
                            <div><dt>Religion</dt><dd><?= $s('religion') ?></dd></div>
                            <div><dt>Previous school</dt><dd><?= $s('previous_school') ?></dd></div>
                            <div><dt>Previous city</dt><dd><?= $s('ps_city') ?></dd></div>
                        </dl>
                    </div>
                </div>
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="portal-profile-card h-100">
                        <h4 class="portal-profile-card__title"><i class="fa fa-calendar-check me-2 text-primary" aria-hidden="true"></i> Enrollment</h4>
                        <dl class="portal-profile-dl">
                            <div><dt>Admission date</dt><dd><?= $admitOut ?></dd></div>
                            <div><dt>GR number</dt><dd><?= $s('gr_no') ?></dd></div>
                            <div><dt>GR date</dt><dd><?= $grDateOut ?></dd></div>
                            <div><dt>Campus</dt><dd><?= $s('campus_name') ?></dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="portal-profile-card h-100">
                        <h4 class="portal-profile-card__title"><i class="fa fa-users me-2 text-primary" aria-hidden="true"></i> Family &amp; contact</h4>
                        <dl class="portal-profile-dl">
                            <div><dt>Father</dt><dd><?= $s('father_name') ?></dd></div>
                            <div><dt>Mother</dt><dd><?= $s('mother_name') ?></dd></div>
                            <div><dt>Father contact</dt><dd><?= $s('father_contact') ?></dd></div>
                            <div><dt>Mother contact</dt><dd><?= $s('mother_contact') ?></dd></div>
                            <div><dt>Address</dt><dd><?php
                                $addr = trim(implode(', ', array_filter([
                                    (string) ($student['address_line1'] ?? ''),
                                    (string) ($student['city'] ?? ''),
                                ])));
                                echo $addr !== '' ? esc($addr) : '—';
                            ?></dd></div>
                        </dl>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="portal-profile-card portal-profile-card--notice h-100">
                        <h4 class="portal-profile-card__title"><i class="fa fa-info-circle me-2" aria-hidden="true"></i> Need a change?</h4>
                        <p class="portal-profile-notice mb-2">
                            Address, phone numbers, documents, and other registration details are updated through the school office to keep records accurate and secure.
                        </p>
                        <p class="portal-profile-notice mb-0 small text-muted">
                            If something looks wrong, please reach out to your campus administration with your child’s registration number.
                        </p>
                    </div>
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
