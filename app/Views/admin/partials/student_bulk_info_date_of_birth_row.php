<?php
$first_name    = $first_name    ?? ($student->first_name    ?? '');
$last_name     = $last_name     ?? ($student->last_name     ?? '');
$student_name  = trim($first_name . ' ' . $last_name);

$date_of_birth = $date_of_birth ?? ($student->date_of_birth ?? '');

$db_status         = isset($db_status) ? (int) $db_status : (int) ($student->db_status ?? 0);
$date_of_birth_age = $date_of_birth_age ?? ($student->date_of_birth_age ?? '');
$toggleCheckedAttr = $db_status === 1 ? 'checked' : '';
?>

<tr>
  <!-- S.No + student id (S.No filled by JS) -->
  <td class="sno-cell sticky-col">
    <input type="hidden" name="student_id" value="<?= esc($student->student_id) ?>">
  </td>

  <!-- Student Name -->
  <td class="student-name-cell sticky-col-2">
    <?= esc($student_name) ?>
  </td>

  <!-- Date of Birth -->
  <td data-col="date_of_birth">
    <input type="date"
           class="form-control form-control-sm"
           name="date_of_birth"
           value="<?= esc($date_of_birth) ?>">
  </td>

  <!-- DOB Status Toggle (db_status) -->
  <td data-col="db_status" class="text-center">
    <div class="form-check form-switch">
      <input type="checkbox"
             class="form-check-input dob-status-toggle"
             id="dbs_<?= esc($student->student_id) ?>"
             <?= $toggleCheckedAttr ?>>
      <label class="form-check-label" for="dbs_<?= esc($student->student_id) ?>"></label>
    </div>

    <!-- hidden actual value sent to server -->
    <input type="hidden"
           name="db_status"
           class="db-status-hidden"
           value="<?= esc($db_status) ?>">
  </td>

  <!-- Date of Birth (Age-based field) as DATE PICKER -->
  <td data-col="date_of_birth_age">
    <input type="date"
           class="form-control form-control-sm dob-age-input"
           name="date_of_birth_age"
           value="<?= esc($date_of_birth_age) ?>"
           <?= $db_status === 1 ? '' : 'disabled' ?>>
  </td>

  <!-- Action -->
  <td class="text-end">
    <button type="button" class="btn btn-sm btn-success saveStudentBtn">
      Save
    </button>
  </td>
</tr>