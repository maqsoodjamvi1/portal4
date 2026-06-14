<?php $financeOnly = ! empty($financeOnly); ?>
<!-- KPI Overview -->
<section class="dash-section">
    <div class="dash-section__head">
        <h2 class="dash-section__title"><i class="fas fa-tachometer-alt"></i> <?= esc(lang('SchoolSetup.dashboard_quick_overview')) ?></h2>
        <span class="dash-section__line"></span>
    </div>
    <div class="dash-kpi-grid">
        <?php if (! $financeOnly && hasPermission('admin-db-students')): ?>
        <a href="<?= base_url('admin/students?status=1') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--blue"><i class="fas fa-user-graduate"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Students</div>
                <div class="dash-stat-card__value"><?= number_format((int) ($noOfstudent ?? 0)) ?></div>
                <div class="dash-stat-card__detail">Active enrolled</div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (! $financeOnly && hasPermission('admin-db-teacher')): ?>
        <a href="<?= base_url('admin/users') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--red"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Faculty</div>
                <div class="dash-stat-card__value"><?= number_format((int) ($infoteachers ?? 0)) ?></div>
                <div class="dash-stat-card__detail">Teaching staff</div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (! $financeOnly && hasPermission('admin-db-attendance')): ?>
        <a href="<?= base_url('admin/students_absentees/add') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--amber"><i class="fas fa-clipboard-check"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Today's Attendance</div>
                <div class="dash-stat-card__value"><?= (int) ($attendanceRate ?? 0) ?>%</div>
                <div class="dash-stat-card__detail">P: <?= (int) ($attendance['present'] ?? 0) ?> Â· A: <?= (int) ($attendance['absent'] ?? 0) ?> Â· L: <?= (int) ($attendance['leaves'] ?? 0) ?></div>
                <div class="dash-stat-card__progress"><div class="dash-stat-card__progress-bar" style="width:<?= min(100, (int) ($attendanceRate ?? 0)) ?>%"></div></div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('admin-db-fee-collection') && isset($monthlyFee)):
            $mfc = is_array($monthlyFeeStudentCounts ?? null) ? $monthlyFeeStudentCounts : [];
            $feeTotalStudents  = (int) ($mfc['total_students'] ?? 0);
            $feePaidStudents   = (int) ($mfc['paid_students'] ?? 0);
            $feeUnpaidStudents = (int) ($mfc['unpaid_students'] ?? 0);
            $feeStudentPct     = $feeTotalStudents > 0
                ? (int) round(($feePaidStudents / $feeTotalStudents) * 100)
                : 0;
        ?>
        <a href="<?= base_url('admin/fee-chalan-pay') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--green"><i class="fas fa-money-bill-wave"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label"><?= esc(date('M Y')) ?> Fees</div>
                <div class="dash-stat-card__value"><?= number_format($feeTotalStudents) ?></div>
                <div class="dash-stat-card__detail">
                    <?= number_format($feePaidStudents) ?> paid · <?= number_format($feeUnpaidStudents) ?> unpaid students
                    <span class="d-block small text-muted mt-1">
                        Rs. <?= number_format((float) ($PaidFee_info ?? 0), 0) ?> collected
                        (<?= (int) ($feePaidPct ?? 0) ?>%)
                    </span>
                </div>
                <div class="dash-stat-card__progress"><div class="dash-stat-card__progress-bar" style="width:<?= min(100, $feeStudentPct) ?>%;background:#10b981"></div></div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('admin-db-session') && isset($academic_session)): ?>
        <a href="<?= base_url('admin/academic_session') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--cyan"><i class="far fa-calendar-alt"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Session</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm"><?= esc($academic_session->session_name ?? 'N/A') ?></div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('admin-db-term') && isset($termInfo)): ?>
        <a href="<?= base_url('admin/terms') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--purple"><i class="fas fa-book"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Current Term</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm"><?= esc($termInfo->name ?? 'N/A') ?></div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('admin-db-week') && isset($termWeeksInfo)): ?>
        <a href="<?= base_url('admin/weeks') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--teal"><i class="fas fa-calendar-week"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Current Week</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm"><?= esc($termWeeksInfo->week_name ?? 'N/A') ?></div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('admin-db-exam') && !empty($examsInfo)): ?>
        <a href="<?= base_url('admin/exams') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--orange"><i class="fas fa-file-alt"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Active Exam</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm"><?= esc($examsInfo->exam_name ?? 'N/A') ?></div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (!$isTeacher && ($totalCollection ?? 0) > 0): ?>
        <div class="dash-stat-card" style="cursor:default">
            <div class="dash-stat-card__icon dash-stat-card__icon--indigo"><i class="fas fa-coins"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Collected This Month</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm">Rs. <?= number_format((float) ($totalCollection ?? 0), 0) ?></div>
                <?php if (($expenseInfoTotal ?? 0) > 0): ?>
                <div class="dash-stat-card__detail">Expenses: Rs. <?= number_format((float) $expenseInfoTotal, 0) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
