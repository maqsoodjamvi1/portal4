<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
{
	$header = 'Add Assets';
	$id = '';
	$cat_name = '';
	$class_id = '';
	$detail = '';
	$subject_id = '';
}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Assets
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Assets</li>
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
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/assets') ?>">Assets</a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/assets/add') ?>"><?php echo $header;?></a></li>
		<?php	}else{	?>
		<li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/assets/edit?id=') . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
		</ul>
<div class="card-body">		
<div class="tab-content">
<?php
	echo form_open( base_url('admin/assets/save'), 'role="form" id="user-edit-form"');
	echo form_hidden('id', (string)$id);
?>
<div class="">
            <div class="form-group">
              <select class="form-control" name="asset_head_id" id="asset_head_id">
              	<option value="">Select Expense Head</option>
                <?php if(isset($asset_heads)){
								foreach ($asset_heads as  $asset_head) { ?>
                <option value="<?php echo $asset_head->asset_head_id; ?>"><?php echo $asset_head->head_title; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label>Purchasing Date</label>
              <input type="date" id="purchasing_date" name="purchasing_date" class="form-control">
            </div>
        </div>
        <div class="col-md-12 bg">
		    <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		</div>
        <div id="sub_cat_list">  
                    
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
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){
	$("#purchasing_date").change(function(){
		$("#loader-1").css("display", "block");
      var asset_head_id = $('#asset_head_id').val();
      var purchasing_date = $('#purchasing_date').val();	    
	    $.ajax({
        url: '/admin/assets/get-assets',
        type: "POST",
        dataType: 'json', // Important for CI4!
        data:{asset_head_id:asset_head_id,purchasing_date:purchasing_date },
        success:function(res){
			   $("#sub_cat_list").html(res.html);
			   $("#loader-1").css("display", "none");
 				}
      });
  });
	
	
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
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
					location.href = '/admin/assets';
				<?php
				}else{
				?>
				location.href = 'admin/assets/edit?id=<?php echo $id;?>&after=edit';
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