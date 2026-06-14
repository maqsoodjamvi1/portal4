<?php $uiNeedsChart = true; ?>
<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard-ui.css?v=20260526') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$userName = trim($userName ?? '');
$greeting = 'Welcome';
$hour = (int) date('G');
if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 17) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}
$displayName = $userName !== '' ? $userName : (session('member_username') ?? 'User');
?>

<div class="dashboard-page">
<?= view('admin/dashboard/partials/layout_role', ['dashboardLayoutRole' => $dashboardLayoutRole ?? 'default']) ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= esc($error) ?></div>
<?php else: ?>

<!-- Hero -->
<div class="dash-hero">
    <div class="dash-hero__inner row align-items-center">
        <div class="col-lg-7">
            <h1 class="dash-hero__greeting"><?= esc($greeting) ?>, <?= esc($displayName) ?>!</h1>
            <?php if (($dashboardLayoutRole ?? 'default') === 'teacher'): ?>
            <p class="dash-hero__sub">
                Your classes, planning, and attendance
                <?php if (isset($termInfo) || isset($termWeeksInfo)): ?>
                for <?= esc($termInfo->name ?? 'this term') ?><?= isset($termWeeksInfo) ? ' &middot; ' . esc($termWeeksInfo->week_name ?? 'this week') : '' ?>
                <?php endif; ?>.
            </p>
            <?php else: ?>
            <p class="dash-hero__sub">Your campus command center &mdash; academics, attendance, fees &amp; operations at a glance.</p>
            <?php endif; ?>
            <div class="dash-hero__meta">
                <?php if (isset($academic_session)): ?>
                <span class="dash-pill"><i class="far fa-calendar-alt"></i> <?= esc($academic_session->session_name ?? 'Session') ?></span>
                <?php endif; ?>
                <?php if (isset($termInfo)): ?>
                <span class="dash-pill"><i class="fas fa-book"></i> <?= esc($termInfo->name ?? 'Term') ?></span>
                <?php endif; ?>
                <?php if (isset($termWeeksInfo)): ?>
                <span class="dash-pill"><i class="fas fa-calendar-week"></i> <?= esc($termWeeksInfo->week_name ?? 'Week') ?></span>
                <?php endif; ?>
                <?php if (!empty($examsInfo)): ?>
                <span class="dash-pill"><i class="fas fa-file-alt"></i> <?= esc($examsInfo->exam_name) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-5 dash-hero__clock mt-3 mt-lg-0">
            <div class="dash-hero__date" id="dashLiveDate"><?= date('l, d M Y') ?></div>
            <div class="dash-hero__time" id="dashLiveTime"><?= date('h:i:s A') ?></div>
        </div>
    </div>
</div>

<?php
$setupProgressAlert = null;
$schoolinfoAlert = function_exists('getSchoolInfo') ? getSchoolInfo() : null;
$userIdAlert = (int) (session('member_userid') ?? 0);
$campusIdAlert = (int) (session('member_campusid') ?? 0);
if ($schoolinfoAlert && ! empty($schoolinfoAlert->system_id) && $userIdAlert > 0
    && ! \App\Libraries\SchoolSetupProgress::isTeacher($userIdAlert, $campusIdAlert)) {
    $setupProgressAlert = \App\Libraries\SchoolSetupProgress::getStatus(
        (int) $schoolinfoAlert->system_id,
        $campusIdAlert > 0 ? $campusIdAlert : (int) ($schoolinfoAlert->campus_id ?? 0)
    );
}
echo view('admin/partials/setup_progress_alert', ['setupProgress' => $setupProgressAlert]);
?>

<?php if (($dashboardLayoutRole ?? 'default') !== 'teacher'): ?>
<?= view('admin/dashboard/partials/action_center', ['actionCenter' => $actionCenter ?? []]) ?>
<?php endif; ?>

<?php
if (! empty($showOptionalModules)) {
    echo view('admin/partials/optional_modules_checklist');
}
?>


<?php
$__dashRole = $dashboardLayoutRole ?? 'default';
$__dashHomeMap = [
    'teacher'   => 'teacher_home',
    'finance'   => 'finance_home',
    'principal' => 'principal_home',
];
$__dashHome = $__dashHomeMap[$__dashRole] ?? 'default_home';
echo view('admin/dashboard/partials/' . $__dashHome, get_defined_vars());
?>

