<?php
$recentAttendance = array_slice($teacherAttendance ?? [], 0, 3);
?>
<section class="dash-section mb-0">
    <div class="dash-panel dash-attendance-hub">
        <div class="dash-panel__head">
            <h3 class="dash-panel__title"><i class="fas fa-user-check"></i> My Attendance</h3>
            <span class="dash-pill" style="font-size:0.7rem"><i class="fas fa-clock"></i> <span id="currentTimeCompact"><?= date('h:i A') ?></span></span>
        </div>
        <div class="dash-panel__body">
            <div id="attendanceStatusContainer" class="mb-3"></div>
            <div class="dash-att-table-wrap">
                <div class="px-3 py-2 border-bottom bg-light"><strong class="text-dark small"><i class="fas fa-history me-1"></i> Recent Records</strong></div>
                <div class="dash-scroll-y dash-scroll-y--sm" id="recentAttendanceList">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if (! empty($recentAttendance)): foreach ($recentAttendance as $att):
                            $statusClass = ($att->status ?? '') === 'late' ? 'warning' : 'success';
                        ?>
                            <tr>
                                <td><?= date('d M', strtotime($att->date)) ?></td>
                                <td><?= $att->checkin ? date('h:i A', strtotime($att->checkin)) : '&mdash;' ?></td>
                                <td><?= $att->checkout ? date('h:i A', strtotime($att->checkout)) : '&mdash;' ?></td>
                                <td><span class="badge text-bg-<?=  $statusClass ?>"><?= ucfirst($att->status ?? 'present') ?></span></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No records found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="text-center mt-3">
                <button class="dash-qr-btn" type="button" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                    <i class="fas fa-qrcode me-1"></i> Scan QR for Attendance
                </button>
            </div>
        </div>
    </div>
</section>

<?php
$showShortcuts = hasPermission('admin-quiz')
    || hasPermission('admin-quiz-assign')
    || hasPermission('admin-add-quiz')
    || hasPermission('admin-health-bmi')
    || hasPermission('admin-weekly-planning')
    || hasPermission('admin-top-level-planning');
?>
<?php if ($showShortcuts): ?>
<section class="dash-section mb-0">
    <div class="dash-section__head">
        <h2 class="dash-section__title"><i class="fas fa-link"></i> Shortcuts</h2>
        <span class="dash-section__line"></span>
    </div>
    <div class="dash-kpi-grid" style="grid-template-columns:1fr 1fr">
        <?php if (hasPermission('admin-quiz') || hasPermission('admin-quiz-assign') || hasPermission('admin-add-quiz')): ?>
        <a href="<?= base_url('admin/quizzes') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--cyan" style="width:48px;min-width:48px;font-size:1rem"><i class="fas fa-clipboard-list"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Quizzes</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm"><?= (int) ($teacherQuizOpen ?? 0) ?> open</div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('admin-health-bmi') || hasPermission('admin-health-bmi-dashboard')): ?>
        <a href="<?= base_url('admin/students/bmi-report') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--red" style="width:48px;min-width:48px;font-size:1rem"><i class="fas fa-heartbeat"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Health BMI</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm">Report</div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('admin-weekly-planning')): ?>
        <a href="<?= base_url('admin/weekly_planning') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--teal" style="width:48px;min-width:48px;font-size:1rem"><i class="fas fa-calendar-week"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Weekly Plan</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm">View</div>
            </div>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('admin-top-level-planning') || hasPermission('admin-add-top-level-planning')): ?>
        <a href="<?= base_url('admin/top_level_planning') ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--purple" style="width:48px;min-width:48px;font-size:1rem"><i class="fas fa-layer-group"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Top Planning</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm">View</div>
            </div>
        </a>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
