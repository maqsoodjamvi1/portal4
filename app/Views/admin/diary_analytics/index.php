<?php $uiNeedsChart = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
    .analytics-card {
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .analytics-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #666;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
    }
    
    .chart-container {
        height: 300px;
        margin-top: 15px;
    }
    
    .type-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 500;
        margin: 3px;
    }
    
    .type-badge.homework { background: #e3f2fd; color: #1976d2; }
    .type-badge.classwork { background: #e8f5e9; color: #388e3c; }
    .type-badge.audio { background: #fce4ec; color: #c2185b; }
    .type-badge.video { background: #fff3e0; color: #f57c00; }
    .type-badge.picture { background: #f3e5f5; color: #7b1fa2; }
    .type-badge.quiz { background: #e0f7fa; color: #00838f; }
    .type-badge.bagpack { background: #e8eaf6; color: #3949ab; }
    
    .data-table {
        font-size: 13px;
    }
    
    .data-table td, .data-table th {
        vertical-align: middle;
        padding: 10px 12px;
    }
    
    .diary-content-preview {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    @media (max-width: 768px) {
        .stat-number {
            font-size: 24px;
        }
        .filter-buttons {
            margin-top: 15px;
        }
    }
</style>

<!-- Content Header -->
<?= view('components/page_header', [
    'title' => 'Class Diary Analytics',
    'icon' => 'fas fa-chart-line',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Class Diary', 'url' => base_url('admin/classdiary-view')],
        ['label' => 'Analytics', 'active' => true],
    ],
]) ?>

<!-- Main Content -->
<section class="content">
    <!-- Filter Section -->
    <div class="filter-section">
        <form id="analyticsFilterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="start_date" id="start_date">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" name="end_date" id="end_date">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-book"></i> Subject</label>
                        <select class="form-control select2" name="subject_id" id="subject_id">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['sid'] ?>"><?= esc($subject['subject_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-graduation-cap"></i> Class</label>
                        <select class="form-control select2" name="class_id" id="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['class_id'] ?>"><?= esc($class['class_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Section</label>
                        <select class="form-control select2" name="section_id" id="section_id">
                            <option value="">All Sections</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?= $section['section_id'] ?>"><?= esc($section['section_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-chalkboard-teacher"></i> Teacher</label>
                        <select class="form-control select2" name="teacher_id" id="teacher_id">
                            <option value="">All Teachers</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher['id'] ?>"><?= esc($teacher['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-layer-group"></i> Term</label>
                        <select class="form-control select2" name="term_id" id="term_id">
                            <option value="">All Terms</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= $term['term_session_id'] ?>"><?= esc($term['term_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-week"></i> Week</label>
                        <select class="form-control select2" name="week_id" id="week_id">
                            <option value="">All Weeks</option>
                            <?php foreach ($weeks as $week): ?>
                                <option value="<?= $week['term_weeks_id'] ?>">Week <?= $week['week_no'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-chart-pie"></i> Report Type</label>
                        <select class="form-control" name="analytics_type" id="analytics_type">
                            <option value="summary">Summary Dashboard</option>
                            <option value="subject_wise">Subject-wise Analysis</option>
                            <option value="teacher_wise">Teacher-wise Analysis</option>
                            <option value="class_wise">Class-wise Analysis</option>
                            <option value="weekly_trend">Weekly Trends</option>
                            <option value="task_completion">Task Completion Analysis</option>
                            <option value="detailed">Detailed Data View</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 filter-buttons">
                    <button type="submit" class="btn btn-primary w-100" style="margin-top: 32px;">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </div>
                <div class="col-md-3 filter-buttons">
                    <button type="button" id="exportBtn" class="btn btn-success w-100" style="margin-top: 32px;">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </button>
                </div>
            </div>
            
            <!-- Feature Toggle Section -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <label><i class="fas fa-sliders-h"></i> Include in Report:</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="include_homework" id="include_homework" value="1" checked>
                            <label class="form-check-label" for="include_homework">Home Work</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="include_classwork" id="include_classwork" value="1" checked>
                            <label class="form-check-label" for="include_classwork">Class Work</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="include_audio" id="include_audio" value="1" checked>
                            <label class="form-check-label" for="include_audio">Audio Tasks</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="include_video" id="include_video" value="1" checked>
                            <label class="form-check-label" for="include_video">Video Tasks</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="include_picture" id="include_picture" value="1" checked>
                            <label class="form-check-label" for="include_picture">Picture Tasks</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="include_quiz" id="include_quiz" value="1" checked>
                            <label class="form-check-label" for="include_quiz">Quizzes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="include_activities" id="include_activities" value="1" checked>
                            <label class="form-check-label" for="include_activities">Activities</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="include_bagpack" id="include_bagpack" value="1" checked>
                            <label class="form-check-label" for="include_bagpack">Bag Pack</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Results Container -->
    <div id="analyticsResults">
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
            <p class="text-muted">Select filters and click "Generate Report" to view analytics</p>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
let currentChart = null;

$(document).ready(function() {
    $('.select2').select2();
    
    // Form submission
    $('#analyticsFilterForm').on('submit', function(e) {
        e.preventDefault();
        loadAnalytics();
    });
    
    // Export button
    $('#exportBtn').on('click', function() {
        exportToExcel();
    });
    
    // Term change - load weeks
    $('#term_id').on('change', function() {
        loadWeeksForTerm($(this).val());
    });
    
    function loadWeeksForTerm(termId) {
        if (termId) {
            $.ajax({
                url: '<?= base_url('admin/classdiary-view/getAllWeeks') ?>',
                type: 'POST',
                data: { term_id: termId },
                dataType: 'json',
                success: function(response) {
                    let weekSelect = $('#week_id');
                    weekSelect.empty().append('<option value="">All Weeks</option>');
                    if (response.weeks) {
                        response.weeks.forEach(week => {
                            weekSelect.append(`<option value="${week.term_weeks_id}">Week ${week.week_no} (${week.start_date} to ${week.end_date})</option>`);
                        });
                    }
                }
            });
        }
    }
    
    function loadAnalytics() {
        $('#analyticsResults').html(`
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-pulse fa-3x text-primary"></i>
                <p class="mt-3">Loading analytics data...</p>
            </div>
        `);
        
        $.ajax({
            url: '<?= base_url('admin/diary-analytics/getAnalytics') ?>',
            type: 'POST',
            data: $('#analyticsFilterForm').serialize(),
            dataType: 'json',
            success: function(response) {
                let reportType = $('#analytics_type').val();
                let html = '';
                
                switch(reportType) {
                    case 'summary':
                        html = renderSummaryDashboard(response);
                        break;
                    case 'subject_wise':
                        html = renderSubjectWiseReport(response);
                        break;
                    case 'teacher_wise':
                        html = renderTeacherWiseReport(response);
                        break;
                    case 'class_wise':
                        html = renderClassWiseReport(response);
                        break;
                    case 'weekly_trend':
                        html = renderWeeklyTrendReport(response);
                        break;
                    case 'task_completion':
                        html = renderTaskCompletionReport(response);
                        break;
                    case 'detailed':
                        html = renderDetailedDataView(response);
                        break;
                    default:
                        html = renderSummaryDashboard(response);
                }
                
                $('#analyticsResults').html(html);
                
                // Initialize DataTables if present
                if ($.fn.DataTable) {
                    $('.data-table').DataTable({
                        pageLength: 25,
                        responsive: true,
                        language: { search: "Search:", lengthMenu: "Show _MENU_ entries" }
                    });
                }
            },
            error: function(xhr) {
                $('#analyticsResults').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Error loading analytics: ${xhr.responseText}
                    </div>
                `);
            }
        });
    }
    
    function renderSummaryDashboard(data) {
        let stats = data.statistics || {};
        let total = data.total_entries || 0;
        
        return `
            <div class="row">
                <!-- Summary Cards -->
                <div class="col-md-3">
                    <div class="card analytics-card bg-primary text-white">
                        <div class="card-body">
                            <div class="stat-number">${total}</div>
                            <div class="stat-label">Total Diary Entries</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card analytics-card bg-info text-white">
                        <div class="card-body">
                            <div class="stat-number">${stats.has_homework || 0}</div>
                            <div class="stat-label">Entries with Homework</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card analytics-card bg-success text-white">
                        <div class="card-body">
                            <div class="stat-number">${stats.has_classwork || 0}</div>
                            <div class="stat-label">Entries with Classwork</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card analytics-card bg-warning text-white">
                        <div class="card-body">
                            <div class="stat-number">${stats.has_quiz || 0}</div>
                            <div class="stat-label">Quizzes Assigned</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Feature Distribution Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-pie"></i> Diary Features Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="featureChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Subject Distribution -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-book"></i> Top Subjects by Entries</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Subject</th><th>Entries</th><th>%</th></tr>
                                </thead>
                                <tbody>
                                    ${(data.subject_distribution || []).map(s => `
                                        <tr>
                                            <td>${escapeHtml(s.subject_name)}</td>
                                            <td>${s.count}</td>
                                            <td>${total ? Math.round(s.count/total*100) : 0}%</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chalkboard-teacher"></i> Teacher Activity</h5>
                        </div>
                        <div class="card-body">
                            <table class="table data-table">
                                <thead>
                                    <tr><th>Teacher</th><th>Entries</th><th>Homework</th><th>Classwork</th><th>Audio</th><th>Video</th><th>Quiz</th></tr>
                                </thead>
                                <tbody>
                                    ${(data.teacher_distribution || []).map(t => `
                                        <tr>
                                            <td>${escapeHtml(t.teacher_name)}</td>
                                            <td>${t.count}</td>
                                            <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Render chart after DOM is ready
        setTimeout(() => {
            let ctx = document.getElementById('featureChart')?.getContext('2d');
            if (ctx && currentChart) currentChart.destroy();
            if (ctx) {
                currentChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Homework', 'Classwork', 'Audio', 'Video', 'Picture', 'Quiz', 'Bag Pack'],
                        datasets: [{
                            label: 'Number of Entries',
                            data: [
                                stats.has_homework || 0,
                                stats.has_classwork || 0,
                                stats.has_audio || 0,
                                stats.has_video || 0,
                                stats.has_picture || 0,
                                stats.has_quiz || 0,
                                stats.has_bagpack || 0
                            ],
                            backgroundColor: ['#1976d2', '#388e3c', '#c2185b', '#f57c00', '#7b1fa2', '#00838f', '#3949ab']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: true }
                });
            }
        }, 100);
    }
    
    function renderSubjectWiseReport(data) {
        let subjects = data.subject_wise_data || [];
        return `
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> Subject-wise Analysis Report</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Total</th>
                                    <th>Homework</th>
                                    <th>Classwork</th>
                                    <th>Audio</th>
                                    <th>Video</th>
                                    <th>Picture</th>
                                    <th>Quiz</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${subjects.map(s => `
                                    <tr>
                                        <td><strong>${escapeHtml(s.subject_name)}</strong></td>
                                        <td>${s.total_entries}</td>
                                        <td>${s.has_homework} <span class="text-muted">(${s.homework_percentage}%)</span></td>
                                        <td>${s.has_classwork} <span class="text-muted">(${s.classwork_percentage}%)</span></td>
                                        <td>${s.has_audio} <span class="text-muted">(${s.audio_percentage}%)</span></td>
                                        <td>${s.has_video} <span class="text-muted">(${s.video_percentage}%)</span></td>
                                        <td>${s.has_picture} <span class="text-muted">(${s.picture_percentage}%)</span></td>
                                        <td>${s.has_quiz} <span class="text-muted">(${s.quiz_percentage}%)</span></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }
    
    function renderTeacherWiseReport(data) {
        let teachers = data.teacher_wise_data || [];
        return `
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chalkboard-teacher"></i> Teacher-wise Analysis Report</h5>
                </div>
                <div class="card-body">
                    <table class="table data-table">
                        <thead>
                            <tr><th>Teacher</th><th>Designation</th><th>Total</th><th>Homework</th><th>Classwork</th><th>Audio</th><th>Video</th><th>Picture</th><th>Quiz</th></tr>
                        </thead>
                        <tbody>
                            ${teachers.map(t => `
                                <tr><td>${escapeHtml(t.teacher_name)}</td>
                                    <td>${escapeHtml(t.teacher_designation || '-')}</td>
                                    <td><strong>${t.total_entries}</strong></td>
                                    <td><span class="type-badge homework">${t.has_homework}</span></td>
                                    <td><span class="type-badge classwork">${t.has_classwork}</span></td>
                                    <td><span class="type-badge audio">${t.has_audio}</span></td>
                                    <td><span class="type-badge video">${t.has_video}</span></td>
                                    <td><span class="type-badge picture">${t.has_picture}</span></td>
                                    <td><span class="type-badge quiz">${t.has_quiz}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    function renderClassWiseReport(data) {
        let classes = data.class_wise_data || [];
        return `
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-school"></i> Class-wise Analysis Report</h5>
                </div>
                <div class="card-body">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>Class</th><th>Section</th><th>Entries</th><th>Subjects</th><th>Teachers</th>
                                <th>Audio</th><th>Video</th><th>Picture</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${classes.map(c => `
                                <tr>
                                    <td>${escapeHtml(c.class_name)}</td>
                                    <td>${escapeHtml(c.section_name)}</td>
                                    <td><strong>${c.total_entries}</strong></td>
                                    <td>${c.subjects_covered}</td>
                                    <td>${c.teachers_involved}</td>
                                    <td>${c.total_audio_tasks}</td>
                                    <td>${c.total_video_tasks}</td>
                                    <td>${c.total_picture_tasks}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    function renderWeeklyTrendReport(data) {
        let trends = data.weekly_trends || [];
        return `
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Weekly Trends Report</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="250"></canvas>
                    <hr>
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>Week</th><th>Dates</th><th>Entries</th><th>Homework</th>
                                <th>Classwork</th><th>Audio</th><th>Video</th><th>Picture</th><th>Quiz</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${trends.map(w => `
                                <tr>
                                    <td>Week ${w.week_no}</td>
                                    <td>${w.week_start_date} to ${w.week_end_date}</td>
                                    <td><strong>${w.total_entries}</strong></td>
                                    <td>${w.homework_count}</td>
                                    <td>${w.classwork_count}</td>
                                    <td>${w.audio_count}</td>
                                    <td>${w.video_count}</td>
                                    <td>${w.picture_count}</td>
                                    <td>${w.quiz_count}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        setTimeout(() => {
            let ctx = document.getElementById('trendChart')?.getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: trends.map(w => `Week ${w.week_no}`),
                        datasets: [
                            { label: 'Total Entries', data: trends.map(w => w.total_entries), borderColor: '#1976d2', fill: false },
                            { label: 'Homework', data: trends.map(w => w.homework_count), borderColor: '#388e3c', fill: false },
                            { label: 'Quizzes', data: trends.map(w => w.quiz_count), borderColor: '#f57c00', fill: false }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: true }
                });
            }
        }, 100);
    }
    
    function renderTaskCompletionReport(data) {
        let stats = data.completion_statistics || [];
        let performance = data.subject_performance || [];
        
        return `
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-check-circle"></i> Task Completion Statistics</h5>
                        </div>
                        <div class="card-body">
                            <table class="table data-table">
                                <thead>
                                    <tr>
                                        <th>Task Type</th><th>Total</th><th>Submitted</th><th>Approved</th>
                                        <th>Rejected</th><th>Pending</th><th>Avg Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${stats.map(s => `
                                        <tr>
                                            <td><span class="type-badge ${s.task_type}">${s.task_type.toUpperCase()}</span></td>
                                            <td>${s.total_assigned}</td>
                                            <td>${s.submitted || 0}</td>
                                            <td class="text-success">${s.approved || 0}</td>
                                            <td class="text-danger">${s.rejected || 0}</td>
                                            <td class="text-warning">${s.pending || 0}</td>
                                            <td>${s.avg_rating ? s.avg_rating.toFixed(1) + ' ?' : '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-trophy"></i> Top Performing Subjects</h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr><th>Subject</th><th>Task Type</th><th>Total Tasks</th><th>Approved</th><th>Success Rate</th><th>Avg Rating</th></tr>
                                </thead>
                                <tbody>
                                    ${performance.map(p => `
                                        <tr>
                                            <td>${escapeHtml(p.subject_name)}</td>
                                            <td>${p.task_type}</td>
                                            <td>${p.total}</td>
                                            <td>${p.approved_count}</td>
                                            <td>${p.total ? Math.round(p.approved_count/p.total*100) : 0}%</td>
                                            <td>${p.avg_rating || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function renderDetailedDataView(data) {
        let entries = data.diary_entries || [];
        
        if (entries.length === 0) {
            return `<div class="alert alert-info">No diary entries found for the selected filters.</div>`;
        }
        
        return `
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Detailed Diary Data 
                        <span class="badge text-bg-secondary">${entries.length} entries</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered data-table">
                            <thead>
                                <tr>
                                    <th>Date</th><th>Class/Section</th><th>Subject</th><th>Teacher</th>
                                    <th>Week</th><th>Home Work</th><th>Class Work</th>
                                    <th>Features</th><th>Bag Pack</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${entries.map(e => `
                                    <tr>
                                        <td>${e.diary_date}</td>
                                        <td>${escapeHtml(e.class_name)} / ${escapeHtml(e.section_name)}</td>
                                        <td><strong>${escapeHtml(e.subject_name)}</strong></td>
                                        <td>${escapeHtml(e.teacher_name || '-')}</td>
                                        <td>Week ${e.week_no}</td>
                                        <td class="diary-content-preview">${stripHtml(e.homework || '-')}</td>
                                        <td class="diary-content-preview">${stripHtml(e.classwork || '-')}</td>
                                        <td>
                                            ${e.has_audio ? '<span class="type-badge audio" title="Audio Task"><i class="fas fa-headphones"></i></span>' : ''}
                                            ${e.has_video ? '<span class="type-badge video" title="Video Task"><i class="fas fa-video"></i></span>' : ''}
                                            ${e.has_picture ? '<span class="type-badge picture" title="Picture Task"><i class="fas fa-image"></i></span>' : ''}
                                            ${e.has_quiz ? '<span class="type-badge quiz" title="Quiz"><i class="fas fa-question-circle"></i></span>' : ''}
                                        </td>
                                        <td>${(e.bagpack_items || []).join(', ') || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }
    
    function exportToExcel() {
        let formData = $('#analyticsFilterForm').serialize();
        $('<form method="POST" action="<?= base_url('admin/diary-analytics/export') ?>">' + 
          '<input type="hidden" name="' + formData.replace(/=/g, '"><input type="hidden" name="').replace(/&/g, '" value="') + '">' +
          '</form>').appendTo('body').submit();
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    function stripHtml(html) {
        if (!html) return '';
        let tmp = document.createElement('DIV');
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || '';
    }
});
</script>

<?= $this->endSection() ?>