<?php endif; /* error check */ ?>
</div><!-- /.dashboard-page -->

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-qrcode me-2"></i> Scan Campus QR Code</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width:100%"></div>
                <div id="qr-reader-results" class="mt-3"></div>
                <div class="mt-3 alert alert-info mb-0"><small><i class="fas fa-info-circle me-1"></i> Point camera at the campus QR code</small></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
(function () {
    'use strict';

    /* Live clock */
    function tickClock() {
        const now = new Date();
        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const elDate = document.getElementById('dashLiveDate');
        const elTime = document.getElementById('dashLiveTime');
        const elCompact = document.getElementById('currentTimeCompact');
        let h = now.getHours(), ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        const timeStr = h + ':' + String(now.getMinutes()).padStart(2,'0') + ':' + String(now.getSeconds()).padStart(2,'0') + ' ' + ampm;
        const dateStr = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
        if (elDate) elDate.textContent = dateStr;
        if (elTime) elTime.textContent = timeStr;
        if (elCompact) elCompact.textContent = timeStr.replace(/:\d{2}\s/, ' ');
    }
    tickClock();
    setInterval(tickClock, 1000);

    /* Session selector */
    const sessionSel = document.getElementById('sessionSelector');
    if (sessionSel) {
        sessionSel.addEventListener('change', function () {
            const url = new URL(window.location.href);
            if (this.value) url.searchParams.set('session_id', this.value);
            else url.searchParams.delete('session_id');
            window.location.href = url.toString();
        });
    }

    /* Charts */
    document.addEventListener('DOMContentLoaded', function () {
        const fmt = v => Number(v).toLocaleString();
        const legendOpts = { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12, padding: 14 } };
        const donutOpts = {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1,
            plugins: { legend: legendOpts }
        };
        const barOpts = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: legendOpts }
        };

        const pieCtx = document.getElementById('pieChart')?.getContext('2d');
        if (pieCtx) {
            const paid = <?= json_encode($PaidFee_info ?? 0) ?>, unpaid = <?= json_encode($RemainingBalance_info ?? 0) ?>;
            if (paid > 0 || unpaid > 0) {
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: { labels: ['Paid', 'Unpaid'], datasets: [{ data: [paid, unpaid], backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0, hoverOffset: 6 }] },
                    options: { ...donutOpts, cutout: '62%' }
                });
            }
        }

        const barCtx = document.getElementById('stackedBarChart')?.getContext('2d');
        if (barCtx) {
            const months = <?= json_encode($prStr ?? []) ?>, paid = <?= json_encode($paidStr ?? []) ?>, unpaid = <?= json_encode($unpaidStr ?? []) ?>;
            if (months.length) {
                new Chart(barCtx, {
                    type: 'bar',
                    data: { labels: months, datasets: [
                        { label: 'Paid', data: paid, backgroundColor: '#10b981', borderRadius: 4 },
                        { label: 'Unpaid', data: unpaid, backgroundColor: '#ef4444', borderRadius: 4 }
                    ]},
                    options: {
                        ...barOpts,
                        scales: {
                            x: { stacked: true, grid: { display: false }, ticks: { maxRotation: 45, minRotation: 0, font: { size: 10 } } },
                            y: { stacked: true, beginAtZero: true, ticks: { callback: fmt }, title: { display: true, text: 'PKR' } }
                        }
                    }
                });
            }
        }

        const attCtx = document.getElementById('pieChartAttendance')?.getContext('2d');
        if (attCtx) {
            const p = <?= json_encode($attendance['present'] ?? 0) ?>, a = <?= json_encode($attendance['absent'] ?? 0) ?>, l = <?= json_encode($attendance['leaves'] ?? 0) ?>;
            if (p + a + l > 0) {
                new Chart(attCtx, {
                    type: 'doughnut',
                    data: { labels: ['Present', 'Absent', 'Leaves'], datasets: [{ data: [p, a, l], backgroundColor: ['#10b981', '#ef4444', '#f59e0b'], borderWidth: 0, hoverOffset: 6 }] },
                    options: { ...donutOpts, cutout: '62%' }
                });
            }
        }

        const strCtx = document.getElementById('stackedBarChartSection')?.getContext('2d');
        if (strCtx) {
            const cls = <?= json_encode($clsArr ?? []) ?>, male = <?= json_encode($stdMArr ?? []) ?>, female = <?= json_encode($stdFArr ?? []) ?>;
            if (cls.length) {
                new Chart(strCtx, {
                    type: 'bar',
                    data: { labels: cls, datasets: [
                        { label: 'Male', data: male, backgroundColor: '#3b82f6', borderRadius: 4 },
                        { label: 'Female', data: female, backgroundColor: '#f97316', borderRadius: 4 }
                    ]},
                    options: {
                        ...barOpts,
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Students' } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }
        }
    });

    /* QR Scanner (teachers) */
    let html5QrCode = null, isScanning = false;
    const baseUrl = <?= json_encode(base_url()) ?>;

    function loadAttendanceStatus() {
        const c = document.getElementById('attendanceStatusContainer');
        if (!c) return;
        c.innerHTML = '<div class="dash-att-stat text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div> Loading…</div>';
        fetch(baseUrl + 'admin/get-recent-attendance', { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(r => r.json()).then(data => {
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const today = new Date();
                const todayStr = today.getDate() + ' ' + months[today.getMonth()] + ' ' + today.getFullYear();
                const arr = Array.isArray(data) ? data : (data.attendance || []);
                const t = arr.find(a => a.date === todayStr);
                if (t && t.checkin && t.checkin !== '-') {
                    if (t.checkout && t.checkout !== '-') {
                        c.innerHTML = '<div class="dash-att-stat"><div class="dash-att-stat__lbl">Today</div><div class="dash-att-stat__num text-success"><i class="fas fa-check-circle"></i> Done</div><div class="small mt-1">' + t.checkin + ' → ' + t.checkout + '</div></div>';
                    } else {
                        c.innerHTML = '<div class="dash-att-stat"><div class="dash-att-stat__lbl">Checked In</div><div class="dash-att-stat__num">' + t.checkin + '</div><div class="small mt-1">Scan again to checkout</div></div>';
                    }
                } else {
                    c.innerHTML = '<div class="dash-att-stat"><div class="dash-att-stat__lbl">Status</div><div class="dash-att-stat__num text-muted">Not checked in</div></div>';
                }
            }).catch(() => { c.innerHTML = '<div class="dash-att-stat text-danger small">Unable to load status</div>'; });
    }

    function startScanner() {
        if (isScanning || typeof Html5Qrcode === 'undefined') return;
        html5QrCode = new Html5Qrcode('qr-reader');
        html5QrCode.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 250, height: 250 } },
            decodedText => {
                if (html5QrCode && isScanning) { html5QrCode.stop(); isScanning = false; }
                fetch(baseUrl + 'admin/process-teacher-attendance', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    body: 'qr_code=' + encodeURIComponent(decodedText)
                }).then(r => r.json()).then(data => {
                    if (typeof $ !== 'undefined') $('#qrScannerModal').modal('hide');
                    if (typeof showToast === 'function') showToast(data.success ? 'success' : 'error', data.message || '');
                    else alert(data.message || (data.success ? 'Done' : 'Failed'));
                    loadAttendanceStatus();
                    if (data.success) setTimeout(() => location.reload(), 1200);
                });
            }, () => {}
        ).then(() => { isScanning = true; }).catch(() => { isScanning = false; });
    }

    function stopScanner() { if (html5QrCode && isScanning) { html5QrCode.stop(); isScanning = false; } }

    const qrModal = document.getElementById('qrScannerModal');
    if (qrModal) {
        if (typeof $ !== 'undefined') {
            $(qrModal).on('shown.bs.modal', startScanner);
            $(qrModal).on('hidden.bs.modal', function () { stopScanner(); const r = document.getElementById('qr-reader-results'); if (r) r.innerHTML = ''; });
        } else {
            qrModal.addEventListener('shown.bs.modal', startScanner);
            qrModal.addEventListener('hidden.bs.modal', () => { stopScanner(); const r = document.getElementById('qr-reader-results'); if (r) r.innerHTML = ''; });
        }
        loadAttendanceStatus();
    }
})();
</script>

<?= $this->endSection() ?>
