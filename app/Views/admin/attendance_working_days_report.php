<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.attendance-report {
    font-size: 14px;
}

/* Compact Calendar Styles */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    background-color: #e9ecef;
    padding: 2px;
    border-radius: 6px;
}

.calendar-day-header {
    background-color: #f8f9fa;
    font-weight: bold;
    padding: 6px 2px;
    text-align: center;
    font-size: 11px;
    border-radius: 4px;
}

.calendar-day {
    background-color: white;
    padding: 4px 2px;
    text-align: center;
    font-size: 11px;
    min-height: 45px;
    border-radius: 4px;
    transition: all 0.2s;
    cursor: pointer;
    position: relative;
}

.calendar-day:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    z-index: 2;
}

/* Date row - date and icon in same line */
.date-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin-bottom: 3px;
}

.day-num {
    font-weight: bold;
    font-size: 12px;
}

.status-icon {
    font-size: 12px;
    font-weight: bold;
}

.status-percentage {
    font-size: 10px;
    font-weight: bold;
    padding: 1px 4px;
    border-radius: 8px;
    background: #f8f9fa;
    display: inline-block;
}

/* Color variations */
.calendar-day-working {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-left: 3px solid #28a745;
}

.calendar-day-off-schedule {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    border-left: 3px solid #dc3545;
}

.calendar-day-off-no-record {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border-left: 3px solid #ff9800;
}

.calendar-day-future {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    border-left: 3px solid #6c757d;
    opacity: 0.8;
}

.weekend {
    background-color: #f8f9fa;
}

/* Summary Cards */
.summary-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    margin-bottom: 15px;
}

.summary-card .inner {
    padding: 15px;
}

.summary-card h3 {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 0;
}

.summary-card .icon {
    font-size: 2.5rem;
    opacity: 0.3;
    position: absolute;
    right: 15px;
    top: 15px;
}

.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 3px;
}

.section-card {
    margin-bottom: 25px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 15px;
    cursor: pointer;
    transition: opacity 0.3s;
}

.section-header:hover {
    opacity: 0.95;
}

.section-header h4 {
    margin: 0;
    font-size: 16px;
}

.section-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 8px;
    font-size: 11px;
}

.stat-badge {
    background: rgba(255,255,255,0.2);
    padding: 3px 10px;
    border-radius: 20px;
}

/* Month/Year Picker */
.month-year-picker {
    display: flex;
    gap: 10px;
    align-items: center;
}

.month-year-picker select {
    width: auto;
    min-width: 120px;
}

/* Tooltip Styles */
.custom-tooltip {
    position: fixed;
    background: rgba(0,0,0,0.95);
    color: white;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 11px;
    z-index: 10000;
    pointer-events: none;
    max-width: 260px;
    white-space: normal;
    line-height: 1.4;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.custom-tooltip:before {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 5px 5px 0;
    border-style: solid;
    border-color: rgba(0,0,0,0.95) transparent transparent;
}

.tooltip-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 3px;
    gap: 15px;
}

.tooltip-label {
    font-weight: bold;
}

.tooltip-divider {
    margin: 5px 0;
    border-color: rgba(255,255,255,0.2);
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
}

