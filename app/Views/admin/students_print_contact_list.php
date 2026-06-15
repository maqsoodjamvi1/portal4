<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(($mode ?? 'class') === 'family' ? 'Contact directory — by family' : 'Contact directory — by class') ?></title>
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
            line-height: 1.4;
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
        @media (max-width: 640px) {
            .report-meta { grid-template-columns: 1fr; }
        }

        .section-heading {
            margin: 16px 0 8px;
            padding: 6px 10px;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #fff;
            background: #1e3a5f;
            border-radius: 2px;
            page-break-after: avoid;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 14px;
            font-size: 0.78rem;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
        }
        table.data-table th {
            background: #334155;
            color: #f8fafc;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.68rem;
            letter-spacing: 0.05em;
            padding: 6px 6px;
        }
        table.data-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        table.data-table tbody tr {
            page-break-inside: avoid;
        }
        .col-sn { width: 2.2rem; text-align: center !important; }
        .col-tel { white-space: nowrap; }

        .family-block {
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
            border-radius: 2px;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .family-head {
            padding: 10px 12px;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #cbd5e1;
        }
        .family-head .parent-names {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }
        .family-head .parent-names .sep { color: #94a3b8; font-weight: 400; margin: 0 6px; }
        .family-head .muted { color: #64748b; font-size: 0.88rem; }
        .family-contacts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4px 16px;
            margin-top: 8px;
            font-size: 0.78rem;
        }
        .family-contacts .kv { margin: 0; }
        .family-contacts .kv span.label {
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.05em;
            margin-right: 6px;
        }
        @media (max-width: 520px) {
            .family-contacts { grid-template-columns: 1fr; }
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
    <span class="hint">A4 portrait · <?= (int) ($row_count ?? 0) ?> record(s). For best results choose margins &ldquo;Default&rdquo; or &ldquo;Minimum&rdquo; in the print dialog.</span>
</div>
<?php
    $rows = $rows ?? [];
    $mode = $mode ?? 'class';
    $schoolName = $school_name ?? 'School';
    $schoolLogo = trim((string) ($school_logo ?? ''));
    $campusLabel = trim((string) ($campus_label ?? ''));
    $filterSummary = $filter_summary ?? [];
    $printedAt = $printed_at ?? '';
    $printedBy = trim((string) ($printed_by ?? ''));
    $reportKind = $mode === 'family' ? 'Family grouping (siblings under one parent record)' : 'By class (ordered by class ID, all sections)';
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
        <h2>Student contact directory</h2>
        <p class="doc-sub"><?= esc($reportKind) ?></p>
    </div>

    <div class="report-meta">
        <div class="meta-cell">
            <span class="meta-label">Report criteria</span>
            <span class="meta-value"><?= esc(implode(' · ', $filterSummary) !== '' ? implode(' · ', $filterSummary) : 'No additional filters (full list)') ?></span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Prepared</span>
            <span class="meta-value"><?= esc($printedAt) ?><?php if ($printedBy !== ''): ?> · <?= esc($printedBy) ?><?php endif; ?></span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Total records</span>
            <span class="meta-value"><?= count($rows) ?> student<?= count($rows) === 1 ? '' : 's' ?></span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Document</span>
            <span class="meta-value">Internal directory — not valid for official certification</span>
        </div>
    </div>

    <?php if (count($rows) === 0): ?>
        <p class="empty">No students match the selected filters.</p>
    <?php elseif ($mode === 'family'): ?>
        <?php
        $lastPid = null;
        foreach ($rows as $r) {
            $pid = (int) ($r['parent_id'] ?? 0);
            $studentName = trim((string) ($r['first_name'] ?? '') . ' ' . (string) ($r['last_name'] ?? ''));
            $cn = trim((string) ($r['class_name'] ?? ''));
            $sn = trim((string) ($r['section_name'] ?? ''));
            $classSec = trim($cn . ($cn !== '' && $sn !== '' ? ' - ' : '') . $sn);

            if ($lastPid !== $pid) {
                if ($lastPid !== null) {
                    echo '</tbody></table></section>';
                }
                $lastPid = $pid;
                $father = trim((string) ($r['father_name'] ?? ''));
                $mother = trim((string) ($r['mother_name'] ?? ''));
                ?>
                <section class="family-block">
                    <div class="family-head">
                        <div class="parent-names">
                            <?php if ($father !== ''): ?>
                                <?= esc($father) ?>
                            <?php else: ?>
                                <span class="muted">Parent record <?= $pid > 0 ? '#' . (int) $pid : '' ?></span>
                            <?php endif; ?>
                            <?php if ($mother !== ''): ?>
                                <span class="sep">|</span> <?= esc($mother) ?>
                            <?php endif; ?>
                        </div>
                        <div class="family-contacts">
                            <p class="kv"><span class="label">Father tel</span><?= esc((string) ($r['father_contact'] ?? '')) ?: '—' ?></p>
                            <p class="kv"><span class="label">Mother tel</span><?= esc((string) ($r['mother_contact'] ?? '')) ?: '—' ?></p>
                            <p class="kv"><span class="label">Emergency</span><?= esc((string) ($r['emergency_contact'] ?? '')) ?: '—' ?></p>
                            <p class="kv"><span class="label">WhatsApp</span><?= esc((string) ($r['whatsapp'] ?? '')) ?: '—' ?></p>
                            <?php if (trim((string) ($r['city'] ?? '')) !== ''): ?>
                                <p class="kv"><span class="label">City</span><?= esc($r['city']) ?></p>
                            <?php endif; ?>
                            <?php if (trim((string) ($r['emergency_contact_person'] ?? '')) !== ''): ?>
                                <p class="kv" style="grid-column: 1 / -1;">
                                    <span class="label">Emergency contact</span>
                                    <?= esc($r['emergency_contact_person']) ?>
                                    <?php if (trim((string) ($r['relationship'] ?? '')) !== ''): ?>
                                        <span class="muted">(<?= esc($r['relationship']) ?>)</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-sn">#</th>
                                <th>Student</th>
                                <th>Class / section</th>
                            </tr>
                        </thead>
                        <tbody>
                <?php
                $famRow = 0;
            }
            $famRow++;
            ?>
                            <tr>
                                <td class="col-sn"><?= (int) $famRow ?></td>
                                <td><?= esc($studentName) ?></td>
                                <td><?= esc($classSec !== '' ? $classSec : '—') ?></td>
                            </tr>
            <?php
        }
        if ($lastPid !== null) {
            echo '</tbody></table></section>';
        }
        ?>
    <?php else: ?>
        <?php
        // One block per class (ordered by class_id in the query); section shown per row.
        $lastClassId = null;
        $classSn = 0;
        foreach ($rows as $r) {
            $classId = (int) ($r['class_id'] ?? 0);
            $cn = trim((string) ($r['class_name'] ?? ''));
            $sn = trim((string) ($r['section_name'] ?? ''));

            if ($classId !== $lastClassId) {
                if ($lastClassId !== null) {
                    echo '</tbody></table>';
                }
                $lastClassId = $classId;
                $classSn = 0;
                $label = $cn !== '' ? $cn : ($classId > 0 ? 'Class #' . $classId : 'Unassigned class');
                ?>
                <h3 class="section-heading"><?= esc($label) ?></h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-sn">#</th>
                            <th>Student</th>
                            <th>Section</th>
                            <th>Father</th>
                            <th>Mother</th>
                            <th class="col-tel">Father contact</th>
                            <th class="col-tel">Mother contact</th>
                            <th class="col-tel">Emergency</th>
                            <th class="col-tel">WhatsApp</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
            }
            $classSn++;
            $studentName = trim((string) ($r['first_name'] ?? '') . ' ' . (string) ($r['last_name'] ?? ''));
            ?>
                        <tr>
                            <td class="col-sn"><?= (int) $classSn ?></td>
                            <td><?= esc($studentName) ?></td>
                            <td><?= esc($sn !== '' ? $sn : '—') ?></td>
                            <td><?= esc((string) ($r['father_name'] ?? '')) ?></td>
                            <td><?= esc((string) ($r['mother_name'] ?? '')) ?></td>
                            <td class="col-tel"><?= esc((string) ($r['father_contact'] ?? '')) ?></td>
                            <td class="col-tel"><?= esc((string) ($r['mother_contact'] ?? '')) ?></td>
                            <td class="col-tel"><?= esc((string) ($r['emergency_contact'] ?? '')) ?></td>
                            <td class="col-tel"><?= esc((string) ($r['whatsapp'] ?? '')) ?></td>
                        </tr>
            <?php
        }
        if ($lastClassId !== null) {
            echo '</tbody></table>';
        }
        ?>
    <?php endif; ?>

    <footer class="report-footer">
        <strong><?= esc($schoolName) ?></strong>
        <?php if ($campusLabel !== ''): ?> · <?= esc($campusLabel) ?><?php endif; ?>
        <br>
        Generated <?= esc($printedAt) ?> — Student contact directory (<?= $mode === 'family' ? 'family view' : 'class view' ?>).
    </footer>
</div>
</body>
</html>
