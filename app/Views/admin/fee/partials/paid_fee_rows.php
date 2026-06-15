<div class="fee-container">
    <!-- Parent and Summary Section -->
    <div class="header-section">
        <div class="parent-info-card">
            <div class="parent-photo">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="parent-details">
                <h2><?= $parent->f_name ?></h2>
                <div class="parent-contacts">
                    <span><i class="fas fa-phone"></i> <?= $parent->father_contact ?></span>
                    <span><i class="fas fa-map-marker-alt"></i> <?= $parent->address_line1 ?></span>
                </div>
            </div>
        </div>
        
        <div class="summary-stats">
            <div class="stat-card unpaid-total">
                <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="stat-content">
                    <h4>Total Unpaid</h4>
                    <p><?= number_format($summaries['total_unpaid']) ?>/-</p>
                </div>
            </div>
            
            <div class="stat-card balance-due">
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                <div class="stat-content">
                    <h4>Balance Due</h4>
                    <p><?= number_format($summaries['balance']) ?>/-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons (keep existing class names) -->
    <div class="action-buttons">
        <button class="btn btn-primary" onclick="payAll()">
            <i class="fas fa-money-bill-wave"></i> Pay All
        </button>
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#discountModal">
            <i class="fas fa-percentage"></i> Apply Discount
        </button>
        <button class="btn btn-info" onclick="sendReminders()">
            <i class="fas fa-sms"></i> Send Reminders
        </button>
    </div>

    <!-- Students List (maintain existing structure) -->
    <div class="students-list">
        <?php foreach ($students as $student): ?>
        <div class="student-card">
            <div class="student-header" onclick="toggleStudentDetails(<?= $student->student_id ?>)">
                <div class="student-info">
                    <img src="<?= base_url('uploads/'.$student->profile_photo) ?>" 
                         alt="<?= $student->first_name ?>" 
                         class="student-avatar"
                         onerror="this.src='<?= base_url('assets/img/default-student.png') ?>'">
                    <div>
                        <h4><?= $student->first_name.' '.$student->last_name ?></h4>
                        <div class="student-meta">
                            <span class="class-label"><?= $student->class_name ?></span>
                            <span class="section-label"><?= $student->section_name ?></span>
                            <span class="unpaid-count"><?= count($student->unpaid_fees) ?> unpaid</span>
                        </div>
                    </div>
                </div>
                <div class="student-total-due">
                    <span>Total Due:</span>
                    <strong><?= number_format(array_sum(array_column($student->unpaid_fees, 'amount'))) ?>/-</strong>
                </div>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
            
            <div class="fee-details" id="fee-details-<?= $student->student_id ?>">
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <th>Period</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($student->unpaid_fees as $fee): ?>
                        <tr>
                            <td><?= $fee->fee_type_name ?></td>
                            <td><?= date('M Y', strtotime($fee->fee_month)) ?></td>
                            <td><?= date('d M Y', strtotime($fee->due_date)) ?></td>
                            <td><?= number_format($fee->amount - $fee->discount) ?>/-</td>
                            <td>
                                <span class="status-badge unpaid">Unpaid</span>
                                <?php if ($fee->discount > 0): ?>
                                <span class="discount-badge">-<?= number_format($fee->discount) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <button class="btn-pay" 
                                        data-chalanid="<?= $fee->chalan_id ?>"
                                        data-studentid="<?= $student->student_id ?>"
                                        data-feeamount="<?= $fee->amount - $fee->discount ?>">
                                    <i class="fas fa-money-bill-alt"></i> Pay
                                </button>
                                <a href="/admin.php#/fee_chalan_single?m=add&id=<?= $student->student_id ?>" 
                                   class="btn-challan">
                                    <i class="fas fa-file-invoice"></i> Challan
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($students)): ?>
        <div class="no-fees-message">
            <i class="fas fa-check-circle"></i>
            <h3>No Unpaid Fees Found</h3>
            <p>All fees are paid up to date for this parent.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Modal (keep existing structure) -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Discount Modal (keep existing structure) -->
<div class="modal fade" id="discountModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!-- Existing discount form content -->
        </div>
    </div>
</div>

<style>
/* Enhanced CSS while maintaining existing class names */
.fee-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f7fa;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 20px;
}

.parent-info-card {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    flex: 1;
    min-width: 300px;
}

.parent-photo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #e3f2fd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: #2196f3;
    font-size: 30px;
}

.parent-details h2 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.parent-contacts {
    display: flex;
    gap: 15px;
    color: #7f8c8d;
    font-size: 14px;
}

.parent-contacts i {
    margin-right: 5px;
}

