<style>
.report-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #333;
}
.report-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 5px;
}
.report-date {
    font-size: 12px;
    color: #666;
}
.bmi-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}
.bmi-underweight { background: #3498db; color: white; }
.bmi-normal { background: #2ecc71; color: white; }
.bmi-overweight { background: #f39c12; color: white; }
.bmi-obese { background: #e74c3c; color: white; }
.summary-stats {
    margin-bottom: 30px;
}
.stat-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    border: 1px solid #e0e0e0;
}
.stat-value {
    font-size: 28px;
    font-weight: bold;
}
.stat-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}
@media print {
    .no-print { display: none; }
    .stat-box { border: 1px solid #ddd; }
    .report-header { border-bottom: 1px solid #000; }
}
</style>

<div class="report-container">
    <div class="report-header">
        <div class="report-title">BMI Health Report</div>
        <div class="report-date">Generated on: <?= date('d-M-Y H:i:s') ?></div>
    </div>
    
    <?php if ($reportType == 'summary'): ?>
        <!-- Summary Report -->
        <?php
        $total = count($students);
        $underweight = 0;
        $normal = 0;
        $overweight = 0;
        $obese = 0;
        $totalBmi = 0;
        
        foreach ($students as $s) {
            if ($s->bmi_category == 'underweight') $underweight++;
            elseif ($s->bmi_category == 'normal') $normal++;
            elseif ($s->bmi_category == 'overweight') $overweight++;
            elseif ($s->bmi_category == 'obese') $obese++;
            $totalBmi += $s->bmi;
        }
        $avgBmi = $total > 0 ? round($totalBmi / $total, 2) : 0;
        ?>
        
        <div class="summary-stats">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-value"><?= $total ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-value" style="color: #3498db;"><?= $underweight ?></div>
                        <div class="stat-label">Underweight (<?= $total ? round($underweight/$total*100) : 0 ?>%)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-value" style="color: #2ecc71;"><?= $normal ?></div>
                        <div class="stat-label">Normal (<?= $total ? round($normal/$total*100) : 0 ?>%)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-value" style="color: #f39c12;"><?= $overweight ?></div>
                        <div class="stat-label">Overweight (<?= $total ? round($overweight/$total*100) : 0 ?>%)</div>
                    </div>
                </div>
                <div class="col-md-3 mt-3">
                    <div class="stat-box">
                        <div class="stat-value" style="color: #e74c3c;"><?= $obese ?></div>
                        <div class="stat-label">Obese (<?= $total ? round($obese/$total*100) : 0 ?>%)</div>
                    </div>
                </div>
                <div class="col-md-3 mt-3">
                    <div class="stat-box">
                        <div class="stat-value"><?= $avgBmi ?></div>
                        <div class="stat-label">Average BMI</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="progress mt-4" style="height: 30px;">
            <div class="progress-bar bg-info" style="width: <?= $total ? round($underweight/$total*100) : 0 ?>%">
                Underweight (<?= round($underweight/$total*100) ?>%)
            </div>
            <div class="progress-bar bg-success" style="width: <?= $total ? round($normal/$total*100) : 0 ?>%">
                Normal (<?= round($normal/$total*100) ?>%)
            </div>
            <div class="progress-bar bg-warning" style="width: <?= $total ? round($overweight/$total*100) : 0 ?>%">
                Overweight (<?= round($overweight/$total*100) ?>%)
            </div>
            <div class="progress-bar bg-danger" style="width: <?= $total ? round($obese/$total*100) : 0 ?>%">
                Obese (<?= round($obese/$total*100) ?>%)
            </div>
        </div>
        
    <?php else: ?>
        <!-- Detailed Report -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Reg No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Height (cm)</th>
                    <th>Weight (kg)</th>
                    <th>BMI</th>
                    <th>Category</th>
                    <th>Last Updated</th>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <?php
                        $categoryClass = '';
                        if ($student->bmi_category == 'underweight') $categoryClass = 'bmi-underweight';
                        elseif ($student->bmi_category == 'normal') $categoryClass = 'bmi-normal';
                        elseif ($student->bmi_category == 'overweight') $categoryClass = 'bmi-overweight';
                        elseif ($student->bmi_category == 'obese') $categoryClass = 'bmi-obese';
                        ?>
                        <tr>
                            <td><?= $student->reg_no ?? '-' ?></td>
                            <td><?= esc($student->first_name . ' ' . $student->last_name) ?></td>
                            <td><?= ($student->class_name ?? '') . ' ' . ($student->section_name ?? '') ?></td>
                            <td><?= ucfirst($student->gender ?? '-') ?></td>
                            <td><?= $student->height ?? '-' ?></td>
                            <td><?= $student->weight ?? '-' ?></td>
                            <td><strong><?= $student->bmi ?? '-' ?></strong></td>
                            <td><span class="bmi-badge <?= $categoryClass ?>"><?= ucfirst($student->bmi_category ?? 'N/A') ?></span></td>
                            <td><?= $student->bmi_updated_date ? date('d-M-Y', strtotime($student->bmi_updated_date)) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div class="report-footer mt-4 text-center text-muted">
            <small>This is a computer-generated report. For any discrepancies, please contact the school administration.</small>
        </div>
    </div>
    
    <div class="no-print text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-2"></i> Print Report
        </button>
    </div>