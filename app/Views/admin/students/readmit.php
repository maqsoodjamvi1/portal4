<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>


<div class="container-fluid px-3">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> Readmit Student</h5>
            <button type="button" class="btn btn-light btn-sm" onclick="window.print();">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
        
        <div class="card-body">
            <!-- Search Section -->
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-search me-2"></i> Search Dropped Students</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Search By:</label>
                                <div class="btn-group btn-group-toggle w-100" data-bs-toggle="buttons">
                                    <label class="btn btn-outline-primary active">
                                        <input type="radio" name="search_type" value="name" checked> <i class="fas fa-user me-1"></i> Student Name
                                    </label>
                                    <label class="btn btn-outline-primary">
                                        <input type="radio" name="search_type" value="father"> <i class="fas fa-user-friends me-1"></i> Father Name
                                    </label>
                                </div>
                            </div>
                            
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control form-control-lg" id="searchStudent" 
                                       placeholder="Type at least 3 characters..." autocomplete="off">
                                <button class="btn btn-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                            </div>
                            <div id="searchResults" class="mt-3" style="display: none;">
                                <div class="list-group" id="resultsList"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Readmission Form (Hidden initially) -->
            <div id="readmissionForm" style="display: none;" class="mt-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-user-check me-2"></i> Student Readmission Details</h6>
                            </div>
                            <div class="card-body">
                                <input type="hidden" id="student_id" name="student_id">
                                
                                <!-- Student Information Display -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text text-muted">Student Name</span>
                                                <span class="info-box-number" id="displayStudentName">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text text-muted">Registration No</span>
                                                <span class="info-box-number" id="displayRegNo">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text text-muted">Father Name</span>
                                                <span class="info-box-number" id="displayFatherName">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-light">
                                            <div class="info-box-content">
                                                <span class="info-box-text text-muted">Previous Class</span>
                                                <span class="info-box-number" id="displayPreviousClass">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="info-box bg-warning">
                                            <div class="info-box-content">
                                                <span class="info-box-text text-dark">Leaving Date</span>
                                                <span class="info-box-number" id="displayLeavingDate">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="info-box bg-warning">
                                            <div class="info-box-content">
                                                <span class="info-box-text text-dark">Leaving Reason</span>
                                                <span class="info-box-number" id="displayLeavingReason">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fee Payment History Section -->
<div class="mt-4" id="feeHistorySection" style="display: none;">
    <div class="card border-info">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-history me-2"></i> Fee Payment History
                <button type="button" class="btn btn-light btn-sm float-end" id="toggleFeeHistory">
                    <i class="fas fa-chevron-up"></i>
                </button>
            </h6>
        </div>
        <div class="card-body" id="feeHistoryContent">
            <div class="text-center py-3">
                <i class="fas fa-spinner fa-spin"></i> Loading payment history...
            </div>
        </div>
    </div>