.summary-stats {
    display: flex;
    gap: 15px;
    flex: 1;
    min-width: 300px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    flex: 1;
}

.stat-icon {
    font-size: 24px;
    margin-right: 15px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.unpaid-total .stat-icon {
    background: #ffebee;
    color: #f44336;
}

.balance-due .stat-icon {
    background: #e3f2fd;
    color: #2196f3;
}

.stat-content h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #7f8c8d;
}

.stat-content p {
    margin: 0;
    font-size: 18px;
    font-weight: bold;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.action-buttons .btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    border-radius: 6px;
    font-weight: 500;
}

.student-card {
    background: white;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

.student-header {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    cursor: pointer;
    transition: background 0.2s;
}

.student-header:hover {
    background: #f8f9fa;
}

.student-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
    border: 2px solid #e3f2fd;
}

.student-info h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.student-meta {
    display: flex;
    gap: 8px;
    font-size: 13px;
}

.class-label, .section-label {
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.class-label {
    background: #e3f2fd;
    color: #2196f3;
}

.section-label {
    background: #e8f5e9;
    color: #4caf50;
}

.unpaid-count {
    color: #f44336;
    font-weight: 500;
}

.student-total-due {
    text-align: right;
    margin-right: 20px;
}

.student-total-due span {
    display: block;
    font-size: 12px;
    color: #7f8c8d;
}

.student-total-due strong {
    font-size: 16px;
    color: #f44336;
}

.toggle-icon {
    transition: transform 0.2s;
    color: #7f8c8d;
}

.fee-details {
    display: none;
    padding: 0 20px 20px;
}

.fee-table {
    width: 100%;
    border-collapse: collapse;
}

.fee-table th {
    background: #f5f7fa;
    padding: 12px 15px;
    text-align: left;
    font-size: 13px;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.fee-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.fee-table tr:last-child td {
    border-bottom: none;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.unpaid {
    background: #ffebee;
    color: #f44336;
}

.discount-badge {
    margin-left: 5px;
    background: #fff8e1;
    color: #ff8f00;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.actions {
    display: flex;
    gap: 8px;
}

.btn-pay, .btn-challan {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-pay {
    background: #4caf50;
    color: white;
}

.btn-pay:hover {
    background: #3d8b40;
}

.btn-challan {
    background: #2196f3;
    color: white;
}

.btn-challan:hover {
    background: #0d8bf2;
}

.no-fees-message {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.no-fees-message i {
    font-size: 50px;
    color: #4caf50;
    margin-bottom: 15px;
}

.no-fees-message h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.no-fees-message p {
    color: #7f8c8d;
    margin: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
    }
    
    .parent-info-card, .summary-stats {
        width: 100%;
    }
    
    .student-header {
        flex-wrap: wrap;
    }
    
    .student-total-due {
        text-align: left;
        width: 100%;
        margin-top: 10px;
        margin-left: 65px;
    }
    
    .fee-table {
        display: block;
        overflow-x: auto;
    }
    
    .actions {
        flex-direction: column;
        gap: 5px;
    }
}
</style>

<script>
// Enhanced JavaScript with existing functionality
function toggleStudentDetails(studentId) {
    const details = document.getElementById(`fee-details-${studentId}`);
    const icon = document.querySelector(`#fee-details-${studentId}`).previousElementSibling.querySelector('.toggle-icon');
    
    if (details.style.display === 'block') {
        details.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    } else {
        details.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    }
}

function showPaymentModal(btn) {
    const chalanId = btn.dataset.chalanid;
    const amount = btn.dataset.feeamount;
    const studentId = btn.dataset.studentid;
    
    $('#paymentModal').load(`/admin/fee/payment_form?chalan_id=${chalanId}&amount=${amount}&student_id=${studentId}`, function() {
        $(this).modal('show');
    });
}

function payAll() {
    if (confirm('Are you sure you want to pay all outstanding fees for all students?')) {
        fetch(`/admin/fee/pay_all?parent_id=<?= $parent_id ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('All fees paid successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing payment');
        });
    }
}

function sendReminders() {
    if (confirm('Send payment reminders to this parent for all unpaid fees?')) {
        fetch(`/admin/fee/send_reminders?parent_id=<?= $parent_id ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Reminders sent successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending reminders');
        });
    }
}

// Initialize - show first student's details by default
document.addEventListener('DOMContentLoaded', function() {
    const firstStudent = document.querySelector('.student-card');
    if (firstStudent) {
        const firstStudentId = firstStudent.querySelector('.student-header').getAttribute('onclick').match(/\d+/)[0];
        toggleStudentDetails(firstStudentId);
    }
});
</script>