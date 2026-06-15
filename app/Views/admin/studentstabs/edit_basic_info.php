<?= form_open_multipart(site_url('admin/students/update_basicinfo'), ['role' => 'form', 'id' => 'students-edit-form-basicinfo', 'method' => 'POST']); ?>

<input type="hidden" name="student_id" value="<?= (int)$id ?>">
<input type="hidden" name="campus_id" value="<?= (int)$campus_id ?>">
<input type="hidden" name="parent_id" value="<?= (int)$parent_id ?>">

<div class="card shadow-sm mb-3">
  <div class="card-header bg-primary text-white">
    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> Edit Student Information</h5>
  </div>
  <div class="card-body">

    <!-- Student Information Section -->
    <h6 class="mb-3 text-primary"><i class="fas fa-user me-2"></i> Student Details</h6>
    <div class="row">
      <div class="form-group col-md-3">
        <label for="reg_no">Registration No</label>
        <input type="text" readonly class="form-control" name="reg_no" id="reg_no" value="<?= $reg_no ?>">
      </div>

      <div class="form-group col-md-6">
        <label for="full_name">Full Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="full_name" id="full_name" 
               value="<?= htmlspecialchars($full_name ?? '') ?>" 
               placeholder="Enter full name (First Last)">
        <input type="hidden" name="first_name" id="first_name_hidden" value="<?= $first_name ?? '' ?>">
        <input type="hidden" name="last_name" id="last_name_hidden" value="<?= $last_name ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label>Gender</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="gender" value="male" <?= ($gender ?? '') == "male" ? 'checked' : '' ?>>
          <label class="form-check-label">Male</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="gender" value="female" <?= ($gender ?? '') == "female" ? 'checked' : '' ?>>
          <label class="form-check-label">Female</label>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-3">
        <label for="gr_no">GR Number</label>
        <input type="text" class="form-control" name="gr_no" value="<?= $gr_no ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="gr_date">GR Date</label>
        <input type="text" class="form-control datepicker" name="gr_date" value="<?= $gr_date_dmy ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="date_of_admission">Admission Date</label>
        <input type="text" class="form-control datepicker" name="date_of_admission" value="<?= $date_of_admission_dmy ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="date_of_birth">Date of Birth</label>
        <input type="text" class="form-control datepicker" name="date_of_birth" value="<?= $date_of_birth_dmy ?? '' ?>">
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-3">
        <label for="cls_sec_id">Class Section <span class="text-danger">*</span></label>
        <select class="form-control" name="cls_sec_id" id="cls_sec_id" required>
          <option value="">-- Select Class Section --</option>
          <?php foreach ($sectionsclassinfo as $row): ?>
            <option value="<?= $row['cls_sec_id'] ?>" 
              <?= ($row['cls_sec_id'] == $cls_sec_id) ? 'selected' : '' ?>>
              <?= esc($row['sectionclassname']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group col-md-3">
        <label for="student_cnic">CNIC / B-Form</label>
        <input type="text" class="form-control cnic-mask" name="student_cnic" value="<?= $student_cnic ?? '' ?>">
      </div>

      <div class="form-group col-md-6">
        <label for="religion">Religion</label>
        <input type="text" class="form-control" name="religion" value="<?= $religion ?? 'Islam' ?>">
      </div>
    </div>

    <!-- Parent/Guardian Information Section -->
    <h6 class="mb-3 mt-4 text-primary"><i class="fas fa-users me-2"></i> Parent / Guardian Information</h6>
    
    <div class="row">
      <div class="form-group col-md-3">
        <label for="father_cnic">Father's CNIC <span class="text-danger">*</span></label>
        <input type="text" class="form-control cnic-mask" name="father_cnic" id="father_cnic" 
               value="<?= $father_cnic ?? '' ?>" onblur="checkfathercnic()">
      </div>

      <div class="form-group col-md-3">
        <label for="f_name">Father's Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="f_name" id="f_name" value="<?= $f_name ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="father_contact">Father's Contact</label>
        <input type="text" class="form-control phone-mask" name="father_contact" id="father_contact" 
               value="<?= $father_contact ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="father_email">Father's Email</label>
        <input type="email" class="form-control" name="father_email" id="father_email" 
               value="<?= $father_email ?? '' ?>">
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-3">
        <label for="father_occupation">Father's Occupation</label>
        <input type="text" class="form-control" name="father_occupation" id="father_occupation" 
               value="<?= $father_occupation ?? '' ?>">
      </div>

      <div class="form-group col-md-9">
        <label for="father_office_address">Father's Office Address</label>
        <input type="text" class="form-control" name="father_office_address" id="father_office_address" 
               value="<?= $father_office_address ?? '' ?>">
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-3">
        <label for="m_name">Mother's Name</label>
        <input type="text" class="form-control" name="m_name" id="m_name" value="<?= $m_name ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="mother_contact">Mother's Contact</label>
        <input type="text" class="form-control phone-mask" name="mother_contact" id="mother_contact" 
               value="<?= $mother_contact ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="whatsapp_contact">WhatsApp Number</label>
        <input type="text" class="form-control phone-mask" name="whatsapp_contact" id="whatsapp_contact" 
               value="<?= $whatsapp_contact ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="caste">Caste</label>
        <input type="text" class="form-control" name="caste" id="caste" value="<?= $caste ?? '' ?>">
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-6">
        <label for="address_line1">Residential Address</label>
        <input type="text" class="form-control" name="address_line1" id="address_line1" 
               value="<?= $address_line1 ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="city">City</label>
        <input type="text" class="form-control" name="city" id="city" value="<?= $city ?? '' ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="hear_source">How did you hear about us?</label>
        <input type="text" class="form-control" name="hear_source" id="hear_source" 
               value="<?= $hear_source ?? '' ?>">
      </div>
    </div>

    <!-- Emergency Contact Section -->
    <h6 class="mb-3 mt-4 text-primary"><i class="fas fa-phone-alt me-2"></i> Emergency Contact</h6>
    
    <div class="row">
      <div class="form-group col-md-4">
        <label for="emergency_contact_person">Emergency Contact Person</label>
        <input type="text" class="form-control" name="emergency_contact_person" id="emergency_contact_person" 
               value="<?= $emergency_contact_person ?? '' ?>">
      </div>

      <div class="form-group col-md-4">
        <label for="emergency_contact">Emergency Contact Number</label>
        <input type="text" class="form-control phone-mask" name="emergency_contact" id="emergency_contact" 
               value="<?= $emergency_contact ?? '' ?>">
      </div>

      <div class="form-group col-md-4">
        <label for="relationship">Relationship</label>
        <input type="text" class="form-control" name="relationship" id="relationship" 
               value="<?= $relationship ?? '' ?>">
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-12">
        <label for="a_address">Emergency Contact Address</label>
        <input type="text" class="form-control" name="a_address" id="a_address" 
               value="<?= $emergency_address ?? '' ?>">
      </div>
    </div>

    <!-- Previous School & Health -->
    <h6 class="mb-3 mt-4 text-primary"><i class="fas fa-school me-2"></i> Previous School & Health</h6>
    
    <div class="row">
      <div class="form-group col-md-6">
        <label for="previous_school">Previous School</label>
        <input type="text" class="form-control" name="previous_school" value="<?= $previous_school ?? '' ?>">
      </div>

      <div class="form-group col-md-6">
        <label for="ps_city">Previous School City</label>
        <input type="text" class="form-control" name="ps_city" value="<?= $ps_city ?? '' ?>">
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-6">
        <label for="health_conditions">Health Conditions</label>
        <textarea class="form-control" name="health_conditions" rows="2"><?= $health_conditions ?? '' ?></textarea>
      </div>

      <div class="form-group col-md-6">
        <label for="major_injuries">Major Injuries</label>
        <textarea class="form-control" name="major_injuries" rows="2"><?= $major_injuries ?? '' ?></textarea>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-12 text-end">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Student</button>
        <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
      </div>
    </div>

  </div>
</div>
<?= form_close(); ?>

<script>
$(function(){
  $('.datepicker').datepicker({ format: 'dd/mm/yyyy', autoclose: true });
  $('.cnic-mask').inputmask('99999-9999999-9');
  $('.phone-mask').inputmask('+99 999 9999999');
  
  // Split full name on submit
  $('#students-edit-form-basicinfo').on('submit', function() {
    var fullName = $('#full_name').val().trim();
    if (fullName) {
      var parts = fullName.split(' ');
      $('#first_name_hidden').val(parts[0]);
      $('#last_name_hidden').val(parts.slice(1).join(' '));
    }
    return true; // Allow normal form submission
  });

  // Simple validation
  $('#students-edit-form-basicinfo').validate({
    rules: {
      full_name: { required: true },
      father_cnic: { required: true },
      f_name: { required: true }
    },
    messages: {
      full_name: { required: 'Full name is required' },
      father_cnic: { required: 'Father CNIC is required' },
      f_name: { required: 'Father name is required' }
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
      error.addClass('invalid-feedback');
      element.closest('.form-group').append(error);
    },
    highlight: function (element) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function (element) {
      $(element).removeClass('is-invalid');
    }
  });
});
</script>