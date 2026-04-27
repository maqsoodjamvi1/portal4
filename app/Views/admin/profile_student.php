<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if (!isset($student_id)) {
    echo "<div class='alert alert-danger m-5'>No Student ID Provided</div>";
    return;
}
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-graduate"></i> Student Profile</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Student Profile</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link active" href="#profile" data-toggle="tab">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#fee" data-toggle="tab">Fee</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#result" data-toggle="tab">Result</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#attendance" data-toggle="tab">Attendance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#health" data-toggle="tab">
                                <i class="fas fa-heartbeat mr-1"></i> Health & BMI
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#chalan" data-toggle="tab">
                                <i class="fas fa-file-invoice mr-1"></i> Chalan Generator
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" target="_blank" href="<?= base_url('admin/leaving-certificate/edit?id='.$student_id) ?>">Certificate</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile">
                            <div id="studentInfo">
                                <div class="text-center p-5">
                                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading profile data...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="fee">
                            <div id="feeInfo">
                                <div class="text-center p-5">
                                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading fee data...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="result">
                            <div id="resultInfo">
                                <div class="text-center p-5">
                                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading result data...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane" id="attendance">
                            <div id="attendanceInfo">
                                <div class="text-center p-5">
                                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading attendance data...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Health & BMI Tab -->
                        <div class="tab-pane" id="health">
                            <div id="healthInfo">
                                <div class="text-center p-5">
                                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading health data...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Chalan Tab Pane -->
                        <div class="tab-pane" id="chalan">
                            <!-- Chalan Filter Options -->
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-sliders-h mr-2"></i>Fee Challan Options
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form id="chalanOptionsForm">
                                        <input type="hidden" name="student_id" id="student_id" value="<?= $student_id ?>">
                                        
                                        <!-- View Type Selection -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><i class="fas fa-layer-group mr-1"></i>View Type</label>
                                                    <select class="form-control select2" name="view_type" id="view_type">
                                                        <option value="student_three_copy">Student Wise - 3 Copies (Bank, School, Student)</option>
                                                        <option value="student_single_page">Student Wise - Single Page (3 Students per Page)</option>
                                                        <option value="family_three_copy">Family Wise - 3 Copies per Student</option>
                                                        <option value="family_single_page">Family Wise - All Students on One Page</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><i class="fas fa-calendar-alt mr-1"></i>Fee Month</label>
                                                    <input type="month" class="form-control" name="fee_month" id="fee_month" 
                                                           value="<?= date('Y-m') ?>">
                                                    <small class="text-muted">Leave empty for all unpaid</small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><i class="fas fa-percentage mr-1"></i>Discount Column</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               name="show_discount" id="show_discount" value="yes" checked>
                                                        <label class="custom-control-label" for="show_discount">Show Discount Column</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Additional Options Row -->
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><i class="fas fa-history mr-1"></i>Payment History</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               name="show_payment_history" id="show_payment_history" value="1" checked>
                                                        <label class="custom-control-label" for="show_payment_history">Show Payment History (12 months)</label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><i class="fas fa-exclamation-triangle mr-1"></i>Display Fine</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               name="fine_after_due_date" id="fine_after_due_date" value="1">
                                                        <label class="custom-control-label" for="fine_after_due_date">Show Late Fee Calculation</label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><i class="fas fa-envelope mr-1"></i>Message Position</label>
                                                    <select class="form-control" name="message_position" id="message_position">
                                                        <option value="header">Header</option>
                                                        <option value="footer">Footer</option>
                                                        <option value="none">Don't Show</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Message Text -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><i class="fas fa-comment mr-1"></i>Message Text</label>
                                                    <textarea class="form-control" name="message_text" id="message_text" 
                                                              rows="2" maxlength="200" placeholder="Enter custom message..."></textarea>
                                                    <small class="text-muted float-right"><span id="charCount">0</span>/200 characters</small>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-primary" onclick="loadChalanData()">
                                        <i class="fas fa-file-invoice mr-2"></i> Generate Challan
                                    </button>
                                    <button type="button" class="btn btn-success" id="printChalanBtn" onclick="printChalan()" style="display: none;">
                                        <i class="fas fa-print mr-2"></i> Print Challan
                                    </button>
                                    <button type="button" class="btn btn-info" id="downloadChalanBtn" onclick="downloadChalan()" style="display: none;">
                                        <i class="fas fa-download mr-2"></i> Download PDF
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="resetChalanOptions()">
                                        <i class="fas fa-undo mr-2"></i> Reset Options
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Challan Display Area -->
                            <div class="card card-primary card-outline mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-file-pdf mr-2"></i>Fee Challan Preview
                                    </h3>
                                </div>
                                <div class="card-body p-0">
                                    <div id="chalanInfo" class="chalan-container">
                                        <div class="text-center p-5">
                                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Select options and click Generate Challan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BMI Measurement Modal -->
