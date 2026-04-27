<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Subject Group';
		$id = $info->sec_id;
		$class_id = $info->class_id;
		$subject_id = intval($info->subject_id);			
	}else{
		$header = 'Add Subject Group';
		$id = 0;
		$class_id = '';
		$subject_id = '';
	}
?>
<!-- Content Header (Page header) -->
	<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Subject Groups
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Subject Groups</li>
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
			echo form_open('c=a_subject_group&m=save', 'role="form" id="class-subjects-edit-form"');
			echo form_hidden('id', $id);
			?>
			<div id="subjectsection"></div>
          	<div class="form-group">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-default">Reset</button>
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
          </div>
          <?php echo form_close();?> </div>
      </div>
    </div>
  </div>
  </div>
  </div>
</section> 
<!-- /.content -->
<script type="text/javascript">
$(function(){

	 $.ajax({
            url: 'admin.php?c=a_subject_group&m=data2', 
            type: "POST",
            data:{},
            success:function(res){
            	console.log(res);
 			   $("#subjectsection").html(res);
			  }
         });

	$('#class-subjects-edit-form').validate({
		
	});
	$('#class-subjects-edit-form').ajaxForm({
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
					location.href = '#/a_subject_group?m=add';
					<?php
				}else{
					?>
					location.href = '#/a_subject_group?m=edit&id=<?php echo $id;?>&after=edit';
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