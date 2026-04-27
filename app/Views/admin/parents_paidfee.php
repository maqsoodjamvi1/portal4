<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
$status = ''; 
if(!empty($_GET['status'])){
    $status = $_GET['status']; 
}
?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Fee Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#/">Dashboard</a></li>
                    <li class="breadcrumb-item active">Fee Report</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary card-outline">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-filter mr-2"></i>Report Filters
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
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Select Class</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                    </div>
                                    <select class="form-control select2" id="cls_sec_id" style="width: 100%;">
                                        <option value="">All Classes</option>
                                        <?php foreach ($sectionsclassinfo as $value) { ?>
                                            <option value="<?= $value['section_id'] ?>"><?= $value['sectionclassname'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Display Options Card -->
                            <div class="card card-outline card-info mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">Display Options</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                          <div class="custom-control custom-switch custom-switch-lg mb-2">
                                            <input type="checkbox" class="custom-control-input" id="monthly_fee" checked>
                                            <label class="custom-control-label" for="monthly_fee">Show Monthly Balance</label>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-lg mb-2">
                                            <input type="checkbox" class="custom-control-input" id="others_fee" checked>
                                            <label class="custom-control-label" for="others_fee">Show Other Balance</label>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-lg mb-2">
                                            <input type="checkbox" class="custom-control-input" id="show_projected" checked>
                                            <label class="custom-control-label" for="show_projected">Projected Fees</label>
                                        </div>
                                      
                                          <div class="custom-control custom-switch custom-switch-lg">
                                            <input type="checkbox" class="custom-control-input" id="show_balance" checked>
                                            <label class="custom-control-label" for="show_balance">Show Total Balance</label>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Month Selection -->
                        <div class="col-md-5 mb-3">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Select Months</h3>
                                    <div class="card-tools">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-success" id="select-all-months">
                                                <i class="fas fa-check-circle mr-1"></i>All
                                            </button>
                                            <button type="button" class="btn btn-danger" id="deselect-all-months">
                                                <i class="fas fa-times-circle mr-1"></i>None
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
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input month-checkbox" type="checkbox" 
                                                        name="months[]" value="<?= $month ?>" id="month_<?= $i ?>" checked>
                                                    <label class="custom-control-label" for="month_<?= $i ?>">
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
                                        <div class="custom-control custom-switch custom-switch-lg mb-2">
                                           <input type="checkbox" class="custom-control-input" id="include_monthly_paid" checked>
                                          <label class="custom-control-label" for="include_monthly_paid">Include Monthly Paid</label>
                                            </div>
                                            <div class="custom-control custom-switch custom-switch-lg mb-2">
                                                <input type="checkbox" class="custom-control-input" id="include_others_paid" checked>
                                                <label class="custom-control-label" for="include_others_paid">Include Other Paid</label>
                                            </div>
                                            <div class="custom-control custom-switch custom-switch-lg mb-2">
                                            <input type="checkbox" class="custom-control-input" id="hide_zero">
                                            <label class="custom-control-label" for="hide_zero">Hide Zero Balances</label>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-lg mb-2">
                                            <input type="checkbox" class="custom-control-input" id="monthly_fee_defaulter">
                                            <label class="custom-control-label" for="monthly_fee_defaulter">Monthly Fee Defaulters</label>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-lg mb-2">
                                            <input type="checkbox" class="custom-control-input" id="other_fee_defaulter">
                                            <label class="custom-control-label" for="other_fee_defaulter">Other Fee Defaulters</label>
                                        </div>
                                        

                                        <div class="custom-control custom-switch custom-switch-lg">
                                        <input type="checkbox" class="custom-control-input" id="show_grand_total" checked>
                                        <label class="custom-control-label" for="show_grand_total">Show Grand Total</label>
                                    </div>
                                        <div class="custom-control custom-switch custom-switch-lg">
                                            <input type="checkbox" class="custom-control-input" id="show_family_head">
                                            <label class="custom-control-label" for="show_family_head">Family Heads Only</label>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex flex-column gap-2 mt-4">
                                        <button class="btn btn-success btn-lg btn-block" id="viewresult">
                                            <i class="fas fa-chart-bar mr-2"></i>Generate Report
                                        </button>
                                        <button class="btn btn-primary btn-lg btn-block" id="printBtn" style="display: none;">
                                            <i class="fas fa-print mr-2"></i>Print Report
                                        </button>
                                        <button class="btn btn-info btn-lg btn-block" id="exportBtn" style="display: none;">
                                            <i class="fas fa-file-excel mr-2"></i>Export to Excel
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

.fee-table .text-left {
    text-align: left;
}

.fee-table .text-right {
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
    // Initialize select2
    $('.select2').select2({
        theme: 'bootstrap4'
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
                const printContent = createPrintContent(res, params);
                $('#studentsList').html(printContent);
                $('#printBtn, #exportBtn').show();
                $("#loader-1").hide();
                
                // Apply table styling
                $('table').addClass('fee-table');
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
                <span class="badge badge-primary filter-badge">
                    <i class="fas fa-graduation-cap mr-1"></i> Class: ${className}
                </span>
            `);
        } else {
            filtersContainer.append(`
                <span class="badge badge-primary filter-badge">
                    <i class="fas fa-graduation-cap mr-1"></i> All Classes
                </span>
            `);
        }
        
        // Months filter
        if(params.months.length > 0) {
            const monthCount = params.months.length;
            const monthText = monthCount === 12 ? 'All months' : `${monthCount} month(s) selected`;
            filtersContainer.append(`
                <span class="badge badge-info filter-badge">
                    <i class="fas fa-calendar-alt mr-1"></i> ${monthText}
                </span>
            `);
        }
        
        // Other filters
        if(params.show_projected) {
            filtersContainer.append(`
                <span class="badge badge-secondary filter-badge">
                    <i class="fas fa-chart-line mr-1"></i> Projected Fees
                </span>
            `);
        }
        
        if(params.show_balance) {
            filtersContainer.append(`
                <span class="badge badge-secondary filter-badge">
                    <i class="fas fa-money-bill-wave mr-1"></i> Outstanding Balances
                </span>
            `);
        }
        
        if(params.hide_zero) {
            filtersContainer.append(`
                <span class="badge badge-warning filter-badge">
                    <i class="fas fa-eye-slash mr-1"></i> Hide Zero Balances
                </span>
            `);
        }
        
        if(params.show_family_head) {
            filtersContainer.append(`
                <span class="badge badge-warning filter-badge">
                    <i class="fas fa-users mr-1"></i> Family Heads Only
                </span>
            `);
        }
        
        if(params.monthly_fee_defaulter) {
            filtersContainer.append(`
                <span class="badge badge-danger filter-badge">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Monthly Fee Defaulters
                </span>
            `);
        }
        if(params.other_fee_defaulter) {
            filtersContainer.append(`
                <span class="badge badge-danger filter-badge">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Other Fee Defaulters
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
        
        // Show/hide grand total based on checkbox
        let processedHtml = res;
        if(!params.show_grand_total) {
            processedHtml = res.replace(/<tr class="total-row.*?<\/tr>/g, '');
        } else {
            processedHtml = fixSumRowAlignment(res);
        }
        
        return `
            <div id="printArea">
                <div class="print-header">
                    <div class="print-title">Fee Summary Report</div>
                    <div class="print-subtitle">Generated: ${generatedDate}</div>
                    <div class="print-filters">
                        ${filtersText}
                    </div>
                </div>
                <div class="table-responsive">${processedHtml}</div>
            </div>
        `;
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
        const printContent = $('#printArea').clone();
        $('body').addClass('printing').html(printContent);
        window.print();
        location.reload();
    });
    
    // Export to Excel (basic implementation)
    $('#exportBtn').click(function() {
        // Create a simple Excel export
        let table = $('#printArea table').clone();
        
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