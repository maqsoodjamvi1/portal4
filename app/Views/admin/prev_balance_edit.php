<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Add Previous Balance';
		$id = 0;
		$cls_sec_id = '';
		$tid = '';		
		
	}else{
		$header = 'Add Previous Balance';
		$id = 0;
		$cls_sec_id = '';
		$tid = '';
		
	}
?>
<!-- Content Header (Page header) -->   
 <section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Previous Balance
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Previous Balance</li>
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
			echo form_open('c=prev_balance&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
			<div class="prevbalance" id="prevbalance">		
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
    	 $.ajax({
            url: 'admin.php?c=prev_balance&m=data', 
            type: "POST",
            data:{},
            success:function(res){
            	console.log(res);
 			   $("#prevbalance").html(res);
			  }
         });
   $('#user-edit-form').validate({
		
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
					location.href = '#/teacher_section?m=add';
					<?php
				}else{
					?>
					location.href = '#/teacher_section?m=edit&id=<?php echo $id;?>&after=edit';
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