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
    'title' => 'Parents Balance Fee',
    'icon' => 'fas fa-balance-scale-left',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Parents Balance Fee', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card sms-card card-primary card-outline" id="balanceFeeFilterCard">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-filter me-2"></i>Report Filters
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" id="filterCollapseBtn" title="Hide/Show filters">
                            <i class="fas fa-minus" id="filterCollapseIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="filterOptionsWrap">
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
                                    'formId' => 'balanceFeeClassFilterForm',
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
                                            <button type="button" class="btn btn-outline-primary month-preset" data-count="3">3M</button>
                                            <button type="button" class="btn btn-outline-primary month-preset" data-count="6">6M</button>
                                            <button type="button" class="btn btn-outline-primary month-preset" data-count="12">12M</button>
                                            <button type="button" class="btn btn-success" id="select-all-months">
                                                <i class="fas fa-check-circle me-1"></i>All
                                            </button>
                                            <button type="button" class="btn btn-danger" id="deselect-all-months">
                                                <i class="fas fa-times-circle me-1"></i>None
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body compact-months-wrap">
                                    <div class="row">
                                        <?php for ($i = 0; $i < 12; $i++) {
                                            $month = date('Y-m', strtotime("-$i months"));
                                            $month_display = date('M y', strtotime($month)); ?>
                                            <div class="col-lg-2 col-md-3 col-4 mb-2">
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
                                    <div class="form-group filter-switch-grid">
                                        <label class="mb-1">Search Family</label>
                                        <input type="text" class="form-control form-control-sm mb-2" id="family_search" placeholder="Search by parent name, family ID, student name...">
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
                                        <div class="btn-group btn-group-sm mt-2" id="reportViewToggle" style="display:none;">
                                            <button type="button" class="btn btn-outline-primary active" id="showCardViewBtn">
                                                <i class="fas fa-th-large me-1"></i>Card View
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" id="showTableViewBtn">
                                                <i class="fas fa-table me-1"></i>Table View
                                            </button>
                                        </div>
                                        <button class="btn btn-primary btn-lg w-100" id="printBtn" style="display: none;">
                                            <i class="fas fa-print me-2"></i>Print Report
                                        </button>
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
.compact-months-wrap{
    max-height: 180px;
    overflow-y: auto;
}
.filter-switch-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 10px;
}
.filter-switch-grid > label,
.filter-switch-grid > input{
    grid-column: 1 / -1;
}
.filter-switch-grid .form-check{
    min-height: 26px;
    margin-bottom: 4px !important;
}
.card-header, .card-body{
    padding-top: .65rem;
    padding-bottom: .65rem;
}
@media (max-width: 992px){
    .filter-switch-grid{
        grid-template-columns: 1fr;
    }
    .compact-months-wrap{
        max-height: 220px;
    }
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


.update-fee-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    white-space: nowrap;
}

/* Make the button full width on small screens */
@media (max-width: 576px) {
    .update-fee-btn {
        width: 100%;
    }
}

/* Loading spinner for button */
.fa-spinner {
    margin-right: 0.3rem;
}


.fee-table td, .fee-table th {
    padding: 8px 12px;
    border: 1px solid #e3e6f0;
    text-align: center;
}