.loading-spinner {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .section-header {
        background: #4a5568 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
        <p>Loading report...</p>
    </div>
</div>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Working Days & Attendance Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Working Days Report</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <?php
            $workingSectionOptions = [['value' => '0', 'label' => 'All Classes/Sections']];
            foreach ($class_sections as $section) {
                $workingSectionOptions[] = [
                    'value' => $section->cls_sec_id,
                    'label' => $section->class_name . ' - ' . $section->section_name,
                ];
            }
            echo view('components/report_filter_bar', [
                'formId' => 'reportForm',
                'title' => 'Working Days Filters',
                'method' => 'post',
                'fields' => [
                    [
                        'type' => 'select',
                        'id' => 'month_year',
                        'name' => 'month_year',
                        'label' => 'Month & Year',
                        'class' => 'form-control report-select2',
                        'required' => true,
                        'options' => [['value' => '', 'label' => 'Select Month & Year']],
                        'col_class' => 'col-md-4 mb-2',
                    ],
                    [
                        'type' => 'select',
                        'id' => 'cls_sec_id',
                        'name' => 'cls_sec_id',
                        'label' => 'Class / Section',
                        'class' => 'form-control report-select2',
                        'options' => $workingSectionOptions,
                        'col_class' => 'col-md-4 mb-2',
                    ],
                ],
                'actions' => [],
            ]);
        ?>

        <!-- Report Container -->
        <div id="reportContainer"></div>
    </div>
</section>

<script>
$(document).ready(function() {
    if (window.ReportUI && window.ReportUI.initReportSelects) {
        window.ReportUI.initReportSelects('#reportForm');
    }

    // Populate month/year picker
    populateMonthYearPicker();
    
    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        generateReport();
    });

    var autoLoadWorkingDays = function () {
        var monthYear = $('#month_year').val();
        if (monthYear) {
            generateReport();
        }
    };

    $('#month_year').on('change', autoLoadWorkingDays);
    $('#cls_sec_id').on('change', autoLoadWorkingDays);
});

function populateMonthYearPicker() {
    var currentYear = new Date().getFullYear();
    var currentMonth = new Date().getMonth() + 1;
    var $select = $('#month_year');
    
    // Generate options for last 5 years and next 1 year
    for (var y = currentYear - 5; y <= currentYear + 1; y++) {
        for (var m = 1; m <= 12; m++) {
            var monthName = new Date(y, m - 1, 1).toLocaleString('default', { month: 'long' });
            var value = y + '-' + String(m).padStart(2, '0');
            var text = monthName + ' ' + y;
            var selected = (y === currentYear && m === currentMonth) ? 'selected' : '';
            $select.append($('<option>', { value: value, text: text, selected: selected }));
        }
    }
}

function generateReport() {
    var monthYear = $('#month_year').val();
    var cls_sec_id = $('#cls_sec_id').val();
    
    if (!monthYear) {
        toastr.warning('Please select month and year');
        return;
    }
    
    var parts = monthYear.split('-');
    var year = parts[0];
    var month = parts[1];
    
    $('#loadingOverlay').show();
    $('#btnGenerate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
    
    $.ajax({
        url: '<?= base_url("admin/attendance-working-days-report/data") ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            year: year,
            month: month,
            cls_sec_id: cls_sec_id
        },
        success: function(response) {
            if (response.success) {
                renderReport(response.data);
            } else {
                toastr.error(response.msg || 'Error generating report');
                $('#reportContainer').html('<div class="alert alert-danger">' + (response.msg || 'Error loading report') + '</div>');
            }
        },
        error: function() {
            toastr.error('Server error. Please try again.');
            $('#reportContainer').html('<div class="alert alert-danger">Server error. Please try again.</div>');
        },
        complete: function() {
            $('#loadingOverlay').hide();
            $('#btnGenerate').prop('disabled', false).html('<i class="fas fa-chart-line"></i> Generate Report');
        }
    });
}

