<?php

$days = $days ?? [];
$slots = $slots ?? [];
$matrix = $matrix ?? [];
$title = $title ?? 'Timetable Report';
$mode = $mode ?? 'class';
$clsSecId = (int)($cls_sec_id ?? 0);
$is_export = (bool)($is_export ?? false);
$sheet_position = (string)($sheet_position ?? 'first');
$show_slot_time = (bool)($show_slot_time ?? false);
$show_teacher_with_subject = (bool)($show_teacher_with_subject ?? true);
$show_adjust_btn = ($mode === 'class' && $clsSecId > 0 && !$is_export);

$sheetClass = 'tt-print-sheet tt-print-sheet--' . ($sheet_position === 'next' ? 'next' : 'first');
?>

<style>
.tt-print-sheet { max-width: 100%; }
</style>

<div class="tt-report-block" data-cls-sec-id="<?= $clsSecId ?>" data-section-title="<?= esc($title) ?>">
<div class="<?= esc($sheetClass) ?>">
<div class="card card-secondary tt-report-table-card">
    <div class="card-header d-flex justify-content-between align-items-center py-2 flex-wrap">
        <h3 class="card-title mb-0 h5"><?= esc($title) ?></h3>
        <?php if (!$is_export): ?>
        <div class="d-flex align-items-center no-print">
            <?php if ($show_adjust_btn): ?>
            <button type="button"
                class="btn btn-sm btn-warning me-1 btn-section-adjust"
                data-cls-sec-id="<?= $clsSecId ?>"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Open manual adjust for this section — drag subjects onto the grid">
                <i class="fas fa-sliders-h"></i> Adjust
            </button>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()" title="Print A4 portrait — one timetable per page">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body p-2 p-md-3">
        <div class="table-responsive tt-print-table-wrap">
            <table class="table table-bordered table-sm tt-print-table">
                <thead class="table-light">
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
                            <td class="fw-bold bg-light tt-slot-col"><?= esc($rowLabel) ?></td>
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
                                                        <div class="fw-bold text-primary">
                                                            <?= esc($sub) ?>
                                                            <?php if ($tn !== ''): ?>
                                                                <span class="text-muted fw-normal"> — <?= esc($tn) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted fw-normal small"> — <?= esc('Teacher not assigned') ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="fw-bold text-primary"><?= esc($sub) ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php
                                                    $cl = trim((string)($e['class_label'] ?? ''));
                                                    $subj = (string)($e['subject_name'] ?? '');
                                                    ?>
                                                    <?php if ($show_teacher_with_subject): ?>
                                                        <div class="fw-bold text-primary">
                                                            <?= esc($subj) ?>
                                                            <?php if ($cl !== ''): ?>
                                                                <span class="text-muted fw-normal"> — <?= esc($cl) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="fw-bold text-primary"><?= esc($subj) ?></div>
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
    </div>
</div>
</div>
</div>
