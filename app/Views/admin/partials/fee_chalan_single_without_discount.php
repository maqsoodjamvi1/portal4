<?php
/** @var array $student_info */
/** @var string $footer_line1 */
/** @var string $footer_line2 */
/** @var int|string $show_line1 */
/** @var int|string $show_line2 */
?>

<div style="width:32%; float:left; margin-left:1%; margin-bottom: 10px;">
  <div dir="rtl" lang="ur"> <?= esc($student_info['chalan_h_msg'] ?? '') ?> </div>
  <div class="chalanwrapper">
    <div class="row">
      <div class="col-sm-3 ml-2 mt-2"></div>
      <div class="col-sm-8" style="font-weight:bold;">Copy</div><br />
      <div class="col-sm-3 ml-2">
        <img style="width: 100%;" src="<?= base_url('system-logo/' . esc($student_info['logo'] ?? '')) ?>">
      </div>
      <div class="col-sm-8">
        <?= esc($student_info['system_name'] ?? '') ?><br />
        <?= esc($student_info['campus_name'] ?? '') ?>, <?= esc($student_info['location'] ?? '') ?>
      </div>
    </div>

    <div class="ml-2 mt-2" style="text-align: left;">
      <?= esc($student_info['bank_name'] ?? '') ?>,
      <?= esc($student_info['bank_address'] ?? '') ?>,
      <?= esc($student_info['bank_code'] ?? '') ?><br />
      <?php if (!empty($student_info['bank_acc'])): ?>
        Account No: <?= esc($student_info['bank_acc']) ?><br />
      <?php endif; ?>
    </div>

    <div class="feeinfo">
      <div class="chalanrows">
        Chalan#: <?= esc($student_info['chalan_no'] ?? $student_info['chalan_id'] ?? 'N/A') ?>
        <span style="float:right; margin-right:10px;">Family#: <?= esc($student_info['family_no'] ?? $student_info['parent_id'] ?? '') ?></span>
        <span style="float:right; margin-right:10px;">Reg#: <?= esc($student_info['reg_no'] ?? '') ?></span>
      </div>
      <div class="chalanrows">Name: <?= esc($student_info['student_name'] ?? '') ?></div>
      <div class="chalanrows">Father Name: <?= esc($student_info['f_name'] ?? '') ?></div>
      <div class="chalancolleft">Class: <?= esc($student_info['class_name'] ?? '') ?></div>
      <div class="chalancolright">Fee Month: <?= esc($student_info['fee_month'] ?? '') ?></div>
      <div class="chalancolleft">Issue Date: <?= esc($student_info['issue_date'] ?? '') ?></div>
      <div class="chalancolright">Due Date: <?= esc($student_info['due_date'] ?? '') ?></div>
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
      <?php foreach ($student_info['student_fee'] ?? [] as $fee_info): ?>
        <?php 
          $amount = $fee_info['amount'];
          $total += $amount;
        ?>
        <?php if ($nCount < 5): ?>
          <tr>
            <td><?= esc($fee_info['fee_name']) ?> (<?= esc($fee_info['fee_month']) ?>)</td>
            <td><?= $amount ?>/-</td>
          </tr>
        <?php else: ?>
          <?php $arialSum += $amount; ?>
        <?php endif; ?>
        <?php $nCount++; ?>
      <?php endforeach; ?>

      <?php if ($arialSum > 0): ?>
        <tr><td>Arrears</td><td><?= $arialSum ?>/-</td></tr>
      <?php endif; ?>

      <?php if ($nCount < 5): ?>
        <?php for ($i = 0; $i < 6 - $nCount; $i++): ?>
          <tr><td style="height: 34px;"></td><td></td></tr>
        <?php endfor; ?>
      <?php endif; ?>

      <?php foreach ($student_info['fee_fine'] ?? [] as $fine): ?>
        <?php $total += $fine['fine_amount']; ?>
        <tr><td>Fine (<?= esc($fine['fee_month']) ?>)</td><td><?= $fine['fine_amount'] ?>/-</td></tr>
      <?php endforeach; ?>

      <tr><td>Total Payable</td><td><?= $total ?>/-</td></tr>
    </table>

    <br />
    <div style="text-align:left; margin-left:5px;">
      <?= esc($student_info['chalan_f_msg'] ?? '') ?>
    </div>
  </div>

  <?php if ($show_line1 == 1): ?>
    <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;">
      <?= esc($footer_line1) ?>&nbsp;&nbsp;
    </div>
  <?php endif; ?>

  <?php if ($show_line2 == 1): ?>
    <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;margin-bottom: 20px;">
      <?= esc($footer_line2) ?>&nbsp;&nbsp;
    </div>
  <?php endif; ?>
</div>