<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	{
		$header = 'Add Attachment Types';
		$id = '';
		$a_type_name = '';
		$a_type_detail = '';
	}
?>
    <!-- Content Header (Page header) -->
   <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Attachment Types
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Attachment Type</li>
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
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/attachment_types') ?>">Attachment Types</a></li>
				<?php if($id == ''){ ?>
					<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/attachment_types/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
					<li class="nav-item"><a class="nav-link active" href="<?php echo '#/attachment_types?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
				<?php } ?>
			</ul>
			<div class="card-body">
			<div class="tab-content">
			<?php
			echo form_open('c=attachment_types&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
			<div class="col-lg-12">
			<div class="table-responsive">  
                <table class="table table-bordered" id="dynamic_field"> 
                <thead>
			        <tr>
			            <td><label for="fee_type_name">Attachment Type Name</label></td>
			            <td> <label for="fee_type_detail">Attachment Type Detail</label></td>
			        </tr>
			    </thead>	
				<?php 
				$i = 0;
				foreach ($info as $key => $value) { //print_r($value); ?>
                    <tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->a_type_id; ?>">
                        	<input type="text" name="a_type_name<?php echo $i; ?>"  value="<?php echo $value->a_type_name; ?>" placeholder="Attachment Name" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="a_type_detail<?php echo $i; ?>" value="<?php echo $value->a_type_detail; ?>" placeholder="Detail" class="form-control name_list"  /></td> 
                    </tr>
                    <?php $i++ ?>  
                    <?php } ?>
                    <tr>    <td></td><td></td><td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td>  </tr>
                </table>  
             
            </div>
			 
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
$(document).ready(function(){      
      var i= <?php echo $i; ?>;  
    
      $('#add').click(function(){  
        
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" name="a_type_name'+i+'" placeholder="Attachment Type Name" class="form-control name_list" required /></td><td><input type="text" name="a_type_detail'+i+'" placeholder="Attachment Type Detail" class="form-control name_list"  /></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
              i++;   
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      });  
  
 }); 	
$(function(){
	
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
			$('#submitBtn').html("Ajax Request is Processing!");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Submit");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
			<?php
				if($id == ''){
					?>
				location.href = '#/attachment_types';
			<?php
				}else{
			?>
			location.href = '#/attachment_types?m=edit&id=<?php echo $id;?>&after=edit';
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