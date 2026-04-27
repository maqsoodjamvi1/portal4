<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Quiz';
		$id = $info->quiz_id;
		$quiz_name = $info->quiz_name;
		$subject_id = $info->class_sub_id;
		$quiz_image = $info->quiz_image;
		$start_datetime = $info->start_datetime;
		$expire_datetime = $info->expire_datetime;

	}else{
		$header = 'Add Quiz';
		$id = '';
		$quiz_name = '';
		$class_id = '';
		$term_id = '';
		$quiz_image = '';
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
          Quiz
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Quiz</li>
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
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/quiz') ?>">Quiz</a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/quiz/add') ?>"><?php echo $header;?></a></li>
		<?php }else{ ?>
		<li class="nav-item"><a class="nav-link active" href="<?php echo '#/quiz?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
	</ul>
	<div class="card-body">
	<div class="tab-content">
	<?php
		echo form_open('c=quiz&m=save', 'role="form" id="user-edit-form"');
		echo form_hidden('id', $id);
	?>
			
			<div class="form-group">
              <label for="class">Subjects</label>
              <select class="form-control" name="subject_id" id="subject_id">
                <?php if(isset($subjectinfo)){
					foreach ($subjectinfo as  $subjectvalue) { 
						print_r($subjectvalue);

						?>
                <option <?php if($subjectvalue->sub_id == $subject_id) { ?> selected <?php } ?> value="<?php echo $subjectvalue->sub_id; ?>"><?php echo $subjectvalue->subject; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
		   <div class="form-group">
              <label for="quiz_name">Quiz Name</label>
              <input type="text" class="form-control" name="quiz_name" id="quiz_name" value="<?php echo $quiz_name;?>">
			</div>
			<div class="form-group">
              <label for="start_datetime">Quiz Image</label>
              <input type="file" class="form-control" name="quiz_image" id="quiz_image" value="<?php echo $quiz_image;?>">
              <input type="hidden" class hh:mm:ss a="form-control" name="quiz_image" id="quiz_image" value="<?php echo $quiz_image;?>">
              <img style="width:100px;" src="worksheets/<?php echo $quiz_image; ?>">
			</div>
			<div class="form-group">
              <label>Start Date/Time</label>
      		  <div class="input-group date" id="datepicker" data-target-input="nearest">
                <input type="text" class="form-control datetimepicker-input" data-target="#datepicker"  name="start_datetime" required value="<?php  echo $start_datetime; ?>"/>
                <div class="input-group-append" data-target="#datepicker" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="expire_datetime">Expire Date/Time</label>
      		  <div class="input-group date" id="datepicker2" data-target-input="nearest">
                <input type="text" class="form-control datetimepicker-input" data-target="#datepicker2" required name="expire_datetime" id="expire_datetime" value="<?php echo $expire_datetime;?>" />
                <div class="input-group-append" data-target="#datepicker2" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
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
			var json = $.parseJSON(JSON.stringify(responseText));
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/quiz';
					<?php
				}else{
					?>
					location.href = '#/quiz';
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