.balance-report-wrap .report-summary-cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(170px,1fr));
    gap:10px;
}
.balance-report-wrap .summary-card{
    border:1px solid #e5e7eb;
    border-radius:10px;
    background:#fff;
    padding:10px 12px;
}
.balance-report-wrap .summary-card .k{font-size:12px;color:#64748b;}
.balance-report-wrap .summary-card .v{font-size:20px;font-weight:700;color:#0f172a;}
.balance-card-grid{
    margin-top:10px;
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(360px,1fr));
    gap:12px;
}
#balanceCardView, #balanceTableView{margin-top:10px;}
.balance-card{
    border:1px solid #e2e8f0;
    border-radius:10px;
    background:#fff;
    padding:10px;
}
.balance-card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;}
.balance-metrics{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;}
.metric-chip{
    font-size:11px;
    background:#eef2ff;
    color:#1e3a8a;
    border:1px solid #c7d2fe;
    padding:2px 8px;
    border-radius:999px;
}
.month-chip-wrap{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:8px;
}
.month-chip{
    border:1px solid #e2e8f0;
    border-radius:8px;
    padding:6px;
    text-align:center;
    background:#fafafa;
}
.month-chip .month-title{font-size:11px;color:#334155;}
.month-chip .month-amount{font-weight:700;font-size:12px;}
.month-chip.chip-due{border-color:#f59e0b;background:#fffbeb;}
.month-chip.chip-clear{border-color:#86efac;background:#f0fdf4;}
.btn-sm{padding:.2rem .35rem;font-size:.7rem;line-height:1.2;}
@media (max-width: 768px){
    .balance-card-grid{grid-template-columns:1fr;}
    .month-chip-wrap{grid-template-columns:repeat(3,minmax(0,1fr));}
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

@media print {
    body * {
        visibility: hidden;
    }
    #printArea, #printArea * {
        visibility: visible;
    }
    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        font-size: 12px;
    }
    .print-header {
        text-align: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #000;
    }
    .print-title {
        font-size: 18px;
        font-weight: bold;
    }
    .print-subtitle {
        font-size: 14px;
        color: #666;
    }
    .print-filters {
        margin: 10px 0;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }
    .print-filter-item {
        margin-right: 15px;
        display: inline-block;
    }
    table {
        width: 100%!important;
    }
    .total-row td {
        background-color: #f8f9fa!important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .no-print {
        display: none!important;
    }
}
</style>

<script>
$(function() {
    const balanceViewStorageKey = 'parents_balancefee_view_mode';
    
    function setFilterCollapsed(collapsed) {
        if (collapsed) {
            $('#filterOptionsWrap').slideUp(150);
            $('#filterCollapseIcon').removeClass('fa-minus').addClass('fa-plus');
        } else {
            $('#filterOptionsWrap').slideDown(150);
            $('#filterCollapseIcon').removeClass('fa-plus').addClass('fa-minus');
        }
    }
    // Initialize select2
    $('.select2').select2({
        theme: 'bootstrap-5'
    });
    
    $('#filterCollapseBtn').on('click', function() {
        const isVisible = $('#filterOptionsWrap').is(':visible');
        setFilterCollapsed(isVisible);
    });

    // Month selection
    $('#select-all-months').click(() => {
        $('.month-checkbox').prop('checked', true).trigger('change');
        toastr.success('All months selected');
    });
    
    $('#deselect-all-months').click(() => {
        $('.month-checkbox').prop('checked', false).trigger('change');
        toastr.info('All months deselected');
    });
    
    $('.month-preset').click(function() {
        const count = parseInt($(this).data('count'), 10);
        $('.month-checkbox').prop('checked', false);
        $('.month-checkbox').slice(0, count).prop('checked', true);
        toastr.info('Selected last ' + count + ' months');
    });


       

                // Add this to your existing JavaScript
            $(document).on('click', '.update-fee-btn', function() {
                const $btn = $(this);
                const parentId = $btn.data('parent');
                const month = $btn.data('month');
                const balance = $btn.data('balance');
                
                // Determine action based on button class
                const action = $btn.hasClass('btn-success') ? 'pay' : 'unpay';
                
                const actionText = action === 'pay' ? 'mark as paid' : 'revert to unpaid';
                const confirmText = `Are you sure you want to ${actionText} ${balance} for this parent in ${month}?`;
                
                if (!confirm(confirmText)) {
                    return;
                }
                
                // Save original button HTML
                const originalHtml = $btn.html();
                let okResponse = false;
                
                // Set loading state
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Processing');
                
                $.ajax({
                    
                    url: "<?= site_url('admin/parents_balancefee/update_fee_status'); ?>",
                    method: 'POST',
                    dataType: 'json', // Ensure response is parsed as JSON
                    data: {
                        parent_id: parentId,
                        month: month,
                        action: action
                    },
                    success: function(response) {
                        if (response.success) {
                            okResponse = true;
                            toastr.success(response.message);
                            
                            // Toggle button state and appearance
                            if (action === 'pay') {
                                $btn.removeClass('btn-success').addClass('btn-warning')
                                    .html('<i class="fas fa-undo me-1"></i> Undo')
                                    .data('action', 'unpay');
                            } else {
                                $btn.removeClass('btn-warning').addClass('btn-success')
                                    .html('<i class="fas fa-check-circle me-1"></i> Mark Paid')
                                    .data('action', 'pay');
                            }
                            
                            // Optional: Refresh the table data
                            // $('#viewresult').click();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        toastr.error('Error updating fee status. Please try again.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        if (!okResponse) {
                            $btn.html(originalHtml);
                        }
                    }
                });
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
            
               url: "<?= site_url('admin/parents_balancefee/data'); ?>",
            method: 'POST',
            data: params,
            success: function(res) {
                // Render full interactive report (card + table wrappers)
                $('#studentsList').html(res);
                applyFamilySearchFilter();
                $('#printBtn, #exportBtn, #reportViewToggle').show();
                const savedMode = localStorage.getItem(balanceViewStorageKey) || 'card';
                setReportView(savedMode);
                // Reduce post-filter blank space by collapsing filters after loading report
                setFilterCollapsed(true);
                $("#loader-1").hide();
                
                // Apply table styling
                $('table').addClass('fee-table');
            },
            error: function(xhr) {
                $('#studentsList').html('<div class="alert alert-danger">Error loading data</div>');
                $('#reportViewToggle').hide();
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
        // Get current date and time
        const now = new Date();
        const generatedDate = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Get class name
        const className = params.cls_sec_id ? $('#cls_sec_id option:selected').text() : 'All Classes';
        
        // Prepare filters text
        let filtersText = '';
        
        // Class filter
        filtersText += `<div class="print-filter-item"><strong>Class:</strong> ${className}</div>`;
        
        // Months filter
        const monthCount = params.months.length;
        const monthText = monthCount === 12 ? 'All months' : `${monthCount} month(s) selected`;
        filtersText += `<div class="print-filter-item"><strong>Months:</strong> ${monthText}</div>`;
        
        // Other filters
        if(params.show_projected) {
            filtersText += `<div class="print-filter-item"><strong>Projected Fees:</strong> Yes</div>`;
        }
        
        if(params.show_balance) {
            filtersText += `<div class="print-filter-item"><strong>Outstanding Balances:</strong> Yes</div>`;
        }
        
        if(params.hide_zero) {
            filtersText += `<div class="print-filter-item"><strong>Hide Zero Balances:</strong> Yes</div>`;
        }
        
        if(params.show_family_head) {
            filtersText += `<div class="print-filter-item"><strong>Family Heads Only:</strong> Yes</div>`;
        }
        
        if(params.monthly_fee_defaulter) {
            filtersText += `<div class="print-filter-item"><strong>Monthly Fee Only:</strong> Yes</div>`;
        }

         if(params.other_fee_defaulter) {
            filtersText += `<div class="print-filter-item"><strong>Other Fee Only:</strong> Yes</div>`;
        }
        
        // Extract detailed table from returned report HTML for print
        const $res = $('<div>').html(res);
        let tableHtml = $res.find('table.balance-table').first().prop('outerHTML') || '';
        if (!tableHtml) {
            tableHtml = '<div class="alert alert-warning">No printable table found.</div>';
        }
        let processedHtml = tableHtml;
        if(!params.show_grand_total) {
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

    // Make parent/students column print-friendly: 2 compact lines.
    function compactPrintableTable(html) {
        const $temp = $('<div>').html(html);
        const $rows = $temp.find('table tbody tr').not('.total-row');
        $rows.each(function() {
            const $td = $(this).find('td').eq(2); // Parent/Students column
            if (!$td.length) return;

            const parentName = $.trim($td.clone().children().remove().end().text()).replace(/\s+/g, ' ');
            const studentsText = $.trim($td.find('small').text());
            const students = studentsText
                ? studentsText.split(',').map(function(s){ return $.trim(s); }).filter(Boolean)
                : [];

            let secondLine = '-';
            if (students.length > 0) {
                const preview = students.slice(0, 2).join(', ');
                const more = students.length - 2;
                secondLine = preview + (more > 0 ? (' +' + more + ' more') : '');
            }

            const esc = function(v){ return $('<div/>').text(v || '').html(); };
            $td.html(
                '<div class="p-parent">' + esc(parentName || '-') + '</div>' +
                '<div class="p-students">' + esc(secondLine) + '</div>'
            );
        });
        return $temp.html();
    }

    function buildPrintableDocument(printData, isLandscape) {
        const orientation = isLandscape ? 'landscape' : 'portrait';
        const pageWidth = isLandscape ? '270mm' : '190mm';
        return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Summary Report</title>
    <style>
        @page { size: A4 ${orientation}; margin: 10mm; }
        html, body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; }
        .print-wrap { width: ${pageWidth}; margin: 0 auto; }
        .print-header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 10px; }
        .print-title { font-size: 20px; font-weight: 700; letter-spacing: .2px; }
        .print-subtitle { font-size: 12px; color: #475569; margin-top: 3px; }
        .print-filters { margin: 8px 0 12px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; }
        .print-filter-item { margin-right: 12px; margin-bottom: 6px; display: inline-block; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 10.5px; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #d1d5db; padding: 4px 5px; vertical-align: top; text-align: center; word-wrap: break-word; }
        th { background: #e2e8f0; color: #0f172a; font-weight: 700; }
        td small { color: #475569; font-size: 9px; }
        th:nth-child(3), td:nth-child(3) { width: 22%; text-align: left !important; }
        .p-parent { font-weight: 600; font-size: 10px; line-height: 1.25; margin-bottom: 2px; }
        .p-students { font-size: 9px; line-height: 1.2; color: #475569; }
        .text-start { text-align: left !important; }
        .text-end { text-align: right !important; }
        .total-row td { font-weight: 700; background: #f1f5f9 !important; }
        .update-fee-btn, .btn { display: none !important; }
    </style>
</head>
<body>
    <div class="print-wrap">
        <div class="print-header">
            <div class="print-title">Fee Summary Report</div>
            <div class="print-subtitle">Generated: ${printData.generatedDate}</div>
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
        const liveReportHtml = $('#studentsList').html() || '';
        const printData = createPrintContent(liveReportHtml, params);
        if (!printData.tableHtml || printData.tableHtml.indexOf('<table') === -1) {
            toastr.error('No printable table found');
            return;
        }
        const $tmp = $('<div>').html(printData.tableHtml);
        const headerCount = $tmp.find('thead th').length;
        const isLandscape = headerCount > 9;
        const printableHtml = buildPrintableDocument(printData, isLandscape);
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
        // Create a simple Excel export from detailed table
        let table = $('#studentsList').find('table.balance-table').first().clone();
        if (!table.length) {
            toastr.error('No detailed table found to export');
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
    
    function setReportView(mode) {
        const normalized = mode === 'table' ? 'table' : 'card';
        const isCard = normalized === 'card';
        $('#balanceCardView').toggle(isCard);
        $('#balanceTableView').toggle(!isCard);
        $('#showCardViewBtn').toggleClass('active', isCard);
        $('#showTableViewBtn').toggleClass('active', !isCard);
        localStorage.setItem(balanceViewStorageKey, normalized);
    }
    
    $('#showCardViewBtn').on('click', function() { setReportView('card'); });
    $('#showTableViewBtn').on('click', function() { setReportView('table'); });
    
    function applyFamilySearchFilter() {
        const q = ($('#family_search').val() || '').trim().toLowerCase();
        if (!q) {
            $('.balance-card').show();
            $('.balance-table tbody tr').show();
            return;
        }
        $('.balance-card').each(function() {
            const t = ($(this).attr('data-search') || '').toLowerCase();
            $(this).toggle(t.indexOf(q) !== -1);
        });
        $('.balance-table tbody tr').each(function() {
            const t = ($(this).text() || '').toLowerCase();
            if ($(this).hasClass('total-row')) return;
            $(this).toggle(t.indexOf(q) !== -1);
        });
    }
    
    $('#family_search').on('input', applyFamilySearchFilter);
});
</script>

<?= $this->endSection() ?>