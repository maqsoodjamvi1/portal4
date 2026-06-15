<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Weekly Planning Progress';
		$id = $info->did;
		$date1 = $info->date1;
		$class_id = $info->class_id;
		$subject_id = $info->subject_id;
		$objectives = $info->objectives;
		$type1 = $info->type1;
		$path1 = $info->path1;

	}else{
		$header = 'Weekly Planning Progress';
		$id = '';
		$date1 = '';
		$class_id = '';
		$subject_id = '';
		$objectives = '';
		$type1 = '';
		$path1 = '';

	}
?>
<?= view('components/page_header', [
    'title' => 'Weekly Planning Progress',
    'icon' => 'fas fa-tasks',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Weekly Planning', 'url' => base_url('admin/weekly_planning')],
        ['label' => 'Progress', 'active' => true],
    ],
]) ?>
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
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/weekly_planning_progress') ?>">Weekly Planning Progress</a></li>
			</ul>
        <div class="card-body">
        <div class="tab-content">
          
			<div class="row">
				<div class="col-lg-2">
				 <div class="form-group">
                  <label for="term">Session</label>
				  <select name="session_id" id="session_id" class="form-control">
				 <!-- <option value="0">Select Session</option>-->
				  <?php foreach($academic_session as $session){ ?>
                  <option value="<?php echo $session->session_id; ?>"><?php echo $session->session_name; ?></option>
				  <?php } ?>
				  </select>
				</div>
				</div>	
	           <div class="col-lg-2">
			    <div class="form-group">
	              <label for="class">Terms session</label>
	              <select class="form-control" name="term_session_id" id="term_session_id">
									<option value="">Select Term Sessio</option>
								  <?php if(isset($termsinfo)){
							      foreach ($termsinfo as $termvalue) { ?>
							        <option value="<?= $termvalue->term_session_id ?>">
							            <?= $termvalue->term_name ?>
							        </option>
									<?php } } ?>
	              </select>
	            </div>
			   </div>
		   
		  <div class="col-lg-2">
            <div class="form-group">
	              <label for="class">Sections</label>
	              <select class="form-control select2" name="section_id" id="section_id">
	              	 <option value="0">Select Section</option>
	                <?php if(isset($sectionsclassinfo)){
						  foreach ($sectionsclassinfo as  $secionvalue) { ?>
	                <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
	              	<?php } ?>
	                <?php } ?>
	              </select>
	            </div>
          </div>
        
		   <div class="col-lg-2">
		   <a class="btn btn-primary" style="margin-top: 19px;height: 24px;line-height: 10px;" onclick="selecttermWeek();" >View</a>
		   </div>
		  
		  </div>
		  <div class="col-md-12 bg">
		    <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		  </div> 
		  <div  id="termweekdates">
          
		  </div>
		    </div>
      </div>
    </div>
  </div>
</section>
<!-- /.content -->
<script type="text/javascript">
$("#term_session_id").change(function(){
	    $("#termweekdates").html('');
	});

$("#section_id").change(function(){
        var section_id = $('#section_id').val();
	     $.ajax({
            url: '/admin/ajax/selectsubjectby-section',
            type: "POST",
            data:{section_id:section_id },
            success:function(res){
 			   $("#subject_id").html(res);
			}
         });
    });

$("#session_id").change(function(){
        var session_id = $('#session_id').val();
	     $.ajax({
            url: '/admin/ajax/selecttermby-session',
            type: "POST",
            data:{session_id:session_id },
            success:function(res){
 			   $("#term_session_id").html(res);
			}
         });
    });


function selecttermWeek(){
	$("#loader-1").css("display", "block");
	var term_session_id = $('#term_session_id').val();
	var session_id = $('#session_id').val();
	var subject_id = $('#subject_id').val();
	var section_id = $('#section_id').val();

	 $.ajax({
            url: '/admin/weekly_planning_progress/get-weekly-planning',
            type: "POST",
            data:{term_session_id:term_session_id,session_id:session_id,section_id:section_id },
            success:function(res){
 			   $("#termweekdates").html(res);
 			   $("#loader-1").css("display", "none");
 			   }
         });	
}
</script>

<?= $this->endSection() ?>