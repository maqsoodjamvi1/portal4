<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Subject Teacher';
		$id = 0;
		$class_id = '';
		$tid = '';
		$subject_id = '';
		
	}else{
		$header = 'Add Subject Teacher';
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
           Subjects Teacher
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Subjects Teacher</li>
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
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/a_teacher_subjects') ?>"> Subjects Teacher</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/a_teacher_subjects/add') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo '#/a_teacher_subjects?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
		</ul>
		<div class="card-body">
		<div class="tab-content">
		<?php
			echo form_open('c=a_teacher_subjects&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
		?>
		<div class="form-group">
		<label for="class">Subjects</label>
			<select class="form-control" name="sub_id" id="subject_id">
				<option value="">Select Subject</option>
			<?php if(isset($subjectinfo)){
				foreach ($subjectinfo as  $subjectvalue) { ?>
				<option <?php if($subjectvalue->sid == $subject_id) { ?> selected <?php } ?> value="<?php echo $subjectvalue->sid?>"><?php echo $subjectvalue->subject_name?></option>
				<?php } ?>
					<?php	}; ?>
			</select>
		</div>
		<div class="col-md-12 bg">
	        <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
	    </div>
		<div class="teacher_subjects_table"></div>
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
	$("#subject_id").change(function(){
        var subject_id = $('#subject_id').val();
        $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/a_teacher_subjects/data'); ?>', 
            type: "POST",
            data:{subject_id:subject_id },
            success:function(res){
            	//console.log(res);
 			   $(".teacher_subjects_table").html(res);
 			   $("#loader-1").css("display", "none");
			 }
         });
    });
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
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
					location.href = '#/a_teacher_subjects?m=add';
					<?php
				}else{
					?>
					location.href = '#/a_teacher_subjects?m=add';
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