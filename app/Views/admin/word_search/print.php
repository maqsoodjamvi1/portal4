<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= esc($worksheetTitle) ?></title>
    <style>
        @page { size: A4 portrait; margin: 8mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; background: #dde1e6; }
        .no-print { position: sticky; top: 0; z-index: 100; background: #1e293b; color: #fff; padding: 10px 16px; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .no-print button { background: #3b82f6; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
        .sheet { width: 210mm; min-height: 277mm; margin: 10px auto; background: #fff; padding: 8mm 10mm; box-shadow: 0 2px 10px rgba(0,0,0,.1); page-break-after: always; }
        .sheet.student-sheet { border: 2px solid #000; padding: 6mm 8mm; }
        .printable-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; margin-bottom: 12px; border-bottom: 3px solid #000; gap: 12px; }
        .header-left { width: 130px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; flex-shrink: 0; }
        .student-photo-container { width: 100px; height: 100px; border: 2px solid #000; border-radius: 50%; overflow: hidden; background: #f0f0f0; }
        .student-photo { width: 100%; height: 100%; object-fit: cover; }
        .student-name-header { font-size: 14pt; font-weight: 800; text-transform: uppercase; text-align: center; }
        .header-center { flex: 1; text-align: center; }
        .school-name { font-size: 20pt; font-weight: 900; text-transform: uppercase; }
        .campus-name { font-size: 14pt; font-weight: 700; }
        .worksheet-title { font-size: 16pt; font-weight: 800; border-bottom: 2px solid #000; display: inline-block; padding-bottom: 4px; }
        .worksheet-sub { font-size: 11pt; margin: 4px 0; }
        .header-right { width: 100px; flex-shrink: 0; text-align: center; }
        .school-logo { max-width: 85px; max-height: 85px; object-fit: contain; }
        .student-info-row { display: flex; justify-content: space-between; background: #f0f0f0; padding: 8px 14px; border: 1px solid #000; margin-bottom: 12px; font-size: 11pt; font-weight: 600; }
        .school-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 4mm; margin-bottom: 4mm; }
        .school-header .school-name { font-size: 14pt; font-weight: 700; }
        .ws-layout { display: flex; gap: 8mm; align-items: flex-start; }
        .ws-grid-wrap { flex: 1; }
        .ws-grid { border-collapse: collapse; margin: 0 auto; }
        .ws-grid td { width: 9mm; height: 9mm; border: 1px solid #333; text-align: center; vertical-align: middle; font-weight: 700; font-size: 12pt; }
        .ws-grid.size-lg td { width: 11mm; height: 11mm; font-size: 14pt; }
        .ws-words { flex: 0 0 55mm; }
        .ws-words h3 { font-size: 12pt; margin: 0 0 4mm; }
        .ws-words ul { list-style: none; padding: 0; margin: 0; columns: 2; font-size: 10pt; }
        .ws-words li { margin-bottom: 2mm; }
        .ws-words .clue { display: block; font-size: 8pt; color: #444; font-weight: 400; }
        .key-banner { text-align: center; font-weight: 700; font-size: 14pt; margin-bottom: 6mm; text-transform: uppercase; }
        .answer-highlight { background: #ffff99 !important; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet { margin: 0; width: 100%; box-shadow: none; page-break-after: always; }
            .answer-highlight { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<?php
    $puzzles = $puzzles ?? [];
    $dirLabel = ($directionMode ?? 'hvd') === 'hv' ? 'Horizontal & Vertical' : 'Horizontal, Vertical & Diagonal';
    $defaultAvatar = base_url('resource/img/avatar-student.png');
    $uploadsBase = base_url('uploads/');
    $resolvePhoto = static function (?string $raw) use ($defaultAvatar, $uploadsBase): string {
        $raw = trim((string) $raw);
        if ($raw === '') return $defaultAvatar;
        if (preg_match('#^https?://#i', $raw)) return $raw;
        return $uploadsBase . ltrim($raw, '/');
    };
?>

<div class="no-print">
    <strong><?= esc($worksheetTitle) ?></strong>
    <span><?= count($puzzles) ?> puzzle(s)</span>
    <button type="button" onclick="window.print()">Print (A4)</button>
    <button type="button" onclick="window.close()">Close</button>
</div>

<?php foreach ($puzzles as $pi => $puzzle): ?>
    <?php
        $rows = (int) ($puzzle['rows'] ?? 15);
        $grid = $puzzle['grid'] ?? [];
        $words = $puzzle['words'] ?? [];
        $placements = $puzzle['placements'] ?? [];
        $hasStudent = ! empty($puzzle['student_name']) || ! empty($puzzle['profile_photo']);
        $sName = trim((string) ($puzzle['student_name'] ?? $studentName ?? ''));
        $sRoll = trim((string) ($puzzle['roll_no'] ?? ''));
        $sClass = trim((string) ($puzzle['class_name'] ?? $clsSecName ?? ''));
        $sPhoto = $resolvePhoto($puzzle['profile_photo'] ?? '');
        $sizeClass = $rows <= 12 ? 'size-lg' : '';
        $highlightCells = [];
    ?>
    <div class="sheet<?= $hasStudent ? ' student-sheet' : '' ?>">
        <?php if ($hasStudent): ?>
        <div class="printable-header">
            <div class="header-left">
                <div class="student-photo-container">
                    <img src="<?= esc($sPhoto) ?>" class="student-photo" alt="" onerror="this.src='<?= esc($defaultAvatar) ?>'">
                </div>
                <div class="student-name-header"><?= esc($sName !== '' ? $sName : 'Student') ?></div>
            </div>
            <div class="header-center">
                <div class="school-name"><?= esc($schoolName ?? 'School') ?></div>
                <?php if (! empty($campusName)): ?><div class="campus-name"><?= esc($campusName) ?></div><?php endif; ?>
                <div class="worksheet-title"><?= esc($worksheetTitle) ?></div>
                <div class="worksheet-sub">Word Search · Grade <?= (int) ($grade ?? 1) ?> · <?= esc($dirLabel) ?></div>
                <?php if ($sClass !== ''): ?><div class="worksheet-sub">Class/Section: <strong><?= esc($sClass) ?></strong></div><?php endif; ?>
            </div>
            <div class="header-right">
                <?php if (! empty($schoolLogo)): ?><img src="<?= esc($schoolLogo) ?>" class="school-logo" alt=""><?php endif; ?>
            </div>
        </div>
        <div class="student-info-row">
            <span><strong>Roll No:</strong> <?= esc($sRoll !== '' ? $sRoll : '__________') ?></span>
            <span><strong>Date:</strong> <?= esc($printDate ?? date('d M Y')) ?></span>
            <span><strong>Class:</strong> <?= esc($sClass !== '' ? $sClass : '__________') ?></span>
        </div>
        <?php else: ?>
        <div class="school-header">
            <div><?php if (! empty($schoolLogo)): ?><img src="<?= esc($schoolLogo) ?>" alt="" style="max-height:18mm"><?php endif; ?></div>
            <div class="school-name"><?= esc($schoolName ?? 'School') ?></div>
            <div><?= esc($printDate ?? date('d M Y')) ?></div>
        </div>
        <h1 style="text-align:center;font-size:16pt;margin:0 0 4px"><?= esc($worksheetTitle) ?></h1>
        <p style="text-align:center;font-size:10pt;color:#444">Word Search · Grade <?= (int) ($grade ?? 1) ?> · <?= esc($dirLabel) ?></p>
        <?php endif; ?>

        <div class="ws-layout">
            <div class="ws-grid-wrap">
                <table class="ws-grid <?= esc($sizeClass) ?>">
                    <?php for ($r = 0; $r < $rows; $r++): ?>
                    <tr>
                        <?php for ($c = 0; $c < $rows; $c++): ?>
                        <td><?= esc($grid[$r][$c] ?? '') ?></td>
                        <?php endfor; ?>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>
            <div class="ws-words">
                <h3>Find these words:</h3>
                <ul>
                    <?php foreach ($words as $w): ?>
                    <li><strong><?= esc($w['word'] ?? '') ?></strong>
                        <?php if (! empty($w['clue'])): ?><span class="clue"><?= esc($w['clue']) ?></span><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <?php if (! empty($withAnswerKey)): ?>
    <?php
        foreach ($placements as $pl) {
            foreach ($pl['cells'] ?? [] as $cell) {
                $highlightCells[$cell[0] . '_' . $cell[1]] = true;
            }
        }
    ?>
    <div class="sheet">
        <div class="key-banner">Answer Key<?= $hasStudent ? ' — ' . esc($sName) : '' ?></div>
        <div class="ws-layout">
            <div class="ws-grid-wrap">
                <table class="ws-grid <?= esc($sizeClass) ?>">
                    <?php for ($r = 0; $r < $rows; $r++): ?>
                    <tr>
                        <?php for ($c = 0; $c < $rows; $c++):
                            $hl = isset($highlightCells[$r . '_' . $c]) ? ' answer-highlight' : '';
                        ?>
                        <td class="<?= trim($hl) ?>"><?= esc($grid[$r][$c] ?? '') ?></td>
                        <?php endfor; ?>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>
            <div class="ws-words">
                <ul>
                    <?php foreach ($words as $w): ?>
                    <li><?= esc($w['word'] ?? '') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

</body>
</html>
