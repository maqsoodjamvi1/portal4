<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info) ){

	$header = 'Edit Students Attendance';
	$id = $info->student_id;
	$class_id = $info->class_id;
	$subject_id = $info->sub_id;
	$obtained_marks = $info->obtained_marks;
	$total_marks = $info->Total_marks;
	$campus_id = $sessionData['campusid'];
	$session_id = $sessionData['sessionid'];
}else{
	$header = 'Add Students Attendance';
	$id = '';
	$class_id = '';
	$subject_id = '';
	$obtained_marks = 0;
	$total_marks = 0;
	$campus_id = $sessionData['campusid'];
	$session_id = $sessionData['sessionid'];
}
?>
<style type="text/css">
	.funkyradio div {
  clear: both;
  overflow: hidden;width: 110px;
}

.funkyradio label {
  width: 100%;
  border-radius: 3px;
  border: 1px solid #D1D3D4;
  font-weight: normal;
}

.funkyradio input[type="radio"]:empty,
.funkyradio input[type="checkbox"]:empty {
  display: none;
}

.funkyradio input[type="radio"]:empty ~ label,
.funkyradio input[type="checkbox"]:empty ~ label {
  position: relative;
  line-height: 2.5em;
  text-indent: 3.25em;
 /* margin-top: 2em;*/
  cursor: pointer;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
}

.funkyradio input[type="radio"]:empty ~ label:before,
.funkyradio input[type="checkbox"]:empty ~ label:before {
  position: absolute;
  display: block;
  top: 0;
  bottom: 0;
  left: 0;
  content: '';
  width: 2.5em;
  background: #999;
  border-radius: 3px 0 0 3px;
}

.funkyradio input[type="radio"]:hover:not(:checked) ~ label,
.funkyradio input[type="checkbox"]:hover:not(:checked) ~ label {
  color: #888;
}

.funkyradio input[type="radio"]:hover:not(:checked) ~ label:before,
.funkyradio input[type="checkbox"]:hover:not(:checked) ~ label:before {
  content: '\2714';
  text-indent: .9em;
  color: #C2C2C2;
}

.funkyradio input[type="radio"]:checked ~ label,
.funkyradio input[type="checkbox"]:checked ~ label {
  color: #777;
}

.funkyradio input[type="radio"]:checked ~ label:before,
.funkyradio input[type="checkbox"]:checked ~ label:before {
  content: '\2714';
  text-indent: .9em;
  color: #333;
  background-color: #ccc;
}

.funkyradio input[type="radio"]:focus ~ label:before,
.funkyradio input[type="checkbox"]:focus ~ label:before {
  box-shadow: 0 0 0 3px #999;
}

.funkyradio-default input[type="radio"]:checked ~ label:before,
.funkyradio-default input[type="checkbox"]:checked ~ label:before {
  color: #fff;
  background-color: rgb(60, 141, 188);
}

.funkyradio-primary input[type="radio"]:checked ~ label:before,
.funkyradio-primary input[type="checkbox"]:checked ~ label:before {
  color: #fff;
  background-color: #337ab7;
}

.funkyradio-success input[type="radio"]:checked ~ label:before,
.funkyradio-success input[type="checkbox"]:checked ~ label:before {
  color: #fff;
  background-color: #5cb85c;
}

.funkyradio-danger input[type="radio"]:checked ~ label:before,
.funkyradio-danger input[type="checkbox"]:checked ~ label:before {
  color: #fff;
  background-color: #d9534f;
}

.funkyradio-warning input[type="radio"]:checked ~ label:before,
.funkyradio-warning input[type="checkbox"]:checked ~ label:before {
  color: #fff;
  background-color: #f0ad4e;
}

.funkyradio-info input[type="radio"]:checked ~ label:before,
.funkyradio-info input[type="checkbox"]:checked ~ label:before {
  color: #fff;
  background-color: #5bc0de;
}
th{ text-align: center; }
</style>
<?= view('components/page_header', [
    'title' => $header,
    'icon' => 'fas fa-user-check',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Attendance', 'url' => base_url('admin/students_attendance')],
        ['label' => isset($info) ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
       	 <div class="card-body">  
        <div class="tab-content">
				 <?php
					echo form_open(base_url('admin/students_attendance/save'), 'role="form" id="user-edit-form"');
					echo form_hidden('id', $id);
				 ?>	
		 <div class="row">
		 	<div id="loader-1" class="overlay col-md-12 text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		   <input type="hidden" name="campus_id" id="campus_id" value="<?php echo $campus_id; ?>" />
		    <div class="d-flex flex-wrap align-items-center col-lg-6 col-lg-offset-3">
	            <div class="form-group float-start">
	              <select class="form-control select2"  style="height: 25px;padding: 0 5px;" name="section_id" id="section_id">
	              	 <option value="0">Select Section</option>
	                <?php if(isset($sectionsclassinfo)){
						          foreach ($sectionsclassinfo as  $secionvalue) { ?>
	                       <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
	              	<?php } ?>
	                <?php } ?>
	              </select>
	            </div>
	            <div class="form-group  float-start" style="margin-left: 15px;">
	             <input type="date" name="date" id="date" required value="<?php echo date('Y-m-d'); ?>" class="form-control" style="height: 24px;line-height: 15px;padding: 0 10px;">
	           </div>
	            <div class="form-group  float-start"  style="margin-left: 15px;">
	            <button type="button" onclick="getstudents();" class="btn btn-sm btn-primary" style="height: 24px;line-height: 10px;">View</button>
	           </div>
	          </div> 
		 </div>
		 <br>
		  <div class="row">          
		  <div class="col-lg-12">
         <div id="students_list_container" ></div>
		 </div>
		  <div class="col-lg-12">
		  	
          <div class="form-group">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
            <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
          </div>
           
		  </div>
		 
		  </div>
		   <?php echo form_close();?> 
		</div>
		</div>
      </div>
    </div>
  </div>
</section>
<!-- /.content -->
<script>
function getstudents() {
 		$("#loader-1").css("display", "block");
		var campus_id = $('#campus_id').val();
		var section_id = $('#section_id').val();
		var date = $('#date').val();
		
 	      $.ajax({
            url: '/admin/students_attendance/get_students_byclass',
            type: "POST",
            data:{section_id: section_id,campus_id:campus_id,date:date },
            success:function(res){
 			   $("#students_list_container").html(res);
 			    $("#loader-1").css("display", "none");
 			  }
         });
 }
</script>
<script type="text/javascript">
$(function(){
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			$('#submitBtn').html("Saving");
			$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Save");
			$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '/admin/students_absentees/add';
					<?php
				}else{
					?>
					location.href = '/admin/students_absentees/add';
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

<?= $this->endSection() ?>