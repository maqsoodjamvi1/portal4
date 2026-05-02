<?php
$days = $days ?? [];
$slots = $slots ?? [];
$matrix = $matrix ?? [];
$title = $title ?? 'Timetable Report';
$mode = $mode ?? 'class';
$report_header = $report_header ?? [];
$is_export = (bool)($is_export ?? false);
$show_outer_header = (bool)($show_outer_header ?? true);
$timing_banner = $timing_banner ?? '';
$show_slot_time = (bool)($show_slot_time ?? false);
$show_teacher_with_subject = (bool)($show_teacher_with_subject ?? true);
?>

<style>
/* Minimal screen-only polish; print rules live in assets/css/timetable-report-print.css */
.tt-print-sheet { max-width: 100%; }
</style>

<div class="tt-print-sheet">
<?php if ($show_outer_header): ?>
<div class="tt-report-header">
    <div class="left">
        <h4><?= esc($report_header['school_name'] ?? 'School') ?></h4>
        <div class="sub">
            <?= esc($report_header['campus_name'] ?? '') ?>
            <?php if (!empty($report_header['campus_name'])): ?> | <?php endif; ?>
            <?= esc($title) ?>
        </div>
    </div>
    <div class="right">
        <div><strong>Generated:</strong> <?= esc($report_header['generated_at'] ?? '') ?></div>
        <div><strong>Mode:</strong> <?= esc(ucfirst($mode)) ?>-wise</div>
    </div>
</div>
<?php endif; ?>

<?php if ($show_outer_header && $timing_banner !== ''): ?>
    <?= $timing_banner ?>
<?php endif; ?>

<div class="card card-secondary tt-report-table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><?= esc($title) ?></h3>
        <button type="button" class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()" title="Print A4 landscape">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
    <div class="card-body p-2 p-md-3">
        <div class="table-responsive tt-print-table-wrap">
            <table class="table table-bordered table-sm tt-print-table">
                <thead class="thead-light">
                    <tr>
                        <th class="tt-slot-col"><?= $show_slot_time ? 'Time slot' : 'Slot' ?></th>
                        <?php foreach ($days as $day): ?>
                            <th><?= esc($day) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slots as $slotIdx => $slot): ?>
                        <?php
                        $start = !empty($slot['start_time']) ? date('h:i A', strtotime($slot['start_time'])) : '';
                        $end   = !empty($slot['end_time']) ? date('h:i A', strtotime($slot['end_time'])) : '';
                        $timeLabel = trim($start . ' - ' . $end);
                        $slotNum = (int)$slotIdx + 1;
                        $rowLabel = $show_slot_time
                            ? ($timeLabel !== '' ? $timeLabel : 'Slot ' . $slotNum)
                            : ('Slot ' . $slotNum);
                        $slotId = (int)($slot['slot_id'] ?? 0);
                        ?>
                        <tr>
                            <td class="font-weight-bold bg-light tt-slot-col"><?= esc($rowLabel) ?></td>
                            <?php foreach ($days as $day): ?>
                                <?php $entries = $matrix[$day][$slotId] ?? []; ?>
                                <td>
                                    <?php if (empty($entries)): ?>
                                        <span class="text-muted">-</span>
                                    <?php else: ?>
                                        <?php foreach ($entries as $e): ?>
                                            <div class="tt-cell-inner mb-2">
                                                <?php if ($mode === 'class'): ?>
                                                    <?php
                                                    $tn = trim((string)($e['teacher_name'] ?? ''));
                                                    $sub = (string)($e['subject_name'] ?? '');
                                                    ?>
                                                    <?php if ($show_teacher_with_subject): ?>
                                                        <div class="font-weight-bold text-primary">
                                                            <?= esc($sub) ?>
                                                            <?php if ($tn !== ''): ?>
                                                                <span class="text-muted font-weight-normal"> — <?= esc($tn) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted font-weight-normal small"> — <?= esc('Teacher not assigned') ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="font-weight-bold text-primary"><?= esc($sub) ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php
                                                    $cl = trim((string)($e['class_label'] ?? ''));
                                                    $subj = (string)($e['subject_name'] ?? '');
                                                    ?>
                                                    <?php if ($show_teacher_with_subject): ?>
                                                        <div class="font-weight-bold text-primary">
                                                            <?= esc($subj) ?>
                                                            <?php if ($cl !== ''): ?>
                                                                <span class="text-muted font-weight-normal"> — <?= esc($cl) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="font-weight-bold text-primary"><?= esc($subj) ?></div>
                                                        <?php if ($cl !== ''): ?>
                                                            <div class="small text-muted"><?= esc($cl) ?></div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="small text-muted mb-0 mt-2 no-print"><i class="fas fa-info-circle"></i> Use your browser’s Print dialog — paper size <strong>A4</strong>, orientation <strong>Landscape</strong> for best fit.</p>
    </div>
</div>
</div>
