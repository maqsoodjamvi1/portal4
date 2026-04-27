<?php
// Safety defaults
if (!function_exists('nf')) {
  function nf($v) {
    return number_format((float)$v, 2, '.', '');
  }
}
$daycare_flag = $daycare_flag ?? ($student->daycare_flag ?? ($student->flag ?? ''));
$parent_name  = $parent_name ?? '';




// Fee helpers (expect these from controller; fallback gracefully)
$class_fee     = isset($class_fee) ? (float)$class_fee : 0.0;
$discounted    = isset($discounted_amount) ? (float)$discounted_amount : (float)($student->discounted_amount ?? 0);
if ($discounted < 0) { $discounted = 0.0; }
if ($class_fee > 0 && $discounted > $class_fee) { $discounted = $class_fee; }
$computed_student_fee = max(0.0, $class_fee - $discounted); 



// Student/parent new fields (controller should pass these; fallback to model props if present)
$first_name        = $first_name        ?? ($student->first_name        ?? '');
$last_name         = $last_name         ?? ($student->last_name         ?? '');
$date_of_admission = $date_of_admission ?? ($student->date_of_admission ?? '');
$fee_plan          = isset($fee_plan)   ? (int)$fee_plan : (int)($student->fee_plan ?? 0);
$std_cnic          = $std_cnic          ?? ($student->std_cnic          ?? '');
$std_type          = isset($std_type)   ? (int)$std_type : (int)($student->std_type ?? 0);

$father_contact    = $father_contact    ?? '';
$whatsapp          = $whatsapp          ?? '';
$mother_contact    = $mother_contact    ?? '';
$emergency_contact = $emergency_contact ?? '';
$father_cnic       = $father_cnic       ?? '';
$f_name            = $f_name            ?? ($parent_name ?: '');


?>


<tr
  data-classfee="<?= esc(number_format($class_fee, 2, '.', '')) ?>"
  data-first-name="<?= esc($first_name) ?>"
  data-last-name="<?= esc($last_name) ?>"
  data-student-name="<?= esc(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))) ?>"
