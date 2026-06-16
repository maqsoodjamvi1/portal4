<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Attendance Report - <?= esc($student_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 20px;
        }
        
        .report-header h1 {
            font-size: 1.5rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 15px;
            height: 100%;
        }
        
        .stats-card i {
            font-size: 1.8rem;
        }
        
        .stats-card h3 {
            font-size: 1.8rem;
            margin: 8px 0;
        }
        
        .stats-card p {
            font-size: 0.8rem;
            margin: 0;
            color: #6c757d;
        }
        
        .sibling-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 12px;
            margin-bottom: 10px;
            background: white;
            min-width: 140px;
        }
        
        .sibling-card.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .sibling-card.active .text-muted {
            color: rgba(255,255,255,0.8) !important;
        }
        
        .sibling-card .card-body {
            padding: 12px;
        }
        
        /* Student avatar/image styles */
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 8px;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .student-avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .student-avatar-placeholder i {
            font-size: 30px;
            color: white;
        }
        
        .sibling-card.active .student-avatar-placeholder {
            border-color: #fff;
        }
        
        .week-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .week-header {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .week-title {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .week-date {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .attendance-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .attendance-table {
            min-width: 100%;
            border-collapse: collapse;
        }
        
        .attendance-table th,
        .attendance-table td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        
        .attendance-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .attendance-table td {
            font-size: 0.85rem;
        }
        
        .status-badge {
            display: inline-block;
            width: 36px;
            padding: 4px 0;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
        }
        
        .status-P { background-color: #d4edda; color: #155724; }
        .status-A { background-color: #f8d7da; color: #721c24; }
        .status-L { background-color: #fff3cd; color: #856404; }
        .status-EL { background-color: #d1ecf1; color: #0c5460; }
        .status-LC { background-color: #ffe5b4; color: #856404; }
        .status-OFF { background-color: #e2e3e5; color: #383d41; }
        
        .sibling-scroll {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }
        
        @media print {
            .no-print, .sibling-selector, .btn, .report-header {
                display: none !important;
            }
            .week-card {
                break-inside: avoid;
            }
        }
        
        @media (max-width: 576px) {
            .stats-card h3 { font-size: 1.3rem; }
            .stats-card i { font-size: 1.3rem; }
            .attendance-table th, .attendance-table td { padding: 6px 4px; font-size: 0.7rem; }
            .status-badge { width: 28px; font-size: 0.65rem; }
            .sibling-card { min-width: 120px; }
            .student-avatar, .student-avatar-placeholder {
                width: 45px;
                height: 45px;
            }
            .student-avatar-placeholder i {
                font-size: 22px;
            }
        }

        /* Student avatar/image styles */
.student-avatar-container {
    width: 60px;
    height: 60px;
    margin: 0 auto 8px;
}

.student-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.student-avatar-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.student-avatar-placeholder i {
    font-size: 30px;
    color: white;
}

.sibling-card.active .student-avatar-placeholder {
    border-color: #fff;
}

.sibling-card {
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 12px;
    margin-bottom: 10px;
    background: white;
    min-width: 140px;
    flex-shrink: 0;  /* Prevents cards from shrinking */
}

.sibling-card.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.sibling-card.active .text-muted {
    color: rgba(255,255,255,0.8) !important;
}

.sibling-card .card-body {
    padding: 12px;
}

.sibling-scroll {
    display: flex;
    overflow-x: auto;
    gap: 10px;
    padding-bottom: 10px;
    margin-bottom: 10px;
    scrollbar-width: thin;
}

/* Fix for card widths on different screen sizes */
@media (max-width: 768px) {
    .sibling-card {
        min-width: 120px;
    }
    .student-avatar-container {
        width: 50px;
        height: 50px;
    }
    .student-avatar-placeholder i {
        font-size: 24px;
    }
}

@media (max-width: 576px) {
    .sibling-card {
        min-width: 100px;
    }
    .student-avatar-container {
        width: 45px;
        height: 45px;
    }
    .student-avatar-placeholder i {
        font-size: 20px;
    }
    .sibling-card .card-body {
        padding: 8px;
    }
    .sibling-card h6 {
        font-size: 0.8rem;
    }
    .sibling-card small {
        font-size: 0.65rem;
    }
}
    </style>
</head>
<body>

<div class="container-fluid px-3 px-md-4">
    <!-- Header -->
    <div class="report-header text-center no-print">
        <h1><i class="fas fa-calendar-check"></i> Attendance Report</h1>
        <p class="mb-0">Track your children's attendance</p>
    </div>

   <!-- Sibling Selector -->
<div class="sibling-selector no-print">
    <label class="form-label fw-bold mb-2"><i class="fas fa-users"></i> Select Child</label>
    <div class="sibling-scroll" id="sibling-list">
        <?php if (isset($children) && is_array($children) && count($children) > 0): ?>
            <?php foreach ($children as $index => $child): ?>
            <div class="card sibling-card" data-student-id="<?= $child->student_id ?>" data-index="<?= $index ?>">
                <div class="card-body text-center p-2">
                    <div class="student-avatar-container">
                        <?php if (!empty($child->profile_photo) && file_exists(FCPATH . 'uploads/' . $child->profile_photo)): ?>
                            <img src="<?= base_url('uploads/' . $child->profile_photo) ?>" class="student-avatar" alt="<?= esc($child->first_name) ?> <?= esc($child->last_name ?? '') ?>">
                        <?php else: ?>
                            <div class="student-avatar-placeholder">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h6 class="card-title mb-0 mt-1"><?= esc($child->first_name) ?> <?= esc($child->last_name ?? '') ?></h6>
                    <small class="text-muted">
                        <?= esc($child->class_short_name ?? 'N/A') ?>
                        <?= ($child->section_name ?? '') ? ' - ' . esc($child->section_name) : '' ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">No children found</div>
        <?php endif; ?>
    </div>
</div>

    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Loading attendance data...</p>
    </div>

    <!-- Attendance Content -->
    <div id="attendance-content"></div>

    <!-- Print Footer -->
    <div class="text-center mt-4 mb-3 no-print">
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260615b') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStudentId = null;
    const shareToken = <?= json_encode($share_token ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    
    // Sibling selection
    $('.sibling-card').on('click', function() {
        $('.sibling-card').removeClass('active');
        $(this).addClass('active');
        currentStudentId = $(this).data('student-id');
        loadAttendance();
    });

    // Auto-select first child
    <?php if (!empty($children)): ?>
    setTimeout(function() {
        $('.sibling-card:first').click();
    }, 300);
    <?php endif; ?>
    
    function loadAttendance() {
        $('#loading-spinner').show();
        $('#attendance-content').hide();

        $.post('<?= base_url("parent/attendance/getChildAttendance") ?>', {
            student_id: currentStudentId,
            share_token: shareToken
        }, function(response) {
            $('#loading-spinner').hide();
            if (response.success) {
                if (response.debug) {
                    console.log('=== DEBUG INFO ===');
                    console.log('Off Days:', response.debug.off_days);
                    console.log('Skipped Days:', response.debug.skipped_days);
                    console.log('cls_sec_id:', response.debug.cls_sec_id);
                    console.log('Total Dates:', response.debug.total_dates);
                    console.log('Attendance Count:', response.debug.attendance_count);
                }
                displayAttendance(response.data);
            } else {
                $('#attendance-content').html('<div class="alert alert-danger">Failed to load attendance data</div>').show();
            }
        }, 'json').fail(function() {
            $('#loading-spinner').hide();
            $('#attendance-content').html('<div class="alert alert-danger">Server error. Please try again.</div>').show();
        });
    }

    function formatYmdShort(ymd) {
        if (!ymd) return '';
        const dt = new Date(String(ymd).replace(/-/g, '/') + ' 12:00:00');
        if (isNaN(dt.getTime())) return ymd;
        return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function renderSummaryCards(summary, compact) {
        const totalDays = summary.total_days || 0;
        const presentCount = summary.present_count || 0;
        const absentCount = summary.absent_count || 0;
        const lateCount = summary.late_count || 0;
        const lateComingCount = summary.late_coming_count || 0;
        const earlyLeaveCount = summary.early_leave_count || 0;
        const attendanceRate = summary.attendance_rate || 0;
        const mb = compact ? 'mb-2' : 'mb-2';
        const col = compact ? 'col-6 col-md-4 col-lg-2' : 'col-6 col-md-2';
        let h = `<div class="row ${compact ? 'g-2' : ''}">`;
        h += `<div class="${col} ${mb}"><div class="stats-card"><i class="fas fa-calendar-alt text-info"></i><h3>${totalDays}</h3><p>Total Days</p></div></div>`;
        h += `<div class="${col} ${mb}"><div class="stats-card"><i class="fas fa-check-circle text-success"></i><h3>${presentCount}</h3><p>Present (P)</p></div></div>`;
        h += `<div class="${col} ${mb}"><div class="stats-card"><i class="fas fa-times-circle text-danger"></i><h3>${absentCount}</h3><p>Absent (A)</p></div></div>`;
        h += `<div class="${col} ${mb}"><div class="stats-card"><i class="fas fa-clock text-warning"></i><h3>${lateCount}</h3><p>Late (L)</p></div></div>`;
        h += `<div class="${col} ${mb}"><div class="stats-card"><i class="fas fa-hourglass-half text-info"></i><h3>${lateComingCount}</h3><p>Late Coming (LC)</p></div></div>`;
        h += `<div class="${col} ${mb}"><div class="stats-card"><i class="fas fa-chart-line text-primary"></i><h3>${attendanceRate}%</h3><p>Attendance Rate</p></div></div>`;
        h += `</div>`;
        if (earlyLeaveCount > 0) {
            h += `<div class="row"><div class="col-6 col-md-4 mx-auto ${mb}"><div class="stats-card"><i class="fas fa-sign-out-alt text-secondary"></i><h3>${earlyLeaveCount}</h3><p>Early Leave (EL)</p></div></div></div>`;
        }
        return h;
    }

    function displayAttendance(data) {
        const student = data.student;
        const attendance = data.attendance;
        const summary = data.summary;
        const currentTerm = data.current_term || null;
        const otherTerms = data.other_terms || [];
        const workingDays = data.working_days || ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Map full day names to short names for display
        const dayNameMap = {
            'Monday': 'Mon',
            'Tuesday': 'Tue',
            'Wednesday': 'Wed',
            'Thursday': 'Thu',
            'Friday': 'Fri',
            'Saturday': 'Sat',
            'Sunday': 'Sun'
        };
        
        // Get the short day names for display columns
        const displayDays = workingDays.map(function(day) {
            return dayNameMap[day] || day.substring(0, 3);
        });
        
        const weeklyData = groupByWeek(attendance);
        
        const periodLabel = currentTerm
            ? `Current term: ${escapeHtml(currentTerm.term_name || '')} · ${escapeHtml(currentTerm.session_name || '')}<br><small class="text-muted">${formatYmdShort(currentTerm.start_date)} – ${formatYmdShort(currentTerm.end_date)}</small><br><small class="text-muted">Attendance counted through ${summary.end_date}</small>`
            : `<small class="text-muted">Session summary: ${summary.start_date} - ${summary.end_date}</small>`;

        let html = `
            <div class="text-center mb-4">
                <h3>${escapeHtml(student.first_name)} ${escapeHtml(student.last_name || '')}</h3>
                <p class="text-muted">${escapeHtml(student.class_short_name || '')} ${student.section_name ? '- ' + escapeHtml(student.section_name) : ''}</p>
                <p class="mb-0">${periodLabel}</p>
            </div>

            <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Current term (detail)</h5>
            ${renderSummaryCards(summary, false)}
        `;

        if (weeklyData.length === 0) {
            html += `<div class="alert alert-info text-center">No attendance records found for the current term period</div>`;
        } else {
            weeklyData.forEach(function(week, weekIndex) {
                const weekNumber = weekIndex + 1;
                
                // Create a map of day names to status
                const statusMap = {};
                week.days.forEach(function(day) {
                    statusMap[day.full_day_name || day.day_name] = day.status;
                });
                
                // Calculate weekly counts
                const weekPresentCount = week.days.filter(d => d.status === 'P').length;
                const weekAbsentCount = week.days.filter(d => d.status === 'A').length;
                const weekLateCount = week.days.filter(d => d.status === 'L').length;
                const weekLateComingCount = week.days.filter(d => d.status === 'LC').length;
                const weekEarlyLeaveCount = week.days.filter(d => d.status === 'EL').length;
                
                html += `
                    <div class="week-card">
                        <div class="week-header d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <span class="week-title">Week ${weekNumber}</span>
                                <span class="week-date">${week.start_date} - ${week.end_date}</span>
                            </div>
                            <div class="week-summary">
                                <span class="text-success">P: ${weekPresentCount}</span> |
                                <span class="text-danger">A: ${weekAbsentCount}</span> |
                                <span class="text-warning">L: ${weekLateCount}</span> |
                                <span class="text-info">LC: ${weekLateComingCount}</span>
                                ${weekEarlyLeaveCount > 0 ? `| <span class="text-secondary">EL: ${weekEarlyLeaveCount}</span>` : ''}
                            </div>
                        </div>
                        <div class="attendance-scroll">
                            <table class="attendance-table">
                                <thead>
                                    <tr>`;
                
                displayDays.forEach(function(day) {
                    html += `<th>${day}</th>`;
                });
                
                html += `</tr>
                                </thead>
                                <tbody>
                                    <tr>`;
                
                displayDays.forEach(function(day) {
                    // Find the full day name to match with statusMap
                    let fullDay = '';
                    for (let [key, value] of Object.entries(dayNameMap)) {
                        if (value === day) {
                            fullDay = key;
                            break;
                        }
                    }
                    
                    const status = statusMap[fullDay] || 'OFF';
                    let statusText = status;
                    let statusClass = `status-${status}`;
                    
                    html += `<td class="text-center"><span class="status-badge ${statusClass}">${statusText}</span></td>`;
                });
                
                html += `</tr>
                                </tbody>
                            </table>
                        </div>
                    </div>`;
            });
        }

        if (otherTerms.length > 0) {
            html += `<hr class="my-4"><h5 class="mb-3"><i class="fas fa-layer-group"></i> Other terms &amp; sessions <small class="text-muted fw-normal">(summary only)</small></h5>`;
            otherTerms.forEach(function(ot) {
                const sn = escapeHtml(ot.session_name || '');
                const tn = escapeHtml(ot.term_name || '');
                const dr = formatYmdShort(ot.start_date) + ' – ' + formatYmdShort(ot.end_date);
                html += `<div class="week-card mb-3"><div class="week-header"><span class="week-title">${sn}</span> · <span class="week-title">${tn}</span><div class="week-date">${dr}</div></div><div class="p-3">${renderSummaryCards(ot.summary || {}, true)}</div></div>`;
            });
        }
        
        $('#attendance-content').html(html).show();
    }

    function groupByWeek(attendance) {
        const weeks = [];
        if (attendance.length === 0) return weeks;
        
        let currentWeek = [];
        let currentWeekStart = null;
        let currentWeekEnd = null;
        
        attendance.forEach(function(day, index) {
            const currentDate = new Date(day.date);
            
            // Get the week number for this date
            const weekNumber = getWeekNumber(currentDate);
            
            if (index === 0) {
                // First day - start first week
                currentWeekStart = day.date;
                currentWeek = [day];
            } else {
                const prevDate = new Date(attendance[index - 1].date);
                const prevWeekNumber = getWeekNumber(prevDate);
                
                if (weekNumber === prevWeekNumber) {
                    // Same week - add to current week
                    currentWeek.push(day);
                } else {
                    // Different week - save current week and start new one
                    weeks.push({
                        start_date: formatDate(currentWeekStart),
                        end_date: formatDate(attendance[index - 1].date),
                        days: currentWeek
                    });
                    currentWeekStart = day.date;
                    currentWeek = [day];
                }
            }
        });
        
        // Push the last week
        if (currentWeek.length > 0) {
            weeks.push({
                start_date: formatDate(currentWeekStart),
                end_date: formatDate(attendance[attendance.length - 1].date),
                days: currentWeek
            });
        }
        
        return weeks;
    }

    function getWeekNumber(date) {
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);
        // Set to nearest Thursday: current date + 4 - current day number
        // Then get weeks
        d.setDate(d.getDate() + 4 - (d.getDay() || 7));
        const yearStart = new Date(d.getFullYear(), 0, 1);
        const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
        return weekNo;
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
});
</script>
</body>
</html>
