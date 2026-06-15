<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Fee Plan Months';
		$id = $info->sec_id;
		$class_id = $info->class_id;
		$subject_id = intval($info->subject_id);			
	}else{
		$header = 'Add Fee Plan Months';
		$id = 0;
		$class_id = '';
		$subject_id = '';
	}
?>
	<?= view('components/page_header', [
    'title' => 'Fee Plan Months',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Class Section', 'active' => true],
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
        url: '/admin/fee_plan_months/data2', 
        type: "POST",
        data: {},
        dataType: 'json', // important! so you get res.html as an object
        success: function(res) {
            $("#subjectsection").html(res.html);
        }
    });
});
</script>

<?= $this->endSection() ?>