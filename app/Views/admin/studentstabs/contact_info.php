
<?= form_open(base_url('admin/students/save_contactinfo'), ['id' => 'students-edit-form-contactinfo']) ?>
<?= form_hidden('id', $id) ?>
<?= form_hidden('campus_id', $campus_id) ?>
  
  <!-- <input type="hidden" name="parent_id" id="parent_id" value="<?php //$parent_id ?>" /> -->
  <div class="container">
  <div class="row">
    <?php
    $fields = [
      ['emergency_contact_person', 'Emergency Contact Person', $emergency_contact_person],
      ['emergency_contact', 'Emergency Contact', $emergency_contact, true],
      ['a_address', 'Emergency Address', $emergency_address],
      ['whatsapp_contact', 'Whatsapp Contact', $whatsapp_contact, true],
      ['father_contact', 'Father Contact', $father_contact, true],
      ['father_email', 'Father Email', $father_email],
      ['address_line1', 'Address Line 1', $address_line1],
      ['city', 'City', $city],
      ['father_occupation', 'Father Occupation', $father_occupation],
      ['father_office_contact', 'Father Office Address', $father_office_address],
      ['m_name', 'Mother Name', $m_name],
      ['mother_contact', 'Mother Contact', $mother_contact, true],
    ];
    foreach ($fields as $field) {
      [$name, $label, $value, $mask] = array_pad($field, 4, false);
    ?>
      <div class="col-md-6 col-lg-4 mb-3">
        <label for="<?= $name ?>"><?= $label ?></label>
        <input type="text" class="form-control" name="<?= $name ?>" id="<?= $name ?>" value="<?= $value ?>" <?= $mask ? 'data-inputmask=\'"mask": "99999999999"\' data-mask' : '' ?>>
      </div>
    <?php } ?>
  </div>

  <div class="row">
    <div class="col-12 text-center">
      <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
      <button type="reset" class="btn btn-secondary">Reset</button>
      <button type="button" class="btn btn-light" onclick="history.go(-1);">Cancel</button>
    </div>
  </div>
</div>

<?= form_close() ?>

<script src="<?= base_url('assets/js/jquery.form.min.js') ?>"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/inputmask/dist/jquery.inputmask.min.js"></script>
<script>
$(function() {
  $('[data-mask]').inputmask();

  $('#students-edit-form-contactinfo').validate({
    errorElement: 'span',
    errorPlacement: function(error, element) {
      error.addClass('invalid-feedback');
      element.closest('.form-group').append(error);
    },
    highlight: function(element) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function(element) {
      $(element).removeClass('is-invalid');
    }
  });

  $('#students-edit-form-contactinfo').ajaxForm({
    beforeSubmit: function() {
      if (!$('#students-edit-form-contactinfo').valid()) return false;
      $('#submitBtn').text("Processing...").prop('disabled', true);
    },
    success: function(response) {
      $('#submitBtn').text("Save").prop('disabled', false);
      const json = typeof response === 'string' ? JSON.parse(response) : response;
      if (json.success) {
        toastr.success(json.msg || 'Saved!');
      } else {
        toastr.error(json.msg || 'Error occurred');
      }
    },
    error: function() {
      $('#submitBtn').text("Save").prop('disabled', false);
      toastr.error('AJAX failed. Check console or network tab.');
    }
  });
});
</script>