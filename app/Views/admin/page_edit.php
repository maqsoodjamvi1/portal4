<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Page';
		$id = $info->page_id;
		$title = $info->title;
		$content = $info->content;

	}else{
		$header = 'Add Page';
		$id = '';
		$title = '';
		$content = '';
	}
?>
<?= view('components/page_header', [
    'title' => 'Page',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Page', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
<div class="row">
<div class="col-lg-12">
  <div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/pages') ?>">Page</a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/pages/add') ?>"><?php echo $header;?></a></li>
		<?php }else{ ?>
		<li class="nav-item"><a class="nav-link active" href="<?php echo '#/pages?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
	</ul>
	<div class="card-body">
	<div class="tab-content">
	<?php
		echo form_open('c=pages&m=save', 'role="form" id="user-edit-form"');
		echo form_hidden('id', $id);
	?>
	<div class="row">	
			<div class="col-sm-12">	
		   <div class="form-group">
          <label for="title">Title</label>
          <input type="text" class="form-control" name="title" id="title" value="<?php echo $title; ?>">
			</div>
			</div>
			<div class="col-sm-12">	
		   <div class="form-group">
          <label for="father_name">Content</label>
          <textarea class="form-control editor" name="content" id="content"><?php echo $content; ?></textarea>
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
<script> 
		$(document).ready(function() {
		  $('.editor').summernote();
		});	
</script>    
<script type="text/javascript">
$(function(){
	$('#datepicker').datetimepicker({
       format: 'YYYY-MM-DD'
     });
    $('#datepicker2').datetimepicker({
        format: 'YYYY-MM-DD'
    });
	$('#user-edit-form').validate({
		rules:{
			name:{
				required:true,
			}
		},
		messages:{
			name:{
				required:'Term is Required',	
			}
		}
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
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
					location.href = '#/pages';
					<?php
				}else{
					?>
					location.href = '#/pages';
					<?php
				}
				?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
})
</script>

<?= $this->endSection() ?>