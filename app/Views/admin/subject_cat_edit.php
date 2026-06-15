<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
{
	$header = 'Add Subject Category';
	$id = '';
	$cat_name = '';
	$class_id = '';
	$detail = '';
	$subject_id = '';
}
?>
<?= view('components/page_header', [
    'title' => 'Subject Categories',
    'icon' => 'fas fa-folder',
    'subtitle' => $header ?? null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Subject Categories', 'url' => base_url('admin/subject_cat')],
        ['label' => isset($info) ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>
<!-- Main content -->
<section class="content">
    <div class="row">
    <div class="col-lg-12">
	  <div class="card card-primary card-outline card-tabs">
    	<div class="card-header p-0 pt-1 border-bottom-0">
		<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/subject_cat') ?>">Subject Categories</a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/subject_cat/add') ?>"><?php echo $header;?></a></li>
		<?php	}else{	?>
		<li class="nav-item"><a class="nav-link" href="<?php echo '#/subject_cat?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
		</ul>
<div class="card-body">		
<div class="tab-content">
<?php
	echo form_open('c=subject_cat&m=save', 'role="form" id="user-edit-form"');
	echo form_hidden('id', $id);
?>
<div class="">
            <div class="form-group">
              <label for="class">Subjects</label>
              <select class="form-control" name="subject_id" id="subject_id">
              	<option value="">Select Subject</option>
                <?php if(isset($subjectinfo)){
						foreach ($subjectinfo as  $subjectvalue) { ?>
                <option <?php if($subjectvalue->sub_id == $subject_id) { ?> selected <?php } ?> value="<?php echo $subjectvalue->sub_id; ?>"><?php echo $subjectvalue->subject; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
        </div>
        <div class="col-md-12 bg">
		    <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		</div>
        <div id="sub_cat_list">  
                    
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
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){
	$("#subject_id").change(function(){
		$("#loader-1").css("display", "block");
        var subject_id = $('#subject_id').val();
	     $.ajax({
            url: 'admin.php?c=subject_cat&m=getsubjectCat',
            type: "POST",
            data:{subject_id:subject_id },
            success:function(res){
			   $("#sub_cat_list").html(res);
			   $("#loader-1").css("display", "none");
 			}

         });
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
					location.href = '#/subject_cat';
				<?php
				}else{
				?>
				location.href = '#/subject_cat?m=edit&id=<?php echo $id;?>&after=edit';
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