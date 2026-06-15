<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($worksheetTitle) ?></title>
    <style>
        @page { size: A4 portrait; margin: 8mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 12pt; color: #000; background: #dde1e6; }
        .no-print { position: sticky; top: 0; z-index: 100; background: #1e293b; color: #fff; padding: 10px 16px; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .no-print button { background: #3b82f6; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .sheet { width: 210mm; min-height: 277mm; margin: 10px auto; background: #fff; padding: 8mm 10mm 10mm; box-shadow: 0 2px 10px rgba(0,0,0,.1); page-break-after: always; }
        .sheet:last-child { page-break-after: auto; }
        .sheet.student-sheet { border: 2px solid #000; padding: 6mm 8mm; }
        .printable-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; margin-bottom: 12px; border-bottom: 3px solid #000; gap: 12px; }
        .header-left { width: 130px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; flex-shrink: 0; }
        .student-photo-container { width: 100px; height: 100px; border: 2px solid #000; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f0f0f0; }
        .student-photo { width: 100%; height: 100%; object-fit: cover; }
        .student-name-header { font-size: 14pt; font-weight: 800; color: #000; text-transform: uppercase; text-align: center; line-height: 1.2; }
        .header-center { flex: 1; text-align: center; padding: 0 8px; }
        .school-name { font-size: 20pt; font-weight: 900; text-transform: uppercase; color: #000; margin-bottom: 4px; letter-spacing: 0.5px; }
        .campus-name { font-size: 14pt; font-weight: 700; color: #000; margin-bottom: 2px; }
        .campus-location { font-size: 11pt; color: #333; margin-bottom: 6px; }
        .worksheet-title { font-size: 16pt; font-weight: 800; color: #000; margin: 6px 0 4px; border-bottom: 2px solid #000; padding-bottom: 4px; display: inline-block; }
        .worksheet-sub { font-size: 11pt; font-weight: 600; color: #000; margin: 4px 0; }
        .header-right { width: 100px; text-align: center; flex-shrink: 0; }
        .school-logo-container { width: 90px; height: 90px; display: flex; align-items: center; justify-content: center; }
        .school-logo { max-width: 85px; max-height: 85px; object-fit: contain; }
        .student-info-row { display: flex; justify-content: space-between; background: #f0f0f0; padding: 8px 14px; border: 1px solid #000; margin-bottom: 12px; font-size: 11pt; font-weight: 600; }
        .student-info-item { display: flex; align-items: center; gap: 6px; }
        .info-label { font-weight: 700; }
        .school-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 4mm; margin-bottom: 4mm; }
        .school-header img { max-height: 18mm; max-width: 40mm; object-fit: contain; }
        .school-header .school-name { font-size: 16pt; font-weight: 700; }
        .school-date { font-size: 11pt; color: #333; }
        .worksheet-title-plain { text-align: center; font-size: 16pt; font-weight: 700; margin: 0 0 4px; }
        .worksheet-sub-plain { text-align: center; font-size: 11pt; color: #444; margin-bottom: 6px; }
        .student-info { display: flex; flex-wrap: wrap; gap: 8mm 12mm; font-size: 12pt; margin-bottom: 5mm; }
        .student-info .field span { display: inline-block; min-width: 35mm; border-bottom: 1px solid #000; }
        .solve-label { font-weight: 700; font-size: 13pt; margin-bottom: 3mm; }
        .page-num { text-align: center; font-size: 8pt; color: #666; margin-top: 4mm; }
        .key-banner { text-align: center; font-size: 13pt; font-weight: 700; margin: 8px 0 6mm; text-transform: uppercase; }
        .print-note { font-size: 8pt; text-align: center; color: #555; margin-top: 2mm; }

        .problems-grid { display: flex; gap: 6mm; }
        .problems-col { flex: 1; }
        .problems-grid.pp-10 .horiz-problem { font-size: 16pt; line-height: 2.4; }
        .problems-grid.pp-15 .horiz-problem { font-size: 15pt; line-height: 2.2; }
        .problems-grid.pp-20 .horiz-problem { font-size: 14pt; line-height: 2.1; }
        .problems-grid.pp-25 .horiz-problem { font-size: 13pt; line-height: 2.0; }
        .problems-grid.pp-30 .horiz-problem { font-size: 12pt; line-height: 1.9; }
        .problems-grid.pp-40 .horiz-problem { font-size: 11pt; line-height: 1.8; }

        .horiz-problem { font-family: 'Courier New', Courier, monospace; font-weight: 600; white-space: pre; }
        .horiz-problem .blank { display: inline-block; min-width: 12mm; border-bottom: 1.5px solid #000; }
        .horiz-problem .answer { color: #c00; border-bottom: none; }

        .vert-grid { display: flex; flex-wrap: wrap; column-gap: 1%; row-gap: 5mm; justify-content: flex-start; }
        .vert-item { width: 19%; flex: 0 0 19%; margin-bottom: 2mm; text-align: center; }
        .vert-grid.pp-10 .vert-problem { font-size: 18pt; }
        .vert-grid.pp-15 .vert-problem { font-size: 17pt; }
        .vert-grid.pp-20 .vert-problem { font-size: 16pt; }
        .vert-grid.pp-25 .vert-problem { font-size: 15pt; }
        .vert-grid.pp-30 .vert-problem { font-size: 14pt; }
        .vert-grid.pp-40 .vert-problem { font-size: 13pt; }
        .vert-num { font-weight: 700; font-size: 13pt; margin-bottom: 1mm; text-align: left; padding-left: 4px; }
        .vert-problem { border-collapse: collapse; margin: 0 auto; width: 100%; max-width: 100%; font-family: 'Courier New', Courier, monospace; font-weight: 600; font-size: 16pt; }
        .vert-problem td { padding: 0; vertical-align: middle; line-height: 1.45; }
        .vert-problem .op-col { width: 1.1em; text-align: center; padding-right: 3px; }
        .vert-problem .num-cell { text-align: right; font-variant-numeric: tabular-nums; letter-spacing: 0; white-space: nowrap; padding-right: 1px; }
        .vert-problem .num-cell.blank { border-bottom: 1.5px solid #000; min-width: 2.5em; min-height: 1.1em; }
        .vert-problem .num-cell.blank.answer { color: #c00; border-bottom: none; }
        .vert-problem .num-cell.answer { color: #c00; }
        .vert-problem .line td { border-top: 1.5px solid #000; height: 0; padding: 0; line-height: 0; }
        .vert-problem .answer-blank { border-bottom: 1.5px solid #000; min-height: 1.35em; line-height: 1.35em; }
        .vert-problem .answer-blank.answer { color: #c00; border-bottom: none; text-align: right; font-variant-numeric: tabular-nums; }
        .vert-problem .result-extra { text-align: right; white-space: nowrap; }
        .vert-problem .result-extra.answer { color: #c00; }
        .vert-problem .result-extra.blank { border-bottom: 1.5px solid #000; width: 100%; min-height: 1.35em; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet { margin: 0; width: 100%; min-height: auto; box-shadow: none; padding: 0; page-break-after: always; }
            .horiz-problem .answer, .vert-problem .num-cell.answer, .vert-problem .num-cell.blank.answer, .vert-problem .answer-blank.answer, .vert-problem .result-extra.answer { color: #c00 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .printable-header { page-break-inside: avoid; break-inside: avoid; }
            .student-info-row { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<?php
    $layout      = $layout ?? 'horizontal';
    $perPage     = max(10, min(40, (int) ($perPage ?? 20)));
    $worksheets  = $worksheets ?? [];
    $operations  = $operations ?? ['+', '-'];
    $pageNum     = 0;

    $renderValue = static function (array $p, string $field, bool $showAnswers): string {
        $missing = (string) ($p['missing'] ?? 'result');
        $val     = match ($field) {
            'operand_a' => (string) ($p['operand_a'] ?? ''),
            'operand_b' => (string) ($p['operand_b'] ?? ''),
            'result'    => (string) ($p['result'] ?? ''),
            'remainder' => (string) ($p['remainder'] ?? ''),
            default     => '',
        };

        if ($showAnswers && ($missing === $field || ($field === 'result' && $missing === 'result'))) {
            if ($field === 'result' && ($p['remainder'] ?? null) !== null && (int) $p['remainder'] > 0) {
                return (string) $p['result'] . ' R ' . (int) $p['remainder'];
            }

            return $val;
        }

        if ($missing === $field) {
            return '';
        }

        return $val;
    };

    $formatHorizontal = static function (array $p, bool $showAnswers) use ($renderValue): string {
        $op  = (string) ($p['operation'] ?? '+');
        $a   = $renderValue($p, 'operand_a', $showAnswers);
        $b   = $renderValue($p, 'operand_b', $showAnswers);
        $res = $renderValue($p, 'result', $showAnswers);
        $missing = (string) ($p['missing'] ?? 'result');

        $padWidth = max(
            4,
            strlen((string) ($p['operand_a'] ?? '')),
            strlen((string) ($p['operand_b'] ?? '')),
            strlen((string) ($p['result'] ?? ''))
        );

        $fmt = static function (string $v, string $field, bool $answers, string $missingField) use ($padWidth): string {
            if ($v === '') {
                $cls = $answers ? 'answer' : 'blank';
                $blank = str_repeat('&nbsp;', max(4, (int) ceil($padWidth / 2)));

                return '<span class="' . $cls . '">' . $blank . '</span>';
            }

            $text = esc(str_pad($v, $padWidth, ' ', STR_PAD_LEFT));
            if ($answers && $missingField === $field) {
                return '<span class="answer">' . $text . '</span>';
            }

            return $text;
        };

        $aDisp = $fmt($a, 'operand_a', $showAnswers, $missing);
        $bDisp = $fmt($b, 'operand_b', $showAnswers, $missing);

        if ($missing === 'remainder' && ($p['remainder'] ?? null) !== null) {
            $resDisp = $showAnswers
                ? '<span class="answer">' . esc((string) $p['result'] . ' R ' . (int) $p['remainder']) . '</span>'
                : '<span class="blank">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
        } else {
            $resDisp = $fmt($res, 'result', $showAnswers, $missing);
        }

        return $aDisp . ' ' . esc($op) . ' ' . $bDisp . ' = ' . $resDisp;
    };

    $formatVertical = static function (array $p, bool $showAnswers): string {
        $op       = (string) ($p['operation'] ?? '+');
        $missing  = (string) ($p['missing'] ?? 'result');
        $operandA = (string) ($p['operand_a'] ?? '');
        $operandB = (string) ($p['operand_b'] ?? '');
        $result   = (string) ($p['result'] ?? '');
        $hasRem   = ($p['remainder'] ?? null) !== null && (int) $p['remainder'] > 0;

        $numCell = static function (
            string $value,
            string $field,
            string $missing,
            bool $showAnswers
        ): string {
            $hide = $missing === $field && ! $showAnswers;
            $cls  = 'num-cell';

            if ($hide) {
                $cls .= ' blank';

                return '<td class="' . $cls . '">&nbsp;</td>';
            }

            if ($showAnswers && $missing === $field) {
                $cls .= ' answer';
            }

            return '<td class="' . $cls . '">' . esc($value) . '</td>';
        };

        if ($missing === 'remainder' && $hasRem) {
            $html  = '<table class="vert-problem"><tbody>';
            $html .= '<tr class="num-row"><td class="op-col">&nbsp;</td>' . $numCell($operandA, 'operand_a', $missing, $showAnswers) . '</tr>';
            $html .= '<tr class="op-row"><td class="op-col">' . esc($op) . '</td>' . $numCell($operandB, 'operand_b', $missing, $showAnswers) . '</tr>';
            $html .= '<tr class="line"><td colspan="2"></td></tr>';

            if ($showAnswers) {
                $ansText = $result . ' R ' . (int) $p['remainder'];
                $html .= '<tr class="result-row"><td colspan="2" class="answer-blank answer">' . esc($ansText) . '</td></tr>';
            } else {
                $html .= '<tr class="result-row"><td colspan="2" class="result-extra blank">&nbsp;</td></tr>';
            }

            $html .= '</tbody></table>';

            return $html;
        }

        $html  = '<table class="vert-problem"><tbody>';
        $html .= '<tr class="num-row"><td class="op-col">&nbsp;</td>' . $numCell($operandA, 'operand_a', $missing, $showAnswers) . '</tr>';
        $html .= '<tr class="op-row"><td class="op-col">' . esc($op) . '</td>' . $numCell($operandB, 'operand_b', $missing, $showAnswers) . '</tr>';
        $html .= '<tr class="line"><td colspan="2"></td></tr>';

        if ($missing === 'result' && ! $showAnswers) {
            $html .= '<tr class="result-row"><td colspan="2" class="answer-blank">&nbsp;</td></tr>';
        } elseif ($showAnswers && $missing === 'result') {
            $html .= '<tr class="result-row"><td colspan="2" class="answer-blank answer">' . esc($result) . '</td></tr>';
        } else {
            $html .= '<tr class="result-row"><td class="op-col">&nbsp;</td>' . $numCell($result, 'result', $missing, $showAnswers) . '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    };

    $typeLabel = $layout === 'vertical' ? 'Vertical (stacked)' : 'Horizontal (inline)';
    $subtitle  = $typeLabel . ' · ' . esc($numberSummary ?? 'Math worksheet');
    $labels    = [];
    foreach (['+' => 'Addition', '-' => 'Subtraction', '×' => 'Multiplication', '÷' => 'Division'] as $op => $lbl) {
        if (in_array($op, $operations, true)) {
            $labels[] = $lbl;
        }
    }
    if ($labels !== []) {
        $subtitle .= ' · ' . implode(' & ', $labels);
    }

    $allPages = [];
    foreach ($worksheets as $ws) {
        $chunks = array_chunk($ws['problems'] ?? [], $perPage);
        foreach ($chunks as $chunk) {
            $allPages[] = [
                'problems'      => $chunk,
                'student_name'  => $ws['student_name'] ?? null,
                'roll_no'       => $ws['roll_no'] ?? null,
                'profile_photo' => $ws['profile_photo'] ?? null,
                'class_name'    => $ws['class_name'] ?? null,
            ];
        }
    }

    $totalPages = count($allPages) + (! empty($withAnswerKey) ? count($allPages) : 0);
    $ppClass    = 'pp-' . $perPage;

    $defaultAvatar = base_url('resource/img/avatar-student.png');
    $uploadsBase   = base_url('uploads/');

    $resolveStudentPhoto = static function (?string $photoRaw) use ($defaultAvatar, $uploadsBase): string {
        $photoRaw = trim((string) $photoRaw);
        if ($photoRaw === '') {
            return $defaultAvatar;
        }
        if (preg_match('#^https?://#i', $photoRaw)) {
            return $photoRaw;
        }

        return $uploadsBase . ltrim($photoRaw, '/');
    };
?>

<div class="no-print">
    <strong><?= esc($worksheetTitle) ?></strong>
    <span><?= esc($numberSummary ?? 'Math worksheet') ?> · <?= count($worksheets) ?> worksheet(s)</span>
    <button type="button" onclick="window.print()">Print (A4)</button>
    <button type="button" onclick="window.close()">Close</button>
</div>

<?php foreach ($allPages as $page): ?>
    <?php
        $pageNum++;
        $hasStudentHdr = ! empty($page['student_name']) || ! empty($page['profile_photo']);
        $sName         = trim((string) ($page['student_name'] ?? ''));
        $sRoll         = trim((string) ($page['roll_no'] ?? ''));
        $sClass        = trim((string) ($page['class_name'] ?? $clsSecName ?? ''));
        $sPhoto        = $resolveStudentPhoto($page['profile_photo'] ?? '');
    ?>
    <div class="sheet<?= $hasStudentHdr ? ' student-sheet' : '' ?>">
        <?php if ($hasStudentHdr): ?>
        <div class="printable-header">
            <div class="header-left">
                <div class="student-photo-container">
                    <img src="<?= esc($sPhoto) ?>" alt="Student Photo" class="student-photo"
                         onerror="this.src='<?= esc($defaultAvatar) ?>'">
                </div>
                <div class="student-name-header"><?= esc($sName !== '' ? $sName : 'Student') ?></div>
            </div>

            <div class="header-center">
                <div class="school-name"><?= esc($schoolName ?? 'School') ?></div>
                <?php if (! empty($campusName)): ?>
                    <div class="campus-name"><?= esc($campusName) ?></div>
                <?php endif; ?>
                <?php if (! empty($campusLocation)): ?>
                    <div class="campus-location"><?= esc($campusLocation) ?></div>
                <?php endif; ?>
                <div class="worksheet-title"><?= esc($worksheetTitle) ?></div>
                <div class="worksheet-sub"><?= esc($subtitle) ?></div>
                <?php if ($sClass !== ''): ?>
                    <div class="worksheet-sub">Class/Section: <strong><?= esc($sClass) ?></strong></div>
                <?php endif; ?>
            </div>

            <div class="header-right">
                <?php if (! empty($schoolLogo)): ?>
                    <div class="school-logo-container">
                        <img src="<?= esc($schoolLogo) ?>" alt="School Logo" class="school-logo">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="student-info-row">
            <div class="student-info-item">
                <span class="info-label">Roll No:</span>
                <span><?= esc($sRoll !== '' ? $sRoll : '__________') ?></span>
            </div>
            <div class="student-info-item">
                <span class="info-label">Date:</span>
                <?php $headerDate = strtotime((string) ($printDate ?? '')) ?: time(); ?>
                <span><?= esc(date('d/m/Y', $headerDate)) ?></span>
            </div>
            <div class="student-info-item">
                <span class="info-label">Class:</span>
                <span><?= esc($sClass !== '' ? $sClass : '__________') ?></span>
            </div>
            <div class="student-info-item">
                <span class="info-label">Marks:</span>
                <span>__________</span>
            </div>
        </div>

        <p class="solve-label">Solve:</p>
        <?php else: ?>
        <div class="school-header">
            <div>
                <?php if (! empty($schoolLogo)): ?>
                    <img src="<?= esc($schoolLogo) ?>" alt="">
                <?php endif; ?>
            </div>
            <div class="school-name"><?= esc($schoolName ?? 'School') ?></div>
            <div class="school-date"><?= esc($printDate ?? date('d M Y')) ?></div>
        </div>

        <h1 class="worksheet-title-plain"><?= esc($worksheetTitle) ?></h1>
        <p class="worksheet-sub-plain"><?= esc($subtitle) ?></p>
        <p class="solve-label">Solve:</p>
        <?php endif; ?>

        <?php if ($layout === 'vertical'): ?>
            <div class="vert-grid <?= esc($ppClass) ?>">
                <?php foreach ($page['problems'] as $p): ?>
                    <div class="vert-item">
                        <div class="vert-num"><?= (int) ($p['num'] ?? 0) ?>)</div>
                        <?= $formatVertical($p, false) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?php
                $half    = (int) ceil(count($page['problems']) / 2);
                $colLeft = array_slice($page['problems'], 0, $half);
                $colRight = array_slice($page['problems'], $half);
            ?>
            <div class="problems-grid <?= esc($ppClass) ?>">
                <div class="problems-col">
                    <?php foreach ($colLeft as $p): ?>
                        <div class="horiz-problem"><?= (int) ($p['num'] ?? 0) ?>) <?= $formatHorizontal($p, false) ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="problems-col">
                    <?php foreach ($colRight as $p): ?>
                        <div class="horiz-problem"><?= (int) ($p['num'] ?? 0) ?>) <?= $formatHorizontal($p, false) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <p class="print-note">Write your answers clearly in the blank spaces.</p>
        <p class="page-num">Page <?= $pageNum ?> of <?= $totalPages ?></p>
    </div>
<?php endforeach; ?>

<?php if (! empty($withAnswerKey)): ?>
    <?php foreach ($allPages as $page): ?>
        <?php $pageNum++; ?>
        <div class="sheet answer-key">
            <div class="key-banner">Answer Key</div>

            <?php if ($layout === 'vertical'): ?>
                <div class="vert-grid <?= esc($ppClass) ?>">
                    <?php foreach ($page['problems'] as $p): ?>
                        <div class="vert-item">
                            <div class="vert-num"><?= (int) ($p['num'] ?? 0) ?>)</div>
                            <?= $formatVertical($p, true) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php
                    $half     = (int) ceil(count($page['problems']) / 2);
                    $colLeft  = array_slice($page['problems'], 0, $half);
                    $colRight = array_slice($page['problems'], $half);
                ?>
                <div class="problems-grid <?= esc($ppClass) ?>">
                    <div class="problems-col">
                        <?php foreach ($colLeft as $p): ?>
                            <div class="horiz-problem"><?= (int) ($p['num'] ?? 0) ?>) <?= $formatHorizontal($p, true) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="problems-col">
                        <?php foreach ($colRight as $p): ?>
                            <div class="horiz-problem"><?= (int) ($p['num'] ?? 0) ?>) <?= $formatHorizontal($p, true) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <p class="page-num">Page <?= $pageNum ?> of <?= $totalPages ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
