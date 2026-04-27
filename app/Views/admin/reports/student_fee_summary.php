<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><?= $title ?></h4>
                <small>Campus ID: <?= $campus_id ?> | Session ID: <?= $session_id ?></small>
            </div>
            <div class="card-body">
                <!-- Export Button -->
                <div class="mb-3">
                    <a href="<?= base_url('admin/student_fee_summary/export_excel') ?>" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>

                <!-- Totals Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h6 class="card-title">Total Students</h6>
                                <h3 class="card-text"><?= number_format($totals['total_active_students']) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h6 class="card-title">Total Monthly Fee</h6>
                                <h3 class="card-text">PKR <?= number_format($totals['total_net_monthly_fee'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h6 class="card-title">Total Annual Fee</h6>
                                <h3 class="card-text">PKR <?= number_format($totals['total_projected_annual_fee'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h6 class="card-title">Avg Fee/Student</h6>
                                <h3 class="card-text">PKR <?= number_format($totals['avg_fee_per_student'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats Row -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Total Discount Given</h6>
                                <h4 class="card-text text-danger">PKR <?= number_format($totals['total_discount_given'], 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Minimum Fee</h6>
                                <h4 class="card-text text-info">PKR <?= number_format($totals['minimum_fee'], 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Maximum Fee</h6>
                                <h4 class="card-text text-info">PKR <?= number_format($totals['maximum_fee'], 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-bar"></i> Fee Distribution Chart
                            </div>
                            <div class="card-body">
                                <canvas id="feeChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-chart-pie"></i> Student Distribution by Fee Range
                            </div>
                            <div class="card-body">
                                <canvas id="studentChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table"></i> Fee Summary by Amount
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fee Amount (PKR)</th>
                                        <th>Number of Students</th>
                                        <th>Total Monthly Fee (PKR)</th>
                                        <th>Projected Annual Fee (PKR)</th>
                                        <th>% of Students</th>
                                        <th>% of Total Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($summary) && is_array($summary)): ?>
                                        <?php foreach ($summary as $item): ?>
                                        <tr>
                                            <td class="text-end"><?= number_format($item['fee_amount'], 2) ?></td>
                                            <td class="text-center"><?= $item['number_of_students'] ?></td>
                                            <td class="text-end"><?= number_format($item['total_monthly_fee_for_this_amount'], 2) ?></td>
                                            <td class="text-end"><?= number_format($item['projected_annual_fee_for_this_amount'], 2) ?></td>
                                            <td class="text-center"><?= $item['percentage_of_total_students'] ?>%</td>
                                            <td class="text-center"><?= $item['percentage_of_total_fee'] ?>%</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No data found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr style="font-weight: bold;">
                                        <td class="text-end">TOTAL</td>
                                        <td class="text-center"><?= number_format($totals['total_active_students']) ?></td>
                                        <td class="text-end"><?= number_format($totals['total_net_monthly_fee'], 2) ?></td>
                                        <td class="text-end"><?= number_format($totals['total_projected_annual_fee'], 2) ?></td>
                                        <td class="text-center">100%</td>
                                        <td class="text-center">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Class-wise Breakdown -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-school"></i> Class-wise Breakdown
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="classBreakdownTable" class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Fee Amount (PKR)</th>
                                        <th>Number of Students</th>
                                        <th>Total Monthly Fee (PKR)</th>
                                        <th>Projected Annual Fee (PKR)</th>
                                        <th>Avg Discount/Student (PKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($class_breakdown) && is_array($class_breakdown)): ?>
                                        <?php foreach ($class_breakdown as $item): ?>
                                        <tr>
                                            <td><?= $item['class_name'] ?></td>
                                            <td class="text-end"><?= number_format($item['fee_amount'], 2) ?></td>
                                            <td class="text-center"><?= $item['number_of_students'] ?></td>
                                            <td class="text-end"><?= number_format($item['total_monthly_fee'], 2) ?></td>
                                            <td class="text-end"><?= number_format($item['projected_annual_fee'], 2) ?></td>
                                            <td class="text-end"><?= number_format($item['avg_discount_per_student'], 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No data found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Student Details -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-users"></i> Student Details
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="studentDetailsTable" class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Reg No</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Standard Fee (PKR)</th>
                                        <th>Discount (PKR)</th>
                                        <th>Net Monthly Fee (PKR)</th>
                                        <th>Projected Annual Fee (PKR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($student_details) && is_array($student_details)): ?>
                                        <?php foreach ($student_details as $student): ?>
                                        <tr>
                                            <td><?= $student['reg_no'] ?></td>
                                            <td><?= $student['student_name'] ?></td>
                                            <td><?= $student['class_name'] ?></td>
                                            <td><?= $student['section_name'] ?></td>
                                            <td class="text-end"><?= number_format($student['standard_monthly_fee'], 2) ?></td>
                                            <td class="text-end text-danger"><?= number_format($student['discount_amount'], 2) ?></td>
                                            <td class="text-end fw-bold"><?= number_format($student['net_monthly_fee'], 2) ?></td>
                                            <td class="text-end"><?= number_format($student['projected_annual_fee'], 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No data found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#classBreakdownTable').DataTable({
                pageLength: 25,
                order: [[0, 'asc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
            
            $('#studentDetailsTable').DataTable({
                pageLength: 25,
                order: [[6, 'desc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        });

        // Charts
        <?php if (!empty($summary) && is_array($summary)): ?>
        const feeAmounts = <?= json_encode(array_column($summary, 'fee_amount')) ?>;
        const studentCounts = <?= json_encode(array_column($summary, 'number_of_students')) ?>;
        const totalFees = <?= json_encode(array_column($summary, 'total_monthly_fee_for_this_amount')) ?>;
        
        // Fee Chart (Bar Chart)
        const feeCtx = document.getElementById('feeChart').getContext('2d');
        new Chart(feeCtx, {
            type: 'bar',
            data: {
                labels: feeAmounts.map(amount => 'PKR ' + Number(amount).toLocaleString()),
                datasets: [{
                    label: 'Total Monthly Fee (PKR)',
                    data: totalFees,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'PKR ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Total: PKR ' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Student Chart (Pie Chart)
        const studentCtx = document.getElementById('studentChart').getContext('2d');
        new Chart(studentCtx, {
            type: 'pie',
            data: {
                labels: feeAmounts.map(amount => 'PKR ' + Number(amount).toLocaleString()),
                datasets: [{
                    data: studentCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} students (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>