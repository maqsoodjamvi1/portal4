<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Daily Diary
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Daily Diary</li>
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
				<li  class="nav-item"><a class="nav-link" href="<?= base_url('admin/classdairy/add') ?>">Add Daily Diary</a></li>
				<li  class="nav-item"><a class="nav-link" href="<?= base_url('admin/classdairy_view') ?>">Daily Diary</a></li>
				<li  class="nav-item"><a class="nav-link" href="<?= base_url('admin/classdairy_audio') ?>">Daily Diary Audio</a></li>
				<li  class="nav-item"><a class="nav-link" href="<?= base_url('admin/classdairy_audio/add') ?>">Add Daily Diary Audio</a>
				</li>
				<li  class="nav-item"><a class="nav-link active" href="<?= base_url('admin/classdairy_otherdetail_view') ?>">Diary Other Detail</a></li>
			</ul>
			<div class="card-body">
			<div class="col-lg-12">
				<div class="row">
					<div class="col-lg-4">
				    <label for="class">Terms</label>
					<select class="form-control" name="term_id" id="term_id" class="form-control">
						 <option value="">Select Term</option>
						<?php  foreach ($terms_session_info as $key => $value) { ?>
							<?php 
							  $this->db->where('term_id', $value->term_id);
							  $terminfo = $this->db->get('terms')->row();
							 ?>
							 <option value="<?php echo $value->term_session_id; ?>">
							 	<?php echo $terminfo->name; ?>
							 </option>
						<?php } ?>
					</select>
					</div>
					<div class="col-lg-4">
					    <div class="form-group">
			              <label for="class">Term Weeks</label>
			              <select class="form-control" name="term_weeks" id="term_weeks">
			               
			              </select>
			           </div>
					</div>
					<div class="col-lg-4">	<br><button type="submit" onclick="submitterms();" style="height: 40px;line-height: 20px;" class="btn btn-primary">View</button></div>
				
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

$("#term_id").change(function(){
        var term_id = $('#term_id').val();
	     $.ajax({
            url: 'admin.php?c=ajax&m=selectTermWeeks',
            type: "POST",
            data:{term_id:term_id },
            success:function(res){
 			   $("#term_weeks").html(res);
 			 }
         });
    });	

function submitterms(){
		var term_id = $('#term_id').val();
		var term_weeks_id = $('#term_weeks').val();
		 $.ajax({
	            url: 'admin.php?c=classdairy_otherdetail_view&m=data',
	            type: "POST",
	            data:{term_id:term_id,term_weeks_id:term_weeks_id },
	            success:function(res){
	 			   $("#termweekdates").html(res);
	 			   }
	         });	
}

</script>

<?= $this->endSection() ?>