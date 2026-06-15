<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Class section roster</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 11mm 14mm 11mm;
        }
        * { box-sizing: border-box; }
        html { font-size: 10.5pt; }
        body {
            margin: 0;
            font-family: "Segoe UI", "Helvetica Neue", Helvetica, Arial, sans-serif;
            line-height: 1.45;
            color: #1a1a1a;
            background: #e8eaed;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #1e293b;
            color: #f8fafc;
            padding: 10px 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .toolbar button {
            background: #f8fafc;
            color: #0f172a;
            border: 0;
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
        }
        .toolbar button:hover { background: #e2e8f0; }
        .toolbar .hint { font-size: 0.85rem; opacity: 0.88; max-width: 520px; }

        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 12px auto 24px;
            padding: 14mm 12mm 16mm;
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }

        .letterhead {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 12px;
            border-bottom: 3px double #0f172a;
            margin-bottom: 14px;
        }
        .letterhead-logo {
            flex: 0 0 auto;
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .letterhead-logo img {
            max-width: 72px;
            max-height: 72px;
            object-fit: contain;
        }
        .letterhead-text { flex: 1; min-width: 0; }
        .school-name {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #0f172a;
            text-transform: uppercase;
        }
        .campus-line {
            margin: 4px 0 0;
            font-size: 0.95rem;
            color: #475569;
            font-weight: 500;
        }

        .doc-title {
            text-align: center;
            margin: 18px 0 6px;
            padding: 10px 12px;
            background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            border-radius: 2px;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #0f172a;
        }
        .doc-title .doc-sub {
            margin: 6px 0 0;
            font-size: 0.88rem;
            color: #334155;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .report-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 24px;
            font-size: 0.82rem;
            color: #334155;
            margin-bottom: 16px;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 2px;
        }
        .report-meta .meta-cell .meta-label {
            display: block;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.68rem;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }
        .report-meta .meta-cell .meta-value {
            font-weight: 500;
            color: #1e293b;
            line-height: 1.35;
        }

        table.roster-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 0.82rem;
            table-layout: fixed;
        }
        table.roster-table thead {
            display: table-header-group;
        }
        table.roster-table th,
        table.roster-table td {
            border: 1px solid #94a3b8;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        table.roster-table th {
            background: #334155;
            color: #f8fafc;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.68rem;
            letter-spacing: 0.05em;
        }
        table.roster-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        table.roster-table tbody tr {
            page-break-inside: avoid;
        }
        .col-section {
            width: 22%;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
        }
        .col-students {
            width: 78%;
            line-height: 1.5;
            word-break: break-word;
        }
        .gender-group {
            margin-bottom: 6px;
        }
        .gender-group:last-of-type {
            margin-bottom: 0;
        }
        .gender-label {
            display: inline-block;
            min-width: 3.2rem;
            margin-right: 6px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            vertical-align: top;
        }
        .gender-label.male { color: #1d4ed8; }
        .gender-label.female { color: #c2410c; }
        .gender-label.other { color: #64748b; }
        .student-name {
            display: inline;
            font-weight: 500;
        }
        .student-name.male { color: #1e40af; }
        .student-name.female { color: #9a3412; }
        .student-name.other { color: #475569; }
        .gender-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 2px;
            font-size: 0.78rem;
        }
        .gender-legend .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .gender-legend .swatch {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            border: 1px solid rgba(0,0,0,.08);
        }
        .gender-legend .swatch.male { background: #dbeafe; border-color: #93c5fd; }
        .gender-legend .swatch.female { background: #ffedd5; border-color: #fdba74; }
        .col-students .count {
            display: block;
            margin-top: 4px;
            font-size: 0.72rem;
            color: #64748b;
            font-weight: 600;
        }
        .empty-names {
            color: #94a3b8;
            font-style: italic;
        }

        .report-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #cbd5e1;
            font-size: 0.72rem;
            color: #64748b;
            text-align: center;
        }
        .report-footer strong { color: #475569; }

        .empty {
            padding: 36px 20px;
            text-align: center;
            color: #64748b;
            font-size: 0.95rem;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
        }

        @media print {
            html { font-size: 10pt; }
            body { background: #fff; }
            .toolbar { display: none !important; }
            .a4-sheet {
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: auto;
                box-shadow: none;
            }
            .letterhead { padding-bottom: 10px; }
        }
    </style>
</head>
<body>
<div class="toolbar no-print">
    <button type="button" onclick="window.print()">Print</button>
    <span class="hint">A4 portrait · <?= (int) ($section_count ?? 0) ?> section(s), <?= (int) ($student_count ?? 0) ?> student(s).</span>
</div>
<?php
    $rows = $rows ?? [];
    $schoolName = $school_name ?? 'School';
    $schoolLogo = trim((string) ($school_logo ?? ''));
    $campusLabel = trim((string) ($campus_label ?? ''));
    $filterSummary = $filter_summary ?? [];
    $printedAt = $printed_at ?? '';
    $printedBy = trim((string) ($printed_by ?? ''));
?>
<div class="a4-sheet">
    <header class="letterhead">
        <?php if ($schoolLogo !== ''): ?>
            <div class="letterhead-logo">
                <img src="<?= esc($schoolLogo) ?>" alt="">
            </div>
        <?php endif; ?>
        <div class="letterhead-text">
            <h1 class="school-name"><?= esc($schoolName) ?></h1>
            <?php if ($campusLabel !== ''): ?>
                <p class="campus-line"><?= esc($campusLabel) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <div class="doc-title">
        <h2>Class section roster</h2>
        <p class="doc-sub">Students listed by class section</p>
    </div>

    <div class="report-meta">
        <div class="meta-cell">
            <span class="meta-label">Report criteria</span>
            <span class="meta-value"><?= esc(implode(' · ', $filterSummary) !== '' ? implode(' · ', $filterSummary) : 'All active sections') ?></span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Prepared</span>
            <span class="meta-value"><?= esc($printedAt) ?><?php if ($printedBy !== ''): ?> · <?= esc($printedBy) ?><?php endif; ?></span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Sections</span>
            <span class="meta-value"><?= (int) ($section_count ?? count($rows)) ?></span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Total students</span>
            <span class="meta-value"><?= (int) ($student_count ?? 0) ?></span>
        </div>
    </div>

    <?php if (count($rows) === 0): ?>
        <p class="empty">No class sections match the selected filters.</p>
    <?php else: ?>
        <div class="gender-legend">
            <span class="legend-item"><span class="swatch male"></span> Boys (blue)</span>
            <span class="legend-item"><span class="swatch female"></span> Girls (orange)</span>
        </div>
        <table class="roster-table">
            <thead>
                <tr>
                    <th class="col-section">Class section</th>
                    <th class="col-students">Students</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                        $maleNames = $row['male_names'] ?? [];
                        $femaleNames = $row['female_names'] ?? [];
                        $otherNames = $row['other_names'] ?? [];
                        $count = (int) ($row['student_count'] ?? 0);
                        $hasStudents = $count > 0;
                    ?>
                    <tr>
                        <td class="col-section"><?= esc((string) ($row['section_label'] ?? '')) ?></td>
                        <td class="col-students">
                            <?php if (! $hasStudents): ?>
                                <span class="empty-names">No students enrolled</span>
                            <?php else: ?>
                                <?php if (! empty($maleNames)): ?>
                                    <div class="gender-group">
                                        <span class="gender-label male">Boys</span>
                                        <?php foreach ($maleNames as $i => $name): ?>
                                            <?php if ($i > 0): ?><span class="student-name male">, </span><?php endif; ?>
                                            <span class="student-name male"><?= esc($name) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (! empty($femaleNames)): ?>
                                    <div class="gender-group">
                                        <span class="gender-label female">Girls</span>
                                        <?php foreach ($femaleNames as $i => $name): ?>
                                            <?php if ($i > 0): ?><span class="student-name female">, </span><?php endif; ?>
                                            <span class="student-name female"><?= esc($name) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (! empty($otherNames)): ?>
                                    <div class="gender-group">
                                        <span class="gender-label other">Other</span>
                                        <?php foreach ($otherNames as $i => $name): ?>
                                            <?php if ($i > 0): ?><span class="student-name other">, </span><?php endif; ?>
                                            <span class="student-name other"><?= esc($name) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <span class="count">
                                <?= $count ?> student<?= $count === 1 ? '' : 's' ?>
                                <?php if ($count > 0): ?>
                                    (<?= (int) ($row['male_count'] ?? 0) ?> boys, <?= (int) ($row['female_count'] ?? 0) ?> girls)
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <footer class="report-footer">
        <strong><?= esc($schoolName) ?></strong>
        <?php if ($campusLabel !== ''): ?> · <?= esc($campusLabel) ?><?php endif; ?>
        <br>
        Generated <?= esc($printedAt) ?> — Class section roster.
    </footer>
</div>
</body>
</html>
