<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$clsName = $classInfo->cls_sec_name ?? '—';
$gradeFunction = $gradeFunction ?? null;
$gradeRanges = $gradeRanges ?? [];
?>

<style>
/* ===== RESET MARGINS FOR FULL WIDTH ===== */
body {
    margin: 0 !important;
    padding: 0 !important;
}

.content {
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
}

/* ===== PRINT OPTIMIZED STYLES ===== */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        box-sizing: border-box;
    }
    
    body {
        background: white !important;
        color: black !important;
        font-size: 10pt;
        font-family: "Arial", "Helvetica", sans-serif;
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1.3;
        width: 100% !important;
    }
    
    .no-print {
        display: none !important;
    }
    
    .report-container {
        width: 100% !important;
        margin: 0 !important;
        padding: 5mm !important;
        box-shadow: none !important;
        border: none !important;
    }
    
    .report-section {
        page-break-inside: avoid;
        break-inside: avoid;
        margin-bottom: 15px;
    }
    
    .section-title {
        page-break-after: avoid;
        break-after: avoid;
    }
    
    table {
        page-break-inside: auto;
        break-inside: auto;
        font-size: 7pt !important;
    }
    
    tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    
    .page-break {
        page-break-before: always;
        break-before: page;
    }
    
    .avoid-break {
        page-break-inside: avoid;
        break-inside: avoid;
    }
}

/* ===== REPORT CONTAINER ===== */
.report-container {
    background: white;
    width: 100% !important;
    margin: 0 !important;
    padding: 15px !important;
    font-family: "Arial", sans-serif;
    color: #333;
}

/* ===== REPORT HEADER ===== */
.report-header {
    text-align: center;
    padding-bottom: 15px;
    margin-bottom: 20px;
    border-bottom: 3px solid #2c3e50;
}

.report-title {
    font-size: 18pt;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.report-subtitle {
    font-size: 10pt;
    color: #7f8c8d;
    margin: 0 0 15px 0;
    font-weight: 500;
}

.report-meta {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
    font-size: 9pt;
}

.meta-item {
    padding: 5px 10px;
    background: #ecf0f1;
    border-radius: 3px;
    border: 1px solid #bdc3c7;
    font-weight: 600;
    color: #2c3e50;
}

/* ===== SUMMARY CARDS ===== */
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin: 20px 0;
    page-break-inside: avoid;
}

@media print {
    .summary-cards {
        grid-template-columns: repeat(4, 1fr);
        gap: 5px;
        margin: 15px 0;
    }
}