>
  <td class="d-none">
    <input type="hidden" name="student_id"  value="<?= esc($student->student_id) ?>">
    <input type="hidden" name="student_fee" value="<?= esc(number_format($computed_student_fee, 2, '.', '')) ?>">
    <input type="hidden" name="class_fee"   value="<?= esc(number_format($class_fee, 2, '.', '')) ?>">
  </td>

  <td class="d-none student-name-source">
    <?= esc(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))) ?>
  </td>

  <!-- Core -->
  <td data-col="date_of_birth">
    <input type="date" class="form-control form-control-sm"
           name="date_of_birth" value="<?= esc($date_of_birth ?? ($student->date_of_birth ?? '')) ?>">
  </td>

  <td data-col="gender">
    <?php $g = strtolower((string)($gender ?? ($student->gender ?? ''))); ?>
    <select class="form-control form-control-sm" name="gender">
      <option value="">Select</option>
      <option value="male"   <?= $g === 'male'   ? 'selected' : '' ?>>Male</option>
      <option value="female" <?= $g === 'female' ? 'selected' : '' ?>>Female</option>
      <option value="other"  <?= $g === 'other'  ? 'selected' : '' ?>>Other</option>
    </select>
  </td>

  <!-- Student Type (flag) from campus flags -->
  <?php if (!empty($campus_flags) && $campus_flags->daycare_flag == 1 && $campus_flags->boarding_flag == 1): ?>
    <td data-col="flag">
      <select class="form-control form-control-sm" name="flag">
        <option value="">Select</option>
        <option value="1" <?= ($daycare_flag == '1') ? 'selected' : '' ?>>DayCare</option>
        <option value="2" <?= ($daycare_flag == '2') ? 'selected' : '' ?>>Boarding</option>
      </select>
    </td>
  <?php else: ?>
    <?php $fixedFlag = (!empty($campus_flags) && $campus_flags->daycare_flag == 1) ? 1 : ((!empty($campus_flags) && $campus_flags->boarding_flag == 1) ? 2 : null); ?>
    <td data-col="flag">
      <input type="hidden" name="flag" value="<?= esc($fixedFlag) ?>">
      <span class="badge badge-info">
        <?= $fixedFlag == 1 ? 'DayCare' : ($fixedFlag == 2 ? 'Boarding' : '—') ?>
      </span>
    </td>
  <?php endif; ?>

  <!-- Photo -->
  <td data-col="profile_photo">
    <div class="d-flex align-items-center">
      <div class="mr-2">
        <?php if (!empty($profile_photo)): ?>
          <img class="photoPreview"
               src="<?= base_url('uploads/' . $profile_photo) ?>"
               style="height:40px;width:40px;object-fit:cover;border-radius:3px;">
        <?php else: ?>
          <div class="photoPreview bg-light border"
               style="height:40px;width:40px;border-radius:3px;display:flex;align-items:center;justify-content:center;font-size:10px;color:#888;">
            No Img
          </div>
        <?php endif; ?>
      </div>
      <div class="flex-grow-1">
        <input type="file" class="form-control form-control-sm mb-1 fileInputPhoto"
               name="profile_photo" accept="image/*">
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-outline-secondary btnCaptureCrop" title="Capture from camera or upload & crop">
            <i class="fas fa-camera"></i> Capture / Crop
          </button>
          <button type="button" class="btn btn-outline-info btnCropExisting" title="Crop the selected/uploaded image">
            <i class="fas fa-crop"></i> Crop Selected
          </button>
        </div>
      </div>
    </div>
  </td>

  <!-- Students table (new) -->
  <td data-col="address">
    <input type="text" class="form-control form-control-sm"
           name="address" value="<?= esc($address ?? ($student->address ?? '')) ?>" maxlength="255">
  </td>

  <td data-col="previous_school">
    <input type="text" class="form-control form-control-sm"
           name="previous_school" value="<?= esc($previous_school ?? ($student->previous_school ?? '')) ?>" maxlength="255">
  </td>

  <td data-col="ps_city">
    <input type="text" class="form-control form-control-sm"
           name="ps_city" value="<?= esc($ps_city ?? ($student->ps_city ?? '')) ?>" maxlength="100">
  </td>

  <td data-col="health_condition">
    <input type="text" class="form-control form-control-sm"
           name="health_condition" value="<?= esc($health_condition ?? ($student->health_condition ?? '')) ?>" maxlength="255">
  </td>

  <td data-col="major_injuries">
    <input type="text" class="form-control form-control-sm"
           name="major_injuries" value="<?= esc($major_injuries ?? ($student->major_injuries ?? '')) ?>" maxlength="255">
  </td>

  <td data-col="caste">
    <input type="text" class="form-control form-control-sm"
           name="caste" value="<?= esc($caste ?? '') ?>" maxlength="100">
  </td>

  <td data-col="gr_no">
    <input type="text" class="form-control form-control-sm"
           name="gr_no" value="<?= esc($gr_no ?? ($student->gr_no ?? '')) ?>" maxlength="50">
  </td>

  <td data-col="gr_date">
    <input type="date" class="form-control form-control-sm"
           name="gr_date" value="<?= esc($gr_date ?? ($student->gr_date ?? '')) ?>">
  </td>

  <td data-col="religion">
    <input type="text" class="form-control form-control-sm"
           name="religion" value="<?= esc($religion ?? ($student->religion ?? '')) ?>" maxlength="50">
  </td>

  <td data-col="city">
    <input type="text" class="form-control form-control-sm"
           name="city" value="<?= esc($city ?? ($student->city ?? '')) ?>" maxlength="100">
  </td>

  <td data-col="hear_source">
    <input type="text" class="form-control form-control-sm"
           name="hear_source" value="<?= esc($hear_source ?? ($student->hear_source ?? '')) ?>" maxlength="100">
  </td>

  <td data-col="emergency_contact_person">
    <input type="text" class="form-control form-control-sm"
           name="emergency_contact_person" value="<?= esc($emergency_contact_person ?? ($student->emergency_contact_person ?? '')) ?>" maxlength="100">
  </td>

  <td data-col="relationship">
    <input type="text" class="form-control form-control-sm"
           name="relationship" value="<?= esc($relationship ?? ($student->relationship ?? '')) ?>" maxlength="50">
  </td>

  <!-- Parent fields (existing) -->
  <td data-col="father_email">
    <input type="email" class="form-control form-control-sm"
           name="father_email" value="<?= esc($father_email ?? '') ?>" maxlength="150">
  </td>

  <td data-col="father_occupation">
    <input type="text" class="form-control form-control-sm"
           name="father_occupation" value="<?= esc($father_occupation ?? '') ?>" maxlength="100">
  </td>

  <td data-col="father_office_address">
    <input type="text" class="form-control form-control-sm"
           name="father_office_address" value="<?= esc($father_office_address ?? '') ?>" maxlength="255">
  </td>

  <td data-col="m_name">
    <input type="text" class="form-control form-control-sm"
           name="m_name" value="<?= esc($m_name ?? '') ?>" maxlength="100">
  </td>

  <!-- Parent fields (NEW) -->
  <td data-col="father_contact">
    <input type="text" class="form-control form-control-sm"
           name="father_contact" value="<?= esc($father_contact) ?>" maxlength="20">
  </td>

  <td data-col="whatsapp">
    <input type="text" class="form-control form-control-sm"
           name="whatsapp" value="<?= esc($whatsapp) ?>" maxlength="20">
  </td>

  <td data-col="mother_contact">
    <input type="text" class="form-control form-control-sm"
           name="mother_contact" value="<?= esc($mother_contact) ?>" maxlength="20">
  </td>

  <td data-col="emergency_contact">
    <input type="text" class="form-control form-control-sm"
           name="emergency_contact" value="<?= esc($emergency_contact) ?>" maxlength="20">
  </td>

  <td data-col="father_cnic">
    <input type="text" class="form-control form-control-sm"
           name="father_cnic" value="<?= esc($father_cnic) ?>" maxlength="25">
  </td>

  <td data-col="f_name">
    <input type="text" class="form-control form-control-sm"
           name="f_name" value="<?= esc($f_name) ?>" maxlength="100">
  </td>

  <!-- Student fields (NEW) -->
  <td data-col="first_name">
    <input type="text" class="form-control form-control-sm"
           name="first_name" value="<?= esc($first_name) ?>" maxlength="100">
  </td>

  <td data-col="last_name">
    <input type="text" class="form-control form-control-sm"
           name="last_name" value="<?= esc($last_name) ?>" maxlength="100">
  </td>

  <td data-col="date_of_admission">
    <input type="date" class="form-control form-control-sm"
           name="date_of_admission" value="<?= esc($date_of_admission) ?>">
  </td>

  <!-- Shows STUDENT FEE, named discounted_amount (your JS converts on save) -->
 <td data-col="discounted_amount">
  <input type="text" class="form-control form-control-sm"
         name="discounted_amount"
         value="<?= esc(number_format($computed_student_fee, 2, '.', '')) ?>"
         data-student-fee="<?= esc(number_format($computed_student_fee, 2, '.', '')) ?>">
