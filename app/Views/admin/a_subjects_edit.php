<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Subject';
		$id = $info->sid;
		$subject_name = $info->subject_name;
		$subject_short_name = $info->subject_short_name;
	}else{
		$header = 'Add Subject';
		$id = '';
		$subject_name = '';
		$subject_short_name = '';
	}
?>
    <?= view('components/page_header', [
    'title' => 'Subjects',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Subjects', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		  <div class="card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/a_subjects') ?>">Subjects</a></li>
				<?php if($id == ''){ ?>
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/a_subjects/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
				<li class="nav-item"><a class="nav-link active" href="<?php echo '#/a_subjects?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
				<?php } ?>
			</ul>
		<div class="card-body">	
		<div class="tab-content">
	<div class="container">
    <div class="form-group">
        <?php
			echo form_open('c=a_subjects&m=save', 'role="form" id="user-edit-form"');
			?>
   
            <div class="table-responsive">  
                <table class="table table-bordered" id="dynamic_field"> 
				<?php
				if(!empty($subjectsinfo)){ 
				$i = 0;
				foreach ($subjectsinfo as $key => $value) { ?>
                    <tr>  
                        <td>
                        	<input type="hidden" name="rowscount[]" value="1" />	
                        	<input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->sid; ?>">
                        	<input type="text" name="subject_name<?php echo $i; ?>"  value="<?php echo $value->subject_name; ?>" placeholder="Subject Name" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="short_name<?php echo $i; ?>" value="<?php echo $value->subject_short_name; ?>" placeholder="Subject Short Name" class="form-control name_list" required="" /></td> 
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
                        	<input type="text" name="subject_name0"  value="" placeholder="Subject Name" class="form-control name_list" required="" /></td>  
                        <td><input type="text" name="short_name0" value="" placeholder="Subject Short Name" class="form-control name_list" required="" /></td> 
                    </tr>
            	<?php } ?>
                <tr><td></td><td></td> <td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td>  </tr>
                </table>  
             
            </div>
   <div class="row">
		 <div class="col-lg-12">
		    <div class="form-group">
		        <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
				<button type="reset" class="btn btn-secondary">Reset</button>
				<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
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
    </div>
</div>
</section>
<!-- /.content -->
<script type="text/javascript">
    $(document).ready(function(){      
      var i= <?php echo $i; ?>;  
      //alert(i);
   
      $('#add').click(function(){  
        
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" name="subject_name'+i+'" placeholder="Subject Name" class="form-control name_list" required /></td><td><input type="text" name="short_name'+i+'" placeholder="Short Name" class="form-control name_list" required /></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
              i++;   
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      });  
  
    });  
</script>
<script type="text/javascript">
$(function(){
	$('#user-edit-form').validate({
		rules:{
			subject_name:{
				required:true,
				remote:{
					param:{
						url:'<?php echo base_url('admin/ajax/check_value&table=a_subject&field=subject_name'); ?>'
					},
					depends:function(element){
						var id = $(element).attr('id');
						return ($(element).val() !== $('#original' + id).val());
					}
				}
			}
		},
		messages:{
			username:{
				required:'Subject is Required',
				remote:'Subject is exists'
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
				<?php
				if($id == ''){
					?>
					location.href = '#/a_subjects';
					<?php
				}else{
					?>
					location.href = '#/a_subjects?m=edit&id=<?php echo $id;?>&after=edit';
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