.summary-card {
    background: white;
    border: 1px solid #dfe6e9;
    border-radius: 4px;
    padding: 12px 8px;
    text-align: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.summary-card .value {
    font-size: 16pt;
    font-weight: 800;
    color: #2980b9;
    margin: 0 0 4px 0;
    line-height: 1;
}

.summary-card .label {
    font-size: 8pt;
    color: #2c3e50;
    font-weight: 600;
    margin: 0;
}

.summary-card .subtext {
    font-size: 7pt;
    color: #7f8c8d;
    margin: 2px 0 0 0;
}

/* ===== TOPPERS SECTION ===== */
.toppers-section {
    margin: 25px 0;
    page-break-inside: avoid;
}

.section-title {
    font-size: 12pt;
    font-weight: 700;
    color: #2c3e50;
    padding-bottom: 6px;
    margin: 0 0 12px 0;
    border-bottom: 2px solid #3498db;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.toppers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

@media print {
    .toppers-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
}

.topper-card {
    background: white;
    border: 1px solid #dfe6e9;
    border-radius: 4px;
    padding: 12px;
    text-align: center;
    position: relative;
    page-break-inside: avoid;
}

.topper-card.position-1 {
    border: 2px solid #f39c12;
    background: linear-gradient(to bottom, #fef9e7 0%, #ffffff 50%);
}

.topper-card.position-2 {
    border: 2px solid #7f8c8d;
    background: linear-gradient(to bottom, #f8f9f9 0%, #ffffff 50%);
}

.topper-card.position-3 {
    border: 2px solid #e67e22;
    background: linear-gradient(to bottom, #fef5e7 0%, #ffffff 50%);
}

.position-badge {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    background: #2c3e50;
    color: white;
    font-weight: bold;
    padding: 3px 12px;
    border-radius: 12px;
    font-size: 8pt;
    white-space: nowrap;
}

.topper-photo {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #3498db;
    margin: 8px auto;
    background: #ecf0f1;
}

.topper-name {
    font-size: 10pt;
    font-weight: 700;
    color: #2c3e50;
    margin: 5px 0 2px 0;
    line-height: 1.2;
    min-height: 25px;
}

.topper-percentage {
    font-size: 14pt;
    font-weight: 800;
    color: #27ae60;
    margin: 8px 0;
    line-height: 1;
}

.topper-stats {
    display: flex;
    justify-content: space-around;
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px solid #dfe6e9;
}

.topper-stat {
    text-align: center;
}

.topper-stat-value {
    font-weight: 700;
    font-size: 10pt;
    color: #2c3e50;
    line-height: 1;
}

.topper-stat-label {
    font-size: 7pt;
    color: #7f8c8d;
    margin-top: 1px;
}

/* ===== SUBJECT TOPPERS ===== */
.subject-toppers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin: 15px 0 20px 0;
    page-break-inside: avoid;
}

.subject-topper-box {
    background: white;
    border: 1px solid #dfe6e9;
    border-radius: 4px;
    padding: 12px;
    page-break-inside: avoid;
}

.subject-title {
    font-size: 10pt;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
    padding-bottom: 5px;
    border-bottom: 1px solid #dfe6e9;
    text-align: center;
}

.subject-toppers-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.subject-topper-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px;
    background: #f8f9fa;
    border-radius: 3px;
    border: 1px solid #e9ecef;
}

.subject-topper-photo {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #bdc3c7;
}

.subject-topper-info {
    flex: 1;
}

.subject-topper-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 9pt;
    margin: 0 0 1px 0;
    line-height: 1.2;
}

.subject-topper-percentage {
    font-weight: 700;
    font-size: 10pt;
    color: #27ae60;
    min-width: 45px;
    text-align: right;
}

/* ===== DETAILED REPORT ===== */
.detailed-report {
    margin-top: 20px;
    overflow-x: auto;
}

.subject-section {
    margin-bottom: 20px;
    page-break-inside: avoid;
}

.subject-header {
    background: #ecf0f1;
    border: 1px solid #bdc3c7;
    border-radius: 3px;
    padding: 8px;
    margin-bottom: 12px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 8px;
    text-align: center;
    page-break-after: avoid;
}

.subject-stat {
    padding: 6px;
}

.subject-stat-label {
    font-size: 8pt;
    color: #2c3e50;
    margin: 0 0 2px 0;
    font-weight: 600;
}

.subject-stat-value {
    font-size: 11pt;
    font-weight: 800;
    color: #2980b9;
    margin: 0;
    line-height: 1;
}

/* ===== STUDENT TABLE - UPDATED FOR MULTI-LINE QUIZ TITLES ===== */
.student-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 8pt;
    min-width: 1200px; /* Minimum width for many columns */
}

.student-table th {
    background: #f8f9fa;
    padding: 6px 3px !important;
    text-align: center !important;
    font-weight: 600;
    color: #2c3e50;
    border: 1px solid #dee2e6;
    white-space: normal !important;
    word-break: break-word;
    font-size: 7.5pt;
    vertical-align: top;
}

.student-table td {
    padding: 4px 3px !important;
    border: 1px solid #dee2e6;
    vertical-align: middle;
    text-align: center !important;
    font-size: 8pt;
}

.student-table tr:nth-child(even) {
    background: #f8f9fa;
}

.student-photo-small {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #bdc3c7;
    margin-right: 5px;
}

.percentage-badge {
    padding: 1px 4px;
    border-radius: 2px;
    font-weight: 600;
    font-size: 7pt;
    display: inline-block;
    min-width: 30px;
    text-align: center;
}

.percentage-high {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.percentage-medium {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.percentage-low {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* ===== QUIZ COLUMN STYLES ===== */
.quiz-column {
    min-width: 80px;
    max-width: 120px;
    word-break: break-word;
}

.quiz-title-cell {
    padding: 4px 2px !important;
    line-height: 1.1;
}

.quiz-title-small {
    font-weight: 600;
    font-size: 7.5pt;
    display: block;
    margin-bottom: 2px;
    line-height: 1.1;
}

.quiz-subject-small {
    font-size: 6.5pt;
    color: #7f8c8d;
    display: block;
    margin-bottom: 2px;
}

.quiz-marks-small {
    font-size: 6.5pt;
    color: #2980b9;
    font-weight: 600;
    display: block;
}

/* ===== REPORT FOOTER ===== */
.report-footer {
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px solid #bdc3c7;
    text-align: center;
    font-size: 7pt;
    color: #7f8c8d;
    page-break-before: avoid;
}

/* ===== ACTION BUTTONS ===== */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

/* ===== UTILITY CLASSES ===== */
.text-center { text-align: center !important; }
.text-right { text-align: right !important; }
.text-left { text-align: left !important; }
.mb-0 { margin-bottom: 0 !important; }
.mt-0 { margin-top: 0 !important; }
.mb-1 { margin-bottom: 4px !important; }
.mb-2 { margin-bottom: 8px !important; }
.mt-1 { margin-top: 4px !important; }
.mt-2 { margin-top: 8px !important; }
.nowrap { white-space: nowrap; }

.attempt-info {
    font-size: 6.5pt;
    color: #7f8c8d;
    margin-top: 1px;
    display: block;
}

/* View Report Button */
.view-report-btn {
    padding: 2px 6px;
    font-size: 8pt;
    border-radius: 3px;
    background: #3498db;
    color: white;
    border: none;
    cursor: pointer;
    transition: background 0.3s;
    white-space: nowrap;
}

.view-report-btn:hover {
    background: #2980b9;
}

/* ===== MODAL STYLES ===== */
.student-report-modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-dialog {
    position: relative;
    margin: 30px auto;
    width: 95%;
    max-width: 1200px;
    max-height: 90vh;
    overflow: hidden;
}

.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.modal-header {
    padding: 15px 20px;
    background: #2c3e50;
    color: white;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    font-size: 14pt;
    font-weight: 600;
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.close-modal {
    background: none;
    border: none;
    color: white;
    font-size: 24pt;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
}

/* Student Report Card */
.student-report-card {
    background: white;
    border-radius: 6px;
    overflow: hidden;
}

.student-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    text-align: center;
}

.student-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid white;
    margin: 0 auto 15px;
    overflow: hidden;
}

.student-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-name {
    font-size: 16pt;
    font-weight: 700;
    margin: 0 0 5px 0;
}

.student-info {
    display: flex;
    justify-content: center;
    gap: 15px;
    font-size: 9pt;
    opacity: 0.9;
}

.performance-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin: 20px 0;
}

.stat-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    text-align: center;
}

.stat-value {
    font-size: 16pt;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
}

.stat-label {
    font-size: 8.5pt;
    color: #6c757d;
    margin: 0;
}

/* ===== RESPONSIVE ADJUSTMENTS ===== */
@media (max-width: 1200px) {
    .report-container {
        padding: 10px !important;
    }
    
    .student-table {
        font-size: 7.5pt;
    }
    
    .quiz-column {
        min-width: 70px;
    }
}

@media (max-width: 768px) {
    .report-title {
        font-size: 14pt;
    }
    
    .summary-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .toppers-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="content">
    <!-- Action Buttons -->
    <div class="action-buttons no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="fas fa-print mr-1"></i> Print Report
        </button>
        <a href="<?= base_url('admin/quizzes') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <button onclick="toggleAllQuizzes()" class="btn btn-info btn-sm" id="toggleQuizzesBtn">
            <i class="fas fa-compress mr-1"></i> Hide Quiz Details
        </button>
    </div>

    <!-- Main Report Container -->
    <div class="report-container">
        <!-- Report Header -->
        <div class="report-header">
            <h1 class="report-title">CLASS PERFORMANCE REPORT</h1>
            <div class="report-subtitle">Academic Performance Analysis</div>
            <div class="report-meta">
                <div class="meta-item"><?= esc($clsName) ?></div>
                <div class="meta-item">Generated: <?= date('d M Y, h:i A') ?></div>
                <div class="meta-item">Students: <?= $totalStudents ?></div>
                <?php if (!empty($currentTerm)): ?>
                <div class="meta-item">Term: <?= esc($currentTerm) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="value"><?= $totalStudents ?></div>
                <div class="label">Total Students</div>
                <div class="subtext">Enrolled</div>
            </div>
            <div class="summary-card">
                <div class="value"><?= $totalQuizzes ?></div>
                <div class="label">Total Quizzes</div>
                <div class="subtext">Conducted</div>
            </div>
            <div class="summary-card">
                <div class="value"><?= $totalAttempts ?></div>
                <div class="label">Quiz Attempts</div>
                <div class="subtext">Submissions</div>
            </div>
            <div class="summary-card">
                <div class="value"><?= (int)($avgParticipation ?? 0) ?>%</div>
                <div class="label">Avg Participation</div>
                <div class="subtext">Per student</div>
            </div>
        </div>

        <!-- Overall Top 3 Performers -->
        <?php if (!empty($overallToppers)): ?>
        <div class="toppers-section report-section">
            <h2 class="section-title">OVERALL TOP PERFORMERS</h2>
            <div class="toppers-grid">
                <?php foreach ($overallToppers as $index => $topper): ?>
                    <?php
                    $position = $index + 1;
                    $photo = !empty($topper['profile_photo']) 
                        ? base_url('uploads/' . ltrim($topper['profile_photo'], '/'))
                        : base_url('resource/img/avatar-student.png');
                    ?>
                    <div class="topper-card position-<?= $position ?>">
                        <div class="position-badge">
                            <?php if ($position == 1): ?>🥇
                            <?php elseif ($position == 2): ?>🥈
                            <?php elseif ($position == 3): ?>🥉
                            <?php endif; ?>
                            Rank #<?= $position ?>
                        </div>
                        <img src="<?= esc($photo) ?>" alt="<?= esc($topper['student_name']) ?>" class="topper-photo">
                        <div class="topper-name"><?= esc($topper['student_name']) ?></div>
                        <div class="topper-percentage"><?= (int)($topper['percentage'] ?? 0) ?>%</div>
                        <div class="topper-stats">
                            <div class="topper-stat">
                                <div class="topper-stat-value"><?= $topper['attempted_quizzes'] ?? 0 ?></div>
                                <div class="topper-stat-label">Attempted</div>
                            </div>
                            <div class="topper-stat">
                                <div class="topper-stat-value"><?= $topper['total_quizzes'] ?? 0 ?></div>
                                <div class="topper-stat-label">Total</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Detailed Single Table Report -->
        <?php if (!empty($quizzes)): ?>
        <div class="detailed-report">
            <h2 class="section-title page-break">DETAILED PERFORMANCE REPORT - <?= esc($currentTerm ?? 'All Terms') ?></h2>
            
            <div class="subject-section report-section">
                <!-- Class Summary -->
                <div class="subject-header">
                    <div class="subject-stat">
                        <div class="subject-stat-label">Total Students</div>
                        <div class="subject-stat-value"><?= $totalStudents ?></div>
                    </div>
                    <div class="subject-stat">
                        <div class="subject-stat-label">Total Quizzes</div>
                        <div class="subject-stat-value"><?= $totalQuizzes ?></div>
                    </div>
                    <div class="subject-stat">
                        <div class="subject-stat-label">Average Participation</div>
                        <div class="subject-stat-value"><?= (int)($avgParticipation ?? 0) ?>%</div>
                    </div>
                    <div class="subject-stat">
                        <div class="subject-stat-label">Total Attempts</div>
                        <div class="subject-stat-value"><?= $totalAttempts ?></div>
                    </div>
                </div>
                
                <!-- Student Performance Table -->
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th width="30" class="text-center">#</th>
                                <th width="150">Student</th>
                                <?php foreach ($quizzes as $index => $quiz): ?>
                                    <th width="90" class="text-center quiz-column quiz-title-cell" data-quiz-id="<?= $quiz->quiz_id ?>">
                                        <div class="quiz-title-small" title="<?= esc($quiz->subject_name . ': ' . $quiz->title) ?>">
                                            <?= esc(strlen($quiz->title) > 15 ? substr($quiz->title, 0, 15).'...' : $quiz->title) ?>
                                        </div>
                                        <div class="quiz-subject-small">
                                            <?= esc(strlen($quiz->subject_name) > 12 ? substr($quiz->subject_name, 0, 12).'...' : $quiz->subject_name) ?>
                                        </div>
                                        <div class="quiz-marks-small">
                                            Max: <?= (int)($quiz->questions_count ?? 0) ?>
                                        </div>
                                        <?php if (isset($quiz->max_attempts) && $quiz->max_attempts > 1): ?>
                                        <div class="quiz-marks-small" style="color: #e74c3c;">
                                            x<?= (int)$quiz->max_attempts ?>
                                        </div>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                                <th width="80" class="text-center">Total Score</th>
                                <th width="65" class="text-center">Overall %</th>
                                <th width="80" class="text-center">Total Attempts</th>
                                <th width="80" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $studentCounter = 0;
                            foreach ($students as $student): 
                                $studentCounter++;
                                $studentId = $student->student_id;
                                $performance = $studentPerformance[$studentId] ?? [];
                                
                                // Calculate overall percentage
                                $overallPercentage = 0;
                                if (!empty($performance) && isset($performance['total_possible']) && $performance['total_possible'] > 0) {
                                    $overallPercentage = round(($performance['total_score'] ?? 0) / $performance['total_possible'] * 100);
                                }
                                
                                $percentageClass = '';
                                if ($overallPercentage >= 80) $percentageClass = 'percentage-high';
                                elseif ($overallPercentage >= 50) $percentageClass = 'percentage-medium';
                                elseif ($overallPercentage > 0) $percentageClass = 'percentage-low';
                                
                                // Calculate total attempts
                                $totalAttemptsForStudent = 0;
                                if (!empty($performance['quiz_scores'])) {
                                    foreach ($performance['quiz_scores'] as $quizScore) {
                                        $totalAttemptsForStudent += $quizScore['attempt_count'] ?? 0;
                                    }
                                }
                                
                                // Calculate total possible score (sum of all quiz max marks * max attempts)
                                $totalPossibleScore = 0;
                                foreach ($quizzes as $quiz) {
                                    $quizScore = $performance['quiz_scores'][$quiz->quiz_id] ?? null;
                                    $attemptCount = $quizScore['attempt_count'] ?? 0;
                                    $maxAttempts = $quiz->max_attempts ?? 1;
                                    $actualAttempts = min($attemptCount, $maxAttempts);
                                    $totalPossibleScore += ($quiz->questions_count ?? 0) * $actualAttempts;
                                }
                            ?>
                                <tr>
                                    <td class="text-center"><?= $studentCounter ?></td>
                                    <td class="text-left">
                                        <div style="display: flex; align-items: center;">
                                            <?php $photo = !empty($student->profile_photo) 
                                                ? base_url('uploads/' . ltrim($student->profile_photo, '/'))
                                                : base_url('resource/img/avatar-student.png'); ?>
                                            <img src="<?= $photo ?>" alt="<?= esc($student->student_name) ?>" class="student-photo-small">
                                            <div>
                                                <div style="font-weight: 600; font-size: 8.5pt;"><?= esc($student->student_name) ?></div>
                                                <div style="font-size: 7pt; color: #7f8c8d;"><?= $student->reg_no ?? 'N/A' ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <?php 
                                    $totalScore = 0;
                                    $totalPossible = 0;
                                    
                                    foreach ($quizzes as $quiz): 
                                        $quizScore = $performance['quiz_scores'][$quiz->quiz_id] ?? null;
                                        $quizMaxMarks = $quiz->questions_count ?? 0;
                                        $quizMaxAttempts = $quiz->max_attempts ?? 1;
                                        
                                        if ($quizScore): 
                                            $quizPercentage = $quizScore['percentage'] ?? 0;
                                            $quizPercentageClass = '';
                                            if ($quizPercentage >= 80) $quizPercentageClass = 'percentage-high';
                                            elseif ($quizPercentage >= 50) $quizPercentageClass = 'percentage-medium';
                                            elseif ($quizPercentage > 0) $quizPercentageClass = 'percentage-low';
                                            
                                            $attemptCount = $quizScore['attempt_count'] ?? 0;
                                            $maxAttemptsAllowed = $quizScore['max_attempts'] ?? 1;
                                            
                                            // Calculate scores
                                            $score = $quizScore['score'] ?? 0; // This should be total score from all attempts
                                            $displayScore = $score; // Sum of all attempts
                                            $possibleScore = $quizMaxMarks * $attemptCount; // Max possible for attempts made
                                            
                                            $totalScore += $score;
                                            $totalPossible += $possibleScore;
                                    ?>
                                            <td class="text-center">
                                                <div style="font-size: 7.5pt; line-height: 1.2;">
                                                    <strong><?= (int)$displayScore ?></strong><br>
                                                    <span class="percentage-badge <?= $quizPercentageClass ?>" style="font-size: 6.5pt;">
                                                        <?= (int)$quizPercentage ?>%
                                                    </span>
                                                    <div class="attempt-info">
                                                        <?= $attemptCount ?>/<?= $maxAttemptsAllowed ?>
                                                    </div>
                                                </div>
                                            </td>
                                        <?php else: ?>
                                            <td class="text-center">
                                                <span class="text-muted" style="font-size: 7.5pt;">—</span>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    
                                    <td class="text-center">
                                        <?php if ($totalScore > 0): ?>
                                            <div style="font-size: 8pt; line-height: 1.2;">
                                                <strong><?= (int)$totalScore ?></strong><br>
                                                <span style="font-size: 7pt; color: #7f8c8d;">
                                                    /<?= (int)$totalPossible ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 8pt;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if ($overallPercentage > 0): ?>
                                            <span class="percentage-badge <?= $percentageClass ?>">
                                                <?= (int)$overallPercentage ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 8pt;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if ($totalAttemptsForStudent > 0): ?>
                                            <div style="font-size: 9pt; font-weight: 600; color: #27ae60;">
                                                <?= $totalAttemptsForStudent ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="font-size: 8pt; color: #95a5a6;">0</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <button class="view-report-btn" onclick="viewStudentReport(<?= $studentId ?>)">
                                            View Report
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Report Footer -->
        <div class="report-footer">
            <p class="mb-1">Report generated on <?= date('F j, Y') ?> | System: Quiz Management System</p>
            <p class="mb-0">This report is computer generated and requires no signature.</p>
        </div>
    </div>
</section>

<!-- Student Report Modal -->
<div id="studentReportModal" class="student-report-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Student Detailed Report Card</h3>
                <button class="close-modal" onclick="closeStudentReport()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="studentReportContent">
                    <!-- Report content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Student performance data passed from controller
const studentPerformanceData = <?= json_encode($studentPerformance) ?>;
const quizzesData = <?= json_encode($quizzes) ?>;
const studentsData = <?= json_encode($students) ?>;

// Toggle quiz details visibility
let showQuizDetails = true;

function toggleAllQuizzes() {
    showQuizDetails = !showQuizDetails;
    const quizColumns = document.querySelectorAll('.quiz-column');
    const toggleBtn = document.getElementById('toggleQuizzesBtn');
    
    if (showQuizDetails) {
        quizColumns.forEach(col => {
            col.style.display = 'table-cell';
        });
        toggleBtn.innerHTML = '<i class="fas fa-compress mr-1"></i> Hide Quiz Details';
    } else {
        quizColumns.forEach(col => {
            col.style.display = 'none';
        });
        toggleBtn.innerHTML = '<i class="fas fa-expand mr-1"></i> Show Quiz Details';
    }
}

// Print optimization
document.addEventListener('DOMContentLoaded', function() {
    // Add print button event listener
    const printBtn = document.querySelector('button[onclick="window.print()"]');
    if (printBtn) {
        printBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Show all columns before printing
            const quizColumns = document.querySelectorAll('.quiz-column');
            quizColumns.forEach(col => {
                col.style.display = 'table-cell';
            });
            window.print();
        });
    }
});

