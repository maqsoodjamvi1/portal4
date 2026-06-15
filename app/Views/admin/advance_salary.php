<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Advance Salary Management',
    'icon' => 'fas fa-hand-holding-usd',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Advance Salary', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <!-- Request Form -->
        <div class="row">
            <div class="col-md-5">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-hand-holding-usd me-1"></i> New Advance Request
                        </h3>
                    </div>
                    <form action="<?= base_url('admin/advance-salary/request') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Employee <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control select2" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?= $emp->id ?>">
                                            <?= esc($emp->first_name . ' ' . $emp->last_name) ?>
                                            (Salary: <?= number_format($emp->basic_salary, 2) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" step="0.01" class="form-control" name="amount"
                                           placeholder="Enter amount" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Repayment Months</label>
                                <select name="repayment_months" class="form-control">
                                    <option value="1">1 Month</option>
                                    <option value="2">2 Months</option>
                                    <option value="3" selected>3 Months</option>
                                    <option value="4">4 Months</option>
                                    <option value="5">5 Months</option>
                                    <option value="6">6 Months</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Reason</label>
                                <textarea name="reason" class="form-control" rows="3"
                                          placeholder="Enter reason for advance salary"></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list me-1"></i> Advance Requests
                        </h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-secondary btn-sm" id="showAll">All</button>
                                <button type="button" class="btn btn-secondary btn-sm" id="showPending">Pending</button>
                                <button type="button" class="btn btn-secondary btn-sm" id="showApproved">Approved</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover" id="advanceTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Amount</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($advances as $adv): ?>
                                <tr data-status="<?= $adv->status ?>">
                                    <td><?= $adv->advance_id ?></td>
                                    <td>
                                        <strong><?= esc($adv->first_name . ' ' . $adv->last_name) ?></strong><br>
                                        <small class="text-muted"><?= esc($adv->designation ?? 'N/A') ?></small>
                                    </td>
                                    <td class="text-end"><?= number_format($adv->amount, 2) ?></td>
                                    <td><?= date('d-M-Y', strtotime($adv->request_date)) ?></td>
                                    <td>
                                        <?php if ($adv->status == 'pending'): ?>
                                            <span class="badge text-bg-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php elseif ($adv->status == 'approved'): ?>
                                            <span class="badge text-bg-success">
                                                <i class="fas fa-check-circle"></i> Approved
                                            </span>
                                        <?php elseif ($adv->status == 'partially_paid'): ?>
                                            <span class="badge text-bg-info">
                                                <i class="fas fa-chart-line"></i> Partially Paid
                                            </span>
                                        <?php elseif ($adv->status == 'fully_paid'): ?>
                                            <span class="badge text-bg-secondary">
                                                <i class="fas fa-check-double"></i> Fully Paid
                                            </span>
                                        <?php else: ?>
                                            <span class="badge text-bg-danger">
                                                <i class="fas fa-times-circle"></i> Rejected
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($adv->status == 'pending'): ?>
                                            <a href="<?= base_url('admin/advance-salary/approve/' . $adv->advance_id) ?>"
                                               class="btn btn-sm btn-success" onclick="return confirm('Approve this request?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="<?= base_url('admin/advance-salary/reject/' . $adv->advance_id) ?>"
                                               class="btn btn-sm btn-danger" onclick="return confirm('Reject this request?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-info" onclick="viewDetails(<?= $adv->advance_id ?>)">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Filter table by status
    $('#showAll').click(function() {
        $('#advanceTable tbody tr').show();
        $(this).addClass('active').siblings().removeClass('active');
    });

    $('#showPending').click(function() {
        $('#advanceTable tbody tr').hide();
        $('#advanceTable tbody tr[data-status="pending"]').show();
        $(this).addClass('active').siblings().removeClass('active');
    });

    $('#showApproved').click(function() {
        $('#advanceTable tbody tr').hide();
        $('#advanceTable tbody tr[data-status="approved"], #advanceTable tbody tr[data-status="partially_paid"], #advanceTable tbody tr[data-status="fully_paid"]').show();
        $(this).addClass('active').siblings().removeClass('active');
    });
});

function viewDetails(advanceId) {
    // Implement modal view for details
    alert('View details for advance ID: ' + advanceId);
}
</script>

<?= $this->endSection() ?>
