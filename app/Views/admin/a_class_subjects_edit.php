<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Section Subjects';
		$id = $info->sec_id;
		$class_id = $info->class_id;
		$subject_id = intval($info->subject_id);			
	}else{
		$header = 'Add Section Subjects';
		$id = 0;
		$class_id = '';
		$subject_id = '';
	}
?>
<?= view('components/page_header', [
    'title' => 'Section Subjects db->query(\'SELECT * FROM section_subjects WHERE subject_id IN (SELECT sid FROM allsubject WHERE system_id =\'.$schoolinfo->system_id.\')\')->row(); if(empty($SectionsSubject_info->sec_sub_id)){ ?> Step 8 Of 10 To Complete System Configuration Your browser does not support the audio element. config->item(\'index_page\');?>#/fee_type?m=add">Click here for next Step',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Section Subjects', 'active' => true],
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
			//echo form_open('c=section_subjects&m=save', 'role="form" id="class-subjects-edit-form"');
			//echo form_hidden('id', $id);
			?>
			<div id="subjectsection"></div>
			<?php if(empty($SectionsSubject_info->sec_sub_id)){ ?>
						<a class="btn btn-primary" href="<?php echo base_url() . $this->config->item('index_page');?>#/fee_type?m=add">Click here for next Step</a>
				<?php } ?> 
          <!-- <div class="form-group">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
            <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
          </div> -->
          <?php //echo form_close();?> </div>
      </div>
    </div>
  </div>
  </div>
  </div>
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){
	 $.ajax({
            url: 'admin.php?c=a_class_subjects&m=data2', 
            type: "POST",
            data:{},
            success:function(res){
            	$("#subjectsection").html(res);
			  }
   });

	
	// $('#class-subjects-edit-form').ajaxForm({
	// 	beforeSubmit:function(formData, jqForm, options){
	// 		//return $('#class-subjects-edit-form').valid();
	// 		$('#submitBtn').html("Saving");
	// 		$('#submitBtn').prop('disabled', true);
	// 	},
	// 	success:function(responseText, statusText, xhr, form){
	// 		$('#submitBtn').html("Save");
	// 		$('#submitBtn').prop('disabled', false);
	// 		var json = $.parseJSON(responseText);
	// 		if(json.fee_type_id == false){
	//         	window.location.href = '<?php echo base_url() . $this->config->item('index_page');?>#/fee_type?m=add';
	//         	return;
	//       	}
	// 		if(json.success){
	// 			toastr.success(json.msg);
	// 			<?php
	// 			if($id == ''){
	// 				?>
	// 				location.reload(true);
	// 				//location.href = '#/section_subjects?m=add';
	// 				<?php
	// 			}else{
	// 				?>
	// 				location.href = '#/section_subjects?m=edit&id=<?php echo $id;?>&after=edit';
	// 				<?php
	// 			}
	// 			?>
	// 		}else{
	// 			toastr.error(json.msg);
	// 		}
	// 		return false;
	// 	}
	// 	});
});
</script>

<?= $this->endSection() ?>