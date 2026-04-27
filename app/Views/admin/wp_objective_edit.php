<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	{
		$header = 'Add Class';
		$id = '';
		$class_name = '';
		$class_short_name = '';
		$detail = '';
	}
?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-8">
            <h1>
               Objectives
            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Objectives</li>
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
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/wp_objectives') ?>">Objectives</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/wp_objectives/add') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo '#/wp_objectives?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
			</ul>
		<div class="card-body">	
		<div class="tab-content">
		<?php
			echo form_open('c=wp_objectives&m=save', 'role="form" id="classes-edit-form"');
			echo form_hidden('id', $id);
		?>
		<div class="col-lg-12">
			<div class="table-responsive">  
                <table class="table table-bordered" id="dynamic_field"> 
                <thead>
			        <tr>
			            <td><label for="Objectives">Objectives</label></td>
			        </tr>
			    </thead>	
				<?php 
				if(!empty($info)){
				$i = 0;	
				foreach ($info as $key => $value) { ?>
                    <tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->obj_id; ?>">
                        	<input type="text" name="objective<?php echo $i; ?>"  value="<?php echo $value->objective; ?>" placeholder="Objective" class="form-control name_list" required="" /></td>  
                    </tr>
                    <?php $i++ ?>  
                    <?php } ?>
                    <?php }else{ 
                    $i = 3;	
                    ?> 
                    	<tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id0" value="0">
                        	<input type="text" name="objective0"  value="" placeholder="Objective" class="form-control name_list" required="" /></td>  
                    </tr>
                    <?php } ?>
                    <tr><td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td>  </tr>
                </table>   
             
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
$(document).ready(function(){      
      var i= <?php echo $i; ?>;  
      //alert(i);
   
      $('#add').click(function(){  
        
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" name="objective'+i+'" placeholder="Objective" class="form-control name_list" required /></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
              i++;   
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      });  
  
 }); 

$(function(){
	$('#classes-edit-form').ajaxForm({
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
					location.href = '#/wp_objectives';
					<?php
				}else{
					?>
					//location.href = '#/wp_objectives?m=edit&id=<?php echo $id;?>&after=edit';
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