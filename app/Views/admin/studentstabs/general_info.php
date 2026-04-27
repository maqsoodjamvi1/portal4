<?php $db = \Config\Database::connect(); ?>
<?= form_open_multipart('c=students&m=save_generalinfo', ['id' => 'students-edit-form-generalinfo']) ?>
<?= form_hidden('id', $id) ?>
<?= form_hidden('campus_id', $campus_id) ?>

<input type="hidden" class="student_id" name="student_id" value="<?= esc($id) ?>">
  
 <div class="card card-primary">
  <div class="card-header">
    <h3 class="card-title">General Student Information</h3>
  </div>
  <div class="card-body">
    <div class="form-row">
      <!-- Date of Birth -->
      <div class="form-group col-md-6 col-lg-4">
        <?php
          if (!empty($date_of_birth) && $date_of_birth != 0) {
              $date_of_birth = DateTime::createFromFormat('Y-m-d', $date_of_birth)->format('d/m/Y');
          } else {
              $date_of_birth = date('d/m/Y');
          }
        ?>
        <label>Date of Birth <span class="text-danger">*</span></label>
        <div class="input-group date" id="dobdatepicker" data-target-input="nearest">
          <input type="text" name="date_of_birth" class="form-control datetimepicker-input" value="<?= $date_of_birth ?>" data-target="#dobdatepicker" />
          <div class="input-group-append" data-target="#dobdatepicker" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
          </div>
        </div>
      </div>

      <!-- Previous School -->
      <div class="form-group col-md-6 col-lg-4">
        <label for="previous_school">Previous School</label>
        <input type="text" class="form-control" name="previous_school" value="<?= $previous_school ?>">
      </div>

      <!-- Previous City -->
      <div class="form-group col-md-6 col-lg-4">
        <label for="ps_city">Previous City</label>
        <input type="text" class="form-control" name="ps_city" value="<?= $ps_city ?>">
      </div>

      <!-- Hear Source -->
      <div class="form-group col-md-6 col-lg-4">
        <label for="hear_source">Hear Source</label>
        <input type="text" class="form-control" name="hear_source" value="<?= $hear_source ?>">
      </div>

      <!-- Health Conditions -->
      <div class="form-group col-md-6 col-lg-6">
        <label for="health_conditions">Health Conditions</label>
        <textarea class="form-control" name="health_conditions"><?= $health_conditions ?></textarea>
      </div>

      <!-- Major Injuries -->
      <div class="form-group col-md-6 col-lg-6">
        <label for="major_injuries">Major Injuries</label>
        <textarea class="form-control" name="major_injuries"><?= $major_injuries ?></textarea>
      </div>

      <!-- Profile Photo Upload -->
      <div class="form-group col-md-6 col-lg-4">
        <label>Upload Student Photo</label>
        <input type="file" class="form-control-file" name="image">
        <input type="hidden" name="image" value="<?= $profile_photo ?>">
      </div>

      <!-- Preview -->
      <div class="form-group col-md-6 col-lg-4 text-center">
        <?php if ($profile_photo): ?>
          <img src="<?= base_url('uploads/' . $profile_photo) ?>" class="img-thumbnail" height="100">
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Attachments Section -->
<div class="card card-secondary">
  <div class="card-header">
    <h3 class="card-title">Student Attachments</h3>
  </div>
  <div class="card-body">
    <div class="row">
      <?php foreach ($attachementTypesInfo as $value):
        $attachment_id = 0;
        $attachementsinfo = $db->table('attachements')
                               ->where('student_id', $id)
                               ->where('a_type_id', $value->a_type_id)
                               ->get()->getRow(); ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <label class="font-weight-bold"><?= esc($value->a_type_name) ?></label>
        <input type="hidden" class="a_type_id<?= $value->a_type_id ?>" value="<?= $value->a_type_id ?>">
        <input type="hidden" class="attachement_id<?= $value->a_type_id ?>" value="<?= $attachment_id ?>">

        <div class="custom-file mb-2">
          <input type="file" class="custom-file-input" id="thumbnail<?= $value->a_type_id ?>">
          <label class="custom-file-label" for="thumbnail<?= $value->a_type_id ?>">Choose file</label>
        </div>
        <div class="text-center">
          <img id="imgthumbnail<?= $value->a_type_id ?>" src="<?= isset($attachementsinfo) ? base_url('studentattachements/' . $attachementsinfo->attachement_path) : '' ?>" class="img-thumbnail" style="max-height: 100px;">
        </div>
      </div>
       <script>
        document.addEventListener('DOMContentLoaded', function () {
          const input = document.getElementById('thumbnail<?= $value->a_type_id ?>');
          input.addEventListener('change', function () {
            const file = this.files[0];
            const formData = new FormData();
            formData.append('file', file);
            formData.append('a_type_id', document.querySelector('.a_type_id<?= $value->a_type_id ?>').value);
            formData.append('student_id', document.querySelector('.student_id').value);
            formData.append('attachement_id', document.querySelector('.attachement_id<?= $value->a_type_id ?>').value);

            fetch("<?= site_url('students/save_attachment') ?>", {
              method: "POST",
              body: formData,
            }).then(response => response.json())
              .then(json => {
                if (json.success) {
                  toastr.success(json.msg);
                } else {
                  toastr.error('Update failed.');
                }
              });

            const reader = new FileReader();
            reader.onload = e => {
              document.getElementById("imgthumbnail<?= $value->a_type_id ?>").src = e.target.result;
            };
            reader.readAsDataURL(file);
          });
        });
      </script>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Submit Buttons -->
<div class="text-center mb-4">
  <button type="submit" id="submitBtn" class="btn btn-success px-4">Save</button>
  <button type="reset" class="btn btn-secondary px-4">Reset</button>
  <button type="button" class="btn btn-light px-4" onclick="history.go(-1);">Cancel</button>
</div>

<?= form_close() ?>

<script>
$(function () {
  $('#dobdatepicker').datetimepicker({ format: 'DD/MM/YYYY' });

  $('#students-edit-form-generalinfo').validate({
    rules: { date_of_birth: { required: true } },
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

  $('#students-edit-form-generalinfo').ajaxForm({
    beforeSubmit: function () {
      $('#submitBtn').text('Saving...').prop('disabled', true);
      return $('#students-edit-form-generalinfo').valid();
    },
    success: function (response) {
      $('#submitBtn').text('Save').prop('disabled', false);
      const json = typeof response === 'string' ? JSON.parse(response) : response;
      if (json.success) {
        toastr.success(json.msg);
      } else {
        toastr.error(json.msg || 'Error occurred.');
      }
    }
  });
});
</script>
