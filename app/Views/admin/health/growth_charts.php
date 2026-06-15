<?php $uiNeedsChart = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.growth-chart-container {
    background: white;
    border-radius: 10px;
    padding: 20px;
}

.chart-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

/* Chart Controls */
.chart-controls {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.control-btn {
    margin-right: 10px;
    transition: all 0.2s;
}

.control-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.metric-toggle {
    display: inline-flex;
    align-items: center;
    margin-right: 15px;
    cursor: pointer;
}

.metric-toggle input {
    margin-right: 5px;
}

/* Data Table */
.growth-data-table {
    max-height: 300px;
    overflow-y: auto;
}

.growth-data-table table {
    font-size: 12px;
}

.growth-data-table th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
}

/* Trend Indicators */
.trend-up { color: #28a745; }
.trend-down { color: #dc3545; }
.trend-stable { color: #ffc107; }

/* Chart Container */
.chart-wrapper {
    position: relative;
    min-height: 450px;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.chart-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
}

.chart-legend-color {
    width: 20px;
    height: 3px;
    border-radius: 2px;
}

/* Zoom Controls */
.zoom-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    background: rgba(255,255,255,0.9);
    border-radius: 5px;
    padding: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.zoom-btn {
    background: white;
    border: 1px solid #ddd;
    padding: 5px 10px;
    margin: 0 2px;
    cursor: pointer;
    border-radius: 3px;
}

.zoom-btn:hover {
    background: #f0f0f0;
}
</style>

<?= view('components/page_header', [
    'title' => 'Growth Charts',
    'icon' => 'fas fa-chart-line',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'BMI Dashboard', 'url' => base_url('admin/health/bmi-dashboard')],
        ['label' => 'Growth Charts', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Select Student</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Student</label>
                        <select id="studentSelect" class="form-control select2">
                            <option value="">Select a student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student->student_id ?>">
                                    <?= esc($student->first_name . ' ' . $student->last_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Chart Type</label>
                        <select id="chartTypeSelect" class="form-control">
                            <option value="line">Line Chart</option>
                            <option value="bar">Bar Chart</option>
                            <option value="scatter">Scatter Plot</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Time Range</label>
                        <select id="timeRangeSelect" class="form-control">
                            <option value="all">All Records</option>
                            <option value="12">Last 12 Months</option>
                            <option value="6">Last 6 Months</option>
                            <option value="3">Last 3 Months</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button id="loadChartBtn" class="btn btn-primary w-100">
                            <i class="fas fa-chart-line me-1"></i> Load Growth Chart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="growthChartContainer" style="display: none;">
        <!-- Student Summary Cards -->
        <div class="row" id="summaryCards">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Current Height</span>
                        <span class="info-box-number" id="currentHeight">-</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-weight"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Current Weight</span>
                        <span class="info-box-number" id="currentWeight">-</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-heartbeat"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Current BMI</span>
                        <span class="info-box-number" id="currentBmi">-</span>
                        <span class="info-box-text" id="bmiCategory"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Growth Rate</span>
                        <span class="info-box-number" id="growthRate">-</span>
                        <span class="info-box-text" id="growthTrend"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Chart -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" id="studentNameDisplay"></h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-secondary" id="toggleHeight">
                                    <i class="fas fa-ruler-vertical"></i> Height
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" id="toggleWeight">
                                    <i class="fas fa-weight"></i> Weight
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" id="toggleBmi">
                                    <i class="fas fa-heartbeat"></i> BMI
                                </button>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary ms-2" id="resetZoom">
                                <i class="fas fa-search-minus"></i> Reset View
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <canvas id="growthChart" style="height: 450px; width: 100%;"></canvas>
                        </div>
                        <div class="chart-legend" id="chartLegend"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Table with Trend Analysis -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table me-2"></i>Growth Data & Trend Analysis
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" id="exportData">
                                <i class="fas fa-download"></i> Export Data
                            </button>
                        </div>
                    </div>
                    <div class="card-body growth-data-table">
                        <table class="table table-striped table-hover" id="growthTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Height (cm)</th>
                                    <th>Height Change</th>
                                    <th>Weight (kg)</th>
                                    <th>Weight Change</th>
                                    <th>BMI</th>
                                    <th>Category</th>
                                    <th>BMI Change</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody id="growthTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Growth Percentile Chart (Optional) -->
        <div class="row mt-3" id="percentileChartRow" style="display: none;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">BMI Percentile Comparison</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="percentileChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
<script>
let growthChart = null;
let percentileChart = null;
let currentData = [];
let visibleMetrics = {
    height: true,
    weight: true,
    bmi: true
};

// Helper function to calculate percentage change
function calculateChange(current, previous) {
    if (!previous || previous === 0) return null;
    const change = ((current - previous) / previous) * 100;
    return change.toFixed(1);
}

function getChangeClass(change) {
    if (!change) return 'text-muted';
    if (change > 0) return 'trend-up';
    if (change < 0) return 'trend-down';
    return 'trend-stable';
}

function getChangeIcon(change) {
    if (!change) return '';
    if (change > 0) return '<i class="fas fa-arrow-up"></i>';
    if (change < 0) return '<i class="fas fa-arrow-down"></i>';
    return '<i class="fas fa-minus"></i>';
}

$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'Select a student'
    });
    
    // Toggle metrics
    $('#toggleHeight').click(function() {
        visibleMetrics.height = !visibleMetrics.height;
        $(this).toggleClass('btn-primary btn-secondary');
        if (growthChart) updateChartVisibility();
    });
    
    $('#toggleWeight').click(function() {
        visibleMetrics.weight = !visibleMetrics.weight;
        $(this).toggleClass('btn-primary btn-secondary');
        if (growthChart) updateChartVisibility();
    });
    
    $('#toggleBmi').click(function() {
        visibleMetrics.bmi = !visibleMetrics.bmi;
        $(this).toggleClass('btn-primary btn-secondary');
        if (growthChart) updateChartVisibility();
    });
    
    $('#resetZoom').click(function() {
        if (growthChart) growthChart.resetZoom();
    });
    
    $('#exportData').click(function() {
        exportToExcel();
    });
    
    $('#loadChartBtn').click(function() {
        const studentId = $('#studentSelect').val();
        const studentName = $('#studentSelect option:selected').text();
        const chartType = $('#chartTypeSelect').val();
        const timeRange = $('#timeRangeSelect').val();
        
        if (!studentId) {
            toastr.warning('Please select a student');
            return;
        }
        
        $('#studentNameDisplay').text('Growth Chart - ' + studentName);
        $('#growthChartContainer').show();
        
        $.ajax({
            url: '<?= base_url("admin/health/growth-charts/data") ?>/' + studentId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                currentData = data;
                
                // Filter by time range
                let filteredData = filterByTimeRange(data, timeRange);
                
                if (filteredData.length === 0) {
                    $('#growthTableBody').html('<tr><td colspan="9" class="text-center text-muted">No growth data available</td></tr>');
                    if (growthChart) growthChart.destroy();
                    return;
                }
                
                // Update summary cards
                updateSummaryCards(filteredData);
                
                // Update data table with trend analysis
                updateDataTable(filteredData);
                
                // Update chart
                updateChart(filteredData, chartType);
                
                // Update percentile chart if enough data
                if (filteredData.length >= 3) {
                    updatePercentileChart(filteredData);
                    $('#percentileChartRow').show();
                } else {
                    $('#percentileChartRow').hide();
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                toastr.error('Error loading growth data');
            }
        });
    });
    
    function filterByTimeRange(data, range) {
        if (range === 'all' || !data.length) return data;
        
        const months = parseInt(range);
        const cutoffDate = new Date();
        cutoffDate.setMonth(cutoffDate.getMonth() - months);
        
        return data.filter(item => new Date(item.recorded_date) >= cutoffDate);
    }
    
    function updateSummaryCards(data) {
        const latest = data[data.length - 1];
        const first = data[0];
        
        $('#currentHeight').text(latest.height ? latest.height + ' cm' : '-');
        $('#currentWeight').text(latest.weight ? latest.weight + ' kg' : '-');
        $('#currentBmi').text(latest.bmi || '-');
        
        let categoryClass = '';
        if (latest.bmi_category === 'underweight') categoryClass = 'text-bg-info';
        else if (latest.bmi_category === 'normal') categoryClass = 'text-bg-success';
        else if (latest.bmi_category === 'overweight') categoryClass = 'text-bg-warning';
        else if (latest.bmi_category === 'obese') categoryClass = 'text-bg-danger';
        
        $('#bmiCategory').html('<span class="badge ' + categoryClass + '">' + (latest.bmi_category || 'N/A') + '</span>');
        
        // Calculate growth rate
        if (first.height && latest.height && data.length > 1) {
            const monthsDiff = (new Date(latest.recorded_date) - new Date(first.recorded_date)) / (1000 * 60 * 60 * 24 * 30);
            const heightGain = latest.height - first.height;
            const monthlyRate = (heightGain / monthsDiff).toFixed(1);
            $('#growthRate').html(monthlyRate + ' cm/month');
            $('#growthTrend').html(heightGain > 0 ? '<span class="trend-up"><i class="fas fa-arrow-up"></i> Growing</span>' : '<span class="trend-down"><i class="fas fa-arrow-down"></i> Declining</span>');
        } else {
            $('#growthRate').html('-');
            $('#growthTrend').html('-');
        }
    }
    
    function updateDataTable(data) {
        let html = '';
        for (let i = 0; i < data.length; i++) {
            const item = data[i];
            const prev = i > 0 ? data[i - 1] : null;
            
            const heightChange = prev ? calculateChange(item.height, prev.height) : null;
            const weightChange = prev ? calculateChange(item.weight, prev.weight) : null;
            const bmiChange = prev ? calculateChange(item.bmi, prev.bmi) : null;
            
            let categoryClass = '';
            if (item.bmi_category === 'underweight') categoryClass = 'text-bg-info';
            else if (item.bmi_category === 'normal') categoryClass = 'text-bg-success';
            else if (item.bmi_category === 'overweight') categoryClass = 'text-bg-warning';
            else if (item.bmi_category === 'obese') categoryClass = 'text-bg-danger';
            
            html += `
                <tr>
                    <td>${new Date(item.recorded_date).toLocaleDateString()}</td>
                    <td><strong>${item.height || '-'}</strong></td>
                    <td class="${getChangeClass(heightChange)}">
                        ${heightChange ? getChangeIcon(heightChange) + ' ' + Math.abs(heightChange) + '%' : '-'}
                    </td>
                    <td><strong>${item.weight || '-'}</strong></td>
                    <td class="${getChangeClass(weightChange)}">
                        ${weightChange ? getChangeIcon(weightChange) + ' ' + Math.abs(weightChange) + '%' : '-'}
                    </td>
                    <td><strong>${item.bmi || '-'}</strong></td>
                    <td><span class="badge ${categoryClass}">${item.bmi_category || '-'}</span></td>
                    <td class="${getChangeClass(bmiChange)}">
                        ${bmiChange ? getChangeIcon(bmiChange) + ' ' + Math.abs(bmiChange) + '%' : '-'}
                    </td>
                    <td>${item.notes || '-'}</td>
                </tr>
            `;
        }
        $('#growthTableBody').html(html);
    }
    
    function updateChart(data, chartType) {
        if (growthChart) growthChart.destroy();
        
        const ctx = document.getElementById('growthChart').getContext('2d');
        const dates = data.map(item => new Date(item.recorded_date).toLocaleDateString());
        
        const datasets = [];
        
        if (visibleMetrics.height) {
            datasets.push({
                label: 'Height (cm)',
                data: data.map(item => item.height),
                borderColor: '#3498db',
                backgroundColor: chartType === 'bar' ? 'rgba(52,152,219,0.5)' : 'rgba(52,152,219,0.1)',
                fill: chartType === 'line',
                tension: 0.3,
                yAxisID: 'y',
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#3498db'
            });
        }
        
        if (visibleMetrics.weight) {
            datasets.push({
                label: 'Weight (kg)',
                data: data.map(item => item.weight),
                borderColor: '#2ecc71',
                backgroundColor: chartType === 'bar' ? 'rgba(46,204,113,0.5)' : 'rgba(46,204,113,0.1)',
                fill: chartType === 'line',
                tension: 0.3,
                yAxisID: 'y',
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#2ecc71'
            });
        }
        
        if (visibleMetrics.bmi) {
            datasets.push({
                label: 'BMI',
                data: data.map(item => item.bmi),
                borderColor: '#e74c3c',
                backgroundColor: chartType === 'bar' ? 'rgba(231,76,60,0.5)' : 'rgba(231,76,60,0.1)',
                fill: chartType === 'line',
                tension: 0.3,
                yAxisID: 'y1',
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#e74c3c'
            });
        }
        
        growthChart = new Chart(ctx, {
            type: chartType,
            data: { labels: dates, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.raw;
                                let unit = '';
                                if (label.includes('Height')) unit = ' cm';
                                if (label.includes('Weight')) unit = ' kg';
                                return label + ': ' + value + unit;
                            }
                        }
                    },
                    legend: { position: 'top' },
                    zoom: {
                        pan: { enabled: true, mode: 'xy' },
                        zoom: {
                            wheel: { enabled: true },
                            pinch: { enabled: true },
                            mode: 'xy'
                        }
                    }
                },
                scales: {
                    y: {
                        title: { display: true, text: 'Height (cm) / Weight (kg)', font: { weight: 'bold' } },
                        beginAtZero: true,
                        grid: { color: '#e9ecef' }
                    },
                    y1: {
                        position: 'right',
                        title: { display: true, text: 'BMI', font: { weight: 'bold' } },
                        grid: { drawOnChartArea: false },
                        beginAtZero: true
                    },
                    x: {
                        title: { display: true, text: 'Date', font: { weight: 'bold' } },
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });
    }
    
    function updateChartVisibility() {
        if (!growthChart) return;
        
        growthChart.data.datasets = growthChart.data.datasets.filter(dataset => {
            if (dataset.label === 'Height (cm)') return visibleMetrics.height;
            if (dataset.label === 'Weight (kg)') return visibleMetrics.weight;
            if (dataset.label === 'BMI') return visibleMetrics.bmi;
            return true;
        });
        
        growthChart.update();
    }
    
    function updatePercentileChart(data) {
        if (percentileChart) percentileChart.destroy();
        
        const ctx = document.getElementById('percentileChart').getContext('2d');
        const dates = data.map(item => new Date(item.recorded_date).toLocaleDateString());
        const percentiles = data.map(item => item.bmi_percentile || 50);
        
        percentileChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'BMI Percentile',
                        data: percentiles,
                        borderColor: '#9b59b6',
                        backgroundColor: 'rgba(155,89,182,0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Healthy Range (5th-85th)',
                        data: Array(dates.length).fill(85),
                        borderColor: '#2ecc71',
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                        showLine: true
                    },
                    {
                        label: 'Lower Healthy Bound (5th)',
                        data: Array(dates.length).fill(5),
                        borderColor: '#f39c12',
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                        showLine: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        title: { display: true, text: 'Percentile (%)' },
                        min: 0,
                        max: 100
                    }
                }
            }
        });
    }
    
    function exportToExcel() {
        const studentName = $('#studentSelect option:selected').text();
        const data = currentData;
        
        let csv = 'Date,Height (cm),Weight (kg),BMI,BMI Category,Notes\n';
        data.forEach(item => {
            csv += `"${item.recorded_date}",${item.height || ''},${item.weight || ''},${item.bmi || ''},"${item.bmi_category || ''}","${item.notes || ''}"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `growth_data_${studentName.replace(/\s/g, '_')}.csv`;
        a.click();
        URL.revokeObjectURL(url);
        toastr.success('Data exported successfully');
    }
});
</script>

<?= $this->endSection() ?>