function renderReport(data) {
    if (!data.sections || data.sections.length === 0) {
        $('#reportContainer').html('<div class="alert alert-info">No data available for the selected period.</div>');
        return;
    }
    
    var html = '';
    
    html += `
        <div class="card">
            <div class="card-body text-center">
                <h3>Working Days & Attendance Report</h3>
                <h4>${data.month_name} ${data.year}</h4>
                <p class="text-muted">Generated on: ${new Date().toLocaleString()}</p>
            </div>
        </div>
        
        <div class="card no-print">
            <div class="card-body">
                <div class="legend">
                    <div class="legend-item"><span class="legend-color" style="background: #d4edda; border-left: 3px solid #28a745;"></span> Working Day</div>
                    <div class="legend-item"><span class="legend-color" style="background: #f8d7da; border-left: 3px solid #dc3545;"></span> OFF (Schedule)</div>
                    <div class="legend-item"><span class="legend-color" style="background: #fff3cd; border-left: 3px solid #ff9800;"></span> OFF (No Record)</div>
                    <div class="legend-item"><span class="legend-color" style="background: #e9ecef; border-left: 3px solid #6c757d;"></span> Future Date</div>
                </div>
            </div>
        </div>
        
        <div class="row mb-3 no-print">
            <div class="col-12 text-right">
                <button onclick="exportReport()" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
                <button onclick="window.print()" class="btn btn-secondary btn-sm">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    `;
    
    for (var i = 0; i < data.sections.length; i++) {
        var section = data.sections[i];
        
        html += `
            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4>
                                <i class="fas fa-chalkboard-teacher"></i> 
                                ${escapeHtml(section.class_name)} - ${escapeHtml(section.section_name)}
                            </h4>
                        </div>
                        <div class="section-stats">
                            <span class="stat-badge">
                                <i class="fas fa-users"></i> Students: ${section.total_students}
                            </span>
                            <span class="stat-badge">
                                <i class="fas fa-calendar-check"></i> Working: ${section.summary.working_days}
                            </span>
                            <span class="stat-badge">
                                <i class="fas fa-calendar-times"></i> OFF (Schedule): ${section.summary.off_schedule_days || 0}
                            </span>
                            <span class="stat-badge">
                                <i class="fas fa-clock"></i> OFF (No Record): ${section.summary.off_no_record_days || 0}
                            </span>
                            <span class="stat-badge">
                                <i class="fas fa-chart-line"></i> Attendance: ${section.summary.attendance_rate}%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="section-body" style="display: block;">
                    <div class="card-body">
                        <!-- Summary Cards Row -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>${section.summary.working_days}</h3>
                                        <p>Working Days</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>${section.summary.off_schedule_days || 0}</h3>
                                        <p>OFF (Schedule)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>${section.summary.off_no_record_days || 0}</h3>
                                        <p>OFF (No Record)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-question-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>${section.summary.attendance_rate}%</h3>
                                        <p>Attendance Rate</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Compact Calendar View -->
                        <div class="mt-4">
                            <div class="calendar-view">
                                <div class="calendar-grid">
                                    <div class="calendar-day-header">Sun</div>
                                    <div class="calendar-day-header">Mon</div>
                                    <div class="calendar-day-header">Tue</div>
                                    <div class="calendar-day-header">Wed</div>
                                    <div class="calendar-day-header">Thu</div>
                                    <div class="calendar-day-header">Fri</div>
                                    <div class="calendar-day-header">Sat</div>
        `;
        
        var firstDayOfMonth = new Date(data.year, data.month - 1, 1);
        var startingDay = firstDayOfMonth.getDay();
        
        for (var d = 0; d < startingDay; d++) {
            html += '<div class="calendar-day"></div>';
        }
        
        for (var j = 0; j < section.daily_status.length; j++) {
            var day = section.daily_status[j];
            var dayOfWeek = new Date(day.date).getDay();
            var weekendClass = (dayOfWeek === 0 || dayOfWeek === 6) ? 'weekend' : '';
            var dayClass = getDayClass(day.status);
            var iconSymbol = getStatusIcon(day.status);
            
            // Prepare detailed tooltip data
            var tooltipData = getDetailedStats(day, section);
            
            html += `
                <div class="calendar-day ${weekendClass} ${dayClass}" 
                     data-tooltip='${JSON.stringify(tooltipData)}'
                     onmouseenter="showTooltip(event, this)"
                     onmouseleave="hideTooltip()">
                    <div class="date-row">
                        <span class="day-num">${day.day_num}</span>
                        <span class="status-icon">${iconSymbol}</span>
                    </div>
                    ${day.status === 'working' ? `<div class="status-percentage">${day.percentage}%</div>` : ''}
                </div>
            `;
        }
        
        html += `
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    $('#reportContainer').html(html);
}

function getDayClass(status) {
    var map = {
        'working': 'calendar-day-working',
        'off_schedule': 'calendar-day-off-schedule',
        'off_no_record': 'calendar-day-off-no-record',
        'future': 'calendar-day-future'
    };
    return map[status] || 'calendar-day-off-no-record';
}

function getStatusIcon(status) {
    var map = {
        'working': '✓',
        'off_schedule': '✗',
        'off_no_record': '!',
        'future': '~'
    };
    return map[status] || '?';
}

function getDetailedStats(day, section) {
    var statusText = day.status === 'working' ? 'Working Day' : 
                     (day.status === 'off_schedule' ? 'OFF Day (Schedule)' : 
                     (day.status === 'off_no_record' ? 'OFF Day (No Record)' : 'Future Date'));
    
    var stats = {
        date: day.date,
        day: day.day,
        status: statusText,
        present: day.present_count,
        absent: day.absent_count || 0,
        late: day.late_count || 0,
        leave: day.leave_count || 0,
        total: day.total_students,
        percentage: day.percentage
    };
    
    return stats;
}

// Global tooltip element
var tooltipElement = null;

function showTooltip(event, element) {
    var tooltipData = $(element).data('tooltip');
    if (!tooltipData) return;
    
    if (tooltipElement) {
        tooltipElement.remove();
    }
    
    tooltipElement = $('<div>', { class: 'custom-tooltip' });
    
    var data = typeof tooltipData === 'string' ? JSON.parse(tooltipData) : tooltipData;
    
    var html = `
        <div style="font-weight: bold; margin-bottom: 5px;">${data.date} (${data.day})</div>
        <hr class="tooltip-divider">
        <div class="tooltip-row">
            <span class="tooltip-label">Status:</span>
            <span>${data.status}</span>
        </div>
    `;
    
    if (data.status === 'Working Day') {
        html += `
            <hr class="tooltip-divider">
            <div class="tooltip-row">
                <span class="tooltip-label">Present:</span>
                <span>${data.present} / ${data.total} (${data.percentage}%)</span>
            </div>
            <div class="tooltip-row">
                <span class="tooltip-label">Absent:</span>
                <span>${data.absent}</span>
            </div>
            <div class="tooltip-row">
                <span class="tooltip-label">Late Coming (LC):</span>
                <span>${data.late}</span>
            </div>
            <div class="tooltip-row">
                <span class="tooltip-label">Leave (L):</span>
                <span>${data.leave}</span>
            </div>
        `;
    } else if (data.status === 'OFF Day (Schedule)') {
        html += `<hr class="tooltip-divider"><div class="tooltip-row">School closed as per schedule</div>`;
    } else if (data.status === 'OFF Day (No Record)') {
        html += `<hr class="tooltip-divider"><div class="tooltip-row">No attendance records found for this day</div>`;
    } else if (data.status === 'Future Date') {
        html += `<hr class="tooltip-divider"><div class="tooltip-row">Data not available for future dates</div>`;
    }
    
    tooltipElement.html(html);
    $('body').append(tooltipElement);
    
    // Position tooltip
    var x = event.clientX + 15;
    var y = event.clientY + 15;
    
    tooltipElement.css({
        top: y + 'px',
        left: x + 'px'
    });
}

function hideTooltip() {
    if (tooltipElement) {
        tooltipElement.remove();
        tooltipElement = null;
    }
}

function toggleSection(element) {
    var body = element.nextElementSibling;
    if (body.style.display === "none") {
        body.style.display = "block";
    } else {
        body.style.display = "none";
    }
}

function exportReport() {
    var monthYear = $('#month_year').val();
    var cls_sec_id = $('#cls_sec_id').val();
    
    if (!monthYear) {
        toastr.warning('Please select month and year');
        return;
    }
    
    var parts = monthYear.split('-');
    var year = parts[0];
    var month = parts[1];
    
    var form = $('<form>', {
        action: '<?= base_url("admin/attendance-working-days-report/export") ?>',
        method: 'POST'
    });
    
    form.append($('<input>', { type: 'hidden', name: 'year', value: year }));
    form.append($('<input>', { type: 'hidden', name: 'month', value: month }));
    form.append($('<input>', { type: 'hidden', name: 'cls_sec_id', value: cls_sec_id }));
    
    $('body').append(form);
    form.submit();
    form.remove();
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
</script>

<?= $this->endSection() ?>