<?php echo form_open('c=a_students&m=save_contactinfo', 'role="form" id="students-edit-form-contactinfo"');
        echo form_hidden('id', $id);
        echo form_hidden('campus_id', $campus_id);
  ?>
  <!-- <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>"  /> -->
  <div class="row">
			  <div class="col-lg-3">
			   <div class="form-group">
              <label for="emergency_contact_person">Emergency Contact Person</label>
              <input type="text" class="form-control" name="emergency_contact_person" id="emergency_contact_person" value="<?php echo $emergency_contact_person;?>" >
            </div>
        	</div>
			 <div class="col-lg-3">
			  <div class="form-group">
              <label for="emergency_contact">Emergency Contact</label>
              <input type="text" class="form-control" name="emergency_contact" id="emergency_contact" value="<?php echo $emergency_contact;?>" data-inputmask='"mask": "99999999999"' data-mask>
            </div>
			 </div> 
			  <div class="col-lg-3">
			  <div class="form-group">
              <label for="emergency_contact">Emergency Address</label>
              <input type="text" class="form-control" name="a_address" id="a_address" value="<?php echo $emergency_address;?>">
              </div>
			  </div>
         	<div class="col-lg-3"  style="width:25%; float:left;"> 
              <div class="form-group">
                <label for="whatsapp_contact">Whatsapp Contact</label>
                <input type="text" class="form-control" name="whatsapp_contact" id="whatsapp_contact" value="<?php echo $whatsapp_contact;?>" data-inputmask='"mask": "99999999999"' data-mask>
              </div>
            </div>
			 <div class="col-lg-3">
          <div class="form-group">
            <label for="father_contact">Father Contact</label>
            <input type="text" class="form-control" name="father_contact" id="father_contact" value="<?php echo $father_contact;?>" data-inputmask='"mask": "99999999999"' data-mask>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="form-group">
            <label for="father_email">Father Email</label>
            <input type="text" class="form-control" name="father_email" id="father_email" value="<?php echo $father_email;?>">
          </div>
        </div>
        <div class="col-lg-3">
          <div class="form-group">
            <label for="address_line1">Address Line1</label>
            <input type="text" class="form-control" name="address_line1" id="address_line1" value="<?php echo $address_line1;?>">
          </div>
        </div>      
      <div class="col-lg-3"> 
        <div class="form-group">
          <label for="address_line2">City</label>
          <input type="text" class="form-control" name="city" id="city" value="<?php echo $city;?>">
        </div>
      </div>
      <div class="col-lg-3">
        <div class="form-group">
          <label for="father_occupation">Father Occupation</label>
          <input type="text" class="form-control" name="father_occupation" id="father_occupation" value="<?php echo $father_occupation;?>">
        </div>
      </div>
      <div class="col-lg-3">
        <div class="form-group">
          <label for="father_office_contact">Father Office Address</label>
          <input type="text" class="form-control" name="father_office_contact" id="father_office_contact" value="<?php echo $father_office_address;?>" >
        </div>
      </div>
      <div class="col-lg-3">
        <div class="form-group">
          <label for="m_name">Mother Name</label>
          <input type="text" class="form-control" name="m_name" id="m_name" value="<?php echo $m_name;?>">
        </div>
      </div>
      <!-- Column End -->
      <div class="col-lg-3"> 
        <div class="form-group">
          <label for="mother_contact">Mother Contact</label>
          <input type="text" class="form-control" name="mother_contact" id="mother_contact" value="<?php echo $mother_contact;?>" data-inputmask='"mask": "99999999999"' data-mask>
        </div>
      </div>
      </div>
      <div class="row">
      <div class="col-lg-12 noprint">
        <div class="form-group">
          <button type="submit" id="submitBtn" class="btn btn-primary studentsubmit">Save</button>
          <button type="reset" class="btn btn-secondary">Reset</button>
          <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
        </div>
      </div>
      </div>
<?php echo form_close();?>     
<script>
$(function(){

  $('[data-mask]').inputmask();
  var dateNow = new Date();
  $('#datepicker').datetimepicker({
      format: 'DD/MM/YYYY',
      defaultDate:moment(dateNow)
    });
  
  $('#datepicker2').datetimepicker({
      format: 'L'
    });

  $('#students-edit-form-contactinfo').validate({
    rules:{
      first_name:{
        required:true,
      },
      last_name:{
        required:true,
      },
      father_cnic:{
        required:true,
      }
    },
    messages:{
      father_cnic:{
        required:'father cnic No is Required',
      }
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
      error.addClass('invalid-feedback');
      element.closest('.form-group').append(error);
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).removeClass('is-invalid');
    }
  });

  $('#students-edit-form-contactinfo').ajaxForm({
    beforeSubmit:function(formData, jqForm, options){
    return $('#students-edit-form-contactinfo').valid();
    $('#submitBtn').html("Ajax Request is Processing!");
    $('#submitBtn').prop('disabled', true);
   },
   success:function(responseText, statusText, xhr, form){
      $('#submitBtn').html("Submit");
      $('#submitBtn').prop('disabled', false);
      var json = $.parseJSON(responseText);
      console.log(json);
      if(json.success){
        toastr.success(json.msg);
        <?php
        if($id == ''){
          ?>
          location.href = '#/a_students?status=1';
          <?php
          }else{
          ?>
          location.href = '#/a_students?status=1';
          <?php
        }
        ?>
      }else{
        toastr.error(json.msg);
      }
      return false;
    }

});
});
</script>     