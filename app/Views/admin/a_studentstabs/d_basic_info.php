<?= form_open_multipart('c=a_students&m=save_basicinfo', ['role' => 'form', 'id' => 'students-edit-form-basicinfo']); ?>
<?= form_hidden('id', $id); ?>
<?= form_hidden('campus_id', $campus_id); ?>
<input type="hidden" name="parent_id" id="parent_id" value="<?= $parent_id ?>" />

<div class="card shadow-sm mb-3">
  <div class="card-header bg-primary text-white">
    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> Basic Student Information</h5>
  </div>
  <div class="card-body">

    <div class="row">
      <div class="form-group col-md-3">
        <label for="reg_no">Registration No</label>
        <input type="text" readonly class="form-control" name="reg_no" id="reg_no" value="<?= $reg_no ?>">
        <input type="hidden" id="originalreg_no" value="<?= $reg_no ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="first_name">First Nameeeee <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="first_name" id="first_name" value="<?= $first_name ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="last_name">Last Nameeeeeee</label>
        <input type="text" class="form-control" name="last_name" id="last_name" value="<?= $last_name ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="gender">Gender</label><br>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" <?= $gender == "male" ? 'checked' : '' ?>>
          <label class="form-check-label" for="gender_male">Male</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female" <?= $gender == "female" ? 'checked' : '' ?>>
          <label class="form-check-label" for="gender_female">Female</label>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-3">
        <label for="father_cnic">Father CNIC <i class="fas fa-info-circle text-secondary" title="Enter CNIC to auto-fetch parent info"></i></label>
        <input type="text" class="form-control" name="father_cnic" id="father_cnic" onkeyup="checkfathercnic()" value="<?= $father_cnic ?>" data-inputmask='"mask": "99999-9999999-9"' data-mask>
      </div>

      <div class="form-group col-md-3">
        <label for="f_name">Father Name</label>
        <input type="text" class="form-control" name="f_name" id="f_name" value="<?= $f_name ?>">
      </div>

      <div class="form-group col-md-3">
        <label for="religion">Religion</label>
        <input type="text" class="form-control" name="religion" id="religion" value="<?= empty($religion) ? 'Islam' : $religion ?>">
      </div>

      <div class="form-group col-md-3">
        <?php
        $formattedDate = !empty($date_of_admission) && $date_of_admission != 0
          ? date("d/m/Y", strtotime($date_of_admission))
          : date("d/m/Y");
        ?>
        <label for="datepicker2">Date Of Admission</label>
        <div class="input-group date" id="datepicker2" data-target-input="nearest">
          <input type="text" class="form-control datetimepicker-input" name="date_of_admission" value="<?= $formattedDate ?>" data-bs-target="#datepicker2" />
          <span class="input-group-text" data-bs-target="#datepicker2" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="form-group col-md-12 text-end">
        <button type="submit" id="submitBtn" class="btn btn-primary studentsubmit"><i class="fas fa-save me-1"></i> Save</button>
        <button type="reset" class="btn btn-secondary"><i class="fas fa-undo me-1"></i> Reset</button>
        <button type="button" class="btn btn-light" onclick="history.go(-1);"><i class="fas fa-times me-1"></i> Cancel</button>
      </div>
    </div>

  </div>
</div>
<?= form_close(); ?>


<script>
$(function(){
  $('[data-mask]').inputmask();

  $('#datepicker2').datetimepicker({
    format: 'L'
  });

  $('#students-edit-form-basicinfo').validate({
    rules:{
      first_name: { required: true },
      father_cnic: { required: true }
    },
    messages:{
      father_cnic: { required: 'Father CNIC is required' }
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

  $('#students-edit-form-basicinfo').ajaxForm({
    beforeSubmit: function() {
      return $('#students-edit-form-basicinfo').valid();
      $('#submitBtn').html("Saving...").prop('disabled', true);
    },
    success: function(responseText) {
      $('#submitBtn').html("Save").prop('disabled', false);
      const json = $.parseJSON(responseText);
      if (json.success) {
        toastr.success(json.msg);
        location.href = '#/a_students?m=edit&id=' + json.student_id;
      } else {
        toastr.error(json.msg);
      }
    }
  });
});
</script>


<style>
  .form-group label {
    font-weight: 600;
  }
  .card-header h5 {
    margin-bottom: 0;
  }
</style>
