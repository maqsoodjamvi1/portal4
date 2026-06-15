<?php $sno = 1; ?>
<?php foreach ($students as $stu): ?>
  <?php foreach ($stu['unpaid'] as $fee): ?>
    <tr>
      <td><input type="checkbox" class="selectFee" value="<?= $fee['fee_id'] ?>"></td>
      <td><?= esc($stu['student_name']) ?></td>
      <td><?= esc($fee['fee_type']) ?></td>
      <td><?= esc($fee['fee_month']) ?></td>
      <td class="text-end"><?= number_format($fee['amount'], 0) ?></td>
      <td><span class="badge text-bg-danger">Unpaid</span></td>
      <td>
        <button class="btn btn-sm btn-success pay_fee" data-id="<?= $fee['fee_id'] ?>">Pay</button>
      </td>
    </tr>
  <?php endforeach; ?>
<?php endforeach; ?>