<div class="modal fade" id="bmiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-heartbeat mr-2"></i> Record Height & Weight
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="bmiForm">
                <?= csrf_field() ?>
                <input type="hidden" name="student_id" id="bmi_student_id" value="<?= $student_id ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Height (cm) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control" name="height" id="height" required>
                                <small class="text-muted">e.g., 145.5 cm</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Weight (kg) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control" name="weight" id="weight" required>
                                <small class="text-muted">e.g., 35.5 kg</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="Any additional notes about this measurement..."></textarea>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        BMI will be automatically calculated using the formula: weight (kg) / (height in meters)²
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save & Calculate BMI</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fee Challan Modal -->
<div class="modal fade" id="feeChalanModal" tabindex="-1" role="dialog" aria-labelledby="feeChalanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="feeChalanModalLabel">
                    <i class="fas fa-file-invoice mr-2"></i> Fee Challan - <span id="modalStudentName"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="chalanLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted">Loading fee challan...</p>
                </div>
                <div id="chalanContent" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printModalChalan()">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
                <a href="#" id="downloadChalanBtn" class="btn btn-success" target="_blank">
                    <i class="fas fa-download mr-2"></i>Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// BMI Calculation Helper
function calculateBMI(height, weight) {
    if (height <= 0 || weight <= 0) return null;
    const heightInMeters = height / 100;
    const bmi = weight / (heightInMeters * heightInMeters);
    return Math.round(bmi * 100) / 100;
}

function getBMICategory(bmi, age = null) {
    if (!bmi) return null;
    if (age && age < 18) {
        // Pediatric BMI categories (simplified)
        if (bmi < 15) return 'underweight';
        if (bmi < 19) return 'normal';
        if (bmi < 23) return 'overweight';
        return 'obese';
    }
    // Adult categories
    if (bmi < 18.5) return 'underweight';
    if (bmi < 25) return 'normal';
    if (bmi < 30) return 'overweight';
    return 'obese';
}

function getCategoryColor(category) {
    const colors = {
        'underweight': '#3498db',
        'normal': '#2ecc71',
        'overweight': '#f39c12',
        'obese': '#e74c3c'
    };
    return colors[category] || '#95a5a6';
}

function getCategoryEmoji(category) {
    const emojis = {
        'underweight': '⚠️',
        'normal': '✅',
        'overweight': '📈',
        'obese': '⚠️⚠️'
    };
    return emojis[category] || '📊';
}

$(document).ready(function() {
    // Load profile data on page load
    loadProfileData();
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%'
    });
    
    // Character counter for message text
    $('#message_text').on('input', function() {
        const count = $(this).val().length;
        $('#charCount').text(count);
    });
    
    // BMI Form Submission
    $('#bmiForm').on('submit', function(e) {
        e.preventDefault();
        
        const height = parseFloat($('#height').val());
        const weight = parseFloat($('#weight').val());
        
        if (!height || !weight) {
            toastr.error('Please enter both height and weight');
            return;
        }
        
        const bmi = calculateBMI(height, weight);
        
        $.ajax({
            url: '<?= base_url("admin/students/update-bmi") ?>',
            type: 'POST',
            data: {
                student_id: $('#bmi_student_id').val(),
                height: height,
                weight: weight,
                bmi: bmi,
                notes: $('textarea[name="notes"]').val(),
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            beforeSend: function() {
                $('#bmiModal .btn-primary').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'BMI recorded successfully');
                    $('#bmiModal').modal('hide');
                    loadHealthData();
                } else {
                    toastr.error(response.message || 'Failed to save BMI data');
                }
            },
            error: function() {
                toastr.error('Error saving BMI data');
            },
            complete: function() {
                $('#bmiModal .btn-primary').prop('disabled', false).html('Save & Calculate BMI');
            }
        });
    });
    
    // Tab click handlers
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr('href');
        
        if (target == '#profile') {
            loadProfileData();
        } else if (target == '#fee') {
            loadFeeData();
        } else if (target == '#result') {
            loadResultData();
        } else if (target == '#attendance') {
            loadAttendanceData();
        } else if (target == '#health') {
            loadHealthData();
        }
    });
});

