<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Student Daily Report',
    'icon' => 'fas fa-calendar-day',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Daily Report', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <div class="card sms-card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-day me-2"></i>Report Filters</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-md-0">
                            <label for="report_date">Select Date</label>
                            <input type="date" id="report_date" class="form-control" value="<?= esc($defaultDate ?? date('Y-m-d')) ?>">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <button type="button" class="btn btn-success" id="generateReportBtn">
                            <i class="fas fa-chart-bar me-1"></i> Generate Report
                        </button>
                        <button type="button" class="btn btn-outline-success" id="exportDailyReportBtn">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </button>
                        <button type="button" class="btn btn-primary d-none" id="printReportBtn">
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
.daily-report-table th,
.daily-report-table td {
    vertical-align: top;
}

.daily-report-table .section-name {
    font-weight: 600;
    white-space: nowrap;
}

.daily-report-table .absent-names {
    text-align: left;
    line-height: 1.5;
}

.daily-report-table .summary-row td {
    background: #f8fafc;
    font-weight: 700;
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

    const exportUrl = <?= json_encode(site_url('admin/student-daily-report/export')) ?>;

    $('#exportDailyReportBtn').on('click', function () {
        const reportDate = $('#report_date').val();
        if (!reportDate) {
            toastr.error('Please select a date');
            return;
        }
        const $form = $('<form method="post"></form>').attr('action', exportUrl);
        $form.append($('<input type="hidden">').attr('name', CSRF_NAME).val(CSRF_HASH));
        $form.append($('<input type="hidden" name="date">').val(reportDate));
        $('body').append($form);
        $form.trigger('submit');
        $form.remove();
    });

    $('#generateReportBtn').on('click', function () {
        const reportDate = $('#report_date').val();
        if (!reportDate) {
            toastr.error('Please select a date');
            return;
        }

        $('#reportLoader').removeClass('d-none');
        $('#reportContainer').empty();
        $('#printReportBtn').addClass('d-none');

        $.ajax({
            url: '<?= site_url('admin/student-daily-report/data') ?>',
            method: 'POST',
            data: {
                date: reportDate,
                [CSRF_NAME]: CSRF_HASH
            },
            success: function (html) {
                $('#reportContainer').html(html);
                lastPrintHtml = html;
                $('#printReportBtn').removeClass('d-none');
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

        const reportDate = $('#report_date').val();
        const formattedDate = reportDate
            ? new Date(reportDate + 'T00:00:00').toLocaleDateString('en-GB', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            })
            : '';

        const $reportHtml = $('<div>').html(lastPrintHtml);
        const tableHtml = $reportHtml.find('table').first().prop('outerHTML') || lastPrintHtml;
        const printableHtml = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Daily Report</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        html, body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; }
        .print-wrap { width: 100%; }
        .print-header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 12px; }
        .print-title { font-size: 20px; font-weight: 700; }
        .print-subtitle { font-size: 12px; color: #475569; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #374151 !important; padding: 6px 8px; vertical-align: top; }
        th { background: #e2e8f0 !important; text-align: left; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        td.text-center, th.text-center { text-align: center; }
        .section-name { font-weight: 700; white-space: nowrap; width: 22%; }
        .absent-names { text-align: left; line-height: 1.45; }
        .summary-row td { font-weight: 700; background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .text-muted { color: #64748b; }
        .text-success { color: #15803d; }
    </style>
</head>
<body>
    <div class="print-wrap">
        <div class="print-header">
            <div class="print-title">Student Daily Report</div>
            <div class="print-subtitle">Date: ${formattedDate}</div>
        </div>
        ${tableHtml}
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