function viewStudentReport(studentId) {
    const performance = studentPerformanceData[studentId];
    if (!performance) {
        alert('Student data not found!');
        return;
    }
    
    // Find student info
    const student = studentsData.find(s => s.student_id == studentId);
    if (!student) {
        alert('Student not found!');
        return;
    }
    
    // Get student photo
    const photo = student.profile_photo ? 
        '<?= base_url("uploads/") ?>' + student.profile_photo.replace(/^\//, '') :
        '<?= base_url("resource/img/avatar-student.png") ?>';
    
    // Calculate overall statistics
    const totalPossibleScore = performance.total_possible || 0;
    const totalScore = performance.total_score || 0;
    const overallPercentage = totalPossibleScore > 0 ? Math.round((totalScore / totalPossibleScore) * 100) : 0;
    
    // Calculate total attempts
    let totalAttemptsCount = 0;
    if (performance.quiz_scores) {
        Object.values(performance.quiz_scores).forEach(quizScore => {
            totalAttemptsCount += quizScore.attempt_count || 0;
        });
    }
    
    // Build the report HTML
    let reportHTML = `
        <div class="student-report-card">
            <div class="student-header">
                <div class="student-avatar">
                    <img src="${photo}" alt="${student.student_name}">
                </div>
                <h2 class="student-name">${student.student_name}</h2>
                <div class="student-info">
                    <span>Roll No: ${student.reg_no || 'N/A'}</span>
                    <span>Class: <?= esc($classInfo->cls_sec_name) ?></span>
                    <span>Total Quizzes: ${performance.attempted_quiz_count || 0}</span>
                </div>
            </div>
            
            <div class="performance-stats">
                <div class="stat-card">
                    <div class="stat-value">${performance.attempted_quiz_count || 0}</div>
                    <div class="stat-label">Quizzes Attempted</div>
                    <div class="stat-subtext">of <?= $totalQuizzes ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${totalAttemptsCount}</div>
                    <div class="stat-label">Total Attempts</div>
                    <div class="stat-subtext">All quizzes combined</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${overallPercentage}%</div>
                    <div class="stat-label">Overall Score</div>
                    <div class="stat-subtext">Based on all attempts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${totalScore}</div>
                    <div class="stat-label">Total Obtained</div>
                    <div class="stat-subtext">/${totalPossibleScore}</div>
                </div>
            </div>
            
            <h3 style="margin: 25px 0 15px 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">
                Detailed Quiz Performance
            </h3>
    `;
    
    // Check if student has attempted any quizzes
    if (performance.attempted_quiz_count > 0 && performance.quiz_scores && Object.keys(performance.quiz_scores).length > 0) {
        // Group by subject
        const subjects = {};
        
        Object.entries(performance.quiz_scores).forEach(([quizId, quizData]) => {
            const quiz = quizzesData.find(q => q.quiz_id == quizId);
            const subjectName = quiz ? quiz.subject_name : 'Unknown Subject';
            
            if (!subjects[subjectName]) {
                subjects[subjectName] = [];
            }
            
            subjects[subjectName].push({
                quizId,
                quizData,
                quizInfo: quiz
            });
        });
        
        // Display by subject
        Object.entries(subjects).forEach(([subjectName, quizzes], subjectIndex) => {
            // Calculate subject totals
            let subjectTotalScore = 0;
            let subjectTotalPossible = 0;
            let subjectTotalAttempts = 0;
            
            quizzes.forEach(item => {
                const quizData = item.quizData;
                const quizMaxMarks = item.quizInfo?.questions_count || 0;
                const attemptCount = quizData.attempt_count || 0;
                
                subjectTotalScore += quizData.score || 0;
                subjectTotalPossible += quizMaxMarks * attemptCount;
                subjectTotalAttempts += attemptCount;
            });
            
            const subjectPercentage = subjectTotalPossible > 0 ? 
                Math.round((subjectTotalScore / subjectTotalPossible) * 100) : 0;
            
            reportHTML += `
                <div class="collapsible-section" onclick="toggleCollapsible(this)">
                    <div class="collapsible-header">
                        <h4>
                            ${subjectName}
                            <span style="color: #27ae60; font-size: 10pt; margin-left: 10px;">
                                ${subjectPercentage}% (${quizzes.length} quizzes, ${subjectTotalAttempts} attempts)
                            </span>
                        </h4>
                        <span class="collapsible-icon">▼</span>
                    </div>
                    <div class="collapsible-content">
                        <div class="quiz-summary" style="margin-bottom: 15px;">
                            <div class="quiz-summary-header">
                                <div>
                                    <h5 class="quiz-title">${subjectName} - Subject Summary</h5>
                                    <span class="quiz-subject">${subjectName}</span>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 11pt; font-weight: 700; color: #27ae60;">
                                        ${subjectPercentage}%
                                    </div>
                                    <div style="font-size: 9pt; color: #7f8c8d;">
                                        ${subjectTotalAttempts} total attempts
                                    </div>
                                </div>
                            </div>
                            <div class="quiz-overall-stats" style="grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));">
                                <div class="quiz-stat">
                                    <div class="quiz-stat-value">${subjectTotalScore}</div>
                                    <div class="quiz-stat-label">Total Obtained</div>
                                </div>
                                <div class="quiz-stat">
                                    <div class="quiz-stat-value">${subjectTotalPossible}</div>
                                    <div class="quiz-stat-label">Total Possible</div>
                                </div>
                                <div class="quiz-stat">
                                    <div class="quiz-stat-value">${quizzes.length}</div>
                                    <div class="quiz-stat-label">Quizzes</div>
                                </div>
                                <div class="quiz-stat">
                                    <div class="quiz-stat-value">${subjectTotalAttempts}</div>
                                    <div class="quiz-stat-label">Attempts</div>
                                </div>
                            </div>
                        </div>
            `;
            
            // Display each quiz in this subject
            quizzes.forEach((item, index) => {
                const quizData = item.quizData;
                const quizInfo = item.quizInfo;
                const quizTitle = quizInfo?.title || 'Unknown Quiz';
                const quizMaxMarks = quizInfo?.questions_count || 0;
                const attemptCount = quizData.attempt_count || 0;
                const maxAttempts = quizInfo?.max_attempts || 1;
                const quizPercentage = quizData.percentage || 0;
                const totalObtained = quizData.score || 0;
                const totalPossible = quizMaxMarks * attemptCount;
                
                reportHTML += `
                    <div class="quiz-summary" style="margin-bottom: 15px; border-left: 4px solid #3498db;">
                        <div class="quiz-summary-header">
                            <div>
                                <h5 class="quiz-title">${quizTitle}</h5>
                                <span class="quiz-subject">${subjectName}</span>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 11pt; font-weight: 700; color: #27ae60;">
                                    ${quizPercentage}%
                                </div>
                                <div style="font-size: 9pt; color: #7f8c8d;">
                                    ${attemptCount}/${maxAttempts} attempts
                                </div>
                            </div>
                        </div>
                        
                        <div class="quiz-overall-stats" style="grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));">
                            <div class="quiz-stat">
                                <div class="quiz-stat-value">${totalObtained}</div>
                                <div class="quiz-stat-label">Total Obtained</div>
                                <div class="quiz-stat-subtext">Sum of all attempts</div>
                            </div>
                            <div class="quiz-stat">
                                <div class="quiz-stat-value">${totalPossible}</div>
                                <div class="quiz-stat-label">Total Possible</div>
                                <div class="quiz-stat-subtext">${quizMaxMarks} × ${attemptCount}</div>
                            </div>
                            <div class="quiz-stat">
                                <div class="quiz-stat-value">${attemptCount > 0 ? Math.round(totalObtained / attemptCount) : 0}</div>
                                <div class="quiz-stat-label">Average Score</div>
                                <div class="quiz-stat-subtext">Per attempt</div>
                            </div>
                            <div class="quiz-stat">
                                <div class="quiz-stat-value">${quizMaxMarks}</div>
                                <div class="quiz-stat-label">Max Marks</div>
                                <div class="quiz-stat-subtext">Per attempt</div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #dee2e6;">
                            <div style="font-size: 9pt; color: #2c3e50;">
                                <i class="fas fa-info-circle mr-2"></i>
                                This quiz allows ${maxAttempts} attempt(s). Student made ${attemptCount} attempt(s).
                            </div>
                        </div>
                    </div>
                `;
            });
            
            reportHTML += `
                    </div>
                </div>
            `;
        });
    } else {
        reportHTML += `
            <div class="no-attempts" style="text-align: center; padding: 40px 20px; color: #95a5a6;">
                <i class="fas fa-clipboard-list" style="font-size: 48pt; margin-bottom: 15px;"></i>
                <h3>No Quiz Attempts Found</h3>
                <p>This student has not attempted any quizzes yet.</p>
            </div>
        `;
    }
    
    reportHTML += `</div>`;
    
    // Display the modal
    document.getElementById('studentReportContent').innerHTML = reportHTML;
    document.getElementById('studentReportModal').style.display = 'block';
}

function toggleCollapsible(element) {
    element.classList.toggle('active');
}

function closeStudentReport() {
    document.getElementById('studentReportModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('studentReportModal');
    if (event.target === modal) {
        closeStudentReport();
    }
}

// Handle escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeStudentReport();
    }
});
</script>

<?= $this->endSection() ?>