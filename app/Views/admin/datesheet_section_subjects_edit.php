<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Datesheet';
		$id = $info->sec_id;
		$class_id = $info->class_id;
		$subject_id = intval($info->subject_id);			
	}else{
		$header = 'Add Datesheet';
		$id = 0;
		$class_id = '';
		$subject_id = '';
	}
?>
<?= view('components/page_header', [
    'title' => $header ?? 'Datesheet',
    'icon' => 'fas fa-calendar-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Datesheet', 'url' => base_url('admin/datesheet')],
        ['label' => isset($info) ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card sms-card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
        <div class="card-body">		
        <div class="tab-content">
      			<div id="subjectsection"></div>
			
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
	 $.ajax({
          url: 'admin.php?c=datesheet_section_subjects&m=data2', 
          type: "POST",
          data:{},
          success:function(res){
          	$("#subjectsection").html(res);
		  		}	
   });
});
</script>

<?= $this->endSection() ?>