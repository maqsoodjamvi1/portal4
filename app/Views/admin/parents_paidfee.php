<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$status = '';
if (!empty($_GET['status'])) {
    $status = $_GET['status'];
}
?>
<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

<?= view('components/page_header', [
    'title' => 'Parents Paid Fee',
    'icon' => 'fas fa-hand-holding-usd',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Parents Paid Fee', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card sms-card card-primary card-outline">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-filter me-2"></i>Report Filters
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Class Selection -->
                        <div class="col-md-3 mb-3">
                            <?php
                                $clsOptions = [['value' => '', 'label' => 'All Classes']];
                                foreach ($sectionsclassinfo as $value) {
                                    $clsOptions[] = [
                                        'value' => $value['section_id'],
                                        'label' => $value['sectionclassname'],
                                    ];
                                }
                                echo view('components/report_filter_bar', [
                                    'formId' => 'paidFeeClassFilterForm',
                                    'title' => 'Class & Display Options',
                                    'fields' => [
                                        [
                                            'type' => 'select',
                                            'id' => 'cls_sec_id',
                                            'name' => 'cls_sec_id',
                                            'label' => 'Select Class',
                                            'class' => 'form-control select2 report-select2',
                                            'options' => $clsOptions,
                                            'col_class' => 'col-md-12 mb-2',
                                        ],
                                        [
                                            'type' => 'raw',
                                            'label' => 'Display Options',
                                            'col_class' => 'col-md-12 mb-2',
                                            'html' => '<div class="form-check form-switch form-switch-lg mb-2"><input type="checkbox" class="form-check-input" id="monthly_fee" checked><label class="form-check-label" for="monthly_fee">Show Monthly Balance</label></div>
                                            <div class="form-check form-switch form-switch-lg mb-2"><input type="checkbox" class="form-check-input" id="others_fee" checked><label class="form-check-label" for="others_fee">Show Other Balance</label></div>
                                            <div class="form-check form-switch form-switch-lg mb-2"><input type="checkbox" class="form-check-input" id="show_projected" checked><label class="form-check-label" for="show_projected">Projected Fees</label></div>
                                            <div class="form-check form-switch form-switch-lg"><input type="checkbox" class="form-check-input" id="show_balance" checked><label class="form-check-label" for="show_balance">Show Total Balance</label></div>',
                                        ],
                                    ],
                                    'actions' => [],
                                ]);
                            ?>
                        </div>

                        <!-- Month Selection -->
                        <div class="col-md-5 mb-3">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Select Months</h3>
                                    <div class="card-tools">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-success" id="select-all-months">
                                                <i class="fas fa-check-circle me-1"></i>All
                                            </button>
                                            <button type="button" class="btn btn-danger" id="deselect-all-months">
                                                <i class="fas fa-times-circle me-1"></i>None
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php for ($i = 0; $i < 12; $i++) {
                                            $month = date('Y-m', strtotime("-$i months"));
                                            $month_display = date('M y', strtotime($month)); ?>
                                            <div class="col-md-3 col-4 mb-2">
                                                <div class="form-check form-check">
                                                    <input class="form-check-input month-checkbox" type="checkbox" 
                                                        name="months[]" value="<?= $month ?>" id="month_<?= $i ?>" checked>
                                                    <label class="form-check-label" for="month_<?= $i ?>">
                                                        <?= $month_display ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters and Actions -->
                        <div class="col-md-4 mb-3">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">Data Filters</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="form-check form-switch form-switch-lg mb-2">
                                           <input type="checkbox" class="form-check-input" id="include_monthly_paid" checked>
                                          <label class="form-check-label" for="include_monthly_paid">Include Monthly Paid</label>
                                            </div>
                                            <div class="form-check form-switch form-switch-lg mb-2">
                                                <input type="checkbox" class="form-check-input" id="include_others_paid" checked>
                                                <label class="form-check-label" for="include_others_paid">Include Other Paid</label>
                                            </div>
                                            <div class="form-check form-switch form-switch-lg mb-2">
                                            <input type="checkbox" class="form-check-input" id="hide_zero">
                                            <label class="form-check-label" for="hide_zero">Hide Zero Balances</label>
                                        </div>
                                        <div class="form-check form-switch form-switch-lg mb-2">
                                            <input type="checkbox" class="form-check-input" id="monthly_fee_defaulter">
                                            <label class="form-check-label" for="monthly_fee_defaulter">Monthly Fee Defaulters</label>
                                        </div>
                                        <div class="form-check form-switch form-switch-lg mb-2">
                                            <input type="checkbox" class="form-check-input" id="other_fee_defaulter">
                                            <label class="form-check-label" for="other_fee_defaulter">Other Fee Defaulters</label>
                                        </div>
                                        

                                        <div class="form-check form-switch form-switch-lg">
                                        <input type="checkbox" class="form-check-input" id="show_grand_total" checked>
                                        <label class="form-check-label" for="show_grand_total">Show Grand Total</label>
                                    </div>
                                        <div class="form-check form-switch form-switch-lg">
                                            <input type="checkbox" class="form-check-input" id="show_family_head">
                                            <label class="form-check-label" for="show_family_head">Family Heads Only</label>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex flex-column gap-2 mt-4">
                                        <button class="btn btn-success btn-lg w-100" id="viewresult">
                                            <i class="fas fa-chart-bar me-2"></i>Generate Report
                                        </button>
                                        <div id="printOptionsWrap" style="display: none;">
                                            <label class="d-block text-muted small mb-1">Print layout</label>
                                            <div class="btn-group btn-group-toggle w-100 mb-2" data-bs-toggle="buttons">
                                                <label class="btn btn-outline-secondary active">
                                                    <input type="radio" name="print_orientation" id="print_orientation_portrait" value="portrait" checked> Portrait
                                                </label>
                                                <label class="btn btn-outline-secondary">
                                                    <input type="radio" name="print_orientation" id="print_orientation_landscape" value="landscape"> Landscape
                                                </label>
                                            </div>
                                            <button class="btn btn-primary btn-lg w-100" id="printBtn">
                                                <i class="fas fa-print me-2"></i>Print Report
                                            </button>
                                        </div>
                                        <button class="btn btn-info btn-lg w-100" id="exportBtn" style="display: none;">
                                            <i class="fas fa-file-excel me-2"></i>Export to Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selected Filters Display -->
                            <div class="card card-outline card-secondary mt-3" id="appliedFiltersCard" style="display: none;">
                                <div class="card-header">
                                    <h3 class="card-title">Applied Filters</h3>
                                </div>
                                <div class="card-body p-2" id="appliedFilters">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results Section -->
                    <div class="card-body position-relative">
                        <div id="loader-1" class="loader" style="display: none;"></div>
                        <div id="studentsList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.loader {
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

.table-responsive {
    overflow-x: auto;
}

.total-row td {
    font-weight: bold;
    background-color: #f8f9fa!important;
}

/* Improved table styling */
.fee-table {
    width: 100%;
    border-collapse: collapse;
}

.fee-table thead th {
    background-color: #4e73df;
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
}

.fee-table tbody tr:nth-child(even) {
    background-color: #f8f9fc;
}

.fee-table tbody tr:hover {
    background-color: #f1f3f9;
}

.fee-table td, .fee-table th {
    padding: 8px 12px;
    border: 1px solid #e3e6f0;
    text-align: center;
}

.fee-table .text-start {
    text-align: left;
}

.fee-table .text-end {
    text-align: right;
}

.fee-table .negative-balance {
    color: #e74a3b;
    font-weight: bold;
}

.fee-table .positive-balance {
    color: #1cc88a;
    font-weight: bold;
}

.filter-badge {
    margin-right: 5px;
    margin-bottom: 5px;
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

.report-preview-filters {
    margin-top: 10px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    text-align: left;
}

.report-preview-filter-item {
    display: inline-block;
    margin-right: 14px;
    margin-bottom: 4px;
    font-size: 12px;
    color: #334155;
}
</style>

<script>
$(function() {
    // Initialize select2
    $('.select2').select2({
        theme: 'bootstrap-5'
    });

    let lastPrintData = null;

    // Month selection
    $('#select-all-months').click(() => {
        $('.month-checkbox').prop('checked', true).trigger('change');
        toastr.success('All months selected');
    });
    
    $('#deselect-all-months').click(() => {
        $('.month-checkbox').prop('checked', false).trigger('change');
        toastr.info('All months deselected');
    });

    // Generate Report
    $('#viewresult').click(function() {
        $("#loader-1").show();
        const params = {
             cls_sec_id: $('#cls_sec_id').val(),
        months: $('.month-checkbox:checked').map((i, el) => el.value).get(),
        show_projected: $('#show_projected').is(':checked') ? 1 : 0,
        show_balance: $('#show_balance').is(':checked') ? 1 : 0,
        hide_zero: $('#hide_zero').is(':checked') ? 1 : 0,
        show_family_head: $('#show_family_head').is(':checked') ? 1 : 0,
        monthly_fee: $('#monthly_fee').is(':checked') ? 1 : 0,
        others_fee: $('#others_fee').is(':checked') ? 1 : 0,
        include_monthly_paid: $('#include_monthly_paid').is(':checked') ? 1 : 0,
        include_others_paid: $('#include_others_paid').is(':checked') ? 1 : 0,
        monthly_fee_defaulter: $('#monthly_fee_defaulter').is(':checked') ? 1 : 0,
        other_fee_defaulter: $('#other_fee_defaulter').is(':checked') ? 1 : 0,
        show_grand_total: $('#show_grand_total').is(':checked') ? 1 : 0
        };

        // Update applied filters display
        updateAppliedFilters(params);

        $.ajax({
            url: '<?= site_url('admin/parents_paidfee/data'); ?>',
            method: 'POST',
            data: params,
            success: function(res) {
                lastPrintData = createPrintContent(res, params);
                $('#studentsList').html(buildScreenPreview(lastPrintData));
                $('#printOptionsWrap, #exportBtn').show();
                $("#loader-1").hide();
            },
            error: function(xhr) {
                $('#studentsList').html('<div class="alert alert-danger">Error loading data</div>');
                $("#loader-1").hide();
            }
        });
    });

    // Update applied filters display
    function updateAppliedFilters(params) {
        const filtersContainer = $('#appliedFilters');
        filtersContainer.empty();
        
        // Class filter
        if(params.cls_sec_id) {
            const className = $('#cls_sec_id option:selected').text();
            filtersContainer.append(`
                <span class="badge text-bg-primary filter-badge">
                    <i class="fas fa-graduation-cap me-1"></i> Class: ${className}
                </span>
            `);
        } else {
            filtersContainer.append(`
                <span class="badge text-bg-primary filter-badge">
                    <i class="fas fa-graduation-cap me-1"></i> All Classes
                </span>
            `);
        }
        
        // Months filter
        if(params.months.length > 0) {
            const monthCount = params.months.length;
            const monthText = monthCount === 12 ? 'All months' : `${monthCount} month(s) selected`;
            filtersContainer.append(`
                <span class="badge text-bg-info filter-badge">
                    <i class="fas fa-calendar-alt me-1"></i> ${monthText}
                </span>
            `);
        }
        
        // Other filters
        if(params.show_projected) {
            filtersContainer.append(`
                <span class="badge text-bg-secondary filter-badge">
                    <i class="fas fa-chart-line me-1"></i> Projected Fees
                </span>
            `);
        }
        
        if(params.show_balance) {
            filtersContainer.append(`
                <span class="badge text-bg-secondary filter-badge">
                    <i class="fas fa-money-bill-wave me-1"></i> Outstanding Balances
                </span>
            `);
        }
        
        if(params.hide_zero) {
            filtersContainer.append(`
                <span class="badge text-bg-warning filter-badge">
                    <i class="fas fa-eye-slash me-1"></i> Hide Zero Balances
                </span>
            `);
        }
        
        if(params.show_family_head) {
            filtersContainer.append(`
                <span class="badge text-bg-warning filter-badge">
                    <i class="fas fa-users me-1"></i> Family Heads Only
                </span>
            `);
        }
        
        if(params.monthly_fee_defaulter) {
            filtersContainer.append(`
                <span class="badge text-bg-danger filter-badge">
                    <i class="fas fa-exclamation-triangle me-1"></i> Monthly Fee Defaulters
                </span>
            `);
        }
        if(params.other_fee_defaulter) {
            filtersContainer.append(`
                <span class="badge text-bg-danger filter-badge">
                    <i class="fas fa-exclamation-triangle me-1"></i> Other Fee Defaulters
                </span>
            `);
        }
        
        $('#appliedFiltersCard').show();
    }

    // Create print content with filters information
    function createPrintContent(res, params) {
        const now = new Date();
        const generatedDate = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const className = params.cls_sec_id ? $('#cls_sec_id option:selected').text() : 'All Classes';
        let filtersText = '';

        filtersText += `<div class="print-filter-item"><strong>Class:</strong> ${className}</div>`;

        const monthCount = params.months.length;
        const monthText = monthCount === 12 ? 'All months' : `${monthCount} month(s) selected`;
        filtersText += `<div class="print-filter-item"><strong>Months:</strong> ${monthText}</div>`;

        if (params.show_projected) {
            filtersText += `<div class="print-filter-item"><strong>Projected Fees:</strong> Yes</div>`;
        }
        if (params.show_balance) {
            filtersText += `<div class="print-filter-item"><strong>Outstanding Balances:</strong> Yes</div>`;
        }
        if (params.hide_zero) {
            filtersText += `<div class="print-filter-item"><strong>Hide Zero Balances:</strong> Yes</div>`;
        }
        if (params.show_family_head) {
            filtersText += `<div class="print-filter-item"><strong>Family Heads Only:</strong> Yes</div>`;
        }
        if (params.monthly_fee_defaulter) {
            filtersText += `<div class="print-filter-item"><strong>Monthly Fee Defaulters:</strong> Yes</div>`;
        }
        if (params.other_fee_defaulter) {
            filtersText += `<div class="print-filter-item"><strong>Other Fee Defaulters:</strong> Yes</div>`;
        }
        if (params.include_monthly_paid) {
            filtersText += `<div class="print-filter-item"><strong>Monthly Paid:</strong> Included</div>`;
        }
        if (params.include_others_paid) {
            filtersText += `<div class="print-filter-item"><strong>Other Paid:</strong> Included</div>`;
        }

        const $res = $('<div>').html(res);
        let tableHtml = $res.find('table.paid-fee-table').first().prop('outerHTML') || $res.find('table').first().prop('outerHTML') || '';
        if (!tableHtml) {
            tableHtml = '<div class="alert alert-warning">No printable table found.</div>';
        }

        let processedHtml = tableHtml;
        if (!params.show_grand_total) {
            processedHtml = processedHtml.replace(/<tr class="total-row.*?<\/tr>/g, '');
        } else {
            processedHtml = fixSumRowAlignment(processedHtml);
        }
        processedHtml = compactPrintableTable(processedHtml);

        return {
            generatedDate: generatedDate,
            filtersText: filtersText,
            tableHtml: processedHtml
        };
    }

    function buildScreenPreview(printData) {
        return `
            <div id="printArea">
                <div class="report-preview-header">
                    <div class="report-preview-title">Parents Paid Fee Report</div>
                    <div class="report-preview-subtitle">Generated: ${printData.generatedDate}</div>
                    <div class="report-preview-filters">${printData.filtersText}</div>
                </div>
                <div class="table-responsive">${printData.tableHtml}</div>
            </div>
        `;
    }

    function compactPrintableTable(html) {
        const $temp = $('<div>').html(html);
        const $rows = $temp.find('table tbody tr').not('.total-row');
        $rows.each(function() {
            const $td = $(this).find('td').eq(2);
            if (!$td.length) return;

            const parentName = $.trim($td.clone().children().remove().end().text()).replace(/\s+/g, ' ');
            const studentsText = $.trim($td.find('small').text());
            const students = studentsText
                ? studentsText.split(',').map(function(s) { return $.trim(s); }).filter(Boolean)
                : [];

            let secondLine = '-';
            if (students.length > 0) {
                const preview = students.slice(0, 2).join(', ');
                const more = students.length - 2;
                secondLine = preview + (more > 0 ? (' +' + more + ' more') : '');
            }

            const esc = function(v) { return $('<div/>').text(v || '').html(); };
            $td.html(
                '<div class="p-parent">' + esc(parentName || '-') + '</div>' +
                '<div class="p-students">' + esc(secondLine) + '</div>'
            );
        });
        return $temp.html();
    }

    function getPrintLayoutStyles(isLandscape, columnCount) {
        if (isLandscape) {
            return {
                pageMargin: '10mm',
                pageWidth: '270mm',
                tableFont: '10.5px',
                cellPadding: '4px 5px',
                parentColWidth: '22%',
                parentFont: '10px',
                studentsFont: '9px',
                headerTitleSize: '20px',
                filterFont: '11px'
            };
        }

        let tableFont = '10.5px';
        let cellPadding = '4px 5px';
        let parentColWidth = '22%';
        let parentFont = '10px';
        let studentsFont = '9px';
        let pageMargin = '10mm';
        let headerTitleSize = '20px';
        let filterFont = '11px';

        if (columnCount > 14) {
            tableFont = '6.5px';
            cellPadding = '2px 2px';
            parentColWidth = '15%';
            parentFont = '7px';
            studentsFont = '6px';
            pageMargin = '5mm';
            headerTitleSize = '15px';
            filterFont = '9px';
        } else if (columnCount > 11) {
            tableFont = '7.5px';
            cellPadding = '2px 3px';
            parentColWidth = '17%';
            parentFont = '8px';
            studentsFont = '7px';
            pageMargin = '6mm';
            headerTitleSize = '16px';
            filterFont = '9px';
        } else if (columnCount > 9) {
            tableFont = '8.5px';
            cellPadding = '3px 3px';
            parentColWidth = '19%';
            parentFont = '9px';
            studentsFont = '8px';
            pageMargin = '8mm';
            headerTitleSize = '18px';
            filterFont = '10px';
        }

        return {
            pageMargin: pageMargin,
            pageWidth: '190mm',
            tableFont: tableFont,
            cellPadding: cellPadding,
            parentColWidth: parentColWidth,
            parentFont: parentFont,
            studentsFont: studentsFont,
            headerTitleSize: headerTitleSize,
            filterFont: filterFont
        };
    }

    function buildPrintableDocument(printData, isLandscape, columnCount) {
        const orientation = isLandscape ? 'landscape' : 'portrait';
        const layout = getPrintLayoutStyles(isLandscape, columnCount || 0);
        const layoutHint = isLandscape
            ? 'A4 landscape'
            : 'A4 portrait — use Minimum margins in the print dialog for wide reports';

        return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Parents Paid Fee Report</title>
    <style>
        @page { size: A4 ${orientation}; margin: ${layout.pageMargin}; }
        html, body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; }
        .print-wrap { width: ${layout.pageWidth}; max-width: 100%; margin: 0 auto; }
        .print-header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 10px; }
        .print-title { font-size: ${layout.headerTitleSize}; font-weight: 700; letter-spacing: .2px; }
        .print-subtitle { font-size: 12px; color: #475569; margin-top: 3px; }
        .print-hint { font-size: 10px; color: #64748b; margin-top: 4px; }
        .print-filters { margin: 8px 0 12px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; }
        .print-filter-item { margin-right: 12px; margin-bottom: 6px; display: inline-block; font-size: ${layout.filterFont}; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: ${layout.tableFont}; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #374151 !important; padding: ${layout.cellPadding}; vertical-align: top; text-align: center; word-wrap: break-word; overflow-wrap: anywhere; }
        th { background: #e2e8f0 !important; color: #0f172a; font-weight: 700; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        td small { color: #475569; font-size: ${layout.studentsFont}; }
        th:nth-child(1), td:nth-child(1) { width: 4%; }
        th:nth-child(2), td:nth-child(2) { width: 6%; }
        th:nth-child(3), td:nth-child(3) { width: ${layout.parentColWidth}; text-align: left !important; }
        .p-parent { font-weight: 600; font-size: ${layout.parentFont}; line-height: 1.2; margin-bottom: 1px; }
        .p-students { font-size: ${layout.studentsFont}; line-height: 1.15; color: #475569; }
        .text-start { text-align: left !important; }
        .text-end { text-align: right !important; }
        .total-row td { font-weight: 700; background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
<body>
    <div class="print-wrap">
        <div class="print-header">
            <div class="print-title">Parents Paid Fee Report</div>
            <div class="print-subtitle">Generated: ${printData.generatedDate}</div>
            <div class="print-hint">${layoutHint}</div>
        </div>
        <div class="print-filters">${printData.filtersText}</div>
        <div>${printData.tableHtml}</div>
    </div>
</body>
</html>`;
    }

    // Fix sum row alignment
    function fixSumRowAlignment(html) {
        const $temp = $('<div>').html(html);
        const $table = $temp.find('table');
        
        // Calculate colspan for sum row
        const headers = $table.find('thead th').length;
        const sumColumns = $table.find('.total-row td').length;
        const colspan = headers - sumColumns + 1;
        
        // Update sum row cells
        $table.find('.total-row td:first').attr('colspan', colspan)
            .nextAll().each(function(index) {
                $(this).attr('data-column', index + colspan);
            });
        
        return $temp.html();
    }

    // Print handling
    $('#printBtn').click(function() {
        if (!lastPrintData || !lastPrintData.tableHtml || lastPrintData.tableHtml.indexOf('<table') === -1) {
            toastr.error('Generate the report first before printing');
            return;
        }

        const $tmp = $('<div>').html(lastPrintData.tableHtml);
        const headerCount = $tmp.find('thead th').length;
        const isLandscape = $('input[name="print_orientation"]:checked').val() === 'landscape';
        const printableHtml = buildPrintableDocument(lastPrintData, isLandscape, headerCount);
        const w = window.open('', '_blank');
        if (!w) {
            toastr.error('Popup blocked. Please allow popups for printing.');
            return;
        }
        w.document.open();
        w.document.write(printableHtml);
        w.document.close();
        w.focus();
        setTimeout(function() {
            w.print();
        }, 250);
    });
    
    // Export to Excel (basic implementation)
    $('#exportBtn').click(function() {
        let table = $('#studentsList').find('table.paid-fee-table').first().clone();
        if (!table.length) {
            table = $('#studentsList').find('table').first().clone();
        }
        if (!table.length) {
            toastr.error('No table found to export');
            return;
        }
        
        // Remove any unwanted elements
        table.find('.no-export').remove();
        
        // Create CSV content
        let csv = [];
        
        // Add headers
        let headers = [];
        table.find('thead th').each(function() {
            headers.push($(this).text().trim());
        });
        csv.push(headers.join(','));
        
        // Add rows
        table.find('tbody tr').each(function() {
            let row = [];
            $(this).find('td').each(function() {
                row.push($(this).text().trim());
            });
            csv.push(row.join(','));
        });
        
        // Create download link
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'fee_report_' + new Date().toISOString().slice(0, 10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>

<?= $this->endSection() ?>