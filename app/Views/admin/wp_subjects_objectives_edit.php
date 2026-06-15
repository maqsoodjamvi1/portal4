<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Subjects Objectives';
		$id = $info->sec_id;
		$class_id = $info->class_id;
		$subject_id = intval($info->subject_id);			
	}else{
		$header = 'Add Subjects Objectives';
		$id = 0;
		$class_id = '';
		$subject_id = '';
	}
?>
<?= view('components/page_header', [
    'title' => 'Subjects Objectives',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Subjects Objectives', 'active' => true],
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
        	<div class="col-lg-2">
            <div class="form-group">
	              <label for="class">Classes</label>
	              <select class="form-control select2" name="class_id" id="class_id">
	              	 <option value="0">Select class</option>
	                <?php if(isset($classesinfo)){
						  			foreach ($classesinfo as  $classvalue) { ?>
	                		<option value="<?php echo $classvalue->class_id; ?>"><?php echo $classvalue->class_name; ?></option>
	              	<?php } ?>
	                <?php } ?>
	              </select>
	            </div>
          </div>
      		<div id="subjectsobjectives"></div>
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
	$("#class_id").change(function(){
        var class_id = $('#class_id').val();
	 $.ajax({
            url: '/admin/wp-subjects-objectives/data2', 
            type: "POST",
            data:{class_id:class_id},
            success:function(res){
            	$("#subjectsobjectives").html(res);
			  }
   });
});
});
</script>

<?= $this->endSection() ?>