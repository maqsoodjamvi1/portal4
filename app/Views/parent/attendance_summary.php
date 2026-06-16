<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Children's Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260615b') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .student-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
        }
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .student-card.active {
            border: 2px solid #0d6efd;
            background-color: #e7f1ff;
        }
        .attendance-badge {
            font-size: 14px;
            padding: 5px 12px;
            border-radius: 20px;
        }
        .status-P { background-color: #d4edda; color: #155724; }
        .status-A { background-color: #f8d7da; color: #721c24; }
        .status-L { background-color: #fff3cd; color: #856404; }
        .status-EL { background-color: #d1ecf1; color: #0c5460; }
        .status- { background-color: #e2e3e5; color: #383d41; }
        .calendar-day {
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            margin: 2px;
        }
        .calendar-day.P { background-color: #d4edda; border-color: #c3e6cb; }
        .calendar-day.A { background-color: #f8d7da; border-color: #f5c6cb; }
        .calendar-day.L { background-color: #fff3cd; border-color: #ffeeba; }
        .calendar-day.EL { background-color: #d1ecf1; border-color: #bee5eb; }
        .filter-btn {
            margin: 5px;
        }
        .share-link {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            word-break: break-all;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .print-only {
            display: none;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-calendar-check"></i> Attendance Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('parent/logout') ?>">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-calendar-alt"></i> My Children's Attendance</h2>
                <p class="text-muted">View and track attendance records for all your children</p>
            </div>
        </div>

        <!-- Children Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <h5><i class="fas fa-users"></i> Select Child</h5>
                <div class="row" id="children-list">
                    <?php foreach ($children as $index => $child): ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="student-card card" data-student-id="<?= $child->student_id ?>" data-index="<?= $index ?>">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <i class="fas fa-user-graduate fa-3x text-primary"></i>
                                </div>
                                <h6 class="card-title mb-0"><?= $child->first_name ?> <?= $child->last_name ?></h6>
                                <small class="text-muted"><?= $child->class_short_name ?? 'N/A' ?> <?= $child->section_name ? '- ' . $child->section_name : '' ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card no-print mb-4">
            <div class="card-body">
                <h5><i class="fas fa-filter"></i> Filter Report</h5>
                <div class="row">
                    <div class="col-md-4">
                        <select id="filter_type" class="form-select">
                            <option value="current_week">Current Week</option>
                            <option value="current_session">Current Session</option>
                            <option value="month" selected>Monthly Report</option>
                            <option value="custom_range">Custom Date Range</option>
                        </select>
                    </div>
                    <div class="col-md-3 month-fields">
                        <input type="text" id="monthpicker" class="form-control" placeholder="Select Month & Year">
                    </div>
                    <div class="col-md-3 custom-fields" style="display:none;">
                        <input type="date" id="start_date" class="form-control" placeholder="Start Date">
                    </div>
                    <div class="col-md-3 custom-fields" style="display:none;">
                        <input type="date" id="end_date" class="form-control" placeholder="End Date">
                    </div>
                    <div class="col-md-2">
                        <button id="apply-filter" class="btn btn-primary w-100">Apply Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Link Section -->
      <!-- Share Link Section -->
<div class="card no-print mb-4" id="share-section" style="display:none;">
    <div class="card-body">
        <h5><i class="fas fa-share-alt"></i> Share Attendance Report</h5>
        <p>Share this link with parents to view attendance in a beautiful web interface:</p>
        <div class="input-group">
            <input type="text" id="share-link" class="form-control" readonly>
            <button class="btn btn-primary" onclick="copyShareLink()">
                <i class="fas fa-copy"></i> Copy Link
            </button>
        </div>
        <small class="text-muted">Parents can click this link to view detailed attendance in their browser</small>
    </div>
</div>

        <!-- Statistics Cards -->
        <div class="row mb-4" id="stats-cards" style="display:none;">
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h6>Attendance Rate</h6>
                    <h2 id="attendance-rate">0%</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <h6>Present Days</h6>
                    <h2 id="present-count">0</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <h6>Absent Days</h6>
                    <h2 id="absent-count">0</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <h6>Total Days</h6>
                    <h2 id="total-days">0</h2>
                </div>
            </div>
        </div>

        <!-- Attendance Calendar View -->
        <div class="card" id="attendance-card" style="display:none;">
            <div class="card-header">
                <h5><i class="fas fa-calendar-week"></i> Attendance Calendar</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="attendance-table">
                        <thead>
                            <tr id="calendar-header"></tr>
                        </thead>
                        <tbody id="calendar-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Print Footer -->
        <div class="print-only text-center mt-4">
            <p>Generated on: <?= date('d M Y H:i:s') ?></p>
            <p>This is a system generated report</p>
        </div>
    </div>

    <!-- Include Month Picker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    let currentStudentId = null;
    let currentSessionId = <?= $session_id ?>;

    // Initialize Month Picker
    $('#monthpicker').datepicker({
        format: "MM yyyy",
        viewMode: "months",
        minViewMode: "months",
        autoclose: true,
        todayHighlight: true
    });

    // Show/hide filter fields
    $('#filter_type').on('change', function() {
        const type = $(this).val();
        $('.month-fields, .custom-fields').hide();
        if (type === 'month') {
            $('.month-fields').show();
        } else if (type === 'custom_range') {
            $('.custom-fields').show();
        }
    });

    // Student selection
    $('.student-card').on('click', function() {
        $('.student-card').removeClass('active');
        $(this).addClass('active');
        currentStudentId = $(this).data('student-id');
        loadAttendance();
        $('#share-section').show();
        updateShareLink();
    });

    // Apply filter
    $('#apply-filter').on('click', function() {
        if (currentStudentId) {
            loadAttendance();
        } else {
            Swal.fire('Info', 'Please select a student first', 'info');
        }
    });

    function loadAttendance() {
        const filterType = $('#filter_type').val();
        const monthYear = $('#monthpicker').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        Swal.fire({
            title: 'Loading...',
            text: 'Fetching attendance data',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.post('<?= base_url("parent/attendance/getChildAttendance") ?>', {
            student_id: currentStudentId,
            session_id: currentSessionId,
            filter_type: filterType,
            month_year: monthYear,
            start_date: startDate,
            end_date: endDate
        }, function(response) {
            Swal.close();
            if (response.success) {
                displayAttendance(response.data);
            } else {
                Swal.fire('Error', 'Failed to load attendance', 'error');
            }
        }, 'json').fail(function() {
            Swal.close();
            Swal.fire('Error', 'Server error', 'error');
        });
    }

    function displayAttendance(data) {
        const summary = data.summary;
        const attendance = data.attendance;
        const student = data.student;

        // Update stats
        $('#attendance-rate').text(summary.attendance_rate + '%');
        $('#present-count').text(summary.present_count);
        $('#absent-count').text(summary.absent_count);
        $('#total-days').text(summary.total_days);
        $('#stats-cards').show();

        // Build calendar view
        let weeks = [];
        let currentWeek = [];
        
        attendance.forEach(function(day, index) {
            const date = new Date(day.date);
            const dayOfWeek = date.getDay();
            
            currentWeek.push(day);
            
            if (dayOfWeek === 6 || index === attendance.length - 1) {
                weeks.push([...currentWeek]);
                currentWeek = [];
            }
        });

        // Build header (Week days)
        let headerHtml = '<th>Date</th><th>Day</th>';
        for (let i = 0; i < 7; i++) {
            // headerHtml += `<th>${getDayName(i)}</th>`;
        }
        headerHtml += '<th>Status</th>';
        $('#calendar-header').html(headerHtml);

        // Build body
        let bodyHtml = '';
        weeks.forEach(function(week) {
            week.forEach(function(day) {
                const statusClass = `status-${day.status}`;
                const statusText = day.status === 'P' ? 'Present' : (day.status === 'A' ? 'Absent' : (day.status === 'L' ? 'Late' : (day.status === 'EL' ? 'Early Leave' : '-')));
                bodyHtml += `
                    <tr>
                        <td>${day.date_formatted}</td>
                        <td>${day.day_name}</td>
                        <td class="${statusClass} text-center fw-bold">${day.status}</td>
                    </tr>
                `;
            });
        });
        
        $('#calendar-body').html(bodyHtml);
        $('#attendance-card').show();

        // Update share link
        updateShareLink();
    }

   function updateShareLink() {
    const shareUrl = `<?= base_url() ?>parent/attendance/share/${currentStudentId}`;
    $('#share-link').val(shareUrl);
}

function copyShareLink() {
    const linkInput = $('#share-link');
    linkInput.select();
    document.execCommand('copy');
    Swal.fire('Success', 'Link copied to clipboard!', 'success');
}

    function getDayName(index) {
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        return days[index];
    }

    // Auto-select first child if available
    <?php if (!empty($children)): ?>
    setTimeout(function() {
        $('.student-card:first').click();
    }, 500);
    <?php endif; ?>
    </script>
</body>
</html>
