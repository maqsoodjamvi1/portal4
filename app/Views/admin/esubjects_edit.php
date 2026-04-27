<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit E Subject';
		$id = $info->sid;
		$subject = $info->subject;
		$short_name = $info->short_name;
	}else{
		$header = 'Add E Subject';
		$id = '';
		$subject_name = '';
		$subject_short_name = '';
	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          E Subjects
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">E Subjects</li>
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
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/subjects') ?>">Subjects</a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/subjects/add') ?>"><?php echo $header;?></a></li>
		<?php }else{ ?>
		<li class="nav-item"><a class="nav-link" href="<?php echo '#/subjects?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
	</ul>
<div class="card-body">
<div class="tab-content">
<div class="">
    <div class="form-group">
    <?php echo form_open('c=esubjects&m=save', 'role="form" id="user-edit-form"'); ?>
        <div class="">  
            <table class="table table-bordered" id="dynamic_field"> 
            <thead><tr><th>Name</th><th>Slug/Short Name</th><th>Description</th></tr></thead><tbody>
			<?php 
				$i = 0;
				foreach ($subjectsinfo as $key => $value) { ?>
                    <tr>  
                     <td>
                        <input type="hidden" name="rowscount[]" value="1" />	
                        <input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->sub_id; ?>">
                        <input type="text" id="slugme<?php echo $i; ?>" name="subject<?php echo $i; ?>"  value="<?php echo $value->subject; ?>" placeholder="Subject Name" class="form-control name_list" required="" /></td> 
                        <td><input type="text" name="slug<?php echo $i; ?>"  value="<?php echo $value->slug; ?>" placeholder="Slug" class="form-control name_list slug<?php echo $i; ?>" required="" /><input type="text" name="short_name<?php echo $i; ?>" value="<?php echo $value->short_name; ?>" placeholder="Subject Short Name" class="form-control name_list" required="" /></td> 
                        <td><input type="text" name="detail<?php echo $i; ?>" value="<?php echo $value->detail; ?>" placeholder="Detail" class="form-control name_list"  />
                         <input type="text" id="meta_title<?php echo $i; ?>" class="form-control" placeholder="Meta Title" name="meta_title<?php echo $i; ?>" value="<?php echo $value->meta_title; ?>">
			             <textarea rows="3" placeholder="Meta Keywords" name="meta_keywords<?php echo $i; ?>" class="form-control"><?php echo $value->meta_keywords; ?></textarea>
			             <textarea rows="3" placeholder="Meta Description" name="meta_description<?php echo $i; ?>" class="form-control"><?php echo $value->meta_description; ?></textarea>	
                        </td> 
                    </tr>
                    
                     <script type="text/javascript">
			          $(function(){
			                $('#slugme<?php echo $i; ?>').slugIt({
			                    output: '.slug<?php echo $i; ?>'
			                });
			            });
			          </script>
			          <?php $i++ ?>  
                    <?php } ?>
                    <tr><td></td><td></td><td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td>  </tr>
                    </tbody>
                </table>  
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
         <?php echo form_close(); ?>
    </div> 
</div>
<script type="text/javascript">
    $(document).ready(function(){      
      var i= <?php echo $i; ?>;  
   
      $('#add').click(function(){  
        
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" id="slugme'+i+'"  name="subject'+i+'" placeholder="Subject Name" class="form-control name_list" required /></td><td><input type="text" name="slug'+ i +'"  value="" placeholder="Slug" class="form-control name_list slug'+ i +'" required="" /><input type="text" name="short_name'+i+'" placeholder="Short Name" class="form-control name_list" required /></td><td><input type="text" name="detail'+i+'" placeholder="Detail" class="form-control name_list"  /><input type="text" id="meta_title'+ i +'" class="form-control" placeholder="Meta Title" name="meta_title'+ i +'" value=""><textarea rows="3" placeholder="Meta Keywords" name="meta_keywords'+ i +'" class="form-control"></textarea><textarea rows="3" placeholder="Meta Description" name="meta_description'+ i +'" class="form-control"></textarea></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
                

             $('#slugme'+ i).slugIt({
            	output: '.slug'+ i
       		 });
       		 i++; 
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      });  
  
    });  
</script>
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
	$('#user-edit-form').validate({
		rules:{
			subject_name:{
				required:true,
				remote:{
					param:{
						url:'<?php echo base_url('admin/ajax/check_value&table=allsubject&field=subject_name'); ?>'
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
			$('#submitBtn').html("Ajax Request is Processing!");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Submit");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php if($id == ''){ ?>
					location.href = '#/esubjects';
				<?php }else{ ?>
					location.href = '#/esubjects?m=edit&id=<?php echo $id;?>&after=edit';
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