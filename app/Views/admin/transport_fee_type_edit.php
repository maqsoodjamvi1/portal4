<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	{
		$header = 'Add Transport Fee';
		$id = '';
		$fee_type_name = '';
		$fee_type_detail = '';
	}
?>
   <?= view('components/page_header', [
    'title' => 'Transport Fee Type',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Transport Fee Type', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		  <div class="card card-primary card-outline card-tabs">
          <div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/transport_fee_type') ?>">Fee Type</a></li>
				<?php if($id == ''){ ?>
					<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/transport_fee_type/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
					<li class="nav-item"><a class="nav-link active" href="<?php echo '#/transport_fee_type?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
				<?php } ?>
			</ul>
			<div class="card-body">
			<div class="tab-content">
			<?php
			echo form_open('c=transport_fee_type&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
			<div class="col-lg-12">
			<div class="table-responsive">  
                <table class="table table-bordered" id="dynamic_field"> 
                <thead>
			        <tr>
			            <td><label for="fee_type_name">Fee Type Name</label></td>
			            <td> <label for="fee_type_detail">Fee Type Detail</label></td>
			             <td> <label for="fee_type_detail">Is Transport Fee</label></td>
			        </tr>
			    </thead>	
				<?php 			
				if(!empty($info)){
				$i = 0;	
				foreach ($info as $key => $value) { //print_r($value); ?>
                    <tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->fee_type_id; ?>">
                        	<input type="text" name="fee_type_name<?php echo $i; ?>"  value="<?php echo $value->fee_type_name; ?>" placeholder="Fee Type Name" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="fee_type_detail<?php echo $i; ?>" value="<?php echo $value->fee_type_detail; ?>" placeholder="Detail" class="form-control name_list"  /></td> 
                        <td><input type="radio" name="is_transport_fee" value="is_transport_fee_<?php echo $i; ?>" placeholder="Detail" class="name_list" <?php if($value->is_transport_fee){ echo "checked='checked'"; }?> <?php if($isTransport){ echo "disabled"; }?>  /></td> 
                    </tr>
                    <?php $i++ ?>  
                	<?php } ?>
                    <?php } else { 
                    $i = 3;	
                    ?>
                   	<tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id0" value="0">
                        	<input type="text" name="fee_type_name0"  value="Transport Fee" placeholder="Transport Type Name" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="fee_type_detail0" value="" placeholder="Detail" class="form-control name_list"  /></td> 
                        <td><input type="radio" name="is_transport_fee" checked value="is_transport_fee_0" placeholder="Detail" class="name_list"  /></td> 
                    </tr>
                    <?php } ?>
                    <tr>    <td></td><td></td><td><button type="button" name="add" id="add" class="btn btn-success">Add Fee Type</button></td>  </tr>
                </table>  
            </div>
			 
		</div>
		<div class="col-lg-12">	
        <div class="form-group">
			<button type="submit" id="submitBtn" class="btn btn-primary me-2">Save</button>
			<!-- <button type="reset" class="btn btn-secondary me-2">Reset</button> -->
			<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
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
$(document).ready(function(){      
      var i= <?php echo $i; ?>;  
      //alert(i);
   
      $('#add').click(function(){  
        
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" name="fee_type_name'+i+'" placeholder="Fee Type Name" class="form-control name_list" required /></td><td><input type="text" name="fee_type_detail'+i+'" placeholder="Fee Type Detail" class="form-control name_list"  /></td><td><input type="radio" name="is_transport_fee" value="is_transport_fee_'+i+'" class="name_list"  /></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
              i++;   
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      });  
  
 }); 	
$(function(){
	$('#user-edit-form').validate({
		rules:{
			fee_type_name:{
				required:true,
			}
		},
		messages:{ 
			fee_type_name:{
				required:'Fee type name is Required',
				}
		}
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
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
				location.href = '#/transport_fee_type';
			<?php
				}else{
			?>
			location.href = '#/transport_fee_type?m=edit&id=<?php echo $id;?>&after=edit';
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