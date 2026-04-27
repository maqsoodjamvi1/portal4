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
<!-- Content Header (Page header) -->
<style type="text/css">
th{ text-align: center; }
.select2-container--default .select2-selection--single, .select2-selection .select2-selection--single{
	border: 1px solid #d2d6de;
    border-radius: 0;
    padding: 2px 12px !important;
    height: 24px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 19px !important;
    right: 3px;
}
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Students Late Comming
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students Late Comming</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
       	<div class="card-body">      
        <div class="tab-content">
		 <?php
			echo form_open('c=students_latecomming&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
		  ?>	
		 <div class="row">
		 	<div class="col-md-12 bg">
		       <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		      </div>
		  </div>
		   <input type="hidden" name="campus_id" id="campus_id" value="<?php echo $campus_id; ?>" />
		   <div class="row">
		    <div class="form-inline col-lg-12">
	            <div class="form-group ">
	              <select class="form-control select2" name="student_id" id="student_id" style="height: 24px;">
	              	 <option value="0">Select Student</option>   
	              </select>
	            </div>
	            <div class="form-group" >
	             <input type="date" name="date" id="date" readonly="readonly" required value="<?php echo date('Y-m-d'); ?>" class="form-control" style="height: 24px;line-height: 15px;padding: 0 10px;">
	           </div>
	            <div class="form-group" >
	            <button type="button" onclick="getstudents();" class="btn btn-sm btn-primary" style="height: 24px;line-height: 14px;">View</button>
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
            <button type="submit"  id="submitBtn" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-default">Reset</button>
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
          </div>
		  </div>
		  </div>
		   <?php echo form_close();?> 
		</div>
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
		var student_id = $('#student_id').val();
		var date = $('#date').val();
		
 	      $.ajax({
            url: 'admin.php?c=students_latecomming&m=get_students_byclass',
            type: "POST",
            data:{student_id: student_id,campus_id:campus_id,date:date },
            success:function(res){
 			   $("#students_list_container").html(res);
 			    $("#loader-1").css("display", "none");
 			  }
         });
 }
</script>
<script type="text/javascript">
$(function(){
$("#student_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=students_latecomming&m=get_studentinfo',
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term: term
            }
        },
       processResults: function (response) {
       	console.log(response);
              return {
                 results: response
              };
           },
           cache: true
    }
 });
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
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
					location.href = '#/students_latecomming?m=add';
					<?php
				}else{
					?>
					location.href = '#/students_latecomming?m=add';
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