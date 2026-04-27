<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){		
		$header = 'Edit School Timing';
		$id = 0;
		$class_id = '';
		$tid = '';
		$subject_id = '';
		
	}else{
		$header = 'Add School Timing';
		$id = 0;
		$class_id = '';
		$tid = '';
		$subject_id = '';
	}
?>
  <!-- Content Header (Page header) -->
  <section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          School Timing
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">School Timing</li>
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
				echo form_open( base_url('/admin/school_timing/save'), 'role="form" id="user-edit-form"');
				echo form_hidden('id',  (string) $id);
			?>
			<div class="form-group">
				<?php //print_r($infoschooltimingtypes); ?>
				<select class="form-control" name="school_timing_type_id" id="school_timing_type_id">
					<option value="">School Timing Types</option>
				<?php if(isset($infoschooltimingtypes)){

					foreach ($infoschooltimingtypes as  $schooltimingtype) {
				 ?>
				<option value="<?php echo $schooltimingtype['type_id']; ?>"><?php echo $schooltimingtype['type_name']; ?></option>
				<?php } ?>
				<?php } ?>	
				</select>
			</div>
			<div class="col-md-12 bg">
		        <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		      </div>
			<div id="timetablearea" class="timetablearea">
				
			</div>
			
              <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
				<button type="reset" class="btn btn-default">Reset</button>
				<button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
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
	//$(".select2").select2({closeOnSelect:false});
	$("#school_timing_type_id").change(function(){
        var school_timing_type_id = $('#school_timing_type_id').val();
        $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/school_timing/data'); ?>', 
            type: "POST",
            data:{school_timing_type_id:school_timing_type_id },
            success:function(res){
            	//console.log(res);
 			   $("#timetablearea").html(res);
 			   $("#loader-1").css("display", "none");
			 }
         });
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
					location.href = '/admin/school_timing/add';
					<?php
				}else{
					?>
					location.href = '/admin/school_timing/add';
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