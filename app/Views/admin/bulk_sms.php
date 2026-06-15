<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Bulk SMS';
		$id = $info->quiz_id;
		$quiz_name = $info->quiz_name;
		$subject_id = $info->class_sub_id;
		$quiz_image = $info->quiz_image;
		$start_datetime = $info->start_datetime;
		$expire_datetime = $info->expire_datetime;

	}else{
		$header = 'Add Bulk SMS';
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
<?= view('components/page_header', [
    'title' => 'Bulk SMS',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Bulk SMS', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
<div class="row">
<div class="col-lg-12">
  <div class="card card-primary card-outline card-tabs">
  <div class="card-header p-0 pt-1 border-bottom-0">
	<div class="card-body">
	<div class="tab-content">
	<?php
		echo form_open('c=bulk_sms&m=save', 'role="form" id="user-edit-form"');
		echo form_hidden('id', $id);
	?>
		
			<div class="form-group">
              <label for="start_datetime">Select Excel</label>
              <input type="file" class="form-control" name="documentfile" id="quiz_image" value="">
     	</div>
     	<div class="form-group">
              <label for="start_datetime">Message</label>
              <textarea class="form-control" name="document" id="document"></textarea>
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
				
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
})
</script>

<?= $this->endSection() ?>