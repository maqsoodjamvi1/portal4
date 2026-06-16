<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/employee-profile.css?v=20260616a') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
$userRoles = $userRoles ?? [];
$userRoleNames = $userRoleNames ?? [];
$photoUrl = $photoUrl ?? base_url('resource/adminlte/dist/img/emp-avatar.jpg');
$isActive = ((int) ($user->status ?? 0)) === 1;

$roleLevelMeta = [
    1 => ['label' => 'Super Admin',       'color' => '#c0392b', 'icon' => 'fa-crown'],
    2 => ['label' => 'Administrator',     'color' => '#6f42c1', 'icon' => 'fa-shield-alt'],
    3 => ['label' => 'Manager',           'color' => '#2980b9', 'icon' => 'fa-user-tie'],
    4 => ['label' => 'Teacher / Faculty', 'color' => '#27ae60', 'icon' => 'fa-chalkboard-teacher'],
    5 => ['label' => 'Staff',             'color' => '#e67e22', 'icon' => 'fa-user'],
    6 => ['label' => 'Support',           'color' => '#7f8c8d', 'icon' => 'fa-hands-helping'],
];

$fmtVal = static function ($value) {
    $value = trim((string) ($value ?? ''));
    return $value !== '' && $value !== '0000-00-00' ? esc($value) : '<span class="text-muted">—</span>';
};

$fmtDate = static function ($value) use ($fmtVal) {
    $value = trim((string) ($value ?? ''));
    if ($value === '' || $value === '0000-00-00') {
        return '<span class="text-muted">—</span>';
    }
    $ts = strtotime($value);
    return $ts ? esc(date('d M Y', $ts)) : $fmtVal($value);
};
?>

<?= view('components/page_header', [
    'title' => 'Employee Profile',
    'icon' => 'fas fa-id-badge me-2 text-primary',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => '', 'active' => true],
    ],
]) ?>


