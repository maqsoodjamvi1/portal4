<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info) ){
				$header = 'Edit Students Results';
				$id = $info->student_id;
				$class_id = $info->class_id;
				$subject_id = $info->sub_id;
				$obtained_marks = $info->obtained_marks;
				$total_marks = $info->Total_marks;
			}else{
				$header = 'Add Students Results';
				$id = '';
				$class_id = '';
				$subject_id = '';
				$obtained_marks = 0;
				$total_marks = 0;
			} 
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Students Results Compilation
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students Results Compilation</li>
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
        <ul class="nav nav-tabs">
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_results/add') ?>">Add Results</a></li>
		  <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_results_compilation/add') ?>"> Compile Results </a></li>
	     <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_results_card') ?>">View Results Cards</a></li>	
        </ul>
        <div class="card-body">
        <div class="tab-content">
		 <?php
			echo form_open('c=students_results_compilation&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
		<div class="row">
			<div class="col-lg-3">
				<div class="form-group">
                <label for="term">Exam</label>
				<select name="eid" id="eid" class="form-control">
				<?php foreach($examinfo as $exam){ ?>
                <option value="<?php echo $exam->eid; ?>"><?php echo $exam->exam_name;?></option>
				<?php } ?>
				</select>
			</div>
			</div>
			<div class="col-lg-2" style="text-align: right;">
				<h6 style="margin-top: 22px;font-size: 16px;">Attendance Rang</h6>
			</div>
			<div class="col-lg-3">
				<div class="form-group">
                <label for="term">Date From</label>
				<input type="date" class="form-control" name="from_date">
			</div>
			</div>
			<div class="col-lg-3">
				<div class="form-group">
                <label for="term">Date To</label>
				<input type="date" class="form-control" name="to_date">
			</div>
			</div>
		</div>
	<div class="row">          
		<div class="col-lg-6">
			<div id="students_list_container"></div>
		</div>
	<div class="col-lg-12">
		<div class="form-group">
		<button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
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
<script type="text/javascript">
$(function(){
	//$(".select").select2({closeOnSelect:false});	
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
			$('#submitBtn').html("Saving!");
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
					location.href = '#/students_results';
					<?php
				}else{
					?>
					location.href = '#/students_results?m=edit&id=<?php echo $id;?>&after=edit';
				<?php } ?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
});
</script>

<?= $this->endSection() ?>