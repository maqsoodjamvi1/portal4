<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php

	if(isset($info)){
		$header = 'Edit Groups';
		// $id = $info->section_id;
		// $section_name = $info->section_name;
		// $short_name = $info->short_name;
	}else{

		$header = 'Add Groups';
		$id = '';
		$section_name = '';
		$short_name ='';
	}

?>
     <?= view('components/page_header', [
    'title' => 'Groups',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Groups', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		  <div class="card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/a_groups') ?>">Groups</a></li>
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/a_groups/add') ?>"><?php echo $header;?></a></li>
				
			</ul>
		<div class="card-body">		
		<div class="tab-content">
		<?php
			echo form_open('c=a_groups&m=save', 'role="form" id="user-edit-form"');
			//echo form_hidden('id', $id);
		?>
		<div class="col-lg-12">
			<div class="table-responsive">  
                <table class="table table-bordered" id="dynamic_field"> 
                <thead>
			        <tr>
			            <td><label for="subject_name">Section Name</label></td>
			            <td> <label for="subject_name">Short Name</label></td>
			        </tr>
			    </thead>	
				<?php 
				if(!empty($info)){
				$i = 0;
				foreach ($info as $key => $value) { ?>
                    <tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->group_id; ?>">
                        	<input type="text" name="group_name<?php echo $i; ?>"  value="<?php echo $value->group_name; ?>" placeholder="Section Name" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="short_name<?php echo $i; ?>" value="<?php echo $value->short_name; ?>" placeholder=" Short Name" class="form-control name_list" required="" /></td> 
                    </tr>
                    <?php $i++ ?>  
                <?php } ?>
                <?php }else{ 
                $i=1;	
                ?> 
                	<tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id0" value="0">
                        	<input type="text" name="group_name0"  value="" placeholder="Section Name" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="short_name0" value="" placeholder=" Short Name" class="form-control name_list" required="" /></td> 
                    </tr>
                <?php } ?>
                <tr><td></td> <td><button type="button" name="add" id="add" class="btn btn-success float-end">Add More</button></td>  </tr>
                </table>  
             
            </div>
			 
		</div>	
        <div class="row">
		 <div class="col-lg-12">
        <div class="form-group">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
			<button type="reset" class="btn btn-secondary">Reset</button>
			<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
        </div>
    	</div></div>
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
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" name="group_name'+i+'" placeholder="Section Name" class="form-control name_list" required /></td><td><input type="text" name="short_name'+i+'" placeholder="Short Name" class="form-control name_list" required /></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
              i++;   
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      });  
  
 });	
$(function(){
	//$(".select2").select2({closeOnSelect:false});	
$('#user-edit-form').ajaxForm({
	beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
			$('#submitBtn').html("Saving");
			$('#submitBtn').prop('disabled', true);
		},
	success:function(responseText, statusText, xhr, form){
		$('#submitBtn').html("Save");
		$('#submitBtn').prop('disabled', false);
		var json = $.parseJSON(responseText);
		
		if(json.success){
			toastr.success(json.msg);	
			location.href = '#/a_groups';
			
		}else{
			toastr.error(json.msg);
		}
		return false;
	}
	});
});
</script>

<?= $this->endSection() ?>