function loadProfileData() {
    $.ajax({
        url: '<?= base_url("admin/profile-student/data") ?>',
        type: "POST",
        data: { student_id: <?= $student_id ?> },
        success: function(res) {
            $("#studentInfo").html(res);
        },
        error: function() {
            $("#studentInfo").html('<div class="alert alert-danger">Error loading profile data</div>');
        }
    });
}

function loadFeeData() {
    $.ajax({
        url: '<?= base_url("admin/profile-student/student-fee-data") ?>',
        type: "POST",
        data: { student_id: <?= $student_id ?> },
        success: function(res) {
            $("#feeInfo").html(res);
            initializeFeeButtons();
        },
        error: function() {
            $("#feeInfo").html('<div class="alert alert-danger">Error loading fee data</div>');
        }
    });
}

function loadHealthData() {
    $.ajax({
        url: '<?= base_url("admin/profile-student/student-health-data") ?>',
        type: "POST",
        data: { student_id: <?= $student_id ?> },
        success: function(res) {
            $("#healthInfo").html(res);
            initializeHealthButtons();
        },
        error: function() {
            $("#healthInfo").html('<div class="alert alert-danger">Error loading health data</div>');
        }
    });
}

function initializeHealthButtons() {
    $('#recordBmiBtn').click(function() {
        $('#bmiModal').modal('show');
    });
}

function initializeFeeButtons() {
    $('.view-chalan-btn').click(function() {
        const chalanId = $(this).data('chalan-id');
        const studentName = $(this).data('student-name');
        loadFeeChalan(chalanId, studentName);
    });
    
    $('#generateChalanBtn').click(function() {
        generateNewChalan();
    });
}

function loadFeeChalan(chalanId, studentName) {
    $('#modalStudentName').text(studentName);
    $('#chalanContent').hide();
    $('#chalanLoading').show();
    $('#feeChalanModal').modal('show');
    
    $.ajax({
        url: '<?= base_url("admin/fee-chalan/get-chalan-details") ?>',
        type: 'POST',
        data: { 
            chalan_id: chalanId,
            student_id: <?= $student_id ?>
        },
        dataType: 'json',
        success: function(response) {
            $('#chalanLoading').hide();
            
            if (response.success) {
                displayChalan(response.chalanData);
                $('#downloadChalanBtn').attr('href', '<?= base_url("admin/fee-chalan/download") ?>?id=' + chalanId);
            } else {
                $('#chalanContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        ${response.message || 'Failed to load challan details'}
                    </div>
                `).show();
            }
        },
        error: function(xhr) {
            $('#chalanLoading').hide();
            $('#chalanContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Error loading challan. Please try again.
                </div>
            `).show();
            console.error(xhr.responseText);
        }
    });
}

function displayChalan(data) {
    let html = `
        <div class="chalan-container">
            <div class="text-center mb-4">
                <h3 class="mb-0">${data.school_name || 'TIME Elementary School'}</h3>
                <p class="mb-0">${data.campus_name || 'Shakrial Campus'}</p>
                <p class="mb-0">${data.bank_details || 'JS Bank, Khana Pull'}</p>
                <p class="mb-0">Account No: ${data.account_no || '9601000000925308'}</p>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Ch. No:</strong> ${data.chalan_no || data.id || ''}</p>
                    <p><strong>Reg:</strong> ${data.reg_no || data.registration || ''}</p>
                    <p><strong>Family ID:</strong> ${data.family_id || ''}</p>
                </div>
                <div class="col-md-6 text-right">
                    <p><strong>Issue Date:</strong> ${data.issue_date || data.created_date || ''}</p>
                    <p><strong>Due Date:</strong> ${data.due_date || ''}</p>
                    <p><strong>Fee Month:</strong> ${data.fee_month || ''}</p>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4>${data.student_name || ''}</h4>
                    <p><strong>Father Name:</strong> ${data.father_name || ''}</p>
                    <p><strong>Class:</strong> ${data.class_name || ''} ${data.section || ''}</p>
                </div>
            </div>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Particulars</th>
                        <th class="text-right">Amount</th>
                        ${data.show_discount ? '<th class="text-right">Discount</th>' : ''}
                        <th class="text-right">Payable</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    if (data.items && data.items.length > 0) {
        data.items.forEach(item => {
            html += `
                <tr>
                    <td>${item.description || 'Fee'}</td>
                    <td class="text-right">${formatCurrency(item.amount)}</td>
                    ${data.show_discount ? `<td class="text-right">${formatCurrency(item.discount || 0)}</td>` : ''}
                    <td class="text-right">${formatCurrency(item.payable || item.amount)}</td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td>Fee Amount</td>
                <td class="text-right">${formatCurrency(data.amount || 0)}</td>
                ${data.show_discount ? '<td class="text-right">' + formatCurrency(data.discount || 0) + '</td>' : ''}
                <td class="text-right">${formatCurrency(data.payable || data.amount || 0)}</td>
            </tr>
        `;
    }
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold">
                        <td>Total Payable</td>
                        <td class="text-right">${formatCurrency(data.total_amount || data.amount || 0)}</td>
                        ${data.show_discount ? '<td class="text-right">' + formatCurrency(data.total_discount || 0) + '</td>' : ''}
                        <td class="text-right">${formatCurrency(data.total_payable || data.payable || data.amount || 0)}</td>
                    </tr>
                </tfoot>
            </table>
            
            ${data.show_payment_history ? displayPaymentHistory(data.payment_history) : ''}
            
            <div class="mt-4 text-center text-muted">
                <p class="mb-0"><small>If any mistakes are found in the challan, please contact the Accounts Office.</small></p>
            </div>
        </div>
    `;
    
    $('#chalanContent').html(html).show();
}

function displayPaymentHistory(history) {
    if (!history || history.length === 0) return '';
    
    let html = `
        <div class="payment-history-section mt-4">
            <h6 class="font-weight-bold">PAYMENT HISTORY (Last 6 Months)</h6>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Month</th>
    `;
    
    history.months.forEach(month => {
        html += `<th class="text-center">${month}</th>`;
    });
    
    html += `<th class="text-center">Total</th></tr></thead><tbody>`;
    
    history.students.forEach(student => {
        html += `<tr><td>${student.name}</td>`;
        student.payments.forEach(payment => {
            html += `<td class="text-center">${payment || '-'}</td>`;
        });
        html += `<td class="text-center font-weight-bold">${student.total}</td></tr>`;
    });
    
    html += `</tbody></table></div>`;
    return html;
}

function generateNewChalan() {
    if (!confirm('Generate new fee challan for this student?')) return;
    
    $.ajax({
        url: '<?= base_url("admin/fee-chalan/generate-single") ?>',
        type: 'POST',
        data: { 
            student_id: <?= $student_id ?>,
            fee_month: prompt('Enter fee month (MM/YYYY):', new Date().toLocaleDateString('en-US', { month: '2-digit', year: 'numeric' }))
        },
        dataType: 'json',
        beforeSend: function() {
            $('#generateChalanBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Generating...');
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Chalan generated successfully');
                loadFeeData();
            } else {
                toastr.error(response.message || 'Failed to generate chalan');
            }
        },
        error: function() {
            toastr.error('Error generating chalan');
        },
        complete: function() {
            $('#generateChalanBtn').prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Generate New Challan');
        }
    });
}

