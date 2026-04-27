<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){
	$header = 'Edit Bill Amount';
}else{
	$header = 'Add Bill Amount';
}

$campus_id = ''; 
if(!empty($_GET['campus_id'])){
 $campus_id = $_GET['campus_id']; 
}
?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Bill Amount  
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Bill Amount</li>
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
			echo form_open('c=bill_amount&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('campus_id', $campus_id);
			?>
			<div class="">
			<div class="col-lg-12">
	        	<div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
	    	</div>
			<div id="feeamountarea" class="feeamountarea"></div>
              <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary mr-2">Save</button>
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

	//$("#plan_id").change(function(){
			//var plan_id = $('#plan_id').val();
      $("#loader-1").css("display", "block");
	    $.ajax({
            url:'<?php echo site_url('c=bill_amount&m=data&campus_id='.$campus_id);?>', 
            type: "POST",
            data:{},
            success:function(res){
		         $("#feeamountarea").html(res);
		 			   $("#loader-1").css("display", "none");
					 }
      });
//});
$('#user-edit-form').validate({
	rules:{
		amount:{
			required:true,
		}
	},
	messages:{
		amount:{
			required:'Bill amount is required',
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
				//location.href = 'admin.php#/';
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