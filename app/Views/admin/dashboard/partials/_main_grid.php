<?php $teacherAttendanceOnly = ! empty($teacherAttendanceOnly); ?>
<!-- Main two-column: Attendance + Sidebar -->
<div class="dash-main-grid<?= $teacherAttendanceOnly ? ' dash-main-grid--teacher' : '' ?>">
    <div class="dash-stack">

        <!-- Staff / Teacher Attendance -->
        <section class="dash-section mb-0">
            <div class="dash-panel dash-attendance-hub">
                <div class="dash-panel__head">
                    <h3 class="dash-panel__title">
                        <i class="fas <?= $showEmployeeAttendance ? 'fa-users' : 'fa-user-check' ?>"></i>
                        <?= $showEmployeeAttendance ? 'Staff Attendance Today' : 'My Attendance' ?>
                    </h3>
                    <span class="dash-pill" style="font-size:0.7rem"><i class="fas fa-clock"></i> <span id="currentTimeCompact"><?= date('h:i A') ?></span></span>
                </div>
                <div class="dash-panel__body">
                    <?php if ($showEmployeeAttendance): ?>
                    <div class="row mb-3">
                        <div class="col-6 col-md-3 mb-2"><div class="dash-att-stat"><div class="dash-att-stat__num"><?= (int) ($totalEmployees ?? 0) ?></div><div class="dash-att-stat__lbl">Total Staff</div></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="dash-att-stat"><div class="dash-att-stat__num text-success"><?= (int) ($presentCount ?? 0) ?></div><div class="dash-att-stat__lbl">Present</div></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="dash-att-stat"><div class="dash-att-stat__num text-warning"><?= (int) ($lateCount ?? 0) ?></div><div class="dash-att-stat__lbl">Late</div></div></div>
                        <div class="col-6 col-md-3 mb-2"><div class="dash-att-stat"><div class="dash-att-stat__num text-danger"><?= max(0, (int) ($totalEmployees ?? 0) - (int) ($presentCount ?? 0)) ?></div><div class="dash-att-stat__lbl">Absent</div></div></div>
                    </div>
                    <div class="dash-att-table-wrap mb-3">
                        <div class="px-3 py-2 border-bottom bg-light"><strong class="text-dark small"><i class="fas fa-user-check text-success me-1"></i> Present (<?= count($teacherAttendance ?? []) ?>)</strong></div>
                        <div class="dash-scroll-y">
                            <table class="table table-sm table-hover mb-0">
                                <thead><tr><th>Employee</th><th>In</th><th>Out</th><th>Status</th><th>Duration</th></tr></thead>
                                <tbody>
                                <?php if (!empty($teacherAttendance)): foreach ($teacherAttendance as $emp):
                                    $statusClass = ($emp->status ?? '') === 'late' ? 'warning' : 'success';
                                ?>
                                    <tr>
                                        <td><strong><?= esc($emp->first_name . ' ' . $emp->last_name) ?></strong><br><small class="text-muted"><?= esc($emp->designation ?? '') ?></small></td>
                                        <td><?= $emp->checkin ? date('h:i A', strtotime($emp->checkin)) : '—' ?></td>
                                        <td><?= $emp->checkout ? date('h:i A', strtotime($emp->checkout)) : '<span class="badge text-bg-warning">Active</span>' ?></td>
                                        <td><span class="badge text-bg-<?=  $statusClass ?>"><?= ucfirst($emp->status ?? 'present') ?></span></td>
                                        <td><?php if ($emp->checkin && $emp->checkout): $h = floor($emp->lc_duration / 60); $m = $emp->lc_duration % 60; echo "{$h}h {$m}m"; else: ?>—<?php endif; ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No attendance records today</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if (!empty($absentEmployees)): ?>
                    <div class="dash-att-table-wrap">
                        <div class="px-3 py-2 border-bottom bg-light"><strong class="text-dark small"><i class="fas fa-user-clock text-danger me-1"></i> Absent (<?= count($absentEmployees) ?>)</strong></div>
                        <div class="dash-scroll-y dash-scroll-y--sm">
                            <table class="table table-sm mb-0"><thead><tr><th>Employee</th><th>Designation</th></tr></thead><tbody>
                            <?php foreach ($absentEmployees as $emp): ?>
                                <tr><td><strong><?= esc($emp->first_name . ' ' . $emp->last_name) ?></strong></td><td><small class="text-muted"><?= esc($emp->designation ?? '') ?></small></td></tr>
                            <?php endforeach; ?>
                            </tbody></table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <div id="attendanceStatusContainer" class="mb-3"></div>
                    <div class="dash-att-table-wrap">
                        <div class="px-3 py-2 border-bottom bg-light"><strong class="text-dark small"><i class="fas fa-history me-1"></i> Recent Records</strong></div>
                        <div class="dash-scroll-y" id="recentAttendanceList">
                            <table class="table table-sm table-hover mb-0">
                                <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Duration</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php if (!empty($teacherAttendance)): foreach ($teacherAttendance as $att):
                                    $sc = ($att->status ?? '') === 'late' ? 'warning' : 'success';
                                ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($att->date)) ?></td>
                                        <td><?= $att->checkin ? date('h:i A', strtotime($att->checkin)) : '—' ?></td>
                                        <td><?= $att->checkout ? date('h:i A', strtotime($att->checkout)) : '—' ?></td>
                                        <td><?php if ($att->checkin && $att->checkout): $h = floor($att->lc_duration / 60); $m = $att->lc_duration % 60; echo "{$h}h {$m}m"; else: ?>—<?php endif; ?></td>
                                        <td><span class="badge text-bg-<?=  $sc ?>"><?= ucfirst($att->status ?? 'present') ?></span></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No records found</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="dash-qr-btn" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                            <i class="fas fa-qrcode me-1"></i> Scan QR for Attendance
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Pending student attendance -->
        <?php if (! $teacherAttendanceOnly && hasPermission('admin-db-attendance')): ?>
        <section class="dash-section mb-0">
            <div class="dash-panel">
                <div class="dash-panel__head">
                    <h3 class="dash-panel__title"><i class="fas fa-exclamation-circle text-warning"></i> Pending Attendance — <?= date('D j M Y', strtotime($attendanceDate ?? date('Y-m-d'))) ?></h3>
                    <span class="dash-pending-badge <?= empty($pendingAttendance) ? 'dash-pending-badge--ok' : '' ?>">
                        <?= empty($pendingAttendance) ? '<i class="fas fa-check"></i> All done' : (int) ($pendingCount ?? 0) . ' pending' ?>
                    </span>
                </div>
                <div class="dash-panel__body dash-panel__body--flush">
                    <?php if (!empty($pendingAttendance)): ?>
                    <div class="dash-scroll-y" style="max-height:320px">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Class</th><th>Teacher</th><th class="text-center">Str</th><th class="text-center">P</th><th class="text-center">A</th><th class="text-center">L</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($pendingAttendance as $row): ?>
                                <tr>
                                    <td><strong><?= esc(($row['class_name'] ?? '') . ' - ' . ($row['section_name'] ?? '')) ?></strong></td>
                                    <td class="small"><?= esc($row['teacher_name'] ?? '—') ?></td>
                                    <td class="text-center"><?= (int) ($row['strength'] ?? 0) ?></td>
                                    <td class="text-center"><span class="dash-mini-stat dash-mini-stat--p"><?= (int) ($row['present_count'] ?? 0) ?></span></td>
                                    <td class="text-center"><span class="dash-mini-stat dash-mini-stat--a"><?= (int) ($row['absent_count'] ?? 0) ?></span></td>
                                    <td class="text-center"><span class="dash-mini-stat dash-mini-stat--l"><?= (int) ($row['leave_count'] ?? 0) ?></span></td>
                                    <td><a class="btn btn-primary btn-sm btn-sm" href="<?= base_url('admin/students_absentees/add?cls_sec_id=' . urlencode($row['cls_sec_id']) . '&date=' . urlencode($attendanceDate ?? date('Y-m-d'))) ?>">Mark</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="dash-empty"><i class="fas fa-check-circle text-success"></i> All classes have marked attendance for today.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

    </div><!-- /left stack -->

    <?php if (! $teacherAttendanceOnly): ?>
    <!-- Right sidebar -->
    <div class="dash-stack">
        <?php
        $academicOps = $academicOps ?? [];
        $showAcademicOps = hasPermission('admin-datesheet') || hasPermission('admin-classdairy');
        ?>
        <?php if ($showAcademicOps): ?>
        <section class="dash-section mb-0">
            <div class="dash-section__head">
                <h2 class="dash-section__title"><i class="fas fa-tasks"></i> Operations</h2>
            </div>
            <div class="dash-kpi-grid" style="grid-template-columns:1fr 1fr">
                <?php if (hasPermission('admin-datesheet')): ?>
                <a href="<?= base_url('admin/quizzes') ?>" class="dash-stat-card">
                    <div class="dash-stat-card__icon dash-stat-card__icon--cyan" style="width:48px;min-width:48px;font-size:1rem"><i class="fas fa-clipboard-list"></i></div>
                    <div class="dash-stat-card__body"><div class="dash-stat-card__label">Quizzes</div><div class="dash-stat-card__value dash-stat-card__value--sm"><?= (int) ($academicOps['quizOpen'] ?? 0) ?> open</div></div>
                </a>
                <a href="<?= base_url('admin/datesheet') ?>" class="dash-stat-card">
                    <div class="dash-stat-card__icon dash-stat-card__icon--purple" style="width:48px;min-width:48px;font-size:1rem"><i class="fas fa-calendar-alt"></i></div>
                    <div class="dash-stat-card__body"><div class="dash-stat-card__label">Date Sheet</div><div class="dash-stat-card__value dash-stat-card__value--sm"><?= (int) ($academicOps['datesheetRowCount'] ?? 0) ?> rows</div></div>
                </a>
                <?php endif; ?>
                <?php if (hasPermission('admin-classdairy')): ?>
                <a href="<?= base_url('admin/recordings') ?>" class="dash-stat-card">
                    <div class="dash-stat-card__icon dash-stat-card__icon--teal" style="width:48px;min-width:48px;font-size:1rem"><i class="fas fa-microphone"></i></div>
                    <div class="dash-stat-card__body"><div class="dash-stat-card__label">Recordings</div><div class="dash-stat-card__value dash-stat-card__value--sm"><?= (int) ($academicOps['pendingAudio'] ?? 0) ?>A · <?= (int) ($academicOps['pendingVideo'] ?? 0) ?>V</div></div>
                </a>
                <a href="<?= base_url('admin/classdiary') ?>" class="dash-stat-card">
                    <div class="dash-stat-card__icon dash-stat-card__icon--orange" style="width:48px;min-width:48px;font-size:1rem"><i class="fas fa-book-open"></i></div>
                    <div class="dash-stat-card__body"><div class="dash-stat-card__label">Diary Today</div><div class="dash-stat-card__value dash-stat-card__value--sm"><?= (int) ($academicOps['diaryEntriesToday'] ?? 0) ?></div></div>
                </a>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Health -->
        <section class="dash-section mb-0">
            <div class="dash-section__head">
                <h2 class="dash-section__title"><i class="fas fa-heartbeat"></i> Health</h2>
                <a href="<?= base_url('admin/students/bmi-report') ?>" class="dash-section__action">Report →</a>
            </div>
            <div class="dash-panel">
                <div class="dash-panel__body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-muted">BMI Assessment</span>
                        <span class="badge text-bg-light"><?= (int) ($bmiStats->total ?? 0) ?> students</span>
                    </div>
                    <?php
                    $bmiTotal = max(1, (int) ($bmiStats->total ?? 0));
                    $uw = ((int) ($bmiStats->underweight ?? 0) / $bmiTotal) * 100;
                    $nm = ((int) ($bmiStats->normal ?? 0) / $bmiTotal) * 100;
                    $ow = ((int) ($bmiStats->overweight ?? 0) / $bmiTotal) * 100;
                    $ob = ((int) ($bmiStats->obese ?? 0) / $bmiTotal) * 100;
                    ?>
                    <div class="dash-bmi-bar">
                        <?php if ($uw > 0): ?><div class="dash-bmi-bar__seg bg-info" style="width:<?= $uw ?>%" title="Underweight"></div><?php endif; ?>
                        <?php if ($nm > 0): ?><div class="dash-bmi-bar__seg bg-success" style="width:<?= $nm ?>%" title="Normal"></div><?php endif; ?>
                        <?php if ($ow > 0): ?><div class="dash-bmi-bar__seg bg-warning" style="width:<?= $ow ?>%" title="Overweight"></div><?php endif; ?>
                        <?php if ($ob > 0): ?><div class="dash-bmi-bar__seg bg-danger" style="width:<?= $ob ?>%" title="Obese"></div><?php endif; ?>
                    </div>
                    <div class="dash-bmi-legend">
                        <div><i class="fas fa-circle text-info"></i> Under<br><strong><?= (int) ($bmiStats->underweight ?? 0) ?></strong></div>
                        <div><i class="fas fa-circle text-success"></i> Normal<br><strong><?= (int) ($bmiStats->normal ?? 0) ?></strong></div>
                        <div><i class="fas fa-circle text-warning"></i> Over<br><strong><?= (int) ($bmiStats->overweight ?? 0) ?></strong></div>
                        <div><i class="fas fa-circle text-danger"></i> Obese<br><strong><?= (int) ($bmiStats->obese ?? 0) ?></strong></div>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-muted">Health Alerts</span>
                        <span class="badge text-bg-<?=  ($healthAlertsCount ?? 0) > 0 ? 'danger' : 'success' ?>"><?= (int) ($healthAlertsCount ?? 0) ?> pending</span>
                    </div>
                    <?php if (!empty($recentHealthAlerts)): foreach ($recentHealthAlerts as $alert):
                        $dotClass = 'default';
                        if (stripos($alert->alert_type ?? '', 'obese') !== false) $dotClass = 'obese';
                        elseif (stripos($alert->alert_type ?? '', 'over') !== false) $dotClass = 'overweight';
                        elseif (stripos($alert->alert_type ?? '', 'under') !== false) $dotClass = 'underweight';
                    ?>
                    <div class="dash-alert-item">
                        <span class="dash-alert-dot dash-alert-dot--<?= $dotClass ?>"></span>
                        <span><strong><?= esc($alert->student_name ?? '') ?></strong> — <?= esc($alert->alert_type ?? '') ?></span>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="text-muted small text-center py-2"><i class="fas fa-check-circle text-success"></i> No pending alerts</div>
                    <?php endif; ?>
                </div>
                <div class="dash-panel__foot"><a href="<?= base_url('admin/students/health-alerts') ?>">View all alerts &rarr;</a></div>
            </div>
        </section>
    </div>
    <?php endif; ?>
</div>
