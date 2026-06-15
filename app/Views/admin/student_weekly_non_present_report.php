<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Student Weekly Non-Present Report',
    'icon' => 'fas fa-calendar-week',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Weekly Non-Present Report', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <div class="card sms-card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-week me-2"></i>Report Filters</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-md-0">
                            <label for="term_session_id">Term Session <span class="text-danger">*</span></label>
                            <select id="term_session_id" class="form-control" required>
                                <option value="">Select Term Session</option>
                                <?php foreach ($terms ?? [] as $term): ?>
                                    <option value="<?= (int) ($term['term_session_id'] ?? 0) ?>">
                                        <?= esc($term['name'] ?? 'Term') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-md-0">
                            <label for="cls_sec_id">Class Section <span class="text-danger">*</span></label>
                            <select id="cls_sec_id" class="form-control" required>
                                <option value="">Select Class Section</option>
                                <option value="all">All Classes</option>
                                <?php foreach ($sections ?? [] as $section): ?>
                                    <option value="<?= (int) ($section['cls_sec_id'] ?? 0) ?>">
                                        <?= esc($section['sectionclassname'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-md-0">
                            <label>Include in Count</label>
                            <div class="border rounded p-2 bg-light">
                                <div class="form-check form-check">
                                    <input type="checkbox" class="form-check-input include-filter" id="include_absent" value="1" checked>
                                    <label class="form-check-label" for="include_absent">Absentees</label>
                                </div>
                                <div class="form-check form-check">
                                    <input type="checkbox" class="form-check-input include-filter" id="include_leave" value="1" checked>
                                    <label class="form-check-label" for="include_leave">Leave</label>
                                </div>
                                <div class="form-check form-check">
                                    <input type="checkbox" class="form-check-input include-filter" id="include_late" value="1" checked>
                                    <label class="form-check-label" for="include_late">Late Coming</label>
                                </div>
                                <div class="form-check form-check">
                                    <input type="checkbox" class="form-check-input include-filter" id="include_early_left" value="1" checked>
                                    <label class="form-check-label" for="include_early_left">Early Left</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-success w-100 mb-2" id="generateReportBtn">
                            <i class="fas fa-chart-bar me-1"></i> Generate Report
                        </button>
                        <button type="button" class="btn btn-primary w-100 d-none" id="printReportBtn">
                            <i class="fas fa-print me-1"></i> Print Report
                        </button>
                    </div>
                </div>

                <div class="position-relative mt-4" style="min-height:120px;">
                    <div id="reportLoader" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="reportContainer"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.weekly-non-present-table {
    table-layout: fixed;
    width: 100%;
    font-size: 12px;
}

.weekly-non-present-table th,
.weekly-non-present-table td {
    vertical-align: middle;
    padding: 5px 4px;
}

.weekly-non-present-table .col-num {
    width: 32px;
    max-width: 32px;
}

.weekly-non-present-table .col-student,
.weekly-non-present-table th.col-student {
    width: var(--student-col, 220px);
    min-width: var(--student-col, 220px);
    max-width: var(--student-col, 220px);
    overflow: hidden;
}

.weekly-non-present-table .student-name {
    text-align: left;
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    line-height: 1.35;
    font-size: 12px;
}

.weekly-non-present-table .col-week {
    width: auto;
    padding: 3px 2px;
}

.weekly-non-present-table .week-col {
    font-size: 11px;
    line-height: 1.25;
}

.weekly-non-present-table .week-col .week-days {
    font-size: 10px;
    font-weight: 400;
}

.weekly-non-present-table .week-count {
    font-size: 12px;
    font-weight: 600;
}

.weekly-non-present-table .col-total {
    width: 44px;
    max-width: 44px;
    font-size: 12px;
    font-weight: 600;
}

.weekly-non-present-table .summary-row td {
    background: #f8fafc;
    font-weight: 700;
}

.report-sections-wrap {
    margin-top: 8px;
}

.report-section-block {
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e2e8f0;
}

.report-section-block:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.section-report-heading {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 10px;
    padding: 8px 12px;
    background: linear-gradient(90deg, #eef2ff 0%, #f8fafc 100%);
    border-start: 4px solid #4e73df;
    border-radius: 0 4px 4px 0;
}

.report-preview-header {
    text-align: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #4e73df;
}

.report-preview-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.report-preview-subtitle {
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
}
</style>

<script>
$(function () {
    const CSRF_NAME = '<?= csrf_token() ?>';
    const CSRF_HASH = '<?= csrf_hash() ?>';
    let lastPrintHtml = '';

    function getIncludeFilterPayload() {
        return {
            include_absent: $('#include_absent').is(':checked') ? 1 : 0,
            include_leave: $('#include_leave').is(':checked') ? 1 : 0,
            include_late: $('#include_late').is(':checked') ? 1 : 0,
            include_early_left: $('#include_early_left').is(':checked') ? 1 : 0
        };
    }

    function hasSelectedIncludeFilter() {
        return $('.include-filter:checked').length > 0;
    }

    $('#generateReportBtn').on('click', function () {
        const termSessionId = $('#term_session_id').val();
        const clsSecId = $('#cls_sec_id').val();

        if (!termSessionId) {
            toastr.error('Please select a term session');
            return;
        }
        if (!clsSecId) {
            toastr.error('Please select a class section or All Classes');
            return;
        }
        if (!hasSelectedIncludeFilter()) {
            toastr.error('Please select at least one type to include in the count');
            return;
        }

        $('#reportLoader').removeClass('d-none');
        $('#reportContainer').empty();
        $('#printReportBtn').addClass('d-none');

        $.ajax({
            url: '<?= site_url('admin/student-weekly-non-present-report/data') ?>',
            method: 'POST',
            data: Object.assign({
                term_session_id: termSessionId,
                cls_sec_id: clsSecId,
                [CSRF_NAME]: CSRF_HASH
            }, getIncludeFilterPayload()),
            success: function (html) {
                $('#reportContainer').html(html);
                lastPrintHtml = html;
                if ($('#reportContainer').find('.report-section-block, table.weekly-non-present-table').length) {
                    $('#printReportBtn').removeClass('d-none');
                }
            },
            error: function () {
                $('#reportContainer').html('<div class="alert alert-danger mb-0">Failed to load report. Please try again.</div>');
            },
            complete: function () {
                $('#reportLoader').addClass('d-none');
            }
        });
    });

    $('#printReportBtn').on('click', function () {
        if (!lastPrintHtml) {
            toastr.error('Generate the report first before printing');
            return;
        }

        const termName = $('#reportMetaTerm').val() || $('#term_session_id option:selected').text().trim();
        const sectionName = $('#reportMetaSection').val()
            || ($('#cls_sec_id').val() === 'all' ? 'All Classes' : $('#cls_sec_id option:selected').text().trim());
        const filtersLabel = $('#reportMetaFilters').val() || '';
        const isAllClasses = ($('#reportMetaAllClasses').val() === '1');
        const $reportHtml = $('<div>').html(lastPrintHtml);
        const weekCount = parseInt($('#reportMetaWeekCount').val(), 10)
            || $reportHtml.find('thead th.week-col').first().length
            || $reportHtml.find('thead th.week-col').length;
        const studentColPx = parseInt($('#reportMetaStudentColPx').val(), 10) || 220;
        const studentColMm = (studentColPx * 0.264583).toFixed(1);
        const orientation = weekCount > 4 ? 'landscape' : 'portrait';
        const pageWidth = weekCount > 4 ? '277mm' : '190mm';
        const pageInnerMm = weekCount > 4 ? 263 : 176;
        const fixedMm = 5 + parseFloat(studentColMm) + 9;
        const weekColMm = weekCount > 0
            ? Math.max(6, (pageInnerMm - fixedMm) / weekCount).toFixed(1)
            : '8';
        const printedOn = new Date().toLocaleDateString('en-GB', {
            day: '2-digit', month: 'short', year: 'numeric'
        });

        let reportBodyHtml = '';
        if (isAllClasses) {
            $reportHtml.find('.report-section-block').each(function () {
                reportBodyHtml += this.outerHTML;
            });
        } else {
            reportBodyHtml = $reportHtml.find('.report-section-block').first().prop('outerHTML')
                || $reportHtml.find('table').first().prop('outerHTML')
                || lastPrintHtml;
        }

        const printableHtml = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Weekly Non-Present Report</title>
    <style>
        @page { size: A4 ${orientation}; margin: 8mm; }
        html, body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; }
        .print-wrap { width: ${pageWidth}; margin: 0 auto; }
        .print-header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 12px; }
        .print-title { font-size: 17px; font-weight: 700; letter-spacing: 0.3px; }
        .print-subtitle { font-size: 10px; color: #475569; margin-top: 4px; line-height: 1.5; }
        .print-meta { font-size: 9px; color: #64748b; margin-top: 2px; }
        .report-section-block { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .report-section-block.page-break-after { page-break-after: always; break-after: page; }
        .section-report-heading {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 8px;
            padding: 6px 10px;
            background: #eef2ff !important;
            border-start: 4px solid #4338ca;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-responsive { overflow: visible; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; table-layout: fixed; margin-bottom: 0; }
        thead { display: table-header-group; }
        tbody tr { page-break-inside: avoid; break-inside: avoid; }
        tfoot { display: table-footer-group; }
        th, td { border: 1px solid #374151 !important; padding: 3px 2px; vertical-align: middle; }
        th { background: #e2e8f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        td.text-center, th.text-center { text-align: center; }
        .col-num { width: 5mm; max-width: 5mm; font-size: 9px; }
        .col-student, .student-name {
            width: ${studentColMm}mm;
            min-width: ${studentColMm}mm;
            max-width: ${studentColMm}mm;
            text-align: left;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            overflow: hidden;
            line-height: 1.3;
            font-size: 9px;
            padding-left: 3px !important;
            padding-right: 3px !important;
        }
        .col-week { width: ${weekColMm}mm; max-width: ${weekColMm}mm; padding: 2px 1px !important; }
        .week-col { font-size: 9px; line-height: 1.15; white-space: normal; }
        .week-col .week-days { font-size: 8px; font-weight: 400; display: block; }
        .week-count { font-size: 10px; font-weight: 600; }
        .col-total { width: 9mm; max-width: 9mm; font-size: 10px; font-weight: 600; }
        .summary-row td { font-weight: 700; background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
<body>
    <div class="print-wrap">
        <div class="print-header">
            <div class="print-title">Student Weekly Non-Present Report</div>
            <div class="print-subtitle">Term: ${termName} | Section: ${sectionName}${filtersLabel ? ' | Included: ' + filtersLabel : ''}</div>
            <div class="print-meta">Printed on ${printedOn}</div>
        </div>
        ${reportBodyHtml}
    </div>
</body>
</html>`;

        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            toastr.error('Popup blocked. Please allow popups for printing.');
            return;
        }

        printWindow.document.open();
        printWindow.document.write(printableHtml);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () {
            printWindow.print();
        }, 250);
    });
});
</script>

<?= $this->endSection() ?>
