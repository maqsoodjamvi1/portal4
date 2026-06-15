<?php $uiNeedsChart = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.bmi-stat-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 10px;
    overflow: hidden;
}
.bmi-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.bmi-underweight { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }
.bmi-normal { background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; }
.bmi-overweight { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
.bmi-obese { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; }
.bmi-stat-value {
    font-size: 36px;
    font-weight: bold;
}
.bmi-stat-label {
    font-size: 14px;
    opacity: 0.9;
}
.alert-item {
    padding: 12px;
    border-start: 4px solid;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    transition: all 0.2s;
}
.alert-item:hover {
    background: #e9ecef;
}
.alert-underweight { border-start-color: #3498db; }
.alert-overweight { border-start-color: #f39c12; }
.alert-obese { border-start-color: #e74c3c; }
.trend-chart-container {
    background: white;
    border-radius: 10px;
    padding: 15px;
}
</style>

<?= view('components/page_header', [
    'title' => 'BMI Dashboard',
    'icon' => 'fas fa-heartbeat',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'BMI Dashboard', 'active' => true],
    ],
]) ?>

<section class="content">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box bmi-stat-card">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Students Assessed</span>
                    <span class="info-box-number"><?= $bmiStats->total ?? 0 ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bmi-stat-card bmi-underweight">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Underweight</span>
                    <span class="info-box-number"><?= $bmiStats->underweight ?? 0 ?></span>
                    <span class="info-box-text small"><?= $bmiStats->total ? round(($bmiStats->underweight ?? 0) / $bmiStats->total * 100) : 0 ?>%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bmi-stat-card bmi-normal">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Normal Weight</span>
                    <span class="info-box-number"><?= $bmiStats->normal ?? 0 ?></span>
                    <span class="info-box-text small"><?= $bmiStats->total ? round(($bmiStats->normal ?? 0) / $bmiStats->total * 100) : 0 ?>%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bmi-stat-card bmi-overweight">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Overweight</span>
                    <span class="info-box-number"><?= $bmiStats->overweight ?? 0 ?></span>
                    <span class="info-box-text small"><?= $bmiStats->total ? round(($bmiStats->overweight ?? 0) / $bmiStats->total * 100) : 0 ?>%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bmi-stat-card bmi-obese">
                <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Obese</span>
                    <span class="info-box-number"><?= $bmiStats->obese ?? 0 ?></span>
                    <span class="info-box-text small"><?= $bmiStats->total ? round(($bmiStats->obese ?? 0) / $bmiStats->total * 100) : 0 ?>%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- BMI Distribution Progress Bar -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">BMI Distribution</h3>
                </div>
                <div class="card-body">
                    <div class="progress" style="height: 30px;">
                        <?php
                        $total = $bmiStats->total ?? 1;
                        $underweightPercent = ($bmiStats->underweight ?? 0) / $total * 100;
                        $normalPercent = ($bmiStats->normal ?? 0) / $total * 100;
                        $overweightPercent = ($bmiStats->overweight ?? 0) / $total * 100;
                        $obesePercent = ($bmiStats->obese ?? 0) / $total * 100;
                        ?>
                        <div class="progress-bar bg-info" style="width: <?= $underweightPercent ?>%" 
                             title="Underweight: <?= $bmiStats->underweight ?? 0 ?>">
                            Underweight (<?= round($underweightPercent) ?>%)
                        </div>
                        <div class="progress-bar bg-success" style="width: <?= $normalPercent ?>%" 
                             title="Normal: <?= $bmiStats->normal ?? 0 ?>">
                            Normal (<?= round($normalPercent) ?>%)
                        </div>
                        <div class="progress-bar bg-warning" style="width: <?= $overweightPercent ?>%" 
                             title="Overweight: <?= $bmiStats->overweight ?? 0 ?>">
                            Overweight (<?= round($overweightPercent) ?>%)
                        </div>
                        <div class="progress-bar bg-danger" style="width: <?= $obesePercent ?>%" 
                             title="Obese: <?= $bmiStats->obese ?? 0 ?>">
                            Obese (<?= round($obesePercent) ?>%)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">BMI Distribution by Category</h3>
                </div>
                <div class="card-body">
                    <canvas id="bmiPieChart" style="min-height: 250px; height: 250px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">BMI Trends (Last 12 Months)</h3>
                </div>
                <div class="card-body">
                    <canvas id="bmiTrendChart" style="min-height: 250px; height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Alerts Section -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell me-2"></i>Recent Health Alerts
                        <span class="badge text-bg-danger ms-2"><?= count($recentAlerts) ?></span>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('admin/health/alerts') ?>" class="btn btn-sm btn-primary">
                            View All Alerts
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentAlerts)): ?>
                        <?php foreach ($recentAlerts as $alert): ?>
                            <div class="alert-item alert-<?= $alert->alert_type ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= esc($alert->first_name . ' ' . $alert->last_name) ?></strong>
                                        <span class="badge text-bg-<?=  $alert->alert_type == 'underweight' ? 'info' : ($alert->alert_type == 'overweight' ? 'warning' : 'danger') ?> ms-2">
                                            <?= ucfirst($alert->alert_type) ?>
                                        </span>
                                        <div class="small text-muted mt-1">
                                            BMI: <?= $alert->bmi_value ?> | 
                                            Registered: <?= date('d-M-Y H:i', strtotime($alert->created_date)) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-success mark-read" data-id="<?= $alert->alert_id ?>">
                                            <i class="fas fa-check"></i> Mark Read
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 small">
                                    <?= esc($alert->message) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-2 text-success"></i>
                            <p>No pending health alerts</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    // BMI Pie Chart
    const pieCtx = document.getElementById('bmiPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Underweight', 'Normal', 'Overweight', 'Obese'],
            datasets: [{
                data: [
                    <?= $bmiStats->underweight ?? 0 ?>,
                    <?= $bmiStats->normal ?? 0 ?>,
                    <?= $bmiStats->overweight ?? 0 ?>,
                    <?= $bmiStats->obese ?? 0 ?>
                ],
                backgroundColor: ['#3498db', '#2ecc71', '#f39c12', '#e74c3c']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    
    // BMI Trend Chart
    const trendData = <?= json_encode($trendData ?? []) ?>;
    const trendCtx = document.getElementById('bmiTrendChart').getContext('2d');
    
    if (trendData.length > 0) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(item => item.month),
                datasets: [{
                    label: 'Average BMI',
                    data: trendData.map(item => item.avg_bmi),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52,152,219,0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        title: { display: true, text: 'BMI Value' }
                    }
                }
            }
        });
    } else {
        trendCtx.canvas.parentNode.innerHTML = '<div class="text-center text-muted py-4">No trend data available</div>';
    }
    
    // Mark alert as read
    $('.mark-read').on('click', function() {
        const alertId = $(this).data('id');
        const $this = $(this);
        
        $.ajax({
            url: '<?= base_url("admin/health/alerts/mark-read") ?>/' + alertId,
            type: 'POST',
            data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $this.closest('.alert-item').fadeOut(300, function() {
                        $(this).remove();
                        if ($('.alert-item').length === 0) {
                            location.reload();
                        }
                    });
                    toastr.success('Alert marked as read');
                }
            }
        });
    });
});
</script>

<?= $this->endSection() ?>