</div>
                                
                                <!-- Readmission Details -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cls_sec_id">New Class Section <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="cls_sec_id" required>
                                                <option value="">-- Select Class Section --</option>
                                                <?php foreach ($sectionsclassinfo as $row): ?>
                                                    <option value="<?= $row['cls_sec_id'] ?>" data-class-id="<?= $row['class_id'] ?>">
                                                        <?= esc($row['sectionclassname']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="readmission_date">Readmission Date <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control datepicker" id="readmission_date" 
                                                   value="<?= date('d/m/Y') ?>" placeholder="dd/mm/yyyy">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Fee Management Section -->
                                <div class="mt-4">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Fee Management</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Outstanding Balance:</strong> Rs. <span id="outstandingBalance">0.00</span>
                                            </div>
                                            
                                            <button type="button" class="btn btn-sm btn-primary mb-3" id="addFeeRow">
                                                <i class="fas fa-plus me-1"></i> Add Fee
                                            </button>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm" id="feeTable">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Fee Type</th>
                                                            <th>Fee Month</th>
                                                            <th>Issue Date</th>
                                                            <th>Due Date</th>
                                                            <th>Amount (Rs.)</th>
                                                            <th>Discount (Rs.)</th>
                                                            <th>Payable (Rs.)</th>
                                                            <th width="50">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="feeRows">
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted">Click "Add Fee" to create fee entries</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <div class="row mt-3">
                                                <div class="col-md-6 offset-md-6">
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th>Total Amount:</th>
                                                            <td class="text-end">Rs. <span id="totalAmount">0.00</span></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Total Discount:</th>
                                                            <td class="text-end">Rs. <span id="totalDiscount">0.00</span></td>
                                                        </tr>
                                                        <tr class="table-success">
                                                            <th>Net Payable:</th>
                                                            <td class="text-end">Rs. <span id="netPayable">0.00</span></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="form-group text-center mt-4">
                                    <button type="button" class="btn btn-success btn-lg" id="btnReadmit">
                                        <i class="fas fa-save me-2"></i> Process Readmission
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-lg" id="btnCancel">
                                        <i class="fas fa-times me-2"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fee Row Template -->
<!-- Fee Row Template -->
<template id="feeRowTemplate">
    <tr>
        <td>
            <select class="form-control form-control-sm fee-type" required>
                <option value="">Select Fee Type</option>
                <?php foreach ($fee_types as $type): ?>
                    <option value="<?= $type->fee_type_id ?>" data-monthly="<?= $type->is_monthly_fee ?? 0 ?>">
                        <?= esc($type->fee_type_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="month" class="form-control form-control-sm fee-month" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm datepicker fee-issue-date" 
                   value="<?= date('d/m/Y') ?>" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm datepicker fee-due-date" 
                   value="<?= date('d/m/Y', strtotime('+10 days')) ?>" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm fee-amount" step="0.01" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm fee-discount" step="0.01" value="0">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm fee-payable" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-fee">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
let searchTimeout;
let selectedStudentId = null;
$(function() {
    // Initialize datepickers
    $('.datepicker').datepicker({ format: 'dd/mm/yyyy', autoclose: true });
    
    // Search timeout variable
    let searchTimeout;
    let selectedStudentId = null;
    
    // Helper function to escape HTML
    window.escapeHtml = function(str) {
        if (!str) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };
    
    // Search functionality
    $('#searchStudent').on('input', function() {
        const searchTerm = $(this).val().trim();
        const searchType = $('input[name="search_type"]:checked').val();
        
        clearTimeout(searchTimeout);
        
        if (searchTerm.length < 3) {
            $('#searchResults').hide();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '<?= site_url("admin/students/search_drop_students") ?>',
                method: 'POST',
                data: {
                    search: searchTerm,
                    search_type: searchType,
                    campus_id: '<?= $campus_id ?>',
                    session_id: '<?= $session_id ?>',
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                beforeSend: function() {
                    console.log('Searching for:', $('#searchStudent').val());
                    console.log('Search type:', $('input[name="search_type"]:checked').val());
                    $('#searchResults').show();
                    $('#resultsList').html('<div class="list-group-item text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');
                },
                success: function(res) {
                    console.log('Search response:', res);
                    
                    if (res.success && res.data && res.data.length > 0) {
                        let html = '';
                        res.data.forEach(function(student) {
                            // Debug: Check if class_display is present
                            console.log('Student:', student.student_name, 'Class:', student.class_display);
                            
                            html += `
                                <a href="javascript:void(0)" class="list-group-item list-group-item-action student-result" 
                                   data-student-id="${student.student_id}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2 flex-wrap">
                                                <strong class="me-2"><i class="fas fa-user-graduate me-1"></i> ${escapeHtml(student.student_name)}</strong>
                                                <span class="badge text-bg-info" style="font-size: 0.7rem;">
                                                    <i class="fas fa-chalkboard me-1"></i> ${escapeHtml(student.class_display || 'No class record')}
                                                </span>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-id-card fa-fw me-1"></i>Reg: ${escapeHtml(student.reg_no || 'N/A')}
                                                    </small>
                                                </div>
                                                <div class="col-md-4 col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-user-friends fa-fw me-1"></i>Father: ${escapeHtml(student.father_name || 'N/A')}
                                                    </small>
                                                </div>
                                                <div class="col-md-4 col-sm-12">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar-times fa-fw me-1"></i>Left: ${student.leaving_date || 'N/A'}
                                                    </small>
                                                </div>
                                            </div>
                                            ${student.leaving_reason && student.leaving_reason !== 'N/A' ? `
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-comment fa-fw me-1"></i>Reason: ${escapeHtml(student.leaving_reason)}
                                                </small>
                                            </div>
                                            ` : ''}
                                        </div>
                                        <i class="fas fa-chevron-right text-muted ms-2 mt-2"></i>
                                    </div>
                                </a>
                            `;
                        });
                        $('#resultsList').html(html);
                        
                        // Bind click events
                        $('.student-result').on('click', function() {
                            const studentId = $(this).data('student-id');
                            loadStudentForReadmission(studentId);
                        });
                    } else {
                        $('#resultsList').html('<div class="list-group-item text-center text-muted"><i class="fas fa-user-slash me-1"></i> No dropped students found</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Search error:', error);
                    $('#resultsList').html('<div class="list-group-item text-danger">Error searching students</div>');
                }
            });
        }, 500);
    });
    
    
    // Clear search
    $('#clearSearch').on('click', function() {
        $('#searchStudent').val('');
        $('#searchResults').hide();
        $('#readmissionForm').hide();
        selectedStudentId = null;
    });
    
    // Add fee row
   // Add fee row
$('#addFeeRow').on('click', function() {
    const $tbody = $('#feeRows');
    if ($tbody.find('tr').length === 1 && $tbody.find('tr td[colspan]').length) {
        $tbody.empty();
    }
    
    const $template = $('#feeRowTemplate').html();
    const $row = $($template);
    
    // Initialize datepickers for new row
    $row.find('.datepicker').datepicker({ format: 'dd/mm/yyyy', autoclose: true });
    
    // Set default fee month to current month
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    $row.find('.fee-month').val(year + '-' + month);
    
    // Calculate payable on amount/discount change
    $row.find('.fee-amount, .fee-discount').on('input', function() {
        const $tr = $(this).closest('tr');
        const amount = parseFloat($tr.find('.fee-amount').val()) || 0;
        const discount = parseFloat($tr.find('.fee-discount').val()) || 0;
        const payable = amount - discount;
        $tr.find('.fee-payable').val(payable.toFixed(2));
        updateTotals();
    });
    
    $tbody.append($row);
    updateTotals();
});
    
    // Remove fee row (delegated)
    $('#feeRows').on('click', '.remove-fee', function() {
        $(this).closest('tr').remove();
        if ($('#feeRows tr').length === 0) {
            $('#feeRows').html('<tr><td colspan="8" class="text-center text-muted">Click "Add Fee" to create fee entries</td></tr>');
        }
        updateTotals();
    });
    
    // Class selection - load fee amounts
    $('#cls_sec_id').on('change', function() {
        const clsSecId = $(this).val();
        if (!clsSecId) return;
        
        // Get class fee amounts for populating default values
        $.ajax({
            url: '<?= site_url("admin/ajax/get_class_fee_amounts") ?>',
            method: 'POST',
            data: {
                cls_sec_id: clsSecId,
                campus_id: '<?= $campus_id ?>',
                session_id: '<?= $session_id ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success' && res.data) {
                    // Pre-populate fee rows for monthly fees
                    $('#feeRows').empty();
                    let hasMonthly = false;
                    
                    res.data.forEach(function(fee) {
                        if (fee.is_monthly) {
                            hasMonthly = true;
                            addFeeRowWithData(fee.fee_type_id, fee.fee_type_title, fee.default_amount);
                        }
                    });
                    
                    if (!hasMonthly) {
                        $('#feeRows').html('<tr><td colspan="8" class="text-center text-muted">Click "Add Fee" to create fee entries</td></tr>');
                    }
                }
            }
        });
    });
    
    // Process readmission
  // Process readmission


// Process readmission button click
$('#btnReadmit').on('click', function() {
    const studentId = $('#student_id').val();
    const clsSecId = $('#cls_sec_id').val();
    const readmissionDate = $('#readmission_date').val();
    
    if (!studentId) {
        toastr.error('No student selected');
        return;
    }
    if (!clsSecId) {
        toastr.error('Please select a class section');
        return;
    }
    if (!readmissionDate) {
        toastr.error('Please select readmission date');
        return;
    }
    
    // Collect fee data from all rows
    const feeData = [];
    let rowCount = 0;
    
    $('#feeRows tr').each(function(index) {
        const $row = $(this);
        // Skip empty message row
        if ($row.find('td[colspan]').length) {
            console.log('Skipping empty message row');
            return;
        }
        
        rowCount++;
        const feeTypeId = $row.find('.fee-type').val();
        const feeMonth = $row.find('.fee-month').val();
        const amount = $row.find('.fee-amount').val();
        const discount = $row.find('.fee-discount').val() || 0;
        const issueDate = $row.find('.fee-issue-date').val();
        const dueDate = $row.find('.fee-due-date').val();
        
        console.log(`Row ${index}:`, {
            feeTypeId, feeMonth, amount, discount, issueDate, dueDate
        });
        
        if (!feeTypeId) {
            console.log(`Row ${index}: No fee type selected - skipping`);
            return;
        }
        
        if (!amount || parseFloat(amount) <= 0) {
            console.log(`Row ${index}: Invalid amount - skipping`);
            toastr.warning(`Row ${index + 1}: Please enter a valid amount`);
            return;
        }
        
        feeData.push({
            fee_type_id: parseInt(feeTypeId),
            fee_month: feeMonth,
            issue_date: issueDate,
            due_date: dueDate,
            amount: parseFloat(amount),
            discount: parseFloat(discount)
        });
    });
    
    console.log('Total rows found:', rowCount);
    console.log('Valid fee entries:', feeData.length);
    console.log('Fee Data to send:', JSON.stringify(feeData, null, 2));
    
    if (feeData.length === 0) {
        toastr.warning('Please add at least one valid fee entry');
        return;
    }
    
    const postData = (window.adminCsrfPayload || function (d) { return d; })({
        student_id: parseInt(studentId, 10),
        cls_sec_id: parseInt(clsSecId, 10),
        readmission_date: readmissionDate,
        fee_data: JSON.stringify(feeData)
    });

    console.log('Final request data:', JSON.stringify({
        student_id: postData.student_id,
        cls_sec_id: postData.cls_sec_id,
        readmission_date: postData.readmission_date,
        fee_data: feeData
    }, null, 2));

    $.ajax({
        url: '<?= site_url("admin/students/process_readmission") ?>',
        method: 'POST',
        dataType: 'json',
        data: postData,
        beforeSend: function() {
            $('#btnReadmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
        },
        success: function(res) {
            console.log('Response:', res);
            if (res.success) {
                toastr.success(res.msg);
                if (res.redirect) {
                    setTimeout(function() {
                        window.location.href = res.redirect;
                    }, 2000);
                }
            } else {
                toastr.error(res.msg);
                $('#btnReadmit').prop('disabled', false).html('<i class="fas fa-save me-2"></i> Process Readmission');
            }
        },
        error: function(xhr) {
            console.error('AJAX Error - Status:', xhr.status);
            console.error('AJAX Error - Response Text:', xhr.responseText);
            console.error('AJAX Error - Status Text:', xhr.statusText);
            
            let errorMsg = 'An error occurred';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.msg || response.message || errorMsg;
            } catch(e) {
                if (xhr.responseText && xhr.responseText.length < 500) {
                    errorMsg = xhr.responseText;
                } else {
                    errorMsg = xhr.statusText || errorMsg;
                }
            }
            toastr.error(errorMsg);
            $('#btnReadmit').prop('disabled', false).html('<i class="fas fa-save me-2"></i> Process Readmission');
        }
    });
});
    $('#btnCancel').on('click', function() {
        $('#readmissionForm').hide();
        $('#searchStudent').val('');
        $('#searchResults').hide();
        selectedStudentId = null;
    });
});

