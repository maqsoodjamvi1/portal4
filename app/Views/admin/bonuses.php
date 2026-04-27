<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Bonus Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bonuses</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Add Bonus Form -->
        <div class="row">
            <div class="col-md-5">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-gift mr-1"></i> Add New Bonus
                        </h3>
                    </div>
                    <form action="<?= base_url('admin/bonuses/add') ?>" method="post">
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
                                <label>Bonus Type</label>
                                <select name="bonus_type" class="form-control">
                                    <option value="attendance">Attendance Bonus</option>
                                    <option value="performance">Performance Bonus</option>
                                    <option value="festival">Festival Bonus</option>
                                    <option value="annual">Annual Bonus</option>
                                    <option value="special">Special Bonus</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">PKR</span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control" name="amount" 
                                           placeholder="Enter bonus amount" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Bonus Month</label>
                                <input type="month" class="form-control" name="bonus_month" 
                                       value="<?= date('Y-m') ?>">
                                <small class="text-muted">Select month for which bonus is awarded</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Reason / Remarks</label>
                                <textarea name="reason" class="form-control" rows="3" 
                                          placeholder="Enter reason for bonus (optional)"></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Add Bonus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i> Bonus History
                        </h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <input type="text" id="bonusSearch" class="form-control" placeholder="Search employee...">
                                <div class="input-group-append">
                                    <button class="btn btn-default" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover" id="bonusTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Month</th>
                                    <th>Date Added</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bonuses as $bonus): ?>
                                <tr>
                                    <td><?= $bonus->bonus_id ?></td>
                                    <td>
                                        <strong><?= esc($bonus->first_name . ' ' . $bonus->last_name) ?></strong><br>
                                        <small class="text-muted"><?= esc($bonus->designation ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = [
                                            'attendance' => 'info',
                                            'performance' => 'primary',
                                            'festival' => 'warning',
                                            'annual' => 'success',
                                            'special' => 'danger'
                                        ];
                                        $class = $badgeClass[$bonus->bonus_type] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $class ?>">
                                            <?= ucfirst($bonus->bonus_type) ?>
                                        </span>
                                    </td>
                                    <td class="text-right font-weight-bold text-success">
                                        <?= number_format($bonus->amount, 2) ?>
                                    </td>
                                    <td><?= date('M Y', strtotime($bonus->bonus_month)) ?></td>
                                    <td><?= date('d-M-Y', strtotime($bonus->created_date)) ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/bonuses/delete/' . $bonus->bonus_id) ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Delete this bonus record?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($bonuses)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> No bonus records found
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-right">
                                        <?= number_format(array_sum(array_column($bonuses, 'amount')), 2) ?>
                                    </th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    // Search functionality
    $('#bonusSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#bonusTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Initialize select2
    $('.select2').select2({
        width: '100%',
        placeholder: 'Select employee'
    });
});
</script>

<?= $this->endSection() ?>