<section class="content">
    <div class="container-fluid">

        <!-- Hero card -->
        <div class="card ep-hero-card shadow-sm mb-3">
            <div class="ep-hero-banner">
                <div class="ep-hero-top">
                    <div>
                        <p class="ep-hero-title">Employee Record</p>
                        <span class="text-white-50 small">ID #<?= (int) $user->id ?></span>
                    </div>
                    <div class="ep-hero-actions">
                        <a href="<?= base_url('admin/users/edit/' . $user->id) ?>" class="btn btn-light btn-sm me-1">
                            <i class="fas fa-edit me-1"></i> Edit Profile
                        </a>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="ep-hero-body">
                <div class="ep-photo-wrap">
                    <img class="ep-photo" src="<?= esc($photoUrl) ?>" alt="<?= esc($fullName) ?>">
                </div>
                <div class="ep-identity">
                    <h2 class="ep-name"><?= esc($fullName ?: $user->username) ?></h2>
                    <p class="ep-designation">
                        <i class="fas fa-briefcase me-1 text-muted"></i>
                        <?= esc($user->designation ?? 'Employee') ?>
                    </p>

                    <span class="ep-status-badge <?= $isActive ? 'active' : 'inactive' ?>">
                        <i class="fas fa-circle" style="font-size:0.5rem;"></i>
                        <?= $isActive ? 'Active' : 'Inactive' ?>
                    </span>

                    <?php if (!empty($userRoles)): ?>
                        <div class="ep-role-strip mt-2">
                            <?php foreach ($userRoles as $role):
                                $level = (int) ($role->level ?? 6);
                                $meta = $roleLevelMeta[$level] ?? $roleLevelMeta[6];
                            ?>
                                <span class="ep-role-pill" style="background:<?= esc($meta['color']) ?>;">
                                    <i class="fas <?= esc($meta['icon']) ?>"></i>
                                    <?= esc($role->rolename) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($userRoleNames)): ?>
                        <div class="ep-role-strip mt-2">
                            <?php foreach ($userRoleNames as $roleName): ?>
                                <span class="ep-role-pill" style="background:#3182ce;">
                                    <i class="fas fa-user-tag"></i>
                                    <?= esc($roleName) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ep-quick-bar">
                <div class="ep-quick-item">
                    <span class="ep-quick-label"><i class="fas fa-user me-1"></i>Username</span>
                    <span class="ep-quick-value"><?= esc($user->username) ?></span>
                </div>
                <div class="ep-quick-item">
                    <span class="ep-quick-label"><i class="fas fa-envelope me-1"></i>Email</span>
                    <span class="ep-quick-value">
                        <?php if (!empty($user->email)): ?>
                            <a href="mailto:<?= esc($user->email) ?>"><?= esc($user->email) ?></a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="ep-quick-item">
                    <span class="ep-quick-label"><i class="fas fa-phone me-1"></i>Mobile</span>
                    <span class="ep-quick-value"><?= $fmtVal($user->mobile_no) ?></span>
                </div>
                <div class="ep-quick-item">
                    <span class="ep-quick-label"><i class="fas fa-calendar-check me-1"></i>Joined</span>
                    <span class="ep-quick-value"><?= $fmtDate($user->joining_date ?? '') ?></span>
                </div>
            </div>
        </div>

        <!-- Assigned roles detail -->
        <?php if (!empty($userRoles)): ?>
        <div class="ep-roles-section">
            <p class="section-heading mb-2"><i class="fas fa-shield-alt me-1"></i> Assigned Roles &amp; Access</p>
            <div class="row">
                <?php foreach ($userRoles as $role):
                    $level = (int) ($role->level ?? 6);
                    $meta = $roleLevelMeta[$level] ?? $roleLevelMeta[6];
                ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="ep-role-card">
                        <div class="ep-role-card-head">
                            <div class="ep-role-icon" style="background:<?= esc($meta['color']) ?>;">
                                <i class="fas <?= esc($meta['icon']) ?>"></i>
                            </div>
                            <div>
                                <h4 class="ep-role-card-title">
                                    <?= esc($role->rolename) ?>
                                    <?php if ((int) ($role->issys ?? 0) === 1): ?>
                                        <span class="ep-sys-badge">System</span>
                                    <?php endif; ?>
                                </h4>
                                <div class="ep-role-card-level"><?= esc($meta['label']) ?></div>
                            </div>
                        </div>
                        <?php if (!empty($role->detail)): ?>
                            <p class="ep-role-card-detail"><?= esc($role->detail) ?></p>
                        <?php else: ?>
                            <p class="ep-role-card-detail text-muted mb-0">No additional description for this role.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php elseif (empty($userRoleNames)): ?>
        <div class="ep-no-role-alert mb-3">
            <i class="fas fa-exclamation-circle me-1"></i>
            No role assigned to this employee.
            <a href="<?= base_url('admin/users/edit/' . $user->id) ?>#tab_roles" class="ms-1">Assign a role</a>
        </div>
        <?php endif; ?>

        <!-- Tabbed content -->
        <div class="card ep-main-card">
            <div class="card-header p-2">
                <ul class="nav nav-pills ep-tab-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab ?? 'profile') === 'profile' ? 'active' : '' ?>"
                           href="<?= base_url('admin/users/view/' . $user->id) ?>">
                            <i class="fas fa-user me-1"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab ?? '') === 'subjects' ? 'active' : '' ?>"
                           href="<?= base_url('admin/users/subjects/' . $user->id) ?>">
                            <i class="fas fa-book me-1"></i> Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab ?? '') === 'timetable' ? 'active' : '' ?>"
                           href="<?= base_url('admin/users/timetable/' . $user->id) ?>">
                            <i class="fas fa-clock me-1"></i> Timetable
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab ?? '') === 'salary' ? 'active' : '' ?>"
                           href="<?= base_url('admin/users/salary/' . $user->id) ?>">
                            <i class="fas fa-money-bill me-1"></i> Salary
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab ?? '') === 'qr' ? 'active' : '' ?>"
                           href="<?= base_url('admin/users/view/' . $user->id . '?tab=qr') ?>">
                            <i class="fas fa-qrcode me-1"></i> QR Code
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <?php if (($activeTab ?? 'profile') === 'profile'): ?>

                    <div class="ep-info-grid">
                        <div class="ep-info-panel">
                            <h5 class="ep-info-panel-title"><i class="fas fa-id-card"></i>Personal Information</h5>
                            <dl class="ep-dl">
                                <dt>CNIC</dt>
                                <dd><?= $fmtVal($user->cnic) ?></dd>
                                <dt>Father's Name</dt>
                                <dd><?= $fmtVal($user->f_name) ?></dd>
                                <dt>Date of Birth</dt>
                                <dd><?= $fmtDate($user->dob ?? '') ?></dd>
                                <dt>Gender</dt>
                                <dd><?= !empty($user->gender) ? esc(ucfirst($user->gender)) : '<span class="text-muted">—</span>' ?></dd>
                            </dl>
                        </div>

                        <div class="ep-info-panel">
                            <h5 class="ep-info-panel-title"><i class="fas fa-building"></i>Employment Details</h5>
                            <dl class="ep-dl">
                                <dt>Joining Date</dt>
                                <dd><?= $fmtDate($user->joining_date ?? '') ?></dd>
                                <dt>Qualification</dt>
                                <dd><?= $fmtVal($user->qualification) ?></dd>
                                <dt>Experience</dt>
                                <dd><?= $fmtVal($user->experience) ?></dd>
                                <dt>Address</dt>
                                <dd><?= $fmtVal($user->address) ?></dd>
                            </dl>
                        </div>

                        <div class="ep-info-panel">
                            <h5 class="ep-info-panel-title"><i class="fas fa-university"></i>Bank Details</h5>
                            <dl class="ep-dl">
                                <dt>Bank Name</dt>
                                <dd><?= $fmtVal($user->bank_name) ?></dd>
                                <dt>Account Title</dt>
                                <dd><?= $fmtVal($user->account_title) ?></dd>
                                <dt>Account Number</dt>
                                <dd><?= $fmtVal($user->account_number) ?></dd>
                                <dt>Branch Code</dt>
                                <dd><?= $fmtVal($user->branch_code) ?></dd>
                            </dl>
                        </div>

                        <div class="ep-info-panel">
                            <h5 class="ep-info-panel-title"><i class="fas fa-key"></i>Account &amp; Access</h5>
                            <dl class="ep-dl">
                                <dt>Username</dt>
                                <dd><?= esc($user->username) ?></dd>
                                <dt>Assigned Roles</dt>
                                <dd>
                                    <?php if (!empty($userRoles)): ?>
                                        <?php foreach ($userRoles as $role):
                                            $level = (int) ($role->level ?? 6);
                                            $meta = $roleLevelMeta[$level] ?? $roleLevelMeta[6];
                                        ?>
                                            <span class="badge me-1 mb-1" style="background:<?= esc($meta['color']) ?>;color:#fff;font-weight:600;">
                                                <?= esc($role->rolename) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php elseif (!empty($userRoleNames)): ?>
                                        <?php foreach ($userRoleNames as $roleName): ?>
                                            <span class="badge text-bg-info me-1 mb-1"><?= esc($roleName) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No role assigned</span>
                                    <?php endif; ?>
                                </dd>
                                <dt>Account Status</dt>
                                <dd>
                                    <?php if ($isActive): ?>
                                        <span class="badge text-bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </dd>
                                <dt>Password</dt>
                                <dd>
                                    <?php if (!empty($passwordDisplay)): ?>
                                        <code><?= esc($passwordDisplay) ?></code>
                                    <?php elseif (!empty($passwordIsEncrypted)): ?>
                                        <span class="text-muted"><i class="fas fa-lock me-1"></i>Encrypted — not visible</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>

                <?php elseif ($activeTab === 'subjects'): ?>

                    <?php
                    $classTeacherInfo = $classTeacherInfo ?? [];
                    if (!empty($subjects) || !empty($classTeacherInfo)):
                        $groupedSubjects = [];
                        $totalSubjects = 0;
                        $totalClasses = 0;

                        if (!empty($subjects)) {
                            foreach ($subjects as $subject) {
                                $classKey = $subject->class_name . ' - ' . $subject->section_name;
                                if (!isset($groupedSubjects[$classKey])) {
                                    $groupedSubjects[$classKey] = [];
                                }
                                $groupedSubjects[$classKey][] = $subject;
                                $totalSubjects++;
                            }
                            $totalClasses = count($groupedSubjects);
                        }

                        $profileStats = $teacherProfileStats ?? null;
                        if (is_array($profileStats)) {
                            $totalSubjects         = (int) ($profileStats['totalSubjects'] ?? $totalSubjects);
                            $totalClasses          = (int) ($profileStats['totalClasses'] ?? $totalClasses);
                            $totalClassTeacher     = (int) ($profileStats['totalClassIncharges'] ?? count($classTeacherInfo));
                            $totalResponsibilities = (int) ($profileStats['totalResponsibilities'] ?? ($totalClasses + $totalClassTeacher));
                        } else {
                            $totalClassTeacher     = count($classTeacherInfo);
                            $totalResponsibilities = $totalClasses + $totalClassTeacher;
                        }
                    ?>

                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= $totalSubjects ?></h3>
                                    <p>Total Subjects Taught</p>
                                </div>
                                <div class="icon"><i class="fas fa-book"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?= $totalClasses ?></h3>
                                    <p>Classes Teaching</p>
                                </div>
                                <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?= $totalClassTeacher ?></h3>
                                    <p>Class Incharges</p>
                                </div>
                                <div class="icon"><i class="fas fa-user-tie"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?= (int) ($totalResponsibilities ?? ($totalClasses + $totalClassTeacher)) ?></h3>
                                    <p>Total Responsibilities</p>
                                </div>
                                <div class="icon"><i class="fas fa-tasks"></i></div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($classTeacherInfo)): ?>
                    <div class="card card-outline card-warning mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-tie me-2"></i>
                                Class Incharge Assignments
                                <span class="badge text-bg-warning ms-2"><?= count($classTeacherInfo) ?> Class<?= count($classTeacherInfo) > 1 ? 'es' : '' ?></span>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($classTeacherInfo as $classTeacher): ?>
                                <div class="col-md-4">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-chalkboard"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text"><?= esc($classTeacher->class_name) ?> - <?= esc($classTeacher->section_name) ?></span>
                                            <span class="info-box-number">
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-alt me-1"></i>
                                                    Since <?= date('d M Y', strtotime($classTeacher->created_date)) ?>
                                                </small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($groupedSubjects)): ?>
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-book me-2"></i>
                                Subjects Taught
                                <span class="badge text-bg-primary ms-2"><?= $totalSubjects ?> Subjects in <?= $totalClasses ?> Classes</span>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($groupedSubjects as $classSection => $classSubjects): ?>
                                <div class="col-md-6">
                                    <div class="card card-outline card-primary">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title">
                                                <i class="fas fa-chalkboard me-2 text-primary"></i>
                                                <?= esc($classSection) ?>
                                            </h5>
                                            <div class="card-tools">
                                                <span class="badge text-bg-primary"><?= count($classSubjects) ?> Subject<?= count($classSubjects) > 1 ? 's' : '' ?></span>
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                                            </div>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-hover mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th style="width:40px">#</th>
                                                        <th>Subject Name</th>
                                                        <th style="width:120px">Assigned Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($classSubjects as $index => $subject): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><strong><?= esc($subject->subject_name) ?></strong></td>
                                                        <td>
                                                            <small class="text-muted">
                                                                <i class="far fa-calendar-alt me-1"></i>
                                                                <?= date('d M Y', strtotime($subject->created_date)) ?>
                                                            </small>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            No subjects or class incharges assigned to this teacher.
                        </div>
                    <?php endif; ?>

                <?php elseif ($activeTab === 'qr'): ?>

                    <div class="card border-0 shadow-none">
                        <div class="card-header bg-white px-0 pt-0">
                            <h3 class="card-title">
                                <i class="fas fa-qrcode me-2"></i>
                                QR Code for <?= esc($fullName) ?>
                            </h3>
                            <div class="card-tools">
                                <?php if (isset($qr) && $qr): ?>
                                    <a href="<?= base_url('admin/qr/view/' . $user->id) ?>" class="btn btn-primary btn-sm" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="<?= base_url('admin/qr/print/' . $user->id) ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url('admin/qr/generate/' . $user->id) ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus-circle"></i> Generate QR Code
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body text-center px-0">
                            <?php if (isset($qr) && $qr): ?>
                                <div class="mb-3">
                                    <img src="<?= base_url('admin/qr/view/' . $user->id) ?>"
                                         alt="QR Code for <?= esc($fullName) ?>"
                                         style="max-width:300px;border:1px solid #ddd;padding:20px;border-radius:10px;">
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6 offset-md-3">
                                        <table class="table table-bordered">
                                            <tr><th>Employee Name</th><td><?= esc($fullName) ?></td></tr>
                                            <tr><th>Employee ID</th><td><?= esc($user->id) ?></td></tr>
                                            <tr><th>QR Code Value</th><td><small class="text-muted"><?= esc($qr->qr_code ?? '') ?></small></td></tr>
                                            <?php if (isset($qr->generated_at)): ?>
                                            <tr><th>Generated On</th><td><?= date('d M Y H:i', strtotime($qr->generated_at)) ?></td></tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    <?php if (isset($qr->is_active) && $qr->is_active): ?>
                                                        <span class="badge text-bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge text-bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <?php if (!empty($recent_attendance)): ?>
                                <div class="mt-4 text-start">
                                    <h5>Recent Attendance</h5>
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Check In</th>
                                                <th>Check Out</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_attendance as $att): ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($att->date)) ?></td>
                                                <td><?= $att->checkin ? date('H:i', strtotime($att->checkin)) : '-' ?></td>
                                                <td><?= $att->checkout ? date('H:i', strtotime($att->checkout)) : '-' ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'secondary';
                                                    if ($att->status === 'present') $statusClass = 'success';
                                                    elseif ($att->status === 'late') $statusClass = 'warning';
                                                    elseif ($att->status === 'absent') $statusClass = 'danger';
                                                    ?>
                                                    <span class="badge text-bg-<?=  $statusClass ?>"><?= ucfirst($att->status ?? 'unknown') ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No QR code generated for this employee yet. Click "Generate QR Code" above to create one.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($activeTab === 'timetable'): ?>

                    <?php if (!empty($schedule)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="bg-light">
                                        <th style="width:120px;">Day</th>
                                        <?php for ($p = 1; $p <= 8; $p++): ?>
                                            <th>Period <?= $p ?></th>
                                        <?php endfor; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($days as $day): ?>
                                    <tr>
                                        <td class="fw-bold bg-light"><?= $day ?></td>
                                        <?php
                                        $daySlots = $schedule[$day] ?? [];
                                        for ($i = 0; $i < 8; $i++):
                                            $slot = $daySlots[$i] ?? null;
                                        ?>
                                        <td class="align-middle">
                                            <?php if ($slot): ?>
                                                <div class="text-primary fw-bold"><?= esc($slot->subject_name) ?></div>
                                                <div class="small"><?= esc($slot->class_name) ?> - <?= esc($slot->section_name) ?></div>
                                                <div class="small text-muted">
                                                    <?= date('h:i A', strtotime($slot->start_time)) ?> -
                                                    <?= date('h:i A', strtotime($slot->end_time)) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php endfor; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i> No timetable assigned to this teacher.
                        </div>
                    <?php endif; ?>

                <?php elseif ($activeTab === 'salary'): ?>

                    <?php
                    $currentSalary = $currentSalary ?? ($user->basic_salary ?? 0);
                    $salarySlips = $salarySlips ?? [];
                    $salaryHistory = $salaryHistory ?? [];
                    $employeeRules = $employeeRules ?? null;
                    $salaryReadOnly = ! empty($salaryReadOnly);
                    $applyDeduction = ! $employeeRules || (int) ($employeeRules->apply_deduction ?? 1) !== 0;
                    $bonusEligible = ! $employeeRules || (int) ($employeeRules->bonus_eligible ?? 1) !== 0;
                    $securityWaived = $employeeRules && (int) ($employeeRules->security_deduction_waived ?? 0) === 1;
                    ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card card-outline card-primary h-100">
                                <div class="card-header">
                                    <h3 class="card-title mb-0"><i class="fas fa-wallet me-1"></i> Basic Salary</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-2">Current monthly basic salary</p>
                                    <h4 class="text-primary mb-0"><?= number_format((float) $currentSalary, 2) ?></h4>
                                    <?php if (! $salaryReadOnly): ?>
                                    <form id="salaryUpdateForm" class="mt-3">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= (int) $user->id ?>">
                                        <div class="form-group">
                                            <label>New Basic Salary</label>
                                            <input type="number" step="0.01" min="0" class="form-control" name="basic_salary"
                                                   value="<?= esc($currentSalary) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Reason for Change</label>
                                            <input type="text" class="form-control" name="increment_reason"
                                                   placeholder="e.g. Annual increment">
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Salary
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary h-100">
                                <div class="card-header">
                                    <h3 class="card-title mb-0"><i class="fas fa-sliders-h me-1"></i> Payroll Rules</h3>
                                </div>
                                <div class="card-body">
                                    <?php if ($salaryReadOnly): ?>
                                    <dl class="mb-0">
                                        <dt>Absence deductions</dt>
                                        <dd><?= $applyDeduction ? 'Enabled' : 'Disabled' ?></dd>
                                        <dt>Attendance bonus</dt>
                                        <dd><?= $bonusEligible ? 'Eligible' : 'Not eligible' ?></dd>
                                        <dt>Security deduction</dt>
                                        <dd><?= $securityWaived ? 'Waived' : 'Applied' ?></dd>
                                        <dt>Custom daily salary</dt>
                                        <dd>
                                            <?php if (! empty($employeeRules->custom_daily_salary)): ?>
                                                <?= number_format((float) $employeeRules->custom_daily_salary, 2) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Uses basic salary / working days</span>
                                            <?php endif; ?>
                                        </dd>
                                        <?php if (! empty($employeeRules->notes)): ?>
                                        <dt>Notes</dt>
                                        <dd><?= esc($employeeRules->notes) ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                    <?php else: ?>
                                    <form id="salaryRulesForm">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= (int) $user->id ?>">
                                        <div class="form-check form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="apply_deduction"
                                                   name="apply_deduction" value="1"
                                                <?= $applyDeduction ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="apply_deduction">Apply absence deductions</label>
                                        </div>
                                        <div class="form-check form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="bonus_eligible"
                                                   name="bonus_eligible" value="1"
                                                <?= $bonusEligible ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="bonus_eligible">Eligible for attendance bonus</label>
                                        </div>
                                        <div class="form-check form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="security_deduction_waived"
                                                   name="security_deduction_waived" value="1"
                                                <?= $securityWaived ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="security_deduction_waived">Waive security deduction</label>
                                        </div>
                                        <div class="form-group">
                                            <label>Custom Daily Salary (optional override)</label>
                                            <input type="number" step="0.01" min="0" class="form-control" name="custom_daily_salary"
                                                   value="<?= esc($employeeRules->custom_daily_salary ?? '') ?>"
                                                   placeholder="Leave blank to use basic / working days">
                                        </div>
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <textarea class="form-control" name="notes" rows="2"><?= esc($employeeRules->notes ?? '') ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="fas fa-save me-1"></i> Save Rules
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-1"></i> Salary Slips</h5>
                        <a href="<?= base_url('admin/users/export/salary/' . $user->id) ?>" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-download me-1"></i> Export CSV
                        </a>
                    </div>

                    <?php if (!empty($salarySlips)): ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Period</th>
                                        <th>Slip No</th>
                                        <th>Net Salary</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salarySlips as $slip): ?>
                                    <tr>
                                        <td><?= date('F Y', strtotime($slip->year . '-' . str_pad($slip->month, 2, '0', STR_PAD_LEFT) . '-01')) ?></td>
                                        <td><?= esc($slip->slip_no) ?></td>
                                        <td><?= number_format((float) $slip->net_salary, 2) ?></td>
                                        <td>
                                            <?php if ($slip->payment_status === 'paid'): ?>
                                                <span class="badge text-bg-success">Paid</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('admin/users/view-salary-slip/' . $user->id . '/' . $slip->slip_id) ?>"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-file-invoice me-1"></i> View Slip
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i> No salary slips generated yet.
                        </div>
                    <?php endif; ?>

                    <h5 class="mb-2"><i class="fas fa-history me-1"></i> Salary History</h5>
                    <?php if (!empty($salaryHistory)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Old Salary</th>
                                        <th>New Salary</th>
                                        <th>Change</th>
                                        <th>Reason</th>
                                        <th>Approved By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salaryHistory as $hist): ?>
                                    <tr>
                                        <td><?= esc($hist->increment_date ?? '') ?></td>
                                        <td><?= number_format((float) ($hist->old_basic_salary ?? 0), 2) ?></td>
                                        <td><?= number_format((float) ($hist->new_basic_salary ?? 0), 2) ?></td>
                                        <td>
                                            <?php $delta = (float) ($hist->increment_amount ?? 0); ?>
                                            <span class="<?= $delta >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= ($delta >= 0 ? '+' : '') . number_format($delta, 2) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($hist->increment_reason ?? '—') ?></td>
                                        <td><?= esc(trim(($hist->first_name ?? '') . ' ' . ($hist->approver_name ?? ''))) ?: '—' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">
                            <i class="fas fa-info-circle me-2"></i> No salary change history recorded.
                        </div>
                    <?php endif; ?>

                    <?php if (! $salaryReadOnly): ?>
                    <script>
                    (function () {
                        function postForm(form, url, okMsg) {
                            form.addEventListener('submit', function (e) {
                                e.preventDefault();
                                var btn = form.querySelector('[type="submit"]');
                                if (btn) btn.disabled = true;
                                fetch(url, {
                                    method: 'POST',
                                    body: new FormData(form),
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    if (data.success) {
                                        alert(data.msg || okMsg);
                                        window.location.reload();
                                    } else {
                                        alert(data.msg || 'Request failed');
                                    }
                                })
                                .catch(function () { alert('Request failed'); })
                                .finally(function () { if (btn) btn.disabled = false; });
                            });
                        }
                        var updateForm = document.getElementById('salaryUpdateForm');
                        var rulesForm = document.getElementById('salaryRulesForm');
                        if (updateForm) {
                            postForm(updateForm, '<?= base_url('admin/users/update-salary') ?>', 'Salary updated');
                        }
                        if (rulesForm) {
                            postForm(rulesForm, '<?= base_url('admin/users/save-salary-rules') ?>', 'Rules saved');
                        }
                    })();
                    </script>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?= $this->endSection() ?>
