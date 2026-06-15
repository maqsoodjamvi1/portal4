  <?php echo form_open_multipart('c=a_students&m=save_generalinfo', 'role="form" id="students-edit-form-generalinfo"');
        echo form_hidden('id', $id);
        echo form_hidden('campus_id', $campus_id);
  ?>
  <!-- <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>"  /> -->
  <div class="row">
			<div class="col-lg-3">
            <div class="form-group">
              <label>Date Of Birth</label>
      			  <?php 
      			  if(!empty($date_of_birth)  && $date_of_birth != 0){
      			 			$date_of_birth = DateTime::createFromFormat('Y-m-d',$date_of_birth);
      			  		$date_of_birth = $date_of_birth->format('d/m/Y');
      			  }else{
      				  $date_of_birth = '';
      			  }
      			  ?>
              <div class="input-group date" id="datepicker" data-target-input="nearest">
                <input type="text" class="form-control datetimepicker-input" data-bs-target="#datepicker"  name="date_of_birth" required value="<?php  echo $date_of_birth; ?>"/>
                <span class="input-group-text" data-bs-target="#datepicker" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
              </div>
            </div>
			</div>
      <!-- /.input group -->
			<div class="col-lg-3">
			 <div class="form-group">
              <label for="previous_school">Previous School</label>
              <input type="text" class="form-control" name="previous_school" id="previous_school" value="<?php echo $previous_school;?>">
            </div>
			</div>
				<div class="col-lg-3"> 
            <div class="form-group">
              <label for="ps_city">Previous City</label>
              <input type="text" class="form-control" name="ps_city" id="ps_city" value="<?php echo $ps_city;?>">
            </div>
			</div>
				<div class="col-lg-3">
            <div class="form-group">
              <label for="hear_source">Hear Source</label>
              <input type="text" class="form-control" name="hear_source" id="hear_source" value="<?php echo $hear_source;?>">
            </div>
          </div>
          
		 </div>	 
		 <div class="row">
		 	<div class="col-lg-3">
        <div class="form-group">
          <label for="health_conditions">Health Conditions</label>
          <textarea  class="form-control" name="health_conditions" id="health_conditions"><?php echo $health_conditions;?></textarea>
        </div>
			</div>
			<div class="col-lg-3">
        <div class="form-group">
          <label for="major_injuries">Major Injuries</label>
          <textarea  class="form-control" name="major_injuries" id="major_injuries"><?php echo $major_injuries;?></textarea>
        </div>
			</div>
			<div class="col-lg-3">
          <div class="form-group">
            <label for="profile_photo">Profile Photo</label>
            <input type="file" name="image" size="20" value="<?php echo $profile_photo; ?>" />
            <br>
            <input type="hidden" name="image" size="20" value="<?php echo $profile_photo; ?>" />
          </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
            <?php if($profile_photo){ ?>
              <img src="<?php echo base_url();?>uploads/<?php echo $profile_photo; ?>" height="100px" >
            <?php } ?>
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
  $('#students-edit-form-generalinfo').validate({
    rules:{
       full_name:{  // Change from first_name/last_name to full_name
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

  $('#students-edit-form-generalinfo').ajaxForm({
    beforeSubmit:function(formData, jqForm, options){
    return $('#students-edit-form-generalinfo').valid();
    $('#submitBtn').html("Ajax Request is Processing!");
    $('#submitBtn').prop('disabled', true);
   },
   success:function(responseText, statusText, xhr, form){
      $('#submitBtn').html("Submit");
      $('#submitBtn').prop('disabled', false);
      var json = $.parseJSON(JSON.stringify(responseText));
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