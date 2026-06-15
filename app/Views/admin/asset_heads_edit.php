<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	{
		$header = 'Add Asset Heads';
		$id = '';
		$fee_type_name = '';
		$fee_type_detail = '';
	}
?>
   <?= view('components/page_header', [
    'title' => 'Asset Heads',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Asset Heads', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		  <div class="card card-primary card-outline card-tabs">
          <div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/asset_heads') ?>">Asset Heads</a></li>
				<?php if($id == ''){ ?>
					<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/asset_heads/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
					<li class="nav-item"><a class="nav-link active" href="<?php echo '#/asset_heads?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
				<?php } ?>
			</ul>
			<div class="card-body">
			<div class="tab-content">
			<?php
			echo form_open('c=asset_heads&m=save', 'role="form" id="user-edit-form"');
			//echo form_hidden('id', $id);
			?>
			<div class="col-lg-12">
			<div class="table-responsive">  
                <table class="table table-bordered" id="dynamic_field"> 
                <thead>
			        <tr>
			            <td><label for="fee_type_name">Asset Head</label></td>
			            <td> <label for="fee_type_detail">Detail</label></td>
			        </tr>
			    </thead>	
				<?php 			
				if(!empty($info)){
				$i = 0;	
				foreach ($info as $key => $value) { //print_r($value); ?>
                    <tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->asset_head_id; ?>">
                        	<input type="text" name="head_title<?php echo $i; ?>"  value="<?php echo $value->head_title; ?>" placeholder="Head Title" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="detail<?php echo $i; ?>" value="<?php echo $value->detail; ?>" placeholder="Detail" class="form-control name_list"  /></td> 
                    </tr>
                    <?php $i++ ?>  
                	<?php } ?>
                    <?php } else { 
                    $i = 1;	
                    ?>
                    	<tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id0" value="0">
                        	<input type="text" name="head_title"  value="" placeholder="Head Title" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="detail0" value="" placeholder="Detail" class="form-control name_list"  /></td> 
                    </tr>
                     <?php } ?>
                    <tr>    <td></td><td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td>  </tr>
                </table>  
            </div>
			 
		</div>	
        <div class="form-group">
			<button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
			<button type="reset" class="btn btn-secondary">Reset</button>
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
$(document).ready(function(){      
      var i= <?php echo $i; ?>;  
      //alert(i);
   
      $('#add').click(function(){  
        
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" name="head_title'+i+'" placeholder="Head Title" class="form-control name_list" required /></td><td><input type="text" name="detail'+i+'" placeholder="Detail" class="form-control name_list"  /></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
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
			head_title:{
				required:true,
			}
		},
		messages:{ 
			head_title:{
				required:'Head Title is Required',
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
				location.href = '#/asset_heads';
			<?php
				}else{
			?>
			location.href = '#/asset_heads?m=edit&id=<?php echo $id;?>&after=edit';
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