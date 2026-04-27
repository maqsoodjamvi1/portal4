<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Bill Plan Months';
	}else{
		$header = 'Add Bill Plan Months';
	}
?>
<!-- Content Header (Page header) -->
	<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Bill Plan Months
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Bill Plan Months</li>
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
            url: 'admin.php?c=bill_plan_months&m=data2', 
            type: "POST",
            data:{},
            success:function(res){
             $("#subjectsection").html(res);
    			  }
   });

});
</script>

<?= $this->endSection() ?>