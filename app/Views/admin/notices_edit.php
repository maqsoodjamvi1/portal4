<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Notice';
		$id = $info->notice_id;
		$notice_name = $info->notice_name;
		$notice_date = $info->notice_date;
		$notice_detail = $info->notice_detail;
		$notice_audio = $info->notice_audio;
		$status = $info->status;
	}else{
		$header = 'Add Notice';
		$id = '';
		$notice_name = '';
		$notice_date = '';
		$notice_detail = '';
		$notice_audio = '';
		$status = 1;
	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Notices
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Notices</li>
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
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/notices') ?>">Notices</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/notices/add') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
			<li class="nav-item"><a class="nav-link" href="<?php echo '#/notices?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
		</ul>
<div class="card-body">		
<div class="tab-content">
	<?php
		echo form_open_multipart('c=notices&m=save', 'role="form" id="classes-edit-form"');
		echo form_hidden('id', $id);
	?>
	<div class="col-lg-6">
    <div class="form-group">
      <label for="notice_name">Notice Name</label>
      <input type="text" class="form-control" name="notice_name" id="notice_name" value="<?php echo $notice_name;?>">
	</div>
	<div class="form-group">
       <label for="notice_date">Notice Date</label>
       <input type="date" style="height: 24px !important;" class="form-control" name="notice_date" id="notice_date" value="<?php echo $notice_date;?>">
	</div>
	<div class="form-group">
       <label for="notice_detail">Detail</label>
      <textarea class="form-control" name="notice_detail" id="notice_detail"></textarea>
 	</div>
 	<div class="form-group">
       <label for="detail">Notice Audio</label>
       <input type="file" name="notice_audio" id="notice_audio" class="form-control">
 	</div>
 	<div class="form-group">
       <label for="detail">Status</label>
       <input type="checkbox" <?php if($status == 1){?>checked="checked" <?php } ?> name="status" id="status" value="1">
 	</div>
</div>
<div class="row">
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
	$('#classes-edit-form').validate({
		rules:{
			class_name:{
				required:true,
			},
			class_short_name:{
				required:true,
			}
		},
		messages:{
			class_name:{
				required:'Class is Required',
			},
			email:{
				required:'Class is Required',
			}
		}
	});
	$('#classes-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#classes-edit-form').valid();
			$('#submitBtn').html("Saving");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(JSON.stringify(responseText));
			console.log(json);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/notices';
					<?php
				}else{
					?>
					location.href = '#/notices';
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