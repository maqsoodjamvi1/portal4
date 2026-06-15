<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){
	$header = 'Edit Hostel Fee Amount';
	$id = $info->amount_id;
	$fee_type_id = $info->fee_type_id;
	$class_id = $info->class_id;
	$fee_amount = $info->amount;
}else{
	$header = 'Add Hostel Fee Amount';
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
              Hostel Fee Structure
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Hostel Fee Amount</li>
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
			echo form_open('c=h_fee_amount&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
			<div class="">
				<div class="form-group">
					<div class="row">
					<div class="float-start ms-2"><label>Choose Session</label></div>
					<div class="float-start col-sm-6">
					<?php $session_id = $this->session->userdata('member_sessionid'); ?>
					<select name="session_id" id="session_id" class="form-control col-lg-4">
						<option value="">Select Session</option>
						<?php foreach($academic_sessioninfo as $session){ ?>
						<option <?php if($session->session_id == $session_id){ ?> selected <?php } ?> value="<?php echo $session->session_id; ?>"><?php echo $session->session_name; ?></option>
						<?php } ?>
					</select>
					</div>
				</div>
			</div>
			<div class="col-lg-12">
	        	<div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
	    	</div>
			<div id="feeamountarea" class="feeamountarea"></div>
              <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary me-2">Save</button>
								<!-- <button type="reset" class="btn btn-secondary">Reset</button> -->
								<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
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

	$("#session_id").change(function(){
		var session_id = $('#session_id').val();
      $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/h_fee_amount/data'); ?>', 
            type: "POST",
            data:{session_id:session_id},
            success:function(res){
             $("#feeamountarea").html(res);
		 			   $("#loader-1").css("display", "none");
					 }
      });
});
$('#session_id').trigger("change");
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
				location.href = '#/addbulkstudents?m=add';
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