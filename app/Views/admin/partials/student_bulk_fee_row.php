<?php
// ---------- Helpers ----------
if (!function_exists('nf')) {
  function nf($v) { return number_format((float)$v, 2, '.', ''); }
}

// ---------- Inputs with fallbacks (from controller) ----------
$student_id = isset($student_id) ? (int)$student_id : 0;

$first_name = isset($first_name) ? (string)$first_name : '';
$last_name  = isset($last_name)  ? (string)$last_name  : '';
$full_name  = trim($first_name . ' ' . $last_name);

// Fee context
$class_fee        = isset($class_fee) ? (float)$class_fee : 0.0;
$student_fee_val  = isset($student_fee) ? (float)$student_fee : 0.0; // controller precomputes it
$fee_plan_val     = isset($fee_plan) ? (int)$fee_plan : 0;

// Months (keys & nets)
$prev_key = isset($prev_key) ? $prev_key : null;
$curr_key = isset($curr_key) ? $curr_key : null;
$next_key = isset($next_key) ? $next_key : null;

$prev_net = isset($prev_net) ? (float)$prev_net : 0.0;
$curr_net = isset($curr_net) ? (float)$curr_net : 0.0;
$next_net = isset($next_net) ? (float)$next_net : 0.0;

$pAmt = isset($pAmt) ? (float)$pAmt : $class_fee;
$cAmt = isset($cAmt) ? (float)$cAmt : $class_fee;
$nAmt = isset($nAmt) ? (float)$nAmt : $class_fee;

$monthly_fee_type_id = isset($monthly_fee_type_id) ? (int)$monthly_fee_type_id : 0;
?>

<tr
  data-classfee="<?= esc(nf($class_fee)) ?>"
  data-student-name="<?= esc($full_name) ?>"
>
  <!-- Hidden id/context -->
  <td class="d-none">
    <input type="hidden" name="student_id" value="<?= esc($student_id) ?>">
    <input type="hidden" name="class_fee"   value="<?= esc(nf($class_fee)) ?>">
  </td>

  <!-- S.No (filled by JS) -->
  <td class="sno-cell sticky-col"></td>

  <!-- Student Name -->
  <td class="student-name-cell sticky-col-2"><?= esc($full_name) ?></td>

  <!-- Previous Month -->
  <td data-col="month_prev" class="align-middle col-month_prev">
    <?php if ($prev_key): ?>
      <input type="checkbox" class="month-apply" name="months[<?= esc($prev_key) ?>][apply]" value="1" checked hidden>
      <input type="number" step="0.01" min="0"
             class="form-control form-control-sm month-net"
             name="months[<?= esc($prev_key) ?>][net]"
             value="<?= esc(nf($prev_net)) ?>"
             data-original="<?= esc(nf($prev_net)) ?>"
             placeholder="0.00">
      <input type="hidden" name="months[<?= esc($prev_key) ?>][amount]"      value="<?= esc(nf($pAmt)) ?>">
      <input type="hidden" name="months[<?= esc($prev_key) ?>][fee_type_id]" value="<?= $monthly_fee_type_id ?>">
      <input type="hidden" name="months[<?= esc($prev_key) ?>][orig_net]"    value="<?= esc(nf($prev_net)) ?>">
    <?php endif; ?>
  </td>

  <!-- Current Month -->
  <td data-col="month_curr" class="align-middle col-month_curr">
    <?php if ($curr_key): ?>
      <input type="checkbox" class="month-apply" name="months[<?= esc($curr_key) ?>][apply]" value="1" checked hidden>
      <input type="number" step="0.01" min="0"
             class="form-control form-control-sm month-net"
             name="months[<?= esc($curr_key) ?>][net]"
             value="<?= esc(nf($curr_net)) ?>"
             data-original="<?= esc(nf($curr_net)) ?>"
             placeholder="0.00">
      <input type="hidden" name="months[<?= esc($curr_key) ?>][amount]"      value="<?= esc(nf($cAmt)) ?>">
      <input type="hidden" name="months[<?= esc($curr_key) ?>][fee_type_id]" value="<?= $monthly_fee_type_id ?>">
      <input type="hidden" name="months[<?= esc($curr_key) ?>][orig_net]"    value="<?= esc(nf($curr_net)) ?>">
    <?php endif; ?>
  </td>

  <!-- Next Month -->
  <td data-col="month_next" class="align-middle col-month_next">
    <?php if ($next_key): ?>
      <input type="checkbox" class="month-apply" name="months[<?= esc($next_key) ?>][apply]" value="1" checked hidden>
      <input type="number" step="0.01" min="0"
             class="form-control form-control-sm month-net"
             name="months[<?= esc($next_key) ?>][net]"
             value="<?= esc(nf($next_net)) ?>"
             data-original="<?= esc(nf($next_net)) ?>"
             placeholder="0.00">
      <input type="hidden" name="months[<?= esc($next_key) ?>][amount]"      value="<?= esc(nf($nAmt)) ?>">
      <input type="hidden" name="months[<?= esc($next_key) ?>][fee_type_id]" value="<?= $monthly_fee_type_id ?>">
      <input type="hidden" name="months[<?= esc($next_key) ?>][orig_net]"    value="<?= esc(nf($next_net)) ?>">
    <?php endif; ?>
  </td>

  <!-- Student Fee (editable) -->
  <td data-col="student_fee" class="col-student_fee" style="min-width:160px;">
  <input name="student_fee" class="form-control form-control-sm text-end"
         value="<?= esc(nf($student_fee ?? 0)) ?>" inputmode="decimal" placeholder="0.00">
</td>

  <!-- Fee Plan (editable) -->
  <td data-col="fee_plan" class="col-fee_plan" style="min-width:140px;">
    <select name="fee_plan" class="form-control form-control-sm">
      <option value="0" <?= ($fee_plan_val===0)?'selected':''; ?>>Monthly</option>
      <option value="1" <?= ($fee_plan_val===1)?'selected':''; ?>>Bi-monthly</option>
      <option value="2" <?= ($fee_plan_val===2)?'selected':''; ?>>Quarterly</option>
      <option value="3" <?= ($fee_plan_val===3)?'selected':''; ?>>Annually</option>
    </select>
  </td>

  <!-- Action -->
  <td class="text-end action-cell" style="width:110px;">
    <button type="button" class="btn btn-sm btn-success saveStudentBtn">
      <i class="fas fa-save"></i> Save
    </button>
  </td>
</tr>
