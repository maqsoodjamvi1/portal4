<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?= esc($title ?? 'Datesheet') ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('student/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Datesheet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?php if (!empty($exam_name)): ?>
                                <?= esc($exam_name) ?> Datesheet
                            <?php else: ?>
                                Exam Datesheet
                            <?php endif; ?>
                        </h3>
                        <?php if (!empty($class_info)): ?>
                            <div class="card-tools">
                                <span class="badge badge-info">
                                    <?= esc($class_info['sectionclassname'] ?? '') ?>
                                </span>
                                <?php if (!empty($student_name)): ?>
                                    <span class="badge badge-secondary ml-2">
                                        <?= esc($student_name) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dsRows)): ?>
                            <div class="datesheet-wrap">
                                <table class="datesheet-table table table-bordered table-hover compact relax">
                                    <colgroup>
                                        <col class="col-date" style="width:15ch">
                                        <col class="col-subject" style="max-width:40%">
                                        <col class="col-syll">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th class="english-text">Date & Day</th>
                                            <th class="english-text">Subject</th>
                                            <th class="english-text">Exam Syllabus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $map = [
                                                'Sun' => 'Sunday',
                                                'Mon' => 'Monday',
                                                'Tue' => 'Tuesday',
                                                'Wed' => 'Wednesday',
                                                'Thu' => 'Thursday',
                                                'Fri' => 'Friday',
                                                'Sat' => 'Saturday'
                                            ];
                                            
                                            foreach ($dsRows as $row):
                                                $dateDayRaw = $row['date_day'] ?? '';
                                                $subjectRaw = $row['subject'] ?? '';
                                                $syllabus = $row['syllabus'] ?? '';
                                                
                                                // Parse date and day
                                                $datePart = $dateDayRaw;
                                                $dayPart = '';
                                                if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/', $dateDayRaw, $m)) {
                                                    $datePart = trim($m[1]);
                                                    $dayPart = trim($m[2]);
                                                }
                                                $fullDay = $map[$dayPart] ?? $dayPart;
                                                
                                                // Parse marks from subject
                                                $marksText = '';
                                                if (preg_match('/\((\d+)\)\s*$/', $subjectRaw, $mm)) {
                                                    $marksVal = (int)$mm[1];
                                                    if ($marksVal > 0) {
                                                        $marksText = 'Total: ' . $marksVal;
                                                    }
                                                    $subject = trim(preg_replace('/\s*\(\d+\)\s*$/', '', $subjectRaw));
                                                } else {
                                                    $subject = $subjectRaw;
                                                }
                                        ?>
                                            <tr>
                                                <td class="date-day-cell english-text" style="white-space:normal;line-height:1.1">
                                                    <span class="dd-date" style="display:block;font-weight:600"><?= esc($datePart) ?></span>
                                                    <?php if ($fullDay): ?>
                                                        <span class="dd-day" style="display:block;opacity:.85"><?= esc($fullDay) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($row['start_time'])): ?>
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="far fa-clock mr-1"></i>
                                                            <?= esc($row['start_time']) ?> - <?= esc($row['end_time'] ?? '') ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="subject-cell english-text" title="<?= esc($subject) ?>" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                    <?= esc($subject) ?>
                                                    <?php if ($marksText): ?>
                                                        <div class="dd-marks text-info small">
                                                            <i class="far fa-dot-circle"></i> <?= esc($marksText) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($row['room_no'])): ?>
                                                        <div class="dd-room text-muted small">
                                                            <i class="fas fa-door-open"></i> Room: <?= esc($row['room_no']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <?php
                                                    // Decide Urdu vs English for syllabus
                                                    $syllClean = (string)$syllabus;
                                                    $isUrdu = (bool)preg_match('/\p{Arabic}/u', $syllClean);
                                                    $syllClass = $isUrdu ? 'syll syll-ur' : 'syll syll-en';
                                                    $syllHtml = nl2br(esc(strip_tags(html_entity_decode($syllClean))), false);
                                                ?>
                                                <td class="<?= $syllClass ?>"><?= $syllHtml ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> No Datesheet Available</h5>
                                <p>Your exam datesheet has not been published yet. Please check back later.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($dsRows)): ?>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        All dates and times are subject to change. Please verify with your teacher.
                                    </small>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                        <i class="fas fa-print mr-1"></i> Print Datesheet
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.datesheet-table {
    width: 100%;
    border-collapse: collapse;
}
.datesheet-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.datesheet-table td {
    vertical-align: top;
    padding: 12px 8px;
}
.syll {
    font-size: 0.9em;
    line-height: 1.4;
}
.syll-ur {
    direction: rtl;
    text-align: right;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.syll-en {
    direction: ltr;
    text-align: left;
}
.dd-marks, .dd-room {
    margin-top: 4px;
    font-size: 0.85em;
}
</style>

<?= $this->endSection() ?>