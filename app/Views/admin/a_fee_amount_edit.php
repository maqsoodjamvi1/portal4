<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){
	$header = 'Edit Fee Type';
	$id = $info->amount_id;
	$fee_type_id = $info->fee_type_id;
	$class_id = $info->class_id;
	$fee_amount = $info->amount;
}else{
	$header = 'Add Fee Type';
	$id = '';
	$fee_type_id = '';
	$class_id = '';
	$fee_amount = '';
}
?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Fee Amount
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Fee Amount</li>
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
			echo form_open('c=a_fee_amount&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
			<div class="col-lg-12">
	        	<div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
	    	</div>
			<div id="feeamountarea" class="feeamountarea"></div>
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
	    $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/a_fee_amount/data'); ?>', 
            type: "POST",
            data:{},
            success:function(res){
            	console.log(res);
 			   $("#feeamountarea").html(res);
 			   $("#loader-1").css("display", "none");
			 }
     });
$('#user-edit-form').validate({
	rules:{
		amount:{
			required:true,
		}
	},
	messages:{
		amount:{
			required:'fee amount is Required',
			}
	}
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
				
				location.reload();			
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
});
</script>

<?= $this->endSection() ?>