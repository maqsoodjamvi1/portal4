<?php if (!empty($students)) : ?>
<div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead class="thead-light">
      <tr>
        <th>#</th>
        <th>Student Name</th>
        <th>Reg No</th>
        <th>Parent</th>
        <th>Family ID</th>
        <th>Class</th>
        <th>Unpaid Month</th>
        <th>Amount</th>
        <th>Pay</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; foreach ($students as $student) : ?>
        <?php if (!empty($student['unpaid'])) : ?>
          <?php foreach ($student['unpaid'] as $month => $data) : ?>
            <tr>
              <td><?= $i++; ?></td>
              <td><?= esc($student['name']) ?></td>
              <td><?= esc($student['reg_no']) ?></td>
              <td><?= esc($student['parent_name']) ?></td>
              <td><?= esc($student['family_id']) ?></td>
              <td><?= esc($student['class_name']) ?></td>
              <td><?= esc($month) ?></td>
              <td class="text-right">AED <?= number_format($data['amount'], 2) ?></td>
              <td>
                <button class="btn btn-success btn-sm pay-fee-btn"
                        data-student-id="<?= $student['id'] ?>"
                        data-month="<?= esc($month) ?>"
                        data-amount="<?= $data['amount'] ?>">
                  Pay
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else : ?>
  <div class="alert alert-warning">No unpaid fee records found.</div>
<?php endif; ?>

<script>
$(document).on('click', '.pay-fee-btn', function() {
  const btn = $(this);
  const studentId = btn.data('student-id');
  const month = btn.data('month');
  const amount = btn.data('amount');

  if (confirm(`Are you sure you want to pay AED ${amount} for ${month}?`)) {
    $.post('<?= base_url('admin/fee-chalan-pay/process-payment') ?>', {
      student_id: studentId,
      month: month,
      amount: amount
    }, function(res) {
      if (res.success) {
        toastr.success('Payment processed successfully');
        btn.closest('tr').remove();
      } else {
        toastr.error(res.message || 'Payment failed');
      }
    }, 'json');
  }
});
</script>