</td>

  <td data-col="fee_plan">
    <select name="fee_plan" class="form-control form-control-sm">
      <option value="0" <?= $fee_plan === 0 ? 'selected' : '' ?>>Monthly</option>
      <option value="1" <?= $fee_plan === 1 ? 'selected' : '' ?>>Bi-monthly</option>
      <option value="2" <?= $fee_plan === 2 ? 'selected' : '' ?>>Quarterly</option>
      <option value="3" <?= $fee_plan === 3 ? 'selected' : '' ?>>Annually</option>
    </select>
  </td>

  <td data-col="std_cnic">
    <input type="text" class="form-control form-control-sm"
           name="std_cnic" value="<?= esc($std_cnic) ?>" maxlength="25">
  </td>

  <td data-col="std_type">
    <select name="std_type" class="form-control form-control-sm">
      <option value="">Select</option>
      <option value="1" <?= $std_type === 1 ? 'selected' : '' ?>>Daycare</option>
      <option value="2" <?= $std_type === 2 ? 'selected' : '' ?>>Boarding</option>
    </select>
  </td>

<!-- Previous Month -->
<td data-col="month_prev" class="align-middle">
  <!-- hidden apply checkbox (kept for backend compatibility) -->
  <input type="checkbox" class="month-apply" 
         name="months[<?= esc($prevKey) ?>][apply]" 
         value="1" checked hidden>

  

  <input type="number" step="0.01" min="0"
         class="form-control form-control-sm month-net"
         name="months[<?= esc($prevKey) ?>][net]"
         value="<?= esc(nf($pNet)) ?>"
         data-original="<?= esc(nf($pNet)) ?>"
         placeholder="0.00">

  <input type="hidden" name="months[<?= esc($prevKey) ?>][amount]"      value="<?= esc(nf($pAmt)) ?>">
  <input type="hidden" name="months[<?= esc($prevKey) ?>][fee_type_id]" value="<?= (int) $monthly_fee_type_id ?>">
  <input type="hidden" name="months[<?= esc($prevKey) ?>][orig_net]"    value="<?= esc(nf($pNet)) ?>">
</td>

<!-- Current Month -->
<td data-col="month_curr" class="align-middle">
  <input type="checkbox" class="month-apply" 
         name="months[<?= esc($currKey) ?>][apply]" 
         value="1" checked hidden>

  

  <input type="number" step="0.01" min="0"
         class="form-control form-control-sm month-net"
         name="months[<?= esc($currKey) ?>][net]"
         value="<?= esc(nf($cNet)) ?>"
         data-original="<?= esc(nf($cNet)) ?>"
         placeholder="0.00">

  <input type="hidden" name="months[<?= esc($currKey) ?>][amount]"      value="<?= esc(nf($cAmt)) ?>">
  <input type="hidden" name="months[<?= esc($currKey) ?>][fee_type_id]" value="<?= (int) $monthly_fee_type_id ?>">
  <input type="hidden" name="months[<?= esc($currKey) ?>][orig_net]"    value="<?= esc(nf($cNet)) ?>">
</td>

<!-- Next Month -->
<td data-col="month_next" class="align-middle">
  <input type="checkbox" class="month-apply" 
         name="months[<?= esc($nextKey) ?>][apply]" 
         value="1" checked hidden>

  

  <input type="number" step="0.01" min="0"
         class="form-control form-control-sm month-net"
         name="months[<?= esc($nextKey) ?>][net]"
         value="<?= esc(nf($nNet)) ?>"
         data-original="<?= esc(nf($nNet)) ?>"
         placeholder="0.00">

  <input type="hidden" name="months[<?= esc($nextKey) ?>][amount]"      value="<?= esc(nf($nAmt)) ?>">
  <input type="hidden" name="months[<?= esc($nextKey) ?>][fee_type_id]" value="<?= (int) $monthly_fee_type_id ?>">
  <input type="hidden" name="months[<?= esc($nextKey) ?>][orig_net]"    value="<?= esc(nf($nNet)) ?>">
</td>
  <!-- Action -->
  <td class="text-right">
    <button type="button" class="btn btn-sm btn-success saveStudentBtn">Save</button>
  </td>
</tr>
