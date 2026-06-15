<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info) ){

		$header = 'Edit Employee Attendance';
		$id = $info->student_id;
		$class_id = $info->class_id;
		$subject_id = $info->sub_id;
		$obtained_marks = $info->obtained_marks;
		$total_marks = $info->Total_marks;
		$campus_id = $sessionData['campusid'];
		$session_id = $sessionData['sessionid'];
	}else{
		$header = 'Add Employee Attendance';
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
<?= view('components/page_header', [
    'title' => 'Employees Leaves',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Employees Leaves', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
        <div class="card-body">  
        <div class="tab-content">
		 <?php
			echo form_open(base_url('admin/employee_leaves/save'), 'role="form" id="user-edit-form"');
			echo form_hidden('id', (string)$id);
		  ?>	
		 <div class="row">
		 	<div class="col-md-12 bg">
		         <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		      </div>
		   <input type="hidden" name="campus_id" id="campus_id" value="<?php echo $campus_id; ?>" />
		    <div class="d-flex flex-wrap align-items-center col-lg-12">
	            <div class="form-group float-start">
	              <select class="form-control select2" name="emp_id" id="emp_id" style="height: 24px;">
	              	 <option value="0">Select Employee</option>
	               
	              </select>
	            </div>
	            <div class="form-group  float-start" style="margin-left: 15px;">
	             <input type="date" readonly="readonly" name="date" id="date" required value="<?php echo date('Y-m-d'); ?>" class="form-control" style="height: 24px;line-height: 15px;padding: 0 10px;">
	           </div>
	            <div class="form-group  float-start"  style="margin-left: 15px;">
	            <button type="button" onclick="getEmployee();" class="btn btn-sm btn-primary" style="height: 24px;line-height: 10px;">View</button>
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
function getEmployee() {
 		$("#loader-1").css("display", "block");
		var campus_id = $('#campus_id').val();
		var emp_id = $('#emp_id').val();
		var date = $('#date').val();
		
 	      $.ajax({
            url: '/admin/employee_leaves/get_employee',
            type: "POST",
            data:{emp_id: emp_id,campus_id:campus_id,date:date },
            success:function(res){
 			   $("#students_list_container").html(res);
 			    $("#loader-1").css("display", "none");
 			  }
         });
 }
</script>
<script type="text/javascript">
$(function(){
$("#emp_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: '/admin/employee_leaves/get_employeeinfo', 
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
					location.href = '/admin/employee_leaves/add';
					<?php
				}else{
					?>
					location.href = '/admin/employee_leaves/add';
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