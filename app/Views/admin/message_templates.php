<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	$schoolinfo = getSchoolInfo();
	if(isset($info)){
		//print_r($info);
		$header = 'Edit Message Templates';
		$id = $info->campus_id;
		$welcome_sms = $info->welcome_sms;
		$attendance_sms = $info->attendance_sms;
		$student_fee_sms = $info->student_fee_sms;
		$family_fee_sms = $info->family_fee_sms;
		//$result_sms = $info->result_sms;
	}else{
		$header = 'Add Message Templates';
		$id = '';
		$welcome_sms = '';
		$attendance_sms = '';
	}
?>
    <?= view('components/page_header', [
    'title' => 'Message Templates',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Message Templates', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
           <div class="card-body">
            <div class="tab-content">
              <div class="active tab-pane" id="settings">
              <?php
helper('form');

echo form_open_multipart(
    site_url('message_templates/save'),
    ['role' => 'form', 'id' => 'user-edit-form']
);

echo form_hidden('id', $id);
?>
				<div class="row"> 
				<div class="col-md-12 bg">
		        <div class="loader" id="loader-1" style="display: none;">
		          <span></span>
		          <span></span>
		          <span></span>
		          <span></span>
		        </div>
		   	 </div> 
          
		<div class="form-group col-lg-12">
          <label for="location">Welcome SMS</label>
          <textarea class="form-control" rows="4" id="welcome_sms" name="welcome_sms"><?php echo $welcome_sms; ?></textarea>
          <input type="button" value="First Name" onclick="formatTextWelcome ('first_name');" /> 
	    <input type="button" value="Last Name" onclick="formatTextWelcome ('last_name');" /> 
	    <input type="button" value="Father Name" onclick="formatTextWelcome ('father_name');" /> 
	    <input type="button" value="Date" onclick="formatTextWelcome ('date');" /> 
    <input type="button" value="Class" onclick="formatTextWelcome ('class');" /> 
 		<script type="text/javascript">
			function formatTextWelcome(tag) {
		   var Field = document.getElementById('welcome_sms');
		   var val = Field.value;
		   var selected_txt = val.substring(Field.selectionStart, Field.selectionEnd);
		   var before_txt = val.substring(0, Field.selectionStart);
		   var after_txt = val.substring(Field.selectionEnd, val.length);
		   Field.value += '{' + tag + '}';
		}
		</script>
		</div>
		<div class="form-group col-lg-12">
        <label for="location">Attendance SMS </label>
 				<textarea class="form-control" rows="4" id="attendance_sms" name="attendance_sms"><?php echo $attendance_sms; ?></textarea>
          
      <input type="button" value="First Name" onclick="formatText ('first_name');" /> 
	    <input type="button" value="Last Name" onclick="formatText ('last_name');" /> 
	    <input type="button" value="Father Name" onclick="formatText ('father_name');" /> 
	    <input type="button" value="Date" onclick="formatText ('date');" /> 
    <input type="button" value="Class" onclick="formatText ('class');" /> 
 		<script type="text/javascript">
			function formatText(tag) {
		   var Field = document.getElementById('attendance_sms');
		   var val = Field.value;
		   var selected_txt = val.substring(Field.selectionStart, Field.selectionEnd);
		   var before_txt = val.substring(0, Field.selectionStart);
		   var after_txt = val.substring(Field.selectionEnd, val.length);
		   Field.value += '{' + tag + '}';
		}
		</script>
		</div>
		<div class="form-group col-lg-12">
          <label for="location">Student Fee SMS </label>
          <textarea class="form-control" rows="4" id="student_fee_sms" name="student_fee_sms"><?php echo $student_fee_sms; ?></textarea>
          
      <input type="button" value="First Name" onclick="formatTextStdFee ('first_name');" /> 
	    <input type="button" value="Last Name" onclick="formatTextStdFee ('last_name');" /> 
	    <input type="button" value="Father Name" onclick="formatTextStdFee ('father_name');" /> 
	    <input type="button" value="Date" onclick="formatTextStdFee ('date');" /> 
    <input type="button" value="Class" onclick="formatTextStdFee ('class');" /> 
 		<script type="text/javascript">
			function formatTextStdFee(tag) {
		   var Field = document.getElementById('student_fee_sms');
		   var val = Field.value;
		   var selected_txt = val.substring(Field.selectionStart, Field.selectionEnd);
		   var before_txt = val.substring(0, Field.selectionStart);
		   var after_txt = val.substring(Field.selectionEnd, val.length);
		   Field.value += '{' + tag + '}';
		}
		</script>
		</div>
		<div class="form-group col-lg-12">
          <label for="location">Family Fee SMS </label>
          <textarea class="form-control" rows="4" id="family_fee_sms" name="family_fee_sms"><?php echo $family_fee_sms; ?></textarea>
          
      <input type="button" value="First Name" onclick="formatTextFFee ('first_name');" /> 
	    <input type="button" value="Last Name" onclick="formatTextFFee ('last_name');" /> 
	    <input type="button" value="Father Name" onclick="formatTextFFee ('father_name');" /> 
	    <input type="button" value="Date" onclick="formatTextFFee ('date');" /> 
    <input type="button" value="Class" onclick="formatTextFFee ('class');" /> 
 		<script type="text/javascript">
			function formatTextFFee(tag) {
		   var Field = document.getElementById('family_fee_sms');
		   var val = Field.value;
		   var selected_txt = val.substring(Field.selectionStart, Field.selectionEnd);
		   var before_txt = val.substring(0, Field.selectionStart);
		   var after_txt = val.substring(Field.selectionEnd, val.length);
		   Field.value += '{' + tag + '}';
		}
		</script>
		</div>
		<div class="form-group col-lg-12">
      <!-- <label for="location">Result SMS </label>
      <textarea class="form-control" rows="4" id="result_sms" name="result_sms"><?php //echo $result_sms; ?></textarea> -->
          
      <!-- <input type="button" value="First Name" onclick="formatTextResult ('first_name');" /> 
	    <input type="button" value="Last Name" onclick="formatTextResult ('last_name');" /> 
	    <input type="button" value="Father Name" onclick="formatTextResult ('father_name');" /> 
	    <input type="button" value="Date" onclick="formatTextResult ('date');" /> 
    <input type="button" value="Class" onclick="formatTextResult ('class');" /> 
 		<script type="text/javascript">
			function formatTextResult(tag) {
		   var Field = document.getElementById('result_sms');
		   var val = Field.value;
		   var selected_txt = val.substring(Field.selectionStart, Field.selectionEnd);
		   var before_txt = val.substring(0, Field.selectionStart);
		   var after_txt = val.substring(Field.selectionEnd, val.length);
		   Field.value += '{' + tag + '}';
		}
		</script> -->
		</div>
	  <div class="col-lg-12">
      <div class="form-group">
      <button type="submit" id="saveProfile" class="btn btn-primary">Save</button>
			<button type="reset" class="btn btn-secondary">Reset</button>
			<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
      </div>
      </div>
     </div>     
    </form>
  </div>
  <!-- /.tab-pane -->
   </div>
</div>
    <!-- /.tab-content -->
  </div>
  <!-- /.nav-tabs-custom -->
</div>
<!-- /.col -->
</div>
<!-- /.row -->
</section>
<script>
	var loadFile = function(event) {
		var image = document.getElementById('output');
		image.src = URL.createObjectURL(event.target.files[0]);
	};
</script>
<script type="text/javascript">
	$(function(){
	 $("#saveProfile").click(function(){
		    $("#loader-1").css("display", "block");
		});	
		
	$(".select2").select2({closeOnSelect:false});
	$('[data-mask]').inputmask();
	$('#user-edit-form').validate({
		rules:{
			email:{
				required:true,
				email:true,
			} 
			
		},
		messages:{
			email:{
				required:'Email is Required',
				email:'Invalid Email',
			}
		}
	});	
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			if(json.success){
				$("#loader-1").css("display", "none");
				toastr.success(json.msg);
				location.href = '#/message_templates';
							
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});	
});				
</script>

<?= $this->endSection() ?>