function printModalChalan() {
    const printContent = document.getElementById('chalanContent').innerHTML;
    const originalBody = document.body.innerHTML;
    
    document.body.innerHTML = `
        <html>
            <head>
                <title>Fee Challan</title>
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
                <style>
                    body { padding: 20px; }
                    .table { width: 100%; margin-bottom: 1rem; }
                    .text-right { text-align: right; }
                    .font-weight-bold { font-weight: bold; }
                </style>
            </head>
            <body>${printContent}</body>
        </html>
    `;
    
    window.print();
    document.body.innerHTML = originalBody;
    location.reload();
}

function formatCurrency(amount) {
    if (!amount) return '0';
    return new Intl.NumberFormat('en-PK', { 
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + '/-';
}

function loadAttendanceData() {
    $.ajax({
        url: '<?= base_url("admin/profile-student/student-attendance-data") ?>',
        type: "POST",
        data: { student_id: <?= $student_id ?> },
        success: function(res) {
            $("#attendanceInfo").html(res);
        },
        error: function() {
            $("#attendanceInfo").html('<div class="alert alert-danger">Error loading attendance data</div>');
        }
    });
}

function loadResultData() {
    $("#loader-1").css("display", "block");
    var academic_result = [];
    var student_id = <?= $student_id ?>;
    
    $.ajax({
        url: '<?= base_url("admin/student-results/data") ?>',
        type: "POST",
        data: { academic_result: academic_result, student_id: student_id },
        success: function(res) {
            $("#resultInfo").html(res);
            $("#loader-1").css("display", "none");
        },
        error: function() {
            $("#resultInfo").html('<div class="alert alert-danger">Error loading result data</div>');
            $("#loader-1").css("display", "none");
        }
    });
}

function loadChalanData() {
    const studentId = <?= $student_id ?>;
    const formData = $('#chalanOptionsForm').serialize();
    
    $('#chalanInfo').html(`
        <div class="text-center p-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Generating challan...</p>
        </div>
    `);
    
    let url = '<?= base_url("admin/fee-chalan/generate") ?>?' + formData + 
              '&selected_student_id=' + studentId + 
              '&search=' + studentId;
    
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            $('#chalanInfo').html(response);
            $('#printChalanBtn, #downloadChalanBtn').show();
            
            if (typeof initializeChalanView === 'function') {
                initializeChalanView();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading challan:', error);
            $('#chalanInfo').html(`
                <div class="alert alert-danger m-3">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Error loading fee challan. Please try again.
                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="loadChalanData()">
                            <i class="fas fa-sync mr-1"></i> Retry
                        </button>
                    </div>
                </div>
            `);
        }
    });
}

