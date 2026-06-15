<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Bill Plan Months';
	}else{
		$header = 'Add Bill Plan Months';
	}
?>
	<?= view('components/page_header', [
    'title' => 'Bill Plan Months',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Bill Plan Months', 'active' => true],
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
            url: '<?= base_url('admin/bill_plan_months/data2') ?>',
            type: "POST",
            data:{},
            success:function(res){
             $("#subjectsection").html(res);
    			  }
   });

});
</script>

<?= $this->endSection() ?>