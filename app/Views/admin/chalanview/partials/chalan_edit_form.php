<div class="container-fluid">
    <h6 class="mb-3">Editing Chalans for: <strong><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></strong> (<?= esc($student['reg_no'] ?? 'No Reg No') ?>)</h6>
    
    <form id="chalan-edit-form">
        <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Fee Type</th>
                        <th>Fee Month</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Discount</th>
                        <th>Net Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chalans as $chalan): ?>
                    <tr>
                        <td>
                            <?= $chalan['chalan_id'] ?>
                            <input type="hidden" name="chalan_id[]" value="<?= $chalan['chalan_id'] ?>">
                        </td>
                        <td><?= esc($chalan['fee_type_name'] ?? 'N/A') ?></td>
                        <td><?= date('M Y', strtotime($chalan['fee_month'] . '-01')) ?></td>
                        <td><?= date('d-m-Y', strtotime($chalan['issue_date'])) ?></td>
                        <td><?= date('d-m-Y', strtotime($chalan['due_date'])) ?></td>
                        <td>
                            <input type="number" name="amount[]" class="form-control form-control-sm amount-input" 
                                   value="<?= $chalan['amount'] ?>" step="0.01" min="0" required>
                        </td>
                        <td>
                            <input type="number" name="discount[]" class="form-control form-control-sm discount-input" 
                                   value="<?= $chalan['discount'] ?>" step="0.01" min="0" max="<?= $chalan['amount'] ?>">
                        </td>
                        <td class="net-amount">
                            <?= number_format($chalan['amount'] - $chalan['discount'], 2) ?>
                        </td>
                        <td>
                            <select name="status[]" class="form-control form-control-sm">
                                <option value="unpaid" <?= $chalan['status'] == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                <option value="paid" <?= $chalan['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="discounted" <?= $chalan['status'] == 'discounted' ? 'selected' : '' ?>>Discounted</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="5" class="text-right">Totals:</th>
                        <th id="total-amount">0</th>
                        <th id="total-discount">0</th>
                        <th id="total-net">0</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle mr-2"></i>
            Changes will be applied immediately. The page will reload to show updated data.
        </div>
    </form>
</div>

<script>
$(function() {
    // Calculate net amount and totals
    function calculateTotals() {
        let totalAmount = 0;
        let totalDiscount = 0;
        
        $('.amount-input').each(function(index) {
            const amount = parseFloat($(this).val()) || 0;
            const discount = parseFloat($('.discount-input').eq(index).val()) || 0;
            const netAmount = amount - discount;
            
            $('.net-amount').eq(index).text(netAmount.toFixed(2));
            
            totalAmount += amount;
            totalDiscount += discount;
        });
        
        $('#total-amount').text(totalAmount.toFixed(2));
        $('#total-discount').text(totalDiscount.toFixed(2));
        $('#total-net').text((totalAmount - totalDiscount).toFixed(2));
    }
    
    // Update max discount when amount changes
    $('.amount-input').on('input', function() {
        const $row = $(this).closest('tr');
        const amount = parseFloat($(this).val()) || 0;
        $row.find('.discount-input').attr('max', amount);
        calculateTotals();
    });
    
    $('.discount-input').on('input', function() {
        const $row = $(this).closest('tr');
        const amount = parseFloat($row.find('.amount-input').val()) || 0;
        let discount = parseFloat($(this).val()) || 0;
        
        if (discount > amount) {
            $(this).val(amount);
        }
        calculateTotals();
    });
    
    // Initial calculation
    calculateTotals();
});
</script>