function printChalan() {
    const printContent = document.getElementById('chalanInfo').innerHTML;
    const originalBody = document.body.innerHTML;
    
    document.body.innerHTML = `
        <html>
            <head>
                <title>Fee Challan</title>
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
                <style>
                    body { padding: 20px; background: white; }
                    .slip-row {
                        display: flex;
                        justify-content: space-between;
                        width: 100%;
                        margin-bottom: 20px;
                        gap: 15px;
                        page-break-inside: avoid;
                    }
                    .slip-col {
                        width: 32%;
                        flex: 1;
                        page-break-inside: avoid;
                    }
                    .no-print, .btn, .edit-chalan-btn, .modal {
                        display: none !important;
                    }
                    @media print {
                        @page {
                            size: A4 landscape;
                            margin: 0.5in;
                        }
                    }
                </style>
            </head>
            <body>${printContent}</body>
        </html>
    `;
    
    window.print();
    document.body.innerHTML = originalBody;
    location.reload();
}

function downloadChalan() {
    const studentId = <?= $student_id ?>;
    const formData = $('#chalanOptionsForm').serialize();
    
    const url = '<?= base_url("admin/fee-chalan/generate") ?>?' + formData + 
                '&selected_student_id=' + studentId + 
                '&search=' + studentId;
    
    window.open(url, '_blank');
}

function resetChalanOptions() {
    $('#view_type').val('student_three_copy').trigger('change');
    $('#fee_month').val('<?= date('Y-m') ?>');
    $('#show_discount').prop('checked', true);
    $('#show_payment_history').prop('checked', true);
    $('#fine_after_due_date').prop('checked', false);
    $('#message_position').val('header');
    $('#message_text').val('');
    $('#charCount').text('0');
    
    $('#chalanInfo').html(`
        <div class="text-center p-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Select options and click Generate Challan</p>
        </div>
    `);
    $('#printChalanBtn, #downloadChalanBtn').hide();
}
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    
    #chalanInfo, #chalanInfo * {
        visibility: visible;
    }
    
    #chalanInfo {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    .no-print, 
    .main-header, 
    .main-sidebar, 
    .content-header, 
    .breadcrumb,
    .card-header,
    .nav-tabs,
    .btn,
    .edit-chalan-btn,
    .modal {
        display: none !important;
    }
    
    .content-wrapper, 
    .content, 
    .card-body {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    @page {
        size: A4 landscape;
        margin: 0.5cm;
    }
}

.chalan-container {
    font-family: 'Arial', sans-serif;
    padding: 20px;
    background: white;
}

.chalan-container .table {
    font-size: 14px;
}

.chalan-container .table th {
    background: #f8f9fa;
}

.payment-history-section {
    page-break-inside: avoid;
}

.payment-history-section .table {
    font-size: 12px;
}

.view-chalan-btn {
    margin-right: 5px;
}

/* BMI Tab Styles */
.bmi-card {
    transition: all 0.3s ease;
}

.bmi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.bmi-stat-value {
    font-size: 36px;
    font-weight: bold;
}

.bmi-category-badge {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}

.health-tip-card {
    background: #f8f9fa;
    border-left: 4px solid #3498db;
    transition: all 0.2s ease;
}

.health-tip-card:hover {
    background: #f0f0f0;
}

.health-tip-title {
    font-weight: 600;
    margin-bottom: 5px;
}

.health-tip-text {
    font-size: 13px;
    color: #666;
}

.nutrition-list {
    list-style: none;
    padding-left: 0;
}

.nutrition-list li {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

.nutrition-list li:last-child {
    border-bottom: none;
}

.nutrition-list .food-good {
    color: #27ae60;
}

.nutrition-list .food-bad {
    color: #e74c3c;
}

.bmi-history-chart {
    height: 200px;
    margin-top: 15px;
}

.alert-health-warning {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

.alert-health-critical {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}
</style>

<?= $this->endSection() ?>