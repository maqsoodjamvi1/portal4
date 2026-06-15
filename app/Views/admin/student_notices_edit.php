<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Notice';
		$id = $info->notice_id;
		$notice_name = $info->notice_name;
		$notice_date = $info->notice_date;
		$notice_detail = $info->notice_detail;
		$notice_audio = $info->notice_audio;
		$status = $info->status;
	}else{
		$header = 'Add Notice';
		$id = '';
		$notice_name = '';
		$notice_date = '';
		$notice_detail = '';
		$notice_audio = '';
		$status = 1;
	}
?>
<?= view('components/page_header', [
    'title' => 'Student Notices',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Notices', 'active' => true],
    ],
]) ?>
    
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
	<div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/student-notices') ?>">Student Notice</a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/student_notices/add') ?>"><?php echo $header;?></a></li>
		<?php }else{ ?>
		<li class="nav-item"><a class="nav-link" href="<?php echo base_url('/student_notices/edit?id=') . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
	</ul>
<div class="card-body">	
<div class="tab-content">
	<?php
		echo form_open( base_url('admin/student_notices/save'), 'role="form" id="classes-edit-form"');
		echo form_hidden('id', (string)$id);
	?>
<div class="col-lg-6">
   <div class="form-group">
      <label for="class">Notices</label><br>
      <select class="form-control select2" name="notice_id" id="notice_id" style="height: 24px;">
      	 <option value="0">Select Notice</option>
      </select>
 	</div>
	<div class="form-group">
       <label for="notice_date">Classes</label><br>
       <?php foreach ($sectionsclassinfo as $key => $value) { 
       	//print_r($value);
        ?>
       <label><input type="checkbox" name="section_id[]" id="section_id" value="<?php echo $value['section_id']; ?>"> <?php echo $value['sectionclassname']; ?></label>	<br>
       <?php } ?>
	</div>	
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
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){
	$("#notice_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: '/admin/student_notices/get_noticeinfo',
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term: term
            }
        },
       processResults: function (response) {
       	console.log(response);
              return {
                 results: response
              };
           },
           cache: true
    }
 });
	
	$('#classes-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			$('#submitBtn').html("Saving");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){	
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);	
			var json = $.parseJSON(responseText);
			console.log(json);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '/admin/student_notices';
					<?php
				}else{
					?>
					location.href = '/admin/student_notices';
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