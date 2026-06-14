<?php if (! ($isTeacher ?? false)): return; endif; ?>
<?php
$sectionCount = count($teacherSections ?? []);
$subjectCount = count($teacherSubjects ?? []);
$pendingCtCount = count($teacherPendingAttendance ?? []);

$checkinLabel = 'Not checked in';
$checkinClass = 'text-muted';
$checkinDetail = 'Tap to scan QR';
if (! empty($todayAttendance) && ! empty($todayAttendance->checkin)) {
    if (! empty($todayAttendance->checkout)) {
        $checkinLabel = 'Done';
        $checkinClass = 'text-success';
        $checkinDetail = date('h:i A', strtotime($todayAttendance->checkin)) . ' &rarr; ' . date('h:i A', strtotime($todayAttendance->checkout));
    } else {
        $checkinLabel = date('h:i A', strtotime($todayAttendance->checkin));
        $checkinClass = 'text-primary';
        $checkinDetail = 'Scan again to checkout';
    }
}

$firstPending = ($teacherPendingAttendance ?? [])[0] ?? null;
$pendingUrl = $firstPending
    ? base_url('admin/students_absentees/add?cls_sec_id=' . (int) $firstPending['cls_sec_id'] . '&date=' . urlencode($attendanceDate ?? date('Y-m-d')))
    : base_url('admin/students_absentees/add');
?>
<section class="dash-section">
    <div class="dash-section__head">
        <h2 class="dash-section__title"><i class="fas fa-sun"></i> Today</h2>
        <span class="dash-section__line"></span>
    </div>
    <div class="dash-kpi-grid">
        <a href="#teacher-classes-section" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--blue"><i class="fas fa-users"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">My Sections</div>
                <div class="dash-stat-card__value"><?= (int) $sectionCount ?></div>
                <div class="dash-stat-card__detail">Assigned classes</div>
            </div>
        </a>

        <div class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--purple"><i class="fas fa-book"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">My Subjects</div>
                <div class="dash-stat-card__value"><?= (int) $subjectCount ?></div>
                <div class="dash-stat-card__detail">Subjects you teach</div>
            </div>
        </div>

        <a href="#" class="dash-stat-card" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
            <div class="dash-stat-card__icon dash-stat-card__icon--green"><i class="fas fa-user-check"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Check-in Today</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm <?= esc($checkinClass) ?>"><?= esc($checkinLabel) ?></div>
                <div class="dash-stat-card__detail"><?= $checkinDetail ?></div>
            </div>
        </a>

        <?php if ($pendingCtCount > 0 && hasPermission('admin-add-student-absentees')): ?>
        <a href="<?= esc($pendingUrl) ?>" class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--amber"><i class="fas fa-clipboard-check"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Attendance to Mark</div>
                <div class="dash-stat-card__value text-warning"><?= (int) $pendingCtCount ?></div>
                <div class="dash-stat-card__detail">Class-teacher section(s)</div>
            </div>
        </a>
        <?php else: ?>
        <div class="dash-stat-card">
            <div class="dash-stat-card__icon dash-stat-card__icon--amber"><i class="fas fa-clipboard-check"></i></div>
            <div class="dash-stat-card__body">
                <div class="dash-stat-card__label">Attendance to Mark</div>
                <div class="dash-stat-card__value dash-stat-card__value--sm text-success"><i class="fas fa-check"></i></div>
                <div class="dash-stat-card__detail">All class attendance done</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