function loadStudentForReadmission(studentId) {
    $.ajax({
        url: '<?= site_url("admin/students/get_student_readmit_info") ?>',
        method: 'POST',
        data: {
            student_id: studentId,
            session_id: '<?= $session_id ?>',
            campus_id: '<?= $campus_id ?>',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        beforeSend: function() {
            $('#readmissionForm').show();
            $('#readmissionForm').addClass('loading-overlay');
        },
        success: function(res) {
            $('#readmissionForm').removeClass('loading-overlay');
            if (res.success) {
                selectedStudentId = studentId;
                $('#student_id').val(studentId);
                $('#displayStudentName').text(res.student.first_name + ' ' + (res.student.last_name || ''));
                $('#displayRegNo').text(res.student.reg_no || 'N/A');
                $('#displayFatherName').text(res.student.father_name || 'N/A');
                $('#displayPreviousClass').text(res.previous_class ? 
                    res.previous_class.class_name + ' - ' + res.previous_class.section_name : 'N/A');
                $('#displayLeavingDate').text(res.student.leaving_date ? 
                    new Date(res.student.leaving_date).toLocaleDateString('en-GB') : 'N/A');
                $('#displayLeavingReason').text(res.student.leaving_reason || 'N/A');
                $('#outstandingBalance').text(parseFloat(res.outstanding_balance).toFixed(2));
                loadFeeHistory(studentId);
                
                $('#readmissionForm').show();
                $('html, body').animate({
                    scrollTop: $('#readmissionForm').offset().top - 100
                }, 500);
            } else {
                toastr.error(res.msg);
            }
        },
        error: function() {
            $('#readmissionForm').removeClass('loading-overlay');
            toastr.error('Error loading student information');
        }
    });
}
function loadFeeHistory(studentId) {
    $('#feeHistoryContent').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading payment history...</div>');
    $('#feeHistorySection').show();
    
    $.ajax({
        url: '<?= site_url("admin/students/get_fee_history") ?>',
        method: 'POST',
        data: {
            student_id: studentId,
            campus_id: '<?= $campus_id ?>',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(res) {
            console.log('Fee history response:', res);
            if (res.success && res.fee_history && res.fee_history.length > 0) {
                displayFeeHistory(res);
            } else {
                $('#feeHistoryContent').html('<div class="alert alert-info">No fee payment history found for this student</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Fee history error:', error);
            $('#feeHistoryContent').html('<div class="alert alert-danger">Error loading payment history. Please try again.</div>');
        }
    });
}
function displayFeeHistory(data) {
    if (!data.fee_history || data.fee_history.length === 0) {
        $('#feeHistoryContent').html('<div class="alert alert-info">No fee payment history found for this student</div>');
        return;
    }
    
    let html = '';
    
    // Summary Cards (rounded, no currency symbol)
    let totalPaid = Math.round(data.total_paid || 0);
    let totalDiscount = Math.round(data.total_discount || 0);
    let totalPayments = data.total_payments || 0;
    
    html += `
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="info-box bg-success text-white">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Paid</span>
                        <span class="info-box-number">${totalPaid.toLocaleString()}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-warning text-dark">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Discount</span>
                        <span class="info-box-number">${totalDiscount.toLocaleString()}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-primary text-white">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Payments</span>
                        <span class="info-box-number">${totalPayments}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Fee History Table
    html += `<div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-bordered table-sm table-hover" id="feeHistoryTable">
            <thead class="table-light">
                <tr>
                    <th rowspan="2">Academic Year</th>
                    <th rowspan="2">Class</th>`;
    
    // Add month columns (January to December)
    for (let month in data.months) {
        html += `<th class="text-center">${data.months[month]}</th>`;
    }
    
    // Add non-monthly fee columns
    data.non_monthly_types.forEach(function(feeType) {
        html += `<th class="text-center">${escapeHtml(feeType)}</th>`;
    });
    
    html += `<th rowspan="2" class="text-center">Total</th>
                </tr>
                <tr>`;
    
    // Month sub-header (empty row)
    for (let month in data.months) {
        html += `<th class="text-center"></th>`;
    }
    data.non_monthly_types.forEach(function() {
        html += `<th class="text-center"></th>`;
    });
    
    html += `</tr></thead><tbody>`;
    
    // Add data rows
    let grandTotal = 0;
    data.fee_history.forEach(function(session) {
        let rowTotal = 0;
        html += `<tr>
            <td><strong>${escapeHtml(session.session_name)}</strong></td>
            <td>${escapeHtml(session.class_section)}</td>`;
        
        // Add monthly fee amounts (rounded)
        for (let monthNum in data.months) {
            let amount = Math.round(session.monthly_amounts[monthNum] || 0);
            rowTotal += amount;
            let displayClass = amount > 0 ? 'table-success' : '';
            html += `<td class="text-end ${displayClass}">
                        ${amount > 0 ? amount.toLocaleString() : '-'}
                      </td>`;
        }
        
        // Add non-monthly fee amounts (rounded)
        data.non_monthly_types.forEach(function(feeType) {
            let amount = Math.round(session.non_monthly[feeType] || 0);
            rowTotal += amount;
            let displayClass = amount > 0 ? 'table-info' : '';
            html += `<td class="text-end ${displayClass}">
                        ${amount > 0 ? amount.toLocaleString() : '-'}
                      </td>`;
        });
        
        html += `<td class="text-end fw-bold">${rowTotal.toLocaleString()}</td>`;
        html += `</tr>`;
        grandTotal += rowTotal;
    });
    
    // Add grand total row
    html += `<tr class="table-light fw-bold">
        <td colspan="2" class="text-end">GRAND TOTAL:</td>`;
    
    for (let monthNum in data.months) {
        html += `<td class="text-end"></td>`;
    }
    data.non_monthly_types.forEach(function() {
        html += `<td class="text-end"></td>`;
    });
    
    html += `<td class="text-end text-success">${grandTotal.toLocaleString()}</td>
        </table>`;
    
    html += `</tbody></table></div>`;
    
    $('#feeHistoryContent').html(html);
}
// Make sure toggle button works
$(document).on('click', '#toggleFeeHistory', function() {
    $('#feeHistoryContent').slideToggle();
    $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
});

// In the addFeeRowWithData function, ensure fee_month is in YYYY-MM format
function addFeeRowWithData(feeTypeId, feeTypeName, defaultAmount) {
    const $tbody = $('#feeRows');
    if ($tbody.find('tr').length === 1 && $tbody.find('tr td[colspan]').length) {
        $tbody.empty();
    }
    
    const $row = $($('#feeRowTemplate').html());
    $row.find('.fee-type').val(feeTypeId);
    $row.find('.fee-amount').val(defaultAmount);
    
    // Use current month
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    $row.find('.fee-month').val(year + '-' + month);
    
    // Add a unique identifier to help with debugging
    $row.attr('data-row-id', Date.now() + Math.random());
    
    $row.find('.fee-issue-date').val('<?= date("d/m/Y") ?>');
    $row.find('.fee-due-date').val('<?= date("d/m/Y", strtotime("+10 days")) ?>');
    
    $row.find('.fee-amount, .fee-discount').on('input', function() {
        const amount = parseFloat($(this).closest('tr').find('.fee-amount').val()) || 0;
        const discount = parseFloat($(this).closest('tr').find('.fee-discount').val()) || 0;
        const payable = amount - discount;
        $(this).closest('tr').find('.fee-payable').val(payable.toFixed(2));
        updateTotals();
    });
    
    $row.find('.fee-amount').trigger('input');
    $tbody.append($row);
    updateTotals();
}

function updateTotals() {
    let totalAmount = 0;
    let totalDiscount = 0;
    
    $('#feeRows .fee-amount').each(function() {
        totalAmount += parseFloat($(this).val()) || 0;
    });
    
    $('#feeRows .fee-discount').each(function() {
        totalDiscount += parseFloat($(this).val()) || 0;
    });
    
    const netPayable = totalAmount - totalDiscount;
    
    $('#totalAmount').text(totalAmount.toFixed(2));
    $('#totalDiscount').text(totalDiscount.toFixed(2));
    $('#netPayable').text(netPayable.toFixed(2));
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>

<style>

    /* Fee History Table Styles */
#feeHistoryContent {
    overflow-x: auto;
}

#feeHistoryTable {
    min-width: 800px;
    font-size: 12px;
}

#feeHistoryTable td, 
#feeHistoryTable th {
    white-space: nowrap;
    padding: 8px 10px;
}

#feeHistoryTable .table-success {
    background-color: #d4edda !important;
}

#feeHistoryTable .table-info {
    background-color: #d1ecf1 !important;
}

@media print {
    #feeHistoryTable {
        font-size: 10px;
    }
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_paginate {
        display: none !important;
    }
}
.loading-overlay {
    position: relative;
    opacity: 0.6;
    pointer-events: none;
}
.loading-overlay:after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.info-box {
    padding: 15px;
    border-radius: 5px;
}
.info-box-number {
    font-size: 18px;
    font-weight: bold;
    display: block;
}
.select2-container .select2-selection--single {
    height: 38px;
}
</style>

<?= $this->endSection() ?>