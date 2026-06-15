<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	// if(isset($_GET['id'])){
	// 	$header = 'Edit System';
	// }else{
	// 	echo "<div class='mt-5 ms-5 alert-danger'>No Record Found </div>";
	// 	exit;
	// }
?>

<?= view('components/page_header', [
    'title' => 'Reports',
    'icon' => 'fas fa-chart-bar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Reports', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="card">  
        	<div class="card-body">
            <div class="tab-content">
              <div class="active tab-pane" id="settings">
              <h1 class="col-sm-6">Fee Reports</h1>
              <a href="<?= base_url('admin/report') ?>" class="btn btn-app col-sm-2">
                <i class="far fa-money-bill-alt"></i> All Reports
              </a>
              <a href="<?= base_url('admin/student_fee_report') ?>" class="btn btn-app col-sm-2">
                <i class="far fa-money-bill-alt"></i> Class Fee Report
              </a>
              <a href="<?= base_url('admin/student_fee_report/report_by_fee_student') ?>" class="btn btn-app col-sm-2">
                <i class="far fa-money-bill-alt"></i> Student Fee Report
              </a>
               <a href="<?= base_url('admin/family_fee_report') ?>" class="btn btn-app col-sm-2">
                <i class="far fa-money-bill-alt"></i> Family Fee Report
              </a>
              <a  href="<?= base_url('admin/student_fee_report/report_by_fee_type') ?>" class="btn btn-app col-sm-2">
                <i class="far fa-money-bill-alt"></i>  Report By Fee Types
              </a>
              <h1 class="col-sm-6">Exam Reports</h1>
              <a href="<?= base_url('admin/classwise_results') ?>" class="btn btn-app col-sm-2">
                <i class="fas fa-chalkboard"></i> Class Result
              </a>
              <a href="<?= base_url('admin/students_results_list') ?>" class="btn btn-app col-sm-2">
                <i class="fas fa-chalkboard"></i> Term Result
              </a>
              <a href="<?= base_url('admin/student_fee_report/report_by_fee_student') ?>" class="btn btn-app col-sm-2">
                <i class="fas fa-chalkboard"></i> Student Result
              </a>
              <h1 class="col-sm-6">Attendance Reports</h1>
              <a href="<?= base_url('admin/attendance_monthlyreport') ?>" class="btn btn-app col-sm-2">  <i class="nav-icon far fa-clock"></i> 
               Monthly Report
              </a>
                  
  						</div>
				  		<!-- /.tab-pane -->
				  	</div>
					</div>
    <!-- /.tab-content -->
  </div>
  <!-- /.nav-tabs-custom -->
</div>
<!-- /.col -->
</div>
<!-- /.row -->
</section>

<?= $this->endSection() ?>