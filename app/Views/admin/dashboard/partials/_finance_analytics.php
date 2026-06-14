<!-- Finance & Analytics -->
<?php if (! empty($forceFinanceAnalytics) || (! ($isTeacher ?? false) && ($dashboardLayoutRole ?? '') !== 'teacher' && hasPermission('admin-db-fee-collection'))): ?>
<section class="dash-section">
    <div class="dash-section__head">
        <h2 class="dash-section__title"><i class="fas fa-chart-line"></i> Finance &amp; Analytics</h2>
        <span class="dash-section__line"></span>
    </div>

    <div class="dash-panel mb-3">
        <div class="dash-panel__head">
            <h3 class="dash-panel__title"><i class="fas fa-filter"></i> Fee Collection Report</h3>
        </div>
        <div class="dash-panel__body">
            <div class="form-group dash-session-select mb-0">
                <label class="small fw-bold text-muted">Academic Session</label>
                <select class="form-control select2" id="sessionSelector">
                    <option value="">Last 12 Months (Default)</option>
                    <?php if (!empty($allSessions)): foreach ($allSessions as $sess): ?>
                    <option value="<?= $sess->session_id ?>" <?= (isset($selectedSessionId) && $selectedSessionId == $sess->session_id) ? 'selected' : '' ?>>
                        <?= esc($sess->session_name) ?> (<?= date('M Y', strtotime($sess->start_date)) ?> – <?= date('M Y', strtotime($sess->end_date)) ?>)
                    </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="dash-panel dash-chart-panel mb-3">
        <div class="dash-panel__head">
            <h3 class="dash-panel__title">Fee Collection — <?= esc($chartTitle ?? 'Monthly Report') ?></h3>
        </div>
        <div class="dash-panel__body dash-panel__body--bar">
            <div class="dash-chart-bar-wrap"><canvas id="stackedBarChart"></canvas></div>
        </div>
    </div>

    <div class="dash-chart-row">
        <?php if (isset($monthlyFee)): ?>
        <div class="dash-panel dash-chart-panel">
            <div class="dash-panel__head"><h3 class="dash-panel__title"><?= esc($monthlyFee->fee_type_name) ?> (<?= date('M Y') ?>)</h3></div>
            <div class="dash-panel__body dash-panel__body--donut">
                <div class="dash-chart-donut-wrap"><canvas id="pieChart"></canvas></div>
            </div>
        </div>
        <?php endif; ?>
        <div class="dash-panel dash-chart-panel">
            <div class="dash-panel__head"><h3 class="dash-panel__title">Student Strength by Class</h3></div>
            <div class="dash-panel__body dash-panel__body--bar">
                <div class="dash-chart-bar-wrap"><canvas id="stackedBarChartSection"></canvas></div>
            </div>
        </div>
    </div>

    <?php if (hasPermission('admin-db-attendance')): ?>
    <div class="dash-panel dash-chart-panel mt-3" style="max-width:420px">
        <div class="dash-panel__head"><h3 class="dash-panel__title">Today's Student Attendance</h3></div>
        <div class="dash-panel__body dash-panel__body--donut">
            <div class="dash-chart-donut-wrap"><canvas id="pieChartAttendance"></canvas></div>
        </div>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>
