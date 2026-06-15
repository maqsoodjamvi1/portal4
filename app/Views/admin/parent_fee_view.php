<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="fee-container">
    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card total-unpaid">
            <h4>Total Unpaid</h4>
            <p><?= number_format($summaries->unpaid_fees + $summaries->unpaid_fines) ?></p>
        </div>
        <div class="card paid-today">
            <h4>Paid Today</h4>
            <p><?= number_format($summaries->paid_today) ?></p>
        </div>
        <div class="card discounts">
            <h4>Discounts</h4>
            <p><?= number_format($summaries->discounted_total) ?></p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="btn btn-primary" onclick="payAll()">Pay All</button>
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#updateDiscountModal">
            Update Discounts
        </button>
        <button class="btn btn-info" onclick="sendSMS()">Send Reminder SMS</button>
    </div>

    <!-- Students Accordion -->
    <div class="student-accordion">
        <?php foreach ($students as $student): ?>
        <div class="student-card">
            <div class="student-header" onclick="toggleStudent(<?= $student->student_id ?>)">
                <div class="student-info">
                    <img src="<?= base_url('uploads/'.$student->profile_photo) ?>" 
                         alt="<?= $student->first_name ?>" 
                         class="student-avatar">
                    <div>
                        <h5><?= "{$student->first_name} {$student->last_name}" ?></h5>
                        <span><?= "{$student->class_name} - {$student->section_name}" ?></span>
                    </div>
                </div>
                <div class="badge"><?= $student->unpaid_count ?> Unpaid</div>
            </div>

            <div class="fee-details" id="details-<?= $student->student_id ?>">
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <th>Month</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($student->unpaid_fees as $fee): ?>
                        <tr>
                            <td><?= $fee->fee_type_name ?></td>
                            <td><?= date('M Y', strtotime($fee->fee_month)) ?></td>
                            <td><?= number_format($fee->amount - $fee->discount) ?></td>
                            <td><?= date('d M Y', strtotime($fee->due_date)) ?></td>
                            <td>
                                <button class="btn-pay" 
                                        data-feeid="<?= $fee->chalan_id ?>"
                                        data-amount="<?= $fee->amount - $fee->discount ?>"
                                        onclick="showPaymentModal(this)">
                                    Pay
                                </button>
                                <a href="<?= site_url("admin/fee/generate_chalan/{$fee->chalan_id}") ?>" 
                                   class="btn-challan" target="_blank">
                                    Generate Challan
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal">
    <!-- Modal content loaded via AJAX -->
</div>

<script>
function showPaymentModal(btn) {
    const feeId = btn.dataset.feeid;
    const amount = btn.dataset.amount;
    
    $('#paymentModal').load(`<?= site_url('admin/fee/payment_form/') ?>${feeId}`, function() {
        $(this).modal('show');
    });
}

function payAll() {
    if (confirm('Pay all outstanding fees?')) {
        fetch(`<?= site_url('admin/fee/pay_all/') ?><?= $parent_id ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }
}
</script>

<?= $this->endSection() ?>