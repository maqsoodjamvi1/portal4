<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Quiz Questions';
		$id = $info->sub_cat_id;
		$cat_name = $info->cat_name;
		$detail = $info->detail;
		$subject_id = $info->sub_id;
		$class_id = $info->class_id;

	}else{
		$header = 'Add Quiz Questions';
		$id = '';
		$quiz_name = '';
		$class_id = '';
		$term_id = '';
		$session_id = '';
		$created_date = '';
		$start_datetime = '';
		$expire_datetime = '';
		$subject_id = '';

	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Quiz Questions
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Quiz Questions</li>
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
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/quiz_questions') ?>">Quiz Questions</a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/quiz_questions/add') ?>"><?php echo $header;?></a></li>
		<?php }else{ ?>
		<li class="nav-item"><a class="nav-link" href="<?php echo '#/quiz_questions?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
	</ul>
	<div class="card-body">
	<div class="tab-content">
	<?php
		echo form_open('c=quiz_questions&m=save', 'role="form" id="user-edit-form"');
		echo form_hidden('id', $_GET['id']);
	?>	
	<div class="form-group">
      <label for="class">Subjects</label>
      <select class="form-control" name="subject_id" id="subject_id">
        <?php if(isset($subjectinfo)){
				foreach ($subjectinfo as  $subjectvalue) { ?>
        <option <?php if($subjectvalue->sub_id == $subject_id) { ?> selected <?php } ?> value="<?php echo $subjectvalue->sub_id; ?>"><?php echo $subjectvalue->subject; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
    <div id="loadQuestions">
    	
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
	$("#subject_id").change(function(){
        var subject_id = $('#subject_id').val();
        var quiz_id = <?php echo $_GET['id']; ?>;
	    $.ajax({
            url: 'admin.php?c=quiz_questions&m=selectQuestion',
            type: "POST",
            data:{subject_id:subject_id,quiz_id:quiz_id},
           	success:function(res){
			    $("#loadQuestions").html(res);
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
					location.href = '#/quiz_questions';
					<?php
				}else{
					?>
					location.href = '#/quiz_questions';
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