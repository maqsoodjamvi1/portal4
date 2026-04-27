<?php if (!empty($student_info) && is_array($student_info)): ?>
<div class="chalanwrapper">
  <div class="row">
    <div class="col-sm-3 ml-2 mt-2"></div>
    <div class="col-sm-8" style="font-weight:bold;">
      <?= esc($copy_type ?? 'Bank Copy') ?>
    </div><br />
    <div class="col-sm-3 ml-2 mt-2">
      <img style="width: 100%;" src="<?= base_url('system-logo/' . esc($student_info['logo'] ?? 'default.png')) ?>">
    </div>
    <div class="col-sm-8">
      <?= esc($student_info['system_name']) ?><br />
      <?= esc($student_info['campus_name']) ?>, <?= esc($student_info['location']) ?>
    </div>
  </div>  

  <div class="ml-2 mt-2" style="text-align: left;">
    <?= esc($student_info['bank_name']) ?>,
    <?= esc($student_info['bank_address']) ?>,
    <?= esc($student_info['bank_code']) ?><br />
    <?php if (!empty($student_info['bank_acc'])): ?>
      Account No: <?= esc($student_info['bank_acc']) ?><br />
    <?php endif; ?>
  </div>

  <div class="feeinfo">
    <div class="chalanrows">Chalan# <?= esc($student_info['chalan_no']) ?></div>
    <div class="chalanrows" style="line-height: 22px;font-size: 12px;">
      Roommates: <?= esc(rtrim($student_info['stdinfo'], ',')) ?>
    </div>
    <div class="chalanrows" style="line-height: 22px;font-size: 12px;">
      Student Name: <?= esc($student_info['student_name']) ?>
    </div>
    <div class="chalanrows" style="line-height: 22px;font-size: 12px;">
      Father Name: <?= esc($student_info['f_name']) ?>
    </div>
    <div class="chalancolleft" style="line-height: 22px;font-size: 12px;">
      Issue Date: <?= esc($student_info['issue_date']) ?>
    </div>
    <div class="chalancolright" style="line-height: 22px;font-size: 12px;">
      Due Date: <?= esc($student_info['due_date']) ?>
    </div>
    <div class="chalancolright" style="line-height: 22px;font-size: 12px;">
      Fee Month: <?= esc($student_info['fee_month']) ?>
    </div>
  </div>

  <table width="98%" border="1" class="feetable">
    <tr>
      <th>Particulars</th>
      <th>Amount</th>
    </tr>
    <?php 
      $total = 0;
      $nCount = 0;
      $arialSum = 0;
    ?>
    <?php foreach ($student_info['student_fee'] as $fee_info): ?>
      <?php 
        $feeAmount = $fee_info['amount'] - $fee_info['discount'];
        $total += $feeAmount;
      ?>
      <?php if ($nCount < 5): ?>
        <tr>
          <td><?= esc($fee_info['fee_month']) ?></td>
          <td><?= esc($feeAmount) ?>/-</td>
        </tr>
      <?php else: ?>
        <?php $arialSum += $feeAmount; ?>
      <?php endif; ?>
      <?php $nCount++; ?>
    <?php endforeach; ?>

    <?php if ($arialSum > 0): ?>
      <tr>
        <td>Arrears</td>
        <td><?= esc($arialSum) ?>/-</td>
      </tr>
    <?php endif; ?>

    <?php for ($i = 1; $i <= max(0, 6 - $nCount); $i++): ?>
      <tr><td style="height: 34px;"></td><td></td></tr>
    <?php endfor; ?>

    <?php foreach ($student_info['fee_fine'] as $value): ?>
      <?php if (!empty($value['fine_amount'])): ?>
        <tr><td>Fine</td><td><?= esc($value['fine_amount']) ?>/-</td></tr>
        <?php $total += $value['fine_amount']; ?>
      <?php endif; ?>
    <?php endforeach; ?>

    <tr>
      <td>Total Payable</td>
      <td><?= esc($total) ?>/-</td>
    </tr>
  </table>
  <br />
  <div style="text-align:left;margin-left: 5px;font-size: 13px;">
    <strong>Note: </strong><?= esc($student_info['chalan_f_msg']) ?>
  </div>
</div>
<?php else: ?>
<div class="alert alert-warning">No data found to display the chalan.</div>
<?php endif; ?>
