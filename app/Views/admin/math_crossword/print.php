<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($worksheetTitle) ?> — Grade <?= (int) $grade ?></title>
    <style>
        @page { size: A4 portrait; margin: 8mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; background: #dde1e6; }
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
        .school-header .school-name { font-size: 14pt; font-weight: 700; }
        .school-date { font-size: 9pt; color: #333; }
        .worksheet-title-plain { text-align: center; font-size: 13pt; font-weight: 700; margin: 0 0 4px; }
        .worksheet-sub-plain { text-align: center; font-size: 9pt; color: #444; margin-bottom: 6px; }
        .student-info { display: flex; flex-wrap: wrap; gap: 8mm 12mm; font-size: 10pt; margin-bottom: 5mm; }
        .student-info .field span { display: inline-block; min-width: 35mm; border-bottom: 1px solid #000; }
        .solve-label { font-weight: 700; margin-bottom: 4mm; }
        .page-num { text-align: center; font-size: 8pt; color: #666; margin-top: 4mm; }
        .puzzles-grid { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 6mm 3%; }
        .puzzles-grid.layout-1 { justify-content: center; }
        .puzzles-grid.layout-2 { flex-direction: column; align-items: center; gap: 8mm; }
        .puzzle-item { width: 48%; margin-bottom: 4mm; text-align: center; overflow: visible; }
        .puzzle-item.full-width { width: 100%; }
        .puzzles-grid.layout-2 .puzzle-item { width: 100%; }
        .puzzle-num { font-weight: 700; font-size: 11pt; margin-bottom: 2mm; }
        .student-tag { font-size: 9pt; font-weight: 400; color: #333; }
        .puzzle-wrap { display: inline-block; line-height: 0; margin: 0 auto; }
        .math-grid { border-collapse: separate; border-spacing: 0; margin: 0; table-layout: fixed; border: 2px solid #000; }
        .math-grid.grid-5.size-1 td { width: 20mm; height: 20mm; font-size: 16pt; }
        .math-grid.grid-5.size-2 td { width: 14mm; height: 14mm; font-size: 12pt; }
        .math-grid.grid-5.size-4 td { width: 11mm; height: 11mm; font-size: 10pt; }
        .math-grid.grid-7.size-1 td { width: 24mm; height: 24mm; font-size: 18pt; }
        .math-grid.grid-7.size-2 td { width: 15mm; height: 15mm; font-size: 14pt; }
        .math-grid.grid-7.size-4 td { width: 12mm; height: 12mm; font-size: 12pt; }
        .math-grid.grid-13.size-1 td { width: 12mm; height: 12mm; font-size: 11pt; }
        .math-grid.grid-13.size-2 td { width: 10mm; height: 10mm; font-size: 9pt; }
        .math-grid.grid-13.size-4 td { width: 8mm; height: 8mm; font-size: 8pt; }
        .math-grid td { border: 1px solid #000; text-align: center; vertical-align: middle; font-weight: 700; line-height: 1; padding: 0; }
        .math-grid td.blank, .math-grid td.block { background: #c8c8c8; }
        .math-grid td.block { background: #333; }
        .math-grid td.operator, .math-grid td.equals { background: #e8e8e8; }
        .math-grid td.number { background: #fff; }
        .math-grid td.result { background: #fff; color: #c00; }
        .math-grid td.result.empty, .math-grid td.operator.empty, .math-grid td.letter.empty { color: transparent; background: #fff; }
        .math-grid td.letter { background: #fff; text-transform: uppercase; }
        .math-grid td.letter.filled { color: #000; }
        .clues-box { text-align: left; font-size: 9pt; margin-top: 3mm; max-width: 100%; }
        .clues-box h4 { font-size: 10pt; margin: 2mm 0 1mm; }
        .clues-box ol { margin: 0 0 2mm 4mm; padding: 0; }
        .key-banner { text-align: center; font-size: 13pt; font-weight: 700; margin: 8px 0; text-transform: uppercase; }
        .print-note { font-size: 8pt; text-align: center; color: #555; margin-top: 2mm; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet { margin: 0; width: 100%; min-height: auto; box-shadow: none; padding: 0; page-break-after: always; }
            .math-grid { border: 1.5pt solid #000; }
            .math-grid td { border: 0.75pt solid #000; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .math-grid td.blank { background: #c8c8c8 !important; }
            .math-grid td.block { background: #333 !important; }
            .math-grid td.operator, .math-grid td.equals { background: #e8e8e8 !important; }
            .printable-header { page-break-inside: avoid; break-inside: avoid; }
            .student-info-row { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <strong><?= esc($worksheetTitle) ?></strong>
    <span>Grade <?= (int) $grade ?> · <?= count($puzzles) ?> puzzle(s)</span>
    <button type="button" onclick="window.print()">Print (A4)</button>
    <button type="button" onclick="window.close()">Close</button>
</div>

<?php
    $perPage     = max(1, min(4, (int) ($perPage ?? 4)));
    $chunks      = array_chunk($puzzles, $perPage);
    $puzzleIndex = 0;
    $totalPages  = count($chunks) + (! empty($withAnswerKey) ? count($chunks) : 0);
    $pageNum     = 0;
    $puzzleType  = $puzzleType ?? 'math_square';

    $gridSizeClass = match ($perPage) {
        1       => 'size-1',
        2       => 'size-2',
        default => 'size-4',
    };
    $layoutClass = match ($perPage) {
        1       => 'layout-1',
        2       => 'layout-2',
        default => 'layout-4',
    };

    $renderPuzzle = static function (array $puzzle, bool $showAnswers, string $gridSizeClass): string {
        $cells = $puzzle['cells'] ?? [];
        $size  = (int) ($puzzle['size'] ?? 7);
        $gridClass = 'grid-' . $size;
        $html  = '<div class="puzzle-wrap"><table class="math-grid ' . esc($gridClass) . ' ' . esc($gridSizeClass) . '" cellspacing="0" cellpadding="0"><tbody>';

        for ($r = 0; $r < $size; $r++) {
            $html .= '<tr>';
            for ($c = 0; $c < $size; $c++) {
                $cell = $cells[$r][$c] ?? ['type' => 'blank'];
                $type = $cell['type'] ?? 'blank';
                $val  = $cell['value'] ?? '';
                $isAnswer = ! empty($cell['answer']);

                if ($type === 'block') {
                    $html .= '<td class="block">&nbsp;</td>';
                    continue;
                }
                if ($type === 'blank') {
                    $html .= '<td class="blank">&nbsp;</td>';
                    continue;
                }
                if ($type === 'letter') {
                    $sol = (string) ($cell['solution'] ?? '');
                    $display = ($showAnswers && $isAnswer) ? esc($sol) : '';
                    $class = 'letter' . ($display === '' ? ' empty' : ' filled');
                    $html .= '<td class="' . $class . '">' . ($display !== '' ? $display : '&nbsp;') . '</td>';
                    continue;
                }
                if ($type === 'result') {
                    $display = ($showAnswers && $isAnswer) ? esc((string) $val) : '';
                    $class   = 'result' . ($display === '' ? ' empty' : '');
                    $html .= '<td class="' . $class . '">' . ($display !== '' ? $display : '&nbsp;') . '</td>';
                    continue;
                }
                if ($type === 'operator' && $isAnswer) {
                    $display = $showAnswers ? esc((string) $val) : '';
                    $class   = 'operator' . ($display === '' ? ' empty' : '');
                    $html .= '<td class="' . $class . '">' . ($display !== '' ? $display : '&nbsp;') . '</td>';
                    continue;
                }

                $html .= '<td class="' . esc($type) . '">' . esc((string) $val) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        if (! empty($puzzle['clues']) && ($puzzle['type'] ?? '') === 'vocab') {
            $html .= '<div class="clues-box">';
            foreach (['across' => 'Across', 'down' => 'Down'] as $key => $label) {
                if (empty($puzzle['clues'][$key])) {
                    continue;
                }
                $html .= '<h4>' . $label . '</h4><ol>';
                foreach ($puzzle['clues'][$key] as $clue) {
                    $html .= '<li><strong>' . (int) ($clue['num'] ?? 0) . '.</strong> ' . esc($clue['clue'] ?? '') . '</li>';
                }
                $html .= '</ol>';
            }
            $html .= '</div>';
        }

        return $html;
    };

    $typeLabel = match ($puzzleType) {
        'vocab'            => 'Vocabulary Crossword',
        'mini_5x5'         => 'Mini 5×5 Math Square',
        'missing_operator' => 'Missing Operator Puzzle',
        default            => 'Math Square Across–Down',
    };

    $subtitle = $typeLabel . ' · Grade ' . (int) $grade;
    if (! empty($operations) && $puzzleType !== 'vocab') {
        $labels = [];
        foreach (['+' => 'Addition', '-' => 'Subtraction', '×' => 'Multiplication', '÷' => 'Division'] as $op => $lbl) {
            if (in_array($op, $operations, true)) {
                $labels[] = $lbl;
            }
        }
        if ($labels !== []) {
            $subtitle .= ' · ' . implode(' & ', $labels);
        }
    }

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

<?php foreach ($chunks as $pagePuzzles): ?>
    <?php
        $pageNum++;
        $pageStudent   = $pagePuzzles[0] ?? [];
        $hasStudentHdr = ! empty($pageStudent['student_name']) || ! empty($pageStudent['profile_photo']);
        $sName         = trim((string) ($pageStudent['student_name'] ?? $studentName ?? ''));
        $sRoll         = trim((string) ($pageStudent['roll_no'] ?? ''));
        $sClass        = trim((string) ($pageStudent['class_name'] ?? $clsSecName ?? ''));
        $sPhoto        = $resolveStudentPhoto($pageStudent['profile_photo'] ?? '');
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

        <?php if ($puzzleIndex === 0 && ! empty($studentName)): ?>
        <div class="student-info">
            <div class="field">Name: <span><?= esc($studentName) ?></span></div>
            <div class="field">Date: <span><?= esc($printDate ?? date('d M Y')) ?></span></div>
        </div>
        <?php endif; ?>
        <p class="solve-label">Solve:</p>
        <?php endif; ?>

        <div class="puzzles-grid <?= esc($layoutClass) ?>">
            <?php foreach ($pagePuzzles as $puzzle): ?>
                <?php $puzzleIndex++; ?>
                <div class="puzzle-item <?= $perPage === 1 ? 'full-width' : '' ?>">
                    <?php if (! $hasStudentHdr || count($pagePuzzles) > 1): ?>
                    <div class="puzzle-num">
                        <?= $puzzleIndex ?>.
                        <?php if (! empty($puzzle['student_name'])): ?>
                            <span class="student-tag"> — <?= esc($puzzle['student_name']) ?><?= ! empty($puzzle['roll_no']) ? ' (' . esc($puzzle['roll_no']) . ')' : '' ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?= $renderPuzzle($puzzle, false, $gridSizeClass) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="print-note">Fill empty boxes only. Check row and column totals match.</p>
        <p class="page-num">Page <?= $pageNum ?> of <?= $totalPages ?></p>
    </div>
<?php endforeach; ?>

<?php if (! empty($withAnswerKey)): ?>
    <?php $puzzleIndex = 0; foreach ($chunks as $pagePuzzles): ?>
    <?php $pageNum++; ?>
    <div class="sheet answer-key">
        <div class="key-banner">Answer Key</div>
        <div class="puzzles-grid <?= esc($layoutClass) ?>">
            <?php foreach ($pagePuzzles as $puzzle): ?>
                <?php $puzzleIndex++; ?>
                <div class="puzzle-item <?= $perPage === 1 ? 'full-width' : '' ?>">
                    <div class="puzzle-num"><?= $puzzleIndex ?>.</div>
                    <?= $renderPuzzle($puzzle, true, $gridSizeClass) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="page-num">Page <?= $pageNum ?> of <?= $totalPages ?></p>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
