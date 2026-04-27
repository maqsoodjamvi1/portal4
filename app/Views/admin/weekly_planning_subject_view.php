<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php $id=''; ?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Weekly Planning View
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Weekly Planning View</li>
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
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/weekly_planning/add') ?>">Add Weekly Planning</a></li>
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/weekly_planning_docview/add') ?>">Weekly Planning Document View</a></li>
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/weekly_planning_view') ?>">Weekly Planning View</a></li>
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/weekly_planning_subject_view') ?>">Weekly Planning Subject View</a></li>
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/weekly_planning_progress') ?>">Weekly Planning Progress</a></li>
			</ul>
			<div class="card-body">
			<div class="col-lg-12">
				<div class="row">
					<div class="col-lg-3">
						 <label for="class">Terms</label>
							<select class="form-control" name="term_id" id="term_id" class="form-control">
							  <option value="">Select Term</option>
							  <?php foreach ($terms_session_info as $value) { ?>
							      <option value="<?= $value->term_session_id ?>">
							          <?= $value->term_name ?>
							      </option>
							  <?php } ?>
							</select>

					</div>
					<div class="col-lg-7">
					    <div class="form-group">
			              <label for="class">Term Weeks</label>
			              <div>
			               <select id="term_weeks" name="term_weeks" class="form-control select2" multiple ></select>
			              </div>
			           </div> 
					</div>
					<div class="col-lg-2">	<button style="height: 24px;line-height: 10px;margin-top: 19px;" type="submit" onclick="submitterms();" class="btn btn-primary">View</button></div>
					<script type="text/javascript">
						function submitterms(){
								 var term_id = $('#term_id').val();
					       var term_week_id = $('#term_weeks').val(); 
								 $.ajax({
							      url: '/admin/weekly_planning_subject_view/data',
							      type: "POST",
							      data:{term_id:term_id,term_week_id:term_week_id },
							      success:function(res){
							 			   $("#termweekdates").html(res);
							 			}
							});
					  }
					</script>
				</div>
			 <div id="termweekdates"></div>

		  </div>
		    <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
      </div>

    </section>
    <!-- /.content -->
<style type="text/css">
   	tr th:first-child{
   	margin:0px; 	
   	}
   	tr th:first-child input[type="text"]{
   		width: 10px !important
   	}
   	tr td{
   		font-family: 'nafees-nastaleeq';
    	font-size: 15px;
   	}
   </style>	
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	//$(".select2").select2({closeOnSelect:false});
	$(".select2").select2({closeOnSelect:false});
	$("#term_id").change(function(){
        var term_id = $('#term_id').val();
	     $.ajax({
            url: '/admin/ajax/select-term-weeks',
            type: "POST",
            data:{term_id:term_id },
            success:function(res){
 			   $("#term_weeks").html(res);
 			 }
         });
    });	

	

});
</script>

<?= $this->endSection() ?>