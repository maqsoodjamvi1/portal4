<?php if (!empty($salaries)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Month</th>
                <th>Salary Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salaries as $salary): ?>
            <tr>
                <td><?= date('F Y', strtotime($salary->date)) ?></td>
                <td><?= number_format($salary->salary) ?></td>
                <td>
                    <?php if ($salary->status == 1): ?>
                        <span class="badge badge-success">Active</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= base_url('admin/users/salarySlip/' . $user->id . '/' . $salary->salary_id) ?>" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-file-pdf mr-1"></i> View Slip
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i> No salary records found.
    </div>
<?php endif; ?>