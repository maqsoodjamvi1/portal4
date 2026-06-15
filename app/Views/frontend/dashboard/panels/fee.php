<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-2">
        <h5 class="mb-0 fs-6"><i class="fa fa-credit-card me-2 text-primary"></i> Family Fee History</h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($feeHistory)): ?>
            <div class="fee-table-wrapper">
                <table class="fee-history-table">
                    <thead>
                        <tr>
                            <th class="month-col">Month</th>
                            <?php foreach ($feeHistory as $record): ?>
                                <th class="month-name"><?= esc(date('M', strtotime($record['month'] . '-01'))) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="row-label">Monthly</td>
                            <?php foreach ($feeHistory as $record): ?>
                                <td class="amount-cell">
                                    <?php if ($record['monthly_paid'] > 0): ?>
                                        <span class="amount-paid"><?= number_format($record['monthly_paid'], 0) ?></span>
                                    <?php else: ?>
                                        <span class="amount-na">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="row-label">Other</td>
                            <?php foreach ($feeHistory as $record): ?>
                                <td class="amount-cell">
                                    <?php if ($record['other_paid'] > 0): ?>
                                        <span class="amount-other"><?= number_format($record['other_paid'], 0) ?></span>
                                    <?php else: ?>
                                        <span class="amount-na">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="row-label total-label">Total</td>
                            <?php foreach ($feeHistory as $record): ?>
                                <td class="amount-cell total-cell">
                                    <?php if ($record['total_paid'] > 0): ?>
                                        <span class="amount-total"><?= number_format($record['total_paid'], 0) ?></span>
                                    <?php else: ?>
                                        <span class="amount-na">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php 
            $unpaidData = $totalUnpaidAmount ?? ['monthly' => 0, 'other' => 0, 'total' => 0];
            if ($unpaidData['total'] > 0): 
            ?>
                <div class="balance-summary">
                    <div class="balance-item">
                        <div class="balance-label">Monthly Bal.</div>
                        <div class="balance-amount monthly-bal"><?= number_format($unpaidData['monthly'], 0) ?></div>
                    </div>
                    <div class="balance-item">
                        <div class="balance-label">Other Bal.</div>
                        <div class="balance-amount other-bal"><?= number_format($unpaidData['other'], 0) ?></div>
                    </div>
                    <div class="balance-item">
                        <div class="balance-label">Total Bal.</div>
                        <div class="balance-amount total-bal"><?= number_format($unpaidData['total'], 0) ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="paid-status">
                    <i class="fa fa-check-circle"></i> All fees are paid up to date!
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-credit-card fa-2x mb-2 opacity-50"></i>
                <p>No fee history available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
